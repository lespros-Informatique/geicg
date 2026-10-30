/**
 * Module JS : Création / Modification d'un Rôle & Permissions RBAC (views/roles/edit.php)
 */
$(document).ready(function() { 
  var racine = window.RACINE || '/';
  if (window.lucide) lucide.createIcons(); 

  // Soumission AJAX du formulaire avec retour dynamique et redirection
  $('form').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-save-role');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Enregistrement en cours...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Rôle enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Rôle enregistré avec succès');
          setTimeout(function() {
            window.location.href = res.redirect || (racine + 'role/list');
          }, 600);
        } else {
          $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer le Rôle & Permissions');
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer le Rôle & Permissions');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });
});

// Toggle ouvrir / réduire un module avec rotation du chevron
window.toggleAccordion = function(grpId) {
  var $body = $('#body-' + grpId);
  var $wrap = $('#chevron-wrap-' + grpId);
  if ($body.is(':visible')) {
    $body.slideUp(180);
    $wrap.css('transform', 'rotate(-90deg)');
  } else {
    $body.slideDown(180);
    $wrap.css('transform', 'rotate(0deg)');
  }
};

// Déplier ou replier tous les modules
window.toggleAllGroups = function(open) {
  if (open) {
    $('.module-body').slideDown(180);
    $('[id^="chevron-wrap-"]').css('transform', 'rotate(0deg)');
  } else {
    $('.module-body').slideUp(180);
    $('[id^="chevron-wrap-"]').css('transform', 'rotate(-90deg)');
  }
};

// Cocher ou décocher toutes les permissions d'un module spécifique
window.checkModulePerms = function(grpId, checked, evt) {
  if (evt) evt.stopPropagation();
  $('.perm-group-' + grpId).prop('checked', checked);
  var total = $('.perm-group-' + grpId).length;
  updateGroupBadge(grpId, total);
};

// Cocher ou décocher globalement toutes les permissions
window.checkAllGlobal = function(checked) {
  $('.perm-checkbox').prop('checked', checked);
  $('[id^="box-"]').each(function() {
    var grpId = $(this).attr('id').replace('box-', '');
    var total = $('.perm-group-' + grpId).length;
    updateGroupBadge(grpId, total);
  });
};

// Mise à jour en temps réel du badge "X / Y cochées"
window.updateGroupBadge = function(grpId, total) {
  var checkedCount = $('.perm-group-' + grpId + ':checked').length;
  $('#badge-' + grpId).text(checkedCount + ' / ' + total + ' cochées');
  if (checkedCount === total) {
    $('#badge-' + grpId).css({'background': '#DCFCE7', 'color': '#15803D', 'borderColor': '#86EFAC'});
  } else if (checkedCount > 0) {
    $('#badge-' + grpId).css({'background': '#EFF6FF', 'color': '#1E3A5F', 'borderColor': '#BFDBFE'});
  } else {
    $('#badge-' + grpId).css({'background': '#F1F5F9', 'color': '#64748B', 'borderColor': '#CBD5E1'});
  }
};

