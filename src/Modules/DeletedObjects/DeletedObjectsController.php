<?php

namespace App\Modules\DeletedObjects;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class DeletedObjectsController
{
    public function index(): void
    {
        LocalAuth::require();

        $service = app_service(DeletedObjectsService::class);

        try {
            $users = $service->getDeletedUsers();
        } catch (\Throwable) {
            $users = [];
        }

        try {
            $groups = $service->getDeletedGroups();
        } catch (\Throwable) {
            $groups = [];
        }

        View::render('deletedobjects/index', [
            'pageTitle' => 'Papierkorb',
            'users'     => $users,
            'groups'    => $groups,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function restore(string $id): void
    {
        LocalAuth::require();

        $service = app_service(DeletedObjectsService::class);
        try {
            $service->restore($id);
            Session::flash('success', 'Objekt erfolgreich wiederhergestellt.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Wiederherstellen fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/deleted');
    }

    public function permanentDelete(string $id): void
    {
        LocalAuth::requireAdmin();

        $service = app_service(DeletedObjectsService::class);
        try {
            $service->permanentDelete($id);
            Session::flash('success', 'Objekt endgültig gelöscht.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Löschen fehlgeschlagen: ' . $e->getMessage());
        }
        Redirect::to('/deleted');
    }
}
