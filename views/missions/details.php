<?php
require_once __DIR__ . '/../../public/inc/header.php';
$mission = isset($mission) ? $mission : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Détails de la mission</h1>
          <p class="page-subtitle">Informations complètes</p>
        </div>
        <a href="<?= RACINE ?>mission/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Mission</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($mission['code_mission'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Commande</span>
              <span class="info-value"><?= htmlspecialchars($mission['commande_code'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Livreur</span>
              <span class="info-value"><?= htmlspecialchars($mission['livreur_code'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Type</span>
              <span class="info-value"><?= htmlspecialchars($mission['type_mission'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Adresse</span>
              <span class="info-value"><?= htmlspecialchars($mission['adresse_mission'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Observation</span>
              <span class="info-value"><?= htmlspecialchars($mission['observation_mission'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= in_array($mission['statut_mission'], ['en_cours','terminee']) ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($mission['statut_mission'] ?? '') ?>
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
