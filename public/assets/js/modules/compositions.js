/**
 * Module JS Unifie : Compositions & Examens (Liste, Planification/Edition & Fiche Details)
 * GEICG - Application Web
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/');

  // =========================================================================
  // 1. VUE : LISTE DES COMPOSITIONS (Execute si #table-compositions est present)
  // =========================================================================
  if ($('#table-compositions').length) {

  var cfg = {};
  try {
    var cfgEl = document.getElementById('compositions-list-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var csrfToken = cfg.csrfToken || '';

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Filtrage Niveau -> Classe
  $('#filter-niveau').on('change', function() {
    var niveauCode = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!niveauCode || !optNiveau || optNiveau === niveauCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#filter-classe option:selected').prop('disabled')) {
      $('#filter-classe').val('').trigger('change.select2');
    } else {
      $('#filter-classe').select2({ width: '100%' });
    }
    table.ajax.reload();
  });

  $('#filter-classe, #filter-annee').on('change', function() {
    var cls = $('#filter-classe').val();
    if (cls) {
      $('#btn-add-composition').attr('href', window.RACINE + 'composition/formulaire?classe_code=' + encodeURIComponent(cls));
    } else {
      $('#btn-add-composition').attr('href', window.RACINE + 'composition/formulaire');
    }
    table.ajax.reload();
  });

  var table = $('#table-compositions').DataTable({
    ajax: {
      url: window.RACINE + 'composition/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { 
        data: null, 
        defaultContent: '',
        width: '50px', 
        render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }
      },
      { 
        data: 'libelle_composition', 
        defaultContent: '',
        render: function(d, t, r) { 
          var typ = (r && r.type_composition) ? r.type_composition : 'EXAMEN';
          var badgeStyle = 'background:#EFF6FF; color:#1D4ED8;';
          var badgeText = 'EXAMEN';
          if (typ === 'DEVOIR') {
            badgeStyle = 'background:#FEF3C7; color:#B45309;';
            badgeText = 'DEVOIR';
          } else if (typ === 'INTERROGATION') {
            badgeStyle = 'background:#F0FDF4; color:#15803D;';
            badgeText = 'INTERRO';
          } else if (typ !== 'EXAMEN') {
            badgeStyle = 'background:#F1F5F9; color:#475569;';
            badgeText = typ;
          }
          var typeBadge = '<span style="font-size:10px; font-weight:800; ' + badgeStyle + ' padding:2px 7px; border-radius:4px; margin-right:6px; display:inline-block;">' + badgeText + '</span>';
          return '<div>' + typeBadge + '<strong style="font-size:13px; color:#0F172A;">' + (d || r.code_composition || '-') + '</strong></div>'; 
        } 
      },
      { 
        data: 'libelle_semestre', 
        defaultContent: '',
        render: function(d, t, r) { 
          return '<span style="font-size:13px; color:#0284C7; font-weight:700;">' + (d || r.semestre_code || '-') + '</span>'; 
        } 
      },
      { 
        data: 'coefficient', 
        defaultContent: '1.00',
        className: 'text-center',
        render: function(d) { 
          var coef = d ? parseFloat(d).toFixed(2) : '1.00';
          return '<span class="badge" style="background:#F1F5F9; color:#334155; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px;">' + coef + '</span>'; 
        } 
      },
      { 
        data: 'date_composition', 
        defaultContent: '',
        render: function(d, type, row) { 
          var dateStr = d || (row ? row.created_at_composition : null);
          if (!dateStr) return '<span style="color:#94A3B8;">-</span>';
          var cleanDate = dateStr.split(' ')[0];
          var parts = cleanDate.split('-');
          if (parts.length === 3) {
            return '<strong style="font-size:13px; color:#1E3A5F;">' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</strong>';
          }
          return '<strong style="font-size:13px; color:#1E3A5F;">' + cleanDate + '</strong>';
        } 
      },
      { 
        data: 'date_fin_composition', 
        defaultContent: '',
        render: function(d, type, row) { 
          var dateStr = d || (row ? (row.date_composition || row.created_at_composition) : null);
          if (!dateStr) return '<span style="color:#94A3B8;">-</span>';
          var cleanDate = dateStr.split(' ')[0];
          var parts = cleanDate.split('-');
          if (parts.length === 3) {
            return '<strong style="font-size:13px; color:#1E3A5F;">' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</strong>';
          }
          return '<strong style="font-size:13px; color:#1E3A5F;">' + cleanDate + '</strong>';
        } 
      },
      { 
        data: 'statut_composition', 
        defaultContent: 'programme',
        width: '90px', 
        className: 'text-center', 
        render: function(d, type, row) {
          var isActif = (d === 'programme' || d === 'actif');
          var checkedAttr = isActif ? 'checked' : '';
          return '<div style="display:flex; justify-content:center; align-items:center;">' +
                 '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Programmé - Cliquez pour terminer' : 'Terminé - Cliquez pour programmer') + '">' +
                 '<input type="checkbox" class="toggle-statut-composition" data-id="' + (row ? row.id_composition : '') + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                 '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                 '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                 '</span>' +
                 '</label>' +
                 '</div>';
        }
      },
      { 
        data: null, 
        defaultContent: '',
        orderable: false, 
        className: 'text-end',
        render: function(d) {
          if (!d) return '';
          var detailsUrl = window.RACINE + 'composition/details/' + (d.editId || d.id_composition);
          var editUrl = window.RACINE + 'composition/edition/' + (d.editId || d.id_composition);
          var noterUrl = window.RACINE + 'note/saisieClasse?type_evaluation_code=EXAMEN&composition_code=' + encodeURIComponent(d.code_composition || '');
          return '<div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:nowrap; align-items:center;">' +
                   '<a href="' + noterUrl + '" class="btn btn-sm" style="background:#16A34A; color:#FFFFFF; border:none; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Saisir les notes de l\'épreuve">' +
                     '<i data-lucide="edit-3" style="width:14px; height:14px;"></i> Noter' +
                   '</a>' +
                   '<a href="' + detailsUrl + '" class="btn btn-sm" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Voir les détails de l\'épreuve">' +
                     '<i data-lucide="eye" style="width:14px; height:14px;"></i> Détails' +
                   '</a>' +
                   '<a href="' + editUrl + '" class="btn btn-sm" style="background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Modifier">' +
                     '<i data-lucide="edit" style="width:14px; height:14px;"></i> Éditer' +
                   '</a>' +
                 '</div>';
        } 
      }
    ],
    language: { 
      url: window.RACINE + 'public/json/datatables-i18n-fr-FR.json'
    },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
      try {
        var api = this.api();
        var rows = api.rows({ search: 'applied' }).data().toArray();
        var total = rows.length;
        var programme = 0;
        var termine = 0;
        rows.forEach(function(r) {
          if (r && (r.statut_composition === 'termine')) {
            termine++;
          } else {
            programme++;
          }
        });
        $('#kpi-total-compositions').text(total);
        $('#kpi-programme-compositions').text(programme);
        $('#kpi-termine-compositions').text(termine);
      } catch (e) {}
    }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-composition', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: window.RACINE + 'composition/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  }

  // =========================================================================
  // 2. VUE : PROGRAMMATION / EDITION D'UNE COMPOSITION (Execute si #form-composition est present)
  // =========================================================================
  if ($('#form-composition').length) {

  var cfg = {};
  try {
    var cfgEl = document.getElementById('compositions-edit-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}

  window.ALL_CLASSES = cfg.allClasses || [];
  window.ALL_FILIERES = cfg.allFilieres || [];
  window.ALL_NIVEAUX = cfg.allNiveaux || [];
  window.EDT_ACTIVE_TARGETS = cfg.edtActiveTargets || [];

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // Synchronisation des dates de début et de fin
  $('#date_composition').on('change', function() {
    var startVal = $(this).val();
    var $endInput = $('#date_fin_composition');
    if (startVal) {
      $endInput.attr('min', startVal);
      if ($endInput.val() && $endInput.val() < startVal) {
        $endInput.val(startVal);
      }
    }
  }).trigger('change');

  // Active Niveaux & Filières mapping from emplois_temps
  var edtActiveNiveaux = [];
  var edtActiveFiliereMap = {}; // niveau_code -> array of filiere_codes

  if (window.EDT_ACTIVE_TARGETS && window.EDT_ACTIVE_TARGETS.length > 0) {
    window.EDT_ACTIVE_TARGETS.forEach(function(item) {
      if (item.niveau_code) {
        if (edtActiveNiveaux.indexOf(item.niveau_code) === -1) {
          edtActiveNiveaux.push(item.niveau_code);
        }
        if (!edtActiveFiliereMap[item.niveau_code]) {
          edtActiveFiliereMap[item.niveau_code] = [];
        }
        if (item.filiere_code && edtActiveFiliereMap[item.niveau_code].indexOf(item.filiere_code) === -1) {
          edtActiveFiliereMap[item.niveau_code].push(item.filiere_code);
        }
      }
    });
  }

  function filterFilieresForNiveauCard($card) {
    var nivCode = $card.find('.sel-niveau-item').val();
    var $checkboxGroup = $card.find('.filieres-checkbox-group');

    if (!nivCode) {
      $checkboxGroup.find('label').show();
      $checkboxGroup.find('.chk-filiere-item').prop('disabled', false);
      return;
    }

    if (edtActiveNiveaux.length > 0) {
      var activeFiliereCodes = edtActiveFiliereMap[nivCode] || [];
      $checkboxGroup.find('.chk-filiere-item').each(function() {
        var fCode = $(this).val();
        var $label = $(this).closest('label');
        if (activeFiliereCodes.indexOf(fCode) !== -1) {
          $label.show();
          $(this).prop('disabled', false);
        } else {
          $label.hide();
          $(this).prop('checked', false);
          $(this).prop('disabled', true);
        }
      });
    } else {
      var validFiliereCodes = window.ALL_CLASSES.filter(function(c) {
        return c.niveau_code === nivCode;
      }).map(function(c) { return c.filiere_code; });

      $checkboxGroup.find('.chk-filiere-item').each(function() {
        var fCode = $(this).val();
        var $label = $(this).closest('label');
        if (validFiliereCodes.indexOf(fCode) !== -1 || validFiliereCodes.length === 0) {
          $label.show();
          $(this).prop('disabled', false);
        } else {
          $label.hide();
          $(this).prop('checked', false);
          $(this).prop('disabled', true);
        }
      });
    }
  }

  function updateDisabledNiveauxOptions() {
    var selectedNiveaux = [];
    $('.sel-niveau-item').each(function() {
      var val = $(this).val();
      if (val) selectedNiveaux.push(val);
    });

    $('.sel-niveau-item').each(function() {
      var $select = $(this);
      var currentVal = $select.val();

      $select.find('option').each(function() {
        var optVal = $(this).attr('value');
        if (!optVal) return;

        var isNotInEdt = (edtActiveNiveaux.length > 0 && edtActiveNiveaux.indexOf(optVal) === -1);
        var isAlreadySelected = (optVal !== currentVal && selectedNiveaux.indexOf(optVal) !== -1);

        if (isNotInEdt || isAlreadySelected) {
          $(this).prop('disabled', true);
        } else {
          $(this).prop('disabled', false);
        }
      });
    });
  }

  function renderClassesPreview($card) {
    filterFilieresForNiveauCard($card);

    var nivCode = $card.find('.sel-niveau-item').val();
    var checkedFilieres = [];
    $card.find('.chk-filiere-item:checked:visible').each(function() {
      checkedFilieres.push($(this).val());
    });

    var $previewContainer = $card.find('.preview-badges-list');
    if (!nivCode) {
      $previewContainer.html('<span style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>');
      return;
    }

    if (checkedFilieres.length === 0) {
      $previewContainer.html('<span style="color: #DC2626; font-weight: 700;">Aucune filière configurée dans l\'emploi du temps pour ce niveau.</span>');
      return;
    }

    var matchingClasses = window.ALL_CLASSES.filter(function(c) {
      if (c.niveau_code !== nivCode) return false;
      return checkedFilieres.indexOf(c.filiere_code) !== -1;
    });

    if (matchingClasses.length === 0) {
      $previewContainer.html('<span style="color: #DC2626; font-weight: 600;">Aucune classe configurée dans l\'emploi du temps.</span>');
    } else {
      var html = matchingClasses.map(function(c) {
        return '<span style="background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">' + c.libelle_classe + '</span>';
      }).join(' ');
      $previewContainer.html(html);
    }
  }

  function updateNiveauRowIndices() {
    $('#niveaux-container .niveau-row-card').each(function(idx) {
      $(this).find('.niveau-row-num').text(idx + 1);
      $(this).find('.sel-niveau-item').attr('name', 'niveaux[' + idx + '][niveau_code]');
      $(this).find('.chk-filiere-item').each(function() {
        $(this).attr('name', 'niveaux[' + idx + '][filiere_codes][]');
      });

      if ($('#niveaux-container .niveau-row-card').length > 1) {
        $(this).find('.btn-remove-niveau-row').css('display', 'inline-flex');
      } else {
        $(this).find('.btn-remove-niveau-row').css('display', 'none');
      }
    });
  }

  // Event handlers for dynamic level row
  $(document).on('change', '.sel-niveau-item', function() {
    var $card = $(this).closest('.niveau-row-card');
    updateDisabledNiveauxOptions();
    renderClassesPreview($card);
  });

  $(document).on('change', '.chk-filiere-item', function() {
    var $card = $(this).closest('.niveau-row-card');
    renderClassesPreview($card);
  });

  $(document).on('click', '.btn-toggle-all-filieres', function(e) {
    e.preventDefault();
    var $card = $(this).closest('.niveau-row-card');
    var $chks = $card.find('.chk-filiere-item:visible');
    var allChecked = $chks.filter(':checked').length === $chks.length;
    $chks.prop('checked', !allChecked);
    $(this).text(allChecked ? 'Tout cocher' : 'Tout décocher');
    renderClassesPreview($card);
  });

  $('#btn-add-niveau-row').on('click', function(e) {
    e.preventDefault();
    var idx = $('#niveaux-container .niveau-row-card').length;
    var filieresHtml = window.ALL_FILIERES.map(function(f) {
      return '<label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #334155; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; cursor: pointer; user-select: none;">' +
             '<input type="checkbox" name="niveaux[' + idx + '][filiere_codes][]" value="' + f.code_filiere + '" class="chk-filiere-item" checked style="accent-color: #1E3A5F;"> ' +
             f.libelle_filiere +
             '</label>';
    }).join('');

    var niveauxHtml = window.ALL_NIVEAUX.map(function(n) {
      return '<option value="' + n.code_niveau + '">' + n.libelle_niveau + '</option>';
    }).join('');

    var newCardHtml = `
      <div class="niveau-row-card" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 20px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <span style="font-weight: 800; font-size: 13px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="bookmark" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Groupe Niveau #<span class="niveau-row-num">${idx + 1}</span>
          </span>
          <button type="button" class="btn-remove-niveau-row" style="background: transparent; border: none; color: #EF4444; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Retirer ce niveau
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; align-items: start;">
          <div>
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px;">
              Niveau d'Études <span style="color: #EF4444;">*</span>
            </label>
            <select class="form-control sel-niveau-item" name="niveaux[${idx}][niveau_code]" required style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
              <option value="">-- Choisir un niveau --</option>
              ${niveauxHtml}
            </select>
          </div>

          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label style="font-weight: 700; font-size: 12px; color: #334155; margin: 0;">
                Filières Concernées <span style="font-size: 11px; color: #15803D; font-weight: 600;">(Emploi du temps)</span>
              </label>
              <span style="font-size: 11px;">
                <a href="#" class="btn-toggle-all-filieres" style="color: #1D4ED8; font-weight: 700; text-decoration: none;">Tout cocher</a>
              </span>
            </div>
            <div class="filieres-checkbox-group" style="display: flex; flex-wrap: wrap; gap: 8px; background: #FFFFFF; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; max-height: 120px; overflow-y: auto;">
              ${filieresHtml}
            </div>
          </div>
        </div>

        <div class="classes-preview-box" style="margin-top: 12px; font-size: 12px; color: #475569; display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
          <span style="font-weight: 700; color: #0F172A;"><i data-lucide="check-circle" style="width: 14px; height: 14px; color: #16A34A; vertical-align: text-bottom;"></i> Classes concernées :</span>
          <span class="preview-badges-list" style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>
        </div>
      </div>
    `;

    $('#niveaux-container').append(newCardHtml);
    if (window.lucide) lucide.createIcons();
    updateNiveauRowIndices();
    updateDisabledNiveauxOptions();
    var $newCard = $('#niveaux-container .niveau-row-card').last();
    renderClassesPreview($newCard);
  });

  $(document).on('click', '.btn-remove-niveau-row', function(e) {
    e.preventDefault();
    if ($('#niveaux-container .niveau-row-card').length > 1) {
      $(this).closest('.niveau-row-card').remove();
      updateNiveauRowIndices();
      updateDisabledNiveauxOptions();
      $('#niveaux-container .niveau-row-card').each(function() {
        renderClassesPreview($(this));
      });
    }
  });

  // Initial setup on page load
  updateDisabledNiveauxOptions();
  $('#niveaux-container .niveau-row-card').each(function() {
    renderClassesPreview($(this));
  });

  // Soumission AJAX du formulaire de programmation de composition
  $('#form-composition').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-composition');
    var originalHtml = $btn.html();

    // Validation préalable : au moins un niveau et une filière cochée
    var hasValidTarget = false;
    $('#niveaux-container .niveau-row-card').each(function() {
      var niv = $(this).find('.sel-niveau-item').val();
      var checkedFil = $(this).find('.chk-filiere-item:checked:visible').length;
      if (!checkedFil) {
        checkedFil = $(this).find('.chk-filiere-item:checked').length;
      }
      if (niv && checkedFil > 0) {
        hasValidTarget = true;
      }
    });

    if (!hasValidTarget) {
      var warningMsg = 'Veuillez sélectionner au moins un niveau et cocher au moins une filière associée.';
      if (window.toastr) {
        toastr.warning(warningMsg);
      } else if (window.Swal) {
        Swal.fire({ icon: 'warning', title: 'Attention', text: warningMsg });
      } else {
        alert(warningMsg);
      }
      return false;
    }

    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;display:inline-block;animation:spin 1s linear infinite;"></i> Enregistrement en cours...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) {
            toastr.success(res.message || 'Composition / Examen programmé avec succès !');
          } else if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Succès',
              text: res.message || 'Composition / Examen programmé avec succès !',
              timer: 1500,
              showConfirmButton: false
            });
          }
          var targetUrl = res.redirect || (window.RACINE + 'composition/list');
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          var errMsg = res.message || 'Erreur lors de la programmation de la composition.';
          if (window.toastr) {
            toastr.error(errMsg);
          } else if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Erreur', text: errMsg });
          } else {
            alert(errMsg);
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        if (window.toastr) {
          toastr.error(msg);
        } else if (window.Swal) {
          Swal.fire({ icon: 'error', title: 'Erreur Serveur', text: msg });
        } else {
          alert(msg);
        }
      }
    });
  });

  }

  // =========================================================================
  // 3. VUE : FICHE DETAILS D'UNE COMPOSITION (Execute si #table-niveaux-filieres ou modale est present)
  // =========================================================================
  if ($('#table-niveaux-filieres').length || $('#form-modal-saisie-notes').length) {

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

  }

  if (window.lucide) lucide.createIcons();
});
