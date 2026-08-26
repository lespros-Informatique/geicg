<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$role = isset($role) ? $role : [];
$roles = isset($roles) ? $roles : (new ModelRole())->getAll();
$fonctions = isset($fonctions) ? $fonctions : (new ModelFonction())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= isset($user['id_user']) ? 'Modifier l\'Utilisateur' : 'Créer un Compte Utilisateur' ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion des accès, des attributions de rôles et des droits CRUD</p>
        </div>
        <a href="<?= RACINE ?>user/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>user/<?= !empty($user['id_user']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($user['id_user'])): ?>
            <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="user" style="width: 18px; height: 18px;"></i> Identité & Coordonnées
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de famille <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="nom" value="<?= htmlspecialchars($user['nom_user'] ?? '') ?>" placeholder="Ex: KOUASSI" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Prénom(s)</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="prenom" value="<?= htmlspecialchars($user['prenom_user'] ?? '') ?>" placeholder="Ex: Jean-Marc">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse Email (Identifiant de connexion)</label>
              <input type="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="email" value="<?= htmlspecialchars($user['email_user'] ?? '') ?>" placeholder="Ex: utilisateur@geicg.ci">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro Téléphone</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="telephone" value="<?= htmlspecialchars($user['telephone_user'] ?? '') ?>" placeholder="Ex: (+225) 07 08 09 10 11">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Fonction / Poste</label>
              <select class="form-control select2" id="sel_fonction_user" name="fonction_code" style="width: 100%;">
                <option value="">-- Sélectionner un poste --</option>
                <?php foreach($fonctions as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_fonction']) ?>" <?= (($user['fonction_code'] ?? '') == $f['code_fonction']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['libelle_fonction']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if (empty($user['id_user'])): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Mot de passe initial <span style="color: #EF4444;">*</span>
              </label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="password" value="123456" placeholder="123456" required>
              <small style="color: #64748B; font-size: 11px; margin-top: 4px; display: block;">Mot de passe de première connexion (défaut : 123456).</small>
            </div>
            <?php endif; ?>
          </div>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 24px 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="shield" style="width: 18px; height: 18px;"></i> Attribution de Rôle & Droits d'Accès
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Rôle principal attribué <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_role_user" name="role_code" style="width: 100%;" required>
                <option value="">-- Sélectionner un rôle --</option>
                <?php foreach($roles as $r): ?>
                  <option value="<?= htmlspecialchars($r['code_role']) ?>" <?= (($role['role_code'] ?? ($role['code_role'] ?? '')) == $r['code_role']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['libelle_role'] . ' (' . ($r['groupe'] ?? $r['module']) . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if (!empty($user['id_user'])): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut du compte</label>
              <select class="form-control" name="actif" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="actif" <?= (($user['statut_user'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Compte Actif (Autorisé)</option>
                <option value="inactif" <?= (($user['statut_user'] ?? '') === 'inactif') ? 'selected' : '' ?>>Compte Inactif (Bloqué)</option>
              </select>
            </div>
            <?php endif; ?>
          </div>

          <!-- Matrice CRUD -->
          <div style="background: #F8FAFC; border-radius: 10px; padding: 18px; border: 1px solid #E2E8F0; margin-bottom: 24px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #1E293B; margin-bottom: 12px;">
              <i data-lucide="key" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle;"></i>
              Privilèges d'Actions Granulaires (CRUD) pour cet utilisateur :
            </label>
            <div style="display: flex; flex-wrap: wrap; gap: 24px;">
              <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">
                <input type="checkbox" name="create_permission" value="1" <?= (!isset($role['create_permission']) || $role['create_permission'] == 1) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #1E3A5F;">
                <span>Création (<strong style="color:#15803D;">Ajouter</strong>)</span>
              </label>
              <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">
                <input type="checkbox" name="edit_permission" value="1" <?= (!isset($role['edit_permission']) || $role['edit_permission'] == 1) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #1E3A5F;">
                <span>Modification (<strong style="color:#0284C7;">Éditer</strong>)</span>
              </label>
              <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">
                <input type="checkbox" name="show_permission" value="1" <?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #1E3A5F;">
                <span>Consultation (<strong style="color:#475569;">Afficher</strong>)</span>
              </label>
              <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer;">
                <input type="checkbox" name="delete_permission" value="1" <?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #1E3A5F;">
                <span>Suppression (<strong style="color:#DC2626;">Supprimer</strong>)</span>
              </label>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer l'Utilisateur</button>
            <a href="<?= RACINE ?>user/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_fonction_user').select2({ placeholder: "-- Sélectionner un poste --", allowClear: true, width: '100%' });
    $('#sel_role_user').select2({ placeholder: "-- Sélectionner un rôle --", allowClear: true, width: '100%' });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
