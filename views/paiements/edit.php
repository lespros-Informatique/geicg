<?php
require_once __DIR__ . '/../../public/inc/header.php';
$paiement = isset($paiement) ? $paiement : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Modifier le paiement</h1>
          <p class="page-subtitle">Mettez Ã  jour les informations du paiement.</p>
        </div>
        <a href="<?= RACINE ?>paiement/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour Ã  la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du paiement</h2>
          </div>
          <span class="badge-status <?= ($paiement['statut_paiement'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
            <?= ($paiement['statut_paiement'] ?? '') == 'actif' ? 'Actif' : 'Inactif' ?>
          </span>
        </div>

        <div class="card-body">
          <form class="formEditPaiement">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_paiement" name="id_paiement" value="<?= htmlspecialchars($paiement['id_paiement'] ?? '') ?>">

            <div class="form-grid">
              <div class="form-field">
                <label for="commande_code">Code commande</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('shopping-cart'); ?></span>
                  <input type="text" class="form-control" id="commande_code" name="commande_code"
                         value="<?= htmlspecialchars($paiement['commande_code'] ?? '') ?>" required>
                </div>
                <div class="error-message" id="commande_codeError"></div>
              </div>

              <div class="form-field">
                <label for="montant_paiement">Montant (FCFA)</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                  <input type="number" class="form-control" id="montant_paiement" name="montant_paiement"
                         value="<?= htmlspecialchars($paiement['montant_paiement'] ?? 0) ?>" required>
                </div>
                <div class="error-message" id="montant_paiementError"></div>
              </div>

              <div class="form-field">
                <label for="methode_paiement">MÃ©thode de paiement</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('credit-card'); ?></span>
                  <select class="form-control" id="methode_paiement" name="methode_paiement">
                    <?php foreach (['cash','mobile_money','carte'] as $m): ?>
                    <option value="<?= $m ?>" <?= ($paiement['methode_paiement'] ?? '') === $m ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$m)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="error-message" id="methode_paiementError"></div>
              </div>

              <div class="form-field">
                <label for="statut_paiement">Statut</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('signal'); ?></span>
                  <select class="form-control" id="statut_paiement" name="statut_paiement">
                    <?php foreach (STATUTS::PAIEMENTS as $s): ?>
                    <option value="<?= $s ?>" <?= ($paiement['statut_paiement'] ?? '') == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="error-message" id="statut_paiementError"></div>
              </div>

              <div class="form-field">
                <label for="reference_transaction">RÃ©fÃ©rence transaction</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('hashtag'); ?></span>
                  <input type="text" class="form-control" id="reference_transaction" name="reference_transaction"
                         value="<?= htmlspecialchars($paiement['reference_transaction'] ?? '') ?>">
                </div>
                <div class="error-message" id="reference_transactionError"></div>
              </div>

              <div class="readonly-grid">
                <div class="readonly-field">
                  <label>Code paiement</label>
                  <p><?= htmlspecialchars($paiement['code_paiement'] ?? '') ?></p>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditPaiement">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary">
                <i data-lucide="x"></i>
                Annuler
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>



<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>


