<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Affectations de Cours & Professeurs</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion des affectations de matières, coefficients et enseignants par classe</p>
        </div>
        <a href="<?= RACINE ?>enseignant_matiere/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Affectation
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-enseignant_matiere" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Enseignant / Professeur</th>
                <th style="padding: 12px;">Matière Enseignée</th>
                <th style="padding: 12px;">Classe Attribuée</th>
                <th style="padding: 12px; text-align: center;">Coefficient</th>
                <th style="padding: 12px;" class="text-center">Statut</th>
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
  $('#table-enseignant_matiere').DataTable({
    ajax: '<?= RACINE ?>enseignant_matiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_enseignant_matiere', defaultContent: '-', width: '50px' },
      { data: 'enseignant_nom', render: function(d, type, row) {
        if (type !== 'display') return d || row.enseignant_code || '';
        var nom = (d && d.trim().length > 0) ? d : row.enseignant_code;
        return '<strong style="color:#0F172A;">' + (nom || '-') + '</strong>';
      }},
      { data: 'libelle_matiere', render: function(d, type, row) {
        if (type !== 'display') return d || row.matiere_code || '';
        return '<span style="color:#1E3A5F; font-weight:600;">' + (d || row.matiere_code || '-') + '</span>';
      }},
      { data: 'libelle_classe', render: function(d, type, row) {
        if (type !== 'display') return d || row.classe_code || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || row.classe_code || '<span style="color:#94A3B8; font-style:italic;">Non assignée</span>') + '</span>';
      }},
      { data: 'coefficient', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || 1;
        return '<span style="display:inline-block; padding: 3px 10px; border-radius: 6px; font-size: 13px; font-weight: 800; background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;">' + (d || '1.0') + '</span>';
      }},
      { data: 'statut_enseignant_matiere', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif'
          ? '<span style="background:#DCFCE7;color:#15803D;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Actif</span>'
          : '<span style="background:#FEE2E2;color:#B91C1C;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'enseignant_matiere/edition/' + (d.editId || d.id_enseignant_matiere) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + window.RACINE + 'enseignant_matiere/details/' + (d.editId || d.id_enseignant_matiere) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
