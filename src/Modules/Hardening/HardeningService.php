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
            $this->itemSpIdleSessionSignout(),
            $this->itemSpOneDriveSharing(),
            $this->itemSpExternalReshare(),
            $this->itemBlockLegacyAuth(),
            $this->itemMfaForAllTemplate(),
            $this->itemGuestInviteRestriction(),
            $this->itemGuestUserRoleRestricted(),
            $this->itemBlockUserAppCreation(),
            $this->itemBlockUserSecurityGroupCreation(),
            $this->itemBlockTenantCreationByUsers(),
            $this->itemRestrictUserReadOthers(),
            $this->itemAppConsentPolicy(),
            $this->itemAuditLogEnable(),
            $this->itemDefenderSafeLinks(),
            $this->itemDlpInPurview(),
            $this->itemPimRoles(),
            $this->itemExternalSenderIdentifier(),
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
            'sp_idle_signout_on'    => $this->applySpIdleSessionSignout(true,  4 * 60, 1 * 60),
            'sp_idle_signout_off'   => $this->applySpIdleSessionSignout(false, 0,      0),
            'sp_onedrive_strict'    => $this->applySpOneDriveSharing('existingExternalUserSharingOnly'),
            'sp_onedrive_off'       => $this->applySpOneDriveSharing('disabled'),
            'sp_no_external_reshare'=> $this->applySpExternalReshare(false),
            'sp_allow_external_reshare' => $this->applySpExternalReshare(true),
            'block_legacy_auth'     => $this->applyBlockLegacyAuth(),
            'guest_invite_admins'   => $this->applyGuestInviteRestriction(),
            'guest_role_restricted' => $this->applyGuestUserRole('2af84b1e-32c8-42b7-82bc-daa82404023b'),     // Restricted Guest
            'guest_role_member'     => $this->applyGuestUserRole('10dae51f-b6af-4016-8d66-8c2a99b929b3'),     // Default Guest (full)
            'block_user_app_create' => $this->applyDefaultUserPerm('allowedToCreateApps',          false),
            'allow_user_app_create' => $this->applyDefaultUserPerm('allowedToCreateApps',          true),
            'block_user_secgroup'   => $this->applyDefaultUserPerm('allowedToCreateSecurityGroups',false),
            'allow_user_secgroup'   => $this->applyDefaultUserPerm('allowedToCreateSecurityGroups',true),
            'block_user_tenants'    => $this->applyDefaultUserPerm('allowedToCreateTenants',       false),
            'allow_user_tenants'    => $this->applyDefaultUserPerm('allowedToCreateTenants',       true),
            'restrict_user_read'    => $this->applyDefaultUserPerm('allowedToReadOtherUsers',      false),
            'allow_user_read'       => $this->applyDefaultUserPerm('allowedToReadOtherUsers',      true),
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

            if ($enabled) {
                $base['status']  = 'on';
                $base['detail']  = 'Security Defaults sind eingeschaltet (Basis-MFA erzwungen).';
                $base['actions'] = [['id' => 'security_defaults_off', 'label' => 'Ausschalten (nur bei aktivem CA)', 'style' => 'outline-warning']];
            } else {
                // OFF is the RECOMMENDED state once Conditional Access enforces MFA —
                // Microsoft even blocks having both on at once. Only flag a problem if
                // there is ALSO no active CA policy (then there's no MFA baseline).
                $caEnabled = 0;
                try {
                    foreach (\App\Modules\ConditionalAccess\ConditionalAccessService::fetchAllPolicies($this->graph) as $p) {
                        if (($p['state'] ?? '') === 'enabled') $caEnabled++;
                    }
                } catch (\Throwable) {
                    $caEnabled = -1; // CA state unreadable
                }

                if ($caEnabled > 0) {
                    $base['status']  = 'on';
                    $base['detail']  = "Ausgeschaltet — durch {$caEnabled} aktive Conditional-Access-Policy(s) ersetzt. Das ist der empfohlene Zustand.";
                    $base['actions'] = [];
                } elseif ($caEnabled === 0) {
                    $base['status']  = 'off';
                    $base['detail']  = 'Ausgeschaltet UND keine aktive Conditional-Access-Policy — es gibt KEINE MFA-Baseline! Entweder Security Defaults einschalten oder CA-Policies erzwingen.';
                    $base['actions'] = [['id' => 'security_defaults_on', 'label' => 'Einschalten (Basis-Schutz)', 'style' => 'outline-primary']];
                } else {
                    $base['status']  = 'warn';
                    $base['detail']  = 'Ausgeschaltet. CA-Status nicht lesbar (Policy.Read.All?) — bitte prüfen, ob Conditional-Access-Policies MFA erzwingen.';
                    $base['actions'] = [['id' => 'security_defaults_on', 'label' => 'Einschalten (Basis-Schutz)', 'style' => 'outline-primary']];
                }
            }
        } catch (\Throwable $e) {
            $base['detail'] = 'Status nicht lesbar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
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
                default                             => 'Wert: ' . htmlspecialchars($cap, ENT_QUOTES),
            };
            $base['actions'] = [
                ['id' => 'sp_sharing_strict', 'label' => 'Auf "bekannte Gäste" stellen', 'style' => 'outline-primary'],
                ['id' => 'sp_sharing_off',    'label' => 'Komplett deaktivieren',         'style' => 'outline-danger'],
            ];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = 'Status nicht lesbar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
        return $base;
    }

    /** Honest info item for a SharePoint tenant setting that Graph cannot manage (PowerShell only). */
    private function spoOnlyItem(string $id, string $title, string $desc, string $why, string $psCmd): array
    {
        $ps = \App\Core\Ui::psBlock(
            "Connect-SPOService -Url https://<tenant>-admin.sharepoint.com\n" . $psCmd,
            'Per SharePoint-PowerShell setzen'
        );
        return [
            'id'        => $id,
            'title'     => $title,
            'category'  => 'Speicher',
            'desc'      => $desc,
            'why'       => $why,
            'status'    => 'info',
            'detail'    => 'Diese Einstellung ist <strong>nicht über Microsoft Graph</strong> verfügbar '
                         . '(nicht Teil der SharePoint-Tenant-Settings in Graph v1.0) — daher nur per SharePoint-PowerShell:' . $ps,
            'actions'   => [['id' => '__link', 'label' => 'SharePoint-Sharing-Einstellungen', 'href' => 'https://admin.microsoft.com/sharepoint?page=sharing', 'style' => 'outline-primary']],
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
    }

    private function itemSpAnonLinkExpiry(): array
    {
        return $this->spoOnlyItem(
            'sp_anon_expiry',
            'Anonyme Freigabe-Links laufen ab',
            'Wenn anonyme Links erlaubt sind, sollten sie zeitlich begrenzt sein — sonst bleiben sie unbegrenzt nutzbar.',
            'DSGVO Art. 5 Abs. 1 lit. e (Speicherbegrenzung).',
            'Set-SPOTenant -RequireAnonymousLinksExpireInDays 30'
        );
    }

    private function itemSpDefaultLinkType(): array
    {
        return $this->spoOnlyItem(
            'sp_default_link',
            'Default-Freigabetyp auf intern',
            'Wenn ein User auf „Teilen" klickt, welcher Link-Typ ist vorausgewählt? „Anyone" als Default begünstigt versehentliche Datenweitergabe.',
            'DSGVO Art. 25 (Privacy by Default).',
            'Set-SPOTenant -DefaultSharingLinkType Internal   # oder: Direct (bestimmte Personen)'
        );
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
            $pols = \App\Modules\ConditionalAccess\ConditionalAccessService::fetchAllPolicies($this->graph);
            $hasBlock = false;
            foreach ($pols as $pol) {
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
            $base['detail'] = htmlspecialchars($e->getMessage(), ENT_QUOTES);
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
            $base['detail'] = 'allowInvitesFrom = ' . htmlspecialchars($current, ENT_QUOTES);
            if ($current !== 'adminsAndGuestInviters' && $current !== 'none') {
                $base['actions'] = [
                    ['id' => 'guest_invite_admins', 'label' => 'Auf "nur Admins" beschränken', 'style' => 'outline-primary'],
                ];
            }
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = htmlspecialchars($e->getMessage(), ENT_QUOTES);
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

    /**
     * Turn a write exception into a user-facing result. On a permission error it
     * names the exact Graph application permission required for this action, so
     * "insufficient privileges" becomes actionable. Note: GraphClient already
     * retries write-403s once with a fresh token, so a stale-token false alarm
     * has been ruled out by the time we get here.
     */
    private function writeError(\Throwable $e, string $perm): array
    {
        $msg = $e->getMessage();
        $isPerm = stripos($msg, 'privilege') !== false
               || stripos($msg, 'authorization') !== false
               || stripos($msg, 'forbidden') !== false
               || str_contains($msg, '403');
        if ($isPerm) {
            return ['ok' => false, 'msg' =>
                "Fehlende Graph-Berechtigung: {$perm}. In der App-Registrierung als "
                . "Anwendungsberechtigung ergänzen, Administratorzustimmung erteilen — danach wird "
                . "das Token automatisch erneuert. (Original: {$msg})"];
        }
        return ['ok' => false, 'msg' => 'Fehler: ' . $msg];
    }

    // ── Apply-Implementierungen ───────────────────────────────────────────

    private function applySecurityDefaults(bool $enabled): array
    {
        try {
            $this->graph->patch('/policies/identitySecurityDefaultsEnforcementPolicy', ['isEnabled' => $enabled]);
            return ['ok' => true, 'msg' => $enabled ? 'Security Defaults eingeschaltet.' : 'Security Defaults ausgeschaltet.'];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'Policy.ReadWrite.SecurityDefaults');
        }
    }

    private function applySpSharing(string $value): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', ['sharingCapability' => $value]);
            return ['ok' => true, 'msg' => "SharePoint Sharing auf {$value} gesetzt."];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'SharePointTenantSettings.ReadWrite.All');
        }
    }

    private function applySpAnonExpiry(int $days): array
    {
        // requireAnonymousLinksExpireInDays is NOT a Graph sharepointSettings field —
        // a PATCH is silently ignored. Be honest instead of faking success.
        return ['ok' => false, 'msg' => "Nicht über Microsoft Graph setzbar — per SharePoint-PowerShell: Set-SPOTenant -RequireAnonymousLinksExpireInDays {$days}."];
    }

    private function applySpDefaultLinkType(string $type): array
    {
        // defaultSharingLinkType is NOT a Graph sharepointSettings field (PATCH ignored).
        return ['ok' => false, 'msg' => 'Nicht über Microsoft Graph setzbar — per SharePoint-PowerShell: Set-SPOTenant -DefaultSharingLinkType ' . ucfirst($type) . '.'];
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
            return $this->writeError($e, 'Policy.ReadWrite.ConditionalAccess');
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
            return $this->writeError($e, 'Policy.ReadWrite.Authorization');
        }
    }

    // ── Zusätzliche Items (Phase 4) ────────────────────────────────────────

    private function itemSpIdleSessionSignout(): array
    {
        $base = [
            'id'        => 'sp_idle_signout',
            'title'     => 'Idle-Session-Signout in SharePoint/OneDrive',
            'category'  => 'Speicher',
            'desc'      => 'Loggt User in SharePoint/OneDrive nach Inaktivität automatisch aus — verhindert, dass jemand einen offenen Browser-Tab missbraucht.',
            'why'       => 'BSI ORP.4.A22 (Authentisierung), ISO 27001 A.9.4.2.',
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=accessControl',
        ];
        try {
            $s = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            $on  = (bool)($s['idleSessionSignOut']['isEnabled'] ?? false);
            $base['status'] = $on ? 'on' : 'off';
            if ($on) {
                $warn = (int)($s['idleSessionSignOut']['warnAfterInSeconds']   ?? 0) / 60;
                $sign = (int)($s['idleSessionSignOut']['signOutAfterInSeconds'] ?? 0) / 60;
                $base['detail'] = "Aktiv: Warnung nach {$warn} min, Sign-out nach {$sign} min.";
            } else {
                $base['detail'] = 'Idle-Sign-out ist nicht konfiguriert.';
            }
            $base['actions'] = $on
                ? [['id' => 'sp_idle_signout_off', 'label' => 'Deaktivieren', 'style' => 'outline-secondary']]
                : [['id' => 'sp_idle_signout_on',  'label' => 'Aktivieren (Sign-out nach 4 h, Warnung nach 3 h)', 'style' => 'outline-primary']];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
        return $base;
    }

    private function itemSpOneDriveSharing(): array
    {
        return $this->spoOnlyItem(
            'sp_onedrive_sharing',
            'OneDrive External Sharing einschränken',
            'Separates Setting für OneDrive (unabhängig vom Tenant-weiten SharePoint-Sharing). Begrenzt, an wen Mitarbeiter ihre OneDrive-Dateien teilen können.',
            'DSGVO Art. 25 + 32, BSI APP.5.2.',
            'Set-SPOTenant -OneDriveSharingCapability ExistingExternalUserSharingOnly   # oder: Disabled'
        );
    }

    private function itemSpExternalReshare(): array
    {
        $base = [
            'id'        => 'sp_external_reshare',
            'title'     => 'Externe Benutzer dürfen nicht weiter teilen',
            'category'  => 'Speicher',
            'desc'      => 'Verhindert dass Gäste, die Zugriff auf eine Datei haben, diese weiter an andere Externe teilen — typischer Daten-Leak-Pfad.',
            'why'       => 'DSGVO Art. 25 (Privacy by Default).',
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
        try {
            $s = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            $allow = (bool)($s['isResharingByExternalUsersEnabled'] ?? true);
            $base['status'] = $allow ? 'off' : 'on';
            $base['detail'] = $allow
                ? 'Externe Benutzer dürfen aktuell weiter teilen — Daten-Leak-Risiko.'
                : 'Externe Benutzer dürfen nicht weiter teilen.';
            $base['actions'] = $allow
                ? [['id' => 'sp_no_external_reshare',    'label' => 'Re-Sharing blockieren', 'style' => 'outline-primary']]
                : [['id' => 'sp_allow_external_reshare', 'label' => 'Re-Sharing erlauben (nicht empfohlen)', 'style' => 'outline-secondary']];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
        return $base;
    }

    private function itemGuestUserRoleRestricted(): array
    {
        // Well-known Role-Template-IDs für Gast-Rollen:
        //   2af84b1e-32c8-42b7-82bc-daa82404023b = Restricted Guest
        //   10dae51f-b6af-4016-8d66-8c2a99b929b3 = Guest User (default, voll)
        $base = [
            'id'        => 'guest_user_role',
            'title'     => 'Gast-Standardrolle einschränken',
            'category'  => 'Identity',
            'desc'      => 'Standardmäßig haben Gäste fast die gleichen Lese-Rechte wie Members (sehen das Verzeichnis). „Restricted Guest" verbirgt diese Information.',
            'why'       => 'BSI ORP.4.A26, NIS-2 Art. 21 Abs. 2(d). Microsoft-Empfehlung für DSGVO-relevante Tenants.',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/AllowlistPolicyBlade',
        ];
        try {
            $p   = $this->graph->get('/policies/authorizationPolicy', [], null, 0);
            $rid = $p['guestUserRoleId'] ?? '';
            $base['status'] = $rid === '2af84b1e-32c8-42b7-82bc-daa82404023b' ? 'on'
                            : ($rid === '10dae51f-b6af-4016-8d66-8c2a99b929b3' ? 'off' : 'warn');
            $base['detail'] = match ($rid) {
                '2af84b1e-32c8-42b7-82bc-daa82404023b' => 'Restricted Guest — minimale Rechte.',
                '10dae51f-b6af-4016-8d66-8c2a99b929b3' => 'Default Guest (volle Lese-Rechte auf Verzeichnis) — DSGVO-Risiko.',
                'a0b1b346-4d3e-4e8b-98f8-753987be4970' => 'User Guest (Standard).',
                default => 'Unbekannte Role-ID: ' . htmlspecialchars($rid, ENT_QUOTES),
            };
            $base['actions'] = $rid === '2af84b1e-32c8-42b7-82bc-daa82404023b'
                ? [['id' => 'guest_role_member',     'label' => 'Auf Default-Guest zurück (nicht empfohlen)', 'style' => 'outline-secondary']]
                : [['id' => 'guest_role_restricted', 'label' => 'Auf Restricted-Guest umstellen', 'style' => 'outline-primary']];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
        return $base;
    }

    private function itemBlockUserAppCreation(): array
    {
        $base = [
            'id'        => 'block_app_creation',
            'title'     => 'User dürfen keine App-Registrierungen anlegen',
            'category'  => 'Apps',
            'desc'      => 'Standardmäßig darf jeder Tenant-User App-Registrierungen anlegen — das wird bei Phishing-Konten missbraucht, um Persistence-Apps mit Tenant-Permissions zu erstellen.',
            'why'       => 'BSI ORP.4.A23, NIS-2 Art. 21 Abs. 2(j). Top-Vektor für Tenant-Hijack.',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UserSettingsBlade',
        ];
        return $this->checkDefaultUserPerm($base, 'allowedToCreateApps', 'block_user_app_create', 'allow_user_app_create');
    }

    private function itemBlockUserSecurityGroupCreation(): array
    {
        $base = [
            'id'        => 'block_secgroup_creation',
            'title'     => 'User dürfen keine Security-Gruppen anlegen',
            'category'  => 'Identity',
            'desc'      => 'Wenn jeder User Security-Gruppen anlegen darf, entstehen über die Zeit Hunderte unkontrollierte Berechtigungs-Container.',
            'why'       => 'BSI ORP.4.A26, Microsoft-Empfehlung für governance-strenge Umgebungen.',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/GroupsManagementMenuBlade',
        ];
        return $this->checkDefaultUserPerm($base, 'allowedToCreateSecurityGroups', 'block_user_secgroup', 'allow_user_secgroup');
    }

    private function itemBlockTenantCreationByUsers(): array
    {
        $base = [
            'id'        => 'block_tenant_creation',
            'title'     => 'User dürfen keine eigenen Tenants anlegen',
            'category'  => 'Identity',
            'desc'      => 'Microsoft erlaubt es Standardnutzern, eigene Azure-AD-Tenants zu erstellen — diese Shadow-Tenants entgehen jeder Governance.',
            'why'       => 'CIS Microsoft 365 Foundations 1.1.1.',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UserSettingsBlade',
        ];
        return $this->checkDefaultUserPerm($base, 'allowedToCreateTenants', 'block_user_tenants', 'allow_user_tenants');
    }

    private function itemRestrictUserReadOthers(): array
    {
        $base = [
            'id'        => 'restrict_user_read',
            'title'     => 'User dürfen Verzeichnis-Profile nicht lesen',
            'category'  => 'Identity',
            'desc'      => 'Standardmäßig sehen alle Mitarbeiter alle Profile (Telefon, Manager, Department). Bei großen Tenants oder DSGVO-strenger Branche einschränken.',
            'why'       => 'DSGVO Art. 5 Abs. 1c (Datenminimierung).',
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UserSettingsBlade',
            '_warning'  => true,
        ];
        $item = $this->checkDefaultUserPerm($base, 'allowedToReadOtherUsers', 'restrict_user_read', 'allow_user_read');
        // Sicherheitshalber zusätzlich warnen
        $item['detail'] .= ' Achtung: Wenn deaktiviert, brechen manche Office-Funktionen (z. B. People-Picker).';
        return $item;
    }

    private function itemExternalSenderIdentifier(): array
    {
        return [
            'id'        => 'external_sender_tag',
            'title'     => '„External"-Tag in Outlook anzeigen',
            'category'  => 'E-Mail',
            'desc'      => 'Outlook kennzeichnet Mails von außerhalb der Organisation mit einem „External"-Banner — sehr wirksam gegen Phishing.',
            'why'       => 'BSI APP.5.3.A11 (Schutz vor Phishing).',
            'status'    => 'info',
            'detail'    => 'Aktivierung nur über Exchange Online PowerShell: <code>Set-ExternalInOutlook -Enabled $true</code>. Graph hat dafür keinen Endpunkt.',
            'actions'   => [],
            'admin_url' => 'https://learn.microsoft.com/de-de/exchange/external-in-outlook-exchange-online',
        ];
    }

    /**
     * Helper: Default-User-Permission prüfen und Aktionen zurückgeben.
     */
    private function checkDefaultUserPerm(array $base, string $key, string $onAction, string $offAction): array
    {
        try {
            $p = $this->graph->get('/policies/authorizationPolicy', [], null, 0);
            $current = (bool)($p['defaultUserRolePermissions'][$key] ?? true);
            $base['status'] = $current ? 'off' : 'on';
            $base['detail'] = $current
                ? "Aktuell: User dürfen dies (defaultUserRolePermissions.{$key} = true)."
                : "Aktuell: User dürfen dies nicht (defaultUserRolePermissions.{$key} = false).";
            $base['actions'] = $current
                ? [['id' => $onAction,  'label' => 'Blockieren', 'style' => 'outline-primary']]
                : [['id' => $offAction, 'label' => 'Wieder erlauben', 'style' => 'outline-secondary']];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = htmlspecialchars($e->getMessage(), ENT_QUOTES);
            $base['actions'] = [];
        }
        return $base;
    }

    // ── Apply-Methoden (Phase 4) ───────────────────────────────────────────

    private function applySpIdleSessionSignout(bool $enabled, int $signOutMin, int $warnMin): array
    {
        try {
            $body = ['idleSessionSignOut' => [
                'isEnabled'               => $enabled,
                'warnAfterInSeconds'      => $warnMin   * 60,
                'signOutAfterInSeconds'   => $signOutMin * 60,
            ]];
            $this->graph->patch('/admin/sharepoint/settings', $body);
            return ['ok' => true, 'msg' => $enabled
                ? "Idle-Sign-out aktiviert: Warnung nach {$warnMin} min, Sign-out nach {$signOutMin} min."
                : 'Idle-Sign-out deaktiviert.'];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'SharePointTenantSettings.ReadWrite.All');
        }
    }

    private function applySpOneDriveSharing(string $value): array
    {
        // oneDriveSharingCapability is NOT a Graph sharepointSettings field (PATCH ignored).
        return ['ok' => false, 'msg' => "Nicht über Microsoft Graph setzbar — per SharePoint-PowerShell: Set-SPOTenant -OneDriveSharingCapability {$value}."];
    }

    private function applySpExternalReshare(bool $allow): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', ['isResharingByExternalUsersEnabled' => $allow]);
            return ['ok' => true, 'msg' => $allow
                ? 'Re-Sharing durch externe Benutzer erlaubt.'
                : 'Re-Sharing durch externe Benutzer blockiert.'];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'SharePointTenantSettings.ReadWrite.All');
        }
    }

    private function applyGuestUserRole(string $roleId): array
    {
        try {
            $this->graph->patch('/policies/authorizationPolicy', ['guestUserRoleId' => $roleId]);
            return ['ok' => true, 'msg' => 'Gast-Rolle auf ID ' . $roleId . ' gesetzt.'];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'Policy.ReadWrite.Authorization');
        }
    }

    private function applyDefaultUserPerm(string $key, bool $value): array
    {
        try {
            $this->graph->patch('/policies/authorizationPolicy', [
                'defaultUserRolePermissions' => [$key => $value],
            ]);
            return ['ok' => true, 'msg' => "defaultUserRolePermissions.{$key} = " . ($value ? 'true' : 'false') . ' gesetzt.'];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'Policy.ReadWrite.Authorization');
        }
    }
}
