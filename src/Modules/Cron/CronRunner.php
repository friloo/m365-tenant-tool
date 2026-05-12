<?php

namespace App\Modules\Cron;

use App\Core\Config;
use App\Database\DB;
use App\Graph\GraphClient;
use App\Modules\ConditionalAccess\ConditionalAccessService;
use App\Modules\Dashboard\DashboardService;
use App\Modules\Devices\DevicesService;
use App\Modules\Groups\GroupsService;
use App\Modules\Licenses\LicensesService;
use App\Modules\MfaMethods\MfaMethodsService;
use App\Modules\NamedLocations\NamedLocationsService;
use App\Modules\SecurityPosture\SecurityPostureService;
use App\Modules\ServiceHealth\ServiceHealthService;
use App\Modules\ShareReview\ShareReviewService;
use App\Modules\StaleAccounts\StaleAccountsService;
use App\Modules\Users\UsersService;
use App\Modules\WeeklyReport\WeeklyReportService;
use App\Queue\QueueWorker;

class CronRunner
{
    /** All registered cron jobs. Key = job_key. */
    private array $definitions;

    public function __construct(private GraphClient $graph)
    {
        $this->definitions = $this->buildDefinitions();
    }

    // ── Public API ─────────────────────────────────────────────

    /**
     * Main entry point called from run-cron.php.
     * Processes queue first (every minute), then checks scheduled jobs.
     * Respects a 55-second total budget to stay within cron minute intervals.
     */
    public function run(): void
    {
        $this->ensureJobsExist();

        $budget  = 55.0;
        $started = microtime(true);

        // Queue worker always runs first — highest priority
        $this->executeIfDue('queue_worker', $started, $budget);

        // Remaining scheduled jobs (stop early if time is up)
        foreach (array_keys($this->definitions) as $key) {
            if ($key === 'queue_worker') continue;
            if ((microtime(true) - $started) >= $budget) break;
            $this->executeIfDue($key, $started, $budget);
        }
    }

    /**
     * Force-run a single job regardless of schedule.
     * Used by the web UI "Run now" button.
     * Returns log output.
     */
    public function runJob(string $jobKey): array
    {
        $this->ensureJobsExist();
        $def = $this->definitions[$jobKey] ?? null;
        if (!$def) {
            return ['status' => 'error', 'log' => "Unknown job: {$jobKey}", 'seconds' => 0];
        }

        $t0 = microtime(true);
        try {
            $log    = ($def['handler'])();
            $status = 'success';
        } catch (\Throwable $e) {
            $log    = $e->getMessage();
            $status = 'error';
        }
        $seconds = round(microtime(true) - $t0, 2);

        $this->saveJobResult($jobKey, $status, $log, $seconds);
        return ['status' => $status, 'log' => $log, 'seconds' => $seconds];
    }

    /** Return all job definitions merged with their current DB state. */
    public function getJobStates(): array
    {
        $this->ensureJobsExist();
        $rows = DB::fetchAll('SELECT * FROM cron_jobs ORDER BY job_key');
        $byKey = [];
        foreach ($rows as $r) {
            $byKey[$r['job_key']] = $r;
        }

        $result = [];
        foreach ($this->definitions as $key => $def) {
            $db = $byKey[$key] ?? [];
            $result[] = array_merge([
                'job_key'          => $key,
                'label'            => $def['label'],
                'description'      => $def['description'] ?? '',
                'default_interval' => $def['default_interval'],
                'enabled'          => 1,
                'interval_minutes' => $def['default_interval'],
                'last_run_at'      => null,
                'last_run_status'  => null,
                'last_run_log'     => null,
                'last_run_seconds' => null,
                'next_run_at'      => null,
            ], $db);
        }
        return $result;
    }

