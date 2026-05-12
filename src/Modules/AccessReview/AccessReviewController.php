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
            'pageTitle'   => 'Zugriffsprüfungen',
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
            $title = 'Gastbenutzer-Review ' . date('d.m.Y');
        }

        try {
            $service  = app_service(AccessReviewService::class);
            $reviewId = $service->createGuestReview($title, LocalAuth::username());
            Session::flash('success', 'Prüfung "' . $title . '" wurde erstellt.');
            Redirect::to('/accessreview/' . $reviewId);
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Erstellen der Prüfung: ' . $e->getMessage());
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
            die('<h2>404 — Prüfung nicht gefunden</h2>');
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
            Session::flash('error', 'Ungültige Entscheidung.');
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
            Session::flash('error', 'Fehler: ' . $e->getMessage());
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
            Session::flash('error', 'Ungültige Entscheidung.');
            Redirect::to('/accessreview/' . $id);
            return;
        }

        try {
            app_service(AccessReviewService::class)->bulkDecide(
                (int)$id,
                $decision,
                LocalAuth::username()
            );
            $label = $decision === 'approve' ? 'genehmigt' : 'widerrufen';
            Session::flash('success', 'Alle ausstehenden Einträge wurden ' . $label . '.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler: ' . $e->getMessage());
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

            $msg = 'Prüfung abgeschlossen.';
            if (!empty($result['revoked'])) {
                $msg .= ' ' . count($result['revoked']) . ' Konto(s) deaktiviert.';
            }
            if (!empty($result['errors'])) {
                $msg .= ' ' . count($result['errors']) . ' Fehler aufgetreten.';
                Session::flash('error', implode(' | ', $result['errors']));
            }
            Session::flash('success', $msg);
        } catch (\Throwable $e) {
            Session::flash('error', 'Fehler beim Anwenden: ' . $e->getMessage());
        }

        Redirect::to('/accessreview/' . $id);
    }
}
