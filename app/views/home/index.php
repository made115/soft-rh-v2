<?php require_once __DIR__ . '/../layouts/private_header.php'; ?>

<?php
$usuarioActual = current_user();
$rolActual = $usuarioActual['nombre_rol'] ?? '';

$nombreRolPanel = match ($rolActual) {
    'administrador' => 'Administrador',
    'recursos_humanos' => 'Recursos Humanos',
    'psicologia' => 'Psicología',
    default => 'Usuario',
};

$tituloMenu = match ($rolActual) {
    'administrador' => 'Menú del Administrador',
    'recursos_humanos' => 'Menú de Recursos Humanos',
    'psicologia' => 'Menú de Psicología',
    default => 'Menú principal',
};

$subtituloPanel = match ($rolActual) {
    'administrador' => 'Panel del Administrador',
    'recursos_humanos' => 'Panel de Recursos Humanos',
    'psicologia' => 'Panel de Psicología',
    default => 'Panel principal',
};
?>

<h1 class="page-title"><?= e($tituloMenu) ?></h1>

<section class="dashboard-welcome">
    <div>
        <h2>Bienvenido, <?= e(current_user()['nombre'] ?? 'Usuario') ?></h2>
        <p><?= e($subtituloPanel) ?></p>
    </div>

    <div class="dashboard-logo-image">
        <img src="<?= base_url('assets/img/logo-soft-rh.png') ?>" alt="Logo SOFT RH">
    </div>
</section>

<section class="dashboard-grid">
    <article class="metric-card">
        <div class="metric-icon">👤</div>
        <div>
            <p>Usuarios</p>
            <h3><?= (int) ($totalUsuarios ?? 0) ?></h3>
        </div>
    </article>

    <article class="metric-card">
        <div class="metric-icon">👥</div>
        <div>
            <p>Empleados</p>
            <h3><?= (int) ($totalEmpleados ?? 0) ?></h3>
        </div>
    </article>

    <article class="metric-card">
        <div class="metric-icon">📄</div>
        <div>
            <p>Contratos</p>
            <h3><?= (int) ($totalContratos ?? 0) ?></h3>
        </div>
    </article>
</section>

<section class="dashboard-panel">
    <div class="dashboard-panel-header">
        <div>
            <h2>Información importante</h2>
            <p class="dashboard-panel-subtitle">Resumen general para seguimiento administrativo</p>
        </div>

        <form method="POST" action="<?= base_url('logout') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-warning" type="submit">Cerrar sesión</button>
        </form>
    </div>

    <div class="important-grid">
        <article class="important-card">
            <div class="important-icon">⏳</div>
            <div>
                <h3>Contratos próximos a vencer</h3>
                <p><?= (int) ($contratosProximosVencer ?? 0) ?></p>
                <span>Vigentes con vencimiento en 15 días o menos</span>
            </div>
        </article>

        <article class="important-card">
            <div class="important-icon">🏖️</div>
            <div>
                <h3>Vacaciones próximas a iniciar</h3>
                <p><?= (int) ($vacacionesProximasIniciar ?? 0) ?></p>
                <span>Dato simulado para presentación</span>
            </div>
        </article>

        <article class="important-card">
            <div class="important-icon">📅</div>
            <div>
                <h3>Vacaciones próximas a terminar</h3>
                <p><?= (int) ($vacacionesProximasTerminar ?? 0) ?></p>
                <span>Dato simulado para presentación</span>
            </div>
        </article>

        <article class="important-card">
            <div class="important-icon">🧠</div>
            <div>
                <h3>Seguimientos psicológicos sin terminar</h3>
                <p><?= (int) ($seguimientosSinTerminar ?? 0) ?></p>
                <span>Dato simulado para presentación</span>
            </div>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>