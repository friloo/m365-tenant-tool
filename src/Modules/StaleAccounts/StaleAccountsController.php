<?php

namespace App\Modules\StaleAccounts;

use App\Auth\LocalAuth;
use App\Core\Config;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;

class StaleAccountsController
{
    public function index(): void
    {
        LocalAuth::require();

        $config  = Config::getInstance();
        $default = (int)($config->get('stale_account_days') ?? 90);
        $days    = (int)($_GET['stale_days'] ?? $default);
        if ($days < 1) {
            $days = 90;
        }

        $service = app_service(StaleAccountsService::class);
        $users   = $service->getStaleUsers($days);
        $stats   = $service->getStats($users);
        $log     = $service->getLog(100);

        View::render('staleaccounts/index', [
            'pageTitle' => 'Inaktive Konten',
            'users'     => $users,
            'stats'     => $stats,
            'log'       => $log,
            'days'      => $days,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function removeLicense(string $userId): void
    {
        LocalAuth::requireAdmin();

        $service = app_service(StaleAccountsService::class);

        // Re-fetch current user to get their licenses and UPN
        try {
            $users = $service->getStaleUsers(1); // Very short threshold to find the user
            $target = null;
            foreach ($users as $u) {
                if ($u['id'] === $userId) {
                    $target = $u;
                    break;
                }
            }

            if (!$target) {
                // Try fetching from the broader set
                $users = $service->getStaleUsers(0);
                foreach ($users as $u) {
                    if ($u['id'] === $userId) {
                        $target = $u;
                        break;
                    }
                }
            }

            if (!$target || empty($target['assignedLicenses'])) {
                Session::flash('error', 'Benutzer nicht gefunden oder hat keine Lizenzen.');
                Redirect::to('/staleaccounts');
                return;
            }

            $skuIds = array_column($target['assignedLicenses'], 'skuId');
            $service->removeLicenses($userId, $skuIds);
            $service->logAction($userId, $target['userPrincipalName'] ?? '', 'license_removed', [
                'skuIds' => $skuIds,
                'count'  => count($skuIds),
            ]);

            Session::flash('success', 'Lizenzen für ' . ($target['displayName'] ?? $userId) . ' entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Entfernen der Lizenzen: ' . $e->getMessage());
        }

        Redirect::to('/staleaccounts');
    }

    public function export(): void
    {
        LocalAuth::require();

        $config  = Config::getInstance();
        $default = (int)($config->get('stale_account_days') ?? 90);
        $days    = (int)($_GET['stale_days'] ?? $default);
        if ($days < 1) {
            $days = 90;
        }

        $users = app_service(StaleAccountsService::class)->getStaleUsers($days);

        CsvExporter::download(
            'inaktive_konten_' . date('Ymd') . '.csv',
            ['Name', 'UPN', 'Abteilung', 'Position', 'Tage inaktiv', 'Letzter Login', 'Lizenzen'],
            array_map(fn($u) => [
                $u['displayName'] ?? '',
                $u['userPrincipalName'] ?? '',
                $u['department'] ?? '',
                $u['jobTitle'] ?? '',
                ($u['neverSignedIn'] ?? false) ? 'Nie' : ($u['daysInactive'] ?? ''),
                CsvExporter::formatDate($u['signInActivity']['lastSignInDateTime'] ?? ''),
                count($u['assignedLicenses'] ?? []),
            ], $users)
        );
    }
}
