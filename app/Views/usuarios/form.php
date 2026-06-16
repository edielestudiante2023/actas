<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isNew ? 'Nuevo usuario' : 'Editar usuario' ?> · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('usuarios') ?>" class="btn btn-sm btn-outline-light">Usuarios</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-3">
            <h4 class="mb-1"><?= $isNew ? 'Nuevo usuario' : 'Editar usuario' ?></h4>
            <p class="text-muted mb-0">Asigna roles por cliente para controlar el alcance de acceso.</p>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
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

        <form action="<?= esc($action) ?>" method="post" class="card">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" value="<?= esc(old('nombre_completo', $usuario['nombre_completo'] ?? '')) ?>" maxlength="200" required autofocus>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label">Tipo</label>
                        <?php $tipo = old('tipo_documento', $usuario['tipo_documento'] ?? 'CC'); ?>
                        <select name="tipo_documento" class="form-select" required>
                            <?php foreach (['CC', 'CE', 'PA', 'NIT'] as $option): ?>
                                <option value="<?= esc($option) ?>" <?= $tipo === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label">Documento</label>
                        <input type="text" name="numero_documento" class="form-control" value="<?= esc(old('numero_documento', $usuario['numero_documento'] ?? '')) ?>" maxlength="20" required>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= esc(old('email', $usuario['email'] ?? '')) ?>" maxlength="150" required>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= esc(old('telefono', $usuario['telefono'] ?? '')) ?>" maxlength="20">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Estado</label>
                        <?php $estado = old('estado', $usuario['estado'] ?? 'activo'); ?>
                        <select name="estado" class="form-select" required>
                            <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            <option value="bloqueado" <?= $estado === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label"><?= $isNew ? 'Contraseña temporal' : 'Nueva contraseña' ?></label>
                        <input type="password" name="password" class="form-control" minlength="8" <?= $isNew ? 'required' : '' ?>>
                        <div class="form-text"><?= $isNew ? 'Mínimo 8 caracteres.' : 'Déjala vacía para conservar la actual.' ?></div>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Roles</h5>

                <?php if ($rolSuperadmin !== null): ?>
                    <?php $checked = old('superadmin') ? true : isset($asignaciones['platform:' . $rolSuperadmin['id_rol']]); ?>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="rolSuperadmin" name="superadmin" value="<?= esc($rolSuperadmin['id_rol']) ?>" <?= $checked ? 'checked' : '' ?>>
                        <label class="form-check-label" for="rolSuperadmin">Superadmin de plataforma</label>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width:220px;">Cliente</th>
                                <?php foreach ($rolesCliente as $rol): ?>
                                    <th class="text-center"><?= esc($rol['nombre']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($clientes === []): ?>
                                <tr>
                                    <td colspan="<?= count($rolesCliente) + 1 ?>" class="text-center text-muted py-4">No hay clientes activos para asignar roles.</td>
                                </tr>
                            <?php endif; ?>

                            <?php $oldAsignaciones = (array) old('asignaciones', []); ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc($cliente['nombre']) ?></td>
                                    <?php foreach ($rolesCliente as $rol): ?>
                                        <?php
                                            $value = $cliente['id_cliente'] . ':' . $rol['id_rol'];
                                            $checked = in_array($value, $oldAsignaciones, true) || isset($asignaciones[$value]);
                                        ?>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="asignaciones[]" value="<?= esc($value) ?>" aria-label="<?= esc($rol['nombre'] . ' en ' . $cliente['nombre']) ?>" <?= $checked ? 'checked' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
