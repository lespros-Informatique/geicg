<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$annees = $annees ?? [];
$semestres = $semestres ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? '';
$isTeacherOnly = !empty($isTeacherOnly);
$canGenerateBulletins = isset($canGenerateBulletins) ? (bool)$canGenerateBulletins : true;
$canViewBulletins = isset($canViewBulletins) ? (bool)$canViewBulletins : true;
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
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">REGISTRE & SYNTHÈSE DES BULLETINS DE NOTES</div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(trim(Auth::fullName() ?: (!empty($isTeacherOnly) ? 'Enseignant' : 'Direction des Études'))) ?></strong></div>
          </div>
        </div>
      </div>

      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <i data-lucide="award" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Bulletins & PV de Notes
            <?php if ($isTeacherOnly): ?>
              <span class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
                <i data-lucide="user-check" style="width: 14px; height: 14px;"></i> Mes Classes
              </span>
            <?php endif; ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Consultation et impression des relevés de notes
          </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url() ?>bulletin/imprimerRegistrePdf<?= !empty($selectedAnneeCode) ? ('?annee_code=' . urlencode($selectedAnneeCode)) : '' ?>" id="btn-print-registre-bulletin" target="_blank" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; cursor: pointer; transition: all 0.2s;" title="Générer et imprimer le registre officiel des bulletins en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer Registre (PDF)
          </a>
          <a href="javascript:void(0);" id="btn-print-classe-bulletins" target="_blank" class="btn btn-outline-primary" style="border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; opacity: 0.5; cursor: not-allowed; pointer-events: none; transition: all 0.2s;" title="Veuillez sélectionner une classe pour imprimer les bulletins de toute la classe.">
            <i data-lucide="files" style="width: 18px; height: 18px;"></i> Imprimer Bulletins Classe
          </a>
          <a href="<?= url('bulletin/pvClasse') ?>" id="btn-pv-classe" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);" title="Consulter le Procès-Verbal récapitulatif de la classe sélectionnée">
            <i data-lucide="file-spreadsheet" style="width: 18px; height: 18px;"></i> Procès-Verbal (PV) de Classe
          </a>
        </div>
      </div>

      <!-- KPI CARDS : STATISTIQUES DES ÉTUDIANTS ET BULLETINS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <!-- Total Inscrits -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Inscrits</div>
            <div style="font-size: 20px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-inscrits">0</div>
          </div>
        </div>

        <!-- Bulletins Disponibles (Avec Notes) -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="file-check" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Bulletins Évalués</div>
            <div style="font-size: 20px; font-weight: 900; color: #4338CA; margin-top: 2px;" id="kpi-bulletins-dispo">0</div>
          </div>
        </div>

        <!-- Admis (Moyenne >= 10/20) -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Admis (&ge; 10 / 20)</div>
            <div style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-moyennes-admis">0</div>
          </div>
        </div>

        <!-- En Attente / 0 Note -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clock" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">En Attente (0 Note)</div>
            <div style="font-size: 20px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-en-attente">0</div>
          </div>
        </div>

      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: center;">
          
          <!-- Année Académique -->
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

          <!-- Semestre -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="clock" style="width: 14px; height: 14px; color: #64748B;"></i> Semestre
            </label>
            <select id="filter-semestre" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les semestres --</option>
              <?php foreach ($semestres as $s): ?>
                <?php $isCloture = (($s['statut_semestre'] ?? '') === 'cloture'); ?>
                <option value="<?= htmlspecialchars($s['code_semestre']) ?>">
                  <?= htmlspecialchars($s['libelle_semestre']) ?><?= $isCloture ? ' (Clôturé)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Niveau d'Études -->
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

          <!-- Classe (Obligatoire pour charger la liste) -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="graduation-cap" style="width: 14px; height: 14px; color: #1E3A5F;"></i> Classe <span style="color: #EF4444; font-weight: 800;">*</span>
            </label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Choisir une classe --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>

      <!-- Alerte Invitation Sélection Classe -->
      <div id="alert-select-classe" style="background: #EFF6FF; border: 1.5px dashed #93C5FD; border-radius: 12px; padding: 18px 24px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px; color: #1E3A5F;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: #DBEAFE; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <i data-lucide="graduation-cap" style="width: 24px; height: 24px;"></i>
        </div>
        <div>
          <div style="font-size: 14px; font-weight: 800; color: #1E3A5F;">Sélectionnez une classe pour afficher les bulletins</div>
          <div style="font-size: 13px; color: #64748B; margin-top: 3px;">
            Par défaut, aucun résultat n'est affiché. Veuillez sélectionner une <strong>Classe</strong> ci-dessus pour charger la liste des étudiants et leurs bulletins semestriels.
          </div>
        </div>
      </div>

      <!-- DataTables Tableau des Inscriptions et Bulletins -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-bulletin" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 45px;">#</th>
                <th style="padding: 12px 14px; width: 140px;">Matricule</th>
                <th style="padding: 12px 14px;">Étudiant</th>
                <th style="padding: 12px 14px; text-align: center; width: 60px;">Sexe</th>
                <th style="padding: 12px 14px; text-align: center;">Moyenne Semestre</th>
                <th style="padding: 12px 14px; text-align: center;">Décision</th>
                <th style="padding: 12px 14px; text-align: right; width: 160px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<?php
ViewContext::setScripts(['public/assets/js/modules/notes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
