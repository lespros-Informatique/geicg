<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$item = $item ?? [];
$annees = $annees ?? (new ModelAnnee())->getAll();
$niveaux = $niveaux ?? (new ModelNiveau())->getAll();
$filieres = $filieres ?? (new ModelFiliere())->getAll();
$classes = $classes ?? (new ModelClasse())->getAll();
$matieres = $matieres ?? (new ModelMatiere())->getAll();
$activeAnneeCode = $activeAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
$semestres = $semestres ?? (new ModelSemestre())->getAll($activeAnneeCode);
$salles = $salles ?? (new ModelSalle())->getAll();

$dbConn = (new Database())->getCon();
if (empty($teachersWithUsers)) {
    $teachersWithUsers = $dbConn->query("
        SELECT e.code_enseignant, CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_complet
        FROM enseignants e
        JOIN users u ON u.code_user = e.code_enseignant
        ORDER BY u.nom_user ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$activeAnneeCode = $activeAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
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

              <!-- Date de l'épreuve -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Date de l'Épreuve <span style="color: #EF4444;">*</span>
                </label>
                <input type="date" class="form-control" name="date_composition" value="<?= htmlspecialchars($item['date_composition'] ?? date('Y-m-d')) ?>" required style="width: 100%; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700; color: #0F172A;">
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
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; font-weight: 700; border-radius: 8px; padding: 11px 26px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);">
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
<script>
window.ALL_CLASSES = <?= json_encode($classes) ?>;
window.ALL_FILIERES = <?= json_encode($filieres) ?>;
window.ALL_NIVEAUX = <?= json_encode($niveaux) ?>;
window.EDT_ACTIVE_TARGETS = <?= json_encode($edtActiveTargets ?? []) ?>;

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // Active Niveaux & Filières mapping from emplois_temps
  var edtActiveNiveaux = [];
  var edtActiveFiliereMap = {}; // niveau_code -> array of filiere_codes

  if (window.EDT_ACTIVE_TARGETS && window.EDT_ACTIVE_TARGETS.length > 0) {
    window.EDT_ACTIVE_TARGETS.forEach(function(item) {
      if (item.niveau_code) {
        if (edtActiveNiveaux.indexOf(item.niveau_code) === -1) {
          edtActiveNiveaux.push(item.niveau_code);
        }
        if (!edtActiveFiliereMap[item.niveau_code]) {
          edtActiveFiliereMap[item.niveau_code] = [];
        }
        if (item.filiere_code && edtActiveFiliereMap[item.niveau_code].indexOf(item.filiere_code) === -1) {
          edtActiveFiliereMap[item.niveau_code].push(item.filiere_code);
        }
      }
    });
  }

  function filterFilieresForNiveauCard($card) {
    var nivCode = $card.find('.sel-niveau-item').val();
    var $checkboxGroup = $card.find('.filieres-checkbox-group');

    if (!nivCode) {
      $checkboxGroup.find('label').show();
      $checkboxGroup.find('.chk-filiere-item').prop('disabled', false);
      return;
    }

    if (edtActiveNiveaux.length > 0) {
      var activeFiliereCodes = edtActiveFiliereMap[nivCode] || [];
      $checkboxGroup.find('.chk-filiere-item').each(function() {
        var fCode = $(this).val();
        var $label = $(this).closest('label');
        if (activeFiliereCodes.indexOf(fCode) !== -1) {
          $label.show();
          $(this).prop('disabled', false);
        } else {
          $label.hide();
          $(this).prop('checked', false);
          $(this).prop('disabled', true);
        }
      });
    } else {
      var validFiliereCodes = window.ALL_CLASSES.filter(function(c) {
        return c.niveau_code === nivCode;
      }).map(function(c) { return c.filiere_code; });

      $checkboxGroup.find('.chk-filiere-item').each(function() {
        var fCode = $(this).val();
        var $label = $(this).closest('label');
        if (validFiliereCodes.indexOf(fCode) !== -1 || validFiliereCodes.length === 0) {
          $label.show();
          $(this).prop('disabled', false);
        } else {
          $label.hide();
          $(this).prop('checked', false);
          $(this).prop('disabled', true);
        }
      });
    }
  }

  function updateDisabledNiveauxOptions() {
    var selectedNiveaux = [];
    $('.sel-niveau-item').each(function() {
      var val = $(this).val();
      if (val) selectedNiveaux.push(val);
    });

    $('.sel-niveau-item').each(function() {
      var $select = $(this);
      var currentVal = $select.val();

      $select.find('option').each(function() {
        var optVal = $(this).attr('value');
        if (!optVal) return;

        var isNotInEdt = (edtActiveNiveaux.length > 0 && edtActiveNiveaux.indexOf(optVal) === -1);
        var isAlreadySelected = (optVal !== currentVal && selectedNiveaux.indexOf(optVal) !== -1);

        if (isNotInEdt || isAlreadySelected) {
          $(this).prop('disabled', true);
        } else {
          $(this).prop('disabled', false);
        }
      });
    });
  }

  function renderClassesPreview($card) {
    filterFilieresForNiveauCard($card);

    var nivCode = $card.find('.sel-niveau-item').val();
    var checkedFilieres = [];
    $card.find('.chk-filiere-item:checked:visible').each(function() {
      checkedFilieres.push($(this).val());
    });

    var $previewContainer = $card.find('.preview-badges-list');
    if (!nivCode) {
      $previewContainer.html('<span style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>');
      return;
    }

    if (checkedFilieres.length === 0) {
      $previewContainer.html('<span style="color: #DC2626; font-weight: 700;">Aucune filière configurée dans l\'emploi du temps pour ce niveau.</span>');
      return;
    }

    var matchingClasses = window.ALL_CLASSES.filter(function(c) {
      if (c.niveau_code !== nivCode) return false;
      return checkedFilieres.indexOf(c.filiere_code) !== -1;
    });

    if (matchingClasses.length === 0) {
      $previewContainer.html('<span style="color: #DC2626; font-weight: 600;">Aucune classe configurée dans l\'emploi du temps.</span>');
    } else {
      var html = matchingClasses.map(function(c) {
        return '<span style="background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">' + c.libelle_classe + '</span>';
      }).join(' ');
      $previewContainer.html(html);
    }
  }

  function updateNiveauRowIndices() {
    $('#niveaux-container .niveau-row-card').each(function(idx) {
      $(this).find('.niveau-row-num').text(idx + 1);
      $(this).find('.sel-niveau-item').attr('name', 'niveaux[' + idx + '][niveau_code]');
      $(this).find('.chk-filiere-item').each(function() {
        $(this).attr('name', 'niveaux[' + idx + '][filiere_codes][]');
      });

      if ($('#niveaux-container .niveau-row-card').length > 1) {
        $(this).find('.btn-remove-niveau-row').css('display', 'inline-flex');
      } else {
        $(this).find('.btn-remove-niveau-row').css('display', 'none');
      }
    });
  }

  // Event handlers for dynamic level row
  $(document).on('change', '.sel-niveau-item', function() {
    var $card = $(this).closest('.niveau-row-card');
    updateDisabledNiveauxOptions();
    renderClassesPreview($card);
  });

  $(document).on('change', '.chk-filiere-item', function() {
    var $card = $(this).closest('.niveau-row-card');
    renderClassesPreview($card);
  });

  $(document).on('click', '.btn-toggle-all-filieres', function(e) {
    e.preventDefault();
    var $card = $(this).closest('.niveau-row-card');
    var $chks = $card.find('.chk-filiere-item:visible');
    var allChecked = $chks.filter(':checked').length === $chks.length;
    $chks.prop('checked', !allChecked);
    $(this).text(allChecked ? 'Tout cocher' : 'Tout décocher');
    renderClassesPreview($card);
  });

  $('#btn-add-niveau-row').on('click', function(e) {
    e.preventDefault();
    var idx = $('#niveaux-container .niveau-row-card').length;
    var filieresHtml = window.ALL_FILIERES.map(function(f) {
      return '<label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #334155; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; cursor: pointer; user-select: none;">' +
             '<input type="checkbox" name="niveaux[' + idx + '][filiere_codes][]" value="' + f.code_filiere + '" class="chk-filiere-item" checked style="accent-color: #1E3A5F;"> ' +
             f.libelle_filiere +
             '</label>';
    }).join('');

    var niveauxHtml = window.ALL_NIVEAUX.map(function(n) {
      return '<option value="' + n.code_niveau + '">' + n.libelle_niveau + '</option>';
    }).join('');

    var newCardHtml = `
      <div class="niveau-row-card" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 20px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <span style="font-weight: 800; font-size: 13px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="bookmark" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Groupe Niveau #<span class="niveau-row-num">${idx + 1}</span>
          </span>
          <button type="button" class="btn-remove-niveau-row" style="background: transparent; border: none; color: #EF4444; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Retirer ce niveau
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; align-items: start;">
          <div>
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px;">
              Niveau d'Études <span style="color: #EF4444;">*</span>
            </label>
            <select class="form-control sel-niveau-item" name="niveaux[${idx}][niveau_code]" required style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
              <option value="">-- Choisir un niveau --</option>
              ${niveauxHtml}
            </select>
          </div>

          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label style="font-weight: 700; font-size: 12px; color: #334155; margin: 0;">
                Filières Concernées <span style="font-size: 11px; color: #15803D; font-weight: 600;">(Emploi du temps)</span>
              </label>
              <span style="font-size: 11px;">
                <a href="#" class="btn-toggle-all-filieres" style="color: #1D4ED8; font-weight: 700; text-decoration: none;">Tout cocher</a>
              </span>
            </div>
            <div class="filieres-checkbox-group" style="display: flex; flex-wrap: wrap; gap: 8px; background: #FFFFFF; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; max-height: 120px; overflow-y: auto;">
              ${filieresHtml}
            </div>
          </div>
        </div>

        <div class="classes-preview-box" style="margin-top: 12px; font-size: 12px; color: #475569; display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
          <span style="font-weight: 700; color: #0F172A;"><i data-lucide="check-circle" style="width: 14px; height: 14px; color: #16A34A; vertical-align: text-bottom;"></i> Classes concernées :</span>
          <span class="preview-badges-list" style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>
        </div>
      </div>
    `;

    $('#niveaux-container').append(newCardHtml);
    if (window.lucide) lucide.createIcons();
    updateNiveauRowIndices();
    updateDisabledNiveauxOptions();
    var $newCard = $('#niveaux-container .niveau-row-card').last();
    renderClassesPreview($newCard);
  });

  $(document).on('click', '.btn-remove-niveau-row', function(e) {
    e.preventDefault();
    if ($('#niveaux-container .niveau-row-card').length > 1) {
      $(this).closest('.niveau-row-card').remove();
      updateNiveauRowIndices();
      updateDisabledNiveauxOptions();
      $('#niveaux-container .niveau-row-card').each(function() {
        renderClassesPreview($(this));
      });
    }
  });

  // Initial setup on page load
  updateDisabledNiveauxOptions();
  $('#niveaux-container .niveau-row-card').each(function() {
    renderClassesPreview($(this));
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
