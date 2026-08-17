<?php
require_once __DIR__ . '/../../public/inc/header.php';
$service = isset($service) ? $service : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Détails du service</h1>
          <p class="page-subtitle">Informations complètes</p>
        </div>
        <a href="<?= RACINE ?>service/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Service</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($service['code_service'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Libellé</span>
              <span class="info-value"><?= htmlspecialchars($service['libelle_service'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Description</span>
              <span class="info-value"><?= htmlspecialchars($service['description_service'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($service['statut_service'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($service['statut_service'] ?? '') ?>
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
