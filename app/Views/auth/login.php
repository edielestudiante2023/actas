<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar · Actas</title>

    <!-- PWA -->
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Actas">
    <link rel="manifest" href="<?= base_url('manifest_login.json') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { max-width: 420px; width: 100%; margin: auto; border: none; border-radius: 18px; box-shadow: 0 12px 40px rgba(0,0,0,.25); }
        .login-logo { width: 72px; height: 72px; border-radius: 16px; }
        .pwa-install-section { margin-top: 18px; padding: 14px; background: #f1f3f5; border: 2px dashed #adb5bd; border-radius: 12px; display: none; }
        .pwa-install-section.visible { display: flex; align-items: center; gap: 12px; }
        .pwa-install-icon { width: 52px; height: 52px; border-radius: 12px; flex-shrink: 0; }
        .pwa-install-info { flex: 1; min-width: 0; }
        .pwa-ios-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 2000; align-items: center; justify-content: center; padding: 20px; }
        .pwa-ios-modal.visible { display: flex; }
        .pwa-ios-modal-content { background: #fff; border-radius: 16px; max-width: 380px; width: 100%; padding: 22px; }
    </style>
</head>
<body>
    <div class="card login-card p-4 m-3">
        <div class="text-center mb-3">
            <img src="<?= base_url('assets/icons/icon-192.png') ?>" alt="Actas" class="login-logo mb-2">
            <h4 class="fw-bold mb-0">Actas</h4>
            <small class="text-muted">Consejos de administración · Propiedad horizontal</small>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold">Ingresar</button>
        </form>

        <div class="text-center mt-3">
            <a href="<?= base_url('verificar') ?>" class="small text-decoration-none">Verificar acta firmada</a>
        </div>

        <!-- PWA install -->
        <div class="pwa-install-section" id="pwaInstallSection">
            <img src="<?= base_url('assets/icons/icon-192.png') ?>" alt="App" class="pwa-install-icon">
            <div class="pwa-install-info">
                <strong class="d-block">Instala la app</strong>
                <small class="text-muted d-block mb-2">Acceso rápido desde tu pantalla de inicio.</small>
                <button type="button" class="btn btn-sm btn-dark" id="pwaInstallBtn">
                    <span id="pwaInstallBtnText">Descargar app</span>
                </button>
            </div>
        </div>
    </div>

    <div class="pwa-ios-modal" id="pwaIosModal">
        <div class="pwa-ios-modal-content">
            <h5>Cómo instalar en iPhone/iPad</h5>
            <ol class="mb-3">
                <li>Toca <strong>Compartir</strong> en la barra de Safari.</li>
                <li>Elige <strong>"Añadir a pantalla de inicio"</strong>.</li>
                <li>Confirma con <strong>Añadir</strong>.</li>
            </ol>
            <button type="button" class="btn btn-dark w-100" id="pwaIosModalClose">Entendido</button>
        </div>
    </div>

    <script>
    (function () {
        var deferredPrompt = null;
        var section = document.getElementById('pwaInstallSection');
        var btn = document.getElementById('pwaInstallBtn');
        var btnText = document.getElementById('pwaInstallBtnText');
        var iosModal = document.getElementById('pwaIosModal');
        var iosClose = document.getElementById('pwaIosModalClose');

        var ua = window.navigator.userAgent;
        var isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) return;

        if (isIOS) {
            section.classList.add('visible');
            btnText.textContent = 'Cómo instalar';
            btn.addEventListener('click', function () { iosModal.classList.add('visible'); });
            iosClose.addEventListener('click', function () { iosModal.classList.remove('visible'); });
            iosModal.addEventListener('click', function (e) { if (e.target === iosModal) iosModal.classList.remove('visible'); });
            return;
        }

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            section.classList.add('visible');
        });
        btn.addEventListener('click', function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (c) {
                if (c.outcome === 'accepted') section.classList.remove('visible');
                deferredPrompt = null;
            });
        });
        window.addEventListener('appinstalled', function () { section.classList.remove('visible'); deferredPrompt = null; });
    })();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?= base_url('sw_login.js') ?>', { scope: '/', updateViaCache: 'none' })
                .catch(function (err) { console.log('SW login error:', err); });
        });
    }
    </script>
</body>
</html>
