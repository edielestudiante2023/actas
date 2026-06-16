<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0;background:#f1f3f5;font-family:Arial,Helvetica,sans-serif;color:#1f2430;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:10px;overflow:hidden;">
                <tr><td style="background:#0d6efd;color:#fff;padding:22px 28px;">
                    <div style="font-size:18px;font-weight:bold;">Votación pendiente</div>
                    <div style="font-size:13px;opacity:.9;"><?= esc($cliente['nombre'] ?? 'Actas') ?></div>
                </td></tr>
                <tr><td style="padding:24px 28px;">
                    <p style="margin:0 0 12px;">Hola <?= esc($nombre) ?>,</p>
                    <p style="margin:0 0 12px;line-height:1.5;">Hay una votación abierta en el acta <strong><?= esc($acta['numero'] ?? $acta['id_acta']) ?></strong>:</p>
                    <p style="margin:0 0 16px;padding:12px 14px;background:#f8f9fb;border-radius:8px;font-weight:bold;"><?= esc($votacion['titulo']) ?></p>
                    <p style="margin:0 0 18px;line-height:1.5;">Ingresa con tu enlace personal y registra tu voto (a favor, en contra o abstención):</p>
                    <p style="margin:0 0 18px;">
                        <a href="<?= esc($votoUrl) ?>" style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;font-weight:bold;">Votar ahora</a>
                    </p>
                    <p style="margin:0 0 6px;font-size:13px;color:#4b5563;">Si el botón no funciona, copia y pega este enlace:<br>
                        <a href="<?= esc($votoUrl) ?>" style="color:#0d6efd;word-break:break-all;"><?= esc($votoUrl) ?></a>
                    </p>
                </td></tr>
                <tr><td style="padding:14px 28px;border-top:1px solid #eef1f5;font-size:12px;color:#6b7280;">Enviado por la plataforma Actas.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
