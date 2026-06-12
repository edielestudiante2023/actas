<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel · Actas</title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <span class="navbar-brand fw-bold">Actas</span>
        <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
    </nav>

    <div class="container py-4">
        <h4>Hola, <?= esc(session('nombre')) ?> 👋</h4>
        <p class="text-muted mb-3"><?= esc(session('email')) ?></p>

        <div class="card">
            <div class="card-header fw-semibold">Tus roles</div>
            <ul class="list-group list-group-flush">
                <?php foreach ((session('roles_full') ?? []) as $r): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= esc($r['nombre']) ?></span>
                        <span class="badge bg-secondary">
                            <?= $r['id_conjunto'] === null ? 'Plataforma' : 'Conjunto #' . esc($r['id_conjunto']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <p class="text-muted mt-4 small">Fase 1 (autenticación) lista. Próximo: CRUD de conjuntos y usuarios. Ver <code>roadmap.md</code>.</p>
    </div>
</body>
</html>
