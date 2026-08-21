<?php require_once __DIR__ . '/../../public/inc/header.php'; $item = $item ?? []; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;">Détails Parent / Tuteur</h1>
        <a href="<?= RACINE ?>parent/list" class="btn btn-secondary">Retour</a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 600px;">
        <table class="table">
          <tr><th style="width:200px;">Étudiant associé</th><td><?= htmlspecialchars($item['etudiant_code'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Nom du père</th><td><?= htmlspecialchars($item['nom_pere'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Téléphone père</th><td><?= htmlspecialchars($item['telephone_pere'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Nom de la mère</th><td><?= htmlspecialchars($item['nom_mere'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Téléphone mère</th><td><?= htmlspecialchars($item['telephone_mere'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Nom du tuteur officiel</th><td><?= htmlspecialchars($item['nom_tuteur'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Téléphone tuteur</th><td><?= htmlspecialchars($item['telephone_tuteur'] ?? '-') ?></td></tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
