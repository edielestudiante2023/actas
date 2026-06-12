<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consejo · <?= esc($cliente['nombre']) ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('clientes') ?>" class="btn btn-sm btn-outline-light">Clientes</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-3">
            <h4 class="mb-1">Consejo de administración</h4>
            <p class="text-muted mb-0"><?= esc($cliente['nombre']) ?></p>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('errors')): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header fw-semibold">Agregar miembro</div>
            <div class="card-body">
                <form action="<?= base_url('clientes/' . $cliente['id_cliente'] . '/consejo') ?>" method="post" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-12 col-lg-3">
                        <label class="form-label">Cargo</label>
                        <?php $cargo = old('cargo', 'consejero'); ?>
                        <select name="cargo" class="form-select" required>
                            <option value="presidente_consejo" <?= $cargo === 'presidente_consejo' ? 'selected' : '' ?>>Presidente del Consejo</option>
                            <option value="consejero" <?= $cargo === 'consejero' ? 'selected' : '' ?>>Consejero</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-5">
                        <label class="form-label">Usuario</label>
                        <select name="id_usuario" class="form-select" required>
                            <option value="">Selecciona usuario</option>
                            <?php if ($presidentesDisponibles !== []): ?>
                                <optgroup label="Usuarios con rol Presidente">
                                    <?php foreach ($presidentesDisponibles as $usuario): ?>
                                        <option value="<?= esc($usuario['id_usuario']) ?>" <?= (string) old('id_usuario') === (string) $usuario['id_usuario'] ? 'selected' : '' ?>>
                                            <?= esc($usuario['nombre_completo']) ?> · <?= esc($usuario['email']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <?php if ($consejerosDisponibles !== []): ?>
                                <optgroup label="Usuarios con rol Consejero">
                                    <?php foreach ($consejerosDisponibles as $usuario): ?>
                                        <option value="<?= esc($usuario['id_usuario']) ?>" <?= (string) old('id_usuario') === (string) $usuario['id_usuario'] ? 'selected' : '' ?>>
                                            <?= esc($usuario['nombre_completo']) ?> · <?= esc($usuario['email']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Primero asigna el rol al usuario en Administración de usuarios.</div>
                    </div>
                    <div class="col-12 col-lg-2">
                        <label class="form-label">Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="<?= esc(old('fecha_inicio', date('Y-m-d'))) ?>">
                    </div>
                    <div class="col-12 col-lg-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Agregar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Miembros</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Cargo</th>
                            <th>Periodo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($miembros === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay miembros registrados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($miembros as $miembro): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($miembro['nombre_completo']) ?></div>
                                    <div class="small text-muted"><?= esc($miembro['email']) ?></div>
                                </td>
                                <td>
                                    <?= $miembro['cargo'] === 'presidente_consejo' ? 'Presidente del Consejo' : 'Consejero' ?>
                                </td>
                                <td>
                                    <div><?= esc($miembro['fecha_inicio'] ?? '') ?></div>
                                    <div class="small text-muted"><?= $miembro['fecha_fin'] ? 'Hasta ' . esc($miembro['fecha_fin']) : 'Vigente' ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $miembro['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= esc($miembro['estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="<?= base_url('clientes/' . $cliente['id_cliente'] . '/consejo/' . $miembro['id'] . '/estado') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="estado" value="<?= $miembro['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <?= $miembro['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
