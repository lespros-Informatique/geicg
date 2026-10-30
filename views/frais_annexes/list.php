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
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
          <?php if (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::PRINT_FRAIS_ANNEXES, PERMISSIONS::VIEW_FRAIS_ANNEXES, PERMISSIONS::MANAGE_FRAIS_ANNEXES, PERMISSIONS::PRINT_FRAIS_SCOLARITE, PERMISSIONS::VIEW_FRAIS_SCOLARITE]))): ?>
            <a href="<?= RACINE ?>fraisAnnexe/imprimerPdf" id="btn-print-frais" target="_blank" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer la Grille Tarifaire (PDF)
            </a>
          <?php endif; ?>
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
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
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
                <th style="padding: 12px;">Type de Filière</th>
                <th style="padding: 12px;">Niveau</th>
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
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 720px; box-shadow: 0 25px 35px -5px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-frais-annexe-title" style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Nouveau Tarification de Frais Annexes
      </h3>
      <button type="button" class="btn-close-modal-frais" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-frais-annexe" style="padding: 24px 28px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_frais_annexe" id="frais_annexe_id" value="">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 18px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Libellé du Tarif <span style="color: #EF4444;">*</span>
          </label>
          <input type="text" name="libelle_frais_annexe" id="frais_libelle" required placeholder="Ex: Pack Frais Annexes Filière Industrielle" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Montant Total des Frais Annexes (FCFA) <span style="color: #EF4444;">*</span>
          </label>
          <input type="number" min="0" step="any" name="montant_frais_annexe" id="frais_montant" required placeholder="Ex: 60000" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 15px; color: #15803D;">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 18px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Type de Filière <span style="color: #EF4444;">*</span>
          </label>
          <select name="type_filiere" id="frais_type_filiere" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="INDUSTRIELLE">Filière Industrielle (Ex: IDA, RIT...)</option>
            <option value="TERTIAIRE">Filière Tertiaire (Ex: RHCOM, GEC...)</option>
            <option value="TOUT">Toutes les Filières (Général)</option>
          </select>
        </div>

        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Niveau d'Étude
          </label>
          <select name="niveau_code" id="frais_niveau_code" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="TOUT">-- Tous les niveaux --</option>
            <?php foreach (($niveaux ?? []) as $n): ?>
              <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                <?= htmlspecialchars($n['libelle_niveau']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 24px;">
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Catégorie <span style="color: #EF4444;">*</span>
          </label>
          <select name="categorie_frais_annexe" id="frais_categorie" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="inscription">Inscription</option>
            <option value="autre">Autre</option>
          </select>
        </div>

        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
          <select name="statut_frais_annexe" id="frais_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-frais" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</button>
        <button type="submit" id="btn-save-frais" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<?php 
ViewContext::setScripts(['public/assets/js/modules/frais_annexes.js']);
require_once __DIR__ . '/../../public/inc/footer.php'; 
?>
