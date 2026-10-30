<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Table d'Assignation Filières ↔ Cycles</h2>
          <a href="<?= RACINE ?>filiere_cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Assignation
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-assignations" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 12px;">ID</th>
                  <th style="padding: 12px;">Code</th>
                  <th style="padding: 12px;">Cycle D'Études</th>
                  <th style="padding: 12px;">Filière Associée</th>
                  <th style="padding: 12px;" class="text-center">Statut</th>
                  <th style="padding: 12px; text-align: right;">Actions</th>
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
            <a href="<?= RACINE ?>filiere/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter une Filière
            </a>
          </div>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-filieres-catalogue" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 12px;">ID</th>
                  <th style="padding: 12px;">Code</th>
                  <th style="padding: 12px;">Nom de la Filière</th>
                  <th style="padding: 12px;">Sigle / Slug</th>
                  <th style="padding: 12px;">Description</th>
                  <th style="padding: 12px;" class="text-center">Statut</th>
                  <th style="padding: 12px; text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENU DU TAB 3 : CYCLES D'ÉTUDES -->
      <div id="tab-cycles" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Référentiel des Cycles d'Études</h2>
          <a href="<?= RACINE ?>cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Cycle
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-cycles-list" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 12px;">ID</th>
                  <th style="padding: 12px;">Code Cycle</th>
                  <th style="padding: 12px;">Libellé du Cycle</th>
                  <th style="padding: 12px;">Slug</th>
                  <th style="padding: 12px;">Description</th>
                  <th style="padding: 12px;" class="text-center">Statut</th>
                  <th style="padding: 12px; text-align: right;">Actions</th>
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

<?php
$pageScripts = ['public/assets/js/modules/filiere_cycles_hub.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>