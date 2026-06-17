<meta name="theme-color" content="#0d6efd">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Actas">
<link rel="manifest" href="<?= base_url('manifest.json') ?>">
<link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png') ?>">
<link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/icons/favicon-32.png') ?>">
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('<?= base_url('sw.js') ?>', { scope: '/', updateViaCache: 'none' })
            .catch(function (e) { console.log('SW error:', e); });
    });
}
</script>
