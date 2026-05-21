<?php

namespace App\Modules\Notifications;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\View;

class NotificationsController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('notifications/index', [
            'pageTitle'     => 'Benachrichtigungen',
            'notifications' => NotificationService::recent(100),
        ]);
        NotificationService::markAllSeen();
    }

    public function markSeen(): void
    {
        LocalAuth::require();
        NotificationService::markAllSeen();
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
