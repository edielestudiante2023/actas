<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compromisos · <?= esc($acta['numero']) ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes') ?>" class="btn btn-sm btn-outline-light">Asistentes</a>
            <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
            <div>
                <h4 class="mb-1">Compromisos y tareas</h4>
                <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?> · <?= esc($acta['numero']) ?></p>
            </div>
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/editar') ?>" class="btn btn-outline-primary">Editar acta</a>
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

        <?php if ($editable): ?>
            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" method="post" class="card mb-3">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Compromiso</label>
                            <textarea name="descripcion" class="form-control" rows="3" required><?= esc(old('descripcion')) ?></textarea>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Responsable asistente</label>
                            <select name="id_responsable" class="form-select">
                                <option value="">Responsable manual</option>
                                <?php foreach ($responsables as $responsable): ?>
                                    <option value="<?= esc($responsable['id_usuario']) ?>" <?= old('id_responsable') == $responsable['id_usuario'] ? 'selected' : '' ?>>
                                        <?= esc($responsable['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Responsable manual</label>
                            <input type="text" name="responsable_nombre" class="form-control" value="<?= esc(old('responsable_nombre')) ?>" maxlength="200">
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Fecha de vencimiento</label>
                            <input type="date" name="fecha_vencimiento" class="form-control" value="<?= esc(old('fecha_vencimiento')) ?>">
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Estado</label>
                            <?php $estadoNuevo = old('estado', 'pendiente'); ?>
                            <select name="estado" class="form-select">
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= esc($estado) ?>" <?= $estadoNuevo === $estado ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $estado)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Avance</label>
                            <input type="number" name="avance" class="form-control" value="<?= esc(old('avance', '0')) ?>" min="0" max="100" required>
                        </div>
                        <div class="col-12 col-lg-4 d-flex align-items-end justify-content-lg-end">
                            <button type="submit" class="btn btn-primary w-100 w-lg-auto">Agregar compromiso</button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Compromiso</th>
                            <th>Responsable</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Avance</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($compromisos === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay compromisos registrados para esta acta.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($compromisos as $compromiso): ?>
                            <?php
                                $estado = (string) $compromiso['estado'];
                                $badge = match ($estado) {
                                    'cumplido' => 'bg-success',
                                    'en_progreso' => 'bg-primary',
                                    'vencido' => 'bg-danger',
                                    'cancelado' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                            ?>
                            <tr>
                                <td style="min-width: 280px;"><?= nl2br(esc($compromiso['descripcion'])) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($compromiso['responsable_nombre'] ?? $compromiso['usuario_nombre'] ?? 'Sin responsable') ?></div>
                                    <div class="small text-muted"><?= esc($compromiso['usuario_email'] ?? '') ?></div>
                                </td>
                                <td><?= esc($compromiso['fecha_vencimiento'] ?? 'Sin fecha') ?></td>
                                <td><span class="badge <?= $badge ?>"><?= esc(str_replace('_', ' ', $estado)) ?></span></td>
                                <td style="min-width: 140px;">
                                    <div class="progress" role="progressbar" aria-valuenow="<?= esc($compromiso['avance']) ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: <?= esc($compromiso['avance']) ?>%;"><?= esc($compromiso['avance']) ?>%</div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <?php if ($editable): ?>
                                        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos/' . $compromiso['id_compromiso']) ?>" method="post" class="d-inline-flex gap-2 align-items-center">
                                            <?= csrf_field() ?>
                                            <select name="estado" class="form-select form-select-sm" style="width:auto;">
                                                <?php foreach ($estados as $opcion): ?>
                                                    <option value="<?= esc($opcion) ?>" <?= $estado === $opcion ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $opcion)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="number" name="avance" class="form-control form-control-sm" value="<?= esc($compromiso['avance']) ?>" min="0" max="100" style="width: 88px;">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Solo lectura</span>
                                    <?php endif; ?>
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
