<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Grille des Tarifs des Frais Annexes</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des barèmes de frais annexes par type de filière et session académique</p>
        </div>
        <div>
          <button type="button" class="btn btn-primary btn-add-frais" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Tarification Frais Annexes
          </button>
        </div>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Année Académique</label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach (($annees ?? []) as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($selectedAnneeCode ?? '') === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['statut_annee'] ?? '') === 'actif' ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Catégorie</label>
            <select id="filter-categorie" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les catégories --</option>
              <option value="inscription">Inscription</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Type de Filière</label>
            <select id="filter-type-filiere" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les types --</option>
              <option value="INDUSTRIELLE">Filière Industrielle</option>
              <option value="TERTIAIRE">Filière Tertiaire</option>
              <option value="TOUT">Toutes Filières (Général)</option>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau d'Étude</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach (($niveaux ?? []) as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- TABLEAU DES TARIFS DE FRAIS ANNEXES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-frais-annexes" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Libellé de la Tarification</th>
                <th style="padding: 12px;">Catégorie</th>
                <th style="padding: 12px;">Cible (Filière)</th>
                <th style="padding: 12px;">Niveau Cible</th>
                <th style="padding: 12px;">Montant Frais Annexes</th>
                <th style="padding: 12px;" class="text-center">Statut</th>
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

