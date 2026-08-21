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
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_galerie']) ? 'Modifier Galerie Médias' : 'Créer un Galerie Médias' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-galeries" action="<?= RACINE ?>galerie/<?= !empty($item['id_galerie']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_galerie'])): ?>
            <input type="hidden" name="id_galerie" value="<?= $item['id_galerie'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre de l'album</label>
            <input type="text" class="form-control" name="titre_galerie" value="<?= htmlspecialchars($item['titre_galerie'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de média</label>
            <select class="form-control" name="type_galerie" required>
              <option value="photo" <?= (($item['type_galerie'] ?? '') === 'photo') ? 'selected' : '' ?>>Album Photos</option>
              <option value="video" <?= (($item['type_galerie'] ?? '') === 'video') ? 'selected' : '' ?>>Album Vidéos</option>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Description</label>
            <textarea class="form-control" name="description_galerie"  rows="3"><?= htmlspecialchars($item['description_galerie'] ?? '') ?></textarea>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>galerie/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
