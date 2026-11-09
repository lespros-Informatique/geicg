/**
 * Module JS Unifié : Configuration de l'Établissement (Option B : 4 Sections AJAX)
 * Conforme Règles Front-End GEICG (IIFE, Isolation, Zero Inline Script)
 */
$(document).ready(function() {
  if (!$('.form-config-section, #form-config-identite').length) return;

  if (window.lucide) {
    lucide.createIcons();
  }

  // 1. Défilement fluide pour la barre de navigation rapide
  $(document).on('click', '.btn-nav-section', function(e) {
    e.preventDefault();
    var target = $(this).attr('href');
    if ($(target).length) {
      $('.btn-nav-section').css({
        'background': '#F8FAFC',
        'color': '#475569',
        'border-color': '#E2E8F0'
      });
      $(this).css({
        'background': '#EFF6FF',
        'color': '#1E3A5F',
        'border-color': '#DBEAFE'
      });
      $('html, body').animate({
        scrollTop: $(target).offset().top - 80
      }, 350);
    }
  });

  // 2. Aperçu dynamique instantané du fichier logo sélectionné
  $('#logo-file-input').on('change', function(e) {
    var file = e.target.files[0];
    if (file) {
      var src = URL.createObjectURL(file);
      $('#logo-img-preview').attr('src', src).css('display', 'block');
      $('#logo-icon-placeholder').css('display', 'none');
    }
  });

  // 3. Soumission AJAX autonome pour chaque section
  $(document).on('submit', '.form-config-section', function(e) {
    e.preventDefault();
    var form = this;
    var $form = $(form);
    var $card = $form.closest('.config-section-card');
    var $btn = $form.find('button[type="submit"]');
    var originalBtnHtml = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    var formData = new FormData(form);

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        var isSuccess = (res.status === 1 || res.status === 'success' || res.success);
        var msg = res.message || 'Section enregistrée avec succès !';

        if (isSuccess) {
          if (typeof showToast === 'function') {
            showToast(msg, 'success', 'Configuration Établissement');
          } else if (window.toastr) {
            toastr.success(msg);
          }

          // Mise à jour de l'image de preview du logo si retournée
          if (res.data && res.data.logo_url) {
            var newLogoUrl = res.data.logo_url + '?t=' + new Date().getTime();
            $('#logo-img-preview').attr('src', newLogoUrl).css('display', 'block');
            $('#logo-icon-placeholder').css('display', 'none');
          }

          // Effet visuel d'enregistrement réussi sur la carte
          $card.css('transition', 'border-color 0.3s ease, box-shadow 0.3s ease')
               .css('border-color', '#86EFAC')
               .css('box-shadow', '0 4px 14px rgba(22, 163, 74, 0.12)');
          setTimeout(function() {
            $card.css('border-color', '#E2E8F0').css('box-shadow', '0 1px 3px rgba(0,0,0,0.05)');
          }, 1800);

          // Rétablissement du bouton avec icône de confirmation
          $btn.html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistré !');
          if (window.lucide) lucide.createIcons();

          setTimeout(function() {
            $btn.prop('disabled', false).html(originalBtnHtml);
            if (window.lucide) lucide.createIcons();
          }, 1600);

        } else {
          if (typeof showToast === 'function') {
            showToast(msg, 'error', 'Erreur de configuration');
          } else if (window.toastr) {
            toastr.error(msg);
          }
          $btn.prop('disabled', false).html(originalBtnHtml);
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function(xhr) {
        var errorMsg = "Une erreur est survenue lors de l'enregistrement de cette section.";
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) errorMsg = json.message;
        } catch(err) {}

        if (typeof showToast === 'function') {
          showToast(errorMsg, 'error', 'Erreur serveur');
        } else if (window.toastr) {
          toastr.error(errorMsg);
        }

        $btn.prop('disabled', false).html(originalBtnHtml);
        if (window.lucide) lucide.createIcons();
      }
    });
  });

});
