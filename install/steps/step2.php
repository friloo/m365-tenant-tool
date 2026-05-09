<h5 class="card-title mb-4">Schritt 2 — Admin-Konto</h5>
<p class="text-muted small mb-4">Erstelle das lokale Administrator-Konto für dieses Tool.</p>

<form method="post" action="?step=2">
    <div class="mb-3">
        <label class="form-label">Benutzername</label>
        <input type="text" name="admin_user" class="form-control" placeholder="admin" minlength="3" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Passwort</label>
        <input type="password" name="admin_password" class="form-control" minlength="8" required>
        <div class="form-text">Mindestens 8 Zeichen.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Passwort bestätigen</label>
        <input type="password" name="admin_confirm" class="form-control" required>
    </div>
    <div class="alert alert-info small">
        <strong>Hinweis:</strong> Das Passwort wird verschlüsselt in der Datenbank gespeichert.
    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="?step=1" class="btn btn-outline-secondary">← Zurück</a>
        <button type="submit" class="btn btn-primary px-4">Weiter →</button>
    </div>
</form>
