<?php require_once __DIR__ . '/../../public/inc/header.php'; $item = $item ?? []; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;">Détails Étudiant</h1>
        <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary">Retour</a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 600px;">
        <table class="table">
          <tr><th style="width:200px;">Matricule élève</th><td><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Nom de famille</th><td><?= htmlspecialchars($item['nom_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Prénoms</th><td><?= htmlspecialchars($item['prenom_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Sexe</th><td><?= htmlspecialchars($item['sexe_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Date de naissance</th><td><?= htmlspecialchars($item['date_naissance_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Lieu de naissance</th><td><?= htmlspecialchars($item['lieu_naissance_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Téléphone étudiant</th><td><?= htmlspecialchars($item['telephone_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Email étudiant</th><td><?= htmlspecialchars($item['email_etudiant'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">N° CNI ou Passeport</th><td><?= htmlspecialchars($item['numero_cni'] ?? '-') ?></td></tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
