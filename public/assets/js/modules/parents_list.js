/**
 * Module JS: Liste des Parents & Tuteurs
 * Externalisation stricte du code JS pour views/parents/list.php
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  $('#table-parents').DataTable({
    ajax: racine + 'parent/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'etudiant_code', defaultContent: '-' },
      { data: 'nom_pere', defaultContent: '-' },
      { data: 'telephone_pere', defaultContent: '-' },
      { data: 'nom_mere', defaultContent: '-' },
      { data: 'telephone_mere', defaultContent: '-' },
      { data: 'nom_tuteur', defaultContent: '-' },
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + racine + 'parent/edition/' + (d.editId || d.id_parent) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'parent/details/' + (d.editId || d.id_parent) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
