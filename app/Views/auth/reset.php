<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?= $this->include("partials/pwa_head") ?>
    <style>
        body { background: #f1f3f5; min-height: 100vh; display: flex; align-items: center; }
        .auth-card { max-width: 440px; width: 100%; margin: auto; border: none; border-radius: 16px; box-shadow: 0 12px 35px rgba(0,0,0,.14); }
    </style>
</head>
<body>
    <main class="card auth-card p-4 m-3">
        <h4 class="mb-1">Nueva contraseña</h4>
        <p class="text-muted mb-3">Usa al menos 8 caracteres.</p>

        <?php if (session('errors')): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('password/reset/' . esc($token, 'url')) ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required minlength="8" autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirm" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar contraseña</button>
        </form>
    </main>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
