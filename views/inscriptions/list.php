<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- PAGE HEADER -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Réinscriptions</h1>
            <span class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 8px;">
              Session <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'Aucune') ?>
            </span>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Liste des étudiants à réinscrire pour l'année académique active</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>inscription/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Réinscription
          </a>
        </div>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
          <span style="font-size: 13px; font-weight: 700; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="filter" style="width: 15px; height: 15px;"></i> Filtres de recherche
          </span>
          <button type="button" id="btn-reset-filters" style="background: none; border: none; color: #64748B; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px;" onmouseover="this.style.color='#EF4444'; this.style.background='#FEF2F2';" onmouseout="this.style.color='#64748B'; this.style.background='none';">
            <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i> Réinitialiser les filtres
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; align-items: flex-end;">
          
          <!-- Filtre Filière -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Filière</label>
            <select id="filter-filiere" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les filières --</option>
              <?php foreach (($filieres ?? []) as $f): ?>
                <option value="<?= htmlspecialchars($f['code_filiere']) ?>">
                  <?= htmlspecialchars($f['libelle_filiere']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Niveau -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Tous les niveaux --</option>
              <?php foreach (($niveaux ?? []) as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Classe -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Classe Antérieure (N-1)</label>
            <select id="filter-classe" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les classes --</option>
              <?php foreach (($classes ?? []) as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-filiere="<?= htmlspecialchars($c['filiere_code'] ?? '') ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>">
                  <?= htmlspecialchars($c['libelle_classe']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>

      <!-- TABLEAU DES ÉTUDIANTS À RÉINSCRIRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-inscriptions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 14px 12px; width: 45px;">#</th>
                <th style="padding: 14px 12px;">Étudiant & Matricule</th>
                <th style="padding: 14px 12px;">Filière & Niveau Antérieur</th>
                <th style="padding: 14px 12px;">Classe Antérieure (N-1)</th>
                <th style="padding: 14px 12px; text-align: right;">Action</th>
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
  var table = $('#table-inscriptions').DataTable({
    order: [],
    ajax: {
      url: '<?= RACINE ?>inscription/apiList',
      data: function(d) {
        d.filiere_code = $('#filter-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'nom_complet', render: function(d, type, row) {
        var mat = row.matricule_etudiant || '-';
        var tel = row.telephone && row.telephone !== '-' ? '<span style="color:#64748B; font-size:11.5px; margin-left:6px;"><i data-lucide="phone" style="width:11px;height:11px;display:inline-block;vertical-align:middle;"></i> ' + row.telephone + '</span>' : '';
        
        return '<div>' +
               '  <div style="font-weight:800; color:#0F172A; font-size:13.5px;">' + d + '</div>' +
               '  <div style="font-size:11.5px; color:#475569; margin-top:1px;"><code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; padding:1px 4px; border-radius:4px;">' + mat + '</code>' + tel + '</div>' +
               '</div>';
      } },
      { data: 'filiere_precedente', render: function(d, type, row) {
        var fil = d || 'Non définie';
        var niv = row.niveau_precedent ? ' • ' + row.niveau_precedent : '';
        return '<div>' +
               '  <div style="font-weight:700; color:#1E3A5F; font-size:13px;">' + fil + '</div>' +
               '  <div style="font-size:11.5px; color:#64748B;">' + (row.niveau_precedent || 'Niveau non défini') + '</div>' +
               '</div>';
      } },
      { data: 'classe_precedente', render: function(d, type, row) {
        if (!d) {
          return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Nouveau / Sans historique</span>';
        }
        var annee = row.annee_precedente ? ' <span style="font-size:11px; color:#64748B;">(' + row.annee_precedente + ')</span>' : '';
        return '<div style="font-weight:700; color:#334155; font-size:13px;">' + d + annee + '</div>';
      } },
      { data: null, orderable: false, width: '130px', className: 'text-end', render: function(d, type, row) {
        return '<a href="<?= RACINE ?>inscription/formulaire?etudiant_code=' + encodeURIComponent(row.code_etudiant) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; font-weight:700; border-radius:6px; padding:6px 14px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 3px rgba(30,58,95,0.2);">' +
               '  <i data-lucide="user-check" style="width:14px;height:14px;"></i> Réinscrire' +
               '</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
  });

  // Rechargement sur changement des filtres
  $('#filter-filiere, #filter-niveau, #filter-classe').on('change', function() {
    var filSelected = $('#filter-filiere').val();
    var nivSelected = $('#filter-niveau').val();

    // Filtre interactif de la liste déroulante des classes
    $('#filter-classe option').each(function() {
      var val = $(this).val();
      if (val === 'ALL') return;
      var cFil = $(this).attr('data-filiere');
      var cNiv = $(this).attr('data-niveau');

      var matchFil = (filSelected === 'ALL' || cFil === filSelected);
      var matchNiv = (nivSelected === 'ALL' || cNiv === nivSelected);

      if (matchFil && matchNiv) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });

    table.ajax.reload();
  });

  // Bouton Réinitialiser
  $('#btn-reset-filters').on('click', function() {
    $('#filter-filiere').val('ALL');
    $('#filter-niveau').val('ALL');
    $('#filter-classe').val('ALL');
    $('#filter-classe option').show();
    table.ajax.reload();
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
