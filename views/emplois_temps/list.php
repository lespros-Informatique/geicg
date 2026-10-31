<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$niveaux = $niveaux ?? [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE D'IMPRESSION (VISIBLE UNIQUEMENT À L'IMPRESSION DIRECTE DU NAVIGATEUR) -->
      <div id="print-header-banner" style="margin-bottom: 16px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">GEICG - GROUPE ÉCOLE D'INGÉNIEURS ET DE COMMERCE DE GAGNOA</h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">
              <?= !empty($isTeacherOnly) ? 'MON EMPLOI DU TEMPS - RELEVÉ INDIVIDUEL DES COURS' : 'RÉPERTOIRE GÉNÉRAL DES EMPLOIS DU TEMPS' ?>
            </div>
            <div style="font-size: 11px; color: #475569; margin-top: 3px;" id="print-filter-summary-text">
              Année Académique : <?= htmlspecialchars($selectedAnneeCode ?? 'Active') ?> &bull; Registre officiel
            </div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(trim((Auth::fullName() ?: (!empty($isTeacherOnly) ? 'Enseignant' : 'Direction des Études')))) ?></strong></div>
          </div>
        </div>
      </div>

      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="calendar-days" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            <?= !empty($isTeacherOnly) ? 'Mon Emploi du Temps' : 'Emplois du Temps' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            <?= !empty($isTeacherOnly) ? 'Consultation de mes créneaux et cours hebdomadaires par classe' : 'Registre et suivi des plannings de cours de l\'année académique active' ?>
          </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;" class="no-print">
          <a href="<?= RACINE ?>emploi/imprimerPdf<?= !empty($selectedAnneeCode) ? ('?annee_code=' . urlencode($selectedAnneeCode)) : '' ?>" id="btn-print-emplois" target="_blank" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" title="Imprimer le registre des emplois du temps en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </a>
          <?php if (!empty($canManage)): ?>
          <a href="<?= RACINE ?>emploi/formulaire" id="btn-add-emploi" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Programmer un Emploi du Temps
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: center;">
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
        </div>
      </div>

      <!-- CARTES KPI DE STATISTIQUES DES EMPLOIS DU TEMPS -->
      <div class="kpi-section no-print" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <!-- Carte 1 : Classes -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="<?= !empty($isTeacherOnly) ? 'graduation-cap' : 'calendar-days' ?>" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
              <?= !empty($isTeacherOnly) ? 'Mes Classes' : 'Classes Planifiées' ?>
            </div>
            <div style="font-size: 20px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-classes">
              <?= (int)($stats['total_classes'] ?? 0) ?>
            </div>
            <small style="font-size: 11px; color: #94A3B8; font-weight: 600;">
              <?= !empty($isTeacherOnly) ? 'Classes assignées' : 'Classes avec planning' ?>
            </small>
          </div>
        </div>

        <!-- Carte 2 : Volume Horaire -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
              <?= !empty($isTeacherOnly) ? 'Mon Volume Horaire' : 'Volume Horaire Total' ?>
            </div>
            <div style="font-size: 20px; font-weight: 900; color: #4338CA; margin-top: 2px;" id="kpi-total-heures">
              <?= htmlspecialchars($stats['total_heures_formatted'] ?? '0h00') ?>
            </div>
            <small style="font-size: 11px; color: #94A3B8; font-weight: 600;">Heures / semaine</small>
          </div>
        </div>

        <?php if (!empty($isTeacherOnly)): ?>
        <!-- Carte 3 Enseignant : Créneaux -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calendar-check-2" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Créneaux de Cours</div>
            <div style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-total-creneaux">
              <?= (int)($stats['total_creneaux'] ?? 0) ?>
            </div>
            <small style="font-size: 11px; color: #94A3B8; font-weight: 600;">Séances hebdomadaires</small>
          </div>
        </div>

        <!-- Carte 4 Enseignant : Matières -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="book-open" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Matières Dispensées</div>
            <div style="font-size: 20px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-total-matieres">
              <?= (int)($stats['total_matieres'] ?? 0) ?>
            </div>
            <small style="font-size: 11px; color: #94A3B8; font-weight: 600;">Matières distinctes</small>
          </div>
        </div>
        <?php else: ?>
        <!-- Carte 3 Gestionnaire : Plannings Complets -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="check-circle-2" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Plannings Complets</div>
            <div style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-plannings-complets">
              <?= (int)($stats['plannings_complets'] ?? 0) ?>
            </div>
            <small style="font-size: 11px; color: #94A3B8; font-weight: 600;">&ge; 5 jours planifiés</small>
          </div>
        </div>

        <!-- Carte 4 Gestionnaire : Plannings en Cours -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="hourglass" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">En Cours de Saisie</div>
            <div style="font-size: 20px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-plannings-brouillons">
              <?= (int)($stats['plannings_brouillons'] ?? 0) ?>
            </div>
            <small style="font-size: 11px; color: #94A3B8; font-weight: 600;">Plannings partiels</small>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <!-- Datatable classes créées cette année -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-emplois_temps" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 50px;">#</th>
                <th style="padding: 12px 14px;">Classe & Niveau</th>
                <th style="padding: 12px 14px;">Volume & Créneaux</th>
                <th style="padding: 12px 14px;"><?= !empty($isTeacherOnly) ? 'Matière(s) dispensée(s)' : 'Matières & Enseignants' ?></th>
                <th style="padding: 12px 14px;">Statut Planning</th>
                <th style="padding: 12px 14px;">Dernière MàJ</th>
                <th style="padding: 12px 14px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Overlay Visualisation & Impression Emploi du Temps Classe -->
