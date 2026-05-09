<h5 class="card-title mb-4">Schritt 4 — Einstellungen</h5>

<form method="post" action="?step=4">
    <div class="mb-3">
        <label class="form-label">Anwendungsname</label>
        <input type="text" name="app_name" class="form-control" value="M365 Tenant Tool" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Cache-Dauer (Minuten)</label>
        <select name="cache_ttl" class="form-select">
            <option value="5">5 Minuten</option>
            <option value="15" selected>15 Minuten (empfohlen)</option>
            <option value="30">30 Minuten</option>
            <option value="60">1 Stunde</option>
        </select>
        <div class="form-text">Wie lange Graph-API-Daten lokal gecacht werden.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Zeitzone</label>
        <select name="timezone" class="form-select">
            <option value="Europe/Berlin" selected>Europe/Berlin (MEZ/MESZ)</option>
            <option value="Europe/Vienna">Europe/Vienna</option>
            <option value="Europe/Zurich">Europe/Zurich</option>
            <option value="UTC">UTC</option>
        </select>
    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="?step=3" class="btn btn-outline-secondary">← Zurück</a>
        <button type="submit" class="btn btn-primary px-4">Weiter →</button>
    </div>
</form>
