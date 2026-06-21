<?php

/**
 * English translations for review-round feature additions (secret-expiry
 * self-check, config-drift detection, action center).
 *
 * @return array<string,string>
 */
return [
    // App secret-expiry self-check (cron)
    'App-Secret-Ablauf prüfen' => 'Check app secret expiry',
    'Warnt, bevor das Client-Secret/Zertifikat der eigenen App-Registrierung abläuft (sonst verliert das Tool den Zugriff).'
        => "Warns before the tool's own app-registration client secret/certificate expires (otherwise the tool loses access).",
    'Kein Ablaufdatum ermittelbar.' => 'No expiry date could be determined.',
    'OK — läuft in :n Tagen ab.'    => 'OK — expires in :n days.',
    'Client-Secret'                 => 'Client secret',
    ':type der App-Registrierung ist ABGELAUFEN'
        => "The app registration's :type has EXPIRED",
    ':type der App-Registrierung läuft in :n Tagen ab'
        => "The app registration's :type expires in :n days",
    'Erneuere das :type im Microsoft Entra Admin Center, sonst verliert das Tool den Graph-Zugriff.'
        => 'Renew the :type in the Microsoft Entra admin center, otherwise the tool loses Graph access.',
    'Warnung gesendet — :type läuft in :n Tagen ab.'
        => 'Warning sent — :type expires in :n days.',
];
