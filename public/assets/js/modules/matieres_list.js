/**
 * Module JS : Liste & Gestion des Matières (views/matieres/list.php)
 */
$(document).ready(function() {
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
});

