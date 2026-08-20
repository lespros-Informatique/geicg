<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$role = isset($role) ? $role : [];
$csrfToken = Validator::generateCsrfToken();
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0;"><?= isset($user['id_user']) ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' ?></h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0;">Gestion des accès et des rôles du personnel</p>
        </div>
        <a href="<?= RACINE ?>user/list" class="btn btn-sm btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 6px; border-radius: 8px;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
          Retour à la liste
        </a>
      </div>

      <div class="card" style="border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 16px rgba(0,0,0,0.03); overflow: hidden;">
        <div class="card-header" style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E293B; margin: 0;">Informations de l'utilisateur</h2>
          <?php if (isset($user['statut_user'])): ?>
            <span class="badge-status <?= $user['statut_user'] == 'actif' ? 'delivered' : 'cancelled' ?>" style="font-size: 12px; padding: 4px 10px;">
              <?= $user['statut_user'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body" style="padding: 28px;">
          <form class="formEditUser" style="width: 100%;">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_user" name="id_user" value="<?= htmlspecialchars($user['id_user'] ?? '') ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
              
              <div class="form-field">
                <label for="nom" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Nom *</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user'); ?></span>
                  <input type="text" class="form-control" id="nom" name="nom"
                         placeholder="Nom" value="<?= htmlspecialchars($user['nom_user'] ?? '') ?>" required style="border-radius: 10px; height: 44px;">
                </div>
                <div class="error-message" id="nomError"></div>
              </div>

              <div class="form-field">
                <label for="prenom" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Prénom(s)</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user'); ?></span>
                  <input type="text" class="form-control" id="prenom" name="prenom"
                         placeholder="Ex: Jean-Marc" value="<?= htmlspecialchars($user['prenom_user'] ?? '') ?>" style="border-radius: 10px; height: 44px;">
                </div>
                <div class="error-message" id="prenomError"></div>
              </div>

              <div class="form-field">
                <label for="telephone" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Téléphone (Login de connexion) *</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('contact'); ?></span>
                  <input type="text" class="form-control" id="telephone" name="telephone"
                         placeholder="Ex: 0708091011" maxlength="10" value="<?= htmlspecialchars($user['telephone_user'] ?? '') ?>" required style="border-radius: 10px; height: 44px;">
                </div>
                <div class="error-message" id="telephoneError"></div>
              </div>

              <div class="form-field">
                <label for="email" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Email (Optionnel)</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                  <input type="email" class="form-control" id="email" name="email"
                         placeholder="Ex: agent@lavex.ci (Optionnel)" value="<?= htmlspecialchars($user['email_user'] ?? '') ?>" style="border-radius: 10px; height: 44px;">
                </div>
                <div class="error-message" id="emailError"></div>
              </div>

              <div class="form-field">
                <label for="role_code" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Rôle attribué *</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user-cog'); ?></span>
                  <select class="form-control" id="role_code" name="role_code" required style="border-radius: 10px; height: 44px;">
                    <option value="">Sélectionner un rôle</option>
                    <?php if ($isSuperAdmin): ?>
                      <option value="ROLE-ADMIN" <?= ($role['code_role'] ?? '') == 'ROLE-ADMIN' ? 'selected' : '' ?>>👑 Administrateur</option>
                    <?php endif; ?>
                    <option value="ROLE-PRO" <?= ($role['code_role'] ?? '') == 'ROLE-PRO' ? 'selected' : '' ?>>👑 Propriétaire</option>
                    <option value="ROLE-GEST" <?= ($role['code_role'] ?? '') == 'ROLE-GEST' ? 'selected' : '' ?>>💼 Gestionnaire</option>
                    <option value="ROLE-LIV" <?= ($role['code_role'] ?? '') == 'ROLE-LIV' ? 'selected' : '' ?>>🛵 Livreur</option>
                  </select>
                </div>
                <div class="error-message" id="roleError"></div>
              </div>

              <?php if (!isset($user['id_user'])): ?>
              <div class="form-field">
                <label for="password" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Mot de passe par défaut</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('lock'); ?></span>
                  <input type="text" class="form-control" id="password" name="password"
                         value="12345" readonly style="border-radius: 10px; height: 44px; background-color: #F1F5F9; color: #475569; font-weight: 700; cursor: not-allowed;">
                </div>
              </div>
              <?php endif; ?>

              <?php if (isset($user['statut_user'])): ?>
              <div class="form-field">
                <label for="actif" style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">Statut de compte</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                  <select class="form-control" id="actif" name="actif" style="border-radius: 10px; height: 44px;">
                    <option value="1" <?= ($user['statut_user'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="0" <?= ($user['statut_user'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                  </select>
                </div>
                <div class="error-message" id="actifError"></div>
              </div>
              <?php endif; ?>

            </div>

            <div class="form-actions" style="border-top: 1px solid #F1F5F9; padding-top: 20px; margin-top: 10px; display: flex; gap: 12px;">
              <button type="submit" class="btn btn-primary btn_actions btnEditUser" style="border-radius: 10px; padding: 10px 24px; font-weight: 700;">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder l'Utilisateur
                </span>
              </button>
              <a href="<?= RACINE ?>user/list" class="btn btn-secondary" style="border-radius: 10px; padding: 10px 20px;">
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

<script src="<?= RACINE ?>json/entities/users.js?v=<?= time() ?>"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
