$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  $('#radio-mode-nouveau').on('change', function() {
    if ($(this).is(':checked')) {
      $('#bloc-user-existant').slideUp(200);
      $('#bloc-user-identity').slideDown(200);
      $('.field-ident').prop('required', true);
      $('#select_user_existant').prop('required', false).val('');
    }
  });

  $('#radio-mode-existant').on('change', function() {
    if ($(this).is(':checked')) {
      $('#bloc-user-identity').slideUp(200);
      $('#bloc-user-existant').slideDown(200);
      $('.field-ident').prop('required', false);
      $('#select_user_existant').prop('required', true);
    }
  });

  // Soumission AJAX du formulaire enseignant (Création & Édition)
  $('#form-enseignant').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-enseignant');
    var originalText = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;display:inline-block;animation:spin 1s linear infinite;"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var targetUrl = res.redirect || (window.RACINE + 'enseignant/list');
          window.location.href = targetUrl;
        } else {
          $btn.prop('disabled', false).html(originalText);
          if (window.lucide) lucide.createIcons();
          var errMsg = res.message || 'Erreur lors de l\'enregistrement.';
          if (typeof showToast === 'function') {
            showToast(errMsg, 'error');
          } else if (window.toastr) {
            toastr.error(errMsg);
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalText);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'enregistrement';
        try {
          if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          } else if (xhr.responseText) {
            var json = JSON.parse(xhr.responseText);
            if (json && json.message) msg = json.message;
          }
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