    /** Update interval and enabled state for a job. */
    public function updateJob(string $jobKey, bool $enabled, int $intervalMinutes): void
    {
        $def = $this->definitions[$jobKey] ?? null;
        if (!$def) return;

        $intervalMinutes = max(1, $intervalMinutes);
        DB::execute(
            'UPDATE cron_jobs SET enabled = ?, interval_minutes = ?,
             next_run_at = DATE_ADD(IFNULL(last_run_at, NOW()), INTERVAL ? MINUTE)
             WHERE job_key = ?',
            [(int)$enabled, $intervalMinutes, $intervalMinutes, $jobKey]
        );
    }

    // ── Internals ──────────────────────────────────────────────

    private function executeIfDue(string $jobKey, float $started, float $budget): void
    {
        $row = DB::fetchOne(
            "SELECT * FROM cron_jobs WHERE job_key = ? AND enabled = 1
             AND (next_run_at IS NULL OR next_run_at <= NOW())",
            [$jobKey]
        );

        if (!$row) return;
        if ((microtime(true) - $started) >= $budget) return;

        $def = $this->definitions[$jobKey] ?? null;
        if (!$def) return;

        $t0 = microtime(true);
        try {
            $log    = ($def['handler'])();
            $status = 'success';
        } catch (\Throwable $e) {
            $log    = $e->getMessage();
            $status = 'error';
        }
        $seconds = round(microtime(true) - $t0, 2);
        $this->saveJobResult($jobKey, $status, $log, $seconds);
    }

    private function saveJobResult(string $jobKey, string $status, string $log, float $seconds): void
    {
        $interval = (int)DB::fetchOne(
            'SELECT interval_minutes FROM cron_jobs WHERE job_key = ?',
            [$jobKey]
        )['interval_minutes'];

        DB::execute(
            'UPDATE cron_jobs
             SET last_run_at = NOW(), last_run_status = ?, last_run_log = ?,
                 last_run_seconds = ?,
                 next_run_at = DATE_ADD(NOW(), INTERVAL ? MINUTE)
             WHERE job_key = ?',
            [$status, substr($log, 0, 4000), $seconds, $interval ?: 60, $jobKey]
        );
    }

    /** Insert missing job rows with defaults (idempotent). */
    private function ensureJobsExist(): void
    {
        foreach ($this->definitions as $key => $def) {
            DB::execute(
                'INSERT IGNORE INTO cron_jobs (job_key, label, description, interval_minutes)
                 VALUES (?, ?, ?, ?)',
                [$key, $def['label'], $def['description'] ?? '', $def['default_interval']]
            );
        }
    }

