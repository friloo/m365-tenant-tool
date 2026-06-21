<?php

namespace App\Modules\Mailboxes;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Helpers\CsvExporter;
use App\Modules\MailboxRules\MailboxRulesService;

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
            'pageTitle' => t('Postfächer'),
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
            'pageTitle'           => $detail['displayName'] ?? t('Postfach'),
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
            Session::flash('error', t('Anzeigename und Alias dürfen nicht leer sein.'));
            Redirect::to('/mailboxes');
            return;
        }

        $service = app_service(MailboxService::class);

        try {
            $service->createSharedMailbox($displayName, $alias, $domain);
            Session::flash('success', t("Shared Mailbox ':name' wird angelegt. Exchange Online benötigt einige Minuten zur Bereitstellung.", ['name' => $displayName]));
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Authorization') || str_contains($msg, 'Forbidden') || str_contains($msg, '403') || str_contains($msg, 'permission') || str_contains($msg, 'Permission')) {
                Session::flash('error', t('Fehlende Berechtigung: User.ReadWrite.All ist in der Azure App nicht erteilt.'));
            } else {
                Session::flash('error', t('Fehler beim Anlegen des Shared Mailbox: :msg', ['msg' => $msg]));
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
                Session::flash('success', t('Weiterleitung wurde entfernt.'));
            } else {
                $service->setForwarding($id, $forwardTo);
                Session::flash('success', t('Weiterleitung gesetzt auf: :address', ['address' => $forwardTo]));
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Authorization') || str_contains($msg, 'Forbidden') || str_contains($msg, '403') || str_contains($msg, 'permission') || str_contains($msg, 'Permission')) {
                Session::flash('error', t('Fehlende Berechtigung: MailboxSettings.ReadWrite ist in der Azure App nicht erteilt.'));
            } else {
                Session::flash('error', t('Fehler beim Speichern der Weiterleitung: :msg', ['msg' => $msg]));
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
            Session::flash('success', $enabled ? t('Abwesenheitsnotiz aktiviert.') : t('Abwesenheitsnotiz deaktiviert.'));
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Authorization') || str_contains($msg, 'Forbidden') || str_contains($msg, '403') || str_contains($msg, 'permission') || str_contains($msg, 'Permission')) {
                Session::flash('error', t('Fehlende Berechtigung: MailboxSettings.ReadWrite ist in der Azure App nicht erteilt.'));
            } else {
                Session::flash('error', t('Fehler beim Speichern der Abwesenheitsnotiz: :msg', ['msg' => $msg]));
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
            app_service(MailboxRulesService::class)->clearCache();
        }

        // Two complementary external-forwarding channels on one page:
        //  - mailbox-level forwarding (forwardingSmtpAddress)
        //  - inbox rules that forward/redirect (messageRules)
        $forwards = app_service(MailboxService::class)->getExternalForwards();
        $report   = app_service(MailboxRulesService::class)->scanAll(500);

        $rulesDiag = null;
        if (($report['scanned_users'] ?? 0) === 0) {
            $rulesDiag = \App\Graph\GraphErrorTranslator::translate(
                app_graph()->getLastError(),
                'MailboxSettings.Read'
            );
        }

        View::render('mailboxes/forwarding', [
            'pageTitle' => t('Weiterleitungen & Regeln'),
            'forwards'  => $forwards,
            'report'    => $report,
            'rulesDiag' => $rulesDiag,
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
                Session::flash('success', t('Weiterleitung entfernt.'));
            } catch (\Throwable $e) {
                Session::flash('error', t('Fehler beim Entfernen der Weiterleitung: :msg', ['msg' => $e->getMessage()]));
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
            [t('Anzeigename'), t('UPN'), t('E-Mail'), t('Weiterleitungsadresse'), t('Aktiviert'), t('An Postfach und weiterleiten')],
            array_map(fn($f) => [
                $f['displayName'],
                $f['userPrincipalName'],
                $f['mail'],
                $f['forwardingAddress'],
                $f['forwardingEnabled'] ? t('Ja') : t('Nein'),
                $f['deliverToMailboxAndForward'] ? t('Ja') : t('Nein'),
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
            'pageTitle' => t('Freigegebene Postfächer'),
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
            [t('Anzeigename'), t('UPN'), t('Größe (MB)'), t('Elemente'), t('Gel. Elemente'), t('Gel. Größe (MB)'), t('Gelöscht')],
            array_map(fn($u) => [
                $u['displayName'],
                $u['upn'],
                number_format($u['storageUsedBytes'] / (1024 ** 2), 2, '.', ''),
                $u['itemCount'],
                $u['deletedItemCount'],
                number_format($u['deletedItemSizeBytes'] / (1024 ** 2), 2, '.', ''),
                $u['isDeleted'] ? t('Ja') : t('Nein'),
            ], $usage)
        );
    }
}
