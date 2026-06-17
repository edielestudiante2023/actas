<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Clientes · Actas<?= $this->endSection() ?>

<?= $this->section('navActions') ?>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-light">Panel</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
        <div>
            <h4 class="mb-1">Clientes</h4>
            <p class="text-muted mb-0">Propiedades horizontales administradas en la plataforma.</p>
        </div>
        <a href="<?= base_url('clientes/nuevo') ?>" class="btn btn-primary">Nuevo cliente</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>NIT</th>
                        <th>Ciudad</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clientes === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay clientes creados.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if (! empty($cliente['logo'])): ?>
                                        <img src="<?= base_url('clientes/' . $cliente['id_cliente'] . '/logo') ?>" alt="<?= esc($cliente['nombre']) ?>" style="width:48px;height:48px;object-fit:contain;border:1px solid #dee2e6;border-radius:8px;background:#fff;">
                                    <?php else: ?>
                                        <div class="bg-white border rounded d-flex align-items-center justify-content-center text-muted fw-semibold" style="width:48px;height:48px;">
                                            <?= esc(mb_strtoupper(mb_substr($cliente['nombre'], 0, 1))) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold"><?= esc($cliente['nombre']) ?></div>
                                        <div class="small text-muted"><?= esc($cliente['direccion'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($cliente['nit'] ?? '') ?></td>
                            <td><?= esc($cliente['ciudad'] ?? '') ?></td>
                            <td>
                                <div><?= esc($cliente['email'] ?? '') ?></div>
                                <div class="small text-muted"><?= esc($cliente['telefono'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $cliente['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= esc($cliente['estado']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= base_url('clientes/' . $cliente['id_cliente'] . '/consejo') ?>" class="btn btn-sm btn-outline-success">Consejo</a>
                                    <a href="<?= base_url('clientes/' . $cliente['id_cliente'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="<?= base_url('clientes/' . $cliente['id_cliente'] . '/estado') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <?= $cliente['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
