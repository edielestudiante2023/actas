<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace no válido · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?= $this->include("partials/pwa_head") ?>
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 520px;">
        <div class="card shadow-sm text-center">
            <div class="card-body p-5">
                <h4>Enlace no válido</h4>
                <p class="text-muted">El enlace de recuperación no existe, expiró o ya fue usado.</p>
                <a href="<?= base_url('password/forgot') ?>" class="btn btn-primary">Solicitar nuevo enlace</a>
            </div>
        </div>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
