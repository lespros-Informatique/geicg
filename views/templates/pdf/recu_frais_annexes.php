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

$modeRaw = strtolower(trim($mode_paiement ?? 'espece'));
$modeLabels = [
    'espece' => 'Espèces (Guichet)',
    'especes' => 'Espèces (Guichet)',
    'mobile_money' => 'Mobile Money',
    'cheque' => 'Chèque Bancaire',
    'virement' => 'Virement Bancaire'
];
$modePaiementLabel = $modeLabels[$modeRaw] ?? ucfirst($modeRaw ?: 'Espèces');

$faOpJour = (float)($frais_annexes_op_du_jour ?? $montant_operation ?? 0);
$faTotalPayer = (float)($frais_annexes_total_payer ?? 0);
$faTotalVerse = (float)($frais_annexes_total_verse ?? $faOpJour);
$faRestePayer = (float)($frais_annexes_reste_payer ?? max(0, $faTotalPayer - $faTotalVerse));
$isSoldeFA = ($faTotalPayer > 0 && $faRestePayer <= 0);
$libelleFA = !empty($libelle_frais_annexe) ? $libelle_frais_annexe : 'Frais Annexes Obligatoires';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu de Frais Annexes - <?= htmlspecialchars($code_paiement ?? '') ?></title>
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
      border: 2px solid #92400E;
      padding: 10px 14px;
      box-sizing: border-box;
    }

    /* Bannières Titres Caisse Annexe */
    .banner-title {
      background: #1E3A5F;
      color: #FFFFFF;
      border-bottom: 3px solid #D97706;
      font-size: 17.5px;
      font-weight: bold;
      text-align: center;
      padding: 5px 6px;
      margin-top: 8px;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .banner-copy {
      background: #FEF3C7;
      color: #92400E;
      border: 1px solid #D97706;
      font-size: 12.5px;
      font-weight: bold;
      text-align: center;
      padding: 4px 6px;
      margin-top: 10px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .annee-header {
      font-size: 15px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 6px;
      color: #1E3A5F;
    }

    /* Grille d'Infos Étudiant */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .info-table td {
      padding: 2px 4px;
      vertical-align: top;
      font-size: 10.5px;
    }
    .photo-box {
      width: 80px;
      height: 90px;
      border: 1px solid #92400E;
      object-fit: cover;
      display: inline-block;
    }
    .val-bold {
      font-weight: bold;
    }
    .text-amber {
      color: #92400E;
      font-weight: bold;
    }
    .text-blue {
      color: #1E3A5F;
      font-weight: bold;
    }

    /* Tableau Financier Frais Annexes */
    .fin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
    }
    .fin-table th, .fin-table td {
      border: 1px solid #000000;
      padding: 4px 6px;
      font-size: 10px;
    }
    .fin-table th {
      background: #FEF3C7;
      color: #78350F;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .fin-table td.col-label {
      font-weight: bold;
      width: 28%;
    }
    .fin-table td.col-num {
      text-align: center;
      font-weight: bold;
      width: 18%;
    }
    .fin-table tr.total-row td {
      background: #E2E8F0;
      color: #0F172A;
      font-weight: bold;
      font-size: 10.5px;
    }

    /* Badges & Warning */
    .badge-solde-green {
      background: #15803D;
      color: #FFFFFF;
      font-size: 12px;
      font-weight: bold;
      padding: 3px 16px;
      display: inline-block;
      text-align: center;
    }
    .badge-solde-amber {
      background: #FEF3C7;
      color: #92400E;
      border: 1px solid #D97706;
      font-size: 11.5px;
      font-weight: bold;
      padding: 3px 14px;
      display: inline-block;
      text-align: center;
    }
    .warning-box {
      background: #FFFBEB;
      border: 1px solid #D97706;
      padding: 4px 8px;
      font-weight: bold;
      font-size: 9.5px;
      color: #78350F;
      margin-top: 6px;
      margin-bottom: 6px;
    }

    .gray-inline-box {
      background: #FEF3C7;
      border: 1px solid #F59E0B;
      display: inline-block;
      min-width: 55px;
      vertical-align: middle;
      padding: 1px 6px;
      color: #78350F;
      font-weight: bold;
    }

    /* Footer & Signatures */
    .footer-note {
      font-size: 9.5px;
      margin-top: 4px;
    }
    .signature-title {
      font-weight: bold;
      text-decoration: underline;
      font-size: 10px;
      color: #1E3A5F;
    }
    .signature-name {
      font-size: 10.5px;
      margin-top: 2px;
      font-weight: bold;
    }

    .dashed-sep {
      border-top: 1px dashed #92400E;
      margin-top: 8px;
      margin-bottom: 6px;
    }
    .ref-caiss {
      font-size: 9px;
      font-style: italic;
      color: #475569;
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

  <!-- BANNIÈRE 1 : REÇU DE RÈGLEMENT FRAIS ANNEXES -->
  <div class="banner-title">REÇU DE RÈGLEMENT — FRAIS ANNEXES </div>
  <div class="annee-header">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?? '-') ?></div>

  <!-- INFOS ÉTUDIANT (REÇU ORIGINAL) -->
  <table class="info-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 50%;">
              Quittance N° : <span class="val-bold text-amber"><?= htmlspecialchars($code_paiement ?? $code_inscription ?? '-') ?></span>
            </td>
            <td style="width: 50%; color: #475569;">Date : <span class="val-bold"><?= htmlspecialchars(!empty($date_operation) ? date('d/m/Y H:i', strtotime($date_operation)) : date('d/m/Y H:i')) ?></span></td>
          </tr>
          <tr>
            <td>Matricule Étudiant : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière / Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Nom & Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
            <td>Classe : <span class="val-bold"><?= htmlspecialchars($libelle_classe ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Nature de l'Opération : <span class="val-bold text-amber"><?= htmlspecialchars($libelleFA) ?></span></td>
            <td>Mode de Règlement : <span class="val-bold"><?= htmlspecialchars($modePaiementLabel) ?></span></td>
          </tr>
          <tr>
            <td>Montant Versé : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?> FCFA</span></td>
            <td class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <div style="display: inline-block; width: 80px; text-align: right;">
          <?php if (!empty($photo_etudiant)): ?>
            <img src="<?= $photo_etudiant ?>" width="80" height="90" class="photo-box" style="width: 110px; height: 110px; border: 1px solid #92400E; object-fit: cover; display: inline-block;">
          <?php else: ?>
            <div class="photo-box" style="width: 100px; height: 100px; border: 1px solid #92400E; background: #FFFBEB; text-align: center; line-height: 90px; color: #92400E; font-size: 9px; display: inline-block;">PHOTO</div>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>
  <br>

  <!-- TABLEAU FINANCIER EXCLUSIVEMENT DÉDIÉ AUX FRAIS ANNEXES (RÈGLE 4) -->
  <table class="fin-table">
    <thead>
      <tr>
        <th style="width: 28%;">RUBRIQUE CAISSE ANNEXE</th>
        <th style="width: 18%;">OP. DU JOUR</th>
        <th style="width: 18%;">Total Exigible</th>
        <th style="width: 18%;">Cumul Versé</th>
        <th style="width: 18%;">Reste à Payer</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="col-label"><?= htmlspecialchars($libelleFA) ?></td>
        <td class="col-num"><?= number_format($faOpJour, 0, ',', ' ') ?> FCFA</td>
        <td class="col-num"><?= number_format($faTotalPayer, 0, ',', ' ') ?> FCFA</td>
        <td class="col-num"><?= number_format($faTotalVerse, 0, ',', ' ') ?> FCFA</td>
        <td class="col-num"><?= number_format($faRestePayer, 0, ',', ' ') ?> FCFA</td>
      </tr>
      <tr class="total-row">
        <td class="col-label">TOTAL FRAIS ANNEXES</td>
        <td class="col-num"><?= number_format($faOpJour, 0, ',', ' ') ?> FCFA</td>
        <td class="col-num"><?= number_format($faTotalPayer, 0, ',', ' ') ?> FCFA</td>
        <td class="col-num"><?= number_format($faTotalVerse, 0, ',', ' ') ?> FCFA</td>
        <td class="col-num"><?= number_format($faRestePayer, 0, ',', ' ') ?> FCFA</td>
      </tr>
    </tbody>
  </table>
<br>
  <!-- STATUT FRAIS ANNEXES & SIGNATURE CAISSIER -->
  <table style="width: 100%; margin-top: 4px;">
    <tr>
      <td style="width: 60%;">
        Situation Frais Annexes :
        <?php if ($isSoldeFA): ?>
          <span class="badge-solde-green">FRAIS ANNEXES SOLDÉS</span>
        <?php else: ?>
          <span class="badge-solde-amber">RESTE À PAYER : <?= number_format($faRestePayer, 0, ',', ' ') ?> FCFA</span>
        <?php endif; ?>
        <?php if (!empty($observations)): ?>
          <div style="font-size: 9.5px; color: #475569; margin-top: 4px;">
            <strong>Motif / Note :</strong> <?= htmlspecialchars($observations) ?>
          </div>
        <?php endif; ?>
      </td>
      <td style="width: 40%; text-align: right; vertical-align: top;">
        <div class="signature-title">CAISSE ANNEXE / SERVICE FINANCIER</div>
        <div class="signature-name"><?= htmlspecialchars($caissier_nom ?? '') ?></div>
        <br>
        <?php if (!empty($barcodeVal)): ?>
          <div style="margin-top: 4px; text-align: right;">
            <div style="display: inline-block; text-align: center;">
              <?= generatePicqerBarcodeHtml($barcodeVal, 26, 1.0) ?>
              <div style="font-size: 8px; font-family: 'Courier New', monospace; font-weight: bold; margin-top: 1px;">* <?= htmlspecialchars($barcodeVal) ?> *</div>
            </div>
          </div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- AVERTISSEMENT N.B -->
  <div class="warning-box">
    <span style="text-decoration: underline;">N.B :</span> - Ce reçu atteste exclusivement du règlement des Frais Annexes (indépendants des frais de scolarité).<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Aucun remboursement n'est admis après encaissement. Conservez précieusement ce reçu pour le retrait des kits.
  </div>

  <table style="width: 100%;" class="footer-note">
    <tr>
      <td style="width: 70%;">Exemplaire Original Étudiant — À présenter pour toute réclamation ou retrait de fournitures.</td>
      <td style="width: 30%; text-align: right;"><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></td>
    </tr>
  </table>
  <br>

  <div class="dashed-sep"></div>
  <br>
  <br>
  <div class="ref-caiss"><?= !empty($ref_caiss) ? ('Réf_caisse_annexe ' . htmlspecialchars($ref_caiss)) : '' ?></div>

  <!-- ========================================================================= -->
  <!-- BANNIÈRE 2 : SOUCHE REÇU DE FRAIS ANNEXES POUR ARCHIVAGE                  -->
  <!-- ========================================================================= -->

  <div class="banner-copy">SOUCHE REÇU DE FRAIS ANNEXES  — ARCHIVAGE CAISSE ANNEXE</div>
  <div class="annee-header" style="font-size: 11px; margin-bottom: 4px;">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?? '-') ?></div>

  <table class="info-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 50%;">
              Quittance N° : <span class="val-bold text-amber"><?= htmlspecialchars($code_paiement ?? $code_inscription ?? '-') ?></span>
            </td>
            <td style="width: 50%; color: #475569;">Date : <span class="val-bold"><?= htmlspecialchars(!empty($date_operation) ? date('d/m/Y H:i', strtotime($date_operation)) : date('d/m/Y H:i')) ?></span></td>
          </tr>
          <tr>
            <td>Matricule : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière / Classe : <span class="val-bold"><?= htmlspecialchars(($libelle_classe ?? '') ?: ($filiere_niveau ?? '-')) ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom & Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>
              Mode : <span class="gray-inline-box"><?= htmlspecialchars($modePaiementLabel) ?></span>
            </td>
            <td>
              Cumul Versé : <span class="gray-inline-box"><?= number_format($faTotalVerse, 0, ',', ' ') ?> FCFA</span>
              &nbsp;&nbsp;
              Reste : <span class="gray-inline-box"><?= number_format($faRestePayer, 0, ',', ' ') ?> FCFA</span>
            </td>
          </tr>
          <tr>
            <td>Montant Versé : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?> FCFA</span></td>
            <td class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <div style="display: inline-block; width: 80px; text-align: right;">
          <?php if (!empty($photo_etudiant)): ?>
            <img src="<?= $photo_etudiant ?>" width="80" height="80" class="photo-box" style="width: 110px; height: 110px; border: 1px solid #92400E; object-fit: cover; display: inline-block;">
          <?php else: ?>
            <div class="photo-box" style="width: 110px; height: 110px; border: 1px solid #92400E; background: #FFFBEB; text-align: center; line-height: 80px; color: #92400E; font-size: 8.5px; display: inline-block;">PHOTO</div>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>
<br>
  <!-- STATUT & SIGNATURE COPIE -->
  <table style="width: 100%; margin-top: 4px;">
    <tr>
      <td style="width: 60%;">
        Situation Frais Annexes :
        <?php if ($isSoldeFA): ?>
          <span class="badge-solde-green">FRAIS ANNEXES SOLDÉS</span>
        <?php else: ?>
          <span class="badge-solde-amber">RESTE : <?= number_format($faRestePayer, 0, ',', ' ') ?> FCFA</span>
        <?php endif; ?>
      </td>
      <td style="width: 40%; text-align: right; vertical-align: top;">
        <div class="signature-title">CAISSE ANNEXE / SERVICE FINANCIER</div> <br>
        <div class="signature-name"><?= htmlspecialchars($caissier_nom ?? '') ?></div>
        <br>
        <?php if (!empty($barcodeVal)): ?>
          <div style="margin-top: 4px; text-align: right;">
            <div style="display: inline-block; text-align: center;">
              <?= generatePicqerBarcodeHtml($barcodeVal, 26, 1.0) ?>
              
              <div style="font-size: 8px; font-family: 'Courier New', monospace; font-weight: bold; margin-top: 1px;">
                * <?= htmlspecialchars($barcodeVal) ?> *</div>
            </div>
          </div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <table style="width: 100%; font-size: 9px; margin-top: 8px;">
    <tr>
      <td style="width: 70%; font-style: italic;"><?= !empty($ref_caiss) ? ('Réf_caisse_annexe ' . htmlspecialchars($ref_caiss)) : '' ?></td>
      <td style="width: 30%; text-align: right;"><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></td>
    </tr>
  </table>

</div>

</body>
</html>
