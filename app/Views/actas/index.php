<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actas · <?= esc($cliente['nombre'] ?? 'Cliente') ?></title>
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
                <h4 class="mb-1">Actas</h4>
                <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?></p>
            </div>
            <a href="<?= base_url('actas/nuevo') ?>" class="btn btn-primary">Nueva acta</a>
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
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Título</th>
                            <th>Modalidad</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($actas === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay actas creadas para este cliente.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($actas as $acta): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($acta['numero'] ?? 'Sin número') ?></div>
                                    <div class="small text-muted">Consecutivo <?= esc($acta['consecutivo'] ?? '') ?></div>
                                </td>
                                <td><?= esc($acta['fecha']) ?></td>
                                <td>
                                    <div><?= esc($acta['titulo'] ?? 'Acta de reunión') ?></div>
                                    <div class="small text-muted"><?= esc($acta['lugar'] ?? '') ?></div>
                                </td>
                                <td><?= esc($acta['modalidad']) ?></td>
                                <td>
                                    <span class="badge <?= $acta['estado'] === 'borrador' ? 'bg-secondary' : ($acta['estado'] === 'firmada' ? 'bg-success' : 'bg-primary') ?>">
                                        <?= esc($acta['estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if (in_array($acta['estado'], ['borrador', 'en_edicion'], true)): ?>
                                        <a href="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes') ?>" class="btn btn-sm btn-outline-success">Asistentes</a>
                                        <a href="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" class="btn btn-sm btn-outline-warning">Compromisos</a>
                                        <a href="<?= base_url('actas/' . $acta['id_acta'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <?php else: ?>
                                        <a href="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" class="btn btn-sm btn-outline-warning">Compromisos</a>
                                        <span class="text-muted small me-2">Solo lectura</span>
                                    <?php endif; ?>
                                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/firmas') ?>" class="btn btn-sm btn-outline-success">Firmas</a>
                                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/pdf') ?>" class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener">PDF</a>
                                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/word') ?>" class="btn btn-sm btn-outline-dark">Word</a>
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
