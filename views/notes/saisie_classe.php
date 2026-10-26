<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$anneeCode = $anneeCode ?? ($_SESSION['annee_active_code'] ?? '');
$anneeLibelle = $anneeLibelle ?? ($_SESSION['annee_active_libelle'] ?? $anneeCode);
$classes = $classes ?? [];
$matieres = $matieres ?? [];
$semestres = $semestres ?? [];
$selectedClasseCode = $selectedClasseCode ?? '';
$selectedMatiereCode = $selectedMatiereCode ?? '';
$selectedSemestreCode = $selectedSemestreCode ?? '';
$selectedTypeEval = $selectedTypeEval ?? '';
$selectedCompositionCode = $selectedCompositionCode ?? '';
$selectedLibelleEval = $selectedLibelleEval ?? '';
$selectedCoefEval = $selectedCoefEval ?? '1.00';
$tousExamens = $tousExamens ?? [];
$etudiants = $etudiants ?? [];
$existingNotes = $existingNotes ?? [];
$compositionsProgrammees = $compositionsProgrammees ?? [];

$typeEvals = [
  'INTERROGATION' => 'Interrogation',
  'DEVOIR' => 'Devoir Sur Table',
  'EXAMEN' => 'Examen / Composition',
  'AUTRE' => 'Autre Évaluation'
];
?>
<style>
  .input-readonly {
    background-color: #F8FAFC !important;
    color: #1E3A5F !important;
    font-weight: 700 !important;
    cursor: not-allowed !important;
    border-color: #CBD5E1 !important;
  }
  .eval-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .input-valeur-note:focus {
    border-color: #1E3A5F !important;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15) !important;
    outline: none;
    background-color: #FFFFFF !important;
  }
  .row-absent {
    background-color: #FEF2F2 !important;
  }
  .row-dirty {
    background-color: #F0FDF4 !important;
  }
  .progress-track {
    background-color: #E2E8F0;
    border-radius: 999px;
    height: 8px;
    overflow: hidden;
    width: 100%;
  }
  .progress-fill {
    background: linear-gradient(90deg, #1E3A5F 0%, #16A34A 100%);
    height: 100%;
    border-radius: 999px;
    transition: width 0.3s ease;
  }
  .kbd-hint {
    background: #E2E8F0;
    color: #334155;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    font-family: monospace;
    border: 1px solid #CBD5E1;
  }
</style>

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
        <form method="GET" action="<?= RACINE ?>note/saisieClasse" id="filter-notes-form">
          
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
                  <span id="badge-semestre-lock" class="eval-badge" style="background: #F1F5F9; color: #64748B; <?= ($selectedTypeEval === 'EXAMEN') ? '' : 'display: none;' ?>">
                    <i data-lucide="lock" style="width: 11px; height: 11px;"></i> Auto
                  </span>
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
                  <span id="badge-matiere-count" class="eval-badge" style="background: #F1F5F9; color: #64748B;">
                    <?= count($matieres) ?> matière(s)
                  </span>
                </label>
                <select name="matiere_code" id="sel_matiere_code" class="form-control select2" required style="width: 100%;">
                  <option value="">-- <?= !empty($selectedClasseCode) ? 'Choisir la matière' : 'Choisir d\'abord une classe' ?> --</option>
                  <?php foreach ($matieres as $m): ?>
                    <?php 
                      $coefVal = isset($m['coefficient']) ? (float)$m['coefficient'] : 1.0;
                      $coefSuffix = " (Coef " . $coefVal . ")";
                    ?>
                    <option value="<?= htmlspecialchars($m['code_matiere']) ?>" 
                            data-coef="<?= htmlspecialchars($coefVal) ?>"
                            <?= ($selectedMatiereCode === $m['code_matiere']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($m['libelle_matiere']) ?><?= $coefSuffix ?>
                    </option>
                  <?php endforeach; ?>
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

      <!-- BANNIÈRE CONTEXTUELLE, KPIS ET TABLEAU DE SAISIE -->
      <?php if (!empty($selectedClasseCode) && !empty($selectedMatiereCode) && !empty($selectedSemestreCode)): ?>
        <?php
          $lblClasse = '';
          foreach ($classes as $c) { if ($c['code_classe'] === $selectedClasseCode) { $lblClasse = $c['libelle_classe']; break; } }
          $lblMatiere = '';
          foreach ($matieres as $m) { if ($m['code_matiere'] === $selectedMatiereCode) { $lblMatiere = $m['libelle_matiere']; break; } }
          $lblSemestre = '';
          foreach ($semestres as $s) { if ($s['code_semestre'] === $selectedSemestreCode) { $lblSemestre = $s['libelle_semestre']; break; } }
          
          $typeLabel = $typeEvals[$selectedTypeEval] ?? $selectedTypeEval;
          $finalLibelle = !empty($selectedLibelleEval) ? $selectedLibelleEval : $typeLabel;
        ?>

        <form id="form-save-batch-notes">
          <input type="hidden" name="classe_code" value="<?= htmlspecialchars($selectedClasseCode) ?>">
          <input type="hidden" name="matiere_code" value="<?= htmlspecialchars($selectedMatiereCode) ?>">
          <input type="hidden" name="semestre_code" value="<?= htmlspecialchars($selectedSemestreCode) ?>">
          <input type="hidden" name="type_evaluation_code" value="<?= htmlspecialchars($selectedTypeEval) ?>">
          <input type="hidden" name="composition_code" value="<?= htmlspecialchars($selectedCompositionCode) ?>">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <!-- Carte Résumé de l'Évaluation et KPIs en temps réel -->
          <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 16px;">
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span class="eval-badge" style="background: #1E3A5F; color: #FFFFFF; font-size: 12px;">
                  <i data-lucide="tag" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($typeLabel) ?>
                </span>
                <span style="font-weight: 800; font-size: 16px; color: #0F172A;">
                  <?= htmlspecialchars($finalLibelle) ?>
                </span>
                <span class="eval-badge" style="background: #DBEAFE; color: #1E40AF;">
                  Coef. <?= htmlspecialchars($selectedCoefEval ?: '1.00') ?>
                </span>
                <span style="color: #64748B; font-size: 13px;">| Classe : <strong style="color: #0F172A;"><?= htmlspecialchars($lblClasse) ?></strong></span>
                <span style="color: #64748B; font-size: 13px;">| Matière : <strong style="color: #0F172A;"><?= htmlspecialchars($lblMatiere) ?></strong></span>
                <span style="color: #64748B; font-size: 13px;">| <?= htmlspecialchars($lblSemestre) ?></span>
              </div>

              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="kbd-hint">Entrée</span>
                <span class="kbd-hint">↓</span>
                <span style="font-size: 12px; color: #64748B;">Suivant</span>
                <span class="kbd-hint" style="margin-left: 6px;">↑</span>
                <span style="font-size: 12px; color: #64748B;">Précédent</span>
                <span class="kbd-hint" style="margin-left: 6px;">ABS</span>
                <span style="font-size: 12px; color: #64748B;">Absent</span>
              </div>
            </div>

            <!-- Dashboard KPIs dynamiques de la grille -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; padding-top: 14px; border-top: 1px solid #F1F5F9;">
              
              <!-- Taux de saisie -->
              <div style="background: #F8FAFC; padding: 12px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 4px; text-transform: uppercase;">Progression Saisie</div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                  <strong id="stat-progress-count" style="font-size: 15px; color: #0F172A; font-weight: 800;">0 / <?= count($etudiants) ?></strong>
                  <span id="stat-progress-pct" class="eval-badge" style="background: #EFF6FF; color: #1D4ED8;">0%</span>
                </div>
                <div class="progress-track">
                  <div class="progress-fill" id="stat-progress-bar" style="width: 0%;"></div>
                </div>
              </div>

              <!-- Moyenne de classe -->
              <div style="background: #F8FAFC; padding: 12px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 4px; text-transform: uppercase;">Moyenne de classe</div>
                <strong id="stat-moyenne-classe" style="font-size: 18px; color: #1E3A5F; font-weight: 800;">- / 20</strong>
              </div>

              <!-- Plus forte note -->
              <div style="background: #F8FAFC; padding: 12px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 4px; text-transform: uppercase;">Plus Forte Note</div>
                <strong id="stat-note-max" style="font-size: 18px; color: #15803D; font-weight: 800;">-</strong>
              </div>

              <!-- Plus faible note -->
              <div style="background: #F8FAFC; padding: 12px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; margin-bottom: 4px; text-transform: uppercase;">Plus Faible Note</div>
                <strong id="stat-note-min" style="font-size: 18px; color: #B91C1C; font-weight: 800;">-</strong>
              </div>

            </div>
          </div>

          <!-- TABLEAU DE SAISIE DES NOTES -->
          <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
            
            <?php if (empty($etudiants)): ?>
              <div style="padding: 36px; text-align: center; color: #64748B;">
                <i data-lucide="users" style="width: 44px; height: 44px; stroke-width: 1.5; color: #94A3B8; margin-bottom: 10px;"></i>
                <p style="margin: 0; font-weight: 700; font-size: 14px; color: #334155;">Aucun étudiant inscrit trouvé dans la classe <?= htmlspecialchars($lblClasse) ?> pour l'année <?= htmlspecialchars($anneeLibelle) ?>.</p>
              </div>
            <?php else: ?>
              
              <!-- Barre d'outils d'assistance rapide -->
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
                <div style="font-size: 13px; font-weight: 700; color: #334155;">
                  Liste des étudiants (<?= count($etudiants) ?>)
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                  <button type="button" id="btn-quick-absent" class="btn btn-sm btn-outline-danger" style="border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="user-x" style="width: 14px; height: 14px;"></i> Marquer non notés comme Absents
                  </button>
                  <button type="button" id="btn-quick-fill" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="copy" style="width: 14px; height: 14px;"></i> Note uniforme
                  </button>
                  <button type="button" id="btn-quick-clear" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Réinitialiser
                  </button>
                </div>
              </div>

              <div style="width: 100%; overflow-x: auto;">
                <table class="table" id="table-notes-classe" style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; border-bottom: 2px solid #E2E8F0;">
                      <th style="padding: 12px 14px; width: 40px;">#</th>
                      <th style="padding: 12px 14px; width: 140px;">Matricule</th>
                      <th style="padding: 12px 14px;">Nom & Prénom Étudiant</th>
                      <th style="padding: 12px 14px; width: 140px; text-align: center;">Note / 20 <span style="color:#EF4444;">*</span></th>
                      <th style="padding: 12px 14px; width: 150px; text-align: center;">Appréciation</th>
                      <th style="padding: 12px 14px;">Observations / Remarques</th>
                      <th style="padding: 12px 14px; width: 80px; text-align: center;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($etudiants as $index => $e): 
                      $inscCode = $e['code_inscription'];
                      $note = $existingNotes[$inscCode] ?? [];
                      $valNote = isset($note['valeur_note']) ? (float)$note['valeur_note'] : '';
                      $obs = $note['observations'] ?? '';
                      $isAbsent = stripos($obs, 'absent') !== false;
                    ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;" class="note-row <?= $isAbsent ? 'row-absent' : '' ?>" data-insc="<?= htmlspecialchars($inscCode) ?>">
                        <td style="padding: 12px 14px; color: #94A3B8; font-weight: 700; font-size: 13px;"><?= $index + 1 ?></td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #1E3A5F; font-size: 13px;">
                          <?= htmlspecialchars($e['matricule_etudiant'] ?: '-') ?>
                        </td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #0F172A; font-size: 13px;">
                          <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                          <input type="text" inputmode="decimal" name="notes[<?= $inscCode ?>][valeur_note]" value="<?= $valNote !== '' ? htmlspecialchars($valNote) : '' ?>" placeholder="0 - 20" class="form-control form-control-sm input-valeur-note" style="border-radius: 8px; text-align: center; font-weight: 800; font-size: 14px; width: 100px; margin: 0 auto; border: 1.5px solid #CBD5E1; color: #0F172A;" data-original="<?= $valNote !== '' ? htmlspecialchars($valNote) : '' ?>">
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                          <span class="badge-appreciation eval-badge" style="background: #F1F5F9; color: #64748B;">
                            <?= $isAbsent ? 'Absent' : 'Non saisi' ?>
                          </span>
                        </td>
                        <td style="padding: 12px 14px;">
                          <input type="text" name="notes[<?= $inscCode ?>][observations]" value="<?= htmlspecialchars($obs) ?>" placeholder="Ajouter une remarque ou ABSENT..." class="form-control form-control-sm input-obs" style="border-radius: 8px; font-size: 12px; border: 1px solid #E2E8F0;" data-original="<?= htmlspecialchars($obs) ?>">
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                          <button type="button" class="btn btn-sm btn-link text-danger btn-toggle-absent" title="Basculer statut Absent" style="padding: 4px;">
                            <i data-lucide="user-minus" style="width: 15px; height: 15px;"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- Boutons de Sauvegarde Double Action -->
              <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; padding-top: 16px; border-top: 1px solid #F1F5F9;">
                <div style="color: #64748B; font-size: 13px;">
                  <i data-lucide="info" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Les notes vides ne sont pas enregistrées. Effacer une note existante la supprime du système.
                </div>

                <div style="display: flex; gap: 12px; align-items: center;">
                  <button type="button" id="btn-save-stay" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; padding: 11px 22px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(30,58,95,0.2);">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i> Enregistrer & Rester
                  </button>

                  <button type="button" id="btn-save-leave" class="btn btn-success" style="background: #16A34A; border-color: #16A34A; font-weight: 700; padding: 11px 24px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(22,163,74,0.25);">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Enregistrer & Quitter
                  </button>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </main>
</div>

<!-- SCRIPTS UX & ERGONOMIE AVANCÉE -->
<script>
window.COMPOSITIONS = <?= json_encode($compositionsProgrammees) ?>;
window.ALL_CLASSES = <?= json_encode(array_values(array_map(function($c) {
  return [
    'code_classe' => $c['code_classe'],
    'libelle_classe' => $c['libelle_classe']
  ];
}, $classes))) ?>;

var isFormDirty = false;
var isInitialLoading = true;

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // Rendu dynamique et intelligent des classes dans le select
  function renderClasseOptions(classList, selectedCode) {
    var $sel = $('#sel_classe_code');
    var currentVal = selectedCode || $sel.val();
    $sel.empty().append('<option value="">-- Choisir la classe --</option>');
    
    var valStillExists = false;
    classList.forEach(function(c) {
      var isSelected = (currentVal && currentVal === c.code_classe);
      if (isSelected) valStillExists = true;
      var opt = $('<option></option>')
        .attr('value', c.code_classe)
        .text(c.libelle_classe);
      if (isSelected) opt.prop('selected', true);
      $sel.append(opt);
    });

    if (!valStillExists && currentVal && !isInitialLoading) {
      $sel.val('');
    }
    $sel.trigger('change.select2');
  }

  // 1. Détection du Type d'Évaluation (Par défaut tous les autres champs sont invisibles)
  $('#sel_type_eval').on('change', function() {
    var typeEval = $(this).val();

    if (!typeEval) {
      $('#box-composition-select').slideUp(150);
      $('#box-params-fields').slideUp(150);
      $('#box-libelle-eval').slideUp(150);
      return;
    }

    if (typeEval === 'EXAMEN') {
      // A) Sélection Type EXAMEN : affiche les champs dédiés (examen programmé)
      $('#box-composition-select').slideDown(200);
      $('#box-libelle-eval').slideUp(150);

      var compCode = $('#sel_composition_code').val();
      if (!compCode) {
        // Invisible tant que l'examen programmé n'est pas choisi
        $('#box-params-fields').slideUp(150);
      } else {
        $('#box-params-fields').slideDown(200);
        $('#sel_composition_code').trigger('change');
      }
    } else {
      // B) Sélection Type INTERROGATION, DEVOIR ou AUTRE :
      // Masquer le bloc d'examen programmé
      $('#box-composition-select').slideUp(150);
      $('#sel_composition_code').val('').trigger('change.select2');

      // Déverrouiller Semestre & Coefficient
      $('#sel_semestre_code').prop('disabled', false);
      $('#badge-semestre-lock').hide();
      $('#input_coef_eval').prop('readonly', false).removeClass('input-readonly');
      $('#badge-coef-mode').text('Modifiable').css({ 'background': '#F1F5F9', 'color': '#475569' });

      // Restaurer toutes les classes
      var currentClasseVal = $('#sel_classe_code').val();
      renderClasseOptions(window.ALL_CLASSES, currentClasseVal);

      // Intitulé et affichage des 4 champs
      $('#box-libelle-eval').slideDown(150);
      if (!$('#input_libelle_eval').val()) {
        var defaultLib = (typeEval === 'DEVOIR') ? 'Devoir Sur Table N°1' : ((typeEval === 'INTERROGATION') ? 'Interrogation N°1' : 'Évaluation N°1');
        $('#input_libelle_eval').val(defaultLib);
      }

      // Afficher immédiatement les 4 champs dans l'ordre strict
      $('#box-params-fields').slideDown(200);
    }
  });

  // A) Sélection du Libellé Examen : remplit Semestre, Coefficient et filtre les Classes
  $('#sel_composition_code').on('change', function() {
    var compCode = $(this).val();
    if (!compCode) {
      if ($('#sel_type_eval').val() === 'EXAMEN') {
        $('#box-params-fields').slideUp(150);
      }
      return;
    }

    var $opt = $(this).find('option:selected');
    var lib = $opt.data('libelle');
    var coef = $opt.data('coef');
    var sem = $opt.data('semestre');
    var rawClasses = $opt.data('classes');
    var targetClasses = [];

    if (rawClasses) {
      try {
        targetClasses = (typeof rawClasses === 'string') ? JSON.parse(rawClasses) : rawClasses;
      } catch(e) {
        targetClasses = [];
      }
    }

    // 1- Remplir et verrouiller Semestre
    if (sem) {
      $('#sel_semestre_code').val(sem).trigger('change.select2');
      $('#semestre_code_hidden').val(sem);
      $('#sel_semestre_code').prop('disabled', true);
      $('#badge-semestre-lock').show();
    }

    // 1- Remplir et verrouiller Coefficient
    if (coef) {
      $('#input_coef_eval').val(coef).prop('readonly', true).addClass('input-readonly');
      $('#badge-coef-mode').text('Lecture Seule').css({ 'background': '#FEF3C7', 'color': '#B45309' });
    }

    if (lib) {
      $('#input_libelle_eval').val(lib);
    }

    // 1- Filtrer les classes concernées par cet examen
    var classesToRender = (targetClasses && targetClasses.length > 0) ? targetClasses : window.ALL_CLASSES;
    var currentClasse = isInitialLoading ? '<?= htmlspecialchars($selectedClasseCode) ?>' : '';
    renderClasseOptions(classesToRender, currentClasse);

    if (!isInitialLoading) {
      // Réinitialiser la matière
      $('#sel_matiere_code').empty().append('<option value="">-- Choisir d\'abord une classe --</option>').trigger('change.select2');
      $('#badge-matiere-count').text('0 matière(s)');
    }

    // Révéler le bloc des 4 champs
    $('#box-params-fields').slideDown(200);
  });

  // 2- La sélection de classe remplit le champ Matière (via API)
  $('#sel_classe_code').on('change', function() {
    var classeCode = $(this).val();
    var $matiereSel = $('#sel_matiere_code');

    if (!classeCode) {
      $matiereSel.empty().append('<option value="">-- Choisir d\'abord une classe --</option>');
      $('#badge-matiere-count').text('0 matière(s)');
      $matiereSel.trigger('change.select2');
      return;
    }

    var typeEval = $('#sel_type_eval').val();
    var compCode = (typeEval === 'EXAMEN') ? $('#sel_composition_code').val() : '';

    $matiereSel.empty().append('<option value="">Chargement des matières...</option>').trigger('change.select2');

    $.ajax({
      url: '<?= RACINE ?>note/getMatieresByClasseApi',
      type: 'GET',
      data: {
        classe_code: classeCode,
        composition_code: compCode
      },
      dataType: 'json',
      success: function(res) {
        $matiereSel.empty().append('<option value="">-- Choisir la matière --</option>');
        var items = (res && res.data) ? res.data : [];
        $('#badge-matiere-count').text(items.length + ' matière(s)');

        var preselectedMatiere = isInitialLoading ? '<?= htmlspecialchars($selectedMatiereCode) ?>' : '';

        items.forEach(function(m) {
          var coef = m.coefficient ? parseFloat(m.coefficient) : 1.0;
          var prof = m.prof_nom ? ' [' + m.prof_nom + ']' : '';
          var isSel = (preselectedMatiere && preselectedMatiere === m.code_matiere);
          var opt = $('<option></option>')
            .attr('value', m.code_matiere)
            .attr('data-coef', coef)
            .text(m.libelle_matiere + ' (Coef ' + coef + ')' + prof);
          if (isSel) opt.prop('selected', true);
          $matiereSel.append(opt);
        });

        $matiereSel.trigger('change.select2');
      },
      error: function() {
        $matiereSel.empty().append('<option value="">-- Erreur de chargement --</option>').trigger('change.select2');
      }
    });
  });

  // 3- La sélection Matière affiche la liste des étudiants de la classe
  $('#sel_matiere_code').on('change', function() {
    if (isInitialLoading) return;

    var matiereCode = $(this).val();
    if (!matiereCode) return;

    var typeEval = $('#sel_type_eval').val();
    var opt = $(this).find('option:selected');
    var coef = opt.data('coef');

    // Mise à jour automatique du coefficient si hors-examen
    if (typeEval !== 'EXAMEN' && coef) {
      $('#input_coef_eval').val(coef);
    }

    // Vérifier si le semestre est choisi
    var semCode = $('#sel_semestre_code').val() || $('#semestre_code_hidden').val();
    if (!semCode) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Semestre requis',
          text: 'Veuillez sélectionner le semestre avant d\'afficher la liste des étudiants.',
          confirmButtonColor: '#1E3A5F'
        }).then(function() {
          $('#sel_semestre_code').select2('open');
        });
      } else {
        alert('Veuillez sélectionner le semestre.');
        $('#sel_semestre_code').focus();
      }
      return;
    }

    var classeCode = $('#sel_classe_code').val();
    if (!classeCode) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Classe requise',
          text: 'Veuillez sélectionner une classe.',
          confirmButtonColor: '#1E3A5F'
        });
      }
      return;
    }

    // Afficher l'animation de chargement
    $('#loading-matiere-grid').slideDown(150);

    // Déverrouiller temporairement les champs verrouillés pour la soumission GET
    $('#sel_semestre_code').prop('disabled', false);
    $('#input_coef_eval').prop('readonly', false);

    // Soumettre le formulaire pour afficher la liste des étudiants
    $('#filter-notes-form').submit();
  });

  // Support sélection Semestre après choix de la matière (Cas B)
  $('#sel_semestre_code').on('change', function() {
    if (isInitialLoading) return;
    var semVal = $(this).val();
    $('#semestre_code_hidden').val(semVal);

    var classeCode = $('#sel_classe_code').val();
    var matiereCode = $('#sel_matiere_code').val();

    if (semVal && classeCode && matiereCode) {
      $('#loading-matiere-grid').slideDown(150);
      $('#sel_semestre_code').prop('disabled', false);
      $('#input_coef_eval').prop('readonly', false);
      $('#filter-notes-form').submit();
    }
  });

  // Avant toute soumission de formulaire, déverrouiller pour inclure tous les champs en GET
  $('#filter-notes-form').on('submit', function() {
    $('#sel_semestre_code').prop('disabled', false);
    $('#input_coef_eval').prop('readonly', false);
  });

  // Initialisation à l'arrivée sur la page
  var initialType = '<?= htmlspecialchars($selectedTypeEval) ?>';
  var initialComp = '<?= htmlspecialchars($selectedCompositionCode) ?>';
  var initialClasse = '<?= htmlspecialchars($selectedClasseCode) ?>';

  if (initialType === 'EXAMEN') {
    $('#box-composition-select').show();
    if (initialComp) {
      var $selCompOpt = $('#sel_composition_code option:selected');
      var rawClasses = $selCompOpt.data('classes');
      var targetClasses = [];
      if (rawClasses) {
        try {
          targetClasses = (typeof rawClasses === 'string') ? JSON.parse(rawClasses) : rawClasses;
        } catch(e) {}
      }
      var classesToRender = (targetClasses && targetClasses.length > 0) ? targetClasses : window.ALL_CLASSES;
      renderClasseOptions(classesToRender, initialClasse);
      $('#sel_semestre_code').prop('disabled', true);
      $('#badge-semestre-lock').show();
      $('#input_coef_eval').prop('readonly', true).addClass('input-readonly');
      $('#badge-coef-mode').text('Lecture Seule').css({ 'background': '#FEF3C7', 'color': '#B45309' });
      $('#box-params-fields').show();
    }
  } else if (initialType) {
    $('#box-composition-select').hide();
    renderClasseOptions(window.ALL_CLASSES, initialClasse);
    $('#sel_semestre_code').prop('disabled', false);
    $('#badge-semestre-lock').hide();
    $('#input_coef_eval').prop('readonly', false).removeClass('input-readonly');
    $('#badge-coef-mode').text('Modifiable').css({ 'background': '#F1F5F9', 'color': '#475569' });
    $('#box-params-fields').show();
    $('#box-libelle-eval').show();
  }

  isInitialLoading = false;

  // 5. Calcul dynamique des appréciations et KPIs en direct (Zero Hardcoding)
  function getAppreciation(val, isAbsent) {
    if (isAbsent) return { text: 'Absent', bg: '#FEE2E2', color: '#B91C1C' };
    if (isNaN(val) || val === '' || val === null) return { text: 'Non saisi', bg: '#F1F5F9', color: '#64748B' };
    val = parseFloat(val);
    if (val >= 16) return { text: 'Très Bien', bg: '#DCFCE7', color: '#15803D' };
    if (val >= 14) return { text: 'Bien', bg: '#E0F2FE', color: '#0369A1' };
    if (val >= 12) return { text: 'Assez Bien', bg: '#FEF3C7', color: '#B45309' };
    if (val >= 10) return { text: 'Passable', bg: '#FFEDD5', color: '#C2410C' };
    return { text: 'Insuffisant', bg: '#FEE2E2', color: '#B91C1C' };
  }

  function updateDynamicStats() {
    var sum = 0;
    var count = 0;
    var totalStudents = $('.note-row').length;
    var maxVal = null;
    var minVal = null;

    $('.input-valeur-note').each(function() {
      var rawVal = $(this).val().trim();
      var $row = $(this).closest('tr');
      var $obsInput = $row.find('.input-obs');
      var obsText = $obsInput.val().trim();
      var isAbsent = $row.hasClass('row-absent') || (obsText.toLowerCase().indexOf('absent') !== -1);

      // Support saisie rapide "a", "abs", "ABS"
      if (rawVal.toLowerCase() === 'a' || rawVal.toLowerCase() === 'abs' || rawVal.toLowerCase() === 'absent') {
        $(this).val('0');
        $obsInput.val('ABSENT');
        $row.addClass('row-absent');
        isAbsent = true;
        rawVal = '0';
      }

      var normalizedVal = rawVal.replace(',', '.');
      var numericVal = parseFloat(normalizedVal);

      var $badge = $row.find('.badge-appreciation');
      var app = getAppreciation(rawVal !== '' ? numericVal : '', isAbsent);
      $badge.text(app.text).css({ 'background': app.bg, 'color': app.color });

      if (rawVal !== '' && !isNaN(numericVal)) {
        if (!isAbsent) {
          sum += numericVal;
          count++;
          if (maxVal === null || numericVal > maxVal) maxVal = numericVal;
          if (minVal === null || numericVal < minVal) minVal = numericVal;
        } else {
          // Absent comptabilisé comme noté
          count++;
        }
      } else if (isAbsent) {
        count++;
      }
    });

    // Progression
    var pct = totalStudents > 0 ? Math.round((count / totalStudents) * 100) : 0;
    $('#stat-progress-count').text(count + ' / ' + totalStudents);
    $('#stat-progress-pct').text(pct + '%');
    $('#stat-progress-bar').css('width', pct + '%');

    // Moyenne, Min, Max
    if (count > 0 && sum > 0) {
      var moy = (sum / count).toFixed(2);
      $('#stat-moyenne-classe').text(moy + ' / 20');
    } else {
      $('#stat-moyenne-classe').text('- / 20');
    }

    $('#stat-note-max').text(maxVal !== null ? maxVal.toFixed(2) + ' / 20' : '-');
    $('#stat-note-min').text(minVal !== null ? minVal.toFixed(2) + ' / 20' : '-');
  }

  // 6. Navigation ergonomique au clavier (Enter, ArrowDown, ArrowUp)
  $(document).on('keydown', '.input-valeur-note', function(e) {
    var $inputs = $('.input-valeur-note');
    var idx = $inputs.index(this);

    if (e.key === 'Enter' || e.key === 'ArrowDown') {
      e.preventDefault();
      if (idx + 1 < $inputs.length) {
        $inputs.eq(idx + 1).focus().select();
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (idx - 1 >= 0) {
        $inputs.eq(idx - 1).focus().select();
      }
    }
  });

  $(document).on('input change', '.input-valeur-note, .input-obs', function() {
    isFormDirty = true;
    $('#badge-unsaved').show();
    var $row = $(this).closest('tr');
    $row.addClass('row-dirty');
    updateDynamicStats();
  });

  updateDynamicStats();

  // 7. Bouton bascule statut absent par ligne
  $(document).on('click', '.btn-toggle-absent', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var $noteInput = $row.find('.input-valeur-note');
    var $obsInput = $row.find('.input-obs');

    if ($row.hasClass('row-absent')) {
      $row.removeClass('row-absent');
      if ($obsInput.val() === 'ABSENT') $obsInput.val('');
    } else {
      $row.addClass('row-absent');
      $noteInput.val('0');
      $obsInput.val('ABSENT');
    }
    isFormDirty = true;
    $('#badge-unsaved').show();
    updateDynamicStats();
  });

  // 8. Outils rapides d'assistance
  $('#btn-quick-absent').on('click', function() {
    $('.note-row').each(function() {
      var $val = $(this).find('.input-valeur-note');
      var $obs = $(this).find('.input-obs');
      if ($val.val().trim() === '') {
        $val.val('0');
        $obs.val('ABSENT');
        $(this).addClass('row-absent row-dirty');
      }
    });
    isFormDirty = true;
    $('#badge-unsaved').show();
    updateDynamicStats();
    if (window.toastr) toastr.info('Étudiants sans note marqués comme Absents.');
  });

  $('#btn-quick-fill').on('click', function() {
    var noteVal = prompt('Entrez la note uniforme à attribuer aux étudiants non notés (entre 0 et 20) :');
    if (noteVal !== null && noteVal.trim() !== '') {
      var num = parseFloat(noteVal.replace(',', '.'));
      if (isNaN(num) || num < 0 || num > 20) {
        alert('Veuillez saisir une note valide entre 0 et 20.');
        return;
      }
      $('.note-row').each(function() {
        var $val = $(this).find('.input-valeur-note');
        if ($val.val().trim() === '') {
          $val.val(num.toFixed(2));
          $(this).addClass('row-dirty');
        }
      });
      isFormDirty = true;
      $('#badge-unsaved').show();
      updateDynamicStats();
    }
  });

  $('#btn-quick-clear').on('click', function() {
    if (confirm('Voulez-vous réinitialiser toutes les modifications non enregistrées ?')) {
      $('.note-row').each(function() {
        var $val = $(this).find('.input-valeur-note');
        var $obs = $(this).find('.input-obs');
        $val.val($val.data('original'));
        $obs.val($obs.data('original'));
        $(this).removeClass('row-dirty');
        if (($obs.val() || '').toLowerCase().indexOf('absent') === -1) {
          $(this).removeClass('row-absent');
        }
      });
      isFormDirty = false;
      $('#badge-unsaved').hide();
      updateDynamicStats();
    }
  });

  // 9. Enregistrement AJAX (Enregistrer & Rester vs Enregistrer & Quitter)
  function executeSave(shouldRedirect) {
    var $form = $('#form-save-batch-notes');
    var $btnSaveStay = $('#btn-save-stay');
    var $btnSaveLeave = $('#btn-save-leave');

    $btnSaveStay.prop('disabled', true);
    $btnSaveLeave.prop('disabled', true);

    var targetBtn = shouldRedirect ? $btnSaveLeave : $btnSaveStay;
    targetBtn.html('<i data-lucide="loader" class="spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: '<?= RACINE ?>note/saveBatch',
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(res) {
        $btnSaveStay.prop('disabled', false).html('<i data-lucide="save"></i> Enregistrer & Rester');
        $btnSaveLeave.prop('disabled', false).html('<i data-lucide="check-circle"></i> Enregistrer & Quitter');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          isFormDirty = false;
          $('#badge-unsaved').hide();
          
          var now = new Date();
          var timeStr = now.toLocaleTimeString();
          $('#last-saved-time').text('Dernière sauvegarde : ' + timeStr);

          if (res.composition_code) {
            $('input[name="composition_code"]').val(res.composition_code);
            $('#sel_composition_code').val(res.composition_code);
          }

          // Mettre à jour les données originales pour chaque champ
          $('.note-row').each(function() {
            var $val = $(this).find('.input-valeur-note');
            var $obs = $(this).find('.input-obs');
            $val.data('original', $val.val());
            $obs.data('original', $obs.val());
            $(this).removeClass('row-dirty');
          });

          if (window.toastr) toastr.success(res.message || 'Notes enregistrées avec succès !');

          if (shouldRedirect) {
            setTimeout(function() {
              window.location.href = '<?= RACINE ?>note/list';
            }, 800);
          }
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement des notes');
        }
      },
      error: function(xhr) {
        $btnSaveStay.prop('disabled', false).html('<i data-lucide="save"></i> Enregistrer & Rester');
        $btnSaveLeave.prop('disabled', false).html('<i data-lucide="check-circle"></i> Enregistrer & Quitter');
        if (window.lucide) lucide.createIcons();
        if (window.toastr) toastr.error('Erreur technique lors de la communication avec le serveur.');
      }
    });
  }

  $('#btn-save-stay').on('click', function(e) {
    e.preventDefault();
    executeSave(false);
  });

  $('#btn-save-leave').on('click', function(e) {
    e.preventDefault();
    executeSave(true);
  });

  $('#form-save-batch-notes').on('submit', function(e) {
    e.preventDefault();
    executeSave(false);
  });

  // Protection contre fermeture accidentelle si modifications non enregistrées
  window.addEventListener('beforeunload', function(e) {
    if (isFormDirty) {
      e.preventDefault();
      e.returnValue = 'Des modifications n\'ont pas été enregistrées.';
      return e.returnValue;
    }
  });

});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
