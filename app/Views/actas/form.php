<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isNew ? 'Nueva acta' : 'Editar acta' ?> · <?= esc($cliente['nombre'] ?? 'Cliente') ?></title>
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
        <div class="mb-3">
            <h4 class="mb-1"><?= $isNew ? 'Nueva acta borrador' : 'Editar acta borrador' ?></h4>
            <p class="text-muted mb-0">
                <?= esc($cliente['nombre'] ?? '') ?>
                <?php if (! $isNew): ?>
                    · <?= esc($acta['numero']) ?>
                <?php endif; ?>
            </p>
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
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" value="<?= esc(old('titulo', $acta['titulo'] ?? 'Reunión ordinaria del consejo de administración')) ?>" maxlength="200">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= esc(old('fecha', $acta['fecha'] ?? date('Y-m-d'))) ?>" required>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label">Hora inicio</label>
                        <input type="time" name="hora_inicio" class="form-control" value="<?= esc(old('hora_inicio', $acta['hora_inicio'] ?? '')) ?>">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label">Hora fin</label>
                        <input type="time" name="hora_fin" class="form-control" value="<?= esc(old('hora_fin', $acta['hora_fin'] ?? '')) ?>">
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="form-label">Modalidad</label>
                        <?php $modalidad = old('modalidad', $acta['modalidad'] ?? 'presencial'); ?>
                        <select name="modalidad" class="form-select" required>
                            <option value="presencial" <?= $modalidad === 'presencial' ? 'selected' : '' ?>>Presencial</option>
                            <option value="virtual" <?= $modalidad === 'virtual' ? 'selected' : '' ?>>Virtual</option>
                            <option value="mixta" <?= $modalidad === 'mixta' ? 'selected' : '' ?>>Mixta</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="form-label">Lugar</label>
                        <input type="text" name="lugar" class="form-control" value="<?= esc(old('lugar', $acta['lugar'] ?? '')) ?>" maxlength="200">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Objeto</label>
                        <textarea name="objeto" class="form-control" rows="2"><?= esc(old('objeto', $acta['objeto'] ?? '')) ?></textarea>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label">Orden del día</label>
                        <textarea name="orden_dia" class="form-control" rows="10"><?= esc(old('orden_dia', $acta['orden_dia'] ?? "1. Verificación del quórum\n2. Lectura y aprobación del orden del día\n3. Desarrollo de la reunión\n4. Compromisos\n5. Cierre")) ?></textarea>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label">Desarrollo</label>
                        <textarea name="desarrollo" class="form-control" rows="10"><?= esc(old('desarrollo', $acta['desarrollo'] ?? '')) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"><?= esc(old('observaciones', $acta['observaciones'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="<?= base_url('actas') ?>" class="btn btn-outline-secondary">Cancelar</a>
                <?php if (! $isNew): ?>
                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes') ?>" class="btn btn-outline-success">Asistentes</a>
                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" class="btn btn-outline-warning">Compromisos</a>
                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones') ?>" class="btn btn-outline-info">Votaciones</a>
                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/anexos') ?>" class="btn btn-outline-secondary">Anexos</a>
                    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/pdf') ?>" class="btn btn-outline-dark" target="_blank" rel="noopener">PDF</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Guardar borrador</button>
            </div>
        </form>
    </main>
</body>
</html>
