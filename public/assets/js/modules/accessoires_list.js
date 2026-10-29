/**
 * Module JS: Gestion des Accessoires / Kits (Catalogue & Suivi des Distributions)
 * Externalisation stricte du code JS pour views/accessoires/list.php
 */
$(document).ready(function() {
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
});
