/**
 * Module JS Unifié : Filières, Cycles, Niveaux & Matières
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // =========================================================================
  // 1. VUE : GESTION DES FILIÈRES, PARCOURS PIVOTS & CYCLES (views/filieres/list.php)
  // =========================================================================
  function initFilieresListModule() {
    if (!$('#filieres-list-config, #table-filieres').length) return;

  var configEl = document.getElementById('filieres-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  if (window.lucide) lucide.createIcons();

  // Gestion des Onglets
  $('.tab-btn').on('click', function() {
    $('.tab-btn').css({ 'color': '#64748B', 'border-bottom-color': 'transparent' }).removeClass('active');
    $(this).css({ 'color': '#1E3A5F', 'border-bottom-color': '#1E3A5F' }).addClass('active');
    $('.tab-content').hide();
    var targetTab = $(this).data('tab');
    $('#' + targetTab).show();
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  });

  // Détection automatique de l'onglet actif selon l'URL ou le paramètre ?tab=
  var urlParams = new URLSearchParams(window.location.search);
  var requestedTab = urlParams.get('tab') || window.location.hash.replace('#', '');
  if (requestedTab && $('#' + requestedTab).length) {
    $('.tab-btn[data-tab="' + requestedTab + '"]').click();
  } else if (window.location.pathname.indexOf('filiere_cycle') !== -1) {
    $('.tab-btn[data-tab="tab-assignations"]').click();
  } else if (window.location.pathname.indexOf('cycle') !== -1) {
    $('.tab-btn[data-tab="tab-cycles"]').click();
  } else if (window.location.pathname.indexOf('filiere') !== -1) {
    $('.tab-btn[data-tab="tab-filieres"]').click();
  }

  // Table 1: Assignations Parcours Pivots
  var tableAssign = $('#table-assignations').DataTable({
    ajax: racine + 'filiere_cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_filiere_cycle', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_cycle', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'libelle_filiere', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'type_filiere', render: function(d) {
        if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
        if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
        return '<span style="color:#94A3B8; font-style:italic; font-size:11px;">Non spécifié</span>';
      }},
      { data: 'libelle_niveau', render: function(d) { 
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">Tous / Global</span>';
        return '<span style="font-weight:700; color:#334155;">' + d + '</span>';
      } },
      { data: 'statut_filiere_cycle', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-fc" data-id="' + row.id_filiere_cycle + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-assign" data-id="' + d.id_filiere_cycle + '" data-cycle="' + (d.cycle_code || '') + '" data-filiere="' + (d.filiere_code || '') + '" data-type-filiere="' + (d.type_filiere || 'AUTO') + '" data-niveau="' + (d.niveau_code || '') + '" data-statut="' + (d.statut_filiere_cycle || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'filiere_cycle/details/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Filières
  var tableFilieres = $('#table-filieres-catalogue').DataTable({
    ajax: racine + 'filiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere', defaultContent: '-' },
      { data: 'code_filiere', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_filiere', render: function(d) { 
        return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
      } },
      { data: 'slug_filiere', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px; letter-spacing:0.3px;">' + d + '</span>';
      } },
      { data: 'type_filiere', render: function(d) {
        if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
        if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
        return '<span style="color:#94A3B8; font-size:12px; font-style:italic;">Non spécifié</span>';
      } },
      { data: 'description_filiere', render: function(d) { return d || '-'; } },
      { data: 'statut_filiere', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-fil" data-id="' + row.id_filiere + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_filiere || '').html();
        var safeSlug = $('<div>').text(d.slug_filiere || '').html();
        var safeDesc = $('<div>').text(d.description_filiere || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-filiere" data-id="' + d.id_filiere + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-type="' + (d.type_filiere || '') + '" data-desc="' + safeDesc + '" data-statut="' + (d.statut_filiere || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'filiere/details/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 3: Cycles d'Études
  var tableCycles = $('#table-cycles-list').DataTable({
    ajax: racine + 'cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cycle', defaultContent: '-' },
      { data: 'code_cycle', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_cycle', render: function(d) { 
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>';
      } },
      { data: 'slug_cycle', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px; letter-spacing:0.3px;">' + d + '</span>';
      } },
      { data: 'description_cycle', render: function(d) { return d || '-'; } },
      { data: 'statut_cycle', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-cyc" data-id="' + row.id_cycle + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_cycle || '').html();
        var safeSlug = $('<div>').text(d.slug_cycle || '').html();
        var safeDesc = $('<div>').text(d.description_cycle || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-cycle" data-id="' + d.id_cycle + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-desc="' + safeDesc + '" data-statut="' + (d.statut_cycle || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'cycle/details/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 4: Niveaux d'Études
  var tableNiveaux = $('#table-niveaux-list').DataTable({
    ajax: racine + 'niveau/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_niveau', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_niveau', render: function(d) { 
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>';
      } },
      { data: 'slug_niveau', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px;">' + d + '</span>';
      } },
      { data: 'statut_niveau', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-niveau" data-id="' + row.id_niveau + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_niveau || '').html();
        var safeSlug = $('<div>').text(d.slug_niveau || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-niveau" data-id="' + d.id_niveau + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-statut="' + (d.statut_niveau || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'niveau/details/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // ==========================================
  // RECHARGEMENT DYNAMIQUE ET SELECT2 DES ASSIGNATIONS
  // ==========================================
  var filieresTypeMap = {};

  function updateAssignTypeDisplay(typeVal) {
    var labelText = '';
    if (typeVal === 'INDUSTRIELLE') labelText = 'Filière Industrielle';
    else if (typeVal === 'TERTIAIRE') labelText = 'Filière Tertiaire';
    else if (typeVal && typeVal !== 'AUTO') labelText = typeVal;

    $('#assign_type_filiere_display').val(labelText);
    $('#assign_type_filiere').val(typeVal || 'AUTO');
  }

  function initAssignSelect2() {
    if ($.fn.select2) {
      $('#assign_cycle_code, #assign_filiere_code, #assign_niveau_code, #assign_statut').select2({
        width: '100%',
        dropdownParent: $('#modal-assignation')
      });
    }
  }

  function reloadAssignSelects(selectedCycle, selectedFiliere, selectedType) {
    var cVal = selectedCycle || $('#assign_cycle_code').val();
    var fVal = selectedFiliere || $('#assign_filiere_code').val();

    $.getJSON(racine + 'cycle/apiList?statut=actif', function(res) {
      if (res && res.data) {
        var opts = '<option value="">-- Sélectionner un cycle --</option>';
        res.data.forEach(function(c) {
          opts += '<option value="' + c.code_cycle + '">' + (c.libelle_cycle || c.slug_cycle || c.code_cycle) + '</option>';
        });
        $('#assign_cycle_code').html(opts);
        if (cVal) $('#assign_cycle_code').val(cVal);
        if ($.fn.select2) $('#assign_cycle_code').trigger('change.select2');
      }
    });

    $.getJSON(racine + 'filiere/apiList?statut=actif', function(res) {
      if (res && res.data) {
        var opts = '<option value="">-- Sélectionner une filière --</option>';
        filieresTypeMap = {};
        res.data.forEach(function(f) {
          filieresTypeMap[f.code_filiere] = f.type_filiere || '';
          opts += '<option value="' + f.code_filiere + '" data-type="' + (f.type_filiere || '') + '">' + (f.libelle_filiere || f.slug_filiere || f.code_filiere) + '</option>';
        });
        $('#assign_filiere_code').html(opts);
        if (fVal) {
          $('#assign_filiere_code').val(fVal);
          var typeToUse = selectedType || filieresTypeMap[fVal] || '';
          updateAssignTypeDisplay(typeToUse);
        }
        if ($.fn.select2) $('#assign_filiere_code').trigger('change.select2');
      }
    });
  }

  $(document).on('change', '#assign_filiere_code', function() {
    var code = $(this).val();
    var type = filieresTypeMap[code] || $(this).find('option:selected').data('type') || '';
    updateAssignTypeDisplay(type);
  });

  // ==========================================
  // HANDLERS : MODAL 1 (ASSIGNATIONS)
  // ==========================================
  $('.btn-add-assignation').on('click', function() {
    $('#form-assignation')[0].reset();
    $('#assign_id').val('');
    $('#modal-assignation-title').html('<i data-lucide="git-merge" style="width: 18px; height: 18px;"></i> Nouveau Parcours Pivot (Cycle - Filière - Niveau)');
    updateAssignTypeDisplay('AUTO');
    reloadAssignSelects();
    $('#modal-assignation').css('display', 'flex');
    initAssignSelect2();
    if ($.fn.select2) {
      $('#assign_cycle_code').val('').trigger('change.select2');
      $('#assign_filiere_code').val('').trigger('change.select2');
      $('#assign_niveau_code').val('').trigger('change.select2');
      $('#assign_statut').val('actif').trigger('change.select2');
    }
    if (window.lucide) lucide.createIcons();
  });

  $(document).on('click', '.btn-edit-assign', function() {
    var id = $(this).data('id');
    var cycle = $(this).data('cycle');
    var filiere = $(this).data('filiere');
    var typeFiliere = $(this).data('type-filiere');
    var niveau = $(this).data('niveau');
    var statut = $(this).data('statut');

    $('#assign_id').val(id);
    $('#assign_cycle_code').val(cycle);
    $('#assign_filiere_code').val(filiere);
    updateAssignTypeDisplay(typeFiliere || filieresTypeMap[filiere] || 'AUTO');
    $('#assign_niveau_code').val(niveau || '');
    $('#assign_statut').val(statut || 'actif');
    $('#modal-assignation-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Parcours Pivot');
    reloadAssignSelects(cycle, filiere, typeFiliere);
    $('#modal-assignation').css('display', 'flex');
    initAssignSelect2();
    if ($.fn.select2) {
      $('#assign_cycle_code').val(cycle).trigger('change.select2');
      $('#assign_filiere_code').val(filiere).trigger('change.select2');
      $('#assign_niveau_code').val(niveau || '').trigger('change.select2');
      $('#assign_statut').val(statut || 'actif').trigger('change.select2');
    }
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-assign').on('click', function() {
    $('#modal-assignation').css('display', 'none');
  });
  $('#modal-assignation').on('click', function(e) {
    if ($(e.target).is('#modal-assignation')) $(this).css('display', 'none');
  });

  $('#form-assignation').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#assign_id').val();
    var url = isEdit ? (racine + 'filiere_cycle/edit') : (racine + 'filiere_cycle/add');
    var $btn = $('#btn-save-assign');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Assignation enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Assignation enregistrée avec succès');
          $('#modal-assignation').css('display', 'none');
          tableAssign.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // ==========================================
  // HANDLERS : MODAL 2 (CATALOGUE FILIÈRES)
  // ==========================================
  $('.btn-add-filiere').on('click', function() {
    $('#form-filiere')[0].reset();
    $('#filiere_id').val('');
    $('#modal-filiere-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter une Filière');
    $('#modal-filiere').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#filiere_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-filiere', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var type = $(this).data('type');
    var desc = $(this).data('desc');
    var statut = $(this).data('statut');

    $('#filiere_id').val(id);
    $('#filiere_libelle').val(libelle);
    $('#filiere_slug').val(slug);
    $('#filiere_type').val(type);
    $('#filiere_desc').val(desc);
    $('#filiere_statut').val(statut || 'actif');
    $('#modal-filiere-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Filière');
    $('#modal-filiere').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#filiere_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-filiere').on('click', function() {
    $('#modal-filiere').css('display', 'none');
  });
  $('#modal-filiere').on('click', function(e) {
    if ($(e.target).is('#modal-filiere')) $(this).css('display', 'none');
  });

  $('#form-filiere').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#filiere_id').val();
    var url = isEdit ? (racine + 'filiere/edit') : (racine + 'filiere/add');
    var $btn = $('#btn-save-filiere');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Filière enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Filière enregistrée avec succès');
          $('#modal-filiere').css('display', 'none');
          tableFilieres.ajax.reload(null, false);
          reloadAssignSelects();
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // ==========================================
  // HANDLERS : MODAL 3 (CYCLES D'ÉTUDES)
  // ==========================================
  $('.btn-add-cycle').on('click', function() {
    $('#form-cycle')[0].reset();
    $('#cycle_id').val('');
    $('#modal-cycle-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Cycle d\'Études');
    $('#modal-cycle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#cycle_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-cycle', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var desc = $(this).data('desc');
    var statut = $(this).data('statut');

    $('#cycle_id').val(id);
    $('#cycle_libelle').val(libelle);
    $('#cycle_slug').val(slug);
    $('#cycle_desc').val(desc);
    $('#cycle_statut').val(statut || 'actif');
    $('#modal-cycle-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Cycle d\'Études');
    $('#modal-cycle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#cycle_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-cycle').on('click', function() {
    $('#modal-cycle').css('display', 'none');
  });
  $('#modal-cycle').on('click', function(e) {
    if ($(e.target).is('#modal-cycle')) $(this).css('display', 'none');
  });

  $('#form-cycle').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#cycle_id').val();
    var url = isEdit ? (racine + 'cycle/edit') : (racine + 'cycle/add');
    var $btn = $('#btn-save-cycle');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Cycle enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Cycle enregistré avec succès');
          $('#modal-cycle').css('display', 'none');
          tableCycles.ajax.reload(null, false);
          reloadAssignSelects();
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // ==========================================
  // HANDLERS : MODAL 4 (NIVEAUX D'ÉTUDES)
  // ==========================================
  $(document).on('click', '.btn-add-niveau', function(e) {
    e.preventDefault();
    $('#form-niveau')[0].reset();
    $('#niveau_id').val('');
    $('#modal-niveau-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau d\'Études');
    $('#modal-niveau').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#niveau_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-niveau', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var statut = $(this).data('statut');

    $('#niveau_id').val(id);
    $('#niveau_libelle').val(libelle);
    $('#niveau_slug').val(slug);
    $('#niveau_statut').val(statut || 'actif');
    $('#modal-niveau-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Niveau d\'Études');
    $('#modal-niveau').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#niveau_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-niveau').on('click', function() {
    $('#modal-niveau').css('display', 'none');
  });

  $('#modal-niveau').on('click', function(e) {
    if ($(e.target).is('#modal-niveau')) $(this).css('display', 'none');
  });

  $('#form-niveau').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#niveau_id').val();
    var url = isEdit ? (racine + 'niveau/edit') : (racine + 'niveau/add');
    var $btn = $('#btn-save-niveau');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Niveau d\'études enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Niveau d\'études enregistré avec succès');
          $('#modal-niveau').css('display', 'none');
          tableNiveaux.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Bascule statut AJAX pour les 4 tables
  function bindAjaxToggle(selector, url, tableRef) {
    $(document).on('change', selector, function() {
      var id = $(this).data('id');
      var isChecked = $(this).is(':checked');
      var $input = $(this);
      $.ajax({
        url: url,
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { id: id, csrf_token: csrfToken },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.showToast) showToast(res.message || 'Statut mis à jour avec succès', 'success');
            else if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
            tableRef.ajax.reload(null, false);
          } else {
            if (window.showToast) showToast(res.message || 'Erreur lors du changement de statut', 'error');
            else if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
            $input.prop('checked', !isChecked);
          }
        },
        error: function() {
          if (window.showToast) showToast('Erreur réseau', 'error');
          else if (window.toastr) toastr.error('Erreur réseau');
          $input.prop('checked', !isChecked);
        }
      });
    });
  }

  bindAjaxToggle('.toggle-statut-fc', racine + 'filiere_cycle/changer', tableAssign);
  bindAjaxToggle('.toggle-statut-fil', racine + 'filiere/changer', tableFilieres);
  bindAjaxToggle('.toggle-statut-cyc', racine + 'cycle/changer', tableCycles);
  bindAjaxToggle('.toggle-statut-niveau', racine + 'niveau/changer', tableNiveaux);

  }

  // =========================================================================
  // 2. VUE : HUB FILIÈRES & CYCLES (views/filiere_cycles/hub.php)
  // =========================================================================
  function initFiliereCyclesHubModule() {
    if (!$('#table-filieres-catalogue, #table-cycles-list').length) return;

  var racine = window.RACINE || '/';
  if (window.lucide) lucide.createIcons();

  // Gestion des Onglets
  $('.tab-btn').on('click', function() {
    $('.tab-btn').css({ 'color': '#64748B', 'border-bottom-color': 'transparent' }).removeClass('active');
    $(this).css({ 'color': '#1E3A5F', 'border-bottom-color': '#1E3A5F' }).addClass('active');
    $('.tab-content').hide();
    var targetTab = $(this).data('tab');
    $('#' + targetTab).show();
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  });

  // Table 1: Assignations
  var tableAssign = $('#table-assignations').DataTable({
    ajax: racine + 'filiere_cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere_cycle', defaultContent: '-', width: '50px' },
      { data: 'code_filiere_cycle', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_cycle', width: '180px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non défini') + '</span>';
      }},
      { data: 'libelle_filiere', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>';
      }},
      { data: 'statut_filiere_cycle', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'filiere_cycle/edition/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'filiere_cycle/details/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Filières
  var tableFilieres = $('#table-filieres-catalogue').DataTable({
    ajax: racine + 'filiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere', defaultContent: '-', width: '50px' },
      { data: 'code_filiere', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_filiere', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'slug_filiere', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px; letter-spacing:0.3px;">' + d + '</span>';
      }},
      { data: 'description_filiere', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d || '-';
      }},
      { data: 'statut_filiere', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'filiere/edition/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'filiere/details/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 3: Cycles d'Études
  var tableCycles = $('#table-cycles-list').DataTable({
    ajax: racine + 'cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cycle', defaultContent: '-', width: '50px' },
      { data: 'code_cycle', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_cycle', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>';
      }},
      { data: 'slug_cycle', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px; letter-spacing:0.3px;">' + d + '</span>';
      }},
      { data: 'description_cycle', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d || '-';
      }},
      { data: 'statut_cycle', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'cycle/edition/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'cycle/details/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  }

  // =========================================================================
  // 3. VUE : CATALOGUE DES NIVEAUX D'ÉTUDES (views/niveaux/list.php)
  // =========================================================================
  function initNiveauxListModule() {
    if (!$('#table-niveaux-catalogue, #niveaux-list-config').length) return;

  var configEl = document.getElementById('niveaux-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  if (window.lucide) lucide.createIcons();

  var tableNiveauxCat = $('#table-niveaux-catalogue').DataTable({
    ajax: racine + 'niveau/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_niveau', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_niveau', render: function(d, type, row) { 
        var useSlug = row.use_slug_niveau;
        if (useSlug && row.slug_niveau) {
          return '<span style="font-weight:700; color:#0F172A;">' + row.slug_niveau + '</span>' +
                 ' <span style="font-size:12px; color:#64748B; margin-left:6px;">(' + (d || '-') + ')</span>';
        }
        var html = '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
        if (row.slug_niveau) {
          html += ' <span class="badge" style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:2px 6px; border-radius:4px; font-weight:700; font-size:11px; margin-left:6px;">' + row.slug_niveau + '</span>';
        }
        return html;
      } },
      { data: 'statut_niveau', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-niveau" data-id="' + row.id_niveau + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_niveau || '').html();
        var safeSlug = $('<div>').text(d.slug_niveau || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-niveau" data-id="' + d.id_niveau + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-statut="' + (d.statut_niveau || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'niveau/details/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Modal : Ajouter
  $('.btn-add-niveau').on('click', function() {
    $('#form-niveau')[0].reset();
    $('#niveau_id').val('');
    $('#modal-niveau-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau');
    $('#modal-niveau').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#niveau_libelle').focus(); }, 100);
  });

  // Modal : Modifier
  $(document).on('click', '.btn-edit-niveau', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var statut = $(this).data('statut');

    $('#niveau_id').val(id);
    $('#niveau_libelle').val(libelle);
    $('#niveau_slug').val(slug);
    $('#niveau_statut').val(statut || 'actif');
    $('#modal-niveau-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Niveau');
    $('#modal-niveau').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#niveau_libelle').focus(); }, 100);
  });

  // Modal : Fermer
  $('.btn-close-modal-niveau').on('click', function() {
    $('#modal-niveau').css('display', 'none');
  });
  $('#modal-niveau').on('click', function(e) {
    if ($(e.target).is('#modal-niveau')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX (Ajout & Modification)
  $('#form-niveau').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#niveau_id').val();
    var url = isEdit ? (racine + 'niveau/edit') : (racine + 'niveau/add');
    var $btn = $('#btn-save-niveau');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Opération réussie', 'success');
          else if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $('#modal-niveau').css('display', 'none');
          tableNiveauxCat.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Bascule statut AJAX
  $(document).off('change', '.toggle-statut-niveau').on('change', '.toggle-statut-niveau', function() {
    var $checkbox = $(this);
    var id = $checkbox.data('id');
    $checkbox.prop('disabled', true);
    $.ajax({
      url: racine + 'niveau/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { id: id, id_niveau: id, csrf_token: csrfToken },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Statut mis à jour', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour');
          tableNiveauxCat.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur');
          $checkbox.prop('checked', !$checkbox.prop('checked')).prop('disabled', false);
        }
      },
      error: function() {
        if (window.showToast) showToast('Erreur serveur', 'error');
        else if (window.toastr) toastr.error('Erreur serveur');
        $checkbox.prop('checked', !$checkbox.prop('checked')).prop('disabled', false);
      }
    });
  });

  }

  // =========================================================================
  // 4. VUE : LISTE & GESTION DES MATIÈRES (views/matieres/list.php)
  // =========================================================================
  function initMatieresListModule() {
    if (!$('#table-matieres, #matieres-list-config').length) return;

  var configEl = document.getElementById('matieres-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  if (window.lucide) lucide.createIcons();

  var table = $('#table-matieres').DataTable({
    ajax: racine + 'matiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_matiere', width: '120px', render: function(d, type) { 
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; 
      }},
      { data: 'libelle_matiere', render: function(d, type) { 
        if (type !== 'display') return d || '';
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>'; 
      }},
      { data: 'statut_matiere', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-matiere" data-id="' + row.id_matiere + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_matiere || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-matiere" data-id="' + d.id_matiere + '" data-libelle="' + safeLibelle + '" data-statut="' + (d.statut_matiere || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'matiere/details/' + (d.editId || d.id_matiere) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Modal : Ajouter
  $('.btn-add-matiere').on('click', function() {
    $('#form-matiere')[0].reset();
    $('#matiere_id').val('');
    $('#modal-matiere-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter une Matière');
    $('#modal-matiere').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#matiere_libelle').focus(); }, 100);
  });

  // Modal : Modifier
  $(document).on('click', '.btn-edit-matiere', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var statut = $(this).data('statut');

    $('#matiere_id').val(id);
    $('#matiere_libelle').val(libelle);
    $('#matiere_statut').val(statut || 'actif');
    $('#modal-matiere-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Matière');
    $('#modal-matiere').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#matiere_libelle').focus(); }, 100);
  });

  // Modal : Fermer
  $('.btn-close-modal-matiere').on('click', function() {
    $('#modal-matiere').css('display', 'none');
  });
  $('#modal-matiere').on('click', function(e) {
    if ($(e.target).is('#modal-matiere')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission Formulaire AJAX
  $('#form-matiere').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#matiere_id').val();
    var url = isEdit ? (racine + 'matiere/edit') : (racine + 'matiere/add');
    var $btn = $('#btn-save-matiere');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Opération réussie', 'success');
          else if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $('#modal-matiere').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
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

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-matiere', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'matiere/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        id_matiere: id,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors du changement de statut', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (typeof showToast === 'function') showToast('Erreur réseau', 'error');
        else if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  }

  $(document).ready(function() {
    initFilieresListModule();
    initFiliereCyclesHubModule();
    initNiveauxListModule();
    initMatieresListModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);