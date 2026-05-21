<?php

namespace App\Modules\Workflows;

use App\Database\DB;
use App\Graph\GraphClient;
use App\Modules\Notifications\NotificationService;

/**
 * Lightweight workflow automation — a stripped-down Power-Automate for
 * everyday Microsoft-365 tenant tasks. A workflow consists of one
 * trigger and one or more actions that share simple template variables
 * (e.g. {{user.id}}, {{user.upn}}).
 *
 * Supported triggers (MVP):
 *   - schedule         — runs every cron tick that lies after `last_run`
 *                        + interval_minutes
 *   - new_guest_user   — matches users created in last 30 minutes
 *                        with userType='Guest'
 *   - new_user_in_group — matches users added to a group since last_run
 *
 * Supported actions (MVP):
 *   - assign_license   — assigns a SKU
 *   - add_to_group     — adds the trigger-user to a static group
 *   - send_mail        — sends a templated mail via Mailer helper
 *   - send_notification — drops an in-app notification
 *
 * Each run is logged to app_workflow_runs so admins can audit who got
 * what done by which automation.
 */
class WorkflowService
{
    public function __construct(private GraphClient $graph) {}

    public const TRIGGERS = [
        'schedule'           => 'Zeitplan (alle X Minuten)',
        'new_guest_user'     => 'Neuer Gast-Benutzer',
        'new_user_in_group'  => 'Neuer Benutzer in Gruppe',
    ];

    public const ACTIONS = [
        'assign_license'     => 'Lizenz zuweisen',
        'add_to_group'       => 'Zu Gruppe hinzufügen',
        'send_mail'          => 'E-Mail senden',
        'send_notification'  => 'In-App-Benachrichtigung erzeugen',
    ];

    public static function listAll(): array
    {
        try { return DB::fetchAll("SELECT * FROM app_workflows ORDER BY id DESC") ?: []; }
        catch (\Throwable) { return []; }
    }

    public static function find(int $id): ?array
    {
        try {
            $r = DB::fetchOne("SELECT * FROM app_workflows WHERE id = ?", [$id]);
            return $r ?: null;
        } catch (\Throwable) { return null; }
    }

    public static function save(?int $id, array $data): int
    {
        $data['actions']     = is_array($data['actions'] ?? null) ? json_encode($data['actions']) : (string)($data['actions'] ?? '[]');
        $data['trigger_cfg'] = is_array($data['trigger_cfg'] ?? null) ? json_encode($data['trigger_cfg']) : (string)($data['trigger_cfg'] ?? '{}');

        if ($id) {
            DB::execute(
                "UPDATE app_workflows SET name=?, trigger_key=?, trigger_cfg=?, actions=?, enabled=? WHERE id=?",
                [$data['name'], $data['trigger_key'], $data['trigger_cfg'], $data['actions'], (int)!empty($data['enabled']), $id]
            );
            return $id;
        }
        DB::execute(
            "INSERT INTO app_workflows (name, trigger_key, trigger_cfg, actions, enabled, created_by)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$data['name'], $data['trigger_key'], $data['trigger_cfg'], $data['actions'], (int)!empty($data['enabled']), (string)($data['created_by'] ?? '')]
        );
        return (int)DB::lastInsertId();
    }

    public static function delete(int $id): void
    {
        try {
            DB::execute("DELETE FROM app_workflow_runs WHERE workflow_id = ?", [$id]);
            DB::execute("DELETE FROM app_workflows WHERE id = ?", [$id]);
        } catch (\Throwable) {}
    }

    public static function runs(int $workflowId, int $limit = 50): array
    {
        try {
            return DB::fetchAll(
                "SELECT * FROM app_workflow_runs WHERE workflow_id = ? ORDER BY id DESC LIMIT " . max(1, min(200, $limit)),
                [$workflowId]
            ) ?: [];
        } catch (\Throwable) { return []; }
    }

