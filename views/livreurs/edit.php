<?php
require_once __DIR__ . '/../../public/inc/header.php';
$livreur = isset($livreur) ? $livreur : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($livreur['id_livreur']) ? 'Modifier le livreur' : 'Ajouter un livreur' ?></h1>
          <p class="page-subtitle">Gestion des livreurs</p>
        </div>
        <a href="<?= RACINE ?>livreur/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du livreur</h2>
          </div>
          <?php if (isset($livreur['statut_livreur'])): ?>
            <span class="badge-status <?= $livreur['statut_livreur'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $livreur['statut_livreur'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditLivreur">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_livreur" name="id_livreur" value="<?= htmlspecialchars($livreur['id_livreur'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="nom_livreur">Nom</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('user'); ?></span>
                   <input type="text" class="form-control" id="nom_livreur" name="nom_livreur"
                          value="<?= htmlspecialchars($livreur['nom_livreur'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="nomError"></div>
               </div>

               <div class="form-field">
                 <label for="prenom_livreur">Prénom</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('user'); ?></span>
                   <input type="text" class="form-control" id="prenom_livreur" name="prenom_livreur"
                          value="<?= htmlspecialchars($livreur['prenom_livreur'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="prenomError"></div>
               </div>

               <div class="form-field">
                 <label for="telephone_livreur">Téléphone</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('phone'); ?></span>
                   <input type="text" class="form-control" id="telephone_livreur" name="telephone_livreur"
                          value="<?= htmlspecialchars($livreur['telephone_livreur'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="telephoneError"></div>
               </div>

               <div class="form-field">
                 <label for="pressing_code">Pressing</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <input type="text" class="form-control" id="pressing_code" name="pressing_code"
                          value="<?= htmlspecialchars($livreur['pressing_code'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="pressingError"></div>
               </div>

               <?php if (isset($livreur['statut_livreur'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($livreur['statut_livreur'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($livreur['statut_livreur'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditLivreur">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>livreur/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/livreurs.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
