<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="calendar-days" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Emplois du Temps
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Registre et suivi des plannings de cours de l'année académique active</p>
        </div>
        <a href="<?= RACINE ?>emploi/formulaire" id="btn-add-emploi" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Programmer un Emploi du Temps
        </a>
      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: center;">
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

      <!-- Datatable classes créées cette année -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-emplois_temps" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 50px;">#</th>
                <th style="padding: 12px 14px;">Classe & Niveau</th>
                <th style="padding: 12px 14px;">Volume & Créneaux</th>
                <th style="padding: 12px 14px;">Matières & Enseignants</th>
                <th style="padding: 12px 14px;">Statut Planning</th>
                <th style="padding: 12px 14px;">Dernière MàJ</th>
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

<!-- Modal Overlay Visualisation & Impression Emploi du Temps Classe -->
<div id="modalViewSchedule" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(3px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 1150px; max-height: 90vh; box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column; animation: slideDown 0.25s ease;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 18px 26px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
      <div>
        <h3 style="font-size: 18px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px;">
          <i data-lucide="calendar" style="width: 22px; height: 22px; color: #38BDF8;"></i>
          Emploi du Temps : <span id="modal-classe-title" style="color: #38BDF8;">-</span>
        </h3>
        <small id="modal-niveau-subtitle" style="color: #94A3B8; font-size: 12.5px; font-weight: 500; margin-top: 2px; display: block;">Niveau -</small>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <button type="button" class="btn-print-schedule" onclick="printSchedule()" style="background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%); color: #FFFFFF; border: none; font-weight: 700; border-radius: 8px; padding: 8px 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(2,132,199,0.35); transition: all 0.2s ease; cursor: pointer;">
          <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer l'Emploi du Temps
        </button>
        <button type="button" class="btn-close-modal" style="background: rgba(255,255,255,0.15); border: none; color: #FFFFFF; font-size: 20px; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;" title="Fermer">&times;</button>
      </div>
    </div>

    <!-- Body -->
    <div id="printable-schedule-area" style="padding: 24px; background: #F8FAFC; flex: 1; overflow-y: auto;">
      
      <!-- En-tête officiel visible uniquement à l'impression -->
      <div class="print-only-header" style="display: none; text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1E3A5F; padding-bottom: 14px;">
        <h2 style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin: 0; text-transform: uppercase;">GROUPE ÉCOLE D'INGÉNIEURS GEICG</h2>
        <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 6px 0 0 0;">EMPLOI DU TEMPS OFFICIEL : <span id="print-classe-name" style="color: #0284C7;">-</span></h3>
        <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">Registre Général des Cours • Année Académique Active</p>
      </div>

      <div id="schedule-matrix-loader" style="text-align: center; padding: 50px;">
        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"><span class="visually-hidden">Chargement...</span></div>
        <p style="margin-top: 12px; color: #64748B; font-weight: 700; font-size: 14px;">Chargement de la grille horaire...</p>
      </div>
      <div id="schedule-matrix-content" style="display: none;"></div>
    </div>

    <!-- Footer -->
    <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 14px 26px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
      <span style="font-size: 12px; color: #64748B; font-weight: 600;">Registre GEICG — Mode Grille Hebdomadaire</span>
      <button type="button" class="btn btn-secondary btn-close-modal" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; font-size: 13px;">Fermer</button>
    </div>
  </div>
</div>

<style>
.btn-print-schedule:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(2,132,199,0.45) !important;
  filter: brightness(1.05);
}
.btn-action-planning:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(30,58,95,0.3) !important;
}
.btn-action-grille:hover {
  background: #E2E8F0 !important;
  color: #0F172A !important;
}
.btn-action-reset:hover {
  background: #DC2626 !important;
  color: #FFFFFF !important;
  border-color: #DC2626 !important;
  box-shadow: 0 4px 10px rgba(220,38,38,0.3) !important;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}
