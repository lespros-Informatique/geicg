<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo"> <?= LOGO ?> </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <!-- Active Academic Year Indicator -->
    <div class="p-3 mx-2 my-2 rounded bg-light border text-center">
        <div class="text-uppercase text-muted" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Année Académique</div>
        <div class="fw-bold text-primary" style="font-size: 13px;">
            <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= RACINE ?>" class="nav-item">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <!-- === MODULE 1 : STRUCTURE & RÉFÉRENTIELS GLOBAUX === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="building"></i> <span>Structure Globale</span>
            </div>
            <a href="<?= RACINE ?>etablissement/list" class="nav-item sub">
                <i data-lucide="landmark"></i> <span>Établissement & Siège</span>
            </a>
            <a href="<?= RACINE ?>cycle/list" class="nav-item sub">
                <i data-lucide="layers"></i> <span>Cycles d'Études</span>
            </a>
            <a href="<?= RACINE ?>filiere/list" class="nav-item sub">
                <i data-lucide="git-branch"></i> <span>Filières & Spécialités</span>
            </a>
            <a href="<?= RACINE ?>niveau/list" class="nav-item sub">
                <i data-lucide="trending-up"></i> <span>Niveaux d'Études</span>
            </a>
            <a href="<?= RACINE ?>salle/list" class="nav-item sub">
                <i data-lucide="door-open"></i> <span>Salles de Cours</span>
            </a>
            <a href="<?= RACINE ?>service/list" class="nav-item sub">
                <i data-lucide="briefcase"></i> <span>Services RH</span>
            </a>
            <a href="<?= RACINE ?>fonction/list" class="nav-item sub">
                <i data-lucide="user-check"></i> <span>Fonctions & Postes</span>
            </a>
        </div>

        <!-- === MODULE 2 : PARAMÉTRAGE ACADÉMIQUE ANNUEL === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="calendar"></i> <span>Cadre Académique</span>
            </div>
            <a href="<?= RACINE ?>annee/list" class="nav-item sub">
                <i data-lucide="calendar-range"></i> <span>Années Académiques</span>
            </a>
            <a href="<?= RACINE ?>classe/list" class="nav-item sub">
                <i data-lucide="graduation-cap"></i> <span>Classes & Promotion</span>
            </a>
            <a href="<?= RACINE ?>semestre/list" class="nav-item sub">
                <i data-lucide="clock"></i> <span>Semestres & Périodes</span>
            </a>
            <a href="<?= RACINE ?>ue/list" class="nav-item sub">
                <i data-lucide="box"></i> <span>Unités d'Enseignement (UE)</span>
            </a>
            <a href="<?= RACINE ?>matiere/list" class="nav-item sub">
                <i data-lucide="book-open"></i> <span>Matières, Coefficients & ECTS</span>
            </a>
            <a href="<?= RACINE ?>scolarite/list" class="nav-item sub">
                <i data-lucide="receipt"></i> <span>Grille des Scolarités</span>
            </a>
            <a href="<?= RACINE ?>tranche/list" class="nav-item sub">
                <i data-lucide="list-checks"></i> <span>Échéanciers de Paiement</span>
            </a>
        </div>

        <!-- === MODULE 3 : SCOLAIRITÉ & ADMISSIONS === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="users"></i> <span>Scolarité & Élèves</span>
            </div>
            <a href="<?= RACINE ?>etudiant/list" class="nav-item sub">
                <i data-lucide="user"></i> <span>Registre des Étudiants</span>
            </a>
            <a href="<?= RACINE ?>parent/list" class="nav-item sub">
                <i data-lucide="contact"></i> <span>Parents & Tuteurs</span>
            </a>
            <a href="<?= RACINE ?>inscription/list" class="nav-item sub">
                <i data-lucide="user-plus"></i> <span>Inscriptions Annuelles</span>
            </a>
            <a href="<?= RACINE ?>accessoire/list" class="nav-item sub">
                <i data-lucide="package"></i> <span>Accessoires & Kits</span>
            </a>
        </div>

        <!-- === MODULE 4 : FINANCE, CAISSE & DÉPENSES === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="wallet"></i> <span>Finance & Caisse</span>
            </div>
            <a href="<?= RACINE ?>paiement/list" class="nav-item sub">
                <i data-lucide="credit-card"></i> <span>Caisse & Encaissements</span>
            </a>
            <a href="<?= RACINE ?>cloture_caisse/list" class="nav-item sub">
                <i data-lucide="lock"></i> <span>Clôture Caisse Journalière</span>
            </a>
            <a href="<?= RACINE ?>impayes/list" class="nav-item sub">
                <i data-lucide="alert-triangle"></i> <span>Relances & Impayés</span>
            </a>
            <a href="<?= RACINE ?>type_depense/list" class="nav-item sub">
                <i data-lucide="tags"></i> <span>Types de Dépenses</span>
            </a>
            <a href="<?= RACINE ?>depense/list" class="nav-item sub">
                <i data-lucide="file-minus"></i> <span>Dépenses & Engagements</span>
            </a>
        </div>

        <!-- === MODULE 5 : PÉDAGOGIE & ÉVALUATIONS === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="award"></i> <span>Pédagogie & Évaluations</span>
            </div>
            <a href="<?= RACINE ?>enseignant/list" class="nav-item sub">
                <i data-lucide="user-check"></i> <span>Corps Enseignant</span>
            </a>
            <a href="<?= RACINE ?>enseignant_matiere/list" class="nav-item sub">
                <i data-lucide="link"></i> <span>Affectations Cours</span>
            </a>
            <a href="<?= RACINE ?>emploi/list" class="nav-item sub">
                <i data-lucide="calendar"></i> <span>Emplois du Temps</span>
            </a>
            <a href="<?= RACINE ?>absence/list" class="nav-item sub">
                <i data-lucide="user-x"></i> <span>Gestion des Absences</span>
            </a>
            <a href="<?= RACINE ?>note/list" class="nav-item sub">
                <i data-lucide="edit-3"></i> <span>Saisie des Notes</span>
            </a>
            <a href="<?= RACINE ?>bulletin/list" class="nav-item sub">
                <i data-lucide="file-text"></i> <span>Bulletins & PV de Notes</span>
            </a>
        </div>

        <!-- === MODULE 6 : COMMUNICATION & MÉDIAS === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="globe"></i> <span>Portail & Médias</span>
            </div>
            <a href="<?= RACINE ?>evenement/list" class="nav-item sub">
                <i data-lucide="bell"></i> <span>Actualités & Événements</span>
            </a>
            <a href="<?= RACINE ?>galerie/list" class="nav-item sub">
                <i data-lucide="image"></i> <span>Galeries Photos/Vidéos</span>
            </a>
            <a href="<?= RACINE ?>document/list" class="nav-item sub">
                <i data-lucide="folder-down"></i> <span>Documents & Supports</span>
            </a>
        </div>

        <!-- === MODULE 7 : COMPTES & SÉCURITÉ (RBAC) === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="shield-check"></i> <span>Sécurité & Accès</span>
            </div>
            <a href="<?= RACINE ?>user/list" class="nav-item sub">
                <i data-lucide="users"></i> <span>Utilisateurs Système</span>
            </a>
            <a href="<?= RACINE ?>role/list" class="nav-item sub">
                <i data-lucide="shield"></i> <span>Rôles & Groupes</span>
            </a>
            <a href="<?= RACINE ?>permission/list" class="nav-item sub">
                <i data-lucide="key"></i> <span>Permissions Granulaires</span>
            </a>
        </div>
    </nav>
</aside>
