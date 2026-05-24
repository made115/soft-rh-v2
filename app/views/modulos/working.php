<?php require_once __DIR__ . '/../layouts/private_header.php'; ?>

<h1 class="page-title"><?= e($title ?? 'Módulo') ?></h1>

<section class="working-wrapper">
    <div class="working-card">
        <div class="working-icon"><?= e($icon ?? '⚙️') ?></div>

        <h2><?= e($heading ?? 'Módulo en preparación') ?></h2>

        <p><?= e($description ?? 'Este módulo será desarrollado posteriormente.') ?></p>

        <div class="working-status">
            <span>Estado actual</span>
            <strong><?= e($status ?? 'En preparación') ?></strong>
        </div>

        <div class="working-actions">
            <a class="btn btn-primary" href="<?= base_url('/') ?>">Volver al inicio</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>