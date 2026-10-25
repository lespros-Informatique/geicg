<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$activeSession = $activeSession ?? null;
$dailyTotals = $dailyTotals ?? ['total_general' => 0, 'nb_encaissements' => 0];
$totalSessions = $totalSessions ?? 0;
$totalOuvertes = $totalOuvertes ?? 0;
$totalCloturees = $totalCloturees ?? 0;
$annees = $annees ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
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

<style>
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
  }

  var table = $('#table_sessions_caisse').DataTable({
    ajax: {
      url: window.RACINE + 'session_caisse/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
      }
    },
    processing: true,
    autoWidth: false,
    order: [[0, 'desc']],
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { 
        data: 'code_session',
        render: function(data) {
          return '<code style="font-weight:800; color:#1E3A5F;">' + (data || '-') + '</code>';
        }
      },
      { 
        data: 'date_session',
        render: function(data) {
          if (!data) return '-';
          return '<strong>' + new Date(data).toLocaleDateString('fr-FR') + '</strong>';
        }
      },
      {
        data: 'caissier_nom',
        render: function(d, t, r) {
          return '<span style="font-weight:700; color:#0F172A;">' + (d && d.trim() ? d : (r.user_code || 'Caissier')) + '</span>';
        }
      },
      {
        data: 'heure_ouverture',
        render: function(d, t, r) {
          var ouv = d ? d.substring(0, 5) : '--:--';
          var clot = r.heure_cloture ? r.heure_cloture.substring(0, 5) : '--:--';
          return '<span style="font-size:12px; color:#475569;">' + ouv + ' &rarr; ' + clot + '</span>';
        }
      },
      { 
        data: 'fond_initial',
        className: 'text-end',
        render: function(data) {
          return '<span style="font-weight:700; color:#475569;">' + Number(data || 0).toLocaleString('fr-FR') + ' F</span>';
        }
      },
      { 
        data: 'total_general',
        className: 'text-end',
        render: function(data) {
          return '<strong style="color:#1E3A5F; font-size:13.5px;">' + Number(data || 0).toLocaleString('fr-FR') + ' F</strong>';
        }
      },
      { 
        data: 'ecart_caisse',
        className: 'text-center',
        render: function(data) {
          var ec = Number(data || 0);
          if (ec === 0) return '<span style="background:#F0FDF4; color:#15803D; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; border:1px solid #BBF7D0;">0 F</span>';
          if (ec > 0) return '<span style="background:#F0FDF4; color:#15803D; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; border:1px solid #BBF7D0;">+' + ec.toLocaleString('fr-FR') + ' F</span>';
          return '<span style="background:#FEF2F2; color:#DC2626; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; border:1px solid #FECACA;">' + ec.toLocaleString('fr-FR') + ' F</span>';
        }
      },
      { 
        data: 'statut_session',
        className: 'text-center',
        render: function(data) {
          if (data === 'ouverte') {
            return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11.5px; border:1px solid #BBF7D0;"><i data-lucide="unlock" style="width:11px;height:11px;vertical-align:-1px;"></i> Ouverte</span>';
          }
          if (data === 'valide') {
            return '<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11.5px; border:1px solid #BFDBFE;"><i data-lucide="check-check" style="width:11px;height:11px;vertical-align:-1px;"></i> Validée</span>';
          }
          return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11.5px; border:1px solid #FDE68A;"><i data-lucide="lock" style="width:11px;height:11px;vertical-align:-1px;"></i> Clôturée</span>';
        }
      },
      { 
        data: null,
        orderable: false,
        className: 'text-end',
        render: function(data, type, row) {
          var btns = '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">';
          btns += '<button type="button" class="btn btn-sm btn-show-transactions" data-id="' + (row.editId || row.id_session) + '" data-code="' + (row.code_session || '') + '" style="font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; padding:5px 10px; font-size:12px; background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; cursor:pointer;" title="Voir les transactions"><i data-lucide="receipt" style="width:13px;height:13px;"></i> Transactions</button>';
          if (row.statut_session === 'ouverte') {
            btns += '<a href="' + window.RACINE + 'session_caisse/cloturer/' + (row.editId || row.id_session) + '" class="btn btn-sm btn-primary" style="font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; background:#1E3A5F; padding:5px 10px; font-size:12px;"><i data-lucide="lock" style="width:13px;height:13px;"></i> Clôturer</a>';
          }
          btns += '<a href="' + window.RACINE + 'session_caisse/details/' + (row.editId || row.id_session) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; padding:5px 10px; font-size:12px; background:#F1F5F9; color:#1E3A5F; border:1px solid #CBD5E1;"><i data-lucide="file-text" style="width:13px;height:13px;"></i> PV & Détails</a>';
          btns += '</div>';
          return btns;
        }
      }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // OUVERTURE DU MODAL TRANSACTIONS DE SESSION
  $(document).on('click', '.btn-show-transactions', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var code = $(this).data('code') || '';
    
    $('#modal-trans-code').text(code || '...');
    $('#modal-trans-sub').text('Chargement des transactions...');
    $('#modal-trans-kpis').html('');
    $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 24px; text-align: center; color: #64748B;"><i data-lucide="loader" class="lucide-spin" style="width: 20px; height: 20px; vertical-align: -4px;"></i> Chargement des opérations en cours...</td></tr>');
    $('#modal-trans-link-pv').attr('href', window.RACINE + 'session_caisse/details/' + id);
    $('#modal-transactions-session').css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'session_caisse/apiTransactions/' + id,
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var s = res.session || {};
          var list = res.data || [];
          
          $('#modal-trans-code').text(s.code_session || code);
          $('#modal-trans-sub').text('Session du ' + (s.date_session || '') + ' • Caissier : ' + (s.caissier_nom || 'Caissier') + ' • Statut : ' + (s.statut_session || ''));

          // Injecter Mini KPIs
          var kpHtml = '';
          kpHtml += '<div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 14px;">';
          kpHtml += '  <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Fond Initial</div>';
          kpHtml += '  <div style="font-size:16px; font-weight:800; color:#0F172A; margin-top:2px;">' + Number(s.fond_initial || 0).toLocaleString('fr-FR') + ' F</div>';
          kpHtml += '</div>';

          kpHtml += '<div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 14px;">';
          kpHtml += '  <div style="font-size:11px; font-weight:700; color:#15803D; text-transform:uppercase;">Total Encaissé Validé</div>';
          kpHtml += '  <div style="font-size:16px; font-weight:900; color:#15803D; margin-top:2px;">' + Number(s.total_valide || 0).toLocaleString('fr-FR') + ' F</div>';
          kpHtml += '  <div style="font-size:11px; color:#166534; font-weight:700;">' + (s.nb_valides || 0) + ' règlement(s) effectif(s)</div>';
          kpHtml += '</div>';

          kpHtml += '<div style="background:' + (s.total_annule > 0 ? '#FEF2F2' : '#F8FAFC') + '; border:1px solid ' + (s.total_annule > 0 ? '#FECACA' : '#E2E8F0') + '; border-radius:8px; padding:10px 14px;">';
          kpHtml += '  <div style="font-size:11px; font-weight:700; color:' + (s.total_annule > 0 ? '#DC2626' : '#64748B') + '; text-transform:uppercase;">Annulations</div>';
          kpHtml += '  <div style="font-size:16px; font-weight:800; color:' + (s.total_annule > 0 ? '#DC2626' : '#64748B') + '; margin-top:2px;">' + Number(s.total_annule || 0).toLocaleString('fr-FR') + ' F</div>';
          kpHtml += '  <div style="font-size:11px; color:' + (s.total_annule > 0 ? '#B91C1C' : '#94A3B8') + '; font-weight:700;">' + (s.nb_annules || 0) + ' annulée(s)</div>';
          kpHtml += '</div>';

          kpHtml += '<div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:10px 14px;">';
          kpHtml += '  <div style="font-size:11px; font-weight:700; color:#1E3A5F; text-transform:uppercase;">Solde Attendu</div>';
          kpHtml += '  <div style="font-size:16px; font-weight:900; color:#1E3A5F; margin-top:2px;">' + Number(s.solde_attendu || 0).toLocaleString('fr-FR') + ' F</div>';
          kpHtml += '  <div style="font-size:11px; color:#475569;">Écart : ' + (s.ecart_caisse > 0 ? '+' : '') + Number(s.ecart_caisse || 0).toLocaleString('fr-FR') + ' F</div>';
          kpHtml += '</div>';

          $('#modal-trans-kpis').html(kpHtml);

          // Remplir tableau
          if (list.length === 0) {
            $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 24px; text-align: center; color: #94A3B8;">Aucune transaction enregistrée au cours de cette session.</td></tr>');
          } else {
            var tbHtml = '';
            for (var i = 0; i < list.length; i++) {
              var t = list[i];
              var isAnn = (String(t.statut_paiement).toLowerCase() === 'annule');
              var rowBg = isAnn ? '#FEF2F2' : '#FFFFFF';
              var cat = String(t.categorie_paiement || 'SCOLARITE').toUpperCase();
              
              tbHtml += '<tr style="border-bottom: 1px solid #F1F5F9; background: ' + rowBg + ';">';
              tbHtml += '  <td style="padding: 10px 12px; color: #64748B; font-weight: 700;">' + (i + 1) + '</td>';
              tbHtml += '  <td style="padding: 10px 12px;"><code style="font-weight: 800; color: #1E3A5F;">' + (t.code_paiement || '-') + '</code></td>';
              tbHtml += '  <td style="padding: 10px 12px; white-space: nowrap; font-size: 12px; color: #334155;"><strong>' + (t.formatted_date || '') + '</strong></td>';
              tbHtml += '  <td style="padding: 10px 12px;">';
              tbHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13px;">' + (t.etudiant_complet || '') + '</div>';
              if (t.matricule_etudiant) tbHtml += '    <div style="font-size: 11px; color: #64748B;">' + t.matricule_etudiant + '</div>';
              tbHtml += '  </td>';
              
              // Type
              tbHtml += '  <td style="padding: 10px 12px;">';
              if (cat === 'FRAIS_ANNEXES') {
                tbHtml += '    <span class="badge" style="background:#F3E8FF; color:#7E22CE; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #E9D5FF;"><i data-lucide="tag" style="width:11px;height:11px;vertical-align:-1px;"></i> Frais Annexes</span>';
              } else {
                tbHtml += '    <span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #BFDBFE;"><i data-lucide="graduation-cap" style="width:11px;height:11px;vertical-align:-1px;"></i> Scolarité</span>';
                if (t.libelle_tranche) {
                  tbHtml += '    <div style="font-size: 11px; color: #475569; margin-top: 2px;">' + t.libelle_tranche + '</div>';
                }
              }
              tbHtml += '  </td>';

              // Mode
              tbHtml += '  <td style="padding: 10px 12px;">';
              tbHtml += '    <span class="badge" style="background:#F8FAFC; color:#334155; font-weight:700; padding:3px 7px; border-radius:5px; font-size:11px; text-transform:uppercase; border:1px solid #CBD5E1;">' + (t.mode_paiement || 'espece') + '</span>';
              tbHtml += '  </td>';

              // Statut
              tbHtml += '  <td style="padding: 10px 12px; text-align: center;">';
              if (isAnn) {
                tbHtml += '    <span class="badge" style="background:#FEE2E2; color:#DC2626; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #FECACA;"><i data-lucide="x-circle" style="width:11px;height:11px;vertical-align:-1px;"></i> Annulé</span>';
                if (t.observations) tbHtml += '    <div style="font-size:10px; color:#B91C1C; margin-top:2px; max-width:140px; line-height:1.2; font-style:italic;">' + t.observations + '</div>';
              } else {
                tbHtml += '    <span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #BBF7D0;"><i data-lucide="check" style="width:11px;height:11px;vertical-align:-1px;"></i> Confirmé</span>';
              }
              tbHtml += '  </td>';

              // Montant
              tbHtml += '  <td style="padding: 10px 12px; text-align: right;">';
              if (isAnn) {
                tbHtml += '    <del style="font-weight:700; color:#94A3B8; font-size:13px;">' + (t.formatted_montant || '') + '</del>';
                tbHtml += '    <div style="font-size:10.5px; color:#DC2626; font-weight:700;">Exclu</div>';
              } else {
                tbHtml += '    <span style="font-weight:900; color:#0F172A; font-size:13.5px;">' + (t.formatted_montant || '') + '</span>';
              }
              tbHtml += '  </td>';

              tbHtml += '</tr>';
            }
            $('#modal-trans-tbody').html(tbHtml);
          }
          if (window.lucide) lucide.createIcons();
        } else {
          $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 20px; text-align: center; color: #DC2626;">Erreur lors du chargement des transactions.</td></tr>');
        }
      },
      error: function() {
        $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 20px; text-align: center; color: #DC2626;">Erreur de communication avec le serveur.</td></tr>');
      }
    });
  });

  $('.btn-close-modal-trans').on('click', function() {
    $('#modal-transactions-session').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-transactions-session')) {
      $('#modal-transactions-session').hide();
    }
  });

  $('#filter-annee').on('change', function() {
    var val = $(this).val();
    window.location.href = window.RACINE + 'session_caisse/list?annee_code=' + encodeURIComponent(val);
  });

  // GESTION MODALE OUVERTURE DE CAISSE DU JOUR
  $(document).on('click', '.btn-open-session-caisse', function(e) {
    e.preventDefault();
    $('#form-open-session-caisse')[0].reset();
    $('#session_date').val(new Date().toISOString().split('T')[0]);
    $('#session_fond_initial').val(0);
    $('#modal-open-session-caisse').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#session_fond_initial').focus().select(); }, 100);
  });

  $('.btn-close-modal-session').on('click', function() {
    $('#modal-open-session-caisse').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-open-session-caisse')) {
      $('#modal-open-session-caisse').hide();
    }
  });

  $('#form-open-session-caisse').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-session');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Ouverture...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'session_caisse/add',
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Session de caisse ouverte avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Session de caisse ouverte avec succès');
          $('#modal-open-session-caisse').hide();
          setTimeout(function() {
            window.location.reload();
          }, 600);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'ouverture de la session', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'ouverture de la session');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
