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
        .firma-preview { max-width: 100%; max-height: 160px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 10px; background: #fff; padding: 8px; display: none; }
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
                <?php if (session('success')): ?>
                    <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
                <?php endif; ?>

                <label class="form-label">Elige cómo registrar tu firma:</label>
                <ul class="nav nav-pills nav-fill mb-3" id="firmaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="dibujar-tab" data-bs-toggle="pill" data-bs-target="#dibujarPanel" type="button" role="tab">Firmar con dedo</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="imagen-tab" data-bs-toggle="pill" data-bs-target="#imagenPanel" type="button" role="tab">Subir imagen</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="dibujarPanel" role="tabpanel" aria-labelledby="dibujar-tab">
                        <canvas id="firmaCanvas" class="firma-canvas"></canvas>
                        <div class="d-flex justify-content-between mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiar">Limpiar</button>
                            <small class="text-muted">Usa el dedo (móvil) o el mouse.</small>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="imagenPanel" role="tabpanel" aria-labelledby="imagen-tab">
                        <input type="file" class="form-control" id="firmaArchivo" accept="image/png,image/jpeg,image/webp">
                        <div class="small text-muted mt-2">Formatos permitidos: PNG, JPG/JPEG o WebP. Máximo 2 MB.</div>
                        <img id="firmaPreview" class="firma-preview mt-3" alt="Vista previa de la firma">
                    </div>
                </div>

                <form action="<?= base_url('firmar/' . esc($token, 'url')) ?>" method="post" id="formFirma" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="firma_imagen" id="firmaImagen">
                    <button type="submit" class="btn btn-primary w-100">Confirmar firma</button>
                </form>

                <hr class="my-4">

                <?php if (! empty($solicitudAusente)): ?>
                    <div class="alert alert-warning mb-0">
                        Tu solicitud para marcarte como ausente está pendiente de revisión.
                    </div>
                <?php else: ?>
                    <form action="<?= base_url('firmar/' . esc($token, 'url') . '/ausente') ?>" method="post">
                        <?= csrf_field() ?>
                        <label for="motivoAusente" class="form-label">No puedo firmar esta acta</label>
                        <textarea class="form-control" id="motivoAusente" name="motivo" rows="3" maxlength="1000" placeholder="Indica el motivo para solicitar que te marquen como ausente" required></textarea>
                        <button type="submit" class="btn btn-outline-secondary w-100 mt-2" onclick="return confirm('Se enviará la solicitud al administrador para revisión. ¿Continuar?');">Solicitar marcar ausente</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <p class="text-center text-muted small mt-3">Al firmar, se registra tu firma con fecha e IP como evidencia.</p>
    </main>

    <script>
    (function () {
        var canvas = document.getElementById('firmaCanvas');
        var ctx = canvas.getContext('2d');
        var drawing = false, hasDrawn = false, uploadedSignature = '';
        var fileInput = document.getElementById('firmaArchivo');
        var preview = document.getElementById('firmaPreview');

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
            uploadedSignature = '';
            if (fileInput) fileInput.value = '';
            if (preview) { preview.removeAttribute('src'); preview.style.display = 'none'; }
        });

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                uploadedSignature = '';
                if (preview) { preview.removeAttribute('src'); preview.style.display = 'none'; }

                var file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                if (!file) return;

                if (!/^image\/(png|jpeg|webp)$/.test(file.type)) {
                    alert('Sube una imagen PNG, JPG/JPEG o WebP.');
                    fileInput.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen no debe superar 2 MB.');
                    fileInput.value = '';
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (ev) {
                    var img = new Image();
                    img.onload = function () {
                        var maxW = 900, maxH = 300;
                        var scale = Math.min(maxW / img.width, maxH / img.height, 1);
                        var w = Math.max(1, Math.round(img.width * scale));
                        var h = Math.max(1, Math.round(img.height * scale));
                        var out = document.createElement('canvas');
                        out.width = w;
                        out.height = h;
                        var outCtx = out.getContext('2d');
                        outCtx.drawImage(img, 0, 0, w, h);
                        uploadedSignature = out.toDataURL('image/png');
                        if (preview) {
                            preview.src = uploadedSignature;
                            preview.style.display = 'block';
                        }
                    };
                    img.onerror = function () {
                        alert('No fue posible leer la imagen de firma.');
                        fileInput.value = '';
                    };
                    img.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        document.getElementById('formFirma').addEventListener('submit', function (e) {
            if (uploadedSignature !== '') {
                document.getElementById('firmaImagen').value = uploadedSignature;
                return;
            }

            if (!hasDrawn) { e.preventDefault(); alert('Dibuja tu firma o sube una imagen antes de confirmar.'); return; }
            document.getElementById('firmaImagen').value = canvas.toDataURL('image/png');
        });
    })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->include("partials/home_fab") ?>
</body>
</html>
