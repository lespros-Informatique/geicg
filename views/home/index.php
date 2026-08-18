<?php
require_once __DIR__ . '/../../public/inc/header.php';
$isPressing = isset($isPressing) ? $isPressing : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$isLivreur = isset($isLivreur) ? $isLivreur : false;
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <!-- HEADER DU DASHBOARD -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layout-dashboard" style="color: #2563EB;"></i> 
            <span id="dash-title">
              <?= $isSuperAdmin ? 'Supervision Réseau' : ($isLivreur ? 'Espace Livreur' : 'Tableau de bord Atelier') ?>
            </span>
          </h1>
          <p id="dash-subtitle" class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0; font-size: 14px;">
            <?= $isSuperAdmin ? 'Vue globale du réseau de pressings, abonnements B2B et métriques financières' : ($isLivreur ? 'Gestion des tournées, collectes et livraisons de colis' : 'Aperçu en temps réel de votre activité et suivi des commandes') ?>
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <?php if ($isSuperAdmin): ?>
            <a href="<?= RACINE ?>pressing/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i class="fa fa-store"></i> Pressings
            </a>
            <a href="<?= RACINE ?>abonnement/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i class="fa fa-credit-card"></i> Gérer Abonnements
            </a>
          <?php elseif ($isLivreur): ?>
            <a href="<?= RACINE ?>mission/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i class="fa fa-route"></i> Mes Tournées
            </a>
          <?php else: ?>
            <a href="<?= RACINE ?>commande/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 10px 18px; border-radius: 10px;">
              <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle commande
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARTES KPI PRINCIPALES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 24px;">
        <!-- KPI 1 : CA TOTAL -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Chiffre d'Affaires</span>
            <h2 id="kpi-ca" style="font-size: 24px; font-weight: 800; color: #059669; margin: 4px 0 2px 0;">0 FCFA</h2>
            <small style="color: #94A3B8; font-size: 11px;">Total généré</small>
          </div>
          <div style="width: 52px; height: 52px; border-radius: 12px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i data-lucide="banknote"></i>
          </div>
        </div>

        <!-- KPI 2 : COMMANDES TOTALES -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Commandes</span>
            <h2 id="kpi-commandes" style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 4px 0 2px 0;">0</h2>
            <small style="color: #94A3B8; font-size: 11px;">Toutes commandes confondues</small>
          </div>
          <div style="width: 52px; height: 52px; border-radius: 12px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i data-lucide="clipboard-list"></i>
          </div>
        </div>

        <!-- KPI 3 : CLIENTS DU PRESSING -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;" id="label-kpi-clients">Clients Actifs</span>
            <h2 id="kpi-clients" style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 4px 0 2px 0;">0</h2>
            <small style="color: #94A3B8; font-size: 11px;">Clients fidélisés</small>
          </div>
          <div style="width: 52px; height: 52px; border-radius: 12px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i data-lucide="users"></i>
          </div>
        </div>

        <!-- KPI 4 : TARIFS / SERVICES -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;" id="label-kpi-catalogue">Tarifs au Catalogue</span>
            <h2 id="kpi-catalogue" style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 4px 0 2px 0;">0</h2>
            <small style="color: #94A3B8; font-size: 11px;">Articles & services actifs</small>
          </div>
          <div style="width: 52px; height: 52px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i data-lucide="tag"></i>
          </div>
        </div>
      </div>

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
