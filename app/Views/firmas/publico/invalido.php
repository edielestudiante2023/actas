<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace no válido</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?= $this->include("partials/pwa_head") ?>
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 520px;">
        <div class="card shadow-sm text-center">
            <div class="card-body p-5">
                <div style="font-size:3rem; line-height:1;"><?= ! empty($yaFirmado) ? '✔️' : '⚠️' ?></div>
                <h4 class="mt-3"><?= ! empty($yaFirmado) ? 'Acta ya firmada' : 'Enlace no válido' ?></h4>
                <p class="text-muted">
                    <?php if (! empty($yaFirmado)): ?>
                        Esta firma ya fue registrada anteriormente. No es necesario firmar de nuevo.
                    <?php else: ?>
                        El enlace de firma no existe, ya fue usado o expiró. Solicita uno nuevo al administrador.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
