/**
 * Module JS : Liste & Gestion des Salles de cours (views/salles/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('salles-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  if (window.lucide) lucide.createIcons();

  var table = $('#table-salles').DataTable({
    ajax: racine + 'salle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_salle', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_salle', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'capacite_salle', width: '100px', className: 'text-center', render: function(d) {
        if (!d || d <= 0) return '<span style="color:#94A3B8;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:700; font-size:12px; padding:4px 8px; border-radius:6px; border:1px solid #BFDBFE;">' + d + ' places</span>';
      }},
      { data: 'statut_salle', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-salle" data-id="' + row.id_salle + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_salle || '').html();
        var safeCapacite = d.capacite_salle || '';
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-salle" data-id="' + d.id_salle + '" data-libelle="' + safeLibelle + '" data-capacite="' + safeCapacite + '" data-statut="' + (d.statut_salle || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'salle/details/' + (d.editId || d.id_salle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Modal : Ajouter
  $('.btn-add-salle').on('click', function() {
    $('#form-salle')[0].reset();
    $('#salle_id').val('');
    $('#modal-salle-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Salle de cours');
    $('#modal-salle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#salle_libelle').focus(); }, 100);
  });

  // Modal : Modifier
  $(document).on('click', '.btn-edit-salle', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var capacite = $(this).data('capacite');
    var statut = $(this).data('statut');

    $('#salle_id').val(id);
    $('#salle_libelle').val(libelle);
    $('#salle_capacite').val(capacite);
    $('#salle_statut').val(statut || 'actif');
    $('#modal-salle-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Salle de cours');
    $('#modal-salle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#salle_libelle').focus(); }, 100);
  });

  // Modal : Fermer
  $('.btn-close-modal-salle').on('click', function() {
    $('#modal-salle').css('display', 'none');
  });
  $('#modal-salle').on('click', function(e) {
    if ($(e.target).is('#modal-salle')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX (Ajout & Modification)
  $('#form-salle').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#salle_id').val();
    var url = isEdit ? (racine + 'salle/edit') : (racine + 'salle/add');
    var $btn = $('#btn-save-salle');

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
          $('#modal-salle').css('display', 'none');
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

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-salle', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'salle/changer',
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

