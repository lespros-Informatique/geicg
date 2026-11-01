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
    if (window.jQuery) {
        var metaCsrf = document.querySelector('meta[name="csrf-token"]');
        if (metaCsrf && metaCsrf.content) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': metaCsrf.content
                }
            });
        }
        $(document).ajaxError(function(event, jqXHR) {
            if (jqXHR.status === 401 || jqXHR.status === 419) {
                var res = {};
                try { res = JSON.parse(jqXHR.responseText || '{}'); } catch(e) {}
                var targetUrl = res.redirect || (window.RACINE || '/') + 'user/connexion?expired=1';
                if (window.toastr) {
                    toastr.warning('Votre session a expiré. Redirection vers la page de connexion...', 'Déconnexion');
                }
                setTimeout(function() {
                    window.location.href = targetUrl;
                }, 800);
            }
        });
    }
</script>

<?php foreach (ViewContext::resolveScripts($pageScripts ?? []) as $scriptUrl): ?>
    <script src="<?= $scriptUrl ?>"></script>
<?php endforeach; ?>