    /** Register all cron job definitions (handlers are closures). */
    private function buildDefinitions(): array
    {
        $graph = $this->graph;

        return [
            'queue_worker' => [
                'label'            => 'Job-Queue verarbeiten',
                'description'      => 'Verarbeitet ausstehende Aufgaben aus der Warteschlange (Lizenzänderungen, Bulk-Aktionen). Läuft jede Minute.',
                'default_interval' => 1,
                'handler'          => function () use ($graph): string {
                    $worker = new QueueWorker($graph);
                    $n = $worker->processNext(20);
                    return $n > 0 ? "{$n} Job(s) verarbeitet" : 'Keine ausstehenden Jobs';
                },
            ],

            'cache_warm' => [
                'label'            => 'Cache vorwärmen (alle Module)',
                'description'      => 'Ruft alle Graph-API-Endpunkte im Hintergrund ab und füllt den DB-Cache. Seiten laden danach sofort aus der DB ohne API-Wartezeit.',
                'default_interval' => 5,
                'handler'          => function () use ($graph): string {
                    $results = [];
                    $ok = 0;
                    $fail = 0;

                    $jobs = [
                        'Dashboard — Metriken'       => fn() => (new DashboardService($graph))->getMetrics(),
                        'Dashboard — Lizenzübersicht'=> fn() => (new DashboardService($graph))->getLicenseSummary(),
                        'Dashboard — Sicherheit'     => fn() => (new DashboardService($graph))->getSecurityStatus(),
                        'Dashboard — Erweitert'      => fn() => (new DashboardService($graph))->getExtendedStats(),
                        'Benutzer — Gesamtliste'     => fn() => (new UsersService($graph))->getAll(),
                        'Benutzer — MFA-Status'      => fn() => (new UsersService($graph))->getMfaStatus(),
                        'MFA-Methoden'               => fn() => (new MfaMethodsService($graph))->getAll(),
                        'Conditional Access'         => fn() => (new ConditionalAccessService($graph))->getPolicies(),
                        'Named Locations'            => fn() => (new NamedLocationsService($graph))->getAll(),
                        'Geräte'                     => fn() => (new DevicesService($graph))->getAll(),
                        'Gruppen'                    => fn() => (new GroupsService($graph))->getAll(),
                        'Lizenzen'                   => fn() => (new LicensesService($graph))->getSkus(),
                        'Dienststatus'               => fn() => (new ServiceHealthService($graph))->getOverview(),
                        'Security Posture'           => fn() => (new SecurityPostureService($graph))->runChecks(),
                    ];

                    foreach ($jobs as $label => $fn) {
                        try {
                            $fn();
                            $ok++;
                        } catch (\Throwable $e) {
                            $results[] = "{$label}: " . $e->getMessage();
                            $fail++;
                        }
                    }

                    $msg = "OK: {$ok}/" . count($jobs);
                    if ($fail > 0) {
                        $msg .= ", Fehler: {$fail} — " . implode('; ', array_slice($results, 0, 3));
                    }
                    return $msg;
                },
            ],

            'share_scan' => [
                'label'            => 'Freigaben scannen',
                'description'      => 'Synchronisiert externe SharePoint-Freigaben aus der Graph API und legt neue Einträge in der Datenbank an.',
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $service = new ShareReviewService($graph);
                    $result  = $service->scanAndSync();
                    $found   = count($result['found'] ?? $result);
                    $new     = count($result['new'] ?? []);
                    return "Gefunden: {$found}, Neu: {$new}";
                },
            ],

            'share_emails' => [
                'label'            => 'Review-E-Mails senden',
                'description'      => 'Sendet Bestätigungs-E-Mails an Freigabe-Besitzer, deren Prüfintervall abgelaufen ist.',
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $service = new ShareReviewService($graph);
                    $n = count($service->sendDueReviewEmails());
                    return "{$n} E-Mail(s) gesendet";
                },
            ],

