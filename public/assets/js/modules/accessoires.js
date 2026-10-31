/**
 * Module JS Unifié : Accessoires, Kits Scolaires & Pièces Administratives à Fournir
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // =========================================================================
  // 1. VUE : CATALOGUE DES ACCESSOIRES & SUIVI DES DISTRIBUTIONS (views/accessoires/list.php)
  // =========================================================================
  function initAccessoiresListModule() {
    if (!$('#table-accessoires').length) return;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
  }

  var racine = (typeof RACINE !== 'undefined') ? RACINE : '/geicg/';
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  var currentFilter = 'all';
  var tableDist = null;
  var tableAcc = null;

  var studentRowsMap = {};
  var activeStudentCode = null;

  function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // 1. Initialiser le DataTable des Distributions
  function initDistTable(filter) {
    if ($.fn.DataTable.isDataTable('#table-distributions')) {
      tableDist.destroy();
    }

    studentRowsMap = {};

    var distUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiDistributions') : (racine + 'accessoire/apiDistributions');

    tableDist = $('#table-distributions').DataTable({
      ajax: {
        url: distUrl,
        type: 'GET',
        data: function(d) {
          d.filter = filter;
          d.annee_code = $('#filter-annee').val();
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { data: 'matricule_etudiant', render: function(d) {
          return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</code>';
        }},
        { data: 'nom_complet', render: function(d) {
          return '<strong style="color:#0F172A; font-size:13.5px;">' + (d || '-') + '</strong>';
        }},
        { data: 'telephone_etudiant', defaultContent: '-', render: function(d) {
          return '<span style="color:#475569; font-weight:600; font-size:12.5px;">' + (d || '-') + '</span>';
        }},
        { data: 'libelle_classe', render: function(d) {
          return '<span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700; padding:4px 8px; border-radius:6px;">' + (d || 'Non assignée') + '</span>';
        }},
        { data: null, className: 'text-center', render: function(d, type, row) {
          var totKits = parseInt(row.total_kits) || 0;
          var totRetires = parseInt(row.total_retires) || 0;
          if (totKits > 0 && totRetires === totKits) {
            return '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Tout retiré (' + totRetires + '/' + totKits + ')</span>';
          } else if (totRetires > 0) {
            return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="pie-chart" style="width:14px;height:14px;"></i> Partiel (' + totRetires + '/' + totKits + ')</span>';
          } else {
            return '<span class="badge" style="background:#FFEDD5; color:#C2410C; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En attente (0/' + totKits + ')</span>';
          }
        }},
        { data: null, orderable: false, className: 'text-end', render: function(d, type, row) {
          return '<button type="button" class="btn btn-sm btn-open-student-kits-modal" data-ins="' + row.code_inscription + '" style="background:#1E3A5F; color:#FFFFFF; border:none; border-radius:6px; font-weight:700; padding:6px 14px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
                 '<i data-lucide="sliders" style="width:14px;height:14px;"></i> Détails & Retraits</button>';
        }}
      ],
      language: (typeof dtLang === 'object') ? dtLang : { url: dtLang },
      drawCallback: function() {
        var api = this.api();
        studentRowsMap = {};
        api.rows().data().each(function(row) {
          if (row.code_inscription) {
            studentRowsMap[row.code_inscription] = row;
          }
        });

        if (activeStudentCode && studentRowsMap[activeStudentCode] && $('#modal-details-kits').is(':visible')) {
          renderStudentKitsModal(studentRowsMap[activeStudentCode]);
        }

        if (window.lucide) lucide.createIcons();
      }
    });
  }

  // 2. Initialiser le DataTable du Catalogue
  function initCatalogueTable() {
    if (!$.fn.DataTable.isDataTable('#table-accessoires')) {
      var accUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiList') : (racine + 'accessoire/apiList');
      tableAcc = $('#table-accessoires').DataTable({
        ajax: {
          url: accUrl,
          type: 'GET',
          data: function(d) {
            d.annee_code = $('#filter-annee').val();
          }
        },
        processing: true,
        autoWidth: false,
        columns: [
          { data: null, width: '50px', render: function(d, type, row, meta) {
            return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
          }},
          { data: 'code_accessoire', width: '130px', render: function(d) {
            if (!d) return '-';
            return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
          }},
          { data: 'libelle_accessoire', render: function(d) {
            return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
          }},
          { data: 'type_filiere_cible', render: function(d) {
            if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
            if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
            return '<span class="badge" style="background:#F1F5F9; color:#475569; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Toutes Filières</span>';
          }},
          { data: 'libelle_niveau', render: function(d) {
            if (d) return '<span class="badge" style="background:#F0FDF4; color:#15803D; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">' + d + '</span>';
            return '<span class="badge" style="background:#F1F5F9; color:#64748B; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tous niveaux</span>';
          }},
          { data: 'statut_accessoire', width: '80px', className: 'text-center', render: function(d, type, row) {
            var isActif = (d === 'actif');
            var checkedAttr = isActif ? 'checked' : '';
            return '<div style="display:flex; justify-content:center; align-items:center;">' +
                   '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
                   '<input type="checkbox" class="toggle-statut-acc" data-id="' + row.id_accessoire + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                   '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                   '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                   '</span>' +
                   '</label>' +
                   '</div>';
          }},
          { data: null, width: '160px', orderable: false, render: function(d) {
            var safeLibelle = $('<div>').text(d.libelle_accessoire || '').html();
            var detailsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/details/' + (d.editId || d.id_accessoire)) : (racine + 'accessoire/details/' + (d.editId || d.id_accessoire));
            return '<button type="button" class="btn btn-sm btn-secondary btn-edit-acc-type" data-id="' + d.id_accessoire + '" data-libelle="' + safeLibelle + '" data-filiere-cible="' + (d.type_filiere_cible || 'TOUT') + '" data-niveau-code="' + (d.niveau_code || '') + '" data-statut="' + (d.statut_accessoire || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
                   '<a href="' + detailsUrl + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
          }, className: 'text-end' }
        ],
        language: (typeof dtLang === 'object') ? dtLang : { url: dtLang },
        drawCallback: function() { if (window.lucide) lucide.createIcons(); }
      });
    }
  }

  $('#filter-annee').on('change', function() {
    var val = $(this).val();
    window.location.href = racine + 'accessoire/list?annee_code=' + encodeURIComponent(val);
  });

  // Modal Type Accessoire : Ajouter
  $(document).on('click', '.btn-add-acc-type', function() {
    $('#form-accessoire-type')[0].reset();
    $('#acc_type_id').val('');
    $('#acc_type_filiere_cible').val('TOUT');
    $('#acc_type_niveau_code').val('');
    $('#modal-accessoire-type-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Type de Kit / Article');
    $('#modal-accessoire-type').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#acc_type_libelle').focus(); }, 100);
  });

  // Modal Type Accessoire : Modifier
  $(document).on('click', '.btn-edit-acc-type', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var filiereCible = $(this).data('filiere-cible');
    var niveauCode = $(this).data('niveau-code');
    var statut = $(this).data('statut');

    $('#acc_type_id').val(id);
    $('#acc_type_libelle').val(libelle);
    $('#acc_type_filiere_cible').val(filiereCible || 'TOUT');
    $('#acc_type_niveau_code').val(niveauCode || '');
    $('#acc_type_statut').val(statut || 'actif');
    $('#modal-accessoire-type-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Type de Kit');
    $('#modal-accessoire-type').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#acc_type_libelle').focus(); }, 100);
  });

  // Modal Type Accessoire : Fermer
  $('.btn-close-modal-acc-type').on('click', function() {
    $('#modal-accessoire-type').css('display', 'none');
  });
  $('#modal-accessoire-type').on('click', function(e) {
    if ($(e.target).is('#modal-accessoire-type')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX Type Accessoire
  $('#form-accessoire-type').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#acc_type_id').val();
    var url = (typeof GEICG !== 'undefined' && GEICG.url) ?
      GEICG.url(isEdit ? 'accessoire/edit' : 'accessoire/add') :
      (racine + (isEdit ? 'accessoire/edit' : 'accessoire/add'));
    var $btn = $('#btn-save-acc-type');

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
          $('#modal-accessoire-type').css('display', 'none');
          if (tableAcc) {
            tableAcc.ajax.reload(null, false);
          }
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

  // Bascule de statut pour le catalogue d'accessoires via Ajax
  $(document).on('change', '.toggle-statut-acc', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);
    var csrf = $('input[name="csrf_token"]').val() || '';
    var changerUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/changer') : (racine + 'accessoire/changer');

    $.ajax({
      url: changerUrl,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        id_accessoire: id,
        csrf_token: csrf
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          if (tableAcc) tableAcc.ajax.reload(null, false);
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

  // 3. Rafraîchir les statistiques KPI
  function reloadStats() {
    var anneeCode = $('#filter-annee').val();
    var statsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiStats?annee_code=' + encodeURIComponent(anneeCode)) : (racine + 'accessoire/apiStats?annee_code=' + encodeURIComponent(anneeCode));
    $.getJSON(statsUrl, function(res) {
      if (res.status === 1 && res.stats) {
        $('#kpi-total-kits').text(res.stats.total);
        $('#kpi-en-attente').text(res.stats.en_attente);
        $('#kpi-retires').text(res.stats.retire);
        $('#kpi-taux').text(res.stats.taux + '%');
      }
    });
  }

  // 4. Gestion des boutons switch / filtres
  $('.btn-switch-filter').on('click', function() {
    $('.btn-switch-filter').removeClass('active').css({
      'background': '#FFFFFF',
      'color': '#475569',
      'border-color': '#CBD5E1'
    });

    $(this).addClass('active');
    var filter = $(this).attr('data-filter');
    currentFilter = filter;

    if (filter === 'all') {
      $(this).css({ 'background': '#1E3A5F', 'color': '#FFFFFF', 'border-color': '#1E3A5F' });
      $('#section-distributions').show();
      $('#section-catalogue').hide();
      $('#table-dist-title').text("Suivi de Tous les Kits Souscrits");
      initDistTable('all');
    } else if (filter === 'en_attente') {
      $(this).css({ 'background': '#FFF7ED', 'color': '#EA580C', 'border-color': '#FED7AA' });
      $('#section-distributions').show();
      $('#section-catalogue').hide();
      $('#table-dist-title').text("Kits en Attente de Retrait par les Étudiants");
      initDistTable('en_attente');
    } else if (filter === 'retire') {
      $(this).css({ 'background': '#F0FDF4', 'color': '#15803D', 'border-color': '#BBF7D0' });
      $('#section-distributions').show();
      $('#section-catalogue').hide();
      $('#table-dist-title').text("Kits Déjà Retirés & Livrés aux Étudiants");
      initDistTable('retire');
    } else if (filter === 'catalogue') {
      $(this).css({ 'background': '#F8FAFC', 'color': '#0F172A', 'border-color': '#94A3B8' });
      $('#section-distributions').hide();
      $('#section-catalogue').show();
      initCatalogueTable();
    }
  });

  // 5. Modal de Détails des Kits Étudiant
  $(document).on('click', '.btn-open-student-kits-modal', function() {
    var insCode = $(this).attr('data-ins');
    var row = studentRowsMap[insCode];
    if (!row) return;
    activeStudentCode = insCode;
    renderStudentKitsModal(row);
    $('#modal-details-kits').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-details').on('click', function() {
    $('#modal-details-kits').css('display', 'none');
    activeStudentCode = null;
  });

  function renderStudentKitsModal(row) {
    $('#detail_student_name').html(escapeHtml(row.nom_complet) + ' <code style="font-weight:800; color:#1E3A5F; background:#EFF6FF; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(row.matricule_etudiant) + '</code>');
    $('#detail_student_sub').text('Classe : ' + (row.libelle_classe || 'Non assignée') + ' | Tél : ' + (row.telephone_etudiant || '-'));

    var totKits = parseInt(row.total_kits) || 0;
    var totRetires = parseInt(row.total_retires) || 0;
    var totEnAttente = parseInt(row.total_en_attente) || 0;

    var globalBadge = '';
    if (totKits > 0 && totRetires === totKits) {
      globalBadge = '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Tout retiré (' + totRetires + '/' + totKits + ')</span>';
    } else if (totRetires > 0) {
      globalBadge = '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="pie-chart" style="width:14px;height:14px;"></i> Partiel (' + totRetires + '/' + totKits + ')</span>';
    } else {
      globalBadge = '<span class="badge" style="background:#FFEDD5; color:#C2410C; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En attente (0/' + totKits + ')</span>';
    }
    $('#detail_student_global_badge').html(globalBadge);

    var tbodyHtml = '';
    if (row.kits_details) {
      var items = row.kits_details.split('|||');
      items.forEach(function(item) {
        var parts = item.split(':::');
        var kitId = parts[0];
        var kitName = parts[1] || 'Kit';
        var kitEtat = parts[2] || 'en_attente';
        var kitDate = parts[3] || '';
        var isRetire = (kitEtat === 'retire');

        var etatBadge = isRetire
          ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:800; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Retiré ' + (kitDate ? 'le ' + kitDate : '') + '</span>'
          : '<span class="badge" style="background:#FFEDD5; color:#C2410C; padding:4px 10px; border-radius:12px; font-weight:800; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:12px;height:12px;"></i> En attente</span>';

        var checked = isRetire ? 'checked' : '';
        var switchHtml = '<label class="switch" style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="Basculer le retrait">' +
                         '<input type="checkbox" class="toggle-retrait-checkbox" data-id="' + kitId + '" ' + checked + ' style="opacity:0; width:0; height:0;">' +
                         '<span class="slider round" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isRetire ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                         '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isRetire ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                         '</span>' +
                         '</label>';

        tbodyHtml += '<tr style="border-bottom: 1px solid #F1F5F9;">' +
                     '<td style="padding:12px;"><strong style="color:#1E3A5F; font-size:13px; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="package" style="width:14px;height:14px;"></i> ' + escapeHtml(kitName) + '</strong></td>' +
                     '<td style="padding:12px; text-align:center;">' + etatBadge + '</td>' +
                     '<td style="padding:12px; text-align:right;">' + switchHtml + '</td>' +
                     '</tr>';
      });
    } else {
      tbodyHtml = '<tr><td colspan="3" style="padding:16px; text-align:center; color:#94A3B8;">Aucun kit attribué</td></tr>';
    }
    $('#detail_kits_tbody').html(tbodyHtml);

    var bulkBtn = '';
    if (totEnAttente > 0) {
      bulkBtn = '<button type="button" class="btn btn-sm btn-toggle-all-kits" data-ins="' + row.code_inscription + '" data-target="retire" style="background:#15803D; color:#FFFFFF; border:none; border-radius:8px; font-weight:700; padding:8px 16px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
                '<i data-lucide="check-check" style="width:15px;height:15px;"></i> Tout marquer comme retiré</button>';
    } else {
      bulkBtn = '<button type="button" class="btn btn-sm btn-toggle-all-kits" data-ins="' + row.code_inscription + '" data-target="en_attente" style="background:#FFFFFF; color:#EA580C; border:1px solid #EA580C; border-radius:8px; font-weight:700; padding:8px 16px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
                '<i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i> Remettre tout en attente</button>';
    }
    $('#detail_bulk_action_container').html(bulkBtn);
    if (window.lucide) lucide.createIcons();
  }

  // 6. Bascule dynamique (Switch Toggle Ajax 1-clic)
  $(document).on('click', '.btn-quick-toggle', function() {
    var id = $(this).attr('data-id');
    toggleKitStatus(id);
  });

  $(document).on('change', '.toggle-retrait-checkbox', function() {
    var id = $(this).attr('data-id');
    toggleKitStatus(id);
  });

  $(document).on('click', '.btn-toggle-all-kits', function() {
    var insCode = $(this).attr('data-ins');
    var targetState = $(this).attr('data-target');
    var csrf = $('input[name="csrf_token"]').val() || '';
    var toggleAllUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/toggleStudentAllKits') : (racine + 'accessoire/toggleStudentAllKits');

    $.ajax({
      url: toggleAllUrl,
      type: 'POST',
      data: {
        inscription_code: insCode,
        target_etat: targetState,
        csrf_token: csrf
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' || res.status === 1) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut des kits de l\'étudiant mis à jour !', 'success', 'Distribution Kits');
          if (tableDist) tableDist.ajax.reload(null, false);
          reloadStats();
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de la mise à jour', 'danger', 'Erreur');
        }
      },
      error: function() {
        if (typeof showToast === 'function') showToast('Une erreur réseau est survenue', 'danger', 'Erreur');
      }
    });
  });

  function toggleKitStatus(id) {
    var toggleUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/toggleRetrait') : (racine + 'accessoire/toggleRetrait');
    $.ajax({
      url: toggleUrl,
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' || res.status === 1) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour !', 'success', 'Distribution Kit');
          tableDist.ajax.reload(null, false);
          reloadStats();
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur de mise à jour', 'danger', 'Erreur');
          tableDist.ajax.reload(null, false);
        }
      },
      error: function() {
        if (typeof showToast === 'function') showToast('Une erreur est survenue lors de la mise à jour', 'danger', 'Erreur');
        tableDist.ajax.reload(null, false);
      }
    });
  }

  // 6. Gestion du Modal d'Attribution Rapide
  $('#btn-open-attrib-modal').on('click', function() {
    $('#modal-attribuer-kit').css('display', 'flex');
    if ($.fn.select2) {
      $('#modal_etudiant_code').select2({
        dropdownParent: $('#modal-attribuer-kit'),
        placeholder: "-- Rechercher par nom, matricule ou tél --",
        allowClear: true,
        width: '100%'
      });
      $('#modal_accessoires_select').select2({
        dropdownParent: $('#modal-attribuer-kit'),
        placeholder: "-- Choisir un ou plusieurs kits --",
        allowClear: true,
        width: '100%'
      });
    }
    if (window.lucide) lucide.createIcons();
  });

  $('#modal_etudiant_code').on('change', function() {
    var etuCode = $(this).val();
    if (!etuCode) {
      $('#modal_student_kit_preview').slideUp(150);
      resetKitSelectOptions([]);
      return;
    }

    var getKitsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/getStudentKits?etudiant_code=' + encodeURIComponent(etuCode)) : (racine + 'accessoire/getStudentKits?etudiant_code=' + encodeURIComponent(etuCode));
    $.getJSON(getKitsUrl, function(res) {
      if (res.status === 1) {
        $('#modal_student_kit_preview').slideDown(200);
        var existingKits = res.existing_kits || [];
        var existingCodes = res.existing_codes || [];
        var count = existingKits.length;

        $('#preview_kit_count_badge').text(count + ' kit' + (count > 1 ? 's' : ''));

        if (count === 0) {
          $('#preview_existing_kits_list').html('<span style="font-size:12.5px; color:#64748B; font-style:italic;">Aucun kit n\'a encore été attribué à cet étudiant pour l\'année scolaire active.</span>');
          $('#preview_kit_alert_msg').hide();
          $('#btn_submit_attribuer').prop('disabled', false).css({ 'opacity': '1', 'cursor': 'pointer' });
        } else {
          var badgesHtml = '';
          existingKits.forEach(function(k) {
            var isRetire = (k.etat_retrait === 'retire');
            var bg = isRetire ? '#DCFCE7' : '#FFEDD5';
            var col = isRetire ? '#15803D' : '#C2410C';
            var iconName = isRetire ? 'check-circle' : 'clock';
            var statusLabel = isRetire 
              ? ('Retiré' + (k.date_retrait_formatee ? ' le ' + k.date_retrait_formatee : '')) 
              : 'En attente de retrait';

            badgesHtml += '<div style="background:' + bg + '; color:' + col + '; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">' +
                          '<i data-lucide="' + iconName + '" style="width:13px;height:13px;"></i> ' +
                          '<span>' + k.libelle_accessoire + '</span>' +
                          '<span style="font-size:11px; opacity:0.85; font-weight:600;">(' + statusLabel + ')</span>' +
                          '</div>';
          });
          $('#preview_existing_kits_list').html(badgesHtml);

          if (res.has_all) {
            $('#preview_kit_alert_msg').show()
              .css({ 'background': '#FEF2F2', 'color': '#991B1B', 'border': '1px solid #FECACA' })
              .html('<strong>Information :</strong> Cet étudiant a déjà reçu <u>la totalité des kits disponibles</u> pour cette année scolaire. Aucun kit supplémentaire ne peut être attribué.');
            $('#btn_submit_attribuer').prop('disabled', true).css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
          } else {
            $('#preview_kit_alert_msg').show()
              .css({ 'background': '#FFFBEB', 'color': '#B45309', 'border': '1px solid #FDE68A' })
              .html('<strong>Règle d\'unicité :</strong> Les kits déjà attribués ci-dessus sont verrouillés pour empêcher les doublons pour la même année.');
            $('#btn_submit_attribuer').prop('disabled', false).css({ 'opacity': '1', 'cursor': 'pointer' });
          }
        }

        resetKitSelectOptions(existingCodes);
        if (window.lucide) lucide.createIcons();
      }
    });
  });

  function resetKitSelectOptions(disabledCodes) {
    $('#modal_accessoires_select option').each(function() {
      var val = $(this).val();
      var baseText = $(this).attr('data-base-text') || $(this).text().replace(' (Déjà attribué cette année)', '');
      $(this).attr('data-base-text', baseText);

      if (disabledCodes.indexOf(val) !== -1) {
        $(this).prop('disabled', true).text(baseText + ' (Déjà attribué cette année)');
      } else {
        $(this).prop('disabled', false).text(baseText);
      }
    });

    var currentVals = $('#modal_accessoires_select').val() || [];
    var validVals = currentVals.filter(function(v) { return disabledCodes.indexOf(v) === -1; });
    $('#modal_accessoires_select').val(validVals);

    if ($.fn.select2) {
      $('#modal_accessoires_select').select2({
        dropdownParent: $('#modal-attribuer-kit'),
        placeholder: "-- Choisir un ou plusieurs kits --",
        allowClear: true,
        width: '100%'
      });
    }
  }

  $('.btn-close-modal').on('click', function() {
    $('#modal-attribuer-kit').hide();
    $('#modal_student_kit_preview').hide();
    if ($.fn.select2) {
      $('#modal_etudiant_code').val(null).trigger('change.select2');
      $('#modal_accessoires_select').val(null).trigger('change.select2');
    }
    resetKitSelectOptions([]);
  });

  $('#form-attribuer-kit').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' || res.status === 1) {
          if (typeof showToast === 'function') showToast(res.message || 'Kit(s) attribué(s) avec succès !', 'success', 'Attribution Kit');
          $('#modal-attribuer-kit').hide();
          $('#modal_student_kit_preview').hide();
          form[0].reset();
          if ($.fn.select2) {
            $('#modal_etudiant_code').val(null).trigger('change.select2');
            $('#modal_accessoires_select').val(null).trigger('change.select2');
          }
          resetKitSelectOptions([]);
          if (tableDist) tableDist.ajax.reload(null, false);
          reloadStats();
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'attribution', 'danger', 'Erreur');
        }
      },
      error: function() {
        if (typeof showToast === 'function') showToast('Erreur de communication avec le serveur', 'danger', 'Erreur');
      }
    });
  });

  // Initialisation au chargement
  initCatalogueTable();
  initDistTable('all');

  }

  // =========================================================================
  // 2. VUE : REGISTRE D'ÉMARGEMENT & BON DE REMISE A4 (views/accessoires/registre.php)
  // =========================================================================
  function initAccessoiresRegistreModule() {
    if (!$('#modal-bon-remise, #filter-statut').length) return;

  var table;
  var currentStudentData = null;
  var racine = (typeof RACINE !== 'undefined') ? RACINE : '/geicg/';

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  if ($.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  if ($.fn.select2) {
    $('#filter-annee, #filter-classe, #filter-statut').select2({ width: '100%' });
    $('#modal-select-etudiant, #modal-select-accessoires').select2({
      dropdownParent: $('#modal-attribuer-kit'),
      width: '100%'
    });
  }

  var distUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiDistributions') : (racine + 'accessoire/apiDistributions');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  function initTable() {
    table = $('#table-distributions').DataTable({
      ajax: {
        url: distUrl,
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
          d.filter = $('#filter-statut').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement des distributions:", error, thrown);
        },
        dataSrc: function(json) {
          return json.data || [];
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var rawName = (d.nom_complet ? d.nom_complet.trim() : '') || 
                          ((d.nom_etudiant || '') + ' ' + (d.prenom_etudiant || '')).trim() || 
                          'Étudiant non identifié';
            
            var parts = rawName.split(' ').filter(function(p) { return p.length > 0; });
            var initials = 'ET';
            if (parts.length >= 2) {
              initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            } else if (parts.length === 1 && parts[0].length >= 2) {
              initials = parts[0].substring(0, 2).toUpperCase();
            }

            var photoPath = d.photo_etudiant ? String(d.photo_etudiant).trim() : '';
            var initialsDiv = '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(30,58,95,0.15);">' + escapeHtml(initials) + '</div>';

            var avatarHtml = initialsDiv;
            if (photoPath !== '') {
              var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : (racine + photoPath.replace(/^\//, ''));
              avatarHtml = '<img src="' + photoUrl + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; flex-shrink: 0;" onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';">' +
                           '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';
            }

            var phone = (d.telephone_etudiant && d.telephone_etudiant !== '-') ? d.telephone_etudiant : null;
            var phoneHtml = phone ? '<div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Tél : ' + escapeHtml(phone) + '</div>' : '<div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Tél : -</div>';

            return '<div style="display: flex; align-items: center; gap: 12px; min-width: 180px;">' +
                     avatarHtml +
                     '<div style="min-width: 0;">' +
                       '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px; line-height: 1.3;">' + escapeHtml(rawName) + '</div>' +
                       phoneHtml +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            return '<div>' +
                     '<span class="badge bg-light text-dark font-monospace border" style="font-size: 11.5px; font-weight: 700; padding: 4px 8px;">' + escapeHtml(d.matricule_etudiant || '-') + '</span>' +
                     '<div style="font-weight: 700; color: #1E3A5F; font-size: 12px; margin-top: 3px;">' + escapeHtml(d.libelle_classe || '-') + '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            var items = d.items || [];
            if (items.length === 0) {
              return '<span class="text-muted" style="font-size: 12px;">Aucun article souscrit</span>';
            }

            var html = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
            items.forEach(function(item) {
              if (item.etat_retrait === 'retire') {
                html += '<span class="badge bg-success" style="font-size: 11px; font-weight: 700; padding: 4px 8px;" title="Remis le ' + (item.date_retrait_formatee || '-') + '">✓ ' + escapeHtml(item.libelle_accessoire) + '</span>';
              } else {
                html += '<span class="badge bg-warning text-dark" style="font-size: 11px; font-weight: 700; padding: 4px 8px;" title="En attente de remise">⏳ ' + escapeHtml(item.libelle_accessoire) + '</span>';
              }
            });
            html += '</div>';
            return html;
          }
        },
        {
          data: null,
          render: function(d) {
            var isComplet = (d.total_retires === d.total_kits && d.total_kits > 0);
            var badgeClass = isComplet ? 'bg-success' : (d.total_retires > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.total_retires + ' / ' + d.total_kits + ' remis' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage || 0;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 110px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 7px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
                       '<div style="height: 100%; width: ' + pct + '%; background: ' + barColor + '; border-radius: 4px;"></div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function() {
            return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                     '<button type="button" class="btn btn-sm btn-success btn-open-student-kit" style="font-weight: 700; border-radius: 8px; padding: 6px 11px; background: #15803D; border: none;" title="Gérer et émarger les fournitures de cet élève">' +
                       '<i data-lucide="package-check" style="width: 14px; height: 14px; margin-right: 4px;"></i> Émarger' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-primary btn-print-bon-row" title="Imprimer le Bon de Remise A4" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="printer" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate-all-kits" title="Tout marquer comme remis en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="check-check" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                   '</div>';
          }
        }
      ],
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      },
      language: (typeof dtLang === 'object') ? dtLang : { url: dtLang }
    });
  }

  initTable();

  // Filtrage interactif
  $('#filter-annee, #filter-classe, #filter-statut').on('change', function() {
    table.ajax.reload();
    refreshKpis();
  });

  $('.kpi-card-clickable').on('click', function() {
    var status = $(this).data('status');
    $('#filter-statut').val(status).trigger('change.select2');
    table.ajax.reload();
  });

  $('#btn-refresh, #btn-refresh-header').on('click', function() {
    table.ajax.reload();
    refreshKpis();
  });

  function refreshKpis() {
    var annee = $('#filter-annee').val();
    var statsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiStats') : (racine + 'accessoire/apiStats');
    $.ajax({
      url: statsUrl,
      type: 'GET',
      data: { annee_code: annee },
      dataType: 'json',
      success: function(res) {
        var s = res.stats || res || {};
        $('#kpi-total-kits').text(s.total || 0);
        $('#kpi-en-attente').text(s.en_attente || 0);
        $('#kpi-retires').text(s.retires || (s.retire || 0));
        $('#kpi-taux').text((s.taux_retrait || (s.taux || 0)) + '%');
      }
    });
  }

  // Ouvrir le modal d'émargement détaillé de l'étudiant
  $('#table-distributions').on('click', '.btn-open-student-kit', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openStudentKitModal(rowData);
  });

  // Ouvrir le Bon de remise depuis la ligne
  $('#table-distributions').on('click', '.btn-print-bon-row', function() {
    var rowData = table.row($(this).closest('tr')).data();
    loadAndOpenBonRemiseModal(rowData.code_inscription);
  });

  $('#btn-print-bon-modal').on('click', function() {
    if (currentStudentData) {
      loadAndOpenBonRemiseModal(currentStudentData.code_inscription);
    }
  });

  // Tout marquer comme remis pour l'étudiant
  $('#table-distributions').on('click', '.btn-quick-validate-all-kits', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllKitsForStudent(rowData.code_inscription);
  });

  $('#btn-tout-valider-kit-modal').on('click', function() {
    if (currentStudentData) {
      validateAllKitsForStudent(currentStudentData.code_inscription);
    }
  });

  // Fermeture des modals
  $('.btn-close-modal-kit').on('click', function() {
    $('#modal-emargement-student').hide();
  });
  $('.btn-close-modal-bon').on('click', function() {
    $('#modal-bon-remise').hide();
  });
  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-emargement-student')) $('#modal-emargement-student').hide();
    if ($(e.target).is('#modal-bon-remise')) $('#modal-bon-remise').hide();
    if ($(e.target).is('#modal-attribuer-kit')) $('#modal-attribuer-kit').hide();
  });

  function openStudentKitModal(d) {
    $('#modal-kit-student-name').text(d.nom_complet);
    $('#modal-kit-student-info').text('Matricule : ' + (d.matricule_etudiant || '-') + ' | Classe : ' + (d.libelle_classe || '-'));
    $('#modal-kit-completion-text').text(d.total_retires + ' / ' + d.total_kits + ' Kits remis (' + d.pourcentage + '%)');

    var html = '';
    var items = d.items || [];
    if (items.length > 0) {
      items.forEach(function(item) {
        var isRetire = (item.etat_retrait === 'retire');
        var borderCol = isRetire ? '#16A34A' : '#EA580C';
        var bgCol = isRetire ? '#F0FDF4' : '#FFF7ED';
        var btnClass = isRetire ? 'btn-outline-secondary' : 'btn-success';
        var btnText = isRetire ? 'Annuler la remise' : 'Marquer comme Remis';
        var iconName = isRetire ? 'rotate-ccw' : 'check';
        var dateText = item.date_retrait_formatee ? ('<span style="font-size: 11px; font-weight: 600; color: #15803D; margin-left: 6px;">(Remis le ' + item.date_retrait_formatee + ')</span>') : '<span style="font-size: 11px; font-weight: 600; color: #EA580C; margin-left: 6px;">(En attente de retrait)</span>';

        html += '<div style="background: ' + bgCol + '; border-left: 5px solid ' + borderCol + '; padding: 14px 18px; border-radius: 12px; border-top: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">' +
                  '<div>' +
                    '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_accessoire) + dateText + '</div>' +
                    '<div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Fourniture souscrite d\'inscription</div>' +
                  '</div>' +
                  '<div>' +
                    '<button type="button" class="btn btn-sm ' + btnClass + ' btn-toggle-item-modal" data-id="' + item.id_accessoire_inscription + '" style="font-weight: 700; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px;">' +
                      '<i data-lucide="' + iconName + '" style="width: 15px; height: 15px;"></i> ' + btnText +
                    '</button>' +
                  '</div>' +
                '</div>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucun article souscrit pour cet élève.</div>';
    }

    $('#modal-kit-items-list').html(html);
    $('#modal-emargement-student').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  }

  // Action toggle item unitaire dans le modal
  $(document).on('click', '.btn-toggle-item-modal', function() {
    var id = $(this).data('id');
    var $btn = $(this);
    $btn.prop('disabled', true);
    var toggleUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/toggleRetrait') : (racine + 'accessoire/toggleRetrait');

    $.ajax({
      url: toggleUrl,
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour');
          table.ajax.reload(function() {
            if (currentStudentData) {
              var updatedData = table.rows().data().toArray().find(function(r) {
                return r.code_inscription === currentStudentData.code_inscription;
              });
              if (updatedData) {
                currentStudentData = updatedData;
                openStudentKitModal(updatedData);
              }
            }
          }, false);
          refreshKpis();
        } else {
          $btn.prop('disabled', false);
          var msg = res.message || 'Erreur de mise à jour';
          if (typeof showToast === 'function') showToast(msg, 'error');
          else if (window.toastr) toastr.error(msg);
        }
      },
      error: function() {
        $btn.prop('disabled', false);
        var msg = 'Erreur lors de la mise à jour du statut.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Action Tout valider pour un étudiant
  function validateAllKitsForStudent(inscrCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les fournitures de cet élève comme REMISES / RETIRÉES ?")) return;

    var toggleStudentUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/toggleStudentAllKits') : (racine + 'accessoire/toggleStudentAllKits');

    $.ajax({
      url: toggleStudentUrl,
      type: 'POST',
      data: {
        inscription_code: inscrCode,
        target_etat: 'retire'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Tous les kits ont été validés avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          table.ajax.reload(null, false);
          refreshKpis();
          $('#modal-emargement-student').hide();
        } else {
          var msgErr = res.message || 'Erreur lors de la validation.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur lors de la connexion au serveur.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  }

  // Chargement et affichage du Bon de Remise A4
  function loadAndOpenBonRemiseModal(inscrCode) {
    var bonUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/getBonRemiseData') : (racine + 'accessoire/getBonRemiseData');
    $.ajax({
      url: bonUrl,
      type: 'GET',
      data: { inscription_code: inscrCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.student) {
          var s = res.student;
          $('#bon-student-name').text((s.nom_etudiant || '') + ' ' + (s.prenom_etudiant || ''));
          $('#bon-student-matricule').text(s.matricule_etudiant || '-');
          $('#bon-student-classe').text(s.libelle_classe || '-');
          $('#bon-student-phone').text(s.telephone_etudiant || '-');
          $('#bon-annee').text(s.libelle_annee ? ('Année Académique ' + s.libelle_annee) : 'GEICG');
          $('#bon-date-emission').text(res.date_emission || '-');
          $('#bon-agent-nom').text(res.agent_nom || 'Administration');

          if (s.nom_etablissement) $('#bon-etab-nom').text(s.nom_etablissement);
          if (s.adresse_etablissement) $('#bon-etab-adresse').text(s.adresse_etablissement || '');
          if (s.telephone_etablissement) $('#bon-etab-tel').text('Tél : ' + s.telephone_etablissement);
          if (s.logo_etablissement) $('#bon-etab-logo').attr('src', racine + s.logo_etablissement).show();

          $('#bon-count-remis').text(res.total_remis || 0);
          $('#bon-count-attente').text(res.total_en_attente || 0);

          var htmlRemis = '';
          if (res.kits_remis && res.kits_remis.length > 0) {
            res.kits_remis.forEach(function(k) {
              htmlRemis += '<tr>' +
                             '<td style="padding:8px;"><strong>' + escapeHtml(k.libelle_accessoire) + '</strong></td>' +
                             '<td style="padding:8px;" class="text-success fw-bold">✓ ' + (k.date_retrait_formatee || 'Remis') + '</td>' +
                             '<td style="padding:8px;"><span class="badge bg-success">Remis & Réceptionné</span></td>' +
                           '</tr>';
            });
          } else {
            htmlRemis = '<tr><td colspan="3" class="text-muted text-center" style="padding:10px;">Aucun article remis pour le moment.</td></tr>';
          }
          $('#bon-table-remis').html(htmlRemis);

          var htmlAtt = '';
          if (res.kits_en_attente && res.kits_en_attente.length > 0) {
            res.kits_en_attente.forEach(function(k) {
              htmlAtt += '<tr>' +
                           '<td style="padding:8px;"><strong>' + escapeHtml(k.libelle_accessoire) + '</strong></td>' +
                           '<td style="padding:8px;">' + (k.date_attribution_formatee || '-') + '</td>' +
                           '<td style="padding:8px;"><span class="badge bg-warning text-dark">⏳ En attente de retrait</span></td>' +
                         '</tr>';
            });
          } else {
            htmlAtt = '<tr><td colspan="3" class="text-success text-center fw-bold" style="padding:10px;">🎉 Complètement remis ! Aucun article en attente.</td></tr>';
          }
          $('#bon-table-attente').html(htmlAtt);

          $('#modal-bon-remise').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          var err = res.message || 'Erreur lors de la génération du bon de remise.';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
        }
      },
      error: function() {
        var msg = 'Erreur de connexion au serveur.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  }

  $('#btn-trigger-print-bon').on('click', function() {
    window.print();
  });

  // Modal Attribution
  $('#btn-open-attrib-modal').on('click', function() {
    $('#modal-attribuer-kit').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-attrib').on('click', function() {
    $('#modal-attribuer-kit').hide();
  });

  $('#form-attribuer-kit').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var attribUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/attribuerKit') : (racine + 'accessoire/attribuerKit');

    $.ajax({
      url: attribUrl,
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Attribution effectuée avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          $('#modal-attribuer-kit').hide();
          table.ajax.reload();
          refreshKpis();
        } else {
          var msgErr = res.message || 'Erreur lors de l\'attribution.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur lors de l\'enregistrement.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  });

  }

  
  // --- Fonctions globales de gestion des Pièces à Fournir (Ajout unitaire / Lot) ---
window.batchRowIndex = 2;

window.switchPieceTab = function(tab) {
  const btnSingle = document.getElementById('tab-btn-single');
  const btnBatch = document.getElementById('tab-btn-batch');
  const paneSingle = document.getElementById('pane-single');
  const paneBatch = document.getElementById('pane-batch');

  if (!btnSingle || !btnBatch || !paneSingle || !paneBatch) return;

  if (tab === 'single') {
    btnSingle.style.background = '#1E3A5F';
    btnSingle.style.color = '#FFFFFF';
    btnSingle.style.border = 'none';

    btnBatch.style.background = '#FFFFFF';
    btnBatch.style.color = '#64748B';
    btnBatch.style.border = '1px solid #CBD5E1';

    paneSingle.style.display = 'block';
    paneBatch.style.display = 'none';
  } else {
    btnBatch.style.background = '#1E3A5F';
    btnBatch.style.color = '#FFFFFF';
    btnBatch.style.border = 'none';

    btnSingle.style.background = '#FFFFFF';
    btnSingle.style.color = '#64748B';
    btnSingle.style.border = '1px solid #CBD5E1';

    paneSingle.style.display = 'none';
    paneBatch.style.display = 'block';
  }
  if (window.lucide) lucide.createIcons();
}

window.addBatchRow = function() {
  const container = document.getElementById('batch-rows-container');
  if (!container) return;

  const rowCount = container.querySelectorAll('.batch-row').length + 1;
  const tr = document.createElement('tr');
  tr.className = 'batch-row';
  tr.style.borderBottom = '1px solid #F1F5F9';
  tr.innerHTML = `
    <td style="padding: 12px 14px; font-weight: 700; color: #64748B;">${rowCount}</td>
    <td style="padding: 12px 14px;">
      <input type="text" 
             name="pieces[${batchRowIndex}][libelle]" 
             placeholder="Ex: Certificat médical d'aptitude" 
             class="form-control" 
             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
    </td>
    <td style="padding: 12px 14px;">
      <input type="text" 
             name="pieces[${batchRowIndex}][description]" 
             placeholder="Ex: Délivré par un médecin agréé" 
             class="form-control" 
             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%;">
    </td>
    <td style="padding: 12px 14px; text-align: center;">
      <button type="button" onclick="removeBatchRow(this)" class="btn btn-sm btn-danger" style="background: #FEE2E2; color: #DC2626; border: none; border-radius: 6px; padding: 6px 10px; cursor: pointer;">
        <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
      </button>
    </td>
  `;
  container.appendChild(tr);
  batchRowIndex++;
  if (window.lucide) lucide.createIcons();
}

window.removeBatchRow = function(btn) {
  const row = btn.closest('tr');
  if (row) {
    row.remove();
    const rows = document.querySelectorAll('#batch-rows-container .batch-row');
    rows.forEach((r, idx) => {
      const tdNum = r.querySelector('td');
      if (tdNum) tdNum.textContent = idx + 1;
    });
  }
}

  // =========================================================================
  // 3. VUE : RÉPERTOIRE DES PIÈCES & DOCUMENTS À FOURNIR (views/pieces_fournir/list.php)
  // =========================================================================
  function initPiecesFournirListModule() {
    if (!$('#table-pieces-fournir, #pieces-fournir-list-config').length) return;

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  var configEl = document.getElementById('pieces-fournir-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var canManagePieces = Boolean(config.canManagePieces);

  var table = $('#table-pieces-fournir').DataTable({
    ajax: racine + 'piece_fournir/apiList',
    processing: true,
    autoWidth: false,
    order: [[0, 'asc']],
    columns: [
      { data: 'code_piece_fournir', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; font-size:12.5px;">' + (d || '-') + '</code>';
      } },
      { data: 'libelle_piece', className: 'col-libelle', render: function(d) {
        return '<div style="font-weight:700; color:#0F172A; font-size:13.5px; display:flex; align-items:flex-start; gap:8px; white-space:normal; overflow-wrap:break-word; line-height:1.4;"><i data-lucide="file-text" style="width:16px;height:16px;color:#3B82F6;flex-shrink:0;margin-top:2px;"></i> <span style="white-space:normal; overflow-wrap:break-word;">' + (d || '-') + '</span></div>';
      } },
      { data: 'description_piece', className: 'col-desc', render: function(d) {
        return '<span style="color:#64748B; font-size:12.5px; white-space:normal; overflow-wrap:break-word; line-height:1.4;">' + (d || '<em style="color:#94A3B8;">Aucune instruction particulière</em>') + '</span>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        if (canManagePieces) {
          return '<a href="' + racine + 'piece_fournir/edition/' + (d.editId || d.id_piece_fournir) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>' +
                 '<a href="' + racine + 'piece_fournir/supprimer/' + (d.editId || d.id_piece_fournir) + '" onclick="return confirm(\'Voulez-vous vraiment supprimer cette pièce du répertoire ?\')" class="btn btn-sm btn-danger" style="background:#EF4444; color:#fff; border:none; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>';
        }
        return '<span style="color:#94A3B8; font-size:12px; font-style:italic;">Consultation</span>';
      } }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  }

  // =========================================================================
  // 4. VUE : AJOUT / ÉDITION DE PIÈCES ADMINISTRATIVES (views/pieces_fournir/edit.php)
  // =========================================================================
  function initPiecesFournirEditModule() {
    if (!$('#tab-btn-single, #tab-btn-batch, #pane-single, #batch-rows-container').length) return;
    if (window.lucide) lucide.createIcons();
  }

  // =========================================================================
  // 5. VUE : DOSSIER DE PIÈCES À FOURNIR PAR CYCLE - LISTE (views/piece_fournir_cycle/list.php)
  // =========================================================================
  function initPieceFournirCycleListModule() {
    if (!$('#table-pieces-cycle, #piece-fournir-cycle-list-config').length) return;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-cycle, #filter-niveau').select2({ width: '100%' });
  }

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  var configEl = document.getElementById('piece-fournir-cycle-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var csrfToken = config.csrfToken || '';

  var table = $('#table-pieces-cycle').DataTable({
    ajax: {
      url: racine + 'piece_fournir_cycle/apiList',
      type: 'GET',
      data: function(d) {
        d.cycle_code = $('#filter-cycle').val();
        d.niveau_code = $('#filter-niveau').val();
      }
    },
    processing: true,
    autoWidth: false,
    order: [[0, 'desc']],
    columns: [
      { data: 'code_piece_cycle', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</code>';
      } },
      { data: 'slug_cycle', render: function(d, type, row) {
        var cycleText = d || row.libelle_cycle || 'Tous cycles';
        return '<span style="font-weight:700; color:#334155; font-size:13px;">' + cycleText + '</span>';
      } },
      { data: 'libelle_niveau', render: function(d) {
        if (d) {
          return '<span class="badge" style="background:#E0F2FE; color:#0369A1; font-weight:800; font-size:12px; padding:5px 10px; border-radius:6px; border:1px solid #BAE6FD; display:inline-block; position:static;">' + d + '</span>';
        }
        return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-weight:700; font-size:12px; padding:5px 10px; border-radius:6px; display:inline-block; position:static;">Tous niveaux</span>';
      } },
      { data: 'libelle_piece', className: 'col-piece', render: function(d, type, row) {
        var desc = row.description_piece ? '<div style="font-size:11.5px; color:#64748B; margin-top:3px; line-height:1.4; white-space:normal; overflow-wrap:break-word;">' + row.description_piece + '</div>' : '';
        return '<div style="font-weight:700; color:#0F172A; white-space:normal; overflow-wrap:break-word; line-height:1.4;">' + (d || '-') + '</div>' + desc;
      } },
      { data: 'nombre_exemplaires', className: 'text-center', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; background:#F1F5F9; padding:4px 10px; border-radius:6px;">' + (d || 1) + ' ex.</span>';
      } },
      { data: 'nature_document', render: function(d) {
        var natureLabels = {
          'photocopie_simple': '<span style="color:#334155; font-weight:600;"><i data-lucide="copy" style="width:13px;height:13px;display:inline;"></i> Photocopie Simple</span>',
          'photocopie_legalisee': '<span style="color:#1E3A5F; font-weight:700;"><i data-lucide="stamp" style="width:13px;height:13px;display:inline;"></i> Photocopie Légalisée</span>',
          'original': '<span style="color:#B45309; font-weight:700;"><i data-lucide="award" style="width:13px;height:13px;display:inline;"></i> Original Requis</span>',
          'numerique': '<span style="color:#2563EB; font-weight:600;"><i data-lucide="upload" style="width:13px;height:13px;display:inline;"></i> Fichier Numérique (PDF)</span>',
          'aucun': '<span style="color:#64748B; font-weight:600;"><i data-lucide="minus-circle" style="width:13px;height:13px;display:inline;"></i> Aucun</span>'
        };
        return natureLabels[d] || (d || 'Photocopie simple');
      } },
      { data: 'statut_piece_cycle', className: 'text-center', render: function(d, type, row) {
        var val = d || 'actif';
        var isActif = val === 'actif';
        var bg = isActif ? '#DCFCE7' : '#FEE2E2';
        var col = isActif ? '#15803D' : '#B91C1C';
        var border = isActif ? '#86EFAC' : '#FCA5A5';

        return '<select class="select-statut-piece-cycle" data-id="' + row.id_piece_cycle + '" style="background:' + bg + '; color:' + col + '; border:1px solid ' + border + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="actif" ' + (isActif ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Actif</option>' +
               '<option value="inactif" ' + (!isActif ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Inactif</option>' +
               '</select>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'piece_fournir_cycle/edition/' + (d.editId || d.id_piece_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>' +
               '<a href="' + racine + 'piece_fournir_cycle/supprimer/' + (d.editId || d.id_piece_cycle) + '" onclick="return confirm(\'Voulez-vous vraiment retirer cette pièce du dossier du cycle ?\')" class="btn btn-sm btn-danger" style="background:#EF4444; color:#fff; border:none; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>';
      } }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-cycle, #filter-niveau').on('change', function() {
    table.ajax.reload();
  });

  $(document).on('change', '.select-statut-piece-cycle', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: racine + 'piece_fournir_cycle/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: newStatut,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau ou serveur');
      }
    });
  });

  }

  // =========================================================================
  // 6. VUE : CONFIGURATION DU DOSSIER DE PIÈCES PAR CYCLE - ÉDITION (views/piece_fournir_cycle/edit.php)
  // =========================================================================
  function initPieceFournirCycleEditModule() {
    if (!$('#piece-fournir-cycle-edit-config, #form-piece-fournir-cycle, #table-cycle-pieces-body').length) return;

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

  }

  $(document).ready(function() {
    initAccessoiresListModule();
    initAccessoiresRegistreModule();
    initPiecesFournirListModule();
    initPiecesFournirEditModule();
    initPieceFournirCycleListModule();
    initPieceFournirCycleEditModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);