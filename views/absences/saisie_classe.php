<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$classes = $classes ?? [];
$matieres = $matieres ?? [];
$selectedClasseCode = $selectedClasseCode ?? '';
$selectedDate = $selectedDate ?? date('Y-m-d');
$selectedMatiereCode = $selectedMatiereCode ?? '';
$etudiants = $etudiants ?? [];
$existingAbsences = $existingAbsences ?? [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <a href="<?= RACINE ?>absence/list" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
            </a>
            <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Registre des Absences par Classe</h1>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Effectuez l'appel et saisissez les absences groupées pour une classe</p>
        </div>
      </div>

      <!-- Formulaire de Sélection de la Classe & Séance -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <form method="GET" action="<?= RACINE ?>absence/saisieClasse" id="filter-form">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Classe <span style="color:#EF4444;">*</span></label>
              <select name="classe_code" id="select-classe" class="form-control select2" required style="width: 100%;">
                <option value="">-- Choisir une classe --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_classe']) ?>" <?= ($selectedClasseCode === $c['code_classe']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['libelle_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Date du Cours / Séance <span style="color:#EF4444;">*</span></label>
              <input type="date" name="date_absence" value="<?= htmlspecialchars($selectedDate) ?>" class="form-control" required style="border-radius: 8px;">
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Matière (Optionnel)</label>
              <select name="matiere_code" class="form-control select2" style="width: 100%;">
                <option value="">-- Toutes / Non spécifiée --</option>
                <?php foreach ($matieres as $m): ?>
                  <option value="<?= htmlspecialchars($m['code_matiere']) ?>" <?= ($selectedMatiereCode === $m['code_matiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['libelle_matiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; width: 100%; padding: 10px;">
                <i data-lucide="search" style="width: 16px; height: 16px; vertical-align: middle;"></i> Charger la Classe
              </button>
            </div>
          </div>
        </form>
      </div>

      <?php if (!empty($selectedClasseCode)): ?>
        <form id="form-save-batch-absences">
          <input type="hidden" name="classe_code" value="<?= htmlspecialchars($selectedClasseCode) ?>">
          <input type="hidden" name="date_absence" value="<?= htmlspecialchars($selectedDate) ?>">
          <input type="hidden" name="matiere_code" value="<?= htmlspecialchars($selectedMatiereCode) ?>">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
              <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0F172A; margin: 0;">
                  Feuille d'Appel des Étudiants (<?= count($etudiants) ?> inscrit(s))
                </h3>
              </div>
              <div style="display: flex; align-items: center; gap: 12px;">
                <div>
                  <label style="font-size: 11px; font-weight: 700; color: #475569; margin-right: 6px;">Durée du cours (heures) :</label>
                  <input type="number" step="0.5" min="0.5" max="8" name="duree_heures" value="2" class="form-control d-inline-block" style="width: 80px; display: inline-block; text-align: center; border-radius: 6px; padding: 4px 8px;">
                </div>
                <button type="button" id="btn-all-present" class="btn btn-sm btn-outline-success" style="font-size: 12px; font-weight: 600; border-radius: 6px;">
                  Tout marquer Présent
                </button>
              </div>
            </div>

            <?php if (empty($etudiants)): ?>
              <div style="padding: 30px; text-align: center; color: #64748B;">
                <i data-lucide="users" style="width: 40px; height: 40px; stroke-width: 1.5; color: #94A3B8; margin-bottom: 8px;"></i>
                <p style="margin: 0; font-weight: 600;">Aucun étudiant inscrit n'a été trouvé dans cette classe pour l'année académique active.</p>
              </div>
            <?php else: ?>
              <div style="width: 100%; overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 13px;">
                      <th style="padding: 12px; width: 40px;">#</th>
                      <th style="padding: 12px;">Matricule</th>
                      <th style="padding: 12px;">Nom & Prénom Élève</th>
                      <th style="padding: 12px; text-align: center; width: 140px;">Statut</th>
                      <th style="padding: 12px; text-align: center; width: 130px;">Justifiée</th>
                      <th style="padding: 12px;">Motif / Remarque</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($etudiants as $index => $e): 
                      $etudCode = $e['code_etudiant'];
                      $hasAbsence = isset($existingAbsences[$etudCode]);
                      $abs = $existingAbsences[$etudCode] ?? [];
                      $isJustifiee = ($abs['justifiee'] ?? 'non') === 'oui';
                    ?>
                      <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" class="student-row">
                        <td style="padding: 12px; color: #94A3B8; font-weight: 600;"><?= $index + 1 ?></td>
                        <td style="padding: 12px; font-weight: 700; color: #1E3A5F; font-size: 13px;">
                          <?= htmlspecialchars($e['matricule_etudiant'] ?: '-') ?>
                        </td>
                        <td style="padding: 12px; font-weight: 600; color: #0F172A;">
                          <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                          <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px;" class="status-toggle-label">
                            <input type="checkbox" name="absences[<?= $etudCode ?>][is_absent]" value="1" class="check-absent" <?= $hasAbsence ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer; accent-color: #DC2626;">
                            <span class="badge-status" style="padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; background: <?= $hasAbsence ? '#FEE2E2' : '#DCFCE7' ?>; color: <?= $hasAbsence ? '#991B1B' : '#166534' ?>;">
                              <?= $hasAbsence ? 'ABSENT' : 'PRÉSENT' ?>
                            </span>
                          </label>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                          <select name="absences[<?= $etudCode ?>][justifiee]" class="form-control form-control-sm select-justifiee" <?= !$hasAbsence ? 'disabled' : '' ?> style="border-radius: 6px; font-size: 12px;">
                            <option value="non" <?= !$isJustifiee ? 'selected' : '' ?>>Non justifiée</option>
                            <option value="oui" <?= $isJustifiee ? 'selected' : '' ?>>Justifiée</option>
                          </select>
                        </td>
                        <td style="padding: 12px;">
                          <input type="text" name="absences[<?= $etudCode ?>][motif]" value="<?= htmlspecialchars($abs['motif_absence'] ?? '') ?>" placeholder="Ex: Raison médicale, retard..." class="form-control form-control-sm input-motif" <?= !$hasAbsence ? 'disabled' : '' ?> style="border-radius: 6px; font-size: 12px;">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-success btn-lg" style="background: #16A34A; border-color: #16A34A; font-weight: 700; padding: 12px 28px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                  <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i> Enregistrer le Registre des Absences
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

  $(document).on('change', '.check-absent', function() {
    var isAbsent = $(this).is(':checked');
    var $row = $(this).closest('tr');
    var $badge = $row.find('.badge-status');
    var $justSelect = $row.find('.select-justifiee');
    var $motifInput = $row.find('.input-motif');

    if (isAbsent) {
      $badge.text('ABSENT').css({ 'background': '#FEE2E2', 'color': '#991B1B' });
      $justSelect.prop('disabled', false);
      $motifInput.prop('disabled', false);
    } else {
      $badge.text('PRÉSENT').css({ 'background': '#DCFCE7', 'color': '#166534' });
      $justSelect.prop('disabled', true);
      $motifInput.prop('disabled', true);
    }
  });

  $('#btn-all-present').on('click', function() {
    $('.check-absent').prop('checked', false).trigger('change');
  });

  $('#form-save-batch-absences').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement en cours...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: '<?= RACINE ?>absence/saveBatch',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Registre des absences enregistré avec succès !');
          setTimeout(function() { window.location.href = '<?= RACINE ?>absence/list'; }, 1000);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          $btn.prop('disabled', false).html('<i data-lucide="check-circle-2"></i> Enregistrer le Registre des Absences');
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur de connexion au serveur');
        $btn.prop('disabled', false).html('<i data-lucide="check-circle-2"></i> Enregistrer le Registre des Absences');
        if (window.lucide) lucide.createIcons();
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
