/**
 * Module JS: Édition de la Fiche Étudiant
 * Externalisation stricte du code JS pour views/etudiants/edit.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var configEl = document.getElementById('etudiants-edit-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var defaultRedirect = config.redirectUrl || (racine + 'etudiant/list');

  $('#form-edit-etudiant').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-etudiant');
    var originalHtml = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin" style="width:18px;height:18px;display:inline-block;animation:spin 1s linear infinite;"></i> <span>Enregistrement en cours...</span>');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Fiche étudiant modifiée avec succès !', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Fiche étudiant modifiée avec succès !');
          }
          var targetUrl = res.redirect || defaultRedirect;
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de la modification', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de la modification');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });
});
