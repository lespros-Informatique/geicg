/**
 * Module JS Unifié : Utilisateurs Système (Liste, Édition & Rôles, Changement de mot de passe)
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // =========================================================================
  // 1. VUE : LISTE & GESTION DES UTILISATEURS (views/users/list.php)
  // =========================================================================
  function initUsersListModule() {
    if (!$('#table-users, #users-list-config').length) return;

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

  }

  // =========================================================================
  // 2. VUE : CRÉATION / MODIFICATION & ATTRIBUTION DES RÔLES (views/users/edit.php)
  // =========================================================================
  function initUsersEditModule() {
    if (!$('#form-user, #users-edit-config').length) return;
 
  var configEl = document.getElementById('users-edit-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var ALL_ROLES = config.allRoles || [];
  var SAVED_ROLES = config.savedRoles || [];
  
  // Indexer les rôles enregistrés pour récupérer leurs droits sauvegardés
  var savedPermsMap = {};
  if (Array.isArray(SAVED_ROLES)) {
    SAVED_ROLES.forEach(function(r) {
      if (r && (r.role_code || r.code_role)) {
        var code = r.role_code || r.code_role;
        savedPermsMap[code] = {
          create: r.create_permission !== undefined ? parseInt(r.create_permission) : 1,
          edit: r.edit_permission !== undefined ? parseInt(r.edit_permission) : 1,
          show: r.show_permission !== undefined ? parseInt(r.show_permission) : 1,
          delete: r.delete_permission !== undefined ? parseInt(r.delete_permission) : 0
        };
      }
    });
  }

  // Dictionnaire des métadonnées de rôles
  var rolesDict = {};
  if (Array.isArray(ALL_ROLES)) {
    ALL_ROLES.forEach(function(r) {
      rolesDict[r.code_role] = r;
    });
  }

  function renderRolePermissions() {
    var selected = $('#sel_roles_user').val() || [];
    if (typeof selected === 'string') selected = [selected];
    var container = $('#rolesPermissionsContainer');
    
    if (!selected.length) {
      container.html('<div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:16px; text-align:center; color:#94A3B8; font-size:13px;">Veuillez sélectionner au moins un rôle ci-dessus pour définir ses permissions.</div>');
      return;
    }

    var html = '';
    selected.forEach(function(roleCode) {
      var rMeta = rolesDict[roleCode] || { libelle_role: roleCode, module: 'Standard', description: '' };
      
      var curCreate = $('#perm_create_' + roleCode).length ? ($('#perm_create_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].create : 1);
      var curEdit   = $('#perm_edit_' + roleCode).length ? ($('#perm_edit_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].edit : 1);
      var curShow   = $('#perm_show_' + roleCode).length ? ($('#perm_show_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].show : 1);
      var curDelete = $('#perm_delete_' + roleCode).length ? ($('#perm_delete_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].delete : 0);

      html += '<div class="role-perm-card" id="card_role_' + roleCode + '" style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-left: 4px solid var(--primary-color, #18385F); border-radius: 10px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">' +
                '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #F1F5F9;">' +
                  '<div>' +
                    '<strong style="font-size: 14px; color: #1E293B; display: inline-flex; align-items: center; gap: 6px;">' +
                      '<i data-lucide="award" style="width: 16px; height: 16px; color: var(--primary-color);"></i> ' +
                      (rMeta.libelle_role || roleCode) +
                    '</strong>' +
                    '<span style="font-size: 11px; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; margin-left: 8px;">' +
                      (rMeta.groupe || rMeta.module || 'Système') +
                    '</span>' +
                  '</div>' +
                  '<div style="display: flex; gap: 6px;">' +
                    '<button type="button" class="btn btn-sm btn-light" onclick="setRoleAllPerms(\'' + roleCode + '\', true)" style="font-size: 11px; padding: 3px 8px; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC;">Tout autoriser</button>' +
                    '<button type="button" class="btn btn-sm btn-light" onclick="setRoleReadOnly(\'' + roleCode + '\')" style="font-size: 11px; padding: 3px 8px; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC;">Lecture seule</button>' +
                  '</div>' +
                '</div>' +

                '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_create_' + roleCode + '" name="role_perms[' + roleCode + '][create]" value="1" ' + (curCreate ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #16A34A;">' +
                    '<span>Créer (<strong style="color:#16A34A;">Ajouter</strong>)</span>' +
                  '</label>' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_edit_' + roleCode + '" name="role_perms[' + roleCode + '][edit]" value="1" ' + (curEdit ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #0284C7;">' +
                    '<span>Modifier (<strong style="color:#0284C7;">Éditer</strong>)</span>' +
                  '</label>' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_show_' + roleCode + '" name="role_perms[' + roleCode + '][show]" value="1" ' + (curShow ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #475569;">' +
                    '<span>Consulter (<strong style="color:#475569;">Afficher</strong>)</span>' +
                  '</label>' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_delete_' + roleCode + '" name="role_perms[' + roleCode + '][delete]" value="1" ' + (curDelete ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #DC2626;">' +
                    '<span>Supprimer (<strong style="color:#DC2626;">Effacer</strong>)</span>' +
                  '</label>' +
                '</div>' +
              '</div>';
    });

    container.html(html);
    if (window.lucide) lucide.createIcons();
  }

  window.setRoleAllPerms = function(roleCode, allow) {
    $('#perm_create_' + roleCode).prop('checked', allow);
    $('#perm_edit_' + roleCode).prop('checked', allow);
    $('#perm_show_' + roleCode).prop('checked', allow);
    $('#perm_delete_' + roleCode).prop('checked', allow);
  };

  window.setRoleReadOnly = function(roleCode) {
    $('#perm_create_' + roleCode).prop('checked', false);
    $('#perm_edit_' + roleCode).prop('checked', false);
    $('#perm_show_' + roleCode).prop('checked', true);
    $('#perm_delete_' + roleCode).prop('checked', false);
  };

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_fonction_user').select2({ placeholder: "-- Sélectionner un poste --", allowClear: true, width: '100%' });
    $('#sel_roles_user').select2({ placeholder: "Sélectionnez un ou plusieurs rôles", closeOnSelect: false, width: '100%' });
    $('#sel_roles_user').on('change', renderRolePermissions);
  }

  // Soumission AJAX du formulaire avec retour dynamique et redirection
  $('#form-user').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-save-user');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Enregistrement en cours...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Utilisateur enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Utilisateur enregistré avec succès');
          setTimeout(function() {
            window.location.href = res.redirect || (racine + 'user/list');
          }, 600);
        } else {
          $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer l\'Utilisateur & Permissions');
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer l\'Utilisateur & Permissions');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Premier rendu à l'ouverture
  renderRolePermissions();

  }

  // =========================================================================
  // 3. VUE : MODIFICATION DU MOT DE PASSE (views/users/editPassword.php)
  // =========================================================================
  function initUsersEditPasswordModule() {
    if (!$('#formEditPassword').length) return;

  const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/'));

  // Toggle afficher/masquer mot de passe
  $(document).on('click', '.btn-toggle-pwd', function() {
    const targetId = $(this).data('target');
    const input = $('#' + targetId);
    const icon = $(this).find('i');

    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      input.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  // Soumission du formulaire
  $('#formEditPassword').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btn_actions');
    const newPwd = $('#new_password').val();
    const confirmPwd = $('#confirm_password').val();

    if (newPwd !== confirmPwd) {
      if (typeof showToast === 'function') showToast('Les nouveaux mots de passe ne correspondent pas !', 'error');
      return;
    }

    if (typeof loading === 'function') loading(btn, true, 'Mise à jour en cours...');

    $.ajax({
      url: baseApi + 'user/editPassword',
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(rep) {
        if (typeof loading === 'function') loading(btn, false);
        if (rep.status) {
          if (typeof showToast === 'function') showToast(rep.message || 'Mot de passe modifié avec succès !', 'success');
          form[0].reset();
          setTimeout(function() {
            window.location.href = baseApi + 'user/profil';
          }, 1000);
        } else {
          if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de la modification', 'error');
        }
      },
      error: function(xhr) {
        if (typeof loading === 'function') loading(btn, false);
        let msg = 'Erreur serveur';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        if (typeof showToast === 'function') showToast(msg, 'error');
      }
    });
  });

  }

  $(document).ready(function() {
    initUsersListModule();
    initUsersEditModule();
    initUsersEditPasswordModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);