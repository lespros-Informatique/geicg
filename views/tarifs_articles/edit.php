<?php
require_once __DIR__ . '/../../public/inc/header.php';
$tarif     = isset($tarif) ? $tarif : [];
$articles  = isset($articles) ? $articles : [];
$services  = isset($services) ? $services : [];
$pressings = isset($pressings) ? $pressings : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($tarif['id_tarif']) ? 'Modifier le tarif article' : 'Ajouter un tarif article' ?></h1>
          <p class="page-subtitle">Gestion de la grille tarifaire</p>
        </div>
        <a href="<?= RACINE ?>tarif/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Configuration du tarif</h2>
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
               <?php if ($isSuperAdmin): ?>
                 <!-- Super Admin peut choisir le pressing à configurer -->
                 <div class="form-field">
                   <label for="pressing_code">Pressing partenaire</label>
                   <div class="input-with-icon">
                     <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                     <select class="form-control" id="pressing_code" name="pressing_code" required>
                       <option value="">-- Choisir un pressing --</option>
                       <?php foreach ($pressings as $p): ?>
                         <option value="<?= htmlspecialchars($p['code_pressing']) ?>" <?= ($tarif['pressing_code'] ?? '') === $p['code_pressing'] ? 'selected' : '' ?>>
                           <?= htmlspecialchars($p['libelle_pressing']) ?> (<?= htmlspecialchars($p['code_pressing']) ?>)
                         </option>
                       <?php endforeach; ?>
                     </select>
                   </div>
                   <div class="error-message" id="pressingError"></div>
                 </div>
               <?php else: ?>
                 <!-- Pressing Pro : son pressing_code est automatiquement injecté -->
                 <input type="hidden" id="pressing_code" name="pressing_code" value="<?= htmlspecialchars($currentPressingCode ?? '') ?>">
               <?php endif; ?>

               <div class="form-field">
                 <label for="article_code">Article du catalogue</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('shirt'); ?></span>
                   <select class="form-control" id="article_code" name="article_code" required>
                     <option value="">-- Sélectionner un article --</option>
                     <?php foreach ($articles as $art): ?>
                       <option value="<?= htmlspecialchars($art['code_article']) ?>" <?= ($tarif['article_code'] ?? '') === $art['code_article'] ? 'selected' : '' ?>>
                         <?= htmlspecialchars($art['libelle_article']) ?> (<?= htmlspecialchars($art['categorie_article_code'] ?? '') ?>)
                       </option>
                     <?php endforeach; ?>
                   </select>
                 </div>
                 <div class="error-message" id="articleError"></div>
               </div>

               <div class="form-field">
                 <label for="service_code">Service associé</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('sparkles'); ?></span>
                   <select class="form-control" id="service_code" name="service_code" required>
                     <option value="">-- Sélectionner un service --</option>
                     <?php foreach ($services as $srv): ?>
                       <option value="<?= htmlspecialchars($srv['code_service']) ?>" <?= ($tarif['service_code'] ?? '') === $srv['code_service'] ? 'selected' : '' ?>>
                         <?= htmlspecialchars($srv['libelle_service']) ?>
                       </option>
                     <?php endforeach; ?>
                   </select>
                 </div>
                 <div class="error-message" id="serviceError"></div>
               </div>

               <div class="form-field">
                 <label for="prix_tarif">Prix appliqué (FCFA)</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                   <input type="number" class="form-control" id="prix_tarif" name="prix_tarif" min="100" step="50"
                          value="<?= htmlspecialchars($tarif['prix_tarif'] ?? 0) ?>" required placeholder="ex: 1500">
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
                  Enregistrer le tarif
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
