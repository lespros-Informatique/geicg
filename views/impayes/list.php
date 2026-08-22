<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Suivi des Relances & Impayés Scolaires</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Historique des relances ébauchées, expédiées et suivi du recouvrement</p>
        </div>
        <a href="<?= RACINE ?>impayes/formulaire" class="btn btn-primary" style="background: #D97706; border-color: #D97706; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="send" style="width: 18px; height: 18px;"></i> Émettre une Relance Impayé
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box;">
        <table id="table-impayes" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Code Relance</th>
              <th>Élève / Étudiant</th>
              <th>Niveau de Relance</th>
              <th>Canal</th>
              <th>Montant Impayé</th>
              <th>Date d'Émission</th>
              <th>Actions</th>
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
  if (window.lucide) lucide.createIcons();

  $('#table-impayes').DataTable({
    ajax: {
      url: '<?= RACINE ?>impayes/apiList',
      type: 'GET'
    },
    columns: [
      { data: 'id_relance', defaultContent: '-' },
      { 
        data: 'code_relance', 
        render: function(d) {
          return '<code style="font-weight:700; color:#D97706;">' + (d || '-') + '</code>';
        }
      },
      { data: 'etudiant_code', defaultContent: '-' },
      { 
        data: 'niveau_relance', 
        render: function(d) {
          if (d === 'rappel_amiable') return '<span class="badge" style="background:#EFF6FF; color:#1E40AF; padding:5px 10px; border-radius:6px; font-weight:700;">Rappel Amiable</span>';
          if (d === 'relance_ferme') return '<span class="badge" style="background:#FEF3C7; color:#92400E; padding:5px 10px; border-radius:6px; font-weight:700;">Relance Ferme (48h)</span>';
          return '<span class="badge" style="background:#FEE2E2; color:#991B1B; padding:5px 10px; border-radius:6px; font-weight:700;">Mise en Demeure</span>';
        }
      },
      { 
        data: 'canal_relance',
        render: function(d) {
          return '<span style="font-weight:600; text-transform:uppercase; font-size:12px;">' + (d || 'SMS') + '</span>';
        }
      },
      { 
        data: 'montant_impaye', 
        render: function(d) {
          return '<strong style="color:#DC2626;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
        }
      },
      { 
        data: 'created_at_relance', 
        render: function(d) {
          if (!d) return '-';
          return new Date(d).toLocaleDateString('fr-FR') + ' ' + new Date(d).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
        }
      },
      { 
        data: null, 
        orderable: false,
        render: function(d) {
          return '<a href="' + window.RACINE + 'impayes/edition/' + (d.editId || d.id_relance) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; padding:4px 10px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Voir</a>';
        }
      }
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
    },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
