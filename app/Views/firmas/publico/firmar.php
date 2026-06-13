<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar acta · <?= esc($cliente['nombre'] ?? 'Actas') ?></title>
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f3f5; }
        .firma-canvas { border: 2px dashed #adb5bd; border-radius: 12px; width: 100%; height: 220px; touch-action: none; background: #fff; }
    </style>
</head>
<body>
    <main class="container py-4" style="max-width: 640px;">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-1">Firma del acta</h4>
                <p class="text-muted mb-3"><?= esc($cliente['nombre'] ?? '') ?></p>

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Acta</span><strong><?= esc($acta['numero'] ?? '') ?></strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Fecha</span><span><?= esc($acta['fecha'] ?? '') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Firmante</span><strong><?= esc($asistente['nombre'] ?? '') ?></strong></li>
                </ul>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
                <?php endif; ?>

                <label class="form-label">Dibuja tu firma en el recuadro:</label>
                <canvas id="firmaCanvas" class="firma-canvas"></canvas>
                <div class="d-flex justify-content-between mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiar">Limpiar</button>
                    <small class="text-muted">Usa el dedo (móvil) o el mouse.</small>
                </div>

                <form action="<?= base_url('firmar/' . esc($token, 'url')) ?>" method="post" id="formFirma" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="firma_imagen" id="firmaImagen">
                    <button type="submit" class="btn btn-primary w-100">Confirmar firma</button>
                </form>
            </div>
        </div>
        <p class="text-center text-muted small mt-3">Al firmar, se registra tu firma con fecha e IP como evidencia.</p>
    </main>

    <script>
    (function () {
        var canvas = document.getElementById('firmaCanvas');
        var ctx = canvas.getContext('2d');
        var drawing = false, hasDrawn = false;

        function resize() {
            var ratio = window.devicePixelRatio || 1;
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#16203a';
        }
        resize();

        function pos(e) {
            var rect = canvas.getBoundingClientRect();
            var p = e.touches ? e.touches[0] : e;
            return { x: p.clientX - rect.left, y: p.clientY - rect.top };
        }
        function start(e) { drawing = true; var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
        function move(e) { if (!drawing) return; var p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasDrawn = true; e.preventDefault(); }
        function end() { drawing = false; }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', end);

        document.getElementById('btnLimpiar').addEventListener('click', function () {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        });

        document.getElementById('formFirma').addEventListener('submit', function (e) {
            if (!hasDrawn) { e.preventDefault(); alert('Dibuja tu firma antes de confirmar.'); return; }
            document.getElementById('firmaImagen').value = canvas.toDataURL('image/png');
        });
    })();
    </script>
</body>
</html>
