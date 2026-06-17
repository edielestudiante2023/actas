<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Consejo · <?= esc($cliente['nombre']) ?><?= $this->endSection() ?>

<?= $this->section('navActions') ?>
    <a href="<?= base_url('clientes') ?>" class="btn btn-sm btn-outline-light">Clientes</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="mb-3">
        <h4 class="mb-1">Consejo de administración</h4>
        <p class="text-muted mb-0"><?= esc($cliente['nombre']) ?></p>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Agregar miembro</div>
        <div class="card-body">
            <form action="<?= base_url('clientes/' . $cliente['id_cliente'] . '/consejo') ?>" method="post" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-12 col-lg-3">
                    <label class="form-label">Cargo</label>
                    <?php $cargo = old('cargo', 'consejero'); ?>
                    <select name="cargo" class="form-select" required>
                        <option value="presidente_consejo" <?= $cargo === 'presidente_consejo' ? 'selected' : '' ?>>Presidente del Consejo</option>
                        <option value="consejero" <?= $cargo === 'consejero' ? 'selected' : '' ?>>Consejero</option>
                    </select>
                </div>
                <div class="col-12 col-lg-5">
                    <label class="form-label">Usuario</label>
                    <select name="id_usuario" class="form-select" required>
                        <option value="">Selecciona usuario</option>
                        <?php if ($presidentesDisponibles !== []): ?>
                            <optgroup label="Usuarios con rol Presidente">
                                <?php foreach ($presidentesDisponibles as $usuario): ?>
                                    <option value="<?= esc($usuario['id_usuario']) ?>" <?= (string) old('id_usuario') === (string) $usuario['id_usuario'] ? 'selected' : '' ?>>
                                        <?= esc($usuario['nombre_completo']) ?> · <?= esc($usuario['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if ($consejerosDisponibles !== []): ?>
                            <optgroup label="Usuarios con rol Consejero">
                                <?php foreach ($consejerosDisponibles as $usuario): ?>
                                    <option value="<?= esc($usuario['id_usuario']) ?>" <?= (string) old('id_usuario') === (string) $usuario['id_usuario'] ? 'selected' : '' ?>>
                                        <?= esc($usuario['nombre_completo']) ?> · <?= esc($usuario['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">Primero asigna el rol al usuario en Administración de usuarios.</div>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label">Inmueble que representa</label>
                    <input type="text" name="inmueble" class="form-control" value="<?= esc(old('inmueble')) ?>" maxlength="120" placeholder="Ej. Torre A - Apto 502" required>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label">Coeficiente (%)</label>
                    <input type="text" name="coeficiente" class="form-control" value="<?= esc(old('coeficiente')) ?>" placeholder="Opcional">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label">Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= esc(old('fecha_inicio', date('Y-m-d'))) ?>">
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">Miembros</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Cargo</th>
                        <th>Inmueble</th>
                        <th>Periodo</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($miembros === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay miembros registrados.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($miembros as $miembro): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= esc($miembro['nombre_completo']) ?></div>
                                <div class="small text-muted"><?= esc($miembro['email']) ?></div>
                            </td>
                            <td>
                                <?= $miembro['cargo'] === 'presidente_consejo' ? 'Presidente del Consejo' : 'Consejero' ?>
                            </td>
                            <td>
                                <?= esc($miembro['inmueble'] ?? '—') ?>
                                <?php if (! empty($miembro['coeficiente'])): ?><div class="small text-muted"><?= esc($miembro['coeficiente']) ?>%</div><?php endif; ?>
                            </td>
                            <td>
                                <div><?= esc($miembro['fecha_inicio'] ?? '') ?></div>
                                <div class="small text-muted"><?= $miembro['fecha_fin'] ? 'Hasta ' . esc($miembro['fecha_fin']) : 'Vigente' ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $miembro['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= esc($miembro['estado']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="<?= base_url('clientes/' . $cliente['id_cliente'] . '/consejo/' . $miembro['id'] . '/estado') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="estado" value="<?= $miembro['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <?= $miembro['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
