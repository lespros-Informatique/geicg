/**
 * Module JS : Liste & Gestion des Classes + Reconduction N-1 -> N (views/classes/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('classes-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var canEditClasse = !!config.canEditClasse;
  var csrfToken = config.csrfToken || '';
  var currentAnneeCode = config.currentAnneeCode || '';

  var table = $('#table-classes').DataTable({
    ajax: {
      url: racine + 'classe/apiList',
      data: function(d) {
        d.annee_code = $('#filter-annee-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_classe', width: '110px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_classe', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_filiere', render: function(d, type, row) {
        if (type !== 'display') return d || row.filiere_code || '';
        return '<span style="color:#1E3A5F; font-weight:600;">' + (d || row.filiere_code || '-') + '</span>';
      }},
      { data: 'libelle_niveau', render: function(d, type, row) {
        if (type !== 'display') return d || row.niveau_code || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || row.niveau_code || '-') + '</span>';
      }},
      { data: 'capacite_max_classe', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#475569;">' + (d ? d + ' places' : '-') + '</span>';
      }},
      { data: 'statut_classe', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        if (!canEditClasse) {
          return '<span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background-color:' + (isActif ? '#DCFCE7' : '#F1F5F9') + '; color:' + (isActif ? '#15803D' : '#64748B') + ';">' + (isActif ? 'Actif' : 'Inactif') + '</span>';
        }
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-classe" data-id="' + row.id_classe + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        var safeLib = d.libelle_classe ? $('<div>').text(d.libelle_classe).html() : '';
        var actionsHtml = '';
        if (canEditClasse) {
          actionsHtml += '<button type="button" class="btn btn-sm btn-secondary btn-edit-classe" data-id="' + (d.id_classe) + '" data-libelle="' + safeLib + '" data-filiere="' + (d.filiere_code || '') + '" data-niveau="' + (d.niveau_code || '') + '" data-annee="' + (d.annee_code || '') + '" data-capacite="' + (d.capacite_max_classe || '35') + '" data-statut="' + (d.statut_classe || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';
        }
        actionsHtml += '<a href="' + racine + 'classe/details/' + (d.editId || d.id_classe) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
        return actionsHtml;
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee-classe').on('change', function() {
    table.ajax.reload();
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-classe', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'classe/changer',
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

  // GESTION DU TOGGLE STATUT DANS LE MODAL
  function updateStatutClasseUI(isActif) {
    if (isActif) {
      $('#statut_classe_slider').css('background-color', '#15803D');
      $('#statut_classe_knob').css('left', '22px');
      $('#statut_classe_text').css('color', '#15803D').text('Actif');
    } else {
      $('#statut_classe_slider').css('background-color', '#CBD5E1');
      $('#statut_classe_knob').css('left', '3px');
      $('#statut_classe_text').css('color', '#64748B').text('Inactif');
    }
  }

  $('#classe_statut').on('change', function() {
    updateStatutClasseUI($(this).is(':checked'));
  });

  // INITIALISATION SELECT2 SUR LES DROPDOWNS DU MODAL
  if ($.fn.select2) {
    $('#classe_filiere, #classe_niveau, #classe_annee').select2({
      dropdownParent: $('#modal-classe'),
      width: '100%',
      placeholder: '-- Choisir --',
      allowClear: false
    });
  }

  // GÉNÉRATION AUTOMATIQUE DU LIBELLÉ DE LA CLASSE
  function autoGenerateLibelleClasse() {
    var fNom = $('#classe_filiere option:selected').data('nom') || '';
    var nNom = $('#classe_niveau option:selected').data('nom') || '';
    if (fNom && nNom) {
      $('#classe_libelle').val(fNom + ' - ' + nNom);
    } else if (fNom) {
      $('#classe_libelle').val(fNom);
    } else if (nNom) {
      $('#classe_libelle').val(nNom);
    }
  }

  $('#classe_filiere, #classe_niveau').on('change select2:select', function() {
    var curVal = $('#classe_libelle').val().trim();
    if ($('#classe_id').val() === '' || curVal === '') {
      autoGenerateLibelleClasse();
    }
  });

  // GESTION MODALE AJOUT / ÉDITION CLASSE
  $(document).on('click', '.btn-add-classe', function(e) {
    e.preventDefault();
    $('#form-classe')[0].reset();
    $('#classe_id').val('');
    $('#classe_capacite').val('35');
    $('#classe_statut').prop('checked', true);
    updateStatutClasseUI(true);
    if ($.fn.select2) {
      $('#classe_filiere, #classe_niveau').val('').trigger('change.select2');
      var selAnnee = $('#filter-annee-classe').val() || currentAnneeCode;
      if (selAnnee) {
        $('#classe_annee').val(selAnnee).trigger('change.select2');
      }
    }
    $('#sel_parcours_pivot').val('');
    $('#classe_cycle').val('');
    $('#modal-classe-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe');
    $('#modal-classe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#sel_parcours_pivot').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-classe', function(e) {
    e.preventDefault();
    $('#form-classe')[0].reset();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var filiere = $(this).data('filiere');
    var niveau = $(this).data('niveau');
    var annee = $(this).data('annee');
    var capacite = $(this).data('capacite');
    var statut = $(this).data('statut');

    $('#classe_id').val(id);
    $('#classe_libelle').val(libelle);
    $('#classe_filiere').val(filiere);
    $('#classe_niveau').val(niveau);
    $('#classe_annee').val(annee);
    if ($.fn.select2) {
      $('#classe_filiere').trigger('change.select2');
      $('#classe_niveau').trigger('change.select2');
      $('#classe_annee').trigger('change.select2');
    }

    var $matchOpt = $('#sel_parcours_pivot option').filter(function() {
      return $(this).data('filiere') == filiere && $(this).data('niveau') == niveau;
    });
    if ($matchOpt.length > 0) {
      $('#sel_parcours_pivot').val($matchOpt.val());
      $('#classe_cycle').val($matchOpt.data('cycle') || '');
    } else {
      $('#sel_parcours_pivot').val('');
      $('#classe_cycle').val('');
    }

    $('#classe_capacite').val(capacite || '35');

    var isActif = (statut === 'actif');
    $('#classe_statut').prop('checked', isActif);
    updateStatutClasseUI(isActif);

    $('#modal-classe-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Classe');
    $('#modal-classe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#classe_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-classe').on('click', function() {
    $('#modal-classe').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-classe')) {
      $('#modal-classe').hide();
    }
  });

  $('#form-classe').on('submit', function(e) {
    e.preventDefault();
    var id = $('#classe_id').val();
    var url = racine + (id ? 'classe/edit' : 'classe/add');
    var $btn = $('#btn-submit-classe');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    var formData = $(this).serializeArray();
    var hasStatut = false;
    for (var i = 0; i < formData.length; i++) {
      if (formData[i].name === 'statut_classe') {
        hasStatut = true;
        break;
      }
    }
    if (!hasStatut) {
      var isChecked = $('#classe_statut').length ? $('#classe_statut').is(':checked') : true;
      formData.push({ name: 'statut_classe', value: isChecked ? 'actif' : 'inactif' });
    }

    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Classe enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Classe enregistrée avec succès');
          $('#modal-classe').hide();
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // SELECTION PARCOURSPIVOT DANS LE MODAL
  $('#sel_parcours_pivot').on('change', function() {
    var $opt = $(this).find('option:selected');
    var filiereCode = $opt.data('filiere') || '';
    var niveauCode = $opt.data('niveau') || '';
    var cycleCode = $opt.data('cycle-code') || '';
    var cycleName = $opt.data('cycle-slug') || $opt.data('cycle') || '';

    $('#classe_filiere').val(filiereCode);
    if ($.fn.select2) $('#classe_filiere').trigger('change.select2');

    $('#classe_niveau').val(niveauCode);
    if ($.fn.select2) $('#classe_niveau').trigger('change.select2');

    $('#classe_cycle_code').val(cycleCode);
    $('#classe_cycle').val(cycleName);
    autoGenerateLibelleClasse();
  });

  // DUPLICATION RAPIDE DE CLASSE (CRÉATION DE SECTION B, C...)
  $(document).on('click', '.btn-duplicate-classe', function(e) {
    e.preventDefault();
    $('#form-classe')[0].reset();
    var libelle = $(this).data('libelle') || '';
    var filiere = $(this).data('filiere') || '';
    var niveau = $(this).data('niveau') || '';
    var annee = $(this).data('annee') || '';
    var capacite = $(this).data('capacite') || '35';

    $('#classe_id').val('');
    $('#classe_libelle').val(libelle ? libelle + ' B' : '');
    $('#classe_filiere').val(filiere);
    $('#classe_niveau').val(niveau);
    $('#classe_annee').val(annee);
    if ($.fn.select2) {
      $('#classe_filiere').trigger('change.select2');
      $('#classe_niveau').trigger('change.select2');
      $('#classe_annee').trigger('change.select2');
    }
    $('#classe_capacite').val(capacite);
    $('#classe_statut').prop('checked', true);
    updateStatutClasseUI(true);

    $('#modal-classe-title').html('<i data-lucide="copy" style="width: 18px; height: 18px;"></i> Dupliquer la Classe / Nouvelle Section');
    $('#modal-classe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#classe_libelle').focus(); }, 100);
  });

  // GESTION MODALE RECONDUCTION DES CLASSES N-1 -> N
  $(document).on('click', '.btn-reconduire-classes', function(e) {
    e.preventDefault();
    $('#form-reconduire-classes')[0].reset();
    var currentYear = $('#filter-annee-classe').val() || currentAnneeCode;
    if (currentYear) {
      $('#reconduire_annee_cible').val(currentYear);
    }
    $('#modal-reconduire-classes').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-reconduire').on('click', function() {
    $('#modal-reconduire-classes').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-reconduire-classes')) {
      $('#modal-reconduire-classes').hide();
    }
  });

  $('#form-reconduire-classes').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-reconduire');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Traitement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: racine + 'classe/reconduire',
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Reconduction effectuée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Reconduction effectuée avec succès');
          $('#modal-reconduire-classes').hide();
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de la reconduction', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de la reconduction');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de la reconduction';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });
});

