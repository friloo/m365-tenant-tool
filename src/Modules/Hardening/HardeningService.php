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
            default                 => ['ok' => false, 'msg' => t('Unbekannte Aktion: :id', ['id' => $id])],
        };
    }

    // ── Item-Definitionen ─────────────────────────────────────────────────

    private function itemSecurityDefaults(): array
    {
        $base = [
            'id'        => 'security_defaults',
            'title'     => t('Security Defaults'),
            'category'  => t('Identity'),
            'desc'      => t('Microsofts Basis-Sicherheitseinstellungen (erzwingt MFA für alle Admins und Endbenutzer). Bei produktivem Conditional-Access-Setup ausschalten und durch maßgeschneiderte CA-Policies ersetzen.'),
            'why'       => t('BSI ORP.4.A9 (Identitäts- und Zugriffsverwaltung), NIS-2 Art. 21 Abs. 2(i).'),
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
                $base['detail']  = t('Security Defaults sind eingeschaltet (Basis-MFA erzwungen).');
                $base['actions'] = [['id' => 'security_defaults_off', 'label' => t('Ausschalten (nur bei aktivem CA)'), 'style' => 'outline-warning']];
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
                    $base['detail']  = t('Ausgeschaltet — durch :count aktive Conditional-Access-Policy(s) ersetzt. Das ist der empfohlene Zustand.', ['count' => $caEnabled]);
                    $base['actions'] = [];
                } elseif ($caEnabled === 0) {
                    $base['status']  = 'off';
                    $base['detail']  = t('Ausgeschaltet UND keine aktive Conditional-Access-Policy — es gibt KEINE MFA-Baseline! Entweder Security Defaults einschalten oder CA-Policies erzwingen.');
                    $base['actions'] = [['id' => 'security_defaults_on', 'label' => t('Einschalten (Basis-Schutz)'), 'style' => 'outline-primary']];
                } else {
                    $base['status']  = 'warn';
                    $base['detail']  = t('Ausgeschaltet. CA-Status nicht lesbar (Policy.Read.All?) — bitte prüfen, ob Conditional-Access-Policies MFA erzwingen.');
                    $base['actions'] = [['id' => 'security_defaults_on', 'label' => t('Einschalten (Basis-Schutz)'), 'style' => 'outline-primary']];
                }
            }
        } catch (\Throwable $e) {
            $base['detail'] = t('Status nicht lesbar: :msg', ['msg' => htmlspecialchars($e->getMessage(), ENT_QUOTES)]);
        }
        return $base;
    }

    private function itemSpSharingCapability(): array
    {
        $base = [
            'id'        => 'sp_sharing',
            'title'     => t('SharePoint External Sharing einschränken'),
            'category'  => t('Speicher'),
            'desc'      => t('Wer darf außerhalb der Organisation auf Dateien zugreifen? Anyone-Links sind DSGVO-kritisch (Art. 25 Privacy by Default).'),
            'why'       => t('DSGVO Art. 25 + 32, BSI APP.5.2 (Microsoft 365), NIS-2 Art. 21 Abs. 2(j).'),
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
                'disabled'                          => t('Externe Freigabe komplett deaktiviert.'),
                'existingExternalUserSharingOnly'   => t('Nur an bekannte Gäste — restriktiv und gut.'),
                'externalUserSharingOnly'           => t('Nur authentifizierte Externe — akzeptabel.'),
                'externalUserAndGuestSharing'       => t('Anyone-Links sind erlaubt — DSGVO-Risiko.'),
                default                             => t('Wert: :value', ['value' => htmlspecialchars($cap, ENT_QUOTES)]),
            };
            $base['actions'] = [
                ['id' => 'sp_sharing_strict', 'label' => t('Auf "bekannte Gäste" stellen'), 'style' => 'outline-primary'],
                ['id' => 'sp_sharing_off',    'label' => t('Komplett deaktivieren'),         'style' => 'outline-danger'],
            ];
        } catch (\Throwable $e) {
            $base['status'] = 'unknown';
            $base['detail'] = t('Status nicht lesbar: :msg', ['msg' => htmlspecialchars($e->getMessage(), ENT_QUOTES)]);
        }
        return $base;
    }

    /** Honest info item for a SharePoint tenant setting that Graph cannot manage (PowerShell only). */
    private function spoOnlyItem(string $id, string $title, string $desc, string $why, string $psCmd): array
    {
        $ps = \App\Core\Ui::psBlock(
            "Connect-SPOService -Url https://<tenant>-admin.sharepoint.com\n" . $psCmd,
            t('Per SharePoint-PowerShell setzen')
        );
        return [
            'id'        => $id,
            'title'     => $title,
            'category'  => t('Speicher'),
            'desc'      => $desc,
            'why'       => $why,
            'status'    => 'info',
            'detail'    => t('Diese Einstellung ist <strong>nicht über Microsoft Graph</strong> verfügbar '
                         . '(nicht Teil der SharePoint-Tenant-Settings in Graph v1.0) — daher nur per SharePoint-PowerShell:') . $ps,
            'actions'   => [['id' => '__link', 'label' => t('SharePoint-Sharing-Einstellungen'), 'href' => 'https://admin.microsoft.com/sharepoint?page=sharing', 'style' => 'outline-primary']],
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
    }

    private function itemSpAnonLinkExpiry(): array
    {
        return $this->spoOnlyItem(
            'sp_anon_expiry',
            t('Anonyme Freigabe-Links laufen ab'),
            t('Wenn anonyme Links erlaubt sind, sollten sie zeitlich begrenzt sein — sonst bleiben sie unbegrenzt nutzbar.'),
            t('DSGVO Art. 5 Abs. 1 lit. e (Speicherbegrenzung).'),
            'Set-SPOTenant -RequireAnonymousLinksExpireInDays 30'
        );
    }

    private function itemSpDefaultLinkType(): array
    {
        return $this->spoOnlyItem(
            'sp_default_link',
            t('Default-Freigabetyp auf intern'),
            t('Wenn ein User auf „Teilen" klickt, welcher Link-Typ ist vorausgewählt? „Anyone" als Default begünstigt versehentliche Datenweitergabe.'),
            t('DSGVO Art. 25 (Privacy by Default).'),
            'Set-SPOTenant -DefaultSharingLinkType Internal   # oder: Direct (bestimmte Personen)'
        );
    }

    private function itemBlockLegacyAuth(): array
    {
        $base = [
            'id'        => 'block_legacy',
            'title'     => t('Legacy-Authentifizierung blockieren'),
            'category'  => t('Identity'),
            'desc'      => t('Basic Auth, IMAP, POP, SMTP-Auth bypassen MFA. Microsoft empfiehlt zwingend eine CA-Policy, die diese Protokolle blockiert.'),
            'why'       => t('BSI ORP.4.A23, NIS-2 Art. 21 Abs. 2(d).'),
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
                ? t('Eine CA-Policy blockt Legacy-Auth aktiv.')
                : t('Keine CA-Policy gegen Legacy-Auth gefunden.');
            if (!$hasBlock) {
                $base['actions'] = [
                    ['id' => 'block_legacy_auth', 'label' => t('CA-Policy "Block Legacy Auth" anlegen (Report-Only)'), 'style' => 'outline-primary'],
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
            'title'     => t('MFA für alle Benutzer (Conditional Access)'),
            'category'  => t('Identity'),
            'desc'      => t('Eine CA-Policy, die für alle Benutzer und Cloud-Apps MFA verlangt — Break-Glass-Accounts ausgeschlossen.'),
            'why'       => t('BSI ORP.4.A21, NIS-2 Art. 21 Abs. 2(i). Microsoft-Statistik: MFA blockt 99,9 % automatisierter Angriffe.'),
            'status'    => 'info',
            'detail'    => t('CA-Policy bitte über das Conditional-Access-Modul anlegen — der Wizard dort bietet eine geprüfte Vorlage inkl. Break-Glass-Exception.'),
            'actions'   => [
                ['id' => '__link', 'label' => t('Zum CA-Modul →'), 'href' => '/conditionalaccess', 'style' => 'outline-primary'],
            ],
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_ConditionalAccess/ConditionalAccessBlade/~/Policies',
        ];
    }

    private function itemGuestInviteRestriction(): array
    {
        $base = [
            'id'        => 'guest_invite',
            'title'     => t('Gast-Einladungen einschränken'),
            'category'  => t('Identity'),
            'desc'      => t('Wer darf B2B-Gäste einladen? Standard: jeder Mitglied — sollte auf Admins beschränkt sein.'),
            'why'       => t('BSI ORP.4.A26 (Schutz vor unautorisierten Identitäten).'),
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
                    ['id' => 'guest_invite_admins', 'label' => t('Auf "nur Admins" beschränken'), 'style' => 'outline-primary'],
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
            'title'     => t('User-App-Consent einschränken'),
            'category'  => t('Apps'),
            'desc'      => t('Verhindert, dass Endnutzer beliebigen 3rd-Party-Apps Zugriff auf ihre Daten gewähren — der Top-Vektor für Tenant-Hijack 2024.'),
            'why'       => t('BSI ORP.4.A26 / NIS-2 Art. 21 Abs. 2(c). Konfiguration unter Entra → Enterprise Apps → Consent and Permissions.'),
            'status'    => 'info',
            'detail'    => t('Microsoft hat dafür keinen einheitlichen Graph-Endpunkt. Im Admin-Center: "Allow user consent for apps from verified publishers, for selected permissions".'),
            'actions'   => [],
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/ConsentPoliciesMenuBlade',
        ];
    }

    private function itemAuditLogEnable(): array
    {
        return [
            'id'        => 'audit_log',
            'title'     => t('Audit-Log aktivieren'),
            'category'  => t('Compliance'),
            'desc'      => t('Ohne aktiviertes Audit-Log gibt es im Sicherheitsvorfall keine Forensik-Daten und keine DSGVO-Rechenschaftspflicht-Nachweise.'),
            'why'       => t('DSGVO Art. 5 Abs. 2 + Art. 32, ISO 27001 A.12.4.'),
            'status'    => 'info',
            'detail'    => t('Audit-Log-Aktivierung erfolgt im Microsoft Purview Compliance Portal. Graph bietet hier keinen Schreib-Endpunkt.'),
            'actions'   => [],
            'admin_url' => 'https://purview.microsoft.com/audit/auditsearch',
        ];
    }

    private function itemDefenderSafeLinks(): array
    {
        return [
            'id'        => 'defender_safelinks',
            'title'     => t('Defender Safe Links + Safe Attachments'),
            'category'  => t('E-Mail'),
            'desc'      => t('Schützt vor Phishing-Links und schädlichen Anhängen. Erforderlich für M365 Business Premium und E5; bei E3 zubuchbar.'),
            'why'       => t('BSI APP.5.3 (E-Mail), NIS-2 Art. 21 Abs. 2(g).'),
            'status'    => 'info',
            'detail'    => t('Konfiguration im Microsoft 365 Defender Portal → Email & collaboration → Policies & rules → Threat policies.'),
            'actions'   => [],
            'admin_url' => 'https://security.microsoft.com/threatpolicy',
        ];
    }

    private function itemDlpInPurview(): array
    {
        return [
            'id'        => 'dlp_purview',
            'title'     => t('DLP-Policies konfigurieren'),
            'category'  => t('Compliance'),
            'desc'      => t('Verhindert versehentliches/absichtliches Versenden sensibler Daten (Kreditkartennummern, Personal­ausweis, IBAN, etc.). Pflicht für DSGVO und PCI-DSS.'),
            'why'       => t('DSGVO Art. 25 + Art. 32.'),
            'status'    => 'info',
            'detail'    => t('DLP-Policies werden im Microsoft Purview Compliance Portal verwaltet. Im Tool werden unter <a href="/dlpincidents">DLP-Vorfälle</a> die Treffer angezeigt.'),
            'actions'   => [],
            'admin_url' => 'https://purview.microsoft.com/datalossprevention/policies',
        ];
    }

    private function itemPimRoles(): array
    {
        return [
            'id'        => 'pim',
            'title'     => t('PIM für Admin-Rollen einrichten'),
            'category'  => t('Identity'),
            'desc'      => t('Privileged Identity Management verlangt JIT-Aktivierung für Admin-Rollen statt dauerhafter Zuweisung. Reduziert die Angriffsfläche dramatisch.'),
            'why'       => t('BSI ORP.4.A23, NIS-2 Art. 21 Abs. 2(j). Empfehlung: keine dauerhaften Global-Administrator-Konten.'),
            'status'    => 'info',
            'detail'    => t('Aktuelle Übersicht der Aktivierungen unter <a href="/pim">PIM-Modul</a>. Konfiguration der PIM-Policies im Entra-Portal.'),
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
                t('Fehlende Graph-Berechtigung: :perm. In der App-Registrierung als Anwendungsberechtigung ergänzen, Administratorzustimmung erteilen — danach wird das Token automatisch erneuert. (Original: :msg)', ['perm' => $perm, 'msg' => $msg])];
        }
        return ['ok' => false, 'msg' => t('Fehler: :msg', ['msg' => $msg])];
    }

    // ── Apply-Implementierungen ───────────────────────────────────────────

    private function applySecurityDefaults(bool $enabled): array
    {
        try {
            $this->graph->patch('/policies/identitySecurityDefaultsEnforcementPolicy', ['isEnabled' => $enabled]);
            return ['ok' => true, 'msg' => $enabled ? t('Security Defaults eingeschaltet.') : t('Security Defaults ausgeschaltet.')];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'Policy.ReadWrite.SecurityDefaults');
        }
    }

    private function applySpSharing(string $value): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', ['sharingCapability' => $value]);
            return ['ok' => true, 'msg' => t('SharePoint Sharing auf :value gesetzt.', ['value' => $value])];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'SharePointTenantSettings.ReadWrite.All');
        }
    }

    private function applySpAnonExpiry(int $days): array
    {
        // requireAnonymousLinksExpireInDays is NOT a Graph sharepointSettings field —
        // a PATCH is silently ignored. Be honest instead of faking success.
        return ['ok' => false, 'msg' => t('Nicht über Microsoft Graph setzbar — per SharePoint-PowerShell: :cmd', ['cmd' => "Set-SPOTenant -RequireAnonymousLinksExpireInDays {$days}."])];
    }

    private function applySpDefaultLinkType(string $type): array
    {
        // defaultSharingLinkType is NOT a Graph sharepointSettings field (PATCH ignored).
        return ['ok' => false, 'msg' => t('Nicht über Microsoft Graph setzbar — per SharePoint-PowerShell: :cmd', ['cmd' => 'Set-SPOTenant -DefaultSharingLinkType ' . ucfirst($type) . '.'])];
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
            return ['ok' => true, 'msg' => t("CA-Policy angelegt im Report-Only-Modus (ID :id). Bitte im Conditional-Access-Modul testen, dann auf 'enabled' umstellen.", ['id' => $id])];
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
            return ['ok' => true, 'msg' => t('Gast-Einladungen auf Admins/Guest-Inviter beschränkt.')];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'Policy.ReadWrite.Authorization');
        }
    }

    // ── Zusätzliche Items (Phase 4) ────────────────────────────────────────

    private function itemSpIdleSessionSignout(): array
    {
        $base = [
            'id'        => 'sp_idle_signout',
            'title'     => t('Idle-Session-Signout in SharePoint/OneDrive'),
            'category'  => t('Speicher'),
            'desc'      => t('Loggt User in SharePoint/OneDrive nach Inaktivität automatisch aus — verhindert, dass jemand einen offenen Browser-Tab missbraucht.'),
            'why'       => t('BSI ORP.4.A22 (Authentisierung), ISO 27001 A.9.4.2.'),
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=accessControl',
        ];
        try {
            $s = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            $on  = (bool)($s['idleSessionSignOut']['isEnabled'] ?? false);
            $base['status'] = $on ? 'on' : 'off';
            if ($on) {
                $warn = (int)($s['idleSessionSignOut']['warnAfterInSeconds']   ?? 0) / 60;
                $sign = (int)($s['idleSessionSignOut']['signOutAfterInSeconds'] ?? 0) / 60;
                $base['detail'] = t('Aktiv: Warnung nach :warn min, Sign-out nach :sign min.', ['warn' => $warn, 'sign' => $sign]);
            } else {
                $base['detail'] = t('Idle-Sign-out ist nicht konfiguriert.');
            }
            $base['actions'] = $on
                ? [['id' => 'sp_idle_signout_off', 'label' => t('Deaktivieren'), 'style' => 'outline-secondary']]
                : [['id' => 'sp_idle_signout_on',  'label' => t('Aktivieren (Sign-out nach 4 h, Warnung nach 3 h)'), 'style' => 'outline-primary']];
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
            t('OneDrive External Sharing einschränken'),
            t('Separates Setting für OneDrive (unabhängig vom Tenant-weiten SharePoint-Sharing). Begrenzt, an wen Mitarbeiter ihre OneDrive-Dateien teilen können.'),
            t('DSGVO Art. 25 + 32, BSI APP.5.2.'),
            'Set-SPOTenant -OneDriveSharingCapability ExistingExternalUserSharingOnly   # oder: Disabled'
        );
    }

    private function itemSpExternalReshare(): array
    {
        $base = [
            'id'        => 'sp_external_reshare',
            'title'     => t('Externe Benutzer dürfen nicht weiter teilen'),
            'category'  => t('Speicher'),
            'desc'      => t('Verhindert dass Gäste, die Zugriff auf eine Datei haben, diese weiter an andere Externe teilen — typischer Daten-Leak-Pfad.'),
            'why'       => t('DSGVO Art. 25 (Privacy by Default).'),
            'admin_url' => 'https://admin.microsoft.com/sharepoint?page=sharing',
        ];
        try {
            $s = $this->graph->get('/admin/sharepoint/settings', [], null, 0);
            $allow = (bool)($s['isResharingByExternalUsersEnabled'] ?? true);
            $base['status'] = $allow ? 'off' : 'on';
            $base['detail'] = $allow
                ? t('Externe Benutzer dürfen aktuell weiter teilen — Daten-Leak-Risiko.')
                : t('Externe Benutzer dürfen nicht weiter teilen.');
            $base['actions'] = $allow
                ? [['id' => 'sp_no_external_reshare',    'label' => t('Re-Sharing blockieren'), 'style' => 'outline-primary']]
                : [['id' => 'sp_allow_external_reshare', 'label' => t('Re-Sharing erlauben (nicht empfohlen)'), 'style' => 'outline-secondary']];
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
            'title'     => t('Gast-Standardrolle einschränken'),
            'category'  => t('Identity'),
            'desc'      => t('Standardmäßig haben Gäste fast die gleichen Lese-Rechte wie Members (sehen das Verzeichnis). „Restricted Guest" verbirgt diese Information.'),
            'why'       => t('BSI ORP.4.A26, NIS-2 Art. 21 Abs. 2(d). Microsoft-Empfehlung für DSGVO-relevante Tenants.'),
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/AllowlistPolicyBlade',
        ];
        try {
            $p   = $this->graph->get('/policies/authorizationPolicy', [], null, 0);
            $rid = $p['guestUserRoleId'] ?? '';
            $base['status'] = $rid === '2af84b1e-32c8-42b7-82bc-daa82404023b' ? 'on'
                            : ($rid === '10dae51f-b6af-4016-8d66-8c2a99b929b3' ? 'off' : 'warn');
            $base['detail'] = match ($rid) {
                '2af84b1e-32c8-42b7-82bc-daa82404023b' => t('Restricted Guest — minimale Rechte.'),
                '10dae51f-b6af-4016-8d66-8c2a99b929b3' => t('Default Guest (volle Lese-Rechte auf Verzeichnis) — DSGVO-Risiko.'),
                'a0b1b346-4d3e-4e8b-98f8-753987be4970' => t('User Guest (Standard).'),
                default => t('Unbekannte Role-ID: :id', ['id' => htmlspecialchars($rid, ENT_QUOTES)]),
            };
            $base['actions'] = $rid === '2af84b1e-32c8-42b7-82bc-daa82404023b'
                ? [['id' => 'guest_role_member',     'label' => t('Auf Default-Guest zurück (nicht empfohlen)'), 'style' => 'outline-secondary']]
                : [['id' => 'guest_role_restricted', 'label' => t('Auf Restricted-Guest umstellen'), 'style' => 'outline-primary']];
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
            'title'     => t('User dürfen keine App-Registrierungen anlegen'),
            'category'  => t('Apps'),
            'desc'      => t('Standardmäßig darf jeder Tenant-User App-Registrierungen anlegen — das wird bei Phishing-Konten missbraucht, um Persistence-Apps mit Tenant-Permissions zu erstellen.'),
            'why'       => t('BSI ORP.4.A23, NIS-2 Art. 21 Abs. 2(j). Top-Vektor für Tenant-Hijack.'),
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UserSettingsBlade',
        ];
        return $this->checkDefaultUserPerm($base, 'allowedToCreateApps', 'block_user_app_create', 'allow_user_app_create');
    }

    private function itemBlockUserSecurityGroupCreation(): array
    {
        $base = [
            'id'        => 'block_secgroup_creation',
            'title'     => t('User dürfen keine Security-Gruppen anlegen'),
            'category'  => t('Identity'),
            'desc'      => t('Wenn jeder User Security-Gruppen anlegen darf, entstehen über die Zeit Hunderte unkontrollierte Berechtigungs-Container.'),
            'why'       => t('BSI ORP.4.A26, Microsoft-Empfehlung für governance-strenge Umgebungen.'),
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/GroupsManagementMenuBlade',
        ];
        return $this->checkDefaultUserPerm($base, 'allowedToCreateSecurityGroups', 'block_user_secgroup', 'allow_user_secgroup');
    }

    private function itemBlockTenantCreationByUsers(): array
    {
        $base = [
            'id'        => 'block_tenant_creation',
            'title'     => t('User dürfen keine eigenen Tenants anlegen'),
            'category'  => t('Identity'),
            'desc'      => t('Microsoft erlaubt es Standardnutzern, eigene Azure-AD-Tenants zu erstellen — diese Shadow-Tenants entgehen jeder Governance.'),
            'why'       => t('CIS Microsoft 365 Foundations 1.1.1.'),
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UserSettingsBlade',
        ];
        return $this->checkDefaultUserPerm($base, 'allowedToCreateTenants', 'block_user_tenants', 'allow_user_tenants');
    }

    private function itemRestrictUserReadOthers(): array
    {
        $base = [
            'id'        => 'restrict_user_read',
            'title'     => t('User dürfen Verzeichnis-Profile nicht lesen'),
            'category'  => t('Identity'),
            'desc'      => t('Standardmäßig sehen alle Mitarbeiter alle Profile (Telefon, Manager, Department). Bei großen Tenants oder DSGVO-strenger Branche einschränken.'),
            'why'       => t('DSGVO Art. 5 Abs. 1c (Datenminimierung).'),
            'admin_url' => 'https://entra.microsoft.com/#view/Microsoft_AAD_IAM/UserSettingsBlade',
            '_warning'  => true,
        ];
        $item = $this->checkDefaultUserPerm($base, 'allowedToReadOtherUsers', 'restrict_user_read', 'allow_user_read');
        // Sicherheitshalber zusätzlich warnen
        $item['detail'] .= ' ' . t('Achtung: Wenn deaktiviert, brechen manche Office-Funktionen (z. B. People-Picker).');
        return $item;
    }

    private function itemExternalSenderIdentifier(): array
    {
        return [
            'id'        => 'external_sender_tag',
            'title'     => t('„External"-Tag in Outlook anzeigen'),
            'category'  => t('E-Mail'),
            'desc'      => t('Outlook kennzeichnet Mails von außerhalb der Organisation mit einem „External"-Banner — sehr wirksam gegen Phishing.'),
            'why'       => t('BSI APP.5.3.A11 (Schutz vor Phishing).'),
            'status'    => 'info',
            'detail'    => t('Aktivierung nur über Exchange Online PowerShell: :cmd. Graph hat dafür keinen Endpunkt.', ['cmd' => '<code>Set-ExternalInOutlook -Enabled $true</code>']),
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
                ? t('Aktuell: User dürfen dies (defaultUserRolePermissions.:key = true).', ['key' => $key])
                : t('Aktuell: User dürfen dies nicht (defaultUserRolePermissions.:key = false).', ['key' => $key]);
            $base['actions'] = $current
                ? [['id' => $onAction,  'label' => t('Blockieren'), 'style' => 'outline-primary']]
                : [['id' => $offAction, 'label' => t('Wieder erlauben'), 'style' => 'outline-secondary']];
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
                ? t('Idle-Sign-out aktiviert: Warnung nach :warn min, Sign-out nach :sign min.', ['warn' => $warnMin, 'sign' => $signOutMin])
                : t('Idle-Sign-out deaktiviert.')];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'SharePointTenantSettings.ReadWrite.All');
        }
    }

    private function applySpOneDriveSharing(string $value): array
    {
        // oneDriveSharingCapability is NOT a Graph sharepointSettings field (PATCH ignored).
        return ['ok' => false, 'msg' => t('Nicht über Microsoft Graph setzbar — per SharePoint-PowerShell: :cmd', ['cmd' => "Set-SPOTenant -OneDriveSharingCapability {$value}."])];
    }

    private function applySpExternalReshare(bool $allow): array
    {
        try {
            $this->graph->patch('/admin/sharepoint/settings', ['isResharingByExternalUsersEnabled' => $allow]);
            return ['ok' => true, 'msg' => $allow
                ? t('Re-Sharing durch externe Benutzer erlaubt.')
                : t('Re-Sharing durch externe Benutzer blockiert.')];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'SharePointTenantSettings.ReadWrite.All');
        }
    }

    private function applyGuestUserRole(string $roleId): array
    {
        try {
            $this->graph->patch('/policies/authorizationPolicy', ['guestUserRoleId' => $roleId]);
            return ['ok' => true, 'msg' => t('Gast-Rolle auf ID :id gesetzt.', ['id' => $roleId])];
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
            return ['ok' => true, 'msg' => t('defaultUserRolePermissions.:key = :value gesetzt.', ['key' => $key, 'value' => ($value ? 'true' : 'false')])];
        } catch (\Throwable $e) {
            return $this->writeError($e, 'Policy.ReadWrite.Authorization');
        }
    }
}
