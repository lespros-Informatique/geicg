<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$etudiants = (new ModelEtudiant())->getAll();
$accessoires = (new ModelAccessoire())->getAll();
$stats = $stats ?? (new ModelAccessoire())->getStats();
$annees = $annees ?? [];
$classes = $classes ?? [];
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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="package-check" style="width: 26px; height: 26px; color: #15803D;"></i> Registre & Remise des Kits & Accessoires
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Suivi des distributions, émargement des retraits de fournitures et gestion des kits par étudiant</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btn-open-attrib-modal" class="btn" style="background: #15803D; border: 1px solid #15803D; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="package-plus" style="width: 18px; height: 18px;"></i> Attribuer un Kit
          </button>
          <a href="<?= RACINE ?>accessoire/list" class="btn btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="settings" style="width: 18px; height: 18px;"></i> Catalogue Kits
          </a>
        </div>
      </div>

      <!-- Filtres (Année & Classe) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; flex: 1;">
            
            <!-- Filter Année -->
            <div style="min-width: 220px;">
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Année Académique</label>
              <select id="filter-annee" class="form-select select2" style="width: 100%;">
                <option value="">-- Toutes les années --</option>
                <?php foreach ($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filter Classe -->
            <div style="min-width: 220px;">
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Classe / Promotion</label>
              <select id="filter-classe" class="form-select select2" style="width: 100%;">
                <option value="">-- Toutes les classes --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_classe']) ?>">
                    <?= htmlspecialchars($c['nom_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

          </div>

          <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" id="btn-refresh" class="btn btn-light" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 15px; border: 1px solid #CBD5E1;">
              <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Actualiser
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Cards -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="package" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total Kits Attribués</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-kits"><?= $stats['total'] ?? 0 ?></div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FED7AA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #EA580C; text-transform: uppercase;">En Attente de Retrait</div>
            <div style="font-size: 22px; font-weight: 900; color: #C2410C; margin-top: 2px;" id="kpi-en-attente"><?= $stats['en_attente'] ?? 0 ?></div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #16A34A; text-transform: uppercase;">Kits Remis / Retirés</div>
            <div style="font-size: 22px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-retires"><?= $stats['retires'] ?? 0 ?></div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F8FAFC; color: #0284C7; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Taux de Remise</div>
            <div style="font-size: 22px; font-weight: 900; color: #0284C7; margin-top: 2px;" id="kpi-taux"><?= $stats['taux_retrait'] ?? 0 ?>%</div>
          </div>
        </div>

      </div>

      <!-- Tableau du Registre d'Émargement des Kits -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <table id="table-distributions" class="table table-hover align-middle w-100" style="width: 100%;">
          <thead>
            <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
              <th>Étudiant & Classe</th>
              <th>Kit / Accessoire</th>
              <th>Date Attribution</th>
              <th>Statut Retrait</th>
              <th>Date Retrait</th>
              <th style="text-align: right;">Action Émargement</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- MODAL : ATTRIBUER UN KIT -->
<div class="modal fade" id="modal-attribuer-kit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
      <div class="modal-header" style="background: #15803D; color: #FFFFFF; border-top-left-radius: 16px; border-top-right-radius: 16px; padding: 18px 24px;">
        <h5 class="modal-title fw-bold text-white mb-0" style="display: flex; align-items: center; gap: 8px;">
          <i data-lucide="package-plus" style="width: 22px; height: 22px;"></i> Attribuer un Kit / Accessoire
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <form id="form-attribuer-kit">
        <div class="modal-body" style="padding: 24px;">
          
          <!-- Sélection Étudiant -->
          <div class="mb-3">
            <label class="form-label font-weight-bold" style="font-size: 13px; font-weight: 700;">Sélectionner l'Étudiant <span class="text-danger">*</span></label>
            <select name="etudiant_code" id="modal-select-etudiant" class="form-select select2" style="width: 100%;" required>
              <option value="">-- Rechercher un étudiant --</option>
              <?php foreach ($etudiants as $e): ?>
                <option value="<?= htmlspecialchars($e['code_etudiant']) ?>">
                  <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?> (<?= htmlspecialchars($e['matricule_etudiant'] ?? 'Sans matricule') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Sélection Kit / Accessoire -->
          <div class="mb-3">
            <label class="form-label font-weight-bold" style="font-size: 13px; font-weight: 700;">Kit(s) / Accessoire(s) <span class="text-danger">*</span></label>
            <select name="accessoires[]" id="modal-select-accessoires" class="form-select select2" multiple style="width: 100%;" required>
              <?php foreach ($accessoires as $acc): ?>
                <option value="<?= htmlspecialchars($acc['code_accessoire']) ?>">
                  <?= htmlspecialchars($acc['libelle_accessoire']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Statut de Retrait Initial -->
          <div class="mb-3">
            <label class="form-label font-weight-bold" style="font-size: 13px; font-weight: 700;">Statut de Retrait Initial</label>
            <select name="etat_retrait" class="form-select" style="border-radius: 8px; font-weight: 700;">
              <option value="en_attente">⏳ En attente de retrait</option>
              <option value="retire">✅ Remis / Retiré immédiatement</option>
            </select>
          </div>

        </div>

        <div class="modal-footer" style="background: #F8FAFC; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; padding: 16px 24px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-weight: 700; border-radius: 8px;">Annuler</button>
          <button type="submit" class="btn btn-success" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; background: #15803D; border-color: #15803D;">Enregistrer l'Attribution</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  var table;

  function initTable() {
    table = $('#table-distributions').DataTable({
      ajax: {
        url: '<?= RACINE ?>accessoire/apiDistributions',
        type: 'GET',
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
        },
        dataSrc: 'data'
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var photo = d.photo_etudiant ? '<?= RACINE ?>' + d.photo_etudiant : '<?= RACINE ?>public/images/avatar.png';
            return '<div style="display: flex; align-items: center; gap: 12px;">' +
                   '<img src="' + photo + '" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0;" onerror="this.src=\'<?= RACINE ?>public/images/avatar.png\'">' +
                   '<div>' +
                     '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">' + escapeHtml(d.nom_etudiant + ' ' + d.prenom_etudiant) + '</div>' +
                     '<div style="font-size: 11.5px; color: #64748B;">Matricule : ' + escapeHtml(d.matricule_etudiant || '-') + ' | Classe : <strong>' + escapeHtml(d.nom_classe || '-') + '</strong></div>' +
                   '</div>' +
                   '</div>';
          }
        },
        {
          data: 'libelle_accessoire',
          render: function(d) {
            return '<span class="badge bg-light text-dark border" style="font-size: 12.5px; font-weight: 800; padding: 6px 12px; color: #1E3A5F !important;">' +
                     '<i data-lucide="package" style="width: 14px; height: 14px; margin-right: 4px;"></i> ' + escapeHtml(d || '-') +
                   '</span>';
          }
        },
        {
          data: 'date_attribution_formatee',
          render: function(d) {
            return '<span style="font-size: 12px; color: #64748B;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: 'etat_retrait',
          render: function(d) {
            if (d === 'retire') {
              return '<span class="badge bg-success" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">✅ Remis / Retiré</span>';
            }
            return '<span class="badge bg-warning text-dark" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">⏳ En attente de retrait</span>';
          }
        },
        {
          data: 'date_retrait_formatee',
          render: function(d) {
            return '<span style="font-size: 12px; font-weight: 700; color: #475569;">' + (d || '-') + '</span>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function(d) {
            var isRetire = (d.etat_retrait === 'retire');
            var btnText = isRetire ? 'Annuler Remise' : 'Marquer comme Remis';
            var btnClass = isRetire ? 'btn-outline-secondary' : 'btn-success';
            var icon = isRetire ? 'rotate-ccw' : 'check';

            return '<button type="button" class="btn btn-sm ' + btnClass + ' btn-toggle-retrait" data-id="' + d.id_accessoire_inscription + '" style="font-weight: 700; border-radius: 8px; padding: 6px 12px;">' +
                     '<i data-lucide="' + icon + '" style="width: 14px; height: 14px; margin-right: 4px;"></i> ' + btnText +
                   '</button>';
          }
        }
      ],
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      },
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
      }
    });
  }

  initTable();

  $('#filter-annee, #filter-classe').on('change', function() {
    table.ajax.reload();
    refreshKpis();
  });

  $('#btn-refresh').on('click', function() {
    table.ajax.reload();
    refreshKpis();
  });

  function refreshKpis() {
    var annee = $('#filter-annee').val();
    $.ajax({
      url: '<?= RACINE ?>accessoire/apiStats',
      type: 'GET',
      data: { annee_code: annee },
      dataType: 'json',
      success: function(s) {
        if (s) {
          $('#kpi-total-kits').text(s.total || 0);
          $('#kpi-en-attente').text(s.en_attente || 0);
          $('#kpi-retires').text(s.retires || 0);
          $('#kpi-taux').text((s.taux_retrait || 0) + '%');
        }
      }
    });
  }

  // Émargement instantané (Bascule l'état de retrait)
  $('#table-distributions').on('click', '.btn-toggle-retrait', function() {
    var id = $(this).data('id');
    var $btn = $(this);
    $btn.prop('disabled', true);

    $.ajax({
      url: '<?= RACINE ?>accessoire/toggleRetrait',
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        table.ajax.reload(null, false);
        refreshKpis();
      },
      error: function() {
        $btn.prop('disabled', false);
        alert('Erreur lors de la mise à jour du statut.');
      }
    });
  });

  // Modal Attribution
  $('#btn-open-attrib-modal').on('click', function() {
    $('#modal-attribuer-kit').modal('show');
  });

  $('#form-attribuer-kit').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
      url: '<?= RACINE ?>accessoire/attribuerKit',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          $('#modal-attribuer-kit').modal('hide');
          table.ajax.reload();
          refreshKpis();
        } else {
          alert(res.message || 'Erreur lors de l\'attribution.');
        }
      },
      error: function() {
        alert('Erreur lors de l\'enregistrement.');
      }
    });
  });

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
