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
use App\Modules\ExecutiveReport\ExecutiveReportService;
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
            return ['status' => 'error', 'log' => t('Unbekannter Job: :jobKey', ['jobKey' => $jobKey]), 'seconds' => 0];
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
                'label'            => t('Job-Queue verarbeiten'),
                'description'      => t('Verarbeitet ausstehende Aufgaben aus der Warteschlange (Lizenzänderungen, Bulk-Aktionen). Läuft jede Minute.'),
                'default_interval' => 1,
                'handler'          => function () use ($graph): string {
                    $worker = new QueueWorker($graph);
                    $n = $worker->processNext(20);
                    return $n > 0 ? t(':n Job(s) verarbeitet', ['n' => $n]) : t('Keine ausstehenden Jobs');
                },
            ],

            'cache_warm' => [
                'label'            => t('Cache vorwärmen (alle Module)'),
                'description'      => t('Ruft alle Graph-API-Endpunkte im Hintergrund ab und füllt den DB-Cache. Seiten laden danach sofort aus der DB ohne API-Wartezeit.'),
                'default_interval' => 5,
                'handler'          => function () use ($graph): string {
                    $results = [];
                    $ok = 0;
                    $fail = 0;

                    $jobs = [
                        t('Dashboard — Metriken')       => fn() => (new DashboardService($graph))->getMetrics(),
                        t('Dashboard — Lizenzübersicht')=> fn() => (new DashboardService($graph))->getLicenseSummary(),
                        t('Dashboard — Sicherheit')     => fn() => (new DashboardService($graph))->getSecurityStatus(),
                        t('Dashboard — Erweitert')      => fn() => (new DashboardService($graph))->getExtendedStats(),
                        t('Benutzer — Gesamtliste')     => fn() => (new UsersService($graph))->getAll(),
                        t('Benutzer — MFA-Status')      => fn() => (new UsersService($graph))->getMfaStatus(),
                        t('MFA-Methoden')               => fn() => (new MfaMethodsService($graph))->getAll(),
                        'Conditional Access'            => fn() => (new ConditionalAccessService($graph))->getPolicies(),
                        'Named Locations'               => fn() => (new NamedLocationsService($graph))->getAll(),
                        t('Geräte')                     => fn() => (new DevicesService($graph))->getAll(),
                        t('Gruppen')                    => fn() => (new GroupsService($graph))->getAll(),
                        t('Lizenzen')                   => fn() => (new LicensesService($graph))->getSkus(),
                        t('Dienststatus')               => fn() => (new ServiceHealthService($graph))->getOverview(),
                        'Security Posture'              => fn() => (new SecurityPostureService($graph))->runChecks(),
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
                        $msg .= ', ' . t('Fehler') . ": {$fail} — " . implode('; ', array_slice($results, 0, 3));
                    }
                    return $msg;
                },
            ],

            'share_scan' => [
                'label'            => t('Freigaben scannen'),
                'description'      => t('Synchronisiert externe SharePoint-Freigaben aus der Graph API und legt neue Einträge in der Datenbank an.'),
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $service = new ShareReviewService($graph);
                    $result  = $service->scanAndSync();
                    $found   = count($result['found'] ?? $result);
                    $new     = count($result['new'] ?? []);
                    return t('Gefunden: :found, Neu: :new', ['found' => $found, 'new' => $new]);
                },
            ],

            'share_emails' => [
                'label'            => t('Review-E-Mails senden'),
                'description'      => t('Sendet Bestätigungs-E-Mails an Freigabe-Besitzer, deren Prüfintervall abgelaufen ist.'),
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $service = new ShareReviewService($graph);
                    $n = count($service->sendDueReviewEmails());
                    return t(':n E-Mail(s) gesendet', ['n' => $n]);
                },
            ],

            'share_auto_revoke' => [
                'label'            => t('Freigaben automatisch widerrufen'),
                'description'      => t('Widerruft Freigaben, für die kein Besitzer innerhalb der Toleranzzeit reagiert hat.'),
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $service = new ShareReviewService($graph);
                    $n = count($service->autoRevokeOverdue());
                    return t(':n Freigabe(n) widerrufen', ['n' => $n]);
                },
            ],

            'stale_cleanup' => [
                'label'            => t('Inaktive Konten bereinigen'),
                'description'      => t('Prüft inaktive Benutzer und entfernt Lizenzen bei konfigurierten Schwellwerten (nur wenn Auto-Release aktiviert).'),
                'default_interval' => 1440,
                'handler'          => function () use ($graph): string {
                    $config  = Config::getInstance();
                    if ($config->get('stale_auto_release_enabled', '0') !== '1') {
                        return t('Auto-Release deaktiviert — übersprungen');
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
                                        t('Lizenz automatisch entzogen: :name', ['name' => $name]),
                                        \App\Helpers\Mailer::alertTemplate(
                                            t('Automatische Lizenzfreigabe'),
                                            t('<p>Dem Benutzer <strong>:name</strong> (<code>:upn</code>) wurden automatisch alle Lizenzen entzogen (inaktiv seit <strong>:days Tagen</strong>).</p><p><a href=":url">→ Inaktive Konten verwalten</a></p>', ['name' => htmlspecialchars($name), 'upn' => htmlspecialchars($upn), 'days' => $days, 'url' => "{$baseUrl}/staleaccounts"]),
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
                                        t('Vorwarnung: Lizenzfreigabe in :remaining Tagen — :name', ['remaining' => $remaining, 'name' => $name]),
                                        \App\Helpers\Mailer::alertTemplate(
                                            t('Bevorstehende Lizenzfreigabe'),
                                            t('<p>Dem Benutzer <strong>:name</strong> werden in <strong>:remaining Tagen</strong> automatisch alle Lizenzen entzogen (inaktiv seit :days Tagen).</p><p><a href=":url">→ Inaktive Konten verwalten</a></p>', ['name' => htmlspecialchars($name), 'remaining' => $remaining, 'days' => $days, 'url' => "{$baseUrl}/staleaccounts"]),
                                            $appName
                                        )
                                    );
                                    $service->logAction($userId, $upn, 'warn_sent', ['remaining' => $remaining]);
                                    $warned++;
                                }
                            }
                        }
                    }
                    return t('Lizenzen entzogen: :released, Warnungen: :warned', ['released' => $released, 'warned' => $warned]);
                },
            ],

            'queue_prune' => [
                'label'            => t('Queue aufräumen'),
                'description'      => t('Löscht abgeschlossene Jobs aus der Warteschlange, die älter als 24 Stunden sind.'),
                'default_interval' => 1440,
                'handler'          => function (): string {
                    $n = \App\Queue\QueueDispatcher::pruneCompleted(24);
                    return t(':n abgeschlossene Job(s) gelöscht', ['n' => $n]);
                },
            ],

            'weekly_report' => [
                'label'            => t('Wöchentlicher E-Mail-Report'),
                'description'      => t('Sendet einen wöchentlichen Zusammenfassungsbericht per E-Mail (konfigurierbar: Wochentag, Empfänger). Läuft täglich und prüft selbst, ob heute der richtige Tag ist.'),
                'default_interval' => 1440,
                'handler'          => function () use ($graph): string {
                    $config = Config::getInstance();
                    if ($config->get('weekly_report_enabled', '0') !== '1') {
                        return t('Wöchentlicher Report deaktiviert — übersprungen');
                    }
                    $reportDay = (int)$config->get('weekly_report_day', '1');
                    if ((int)date('N') !== $reportDay) {
                        return t('Heute kein Report-Tag — übersprungen');
                    }
                    $service = new WeeklyReportService($graph);
                    return $service->generate();
                },
            ],

            'executive_report' => [
                'label'            => t('Monatlicher Executive-Report'),
                'description'      => t('Versendet am ersten Tag jedes Monats den Executive-Report an die Geschäftsführung. Läuft täglich und prüft selbst, ob heute der 1. ist.'),
                'default_interval' => 1440,
                'handler'          => function () use ($graph): string {
                    if (Config::getInstance()->get('executive_report_enabled', '0') !== '1') {
                        return t('Executive-Report deaktiviert — übersprungen');
                    }
                    if ((int)date('j') !== 1) {
                        return t('Heute nicht der 1. des Monats — übersprungen');
                    }
                    return (new ExecutiveReportService($graph))->generate();
                },
            ],

            'alert_new_defender' => [
                'label'            => t('Alert: Neue Defender-Warnungen'),
                'description'      => t('Sendet E-Mail wenn neue ungelöste Defender Alerts seit dem letzten Check aufgetreten sind.'),
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $config  = Config::getInstance();
                    $to      = $config->get('alert_email_to', '');
                    if (!$to) {
                        return t('Kein Empfänger konfiguriert');
                    }

                    $defaultLastCheck = (new \DateTimeImmutable('-24 hours'))->format('c');
                    $lastCheck        = $config->get('alert_defender_last_check', $defaultLastCheck);
                    $appName          = $config->get('app_name', 'M365 Tenant Tool');

                    try {
                        $data = $graph->get(
                            '/security/alerts_v2',
                            [
                                '$filter' => "status eq 'new' and createdDateTime gt '{$lastCheck}'",
                                '$top'    => 50,
                                '$select' => 'id,title,severity,createdDateTime,alertWebUrl',
                            ],
                            null,
                            0
                        );
                    } catch (\Throwable $e) {
                        if (str_contains($e->getMessage(), '403')) {
                            return t('Defender-API nicht verfügbar (403 — fehlende Lizenz oder Berechtigung)');
                        }
                        throw $e;
                    }

                    $alerts = $data['value'] ?? [];
                    $config->set('alert_defender_last_check', (new \DateTimeImmutable())->format('c'));

                    if (empty($alerts)) {
                        return t('Keine neuen Alerts');
                    }

                    $rows = '';
                    foreach ($alerts as $alert) {
                        $title    = htmlspecialchars($alert['title'] ?? '—');
                        $severity = htmlspecialchars($alert['severity'] ?? '—');
                        $created  = htmlspecialchars($alert['createdDateTime'] ?? '—');
                        $url      = htmlspecialchars($alert['alertWebUrl'] ?? '#');
                        $rows .= "<tr><td>{$title}</td><td>{$severity}</td><td>{$created}</td>"
                               . '<td><a href="' . $url . '">' . t('Link') . '</a></td></tr>';
                    }

                    $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">'
                          . '<thead><tr><th>' . t('Titel') . '</th><th>' . t('Schweregrad') . '</th><th>' . t('Erstellt') . '</th><th>' . t('Details') . '</th></tr></thead>'
                          . "<tbody>{$rows}</tbody></table>";

                    $count = count($alerts);
                    \App\Helpers\Mailer::send(
                        $to,
                        t('Defender Alert: :count neue Warnung(en)', ['count' => $count]),
                        \App\Helpers\Mailer::alertTemplate(
                            t('Neue Defender-Warnungen (:count)', ['count' => $count]),
                            t('<p>Es wurden <strong>:count</strong> neue Defender-Alert(s) gefunden:</p>:html', ['count' => $count, 'html' => $html]),
                            $appName
                        )
                    );
                    \App\Modules\Notifications\NotificationService::push(
                        t(':count neue Defender-Warnungen', ['count' => $count]),
                        t('Bitte unter /defenderalerts prüfen und bewerten.'),
                        'critical', '/defenderalerts', 'defender',
                        'defender_' . date('YmdH')
                    );

                    return t(':count neue Alerts — E-Mail gesendet', ['count' => $count]);
                },
            ],

            'alert_service_incidents' => [
                'label'            => t('Alert: Dienststörungen'),
                'description'      => t('Sendet E-Mail wenn Microsoft-Dienste mit Störungen gemeldet werden.'),
                'default_interval' => 30,
                'handler'          => function () use ($graph): string {
                    $config  = Config::getInstance();
                    $to      = $config->get('alert_email_to', '');
                    if (!$to) {
                        return t('Kein Empfänger konfiguriert');
                    }

                    $appName      = $config->get('app_name', 'M365 Tenant Tool');
                    $knownRaw     = $config->get('alert_service_known_incidents', '[]');
                    $knownIds     = json_decode($knownRaw, true) ?? [];

                    $data = $graph->get(
                        '/admin/serviceAnnouncement/issues',
                        [
                            '$filter' => "status ne 'resolved'",
                            '$select' => 'id,title,service,status,startDateTime',
                            '$top'    => 50,
                        ],
                        null,
                        0
                    );

                    $incidents  = $data['value'] ?? [];
                    $currentIds = array_column($incidents, 'id');
                    $newOnes    = array_filter(
                        $incidents,
                        fn(array $i) => !in_array($i['id'], $knownIds, true)
                    );

                    $config->set('alert_service_known_incidents', json_encode($currentIds));

                    if (empty($newOnes)) {
                        return t('Keine neuen Dienststörungen');
                    }

                    $rows = '';
                    foreach ($newOnes as $inc) {
                        $service = htmlspecialchars($inc['service'] ?? '—');
                        $status  = htmlspecialchars($inc['status'] ?? '—');
                        $title   = htmlspecialchars($inc['title'] ?? '—');
                        $start   = htmlspecialchars($inc['startDateTime'] ?? '—');
                        $rows .= "<tr><td>{$service}</td><td>{$status}</td><td>{$title}</td><td>{$start}</td></tr>";
                    }

                    $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">'
                          . '<thead><tr><th>' . t('Dienst') . '</th><th>' . t('Status') . '</th><th>' . t('Titel') . '</th><th>' . t('Beginn') . '</th></tr></thead>'
                          . "<tbody>{$rows}</tbody></table>";

                    $count = count($newOnes);
                    \App\Helpers\Mailer::send(
                        $to,
                        t('Dienststörung: :count neue Incident(s)', ['count' => $count]),
                        \App\Helpers\Mailer::alertTemplate(
                            t('Neue Dienststörungen (:count)', ['count' => $count]),
                            t('<p>Es wurden <strong>:count</strong> neue Dienststörung(en) erkannt:</p>:html', ['count' => $count, 'html' => $html]),
                            $appName
                        )
                    );

                    return t(':count neue Incidents — E-Mail gesendet', ['count' => $count]);
                },
            ],

            'alert_new_risky_users' => [
                'label'            => t('Alert: Neue Risiko-Benutzer'),
                'description'      => t('Sendet E-Mail wenn neue Benutzer einen aktiven Risikostatus erhalten haben.'),
                'default_interval' => 60,
                'handler'          => function () use ($graph): string {
                    $config  = Config::getInstance();
                    $to      = $config->get('alert_email_to', '');
                    if (!$to) {
                        return t('Kein Empfänger konfiguriert');
                    }

                    $defaultLastCheck = (new \DateTimeImmutable('-24 hours'))->format('c');
                    $lastCheck        = $config->get('alert_risky_last_check', $defaultLastCheck);
                    $appName          = $config->get('app_name', 'M365 Tenant Tool');

                    $data = $graph->getEventual(
                        '/identityProtection/riskyUsers',
                        [
                            '$filter' => "riskState eq 'atRisk' and riskLastUpdatedDateTime gt '{$lastCheck}'",
                            '$select' => 'userPrincipalName,riskLevel,riskState,riskLastUpdatedDateTime',
                            '$top'    => 50,
                        ],
                        null,
                        0
                    );

                    $users = $data['value'] ?? [];
                    $config->set('alert_risky_last_check', (new \DateTimeImmutable())->format('c'));

                    if (empty($users)) {
                        return t('Keine neuen Risiko-Benutzer');
                    }

                    $rows = '';
                    foreach ($users as $user) {
                        $upn     = htmlspecialchars($user['userPrincipalName'] ?? '—');
                        $level   = htmlspecialchars($user['riskLevel'] ?? '—');
                        $state   = htmlspecialchars($user['riskState'] ?? '—');
                        $updated = htmlspecialchars($user['riskLastUpdatedDateTime'] ?? '—');
                        $rows .= "<tr><td>{$upn}</td><td>{$level}</td><td>{$state}</td><td>{$updated}</td></tr>";
                    }

                    $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">'
                          . '<thead><tr><th>UPN</th><th>' . t('Risikostufe') . '</th><th>' . t('Status') . '</th><th>' . t('Aktualisiert') . '</th></tr></thead>'
                          . "<tbody>{$rows}</tbody></table>";

                    $count = count($users);
                    \App\Helpers\Mailer::send(
                        $to,
                        t('Risiko-Benutzer: :count neue(r) Benutzer', ['count' => $count]),
                        \App\Helpers\Mailer::alertTemplate(
                            t('Neue Risiko-Benutzer (:count)', ['count' => $count]),
                            t('<p>Es wurden <strong>:count</strong> neue Risiko-Benutzer erkannt:</p>:html', ['count' => $count, 'html' => $html]),
                            $appName
                        )
                    );

                    return t(':count neue Risiko-Benutzer — E-Mail gesendet', ['count' => $count]);
                },
            ],

            'audit_diff_snapshot' => [
                'label'            => t('Audit-Diff-Snapshot'),
                'description'      => t('Tenant-Snapshot für Audit-Diff erstellen'),
                'default_interval' => 1440, // daily
                'handler'          => function () use ($graph): string {
                    $svc = new \App\Modules\AuditDiff\SnapshotService($graph);
                    $id  = $svc->capture('daily');
                    \App\Modules\AuditDiff\SnapshotService::trim(365, 365);
                    return t('Snapshot #:id erstellt', ['id' => $id]);
                },
            ],

            'notification_trim' => [
                'label'            => t('Benachrichtigungen aufräumen'),
                'description'      => t('Alte In-App-Benachrichtigungen aufräumen'),
                'default_interval' => 1440,
                'handler'          => function (): string {
                    $deleted = \App\Modules\Notifications\NotificationService::trim(500, 90);
                    return t(':deleted alte Benachrichtigungen entfernt', ['deleted' => $deleted]);
                },
            ],

            'workflow_runner' => [
                'label'            => t('Workflow-Runner'),
                'description'      => t('Geplante Workflow-Automatisierungen ausführen'),
                'default_interval' => 15,
                'handler'          => function () use ($graph): string {
                    $svc = new \App\Modules\Workflows\WorkflowService($graph);
                    $r   = $svc->runDue();
                    return t('Ausgeführt: :ran Workflows · :actions Aktionen', ['ran' => $r['ran'], 'actions' => $r['actions']]);
                },
            ],
        ];
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }
}
