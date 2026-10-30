/**
 * Module JS: Configuration du Dossier de Pièces par Cycle
 * Externalisation stricte du code JS pour views/piece_fournir_cycle/edit.php
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var configEl = document.getElementById('piece-fournir-cycle-edit-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var pieceOptions = Array.isArray(config.pieceOptions) ? config.pieceOptions : [];
  var alreadyAssignedCodes = [];
  var rowIndex = 0;

  function loadAssignedPiecesForCycle(cycleCode, niveauCode) {
    if (!cycleCode) {
      alreadyAssignedCodes = [];
      refreshAllDropdowns();
      return;
    }

    $.ajax({
      url: racine + 'piece_fournir_cycle/getByCycleApi',
      type: 'GET',
      data: { 
        cycle_code: cycleCode,
        niveau_code: niveauCode || ''
      },
      dataType: 'json',
      success: function(res) {
        alreadyAssignedCodes = res.assignedCodes || [];
        refreshAllDropdowns();
        if (alreadyAssignedCodes.length > 0) {
          if (window.toastr) toastr.info(alreadyAssignedCodes.length + ' pièce(s) sont déjà enregistrées pour cette configuration.');
        }
      }
    });
  }

  function showDuplicateWarning(msg) {
    if (window.toastr) {
      toastr.warning(msg);
    }
    $('#duplicate-warning-text').html(msg);
    $('#duplicate-warning-banner').css('display', 'flex').hide().fadeIn(200);
  }

  function hideDuplicateWarning() {
    var selected = [];
    var hasDup = false;
    $('.sel-piece').each(function() {
      var v = $(this).val();
      if (v) {
        if (selected.indexOf(v) !== -1 || alreadyAssignedCodes.indexOf(v) !== -1) {
          hasDup = true;
        }
        selected.push(v);
      }
    });
    if (!hasDup) {
      $('#duplicate-warning-banner').fadeOut(200);
    }
  }

  function highlightDuplicateSelect(selectElem) {
    selectElem.css({
      'border': '2px solid #EF4444',
      'background-color': '#FEF2F2',
      'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.2)'
    });
    setTimeout(function() {
      selectElem.css({
        'border': '',
        'background-color': '',
        'box-shadow': ''
      });
    }, 2500);
  }

  function refreshAllDropdowns() {
    $('.bulk-row-cycle').each(function() {
      var currentSelect = $(this).find('.sel-piece');
      var currentVal = currentSelect.val();

      var selectedInOtherRows = [];
      $('.sel-piece').not(currentSelect).each(function() {
        var v = $(this).val();
        if (v) selectedInOtherRows.push(v);
      });

      currentSelect.find('option').each(function() {
        var optVal = $(this).val();
        if (!optVal) return;

        var isAlreadyInDb = alreadyAssignedCodes.indexOf(optVal) !== -1;
        var isSelectedOther = selectedInOtherRows.indexOf(optVal) !== -1;

        var baseLibelle = '';
        for (var i = 0; i < pieceOptions.length; i++) {
          if (pieceOptions[i].code === optVal) {
            baseLibelle = pieceOptions[i].libelle;
            break;
          }
        }

        if (isAlreadyInDb) {
          $(this).prop('disabled', true);
          $(this).text(baseLibelle + ' (Déjà configuré dans ce cycle)');
        } else if (isSelectedOther) {
          $(this).prop('disabled', true);
          $(this).text(baseLibelle + ' (Déjà sélectionné ci-dessus)');
        } else {
          $(this).prop('disabled', false);
          $(this).text(baseLibelle);
        }
      });

      if (alreadyAssignedCodes.indexOf(currentVal) !== -1) {
        currentSelect.val('');
      }
    });
  }

  function createRow(selectedCode, nbEx, nature) {
    selectedCode = selectedCode || '';
    nbEx = nbEx || 1;
    nature = nature || 'photocopie_simple';

    var optHtml = '<option value="">-- Choisir une pièce à fournir --</option>';
    pieceOptions.forEach(function(item) {
      var isAlreadyInDb = alreadyAssignedCodes.indexOf(item.code) !== -1;
      var isSel = (item.code === selectedCode && !isAlreadyInDb) ? 'selected' : '';
      var disabled = isAlreadyInDb ? 'disabled' : '';
      var suffix = isAlreadyInDb ? ' (Déjà configuré dans ce cycle)' : '';
      optHtml += '<option value="' + item.code + '" ' + isSel + ' ' + disabled + '>' + $('<div>').text(item.libelle + suffix).html() + '</option>';
    });

    var html = '<tr class="bulk-row-cycle" data-idx="' + rowIndex + '" style="border-bottom: 1px solid #F1F5F9;">' +
      '<td style="padding: 10px 12px;">' +
        '<select name="items[' + rowIndex + '][piece_code]" required class="form-select sel-piece" style="border-radius:6px; font-weight:700; font-size:13px; padding:8px 12px; width:100%; box-sizing:border-box;">' +
          optHtml +
        '</select>' +
      '</td>' +
      '<td style="padding: 10px 12px; text-align:center;">' +
        '<input type="number" min="1" step="1" name="items[' + rowIndex + '][nombre_exemplaires]" value="' + nbEx + '" required class="form-control" style="border-radius:6px; font-weight:700; text-align:center; font-size:13px; padding:8px; width:100%; box-sizing:border-box;">' +
      '</td>' +
      '<td style="padding: 10px 12px;">' +
        '<select name="items[' + rowIndex + '][nature_document]" class="form-select" style="border-radius:6px; font-weight:600; font-size:12.5px; padding:8px 10px; width:100%; box-sizing:border-box;">' +
          '<option value="photocopie_simple" ' + (nature === 'photocopie_simple' ? 'selected' : '') + '>Photocopie Simple</option>' +
          '<option value="photocopie_legalisee" ' + (nature === 'photocopie_legalisee' ? 'selected' : '') + '>Photocopie Légalisée</option>' +
          '<option value="original" ' + (nature === 'original' ? 'selected' : '') + '>Original Requis</option>' +
          '<option value="numerique" ' + (nature === 'numerique' ? 'selected' : '') + '>Fichier Numérique (Scan)</option>' +
          '<option value="aucun" ' + (nature === 'aucun' ? 'selected' : '') + '>Aucun</option>' +
        '</select>' +
      '</td>' +
      '<td style="padding: 10px 12px; text-align:center;">' +
        '<button type="button" class="btn btn-sm btn-delete-row-cycle" style="background:#FEE2E2; color:#B91C1C; border:none; border-radius:6px; width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;" title="Supprimer la ligne">' +
          '✕' +
        '</button>' +
      '</td>' +
    '</tr>';

    $('#bulk-rows-cycle-container').append(html);
    rowIndex++;
    refreshAllDropdowns();
  }

  if ($('#bulk-rows-cycle-container').length && $('#bulk-rows-cycle-container tr').length === 0) {
    createRow('', 1, 'photocopie_simple');
    createRow('', 1, 'original');
  }

  $('#select_target_cycle').on('change', function() {
    var cycleCode = $(this).val();
    var $nivSelect = $('#select_target_niveau');

    if (!cycleCode) {
      $nivSelect.empty()
        .append('<option value="">-- Sélectionnez d\'abord un cycle --</option>')
        .prop('disabled', true)
        .attr('readonly', 'readonly')
        .css({
          'background-color': '#F8FAFC',
          'color': '#94A3B8',
          'cursor': 'not-allowed'
        });
      alreadyAssignedCodes = [];
      refreshAllDropdowns();
      return;
    }

    $nivSelect.empty()
      .append('<option value="">Chargement des niveaux d\'études...</option>')
      .prop('disabled', true)
      .attr('readonly', 'readonly')
      .css({
        'background-color': '#F8FAFC',
        'color': '#64748B',
        'cursor': 'wait'
      });

    $.ajax({
      url: racine + 'piece_fournir_cycle/getByCycleApi',
      type: 'GET',
      data: { 
        cycle_code: cycleCode,
        niveau_code: ''
      },
      dataType: 'json',
      success: function(res) {
        alreadyAssignedCodes = res.assignedCodes || [];
        refreshAllDropdowns();

        $nivSelect.empty();
        $nivSelect.append('<option value="">-- Sélectionner un niveau d\'étude --</option>');
        if (res.niveaux && res.niveaux.length > 0) {
          res.niveaux.forEach(function(niv) {
            $nivSelect.append('<option value="' + niv.code_niveau + '">' + $('<div>').text(niv.libelle_niveau).html() + '</option>');
          });
        }

        $nivSelect
          .prop('disabled', false)
          .removeAttr('readonly')
          .css({
            'background-color': '#FFFFFF',
            'color': '#0F172A',
            'cursor': 'pointer'
          });

        if (alreadyAssignedCodes.length > 0) {
          if (window.toastr) toastr.info(alreadyAssignedCodes.length + ' pièce(s) sont déjà enregistrées pour ce cycle.');
        }
      },
      error: function() {
        $nivSelect.empty()
          .append('<option value="">-- Sélectionner un niveau d\'étude --</option>')
          .prop('disabled', false)
          .removeAttr('readonly')
          .css({
            'background-color': '#FFFFFF',
            'color': '#0F172A',
            'cursor': 'pointer'
          });
      }
    });
  });

  $('#select_target_niveau').on('change', function() {
    var cycle = $('#select_target_cycle').val();
    var niveau = $(this).val();
    if (cycle) {
      loadAssignedPiecesForCycle(cycle, niveau);
    }
  });

  $('#select_edit_cycle').on('change', function() {
    var cycleCode = $(this).val();
    var $niv = $('#select_edit_niveau');
    if (!cycleCode) {
      $niv.empty().append('<option value="">-- Sélectionner un niveau d\'étude --</option>');
      return;
    }
    $.ajax({
      url: racine + 'piece_fournir_cycle/getByCycleApi',
      type: 'GET',
      data: { cycle_code: cycleCode },
      dataType: 'json',
      success: function(res) {
        var cur = $niv.val();
        $niv.empty().append('<option value="">-- Sélectionner un niveau d\'étude --</option>');
        if (res.niveaux && res.niveaux.length > 0) {
          res.niveaux.forEach(function(n) {
            var sel = (n.code_niveau === cur) ? 'selected' : '';
            $niv.append('<option value="' + n.code_niveau + '" ' + sel + '>' + $('<div>').text(n.libelle_niveau).html() + '</option>');
          });
        }
      }
    });
  });

  $(document).on('change', '.sel-piece', function() {
    var changedVal = $(this).val();
    var thisSelect = $(this);
    if (!changedVal) {
      hideDuplicateWarning();
      refreshAllDropdowns();
      return;
    }

    var rawObj = pieceOptions.find(function(p) { return p.code === changedVal; });
    var pieceLibelle = rawObj ? rawObj.libelle : 'Document';

    if (alreadyAssignedCodes.indexOf(changedVal) !== -1) {
      thisSelect.val('');
      showDuplicateWarning('<strong>Pièce déjà existante :</strong> La pièce « <b>' + $('<div>').text(pieceLibelle).html() + '</b> » est déjà enregistrée pour ce cycle académique.');
      highlightDuplicateSelect(thisSelect);
      refreshAllDropdowns();
      return;
    }

    var count = 0;
    $('.sel-piece').each(function() {
      if ($(this).val() === changedVal) count++;
    });

    if (count > 1) {
      thisSelect.val('');
      showDuplicateWarning('<strong>Doublon détecté :</strong> Vous avez déjà sélectionné la pièce « <b>' + $('<div>').text(pieceLibelle).html() + '</b> » dans une autre ligne de ce dossier.');
      highlightDuplicateSelect(thisSelect);
      refreshAllDropdowns();
    } else {
      hideDuplicateWarning();
      refreshAllDropdowns();
    }
  });

  $('#btn-add-row-cycle').on('click', function() {
    createRow('', 1, 'photocopie_simple', 'obligatoire');
  });

  $(document).on('click', '.btn-delete-row-cycle', function() {
    if ($('#bulk-rows-cycle-container tr').length > 1) {
      $(this).closest('tr').remove();
      hideDuplicateWarning();
      refreshAllDropdowns();
    } else {
      if (window.toastr) toastr.info('Vous devez conserver au moins un document dans le dossier.');
    }
  });

  $('#form-bulk-piece-cycle').on('submit', function(e) {
    $('#select_target_niveau').prop('disabled', false);
    var cycle = $('#select_target_cycle').val();
    if (!cycle) {
      e.preventDefault();
      var msg = 'Veuillez sélectionner le cycle académique ciblé.';
      if (window.toastr) toastr.error(msg);
      showDuplicateWarning(msg);
      $('#select_target_cycle').focus();
      return false;
    }

    var niveau = $('#select_target_niveau').val();
    if (!niveau) {
      e.preventDefault();
      var msg = 'Veuillez sélectionner le niveau d\'étude.';
      if (window.toastr) toastr.error(msg);
      showDuplicateWarning(msg);
      $('#select_target_niveau').focus();
      return false;
    }

    var selectedPieces = [];
    var hasDuplicate = false;
    var duplicateName = '';
    var hasValid = false;

    $('.sel-piece').each(function() {
      var val = $.trim($(this).val());
      var rawObj = pieceOptions.find(function(p) { return p.code === val; });
      var pieceLibelle = rawObj ? rawObj.libelle : 'Document';

      if (val !== '') {
        hasValid = true;
        if (alreadyAssignedCodes.indexOf(val) !== -1 || selectedPieces.indexOf(val) !== -1) {
          hasDuplicate = true;
          duplicateName = pieceLibelle;
        }
        selectedPieces.push(val);
      }
    });

    if (!hasValid) {
      e.preventDefault();
      var msg = 'Veuillez sélectionner au moins une pièce à fournir dans le dossier.';
      if (window.toastr) toastr.error(msg);
      showDuplicateWarning(msg);
      return false;
    }

    if (hasDuplicate) {
      e.preventDefault();
      var msg = '<strong>Attention :</strong> La pièce « <b>' + $('<div>').text(duplicateName).html() + '</b> » est en doublon ou existe déjà pour ce cycle.';
      if (window.toastr) toastr.error(msg);
      showDuplicateWarning(msg);
      return false;
    }
  });
});
