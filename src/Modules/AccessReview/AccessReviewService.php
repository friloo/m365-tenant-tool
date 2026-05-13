<?php

namespace App\Modules\AccessReview;

use App\Database\DB;
use App\Graph\GraphClient;

class AccessReviewService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Creates a new guest review and loads all guest users as items.
     * Returns the new review ID.
     */
    public function createGuestReview(string $title, string $createdBy): int
    {
        DB::execute(
            "INSERT INTO access_reviews (title, type, status, created_by) VALUES (?, 'guests', 'open', ?)",
            [$title, $createdBy]
        );
        $reviewId = (int)DB::lastInsertId();

        // Fetch all guest users — no cache (ttl=0)
        $result = $this->graph->getEventual(
            '/users',
            [
                '$count'  => 'true',
                '$top'    => '999',
                '$select' => 'id,userPrincipalName,displayName,signInActivity',
                '$filter' => "userType eq 'Guest'",
            ],
            'ar_guests_' . time(),
            0
        );

        $guests = $result['value'] ?? [];

        foreach ($guests as $guest) {
            $lastSignIn = $guest['signInActivity']['lastSignInDateTime'] ?? null;
            $lastSignInFmt = $lastSignIn ? date('Y-m-d H:i:s', strtotime($lastSignIn)) : null;

            DB::execute(
                "INSERT INTO access_review_items
                    (review_id, user_id, user_upn, user_name, last_signin, decision)
                 VALUES (?, ?, ?, ?, ?, 'pending')",
                [
                    $reviewId,
                    $guest['id'] ?? '',
                    $guest['userPrincipalName'] ?? '',
                    $guest['displayName'] ?? '',
                    $lastSignInFmt,
                ]
            );
        }

        return $reviewId;
    }

    /**
     * Returns all reviews (newest first) with item_count and pending_count.
     */
    public function getAll(): array
    {
        return DB::fetchAll(
            "SELECT r.*,
                    COUNT(i.id)                                       AS item_count,
                    SUM(i.decision = 'pending')                       AS pending_count,
                    SUM(i.decision = 'approve')                       AS approve_count,
                    SUM(i.decision = 'revoke')                        AS revoke_count
             FROM access_reviews r
             LEFT JOIN access_review_items i ON i.review_id = r.id
             GROUP BY r.id
             ORDER BY r.created_at DESC"
        );
    }

    /**
     * Returns a review with its items (inaktivste zuerst — last_signin ASC).
     */
    public function getReview(int $id): ?array
    {
        $review = DB::fetchOne(
            "SELECT * FROM access_reviews WHERE id = ?",
            [$id]
        );

        if (!$review) {
            return null;
        }

        $items = DB::fetchAll(
            "SELECT * FROM access_review_items
             WHERE review_id = ?
             ORDER BY last_signin ASC",
            [$id]
        );

        $review['items']         = $items;
        $review['item_count']    = count($items);
        $review['pending_count'] = count(array_filter($items, fn($i) => $i['decision'] === 'pending'));
        $review['approve_count'] = count(array_filter($items, fn($i) => $i['decision'] === 'approve'));
        $review['revoke_count']  = count(array_filter($items, fn($i) => $i['decision'] === 'revoke'));

        return $review;
    }

    /**
     * Sets the decision for a single item.
     */
    public function decide(int $itemId, string $decision, string $decidedBy): void
    {
        DB::execute(
            "UPDATE access_review_items
             SET decision = ?, decided_by = ?, decided_at = NOW()
             WHERE id = ?",
            [$decision, $decidedBy, $itemId]
        );
    }

    /**
     * Sets the decision for all pending items in a review.
     */
    public function bulkDecide(int $reviewId, string $decision, string $decidedBy): void
    {
        DB::execute(
            "UPDATE access_review_items
             SET decision = ?, decided_by = ?, decided_at = NOW()
             WHERE review_id = ? AND decision = 'pending'",
            [$decision, $decidedBy, $reviewId]
        );
    }

    /**
     * Executes all revoke decisions (disables accounts via Graph) and closes the review.
     * Returns ['revoked' => [...upns], 'errors' => [...messages]]
     */
    public function applyAndClose(int $reviewId): array
    {
        $items = DB::fetchAll(
            "SELECT * FROM access_review_items WHERE review_id = ? AND decision = 'revoke'",
            [$reviewId]
        );

        $revoked = [];
        $errors  = [];

        foreach ($items as $item) {
            try {
                $this->graph->patch("/users/{$item['user_id']}", ['accountEnabled' => false]);
                $revoked[] = $item['user_upn'];
            } catch (\Throwable $e) {
                $errors[] = "Fehler bei {$item['user_upn']}: " . $e->getMessage();
            }
        }

        DB::execute(
            "UPDATE access_reviews SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$reviewId]
        );

        return ['revoked' => $revoked, 'errors' => $errors];
    }
}
