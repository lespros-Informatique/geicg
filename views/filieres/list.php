<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$cycles = (new ModelCycle())->getByStatus('actif');
$filieres = (new ModelFiliere())->getByStatus('actif');
$niveaux = (new ModelNiveau())->getActifs();
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
            <i data-lucide="layers" style="color: #1E3A5F; width: 24px; height: 24px;"></i> Offre Académique : Cycles, Filières & Niveaux
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion des Cycles, Filières, Niveaux</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <?php if (isset($canAccess) && $canAccess([PERMISSIONS::PRINT_OFFRE_ACADEMIQUE, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_CYCLES])): ?>
            <a href="<?= RACINE ?>filiere_cycle/imprimerPdf" target="_blank" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer l'Offre Académique (PDF)
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Barre d'Onglets Structurée -->
      <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 2px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" data-tab="tab-assignations" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #1E3A5F; border-bottom: 3px solid #1E3A5F; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="git-merge" style="width: 16px; height: 16px;"></i> 1.(Cycle - Filière - Niveau)
        </button>
        <button type="button" class="tab-btn" data-tab="tab-filieres" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="book-open" style="width: 16px; height: 16px;"></i> 2. Catalogue des Filières
        </button>
        <button type="button" class="tab-btn" data-tab="tab-cycles" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 16px; height: 16px;"></i> 3. Cycles d'Études
        </button>
        <button type="button" class="tab-btn" data-tab="tab-niveaux" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i> 4. Niveaux d'Études
        </button>
      </div>

      <!-- CONTENU DU TAB 1 : ASSIGNATIONS PARCOURS PIVOTS -->
      <div id="tab-assignations" class="tab-content" style="display: block;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Table des Parcours Pivots (Cycle ↔ Filière ↔ Niveau)</h2>
          <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php if (isset($canAccess) && $canAccess([PERMISSIONS::PRINT_OFFRE_ACADEMIQUE, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::VIEW_CYCLES])): ?>
              <a href="<?= RACINE ?>filiere_cycle/imprimerPdf" target="_blank" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; text-decoration: none;">
                <i data-lucide="printer" style="width: 15px; height: 15px;"></i> Imprimer l'Offre (PDF)
              </a>
            <?php endif; ?>
            <button type="button" class="btn btn-primary btn-add-assignation" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouveau Parcours Pivot
            </button>
          </div>
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
                  <th>Type Filière</th>
                  <th>Niveau d'Études</th>
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
          <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php if (isset($canAccess) && $canAccess([PERMISSIONS::PRINT_FILIERES, PERMISSIONS::VIEW_FILIERES, PERMISSIONS::PRINT_OFFRE_ACADEMIQUE])): ?>
              <a href="<?= RACINE ?>filiere/imprimerPdf" target="_blank" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; text-decoration: none;">
                <i data-lucide="printer" style="width: 15px; height: 15px;"></i> Imprimer les Filières (PDF)
              </a>
            <?php endif; ?>
            <button type="button" class="btn btn-primary btn-add-filiere" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter une Filière
            </button>
          </div>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-filieres-catalogue" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>Code</th>
                  <th>Nom de la Filière</th>
                  <th>Sigle / Slug</th>
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
                  <th>Slug</th>
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

      <!-- CONTENU DU TAB 4 : NIVEAUX D'ÉTUDES -->
      <div id="tab-niveaux" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Référentiel des Niveaux d'Études</h2>
          <button type="button" class="btn btn-primary btn-add-niveau" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Niveau
          </button>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-niveaux-list" class="table display nowrap" style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>Code Niveau</th>
                  <th>Libellé du Niveau</th>
                  <th>Sigle / Slug</th>
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

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Type de Filière
        </label>
        <input type="text" id="assign_type_filiere_display" readonly value="" placeholder="Type de filière" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #475569; font-weight: 700; font-size: 14px; cursor: not-allowed;">
        <input type="hidden" name="type_filiere" id="assign_type_filiere" value="AUTO">
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Niveau d'études <span style="color: #EF4444;">*</span>
        </label>
        <select name="niveau_code" id="assign_niveau_code" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="">-- Sélectionner un niveau --</option>
          <?php foreach (($niveaux ?? []) as $niv): ?>
            <option value="<?= htmlspecialchars($niv['code_niveau']) ?>"><?= htmlspecialchars($niv['libelle_niveau']) ?></option>
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

<!-- ========================================================================= -->
<!-- MODAL 4 : NIVEAUX D'ÉTUDES -->
<!-- ========================================================================= -->
<div id="modal-niveau" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-niveau-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter un Niveau d'Études
      </h3>
      <button type="button" class="btn-close-modal-niveau" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-niveau" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_niveau" id="niveau_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Intitulé complet du niveau <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_niveau" id="niveau_libelle" required placeholder="Ex: Licence 1, Master 2, BTS 1ère Année..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Abréviation / Sigle court
        </label>
        <input type="text" name="slug_niveau" id="niveau_slug" placeholder="Ex: L1, L2, M1, M2, BTS1..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Utilisé pour les affichages compacts dans les listes et emplois du temps.</small>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_niveau" id="niveau_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-niveau" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-niveau" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<script type="application/json" id="filieres-list-config">
<?= json_encode([
    'racine' => RACINE,
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/filieres_list.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>