<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <span class="navbar-brand fw-bold">Actas</span>
        <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
    </nav>

    <div class="container py-4">
        <h4>Hola, <?= esc(session('nombre')) ?></h4>
        <p class="text-muted mb-3"><?= esc(session('email')) ?></p>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <?php if (session('es_superadmin')): ?>
            <div class="mb-3 d-flex flex-wrap gap-2">
                <a href="<?= base_url('clientes') ?>" class="btn btn-primary">Administrar clientes</a>
                <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-primary">Administrar usuarios</a>
            </div>
        <?php endif; ?>

        <?php if ($cliente_activo !== null): ?>
            <div class="mb-3">
                <a href="<?= base_url('actas') ?>" class="btn btn-success">Gestionar actas</a>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header fw-semibold">Cliente activo</div>
            <div class="card-body">
                <?php if ($cliente_activo !== null): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <?php if (! empty($cliente_activo['logo'])): ?>
                            <img src="<?= base_url('clientes/' . $cliente_activo['id_cliente'] . '/logo') ?>" alt="<?= esc($cliente_activo['nombre']) ?>" style="width:56px;height:56px;object-fit:contain;border:1px solid #dee2e6;border-radius:8px;background:#fff;">
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold"><?= esc($cliente_activo['nombre']) ?></div>
                            <div class="text-muted small">ID <?= esc($cliente_activo['id_cliente']) ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3">No hay cliente activo seleccionado.</p>
                <?php endif; ?>

                <?php $clientesDisponibles = $clientes_disponibles ?? []; ?>
                <?php if ($clientesDisponibles !== []): ?>
                    <form action="<?= base_url('clientes/activo') ?>" method="post" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Seleccionar cliente</label>
                            <select name="id_cliente" class="form-select">
                                <?php if (session('es_superadmin')): ?>
                                    <option value="">Sin cliente activo</option>
                                <?php endif; ?>
                                <?php foreach ($clientesDisponibles as $cliente): ?>
                                    <option value="<?= esc($cliente['id_cliente']) ?>" <?= (int) ($cliente_activo['id_cliente'] ?? 0) === (int) $cliente['id_cliente'] ? 'selected' : '' ?>>
                                        <?= esc($cliente['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <button type="submit" class="btn btn-outline-primary w-100">Aplicar</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-muted mb-0">No tienes clientes activos disponibles.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Tus roles</div>
            <ul class="list-group list-group-flush">
                <?php foreach ((session('roles_full') ?? []) as $r): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= esc($r['nombre']) ?></span>
                        <span class="badge bg-secondary">
                            <?= $r['id_cliente'] === null ? 'Plataforma' : 'Cliente #' . esc($r['id_cliente']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <p class="text-muted mt-4 small">Siguiente: completar asistentes, quórum y compromisos del acta. Ver <code>roadmap.md</code>.</p>
    </div>
</body>
</html>
