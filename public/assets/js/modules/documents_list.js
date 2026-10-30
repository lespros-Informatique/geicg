/**
 * Module JS : Liste & Gestion des Documents Pédagogiques (views/documents/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('documents-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-documents').DataTable({
    ajax: racine + 'document/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'libelle_document', defaultContent: '-' },
      { data: 'libelle_filiere', defaultContent: '-' },
      { data: 'libelle_niveau', defaultContent: '-' },
      { data: 'lien_document', defaultContent: '-', width: '130px', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;font-size:12px;font-style:italic;">Aucun fichier</span>';
        return '<a href="' + d + '" target="_blank" download class="btn btn-sm" style="background:#F1F5F9;color:#1E3A5F;border:1px solid #CBD5E1;font-weight:700;font-size:11.5px;padding:4px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;"><i data-lucide="download" style="width:13px;height:13px;"></i> Télécharger</a>';
      }},
      { data: 'statut_document', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-doc" data-id="' + row.id_document + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + racine + 'document/edition/' + (d.editId || d.id_document) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + racine + 'document/details/' + (d.editId || d.id_document) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Gestion du Switch Activer / Désactiver en Ajax
  $(document).on('change', '.toggle-statut-doc', function() {
    var idDoc = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'document/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: idDoc,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) {
            toastr.success(res.message || 'Statut mis à jour avec succès');
          }
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur de communication avec le serveur');
        $input.prop('checked', !isChecked);
      }
    });
  });
});

