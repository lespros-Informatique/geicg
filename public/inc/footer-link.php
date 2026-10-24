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

    // Prise en charge des notifications différées / cross-page
    (function() {
        try {
            var stored = sessionStorage.getItem('flash_toast');
            if (stored) {
                sessionStorage.removeItem('flash_toast');
                var data = JSON.parse(stored);
                if (data && data.message) {
                    var type = data.type || 'success';
                    if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') {
                        toastr[type](data.message);
                    } else if (typeof showToast === 'function') {
                        showToast(data.message, type);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: type === 'error' ? 'error' : 'success', title: type === 'error' ? 'Erreur' : 'Succès', html: data.message, timer: 2500, showConfirmButton: false });
                    }
                }
            }
        } catch(e) {}
    })();
</script>
