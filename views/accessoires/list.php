<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="package" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Kits & Accessoires d'Inscription
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Répertoire central des articles et kits scolaires</p>
        </div>
        <?php if (!isset($canAccess) || $canAccess([PERMISSIONS::MANAGE_ACCESSOIRES, PERMISSIONS::CONFIG_ACADEMIQUE])): ?>
        <button type="button" class="btn btn-primary btn-add-acc-type" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Kit / Article
        </button>
        <?php endif; ?>
      </div>

      <!-- Navigation Tabs (Kits par Filière & Niveau vs Répertoire des Kits) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px; flex-wrap: wrap;">
        <a href="<?= url('accessoire/cibles') ?>" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 17px; height: 17px;"></i> Kits Exigés par Filière & Niveau
        </a>
        <a href="<?= url('accessoire/list') ?>" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="package" style="width: 17px; height: 17px;"></i> Répertoire des Kits & Articles
        </a>
      </div>

      <!-- TABLE CARD : RÉPERTOIRE DES ACCESSOIRES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-accessoires" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px; width: 150px;">Code</th>
                <th style="padding: 12px;">Désignation du Kit / Article</th>
                <th style="padding: 12px; width: 100px;" class="text-center">Statut</th>
                <th style="padding: 12px; width: 160px; text-align: right;">Actions</th>
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
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UN KIT OU ARTICLE DU RÉPERTOIRE -->
<!-- ========================================================================= -->
<div id="modal-accessoire-type" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 460px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-accessoire-type-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Kit / Article
      </h3>
      <button type="button" class="btn-close-modal-acc-type" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-accessoire-type" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_accessoire" id="acc_type_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Désignation du kit / accessoire <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_accessoire" id="acc_type_libelle" required placeholder="Ex: Polo, Blouse Blanche, Cravate, Tissu..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_accessoire" id="acc_type_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-acc-type" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-acc-type" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<?php
ViewContext::setScripts(['public/assets/js/modules/accessoires.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
