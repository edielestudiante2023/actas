<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-light">Panel</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
            <div>
                <h4 class="mb-1">Usuarios</h4>
                <p class="text-muted mb-0">Usuarios de la plataforma y roles asignados por cliente.</p>
            </div>
            <a href="<?= base_url('usuarios/nuevo') ?>" class="btn btn-primary">Nuevo usuario</a>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Documento</th>
                            <th>Roles</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($usuarios === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay usuarios creados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($usuarios as $usuario): ?>
                            <?php $asignaciones = $rolesPorUsuario[(int) $usuario['id_usuario']] ?? []; ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($usuario['nombre_completo']) ?></div>
                                    <div class="small text-muted"><?= esc($usuario['email']) ?></div>
                                    <div class="small text-muted"><?= esc($usuario['telefono'] ?? '') ?></div>
                                </td>
                                <td><?= esc($usuario['tipo_documento']) ?> <?= esc($usuario['numero_documento']) ?></td>
                                <td>
                                    <?php if ($asignaciones === []): ?>
                                        <span class="text-muted small">Sin roles activos</span>
                                    <?php endif; ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($asignaciones as $asignacion): ?>
                                            <span class="badge text-bg-secondary">
                                                <?= esc($asignacion['rol_nombre']) ?>
                                                · <?= $asignacion['id_cliente'] === null ? 'Plataforma' : esc($asignacion['cliente_nombre']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $usuario['estado'] === 'activo' ? 'bg-success' : ($usuario['estado'] === 'bloqueado' ? 'bg-danger' : 'bg-secondary') ?>">
                                        <?= esc($usuario['estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        <a href="<?= base_url('usuarios/' . $usuario['id_usuario'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="<?= base_url('usuarios/' . $usuario['id_usuario'] . '/estado') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="estado" value="<?= $usuario['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <?= $usuario['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                        <?php if ($usuario['estado'] !== 'bloqueado'): ?>
                                            <form action="<?= base_url('usuarios/' . $usuario['id_usuario'] . '/estado') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="estado" value="bloqueado">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Bloquear</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <?= $this->include("partials/home_fab") ?>
    <?= $this->include("partials/notif_bell") ?>
</body>
</html>
