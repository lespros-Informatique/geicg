<?php
require_once __DIR__ . '/../../public/inc/header.php';
$role = isset($role) ? $role : [];
$encryptedId = isset($encryptedId) ? $encryptedId : '';
$allPermissions = isset($allPermissions) ? $allPermissions : [];
$assignedCodes = isset($assignedCodes) ? $assignedCodes : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="shield" style="color: #2563EB;"></i> <?= isset($role['id_role']) ? 'Configuration du Rôle : ' . htmlspecialchars($role['libelle_role']) : 'Ajouter un Rôle' ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Définition des accès et permissions attribuées aux utilisateurs</p>
        </div>
        <a href="<?= RACINE ?>role/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i class="fa fa-arrow-left"></i> Retour aux rôles
        </a>
      </div>

      <!-- 1. FORMULAIRE INFORMATIONS DU RÔLE -->
      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 22px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="info" style="color: #2563EB; width: 18px; height: 18px;"></i> Informations du rôle
        </h2>
        
        <form class="formEditRole" id="formEditRole">
          <?= Validator::csrfField() ?>
          <input type="hidden" id="id_role" name="id_role" value="<?= htmlspecialchars($role['id_role'] ?? '') ?>">

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Code Rôle *</label>
              <input type="text" class="form-control" id="code_role" name="code_role"
                     placeholder="ex: ROLE-GEST, ROLE-LIV..."
                     value="<?= htmlspecialchars($role['code_role'] ?? '') ?>" <?= isset($role['id_role']) ? 'readonly' : 'required' ?>
                     style="background: <?= isset($role['id_role']) ? '#F8FAFC' : '#FFF' ?>; font-weight: 700;">
            </div>

            <div class="form-group">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Libellé Rôle *</label>
              <input type="text" class="form-control" id="libelle_role" name="libelle_role"
                     placeholder="ex: Gérant de Pressing, Caissier..."
                     value="<?= htmlspecialchars($role['libelle_role'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 18px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Description</label>
            <textarea class="form-control" id="description_role" name="description_role" rows="2" placeholder="Description des fonctions de ce rôle..."><?= htmlspecialchars($role['description_role'] ?? '') ?></textarea>
          </div>

          <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary btn_actions" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Sauvegarder les informations
            </button>
          </div>
        </form>
      </div>

      <!-- 2. SECTION PERMISSIONS AVEC ACCORDÉONS ET BOUTONS TOUT COCHER / TOUT DÉCOCHER -->
      <?php if (isset($role['id_role'])): ?>
      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- HEADER DE LA SECTION PERMISSIONS + BOUTONS GLOBAUX -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
          <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="key" style="color: #2563EB; width: 18px; height: 18px;"></i> Permissions & Privilèges du rôle
            </h2>
            <small style="color: #64748B;">Cochez les droits attribués aux utilisateurs disposant de ce rôle</small>
          </div>

          <!-- BOUTONS GLOBAUX -->
          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn btn-sm btn-primary" onclick="checkAllGlobal()" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 700;">
              <i class="fa fa-check-double"></i> Tout cocher
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="uncheckAllGlobal()" style="display: inline-flex; align-items: center; gap: 5px;">
              <i class="fa fa-times"></i> Tout décocher
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="expandAllGroups()" style="display: inline-flex; align-items: center; gap: 4px;" title="Tout déplier">
              <i class="fa fa-chevron-down"></i> Déplier
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="collapseAllGroups()" style="display: inline-flex; align-items: center; gap: 4px;" title="Tout replier">
              <i class="fa fa-chevron-up"></i> Replier
            </button>
          </div>
        </div>

        <form id="formPermissions">
          <?= Validator::csrfField() ?>
          <input type="hidden" id="id_role_permissions" name="id_role" value="<?= htmlspecialchars($role['id_role'] ?? '') ?>">

          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php 
            $groupIndex = 0;
            foreach ($allPermissions as $group => $items): 
              $groupIndex++;
              $groupId = 'grp_' . $groupIndex;
            ?>
              <!-- ACCORDÉON DU GROUPE / MODULE -->
              <div class="permission-group-box" id="group-<?= $groupId ?>" data-group-id="<?= $groupId ?>" 
                   style="border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; background: #FFFFFF; transition: box-shadow 0.15s ease;">
                
                <!-- EN-TÊTE DU GROUPE (CLIQUABLE) -->
                <div class="permission-group-header" onclick="toggleGroupAccordion('<?= $groupId ?>')"
                     style="padding: 12px 18px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none;">
                  
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-chevron-down toggle-chevron" id="chevron-<?= $groupId ?>" style="color: #64748B; font-size: 13px; transition: transform 0.2s ease;"></i>
                    <strong style="color: #1E293B; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                      <?= htmlspecialchars($group) ?>
                    </strong>
                    <span id="badge-<?= $groupId ?>" style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px; background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                      0 / <?= count($items) ?>
                    </span>
                  </div>

                  <!-- BOUTONS DU MODULE -->
                  <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                    <button type="button" class="btn btn-sm" onclick="checkGroup('<?= $groupId ?>', event)" 
                            style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
                      <i class="fa fa-check"></i> Tout cocher
                    </button>
                    <button type="button" class="btn btn-sm" onclick="uncheckGroup('<?= $groupId ?>', event)" 
                            style="background: #FFF; color: #64748B; border: 1px solid #CBD5E1; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                      <i class="fa fa-times"></i> Décocher
                    </button>
                  </div>
                </div>

                <!-- CORPS DU GROUPE (CHECKBOXES) -->
                <div class="permission-group-body" id="body-<?= $groupId ?>" style="padding: 16px 18px; display: block;">
                  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;">
                    <?php foreach ($items as $p): ?>
                      <?php $isChecked = in_array($p['code_permission'], $assignedCodes, true); ?>
                      <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1px solid #E2E8F0; border-radius: 8px; background: #FAFAFA; cursor: pointer; transition: all 0.15s ease;"
                             onmouseover="this.style.background='#F1F5F9'; this.style.borderColor='#CBD5E1';" 
                             onmouseout="this.style.background='#FAFAFA'; this.style.borderColor='#E2E8F0';">
                        <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($p['code_permission']) ?>" 
                               <?= $isChecked ? 'checked' : '' ?> style="margin-top: 3px; cursor: pointer; width: 16px; height: 16px;">
                        <div style="flex: 1;">
                          <strong style="display: block; font-size: 13px; color: #1E293B; line-height: 1.3;">
                            <?= htmlspecialchars($p['libelle_permission'] ?: $p['code_permission']) ?>
                          </strong>
                          <?php if (!empty($p['description_permission'])): ?>
                            <small style="color: #64748B; font-size: 11px; line-height: 1.3; display: block; margin-top: 2px;">
                              <?= htmlspecialchars($p['description_permission']) ?>
                            </small>
                          <?php endif; ?>
                        </div>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>

              </div>
            <?php endforeach; ?>
          </div>

          <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
            <button type="submit" class="btn btn-primary btn_actions" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 10px 20px;">
              <i data-lucide="save" style="width: 18px; height: 18px;"></i> Enregistrer les permissions du rôle
            </button>
          </div>
        </form>
      </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>public/json/mobile-list.js"></script>
<script src="<?= RACINE ?>public/json/entities/roles.js?v=2"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
