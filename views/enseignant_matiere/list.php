<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
$matieres = $matieres ?? (new ModelMatiere())->getAll();
$enseignants = $enseignants ?? (new ModelEnseignant())->getActifs();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Affectations des Matières & Enseignants par Classe</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Affectation des cours, des enseignants et des coefficients généraux par classe</p>
        </div>
        <button type="button" class="btn btn-primary btn-add-em" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Affectation
        </button>
      </div>

      <!-- Filtres Multi-Critères (Année, Niveau & Classe Select2) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Année Académique</label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Classe</label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-enseignant_matiere" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
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
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Filtrage en cascade Niveau -> Classe
  $('#filter-niveau').on('change', function() {
    var niveauCode = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!niveauCode || !optNiveau || optNiveau === niveauCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#filter-classe option:selected').prop('disabled')) {
      $('#filter-classe').val('').trigger('change.select2');
    } else {
      $('#filter-classe').select2({ width: '100%' });
    }
    table.ajax.reload();
  });

  $('#filter-classe, #filter-annee').on('change', function() {
    table.ajax.reload();
  });

  var table = $('#table-enseignant_matiere').DataTable({
    ajax: {
      url: '<?= RACINE ?>enseignant_matiere/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
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
      { data: 'statut_enseignant_matiere', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-em" data-id="' + row.id_enseignant_matiere + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-em" data-id="' + (d.id_enseignant_matiere) + '" data-enseignant="' + (d.enseignant_code || '') + '" data-matiere="' + (d.matiere_code || '') + '" data-classe="' + (d.classe_code || '') + '" data-coef="' + (d.coefficient || '1.0') + '" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>'
             + '<a href="' + window.RACINE + 'enseignant_matiere/details/' + (d.editId || d.id_enseignant_matiere) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });


  $(document).on('change', '.toggle-statut-em', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>enseignant_matiere/changer',
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

  // GESTION MODALE AJOUT / ÉDITION AFFECTATION ENSEIGNANT-MATIÈRE
  $(document).on('click', '.btn-add-em', function(e) {
    e.preventDefault();
    $('#form-em')[0].reset();
    $('#em_id').val('');
    $('#em_coef').val('1.0');
    $('#modal-em-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Affectation Matière / Enseignant');
    $('#modal-em').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#em_enseignant').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-em', function(e) {
    e.preventDefault();
    $('#form-em')[0].reset();
    var id = $(this).data('id');
    var ens = $(this).data('enseignant');
    var mat = $(this).data('matiere');
    var cls = $(this).data('classe');
    var coef = $(this).data('coef');

    $('#em_id').val(id);
    $('#em_enseignant').val(ens);
    $('#em_matiere').val(mat);
    $('#em_classe').val(cls);
    $('#em_coef').val(coef || '1.0');

    $('#modal-em-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Affectation');
    $('#modal-em').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-em').on('click', function() {
    $('#modal-em').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-em')) {
      $('#modal-em').hide();
    }
  });

  $('#form-em').on('submit', function(e) {
    e.preventDefault();
    var id = $('#em_id').val();
    var url = window.RACINE + (id ? 'enseignant_matiere/edit' : 'enseignant_matiere/add');
    var $btn = $('#btn-submit-em');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: url,
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Affectation enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Affectation enregistrée avec succès');
          $('#modal-em').hide();
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

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : AFFECTATION ENSEIGNANT ↔ MATIÈRE PAR CLASSE          -->
<!-- ========================================================================= -->
<div id="modal-em" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-em-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Affectation Matière / Enseignant
      </h3>
      <button type="button" class="btn-close-modal-em" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-em" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_enseignant_matiere" id="em_id" value="">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Enseignant / Professeur <span style="color: #EF4444;">*</span>
        </label>
        <select name="enseignant_code" id="em_enseignant" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
          <option value="">-- Sélectionner un enseignant --</option>
          <?php foreach ($enseignants as $ens): ?>
            <?php $nomEns = !empty(trim($ens['nom_complet'] ?? '')) ? $ens['nom_complet'] : ($ens['nom'] ?? $ens['code_enseignant']); ?>
            <option value="<?= htmlspecialchars($ens['code_enseignant']) ?>">
              <?= htmlspecialchars($nomEns . ' (' . $ens['code_enseignant'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Matière Enseignée <span style="color: #EF4444;">*</span>
        </label>
        <select name="matiere_code" id="em_matiere" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
          <option value="">-- Sélectionner une matière --</option>
          <?php foreach ($matieres as $m): ?>
            <option value="<?= htmlspecialchars($m['code_matiere']) ?>">
              <?= htmlspecialchars($m['libelle_matiere'] . ' (' . $m['code_matiere'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 22px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Classe Attribuée <span style="color: #EF4444;">*</span>
          </label>
          <select name="classe_code" id="em_classe" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
            <option value="">-- Sélectionner la classe --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= htmlspecialchars($c['code_classe']) ?>">
                <?= htmlspecialchars($c['libelle_classe']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Coefficient <span style="color: #EF4444;">*</span>
          </label>
          <input type="number" step="0.25" min="0.1" name="coefficient" id="em_coef" required value="1.0" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 800; font-size: 14px; text-align: center;">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-em" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-em" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
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
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
