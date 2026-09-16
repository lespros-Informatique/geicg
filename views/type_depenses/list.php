<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Types de Dépenses</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Types de Dépenses</p>
        </div>
        <button type="button" class="btn btn-primary btn-add-type-depense" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Catégorie de Dépense
        </button>
      </div>

      <!-- Navigation Tabs (Dépenses & Engagements vs Types de Dépenses) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
        <a href="<?= RACINE ?>depense/list" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="file-minus" style="width: 17px; height: 17px;"></i> Dépenses & Engagements
        </a>
        <a href="<?= RACINE ?>type_depense/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="tags" style="width: 17px; height: 17px;"></i> Types / Catégories de Dépenses
        </a>
      </div>

      <!-- Quick Add Form Card pour Catégorie de Dépense (Soumission AJAX) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #CBD5E1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Catégorie / Type de Dépense
        </h3>
        <form id="form-quick-add-type-depense" action="<?= RACINE ?>type_depense/add" method="POST" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <div style="flex: 1; min-width: 280px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom / Désignation de la catégorie *</label>
            <input type="text" name="libelle_type_depense" id="quick_libelle_type_depense" required placeholder="Ex: Fournitures de bureau, Électricité & Eau, Maintenance des locaux..." class="form-control" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; width: 100%; box-sizing: border-box;">
          </div>
          <button type="submit" id="btn-quick-save" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; height: 42px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer la Catégorie
          </button>
        </form>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-type_depenses" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code Catégorie</th>
                <th style="padding: 12px;">Désignation Catégorie</th>
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

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : MODIFIER / AJOUTER TYPE DE DÉPENSE -->
<!-- ========================================================================= -->
<div id="modal-type-depense" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-type-depense-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Catégorie de Dépense
      </h3>
      <button type="button" class="btn-close-modal-td" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-type-depense-modal" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_type_depense" id="td_modal_id" value="">

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Désignation de la catégorie <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_type_depense" id="td_modal_libelle" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-td" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-td-modal" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  var table = $('#table-type_depenses').DataTable({
    ajax: '<?= RACINE ?>type_depense/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_type_depense', defaultContent: '-' },
      { data: 'libelle_type_depense', defaultContent: '-' },
      { data: null, orderable: false, render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_type_depense || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-td" data-id="' + d.id_type_depense + '" data-libelle="' + safeLibelle + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + window.RACINE + 'type_depense/details/' + (d.editId || d.id_type_depense) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bouton Ajouter en haut -> Ouvre la modale ou focus le formulaire rapide
  $('.btn-add-type-depense').on('click', function() {
    $('#td_modal_id').val('');
    $('#td_modal_libelle').val('');
    $('#modal-type-depense-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Catégorie de Dépense');
    $('#modal-type-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#td_modal_libelle').focus(); }, 100);
  });

  // Bouton Éditer dans la table -> Ouvre la modale d'édition pré-remplie
  $(document).on('click', '.btn-edit-td', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');

    $('#td_modal_id').val(id);
    $('#td_modal_libelle').val(libelle);
    $('#modal-type-depense-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Catégorie de Dépense');
    $('#modal-type-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#td_modal_libelle').focus(); }, 100);
  });

  // Fermer la modale
  $('.btn-close-modal-td').on('click', function() {
    $('#modal-type-depense').css('display', 'none');
  });
  $('#modal-type-depense').on('click', function(e) {
    if ($(e.target).is('#modal-type-depense')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX du formulaire rapide
  $('#form-quick-add-type-depense').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-quick-save');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: '<?= RACINE ?>type_depense/add',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer la Catégorie');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Catégorie créée avec succès');
          $('#quick_libelle_type_depense').val('');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer la Catégorie');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Soumission AJAX de la modale d'édition / ajout
  $('#form-type-depense-modal').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#td_modal_id').val();
    var url = isEdit ? '<?= RACINE ?>type_depense/edit' : '<?= RACINE ?>type_depense/add';
    var $btn = $('#btn-save-td-modal');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $('#modal-type-depense').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.toastr) toastr.error(msg);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
