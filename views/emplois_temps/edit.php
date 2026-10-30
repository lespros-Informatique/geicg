<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$cycles = (new ModelCycle())->getAll();
$filieres = (new ModelFiliere())->getAll();
$niveaux = (new ModelNiveau())->getAll();
$classes = (new ModelClasse())->getAll();
$salles = (new ModelSalle())->getAll();
$scolarites = (new ModelScolarite())->getAll();
$ues = [];
$matieres = (new ModelMatiere())->getAll();
$semestres = (new ModelSemestre())->getAll();
$etudiants = (new ModelEtudiant())->getAll();
$inscriptions = (new ModelInscription())->getAll();
$typeDepenses = (new ModelTypeDepense())->getAll();
$users = (new ModelUser())->getAll();
$enseignants = (new ModelEnseignant())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_emploi']) ? 'Éditer ' : 'Ajouter ' ?> Créneau Horaire</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisie des données du module Emplois du Temps</p>
        </div>
        <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <?php
          $currentClasse = $item['classe_code'] ?? ($selectedClasseCode ?? '');
          $activeClasseLibelle = $activeClasseLibelle ?? '';
          if (empty($activeClasseLibelle) && !empty($currentClasse)) {
              foreach ($classes as $c) {
                  if ($c['code_classe'] == $currentClasse) {
                      $activeClasseLibelle = $c['libelle_classe'];
                      break;
                  }
              }
          }
        ?>
        <!-- BANDEAU INFORMATION CLASSE EN COURS -->
        <div id="active-class-banner" style="background: #F0F9FF; border: 1.5px solid #BAE6FD; border-radius: 10px; padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; transition: all 0.3s ease;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: 8px; background: #0284C7; color: #FFFFFF; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 800;">
              <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #0284C7;">Classe en cours de planification</div>
              <div id="active-class-title" style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 1px;">
                <?= !empty($activeClasseLibelle) ? htmlspecialchars($activeClasseLibelle) : 'Aucune classe sélectionnée' ?>
              </div>
            </div>
          </div>
          <span id="active-class-badge" class="badge" style="background: #1E3A5F; color: #FFFFFF; font-size: 12px; padding: 6px 14px; border-radius: 6px; font-weight: 700; display: <?= !empty($activeClasseLibelle) ? 'inline-block' : 'none' ?>;">
            <i data-lucide="bookmark-check" style="width: 14px; height: 14px; display: inline-block; vertical-align: text-bottom; margin-right: 4px;"></i> Session active
          </span>
        </div>

        <!-- WIDGET DE PREVIEW INTERACTIF EN DIRECT -->
        <div id="schedule-preview-card" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; margin-bottom: 28px;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <i data-lucide="calendar" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Aperçu Hebdomadaire en Direct</h3>
              <span id="preview-loading-spinner" style="display: none; font-size: 12px; color: #2563EB; font-weight: 600;">
                <i data-lucide="loader-2" class="spin" style="width: 14px; height: 14px; display: inline-block;"></i> Chargement...
              </span>
            </div>
            <!-- Boutons de Mode d'Aperçu & Réinitialisation -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <div style="display: flex; background: #E2E8F0; border-radius: 8px; padding: 3px;">
                <button type="button" id="btn-view-class" class="btn-toggle-preview active" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s; background: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="layout-grid" style="width: 14px; height: 14px;"></i> Planning Classe
                </button>
                <button type="button" id="btn-view-teacher" class="btn-toggle-preview" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s; background: transparent; color: #475569; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="user-check" style="width: 14px; height: 14px;"></i> Planning Enseignant
                </button>
              </div>
              <button type="button" id="btn-reset-schedule" class="btn btn-sm btn-outline-danger" style="font-size: 11px; font-weight: 700; border-radius: 6px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #FCA5A5; background: #FEF2F2; color: #DC2626;" title="Vider tout l'emploi du temps de cette classe">
                <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i> Vider Planning
              </button>
            </div>
          </div>

          <!-- GRILLE DES 6 JOURS DE LA SEMAINE -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: 10px; width: 100%;">
            <?php foreach (['lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi', 'vendredi' => 'Vendredi', 'samedi' => 'Samedi'] as $dayKey => $dayLabel): ?>
              <div class="day-preview-col" data-day="<?= $dayKey ?>" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px; min-height: 140px; display: flex; flex-direction: column;">
                <div style="font-weight: 800; font-size: 12px; color: #1E3A5F; border-bottom: 2px solid #CBD5E1; padding-bottom: 6px; margin-bottom: 8px; text-align: center; text-transform: uppercase; letter-spacing: 0.5px;">
                  <?= $dayLabel ?>
                </div>
                <div class="day-slots-container" id="day-slots-<?= $dayKey ?>" style="display: flex; flex-direction: column; gap: 6px; flex-grow: 1;">
                  <!-- Les cartes de cours existants seront injectées en JS -->
                </div>
                <!-- Bloc dynamique représentant la saisie en cours (Live Draft Slot) -->
                <div class="draft-slot-box" id="draft-slot-<?= $dayKey ?>" style="display: none; background: #EFF6FF; border: 1.5px dashed #2563EB; border-radius: 6px; padding: 6px 8px; font-size: 11px; margin-top: 6px; transition: all 0.2s ease;">
                  <div style="font-weight: 800; color: #1E40AF; display: flex; align-items: center; gap: 4px;">
                    <i data-lucide="clock" style="width: 12px; height: 12px;"></i> <span class="draft-slot-time">00:00 - 00:00</span>
                  </div>
                  <div class="draft-slot-mat" style="font-weight: 700; color: #1E3A5F; margin-top: 2px;">Nouveau Créneau</div>
                  <div class="draft-slot-room" style="color: #64748B; font-size: 10px;">-</div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <form action="<?= RACINE ?>emploi/<?= !empty($item['id_emploi']) ? 'edit' : 'add' ?>" method="POST" id="form-emploi" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_emploi'])): ?>
            <input type="hidden" name="id_emploi" value="<?= $item['id_emploi'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe concernée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_cls_et" style="width: 100%;" name="classe_code" required>
                <option value="">-- Rechercher une classe --</option>
                <?php foreach($classes as $cl): ?>
                  <option value="<?= $cl['code_classe'] ?>" <?= ($currentClasse == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matière dispensée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_mat_et" style="width: 100%;" name="matiere_code" required>
                <option value="">-- Rechercher une matière --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= $m['code_matiere'] ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>><?= htmlspecialchars($m['libelle_matiere']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Enseignant responsable <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_ens_et" style="width: 100%;" name="enseignant_code" required>
                <option value="">-- Rechercher un enseignant --</option>
                <?php foreach($enseignants as $ens): ?>
                  <?php 
                    $nomProf = trim(($ens['nom_enseignant'] ?? '') . ' ' . ($ens['prenom_enseignant'] ?? ''));
                    $gradeProf = !empty($ens['grade_enseignant']) ? " ({$ens['grade_enseignant']})" : '';
                    $labelProf = $nomProf ?: ($ens['code_enseignant'] ?? 'Enseignant');
                  ?>
                  <option value="<?= $ens['code_enseignant'] ?>" <?= (($item['enseignant_code'] ?? '') == $ens['code_enseignant']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($labelProf . $gradeProf) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div id="teacher-assigned-badge" style="margin-top: 4px; display: none;"></div>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Salle de cours <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_sal_et" style="width: 100%;" name="salle_code" required>
                <option value="">-- Rechercher une salle --</option>
                <?php foreach($salles as $s): ?>
                  <option value="<?= $s['code_salle'] ?>" <?= (($item['salle_code'] ?? '') == $s['code_salle']) ? 'selected' : '' ?>><?= htmlspecialchars($s['libelle_salle']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="schedule-timing-row" style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; width: 100%;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Jour de la semaine <span style="color: #EF4444;">*</span></label>
                <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="jour" required>
                  <?php foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $j): ?>
                    <option value="<?= strtolower($j) ?>" <?= (strtolower($item['jour'] ?? '') === strtolower($j)) ? 'selected' : '' ?>><?= $j ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Heure de début <span style="color: #EF4444;">*</span></label>
                <input type="time" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="heure_debut" value="<?= htmlspecialchars($item['heure_debut'] ?? '') ?>" placeholder="Ex: 08:00" required>
              </div>
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Heure de fin <span style="color: #EF4444;">*</span></label>
                <input type="time" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="heure_fin" value="<?= htmlspecialchars($item['heure_fin'] ?? '') ?>" placeholder="Ex: 11:30" required>
              </div>
            </div>
          </div>

          <!-- BANDEAU DYNAMIQUE DE DÉTECTION DES CONFLITS -->
          <div id="schedule-conflict-banner" style="display: none; margin-top: 24px; border-radius: 10px; padding: 16px 20px; transition: all 0.3s ease;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
              <div id="conflict-icon-container" style="width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i id="conflict-icon" data-lucide="check-circle" style="width: 22px; height: 22px;"></i>
              </div>
              <div style="flex-grow: 1;">
                <div id="conflict-status-title" style="font-weight: 800; font-size: 14px;">Vérification de la disponibilité</div>
                <div id="conflict-status-subtitle" style="font-size: 13px; margin-top: 3px;">Analyse du créneau horaire...</div>
                <div id="conflict-items-list" style="margin-top: 10px; display: flex; flex-direction: column; gap: 8px;"></div>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <?php if (empty($item['id_emploi'])): ?>
              <button type="submit" name="submit_action" value="save_and_new" id="btn-submit-emploi-new" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Enregistrer & Créer un autre créneau pour cette classe
              </button>
              <button type="submit" name="submit_action" value="save" id="btn-submit-emploi" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">
                Enregistrer & Fermer
              </button>
            <?php else: ?>
              <button type="submit" name="submit_action" value="save" id="btn-submit-emploi" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <?php endif; ?>
            <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script type="application/json" id="emplois-temps-edit-config">
<?= json_encode([
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
$pageScripts = ['public/assets/js/modules/emplois_temps_edit.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
