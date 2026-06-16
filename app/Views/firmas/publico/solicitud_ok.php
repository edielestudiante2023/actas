<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud recibida</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 560px;">
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="display-5 text-primary mb-3">✓</div>
                <h4>Solicitud recibida</h4>
                <p class="text-muted mb-0">El administrador revisará tu solicitud para marcarte como ausente.</p>
            </div>
        </div>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
