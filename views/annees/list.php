<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Années Académiques</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Années Académiques</p>
        </div>
        <button type="button" class="btn btn-primary btn-add-annee" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Année Académique
        </button>
      </div>

      <!-- Navigation Tabs (Années Académiques vs Semestres & Périodes) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
        <a href="<?= RACINE ?>annee/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="calendar-range" style="width: 17px; height: 17px;"></i> Années Académiques
        </a>
        <a href="<?= RACINE ?>semestre/list" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="clock" style="width: 17px; height: 17px;"></i> Semestres & Périodes
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-annees" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
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
  var table = $('#table-annees').DataTable({
    ajax: '<?= RACINE ?>annee/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_annee', width: '120px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_annee', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { 
        data: 'date_debut_annee', 
        render: function(d) {
          if (!d) return '<span style="color:#94A3B8;">-</span>';
          var p = d.split('-');
          var formatted = p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
          return '<span style="font-weight:700; color:#1E293B; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="calendar" style="width:14px;height:14px;color:#1E3A5F;"></i> ' + formatted + '</span>';
        }
      },
      { 
        data: 'date_fin_annee', 
        render: function(d) {
          if (!d) return '<span style="color:#94A3B8;">-</span>';
          var p = d.split('-');
          var formatted = p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
          return '<span style="font-weight:700; color:#1E293B; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="calendar" style="width:14px;height:14px;color:#1E3A5F;"></i> ' + formatted + '</span>';
        }
      },
      { data: 'statut_annee', width: '130px', className: 'text-center', render: function(d, type, row) {
        if (d === 'actif') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Active en cours</span>';
        } else if (d === 'planifie') {
          return '<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En préparation</span>';
        } else {
          return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturée</span>';
        }
      }},
      { data: null, width: '220px', orderable: false, render: function(d) {
        var isActif = (d.statut_annee === 'actif');
        var isPlanifie = (d.statut_annee === 'planifie');

        var statusBtn = '';
        if (isActif) {
          statusBtn = '<button type="button" class="btn btn-sm btn-warning btn-activate-annee" data-id="' + d.id_annee + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer; background:#F59E0B; border-color:#F59E0B; color:#FFFFFF;" title="Clôturer cette année académique"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturer</button>';
        } else if (isPlanifie) {
          statusBtn = '<button type="button" class="btn btn-sm btn-success btn-activate-annee" data-id="' + d.id_annee + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" title="Activer cette année académique"><i data-lucide="power" style="width:14px;height:14px;"></i> Activer</button>';
        }

        var isPlanifie = (d.statut_annee === 'planifie');

        var editBtn = isPlanifie ?
          '<button type="button" class="btn btn-sm btn-secondary btn-edit-annee" data-id="' + d.id_annee + '" data-libelle="' + (d.libelle_annee ? $('<div>').text(d.libelle_annee).html() : '') + '" data-debut="' + (d.date_debut_annee || '') + '" data-fin="' + (d.date_fin_annee || '') + '" data-statut="' + (d.statut_annee || 'planifie') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' :
          '<button class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; opacity:0.5; cursor:not-allowed;" disabled title="Seule une année académique en préparation (planifiée) peut être éditée"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';

        return statusBtn + editBtn +
               '<a href="' + window.RACINE + 'annee/details/' + (d.editId || d.id_annee) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule d'activation d'année via Ajax
  $(document).on('click', '.btn-activate-annee', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    $.ajax({
      url: '<?= RACINE ?>annee/changer',
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
            showToast(res.message || 'Année activée avec succès', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Année activée avec succès');
          }
          if (res.activeYear && res.activeYear.libelle_annee) {
            $('#activeAnneeDisplay').text(res.activeYear.libelle_annee);
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
          showToast('Erreur de communication réseau', 'error');
        } else if (window.toastr) {
          toastr.error('Erreur réseau');
        }
      }
    });
  });

  // GESTION MODALE AJOUT / ÉDITION ANNÉE ACADÉMIQUE
  $(document).on('click', '.btn-add-annee', function(e) {
    e.preventDefault();
    $('#form-annee')[0].reset();
    $('#annee_id').val('');
    $('#annee_statut').val('planifie');
    $('#modal-annee-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Année Académique');
    $('#modal-annee').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#annee_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-annee', function(e) {
    e.preventDefault();
    $('#form-annee')[0].reset();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var debut = $(this).data('debut');
    var fin = $(this).data('fin');
    var statut = $(this).data('statut') || 'planifie';

    $('#annee_id').val(id);
    $('#annee_libelle').val(libelle);
    $('#annee_date_debut').val(debut);
    $('#annee_date_fin').val(fin);
    $('#annee_statut').val(statut);

    $('#modal-annee-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Année Académique');
    $('#modal-annee').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#annee_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-annee').on('click', function() {
    $('#modal-annee').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-annee')) {
      $('#modal-annee').hide();
    }
  });

  $('#form-annee').on('submit', function(e) {
    e.preventDefault();
    var id = $('#annee_id').val();
    var url = window.RACINE + (id ? 'annee/edit' : 'annee/add');
    var $btn = $('#btn-submit-annee');
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
          if (typeof showToast === 'function') {
            showToast(res.message || 'Année académique enregistrée avec succès', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Année académique enregistrée avec succès');
          }
          $('#modal-annee').hide();
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          }
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
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });
});
</script>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UNE ANNÉE ACADÉMIQUE             -->
<!-- ========================================================================= -->
<div id="modal-annee" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-annee-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Année Académique
      </h3>
      <button type="button" class="btn-close-modal-annee" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-annee" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_annee" id="annee_id" value="">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Année Académique <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_annee" id="annee_libelle" required placeholder="Ex: 2026-2027" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de Début <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="date_debut_annee" id="annee_date_debut" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de Fin <span style="color: #EF4444;">*</span>
          </label>
      </div>

      <div class="form-group" style="margin-bottom: 22px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Statut de l'Année
        </label>
        <select name="statut_annee" id="annee_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px;">
          <option value="planifie">🔵 En préparation (Année Future / Pré-inscriptions)</option>
          <option value="cloture">⚪ Clôturée (Année Passée / Historique)</option>
        </select>
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">L'activation officielle de l'année active principale s'effectue via le bouton « Activer » du tableau.</small>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-annee" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-annee" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
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
