<?php
require_once __DIR__ . '/../../public/inc/header.php';
$livreur = isset($livreur) ? $livreur : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Détails du livreur</h1>
          <p class="page-subtitle">Informations complètes</p>
        </div>
        <a href="<?= RACINE ?>livreur/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Livreur</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($livreur['code_livreur'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Nom</span>
              <span class="info-value"><?= htmlspecialchars($livreur['nom_livreur'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Prénom</span>
              <span class="info-value"><?= htmlspecialchars($livreur['prenom_livreur'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Téléphone</span>
              <span class="info-value"><?= htmlspecialchars($livreur['telephone_livreur'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Pressing</span>
              <span class="info-value"><?= htmlspecialchars($livreur['pressing_code'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($livreur['statut_livreur'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($livreur['statut_livreur'] ?? '') ?>
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
