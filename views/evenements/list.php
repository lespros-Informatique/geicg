<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Événements & Actualités</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Événements & Actualités</p>
        </div>
        <button type="button" class="btn btn-primary btn-add-evenement" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Événement
        </button>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-evenements" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Titre de l'Événement</th>
                <th style="padding: 12px;">Date & Heure</th>
                <th style="padding: 12px;">Lieu / Campus</th>
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

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UN ÉVÉNEMENT                      -->
<!-- ========================================================================= -->
<div id="modal-evenement" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-evenement-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Événement
      </h3>
      <button type="button" class="btn-close-modal-evenement" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-evenement" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_evenement" id="evenement_id" value="">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Titre de l'événement <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="titre_evenement" id="evenement_titre" required placeholder="Ex: Journée d'Orientation & Conférence IA" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de l'événement <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="date_evenement" id="evenement_date" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Lieu / Campus
          </label>
          <input type="text" name="lieu_evenement" id="evenement_lieu" placeholder="Ex: Grand Amphithéâtre" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Description détaillée
        </label>
        <textarea name="description_evenement" id="evenement_desc" rows="3" placeholder="Ex: Présentation des filières et opportunités de stage..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;"></textarea>
      </div>

      <div class="form-group" style="margin-bottom: 22px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <div style="display: flex; align-items: center; gap: 10px;">
          <label style="position: relative; display: inline-block; width: 42px; height: 22px; margin: 0; cursor: pointer;">
            <input type="checkbox" name="statut_evenement" id="evenement_statut" value="actif" checked style="opacity: 0; width: 0; height: 0;">
            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #15803D; transition: .3s; border-radius: 22px;" id="statut_slider">
              <span style="position: absolute; content: ''; height: 16px; width: 16px; left: 22px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%;" id="statut_knob"></span>
            </span>
          </label>
          <span id="statut_text" style="font-weight: 700; font-size: 13px; color: #15803D;">Actif (Visible)</span>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-evenement" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-evenement" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<style>
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
$(document).ready(function() {
  var table = $('#table-evenements').DataTable({
    ajax: '<?= RACINE ?>evenement/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'titre_evenement', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'date_evenement', width: '130px', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">-</span>';
        var p = d.split('-');
        var formatted = p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
        return '<span style="font-weight:600; color:#334155; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="calendar" style="width:13px;height:13px;color:#1E3A5F;"></i> ' + formatted + '</span>';
      }},
      { data: 'lieu_evenement', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">Non précisé</span>';
        return '<span style="color:#475569; font-weight:500;"><i data-lucide="map-pin" style="width:13px;height:13px;color:#64748B;display:inline-block;vertical-align:middle;margin-right:4px;"></i>' + d + '</span>';
      }},
      { data: 'statut_evenement', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-event" data-id="' + row.id_evenement + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-evenement" data-id="' + d.id_evenement + '" data-titre="' + (d.titre_evenement ? $('<div>').text(d.titre_evenement).html() : '') + '" data-date="' + (d.date_evenement || '') + '" data-lieu="' + (d.lieu_evenement ? $('<div>').text(d.lieu_evenement).html() : '') + '" data-desc="' + (d.description_evenement ? $('<div>').text(d.description_evenement).html() : '') + '" data-statut="' + (d.statut_evenement || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + window.RACINE + 'evenement/details/' + (d.editId || d.id_evenement) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-event', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>evenement/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  // GESTION DU TOGGLE STATUT DANS LE MODAL
  function updateStatutUI(isActif) {
    if (isActif) {
      $('#statut_slider').css('background-color', '#15803D');
      $('#statut_knob').css('left', '22px');
      $('#statut_text').css('color', '#15803D').text('Actif (Visible)');
    } else {
      $('#statut_slider').css('background-color', '#CBD5E1');
      $('#statut_knob').css('left', '3px');
      $('#statut_text').css('color', '#64748B').text('Inactif (Masqué)');
    }
  }

  $('#evenement_statut').on('change', function() {
    updateStatutUI($(this).is(':checked'));
  });

  // GESTION MODALE AJOUT / ÉDITION ÉVÉNEMENT
  $(document).on('click', '.btn-add-evenement', function(e) {
    e.preventDefault();
    $('#form-evenement')[0].reset();
    $('#evenement_id').val('');
    $('#evenement_date').val(new Date().toISOString().split('T')[0]);
    $('#evenement_statut').prop('checked', true);
    updateStatutUI(true);
    $('#modal-evenement-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Événement');
    $('#modal-evenement').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#evenement_titre').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-evenement', function(e) {
    e.preventDefault();
    $('#form-evenement')[0].reset();
    var id = $(this).data('id');
    var titre = $(this).data('titre');
    var date = $(this).data('date');
    var lieu = $(this).data('lieu');
    var desc = $(this).data('desc');
    var statut = $(this).data('statut');

    $('#evenement_id').val(id);
    $('#evenement_titre').val(titre);
    $('#evenement_date').val(date);
    $('#evenement_lieu').val(lieu);
    $('#evenement_desc').val(desc);

    var isActif = (statut === 'actif');
    $('#evenement_statut').prop('checked', isActif);
    updateStatutUI(isActif);

    $('#modal-evenement-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Événement');
    $('#modal-evenement').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#evenement_titre').focus(); }, 100);
  });

  $('.btn-close-modal-evenement').on('click', function() {
    $('#modal-evenement').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-evenement')) {
      $('#modal-evenement').hide();
    }
  });

  $('#form-evenement').on('submit', function(e) {
    e.preventDefault();
    var id = $('#evenement_id').val();
    var url = window.RACINE + (id ? 'evenement/edit' : 'evenement/add');
    var $btn = $('#btn-submit-evenement');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    var formData = $(this).serializeArray();
    var hasStatut = false;
    for (var i = 0; i < formData.length; i++) {
      if (formData[i].name === 'statut_evenement') {
        hasStatut = true;
        break;
      }
    }
    if (!hasStatut) {
      formData.push({ name: 'statut_evenement', value: 'inactif' });
    }

    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Événement enregistré avec succès');
          $('#modal-evenement').hide();
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          else alert(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (window.toastr) toastr.error(msg);
        else alert(msg);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
