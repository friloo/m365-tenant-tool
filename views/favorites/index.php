<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); ?>

<div class="alert alert-light border d-flex align-items-start gap-2 mb-4">
  <i class="bi bi-star-fill text-warning mt-1"></i>
  <div class="small">
    <?= te('Hier liegen deine angepinnten Seiten. Öffne ein beliebiges Modul und tippe oben rechts auf den') ?>
    <i class="bi bi-star"></i><?= te('-Stern, um es zu den Favoriten hinzuzufügen. Die Favoriten werden lokal in diesem Browser gespeichert.') ?>
  </div>
</div>

<div id="favEmpty" class="content-card text-center text-muted py-5" style="display:none;">
  <i class="bi bi-stars" style="font-size:40px;display:block;margin-bottom:10px;opacity:.5;"></i>
  <?= te('Noch keine Favoriten. Tippe auf einer Modulseite oben rechts auf den Stern.') ?>
</div>

<div class="row g-3" id="favGrid"></div>
