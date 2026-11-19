<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= asset('json/func.js', true) ?>"></script>
<script src="<?= asset('json/validator.js', true) ?>"></script>
<script src="<?= asset('json/app.js', true) ?>"></script>
<script src="<?= asset('json/auth.js', true) ?>"></script>
<script src="<?= asset('json/theme-manager.js', true) ?>"></script>
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

