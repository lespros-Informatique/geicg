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
            <i data-lucide="trending-up" style="color: #1E3A5F; width: 24px; height: 24px;"></i> Niveaux d'Études & Assignations
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion centralisée du Catalogue des Niveaux et de leurs Assignations aux Filières</p>
        </div>
      </div>

      <!-- Barre d'Onglets Structurée -->
      <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 2px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" data-tab="tab-assignations-niveaux" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #1E3A5F; border-bottom: 3px solid #1E3A5F; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="git-merge" style="width: 16px; height: 16px;"></i> 1. Assignations Filières - Niveaux
        </button>
        <button type="button" class="tab-btn" data-tab="tab-catalogue-niveaux" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="trending-up" style="width: 16px; height: 16px;"></i> 2. Catalogue des Niveaux d'Études
        </button>
      </div>

      <!-- CONTENU DU TAB 1 : ASSIGNATIONS FILIÈRES - NIVEAUX -->
      <div id="tab-assignations-niveaux" class="tab-content" style="display: block;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Table d'Assignation Filières ↔ Niveaux</h2>
          <a href="<?= RACINE ?>filiere_niveau/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Assignation Filière - Niveau
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-assignations-niveaux" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Code</th>
                  <th>Filière d'Études</th>
                  <th>Niveau Rattaché</th>
                  <th class="text-center">Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENU DU TAB 2 : CATALOGUE DES NIVEAUX -->
      <div id="tab-catalogue-niveaux" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Catalogue Général des Niveaux d'Études</h2>
          <a href="<?= RACINE ?>niveau/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Niveau
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-niveaux-catalogue" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Code</th>
                  <th>Intitulé du Niveau</th>
                  <th class="text-center">Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
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

  // Table 1: Assignations Filières - Niveaux
  var tableAssignNiv = $('#table-assignations-niveaux').DataTable({
    ajax: '<?= RACINE ?>filiere_niveau/apiList',
    scrollX: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere_niveau', defaultContent: '-' },
      { data: 'code_filiere_niveau', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_filiere', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non définie') + '</span>'; } },
      { data: 'libelle_niveau', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'statut_filiere_niveau', className: 'text-center', render: function(d) {
        return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700;">Inactif</span>';
      } },
      { data: null, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>filiere_niveau/edition/' + (d.editId || d.id_filiere_niveau) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="<?= RACINE ?>filiere_niveau/details/' + (d.editId || d.id_filiere_niveau) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Niveaux
  var tableNiveauxCat = $('#table-niveaux-catalogue').DataTable({
    ajax: '<?= RACINE ?>niveau/apiList',
    scrollX: true,
    autoWidth: false,
    columns: [
      { data: 'id_niveau', defaultContent: '-' },
      { data: 'code_niveau', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_niveau', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>'; } },
      { data: 'statut_niveau', className: 'text-center', render: function(d) {
        return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700;">Inactif</span>';
      } },
      { data: null, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>niveau/edition/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="<?= RACINE ?>niveau/details/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>