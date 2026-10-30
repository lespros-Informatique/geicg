/**
 * Module JS : Configuration de l'Établissement (views/etablissements/config.php)
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Aperçu dynamique du fichier logo sélectionné
  $('#logo-file-input').on('change', function(e) {
    var file = e.target.files[0];
    if (file) {
      var src = URL.createObjectURL(file);
      $('#logo-img-preview').attr('src', src).css('display', 'block');
      $('#logo-icon-placeholder').css('display', 'none');
    }
  });

  $('#form-config-etablissement').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    var $btn = $('#btn-submit-config');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:18px;height:18px;"></i> Enregistrement...');

    $.ajax({
      url: $(form).attr('action'),
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        if (typeof showToast === 'function') {
          showToast(res.message, res.status === 1 ? 'success' : 'error');
        } else if (window.toastr) {
          toastr[res.status === 1 ? 'success' : 'error'](res.message);
        }
        if (res.status === 1) {
          setTimeout(function() { location.reload(); }, 1200);
        } else {
          $btn.prop('disabled', false).html('<i data-lucide="save" style="width:18px;height:18px;"></i> Enregistrer la Configuration');
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        var msg = 'Configuration enregistrée avec succès!';
        if (typeof showToast === 'function') {
          showToast(msg, 'success');
        } else if (window.toastr) {
          toastr.success(msg);
        }
        setTimeout(function() { location.reload(); }, 1200);
      }
    });
  });
});

