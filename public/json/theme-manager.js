/**
 * GEICG - Theme & Color Manager
 * Gestion interactive des thèmes et des couleurs institutionnelles de l'école
 */
(function() {
    const ZONES = ['primary', 'topbar', 'sidebar', 'content'];
    const DEFAULTS = {
        primary: 'navy',
        topbar: 'light',
        sidebar: 'light',
        content: 'light'
    };

    function getSavedPref(zone) {
        try {
            return localStorage.getItem('theme_' + zone) || DEFAULTS[zone];
        } catch (e) {
            return DEFAULTS[zone];
        }
    }

    function setSavedPref(zone, value) {
        try {
            localStorage.setItem('theme_' + zone, value);
        } catch (e) {
            console.warn('[theme-manager] storage error:', e);
        }
    }

    const COLOR_HEX_MAP = {
        navy: '#18385F',
        bordeaux: '#5C0808',
        royal: '#2563EB',
        emerald: '#047857'
    };

    function applyThemeToDOM(zone, value) {
        document.documentElement.setAttribute('data-theme-' + zone, value);
        
        // Si c'est la couleur principale, injecter directement la variable CSS
        if (zone === 'primary' && COLOR_HEX_MAP[value]) {
            const hex = COLOR_HEX_MAP[value];
            document.documentElement.style.setProperty('--primary-color', hex);
            document.documentElement.style.setProperty('--primary', hex);
            document.documentElement.style.setProperty('--btn-primary-bg', hex);
            document.documentElement.style.setProperty('--secondary-color', hex);
            document.documentElement.style.setProperty('--sidebar-active-text', hex);
        }
        
        // Mettre à jour l'état visuel actif dans le panneau
        const options = document.querySelectorAll('.theme-option[data-category="' + zone + '"]');
        options.forEach(function(opt) {
            const optVal = opt.getAttribute('data-value');
            if (optVal === value) {
                opt.classList.add('active');
                opt.style.borderColor = (zone === 'primary' && COLOR_HEX_MAP[value]) ? COLOR_HEX_MAP[value] : '#18385F';
                opt.style.background = 'rgba(24, 56, 95, 0.08)';
            } else {
                opt.classList.remove('active');
                opt.style.borderColor = 'transparent';
                opt.style.background = 'transparent';
            }
        });
    }

    function initThemeManager() {
        // 1. Appliquer les thèmes mémorisés
        ZONES.forEach(function(zone) {
            applyThemeToDOM(zone, getSavedPref(zone));
        });

        const themeBtn = document.getElementById('themeBtn');
        const themePanel = document.getElementById('themePanel');
        const themePanelClose = document.getElementById('themePanelClose');

        if (!themeBtn || !themePanel) return;

        // 2. Gestion de l'ouverture / fermeture du panneau au clic sur le bouton Palette
        themeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = themePanel.classList.contains('active');
            
            // Fermer les autres dropdowns
            document.querySelectorAll('.dropdown-panel').forEach(function(p) {
                if (p !== themePanel) p.classList.remove('active');
            });

            if (isOpen) {
                themePanel.classList.remove('active');
            } else {
                themePanel.classList.add('active');
            }
        });

        // 3. Fermeture via le bouton X dans le panneau
        if (themePanelClose) {
            themePanelClose.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                themePanel.classList.remove('active');
            });
        }

        // 4. Empêcher la fermeture quand on clique à l'intérieur du panneau
        themePanel.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // 5. Clic sur les options de couleur
        document.querySelectorAll('.theme-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const category = this.getAttribute('data-category');
                const value = this.getAttribute('data-value');
                
                if (category && value) {
                    setSavedPref(category, value);
                    applyThemeToDOM(category, value);
                    
                    if (window.lucide) {
                        try { lucide.createIcons(); } catch(err) {}
                    }
                }
            });
        });

        // 6. Fermeture au clic en dehors
        document.addEventListener('click', function(e) {
            if (!themePanel.contains(e.target) && !e.target.closest('#themeBtn')) {
                themePanel.classList.remove('active');
            }
        });

        // 7. Fermeture avec la touche Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && themePanel.classList.contains('active')) {
                themePanel.classList.remove('active');
            }
        });

        if (window.lucide) {
            try { lucide.createIcons(); } catch(err) {}
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeManager);
    } else {
        initThemeManager();
    }
})();
