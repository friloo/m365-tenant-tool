<?php
$e = fn($v) => \App\Core\View::escape($v);
$adoptionActive = (($_GET['tab'] ?? '') === 'adoption');
?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $adoptionActive ? '' : 'active' ?>" data-bs-toggle="tab" data-bs-target="#tab-usage" type="button" role="tab">
            <i class="bi bi-bar-chart-steps me-1"></i>Nutzungsberichte
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $adoptionActive ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-adoption" type="button" role="tab">
            <i class="bi bi-graph-up-arrow me-1"></i>Adoption
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade <?= $adoptionActive ? '' : 'show active' ?>" id="tab-usage" role="tabpanel">
        <?php include BASE_PATH . '/views/usagereports/index.php'; ?>
    </div>
    <div class="tab-pane fade <?= $adoptionActive ? 'show active' : '' ?>" id="tab-adoption" role="tabpanel">
        <?php include BASE_PATH . '/views/adoption/index.php'; ?>
    </div>
</div>
