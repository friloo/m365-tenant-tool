<?php

namespace App\Modules\PasswordExpiry;

use App\Graph\GraphClient;

class PasswordExpiryService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch all users with the fields needed for password-expiry analysis.
     *
     * @return array<int, array>
     */
    public function getAll(): array
    {
        try {
            return $this->graph->paginate(
                '/users',
                [
                    '$select' => 'id,displayName,userPrincipalName,accountEnabled,passwordPolicies,lastPasswordChangeDateTime,signInActivity,onPremisesSyncEnabled',
                    '$top'    => '999',
                ],
                50,
                'password_expiry_users',
                3600
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Categorize enabled users by password-expiry status.
     *
     * A password never expires when:
     *   - passwordPolicies contains "DisablePasswordExpiration", OR
     *   - lastPasswordChangeDateTime is null
     *
     * For expirable passwords:
     *   - daysUntilExpiry = $expiryDays - daysSinceLastChange
     *   - expired  : daysUntilExpiry < 0
     *   - critical : 0 ≤ daysUntilExpiry ≤ 14
     *   - warning  : 15 ≤ daysUntilExpiry ≤ 30
     *   - ok       : daysUntilExpiry > 30
     *
     * @param  array $users       Raw users from getAll()
     * @param  int   $expiryDays  Configured expiry window (default 90)
     * @return array{expired: array, critical: array, warning: array, ok: array, never: array}
     */
    public function analyzeUsers(array $users, int $expiryDays): array
    {
        $buckets = [
            'expired'  => [],
            'critical' => [],
            'warning'  => [],
            'ok'       => [],
            'never'    => [],
        ];

        $now = time();

        foreach ($users as $user) {
            // Skip disabled accounts
            if (!($user['accountEnabled'] ?? true)) {
                continue;
            }

            $policies  = (string)($user['passwordPolicies'] ?? '');
            $lastChange = $user['lastPasswordChangeDateTime'] ?? null;

            // Determine if password never expires
            $neverExpires = str_contains($policies, 'DisablePasswordExpiration')
                            || $lastChange === null;

            if ($neverExpires) {
                $buckets['never'][] = array_merge($user, [
                    'daysUntilExpiry' => null,
                    'expiresAt'       => null,
                    'daysSinceChange' => null,
                    'isHybrid'        => (bool)($user['onPremisesSyncEnabled'] ?? false),
                ]);
                continue;
            }

            $daysSinceChange  = (int)floor(($now - strtotime($lastChange)) / 86400);
            $daysUntilExpiry  = $expiryDays - $daysSinceChange;
            $expiresAt        = date('Y-m-d', strtotime($lastChange) + ($expiryDays * 86400));

            $enriched = array_merge($user, [
                'daysUntilExpiry' => $daysUntilExpiry,
                'expiresAt'       => $expiresAt,
                'daysSinceChange' => $daysSinceChange,
                'isHybrid'        => (bool)($user['onPremisesSyncEnabled'] ?? false),
            ]);

            if ($daysUntilExpiry < 0) {
                $buckets['expired'][] = $enriched;
            } elseif ($daysUntilExpiry <= 14) {
                $buckets['critical'][] = $enriched;
            } elseif ($daysUntilExpiry <= 30) {
                $buckets['warning'][] = $enriched;
            } else {
                $buckets['ok'][] = $enriched;
            }
        }

        // Sort expired/critical/warning by daysUntilExpiry ascending (most urgent first)
        foreach (['expired', 'critical', 'warning'] as $bucket) {
            usort($buckets[$bucket], fn($a, $b) => ($a['daysUntilExpiry'] ?? 0) <=> ($b['daysUntilExpiry'] ?? 0));
        }

        return $buckets;
    }
}
