<?php
if (! session('isLoggedIn')) {
    return;
}
$pendientes = (new \App\Libraries\Pendientes())->items((int) session('id_usuario'));
$n = count($pendientes);
?>
<div id="notifBellWrap" style="position:fixed;right:18px;bottom:82px;z-index:1080;">
    <button type="button" id="notifBellBtn" title="Pendientes" aria-label="Pendientes"
        style="position:relative;width:54px;height:54px;border-radius:50%;border:none;background:#16203a;color:#fff;box-shadow:0 6px 18px rgba(13,21,40,.35);cursor:pointer;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
        </svg>
        <?php if ($n > 0): ?>
            <span style="position:absolute;top:-4px;right:-4px;background:#dc3545;color:#fff;border-radius:999px;min-width:20px;height:20px;font-size:12px;line-height:20px;text-align:center;padding:0 5px;font-weight:bold;"><?= $n ?></span>
        <?php endif; ?>
    </button>

    <div id="notifBellPanel" style="display:none;position:absolute;right:0;bottom:64px;width:300px;max-height:60vh;overflow:auto;background:#fff;border:1px solid #e2e6ee;border-radius:12px;box-shadow:0 12px 30px rgba(0,0,0,.25);">
        <div style="padding:10px 14px;border-bottom:1px solid #eef1f5;font-weight:600;color:#16203a;">Pendientes (<?= $n ?>)</div>
        <?php if ($n === 0): ?>
            <div style="padding:16px 14px;color:#6b7280;font-size:14px;">No tienes pendientes. 🎉</div>
        <?php else: ?>
            <?php foreach ($pendientes as $p): ?>
                <a href="<?= esc($p['url']) ?>" style="display:block;padding:11px 14px;border-bottom:1px solid #f1f3f7;text-decoration:none;color:#1f2430;">
                    <div style="font-size:14px;font-weight:600;"><?= esc($p['icono']) ?> <?= esc($p['texto']) ?></div>
                    <div style="font-size:12px;color:#6b7280;"><?= esc($p['detalle']) ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    var btn = document.getElementById('notifBellBtn');
    var panel = document.getElementById('notifBellPanel');
    var wrap = document.getElementById('notifBellWrap');
    if (!btn || !panel) return;
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) panel.style.display = 'none';
    });
})();
</script>
