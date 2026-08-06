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

         <div class="nav-section">
             <div class="nav-section-title">
                 <i data-lucide="users"></i> <span>Gestion</span>
             </div>
             <a href="<?= RACINE ?>user/list" class="nav-item sub">
                 <i data-lucide="user"></i> <span>Utilisateurs</span>
             </a>
             <a href="<?= RACINE ?>client/list" class="nav-item sub">
                 <i data-lucide="contact"></i> <span>Clients</span>
             </a>
         </div>

         <div class="nav-section">
             <div class="nav-section-title">
                 <i data-lucide="file-text"></i> <span>Catalogue</span>
             </div>
             <a href="<?= RACINE ?>article/list" class="nav-item sub">
                 <i data-lucide="file-text"></i> <span>Articles</span>
             </a>
         </div>

         <div class="nav-section">
             <div class="nav-section-title">
                 <i data-lucide="shopping-cart"></i> <span>Commercial</span>
             </div>
             <a href="<?= RACINE ?>commande/list" class="nav-item sub">
                 <i data-lucide="clipboard-list"></i> <span>Commandes</span>
             </a>
             <a href="<?= RACINE ?>paiement/list" class="nav-item sub">
                 <i data-lucide="wallet"></i> <span>Paiements</span>
             </a>
         </div>
     </nav>
 </aside>
