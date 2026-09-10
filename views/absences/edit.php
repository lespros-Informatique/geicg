<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$dbConn = (new Database())->getCon();
$classes = (new ModelClasse())->getAll();
$matieres = (new ModelMatiere())->getAll();

$etudiantsDetails = $dbConn->query("
    SELECT DISTINCT e.code_etudiant, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
           i.classe_code, cl.libelle_classe
    FROM etudiants e
    LEFT JOIN inscriptions i ON i.etudiant_code = e.code_etudiant
    LEFT JOIN classes cl ON cl.code_classe = i.classe_code
    ORDER BY cl.libelle_classe ASC, e.nom_etudiant ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Structurer les étudiants uniquement par classe
$studentsByClass = [];
foreach ($etudiantsDetails as $e) {
  $cCode = $e['classe_code'] ?: 'NO_CLASS';
  if (!isset($studentsByClass[$cCode])) {
    $studentsByClass[$cCode] = [];
  }
  $nomAffiche = trim($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']);
  if (!empty($e['matricule_etudiant'])) {
    $nomAffiche .= ' (' . $e['matricule_etudiant'] . ')';
  }
  $studentsByClass[$cCode][] = [
    'code_etudiant' => $e['code_etudiant'],
    'nom_affiche' => $nomAffiche
  ];
}

$selectedClasseCode = $item['classe_code'] ?? '';
$selectedEtudiantCode = $item['etudiant_code'] ?? '';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_absence']) ? 'Éditer ' : 'Ajouter ' ?> Absence</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Sélectionnez obligatoirement la classe pour accéder à la liste des étudiants</p>
        </div>
        <a href="<?= RACINE ?>absence/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>absence/<?= !empty($item['id_absence']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_absence'])): ?>
            <input type="hidden" name="id_absence" value="<?= $item['id_absence'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Étape 1 : Choisir la Classe (OBLIGATOIRE) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">1. Sélectionner la Classe <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_classe_ab" style="width: 100%;" name="classe_code" required>
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
              <select class="form-control select2" id="sel_etud_ab" style="width: 100%;" name="etudiant_code" required disabled>
                <option value="">-- Veuillez d'abord choisir une classe --</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">3. Matière manquée</label>
              <select class="form-control select2" id="sel_mat_ab" style="width: 100%;" name="matiere_code">
                <option value="">-- Toutes les matières / Non spécifié --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= htmlspecialchars($m['code_matiere']) ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>><?= htmlspecialchars($m['libelle_matiere']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">4. Date du cours <span style="color: #EF4444;">*</span></label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="date_absence" value="<?= htmlspecialchars($item['date_absence'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">5. Volume d'heures manquées <span style="color: #EF4444;">*</span></label>
              <input type="number" step="0.5" min="0.5" max="8" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700;" name="duree_heures" value="<?= htmlspecialchars($item['duree_heures'] ?? '2') ?>" placeholder="Ex: 2" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">6. Justifiée ?</label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="justifiee">
                <option value="non" <?= (($item['justifiee'] ?? 'non') === 'non') ? 'selected' : '' ?>>Non justifiée</option>
                <option value="oui" <?= (($item['justifiee'] ?? '') === 'oui') ? 'selected' : '' ?>>Justifiée</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Motif / Explication</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="motif_absence" placeholder="Ex: Rendez-vous médical (Justificatif fourni)..." rows="3"><?= htmlspecialchars($item['motif_absence'] ?? '') ?></textarea>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer l'Absence</button>
            <a href="<?= RACINE ?>absence/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
  var selectedEtudCode = <?= json_encode($selectedEtudiantCode) ?>;

  if ($.fn.select2) {
    $('#sel_classe_ab, #sel_mat_ab').select2({ width: '100%' });
  }

  function loadStudentsForClasse(classeCode, keepSelected) {
    var $el = $('#sel_etud_ab');
    $el.empty();
    
    if (!classeCode || !studentsByClass[classeCode]) {
      $el.append('<option value="">-- Veuillez d\'abord choisir une classe --</option>');
      $el.prop('disabled', true);
    } else {
      $el.append('<option value="">-- Choisir un élève de la classe --</option>');
      var list = studentsByClass[classeCode];
      $.each(list, function(i, st) {
        var isSelected = (keepSelected && st.code_etudiant === selectedEtudCode) ? 'selected' : '';
        $el.append('<option value="' + st.code_etudiant + '" ' + isSelected + '>' + st.nom_affiche + '</option>');
      });
      $el.prop('disabled', false);
    }
    
    if ($.fn.select2) {
      $el.select2({ placeholder: "-- Choisir un élève --", width: '100%' });
    }
  }

  $('#sel_classe_ab').on('change', function() {
    loadStudentsForClasse($(this).val(), false);
  });

  var initialClasse = $('#sel_classe_ab').val();
  if (initialClasse) {
    loadStudentsForClasse(initialClasse, true);
  } else {
    loadStudentsForClasse('', false);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
