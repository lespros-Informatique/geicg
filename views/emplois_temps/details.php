<?php require_once __DIR__ . '/../../public/inc/header.php'; $item = $item ?? []; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;">Détails Créneau Horaire</h1>
        <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary">Retour</a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 600px;">
        <table class="table">
          <tr><th style="width:200px;">Classe</th><td><?= htmlspecialchars($item['classe_code'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Matière</th><td><?= htmlspecialchars($item['matiere_code'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Enseignant</th><td><?= htmlspecialchars($item['enseignant_code'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Salle</th><td><?= htmlspecialchars($item['salle_code'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Jour de cours</th><td><?= htmlspecialchars($item['jour'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Heure de début</th><td><?= htmlspecialchars($item['heure_debut'] ?? '-') ?></td></tr>
          <tr><th style="width:200px;">Heure de fin</th><td><?= htmlspecialchars($item['heure_fin'] ?? '-') ?></td></tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
