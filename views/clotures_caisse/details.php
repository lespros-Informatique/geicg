<?php require_once __DIR__ . '/../../public/inc/header.php'; $item = $item ?? []; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;">Détails Clôture de Caisse</h1>
        <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary">Retour</a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 600px;">
        <table class="table">
          <tr><th style="width:200px;">Date de la caisse</th><td><?= htmlspecialchars($item['date_cloture'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Total Espèces (FCFA)</th><td><?= htmlspecialchars($item['total_especes'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Total Mobile Money (FCFA)</th><td><?= htmlspecialchars($item['total_mobile_money'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Total Chèque / Virement (FCFA)</th><td><?= htmlspecialchars($item['total_cheque_virement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Total Général Caisse (FCFA)</th><td><?= htmlspecialchars($item['total_general'] ?? '-') ?></td></tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
