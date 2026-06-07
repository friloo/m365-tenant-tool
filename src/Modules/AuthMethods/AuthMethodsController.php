<?php

namespace App\Modules\AuthMethods;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class AuthMethodsController
{
    public function index(): void
    {
        LocalAuth::require();

        if (isset($_GET['refresh'])) {
            app_graph()->getCache()->forget('auth_methods_policy');
        }

        /** @var AuthMethodsService $svc */
        $svc = app_service(AuthMethodsService::class);
        ['data' => $methods, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $svc->getMethods(),
            'Policy.Read.All'
        );
        $methods ??= [];

        View::render('authmethods/index', [
            'pageTitle' => 'Authentifizierungsmethoden-Richtlinie',
            'methods'   => $methods,
            'diag'      => $diag,
            'isAdmin'   => LocalAuth::isAdmin(),
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function setState(string $id): void
    {
        LocalAuth::requireAdmin();
        $state = trim($_POST['state'] ?? '');
        try {
            app_service(AuthMethodsService::class)->setState($id, $state);
            AppAudit::log('authmethod_set_state', 'authmethods', "Method: {$id} → {$state}");
            Session::flash('success', "Methode \"{$id}\" auf \"{$state}\" gesetzt.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
        }
        Redirect::to('/authmethods');
    }
}
