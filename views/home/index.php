<?php
require_once __DIR__ . '/../../public/inc/header.php';

$stats = $stats ?? [];
$annees = $annees ?? [];
$roleCode = $roleCode ?? ($_SESSION[USERS_AUTH]['role_code'] ?? 'ROLE_SUPERADMIN');
$auth = $auth ?? ($_SESSION[USERS_AUTH] ?? []);
$recentInscriptions = $recentInscriptions ?? [];
$recentPaiements = $recentPaiements ?? [];
$recentDepenses = $recentDepenses ?? [];
$teacherCourses = $teacherCourses ?? [];

// Récupération dynamique des permissions utilisateur (100% RBAC)
$userPermissions = $_SESSION[USERS_AUTH]['permissions'] ?? [];
if (empty($userPermissions) && isset($this) && method_exists($this, 'getUserPermissions')) {
    $userPermissions = $this->getUserPermissions();
}

$hasPerm = function(string $code) use ($userPermissions): bool {
    return in_array('*', $userPermissions, true) || in_array($code, $userPermissions, true);
};

$isAdminOrDG = $hasPerm('VIEW_DASHBOARD_EXECUTIVE');
$isPedagogie = $hasPerm('VIEW_DASHBOARD_PEDAGOGIE');
$isScolarite = $hasPerm('VIEW_DASHBOARD_SCOLARITE');
$isFinance = $hasPerm('VIEW_DASHBOARD_FINANCE');
$isEnseignant = $hasPerm('VIEW_DASHBOARD_ENSEIGNANT');
$isCommunication = $hasPerm('VIEW_DASHBOARD_COMMUNICATION');
$canViewActions = $hasPerm('VIEW_DASHBOARD_ACTIONS');

