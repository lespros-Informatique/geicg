<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$cycles = (new ModelCycle())->getByStatus('actif');
$filieres = (new ModelFiliere())->getByStatus('actif');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête Général du Hub -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layers" style="color: #1E3A5F; width: 24px; height: 24px;"></i> Filières & Cycles d'Études
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion centralisée du Catalogue des Filières, des Cycles et de leurs Assignations</p>
        </div>
      </div>

      <!-- Barre d'Onglets Structurée -->
      <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 2px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" data-tab="tab-assignations" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #1E3A5F; border-bottom: 3px solid #1E3A5F; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="git-merge" style="width: 16px; height: 16px;"></i> 1. Assignations Filières - Cycles
        </button>
        <button type="button" class="tab-btn" data-tab="tab-filieres" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="book-open" style="width: 16px; height: 16px;"></i> 2. Catalogue des Filières
        </button>
        <button type="button" class="tab-btn" data-tab="tab-cycles" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 16px; height: 16px;"></i> 3. Cycles d'Études
        </button>
      </div>

      <!-- CONTENU DU TAB 1 : ASSIGNATIONS -->
      <div id="tab-assignations" class="tab-content" style="display: block;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Table d'Assignation Filières ↔ Cycles</h2>
          <button type="button" class="btn btn-primary btn-add-assignation" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Assignation
          </button>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-assignations" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>Code</th>
                  <th>Cycle D'Études</th>
                  <th>Filière Associée</th>
                  <th class="text-center">Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENU DU TAB 2 : CATALOGUE FILIÈRES -->
      <div id="tab-filieres" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Catalogue Général des Filières</h2>
          <button type="button" class="btn btn-primary btn-add-filiere" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter une Filière
          </button>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-filieres-catalogue" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>Code</th>
                  <th>Nom de la Filière</th>
                  <th>Type</th>
                  <th>Description</th>
                  <th class="text-center">Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENU DU TAB 3 : CYCLES D'ÉTUDES -->
      <div id="tab-cycles" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Référentiel des Cycles d'Études</h2>
          <button type="button" class="btn btn-primary btn-add-cycle" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Cycle
          </button>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-cycles-list" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>Code Cycle</th>
                  <th>Libellé du Cycle</th>
                  <th>Description</th>
                  <th class="text-center">Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1 : ASSIGNATION FILIÈRE ↔ CYCLE -->
