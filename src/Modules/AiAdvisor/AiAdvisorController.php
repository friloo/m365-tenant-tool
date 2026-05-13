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
            // For non-AJAX callers we still need a redirect to keep the form
            // contract; for AJAX we return JSON.
            if ($this->wantsJson()) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'KI ist nicht aktiviert.']);
                return;
            }
            Redirect::to('/ai');
        }

        // Don't let an aborted browser kill the analysis. The browser may give
        // up after 60s (proxy timeout, fetch abort) but the analysis can take
        // several minutes on big tenants; we want the cache to be populated
        // anyway so the next page load shows fresh data.
        @ignore_user_abort(true);
        @set_time_limit(600);

        try {
            $service->analyze();
            if ($this->wantsJson()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => true]);
                return;
            }
            Session::flash('success', 'KI-Analyse abgeschlossen.');
        } catch (\Throwable $e) {
            if ($this->wantsJson()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
                return;
            }
            Session::flash('error', 'Analyse fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/ai');
    }

    private function wantsJson(): bool
    {
        $accept   = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xRequest = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strcasecmp($xRequest, 'XMLHttpRequest') === 0;
    }

    public function clearCache(): void
    {
        LocalAuth::requireAdmin();
        app_service(AiAdvisorService::class)->clearCache();
        Session::flash('success', 'KI-Analyse-Cache geleert.');
        Redirect::to('/ai');
    }

    /**
     * Returns the exact payload last sent to the AI provider as JSON, for
     * the protocol modal in /settings. Admin-only.
     */
    public function lastPayload(): void
    {
        LocalAuth::requireAdmin();
        header('Content-Type: application/json; charset=utf-8');
        $payload = app_service(AiAdvisorService::class)->getLastPayload();
        if (!$payload) {
            echo json_encode(['empty' => true, 'message' => 'Es wurde noch keine KI-Analyse durchgeführt. Starte die Analyse einmal unter "Zum KI-Berater".']);
            return;
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
