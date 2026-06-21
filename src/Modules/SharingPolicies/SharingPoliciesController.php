<?php

namespace App\Modules\SharingPolicies;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class SharingPoliciesController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(SharingPoliciesService::class);

        View::render('sharingpolicies/index', [
            'pageTitle'        => t('Freigaberichtlinien'),
            'spSettings'       => $service->getSharePointSettings(),
            'teamsSettings'    => $service->getTeamsSettings(),
            'crossTenant'      => $service->getCrossTenantPolicy(),
            'sites'            => $service->getSitesSharingSettings(),
            'flash'            => Session::getFlash('success'),
            'error'            => Session::getFlash('error'),
        ]);
    }

    public function updateSharePoint(): void
    {
        LocalAuth::requireAdmin();
        $service = app_service(SharingPoliciesService::class);

        $allowed = [
            'sharingCapability',
            'defaultSharingLinkType',
            'defaultLinkPermission',
            'fileAnonymousLinkType',
            'folderAnonymousLinkType',
            'isExternalUserSelfServiceSignUpEnabled',
            'isGuestUserSyncToSharePointAllowed',
        ];

        $payload = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key]) && $_POST[$key] !== '') {
                // Boolean fields
                if (in_array($key, ['isExternalUserSelfServiceSignUpEnabled', 'isGuestUserSyncToSharePointAllowed'])) {
                    $payload[$key] = $_POST[$key] === '1';
                } else {
                    $payload[$key] = $_POST[$key];
                }
            }
        }

        try {
            if (!empty($payload)) {
                $service->updateSharePointSettings($payload);
            }
            Session::flash('success', t('SharePoint-Freigabeeinstellungen gespeichert.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler beim Speichern: ') . $e->getMessage());
        }

        Redirect::to('/sharing/policies');
    }

    public function updateSite(): void
    {
        LocalAuth::requireAdmin();
        $siteId     = trim($_POST['site_id'] ?? '');
        $capability = trim($_POST['capability'] ?? '');

        $valid = ['Disabled', 'ExistingExternalUserSharingOnly', 'ExternalUserSharingOnly', 'ExternalUserAndGuestSharing'];
        if (!$siteId || !in_array($capability, $valid)) {
            Session::flash('error', t('Ungültige Eingabe.'));
            Redirect::to('/sharing/policies');
        }

        try {
            app_service(SharingPoliciesService::class)->updateSiteSharing($siteId, $capability);
            Session::flash('success', t('Freigabe-Einstellung für die Site aktualisiert.'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: ') . $e->getMessage());
        }

        Redirect::to('/sharing/policies#sites');
    }
}
