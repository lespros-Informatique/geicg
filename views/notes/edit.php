<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$anneeCode = $anneeCode ?? Session::get('annee_active_code', '');
$anneeLibelle = $anneeLibelle ?? Session::get('annee_active_libelle', $anneeCode);
$niveaux = $niveaux ?? [];
$matieres = $matieres ?? [];
$inscriptionsDetails = $inscriptionsDetails ?? [];

// Structurer les étudiants strictement par classe
$studentsByClass = [];
foreach ($inscriptionsDetails as $ins) {
  $cCode = $ins['classe_code'] ?: 'NO_CLASS';
  if (!isset($studentsByClass[$cCode])) {
    $studentsByClass[$cCode] = [];
  }
  $nomAffiche = (!empty($ins['etudiant_nom'])) ? $ins['etudiant_nom'] : $ins['code_inscription'];
  if (!empty($ins['matricule_etudiant'])) {
    $nomAffiche .= ' (' . $ins['matricule_etudiant'] . ')';
  }
  $studentsByClass[$cCode][] = [
    'code_inscription' => $ins['code_inscription'],
    'nom_affiche' => $nomAffiche
  ];
}

$selectedInscriptionCode = $item['inscription_code'] ?? '';
if (empty($selectedClasseCode) && !empty($selectedInscriptionCode)) {
  foreach ($inscriptionsDetails as $ins) {
    if ($ins['code_inscription'] === $selectedInscriptionCode) {
      $selectedClasseCode = $ins['classe_code'];
      break;
    }
  }
}

