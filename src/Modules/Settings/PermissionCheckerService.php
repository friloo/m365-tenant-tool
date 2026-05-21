<?php

namespace App\Modules\Settings;

use App\Graph\GraphClient;

class PermissionCheckerService
{
    public function __construct(private GraphClient $graph) {}

    // ── Required permissions map ───────────────────────────────────────────
    // Each entry: description (German), features (array of strings), write (bool)
    public static function getRequiredPermissions(): array
    {
        return [
            // ── Core directory ──────────────────────────────────────────
            'User.Read.All' => [
                'desc'     => 'Benutzerprofile lesen',
                'features' => ['Benutzer (Liste & Detail)', 'MFA-Methoden', 'Passwort-Ablauf', 'Inaktive Konten', 'Sign-in-Log (Benutzerfilter)', 'Offboarding', 'Externe Weiterleitungen'],
                'section'  => 'Verzeichnis',
                'write'    => false,
            ],
            'User.ReadWrite.All' => [
                'desc'     => 'Benutzer anlegen, bearbeiten und löschen',
                'features' => ['Onboarding (neuen Benutzer anlegen)', 'Benutzerdaten ändern (Job-Titel, Abteilung, Telefon)'],
                'section'  => 'Verzeichnis',
                'write'    => true,
            ],
            'User.EnableDisableAccount.All' => [
                'desc'     => 'Benutzerkonten aktivieren/deaktivieren',
                'features' => ['Benutzer aktivieren/deaktivieren', 'Offboarding (Konto deaktivieren)'],
                'section'  => 'Verzeichnis',
                'write'    => true,
            ],
            'User.ManageIdentities.All' => [
                'desc'     => 'Authentifizierungsmethoden verwalten',
                'features' => ['MFA zurücksetzen', 'Anmeldesitzungen widerrufen'],
                'section'  => 'Verzeichnis',
                'write'    => true,
            ],
            'UserAuthenticationMethod.ReadWrite.All' => [
                'desc'     => 'Authentifizierungsmethoden lesen und schreiben',
                'features' => ['MFA-Methoden (Detail)', 'MFA zurücksetzen'],
                'section'  => 'Verzeichnis',
                'write'    => true,
            ],
            'Directory.Read.All' => [
                'desc'     => 'Verzeichnis lesen (Gruppen, Rollen, Geräte)',
                'features' => ['Gruppen & Teams', 'Admin-Rollen', 'Gastbenutzer', 'Lizenz-Berater', 'App-Registrierungen'],
                'section'  => 'Verzeichnis',
                'write'    => false,
            ],
            'Group.ReadWrite.All' => [
                'desc'     => 'Gruppen lesen und schreiben',
                'features' => ['Gruppe anlegen', 'Gruppe löschen', 'Mitglieder hinzufügen/entfernen', 'Gruppenbesitzer verwalten'],
                'section'  => 'Verzeichnis',
                'write'    => true,
            ],
            // ── Licenses ───────────────────────────────────────────────
            'Organization.Read.All' => [
                'desc'     => 'Organisationsinformationen lesen',
                'features' => ['Dashboard (Mandanteninfo)', 'Lizenz-Ablauf (beta)'],
                'section'  => 'Lizenzen',
                'write'    => false,
            ],
            'LicenseAssignment.ReadWrite.All' => [
                'desc'     => 'Lizenzen zuweisen und entfernen',
                'features' => ['Lizenz zuweisen', 'Lizenz entfernen', 'Offboarding (Lizenzen entfernen)'],
                'section'  => 'Lizenzen',
                'write'    => true,
            ],
            // ── Storage & Sharing ──────────────────────────────────────
            'Sites.Read.All' => [
                'desc'     => 'SharePoint-Sites lesen',
                'features' => ['SharePoint', 'OneDrive', 'Freigaben'],
                'section'  => 'Speicher & Freigaben',
                'write'    => false,
            ],
            'Files.ReadWrite.All' => [
                'desc'     => 'Dateien und Freigabeberechtigungen verwalten',
                'features' => ['Externe Freigabe widerrufen', 'Freigaben-Monitor'],
                'section'  => 'Speicher & Freigaben',
                'write'    => true,
            ],
            'SharePointTenantSettings.ReadWrite.All' => [
                'desc'     => 'SharePoint-Tenant-Einstellungen lesen und schreiben',
                'features' => ['Freigaberichtlinien lesen', 'Freigaberichtlinien setzen', 'Tenant-Härtung (Sharing-Toggles)'],
                'section'  => 'Speicher & Freigaben',
                'write'    => true,
            ],
            'Policy.ReadWrite.Authorization' => [
                'desc'     => 'Authorization-Policy ändern (Gast-Einladungs-Regeln, App-Consent-Defaults)',
                'features' => ['Tenant-Härtung: Gast-Einladungen einschränken', 'Tenant-Härtung: App-Consent'],
                'section'  => 'Sicherheit',
                'write'    => true,
            ],
            // ── Exchange & Mailboxes ───────────────────────────────────
            'Mail.ReadBasic.All' => [
                'desc'     => 'Basisinfos zu Postfächern lesen',
                'features' => ['Postfächer (Liste)', 'Postfach-Ordner'],
                'section'  => 'Exchange & Kommunikation',
                'write'    => false,
            ],
            'Mail.Read' => [
                'desc'     => 'Mailbox-Inhalte und Inbox-Regeln tenant-weit lesen',
                'features' => ['Auto-Forward-Audit (Mailbox-Regeln scannen)'],
                'section'  => 'Exchange & Kommunikation',
                'write'    => false,
            ],
            'MailboxSettings.ReadWrite' => [
                'desc'     => 'Postfacheinstellungen lesen und schreiben',
                'features' => ['Postfach-Detail', 'Auto-Antwort setzen', 'Weiterleitung setzen/entfernen', 'Externe Weiterleitungen', 'Freigegebene Postfächer'],
                'section'  => 'Exchange & Kommunikation',
                'write'    => true,
            ],
            // ── Reports ───────────────────────────────────────────────
            'Reports.Read.All' => [
                'desc'     => 'Aktivitäts- und Nutzungsberichte lesen',
                'features' => ['Teams-Nutzung', 'Adoptions-Report', 'Inaktive Gruppen', 'OneDrive-Bericht', 'Security Posture (MFA-Registrierungsrate, SSPR-Adoption, Admin-MFA-Prüfung)'],
                'section'  => 'Berichte',
                'write'    => false,
            ],
            'AuditLog.Read.All' => [
                'desc'     => 'Audit-Log und Anmeldeprotokolle lesen',
                'features' => ['Audit-Log', 'Sign-in-Log', 'Benutzer Sign-in-Verlauf'],
                'section'  => 'Berichte',
                'write'    => false,
            ],
            // ── Security ──────────────────────────────────────────────
            'Policy.Read.All' => [
                'desc'     => 'Alle Richtlinien lesen (CA, Named Locations, Auth-Richtlinien)',
                'features' => ['Conditional Access (Übersicht & Analyse)', 'Named Locations', 'Security Posture (Security Defaults, App-Zustimmungsrichtlinie, Gasteinladungsrichtlinie, CA-Sitzungssteuerung)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'Policy.ReadWrite.ConditionalAccess' => [
                'desc'     => 'Conditional Access Richtlinien lesen und schreiben',
                'features' => ['Sicherheit (CA-Richtlinien anzeigen)', 'CA-Richtlinie aktivieren/deaktivieren'],
                'section'  => 'Sicherheit',
                'write'    => true,
            ],
            'SecurityAlert.ReadWrite.All' => [
                'desc'     => 'Sicherheitswarnungen lesen und verwalten',
                'features' => ['Defender Alerts', 'Mail Flow & Schutz (Alerts)', 'Alert auflösen'],
                'section'  => 'Sicherheit',
                'write'    => true,
            ],
            'IdentityRiskyUser.Read.All' => [
                'desc'     => 'Risiko-Benutzer lesen (Entra ID Protection)',
                'features' => ['Risiko-Anmeldungen (Liste der gefährdeten Benutzer)', 'Dashboard-Kachel "Risikobenutzer"'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'IdentityRiskyUser.ReadWrite.All' => [
                'desc'     => 'Risiko-Benutzer verwalten (bestätigen / verwerfen)',
                'features' => ['Risiko bestätigen', 'Risiko verwerfen'],
                'section'  => 'Sicherheit',
                'write'    => true,
            ],
            'IdentityRiskEvent.Read.All' => [
                'desc'     => 'Risiko-Erkennungen lesen (Entra ID Protection)',
                'features' => ['Risiko-Anmeldungen (Erkennungen / Risk Detections)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'SecurityEvents.Read.All' => [
                'desc'     => 'Sicherheitsereignisse und Secure Score lesen',
                'features' => ['Secure Score', 'Security Posture'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'RoleManagement.Read.Directory' => [
                'desc'     => 'Admin-Rollenzuweisungen + PIM lesen',
                'features' => ['PIM-Übersicht (JIT-Admin)', 'Admin-Rollen lesen (read-only)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'RoleManagement.ReadWrite.Directory' => [
                'desc'     => 'Admin-Rollenzuweisungen lesen und schreiben',
                'features' => ['Admin-Rollen (Übersicht)', 'Admin-Rolle zuweisen', 'Admin-Rolle entfernen', 'Security Posture (Admin-MFA-Prüfung, PIM-Adoption)'],
                'section'  => 'Sicherheit',
                'write'    => true,
            ],
            'Application.Read.All' => [
                'desc'     => 'App-Registrierungen und Service-Principals lesen',
                'features' => ['OAuth-App-Audit (Enterprise Apps Inventur + Risk-Score)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'Application.ReadWrite.All' => [
                'desc'     => 'App-Registrierungen lesen und verwalten',
                'features' => ['App-Registrierungen (Detail)', 'App-Secret hinzufügen', 'App-Secret löschen', 'Enterprise Apps (Service Principals)'],
                'section'  => 'Sicherheit',
                'write'    => true,
            ],
            'AttackSimulation.Read.All' => [
                'desc'     => 'Defender Attack-Simulation-Daten lesen',
                'features' => ['Phishing-Simulationen (durchgeführte Kampagnen, Klick-/Compromised-Quote)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'LifecycleWorkflows.Read.All' => [
                'desc'     => 'Entra-ID-Governance-Lifecycle-Workflows lesen',
                'features' => ['Lifecycle-Workflows-Übersicht (Joiner/Mover/Leaver)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'IdentityProvider.Read.All' => [
                'desc'     => 'Externe Identity Providers lesen',
                'features' => ['Identity Provider Trust (Google, Facebook, SAML/WS-Fed)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'AppCatalog.Read.All' => [
                'desc'     => 'Teams-App-Katalog lesen',
                'features' => ['Teams Governance (App-Übersicht)', 'Teams Policies (App-Setup-Richtlinien)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            'Teamwork.Read.All' => [
                'desc'     => 'Tenant-weite Teams-Einstellungen lesen',
                'features' => ['Teams Governance', 'Teams Policies (Tenant-Einstellungen)'],
                'section'  => 'Sicherheit',
                'write'    => false,
            ],
            // ── Devices ──────────────────────────────────────────────
            'DeviceManagementManagedDevices.ReadWrite.All' => [
                'desc'     => 'Intune-Geräte lesen und verwalten',
                'features' => ['Geräte (Liste & Detail)', 'Gerät zurücksetzen (Retire)', 'Gerät wischen (Wipe)'],
                'section'  => 'Geräte & Compliance',
                'write'    => true,
            ],
            'DeviceManagementManagedDevices.PrivilegedOperations.All' => [
                'desc'     => 'Privilegierte Intune-Aktionen (Sync, Retire, Wipe)',
                'features' => ['Gerät synchronisieren (syncDevice)', 'Retire / Wipe'],
                'section'  => 'Geräte & Compliance',
                'write'    => true,
            ],
            'BitLockerKey.Read.All' => [
                'desc'     => 'BitLocker-Wiederherstellungsschlüssel lesen',
                'features' => ['Gerät: BitLocker-Schlüssel anzeigen'],
                'section'  => 'Geräte & Compliance',
                'write'    => false,
            ],
            // ── Information Protection / Compliance ───────────────────
            'InformationProtectionPolicy.Read.All' => [
                'desc'     => 'Information-Protection-Richtlinien & Sensitivity Labels lesen',
                'features' => ['Sensitivity Labels (Übersicht & Policy-Settings)', 'DLP-Richtlinien (Label-Übersicht)'],
                'section'  => 'Compliance & Schutz',
                'write'    => false,
            ],
            'eDiscovery.Read.All' => [
                'desc'     => 'eDiscovery-Fälle (Aufbewahrungsrichtlinien) lesen',
                'features' => ['Aufbewahrungsrichtlinien (eDiscovery-Fälle)'],
                'section'  => 'Compliance & Schutz',
                'write'    => false,
            ],
            // ── Domain & Tenant-Konfiguration ─────────────────────────
            'Domain.Read.All' => [
                'desc'     => 'Domains des Tenants lesen',
                'features' => ['Domain Health (DNS/DKIM/DMARC-Checks)'],
                'section'  => 'Administration',
                'write'    => false,
            ],
            // ── Service Health / MessageCenter ────────────────────────
            'ServiceHealth.Read.All' => [
                'desc'     => 'Dienststatus lesen',
                'features' => ['Dienststatus', 'Mail Flow & Schutz (Exchange-Status)', 'Message Center'],
                'section'  => 'Administration',
                'write'    => false,
            ],
            'ServiceMessage.Read.All' => [
                'desc'     => 'Message Center Nachrichten lesen',
                'features' => ['Message Center'],
                'section'  => 'Administration',
                'write'    => false,
            ],
        ];
    }

    // ── Token extraction & decoding ────────────────────────────────────────

    public function getAccessToken(): string
    {
        // ReflectionProperty::setAccessible() is implicit since PHP 8.1 and
        // emits a deprecation warning in PHP 8.5+, so we just read the
        // private property directly via reflection.
        $rc     = new \ReflectionClass($this->graph);
        $tmProp = $rc->getProperty('tokenManager');
        /** @var \App\Auth\GraphTokenManager $tm */
        $tm = $tmProp->getValue($this->graph);
        return $tm->getToken();
    }

    private function decodeJwt(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return [];
        }
        $payload = $parts[1];
        // Base64url → base64
        $payload = str_pad(strtr($payload, '-_', '+/'), strlen($payload) + (4 - strlen($payload) % 4) % 4, '=');
        return json_decode(base64_decode($payload), true) ?? [];
    }

    // ── Public API ─────────────────────────────────────────────────────────

    public function getTokenInfo(): array
    {
        try {
            $token  = $this->getAccessToken();
            $claims = $this->decodeJwt($token);
            return [
                'app_id'    => $claims['appid'] ?? $claims['azp'] ?? '–',
                'tenant_id' => $claims['tid'] ?? '–',
                'expires'   => isset($claims['exp']) ? (new \DateTimeImmutable('@' . $claims['exp']))->setTimezone(new \DateTimeZone(date_default_timezone_get())) : null,
                'issued'    => isset($claims['iat']) ? (new \DateTimeImmutable('@' . $claims['iat']))->setTimezone(new \DateTimeZone(date_default_timezone_get())) : null,
                'roles'     => $claims['roles'] ?? [],
            ];
        } catch (\Throwable) {
            return ['app_id' => '–', 'tenant_id' => '–', 'expires' => null, 'issued' => null, 'roles' => []];
        }
    }

    public function getTenantName(): string
    {
        try {
            $org = $this->graph->get('/organization', ['$select' => 'displayName'], 'org_name', 3600);
            return $org['value'][0]['displayName'] ?? '–';
        } catch (\Throwable) {
            return '–';
        }
    }

    public function checkPermissions(): array
    {
        $info     = $this->getTokenInfo();
        $granted  = array_map('strtolower', $info['roles']);
        $required = self::getRequiredPermissions();

        $results = [];
        foreach ($required as $perm => $meta) {
            $has = in_array(strtolower($perm), $granted, true);
            $results[$perm] = array_merge($meta, [
                'granted' => $has,
                'perm'    => $perm,
            ]);
        }
        return $results;
    }

    public function getSummary(array $checked): array
    {
        $total   = count($checked);
        $granted = count(array_filter($checked, fn($r) => $r['granted']));
        $missing = $total - $granted;

        $missingWrite = count(array_filter($checked, fn($r) => !$r['granted'] && $r['write']));
        $missingRead  = $missing - $missingWrite;

        // Collect all affected features from missing permissions
        $affectedFeatures = [];
        foreach ($checked as $r) {
            if (!$r['granted']) {
                foreach ($r['features'] as $f) {
                    $affectedFeatures[$f] = true;
                }
            }
        }

        return [
            'total'             => $total,
            'granted'           => $granted,
            'missing'           => $missing,
            'missing_write'     => $missingWrite,
            'missing_read'      => $missingRead,
            'affected_features' => array_keys($affectedFeatures),
        ];
    }
}
