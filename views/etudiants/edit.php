<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_etudiant']) ? 'Modifier Étudiant' : 'Créer un Étudiant' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-etudiants" action="<?= RACINE ?>etudiant/<?= !empty($item['id_etudiant']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_etudiant'])): ?>
            <input type="hidden" name="id_etudiant" value="<?= $item['id_etudiant'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule élève</label>
            <input type="text" class="form-control" name="matricule_etudiant" value="<?= htmlspecialchars($item['matricule_etudiant'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de famille</label>
            <input type="text" class="form-control" name="nom_etudiant" value="<?= htmlspecialchars($item['nom_etudiant'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Prénoms</label>
            <input type="text" class="form-control" name="prenom_etudiant" value="<?= htmlspecialchars($item['prenom_etudiant'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Sexe</label>
            <select class="form-control" name="sexe_etudiant" required>
              <option value="M" <?= (($item['sexe_etudiant'] ?? '') === 'M') ? 'selected' : '' ?>>Masculin (M)</option>
              <option value="F" <?= (($item['sexe_etudiant'] ?? '') === 'F') ? 'selected' : '' ?>>Féminin (F)</option>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de naissance</label>
            <input type="date" class="form-control" name="date_naissance_etudiant" value="<?= htmlspecialchars($item['date_naissance_etudiant'] ?? '') ?>" >
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Lieu de naissance</label>
            <input type="text" class="form-control" name="lieu_naissance_etudiant" value="<?= htmlspecialchars($item['lieu_naissance_etudiant'] ?? '') ?>" >
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone étudiant</label>
            <input type="text" class="form-control" name="telephone_etudiant" value="<?= htmlspecialchars($item['telephone_etudiant'] ?? '') ?>" >
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Email étudiant</label>
            <input type="text" class="form-control" name="email_etudiant" value="<?= htmlspecialchars($item['email_etudiant'] ?? '') ?>" >
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">N° CNI ou Passeport</label>
            <input type="text" class="form-control" name="numero_cni" value="<?= htmlspecialchars($item['numero_cni'] ?? '') ?>" >
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
