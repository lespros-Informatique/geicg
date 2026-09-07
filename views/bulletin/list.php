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
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Bulletins & PV de Notes</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Génération, consultation et impression des relevés de notes et Procès-Verbaux de délibération par classe</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <a href="<?= RACINE ?>bulletin/pvClasse" id="btn-pv-classe" class="btn btn-outline-primary" style="border-color: #1E3A5F; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="file-spreadsheet" style="width: 18px; height: 18px;"></i> Procès-Verbal (PV) de Classe
          </a>
        </div>
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
          <table id="table-bulletin" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Matricule</th>
                <th style="padding: 12px;">Nom & Prénom Étudiant</th>
                <th style="padding: 12px;">Classe</th>
                <th style="padding: 12px;">Année Académique</th>
                <th style="padding: 12px;">Notes Saisies</th>
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

  // Mettre à jour l'URL du bouton PV de classe lors de la sélection d'une classe
  $('#filter-classe').on('change', function() {
    var classeCode = $(this).val();
    if (classeCode) {
      $('#btn-pv-classe').attr('href', '<?= RACINE ?>bulletin/pvClasse?classe_code=' + encodeURIComponent(classeCode));
    } else {
      $('#btn-pv-classe').attr('href', '<?= RACINE ?>bulletin/pvClasse');
    }
    table.ajax.reload();
  });

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

  $('#filter-annee').on('change', function() {
    table.ajax.reload();
  });

  var table = $('#table-bulletin').DataTable({
    ajax: {
      url: '<?= RACINE ?>bulletin/apiList',
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
      { data: 'matricule_etudiant', defaultContent: '<span style="color:#94A3B8; font-style:italic;">-</span>', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#1E3A5F;">' + (d || '-') + '</strong>';
      }},
      { data: 'etudiant_nom',    defaultContent: '-', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:600; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'classe_nom',      defaultContent: '-', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || '<span style=\"color:#94A3B8; font-style:italic;\">Non assigné</span>') + '</span>';
      }},
      { data: 'annee_nom',       defaultContent: '-', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#64748B;">' + (d || '-') + '</span>';
      }},
      { data: 'nb_notes',        defaultContent: '0', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || 0;
        var count = parseInt(d) || 0;
        if (count > 0) {
          return '<span style="display:inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #DCFCE7; color: #15803D;">' + count + ' note(s)</span>';
        } else {
          return '<span style="display:inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #F1F5F9; color: #64748B;">0 note</span>';
        }
      }},
      { data: null, width: '170px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'bulletin/details/' + (d.editId || d.id_inscription) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F;border-color:#1E3A5F;color:#fff;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;"><i data-lucide="file-text" style="width:14px;height:14px;"></i> Voir Bulletin</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