    /**
     * Called from the cron. Inspects all enabled workflows, evaluates
     * their triggers and dispatches actions for each match.
     */
    public function runDue(): array
    {
        $ran = 0; $actions = 0;
        $workflows = DB::fetchAll("SELECT * FROM app_workflows WHERE enabled = 1") ?: [];
        foreach ($workflows as $w) {
            try {
                $matches = $this->evaluateTrigger($w);
                if (empty($matches)) {
                    DB::execute("UPDATE app_workflows SET last_run=NOW(), last_status='idle', last_msg='Keine Treffer' WHERE id=?", [(int)$w['id']]);
                    continue;
                }
                foreach ($matches as $ctx) {
                    $a = $this->executeActions($w, $ctx);
                    $actions += $a;
                }
                $ran++;
                DB::execute(
                    "UPDATE app_workflows SET last_run=NOW(), last_status='ok', last_msg=? WHERE id=?",
                    ['Treffer: ' . count($matches), (int)$w['id']]
                );
            } catch (\Throwable $e) {
                DB::execute(
                    "UPDATE app_workflows SET last_run=NOW(), last_status='error', last_msg=? WHERE id=?",
                    [substr($e->getMessage(), 0, 250), (int)$w['id']]
                );
            }
        }
        return ['ran' => $ran, 'actions' => $actions];
    }

    /**
     * Evaluate the trigger and return a list of context arrays (one per
     * match). Each context goes through actions one at a time.
     *
     * @return list<array<string,mixed>>
     */
    private function evaluateTrigger(array $w): array
    {
        $cfg     = json_decode((string)($w['trigger_cfg'] ?? '{}'), true) ?: [];
        $lastRun = $w['last_run'] ? strtotime((string)$w['last_run']) : 0;

        return match ($w['trigger_key']) {
            'schedule' => $this->triggerSchedule($cfg, $lastRun),
            'new_guest_user'    => $this->triggerNewGuest($lastRun),
            'new_user_in_group' => $this->triggerNewUserInGroup($cfg, $lastRun),
            default => [],
        };
    }

    private function triggerSchedule(array $cfg, int $lastRun): array
    {
        $interval = max(15, (int)($cfg['interval_minutes'] ?? 60)) * 60;
        if ($lastRun > 0 && (time() - $lastRun) < $interval) return [];
        return [['trigger' => 'schedule', 'time' => date('c')]];
    }

    private function triggerNewGuest(int $lastRun): array
    {
        $since = $lastRun > 0 ? date('c', $lastRun) : date('c', time() - 1800);
        try {
            $r = $this->graph->get('/users', [
                '$filter' => "userType eq 'Guest' and createdDateTime gt {$since}",
                '$select' => 'id,userPrincipalName,displayName',
                '$top'    => '50',
            ]);
            $out = [];
            foreach ($r['value'] ?? [] as $u) {
                $out[] = ['user' => $u, 'trigger' => 'new_guest_user'];
            }
            return $out;
        } catch (\Throwable) { return []; }
    }

    private function triggerNewUserInGroup(array $cfg, int $lastRun): array
    {
        $groupId = (string)($cfg['group_id'] ?? '');
        if ($groupId === '') return [];
        $since = $lastRun > 0 ? date('c', $lastRun) : date('c', time() - 86400);
        try {
            // Note: Graph membership doesn't carry "added at" — we list
            // members and rely on user.createdDateTime as a proxy for
            // "newly arrived in tenant + in this group".
            $r = $this->graph->get("/groups/{$groupId}/members",
                ['$select' => 'id,userPrincipalName,displayName,createdDateTime', '$top' => '50']);
            $out = [];
            foreach ($r['value'] ?? [] as $u) {
                $created = $u['createdDateTime'] ?? null;
                if ($created && strtotime($created) > strtotime($since)) {
                    $out[] = ['user' => $u, 'trigger' => 'new_user_in_group'];
                }
            }
            return $out;
        } catch (\Throwable) { return []; }
    }

