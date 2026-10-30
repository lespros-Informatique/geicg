/**
 * Module JS : Liste & Gestion des Utilisateurs Système (views/users/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('users-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-users').DataTable({
    ajax: racine + 'user/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'nom', render: function(d, type, row) {
        if (type !== 'display') return (d || '') + ' ' + (row.prenom || '');
        var nomComplet = (d || '') + ' ' + (row.prenom || '');
        return '<strong style="color:#0F172A;">' + (nomComplet.trim() || '-') + '</strong>';
      }},
      { data: 'email', render: function(d, type, row) {
        if (type !== 'display') return d || row.telephone || '';
        var res = '';
        if (d) res += '<div style="font-weight:600; color:#1E3A5F; font-size:13px;">' + d + '</div>';
        if (row.telephone) res += '<div style="font-size:12px; color:#64748B;">' + row.telephone + '</div>';
        return res || '-';
      }},
      { data: 'fonction', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || '-') + '</span>';
      }},
      { data: 'roles_list', render: function(d, type, row) {
        if (type !== 'display') return (row.roles_list && row.roles_list.length) ? row.roles_list.join(', ') : (row.role || '');
        var roles = (row.roles_list && row.roles_list.length) ? row.roles_list : (row.role ? [row.role] : []);
        if (!roles.length || roles[0] === 'Non attribué') {
          return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Non attribué</span>';
        }
        var badges = roles.map(function(r) {
          return '<span style="background:rgba(24, 56, 95, 0.08); color:var(--primary-color, #18385F); border: 1px solid rgba(24, 56, 95, 0.18); font-weight:700; padding:3px 8px; border-radius:6px; font-size:11.5px; display:inline-block; margin:2px 2px;">' + r + '</span>';
        });
        return '<div style="display:flex; flex-wrap:wrap; gap:3px; max-width:260px;">' + badges.join('') + '</div>';
      }},
      { data: 'statut', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-user" data-id="' + (row.id || row.id_user) + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + racine + 'user/edition/' + (d.editId || d.id) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'user/details/' + (d.editId || d.id) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Profil</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-user', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'user/changer',
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

