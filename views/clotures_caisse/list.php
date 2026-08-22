<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Clôtures de Caisse Journalières</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Clôtures de Caisse Journalières</p>
        </div>
        <a href="<?= RACINE ?>cloture_caisse/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Clôture de Caisse
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-clotures_caisse" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code Clôture</th>
                <th style="padding: 12px;">Date Caisse</th>
                <th style="padding: 12px;">Espèces (FCFA)</th>
                <th style="padding: 12px;">Mobile Money</th>
                <th style="padding: 12px;">Chèques / Vir.</th>
                <th style="padding: 12px;">Total Général</th>
                <th style="padding: 12px;">Statut</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
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
  $('#table-clotures_caisse').DataTable({
    ajax: '<?= RACINE ?>cloture_caisse/apiList',
    scrollX: true,
    autoWidth: false,
    columns: [
      { data: 'id_cloture', defaultContent: '-' },
      { data: 'code_cloture', defaultContent: '-' },
      { data: 'date_cloture', defaultContent: '-' },
      { data: 'total_especes', defaultContent: '-' },
      { data: 'total_mobile_money', defaultContent: '-' },
      { data: 'total_cheque_virement', defaultContent: '-' },
      { data: 'total_general', defaultContent: '-' },
      { data: 'statut_cloture', render: function(d) {
        return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700;">Inactif</span>';
      } },
      { data: null, render: function(d) {
        return '<a href="' + window.RACINE + 'cloture_caisse/edition/' + (d.editId || d.id_cloture) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'cloture_caisse/details/' + (d.editId || d.id_cloture) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
