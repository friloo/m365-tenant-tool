<!DOCTYPE html>
<html lang="<?= \App\Core\View::escape(\App\Core\I18n::locale()) ?>">
<head><meta charset="UTF-8"><title>404</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="text-center">
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <p class="lead text-muted"><?= te('Seite nicht gefunden') ?></p>
    <a href="/" class="btn btn-primary"><?= te('Zum Dashboard') ?></a>
</div>
</body></html>
