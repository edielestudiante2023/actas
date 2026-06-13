<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmas · <?= esc($acta['numero']) ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="<?= base_url('dashboard') ?>" class="navbar-brand fw-bold text-decoration-none">Actas</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/anexos') ?>" class="btn btn-sm btn-outline-light">Anexos</a>
            <a href="<?= base_url('actas/' . $acta['id_acta'] . '/pdf') ?>" class="btn btn-sm btn-outline-light" target="_blank" rel="noopener">PDF</a>
            <a href="<?= base_url('actas') ?>" class="btn btn-sm btn-outline-light">Actas</a>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
            <div>
                <h4 class="mb-1">Estado de firmas</h4>
                <p class="text-muted mb-0"><?= esc($cliente['nombre'] ?? '') ?> · <?= esc($acta['numero']) ?> · Estado: <strong><?= esc(str_replace('_', ' ', $acta['estado'])) ?></strong></p>
            </div>
            <div class="text-md-end">
                <span class="badge bg-primary fs-6"><?= (int) $firmados ?> / <?= (int) $total ?> firmadas</span>
            </div>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <?php if (! empty($acta['codigo_verificacion'])): ?>
            <div class="alert alert-success">
                Acta <strong>firmada</strong>. Código de verificación: <strong><?= esc($acta['codigo_verificacion']) ?></strong>
            </div>
        <?php endif; ?>

        <?php if ($editable): ?>
            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/cerrar') ?>" method="post" class="mb-3" onsubmit="return confirm('Al cerrar, el acta pasa a firma y deja de ser editable. ¿Continuar?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Cerrar y enviar a firmas</button>
                <span class="text-muted ms-2 small">Genera los enlaces de firma para los asistentes que asisten y requieren firma.</span>
            </form>
        <?php endif; ?>

        <?php if ($acta['estado'] === 'pendiente_firma'): ?>
            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/firmas/email') ?>" method="post" class="mb-3" onsubmit="return confirm('Se enviará el enlace a todos los firmantes pendientes con correo válido. ¿Continuar?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-primary">Enviar pendientes por email</button>
            </form>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Firmante</th>
                            <th>Estado</th>
                            <th>Firmado</th>
                            <th>Enlace de firma</th>
                            <th>WhatsApp</th>
                            <th>Enlace</th>
                            <th class="text-end">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $firmantes = array_filter($asistentes, static fn ($a) => (int) $a['requiere_firma'] === 1 && $a['asistencia'] === 'asiste');
                        ?>
                        <?php if ($firmantes === []): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay firmantes. Cierra el acta para generar enlaces.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($firmantes as $a): ?>
                            <?php
                                $estado = (string) $a['firma_estado'];
                                $badge = $estado === 'firmada' ? 'bg-success' : ($estado === 'rechazada' ? 'bg-danger' : 'bg-warning text-dark');
                                $tok = $tokens[(int) $a['id_asistente']] ?? null;
                                $tokenVigente = $tok !== null && empty($tok['usado_at']) && (empty($tok['expires_at']) || strtotime((string) $tok['expires_at']) >= time());
                                $url = $tokenVigente ? base_url('firmar/' . $tok['token']) : '';
                                $whatsappTexto = 'Hola ' . ($a['nombre'] ?? '') . ', por favor firma el acta ' . ($acta['numero'] ?? '') . ' de ' . ($cliente['nombre'] ?? 'la copropiedad') . ': ' . $url;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($a['nombre']) ?></div>
                                    <div class="small text-muted"><?= esc($a['cargo'] ?? '') ?><?= ! empty($a['email']) ? ' · ' . esc($a['email']) : '' ?></div>
                                </td>
                                <td><span class="badge <?= $badge ?>"><?= esc(str_replace('_', ' ', $estado)) ?></span></td>
                                <td class="small"><?= esc($a['firma_at'] ? substr((string) $a['firma_at'], 0, 16) : '—') ?></td>
                                <td style="min-width:280px;">
                                    <?php if ($estado === 'firmada'): ?>
                                        <span class="text-success small">✔ Firmado</span>
                                    <?php elseif ($tokenVigente): ?>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" value="<?= esc($url) ?>" readonly onclick="this.select()">
                                            <a class="btn btn-outline-primary" href="<?= esc($url) ?>" target="_blank" rel="noopener">Abrir</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin enlace (cierra el acta)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($estado !== 'firmada' && $tokenVigente): ?>
                                        <a class="btn btn-sm btn-outline-success" href="https://wa.me/?text=<?= rawurlencode($whatsappTexto) ?>" target="_blank" rel="noopener">WhatsApp</a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($estado !== 'firmada' && $acta['estado'] === 'pendiente_firma'): ?>
                                        <div class="d-inline-flex gap-1">
                                            <form action="<?= base_url('actas/' . $acta['id_acta'] . '/firmas/enlace/' . $a['id_asistente'] . '/regenerar') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $tokenVigente ? 'Regenerar' : 'Generar' ?></button>
                                            </form>
                                            <?php if ($tokenVigente): ?>
                                                <form action="<?= base_url('actas/' . $acta['id_acta'] . '/firmas/enlace/' . $a['id_asistente'] . '/cancelar') ?>" method="post" onsubmit="return confirm('Este enlace dejará de funcionar. ¿Continuar?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($estado !== 'firmada' && $tokenVigente && ! empty($a['email']) && $acta['estado'] === 'pendiente_firma'): ?>
                                        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/firmas/email/' . $a['id_asistente']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Enviar</button>
                                        </form>
                                    <?php elseif ($estado !== 'firmada' && ! empty($a['email']) && $acta['estado'] === 'pendiente_firma'): ?>
                                        <form action="<?= base_url('actas/' . $acta['id_acta'] . '/firmas/enlace/' . $a['id_asistente'] . '/regenerar') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="enviar_email" value="1">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Generar y enviar</button>
                                        </form>
                                    <?php elseif (empty($a['email'])): ?>
                                        <span class="text-muted small">Sin correo</span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
