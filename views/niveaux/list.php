<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Niveaux d'Études</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Catalogue général des niveaux académiques (ex: Licence 1, Licence 2, Master 1, Master 2...)</p>
        </div>
        <div>
          <button type="button" class="btn btn-primary btn-add-niveau" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau
          </button>
        </div>
      </div>

      <!-- CATALOGUE DES NIVEAUX -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-niveaux-catalogue" class="table display nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>Code</th>
                <th>Intitulé du Niveau</th>
                <th class="text-center">Statut</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UN NIVEAU D'ÉTUDES -->
<!-- ========================================================================= -->
<div id="modal-niveau" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-niveau-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau
      </h3>
      <button type="button" class="btn-close-modal-niveau" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-niveau" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_niveau" id="niveau_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Intitulé complet du niveau <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_niveau" id="niveau_libelle" required placeholder="Ex: Licence 1, Master 2, BTS 1ère Année..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Abréviation / Sigle court
        </label>
        <input type="text" name="slug_niveau" id="niveau_slug" placeholder="Ex: L1, L2, M1, M2, BTS1..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Utilisé pour les affichages compacts dans les listes et emplois du temps.</small>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_niveau" id="niveau_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-niveau" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-niveau" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<script type="application/json" id="niveaux-list-config">
<?= json_encode([
    'racine' => RACINE,
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/filieres.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>