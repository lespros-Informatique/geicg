<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$dailyTotals = $dailyTotals ?? ['total_general' => 0, 'nb_encaissements' => 0];
$totalSessions = $totalSessions ?? 0;
$totalOuvertes = $totalOuvertes ?? 0;
$totalCloturees = $totalCloturees ?? 0;
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
            <i data-lucide="wallet" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Sessions de Caisse & Rapprochement
          </h1>
          <p style="color: #64748B; font-size: 13.5px; margin: 4px 0 0 0;">Suivi des ouvertures de journée, fonds de caisse, encaissements et arrêtés de comptes</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <?php if (!empty($activeSession)): ?>
            <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-secondary" style="background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
              <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvel Encaissement
            </a>
            <a href="<?= RACINE ?>session_caisse/cloturer/<?= !empty($activeSession['id_session']) ? $this->validator->crypter($activeSession['id_session']) : '' ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(30,58,95,0.2);">
              <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Clôturer la Caisse
            </a>
          <?php else: ?>
            <button type="button" class="btn btn-success btn-open-session-caisse" style="background: #15803D; border-color: #15803D; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 22px; box-shadow: 0 2px 5px rgba(21,128,61,0.25); cursor: pointer; border: none;">
              <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouvrir la Caisse du Jour
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Filtre Année Académique (Select2) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
              <span style="font-size: 13px; font-weight: 700; color: #0F172A; display: block;">Année Académique</span>
              <span style="font-size: 11.5px; color: #64748B;">Filtrer les sessions de caisse par année</span>
            </div>
          </div>
          <div style="min-width: 260px; flex-grow: 0;">
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Grille des Indicateurs KPI (4 Cartes Harmoniques) -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 24px;">
        
        <!-- Carte 1 : État Caisse du Jour -->
        <div class="card" style="background: <?= !empty($activeSession) ? '#F0FDF4' : '#FFFBEB' ?>; border-radius: 12px; padding: 20px; border: 1.5px solid <?= !empty($activeSession) ? '#BBF7D0' : '#FDE68A' ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
          <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 12px; background: <?= !empty($activeSession) ? '#DCFCE7' : '#FEF3C7' ?>; color: <?= !empty($activeSession) ? '#15803D' : '#D97706' ?>; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="<?= !empty($activeSession) ? 'unlock' : 'alert-triangle' ?>" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 11px; font-weight: 700; color: <?= !empty($activeSession) ? '#166534' : '#92400E' ?>; text-transform: uppercase; letter-spacing: 0.5px;">Caisse Aujourd'hui</div>
            <div style="font-size: 16px; font-weight: 800; color: <?= !empty($activeSession) ? '#15803D' : '#D97706' ?>; line-height: 1.3; margin-top: 2px;">
              <?= !empty($activeSession) ? 'OUVERTE (' . htmlspecialchars($activeSession['code_session']) . ')' : 'Non Ouverte' ?>
            </div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
              <?= !empty($activeSession) ? 'Fond : <strong>' . number_format((float)$activeSession['fond_initial'], 0, ',', ' ') . ' F</strong>' : 'Ouvrez une session pour débuter' ?>
            </div>
          </div>
        </div>

        <!-- Carte 2 : Fond Initial de la Session Active ou Récente -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 12px; background: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="coins" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
              Fond Initial <?= !empty($activeSession) ? '(En cours)' : '(Dernière session)' ?>
            </div>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; line-height: 1.3; margin-top: 2px;">
              <?= number_format((float)($activeSession['fond_initial'] ?? ($latestSession['fond_initial'] ?? 0)), 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</span>
            </div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Monnaie de réserve de départ</div>
          </div>
        </div>

        <!-- Carte 3 : Encaissements en Direct Aujourd'hui ou Cumul Exercice -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
              <?= !empty($activeSession) ? "Encaissements Aujourd'hui" : "Cumul Encaissé Sessions" ?>
            </div>
            <div style="font-size: 20px; font-weight: 900; color: #1E3A5F; line-height: 1.3; margin-top: 2px;">
              <?= number_format((float)(!empty($activeSession) ? ($dailyTotals['total_general'] ?? 0) : ($totalCumulSessions ?? 0)), 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 700; color: #1E3A5F;">FCFA</span>
            </div>
            <div style="font-size: 11.5px; color: #15803D; font-weight: 700; margin-top: 2px;">
              <?= !empty($activeSession) ? ((int)($dailyTotals['nb_encaissements'] ?? 0) . ' règlement(s) enregistré(s)') : ((int)$totalSessions . ' session(s) enregistrée(s)') ?>
            </div>
          </div>
        </div>

        <!-- Carte 4 : Total Historique des Sessions -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Registre des Sessions</div>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; line-height: 1.3; margin-top: 2px;">
              <?= (int)$totalSessions ?> <span style="font-size: 13px; font-weight: 700; color: #64748B;">session(s)</span>
            </div>
            <div style="font-size: 11.5px; color: #B45309; margin-top: 2px; font-weight: 700;">
              <?= (int)$totalCloturees ?> clôturée(s) &bull; <?= (int)$totalOuvertes ?> en cours
            </div>
          </div>
        </div>

      </div>

      <!-- Card Table des Sessions -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="history" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Journal & Historique des Sessions de Caisse
            </h3>
            <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">Consultez les arrêtés de comptes, les écarts et imprimez les procès-verbaux</p>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table_sessions_caisse" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 45px;">#</th>
                <th style="padding: 12px 14px;">Code Session</th>
                <th style="padding: 12px 14px;">Date Session</th>
                <th style="padding: 12px 14px;">Caissier Responsable</th>
                <th style="padding: 12px 14px;">Horaires</th>
                <th style="padding: 12px 14px; text-align: right;">Fond Initial</th>
                <th style="padding: 12px 14px; text-align: right;">Total Encaissé</th>
                <th style="padding: 12px 14px; text-align: center;">Écart Caisse</th>
                <th style="padding: 12px 14px; text-align: center;">Statut</th>
                <th class="text-end" style="padding: 12px 14px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : TOUTES LES TRANSACTIONS DE LA SESSION                 -->
<!-- ========================================================================= -->
<div id="modal-transactions-session" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 960px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="receipt" style="width: 18px; height: 18px; color: #93C5FD;"></i> Transactions de la Session <span id="modal-trans-code" style="color: #93C5FD; font-family: monospace;">-</span>
        </h3>
        <p id="modal-trans-sub" style="font-size: 12px; color: #CBD5E1; margin: 3px 0 0 0;">Chargement des données...</p>
      </div>
      <button type="button" class="btn-close-modal-trans" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">
      <!-- Mini Cartes KPI de la Session -->
      <div id="modal-trans-kpis" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <!-- Injecté dynamiquement par JS -->
      </div>

      <!-- Tableau des Transactions -->
      <div style="border: 1px solid #E2E8F0; border-radius: 10px; overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px; margin: 0;">
          <thead>
            <tr style="background: #F8FAFC; border-bottom: 1.5px solid #E2E8F0; text-align: left; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase;">
              <th style="padding: 10px 12px; width: 35px;">#</th>
              <th style="padding: 10px 12px;">Réf. Reçu</th>
              <th style="padding: 10px 12px;">Date & Heure</th>
              <th style="padding: 10px 12px;">Étudiant</th>
              <th style="padding: 10px 12px;">Type</th>
              <th style="padding: 10px 12px;">Mode</th>
              <th style="padding: 10px 12px; text-align: center;">Statut</th>
              <th style="padding: 10px 12px; text-align: right;">Montant</th>
            </tr>
          </thead>
          <tbody id="modal-trans-tbody">
            <tr>
              <td colspan="8" style="padding: 20px; text-align: center; color: #64748B;">
                <i data-lucide="loader" class="lucide-spin" style="width: 18px; height: 18px; vertical-align: -3px;"></i> Chargement des transactions...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div style="padding: 14px 24px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
      <a id="modal-trans-link-pv" href="#" class="btn btn-sm btn-info" style="font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; background: #1E3A5F; color: #FFFFFF; padding: 7px 14px; text-decoration: none;">
        <i data-lucide="file-text" style="width: 14px; height: 14px;"></i> Ouvrir le PV Complet & Imprimer
      </a>
      <button type="button" class="btn btn-secondary btn-close-modal-trans" style="font-weight: 700; border-radius: 6px; padding: 7px 16px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
        Fermer
      </button>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : OUVERTURE DE LA SESSION DE CAISSE DU JOUR            -->
<!-- ========================================================================= -->
<div id="modal-open-session-caisse" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #15803D; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouverture de Session de Caisse
      </h3>
      <button type="button" class="btn-close-modal-session" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-open-session-caisse" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

      <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="info" style="color: #15803D; width: 20px; height: 20px; flex-shrink: 0;"></i>
        <div style="font-size: 12.5px; color: #166534; line-height: 1.4;">
          L'ouverture initialise la journée financière. Tous les encaissements effectués seront rattachés à cette session.
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Date de la session <span style="color: #EF4444;">*</span>
        </label>
        <input type="date" name="date_session" id="session_date" required value="<?= date('Y-m-d') ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Fond de caisse initial (FCFA) <span style="color: #EF4444;">*</span>
        </label>
        <div style="position: relative;">
          <input type="number" min="0" step="any" name="fond_initial" id="session_fond_initial" required value="0" placeholder="Ex: 50000" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px 10px 42px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 800; font-size: 15px; color: #0F172A;">
          <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748B; font-weight: 700; font-size: 13px;">F</span>
        </div>
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Montant en espèces disponible dans le tiroir au démarrage.</small>
      </div>

      <div class="form-group" style="margin-bottom: 22px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Observations / Remarques d'ouverture
        </label>
        <textarea name="observations_ouverture" id="session_observations" rows="2" placeholder="Ex: Fond de caisse vérifié en présence du responsable..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;"></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-session" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-session" class="btn btn-success" style="background: #15803D; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(21,128,61,0.2);">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Confirmer l'Ouverture
        </button>
      </div>
    </form>
  </div>
</div>

<?php ViewContext::setScripts(['public/assets/js/modules/paiements.js']); ?>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
