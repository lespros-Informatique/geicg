<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Affectations de Cours & Professeurs</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Affectations de Cours & Professeurs</p>
        </div>
        <a href="<?= RACINE ?>enseignant_matiere/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Affectation de Cours
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow-x: auto;">
        <table id="table-enseignant_matiere" class="table display nowrap" style="width:100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
              <th style="padding: 12px;">ID</th>
              <th style="padding: 12px;">Professeur</th>
              <th style="padding: 12px;">Matière</th>
              <th style="padding: 12px;">Classe</th>
              <th style="padding: 12px; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<script>
$(document).ready(function() {
  $('#table-enseignant_matiere').DataTable({
    ajax: '<?= RACINE ?>enseignant_matiere/apiList',
    scrollX: true,
    autoWidth: false,
    columns: [
      { data: 'id_enseignant_matiere', defaultContent: '-' },
      { data: 'enseignant_code', defaultContent: '-' },
      { data: 'matiere_code', defaultContent: '-' },
      { data: 'classe_code', defaultContent: '-' },
      { data: null, render: function(d) {
        return '<a href="' + window.RACINE + 'enseignant_matiere/edition/' + d.editId + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'enseignant_matiere/details/' + d.editId + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