<!-- ========================================================================= -->
<div id="modal-assignation" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-assignation-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="git-merge" style="width: 18px; height: 18px;"></i> Nouvelle Assignation Filière ↔ Cycle
      </h3>
      <button type="button" class="btn-close-modal-assign" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-assignation" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_filiere_cycle" id="assign_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Cycle Académique <span style="color: #EF4444;">*</span>
        </label>
        <select name="cycle_code" id="assign_cycle_code" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="">-- Sélectionner un cycle --</option>
          <?php foreach ($cycles as $cy): ?>
            <option value="<?= htmlspecialchars($cy['code_cycle']) ?>"><?= htmlspecialchars($cy['libelle_cycle']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Filière à rattacher <span style="color: #EF4444;">*</span>
        </label>
        <select name="filiere_code" id="assign_filiere_code" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="">-- Sélectionner une filière --</option>
          <?php foreach ($filieres as $fil): ?>
            <option value="<?= htmlspecialchars($fil['code_filiere']) ?>"><?= htmlspecialchars($fil['libelle_filiere']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_filiere_cycle" id="assign_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-assign" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-assign" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2 : CATALOGUE DES FILIÈRES -->
<!-- ========================================================================= -->
<div id="modal-filiere" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-filiere-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="book-open" style="width: 18px; height: 18px;"></i> Ajouter une Filière
      </h3>
      <button type="button" class="btn-close-modal-filiere" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-filiere" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_filiere" id="filiere_id" value="">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Nom complet de la Filière <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_filiere" id="filiere_libelle" required placeholder="Ex: Informatique et Sciences du Numérique" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Sigle / Code court</label>
          <input type="text" name="slug_filiere" id="filiere_slug" placeholder="Ex: ISN, GEC, FC..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de Filière</label>
          <select name="type_filiere" id="filiere_type" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="">-- Non spécifié --</option>
            <option value="TERTIAIRE">Tertiaire</option>
            <option value="INDUSTRIELLE">Industrielle</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Description / Débouchés</label>
        <textarea name="description_filiere" id="filiere_desc" rows="2" placeholder="Brève présentation de la formation..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13.5px;"></textarea>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_filiere" id="filiere_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-filiere" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-filiere" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3 : CYCLES D'ÉTUDES -->
<!-- ========================================================================= -->
<div id="modal-cycle" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-cycle-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Ajouter un Cycle d'Études
      </h3>
      <button type="button" class="btn-close-modal-cycle" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-cycle" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_cycle" id="cycle_id" value="">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Libellé du Cycle <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_cycle" id="cycle_libelle" required placeholder="Ex: Brevet de Technicien Supérieur, Licence Professionnelle, Master..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Sigle / Nom court</label>
        <input type="text" name="slug_cycle" id="cycle_slug" placeholder="Ex: BTS, LICENCE, MASTER..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Description</label>
        <textarea name="description_cycle" id="cycle_desc" rows="2" placeholder="Informations complémentaires..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13.5px;"></textarea>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_cycle" id="cycle_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-cycle" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-cycle" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Gestion des Onglets
  $('.tab-btn').on('click', function() {
    $('.tab-btn').css({ 'color': '#64748B', 'border-bottom-color': 'transparent' }).removeClass('active');
    $(this).css({ 'color': '#1E3A5F', 'border-bottom-color': '#1E3A5F' }).addClass('active');
    $('.tab-content').hide();
    var targetTab = $(this).data('tab');
    $('#' + targetTab).show();
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  });

  // Détection automatique de l'onglet actif selon l'URL ou le paramètre ?tab=
  var urlParams = new URLSearchParams(window.location.search);
  var requestedTab = urlParams.get('tab') || window.location.hash.replace('#', '');
  if (requestedTab && $('#' + requestedTab).length) {
    $('.tab-btn[data-tab="' + requestedTab + '"]').click();
  } else if (window.location.pathname.indexOf('filiere_cycle') !== -1) {
    $('.tab-btn[data-tab="tab-assignations"]').click();
  } else if (window.location.pathname.indexOf('cycle') !== -1) {
    $('.tab-btn[data-tab="tab-cycles"]').click();
  } else if (window.location.pathname.indexOf('filiere') !== -1) {
    $('.tab-btn[data-tab="tab-filieres"]').click();
  }

  // Table 1: Assignations
  var tableAssign = $('#table-assignations').DataTable({
    ajax: '<?= RACINE ?>filiere_cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_filiere_cycle', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_cycle', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'libelle_filiere', render: function(d) { return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>'; } },
      { data: 'statut_filiere_cycle', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-fc" data-id="' + row.id_filiere_cycle + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-assign" data-id="' + d.id_filiere_cycle + '" data-cycle="' + (d.cycle_code || '') + '" data-filiere="' + (d.filiere_code || '') + '" data-statut="' + (d.statut_filiere_cycle || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="<?= RACINE ?>filiere_cycle/details/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Filières
  var tableFilieres = $('#table-filieres-catalogue').DataTable({
    ajax: '<?= RACINE ?>filiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere', defaultContent: '-' },
      { data: 'code_filiere', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_filiere', render: function(d, type, row) { 
        var useSlug = row.use_slug_filiere;
        if (useSlug && row.slug_filiere) {
          return '<span style="font-weight:700; color:#0F172A;">' + row.slug_filiere + '</span>' +
                 ' <span style="font-size:12px; color:#64748B; margin-left:6px;">(' + (d || '-') + ')</span>';
        }
        var html = '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
        if (row.slug_filiere) {
          html += ' <span class="badge" style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:2px 6px; border-radius:4px; font-weight:700; font-size:11px; margin-left:6px;">' + row.slug_filiere + '</span>';
        }
        return html;
      } },
      { data: 'type_filiere', render: function(d) {
        if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
        if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
        return '<span style="color:#94A3B8; font-size:12px; font-style:italic;">Non spécifié</span>';
      } },
      { data: 'description_filiere', render: function(d) { return d || '-'; } },
      { data: 'statut_filiere', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-fil" data-id="' + row.id_filiere + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_filiere || '').html();
        var safeSlug = $('<div>').text(d.slug_filiere || '').html();
        var safeDesc = $('<div>').text(d.description_filiere || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-filiere" data-id="' + d.id_filiere + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-type="' + (d.type_filiere || '') + '" data-desc="' + safeDesc + '" data-statut="' + (d.statut_filiere || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="<?= RACINE ?>filiere/details/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 3: Cycles d'Études
  var tableCycles = $('#table-cycles-list').DataTable({
    ajax: '<?= RACINE ?>cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cycle', defaultContent: '-' },
      { data: 'code_cycle', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_cycle', render: function(d, type, row) { 
        var useSlug = row.use_slug_cycle;
        if (useSlug && row.slug_cycle) {
          return '<span style="font-weight:700; color:#1E3A5F;">' + row.slug_cycle + '</span>' +
                 ' <span style="font-size:12px; color:#64748B; margin-left:6px;">(' + (d || '-') + ')</span>';
        }
        var html = '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>';
        if (row.slug_cycle) {
          html += ' <span class="badge" style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:2px 6px; border-radius:4px; font-weight:700; font-size:11px; margin-left:6px;">' + row.slug_cycle + '</span>';
        }
        return html;
      } },
      { data: 'description_cycle', render: function(d) { return d || '-'; } },
      { data: 'statut_cycle', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-cyc" data-id="' + row.id_cycle + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, className: 'text-end', render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_cycle || '').html();
        var safeSlug = $('<div>').text(d.slug_cycle || '').html();
        var safeDesc = $('<div>').text(d.description_cycle || '').html();
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-cycle" data-id="' + d.id_cycle + '" data-libelle="' + safeLibelle + '" data-slug="' + safeSlug + '" data-desc="' + safeDesc + '" data-statut="' + (d.statut_cycle || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="<?= RACINE ?>cycle/details/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // ==========================================
  // RECHARGEMENT DYNAMIQUE ET SELECT2 DES ASSIGNATIONS
  // ==========================================
  function initAssignSelect2() {
    if ($.fn.select2) {
      $('#assign_cycle_code, #assign_filiere_code, #assign_statut').select2({
        width: '100%',
        dropdownParent: $('#modal-assignation')
      });
    }
  }

  function reloadAssignSelects(selectedCycle, selectedFiliere) {
    var cVal = selectedCycle || $('#assign_cycle_code').val();
    var fVal = selectedFiliere || $('#assign_filiere_code').val();

    $.getJSON('<?= RACINE ?>cycle/apiList?statut=actif', function(res) {
      if (res && res.data) {
        var opts = '<option value="">-- Sélectionner un cycle --</option>';
        res.data.forEach(function(c) {
          opts += '<option value="' + c.code_cycle + '">' + (c.libelle_cycle || c.slug_cycle || c.code_cycle) + '</option>';
        });
        $('#assign_cycle_code').html(opts);
        if (cVal) $('#assign_cycle_code').val(cVal);
        if ($.fn.select2) $('#assign_cycle_code').trigger('change.select2');
      }
    });

    $.getJSON('<?= RACINE ?>filiere/apiList?statut=actif', function(res) {
      if (res && res.data) {
        var opts = '<option value="">-- Sélectionner une filière --</option>';
        res.data.forEach(function(f) {
          opts += '<option value="' + f.code_filiere + '">' + (f.libelle_filiere || f.slug_filiere || f.code_filiere) + '</option>';
        });
        $('#assign_filiere_code').html(opts);
        if (fVal) $('#assign_filiere_code').val(fVal);
        if ($.fn.select2) $('#assign_filiere_code').trigger('change.select2');
      }
    });
  }

  // ==========================================
  // HANDLERS : MODAL 1 (ASSIGNATIONS)
  // ==========================================
  $('.btn-add-assignation').on('click', function() {
    $('#form-assignation')[0].reset();
    $('#assign_id').val('');
    $('#modal-assignation-title').html('<i data-lucide="git-merge" style="width: 18px; height: 18px;"></i> Nouvelle Assignation Filière ↔ Cycle');
    reloadAssignSelects();
    $('#modal-assignation').css('display', 'flex');
    initAssignSelect2();
    if ($.fn.select2) {
      $('#assign_cycle_code').val('').trigger('change.select2');
      $('#assign_filiere_code').val('').trigger('change.select2');
      $('#assign_statut').val('actif').trigger('change.select2');
    }
    if (window.lucide) lucide.createIcons();
  });

  $(document).on('click', '.btn-edit-assign', function() {
    var id = $(this).data('id');
    var cycle = $(this).data('cycle');
    var filiere = $(this).data('filiere');
    var statut = $(this).data('statut');

    $('#assign_id').val(id);
    $('#assign_cycle_code').val(cycle);
    $('#assign_filiere_code').val(filiere);
    $('#assign_statut').val(statut || 'actif');
    $('#modal-assignation-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier l\'Assignation');
    reloadAssignSelects(cycle, filiere);
    $('#modal-assignation').css('display', 'flex');
    initAssignSelect2();
    if ($.fn.select2) {
      $('#assign_cycle_code').val(cycle).trigger('change.select2');
      $('#assign_filiere_code').val(filiere).trigger('change.select2');
      $('#assign_statut').val(statut || 'actif').trigger('change.select2');
    }
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-assign').on('click', function() {
    $('#modal-assignation').css('display', 'none');
  });
  $('#modal-assignation').on('click', function(e) {
    if ($(e.target).is('#modal-assignation')) $(this).css('display', 'none');
  });

  $('#form-assignation').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#assign_id').val();
    var url = isEdit ? '<?= RACINE ?>filiere_cycle/edit' : '<?= RACINE ?>filiere_cycle/add';
    var $btn = $('#btn-save-assign');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Assignation enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Assignation enregistrée avec succès');
          $('#modal-assignation').css('display', 'none');
          tableAssign.ajax.reload(null, false);
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // ==========================================
  // HANDLERS : MODAL 2 (CATALOGUE FILIÈRES)
  // ==========================================
  $('.btn-add-filiere').on('click', function() {
    $('#form-filiere')[0].reset();
    $('#filiere_id').val('');
    $('#modal-filiere-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter une Filière');
    $('#modal-filiere').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#filiere_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-filiere', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var type = $(this).data('type');
    var desc = $(this).data('desc');
    var statut = $(this).data('statut');

    $('#filiere_id').val(id);
    $('#filiere_libelle').val(libelle);
    $('#filiere_slug').val(slug);
    $('#filiere_type').val(type);
    $('#filiere_desc').val(desc);
    $('#filiere_statut').val(statut || 'actif');
    $('#modal-filiere-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Filière');
    $('#modal-filiere').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#filiere_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-filiere').on('click', function() {
    $('#modal-filiere').css('display', 'none');
  });
  $('#modal-filiere').on('click', function(e) {
    if ($(e.target).is('#modal-filiere')) $(this).css('display', 'none');
  });

  $('#form-filiere').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#filiere_id').val();
    var url = isEdit ? '<?= RACINE ?>filiere/edit' : '<?= RACINE ?>filiere/add';
    var $btn = $('#btn-save-filiere');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Filière enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Filière enregistrée avec succès');
          $('#modal-filiere').css('display', 'none');
          tableFilieres.ajax.reload(null, false);
          reloadAssignSelects();
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // ==========================================
  // HANDLERS : MODAL 3 (CYCLES D'ÉTUDES)
  // ==========================================
  $('.btn-add-cycle').on('click', function() {
    $('#form-cycle')[0].reset();
    $('#cycle_id').val('');
    $('#modal-cycle-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Cycle d\'Études');
    $('#modal-cycle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#cycle_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-cycle', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var slug = $(this).data('slug');
    var desc = $(this).data('desc');
    var statut = $(this).data('statut');

    $('#cycle_id').val(id);
    $('#cycle_libelle').val(libelle);
    $('#cycle_slug').val(slug);
    $('#cycle_desc').val(desc);
    $('#cycle_statut').val(statut || 'actif');
    $('#modal-cycle-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Cycle d\'Études');
    $('#modal-cycle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#cycle_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-cycle').on('click', function() {
    $('#modal-cycle').css('display', 'none');
  });
  $('#modal-cycle').on('click', function(e) {
    if ($(e.target).is('#modal-cycle')) $(this).css('display', 'none');
  });

  $('#form-cycle').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#cycle_id').val();
    var url = isEdit ? '<?= RACINE ?>cycle/edit' : '<?= RACINE ?>cycle/add';
    var $btn = $('#btn-save-cycle');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.showToast) showToast(res.message || 'Cycle enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Cycle enregistré avec succès');
          $('#modal-cycle').css('display', 'none');
          tableCycles.ajax.reload(null, false);
          reloadAssignSelects();
        } else {
          if (window.showToast) showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.showToast) showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Bascule statut AJAX pour les 3 tables
  function bindAjaxToggle(selector, url, tableRef) {
    $(document).on('change', selector, function() {
      var id = $(this).data('id');
      var isChecked = $(this).is(':checked');
      var $input = $(this);
      $.ajax({
        url: url,
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { id: id, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.showToast) showToast(res.message || 'Statut mis à jour avec succès', 'success');
            else if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
            tableRef.ajax.reload(null, false);
          } else {
            if (window.showToast) showToast(res.message || 'Erreur lors du changement de statut', 'error');
            else if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
            $input.prop('checked', !isChecked);
          }
        },
        error: function() {
          if (window.showToast) showToast('Erreur réseau', 'error');
          else if (window.toastr) toastr.error('Erreur réseau');
          $input.prop('checked', !isChecked);
        }
      });
    });
  }

  bindAjaxToggle('.toggle-statut-fc', '<?= RACINE ?>filiere_cycle/changer', tableAssign);
  bindAjaxToggle('.toggle-statut-fil', '<?= RACINE ?>filiere/changer', tableFilieres);
  bindAjaxToggle('.toggle-statut-cyc', '<?= RACINE ?>cycle/changer', tableCycles);
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>