<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$stats = $stats ?? (new ModelDepense())->getStats();
$annees = $annees ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Dépenses & Charges de Fonctionnement</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et suivi des décaissements, engagements budgétaires et frais généraux</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>type_depense/list" class="btn btn-secondary" style="background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="tags" style="width: 18px; height: 18px;"></i> Catégories de Dépenses
          </a>
          <a href="<?= RACINE ?>depense/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Décaissement / Dépense
          </a>
        </div>
      </div>

      <!-- Barre de Filtrage Année Académique (Select2) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
              <span style="font-size: 13px; font-weight: 700; color: #0F172A; display: block;">Année Académique</span>
              <span style="font-size: 11.5px; color: #64748B;">Filtrer le registre des dépenses par année</span>
            </div>
          </div>
          <div style="min-width: 260px; flex-grow: 0;">
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['statut_annee']) && $a['statut_annee'] === 'actif') ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- CARTES KPI DE STATISTIQUES FINANCIÈRES DES DÉPENSES -->
      <!-- ========================================================================= -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Montant Engagé -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="trending-down" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total Dépenses Engagées</div>
            <div style="font-size: 20px; font-weight: 900; color: #991B1B; margin-top: 2px;" id="kpi-total-montant"><?= number_format($stats['total_montant'], 0, ',', ' ') ?> FCFA</div>
          </div>
        </div>

        <!-- Nombre de Dépenses -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Dépenses Enregistrées</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-count"><?= $stats['total_count'] ?></div>
          </div>
        </div>

        <!-- Dépense Moyenne -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF7ED; color: #C2410C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calculator" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Dépense Moyenne</div>
            <div style="font-size: 20px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-moyenne"><?= number_format($stats['moyenne'], 0, ',', ' ') ?> FCFA</div>
          </div>
        </div>

        <!-- Catégories de Dépenses -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="tags" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Catégories Actives</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-types"><?= $stats['total_types'] ?></div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- TABLEAU DES DÉPENSES DE FONCTIONNEMENT -->
      <!-- ========================================================================= -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Registre des Dépenses & Décaissements</h3>
            <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Liste détaillée de tous les engagements financiers enregistrés</p>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-depenses" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code Dépense</th>
                <th style="padding: 12px;">Catégorie</th>
                <th style="padding: 12px;">Motif / Description</th>
                <th style="padding: 12px; text-align: right;">Montant Engagé (FCFA)</th>
                <th style="padding: 12px;">Date Engagement</th>
                <th style="padding: 12px;">Enregistré Par</th>
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
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
  }

  function reloadStats() {
    var anneeCode = $('#filter-annee').val();
    $.getJSON('<?= RACINE ?>depense/apiStats?annee_code=' + encodeURIComponent(anneeCode), function(res) {
      if (res.status === 1 && res.stats) {
        $('#kpi-total-montant').text(Number(res.stats.total_montant).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-total-count').text(res.stats.total_count);
        $('#kpi-moyenne').text(Number(res.stats.moyenne).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-total-types').text(res.stats.total_types);
      }
    });
  }

  var table = $('#table-depenses').DataTable({
    ajax: {
      url: '<?= RACINE ?>depense/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_depense', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + d + '</code>';
      }},
      { data: 'libelle_type_depense', render: function(d) {
        return '<span class="badge" style="background:#F3E8FF; color:#7E22CE; font-weight:700; padding:5px 10px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="tag" style="width:12px;height:12px;"></i> ' + (d || 'Général') + '</span>';
      }},
      { data: 'description_depense', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">Aucun motif spécifié</span>';
        var shortText = (d.length > 55) ? d.substring(0, 55) + '...' : d;
        return '<span style="color:#334155; font-weight:600; font-size:13px;" title="' + String(d).replace(/"/g, '&quot;') + '">' + shortText + '</span>';
      }},
      { data: 'montant_depense', className: 'text-end', render: function(d) {
        var num = parseFloat(d) || 0;
        return '<strong style="color:#991B1B; font-size:14px;">' + num.toLocaleString('fr-FR') + ' FCFA</strong>';
      }},
      { data: 'periode_depense', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          return '<span style="color:#475569; font-weight:600; font-size:12.5px;"><i data-lucide="calendar" style="width:13px;height:13px;display:inline;margin-right:4px;"></i>' + dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + '</span>';
        }
        return d;
      }},
      { data: 'auteur_nom_complet', render: function(d) {
        if (!d || d.trim() === '') return '<span style="color:#94A3B8;">-</span>';
        return '<span style="color:#0F172A; font-weight:700; font-size:12.5px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="user" style="width:13px;height:13px;color:#64748B;"></i> ' + d + '</span>';
      }},
      { data: 'statut_depense', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-depense" data-id="' + row.id_depense + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'depense/edition/' + (d.editId || d.id_depense) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'depense/details/' + (d.editId || d.id_depense) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.toggle-statut-depense', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>depense/changer',
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
          reloadStats();
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

  $('#filter-annee').on('change', function() {
    var val = $(this).val();
    window.location.href = '<?= RACINE ?>depense/list?annee_code=' + encodeURIComponent(val);
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
