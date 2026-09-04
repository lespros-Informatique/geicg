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
            <i data-lucide="file-check" style="width: 24px; height: 24px; color: #1E3A5F;"></i> <?= !empty($item['id_piece_fournir']) ? 'Modification de la Pièce / Document' : 'Nouvelle Pièce au Répertoire' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            <?= !empty($item['id_piece_fournir']) ? 'Mise à jour des informations de la pièce' : 'Ajoutez un ou plusieurs documents administratifs exigés aux dossiers des étudiants' ?>
          </p>
        </div>
        <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour au répertoire
        </a>
      </div>

      <?php if (!empty($item['id_piece_fournir'])): ?>
        <!-- FORMULAIRE MODIFICATION SIMPLE -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          <form action="<?= RACINE ?>piece_fournir/edit" method="POST" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
            <input type="hidden" name="id_piece_fournir" value="<?= $item['id_piece_fournir'] ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px; width: 100%;">
              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Code Document</label>
                <input type="text" value="<?= htmlspecialchars($item['code_piece_fournir'] ?? '') ?>" disabled class="form-control" style="background:#F8FAFC; border-radius: 8px; padding: 10px 14px; font-weight: 700; color: #1E3A5F; width: 100%;">
              </div>

              <div>
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
                <select name="statut_piece" class="form-select" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="actif" <?= ($item['statut_piece'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                  <option value="inactif" <?= ($item['statut_piece'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                </select>
              </div>

              <div style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Intitulé du document / pièce *</label>
                <input type="text" name="libelle_piece" value="<?= htmlspecialchars($item['libelle_piece'] ?? '') ?>" required placeholder="Ex: 01 Photocopie de la CNI..." class="form-control" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; width: 100%;">
              </div>

              <div style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Instructions / Précisions complémentaires</label>
                <textarea name="description_piece" rows="3" placeholder="Ex: Document en cours de validité, copie certifiée conforme..." class="form-control" style="border-radius: 8px; padding: 10px 14px; border: 1px solid #CBD5E1; font-size: 13.5px; width: 100%;"><?= htmlspecialchars($item['description_piece'] ?? '') ?></textarea>
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
              <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</a>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer les modifications</button>
            </div>
          </form>
        </div>

      <?php else: ?>
        <!-- FORMULAIRE AJOUT RAPIDE / MULTI-LIGNES DE DOCUMENTS -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          <form action="<?= RACINE ?>piece_fournir/add" method="POST" id="form-add-pieces" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid #EFF6FF;">
              <div>
                <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0;">Documents / Pièces à Ajouter</h3>
                <p style="color: #64748B; font-size: 12.5px; margin: 2px 0 0 0;">Saisissez les pièces requises pour les dossiers des élèves / étudiants.</p>
              </div>
              <button type="button" id="btn-add-piece-row" class="btn" style="background: #EFF6FF; color: #1E3A5F; border: 1.5px solid #BFDBFE; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="plus-circle" style="width: 15px; height: 15px;"></i> + Ajouter une pièce
              </button>
            </div>

            <div id="pieces-container" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; width: 100%;">
              <!-- Lignes ajoutées dynamiquement -->
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
              <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</a>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; font-size: 14px; border-radius: 8px; padding: 11px 28px; box-shadow: 0 4px 12px rgba(30,58,95,0.25);">
                Enregistrer au répertoire
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  var pieceIdx = 0;

  function addPieceRow(libelle, desc) {
    libelle = libelle || '';
    desc = desc || '';

    var html = '<div class="piece-row" style="display: flex; gap: 14px; align-items: center; background: #F8FAFC; padding: 14px; border-radius: 8px; border: 1px solid #E2E8F0; width: 100%; box-sizing: border-box;">' +
      '<div style="flex: 1;">' +
        '<input type="text" name="pieces[' + pieceIdx + '][libelle]" value="' + $('<div>').text(libelle).html() + '" placeholder="Ex: 01 Photocopie de la CNI *" required class="form-control inp-piece-libelle" style="border-radius:6px; font-weight:700; font-size:13.5px; padding:9px 12px; width:100%; box-sizing:border-box;">' +
      '</div>' +
      '<div style="flex: 1;">' +
        '<input type="text" name="pieces[' + pieceIdx + '][description]" value="' + $('<div>').text(desc).html() + '" placeholder="Instructions (ex: copie certifiée conforme)" class="form-control" style="border-radius:6px; font-size:13px; padding:9px 12px; width:100%; box-sizing:border-box;">' +
      '</div>' +
      '<div>' +
        '<button type="button" class="btn btn-sm btn-delete-piece" style="background:#FEE2E2; color:#B91C1C; border:none; border-radius:6px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-weight:bold;" title="Supprimer la ligne">✕</button>' +
      '</div>' +
    '</div>';

    $('#pieces-container').append(html);
    pieceIdx++;
  }

  // Initial empty rows
  if ($('#pieces-container').length && $('#pieces-container .piece-row').length === 0) {
    addPieceRow('', '');
    addPieceRow('', '');
  }

  $('#btn-add-piece-row').on('click', function() {
    addPieceRow('', '');
  });

  $(document).on('click', '.btn-delete-piece', function() {
    if ($('#pieces-container .piece-row').length > 1) {
      $(this).closest('.piece-row').remove();
    }
  });

  $(document).on('change', '.inp-piece-libelle', function() {
    var val = $.trim($(this).val()).toLowerCase();
    if (!val) return;
    var count = 0;
    var thisInp = $(this);
    $('.inp-piece-libelle').each(function() {
      if ($.trim($(this).val()).toLowerCase() === val) count++;
    });

    if (count > 1) {
      if (window.toastr) toastr.warning('<strong>Doublon détecté :</strong> Vous avez déjà saisi le document « <b>' + $('<div>').text(thisInp.val()).html() + '</b> » dans une autre ligne.');
      thisInp.val('').css({
        'border': '2px solid #EF4444',
        'background-color': '#FEF2F2'
      });
      setTimeout(function() {
        thisInp.css({ 'border': '', 'background-color': '' });
      }, 2500);
    }
  });

  $('#form-add-pieces').on('submit', function(e) {
    var hasValid = false;
    var seenLibelles = [];
    var hasDuplicate = false;
    var dupName = '';

    $('.inp-piece-libelle').each(function() {
      var rawVal = $.trim($(this).val());
      var val = rawVal.toLowerCase();
      if (val !== '') {
        hasValid = true;
        if (seenLibelles.indexOf(val) !== -1) {
          hasDuplicate = true;
          dupName = rawVal;
        } else {
          seenLibelles.push(val);
        }
      }
    });

    if (!hasValid) {
      e.preventDefault();
      var msg = 'Veuillez renseigner au moins un document à fournir.';
      if (window.toastr) toastr.error(msg); else alert(msg);
      return false;
    }

    if (hasDuplicate) {
      e.preventDefault();
      var msg = '<strong>Attention :</strong> Vous avez saisi le même intitulé de document (« <b>' + $('<div>').text(dupName).html() + '</b> ») plusieurs fois.';
      if (window.toastr) toastr.error(msg); else alert(msg);
      return false;
    }
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
