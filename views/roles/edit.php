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
      <div class="page-header">
        <h1><?= isset($role['id_role']) ? 'Modifier le rôle' : 'Ajouter un rôle' ?></h1>
        <a href="<?= RACINE ?>role/list" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Retour</a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <h2>Informations du rôle</h2>
        </div>
        <div class="card-body">
          <form class="formEditRole" id="formEditRole">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_role" name="id_role" value="<?= htmlspecialchars($role['id_role'] ?? '') ?>">

            <div class="form-grid">
              <div class="form-field">
                <label for="code_role">Code</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('hash'); ?></span>
                  <input type="text" class="form-control" id="code_role" name="code_role"
                         value="<?= htmlspecialchars($role['code_role'] ?? '') ?>" <?= isset($role['id_role']) ? 'readonly' : 'required' ?>>
                </div>
                <div class="error-message" id="codeError"></div>
              </div>

              <div class="form-field">
                <label for="libelle_role">Libellé</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                  <input type="text" class="form-control" id="libelle_role" name="libelle_role"
                         value="<?= htmlspecialchars($role['libelle_role'] ?? '') ?>" required>
                </div>
                <div class="error-message" id="libelleError"></div>
              </div>

              <div class="form-field" style="grid-column: 1 / -1;">
                <label for="description_role">Description</label>
                <textarea class="form-control" id="description_role" name="description_role" rows="3"><?= htmlspecialchars($role['description_role'] ?? '') ?></textarea>
                <div class="error-message" id="descriptionError"></div>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions">
                <span class="btn-text"><i data-lucide="save"></i> Sauvegarder</span>
              </button>
              <a href="<?= RACINE ?>role/list" class="btn btn-secondary"><i data-lucide="x"></i> Annuler</a>
            </div>
          </form>
        </div>
      </div>

      <?php if (isset($role['id_role'])): ?>
      <div class="card" style="margin-top: 20px;">
        <div class="card-header"><h3>Permissions du rôle</h3></div>
        <div class="card-body" style="padding: 20px;">
          <form id="formPermissions">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_role_permissions" name="id_role" value="<?= htmlspecialchars($role['id_role'] ?? '') ?>">

            <?php foreach ($allPermissions as $group => $items): ?>
            <div class="permission-group" style="margin-bottom: 20px;">
              <h4 style="margin-bottom: 10px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
                <?= htmlspecialchars($group) ?>
              </h4>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;">
                <?php foreach ($items as $p): ?>
                <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px; background: #fff;">
                  <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($p['code_permission']) ?>" 
                         <?= in_array($p['code_permission'], $assignedCodes, true) ? 'checked' : '' ?>>
                  <span style="font-size: 0.9rem;"><?= htmlspecialchars($p['libelle_permission']) ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions">
                <span class="btn-text"><i data-lucide="save"></i> Enregistrer les permissions</span>
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/entities/roles.js?v=1"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
