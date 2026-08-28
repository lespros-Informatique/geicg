<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$activeSession = $activeSession ?? null;
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
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Gestion des Sessions de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Ouvertures de journée, fonds de caisse, encaissements et arrêtés de comptes</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <?php if (!empty($activeSession)): ?>
            <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-secondary" style="background: #EFF6FF; border-color: #BFDBFE; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
              <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i> Nouvel Encaissement
            </a>
            <a href="<?= RACINE ?>session_caisse/cloturer/<?= !empty($activeSession['id_session']) ? $this->validator->crypter($activeSession['id_session']) : '' ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
              <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Clôturer la Session en Cours
            </a>
          <?php else: ?>
            <a href="<?= RACINE ?>session_caisse/formulaire" class="btn btn-success" style="background: #166534; border-color: #166534; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 20px;">
              <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouvrir la Caisse du Jour
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Bandeau Indicateurs & Statut de la Session Active -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- État de la Caisse Aujourd'hui -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <?php if (!empty($activeSession)): ?>
            <div style="width: 46px; height: 46px; border-radius: 10px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="unlock" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Session Actuelle</div>
              <div style="font-size: 16px; font-weight: 800; color: #166534; line-height: 1.2;">OUVERTE (<?= htmlspecialchars($activeSession['code_session']) ?>)</div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Fond : <?= number_format((float)$activeSession['fond_initial'], 0, ',', ' ') ?> F</div>
            </div>
          <?php else: ?>
            <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="alert-triangle" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #92400E; text-transform: uppercase; letter-spacing: 0.5px;">Session Actuelle</div>
              <div style="font-size: 16px; font-weight: 800; color: #D97706; line-height: 1.2;">Caisse Non Ouverte</div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Ouvrez une session pour encaisser</div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Encaissements en direct aujourd'hui -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="receipt" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Aujourd'hui</div>
            <div style="font-size: 20px; font-weight: 900; color: #1E3A5F; line-height: 1.2;"><?= number_format((float)($dailyTotals['total_general'] ?? 0), 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;"><?= (int)($dailyTotals['nb_encaissements'] ?? 0) ?> transaction(s)</div>
          </div>
        </div>

        <!-- Total Sessions Ouvertes / Clôturées -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="layers" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Sessions Caisse</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)$totalSessions ?></div>
            <div style="font-size: 11.5px; color: #7E22CE; margin-top: 2px; font-weight: 600;"><?= (int)$totalCloturees ?> clôturée(s)</div>
          </div>
        </div>

      </div>

      <!-- Table des Sessions -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table_sessions_caisse" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code Session</th>
                <th style="padding: 12px;">Date Session</th>
                <th style="padding: 12px;">Caissier</th>
                <th style="padding: 12px;">Horaires</th>
                <th style="padding: 12px;">Fond Initial</th>
                <th style="padding: 12px;">Total Encaissé</th>
                <th style="padding: 12px;">Écart</th>
                <th class="text-center" style="padding: 12px;">Statut</th>
                <th class="text-end" style="padding: 12px;">Actions</th>
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

  var table = $('#table_sessions_caisse').DataTable({
    ajax: {
      url: window.RACINE + 'session_caisse/apiList',
      type: 'GET'
    },
    processing: true,
    autoWidth: false,
    order: [[0, 'desc']],
    columns: [
      { data: 'id_session', defaultContent: '-' },
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
        render: function(data) {
          return '<span style="font-weight:700; color:#475569;">' + Number(data || 0).toLocaleString('fr-FR') + ' F</span>';
        }
      },
      { 
        data: 'total_general',
        render: function(data) {
          return '<strong style="color:#0F172A;">' + Number(data || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
        }
      },
      {
        data: 'ecart_caisse',
        render: function(data) {
          var ec = Number(data || 0);
          if (ec === 0) return '<span style="color:#15803D; font-weight:700; font-size:12px;">0 F</span>';
          if (ec > 0) return '<span style="color:#15803D; font-weight:700; font-size:12px;">+' + ec.toLocaleString('fr-FR') + ' F</span>';
          return '<span style="color:#DC2626; font-weight:700; font-size:12px;">' + ec.toLocaleString('fr-FR') + ' F</span>';
        }
      },
      { 
        data: 'statut_session',
        width: '135px',
        className: 'text-center',
        render: function(d, type, row) {
          var val = d || 'ouverte';
          var bgColors = { 'ouverte': '#DCFCE7', 'cloturee': '#FEF3C7', 'valide': '#DBEAFE', 'rejete': '#FEE2E2' };
          var textColors = { 'ouverte': '#15803D', 'cloturee': '#B45309', 'valide': '#1E40AF', 'rejete': '#B91C1C' };
          var borderColors = { 'ouverte': '#86EFAC', 'cloturee': '#FCD34D', 'valide': '#93C5FD', 'rejete': '#FCA5A5' };
          var currentBg = bgColors[val] || '#F1F5F9';
          var currentText = textColors[val] || '#334155';
          var currentBorder = borderColors[val] || '#CBD5E1';

          return '<select class="select-statut-session" data-id="' + row.id_session + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
                 '<option value="ouverte" ' + (val === 'ouverte' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Ouverte</option>' +
                 '<option value="cloturee" ' + (val === 'cloturee' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">Clôturée</option>' +
                 '<option value="valide" ' + (val === 'valide' ? 'selected' : '') + ' style="background:#fff; color:#1E40AF;">Validée</option>' +
                 '<option value="rejete" ' + (val === 'rejete' ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Rejetée</option>' +
                 '</select>';
        }
      },
      { 
        data: null,
        orderable: false,
        className: 'text-end',
        render: function(data, type, row) {
          var btns = '';
          if (row.statut_session === 'ouverte') {
            btns += '<a href="' + window.RACINE + 'session_caisse/cloturer/' + (row.editId || row.id_session) + '" class="btn btn-sm btn-primary" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; background:#1E3A5F;"><i data-lucide="lock" style="width:13px;height:13px;"></i> Clôturer</a>';
          }
          btns += '<a href="' + window.RACINE + 'session_caisse/details/' + (row.editId || row.id_session) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="file-text" style="width:13px;height:13px;"></i> PV & Détails</a>';
          return btns;
        }
      }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Changement de statut Session
  $(document).on('change', '.select-statut-session', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>session_caisse/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: newStatut,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut de session mis à jour');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur');
          table.ajax.reload(null, false);
        }
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
