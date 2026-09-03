<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layers" style="width: 24px; height: 24px; color: #1E3A5F;"></i> <?= !empty($item['id_piece_cycle']) ? 'Modification Pièce de Cycle' : 'Configuration du Dossier de Pièces par Cycle' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            <?= !empty($item['id_piece_cycle']) ? 'Mise à jour des critères d\'exigence de la pièce pour ce cycle' : 'Sélectionnez les pièces administratives à exiger aux étudiants pour ce cycle académique' ?>
          </p>
        </div>
        <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
        </a>
      </div>

      <?php if (!empty($item['id_piece_cycle'])): ?>
        <!-- FORMULAIRE MODIFICATION UNITAIRE -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          <form action="<?= RACINE ?>piece_fournir_cycle/edit" method="POST" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
            <input type="hidden" name="id_piece_cycle" value="<?= $item['id_piece_cycle'] ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px; width: 100%;">
              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Cycle académique *</label>
                <select name="cycle_code" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <?php foreach ($cycles as $cy): ?>
                    <option value="<?= $cy['code_cycle'] ?>" <?= ($item['cycle_code'] ?? '') === $cy['code_cycle'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cy['libelle_cycle']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Document / Pièce administrative *</label>
                <select name="piece_code" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <?php foreach ($pieces as $p): ?>
                    <option value="<?= $p['code_piece_fournir'] ?>" <?= ($item['piece_code'] ?? '') === $p['code_piece_fournir'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($p['libelle_piece']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nombre d'exemplaires demandés *</label>
                <input type="number" min="1" step="1" name="nombre_exemplaires" value="<?= (int)($item['nombre_exemplaires'] ?? 1) ?>" required class="form-control" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nature du document *</label>
                <select name="nature_document" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                  <option value="photocopie_simple" <?= ($item['nature_document'] ?? '') === 'photocopie_simple' ? 'selected' : '' ?>>Photocopie Simple</option>
                  <option value="photocopie_legalisee" <?= ($item['nature_document'] ?? '') === 'photocopie_legalisee' ? 'selected' : '' ?>>Photocopie Légalisée / Certifiée conforme</option>
                  <option value="original" <?= ($item['nature_document'] ?? '') === 'original' ? 'selected' : '' ?>>Original Requis</option>
                  <option value="numerique" <?= ($item['nature_document'] ?? '') === 'numerique' ? 'selected' : '' ?>>Fichier Numérique (Scan PDF)</option>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Caractère de l'exigence *</label>
                <select name="est_obligatoire" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                  <option value="obligatoire" <?= ($item['est_obligatoire'] ?? '') === 'obligatoire' ? 'selected' : '' ?>>Obligatoire (Bloquant)</option>
                  <option value="complementaire" <?= ($item['est_obligatoire'] ?? '') === 'complementaire' ? 'selected' : '' ?>>Complémentaire (Sous réserve)</option>
                  <option value="facultatif" <?= ($item['est_obligatoire'] ?? '') === 'facultatif' ? 'selected' : '' ?>>Facultatif</option>
                </select>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
                <select name="statut_piece_cycle" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="actif" <?= ($item['statut_piece_cycle'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                  <option value="inactif" <?= ($item['statut_piece_cycle'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                </select>
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
              <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</a>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer les modifications</button>
            </div>
          </form>
        </div>

      <?php else: ?>
        <!-- FORMULAIRE AJOUT RAPIDE / PANIER DE PIÈCES À FOURNIR PAR CYCLE -->
        <form action="<?= RACINE ?>piece_fournir_cycle/add" method="POST" id="form-bulk-piece-cycle" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <!-- Choix du Cycle & Année -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #CBD5E1; box-shadow: 0 2px 8px rgba(15,23,42,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Cycle Académique de Destination
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%;">
              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Cycle ciblé *</label>
                <select name="cycle_code" id="select_target_cycle" required class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="">-- Sélectionner un cycle --</option>
                  <?php foreach ($cycles as $cy): ?>
                    <option value="<?= $cy['code_cycle'] ?>">
                      <?= htmlspecialchars($cy['libelle_cycle']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Ces pièces constitueront le dossier administratif exigé pour tous les étudiants inscrits dans ce cycle.</small>
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Année Académique</label>
                <select name="annee_code" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                  <?php foreach ($annees as $an): ?>
                    <option value="<?= $an['code_annee'] ?? $an['id_annee'] ?>">
                      <?= htmlspecialchars($an['libelle_annee'] ?? $an['nom_annee']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Panier / Tableau Dynamique des Pièces de Cycle -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 2px 8px rgba(15,23,42,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid #EFF6FF;">
              <div>
                <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="folder-plus" style="width: 18px; height: 18px;"></i> Composition du Dossier Administratif
                </h3>
                <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">Sélectionnez les pièces à joindre obligatoirement au dossier d'inscription.</p>
              </div>
              <button type="button" id="btn-add-row-cycle" class="btn" style="background: #EFF6FF; color: #1E3A5F; border: 1.5px solid #BFDBFE; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> + Ajouter une pièce
              </button>
            </div>

            <!-- Table des lignes -->
            <div style="width: 100%; overflow-x: auto;">
              <table class="table" id="table-bulk-cycle-items" style="width: 100%; border-collapse: collapse; margin: 0;">
                <thead>
                  <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <th style="padding: 10px 12px; width: 42%;">Pièce / Document à Fournir *</th>
                    <th style="padding: 10px 12px; width: 12%; text-align: center;">Exemplaires</th>
                    <th style="padding: 10px 12px; width: 22%;">Nature du Document</th>
                    <th style="padding: 10px 12px; width: 18%; text-align: center;">Caractère</th>
                    <th style="padding: 10px 12px; width: 6%; text-align: center;"></th>
                  </tr>
                </thead>
                <tbody id="bulk-rows-cycle-container">
                  <!-- Lignes ajoutées dynamiquement -->
                </tbody>
              </table>
            </div>

            <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
              <a href="<?= RACINE ?>piece_fournir/formulaire" target="_blank" style="font-size: 12.5px; font-weight: 700; color: #3B82F6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Créer une nouvelle pièce dans le répertoire &rarr;
              </a>
            </div>
          </div>

          <!-- Bottom Submit Bar -->
          <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px;">
            <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 12px 24px;">Annuler</a>
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; font-size: 14px; border-radius: 8px; padding: 12px 32px; box-shadow: 0 4px 12px rgba(30,58,95,0.25); display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Enregistrer le Dossier du Cycle
            </button>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </main>
</div>

<script>
var pieceOptions = <?= json_encode(array_map(function($p) {
  return [
    'code' => $p['code_piece_fournir'],
    'libelle' => $p['libelle_piece']
  ];
}, $pieces ?? [])) ?>;

var alreadyAssignedCodes = [];

$(document).ready(function() {
  var rowIndex = 0;

  function loadAssignedPiecesForCycle(cycleCode) {
    if (!cycleCode) {
      alreadyAssignedCodes = [];
      refreshAllDropdowns();
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>piece_fournir_cycle/getByCycleApi',
      type: 'GET',
      data: { cycle_code: cycleCode },
      dataType: 'json',
      success: function(res) {
        alreadyAssignedCodes = res.assignedCodes || [];
        refreshAllDropdowns();
        if (alreadyAssignedCodes.length > 0) {
          if (window.toastr) toastr.info(alreadyAssignedCodes.length + ' pièce(s) sont déjà enregistrées pour ce cycle.');
        }
      }
    });
  }

  function getCurrentlySelectedPieces() {
    var selected = [];
    $('.sel-piece').each(function() {
      var val = $(this).val();
      if (val) selected.push(val);
    });
    return selected;
  }

  function refreshAllDropdowns() {
    $('.bulk-row-cycle').each(function() {
      var select = $(this).find('.sel-piece');
      var currentVal = select.val();

      select.find('option').each(function() {
        var optVal = $(this).val();
        if (!optVal) return;

        var isAlreadyInDb = alreadyAssignedCodes.indexOf(optVal) !== -1;
        var baseLibelle = '';
        for (var i = 0; i < pieceOptions.length; i++) {
          if (pieceOptions[i].code === optVal) {
            baseLibelle = pieceOptions[i].libelle;
            break;
          }
        }

        if (isAlreadyInDb) {
          $(this).prop('disabled', true);
          $(this).text(baseLibelle + ' (Déjà configuré dans ce cycle)');
        } else {
          $(this).prop('disabled', false);
          $(this).text(baseLibelle);
        }
      });

      if (alreadyAssignedCodes.indexOf(currentVal) !== -1) {
        select.val('');
      }
    });
  }

  function createRow(selectedCode, nbEx, nature, exigence) {
    selectedCode = selectedCode || '';
    nbEx = nbEx || 1;
    nature = nature || 'photocopie_simple';
    exigence = exigence || 'obligatoire';

    var optHtml = '<option value="">-- Choisir une pièce à fournir --</option>';
    pieceOptions.forEach(function(item) {
      var isAlreadyInDb = alreadyAssignedCodes.indexOf(item.code) !== -1;
      var isSel = (item.code === selectedCode && !isAlreadyInDb) ? 'selected' : '';
      var disabled = isAlreadyInDb ? 'disabled' : '';
      var suffix = isAlreadyInDb ? ' (Déjà configuré dans ce cycle)' : '';
      optHtml += '<option value="' + item.code + '" ' + isSel + ' ' + disabled + '>' + $('<div>').text(item.libelle + suffix).html() + '</option>';
    });

    var html = '<tr class="bulk-row-cycle" data-idx="' + rowIndex + '" style="border-bottom: 1px solid #F1F5F9;">' +
      '<td style="padding: 10px 12px;">' +
        '<select name="items[' + rowIndex + '][piece_code]" required class="form-select sel-piece" style="border-radius:6px; font-weight:700; font-size:13px; padding:8px 12px; width:100%; box-sizing:border-box;">' +
          optHtml +
        '</select>' +
      '</td>' +
      '<td style="padding: 10px 12px; text-align:center;">' +
        '<input type="number" min="1" step="1" name="items[' + rowIndex + '][nombre_exemplaires]" value="' + nbEx + '" required class="form-control" style="border-radius:6px; font-weight:700; text-align:center; font-size:13px; padding:8px; width:100%; box-sizing:border-box;">' +
      '</td>' +
      '<td style="padding: 10px 12px;">' +
        '<select name="items[' + rowIndex + '][nature_document]" class="form-select" style="border-radius:6px; font-weight:600; font-size:12.5px; padding:8px 10px; width:100%; box-sizing:border-box;">' +
          '<option value="photocopie_simple" ' + (nature === 'photocopie_simple' ? 'selected' : '') + '>Photocopie Simple</option>' +
          '<option value="photocopie_legalisee" ' + (nature === 'photocopie_legalisee' ? 'selected' : '') + '>Photocopie Légalisée</option>' +
          '<option value="original" ' + (nature === 'original' ? 'selected' : '') + '>Original Requis</option>' +
          '<option value="numerique" ' + (nature === 'numerique' ? 'selected' : '') + '>Fichier Numérique (Scan)</option>' +
        '</select>' +
      '</td>' +
      '<td style="padding: 10px 12px; text-align:center;">' +
        '<select name="items[' + rowIndex + '][est_obligatoire]" class="form-select" style="border-radius:6px; font-weight:700; font-size:12px; padding:6px 10px; width:100%; box-sizing:border-box;">' +
          '<option value="obligatoire" ' + (exigence === 'obligatoire' ? 'selected' : '') + '>Obligatoire</option>' +
          '<option value="complementaire" ' + (exigence === 'complementaire' ? 'selected' : '') + '>Complémentaire</option>' +
          '<option value="facultatif" ' + (exigence === 'facultatif' ? 'selected' : '') + '>Facultatif</option>' +
        '</select>' +
      '</td>' +
      '<td style="padding: 10px 12px; text-align:center;">' +
        '<button type="button" class="btn btn-sm btn-delete-row-cycle" style="background:#FEE2E2; color:#B91C1C; border:none; border-radius:6px; width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;" title="Supprimer la ligne">' +
          '✕' +
        '</button>' +
      '</td>' +
    '</tr>';

    $('#bulk-rows-cycle-container').append(html);
    rowIndex++;
  }

  // Prepopulate initial empty lines
  if ($('#bulk-rows-cycle-container').length && $('#bulk-rows-cycle-container tr').length === 0) {
    createRow('', 1, 'photocopie_simple', 'obligatoire');
    createRow('', 1, 'original', 'obligatoire');
  }

  $('#select_target_cycle').on('change', function() {
    var cycle = $(this).val();
    loadAssignedPiecesForCycle(cycle);
  });

  // Check on piece select change
  $(document).on('change', '.sel-piece', function() {
    var changedVal = $(this).val();
    if (!changedVal) return;

    if (alreadyAssignedCodes.indexOf(changedVal) !== -1) {
      alert('Cette pièce est déjà configurée pour ce cycle académique.');
      $(this).val('');
      return;
    }

    var count = 0;
    var thisSelect = $(this);
    $('.sel-piece').each(function() {
      if ($(this).val() === changedVal) count++;
    });

    if (count > 1) {
      alert('Vous avez déjà sélectionné cette pièce dans une autre ligne de ce formulaire.');
      thisSelect.val('');
    }
  });

  $('#btn-add-row-cycle').on('click', function() {
    createRow('', 1, 'photocopie_simple', 'obligatoire');
  });

  $(document).on('click', '.btn-delete-row-cycle', function() {
    if ($('#bulk-rows-cycle-container tr').length > 1) {
      $(this).closest('tr').remove();
    } else {
      if (window.toastr) toastr.info('Vous devez conserver au moins un document dans le dossier.');
    }
  });

  $('#form-bulk-piece-cycle').on('submit', function(e) {
    var cycle = $('#select_target_cycle').val();
    if (!cycle) {
      e.preventDefault();
      alert('Veuillez sélectionner le cycle académique ciblé.');
      $('#select_target_cycle').focus();
      return false;
    }

    var selectedPieces = [];
    var hasDuplicate = false;
    var hasValid = false;

    $('.sel-piece').each(function() {
      var val = $.trim($(this).val());
      if (val !== '') {
        hasValid = true;
        if (alreadyAssignedCodes.indexOf(val) !== -1) {
          hasDuplicate = true;
        }
        if (selectedPieces.indexOf(val) !== -1) {
          hasDuplicate = true;
        }
        selectedPieces.push(val);
      }
    });

    if (!hasValid) {
      e.preventDefault();
      alert('Veuillez sélectionner au moins une pièce à fournir.');
      return false;
    }

    if (hasDuplicate) {
      e.preventDefault();
      alert('Certaines pièces sélectionnées sont des doublons ou existent déjà pour ce cycle.');
      return false;
    }
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