<!-- ========================================================================= -->
<!-- MODAL DE CRÉATION / ÉDITION D'UN TARIF DE FRAIS ANNEXES -->
<!-- ========================================================================= -->
<div id="modal-frais-annexe" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-frais-annexe-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Tarification de Frais Annexes
      </h3>
      <button type="button" class="btn-close-modal-frais" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-frais-annexe" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_frais_annexe" id="frais_annexe_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Libellé du Tarif <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_frais_annexe" id="frais_libelle" required placeholder="Ex: Pack Frais Annexes Filière Industrielle" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Catégorie <span style="color: #EF4444;">*</span>
        </label>
        <select name="categorie_frais_annexe" id="frais_categorie" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="inscription">Inscription</option>
          <option value="autre">Autre</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Type de Filières Cible <span style="color: #EF4444;">*</span>
        </label>
        <select name="type_filiere" id="frais_type_filiere" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="INDUSTRIELLE">Filière Industrielle (Ex: IDA, RIT...)</option>
          <option value="TERTIAIRE">Filière Tertiaire (Ex: RHCOM, GEC...)</option>
          <option value="TOUT">Toutes les Filières (Général)</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Niveau d'Étude Cible
        </label>
        <select name="niveau_code" id="frais_niveau_code" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="TOUT">-- Tous les niveaux --</option>
          <?php foreach (($niveaux ?? []) as $n): ?>
            <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
              <?= htmlspecialchars($n['libelle_niveau']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Montant Total des Frais Annexes (FCFA) <span style="color: #EF4444;">*</span>
        </label>
        <input type="number" min="0" step="any" name="montant_frais_annexe" id="frais_montant" required placeholder="Ex: 60000" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 15px; color: #15803D;">
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_frais_annexe" id="frais_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-frais" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-frais" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>

<script>
$(document).ready(function() {
  $('.select2').select2({ width: '100%' });

  var tableFrais = $('#table-frais-annexes').DataTable({
    ajax: {
      url: '<?= RACINE ?>fraisAnnexe/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.categorie = $('#filter-categorie').val();
        d.type_filiere = $('#filter-type-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_frais_annexe', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_frais_annexe', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'categorie_frais_annexe', render: function(d) {
        if (!d) return '-';
        var label = (d === 'inscription') ? 'Inscription' : 'Autre';
        return '<span style="font-weight:600; color:#334155;">' + label + '</span>';
      }},
      { data: 'type_filiere', render: function(d) {
        if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
        if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
        return '<span class="badge" style="background:#F1F5F9; color:#475569; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Toutes Filières</span>';
      }},
      { data: 'libelle_niveau', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">Tous les Niveaux</span>';
        return '<span style="font-weight:600; color:#334155;">' + d + '</span>';
      }},
      { data: 'montant_frais_annexe', render: function(d) {
        var val = parseFloat(d || 0);
        return '<span style="font-weight:800; color:#15803D; font-size:14px;">' + val.toLocaleString('fr-FR') + ' FCFA</span>';
      }},
      { data: 'statut_frais_annexe', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-frais" data-id="' + row.id_frais_annexe + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '120px', orderable: false, render: function(d) {
        var safeLib = (d.libelle_frais_annexe || '').replace(/"/g, '&quot;');
        var fid = d.id_frais_annexe || d.id || '';
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-frais" ' +
               'data-id="' + fid + '" ' +
               'data-libelle="' + safeLib + '" ' +
               'data-categorie="' + (d.categorie_frais_annexe || 'autre') + '" ' +
               'data-type-filiere="' + (d.type_filiere || 'TOUT') + '" ' +
               'data-niveau-code="' + (d.niveau_code || 'TOUT') + '" ' +
               'data-montant="' + (d.montant_frais_annexe || '0') + '" ' +
               'data-statut="' + (d.statut_frais_annexe || 'actif') + '" ' +
               'style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">' +
               '<i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee, #filter-categorie, #filter-type-filiere, #filter-niveau').on('change', function() {
    tableFrais.ajax.reload();
  });

  // Modal : Ajouter
  $('.btn-add-frais').on('click', function() {
    $('#form-frais-annexe')[0].reset();
    $('#frais_annexe_id').val('');
    $('#frais_categorie').val('inscription');
    $('#frais_type_filiere').val('INDUSTRIELLE');
    $('#frais_niveau_code').val('TOUT');
    $('#modal-frais-annexe-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Tarification de Frais Annexes');
    $('#modal-frais-annexe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Modal : Éditer
  $(document).on('click', '.btn-edit-frais', function() {
    var $btn = $(this);
    var id = $btn.attr('data-id') || $btn.data('id');
    var libelle = $btn.attr('data-libelle') || $btn.data('libelle');
    var categorie = $btn.attr('data-categorie') || $btn.data('categorie') || 'autre';
    var typeFiliere = $btn.attr('data-type-filiere') || $btn.data('typeFiliere') || $btn.data('type-filiere');
    var niveauCode = $btn.attr('data-niveau-code') || $btn.data('niveauCode') || $btn.data('niveau-code');
    var montant = $btn.attr('data-montant') || $btn.data('montant');
    var statut = $btn.attr('data-statut') || $btn.data('statut');

    var decLib = $('<div>').html(libelle || '').text();

    $('#frais_annexe_id').val(id);
    $('#frais_libelle').val(decLib || libelle);
    $('#frais_categorie').val(categorie);
    $('#frais_type_filiere').val(typeFiliere || 'TOUT');
    $('#frais_niveau_code').val(niveauCode || 'TOUT');
    $('#frais_montant').val(montant);
    $('#frais_statut').val(statut || 'actif');
    $('#modal-frais-annexe-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Tarif de Frais Annexes');
    $('#modal-frais-annexe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Fermer modal via bouton (croix ou Annuler)
  $(document).on('click', '.btn-close-modal-frais', function() {
    $('#modal-frais-annexe').css('display', 'none');
  });

  // Fermer modal en cliquant sur l'arrière-plan (backdrop)
  $(document).on('click', '#modal-frais-annexe', function(e) {
    if ($(e.target).is('#modal-frais-annexe')) {
      $(this).css('display', 'none');
    }
  });

  // Helper de notification
  function notify(msg, type) {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else if (window.toastr && typeof toastr[type] === 'function') {
      toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  // Soumission Formulaire
  $('#form-frais-annexe').on('submit', function(e) {
    e.preventDefault();
    var isEdit = $('#frais_annexe_id').val() !== '';
    var targetUrl = isEdit ? '<?= RACINE ?>fraisAnnexe/edit' : '<?= RACINE ?>fraisAnnexe/store';
    var $btn = $('#btn-save-frais');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: targetUrl,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          notify(res.message || 'Opération réussie avec succès !', 'success');
          $('#modal-frais-annexe').css('display', 'none');
          tableFrais.ajax.reload(null, false);
        } else {
          notify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr, status, err) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = xhr.responseJSON?.message || err || 'Erreur lors de l\'enregistrement';
        notify(msg, 'error');
      }
    });
  });

  // Toggle Statut
  $(document).on('change', '.toggle-statut-frais', function() {
    var id = $(this).data('id');
    $.ajax({
      url: '<?= RACINE ?>fraisAnnexe/toggleStatut',
      type: 'POST',
      data: { id_frais_annexe: id, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          notify(res.message, 'success');
        } else {
          notify(res.message, 'error');
        }
        tableFrais.ajax.reload(null, false);
      },
      error: function() {
        notify('Erreur serveur lors du changement de statut', 'error');
        tableFrais.ajax.reload(null, false);
      }
    });
  });
});
</script>