<div id="modalViewSchedule" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(3px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 1150px; max-height: 90vh; box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column; animation: slideDown 0.25s ease;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 18px 26px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
      <div>
        <h3 style="font-size: 18px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px;">
          <i data-lucide="calendar" style="width: 22px; height: 22px; color: #38BDF8;"></i>
          Emploi du Temps : <span id="modal-classe-title" style="color: #38BDF8;">-</span>
        </h3>
        <small id="modal-niveau-subtitle" style="color: #94A3B8; font-size: 12.5px; font-weight: 500; margin-top: 2px; display: block;">Niveau -</small>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <button type="button" class="btn-print-schedule" onclick="printSchedule()" style="background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%); color: #FFFFFF; border: none; font-weight: 700; border-radius: 8px; padding: 8px 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(2,132,199,0.35); transition: all 0.2s ease; cursor: pointer;">
          <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer l'Emploi du Temps
        </button>
        <button type="button" class="btn-close-modal" style="background: rgba(255,255,255,0.15); border: none; color: #FFFFFF; font-size: 20px; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;" title="Fermer">&times;</button>
      </div>
    </div>

    <!-- Body -->
    <div id="printable-schedule-area" style="padding: 24px; background: #F8FAFC; flex: 1; overflow-y: auto;">
      
      <!-- En-tête  visible uniquement à l'impression -->
      <div class="print-only-header" style="display: none; text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1E3A5F; padding-bottom: 14px;">
        <h2 style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin: 0; text-transform: uppercase;">GROUPE ÉCOLE D'INGÉNIEURS GEICG</h2>
        <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 6px 0 0 0;">EMPLOI DU TEMPS : <span id="print-classe-name" style="color: #0284C7;">-</span></h3>
        <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">Registre Général des Cours • Année Académique Active</p>
      </div>

      <div id="schedule-matrix-loader" style="text-align: center; padding: 50px;">
        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"><span class="visually-hidden">Chargement...</span></div>
        <p style="margin-top: 12px; color: #64748B; font-weight: 700; font-size: 14px;">Chargement de la grille horaire...</p>
      </div>
      <div id="schedule-matrix-content" style="display: none;"></div>
    </div>

    <!-- Footer -->
    <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 14px 26px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
      <span style="font-size: 12px; color: #64748B; font-weight: 600;">Registre GEICG — Mode Grille Hebdomadaire</span>
      <button type="button" class="btn btn-secondary btn-close-modal" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; font-size: 13px;">Fermer</button>
    </div>
  </div>
</div>

<script type="application/json" id="emplois-temps-list-config">
<?= json_encode([
    'racine' => RACINE,
    'canManage' => !empty($canManage),
    'isTeacherOnly' => !empty($isTeacherOnly),
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/emplois_temps.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
