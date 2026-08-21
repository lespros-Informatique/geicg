<?php require_once __DIR__ . '/../../public/inc/header.php'; $item = $item ?? []; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;">Détails Règlement Caisse</h1>
        <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary">Retour</a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 600px;">
        <table class="table">
          <tr><th style="width:200px;">Inscription Élève</th><td><?= htmlspecialchars($item['inscription_code'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Montant versé (FCFA)</th><td><?= htmlspecialchars($item['montant_paiement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Mode de règlement</th><td><?= htmlspecialchars($item['mode_paiement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Motif du versement</th><td><?= htmlspecialchars($item['type_paiement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Référence transaction / N° Chèque</th><td><?= htmlspecialchars($item['reference_paiement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Observations</th><td><?= htmlspecialchars($item['observations'] ?? '-') ?></td></tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
