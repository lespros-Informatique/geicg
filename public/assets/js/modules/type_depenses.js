/**
 * Module JS: Gestion des Types et Catégories de Dépenses
 * Externalisation stricte du code JS pour views/type_depenses/list.php
 */

$(document).ready(function() {
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
});
