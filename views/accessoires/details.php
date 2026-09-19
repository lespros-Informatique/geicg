<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Accessoire / Dotation : <?= htmlspecialchars($item['libelle_accessoire'] ?? 'Article') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Kit scolaire, uniformes, insignes et fournitures officielles</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>accessoire/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux accessoires
          </a>
          <a href="<?= RACINE ?>accessoire/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier l'accessoire
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DE L'ACCESSOIRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="package" style="width: 18px; height: 18px;"></i> Caractéristiques & Tarification
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Désignation</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_accessoire'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_accessoire'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Public Cible (Filières)</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 6px;">
              <?php 
                $tf = $item['type_filiere_cible'] ?? 'TOUT';
                if ($tf === 'INDUSTRIELLE') echo '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 12px; border-radius:6px; font-weight:800;">Filière Industrielle</span>';
                elseif ($tf === 'TERTIAIRE') echo '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 12px; border-radius:6px; font-weight:800;">Filière Tertiaire</span>';
                else echo '<span class="badge" style="background:#F1F5F9; color:#475569; padding:4px 12px; border-radius:6px; font-weight:800;">Toutes Filières</span>';
              ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Attribution automatique selon la classe</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Statut</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_accessoire'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Disponible / Actif</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Indisponible</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
