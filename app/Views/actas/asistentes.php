<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistentes · <?= esc($acta['numero']) ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
            <div>
                <h4 class="mb-1">Asistentes y quórum</h4>
                <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?> · <?= esc($acta['numero']) ?></p>
            </div>
            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes/importar-consejo') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Importar consejo</button>
            </form>
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

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Miembros consejo</div>
                        <div class="fs-4 fw-semibold"><?= esc($quorum['total']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Presentes</div>
                        <div class="fs-4 fw-semibold"><?= esc($quorum['presentes']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Requeridos</div>
                        <div class="fs-4 fw-semibold"><?= esc($quorum['requerido']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Quórum</div>
                        <div class="fs-5 fw-semibold <?= $quorum['cumple'] ? 'text-success' : 'text-danger' ?>">
                            <?= $quorum['cumple'] ? 'Cumple' : 'No cumple' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asistente</th>
                            <th>Cargo</th>
                            <th>Asistencia</th>
                            <th>Firma</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($asistentes === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Importa el consejo del cliente para cargar asistentes.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($asistentes as $asistente): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($asistente['nombre']) ?></div>
                                    <div class="small text-muted"><?= esc($asistente['email'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div><?= esc($asistente['cargo'] ?? '') ?></div>
                                    <div class="small text-muted"><?= esc($asistente['tipo']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $asistente['asistencia'] === 'asiste' ? 'bg-success' : ($asistente['asistencia'] === 'excusa' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                        <?= esc($asistente['asistencia']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $asistente['firma_estado'] === 'firmada' ? 'bg-success' : ($asistente['firma_estado'] === 'pendiente' ? 'bg-primary' : 'bg-secondary') ?>">
                                        <?= esc($asistente['firma_estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes/' . $asistente['id_asistente']) ?>" method="post" class="d-inline-flex gap-2 align-items-center">
                                        <?= csrf_field() ?>
                                        <select name="asistencia" class="form-select form-select-sm" style="width:auto;">
                                            <option value="asiste" <?= $asistente['asistencia'] === 'asiste' ? 'selected' : '' ?>>Asiste</option>
                                            <option value="no_asiste" <?= $asistente['asistencia'] === 'no_asiste' ? 'selected' : '' ?>>No asiste</option>
                                            <option value="excusa" <?= $asistente['asistencia'] === 'excusa' ? 'selected' : '' ?>>Excusa</option>
                                        </select>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requiere_firma" value="1" id="firma<?= esc($asistente['id_asistente']) ?>" <?= (int) $asistente['requiere_firma'] === 1 ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="firma<?= esc($asistente['id_asistente']) ?>">Firma</label>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
