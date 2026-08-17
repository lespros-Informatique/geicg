<?php
require_once __DIR__ . '/../../public/inc/header.php';
$service = isset($service) ? $service : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($service['id_service']) ? 'Modifier le service' : 'Ajouter un service' ?></h1>
          <p class="page-subtitle">Gestion des services</p>
        </div>
        <a href="<?= RACINE ?>service/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du service</h2>
          </div>
          <?php if (isset($service['statut_service'])): ?>
            <span class="badge-status <?= $service['statut_service'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $service['statut_service'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditService">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_service" name="id_service" value="<?= htmlspecialchars($service['id_service'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="libelle_service">Libellé</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <input type="text" class="form-control" id="libelle_service" name="libelle_service"
                          value="<?= htmlspecialchars($service['libelle_service'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="libelleError"></div>
               </div>

               <div class="form-field">
                 <label for="description_service">Description</label>
                 <textarea class="form-control" id="description_service" name="description_service"><?= htmlspecialchars($service['description_service'] ?? '') ?></textarea>
                 <div class="error-message" id="descriptionError"></div>
               </div>

               <?php if (isset($service['statut_service'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($service['statut_service'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($service['statut_service'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditService">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>service/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/services.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
