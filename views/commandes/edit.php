<?php
require_once __DIR__ . '/../../public/inc/header.php';
$order = isset($order) ? $order : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Modifier la commande</h1>
          <p class="page-subtitle">Mettez Ã  jour les informations de la commande.</p>
        </div>
        <a href="<?= RACINE ?>commande/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour Ã  la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations de la commande</h2>
          </div>
          <span class="badge-status <?= ($order['statut_commande'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
            <?= ($order['statut_commande'] ?? '') == 'actif' ? 'Actif' : 'Inactif' ?>
          </span>
        </div>

        <div class="card-body">
          <form class="formEditOrder">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_commande" name="id_commande" value="<?= htmlspecialchars($order['id_commande'] ?? '') ?>">

            <div class="form-grid">
              <div class="form-field">
                <label for="client_code">Code client</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('user'); ?></span>
                  <input type="text" class="form-control" id="client_code" name="client_code"
                         value="<?= htmlspecialchars($order['client_code'] ?? '') ?>" required>
                </div>
                <div class="error-message" id="client_codeError"></div>
              </div>

              <div class="form-field">
                <label for="adresse_livraison_commande">Adresse de livraison</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                  <textarea class="form-control" id="adresse_livraison_commande" name="adresse_livraison_commande" required><?= htmlspecialchars($order['adresse_livraison_commande'] ?? '') ?></textarea>
                </div>
                <div class="error-message" id="adresse_livraison_commandeError"></div>
              </div>

              <div class="form-field">
                <label for="montant_total_commande">Montant total (FCFA)</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                  <input type="number" class="form-control" id="montant_total_commande" name="montant_total_commande"
                         value="<?= htmlspecialchars($order['montant_total_commande'] ?? 0) ?>" required>
                </div>
                <div class="error-message" id="montant_total_commandeError"></div>
              </div>

              <div class="form-field">
                <label for="statut_commande">Statut</label>
                <div class="input-with-icon">
                  <span class="input-icon"><?= Validator::icon('signal'); ?></span>
                  <select class="form-control" id="statut_commande" name="statut_commande">
                    <?php foreach (STATUTS::COMMANDES as $s): ?>
                    <option value="<?= $s ?>" <?= ($order['statut_commande'] ?? '') == $s ? 'selected' : '' ?>><?= str_replace('_',' ',ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="error-message" id="statut_commandeError"></div>
              </div>

              <div class="readonly-grid">
                <div class="readonly-field">
                  <label>Code commande</label>
                  <p><?= htmlspecialchars($order['code_commande'] ?? '') ?></p>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditOrder">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>commande/list" class="btn btn-secondary">
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
