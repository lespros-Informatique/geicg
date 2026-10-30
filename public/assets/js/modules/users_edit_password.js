/**
 * Module JS : Modification du Mot de Passe Utilisateur (views/users/editPassword.php)
 */
$(document).ready(function() {
  const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/'));

  // Toggle afficher/masquer mot de passe
  $(document).on('click', '.btn-toggle-pwd', function() {
    const targetId = $(this).data('target');
    const input = $('#' + targetId);
    const icon = $(this).find('i');

    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      input.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  // Soumission du formulaire
  $('#formEditPassword').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btn_actions');
    const newPwd = $('#new_password').val();
    const confirmPwd = $('#confirm_password').val();

    if (newPwd !== confirmPwd) {
      if (typeof showToast === 'function') showToast('Les nouveaux mots de passe ne correspondent pas !', 'error');
      return;
    }

    if (typeof loading === 'function') loading(btn, true, 'Mise à jour en cours...');

    $.ajax({
      url: baseApi + 'user/editPassword',
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(rep) {
        if (typeof loading === 'function') loading(btn, false);
        if (rep.status) {
          if (typeof showToast === 'function') showToast(rep.message || 'Mot de passe modifié avec succès !', 'success');
          form[0].reset();
          setTimeout(function() {
            window.location.href = baseApi + 'user/profil';
          }, 1000);
        } else {
          if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de la modification', 'error');
        }
      },
      error: function(xhr) {
        if (typeof loading === 'function') loading(btn, false);
        let msg = 'Erreur serveur';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        if (typeof showToast === 'function') showToast(msg, 'error');
      }
    });
  });
});

