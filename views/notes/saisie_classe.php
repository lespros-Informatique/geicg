<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$classes = $classes ?? [];
$matieres = $matieres ?? [];
$semestres = $semestres ?? [];
$selectedClasseCode = $selectedClasseCode ?? '';
$selectedMatiereCode = $selectedMatiereCode ?? '';
$selectedSemestreCode = $selectedSemestreCode ?? '';
$selectedTypeEval = $selectedTypeEval ?? 'EXAMEN';
$etudiants = $etudiants ?? [];
$existingNotes = $existingNotes ?? [];
$typeEvals = [
  'EXAMEN' => 'Examen Fin de Semestre',
  'CC' => 'Contrôle Continu',
  'TP' => 'Travaux Pratiques (TP)',
  'DEVOIR' => 'Devoir Sur Table'
];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <a href="<?= RACINE ?>note/list" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
            </a>
            <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Grille de Saisie des Notes par Classe</h1>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisissez et enregistrez les évaluations d'une classe complète pour une matière</p>
        </div>
      </div>

      <!-- Formulaire de Sélection (Classe, Matière, Semestre, Type Éval) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <form method="GET" action="<?= RACINE ?>note/saisieClasse" id="filter-notes-form">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; align-items: end;">
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Classe <span style="color:#EF4444;">*</span></label>
              <select name="classe_code" class="form-control select2" required style="width: 100%;">
                <option value="">-- Choisir la classe --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_classe']) ?>" <?= ($selectedClasseCode === $c['code_classe']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['libelle_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Matière <span style="color:#EF4444;">*</span></label>
              <select name="matiere_code" class="form-control select2" required style="width: 100%;">
                <option value="">-- Choisir la matière --</option>
                <?php foreach ($matieres as $m): ?>
                  <option value="<?= htmlspecialchars($m['code_matiere']) ?>" <?= ($selectedMatiereCode === $m['code_matiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['libelle_matiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Semestre <span style="color:#EF4444;">*</span></label>
              <select name="semestre_code" class="form-control select2" required style="width: 100%;">
                <option value="">-- Choisir le semestre --</option>
                <?php foreach ($semestres as $s): ?>
                  <option value="<?= htmlspecialchars($s['code_semestre']) ?>" <?= ($selectedSemestreCode === $s['code_semestre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['libelle_semestre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Type d'Évaluation</label>
              <select name="type_evaluation_code" class="form-control select2" style="width: 100%;">
                <?php foreach ($typeEvals as $code => $lbl): ?>
                  <option value="<?= htmlspecialchars($code) ?>" <?= ($selectedTypeEval === $code) ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; width: 100%; padding: 10px;">
                <i data-lucide="filter" style="width: 16px; height: 16px; vertical-align: middle;"></i> Afficher Grille
              </button>
            </div>
          </div>
        </form>
      </div>

      <?php if (!empty($selectedClasseCode) && !empty($selectedMatiereCode) && !empty($selectedSemestreCode)): ?>
        <form id="form-save-batch-notes">
          <input type="hidden" name="classe_code" value="<?= htmlspecialchars($selectedClasseCode) ?>">
          <input type="hidden" name="matiere_code" value="<?= htmlspecialchars($selectedMatiereCode) ?>">
          <input type="hidden" name="semestre_code" value="<?= htmlspecialchars($selectedSemestreCode) ?>">
          <input type="hidden" name="type_evaluation_code" value="<?= htmlspecialchars($selectedTypeEval) ?>">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
              <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0F172A; margin: 0;">
                  Grille de Saisie (<?= count($etudiants) ?> étudiant(s) dans la classe)
                </h3>
              </div>
              <div style="display: flex; align-items: center; gap: 16px; background: #F8FAFC; padding: 6px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <span style="font-size: 12px; font-weight: 700; color: #64748B;">Moyenne provisoire de classe :</span>
                <strong id="stat-moyenne-classe" style="font-size: 15px; color: #1E3A5F;">- / 20</strong>
              </div>
            </div>

            <?php if (empty($etudiants)): ?>
              <div style="padding: 30px; text-align: center; color: #64748B;">
                <i data-lucide="users" style="width: 40px; height: 40px; stroke-width: 1.5; color: #94A3B8; margin-bottom: 8px;"></i>
                <p style="margin: 0; font-weight: 600;">Aucun étudiant inscrit trouvé dans cette classe pour l'année active.</p>
              </div>
            <?php else: ?>
              <div style="width: 100%; overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 13px;">
                      <th style="padding: 12px; width: 40px;">#</th>
                      <th style="padding: 12px; width: 140px;">Matricule</th>
                      <th style="padding: 12px;">Nom & Prénom Étudiant</th>
                      <th style="padding: 12px; width: 140px; text-align: center;">Note /20</th>
                      <th style="padding: 12px; width: 140px; text-align: center;">Appréciation</th>
                      <th style="padding: 12px;">Observations / Remarques</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($etudiants as $index => $e): 
                      $inscCode = $e['code_inscription'];
                      $note = $existingNotes[$inscCode] ?? [];
                      $valNote = isset($note['valeur_note']) ? (float)$note['valeur_note'] : '';
                    ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;" class="note-row">
                        <td style="padding: 12px; color: #94A3B8; font-weight: 600;"><?= $index + 1 ?></td>
                        <td style="padding: 12px; font-weight: 700; color: #1E3A5F; font-size: 13px;">
                          <?= htmlspecialchars($e['matricule_etudiant'] ?: '-') ?>
                        </td>
                        <td style="padding: 12px; font-weight: 600; color: #0F172A;">
                          <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                          <input type="number" step="0.25" min="0" max="20" name="notes[<?= $inscCode ?>][valeur_note]" value="<?= $valNote !== '' ? htmlspecialchars($valNote) : '' ?>" placeholder="e.g. 14.5" class="form-control form-control-sm input-valeur-note" style="border-radius: 6px; text-align: center; font-weight: 700; font-size: 14px; width: 110px; margin: 0 auto;">
                        </td>
                        <td style="padding: 12px; text-align: center;">
                          <span class="badge-appreciation" style="padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #F1F5F9; color: #64748B;">
                            Non saisi
                          </span>
                        </td>
                        <td style="padding: 12px;">
                          <input type="text" name="notes[<?= $inscCode ?>][observations]" value="<?= htmlspecialchars($note['observations'] ?? '') ?>" placeholder="Observations..." class="form-control form-control-sm" style="border-radius: 6px; font-size: 12px;">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-success btn-lg" style="background: #16A34A; border-color: #16A34A; font-weight: 700; padding: 12px 28px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
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

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

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

  $('#form-save-batch-notes').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement en cours...');
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
