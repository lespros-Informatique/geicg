<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$dbConn = (new Database())->getCon();
$classes = (new ModelClasse())->getAll();
$matieres = (new ModelMatiere())->getAll();
$semestres = (new ModelSemestre())->getAll();

$inscriptionsDetails = $dbConn->query("
    SELECT i.code_inscription, i.classe_code,
           CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS etudiant_nom,
           e.matricule_etudiant,
           cl.libelle_classe
    FROM inscriptions i
    LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
    LEFT JOIN classes cl ON cl.code_classe = i.classe_code
    ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Structurer les étudiants strictement par classe (Pas de liste globale en vrac)
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
$selectedClasseCode = '';
if (!empty($selectedInscriptionCode)) {
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
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_note']) ? 'Éditer ' : 'Ajouter ' ?> Note / Évaluation</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Sélectionnez obligatoirement la classe pour accéder à la liste des étudiants</p>
        </div>
        <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>note/<?= !empty($item['id_note']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_note'])): ?>
            <input type="hidden" name="id_note" value="<?= $item['id_note'] ?>">
          <?php endif; ?>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Étape 1 : Choisir la Classe (OBLIGATOIRE) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">1. Sélectionner la Classe <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_classe_no" style="width: 100%;" required>
                <option value="">-- Choisir d'abord une classe --</option>
                <?php foreach($classes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_classe']) ?>" <?= ($selectedClasseCode === $c['code_classe']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['libelle_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Étape 2 : Choisir l'Élève (Strictement réservé à la classe sélectionnée) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">2. Sélectionner l'Élève de la classe <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_ins_no" style="width: 100%;" name="inscription_code" required disabled>
                <option value="">-- Veuillez d'abord choisir une classe --</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">3. Matière évaluée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_mat_no" style="width: 100%;" name="matiere_code" required>
                <option value="">-- Rechercher une matière --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= htmlspecialchars($m['code_matiere']) ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>><?= htmlspecialchars($m['libelle_matiere']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">4. Semestre académique <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_sem_no" style="width: 100%;" name="semestre_code" required>
                <option value="">-- Rechercher un semestre --</option>
                <?php foreach($semestres as $sm): ?>
                  <option value="<?= htmlspecialchars($sm['code_semestre']) ?>" <?= (($item['semestre_code'] ?? '') == $sm['code_semestre']) ? 'selected' : '' ?>><?= htmlspecialchars($sm['libelle_semestre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">5. Type d'évaluation <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="type_evaluation_code" required>
                <option value="EXAMEN" <?= (($item['type_evaluation_code'] ?? '') === 'EXAMEN' || ($item['type_evaluation_code'] ?? '') === 'Examen') ? 'selected' : '' ?>>Examen Semestriel</option>
                <option value="CC" <?= (($item['type_evaluation_code'] ?? '') === 'CC' || ($item['type_evaluation_code'] ?? '') === 'Devoir') ? 'selected' : '' ?>>Contrôle Continu / Devoir</option>
                <option value="TP" <?= (($item['type_evaluation_code'] ?? '') === 'TP') ? 'selected' : '' ?>>Travaux Pratiques (TP)</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">6. Note sur 20 <span style="color: #EF4444;">*</span></label>
              <input type="number" step="0.25" min="0" max="20" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700;" name="valeur_note" value="<?= htmlspecialchars($item['valeur_note'] ?? '') ?>" placeholder="Ex: 15.5" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Observations / Remarques du professeur</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="observations" placeholder="Ex: Excellent travail, très bonne assiduité..." rows="3"><?= htmlspecialchars($item['observations'] ?? ($item['appreciation'] ?? '')) ?></textarea>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer la Note</button>
            <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_classe_no, #sel_mat_no, #sel_sem_no').select2({ width: '100%' });
  }

  function loadStudentsForClasse(classeCode, keepSelected) {
    var $el = $('#sel_ins_no');
    $el.empty();
    
    if (!classeCode || !studentsByClass[classeCode]) {
      $el.append('<option value="">-- Veuillez d\'abord choisir une classe --</option>');
      $el.prop('disabled', true);
    } else {
      $el.append('<option value="">-- Choisir un élève de la classe --</option>');
      var list = studentsByClass[classeCode];
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
