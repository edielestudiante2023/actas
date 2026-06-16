<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $cliente === null ? 'Nuevo cliente' : 'Editar cliente' ?> · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('clientes') ?>" class="btn btn-sm btn-outline-light">Clientes</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-3">
            <h4 class="mb-1"><?= $cliente === null ? 'Nuevo cliente' : 'Editar cliente' ?></h4>
            <p class="text-muted mb-0">El logo se usará en el portal del cliente y en los encabezados de actas.</p>
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

        <form action="<?= esc($action) ?>" method="<?= esc($method) ?>" enctype="multipart/form-data" class="card">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <label class="form-label">Nombre del cliente</label>
                        <input type="text" name="nombre" class="form-control" value="<?= esc(old('nombre', $cliente['nombre'] ?? '')) ?>" maxlength="200" required autofocus>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">NIT</label>
                        <input type="text" name="nit" class="form-control" value="<?= esc(old('nit', $cliente['nit'] ?? '')) ?>" maxlength="30">
                    </div>
                    <div class="col-12 col-lg-8">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= esc(old('direccion', $cliente['direccion'] ?? '')) ?>" maxlength="200">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" value="<?= esc(old('ciudad', $cliente['ciudad'] ?? '')) ?>" maxlength="100">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= esc(old('telefono', $cliente['telefono'] ?? '')) ?>" maxlength="20">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= esc(old('email', $cliente['email'] ?? '')) ?>" maxlength="150">
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Estado</label>
                        <?php $estado = old('estado', $cliente['estado'] ?? 'activo'); ?>
                        <select name="estado" class="form-select" required>
                            <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">PNG, JPG, JPEG o WEBP. Máximo 2 MB.</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <?php if (! empty($cliente['logo'])): ?>
                            <label class="form-label">Logo actual</label>
                            <div>
                                <img src="<?= base_url('clientes/' . $cliente['id_cliente'] . '/logo') ?>" alt="<?= esc($cliente['nombre']) ?>" style="max-width:180px;max-height:90px;object-fit:contain;border:1px solid #dee2e6;border-radius:8px;background:#fff;padding:8px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="<?= base_url('clientes') ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
