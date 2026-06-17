<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Asistentes · <?= esc($acta['numero']) ?><?= $this->endSection() ?>

<?= $this->section('navActions') ?>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/compromisos') ?>" class="btn btn-sm btn-outline-light">Compromisos</a>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/votaciones') ?>" class="btn btn-sm btn-outline-light">Votaciones</a>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/anexos') ?>" class="btn btn-sm btn-outline-light">Anexos</a>
    <a href="<?= base_url('actas/' . $acta['id_acta'] . '/firmas') ?>" class="btn btn-sm btn-outline-light">Firmas</a>
    <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
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

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Miembros consejo</div>
                <div class="fs-4 fw-semibold"><?= esc($quorum['total']) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Presentes</div>
                <div class="fs-4 fw-semibold"><?= esc($quorum['presentes']) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Requeridos</div>
                <div class="fs-4 fw-semibold"><?= esc($quorum['requerido']) ?></div>
            </div></div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Quórum</div>
                <div class="fs-5 fw-semibold <?= $quorum['cumple'] ? 'text-success' : 'text-danger' ?>">
                    <?= $quorum['cumple'] ? 'Cumple' : 'No cumple' ?>
                </div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Asistente</th>
                        <th>Cargo</th>
                        <th>Inmueble</th>
                        <th>Asistencia</th>
                        <th>Firma</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($asistentes === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Importa el consejo del cliente para cargar asistentes.</td>
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
                            <td><?= esc($asistente['inmueble'] ?? '—') ?></td>
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
<?= $this->endSection() ?>
