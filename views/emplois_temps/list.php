<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? (Session::get('annee_active_code', ''));
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
            <i data-lucide="calendar-days" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Emplois du Temps
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Registre et suivi des plannings de cours de l'année académique active</p>
        </div>
        <a href="<?= RACINE ?>emploi/formulaire" id="btn-add-emploi" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Programmer un Emploi du Temps
        </a>
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

      <!-- Datatable classes créées cette année -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-emplois_temps" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 50px;">#</th>
                <th style="padding: 12px 14px;">Classe & Niveau</th>
                <th style="padding: 12px 14px;">Volume & Créneaux</th>
                <th style="padding: 12px 14px;">Matières & Enseignants</th>
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

<?php
$pageScripts = ['public/assets/js/modules/emplois_temps_list.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
