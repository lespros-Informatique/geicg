<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$anneeCode = $anneeCode ?? (Session::get('annee_active_code', ''));
$anneeLibelle = $anneeLibelle ?? (Session::get('annee_active_libelle') ?? $anneeCode);
$matieres = $matieres ?? [];
$selectedTypeEval = $selectedTypeEval ?? '';
$selectedCompositionCode = $selectedCompositionCode ?? '';
$selectedLibelleEval = $selectedLibelleEval ?? '';
$selectedCoefEval = $selectedCoefEval ?? '1.00';
$tousExamens = $tousExamens ?? [];
$existingNotes = $existingNotes ?? [];
$compositionsProgrammees = $compositionsProgrammees ?? [];

$typeEvals = [
  'INTERROGATION' => 'Interrogation',
  'DEVOIR' => 'Devoir Sur Table',
  'EXAMEN' => 'Examen / Composition',
  'AUTRE' => 'Autre Évaluation'
];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <a href="<?= RACINE ?>note/list" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 700;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Registre
            </a>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="edit-3" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
              Saisie des Notes & Évaluations
            </h1>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Sélectionnez la classe et la matière, puis saisissez rapidement les notes avec navigation au clavier</p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
          <span id="badge-unsaved" class="eval-badge" style="background: #FEF3C7; color: #92400E; display: none;">
            <i data-lucide="alert-triangle" style="width: 12px; height: 12px;"></i> Modifications non enregistrées
          </span>
          <span id="last-saved-time" style="font-size: 12px; color: #64748B; font-weight: 600;"></span>
        </div>
      </div>

      <!-- FORMULAIRE DE SÉLECTION INTELLIGENT & DYNAMIQUE -->
      <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <form method="POST" action="javascript:void(0);" id="filter-notes-form" onsubmit="return false;">
          
          <!-- LIGNE 1 : Année Académique & Type d'Évaluation -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; align-items: start;">
            
            <!-- 1. Année Académique (Readonly) -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Année Académique</span>
              </label>
              <input type="text" class="form-control input-readonly" value="<?= htmlspecialchars($anneeLibelle) ?>" readonly style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px;">
              <input type="hidden" name="annee_code" value="<?= htmlspecialchars($anneeCode) ?>">
            </div>

            <!-- 2. Type d'Évaluation -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">
                Type d'Évaluation <span style="color:#EF4444;">*</span>
              </label>
              <select name="type_evaluation_code" id="sel_type_eval" class="form-control select2" required style="width: 100%;">
                <option value="">-- Choisir le type d'évaluation --</option>
                <?php foreach ($typeEvals as $code => $lbl): ?>
                  <option value="<?= htmlspecialchars($code) ?>" <?= ($selectedTypeEval === $code) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lbl) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

          </div>

          <!-- CAS A : BLOC DÉDIÉ AUX EXAMENS (Visible uniquement si Type == EXAMEN) -->
          <div id="box-composition-select" style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed #E2E8F0; <?= ($selectedTypeEval === 'EXAMEN') ? 'display: block;' : 'display: none;' ?>">
            <div class="form-group">
              <label style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="award" style="width: 16px; height: 16px; color: #0284C7;"></i> Examen / Composition Programmée <span style="color:#EF4444;">*</span>
              </label>
              <select name="composition_code" id="sel_composition_code" class="form-control select2" style="width: 100%;">
                <option value="">-- Sélectionner l'examen programmé --</option>
                <?php foreach ($tousExamens as $exam): ?>
                  <?php 
                    $coef = !empty($exam['coefficient']) ? ' (Coef ' . floatval($exam['coefficient']) . ')' : '';
                    $isSel = ($selectedCompositionCode === $exam['code_composition']);
                    $jsonClasses = htmlspecialchars(json_encode($exam['target_classes'] ?? []), ENT_QUOTES, 'UTF-8');
                  ?>
                  <option value="<?= htmlspecialchars($exam['code_composition']) ?>" 
                          data-libelle="<?= htmlspecialchars($exam['libelle_composition']) ?>"
                          data-coef="<?= htmlspecialchars($exam['coefficient']) ?>"
                          data-semestre="<?= htmlspecialchars($exam['semestre_code']) ?>"
                          data-classes='<?= $jsonClasses ?>'
                          <?= $isSel ? 'selected' : '' ?>>
                    [Examen] <?= htmlspecialchars($exam['libelle_composition']) ?> <?= $coef ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span id="msg-no-composition" style="font-size: 12px; color: #DC2626; margin-top: 4px; display: none; font-weight: 600;">
                <i data-lucide="alert-circle" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Aucun examen programmé trouvé pour cette année.
              </span>
            </div>
          </div>

          <!-- BLOC DES 4 CHAMPS : ORDRE STRICT DEMANDÉ -->
          <!-- 1- Semestre - Coefficient - Classe (champ select) - Matière (champ select) -->
          <?php
            $isParamsVisible = (!empty($selectedTypeEval) && ($selectedTypeEval !== 'EXAMEN' || !empty($selectedCompositionCode)));
          ?>
          <div id="box-params-fields" style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed #E2E8F0; <?= $isParamsVisible ? 'display: block;' : 'display: none;' ?>">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; align-items: start;">
              
              <!-- 1. Semestre -->
              <div class="form-group">
                <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                  <span> Semestre <span style="color:#EF4444;">*</span></span>
                 
                </label>
                <select name="semestre_code" id="sel_semestre_code" class="form-control select2" required style="width: 100%;">
                  <option value="">-- Choisir le semestre --</option>
                  <?php foreach ($semestres as $s): ?>
                    <option value="<?= htmlspecialchars($s['code_semestre']) ?>" <?= ($selectedSemestreCode === $s['code_semestre']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['libelle_semestre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" name="semestre_code_hidden" id="semestre_code_hidden" value="<?= htmlspecialchars($selectedSemestreCode) ?>">
              </div>

              <!-- 2. Coefficient -->
              <div class="form-group">
                <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                  <span> Coefficient <span style="color:#EF4444;">*</span></span>
                  
                </label>
                <input type="number" step="0.25" min="0.5" max="10" name="coefficient" id="input_coef_eval" 
                       value="<?= htmlspecialchars($selectedCoefEval ?: '1.00') ?>" 
                       class="form-control <?= ($selectedTypeEval === 'EXAMEN') ? 'input-readonly' : '' ?>" 
                       <?= ($selectedTypeEval === 'EXAMEN') ? 'readonly' : '' ?>
                       style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 800; color: #1E40AF;">
              </div>

              <!-- 3. Classe (champ select) -->
              <div class="form-group">
                <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">
                   Classe <span style="color:#EF4444;">*</span>
                </label>
                <select name="classe_code" id="sel_classe_code" class="form-control select2" required style="width: 100%;">
                  <option value="">-- Choisir la classe --</option>
                  <?php foreach ($classes as $c): ?>
                    <option value="<?= htmlspecialchars($c['code_classe']) ?>" <?= ($selectedClasseCode === $c['code_classe']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['libelle_classe']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- 4. Matière (champ select) -->
              <div class="form-group">
                <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                  <span> Matière <span style="color:#EF4444;">*</span></span>
                 
                </label>
                <select name="matiere_code" id="sel_matiere_code" class="form-control select2" required style="width: 100%;">
                  <option value="">-- <?= !empty($selectedClasseCode) ? 'Choisir la matière' : 'Choisir d\'abord une classe' ?> --</option>
                  <?php if (!empty($selectedClasseCode) && !empty($matieres)): ?>
                    <?php foreach ($matieres as $m): ?>
                      <?php 
                        $coefVal = isset($m['coefficient']) ? (float)$m['coefficient'] : 1.0;
                        $coefSuffix = " (Coef " . $coefVal . ")";
                        $profSuffix = !empty($m['prof_nom']) ? " [" . htmlspecialchars($m['prof_nom']) . "]" : "";
                      ?>
                      <option value="<?= htmlspecialchars($m['code_matiere']) ?>" 
                              data-coef="<?= htmlspecialchars($coefVal) ?>"
                              <?= ($selectedMatiereCode === $m['code_matiere']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['libelle_matiere']) ?><?= $coefSuffix ?><?= $profSuffix ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

            </div>

            <!-- Champ Intitulé pour Interrogation / Devoir / Autre -->
            <div id="box-libelle-eval" style="margin-top: 14px; <?= (empty($selectedTypeEval) || $selectedTypeEval === 'EXAMEN') ? 'display: none;' : 'display: block;' ?>">
              <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                Intitulé / Libellé de l'Évaluation
              </label>
              <input type="text" name="libelle_eval" id="input_libelle_eval" value="<?= htmlspecialchars($selectedLibelleEval) ?>" placeholder="Ex: Interrogation N°1, Devoir Surveillé..." class="form-control" style="width: 100%; padding: 8px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
            </div>

            <!-- Indicateur de chargement automatique lors du choix de matière -->
            <div id="loading-matiere-grid" style="display: none; margin-top: 14px; padding: 12px; background: #EFF6FF; border-radius: 8px; border: 1px solid #BFDBFE; text-align: center; color: #1D4ED8; font-weight: 700; font-size: 13px;">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              Chargement de la liste des étudiants de la classe...
            </div>

          </div>

        </form>
      </div>

      <!-- BANNIÈRE CONTEXTUELLE, KPIS ET TABLEAU DE SAISIE CHARGÉS DYNAMIQUEMENT EN AJAX -->
      <div id="box-grille-notes-container">
        <?php if (!empty($selectedClasseCode) && !empty($selectedMatiereCode) && !empty($selectedSemestreCode)): ?>
          <?php include __DIR__ . '/partials/grille_notes_table.php'; ?>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- SCRIPTS UX & ERGONOMIE AVANCÉE -->
<script type="application/json" id="notes-saisie-classe-config">
<?= json_encode([
    'compositions' => $compositionsProgrammees ?? [],
    'allClasses' => array_values(array_map(function($c) {
        return [
            'code_classe' => $c['code_classe'],
            'libelle_classe' => $c['libelle_classe']
        ];
    }, $classes ?? [])),
    'selectedTypeEval' => $selectedTypeEval ?? '',
    'selectedCompositionCode' => $selectedCompositionCode ?? '',
    'selectedClasseCode' => $selectedClasseCode ?? '',
    'selectedMatiereCode' => $selectedMatiereCode ?? ''
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/notes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
