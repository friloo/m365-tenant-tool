<?php

namespace App\Modules\AccessReview;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class AccessReviewController
{
    /**
     * GET /accessreview — list all reviews + form for new review
     */
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(AccessReviewService::class);
        $reviews = $service->getAll();

        $openCount  = count(array_filter($reviews, fn($r) => $r['status'] === 'open'));
        $totalGuests = 0;
        foreach ($reviews as $r) {
            if ((int)$r['item_count'] > $totalGuests) {
                $totalGuests = (int)$r['item_count'];
            }
        }

        View::render('accessreview/index', [
            'pageTitle'   => t('Zugriffsprüfungen'),
            'reviews'     => $reviews,
            'openCount'   => $openCount,
            'totalGuests' => $totalGuests,
            'flash'       => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    /**
     * POST /accessreview — create new guest review
     */
    public function create(): void
    {
        LocalAuth::requireAdmin();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $title = t('Gastbenutzer-Review :date', ['date' => date('d.m.Y')]);
        }

        try {
            $service  = app_service(AccessReviewService::class);
            $reviewId = $service->createGuestReview($title, LocalAuth::username());
            Session::flash('success', t('Prüfung ":title" wurde erstellt.', ['title' => $title]));
            Redirect::to('/accessreview/' . $reviewId);
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler beim Erstellen der Prüfung: :msg', ['msg' => $e->getMessage()]));
            Redirect::to('/accessreview');
        }
    }

    /**
     * GET /accessreview/{id} — show single review with items
     */
    public function show(string $id): void
    {
        LocalAuth::require();

        $service = app_service(AccessReviewService::class);
        $review  = $service->getReview((int)$id);

        if (!$review) {
            http_response_code(404);
            die('<h2>' . t('404 — Prüfung nicht gefunden') . '</h2>');
        }

        View::render('accessreview/show', [
            'pageTitle' => $review['title'],
            'review'    => $review,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    /**
     * POST /accessreview/{id}/decide/{itemId} — set decision for a single item
     */
    public function decide(string $id, string $itemId): void
    {
        LocalAuth::require();

        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['approve', 'revoke', 'pending'], true)) {
            Session::flash('error', t('Ungültige Entscheidung.'));
            Redirect::to('/accessreview/' . $id);
            return;
        }

        try {
            app_service(AccessReviewService::class)->decide(
                (int)$itemId,
                $decision,
                LocalAuth::username()
            );
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: :msg', ['msg' => $e->getMessage()]));
        }

        Redirect::to('/accessreview/' . $id);
    }

    /**
     * POST /accessreview/{id}/bulk — set decision for all pending items at once
     */
    public function bulkDecide(string $id): void
    {
        LocalAuth::require();

        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['approve', 'revoke'], true)) {
            Session::flash('error', t('Ungültige Entscheidung.'));
            Redirect::to('/accessreview/' . $id);
            return;
        }

        try {
            app_service(AccessReviewService::class)->bulkDecide(
                (int)$id,
                $decision,
                LocalAuth::username()
            );
            $label = $decision === 'approve' ? t('genehmigt') : t('widerrufen');
            Session::flash('success', t('Alle ausstehenden Einträge wurden :label.', ['label' => $label]));
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler: :msg', ['msg' => $e->getMessage()]));
        }

        Redirect::to('/accessreview/' . $id);
    }

    /**
     * POST /accessreview/{id}/apply — execute revoke decisions and close review
     */
    public function apply(string $id): void
    {
        LocalAuth::requireAdmin();

        try {
            $result = app_service(AccessReviewService::class)->applyAndClose((int)$id);

            $msg = t('Prüfung abgeschlossen.');
            if (!empty($result['revoked'])) {
                $msg .= ' ' . t(':count Konto(s) deaktiviert.', ['count' => count($result['revoked'])]);
            }
            if (!empty($result['errors'])) {
                $msg .= ' ' . t(':count Fehler aufgetreten.', ['count' => count($result['errors'])]);
                Session::flash('error', implode(' | ', $result['errors']));
            }
            Session::flash('success', $msg);
        } catch (\Throwable $e) {
            Session::flash('error', t('Fehler beim Anwenden: :msg', ['msg' => $e->getMessage()]));
        }

        Redirect::to('/accessreview/' . $id);
    }
}
