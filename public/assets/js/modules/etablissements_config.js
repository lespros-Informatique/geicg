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

  // 2b. Ajout / Suppression dynamique de paliers dans le Barème Bulletin (Section 5)
  $(document).on('click', '#btn-add-bareme-row', function() {
    var $tbody = $('#tbody-baremes-bulletin');
    var idx = $tbody.find('tr').length + '_' + new Date().getTime();
    var rowHtml = '<tr class="bareme-row" style="border-bottom: 1px solid #E2E8F0;">' +
      '<td style="padding: 8px 6px;">' +
        '<input type="hidden" name="baremes[' + idx + '][id_bareme]" value="0">' +
        '<input type="number" step="0.01" min="0" max="20" name="baremes[' + idx + '][note_min]" value="0.00" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1; font-weight: 700;" required>' +
      '</td>' +
      '<td style="padding: 8px 6px;">' +
        '<input type="number" step="0.01" min="0" max="20" name="baremes[' + idx + '][note_max]" value="10.00" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1; font-weight: 700;" required>' +
      '</td>' +
      '<td style="padding: 8px 6px;">' +
        '<input type="text" name="baremes[' + idx + '][appreciation_matiere]" value="" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1;" placeholder="Appréciation matière" required>' +
      '</td>' +
      '<td style="padding: 8px 6px;">' +
        '<input type="text" name="baremes[' + idx + '][mention_bulletin]" value="" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1;" placeholder="Mention bilan" required>' +
      '</td>' +
      '<td style="padding: 8px 6px;">' +
        '<input type="text" name="baremes[' + idx + '][appreciation_conseil]" value="" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1;" placeholder="Appréciation conseil (S1)" required>' +
      '</td>' +
      '<td style="padding: 8px 6px;">' +
        '<input type="text" name="baremes[' + idx + '][appreciation_conseil_s2]" value="" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1;" placeholder="Décision conseil (S2)" required>' +
      '</td>' +
      '<td style="padding: 8px 6px;">' +
        '<select name="baremes[' + idx + '][code_distinction_sanction]" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #CBD5E1; background: #FFFFFF;">' +
          '<option value="AUCUN">Aucune case cochée</option>' +
          '<option value="FELICITATIONS">Distinction : Tableau d\'honneur + Félicitations</option>' +
          '<option value="ENCOURAGEMENTS">Distinction : Tableau d\'honneur + Encouragements</option>' +
          '<option value="HONNEUR">Distinction : Tableau d\'honneur</option>' +
          '<option value="AVERTISSEMENT">Sanction : Avertissement Travail</option>' +
          '<option value="BLAME">Sanction : Blâme Travail</option>' +
        '</select>' +
      '</td>' +
      '<td style="padding: 8px 6px; text-align: center;">' +
        '<button type="button" class="btn-remove-bareme-row" title="Supprimer ce palier" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; border-radius: 6px; padding: 6px 8px; cursor: pointer;">' +
          '<i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>' +
        '</button>' +
      '</td>' +
    '</tr>';
    $tbody.append(rowHtml);
    if (window.lucide) lucide.createIcons();
  });

  $(document).on('click', '.btn-remove-bareme-row', function() {
    var $tbody = $('#tbody-baremes-bulletin');
    if ($tbody.find('tr').length <= 1) {
      if (typeof showToast === 'function') {
        showToast('Le barème doit conserver au moins un palier.', 'warning', 'Barème Bulletin');
      }
      return;
    }
    $(this).closest('tr').remove();
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
