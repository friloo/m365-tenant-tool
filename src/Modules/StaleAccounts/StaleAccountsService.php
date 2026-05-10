<?php

namespace App\Modules\StaleAccounts;

use App\Database\DB;
use App\Graph\GraphClient;

class StaleAccountsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch enabled users and filter those whose last sign-in is older than $days,
     * or who have never signed in. Adds a computed `daysInactive` field.
     * Cached 30 minutes.
     */
    public function getStaleUsers(int $days = 90): array
    {
        try {
            $all = $this->graph->paginate(
                '/users',
                [
                    '$select' => 'id,displayName,userPrincipalName,accountEnabled,assignedLicenses,signInActivity,department,jobTitle',
                    '$filter' => 'accountEnabled eq true',
                    '$top'    => '999',
                ],
                10,
                'stale_users_enabled',
                1800
            );
        } catch (\Throwable) {
            return [];
        }

        $cutoff = strtotime("-{$days} days");
        $stale  = [];

        foreach ($all as $user) {
            $lastSignIn = $user['signInActivity']['lastSignInDateTime'] ?? null;

            if ($lastSignIn === null) {
                // Never signed in
                $daysInactive = null; // Represents "never"
                $stale[] = array_merge($user, ['daysInactive' => $daysInactive, 'neverSignedIn' => true]);
            } elseif (strtotime($lastSignIn) < $cutoff) {
                $daysInactive = (int)floor((time() - strtotime($lastSignIn)) / 86400);
                $stale[] = array_merge($user, ['daysInactive' => $daysInactive, 'neverSignedIn' => false]);
            }
        }

        // Sort by daysInactive descending (never-signed-in last, then highest days first)
        usort($stale, function ($a, $b) {
            $aVal = $a['neverSignedIn'] ? PHP_INT_MAX : ($a['daysInactive'] ?? 0);
            $bVal = $b['neverSignedIn'] ? PHP_INT_MAX : ($b['daysInactive'] ?? 0);
            return $bVal <=> $aVal;
        });

        return $stale;
    }

    /**
     * Compute summary stats from a stale users array.
     */
    public function getStats(array $users): array
    {
        $total         = count($users);
        $withLicenses  = 0;
        $noLicenses    = 0;
        $neverSignedIn = 0;

        foreach ($users as $u) {
            $hasLicense = !empty($u['assignedLicenses']);
            if ($hasLicense) {
                $withLicenses++;
            } else {
                $noLicenses++;
            }
            if ($u['neverSignedIn'] ?? false) {
                $neverSignedIn++;
            }
        }

        return [
            'total'         => $total,
            'withLicenses'  => $withLicenses,
            'noLicenses'    => $noLicenses,
            'neverSignedIn' => $neverSignedIn,
            'costRisk'      => $withLicenses, // licensed stale = wasted money
        ];
    }

    /**
     * Remove all assigned licenses from a user.
     */
    public function removeLicenses(string $userId, array $skuIds): void
    {
        $this->graph->patch("/users/{$userId}/assignLicense", [
            'addLicenses'    => [],
            'removeLicenses' => $skuIds,
        ]);
    }

    /**
     * Log an action taken on a stale account.
     */
    public function logAction(string $userId, string $upn, string $action, array $details): void
    {
        DB::execute(
            'INSERT INTO stale_account_log (user_id, user_upn, action, details) VALUES (?, ?, ?, ?)',
            [$userId, $upn, $action, json_encode($details)]
        );
    }

    /**
     * Retrieve recent log entries.
     */
    public function getLog(int $limit = 100): array
    {
        try {
            return DB::fetchAll(
                'SELECT * FROM stale_account_log ORDER BY created_at DESC LIMIT ?',
                [$limit]
            );
        } catch (\Throwable) {
            return [];
        }
    }
}
