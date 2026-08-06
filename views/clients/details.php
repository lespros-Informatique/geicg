<?php
require_once __DIR__ . '/../../public/inc/header.php';
$client = isset($client) ? $client : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>DÃ©tails du client</h1>
          <p class="page-subtitle">Informations complÃ¨tes</p>
        </div>
        <a href="<?= RACINE ?>client/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Client</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($client['code_client'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Nom</span>
              <span class="info-value"><?= htmlspecialchars($client['nom_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">TÃ©lÃ©phone</span>
              <span class="info-value"><?= htmlspecialchars($client['telephone_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Quartier</span>
              <span class="info-value"><?= htmlspecialchars($client['quartier_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Adresse</span>
              <span class="info-value"><?= htmlspecialchars($client['adresse_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($client['statut_client'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($client['statut_client'] ?? '') ?>
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($commandes)): ?>
      <div class="detail-card" style="margin-top: 24px;">
        <div class="detail-card-header"><h2>Commandes</h2></div>
        <div class="detail-card-body">
          <div class="mobile-list-container" id="commandesMobileList"></div>
          <div class="table-responsive-mobile">
            <table class="table" id="dataTable">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Date</th>
                  <th>Statut</th>
                  <th>Détail</th>
                  <th>Paiements</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($commandes as $cmd): ?>
                <tr>
                  <td><?= htmlspecialchars($cmd['code_commande'] ?? '') ?></td>
                  <td><?= htmlspecialchars($cmd['date_commande'] ?? '') ?></td>
                  <td>
                    <span class="badge-status <?= ($cmd['statut_commande'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                      <?= htmlspecialchars($cmd['statut_commande'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <?php if (!empty($cmd['editId'])): ?>
                      <a href="<?= RACINE ?>commande/details/<?= htmlspecialchars($cmd['editId']) ?>" class="btn-action btn-action-secondary" title="Voir détail">
                        <i class="fa fa-eye"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($cmd['paiements'])): ?>
                      <?php foreach ($cmd['paiements'] as $p): ?>
                        <div style="margin-bottom:4px;">
                          <strong><?= htmlspecialchars($p['code_paiement'] ?? '') ?></strong>
                          <span class="badge-status <?= ($p['statut_paiement'] ?? '') == 'valide' ? 'delivered' : 'cancelled' ?>" style="font-size:0.75rem;">
                            <?= htmlspecialchars($p['statut_paiement'] ?? '') ?>
                          </span>
                          <span style="font-size:0.85rem;color:#666;">
                            <?= isset($p['montant_paiement']) ? number_format((float)$p['montant_paiement'], 0, ',', ' ') . ' FCFA' : '' ?>
                          </span>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span style="color:#999;">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <script>
        (function() {
          var rawData = <?= json_encode($commandes) ?>;
          var container = document.getElementById('commandesMobileList');
          console.log('[details mobile] rawData count=', rawData ? rawData.length : 0, 'container=', !!container);
          if (!container || !rawData || !rawData.length) return;

          function renderPaiements(paiements) {
            if (!paiements || !paiements.length) return '<span style="color:#999;">-</span>';
            return paiements.map(function(p) {
              var cls = (p.statut_paiement || '') === 'valide' ? 'delivered' : 'cancelled';
              var montant = p.montant_paiement ? Number(p.montant_paiement).toLocaleString('fr-FR') + ' FCFA' : '';
              return '<div style="margin-bottom:3px;">' +
                '<strong>' + (p.code_paiement || '') + '</strong> ' +
                '<span class="badge-status ' + cls + '" style="font-size:0.7rem;">' + (p.statut_paiement || '') + '</span> ' +
                '<span style="font-size:0.8rem;color:#666;">' + montant + '</span>' +
              '</div>';
            }).join('');
          }

          var cards = rawData.map(function(cmd) {
            var detailHref = '<?= RACINE ?>commande/details/' + (cmd.editId || '');
            return '<div class="mobile-item" style="padding:12px;border-bottom:1px solid var(--border-color);">' +
              '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">' +
                '<div>' +
                  '<div style="font-weight:600;">' + (cmd.code_commande || '') + '</div>' +
                  '<div style="font-size:0.8rem;color:#666;">' + (cmd.date_commande || '') + '</div>' +
                '</div>' +
                '<a href="' + detailHref + '" class="btn-action btn-action-secondary" title="Voir détail" style="padding:6px 10px;">' +
                  '<i class="fa fa-eye"></i>' +
                '</a>' +
              '</div>' +
              '<div style="font-size:0.75rem;color:#666;margin-bottom:4px;">' +
                '<span class="badge-status ' + ((cmd.statut_commande || '') === 'actif' ? 'delivered' : 'cancelled') + '">' + (cmd.statut_commande || '') + '</span>' +
              '</div>' +
              '<div style="font-size:0.85rem;">' +
                '<div style="margin-bottom:4px;font-weight:600;font-size:0.75rem;text-transform:uppercase;color:#999;">Paiements</div>' +
                renderPaiements(cmd.paiements) +
              '</div>' +
            '</div>';
          }).join('');

          container.innerHTML = cards;
        })();
      </script>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
