/**
 * Module JS Unifié : Dépenses & Catégories de Dépenses (Listing, Décaissements, Catégorisation)
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // =========================================================================
  // 1. VUE : LISTE DES DÉPENSES & DÉCAISSEMENTS (views/depenses/list.php)
  // =========================================================================
  function initDepensesListModule() {
    if (!$('#table-depenses, #depenses-list-config').length) return;

  if (window.lucide) lucide.createIcons();
  
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  var configEl = document.getElementById('depenses-list-config');
  var config = {};
  if (configEl) {
    try {
      config = JSON.parse(configEl.textContent);
    } catch (e) {
      console.error('Erreur parsing depenses-list-config:', e);
    }
  }

  var canValidate = Boolean(config.canValidate);
  var csrfToken = config.csrfToken || '';

  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
    $('#filter-type-depense').select2({ width: '100%' });
    $('#depense_modal_type').select2({
      dropdownParent: $('#modal-depense'),
      width: '100%',
      placeholder: '-- Sélectionnez une catégorie --'
    });
  }

  function showNotify(msg, type) {
    type = type || 'info';
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else if (window.Swal) {
      var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
      });
      Toast.fire({
        icon: type,
        title: msg
      });
    } else {
      alert(msg);
    }
  }

  function reloadStats() {
    var typeCode = $('#filter-type-depense').val();
    var dateDebut = $('#filter-date-debut').val();
    var dateFin = $('#filter-date-fin').val();

    var params = [];
    if (typeCode) params.push('type_depense_code=' + encodeURIComponent(typeCode));
    if (dateDebut) params.push('date_debut=' + encodeURIComponent(dateDebut));
    if (dateFin) params.push('date_fin=' + encodeURIComponent(dateFin));

    var url = racine + 'depense/apiStats' + (params.length ? '?' + params.join('&') : '');

    $.getJSON(url, function(res) {
      if (res.status === 1 && res.stats) {
        $('#kpi-montant-approuve').text(Number(res.stats.montant_approuve || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-count-approuve').text((res.stats.count_approuve || 0) + ' dépense(s)');
        $('#kpi-montant-attente').text(Number(res.stats.montant_en_attente || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-count-attente').text((res.stats.count_en_attente || 0) + ' dépense(s)');
        $('#kpi-montant-annule').text(Number(res.stats.montant_annule || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-count-annule').text((res.stats.count_annule || 0) + ' dépense(s)');
      }
    });
  }

  var table = $('#table-depenses').DataTable({
    ajax: {
      url: racine + 'depense/apiList',
      type: 'GET',
      data: function(d) {
        d.type_depense_code = $('#filter-type-depense').val();
        d.date_debut = $('#filter-date-debut').val();
        d.date_fin = $('#filter-date-fin').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '40px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_depense', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + d + '</code>';
      }},
      { data: 'libelle_type_depense', render: function(d) {
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #DBEAFE; font-weight:700; padding:5px 10px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="tag" style="width:12px;height:12px;color:#2563EB;"></i> ' + (d || 'Général') + '</span>';
      }},
      { data: 'montant_depense', className: 'text-end', render: function(d) {
        var num = parseFloat(d) || 0;
        return '<strong style="color:#991B1B; font-size:14px;">' + num.toLocaleString('fr-FR') + ' FCFA</strong>';
      }},
      { data: 'periode_depense', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          return '<span style="color:#475569; font-weight:600; font-size:12.5px;"><i data-lucide="calendar" style="width:13px;height:13px;display:inline;margin-right:4px;"></i>' + dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + '</span>';
        }
        return d;
      }},
      { data: null, render: function(d) {
        var auteur = d.auteur_nom_complet ? d.auteur_nom_complet.trim() : '-';
        var confirmateur = d.confirmateur_nom_complet ? d.confirmateur_nom_complet.trim() : '';
        var html = '<div style="font-size:12px; color:#0F172A; font-weight:700;"><i data-lucide="user" style="width:12px;height:12px;color:#64748B;"></i> ' + auteur + '</div>';
        if (confirmateur && d.statut_depense !== 'en_attente') {
          var label = (d.statut_depense === 'approuve') ? 'Validé par' : 'Annulé par';
          html += '<div style="font-size:10.5px; color:#64748B; font-weight:600;">' + label + ' : ' + confirmateur + '</div>';
        }
        return html;
      }},
      { data: 'statut_depense', width: '130px', className: 'text-center', render: function(d) {
        if (d === 'approuve') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:6px 12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:13px;height:13px;"></i> Approuvé</span>';
        } else if (d === 'annule') {
          return '<span class="badge" style="background:#FEE2E2; color:#991B1B; font-weight:800; padding:6px 12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="x-circle" style="width:13px;height:13px;"></i> Annulé</span>';
        } else {
          return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-weight:800; padding:6px 12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:13px;height:13px;"></i> En attente</span>';
        }
      }},
      { data: null, orderable: false, className: 'text-center no-print', render: function(d) {
        var descVal = $('<div>').text(d.description_depense || d.libelle_depense || '').html();
        var benVal = $('<div>').text(d.beneficiaire || '').html();
        var rawDate = d.periode_depense ? d.periode_depense.split(' ')[0] : '';

        // 1. BOUTON ÉDITER (Icône uniquement) - Disponible UNIQUEMENT si la dépense est En attente
        var btnEdit = '';
        if (d.statut_depense === 'en_attente') {
          btnEdit = '<button type="button" class="btn btn-sm btn-secondary btn-edit-depense" ' +
                    'data-id="' + d.id_depense + '" ' +
                    'data-type="' + (d.type_depense_code || '') + '" ' +
                    'data-description="' + descVal + '" ' +
                    'data-montant="' + (d.montant_depense || '') + '" ' +
                    'data-mode="' + (d.mode_reglement || 'espece') + '" ' +
                    'data-beneficiaire="' + benVal + '" ' +
                    'data-date="' + rawDate + '" ' +
                    'data-piece="' + (d.piece_justificative || '') + '" ' +
                    'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; background:#64748B; border:none; color:#FFF; box-shadow:0 2px 4px rgba(100,116,139,0.25);" ' +
                    'title="Modifier la dépense (En attente)">' +
                    '<i data-lucide="edit" style="width:15px;height:15px;"></i></button>';
        } else {
          var labelStatut = (d.statut_depense === 'approuve') ? 'approuvée (statut définitif)' : 'annulée';
          btnEdit = '<button type="button" class="btn btn-sm btn-outline-secondary" disabled ' +
                    'style="width:32px; height:32px; padding:0; border-radius:8px; opacity:0.4; cursor:not-allowed; display:inline-flex; align-items:center; justify-content:center; border-color:#CBD5E1; color:#94A3B8;" ' +
                    'title="Impossible de modifier une dépense ' + labelStatut + '">' +
                    '<i data-lucide="lock" style="width:15px;height:15px;"></i></button>';
        }

        // 2. BOUTONS D'APPROBATION ET D'ANNULATION (Icônes uniquement)
        var btnStatus = '';
        if (d.statut_depense === 'en_attente') {
          if (canValidate) {
            btnStatus = '<button type="button" onclick="changerStatutDepense(' + d.id_depense + ', \'approuve\')" ' +
                        'class="btn btn-sm btn-success" ' +
                        'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; background:#16A34A; border:none; color:#FFF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(22,163,74,0.25);" ' +
                        'title="Approuver définitivement cette dépense">' +
                        '<i data-lucide="check" style="width:15px;height:15px;"></i></button> ' +
                        '<button type="button" onclick="changerStatutDepense(' + d.id_depense + ', \'annule\')" ' +
                        'class="btn btn-sm btn-danger" ' +
                        'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; background:#DC2626; border:none; color:#FFF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(220,38,38,0.25);" ' +
                        'title="Annuler cette dépense">' +
                        '<i data-lucide="x" style="width:15px;height:15px;"></i></button>';
          }
        } else if (d.statut_depense === 'approuve') {
          btnStatus = '<button type="button" class="btn btn-sm btn-success" disabled ' +
                      'style="width:32px; height:32px; padding:0; border-radius:8px; opacity:0.85; background:#16A34A; border:none; color:#FFF; cursor:not-allowed; display:inline-flex; align-items:center; justify-content:center;" ' +
                      'title="Dépense approuvée indéfiniment (Statut définitif non modifiable)">' +
                      '<i data-lucide="check-circle" style="width:15px;height:15px;"></i></button>';
        } else if (d.statut_depense === 'annule') {
          if (canValidate) {
            btnStatus = '<button type="button" onclick="changerStatutDepense(' + d.id_depense + ', \'en_attente\')" ' +
                        'class="btn btn-sm btn-outline-warning" ' +
                        'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:700; color:#B45309; border:1px solid #FCD34D; background:#FFFBEB; display:inline-flex; align-items:center; justify-content:center;" ' +
                        'title="Remettre en attente de validation">' +
                        '<i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i></button>';
          }
        }

        // 3. BOUTON DÉTAILS
        var btnDetails = '<a href="' + racine + 'depense/details/' + (d.editId || d.id_depense) + '" ' +
                         'class="btn btn-sm btn-primary" ' +
                         'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; background:#1E3A5F; border-color:#1E3A5F; color:#FFF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(30,58,95,0.2);" ' +
                         'title="Consulter la fiche détails de la dépense">' +
                         '<i data-lucide="eye" style="width:15px;height:15px;"></i></a>';

        // 4. BOUTON PIÈCE JUSTIFICATIVE
        var btnDoc = '';
        if (d.piece_justificative) {
          btnDoc = '<a href="' + racine + 'public/' + d.piece_justificative + '" target="_blank" ' +
                   'class="btn btn-sm btn-outline-info" ' +
                   'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; color:#0284C7; border:1px solid #BAE6FD; background:#F0F9FF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(2,132,199,0.15);" ' +
                   'title="Consulter / Télécharger la pièce justificative (Max 3 Mo)">' +
                   '<i data-lucide="paperclip" style="width:15px;height:15px;"></i></a>';
        }

        return '<div style="display:flex; align-items:center; justify-content:center; gap:6px; flex-wrap:nowrap;">' +
               btnEdit +
               btnStatus +
               btnDetails +
               btnDoc +
               '</div>';
      }}
    ],
    language: { url: dtLang },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Ouvrir la modale pour un NOUVEAU décaissement
  $('.btn-add-depense').on('click', function() {
    $('#depense_modal_id').val('');
    $('#form-depense-modal')[0].reset();
    if ($.fn.select2) {
      $('#depense_modal_type').val('').trigger('change');
    }
    $('#depense_modal_piece').val('');
    $('#piece-justificative-preview').html('');
    var today = new Date().toISOString().split('T')[0];
    $('#depense_modal_date').val(today);
    $('#modal-depense-title').html('<i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Nouveau Décaissement / Dépense');
    $('#modal-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#depense_modal_description').focus(); }, 100);
  });

  // Ouvrir la modale pour MODIFIER une dépense
  $(document).on('click', '.btn-edit-depense', function() {
    var id = $(this).data('id');
    var type = $(this).data('type');
    var description = $(this).data('description');
    var montant = $(this).data('montant');
    var mode = $(this).data('mode');
    var beneficiaire = $(this).data('beneficiaire');
    var date = $(this).data('date');
    var piece = $(this).data('piece');

    $('#depense_modal_id').val(id);
    if ($.fn.select2) {
      $('#depense_modal_type').val(type).trigger('change');
    } else {
      $('#depense_modal_type').val(type);
    }
    $('#depense_modal_description').val(description);
    $('#depense_modal_montant').val(montant);
    $('#depense_modal_mode').val(mode || 'espece');
    $('#depense_modal_beneficiaire').val(beneficiaire);
    $('#depense_modal_date').val(date);
    $('#depense_modal_piece').val('');

    if (piece) {
      $('#piece-justificative-preview').html('<a href="' + racine + 'public/' + piece + '" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="paperclip" style="width:13px;height:13px;"></i> Voir la pièce justificative actuelle</a>');
    } else {
      $('#piece-justificative-preview').html('');
    }

    $('#modal-depense-title').html('<i data-lucide="edit" style="width: 20px; height: 20px;"></i> Modifier la Dépense');
    $('#modal-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#depense_modal_description').focus(); }, 100);
  });

  // Fermer la modale
  $('.btn-close-modal-depense').on('click', function() {
    $('#modal-depense').css('display', 'none');
  });
  $('#modal-depense').on('click', function(e) {
    if ($(e.target).is('#modal-depense')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX du formulaire de modale
  $('#form-depense-modal').on('submit', function(e) {
    e.preventDefault();

    // Contrôle client de la taille de la pièce justificative (Max 3 Mo)
    var fileInput = $('#depense_modal_piece')[0];
    if (fileInput && fileInput.files && fileInput.files[0]) {
      var file = fileInput.files[0];
      var maxBytes = 3 * 1024 * 1024; // 3 Mo
      if (file.size > maxBytes) {
        var currentMb = (file.size / (1024 * 1024)).toFixed(2);
        showNotify('La pièce justificative dépasse la taille maximale autorisée de 3 Mo (Fichier actuel : ' + currentMb + ' Mo)', 'error');
        return false;
      }
    }

    var isEdit = !!$('#depense_modal_id').val();
    var url = isEdit ? (racine + 'depense/edit') : (racine + 'depense/add');
    var $btn = $('#btn-save-depense-modal');
    var formData = new FormData(this);

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Dépense');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Décaissement enregistré avec succès !', 'success');
          $('#modal-depense').css('display', 'none');
          table.ajax.reload(null, false);
          reloadStats();
        } else {
          showNotify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Dépense');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        showNotify(msg, 'error');
      }
    });
  });

  window.changerStatutDepense = function(id, newStatut) {
    var labels = {
      'approuve': 'approuver',
      'annule': 'annuler',
      'en_attente': 'remettre en attente'
    };
    var actionMsg = labels[newStatut] || 'modifier le statut de';
    if (!confirm('Voulez-vous vraiment ' + actionMsg + ' cette dépense ?')) {
      return;
    }

    $.ajax({
      url: racine + 'depense/changer',
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
          showNotify(res.message || 'Statut mis à jour avec succès', 'success');
          table.ajax.reload(null, false);
          reloadStats();
        } else {
          showNotify(res.message || 'Erreur lors du changement de statut', 'error');
        }
      },
      error: function() {
        showNotify('Erreur réseau ou permission insuffisante', 'error');
      }
    });
  };

  $('#filter-type-depense, #filter-date-debut, #filter-date-fin').on('change input', function() {
    table.ajax.reload();
    reloadStats();
  });

  $('#btn-reset-filters').on('click', function() {
    if ($.fn.select2 && $('#filter-type-depense').data('select2')) {
      $('#filter-type-depense').val('').trigger('change.select2');
    } else {
      $('#filter-type-depense').val('');
    }
    $('#filter-date-debut').val('');
    $('#filter-date-fin').val('');
    table.ajax.reload();
    reloadStats();
  });


  }

  // =========================================================================
  // 2. VUE : ÉDITION D'UNE DÉPENSE (views/depenses/edit.php)
  // =========================================================================
  function initDepensesEditModule() {
    if (!$('#select_type_depense').length) return;
 
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Rechercher la catégorie de dépense --",
      allowClear: true,
      width: '100%'
    });
  }

  }

  // =========================================================================
  // 3. VUE : TYPES ET CATÉGORIES DE DÉPENSES (views/type_depenses/list.php)
  // =========================================================================
  function initTypeDepensesListModule() {
    if (!$('#table-type_depenses, #type-depenses-config').length) return;

  if (window.lucide) lucide.createIcons();

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  var configEl = document.getElementById('type-depenses-config');
  var config = {};
  if (configEl) {
    try {
      config = JSON.parse(configEl.textContent);
    } catch (e) {
      console.error('Erreur parsing type-depenses-config:', e);
    }
  }

  var csrfToken = config.csrfToken || '';

  var table = $('#table-type_depenses').DataTable({
    ajax: racine + 'type_depense/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_type_depense', defaultContent: '-' },
      { data: 'libelle_type_depense', defaultContent: '-' },
      { data: 'statut_type_depense', width: '100px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d !== 'inactif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-td" data-id="' + row.id_type_depense + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, orderable: false, render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_type_depense || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-td" data-id="' + d.id_type_depense + '" data-libelle="' + safeLibelle + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'type_depense/details/' + (d.editId || d.id_type_depense) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: dtLang },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  function showNotify(msg, type) {
    type = type || 'info';
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else if (window.Swal) {
      var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
      });
      Toast.fire({
        icon: type,
        title: msg
      });
    } else {
      alert(msg);
    }
  }

  // Modification dynamique de statut par toggle
  $(document).on('change', '.toggle-statut-td', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);
    $.ajax({
      url: racine + 'type_depense/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { id: id, csrf_token: csrfToken },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Statut mis à jour avec succès', 'success');
          table.ajax.reload(null, false);
        } else {
          showNotify(res.message || 'Erreur lors du changement de statut', 'error');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        showNotify('Erreur réseau', 'error');
        $input.prop('checked', !isChecked);
      }
    });
  });

  // Bouton Ajouter en haut -> Ouvre la modale
  $('.btn-add-type-depense').on('click', function() {
    $('#td_modal_id').val('');
    $('#td_modal_libelle').val('');
    $('#modal-type-depense-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Catégorie de Dépense');
    $('#modal-type-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#td_modal_libelle').focus(); }, 100);
  });

  // Bouton Éditer dans la table -> Ouvre la modale d'édition pré-remplie
  $(document).on('click', '.btn-edit-td', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');

    $('#td_modal_id').val(id);
    $('#td_modal_libelle').val(libelle);
    $('#modal-type-depense-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Catégorie de Dépense');
    $('#modal-type-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#td_modal_libelle').focus(); }, 100);
  });

  // Fermer la modale
  $('.btn-close-modal-td').on('click', function() {
    $('#modal-type-depense').css('display', 'none');
  });
  $('#modal-type-depense').on('click', function(e) {
    if ($(e.target).is('#modal-type-depense')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX de la modale d'édition / ajout
  $('#form-type-depense-modal').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#td_modal_id').val();
    var url = isEdit ? (racine + 'type_depense/edit') : (racine + 'type_depense/add');
    var $btn = $('#btn-save-td-modal');

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
          showNotify(res.message || 'Opération réussie avec succès', 'success');
          $('#modal-type-depense').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          showNotify(res.message || 'Erreur lors de l\'enregistrement', 'error');
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
        showNotify(msg, 'error');
      }
    });
  });

  }

  $(document).ready(function() {
    initDepensesListModule();
    initDepensesEditModule();
    initTypeDepensesListModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);