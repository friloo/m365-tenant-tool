<?php

namespace App\Graph;

/**
 * Übersetzt rohe Microsoft-Graph-Fehler in präzise, handlungsfähige
 * deutsche Hinweise. Verwendet wird das Result von GraphClient::getLastError()
 * oder eine vergleichbare Exception-Message.
 *
 * Liefert null, wenn kein Fehler vorliegt — der Caller soll dann die
 * generische "Keine Daten"-Anzeige verwenden.
 *
 * Rückgabe-Schema:
 *   [
 *     'type'   => string  Kategorie, z.B. 'license'|'permission'|'feature_disabled',
 *     'short'  => string  einzeilige Überschrift,
 *     'detail' => string  ausführlicher Hinweis mit Lösungsweg,
 *     'fix_url'=> string? optionaler Tool-interner Pfad zur Lösung
 *   ]
 */
class GraphErrorTranslator
{
    /**
     * @param array{status?:int,code?:string,message?:string,url?:string}|null $err
     * @param string|null $requiredPermission optional — wenn der Caller weiß,
     *                                        welche Permission der Endpunkt braucht,
     *                                        wird sie im Hint genannt.
     * @return array{type:string,short:string,detail:string,fix_url?:string}|null
     */
    public static function translate(?array $err, ?string $requiredPermission = null): ?array
    {
        if (!$err) return null;
        $status = (int)($err['status']  ?? 0);
        $code   = (string)($err['code'] ?? '');
        $msg    = (string)($err['message'] ?? '');
        $lc     = strtolower($msg);

        // 1) Bestimmte Lizenz-/Tenant-spezifische Fehler (am präzisesten zuerst)
        if (stripos($msg, 'SPO license') !== false
            || (stripos($msg, 'SharePoint') !== false && stripos($msg, 'license') !== false)) {
            return [
                'type'   => 'license',
                'short'  => 'SharePoint nicht lizenziert',
                'detail' => 'Der Tenant hat keine SharePoint-Online-Lizenz. Diese Funktion ist daher nicht zutreffend.',
            ];
        }
        if (stripos($msg, 'not applicable to target tenant') !== false) {
            return [
                'type'   => 'not_applicable',
                'short'  => 'Im Tenant nicht aktiviert',
                'detail' => 'Microsoft Graph meldet "Request not applicable to target tenant". Häufige Gründe: passende Lizenz fehlt (z.B. Intune/EM&S für /deviceManagement, Office-365-Subscription für Reports), Dienst ist im Tenant nicht aktiviert, oder der Tenant-Typ (B2C, Government, Sovereign Cloud) unterstützt diesen Endpunkt nicht.',
            ];
        }
        if (stripos($msg, 'Tenant does not have') !== false && stripos($msg, 'license') !== false) {
            return [
                'type'   => 'license',
                'short'  => 'Lizenz fehlt im Tenant',
                'detail' => 'Microsoft Graph meldet: ' . self::trimMsg($msg),
            ];
        }
        if (stripos($msg, 'anonymized') !== false || stripos($msg, 'anonym') !== false) {
            return [
                'type'   => 'feature_disabled',
                'short'  => 'Datenschutzmodus für Berichte aktiv',
                'detail' => 'Im Microsoft-365-Admin-Center ist der Datenschutz für Berichtsdaten aktiviert — die Endpunkte liefern dann anonymisierte oder leere Daten. Lösungsweg: Admin-Center → Einstellungen → Org-Einstellungen → Berichte → "Verborgene Benutzer-/Gruppen-/Site-Namen anzeigen" einschalten.',
            ];
        }

        // 2) Status-basierte generische Hinweise
        if ($status === 401 || stripos($code, 'invalidauthentication') !== false) {
            return [
                'type'    => 'authentication',
                'short'   => 'Token ungültig oder abgelaufen',
                'detail'  => 'Microsoft Graph hat den Zugriff abgelehnt (401). Bitte Token-Aktualisierung versuchen.',
                'fix_url' => '/settings/refresh-token',
            ];
        }
        if ($status === 403 || stripos($code, 'authorization_requestdenied') !== false
            || stripos($code, 'insufficientprivileges') !== false || stripos($code, 'invalidscope') !== false) {
            $permHint = $requiredPermission ? " Konkret benötigt: <code>{$requiredPermission}</code>." : '';
            return [
                'type'    => 'permission',
                'short'   => 'Berechtigung fehlt oder kein Admin Consent',
                'detail'  => 'Microsoft Graph hat die Anfrage abgelehnt (403).' . $permHint
                          . ' Mögliche Ursachen: Die App-Berechtigung ist in Azure noch nicht eingetragen, oder ein Global Admin hat noch keinen Admin Consent erteilt. Unter Einstellungen → Berechtigungen prüfen welche fehlt.',
                'fix_url' => '/settings/permissions',
            ];
        }
        if ($status === 404) {
            $url = (string)($err['url'] ?? '');
            // Reports-API spezifisch: hier ist 404 fast immer kein
            // "Pfad existiert nicht", sondern eine der drei typischen
            // Tenant-Konfigurationen — Microsoft gibt für alle drei
            // denselben Statuscode zurück.
            if (str_contains($url, '/reports/')) {
                return [
                    'type'   => 'reports_unavailable',
                    'short'  => 'Reports-API liefert keine Daten (404)',
                    'detail' => 'Der Endpunkt ' . self::trimMsg($url) . ' antwortet mit 404. '
                              . 'Microsoft gibt diesen Statuscode aus drei Gründen — bitte einen davon prüfen: '
                              . '(1) Tenant hat keinen Office-365-Plan, der Aktivitätsberichte unterstützt (E1/E3/E5 oder Business). '
                              . '(2) Datenschutz für Berichte ist aktiviert — Admin Center → Einstellungen → Org-Einstellungen → Berichte → "Verborgene Namen anzeigen". '
                              . '(3) Reports.Read.All-Permission fehlt im App-Token (zwar oft 403, aber bei manchen Lizenz-Kombinationen wird 404 gemeldet). '
                              . 'Original-Antwort: ' . self::trimMsg($msg),
                    'fix_url' => '/settings/permissions',
                ];
            }
            return [
                'type'   => 'not_found',
                'short'  => 'Endpunkt oder Ressource nicht gefunden (404)',
                'detail' => 'Microsoft Graph antwortet mit 404 für ' . self::trimMsg($url) . '. '
                          . 'Mögliche Gründe: API-Pfad in neuerer Graph-Version umbenannt, Ressource im Tenant nicht angelegt, oder Tenant-Typ unterstützt diesen Endpunkt nicht. '
                          . 'Original-Antwort: ' . self::trimMsg($msg),
            ];
        }
        if ($status === 429) {
            return [
                'type'   => 'rate_limit',
                'short'  => 'Rate-Limit erreicht',
                'detail' => 'Microsoft Graph drosselt die Anfragen (429). Bitte später erneut versuchen.',
            ];
        }
        if ($status >= 500) {
            return [
                'type'   => 'server_error',
                'short'  => 'Microsoft Graph antwortet mit Server-Fehler',
                'detail' => 'Microsoft Graph hat den Status ' . $status . ' geliefert. Das ist meist temporär — bitte später erneut versuchen.',
            ];
        }

        // 3) Fallback: rohe Botschaft anzeigen, damit der Admin selbst entscheiden kann
        return [
            'type'   => 'unknown',
            'short'  => 'Microsoft Graph: Fehler',
            'detail' => 'HTTP ' . ($status ?: '?') . ' — ' . self::trimMsg($msg ?: $code ?: 'unbekannt'),
        ];
    }

