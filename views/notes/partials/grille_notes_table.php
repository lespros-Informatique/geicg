<?php
// Vue partielle : Grille de saisie des notes et KPIs (chargée en direct ou via AJAX)
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
