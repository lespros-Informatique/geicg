/**
 * Module JS : Catalogue des Niveaux d'Études (views/niveaux/list.php)
 */
$(document).ready(function() {
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
});

