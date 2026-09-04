<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = (new ModelAnnee())->getAll();
$filieres = (new ModelFiliere())->getAll();
$niveaux = (new ModelNiveau())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_scolarite']) ? 'Éditer ' : 'Ajouter ' ?> Tarif de Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des grilles tarifaires par année, filière et niveau d'études</p>
        </div>
        <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>scolarite/<?= !empty($item['id_scolarite']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_scolarite'])): ?>
            <input type="hidden" name="id_scolarite" value="<?= $item['id_scolarite'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="calculator" style="width: 18px; height: 18px;"></i> Paramètres du Tarif de Scolarité
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Année Académique (Sélection Readonly / Verrouillée sur l'année active) -->
            <?php 
              $currentAnneeCode = $item['annee_code'] ?? ($_SESSION['annee_active_code'] ?? '');
              $currentAnneeLibelle = $_SESSION['annee_active_libelle'] ?? 'Session Active';
            ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Année Académique <span style="font-size: 11px; font-weight: 500; color: #64748B; margin-left: 6px;">(Session Active)</span>
              </label>
              <input type="hidden" name="annee_code" value="<?= htmlspecialchars($currentAnneeCode) ?>">
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; font-weight: 800; pointer-events: none; cursor: not-allowed;" tabindex="-1" readonly>
                <?php foreach($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" data-debut="<?= htmlspecialchars($a['date_debut_annee'] ?? '') ?>" data-fin="<?= htmlspecialchars($a['date_fin_annee'] ?? '') ?>" <?= ($currentAnneeCode == $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?> <?= (($a['statut_annee'] ?? '') === 'actif') ? '(Session Active)' : '' ?>
                  </option>
                <?php endforeach; ?>
                <?php if (empty($annees)): ?>
                  <option value="<?= htmlspecialchars($currentAnneeCode) ?>" selected><?= htmlspecialchars($currentAnneeLibelle) ?></option>
                <?php endif; ?>
              </select>
            </div>

            <!-- Filière (Select2) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Filière rattachée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_filiere_scolarite" name="filiere_code" style="width: 100%;" required>
                <option value="">-- Choisir une filière --</option>
                <?php foreach($filieres as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_filiere']) ?>" <?= (($item['filiere_code'] ?? '') == $f['code_filiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['libelle_filiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Niveau d'études (Select2) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau d'études <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_niveau_scolarite" name="niveau_code" style="width: 100%;" required>
                <option value="">-- Choisir un niveau --</option>
                <?php foreach($niveaux as $n): ?>
                  <option value="<?= htmlspecialchars($n['code_niveau']) ?>" <?= (($item['niveau_code'] ?? '') == $n['code_niveau']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n['libelle_niveau']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Régime / Statut d'affectation (Select statique) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut d'Affectation <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_affectation_scolarite" name="affectation_etat" style="width: 100%;" required>
                <option value="affecte" <?= (($item['affectation_etat'] ?? 'affecte') === 'affecte') ? 'selected' : '' ?>>Affecté (de l'État)</option>
                <option value="non_affecte" <?= (($item['affectation_etat'] ?? '') === 'non_affecte') ? 'selected' : '' ?>>Non Affecté (Privé)</option>
              </select>
            </div>

            <!-- Montant annuel -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Montant annuel (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" step="1000" min="0" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="montant_scolarite" value="<?= htmlspecialchars($item['montant_scolarite'] ?? '') ?>" placeholder="Ex: 650000" required>
            </div>

          </div>

          <!-- SECTION DYNAMIQUE : ÉCHÉANCIER / TRANCHES DE SCOLARITÉ (PANIER) -->
          <input type="hidden" name="deleted_tranches_ids" id="deleted_tranches_ids" value="">

          <div style="margin-top: 32px; padding-top: 24px; border-top: 2px dashed #E2E8F0;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div>
                <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="shopping-cart" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Échéancier des Tranches de Paiement
                </h3>
                <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">
                  Ajoutez et planifiez directement les tranches / versements pour cette scolarité (optionnel).
                </p>
              </div>
              
              <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" id="btn-add-tranche" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 8px 16px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #F8FAFC; cursor: pointer;">
                  <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter une tranche
                </button>
              </div>
            </div>

            <!-- Résumé de répartition / Badge indicateur de cumul -->
            <div id="tranches-summary-bar" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 18px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
              <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center; font-size: 13px;">
                <div>Montant Scolarité : <strong id="lbl-total-scolarite" style="color: #0F172A;">0 FCFA</strong></div>
                <div>Total Tranches : <strong id="lbl-cumul-tranches" style="color: #1E3A5F;">0 FCFA</strong></div>
                <div>Reste à répartir : <strong id="lbl-reste-scolarite" style="color: #64748B;">0 FCFA</strong></div>
              </div>
              <div id="badge-repartition-status" style="font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; background: #EFF6FF; color: #1E3A5F;">
                0 tranche(s)
              </div>
            </div>

            <!-- Table dynamique Panier Tranches -->
            <div style="width: 100%; overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 10px;">
              <table class="table" id="table-panier-tranches" style="width: 100%; margin: 0; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F1F5F9; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <th style="padding: 10px 12px; width: 45px; text-align: center;">#</th>
                    <th style="padding: 10px 12px; min-width: 200px;">Libellé de la tranche *</th>
                    <th style="padding: 10px 12px; width: 180px;">Montant (FCFA) *</th>
                    <th style="padding: 10px 12px; width: 170px;">Date Limite *</th>
                    <th style="padding: 10px 12px; width: 110px; text-align: center;">Statut</th>
                    <th style="padding: 10px 12px; width: 60px; text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody id="tbody-tranches">
                  <!-- Lignes injectées dynamiquement -->
                </tbody>
              </table>
              <div id="tranches-empty-state" style="padding: 24px; text-align: center; color: #94A3B8; font-size: 13px; font-style: italic;">
                <i data-lucide="layers" style="width: 28px; height: 28px; display: block; margin: 0 auto 6px auto; opacity: 0.6;"></i>
                Aucune tranche ajoutée pour le moment. Cliquez sur <strong>"+ Ajouter une tranche"</strong> pour définir l'échéancier.
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_annee_scolarite').select2({ placeholder: "-- Choisir une année --", allowClear: true, width: '100%' });
    $('#sel_filiere_scolarite').select2({ placeholder: "-- Choisir une filière --", allowClear: true, width: '100%' });
    $('#sel_niveau_scolarite').select2({ placeholder: "-- Choisir un niveau --", allowClear: true, width: '100%' });
    $('#sel_affectation_scolarite').select2({ minimumResultsForSearch: Infinity, width: '100%' });

    $('#sel_annee_scolarite').on('change select2:select', function() {
      updateTranchesSummary();
    });
  }

  var existingTranches = <?= json_encode($tranches ?? []) ?>;
  var trancheCounter = 0;
  var deletedTrancheIds = [];

  function formatFcfa(val) {
    return Number(val || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  function formatDateFr(isoDateStr) {
    if (!isoDateStr) return '';
    var parts = isoDateStr.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return isoDateStr;
  }

  function displayFormError(msg, $form) {
    if (window.toastr) {
      toastr.error(msg);
    } else if (typeof showToast === 'function') {
      showToast(msg, 'error');
    }
    
    $form.find('.js-date-error-banner').remove();
    var alertHtml = '<div class="alert alert-danger js-date-error-banner" style="background:#FEE2E2; border:1px solid #FCA5A5; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:10px;">' +
                    '<i data-lucide="alert-triangle" style="width:18px; height:18px; flex-shrink:0;"></i>' +
                    '<span>' + msg + '</span>' +
                    '</div>';
    $form.prepend(alertHtml);
    if (window.lucide) lucide.createIcons();
    $('html, body').animate({ scrollTop: $form.offset().top - 80 }, 200);
  }

  function updateTranchesSummary() {
    var montantScolarite = parseFloat($('input[name="montant_scolarite"]').val()) || 0;
    var totalTranches = 0;
    var rowCount = 0;

    $('#tbody-tranches tr').each(function(idx) {
      rowCount++;
      $(this).find('.tranche-index-label').text(rowCount);
      var montant = parseFloat($(this).find('.input-tranche-montant').val()) || 0;
      totalTranches += montant;
    });

    $('#lbl-total-scolarite').text(formatFcfa(montantScolarite));
    $('#lbl-cumul-tranches').text(formatFcfa(totalTranches));

    var diff = montantScolarite - totalTranches;
    var $lblReste = $('#lbl-reste-scolarite');
    var $badge = $('#badge-repartition-status');
    var $btnAdd = $('#btn-add-tranche');

    // Désactivation du bouton "Ajouter une tranche" si la scolarité n'est pas renseignée (0) ou est déjà intégralement couverte
    if (montantScolarite <= 0) {
      $btnAdd.prop('disabled', true).css({
        'opacity': '0.5',
        'cursor': 'not-allowed',
        'pointer-events': 'none'
      }).attr('title', 'Veuillez d\'abord renseigner le montant annuel de la scolarité.');
    } else if (totalTranches >= montantScolarite) {
      $btnAdd.prop('disabled', true).css({
        'opacity': '0.5',
        'cursor': 'not-allowed',
        'pointer-events': 'none'
      }).attr('title', 'La totalité de la scolarité est déjà couverte par les tranches.');
    } else {
      $btnAdd.prop('disabled', false).css({
        'opacity': '1',
        'cursor': 'pointer',
        'pointer-events': 'auto'
      }).attr('title', 'Ajouter une tranche');
    }

    if (rowCount === 0) {
      $('#tranches-empty-state').show();
      $lblReste.text(formatFcfa(montantScolarite)).css('color', '#64748B');
      $badge.text('0 tranche').css({ 'background': '#F1F5F9', 'color': '#64748B' });
    } else {
      $('#tranches-empty-state').hide();
      if (montantScolarite > 0) {
        if (Math.abs(diff) < 0.01) {
          $lblReste.text('0 FCFA (100% Couvert)').css('color', '#15803D');
          $badge.text(rowCount + ' tranche(s) - 100% planifié').css({ 'background': '#DCFCE7', 'color': '#15803D' });
        } else if (diff > 0) {
          $lblReste.text(formatFcfa(diff) + ' restant').css('color', '#B45309');
          $badge.text(rowCount + ' tranche(s) - Incomplet').css({ 'background': '#FEF3C7', 'color': '#B45309' });
        } else {
          $lblReste.text('Dépassement de ' + formatFcfa(Math.abs(diff))).css('color', '#B91C1C');
          $badge.text(rowCount + ' tranche(s) - Dépassement !').css({ 'background': '#FEE2E2', 'color': '#B91C1C' });
        }
      } else {
        $lblReste.text(formatFcfa(diff)).css('color', '#64748B');
        $badge.text(rowCount + ' tranche(s)').css({ 'background': '#EFF6FF', 'color': '#1E3A5F' });
      }
    }
  }

  function addTrancheRow(data) {
    trancheCounter++;
    var idx = trancheCounter;
    var id = data ? (data.id_tranche || '') : '';
    var libelle = data ? (data.libelle_tranche || '') : ('Tranche ' + ($('#tbody-tranches tr').length + 1));
    var montant = data ? (data.montant_tranche || '') : '';
    var dateLimite = data ? (data.date_limite || '') : '';
    var statut = data ? (data.statut_tranche || 'actif') : 'actif';

    var escapedLibelle = $('<div>').text(libelle).html();

    var rowHtml = '<tr class="tranche-row" data-idx="' + idx + '" style="border-bottom:1px solid #F1F5F9;">' +
      '<td style="padding:8px 12px; text-align:center; font-weight:800; color:#1E3A5F;" class="tranche-index-label">1</td>' +
      '<td style="padding:8px 12px;">' +
        (id ? '<input type="hidden" name="tranches[' + idx + '][id_tranche]" value="' + id + '">' : '') +
        '<input type="text" name="tranches[' + idx + '][libelle_tranche]" class="form-control form-control-sm input-tranche-libelle" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px; font-weight:600; font-size:13px;" value="' + escapedLibelle + '" placeholder="Ex: 1ère Tranche (Inscription)" required>' +
      '</td>' +
      '<td style="padding:8px 12px;">' +
        '<input type="number" step="1000" min="0" name="tranches[' + idx + '][montant_tranche]" class="form-control form-control-sm input-tranche-montant" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px; font-weight:700; color:#0F172A; font-size:13px;" value="' + montant + '" placeholder="Ex: 250000" required>' +
      '</td>' +
      '<td style="padding:8px 12px;">' +
        '<input type="date" name="tranches[' + idx + '][date_limite]" class="form-control form-control-sm input-tranche-date" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:6px 10px; font-size:13px;" value="' + dateLimite + '" required>' +
      '</td>' +
      '<td style="padding:8px 12px; text-align:center;">' +
        '<select name="tranches[' + idx + '][statut_tranche]" class="form-control form-control-sm" style="width:100%; border:1px solid #CBD5E1; border-radius:6px; padding:5px 8px; font-size:12px; font-weight:700;">' +
          '<option value="actif" ' + (statut === 'actif' ? 'selected' : '') + '>Actif</option>' +
          '<option value="inactif" ' + (statut === 'inactif' ? 'selected' : '') + '>Inactif</option>' +
        '</select>' +
      '</td>' +
      '<td style="padding:8px 12px; text-align:center;">' +
        '<button type="button" class="btn btn-sm btn-danger btn-remove-tranche" data-id="' + id + '" style="background:#FEE2E2; border:none; color:#B91C1C; border-radius:6px; padding:6px 9px; cursor:pointer;" title="Supprimer cette tranche">' +
          '<i data-lucide="trash-2" style="width:15px; height:15px;"></i>' +
        '</button>' +
      '</td>' +
    '</tr>';

    $('#tbody-tranches').append(rowHtml);
    if (window.lucide) lucide.createIcons();
    updateTranchesSummary();
  }

  $('#btn-add-tranche').on('click', function(e) {
    var montantScolarite = parseFloat($('input[name="montant_scolarite"]').val()) || 0;
    var totalTranches = 0;
    $('#tbody-tranches .input-tranche-montant').each(function() {
      totalTranches += parseFloat($(this).val()) || 0;
    });

    if (montantScolarite <= 0) {
      e.preventDefault();
      displayFormError("Veuillez d'abord saisir le montant annuel de la scolarité avant de pouvoir ajouter des tranches.", $('form'));
      return false;
    }

    if (totalTranches >= montantScolarite) {
      e.preventDefault();
      displayFormError("Le montant total de la scolarité (" + formatFcfa(montantScolarite) + ") est déjà entièrement couvert par les tranches actuelles. Impossible d'ajouter une tranche supplémentaire.", $('form'));
      return false;
    }

    addTrancheRow();
  });

  $(document).on('click', '.btn-remove-tranche', function() {
    var id = $(this).data('id');
    if (id) {
      deletedTrancheIds.push(id);
      $('#deleted_tranches_ids').val(deletedTrancheIds.join(','));
    }
    $(this).closest('tr').remove();
    updateTranchesSummary();
  });

  $(document).on('input change', 'input[name="montant_scolarite"], .input-tranche-montant', function() {
    updateTranchesSummary();
  });

  // Contrôle global sur la soumission du formulaire
  $('form').on('submit', function(e) {
    var montantScolarite = parseFloat($('input[name="montant_scolarite"]').val()) || 0;
    var totalTranches = 0;

    $('#tbody-tranches tr').each(function() {
      var montant = parseFloat($(this).find('.input-tranche-montant').val()) || 0;
      totalTranches += montant;
    });

    // Contrôle du dépassement de scolarité
    if (montantScolarite > 0 && totalTranches > montantScolarite) {
      e.preventDefault();
      var depassement = totalTranches - montantScolarite;
      displayFormError("Erreur sur le montant : Le cumul des tranches (" + formatFcfa(totalTranches) + ") dépasse le montant annuel de la scolarité (" + formatFcfa(montantScolarite) + ") de " + formatFcfa(depassement) + ". Veuillez ajuster les montants des tranches.", $('form'));
      return false;
    }
  });

  // Pre-fill existing tranches on load
  if (existingTranches && existingTranches.length > 0) {
    existingTranches.forEach(function(t) {
      addTrancheRow(t);
    });
  } else {
    updateTranchesSummary();
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
