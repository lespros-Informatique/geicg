/**
 * Module JS : Liste & Gestion des Événements (views/evenements/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('evenements-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-evenements').DataTable({
    ajax: racine + 'evenement/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'titre_evenement', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'date_evenement', width: '130px', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">-</span>';
        var p = d.split('-');
        var formatted = p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
        return '<span style="font-weight:600; color:#334155; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="calendar" style="width:13px;height:13px;color:#1E3A5F;"></i> ' + formatted + '</span>';
      }},
      { data: 'lieu_evenement', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">Non précisé</span>';
        return '<span style="color:#475569; font-weight:500;"><i data-lucide="map-pin" style="width:13px;height:13px;color:#64748B;display:inline-block;vertical-align:middle;margin-right:4px;"></i>' + d + '</span>';
      }},
      { data: 'statut_evenement', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-event" data-id="' + row.id_evenement + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-evenement" data-id="' + d.id_evenement + '" data-titre="' + (d.titre_evenement ? $('<div>').text(d.titre_evenement).html() : '') + '" data-date="' + (d.date_evenement || '') + '" data-lieu="' + (d.lieu_evenement ? $('<div>').text(d.lieu_evenement).html() : '') + '" data-desc="' + (d.description_evenement ? $('<div>').text(d.description_evenement).html() : '') + '" data-statut="' + (d.statut_evenement || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'evenement/details/' + (d.editId || d.id_evenement) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-event', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'evenement/changer',
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
  function updateStatutUI(isActif) {
    if (isActif) {
      $('#statut_slider').css('background-color', '#15803D');
      $('#statut_knob').css('left', '22px');
      $('#statut_text').css('color', '#15803D').text('Actif (Visible)');
    } else {
      $('#statut_slider').css('background-color', '#CBD5E1');
      $('#statut_knob').css('left', '3px');
      $('#statut_text').css('color', '#64748B').text('Inactif (Masqué)');
    }
  }

  $('#evenement_statut').on('change', function() {
    updateStatutUI($(this).is(':checked'));
  });

  // GESTION MODALE AJOUT / ÉDITION ÉVÉNEMENT
  $(document).on('click', '.btn-add-evenement', function(e) {
    e.preventDefault();
    $('#form-evenement')[0].reset();
    $('#evenement_id').val('');
    $('#evenement_date').val(new Date().toISOString().split('T')[0]);
    $('#evenement_statut').prop('checked', true);
    updateStatutUI(true);
    $('#modal-evenement-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Événement');
    $('#modal-evenement').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#evenement_titre').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-evenement', function(e) {
    e.preventDefault();
    $('#form-evenement')[0].reset();
    var id = $(this).data('id');
    var titre = $(this).data('titre');
    var date = $(this).data('date');
    var lieu = $(this).data('lieu');
    var desc = $(this).data('desc');
    var statut = $(this).data('statut');

    $('#evenement_id').val(id);
    $('#evenement_titre').val(titre);
    $('#evenement_date').val(date);
    $('#evenement_lieu').val(lieu);
    $('#evenement_desc').val(desc);

    var isActif = (statut === 'actif');
    $('#evenement_statut').prop('checked', isActif);
    updateStatutUI(isActif);

    $('#modal-evenement-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Événement');
    $('#modal-evenement').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#evenement_titre').focus(); }, 100);
  });

  $('.btn-close-modal-evenement').on('click', function() {
    $('#modal-evenement').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-evenement')) {
      $('#modal-evenement').hide();
    }
  });

  $('#form-evenement').on('submit', function(e) {
    e.preventDefault();
    var id = $('#evenement_id').val();
    var url = racine + (id ? 'evenement/edit' : 'evenement/add');
    var $btn = $('#btn-submit-evenement');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    var formData = $(this).serializeArray();
    var hasStatut = false;
    for (var i = 0; i < formData.length; i++) {
      if (formData[i].name === 'statut_evenement') {
        hasStatut = true;
        break;
      }
    }
    if (!hasStatut) {
      formData.push({ name: 'statut_evenement', value: 'inactif' });
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
          if (typeof showToast === 'function') showToast(res.message || 'Événement enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Événement enregistré avec succès');
          $('#modal-evenement').hide();
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
});

