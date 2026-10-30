<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$item = $item ?? [];
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$filieres = $filieres ?? [];
$classes = $classes ?? [];
$matieres = $matieres ?? [];
$activeAnneeCode = $activeAnneeCode ?? Session::get('annee_active_code', '');
$semestres = $semestres ?? [];
$salles = $salles ?? [];
$teachersWithUsers = $teachersWithUsers ?? [];
$activeAnneeLibelle = '';
foreach ($annees as $a) {
    if ($a['code_annee'] === $activeAnneeCode) {
        $activeAnneeLibelle = $a['libelle_annee'];
        break;
    }
}
if (empty($activeAnneeLibelle) && !empty($annees)) {
    $activeAnneeCode = $annees[0]['code_annee'];
    $activeAnneeLibelle = $annees[0]['libelle_annee'];
}

$selectedClasseCode = $item['classe_code'] ?? ($selectedClasseCode ?? '');
$selectedMatiereCode = $item['matiere_code'] ?? ($selectedMatiereCode ?? '');
$selectedType = $item['type_composition'] ?? ($selectedTypeEval ?? 'COMPOSITION');
$isEditMode = !empty($item['id_composition']);
$existingNiveaux = $existingNiveaux ?? [];
if (empty($existingNiveaux)) {
    $existingNiveaux = [['niveau_code' => '', 'filiere_codes' => []]];
}
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="award" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            <?= $isEditMode ? 'Éditer la ' : 'Programmer une ' ?> Composition / Examen
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Définition de l'épreuve, sélection multi-niveaux & filières, coefficients et créneaux horaires</p>
        </div>
        <a href="<?= RACINE ?>composition/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour au registre
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>composition/<?= $isEditMode ? 'edit' : 'add' ?>" method="POST" style="width: 100%;" id="form-composition">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEditMode): ?>
            <input type="hidden" name="id_composition" value="<?= $item['id_composition'] ?>">
          <?php endif; ?>

          <!-- BLOC 1: PARAMÈTRES GÉNÉRAUX -->
          <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed #E2E8F0;">
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="settings" style="width: 18px; height: 18px;"></i> 1. Paramètres Généraux de l'Épreuve
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              
              <!-- Année Académique (Readonly Select) -->
              <div class="form-group">
                <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  <span>Année Académique <span style="color: #EF4444;">*</span></span>
                  <span style="font-size: 11px; background: #EFF6FF; color: #1D4ED8; padding: 2px 8px; border-radius: 6px; font-weight: 700;">Active</span>
                </label>
                <input type="hidden" name="annee_code" value="<?= htmlspecialchars($activeAnneeCode) ?>">
                <select class="form-control" disabled style="width: 100%; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; font-weight: 700; cursor: not-allowed;">
                  <?php foreach ($annees as $a): ?>
                    <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($activeAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Type d'Épreuve -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Type d'Épreuve <span style="color: #EF4444;">*</span>
                </label>
                <?php 
                  $currentType = $item['type_composition'] ?? 'EXAMEN';
                ?>
                <select class="form-control select2" name="type_composition" required style="width: 100%;">
                  <option value="EXAMEN" <?= ($currentType === 'EXAMEN') ? 'selected' : '' ?>>Examen / Composition Officielle</option>
                  <option value="DEVOIR" <?= ($currentType === 'DEVOIR') ? 'selected' : '' ?>>Devoir Sur Table</option>
                  <option value="INTERROGATION" <?= ($currentType === 'INTERROGATION') ? 'selected' : '' ?>>Interrogation</option>
                  <option value="AUTRE" <?= ($currentType === 'AUTRE') ? 'selected' : '' ?>>Autre Évaluation</option>
                </select>
              </div>

              <!-- Semestre Académique (Selectable) -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Semestre Académique <span style="color: #EF4444;">*</span>
                </label>
                <select class="form-control select2" name="semestre_code" required style="width: 100%;">
                  <option value="">-- Choisir le semestre --</option>
                  <?php foreach($semestres as $s): ?>
                    <option value="<?= htmlspecialchars($s['code_semestre']) ?>" <?= (($item['semestre_code'] ?? '') === $s['code_semestre']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['libelle_semestre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Coefficient de l'épreuve -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Coefficient de l'Épreuve <span style="color: #EF4444;">*</span>
                </label>
                <input type="number" step="0.25" min="0.5" max="10" class="form-control" name="coefficient" value="<?= htmlspecialchars($item['coefficient'] ?? '2.00') ?>" required style="width: 100%; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 800; color: #1E40AF;">
              </div>

              <!-- Date de l'épreuve (Début) -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Date de l'Épreuve <span style="color: #EF4444;">*</span>
                </label>
                <input type="date" class="form-control" name="date_composition" id="date_composition" value="<?= htmlspecialchars($item['date_composition'] ?? date('Y-m-d')) ?>" required style="width: 100%; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700; color: #0F172A;">
              </div>

              <!-- Date fin de l'épreuve -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Date fin de l'Épreuve <span style="color: #EF4444;">*</span>
                </label>
                <input type="date" class="form-control" name="date_fin_composition" id="date_fin_composition" value="<?= htmlspecialchars($item['date_fin_composition'] ?? ($item['date_composition'] ?? date('Y-m-d'))) ?>" required style="width: 100%; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700; color: #0F172A;">
              </div>

              <!-- Intitulé de l'épreuve -->
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Intitulé de l'Épreuve <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" class="form-control" name="libelle_composition" value="<?= htmlspecialchars($item['libelle_composition'] ?? '') ?>" placeholder="Ex: Composition du 1er Semestre - Algo, Examen Blanc BTS 2026..." required style="width: 100%; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700; color: #0F172A;">
              </div>

            </div>
          </div>

          <!-- BLOC 2: SELECTION PAR NIVEAUX & FILIERES DYNAMIQUES -->
          <div style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div>
                <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="layers" style="width: 18px; height: 18px;"></i> 2. Niveaux & Filières
                </h3>
                <p style="color: #64748B; font-size: 12px; margin: 2px 0 0 0;">Sélectionnez un ou plusieurs niveaux et leurs filières d'études rattachées.</p>
              </div>
              <button type="button" id="btn-add-niveau-row" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Niveau
              </button>
            </div>

            <div id="niveaux-container" style="display: flex; flex-direction: column; gap: 16px;">
              <?php foreach ($existingNiveaux as $idx => $rowNiv): ?>
                <?php
                  $rowNivCode = $rowNiv['niveau_code'] ?? '';
                  $rowFilCodes = $rowNiv['filiere_codes'] ?? [];
                  $isNewRow = empty($rowNivCode) && empty($rowFilCodes);
                ?>
                <!-- Card Niveau Row <?= $idx ?> -->
                <div class="niveau-row-card" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 20px; position: relative;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-weight: 800; font-size: 13px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                      <i data-lucide="bookmark" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Groupe Niveau #<span class="niveau-row-num"><?= $idx + 1 ?></span>
                    </span>
                    <button type="button" class="btn-remove-niveau-row" style="background: transparent; border: none; color: #EF4444; font-size: 12px; font-weight: 700; cursor: pointer; display: <?= count($existingNiveaux) > 1 ? 'inline-flex' : 'none' ?>; align-items: center; gap: 4px;">
                      <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Retirer ce niveau
                    </button>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; align-items: start;">
                    <!-- Select Niveau -->
                    <div>
                      <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px;">
                        Niveau d'Études <span style="color: #EF4444;">*</span>
                      </label>
                      <select class="form-control sel-niveau-item" name="niveaux[<?= $idx ?>][niveau_code]" required style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
                        <option value="">-- Choisir un niveau --</option>
                        <?php foreach($niveaux as $n): ?>
                          <option value="<?= htmlspecialchars($n['code_niveau']) ?>" <?= ($rowNivCode === $n['code_niveau']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($n['libelle_niveau']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filières du Niveau -->
                    <div>
                      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 12px; color: #334155; margin: 0;">
                          Filières Concernées <span style="color: #64748B; font-weight: 400;">(Toutes si non coché)</span>
                        </label>
                        <span style="font-size: 11px;">
                          <a href="#" class="btn-toggle-all-filieres" style="color: #1D4ED8; font-weight: 700; text-decoration: none;">Tout cocher</a>
                        </span>
                      </div>
                      <div class="filieres-checkbox-group" style="display: flex; flex-wrap: wrap; gap: 8px; background: #FFFFFF; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; max-height: 120px; overflow-y: auto;">
                        <?php foreach($filieres as $f): ?>
                          <?php 
                            $isChecked = $isNewRow || in_array($f['code_filiere'], $rowFilCodes, true);
                          ?>
                          <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #334155; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="niveaux[<?= $idx ?>][filiere_codes][]" value="<?= htmlspecialchars($f['code_filiere']) ?>" class="chk-filiere-item" <?= $isChecked ? 'checked' : '' ?> style="accent-color: #1E3A5F;">
                            <?= htmlspecialchars($f['libelle_filiere']) ?>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>

                  <!-- Dynamic Classes Impacted Preview -->
                  <div class="classes-preview-box" style="margin-top: 12px; font-size: 12px; color: #475569; display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
                    <span style="font-weight: 700; color: #0F172A;"><i data-lucide="check-circle" style="width: 14px; height: 14px; color: #16A34A; vertical-align: text-bottom;"></i> Classes concernées :</span>
                    <span class="preview-badges-list" style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" id="btn-submit-composition" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; font-weight: 700; border-radius: 8px; padding: 11px 26px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);">
              <i data-lucide="save" style="width: 16px; height: 16px; vertical-align: text-bottom; margin-right: 4px;"></i> Enregistrer la Programmation
            </button>
            <a href="<?= RACINE ?>composition/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<!-- Raw data for JS class filtering -->
<script type="application/json" id="compositions-edit-config">
<?= json_encode([
    'allClasses' => $classes ?? [],
    'allFilieres' => $filieres ?? [],
    'allNiveaux' => $niveaux ?? [],
    'edtActiveTargets' => $edtActiveTargets ?? []
]) ?>
</script>
<?php
$pageScripts = ['public/assets/js/modules/compositions_edit.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
