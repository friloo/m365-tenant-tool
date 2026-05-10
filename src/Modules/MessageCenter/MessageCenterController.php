<?php

namespace App\Modules\MessageCenter;

use App\Auth\LocalAuth;
use App\Core\View;

class MessageCenterController
{
    public function index(): void
    {
        LocalAuth::require();

        // Optional cache bust
        if (($_GET['refresh'] ?? '') === '1') {
            app_graph()->getCache()->forget('msgcenter_messages');
        }

        $filters = [
            'category' => $_GET['category'] ?? '',
            'severity' => $_GET['severity'] ?? '',
            'service'  => $_GET['service']  ?? '',
            'unread'   => $_GET['unread']   ?? '',
        ];

        /** @var MessageCenterService $service */
        $service = app_service(MessageCenterService::class);

        // Fetch all messages unfiltered — used for stats + dropdown population
        $allMessages = $service->getMessages([]);

        $stats      = $service->getStats($allMessages);
        $services   = $service->getDistinctServices($allMessages);
        $categories = $service->getDistinctCategories($allMessages);

        // Apply filters to get the displayed subset
        $messages = $service->getMessages($filters);

        View::render('msgcenter/index', [
            'pageTitle'  => 'Message Center',
            'messages'   => $messages,
            'stats'      => $stats,
            'services'   => $services,
            'categories' => $categories,
            'filters'    => $filters,
        ]);
    }
}
