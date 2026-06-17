<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?= $this->renderSection('head') ?>
    <?= $this->include('partials/pwa_head') ?>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2 flex-wrap">
            <?= $this->renderSection('navActions') ?>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <?= $this->include('partials/alerts') ?>
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('partials/home_fab') ?>
    <?= $this->include('partials/notif_bell') ?>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
