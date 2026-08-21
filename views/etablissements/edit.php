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
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_etablissement']) ? 'Modifier Établissement' : 'Créer un Établissement' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-etablissements" action="<?= RACINE ?>etablissement/<?= !empty($item['id_etablissement']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_etablissement'])): ?>
            <input type="hidden" name="id_etablissement" value="<?= $item['id_etablissement'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de l'établissement</label>
            <input type="text" class="form-control" name="libelle_etablissement" value="<?= htmlspecialchars($item['libelle_etablissement'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse physique</label>
            <textarea class="form-control" name="adresse_etablissement"  rows="3"><?= htmlspecialchars($item['adresse_etablissement'] ?? '') ?></textarea>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone principal</label>
            <input type="text" class="form-control" name="telephone_etablissement" value="<?= htmlspecialchars($item['telephone_etablissement'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone secondaire</label>
            <input type="text" class="form-control" name="telephone_etablissement2" value="<?= htmlspecialchars($item['telephone_etablissement2'] ?? '') ?>" >
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Email officiel</label>
            <input type="text" class="form-control" name="email_etablissement" value="<?= htmlspecialchars($item['email_etablissement'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Slogan</label>
            <input type="text" class="form-control" name="slogan_etablissement" value="<?= htmlspecialchars($item['slogan_etablissement'] ?? '') ?>" >
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>etablissement/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
