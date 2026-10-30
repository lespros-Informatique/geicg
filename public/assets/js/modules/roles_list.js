/**
 * Module JS : Liste & Gestion des Rôles & Profils RBAC (views/roles/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('roles-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-roles').DataTable({
    ajax: racine + 'role/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_role', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_role', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'groupe', render: function(d, type, row) {
        if (type !== 'display') return d || row.module || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || row.module || 'Général') + '</span>';
      }},
      { data: 'nb_users', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || 0;
        return '<span style="font-weight:700; color:#1E3A5F; background:#EFF6FF; padding:3px 10px; border-radius:10px; font-size:12px;">' + (d || 0) + ' users</span>';
      }},
      { data: 'nb_permissions', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || 0;
        return '<span style="font-weight:700; color:#15803D; background:#DCFCE7; padding:3px 10px; border-radius:10px; font-size:12px;">' + (d || 0) + ' droits</span>';
      }},
      { data: 'statut_role', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-role" data-id="' + row.id + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + racine + 'role/edition/' + (d.editId || d.id) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'role/details/' + (d.editId || d.id) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.toggle-statut-role', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'role/changer',
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

