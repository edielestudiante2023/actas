<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar · Actas</title>

    <!-- Favicon (emblema dorado) -->
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/icons/favicon-32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/icons/favicon-16.png') ?>">

    <!-- PWA -->
    <meta name="theme-color" content="#16203a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Actas">
    <link rel="manifest" href="<?= base_url('manifest_login.json') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --navy:#16203a; --navy-deep:#0d1528; --gold:#c9a24b; --gold-dark:#b0883a; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh; margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
            background:
                radial-gradient(1200px 600px at 15% -10%, rgba(201,162,75,.18), transparent 60%),
                radial-gradient(900px 500px at 110% 110%, rgba(201,162,75,.10), transparent 55%),
                linear-gradient(160deg, var(--navy) 0%, var(--navy-deep) 100%);
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }
        .auth-card {
            position: relative; width: 100%; max-width: 410px;
            background: #fff; border-radius: 22px; padding: 64px 30px 28px;
            box-shadow: 0 24px 60px rgba(0,0,0,.45); border: 1px solid rgba(255,255,255,.06);
        }
        .brand-badge {
            position: absolute; top: -42px; left: 50%; transform: translateX(-50%);
            width: 84px; height: 84px; border-radius: 24px;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 12px 28px rgba(13,21,40,.22); border: 1px solid rgba(201,162,75,.45);
        }
        .brand-badge img { width: 58px; height: 58px; }
        .auth-title { font-weight: 800; letter-spacing: .5px; color: var(--navy); margin: 0; }
        .auth-sub { color: #6b7280; font-size: .82rem; }
        .form-label { font-weight: 600; font-size: .82rem; color: #374151; margin-bottom: 4px; }
        .input-group-text { background: #f8fafc; border-right: 0; color: var(--gold-dark); }
        .form-control { border-left: 0; }
        .form-control:focus { box-shadow: none; border-color: var(--gold); }
        .input-group:focus-within { box-shadow: 0 0 0 .2rem rgba(201,162,75,.18); border-radius: .5rem; }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control { border-color: var(--gold); }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
            color: var(--navy); font-weight: 700; border: none; padding: 11px; border-radius: 12px;
            box-shadow: 0 8px 20px rgba(201,162,75,.35); transition: transform .15s, box-shadow .15s;
        }
        .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(201,162,75,.45); color: var(--navy); }
        .auth-links a { color: var(--gold-dark); font-size: .82rem; text-decoration: none; font-weight: 600; }
        .auth-links a:hover { text-decoration: underline; }
        .toggle-pass { cursor: pointer; background: #f8fafc; border-left: 0; color: #9ca3af; }
        .pwa-install-section {
            margin-top: 22px; padding: 14px; background: #f8f9fb; border: 1px dashed #cdd5e1;
            border-radius: 14px; display: none;
        }
        .pwa-install-section.visible { display: flex; align-items: center; gap: 12px; }
        .pwa-install-icon { width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0; }
        .pwa-ios-modal { display: none; position: fixed; inset: 0; background: rgba(13,21,40,.7); z-index: 2000; align-items: center; justify-content: center; padding: 20px; }
        .pwa-ios-modal.visible { display: flex; }
        .pwa-ios-modal-content { background: #fff; border-radius: 16px; max-width: 380px; width: 100%; padding: 22px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand-badge">
            <img src="<?= base_url('assets/icons/entrega-05.png') ?>" alt="Actas">
        </div>

        <div class="text-center mb-4">
            <h3 class="auth-title">ACTAS</h3>
            <div class="auth-sub">Consejos de administración · Propiedad horizontal</div>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@dominio.com" value="<?= esc(old('email')) ?>" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passInput" class="form-control" placeholder="••••••••" required>
                    <span class="input-group-text toggle-pass" id="togglePass"><i class="bi bi-eye" id="togglePassIcon"></i></span>
                </div>
            </div>
            <button type="submit" class="btn btn-gold w-100">Ingresar</button>
        </form>

        <div class="text-center mt-3 auth-links">
            <a href="<?= base_url('password/forgot') ?>">Olvidé mi contraseña</a>
            <span class="text-muted mx-2">·</span>
            <a href="<?= base_url('verificar') ?>">Verificar acta firmada</a>
        </div>

        <!-- PWA install -->
        <div class="pwa-install-section" id="pwaInstallSection">
            <img src="<?= base_url('assets/icons/icon-192.png') ?>" alt="App" class="pwa-install-icon">
            <div class="flex-fill">
                <strong class="d-block" style="font-size:.92rem;">Instala la app</strong>
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
    // Mostrar/ocultar contraseña
    (function () {
        var t = document.getElementById('togglePass'), inp = document.getElementById('passInput'), ic = document.getElementById('togglePassIcon');
        if (t) t.addEventListener('click', function () {
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            ic.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    })();

    // PWA install
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
