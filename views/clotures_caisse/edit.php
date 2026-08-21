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
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_cloture']) ? 'Modifier Clôture de Caisse' : 'Créer un Clôture de Caisse' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-clotures_caisse" action="<?= RACINE ?>cloture_caisse/<?= !empty($item['id_cloture']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_cloture'])): ?>
            <input type="hidden" name="id_cloture" value="<?= $item['id_cloture'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de la caisse</label>
            <input type="date" class="form-control" name="date_cloture" value="<?= htmlspecialchars($item['date_cloture'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Espèces (FCFA)</label>
            <input type="number"  class="form-control" name="total_especes" value="<?= htmlspecialchars($item['total_especes'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Mobile Money (FCFA)</label>
            <input type="number"  class="form-control" name="total_mobile_money" value="<?= htmlspecialchars($item['total_mobile_money'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Chèque / Virement (FCFA)</label>
            <input type="number"  class="form-control" name="total_cheque_virement" value="<?= htmlspecialchars($item['total_cheque_virement'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Général Caisse (FCFA)</label>
            <input type="number"  class="form-control" name="total_general" value="<?= htmlspecialchars($item['total_general'] ?? '') ?>" required>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