    /**
     * Run every action of a workflow against one trigger context.
     * Returns the number of action invocations.
     */
    private function executeActions(array $w, array $ctx): int
    {
        $actions = json_decode((string)$w['actions'], true) ?: [];
        $count = 0;
        foreach ($actions as $action) {
            $type = (string)($action['type'] ?? '');
            $cfg  = (array)($action['cfg']  ?? []);
            try {
                $detail = $this->dispatchAction($type, $cfg, $ctx);
                $this->logRun((int)$w['id'], 'ok', (string)($ctx['user']['userPrincipalName'] ?? $ctx['trigger']), $type . ': ' . $detail);
            } catch (\Throwable $e) {
                $this->logRun((int)$w['id'], 'error', (string)($ctx['user']['userPrincipalName'] ?? $ctx['trigger']), $type . ': ' . $e->getMessage());
            }
            $count++;
        }
        return $count;
    }

    private function dispatchAction(string $type, array $cfg, array $ctx): string
    {
        $user = (array)($ctx['user'] ?? []);
        switch ($type) {
            case 'assign_license':
                $sku = (string)($cfg['sku_id'] ?? '');
                if (!$sku || empty($user['id'])) return 'übersprungen (keine SKU/User)';
                $this->graph->post("/users/{$user['id']}/assignLicense", [
                    'addLicenses'    => [['skuId' => $sku]],
                    'removeLicenses' => [],
                ]);
                return 'Lizenz ' . $sku . ' an ' . ($user['userPrincipalName'] ?? '?') . ' zugewiesen';

            case 'add_to_group':
                $gid = (string)($cfg['group_id'] ?? '');
                if (!$gid || empty($user['id'])) return 'übersprungen (keine Gruppe/User)';
                $this->graph->post("/groups/{$gid}/members/\$ref", [
                    '@odata.id' => 'https://graph.microsoft.com/v1.0/directoryObjects/' . $user['id'],
                ]);
                return $user['userPrincipalName'] . ' zu Gruppe ' . $gid;

            case 'send_mail':
                $to = $this->resolveTemplate((string)($cfg['to'] ?? ''), $ctx);
                $subject = $this->resolveTemplate((string)($cfg['subject'] ?? 'Workflow-Benachrichtigung'), $ctx);
                $body = $this->resolveTemplate((string)($cfg['body'] ?? ''), $ctx);
                if ($to === '') return 'keine Empfänger-Adresse';
                if (class_exists(\App\Helpers\Mailer::class)) {
                    \App\Helpers\Mailer::send($to, $subject, $body);
                    return 'Mail an ' . $to;
                }
                return 'Mailer fehlt';

            case 'send_notification':
                $title = $this->resolveTemplate((string)($cfg['title'] ?? 'Workflow'), $ctx);
                $body  = $this->resolveTemplate((string)($cfg['body']  ?? ''), $ctx);
                NotificationService::push($title, $body, (string)($cfg['severity'] ?? 'info'), null, 'workflow');
                return 'In-App-Benachrichtigung erzeugt';

            default:
                return 'Unbekannte Aktion ' . $type;
        }
    }

    private function resolveTemplate(string $tpl, array $ctx): string
    {
        if ($tpl === '') return '';
        $flat = ['trigger' => $ctx['trigger'] ?? ''];
        foreach (((array)($ctx['user'] ?? [])) as $k => $v) {
            if (!is_array($v)) $flat['user.' . $k] = (string)$v;
        }
        return preg_replace_callback('/\{\{\s*([a-z0-9_.]+)\s*\}\}/i', function ($m) use ($flat) {
            return $flat[$m[1]] ?? $m[0];
        }, $tpl) ?? $tpl;
    }

    private function logRun(int $workflowId, string $status, string $target, string $detail): void
    {
        try {
            DB::execute(
                "INSERT INTO app_workflow_runs (workflow_id, status, target, detail) VALUES (?, ?, ?, ?)",
                [$workflowId, $status, $target, mb_substr($detail, 0, 1000)]
            );
        } catch (\Throwable) {}
    }
}
