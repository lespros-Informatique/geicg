(function() {
    const STORAGE_KEY = 'woli-theme-prefs';
    const DEFAULT_PREFS = { topbar: 'light', sidebar: 'light', content: 'light' };

    function loadPrefs() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') {
                    return { ...DEFAULT_PREFS, ...parsed };
                }
            }
        } catch (e) {
            console.warn('[theme-manager] failed to load prefs:', e);
        }
        return { ...DEFAULT_PREFS };
    }

    function savePrefs(prefs) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
        } catch (e) {
            console.warn('[theme-manager] failed to save prefs:', e);
        }
    }

    function applyTheme(prefs) {
        document.documentElement.setAttribute('data-theme-topbar', prefs.topbar);
        document.documentElement.setAttribute('data-theme-sidebar', prefs.sidebar);
        document.documentElement.setAttribute('data-theme-content', prefs.content);

        document.querySelectorAll('#topbarOptions .theme-option').forEach(function(el) {
            el.classList.toggle('active', el.dataset.value === prefs.topbar);
        });
        document.querySelectorAll('#sidebarOptions .theme-option').forEach(function(el) {
            el.classList.toggle('active', el.dataset.value === prefs.sidebar);
        });
        document.querySelectorAll('#contentOptions .theme-option').forEach(function(el) {
            el.classList.toggle('active', el.dataset.value === prefs.content);
        });
    }

    function init() {
        const prefs = loadPrefs();
        applyTheme(prefs);

        const themeBtn = document.getElementById('themeBtn');
        const themePanel = document.getElementById('themePanel');
        const themePanelClose = document.getElementById('themePanelClose');

        if (!themeBtn || !themePanel) return;

        themeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isActive = themePanel.classList.contains('active');
            document.querySelectorAll('.dropdown-panel').forEach(function(p) { p.classList.remove('active'); });
            if (!isActive) {
                themePanel.classList.add('active');
            }
        });

        if (themePanelClose) {
            themePanelClose.addEventListener('click', function(e) {
                e.stopPropagation();
                themePanel.classList.remove('active');
            });
        }

        themePanel.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        function setupZone(containerId, zone) {
            var container = document.getElementById(containerId);
            if (!container) return;
            container.addEventListener('click', function(e) {
                var option = e.target.closest('.theme-option');
                if (!option) return;
                e.preventDefault();
                e.stopPropagation();
                prefs[zone] = option.dataset.value;
                savePrefs(prefs);
                applyTheme(prefs);
                try { lucide.createIcons(); } catch(e) {}
            });
        }

        setupZone('topbarOptions', 'topbar');
        setupZone('sidebarOptions', 'sidebar');
        setupZone('contentOptions', 'content');

        document.addEventListener('click', function(e) {
            if (!themePanel.contains(e.target) && e.target !== themeBtn) {
                themePanel.classList.remove('active');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                themePanel.classList.remove('active');
            }
        });

        try { lucide.createIcons(); } catch(e) {}
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
