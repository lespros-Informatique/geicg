<?php
  if (!isset($globalEtablissementLogo)) {
      try {
          $db = (new Database())->getCon();
          $stmt = $db->query("SELECT logo_etablissement, libelle_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1");
          $etabRow = $stmt->fetch(PDO::FETCH_ASSOC);
          $rawLogo = $etabRow['logo_etablissement'] ?? '';
          $globalEtablissementLogo = (!empty($rawLogo)) ? ((strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/')) : '';
          $globalEtablissementNom = $etabRow['libelle_etablissement'] ?? 'GEICG';
      } catch (Exception $e) {
          $globalEtablissementLogo = '';
          $globalEtablissementNom = 'GEICG';
      }
  }
  $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

  // --- SYSTÈME D'AUTORISATIONS & RBAC DU SIDEBAR (100% PERMISSIONS) ---
  $userRoles = $_SESSION[USERS_AUTH]['roles'] ?? [];
  if (empty($userRoles)) {
      $singleRole = $_SESSION[USERS_AUTH]['role_code'] ?? ($_SESSION['role_code'] ?? 'ROLE_USER');
      $userRoles = !empty($singleRole) ? [$singleRole] : ['ROLE_USER'];
  }
  if (is_string($userRoles)) {
      $userRoles = [$userRoles];
  }

  // Récupérer les permissions cumulées de tous les rôles de l'utilisateur
  $userPermissions = $_SESSION['permissions'] ?? [];
  if (empty($userPermissions)) {
      try {
          $dbConn = (new Database())->getCon();
          $inClause = implode(',', array_fill(0, count($userRoles), '?'));
          $stmtP = $dbConn->prepare("
              SELECT DISTINCT rp.permission_code 
              FROM role_permissions rp
              JOIN permissions p ON rp.permission_code = p.code_permission
              WHERE rp.role_code IN ($inClause) AND p.statut_permission = 'actif'
          ");
          $stmtP->execute($userRoles);
          $userPermissions = $stmtP->fetchAll(PDO::FETCH_COLUMN) ?: [];
          $_SESSION['permissions'] = $userPermissions;
      } catch (Exception $e) {
          $userPermissions = [];
      }
  }

  /**
   * Helper d'autorisation granulaire pour les éléments du menu
   * Débloque le module uniquement si l'utilisateur possède au moins UNE des permissions spécifiées
   */
  $canAccess = function(array $requiredPerms = []) use ($userPermissions) {
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
    color: var(--primary-color);
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
    color: var(--primary-color);
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
    border-left-color: var(--primary-color);
    background: rgba(30, 58, 95, 0.06);
    color: var(--primary-color);
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
    color: var(--primary-color) !important;
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
    color: var(--primary-color) !important;
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
  .sidebar.collapsed .nav-item i {
    margin-right: 0 !important;
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

<aside class="sidebar bg-white border-end shadow-sm" id="sidebar">
    <!-- Sidebar Header / Logo & Collapser -->
    <div class="sidebar-header d-flex align-items-center justify-content-between p-3 border-bottom">
        <div class="logo d-flex align-items-center gap-2">
            <?php if (!empty($globalEtablissementLogo)): ?>
                <img src="<?= htmlspecialchars($globalEtablissementLogo) ?>" 
                     alt="Logo Établissement" class="img-fluid rounded" style="max-height: 40px; width: auto; object-fit: contain;"
                     onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-block';">
                <span class="logo-fallback-text" style="display: none; letter-spacing: 1px; color: #1E3A5F; font-size: 18px; font-weight: 800;">
                    <?= htmlspecialchars($globalEtablissementNom ?? 'GEICG') ?>
                </span>
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
    <div class="sidebar-academic-badge p-2 mx-2 my-2 rounded bg-light border text-center d-flex flex-column align-items-center justify-content-center">
        <div class="full-badge w-100 text-center d-flex flex-column align-items-center justify-content-center">
            <div class="text-uppercase text-muted text-center" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px; text-align: center; width: 100%;">Année Académique</div>
            <div class="fw-bold text-primary text-center" style="font-size: 13px; text-align: center; width: 100%;">
                <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'Aucune') ?>
            </div>
        </div>
        <div class="mini-badge text-center" title="Année <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'Aucune') ?>" style="text-align: center; width: 100%;">
            <?= htmlspecialchars(substr($_SESSION['annee_active_libelle'] ?? 'Aucune', -5)) ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= RACINE ?>" class="nav-item <?= ($currentUri === RACINE || $currentUri === RACINE . 'public/' || $currentUri === '/geicg/' || $currentUri === '/geicg/public/') ? 'active' : '' ?>" data-title="Tableau de bord">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <!-- === MODULE 1 : STRUCTURE & ÉTABLISSEMENT === -->
        <?php
          $showEtab = $canAccess(['MANAGE_ETABLISSEMENT', 'MANAGE_SCHOOL', 'CONFIG_SYSTEM']);
          $showFilCycles = $canAccess(['MANAGE_FILIERES', 'VIEW_FILIERES', 'MANAGE_CYCLES', 'VIEW_CYCLES', 'CONFIG_ACADEMIQUE']);
          $showNiveaux = $canAccess(['MANAGE_NIVEAUX', 'VIEW_NIVEAUX', 'CONFIG_ACADEMIQUE']);
          $showSalles = $canAccess(['MANAGE_SALLES', 'VIEW_SALLES', 'CONFIG_ACADEMIQUE']);
          $hasSecStructure = $showEtab || $showFilCycles || $showNiveaux || $showSalles;
        ?>
        <?php if ($hasSecStructure): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-structure" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="building" style="width: 16px; height: 16px;"></i> <span>Structure & Établissement</span>
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
                <a href="<?= RACINE ?>niveau/list" class="nav-item sub <?= strpos($currentUri, '/niveau/') !== false ? 'active' : '' ?>" data-title="Niveaux d'Études">
                    <i data-lucide="trending-up"></i> <span>Niveaux d'Études</span>
                </a>
                <?php endif; ?>
                <?php if ($showSalles): ?>
                <a href="<?= RACINE ?>salle/list" class="nav-item sub <?= strpos($currentUri, '/salle/') !== false ? 'active' : '' ?>" data-title="Salles de Cours">
                    <i data-lucide="door-open"></i> <span>Salles de Cours</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 2 : PLANIFICATION ACADÉMIQUE === -->
        <?php
          $showAnnees = $canAccess(['MANAGE_ANNEES', 'VIEW_ANNEES', 'MANAGE_YEARS', 'CONFIG_ACADEMIQUE']);
          $showClasses = $canAccess(['MANAGE_CLASSES', 'VIEW_CLASSES', 'CONFIG_ACADEMIQUE']);
          $showSemestres = $canAccess(['MANAGE_SEMESTRES', 'VIEW_SEMESTRES', 'CONFIG_ACADEMIQUE']);
          $showUe = $canAccess(['MANAGE_UE', 'VIEW_UE', 'CONFIG_ACADEMIQUE']);
          $showMatieres = $canAccess(['MANAGE_MATIERES', 'VIEW_MATIERES', 'CONFIG_ACADEMIQUE']);
          $hasSecAcademique = $showAnnees || $showClasses || $showSemestres || $showUe || $showMatieres;
        ?>
        <?php if ($hasSecAcademique): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-academique" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> <span>Planification Académique</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-academique">
                <?php if ($showAnnees || $showSemestres): ?>
                <a href="<?= RACINE ?>annee/list" class="nav-item sub <?= (strpos($currentUri, '/annee') !== false || strpos($currentUri, '/semestre') !== false) ? 'active' : '' ?>" data-title="Années & Semestres">
                    <i data-lucide="calendar-range"></i> <span>Années & Semestres</span>
                </a>
                <?php endif; ?>
                <?php if ($showClasses): ?>
                <a href="<?= RACINE ?>classe/list" class="nav-item sub <?= strpos($currentUri, '/classe/') !== false ? 'active' : '' ?>" data-title="Classes & Promotions">
                    <i data-lucide="graduation-cap"></i> <span>Classes & Promotions</span>
                </a>
                <?php endif; ?>
                <?php if ($showUe): ?>
                <a href="<?= RACINE ?>ue/list" class="nav-item sub <?= strpos($currentUri, '/ue/') !== false || strpos($currentUri, '/unites_enseignement/') !== false ? 'active' : '' ?>" data-title="Unités d'Enseignement (UE)">
                    <i data-lucide="layers"></i> <span>Unités d'Enseignement (UE)</span>
                </a>
                <?php endif; ?>
                <?php if ($showMatieres): ?>
                <a href="<?= RACINE ?>matiere/list" class="nav-item sub <?= strpos($currentUri, '/matiere/') !== false ? 'active' : '' ?>" data-title="Matières">
                    <i data-lucide="book-open"></i> <span>Matières</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 3 : ADMISSIONS & SCOLARITÉ === -->
        <?php
          $showEtudiants = $canAccess(['MANAGE_ETUDIANTS', 'VIEW_ETUDIANTS', 'MANAGE_STUDENTS']);
          $showParents = $canAccess(['MANAGE_PARENTS', 'VIEW_PARENTS', 'MANAGE_STUDENTS']);
          $showInscriptions = $canAccess(['MANAGE_INSCRIPTIONS', 'VIEW_INSCRIPTIONS', 'MANAGE_ENROLLMENTS']);
          $showDepotDossiers = $canAccess(['MANAGE_DEPOT_DOSSIERS', 'VIEW_DEPOT_DOSSIERS', 'MANAGE_PIECES', 'MANAGE_INSCRIPTIONS']);
          $showRemiseKits = $canAccess(['MANAGE_REMISE_KITS', 'VIEW_REMISE_KITS', 'MANAGE_ACCESSOIRES', 'MANAGE_INSCRIPTIONS']);
          $showPiecesFournir = $canAccess(['MANAGE_PIECES', 'VIEW_PIECES', 'CONFIG_ACADEMIQUE']);
          $showAccessoires = $canAccess(['MANAGE_ACCESSOIRES', 'VIEW_ACCESSOIRES', 'CONFIG_ACADEMIQUE']);
          $hasSecEleves = $showEtudiants || $showParents || $showInscriptions || $showDepotDossiers || $showRemiseKits || $showPiecesFournir || $showAccessoires;
        ?>
        <?php if ($hasSecEleves): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-eleves" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="users" style="width: 16px; height: 16px;"></i> <span>Admissions & Scolarité</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-eleves">
                <?php if ($showEtudiants): ?>
                <a href="<?= RACINE ?>etudiant/list" class="nav-item sub <?= strpos($currentUri, '/etudiant/') !== false ? 'active' : '' ?>" data-title="Registre des Étudiants">
                    <i data-lucide="users"></i> <span>Registre des Étudiants</span>
                </a>
                <?php endif; ?>
                <?php if ($showInscriptions): ?>
                <a href="<?= RACINE ?>inscription/list" class="nav-item sub <?= strpos($currentUri, '/inscription/') !== false ? 'active' : '' ?>" data-title="Inscriptions & Réinscriptions">
                    <i data-lucide="clipboard-check"></i> <span>Inscriptions & Réinscriptions</span>
                </a>
                <?php endif; ?>
                <?php if ($showDepotDossiers): ?>
                <a href="<?= RACINE ?>dossier_etudiant/list" class="nav-item sub <?= strpos($currentUri, '/dossier_etudiant/') !== false ? 'active' : '' ?>" data-title="Dépôt des Dossiers Étudiants">
                    <i data-lucide="folder-check"></i> <span>Dépôt des Dossiers Étudiants</span>
                </a>
                <?php endif; ?>
                <?php if ($showRemiseKits): ?>
                <a href="<?= RACINE ?>accessoire_inscription/registre" class="nav-item sub <?= (strpos($currentUri, '/accessoire_inscription/') !== false || strpos($currentUri, '/accessoire/registre') !== false) ? 'active' : '' ?>" data-title="Registre & Remise des Kits">
                    <i data-lucide="package-check"></i> <span>Registre & Remise des Kits</span>
                </a>
                <?php endif; ?>
                <?php if ($showParents): ?>
                <a href="<?= RACINE ?>parent/list" class="nav-item sub <?= strpos($currentUri, '/parent/') !== false ? 'active' : '' ?>" data-title="Parents & Tuteurs">
                    <i data-lucide="contact"></i> <span>Parents & Tuteurs</span>
                </a>
                <?php endif; ?>
                <?php if ($showPiecesFournir): ?>
                <a href="<?= RACINE ?>piece_fournir/list" class="nav-item sub <?= strpos($currentUri, '/piece_fournir') !== false ? 'active' : '' ?>" data-title="Catalogue des Pièces à Fournir">
                    <i data-lucide="file-check-2"></i> <span>Catalogue Pièces à Fournir</span>
                </a>
                <?php endif; ?>
                <?php if ($showAccessoires): ?>
                <a href="<?= RACINE ?>accessoire/list" class="nav-item sub <?= strpos($currentUri, '/accessoire/list') !== false ? 'active' : '' ?>" data-title="Catalogue des Kits & Accessoires">
                    <i data-lucide="package"></i> <span>Catalogue Kits & Accessoires</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 4 : FINANCE, CAISSE & DÉPENSES === -->
        <?php
          $showScolariteGrille = $canAccess(['MANAGE_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE']);
          $showPaiements = $canAccess(['MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS']);
          $showOuvCaisse = $canAccess(['MANAGE_CAISSE', 'OUVERTURE_CAISSE', 'MANAGE_PAYMENTS', 'RECORD_PAIEMENTS']);
          $showClotCaisse = $canAccess(['CLOTURE_CAISSE', 'VIEW_RAPPORTS_FINANCIERS', 'MANAGE_PAYMENTS']);
          $showImpayes = $canAccess(['MANAGE_IMPAYES', 'VIEW_IMPAYES', 'SEND_RELANCES', 'MANAGE_MORATOIRES']);
          $showTypeDep = $canAccess(['MANAGE_TYPES_DEPENSE', 'VIEW_TYPES_DEPENSE', 'VALIDATE_EXPENSES']);
          $showDepenses = $canAccess(['RECORD_DEPENSES', 'VIEW_DEPENSES', 'MANAGE_EXPENSES', 'VALIDATE_DEPENSES']);
          $hasSecFinance = $showScolariteGrille || $showPaiements || $showOuvCaisse || $showClotCaisse || $showImpayes || $showTypeDep || $showDepenses;
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
                <?php if ($showScolariteGrille): ?>
                <a href="<?= RACINE ?>scolarite/list" class="nav-item sub <?= strpos($currentUri, '/scolarite/') !== false || strpos($currentUri, '/tranche/') !== false ? 'active' : '' ?>" data-title="Scolarités & Échéanciers">
                    <i data-lucide="receipt"></i> <span>Scolarités & Échéanciers</span>
                </a>
                <?php endif; ?>
                <?php if ($showPaiements): ?>
                <a href="<?= RACINE ?>paiement/list" class="nav-item sub <?= strpos($currentUri, '/paiement/') !== false ? 'active' : '' ?>" data-title="Caisse & Encaissements">
                    <i data-lucide="credit-card"></i> <span>Caisse & Encaissements</span>
                </a>
                <?php endif; ?>
                <?php if ($showOuvCaisse || $showClotCaisse): ?>
                <a href="<?= RACINE ?>session_caisse/list" class="nav-item sub <?= (strpos($currentUri, '/session_caisse/') !== false || strpos($currentUri, '/ouverture_caisse/') !== false || strpos($currentUri, '/cloture_caisse/') !== false) ? 'active' : '' ?>" data-title="Sessions de Caisse">
                    <i data-lucide="vault"></i> <span>Sessions de Caisse</span>
                </a>
                <?php endif; ?>
                <?php if ($showImpayes): ?>
                <a href="<?= RACINE ?>impayes/list" class="nav-item sub <?= strpos($currentUri, '/impayes/') !== false ? 'active' : '' ?>" data-title="Relances & Impayés">
                    <i data-lucide="alert-triangle"></i> <span>Relances & Impayés</span>
                </a>
                <?php endif; ?>
                <?php if ($showDepenses || $showTypeDep): ?>
                <a href="<?= RACINE ?>depense/list" class="nav-item sub <?= (strpos($currentUri, '/depense') !== false || strpos($currentUri, '/type_depense') !== false) ? 'active' : '' ?>" data-title="Dépenses & Engagements">
                    <i data-lucide="file-minus"></i> <span>Dépenses & Engagements</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 5 : PÉDAGOGIE & ÉVALUATIONS === -->
        <?php
          $showEnseignants = $canAccess(['MANAGE_ENSEIGNANTS', 'VIEW_ENSEIGNANTS', 'MANAGE_TEACHERS']);
          $showAffectations = $canAccess(['MANAGE_AFFECTATIONS', 'VIEW_AFFECTATIONS', 'MANAGE_TEACHERS']);
          $showEmplois = $canAccess(['MANAGE_EMPLOI_TEMPS', 'VIEW_EMPLOI_TEMPS', 'MANAGE_SCHEDULES']);
          $showAbsences = $canAccess(['MANAGE_ABSENCES', 'VIEW_ABSENCES']);
          $showNotes = $canAccess(['ENTER_NOTES', 'VIEW_NOTES', 'MANAGE_GRADES', 'LOCK_NOTES']);
          $showBulletins = $canAccess(['GENERATE_BULLETINS', 'VIEW_BULLETINS', 'VIEW_REPORTS']);
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

                <?php if ($showEmplois): ?>
                <a href="<?= RACINE ?>emploi/list" class="nav-item sub <?= strpos($currentUri, '/emploi/') !== false ? 'active' : '' ?>" data-title="Emplois du Temps">
                    <i data-lucide="calendar"></i> <span>Emplois du Temps</span>
                </a>
                <?php endif; ?>
                <?php if ($showAbsences): ?>
                <a href="<?= RACINE ?>absence/list" class="nav-item sub <?= strpos($currentUri, '/absence/') !== false ? 'active' : '' ?>" data-title="Absences par Classe">
                    <i data-lucide="user-x"></i> <span>Absences & Appel par Classe</span>
                </a>
                <?php endif; ?>
                <?php if ($showNotes): ?>
                <a href="<?= RACINE ?>composition/list" class="nav-item sub <?= strpos($currentUri, '/composition/') !== false ? 'active' : '' ?>" data-title="Planning Compositions & Examens">
                    <i data-lucide="award"></i> <span>Planning Compositions & Examens</span>
                </a>
                <a href="<?= RACINE ?>note/saisieClasse" class="nav-item sub <?= strpos($currentUri, '/note/saisieClasse') !== false ? 'active' : '' ?>" data-title="Saisie des Notes par Classe">
                    <i data-lucide="edit-3"></i> <span>Saisie des Notes par Classe</span>
                </a>
                <a href="<?= RACINE ?>note/list" class="nav-item sub <?= (strpos($currentUri, '/note/list') !== false || (strpos($currentUri, '/note/') !== false && strpos($currentUri, 'saisieClasse') === false)) ? 'active' : '' ?>" data-title="Registre & Journal des Notes">
                    <i data-lucide="list"></i> <span>Registre & Journal des Notes</span>
                </a>
                <?php endif; ?>
                <?php if ($showBulletins): ?>
                <a href="<?= RACINE ?>bulletin/list" class="nav-item sub <?= strpos($currentUri, '/bulletin/') !== false ? 'active' : '' ?>" data-title="Bulletins & PV par Classe">
                    <i data-lucide="file-text"></i> <span>Bulletins & PV de Notes</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE 6 : PORTAIL & COMMUNICATION === -->
        <?php
          $showEvents = $canAccess(['MANAGE_EVENEMENTS', 'VIEW_EVENEMENTS', 'MANAGE_EVENTS']);
          $showGaleries = $canAccess(['MANAGE_GALLERY', 'VIEW_GALLERY', 'MANAGE_COMMUNICATION']);
          $showDocs = $canAccess(['MANAGE_DOCUMENTS', 'VIEW_DOCUMENTS']);
          $hasSecMedias = $showEvents || $showGaleries || $showDocs;
        ?>
        <?php if ($hasSecMedias): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-medias" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="globe" style="width: 16px; height: 16px;"></i> <span>Portail & Communication</span>
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

        <!-- === MODULE 7 : ADMINISTRATION & SÉCURITÉ === -->
        <?php
          $showUsers = $canAccess(['MANAGE_USERS', 'VIEW_USERS', 'MANAGE_ACCOUNTS']);
          $showFonctions = $canAccess(['MANAGE_FONCTIONS', 'VIEW_FONCTIONS']);
          $showRoles = $canAccess(['MANAGE_ROLES', 'VIEW_ROLES']);
          $showPerms = $canAccess(['MANAGE_PERMISSIONS', 'VIEW_PERMISSIONS']);
          $hasSecSecurite = $showUsers || $showFonctions || $showRoles || $showPerms;
        ?>
        <?php if ($hasSecSecurite): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-securite" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i> <span>Administration & Sécurité</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-securite">
                <?php if ($showUsers): ?>
                <a href="<?= RACINE ?>user/list" class="nav-item sub <?= strpos($currentUri, '/user/') !== false ? 'active' : '' ?>" data-title="Utilisateurs Système">
                    <i data-lucide="users"></i> <span>Utilisateurs Système</span>
                </a>
                <?php endif; ?>
                <?php if ($showFonctions): ?>
                <a href="<?= RACINE ?>fonction/list" class="nav-item sub <?= strpos($currentUri, '/fonction/') !== false ? 'active' : '' ?>" data-title="Fonctions & Postes">
                    <i data-lucide="user-check"></i> <span>Fonctions & Postes</span>
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
  // Toggle réduction/déploiement du sidebar
  $(document).on('click', '#sidebarToggle', function(e) {
    e.preventDefault();
    e.stopPropagation();
    if (typeof toggleSidebar === 'function') {
      toggleSidebar();
    } else {
      var $sidebar = $('#sidebar, #mainSidebar, .sidebar');
      var $mainContent = $('.main-content');
      var $footer = $('#footer, .footer');
      $sidebar.toggleClass('collapsed');
      var isCollapsed = $sidebar.hasClass('collapsed');
      $mainContent.toggleClass('expanded', isCollapsed);
      $footer.toggleClass('expanded', isCollapsed);
      try {
        localStorage.setItem('geicg_sidebar_collapsed', isCollapsed ? '1' : '0');
      } catch(ex) {}
      if (window.lucide) {
        lucide.createIcons();
      }
    }
  });

  // Accordéons du sidebar (Comportement accordéon unique : fermer les autres modules à l'ouverture)
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
        // Fermer automatiquement tous les autres accordéons ouverts
        $('.sidebar-accordion-toggle').not($toggle).each(function() {
          var $otherToggle = $(this);
          var otherTargetId = $otherToggle.attr('data-bs-target');
          var $otherTarget = $(otherTargetId);
          if ($otherTarget.length && $otherToggle.attr('aria-expanded') === 'true') {
            $otherTarget.slideUp(200, function() {
              $otherTarget.removeClass('show');
            });
            $otherToggle.attr('aria-expanded', 'false');
          }
        });

        // Ouvrir la section sélectionnée
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
