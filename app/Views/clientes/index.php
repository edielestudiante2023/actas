<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes · Actas</title>
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
                <h4 class="mb-1">Clientes</h4>
                <p class="text-muted mb-0">Propiedades horizontales administradas en la plataforma.</p>
            </div>
            <a href="<?= base_url('clientes/nuevo') ?>" class="btn btn-primary">Nuevo cliente</a>
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
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Ciudad</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($clientes === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay clientes creados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (! empty($cliente['logo'])): ?>
                                            <img src="<?= base_url('clientes/' . $cliente['id_cliente'] . '/logo') ?>" alt="<?= esc($cliente['nombre']) ?>" style="width:48px;height:48px;object-fit:contain;border:1px solid #dee2e6;border-radius:8px;background:#fff;">
                                        <?php else: ?>
                                            <div class="bg-white border rounded d-flex align-items-center justify-content-center text-muted fw-semibold" style="width:48px;height:48px;">
                                                <?= esc(mb_strtoupper(mb_substr($cliente['nombre'], 0, 1))) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold"><?= esc($cliente['nombre']) ?></div>
                                            <div class="small text-muted"><?= esc($cliente['direccion'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($cliente['nit'] ?? '') ?></td>
                                <td><?= esc($cliente['ciudad'] ?? '') ?></td>
                                <td>
                                    <div><?= esc($cliente['email'] ?? '') ?></div>
                                    <div class="small text-muted"><?= esc($cliente['telefono'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $cliente['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= esc($cliente['estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?= base_url('clientes/' . $cliente['id_cliente'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="<?= base_url('clientes/' . $cliente['id_cliente'] . '/estado') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <?= $cliente['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                    </div>
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
