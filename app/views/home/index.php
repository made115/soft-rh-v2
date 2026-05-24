<?php require_once __DIR__ . '/../layouts/private_header.php'; ?>

<h1 class="page-title">Menú del Administrador</h1>

<section class="dashboard-welcome">
    <div>
        <h2>Bienvenido, <?= e(current_user()['nombre'] ?? 'Usuario') ?></h2>
        <p>Panel principal de SOFT RH V2</p>
    </div>

    <div class="dashboard-logo">
        SOFT <span>RH</span>
    </div>
</section>

<section class="dashboard-grid">
    <article class="metric-card">
        <div class="metric-icon">👤</div>
        <div>
            <p>Usuarios</p>
            <h3>0</h3>
        </div>
    </article>

    <article class="metric-card">
        <div class="metric-icon">👥</div>
        <div>
            <p>Empleados</p>
            <h3>0</h3>
        </div>
    </article>

    <article class="metric-card">
        <div class="metric-icon">📄</div>
        <div>
            <p>Contratos</p>
            <h3>0</h3>
        </div>
    </article>
</section>

<section class="dashboard-panel">
    <div class="dashboard-panel-header">
        <h2>Información de la sesión</h2>

        <form method="POST" action="<?= base_url('logout') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-warning" type="submit">Cerrar sesión</button>
        </form>
    </div>

    <div class="table-box dashboard-table-box">
        <table class="data-table dashboard-table">
            <tbody>
                <tr>
                    <th>Nombre</th>
                    <td><?= e(current_user()['nombre'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Usuario</th>
                    <td><?= e(current_user()['nombre_usuario'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Rol</th>
                    <td><?= e(current_user()['nombre_rol'] ?? '') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/private_footer.php'; ?>