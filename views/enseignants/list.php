<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Corps Enseignant & Professeurs</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion des enseignants</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;" class="no-print">
          <button type="button" id="btn-programme-cours" class="btn btn-outline-primary" style="border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; transition: all 0.2s;" title="Afficher le programme des cours du jour ou d'une autre date">
            <i data-lucide="calendar" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Programme des Cours
          </button>
          <a href="<?= RACINE ?>enseignant/imprimerPdf" id="btn-print-enseignants" target="_blank" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;" title="Générer & Imprimer le répertoire des enseignants en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </a>
          <a href="<?= RACINE ?>enseignant/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> Nouvel Enseignant
          </a>
        </div>
      </div>
      <!-- CARTES KPI / STATISTIQUES DU CORPS ENSEIGNANT -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Enseignants -->
        <div class="card kpi-card-filter" data-filter="" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Afficher tous les enseignants">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Enseignants</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-enseignants"><?= (int)($stats['total'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Genre (Hommes / Femmes) -->
        <div class="card kpi-card-filter" data-filter="genre" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Filtrer par genre (cliquer pour basculer Hommes / Femmes)">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #F5F3FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Genre</div>
            <div style="font-size: 20px; font-weight: 900; color: #0F172A; margin-top: 2px; display: flex; align-items: baseline; gap: 6px;">
              <span style="color: #2563EB;" title="Hommes"><span id="kpi-hommes-enseignants"><?= (int)($stats['hommes'] ?? 0) ?></span> <span style="font-size: 13px; font-weight: 700;">H</span></span>
              <span style="color: #CBD5E1; font-weight: 400;">/</span>
              <span style="color: #EC4899;" title="Femmes"><span id="kpi-femmes-enseignants"><?= (int)($stats['femmes'] ?? 0) ?></span> <span style="font-size: 13px; font-weight: 700;">F</span></span>
            </div>
          </div>
        </div>

        <!-- Enseignants Permanents -->
        <div class="card kpi-card-filter" data-filter="permanent" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Filtrer les enseignants permanents">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="briefcase" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Contrats Permanents</div>
            <div style="font-size: 22px; font-weight: 900; color: #4338CA; margin-top: 2px;" id="kpi-permanents-enseignants"><?= (int)($stats['permanents'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Vacataires & Prestataires -->
        <div class="card kpi-card-filter" data-filter="vacataire" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Filtrer les vacataires & prestataires">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Vacataires / Prestataires</div>
            <div style="font-size: 22px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-vacataires-enseignants"><?= (int)($stats['vacataires'] ?? 0) ?></div>
          </div>
        </div>

      </div>

      <!-- BARRE DE FILTRES MULTI-CRITÈRES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="filter" style="width: 16px; height: 16px; color: #3B82F6;"></i> Filtres de Recherche Avancée
          </div>
          <button type="button" id="btn-reset-filters" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid #CBD5E1; color: #475569; background: #F8FAFC; cursor: pointer;">
            <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i> Réinitialiser les filtres
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end;">
          
          <!-- 1. Année Académique -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i> Année Académique
            </label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php if (!empty($annees)): ?>
                <?php foreach ($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>">
                    <?= htmlspecialchars($a['libelle_annee']) ?><?= ($a['statut_annee'] === 'actif') ? ' (En cours)' : '' ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <!-- 2. Genre -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="user" style="width: 14px; height: 14px; color: #64748B;"></i> Genre
            </label>
            <select id="filter-sexe" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les genres --</option>
              <option value="M">Masculin</option>
              <option value="F">Féminin</option>
            </select>
          </div>

          <!-- 3. Type de Contrat -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="briefcase" style="width: 14px; height: 14px; color: #64748B;"></i> Type de Contrat
            </label>
            <select id="filter-contrat" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les contrats --</option>
              <option value="permanent">Permanent / Titulaire</option>
              <option value="vacataire">Vacataire</option>
              <option value="prestataire">Prestataire</option>
            </select>
          </div>

        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-enseignants" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Enseignant </th>
                <th style="padding: 12px;" class="text-center">Genre</th>
                <th style="padding: 12px;">Email / Téléphone</th>
                <th style="padding: 12px;" class="text-center">Volume Horaire</th>
                <th style="padding: 12px;">Contrat</th>
                <th style="padding: 12px;" class="text-center">Statut</th>
                <th style="padding: 12px; text-align: right; width: 130px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL : PROGRAMME DES COURS (AUJOURD'HUI & AUTRES DATES) -->
    <!-- ========================================================================= -->
    <div id="modal-programme-cours" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); z-index: 9999; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(2px);">
      <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 1020px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.25s ease;">
        
        <!-- En-tête Modal -->
        <div style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
          <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.12); display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar-check" style="width: 22px; height: 22px; color: #FFFFFF;"></i>
            </div>
            <div>
              <h3 style="font-size: 17px; font-weight: 800; margin: 0; color: #FFFFFF; letter-spacing: -0.2px;">Programme des Cours & Séances</h3>
              <p style="margin: 2px 0 0 0; font-size: 12px; color: #93C5FD;" id="modal-programme-sub-text">Planning pédagogique journalier</p>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <span id="badge-programme-jour" style="background: #3B82F6; color: #FFFFFF; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">
              Aujourd'hui
            </span>
            <button type="button" class="btn-close-programme-modal" style="background: rgba(255,255,255,0.1); border: none; color: #FFFFFF; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 20px; line-height: 1; transition: background 0.2s;">&times;</button>
          </div>
        </div>

        <!-- Barre d'outils / Sélecteur de date interactif -->
        <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
          
          <!-- Contrôles de navigation de date -->
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" id="btn-programme-prev-day" class="btn btn-sm btn-outline-secondary" style="border: 1px solid #CBD5E1; background: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 6px 12px;" title="Jour précédent">
              <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
            </button>

            <div style="display: flex; align-items: center; gap: 6px; background: #FFFFFF; border: 1.5px solid #3B82F6; border-radius: 8px; padding: 4px 10px;">
              <i data-lucide="calendar" style="width: 16px; height: 16px; color: #3B82F6;"></i>
              <input type="date" id="input-programme-date" value="<?= date('Y-m-d') ?>" style="border: none; outline: none; font-weight: 700; color: #0F172A; font-size: 13px; background: transparent; cursor: pointer;">
            </div>

            <button type="button" id="btn-programme-next-day" class="btn btn-sm btn-outline-secondary" style="border: 1px solid #CBD5E1; background: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 6px 12px;" title="Jour suivant">
              <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </button>

            <button type="button" id="btn-programme-today" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i> Aujourd'hui
            </button>
          </div>

          <!-- Filtre optionnel Enseignant -->
          <div style="display: flex; align-items: center; gap: 8px;">
            <label for="select-programme-enseignant" style="font-size: 12px; font-weight: 700; color: #475569; margin: 0; white-space: nowrap;">
              <i data-lucide="user" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i> Enseignant :
            </label>
            <select id="select-programme-enseignant" class="form-control" style="font-size: 12.5px; border-radius: 8px; padding: 6px 12px; min-width: 200px; max-width: 260px;">
              <option value="">-- Tous les enseignants --</option>
              <?php if (!empty($allEnseignants)): ?>
                <?php foreach ($allEnseignants as $ensItem): ?>
                  <option value="<?= htmlspecialchars($ensItem['code_enseignant']) ?>">
                    <?= htmlspecialchars(trim(($ensItem['nom_enseignant'] ?? '') . ' ' . ($ensItem['prenom_enseignant'] ?? ''))) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

        </div>

        <!-- Corps Modal (Scrollable) -->
        <div style="padding: 24px; overflow-y: auto; flex: 1;">
          
          <!-- Mini Synthèse Chiffrée de la Journée -->
          <div id="programme-kpis-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px;">
            
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 12px 16px;">
              <div style="font-size: 11px; font-weight: 800; color: #1D4ED8; text-transform: uppercase;">Séances Planifiées</div>
              <div style="font-size: 20px; font-weight: 900; color: #1E3A5F; margin-top: 2px;" id="kpi-programme-cours">0</div>
            </div>

            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 12px 16px;">
              <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase;">Volume Horaire Total</div>
              <div style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-programme-heures">0 h</div>
            </div>

            <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 12px 16px;">
              <div style="font-size: 11px; font-weight: 800; color: #7E22CE; text-transform: uppercase;">Enseignants Mobilisés</div>
              <div style="font-size: 20px; font-weight: 900; color: #7E22CE; margin-top: 2px;" id="kpi-programme-profs">0</div>
            </div>

            <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 12px 16px;">
              <div style="font-size: 11px; font-weight: 800; color: #B45309; text-transform: uppercase;">Classes Concernées</div>
              <div style="font-size: 20px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-programme-classes">0</div>
            </div>

          </div>

          <!-- Zone d'état de chargement -->
          <div id="programme-loading" style="display: none; text-align: center; padding: 40px 0;">
            <div class="spinner-border text-primary" role="status" style="width: 32px; height: 32px;"></div>
            <div style="margin-top: 10px; color: #64748B; font-weight: 600; font-size: 13px;">Chargement du programme en cours...</div>
          </div>

          <!-- Tableau du Programme des Cours -->
          <div id="programme-table-wrapper" style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F1F5F9; color: #475569; font-size: 12px; text-align: left;">
                  <th style="padding: 10px 12px; width: 140px;">Créneau Horaire</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Salle</th>
                  <th style="padding: 10px 12px;">Enseignant Responsable</th>
                </tr>
              </thead>
              <tbody id="programme-cours-tbody">
                <!-- Injecté dynamiquement en AJAX -->
              </tbody>
            </table>
          </div>

          <!-- État vide (Aucun cours) -->
          <div id="programme-empty-state" style="display: none; text-align: center; padding: 40px 20px; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: #EFF6FF; color: #3B82F6; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
              <i data-lucide="calendar-x-2" style="width: 28px; height: 28px;"></i>
            </div>
            <h4 style="font-size: 15px; font-weight: 800; color: #1E293B; margin: 0;">Aucun cours programmé</h4>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;" id="programme-empty-msg">Aucune séance d'enseignement n'est planifiée pour cette date.</p>
          </div>

        </div>

        <!-- Pied Modal -->
        <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <div style="font-size: 12px; color: #64748B;" id="programme-footer-info">
            Programme synchronisé sur l'emploi du temps actif
          </div>
          <div style="display: flex; gap: 10px;">
            <button type="button" id="btn-print-programme-modal" class="btn btn-outline-secondary" style="font-weight: 700; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="printer" style="width: 15px; height: 15px;"></i> Imprimer
            </button>
            <button type="button" class="btn btn-secondary btn-close-programme-modal" style="font-weight: 700; border-radius: 8px; padding: 8px 18px;">
              Fermer
            </button>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
<script type="application/json" id="enseignants-list-config">
<?= json_encode([
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/enseignants.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
