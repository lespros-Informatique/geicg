<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$totalOuvertures = isset($totalOuvertures) ? $totalOuvertures : 0;
$totalClotures = isset($totalClotures) ? $totalClotures : 0;
$caisseJourOuverte = isset($caisseJourOuverte) ? $caisseJourOuverte : null;
$caisseJourCloturee = isset($caisseJourCloturee) ? $caisseJourCloturee : null;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Gestion de Caisse : Ouvertures & Clôtures</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Contrôle des sessions journalières, fonds de caisse et arrêtés de comptes</p>
        </div>
        <div id="caisse-actions-container" style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>ouverture_caisse/formulaire" id="btn-action-ouv" class="btn btn-success" style="background: #166534; border-color: #166534; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Nouvelle Ouverture Caisse
          </a>
          <a href="<?= RACINE ?>cloture_caisse/formulaire" id="btn-action-clot" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Nouvelle Clôture Caisse
          </a>
        </div>
      </div>

      <!-- Bandeau Indicateurs & Statut de la Journée -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- État de la Caisse Aujourd'hui -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <?php if (!empty($caisseJourCloturee)): ?>
            <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="lock" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 11.5px; font-weight: 700; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">Caisse du Jour</div>
              <div style="font-size: 16px; font-weight: 800; color: #DC2626; line-height: 1.2;">Clôturée (<?= htmlspecialchars($caisseJourCloturee['code_cloture']) ?>)</div>
            </div>
          <?php elseif (!empty($caisseJourOuverte)): ?>
            <div style="width: 46px; height: 46px; border-radius: 10px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="unlock" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 11.5px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Caisse du Jour</div>
              <div style="font-size: 16px; font-weight: 800; color: #16A34A; line-height: 1.2;">Session Ouverte (<?= number_format((float)$caisseJourOuverte['fond_initial'], 0, ',', ' ') ?> F)</div>
            </div>
          <?php else: ?>
            <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="alert-triangle" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 11.5px; font-weight: 700; color: #92400E; text-transform: uppercase; letter-spacing: 0.5px;">Caisse du Jour</div>
              <div style="font-size: 16px; font-weight: 800; color: #D97706; line-height: 1.2;">Non Ouverte</div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Total Sessions d'Ouverture -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Ouvertures Enregistrées</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)$totalOuvertures ?></div>
          </div>
        </div>

        <!-- Total Clôtures Effectuées -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="file-check" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Arrêtés / Clôtures</div>
            <div style="font-size: 22px; font-weight: 800; color: #7E22CE; line-height: 1.2;"><?= (int)$totalClotures ?></div>
          </div>
        </div>

      </div>

      <!-- Nav Tabs -->
      <ul class="nav nav-tabs" id="caisseTabs" role="tablist" style="border-bottom: 2px solid #E2E8F0; margin-bottom: 20px; gap: 8px; list-style: none; padding-left: 0; display: flex;">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-ouvertures-btn" data-bs-target="#tab-ouvertures" type="button" role="tab" style="font-weight: 700; padding: 12px 22px; border-radius: 8px 8px 0 0; color: #166534; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E2E8F0; border-bottom: none; background: #FFFFFF; cursor: pointer;">
            <i data-lucide="unlock" style="width: 16px; height: 16px;"></i> Registre des Ouvertures
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-clotures-btn" data-bs-target="#tab-clotures" type="button" role="tab" style="font-weight: 700; padding: 12px 22px; border-radius: 8px 8px 0 0; color: #64748B; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E2E8F0; border-bottom: none; background: #F8FAFC; cursor: pointer;">
            <i data-lucide="lock" style="width: 16px; height: 16px;"></i> Arrêtés & Clôtures Journalières
          </button>
        </li>
      </ul>

      <div class="tab-content" id="caisseTabsContent">
        
        <!-- TAB 1 : REGISTRE DES OUVERTURES -->
        <div class="tab-pane fade show active" id="tab-ouvertures" role="tabpanel">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table_ouvertures_caisse" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Code Ouverture</th>
                    <th style="padding: 12px;">Date Session</th>
                    <th style="padding: 12px;">Heure</th>
                    <th style="padding: 12px;">Fond Initial (FCFA)</th>
                    <th class="text-center" style="padding: 12px;">Statut</th>
                    <th class="text-end" style="padding: 12px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 2 : ARRÊTÉS & CLÔTURES DE CAISSE -->
        <div class="tab-pane fade" id="tab-clotures" role="tabpanel" style="display: none;">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table_clotures_caisse" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Code Clôture</th>
                    <th style="padding: 12px;">Date Caisse</th>
                    <th style="padding: 12px;">Espèces (FCFA)</th>
                    <th style="padding: 12px;">Mobile Money</th>
                    <th style="padding: 12px;">Chèques / Vir.</th>
                    <th style="padding: 12px;">Total Général</th>
                    <th class="text-center" style="padding: 12px;">Statut</th>
                    <th class="text-end" style="padding: 12px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // 1. DataTable Ouvertures
  var tableOuvertures = $('#table_ouvertures_caisse').DataTable({
    ajax: {
      url: window.RACINE + 'ouverture_caisse/apiList',
      type: 'GET'
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_ouverture', defaultContent: '-' },
      { 
        data: 'code_ouverture',
        render: function(data) {
          return '<code style="font-weight:700; color:#1E3A5F;">' + (data || '-') + '</code>';
        }
      },
      { 
        data: 'date_ouverture',
        render: function(data) {
          if (!data) return '-';
          return '<strong>' + new Date(data).toLocaleDateString('fr-FR') + '</strong>';
        }
      },
      { data: 'heure_ouverture', defaultContent: '-' },
      { 
        data: 'fond_initial',
        render: function(data) {
          return '<strong style="color:#0F172A;">' + Number(data || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
        }
      },
      { 
        data: 'statut_ouverture',
        width: '130px',
        className: 'text-center',
        render: function(d, type, row) {
          var val = d || 'ouverte';
          var isOuverte = (val === 'ouverte');
          var currentBg = isOuverte ? '#DCFCE7' : '#F1F5F9';
          var currentText = isOuverte ? '#166534' : '#475569';
          var currentBorder = isOuverte ? '#86EFAC' : '#CBD5E1';

          return '<select class="select-statut-ouverture" data-id="' + row.id_ouverture + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
                 '<option value="ouverte" ' + (isOuverte ? 'selected' : '') + ' style="background:#fff; color:#166534;">Ouverte</option>' +
                 '<option value="cloturee" ' + (!isOuverte ? 'selected' : '') + ' style="background:#fff; color:#475569;">Clôturée</option>' +
                 '</select>';
        }
      },
      { 
        data: null,
        orderable: false,
        className: 'text-end',
        render: function(data, type, row) {
          return '<a href="' + window.RACINE + 'ouverture_caisse/edition/' + (row.editId || row.id_ouverture) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
                 '<a href="' + window.RACINE + 'ouverture_caisse/details/' + (row.editId || row.id_ouverture) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
        }
      }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // 2. DataTable Clôtures
  var tableClotures = $('#table_clotures_caisse').DataTable({
    ajax: {
      url: window.RACINE + 'cloture_caisse/apiList',
      type: 'GET'
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cloture', defaultContent: '-' },
      { data: 'code_cloture', render: function(d) {
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      } },
      { data: 'date_cloture', render: function(d) {
        return d ? '<strong>' + new Date(d).toLocaleDateString('fr-FR') + '</strong>' : '-';
      } },
      { data: 'total_especes', render: function(d) { return d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'; } },
      { data: 'total_mobile_money', render: function(d) { return d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'; } },
      { data: 'total_cheque_virement', render: function(d) { return d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'; } },
      { data: 'total_general', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA') + '</strong>';
      } },
      { data: 'statut_cloture', width: '130px', className: 'text-center', render: function(d, type, row) {
        var val = d || 'attente';
        var bgColors = { 'valide': '#DCFCE7', 'attente': '#FEF3C7', 'rejete': '#FEE2E2' };
        var textColors = { 'valide': '#15803D', 'attente': '#B45309', 'rejete': '#B91C1C' };
        var borderColors = { 'valide': '#86EFAC', 'attente': '#FCD34D', 'rejete': '#FCA5A5' };
        var currentBg = bgColors[val] || '#F1F5F9';
        var currentText = textColors[val] || '#334155';
        var currentBorder = borderColors[val] || '#CBD5E1';

        return '<select class="select-statut-cloture" data-id="' + row.id_cloture + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="attente" ' + (val === 'attente' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">En attente</option>' +
               '<option value="valide" ' + (val === 'valide' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Validée</option>' +
               '<option value="rejete" ' + (val === 'rejete' ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Rejetée</option>' +
               '</select>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        return '<a href="' + window.RACINE + 'cloture_caisse/edition/' + (d.editId || d.id_cloture) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'cloture_caisse/details/' + (d.editId || d.id_cloture) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> PV Clôture</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Changement de statut Ouverture
  $(document).on('change', '.select-statut-ouverture', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>ouverture_caisse/changer',
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
          if (window.toastr) toastr.success(res.message || 'Statut d\'ouverture mis à jour');
          tableOuvertures.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur');
          tableOuvertures.ajax.reload(null, false);
        }
      }
    });
  });

  // Changement de statut Clôture
  $(document).on('change', '.select-statut-cloture', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>cloture_caisse/changer',
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
          if (window.toastr) toastr.success(res.message || 'Statut de clôture mis à jour');
          tableClotures.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur');
          tableClotures.ajax.reload(null, false);
        }
      }
    });
  });

  // 3. Gestion des Onglets
  function switchTab(tabId) {
    if (tabId === 'clotures') {
      $('#tab-ouvertures').hide().removeClass('show active');
      $('#tab-clotures').show().addClass('show active');
      $('#tab-ouvertures-btn').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B' });
      $('#tab-clotures-btn').addClass('active').css({ 'background': '#FFFFFF', 'color': '#1E3A5F' });
      tableClotures.columns.adjust().draw();
    } else {
      $('#tab-clotures').hide().removeClass('show active');
      $('#tab-ouvertures').show().addClass('show active');
      $('#tab-clotures-btn').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B' });
      $('#tab-ouvertures-btn').addClass('active').css({ 'background': '#FFFFFF', 'color': '#166534' });
      tableOuvertures.columns.adjust().draw();
    }
    if (window.lucide) lucide.createIcons();
  }

  $('#tab-ouvertures-btn').on('click', function() {
    switchTab('ouvertures');
    window.history.replaceState(null, null, '?tab=ouvertures');
  });

  $('#tab-clotures-btn').on('click', function() {
    switchTab('clotures');
    window.history.replaceState(null, null, '?tab=clotures');
  });

  // Check URL params
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('tab') === 'clotures') {
    switchTab('clotures');
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
