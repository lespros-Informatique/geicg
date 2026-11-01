<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$annees = $annees ?? [];
$matieres = $matieres ?? [];
$semestres = $semestres ?? [];
$compositions = $compositions ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? '';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="award" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Registre des Évaluations
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Synthèse des sessions d'évaluations par classe </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <?php if (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::ENTER_NOTES]))): ?>
          <a href="<?= RACINE ?>note/saisieClasse" id="btn-saisie-classe" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
            <i data-lucide="edit-3" style="width: 18px; height: 18px;"></i> Saisie des Notes
          </a>
          <a href="<?= RACINE ?>composition/formulaire?type=EXAMEN" class="btn btn-outline-secondary" style="border-color: #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; background: #FFFFFF;">
            <i data-lucide="calendar-plus" style="width: 18px; height: 18px;"></i> Planifier un Examen
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i> Année Académique
            </label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="clock" style="width: 14px; height: 14px; color: #64748B;"></i> Semestre
            </label>
            <select id="filter-semestre" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les semestres --</option>
              <?php foreach ($semestres as $s): ?>
                <option value="<?= htmlspecialchars($s['code_semestre']) ?>"><?= htmlspecialchars($s['libelle_semestre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="layers" style="width: 14px; height: 14px; color: #64748B;"></i> Niveau d'Études
            </label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="graduation-cap" style="width: 14px; height: 14px; color: #64748B;"></i> Classe
            </label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="tag" style="width: 14px; height: 14px; color: #64748B;"></i> Type Évaluation
            </label>
            <select id="filter-type-eval" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les types --</option>
              <option value="EXAMEN">Examens / Compositions</option>
              <option value="DEVOIR">Devoirs Surveillés</option>
              <option value="INTERROGATION">Interrogations Écrites</option>
              <option value="AUTRE">Autres Évaluations</option>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="file-text" style="width: 14px; height: 14px; color: #64748B;"></i> Libellé Compo
            </label>
            <select id="filter-compo" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les compos --</option>
              <?php foreach ($compositions as $cp): ?>
                <option value="<?= htmlspecialchars($cp['code_composition']) ?>" data-type="<?= htmlspecialchars($cp['type_composition'] ?? '') ?>">
                  <?= htmlspecialchars($cp['libelle_composition']) ?> (<?= htmlspecialchars($cp['type_composition'] ?? 'Évaluation') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="book-open" style="width: 14px; height: 14px; color: #64748B;"></i> Matière
            </label>
            <select id="filter-matiere" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les matières --</option>
              <?php foreach ($matieres as $m): ?>
                <option value="<?= htmlspecialchars($m['code_matiere']) ?>"><?= htmlspecialchars($m['libelle_matiere']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- KPI CARDS : STATISTIQUES DES ÉVALUATIONS PAR CLASSE -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <!-- Total Sessions -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clipboard-list" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Sessions Totales</div>
            <div style="font-size: 20px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-sessions">0</div>
          </div>
        </div>

        <!-- Examens -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="award" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Épreuves d'Examens</div>
            <div style="font-size: 20px; font-weight: 900; color: #4338CA; margin-top: 2px;" id="kpi-total-examens">0</div>
          </div>
        </div>

        <!-- Devoirs & Interros -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="file-edit" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Devoirs & Interros</div>
            <div style="font-size: 20px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-total-controles">0</div>
          </div>
        </div>

        <!-- Saisies Complètes -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Saisies Complètes</div>
            <div style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-total-complets">0</div>
          </div>
        </div>

      </div>

      <!-- DataTables Sessions d'Évaluations par Classe -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-notes" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 45px;">#</th>
                <th style="padding: 12px 14px;">Classe</th>
                <th style="padding: 12px 14px;">Évaluation / Compo</th>
                <th style="padding: 12px 14px; text-align: center;">Type Évaluation</th>
                <th style="padding: 12px 14px;">Matière</th>
                <th style="padding: 12px 14px;">Semestre</th>
                <th style="padding: 12px 14px; text-align: center; width: 70px;">Coef</th>
                <th style="padding: 12px 14px; text-align: center;">Progression Saisie</th>
                <th style="padding: 12px 14px; text-align: center;">Moyenne Classe</th>
                <th style="padding: 12px 14px; text-align: right; width: 90px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- MODALE DÉTAILS DES NOTES DE LA CLASSE -->
<div class="modal fade" id="modal-details-notes" tabindex="-1" aria-labelledby="modalDetailsNotesLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 850px;">
    <div class="modal-content" style="border-radius: 14px; border: 1px solid #E2E8F0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
      
      <div class="modal-header" style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 18px 24px; border-top-left-radius: 14px; border-top-right-radius: 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h5 class="modal-title" id="modalDetailsNotesLabel" style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="award" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
            <span id="modal-compo-title">Détails des Notes</span>
          </h5>
          <p id="modal-compo-subtitle" style="font-size: 13px; color: #64748B; margin: 3px 0 0 0;">Classe : - &bull; Matière : -</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748B;">&times;</button>
      </div>

      <div class="modal-body" style="padding: 24px;">
        <!-- Loading Spinner -->
        <div id="modal-loading-spinner" style="text-align: center; padding: 40px; display: none;">
          <div class="spinner-border text-primary" role="status" style="width: 32px; height: 32px;"></div>
          <p style="margin-top: 10px; font-size: 13px; color: #64748B;">Chargement des notes de la classe...</p>
        </div>

        <!-- Content Container -->
        <div id="modal-details-content">
          
          <!-- Mini KPIs Grid -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Inscrits</span>
              <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 2px;" id="md-total-etudiants">0</div>
            </div>
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 12px 14px;">
              <span style="font-size: 11px; font-weight: 700; color: #1D4ED8; text-transform: uppercase;">Notes Saisies</span>
              <div style="font-size: 18px; font-weight: 800; color: #1D4ED8; margin-top: 2px;" id="md-nb-notes">0</div>
            </div>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 12px 14px;">
              <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Moyenne Classe</span>
              <div style="font-size: 18px; font-weight: 800; color: #15803D; margin-top: 2px;" id="md-moyenne-classe">-</div>
            </div>
            <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 12px 14px;">
              <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Taux de Réussite</span>
              <div style="font-size: 18px; font-weight: 800; color: #7E22CE; margin-top: 2px;" id="md-taux-reussite">-</div>
            </div>
          </div>

          <!-- Students Marks Table -->
          <div style="border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden;">
            <table class="table" style="width: 100%; margin: 0; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; color: #64748B; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                  <th style="padding: 10px 14px; width: 40px;">#</th>
                  <th style="padding: 10px 14px;">Matricule</th>
                  <th style="padding: 10px 14px;">Nom & Prénom(s)</th>
                  <th style="padding: 10px 14px; text-align: center;">Note / 20</th>
                  <th style="padding: 10px 14px; text-align: center;">Mention</th>
                  <th style="padding: 10px 14px;">Observations</th>
                </tr>
              </thead>
              <tbody id="md-students-list"></tbody>
            </table>
          </div>

        </div>
      </div>

      <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 24px; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px; display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 700; padding: 8px 18px;">
          Fermer
        </button>
        <a href="#" id="md-btn-go-saisie" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; border-radius: 8px; font-weight: 700; padding: 8px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Saisir / Modifier les Notes
        </a>
      </div>

    </div>
  </div>
</div>

<script type="application/json" id="notes-list-config">
<?= json_encode([
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/notes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>