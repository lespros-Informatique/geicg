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
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_evenement']) ? 'Modifier Événement' : 'Créer un Événement' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-evenements" action="<?= RACINE ?>evenement/<?= !empty($item['id_evenement']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_evenement'])): ?>
            <input type="hidden" name="id_evenement" value="<?= $item['id_evenement'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre de l'événement</label>
            <input type="text" class="form-control" name="titre_evenement" value="<?= htmlspecialchars($item['titre_evenement'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Description complète</label>
            <textarea class="form-control" name="description_evenement" required rows="3"><?= htmlspecialchars($item['description_evenement'] ?? '') ?></textarea>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>evenement/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
