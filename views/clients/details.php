<?php
require_once __DIR__ . '/../../public/inc/header.php';
$client = isset($client) ? $client : [];
$commandes = isset($commandes) ? $commandes : [];
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user" style="color: #2563EB;"></i> Fiche Client : <?= htmlspecialchars($client['nom_client'] ?? '') ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">
            <?= $isSuperAdmin ? 'Consultation en lecture seule du compte client' : 'Informations complètes et historique des commandes' ?>
          </p>
        </div>
        <a href="<?= RACINE ?>client/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour aux clients
        </a>
      </div>

      <div class="detail-card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="detail-card-header" style="margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
          <h2 style="font-size: 17px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="info" style="color: #2563EB; width: 18px; height: 18px;"></i> Informations Personnelles
          </h2>
        </div>
        <div class="detail-card-body">
          <div class="info-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div class="info-item">
              <span class="info-label" style="display: block; font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Code Client</span>
              <span class="info-value code-badge" style="font-weight: 700; margin-top: 4px; display: inline-block;"><?= htmlspecialchars($client['code_client'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label" style="display: block; font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Nom & Prénoms</span>
              <span class="info-value" style="font-weight: 700; color: #1E293B; margin-top: 4px; display: inline-block;"><?= htmlspecialchars($client['nom_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label" style="display: block; font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Téléphone</span>
              <span class="info-value" style="font-weight: 600; color: #1E293B; margin-top: 4px; display: inline-block;"><i class="fa fa-phone" style="color: #2563EB;"></i> <?= htmlspecialchars($client['telephone_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label" style="display: block; font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Quartier</span>
              <span class="info-value" style="color: #2563EB; font-weight: 600; margin-top: 4px; display: inline-block;"><i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($client['quartier_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label" style="display: block; font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Adresse</span>
              <span class="info-value" style="color: #334155; margin-top: 4px; display: inline-block;"><?= htmlspecialchars($client['adresse_client'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label" style="display: block; font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Statut</span>
              <span class="info-value" style="margin-top: 4px; display: inline-block;">
                <span class="badge-status <?= ($client['statut_client'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($client['statut_client'] ?? '') ?>
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($commandes)): ?>
      <div class="detail-card" style="margin-top: 24px; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="detail-card-header" style="margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
          <h2 style="font-size: 17px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="shopping-bag" style="color: #2563EB; width: 18px; height: 18px;"></i> Historique des Commandes (<?= count($commandes) ?>)
          </h2>
        </div>
        <div class="detail-card-body">
          <div class="mobile-list-container" id="commandesMobileList"></div>
          <div class="table-responsive-mobile">
            <table class="table" id="dataTable" style="width: 100%;">
              <thead>
                <tr>
                  <th>Code Commande</th>
                  <th>Date</th>
                  <th>Statut</th>
                  <th>Détail</th>
                  <th>Paiements</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($commandes as $cmd): ?>
                <tr>
                  <td><strong style="color: #1E293B;"><?= htmlspecialchars($cmd['code_commande'] ?? '') ?></strong></td>
                  <td><?= htmlspecialchars($cmd['created_at_commande'] ?? '') ?></td>
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
                   '<div style="font-size:0.8rem;color:#666;">' + (cmd.created_at_commande || '') + '</div>' +
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
