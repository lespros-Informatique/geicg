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
        <form id="form-user" action="<?= RACINE ?>user/<?= !empty($user['id_user']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
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
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="telephone" value="<?= htmlspecialchars($user['telephone_user'] ?? '') ?>" placeholder="Ex: 0708091011">
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
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1; background: #F0FDF4; border: 1.5px dashed #86EFAC; border-radius: 8px; padding: 12px 16px;">
              <div style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #16A34A;"></i>
                Sécurité : Mot de passe généré automatiquement
              </div>
              <small style="color: #15803D; font-size: 12px; margin-top: 4px; display: block;">
                Un mot de passe sécurisé sera généré automatiquement par le serveur en arrière-plan et affiché dès l'enregistrement du compte.
              </small>
            </div>
            <?php endif; ?>
          </div>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 24px 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="shield" style="width: 18px; height: 18px;"></i> Attribution des Rôles & Droits d'Accès
          </h3>

          <?php 
            $selectedRoleCodes = isset($userRoleCodes) ? $userRoleCodes : (isset($role['role_code']) ? [$role['role_code']] : []);
          ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                <span>Rôle(s) attribué(s) à l'utilisateur <span style="color: #EF4444;">*</span></span>
                <small style="color: #64748B; font-weight: normal; font-size: 11.5px;">Sélection multiple possible (cumul des accès)</small>
              </label>
              <select class="form-control select2" id="sel_roles_user" name="roles[]" multiple="multiple" style="width: 100%;" required>
                <?php foreach($roles as $r): ?>
                  <option value="<?= htmlspecialchars($r['code_role']) ?>" <?= in_array($r['code_role'], $selectedRoleCodes, true) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['libelle_role'] . ' (' . ($r['groupe'] ?? $r['module']) . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small style="color: #64748B; font-size: 11.5px; margin-top: 6px; display: block;">
                💡 <span style="font-weight: 600;">Exemple :</span> Un utilisateur peut être simultanément <em>Directeur des Études</em> et <em>Enseignant</em>, ou <em>Comptable</em> et <em>Caissier</em>.
              </small>
            </div>

            <?php if (!empty($user['id_user'])): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut du compte</label>
              <select class="form-control" name="actif" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="actif" <?= (($user['statut_user'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Compte Actif (Autorisé)</option>
                <option value="inactif" <?= (($user['statut_user'] ?? '') === STATUTS::INACTIF) ? 'selected' : '' ?>>Compte Inactif (Bloqué)</option>
              </select>
            </div>
            <?php endif; ?>
          </div>

          <!-- Matrice CRUD Granulaire par Rôle -->
          <div style="margin-top: 6px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
              <h4 style="font-size: 14px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="sliders" style="width: 17px; height: 17px; color: var(--primary-color);"></i>
                Permissions d'Action (CRUD) spécifiques par Rôle
              </h4>
              <span style="font-size: 12px; color: #64748B;">Configurez les autorisations pour chaque rôle sélectionné</span>
            </div>

            <div id="rolesPermissionsContainer" style="display: flex; flex-direction: column; gap: 12px;"></div>
          </div>

          <!-- Matrice d'Accès aux Années Antérieures -->
          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 28px 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="calendar-clock" style="width: 18px; height: 18px; color: var(--primary-color);"></i> Accès aux Années Académiques Antérieures
          </h3>
          <p style="font-size: 12.5px; color: #64748B; margin-top: -10px; margin-bottom: 16px;">
            Définissez les privilèges de consultation ou de régularisation/édition accordés à cet utilisateur sur chaque année antérieure.
          </p>

          <?php 
            $allAnneesList = isset($allAnneesList) ? $allAnneesList : (new ModelAnnee())->getAll();
            $userAnneeAcces = isset($userAnneeAcces) ? $userAnneeAcces : (!empty($user['code_user']) ? (new ModelUser())->getUserAnneeAcces($user['code_user']) : []);
          ?>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
              <?php foreach ($allAnneesList as $an): ?>
                <?php 
                  $isActif = (($an['statut_annee'] ?? '') === STATUTS::ACTIF);
                  $curVal = $userAnneeAcces[$an['code_annee']] ?? 'none';
                ?>
                <div style="background: #FFFFFF; border: 1.5px solid <?= $isActif ? '#BBF7D0' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div>
                      <strong style="font-size: 14px; color: #0F172A; display: block;"><?= htmlspecialchars($an['libelle_annee']) ?></strong>
                      <?php if ($isActif): ?>
                        <span style="font-size: 10.5px; font-weight: 700; color: #166534; background: #DCFCE7; padding: 2px 7px; border-radius: 4px; display: inline-block; margin-top: 3px;">Année active (Accès libre)</span>
                      <?php else: ?>
                        <span style="font-size: 10.5px; font-weight: 600; color: #64748B; background: #F1F5F9; padding: 2px 7px; border-radius: 4px; display: inline-block; margin-top: 3px;">Année antérieure</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <?php if ($isActif): ?>
                    <div style="font-size: 12px; color: #15803D; font-weight: 600; background: #F0FDF4; padding: 8px 10px; border-radius: 6px; border: 1px solid #DCFCE7;">
                      ✓ Accès automatique pour tous les utilisateurs
                    </div>
                  <?php else: ?>
                    <div>
                      <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">Privilège d'accès :</label>
                      <select name="annee_acces[<?= htmlspecialchars($an['code_annee']) ?>]" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 600; font-size: 13px; padding: 8px 10px; border: 1px solid #CBD5E1;">
                        <option value="none" <?= $curVal === 'none' ? 'selected' : '' ?>>⛔ Aucun accès (Masquée)</option>
                        <option value="lecture" <?= $curVal === 'lecture' ? 'selected' : '' ?>>👁️ Lecture seule (Consultation)</option>
                        <option value="ecriture" <?= $curVal === 'ecriture' ? 'selected' : '' ?>>✏️ Édition (Saisie / Régularisation)</option>
                      </select>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; align-items: center;">
            <button type="submit" id="btn-save-user" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 11px 26px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="check" style="width: 18px; height: 18px;"></i>
              Enregistrer l'Utilisateur & Permissions
            </button>
            <a href="<?= RACINE ?>user/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 22px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Annuler
            </a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script type="application/json" id="users-edit-config">
<?= json_encode([
    'racine' => RACINE,
    'allRoles' => $roles ?? [],
    'savedRoles' => !empty($userRoles) ? $userRoles : (!empty($role) ? [$role] : [])
]) ?>
</script>
<?php
$pageScripts = ['public/assets/js/modules/users_edit.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
