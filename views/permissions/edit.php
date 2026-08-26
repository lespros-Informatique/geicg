<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : (isset($permission) ? $permission : []);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_permission']) ? 'Éditer ' : 'Ajouter ' ?> une Permission Granulaire</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des autorisations unitaires et des privilèges système</p>
        </div>
        <a href="<?= RACINE ?>permission/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>permission/<?= !empty($item['id_permission']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_permission'])): ?>
            <input type="hidden" name="id_permission" value="<?= $item['id_permission'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Libellé de la Permission <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" name="libelle_permission" value="<?= htmlspecialchars($item['libelle_permission'] ?? '') ?>" placeholder="Ex: Saisie et Édition des Notes" required style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600;">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Code Système <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" name="code_permission" value="<?= htmlspecialchars($item['code_permission'] ?? '') ?>" placeholder="Ex: MANAGE_GRADES" <?= !empty($item['id_permission']) ? 'readonly' : 'required' ?> style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: <?= !empty($item['id_permission']) ? '#F1F5F9' : '#FFF' ?>; font-family: monospace; font-weight: 700;">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Module Métier Associé <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" name="module_permission" style="width: 100%;" required>
                <option value="ACADEMIQUE" <?= (($item['module_permission'] ?? '') === 'ACADEMIQUE') ? 'selected' : '' ?>>Structure Académique (Cycles, Filières, Classes)</option>
                <option value="PEDAGOGIE" <?= (($item['module_permission'] ?? '') === 'PEDAGOGIE') ? 'selected' : '' ?>>Pédagogie & Notes (Enseignants, Cours, Examens)</option>
                <option value="SCOLAIRITE" <?= (($item['module_permission'] ?? '') === 'SCOLAIRITE') ? 'selected' : '' ?>>Scolarité & Admissions (Élèves, Inscriptions)</option>
                <option value="FINANCE" <?= (($item['module_permission'] ?? '') === 'FINANCE') ? 'selected' : '' ?>>Finances & Caisse (Paiements, Dépenses)</option>
                <option value="ADMINISTRATION" <?= (($item['module_permission'] ?? '') === 'ADMINISTRATION') ? 'selected' : '' ?>>Administration & Sécurité (Users, Rôles)</option>
                <option value="COMMUNICATION" <?= (($item['module_permission'] ?? '') === 'COMMUNICATION') ? 'selected' : '' ?>>Communication & Médias (Événements, Documents)</option>
              </select>
            </div>

            <?php if (!empty($item['id_permission'])): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
              <select class="form-control" name="statut_permission" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
                <option value="actif" <?= (($item['statut_permission'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= (($item['statut_permission'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif</option>
              </select>
            </div>
            <?php endif; ?>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer la Permission</button>
            <a href="<?= RACINE ?>permission/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
