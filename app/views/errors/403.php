<?php require_once __DIR__ . '/../layouts/private_header.php'; ?>

<h1 class="page-title">Acceso denegado</h1>

<section class="working-wrapper">
    <div class="working-card">
        <div class="working-icon">🔒</div>

        <h2>No tienes permiso para acceder a este módulo</h2>

        <p>
            Tu rol actual no cuenta con autorización para utilizar esta sección del sistema.
        </p>

        <div class="working-actions">
            <a class="btn btn-primary" href="<?= base_url('/') ?>">Volver al inicio</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>