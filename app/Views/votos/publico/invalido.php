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
                <div style="font-size:3rem;line-height:1;"><?= ! empty($cerrada) ? '🔒' : '⚠️' ?></div>
                <h4 class="mt-3"><?= ! empty($cerrada) ? 'Votación cerrada' : 'Enlace no válido' ?></h4>
                <p class="text-muted">
                    <?php if (! empty($cerrada)): ?>
                        Esta votación ya fue cerrada. Ya no se pueden registrar votos.
                    <?php else: ?>
                        El enlace de voto no existe o expiró. Solicita uno nuevo al administrador del acta.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
