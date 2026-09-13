<?php
require_once __DIR__ . '/../../public/inc/header.php';

$stats = $stats ?? [];
$annees = $annees ?? [];
$auth = $auth ?? ($_SESSION[USERS_AUTH] ?? []);
$recentInscriptions = $recentInscriptions ?? [];
$recentPaiements = $recentPaiements ?? [];
$recentDepenses = $recentDepenses ?? [];
$teacherCourses = $teacherCourses ?? [];

// Récupération dynamique des permissions utilisateur (100% RBAC - Basé uniquement sur les permissions)
$rawPerms = $_SESSION['permissions'] ?? [];
if (empty($rawPerms) && isset($_SESSION[USERS_AUTH]['permissions']) && is_array($_SESSION[USERS_AUTH]['permissions']) && !isset($_SESSION[USERS_AUTH]['permissions']['create'])) {
    $rawPerms = $_SESSION[USERS_AUTH]['permissions'];
}
$userPermissions = is_array($rawPerms) ? $rawPerms : [];

// Helper d'autorisation granulaire 100% basé sur les permissions
$hasPerm = function(string $code) use ($userPermissions): bool {
    return in_array('*', $userPermissions, true) || in_array($code, $userPermissions, true);
};

$isAdminOrDG = $hasPerm('VIEW_DASHBOARD_EXECUTIVE');
$isFinance = $hasPerm('VIEW_DASHBOARD_FINANCE');
$isPedagogie = $hasPerm('VIEW_DASHBOARD_PEDAGOGIE');
$isScolarite = $hasPerm('VIEW_DASHBOARD_SCOLARITE');
$isEnseignant = $hasPerm('VIEW_DASHBOARD_ENSEIGNANT');
$isCommunication = $hasPerm('VIEW_DASHBOARD_COMMUNICATION');
$canViewActions = $hasPerm('VIEW_DASHBOARD_ACTIONS');

$monthlyFinancials = $stats['monthly_financials'] ?? ['labels' => [], 'encaissements' => [], 'depenses' => []];
$filieresDistribution = $stats['filieres_distribution'] ?? ['labels' => [], 'series' => []];
$guichetAlerts = $stats['guichet_alerts'] ?? ['dossiers_incomplets' => 0, 'kits_en_attente' => 0];

$caAttendu = (float)($stats['ca_attendu'] ?? 0);
$caEncaisse = (float)($stats['ca_encaisse'] ?? 0);
$tauxRecouvrement = ($caAttendu > 0) ? min(100, round(($caEncaisse / $caAttendu) * 100, 1)) : 0;
?>

