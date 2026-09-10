<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$montant = (float)($item['montant_depense'] ?? ($item['montant'] ?? 0));
?>
<style>
@media print {
  body { background: #fff !important; color: #000 !important; }
  .sidebar, .nav-header, .page-header-actions, .no-print { display: none !important; }
  .app-layout { display: block !important; }
  .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  .content-wrapper { padding: 0 !important; }
  .card-bon { border: 1px dashed #000 !important; box-shadow: none !important; }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Bon de Décaissement / Dépense N° <?= htmlspecialchars($item['code_depense'] ?? ('DEP-' . $item['id_depense'])) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Bénéficiaire : <strong><?= htmlspecialchars($item['beneficiaire_depense'] ?? '-') ?></strong> &bull; Ligne : <strong><?= htmlspecialchars($item['libelle_type_depense'] ?? '-') ?></strong></p>
        </div>
        <div class="page-header-actions" style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>depense/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux dépenses
          </a>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Bon
          </button>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : BON DE DÉCAISSEMENT OFFICIEL -->
      <div class="card card-bon" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        
        <!-- En-tête du Bon -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #1E3A5F; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; border-radius: 10px; background: #DC2626; color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="file-minus" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; text-transform: uppercase;">INSTITUT SUPÉRIEUR GEICG</h2>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Direction Financière & Comptable &bull; Bon de Sortie de Caisse</p>
            </div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 16px; font-weight: 800; color: #DC2626; font-family: monospace;">
              PIÈCE DE SORTIE N° <?= htmlspecialchars($item['code_depense'] ?? ('DEP-' . $item['id_depense'])) ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
              Date : <?= !empty($item['date_depense']) ? date('d/m/Y', strtotime($item['date_depense'])) : date('d/m/Y') ?>
            </div>
          </div>
        </div>

        <!-- Détails de la Dépense -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
          <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #991B1B; text-transform: uppercase;">Montant Décaissé</span>
            <div style="font-size: 26px; font-weight: 900; color: #DC2626; margin-top: 4px;"><?= number_format($montant, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Mode : <?= htmlspecialchars($item['mode_paiement_depense'] ?? ($item['mode_reglement'] ?? 'Espèces')) ?></div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Bénéficiaire des Fonds</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['beneficiaire_depense'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Ligne : <?= htmlspecialchars($item['libelle_type_depense'] ?? 'Générale') ?></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Ordonnateur / Enregistré par</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? 'Comptabilité')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Année : <?= htmlspecialchars($item['libelle_annee'] ?? 'En cours') ?></div>
          </div>
        </div>

        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 4px;">Motif / Justification de la charge :</span>
          <div style="font-size: 14px; color: #0F172A; font-weight: 600; line-height: 1.5;">
            <?= htmlspecialchars($item['motif_depense'] ?? ($item['description_depense'] ?? '-')) ?>
          </div>
        </div>

        <!-- Signatures & Validation -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 50px;">Le Bénéficiaire (Émargement)</div>
            <div style="font-size: 11px; color: #94A3B8; font-style: italic;">Pour acquit des fonds reçus</div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 50px;">Le Responsable Financier / Direction</div>
            <div style="font-size: 11px; color: #94A3B8; font-style: italic;">Bon à payer & Visa de sortie</div>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
