<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
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
            <i data-lucide="folder-check" style="width: 26px; height: 26px; color: #1E3A5F;"></i> Dépôt des Dossiers Étudiants
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion, réception et contrôle de la complétude des pièces à fournir par les étudiants inscrits</p>
        </div>
      </div>

      <!-- Barre de Filtres (Année & Classe) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          
          <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; flex: 1;">
            <!-- Année Académique -->
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

            <!-- Classe / Promotion -->
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

      <!-- Cards KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total Étudiants</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-etudiants">0</div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #DCFCE7; display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #16A34A; text-transform: uppercase;">Dossiers Complets</div>
            <div style="font-size: 22px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-dossiers-complets">0</div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #FED7AA; display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="alert-circle" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #EA580C; text-transform: uppercase;">Dossiers Incomplets</div>
            <div style="font-size: 22px; font-weight: 900; color: #C2410C; margin-top: 2px;" id="kpi-dossiers-incomplets">0</div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #F8FAFC; color: #0284C7; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="pie-chart" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Taux de Dépôt Global</div>
            <div style="font-size: 22px; font-weight: 900; color: #0284C7; margin-top: 2px;" id="kpi-taux-global">0%</div>
          </div>
        </div>
      </div>

      <!-- Tableau des Étudiants et Dépôt des Dossiers -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <table id="table-dossiers" class="table table-hover align-middle w-100" style="width: 100%;">
          <thead>
            <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
              <th>Étudiant</th>
              <th>Matricule</th>
              <th>Classe</th>
              <th>Pièces Déposées</th>
              <th>Complétude</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- MODAL : ÉMARGEMENT DU DOSSIER ÉTUDIANT -->
