<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$dbConn = (new Database())->getCon();
$niveaux = (new ModelNiveau())->getAll();
$classes = (new ModelClasse())->getAll();
$matieres = (new ModelMatiere())->getAll();
$semestres = (new ModelSemestre())->getAll();

$inscriptionsDetails = $dbConn->query("
    SELECT i.code_inscription, i.classe_code,
           CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS etudiant_nom,
           e.matricule_etudiant,
           cl.libelle_classe,
           cl.niveau_code
    FROM inscriptions i
    LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
    LEFT JOIN classes cl ON cl.code_classe = i.classe_code
    ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC
")->fetchAll(PDO::FETCH_ASSOC);

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
$selectedClasseCode = $selectedClasseCode ?? '';
if (empty($selectedClasseCode) && !empty($selectedInscriptionCode)) {
  foreach ($inscriptionsDetails as $ins) {
    if ($ins['code_inscription'] === $selectedInscriptionCode) {
      $selectedClasseCode = $ins['classe_code'];
      break;
    }
  }
}
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
            <?= !empty($item['id_note']) ? 'Éditer ' : 'Ajouter ' ?> Note / Évaluation
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Menu de sélection par classe pour la gestion des notes individuelles</p>
        </div>
        <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
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
              Classe Cible <span style="color: #EF4444;">*</span>
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
        <form action="<?= RACINE ?>note/<?= !empty($item['id_note']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_note'])): ?>
            <input type="hidden" name="id_note" value="<?= $item['id_note'] ?>">
          <?php endif; ?>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 22px; width: 100%;">
            
            <!-- Étape 1 : Choisir l'Élève (Strictement réservé à la classe sélectionnée) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                1. Sélectionner l'Élève <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="sel_ins_no" style="width: 100%;" name="inscription_code" required disabled>
                <option value="">-- Veuillez d'abord choisir une classe ci-dessus --</option>
              </select>
            </div>

            <!-- Étape 2 : Matière évaluée -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                2. Matière évaluée <span style="color: #EF4444;">*</span>
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

            <!-- Étape 3 : Semestre académique -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                3. Semestre académique <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="sel_sem_no" style="width: 100%;" name="semestre_code" required>
                <option value="">-- Rechercher un semestre --</option>
                <?php foreach($semestres as $sm): ?>
                  <option value="<?= htmlspecialchars($sm['code_semestre']) ?>" <?= (($item['semestre_code'] ?? '') == $sm['code_semestre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sm['libelle_semestre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Étape 4 : Type d'évaluation -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                4. Type d'Évaluation <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="type_evaluation_code" required>
                <option value="EXAMEN" <?= (($item['type_evaluation_code'] ?? '') === 'EXAMEN' || ($item['type_evaluation_code'] ?? '') === 'Examen') ? 'selected' : '' ?>>Examen Semestriel</option>
                <option value="CC" <?= (($item['type_evaluation_code'] ?? '') === 'CC' || ($item['type_evaluation_code'] ?? '') === 'Devoir') ? 'selected' : '' ?>>Contrôle Continu / Devoir</option>
                <option value="TP" <?= (($item['type_evaluation_code'] ?? '') === 'TP') ? 'selected' : '' ?>>Travaux Pratiques (TP)</option>
              </select>
            </div>

            <!-- Étape 5 : Note sur 20 -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                5. Note sur 20 <span style="color: #EF4444;">*</span>
              </label>
              <input type="number" step="0.25" min="0" max="20" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 800;" name="valeur_note" value="<?= htmlspecialchars($item['valeur_note'] ?? '') ?>" placeholder="Ex: 15.5" required>
            </div>

            <!-- Observations -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Observations / Remarques de l'Enseignant</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="observations" placeholder="Ex: Excellent travail, assiduité exemplaire..." rows="3"><?= htmlspecialchars($item['observations'] ?? ($item['appreciation'] ?? '')) ?></textarea>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; font-weight: 700; border-radius: 8px; padding: 11px 26px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);">
              <i data-lucide="save" style="width: 16px; height: 16px; vertical-align: text-bottom; margin-right: 4px;"></i> Enregistrer la Note
            </button>
            <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  
  var studentsByClass = <?= json_encode($studentsByClass) ?>;
  var selectedInscCode = <?= json_encode($selectedInscriptionCode) ?>;

  if ($.fn.select2) {
    $('#sel_niveau_filter, #sel_classe_no, #sel_mat_no, #sel_sem_no').select2({ width: '100%' });
  }

  // Filtrage Niveau -> Classe
  $('#sel_niveau_filter').on('change', function() {
    var nivCode = $(this).val();
    $('#sel_classe_no option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!nivCode || !optNiveau || optNiveau === nivCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#sel_classe_no option:selected').prop('disabled')) {
      $('#sel_classe_no').val('').trigger('change.select2');
      loadStudentsForClasse('', false);
    } else {
      $('#sel_classe_no').select2({ width: '100%' });
    }
  });

  function loadStudentsForClasse(classeCode, keepSelected) {
    var $el = $('#sel_ins_no');
    $el.empty();
    
    if (!classeCode || !studentsByClass[classeCode]) {
      $el.append('<option value="">-- Veuillez d\'abord choisir une classe ci-dessus --</option>');
      $el.prop('disabled', true);
      $('#class-info-banner').css('display', 'none');
    } else {
      var list = studentsByClass[classeCode];
      var clsName = $('#sel_classe_no option:selected').text();

      $('#class-info-title').text(clsName);
      $('#class-student-badge').text(list.length + ' élève' + (list.length > 1 ? 's' : '') + ' inscrit' + (list.length > 1 ? 's' : ''));
      $('#class-info-banner').css('display', 'flex');

      $el.append('<option value="">-- Choisir un élève de la classe --</option>');
      $.each(list, function(i, st) {
        var isSelected = (keepSelected && st.code_inscription === selectedInscCode) ? 'selected' : '';
        $el.append('<option value="' + st.code_inscription + '" ' + isSelected + '>' + st.nom_affiche + '</option>');
      });
      $el.prop('disabled', false);
    }
    
    if ($.fn.select2) {
      $el.select2({ placeholder: "-- Choisir un élève --", width: '100%' });
    }
  }

  $('#sel_classe_no').on('change', function() {
    loadStudentsForClasse($(this).val(), false);
  });

  var initialClasse = $('#sel_classe_no').val();
  if (initialClasse) {
    loadStudentsForClasse(initialClasse, true);
  } else {
    loadStudentsForClasse('', false);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
