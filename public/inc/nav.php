 <header class="topbar">
                <div class="topbar-left">
                    <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn" style="display:none;">
                        <i data-lucide="menu"></i>
                    </button>
                    <div class="search-wrapper search-wrapper--desktop">
                        <div class="search-box">
                            <i data-lucide="search"></i>
                            <input type="text" id="globalSearch" placeholder="Rechercher commandes, clients, produits..." autocomplete="off">
                            <button class="search-clear" id="searchClear" type="button">
                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                            </button>
                            <span class="search-shortcut" id="searchShortcut">Ctrl K</span>
                        </div>
                        <div class="search-results" id="searchResults"></div>
                    </div>
                    <button class="btn-icon search-toggle" id="searchToggle" title="Rechercher">
                        <i data-lucide="search"></i>
                    </button>
                    <div class="search-wrapper search-wrapper--mobile" id="searchMobile">
                        <div class="search-box">
                            <i data-lucide="search"></i>
                            <input type="text" id="globalSearchMobile" placeholder="Rechercher..." autocomplete="off">
                            <button class="search-clear" id="searchClearMobile" type="button">
                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                            </button>
                        </div>
                        <div class="search-results" id="searchResultsMobile"></div>
                    </div>
                </div>
<div class="topbar-actions">
                    <div class="quick-actions">
                        <button class="btn-icon" id="quickActionsBtn" title="Actions rapides">
                            <i data-lucide="zap"></i>
                        </button>
                        <div class="dropdown-panel" id="quickActionsPanel">
                            <div class="dropdown-header">
                                <h3>Actions rapides</h3>
                            </div>
                            <div class="dropdown-grid">
                                <a href="<?= RACINE ?>commande/list" class="dropdown-card">
                                    <i data-lucide="shopping-cart"></i>
                                    <span>Nouvelle commande</span>
                                </a>
                                <a href="<?= RACINE ?>client/list" class="dropdown-card">
                                    <i data-lucide="users"></i>
                                    <span>Nouveau client</span>
                                </a>
                                <a href="<?= RACINE ?>paiement/list" class="dropdown-card">
                                    <i data-lucide="credit-card"></i>
                                    <span>Paiements</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <button class="btn-icon" id="themeBtn" title="Thèmes">
                        <i data-lucide="palette"></i>
                    </button>
                     <div class="dropdown-panel" id="themePanel" style="min-width:320px; padding: 16px;">
                         <div class="theme-panel-header">
                             <h3><i data-lucide="sun" style="width:16px;height:16px;"></i> Apparence</h3>
                             <button class="theme-panel-close" id="themePanelClose"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
                         </div>
                         <div class="theme-section">
                             <div class="theme-section-label">Barre supérieure</div>
                             <div class="theme-options" id="topbarOptions">
                                 <div class="theme-option active" data-value="light">
                                     <div class="theme-swatch" style="background: #FFFFFF; border-color: #E5E7EB;"></div>
                                     <span class="theme-option-label">Claire</span>
                                 </div>
                                 <div class="theme-option" data-value="dark">
                                     <div class="theme-swatch" style="background: #1F2937;"></div>
                                     <span class="theme-option-label">Sombre</span>
                                 </div>
                             </div>
                         </div>
                         <div class="theme-section">
                             <div class="theme-section-label">Barre latérale</div>
                             <div class="theme-options" id="sidebarOptions">
                                 <div class="theme-option active" data-value="light">
                                     <div class="theme-swatch" style="background: #FFFFFF; border-color: #E5E7EB;"></div>
                                     <span class="theme-option-label">Claire</span>
                                 </div>
                                 <div class="theme-option" data-value="dark">
                                     <div class="theme-swatch" style="background: #0F172A;"></div>
                                     <span class="theme-option-label">Sombre</span>
                                 </div>
                             </div>
                         </div>
                         <div class="theme-section">
                             <div class="theme-section-label">Contenu principal</div>
                             <div class="theme-options" id="contentOptions">
                                 <div class="theme-option active" data-value="light">
                                     <div class="theme-swatch" style="background: #F8F9FA; border-color: #E5E7EB;"></div>
                                     <span class="theme-option-label">Clair</span>
                                 </div>
                                 <div class="theme-option" data-value="dark">
                                     <div class="theme-swatch" style="background: #111827;"></div>
                                     <span class="theme-option-label">Sombre</span>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="notification-wrapper">
                        <button class="btn-icon" id="notificationBtn">
                            <i data-lucide="bell"></i>
                            <span class="badge">3</span>
                        </button>
                        <div class="dropdown-panel" id="notificationPanel">
                            <div class="dropdown-header">
                                <h3>Notifications</h3>
                                <span class="mark-all">Tout lu</span>
                            </div>
                            <div class="notification-list">
                                <div class="notification-card">
                                    <i data-lucide="shopping-cart" class="icon-primary"></i>
                                    <div class="notification-content">
                                        <strong>CMD-001</strong> nouvelle commande en attente
                                        <small>Il y a 5 min</small>
                                    </div>
                                </div>
                                <div class="notification-card">
                                    <i data-lucide="alert-triangle" class="icon-warning"></i>
                                    <div class="notification-content">
                                        Stock faible : <strong>Sneakers Air</strong>
                                        <small>Il y a 1h</small>
                                    </div>
                                </div>
                                <div class="notification-card">
                                    <i data-lucide="truck" class="icon-info"></i>
                                    <div class="notification-content">
                                        <strong>LIV-002</strong> en cours de livraison
                                        <small>Hier</small>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-footer">
                                <a href="#">Voir toutes les notifications</a>
                            </div>
                        </div>
                    </div>
                    <div class="admin-profile" id="profileBtn">
                        <img src="https://ui-avatars.com/api/?name=Admin" alt="Admin">
                        <span>Admin Lavex <i data-lucide="chevron-down"></i></span>
                        <div class="dropdown-panel" id="profilePanel">
                            <div class="profile-header">
                                <img src="https://ui-avatars.com/api/?name=Admin" alt="Admin">
                                <div>
                                    <strong>Admin Lavex</strong>
                                     <small>admin@lavex.com</small>
                                </div>
                            </div>
                             <a href="<?= RACINE ?>user/profil" class="dropdown-item"><i data-lucide="user"></i> Mon profil</a>
                             <hr>
                            <a href="<?= RACINE ?>user/decon" class="dropdown-item logout"><i data-lucide="log-out"></i> Déconnexion</a>
                        </div>
                    </div>
                </div>
            </header>