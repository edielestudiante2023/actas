<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compromisos · <?= esc($cliente['nombre'] ?? 'Actas') ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-light">Panel</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-3">
            <h4 class="mb-1">Compromisos del conjunto</h4>
            <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?></p>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('errors')): ?>
            <div class="alert alert-danger py-2"><ul class="mb-0"><?php foreach (session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <div class="btn-group mb-3" role="group" aria-label="Filtro de compromisos">
            <a href="<?= base_url('compromisos?mios=0') ?>" class="btn btn-sm <?= $verMios ? 'btn-outline-primary' : 'btn-primary' ?>">Todos (<?= (int) $countTodos ?>)</a>
            <a href="<?= base_url('compromisos?mios=1') ?>" class="btn btn-sm <?= $verMios ? 'btn-primary' : 'btn-outline-primary' ?>">Míos (<?= (int) $countMios ?>)</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-secondary">Total: <?= (int) $resumen['total'] ?></span>
            <span class="badge bg-warning text-dark">Pendientes: <?= (int) $resumen['pendiente'] ?></span>
            <span class="badge bg-primary">En progreso: <?= (int) $resumen['en_progreso'] ?></span>
            <span class="badge bg-success">Cumplidos: <?= (int) $resumen['cumplido'] ?></span>
            <span class="badge bg-danger">Vencidos: <?= (int) $resumen['vencido'] ?></span>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Compromiso</th>
                            <th>Acta</th>
                            <th>Responsable</th>
                            <th>Vence</th>
                            <th>Estado</th>
                            <th>Avance</th>
                            <th class="text-end">Actualizar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($compromisos === []): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay compromisos registrados en este conjunto.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($compromisos as $c): ?>
                            <?php
                                $estado = (string) $c['estado'];
                                $badge = match ($estado) {
                                    'cumplido' => 'bg-success',
                                    'en_progreso' => 'bg-primary',
                                    'vencido' => 'bg-danger',
                                    'cancelado' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                            ?>
                            <tr>
                                <td style="min-width:240px;"><?= nl2br(esc($c['descripcion'])) ?></td>
                                <td>
                                    <a href="<?= base_url('actas/' . $c['id_acta'] . '/compromisos') ?>" class="text-decoration-none"><?= esc($c['acta_numero'] ?? ('#' . $c['id_acta'])) ?></a>
                                    <div class="small text-muted"><?= esc(str_replace('_', ' ', (string) ($c['acta_estado'] ?? ''))) ?></div>
                                </td>
                                <td>
                                    <div><?= esc($c['responsable_nombre'] ?? $c['usuario_nombre'] ?? 'Sin responsable') ?></div>
                                    <div class="small text-muted"><?= esc($c['usuario_email'] ?? '') ?></div>
                                </td>
                                <td><?= esc($c['fecha_vencimiento'] ?? 'Sin fecha') ?></td>
                                <td><span class="badge <?= $badge ?>"><?= esc(str_replace('_', ' ', $estado)) ?></span></td>
                                <td style="min-width:130px;">
                                    <div class="progress" role="progressbar" aria-valuenow="<?= esc($c['avance']) ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: <?= esc($c['avance']) ?>%;"><?= esc($c['avance']) ?>%</div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <form action="<?= base_url('compromisos/' . $c['id_compromiso']) ?>" method="post" class="d-inline-flex gap-1 align-items-center justify-content-end">
                                        <?= csrf_field() ?>
                                        <select name="estado" class="form-select form-select-sm" style="width:auto;">
                                            <?php foreach ($estados as $op): ?>
                                                <option value="<?= esc($op) ?>" <?= $estado === $op ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $op)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="number" name="avance" class="form-control form-control-sm" value="<?= esc($c['avance']) ?>" min="0" max="100" style="width:80px;">
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
