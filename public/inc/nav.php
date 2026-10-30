<?php
  extract(ViewContext::navData());
?>
<?php if (Session::has('flash_success')): ?>
  <script>
    (function() {
      function fireFlashSuccess() {
        var msg = <?= json_encode(Session::get('flash_success')) ?>;
        if (typeof showToast === 'function') {
          showToast(msg, 'success');
        } else if (typeof toastr !== 'undefined') {
          toastr.success(msg);
        }
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fireFlashSuccess);
      } else {
        fireFlashSuccess();
      }
    })();
  </script>
  <?php Session::remove('flash_success'); ?>
<?php endif; ?>

<?php if (Session::has('flash_error')): ?>
  <script>
    (function() {
      function fireFlashError() {
        var msg = <?= json_encode(Session::get('flash_error')) ?>;
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (typeof toastr !== 'undefined') {
          toastr.error(msg);
        }
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fireFlashError);
      } else {
        fireFlashError();
      }
    })();
  </script>
  <?php Session::remove('flash_error'); ?>
<?php endif; ?>

<header class="topbar">
    <div class="topbar-left">
        <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn" title="Menu mobile">
            <i data-lucide="menu"></i>
        </button>
        <div class="search-wrapper search-wrapper--desktop">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="globalSearch" placeholder="Rechercher étudiants, inscriptions, matières, cours..." autocomplete="off">
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
        <!-- Sélecteur d'Année Académique Active (Indépendant par Session) -->
        <div class="annee-switcher" style="position: relative;">
            <button class="btn-annee-switcher" id="anneeSwitcherBtn" type="button" title="Changer l'année académique active" style="display: inline-flex; align-items: center; gap: 8px; background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1E3A5F; font-weight: 800; font-size: 13px; padding: 7px 14px; border-radius: 20px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <i data-lucide="graduation-cap" style="width: 16px; height: 16px; color: #1E3A5F;"></i>
                <span id="activeAnneeDisplay" style="white-space: nowrap;"><?= htmlspecialchars($activeAnneeLibelle) ?></span>
                <i data-lucide="chevron-down" style="width: 13px; height: 13px; color: #64748B;"></i>
            </button>
            <div class="dropdown-panel" id="anneeSwitcherPanel" style="width: 290px; padding: 12px; border-radius: 14px; box-shadow: 0 15px 35px -5px rgba(0,0,0,0.2); border: 1px solid #E2E8F0; z-index: 1050;">
                <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 6px 8px 6px; border-bottom: 1.5px solid #F1F5F9; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i data-lucide="calendar" style="width: 13px; height: 13px;"></i> Année Académique
                    </span>
                    <span class="badge" style="background:#DCFCE7; color:#15803D; font-size:10px; font-weight:700; padding:2px 7px; border-radius:6px;">Sélecteur</span>
                </div>
                <div style="max-height: 270px; overflow-y: auto; padding-right: 2px;">
                    <?php if (!empty($allAnneesNav)): ?>
                        <?php foreach ($allAnneesNav as $anItem): ?>
                            <?php if (!empty($anItem['_canAccessThisYear'])): ?>
                                <button type="button" class="btn-select-annee-item" data-code="<?= htmlspecialchars($anItem['code_annee']) ?>" data-libelle="<?= htmlspecialchars($anItem['libelle_annee']) ?>" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 9px 12px; border-radius: 8px; border: <?= !empty($anItem['_isSelected']) ? '1.5px solid #3B82F6' : '1px solid transparent' ?>; background: <?= !empty($anItem['_isSelected']) ? '#EFF6FF' : 'transparent' ?>; text-align: left; cursor: pointer; margin-bottom: 4px; transition: all 0.15s ease;">
                                    <div style="display: flex; align-items: center; gap: 9px;">
                                        <div style="width: 26px; height: 26px; border-radius: 6px; background: <?= !empty($anItem['_isSelected']) ? '#DBEAFE' : '#F1F5F9' ?>; color: <?= !empty($anItem['_isSelected']) ? '#1E3A5F' : '#64748B' ?>; display: flex; align-items: center; justify-content: center;">
                                            <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: <?= !empty($anItem['_isSelected']) ? '800' : '600' ?>; color: <?= !empty($anItem['_isSelected']) ? '#1E3A5F' : '#0F172A' ?>; font-size: 13px; line-height: 1.2;">
                                                <?= htmlspecialchars($anItem['libelle_annee']) ?>
                                            </div>
                                            <?php if (!empty($anItem['_isCurrentActiveDb'])): ?>
                                                <div style="font-size: 10px; color: #16A34A; font-weight: 700;">Active en cours</div>
                                            <?php elseif (($anItem['statut_annee'] ?? '') === 'planifie'): ?>
                                                <div style="font-size: 10px; color: #2563EB; font-weight: 700;">En préparation (Future)</div>
                                            <?php else: ?>
                                                <div style="font-size: 10px; color: #64748B; font-weight: 600;">Année clôturée (Historique)</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($anItem['_isSelected'])): ?>
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #1E3A5F; color: #FFF; font-size: 11px; font-weight: 800;">✓</span>
                                    <?php endif; ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 12px; font-size: 12px; color: #64748B; text-align: center;">Aucune année disponible</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="quick-actions" style="position: relative;">
            <button class="btn-icon" id="quickActionsBtn" title="Actions rapides">
                <i data-lucide="zap"></i>
            </button>
            <div class="dropdown-panel" id="quickActionsPanel">
                <div class="dropdown-header">
                    <h3>Raccourcis GEICG</h3>
                </div>
                <div class="dropdown-grid">
                    <a href="<?= RACINE ?>inscription/add" class="dropdown-card">
                        <i data-lucide="user-plus"></i>
                        <span>Nouvelle Inscription</span>
                    </a>
                    <a href="<?= RACINE ?>paiement/add" class="dropdown-card">
                        <i data-lucide="credit-card"></i>
                        <span>Nouveau Paiement</span>
                    </a>
                    <a href="<?= RACINE ?>note/add" class="dropdown-card">
                        <i data-lucide="edit-3"></i>
                        <span>Saisir Notes</span>
                    </a>
                    <a href="<?= RACINE ?>etudiant/list" class="dropdown-card">
                        <i data-lucide="users"></i>
                        <span>Étudiants</span>
                    </a>
                    <a href="<?= RACINE ?>emploi/list" class="dropdown-card">
                        <i data-lucide="calendar"></i>
                        <span>Emplois du temps</span>
                    </a>
                    <a href="<?= RACINE ?>bulletin/list" class="dropdown-card">
                        <i data-lucide="file-text"></i>
                        <span>Bulletins</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="theme-wrapper" style="position: relative;">
            <button class="btn-icon" id="themeBtn" title="Personnaliser les couleurs & thèmes">
                <i data-lucide="palette"></i>
            </button>
            <div class="dropdown-panel theme-expanded-panel" id="themePanel" style="width: 520px; max-width: 95vw; max-height: 84vh; overflow-y: auto; padding: 24px; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <!-- En-tête -->
                <div class="theme-panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid var(--border-color, #E2E8F0);">
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--text-primary, #1E293B); display: flex; align-items: center; gap: 10px;">
                            <i data-lucide="palette" style="width:20px; height:20px; color: var(--primary-color);"></i> Personnalisation & Apparence
                        </h3>
                        <p style="margin: 3px 0 0 30px; font-size: 12px; color: var(--text-secondary, #64748B);">Adaptez les couleurs, polices et affichages à votre confort</p>
                    </div>
                    <button class="theme-panel-close" id="themePanelClose" style="background: var(--bg-secondary, #F1F5F9); border: 1px solid var(--border-color, #CBD5E1); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748B; transition: all 0.2s ease;">
                        <i data-lucide="x" style="width:18px;height:18px;"></i>
                    </button>
                </div>

                <!-- GRILLE 2 COLONNES POUR LES COULEURS DE STRUCTURE -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <!-- Thème Principal / Accents École -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="award" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Couleur Principale
                        </div>
                        <div class="theme-options" id="primaryOptions" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                            <div class="theme-option active" data-category="primary" data-value="navy" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #18385F; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Marine</span>
                            </div>
                            <div class="theme-option" data-category="primary" data-value="bordeaux" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #5C0808; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Bordeaux</span>
                            </div>
                            <div class="theme-option" data-category="primary" data-value="royal" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #2563EB; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Royal</span>
                            </div>
                            <div class="theme-option" data-category="primary" data-value="emerald" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #047857; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Émeraude</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Sombre / Clair Global -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="sun-moon" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Mode D'Affichage
                        </div>
                        <div class="theme-options" id="contentOptions" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                            <div class="theme-option active" data-category="content" data-value="light" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <i data-lucide="sun" style="width: 20px; height: 20px; color: #F59E0B;"></i>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Mode Clair</span>
                            </div>
                            <div class="theme-option" data-category="content" data-value="dark" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <i data-lucide="moon" style="width: 20px; height: 20px; color: #6366F1;"></i>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Dark Mode</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION TYPOGRAPHIE & TAILLE -->
                <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="type" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Police & Typographie
                    </div>
                    <div class="theme-options" id="fontOptions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 8px; margin-bottom: 12px;">
                        <div class="theme-option active" data-category="font" data-value="inter" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF; font-family: 'Inter', sans-serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Inter</span>
                        </div>
                        <div class="theme-option" data-category="font" data-value="poppins" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF; font-family: 'Poppins', sans-serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Poppins</span>
                        </div>
                        <div class="theme-option" data-category="font" data-value="roboto" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF; font-family: 'Roboto', sans-serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Roboto</span>
                        </div>
                        <div class="theme-option" data-category="font" data-value="merriweather" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF; font-family: 'Merriweather', serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Serif</span>
                        </div>
                    </div>

                    <!-- Taille & Densité -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-top: 10px; border-top: 1px dashed var(--border-color, #E2E8F0);">
                        <span style="font-size: 12px; font-weight: 700; color: #475569;">Densité d'affichage :</span>
                        <div class="theme-options" id="fontSizeOptions" style="display: flex; gap: 8px;">
                            <div class="theme-option" data-category="fontsize" data-value="small" style="padding: 6px 12px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Compact (13px)</span>
                            </div>
                            <div class="theme-option active" data-category="fontsize" data-value="normal" style="padding: 6px 12px; border-radius: 8px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Normal (14px)</span>
                            </div>
                            <div class="theme-option" data-category="fontsize" data-value="large" style="padding: 6px 12px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Confort (16px)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION BARRES DE NAVIGATION (TOPBAR & SIDEBAR) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <!-- Barre Supérieure (Topbar) -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="layout" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Barre Supérieure
                        </div>
                        <div class="theme-options" id="topbarOptions" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <div class="theme-option active" data-category="topbar" data-value="light" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #CBD5E1;"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Claire</span>
                            </div>
                            <div class="theme-option" data-category="topbar" data-value="dark" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #1F2937; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Sombre</span>
                            </div>
                            <div class="theme-option" data-category="topbar" data-value="navy" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #18385F; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Marine</span>
                            </div>
                            <div class="theme-option" data-category="topbar" data-value="bordeaux" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #5C0808; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Bordeaux</span>
                            </div>
                        </div>
                    </div>

                    <!-- Barre Latérale (Sidebar) -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="sidebar" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Menu Latéral
                        </div>
                        <div class="theme-options" id="sidebarOptions" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <div class="theme-option active" data-category="sidebar" data-value="light" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #CBD5E1;"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Claire</span>
                            </div>
                            <div class="theme-option" data-category="sidebar" data-value="dark" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #0F172A; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Sombre</span>
                            </div>
                            <div class="theme-option" data-category="sidebar" data-value="navy" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #18385F; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Marine</span>
                            </div>
                            <div class="theme-option" data-category="sidebar" data-value="bordeaux" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #5C0808; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Bordeaux</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION ARRONDIS DES BORDURES -->
                <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="square" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Style des Arrondis (Cartes & Boutons)
                    </div>
                    <div class="theme-options" id="radiusOptions" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <div class="theme-option" data-category="radius" data-value="sharp" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 12px; border-radius: 4px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                            <div style="width: 16px; height: 16px; border: 2px solid var(--primary-color); border-radius: 2px;"></div>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Carré (4px)</span>
                        </div>
                        <div class="theme-option active" data-category="radius" data-value="normal" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 12px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                            <div style="width: 16px; height: 16px; border: 2px solid var(--primary-color); border-radius: 6px;"></div>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Moderne (10px)</span>
                        </div>
                        <div class="theme-option" data-category="radius" data-value="round" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 12px; border-radius: 14px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                            <div style="width: 16px; height: 16px; border: 2px solid var(--primary-color); border-radius: 12px;"></div>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Arrondi (16px)</span>
                        </div>
                    </div>
                </div>

                <!-- Bouton Réinitialiser -->
                <div style="text-align: center; padding-top: 6px;">
                    <button type="button" id="themeResetBtn" style="background: var(--bg-secondary, #F1F5F9); border: 1.5px dashed var(--border-color, #CBD5E1); border-radius: 10px; padding: 10px 16px; font-size: 12px; font-weight: 700; color: #64748B; cursor: pointer; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease;">
                        <i data-lucide="rotate-ccw" style="width: 15px; height: 15px;"></i> Réinitialiser tous les paramètres par défaut
                    </button>
                </div>
            </div>
        </div>
        <div class="notification-wrapper">
            <button class="btn-icon" id="notificationBtn" title="Notifications">
                <i data-lucide="bell"></i>
                <?php if (isset($unreadNotifsCount) && $unreadNotifsCount > 0): ?>
                    <span class="badge"><?= $unreadNotifsCount ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-panel" id="notificationPanel" style="width: 320px;">
                <div class="dropdown-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Notifications</h3>
                    <span style="font-size: 11px; color: #64748B;"><?= isset($recentAdminNotifs) ? count($recentAdminNotifs) : 0 ?> récente(s)</span>
                </div>
                <div class="notification-list">
                    <?php if (empty($recentAdminNotifs)): ?>
                        <div style="padding: 16px; text-align: center; color: #94A3B8; font-size: 13px;">
                            Aucune notification récente
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentAdminNotifs as $notif): ?>
                            <div class="notification-card" style="<?= empty($notif['lu_notification']) ? 'background: #F8FAFC;' : '' ?>">
                                <i data-lucide="bell" class="icon-primary" style="width: 18px; height: 18px;"></i>
                                <div class="notification-content">
                                    <strong><?= htmlspecialchars($notif['titre_notification'] ?? 'Notification') ?></strong>
                                    <p style="margin: 2px 0 0; font-size: 12px; color: #475569;"><?= htmlspecialchars($notif['message_notification'] ?? '') ?></p>
                                    <small style="color: #94A3B8; font-size: 10px;"><?= !empty($notif['created_at_notification']) ? date('d/m H:i', strtotime($notif['created_at_notification'])) : '' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="admin-profile" id="profileBtn">
            <span class="avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                <?= strtoupper(substr($currentUserName ?? 'A', 0, 1)) ?>
            </span>
            <span><?= htmlspecialchars($currentUserName ?? 'Utilisateur') ?> <i data-lucide="chevron-down"></i></span>
            <div class="dropdown-panel" id="profilePanel">
                <div class="profile-header">
                    <div>
                        <strong><?= htmlspecialchars($currentUserName ?? 'Utilisateur') ?></strong>
                        <small style="color: #64748B; display: block;"><?= htmlspecialchars($currentUserEmail ?? '') ?></small>
                        <?php if (!empty($navRoles)): ?>
                          <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                            <?php foreach($navRoles as $nr): ?>
                              <span style="font-size: 10px; font-weight: 700; background: rgba(24, 56, 95, 0.08); color: var(--primary-color, #18385F); padding: 2px 6px; border-radius: 4px;">
                                <?= htmlspecialchars(str_replace('ROLE_', '', $nr)) ?>
                              </span>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= RACINE ?>user/profil" class="dropdown-item"><i data-lucide="user"></i> Mon profil</a>
                <hr>
                <a href="<?= RACINE ?>user/decon" class="dropdown-item logout"><i data-lucide="log-out"></i> Déconnexion</a>
            </div>
        </div>
    </div>