@media print {
  body * { visibility: hidden !important; }
  #printable-schedule-area, #printable-schedule-area * { visibility: visible !important; }
  #printable-schedule-area { 
    position: absolute !important; 
    left: 0 !important; 
    top: 0 !important; 
    width: 100% !important; 
    padding: 20px !important; 
    background: #FFFFFF !important;
  }
  .print-only-header { display: block !important; }
  .modal-footer, .btn-close-modal, .btn-print-schedule { display: none !important; }
}
</style>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Cascading filter Niveau -> Classe
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
      $('#btn-add-emploi').attr('href', '<?= RACINE ?>emploi/formulaire?classe_code=' + encodeURIComponent(cls));
    } else {
      $('#btn-add-emploi').attr('href', '<?= RACINE ?>emploi/formulaire');
    }
    table.ajax.reload();
  });

  var table = $('#table-emplois_temps').DataTable({
    ajax: {
      url: '<?= RACINE ?>emploi/apiList',
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
        data: 'libelle_classe', 
        render: function(d, t, r) { 
          return '<div style="display:flex; flex-direction:column; gap:3px;">' +
                   '<strong style="font-size:14px; color:#0F172A;">' + (d || r.code_classe) + '</strong>' +
                   '<span style="display:inline-block; width:fit-content; background:#E2E8F0; color:#475569; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">' + (r.libelle_niveau || 'Niveau non défini') + '</span>' +
                 '</div>'; 
        } 
      },
      { 
        data: 'total_heures_formatted', 
        render: function(d, t, r) { 
          return '<div style="display:flex; flex-direction:column; gap:3px;">' +
                   '<span style="display:inline-flex; align-items:center; gap:5px; background:#EFF6FF; color:#1E40AF; font-size:13px; font-weight:800; padding:4px 10px; border-radius:6px; width:fit-content;">' +
                     '<i data-lucide="clock" style="width:14px; height:14px;"></i> ' + d +
                   '</span>' +
                   '<small style="color:#64748B; font-size:11px; font-weight:600;">' + r.nb_creneaux + ' créneau(x) • ' + r.nb_jours + ' jour(s)</small>' +
                 '</div>'; 
        } 
      },
      { 
        data: 'nb_matieres', 
        render: function(d, t, r) { 
          var matList = r.matieres_noms ? r.matieres_noms : 'Aucune matière';
          if (matList.length > 45) matList = matList.substring(0, 42) + '...';
          return '<div style="display:flex; flex-direction:column; gap:3px;">' +
                   '<span style="font-size:12px; font-weight:700; color:#334155;">' + d + ' matière(s) • ' + r.nb_profs + ' enseignant(s)</span>' +
                   '<span style="font-size:11px; color:#64748B; font-style:italic;" title="' + (r.matieres_noms || '') + '">' + matList + '</span>' +
                 '</div>'; 
        } 
      },
      { 
        data: 'statut_planning', 
        className: 'text-center', 
        render: function(d, t, r) {
          if (d === 'Complet') {
            return '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 12px; border-radius:20px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;">' +
                     '<i data-lucide="check-circle-2" style="width:13px; height:13px;"></i> Planning Complet' +
                   '</span>';
          } else {
            return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:6px 12px; border-radius:20px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;">' +
                     '<i data-lucide="clock" style="width:13px; height:13px;"></i> En Saisie (' + r.nb_jours + '/6 j)' +
                   '</span>';
          }
        }
      },
      { 
        data: 'last_update', 
        render: function(d) { return '<span style="font-size:12px; color:#64748B; font-weight:600;">' + d + '</span>'; } 
      },
      { 
        data: null, 
        orderable: false, 
        className: 'text-end',
        render: function(d) {
          var codeEsc = encodeURIComponent(d.code_classe);
          var libEsc = $('<div>').text(d.libelle_classe).html();
          return '<div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:nowrap;">' +
                   '<a href="<?= RACINE ?>emploi/formulaire?classe_code=' + codeEsc + '" class="btn btn-sm btn-action-planning" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; border: none; border-radius: 6px; font-weight: 700; padding: 6px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(30,58,95,0.2);" title="Gérer le planning">' +
                     '<i data-lucide="calendar-plus" style="width:14px; height:14px;"></i> Planning' +
                   '</a>' +
                   '<button type="button" class="btn btn-sm btn-action-grille btn-view-matrix" data-code="' + d.code_classe + '" data-libelle="' + libEsc + '" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; border-radius: 6px; font-weight: 700; padding: 6px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease;" title="Visualiser l\'emploi du temps">' +
                     '<i data-lucide="eye" style="width:14px; height:14px;"></i> Grille' +
                   '</button>' +
                   '<button type="button" class="btn btn-sm btn-action-reset btn-reset-classe" data-code="' + d.code_classe + '" data-libelle="' + libEsc + '" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; border-radius: 6px; font-weight: 700; padding: 6px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(220,38,38,0.08);" title="Vider le planning de cette classe">' +
                     '<i data-lucide="trash-2" style="width:14px; height:14px;"></i> Vider' +
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

  // Gestion de la fermeture de la modale Grille
  $(document).on('click', '.btn-close-modal', function() {
    $('#modalViewSchedule').hide();
  });
  $(document).on('click', '#modalViewSchedule', function(e) {
    if (e.target === this) {
      $('#modalViewSchedule').hide();
    }
  });

  // Action Visualiser Grille (Modal)
  $(document).on('click', '.btn-view-matrix', function() {
    var classeCode = $(this).data('code');
    var classeLibelle = $(this).data('libelle');
    
    $('#modal-classe-title').text(classeLibelle);
    $('#print-classe-name').text(classeLibelle);
    $('#modal-niveau-subtitle').text('Code Classe: ' + classeCode);
    $('#schedule-matrix-loader').show();
    $('#schedule-matrix-content').hide().empty();
    
    $('#modalViewSchedule').css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: '<?= RACINE ?>emploi/getClassScheduleMatrix',
      type: 'GET',
      data: { classe_code: classeCode },
      dataType: 'json',
      success: function(res) {
        $('#schedule-matrix-loader').hide();
        if (res.status === 1) {
          if (res.classe && res.classe.libelle_niveau) {
            $('#modal-niveau-subtitle').text(res.classe.libelle_niveau);
          }
          renderScheduleMatrix(res.slots);
          $('#schedule-matrix-content').show();
          if (window.lucide) lucide.createIcons();
        } else {
          $('#schedule-matrix-content').html('<div class="alert alert-danger">Erreur de chargement de l\'emploi du temps.</div>').show();
        }
      },
      error: function() {
        $('#schedule-matrix-loader').hide();
        $('#schedule-matrix-content').html('<div class="alert alert-danger">Erreur réseau lors de la récupération des données.</div>').show();
      }
    });
  });

  // Action Réinitialiser / Vider Planning Classe avec SweetAlert2
  $(document).on('click', '.btn-reset-classe', function() {
    var classeCode = $(this).data('code');
    var classeLibelle = $(this).data('libelle');

    if (window.Swal) {
      Swal.fire({
        title: 'ATTENTION !',
        html: 'Voulez-vous vraiment supprimer <strong>TOUS les créneaux horaires</strong> de la classe <span style="color:#DC2626; font-weight:800;">"' + classeLibelle + '"</span> ?<br><small style="color:#64748B;">Cette action est irréversible.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i data-lucide="trash-2" style="width:16px; height:16px; margin-right:4px;"></i> Oui, tout supprimer',
        cancelButtonText: 'Annuler',
        customClass: {
          confirmButton: 'btn btn-danger font-bold',
          cancelButton: 'btn btn-secondary font-bold'
        }
      }).then(function(result) {
        if (result.isConfirmed) {
          executeResetClasse(classeCode);
        }
      });
    } else if (confirm('ATTENTION : Voulez-vous vraiment supprimer TOUS les créneaux horaires de la classe "' + classeLibelle + '" ?')) {
      executeResetClasse(classeCode);
    }
  });

  function executeResetClasse(classeCode) {
    $.ajax({
      url: '<?= RACINE ?>emploi/resetClasseSchedule',
      type: 'POST',
      data: { classe_code: classeCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'L\'emploi du temps de la classe a été entièrement réinitialisé !');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de la réinitialisation');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
      }
    });
  }

  function renderScheduleMatrix(slots) {
    var days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    var dayLabels = {
      'lundi': 'Lundi',
      'mardi': 'Mardi',
      'mercredi': 'Mercredi',
      'jeudi': 'Jeudi',
      'vendredi': 'Vendredi',
      'samedi': 'Samedi'
    };

    var grouped = {};
    days.forEach(function(d) { grouped[d] = []; });

    slots.forEach(function(s) {
      var j = (s.jour || '').toLowerCase().trim();
      if (grouped[j]) {
        grouped[j].push(s);
      }
    });

    var html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">';

    days.forEach(function(d) {
      var list = grouped[d];
      html += '<div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">';
      html +=   '<div style="background: #1E3A5F; color: #FFFFFF; padding: 10px 14px; font-weight: 800; font-size: 14px; display: flex; justify-content: space-between; align-items: center;">';
      html +=     '<span>' + dayLabels[d] + '</span>';
      html +=     '<span class="badge" style="background: rgba(255,255,255,0.2); font-size: 11px;">' + list.length + ' cours</span>';
      html +=   '</div>';
      html +=   '<div style="padding: 12px; display: flex; flex-direction: column; gap: 10px; min-height: 120px;">';

      if (list.length === 0) {
        html += '<div style="text-align: center; color: #94A3B8; font-size: 12px; margin: auto; padding: 20px 0;">Aucun cours programmé</div>';
      } else {
        list.forEach(function(item) {
          var debut = (item.heure_debut || '').substring(0,5);
          var fin = (item.heure_fin || '').substring(0,5);
          var mat = item.libelle_matiere || item.matiere_code || 'Matière n/a';
          var prof = item.nom_prof ? item.nom_prof.trim() : (item.enseignant_code || 'Non spécifié');
          var salle = item.libelle_salle || item.salle_code || 'Salle n/a';

          html += '<div style="background: #F8FAFC; border-left: 4px solid #0284C7; border: 1px solid #E2E8F0; border-left-width: 4px; border-left-color: #0284C7; border-radius: 8px; padding: 10px 12px;">';
          html +=   '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
          html +=     '<span style="font-weight: 800; font-size: 13px; color: #0284C7;">' + debut + ' - ' + fin + '</span>';
          html +=     '<span class="badge" style="background: #E0F2FE; color: #0369A1; font-size: 10px; font-weight: 700;">' + salle + '</span>';
          html +=   '</div>';
          html +=   '<div style="font-weight: 700; font-size: 13px; color: #0F172A; margin-bottom: 3px;">' + mat + '</div>';
          html +=   '<div style="font-size: 11px; color: #64748B; display: flex; align-items: center; gap: 4px;">';
          html +=     '<i data-lucide="user" style="width: 12px; height: 12px;"></i> ' + prof;
          html +=   '</div>';
          html += '</div>';
        });
      }

      html +=   '</div>';
      html += '</div>';
    });

    html += '</div>';
    $('#schedule-matrix-content').html(html);
  }
});

function printSchedule() {
  window.print();
}
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
