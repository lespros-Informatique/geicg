<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$accessoires = $accessoires ?? [];
$cycles = $cycles ?? [];
$niveaux = $niveaux ?? [];
$filieres = $filieres ?? [];
$filiereCyclesMap = $filiereCyclesMap ?? [];

$filiereToCycles = [];
$niveauToCycles = [];
foreach ($filiereCyclesMap as $fc) {
    $cCode = $fc['cycle_code'] ?? '';
    $fCode = $fc['filiere_code'] ?? '';
    $nCode = $fc['niveau_code'] ?? '';
    if (!empty($fCode) && !empty($cCode) && !in_array($cCode, $filiereToCycles[$fCode] ?? [], true)) {
        $filiereToCycles[$fCode][] = $cCode;
    }
    if (!empty($nCode) && !empty($cCode) && !in_array($cCode, $niveauToCycles[$nCode] ?? [], true)) {
        $niveauToCycles[$nCode][] = $cCode;
    }
}
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layers" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Kits - Filière & Niveau
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des kits et accessoires.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <?php if (!isset($canAccess) || $canAccess([PERMISSIONS::MANAGE_ACCESSOIRES, PERMISSIONS::VIEW_ACCESSOIRES, PERMISSIONS::CONFIG_ACADEMIQUE, PERMISSIONS::MANAGE_INSCRIPTIONS])): ?>
          <a href="<?= url('accessoire/imprimerCiblesPdf') ?>" target="_blank" id="btn-print-cibles-pdf" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;" title="Imprimer la liste des kits exigés par filière et niveau en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </a>
          <?php endif; ?>
          <?php if (!isset($canAccess) || $canAccess([PERMISSIONS::MANAGE_ACCESSOIRES, PERMISSIONS::CONFIG_ACADEMIQUE])): ?>
          <button type="button" class="btn btn-primary btn-add-acc-cible" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Configurer Kits Filière / Niveau
          </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Navigation Tabs (Kits par Filière & Niveau vs Répertoire des Kits) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px; flex-wrap: wrap;">
        <a href="<?= url('accessoire/cibles') ?>" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 17px; height: 17px;"></i> Kits Exigés par Filière & Niveau
        </a>
        <a href="<?= url('accessoire/list') ?>" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="package" style="width: 17px; height: 17px;"></i> Répertoire des Kits & Articles
        </a>
      </div>

      <!-- Filtres par Cycle, Filière Spécifique & Niveau d'étude -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
          <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Filtrer par Cycle</label>
            <select id="filter-cible-cycle" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les cycles --</option>
              <?php foreach ($cycles as $cyc): ?>
                <option value="<?= htmlspecialchars($cyc['code_cycle']) ?>">
                  <?= htmlspecialchars(!empty($cyc['slug_cycle']) ? $cyc['slug_cycle'] . ' - ' . $cyc['libelle_cycle'] : $cyc['libelle_cycle']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="flex: 1; min-width: 240px;">
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Filtrer par Filière / Spécialité</label>
            <select id="filter-cible-filiere-code" class="form-control select2" disabled style="width: 100%; background: #F1F5F9; color: #64748B; cursor: not-allowed;">
              <option value="">-- Sélectionnez d'abord un cycle --</option>
            </select>
          </div>
          <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Filtrer par Niveau d'étude</label>
            <select id="filter-cible-niveau" class="form-control select2" disabled style="width: 100%; background: #F1F5F9; color: #64748B; cursor: not-allowed;">
              <option value="">-- Sélectionnez d'abord un cycle --</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-accessoires-cibles" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <th style="padding: 12px 14px; width: 50px;">#</th>
                <th style="padding: 12px 14px;">Kit / Accessoire</th>
                <th style="padding: 12px 14px;">Cycle d'étude</th>
                <th style="padding: 12px 14px;">Filière / Spécialité</th>
                <th style="padding: 12px 14px;">Niveau d'étude</th>
                <th style="padding: 12px 14px; width: 100px;" class="text-center">Statut</th>
                <th style="padding: 12px 5px; text-align: center; width: 50px;">Actions</th>
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
<!-- MODAL INTERACTIVE : CONFIGURER / ASSOCIER KITS PAR FILIÈRE ET NIVEAU -->
<!-- ========================================================================= -->
<div id="modal-accessoire-cible" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-accessoire-cible-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Configurer Kits par Filière & Niveau
      </h3>
      <button type="button" class="btn-close-modal-acc-cible" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-accessoire-cible" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_accessoire_niveau_filiere" id="acc_cible_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Kit(s) / Accessoire(s) du Répertoire <span style="color: #EF4444;">*</span>
        </label>
        <div id="acc_cible_kits_container" style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 12px; background: #F8FAFC; display: flex; flex-direction: column; gap: 8px; max-height: 160px; overflow-y: auto;">
          <?php if (!empty($accessoires)): ?>
            <?php foreach ($accessoires as $acc): ?>
              <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #0F172A; cursor: pointer; margin: 0;">
                <input type="checkbox" name="accessoire_codes[]" class="chk-cible-acc" value="<?= htmlspecialchars($acc['code_accessoire']) ?>" style="width: 16px; height: 16px; accent-color: #1E3A5F; cursor: pointer;">
                <span><?= htmlspecialchars($acc['libelle_accessoire']) ?></span>
              </label>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="font-size: 12.5px; color: #64748B;">
              Aucun kit enregistré dans le répertoire. <a href="<?= url('accessoire/list') ?>" style="color: #1E3A5F; font-weight: 700;">Ajouter d'abord des kits au répertoire</a>.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Cycle d'étude <span style="color: #EF4444;">*</span>
        </label>
        <select name="cycle_code" id="acc_cible_cycle" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="">-- Sélectionner un cycle d'étude --</option>
          <?php foreach ($cycles as $cyc): ?>
            <option value="<?= htmlspecialchars($cyc['code_cycle']) ?>">
              <?= htmlspecialchars(!empty($cyc['slug_cycle']) ? $cyc['slug_cycle'] . ' - ' . $cyc['libelle_cycle'] : $cyc['libelle_cycle']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Sélectionnez un cycle pour activer et charger ses filières et niveaux associés</small>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Filière(s) / Spécialité(s) Cible(s)
        </label>
        <select name="filiere_code" id="acc_cible_filiere_code" disabled class="form-control select2" style="width: 100%;">
          <option value="">-- Sélectionnez d'abord un cycle --</option>
        </select>
        <div id="acc_cible_filiere_empty" style="display: none; font-size: 12px; color: #B45309; font-weight: 600; margin-top: 4px;">
          Aucune filière associée à ce cycle dans la table pivot.
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 22px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Niveau(x) d'étude Cible(s)
        </label>
        <select name="niveau_code" id="acc_cible_niveau_code" disabled class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F1F5F9; color: #64748B; cursor: not-allowed; font-weight: 600; font-size: 14px;">
          <option value="">-- Sélectionnez d'abord un cycle --</option>
        </select>
        <div id="acc_cible_niveau_empty" style="display: none; font-size: 12px; color: #B45309; font-weight: 600; margin-top: 4px;">
          Aucun niveau associé à ce cycle dans la table pivot.
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-acc-cible" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-acc-cible" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
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

