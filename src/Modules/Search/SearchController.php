<?php

namespace App\Modules\Search;

use App\Auth\LocalAuth;

class SearchController
{
    public function api(): void
    {
        LocalAuth::require();

        $q = trim($_GET['q'] ?? '');

        if (mb_strlen($q) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['results' => []]);
            return;
        }

        $service = new SearchService(app_graph());
        $results = $service->search($q);

        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
    }
}
