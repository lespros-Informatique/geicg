<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="file-check" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Pièces & Documents à Fournir
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Répertoire central des pièces administratives et justificatifs demandés aux étudiants (CNI, Acte de naissance, Diplômes...)</p>
        </div>
        <div>
          <a href="<?= RACINE ?>piece_fournir/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Document
          </a>
        </div>
      </div>

      <!-- Navigation Tabs (Répertoire vs Dossiers par Cycle) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
        <a href="<?= RACINE ?>piece_fournir/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="file-text" style="width: 17px; height: 17px;"></i> Répertoire des Pièces
        </a>
        <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 17px; height: 17px;"></i> Dossiers Exigés par Cycle
        </a>
      </div>

      <!-- KPI Summary Cards -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Pièces -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="file-check-2" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Pièces Référencées</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($summary['total'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Pièces Actives -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Pièces Actives</div>
            <div style="font-size: 22px; font-weight: 800; color: #15803D; line-height: 1.2;"><?= (int)($summary['actifs'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Utilisées dans les Cycles -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="folder-check" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Assignées aux Cycles</div>
            <div style="font-size: 22px; font-weight: 800; color: #7E22CE; line-height: 1.2;"><?= (int)($summary['utilises'] ?? 0) ?></div>
          </div>
        </div>

      </div>

      <!-- Main Table Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-pieces-fournir" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <th style="padding: 12px 14px;">Code</th>
                <th style="padding: 12px 14px;">Intitulé du Document / Pièce à Fournir</th>
                <th style="padding: 12px 14px;">Précisions & Instructions</th>
                <th style="padding: 12px 14px; text-align: center;">Cycles Assignés</th>
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

<script>
$(document).ready(function() {
  var table = $('#table-pieces-fournir').DataTable({
    ajax: '<?= RACINE ?>piece_fournir/apiList',
    processing: true,
    autoWidth: false,
    order: [[0, 'asc']],
    columns: [
      { data: 'code_piece_fournir', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; font-size:12.5px;">' + (d || '-') + '</code>';
      } },
      { data: 'libelle_piece', render: function(d) {
        return '<div style="font-weight:700; color:#0F172A; font-size:13.5px; display:flex; align-items:center; gap:8px;"><i data-lucide="file-text" style="width:16px;height:16px;color:#3B82F6;flex-shrink:0;"></i> ' + (d || '-') + '</div>';
      } },
      { data: 'description_piece', render: function(d) {
        return '<span style="color:#64748B; font-size:12.5px;">' + (d || '<em style="color:#94A3B8;">Aucune instruction particulière</em>') + '</span>';
      } },
      { data: 'nb_cycles_utilises', className: 'text-center', render: function(d) {
        var count = parseInt(d) || 0;
        if (count > 0) {
          return '<span class="badge" style="background:#FAF5FF; color:#7E22CE; font-weight:800; padding:5px 10px; border-radius:6px; border:1px solid #E9D5FF;">' + count + ' cycle(s)</span>';
        }
        return '<span class="badge" style="background:#F1F5F9; color:#94A3B8; font-weight:600; padding:4px 8px; border-radius:6px;">Non assigné</span>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>piece_fournir/edition/' + (d.editId || d.id_piece_fournir) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>' +
               '<a href="<?= RACINE ?>piece_fournir/supprimer/' + (d.editId || d.id_piece_fournir) + '" onclick="return confirm(\'Voulez-vous vraiment supprimer cette pièce du répertoire ?\')" class="btn btn-sm btn-danger" style="background:#EF4444; color:#fff; border:none; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
