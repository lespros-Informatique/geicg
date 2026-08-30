<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$filieres = $filieres ?? [];
$classes = $classes ?? [];
$anneeActive = $anneeActive ?? '';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="users" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Registre des Étudiants
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation et gestion du registre des étudiants avec colonnes et filtres intelligents</p>
        </div>
        <a href="<?= RACINE ?>etudiant/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Inscription / Dossier
        </a>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES & INTELLIGENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
          <span style="font-size: 13px; font-weight: 700; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="filter" style="width: 15px; height: 15px;"></i> Filtres de Recherche Dynamique
          </span>
          <button type="button" id="btn-reset-filters" style="background: none; border: none; color: #64748B; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px;" onmouseover="this.style.color='#EF4444'; this.style.background='#FEF2F2';" onmouseout="this.style.color='#64748B'; this.style.background='none';">
            <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i> Réinitialiser les filtres
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: flex-end;">
          
          <!-- Filtre Année Académique -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Année Académique</label>
            <select id="filter-annee" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($a['code_annee'] === $anneeActive) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['code_annee'] === $anneeActive) ? '(En cours)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Filière -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Filière</label>
            <select id="filter-filiere" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les filières --</option>
              <?php foreach ($filieres as $f): ?>
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
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Classe -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Classe Spécifique</label>
            <select id="filter-classe" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-filiere="<?= htmlspecialchars($c['filiere_code'] ?? '') ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>" data-annee="<?= htmlspecialchars($c['annee_code'] ?? '') ?>">
                  <?= htmlspecialchars($c['libelle_classe']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Statut -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">Statut Compte</label>
            <select id="filter-statut" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Tous les statuts --</option>
              <option value="actif">Actifs uniquement</option>
              <option value="inactif">Inactifs uniquement</option>
            </select>
          </div>

        </div>
      </div>

      <!-- TABLEAU DU REGISTRE DES ÉTUDIANTS (Colonnes intelligentes) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-etudiants" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 45px;">#</th>
                <th style="padding: 12px;">Matricule</th>
                <th style="padding: 12px;">Étudiant (Nom & Prénoms)</th>
                <th style="padding: 12px;">Sexe</th>
                <th style="padding: 12px;">Téléphone</th>
                <th style="padding: 12px;">Classe</th>
                <th style="padding: 12px;">Filière</th>
                <th style="padding: 12px;">Niveau</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
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
  
  // Instance DataTable avec colonnes intelligentes
  var table = $('#table-etudiants').DataTable({
    ajax: {
      url: '<?= RACINE ?>etudiant/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.statut_etudiant = $('#filter-statut').val();
      }
    },
    processing: true,
    autoWidth: false,
    pageLength: 25,
    columns: [
      // 0. # Incrémenté
      { data: null, width: '45px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},

      // 1. Matricule
      { data: 'matricule_etudiant', width: '110px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + d + '</code>';
      }},

      // 2. Nom & Prénoms avec avatar
      { data: null, render: function(d, type, row) {
        var nom = (row.nom_etudiant || '').toUpperCase();
        var prenom = row.prenom_etudiant || '';
        var initiales = (nom.charAt(0) + (prenom.charAt(0) || '')).toUpperCase();
        
        return '<div style="display:flex; align-items:center; gap:10px;">' +
               '<div style="width:32px; height:32px; border-radius:50%; background:#1E3A5F; color:#FFF; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">' + initiales + '</div>' +
               '<div>' +
               '<div style="font-weight:700; color:#0F172A; font-size:13px;">' + nom + ' ' + prenom + '</div>' +
               '<small style="color:#64748B; font-size:11px;">Réf: ' + (row.code_etudiant || '-') + '</small>' +
               '</div>' +
               '</div>';
      }},

      // 3. Sexe
      { data: 'sexe_etudiant', width: '80px', render: function(d) {
        if (!d) return '-';
        var isFem = (d.toLowerCase().indexOf('f') !== -1);
        var bg = isFem ? '#FDF2F8' : '#EFF6FF';
        var col = isFem ? '#DB2777' : '#2563EB';
        var border = isFem ? '#FBCFE8' : '#BFDBFE';
        return '<span style="background:' + bg + '; color:' + col + '; border:1px solid ' + border + '; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700;">' + d + '</span>';
      }},

      // 4. Téléphone
      { data: 'telephone_etudiant', defaultContent: '-', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">-</span>';
        return '<span style="font-weight:600; color:#334155; font-size:12px;">' + d + '</span>';
      }},

      // 5. Classe (Indépendante)
      { data: 'libelle_classe', defaultContent: '-', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">Non assigné</span>';
        return '<strong style="color:#0F172A; font-size:12px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="graduation-cap" style="width:14px;height:14px;color:#D97706;"></i> ' + d + '</strong>';
      }},

      // 6. Filière (Indépendante)
      { data: 'libelle_filiere', defaultContent: '-', render: function(d, type, row) {
        var fil = d || row.code_filiere || '-';
        return '<span style="font-size:12px; font-weight:600; color:#1E293B;">' + fil + '</span>';
      }},

      // 7. Niveau (Indépendant)
      { data: 'libelle_niveau', defaultContent: '-', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">-</span>';
        return '<span style="background:#F1F5F9; color:#475569; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:700;">' + d + '</span>';
      }},

      // 8. Statut Toggle
      { data: 'statut_etudiant', width: '70px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:36px; height:18px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-etudiant" data-id="' + row.id_etudiant + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#166534' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:12px; width:12px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},

      // 9. Actions
      { data: null, width: '160px', orderable: false, render: function(d) {
        var idCrypte = d.editId || d.id_etudiant;
        return '<a href="' + window.RACINE + 'etudiant/edition/' + idCrypte + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:13px;height:13px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'etudiant/details/' + idCrypte + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:13px;height:13px;"></i> Dossier</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Gestion intelligente de la visibilité des colonnes
  function updateSmartColumns() {
    var selFil = $('#filter-filiere').val();
    var selNiv = $('#filter-niveau').val();
    var selCls = $('#filter-classe').val();

    // Colonne 5 : Classe -> masquée si une classe spécifique est sélectionnée
    table.column(5).visible(selCls === 'ALL' || !selCls);

    // Colonne 6 : Filière -> masquée si une filière spécifique est sélectionnée
    table.column(6).visible(selFil === 'ALL' || !selFil);

    // Colonne 7 : Niveau -> masquée si un niveau spécifique est sélectionné
    table.column(7).visible(selNiv === 'ALL' || !selNiv);
  }

  // Déclenchement automatique du rechargement et des colonnes intelligentes
  $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe, #filter-statut').on('change', function() {
    filterClassDropdown();
    updateSmartColumns();
    table.ajax.reload();
  });

  // Filtrage en cascade du menu des classes selon la filière et le niveau choisis
  function filterClassDropdown() {
    var selFil = $('#filter-filiere').val();
    var selNiv = $('#filter-niveau').val();
    var selAnn = $('#filter-annee').val();

    $('#filter-classe option').each(function() {
      var optVal = $(this).val();
      if (optVal === 'ALL') return;

      var optFil = $(this).data('filiere');
      var optNiv = $(this).data('niveau');
      var optAnn = $(this).data('annee');

      var matchFil = (selFil === 'ALL' || !selFil || optFil === selFil);
      var matchNiv = (selNiv === 'ALL' || !selNiv || optNiv === selNiv);
      var matchAnn = (selAnn === 'ALL' || !selAnn || optAnn === selAnn);

      if (matchFil && matchNiv && matchAnn) {
        $(this).show();
      } else {
        $(this).hide();
        if ($('#filter-classe').val() === optVal) {
          $('#filter-classe').val('ALL');
        }
      }
    });
  }

  // Réinitialisation des filtres
  $('#btn-reset-filters').on('click', function() {
    $('#filter-annee').val('<?= $anneeActive ?>');
    $('#filter-filiere').val('ALL');
    $('#filter-niveau').val('ALL');
    $('#filter-classe').val('ALL');
    $('#filter-statut').val('ALL');
    filterClassDropdown();
    updateSmartColumns();
    table.ajax.reload();
  });

  // Bascule instantanée du statut étudiant
  $(document).on('change', '.toggle-statut-etudiant', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>etudiant/changer',
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

  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
