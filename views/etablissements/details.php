<?php require_once __DIR__ . '/../../public/inc/header.php'; $item = $item ?? []; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;">Détails Établissement</h1>
        <a href="<?= RACINE ?>etablissement/list" class="btn btn-secondary">Retour</a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 600px;">
        <table class="table">
          <tr><th style="width:200px;">Nom de l'établissement</th><td><?= htmlspecialchars($item['libelle_etablissement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Adresse physique</th><td><?= htmlspecialchars($item['adresse_etablissement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Téléphone principal</th><td><?= htmlspecialchars($item['telephone_etablissement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Téléphone secondaire</th><td><?= htmlspecialchars($item['telephone_etablissement2'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Email officiel</th><td><?= htmlspecialchars($item['email_etablissement'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Slogan</th><td><?= htmlspecialchars($item['slogan_etablissement'] ?? '-') ?></td></tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
