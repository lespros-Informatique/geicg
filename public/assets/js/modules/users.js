/**
 * Module JS Unifié : Utilisateurs Système, Rôles & Permissions RBAC
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

  // --- Fonctions globales de gestion des Rôles & Permissions ---
// Toggle ouvrir / réduire un module avec rotation du chevron
window.toggleAccordion = function(grpId) {
  var $body = $('#body-' + grpId);
  var $wrap = $('#chevron-wrap-' + grpId);
  if ($body.is(':visible')) {
    $body.slideUp(180);
    $wrap.css('transform', 'rotate(-90deg)');
  } else {
    $body.slideDown(180);
    $wrap.css('transform', 'rotate(0deg)');
  }
};

// Déplier ou replier tous les modules
window.toggleAllGroups = function(open) {
  if (open) {
    $('.module-body').slideDown(180);
    $('[id^="chevron-wrap-"]').css('transform', 'rotate(0deg)');
  } else {
    $('.module-body').slideUp(180);
    $('[id^="chevron-wrap-"]').css('transform', 'rotate(-90deg)');
  }
};

// Cocher ou décocher toutes les permissions d'un module spécifique
window.checkModulePerms = function(grpId, checked, evt) {
  if (evt) evt.stopPropagation();
  $('.perm-group-' + grpId).prop('checked', checked);
  var total = $('.perm-group-' + grpId).length;
  updateGroupBadge(grpId, total);
};

// Cocher ou décocher globalement toutes les permissions
window.checkAllGlobal = function(checked) {
  $('.perm-checkbox').prop('checked', checked);
  $('[id^="box-"]').each(function() {
    var grpId = $(this).attr('id').replace('box-', '');
    var total = $('.perm-group-' + grpId).length;
    updateGroupBadge(grpId, total);
  });
};

// Mise à jour en temps réel du badge "X / Y cochées"
window.updateGroupBadge = function(grpId, total) {
  var checkedCount = $('.perm-group-' + grpId + ':checked').length;
  $('#badge-' + grpId).text(checkedCount + ' / ' + total + ' cochées');
  if (checkedCount === total) {
    $('#badge-' + grpId).css({'background': '#DCFCE7', 'color': '#15803D', 'borderColor': '#86EFAC'});
  } else if (checkedCount > 0) {
    $('#badge-' + grpId).css({'background': '#EFF6FF', 'color': '#1E3A5F', 'borderColor': '#BFDBFE'});
  } else {
    $('#badge-' + grpId).css({'background': '#F1F5F9', 'color': '#64748B', 'borderColor': '#CBD5E1'});
  }
};

var isCustomModule = false;
window.toggleModuleInput = function() {
  isCustomModule = !isCustomModule;
  if (isCustomModule) {
    $('#bloc-select-module').hide();
    $('#bloc-text-module').show();
    $('#input_nouveau_module').focus();
    $('#btn-toggle-module').html('<i data-lucide="list" style="width:14px;height:14px;"></i> Choisir un module existant');
    $('#help-module').text('Saisissez le nom en lettres majuscules (ex: BIBLIOTHEQUE).');
  } else {
    $('#bloc-text-module').hide();
    $('#input_nouveau_module').val('');
    $('#bloc-select-module').show();
    $('#btn-toggle-module').html('<i data-lucide="plus-circle" style="width:14px;height:14px;"></i> + Créer un nouveau module');
    $('#help-module').text('Choisissez parmi les modules existants ou cliquez ci-dessus pour en créer un nouveau.');
  }
  if (window.lucide) lucide.createIcons();
};

  // =========================================================================
  // 4. VUE : LISTE & GESTION DES RÔLES RBAC (views/roles/list.php)
  // =========================================================================
  function initRolesListModule() {
    if (!$('#table-roles, #roles-list-config').length) return;

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

  }

  // =========================================================================
  // 5. VUE : CRÉATION / MODIFICATION D'UN RÔLE RBAC (views/roles/edit.php)
  // =========================================================================
  function initRolesEditModule() {
    if (!$('#btn-save-role, #roles-edit-config, .perm-checkbox').length) return;
 
  var racine = window.RACINE || '/';
  if (window.lucide) lucide.createIcons(); 

  // Soumission AJAX du formulaire avec retour dynamique et redirection
  $('form').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-save-role');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Enregistrement en cours...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Rôle enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Rôle enregistré avec succès');
          setTimeout(function() {
            window.location.href = res.redirect || (racine + 'role/list');
          }, 600);
        } else {
          $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer le Rôle & Permissions');
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer le Rôle & Permissions');
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
  }

  // =========================================================================
  // 6. VUE : LISTE & GESTION DES PERMISSIONS GRANULAIRES (views/permissions/list.php)
  // =========================================================================
  function initPermissionsListModule() {
    if (!$('#table-permissions, #permissions-list-config').length) return;

  var configEl = document.getElementById('permissions-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-permissions').DataTable({
    ajax: racine + 'permission/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_permission', render: function(d) {
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      } },
      { data: 'libelle_permission', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      } },
      { data: 'module_permission', render: function(d) {
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:700; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</span>';
      } },
      { data: 'statut_permission', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-permission" data-id="' + row.id_permission + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + racine + 'permission/edition/' + (d.editId || d.id_permission) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + racine + 'permission/details/' + (d.editId || d.id_permission) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.toggle-statut-permission', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'permission/changer',
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
  // 7. VUE : CRÉATION / MODIFICATION D'UNE PERMISSION (views/permissions/edit.php)
  // =========================================================================
  function initPermissionsEditModule() {
    if (!$('#btn-toggle-module, #bloc-select-module').length) return;
 
  if (window.lucide) lucide.createIcons();

  }

  // =========================================================================
  // 8. VUE : MOT DE PASSE OUBLIÉ (views/users/forgot_password.php)
  // =========================================================================
  function initForgotPasswordModule() {
    if (!$('#form-forgot-password').length) return;

    var cfg = {};
    var cfgEl = document.getElementById('forgot-password-config');
    if (cfgEl) {
      try { cfg = JSON.parse(cfgEl.textContent || '{}'); } catch (e) {}
    }
    var actionUrl = cfg.actionUrl || ((window.RACINE || '/') + 'user/motDePasseOublie');

    $('#form-forgot-password').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $('#btn-submit-forgot');
      var $feedback = $('#forgot-password-feedback');
      var originalHtml = $btn.html();

      $feedback.hide();
      $btn.prop('disabled', true).html('<span class="btn-text"><i data-lucide="loader"></i> Envoi en cours...</span>');
      if (window.lucide) lucide.createIcons();

      $.ajax({
        url: actionUrl,
        type: 'POST',
        data: $form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();

          var isSuccess = (res && (res.status === 1 || res.status === true || res.success));
          var msg = (res && res.message) ? res.message : (isSuccess ? 'Demande traitée avec succès.' : 'Une erreur est survenue.');

          if (isSuccess) {
            $feedback.css({ 'background': '#F0FDF4', 'border': '1px solid #BBF7D0', 'color': '#15803D' }).text(msg).fadeIn();
            if (typeof showToast === 'function') showToast(msg, 'success');
            $form[0].reset();
          } else {
            $feedback.css({ 'background': '#FEF2F2', 'border': '1px solid #FECACA', 'color': '#B91C1C' }).text(msg).fadeIn();
            if (typeof showToast === 'function') showToast(msg, 'error');
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur de communication avec le serveur.';
          try {
            var json = xhr.responseJSON || JSON.parse(xhr.responseText);
            if (json && json.message) msg = json.message;
          } catch (err) {}
          $feedback.css({ 'background': '#FEF2F2', 'border': '1px solid #FECACA', 'color': '#B91C1C' }).text(msg).fadeIn();
          if (typeof showToast === 'function') showToast(msg, 'error');
        }
      });
    });
  }

  // =========================================================================
  // 9. VUE : RÉINITIALISATION DU MOT DE PASSE (views/users/reset_password.php)
  // =========================================================================
  function initResetPasswordModule() {
    if (!$('#form-reset-password').length) return;

    var cfg = {};
    var cfgEl = document.getElementById('reset-password-config');
    if (cfgEl) {
      try { cfg = JSON.parse(cfgEl.textContent || '{}'); } catch (e) {}
    }
    var actionUrl = cfg.actionUrl || ((window.RACINE || '/') + 'user/reset-password');
    var loginUrl = cfg.loginUrl || ((window.RACINE || '/') + 'user/connexion');

    $(document).on('click', '.btn-toggle-reset-pwd', function() {
      var targetSel = $(this).data('target');
      var $input = $(targetSel);
      if (!$input.length) return;
      var isPwd = ($input.attr('type') === 'password');
      $input.attr('type', isPwd ? 'text' : 'password');
      $(this).html('<i data-lucide="' + (isPwd ? 'eye-off' : 'eye') + '"></i>');
      if (window.lucide) lucide.createIcons();
    });

    $('#form-reset-password').on('submit', function(e) {
      e.preventDefault();
      var newPwd = $('#reset_new_password').val() || '';
      var confPwd = $('#reset_confirm_password').val() || '';
      var $feedback = $('#reset-password-feedback');

      if (newPwd.length < 6) {
        $feedback.css({ 'background': '#FEF2F2', 'border': '1px solid #FECACA', 'color': '#B91C1C' }).text('Le mot de passe doit contenir au moins 6 caractères.').fadeIn();
        return;
      }
      if (newPwd !== confPwd) {
        $feedback.css({ 'background': '#FEF2F2', 'border': '1px solid #FECACA', 'color': '#B91C1C' }).text('La confirmation du mot de passe ne correspond pas.').fadeIn();
        return;
      }

      var $form = $(this);
      var $btn = $('#btn-submit-reset');
      var originalHtml = $btn.html();

      $feedback.hide();
      $btn.prop('disabled', true).html('<span class="btn-text"><i data-lucide="loader"></i> Mise à jour...</span>');
      if (window.lucide) lucide.createIcons();

      $.ajax({
        url: actionUrl,
        type: 'POST',
        data: $form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          var isSuccess = (res && (res.status === 1 || res.status === true || res.success));
          var msg = (res && res.message) ? res.message : (isSuccess ? 'Mot de passe réinitialisé !' : 'Erreur lors de la réinitialisation.');

          if (isSuccess) {
            $feedback.css({ 'background': '#F0FDF4', 'border': '1px solid #BBF7D0', 'color': '#15803D' }).text(msg).fadeIn();
            if (typeof showToast === 'function') showToast(msg, 'success');
            setTimeout(function() {
              window.location.href = (res && res.redirect) ? res.redirect : loginUrl;
            }, 1400);
          } else {
            $btn.prop('disabled', false).html(originalHtml);
            if (window.lucide) lucide.createIcons();
            $feedback.css({ 'background': '#FEF2F2', 'border': '1px solid #FECACA', 'color': '#B91C1C' }).text(msg).fadeIn();
            if (typeof showToast === 'function') showToast(msg, 'error');
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur de communication avec le serveur.';
          try {
            var json = xhr.responseJSON || JSON.parse(xhr.responseText);
            if (json && json.message) msg = json.message;
          } catch (err) {}
          $feedback.css({ 'background': '#FEF2F2', 'border': '1px solid #FECACA', 'color': '#B91C1C' }).text(msg).fadeIn();
          if (typeof showToast === 'function') showToast(msg, 'error');
        }
      });
    });
  }

  // =========================================================================
  // 10. VUE : DÉTAILS D'UN COMPTE UTILISATEUR (views/users/details.php)
  // =========================================================================
  function initUsersDetailsModule() {
    if (!$('#users-details-config').length) return;

    var cfg = {};
    try {
      var cfgEl = document.getElementById('users-details-config');
      if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
    } catch (e) {}

    var racine = cfg.racine || window.RACINE || '/';
    var csrfToken = cfg.csrfToken || '';
    var userId = cfg.userId || 0;
    var isActif = !!cfg.isActif;
    var nomComplet = cfg.nomComplet || '';

    function notify(msg, type) {
      if (typeof window.showToast === 'function') {
        window.showToast(msg, type || 'info');
      } else if (window.toastr) {
        if (type === 'error') toastr.error(msg);
        else toastr.success(msg);
      }
    }

    // Filtrage combiné module + recherche sur les permissions RBAC effectives
    function filterUserPermissions() {
      var selectedMod = ($('#filter-user-perm-module').val() || '').toUpperCase();
      var q = ($('#search-user-permissions').val() || '').toLowerCase().trim();
      var totalVisible = 0;

      $('#user-permissions-list .user-perm-module-group').each(function() {
        var $grp = $(this);
        var grpMod = String($grp.data('module') || '').toUpperCase();
        var modMatch = (!selectedMod || grpMod === selectedMod);
        var grpVisible = 0;

        $grp.find('.user-perm-item').each(function() {
          var haystack = String($(this).data('search') || '').toLowerCase();
          var show = modMatch && (!q || haystack.indexOf(q) !== -1);
          $(this).toggle(show);
          if (show) {
            grpVisible++;
            totalVisible++;
          }
        });

        $grp.toggle(grpVisible > 0);
      });

      $('#count-visible-user-perms').text(totalVisible);
    }

    $('#filter-user-perm-module').on('change', filterUserPermissions);
    $('#search-user-permissions').on('input', filterUserPermissions);

    // Bascule rapide du statut du compte (Activer / Désactiver)
    $('#btn-toggle-statut-user-detail').on('click', function() {
      if (!userId) return;
      var actionLabel = isActif ? 'désactiver' : 'activer';
      var confirmTitle = isActif ? 'Désactiver ce compte ?' : 'Activer ce compte ?';
      var confirmText = 'Voulez-vous vraiment ' + actionLabel + ' le compte de « ' + nomComplet + ' » ?';

      function executeToggle() {
        $.ajax({
          url: racine + 'user/changer',
          type: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: {
            id: userId,
            id_user: userId,
            csrf_token: csrfToken
          },
          dataType: 'json',
          success: function(res) {
            if (res && (res.status === 1 || res.success)) {
              notify(res.message || 'Statut mis à jour avec succès', 'success');
              setTimeout(function() { window.location.reload(); }, 600);
            } else {
              notify((res && res.message) || 'Erreur lors du changement de statut', 'error');
            }
          },
          error: function() {
            notify('Erreur réseau ou serveur', 'error');
          }
        });
      }

      if (window.Swal) {
        Swal.fire({
          title: confirmTitle,
          text: confirmText,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: isActif ? '#D97706' : '#16A34A',
          cancelButtonColor: '#64748B',
          confirmButtonText: isActif ? 'Oui, désactiver' : 'Oui, activer',
          cancelButtonText: 'Annuler'
        }).then(function(result) {
          if (result.isConfirmed) executeToggle();
        });
      } else if (window.confirm(confirmText)) {
        executeToggle();
      }
    });
  }

  // =========================================================================
  // 11. VUE : MON PROFIL UTILISATEUR (views/users/profil.php)
  // =========================================================================
  function initUsersProfilModule() {
    if (!$('#users-profil-config').length) return;

    var cfg = {};
    try {
      var cfgEl = document.getElementById('users-profil-config');
      if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
    } catch (e) {}

    var racine = cfg.racine || window.RACINE || '/';
    var csrfToken = cfg.csrfToken || '';
    var updateProfilUrl = cfg.updateProfilUrl || (racine + 'user/updateProfil');
    var uploadPhotoUrl = cfg.uploadPhotoUrl || (racine + 'user/uploadPhotoProfil');
    var editPasswordUrl = cfg.editPasswordUrl || (racine + 'user/editPassword');

    function notify(msg, type) {
      if (typeof window.showToast === 'function') {
        window.showToast(msg, type || 'info');
      } else if (window.toastr) {
        if (type === 'error') toastr.error(msg);
        else if (type === 'warning') toastr.warning(msg);
        else toastr.success(msg);
      }
    }

    function showFeedback($box, msg, isSuccess) {
      $box.css({
        background: isSuccess ? '#F0FDF4' : '#FEF2F2',
        border: isSuccess ? '1px solid #BBF7D0' : '1px solid #FECACA',
        color: isSuccess ? '#15803D' : '#B91C1C'
      }).text(msg).fadeIn(180);
    }

    // 1. Navigation par onglets
    function switchProfilTab(tabKey) {
      var $target = $('#tab-panel-' + tabKey);
      if (!$target.length) return;
      $('.profil-tab-btn').removeClass('is-active');
      $('.profil-tab-btn[data-tab="' + tabKey + '"]').addClass('is-active');
      $('.profil-tab-panel').hide();
      $target.show();
      if (window.lucide) lucide.createIcons();
    }

    $(document).on('click', '.profil-tab-btn', function() {
      var tabKey = $(this).data('tab');
      if (tabKey) switchProfilTab(tabKey);
    });

    if (window.location.hash) {
      var hashTab = window.location.hash.replace('#', '');
      if ($('#tab-panel-' + hashTab).length) {
        switchProfilTab(hashTab);
      }
    }

    // 2. Téléversement instantané de la photo de profil
    $('#btn-trigger-photo-profil').on('click', function() {
      $('#input-photo-profil').trigger('click');
    });

    $('#input-photo-profil').on('change', function() {
      var file = this.files && this.files[0];
      if (!file) return;

      if (file.size > 3 * 1024 * 1024) {
        notify("L'image ne doit pas dépasser 3 Mo.", 'error');
        $(this).val('');
        return;
      }
      if (!file.type || file.type.indexOf('image/') !== 0) {
        notify('Veuillez sélectionner un fichier image valide (JPG, PNG, WEBP).', 'error');
        $(this).val('');
        return;
      }

      var $photoImg = $('#profil-hero-photo');
      var prevSrc = $photoImg.attr('src');
      var previewUrl = URL.createObjectURL(file);
      $photoImg.attr('src', previewUrl).css('opacity', '0.6');

      var fd = new FormData();
      fd.append('csrf_token', csrfToken);
      fd.append('photo_user', file);

      $.ajax({
        url: uploadPhotoUrl,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
          $photoImg.css('opacity', '1');
          $('#input-photo-profil').val('');
          if (res && (res.status === 1 || res.success)) {
            var newUrl = (res.data && res.data.photo_url) ? res.data.photo_url : previewUrl;
            $photoImg.attr('src', newUrl);
            // Synchroniser l'avatar dans la barre de navigation s'il existe
            $('.nav-user-avatar, .user-avatar-img').attr('src', newUrl);
            notify(res.message || 'Photo de profil mise à jour !', 'success');
          } else {
            $photoImg.attr('src', prevSrc);
            notify((res && res.message) || 'Erreur lors du téléversement de la photo.', 'error');
          }
        },
        error: function(xhr) {
          $photoImg.css('opacity', '1').attr('src', prevSrc);
          $('#input-photo-profil').val('');
          var msg = 'Erreur réseau lors du téléversement de la photo.';
          try {
            var j = xhr.responseJSON || JSON.parse(xhr.responseText);
            if (j && j.message) msg = j.message;
          } catch (err) {}
          notify(msg, 'error');
        }
      });
    });

    // 3. Mise à jour AJAX des informations personnelles
    $('#form-update-profil').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $('#btn-save-profil');
      var $feedback = $('#profil-infos-feedback');
      var origHtml = $btn.html();

      $feedback.hide();
      $btn.prop('disabled', true).css('opacity', '0.75').text('Enregistrement...');

      $.ajax({
        url: $form.attr('action') || updateProfilUrl,
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
          $btn.prop('disabled', false).css('opacity', '1').html(origHtml);
          if (window.lucide) lucide.createIcons();

          if (res && (res.status === 1 || res.success)) {
            var d = res.data || {};
            if (d.nom_complet) $('#profil-hero-name').text(d.nom_complet);
            $('#profil-hero-email').html('<i data-lucide="mail" style="width: 14px; height: 14px;"></i> ' + $('<div>').text(d.email_user || '-').html());
            $('#profil-hero-tel').html('<i data-lucide="phone" style="width: 14px; height: 14px;"></i> ' + $('<div>').text(d.telephone_user || '-').html());
            if (d.sexe_user !== undefined) {
              var sxTxt = d.sexe_user === 'M' ? 'Masculin' : (d.sexe_user === 'F' ? 'Féminin' : 'Non précisé');
              $('#profil-summary-sexe').text(sxTxt);
            }
            if (d.updated_at) {
              $('#profil-summary-updated').text('Modifié le : ' + d.updated_at);
            }
            if (window.lucide) lucide.createIcons();
            showFeedback($feedback, res.message || 'Profil mis à jour avec succès !', true);
            notify(res.message || 'Profil mis à jour avec succès !', 'success');
          } else {
            var errMsg = (res && res.message) || 'Erreur lors de la mise à jour.';
            showFeedback($feedback, errMsg, false);
            notify(errMsg, 'error');
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).css('opacity', '1').html(origHtml);
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur de communication avec le serveur.';
          try {
            var j = xhr.responseJSON || JSON.parse(xhr.responseText);
            if (j && j.message) msg = j.message;
          } catch (err) {}
          showFeedback($feedback, msg, false);
          notify(msg, 'error');
        }
      });
    });

    // 4. Sécurité : bascule visibilité, indicateur de force & changement mot de passe AJAX
    $(document).on('click', '.btn-toggle-profil-pwd', function() {
      var targetSel = $(this).data('target');
      var $input = $(targetSel);
      if (!$input.length) return;
      var isPwd = ($input.attr('type') === 'password');
      $input.attr('type', isPwd ? 'text' : 'password');
    });

    function evaluatePwdStrength(pwd) {
      if (!pwd) return { pct: 0, color: '#E2E8F0', label: '-' };
      var score = 0;
      if (pwd.length >= 6) score++;
      if (pwd.length >= 10) score++;
      if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
      if (/\d/.test(pwd)) score++;
      if (/[^A-Za-z0-9]/.test(pwd)) score++;

      if (score <= 1) return { pct: 25, color: '#DC2626', label: 'Faible' };
      if (score <= 3) return { pct: 60, color: '#D97706', label: 'Moyen' };
      return { pct: 100, color: '#16A34A', label: 'Fort' };
    }

    function checkPwdMatch() {
      var n = $('#profil_new_password').val() || '';
      var c = $('#profil_confirm_password').val() || '';
      var $hint = $('#profil-pwd-match-hint');
      if (!c) {
        $hint.hide();
        return;
      }
      if (n === c) {
        $hint.css('color', '#15803D').text('✓ Les mots de passe correspondent').show();
      } else {
        $hint.css('color', '#DC2626').text('✕ Les mots de passe ne correspondent pas').show();
      }
    }

    $('#profil_new_password').on('input', function() {
      var st = evaluatePwdStrength($(this).val() || '');
      $('#profil-pwd-strength-bar').css({ width: st.pct + '%', backgroundColor: st.color });
      $('#profil-pwd-strength-label').css('color', st.pct > 0 ? st.color : '#64748B').text('Force du mot de passe : ' + st.label);
      checkPwdMatch();
    });

    $('#profil_confirm_password').on('input', checkPwdMatch);

    $('#form-profil-password').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $('#btn-save-profil-pwd');
      var $feedback = $('#profil-pwd-feedback');
      var newPwd = $('#profil_new_password').val() || '';
      var confPwd = $('#profil_confirm_password').val() || '';

      $feedback.hide();
      if (newPwd.length < 6) {
        showFeedback($feedback, 'Le nouveau mot de passe doit contenir au moins 6 caractères.', false);
        return;
      }
      if (newPwd !== confPwd) {
        showFeedback($feedback, 'La confirmation du mot de passe ne correspond pas.', false);
        return;
      }

      var origHtml = $btn.html();
      $btn.prop('disabled', true).css('opacity', '0.75').text('Mise à jour...');

      $.ajax({
        url: $form.attr('action') || editPasswordUrl,
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
          $btn.prop('disabled', false).css('opacity', '1').html(origHtml);
          if (window.lucide) lucide.createIcons();

          if (res && (res.status === 1 || res.success)) {
            $form[0].reset();
            $('#profil-pwd-strength-bar').css({ width: '0%' });
            $('#profil-pwd-strength-label').css('color', '#64748B').text('Force du mot de passe : -');
            $('#profil-pwd-match-hint').hide();
            showFeedback($feedback, res.message || 'Mot de passe modifié avec succès !', true);
            notify(res.message || 'Mot de passe modifié avec succès !', 'success');
          } else {
            var errMsg = (res && res.message) || 'Erreur lors de la modification du mot de passe.';
            showFeedback($feedback, errMsg, false);
            notify(errMsg, 'error');
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).css('opacity', '1').html(origHtml);
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur réseau lors de la modification du mot de passe.';
          try {
            var j = xhr.responseJSON || JSON.parse(xhr.responseText);
            if (j && j.message) msg = j.message;
          } catch (err) {}
          showFeedback($feedback, msg, false);
          notify(msg, 'error');
        }
      });
    });

    // 5. Filtrage des permissions RBAC effectives
    function filterProfilPerms() {
      var selectedMod = ($('#filter-profil-perm-module').val() || '').toUpperCase();
      var q = ($('#search-profil-perms').val() || '').toLowerCase().trim();
      var visibleTotal = 0;

      $('#profil-permissions-list .profil-perm-group').each(function() {
        var $grp = $(this);
        var grpMod = String($grp.data('module') || '').toUpperCase();
        var modMatch = (!selectedMod || grpMod === selectedMod);
        var grpVisible = 0;

        $grp.find('.profil-perm-item').each(function() {
          var hay = String($(this).data('search') || '').toLowerCase();
          var show = modMatch && (!q || hay.indexOf(q) !== -1);
          $(this).toggle(show);
          if (show) {
            grpVisible++;
            visibleTotal++;
          }
        });
        $grp.toggle(grpVisible > 0);
      });

      $('#profil-perm-visible-count').text(visibleTotal);
    }

    $('#filter-profil-perm-module').on('change', filterProfilPerms);
    $('#search-profil-perms').on('input', filterProfilPerms);
  }

  $(document).ready(function() {
    initUsersListModule();
    initUsersEditModule();
    initUsersEditPasswordModule();
    initRolesListModule();
    initRolesEditModule();
    initPermissionsListModule();
    initPermissionsEditModule();
    initForgotPasswordModule();
    initResetPasswordModule();
    initUsersDetailsModule();
    initUsersProfilModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);