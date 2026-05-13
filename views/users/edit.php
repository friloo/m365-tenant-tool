<?php use App\Core\View; $e = fn($v) => View::escape($v); ?>

<div class="mb-3">
    <a href="/users/<?= $e($user['id']) ?>" class="text-muted text-decoration-none small">← Zurück zu <?= $e($user['displayName'] ?? 'Benutzer') ?></a>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-success alert-dismissible mb-3"><i class="bi bi-check-circle me-2"></i><?= $e($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="content-card">
            <div class="card-header-custom">
                <i class="bi bi-pencil-square text-primary"></i>
                <h6>Benutzer bearbeiten</h6>
            </div>
            <div class="card-body-custom">

                <?php if (!empty($user['onPremisesSyncEnabled'])): ?>
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div>Dieser Benutzer wird aus dem lokalen Active Directory synchronisiert. Felder wie Abteilung und Jobtitel können beim nächsten Sync überschrieben werden.</div>
                    </div>
                <?php endif; ?>

                <form method="post" action="/users/<?= $e($user['id']) ?>/update">
                    <?= \App\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="displayName" class="form-label fw-medium">Anzeigename</label>
                        <input type="text" class="form-control" id="displayName" name="displayName"
                               value="<?= $e($user['displayName'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jobTitle" class="form-label fw-medium">Jobtitel</label>
                        <input type="text" class="form-control" id="jobTitle" name="jobTitle"
                               value="<?= $e($user['jobTitle'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="department" class="form-label fw-medium">Abteilung</label>
                        <input type="text" class="form-control" id="department" name="department"
                               value="<?= $e($user['department'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="mobilePhone" class="form-label fw-medium">Mobiltelefon</label>
                        <input type="text" class="form-control" id="mobilePhone" name="mobilePhone"
                               value="<?= $e($user['mobilePhone'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label for="officeLocation" class="form-label fw-medium">Bürostandort</label>
                        <input type="text" class="form-control" id="officeLocation" name="officeLocation"
                               value="<?= $e($user['officeLocation'] ?? '') ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Speichern
                        </button>
                        <a href="/users/<?= $e($user['id']) ?>" class="btn btn-outline-secondary">Abbrechen</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
