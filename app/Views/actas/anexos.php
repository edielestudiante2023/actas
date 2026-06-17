<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Anexos · <?= esc($acta['numero']) ?><?= $this->endSection() ?>

<?= $this->section('navActions') ?>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/asistentes') ?>" class="btn btn-sm btn-outline-light">Asistentes</a>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" class="btn btn-sm btn-outline-light">Compromisos</a>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones') ?>" class="btn btn-sm btn-outline-light">Votaciones</a>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/firmas') ?>" class="btn btn-sm btn-outline-light">Firmas</a>
    <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <?php
    $fmtSize = static function (?int $b): string {
        $b = (int) $b;
        if ($b <= 0) return '—';
        if ($b < 1024) return $b . ' B';
        if ($b < 1048576) return round($b / 1024, 1) . ' KB';
        return round($b / 1048576, 1) . ' MB';
    };
    ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
        <div>
            <h4 class="mb-1">Anexos del acta</h4>
            <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?> · <?= esc($acta['numero']) ?></p>
        </div>
        <a href="<?= base_url('actas/' . $acta['id_acta'] . '/editar') ?>" class="btn btn-outline-primary">Editar acta</a>
    </div>

    <?php if ($editable): ?>
        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/anexos') ?>" method="post" enctype="multipart/form-data" class="card mb-3">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <label class="form-label">Nombre / descripción (opcional)</label>
                        <input type="text" name="nombre" class="form-control" value="<?= esc(old('nombre')) ?>" maxlength="200" placeholder="Si lo dejas vacío, se usa el nombre del archivo">
                    </div>
                    <div class="col-12 col-lg-5">
                        <label class="form-label">Archivo</label>
                        <input type="file" name="archivo" class="form-control" required>
                    </div>
                    <div class="col-12 col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">Adjuntar</button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Tipos: <?= esc(str_replace(',', ', ', $ext)) ?> · Máx <?= (int) round($maxKb / 1024) ?> MB.</small>
            </div>
        </form>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($anexos === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay anexos adjuntos a esta acta.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($anexos as $anexo): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($anexo['nombre']) ?></td>
                            <td class="small text-muted"><?= esc($anexo['mime'] ?? '—') ?></td>
                            <td><?= esc($fmtSize($anexo['tamano'] ?? 0)) ?></td>
                            <td><?= esc(substr((string) $anexo['created_at'], 0, 16)) ?></td>
                            <td class="text-end">
                                <a href="<?= base_url('actas/' . $acta['id_acta'] . '/anexos/' . $anexo['id_anexo'] . '/descargar') ?>" class="btn btn-sm btn-outline-dark">Descargar</a>
                                <?php if ($editable): ?>
                                    <form action="<?= base_url('actas/' . $acta['id_acta'] . '/anexos/' . $anexo['id_anexo'] . '/eliminar') ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este anexo?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
