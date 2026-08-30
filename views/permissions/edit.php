<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : (isset($permission) ? $permission : []);
$isEdit = !empty($item['id_permission']);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= $isEdit ? 'Éditer ' : 'Ajouter ' ?> une Permission Granulaire</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des autorisations unitaires et des privilèges système</p>
        </div>
        <a href="<?= RACINE ?>permission/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>permission/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_permission" value="<?= $item['id_permission'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Libellé de la Permission <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" name="libelle_permission" value="<?= htmlspecialchars($item['libelle_permission'] ?? '') ?>" placeholder="Ex: Saisie et Édition des Notes" required style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600;">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Code Système <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" name="code_permission" value="<?= htmlspecialchars($item['code_permission'] ?? '') ?>" placeholder="Ex: MANAGE_GRADES" <?= $isEdit ? 'readonly' : 'required' ?> style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: <?= $isEdit ? '#F1F5F9' : '#FFF' ?>; font-family: monospace; font-weight: 700;">
            </div>

            <!-- Choix du Module (Existant ou Nouveau) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label style="font-weight: 700; font-size: 13px; color: #334155; margin: 0;">Module Métier Associé <span style="color: #EF4444;">*</span></label>
                <button type="button" id="btn-toggle-module" onclick="toggleModuleInput()" style="background: none; border: none; color: #1E3A5F; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 0;">
                  <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i> + Créer un nouveau module
                </button>
              </div>

              <?php
                $dbConn = (new Database())->getCon();
                $existingModules = $dbConn->query("SELECT DISTINCT module_permission FROM permissions WHERE module_permission IS NOT NULL AND module_permission != '' ORDER BY module_permission ASC")->fetchAll(PDO::FETCH_COLUMN);
                $curMod = strtoupper($item['module_permission'] ?? 'ADMINISTRATION');
              ?>

              <!-- Cas 1 : Sélecteur de module existant -->
              <div id="bloc-select-module">
                <select class="form-control" name="module_permission" id="select_module_permission" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600;">
                  <?php foreach ($existingModules as $m): ?>
                    <option value="<?= htmlspecialchars($m) ?>" <?= ($curMod === strtoupper($m)) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($m) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Cas 2 : Champ texte pour taper un nouveau module -->
              <div id="bloc-text-module" style="display: none;">
                <input type="text" class="form-control" name="nouveau_module" id="input_nouveau_module" placeholder="Tapez le nom du nouveau module (ex: BIBLIOTHEQUE, STAGES...)" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid #3B82F6; background: #EFF6FF; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">
              </div>
              <small id="help-module" style="color: #64748B; font-size: 11px; margin-top: 4px; display: block;">Choisissez parmi les modules existants ou cliquez ci-dessus pour en créer un nouveau.</small>
            </div>

            <?php if ($isEdit): ?>
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
var isCustomModule = false;
function toggleModuleInput() {
  isCustomModule = !isCustomModule;
  if (isCustomModule) {
    $('#bloc-select-module').hide();
    $('#bloc-text-module').show();
    $('#input_nouveau_module').focus();
    $('#btn-toggle-module').html('<i data-lucide="list" style="width:14px;height:14px;"></i> Choisir un module existant');
    $('#help-module').text('Saisissez le nom en lettres majuscules (ex: BIBLIOTHEQUE).');
  } else {
    $('#bloc-text-module').hide();
    $('#input_nouveau_module').val('');
    $('#bloc-select-module').show();
    $('#btn-toggle-module').html('<i data-lucide="plus-circle" style="width:14px;height:14px;"></i> + Créer un nouveau module');
    $('#help-module').text('Choisissez parmi les modules existants ou cliquez ci-dessus pour en créer un nouveau.');
  }
  if (window.lucide) lucide.createIcons();
}

$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
