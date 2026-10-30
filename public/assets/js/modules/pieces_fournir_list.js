/**
 * Module JS: Répertoire des Pièces & Documents à Fournir
 * Externalisation stricte du code JS pour views/pieces_fournir/list.php
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  var configEl = document.getElementById('pieces-fournir-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var canManagePieces = Boolean(config.canManagePieces);

  var table = $('#table-pieces-fournir').DataTable({
    ajax: racine + 'piece_fournir/apiList',
    processing: true,
    autoWidth: false,
    order: [[0, 'asc']],
    columns: [
      { data: 'code_piece_fournir', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; font-size:12.5px;">' + (d || '-') + '</code>';
      } },
      { data: 'libelle_piece', className: 'col-libelle', render: function(d) {
        return '<div style="font-weight:700; color:#0F172A; font-size:13.5px; display:flex; align-items:flex-start; gap:8px; white-space:normal; overflow-wrap:break-word; line-height:1.4;"><i data-lucide="file-text" style="width:16px;height:16px;color:#3B82F6;flex-shrink:0;margin-top:2px;"></i> <span style="white-space:normal; overflow-wrap:break-word;">' + (d || '-') + '</span></div>';
      } },
      { data: 'description_piece', className: 'col-desc', render: function(d) {
        return '<span style="color:#64748B; font-size:12.5px; white-space:normal; overflow-wrap:break-word; line-height:1.4;">' + (d || '<em style="color:#94A3B8;">Aucune instruction particulière</em>') + '</span>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        if (canManagePieces) {
          return '<a href="' + racine + 'piece_fournir/edition/' + (d.editId || d.id_piece_fournir) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>' +
                 '<a href="' + racine + 'piece_fournir/supprimer/' + (d.editId || d.id_piece_fournir) + '" onclick="return confirm(\'Voulez-vous vraiment supprimer cette pièce du répertoire ?\')" class="btn btn-sm btn-danger" style="background:#EF4444; color:#fff; border:none; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>';
        }
        return '<span style="color:#94A3B8; font-size:12px; font-style:italic;">Consultation</span>';
      } }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
