<?php

namespace App\Modules\ActionCenter;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\View;
use App\Modules\SecurityPosture\SecurityPostureService;

/**
 * One "start here" surface that turns tenant configuration into a prioritized
 * to-do list: an overall score, a short setup-completeness checklist, and the
 * highest-impact next actions (each deep-linking to the module that fixes it).
 *
 * It reuses the existing SecurityPosture engine — no new Graph permissions.
 */
class ActionCenterController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->flush();
        }

        $score = ['percent' => null, 'passed' => 0, 'warned' => 0, 'failed' => 0, 'unknown' => 0, 'total' => 0];
        $recommendations = [];
        $postureError = false;

        try {
            $service         = app_service(SecurityPostureService::class);
            $checks          = $service->runChecks();
            $score           = $service->getScore($checks);
            $recommendations = $service->getRecommendations($checks);
        } catch (\Throwable) {
            $postureError = true;
        }

        View::render('actioncenter/index', [
            'pageTitle'       => t('Konfigurations-Center'),
            'score'           => $score,
            'recommendations' => $recommendations,
            'postureError'    => $postureError,
            'setup'           => $this->setupChecklist(),
        ]);
    }

    /**
     * Lightweight, config-only "is the tool itself set up" checklist — no Graph
     * calls, so it always renders even before credentials are configured.
     *
     * @return list<array{label:string,done:bool,url:string,hint:string}>
     */
    private function setupChecklist(): array
    {
        $c = Config::getInstance();
        $isAdmin = LocalAuth::isAdmin();

        $items = [
            [
                'label' => t('Microsoft-365-Verbindung konfiguriert'),
                'done'  => $c->get('tenant_id') && $c->get('client_id') && $c->get('client_secret'),
                'url'   => '/settings',
                'hint'  => t('Tenant-ID, Client-ID und Client-Secret hinterlegen.'),
            ],
            [
                'label' => t('Einrichtungs-Assistent abgeschlossen'),
                'done'  => (string)$c->get('setup_wizard_completed', '0') === '1',
                'url'   => '/setup',
                'hint'  => t('Geführte Erst-Einrichtung durchlaufen.'),
            ],
            [
                'label' => t('Compliance-Profil ausgewählt'),
                'done'  => (string)$c->get('compliance_profile', '') !== '',
                'url'   => '/complianceprofile',
                'hint'  => t('Branchen-Härtungs-Defaults mit einem Klick anwenden.'),
            ],
            [
                'label' => t('Alarm-E-Mail hinterlegt'),
                'done'  => (string)$c->get('alert_email_to', '') !== '',
                'url'   => '/settings#benachrichtigungen',
                'hint'  => t('Empfänger für Sicherheits-Warnungen festlegen.'),
            ],
            [
                'label' => t('Backup-Status dokumentiert'),
                'done'  => (string)$c->get('backup_provider', '') !== '',
                'url'   => '/backup',
                'hint'  => t('Backup-Lösung für M365-Daten hinterlegen.'),
            ],
            [
                'label' => t('Drift-Baseline gesetzt'),
                'done'  => (int)$c->get('drift_baseline_snapshot_id', 0) > 0,
                'url'   => '/auditdiff',
                'hint'  => t('Bekannten, sicheren Stand als Baseline festlegen.'),
            ],
        ];

        // 2FA for the local admin (admin-only relevance).
        if ($isAdmin) {
            $items[] = [
                'label' => t('2FA für Admin-Login aktiv'),
                'done'  => (string)$c->get('admin_totp_secret', '') !== '',
                'url'   => '/settings/2fa',
                'hint'  => t('Admin-Konto mit TOTP absichern.'),
            ];
        }

        return $items;
    }
}
