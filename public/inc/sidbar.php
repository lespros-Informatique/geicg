<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo"> <?= LOGO ?> </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i data-lucide="menu"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= RACINE ?>" class="nav-item">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <?php if ($isSuperAdmin): ?>
        <!-- === SUPER ADMIN : HUB PRESSINGS & GESTION GLOBALE === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="store"></i> <span>Partenaires & Finances</span>
            </div>
            <a href="<?= RACINE ?>pressing/list" class="nav-item sub">
                <i data-lucide="map-pin"></i> <span>Pressings (Hub 360°)</span>
            </a>
            <a href="<?= RACINE ?>retrait/list" class="nav-item sub">
                <i data-lucide="wallet"></i> <span>Retraits Pressings</span>
            </a>
            <a href="<?= RACINE ?>paiement/list" class="nav-item sub">
                <i data-lucide="receipt"></i> <span>Paiements Réseau</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="layers"></i> <span>Marketplace & Offres</span>
            </div>
            <a href="<?= RACINE ?>categorie/list" class="nav-item sub">
                <i data-lucide="tag"></i> <span>Catégories d'articles</span>
            </a>
            <a href="<?= RACINE ?>forfait/list" class="nav-item sub">
                <i data-lucide="award"></i> <span>Forfaits B2B</span>
            </a>
            <a href="<?= RACINE ?>abonnement/list" class="nav-item sub">
                <i data-lucide="credit-card"></i> <span>Abonnements Pressings</span>
            </a>
            <a href="<?= RACINE ?>ville/list" class="nav-item sub">
                <i data-lucide="navigation"></i> <span>Villes & Quartiers</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="shield-check"></i> <span>Comptes & Sécurité</span>
            </div>
            <a href="<?= RACINE ?>user/list" class="nav-item sub">
                <i data-lucide="users"></i> <span>Utilisateurs Backoffice</span>
            </a>
            <a href="<?= RACINE ?>role/list" class="nav-item sub">
                <i data-lucide="shield"></i> <span>Rôles & Accès</span>
            </a>
            <a href="<?= RACINE ?>permission/list" class="nav-item sub">
                <i data-lucide="key"></i> <span>Permissions</span>
            </a>
            <a href="<?= RACINE ?>client/list" class="nav-item sub">
                <i data-lucide="contact"></i> <span>Clients Marketplace</span>
            </a>
        </div>

        <?php elseif ($isPressing): ?>
        <!-- === PRESSING PRO : GESTION DE L'ÉTABLISSEMENT === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="shopping-bag"></i> <span>Activité Commerciale</span>
            </div>
            <a href="<?= RACINE ?>commande/list" class="nav-item sub">
                <i data-lucide="clipboard-list"></i> <span>Mes Commandes</span>
            </a>
            <a href="<?= RACINE ?>retrait/list" class="nav-item sub">
                <i data-lucide="wallet"></i> <span>Mon Portefeuille & Retraits</span>
            </a>
            <a href="<?= RACINE ?>client/list" class="nav-item sub">
                <i data-lucide="contact"></i> <span>Mes Clients</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="tag"></i> <span>Mon Catalogue</span>
            </div>
            <a href="<?= RACINE ?>tarif/list" class="nav-item sub">
                <i data-lucide="dollar-sign"></i> <span>Mes Tarifs Articles</span>
            </a>
            <a href="<?= RACINE ?>article/list" class="nav-item sub">
                <i data-lucide="shirt"></i> <span>Mes Articles</span>
            </a>
            <a href="<?= RACINE ?>service/list" class="nav-item sub">
                <i data-lucide="sparkles"></i> <span>Mes Services</span>
            </a>
            <a href="<?= RACINE ?>horaire/list" class="nav-item sub">
                <i data-lucide="clock"></i> <span>Mes Horaires</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="truck"></i> <span>Logistique & Compte</span>
            </div>
            <a href="<?= RACINE ?>livreur/list" class="nav-item sub">
                <i data-lucide="user-check"></i> <span>Mes Livreurs</span>
            </a>
            <a href="<?= RACINE ?>abonnement/list" class="nav-item sub">
                <i data-lucide="credit-card"></i> <span>Mon Abonnement B2B</span>
            </a>
        </div>

        <?php elseif ($isLivreur): ?>
        <!-- === LIVREUR : ESPACE LOGISTIQUE & TERRAIN === -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="truck"></i> <span>Mes Tournées</span>
            </div>
            <a href="<?= RACINE ?>mission/list" class="nav-item sub">
                <i data-lucide="clipboard-list"></i> <span>Missions & Courses</span>
            </a>
            <a href="<?= RACINE ?>mission/carte" class="nav-item sub">
                <i data-lucide="map"></i> <span>Carte des Tournées</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">
                <i data-lucide="bell"></i> <span>Alertes & Compte</span>
            </div>
            <a href="<?= RACINE ?>notification/list" class="nav-item sub">
                <i data-lucide="bell-ring"></i> <span>Notifications</span>
            </a>
            <a href="<?= RACINE ?>user/profil" class="nav-item sub">
                <i data-lucide="user"></i> <span>Mon Profil</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>
</aside>
