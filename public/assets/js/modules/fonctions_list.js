/**
 * Module JS : Liste & Gestion des Fonctions / Postes (views/fonctions/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('fonctions-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  if (window.lucide) lucide.createIcons();

  var table = $('#table-fonctions').DataTable({
    ajax: racine + 'fonction/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_fonction', defaultContent: '-' },
      { data: 'libelle_fonction', defaultContent: '-' },
      { data: 'statut_fonction', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-fonction" data-id="' + row.id_fonction + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, orderable: false, render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_fonction || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-fonction" data-id="' + d.id_fonction + '" data-libelle="' + safeLibelle + '" data-statut="' + (d.statut_fonction || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'fonction/details/' + (d.editId || d.id_fonction) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Modal : Ajouter
  $('.btn-add-fonction').on('click', function() {
    $('#form-fonction')[0].reset();
    $('#fonction_id').val('');
    $('#modal-fonction-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Fonction / Poste');
    $('#modal-fonction').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#fonction_libelle').focus(); }, 100);
  });

  // Modal : Modifier
  $(document).on('click', '.btn-edit-fonction', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var statut = $(this).data('statut');

    $('#fonction_id').val(id);
    $('#fonction_libelle').val(libelle);
    $('#fonction_statut').val(statut || 'actif');
    $('#modal-fonction-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Fonction / Poste');
    $('#modal-fonction').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#fonction_libelle').focus(); }, 100);
  });

  // Modal : Fermer
  $('.btn-close-modal-fonction').on('click', function() {
    $('#modal-fonction').css('display', 'none');
  });
  $('#modal-fonction').on('click', function(e) {
    if ($(e.target).is('#modal-fonction')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission Formulaire AJAX (Ajout & Modification)
  $('#form-fonction').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#fonction_id').val();
    var url = isEdit ? (racine + 'fonction/edit') : (racine + 'fonction/add');
    var $btn = $('#btn-save-fonction');

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
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $('#modal-fonction').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
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
        if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Bascule statut AJAX
  $(document).on('change', '.toggle-statut-fonction', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'fonction/changer',
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
});

