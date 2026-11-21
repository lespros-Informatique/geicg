<?php
// 1. Initialisation défensive de la configuration (Règle 9)
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } elseif (file_exists(__DIR__ . '/../../../models/configurations/ModelConfiguration.php')) {
        require_once __DIR__ . '/../../../models/configurations/ModelConfiguration.php';
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}

// Helper code-barres officiel Picqer & fallback natif mPDF
if (!function_exists('generatePicqerBarcodeHtml')) {
    function generatePicqerBarcodeHtml($code, $height = 26, $widthFactor = 1.0) {
        $code = (string)($code ?? '');
        if (empty($code)) return '';
        try {
            if (class_exists('\\Picqer\\Barcode\\BarcodeGeneratorPNG')) {
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                $pngData = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);
                return '<img src="data:image/png;base64,' . base64_encode($pngData) . '" alt="Code-barres ' . htmlspecialchars($code) . '" style="display:inline-block; vertical-align:middle; height:' . $height . 'px; width:auto; max-width:180px;">';
            }
        } catch (\Throwable $e) {}
        return '<barcode code="' . htmlspecialchars($code) . '" type="C128A" size="0.6" height="0.6" />';
    }
}
$barcodeVal = !empty($code_paiement) ? $code_paiement : (!empty($numero_recu) ? $numero_recu : ($code_barre_val ?? $ref_caiss ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu de Versement</title>
  <style>
    @page {
      margin: 8mm 8mm 8mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #000000;
      line-height: 1.2;
    }
    .page-frame {
      border: 2px solid #800000;
      padding: 10px 14px;
      box-sizing: border-box;
    }

    /* Header Institution */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .logo-box {
      width: 60px;
      height: 50px;
      background: #800000;
      color: #FFFFFF;
      text-align: center;
      font-weight: bold;
      font-size: 11px;
      padding-top: 4px;
      display: inline-block;
    }
    .inst-title {
      font-size: 13.5px;
      font-weight: bold;
      color: #800000;
      text-decoration: underline;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 11px;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 2px;
    }
    .inst-contacts {
      font-size: 10px;
      font-style: italic;
      text-align: center;
      color: #000000;
      margin-top: 1px;
    }

    /* Bannières Titres */
    .banner-title {
      background-color: #999999;
      color: #000000;
      text-align: center;
      padding: 6px 0;
      margin: 10px 0 15px 0;
      font-weight: bold;
      font-size: 16.5pt;
      font-family: 'arial bold';
      letter-spacing: 1.2px;
      text-transform: uppercase;
    }
    .banner-copy {
      background-color: #999999;
      color: #000000;
      text-align: center;
      padding: 6px 0;
      margin: 10px 0 15px 0;
      font-weight: bold;
      font-size: 12.5pt;
      font-family: 'arial bold';
      letter-spacing: 1.2px;
      text-transform: uppercase;
    }

    .annee-header {
       text-align: center;
      font-weight: bold;
      font-size: 10.5pt;
      margin-bottom: 8px;
      color: #000000;
    }

    /* Grille d'Infos Étudiant */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .info-table td {
      padding: 3px 4px 3px 0;
      vertical-align: middle;
      font-size: 9.6pt;
      line-height: 1.5;
    }
    .photo-box {
      width: 120px;
      height: 130px;
      border: 1px solid #000000;
      object-fit: cover;
      display: inline-block;
    }
    .val-bold {
      font-weight: bold;
    }
    .text-blue {
      color: #172f43ff;
      font-weight: bold;
    }

    /* Zone Solde & Code-barres / Caissier (hors table pour mPDF) */
    .bottom-status-row {
      width: 100%;
      margin-top: 35px;
      margin-bottom: 6px;
    }
    .bottom-status-left {
      width: 58%;
      float: left;
      padding-top: 4px;
    }
    .bottom-status-right {
      width: 210px;
      float: right;
      text-align: center;
    }
    .solde-inline-table {
      border-collapse: collapse;
    }
    .solde-label-cell {
      font-size: 9.2pt;
      padding-right: 12px;
      vertical-align: middle;
    }
    .solde-badge-cell {
      background-color: #999999;
      color: #ea0a0aff;
      font-weight: bold;
      font-size: 10.5pt;
      padding: 4px 14px;
      text-align: center;
      vertical-align: middle;
      letter-spacing: 0.6px;
    }
    .barcode-box {
      text-align: center;
      margin-bottom: 6px;
    }

    /* Tableau Financier */
    .fin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
      
    }
    .fin-table th, .fin-table td {
      border: 1px solid #000000;
      padding: 3px 5px;
      font-size: 9.5pt;
    }
    .fin-table th {
      background: #FFFFFF;
      font-weight: bold;
      text-align: center;
    }
    .fin-table td.col-label {
      font-weight: bold;
      width: 25%;
    }
    .fin-table td.col-num {
      text-align: center;
      font-weight: bold;
      width: 18.75%;
    }
    .fin-table td.cell-black {
      background: #000000;
    }
    .fin-table tr.total-row td {
      background: #A6A6A6;
      color: #000000;
      font-weight: bold;
      font-size: 11px;
    }

    /* Badges & Warning */
    .badge-solde-red {
      background: #FF0000;
      color: #FFFFFF;
      font-size: 13px;
      font-weight: bold;
      padding: 3px 18px;
      display: inline-block;
      text-align: center;
    }
    .badge-solde-gray {
      background: #A6A6A6;
      color: #000000;
      font-size: 12px;
      font-weight: bold;
      padding: 3px 18px;
      display: inline-block;
      text-align: center;
    }
    .warning-box {
     

      background-color: #e4e42cff;
      border: 1px solid #000000;
      padding: 3px 6px;
      font-weight: bold;
      font-size: 8.5pt;
      font-style: italic;
      color: #000000;
      margin: 8px 0 8px 0;
    }

    /* Gray boxes for copy section */
    .gray-inline-box {
      background: #A6A6A6;
      display: inline-block;
      min-width: 60px;
      height: 14px;
      vertical-align: middle;
      padding: 1px 6px;
      color: #000000;
      font-weight: bold;
    }

    /* Footer & Signatures */
    .footer-note {
      font-size: 8.5pt;
      color: #000000;
      font-style: italic;
      margin-bottom: 20px;
    }
    .signature-area {
      text-align: right;
    }
    .signature-title {
      font-weight: bold;
      text-decoration: underline;
      font-size: 9pt;
      text-align: center;
      margin-top: 4px;
      margin-bottom: 2px;
    }
    .signature-name {
      font-size: 8.8pt;
      font-weight: bold;
      text-align: center;
      margin-top: 2px;
    }

    .dashed-sep {
      border-top: 1px dashed #000000;
      margin-top: 8px;
      margin-bottom: 6px;
    }
    .ref-caiss {
      font-size: 10.5pt;
      font-style: italic;
      text-align: center;
      margin-top: 15px;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG (MODE COMPACT) -->
  <?php 
    $headerMode = 'compact';
    $showHeaderDivider = false;
    include __DIR__ . '/components/header_institution.php'; 
  ?>
<hr>
  <!-- BANNIÈRE 1 : REÇU DE VERSEMENT -->
  <div class="banner-title">REÇU DE VERSEMENT</div>
  <div class="annee-header">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '-') ?></div>

  <!-- INFOS ÉTUDIANT (REÇU ORIGINAL) -->
  <table class="info-table">
    <tr>
      <td style="width: 76%; vertical-align: top;">
        <table style="width: 100%; border-collapse: collapse;">
          <tr>
            <td style="width: 52%;">
              N° : &nbsp;<span class="val-bold"><?= htmlspecialchars($code_paiement ?? $code_inscription ?? '-') ?></span>
            </td>
            <?php
              $dateOpDisplay = date('d/m/Y');
              if (!empty($date_operation_raw)) {
                  $tsOp = strtotime((string)$date_operation_raw);
                  if ($tsOp !== false) $dateOpDisplay = date('d/m/Y H:i:s', $tsOp);
              } elseif (!empty($date_operation)) {
                  $dateOpDisplay = trim((string)$date_operation);
              }
            ?>
            <td style="width: 48%;">Date : &nbsp;<span class="val-bold"><?= htmlspecialchars($dateOpDisplay) ?></span></td>
          </tr>
          <tr>
            <td>Matricule : &nbsp;<span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière / Niveau : &nbsp;<span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom &amp; Prénoms(s) : &nbsp;<span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Type d'Opération : &nbsp;<span class="val-bold"><?= htmlspecialchars($type_operation ?? '-') ?></span></td>
            <td>Statut : &nbsp;<span class="val-bold"><?= htmlspecialchars($statut_affectation ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Montant de l'Opération : &nbsp;<span class="val-bold">(<?= number_format($montant_operation ?? 0, 0, ',', ' ') ?> CFA)</span> &nbsp;<span class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 24%; text-align: right; vertical-align: top;">
        <div style="display: inline-block; width: 120px; text-align: right;">
          <?php if (!empty($photo_etudiant)): ?>
            <img src="<?= $photo_etudiant ?>" width="120" height="130" class="photo-box" style="width: 120px; height: 130px; border: 1px solid #000000; object-fit: cover; display: inline-block;">
          <?php else: ?>
            <div class="photo-box" style="width: 120px; height: 130px; border: 1px solid #000000; background: #F1F5F9; text-align: center; line-height: 126px; color: #94A3B8; font-size: 9px; display: inline-block;">PHOTO</div>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>
<br>
  <!-- TABLEAU FINANCIER DÉTAILLÉ -->
  <table class="fin-table">
    <thead>
      <tr>
        <th style="width: 25%;"></th>
        <th style="width: 18.75%;">OP. DU JOUR</th>
        <th style="width: 18.75%;">Total à payer</th>
        <th style="width: 18.75%;">Total Versé</th>
        <th style="width: 18.75%;">Reste à Payer</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="col-label">SCOLARITE</td>
        <td class="col-num"><?= isset($scolarite_op_du_jour) && $scolarite_op_du_jour > 0 ? number_format($scolarite_op_du_jour, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"><?= isset($scolarite_total_payer) ? number_format($scolarite_total_payer, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"><?= isset($scolarite_total_verse) ? number_format($scolarite_total_verse, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"><?= isset($scolarite_reste_payer) ? number_format($scolarite_reste_payer, 0, ',', ' ') . 'CFA' : '0CFA' ?></td>
      </tr>
      <tr>
        <td class="col-label">Droit d'Inscription</td>
        <td class="col-num"><?= isset($droit_op_du_jour) && $droit_op_du_jour > 0 ? number_format($droit_op_du_jour, 0, ',', ' ') . 'CFA' : '' ?></td>
        <?php
          $droitPayerVal = $droit_total_payer ?? ($droit_tot_payer ?? 0);
          $droitVerseVal = $droit_total_verse ?? ($droit_tot_verse ?? 0);
          $droitResteVal = $droit_reste_payer ?? max(0, (float)$droitPayerVal - (float)$droitVerseVal);
        ?>
        <?php if ((float)$droitPayerVal > 0): ?>
          <td class="col-num"><?= number_format($droitPayerVal, 0, ',', ' ') ?>CFA</td>
          <td class="col-num"><?= number_format($droitVerseVal, 0, ',', ' ') ?>CFA</td>
          <td class="col-num"><?= number_format($droitResteVal, 0, ',', ' ') ?>CFA</td>
        <?php else: ?>
          <td class="col-num cell-black"></td>
          <td class="col-num cell-black"></td>
          <td class="col-num cell-black"></td>
        <?php endif; ?>
      </tr>
      <tr>
        <td class="col-label">Versement</td>
        <td class="col-num"><?= number_format($versement_op_du_jour ?? $montant_operation ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num"></td>
        <td class="col-num"></td>
        <td class="col-num"></td>
      </tr>
      <tr class="total-row">
        <td class="col-label">TOTAL</td>
        <td class="col-num"><?= number_format($total_op_du_jour ?? $montant_operation ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num"><?= number_format($total_payer ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num"><?= number_format($total_verse ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num"><?= number_format($total_reste_payer ?? 0, 0, ',', ' ') ?>CFA</td>
      </tr>
    </tbody>
  </table>

  <!-- STATUT PROCHAIN PAIEMENT & SIGNATURE CAISSIER -->
  <?php 
    $resteScolDisplay = (float)($total_reste_payer ?? ($scolarite_reste_payer ?? 0));
    $badgeText = ($resteScolDisplay <= 0) ? 'SOLDÉ' : ('RESTE : ' . number_format($resteScolDisplay, 0, ',', ' ') . ' CFA');
  ?>
  <div class="bottom-status-row">
    <div class="bottom-status-left">
      <table class="solde-inline-table">
        <tr>
          <td class="solde-label-cell">Date du Prochain Payement :</td>
          <td class="solde-badge-cell"><?= $badgeText ?></td>
        </tr>
      </table>
    </div>
    <div class="bottom-status-right">
      <?php if (!empty($barcodeVal)): ?>
        <div class="barcode-box">
          <?= generatePicqerBarcodeHtml($barcodeVal, 32, 1.25) ?>
        </div>
      <?php endif; ?>
      <div class="signature-title">CAISSIER(RE)</div>
      <div class="signature-name"><?= htmlspecialchars($caissier_nom ?? '') ?></div>
    </div>
    <div style="clear: both;"></div>
  </div>

  <!-- AVERTISSEMENT N.B -->
  <div class="warning-box">
    <span style="text-decoration: underline;">N.B:</span> - Aucun remboursement n'est admis après l'inscription.<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Les paiements se font uniquement chez la caissière
  </div>

  <table style="width: 100%;" class="footer-note">
    <tr>
      <td style="width: 70%;">Ce reçu est à conserver. Pour toute réclamation il doit être présenté.</td>
      <td style="width: 30%; text-align: right;"><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></td>
    </tr>
  </table>

  <div class="dashed-sep"></div>
  <div class="ref-caiss"><?= !empty($ref_caiss) ? ('Réf_caiss ' . htmlspecialchars($ref_caiss)) : '' ?></div>

  <!-- ========================================================================= -->
  <!-- BANNIÈRE 2 : COPIE REÇU POUR ARCHIVAGE                                      -->
  <!-- ========================================================================= -->
  <?php 
    $numVersementText = isset($num_versement) ? ($num_versement . ($num_versement == 1 ? 'er' : 'ème')) : '1er';
  ?>
  <div class="banner-copy">COPIE REÇU DU <?= htmlspecialchars($numVersementText) ?> VERSEMENT POUR ARCHIVAGE</div>
  <div class="annee-header">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '-') ?></div>

  <table class="info-table">
    <tr>
      <td style="width: 76%; vertical-align: top;">
        <table style="width: 100%; border-collapse: collapse;">
          <tr>
            <td style="width: 52%;">
              N° : &nbsp;<span class="val-bold"><?= htmlspecialchars($code_paiement ?? $code_inscription ?? '-') ?></span>
            </td>
            <td style="width: 48%;">Date : &nbsp;<span class="val-bold"><?= htmlspecialchars($dateOpDisplay) ?></span></td>
          </tr>
          <tr>
            <td>Matricule : &nbsp;<span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière / Niveau : &nbsp;<span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom &amp; Prénoms(s) : &nbsp;<span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">
              Type d'Opération : &nbsp;<span style="background:#CCCCCC; padding:1px 6px; font-weight:bold;"><?= htmlspecialchars($type_operation ?? '-') ?></span>
            </td>
          </tr>
          <tr>
            <td colspan="2">Montant de l'Opération : &nbsp;<span class="val-bold">(<?= number_format($montant_operation ?? 0, 0, ',', ' ') ?> CFA)</span> &nbsp;<span class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 24%; text-align: right; vertical-align: top;">
        <div style="display: inline-block; width: 120px; text-align: right;">
          <?php if (!empty($photo_etudiant)): ?>
            <img src="<?= $photo_etudiant ?>" width="120" height="130" class="photo-box" style="width: 120px; height: 130px; border: 1px solid #000000; object-fit: cover; display: inline-block;">
          <?php else: ?>
            <div class="photo-box" style="width: 120px; height: 130px; border: 1px solid #000000; background: #F1F5F9; text-align: center; line-height: 126px; color: #94A3B8; font-size: 8.5px; display: inline-block;">PHOTO</div>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- STATUT PROCHAIN PAIEMENT & SIGNATURE COPIE -->
  <div class="bottom-status-row">
    <div class="bottom-status-left">
      <table class="solde-inline-table">
        <tr>
          <td class="solde-label-cell">Date du Prochain Payement :</td>
          <td class="solde-badge-cell"><?= $badgeText ?></td>
        </tr>
      </table>
    </div>
    <div class="bottom-status-right">
      <?php if (!empty($barcodeVal)): ?>
        <div class="barcode-box">
          <?= generatePicqerBarcodeHtml($barcodeVal, 32, 1.25) ?>
        </div>
      <?php endif; ?>
      <div class="signature-title">CAISSIER(RE)</div>
      <div class="signature-name"><?= htmlspecialchars($caissier_nom ?? '') ?></div>
    </div>
    <div style="clear: both;"></div>
  </div>


</div>

</body>
</html>

