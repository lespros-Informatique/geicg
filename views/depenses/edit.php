<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$typeDepenses = (new ModelTypeDepense())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_depense']) ? 'Modifier Dépense / Engagement' : 'Créer un Dépense / Engagement' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-depenses" action="<?= RACINE ?>depense/<?= !empty($item['id_depense']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_depense'])): ?>
            <input type="hidden" name="id_depense" value="<?= $item['id_depense'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de dépense</label>
            <select class="form-control" name="type_depense_code" required>
              <option value="">-- Sélectionner la catégorie --</option>
              <?php foreach($typeDepenses as $td): ?>
                <option value="<?= $td['code_type_depense'] ?>" <?= (($item['type_depense_code'] ?? '') == $td['code_type_depense']) ? 'selected' : '' ?>><?= htmlspecialchars($td['libelle_type_depense']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Montant engagé (FCFA)</label>
            <input type="number"  class="form-control" name="montant_depense" value="<?= htmlspecialchars($item['montant_depense'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de la dépense</label>
            <input type="date" class="form-control" name="periode_depense" value="<?= htmlspecialchars($item['periode_depense'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Description & Justificatif</label>
            <textarea class="form-control" name="description_depense" required rows="3"><?= htmlspecialchars($item['description_depense'] ?? '') ?></textarea>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>depense/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
