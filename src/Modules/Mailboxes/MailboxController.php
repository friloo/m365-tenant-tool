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
        ['data' => $usage, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getUsageSummary(),
            'Reports.Read.All'
        );
        $usage ??= [];
        $stats = $service->getStats($usage);

        usort($usage, fn($a, $b) => $b['storageUsedBytes'] <=> $a['storageUsedBytes']);

        View::render('mailboxes/index', [
            'pageTitle' => 'Postfächer',
            'usage'     => $usage,
            'stats'     => $stats,
            'diag'      => $diag,
        ]);
    }

    public function show(string $id): void
    {
        LocalAuth::require();

        $service             = app_service(MailboxService::class);
        $detail              = $service->getMailboxDetail($id);
        $folders             = $service->getMailFolders($id);
        $calendarPermissions = $service->getCalendarPermissions($id);

        View::render('mailboxes/detail', [
            'pageTitle'           => $detail['displayName'] ?? 'Postfach',
            'detail'              => $detail,
            'folders'             => $folders,
            'calendarPermissions' => $calendarPermissions,
            'flash'               => Session::getFlash('success'),
            'error'               => Session::getFlash('error'),
        ]);
    }

    public function createSharedMailbox(): void
    {
        LocalAuth::requireAdmin();

        $displayName = trim($_POST['display_name'] ?? '');
        $alias       = trim($_POST['alias'] ?? '');
        $domain      = trim($_POST['domain'] ?? '');

        // Sanitize alias: lowercase, only alphanumeric and hyphens.
        $alias = preg_replace('/[^a-z0-9\-]/', '', strtolower($alias));

        if ($displayName === '' || $alias === '') {
            Session::flash('error', 'Anzeigename und Alias dürfen nicht leer sein.');
            Redirect::to('/mailboxes');
            return;
        }

        $service = app_service(MailboxService::class);

        try {
            $service->createSharedMailbox($displayName, $alias, $domain);
            Session::flash('success', "Shared Mailbox '{$displayName}' wird angelegt. Exchange Online benötigt einige Minuten zur Bereitstellung.");
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Authorization') || str_contains($msg, 'Forbidden') || str_contains($msg, '403') || str_contains($msg, 'permission') || str_contains($msg, 'Permission')) {
                Session::flash('error', 'Fehlende Berechtigung: User.ReadWrite.All ist in der Azure App nicht erteilt.');
            } else {
                Session::flash('error', 'Fehler beim Anlegen des Shared Mailbox: ' . $msg);
            }
        }

        Redirect::to('/mailboxes');
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

    // ── External Forwards ─────────────────────────────────────────────────────

    public function externalForwards(): void
    {
        LocalAuth::require();

        if (($_GET['refresh'] ?? '') === '1') {
            app_graph()->getCache()->forget('mailbox_external_forwards');
        }

        $service  = app_service(MailboxService::class);
        $forwards = $service->getExternalForwards();

        View::render('mailboxes/external-forwards', [
            'pageTitle' => 'Externe Weiterleitungen',
            'forwards'  => $forwards,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function removeForwardingExternal(): void
    {
        LocalAuth::require();

        $userId = trim($_POST['user_id'] ?? '');

        if ($userId !== '') {
            $service = app_service(MailboxService::class);
            try {
                $service->removeExternalForward($userId);
                Session::flash('success', 'Weiterleitung entfernt.');
            } catch (\Throwable $e) {
                Session::flash('error', 'Fehler beim Entfernen der Weiterleitung: ' . $e->getMessage());
            }
        }

        Redirect::to('/mailboxes/external-forwards');
    }

    public function exportExternalForwards(): void
    {
        LocalAuth::require();

        $service  = app_service(MailboxService::class);
        $forwards = $service->getExternalForwards();

        CsvExporter::download(
            'externe_weiterleitungen_' . date('Ymd') . '.csv',
            ['Anzeigename', 'UPN', 'E-Mail', 'Weiterleitungsadresse', 'Aktiviert', 'An Postfach und weiterleiten'],
            array_map(fn($f) => [
                $f['displayName'],
                $f['userPrincipalName'],
                $f['mail'],
                $f['forwardingAddress'],
                $f['forwardingEnabled'] ? 'Ja' : 'Nein',
                $f['deliverToMailboxAndForward'] ? 'Ja' : 'Nein',
            ], $forwards)
        );
    }

    // ── Shared Mailboxes ──────────────────────────────────────────────────────

    public function sharedMailboxes(): void
    {
        LocalAuth::require();

        if (($_GET['refresh'] ?? '') === '1') {
            app_graph()->getCache()->forget('mailbox_shared_list');
        }

        $service   = app_service(MailboxService::class);
        $mailboxes = $service->getSharedMailboxes();

        View::render('mailboxes/shared', [
            'pageTitle' => 'Freigegebene Postfächer',
            'mailboxes' => $mailboxes,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    // ── CSV Export (usage report) ─────────────────────────────────────────────

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