</header>

<?php if ($isPastAnneeNav): ?>
  <div style="background: <?= $isReadOnlyAnneeNav ? '#FFFBEB' : '#EFF6FF' ?>; color: <?= $isReadOnlyAnneeNav ? '#B45309' : '#1E40AF' ?>; border-bottom: 1.5px solid <?= $isReadOnlyAnneeNav ? '#FCD34D' : '#93C5FD' ?>; padding: 8px 24px; font-size: 12.5px; font-weight: 700; display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; box-sizing: border-box;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <i data-lucide="<?= $isReadOnlyAnneeNav ? 'lock' : 'unlock' ?>" style="width: 16px; height: 16px; flex-shrink: 0; color: <?= $isReadOnlyAnneeNav ? '#D97706' : '#2563EB' ?>;"></i>
      <span>
        Mode Historique (Année <?= htmlspecialchars($activeAnneeLibelle) ?>) — 
        <?= $isReadOnlyAnneeNav ? '<strong>Lecture seule</strong> (Aucune modification autorisée sur cette année)' : '<strong>Édition / Régularisation autorisée</strong>' ?>
      </span>
    </div>
    <span class="badge" style="background: <?= $isReadOnlyAnneeNav ? '#FEF3C7' : '#DBEAFE' ?>; color: <?= $isReadOnlyAnneeNav ? '#92400E' : '#1E3A5F' ?>; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 800;">
      <?= $isReadOnlyAnneeNav ? 'Lecture Seule' : 'Mode Saisie' ?>
    </span>
  </div>
<?php endif; ?>