<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>DÃ©tails de l'utilisateur</h1>
          <p class="page-subtitle">Informations complÃ¨tes</p>
        </div>
        <a href="<?= RACINE ?>user/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Utilisateur</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($user['code_user'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Nom</span>
              <span class="info-value"><?= htmlspecialchars($user['nom_user'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">PrÃ©nom</span>
              <span class="info-value"><?= htmlspecialchars($user['prenom_user'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">TÃ©lÃ©phone</span>
              <span class="info-value"><?= htmlspecialchars($user['telephone_user'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">RÃ´le</span>
              <span class="info-value"><?= htmlspecialchars($role['libelle_role'] ?? ($user['role_user'] ?? '-')) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($user['statut_user'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($user['statut_user'] ?? '') ?>
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
