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
          <button type="button" class="btn btn-primary btn-add-niveau" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau
          </button>
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

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UN NIVEAU D'ÉTUDES -->
<!-- ========================================================================= -->
<div id="modal-niveau" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-niveau-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau
      </h3>
      <button type="button" class="btn-close-modal-niveau" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-niveau" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_niveau" id="niveau_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Intitulé complet du niveau <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_niveau" id="niveau_libelle" required placeholder="Ex: Licence 1, Master 2, BTS 1ère Année..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Abréviation / Sigle court
        </label>
        <input type="text" name="slug_niveau" id="niveau_slug" placeholder="Ex: L1, L2, M1, M2, BTS1..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Utilisé pour les affichages compacts dans les listes et emplois du temps.</small>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_niveau" id="niveau_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-niveau" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-niveau" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
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
      { data: 'libelle_niveau', render: function(d, type, row) { 
        var useSlug = row.use_slug_niveau;
        if (useSlug && row.slug_niveau) {
          return '<span style="font-weight:700; color:#0F172A;">' + row.slug_niveau + '</span>' +
                 ' <span style="font-size:12px; color:#64748B; margin-left:6px;">(' + (d || '-') + ')</span>';
        }
        var html = '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
        if (row.slug_niveau) {
          html += ' <span class="badge" style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:2px 6px; border-radius:4px; font-weight:700; font-size:11px; margin-left:6px;">' + row.slug_niveau + '</span>';
        }
        return html;
      } },
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
        var safeLibelle = $('<div>').text(d.libelle_niveau || '').html();
        var safeSlug = $('<div>').text(d.slug_niveau || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-niveau" data-id="' + d.id_niveau + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-statut="' + (d.statut_niveau || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="<?= RACINE ?>niveau/details/' + (d.editId || d.id_niveau) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Modal : Ajouter
  $('.btn-add-niveau').on('click', function() {
    $('#form-niveau')[0].reset();
    $('#niveau_id').val('');
    $('#modal-niveau-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau');
    $('#modal-niveau').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#niveau_libelle').focus(); }, 100);
  });

  // Modal : Modifier
  $(document).on('click', '.btn-edit-niveau', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var statut = $(this).data('statut');

    $('#niveau_id').val(id);
    $('#niveau_libelle').val(libelle);
    $('#niveau_slug').val(slug);
    $('#niveau_statut').val(statut || 'actif');
    $('#modal-niveau-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Niveau');
    $('#modal-niveau').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#niveau_libelle').focus(); }, 100);
  });

  // Modal : Fermer
  $('.btn-close-modal-niveau').on('click', function() {
    $('#modal-niveau').css('display', 'none');
  });
  $('#modal-niveau').on('click', function(e) {
    if ($(e.target).is('#modal-niveau')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX (Ajout & Modification)
  $('#form-niveau').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#niveau_id').val();
    var url = isEdit ? '<?= RACINE ?>niveau/edit' : '<?= RACINE ?>niveau/add';
    var $btn = $('#btn-save-niveau');

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
          if (window.showToast) showToast(res.message || 'Opération réussie', 'success');
          else if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $('#modal-niveau').css('display', 'none');
          tableNiveauxCat.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
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
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Bascule statut AJAX
  $(document).off('change', '.toggle-statut-niveau').on('change', '.toggle-statut-niveau', function() {
    var $checkbox = $(this);
    var id = $checkbox.data('id');
    $checkbox.prop('disabled', true);
    $.ajax({
      url: '<?= RACINE ?>niveau/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { id_niveau: id, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Statut mis à jour', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour');
          tableNiveauxCat.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur');
          $checkbox.prop('checked', !$checkbox.prop('checked')).prop('disabled', false);
        }
      },
      error: function() {
        if (window.showToast) showToast('Erreur serveur', 'error');
        else if (window.toastr) toastr.error('Erreur serveur');
        $checkbox.prop('checked', !$checkbox.prop('checked')).prop('disabled', false);
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>