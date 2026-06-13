<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Firma de acta</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:22px 24px;background:#0d6efd;color:#ffffff;">
                            <div style="font-size:18px;font-weight:bold;">Firma pendiente de acta</div>
                            <div style="font-size:13px;margin-top:4px;"><?= esc($cliente['nombre'] ?? 'Cliente') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px;">Hola <?= esc($nombre ?? '') ?>,</p>
                            <p style="margin:0 0 14px;line-height:1.5;">
                                Tienes pendiente la firma del acta
                                <strong><?= esc($acta['numero'] ?? '') ?></strong>
                                <?php if (! empty($acta['titulo'])): ?>
                                    — <?= esc($acta['titulo']) ?>
                                <?php endif; ?>.
                            </p>
                            <?php if (! empty($esPrueba)): ?>
                                <p style="margin:0 0 14px;line-height:1.5;">Este es un correo de prueba de configuración.</p>
                            <?php endif; ?>
                            <p style="margin:22px 0;">
                                <a href="<?= esc($firmaUrl) ?>" style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:6px;font-weight:bold;">Abrir enlace de firma</a>
                            </p>
                            <p style="margin:0 0 14px;line-height:1.5;font-size:13px;color:#4b5563;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                <span style="word-break:break-all;"><?= esc($firmaUrl) ?></span>
                            </p>
                            <?php if (! empty($expira)): ?>
                                <p style="margin:0;line-height:1.5;font-size:13px;color:#4b5563;">El enlace vence el <?= esc($expira) ?>.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f9fafb;color:#6b7280;font-size:12px;">
                            Enviado por la plataforma Actas.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
