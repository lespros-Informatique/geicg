<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="file-check" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Pièces & Documents à Fournir
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Répertoire central des pièces administratives</p>
        </div>
        <?php if (isset($canAccess) && $canAccess([PERMISSIONS::MANAGE_PIECES, PERMISSIONS::CONFIG_ACADEMIQUE])): ?>
        <a href="<?= RACINE ?>piece_fournir/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Document
        </a>
        <?php endif; ?>
      </div>

      <!-- Navigation Tabs (Dossiers par Cycle vs Répertoire) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
        <a href="<?= RACINE ?>piece_fournir_cycle/list" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 17px; height: 17px;"></i> Dossiers Exigés par Cycle
        </a>
        <a href="<?= RACINE ?>piece_fournir/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="file-text" style="width: 17px; height: 17px;"></i> Répertoire des Pièces
        </a>
      </div>

      <!-- TABLE CARD -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-pieces-fournir" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Code</th>
                <th class="col-libelle" style="padding: 12px; min-width: 250px;">Libellé de la Pièce</th>
                <th class="col-desc" style="padding: 12px; min-width: 200px;">Description / Instructions</th>
                <th class="text-end" style="padding: 12px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<?php
$piecesFournirListConfig = [
  'canManagePieces' => (isset($canAccess) && $canAccess([PERMISSIONS::MANAGE_PIECES, PERMISSIONS::CONFIG_ACADEMIQUE]))
];
?>
<script type="application/json" id="pieces-fournir-list-config"><?= json_encode($piecesFournirListConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<?php 
ViewContext::setScripts(['public/assets/js/modules/accessoires.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>
