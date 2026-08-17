<?php
require_once __DIR__ . '/../../public/inc/header.php';
$tarif = isset($tarif) ? $tarif : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($tarif['id_tarif']) ? 'Modifier le tarif' : 'Ajouter un tarif' ?></h1>
          <p class="page-subtitle">Gestion des tarifs</p>
        </div>
        <a href="<?= RACINE ?>tarif/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du tarif</h2>
          </div>
          <?php if (isset($tarif['statut_tarif'])): ?>
            <span class="badge-status <?= $tarif['statut_tarif'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $tarif['statut_tarif'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditTarif">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_tarif" name="id_tarif" value="<?= htmlspecialchars($tarif['id_tarif'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="pressing_code">Pressing</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <input type="text" class="form-control" id="pressing_code" name="pressing_code"
                          value="<?= htmlspecialchars($tarif['pressing_code'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="pressingError"></div>
               </div>

               <div class="form-field">
                 <label for="article_code">Article</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <input type="text" class="form-control" id="article_code" name="article_code"
                          value="<?= htmlspecialchars($tarif['article_code'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="articleError"></div>
               </div>

               <div class="form-field">
                 <label for="service_code">Service</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('briefcase'); ?></span>
                   <input type="text" class="form-control" id="service_code" name="service_code"
                          value="<?= htmlspecialchars($tarif['service_code'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="serviceError"></div>
               </div>

               <div class="form-field">
                 <label for="prix_tarif">Prix (FCFA)</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                   <input type="number" class="form-control" id="prix_tarif" name="prix_tarif"
                          value="<?= htmlspecialchars($tarif['prix_tarif'] ?? 0) ?>" required>
                 </div>
                 <div class="error-message" id="prixError"></div>
               </div>

               <?php if (isset($tarif['statut_tarif'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($tarif['statut_tarif'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($tarif['statut_tarif'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditTarif">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>tarif/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/tarifs.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
