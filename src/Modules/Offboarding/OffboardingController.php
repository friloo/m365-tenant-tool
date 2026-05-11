<?php

namespace App\Modules\Offboarding;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Core\Session;
use App\Core\Redirect;

class OffboardingController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(OffboardingService::class);

        $userId = $_GET['user'] ?? null;
        $user   = null;
        $state  = null;

        if ($userId) {
            $user  = $service->getUser($userId);
            $state = $service->getOffboardingState($user);
        }

        View::render('offboarding/index', [
            'pageTitle' => 'Offboarding-Assistent',
            'user'      => $user,
            'state'     => $state,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function search(): void
    {
        LocalAuth::require();
        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }

        $service = app_service(OffboardingService::class);
        echo json_encode($service->searchUsers($q));
    }

    public function disableAccount(): void
    {
        LocalAuth::requireAdmin();
        $userId = $_POST['user_id'] ?? '';
        $service = app_service(OffboardingService::class);
        try {
            $service->disableAccount($userId);
            Session::flash('success', 'Konto deaktiviert.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/offboarding?user=' . urlencode($userId));
    }

    public function revokeSessions(): void
    {
        LocalAuth::requireAdmin();
        $userId = $_POST['user_id'] ?? '';
        $service = app_service(OffboardingService::class);
        try {
            $service->revokeSessions($userId);
            Session::flash('success', 'Alle Sitzungen widerrufen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/offboarding?user=' . urlencode($userId));
    }

    public function removeLicenses(): void
    {
        LocalAuth::requireAdmin();
        $userId = $_POST['user_id'] ?? '';
        $service = app_service(OffboardingService::class);
        try {
            $service->removeAllLicenses($userId);
            Session::flash('success', 'Alle Lizenzen entfernt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/offboarding?user=' . urlencode($userId));
    }

    public function removeGroups(): void
    {
        LocalAuth::requireAdmin();
        $userId = $_POST['user_id'] ?? '';
        $service = app_service(OffboardingService::class);
        try {
            $removed = $service->removeFromAllGroups($userId);
            Session::flash('success', "{$removed} Gruppe(n) entfernt.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/offboarding?user=' . urlencode($userId));
    }

    public function convertMailbox(): void
    {
        LocalAuth::requireAdmin();
        $userId = $_POST['user_id'] ?? '';
        Session::flash('success', 'Postfach-Konvertierung muss im Exchange Admin Center manuell durchgeführt werden. Direktlink im Profil des Benutzers.');
        Redirect::to('/offboarding?user=' . urlencode($userId));
    }
}
