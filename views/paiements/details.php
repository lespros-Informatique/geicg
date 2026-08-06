<?php
require_once __DIR__ . '/../../public/inc/header.php';
$paiement = isset($paiement) ? $paiement : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <h1>Détails du paiement</h1>
        <a href="<?= RACINE ?>paiement/list" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Retour</a>
      </div>

      <div class="card" style="margin-top: 20px;">
        <div class="card-body" style="padding: 20px;">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Code paiement</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($paiement['code_paiement'] ?? '') ?></p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Commande</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($paiement['commande_code'] ?? 'N/A') ?></p>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Montant</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($paiement['montant_paiement'] ?? '0') ?> FCFA</p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Mode</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($paiement['mode_paiement'] ?? 'N/A') ?></p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Référence</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($paiement['reference_paiement'] ?? 'N/A') ?></p>
              </div>
            </div>
          </div>
          <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Statut</label>
            <span class="badge-status <?= ($paiement['statut_paiement'] ?? '') == 'valide' ? 'delivered' : (($paiement['statut_paiement'] ?? '') == 'annule' ? 'cancelled' : 'pending') ?>"><?= htmlspecialchars($paiement['statut_paiement'] ?? '') ?></span>
          </div>
        </div>
      </div>

      <div class="form-actions" style="margin-top: 20px;">
        <a href="<?= RACINE ?>paiement/edition/<?= $encryptedId ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i> Modifier</a>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
