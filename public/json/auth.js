// const LINK = window.location.origin + '/kits/';

function handleLogin() {
    $('.formConnexion').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        // console.log('[LOGIN] form data:', formData);return
        $.ajax({
            url: LINK + 'user/connexion',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                loading('.btnConnexion', true, '<i class="fa fa-spinner fa-spin"></i> Connexion...');
            },
            success: function(rep) {
                console.log(rep);
                
                loading('.btnConnexion', false, '<i class="fas fa-sign-in-alt"></i> Se connecter');
                console.log('[LOGIN] success response:', rep);
                if (rep.status) {
                    showToast(rep.message, 'success');
                    setTimeout(function() {
                        window.location.href = LINK;
                    }, 2000);
                } else {
                    showToast(rep.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                loading('.btnConnexion', false, '<i class="fas fa-sign-in-alt"></i> Se connecter');
                console.error('[LOGIN] ajax error:', status, error);
                console.error('[LOGIN] xhr status:', xhr.status);
                let msg = 'Erreur serveur';
                try {
                    const resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                    if (resp && resp.message) msg = resp.message;
                } catch(e) {}
                showToast(msg, 'error');
            }
        });
    });
}

$(document).ready(function() {
    handleLogin();

    const toggleBtn = document.getElementById('togglePassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (!passwordInput || !eyeIcon) return;

            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            if (window.lucide) lucide.createIcons();

            this.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    }
});
