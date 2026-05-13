<?php

namespace App\Modules\DeletedObjects;

use App\Graph\GraphClient;

class DeletedObjectsService
{
    public function __construct(private GraphClient $graph) {}

    public function getDeletedUsers(): array
    {
        return $this->graph->paginate(
            '/directory/deletedItems/microsoft.graph.user',
            [
                '$select' => 'id,displayName,userPrincipalName,deletedDateTime,mail,jobTitle,department',
                '$top'    => '100',
            ],
            5,
            'deleted_users',
            300
        );
    }

    public function getDeletedGroups(): array
    {
        return $this->graph->paginate(
            '/directory/deletedItems/microsoft.graph.group',
            [
                '$select' => 'id,displayName,deletedDateTime,mail,groupTypes',
                '$top'    => '100',
            ],
            5,
            'deleted_groups',
            300
        );
    }

    public function restore(string $id): array
    {
        $result = $this->graph->post("/directory/deletedItems/{$id}/restore", []);
        $this->graph->getCache()->forget('deleted_users');
        $this->graph->getCache()->forget('deleted_groups');
        return $result;
    }

    public function permanentDelete(string $id): void
    {
        $this->graph->delete("/directory/deletedItems/{$id}");
        $this->graph->getCache()->forget('deleted_users');
        $this->graph->getCache()->forget('deleted_groups');
    }
}
