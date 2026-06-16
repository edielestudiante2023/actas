<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votaciones · <?= esc($acta['numero']) ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes') ?>" class="btn btn-sm btn-outline-light">Asistentes</a>
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" class="btn btn-sm btn-outline-light">Compromisos</a>
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/anexos') ?>" class="btn btn-sm btn-outline-light">Anexos</a>
            <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
            <div>
                <h4 class="mb-1">Votaciones y decisiones</h4>
                <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?> · <?= esc($acta['numero']) ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones') ?>" class="btn btn-outline-secondary">Actualizar conteo</a>
                <a href="<?= base_url('actas/' . $acta['id_acta'] . '/editar') ?>" class="btn btn-outline-primary">Editar acta</a>
            </div>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('errors')): ?>
            <div class="alert alert-danger py-2"><ul class="mb-0"><?php foreach (session('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <?php
        $abiertas = array_values(array_filter($votaciones, static fn ($v) => $v['estado'] === 'abierta' && $v['modo'] === 'digital'));
        $cerradas = array_values(array_filter($votaciones, static fn ($v) => $v['estado'] !== 'abierta'));
        ?>

        <?php if ($editable): ?>
            <div class="card mb-3 border-primary">
                <div class="card-header bg-primary text-white fw-semibold">Abrir votación digital (en vivo)</div>
                <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones/abrir') ?>" method="post" class="card-body">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <label class="form-label">Tema / decisión a votar</label>
                            <input type="text" name="titulo" class="form-control" maxlength="200" required>
                        </div>
                        <div class="col-12 col-lg-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Abrir votación</button>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción (opcional)</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Los consejeros presentes votan desde su sesión. El resultado se calcula al cerrar (mayoría simple).</small>
                </form>
            </div>
        <?php endif; ?>

        <!-- Votaciones abiertas (en vivo) -->
        <?php foreach ($abiertas as $v): ?>
            <?php $c = $v['_conteo'] ?? ['favor' => 0, 'contra' => 0, 'abstencion' => 0, 'total' => 0]; $miVoto = $v['_mi_voto'] ?? null; ?>
            <div class="card mb-3 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <span class="badge bg-warning text-dark mb-1">Votación abierta</span>
                            <h5 class="mb-1"><?= esc($v['titulo']) ?></h5>
                            <?php if (! empty($v['descripcion'])): ?><div class="text-muted small"><?= nl2br(esc($v['descripcion'])) ?></div><?php endif; ?>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Conteo en vivo</div>
                            <div><span class="badge bg-success">A favor: <?= (int) $c['favor'] ?></span>
                                 <span class="badge bg-danger">Contra: <?= (int) $c['contra'] ?></span>
                                 <span class="badge bg-secondary">Abst.: <?= (int) $c['abstencion'] ?></span>
                                 <span class="badge bg-dark">Total: <?= (int) $c['total'] ?></span></div>
                        </div>
                    </div>

                    <hr>

                    <?php if ($miAsistente !== null && ($miAsistente['asistencia'] ?? '') === 'asiste'): ?>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="text-muted small me-2">Tu voto<?= $miVoto ? ' (actual: ' . esc($miVoto) . ')' : '' ?>:</span>
                            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones/' . $v['id_votacion'] . '/votar') ?>" method="post" class="d-flex gap-2">
                                <?= csrf_field() ?>
                                <button name="voto" value="favor" class="btn btn-<?= $miVoto === 'favor' ? '' : 'outline-' ?>success">A favor</button>
                                <button name="voto" value="contra" class="btn btn-<?= $miVoto === 'contra' ? '' : 'outline-' ?>danger">En contra</button>
                                <button name="voto" value="abstencion" class="btn btn-<?= $miVoto === 'abstencion' ? '' : 'outline-' ?>secondary">Abstención</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small">No estás registrado como asistente presente de esta acta, por lo que no puedes votar.</div>
                    <?php endif; ?>

                    <?php if ($editable): ?>
                        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones/' . $v['id_votacion'] . '/cerrar') ?>" method="post" class="mt-3" onsubmit="return confirm('¿Cerrar la votación y calcular el resultado?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-dark btn-sm">Cerrar votación</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($editable): ?>
            <details class="mb-3">
                <summary class="text-muted">Registrar votación manual (sin votación en vivo)</summary>
                <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones') ?>" method="post" class="card mt-2">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <label class="form-label">Tema / decisión</label>
                                <input type="text" name="titulo" class="form-control" value="<?= esc(old('titulo')) ?>" maxlength="200" required>
                            </div>
                            <div class="col-12"><label class="form-label">Descripción (opcional)</label>
                                <textarea name="descripcion" class="form-control" rows="2"><?= esc(old('descripcion')) ?></textarea></div>
                            <div class="col-4 col-lg-3"><label class="form-label">A favor</label>
                                <input type="number" name="votos_favor" class="form-control" value="<?= esc(old('votos_favor', '0')) ?>" min="0" required></div>
                            <div class="col-4 col-lg-3"><label class="form-label">En contra</label>
                                <input type="number" name="votos_contra" class="form-control" value="<?= esc(old('votos_contra', '0')) ?>" min="0" required></div>
                            <div class="col-4 col-lg-3"><label class="form-label">Abstenciones</label>
                                <input type="number" name="abstenciones" class="form-control" value="<?= esc(old('abstenciones', '0')) ?>" min="0" required></div>
                            <div class="col-12 col-lg-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary w-100">Registrar manual</button></div>
                        </div>
                    </div>
                </form>
            </details>
        <?php endif; ?>

        <!-- Historial -->
        <div class="card">
            <div class="card-header fw-semibold">Historial de votaciones</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tema</th><th class="text-center">A favor</th><th class="text-center">Contra</th>
                            <th class="text-center">Abst.</th><th>Resultado</th><th>Modo</th><th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cerradas === []): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay votaciones cerradas todavía.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($cerradas as $votacion): ?>
                            <?php
                                $resultado = (string) $votacion['resultado'];
                                $badge = match ($resultado) { 'aprobada' => 'bg-success', 'rechazada' => 'bg-danger', default => 'bg-warning text-dark' };
                            ?>
                            <tr>
                                <td style="min-width: 220px;">
                                    <div class="fw-semibold"><?= esc($votacion['titulo']) ?></div>
                                    <?php if (! empty($votacion['descripcion'])): ?><div class="small text-muted"><?= nl2br(esc($votacion['descripcion'])) ?></div><?php endif; ?>
                                </td>
                                <td class="text-center"><?= esc($votacion['votos_favor']) ?></td>
                                <td class="text-center"><?= esc($votacion['votos_contra']) ?></td>
                                <td class="text-center"><?= esc($votacion['abstenciones']) ?></td>
                                <td><span class="badge <?= $badge ?>"><?= esc($resultado) ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($votacion['modo']) ?></span></td>
                                <td class="text-end">
                                    <?php if ($editable && $votacion['modo'] === 'manual'): ?>
                                        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones/' . $votacion['id_votacion']) ?>" method="post" class="d-inline-flex flex-wrap gap-1 align-items-center justify-content-end">
                                            <?= csrf_field() ?>
                                            <input type="number" name="votos_favor" class="form-control form-control-sm" value="<?= esc($votacion['votos_favor']) ?>" min="0" style="width: 60px;" title="A favor">
                                            <input type="number" name="votos_contra" class="form-control form-control-sm" value="<?= esc($votacion['votos_contra']) ?>" min="0" style="width: 60px;" title="En contra">
                                            <input type="number" name="abstenciones" class="form-control form-control-sm" value="<?= esc($votacion['abstenciones']) ?>" min="0" style="width: 60px;" title="Abstenciones">
                                            <select name="resultado" class="form-select form-select-sm" style="width:auto;" title="Resultado">
                                                <option value="">Auto</option>
                                                <?php foreach ($resultados as $opcion): ?>
                                                    <option value="<?= esc($opcion) ?>" <?= $resultado === $opcion ? 'selected' : '' ?>><?= esc($opcion) ?></option>
                                                <?php endforeach; ?>
                                            </select>
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
    <?= $this->include("partials/home_fab") ?>
    <?= $this->include("partials/notif_bell") ?>
</body>
</html>