$typeEvals = [
  'INTERROGATION' => 'Interrogation',
  'DEVOIR' => 'Devoir / Contrôle Continu',
  'EXAMEN' => 'Examen / Composition'
];
$selectedTypeEval = $item['type_evaluation_code'] ?? 'INTERROGATION';
if ($selectedTypeEval === 'CC') $selectedTypeEval = 'DEVOIR';
if ($selectedTypeEval === 'TP') $selectedTypeEval = 'INTERROGATION';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="edit-3" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
            <?= !empty($item['id_note']) ? 'Éditer ' : 'Ajouter ' ?> Note / Évaluation Individuelle
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Menu de sélection par classe pour la gestion des notes individuelles</p>
        </div>
        <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <!-- BANNIÈRE CADRE & ANNÉE ACADÉMIQUE -->
      <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748B;">Année Académique Active</div>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 2px; display: flex; align-items: center; gap: 8px;">
              <?= htmlspecialchars($anneeLibelle) ?>
              <span class="eval-badge" style="background: #DCFCE7; color: #15803D;">Session Active</span>
            </div>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 12px; font-weight: 700; color: #64748B;">Saisie Groupée par Classe ?</span>
          <a href="<?= RACINE ?>note/saisieClasse" class="btn btn-outline-primary" style="font-weight: 700; font-size: 13px; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="users" style="width: 15px; height: 15px;"></i> Passer à la saisie par classe
          </a>
        </div>
      </div>

      <!-- MENU DE SÉLECTION PAR CLASSE (Filtre Niveau + Classe) -->
      <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 22px; border: 1px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <div style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #1E3A5F; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="filter" style="width: 16px; height: 16px; color: #0284C7;"></i>
          Menu de Sélection de la Classe
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; align-items: end;">
          <!-- Filtres Niveau -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">
              <i data-lucide="layers" style="width: 14px; height: 14px; color: #64748B; display: inline-block; vertical-align: text-bottom;"></i>
              Filtre par Niveau d'Études
            </label>
            <select class="form-control select2" id="sel_niveau_filter" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Sélection Classe (OBLIGATOIRE) -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">
              <i data-lucide="graduation-cap" style="width: 14px; height: 14px; color: #64748B; display: inline-block; vertical-align: text-bottom;"></i>
              Classe <span style="color: #EF4444;">*</span>
            </label>
            <select class="form-control select2" id="sel_classe_no" name="classe_code" style="width: 100%;" required>
              <option value="">-- Sélectionner une classe --</option>
              <?php foreach($classes as $c): ?>
                <?php 
                  $cCode = $c['code_classe'];
                  $studentCount = isset($studentsByClass[$cCode]) ? count($studentsByClass[$cCode]) : 0;
                ?>
                <option value="<?= htmlspecialchars($cCode) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>" <?= ($selectedClasseCode === $cCode) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['libelle_classe']) ?> (<?= $studentCount ?> élève<?= $studentCount > 1 ? 's' : '' ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Banner Contextuel Classe Sélectionnée -->
        <div id="class-info-banner" style="display: none; background: #F0F9FF; border: 1.5px solid #BAE6FD; border-radius: 10px; padding: 12px 18px; margin-top: 16px; align-items: center; justify-content: space-between; gap: 12px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #0284C7; color: #FFFFFF; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 800;">
              <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #0284C7;">Classe Active</div>
              <div id="class-info-title" style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 1px;">-</div>
            </div>
          </div>
          <span id="class-student-badge" class="badge" style="background: #1E3A5F; color: #FFFFFF; font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 700;">
            0 élève
          </span>
        </div>
      </div>

      <!-- FORMULAIRE DE SAISIE DE LA NOTE -->
      <div class="card" style="background: #FFFFFF; border-radius: 14px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>note/<?= !empty($item['id_note']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;" id="form-note-indiv">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_note'])): ?>
            <input type="hidden" name="id_note" value="<?= $item['id_note'] ?>">
          <?php endif; ?>
          <input type="hidden" name="annee_code" value="<?= htmlspecialchars($anneeCode) ?>">
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; width: 100%;">
            
            <!-- Étape 1 : Choisir l'Élève (Strictement réservé à la classe sélectionnée) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                1. Sélectionner l'Élève de la classe <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="sel_ins_no" style="width: 100%;" name="inscription_code" required disabled>
                <option value="">-- Veuillez d'abord choisir une classe ci-dessus --</option>
              </select>
            </div>

            <!-- Étape 2 : Type d'Évaluation -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                2. Type d'Évaluation <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="sel_type_eval" style="width: 100%;" name="type_evaluation_code" required>
                <?php foreach ($typeEvals as $code => $lbl): ?>
                  <option value="<?= htmlspecialchars($code) ?>" <?= ($selectedTypeEval === $code) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lbl) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Étape 3 : Matière évaluée -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                3. Matière évaluée <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="sel_mat_no" style="width: 100%;" name="matiere_code" required>
                <option value="">-- Rechercher une matière --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= htmlspecialchars($m['code_matiere']) ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['libelle_matiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Étape 4 : Semestre académique -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                <span>4. Semestre académique <span style="color: #EF4444;">*</span></span>
                <span id="badge-semestre-lock" class="eval-badge" style="background: #F1F5F9; color: #64748B; display: none;">
                  <i data-lucide="lock" style="width: 11px; height: 11px;"></i> Auto
                </span>
              </label>
              <select class="form-control select2" id="sel_sem_no" style="width: 100%;" name="semestre_code" required>
                <option value="">-- Rechercher un semestre --</option>
                <?php foreach($semestres as $sm): ?>
                  <option value="<?= htmlspecialchars($sm['code_semestre']) ?>" <?= (($item['semestre_code'] ?? '') == $sm['code_semestre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sm['libelle_semestre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="semestre_code_hidden" id="semestre_code_hidden" value="<?= htmlspecialchars($item['semestre_code'] ?? '') ?>">
            </div>

          </div>

          <!-- BLOC CONDITIONNEL DÉDIÉ AUX EXAMENS & COMPOSITIONS -->
          <div id="box-composition-select" style="margin-top: 22px; padding-top: 18px; border-top: 1px dashed #CBD5E1; <?= ($selectedTypeEval === 'EXAMEN') ? 'display: block;' : 'display: none;' ?>">
            <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 18px;">
              <label style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="award" style="width: 18px; height: 18px; color: #0284C7;"></i> Épreuve Examen / Composition Programmée <span style="color:#EF4444;">*</span>
              </label>
              <select name="composition_code" id="sel_composition_code" class="form-control select2" style="width: 100%;">
                <option value="">-- Sélectionner l'épreuve programmée --</option>
              </select>
              <span id="msg-no-composition" style="font-size: 12px; color: #DC2626; margin-top: 6px; display: none; font-weight: 600;">
                <i data-lucide="alert-circle" style="width: 14px; height: 14px; vertical-align: text-bottom;"></i> Aucune composition programmée disponible pour cette classe et matière.
              </span>
            </div>
          </div>

          <!-- PROPRIÉTÉS DE L'ÉVALUATION (LIBELLÉ & COEFFICIENT) -->
          <div style="margin-top: 22px; padding-top: 18px; border-top: 1px dashed #CBD5E1; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            
            <!-- Intitulé / Libellé de l'Évaluation -->
            <div class="form-group" style="grid-column: span 2;">
              <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Intitulé / Libellé de l'Évaluation</span>
                <span id="badge-libelle-mode" class="eval-badge" style="background: #F1F5F9; color: #475569;">Modifiable</span>
              </label>
              <input type="text" name="libelle_eval" id="input_libelle_eval" value="<?= htmlspecialchars($item['libelle_eval'] ?? '') ?>" placeholder="Ex: Interrogation N°1, Devoir Surveillé..." class="form-control" style="width: 100%; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
            </div>

            <!-- Coefficient -->
            <div class="form-group">
              <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                <span>Coefficient</span>
                <span id="badge-coef-mode" class="eval-badge" style="background: #F1F5F9; color: #475569;">Modifiable</span>
              </label>
              <input type="number" step="0.25" min="0.5" max="10" name="coefficient" id="input_coef_eval" value="<?= htmlspecialchars($item['coefficient'] ?? '1.00') ?>" class="form-control" style="width: 100%; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 800; color: #1E40AF;">
            </div>

          </div>

          <!-- SAISIE DE LA NOTE & APPRÉCIATION -->
          <div style="margin-top: 22px; padding-top: 18px; border-top: 1px dashed #CBD5E1; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; align-items: start;">
            
            <!-- Note sur 20 -->
            <div class="form-group">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Note sur 20 <span style="color: #EF4444;">*</span>
              </label>
              <div style="display: flex; align-items: center; gap: 12px;">
                <input type="number" step="0.25" min="0" max="20" class="form-control" id="valeur_note_input" style="width: 140px; box-sizing: border-box; padding: 11px 14px; font-size: 18px; border-radius: 8px; border: 2px solid #0284C7; background: #FFFFFF; color: #0F172A; font-weight: 800; text-align: center;" name="valeur_note" value="<?= htmlspecialchars($item['valeur_note'] ?? '') ?>" placeholder="Ex: 15.5" required>
                <div id="badge-appreciation-live" class="eval-badge" style="background: #F1F5F9; color: #64748B; padding: 8px 14px; font-size: 13px; font-weight: 800; border-radius: 8px;">
                  Non saisi
                </div>
              </div>
            </div>

            <!-- Observations / Remarques -->
            <div class="form-group" style="grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Observations / Remarques de l'Enseignant</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="observations" placeholder="Ex: Excellent travail, assiduité exemplaire..." rows="3"><?= htmlspecialchars($item['observations'] ?? ($item['appreciation'] ?? '')) ?></textarea>
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; font-weight: 700; border-radius: 8px; padding: 12px 28px; box-shadow: 0 4px 10px rgba(30,58,95,0.25); display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="save" style="width: 18px; height: 18px;"></i> Enregistrer la Note
            </button>
            <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 12px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script type="application/json" id="notes-edit-config">
<?= json_encode([
    'studentsByClass' => $studentsByClass ?? [],
    'selectedInscCode' => $selectedInscriptionCode ?? '',
    'existingCompCode' => $item['composition_code'] ?? ''
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/notes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
