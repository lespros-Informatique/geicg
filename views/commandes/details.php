<?php
require_once __DIR__ . '/../../public/inc/header.php';
$order = isset($order) ? $order : [];
$montantTotal = 0;
foreach ($lignes as $l) {
    $montantTotal += (float) ($l['sous_total_commande_detail'] ?? 0);
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
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Date commande</label>
                 <p style="font-size: 1rem; margin: 5px 0;"><?= htmlspecialchars($order['created_at_commande'] ?? '') ?></p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Statut</label>
                <span class="badge-status <?= ($order['statut_commande'] ?? '') === 'actif' ? 'delivered' : 'cancelled' ?>"><?= htmlspecialchars($order['statut_commande'] ?? '') ?></span>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Suivi commande</label>
                <span class="badge-status <?= ($order['statut_suivi_commande'] ?? '') === 'livree' ? 'delivered' : (($order['statut_suivi_commande'] ?? '') === 'annulee' ? 'cancelled' : 'info') ?>"><?= htmlspecialchars($order['statut_suivi_commande'] ?? '') ?></span>
              </div>
            </div>
          </div>

          <div class="form-group" style="margin-top: 15px;">
            <label style="font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Actions de suivi</label>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;" id="commandeTransitionButtons">
              <?php
                $current = $order['statut_suivi_commande'] ?? 'creee';
                $transitions = [];
                if ($current === 'creee') $transitions = [['label' => 'Marquer collectée', 'next' => 'collectee'], ['label' => 'Annuler', 'next' => 'annulee']];
                elseif ($current === 'collectee') $transitions = [['label' => 'Mettre en traitement', 'next' => 'en_traitement'], ['label' => 'Annuler', 'next' => 'annulee']];
                elseif ($current === 'en_traitement') $transitions = [['label' => 'Marquer prête', 'next' => 'prete'], ['label' => 'Annuler', 'next' => 'annulee']];
                elseif ($current === 'prete') $transitions = [['label' => 'Marquer livrée', 'next' => 'livree'], ['label' => 'Annuler', 'next' => 'annulee']];
              ?>
              <?php foreach ($transitions as $t): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="transitionCommande(<?= $order['id_commande'] ?>, '<?= $t['next'] ?>')"><?= $t['label'] ?></button>
              <?php endforeach; ?>
              <?php if (empty($transitions) && in_array($current, ['livree','annulee'], true)): ?>
                <span style="color:#999; font-size:0.85rem;">Commande clôturée</span>
              <?php endif; ?>
            </div>
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
            <thead><tr><th>Article</th><th>Quantité</th><th>Prix unitaire</th><th>Sous-total</th></tr></thead>
            <tbody>
              <?php foreach ($lignes as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['article_code'] ?? ($l['service_code'] ?? '')) ?></td>
                <td><?= htmlspecialchars($l['quantite_commande_detail'] ?? '1') ?></td>
                <td><?= number_format((float) ($l['prix_unitaire_commande_detail'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                <td><?= number_format((float) ($l['sous_total_commande_detail'] ?? 0), 0, ',', ' ') ?> FCFA</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
      <script>
        function transitionCommande(id, nextStatus) {
          if (!id || !nextStatus) return;
          showConfirm('Changer le statut de suivi de cette commande ?', function() {
            $.post(LINK + 'commande/transition', { id_commande: id, statut_suivi_commande: nextStatus }, function(rep) {
              showToast(rep.message || 'Statut mis à jour', rep.status ? 'success' : 'error');
              if (rep.status) {
                setTimeout(() => window.location.reload(), 700);
              }
            }, 'json').fail(function() { showToast('Erreur serveur', 'error'); });
          });
        }
      </script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
