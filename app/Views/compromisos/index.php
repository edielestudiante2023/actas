<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compromisos · <?= esc($cliente['nombre'] ?? 'Actas') ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .chip { text-decoration: none; }
        .chip.activo { outline: 3px solid #16203a; outline-offset: 1px; }
        thead tr.filtros input { width: 100%; font-size: .8rem; padding: 2px 6px; }
    </style>
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

        <?php $m = $verMios ? 1 : 0; ?>
        <div class="btn-group mb-3" role="group" aria-label="Filtro de compromisos">
            <a href="<?= base_url('compromisos?mios=0') ?>" class="btn btn-sm <?= $verMios ? 'btn-outline-primary' : 'btn-primary' ?>">Todos (<?= (int) $countTodos ?>)</a>
            <a href="<?= base_url('compromisos?mios=1') ?>" class="btn btn-sm <?= $verMios ? 'btn-primary' : 'btn-outline-primary' ?>">Míos (<?= (int) $countMios ?>)</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="<?= base_url('compromisos?mios=' . $m) ?>" class="badge bg-secondary chip <?= $estadoActivo === '' ? 'activo' : '' ?>">Total: <?= (int) $resumen['total'] ?></a>
            <a href="<?= base_url('compromisos?mios=' . $m . '&estado=pendiente') ?>" class="badge bg-warning text-dark chip <?= $estadoActivo === 'pendiente' ? 'activo' : '' ?>">Pendientes: <?= (int) $resumen['pendiente'] ?></a>
            <a href="<?= base_url('compromisos?mios=' . $m . '&estado=en_progreso') ?>" class="badge bg-primary chip <?= $estadoActivo === 'en_progreso' ? 'activo' : '' ?>">En progreso: <?= (int) $resumen['en_progreso'] ?></a>
            <a href="<?= base_url('compromisos?mios=' . $m . '&estado=cumplido') ?>" class="badge bg-success chip <?= $estadoActivo === 'cumplido' ? 'activo' : '' ?>">Cumplidos: <?= (int) $resumen['cumplido'] ?></a>
            <a href="<?= base_url('compromisos?mios=' . $m . '&estado=vencido') ?>" class="badge bg-danger chip <?= $estadoActivo === 'vencido' ? 'activo' : '' ?>">Vencidos: <?= (int) $resumen['vencido'] ?></a>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tablaCompromisos" class="table align-middle table-striped" style="width:100%;">
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
                        <tr class="filtros">
                            <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <td data-order="<?= esc($estado) ?>"><span class="badge <?= $badge ?>"><?= esc(str_replace('_', ' ', $estado)) ?></span></td>
                                <td data-order="<?= esc($c['avance']) ?>" style="min-width:130px;">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(function () {
        var table = $('#tablaCompromisos').DataTable({
            orderCellsTop: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[3, 'asc']],
            columnDefs: [{ targets: [5, 6], orderable: false, searchable: false }],
            language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' }
        });
        $('#tablaCompromisos thead tr.filtros th').each(function (i) {
            var input = $('input', this);
            if (!input.length) return;
            input.on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table.column(i).search(this.value).draw();
                }
            });
        });
    });
    </script>
    <?= $this->include("partials/home_fab") ?>
    <?= $this->include("partials/notif_bell") ?>
</body>
</html>
