<footer class="footer" id="footer">
        <div class="footer-content">
            <div>&copy; 2026 LAVEX Admin. Tous droits réservés.</div>
            <div class="footer-links">
                <a href="#">Documentation</a>
                <a href="#">Support</a>
            </div>
        </div>
    </footer>

    <div class="bottom-nav" id="bottomNav">
        <a href="<?= RACINE ?>" class="bottom-nav-item">
            <i data-lucide="<?= !empty($isPressing) ? 'store' : 'layout-dashboard' ?>"></i>
            <span><?= !empty($isPressing) ? 'Atelier' : 'Accueil' ?></span>
        </a>
        
        <?php if (!empty($isPressing)): ?>
            <a href="<?= RACINE ?>commande/list" class="bottom-nav-item">
                <i data-lucide="clipboard-list"></i>
                <span>Commandes</span>
            </a>
            <a href="<?= RACINE ?>tarifs/list" class="bottom-nav-item">
                <i data-lucide="tag"></i>
                <span>Tarifs</span>
            </a>
            <a href="<?= RACINE ?>retrait/list" class="bottom-nav-item">
                <i data-lucide="wallet"></i>
                <span>Solde</span>
            </a>
        <?php elseif (!empty($isLivreur)): ?>
            <a href="<?= RACINE ?>mission/carte" class="bottom-nav-item">
                <i data-lucide="navigation"></i>
                <span>GPS Live</span>
            </a>
            <a href="<?= RACINE ?>mission/list" class="bottom-nav-item">
                <i data-lucide="clipboard-list"></i>
                <span>Missions</span>
            </a>
        <?php else: ?>
            <a href="<?= RACINE ?>pressing/list" class="bottom-nav-item">
                <i data-lucide="building-2"></i>
                <span>Pressings</span>
            </a>
            <a href="<?= RACINE ?>commande/list" class="bottom-nav-item">
                <i data-lucide="clipboard-list"></i>
                <span>Commandes</span>
            </a>
            <a href="<?= RACINE ?>abonnement/list" class="bottom-nav-item">
                <i data-lucide="credit-card"></i>
                <span>Abos B2B</span>
            </a>
        <?php endif; ?>

        <button type="button" class="bottom-nav-item" id="bnProfil">
            <i data-lucide="user"></i>
            <span>Profil</span>
        </button>
    </div>


    <div class="dropdown-panel bottom-sheet" id="panelProfil">
        <div class="dropdown-header">
            <h3>Profil</h3>
        </div>
        <div class="dropdown-grid">
            <a href="<?= RACINE ?>user/profil" class="dropdown-card">
                <i data-lucide="user"></i>
                <span>Mon profil</span>
            </a>
            <a href="<?= RACINE ?>user/editPassword" class="dropdown-card">
                <i data-lucide="lock"></i>
                <span>Mot de passe</span>
            </a>
        </div>
    </div>

    <!-- Generic Modal -->
    <div class="modal-overlay" id="genericModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Ajouter</h3>
                <button class="modal-close" id="modalClose"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body" id="modalBody">
                <form id="modalForm"></form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" id="modalCancel">Annuler</button>
                <button class="btn-primary" id="modalSave">Enregistrer</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="confirmModal">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title" id="confirmTitle">Confirmation</h3>
                <button class="modal-close" id="confirmClose"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">Êtes-vous sûr ?</p>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" id="confirmCancel">Annuler</button>
                <button class="btn-primary" style="background: var(--danger);" id="confirmOk">Confirmer</button>
            </div>
        </div>
    </div>



    <div class="mobile-actions-overlay" id="mobileActionOverlay">
        <div class="mobile-actions-sheet" id="mobileActionSheet">
            <div class="mobile-actions-content" id="mobileActionsContent"></div>
        </div>
    </div>

   <?php include_once __DIR__ . '/footer-link.php' ?>
</body>
</html>