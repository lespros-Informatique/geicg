$(document).ready(function() {
  var cfg = {};
  try {
    var cfgEl = document.getElementById('compositions-details-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var defaultCompositionCode = cfg.compositionCode || '';

  if (window.lucide) lucide.createIcons();

  if ($.fn.DataTable && $('#table-niveaux-filieres').length) {
    $('#table-niveaux-filieres').DataTable({
      pageLength: 10,
      autoWidth: false,
      language: {
        url: window.RACINE + 'public/json/datatables-i18n-fr-FR.json'
      },
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      }
    });
  }

  var allCheckedState = false;

  $(document).on('click', '.btn-open-saisie-modal', function() {
    var $btn = $(this);
    var classeCode = $btn.attr('data-classe-code') || $btn.data('classe-code') || '';
    var classeLibelle = $btn.attr('data-classe-libelle') || $btn.data('classe-libelle') || '';
    var cibleCode = $btn.attr('data-cible-code') || $btn.data('cible-code') || '';

    $('#modal_input_classe_code').val(classeCode);
    $('#modal_input_cible_code').val(cibleCode);
    $('#modal_display_classe_name').text(classeLibelle);

    $('#modal-saisie-matiere').css('display', 'flex');
    loadMatieresForSelectedClasse();
  });

  function loadMatieresForSelectedClasse() {
    var classeCode = $('#modal_input_classe_code').val();
    var cibleCode = $('#modal_input_cible_code').val();
    var compositionCode = $('input[name="composition_code"]').val() || defaultCompositionCode;

    var $container = $('#matieres-checkboxes-container');
    $container.html('<div style="padding: 30px; text-align: center; color: #64748B;"><i data-lucide="loader" class="spin" style="width: 24px; height: 24px;"></i><p style="margin: 8px 0 0 0; font-size: 13px;">Chargement des matières de l\'emploi du temps...</p></div>');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'composition/getMatieresClasseApi',
      type: 'GET',
      data: { classe_code: classeCode, composition_code: compositionCode, composition_niveau_filiere_code: cibleCode },
      dataType: 'json',
      success: function(res) {
        if (res && res.data) {
          var items = res.data;
          var savedCodes = res.saved_codes || [];
          var source = res.source;

          if (source === 'emploi_temps') {
            $('#badge-source-matieres').html('<i data-lucide="clock" style="width:12px;height:12px;"></i> Emploi du Temps (Session Active)').css({ 'background': '#DCFCE7', 'color': '#15803D' });
          } else if (source === 'enseignant_matiere') {
            $('#badge-source-matieres').html('<i data-lucide="user-check" style="width:12px;height:12px;"></i> Affectation Enseignants').css({ 'background': '#FEF3C7', 'color': '#B45309' });
          } else {
            $('#badge-source-matieres').html('<i data-lucide="book-open" style="width:12px;height:12px;"></i> Toutes les Matières').css({ 'background': '#F1F5F9', 'color': '#475569' });
          }

          $('#badge-matieres-count').text(items.length + ' matière' + (items.length > 1 ? 's' : ''));

          if (items.length === 0) {
            $container.html('<div style="padding: 24px; text-align: center; color: #94A3B8; font-size: 13px;">Aucune matière trouvée pour cette classe.</div>');
            return;
          }

          var html = '';
          items.forEach(function(m, idx) {
            var isChecked = false;
            if (savedCodes.length > 0) {
              isChecked = (savedCodes.indexOf(m.code_matiere) !== -1);
            } else {
              isChecked = (idx === 0);
            }
            var checkedAttr = isChecked ? 'checked' : '';

            html += '<label class="matiere-card-item ' + (isChecked ? 'selected' : '') + '">';
            html += '  <div style="display: flex; align-items: center; gap: 12px;">';
            html += '    <input type="checkbox" name="matiere_codes[]" value="' + m.code_matiere + '" class="chk-matiere-item" ' + checkedAttr + ' style="width: 18px; height: 18px; accent-color: #1E3A5F; cursor: pointer;">';
            html += '    <span style="font-weight: 800; font-size: 13.5px; color: #0F172A;">' + m.libelle_matiere + '</span>';
            html += '  </div>';
            html += '  <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">' + m.code_matiere + '</span>';
            html += '</label>';
          });

          $container.html(html);
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        $container.html('<div style="padding: 20px; text-align: center; color: #DC2626; font-size: 13px;">Erreur lors du chargement des matières.</div>');
      }
    });
  }

  // Highlight et sélection interactive des cartes
  $(document).on('change', '.chk-matiere-item', function() {
    var $card = $(this).closest('.matiere-card-item');
    if ($(this).is(':checked')) {
      $card.addClass('selected');
    } else {
      $card.removeClass('selected');
    }
  });

  // Bouton Tout cocher / Tout décocher
  $(document).on('click', '#btn-toggle-select-all', function(e) {
    e.preventDefault();
    allCheckedState = !allCheckedState;
    $('.chk-matiere-item:visible').prop('checked', allCheckedState).trigger('change');
    $(this).text(allCheckedState ? 'Tout décocher' : 'Tout cocher');
  });

  // Form submit handler : enregistrement dans composition_matieres sans redirection
  $(document).on('submit', '#form-modal-saisie-notes', function(e) {
    e.preventDefault();

    var checkedMatieres = $('.chk-matiere-item:checked');
    var selectedMatCodes = [];
    checkedMatieres.each(function() {
      selectedMatCodes.push($(this).val());
    });

    var cibleCode = $('#modal_input_cible_code').val();
    var compositionCode = $('input[name="composition_code"]').val() || defaultCompositionCode;

    if (!cibleCode || !compositionCode) {
      if (window.toastr) {
        toastr.error('Données de composition manquantes. Veuillez rouvrir le formulaire.', 'Erreur');
      }
      return false;
    }

    var $btnSubmit = $(this).find('button[type="submit"]');
    var originalBtnHtml = '<i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider les Matières';
    
    $btnSubmit.prop('disabled', true).html('<span class="btn-circle-loader" style="margin-right: 6px;"></span> Enregistrement...');

    $.ajax({
      url: window.RACINE + 'composition/saveMatieresClasseApi',
      type: 'POST',
      data: {
        composition_code: compositionCode,
        composition_niveau_filiere_code: cibleCode,
        matiere_codes: selectedMatCodes
      },
      dataType: 'json',
      success: function(res) {
        $btnSubmit.prop('disabled', false).html(originalBtnHtml);
        if (window.lucide) lucide.createIcons();

        if (res && (res.status === 1 || res.status === '1' || res.success)) {
          if (window.toastr) {
            toastr.success(res.message || 'Matières enregistrées avec succès !', 'Succès');
          }
          $('#modal-saisie-matiere').hide();
          setTimeout(function() {
            location.reload();
          }, 300);
        } else {
          if (window.toastr) {
            toastr.error((res ? res.message : null) || 'Erreur lors de l\'enregistrement des matières.', 'Erreur');
          }
        }
      },
      error: function(xhr, status, err) {
        $btnSubmit.prop('disabled', false).html(originalBtnHtml);
        if (window.lucide) lucide.createIcons();
        if (window.toastr) {
          toastr.error('Erreur de connexion au serveur lors de l\'enregistrement.', 'Erreur Réseau');
        }
      }
    });
    return false;
  });

  $(document).on('click', '.btn-close-saisie-modal', function() {
    $('#modal-saisie-matiere').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-saisie-matiere')) {
      $('#modal-saisie-matiere').hide();
    }
  });
});

