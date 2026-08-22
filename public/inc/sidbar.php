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
?>
<style>
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
        <button class="sidebar-toggle" id="sidebarToggle">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <!-- Indicator Active Academic Year -->
    <div class="p-3 mx-2 my-2 rounded bg-light border text-center">
        <div class="text-uppercase text-muted" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Année Académique</div>
        <div class="fw-bold text-primary" style="font-size: 13px;">
            <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= RACINE ?>" class="nav-item <?= ($currentUri === RACINE || $currentUri === RACINE . 'public/') ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <!-- === MODULE 1 : STRUCTURE & RÉFÉRENTIELS GLOBAUX === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-structure" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="building" style="width: 16px; height: 16px;"></i> <span>Structure Globale</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-structure">
                <a href="<?= RACINE ?>etablissement/config" class="nav-item sub <?= strpos($currentUri, '/etablissement/') !== false ? 'active' : '' ?>">
                    <i data-lucide="landmark"></i> <span>Configuration Établissement</span>
                </a>
                <a href="<?= RACINE ?>filiere_cycle/list" class="nav-item sub <?= strpos($currentUri, '/filiere_cycle/') !== false || strpos($currentUri, '/filiere/') !== false || strpos($currentUri, '/cycle/') !== false ? 'active' : '' ?>">
                    <i data-lucide="layers"></i> <span>Filières & Cycles</span>
                </a>
                <a href="<?= RACINE ?>niveau/list" class="nav-item sub <?= strpos($currentUri, '/niveau/') !== false || strpos($currentUri, '/filiere_niveau/') !== false ? 'active' : '' ?>">
                    <i data-lucide="trending-up"></i> <span>Niveaux d'Études</span>
                </a>
                <a href="<?= RACINE ?>salle/list" class="nav-item sub <?= strpos($currentUri, '/salle/') !== false ? 'active' : '' ?>">
                    <i data-lucide="door-open"></i> <span>Salles de Cours</span>
                </a>
                <a href="<?= RACINE ?>fonction/list" class="nav-item sub <?= strpos($currentUri, '/fonction/') !== false ? 'active' : '' ?>">
                    <i data-lucide="user-check"></i> <span>Fonctions & Postes</span>
                </a>
            </div>
        </div>

        <!-- === MODULE 2 : CADRE ACADÉMIQUE & SCOLARITÉS === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-academique" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> <span>Cadre Académique</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-academique">
                <a href="<?= RACINE ?>annee/list" class="nav-item sub <?= strpos($currentUri, '/annee/') !== false ? 'active' : '' ?>">
                    <i data-lucide="calendar-range"></i> <span>Années Académiques</span>
                </a>
                <a href="<?= RACINE ?>classe/list" class="nav-item sub <?= strpos($currentUri, '/classe/') !== false ? 'active' : '' ?>">
                    <i data-lucide="graduation-cap"></i> <span>Classes & Promotion</span>
                </a>
                <a href="<?= RACINE ?>semestre/list" class="nav-item sub <?= strpos($currentUri, '/semestre/') !== false ? 'active' : '' ?>">
                    <i data-lucide="clock"></i> <span>Semestres & Périodes</span>
                </a>
                <a href="<?= RACINE ?>matiere/list" class="nav-item sub <?= strpos($currentUri, '/matiere/') !== false ? 'active' : '' ?>">
                    <i data-lucide="book-open"></i> <span>Matières & Coefficients</span>
                </a>
                <a href="<?= RACINE ?>scolarite/list" class="nav-item sub <?= strpos($currentUri, '/scolarite/') !== false || strpos($currentUri, '/tranche/') !== false ? 'active' : '' ?>">
                    <i data-lucide="receipt"></i> <span>Scolarités & Échéanciers</span>
                </a>
            </div>
        </div>

        <!-- === MODULE 3 : SCOLAIRITÉ & ADMISSIONS === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-eleves" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="users" style="width: 16px; height: 16px;"></i> <span>Scolarité & Élèves</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-eleves">
                <a href="<?= RACINE ?>etudiant/list" class="nav-item sub <?= strpos($currentUri, '/etudiant/') !== false ? 'active' : '' ?>">
                    <i data-lucide="user"></i> <span>Registre des Étudiants</span>
                </a>
                <a href="<?= RACINE ?>parent/list" class="nav-item sub <?= strpos($currentUri, '/parent/') !== false ? 'active' : '' ?>">
                    <i data-lucide="contact"></i> <span>Parents & Tuteurs</span>
                </a>
                <a href="<?= RACINE ?>inscription/list" class="nav-item sub <?= strpos($currentUri, '/inscription/') !== false ? 'active' : '' ?>">
                    <i data-lucide="user-plus"></i> <span>Inscriptions Annuelles</span>
                </a>
                <a href="<?= RACINE ?>accessoire/list" class="nav-item sub <?= strpos($currentUri, '/accessoire/') !== false ? 'active' : '' ?>">
                    <i data-lucide="package"></i> <span>Accessoires & Kits</span>
                </a>
            </div>
        </div>

        <!-- === MODULE 4 : FINANCE, CAISSE & DÉPENSES === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-finance" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="wallet" style="width: 16px; height: 16px;"></i> <span>Finance & Caisse</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-finance">
                <a href="<?= RACINE ?>paiement/list" class="nav-item sub <?= strpos($currentUri, '/paiement/') !== false ? 'active' : '' ?>">
                    <i data-lucide="credit-card"></i> <span>Caisse & Encaissements</span>
                </a>
                <a href="<?= RACINE ?>ouverture_caisse/list" class="nav-item sub <?= strpos($currentUri, '/ouverture_caisse/') !== false ? 'active' : '' ?>">
                    <i data-lucide="unlock"></i> <span>Ouverture de Caisse</span>
                </a>
                <a href="<?= RACINE ?>cloture_caisse/list" class="nav-item sub <?= strpos($currentUri, '/cloture_caisse/') !== false ? 'active' : '' ?>">
                    <i data-lucide="lock"></i> <span>Clôture Caisse Journalière</span>
                </a>
                <a href="<?= RACINE ?>impayes/list" class="nav-item sub <?= strpos($currentUri, '/impayes/') !== false ? 'active' : '' ?>">
                    <i data-lucide="alert-triangle"></i> <span>Relances & Impayés</span>
                </a>
                <a href="<?= RACINE ?>type_depense/list" class="nav-item sub <?= strpos($currentUri, '/type_depense/') !== false ? 'active' : '' ?>">
                    <i data-lucide="tags"></i> <span>Types de Dépenses</span>
                </a>
                <a href="<?= RACINE ?>depense/list" class="nav-item sub <?= strpos($currentUri, '/depense/') !== false ? 'active' : '' ?>">
                    <i data-lucide="file-minus"></i> <span>Dépenses & Engagements</span>
                </a>
            </div>
        </div>

        <!-- === MODULE 5 : PÉDAGOGIE & ÉVALUATIONS === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-pedagogie" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="award" style="width: 16px; height: 16px;"></i> <span>Pédagogie & Évaluations</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-pedagogie">
                <a href="<?= RACINE ?>enseignant/list" class="nav-item sub <?= strpos($currentUri, '/enseignant/') !== false && strpos($currentUri, '_matiere') === false ? 'active' : '' ?>">
                    <i data-lucide="user-check"></i> <span>Corps Enseignant</span>
                </a>
                <a href="<?= RACINE ?>enseignant_matiere/list" class="nav-item sub <?= strpos($currentUri, '/enseignant_matiere/') !== false ? 'active' : '' ?>">
                    <i data-lucide="link"></i> <span>Affectations Cours</span>
                </a>
                <a href="<?= RACINE ?>emploi/list" class="nav-item sub <?= strpos($currentUri, '/emploi/') !== false ? 'active' : '' ?>">
                    <i data-lucide="calendar"></i> <span>Emplois du Temps</span>
                </a>
                <a href="<?= RACINE ?>absence/list" class="nav-item sub <?= strpos($currentUri, '/absence/') !== false ? 'active' : '' ?>">
                    <i data-lucide="user-x"></i> <span>Gestion des Absences</span>
                </a>
                <a href="<?= RACINE ?>note/list" class="nav-item sub <?= strpos($currentUri, '/note/') !== false ? 'active' : '' ?>">
                    <i data-lucide="edit-3"></i> <span>Saisie des Notes</span>
                </a>
                <a href="<?= RACINE ?>bulletin/list" class="nav-item sub <?= strpos($currentUri, '/bulletin/') !== false ? 'active' : '' ?>">
                    <i data-lucide="file-text"></i> <span>Bulletins & PV de Notes</span>
                </a>
            </div>
        </div>

        <!-- === MODULE 6 : COMMUNICATION & MÉDIAS === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-medias" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="globe" style="width: 16px; height: 16px;"></i> <span>Portail & Médias</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-medias">
                <a href="<?= RACINE ?>evenement/list" class="nav-item sub <?= strpos($currentUri, '/evenement/') !== false ? 'active' : '' ?>">
                    <i data-lucide="bell"></i> <span>Actualités & Événements</span>
                </a>
                <a href="<?= RACINE ?>galerie/list" class="nav-item sub <?= strpos($currentUri, '/galerie/') !== false ? 'active' : '' ?>">
                    <i data-lucide="image"></i> <span>Galeries Photos/Vidéos</span>
                </a>
                <a href="<?= RACINE ?>document/list" class="nav-item sub <?= strpos($currentUri, '/document/') !== false ? 'active' : '' ?>">
                    <i data-lucide="folder-down"></i> <span>Documents & Supports</span>
                </a>
            </div>
        </div>

        <!-- === MODULE 7 : COMPTES & SÉCURITÉ (RBAC) === -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-securite" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i> <span>Sécurité & Accès</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-securite">
                <a href="<?= RACINE ?>user/list" class="nav-item sub <?= strpos($currentUri, '/user/') !== false ? 'active' : '' ?>">
                    <i data-lucide="users"></i> <span>Utilisateurs Système</span>
                </a>
                <a href="<?= RACINE ?>role/list" class="nav-item sub <?= strpos($currentUri, '/role/') !== false ? 'active' : '' ?>">
                    <i data-lucide="shield"></i> <span>Rôles & Groupes</span>
                </a>
                <a href="<?= RACINE ?>permission/list" class="nav-item sub <?= strpos($currentUri, '/permission/') !== false ? 'active' : '' ?>">
                    <i data-lucide="key"></i> <span>Permissions Granulaires</span>
                </a>
            </div>
        </div>
    </nav>
</aside>

<script>
$(document).ready(function() {
  // Toggle Accordion Click Handler (Expand / Collapse)
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

  // Auto-expand the accordion section containing the active page link
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
    // Default expand structure section if on dashboard
    $('#sec-structure').addClass('show').show();
    $('.sidebar-accordion-toggle[data-bs-target="#sec-structure"]').attr('aria-expanded', 'true');
  }

  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>
