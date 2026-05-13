<?php

namespace App\Modules\Onboarding;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class OnboardingController
{
    public function wizard(): void
    {
        LocalAuth::require();
        $service  = app_service(OnboardingService::class);

        $licenses = [];
        $groups   = [];
        $domains  = [];
        $loadError = null;
        try {
            $licenses = $service->getAvailableLicenses();
        } catch (\Throwable $e) {
            error_log('Onboarding licenses: ' . $e->getMessage());
            $loadError = 'Lizenzen konnten nicht geladen werden: ' . $e->getMessage();
        }
        try {
            $groups = $service->getGroups();
        } catch (\Throwable $e) {
            error_log('Onboarding groups: ' . $e->getMessage());
            $loadError = ($loadError ? $loadError . ' | ' : '')
                . 'Gruppen konnten nicht geladen werden: ' . $e->getMessage();
        }
        try {
            $domains = $service->getVerifiedDomains();
        } catch (\Throwable $e) {
            error_log('Onboarding domains: ' . $e->getMessage());
        }

        View::render('onboarding/wizard', [
            'pageTitle' => 'Benutzer-Onboarding',
            'licenses'  => $licenses,
            'groups'    => $groups,
            'domains'   => $domains,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error') ?: $loadError,
        ]);
    }

    public function create(): void
    {
        LocalAuth::require();

        $data = [
            'displayName'       => trim($_POST['displayName'] ?? ''),
            'userPrincipalName' => trim($_POST['userPrincipalName'] ?? ''),
            'password'          => $_POST['password'] ?? '',
            'jobTitle'          => trim($_POST['jobTitle'] ?? ''),
            'department'        => trim($_POST['department'] ?? ''),
            'usageLocation'     => $_POST['usageLocation'] ?? 'DE',
            'skuId'             => $_POST['skuId'] ?? '',
            'groupIds'          => $_POST['groupIds'] ?? [],
        ];

        try {
            $result = app_service(OnboardingService::class)->runOnboarding($data);

            if ($result['user'] === null) {
                // Translator liefert konkreten Hinweis (Permission fehlt, Lizenz,
                // etc.) statt nur die rohe Graph-Message.
                $errMsg = implode(' | ', $result['errors']);
                $hint   = $this->permissionHint($errMsg);
                Session::flash('error', $errMsg . ($hint ? ' — ' . $hint : ''));
                Redirect::to('/onboarding');
                return;
            }

            $msg = 'Benutzer "' . ($result['user']['displayName'] ?? '') . '" erfolgreich erstellt.';
            if (!empty($result['errors'])) {
                $msg .= ' Hinweise: ' . implode(' | ', $result['errors']);
            }
            Session::flash('success', $msg);
            Redirect::to('/users/' . ($result['user']['id'] ?? ''));
        } catch (\Throwable $e) {
            $hint = $this->permissionHint($e->getMessage());
            Session::flash('error', 'Onboarding fehlgeschlagen: ' . $e->getMessage() . ($hint ? ' — ' . $hint : ''));
            Redirect::to('/onboarding');
        }
    }

    /**
     * Map raw Graph errors to a concrete next-step hint for the operator.
     * "Insufficient privileges" gets explicit because the writing endpoints
     * need different permissions than the read-only ones we usually check.
     */
    private function permissionHint(string $msg): ?string
    {
        if (stripos($msg, 'Insufficient privileges') !== false || stripos($msg, 'Authorization_RequestDenied') !== false) {
            return 'Die App-Registrierung in Azure braucht die Application-Permission "User.ReadWrite.All" (+ Admin Consent). Lese-Berechtigung allein reicht für POST /users nicht. Permissions prüfen unter /settings/permissions.';
        }
        if (stripos($msg, 'license') !== false && stripos($msg, 'available') !== false) {
            return 'Im Tenant sind keine verfügbaren Lizenzen für die gewählte SKU vorhanden.';
        }
        if (stripos($msg, 'userPrincipalName') !== false && stripos($msg, 'already exists') !== false) {
            return 'Ein Benutzer mit dieser UPN existiert bereits.';
        }
        return null;
    }
}
