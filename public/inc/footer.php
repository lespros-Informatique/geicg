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
            <i data-lucide="home"></i>
            <span>Accueil</span>
        </a>
        <a href="<?= RACINE ?>paiement/list" class="bottom-nav-item">
            <i data-lucide="credit-card"></i>
            <span>Paiement</span>
        </a>
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
            <a href="<?= RACINE ?>setting/list" class="dropdown-card">
                <i data-lucide="settings"></i>
                <span>Paramètres</span>
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

    <div class="modal-overlay" id="commandeModalOverlay">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h3 class="modal-title" id="commandeModalTitle">Nouvelle commande</h3>
                <button class="modal-close" id="commandeModalClose"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body" id="commandeModalBody">
                <form id="commandeForm">
                    <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
                    <input type="hidden" name="client_code" id="cmdClientCode">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Code client</label>
                            <div class="input-wrapper">
                                <input type="text" name="client_code_display" id="cmdClientCodeDisplay" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Campagne</label>
                            <div class="input-wrapper">
                                <input type="text" name="campagne_code_display" id="cmdCampagneDisplay" readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" id="commandeModalCancel">Annuler</button>
                <button class="btn-primary" id="commandeModalSave">Enregistrer</button>
            </div>
        </div>
    </div>

    <div class="mobile-actions-overlay" id="mobileActionOverlay">
        <div class="mobile-actions-sheet" id="mobileActionSheet">
            <div class="mobile-actions-content" id="mobileActionsContent"></div>
        </div>
    </div>

   <?php include_once 'footer-link.php' ?>
</body>
</html>