<?php
use App\Core\View;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css">

<div class="content-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 class="mb-1"><i class="bi bi-book text-primary"></i> <?= te('REST-API Dokumentation') ?></h1>
            <p class="text-muted mb-0"><?= te('Vollständige OpenAPI-3.0-Spezifikation aller verfügbaren Endpunkte. Über "Try it out" können Aufrufe direkt aus dem Browser getestet werden.') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/settings/api-keys" class="btn btn-primary"><i class="bi bi-key"></i> <?= te('API-Keys verwalten') ?></a>
            <a href="/api/openapi.json" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-filetype-json"></i> <?= te('Roh-Spec') ?></a>
        </div>
    </div>
    <div class="alert alert-info mt-3 mb-0">
        <i class="bi bi-info-circle"></i>
        <strong><?= te('So nutzt du die API:') ?></strong>
        <?= t('Klicke oben rechts in Swagger UI auf <code>Authorize</code> und füge deinen API-Key ein (erstellen unter <a href="/settings/api-keys">API-Schlüssel</a>) &mdash; dann sind alle Endpunkte direkt ausprobierbar.') ?>
    </div>
</div>

<div class="content-card p-0" style="overflow:hidden;">
    <div id="swagger-ui"></div>
</div>

<style>
    /* Swagger UI im App-Look einbetten */
    #swagger-ui { padding: 16px; }
    #swagger-ui .topbar { display: none; }   /* hide Swagger's own header */
    #swagger-ui .info { margin: 16px 0 24px; }
    #swagger-ui .info hgroup.main { display: flex; align-items: baseline; gap: 12px; }
    #swagger-ui .scheme-container { background: transparent; box-shadow: none; padding: 12px 0; }
    #swagger-ui .opblock-tag { font-size: 18px; }
    #swagger-ui .opblock { box-shadow: none; border-radius: 6px; }
    #swagger-ui .opblock .opblock-summary { padding: 8px 12px; }
    #swagger-ui pre.microlight, #swagger-ui .opblock-body pre {
        background: #1a1a2e !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
<script>
window.addEventListener('load', function () {
    window.ui = SwaggerUIBundle({
        url:    "/api/openapi.json",
        dom_id: "#swagger-ui",
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        layout: "BaseLayout",
        defaultModelsExpandDepth: -1,
        docExpansion: "list",
        tryItOutEnabled: true,
        persistAuthorization: true,
        filter: true,
    });
});
</script>
