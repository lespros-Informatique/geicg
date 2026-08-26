<?php
  if (!isset($globalEtablissementLogo)) {
      try {
          $db = (new Database())->getCon();
          $stmt = $db->query("SELECT logo_etablissement, libelle_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1");
          $etabRow = $stmt->fetch(PDO::FETCH_ASSOC);
          $globalEtablissementLogo = $etabRow['logo_etablissement'] ?? '';
          $globalEtablissementNom = $etabRow['libelle_etablissement'] ?? 'GEICG';
      } catch (Exception $e) {
          $globalEtablissementLogo = '';
          $globalEtablissementNom = 'GEICG';
      }
  }
  $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

  // --- SYSTÈME D'AUTORISATIONS & RBAC DU SIDEBAR ---
  $userRoleCode = $_SESSION[USERS_AUTH]['role_code'] ?? ($_SESSION['role_code'] ?? 'ROLE_USER');
  $isSuperAdmin = in_array($userRoleCode, ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);

  // Récupérer les permissions de l'utilisateur
  $userPermissions = $_SESSION['permissions'] ?? [];
  if (!$isSuperAdmin && empty($userPermissions)) {
      try {
          $dbConn = (new Database())->getCon();
          $stmtP = $dbConn->prepare("
              SELECT rp.permission_code 
              FROM role_permissions rp
              JOIN permissions p ON rp.permission_code = p.code_permission
              WHERE rp.role_code = ? AND p.statut_permission = 'actif'
          ");
          $stmtP->execute([$userRoleCode]);
          $userPermissions = $stmtP->fetchAll(PDO::FETCH_COLUMN) ?: [];
          $_SESSION['permissions'] = $userPermissions;
      } catch (Exception $e) {
          $userPermissions = [];
      }
  }

  /**
   * Helper d'autorisation granulaire pour les éléments du menu
   */
  $canAccess = function(array $requiredPerms = [], array $allowedRoles = []) use ($isSuperAdmin, $userRoleCode, $userPermissions) {
      if ($isSuperAdmin) return true;
      if (!empty($allowedRoles) && in_array($userRoleCode, $allowedRoles, true)) return true;
      if (in_array('*', $userPermissions, true)) return true;
      foreach ($requiredPerms as $perm) {
          if (in_array($perm, $userPermissions, true)) return true;
      }
      return false;
  };
?>
<style>
  /* --- BASE SIDEBAR STYLES --- */
  .sidebar {
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .sidebar-accordion-toggle {
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    margin: 4px 0;
    border-radius: 8px;
    font-weight: 700;
    color: #475569;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.2s ease;
  }
  .sidebar-accordion-toggle:hover {
    background: rgba(30, 58, 95, 0.05);
    color: #1E3A5F;
  }
  .sidebar-accordion-toggle .chevron-icon {
    width: 14px;
    height: 14px;
    transition: transform 0.25s ease;
  }
  .sidebar-accordion-toggle[aria-expanded="true"] .chevron-icon {
    transform: rotate(180deg);
  }
  .sidebar-accordion-toggle[aria-expanded="true"] {
    color: #1E3A5F;
  }
  .sidebar-nav .nav-section-items {
    padding-left: 6px;
    display: none;
  }
  .sidebar-nav .nav-section-items.show {
    display: block;
  }
  .sidebar-nav .nav-item.sub {
    font-size: 13px;
    padding: 8px 12px 8px 16px;
    border-left: 2px solid transparent;
    margin: 2px 0;
    transition: all 0.2s ease;
  }
  .sidebar-nav .nav-item.sub.active,
  .sidebar-nav .nav-item.sub:hover {
    border-left-color: #1E3A5F;
    background: rgba(30, 58, 95, 0.06);
    color: #1E3A5F;
    font-weight: 700;
  }

  .sidebar-academic-badge .mini-badge {
    display: none;
  }

  /* --- COMPACT MINI SIDEBAR (COLLAPSED STATE) --- */
  .sidebar.collapsed {
    width: 76px !important;
    min-width: 76px !important;
    max-width: 76px !important;
    overflow-x: hidden;
  }
  .sidebar.collapsed .sidebar-header {
    padding: 12px 6px !important;
    justify-content: center !important;
    min-height: 64px !important;
  }
  .sidebar.collapsed .logo {
    display: none !important;
  }
  .sidebar.collapsed .sidebar-toggle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 38px !important;
    height: 38px !important;
    border-radius: 8px !important;
    background: #EFF6FF !important;
    color: #1E3A5F !important;
    border: 1.5px solid #BFDBFE !important;
    margin: 0 auto !important;
    cursor: pointer !important;
  }
  .sidebar.collapsed .sidebar-toggle:hover {
    background: #DBEAFE !important;
  }
  
  .sidebar.collapsed .sidebar-academic-badge {
    padding: 6px 4px !important;
    margin: 6px 6px !important;
  }
  .sidebar.collapsed .sidebar-academic-badge .full-badge {
    display: none !important;
  }
  .sidebar.collapsed .sidebar-academic-badge .mini-badge {
    display: block !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    color: #1E3A5F !important;
  }

  .sidebar.collapsed .sidebar-accordion-toggle {
    display: none !important;
  }
  .sidebar.collapsed .nav-section-items {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    padding: 0 !important;
  }
  .sidebar.collapsed .nav-section {
    padding: 6px 0 !important;
    margin: 4px 0 !important;
    border-top: 1px solid #E2E8F0 !important;
    width: 100% !important;
  }
  .sidebar.collapsed .nav-item {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 44px !important;
    height: 42px !important;
    margin: 3px auto !important;
    padding: 0 !important;
    border-radius: 8px !important;
    position: relative !important;
    border-left: none !important;
  }
  .sidebar.collapsed .nav-item span {
    display: none !important;
  }
  .sidebar.collapsed .nav-item i,
  .sidebar.collapsed .nav-item [data-lucide] {
    width: 20px !important;
    height: 20px !important;
    margin: 0 !important;
  }
  .sidebar.collapsed .nav-item.active {
    background: #1E3A5F !important;
    color: #FFFFFF !important;
  }
  .sidebar.collapsed .nav-item.active i,
  .sidebar.collapsed .nav-item.active [data-lucide] {
    color: #FFFFFF !important;
  }

  /* Tooltip flottant au survol en mode réduit */
  .sidebar.collapsed .nav-item:hover::after {
    content: attr(data-title);
    position: fixed;
    left: 86px;
    background: #0F172A;
    color: #FFFFFF;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 6px;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.3);
    z-index: 99999;
    pointer-events: none;
    line-height: 1.4;
  }

  .main-content.expanded {
    margin-left: 76px !important;
    width: calc(100% - 76px) !important;
    max-width: calc(100vw - 76px) !important;
  }
  .footer.expanded {
    margin-left: 76px !important;
    width: calc(100% - 76px) !important;
  }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo" style="display: flex; align-items: center; justify-content: center; max-height: 48px;">
            <?php if (!empty($globalEtablissementLogo)): ?>
                <?php $logoUrl = (strpos($globalEtablissementLogo, 'http') === 0) ? $globalEtablissementLogo : RACINE . ltrim($globalEtablissementLogo, '/'); ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo GEICG" style="max-height: 42px; max-width: 140px; object-fit: contain;">
            <?php else: ?>
                <span style="letter-spacing: 1px; color: #1E3A5F; font-size: 20px; font-weight: 800;">
                    <?= htmlspecialchars($globalEtablissementNom ?? 'GEICG') ?>
                </span>
            <?php endif; ?>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" title="Réduire / Déployer le menu">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <!-- Indicator Active Academic Year -->
    <div class="sidebar-academic-badge p-2 mx-2 my-2 rounded bg-light border text-center">
        <div class="full-badge">
            <div class="text-uppercase text-muted" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Année Académique</div>
            <div class="fw-bold text-primary" style="font-size: 13px;">
                <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?>
            </div>
        </div>
        <div class="mini-badge" title="Année <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?>">
            <?= htmlspecialchars(substr($_SESSION['annee_active_libelle'] ?? '25-26', -5)) ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= RACINE ?>" class="nav-item <?= ($currentUri === RACINE || $currentUri === RACINE . 'public/' || $currentUri === '/geicg/' || $currentUri === '/geicg/public/') ? 'active' : '' ?>" data-title="Tableau de bord">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <!-- === MODULE 1 : STRUCTURE & RÉFÉRENTIELS GLOBAUX === -->
        <?php
          $showEtab = $canAccess(['MANAGE_ETABLISSEMENT', 'CONFIG_SYSTEM'], ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);
          $showFilCycles = $canAccess(['MANAGE_FILIERES', 'VIEW_FILIERES', 'MANAGE_CYCLES', 'VIEW_CYCLES', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE']);
          $showNiveaux = $canAccess(['MANAGE_NIVEAUX', 'VIEW_NIVEAUX', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE']);
          $showSalles = $canAccess(['MANAGE_SALLES', 'VIEW_SALLES', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE', 'ROLE_ENSEIGNANT']);
          $showFonctions = $canAccess(['MANAGE_FONCTIONS', 'CONFIG_SYSTEM', 'MANAGE_STAFF'], ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL', 'ROLE_DIR_ETUDES']);
          $hasSecStructure = $showEtab || $showFilCycles || $showNiveaux || $showSalles || $showFonctions;
        ?>
        <?php if ($hasSecStructure): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-structure" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="building" style="width: 16px; height: 16px;"></i> <span>Structure Globale</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-structure">
                <?php if ($showEtab): ?>
                <a href="<?= RACINE ?>etablissement/config" class="nav-item sub <?= strpos($currentUri, '/etablissement/') !== false ? 'active' : '' ?>" data-title="Configuration Établissement">
                    <i data-lucide="landmark"></i> <span>Configuration Établissement</span>
                </a>
                <?php endif; ?>
                <?php if ($showFilCycles): ?>
                <a href="<?= RACINE ?>filiere_cycle/list" class="nav-item sub <?= strpos($currentUri, '/filiere_cycle/') !== false || strpos($currentUri, '/filiere/') !== false || strpos($currentUri, '/cycle/') !== false ? 'active' : '' ?>" data-title="Filières & Cycles">
                    <i data-lucide="layers"></i> <span>Filières & Cycles</span>
                </a>
                <?php endif; ?>
                <?php if ($showNiveaux): ?>
                <a href="<?= RACINE ?>niveau/list" class="nav-item sub <?= strpos($currentUri, '/niveau/') !== false || strpos($currentUri, '/filiere_niveau/') !== false ? 'active' : '' ?>" data-title="Niveaux d'Études">
                    <i data-lucide="trending-up"></i> <span>Niveaux d'Études</span>
                </a>
                <?php endif; ?>
                <?php if ($showSalles): ?>
                <a href="<?= RACINE ?>salle/list" class="nav-item sub <?= strpos($currentUri, '/salle/') !== false ? 'active' : '' ?>" data-title="Salles de Cours">
                    <i data-lucide="door-open"></i> <span>Salles de Cours</span>
                </a>
                <?php endif; ?>
                <?php if ($showFonctions): ?>
                <a href="<?= RACINE ?>fonction/list" class="nav-item sub <?= strpos($currentUri, '/fonction/') !== false ? 'active' : '' ?>" data-title="Fonctions & Postes">
                    <i data-lucide="user-check"></i> <span>Fonctions & Postes</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 2 : CADRE ACADÉMIQUE & SCOLARITÉS === -->
        <?php
          $showAnnees = $canAccess(['MANAGE_ANNEES', 'VIEW_ANNEES', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_SCOLARITE', 'ROLE_COMPTABLE']);
          $showClasses = $canAccess(['MANAGE_CLASSES', 'VIEW_CLASSES', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE']);
          $showSemestres = $canAccess(['MANAGE_SEMESTRES', 'VIEW_SEMESTRES', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP']);
          $showUe = $canAccess(['MANAGE_UE', 'VIEW_UE', 'CONFIG_ACADEMIQUE'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP']);
          $showMatieres = $canAccess(['MANAGE_MATIERES', 'VIEW_MATIERES', 'CONFIG_ACADEMIQUE', 'MANAGE_COEFFICIENTS'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_ENSEIGNANT']);
          $showScolariteGrille = $canAccess(['MANAGE_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE', 'MANAGE_TRANCHES', 'VIEW_TRANCHES'], ['ROLE_DIR_ETUDES', 'ROLE_SCOLARITE', 'ROLE_COMPTABLE', 'ROLE_CAISSIER']);
          $hasSecAcademique = $showAnnees || $showClasses || $showSemestres || $showUe || $showMatieres || $showScolariteGrille;
        ?>
        <?php if ($hasSecAcademique): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-academique" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> <span>Cadre Académique</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-academique">
                <?php if ($showAnnees): ?>
                <a href="<?= RACINE ?>annee/list" class="nav-item sub <?= strpos($currentUri, '/annee/') !== false ? 'active' : '' ?>" data-title="Années Académiques">
                    <i data-lucide="calendar-range"></i> <span>Années Académiques</span>
                </a>
                <?php endif; ?>
                <?php if ($showClasses): ?>
                <a href="<?= RACINE ?>classe/list" class="nav-item sub <?= strpos($currentUri, '/classe/') !== false ? 'active' : '' ?>" data-title="Classes & Promotions">
                    <i data-lucide="graduation-cap"></i> <span>Classes & Promotions</span>
                </a>
                <?php endif; ?>
                <?php if ($showSemestres): ?>
                <a href="<?= RACINE ?>semestre/list" class="nav-item sub <?= strpos($currentUri, '/semestre/') !== false ? 'active' : '' ?>" data-title="Semestres & Périodes">
                    <i data-lucide="clock"></i> <span>Semestres & Périodes</span>
                </a>
                <?php endif; ?>
                <?php if ($showUe): ?>
                <a href="<?= RACINE ?>ue/list" class="nav-item sub <?= strpos($currentUri, '/ue/') !== false || strpos($currentUri, '/unites_enseignement/') !== false ? 'active' : '' ?>" data-title="Unités d'Enseignement (UE)">
                    <i data-lucide="layers"></i> <span>Unités d'Enseignement (UE)</span>
                </a>
                <?php endif; ?>
                <?php if ($showMatieres): ?>
                <a href="<?= RACINE ?>matiere/list" class="nav-item sub <?= strpos($currentUri, '/matiere/') !== false ? 'active' : '' ?>" data-title="Matières & Coefficients">
                    <i data-lucide="book-open"></i> <span>Matières & Coefficients</span>
                </a>
                <?php endif; ?>
                <?php if ($showScolariteGrille): ?>
                <a href="<?= RACINE ?>scolarite/list" class="nav-item sub <?= strpos($currentUri, '/scolarite/') !== false || strpos($currentUri, '/tranche/') !== false ? 'active' : '' ?>" data-title="Scolarités & Échéanciers">
                    <i data-lucide="receipt"></i> <span>Scolarités & Échéanciers</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 3 : SCOLARITÉ & ADMISSIONS === -->
        <?php
          $showEtudiants = $canAccess(['MANAGE_ETUDIANTS', 'VIEW_ETUDIANTS', 'MANAGE_STUDENTS'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE', 'ROLE_COMPTABLE', 'ROLE_CAISSIER', 'ROLE_ENSEIGNANT']);
          $showParents = $canAccess(['MANAGE_PARENTS', 'VIEW_PARENTS', 'MANAGE_STUDENTS'], ['ROLE_DIR_ETUDES', 'ROLE_SCOLARITE']);
          $showInscriptions = $canAccess(['MANAGE_INSCRIPTIONS', 'VIEW_INSCRIPTIONS', 'MANAGE_ENROLLMENTS'], ['ROLE_DIR_ETUDES', 'ROLE_SCOLARITE', 'ROLE_COMPTABLE', 'ROLE_CAISSIER']);
          $showAccessoires = $canAccess(['MANAGE_ACCESSOIRES', 'VIEW_ACCESSOIRES'], ['ROLE_SCOLARITE', 'ROLE_COMPTABLE', 'ROLE_CAISSIER']);
          $hasSecEleves = $showEtudiants || $showParents || $showInscriptions || $showAccessoires;
        ?>
        <?php if ($hasSecEleves): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-eleves" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="users" style="width: 16px; height: 16px;"></i> <span>Scolarité & Élèves</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-eleves">
                <?php if ($showEtudiants): ?>
                <a href="<?= RACINE ?>etudiant/list" class="nav-item sub <?= strpos($currentUri, '/etudiant/') !== false ? 'active' : '' ?>" data-title="Registre des Étudiants">
                    <i data-lucide="user"></i> <span>Registre des Étudiants</span>
                </a>
                <?php endif; ?>
                <?php if ($showParents): ?>
                <a href="<?= RACINE ?>parent/list" class="nav-item sub <?= strpos($currentUri, '/parent/') !== false ? 'active' : '' ?>" data-title="Parents & Tuteurs">
                    <i data-lucide="contact"></i> <span>Parents & Tuteurs</span>
                </a>
                <?php endif; ?>
                <?php if ($showInscriptions): ?>
                <a href="<?= RACINE ?>inscription/list" class="nav-item sub <?= strpos($currentUri, '/inscription/') !== false ? 'active' : '' ?>" data-title="Inscriptions Annuelles">
                    <i data-lucide="user-plus"></i> <span>Inscriptions Annuelles</span>
                </a>
                <?php endif; ?>
                <?php if ($showAccessoires): ?>
                <a href="<?= RACINE ?>accessoire/list" class="nav-item sub <?= strpos($currentUri, '/accessoire/') !== false ? 'active' : '' ?>" data-title="Accessoires & Kits">
                    <i data-lucide="package"></i> <span>Accessoires & Kits</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 4 : FINANCE, CAISSE & DÉPENSES === -->
        <?php
          $showPaiements = $canAccess(['MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS'], ['ROLE_COMPTABLE', 'ROLE_CAISSIER']);
          $showOuvCaisse = $canAccess(['MANAGE_CAISSE', 'OUVERTURE_CAISSE', 'MANAGE_PAYMENTS'], ['ROLE_COMPTABLE', 'ROLE_CAISSIER']);
          $showClotCaisse = $canAccess(['CLOTURE_CAISSE', 'VIEW_RAPPORTS_FINANCIERS', 'MANAGE_PAYMENTS'], ['ROLE_COMPTABLE', 'ROLE_CAISSIER']);
          $showImpayes = $canAccess(['MANAGE_IMPAYES', 'VIEW_IMPAYES', 'SEND_RELANCES', 'MANAGE_MORATOIRES'], ['ROLE_DIR_ETUDES', 'ROLE_SCOLARITE', 'ROLE_COMPTABLE']);
          $showTypeDep = $canAccess(['MANAGE_TYPES_DEPENSE', 'VIEW_TYPES_DEPENSE', 'VALIDATE_EXPENSES'], ['ROLE_COMPTABLE']);
          $showDepenses = $canAccess(['RECORD_DEPENSES', 'VIEW_DEPENSES', 'VALIDATE_DEPENSES', 'VALIDATE_EXPENSES'], ['ROLE_COMPTABLE']);
          $hasSecFinance = $showPaiements || $showOuvCaisse || $showClotCaisse || $showImpayes || $showTypeDep || $showDepenses;
        ?>
        <?php if ($hasSecFinance): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-finance" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="wallet" style="width: 16px; height: 16px;"></i> <span>Finance & Caisse</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-finance">
                <?php if ($showPaiements): ?>
                <a href="<?= RACINE ?>paiement/list" class="nav-item sub <?= strpos($currentUri, '/paiement/') !== false ? 'active' : '' ?>" data-title="Caisse & Encaissements">
                    <i data-lucide="credit-card"></i> <span>Caisse & Encaissements</span>
                </a>
                <?php endif; ?>
                <?php if ($showOuvCaisse): ?>
                <a href="<?= RACINE ?>ouverture_caisse/list" class="nav-item sub <?= strpos($currentUri, '/ouverture_caisse/') !== false ? 'active' : '' ?>" data-title="Ouverture de Caisse">
                    <i data-lucide="unlock"></i> <span>Ouverture de Caisse</span>
                </a>
                <?php endif; ?>
                <?php if ($showClotCaisse): ?>
                <a href="<?= RACINE ?>cloture_caisse/list" class="nav-item sub <?= strpos($currentUri, '/cloture_caisse/') !== false ? 'active' : '' ?>" data-title="Clôture Caisse Journalière">
                    <i data-lucide="lock"></i> <span>Clôture Caisse Journalière</span>
                </a>
                <?php endif; ?>
                <?php if ($showImpayes): ?>
                <a href="<?= RACINE ?>impayes/list" class="nav-item sub <?= strpos($currentUri, '/impayes/') !== false ? 'active' : '' ?>" data-title="Relances & Impayés">
                    <i data-lucide="alert-triangle"></i> <span>Relances & Impayés</span>
                </a>
                <?php endif; ?>
                <?php if ($showTypeDep): ?>
                <a href="<?= RACINE ?>type_depense/list" class="nav-item sub <?= strpos($currentUri, '/type_depense/') !== false ? 'active' : '' ?>" data-title="Types de Dépenses">
                    <i data-lucide="tags"></i> <span>Types de Dépenses</span>
                </a>
                <?php endif; ?>
                <?php if ($showDepenses): ?>
                <a href="<?= RACINE ?>depense/list" class="nav-item sub <?= strpos($currentUri, '/depense/') !== false ? 'active' : '' ?>" data-title="Dépenses & Engagements">
                    <i data-lucide="file-minus"></i> <span>Dépenses & Engagements</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 5 : PÉDAGOGIE & ÉVALUATIONS === -->
        <?php
          $showEnseignants = $canAccess(['MANAGE_ENSEIGNANTS', 'VIEW_ENSEIGNANTS', 'MANAGE_TEACHERS'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP']);
          $showAffectations = $canAccess(['MANAGE_AFFECTATIONS', 'VIEW_AFFECTATIONS', 'MANAGE_TEACHERS'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_ENSEIGNANT']);
          $showEmplois = $canAccess(['MANAGE_EMPLOI_TEMPS', 'VIEW_EMPLOI_TEMPS', 'MANAGE_SCHEDULES'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE', 'ROLE_ENSEIGNANT']);
          $showAbsences = $canAccess(['MANAGE_ABSENCES', 'VIEW_ABSENCES'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE', 'ROLE_ENSEIGNANT']);
          $showNotes = $canAccess(['ENTER_NOTES', 'VIEW_NOTES', 'MANAGE_GRADES', 'LOCK_NOTES', 'LOCK_GRADES'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_ENSEIGNANT']);
          $showBulletins = $canAccess(['GENERATE_BULLETINS', 'VIEW_BULLETINS', 'VIEW_REPORTS'], ['ROLE_DIR_ETUDES', 'ROLE_CHEF_DEP', 'ROLE_SCOLARITE', 'ROLE_ENSEIGNANT']);
          $hasSecPedagogie = $showEnseignants || $showAffectations || $showEmplois || $showAbsences || $showNotes || $showBulletins;
        ?>
        <?php if ($hasSecPedagogie): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-pedagogie" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="award" style="width: 16px; height: 16px;"></i> <span>Pédagogie & Évaluations</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-pedagogie">
                <?php if ($showEnseignants): ?>
                <a href="<?= RACINE ?>enseignant/list" class="nav-item sub <?= strpos($currentUri, '/enseignant/') !== false && strpos($currentUri, '_matiere') === false ? 'active' : '' ?>" data-title="Corps Enseignant">
                    <i data-lucide="user-check"></i> <span>Corps Enseignant</span>
                </a>
                <?php endif; ?>
                <?php if ($showAffectations): ?>
                <a href="<?= RACINE ?>enseignant_matiere/list" class="nav-item sub <?= strpos($currentUri, '/enseignant_matiere/') !== false ? 'active' : '' ?>" data-title="Affectations Cours">
                    <i data-lucide="link"></i> <span>Affectations Cours</span>
                </a>
                <?php endif; ?>
                <?php if ($showEmplois): ?>
                <a href="<?= RACINE ?>emploi/list" class="nav-item sub <?= strpos($currentUri, '/emploi/') !== false ? 'active' : '' ?>" data-title="Emplois du Temps">
                    <i data-lucide="calendar"></i> <span>Emplois du Temps</span>
                </a>
                <?php endif; ?>
                <?php if ($showAbsences): ?>
                <a href="<?= RACINE ?>absence/list" class="nav-item sub <?= strpos($currentUri, '/absence/') !== false ? 'active' : '' ?>" data-title="Gestion des Absences">
                    <i data-lucide="user-x"></i> <span>Gestion des Absences</span>
                </a>
                <?php endif; ?>
                <?php if ($showNotes): ?>
                <a href="<?= RACINE ?>note/list" class="nav-item sub <?= strpos($currentUri, '/note/') !== false ? 'active' : '' ?>" data-title="Saisie des Notes">
                    <i data-lucide="edit-3"></i> <span>Saisie des Notes</span>
                </a>
                <?php endif; ?>
                <?php if ($showBulletins): ?>
                <a href="<?= RACINE ?>bulletin/list" class="nav-item sub <?= strpos($currentUri, '/bulletin/') !== false ? 'active' : '' ?>" data-title="Bulletins & PV de Notes">
                    <i data-lucide="file-text"></i> <span>Bulletins & PV de Notes</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 6 : COMMUNICATION & MÉDIAS === -->
        <?php
          $showEvents = $canAccess(['MANAGE_EVENTS', 'VIEW_EVENTS', 'MANAGE_COMMUNICATION'], ['ROLE_DIR_ETUDES', 'ROLE_COMMUNICATION', 'ROLE_SCOLARITE', 'ROLE_ENSEIGNANT']);
          $showGaleries = $canAccess(['MANAGE_GALLERY', 'VIEW_GALLERY', 'MANAGE_COMMUNICATION'], ['ROLE_DIR_ETUDES', 'ROLE_COMMUNICATION', 'ROLE_ENSEIGNANT']);
          $showDocs = $canAccess(['MANAGE_DOCUMENTS', 'VIEW_DOCUMENTS', 'MANAGE_COMMUNICATION'], ['ROLE_DIR_ETUDES', 'ROLE_COMMUNICATION', 'ROLE_SCOLARITE', 'ROLE_ENSEIGNANT']);
          $hasSecMedias = $showEvents || $showGaleries || $showDocs;
        ?>
        <?php if ($hasSecMedias): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-medias" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="globe" style="width: 16px; height: 16px;"></i> <span>Portail & Médias</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-medias">
                <?php if ($showEvents): ?>
                <a href="<?= RACINE ?>evenement/list" class="nav-item sub <?= strpos($currentUri, '/evenement/') !== false ? 'active' : '' ?>" data-title="Actualités & Événements">
                    <i data-lucide="bell"></i> <span>Actualités & Événements</span>
                </a>
                <?php endif; ?>
                <?php if ($showGaleries): ?>
                <a href="<?= RACINE ?>galerie/list" class="nav-item sub <?= strpos($currentUri, '/galerie/') !== false ? 'active' : '' ?>" data-title="Galeries Photos/Vidéos">
                    <i data-lucide="image"></i> <span>Galeries Photos/Vidéos</span>
                </a>
                <?php endif; ?>
                <?php if ($showDocs): ?>
                <a href="<?= RACINE ?>document/list" class="nav-item sub <?= strpos($currentUri, '/document/') !== false ? 'active' : '' ?>" data-title="Documents & Supports">
                    <i data-lucide="folder-down"></i> <span>Documents & Supports</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 7 : COMPTES & SÉCURITÉ (RBAC) === -->
        <?php
          $showUsers = $canAccess(['MANAGE_USERS', 'VIEW_USERS', 'MANAGE_ACCOUNTS'], ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);
          $showRoles = $canAccess(['MANAGE_ROLES', 'VIEW_ROLES', 'CONFIG_SECURITY'], ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);
          $showPerms = $canAccess(['MANAGE_PERMISSIONS', 'VIEW_PERMISSIONS', 'CONFIG_SECURITY'], ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL']);
          $hasSecSecurite = $showUsers || $showRoles || $showPerms;
        ?>
        <?php if ($hasSecSecurite): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-securite" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i> <span>Sécurité & Accès</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-securite">
                <?php if ($showUsers): ?>
                <a href="<?= RACINE ?>user/list" class="nav-item sub <?= strpos($currentUri, '/user/') !== false ? 'active' : '' ?>" data-title="Utilisateurs Système">
                    <i data-lucide="users"></i> <span>Utilisateurs Système</span>
                </a>
                <?php endif; ?>
                <?php if ($showRoles): ?>
                <a href="<?= RACINE ?>role/list" class="nav-item sub <?= strpos($currentUri, '/role/') !== false ? 'active' : '' ?>" data-title="Rôles & Groupes">
                    <i data-lucide="shield"></i> <span>Rôles & Groupes</span>
                </a>
                <?php endif; ?>
                <?php if ($showPerms): ?>
                <a href="<?= RACINE ?>permission/list" class="nav-item sub <?= strpos($currentUri, '/permission/') !== false ? 'active' : '' ?>" data-title="Permissions Granulaires">
                    <i data-lucide="key"></i> <span>Permissions Granulaires</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </nav>
</aside>

<script>
$(document).ready(function() {
  // Accordéons du sidebar
  $(document).on('click', '.sidebar-accordion-toggle', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var $toggle = $(this);
    var targetId = $toggle.attr('data-bs-target');
    var $target = $(targetId);

    if ($target.length) {
      var isExpanded = $toggle.attr('aria-expanded') === 'true';
      if (isExpanded) {
        $target.slideUp(200, function() {
          $target.removeClass('show');
        });
        $toggle.attr('aria-expanded', 'false');
      } else {
        $target.slideDown(200, function() {
          $target.addClass('show');
        });
        $toggle.attr('aria-expanded', 'true');
      }
    }
  });

  // Déplier automatiquement la section contenant le lien actif
  var $activeLink = $('.sidebar-nav .nav-item.sub.active');
  if ($activeLink.length) {
    var $parentItems = $activeLink.closest('.nav-section-items');
    if ($parentItems.length) {
      $parentItems.addClass('show').show();
      var $parentToggle = $parentItems.siblings('.sidebar-accordion-toggle');
      if ($parentToggle.length) {
        $parentToggle.attr('aria-expanded', 'true');
      }
    }
  } else {
    var $firstSection = $('.sidebar-nav .nav-section-items').first();
    if ($firstSection.length) {
      $firstSection.addClass('show').show();
      var $firstToggle = $firstSection.siblings('.sidebar-accordion-toggle');
      if ($firstToggle.length) {
        $firstToggle.attr('aria-expanded', 'true');
      }
    }
  }

  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>
