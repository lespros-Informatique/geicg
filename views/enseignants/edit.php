<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$users = (new ModelUser())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_enseignant']) ? 'Modifier Enseignant' : 'Créer un Enseignant' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-enseignants" action="<?= RACINE ?>enseignant/<?= !empty($item['id_enseignant']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_enseignant'])): ?>
            <input type="hidden" name="id_enseignant" value="<?= $item['id_enseignant'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Compte Utilisateur Rattaché</label>
            <select class="form-control" name="user_code" required>
              <option value="">-- Sélectionner un compte utilisateur --</option>
              <?php foreach($users as $u): ?>
                <option value="<?= $u['code_user'] ?>" <?= (($item['user_code'] ?? '') == $u['code_user']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nom_user'] . ' ' . ($u['prenom_user'] ?? '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