            'share_auto_revoke' => [
                'label'            => 'Freigaben automatisch widerrufen',
                'description'      => 'Widerruft Freigaben, für die kein Besitzer innerhalb der Toleranzzeit reagiert hat.',
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $service = new ShareReviewService($graph);
                    $n = count($service->autoRevokeOverdue());
                    return "{$n} Freigabe(n) widerrufen";
                },
            ],

            'stale_cleanup' => [
                'label'            => 'Inaktive Konten bereinigen',
                'description'      => 'Prüft inaktive Benutzer und entfernt Lizenzen bei konfigurierten Schwellwerten (nur wenn Auto-Release aktiviert).',
                'default_interval' => 1440,
                'handler'          => function () use ($graph): string {
                    $config  = Config::getInstance();
                    if ($config->get('stale_auto_release_enabled', '0') !== '1') {
                        return 'Auto-Release deaktiviert — übersprungen';
                    }

                    $autoReleaseDays = (int)$config->get('stale_auto_release_days', '180');
                    $warnDaysBefore  = (int)$config->get('stale_warn_days_before', '14');
                    $alertEmail      = $config->get('alert_email_to', '');
                    $appName         = $config->get('app_name', 'M365 Tenant Tool');
                    $baseUrl         = rtrim($config->get('app_base_url', ''), '/');

                    $service    = new StaleAccountsService($graph);
                    $staleUsers = $service->getStaleUsers($autoReleaseDays);

                    $released = 0;
                    $warned   = 0;

                    foreach ($staleUsers as $user) {
                        $userId = $user['id'] ?? '';
                        $upn    = $user['userPrincipalName'] ?? '';
                        $name   = $user['displayName'] ?? $upn;
                        $days   = $user['daysInactive'];
                        if (!$userId || empty($user['assignedLicenses'])) continue;

                        $skuIds = array_column($user['assignedLicenses'], 'skuId');

                        if ($days !== null && $days >= $autoReleaseDays) {
                            try {
                                $service->removeLicenses($userId, $skuIds);
                                $service->logAction($userId, $upn, 'license_removed', [
                                    'skuIds' => $skuIds, 'daysInactive' => $days, 'trigger' => 'cron',
                                ]);
                                if ($alertEmail) {
                                    \App\Helpers\Mailer::send(
                                        $alertEmail,
                                        "Lizenz automatisch entzogen: {$name}",
                                        \App\Helpers\Mailer::alertTemplate(
                                            'Automatische Lizenzfreigabe',
                                            "<p>Dem Benutzer <strong>" . htmlspecialchars($name) . "</strong> (<code>" . htmlspecialchars($upn) . "</code>) wurden automatisch alle Lizenzen entzogen (inaktiv seit <strong>{$days} Tagen</strong>).</p><p><a href=\"{$baseUrl}/staleaccounts\">→ Inaktive Konten verwalten</a></p>",
                                            $appName
                                        )
                                    );
                                }
                                $released++;
                            } catch (\Throwable) {}
                        } elseif ($warnDaysBefore > 0 && $days !== null) {
                            $remaining = $autoReleaseDays - $days;
                            if ($remaining > 0 && $remaining <= $warnDaysBefore) {
                                $already = DB::fetchOne(
                                    "SELECT id FROM stale_account_log WHERE user_id = ? AND action = 'warn_sent'
                                     AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                                    [$userId, $warnDaysBefore]
                                );
                                if (!$already && $alertEmail) {
                                    \App\Helpers\Mailer::send(
                                        $alertEmail,
                                        "Vorwarnung: Lizenzfreigabe in {$remaining} Tagen — {$name}",
                                        \App\Helpers\Mailer::alertTemplate(
                                            'Bevorstehende Lizenzfreigabe',
                                            "<p>Dem Benutzer <strong>" . htmlspecialchars($name) . "</strong> werden in <strong>{$remaining} Tagen</strong> automatisch alle Lizenzen entzogen (inaktiv seit {$days} Tagen).</p><p><a href=\"{$baseUrl}/staleaccounts\">→ Inaktive Konten verwalten</a></p>",
                                            $appName
                                        )
                                    );
                                    $service->logAction($userId, $upn, 'warn_sent', ['remaining' => $remaining]);
                                    $warned++;
                                }
                            }
                        }
                    }
                    return "Lizenzen entzogen: {$released}, Warnungen: {$warned}";
                },
            ],

            'queue_prune' => [
                'label'            => 'Queue aufräumen',
                'description'      => 'Löscht abgeschlossene Jobs aus der Warteschlange, die älter als 24 Stunden sind.',
                'default_interval' => 1440,
                'handler'          => function (): string {
                    $n = \App\Queue\QueueDispatcher::pruneCompleted(24);
                    return "{$n} abgeschlossene Job(s) gelöscht";
                },
            ],

            'weekly_report' => [
                'label'            => 'Wöchentlicher E-Mail-Report',
                'description'      => 'Sendet einen wöchentlichen Zusammenfassungsbericht per E-Mail (konfigurierbar: Wochentag, Empfänger). Läuft täglich und prüft selbst, ob heute der richtige Tag ist.',
                'default_interval' => 1440,
                'handler'          => function () use ($graph): string {
                    $config = Config::getInstance();
                    if ($config->get('weekly_report_enabled', '0') !== '1') {
                        return 'Wöchentlicher Report deaktiviert — übersprungen';
                    }
                    $reportDay = (int)$config->get('weekly_report_day', '1');
                    if ((int)date('N') !== $reportDay) {
                        return 'Heute kein Report-Tag — übersprungen';
                    }
                    $service = new WeeklyReportService($graph);
                    return $service->generate();
                },
            ],
        ];
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }
}
