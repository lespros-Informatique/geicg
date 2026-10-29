/**
 * Module JS: Gestion des Frais Annexes
 * Externalisation stricte du code JS pour views/frais_annexes/list.php
 */
$(document).ready(function() {
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  var listUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('fraisAnnexe/apiList') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'fraisAnnexe/apiList');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'json/datatables-i18n-fr-FR.json');

  var tableFrais = $('#table-frais-annexes').DataTable({
    ajax: {
      url: listUrl,
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.categorie = $('#filter-categorie').val();
        d.type_filiere = $('#filter-type-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_frais_annexe', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_frais_annexe', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'categorie_frais_annexe', render: function(d) {
        if (!d) return '-';
        var label = (d === 'inscription') ? 'Inscription' : 'Autre';
        return '<span style="font-weight:600; color:#334155;">' + label + '</span>';
      }},
      { data: 'type_filiere', render: function(d) {
        if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
        if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
        return '<span class="badge" style="background:#F1F5F9; color:#475569; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Toutes Filières</span>';
      }},
      { data: 'libelle_niveau', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">Tous les Niveaux</span>';
        return '<span style="font-weight:600; color:#334155;">' + d + '</span>';
      }},
      { data: 'montant_frais_annexe', render: function(d) {
        var val = parseFloat(d || 0);
        return '<span style="font-weight:800; color:#15803D; font-size:14px;">' + val.toLocaleString('fr-FR') + ' FCFA</span>';
      }},
      { data: 'statut_frais_annexe', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-frais" data-id="' + row.id_frais_annexe + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '120px', orderable: false, render: function(d) {
        var safeLib = (d.libelle_frais_annexe || '').replace(/"/g, '&quot;');
        var fid = d.id_frais_annexe || d.id || '';
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-frais" ' +
               'data-id="' + fid + '" ' +
               'data-libelle="' + safeLib + '" ' +
               'data-categorie="' + (d.categorie_frais_annexe || 'autre') + '" ' +
               'data-type-filiere="' + (d.type_filiere || 'TOUT') + '" ' +
               'data-niveau-code="' + (d.niveau_code || 'TOUT') + '" ' +
               'data-montant="' + (d.montant_frais_annexe || '0') + '" ' +
               'data-statut="' + (d.statut_frais_annexe || 'actif') + '" ' +
               'style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">' +
               '<i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';
      }, className: 'text-end' }
    ],
    language: (typeof dtLang === 'object') ? dtLang : { url: dtLang },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  function updatePrintUrl() {
    var annee = $('#filter-annee').val() || '';
    var categorie = $('#filter-categorie').val() || '';
    var typeFiliere = $('#filter-type-filiere').val() || '';
    var niveau = $('#filter-niveau').val() || '';

    var params = new URLSearchParams();
    if (annee) params.set('annee_code', annee);
    if (categorie) params.set('categorie', categorie);
    if (typeFiliere) params.set('type_filiere', typeFiliere);
    if (niveau) params.set('niveau_code', niveau);

    var qs = params.toString();
    var printBase = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('fraisAnnexe/imprimerPdf') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'fraisAnnexe/imprimerPdf');
    var href = printBase + (qs ? '?' + qs : '');
    $('#btn-print-frais').attr('href', href);
  }

  $('#filter-annee, #filter-categorie, #filter-type-filiere, #filter-niveau').on('change', function() {
    tableFrais.ajax.reload();
    updatePrintUrl();
  });

  updatePrintUrl();

  // Modal : Ajouter
  $('.btn-add-frais').on('click', function() {
    $('#form-frais-annexe')[0].reset();
    $('#frais_annexe_id').val('');
    $('#frais_categorie').val('inscription');
    $('#frais_type_filiere').val('INDUSTRIELLE');
    $('#frais_niveau_code').val('TOUT');
    $('#modal-frais-annexe-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Tarification de Frais Annexes');
    $('#modal-frais-annexe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Modal : Éditer
  $(document).on('click', '.btn-edit-frais', function() {
    var $btn = $(this);
    var id = $btn.attr('data-id') || $btn.data('id');
    var libelle = $btn.attr('data-libelle') || $btn.data('libelle');
    var categorie = $btn.attr('data-categorie') || $btn.data('categorie') || 'autre';
    var typeFiliere = $btn.attr('data-type-filiere') || $btn.data('typeFiliere') || $btn.data('type-filiere');
    var niveauCode = $btn.attr('data-niveau-code') || $btn.data('niveauCode') || $btn.data('niveau-code');
    var montant = $btn.attr('data-montant') || $btn.data('montant');
    var statut = $btn.attr('data-statut') || $btn.data('statut');

    var decLib = $('<div>').html(libelle || '').text();

    $('#frais_annexe_id').val(id);
    $('#frais_libelle').val(decLib || libelle);
    $('#frais_categorie').val(categorie);
    $('#frais_type_filiere').val(typeFiliere || 'TOUT');
    $('#frais_niveau_code').val(niveauCode || 'TOUT');
    $('#frais_montant').val(montant);
    $('#frais_statut').val(statut || 'actif');
    $('#modal-frais-annexe-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Tarif de Frais Annexes');
    $('#modal-frais-annexe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Fermer modal via bouton (croix ou Annuler)
  $(document).on('click', '.btn-close-modal-frais', function() {
    $('#modal-frais-annexe').css('display', 'none');
  });

  // Fermer modal en cliquant sur l'arrière-plan (backdrop)
  $(document).on('click', '#modal-frais-annexe', function(e) {
    if ($(e.target).is('#modal-frais-annexe')) {
      $(this).css('display', 'none');
    }
  });

  function notify(msg, type) {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else if (window.toastr && typeof toastr[type] === 'function') {
      toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  // Soumission Formulaire
  $('#form-frais-annexe').on('submit', function(e) {
    e.preventDefault();
    var isEdit = $('#frais_annexe_id').val() !== '';
    var targetUrl = (typeof GEICG !== 'undefined' && GEICG.url) ?
      GEICG.url(isEdit ? 'fraisAnnexe/edit' : 'fraisAnnexe/store') :
      ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + (isEdit ? 'fraisAnnexe/edit' : 'fraisAnnexe/store'));
    var $btn = $('#btn-save-frais');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: targetUrl,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          notify(res.message || 'Opération réussie avec succès !', 'success');
          $('#modal-frais-annexe').css('display', 'none');
          tableFrais.ajax.reload(null, false);
        } else {
          notify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr, status, err) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = (xhr.responseJSON && xhr.responseJSON.message) || err || 'Erreur lors de l\'enregistrement';
        notify(msg, 'error');
      }
    });
  });

  // Toggle Statut
  $(document).on('change', '.toggle-statut-frais', function() {
    var id = $(this).data('id');
    var csrfToken = $('input[name="csrf_token"]').val() || '';
    var toggleUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('fraisAnnexe/toggleStatut') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'fraisAnnexe/toggleStatut');

    $.ajax({
      url: toggleUrl,
      type: 'POST',
      data: { id_frais_annexe: id, csrf_token: csrfToken },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          notify(res.message, 'success');
        } else {
          notify(res.message, 'error');
        }
        tableFrais.ajax.reload(null, false);
      },
      error: function() {
        notify('Erreur serveur lors du changement de statut', 'error');
        tableFrais.ajax.reload(null, false);
      }
    });
  });
});
