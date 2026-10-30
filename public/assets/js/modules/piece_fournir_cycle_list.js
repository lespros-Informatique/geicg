/**
 * Module JS: Dossier de Pièces à Fournir par Cycle
 * Externalisation stricte du code JS pour views/piece_fournir_cycle/list.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-cycle, #filter-niveau').select2({ width: '100%' });
  }

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  var configEl = document.getElementById('piece-fournir-cycle-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var csrfToken = config.csrfToken || '';

  var table = $('#table-pieces-cycle').DataTable({
    ajax: {
      url: racine + 'piece_fournir_cycle/apiList',
      type: 'GET',
      data: function(d) {
        d.cycle_code = $('#filter-cycle').val();
        d.niveau_code = $('#filter-niveau').val();
      }
    },
    processing: true,
    autoWidth: false,
    order: [[0, 'desc']],
    columns: [
      { data: 'code_piece_cycle', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</code>';
      } },
      { data: 'slug_cycle', render: function(d, type, row) {
        var cycleText = d || row.libelle_cycle || 'Tous cycles';
        return '<span style="font-weight:700; color:#334155; font-size:13px;">' + cycleText + '</span>';
      } },
      { data: 'libelle_niveau', render: function(d) {
        if (d) {
          return '<span class="badge" style="background:#E0F2FE; color:#0369A1; font-weight:800; font-size:12px; padding:5px 10px; border-radius:6px; border:1px solid #BAE6FD; display:inline-block; position:static;">' + d + '</span>';
        }
        return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-weight:700; font-size:12px; padding:5px 10px; border-radius:6px; display:inline-block; position:static;">Tous niveaux</span>';
      } },
      { data: 'libelle_piece', className: 'col-piece', render: function(d, type, row) {
        var desc = row.description_piece ? '<div style="font-size:11.5px; color:#64748B; margin-top:3px; line-height:1.4; white-space:normal; overflow-wrap:break-word;">' + row.description_piece + '</div>' : '';
        return '<div style="font-weight:700; color:#0F172A; white-space:normal; overflow-wrap:break-word; line-height:1.4;">' + (d || '-') + '</div>' + desc;
      } },
      { data: 'nombre_exemplaires', className: 'text-center', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; background:#F1F5F9; padding:4px 10px; border-radius:6px;">' + (d || 1) + ' ex.</span>';
      } },
      { data: 'nature_document', render: function(d) {
        var natureLabels = {
          'photocopie_simple': '<span style="color:#334155; font-weight:600;"><i data-lucide="copy" style="width:13px;height:13px;display:inline;"></i> Photocopie Simple</span>',
          'photocopie_legalisee': '<span style="color:#1E3A5F; font-weight:700;"><i data-lucide="stamp" style="width:13px;height:13px;display:inline;"></i> Photocopie Légalisée</span>',
          'original': '<span style="color:#B45309; font-weight:700;"><i data-lucide="award" style="width:13px;height:13px;display:inline;"></i> Original Requis</span>',
          'numerique': '<span style="color:#2563EB; font-weight:600;"><i data-lucide="upload" style="width:13px;height:13px;display:inline;"></i> Fichier Numérique (PDF)</span>',
          'aucun': '<span style="color:#64748B; font-weight:600;"><i data-lucide="minus-circle" style="width:13px;height:13px;display:inline;"></i> Aucun</span>'
        };
        return natureLabels[d] || (d || 'Photocopie simple');
      } },
      { data: 'statut_piece_cycle', className: 'text-center', render: function(d, type, row) {
        var val = d || 'actif';
        var isActif = val === 'actif';
        var bg = isActif ? '#DCFCE7' : '#FEE2E2';
        var col = isActif ? '#15803D' : '#B91C1C';
        var border = isActif ? '#86EFAC' : '#FCA5A5';

        return '<select class="select-statut-piece-cycle" data-id="' + row.id_piece_cycle + '" style="background:' + bg + '; color:' + col + '; border:1px solid ' + border + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="actif" ' + (isActif ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Actif</option>' +
               '<option value="inactif" ' + (!isActif ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Inactif</option>' +
               '</select>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'piece_fournir_cycle/edition/' + (d.editId || d.id_piece_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>' +
               '<a href="' + racine + 'piece_fournir_cycle/supprimer/' + (d.editId || d.id_piece_cycle) + '" onclick="return confirm(\'Voulez-vous vraiment retirer cette pièce du dossier du cycle ?\')" class="btn btn-sm btn-danger" style="background:#EF4444; color:#fff; border:none; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>';
      } }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-cycle, #filter-niveau').on('change', function() {
    table.ajax.reload();
  });

  $(document).on('change', '.select-statut-piece-cycle', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: racine + 'piece_fournir_cycle/changer',
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
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau ou serveur');
      }
    });
  });
});
