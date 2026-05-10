<?php

namespace App\Modules\Mailboxes;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Helpers\CsvExporter;

class MailboxController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(MailboxService::class);
        $usage   = $service->getUsageSummary();
        $stats   = $service->getStats($usage);

        // Sort by storage descending for the default table view
        usort($usage, fn($a, $b) => $b['storageUsedBytes'] <=> $a['storageUsedBytes']);

        View::render('mailboxes/index', [
            'pageTitle' => 'Postfächer',
            'usage'     => $usage,
            'stats'     => $stats,
        ]);
    }

    public function export(): void
    {
        LocalAuth::require();

        $service = app_service(MailboxService::class);
        $usage   = $service->getUsageSummary();

        CsvExporter::download('postfaecher_' . date('Ymd') . '.csv',
            ['Anzeigename', 'UPN', 'Größe (MB)', 'Elemente', 'Gel. Elemente', 'Gel. Größe (MB)', 'Gelöscht'],
            array_map(fn($u) => [
                $u['displayName'],
                $u['upn'],
                number_format($u['storageUsedBytes'] / (1024 ** 2), 2, '.', ''),
                $u['itemCount'],
                $u['deletedItemCount'],
                number_format($u['deletedItemSizeBytes'] / (1024 ** 2), 2, '.', ''),
                $u['isDeleted'] ? 'Ja' : 'Nein',
            ], $usage)
        );
    }
}
