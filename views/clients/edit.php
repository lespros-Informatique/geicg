<?php
require_once __DIR__ . '/../../public/inc/header.php';
$client = isset($client) ? $client : [];
$csrfToken = Validator::generateCsrfToken();
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($client['id_client']) ? 'Modifier le client' : 'Ajouter un client' ?></h1>
          <p class="page-subtitle">Gestion des clients</p>
        </div>
        <a href="<?= RACINE ?>client/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour Ã  la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du client</h2>
          </div>
          <?php if (isset($client['statut_client'])): ?>
            <span class="badge-status <?= $client['statut_client'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $client['statut_client'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditClient">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_client" name="id_client" value="<?= htmlspecialchars($client['id_client'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="nom">Nom</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('user'); ?></span>
                   <input type="text" class="form-control" id="nom" name="nom"
                          value="<?= htmlspecialchars($client['nom_client'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="nomError"></div>
               </div>

               <div class="form-field">
                 <label for="telephone">TÃ©lÃ©phone</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('contact'); ?></span>
                   <input type="text" class="form-control" id="telephone" name="telephone"
                          value="<?= htmlspecialchars($client['telephone_client'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="telephoneError"></div>
               </div>

               <div class="form-field">
                 <label for="email_client">Email</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                   <input type="email" class="form-control" id="email_client" name="email_client"
                          value="<?= htmlspecialchars($client['email_client'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="emailError"></div>
               </div>

               <div class="form-field">
                 <label for="quartier_client">Quartier</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <input type="text" class="form-control" id="quartier_client" name="quartier_client"
                          value="<?= htmlspecialchars($client['quartier_client'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="quartierError"></div>
               </div>

               <div class="form-field">
                 <label for="adresse_client">Adresse</label>
                 <textarea class="form-control" id="adresse_client" name="adresse_client"><?= htmlspecialchars($client['adresse_client'] ?? '') ?></textarea>
                 <div class="error-message" id="adresseError"></div>
               </div>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditClient">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>client/list" class="btn btn-secondary">
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

<script src="<?= RACINE ?>json/entities/clients.js?v=3"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
