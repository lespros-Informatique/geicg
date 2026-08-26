<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Utilisateurs Système & Sécurité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion des comptes du personnel, rôles attribués et accès sécurisés</p>
        </div>
        <a href="<?= RACINE ?>user/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> Nouvel Utilisateur
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-users" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Nom complet</th>
                <th style="padding: 12px;">Contact (Email / Tél)</th>
                <th style="padding: 12px;">Fonction</th>
                <th style="padding: 12px;">Rôle Attribué</th>
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
  $('#table-users').DataTable({
    ajax: '<?= RACINE ?>user/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id', defaultContent: '-', width: '50px' },
      { data: 'code', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'nom', render: function(d, type, row) {
        if (type !== 'display') return (d || '') + ' ' + (row.prenom || '');
        var nomComplet = (d || '') + ' ' + (row.prenom || '');
        return '<strong style="color:#0F172A;">' + (nomComplet.trim() || '-') + '</strong>';
      }},
      { data: 'email', render: function(d, type, row) {
        if (type !== 'display') return d || row.telephone || '';
        var res = '';
        if (d) res += '<div style="font-weight:600; color:#1E3A5F; font-size:13px;">' + d + '</div>';
        if (row.telephone) res += '<div style="font-size:12px; color:#64748B;">' + row.telephone + '</div>';
        return res || '-';
      }},
      { data: 'fonction', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || '-') + '</span>';
      }},
      { data: 'role', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="background:#EFF6FF; color:#1E3A5F; font-weight:700; padding:4px 10px; border-radius:8px; font-size:12px; display:inline-block;">' + (d || 'Non attribué') + '</span>';
      }},
      { data: 'statut', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Actif</span>' : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Inactif</span>';
      } },
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'user/edition/' + (d.editId || d.id) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'user/details/' + (d.editId || d.id) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Profil</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
