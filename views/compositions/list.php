<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$matieres = $matieres ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="award" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Programmation Compositions & Examens
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Planning et calendrier des épreuves écrites, compositions semestrielles et examens officiels</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <a href="<?= RACINE ?>composition/formulaire" id="btn-add-composition" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Programmer une Composition / Examen
          </a>
        </div>
      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i> Année Académique
            </label>
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
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="layers" style="width: 14px; height: 14px; color: #64748B;"></i> Niveau d'Études
            </label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="graduation-cap" style="width: 14px; height: 14px; color: #64748B;"></i> Classe
            </label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- DataTables Compositions Programmées -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-compositions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 50px;">#</th>
                <th style="padding: 12px 14px;">Intitulé</th>
                <th style="padding: 12px 14px;">Semestre</th>
                <th style="padding: 12px 14px; text-align: center;">Coef</th>
                <th style="padding: 12px 14px;">Date</th>
                <th style="padding: 12px 14px; text-align: center; width: 90px;">Statut</th>
                <th style="padding: 12px 14px; text-align: right;">Actions</th>
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

  // Filtrage Niveau -> Classe
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
    var cls = $('#filter-classe').val();
    if (cls) {
      $('#btn-add-composition').attr('href', '<?= RACINE ?>composition/formulaire?classe_code=' + encodeURIComponent(cls));
    } else {
      $('#btn-add-composition').attr('href', '<?= RACINE ?>composition/formulaire');
    }
    table.ajax.reload();
  });

  var table = $('#table-compositions').DataTable({
    ajax: {
      url: '<?= RACINE ?>composition/apiList',
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
      { 
        data: null, 
        width: '50px', 
        render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }
      },
      { 
        data: 'libelle_composition', 
        render: function(d, t, r) { 
          return '<strong style="font-size:14px; color:#0F172A;">' + (d || r.code_composition || '-') + '</strong>'; 
        } 
      },
      { 
        data: 'libelle_semestre', 
        render: function(d, t, r) { 
          return '<span style="font-size:13px; color:#0284C7; font-weight:700;">' + (d || r.semestre_code || '-') + '</span>'; 
        } 
      },
      { 
        data: 'coefficient', 
        className: 'text-center',
        render: function(d) { 
          var coef = d ? parseFloat(d).toFixed(2) : '1.00';
          return '<span class="badge" style="background:#F1F5F9; color:#334155; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px;">' + coef + '</span>'; 
        } 
      },
      { 
        data: 'date_composition', 
        render: function(d, type, row) { 
          var dateStr = d || row.created_at_composition;
          if (!dateStr) return '<span style="color:#94A3B8;">-</span>';
          var cleanDate = dateStr.split(' ')[0];
          var parts = cleanDate.split('-');
          if (parts.length === 3) {
            return '<strong style="font-size:13px; color:#1E3A5F;">' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</strong>';
          }
          return '<strong style="font-size:13px; color:#1E3A5F;">' + cleanDate + '</strong>';
        } 
      },
      { 
        data: 'statut_composition', 
        width: '90px', 
        className: 'text-center', 
        render: function(d, type, row) {
          var isActif = (d === 'programme' || d === 'actif');
          var checkedAttr = isActif ? 'checked' : '';
          return '<div style="display:flex; justify-content:center; align-items:center;">' +
                 '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Programmé - Cliquez pour terminer' : 'Terminé - Cliquez pour programmer') + '">' +
                 '<input type="checkbox" class="toggle-statut-composition" data-id="' + row.id_composition + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                 '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                 '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                 '</span>' +
                 '</label>' +
                 '</div>';
        }
      },
      { 
        data: null, 
        orderable: false, 
        className: 'text-end',
        render: function(d) {
          var detailsUrl = '<?= RACINE ?>composition/details/' + d.editId;
          var editUrl = '<?= RACINE ?>composition/edition/' + d.editId;
          return '<div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:nowrap; align-items:center;">' +
                   '<a href="' + detailsUrl + '" class="btn btn-sm" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Voir les détails de l\'épreuve">' +
                     '<i data-lucide="eye" style="width:14px; height:14px;"></i> Détails' +
                   '</a>' +
                   '<a href="' + editUrl + '" class="btn btn-sm" style="background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Modifier">' +
                     '<i data-lucide="edit-3" style="width:14px; height:14px;"></i> Éditer' +
                   '</a>' +
                   '<button type="button" class="btn btn-sm btn-delete-comp" data-id="' + d.id_composition + '" data-libelle="' + $('<div>').text(d.libelle_composition).html() + '" style="background:#FEF2F2; color:#DC2626; border:1px solid #FCA5A5; border-radius:6px; font-weight:700; padding:6px 10px; font-size:12px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;" title="Supprimer">' +
                     '<i data-lucide="trash-2" style="width:14px; height:14px;"></i>' +
                   '</button>' +
                 '</div>';
        } 
      }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-composition', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>composition/changer',
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

  // Action Supprimer Composition
  $(document).on('click', '.btn-delete-comp', function() {
    var id = $(this).data('id');
    var lib = $(this).data('libelle');

    if (window.Swal) {
      Swal.fire({
        title: 'Supprimer cette épreuve ?',
        html: 'Voulez-vous vraiment retirer la composition <strong style="color:#DC2626;">"' + lib + '"</strong> du calendrier ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      }).then(function(res) {
        if (res.isConfirmed) executeDeleteComp(id);
      });
    } else if (confirm('Supprimer cette épreuve ?')) {
      executeDeleteComp(id);
    }
  });

  function executeDeleteComp(id) {
    $.ajax({
      url: '<?= RACINE ?>composition/delete',
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Composition supprimée avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur de suppression');
        }
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
