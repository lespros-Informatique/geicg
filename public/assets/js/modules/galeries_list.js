/**
 * Module JS : Liste & Gestion des Galeries Médias (views/galeries/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('galeries-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-galeries').DataTable({
    ajax: racine + 'galerie/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_galerie',  defaultContent: '-', width: '130px' },
      { data: 'titre_galerie', defaultContent: '-' },
      { data: 'type_galerie',  defaultContent: '-', width: '110px', render: function(d) {
        if (!d) return '-';
        return d === 'video'
          ? '<span style="background:#F1F5F9;color:#475569;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Vidéo</span>'
          : '<span style="background:#EFF6FF;color:#1E3A5F;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Photo</span>';
      }},
      { data: 'statut_galerie', defaultContent: '-', width: '130px', className: 'text-center', render: function(d, type, row) {
        var val = d || 'actif';
        var bgColors = { 'actif': '#DCFCE7', 'brouillon': '#FEF3C7', 'archive': '#F1F5F9' };
        var textColors = { 'actif': '#15803D', 'brouillon': '#B45309', 'archive': '#475569' };
        var borderColors = { 'actif': '#86EFAC', 'brouillon': '#FCD34D', 'archive': '#CBD5E1' };
        var currentBg = bgColors[val] || '#F1F5F9';
        var currentText = textColors[val] || '#334155';
        var currentBorder = borderColors[val] || '#CBD5E1';

        return '<select class="select-statut-galerie" data-id="' + row.id_galerie + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="actif" ' + (val === 'actif' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Actif</option>' +
               '<option value="brouillon" ' + (val === 'brouillon' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">Brouillon</option>' +
               '<option value="archive" ' + (val === 'archive' ? 'selected' : '') + ' style="background:#fff; color:#475569;">Archivé</option>' +
               '</select>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + racine + 'galerie/edition/' + (d.editId || d.id_galerie) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + racine + 'galerie/details/' + (d.editId || d.id_galerie) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Changement de statut via Ajax
  $(document).on('change', '.select-statut-galerie', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: racine + 'galerie/changer',
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
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          table.ajax.reload(null, false);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        table.ajax.reload(null, false);
      }
    });
  });
});

