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
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user-check" style="color: #2563EB;"></i> <?= isset($client['id_client']) ? 'Modifier le client' : 'Ajouter un client' ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
            Mise à jour des coordonnées et des informations de livraison du client
          </p>
        </div>
        <a href="<?= RACINE ?>client/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF; max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 14px;">
          <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="info" style="color: #2563EB; width: 18px; height: 18px;"></i> Coordonnées & Identifiants Client
          </h2>
          <?php if (isset($client['statut_client'])): ?>
            <span class="badge-status <?= $client['statut_client'] == 'actif' ? 'delivered' : 'cancelled' ?>" style="font-weight: 700;">
              <?= $client['statut_client'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body" style="padding: 0;">
          <!-- BANNIÈRE D'ALERTE ROUGE EN CAS D'ERREUR -->
          <div id="editClientAlert" class="alert alert-danger" style="display: none; background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 13.5px; font-weight: 600; align-items: center; gap: 8px;">
            <i class="fa fa-exclamation-circle"></i> <span id="editClientAlertText"></span>
          </div>

          <form class="formEditClient">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_client" name="id_client" value="<?= htmlspecialchars($client['id_client'] ?? '') ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
              <div class="form-group" style="margin: 0;">
                <label for="nom" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                  Nom & Prénoms <span style="color: #DC2626;">*</span>
                </label>
                <input type="text" class="form-control" id="nom" name="nom"
                       value="<?= htmlspecialchars($client['nom_client'] ?? '') ?>" placeholder="Ex: Kouassi Jean" required style="width: 100%; padding: 10px 12px; font-size: 14px;">
              </div>

              <div class="form-group" style="margin: 0;">
                <label for="telephone" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                  Téléphone (Login de connexion) <span style="color: #DC2626;">*</span>
                </label>
                <input type="text" class="form-control" id="telephone" name="telephone"
                       value="<?= htmlspecialchars($client['telephone_client'] ?? '') ?>" placeholder="Ex: 0708091011" required style="width: 100%; padding: 10px 12px; font-size: 14px;">
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
              <div class="form-group" style="margin: 0;">
                <label for="email_client" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                  Email (Optionnel)
                </label>
                <input type="email" class="form-control" id="email_client" name="email_client"
                       value="<?= htmlspecialchars($client['email_client'] ?? '') ?>" placeholder="Ex: client@lavex.ci" style="width: 100%; padding: 10px 12px; font-size: 14px;">
              </div>

              <div class="form-group" style="margin: 0;">
                <label for="quartier_client" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                  Quartier
                </label>
                <input type="text" class="form-control" id="quartier_client" name="quartier_client"
                       value="<?= htmlspecialchars($client['quartier_client'] ?? '') ?>" placeholder="Ex: Angré 8ème Tranche" style="width: 100%; padding: 10px 12px; font-size: 14px;">
              </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
              <label for="adresse_client" style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                Adresse de livraison / domicile
              </label>
              <textarea class="form-control" id="adresse_client" name="adresse_client" rows="3" placeholder="Ex: Rue des Jardins, Villa 14 face à l'école" style="width: 100%; padding: 10px 12px; font-size: 14px;"><?= htmlspecialchars($client['adresse_client'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 16px;">
              <a href="<?= RACINE ?>client/list" class="btn btn-secondary" style="font-weight: 600;">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i> Annuler
              </a>
              <button type="submit" class="btn btn-primary btnEditClient" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="save" style="width: 16px; height: 16px;"></i> Sauvegarder les modifications
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/entities/clients.js?v=<?= time() ?>"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
