<?php

namespace App\Modules\MailboxRules;

use App\Graph\GraphClient;

/**
 * Scannt alle Mailboxen nach Inbox-Regeln, die automatisch an externe
 * Domains weiterleiten — der häufigste Exfiltrations­vektor bei
 * kompromittierten Konten.
 */
class MailboxRulesService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Liefert alle User-Mailboxen mit ihren Inbox-Regeln. Performance-
     * Hinweis: bei >500 Usern dauert das einige Sekunden, daher harter
     * Cache von 30 Minuten. Per ?refresh=1 invalidierbar.
     *
     * @return array{
     *   external_forward: array<int,array{upn:string,name:string,rule:string,forwards_to:string,enabled:bool}>,
     *   internal_forward: array<int,array{upn:string,name:string,rule:string,forwards_to:string,enabled:bool}>,
     *   delete_rules:     array<int,array{upn:string,name:string,rule:string,enabled:bool}>,
     *   scanned_users:    int,
     *   skipped_errors:   int
     * }
     */
    public function scanAll(int $maxUsers = 500): array
    {
        $cacheKey = 'mailbox_rules_scan';
        $cached   = $this->graph->getCache()->get($cacheKey);
        if (!empty($cached)) return $cached;

        $users = [];
        try {
            $users = $this->graph->paginate(
                '/users',
                ['$select' => 'id,displayName,userPrincipalName,mail,accountEnabled', '$top' => '999'],
                10,
                'mailbox_rules_users',
                1800
            );
        } catch (\Throwable $e) {
            error_log('MailboxRules: user list failed: ' . $e->getMessage());
            return $this->emptyResult();
        }

        // Tenant-eigene Domains für Internal/External-Klassifizierung
        $ownDomains = $this->getOwnDomains();

        $externalFwd = [];
        $internalFwd = [];
        $deleteRules = [];
        $scanned     = 0;
        $skipped     = 0;

        foreach ($users as $u) {
            if ($scanned >= $maxUsers) break;
            if (!($u['accountEnabled'] ?? true)) continue;
            $upn = $u['userPrincipalName'] ?? '';
            if ($upn === '') continue;

            try {
                $rules = $this->graph->get(
                    "/users/{$u['id']}/mailFolders('inbox')/messageRules",
                    [],
                    null,                                 // kein Cache pro User
                    0
                );
                $list = $rules['value'] ?? [];
                $scanned++;

                foreach ($list as $rule) {
                    $actions  = $rule['actions']  ?? [];
                    $enabled  = $rule['isEnabled'] ?? false;
                    $name     = $rule['displayName'] ?? '(ohne Name)';

                    // Forward / ForwardAsAttachment / Redirect
                    foreach (['forwardTo', 'forwardAsAttachmentTo', 'redirectTo'] as $key) {
                        if (empty($actions[$key])) continue;
                        foreach ($actions[$key] as $addr) {
                            $email = strtolower($addr['emailAddress']['address'] ?? '');
                            if ($email === '') continue;
                            $isExternal = !$this->isOwnDomain($email, $ownDomains);
                            $entry = [
                                'upn'         => $upn,
                                'name'        => $u['displayName'] ?? $upn,
                                'rule'        => $name,
                                'forwards_to' => $email,
                                'enabled'     => $enabled,
                            ];
                            if ($isExternal) $externalFwd[] = $entry;
                            else             $internalFwd[] = $entry;
                        }
                    }

                    // Lösch-Regeln
                    if (!empty($actions['delete']) || !empty($actions['permanentDelete'])) {
                        $deleteRules[] = [
                            'upn'    => $upn,
                            'name'   => $u['displayName'] ?? $upn,
                            'rule'   => $name,
                            'enabled'=> $enabled,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // 403 für nicht-gemailter User (z.B. ResourceMailbox ohne Postfach)
                // oder 404 ohne Inbox — überspringen.
                $skipped++;
                continue;
            }
        }

        $result = [
            'external_forward' => $externalFwd,
            'internal_forward' => $internalFwd,
            'delete_rules'     => $deleteRules,
            'scanned_users'    => $scanned,
            'skipped_errors'   => $skipped,
            'truncated'        => $scanned >= $maxUsers && count($users) > $maxUsers,
        ];

        // 30 Min Cache — Inbox-Regeln ändern sich selten
        $this->graph->getCache()->set($cacheKey, $result, 1800);
        return $result;
    }

    public function clearCache(): void
    {
        $this->graph->getCache()->forget('mailbox_rules_scan');
        $this->graph->getCache()->forget('mailbox_rules_users');
        $this->graph->getCache()->forget('mailbox_rules_domains');
    }

    private function getOwnDomains(): array
    {
        try {
            $rows = $this->graph->paginate(
                '/domains',
                ['$select' => 'id,isVerified'],
                5,
                'mailbox_rules_domains',
                3600
            );
            $domains = [];
            foreach ($rows as $d) {
                if ($d['isVerified'] ?? false) $domains[strtolower($d['id'] ?? '')] = true;
            }
            return $domains;
        } catch (\Throwable) {
            return [];
        }
    }

    private function isOwnDomain(string $email, array $ownDomains): bool
    {
        $at = strrpos($email, '@');
        if ($at === false) return false;
        $domain = strtolower(substr($email, $at + 1));
        return isset($ownDomains[$domain]);
    }

    private function emptyResult(): array
    {
        return [
            'external_forward' => [],
            'internal_forward' => [],
            'delete_rules'     => [],
            'scanned_users'    => 0,
            'skipped_errors'   => 0,
            'truncated'        => false,
        ];
    }
}
