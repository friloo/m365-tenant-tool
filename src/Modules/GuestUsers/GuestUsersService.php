<?php

namespace App\Modules\GuestUsers;

use App\Graph\GraphClient;

class GuestUsersService
{
    public function __construct(private GraphClient $graph) {}

    public function getAll(): array
    {
        return $this->graph->paginate(
            '/users',
            [
                '$select' => 'id,displayName,userPrincipalName,mail,accountEnabled,signInActivity,createdDateTime,externalUserState,externalUserStateChangeDateTime,userType',
                '$filter' => "userType eq 'Guest'",
                '$top'    => '999',
            ],
            30,
            'guests_all',
            900
        );
    }

    public function disableGuest(string $userId): void
    {
        $this->graph->patch("/users/{$userId}", ['accountEnabled' => false]);
        $this->graph->getCache()->forget('guests_all');
    }

    public function removeGuest(string $userId): void
    {
        $this->graph->delete("/users/{$userId}");
        $this->graph->getCache()->forget('guests_all');
    }

    public function getStats(array $guests): array
    {
        $now = time();
        $stats = ['total' => count($guests), 'active' => 0, 'inactive_90d' => 0, 'never_signed_in' => 0, 'pending' => 0];
        foreach ($guests as $g) {
            if (!($g['accountEnabled'] ?? true)) continue;
            $stats['active']++;
            $lastSignIn = $g['signInActivity']['lastSignInDateTime'] ?? null;
            if (!$lastSignIn) {
                $stats['never_signed_in']++;
            } elseif (($now - strtotime($lastSignIn)) > 90 * 86400) {
                $stats['inactive_90d']++;
            }
            if (($g['externalUserState'] ?? '') === 'PendingAcceptance') {
                $stats['pending']++;
            }
        }
        return $stats;
    }
}
