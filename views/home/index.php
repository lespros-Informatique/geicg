<?php
require_once __DIR__ . '/../../public/inc/header.php';
$stats = $stats ?? [];
$recentInscriptions = $recentInscriptions ?? [];
$recentPaiements = $recentPaiements ?? [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper" style="padding: 24px;">
      <!-- HEADER DU DASHBOARD -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layout-dashboard" style="color: #1E3A5F;"></i> 
            <span>Tableau de Bord - GEICG</span>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
            Aperçu général de l'établissement - Année Académique <strong><?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?></strong>
          </p>
        </div>

        <div class="page-header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= RACINE ?>inscription/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
            <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i> Inscriptions
          </a>
          <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #059669; border-color: #059669; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
            <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i> Caisse & Encaissements
          </a>
        </div>
      </div>

      <!-- KPI GRID -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- KPI 1 : Inscriptions -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Effectif Étudiants</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">
            <?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
            Inscriptions validées cette année
          </div>
        </div>

        <!-- KPI 2 : Recouvrement -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Caisse Encaissees</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 24px; font-weight: 800; color: #047857; line-height: 1;">
            <?= number_format($stats['ca_encaisse'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 14px; font-weight: 600;">FCFA</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
            Total des règlements confirmés
          </div>
        </div>

        <!-- KPI 3 : Impayés / Reliquat -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Reliquat Scolarite</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF2F2; color: #B91C1C; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 24px; font-weight: 800; color: #B91C1C; line-height: 1;">
            <?= number_format($stats['reliquat_impayes'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 14px; font-weight: 600;">FCFA</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
            Reste à recouvrer sur la scolarité
          </div>
        </div>

        <!-- KPI 4 : Enseignants & Cours -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Classes & Professeurs</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="graduation-cap" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1;">
            <?= (int)($stats['total_classes'] ?? 0) ?> <span style="font-size: 13px; font-weight: 600; color: #64748B;">Classes</span> / <?= (int)($stats['total_enseignants'] ?? 0) ?> <span style="font-size: 13px; font-weight: 600; color: #64748B;">Enseignants</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
            <?= (int)($stats['total_matieres'] ?? 0) ?> Matières au programme
          </div>
        </div>
      </div>

      <!-- TABLES RECENTES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(480px, 1fr)); gap: 24px;">
        <!-- Dernières Inscriptions -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="user-plus" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Inscriptions Récentes
            </h3>
            <a href="<?= RACINE ?>inscription/list" style="font-size: 12px; font-weight: 600; color: #1E3A5F; text-decoration: none;">Voir tout</a>
          </div>

          <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                  <th style="padding: 10px 12px;">Matricule</th>
                  <th style="padding: 10px 12px;">Nom & Prénoms</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px; text-align: right;">Scolarité</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentInscriptions)): ?>
                  <tr>
                    <td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucune inscription récente</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentInscriptions as $insc): ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 10px 12px; font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($insc['matricule_etudiant'] ?? '') ?></td>
                      <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars(($insc['nom_etudiant'] ?? '') . ' ' . ($insc['prenom_etudiant'] ?? '')) ?></td>
                      <td style="padding: 10px 12px; color: #475569;"><?= htmlspecialchars($insc['libelle_classe'] ?? 'Non assigné') ?></td>
                      <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #0F172A;"><?= number_format((float)($insc['montant_scolarite_inscription'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Derniers Paiements de Caisse -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="credit-card" style="width: 18px; height: 18px; color: #059669;"></i> Derniers Règlements Caisse
            </h3>
            <a href="<?= RACINE ?>paiement/list" style="font-size: 12px; font-weight: 600; color: #059669; text-decoration: none;">Voir tout</a>
          </div>

          <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                  <th style="padding: 10px 12px;">Réf. Reçu</th>
                  <th style="padding: 10px 12px;">Étudiant</th>
                  <th style="padding: 10px 12px;">Mode</th>
                  <th style="padding: 10px 12px; text-align: right;">Montant</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentPaiements)): ?>
                  <tr>
                    <td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucun paiement récent</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentPaiements as $p): ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 10px 12px; font-weight: 700; color: #059669;"><?= htmlspecialchars($p['code_paiement'] ?? '') ?></td>
                      <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? '')) ?></td>
                      <td style="padding: 10px 12px; color: #475569; text-transform: uppercase; font-size: 11px;"><span class="badge" style="background: #E0F2FE; color: #0369A1; padding: 4px 8px; border-radius: 6px; font-weight: 700;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Caisse') ?></span></td>
                      <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #047857;"><?= number_format((float)($p['montant_paiement'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

          </div>
        </div>

        <!-- KPI 3 : CLIENTS DU PRESSING -->
        <div class="kpi-card-item" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;" id="label-kpi-clients">Clients Actifs</span>
            <h2 id="kpi-clients" class="kpi-card-val" style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 4px 0 2px 0;">0</h2>
            <small style="color: #94A3B8; font-size: 11px;">Clients fidélisés</small>
          </div>
          <div style="width: 52px; height: 52px; border-radius: 12px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            <i data-lucide="users"></i>
          </div>
        </div>

        <!-- KPI 4 : TARIFS / SERVICES -->
        <div class="kpi-card-item" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;" id="label-kpi-catalogue">Tarifs au Catalogue</span>
            <h2 id="kpi-catalogue" class="kpi-card-val" style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 4px 0 2px 0;">0</h2>
            <small style="color: #94A3B8; font-size: 11px;">Articles & services actifs</small>
          </div>
          <div style="width: 52px; height: 52px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            <i data-lucide="tag"></i>
          </div>
        </div>
      </div>

      <!-- STATUT ABONNEMENT B2B POUR LE PRESSING -->
      <?php if (!empty($isPressing)): ?>
        <div style="background: <?= !empty($isSubscriptionActive) ? '#F0FDF4' : '#FEF2F2' ?>; border: 1px solid <?= !empty($isSubscriptionActive) ? '#BBF7D0' : '#FECACA' ?>; border-radius: 14px; padding: 18px 22px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= !empty($isSubscriptionActive) ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= !empty($isSubscriptionActive) ? '#16A34A' : '#DC2626' ?>; display: flex; align-items: center; justify-content: center; font-size: 22px;">
              <i class="fa <?= !empty($isSubscriptionActive) ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            </div>
            <div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #1E293B;">
                  <?= !empty($isSubscriptionActive) ? 'Abonnement B2B Actif' : 'Abonnement B2B Expiré ou Inactif' ?>
                </h4>
                <span class="badge-status <?= !empty($isSubscriptionActive) ? 'delivered' : 'cancelled' ?>" style="font-size: 11px;">
                  <?= !empty($isSubscriptionActive) ? 'Actif' : 'Suspendu' ?>
                </span>
              </div>
              <p style="margin: 2px 0 0; font-size: 13px; color: #64748B;">
                <?php if (!empty($isSubscriptionActive) && !empty($subscriptionDetails)): ?>
                  Forfait : <strong><?= htmlspecialchars($subscriptionDetails['libelle_forfait'] ?? 'Standard') ?></strong> • Expire le <?= htmlspecialchars(date('d/m/Y', strtotime($subscriptionDetails['date_fin_abonnement'] ?? ''))) ?> (<strong><?= (int)($subscriptionDetails['jours_restants'] ?? 0) ?> jours</strong> restants)
                <?php else: ?>
                  Votre compte pressing est actuellement inactif. Vous devez souscrire à un forfait pour débloquer le traitement des commandes et vos tarifs.
                <?php endif; ?>
              </p>
            </div>
          </div>
          <div>
            <a href="<?= RACINE ?>abonnement/list" class="btn btn-sm <?= !empty($isSubscriptionActive) ? 'btn-secondary' : 'btn-primary' ?>" style="font-weight: 700; <?= empty($isSubscriptionActive) ? 'background:#DC2626; border-color:#DC2626; color:#FFF;' : '' ?>">
              <i class="fa fa-id-card"></i> <?= !empty($isSubscriptionActive) ? 'Gérer mon forfait' : 'Souscrire maintenant' ?>
            </a>
          </div>
        </div>
      <?php endif; ?>

      <!-- PIPELINE D'ATELIER : STATUTS EN DIRECT -->
      <div style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="activity" style="color: #2563EB;"></i> Pipeline d'Atelier en Direct
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
          <!-- 1. À traiter / Inventaire -->
          <a href="<?= RACINE ?>commande/list" style="text-decoration: none; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.15s ease;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #B45309; text-transform: uppercase;">À traiter / Peser</span>
              <h4 id="pipe-atraiter" style="font-size: 22px; font-weight: 800; color: #92400E; margin: 2px 0 0 0;">0</h4>
            </div>
            <div style="width: 38px; height: 38px; border-radius: 8px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="alert-circle"></i>
            </div>
          </a>

          <!-- 2. En traitement / Lavage -->
          <a href="<?= RACINE ?>commande/list" style="text-decoration: none; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.15s ease;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #1D4ED8; text-transform: uppercase;">En traitement</span>
              <h4 id="pipe-traitement" style="font-size: 22px; font-weight: 800; color: #1E40AF; margin: 2px 0 0 0;">0</h4>
            </div>
            <div style="width: 38px; height: 38px; border-radius: 8px; background: #DBEAFE; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="refresh-cw"></i>
            </div>
          </a>

          <!-- 3. Prêtes / Repassées -->
          <a href="<?= RACINE ?>commande/list" style="text-decoration: none; background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.15s ease;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #6D28D9; text-transform: uppercase;">Prêtes en atelier</span>
              <h4 id="pipe-pretes" style="font-size: 22px; font-weight: 800; color: #5B21B6; margin: 2px 0 0 0;">0</h4>
            </div>
            <div style="width: 38px; height: 38px; border-radius: 8px; background: #EDE9FE; color: #6D28D9; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="package-check"></i>
            </div>
          </a>

          <!-- 4. En livraison -->
          <a href="<?= RACINE ?>commande/list" style="text-decoration: none; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.15s ease;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">En cours de livraison</span>
              <h4 id="pipe-livraison" style="font-size: 22px; font-weight: 800; color: #166534; margin: 2px 0 0 0;">0</h4>
            </div>
            <div style="width: 38px; height: 38px; border-radius: 8px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="truck"></i>
            </div>
          </a>

          <!-- 5. Livrées / Terminées -->
          <a href="<?= RACINE ?>commande/list" style="text-decoration: none; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.15s ease;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase;">Livrées / Finies</span>
              <h4 id="pipe-livrees" style="font-size: 22px; font-weight: 800; color: #065F46; margin: 2px 0 0 0;">0</h4>
            </div>
            <div style="width: 38px; height: 38px; border-radius: 8px; background: #D1FAE5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="check-circle"></i>
            </div>
          </a>
        </div>
      </div>

      <!-- RACCOURCIS D'ACTIONS RAPIDES ADAPTÉS AU RÔLE -->
      <div style="margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="zap" style="color: #2563EB;"></i> Actions Rapides
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
          <?php if ($isSuperAdmin): ?>
            <!-- SUPER ADMIN ACTIONS -->
            <a href="<?= RACINE ?>pressing/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-store"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Pressings Partenaires</strong>
                <small style="color: #64748B;">Hub & fiches 360°</small>
              </div>
            </a>

            <a href="<?= RACINE ?>abonnement/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-credit-card"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Abonnements B2B</strong>
                <small style="color: #64748B;">Souscriptions & suivis</small>
              </div>
            </a>

            <a href="<?= RACINE ?>forfait/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-box-open"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Forfaits B2B</strong>
                <small style="color: #64748B;">Grille & tarifs réseau</small>
              </div>
            </a>

            <a href="<?= RACINE ?>user/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-users-cog"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Utilisateurs & Rôles</strong>
                <small style="color: #64748B;">Comptes & droits</small>
              </div>
            </a>

            <a href="<?= RACINE ?>ville/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-map-marked-alt"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Villes & Quartiers</strong>
                <small style="color: #64748B;">Zones de couverture</small>
              </div>
            </a>

            <a href="<?= RACINE ?>notification/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-bullhorn"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Notifications</strong>
                <small style="color: #64748B;">Diffusions & alertes</small>
              </div>
            </a>

          <?php elseif ($isLivreur): ?>
            <!-- LIVREUR ACTIONS -->
            <a href="<?= RACINE ?>mission/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-motorcycle"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mes Missions</strong>
                <small style="color: #64748B;">Tournées & livraisons</small>
              </div>
            </a>

            <a href="<?= RACINE ?>commande/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-box"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Commandes Assignées</strong>
                <small style="color: #64748B;">Colis en cours</small>
              </div>
            </a>

            <a href="<?= RACINE ?>notification/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-bell"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Alertes & Notifs</strong>
                <small style="color: #64748B;">Messages de courses</small>
              </div>
            </a>

          <?php else: ?>
            <!-- PRESSING / PRO ACTIONS -->
            <a href="<?= RACINE ?>commande/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-clipboard-list"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mes Commandes</strong>
                <small style="color: #64748B;">Suivi & traitement</small>
              </div>
            </a>

            <a href="<?= RACINE ?>client/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-users"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mes Clients</strong>
                <small style="color: #64748B;">Carnet d'adresses</small>
              </div>
            </a>

            <a href="<?= RACINE ?>tarif/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-tags"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Grille Tarifaire</strong>
                <small style="color: #64748B;">Prix par article</small>
              </div>
            </a>

            <a href="<?= RACINE ?>horaire/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-clock"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mes Horaires</strong>
                <small style="color: #64748B;">Plages d'ouverture</small>
              </div>
            </a>

            <a href="<?= RACINE ?>livreur/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-motorcycle"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mes Livreurs</strong>
                <small style="color: #64748B;">Équipe de coursiers</small>
              </div>
            </a>

            <a href="<?= RACINE ?>retrait/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-wallet"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mon Portefeuille</strong>
                <small style="color: #64748B;">Solde & retraits Wave/OM</small>
              </div>
            </a>

            <a href="<?= RACINE ?>abonnement/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0; transition: all 0.15s ease;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FDF2F8; color: #DB2777; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-id-card"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Mon Abonnement</strong>
                <small style="color: #64748B;">Forfait & renouvellement</small>
              </div>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- TABLEAU DES DERNIÈRES COMMANDES -->
      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="padding: 16px 20px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
          <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="clock" style="color: #2563EB; width: 18px; height: 18px;"></i> Dernières Commandes
          </h2>
          <a href="<?= RACINE ?>commande/list" style="color: #2563EB; font-weight: 700; font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 4px;">
            Voir toutes les commandes <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
          </a>
        </div>

        <div class="mobile-list-container" id="recentOrdersMobile"></div>
        <div class="table-responsive-mobile">
          <table class="table" id="recentOrdersTable" style="width: 100%; border-collapse: collapse; margin: 0;">
            <thead style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
              <tr>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Code</th>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Type</th>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Client</th>
                <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 700; color: #64748B;">Montant</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">Étape de suivi</th>
                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Date</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="7" style="text-align: center; padding: 24px; color: #94A3B8;">Chargement des commandes récentes...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/mobile-list.js"></script>
<script src="<?= RACINE ?>json/dashboard.js?v=4"></script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
