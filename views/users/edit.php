<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$csrfToken = Validator::generateCsrfToken();
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($user['id_user']) ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' ?></h1>
          <p class="page-subtitle">Gestion des utilisateurs</p>
        </div>
        <a href="<?= RACINE ?>user/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour Ã  la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations de l'utilisateur</h2>
          </div>
          <?php if (isset($user['statut_user'])): ?>
            <span class="badge-status <?= $user['statut_user'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $user['statut_user'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditUser">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_user" name="id_user" value="<?= htmlspecialchars($user['id_user'] ?? '') ?>">

            <div class="form-grid">
              <div class="form-field">
                <label for="code">Code</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('tag'); ?></span>
                  <input type="text" class="form-control" id="code" name="code"
                         value="<?= htmlspecialchars($user['code_user'] ?? '') ?>">
                </div>
                <div class="error-message" id="codeError"></div>
              </div>

              <div class="form-field">
                <label for="nom">Nom</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user'); ?></span>
                  <input type="text" class="form-control" id="nom" name="nom"
                         value="<?= htmlspecialchars($user['nom_user'] ?? '') ?>" required>
                </div>
                <div class="error-message" id="nomError"></div>
              </div>

              <div class="form-field">
                <label for="prenom">PrÃ©nom</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user'); ?></span>
                  <input type="text" class="form-control" id="prenom" name="prenom"
                         value="<?= htmlspecialchars($user['prenom_user'] ?? '') ?>">
                </div>
                <div class="error-message" id="prenomError"></div>
              </div>

              <div class="form-field">
                <label for="telephone">TÃ©lÃ©phone</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('contact'); ?></span>
                  <input type="text" class="form-control" id="telephone" name="telephone"
                         value="<?= htmlspecialchars($user['telephone_user'] ?? '') ?>">
                </div>
                <div class="error-message" id="telephoneError"></div>
              </div>

              <div class="form-field">
                <label for="role_code">RÃ´le</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user-cog'); ?></span>
                  <select class="form-control" id="role_code" name="role_code">
                    <option value="">SÃ©lectionner un rÃ´le</option>
                    <option value="ROLE-ADMIN" <?= ($role['code_role'] ?? '') == 'ROLE-ADMIN' ? 'selected' : '' ?>>Administrateur</option>
                    <option value="ROLE-PRO" <?= ($role['code_role'] ?? '') == 'ROLE-PRO' ? 'selected' : '' ?>>PropriÃ©taire</option>
                    <option value="ROLE-LIV" <?= ($role['code_role'] ?? '') == 'ROLE-LIV' ? 'selected' : '' ?>>Livreur</option>
                  </select>
                </div>
                <div class="error-message" id="roleError"></div>
              </div>

              <?php if (isset($user['statut_user'])): ?>
              <div class="form-field">
                <label for="actif">Statut</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                  <select class="form-control" id="actif" name="actif">
                    <option value="1" <?= ($user['statut_user'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="0" <?= ($user['statut_user'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                  </select>
                </div>
                <div class="error-message" id="actifError"></div>
              </div>
              <?php endif; ?>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditUser">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>user/list" class="btn btn-secondary">
                <i data-lucide="x"></i>
                Annuler
              </a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/entities/users.js?v=3"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
