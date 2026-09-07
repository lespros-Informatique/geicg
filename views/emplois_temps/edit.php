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
<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_cls_et').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
    $('#sel_mat_et').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_ens_et').select2({ placeholder: "-- Rechercher un enseignant --", allowClear: true, width: '100%' });
    $('#sel_sal_et').select2({ placeholder: "-- Rechercher une salle --", allowClear: true, width: '100%' });
  }

  // 1. Auto-Sélection Enseignant sur choix Classe + Matière
  function checkAndAutoSelectTeacher() {
    var classeCode = $('#sel_cls_et').val();
    var matiereCode = $('#sel_mat_et').val();

    if (!classeCode || !matiereCode) {
      $('#teacher-assigned-badge').slideUp(150);
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>emploi/getAssignedTeacher',
      type: 'GET',
      data: { classe_code: classeCode, matiere_code: matiereCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data && res.data.enseignant_code) {
          var ensCode = res.data.enseignant_code;
          var currentVal = $('#sel_ens_et').val();
          if (currentVal !== ensCode) {
            $('#sel_ens_et').val(ensCode).trigger('change');
          }
          $('#teacher-assigned-badge').html(
            '<div style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1E3A5F; font-size:12px; font-weight:700; padding:6px 12px; border-radius:6px; margin-top:6px; display:flex; align-items:center; gap:6px;">' +
            '<i data-lucide="sparkles" style="width:14px;height:14px;color:#2563EB;"></i> Enseignant titulaire détecté : ' + res.data.nom_complet + ' (' + (res.data.grade || 'Titulaire') + ')' +
            '</div>'
          ).slideDown(200);
          if (window.lucide) lucide.createIcons();
        } else {
          $('#teacher-assigned-badge').slideUp(150);
        }
      }
    });
  }

  // 2. Contrôle de Conflits en Temps Réel
  var conflictTimer = null;
  function checkScheduleConflicts() {
    clearTimeout(conflictTimer);
    conflictTimer = setTimeout(function() {
      var classeCode = $('#sel_cls_et').val();
      var salleCode = $('#sel_sal_et').val();
      var enseignantCode = $('#sel_ens_et').val();
      var jour = $('select[name="jour"]').val();
      var heureDebut = $('input[name="heure_debut"]').val();
      var heureFin = $('input[name="heure_fin"]').val();
      var idEmploi = $('input[name="id_emploi"]').val() || '';

      if (!jour || !heureDebut || !heureFin) {
        $('#schedule-conflict-banner').slideUp(200);
        return;
      }

      $.ajax({
        url: '<?= RACINE ?>emploi/checkScheduleConflicts',
        type: 'GET',
        data: {
          classe_code: classeCode,
          salle_code: salleCode,
          enseignant_code: enseignantCode,
          jour: jour,
          heure_debut: heureDebut,
          heure_fin: heureFin,
          id_emploi: idEmploi
        },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1) {
            var banner = $('#schedule-conflict-banner');
            var iconBox = $('#conflict-icon-container');
            var icon = $('#conflict-icon');
            var title = $('#conflict-status-title');
            var subtitle = $('#conflict-status-subtitle');
            var list = $('#conflict-items-list');

            list.empty();

            if (res.has_conflict && res.conflicts && res.conflicts.length > 0) {
              banner.css({ 'background': '#FEF2F2', 'border': '1.5px solid #FCA5A5' });
              iconBox.css({ 'background': '#FEE2E2', 'color': '#DC2626' });
              icon.attr('data-lucide', 'alert-triangle');
              title.css('color', '#991B1B').text('Attention : Conflit d\'emploi du temps détecté !');
              subtitle.css('color', '#B91C1C').text('Ce créneau horaire chevauche un ou plusieurs cours existants :');

              res.conflicts.forEach(function(c) {
                var itemHtml = '<div style="background:#FFFFFF; border:1px solid #FECACA; border-radius:8px; padding:10px 14px; font-size:12.5px; color:#7F1D1D;">' +
                  '<div style="font-weight:800; display:flex; align-items:center; gap:6px; margin-bottom:3px;">' +
                  '<span class="badge" style="background:#DC2626; color:#FFF; font-size:10.5px; padding:2px 6px; border-radius:4px; text-transform:uppercase;">' + c.type + '</span> ' +
                  c.title +
                  '</div>' +
                  '<div>' + c.message + '</div>' +
                  '</div>';
                list.append(itemHtml);
              });

              $('#btn-submit-emploi, #btn-submit-emploi-new').prop('disabled', true).css('opacity', '0.6').attr('title', 'Veuillez résoudre le conflit avant d\'enregistrer');
              banner.stop(true, true).slideDown(250);
            } else {
              banner.css({ 'background': '#F0FDF4', 'border': '1.5px solid #BBF7D0' });
              iconBox.css({ 'background': '#DCFCE7', 'color': '#15803D' });
              icon.attr('data-lucide', 'check-circle-2');
              title.css('color', '#166534').text('Créneau Parfaitement Disponible');
              subtitle.css('color', '#15803D').text('La salle, l\'enseignant et la classe sont 100% libres sur cette plage horaire.');
              $('#btn-submit-emploi, #btn-submit-emploi-new').prop('disabled', false).css('opacity', '1').removeAttr('title');
              banner.stop(true, true).slideDown(250);
            }
            if (window.lucide) lucide.createIcons();
          }
        }
      });
    }, 150);
  }

  // 3. Mise à jour dynamique du bandeau de classe en cours & sync session
  function updateActiveClassBanner() {
    var selText = $('#sel_cls_et option:selected').text();
    var val = $('#sel_cls_et').val();

    if (val && selText && val !== '') {
      $('#active-class-title').text(selText);
      $('#active-class-badge').show();
      
      $.ajax({
        url: '<?= RACINE ?>emploi/setSessionClasse',
        type: 'GET',
        data: { classe_code: val }
      });
    } else {
      $('#active-class-title').text('Aucune classe sélectionnée');
      $('#active-class-badge').hide();
    }
  }

  // 4. Live Schedule Preview Widget Logic
  var activePreviewMode = 'class'; // 'class' ou 'teacher'

  function updateDraftSlotPreview() {
    $('.draft-slot-box').hide();

    var jour = $('select[name="jour"]').val();
    var hDebut = $('input[name="heure_debut"]').val();
    var hFin = $('input[name="heure_fin"]').val();
    var matText = $('#sel_mat_et option:selected').text();
    var salText = $('#sel_sal_et option:selected').text();

    if (jour && hDebut && hFin) {
      var dayKey = jour.toLowerCase();
      var $draftBox = $('#draft-slot-' + dayKey);
      if ($draftBox.length) {
        $draftBox.find('.draft-slot-time').text(hDebut.substring(0,5) + ' - ' + hFin.substring(0,5));
        
        var cleanMat = (matText && matText.indexOf('--') === -1) ? matText : 'Nouveau Créneau';
        var cleanSal = (salText && salText.indexOf('--') === -1) ? salText : '-';
        
        $draftBox.find('.draft-slot-mat').text(cleanMat);
        $draftBox.find('.draft-slot-room').text(cleanSal);
        $draftBox.stop(true, true).fadeIn(150);
        if (window.lucide) lucide.createIcons();
      }
    }
  }

  function loadSchedulePreview() {
    $('.day-slots-container').empty();
    $('#preview-loading-spinner').show();

    var url = '';
    var params = {};

    if (activePreviewMode === 'class') {
      var classeCode = $('#sel_cls_et').val();
      if (!classeCode) {
        $('#preview-loading-spinner').hide();
        return;
      }
      url = '<?= RACINE ?>emploi/apiList';
      params = { classe_code: classeCode };
    } else {
      var ensCode = $('#sel_ens_et').val();
      if (!ensCode) {
        $('#preview-loading-spinner').hide();
        return;
      }
      url = '<?= RACINE ?>emploi/getTeacherSchedule';
      params = { enseignant_code: ensCode };
    }

    $.ajax({
      url: url,
      type: 'GET',
      data: params,
      dataType: 'json',
      success: function(res) {
        $('#preview-loading-spinner').hide();
        var items = res.data || res.items || [];
        var currentId = $('input[name="id_emploi"]').val();

        items.forEach(function(item) {
          if (currentId && (item.id_emploi == currentId || item.id == currentId)) {
            return; // ne pas dupliquer le créneau en cours d'édition
          }

          var dayKey = (item.jour || '').toLowerCase();
          var $container = $('#day-slots-' + dayKey);

          if ($container.length) {
            var hDeb = item.heure_debut ? item.heure_debut.substring(0,5) : '';
            var hFin = item.heure_fin ? item.heure_fin.substring(0,5) : '';
            var mat = item.libelle_matiere || item.matiere_code || 'Cours';
            var sal = item.libelle_salle || item.salle_code || '';
            var extraInfo = (activePreviewMode === 'class') ? (item.nom_prof || '') : (item.libelle_classe || '');

            var cardHtml = '<div class="preview-slot-card" data-id="' + (item.id_emploi || item.id) + '" style="background:#F1F5F9; border-left:3px solid #1E3A5F; border-radius:6px; padding:6px 8px; font-size:10.5px; box-shadow:0 1px 2px rgba(0,0,0,0.03); margin-bottom:4px;">' +
              '<div style="display:flex; justify-content:space-between; align-items:flex-start;">' +
                '<div style="font-weight:800; color:#0F172A; line-height:1.2; padding-right:4px;">' + mat + '</div>' +
                '<button type="button" class="btn-delete-slot" data-id="' + (item.id_emploi || item.id) + '" style="background:none; border:none; color:#EF4444; padding:0; cursor:pointer; opacity:0.8; transition:opacity 0.2s;" title="Supprimer ce créneau">' +
                  '<i data-lucide="trash-2" style="width:12px; height:12px;"></i>' +
                '</button>' +
              '</div>' +
              '<div style="color:#2563EB; font-weight:700; font-size:10px; margin-top:2px;">' + hDeb + ' - ' + hFin + '</div>' +
              '<div style="color:#64748B; font-size:9.5px; margin-top:1px;">' + sal + (extraInfo ? (' • ' + extraInfo) : '') + '</div>' +
              '</div>';

            $container.append(cardHtml);
          }
        });

        updateDraftSlotPreview();
      },
      error: function() {
        $('#preview-loading-spinner').hide();
      }
    });
  }

  // Toggle Mode Aperçu Classe / Enseignant
  $('#btn-view-class').on('click', function() {
    activePreviewMode = 'class';
    $(this).css({ 'background': '#1E3A5F', 'color': '#FFFFFF' }).addClass('active');
    $('#btn-view-teacher').css({ 'background': 'transparent', 'color': '#475569' }).removeClass('active');
    loadSchedulePreview();
  });

  $('#btn-view-teacher').on('click', function() {
    activePreviewMode = 'teacher';
    $(this).css({ 'background': '#1E3A5F', 'color': '#FFFFFF' }).addClass('active');
    $('#btn-view-class').css({ 'background': 'transparent', 'color': '#475569' }).removeClass('active');
    loadSchedulePreview();
  });

  // Événements en Temps Réel (input, keyup, change)
  $('#sel_cls_et').on('change select2:select select2:clear', function() {
    updateActiveClassBanner();
    checkAndAutoSelectTeacher();
    checkScheduleConflicts();
    loadSchedulePreview();
  });

  $('#sel_mat_et').on('change select2:select select2:clear', function() {
    checkAndAutoSelectTeacher();
    checkScheduleConflicts();
    updateDraftSlotPreview();
  });

  $('#sel_ens_et').on('change select2:select select2:clear', function() {
    checkScheduleConflicts();
    if (activePreviewMode === 'teacher') {
      loadSchedulePreview();
    } else {
      updateDraftSlotPreview();
    }
  });

  $('#sel_sal_et, select[name="jour"]').on('change select2:select select2:clear', function() {
    checkScheduleConflicts();
    updateDraftSlotPreview();
  });

  $('input[name="heure_debut"], input[name="heure_fin"]').on('input keyup change blur', function() {
    checkScheduleConflicts();
    updateDraftSlotPreview();
  });

  // 5. Suppression Unitaire d'un Créneau depuis l'aperçu
  $(document).on('click', '.btn-delete-slot', function(e) {
    e.preventDefault();
    var id = $(this).attr('data-id') || $(this).data('id');
    var $card = $(this).closest('.preview-slot-card');

    if (!id) {
      if (window.toastr) toastr.error('ID du créneau introuvable');
      return;
    }

    function performDelete() {
      $.ajax({
        url: '<?= RACINE ?>emploi/delete',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { id: id, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Créneau supprimé avec succès');
            $card.slideUp(200, function() { $(this).remove(); });
            checkScheduleConflicts();
            loadSchedulePreview();
          } else {
            if (window.Swal) {
              Swal.fire({ icon: 'error', title: 'Erreur', text: res.message || 'Erreur lors de la suppression' });
            } else if (window.toastr) toastr.error(res.message || 'Erreur lors de la suppression');
          }
        }
      });
    }

    if (window.Swal) {
      Swal.fire({
        title: 'Supprimer ce créneau ?',
        text: 'Voulez-vous vraiment retirer ce cours du planning ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      }).then(function(result) {
        if (result.isConfirmed) performDelete();
      });
    } else {
      if (confirm('Voulez-vous vraiment supprimer ce créneau horaire ?')) {
        performDelete();
      }
    }
  });

  // 6. Réinitialisation Globale de l'Emploi du Temps d'une Classe
  $('#btn-reset-schedule').on('click', function() {
    var classeCode = $('#sel_cls_et').val();
    var classeName = $('#sel_cls_et option:selected').text();

    if (!classeCode) {
      if (window.Swal) {
        Swal.fire({ icon: 'info', title: 'Information', text: 'Veuillez d\'abord sélectionner une classe.' });
      } else {
        alert('Veuillez d\'abord sélectionner une classe.');
      }
      return;
    }

    function performReset() {
      $.ajax({
        url: '<?= RACINE ?>emploi/resetClasseSchedule',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { classe_code: classeCode, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.Swal) {
              Swal.fire({
                icon: 'success',
                title: 'Emploi du temps réinitialisé !',
                text: 'Tous les créneaux horaires de la classe ont été purgés avec succès.',
                timer: 1800,
                showConfirmButton: false
              });
            } else if (window.toastr) toastr.success(res.message || 'Emploi du temps réinitialisé !');
            loadSchedulePreview();
            checkScheduleConflicts();
          } else {
            if (window.Swal) {
              Swal.fire({ icon: 'error', title: 'Erreur', text: res.message || 'Erreur lors de la réinitialisation' });
            } else if (window.toastr) toastr.error(res.message || 'Erreur lors de la réinitialisation');
          }
        }
      });
    }

    if (window.Swal) {
      Swal.fire({
        title: 'ATTENTION : Vider le planning ?',
        html: 'Voulez-vous vraiment supprimer <b>TOUS</b> les créneaux horaires de la classe <br><b style="color:#DC2626; font-size:16px;">"' + htmlEntities(classeName) + '"</b> ?<br><br><small style="color:#64748B;">Cette action est définitive et irréversible.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, Vider Tout',
        cancelButtonText: 'Annuler'
      }).then(function(result) {
        if (result.isConfirmed) performReset();
      });
    } else {
      if (confirm('ATTENTION : Voulez-vous vraiment supprimer TOUS les créneaux horaires de la classe "' + classeName + '" ?')) {
        performReset();
      }
    }
  });

  function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // 7. Soumission du Formulaire 100% AJAX (Sans rechargement de page)
  $('#form-emploi').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $(document.activeElement);
    var submitAction = $submitBtn.val() || 'save';
    var formData = $form.serializeArray();
    formData.push({ name: 'submit_action', value: submitAction });

    var $btns = $form.find('button[type="submit"]');
    $btns.prop('disabled', true).css('opacity', '0.7');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $.param(formData),
      dataType: 'json',
      success: function(res) {
        $btns.prop('disabled', false).css('opacity', '1');
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Créneau enregistré avec succès !');

          if (submitAction === 'save' && res.redirect) {
            window.location.href = res.redirect;
            return;
          }

          // Enregistrer & Créer un autre (100% AJAX) : Réinitialiser uniquement le créneau
          $('#sel_mat_et').val('').trigger('change.select2');
          $('#sel_sal_et').val('').trigger('change.select2');
          $('input[name="heure_debut"]').val('');
          $('input[name="heure_fin"]').val('');
          $('#schedule-conflict-banner').slideUp(200);

          // Rafraîchir instantanément l'aperçu
          loadSchedulePreview();
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function() {
        $btns.prop('disabled', false).css('opacity', '1');
        if (window.toastr) toastr.error('Erreur réseau lors de l\'enregistrement');
      }
    });
  });

  // Exécution initiale si des champs sont déjà renseignés
  if ($('#sel_cls_et').val()) {
    checkAndAutoSelectTeacher();
    loadSchedulePreview();
  }
  updateDraftSlotPreview();
  if ($('input[name="heure_debut"]').val() && $('input[name="heure_fin"]').val()) {
    checkScheduleConflicts();
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
