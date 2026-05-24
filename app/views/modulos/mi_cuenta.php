<?php require_once __DIR__ . '/../layouts/private_header.php'; ?>

<h1 class="page-title">Mi cuenta</h1>

<section class="account-wrapper">
    <div class="account-card">
        <div class="account-header">
            <div class="account-avatar">
                <?= e(strtoupper(substr(current_user()['nombre'] ?? 'U', 0, 1))) ?>
            </div>

            <div>
                <h2><?= e(current_user()['nombre'] ?? '') ?></h2>
                <p><?= e(current_user()['nombre_usuario'] ?? '') ?></p>
            </div>
        </div>

        <div class="account-info-grid">
            <div class="account-info-item">
                <span>Nombre</span>
                <strong><?= e(current_user()['nombre'] ?? '') ?></strong>
            </div>

            <div class="account-info-item">
                <span>Usuario</span>
                <strong><?= e(current_user()['nombre_usuario'] ?? '') ?></strong>
            </div>

            <div class="account-info-item">
                <span>Rol</span>
                <strong><?= e(current_user()['nombre_rol'] ?? '') ?></strong>
            </div>
        </div>

        <div class="account-actions">
            <a class="btn btn-secondary" href="<?= base_url('/') ?>">Volver al inicio</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>