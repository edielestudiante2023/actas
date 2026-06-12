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
            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones') ?>" method="post" class="card mb-3">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <label class="form-label">Tema / decisión a votar</label>
                            <input type="text" name="titulo" class="form-control" value="<?= esc(old('titulo')) ?>" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción (opcional)</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?= esc(old('descripcion')) ?></textarea>
                        </div>
                        <div class="col-4 col-lg-3">
                            <label class="form-label">A favor</label>
                            <input type="number" name="votos_favor" class="form-control" value="<?= esc(old('votos_favor', '0')) ?>" min="0" required>
                        </div>
                        <div class="col-4 col-lg-3">
                            <label class="form-label">En contra</label>
                            <input type="number" name="votos_contra" class="form-control" value="<?= esc(old('votos_contra', '0')) ?>" min="0" required>
                        </div>
                        <div class="col-4 col-lg-3">
                            <label class="form-label">Abstenciones</label>
                            <input type="number" name="abstenciones" class="form-control" value="<?= esc(old('abstenciones', '0')) ?>" min="0" required>
                        </div>
                        <div class="col-12 col-lg-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Registrar votación</button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">El resultado se calcula por mayoría simple. En empate queda <em>pendiente</em> (desempate manual).</small>
                </div>
            </form>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tema</th>
                            <th class="text-center">A favor</th>
                            <th class="text-center">Contra</th>
                            <th class="text-center">Abst.</th>
                            <th>Resultado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($votaciones === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay votaciones registradas para esta acta.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($votaciones as $votacion): ?>
                            <?php
                                $resultado = (string) $votacion['resultado'];
                                $badge = match ($resultado) {
                                    'aprobada'  => 'bg-success',
                                    'rechazada' => 'bg-danger',
                                    default     => 'bg-warning text-dark',
                                };
                            ?>
                            <tr>
                                <td style="min-width: 260px;">
                                    <div class="fw-semibold"><?= esc($votacion['titulo']) ?></div>
                                    <?php if (! empty($votacion['descripcion'])): ?>
                                        <div class="small text-muted"><?= nl2br(esc($votacion['descripcion'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= esc($votacion['votos_favor']) ?></td>
                                <td class="text-center"><?= esc($votacion['votos_contra']) ?></td>
                                <td class="text-center"><?= esc($votacion['abstenciones']) ?></td>
                                <td><span class="badge <?= $badge ?>"><?= esc(str_replace('_', ' ', $resultado)) ?></span></td>
                                <td class="text-end">
                                    <?php if ($editable): ?>
                                        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones/' . $votacion['id_votacion']) ?>" method="post" class="d-inline-flex flex-wrap gap-1 align-items-center justify-content-end">
                                            <?= csrf_field() ?>
                                            <input type="number" name="votos_favor" class="form-control form-control-sm" value="<?= esc($votacion['votos_favor']) ?>" min="0" style="width: 64px;" title="A favor">
                                            <input type="number" name="votos_contra" class="form-control form-control-sm" value="<?= esc($votacion['votos_contra']) ?>" min="0" style="width: 64px;" title="En contra">
                                            <input type="number" name="abstenciones" class="form-control form-control-sm" value="<?= esc($votacion['abstenciones']) ?>" min="0" style="width: 64px;" title="Abstenciones">
                                            <select name="resultado" class="form-select form-select-sm" style="width:auto;" title="Resultado (vacío = automático)">
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
</body>
</html>
