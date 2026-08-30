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
            <i data-lucide="layers" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Dossier de Pièces à Fournir par Cycle
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des documents et justificatifs exigés pour l'inscription dans chaque cycle académique</p>
        </div>
        <div>
          <a href="<?= RACINE ?>piece_fournir_cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Configurer Pièces du Cycle
          </a>
        </div>
      </div>

      <!-- Navigation Tabs (Répertoire vs Dossiers par Cycle) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
        <a href="<?= RACINE ?>piece_fournir/list" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="file-text" style="width: 17px; height: 17px;"></i> Répertoire des Pièces
        </a>
        <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 17px; height: 17px;"></i> Dossiers Exigés par Cycle
        </a>
      </div>

      <!-- KPI Summary Cards -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Pièces Assignées -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="folder-check" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Pièces Assignées</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($summary['total'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Pièces Obligatoires -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-square" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Obligatoires</div>
            <div style="font-size: 22px; font-weight: 800; color: #15803D; line-height: 1.2;"><?= (int)($summary['obligatoires'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Facultatives / Complémentaires -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px;">Complémentaires</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($summary['facultatifs'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Cycles Configurés -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Cycles Configurés</div>
            <div style="font-size: 22px; font-weight: 800; color: #7E22CE; line-height: 1.2;"><?= (int)($summary['cycles_configures'] ?? 0) ?></div>
          </div>
        </div>

      </div>

      <!-- Main Table Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-pieces-cycle" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <th style="padding: 12px 14px;">Code</th>
                <th style="padding: 12px 14px;">Cycle Académique</th>
                <th style="padding: 12px 14px;">Document / Pièce Exigée</th>
                <th style="padding: 12px 14px; text-align: center;">Exemplaires</th>
                <th style="padding: 12px 14px;">Nature du Document</th>
                <th style="padding: 12px 14px; text-align: center;">Caractère</th>
                <th style="padding: 12px 14px; text-align: center;">Statut</th>
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
  var table = $('#table-pieces-cycle').DataTable({
    ajax: '<?= RACINE ?>piece_fournir_cycle/apiList',
    processing: true,
    autoWidth: false,
    order: [[0, 'desc']],
    columns: [
      { data: 'code_piece_cycle', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</code>';
      } },
      { data: 'libelle_cycle', render: function(d) {
        return '<span class="badge" style="background:#FAF5FF; color:#7E22CE; font-weight:800; font-size:12px; padding:5px 10px; border-radius:6px; border:1px solid #E9D5FF;">' + (d || 'Tous cycles') + '</span>';
      } },
      { data: 'libelle_piece', render: function(d, type, row) {
        var desc = row.description_piece ? '<div style="font-size:11.5px; color:#64748B; margin-top:2px;">' + row.description_piece + '</div>' : '';
        return '<div style="font-weight:700; color:#0F172A;">' + (d || '-') + '</div>' + desc;
      } },
      { data: 'nombre_exemplaires', className: 'text-center', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; background:#F1F5F9; padding:4px 10px; border-radius:6px;">' + (d || 1) + ' ex.</span>';
      } },
      { data: 'nature_document', render: function(d) {
        var natureLabels = {
          'photocopie_simple': '<span style="color:#334155; font-weight:600;"><i data-lucide="copy" style="width:13px;height:13px;display:inline;"></i> Photocopie Simple</span>',
          'photocopie_legalisee': '<span style="color:#1E3A5F; font-weight:700;"><i data-lucide="stamp" style="width:13px;height:13px;display:inline;"></i> Photocopie Légalisée</span>',
          'original': '<span style="color:#B45309; font-weight:700;"><i data-lucide="award" style="width:13px;height:13px;display:inline;"></i> Original Requis</span>',
          'numerique': '<span style="color:#2563EB; font-weight:600;"><i data-lucide="upload" style="width:13px;height:13px;display:inline;"></i> Fichier Numérique (PDF)</span>'
        };
        return natureLabels[d] || (d || 'Photocopie simple');
      } },
      { data: 'est_obligatoire', className: 'text-center', render: function(d) {
        if (d === 'obligatoire') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:4px 10px; border-radius:6px;">Obligatoire</span>';
        } else if (d === 'complementaire') {
          return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-weight:800; padding:4px 10px; border-radius:6px;">Complémentaire</span>';
        }
        return '<span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700; padding:4px 10px; border-radius:6px;">Facultatif</span>';
      } },
      { data: 'statut_piece_cycle', className: 'text-center', render: function(d, type, row) {
        var val = d || 'actif';
        var isActif = val === 'actif';
        var bg = isActif ? '#DCFCE7' : '#FEE2E2';
        var col = isActif ? '#15803D' : '#B91C1C';
        var border = isActif ? '#86EFAC' : '#FCA5A5';

        return '<select class="select-statut-piece-cycle" data-id="' + row.id_piece_cycle + '" style="background:' + bg + '; color:' + col + '; border:1px solid ' + border + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="actif" ' + (isActif ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Actif</option>' +
               '<option value="inactif" ' + (!isActif ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Inactif</option>' +
               '</select>';
      } },
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>piece_fournir_cycle/edition/' + (d.editId || d.id_piece_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>' +
               '<a href="<?= RACINE ?>piece_fournir_cycle/supprimer/' + (d.editId || d.id_piece_cycle) + '" onclick="return confirm(\'Voulez-vous vraiment retirer cette pièce du dossier du cycle ?\')" class="btn btn-sm btn-danger" style="background:#EF4444; color:#fff; border:none; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // AJAX Status Change
  $(document).on('change', '.select-statut-piece-cycle', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>piece_fournir_cycle/changer',
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
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau ou serveur');
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
