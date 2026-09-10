<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Niveaux d'Études</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Catalogue général des niveaux académiques (ex: Licence 1, Licence 2, Master 1, Master 2...)</p>
        </div>
        <div>
          <a href="<?= RACINE ?>niveau/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau
          </a>
        </div>
      </div>

      <!-- CATALOGUE DES NIVEAUX -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-niveaux-catalogue" class="table display nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
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
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  var tableNiveauxCat = $('#table-niveaux-catalogue').DataTable({
    ajax: '<?= RACINE ?>niveau/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_niveau', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_niveau', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>'; } },
      { data: 'statut_niveau', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-niveau" data-id="' + row.id_niveau + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>niveau/edition/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="<?= RACINE ?>niveau/details/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  function bindAjaxToggle(selector, url, datatable) {
    $(document).off('change', selector).on('change', selector, function() {
      var $checkbox = $(this);
      var id = $checkbox.data('id');
      $checkbox.prop('disabled', true);
      $.ajax({
        url: url,
        type: 'POST',
        data: { id_niveau: id, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour', 'success');
            datatable.ajax.reload(null, false);
          } else {
            if (typeof showToast === 'function') showToast(res.message || 'Erreur', 'error');
            $checkbox.prop('checked', !$checkbox.prop('checked')).prop('disabled', false);
          }
        },
        error: function() {
          if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
          $checkbox.prop('checked', !$checkbox.prop('checked')).prop('disabled', false);
        }
      });
    });
  }

  bindAjaxToggle('.toggle-statut-niveau', '<?= RACINE ?>niveau/changer', tableNiveauxCat);
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>