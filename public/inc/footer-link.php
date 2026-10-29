<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= RACINE ?>public/json/func.js?v=<?= time() ?>"></script>
<script src="<?= RACINE ?>public/json/validator.js?v=<?= time() ?>"></script>
<script src="<?= RACINE ?>public/json/app.js?v=<?= time() ?>"></script>
<script src="<?= RACINE ?>public/json/auth.js?v=<?= time() ?>"></script>
<script src="<?= RACINE ?>public/json/theme-manager.js?v=<?= time() ?>"></script>
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

<?php if (!empty($pageScripts)): ?>
    <?php foreach ((array)$pageScripts as $script): 
        $scriptClean = ltrim($script, '/');
        $physicalFile = dirname(__DIR__, 2) . '/' . $scriptClean;
        $v = file_exists($physicalFile) ? filemtime($physicalFile) : time();
    ?>
    <script src="<?= RACINE ?><?= htmlspecialchars($scriptClean, ENT_QUOTES, 'UTF-8') ?>?v=<?= $v ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

