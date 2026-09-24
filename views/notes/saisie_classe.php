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
$selectedTypeEval = $selectedTypeEval ?? 'INTERROGATION';
$selectedCompositionCode = $selectedCompositionCode ?? '';
$selectedLibelleEval = $selectedLibelleEval ?? '';
$selectedCoefEval = $selectedCoefEval ?? '1.00';
$etudiants = $etudiants ?? [];
$existingNotes = $existingNotes ?? [];
$compositionsProgrammees = $compositionsProgrammees ?? [];

$typeEvals = [
  'INTERROGATION' => 'Interrogation',
  'DEVOIR' => 'Devoir Sur Table',
  'EXAMEN' => 'Examen / Composition'
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
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Sélectionnez les critères d'évaluation et saisissez les notes par classe</p>
        </div>
      </div>

      <!-- FORMULAIRE DE SÉLECTION INTELLIGENT & DYNAMIQUE -->
      <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <form method="GET" action="<?= RACINE ?>note/saisieClasse" id="filter-notes-form">
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; align-items: start;">
            
            <!-- 1. Année Académique (Readonly) -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Année Académique</span>
                <span class="eval-badge" style="background: #EFF6FF; color: #1D4ED8;">Active</span>
              </label>
              <input type="text" class="form-control input-readonly" value="<?= htmlspecialchars($anneeLibelle) ?>" readonly style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px;">
              <input type="hidden" name="annee_code" value="<?= htmlspecialchars($anneeCode) ?>">
            </div>

            <!-- 2. Type d'Évaluation (Select Statique) -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">
                Type d'Évaluation <span style="color:#EF4444;">*</span>
              </label>
              <select name="type_evaluation_code" id="sel_type_eval" class="form-control select2" required style="width: 100%;">
                <?php foreach ($typeEvals as $code => $lbl): ?>
                  <option value="<?= htmlspecialchars($code) ?>" <?= ($selectedTypeEval === $code) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lbl) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- 3. Classe -->
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

            <!-- 4. Matière -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">
                Matière <span style="color:#EF4444;">*</span>
              </label>
              <select name="matiere_code" id="sel_matiere_code" class="form-control select2" required style="width: 100%;">
                <option value="">-- Choisir la matière --</option>
                <?php foreach ($matieres as $m): ?>
                  <option value="<?= htmlspecialchars($m['code_matiere']) ?>" <?= ($selectedMatiereCode === $m['code_matiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['libelle_matiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- 5. Semestre -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Semestre <span style="color:#EF4444;">*</span></span>
                <span id="badge-semestre-lock" class="eval-badge" style="background: #F1F5F9; color: #64748B; display: none;">
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
              <!-- Hidden input to submit semestre when select is disabled -->
              <input type="hidden" name="semestre_code_hidden" id="semestre_code_hidden" value="<?= htmlspecialchars($selectedSemestreCode) ?>">
            </div>

          </div>

          <!-- BLOC CONDITIONNEL DÉDIÉ AUX EXAMENS & COMPOSITIONS -->
          <div id="box-composition-select" style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed #E2E8F0; <?= ($selectedTypeEval === 'EXAMEN') ? 'display: block;' : 'display: none;' ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; align-items: center;">
              <div style="grid-column: 1 / -1;">
                <label style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="award" style="width: 16px; height: 16px; color: #0284C7;"></i> Épreuve / Composition Programmée <span style="color:#EF4444;">*</span>
                </label>
                <select name="composition_code" id="sel_composition_code" class="form-control select2" style="width: 100%;">
                  <option value="">-- Sélectionner l'épreuve programmée --</option>
                  <?php foreach ($compositionsProgrammees as $comp): ?>
                    <?php 
                      $dComp = !empty($comp['date_composition']) ? date('d/m/Y', strtotime($comp['date_composition'])) : '';
                      $coef = !empty($comp['coefficient']) ? ' (Coef ' . floatval($comp['coefficient']) . ')' : '';
                      $isSel = ($selectedCompositionCode === $comp['code_composition']);
                    ?>
                    <option value="<?= htmlspecialchars($comp['code_composition']) ?>" 
                            data-libelle="<?= htmlspecialchars($comp['libelle_composition']) ?>"
                            data-coef="<?= htmlspecialchars($comp['coefficient']) ?>"
                            data-semestre="<?= htmlspecialchars($comp['semestre_code']) ?>"
                            <?= $isSel ? 'selected' : '' ?>>
                      [Examen] <?= htmlspecialchars($comp['libelle_composition']) ?> <?= !empty($dComp) ? "- $dComp" : '' ?> <?= $coef ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span id="msg-no-composition" style="font-size: 12px; color: #DC2626; margin-top: 4px; display: none; font-weight: 600;">
                  <i data-lucide="alert-circle" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Aucune composition programmée trouvée pour cette classe et matière.
                </span>
              </div>
            </div>
          </div>

          <!-- BLOC CHAMPS LIBELLÉ & COEFFICIENT (ÉDITABLES OU READONLY DÉPENDANT DU TYPE) -->
          <div style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed #E2E8F0; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; align-items: end;">
            
            <!-- Libellé de l'Évaluation -->
            <div class="form-group" style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Intitulé / Libellé de l'Évaluation</span>
                <span id="badge-libelle-mode" class="eval-badge" style="background: #F1F5F9; color: #475569;">Modifiable</span>
              </label>
              <input type="text" name="libelle_eval" id="input_libelle_eval" value="<?= htmlspecialchars($selectedLibelleEval) ?>" placeholder="Ex: Interrogation N°1, Devoir Surveillé..." class="form-control" style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
            </div>

            <!-- Coefficient -->
            <div class="form-group">
              <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Coefficient</span>
                <span id="badge-coef-mode" class="eval-badge" style="background: #F1F5F9; color: #475569;">Modifiable</span>
              </label>
              <input type="number" step="0.25" min="0.5" max="10" name="coefficient" id="input_coef_eval" value="<?= htmlspecialchars($selectedCoefEval ?: '1.00') ?>" class="form-control" style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 800; color: #1E40AF;">
            </div>

            <!-- Bouton Afficher Grille -->
            <div>
              <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; font-weight: 700; border-radius: 8px; width: 100%; padding: 11px; box-shadow: 0 4px 10px rgba(30,58,95,0.25); display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <i data-lucide="filter" style="width: 16px; height: 16px;"></i> Afficher la Grille
              </button>
            </div>
          </div>

        </form>
      </div>

      <!-- BANNIÈRE CONTEXTUELLE ET TABLEAU DE SAISIE -->
      <?php if (!empty($selectedClasseCode) && !empty($selectedMatiereCode) && !empty($selectedSemestreCode)): ?>
        <?php
          // Libellé de classe & matière pour affichage propre
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

          <!-- Carte Résumé de l'Évaluation -->
          <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
              <span class="eval-badge" style="background: #1E3A5F; color: #FFFFFF; font-size: 12px;">
                <i data-lucide="tag" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($typeLabel) ?>
              </span>
              <span style="font-weight: 800; font-size: 15px; color: #0F172A;">
                <?= htmlspecialchars($finalLibelle) ?>
              </span>
              <span class="eval-badge" style="background: #DBEAFE; color: #1E40AF;">
                Coef. <?= htmlspecialchars($selectedCoefEval ?: '1.00') ?>
              </span>
              <span style="color: #64748B; font-size: 13px;">| Classe : <strong style="color: #0F172A;"><?= htmlspecialchars($lblClasse) ?></strong></span>
              <span style="color: #64748B; font-size: 13px;">| Matière : <strong style="color: #0F172A;"><?= htmlspecialchars($lblMatiere) ?></strong></span>
              <span style="color: #64748B; font-size: 13px;">| <?= htmlspecialchars($lblSemestre) ?></span>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; background: #FFFFFF; padding: 8px 16px; border-radius: 8px; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
              <span style="font-size: 12px; font-weight: 700; color: #64748B;">Moyenne de classe :</span>
              <strong id="stat-moyenne-classe" style="font-size: 16px; color: #1E3A5F; font-weight: 800;">- / 20</strong>
            </div>
          </div>

          <!-- TABLEAU DE SAISIE DES NOTES -->
          <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
            
            <?php if (empty($etudiants)): ?>
              <div style="padding: 36px; text-align: center; color: #64748B;">
                <i data-lucide="users" style="width: 44px; height: 44px; stroke-width: 1.5; color: #94A3B8; margin-bottom: 10px;"></i>
                <p style="margin: 0; font-weight: 700; font-size: 14px; color: #334155;">Aucun étudiant inscrit trouvé dans la classe <?= htmlspecialchars($lblClasse) ?>.</p>
              </div>
            <?php else: ?>
              <div style="width: 100%; overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; border-bottom: 2px solid #E2E8F0;">
                      <th style="padding: 12px 14px; width: 40px;">#</th>
                      <th style="padding: 12px 14px; width: 140px;">Matricule</th>
                      <th style="padding: 12px 14px;">Nom & Prénom Étudiant</th>
                      <th style="padding: 12px 14px; width: 150px; text-align: center;">Note / 20 <span style="color:#EF4444;">*</span></th>
                      <th style="padding: 12px 14px; width: 150px; text-align: center;">Appréciation</th>
                      <th style="padding: 12px 14px;">Observations / Remarques</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($etudiants as $index => $e): 
                      $inscCode = $e['code_inscription'];
                      $note = $existingNotes[$inscCode] ?? [];
                      $valNote = isset($note['valeur_note']) ? (float)$note['valeur_note'] : '';
                    ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;" class="note-row">
                        <td style="padding: 12px 14px; color: #94A3B8; font-weight: 700; font-size: 13px;"><?= $index + 1 ?></td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #1E3A5F; font-size: 13px;">
                          <?= htmlspecialchars($e['matricule_etudiant'] ?: '-') ?>
                        </td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #0F172A; font-size: 13px;">
                          <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                          <input type="number" step="0.25" min="0" max="20" name="notes[<?= $inscCode ?>][valeur_note]" value="<?= $valNote !== '' ? htmlspecialchars($valNote) : '' ?>" placeholder="Note / 20" class="form-control form-control-sm input-valeur-note" style="border-radius: 8px; text-align: center; font-weight: 800; font-size: 14px; width: 110px; margin: 0 auto; border: 1.5px solid #CBD5E1; color: #0F172A;">
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                          <span class="badge-appreciation eval-badge" style="background: #F1F5F9; color: #64748B;">
                            Non saisi
                          </span>
                        </td>
                        <td style="padding: 12px 14px;">
                          <input type="text" name="notes[<?= $inscCode ?>][observations]" value="<?= htmlspecialchars($note['observations'] ?? '') ?>" placeholder="Ajouter une remarque..." class="form-control form-control-sm" style="border-radius: 8px; font-size: 12px; border: 1px solid #E2E8F0;">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="submit" class="btn btn-success btn-lg" style="background: #16A34A; border-color: #16A34A; font-weight: 700; padding: 12px 28px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(22,163,74,0.25);">
                  <i data-lucide="save" style="width: 20px; height: 20px;"></i> Enregistrer les Notes de la Classe
                </button>
              </div>
            <?php endif; ?>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </main>
</div>

<!-- Raw Compositions JSON for instant client-side lookup -->
<script>
window.COMPOSITIONS = <?= json_encode($compositionsProgrammees) ?>;

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  function handleTypeEvalChange() {
    var typeEval = $('#sel_type_eval').val();
    
    if (typeEval === 'EXAMEN') {
      $('#box-composition-select').slideDown(200);
      
      // Mode Examen : Libellé et coef verrouillés / gérés par l'épreuve
      var selectedCompCode = $('#sel_composition_code').val();
      var selectedOption = $('#sel_composition_code option:selected');
      
      if (selectedCompCode && selectedOption.length) {
        var lib = selectedOption.data('libelle');
        var coef = selectedOption.data('coef');
        var sem = selectedOption.data('semestre');

        if (lib) $('#input_libelle_eval').val(lib);
        if (coef) $('#input_coef_eval').val(coef);
        if (sem) {
          $('#sel_semestre_code').val(sem).trigger('change.select2');
          $('#semestre_code_hidden').val(sem);
        }
      }

      $('#input_libelle_eval').prop('readonly', true).addClass('input-readonly');
      $('#input_coef_eval').prop('readonly', true).addClass('input-readonly');
      $('#badge-libelle-mode').text('Lecture Seule').css({ 'background': '#FEF3C7', 'color': '#B45309' });
      $('#badge-coef-mode').text('Lecture Seule').css({ 'background': '#FEF3C7', 'color': '#B45309' });
      $('#badge-semestre-lock').show();

    } else {
      // Mode Interrogation / Devoir : Champs Libellé, Coef et Semestre modifiables
      $('#box-composition-select').slideUp(200);
      
      $('#input_libelle_eval').prop('readonly', false).removeClass('input-readonly');
      $('#input_coef_eval').prop('readonly', false).removeClass('input-readonly');
      
      if ($('#input_libelle_eval').val() === '' || $('#input_libelle_eval').val().indexOf('Composition') !== -1) {
        $('#input_libelle_eval').val(typeEval === 'DEVOIR' ? 'Devoir Sur Table N°1' : 'Interrogation N°1');
      }
      if ($('#input_coef_eval').val() === '' || $('#input_coef_eval').val() === '2.00') {
        $('#input_coef_eval').val('1.00');
      }

      $('#badge-libelle-mode').text('Modifiable').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#badge-coef-mode').text('Modifiable').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#badge-semestre-lock').hide();
    }
  }

  // Event handler on Type Evaluation change
  $('#sel_type_eval').on('change', function() {
    handleTypeEvalChange();
  });

  // Event handler on Composition Selection change
  $('#sel_composition_code').on('change', function() {
    var $opt = $(this).find('option:selected');
    if ($opt.length && $opt.val()) {
      var lib = $opt.data('libelle');
      var coef = $opt.data('coef');
      var sem = $opt.data('semestre');

      if (lib) $('#input_libelle_eval').val(lib);
      if (coef) $('#input_coef_eval').val(coef);
      if (sem) {
        $('#sel_semestre_code').val(sem).trigger('change.select2');
        $('#semestre_code_hidden').val(sem);
      }
    }
  });

  // Ajax dynamic load of compositions when Classe or Matiere changes
  function fetchCompositionsAjax() {
    var classeCode = $('#sel_classe_code').val();
    var matiereCode = $('#sel_matiere_code').val();
    var typeEval = $('#sel_type_eval').val();

    if (typeEval !== 'EXAMEN' || !classeCode || !matiereCode) return;

    $.ajax({
      url: '<?= RACINE ?>composition/getByClasseMatiereApi',
      type: 'GET',
      data: { classe_code: classeCode, matiere_code: matiereCode },
      dataType: 'json',
      success: function(res) {
        if (res && res.data) {
          var $select = $('#sel_composition_code');
          $select.empty().append('<option value="">-- Sélectionner l\'épreuve programmée --</option>');
          
          if (res.data.length === 0) {
            $('#msg-no-composition').show();
          } else {
            $('#msg-no-composition').hide();
            res.data.forEach(function(comp) {
              var dComp = comp.date_composition ? comp.date_composition : '';
              var coef = comp.coefficient ? ' (Coef ' + parseFloat(comp.coefficient) + ')' : '';
              var optHtml = '<option value="' + comp.code_composition + '" data-libelle="' + comp.libelle_composition + '" data-coef="' + comp.coefficient + '" data-semestre="' + comp.semestre_code + '">[Examen] ' + comp.libelle_composition + ' ' + coef + '</option>';
              $select.append(optHtml);
            });
          }
          $select.trigger('change.select2');
        }
      }
    });
  }

  $('#sel_classe_code, #sel_matiere_code').on('change', function() {
    fetchCompositionsAjax();
  });

  // Initial trigger
  handleTypeEvalChange();

  // Appréciations & Live Class Average calculation
  function getAppreciation(val) {
    if (isNaN(val) || val === '') return { text: 'Non saisi', bg: '#F1F5F9', color: '#64748B' };
    val = parseFloat(val);
    if (val >= 16) return { text: 'Très Bien', bg: '#DCFCE7', color: '#15803D' };
    if (val >= 14) return { text: 'Bien', bg: '#E0F2FE', color: '#0369A1' };
    if (val >= 12) return { text: 'Assez Bien', bg: '#FEF3C7', color: '#B45309' };
    if (val >= 10) return { text: 'Passable', bg: '#FFEDD5', color: '#C2410C' };
    return { text: 'Insuffisant', bg: '#FEE2E2', color: '#B91C1C' };
  }

  function updateRowAppreciations() {
    var sum = 0;
    var count = 0;

    $('.input-valeur-note').each(function() {
      var val = $(this).val();
      var $badge = $(this).closest('tr').find('.badge-appreciation');
      var app = getAppreciation(val);
      $badge.text(app.text).css({ 'background': app.bg, 'color': app.color });

      if (val !== '' && !isNaN(val)) {
        sum += parseFloat(val);
        count++;
      }
    });

    if (count > 0) {
      var moy = (sum / count).toFixed(2);
      $('#stat-moyenne-classe').text(moy + ' / 20');
    } else {
      $('#stat-moyenne-classe').text('- / 20');
    }
  }

  $('.input-valeur-note').on('input change', function() {
    updateRowAppreciations();
  });
  updateRowAppreciations();

  // Submit form batch notes
  $('#form-save-batch-notes').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: '<?= RACINE ?>note/saveBatch',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Notes enregistrées avec succès !');
          setTimeout(function() { window.location.href = '<?= RACINE ?>note/list'; }, 1000);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          $btn.prop('disabled', false).html('<i data-lucide="save"></i> Enregistrer les Notes de la Classe');
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur de connexion au serveur');
        $btn.prop('disabled', false).html('<i data-lucide="save"></i> Enregistrer les Notes de la Classe');
        if (window.lucide) lucide.createIcons();
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