<div class="modal fade" id="modal-dossier" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
      <div class="modal-header" style="background: #1E3A5F; color: #FFFFFF; border-top-left-radius: 16px; border-top-right-radius: 16px; padding: 18px 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="folder-check" style="width: 22px; height: 22px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-white mb-0" id="modal-student-name">Dossier de l'Étudiant</h5>
            <div id="modal-student-info" style="font-size: 12px; color: #94A3B8;">Classe / Matricule</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <div class="modal-body" style="padding: 24px; background: #F8FAFC;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 14px 18px; background: #FFFFFF; border-radius: 10px; border: 1px solid #E2E8F0;">
          <div>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Statut Global</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A;" id="modal-completion-text">0 / 0 Pièces Dépôts</div>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" id="btn-tout-valider-modal" class="btn btn-sm btn-success" style="font-weight: 700; border-radius: 8px; padding: 8px 14px;">
              <i data-lucide="check-check" style="width: 16px; height: 16px; margin-right: 4px;"></i> Tout Valider
            </button>
          </div>
        </div>

        <div id="modal-pieces-list" style="display: flex; flex-direction: column; gap: 12px;">
          <!-- Liste dynamique des pièces requises -->
        </div>

      </div>

      <div class="modal-footer" style="background: #FFFFFF; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; padding: 16px 24px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-weight: 700; border-radius: 8px;">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  var table;
  var currentStudentData = null;

  function initTable() {
    table = $('#table-dossiers').DataTable({
      ajax: {
        url: '<?= RACINE ?>dossier_etudiant/apiList',
        type: 'GET',
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
        },
        dataSrc: function(json) {
          var items = json.data || [];
          var totalCount = items.length;
          var completsCount = 0;
          var incompletsCount = 0;
          var totalPiecesGlobal = 0;
          var totalDeposeesGlobal = 0;

          items.forEach(function(i) {
            if (i.est_complet) completsCount++;
            else incompletsCount++;
            totalPiecesGlobal += i.total_pieces;
            totalDeposeesGlobal += i.deposees_count;
          });

          var tauxGlobal = (totalPiecesGlobal > 0) ? Math.round((totalDeposeesGlobal / totalPiecesGlobal) * 100) : 0;

          $('#kpi-total-etudiants').text(totalCount);
          $('#kpi-dossiers-complets').text(completsCount);
          $('#kpi-dossiers-incomplets').text(incompletsCount);
          $('#kpi-taux-global').text(tauxGlobal + '%');

          return items;
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var photo = d.photo ? '<?= RACINE ?>' + d.photo : '<?= RACINE ?>public/images/avatar.png';
            return '<div style="display: flex; align-items: center; gap: 12px;">' +
                   '<img src="' + photo + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0;" onerror="this.src=\'<?= RACINE ?>public/images/avatar.png\'">' +
                   '<div>' +
                     '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">' + escapeHtml(d.nom_complet) + '</div>' +
                   '</div>' +
                   '</div>';
          }
        },
        {
          data: 'matricule',
          render: function(d) {
            return '<span class="badge bg-light text-dark font-monospace" style="font-size: 12px; font-weight: 700; padding: 6px 10px; border: 1px solid #CBD5E1;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: 'classe',
          render: function(d) {
            return '<span style="font-weight: 700; color: #1E3A5F;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var badgeClass = d.est_complet ? 'bg-success' : (d.deposees_count > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.deposees_count + ' / ' + d.total_pieces + ' pièce(s)' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 120px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
                       '<div style="height: 100%; width: ' + pct + '%; background: ' + barColor + '; border-radius: 4px;"></div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function(d) {
            return '<div style="display: flex; justify-content: flex-end; gap: 8px;">' +
                     '<button type="button" class="btn btn-sm btn-primary btn-open-dossier" style="font-weight: 700; border-radius: 8px; padding: 6px 12px;">' +
                       '<i data-lucide="folder-open" style="width: 14px; height: 14px; margin-right: 4px;"></i> Gérer' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate" title="Tout valider en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="check-check" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                   '</div>';
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
  });

  $('#btn-refresh').on('click', function() {
    table.ajax.reload();
  });

  // Action Gérer le dossier (Modal)
  $('#table-dossiers').on('click', '.btn-open-dossier', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openDossierModal(rowData);
  });

  // Action Tout valider rapide
  $('#table-dossiers').on('click', '.btn-quick-validate', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllStudentPieces(rowData.code_inscription, rowData.code_etudiant);
  });

  $('#btn-tout-valider-modal').on('click', function() {
    if (currentStudentData) {
      validateAllStudentPieces(currentStudentData.code_inscription, currentStudentData.code_etudiant);
    }
  });

  function openDossierModal(d) {
    $('#modal-student-name').text(d.nom_complet);
    $('#modal-student-info').text('Matricule : ' + (d.matricule || '-') + ' | Classe : ' + (d.classe || '-'));
    $('#modal-completion-text').text(d.deposees_count + ' / ' + d.total_pieces + ' Pièce(s) Déposée(s) (' + d.pourcentage + '%)');

    var html = '';
    if (d.dossier_items && d.dossier_items.length > 0) {
      d.dossier_items.forEach(function(item) {
        var st = item.statut_depot || 'en_attente';
        html += '<div class="piece-card" style="background: #FFFFFF; padding: 16px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">' +
                  '<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">' +
                    '<div>' +
                      '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_piece) + '</div>' +
                      '<div style="font-size: 12px; color: #64748B; margin-top: 2px;">' + escapeHtml(item.description_piece || 'Pièce requise pour le dossier') + '</div>' +
                    '</div>' +
                    '<div>' +
                      '<select class="form-select form-select-sm select-statut-piece" data-inscription="' + d.code_inscription + '" data-etudiant="' + d.code_etudiant + '" data-piece="' + item.piece_code + '" style="font-weight: 700; border-radius: 8px; min-width: 140px;">' +
                        '<option value="depose" ' + (st === 'depose' ? 'selected' : '') + '>✅ Déposé</option>' +
                        '<option value="en_attente" ' + (st === 'en_attente' ? 'selected' : '') + '>⏳ En attente</option>' +
                        '<option value="rejete" ' + (st === 'rejete' ? 'selected' : '') + '>❌ Rejeté</option>' +
                        '<option value="non_requis" ' + (st === 'non_requis' ? 'selected' : '') + '>⚪ Non requis</option>' +
                      '</select>' +
                    '</div>' +
                  '</div>' +
                  '<div style="display: flex; gap: 10px; align-items: center;">' +
                    '<input type="text" class="form-control form-control-sm input-obs-piece" data-inscription="' + d.code_inscription + '" data-etudiant="' + d.code_etudiant + '" data-piece="' + item.piece_code + '" placeholder="Remarques / Observations..." value="' + escapeHtml(item.observations || '') + '" style="border-radius: 6px; font-size: 12px;">' +
                    '<button type="button" class="btn btn-sm btn-primary btn-save-piece" data-inscription="' + d.code_inscription + '" data-etudiant="' + d.code_etudiant + '" data-piece="' + item.piece_code + '" style="font-weight: 700; border-radius: 6px; white-space: nowrap;">Enregistrer</button>' +
                  '</div>' +
                '</div>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucune pièce exigée configurée pour le cycle de cette classe.</div>';
    }

    $('#modal-pieces-list').html(html);
    $('#modal-dossier').modal('show');
    if (window.lucide) lucide.createIcons();
  }

  // Action enregistrer modification statut unitaire
  $(document).on('click', '.btn-save-piece', function() {
    var $btn = $(this);
    var insCode = $btn.data('inscription');
    var etuCode = $btn.data('etudiant');
    var pieceCode = $btn.data('piece');
    var $card = $btn.closest('.piece-card');
    var statut = $card.find('.select-statut-piece').val();
    var obs = $card.find('.input-obs-piece').val();

    $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spin" style="width: 14px; height: 14px;"></i>');

    $.ajax({
      url: '<?= RACINE ?>dossier_etudiant/saveStatut',
      type: 'POST',
      data: {
        inscription_code: insCode,
        etudiant_code: etuCode,
        piece_code: pieceCode,
        statut: statut,
        observations: obs
      },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).text('Enregistré !');
        setTimeout(function() { $btn.text('Enregistrer'); }, 1500);
        table.ajax.reload(null, false);
      },
      error: function() {
        $btn.prop('disabled', false).text('Enregistrer');
        alert('Erreur lors de la mise à jour.');
      }
    });
  });

  function validateAllStudentPieces(insCode, etuCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les pièces de ce dossier comme DÉPOSÉES ?")) return;

    $.ajax({
      url: '<?= RACINE ?>dossier_etudiant/saveAll',
      type: 'POST',
      data: {
        inscription_code: insCode,
        etudiant_code: etuCode
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          table.ajax.reload(null, false);
          $('#modal-dossier').modal('hide');
        } else {
          alert(res.message || 'Erreur lors de la validation.');
        }
      },
      error: function() {
        alert('Erreur de connexion au serveur.');
      }
    });
  }

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
