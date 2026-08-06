<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= RACINE ?>json/func.js"></script>
<script src="<?= RACINE ?>json/validator.js"></script>
<script src="<?= RACINE ?>json/app.js?v=4"></script>
<script src="<?= RACINE ?>json/auth.js"></script>
<script src="<?= RACINE ?>json/theme-manager.js"></script>
<script>
    try {
        lucide.createIcons();
    } catch(e) {
        console.warn('lucide error:', e);
    }
    window.addEventListener('error', function(e) {
        console.error('[global error]', e.message, 'at', e.filename + ':' + e.lineno);
    });
</script>