    /**
     * Helper-Pattern: führt einen Service-Aufruf aus, fängt Throwables,
     * konsultiert nach Erfolg getLastError() (für 403/404 die der GraphClient
     * still swalloed), und liefert ['data' => …, 'diag' => …|null].
     *
     * Beispiel:
     *   ['data' => $users, 'diag' => $diag] = GraphErrorTranslator::guard(
     *       fn() => $svc->getAll(),
     *       'User.Read.All'
     *   );
     */
    public static function guard(callable $fn, ?string $permission = null, mixed $emptyValue = []): array
    {
        try {
            $result = $fn();
            $diag   = null;
            // Wenn der Result leer ist UND der GraphClient zuletzt einen
            // (stillschweigend geswallowed-en) Fehler hatte, surface ihn.
            $isEmpty = $result === null || $result === [] || $result === '' || $result === false;
            if ($isEmpty) {
                $err = function_exists('app_graph') ? app_graph()->getLastError() : null;
                $diag = self::translate($err, $permission);
            }
            return ['data' => $result, 'diag' => $diag];
        } catch (\Throwable $e) {
            return ['data' => $emptyValue, 'diag' => self::fromThrowable($e, $permission)];
        }
    }

    /**
     * Bequemer Wrapper: nimmt eine Throwable-Message und produziert die
     * gleiche Diagnose. Wird genutzt, wenn der Caller nur eine Exception hat
     * und nicht das strukturierte lastError-Array.
     */
    public static function fromThrowable(\Throwable $e, ?string $requiredPermission = null): array
    {
        $msg = $e->getMessage();
        // Heuristik: HTTP-Code aus "Graph API error on URL: HTTP 403" extrahieren
        $status = 0;
        if (preg_match('/HTTP\s+(\d{3})/', $msg, $m)) $status = (int)$m[1];
        $hint = self::translate(['status' => $status, 'message' => $msg], $requiredPermission);
        return $hint ?? [
            'type'   => 'unknown',
            'short'  => 'Fehler',
            'detail' => self::trimMsg($msg),
        ];
    }

    private static function trimMsg(string $msg): string
    {
        // Lange URLs im Fehlertext sind verwirrend; auf das Wesentliche kürzen.
        $msg = preg_replace('#https://graph\.microsoft\.com/[^\s:]+:?#', '', $msg) ?? $msg;
        $msg = preg_replace('#^Graph API error on\s*:?\s*#i', '', $msg) ?? $msg;
        return trim($msg);
    }
}
