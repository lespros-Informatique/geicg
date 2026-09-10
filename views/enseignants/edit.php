<?php 
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$availableUsers = isset($availableUsers) ? $availableUsers : [];
$isEdit = !empty($item['id_enseignant']);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= $isEdit ? 'Éditer l\'Enseignant' : 'Nouvel Enseignant' ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion du corps enseignant unifié avec les comptes utilisateurs de l'établissement</p>
        </div>
        <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>enseignant/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_enseignant" value="<?= $item['id_enseignant'] ?>">
          <?php endif; ?>

          <?php if (!$isEdit): ?>
          <!-- Sélecteur de Mode de Création -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px;">
            <label style="display: block; font-weight: 800; font-size: 13.5px; color: #1E3A5F; margin-bottom: 10px;">
              <i data-lucide="help-circle" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle;"></i> Type d'enregistrement :
            </label>
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
              <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #334155; cursor: pointer;">
                <input type="radio" name="mode_creation" value="nouveau" checked id="radio-mode-nouveau" style="width: 17px; height: 17px; accent-color: #1E3A5F;">
                <span>Créer un nouvel enseignant (Nouvel employé)</span>
              </label>
              <?php if (!empty($availableUsers)): ?>
              <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #334155; cursor: pointer;">
                <input type="radio" name="mode_creation" value="existant" id="radio-mode-existant" style="width: 17px; height: 17px; accent-color: #1E3A5F;">
                <span>Rattacher un membre / employé existant de l'école (<?= count($availableUsers) ?> disponible(s))</span>
              </label>
              <?php endif; ?>
            </div>
          </div>

          <!-- Bloc Sélection Utilisateur Existant -->
          <div id="bloc-user-existant" style="display: none; background: #EFF6FF; border: 1.5px solid #BFDBFE; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px;">
            <label style="display: block; font-weight: 800; font-size: 13px; color: #1E3A5F; margin-bottom: 6px;">
              Sélectionner le compte employé / utilisateur à désigner comme enseignant <span style="color: #EF4444;">*</span>
            </label>
            <select name="user_code_existant" id="select_user_existant" class="form-control" style="width: 100%; padding: 11px 14px; font-size: 14px; font-weight: 700; border-radius: 8px; border: 1px solid #93C5FD; background: #FFFFFF;">
              <option value="">-- Choisir un utilisateur --</option>
              <?php foreach ($availableUsers as $u): ?>
                <option value="<?= $u['code_user'] ?>">
                  <?= htmlspecialchars($u['nom_user'] . ' ' . $u['prenom_user']) ?> (<?= htmlspecialchars($u['email_user'] ?: $u['telephone_user']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <small style="color: #1E40AF; font-size: 12px; margin-top: 6px; display: block;">
              Cet utilisateur conservera ses accès actuels et recevra en plus le rôle <strong>Enseignant</strong> et ses privilèges pédagogiques.
            </small>
          </div>
          <?php endif; ?>

          <!-- Bloc Identité Utilisateur (Nouveau ou Édition) -->
          <div id="bloc-user-identity">
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
              <i data-lucide="user" style="width: 18px; height: 18px;"></i> Identité & Compte Utilisateur
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de famille <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control field-ident" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="nom_user" value="<?= htmlspecialchars($item['nom_user'] ?? ($item['nom_enseignant'] ?? '')) ?>" placeholder="Ex: KOUASSI" required>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Prénom(s) <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control field-ident" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="prenom_user" value="<?= htmlspecialchars($item['prenom_user'] ?? ($item['prenom_enseignant'] ?? '')) ?>" placeholder="Ex: Jean-Marc" required>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse Email (Identifiant de connexion)</label>
                <input type="email" class="form-control field-ident" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="email_user" value="<?= htmlspecialchars($item['email_user'] ?? ($item['email_enseignant'] ?? '')) ?>" placeholder="Ex: prof.kouassi@geicg.ci">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro Téléphone (Contact & Login)</label>
                <input type="text" class="form-control field-ident" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="telephone_user" value="<?= htmlspecialchars($item['telephone_user'] ?? ($item['telephone_enseignant'] ?? '')) ?>" placeholder="Ex: 0708091011">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Genre / Sexe</label>
                <select class="form-control field-ident" name="sexe_user" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                  <option value="Masculin" <?= (in_array($item['sexe_user'] ?? ($item['sexe_enseignant'] ?? 'M'), ['M', 'Masculin'])) ? 'selected' : '' ?>>Masculin (M)</option>
                  <option value="Féminin" <?= (in_array($item['sexe_user'] ?? ($item['sexe_enseignant'] ?? ''), ['F', 'Féminin'])) ? 'selected' : '' ?>>Féminin (F)</option>
                </select>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  <?= $isEdit ? 'Changer le mot de passe (Laisser vide pour conserver)' : 'Mot de passe initial (Optionnel)' ?>
                </label>
                <input type="password" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="password_user" placeholder="<?= $isEdit ? 'Nouveau mot de passe...' : 'Généré automatiquement si vide' ?>">
              </div>
            </div>
          </div>

          <!-- Bloc Attributs Pédagogiques & Contrat Enseignant -->
          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 24px 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="award" style="width: 18px; height: 18px;"></i> Spécialisation Enseignant & Contrat RH
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Grade / Titre académique</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="grade_enseignant" value="<?= htmlspecialchars($item['grade_enseignant'] ?? '') ?>" placeholder="Ex: Docteur, Ingénieur, Professeur...">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de contrat</label>
              <select class="form-control" name="type_contrat" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="permanent" <?= (($item['type_contrat'] ?? 'permanent') === 'permanent') ? 'selected' : '' ?>>Permanent / Titulaire</option>
                <option value="vacataire" <?= (($item['type_contrat'] ?? '') === 'vacataire') ? 'selected' : '' ?>>Vacataire (Paiement à l'heure)</option>
                <option value="prestataire" <?= (($item['type_contrat'] ?? '') === 'prestataire') ? 'selected' : '' ?>>Prestataire / Consultant externe</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro d'Autorisation d'Enseigner</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="numero_autorisation" value="<?= htmlspecialchars($item['numero_autorisation'] ?? '') ?>" placeholder="Ex: AUT-2024-00892">
              <small style="color: #64748B; font-size: 11px; margin-top: 4px; display: block;">Numéro d'autorisation ministérielle officielle.</small>
            </div>

            <?php if ($isEdit): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut d'activité</label>
              <select class="form-control" name="statut_enseignant" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="actif" <?= (($item['statut_enseignant'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Actif (En service)</option>
                <option value="inactif" <?= (($item['statut_enseignant'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif / Suspendu</option>
              </select>
            </div>
            <?php endif; ?>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">
              <?= $isEdit ? 'Mettre à jour l\'Enseignant' : 'Enregistrer l\'Enseignant' ?>
            </button>
            <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  $('#radio-mode-nouveau').on('change', function() {
    if ($(this).is(':checked')) {
      $('#bloc-user-existant').slideUp(200);
      $('#bloc-user-identity').slideDown(200);
      $('.field-ident').prop('required', true);
      $('#select_user_existant').prop('required', false);
    }
  });

  $('#radio-mode-existant').on('change', function() {
    if ($(this).is(':checked')) {
      $('#bloc-user-identity').slideUp(200);
      $('#bloc-user-existant').slideDown(200);
      $('.field-ident').prop('required', false);
      $('#select_user_existant').prop('required', true);
    }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
