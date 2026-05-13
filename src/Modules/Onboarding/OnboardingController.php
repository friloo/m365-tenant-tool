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

        View::render('onboarding/wizard', [
            'pageTitle' => 'Benutzer-Onboarding',
            'licenses'  => $licenses,
            'groups'    => $groups,
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
                Session::flash('error', implode(' | ', $result['errors']));
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
            Session::flash('error', 'Onboarding fehlgeschlagen: ' . $e->getMessage());
            Redirect::to('/onboarding');
        }
    }
}
