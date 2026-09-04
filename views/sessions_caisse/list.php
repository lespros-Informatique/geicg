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
            <a href="<?= RACINE ?>session_caisse/formulaire" class="btn btn-success" style="background: #15803D; border-color: #15803D; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 22px; box-shadow: 0 2px 5px rgba(21,128,61,0.25);">
              <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouvrir la Caisse du Jour
            </a>
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

        <!-- Carte 2 : Fond Initial de la Session Active -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 12px; background: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="coins" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Fond de Caisse Initial</div>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; line-height: 1.3; margin-top: 2px;">
              <?= number_format((float)($activeSession['fond_initial'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</span>
            </div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Monnaie de réserve de départ</div>
          </div>
        </div>

        <!-- Carte 3 : Encaissements en Direct Aujourd'hui -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Aujourd'hui</div>
            <div style="font-size: 20px; font-weight: 900; color: #1E3A5F; line-height: 1.3; margin-top: 2px;">
              <?= number_format((float)($dailyTotals['total_general'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 700; color: #1E3A5F;">FCFA</span>
            </div>
            <div style="font-size: 11.5px; color: #15803D; font-weight: 700; margin-top: 2px;">
              <?= (int)($dailyTotals['nb_encaissements'] ?? 0) ?> règlement(s) enregistré(s)
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
              <?= (int)$totalCloturees ?> clôturée(s) & archivée(s)
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
        data: null,
        orderable: false,
        className: 'text-end',
        render: function(data, type, row) {
          var btns = '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">';
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

  $('#filter-annee').on('change', function() {
    var val = $(this).val();
    window.location.href = window.RACINE + 'session_caisse/list?annee_code=' + encodeURIComponent(val);
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
