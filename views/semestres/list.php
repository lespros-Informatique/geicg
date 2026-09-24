<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Semestres & Périodes</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Semestres & Périodes</p>
        </div>
        <button type="button" class="btn btn-primary btn-add-semestre" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Semestre
        </button>
      </div>



      <!-- Navigation Tabs (Années Académiques vs Semestres & Périodes) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
        <a href="<?= RACINE ?>annee/list" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="calendar-range" style="width: 17px; height: 17px;"></i> Années Académiques
        </a>
        <a href="<?= RACINE ?>semestre/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="clock" style="width: 17px; height: 17px;"></i> Semestres & Périodes
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-semestres" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Libellé Semestre</th>
                <th style="padding: 12px;">Année Académique</th>
                <th style="padding: 12px;">Date Début</th>
                <th style="padding: 12px;">Date Fin</th>
                <th style="padding: 12px;">Statut</th>
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

  var table = $('#table-semestres').DataTable({
    ajax: '<?= RACINE ?>semestre/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_semestre', defaultContent: '-', width: '110px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_semestre', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_annee', defaultContent: '<span style="color:#94A3B8; font-style:italic;">Non définie</span>', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#1E3A5F; font-weight:600;">' + (d || '<span style="color:#94A3B8; font-style:italic;">Non définie</span>') + '</span>';
      }},
      { data: 'date_debut_semestre', defaultContent: '-' },
      { data: 'date_fin_semestre', defaultContent: '-' },
      { data: 'statut_semestre', width: '130px', className: 'text-center', render: function(d, type, row) {
        if (d === 'actif') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Active en cours</span>';
        } else if (d === 'planifie') {
          return '<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En préparation</span>';
        } else {
          return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturé</span>';
        }
      }},
      { data: null, width: '220px', orderable: false, render: function(d) {
        var isActif = (d.statut_semestre === 'actif');
        var isPlanifie = (d.statut_semestre === 'planifie');

        var statusBtn = '';
        if (isActif) {
          statusBtn = '<button type="button" class="btn btn-sm btn-warning btn-activate-semestre" data-id="' + d.id_semestre + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer; background:#F59E0B; border-color:#F59E0B; color:#FFFFFF;" title="Clôturer ce semestre"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturer</button>';
        } else if (isPlanifie) {
          statusBtn = '<button type="button" class="btn btn-sm btn-success btn-activate-semestre" data-id="' + d.id_semestre + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" title="Activer ce semestre"><i data-lucide="power" style="width:14px;height:14px;"></i> Activer</button>';
        }

        var editBtn = isPlanifie ?
          '<button type="button" class="btn btn-sm btn-secondary btn-edit-semestre" data-id="' + d.id_semestre + '" data-libelle="' + (d.libelle_semestre || '') + '" data-annee="' + (d.annee_code || '') + '" data-debut="' + (d.date_debut_semestre || '') + '" data-fin="' + (d.date_fin_semestre || '') + '" data-statut="' + (d.statut_semestre || 'planifie') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' :
          '<button class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; opacity:0.5; cursor:not-allowed;" disabled title="Seul un semestre en préparation (planifié) peut être édité"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';

        return statusBtn + editBtn +
               '<a href="' + window.RACINE + 'semestre/details/' + (d.editId || d.id_semestre) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });


  // Bascule de statut d'un semestre via Ajax (Activer / Clôturer)
  $(document).on('click', '.btn-activate-semestre', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    $.ajax({
      url: '<?= RACINE ?>semestre/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Statut mis à jour avec succès', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Statut mis à jour avec succès');
          }
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors du changement de statut', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors du changement de statut');
          }
        }
      },
      error: function() {
        if (typeof showToast === 'function') {
          showToast('Erreur réseau', 'error');
        } else if (window.toastr) {
          toastr.error('Erreur réseau');
        }
      }
    });
  });

  // GESTION MODALE AJOUT / ÉDITION SEMESTRE
  $(document).on('click', '.btn-add-semestre', function(e) {
    e.preventDefault();
    $('#form-semestre')[0].reset();
    $('#semestre_id').val('');
    $('#semestre_statut').val('planifie');
    $('#semestre_annee').val('<?= htmlspecialchars($selectedAnneeCode) ?>');
    $('#modal-semestre-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Semestre');
    $('#modal-semestre').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#semestre_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-semestre', function(e) {
    e.preventDefault();
    $('#form-semestre')[0].reset();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var annee = $(this).data('annee');
    var debut = $(this).data('debut');
    var fin = $(this).data('fin');
    var statut = $(this).data('statut') || 'planifie';

    $('#semestre_id').val(id);
    $('#semestre_libelle').val(libelle);
    $('#semestre_annee').val(annee);
    $('#semestre_date_debut').val(debut !== '-' ? debut : '');
    $('#semestre_date_fin').val(fin !== '-' ? fin : '');
    $('#semestre_statut').val(statut);

    $('#modal-semestre-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Semestre');
    $('#modal-semestre').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#semestre_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-semestre').on('click', function() {
    $('#modal-semestre').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-semestre')) {
      $('#modal-semestre').hide();
    }
  });

  $('#form-semestre').on('submit', function(e) {
    e.preventDefault();
    var id = $('#semestre_id').val();
    var url = window.RACINE + (id ? 'semestre/edit' : 'semestre/add');
    var $btn = $('#btn-submit-semestre');
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
          if (typeof showToast === 'function') showToast(res.message || 'Semestre enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Semestre enregistré avec succès');
          $('#modal-semestre').hide();
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
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UN SEMESTRE                       -->
<!-- ========================================================================= -->
<div id="modal-semestre" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-semestre-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Semestre
      </h3>
      <button type="button" class="btn-close-modal-semestre" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-semestre" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_semestre" id="semestre_id" value="">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Désignation du Semestre <span style="color: #EF4444;">*</span>
        </label>
        <select name="libelle_semestre" id="semestre_libelle" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
          <option value="">-- Choisir un semestre --</option>
          <option value="Semestre 1">Semestre 1 (S1)</option>
          <option value="Semestre 2">Semestre 2 (S2)</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Année Académique <span style="color: #EF4444;">*</span>
        </label>
        <select name="annee_code" id="semestre_annee" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 14px;">
          <option value="">-- Sélectionner l'année académique --</option>
          <?php foreach ($annees as $an): ?>
            <option value="<?= htmlspecialchars($an['code_annee']) ?>" <?= ($selectedAnneeCode === $an['code_annee']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($an['libelle_annee']) ?> <?= (!empty($an['est_active'])) ? ' (Active)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de Début <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="date_debut_semestre" id="semestre_date_debut" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de Fin <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="date_fin_semestre" id="semestre_date_fin" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 22px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Statut du Semestre
        </label>
        <select name="statut_semestre" id="semestre_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px;">
          <option value="planifie">🔵 En préparation (Semestre Futur)</option>
          <option value="cloture">⚪ Clôturé (Semestre Passé / Historique)</option>
        </select>
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">L'activation du semestre s'effectue via le bouton « Activer » du tableau.</small>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-semestre" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-semestre" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
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
