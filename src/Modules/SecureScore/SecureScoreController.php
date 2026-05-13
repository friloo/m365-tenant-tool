<?php

namespace App\Modules\SecureScore;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Core\Session;

class SecureScoreController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(SecureScoreService::class);
        ['data' => $latest, 'diag' => $diag] = \App\Graph\GraphErrorTranslator::guard(
            fn() => $service->getLatest(),
            'SecurityEvents.Read.All'
        );
        $latest ??= [];
        $history = $service->getHistory(30);

        $controlScores = $latest['controlScores'] ?? [];
        $grouped       = $service->groupByCategory($controlScores);

        $currentScore = (float)($latest['currentScore'] ?? 0);
        $maxScore     = (float)($latest['maxScore']     ?? 0);
        $pct          = $maxScore > 0 ? (int)round(($currentScore / $maxScore) * 100) : 0;

        View::render('securescore/index', [
            'pageTitle'    => 'Secure Score',
            'latest'       => $latest,
            'history'      => $history,
            'grouped'      => $grouped,
            'currentScore' => $currentScore,
            'maxScore'     => $maxScore,
            'pct'          => $pct,
            'diag'         => $diag,
        ]);
    }
}
