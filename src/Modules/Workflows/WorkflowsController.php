<?php

namespace App\Modules\Workflows;

use App\Auth\LocalAuth;
use App\Core\AppAudit;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;

class WorkflowsController
{
    public function index(): void
    {
        LocalAuth::require();
        View::render('workflows/index', [
            'pageTitle' => t('Workflow-Automatisierung'),
            'workflows' => WorkflowService::listAll(),
        ]);
    }

    public function edit(string $id = '0'): void
    {
        LocalAuth::requireAdmin();
        $workflow = (int)$id > 0 ? WorkflowService::find((int)$id) : null;
        View::render('workflows/edit', [
            'pageTitle' => $workflow ? t('Workflow: :name', ['name' => $workflow['name']]) : t('Neuer Workflow'),
            'workflow'  => $workflow,
            'triggers'  => WorkflowService::TRIGGERS,
            'actions'   => WorkflowService::ACTIONS,
            'runs'      => $workflow ? WorkflowService::runs((int)$workflow['id'], 30) : [],
        ]);
    }

    public function save(): void
    {
        LocalAuth::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        // Decode the per-row action array into structured form
        $rawActions = $_POST['actions'] ?? [];
        $actions = [];
        if (is_array($rawActions)) {
            foreach ($rawActions as $row) {
                $type = (string)($row['type'] ?? '');
                if ($type === '') continue;
                $cfg = [];
                foreach ((array)($row['cfg'] ?? []) as $k => $v) {
                    $cfg[$k] = (string)$v;
                }
                $actions[] = ['type' => $type, 'cfg' => $cfg];
            }
        }
        $triggerCfg = [];
        foreach ((array)($_POST['trigger_cfg'] ?? []) as $k => $v) $triggerCfg[$k] = (string)$v;

        $saved = WorkflowService::save($id ?: null, [
            'name'        => (string)($_POST['name'] ?? 'Unbenannter Workflow'),
            'trigger_key' => (string)($_POST['trigger_key'] ?? 'schedule'),
            'trigger_cfg' => $triggerCfg,
            'actions'     => $actions,
            'enabled'     => !empty($_POST['enabled']),
            'created_by'  => LocalAuth::username(),
        ]);
        AppAudit::log('workflow_save', 'workflows', "ID: {$saved}");
        Session::flash('success', t('Workflow gespeichert.'));
        Redirect::to('/workflows/edit/' . $saved);
    }

    public function delete(string $id): void
    {
        LocalAuth::requireAdmin();
        WorkflowService::delete((int)$id);
        AppAudit::log('workflow_delete', 'workflows', "ID: {$id}");
        Session::flash('success', t('Workflow gelöscht.'));
        Redirect::to('/workflows');
    }

    public function runNow(string $id): void
    {
        LocalAuth::requireAdmin();
        $workflow = WorkflowService::find((int)$id);
        if (!$workflow) {
            Session::flash('error', t('Workflow nicht gefunden.'));
            Redirect::to('/workflows');
        }
        try {
            $svc = app_service(WorkflowService::class);
            $svc->runDue();
            Session::flash('success', t('Workflow ausgeführt (siehe Run-Log).'));
        } catch (\Throwable $e) {
            Session::flash('error', t('Ausführung fehlgeschlagen: :error', ['error' => $e->getMessage()]));
        }
        Redirect::to('/workflows/edit/' . (int)$id);
    }
}
