<?php

namespace App\Modules\Settings;

use App\Graph\GraphClient;

/**
 * Checks the expiry of the tool's OWN Microsoft Entra app-registration
 * credential (client secret / certificate). If it lapses the tool loses all
 * Graph access, so we surface it proactively (cron → notification).
 */
class AppCredentialService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Resolve the soonest-expiring credential of the configured app.
     *
     * @return array{found:bool,app_id:?string,type:?string,expires_at:?string,days_left:?int}
     */
    public function check(): array
    {
        $none = ['found' => false, 'app_id' => null, 'type' => null, 'expires_at' => null, 'days_left' => null];

        // The app id the current token was issued for.
        $appId = (new PermissionCheckerService($this->graph))->getTokenInfo()['app_id'] ?? null;
        if (!$appId || $appId === '–') {
            return $none;
        }

        try {
            $res = $this->graph->get(
                '/applications',
                ['$filter' => "appId eq '{$appId}'", '$select' => 'id,displayName,passwordCredentials,keyCredentials'],
                'self_app_creds',
                3600
            );
        } catch (\Throwable) {
            return $none;
        }

        $app = $res['value'][0] ?? null;
        if (!$app) {
            return ['found' => true, 'app_id' => $appId, 'type' => null, 'expires_at' => null, 'days_left' => null];
        }

        $soonest = null;
        $soonestType = null;
        foreach ([['passwordCredentials', 'secret'], ['keyCredentials', 'certificate']] as [$field, $label]) {
            foreach ($app[$field] ?? [] as $cred) {
                $end = $cred['endDateTime'] ?? null;
                if (!$end) continue;
                $ts = strtotime($end);
                if ($ts === false) continue;
                if ($soonest === null || $ts < $soonest) {
                    $soonest     = $ts;
                    $soonestType = $label;
                }
            }
        }

        if ($soonest === null) {
            return ['found' => true, 'app_id' => $appId, 'type' => null, 'expires_at' => null, 'days_left' => null];
        }

        $daysLeft = (int)floor(($soonest - time()) / 86400);
        return [
            'found'      => true,
            'app_id'     => $appId,
            'type'       => $soonestType,
            'expires_at' => date('c', $soonest),
            'days_left'  => $daysLeft,
        ];
    }
}
