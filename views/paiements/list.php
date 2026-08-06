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
        <button class="btn btn-primary" id="openPaiementModal">
          <i class="fa fa-plus"></i> Nouveau paiement
        </button>
      </div>

      <div class="card">
        <div class="card-body">
          <div class="table-responsive-mobile">
            <table class="table" id="dataTable">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Commande</th>
                  <th>Montant</th>
                  <th>Mode</th>
                  <th>Statut</th>
                  <th>Date</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
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
        <div class="form-group">
          <label>Code commande</label>
          <div class="input-wrapper">
            <input type="text" name="commande_code" id="payCommandeCode" required>
          </div>
        </div>
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
                <option value="especes">Espèce</option>
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

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>

<script>
$(function() {
    const modal = document.getElementById('paiementModal');
    const openBtn = document.getElementById('openPaiementModal');
    const closeBtn = document.getElementById('paiementModalClose');
    const cancelBtn = document.getElementById('paiementModalCancel');
    const saveBtn = document.getElementById('paiementModalSave');
    const form = document.getElementById('paiementForm');

    function setToday() {
        var d = new Date();
        var str = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0') + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        document.getElementById('payDate').value = str;
    }

    function openModal() {
        setToday();
        form.reset();
        modal.classList.add('active');
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });

    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            loading(saveBtn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');
            $.ajax({
                url: LINK + 'paiement/add',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(resp) {
                    loading(saveBtn, false, 'Enregistrer');
                    if (resp && resp.status) {
                        showToast(resp.message || 'Paiement enregistré', 'success');
                        closeModal();
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    } else {
                        showToast(resp ? resp.message : 'Erreur', 'error');
                    }
                },
                error: function() {
                    loading(saveBtn, false, 'Enregistrer');
                    showToast('Erreur serveur', 'error');
                }
            });
        });
    }
});
</script>
