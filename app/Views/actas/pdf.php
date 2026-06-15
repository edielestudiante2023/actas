<?php
$fmtFecha = static function (?string $v): string {
    if (empty($v)) {
        return '—';
    }
    $t = strtotime($v);

    return $t ? date('d/m/Y', $t) : esc($v);
};
$estadoTxt = ucfirst(str_replace('_', ' ', (string) $acta['estado']));
$firmaValida = static function (?string $firma): bool {
    return is_string($firma) && preg_match('/^data:image\/(png|jpeg|webp);base64,[A-Za-z0-9+\/=]+$/', $firma) === 1;
};
$fmtFechaHora = static function (?string $v): string {
    if (empty($v)) {
        return '—';
    }
    $t = strtotime($v);

    return $t ? date('d/m/Y H:i', $t) : esc($v);
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 11px; color: #1f2430; margin: 0; }
        h1 { font-size: 16px; margin: 0; color: #16203a; }
        h2 { font-size: 12px; margin: 14px 0 4px; color: #16203a; border-bottom: 1px solid #c9a24b; padding-bottom: 2px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: middle; }
        .logo { width: 64px; height: 64px; }
        .meta { margin-top: 8px; }
        .meta td { padding: 3px 6px; border: 1px solid #e2e6ee; }
        .meta .k { background: #f4f6fa; font-weight: bold; width: 18%; }
        .data { margin-top: 4px; }
        .data th, .data td { border: 1px solid #e2e6ee; padding: 4px 6px; text-align: left; }
        .data th { background: #16203a; color: #fff; font-size: 10px; }
        .center { text-align: center; }
        .badge { padding: 1px 6px; border-radius: 6px; font-size: 9px; color: #fff; }
        .b-ok { background: #1f9d55; }
        .b-no { background: #c0392b; }
        .b-w { background: #c9a24b; }
        .pre { white-space: pre-wrap; }
        .signatures { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 4px; }
        .sig-cell { width: 50%; border: 1px solid #d8dde8; padding: 8px; vertical-align: top; height: 116px; }
        .sig-box { height: 54px; border-bottom: 1px solid #9aa4b2; text-align: center; margin-bottom: 5px; }
        .sig-img { max-width: 210px; max-height: 52px; }
        .sig-placeholder { color: #6b7280; font-size: 10px; padding-top: 18px; }
        .sig-name { font-weight: bold; color: #16203a; }
        .sig-meta { font-size: 9px; color: #6b7280; line-height: 1.35; }
        .foot { margin-top: 18px; border-top: 1px solid #e2e6ee; padding-top: 6px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width:74px;">
                <?php if (! empty($logo)): ?><img src="<?= $logo ?>" class="logo" alt=""><?php endif; ?>
            </td>
            <td>
                <h1>ACTA DE REUNIÓN</h1>
                <div><strong><?= esc($cliente['nombre'] ?? '') ?></strong></div>
                <div class="muted">
                    <?php if (! empty($cliente['nit'])): ?>NIT: <?= esc($cliente['nit']) ?> · <?php endif; ?>
                    Consejo de Administración
                </div>
            </td>
            <td style="text-align:right; width:26%;">
                <div><strong>N.º</strong> <?= esc($acta['numero'] ?? $acta['id_acta']) ?></div>
                <div class="muted"><?= esc($estadoTxt) ?></div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td class="k">Título</td><td colspan="3"><?= esc($acta['titulo'] ?? 'Reunión del consejo de administración') ?></td>
        </tr>
        <tr>
            <td class="k">Fecha</td><td><?= $fmtFecha($acta['fecha']) ?></td>
            <td class="k">Horario</td><td><?= esc($acta['hora_inicio'] ?? '—') ?> a <?= esc($acta['hora_fin'] ?? '—') ?></td>
        </tr>
        <tr>
            <td class="k">Lugar</td><td><?= esc($acta['lugar'] ?? '—') ?></td>
            <td class="k">Modalidad</td><td><?= esc(ucfirst((string) $acta['modalidad'])) ?></td>
        </tr>
        <tr>
            <td class="k">Quórum</td>
            <td colspan="3">
                <?= (int) $quorum['presentes'] ?> de <?= (int) $quorum['total'] ?> miembros (requerido <?= (int) $quorum['requerido'] ?>) —
                <span class="badge <?= $quorum['cumple'] ? 'b-ok' : 'b-no' ?>"><?= $quorum['cumple'] ? 'Hay quórum' : 'Sin quórum' ?></span>
            </td>
        </tr>
    </table>

    <?php if (! empty($acta['objeto'])): ?>
        <h2>Objeto</h2>
        <div class="pre"><?= esc($acta['objeto']) ?></div>
    <?php endif; ?>

    <?php if (! empty($acta['orden_dia'])): ?>
        <h2>Orden del día</h2>
        <div class="pre"><?= esc($acta['orden_dia']) ?></div>
    <?php endif; ?>

    <h2>Asistentes</h2>
    <table class="data">
        <thead>
            <tr><th>Nombre</th><th>Cargo</th><th>Tipo</th><th>Asistencia</th></tr>
        </thead>
        <tbody>
            <?php if ($asistentes === []): ?>
                <tr><td colspan="4" class="center muted">Sin asistentes registrados.</td></tr>
            <?php endif; ?>
            <?php foreach ($asistentes as $a): ?>
                <tr>
                    <td><?= esc($a['nombre']) ?></td>
                    <td><?= esc($a['cargo'] ?? '—') ?></td>
                    <td><?= esc(str_replace('_', ' ', (string) $a['tipo'])) ?></td>
                    <td><?= esc(str_replace('_', ' ', (string) $a['asistencia'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (! empty($acta['desarrollo'])): ?>
        <h2>Desarrollo</h2>
        <div class="pre"><?= esc($acta['desarrollo']) ?></div>
    <?php endif; ?>

    <?php if ($votaciones !== []): ?>
        <h2>Votaciones y decisiones</h2>
        <table class="data">
            <thead>
                <tr><th>Tema</th><th class="center">A favor</th><th class="center">Contra</th><th class="center">Abst.</th><th>Resultado</th></tr>
            </thead>
            <tbody>
                <?php foreach ($votaciones as $v): ?>
                    <?php $rb = $v['resultado'] === 'aprobada' ? 'b-ok' : ($v['resultado'] === 'rechazada' ? 'b-no' : 'b-w'); ?>
                    <tr>
                        <td><?= esc($v['titulo']) ?><?php if (! empty($v['descripcion'])): ?><div class="muted"><?= esc($v['descripcion']) ?></div><?php endif; ?></td>
                        <td class="center"><?= (int) $v['votos_favor'] ?></td>
                        <td class="center"><?= (int) $v['votos_contra'] ?></td>
                        <td class="center"><?= (int) $v['abstenciones'] ?></td>
                        <td><span class="badge <?= $rb ?>"><?= esc(ucfirst((string) $v['resultado'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($compromisos !== []): ?>
        <h2>Compromisos y tareas</h2>
        <table class="data">
            <thead>
                <tr><th>Compromiso</th><th>Responsable</th><th>Vence</th><th>Estado</th><th class="center">Avance</th></tr>
            </thead>
            <tbody>
                <?php foreach ($compromisos as $c): ?>
                    <tr>
                        <td><?= esc($c['descripcion']) ?></td>
                        <td><?= esc($c['responsable_nombre'] ?? $c['usuario_nombre'] ?? '—') ?></td>
                        <td><?= $fmtFecha($c['fecha_vencimiento'] ?? null) ?></td>
                        <td><?= esc(str_replace('_', ' ', (string) $c['estado'])) ?></td>
                        <td class="center"><?= (int) $c['avance'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (! empty($acta['observaciones'])): ?>
        <h2>Observaciones</h2>
        <div class="pre"><?= esc($acta['observaciones']) ?></div>
    <?php endif; ?>

    <?php
    $firmantesPdf = array_values(array_filter($asistentes, static fn ($a) => (int) $a['requiere_firma'] === 1));
    ?>
    <?php if ($firmantesPdf !== []): ?>
        <h2>Firmas</h2>
        <table class="signatures">
            <tbody>
                <?php foreach (array_chunk($firmantesPdf, 2) as $fila): ?>
                    <tr>
                        <?php foreach ($fila as $firmante): ?>
                            <?php
                                $estadoFirma = (string) ($firmante['firma_estado'] ?? 'pendiente');
                                $firmado = $estadoFirma === 'firmada' && $firmaValida($firmante['firma_imagen'] ?? null);
                                $estadoVisible = $estadoFirma === 'firmada' ? 'Firmado' : ucfirst(str_replace('_', ' ', $estadoFirma));
                            ?>
                            <td class="sig-cell">
                                <div class="sig-box">
                                    <?php if ($firmado): ?>
                                        <img src="<?= $firmante['firma_imagen'] ?>" class="sig-img" alt="">
                                    <?php else: ?>
                                        <div class="sig-placeholder"><?= esc($estadoVisible) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="sig-name"><?= esc($firmante['nombre'] ?? '') ?></div>
                                <div class="sig-meta">
                                    <?= esc($firmante['cargo'] ?? 'Firmante') ?><br>
                                    Estado: <?= esc($estadoVisible) ?><br>
                                    Fecha: <?= $fmtFechaHora($firmante['firma_at'] ?? null) ?>
                                    <?php if (! empty($firmante['firma_ip'])): ?><br>IP: <?= esc($firmante['firma_ip']) ?><?php endif; ?>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <?php if (count($fila) === 1): ?>
                            <td class="sig-cell"></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (! empty($anexos)): ?>
        <h2>Anexos</h2>
        <table class="data">
            <thead>
                <tr><th>#</th><th>Nombre</th><th>Tipo</th></tr>
            </thead>
            <tbody>
                <?php foreach ($anexos as $i => $anexo): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($anexo['nombre']) ?></td>
                        <td><?= esc($anexo['mime'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="foot">
        <?php if (! empty($acta['codigo_verificacion'])): ?>
            Código de verificación: <strong><?= esc($acta['codigo_verificacion']) ?></strong> ·
        <?php endif; ?>
        Documento generado por la plataforma Actas · <?= esc($cliente['nombre'] ?? '') ?>
    </div>
</body>
</html>
