<?php
require_once __DIR__ . '/../../public/inc/header.php';

$stats = $stats ?? [];
$roleCode = $roleCode ?? ($_SESSION[USERS_AUTH]['role_code'] ?? 'ROLE_SUPERADMIN');
$auth = $auth ?? ($_SESSION[USERS_AUTH] ?? []);
$recentInscriptions = $recentInscriptions ?? [];
$recentPaiements = $recentPaiements ?? [];
$recentDepenses = $recentDepenses ?? [];
$teacherCourses = $teacherCourses ?? [];

$isAdminOrDG = in_array($roleCode, ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);
$isPedagogie = in_array($roleCode, ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP']);
$isScolarite = ($roleCode === 'ROLE_SCOLARITE');
$isFinance = in_array($roleCode, ['ROLE_COMPTABLE', 'ROLE_CAISSIER']);
$isEnseignant = ($roleCode === 'ROLE_ENSEIGNANT');
$isCommunication = ($roleCode === 'ROLE_COMMUNICATION');

// Rôle par défaut si non reconnu = Administrateur / Global
if (!$isAdminOrDG && !$isPedagogie && !$isScolarite && !$isFinance && !$isEnseignant && !$isCommunication) {
    $isAdminOrDG = true;
}
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- HEADER DU TABLEAU DE BORD ADAPTÉ AU PROFIL -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layout-dashboard" style="color: #1E3A5F; width: 26px; height: 26px;"></i> 
            <span>
              <?php if ($isAdminOrDG): ?>
                Tableau de Bord &bull; Direction & Administration
              <?php elseif ($isPedagogie): ?>
                Tableau de Bord &bull; Direction Pédagogique & Études
              <?php elseif ($isScolarite): ?>
                Tableau de Bord &bull; Service Scolarité & Admissions
              <?php elseif ($isFinance): ?>
                Tableau de Bord &bull; Finance, Caisse & Recouvrement
              <?php elseif ($isEnseignant): ?>
                Tableau de Bord &bull; Espace Enseignant / Formateur
              <?php elseif ($isCommunication): ?>
                Tableau de Bord &bull; Communication & Événements
              <?php endif; ?>
            </span>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
            Année Académique Active : <strong><?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?></strong> &bull; Bienvenue, <strong><?= htmlspecialchars($auth['nom_user'] ?? 'Utilisateur') ?></strong>
          </p>
        </div>

        <!-- ACTIONS RAPIDES D'EN-TÊTE -->
        <div class="page-header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <?php if ($isAdminOrDG || $isScolarite): ?>
            <a href="<?= RACINE ?>inscription/formulaire" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i> Nouvelle Inscription
            </a>
          <?php endif; ?>

          <?php if ($isAdminOrDG || $isFinance): ?>
            <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #059669; border-color: #059669; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i> Encaisser Paiement
            </a>
            <a href="<?= RACINE ?>depense/formulaire" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #FFFFFF; border: 1px solid #DC2626; color: #DC2626; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="arrow-up-right" style="width: 16px; height: 16px;"></i> Saisir Dépense
            </a>
          <?php endif; ?>

          <?php if ($isPedagogie || $isEnseignant): ?>
            <a href="<?= RACINE ?>note/formulaire" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Saisie des Notes
            </a>
            <a href="<?= RACINE ?>absence/formulaire" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #D97706; border-color: #D97706; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="clock" style="width: 16px; height: 16px;"></i> Pointer Absences
            </a>
          <?php endif; ?>

          <?php if ($isCommunication): ?>
            <a href="<?= RACINE ?>actualite/formulaire" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Actualité
            </a>
            <a href="<?= RACINE ?>evenement/formulaire" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #7C3AED; border-color: #7C3AED; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
              <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Ajouter Événement
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 1 : KPI GRID ADAPTÉE STRICTEMENT AU RÔLE                          -->
      <!-- ========================================================================= -->
      
      <?php if ($isAdminOrDG): ?>
        <!-- KPI GRID : DIRECTION GÉNÉRALE & SUPERADMIN -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <!-- KPI 1 : Effectif Étudiants -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Inscrits Actifs</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="users" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">
              <?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
              <?= (int)($stats['total_classes'] ?? 0) ?> Classes ouvertes
            </div>
          </div>

          <!-- KPI 2 : Caisse Encaissée -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Recouvrement Caisse</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 800; color: #047857; line-height: 1;">
              <?= number_format($stats['ca_encaisse'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
              Sur <?= number_format($stats['ca_attendu'] ?? 0, 0, ',', ' ') ?> attendus
            </div>
          </div>

          <!-- KPI 3 : Dépenses Engagées -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Dépenses & Charges</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="arrow-up-right" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 800; color: #DC2626; line-height: 1;">
              <?= number_format($stats['total_depenses'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
              Décaissements de l'année
            </div>
          </div>

          <!-- KPI 4 : Solde Net -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Solde Net Caisse</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 800; color: <?= ($stats['solde_net'] ?? 0) >= 0 ? '#15803D' : '#DC2626' ?>; line-height: 1;">
              <?= number_format($stats['solde_net'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
              Recettes &minus; Dépenses
            </div>
          </div>

          <!-- KPI 5 : Reliquat Impayés -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Reliquat Scolarité</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 22px; font-weight: 800; color: #D97706; line-height: 1;">
              <?= number_format($stats['reliquat_impayes'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
              Reste à recouvrer
            </div>
          </div>

          <!-- KPI 6 : Personnel Enseignant -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Corps Enseignant</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="graduation-cap" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #7E22CE; line-height: 1;">
              <?= (int)($stats['total_enseignants'] ?? 0) ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
              <?= (int)($stats['total_matieres'] ?? 0) ?> Matières au programme
            </div>
          </div>
        </div>

      <?php elseif ($isPedagogie): ?>
        <!-- KPI GRID : DIRECTION PÉDAGOGIQUE -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classes Actives</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="layout-grid"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0F172A;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Promotions configurées</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Effectif Étudiants</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="users"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #047857;"><?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Apprenants inscrits</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Enseignants</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;"><i data-lucide="graduation-cap"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #7E22CE;"><?= (int)($stats['total_enseignants'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Professeurs actifs</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Matières / Cours</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;"><i data-lucide="book-open"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #D97706;"><?= (int)($stats['total_matieres'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Modules au catalogue</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Notes Saisies</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;"><i data-lucide="check-square"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #16A34A;"><?= (int)($stats['total_notes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Évaluations validées</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Salles de Cours</span>
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #F8FAFC; color: #475569; display: flex; align-items: center; justify-content: center;"><i data-lucide="door-open"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #475569;"><?= (int)($stats['total_salles'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Salles disponibles</div>
          </div>
        </div>

      <?php elseif ($isFinance): ?>
        <!-- KPI GRID : COMPTABILITÉ & CAISSE -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Caisse Encaissée</span>
            <div style="font-size: 24px; font-weight: 800; color: #047857; margin-top: 8px;"><?= number_format($stats['ca_encaisse'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Règlements validés</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Scolarité Attendue</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 8px;"><?= number_format($stats['ca_attendu'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Montant global annuel</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Reliquat des Impayés</span>
            <div style="font-size: 24px; font-weight: 800; color: #DC2626; margin-top: 8px;"><?= number_format($stats['reliquat_impayes'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Reste à recouvrer</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Décaissements</span>
            <div style="font-size: 24px; font-weight: 800; color: #B91C1C; margin-top: 8px;"><?= number_format($stats['total_depenses'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Dépenses enregistrées</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Solde Net</span>
            <div style="font-size: 24px; font-weight: 800; color: <?= ($stats['solde_net'] ?? 0) >= 0 ? '#15803D' : '#DC2626' ?>; margin-top: 8px;">
              <?= number_format($stats['solde_net'] ?? 0, 0, ',', ' ') ?> FCFA
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Position financière nette</div>
          </div>
        </div>

      <?php elseif ($isScolarite): ?>
        <!-- KPI GRID : SCOLARITÉ & ADMISSIONS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Inscriptions Validées</span>
            <div style="font-size: 28px; font-weight: 800; color: #1E3A5F; margin-top: 6px;"><?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Dossiers actifs</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classes Ouvertes</span>
            <div style="font-size: 28px; font-weight: 800; color: #047857; margin-top: 6px;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Promotions actives</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Absences Signalées</span>
            <div style="font-size: 28px; font-weight: 800; color: #D97706; margin-top: 6px;"><?= (int)($stats['total_absences'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Heures d'absence</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Reliquat Scolarité</span>
            <div style="font-size: 22px; font-weight: 800; color: #DC2626; margin-top: 6px;"><?= number_format($stats['reliquat_impayes'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">À relancer</div>
          </div>
        </div>

      <?php elseif ($isEnseignant): ?>
        <!-- KPI GRID : ENSEIGNANT / FORMATEUR -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Mes Cours & Matières</span>
            <div style="font-size: 28px; font-weight: 800; color: #1E3A5F; margin-top: 6px;"><?= (int)($stats['teacher_courses'] ?? count($teacherCourses)) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Affectations actives</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Mes Classes Attribuées</span>
            <div style="font-size: 28px; font-weight: 800; color: #047857; margin-top: 6px;"><?= (int)($stats['teacher_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Groupes / Niveaux</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Notes Enregistrées</span>
            <div style="font-size: 28px; font-weight: 800; color: #7E22CE; margin-top: 6px;"><?= (int)($stats['total_notes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Saisies dans le système</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Salles & Équipements</span>
            <div style="font-size: 28px; font-weight: 800; color: #475569; margin-top: 6px;"><?= (int)($stats['total_salles'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Espaces pédagogiques</div>
          </div>
        </div>

      <?php elseif ($isCommunication): ?>
        <!-- KPI GRID : COMMUNICATION & ÉVÉNEMENTS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Articles & Actualités</span>
            <div style="font-size: 28px; font-weight: 800; color: #1E3A5F; margin-top: 6px;"><?= (int)($stats['total_actualites'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Publications actives</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Événements</span>
            <div style="font-size: 28px; font-weight: 800; color: #7E22CE; margin-top: 6px;"><?= (int)($stats['total_evenements'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Planifiés / à venir</div>
          </div>

          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Documents & Médias</span>
            <div style="font-size: 28px; font-weight: 800; color: #047857; margin-top: 6px;"><?= (int)($stats['total_documents'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Fichiers partagés</div>
          </div>
        </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- SECTION 2 : RACCOURCIS D'ACTIONS SELON LE RÔLE                           -->
      <!-- ========================================================================= -->
      <div style="margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="zap" style="color: #1E3A5F; width: 18px; height: 18px;"></i> Modules & Raccourcis Rapides
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
          <?php if ($isAdminOrDG): ?>
            <a href="<?= RACINE ?>inscription/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i data-lucide="user-check"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Inscriptions</strong>
                <small style="color: #64748B;">Registre annuel</small>
              </div>
            </a>

            <a href="<?= RACINE ?>paiement/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i data-lucide="wallet"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Caisse & Règlements</strong>
                <small style="color: #64748B;">Suivi des reçus</small>
              </div>
            </a>

            <a href="<?= RACINE ?>depense/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i data-lucide="arrow-up-right"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Dépenses</strong>
                <small style="color: #64748B;">Engagements & charges</small>
              </div>
            </a>

            <a href="<?= RACINE ?>classe/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i data-lucide="layout-grid"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Classes & Niveaux</strong>
                <small style="color: #64748B;">Structure pédagogique</small>
              </div>
            </a>

            <a href="<?= RACINE ?>user/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i data-lucide="users"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Utilisateurs & Rôles</strong>
                <small style="color: #64748B;">Comptes & habilitations</small>
              </div>
            </a>

            <a href="<?= RACINE ?>annee/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i data-lucide="calendar"></i>
              </div>
              <div>
                <strong style="color: #1E293B; font-size: 13px; display: block;">Années Académiques</strong>
                <small style="color: #64748B;">Sessions & semestres</small>
              </div>
            </a>

          <?php elseif ($isPedagogie): ?>
            <a href="<?= RACINE ?>classe/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="layout-grid"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Classes</strong><small style="color: #64748B;">Effectifs & promotions</small></div>
            </a>

            <a href="<?= RACINE ?>enseignant_matiere/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;"><i data-lucide="git-merge"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Affectation des Cours</strong><small style="color: #64748B;">Matières & coefficients</small></div>
            </a>

            <a href="<?= RACINE ?>note/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="edit-3"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Notes & Évaluations</strong><small style="color: #64748B;">Contrôles & examens</small></div>
            </a>

            <a href="<?= RACINE ?>bulletin/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;"><i data-lucide="award"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Bulletins & Moyennes</strong><small style="color: #64748B;">Délibérations</small></div>
            </a>

            <a href="<?= RACINE ?>emploi_temps/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center;"><i data-lucide="calendar"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Emploi du Temps</strong><small style="color: #64748B;">Plannings & créneaux</small></div>
            </a>

          <?php elseif ($isFinance): ?>
            <a href="<?= RACINE ?>paiement/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="credit-card"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Journal Caisse</strong><small style="color: #64748B;">Encaissements</small></div>
            </a>

            <a href="<?= RACINE ?>scolarite/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="layers"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Grille Scolarité</strong><small style="color: #64748B;">Montants par filière</small></div>
            </a>

            <a href="<?= RACINE ?>depense/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;"><i data-lucide="arrow-up-right"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Dépenses</strong><small style="color: #64748B;">Décaissements</small></div>
            </a>

            <a href="<?= RACINE ?>cloture_caisse/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;"><i data-lucide="lock"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Clôtures de Caisse</strong><small style="color: #64748B;">Arrêtés journaliers</small></div>
            </a>

          <?php elseif ($isScolarite): ?>
            <a href="<?= RACINE ?>inscription/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="user-plus"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Inscriptions</strong><small style="color: #64748B;">Registre & admissions</small></div>
            </a>

            <a href="<?= RACINE ?>etudiant/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="users"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Fiches Étudiants</strong><small style="color: #64748B;">Dossiers & matricules</small></div>
            </a>

            <a href="<?= RACINE ?>badge_etudiant/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;"><i data-lucide="credit-card"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Cartes / Badges</strong><small style="color: #64748B;">Impression des cartes</small></div>
            </a>

            <a href="<?= RACINE ?>absence/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Suivi des Absences</strong><small style="color: #64748B;">Pointage d'assiduité</small></div>
            </a>

          <?php elseif ($isEnseignant): ?>
            <a href="<?= RACINE ?>note/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="edit-3"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Mes Notes Saisies</strong><small style="color: #64748B;">Contrôles continus & TPs</small></div>
            </a>

            <a href="<?= RACINE ?>absence/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="user-x"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Appel & Absences</strong><small style="color: #64748B;">Pointage par cours</small></div>
            </a>

            <a href="<?= RACINE ?>emploi_temps/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;"><i data-lucide="calendar"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Mon Emploi du Temps</strong><small style="color: #64748B;">Créneaux de la semaine</small></div>
            </a>

          <?php elseif ($isCommunication): ?>
            <a href="<?= RACINE ?>actualite/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;"><i data-lucide="newspaper"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Actualités</strong><small style="color: #64748B;">Articles du portail</small></div>
            </a>

            <a href="<?= RACINE ?>evenement/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;"><i data-lucide="calendar"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Événements</strong><small style="color: #64748B;">Agenda académique</small></div>
            </a>

            <a href="<?= RACINE ?>document/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;"><i data-lucide="file-text"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Documents</strong><small style="color: #64748B;">Téléchargements publics</small></div>
            </a>

            <a href="<?= RACINE ?>galerie/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
              <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center;"><i data-lucide="image"></i></div>
              <div><strong style="color: #1E293B; font-size: 13px; display: block;">Galerie Photos</strong><small style="color: #64748B;">Albums & médias</small></div>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 3 : TABLEAUX & ACTIVITÉS RÉCENTES (FILTRÉS SELON LE RÔLE)        -->
      <!-- ========================================================================= -->
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 20px;">
        
        <!-- TABLEAU 1 : Inscriptions Récentes (Visible par Admin, DG, Scolarité, Pédagogie) -->
        <?php if ($isAdminOrDG || $isScolarite || $isPedagogie): ?>
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="user-plus" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Dernières Inscriptions
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
                        <td style="padding: 10px 12px; font-weight: 700; color: #1E3A5F; font-family: monospace;">
                          <a href="<?= RACINE ?>inscription/details/<?= $this->validator->crypter($insc['id_inscription']) ?>" style="color: #1E3A5F; text-decoration: underline;">
                            <?= htmlspecialchars($insc['matricule_etudiant'] ?? $insc['code_inscription']) ?>
                          </a>
                        </td>
                        <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($insc['nom_complet_etudiant'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; color: #475569;"><?= htmlspecialchars($insc['libelle_classe'] ?? 'Non assigné') ?></td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #0F172A;"><?= number_format((float)($insc['montant_scolarite_inscription'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <!-- TABLEAU 2 : Règlements Récents de Caisse (Visible par Admin, DG, Finance) -->
        <?php if ($isAdminOrDG || $isFinance): ?>
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="wallet" style="width: 18px; height: 18px; color: #059669;"></i> Derniers Règlements Encaissés
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
                      <td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucun paiement enregistré pour le moment</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentPaiements as $p): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px 12px; font-weight: 700; color: #059669; font-family: monospace;">
                          <a href="<?= RACINE ?>paiement/details/<?= $this->validator->crypter($p['id_paiement']) ?>" style="color: #059669; text-decoration: underline;">
                            <?= htmlspecialchars($p['reference_paiement'] ?? ($p['code_paiement'] ?? '-')) ?>
                          </a>
                        </td>
                        <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($p['nom_complet_etudiant'] ?? '-') ?></td>
                        <td style="padding: 10px 12px;"><span class="badge" style="background: #E0F2FE; color: #0369A1; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Caisse') ?></span></td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #047857;"><?= number_format((float)($p['montant_paiement'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <!-- TABLEAU 3 : Dépenses Récentes (Visible par Admin, DG, Finance) -->
        <?php if ($isAdminOrDG || $isFinance): ?>
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="arrow-up-right" style="width: 18px; height: 18px; color: #DC2626;"></i> Dernières Dépenses Engagées
              </h3>
              <a href="<?= RACINE ?>depense/list" style="font-size: 12px; font-weight: 600; color: #DC2626; text-decoration: none;">Voir tout</a>
            </div>

            <div style="overflow-x: auto;">
              <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                    <th style="padding: 10px 12px;">Code Dépense</th>
                    <th style="padding: 10px 12px;">Type / Motif</th>
                    <th style="padding: 10px 12px;">Période</th>
                    <th style="padding: 10px 12px; text-align: right;">Montant</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentDepenses)): ?>
                    <tr>
                      <td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucune dépense enregistrée</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentDepenses as $dep): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px 12px; font-weight: 700; color: #DC2626; font-family: monospace;">
                          <a href="<?= RACINE ?>depense/details/<?= $this->validator->crypter($dep['id_depense']) ?>" style="color: #DC2626; text-decoration: underline;">
                            <?= htmlspecialchars($dep['code_depense'] ?? '-') ?>
                          </a>
                        </td>
                        <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($dep['libelle_type_depense'] ?? ($dep['description_depense'] ?? 'Charge générale')) ?></td>
                        <td style="padding: 10px 12px; color: #64748B;"><?= htmlspecialchars($dep['periode_depense'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #DC2626;"><?= number_format((float)($dep['montant_depense'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <!-- TABLEAU 4 : Cours Affectés (Visible par Enseignant) -->
        <?php if ($isEnseignant): ?>
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="book-open" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Mes Cours & Affectations
              </h3>
            </div>

            <div style="overflow-x: auto;">
              <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                    <th style="padding: 10px 12px;">Matière</th>
                    <th style="padding: 10px 12px;">Classe</th>
                    <th style="padding: 10px 12px;">Niveau</th>
                    <th style="padding: 10px 12px; text-align: center;">Coef.</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($teacherCourses)): ?>
                    <tr>
                      <td colspan="4" style="padding: 16px; text-align: center; color: #94A3B8;">Aucun cours ne vous a été assigné pour le moment.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($teacherCourses as $tc): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px 12px; font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($tc['libelle_matiere'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($tc['libelle_classe'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; color: #64748B;"><?= htmlspecialchars($tc['libelle_niveau'] ?? '-') ?></td>
                        <td style="padding: 10px 12px; text-align: center; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($tc['coefficient'] ?? '1') ?></td>
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

<script>
$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
