<h5 class="card-title mb-4">Schritt 1 — Datenbankverbindung</h5>
<p class="text-muted small mb-4">Gib die MySQL-Zugangsdaten ein. Die Tabellen werden automatisch angelegt.</p>

<form method="post" action="?step=1">
    <div class="row g-3">
        <div class="col-8">
            <label class="form-label">Datenbankhost</label>
            <input type="text" name="db_host" class="form-control" value="localhost" required>
        </div>
        <div class="col-4">
            <label class="form-label">Port</label>
            <input type="number" name="db_port" class="form-control" value="3306" required>
        </div>
        <div class="col-12">
            <label class="form-label">Datenbankname</label>
            <input type="text" name="db_name" class="form-control" placeholder="m365tool" required>
        </div>
        <div class="col-6">
            <label class="form-label">Benutzer</label>
            <input type="text" name="db_user" class="form-control" placeholder="root" required>
        </div>
        <div class="col-6">
            <label class="form-label">Passwort</label>
            <input type="password" name="db_password" class="form-control" placeholder="optional">
        </div>
    </div>
    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary px-4">Verbinden &amp; weiter →</button>
    </div>
</form>
