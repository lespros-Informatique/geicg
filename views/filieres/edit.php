<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$cycles = (new ModelCycle())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_filiere']) ? 'Modifier Filière' : 'Créer un Filière' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-filieres" action="<?= RACINE ?>filiere/<?= !empty($item['id_filiere']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_filiere'])): ?>
            <input type="hidden" name="id_filiere" value="<?= $item['id_filiere'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de la filière</label>
            <input type="text" class="form-control" name="libelle_filiere" value="<?= htmlspecialchars($item['libelle_filiere'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Cycle rattaché</label>
            <select class="form-control" name="cycle_code" required>
              <option value="">-- Sélectionner un cycle --</option>
              <?php foreach($cycles as $c): ?>
                <option value="<?= $c['code_cycle'] ?>" <?= (($item['cycle_code'] ?? '') == $c['code_cycle']) ? 'selected' : '' ?>><?= htmlspecialchars($c['libelle_cycle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Description & Débouchés</label>
            <textarea class="form-control" name="description_filiere"  rows="3"><?= htmlspecialchars($item['description_filiere'] ?? '') ?></textarea>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>filiere/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
