<?php

/**
 * English translations for the Security Posture backend layer
 * (SecurityPostureService) — check display labels/descriptions, status detail
 * strings and the prioritized recommendations generated in PHP.
 *
 * Note: the 'category' values (e.g. 'Identität & MFA', 'Conditional Access')
 * are intentionally NOT translated here. They are used as comparison keys in
 * views/securityposture/index.php ($categoryIcons lookup and $catOrder sort
 * ordering) and translating them at the source would break that logic. They
 * are displayed in the view via $e($category) and would need view-level
 * handling instead.
 *
 * Dynamic numeric/string values are passed via :param placeholders.
 *
 * @return array<string,string>
 */
return [

    // ---------------------------------------------------------------------
    // Recommendations (getRecommendations) — title / description / action
    // ---------------------------------------------------------------------

    // ca_mfa_all_users
    'MFA für alle Benutzer erzwingen' => 'Enforce MFA for all users',
    'Keine aktive Conditional-Access-Richtlinie verlangt MFA für alle Benutzer. Ohne diese Richtlinie können Konten allein durch gestohlene Passwörter kompromittiert werden.'
        => 'No active Conditional Access policy requires MFA for all users. Without this policy, accounts can be compromised through stolen passwords alone.',
    'Richtlinie erstellen' => 'Create policy',
    'MFA-Richtlinie aktivieren (Report-Modus)' => 'Enable MFA policy (report-only mode)',
    'Eine MFA-Richtlinie ist im Report-Modus vorhanden, erzwingt MFA aber noch nicht. Aktivierung erforderlich.'
        => 'An MFA policy exists in report-only mode but is not yet enforcing MFA. Activation required.',
    'Zu CA-Richtlinien' => 'Go to CA policies',

    // ca_admin_mfa
    'Administratoren durch dedizierte MFA-Richtlinie schützen' => 'Protect administrators with a dedicated MFA policy',
    'Keine aktive CA-Richtlinie erzwingt MFA explizit für Admin-Rollen. Administratorkonten sind besonders hochwertige Angriffsziele.'
        => 'No active CA policy explicitly enforces MFA for admin roles. Administrator accounts are especially high-value attack targets.',

    // legacy_auth_blocked
    'Legacy-Authentifizierung blockieren' => 'Block legacy authentication',
    'Ältere Protokolle (IMAP, POP3, SMTP AUTH, MAPI) umgehen MFA vollständig. Über 99% der Passwort-Spray-Angriffe nutzen Legacy-Auth.'
        => 'Legacy protocols (IMAP, POP3, SMTP AUTH, MAPI) bypass MFA entirely. Over 99% of password-spray attacks use legacy authentication.',
    'Legacy-Auth-Blockierung aktivieren' => 'Enable legacy authentication blocking',
    'Die Richtlinie zum Blockieren von Legacy-Authentifizierung ist nur im Report-Modus. Vollständige Aktivierung erforderlich.'
        => 'The policy to block legacy authentication is in report-only mode. Full activation required.',

    // sign_in_risk_policy
    'Risikobasierte Anmelderichtlinie einrichten' => 'Set up a risk-based sign-in policy',
    'Keine CA-Richtlinie reagiert auf Anmelderisiken (verdächtige IPs, unmögliche Reisen). Microsoft Entra erkennt diese Muster automatisch, sie werden aber nicht genutzt.'
        => 'No CA policy responds to sign-in risks (suspicious IPs, impossible travel). Microsoft Entra detects these patterns automatically, but they are not being used.',

    // user_risk_policy
    'Benutzerrisiko-Richtlinie einrichten' => 'Set up a user-risk policy',
    'Keine CA-Richtlinie reagiert auf hohes Benutzerrisiko (geleakte Credentials, kompromittierte Konten). Betroffene Benutzer können ungehindert weiterarbeiten.'
        => 'No CA policy responds to high user risk (leaked credentials, compromised accounts). Affected users can continue working unhindered.',

    // mfa_registration_rate
    'MFA-Registrierungsrate erhöhen' => 'Increase MFA registration rate',
    'Weniger als 75% der Benutzer haben MFA registriert. Kampagne zur MFA-Einrichtung starten oder Registrierung per CA erzwingen.'
        => 'Fewer than 75% of users have registered MFA. Launch an MFA enrollment campaign or enforce registration via CA.',
    'MFA-Übersicht' => 'MFA overview',
    'MFA-Registrierungsrate verbessern' => 'Improve MFA registration rate',
    'Die MFA-Registrierungsrate liegt unter 95%. Nicht registrierte Benutzer identifizieren und zur Registrierung auffordern.'
        => 'The MFA registration rate is below 95%. Identify unregistered users and prompt them to register.',
    'Nicht registrierte anzeigen' => 'Show unregistered',

    // risky_users_open
    'Risikobehaftete Benutzer untersuchen' => 'Investigate risky users',
    'Mehrere Benutzer haben einen aktiven Risikostatus (atRisk). Diese Konten sind möglicherweise kompromittiert und benötigen sofortige Überprüfung.'
        => 'Several users have an active risk state (atRisk). These accounts may be compromised and require immediate review.',
    'Risikobenutzer anzeigen' => 'Show risky users',
    'Risikobenutzer überprüfen' => 'Review risky users',
    'Einige Benutzer haben einen aktiven Risikostatus. Überprüfung und ggf. Kennwortänderung empfohlen.'
        => 'Some users have an active risk state. Review and, if necessary, a password change is recommended.',

    // device_compliance_rate
    'Geräte-Compliance-Rate verbessern' => 'Improve device compliance rate',
    'Mehr als 20% der verwalteten Geräte sind nicht konform. Nicht konforme Geräte sollten keinen Zugriff auf Unternehmensressourcen erhalten.'
        => 'More than 20% of managed devices are non-compliant. Non-compliant devices should not be granted access to corporate resources.',
    'Geräte anzeigen' => 'Show devices',

    // ca_device_compliance
    'Gerätekonformität in Conditional Access erzwingen' => 'Enforce device compliance in Conditional Access',
    'Keine CA-Richtlinie verlangt konforme oder Hybrid-Azure-AD-joinete Geräte. Ermöglicht Zugriff von unverwalteten Privatgeräten.'
        => 'No CA policy requires compliant or Hybrid Azure AD joined devices. This allows access from unmanaged personal devices.',

    // defender_alerts
    'Offene Defender-Alerts bearbeiten' => 'Resolve open Defender alerts',
    'Es gibt viele ungelöste Microsoft Defender-Sicherheitswarnungen. Alerts sollten zeitnah untersucht und geschlossen werden.'
        => 'There are many unresolved Microsoft Defender security alerts. Alerts should be investigated and closed promptly.',
    'Alerts anzeigen' => 'Show alerts',
    'Offene Defender-Alerts prüfen' => 'Review open Defender alerts',
    'Einige ungelöste Microsoft Defender-Sicherheitswarnungen vorhanden.'
        => 'Some unresolved Microsoft Defender security alerts are present.',

    // secure_score
    'Microsoft Secure Score verbessern' => 'Improve Microsoft Secure Score',
    'Der Secure Score liegt unter 30%. Microsoft empfiehlt konkrete Maßnahmen im Security-Portal. Höchst-Priorität-Empfehlungen zuerst umsetzen.'
        => 'The Secure Score is below 30%. Microsoft recommends specific actions in the Security portal. Implement the highest-priority recommendations first.',
    'Sicherheitsmodul' => 'Security module',
    'Secure Score weiter verbessern' => 'Further improve Secure Score',
    'Der Secure Score liegt unter 50%. Weitere Empfehlungen aus dem Microsoft Security Center umsetzen.'
        => 'The Secure Score is below 50%. Implement further recommendations from the Microsoft Security Center.',

    // admin_count
    'Anzahl globaler Administratoren reduzieren' => 'Reduce the number of Global Administrators',
    'Mehr als 5 aktive globale Administratoren erhöhen das Angriffsrisiko erheblich. Microsoft empfiehlt max. 2-4 globale Admins und die Nutzung von Least-Privilege-Rollen.'
        => 'More than 5 active Global Administrators significantly increase the attack risk. Microsoft recommends a maximum of 2-4 Global Admins and the use of least-privilege roles.',
    'Benutzerrollen prüfen' => 'Review user roles',
    'Globale Administratoren auf Notwendigkeit prüfen' => 'Review Global Administrators for necessity',
    'Es gibt 3-5 globale Administratoren. Prüfen ob Least-Privilege-Rollen ausreichen würden.'
        => 'There are 3-5 Global Administrators. Check whether least-privilege roles would suffice.',

    // named_locations
    'Vertrauenswürdige Standorte konfigurieren' => 'Configure trusted locations',
    'Keine Named Locations konfiguriert. Vertrauenswürdige IP-Bereiche und Länder ermöglichen differenziertere CA-Richtlinien.'
        => 'No named locations configured. Trusted IP ranges and countries enable more granular CA policies.',
    'Standorte konfigurieren' => 'Configure locations',

    // app_secrets_expiry
    'Abgelaufene App-Secrets erneuern' => 'Renew expired app secrets',
    'Abgelaufene App-Secrets können Dienste unterbrechen oder eine Sicherheitslücke darstellen wenn Rotationszyklen nicht eingehalten werden.'
        => 'Expired app secrets can disrupt services or pose a security gap when rotation cycles are not observed.',
    'App-Registrierungen' => 'App registrations',
    'App-Secrets bald ablaufend — erneuern' => 'App secrets expiring soon — renew',
    'Einige App-Secrets laufen in weniger als 30 Tagen ab. Jetzt erneuern um Dienstunterbrechungen zu vermeiden.'
        => 'Some app secrets expire in less than 30 days. Renew them now to avoid service disruptions.',

    // no_stale_licensed
    'Inaktive lizenzierte Konten bereinigen' => 'Clean up inactive licensed accounts',
    'Mehrere aktive, lizenzierte Benutzer haben sich seit über 90 Tagen nicht angemeldet. Ungenutzte Konten sollten deaktiviert und Lizenzen freigegeben werden.'
        => 'Several active, licensed users have not signed in for more than 90 days. Unused accounts should be disabled and their licenses reclaimed.',
    'Benutzer prüfen' => 'Review users',
    'Inaktive Benutzerkonten überprüfen' => 'Review inactive user accounts',
    'Einige lizenzierte Benutzer waren über 90 Tage inaktiv.'
        => 'Some licensed users have been inactive for more than 90 days.',

    // guest_user_count
    'Gastbenutzer überprüfen und bereinigen' => 'Review and clean up guest users',
    'Viele aktive Gastbenutzer können das Risiko unbeabsichtigter Datenweitergabe erhöhen. Regelmäßige Zugriffsüberprüfungen (Access Reviews) empfohlen.'
        => 'Many active guest users can increase the risk of unintended data sharing. Regular access reviews are recommended.',
    'Benutzer anzeigen' => 'Show users',

    // passwordless_capable
    'Passwortlose Authentifizierung einführen' => 'Adopt passwordless authentication',
    'Noch keine Benutzer haben passwortlose Methoden (FIDO2, Windows Hello, Microsoft Authenticator Passwordless) registriert. Diese sind phishing-sicherer als klassische MFA.'
        => 'No users have yet registered passwordless methods (FIDO2, Windows Hello, Microsoft Authenticator passwordless). These are more phishing-resistant than classic MFA.',
    'MFA-Methoden' => 'MFA methods',

    // security_defaults
    'Basis-Schutz fehlt — weder Security Defaults noch CA aktiv' => 'Baseline protection missing — neither Security Defaults nor CA active',
    'Weder Security Defaults noch Conditional-Access-Richtlinien sind aktiv. Der Tenant hat keinen automatisierten Basisschutz gegen gängige Angriffe.'
        => 'Neither Security Defaults nor Conditional Access policies are active. The tenant has no automated baseline protection against common attacks.',
    'CA-Richtlinien einrichten' => 'Set up CA policies',
    'Security Defaults und CA gleichzeitig aktiv' => 'Security Defaults and CA active at the same time',
    'Security Defaults und eigene CA-Richtlinien sind gleichzeitig aktiv. Dies kann zu Konflikten führen. Security Defaults deaktivieren und vollständig auf CA setzen.'
        => 'Security Defaults and custom CA policies are active at the same time. This can lead to conflicts. Disable Security Defaults and rely fully on CA.',

    // admins_mfa_registered
    'Globale Admins ohne MFA-Registrierung' => 'Global Admins without MFA registration',
    'Mindestens ein globaler Administrator hat keine MFA-Methode registriert. Admin-Konten ohne MFA sind das größte Einzelrisiko in einem M365-Tenant.'
        => 'At least one Global Administrator has no MFA method registered. Admin accounts without MFA are the single greatest risk in an M365 tenant.',
    'MFA-Status prüfen' => 'Check MFA status',
    'Admin-MFA-Status überprüfen' => 'Review admin MFA status',
    'MFA-Daten für globale Administratoren konnten nicht vollständig verifiziert werden.'
        => 'MFA data for Global Administrators could not be fully verified.',

    // app_consent_policy
    'Benutzer dürfen beliebigen Apps zustimmen' => 'Users may consent to any apps',
    'Die aktuelle App-Zustimmungsrichtlinie erlaubt Benutzern, OAuth-Berechtigungen an Drittanbieter-Apps zu vergeben. Dies ermöglicht OAuth-Phishing-Angriffe (Consent Phishing).'
        => 'The current app consent policy allows users to grant OAuth permissions to third-party apps. This enables OAuth phishing attacks (consent phishing).',
    'Richtlinie prüfen' => 'Review policy',
    'App-Zustimmungsrichtlinie einschränken' => 'Restrict the app consent policy',
    'Benutzer können bestimmten Apps ohne Admin-Genehmigung zustimmen. Admin-Consent-Workflow aktivieren um alle Zustimmungen zu kontrollieren.'
        => 'Users can consent to certain apps without admin approval. Enable the admin consent workflow to control all consent grants.',

    // external_collab_policy
    'Einladungsrichtlinie für Gäste einschränken' => 'Restrict the guest invitation policy',
    'Jeder (auch externe Gäste) kann neue Gäste in den Tenant einladen. Einladungen sollten auf Admins und Gast-Einlader begrenzt werden.'
        => 'Anyone (including external guests) can invite new guests into the tenant. Invitations should be limited to admins and guest inviters.',
    'Gasteinladungen nur durch Admins erlauben' => 'Allow guest invitations by admins only',
    'Alle Benutzer dürfen Gäste einladen. Empfehlung: nur Admins und dedizierte Gast-Einlader.'
        => 'All users may invite guests. Recommendation: admins and dedicated guest inviters only.',

    // sspr_adoption
    'Self-Service Password Reset (SSPR) einführen' => 'Adopt Self-Service Password Reset (SSPR)',
    'Kein Benutzer hat SSPR registriert. SSPR reduziert Helpdesk-Aufwand und verhindert dass Benutzer unsichere Passwort-Reset-Wege nutzen.'
        => 'No user has registered for SSPR. SSPR reduces helpdesk workload and prevents users from using insecure password-reset methods.',
    'SSPR-Registrierungsrate verbessern' => 'Improve SSPR registration rate',
    'Weniger als 50% der Benutzer haben SSPR registriert. Fehlende Registrierungen erhöhen Helpdesk-Last.'
        => 'Fewer than 50% of users have registered for SSPR. Missing registrations increase the helpdesk load.',

    // ca_session_controls
    'Sitzungslebensdauer einschränken (CA)' => 'Restrict session lifetime (CA)',
    'Keine CA-Richtlinie erzwingt eine maximale Sitzungsdauer oder verhindert persistente Browser-Sitzungen. Lang lebende Tokens erhöhen das Risiko bei gestohlenen Refresh-Tokens.'
        => 'No CA policy enforces a maximum session duration or prevents persistent browser sessions. Long-lived tokens increase the risk from stolen refresh tokens.',

    // pim_adoption
    'Privileged Identity Management (PIM) einführen' => 'Adopt Privileged Identity Management (PIM)',
    'Keine PIM-berechtigten Rollenzuweisungen gefunden. PIM ermöglicht Just-in-Time-Zugriff für Admin-Rollen — Admins sind nur aktiv wenn nötig, mit Genehmigungsprozess und Audit-Trail.'
        => 'No PIM-eligible role assignments found. PIM enables just-in-time access for admin roles — admins are active only when needed, with an approval process and audit trail.',
    'Benutzer & Rollen' => 'Users & roles',

    // break_glass
    'Notfallzugangskonto (Break-Glass) fehlt' => 'Emergency access account (break-glass) missing',
    'Kein globales Admin-Konto ohne Lizenz gefunden. Microsoft empfiehlt mindestens 2 dedizierte Notfallkonten: cloud-only, kein MFA, keine Lizenz, starkes Passwort offline hinterlegt — für den Fall dass MFA oder CA nicht funktionieren.'
        => 'No Global Admin account without a license found. Microsoft recommends at least 2 dedicated emergency accounts: cloud-only, no MFA, no license, with a strong password stored offline — in case MFA or CA fail.',
    'Admin-Rollen prüfen' => 'Review admin roles',
    'Nur 1 Notfallkonto konfiguriert' => 'Only 1 emergency account configured',
    'Nur ein potenzielles Notfallkonto gefunden. Microsoft empfiehlt mindestens 2 unabhängige Break-Glass-Konten für Redundanz.'
        => 'Only one potential emergency account found. Microsoft recommends at least 2 independent break-glass accounts for redundancy.',

    // defender_for_office
    'Defender for Office 365 nicht lizenziert' => 'Defender for Office 365 not licensed',
    'Kein Microsoft Defender for Office 365 Abonnement gefunden. Safe Links, Safe Attachments und Anti-Phishing-Schutz sind nicht verfügbar — ein kritisches Sicherheitsrisiko für E-Mail-Angriffe.'
        => 'No Microsoft Defender for Office 365 subscription found. Safe Links, Safe Attachments and anti-phishing protection are unavailable — a critical security risk for email-based attacks.',
    'Lizenzen prüfen' => 'Review licenses',

    // ---------------------------------------------------------------------
    // Check base labels & descriptions
    // ---------------------------------------------------------------------

    // mfa_registration_rate
    'MFA-Registrierungsrate' => 'MFA registration rate',
    'Anteil der Benutzer mit registrierter MFA-Methode.' => 'Share of users with a registered MFA method.',

    // ca_mfa_all_users
    'MFA für alle Benutzer (CA)' => 'MFA for all users (CA)',
    'Aktive CA-Richtlinie, die MFA für alle oder die meisten Benutzer verlangt.'
        => 'Active CA policy requiring MFA for all or most users.',

    // ca_admin_mfa
    'MFA für Administratoren (CA)' => 'MFA for administrators (CA)',
    'Aktive CA-Richtlinie, die MFA explizit für Admin-Rollen verlangt.'
        => 'Active CA policy requiring MFA explicitly for admin roles.',

    // passwordless_capable
    'Passwortlose Authentifizierung' => 'Passwordless authentication',
    'Mindestens ein Benutzer nutzt FIDO2, Windows Hello oder Authenticator Passwordless.'
        => 'At least one user uses FIDO2, Windows Hello or Authenticator passwordless.',

    // risky_users_open
    'Risikobenutzer (atRisk)' => 'Risky users (atRisk)',
    'Anzahl der Benutzer mit aktivem Risikostatus in Entra Identity Protection.'
        => 'Number of users with an active risk state in Entra Identity Protection.',

    // legacy_auth_blocked
    'Legacy-Authentifizierung blockiert' => 'Legacy authentication blocked',
    'CA-Richtlinie blockiert ältere Protokolle (IMAP, POP3, SMTP AUTH, MAPI).'
        => 'CA policy blocks legacy protocols (IMAP, POP3, SMTP AUTH, MAPI).',

    // sign_in_risk_policy
    'Anmelderisiko-Richtlinie (CA)' => 'Sign-in risk policy (CA)',
    'Aktive CA-Richtlinie reagiert auf mittleres/hohes Anmelderisiko.'
        => 'Active CA policy responds to medium/high sign-in risk.',

    // user_risk_policy
    'Benutzerrisiko-Richtlinie (CA)' => 'User-risk policy (CA)',
    'Aktive CA-Richtlinie reagiert auf hohes Benutzerrisiko (kompromittierte Konten).'
        => 'Active CA policy responds to high user risk (compromised accounts).',

    // ca_device_compliance
    'Gerätekonformität in CA' => 'Device compliance in CA',
    'CA-Richtlinie verlangt konforme oder Hybrid-AD-joinete Geräte.'
        => 'CA policy requires compliant or Hybrid AD joined devices.',

    // ca_guest_restriction
    'Gastbenutzer-CA-Richtlinie' => 'Guest-user CA policy',
    'Aktive CA-Richtlinie mit speziellen Bedingungen für Gastbenutzer.'
        => 'Active CA policy with specific conditions for guest users.',

    // device_compliance_rate
    'Geräte-Compliance-Rate' => 'Device compliance rate',
    'Anteil der verwalteten Intune-Geräte, die konform sind.'
        => 'Share of managed Intune devices that are compliant.',

    // defender_alerts
    'Offene Defender-Alerts' => 'Open Defender alerts',
    'Anzahl ungelöster Microsoft Defender-Sicherheitswarnungen.'
        => 'Number of unresolved Microsoft Defender security alerts.',

    // secure_score
    'Microsoft Secure Score' => 'Microsoft Secure Score',
    'Secure Score als Prozentwert des erreichbaren Maximums (Ziel: >50%).'
        => 'Secure Score as a percentage of the achievable maximum (target: >50%).',

    // admin_count
    'Globale Administratoren' => 'Global Administrators',
    'Anzahl der Benutzer mit der Rolle "Globaler Administrator" (Ziel: max. 4).'
        => 'Number of users with the "Global Administrator" role (target: max. 4).',

    // named_locations
    'Named Locations konfiguriert' => 'Named locations configured',
    'Mindestens ein vertrauenswürdiger Standort (IP oder Land) ist konfiguriert.'
        => 'At least one trusted location (IP or country) is configured.',

    // app_secrets_expiry
    'App-Secrets Ablaufdatum' => 'App secret expiry',
    'App-Registrierungen ohne abgelaufene oder bald ablaufende Secrets.'
        => 'App registrations without expired or soon-to-expire secrets.',

    // no_stale_licensed
    'Inaktive lizenzierte Konten' => 'Inactive licensed accounts',
    'Aktive, lizenzierte Benutzer ohne Anmeldung seit mehr als 90 Tagen.'
        => 'Active, licensed users with no sign-in for more than 90 days.',

    // guest_user_count
    'Gastbenutzer' => 'Guest users',
    'Anzahl aktiver Gastbenutzer — sollte regelmäßig überprüft werden.'
        => 'Number of active guest users — should be reviewed regularly.',

    // security_defaults
    'Security Defaults vs. CA' => 'Security Defaults vs. CA',
    'Security Defaults und Conditional Access sollten nicht gleichzeitig aktiv sein.'
        => 'Security Defaults and Conditional Access should not be active at the same time.',

    // admins_mfa_registered
    'Alle Admins haben MFA' => 'All admins have MFA',
    'Alle globalen Administratoren haben eine MFA-Methode registriert.'
        => 'All Global Administrators have a registered MFA method.',

    // sspr_adoption
    'Self-Service Password Reset (SSPR)' => 'Self-Service Password Reset (SSPR)',
    'Anteil der Benutzer mit registrierter SSPR-Methode.'
        => 'Share of users with a registered SSPR method.',

    // ca_session_controls
    'CA-Sitzungssteuerung' => 'CA session controls',
    'CA-Richtlinie begrenzt Sitzungsdauer oder verhindert persistente Browser-Sitzungen.'
        => 'CA policy limits session duration or prevents persistent browser sessions.',

    // pim_adoption
    'Privileged Identity Management (PIM)' => 'Privileged Identity Management (PIM)',
    'Just-in-Time Admin-Zugriff durch PIM-berechtigte Rollenzuweisungen.'
        => 'Just-in-time admin access through PIM-eligible role assignments.',

    // app_consent_policy
    'App-Zustimmungsrichtlinie' => 'App consent policy',
    'Benutzer dürfen nicht ohne Admin-Genehmigung OAuth-Berechtigungen vergeben.'
        => 'Users must not grant OAuth permissions without admin approval.',

    // external_collab_policy
    'Gasteinladungsrichtlinie' => 'Guest invitation policy',
    'Wer darf externe Gastbenutzer in den Tenant einladen.'
        => 'Who is allowed to invite external guest users into the tenant.',

    // break_glass
    'Notfallzugangskonto konfiguriert' => 'Emergency access account configured',
    'Mindestens 2 globale Admin-Konten ohne Lizenz und ohne On-Premises-Sync vorhanden (Break-Glass-Muster).'
        => 'At least 2 Global Admin accounts without a license and without on-premises sync are present (break-glass pattern).',

    // defender_for_office
    'Defender for Office 365 lizenziert' => 'Defender for Office 365 licensed',
    'Microsoft Defender for Office 365 (Safe Links, Safe Attachments, Anti-Phishing) ist aktiv.'
        => 'Microsoft Defender for Office 365 (Safe Links, Safe Attachments, anti-phishing) is active.',

    // gdpr_tenant_region
    'Tenant-Region in EU/EWR' => 'Tenant region in EU/EEA',
    'Der Tenant-Standort bestimmt, in welcher Datacenter-Region M365-Daten primär gespeichert werden. EU-Standort ist für DSGVO-konforme Verarbeitung relevant.'
        => 'The tenant location determines the data center region where M365 data is primarily stored. An EU location is relevant for GDPR-compliant processing.',

    // gdpr_sharepoint_sharing
    'SharePoint External Sharing restriktiv' => 'SharePoint external sharing restrictive',
    'Die Tenant-weite Freigabe-Einstellung sollte externe Freigabe einschränken — Anyone-Links sind DSGVO-kritisch (Art. 25 Privacy by Default).'
        => 'The tenant-wide sharing setting should restrict external sharing — Anyone links are GDPR-critical (Art. 25 privacy by default).',

    // gdpr_anonymous_link_expiry
    'Anonyme Freigabe-Links laufen ab' => 'Anonymous sharing links expire',
    'Anyone-Links ohne Ablaufdatum verletzen Speicherbegrenzung (Art. 5 Abs. 1e DSGVO). Empfehlung: ≤ 90 Tage.'
        => 'Anyone links without an expiry date violate storage limitation (Art. 5(1)(e) GDPR). Recommendation: ≤ 90 days.',

    // gdpr_default_sharing_link
    'Standard-Freigabetyp ist intern' => 'Default sharing type is internal',
    'Der Default-Linktyp sollte „internal" oder „direct" (named) sein — Anyone als Standard begünstigt versehentliche Datenweitergabe.'
        => 'The default link type should be "internal" or "direct" (named) — Anyone as the default encourages accidental data sharing.',

    // gdpr_sensitivity_labels
    'Sensitivity Labels veröffentlicht' => 'Sensitivity labels published',
    'Vertraulichkeitsbezeichnungen sind Voraussetzung für Information-Protection (Art. 32 DSGVO Maßnahmen zur Datenintegrität).'
        => 'Sensitivity labels are a prerequisite for information protection (Art. 32 GDPR measures for data integrity).',

    // gdpr_dlp_or_labels
    'DLP-/Label-Schutz für personenbezogene Daten' => 'DLP/label protection for personal data',
    'Mindestens eine Information-Protection-Schutzmaßnahme (Sensitivity Label aktiv) ist erforderlich (Art. 25 + Art. 32 DSGVO).'
        => 'At least one information protection measure (an active sensitivity label) is required (Art. 25 + Art. 32 GDPR).',

    // gdpr_retention_policies
    'Aufbewahrungs-/eDiscovery-Fälle aktiv' => 'Retention/eDiscovery cases active',
    'Aufbewahrungsrichtlinien sind nötig für Speicherbegrenzung & Auskunfts-/Löschpflichten (Art. 5 + Art. 17 DSGVO).'
        => 'Retention policies are required for storage limitation and access/erasure obligations (Art. 5 + Art. 17 GDPR).',

    // gdpr_audit_log
    'Audit-Log aktiv & abrufbar' => 'Audit log active & retrievable',
    'Ohne Audit-Log keine Nachvollziehbarkeit von Datenzugriffen (Art. 32 DSGVO, Rechenschaftspflicht).'
        => 'Without an audit log there is no traceability of data access (Art. 32 GDPR, accountability).',

    // ---------------------------------------------------------------------
    // Status detail strings
    // ---------------------------------------------------------------------

    'Berechtigung fehlt oder API-Fehler.' => 'Permission missing or API error.',

    // mfa_registration_rate
    ':registered/:total Benutzer haben MFA registriert (:rate%).'
        => ':registered/:total users have registered MFA (:rate%).',
    'MFA-Registrierungsrate: :rate% (:registered/:total) — Ziel ist ≥95%.'
        => 'MFA registration rate: :rate% (:registered/:total) — target is ≥95%.',
    'Niedrige MFA-Registrierungsrate: :rate% (:registered/:total).'
        => 'Low MFA registration rate: :rate% (:registered/:total).',

    // ca_mfa_all_users
    'Aktive CA-Richtlinie verlangt MFA für alle Benutzer.'
        => 'Active CA policy requires MFA for all users.',
    'MFA-Richtlinie existiert, ist aber nur im Report-Modus — noch nicht aktiv.'
        => 'An MFA policy exists but is in report-only mode — not yet active.',
    'Keine aktive CA-Richtlinie erzwingt MFA für alle Benutzer.'
        => 'No active CA policy enforces MFA for all users.',

    // ca_admin_mfa
    'Aktive CA-Richtlinie verlangt MFA für Admin-Rollen oder alle Benutzer.'
        => 'Active CA policy requires MFA for admin roles or all users.',
    'Keine aktive CA-Richtlinie schützt explizit Admin-Rollen mit MFA.'
        => 'No active CA policy explicitly protects admin roles with MFA.',

    // passwordless_capable
    ':capable von :total Benutzer(n) sind für passwortlose Anmeldung registriert.'
        => ':capable of :total user(s) are registered for passwordless sign-in.',
    'Noch kein Benutzer hat eine passwortlose Methode (FIDO2, Windows Hello, Passwordless) registriert.'
        => 'No user has yet registered a passwordless method (FIDO2, Windows Hello, passwordless).',

    // risky_users_open
    'Berechtigung fehlt (IdentityRiskyUser.Read.All erforderlich).'
        => 'Permission missing (IdentityRiskyUser.Read.All required).',
    'Keine Benutzer mit aktivem Risikostatus.'
        => 'No users with an active risk state.',
    ':count Benutzer mit aktivem Risikostatus — Überprüfung empfohlen.'
        => ':count users with an active risk state — review recommended.',
    ':count Benutzer mit aktivem Risikostatus erfordern sofortige Aufmerksamkeit.'
        => ':count users with an active risk state require immediate attention.',

    // legacy_auth_blocked
    'Aktive CA-Richtlinie blockiert Legacy-Authentifizierung.'
        => 'Active CA policy blocks legacy authentication.',
    'Legacy-Auth-Block im Report-Modus — noch nicht aktiv.'
        => 'Legacy auth block in report-only mode — not yet active.',
    'Keine CA-Richtlinie blockiert Legacy-Authentifizierung.'
        => 'No CA policy blocks legacy authentication.',

    // sign_in_risk_policy
    'Aktive CA-Richtlinie reagiert auf Anmelderisiko (mittel/hoch).'
        => 'Active CA policy responds to sign-in risk (medium/high).',
    'Keine CA-Richtlinie reagiert auf Anmelderisiken. Benötigt Entra ID P2.'
        => 'No CA policy responds to sign-in risks. Requires Entra ID P2.',

    // user_risk_policy
    'Aktive CA-Richtlinie reagiert auf hohes Benutzerrisiko.'
        => 'Active CA policy responds to high user risk.',
    'Keine CA-Richtlinie reagiert auf Benutzerrisiken. Benötigt Entra ID P2.'
        => 'No CA policy responds to user risks. Requires Entra ID P2.',

    // ca_device_compliance
    'Aktive CA-Richtlinie fordert konforme/Hybrid-Geräte.'
        => 'Active CA policy requires compliant/hybrid devices.',
    'Keine CA-Richtlinie erzwingt Gerätekonformität.'
        => 'No CA policy enforces device compliance.',

    // ca_guest_restriction
    'Aktive CA-Richtlinie adressiert Gastbenutzer spezifisch.'
        => 'Active CA policy specifically addresses guest users.',
    'Keine CA-Richtlinie mit expliziten Bedingungen für Gastbenutzer gefunden.'
        => 'No CA policy with explicit conditions for guest users found.',

    // device_compliance_rate
    'Keine Intune-Geräte gefunden oder Berechtigung fehlt.'
        => 'No Intune devices found or permission missing.',
    ':rate% der Geräte sind konform (:nonCompliant nicht konform von :total).'
        => ':rate% of devices are compliant (:nonCompliant non-compliant of :total).',
    'Compliance-Rate: :rate% — :nonCompliant von :total Geräten nicht konform.'
        => 'Compliance rate: :rate% — :nonCompliant of :total devices non-compliant.',
    'Niedrige Compliance-Rate: :rate% — :nonCompliant von :total Geräten nicht konform.'
        => 'Low compliance rate: :rate% — :nonCompliant of :total devices non-compliant.',
    'Berechtigung fehlt (DeviceManagementManagedDevices.Read.All).'
        => 'Permission missing (DeviceManagementManagedDevices.Read.All).',

    // defender_alerts
    'Berechtigung fehlt oder Defender nicht lizenziert.'
        => 'Permission missing or Defender not licensed.',
    'Keine offenen Defender-Sicherheitswarnungen.'
        => 'No open Defender security alerts.',
    ':count offene Sicherheitswarnung(en) — Überprüfung empfohlen.'
        => ':count open security alert(s) — review recommended.',
    ':count offene Sicherheitswarnungen erfordern Aufmerksamkeit.'
        => ':count open security alerts require attention.',

    // secure_score
    'Keine Secure-Score-Daten verfügbar.'
        => 'No Secure Score data available.',
    'Secure Score: :current/:max Punkte (:pct%).'
        => 'Secure Score: :current/:max points (:pct%).',
    'Secure Score: :current/:max Punkte (:pct%) — Verbesserungspotenzial.'
        => 'Secure Score: :current/:max points (:pct%) — room for improvement.',
    'Niedriger Secure Score: :current/:max Punkte (:pct%).'
        => 'Low Secure Score: :current/:max points (:pct%).',

    // admin_count
    'Globale Administratoren-Rolle nicht gefunden.'
        => 'Global Administrators role not found.',
    ':count globale Administrator(en) — optimal (max. 4 empfohlen).'
        => ':count Global Administrator(s) — optimal (max. 4 recommended).',
    ':count globale Administratoren — akzeptabel, aber Least-Privilege prüfen.'
        => ':count Global Administrators — acceptable, but review least privilege.',
    ':count globale Administratoren — zu viele. Microsoft empfiehlt max. 2-4.'
        => ':count Global Administrators — too many. Microsoft recommends max. 2-4.',

    // named_locations
    ':count Named Location(s) konfiguriert.'
        => ':count named location(s) configured.',
    'Keine Named Locations konfiguriert. Vertrauenswürdige IPs/Länder fehlen.'
        => 'No named locations configured. Trusted IPs/countries are missing.',

    // app_secrets_expiry
    'Berechtigung fehlt (Application.Read.All erforderlich).'
        => 'Permission missing (Application.Read.All required).',
    ':expired abgelaufenes Secret(s)' => ':expired expired secret(s)',
    ', :soon läuft in <30 Tagen ab.' => ', :soon expiring in <30 days.',
    '.' => '.',
    ':soon Secret(s) läuft in <30 Tagen ab — Erneuerung erforderlich.'
        => ':soon secret(s) expiring in <30 days — renewal required.',
    'Keine abgelaufenen oder bald ablaufenden App-Secrets.'
        => 'No expired or soon-to-expire app secrets.',

    // no_stale_licensed
    'Keine inaktiven lizenzierten Konten gefunden.'
        => 'No inactive licensed accounts found.',
    ':stale lizenzierte Benutzer seit >90 Tagen inaktiv.'
        => ':stale licensed users inactive for >90 days.',
    ':stale lizenzierte Benutzer seit >90 Tagen ohne Anmeldung.'
        => ':stale licensed users without sign-in for >90 days.',

    // guest_user_count
    ':guests aktive Gastbenutzer — unkritisch.'
        => ':guests active guest users — uncritical.',
    ':guests Gastbenutzer — regelmäßige Überprüfung empfohlen.'
        => ':guests guest users — regular review recommended.',
    ':guests Gastbenutzer — Überprüfung und Bereinigung erforderlich.'
        => ':guests guest users — review and cleanup required.',

    // security_defaults
    'Security Defaults ist aktiv, aber eigene CA-Richtlinien sind ebenfalls aktiviert — kann zu Konflikten führen.'
        => 'Security Defaults is active, but custom CA policies are also enabled — this can lead to conflicts.',
    'Weder Security Defaults noch aktive CA-Richtlinien vorhanden — kein Basisschutz.'
        => 'Neither Security Defaults nor active CA policies are present — no baseline protection.',
    'Security Defaults aktiv — bietet Basisschutz, aber keine granulare Steuerung. CA-Migration empfohlen.'
        => 'Security Defaults active — provides baseline protection but no granular control. CA migration recommended.',
    'Security Defaults deaktiviert, eigene CA-Richtlinien aktiv — optimal.'
        => 'Security Defaults disabled, custom CA policies active — optimal.',
    'Berechtigung fehlt (Policy.Read.All erforderlich).'
        => 'Permission missing (Policy.Read.All required).',

    // admins_mfa_registered
    'Globale Administratorrolle nicht gefunden.'
        => 'Global Administrator role not found.',
    'Keine Mitglieder der Administratorrolle gefunden.'
        => 'No members of the administrator role found.',
    'Alle :count globale(n) Administrator(en) haben MFA registriert.'
        => 'All :count Global Administrator(s) have registered MFA.',
    ':count Admin(s) ohne MFA: :list' => ':count admin(s) without MFA: :list',
    ' …' => ' …',

    // sspr_adoption
    'Keine Benutzerdaten verfügbar.' => 'No user data available.',
    ':ssprCount/:total Benutzer haben SSPR registriert (:rate%).'
        => ':ssprCount/:total users have registered SSPR (:rate%).',
    'Nur :rate% (:ssprCount/:total) haben SSPR registriert — Ziel: >70%.'
        => 'Only :rate% (:ssprCount/:total) have registered SSPR — target: >70%.',
    'Kein Benutzer hat SSPR registriert. SSPR-Einführung empfohlen.'
        => 'No user has registered for SSPR. Adopting SSPR is recommended.',
    'Berechtigung fehlt (Reports.Read.All) oder SSPR nicht lizenziert.'
        => 'Permission missing (Reports.Read.All) or SSPR not licensed.',

    // ca_session_controls
    'Aktive CA-Richtlinie steuert Sitzungslebensdauer oder persistente Sitzungen.'
        => 'Active CA policy controls session lifetime or persistent sessions.',
    'Keine CA-Richtlinie kontrolliert Sitzungsdauer oder Browser-Persistenz.'
        => 'No CA policy controls session duration or browser persistence.',

    // pim_adoption
    ':count PIM-berechtigte Rollenzuweisung(en) aktiv — Just-in-Time-Zugriff wird genutzt.'
        => ':count PIM-eligible role assignment(s) active — just-in-time access is in use.',
    'Keine PIM-berechtigten Rollenzuweisungen. Alle Admins haben dauerhaften Zugriff.'
        => 'No PIM-eligible role assignments. All admins have permanent access.',
    'Berechtigung fehlt (RoleManagement.Read.Directory) oder Entra ID P2 nicht lizenziert.'
        => 'Permission missing (RoleManagement.Read.Directory) or Entra ID P2 not licensed.',

    // app_consent_policy
    'Benutzer können keinen Apps ohne Admin-Genehmigung zustimmen — optimal.'
        => 'Users cannot consent to any apps without admin approval — optimal.',
    'Benutzer dürfen beliebigen Apps zustimmen (legacy consent policy). Consent-Phishing-Risiko.'
        => 'Users may consent to any apps (legacy consent policy). Consent phishing risk.',
    'Benutzer dürfen risikoarmen Apps zustimmen. Admin-Consent-Workflow für alle empfohlen.'
        => 'Users may consent to low-risk apps. An admin consent workflow for all is recommended.',
    'Consent-Richtlinie vorhanden — manuelle Überprüfung empfohlen: :policies'
        => 'Consent policy present — manual review recommended: :policies',

    // external_collab_policy
    'Niemand darf einladen — sehr restriktiv.'
        => 'No one may invite — very restrictive.',
    'Nur Admins und Gast-Einlader dürfen einladen — empfohlen.'
        => 'Only admins and guest inviters may invite — recommended.',
    'Alle Mitglieder dürfen einladen — moderat.'
        => 'All members may invite — moderate.',
    'Jeder (inkl. Gäste) darf einladen — unsicher.'
        => 'Anyone (incl. guests) may invite — insecure.',
    'Einstellung: :setting' => 'Setting: :setting',
    ' Empfehlung: auf Admins und Gast-Einlader einschränken.'
        => ' Recommendation: restrict to admins and guest inviters.',
    ' Einschränkung auf Admins dringend empfohlen.'
        => ' Restriction to admins strongly recommended.',

    // break_glass
    'Globale Admin-Rolle nicht gefunden.' => 'Global Admin role not found.',
    ':candidates potenzielle Notfallkonten gefunden (ohne Lizenz, Cloud-only).'
        => ':candidates potential emergency accounts found (no license, cloud-only).',
    'Nur 1 Notfallkonto gefunden. Microsoft empfiehlt mindestens 2.'
        => 'Only 1 emergency account found. Microsoft recommends at least 2.',
    'Kein globales Admin-Konto ohne Lizenz gefunden. Notfallkonten sollten keine Lizenzen haben.'
        => 'No Global Admin account without a license found. Emergency accounts should hold no licenses.',

    // defender_for_office
    'Defender for Office 365 Lizenz aktiv: :sku'
        => 'Defender for Office 365 license active: :sku',
    'Defender for Office 365 im Bundle enthalten: :sku'
        => 'Defender for Office 365 included in bundle: :sku',
    'Kein Defender for Office 365 Abonnement aktiv.'
        => 'No Defender for Office 365 subscription active.',

    // gdpr_tenant_region
    'Organisation nicht lesbar.' => 'Organization not readable.',
    'Tenant-Region: :code' => 'Tenant region: :code',
    ' (preferredDataLocation=:pdl)' => ' (preferredDataLocation=:pdl)',
    'Tenant-Region außerhalb EU/EWR: :code. DSGVO-Übermittlung in Drittländer prüfen (Art. 44–49).'
        => 'Tenant region outside EU/EEA: :code. Review GDPR transfers to third countries (Art. 44–49).',

    // gdpr_sharepoint_sharing
    'Externe Freigabe komplett deaktiviert.'
        => 'External sharing completely disabled.',
    'Nur an bekannte externe Benutzer — restriktiv.'
        => 'Only to known external users — restrictive.',
    'Nur an authentifizierte Externe — akzeptabel, aber prüfen.'
        => 'Only to authenticated external users — acceptable, but review.',
    'Anyone-Links sind aktiv — DSGVO-Risiko: unbekannte Dritte können auf Daten zugreifen.'
        => 'Anyone links are active — GDPR risk: unknown third parties can access data.',
    'Unbekannter sharingCapability-Wert: :cap'
        => 'Unknown sharingCapability value: :cap',
    'SharePoint-Tenant-Settings nicht lesbar (Permission SharePointTenantSettings.Read.All?).'
        => 'SharePoint tenant settings not readable (permission SharePointTenantSettings.Read.All?).',

    // gdpr_anonymous_link_expiry
    'SharePoint-Tenant-Settings nicht lesbar.'
        => 'SharePoint tenant settings not readable.',
    'Externe Freigabe deaktiviert — Ablauf irrelevant.'
        => 'External sharing disabled — expiry irrelevant.',
    'Anyone-Links haben keinen Ablauf — DSGVO-Risiko.'
        => 'Anyone links have no expiry — GDPR risk.',
    'Anyone-Links laufen nach :days Tagen ab — empfohlen ≤ 90.'
        => 'Anyone links expire after :days days — recommended ≤ 90.',
    'Anyone-Links laufen nach :days Tagen ab.'
        => 'Anyone links expire after :days days.',

    // gdpr_default_sharing_link
    'Standard-Link: :type' => 'Default link: :type',
    'Standard-Link ist Anyone — DSGVO-kritisch.'
        => 'Default link is Anyone — GDPR-critical.',

    // loadSpSettings
    'SharePoint Online ist im Tenant nicht lizenziert — Prüfung nicht zutreffend.'
        => 'SharePoint Online is not licensed in the tenant — check not applicable.',

    // gdpr_sensitivity_labels
    'Sensitivity-Labels-Endpunkt nicht erreichbar — Berechtigung InformationProtectionPolicy.Read.All prüfen.'
        => 'Sensitivity labels endpoint not reachable — check the InformationProtectionPolicy.Read.All permission.',
    'Keine Sensitivity Labels gefunden.' => 'No sensitivity labels found.',
    ':count Labels existieren, aber keines ist aktiv.'
        => ':count labels exist, but none is active.',
    ':active aktive Sensitivity Labels (von :total)'
        => ':active active sensitivity labels (of :total)',

    // gdpr_dlp_or_labels
    ':active Sensitivity Labels aktiv.' => ':active sensitivity labels active.',
    'Keine aktive Schutzmaßnahme (DLP/Label) gefunden.'
        => 'No active protection measure (DLP/label) found.',

    // gdpr_retention_policies
    'Keine eDiscovery-/Aufbewahrungsfälle konfiguriert.'
        => 'No eDiscovery/retention cases configured.',
    ':active aktive Fälle, :total insgesamt.'
        => ':active active cases, :total in total.',

    // gdpr_audit_log
    'Audit-Log nicht abrufbar — Berechtigung AuditLog.Read.All prüfen.'
        => 'Audit log not retrievable — check the AuditLog.Read.All permission.',
    'Audit-Log liefert Daten.' => 'Audit log returns data.',
    'Audit-Log antwortet, aber leer — Permission/Ausstellungsdatum prüfen.'
        => 'Audit log responds but is empty — check permission/issue date.',
    'Audit-Log nicht abrufbar: :error' => 'Audit log not retrievable: :error',
];
