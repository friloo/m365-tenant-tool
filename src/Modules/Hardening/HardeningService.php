<?php

namespace App\Modules\Hardening;

use App\Graph\GraphClient;

/**
 * Tenant-Hardening — kuratierte Liste der wichtigsten Sicherheits-
 * Toggles, die via Graph API direkt schaltbar sind. Wo Graph keinen
 * Schreib-Endpunkt anbietet, liefert das Modul Deep-Links ins
 * richtige Admin-Center.
 *
 * Jede Aktion ist als idempotente Operation modelliert: das Tool
 * liest den aktuellen Zustand, der Admin klickt 'Aktivieren'/
 * 'Härten', das Tool ruft PATCH/POST mit dem gewünschten Wert und
 * gibt die neue Antwort zurück.
 */
class HardeningService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Liefert alle bekannten Hardening-Items mit aktuellem Status.
     *
     * @return array<int, array<string,mixed>>
     */
    public function getItems(): array
    {
        return [
            $this->itemSecurityDefaults(),
            $this->itemSpSharingCapability(),
            $this->itemSpAnonLinkExpiry(),
            $this->itemSpDefaultLinkType(),
            $this->itemBlockLegacyAuth(),
            $this->itemMfaForAllTemplate(),
            $this->itemGuestInviteRestriction(),
            $this->itemAppConsentPolicy(),
            $this->itemAuditLogEnable(),
            $this->itemDefenderSafeLinks(),
            $this->itemDlpInPurview(),
            $this->itemPimRoles(),
        ];
    }

    /**
     * Führt eine Hardening-Aktion aus. Liefert ['ok' => bool, 'msg' => string].
     */
    public function apply(string $id): array
    {
        return match ($id) {
            'security_defaults_off' => $this->applySecurityDefaults(false),
            'security_defaults_on'  => $this->applySecurityDefaults(true),
            'sp_sharing_strict'     => $this->applySpSharing('existingExternalUserSharingOnly'),
            'sp_sharing_off'        => $this->applySpSharing('disabled'),
            'sp_anon_expiry_30'     => $this->applySpAnonExpiry(30),
            'sp_anon_expiry_90'     => $this->applySpAnonExpiry(90),
            'sp_default_internal'   => $this->applySpDefaultLinkType('internal'),
            'sp_default_direct'     => $this->applySpDefaultLinkType('direct'),
            'block_legacy_auth'     => $this->applyBlockLegacyAuth(),
            'guest_invite_admins'   => $this->applyGuestInviteRestriction(),
            default                 => ['ok' => false, 'msg' => 'Unbekannte Aktion: ' . $id],
        };
    }

    // ── Item-Definitionen ─────────────────────────────────────────────────

    private function itemSecurityDefaults(): array
    {
        $base = [
            'id'        => 'security_defaults',
            'title'     => 'Security Defaults',
            'category'  => 'Identity',
            'desc'      => 'Microsofts Basis-Sicherheitseinstellungen (erzwingt MFA für alle Admins und Endbenutzer). Bei produktivem Conditional-Access-Setup ausschalten und durch maßgeschneiderte CA-Policies ersetzen.',
            'why'       => 'BSI ORP.4.A9 (Identitäts- und Zugriffsverwaltung), NIS-2 Art. 21 Abs. 2(i).',
            'status'    => 'unknown',
            'detail'    => '',
            'actions'   => [],
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/PropertiesBlade',
        ];
        try {
            $r = $this->graph->get('/policies/identitySecurityDefaultsEnforcementPolicy', [], null, 0);
            $enabled = (bool)($r['isEnabled'] ?? false);
            $base['status'] = $enabled ? 'on' : 'off';
            $base['detail'] = $enabled ? 'Security Defaults sind eingeschaltet.' : 'Security Defaults sind ausgeschaltet.';
            $base['actions'] = $enabled
                ? [['id' => 'security_defaults_off', 'label' => 'Ausschalten (nur bei aktivem CA)', 'style' => 'outline-warning']]
                : [['id' => 'security_defaults_on',  'label' => 'Einschalten (Basis-Schutz)',       'style' => 'outline-primary']];
        } catch (\Throwable $e) {
            $base['detail'] = 'Status nicht lesbar: ' . $e->getMessage();
        }
        return $base;
    }

    private function itemSpSharingCapability(): array
    {
        $base = [
            'id'        => 'sp_sharing',
            'title'     => 'SharePoint External Sharing einschränken',
            'category'  => 'Speicher',
            'desc'      => 'Wer darf außerhalb der Organisation auf Dateien zugreifen? Anyone-Links sind DSGVO-kritisch (Art. 25 Privacy by Default).',
            'why'       => 'DSGVO Art. 25 + 32, BSI APP.5.2 (Microsoft 365), NIS-2 Art. 21 Abs. 2(j).',
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
        try {
            $s = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            if (empty($s)) throw new \RuntimeException('Settings nicht lesbar (Permission?)');
            $cap = $s['sharingCapability'] ?? '';
            $base['status'] = match ($cap) {
                'disabled'                          => 'on',
                'existingExternalUserSharingOnly'   => 'on',
                'externalUserSharingOnly'           => 'warn',
                'externalUserAndGuestSharing'       => 'off',
                default                             => 'unknown',
            };
            $base['detail'] = match ($cap) {
                'disabled'                          => 'Externe Freigabe komplett deaktiviert.',
                'existingExternalUserSharingOnly'   => 'Nur an bekannte Gäste — restriktiv und gut.',
                'externalUserSharingOnly'           => 'Nur authentifizierte Externe — akzeptabel.',
                'externalUserAndGuestSharing'       => 'Anyone-Links sind erlaubt — DSGVO-Risiko.',
                default                             => "Wert: {$cap}",
            };
            $base['actions'] = [
                ['id' => 'sp_sharing_strict', 'label' => 'Auf "bekannte Gäste" stellen', 'style' => 'outline-primary'],
                ['id' => 'sp_sharing_off',    'label' => 'Komplett deaktivieren',         'style' => 'outline-danger'],
            ];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = 'Status nicht lesbar: ' . $e->getMessage();
        }
        return $base;
    }

    private function itemSpAnonLinkExpiry(): array
    {
        $base = [
            'id'        => 'sp_anon_expiry',
            'title'     => 'Anonyme Freigabe-Links laufen ab',
            'category'  => 'Speicher',
            'desc'      => 'Wenn anonyme Links erlaubt sind, sollten sie zeitlich begrenzt sein — sonst bleiben sie unbegrenzt nutzbar (Speicherbegrenzung Art. 5 Abs. 1e DSGVO).',
            'why'       => 'DSGVO Art. 5 Abs. 1 lit. e (Speicherbegrenzung).',
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
        try {
            $s    = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            $days = (int)($s['requireAnonymousLinksExpireInDays'] ?? 0);
            $base['status'] = $days > 0 && $days <= 90 ? 'on' : ($days > 90 ? 'warn' : 'off');
            $base['detail'] = $days > 0 ? "Aktueller Ablauf: {$days} Tage" : 'Kein Ablauf — Links bleiben unbegrenzt.';
            $base['actions'] = [
                ['id' => 'sp_anon_expiry_30', 'label' => 'Auf 30 Tage setzen',  'style' => 'outline-primary'],
                ['id' => 'sp_anon_expiry_90', 'label' => 'Auf 90 Tage setzen',  'style' => 'outline-secondary'],
            ];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = $e->getMessage();
        }
        return $base;
    }

    private function itemSpDefaultLinkType(): array
    {
        $base = [
            'id'        => 'sp_default_link',
            'title'     => 'Default-Freigabetyp auf intern',
            'category'  => 'Speicher',
            'desc'      => 'Wenn ein User auf „Teilen" klickt, welcher Link-Typ wird vorausgewählt? Anyone als Default begünstigt versehentliche Datenweitergabe.',
            'why'       => 'DSGVO Art. 25 (Privacy by Default).',
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
        try {
            $s    = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            $type = $s['defaultSharingLinkType'] ?? '';
            $base['status'] = in_array($type, ['internal', 'direct'], true) ? 'on'
                            : ($type === 'anonymousAccess' ? 'off' : 'warn');
            $base['detail'] = "Aktuell: {$type}";
            $base['actions'] = [
                ['id' => 'sp_default_internal', 'label' => 'Standard: nur Organisation', 'style' => 'outline-primary'],
                ['id' => 'sp_default_direct',   'label' => 'Standard: bestimmte Personen', 'style' => 'outline-secondary'],
            ];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = $e->getMessage();
        }
        return $base;
    }

    private function itemBlockLegacyAuth(): array
    {
        $base = [
            'id'        => 'block_legacy',
            'title'     => 'Legacy-Authentifizierung blockieren',
            'category'  => 'Identity',
            'desc'      => 'Basic Auth, IMAP, POP, SMTP-Auth bypassen MFA. Microsoft empfiehlt zwingend eine CA-Policy, die diese Protokolle blockiert.',
            'why'       => 'BSI ORP.4.A23, NIS-2 Art. 21 Abs. 2(d).',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
        ];
        try {
            $p = $this->graph->get('/identity/conditionalAccess/policies', ['$top' => '200'], 'hardening_ca', 300);
            $hasBlock = false;
            foreach ($p['value'] ?? [] as $pol) {
                if (($pol['state'] ?? '') !== 'enabled') continue;
                $clientApps = $pol['conditions']['clientAppTypes'] ?? [];
                if (in_array('exchangeActiveSync', $clientApps, true)
                 || in_array('other',              $clientApps, true)) {
                    $grants = $pol['grantControls']['builtInControls'] ?? [];
                    if (in_array('block', $grants, true)) { $hasBlock = true; break; }
                }
            }
            $base['status'] = $hasBlock ? 'on' : 'off';
            $base['detail'] = $hasBlock
                ? 'Eine CA-Policy blockt Legacy-Auth aktiv.'
                : 'Keine CA-Policy gegen Legacy-Auth gefunden.';
            if (!$hasBlock) {
                $base['actions'] = [
                    ['id' => 'block_legacy_auth', 'label' => 'CA-Policy "Block Legacy Auth" anlegen (Report-Only)', 'style' => 'outline-primary'],
                ];
            }
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = $e->getMessage();
        }
        return $base;
    }

    private function itemMfaForAllTemplate(): array
    {
        return [
            'id'        => 'mfa_for_all',
            'title'     => 'MFA für alle Benutzer (Conditional Access)',
            'category'  => 'Identity',
            'desc'      => 'Eine CA-Policy, die für alle Benutzer und Cloud-Apps MFA verlangt — Break-Glass-Accounts ausgeschlossen.',
            'why'       => 'BSI ORP.4.A21, NIS-2 Art. 21 Abs. 2(i). Microsoft-Statistik: MFA blockt 99,9 % automatisierter Angriffe.',
            'status'    => 'info',
            'detail'    => 'CA-Policy bitte über das Conditional-Access-Modul anlegen — der Wizard dort bietet eine geprüfte Vorlage inkl. Break-Glass-Exception.',
            'actions'   => [
                ['id' => '__link', 'label' => 'Zum CA-Modul →', 'href' => '/conditionalaccess', 'style' => 'outline-primary'],
            ],
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
        ];
    }

    private function itemGuestInviteRestriction(): array
    {
        $base = [
            'id'        => 'guest_invite',
            'title'     => 'Gast-Einladungen einschränken',
            'category'  => 'Identity',
            'desc'      => 'Wer darf B2B-Gäste einladen? Standard: jeder Mitglied — sollte auf Admins beschränkt sein.',
            'why'       => 'BSI ORP.4.A26 (Schutz vor unautorisierten Identitäten).',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/AllowlistPolicyBlade',
        ];
        try {
            $p = $this->graph->get('/policies/authorizationPolicy', [], null, 0);
            $current = $p['allowInvitesFrom'] ?? '';
            $base['status'] = match ($current) {
                'adminsAndGuestInviters' => 'on',
                'none'                   => 'on',
                'everyone'               => 'off',
                'adminsGuestInvitersAndAllMembers' => 'warn',
                default                  => 'unknown',
            };
            $base['detail'] = "allowInvitesFrom = {$current}";
            if ($current !== 'adminsAndGuestInviters' && $current !== 'none') {
                $base['actions'] = [
                    ['id' => 'guest_invite_admins', 'label' => 'Auf "nur Admins" beschränken', 'style' => 'outline-primary'],
                ];
            }
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = $e->getMessage();
        }
        return $base;
    }

    private function itemAppConsentPolicy(): array
    {
        return [
            'id'        => 'app_consent',
            'title'     => 'User-App-Consent einschränken',
            'category'  => 'Apps',
            'desc'      => 'Verhindert, dass Endnutzer beliebigen 3rd-Party-Apps Zugriff auf ihre Daten gewähren — der Top-Vektor für Tenant-Hijack 2024.',
            'why'       => 'BSI ORP.4.A26 / NIS-2 Art. 21 Abs. 2(c). Konfiguration unter Entra → Enterprise Apps → Consent and Permissions.',
            'status'    => 'info',
            'detail'    => 'Microsoft hat dafür keinen einheitlichen Graph-Endpunkt. Im Admin-Center: "Allow user consent for apps from verified publishers, for selected permissions".',
            'actions'   => [],
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/ConsentPoliciesMenuBlade',
        ];
    }

    private function itemAuditLogEnable(): array
    {
        return [
            'id'        => 'audit_log',
            'title'     => 'Audit-Log aktivieren',
            'category'  => 'Compliance',
            'desc'      => 'Ohne aktiviertes Audit-Log gibt es im Sicherheitsvorfall keine Forensik-Daten und keine DSGVO-Rechenschaftspflicht-Nachweise.',
            'why'       => 'DSGVO Art. 5 Abs. 2 + Art. 32, ISO 27001 A.12.4.',
            'status'    => 'info',
            'detail'    => 'Audit-Log-Aktivierung erfolgt im Microsoft Purview Compliance Portal. Graph bietet hier keinen Schreib-Endpunkt.',
            'actions'   => [],
            'admin_url' => 'https://purview.microsoft.com/audit/auditsearch',
        ];
    }

    private function itemDefenderSafeLinks(): array
    {
        return [
            'id'        => 'defender_safelinks',
            'title'     => 'Defender Safe Links + Safe Attachments',
            'category'  => 'E-Mail',
            'desc'      => 'Schützt vor Phishing-Links und schädlichen Anhängen. Erforderlich für M365 Business Premium und E5; bei E3 zubuchbar.',
            'why'       => 'BSI APP.5.3 (E-Mail), NIS-2 Art. 21 Abs. 2(g).',
            'status'    => 'info',
            'detail'    => 'Konfiguration im Microsoft 365 Defender Portal → Email & collaboration → Policies & rules → Threat policies.',
            'actions'   => [],
            'admin_url' => 'https://security.microsoft.com/threatpolicy',
        ];
    }

    private function itemDlpInPurview(): array
    {
        return [
            'id'        => 'dlp_purview',
            'title'     => 'DLP-Policies konfigurieren',
            'category'  => 'Compliance',
            'desc'      => 'Verhindert versehentliches/absichtliches Versenden sensibler Daten (Kreditkartennummern, Personal­ausweis, IBAN, etc.). Pflicht für DSGVO und PCI-DSS.',
            'why'       => 'DSGVO Art. 25 + Art. 32.',
            'status'    => 'info',
            'detail'    => 'DLP-Policies werden im Microsoft Purview Compliance Portal verwaltet. Im Tool werden unter <a href="/dlpincidents">DLP-Vorfälle</a> die Treffer angezeigt.',
            'actions'   => [],
            'admin_url' => 'https://purview.microsoft.com/datalossprevention/policies',
        ];
    }

    private function itemPimRoles(): array
    {
        return [
            'id'        => 'pim',
            'title'     => 'PIM für Admin-Rollen einrichten',
            'category'  => 'Identity',
            'desc'      => 'Privileged Identity Management verlangt JIT-Aktivierung für Admin-Rollen statt dauerhafter Zuweisung. Reduziert die Angriffsfläche dramatisch.',
            'why'       => 'BSI ORP.4.A23, NIS-2 Art. 21 Abs. 2(j). Empfehlung: keine dauerhaften Global-Administrator-Konten.',
            'status'    => 'info',
            'detail'    => 'Aktuelle Übersicht der Aktivierungen unter <a href="/pim">PIM-Modul</a>. Konfiguration der PIM-Policies im Entra-Portal.',
            'actions'   => [],
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_Azure_PIMCommon/CommonMenuBlade',
        ];
    }

    // ── Apply-Implementierungen ───────────────────────────────────────────

    private function applySecurityDefaults(bool $enabled): array
    {
        try {
            $this->graph->patch('/policies/identitySecurityDefaultsEnforcementPolicy', ['isEnabled' => $enabled]);
            return ['ok' => true, 'msg' => $enabled ? 'Security Defaults eingeschaltet.' : 'Security Defaults ausgeschaltet.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }

    private function applySpSharing(string $value): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', ['sharingCapability' => $value]);
            return ['ok' => true, 'msg' => "SharePoint Sharing auf {$value} gesetzt."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }

    private function applySpAnonExpiry(int $days): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', [
                'requireAnonymousLinksExpireInDays' => $days,
            ]);
            return ['ok' => true, 'msg' => "Anonyme Link-Ablauf auf {$days} Tage gesetzt."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }

    private function applySpDefaultLinkType(string $type): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', ['defaultSharingLinkType' => $type]);
            return ['ok' => true, 'msg' => "Standard-Linktyp auf {$type} gesetzt."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }

    private function applyBlockLegacyAuth(): array
    {
        $name = 'Block Legacy Authentication (auto-created by M365 Tool)';
        $body = [
            'displayName' => $name,
            'state'       => 'enabledForReportingButNotEnforced',  // Report-Only Default
            'conditions'  => [
                'clientAppTypes' => ['exchangeActiveSync', 'other'],
                'users'          => ['includeUsers' => ['All']],
                'applications'   => ['includeApplications' => ['All']],
            ],
            'grantControls' => [
                'operator'        => 'OR',
                'builtInControls' => ['block'],
            ],
        ];
        try {
            $r = $this->graph->post('/identity/conditionalAccess/policies', $body);
            $id = $r['id'] ?? '';
            $this->graph->getCache()->forget('hardening_ca');
            return ['ok' => true, 'msg' => "CA-Policy angelegt im Report-Only-Modus (ID {$id}). Bitte im Conditional-Access-Modul testen, dann auf 'enabled' umstellen."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }

    private function applyGuestInviteRestriction(): array
    {
        try {
            $this->graph->patch('/policies/authorizationPolicy', [
                'allowInvitesFrom' => 'adminsAndGuestInviters',
            ]);
            return ['ok' => true, 'msg' => 'Gast-Einladungen auf Admins/Guest-Inviter beschränkt.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }
}
