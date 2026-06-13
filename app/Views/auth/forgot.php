<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f3f5; min-height: 100vh; display: flex; align-items: center; }
        .auth-card { max-width: 440px; width: 100%; margin: auto; border: none; border-radius: 16px; box-shadow: 0 12px 35px rgba(0,0,0,.14); }
    </style>
</head>
<body>
    <main class="card auth-card p-4 m-3">
        <h4 class="mb-1">Recuperar contraseña</h4>
        <p class="text-muted mb-3">Te enviaremos un enlace para crear una nueva contraseña.</p>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('password/forgot') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar enlace</button>
        </form>

        <div class="text-center mt-3">
            <a href="<?= base_url('login') ?>" class="small text-decoration-none">Volver al ingreso</a>
        </div>
    </main>
</body>
</html>
