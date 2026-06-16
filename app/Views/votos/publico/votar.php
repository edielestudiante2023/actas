<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votar · <?= esc($cliente['nombre'] ?? 'Actas') ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background:#f1f3f5; } </style>
</head>
<body>
    <main class="container py-4" style="max-width: 560px;">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-1">Votación</h4>
                <p class="text-muted mb-3"><?= esc($cliente['nombre'] ?? '') ?> · Acta <?= esc($acta['numero'] ?? '') ?></p>

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><span class="text-muted small">Tema</span><div class="fw-semibold"><?= esc($votacion['titulo']) ?></div>
                        <?php if (! empty($votacion['descripcion'])): ?><div class="small text-muted mt-1"><?= nl2br(esc($votacion['descripcion'])) ?></div><?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Votante</span><strong><?= esc($asistente['nombre'] ?? '') ?></strong></li>
                </ul>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
                <?php endif; ?>
                <?php if ($miVoto !== null): ?>
                    <div class="alert alert-info py-2">Tu voto actual es <strong><?= esc($miVoto) ?></strong>. Puedes cambiarlo mientras la votación esté abierta.</div>
                <?php endif; ?>

                <p class="form-label">Selecciona tu voto:</p>
                <form action="<?= base_url('votar/' . esc($token, 'url')) ?>" method="post" class="d-grid gap-2">
                    <?= csrf_field() ?>
                    <button name="voto" value="favor" class="btn btn-lg btn-<?= $miVoto === 'favor' ? '' : 'outline-' ?>success">A favor</button>
                    <button name="voto" value="contra" class="btn btn-lg btn-<?= $miVoto === 'contra' ? '' : 'outline-' ?>danger">En contra</button>
                    <button name="voto" value="abstencion" class="btn btn-lg btn-<?= $miVoto === 'abstencion' ? '' : 'outline-' ?>secondary">Abstención</button>
                </form>
            </div>
        </div>
        <p class="text-center text-muted small mt-3">Tu voto queda registrado con fecha e IP como evidencia.</p>
    </main>
</body>
</html>
