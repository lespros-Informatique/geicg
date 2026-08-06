<?php
require_once __DIR__ . '/../../public/inc/header.php';
$order = isset($order) ? $order : [];
$montantTotal = 0;
foreach ($lignes as $l) {
    $montantTotal += (float) ($l['prix_kit'] ?? 0);
}
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <h1>Détails de la commande</h1>
        <a href="<?= RACINE ?>commande/list" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Retour</a>
      </div>

      <div class="card" style="margin-top: 20px;">
        <div class="card-body" style="padding: 20px;">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Code commande</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($order['code_commande'] ?? '') ?></p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Client</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($order['nom_client'] ?? ($order['client_code'] ?? 'N/A')) ?></p>
              </div>
            </div>
          </div>
          <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Adresse de livraison</label>
            <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($order['adresse_client'] ?? ($order['adresse_livraison_commande'] ?? 'N/A')) ?></p>
          </div>
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Montant total</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Campagne</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($order['campagne_code'] ?? 'N/A') ?></p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Date commande</label>
                <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($order['date_commande'] ?? '') ?></p>
              </div>
            </div>
          </div>
          <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Statut</label>
            <span class="badge-status <?= ($order['statut_commande'] ?? '') === 'actif' ? 'delivered' : 'cancelled' ?>"><?= htmlspecialchars($order['statut_commande'] ?? '') ?></span>
          </div>
        </div>
      </div>

      <div class="form-actions" style="margin-top: 20px;">
        <a href="<?= RACINE ?>commande/edition/<?= $encryptedId ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i> Modifier</a>
      </div>

      <?php if (!empty($lignes)): ?>
      <div class="card" style="margin-top: 20px;">
        <div class="card-header"><h3>Lignes de commande</h3></div>
        <div class="card-body" style="padding: 20px;">
          <table class="table table-sm">
            <thead><tr><th>Kit</th><th>Prix kit</th><th>Statut ligne</th><th>Retrait</th></tr></thead>
            <tbody>
              <?php foreach ($lignes as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['libelle_kit'] ?? ($l['kit_code'] ?? '')) ?></td>
                <td><?= number_format((float) ($l['prix_kit'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                <td><span class="badge-status <?= ($l['statut_ligne'] ?? '') === 'solde' ? 'delivered' : (($l['statut_ligne'] ?? '') === 'partiel' ? 'shipping' : 'pending') ?>"><?= htmlspecialchars($l['statut_ligne'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($l['retrait_ligne_commande'] ?? 'non') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