<!-- Include ApexCharts library for responsive interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
  .dash-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.03);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .dash-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    border-color: #CBD5E1;
  }
  .dash-quick-link {
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 12px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    transition: all 0.2s ease;
  }
  .dash-quick-link:hover {
    background: #F8FAFC;
    border-color: var(--primary-color, #1E3A5F);
    transform: translateY(-2px);
  }
  
  /* Grille à 4 Colonnes & Grandes Cartes avec Métriques Géantes */
  .dash-module-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
  }
  @media (max-width: 1366px) {
    .dash-module-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 768px) {
    .dash-module-grid {
      grid-template-columns: 1fr;
    }
  }
  .dash-module-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 22px;
    text-decoration: none !important;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 170px;
    box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03), 0 2px 4px -1px rgba(15, 23, 42, 0.02);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .dash-module-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 28px -4px rgba(15, 23, 42, 0.1), 0 8px 16px -4px rgba(15, 23, 42, 0.06);
    border-color: #CBD5E1;
  }
  .dash-module-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .dash-module-num {
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
    margin-top: 12px;
    letter-spacing: -0.5px;
  }
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- ========================================================================= -->
      <!-- BANNIÈRE HERO D'EN-TÊTE DU TABLEAU DE BORD                                -->
      <!-- ========================================================================= -->
      <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border-radius: 16px; padding: 24px 28px; color: #FFFFFF; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(30, 58, 95, 0.3); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <span style="background: rgba(255,255,255,0.15); font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
              GROUPE EICG
            </span>
            <span style="font-size: 12px; color: #94A3B8;">&bull; <?= date('d/m/Y') ?></span>
          </div>
          <h1 style="font-size: 24px; font-weight: 900; margin: 0; color: #FFFFFF; letter-spacing: -0.5px;">
            Bonjour, <?= htmlspecialchars($auth['prenom_user'] ?? ($auth['nom_user'] ?? 'Utilisateur')) ?> 👋
          </h1>
          <p style="color: #CBD5E1; font-size: 13.5px; margin: 6px 0 0 0;">
            Bienvenue sur le système de gestion intégrée GEICG. 
          </p>
        </div>


      </div>

      <!-- ========================================================================= -->
      <!-- BANDEAU D'ALERTES RAPIDES GUICHET & RECOUVREMENT                          -->
      <!-- ========================================================================= -->
      <?php if (($isAdminOrDG || $isScolarite || $isFinance) && ($guichetAlerts['dossiers_incomplets'] > 0 || $guichetAlerts['kits_en_attente'] > 0 || ($stats['reliquat_impayes'] ?? 0) > 0)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 24px;">
          
          <?php if ($guichetAlerts['dossiers_incomplets'] > 0 && ($hasPerm('MANAGE_DEPOT_DOSSIERS') || $isScolarite || $isAdminOrDG)): ?>
            <a href="<?= RACINE ?>dossier_etudiant/list" class="dash-card" style="text-decoration: none; border-left: 4px solid #EA580C; background: #FFF7ED; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #FFEDD5; color: #C2410C; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="folder-alert" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                  <div style="font-size: 13px; font-weight: 800; color: #9A3412;"><?= $guichetAlerts['dossiers_incomplets'] ?> Dossier(s) Incomplet(s)</div>
                  <div style="font-size: 11.5px; color: #C2410C;">Pièces requises en attente de dépôt</div>
                </div>
              </div>
              <i data-lucide="chevron-right" style="width: 18px; height: 18px; color: #C2410C;"></i>
            </a>
          <?php endif; ?>

          <?php if ($guichetAlerts['kits_en_attente'] > 0 && ($hasPerm('MANAGE_REMISE_KITS') || $isScolarite || $isAdminOrDG)): ?>
            <a href="<?= RACINE ?>accessoire_inscription/registre" class="dash-card" style="text-decoration: none; border-left: 4px solid #D97706; background: #FFFBEB; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="package-check" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                  <div style="font-size: 13px; font-weight: 800; color: #92400E;"><?= $guichetAlerts['kits_en_attente'] ?> Kit(s) en Attente de Retrait</div>
                  <div style="font-size: 11.5px; color: #B45309;">Fournitures attribuées à émarger</div>
                </div>
              </div>
              <i data-lucide="chevron-right" style="width: 18px; height: 18px; color: #B45309;"></i>
            </a>
          <?php endif; ?>

          <?php if (($stats['reliquat_impayes'] ?? 0) > 0 && ($hasPerm('MANAGE_IMPAYES') || $isFinance || $isAdminOrDG)): ?>
            <a href="<?= RACINE ?>impayes/list" class="dash-card" style="text-decoration: none; border-left: 4px solid #DC2626; background: #FEF2F2; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEE2E2; color: #991B1B; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                  <div style="font-size: 13px; font-weight: 800; color: #991B1B;">Reliquat Scolarités à Recouvrer</div>
                  <div style="font-size: 11.5px; color: #B91C1C;"><?= number_format($stats['reliquat_impayes'], 0, ',', ' ') ?> FCFA restant</div>
                </div>
              </div>
              <i data-lucide="chevron-right" style="width: 18px; height: 18px; color: #B91C1C;"></i>
            </a>
          <?php endif; ?>

        </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- SECTION 1 : KPI GRID ADAPTÉE STRICTEMENT AU PROFIL RBAC                   -->
      <!-- ========================================================================= -->
      
      <?php if ($isAdminOrDG): ?>
        <!-- KPI GRID : DIRECTION GÉNÉRALE & SUPERADMIN -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">

          <!-- Recouvrement Caisse -->
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Recouvrement Caisse</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 900; color: #047857; line-height: 1;">
              <?= number_format($stats['ca_encaisse'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 11px;">FCFA</span>
            </div>
            <div style="margin-top: 8px;">
              <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 3px;">
                <span>Taux de recouvrement</span>
                <span><?= $tauxRecouvrement ?>%</span>
              </div>
              <div style="height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden;">
                <div style="height: 100%; width: <?= $tauxRecouvrement ?>%; background: #047857; border-radius: 3px;"></div>
              </div>
            </div>
          </div>



          <!-- Dépenses Engagées -->
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Dépenses Engagées</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="arrow-up-right" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 900; color: #DC2626; line-height: 1;">
              <?= number_format($stats['total_depenses'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 11px;">FCFA</span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
              Total des décaissements validés
            </div>
          </div>

          <!-- Solde Net Financial -->
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Solde Net Caisse</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 900; color: <?= ($stats['solde_net'] ?? 0) >= 0 ? '#15803D' : '#DC2626' ?>; line-height: 1;">
              <?= number_format($stats['solde_net'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 11px;">FCFA</span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
              Recettes &minus; Dépenses
            </div>
          </div>
        </div>


      <?php elseif ($isFinance): ?>
        <!-- KPI GRID : COMPTABILITÉ & CAISSE -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Caisse Encaissée</span>
            <div style="font-size: 24px; font-weight: 900; color: #047857; margin-top: 8px;"><?= number_format($stats['ca_encaisse'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Règlements validés</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Scolarité Attendue</span>
            <div style="font-size: 24px; font-weight: 900; color: #1E3A5F; margin-top: 8px;"><?= number_format($stats['ca_attendu'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Montant global annuel</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Reliquat des Impayés</span>
            <div style="font-size: 24px; font-weight: 900; color: #DC2626; margin-top: 8px;"><?= number_format($stats['reliquat_impayes'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Reste à recouvrer</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Solde Net Financier</span>
            <div style="font-size: 24px; font-weight: 900; color: <?= ($stats['solde_net'] ?? 0) >= 0 ? '#15803D' : '#DC2626' ?>; margin-top: 8px;">
              <?= number_format($stats['solde_net'] ?? 0, 0, ',', ' ') ?> FCFA
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Encaissements &minus; Dépenses</div>
          </div>
        </div>

      <?php elseif ($isScolarite): ?>
        <!-- KPI GRID : SCOLARITÉ & ADMISSIONS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Inscriptions Validées</span>
            <div style="font-size: 26px; font-weight: 900; color: #1E3A5F; margin-top: 6px;"><?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Dossiers d'élèves actifs</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Classes Ouvertes</span>
            <div style="font-size: 26px; font-weight: 900; color: #047857; margin-top: 6px;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Promotions actives</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Absences Signalées</span>
            <div style="font-size: 26px; font-weight: 900; color: #D97706; margin-top: 6px;"><?= (int)($stats['total_absences'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Heures d'absence</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Reliquat Scolarité</span>
            <div style="font-size: 22px; font-weight: 900; color: #DC2626; margin-top: 6px;"><?= number_format($stats['reliquat_impayes'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">À relancer</div>
          </div>
        </div>

      <?php elseif ($isEnseignant): ?>
        <!-- KPI GRID : ENSEIGNANT / FORMATEUR -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Mes Cours & Matières</span>
            <div style="font-size: 26px; font-weight: 900; color: #1E3A5F; margin-top: 6px;"><?= (int)($stats['teacher_courses'] ?? count($teacherCourses)) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Affectations actives</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Mes Classes Attribuées</span>
            <div style="font-size: 26px; font-weight: 900; color: #047857; margin-top: 6px;"><?= (int)($stats['teacher_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Groupes / Niveaux</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Notes Enregistrées</span>
            <div style="font-size: 26px; font-weight: 900; color: #7E22CE; margin-top: 6px;"><?= (int)($stats['total_notes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Saisies dans le système</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Salles Disponibles</span>
            <div style="font-size: 26px; font-weight: 900; color: #475569; margin-top: 6px;"><?= (int)($stats['total_salles'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Espaces pédagogiques</div>
          </div>
        </div>

      <?php elseif ($isCommunication): ?>
        <!-- KPI GRID : COMMUNICATION & ÉVÉNEMENTS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Articles & Actualités</span>
            <div style="font-size: 26px; font-weight: 900; color: #1E3A5F; margin-top: 6px;"><?= (int)($stats['total_actualites'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Publications actives</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Événements</span>
            <div style="font-size: 26px; font-weight: 900; color: #7E22CE; margin-top: 6px;"><?= (int)($stats['total_evenements'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Planifiés / à venir</div>
          </div>

          <div class="dash-card">
            <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Documents & Médias</span>
            <div style="font-size: 26px; font-weight: 900; color: #047857; margin-top: 6px;"><?= (int)($stats['total_documents'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Fichiers partagés</div>
          </div>
        </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- SECTION 2 : GRAPHIQUES D'ANALYSE DÉCISIONNELLE (APEXCHARTS)                -->
      <!-- ========================================================================= -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 24px;">
        
        <!-- Graphique 1 : Évolution Mensuelle Financière -->
        <?php if ($isAdminOrDG || $isFinance): ?>
          <div class="dash-card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="trending-up" style="width: 20px; height: 20px; color: #047857;"></i> Flux de Trésorerie Mensuel
                </h3>
                <span style="font-size: 12px; color: #64748B;">Recouvrements de Scolarité vs Dépenses (FCFA)</span>
              </div>
            </div>
            <div id="chart-financial-flux" style="min-height: 290px;"></div>
          </div>
        <?php endif; ?>


      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 3 : PANORAMA & CARDS DES MODULES APPLICATIFS PAR GROUPES          -->
      <!-- ========================================================================= -->
      <?php if ($canViewActions): ?>
        <?php
          $canGroup1 = $hasPerm('VIEW_INSCRIPTIONS') || $hasPerm('MANAGE_INSCRIPTIONS') || $hasPerm('MANAGE_DEPOT_DOSSIERS') || $hasPerm('VIEW_DEPOT_DOSSIERS') || $hasPerm('MANAGE_REMISE_KITS') || $hasPerm('VIEW_REMISE_KITS') || $hasPerm('MANAGE_ACCESSOIRES') || $hasPerm('VIEW_ETUDIANTS') || $hasPerm('MANAGE_ETUDIANTS');
          $canGroup2 = $hasPerm('VIEW_PAIEMENTS') || $hasPerm('RECORD_PAIEMENTS') || $hasPerm('OUVERTURE_CAISSE') || $hasPerm('CLOTURE_CAISSE') || $hasPerm('MANAGE_CAISSE') || $hasPerm('MANAGE_IMPAYES') || $hasPerm('VIEW_IMPAYES') || $hasPerm('VIEW_DEPENSES') || $hasPerm('RECORD_DEPENSES');
          $canGroup3 = $hasPerm('VIEW_NOTES') || $hasPerm('ENTER_NOTES') || $hasPerm('VIEW_BULLETINS') || $hasPerm('GENERATE_BULLETINS') || $hasPerm('VIEW_ABSENCES') || $hasPerm('MANAGE_ABSENCES') || $hasPerm('VIEW_ENSEIGNANTS') || $hasPerm('MANAGE_ENSEIGNANTS');
          $canGroup4 = $hasPerm('VIEW_CLASSES') || $hasPerm('MANAGE_CLASSES') || $hasPerm('VIEW_FILIERES') || $hasPerm('MANAGE_FILIERES') || $hasPerm('VIEW_MATIERES') || $hasPerm('MANAGE_MATIERES');
          $canGroup5 = $hasPerm('VIEW_USERS') || $hasPerm('MANAGE_USERS') || $hasPerm('MANAGE_ROLES');
        ?>

        <div style="margin-bottom: 36px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
            <div>
              <h3 style="font-size: 19px; font-weight: 900; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="layout-grid" style="color: #1E3A5F; width: 26px; height: 26px;"></i> Modules & Services Applicatifs GEICG
              </h3>
              <span style="font-size: 13px; color: #64748B;">Indicateurs clés et accès direct aux registres groupés par domaines fonctionnels</span>
            </div>
          </div>

          <!-- GROUPE 1 : Admissions & Scolarité -->
          <?php if ($canGroup1): ?>
            <div style="margin-bottom: 30px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #E2E8F0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="background: #1E3A5F; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="clipboard-check" style="width: 18px; height: 18px;"></i>
                  </div>
                  <div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Admissions, Inscriptions & Guichet Étudiants</h4>
                    <span style="font-size: 12px; color: #64748B;">Inscriptions des apprenants, suivi des dossiers et remise des fournitures</span>
                  </div>
                </div>
                <span style="font-size: 11px; font-weight: 800; background: #DBEAFE; color: #1E40AF; padding: 4px 12px; border-radius: 20px;">Domaine Scolarité</span>
              </div>

              <div class="dash-module-grid">
                <?php if ($hasPerm('VIEW_INSCRIPTIONS') || $hasPerm('MANAGE_INSCRIPTIONS')): ?>
                  <a href="<?= RACINE ?>inscription/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #EFF6FF; color: #1D4ED8;">
                          <i data-lucide="clipboard-check" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #DBEAFE; color: #1E40AF; padding: 4px 10px; border-radius: 20px;">Inscriptions</span>
                      </div>
                      <div class="dash-module-num" style="color: #0F172A;">
                        <?= number_format((int)($stats['total_etudiants'] ?? 0), 0, ',', ' ') ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Étudiants inscrits actifs</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Inscriptions & Admissions</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('MANAGE_DEPOT_DOSSIERS') || $hasPerm('VIEW_DEPOT_DOSSIERS') || $hasPerm('MANAGE_INSCRIPTIONS')): ?>
                  <a href="<?= RACINE ?>dossier_etudiant/list" class="dash-module-card" style="border-left: 4px solid #EA580C;">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FFF7ED; color: #EA580C;">
                          <i data-lucide="folder-check" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #FFEDD5; color: #C2410C; padding: 4px 10px; border-radius: 20px;">Dossiers</span>
                      </div>
                      <div class="dash-module-num" style="color: #EA580C;">
                        <?= (int)($guichetAlerts['dossiers_incomplets'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #9A3412; margin-top: 2px;">Dossier(s) incomplet(s) à compléter</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Dépôt des Dossiers</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('MANAGE_REMISE_KITS') || $hasPerm('VIEW_REMISE_KITS') || $hasPerm('MANAGE_ACCESSOIRES')): ?>
                  <a href="<?= RACINE ?>accessoire_inscription/registre" class="dash-module-card" style="border-left: 4px solid #15803D;">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #F0FDF4; color: #15803D;">
                          <i data-lucide="package-check" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #DCFCE7; color: #15803D; padding: 4px 10px; border-radius: 20px;">Kits</span>
                      </div>
                      <div class="dash-module-num" style="color: #15803D;">
                        <?= (int)($guichetAlerts['kits_en_attente'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #166534; margin-top: 2px;">Kit(s) en attente de retrait</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Registre & Remise Kits</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_ETUDIANTS') || $hasPerm('MANAGE_ETUDIANTS')): ?>
                  <a href="<?= RACINE ?>etudiant/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #EEF2FF; color: #4F46E5;">
                          <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #E0E7FF; color: #3730A3; padding: 4px 10px; border-radius: 20px;">Effectif</span>
                      </div>
                      <div class="dash-module-num" style="color: #3730A3;">
                        <?= number_format((int)($stats['total_etudiants'] ?? 0), 0, ',', ' ') ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Apprenants au registre</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Registre des Étudiants</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- GROUPE 2 : Finance & Caisse -->
          <?php if ($canGroup2): ?>
            <div style="margin-bottom: 30px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #E2E8F0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="background: #047857; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i>
                  </div>
                  <div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Gestion Financière, Caisse & Trésorerie</h4>
                    <span style="font-size: 12px; color: #64748B;">Encaissements de scolarité, ouvertures de caisse, relances impayés et dépenses</span>
                  </div>
                </div>
                <span style="font-size: 11px; font-weight: 800; background: #D1FAE5; color: #065F46; padding: 4px 12px; border-radius: 20px;">Domaine Finance</span>
              </div>

              <div class="dash-module-grid">
                <?php if ($hasPerm('VIEW_PAIEMENTS') || $hasPerm('RECORD_PAIEMENTS')): ?>
                  <a href="<?= RACINE ?>paiement/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #ECFDF5; color: #047857;">
                          <i data-lucide="credit-card" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #D1FAE5; color: #065F46; padding: 4px 10px; border-radius: 20px;">Encaissements</span>
                      </div>
                      <div class="dash-module-num" style="color: #047857; font-size: 22px;">
                        <?= number_format((float)($stats['ca_encaisse'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Total recettes encaissées</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Caisse & Règlement</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('OUVERTURE_CAISSE') || $hasPerm('CLOTURE_CAISSE') || $hasPerm('MANAGE_CAISSE')): ?>
                  <a href="<?= RACINE ?>session_caisse/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FEF3C7; color: #D97706;">
                          <i data-lucide="vault" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #FDE68A; color: #92400E; padding: 4px 10px; border-radius: 20px;">Sessions</span>
                      </div>
                      <div class="dash-module-num" style="color: #D97706;">
                        <?= (int)($stats['total_sessions_caisse'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Session(s) caisse ouverte(s)</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Sessions de Caisse</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('MANAGE_IMPAYES') || $hasPerm('VIEW_IMPAYES')): ?>
                  <a href="<?= RACINE ?>impayes/list" class="dash-module-card" style="border-left: 4px solid #DC2626;">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FEF2F2; color: #DC2626;">
                          <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #FEE2E2; color: #991B1B; padding: 4px 10px; border-radius: 20px;">Relances</span>
                      </div>
                      <div class="dash-module-num" style="color: #DC2626; font-size: 22px;">
                        <?= number_format((float)($stats['reliquat_impayes'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #991B1B; margin-top: 2px;">Reliquat scolarité impayé</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Relances & Impayés</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_DEPENSES') || $hasPerm('RECORD_DEPENSES')): ?>
                  <a href="<?= RACINE ?>depense/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FFF1F2; color: #E11D48;">
                          <i data-lucide="file-minus" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #FFE4E6; color: #9F1239; padding: 4px 10px; border-radius: 20px;">Dépenses</span>
                      </div>
                      <div class="dash-module-num" style="color: #E11D48; font-size: 22px;">
                        <?= number_format((float)($stats['total_depenses'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Décaissements validés</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Dépenses & Engagements</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- GROUPE 3 : Pédagogie, Examens & Notes -->
          <?php if ($canGroup3): ?>
            <div style="margin-bottom: 30px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #E2E8F0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="background: #7E22CE; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="award" style="width: 18px; height: 18px;"></i>
                  </div>
                  <div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Pédagogie, Évaluations, Notes & Absences</h4>
                    <span style="font-size: 12px; color: #64748B;">Planification des examens, saisie des devoirs, délibérations et corps enseignant</span>
                  </div>
                </div>
                <span style="font-size: 11px; font-weight: 800; background: #F3E8FF; color: #6B21A8; padding: 4px 12px; border-radius: 20px;">Domaine Pédagogie</span>
              </div>

              <div class="dash-module-grid">
                <?php if ($hasPerm('VIEW_NOTES') || $hasPerm('ENTER_NOTES')): ?>
                  <a href="<?= RACINE ?>composition/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FAF5FF; color: #7E22CE;">
                          <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #F3E8FF; color: #6B21A8; padding: 4px 10px; border-radius: 20px;">Examens</span>
                      </div>
                      <div class="dash-module-num" style="color: #7E22CE;">
                        <?= (int)($stats['total_notes'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Évaluations enregistrées</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Planning Examens</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>

                  <a href="<?= RACINE ?>note/saisieClasse" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #F3E8FF; color: #6B21A8;">
                          <i data-lucide="edit-3" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #E9D5FF; color: #581C87; padding: 4px 10px; border-radius: 20px;">Notes</span>
                      </div>
                      <div class="dash-module-num" style="color: #6B21A8;">
                        <?= (int)($stats['total_matieres'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Matières prêtes pour saisie</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Saisie des Notes</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_BULLETINS') || $hasPerm('GENERATE_BULLETINS')): ?>
                  <a href="<?= RACINE ?>bulletin/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FFFBEB; color: #B45309;">
                          <i data-lucide="award" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #FEF3C7; color: #92400E; padding: 4px 10px; border-radius: 20px;">Bulletins</span>
                      </div>
                      <div class="dash-module-num" style="color: #B45309;">
                        <?= (int)($stats['total_classes'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Classes prêtes pour PV</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Bulletins & PV</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_ABSENCES') || $hasPerm('MANAGE_ABSENCES')): ?>
                  <a href="<?= RACINE ?>absence/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #FEF3C7; color: #B45309;">
                          <i data-lucide="user-x" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #FDE68A; color: #92400E; padding: 4px 10px; border-radius: 20px;">Absences</span>
                      </div>
                      <div class="dash-module-num" style="color: #B45309;">
                        <?= (int)($stats['total_absences'] ?? 0) ?> <span style="font-size: 14px;">h</span>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Heures d'absences saisies</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Absences & Appel</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_ENSEIGNANTS') || $hasPerm('MANAGE_ENSEIGNANTS')): ?>
                  <a href="<?= RACINE ?>enseignant/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #F5F3FF; color: #5B21B6;">
                          <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #DDD6FE; color: #5B21B6; padding: 4px 10px; border-radius: 20px;">Enseignants</span>
                      </div>
                      <div class="dash-module-num" style="color: #5B21B6;">
                        <?= (int)($stats['total_enseignants'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Formateurs actifs au registre</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Corps Enseignant</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- GROUPE 4 : Structure Académique & Formations -->
          <?php if ($canGroup4): ?>
            <div style="margin-bottom: 30px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #E2E8F0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="background: #334155; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="layers" style="width: 18px; height: 18px;"></i>
                  </div>
                  <div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Structure Académique & Offre de Formation</h4>
                    <span style="font-size: 12px; color: #64748B;">Gestion des classes, promotions, filières, cycles d'études et catalogue de matières</span>
                  </div>
                </div>
                <span style="font-size: 11px; font-weight: 800; background: #E2E8F0; color: #334155; padding: 4px 12px; border-radius: 20px;">Domaine Académique</span>
              </div>

              <div class="dash-module-grid">
                <?php if ($hasPerm('VIEW_CLASSES') || $hasPerm('MANAGE_CLASSES')): ?>
                  <a href="<?= RACINE ?>classe/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #F0FDF4; color: #047857;">
                          <i data-lucide="graduation-cap" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #A7F3D0; color: #065F46; padding: 4px 10px; border-radius: 20px;">Classes</span>
                      </div>
                      <div class="dash-module-num" style="color: #047857;">
                        <?= (int)($stats['total_classes'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Promotions & groupes ouverts</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Classes & Promotions</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_FILIERES') || $hasPerm('MANAGE_FILIERES')): ?>
                  <a href="<?= RACINE ?>filiere_cycle/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #EFF6FF; color: #1D4ED8;">
                          <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #BFDBFE; color: #1E40AF; padding: 4px 10px; border-radius: 20px;">Filières</span>
                      </div>
                      <div class="dash-module-num" style="color: #1D4ED8;">
                        <?= (int)($stats['total_filieres'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Filières & spécialités</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Filières & Cycles</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <?php if ($hasPerm('VIEW_MATIERES') || $hasPerm('MANAGE_MATIERES')): ?>
                  <a href="<?= RACINE ?>matiere/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #F8FAFC; color: #334155;">
                          <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #E2E8F0; color: #334155; padding: 4px 10px; border-radius: 20px;">Matières</span>
                      </div>
                      <div class="dash-module-num" style="color: #334155;">
                        <?= (int)($stats['total_matieres'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Matières & Unités au catalogue</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Matières & Unités</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- GROUPE 5 : Administration & Sécurité -->
          <?php if ($canGroup5): ?>
            <div style="margin-bottom: 10px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #E2E8F0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="background: #0F172A; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
                  </div>
                  <div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Administration Système & Sécurité RBAC</h4>
                    <span style="font-size: 12px; color: #64748B;">Comptes utilisateurs, attribution des rôles et contrôle d'accès aux privilèges</span>
                  </div>
                </div>
                <span style="font-size: 11px; font-weight: 800; background: #0F172A; color: white; padding: 4px 12px; border-radius: 20px;">Sécurité Système</span>
              </div>

              <div class="dash-module-grid">
                <?php if ($hasPerm('VIEW_USERS') || $hasPerm('MANAGE_USERS') || $hasPerm('MANAGE_ROLES')): ?>
                  <a href="<?= RACINE ?>user/list" class="dash-module-card">
                    <div>
                      <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="dash-module-icon" style="background: #F8FAFC; color: #1E3A5F;">
                          <i data-lucide="shield-check" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; background: #E2E8F0; color: #1E3A5F; padding: 4px 10px; border-radius: 20px;">Sécurité</span>
                      </div>
                      <div class="dash-module-num" style="color: #1E3A5F;">
                        <?= (int)($stats['total_users'] ?? 0) ?>
                      </div>
                      <div style="font-size: 12px; font-weight: 700; color: #64748B; margin-top: 2px;">Comptes utilisateurs actifs</div>
                    </div>
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                      <strong style="color: #1E3A5F; font-size: 13px;">Utilisateurs & RBAC</strong>
                      <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: #94A3B8;"></i>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- SECTION 4 : TABLEAUX DE FLUX RÉCENT ENCAISSEMENT & INSCRIPTIONS            -->
      <!-- ========================================================================= -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px;">
        
        <!-- Table 1 : Inscriptions Récentes -->
        <?php if ($hasPerm('VIEW_INSCRIPTIONS') || $hasPerm('MANAGE_INSCRIPTIONS')): ?>
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="user-plus" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Inscriptions Récentes
              </h3>
              <a href="<?= RACINE ?>inscription/list" style="font-size: 12px; font-weight: 700; color: #1E3A5F; text-decoration: none;">Voir tout &rarr;</a>
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
                    <tr><td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucune inscription récente</td></tr>
                  <?php else: ?>
                    <?php foreach ($recentInscriptions as $insc): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px 12px; font-weight: 700; color: #1E3A5F; font-family: monospace;">
                          <?= htmlspecialchars($insc['matricule_etudiant'] ?? $insc['code_inscription']) ?>
                        </td>
                        <td style="padding: 10px 12px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($insc['nom_complet_etudiant'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; color: #475569;"><?= htmlspecialchars($insc['libelle_classe'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #0F172A;"><?= number_format((float)($insc['montant_scolarite_inscription'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <!-- Table 2 : Derniers Encaissés -->
        <?php if ($hasPerm('VIEW_PAIEMENTS') || $hasPerm('RECORD_PAIEMENTS')): ?>
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="wallet" style="width: 18px; height: 18px; color: #047857;"></i> Derniers Règlements Encaissés
              </h3>
              <a href="<?= RACINE ?>paiement/list" style="font-size: 12px; font-weight: 700; color: #047857; text-decoration: none;">Voir tout &rarr;</a>
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
                    <tr><td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucun paiement récent</td></tr>
                  <?php else: ?>
                    <?php foreach ($recentPaiements as $p): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px 12px; font-weight: 700; color: #047857; font-family: monospace;">
                          <?= htmlspecialchars($p['reference_paiement'] ?? ($p['code_paiement'] ?? '-')) ?>
                        </td>
                        <td style="padding: 10px 12px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($p['nom_complet_etudiant'] ?? '-') ?></td>
                        <td style="padding: 10px 12px;"><span class="badge bg-light text-dark border" style="font-weight: 700; font-size: 11px;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Caisse') ?></span></td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #047857;"><?= number_format((float)($p['montant_paiement'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT INITIALISATION DE APEXCHARTS ET LUCIDE ICONS                   -->
<!-- ========================================================================= -->
<script>
$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }

  // --- Graphique 1 : Flux Financier Mensuel (Encaissements vs Dépenses) ---
  var financialContainer = document.querySelector("#chart-financial-flux");
  if (financialContainer && typeof ApexCharts !== 'undefined') {
    var monthlyData = <?= json_encode($monthlyFinancials) ?>;
    
    var optionsFlux = {
      series: [
        {
          name: 'Encaissements Caisse',
          data: monthlyData.encaissements || [0,0,0,0,0,0,0,0,0,0,0,0]
        },
        {
          name: 'Dépenses Engagées',
          data: monthlyData.depenses || [0,0,0,0,0,0,0,0,0,0,0,0]
        }
      ],
      chart: {
        type: 'area',
        height: 300,
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false }
      },
      colors: ['#047857', '#DC2626'],
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 3 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      xaxis: {
        categories: monthlyData.labels || ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            return (val >= 1000) ? (val / 1000).toFixed(0) + 'k' : val;
          }
        }
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
          }
        }
      },
      legend: { position: 'top', horizontalAlign: 'right' }
    };

    var chartFlux = new ApexCharts(financialContainer, optionsFlux);
    chartFlux.render();
  }

  // --- Graphique 2 : Répartition par Filières (Donut Chart) ---
  var filiereContainer = document.querySelector("#chart-filieres-dist");
  if (filiereContainer && typeof ApexCharts !== 'undefined') {
    var filieresData = <?= json_encode($filieresDistribution) ?>;

    var optionsFiliere = {
      series: filieresData.series || [],
      labels: filieresData.labels || [],
      chart: {
        type: 'donut',
        height: 300,
        fontFamily: 'Inter, sans-serif'
      },
      colors: ['#1E3A5F', '#047857', '#7C3AED', '#D97706', '#0284C7', '#DC2626', '#475569'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: true },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: { width: 300 },
          legend: { position: 'bottom' }
        }
      }]
    };

    var chartFiliere = new ApexCharts(filiereContainer, optionsFiliere);
    chartFiliere.render();
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
