<?php

namespace App\Modules\Mailboxes;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
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

    public function show(string $id): void
    {
        LocalAuth::require();

        $service = app_service(MailboxService::class);
        $detail  = $service->getMailboxDetail($id);
        $folders = $service->getMailFolders($id);

        View::render('mailboxes/detail', [
            'pageTitle' => $detail['displayName'] ?? 'Postfach',
            'detail'    => $detail,
            'folders'   => $folders,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function setForwarding(string $id): void
    {
        LocalAuth::require();

        $service   = app_service(MailboxService::class);
        $forwardTo = trim($_POST['forward_to'] ?? '');

        try {
            if ($forwardTo === '') {
                $service->removeForwarding($id);
                Session::flash('success', 'Weiterleitung wurde entfernt.');
            } else {
                $service->setForwarding($id, $forwardTo);
                Session::flash('success', 'Weiterleitung gesetzt auf: ' . $forwardTo);
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Authorization') || str_contains($msg, 'Forbidden') || str_contains($msg, '403') || str_contains($msg, 'permission') || str_contains($msg, 'Permission')) {
                Session::flash('error', 'Fehlende Berechtigung: MailboxSettings.ReadWrite ist in der Azure App nicht erteilt.');
            } else {
                Session::flash('error', 'Fehler beim Speichern der Weiterleitung: ' . $msg);
            }
        }

        Redirect::to('/mailboxes/' . $id);
    }

    public function setAutoReply(string $id): void
    {
        LocalAuth::require();

        $service = app_service(MailboxService::class);
        $enabled = isset($_POST['auto_reply_enabled']);
        $message = trim($_POST['auto_reply_message'] ?? '');

        try {
            $service->setAutoReply($id, $message, $enabled);
            Session::flash('success', $enabled ? 'Abwesenheitsnotiz aktiviert.' : 'Abwesenheitsnotiz deaktiviert.');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Authorization') || str_contains($msg, 'Forbidden') || str_contains($msg, '403') || str_contains($msg, 'permission') || str_contains($msg, 'Permission')) {
                Session::flash('error', 'Fehlende Berechtigung: MailboxSettings.ReadWrite ist in der Azure App nicht erteilt.');
            } else {
                Session::flash('error', 'Fehler beim Speichern der Abwesenheitsnotiz: ' . $msg);
            }
        }

        Redirect::to('/mailboxes/' . $id);
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
