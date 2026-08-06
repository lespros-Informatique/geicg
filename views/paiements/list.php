<?php
require_once __DIR__ . '/../../public/inc/header.php';
$csrfToken = Validator::generateCsrfToken();

?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <h1>Paiements</h1>
      </div>

      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3>1. Sélectionner un client</h3>
        </div>
        <div class="card-body">
          <div id="clientsLoading" style="display:none;color:#666;">Chargement des clients...</div>
          <div id="clientsList" class="clients-grid"></div>
          <div id="clientsEmpty" style="display:none;color:#999;text-align:center;padding:20px;">Aucun client trouvé pour cette campagne.</div>
        </div>
      </div>

      <div class="card" style="margin-bottom:20px;display:none;" id="kitsCard">
        <div class="card-header">
          <h3>2. Sélectionner un kit</h3>
          <button class="btn btn-sm btn-secondary" id="backToClients" style="display:none;"><i class="fa fa-arrow-left"></i> Retour</button>
        </div>
        <div class="card-body">
          <div id="kitsLoading" style="display:none;color:#666;">Chargement des kits...</div>
          <div id="kitsList" class="kits-grid"></div>
          <div id="kitsEmpty" style="display:none;color:#999;text-align:center;padding:20px;">Ce client n'a pas de kit pour cette campagne.</div>
        </div>
      </div>

      <div class="card" style="margin-bottom:20px;display:none;" id="calendarCard">
        <div class="card-header">
          <h3>3. Calendrier des paiements</h3>
          <div style="display:flex;gap:8px;">
            <button type="button" class="btn btn-sm btn-warning" id="closeSessionBtn" style="display:none;">
              <i class="fa fa-toggle-on"></i> Fermer la session
            </button>
            <button class="btn btn-sm btn-secondary" id="backToKits" style="display:none;"><i class="fa fa-arrow-left"></i> Retour</button>
          </div>
        </div>
        <div class="card-body">
          <div id="calendarInfo" style="margin-bottom:12px;font-size:0.9rem;color:#666;"></div>
          <div id="calendarLoading" style="display:none;color:#666;">Chargement du calendrier...</div>
          <div id="calendarGrid" class="calendar-grid"></div>
          <div id="calendarEmpty" style="display:none;color:#999;text-align:center;padding:20px;">Aucune donnée.</div>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal-overlay" id="paiementModal">
  <div class="modal" style="max-width: 480px;">
    <div class="modal-header">
      <h3 class="modal-title">Enregistrer un paiement</h3>
      <button class="modal-close" id="paiementModalClose"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
      <form id="paiementForm">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="ligne_commande_code" id="payLigneCode">
        <input type="hidden" name="code" id="payCode">
        <div class="form-row">
          <div class="form-group">
            <label>Date</label>
            <div class="input-wrapper">
              <input type="text" name="date_paiement" id="payDate" readonly>
            </div>
          </div>
          <div class="form-group">
            <label>Montant (FCFA)</label>
            <div class="input-wrapper">
              <input type="number" name="montant_paiement" id="payMontant" required min="1" step="1">
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Mode de paiement</label>
            <div class="input-wrapper">
              <select name="mode_paiement" id="payMode">
                <option value="espece">Espèce</option>
                <option value="orange_money">Orange Money</option>
                <option value="mtn_money">MTN Money</option>
                <option value="wave">Wave</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Référence</label>
            <div class="input-wrapper">
              <input type="text" name="reference_paiement" id="payReference">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Observation</label>
          <div class="input-wrapper">
            <textarea name="observation_paiement" id="payObservation" rows="2"></textarea>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" id="paiementModalCancel">Annuler</button>
      <button class="btn-primary" id="paiementModalSave">Enregistrer</button>
    </div>
  </div>
  </div>
</div>

<div class="modal-overlay" id="sessionModal">
  <div class="modal" style="max-width: 420px;">
    <div class="modal-header">
      <h3 class="modal-title">Session de caisse</h3>
      <button class="modal-close" id="sessionModalClose"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom:12px;">Aucune session de caisse ouverte. Veuillez ouvrir une session pour continuer.</p>
      <form id="sessionForm">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="form-group">
          <label>Montant d'ouverture (FCFA)</label>
          <div class="input-wrapper">
            <input type="number" name="montant_ouverture" id="sessionMontant" value="0" min="0" step="1" required>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" id="sessionModalCancel">Annuler</button>
      <button class="btn-primary" id="sessionModalOpen">Ouvrir la session</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="closeSessionModal">
  <div class="modal" style="max-width: 480px;">
    <div class="modal-header">
      <h3 class="modal-title">Fermer la session de caisse</h3>
      <button class="modal-close" id="closeSessionModalClose"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
      <form id="closeSessionForm">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="session_id" id="closeSessionId">
        <div class="form-group">
          <label>Montant attendu (FCFA)</label>
          <div class="input-wrapper">
            <input type="number" name="montant_attendu" id="closeMontantAttendu" readonly>
          </div>
        </div>
        <div class="form-group">
          <label>Montant réel compté (FCFA)</label>
          <div class="input-wrapper">
            <input type="number" name="montant_reel" id="closeMontantReel" min="0" step="1" required>
          </div>
        </div>
        <div class="form-group">
          <label>Écart</label>
          <div class="input-wrapper">
            <input type="text" name="ecart" id="closeEcart" readonly>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" id="closeSessionModalCancel">Annuler</button>
      <button class="btn-primary" id="closeSessionModalSave">Fermer la session</button>
    </div>
  </div>
</div>

<script src="<?= RACINE ?>json/payment-calendar.js?v=1"></script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
