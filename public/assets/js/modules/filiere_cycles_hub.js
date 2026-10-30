/**
 * Module JS : Hub Filières & Cycles (views/filiere_cycles/hub.php)
 */
$(document).ready(function() {
  var racine = window.RACINE || '/';
  if (window.lucide) lucide.createIcons();

  // Gestion des Onglets
  $('.tab-btn').on('click', function() {
    $('.tab-btn').css({ 'color': '#64748B', 'border-bottom-color': 'transparent' }).removeClass('active');
    $(this).css({ 'color': '#1E3A5F', 'border-bottom-color': '#1E3A5F' }).addClass('active');
    $('.tab-content').hide();
    var targetTab = $(this).data('tab');
    $('#' + targetTab).show();
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  });

  // Table 1: Assignations
  var tableAssign = $('#table-assignations').DataTable({
    ajax: racine + 'filiere_cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere_cycle', defaultContent: '-', width: '50px' },
      { data: 'code_filiere_cycle', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_cycle', width: '180px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non défini') + '</span>';
      }},
      { data: 'libelle_filiere', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>';
      }},
      { data: 'statut_filiere_cycle', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'filiere_cycle/edition/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'filiere_cycle/details/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Filières
  var tableFilieres = $('#table-filieres-catalogue').DataTable({
    ajax: racine + 'filiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere', defaultContent: '-', width: '50px' },
      { data: 'code_filiere', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_filiere', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'slug_filiere', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px; letter-spacing:0.3px;">' + d + '</span>';
      }},
      { data: 'description_filiere', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d || '-';
      }},
      { data: 'statut_filiere', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'filiere/edition/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'filiere/details/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 3: Cycles d'Études
  var tableCycles = $('#table-cycles-list').DataTable({
    ajax: racine + 'cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cycle', defaultContent: '-', width: '50px' },
      { data: 'code_cycle', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_cycle', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>';
      }},
      { data: 'slug_cycle', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; font-weight:800; font-size:11.5px; padding:3px 9px; border-radius:6px; letter-spacing:0.3px;">' + d + '</span>';
      }},
      { data: 'description_cycle', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d || '-';
      }},
      { data: 'statut_cycle', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + racine + 'cycle/edition/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'cycle/details/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});

