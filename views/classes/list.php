<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$filieres = $filieres ?? (new ModelFiliere())->getFilieresAssocieesAuxCycles();
$niveaux = $niveaux ?? (new ModelNiveau())->getAll();
$currentAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Classes & Promotions</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre des promotions d'étudiants</p>
        </div>
        <button type="button" class="btn btn-primary btn-add-classe" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe
        </button>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-classes" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Libellé de la Classe</th>
                <th style="padding: 12px;">Filière</th>
                <th style="padding: 12px;">Niveau</th>
                <th style="padding: 12px; text-align: center;">Capacité</th>
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
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UNE CLASSE                        -->
<!-- ========================================================================= -->
<div id="modal-classe" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-classe-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe / Promotion
      </h3>
      <button type="button" class="btn-close-modal-classe" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-classe" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_classe" id="classe_id" value="">
      <input type="hidden" name="annee_code" id="classe_annee" value="<?= htmlspecialchars($currentAnneeCode) ?>">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Filière rattachée <span style="color: #EF4444;">*</span>
        </label>
        <select name="filiere_code" id="classe_filiere" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
          <option value="">-- Choisir une filière --</option>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= htmlspecialchars($f['code_filiere']) ?>" data-nom="<?= htmlspecialchars($f['libelle_filiere']) ?>">
              <?= htmlspecialchars($f['libelle_filiere']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Niveau d'études <span style="color: #EF4444;">*</span>
        </label>
        <select name="niveau_code" id="classe_niveau" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
          <option value="">-- Choisir un niveau --</option>
          <?php foreach ($niveaux as $n): ?>
            <option value="<?= htmlspecialchars($n['code_niveau']) ?>" data-nom="<?= htmlspecialchars($n['libelle_niveau']) ?>">
              <?= htmlspecialchars($n['libelle_niveau']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Libellé de la classe <span style="color: #EF4444;">*</span>
          <span style="font-size: 11px; font-weight: 500; color: #64748B; margin-left: 6px;">(Généré automatiquement)</span>
        </label>
        <input type="text" name="libelle_classe" id="classe_libelle" required placeholder="Ex: IDA - Première année" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Modifiable pour ajouter une section (ex: Gr. A, Gr. B, Soir).</small>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px; align-items: center;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Capacité d'accueil</label>
          <input type="number" min="1" max="500" name="capacite_max_classe" id="classe_capacite" value="35" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
        </div>
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
          <div style="display: flex; align-items: center; gap: 10px; padding-top: 4px;">
            <label style="position: relative; display: inline-block; width: 42px; height: 22px; margin: 0; cursor: pointer;">
              <input type="checkbox" name="statut_classe" id="classe_statut" value="actif" checked style="opacity: 0; width: 0; height: 0;">
              <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #15803D; transition: .3s; border-radius: 22px;" id="statut_classe_slider">
                <span style="position: absolute; content: ''; height: 16px; width: 16px; left: 22px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%;" id="statut_classe_knob"></span>
              </span>
            </label>
            <span id="statut_classe_text" style="font-weight: 700; font-size: 13px; color: #15803D;">Actif</span>
          </div>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-classe" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-classe" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
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
  var table = $('#table-classes').DataTable({
    ajax: {
      url: '<?= RACINE ?>classe/apiList'
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_classe', width: '110px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_classe', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_filiere', render: function(d, type, row) {
        if (type !== 'display') return d || row.filiere_code || '';
        return '<span style="color:#1E3A5F; font-weight:600;">' + (d || row.filiere_code || '-') + '</span>';
      }},
      { data: 'libelle_niveau', render: function(d, type, row) {
        if (type !== 'display') return d || row.niveau_code || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || row.niveau_code || '-') + '</span>';
      }},
      { data: 'capacite_max_classe', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#475569;">' + (d ? d + ' places' : '-') + '</span>';
      }},
      { data: 'statut_classe', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-classe" data-id="' + row.id_classe + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-classe" data-id="' + (d.id_classe) + '" data-libelle="' + (d.libelle_classe ? $('<div>').text(d.libelle_classe).html() : '') + '" data-filiere="' + (d.filiere_code || '') + '" data-niveau="' + (d.niveau_code || '') + '" data-capacite="' + (d.capacite_max_classe || '35') + '" data-statut="' + (d.statut_classe || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + window.RACINE + 'classe/details/' + (d.editId || d.id_classe) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-classe', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>classe/changer',
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
  function updateStatutClasseUI(isActif) {
    if (isActif) {
      $('#statut_classe_slider').css('background-color', '#15803D');
      $('#statut_classe_knob').css('left', '22px');
      $('#statut_classe_text').css('color', '#15803D').text('Actif');
    } else {
      $('#statut_classe_slider').css('background-color', '#CBD5E1');
      $('#statut_classe_knob').css('left', '3px');
      $('#statut_classe_text').css('color', '#64748B').text('Inactif');
    }
  }

  $('#classe_statut').on('change', function() {
    updateStatutClasseUI($(this).is(':checked'));
  });

  // GÉNÉRATION AUTOMATIQUE DU LIBELLÉ DE LA CLASSE
  function autoGenerateLibelleClasse() {
    var fNom = $('#classe_filiere option:selected').data('nom') || '';
    var nNom = $('#classe_niveau option:selected').data('nom') || '';
    if (fNom && nNom) {
      $('#classe_libelle').val(fNom + ' - ' + nNom);
    } else if (fNom) {
      $('#classe_libelle').val(fNom);
    } else if (nNom) {
      $('#classe_libelle').val(nNom);
    }
  }

  $('#classe_filiere, #classe_niveau').on('change', function() {
    // Ne régénérer que si on est en création ou si l'utilisateur change de filière/niveau
    var curVal = $('#classe_libelle').val().trim();
    if ($('#classe_id').val() === '' || curVal === '') {
      autoGenerateLibelleClasse();
    }
  });

  // GESTION MODALE AJOUT / ÉDITION CLASSE
  $(document).on('click', '.btn-add-classe', function(e) {
    e.preventDefault();
    $('#form-classe')[0].reset();
    $('#classe_id').val('');
    $('#classe_capacite').val('35');
    $('#classe_statut').prop('checked', true);
    updateStatutClasseUI(true);
    $('#modal-classe-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe / Promotion');
    $('#modal-classe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#classe_filiere').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-classe', function(e) {
    e.preventDefault();
    $('#form-classe')[0].reset();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var filiere = $(this).data('filiere');
    var niveau = $(this).data('niveau');
    var capacite = $(this).data('capacite');
    var statut = $(this).data('statut');

    $('#classe_id').val(id);
    $('#classe_libelle').val(libelle);
    $('#classe_filiere').val(filiere);
    $('#classe_niveau').val(niveau);
    $('#classe_capacite').val(capacite || '35');

    var isActif = (statut === 'actif');
    $('#classe_statut').prop('checked', isActif);
    updateStatutClasseUI(isActif);

    $('#modal-classe-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Classe / Promotion');
    $('#modal-classe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#classe_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-classe').on('click', function() {
    $('#modal-classe').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-classe')) {
      $('#modal-classe').hide();
    }
  });

  $('#form-classe').on('submit', function(e) {
    e.preventDefault();
    var id = $('#classe_id').val();
    var url = window.RACINE + (id ? 'classe/edit' : 'classe/add');
    var $btn = $('#btn-submit-classe');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    var formData = $(this).serializeArray();
    var hasStatut = false;
    for (var i = 0; i < formData.length; i++) {
      if (formData[i].name === 'statut_classe') {
        hasStatut = true;
        break;
      }
    }
    if (!hasStatut) {
      formData.push({ name: 'statut_classe', value: 'inactif' });
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
          if (typeof showToast === 'function') showToast(res.message || 'Classe enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Classe enregistrée avec succès');
          $('#modal-classe').hide();
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
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
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
