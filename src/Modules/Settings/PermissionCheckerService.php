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
                'desc'     => t('Benutzerprofile lesen'),
                'features' => [t('Benutzer (Liste & Detail)'), t('MFA-Methoden'), t('Passwort-Ablauf'), t('Inaktive Konten'), t('Sign-in-Log (Benutzerfilter)'), t('Offboarding'), t('Externe Weiterleitungen')],
                'section'  => t('Verzeichnis'),
                'write'    => false,
            ],
            'User.ReadWrite.All' => [
                'desc'     => t('Benutzer anlegen, bearbeiten und löschen'),
                'features' => [t('Onboarding (neuen Benutzer anlegen)'), t('Benutzerdaten ändern (Job-Titel, Abteilung, Telefon)')],
                'section'  => t('Verzeichnis'),
                'write'    => true,
            ],
            'User.EnableDisableAccount.All' => [
                'desc'     => t('Benutzerkonten aktivieren/deaktivieren'),
                'features' => [t('Benutzer aktivieren/deaktivieren'), t('Offboarding (Konto deaktivieren)')],
                'section'  => t('Verzeichnis'),
                'write'    => true,
            ],
            'User.ManageIdentities.All' => [
                'desc'     => t('Authentifizierungsmethoden verwalten'),
                'features' => [t('MFA zurücksetzen'), t('Anmeldesitzungen widerrufen')],
                'section'  => t('Verzeichnis'),
                'write'    => true,
            ],
            'UserAuthenticationMethod.ReadWrite.All' => [
                'desc'     => t('Authentifizierungsmethoden lesen und schreiben'),
                'features' => [t('MFA-Methoden (Detail)'), t('MFA zurücksetzen')],
                'section'  => t('Verzeichnis'),
                'write'    => true,
            ],
            'Directory.Read.All' => [
                'desc'     => t('Verzeichnis lesen (Gruppen, Rollen, Geräte)'),
                'features' => [t('Gruppen & Teams'), t('Admin-Rollen'), t('Gastbenutzer'), t('Lizenz-Berater'), t('App-Registrierungen')],
                'section'  => t('Verzeichnis'),
                'write'    => false,
            ],
            'Group.ReadWrite.All' => [
                'desc'     => t('Gruppen lesen und schreiben'),
                'features' => [t('Gruppe anlegen'), t('Gruppe löschen'), t('Mitglieder hinzufügen/entfernen'), t('Gruppenbesitzer verwalten')],
                'section'  => t('Verzeichnis'),
                'write'    => true,
            ],
            // ── Licenses ───────────────────────────────────────────────
            'Organization.Read.All' => [
                'desc'     => t('Organisationsinformationen lesen'),
                'features' => [t('Dashboard (Mandanteninfo)'), t('Lizenz-Ablauf (beta)')],
                'section'  => t('Lizenzen'),
                'write'    => false,
            ],
            'LicenseAssignment.ReadWrite.All' => [
                'desc'     => t('Lizenzen zuweisen und entfernen'),
                'features' => [t('Lizenz zuweisen'), t('Lizenz entfernen'), t('Offboarding (Lizenzen entfernen)')],
                'section'  => t('Lizenzen'),
                'write'    => true,
            ],
            // ── Storage & Sharing ──────────────────────────────────────
            'Sites.Read.All' => [
                'desc'     => t('SharePoint-Sites lesen'),
                'features' => [t('SharePoint'), t('OneDrive'), t('Freigaben')],
                'section'  => t('Speicher & Freigaben'),
                'write'    => false,
            ],
            'Files.ReadWrite.All' => [
                'desc'     => t('Dateien und Freigabeberechtigungen verwalten'),
                'features' => [t('Externe Freigabe widerrufen'), t('Freigaben-Monitor')],
                'section'  => t('Speicher & Freigaben'),
                'write'    => true,
            ],
            'SharePointTenantSettings.ReadWrite.All' => [
                'desc'     => t('SharePoint-Tenant-Einstellungen lesen und schreiben'),
                'features' => [t('Freigaberichtlinien lesen'), t('Freigaberichtlinien setzen'), t('Tenant-Härtung (Sharing-Toggles)')],
                'section'  => t('Speicher & Freigaben'),
                'write'    => true,
            ],
            'Policy.ReadWrite.Authorization' => [
                'desc'     => t('Authorization-Policy ändern (Gast-Einladungs-Regeln, App-Consent-Defaults, Gast-Rolle, User-Default-Permissions)'),
                'features' => [t('Security Center: Gast-Einladungen einschränken'), t('Security Center: Gast-Rolle & User-Standardrechte'), t('Security Center: App-Consent')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'Policy.ReadWrite.SecurityDefaults' => [
                'desc'     => t('Security Defaults ein-/ausschalten'),
                'features' => [t('Security Center: Security Defaults umschalten')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            // ── Exchange & Mailboxes ───────────────────────────────────
            'Mail.ReadBasic.All' => [
                'desc'     => t('Basisinfos zu Postfächern lesen'),
                'features' => [t('Postfächer (Liste)'), t('Postfach-Ordner')],
                'section'  => t('Exchange & Kommunikation'),
                'write'    => false,
            ],
            'Mail.Read' => [
                'desc'     => t('Mailbox-Inhalte und Inbox-Regeln tenant-weit lesen'),
                'features' => [t('Auto-Forward-Audit (Mailbox-Regeln scannen)')],
                'section'  => t('Exchange & Kommunikation'),
                'write'    => false,
            ],
            'MailboxSettings.ReadWrite' => [
                'desc'     => t('Postfacheinstellungen lesen und schreiben'),
                'features' => [t('Postfach-Detail'), t('Auto-Antwort setzen'), t('Weiterleitung setzen/entfernen'), t('Externe Weiterleitungen'), t('Freigegebene Postfächer')],
                'section'  => t('Exchange & Kommunikation'),
                'write'    => true,
            ],
            // ── Reports ───────────────────────────────────────────────
            'Reports.Read.All' => [
                'desc'     => t('Aktivitäts- und Nutzungsberichte lesen'),
                'features' => [t('Teams-Nutzung'), t('Adoptions-Report'), t('Inaktive Gruppen'), t('OneDrive-Bericht'), t('Security Posture (MFA-Registrierungsrate, SSPR-Adoption, Admin-MFA-Prüfung)')],
                'section'  => t('Berichte'),
                'write'    => false,
            ],
            'AuditLog.Read.All' => [
                'desc'     => t('Audit-Log und Anmeldeprotokolle lesen'),
                'features' => [t('Audit-Log'), t('Sign-in-Log'), t('Benutzer Sign-in-Verlauf')],
                'section'  => t('Berichte'),
                'write'    => false,
            ],
            // ── Security ──────────────────────────────────────────────
            'Policy.Read.All' => [
                'desc'     => t('Alle Richtlinien lesen (CA, Named Locations, Auth-Richtlinien)'),
                'features' => [t('Conditional Access (Übersicht & Analyse)'), t('Named Locations'), t('Authentifizierungsmethoden (Anzeige)'), t('Security Posture (Security Defaults, App-Zustimmungsrichtlinie, Gasteinladungsrichtlinie, CA-Sitzungssteuerung)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'Policy.ReadWrite.ConditionalAccess' => [
                'desc'     => t('Conditional Access Richtlinien lesen und schreiben'),
                'features' => [t('Sicherheit (CA-Richtlinien anzeigen)'), t('CA-Richtlinie aktivieren/deaktivieren')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'Policy.ReadWrite.AuthenticationMethod' => [
                'desc'     => t('Authentifizierungsmethoden-Richtlinie lesen und schreiben'),
                'features' => [t('Authentifizierungsmethoden aktivieren/deaktivieren (FIDO2, Authenticator, SMS, Voice …)')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'RoleManagementPolicy.Read.Directory' => [
                'desc'     => t('PIM-Rollenrichtlinien (Aktivierungsregeln) lesen'),
                'features' => [t('PIM-Einstellungen (MFA-/Begründungs-/Genehmigungspflicht, max. Aktivierungsdauer je Rolle)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'SecurityAlert.ReadWrite.All' => [
                'desc'     => t('Sicherheitswarnungen lesen und verwalten'),
                'features' => [t('Defender Alerts'), t('Mail Flow & Schutz (Alerts)'), t('Alert auflösen')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'IdentityRiskyUser.Read.All' => [
                'desc'     => t('Risiko-Benutzer lesen (Entra ID Protection)'),
                'features' => [t('Risiko-Anmeldungen (Liste der gefährdeten Benutzer)'), t('Dashboard-Kachel "Risikobenutzer"')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'IdentityRiskyUser.ReadWrite.All' => [
                'desc'     => t('Risiko-Benutzer verwalten (bestätigen / verwerfen)'),
                'features' => [t('Risiko bestätigen'), t('Risiko verwerfen')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'IdentityRiskEvent.Read.All' => [
                'desc'     => t('Risiko-Erkennungen lesen (Entra ID Protection)'),
                'features' => [t('Risiko-Anmeldungen (Erkennungen / Risk Detections)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'SecurityEvents.Read.All' => [
                'desc'     => t('Sicherheitsereignisse und Secure Score lesen'),
                'features' => [t('Secure Score'), t('Security Posture')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'RoleManagement.Read.Directory' => [
                'desc'     => t('Admin-Rollenzuweisungen + PIM lesen'),
                'features' => [t('PIM-Übersicht (JIT-Admin)'), t('Admin-Rollen lesen (read-only)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'RoleManagement.ReadWrite.Directory' => [
                'desc'     => t('Admin-Rollenzuweisungen lesen und schreiben'),
                'features' => [t('Admin-Rollen (Übersicht)'), t('Admin-Rolle zuweisen'), t('Admin-Rolle entfernen'), t('Security Posture (Admin-MFA-Prüfung, PIM-Adoption)')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'Application.Read.All' => [
                'desc'     => t('App-Registrierungen und Service-Principals lesen'),
                'features' => [t('OAuth-App-Audit (Enterprise Apps Inventur + Risk-Score)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'Application.ReadWrite.All' => [
                'desc'     => t('App-Registrierungen lesen und verwalten'),
                'features' => [t('App-Registrierungen (Detail)'), t('App-Secret hinzufügen'), t('App-Secret löschen'), t('Enterprise Apps (Service Principals)')],
                'section'  => t('Sicherheit'),
                'write'    => true,
            ],
            'AttackSimulation.Read.All' => [
                'desc'     => t('Defender Attack-Simulation-Daten lesen'),
                'features' => [t('Phishing-Simulationen (durchgeführte Kampagnen, Klick-/Compromised-Quote)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'LifecycleWorkflows.Read.All' => [
                'desc'     => t('Entra-ID-Governance-Lifecycle-Workflows lesen'),
                'features' => [t('Lifecycle-Workflows-Übersicht (Joiner/Mover/Leaver)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'IdentityProvider.Read.All' => [
                'desc'     => t('Externe Identity Providers lesen'),
                'features' => [t('Identity Provider Trust (Google, Facebook, SAML/WS-Fed)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'AppCatalog.Read.All' => [
                'desc'     => t('Teams-App-Katalog lesen'),
                'features' => [t('Teams Governance (App-Übersicht)'), t('Teams Policies (App-Setup-Richtlinien)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            'Teamwork.Read.All' => [
                'desc'     => t('Tenant-weite Teams-Einstellungen lesen'),
                'features' => [t('Teams Governance'), t('Teams Policies (Tenant-Einstellungen)')],
                'section'  => t('Sicherheit'),
                'write'    => false,
            ],
            // ── Devices ──────────────────────────────────────────────
            'DeviceManagementManagedDevices.ReadWrite.All' => [
                'desc'     => t('Intune-Geräte lesen und verwalten'),
                'features' => [t('Geräte (Liste & Detail)'), t('Gerät zurücksetzen (Retire)'), t('Gerät wischen (Wipe)')],
                'section'  => t('Geräte & Compliance'),
                'write'    => true,
            ],
            'DeviceManagementManagedDevices.PrivilegedOperations.All' => [
                'desc'     => t('Privilegierte Intune-Aktionen (Sync, Retire, Wipe)'),
                'features' => [t('Gerät synchronisieren (syncDevice)'), t('Retire / Wipe')],
                'section'  => t('Geräte & Compliance'),
                'write'    => true,
            ],
            'BitLockerKey.Read.All' => [
                'desc'     => t('BitLocker-Wiederherstellungsschlüssel lesen'),
                'features' => [t('Gerät: BitLocker-Schlüssel anzeigen')],
                'section'  => t('Geräte & Compliance'),
                'write'    => false,
            ],
            // ── Information Protection / Compliance ───────────────────
            'InformationProtectionPolicy.Read.All' => [
                'desc'     => t('Information-Protection-Richtlinien & Sensitivity Labels lesen'),
                'features' => [t('Sensitivity Labels (Übersicht & Policy-Settings)'), t('DLP-Richtlinien (Label-Übersicht)')],
                'section'  => t('Compliance & Schutz'),
                'write'    => false,
            ],
            'eDiscovery.Read.All' => [
                'desc'     => t('eDiscovery-Fälle (Aufbewahrungsrichtlinien) lesen'),
                'features' => [t('Aufbewahrungsrichtlinien (eDiscovery-Fälle)')],
                'section'  => t('Compliance & Schutz'),
                'write'    => false,
            ],
            // ── Domain & Tenant-Konfiguration ─────────────────────────
            'Domain.Read.All' => [
                'desc'     => t('Domains des Tenants lesen'),
                'features' => [t('Domain Health (DNS/DKIM/DMARC-Checks)')],
                'section'  => t('Administration'),
                'write'    => false,
            ],
            // ── Service Health / MessageCenter ────────────────────────
            'ServiceHealth.Read.All' => [
                'desc'     => t('Dienststatus lesen'),
                'features' => [t('Dienststatus'), t('Mail Flow & Schutz (Exchange-Status)'), t('Message Center')],
                'section'  => t('Administration'),
                'write'    => false,
            ],
            'ServiceMessage.Read.All' => [
                'desc'     => t('Message Center Nachrichten lesen'),
                'features' => [t('Message Center')],
                'section'  => t('Administration'),
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
