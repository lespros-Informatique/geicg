<?php
  // 1. Mise en cache de session pour le Logo & le Nom de l'Établissement (évite la requête SQL répétée à chaque clic)
  if (!isset($globalEtablissementLogo)) {
      if (isset($_SESSION['etablissement_logo']) && isset($_SESSION['etablissement_nom'])) {
          $globalEtablissementLogo = $_SESSION['etablissement_logo'];
          $globalEtablissementNom = $_SESSION['etablissement_nom'];
      } else {
          try {
              $db = (new Database())->getCon();
              $stmt = $db->query("SELECT logo_etablissement, libelle_etablissement FROM etablissements WHERE statut_etablissement = 'actif' ORDER BY id_etablissement ASC LIMIT 1");
              $etabRow = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
              $rawLogo = $etabRow['logo_etablissement'] ?? '';
              $globalEtablissementLogo = (!empty($rawLogo)) ? ((strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/')) : '';
              $globalEtablissementNom = $etabRow['libelle_etablissement'] ?? 'GEICG';
              $_SESSION['etablissement_logo'] = $globalEtablissementLogo;
              $_SESSION['etablissement_nom'] = $globalEtablissementNom;
          } catch (Exception $e) {
              $globalEtablissementLogo = '';
              $globalEtablissementNom = 'GEICG';
          }
      }
  }

  $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

  // 2. Gestion des permissions RBAC déléguée à la classe Auth (source de vérité unique)
  //    - Auth::permissions() : cache session + chargement dynamique si absent
  //    - Auth::isSuperAdmin() : reconnaît '*' ET les rôles super-admin (ROLE_SUPERADMIN, all_control...)
  $isSuperAdmin = Auth::isSuperAdmin();
  $permMap = array_fill_keys(Auth::permissions(), true);

  /**
   * Helper d'autorisation granulaire instantané O(1)
   */
  $canAccess = function($requiredPerms = []) use ($permMap, $isSuperAdmin): bool {
      if ($isSuperAdmin) return true;
      foreach ((array)$requiredPerms as $perm) {
          if (isset($permMap[$perm])) return true;
      }
      return false;
  };

  /**
   * Helper d'URL active : simplifie le HTML et évite la cascade de strpos
   */
  $isActiveRoute = function($patterns, $excludePatterns = []) use ($currentUri): string {
      if (!is_array($patterns)) $patterns = [$patterns];
      if (!is_array($excludePatterns)) $excludePatterns = [$excludePatterns];

      foreach ($excludePatterns as $ex) {
          if (!empty($ex) && strpos($currentUri, $ex) !== false) return '';
      }
      foreach ($patterns as $pattern) {
          if (!empty($pattern) && strpos($currentUri, $pattern) !== false) return 'active';
      }
      return '';
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
        <a href="<?= RACINE ?>" class="nav-item <?= in_array($currentUri, [RACINE, RACINE . 'public/', '/geicg/', '/geicg/public/'], true) ? 'active' : '' ?>" data-title="Tableau de bord">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <!-- === MENU 1 : STRUCTURE & ÉTABLISSEMENT === -->
        <?php if ($canAccess('MENU_STRUCTURE')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-structure" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="building" style="width: 16px; height: 16px;"></i> <span>Structure & Établissement</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-structure">
                <?php if ($canAccess(['MANAGE_ETABLISSEMENT', 'CONFIG_SYSTEM'])): ?>
                <a href="<?= RACINE ?>etablissement/config" class="nav-item sub <?= $isActiveRoute('/etablissement/') ?>" data-title="Configuration Établissement">
                    <i data-lucide="landmark"></i> <span>Configuration Établissement</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_FILIERES', 'VIEW_FILIERES', 'MANAGE_CYCLES', 'VIEW_CYCLES', 'CONFIG_ACADEMIQUE', 'MANAGE_NIVEAUX', 'VIEW_NIVEAUX'])): ?>
                <a href="<?= RACINE ?>filiere_cycle/list" class="nav-item sub <?= $isActiveRoute(['/filiere_cycle/', '/filiere/', '/cycle/', '/niveau/']) ?>" data-title="Offre Académique (Cycles, Filières & Niveaux)">
                    <i data-lucide="layers"></i> <span>Cycles, Filières & Niveaux</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_SALLES', 'VIEW_SALLES', 'CONFIG_ACADEMIQUE'])): ?>
                <a href="<?= RACINE ?>salle/list" class="nav-item sub <?= $isActiveRoute('/salle/') ?>" data-title="Salles de Cours">
                    <i data-lucide="door-open"></i> <span>Salles de Cours</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_PIECES', 'VIEW_PIECES', 'CONFIG_ACADEMIQUE', 'MANAGE_INSCRIPTIONS'])): ?>
                <a href="<?= RACINE ?>piece_fournir/list" class="nav-item sub <?= $isActiveRoute('/piece_fournir') ?>" data-title="Pièces à Fournir">
                    <i data-lucide="file-check-2"></i> <span>Pièces à Fournir</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_ACCESSOIRES', 'VIEW_ACCESSOIRES', 'CONFIG_ACADEMIQUE', 'MANAGE_INSCRIPTIONS'])): ?>
                <a href="<?= RACINE ?>accessoire/list" class="nav-item sub <?= $isActiveRoute('/accessoire/list') ?>" data-title="Kits & Accessoires">
                    <i data-lucide="package"></i> <span>Kits & Accessoires</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MENU 2 : PLANIFICATION ACADÉMIQUE === -->
        <?php if ($canAccess('MENU_PLANIFICATION')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-academique" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> <span>Planification Académique</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-academique">
                <?php if ($canAccess(['MANAGE_ANNEES', 'VIEW_ANNEES', 'CONFIG_ACADEMIQUE', 'MANAGE_SEMESTRES', 'VIEW_SEMESTRES'])): ?>
                <a href="<?= RACINE ?>annee/list" class="nav-item sub <?= $isActiveRoute(['/annee', '/semestre']) ?>" data-title="Années & Semestres">
                    <i data-lucide="calendar-range"></i> <span>Années & Semestres</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_CLASSES', 'VIEW_CLASSES', 'CONFIG_ACADEMIQUE'])): ?>
                <a href="<?= RACINE ?>classe/list" class="nav-item sub <?= $isActiveRoute('/classe/') ?>" data-title="Classes">
                    <i data-lucide="graduation-cap"></i> <span>Classes</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_UE', 'VIEW_UE', 'CONFIG_ACADEMIQUE'])): ?>
                <a href="<?= RACINE ?>ue/list" class="nav-item sub <?= $isActiveRoute(['/ue/', '/unites_enseignement/']) ?>" data-title="Unités d'Enseignement (UE)">
                    <i data-lucide="layers"></i> <span>Unités d'Enseignement (UE)</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_MATIERES', 'VIEW_MATIERES', 'CONFIG_ACADEMIQUE'])): ?>
                <a href="<?= RACINE ?>matiere/list" class="nav-item sub <?= $isActiveRoute('/matiere/') ?>" data-title="Matières">
                    <i data-lucide="book-open"></i> <span>Matières</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MENU 3 : ADMISSIONS & SCOLARITÉ === -->
        <?php if ($canAccess('MENU_SCOLARITE')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-eleves" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="users" style="width: 16px; height: 16px;"></i> <span>Admissions & Scolarité</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-eleves">
                <?php if ($canAccess(['MANAGE_ETUDIANTS', 'VIEW_ETUDIANTS'])): ?>
                <a href="<?= RACINE ?>etudiant/list" class="nav-item sub <?= $isActiveRoute('/etudiant/') ?>" data-title="Registre des Étudiants">
                    <i data-lucide="users"></i> <span>Registre des Étudiants</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_INSCRIPTIONS', 'VIEW_INSCRIPTIONS'])): ?>
                <a href="<?= RACINE ?>reinscription/list" class="nav-item sub <?= $isActiveRoute(['/inscription/', '/reinscription/'], '/inscription/sans_photo') ?>" data-title="Réinscriptions">
                    <i data-lucide="clipboard-check"></i> <span>Réinscriptions</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['VIEW_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS_SANS_PHOTO', 'MANAGE_INSCRIPTIONS'])): ?>
                <a href="<?= RACINE ?>inscription/prise_de_vue" class="nav-item sub <?= $isActiveRoute(['/inscription/prise_de_vue', '/inscription/sans_photo']) ?>" data-title="Prise de vue">
                    <i data-lucide="camera"></i> <span>Prise de vue</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_DEPOT_DOSSIERS', 'VIEW_DEPOT_DOSSIERS'])): ?>
                <a href="<?= RACINE ?>dossier_etudiant/list" class="nav-item sub <?= $isActiveRoute('/dossier_etudiant/') ?>" data-title="Dépôt des Dossiers Étudiants">
                    <i data-lucide="folder-open"></i> <span>Dépôt des Dossiers </span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_REMISE_KITS', 'VIEW_REMISE_KITS'])): ?>
                <a href="<?= RACINE ?>accessoire_inscription/registre" class="nav-item sub <?= $isActiveRoute(['/accessoire_inscription/', '/accessoire/registre']) ?>" data-title="Registre & Remise des Kits">
                    <i data-lucide="package-check"></i> <span>Retrait des Kits</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_PARENTS', 'VIEW_PARENTS', 'MANAGE_ETUDIANTS'])): ?>
                <a href="<?= RACINE ?>parent/list" class="nav-item sub <?= $isActiveRoute('/parent/') ?>" data-title="Parents & Tuteurs">
                    <i data-lucide="contact"></i> <span>Parents & Tuteurs</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MENU 4 : FINANCE, CAISSE & DÉPENSES === -->
        <?php if ($canAccess('MENU_FINANCE')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-finance" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="wallet" style="width: 16px; height: 16px;"></i> <span>Finance & Caisse</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-finance">
                <?php if ($canAccess(['MANAGE_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE'])): ?>
                <a href="<?= RACINE ?>scolarite/list" class="nav-item sub <?= $isActiveRoute(['/scolarite/', '/tranche/']) ?>" data-title="Scolarités & Échéanciers">
                    <i data-lucide="receipt"></i> <span>Scolarités & Échéanciers</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_FRAIS_ANNEXES', 'VIEW_FRAIS_ANNEXES', 'MANAGE_FRAIS_SCOLARITE', 'VIEW_FRAIS_SCOLARITE'])): ?>
                <a href="<?= RACINE ?>fraisAnnexe/list" class="nav-item sub <?= $isActiveRoute('/fraisAnnexe/') ?>" data-title="Grille Frais Annexes">
                    <i data-lucide="package-check"></i> <span>Grille Frais Annexes</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['VIEW_CAISSE_ANNEXE', 'MANAGE_CAISSE_ANNEXE', 'VIEW_PAIEMENTS', 'MANAGE_PAIEMENTS', 'VIEW_RAPPORTS_FINANCIERS', 'VIEW_FRAIS_ANNEXES', 'MANAGE_FRAIS_ANNEXES'])): ?>
                <a href="<?= RACINE ?>caisse_annexe/list" class="nav-item sub <?= $isActiveRoute('/caisse_annexe') ?>" data-title="Caisse Annexe (Bilan)">
                    <i data-lucide="wallet-cards"></i> <span>Caisse Annexe</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS', 'RECORD_PAIEMENTS'])): ?>
                <a href="<?= RACINE ?>paiement/list" class="nav-item sub <?= $isActiveRoute('/paiement/', '/comptabilite_etudiants') ?>" data-title="Caisse & Encaissements">
                    <i data-lucide="credit-card"></i> <span>Caisse & Encaissements</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['VIEW_COMPTABILITE_ETUDIANTS', 'MANAGE_PAIEMENTS', 'VIEW_PAIEMENTS', 'VIEW_RAPPORTS_FINANCIERS', 'EXPORT_FINANCE'])): ?>
                <a href="<?= RACINE ?>paiement/comptabilite_etudiants" class="nav-item sub <?= $isActiveRoute('/comptabilite_etudiants') ?>" data-title="Comptabilité Étudiants">
                    <i data-lucide="users"></i> <span>Comptabilité Étudiants</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_CAISSE', 'OUVERTURE_CAISSE', 'CLOTURE_CAISSE', 'VIEW_RAPPORTS_FINANCIERS', 'MANAGE_PAIEMENTS', 'RECORD_PAIEMENTS'])): ?>
                <a href="<?= RACINE ?>session_caisse/list" class="nav-item sub <?= $isActiveRoute(['/session_caisse/', '/ouverture_caisse/', '/cloture_caisse/']) ?>" data-title="Sessions de Caisse">
                    <i data-lucide="vault"></i> <span>Sessions de Caisse</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_IMPAYES', 'VIEW_IMPAYES', 'SEND_RELANCES', 'MANAGE_MORATOIRES'])): ?>
                <a href="<?= RACINE ?>impayes/list" class="nav-item sub <?= $isActiveRoute('/impayes/') ?>" data-title="Relances & Impayés">
                    <i data-lucide="alert-triangle"></i> <span>Relances & Impayés</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_ARRIERES', 'VIEW_ARRIERES', 'MANAGE_IMPAYES', 'MANAGE_PAIEMENTS'])): ?>
                <a href="<?= RACINE ?>arriere/list" class="nav-item sub <?= $isActiveRoute('/arriere/') ?>" data-title="Arriérés Années Antérieures">
                    <i data-lucide="history"></i> <span>Arriérés Années Antérieures</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['RECORD_DEPENSES', 'VIEW_DEPENSES', 'VALIDATE_DEPENSES', 'MANAGE_TYPES_DEPENSE', 'VIEW_TYPES_DEPENSE'])): ?>
                <a href="<?= RACINE ?>depense/list" class="nav-item sub <?= $isActiveRoute(['/depense', '/type_depense']) ?>" data-title="Dépenses & Engagements">
                    <i data-lucide="file-minus"></i> <span>Dépenses & Engagements</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MENU 5 : PÉDAGOGIE & ÉVALUATIONS === -->
        <?php if ($canAccess('MENU_PEDAGOGIE')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-pedagogie" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="award" style="width: 16px; height: 16px;"></i> <span>Pédagogie & Évaluations</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-pedagogie">
                <?php if ($canAccess(['MANAGE_ENSEIGNANTS', 'VIEW_ENSEIGNANTS'])): ?>
                <a href="<?= RACINE ?>enseignant/list" class="nav-item sub <?= $isActiveRoute('/enseignant/', '_matiere') ?>" data-title="Corps Enseignant">
                    <i data-lucide="user-check"></i> <span>Corps Enseignant</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_EMPLOI_TEMPS', 'VIEW_EMPLOI_TEMPS'])): ?>
                <a href="<?= RACINE ?>emploi/list" class="nav-item sub <?= $isActiveRoute('/emploi/') ?>" data-title="Emplois du Temps">
                    <i data-lucide="calendar"></i> <span>Emplois du Temps</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_ABSENCES', 'VIEW_ABSENCES'])): ?>
                <a href="<?= RACINE ?>absence/list" class="nav-item sub <?= $isActiveRoute('/absence/') ?>" data-title="Absences par Classe">
                    <i data-lucide="user-x"></i> <span>Absences & Appel par Classe</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['ENTER_NOTES', 'VIEW_NOTES', 'LOCK_NOTES', 'VIEW_EXAMENS', 'MANAGE_EXAMENS'])): ?>
                <a href="<?= RACINE ?>composition/list" class="nav-item sub <?= $isActiveRoute('/composition/') ?>" data-title="Planning Compositions & Examens">
                    <i data-lucide="award"></i> <span>Planning Compositions & Examens</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['ENTER_NOTES', 'VIEW_NOTES', 'LOCK_NOTES'])): ?>
                <a href="<?= RACINE ?>note/saisieClasse" class="nav-item sub <?= $isActiveRoute('/note/saisieClasse') ?>" data-title="Saisie des Notes par Classe">
                    <i data-lucide="edit-3"></i> <span>Saisie des Notes par Classe</span>
                </a>
                <a href="<?= RACINE ?>note/list" class="nav-item sub <?= $isActiveRoute('/note/', 'saisieClasse') ?>" data-title="Registre & Journal des Notes">
                    <i data-lucide="list"></i> <span>Registre & Journal des Notes</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['GENERATE_BULLETINS', 'VIEW_BULLETINS', 'VIEW_REPORTS'])): ?>
                <a href="<?= RACINE ?>bulletin/list" class="nav-item sub <?= $isActiveRoute(['/bulletin/list', RACINE . 'bulletin/'], 'pvClasse') ?>" data-title="Bulletins Semestriels">
                    <i data-lucide="file-text"></i> <span>Bulletins Semestriels</span>
                </a>
                <a href="<?= RACINE ?>bulletin/pvClasse" class="nav-item sub <?= $isActiveRoute('/bulletin/pvClasse') ?>" data-title="PV de Délibération">
                    <i data-lucide="file-spreadsheet"></i> <span>PV de Délibération</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MENU 6 : PORTAIL & COMMUNICATION === -->
        <?php if ($canAccess('MENU_COMMUNICATION')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-medias" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="globe" style="width: 16px; height: 16px;"></i> <span>Portail & Communication</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-medias">
                <?php if ($canAccess(['MANAGE_EVENEMENTS', 'VIEW_EVENEMENTS'])): ?>
                <a href="<?= RACINE ?>evenement/list" class="nav-item sub <?= $isActiveRoute('/evenement/') ?>" data-title="Actualités & Événements">
                    <i data-lucide="bell"></i> <span>Actualités & Événements</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_GALLERY', 'VIEW_GALLERY', 'MANAGE_COMMUNICATION'])): ?>
                <a href="<?= RACINE ?>galerie/list" class="nav-item sub <?= $isActiveRoute('/galerie/') ?>" data-title="Galeries Photos/Vidéos">
                    <i data-lucide="image"></i> <span>Galeries Photos/Vidéos</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_DOCUMENTS', 'VIEW_DOCUMENTS'])): ?>
                <a href="<?= RACINE ?>document/list" class="nav-item sub <?= $isActiveRoute('/document/') ?>" data-title="Documents & Supports">
                    <i data-lucide="folder-down"></i> <span>Documents & Supports</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MENU 7 : ADMINISTRATION & SÉCURITÉ === -->
        <?php if ($canAccess('MENU_ADMINISTRATION')): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-securite" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i> <span>Administration & Sécurité</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-securite">
                <?php if ($canAccess(['MANAGE_USERS', 'VIEW_USERS'])): ?>
                <a href="<?= RACINE ?>user/list" class="nav-item sub <?= $isActiveRoute('/user/') ?>" data-title="Utilisateurs Système">
                    <i data-lucide="users"></i> <span>Utilisateurs Système</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_FONCTIONS', 'VIEW_FONCTIONS'])): ?>
                <a href="<?= RACINE ?>fonction/list" class="nav-item sub <?= $isActiveRoute('/fonction/') ?>" data-title="Fonctions & Postes">
                    <i data-lucide="user-check"></i> <span>Fonctions & Postes</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_ROLES', 'VIEW_ROLES'])): ?>
                <a href="<?= RACINE ?>role/list" class="nav-item sub <?= $isActiveRoute('/role/') ?>" data-title="Rôles & Groupes">
                    <i data-lucide="shield"></i> <span>Rôles & Groupes</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccess(['MANAGE_PERMISSIONS', 'VIEW_PERMISSIONS'])): ?>
                <a href="<?= RACINE ?>permission/list" class="nav-item sub <?= $isActiveRoute('/permission/') ?>" data-title="Permissions Granulaires">
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
