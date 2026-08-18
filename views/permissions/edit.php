<?php
require_once __DIR__ . '/../../public/inc/header.php';
$permission = isset($permission) ? $permission : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <h1><?= isset($permission['id_permission']) ? 'Modifier la permission' : 'Ajouter une permission' ?></h1>
        <a href="<?= RACINE ?>permission/list" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Retour</a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <h2>Informations de la permission</h2>
        </div>
        <div class="card-body">
          <form class="formEditPermission">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_permission" name="id_permission" value="<?= htmlspecialchars($permission['id_permission'] ?? '') ?>">

            <div class="form-grid">
              <div class="form-field">
                <label for="code_permission">Code</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('hash'); ?></span>
                  <input type="text" class="form-control" id="code_permission" name="code_permission"
                         value="<?= htmlspecialchars($permission['code_permission'] ?? '') ?>" <?= isset($permission['id_permission']) ? 'readonly' : 'required' ?>>
                </div>
                <div class="error-message" id="codeError"></div>
              </div>

              <div class="form-field">
                <label for="libelle_permission">Libellé</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                  <input type="text" class="form-control" id="libelle_permission" name="libelle_permission"
                         value="<?= htmlspecialchars($permission['libelle_permission'] ?? '') ?>" required>
                </div>
                <div class="error-message" id="libelleError"></div>
              </div>

              <div class="form-field" style="grid-column: 1 / -1;">
                <label for="description_permission">Description</label>
                <textarea class="form-control" id="description_permission" name="description_permission" rows="3"><?= htmlspecialchars($permission['description_permission'] ?? '') ?></textarea>
                <div class="error-message" id="descriptionError"></div>
              </div>

              <?php if (isset($permission['statut_permission'])): ?>
              <div class="form-field">
                <label for="statut_permission">Statut</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                  <select class="form-control" id="statut_permission" name="statut_permission">
                    <option value="actif" <?= ($permission['statut_permission'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= ($permission['statut_permission'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                  </select>
                </div>
                <div class="error-message" id="statutError"></div>
              </div>
              <?php endif; ?>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditPermission">
                <span class="btn-text"><i data-lucide="save"></i> Sauvegarder</span>
              </button>
              <a href="<?= RACINE ?>permission/list" class="btn btn-secondary"><i data-lucide="x"></i> Annuler</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/entities/permissions.js?v=1"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
