<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête Général du Hub -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layers" style="color: #1E3A5F; width: 24px; height: 24px;"></i> Filières & Cycles d'Études
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion centralisée du Catalogue des Filières, des Cycles et de leurs Assignations</p>
        </div>
      </div>

      <!-- Barre d'Onglets Structurée -->
      <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 2px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" data-tab="tab-assignations" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #1E3A5F; border-bottom: 3px solid #1E3A5F; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="git-merge" style="width: 16px; height: 16px;"></i> 1. Assignations Filières - Cycles
        </button>
        <button type="button" class="tab-btn" data-tab="tab-filieres" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="book-open" style="width: 16px; height: 16px;"></i> 2. Catalogue des Filières
        </button>
        <button type="button" class="tab-btn" data-tab="tab-cycles" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 16px; height: 16px;"></i> 3. Cycles d'Études
        </button>
      </div>

      <!-- CONTENU DU TAB 1 : ASSIGNATIONS -->
      <div id="tab-assignations" class="tab-content" style="display: block;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Table d'Assignation Filières ↔ Cycles</h2>
          <a href="<?= RACINE ?>filiere_cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Assignation
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow-x: auto;">
          <table id="table-assignations" class="table display responsive nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th>Cycle D'Études</th>
                <th>Filière Associée</th>
                <th>Statut</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- CONTENU DU TAB 2 : CATALOGUE FILIÈRES -->
      <div id="tab-filieres" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Catalogue Général des Filières</h2>
          <a href="<?= RACINE ?>filiere/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter une Filière
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow-x: auto;">
          <table id="table-filieres-catalogue" class="table display responsive nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th>Code</th>
                <th>Nom de la Filière</th>
                <th>Description</th>
                <th>Statut</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- CONTENU DU TAB 3 : CYCLES D'ÉTUDES -->
      <div id="tab-cycles" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Référentiel des Cycles d'Études</h2>
          <a href="<?= RACINE ?>cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Cycle
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow-x: auto;">
          <table id="table-cycles-list" class="table display responsive nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th>Code Cycle</th>
                <th>Libellé du Cycle</th>
                <th>Description</th>
                <th>Statut</th>
                <th style="text-align: right;">Actions</th>
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

  // Gestion des Onglets
  $('.tab-btn').on('click', function() {
    $('.tab-btn').css({ 'color': '#64748B', 'border-bottom-color': 'transparent' }).removeClass('active');
    $(this).css({ 'color': '#1E3A5F', 'border-bottom-color': '#1E3A5F' }).addClass('active');
    $('.tab-content').hide();
    var targetTab = $(this).data('tab');
    $('#' + targetTab).show();
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  });

  // Table 1: Assignations
  var tableAssign = $('#table-assignations').DataTable({
    ajax: '<?= RACINE ?>filiere_cycle/apiList',
    columns: [
      { data: 'libelle_cycle', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'libelle_filiere', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'statut_filiere_cycle', render: function(d) { return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>'; } },
      {
        data: null, orderable: false, className: 'text-end',
        render: function(data, type, row) {
          return '<a href="<?= RACINE ?>filiere_cycle/edition/' + row.editId + '" class="btn btn-sm" style="background:#F1F5F9; color:#334155; border-radius:6px; padding:6px 10px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i></a>';
        }
      }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Filières
  var tableFilieres = $('#table-filieres-catalogue').DataTable({
    ajax: '<?= RACINE ?>filiere/apiList',
    columns: [
      { data: 'code_filiere', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_filiere', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>'; } },
      { data: 'description_filiere', render: function(d) { return d || '-'; } },
      { data: 'statut_filiere', render: function(d) { return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>'; } },
      {
        data: null, orderable: false, className: 'text-end',
        render: function(data, type, row) {
          return '<a href="<?= RACINE ?>filiere/edition/' + row.editId + '" class="btn btn-sm" style="background:#F1F5F9; color:#334155; border-radius:6px; padding:6px 10px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i></a>';
        }
      }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 3: Cycles d'Études
  var tableCycles = $('#table-cycles-list').DataTable({
    ajax: '<?= RACINE ?>cycle/apiList',
    columns: [
      { data: 'code_cycle', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_cycle', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>'; } },
      { data: 'description_cycle', render: function(d) { return d || '-'; } },
      { data: 'statut_cycle', render: function(d) { return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>'; } },
      {
        data: null, orderable: false, className: 'text-end',
        render: function(data, type, row) {
          return '<a href="<?= RACINE ?>cycle/edition/' + row.editId + '" class="btn btn-sm" style="background:#F1F5F9; color:#334155; border-radius:6px; padding:6px 10px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i></a>';
        }
      }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>