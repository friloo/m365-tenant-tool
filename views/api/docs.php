<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>M365 Tenant Tool — API-Dokumentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>
        body { margin: 0; background: #fafafa; font-family: "Segoe UI", system-ui, -apple-system, sans-serif; }
        .topbar-mini {
            background: #0078d4; color: #fff; padding: 12px 24px;
            display: flex; align-items: center; gap: 16px;
        }
        .topbar-mini a { color: #fff; text-decoration: none; font-size: 13px; opacity: 0.9; }
        .topbar-mini a:hover { opacity: 1; text-decoration: underline; }
        .topbar-mini h1 { font-size: 17px; margin: 0; flex: 1; font-weight: 600; }
        #swagger-ui { max-width: 1200px; margin: 0 auto; padding: 16px; }
    </style>
</head>
<body>
<div class="topbar-mini">
    <h1><i class="bi bi-code-slash"></i> M365 Tenant Tool — REST-API</h1>
    <a href="/">← Zurück zur App</a>
    <a href="/settings/api-keys">API-Keys verwalten</a>
    <a href="/api/openapi.json" target="_blank">openapi.json</a>
</div>
<div id="swagger-ui"></div>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
<script>
window.onload = function() {
    window.ui = SwaggerUIBundle({
        url:    "/api/openapi.json",
        dom_id: "#swagger-ui",
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        layout: "BaseLayout",
        defaultModelsExpandDepth: -1,
        tryItOutEnabled: true,
        persistAuthorization: true,
    });
};
</script>
</body>
</html>
