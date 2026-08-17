<?php
require_once __DIR__ . '/../../public/inc/header.php';
$pressing = isset($pressing) ? $pressing : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($pressing['id_pressing']) ? 'Modifier le pressing' : 'Ajouter un pressing' ?></h1>
          <p class="page-subtitle">Gestion des pressings</p>
        </div>
        <a href="<?= RACINE ?>pressing/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du pressing</h2>
          </div>
          <?php if (isset($pressing['statut_pressing'])): ?>
            <span class="badge-status <?= $pressing['statut_pressing'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $pressing['statut_pressing'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditPressing">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_pressing" name="id_pressing" value="<?= htmlspecialchars($pressing['id_pressing'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="libelle_pressing">Libellé</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <input type="text" class="form-control" id="libelle_pressing" name="libelle_pressing"
                          value="<?= htmlspecialchars($pressing['libelle_pressing'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="libelleError"></div>
               </div>

               <div class="form-field">
                 <label for="telephone_pressing">Téléphone</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('phone'); ?></span>
                   <input type="text" class="form-control" id="telephone_pressing" name="telephone_pressing"
                          value="<?= htmlspecialchars($pressing['telephone_pressing'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="telephoneError"></div>
               </div>

               <div class="form-field">
                 <label for="email_pressing">Email</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                   <input type="email" class="form-control" id="email_pressing" name="email_pressing"
                          value="<?= htmlspecialchars($pressing['email_pressing'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="emailError"></div>
               </div>

               <div class="form-field">
                 <label for="adresse_pressing">Adresse</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <textarea class="form-control" id="adresse_pressing" name="adresse_pressing"><?= htmlspecialchars($pressing['adresse_pressing'] ?? '') ?></textarea>
                 </div>
                 <div class="error-message" id="adresseError"></div>
               </div>

               <?php if (isset($pressing['statut_pressing'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($pressing['statut_pressing'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($pressing['statut_pressing'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                     <option value="2" <?= ($pressing['statut_pressing'] ?? '') == 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditPressing">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>pressing/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/pressings.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
