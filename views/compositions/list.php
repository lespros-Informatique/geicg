<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$annees = $annees ?? [];
$matieres = $matieres ?? [];
$semestres = $semestres ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? '';
$canManageExamens = isset($canManageExamens) ? (bool)$canManageExamens : true;
$canViewExamens = isset($canViewExamens) ? (bool)$canViewExamens : true;
$canEnterNotes = isset($canEnterNotes) ? (bool)$canEnterNotes : true;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE D'IMPRESSION (VISIBLE UNIQUEMENT À L'IMPRESSION) -->
      <div id="print-header-banner" style="margin-bottom: 16px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">GEICG - GROUPE ÉCOLE D'INGÉNIEURS ET DE COMMERCE DE GAGNOA</h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">PROGRAMMATION & CALENDRIER DES ÉVALUATIONS</div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(trim(Auth::fullName() ?: 'Direction des Études')) ?></strong></div>
          </div>
        </div>
      </div>

      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="award" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Programmation des Examens
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Planning et calendrier des épreuves d'examens semestriels et finaux</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;" class="no-print">
          <button type="button" onclick="window.print()" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; transition: all 0.2s;" title="Imprimer le calendrier des évaluations">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </button>
          <?php if ($canManageExamens): ?>
          <a href="<?= url() ?>composition/formulaire?type=EXAMEN" id="btn-add-composition" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Programmer un Examen
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
              <i data-lucide="bookmark" style="width: 14px; height: 14px; color: #64748B;"></i> Type Compo
            </label>
            <select id="filter-type-compo" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les types --</option>
              <option value="EXAMEN">Examens / Compositions</option>
              <option value="DEVOIR">Devoirs Surveillés</option>
              <option value="INTERROGATION">Interrogations Écrites</option>
              <option value="AUTRE">Autres Évaluations</option>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="tag" style="width: 14px; height: 14px; color: #64748B;"></i> Statut
            </label>
            <select id="filter-statut" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les statuts --</option>
              <option value="programme">Programmé</option>
              <option value="termine">Clôturé / Terminé</option>
              <option value="annule">Annulé</option>
            </select>
          </div>
        </div>
      </div>

      <!-- CARTES KPI DE STATISTIQUES DES EXAMENS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <!-- Total Examens -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="award" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Évaluations</div>
            <div style="font-size: 20px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-compositions">0</div>
          </div>
        </div>

        <!-- Programmés -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clock" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Évaluations Programmés</div>
            <div style="font-size: 20px; font-weight: 900; color: #4338CA; margin-top: 2px;" id="kpi-programme-compositions">0</div>
          </div>
        </div>

        <!-- Clôturés / Terminés -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Évaluations Clôturés</div>
            <div style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-termine-compositions">0</div>
          </div>
        </div>

        <!-- Annulés -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #FEE2E2; color: #B91C1C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="ban" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Évaluations Annulés</div>
            <div style="font-size: 20px; font-weight: 900; color: #B91C1C; margin-top: 2px;" id="kpi-annule-compositions">0</div>
          </div>
        </div>

      </div>

      <!-- DataTables Compositions Programmées -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-compositions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 50px;">#</th>
                <th style="padding: 12px 14px;">Intitulé</th>
                <th style="padding: 12px 14px; text-align: center; width: 95px;">Type Compo</th>
                <th style="padding: 12px 14px;">Semestre</th>
                <th style="padding: 12px 14px; text-align: center;">Coef</th>
                <th style="padding: 12px 14px;">Date Début</th>
                <th style="padding: 12px 14px;">Date Fin</th>
                <th style="padding: 12px 14px; text-align: center; width: 100px;">Statut</th>
                <th style="padding: 12px 14px; text-align: right; width: 180px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<script type="application/json" id="compositions-list-config">
<?= json_encode([
    'csrfToken' => Validator::generateCsrfToken(),
    'canManageExamens' => (bool)$canManageExamens,
    'canViewExamens' => (bool)$canViewExamens,
    'canEnterNotes' => (bool)$canEnterNotes
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/compositions.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
