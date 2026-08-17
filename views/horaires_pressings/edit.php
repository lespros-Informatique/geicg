<?php
require_once __DIR__ . '/../../public/inc/header.php';
$horaire = isset($horaire) ? $horaire : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($horaire['id_horaire']) ? 'Modifier l\'horaire' : 'Ajouter un horaire' ?></h1>
          <p class="page-subtitle">Gestion des horaires</p>
        </div>
        <a href="<?= RACINE ?>horaire/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations de l'horaire</h2>
          </div>
        </div>

        <div class="card-body">
          <form class="formEditHoraire">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_horaire" name="id_horaire" value="<?= htmlspecialchars($horaire['id_horaire'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="pressing_code">Pressing</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <input type="text" class="form-control" id="pressing_code" name="pressing_code"
                          value="<?= htmlspecialchars($horaire['pressing_code'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="pressingError"></div>
               </div>

               <div class="form-field">
                 <label for="jour">Jour</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('calendar'); ?></span>
                   <select class="form-control" id="jour" name="jour" required>
                     <option value="">Sélectionner</option>
                     <option value="lundi" <?= ($horaire['jour'] ?? '') === 'lundi' ? 'selected' : '' ?>>Lundi</option>
                     <option value="mardi" <?= ($horaire['jour'] ?? '') === 'mardi' ? 'selected' : '' ?>>Mardi</option>
                     <option value="mercredi" <?= ($horaire['jour'] ?? '') === 'mercredi' ? 'selected' : '' ?>>Mercredi</option>
                     <option value="jeudi" <?= ($horaire['jour'] ?? '') === 'jeudi' ? 'selected' : '' ?>>Jeudi</option>
                     <option value="vendredi" <?= ($horaire['jour'] ?? '') === 'vendredi' ? 'selected' : '' ?>>Vendredi</option>
                     <option value="samedi" <?= ($horaire['jour'] ?? '') === 'samedi' ? 'selected' : '' ?>>Samedi</option>
                     <option value="dimanche" <?= ($horaire['jour'] ?? '') === 'dimanche' ? 'selected' : '' ?>>Dimanche</option>
                   </select>
                 </div>
                 <div class="error-message" id="jourError"></div>
               </div>

               <div class="form-field">
                 <label for="heure_ouverture">Heure ouverture</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                   <input type="time" class="form-control" id="heure_ouverture" name="heure_ouverture"
                          value="<?= htmlspecialchars($horaire['heure_ouverture'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="ouvertureError"></div>
               </div>

               <div class="form-field">
                 <label for="heure_fermeture">Heure fermeture</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                   <input type="time" class="form-control" id="heure_fermeture" name="heure_fermeture"
                          value="<?= htmlspecialchars($horaire['heure_fermeture'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="fermetureError"></div>
               </div>

               <div class="form-field">
                 <label for="est_ferme">Fermé</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="est_ferme" name="est_ferme">
                     <option value="0" <?= ($horaire['est_ferme'] ?? 0) == 0 ? 'selected' : '' ?>>Non</option>
                     <option value="1" <?= ($horaire['est_ferme'] ?? 0) == 1 ? 'selected' : '' ?>>Oui</option>
                   </select>
                 </div>
                 <div class="error-message" id="fermeError"></div>
               </div>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditHoraire">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>horaire/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/horaires.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
