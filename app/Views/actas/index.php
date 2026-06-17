<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Actas · <?= esc($cliente['nombre'] ?? 'Cliente') ?><?= $this->endSection() ?>

<?= $this->section('navActions') ?>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-light">Panel</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
        <div>
            <h4 class="mb-1">Actas</h4>
            <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?></p>
        </div>
        <a href="<?= base_url('actas/nuevo') ?>" class="btn btn-primary">Nueva acta</a>
    </div>

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
<?= $this->endSection() ?>
