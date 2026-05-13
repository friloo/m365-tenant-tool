<?php
namespace App\Modules\AiAdvisor;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class AiAdvisorController
{
    public function index(): void
    {
        LocalAuth::require();
        $service = app_service(AiAdvisorService::class);

        View::render('ai/index', [
            'pageTitle' => 'KI-Sicherheitsberater',
            'enabled'   => $service->isEnabled(),
            'analysis'  => $service->isEnabled() ? $service->getCachedAnalysis() : null,
            'provider'  => $service->getProviderLabel(),
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function analyze(): void
    {
        LocalAuth::require();
        $service = app_service(AiAdvisorService::class);

        if (!$service->isEnabled()) {
            Redirect::to('/ai');
        }

        try {
            set_time_limit(120);
            $service->analyze();
            Session::flash('success', 'KI-Analyse abgeschlossen.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Analyse fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/ai');
    }

    public function clearCache(): void
    {
        LocalAuth::requireAdmin();
        app_service(AiAdvisorService::class)->clearCache();
        Session::flash('success', 'KI-Analyse-Cache geleert.');
        Redirect::to('/ai');
    }
}
