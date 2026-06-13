<?php
$fmtFecha = static function (?string $v): string {
    if (empty($v)) {
        return '—';
    }
    $t = strtotime($v);

    return $t ? date('d/m/Y', $t) : $v;
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar acta · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 760px;">
        <div class="card shadow-sm mb-3">
            <div class="card-body p-4">
                <h4 class="mb-1">Verificar acta</h4>
                <p class="text-muted mb-4">Consulta el código de verificación de un acta firmada.</p>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
                <?php endif; ?>
                <?php if (! empty($error)): ?>
                    <div class="alert alert-warning py-2"><?= esc($error) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('verificar') ?>" method="post" class="row g-2">
                    <?= csrf_field() ?>
                    <div class="col-12 col-md">
                        <input type="text" name="codigo" class="form-control form-control-lg text-uppercase" value="<?= esc($codigo ?? '') ?>" placeholder="Código de verificación" maxlength="80" required>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Verificar</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (! empty($acta)): ?>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Acta válida</h5>
                            <div class="text-muted"><?= esc($cliente['nombre'] ?? 'Cliente') ?></div>
                        </div>
                        <span class="badge bg-success align-self-md-start">Firmada</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <div class="text-muted small">Número</div>
                            <div class="fw-semibold"><?= esc($acta['numero'] ?? $acta['id_acta']) ?></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-muted small">Fecha</div>
                            <div class="fw-semibold"><?= esc($fmtFecha($acta['fecha'] ?? null)) ?></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-muted small">Código</div>
                            <div class="fw-semibold"><?= esc($acta['codigo_verificacion']) ?></div>
                        </div>
                    </div>

                    <?php if (! empty($acta['titulo'])): ?>
                        <div class="mb-3">
                            <div class="text-muted small">Título</div>
                            <div><?= esc($acta['titulo']) ?></div>
                        </div>
                    <?php endif; ?>

                    <h6 class="mt-4">Firmantes</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cargo</th>
                                    <th>Fecha firma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($firmantes)): ?>
                                    <tr><td colspan="3" class="text-muted text-center">Sin firmantes registrados.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($firmantes as $firmante): ?>
                                    <tr>
                                        <td><?= esc($firmante['nombre']) ?></td>
                                        <td><?= esc($firmante['cargo'] ?? '—') ?></td>
                                        <td><?= esc(! empty($firmante['firma_at']) ? substr((string) $firmante['firma_at'], 0, 16) : '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
