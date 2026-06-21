<?php

/**
 * English translations for the Best-Practice backend layer
 * (BestPracticeService / BestPracticeController) — the curated hardening
 * guide: phase titles/subtitles/intros, per-step titles, rationale ("why"),
 * remediation steps ("how"), link labels and controller flash messages.
 *
 * Only DISPLAY values are translated. Stable IDs, icons, colours, links,
 * config keys and the auto-detection logic remain untouched in PHP.
 *
 * @return array<string,string>
 */
return [

    // ── Controller: page title, flash messages, audit text ──────────
    'Tenant-Härtungs-Leitfaden'   => 'Tenant Hardening Guide',
    'Kein Schritt angegeben.'     => 'No step specified.',
    'Fortschritt zurückgesetzt.'  => 'Progress reset.',
    'Fortschritt zurückgesetzt'   => 'Progress reset',
    'Schritt :id → :state'        => 'Step :id → :state',

    // ── PHASE 1 ─────────────────────────────────────────────────────
    'Phase 1 — Fundament' => 'Phase 1 — Foundation',
    'Einmalig · ca. 10 Minuten' => 'One-time · approx. 10 minutes',
    'Die Grundlagen, ohne die alles andere nichts hilft: stabile Tenant-Verbindung, korrekte Berechtigungen, dein eigenes Admin-Konto abgesichert.' => 'The fundamentals without which nothing else helps: a stable tenant connection, correct permissions and your own admin account secured.',

    'Einrichtungs-Assistent durchlaufen' => 'Complete the setup wizard',
    'Prüft, dass Tenant-ID/Client-Secret korrekt sind, ein Token bezogen werden kann, die Permissions reichen — und fragt Benachrichtigungs-Empfänger und Branding ab.' => 'Verifies that the tenant ID/client secret are correct, that a token can be obtained and that permissions are sufficient — and captures notification recipients and branding.',
    'Öffne den Assistenten und klicke alle fünf Schritte durch.' => 'Open the wizard and click through all five steps.',
    'Bei „Empfänger" mindestens eine E-Mail eintragen (Security-Postfach), damit dich später Alerts erreichen.' => 'Under "Recipients", enter at least one email address (a security mailbox) so that alerts reach you later.',
    'Im letzten Schritt das passende Compliance-Profil auswählen — Details dazu in Phase 2.' => 'In the final step, select the appropriate compliance profile — details follow in Phase 2.',
    'Zum Assistenten' => 'Open the wizard',

    'Graph-API-Berechtigungen vollständig' => 'Graph API permissions complete',
    'Ohne die richtigen App-Permissions sind viele Module blind. Fehlende Berechtigungen müssen mit Admin-Consent in Entra erteilt werden.' => 'Without the correct app permissions many modules are blind. Missing permissions must be granted with admin consent in Entra.',
    'Auf /settings/permissions sollten alle Zeilen grün sein.' => 'On /settings/permissions every row should be green.',
    'Fehlende Permissions: in der Entra-App-Registrierung „API permissions" → „Add a permission" → „Microsoft Graph" → „Application permissions".' => 'Missing permissions: in the Entra app registration go to "API permissions" → "Add a permission" → "Microsoft Graph" → "Application permissions".',
    'Anschließend „Grant admin consent for …" klicken (das ist die zweite Schaltfläche, häufig übersehen).' => 'Then click "Grant admin consent for …" (this is the second button, often overlooked).',
    'Im Tool dann „Cache leeren" und Permissions-Seite neu laden.' => 'In the tool, then click "Clear cache" and reload the permissions page.',
    'Berechtigungs-Audit öffnen' => 'Open permission audit',

    'Eigene 2FA aktivieren' => 'Enable your own 2FA',
    'Das Tool selbst hat einen Admin-Login. Ohne 2FA ist es ein einzelnes Passwort weg vom kompletten Tenant. TOTP-RFC-6238, kompatibel mit Microsoft/Google Authenticator, Aegis usw.' => 'The tool itself has an admin login. Without 2FA, a single password is all that stands between an attacker and the entire tenant. TOTP RFC 6238, compatible with Microsoft/Google Authenticator, Aegis, etc.',
    'QR-Code mit der Authenticator-App scannen.' => 'Scan the QR code with the authenticator app.',
    'Zur Verifikation den 6-stelligen Code eingeben.' => 'Enter the 6-digit code to verify.',
    'Wiederherstellungs-Codes ausdrucken oder im Passwort-Manager ablegen — wirklich, nicht überspringen.' => 'Print the recovery codes or store them in your password manager — really, do not skip this.',
    '2FA einrichten' => 'Set up 2FA',

    'Tool-Benutzer aufräumen' => 'Clean up tool users',
    'Shared-Accounts (z. B. „admin@firma.de" mit drei Personen) gehen gegen jede Compliance. Sauberer: jeder Admin/Operator hat eigenen Zugang im Tool — verknüpft mit dem Entra-Konto.' => 'Shared accounts (e.g. "admin@company.com" used by three people) violate every compliance standard. Cleaner: each admin/operator has their own access to the tool — linked to their Entra account.',
    'Pro Person einen Eintrag anlegen, Rolle „Admin" oder „Operator".' => 'Create one entry per person, with the role "Admin" or "Operator".',
    'Generische/geteilte Logins entfernen.' => 'Remove generic/shared logins.',
    'Operator-Rolle für Helpdesk: kann scannen, Lizenzen umschichten, kein Zugriff auf Einstellungen.' => 'Operator role for the help desk: can scan and reassign licences, but has no access to settings.',
    'Benutzer-Zugang' => 'User access',

    // ── PHASE 2 ─────────────────────────────────────────────────────
    'Phase 2 — One-Click-Härtung' => 'Phase 2 — One-Click Hardening',
    'Einmalig · ca. 5 Minuten' => 'One-time · approx. 5 minutes',
    'Die größten Hebel mit wenigen Klicks. Profile bündeln branchen-typische Härtung; das Hardening-Modul lässt dich einzelne Items feintunen.' => 'The biggest levers with just a few clicks. Profiles bundle industry-typical hardening; the hardening module lets you fine-tune individual items.',

    'Compliance-Profil anwenden' => 'Apply a compliance profile',
    'Setzt mit einem Klick 6–13 Hardening-Defaults entlang einer bekannten Regulierung (DSGVO, KRITIS, BaFin/DORA, BSI).' => 'Sets 6–13 hardening defaults with a single click, aligned to a known regulation (GDPR, KRITIS, BaFin/DORA, BSI).',
    'Standard / DSGVO-Basis — für jeden Tenant ein guter Start.' => 'Standard / GDPR baseline — a good starting point for any tenant.',
    'Gesundheitswesen — wenn Patienten-/Klientendaten verarbeitet werden.' => 'Healthcare — when patient/client data is processed.',
    'Finanzwesen — Banken, Versicherungen, Asset Manager.' => 'Finance — banks, insurers, asset managers.',
    'Öffentlicher Sektor — Behörden, Stadtwerke, Hochschulen.' => 'Public sector — government agencies, municipal utilities, universities.',
    'Bildung — Schulen, Kitas (Schutz von Kinder-Daten gem. DSGVO Art. 8).' => 'Education — schools, day-care centres (protection of children\'s data under GDPR Art. 8).',
    'Profile öffnen' => 'Open profiles',

    'Restliche Hardening-Items abarbeiten' => 'Work through the remaining hardening items',
    'Das Profil deckt 60–80 % ab. Der Rest sind Items, die nicht in jedes Branchen-Muster passen — z. B. App-Anlage durch Endbenutzer blockieren oder Idle-Session-Signout.' => 'The profile covers 60–80%. The rest are items that do not fit every industry pattern — e.g. blocking end-user app creation or idle-session sign-out.',
    'Alles auf „unkritisch" / grün bringen, was Graph direkt schalten kann.' => 'Bring everything that Graph can set directly to "non-critical" / green.',
    'Wo „Admin-Center öffnen" steht: Deep-Link folgen, Wert prüfen, ggf. anpassen.' => 'Where it says "Open admin center": follow the deep link, check the value and adjust if necessary.',
    'Items mit Status „unbekannt" → meist nur fehlende Permission. Zurück zu Phase 1, Schritt 2.' => 'Items with the status "unknown" → usually just a missing permission. Return to Phase 1, step 2.',
    'Hardening-Modul' => 'Hardening module',

    // ── PHASE 3 ─────────────────────────────────────────────────────
    'Phase 3 — Identity & Zugriff' => 'Phase 3 — Identity & Access',
    'Einmalig · ca. 15 Minuten' => 'One-time · approx. 15 minutes',
    '80 % der echten Angriffe gehen über kompromittierte Identitäten. Dieser Block bringt den größten Sicherheitsgewinn pro Minute.' => '80% of real attacks go through compromised identities. This block delivers the greatest security gain per minute.',

    'Break-Glass-Accounts einrichten' => 'Set up break-glass accounts',
    'Falls dein MFA-Dienst ausfällt oder dein Conditional-Access dich aussperrt, brauchst du ein Notfall-Konto. Pflicht laut BSI und allen Audit-Standards.' => 'If your MFA service fails or your Conditional Access locks you out, you need an emergency account. Mandatory under BSI guidance and all audit standards.',
    'Zwei dedizierte Konten anlegen (z. B. breakglass1@…, breakglass2@…).' => 'Create two dedicated accounts (e.g. breakglass1@…, breakglass2@…).',
    'Global Admin permanent zuweisen — diese Accounts sind nicht für PIM.' => 'Assign Global Admin permanently — these accounts are not for PIM.',
    'FIDO2-Hardware-Key (z. B. YubiKey) registrieren, in einen Safe legen.' => 'Register a FIDO2 hardware key (e.g. YubiKey) and store it in a safe.',
    'Conditional-Access-Policies müssen diese UPNs **ausnehmen**.' => 'Conditional Access policies must **exclude** these UPNs.',
    'Test-Login alle 90 Tage dokumentieren.' => 'Document a test login every 90 days.',
    'Break-Glass-Modul' => 'Break-glass module',

    'Conditional Access mindestens auf Baseline' => 'Conditional Access at least at baseline',
    'Conditional Access ist das stärkste Identity-Werkzeug in M365. Wer das nicht hat, ist auf Security-Defaults angewiesen — bei mittlerer Komplexität nicht mehr ausreichend.' => 'Conditional Access is the most powerful identity tool in M365. Without it you have to rely on security defaults — no longer sufficient at moderate complexity.',
    'Mindest-Set: „MFA für alle Benutzer", „Block Legacy Auth", „MFA für Admin-Rollen" (separate strenge Policy).' => 'Minimum set: "MFA for all users", "Block Legacy Auth", "MFA for admin roles" (a separate, strict policy).',
    'Empfehlung: „Compliant device required" für sensible Apps (SharePoint, Exchange).' => 'Recommendation: "Compliant device required" for sensitive apps (SharePoint, Exchange).',
    'Test-Mode („Report-only") zuerst — eine Woche beobachten, dann aktivieren.' => 'Test mode ("Report-only") first — observe for one week, then enable.',
    'Break-Glass-Accounts **explizit ausnehmen** (siehe oben).' => 'Break-glass accounts must be **explicitly excluded** (see above).',
    'CA-Policies' => 'CA policies',

    'Named Locations definieren' => 'Define named locations',
    'Mit benannten Standorten kannst du CA-Policies geo-fence-artig einschränken („nur aus DE/AT/CH") oder die Office-IPs als „trusted" markieren.' => 'With named locations you can restrict CA policies in a geo-fence-like manner ("only from DE/AT/CH") or mark the office IPs as "trusted".',
    'Eigene öffentliche Office-IPs anlegen (statische Ausgangs-IPs).' => 'Add your own public office IPs (static egress IPs).',
    'Länder-Whitelist mit den tatsächlich genutzten Ländern.' => 'Country whitelist containing the countries actually in use.',
    'Reise-Whitelist temporär ergänzen, wenn Mitarbeiter ins Ausland fahren.' => 'Add a travel whitelist temporarily when employees go abroad.',
    'Named Locations' => 'Named locations',

    'Authentication-Strength: phishing-resistent' => 'Authentication strength: phishing-resistant',
    'SMS-MFA ist veraltet, klassisches App-Push ist anfällig für MFA-Fatigue. Standard sollte FIDO2 / Authenticator mit Number-Matching sein.' => 'SMS MFA is outdated and classic app push is vulnerable to MFA fatigue. The standard should be FIDO2 / Authenticator with number matching.',
    'Im Authentication-Strength-Modul Tenant-Policy auf „phishing-resistant" prüfen.' => 'In the authentication-strength module, check that the tenant policy is set to "phishing-resistant".',
    'Im Hardening-Modul Number-Matching aktivieren falls noch nicht erfolgt.' => 'In the hardening module, enable number matching if not already done.',
    'Für Admin-Rollen: CA-Policy mit „phishing-resistant MFA" als Bedingung.' => 'For admin roles: a CA policy with "phishing-resistant MFA" as a condition.',
    'Auth-Strength' => 'Auth strength',

    'PIM: Permanente Admin-Rollen eliminieren' => 'PIM: Eliminate permanent admin roles',
    'Wer 24/7 Global Admin ist, ist 24/7 ein lohnendes Ziel. PIM macht Admin-Rechte Just-in-Time aktivierbar — alle Aktivierungen sind auditierbar.' => 'Anyone who is Global Admin 24/7 is a worthwhile target 24/7. PIM makes admin rights activatable just-in-time — and every activation is auditable.',
    'Liste der permanenten Admin-Zuweisungen anschauen.' => 'Review the list of permanent admin assignments.',
    'Auf „Eligible" (PIM-fähig) umstellen, statt „Active".' => 'Switch them to "Eligible" (PIM-enabled) instead of "Active".',
    'Aktivierungs-Zeitraum auf 4–8 Stunden begrenzen.' => 'Limit the activation window to 4–8 hours.',
    'Genehmigungs-Workflow optional bei Global Admin / Privileged Role Admin.' => 'An optional approval workflow for Global Admin / Privileged Role Admin.',
    'PIM-Übersicht' => 'PIM overview',

    'Token-Lifetime / Sign-in-Frequency' => 'Token lifetime / sign-in frequency',
    'Standard ist „Token bleibt 90 Tage gültig". Bei einem kompromittierten Refresh-Token kann sich der Angreifer Wochen lang halten, auch nach Passwort-Reset.' => 'The default is "the token stays valid for 90 days". With a compromised refresh token an attacker can persist for weeks, even after a password reset.',
    'CA-Policy mit „Sign-in frequency" anlegen.' => 'Create a CA policy with "Sign-in frequency".',
    'Admin-Rollen: 4–8 Stunden.' => 'Admin roles: 4–8 hours.',
    'Normale Benutzer: 12–24 Stunden.' => 'Regular users: 12–24 hours.',
    'Sehr sensible Anwendungen (Finanzen): jeweils ganz neu authentifizieren.' => 'Highly sensitive applications (finance): re-authenticate from scratch each time.',
    'Token-Lifetime' => 'Token lifetime',

    'Admin-Rollen ausmisten' => 'Prune admin roles',
    'Erfahrungsgemäß haben 2–3× so viele Personen Admin-Rollen wie nötig — angesammelt über Jahre. Audit: ist diese Person noch in der Position?' => 'Experience shows that 2–3× as many people hold admin roles as necessary — accumulated over the years. Audit question: is this person still in that position?',
    'Pro Rolle: ist sie wirklich überall nötig? Kann eine weniger privilegierte Rolle reichen?' => 'For each role: is it really needed everywhere? Would a less privileged role suffice?',
    'Global Admin: maximal 2–5 Personen (BSI-Empfehlung).' => 'Global Admin: at most 2–5 people (BSI recommendation).',
    'Bei Unsicherheit: Rolle entziehen → 2 Wochen warten → meldet sich niemand → bleibt entzogen.' => 'When in doubt: revoke the role → wait 2 weeks → if no one speaks up → it stays revoked.',
    'Admin-Rollen' => 'Admin roles',

    // ── PHASE 4 ─────────────────────────────────────────────────────
    'Phase 4 — Daten & E-Mail' => 'Phase 4 — Data & Email',
    'Hier geht es darum, dass Daten nicht ungewollt rausfließen — und dass eingehende Mails nicht so leicht zu fälschen sind.' => 'This is about preventing data from leaking out unintentionally — and making incoming mail harder to spoof.',

    'SharePoint/OneDrive-Sharing einschränken' => 'Restrict SharePoint/OneDrive sharing',
    'Standard ist „Anyone-Links erlaubt, beliebige externe Mails einladbar". DSGVO-konform ist eher: nur bestehende Gäste dürfen Inhalte erhalten, Anon-Links laufen automatisch ab.' => 'The default is "Anyone links allowed, any external address can be invited". GDPR-compliant is more like: only existing guests may receive content and anonymous links expire automatically.',
    '„existingExternalUserSharingOnly" als Capability.' => '"existingExternalUserSharingOnly" as the capability.',
    'Anonym-Link-Ablauf: 30 Tage (oder weniger).' => 'Anonymous link expiry: 30 days (or less).',
    'Re-Sharing durch externe Benutzer deaktivieren.' => 'Disable re-sharing by external users.',
    'Idle-Session-Signout aktivieren (4h Idle → automatisch ausloggen).' => 'Enable idle-session sign-out (4h idle → automatic sign-out).',
    'Sharing-Richtlinien' => 'Sharing policies',

    'SPF / DKIM / DMARC für alle Domains' => 'SPF / DKIM / DMARC for all domains',
    'Ohne DMARC kann jeder im Internet deine Domain als Absender fälschen. Mit SPF + DKIM + DMARC mit „reject"-Policy ist Spoofing technisch ausgeschlossen.' => 'Without DMARC, anyone on the internet can spoof your domain as the sender. With SPF + DKIM + DMARC and a "reject" policy, spoofing is technically impossible.',
    'Pro Domain alle drei DNS-Records grün haben.' => 'Get all three DNS records green for each domain.',
    'SPF: alle berechtigten Sender (EXO + Marketing-Tools wie Mailchimp + ERP-System) eintragen.' => 'SPF: list all authorised senders (EXO + marketing tools such as Mailchimp + ERP system).',
    'DKIM: in Defender / Exchange Admin Center aktivieren.' => 'DKIM: enable in Defender / Exchange Admin Center.',
    'DMARC: erst „p=none" → Reports prüfen → dann „p=quarantine" → später „p=reject".' => 'DMARC: start with "p=none" → review reports → then "p=quarantine" → later "p=reject".',
    'Domain Health' => 'Domain Health',

    'Defender for Office: Safe Links / Safe Attachments' => 'Defender for Office: Safe Links / Safe Attachments',
    'Klassische Phishing-Mail-Filter erkennen 80 %. Safe-Links + Safe-Attachments fangen die restlichen 20 % ab — durch Sandbox-Detonation und Time-of-Click-Re-Check.' => 'Classic phishing mail filters catch 80%. Safe Links + Safe Attachments catch the remaining 20% — through sandbox detonation and a time-of-click re-check.',
    'In Defender for Office: Standard-Preset-Policies aktivieren.' => 'In Defender for Office: enable the standard preset policies.',
    'External-Sender-Identifier („Externe Mail" Tag) einschalten.' => 'Turn on the external-sender identifier (the "External mail" tag).',
    'Verdächtige Domains, die häufig Phishing senden, in den Block-Listen pflegen.' => 'Maintain suspicious domains that frequently send phishing in the block lists.',
    'Mail Flow & Schutz' => 'Mail Flow & Protection',

    'Sensitivity Labels einrichten' => 'Set up sensitivity labels',
    'Labels ermöglichen Verschlüsselung, Wasserzeichen und Rechte-Verwaltung „on save". Auch wenn ein Dokument den Tenant verlässt, bleibt es geschützt.' => 'Labels enable encryption, watermarks and rights management "on save". Even when a document leaves the tenant, it stays protected.',
    'Mindest-Set: Öffentlich · Intern · Vertraulich · Streng vertraulich.' => 'Minimum set: Public · Internal · Confidential · Highly Confidential.',
    '„Vertraulich" und „Streng vertraulich" automatisch verschlüsseln.' => 'Encrypt "Confidential" and "Highly Confidential" automatically.',
    'Auto-Apply-Regeln (z. B. Kreditkartennummer → automatisch „Vertraulich").' => 'Auto-apply rules (e.g. credit card number → automatically "Confidential").',
    'Sensitivity Labels' => 'Sensitivity Labels',

    'DLP-Policy für die kritischste Datenkategorie' => 'DLP policy for the most critical data category',
    'Eine einzige sinnvoll konfigurierte DLP-Policy ist besser als zehn überregulierende — Mitarbeiter umgehen sonst alles.' => 'A single sensibly configured DLP policy is better than ten over-regulating ones — otherwise employees work around everything.',
    'Branchen-typische erste Policy: Healthcare → Gesundheitsdaten, Finance → IBAN/Kreditkarte, B2B → Geschäftsgeheimnisse.' => 'Industry-typical first policy: Healthcare → health data, Finance → IBAN/credit card, B2B → trade secrets.',
    'Erst „Tip" + Audit, dann nach 2 Wochen „Block" für externe Empfänger.' => 'First "Tip" + audit, then after 2 weeks "Block" for external recipients.',
    'False-Positives sammeln und Policy iterieren.' => 'Collect false positives and iterate on the policy.',
    'DLP-Richtlinien' => 'DLP policies',

    'Aufbewahrungs-Policy nach DSGVO/GoBD' => 'Retention policy per GDPR/GoBD',
    'Du **musst** löschen (DSGVO: nur so lang wie nötig) — und **musst** aufbewahren (GoBD: Geschäftsbriefe 6 Jahre, Buchhaltung 10 Jahre). Beide Anforderungen in eine Policy gegossen.' => 'You **must** delete (GDPR: only as long as necessary) — and you **must** retain (GoBD: business correspondence 6 years, accounting 10 years). Both requirements cast into a single policy.',
    'Mail-Postfächer: 10 Jahre für Buchhaltung / Geschäftsführung, 6 Jahre Standard.' => 'Mailboxes: 10 years for accounting / management, 6 years by default.',
    'Teams-Chats: 1–2 Jahre.' => 'Teams chats: 1–2 years.',
    'OneDrive: 30 Tage nach Account-Löschung dann hard delete.' => 'OneDrive: hard delete 30 days after account deletion.',
    'Rechtliche Klärung mit Datenschutz/Steuerberater vor Aktivierung.' => 'Obtain legal clarification with your data protection officer/tax advisor before enabling.',
    'Aufbewahrung' => 'Retention',

    'Customer Lockbox aktivieren' => 'Enable Customer Lockbox',
    'Standardmäßig kann ein Microsoft-Support-Mitarbeiter auf deine Tenant-Daten zugreifen, wenn ein Ticket es nahelegt. Mit Customer Lockbox musst du jeden Zugriff explizit freigeben.' => 'By default a Microsoft support engineer can access your tenant data when a ticket warrants it. With Customer Lockbox you must explicitly approve every access.',
    'Erfordert M365 E5 oder Customer-Lockbox-Add-On.' => 'Requires M365 E5 or the Customer Lockbox add-on.',
    'Freigabe-Workflow im Tool dokumentieren (wer entscheidet, wie schnell).' => 'Document the approval workflow in the tool (who decides, how quickly).',
    'DSGVO-Auftragsverarbeitungsvertrag mit Microsoft entsprechend ergänzen.' => 'Amend the GDPR data processing agreement with Microsoft accordingly.',
    'Customer Lockbox' => 'Customer Lockbox',

    // ── PHASE 5 ─────────────────────────────────────────────────────
    'Phase 5 — Gäste, Apps & externe Identitäten' => 'Phase 5 — Guests, Apps & External Identities',
    'Gäste und OAuth-Apps sind oft Jahre alt und wurden nie überprüft. Hier räumst du auf, was sich „eingenistet" hat.' => 'Guests and OAuth apps are often years old and have never been reviewed. Here you clean up whatever has "taken root".',

    'Alte Gast-Benutzer entfernen' => 'Remove old guest users',
    'Jeder Gast ist ein potentieller Angriffsvektor — Konten, die zu einer fremden Organisation gehören, die du nicht kontrollierst. Ohne Sign-In seit 90+ Tagen: meist nicht mehr nötig.' => 'Every guest is a potential attack vector — accounts that belong to an external organisation you do not control. No sign-in for 90+ days: usually no longer needed.',
    'Filter: „Last sign-in > 90 days" oder „nie".' => 'Filter: "Last sign-in > 90 days" or "never".',
    'Bevor du entfernst: Owner der Sharing-Beziehung kurz informieren.' => 'Before removing: briefly inform the owner of the sharing relationship.',
    'Bulk-deaktivieren statt sofort löschen — 30 Tage Beobachtungs-Phase.' => 'Bulk-disable instead of deleting immediately — a 30-day observation phase.',
    'Gast-Benutzer' => 'Guest users',

    'Access Review für Gäste anlegen' => 'Create an access review for guests',
    'NIS-2 und DSGVO verlangen periodische Berechtigungs-Überprüfung. Mit einem Access Review fragst du automatisiert alle Owner: „Brauchen die diesen Zugriff noch?"' => 'NIS-2 and GDPR require periodic permission reviews. With an access review you automatically ask every owner: "Do they still need this access?"',
    'Quartals-Rhythmus für Gäste.' => 'Quarterly cadence for guests.',
    'Halbjährlich für interne sensible Gruppen (Geschäftsführung, IT-Admins).' => 'Semi-annually for internal sensitive groups (management, IT admins).',
    'Auto-revoke bei „no response" aktivieren.' => 'Enable auto-revoke on "no response".',
    'Access Reviews' => 'Access Reviews',

    'Cross-Tenant Access regeln' => 'Govern cross-tenant access',
    'Standard ist „mit jedem M365-Tenant darf B2B-Collaboration laufen". Wenn du nur mit zwei Partnern arbeitest, ist eine Allow-List sicherer als die offene Welt.' => 'The default is "B2B collaboration is allowed with any M365 tenant". If you only work with two partners, an allow list is safer than the open world.',
    'Partner-Tenants explizit erlauben.' => 'Explicitly allow partner tenants.',
    'Default-Policy auf „inbound block" stellen.' => 'Set the default policy to "inbound block".',
    'Teams Shared Channels nur mit ausgewählten Tenants.' => 'Teams shared channels only with selected tenants.',
    'Cross-Tenant' => 'Cross-Tenant',

    'OAuth-Apps mit Risk-Score prüfen' => 'Review OAuth apps by risk score',
    'Drittanbieter-Apps können „Mail.ReadWrite" oder schlimmer haben — und niemand erinnert sich, wer das vor 3 Jahren genehmigt hat. Diese Module zeigt High-Privilege × Inaktivität als Risiko.' => 'Third-party apps may hold "Mail.ReadWrite" or worse — and no one remembers who approved it 3 years ago. This module surfaces high-privilege × inactivity as a risk.',
    'Apps mit Risk-Score > 50 untersuchen.' => 'Investigate apps with a risk score > 50.',
    'Inaktiv seit 180+ Tagen → revoken.' => 'Inactive for 180+ days → revoke.',
    'Microsoft-Apps in Ruhe lassen (selbst wenn der Score hoch ist).' => 'Leave Microsoft apps alone (even if the score is high).',
    'App-Consent-Policy verschärfen: User-Consent nur für Low-Risk-Permissions.' => 'Tighten the app consent policy: user consent only for low-risk permissions.',
    'OAuth-App-Audit' => 'OAuth App Audit',

    'Eigene App-Registrierungen verwalten' => 'Manage your own app registrations',
    'Client Secrets sollten unter 1 Jahr alt sein und in einem Geheimnis-Manager liegen. Verwaiste Apps löschen — jede ist eine potentielle Hintertür.' => 'Client secrets should be less than 1 year old and stored in a secrets manager. Delete orphaned apps — each one is a potential backdoor.',
    'Secrets, die in den nächsten 30 Tagen ablaufen → erneuern, alte revoken.' => 'Secrets expiring within the next 30 days → renew, and revoke the old ones.',
    'Apps ohne Owner: identifizieren und Owner zuweisen.' => 'Apps without an owner: identify them and assign an owner.',
    'Unbenutzte Apps löschen.' => 'Delete unused apps.',
    'App-Registrierungen' => 'App registrations',

    // ── PHASE 6 ─────────────────────────────────────────────────────
    'Phase 6 — Automatisierung scharfschalten' => 'Phase 6 — Arm Automation',
    'Damit das Tool ab jetzt für dich arbeitet — Reports verschickt, Alerts pusht, Snapshots erstellt.' => 'So that from now on the tool works for you — sending reports, pushing alerts and creating snapshots.',

    'Cron-Jobs aktiviert prüfen' => 'Verify cron jobs are enabled',
    'Ohne aktive Cron-Jobs gibt es keine Reports, keine Sharing-Scans, keine Audit-Diff-Snapshots. Voraussetzung: System-Cron alle Minute auf run-cron.php zeigt.' => 'Without active cron jobs there are no reports, no sharing scans and no audit-diff snapshots. Prerequisite: a system cron pointing to run-cron.php every minute.',
    'Server-Cron prüfen: `* * * * * /usr/bin/php /pfad/zu/run-cron.php`.' => 'Check the server cron: `* * * * * /usr/bin/php /path/to/run-cron.php`.',
    'Im Tool unter /cron alle Jobs „enabled" lassen (insbesondere alert_new_defender, alert_new_risky_users, audit_diff_snapshot).' => 'In the tool under /cron, leave all jobs "enabled" (in particular alert_new_defender, alert_new_risky_users, audit_diff_snapshot).',
    'Einen Job manuell „Run now" auslösen — sollte „success" liefern.' => 'Trigger a job manually with "Run now" — it should return "success".',
    'Cron-Übersicht' => 'Cron overview',

    'Benachrichtigungs-Empfänger sind gesetzt' => 'Notification recipients are configured',
    'Alle Alerts (Risk, Defender, Service-Health) gehen an konfigurierte Empfänger. Ohne diese Adresse passiert „leise" und du erfährst von Vorfällen erst aus dem Audit-Log.' => 'All alerts (Risk, Defender, Service Health) go to the configured recipients. Without this address everything happens "silently" and you only learn of incidents from the audit log.',
    'Mindestens eine Security-Mailbox als Empfänger.' => 'At least one security mailbox as a recipient.',
    'Optional eine zweite Adresse für ITSM-Tickets (Jira, ServiceNow per Mail-Gateway).' => 'Optionally a second address for ITSM tickets (Jira, ServiceNow via a mail gateway).',
    'Test-Mail aus den Einstellungen auslösen.' => 'Send a test email from the settings.',
    'Einstellungen (Tab „Benachrichtigungen")' => 'Settings (the "Notifications" tab)',

    'Executive-Report monatlich an Leitung' => 'Monthly executive report to management',
    'Eine Seite, einmal im Monat, mit KPIs & Trends. Hält das Thema Sicherheit oben auf der GF-Agenda — ohne dass du jedes Mal Folien bauen musst.' => 'One page, once a month, with KPIs & trends. Keeps security high on the management agenda — without you building slides every time.',
    'Aktivieren, Empfänger setzen (Geschäftsführung, IT-Leitung).' => 'Enable it and set the recipients (management, IT leadership).',
    'Erste Vorschau im Tool ansehen, ggf. anpassen.' => 'View the first preview in the tool and adjust if necessary.',
    'Test-Versand starten.' => 'Start a test send.',
    'Executive-Report' => 'Executive Report',

    'Backup-Status tracken' => 'Track backup status',
    'Microsoft 365 hat kein klassisches Backup. Wenn ein Mitarbeiter zwischen Tag 31 und Tag 60 eine Datei löscht, ist sie weg. Drittanbieter-Backup (Veeam, AvePoint, Acronis, etc.) dokumentieren.' => 'Microsoft 365 has no classic backup. If an employee deletes a file between day 31 and day 60, it is gone. Document your third-party backup (Veeam, AvePoint, Acronis, etc.).',
    'Backup-Anbieter eintragen.' => 'Enter the backup provider.',
    'Letzten erfolgreichen Lauf einmalig manuell setzen — der Health-Score nutzt das danach.' => 'Set the last successful run manually once — the health score uses it from then on.',
    'Wenn noch kein Backup: jetzt evaluieren (durchschnittlich 3–6 €/User/Monat).' => 'If you have no backup yet: evaluate now (on average €3–6/user/month).',
    'Backup-Status' => 'Backup status',

    'Mindestens einen Workflow anlegen' => 'Create at least one workflow',
    'Workflows nehmen Routine ab. Beispiel: „Neuer Gast → Mail an Compliance-Team + Notification im Tool" — passiert dann automatisch, ohne dass jemand drauf achten muss.' => 'Workflows take routine off your hands. Example: "New guest → email to the compliance team + notification in the tool" — then happens automatically, without anyone having to watch for it.',
    'Trigger „Neuer Gast-Benutzer" wählen.' => 'Choose the trigger "New guest user".',
    'Aktion „In-App-Benachrichtigung erzeugen" + „Mail senden" verketten.' => 'Chain the actions "Create in-app notification" + "Send email".',
    'Workflow aktivieren, eine Woche beobachten.' => 'Enable the workflow and observe for one week.',
    'Workflows' => 'Workflows',

    'Sharing-Monitor mit Owner-Befragung' => 'Sharing monitor with owner survey',
    'Externe Freigaben werden jeden Monat automatisch geprüft, der Owner bekommt eine Mail mit „Brauchst du das noch?". Antwortet keiner → automatischer Widerruf nach 14 Tagen.' => 'External shares are checked automatically every month and the owner receives an email asking "Do you still need this?". If no one responds → automatic revocation after 14 days.',
    'Aktivieren im Sharing-Monitor.' => 'Enable it in the sharing monitor.',
    'Befragungs-Mail-Text personalisieren (Firmen-Logo, Tonalität).' => 'Personalise the survey email text (company logo, tone).',
    'Auto-Widerruf konservativ einstellen (lieber Erinnerung statt sofortiger Widerruf).' => 'Configure auto-revocation conservatively (prefer a reminder over immediate revocation).',
    'Sharing-Monitor' => 'Sharing Monitor',

    // ── PHASE 7 ─────────────────────────────────────────────────────
    'Phase 7 — Wöchentliche Routine' => 'Phase 7 — Weekly Routine',
    'Jede Woche · ca. 5 Minuten' => 'Every week · approx. 5 minutes',
    'Diese Liste wandert in deinen Kalender. Sie ersetzt nicht die täglichen E-Mail-Alerts — sie sorgt dafür, dass nichts „stillschweigend" stehen bleibt.' => 'This list goes into your calendar. It does not replace the daily email alerts — it makes sure nothing is left "quietly" unattended.',

    'Dashboard + Notifications-Glocke' => 'Dashboard + notifications bell',
    'KPI-Sparklines zeigen Trend-Veränderungen, die einzelne Module nicht erkennen können (z. B. „MFA-Abdeckung sinkt seit 3 Wochen langsam").' => 'KPI sparklines show trend changes that individual modules cannot detect (e.g. "MFA coverage has been slowly declining for 3 weeks").',
    'Sparklines auf signifikante Ausschläge nach unten prüfen.' => 'Check the sparklines for significant downward swings.',
    'Glocke (oben rechts) → letzte Events kurz durchgehen.' => 'Bell (top right) → quickly go through the latest events.',
    'Bei rotem Pfeil: zugehöriges Modul öffnen, Ursache klären.' => 'On a red arrow: open the relevant module and determine the cause.',
    'Dashboard' => 'Dashboard',

    'Risk-Module bewerten' => 'Assess the risk modules',
    'Riskante Anmeldungen, Defender-Alerts und MFA-Fatigue / Insider-Threat-Signale gehören jede Woche durchgesehen — auch wenn null. Dann ist es ein „kein-Vorfall"-Beleg.' => 'Risky sign-ins, Defender alerts and MFA-fatigue / insider-threat signals should be reviewed every week — even if they are zero. Then it serves as a "no-incident" record.',
    'Riskante Anmeldungen: jede bewerten („compromised" oder „dismissed").' => 'Risky sign-ins: assess each one ("compromised" or "dismissed").',
    'Defender-Alerts: jede triagieren.' => 'Defender alerts: triage each one.',
    'MFA-Fatigue: ungewöhnliche Zahlen → Auth-Strength prüfen.' => 'MFA fatigue: unusual numbers → check auth strength.',
    'Insider-Threat: Top-3 Kandidaten ansehen.' => 'Insider threat: review the top 3 candidates.',
    'Risiko-Anmeldungen' => 'Risky sign-ins',

    'Auto-Forward-Audit' => 'Auto-forward audit',
    'Eine Inbox-Regel, die alle Mails an eine externe Adresse weiterleitet, ist der häufigste Exfiltrations-Pfad nach Account-Übernahme. Neue Treffer → sofort prüfen.' => 'An inbox rule that forwards all mail to an external address is the most common exfiltration path after an account takeover. New hits → check immediately.',
    'Treffer auf externe Domains öffnen.' => 'Open hits pointing to external domains.',
    'Mit dem User Rücksprache halten — meist „Phishing-Vorfall vor 2 Monaten".' => 'Check back with the user — usually "a phishing incident 2 months ago".',
    'Regel entfernen, MFA reset, Sessions revoken.' => 'Remove the rule, reset MFA, revoke sessions.',

    'Inaktive Konten & DLP-Vorfälle' => 'Inactive accounts & DLP incidents',
    'Inaktive Lizenzen kosten Geld; DLP-Treffer zeigen, was Mitarbeiter wirklich versucht haben weiterzuleiten.' => 'Inactive licences cost money; DLP hits show what employees actually tried to forward.',
    'Stale-Account-Liste durchgehen, Lizenzen entziehen.' => 'Go through the stale-account list and revoke licences.',
    'DLP-Treffer: ist es ein false-positive oder ein echter Vorfall? Bei echtem Vorfall: Mitarbeiter ansprechen, Policy ggf. anpassen.' => 'DLP hits: is it a false positive or a genuine incident? For a genuine incident: speak to the employee and adjust the policy if necessary.',
    'Inaktive Konten' => 'Inactive accounts',

    'Audit-Diff prüfen' => 'Review the audit diff',
    'Vergleicht den heutigen Snapshot mit dem von vor 7 Tagen. Zeigt unerwartete Änderungen sofort — z. B. „CA-Policy wurde deaktiviert" oder „Sharing-Capability wurde geöffnet".' => 'Compares today\'s snapshot with the one from 7 days ago. Surfaces unexpected changes immediately — e.g. "a CA policy was disabled" or "a sharing capability was opened up".',
    'Auf /auditdiff den heutigen und den vor 7 Tagen vergleichen.' => 'On /auditdiff, compare today\'s snapshot with the one from 7 days ago.',
    'Bei unerwarteten Änderungen → Audit-Log nach „wer war das" durchsuchen.' => 'On unexpected changes → search the audit log for "who did this".',
    'Audit-Diff' => 'Audit Diff',

    // ── PHASE 8 ─────────────────────────────────────────────────────
    'Phase 8 — Monatlich & vierteljährlich' => 'Phase 8 — Monthly & Quarterly',
    'Routine · ca. 30 Minuten/Monat' => 'Routine · approx. 30 minutes/month',
    'Was nicht jede Woche dran sein muss — aber jeden Monat oder jedes Quartal.' => 'What does not need attention every week — but every month or every quarter.',

    'DSGVO/NIS-2-PDF generieren & archivieren' => 'Generate & archive the GDPR/NIS-2 PDF',
    'Auditfähiger Bericht mit Tenant-Stammdaten + allen Hardening-Items + Zuordnung zu DSGVO/NIS-2/BSI-Artikeln. Monatlich für die Compliance-Akte.' => 'An audit-ready report with tenant master data + all hardening items + mapping to GDPR/NIS-2/BSI articles. Monthly, for the compliance file.',
    'Auf /auditreport → „Als PDF speichern" im Browser.' => 'On /auditreport → "Save as PDF" in the browser.',
    'PDF in den Compliance-Ordner (separat archivieren).' => 'PDF into the compliance folder (archive it separately).',
    'Datenschutzbeauftragten/IT-Leitung CC informieren.' => 'CC the data protection officer/IT leadership.',
    'Audit-Report' => 'Audit Report',

    'Access-Review-Lauf abschließen' => 'Complete the access-review run',
    'Wenn der Quartals-Review läuft: Entscheidungen einsammeln, anwenden, neuen Review-Lauf für nächstes Quartal anlegen.' => 'When the quarterly review is running: collect decisions, apply them and create a new review run for next quarter.',
    'Offenen Review öffnen, alle Entscheidungen durchgehen.' => 'Open the pending review and go through all decisions.',
    '„Apply" klicken — Entscheidungen werden ausgeführt.' => 'Click "Apply" — the decisions are executed.',
    'Neuen Review für nächstes Quartal anlegen.' => 'Create a new review for next quarter.',

    'Lizenz-Berater & Kosten-Trend' => 'License advisor & cost trend',
    'Welche Lizenzen werden tatsächlich genutzt? Wer hat zu hohe Lizenz für seinen Bedarf? Spart oft 5–15 % der M365-Kosten.' => 'Which licences are actually used? Who has too high a licence for their needs? Often saves 5–15% of M365 costs.',
    'Lizenz-Berater → Empfehlungen anschauen.' => 'License advisor → review the recommendations.',
    'Mit HR/Geschäftsführung absprechen.' => 'Coordinate with HR/management.',
    'Schrittweise umstellen, nicht alles auf einmal.' => 'Migrate step by step, not all at once.',
    'Lizenz-Berater' => 'License Advisor',

    'Secure-Score-Trend ansehen' => 'Review the Secure Score trend',
    'Microsofts eigene Score-Metrik. Punkte-Quelle: noch nicht umgesetzte Empfehlungen → wenig Aufwand für viel Score.' => 'Microsoft\'s own score metric. Source of points: recommendations not yet implemented → little effort for a lot of score.',
    '30/90-Tage-Trend ansehen.' => 'Review the 30/90-day trend.',
    'Top-5 ungeöffnete Empfehlungen umsetzen (oft 5–10 Minuten Aufwand).' => 'Implement the top 5 unopened recommendations (often 5–10 minutes of effort).',
    'Score-Sprung im Executive-Report dokumentieren.' => 'Document the score jump in the executive report.',
    'Secure Score' => 'Secure Score',

    'KI-Sicherheitsberater anstoßen' => 'Kick off the AI security advisor',
    'Konsolidierte Bewertung über BSI + NIS-2 + DSGVO + Anomalien hinweg. Nennt konkrete Top-Empfehlungen mit Quellen-Artikel.' => 'A consolidated assessment across BSI + NIS-2 + GDPR + anomalies. Names concrete top recommendations with source articles.',
    'Auf /ai → „Analyse starten".' => 'On /ai → "Start analysis".',
    'Empfehlungen priorisieren — was hat den höchsten Impact für den geringsten Aufwand?' => 'Prioritise the recommendations — which has the highest impact for the least effort?',
    'In dein Ticket-System übernehmen.' => 'Carry them over into your ticketing system.',
    'KI-Berater' => 'AI Advisor',

    'Break-Glass-Login testen' => 'Test the break-glass login',
    'Wenn du es nicht regelmäßig testest, weißt du im Ernstfall nicht, ob es noch funktioniert. Mindestens vierteljährlich.' => 'If you do not test it regularly, you will not know whether it still works when it matters. At least quarterly.',
    'Mit dem hinterlegten FIDO2-Key einloggen.' => 'Log in with the stored FIDO2 key.',
    'Datum + Tester im Break-Glass-Modul dokumentieren.' => 'Document the date + tester in the break-glass module.',
    'Nach Test sofort wieder ausloggen.' => 'Log out again immediately after the test.',
    'Break-Glass' => 'Break-Glass',
];