// Si aucune permission spécifique n'est cochée mais que l'utilisateur a accès au dashboard
if (!$isAdminOrDG && !$isPedagogie && !$isScolarite && !$isFinance && !$isEnseignant && !$isCommunication) {
    $isAdminOrDG = true;
}

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
              <?php if ($isAdminOrDG): ?>Direction Générale & Synthèse
              <?php elseif ($isPedagogie): ?>Direction Pédagogique & Études
              <?php elseif ($isScolarite): ?>Service Scolarité & Admissions
              <?php elseif ($isFinance): ?>Service Finance, Caisse & Recouvrement
              <?php elseif ($isEnseignant): ?>Espace Enseignant / Formateur
              <?php elseif ($isCommunication): ?>Communication & Événements
              <?php else: ?>Vue Générale
              <?php endif; ?>
            </span>
            <span style="font-size: 12px; color: #94A3B8;">&bull; <?= date('d/m/Y') ?></span>
          </div>
          <h1 style="font-size: 24px; font-weight: 900; margin: 0; color: #FFFFFF; letter-spacing: -0.5px;">
            Bonjour, <?= htmlspecialchars($auth['prenom_user'] ?? ($auth['nom_user'] ?? 'Utilisateur')) ?> 👋
          </h1>
          <p style="color: #CBD5E1; font-size: 13.5px; margin: 6px 0 0 0;">
            Bienvenue sur le système de gestion intégrée GEICG. Suivi analytique et indicateurs clés en temps réel.
          </p>
        </div>

        <!-- Sélecteur d'Année Académique Active -->
        <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 12px 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; gap: 12px;">
          <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; color: #FFFFFF;">
            <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94A3B8; letter-spacing: 0.5px;">Année Académique Active</div>
            <form action="<?= RACINE ?>accessoire/list" method="GET" id="form-annee-dashboard" style="margin-top: 2px;">
              <select name="annee_code" onchange="window.location.href='<?= RACINE ?>?annee_code=' + this.value;" style="background: transparent; border: none; color: #FFFFFF; font-weight: 800; font-size: 14px; cursor: pointer; padding: 0; margin: 0; focus: outline-none;">
                <?php foreach ($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($stats['annee_code'] === $a['code_annee']) ? 'selected' : '' ?> style="color: #0F172A;">
                    <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
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
          <!-- Inscrits Actifs -->
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Effectif Inscrits</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="users" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 26px; font-weight: 900; color: #0F172A; line-height: 1;">
              <?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 8px;">
              <?= (int)($stats['total_classes'] ?? 0) ?> Classes ou promotions ouvertes
            </div>
          </div>

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

      <?php elseif ($isPedagogie): ?>
        <!-- KPI GRID : DIRECTION PÉDAGOGIQUE -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Classes Actives</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="graduation-cap"></i></div>
            </div>
            <div style="font-size: 26px; font-weight: 900; color: #0F172A;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Promotions actives</div>
          </div>

          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Effectif Apprenants</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="users"></i></div>
            </div>
            <div style="font-size: 26px; font-weight: 900; color: #047857;"><?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Étudiants inscrits</div>
          </div>

          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Corps Enseignant</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;"><i data-lucide="award"></i></div>
            </div>
            <div style="font-size: 26px; font-weight: 900; color: #7E22CE;"><?= (int)($stats['total_enseignants'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Formateurs actifs</div>
          </div>

          <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Notes Saisies</span>
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;"><i data-lucide="edit-3"></i></div>
            </div>
            <div style="font-size: 26px; font-weight: 900; color: #16A34A;"><?= (int)($stats['total_notes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">Évaluations enregistrées</div>
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

        <!-- Graphique 2 : Répartition des Effectifs par Filières -->
        <?php if ($isAdminOrDG || $isPedagogie || $isScolarite): ?>
          <div class="dash-card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="pie-chart" style="width: 20px; height: 20px; color: #1E3A5F;"></i> Inscrits par Filières
                </h3>
                <span style="font-size: 12px; color: #64748B;">Distribution des étudiants selon le domaine d'études</span>
              </div>
            </div>
            <div id="chart-filieres-dist" style="min-height: 290px;"></div>
          </div>
        <?php endif; ?>

      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 3 : PANORAMA & CARDS DES MODULES APPLICATIFS COMPLETS              -->
      <!-- ========================================================================= -->
      <?php if ($canViewActions): ?>
        <div style="margin-bottom: 28px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
              <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="grid" style="color: #1E3A5F; width: 22px; height: 22px;"></i> Modules & Services Applicatifs GEICG
              </h3>
              <span style="font-size: 12.5px; color: #64748B;">Accès direct aux fonctionnalités et registres selon votre profil d'habilitation RBAC</span>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
            
            <!-- MODULE 1 : Admissions & Scolarité -->
            <?php if ($hasPerm('VIEW_INSCRIPTIONS') || $hasPerm('MANAGE_INSCRIPTIONS')): ?>
              <a href="<?= RACINE ?>inscription/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="clipboard-check" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Inscriptions</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #DBEAFE; color: #1E40AF; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_etudiants'] ?? 0) ?></span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Admissions & dossiers</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('MANAGE_DEPOT_DOSSIERS') || $hasPerm('VIEW_DEPOT_DOSSIERS') || $hasPerm('MANAGE_INSCRIPTIONS')): ?>
              <a href="<?= RACINE ?>dossier_etudiant/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="folder-check" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Dépôt Dossiers</strong>
                    <?php if (($guichetAlerts['dossiers_incomplets'] ?? 0) > 0): ?>
                      <span style="font-size: 10px; font-weight: 800; background: #FFEDD5; color: #C2410C; padding: 2px 7px; border-radius: 10px;"><?= $guichetAlerts['dossiers_incomplets'] ?> incomp.</span>
                    <?php endif; ?>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Contrôle des pièces</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('MANAGE_REMISE_KITS') || $hasPerm('VIEW_REMISE_KITS') || $hasPerm('MANAGE_ACCESSOIRES')): ?>
              <a href="<?= RACINE ?>accessoire_inscription/registre" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="package-check" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Remise des Kits</strong>
                    <?php if (($guichetAlerts['kits_en_attente'] ?? 0) > 0): ?>
                      <span style="font-size: 10px; font-weight: 800; background: #DCFCE7; color: #15803D; padding: 2px 7px; border-radius: 10px;"><?= $guichetAlerts['kits_en_attente'] ?> en att.</span>
                    <?php endif; ?>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Émargement fourniture</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_ETUDIANTS') || $hasPerm('MANAGE_ETUDIANTS')): ?>
              <a href="<?= RACINE ?>etudiant/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="users" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Registre Étudiants</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Base des apprenants</small>
                </div>
              </a>
            <?php endif; ?>

            <!-- MODULE 2 : Finance & Caisse -->
            <?php if ($hasPerm('VIEW_PAIEMENTS') || $hasPerm('RECORD_PAIEMENTS')): ?>
              <a href="<?= RACINE ?>paiement/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="credit-card" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Caisse & Encaissements</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Règlements & reçus</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('OUVERTURE_CAISSE') || $hasPerm('CLOTURE_CAISSE') || $hasPerm('MANAGE_CAISSE')): ?>
              <a href="<?= RACINE ?>session_caisse/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="vault" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Sessions Caisse</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #FDE68A; color: #92400E; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_sessions_caisse'] ?? 0) ?> ouver.</span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Ouverture / Clôture</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('MANAGE_IMPAYES') || $hasPerm('VIEW_IMPAYES')): ?>
              <a href="<?= RACINE ?>impayes/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="alert-triangle" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Relances Impayés</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Suivi du recouvrement</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_DEPENSES') || $hasPerm('RECORD_DEPENSES')): ?>
              <a href="<?= RACINE ?>depense/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEE2E2; color: #991B1B; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="file-minus" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Dépenses & Charges</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Sorties de caisse</small>
                </div>
              </a>
            <?php endif; ?>

            <!-- MODULE 3 : Pédagogie, Examens & Notes -->
            <?php if ($hasPerm('VIEW_NOTES') || $hasPerm('ENTER_NOTES')): ?>
              <a href="<?= RACINE ?>composition/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Examens & Évaluations</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Planning des devoirs</small>
                </div>
              </a>

              <a href="<?= RACINE ?>note/saisieClasse" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #6B21A8; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="edit-3" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Saisie des Notes</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Par classe et matière</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_BULLETINS') || $hasPerm('GENERATE_BULLETINS')): ?>
              <a href="<?= RACINE ?>bulletin/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FFFBEB; color: #B45309; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="award" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <strong style="color: #0F172A; font-size: 13.5px; display: block;">Bulletins & PV</strong>
                  <small style="color: #64748B; font-size: 11.5px;">Génération & calculs</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_ABSENCES') || $hasPerm('MANAGE_ABSENCES')): ?>
              <a href="<?= RACINE ?>absence/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="user-x" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Absences & Appel</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #FDE68A; color: #92400E; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_absences'] ?? 0) ?> h</span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Suivi de présence</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_ENSEIGNANTS') || $hasPerm('MANAGE_ENSEIGNANTS')): ?>
              <a href="<?= RACINE ?>enseignant/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #F5F3FF; color: #5B21B6; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="user-check" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Corps Enseignant</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #DDD6FE; color: #5B21B6; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_enseignants'] ?? 0) ?></span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Formateurs actifs</small>
                </div>
              </a>
            <?php endif; ?>

            <!-- MODULE 4 : Structure & Académique -->
            <?php if ($hasPerm('VIEW_CLASSES') || $hasPerm('MANAGE_CLASSES')): ?>
              <a href="<?= RACINE ?>classe/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #047857; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="graduation-cap" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Classes & Promotions</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #A7F3D0; color: #065F46; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_classes'] ?? 0) ?></span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Groupes académiques</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_FILIERES') || $hasPerm('MANAGE_FILIERES')): ?>
              <a href="<?= RACINE ?>filiere_cycle/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="layers" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Filières & Cycles</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #BFDBFE; color: #1E40AF; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_filieres'] ?? 0) ?></span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Spécialités d'études</small>
                </div>
              </a>
            <?php endif; ?>

            <?php if ($hasPerm('VIEW_MATIERES') || $hasPerm('MANAGE_MATIERES')): ?>
              <a href="<?= RACINE ?>matiere/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #F8FAFC; color: #334155; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="book-open" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Matières & Unités</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #E2E8F0; color: #334155; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_matieres'] ?? 0) ?></span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Programme d'études</small>
                </div>
              </a>
            <?php endif; ?>

            <!-- MODULE 5 : Administration & Sécurité -->
            <?php if ($hasPerm('VIEW_USERS') || $hasPerm('MANAGE_USERS') || $hasPerm('MANAGE_ROLES')): ?>
              <a href="<?= RACINE ?>user/list" class="dash-quick-link">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center; shrink: 0;">
                  <i data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
                </div>
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                    <strong style="color: #0F172A; font-size: 13.5px;">Utilisateurs & RBAC</strong>
                    <span style="font-size: 10px; font-weight: 800; background: #E2E8F0; color: #1E3A5F; padding: 2px 7px; border-radius: 10px;"><?= (int)($stats['total_users'] ?? 0) ?></span>
                  </div>
                  <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 2px;">Comptes & autorisations</small>
                </div>
              </a>
            <?php endif; ?>

          </div>
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
