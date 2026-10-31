<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layers" style="width: 24px; height: 24px; color: #1E3A5F;"></i> <?= !empty($item['id_piece_cycle']) ? 'Modification Pièce de Cycle' : 'Configuration du Dossier de Pièces par Cycle' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            <?= !empty($item['id_piece_cycle']) ? 'Mise à jour des critères d\'exigence de la pièce pour ce cycle' : 'Sélectionnez les pièces administratives à exiger aux étudiants pour ce cycle académique' ?>
          </p>
        </div>
        <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
        </a>
      </div>

      <?php if (!empty($item['id_piece_cycle'])): ?>
        <!-- FORMULAIRE MODIFICATION UNITAIRE -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          <form action="<?= RACINE ?>piece_fournir_cycle/edit" method="POST" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
            <input type="hidden" name="id_piece_cycle" value="<?= $item['id_piece_cycle'] ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px; width: 100%;">
              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Cycle académique <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <select name="cycle_code" id="select_edit_cycle" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <?php foreach ($cycles as $cy): ?>
                    <option value="<?= $cy['code_cycle'] ?>" <?= ($item['cycle_code'] ?? '') === $cy['code_cycle'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cy['libelle_cycle']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Niveau d'étude <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <select name="niveau_code" id="select_edit_niveau" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="">-- Sélectionner un niveau d'étude --</option>
                  <?php 
                    $editNiveaux = !empty($item['cycle_code']) ? (new ModelNiveau())->getByCycle($item['cycle_code']) : ($niveaux ?? []);
                    foreach ($editNiveaux as $niv): 
                  ?>
                    <option value="<?= $niv['code_niveau'] ?>" <?= ($item['niveau_code'] ?? '') === $niv['code_niveau'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($niv['libelle_niveau']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Document / Pièce administrative <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <select name="piece_code" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <?php foreach ($pieces as $p): ?>
                    <option value="<?= $p['code_piece_fournir'] ?>" <?= ($item['piece_code'] ?? '') === $p['code_piece_fournir'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($p['libelle_piece']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Nombre d'exemplaires demandés <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <input type="number" min="1" step="1" name="nombre_exemplaires" value="<?= (int)($item['nombre_exemplaires'] ?? 1) ?>" required class="form-control" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Nature du document <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <select name="nature_document" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                  <option value="photocopie_simple" <?= ($item['nature_document'] ?? '') === 'photocopie_simple' ? 'selected' : '' ?>>Photocopie Simple</option>
                  <option value="photocopie_legalisee" <?= ($item['nature_document'] ?? '') === 'photocopie_legalisee' ? 'selected' : '' ?>>Photocopie Légalisée / Certifiée conforme</option>
                  <option value="original" <?= ($item['nature_document'] ?? '') === 'original' ? 'selected' : '' ?>>Original Requis</option>
                  <option value="numerique" <?= ($item['nature_document'] ?? '') === 'numerique' ? 'selected' : '' ?>>Fichier Numérique (Scan PDF)</option>
                  <option value="aucun" <?= ($item['nature_document'] ?? '') === 'aucun' ? 'selected' : '' ?>>Aucun</option>
                </select>
              </div>



              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
                <select name="statut_piece_cycle" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="actif" <?= ($item['statut_piece_cycle'] ?? '') === STATUTS::ACTIF ? 'selected' : '' ?>>Actif</option>
                  <option value="inactif" <?= ($item['statut_piece_cycle'] ?? '') === STATUTS::INACTIF ? 'selected' : '' ?>>Inactif</option>
                </select>
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
              <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</a>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer les modifications</button>
            </div>
          </form>
        </div>

      <?php else: ?>
        <!-- FORMULAIRE AJOUT RAPIDE / PANIER DE PIÈCES À FOURNIR PAR CYCLE -->
        <form action="<?= RACINE ?>piece_fournir_cycle/add" method="POST" id="form-bulk-piece-cycle" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <!-- Choix du Cycle & Niveau -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #CBD5E1; box-shadow: 0 2px 8px rgba(15,23,42,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Cycle Académique & Niveau de Destination
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%;">
              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Cycle ciblé <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <select name="cycle_code" id="select_target_cycle" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="">-- Sélectionner un cycle --</option>
                  <?php foreach ($cycles as $cy): ?>
                    <option value="<?= $cy['code_cycle'] ?>">
                      <?= htmlspecialchars($cy['libelle_cycle']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Niveau d'étude <span style="color: #EF4444; font-weight: 800;">*</span>
                </label>
                <select name="niveau_code" id="select_target_niveau" required disabled readonly class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%; background-color: #F8FAFC; color: #94A3B8; cursor: not-allowed;">
                  <option value="">-- Sélectionnez d'abord un cycle --</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Panier / Tableau Dynamique des Pièces de Cycle -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 2px 8px rgba(15,23,42,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid #EFF6FF;">
              <div>
                <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="folder-plus" style="width: 18px; height: 18px;"></i> Composition du Dossier Administratif
                </h3>
                <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">Sélectionnez les pièces à joindre obligatoirement au dossier d'inscription.</p>
              </div>
              <button type="button" id="btn-add-row-cycle" class="btn" style="background: #EFF6FF; color: #1E3A5F; border: 1.5px solid #BFDBFE; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> + Ajouter une pièce
              </button>
            </div>

            <!-- Dynamic Duplicate Warning Banner -->
            <div id="duplicate-warning-banner" style="display: none; background: #FEF2F2; border: 1.5px solid #FCA5A5; color: #991B1B; padding: 12px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; margin-bottom: 16px; align-items: center; gap: 10px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.08);">
              <i data-lucide="alert-triangle" style="width: 20px; height: 20px; flex-shrink: 0; color: #DC2626;"></i>
              <span id="duplicate-warning-text"></span>
            </div>

            <!-- Table des lignes -->
            <div style="width: 100%; overflow-x: auto;">
              <table class="table" id="table-bulk-cycle-items" style="width: 100%; border-collapse: collapse; margin: 0;">
                <thead>
                  <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <th style="padding: 10px 12px; width: 50%;">Pièce / Document à Fournir <span style="color: #EF4444; font-weight: 800;">*</span></th>
                    <th style="padding: 10px 12px; width: 15%; text-align: center;">Exemplaires</th>
                    <th style="padding: 10px 12px; width: 29%;">Nature du Document</th>
                    <th style="padding: 10px 12px; width: 6%; text-align: center;"></th>
                  </tr>
                </thead>
                <tbody id="bulk-rows-cycle-container">
                  <!-- Lignes ajoutées dynamiquement -->
                </tbody>
              </table>
            </div>

            <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
              <a href="<?= RACINE ?>piece_fournir/formulaire" target="_blank" style="font-size: 12.5px; font-weight: 700; color: #3B82F6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Créer une nouvelle pièce dans le répertoire &rarr;
              </a>
            </div>
          </div>

          <!-- Bottom Submit Bar -->
          <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px;">
            <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 12px 24px;">Annuler</a>
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; font-size: 14px; border-radius: 8px; padding: 12px 32px; box-shadow: 0 4px 12px rgba(30,58,95,0.25); display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Enregistrer le Dossier du Cycle
            </button>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </main>
</div>

<?php
$pieceFournirCycleEditConfig = [
  'pieceOptions' => array_map(function($p) {
    return [
      'code' => $p['code_piece_fournir'],
      'libelle' => $p['libelle_piece']
    ];
  }, $pieces ?? [])
];
?>
<script type="application/json" id="piece-fournir-cycle-edit-config"><?= json_encode($pieceFournirCycleEditConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<?php 
ViewContext::setScripts(['public/assets/js/modules/accessoires.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>
