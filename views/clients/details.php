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
          <h1 style="font-size: 22px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user" style="color: #2563EB;"></i> Fiche Client : <?= htmlspecialchars($client['nom_client'] ?? 'Client') ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
            <?= $isSuperAdmin ? 'Consultation globale du profil client' : 'Coordonnées et historique des commandes du client' ?>
          </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <?php if (!empty($client['id_client']) && !empty($encryptedId)): ?>
            <a href="<?= RACINE ?>client/edition/<?= htmlspecialchars($encryptedId) ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier le client
            </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>client/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
          </a>
        </div>
      </div>

      <!-- CARTE 1 : INFORMATIONS PERSONNELLES -->
      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF; margin-bottom: 24px;">
        <div style="margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
          <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="info" style="color: #2563EB; width: 18px; height: 18px;"></i> Coordonnées & Identifiants
          </h2>
          <span class="badge-status <?= ($client['statut_client'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>" style="font-weight: 700;">
            <?= htmlspecialchars(ucfirst($client['statut_client'] ?? 'Actif')) ?>
          </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Code Client</span>
            <span class="code-badge" style="font-size: 14px; font-weight: 800; margin-top: 4px; display: inline-block;">
              <?= htmlspecialchars($client['code_client'] ?? '-') ?>
            </span>
          </div>

          <div>
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Nom & Prénoms</span>
            <strong style="font-size: 15px; color: #1E293B; margin-top: 4px; display: block;">
              <?= htmlspecialchars($client['nom_client'] ?? '-') ?>
            </strong>
          </div>

          <div>
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Téléphone (Login)</span>
            <span style="font-size: 14px; font-weight: 700; color: #2563EB; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
              <i class="fa fa-phone"></i> <?= htmlspecialchars($client['telephone_client'] ?? '-') ?>
            </span>
          </div>

          <div>
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Email</span>
            <span style="font-size: 14px; color: #334155; margin-top: 4px; display: block;">
              <?= htmlspecialchars($client['email_client'] ?? '-') ?>
            </span>
          </div>

          <div>
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Quartier</span>
            <span style="font-size: 14px; color: #2563EB; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
              <i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($client['quartier_client'] ?? '-') ?>
            </span>
          </div>

          <div>
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Adresse Domicile</span>
            <span style="font-size: 13.5px; color: #334155; margin-top: 4px; display: block; line-height: 1.4;">
              <?= htmlspecialchars($client['adresse_client'] ?? '-') ?>
            </span>
          </div>
        </div>
      </div>

      <!-- CARTE 2 : HISTORIQUE DES COMMANDES DU CLIENT -->
      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div style="margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
          <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="shopping-bag" style="color: #2563EB; width: 18px; height: 18px;"></i> Historique des Commandes (<?= count($commandes) ?>)
          </h2>
          <?php if (!empty($client['code_client'])): ?>
            <a href="<?= RACINE ?>commande/list?client=<?= htmlspecialchars($client['code_client']) ?>" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
              <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Créer une commande
            </a>
          <?php endif; ?>
        </div>

        <?php if (!empty($commandes)): ?>
        <div class="table-responsive-mobile">
          <table class="table" style="width: 100%;">
            <thead>
              <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Étape de suivi</th>
                <th>Date</th>
                <th style="text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($commandes as $cmd): ?>
              <tr>
                <td><strong style="color: #1E293B;">#<?= htmlspecialchars($cmd['code_commande'] ?? '') ?></strong></td>
                <td>
                  <?php if (($cmd['type_commande'] ?? '') === 'colis'): ?>
                    <span style="background: #FEF3C7; color: #92400E; padding: 3px 8px; border-radius: 12px; font-weight: 700; font-size: 11px;">Collecte au Sac</span>
                  <?php else: ?>
                    <span style="background: #EFF6FF; color: #1E40AF; padding: 3px 8px; border-radius: 12px; font-weight: 700; font-size: 11px;">Commande Détaillée</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color: #059669;">
                    <?= isset($cmd['montant_total_commande']) ? number_format((float)$cmd['montant_total_commande'], 0, ',', ' ') . ' FCFA' : '0 FCFA' ?>
                  </strong>
                </td>
                <td>
                  <span class="badge-status <?= ($cmd['statut_commande'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>" style="font-weight: 700;">
                    <?= htmlspecialchars(ucfirst($cmd['statut_suivi_commande'] ?? ($cmd['statut_commande'] ?? ''))) ?>
                  </span>
                </td>
                <td style="color: #64748B; font-size: 13px;"><?= htmlspecialchars($cmd['created_at_commande'] ?? '-') ?></td>
                <td style="text-align: center;">
                  <?php if (!empty($cmd['editId'])): ?>
                    <a href="<?= RACINE ?>commande/details/<?= htmlspecialchars($cmd['editId']) ?>" class="btn-action btn-action-secondary" title="Voir la commande" style="display: inline-flex; align-items: center; justify-content: center;">
                      <i class="fa fa-eye"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div style="text-align: center; padding: 30px; color: #94A3B8; font-size: 14px;">
            <i data-lucide="inbox" style="width: 36px; height: 36px; stroke-width: 1.5; margin-bottom: 8px; opacity: 0.6;"></i>
            <p style="margin: 0;">Aucune commande enregistrée pour ce client pour le moment.</p>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
