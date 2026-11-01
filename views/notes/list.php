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
                <th style="padding: 12px 14px; text-align: right; width: 130px;">Actions</th>
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
<div id="modal-details-notes" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); z-index: 9999; justify-content: center; align-items: center; padding: 16px; overflow-y: auto;">
  <div style="max-width: 920px; width: 100%; margin: auto; max-height: calc(100vh - 40px); display: flex; flex-direction: column; background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
    
    <!-- En-tête Modale -->
    <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; gap: 16px;">
      <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #DBEAFE;">
          <i data-lucide="clipboard-check" style="width: 22px; height: 22px;"></i>
        </div>
        <div style="min-width: 0; flex: 1;">
          <h5 id="modal-compo-title" style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            Détails des Notes
          </h5>
          <div id="modal-compo-subtitle" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 6px;">
            <span style="font-size: 13px; color: #64748B;">Classe : - &bull; Matière : -</span>
          </div>
        </div>
      </div>
      <button type="button" class="btn-close-details-modal" aria-label="Fermer" style="background: #F1F5F9; border: 1px solid #E2E8F0; font-size: 20px; cursor: pointer; color: #64748B; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="Fermer la fenêtre">&times;</button>
    </div>

    <div style="padding: 24px; overflow-y: auto; flex: 1;">
      <!-- Loading Spinner -->
      <div id="modal-loading-spinner" style="text-align: center; padding: 50px 20px; display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 36px; height: 36px;"></div>
        <p style="margin-top: 12px; font-size: 13px; font-weight: 600; color: #64748B;">Chargement des notes de la classe...</p>
      </div>

      <!-- Content Container -->
      <div id="modal-details-content">
        
        <!-- Cartouches KPIs (Strictement sur 1 seule ligne avec icônes et couleurs dédiées) -->
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px;">
          
          <!-- Total Inscrits -->
          <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 12px; min-width: 0;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Total Inscrits</div>
              <div style="font-size: 20px; font-weight: 900; color: #0F172A; line-height: 1.2; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="md-total-etudiants">0</div>
              <div style="font-size: 10.5px; color: #94A3B8; font-weight: 600; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Effectif classe</div>
            </div>
          </div>

          <!-- Notes Saisies -->
          <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 12px; min-width: 0;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Copies Saisies</div>
              <div style="font-size: 20px; font-weight: 900; color: #1D4ED8; line-height: 1.2; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="md-nb-notes">0</div>
              <div style="font-size: 10.5px; color: #94A3B8; font-weight: 600; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="md-progression-text">Progression saisie</div>
            </div>
          </div>

          <!-- Moyenne Classe -->
          <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 12px; min-width: 0;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="award" style="width: 20px; height: 20px;"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Moyenne Classe</div>
              <div style="font-size: 20px; font-weight: 900; color: #059669; line-height: 1.2; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="md-moyenne-classe">-</div>
              <div style="font-size: 10.5px; color: #94A3B8; font-weight: 600; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Barème / 20</div>
            </div>
          </div>

          <!-- Taux de Réussite -->
          <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 12px; min-width: 0;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="trending-up" style="width: 20px; height: 20px;"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Taux de Réussite</div>
              <div style="font-size: 20px; font-weight: 900; color: #D97706; line-height: 1.2; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="md-taux-reussite">-</div>
              <div style="font-size: 10.5px; color: #94A3B8; font-weight: 600; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Notes &ge; 10/20</div>
            </div>
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
                <th style="padding: 10px 14px; text-align: center;">Rang</th>
                <th style="padding: 10px 14px; text-align: center;">Mention</th>
                <th style="padding: 10px 14px;">Observations</th>
              </tr>
            </thead>
            <tbody id="md-students-list"></tbody>
          </table>
        </div>

      </div>
    </div>

    <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
      <button type="button" class="btn btn-secondary btn-close-details-modal" style="border-radius: 8px; font-weight: 700; padding: 8px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
        Fermer
      </button>
      <div style="display: flex; gap: 10px; align-items: center;">
        <a href="#" id="md-btn-go-print" target="_blank" class="btn btn-outline-secondary" style="border: 1px solid #CBD5E1; color: #1E293B; background: #FFFFFF; border-radius: 8px; font-weight: 700; padding: 8px 16px; display: inline-flex; align-items: center; gap: 8px;" title="Imprimer la fiche de notes (PDF)">
          <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer (PDF)
        </a>
        <a href="#" id="md-btn-go-saisie" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; border-radius: 8px; font-weight: 700; padding: 8px 20px; display: inline-flex; align-items: center; gap: 8px; color: #FFFFFF; text-decoration: none;">
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