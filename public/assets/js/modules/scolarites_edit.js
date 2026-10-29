/**
 * Module JS: Édition / Création de Scolarité & Tranches (Panier)
 * Externalisation stricte du code JS pour views/scolarites/edit.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  var configEl = document.getElementById('scolarite-edit-config');
  var config = {};
  if (configEl) {
    try {
      config = JSON.parse(configEl.textContent);
    } catch (e) {
      console.error('Erreur parsing scolarite-edit-config:', e);
    }
  }

  var isAddMode = Boolean(config.isAddMode);
  var allNiveaux = config.allNiveaux || [];
  var allFilieres = config.allFilieres || [];
  var cycleToNiveauxMap = config.cycleToNiveauxMap || {};
  var filiereCyclesPivot = config.filiereCyclesPivot || [];
  var preselectedFiliereCode = config.preselectedFiliereCode || '';
  var preselectedNiveauCode = config.preselectedNiveauCode || '';
  var existingTranches = config.existingTranches || [];
  var redirectUrl = config.redirectUrl || ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'scolarite/list');

  if ($.fn.select2) {
    $('#sel_annee_scolarite').select2({ placeholder: "-- Choisir une année --", allowClear: true, width: '100%' });
    $('#sel_cycle_scolarite').select2({ placeholder: "-- Tous les cycles --", allowClear: true, width: '100%' });
    $('#sel_type_filiere_scolarite').select2({ placeholder: "-- Tous les types --", allowClear: true, width: '100%' });
    $('#sel_filiere_scolarite').select2({
      placeholder: isAddMode ? "Sélectionnez une ou plusieurs filières..." : "-- Choisir une filière --",
      allowClear: !isAddMode,
      width: '100%'
    });
    $('#sel_niveau_scolarite').select2({
      placeholder: isAddMode ? "Sélectionnez un ou plusieurs niveaux..." : "-- Choisir un niveau --",
      allowClear: !isAddMode,
      width: '100%'
    });
    $('#sel_affectation_scolarite').select2({ minimumResultsForSearch: Infinity, width: '100%' });

    $('#sel_annee_scolarite').on('change select2:select', function() {
      updateTranchesSummary();
    });
  }

  // Verrouillage / Déverrouillage séquentiel strict en cascade
  function updateCascadeLocks() {
    var selectedCycle = $('#sel_cycle_scolarite').val();
    var rawNiveaux = $('#sel_niveau_scolarite').val();
    var selectedNiveaux = [];
    if (Array.isArray(rawNiveaux)) {
      selectedNiveaux = rawNiveaux.filter(Boolean);
    } else if (rawNiveaux) {
      selectedNiveaux = [rawNiveaux];
    }

    var hasCycle = Boolean(selectedCycle);
    var hasNiveau = selectedNiveaux.length > 0;

    // Étape 2: Verrouiller / Déverrouiller le champ Niveau(x) d'études
    if (!hasCycle) {
      $('#sel_niveau_scolarite').prop('disabled', true);
      if (isAddMode) $('#btn-toggle-all-niveaux').hide();
    } else {
      $('#sel_niveau_scolarite').prop('disabled', false);
      if (isAddMode) $('#btn-toggle-all-niveaux').show();
    }

    // Étape 3 & 4: Verrouiller / Déverrouiller Type de Filière et Filière(s) rattachée(s)
    if (!hasCycle || !hasNiveau) {
      $('#sel_type_filiere_scolarite').prop('disabled', true);
      $('#sel_filiere_scolarite').prop('disabled', true);
      if (isAddMode) $('#btn-toggle-all-filieres').hide();
    } else {
      $('#sel_type_filiere_scolarite').prop('disabled', false);
      $('#sel_filiere_scolarite').prop('disabled', false);
      if (isAddMode) $('#btn-toggle-all-filieres').show();
    }

    // Rafraîchir le composant Select2
    if ($.fn.select2) {
      $('#sel_niveau_scolarite, #sel_type_filiere_scolarite, #sel_filiere_scolarite').trigger('change.select2');
    }
  }

  // 1. Filtrage en cascade des Niveaux d'études selon le Cycle sélectionné
  function filterNiveauxByCycle() {
    var selectedCycle = $('#sel_cycle_scolarite').val();
    var $niveauSelect = $('#sel_niveau_scolarite');
    var currentVals = $niveauSelect.val();

    $niveauSelect.empty();

    if (!selectedCycle) {
      $niveauSelect.append('<option value="" disabled selected>-- 2. Choisir d\'abord un cycle académique --</option>');
      $niveauSelect.trigger('change.select2');
      updateCascadeLocks();
      return;
    }

    if (!isAddMode) {
      $niveauSelect.append('<option value="">-- Choisir un niveau --</option>');
    }

    var validNiveauCodes = cycleToNiveauxMap[selectedCycle] || [];
    var count = 0;

    allNiveaux.forEach(function(n) {
      if (validNiveauCodes.indexOf(n.code_niveau) !== -1) {
        count++;
        var isSel = false;
        if (Array.isArray(currentVals)) {
          isSel = currentVals.indexOf(n.code_niveau) !== -1;
        } else {
          isSel = (n.code_niveau === currentVals || n.code_niveau === preselectedNiveauCode);
        }
        var labelText = n.libelle_niveau + (n.code_niveau ? ' (' + n.code_niveau + ')' : '');
        var optHtml = '<option value="' + $('<div>').text(n.code_niveau).html() + '"' + (isSel ? ' selected' : '') + '>' + $('<div>').text(labelText).html() + '</option>';
        $niveauSelect.append(optHtml);
      }
    });

    if (count === 0) {
      $niveauSelect.append('<option value="" disabled>(Aucun niveau rattaché à ce cycle)</option>');
    }

    $niveauSelect.trigger('change.select2');
    updateCascadeLocks();
  }

  // 2. Filtrage en cascade des Filières selon (Cycle x Niveaux d'études x Type de Filière)
  function filterFilieresByCascade() {
    var selectedCycle = $('#sel_cycle_scolarite').val();
    var rawNiveaux = $('#sel_niveau_scolarite').val();
    var selectedNiveaux = [];
    if (Array.isArray(rawNiveaux)) {
      selectedNiveaux = rawNiveaux.filter(Boolean);
    } else if (rawNiveaux) {
      selectedNiveaux = [rawNiveaux];
    }
    var selectedType = $('#sel_type_filiere_scolarite').val();
    var $filiereSelect = $('#sel_filiere_scolarite');
    var currentVals = $filiereSelect.val();

    $filiereSelect.empty();

    if (!selectedCycle || selectedNiveaux.length === 0) {
      $filiereSelect.append('<option value="" disabled selected>-- 4. Choisir d\'abord le(s) niveau(x) d\'études --</option>');
      $filiereSelect.trigger('change.select2');
      updateCascadeLocks();
      return;
    }

    if (!isAddMode) {
      $filiereSelect.append('<option value="">-- Choisir une filière --</option>');
    }

    var count = 0;
    allFilieres.forEach(function(f) {
      var matchCycle = (f.cycles && f.cycles.indexOf(selectedCycle) !== -1);
      var matchType = !selectedType || (f.type_filiere === selectedType);
      var matchNiveau = filiereCyclesPivot.some(function(fc) {
        return fc.cycle_code === selectedCycle &&
               fc.filiere_code === f.code_filiere &&
               (!fc.niveau_code || selectedNiveaux.indexOf(fc.niveau_code) !== -1);
      });

      if (matchCycle && matchType && matchNiveau) {
        count++;
        var isSel = false;
        if (Array.isArray(currentVals)) {
          isSel = currentVals.indexOf(f.code_filiere) !== -1;
        } else {
          isSel = (f.code_filiere === currentVals || f.code_filiere === preselectedFiliereCode);
        }
        var labelText = f.libelle_filiere + (f.code_filiere ? ' (' + f.code_filiere + ')' : '');
        var optHtml = '<option value="' + $('<div>').text(f.code_filiere).html() + '"' + (isSel ? ' selected' : '') + '>' + $('<div>').text(labelText).html() + '</option>';
        $filiereSelect.append(optHtml);
      }
    });

    if (count === 0) {
      $filiereSelect.append('<option value="" disabled>(Aucune filière disponible pour ces critères)</option>');
    }

    $filiereSelect.trigger('change.select2');
    updateCascadeLocks();
  }

  // Événements de cascade
  $('#sel_cycle_scolarite').on('change select2:select select2:clear', function() {
    preselectedNiveauCode = '';
    preselectedFiliereCode = '';
    $('#sel_niveau_scolarite').val(null);
    $('#sel_filiere_scolarite').val(null);
    filterNiveauxByCycle();
    filterFilieresByCascade();
    checkDuplicateScolarites();
  });

  $('#sel_niveau_scolarite').on('change select2:select select2:unselect select2:clear', function() {
    preselectedFiliereCode = '';
    $('#sel_filiere_scolarite').val(null);
    filterFilieresByCascade();
    checkDuplicateScolarites();
  });

  $('#sel_type_filiere_scolarite').on('change select2:select select2:clear', function() {
    filterFilieresByCascade();
    checkDuplicateScolarites();
  });

  // Initialisation au chargement de la page
  filterNiveauxByCycle();
  filterFilieresByCascade();
  updateCascadeLocks();

  var isAllDuplicate = false;

  function displayDuplicateNotice(msg, isError, $form) {
    $form.find('.js-duplicate-notice-banner').remove();
    if (!msg) return;

    var bgColor = isError ? '#FEE2E2' : '#FEF3C7';
    var borderColor = isError ? '#FCA5A5' : '#FCD34D';
    var textColor = isError ? '#991B1B' : '#B45309';
    var iconName = isError ? 'alert-triangle' : 'info';

    var alertHtml = '<div class="alert js-duplicate-notice-banner" style="background:' + bgColor + '; border:1px solid ' + borderColor + '; color:' + textColor + '; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:10px;">' +
                    '<i data-lucide="' + iconName + '" style="width:18px; height:18px; flex-shrink:0;"></i>' +
                    '<span>' + msg + '</span>' +
                    '</div>';
    $form.prepend(alertHtml);
    if (window.lucide) lucide.createIcons();
  }

  function checkDuplicateScolarites() {
    var filieres = $('#sel_filiere_scolarite').val();
    var niveaux = $('#sel_niveau_scolarite').val();
    var cycleCode = $('#sel_cycle_scolarite').val() || '';
    var typeFiliere = $('#sel_type_filiere_scolarite').val() || '';
    var affectation = $('#sel_affectation_scolarite').val();
    var anneeCode = $('input[name="annee_code"]').val() || '';
    var idScolarite = $('input[name="id_scolarite"]').val() || 0;
    var $form = $('form');

    if (niveaux && (Array.isArray(niveaux) ? niveaux.length > 0 : niveaux !== '')) {
      var checkUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('scolarite/checkExists') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'scolarite/checkExists');
      $.ajax({
        url: checkUrl,
        type: 'GET',
        data: {
          annee_code: anneeCode,
          cycle_code: cycleCode,
          type_filiere: typeFiliere,
          filiere_codes: filieres,
          filiere_code: filieres,
          niveau_codes: niveaux,
          niveau_code: niveaux,
          affectation_etat: affectation,
          id_scolarite: idScolarite
        },
        dataType: 'json',
        success: function(resp) {
          if (resp) {
            if (resp.all_exist) {
              isAllDuplicate = true;
              displayDuplicateNotice(resp.message, true, $form);
              $form.find('button[type="submit"]').prop('disabled', true).css('opacity', '0.6');
            } else if (resp.existing_count > 0) {
              isAllDuplicate = false;
              displayDuplicateNotice(resp.message, false, $form);
              $form.find('button[type="submit"]').prop('disabled', false).css('opacity', '1');
            } else {
              isAllDuplicate = false;
              $form.find('.js-duplicate-notice-banner').remove();
              $form.find('button[type="submit"]').prop('disabled', false).css('opacity', '1');
            }
          }
        }
      });
    } else {
      isAllDuplicate = false;
      $form.find('.js-duplicate-notice-banner').remove();
      $form.find('button[type="submit"]').prop('disabled', false).css('opacity', '1');
    }
  }

  $('#sel_filiere_scolarite, #sel_niveau_scolarite, #sel_affectation_scolarite').on('change select2:select select2:unselect select2:clear', function() {
    checkDuplicateScolarites();
  });

  var trancheCounter = 0;
  var deletedTrancheIds = [];

  function formatFcfa(val) {
    return Number(val || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  function displayFormError(msg, $form) {
    if (window.toastr) {
      toastr.error(msg);
    } else if (typeof showToast === 'function') {
      showToast(msg, 'error');
    }
    
    $form.find('.js-date-error-banner').remove();
    var alertHtml = '<div class="alert alert-danger js-date-error-banner" style="background:#FEE2E2; border:1px solid #FCA5A5; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:10px;">' +
                    '<i data-lucide="alert-triangle" style="width:18px; height:18px; flex-shrink:0;"></i>' +
                    '<span>' + msg + '</span>' +
                    '</div>';
    $form.prepend(alertHtml);
    if (window.lucide) lucide.createIcons();
    $('html, body').animate({ scrollTop: $form.offset().top - 80 }, 200);
  }

  function displayFormSuccess(msg, $form) {
    if (window.toastr) {
      toastr.success(msg);
    } else if (typeof showToast === 'function') {
      showToast(msg, 'success');
    }
    
    $form.find('.js-date-error-banner, .js-date-success-banner').remove();
    var alertHtml = '<div class="alert alert-success js-date-success-banner" style="background:#DCFCE7; border:1px solid #86EFAC; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:10px;">' +
                    '<i data-lucide="check-circle" style="width:18px; height:18px; flex-shrink:0;"></i>' +
                    '<span>' + msg + '</span>' +
                    '</div>';
    $form.prepend(alertHtml);
    if (window.lucide) lucide.createIcons();
    $('html, body').animate({ scrollTop: $form.offset().top - 80 }, 200);
  }

  function updateTranchesSummary() {
    var montantScolarite = parseFloat($('input[name="montant_scolarite"]').val()) || 0;
    var totalTranches = 0;
    var rowCount = 0;

    $('#tbody-tranches tr').each(function(idx) {
      rowCount++;
      $(this).find('.tranche-index-label').text(rowCount);
      var montant = parseFloat($(this).find('.input-tranche-montant').val()) || 0;
      totalTranches += montant;
    });

    $('#lbl-total-scolarite').text(formatFcfa(montantScolarite));
    $('#lbl-cumul-tranches').text(formatFcfa(totalTranches));

    var diff = montantScolarite - totalTranches;
    var $lblReste = $('#lbl-reste-scolarite');
    var $badge = $('#badge-repartition-status');
    var $btnAdd = $('#btn-add-tranche');

    if (montantScolarite <= 0) {
      $btnAdd.prop('disabled', true).css({
        'opacity': '0.5',
        'cursor': 'not-allowed',
        'pointer-events': 'none'
      }).attr('title', 'Veuillez d\'abord renseigner le montant annuel de la scolarité.');
    } else if (totalTranches >= montantScolarite) {
      $btnAdd.prop('disabled', true).css({
        'opacity': '0.5',
        'cursor': 'not-allowed',
        'pointer-events': 'none'
      }).attr('title', 'La totalité de la scolarité est déjà couverte par les tranches.');
    } else {
      $btnAdd.prop('disabled', false).css({
        'opacity': '1',
        'cursor': 'pointer',
        'pointer-events': 'auto'
      }).attr('title', 'Ajouter une tranche');
    }

    if (rowCount === 0) {
      $('#tranches-empty-state').show();
      $lblReste.text(formatFcfa(montantScolarite)).css('color', '#64748B');
      $badge.text('0 tranche').css({ 'background': '#F1F5F9', 'color': '#64748B' });
    } else {
      $('#tranches-empty-state').hide();
      if (montantScolarite > 0) {
        if (Math.abs(diff) < 0.01) {
          $lblReste.text('0 FCFA (100% Couvert)').css('color', '#15803D');
          $badge.text(rowCount + ' tranche(s) - 100% planifié').css({ 'background': '#DCFCE7', 'color': '#15803D' });
        } else if (diff > 0) {
          $lblReste.text(formatFcfa(diff) + ' restant').css('color', '#B45309');
          $badge.text(rowCount + ' tranche(s) - Incomplet').css({ 'background': '#FEF3C7', 'color': '#B45309' });
        } else {
          $lblReste.text('Dépassement de ' + formatFcfa(Math.abs(diff))).css('color', '#B91C1C');
          $badge.text(rowCount + ' tranche(s) - Dépassement !').css({ 'background': '#FEE2E2', 'color': '#B91C1C' });
        }
      } else {
        $lblReste.text(formatFcfa(diff)).css('color', '#64748B');
        $badge.text(rowCount + ' tranche(s)').css({ 'background': '#EFF6FF', 'color': '#1E3A5F' });
      }
    }
  }

  function addTrancheRow(data) {
    trancheCounter++;
    var idx = trancheCounter;
    var id = data ? (data.id_tranche || '') : '';
    var libelle = data ? (data.libelle_tranche || '') : ('Tranche ' + ($('#tbody-tranches tr').length + 1));
    var montant = data ? (data.montant_tranche || '') : '';
    var dateLimite = data ? (data.date_limite || '') : '';
    var statut = data ? (data.statut_tranche || 'actif') : 'actif';

    var escapedLibelle = $('<div>').text(libelle).html();

    var rowHtml = '<tr class="tranche-row" data-idx="' + idx + '" style="border-bottom:1px solid #F1F5F9;">' +
      '<td style="padding:8px 12px; text-align:center; font-weight:800; color:#1E3A5F;" class="tranche-index-label">1</td>' +
      '<td style="padding:8px 12px;">' +
        (id ? '<input type="hidden" name="tranches[' + idx + '][id_tranche]" value="' + id + '">' : '') +
        '<input type="text" name="tranches[' + idx + '][libelle_tranche]" class="form-control form-control-sm input-tranche-libelle" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px; font-weight:600; font-size:13px;" value="' + escapedLibelle + '" placeholder="Ex: 1ère Tranche (Inscription)" required>' +
      '</td>' +
      '<td style="padding:8px 12px;">' +
        '<input type="number" step="any" min="0" name="tranches[' + idx + '][montant_tranche]" class="form-control form-control-sm input-tranche-montant" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px; font-weight:700; color:#0F172A; font-size:13px;" value="' + montant + '" placeholder="Ex: 250000" required>' +
      '</td>' +
      '<td style="padding:8px 12px;">' +
        '<input type="date" name="tranches[' + idx + '][date_limite]" class="form-control form-control-sm input-tranche-date" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px; font-size:13px;" value="' + dateLimite + '" required>' +
      '</td>' +
      '<td style="padding:8px 12px; text-align:center;">' +
        '<select name="tranches[' + idx + '][statut_tranche]" class="form-control form-control-sm" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:5px 8px; font-size:12px; font-weight:700;">' +
          '<option value="actif" ' + (statut === 'actif' ? 'selected' : '') + '>Actif</option>' +
          '<option value="inactif" ' + (statut === 'inactif' ? 'selected' : '') + '>Inactif</option>' +
        '</select>' +
      '</td>' +
      '<td style="padding:8px 12px; text-align:center;">' +
        '<button type="button" class="btn btn-sm btn-danger btn-remove-tranche" data-id="' + id + '" style="background:#FEE2E2; border:none; color:#B91C1C; border-radius:6px; padding:6px 9px; cursor:pointer;" title="Supprimer cette tranche">' +
          '<i data-lucide="trash-2" style="width:15px; height:15px;"></i>' +
        '</button>' +
      '</td>' +
    '</tr>';

    $('#tbody-tranches').append(rowHtml);
    if (window.lucide) lucide.createIcons();
    updateTranchesSummary();
  }

  $('#btn-add-tranche').on('click', function(e) {
    var montantScolarite = parseFloat($('input[name="montant_scolarite"]').val()) || 0;
    var totalTranches = 0;
    $('#tbody-tranches .input-tranche-montant').each(function() {
      totalTranches += parseFloat($(this).val()) || 0;
    });

    if (montantScolarite <= 0) {
      e.preventDefault();
      displayFormError("Veuillez d'abord saisir le montant annuel de la scolarité avant de pouvoir ajouter des tranches.", $('form'));
      return false;
    }

    if (totalTranches >= montantScolarite) {
      e.preventDefault();
      displayFormError("Le montant total de la scolarité (" + formatFcfa(montantScolarite) + ") est déjà entièrement couvert par les tranches actuelles. Impossible d'ajouter une tranche supplémentaire.", $('form'));
      return false;
    }

    addTrancheRow();
  });

  $(document).on('click', '.btn-remove-tranche', function() {
    var id = $(this).data('id');
    if (id) {
      deletedTrancheIds.push(id);
      $('#deleted_tranches_ids').val(deletedTrancheIds.join(','));
    }
    $(this).closest('tr').remove();
    updateTranchesSummary();
  });

  $(document).on('input change', 'input[name="montant_scolarite"], .input-tranche-montant', function() {
    updateTranchesSummary();
  });

  // Soumission AJAX du formulaire
  $('form').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btnSubmit = $form.find('button[type="submit"]');

    if (isAllDuplicate) {
      displayFormError("Impossible d'enregistrer : Toutes les combinaisons sélectionnées existent déjà en base.", $form);
      return false;
    }

    $form.find('.js-date-error-banner, .js-date-success-banner').remove();

    var montantScolarite = parseFloat($('input[name="montant_scolarite"]').val()) || 0;
    var totalTranches = 0;

    $('#tbody-tranches tr').each(function() {
      var montant = parseFloat($(this).find('.input-tranche-montant').val()) || 0;
      totalTranches += montant;
    });

    if (montantScolarite > 0 && totalTranches > montantScolarite) {
      var depassement = totalTranches - montantScolarite;
      displayFormError("Erreur sur le montant : Le cumul des tranches (" + formatFcfa(totalTranches) + ") dépasse le montant annuel de la scolarité (" + formatFcfa(montantScolarite) + ") de " + formatFcfa(depassement) + ". Veuillez ajuster les montants des tranches.", $form);
      return false;
    }

    $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:14px;height:14px;margin-right:6px;"></span> Enregistrement...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(response) {
        $btnSubmit.prop('disabled', false).html('Enregistrer');
        if (response && (response.status === 1 || response.status === 'success' || response.success)) {
          var msg = response.message || 'Tarif de scolarité enregistré avec succès !';
          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Succès !',
              text: msg,
              timer: 1500,
              showConfirmButton: false
            }).then(function() {
              window.location.href = redirectUrl;
            });
          } else {
            displayFormSuccess(msg, $form);
            setTimeout(function() {
              window.location.href = redirectUrl;
            }, 1200);
          }
        } else {
          var errorMsg = (response && response.message) ? response.message : 'Une erreur est survenue lors de l\'enregistrement.';
          if (window.Swal) {
            Swal.fire({
              icon: 'error',
              title: 'Erreur',
              text: errorMsg
            });
          }
          displayFormError(errorMsg, $form);
        }
      },
      error: function(xhr) {
        $btnSubmit.prop('disabled', false).html('Enregistrer');
        var errText = 'Erreur serveur lors de l\'enregistrement.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errText = xhr.responseJSON.message;
        }
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: errText
          });
        }
        displayFormError(errText, $form);
      }
    });
  });

  // Pré-remplissage des tranches existantes
  if (existingTranches && existingTranches.length > 0) {
    existingTranches.forEach(function(t) {
      addTrancheRow(t);
    });
  } else {
    updateTranchesSummary();
  }
});
