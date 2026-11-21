<?php
/**
 * Template Officiel du Reçu d'Inscription (Modèle GROUPE EICG 100% Conforme)
 * Emplacement : /views/templates/pdf/recu_inscription.php
 */

if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}

// Générateur de code-barres compact et haut via Picqer Barcode Generator
if (!function_exists('generatePicqerBarcodeHtml')) {
    function generatePicqerBarcodeHtml($code, $height = 42, $widthFactor = 1) {
        $code = (string)($code ?? '');
        if (empty($code)) return '';
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $wf = max(1, (int)round((float)$widthFactor));
            $pngData = $generator->getBarcode($code, $generator::TYPE_CODE_128, $wf, $height);
            return '<img src="data:image/png;base64,' . base64_encode($pngData) . '" alt="Code-barres ' . htmlspecialchars($code) . '" style="display:inline-block; vertical-align:middle; height:' . $height . 'px; width:auto; max-width:150px;">';
        } catch (\Throwable $e) {
            return '<div style="font-family:monospace; font-weight:bold; font-size:10px;">' . htmlspecialchars($code) . '</div>';
        }
    }
}

// Chargement du logo institutionnel depuis la table configurations
if (!empty($logo_src)) {
    $logoSrc = $logo_src;
} else {
    $logoSrc = '';
    if (!empty($etablissement['logo_etablissement'])) {
        $cleanLogo = ltrim($etablissement['logo_etablissement'], '/');
        foreach ([__DIR__ . '/../../../public/' . $cleanLogo, __DIR__ . '/../../../' . $cleanLogo] as $candLogo) {
            if (file_exists($candLogo) && is_file($candLogo)) {
                $extL = strtolower(pathinfo($candLogo, PATHINFO_EXTENSION));
                $mimeL = ($extL === 'png') ? 'png' : (($extL === 'webp') ? 'webp' : 'jpeg');
                $logoSrc = 'data:image/' . $mimeL . ';base64,' . base64_encode(file_get_contents($candLogo));
                break;
            }
        }
    }
}

// Chargement de la photo étudiant ou placeholder par défaut
if (!empty($photo_etudiant)) {
    $photo_src = $photo_etudiant;
} else if (empty($photo_src)) {
    $placeholderPhotoFile = placeholderPath('etudiant');
    if (file_exists($placeholderPhotoFile)) {
        $photo_src = 'data:image/png;base64,' . base64_encode(file_get_contents($placeholderPhotoFile));
    }
}

// Données dynamiques
$annee_universitaire = $annee_universitaire ?? '';
$numero_recu = $numero_recu ?? '';
$date_operation = $date_operation ?? '';
$matricule = $matricule ?? '';
$nom_prenoms = $nom_prenoms ?? '';
$filiere_niveau = $filiere_niveau ?? '';
$statut_etudiant = $statut_etudiant ?? '';
$type_operation = $type_operation ?? '';
$montant_operation = (float)($montant_operation ?? 0);
$montant_operation_formatted = $montant_operation_formatted ?? ($montant_operation > 0 ? number_format($montant_operation, 0, ',', ' ') . '  FCFA' : '0 FCFA');
$montant_en_lettres = $montant_en_lettres ?? '';
$scolarite_total = (float)($scolarite_total ?? 0);
$total_verse = (float)($total_verse ?? 0);
$reste_a_payer = (float)($reste_a_payer ?? 0);
$caissier_nom = $caissier_nom ?? '';
$date_impression = $date_impression ?? date('d/m/Y H:i:s');
$code_barre_val = $code_barre_val ?? $numero_recu;

// Montants lignes du tableau
$premiere_tranche = (float)($premiere_tranche ?? 0);
$frais_annexes_inscription = (float)($frais_annexes_inscription ?? 0);
$droit_tot_payer = (float)($droit_tot_payer ?? ($premiere_tranche + $frais_annexes_inscription));

$droit_op_jour = (float)($droit_op_jour ?? 0);
$droit_tot_verse = (float)($droit_tot_verse ?? 0);

$scolarite_op_jour = (float)($scolarite_op_jour ?? max(0, $montant_operation - $droit_op_jour));
$scolarite_tot_payer = (float)($scolarite_tot_payer ?? $scolarite_total);
$scolarite_tot_verse = (float)($scolarite_tot_verse ?? max(0, $total_verse - $droit_tot_verse));
$scolarite_reste = (float)($scolarite_reste ?? max(0, $scolarite_tot_payer - $scolarite_tot_verse));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu d'Inscription - <?= htmlspecialchars($nom_prenoms) ?></title>
  <style>
    @page {
      margin: 6mm 10mm 6mm 10mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 8.5pt;
      color: #000000;
      line-height: 1.25;
      background-color: #FFFFFF;
      margin: 0 auto;
      padding: 0;
    }

    .main-wrapper {
      border: 1.5px solid #400303ff;
      padding: 6px 25px;
      box-sizing: border-box;
      background: #FFFFFF;
      margin: 0 auto;
    }

    /* EN-TÊTE ÉTABLISSEMENT */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2px;
    }
    .header-logo-cell {
      width: 95px;
      vertical-align: top;
    }
    .header-logo-img {
      max-height: 48px;
      width: auto;
      max-width: 90px;
    }
    .header-text-cell {
      text-align: center;
      vertical-align: top;
    }
    .header-title {
      color: #800000;
      font-family: Arial, sans-serif;
      font-weight: bold;
      font-size: 11.5pt;
      text-transform: uppercase;
      margin: 0 0 8px 0;
      text-decoration: underline;
      letter-spacing: 0.2px;
    }
    .header-subtitle-1 {
      font-weight: bold;
      font-style: italic;
      font-size: 8.5pt;
      margin-top: 8px;
      margin-bottom: 5px;
      color: #000000;
    }
    .header-subtitle-2 {
      font-weight: bold;
      font-style: italic;
      font-size: 8.5pt;
      margin-top: 5px;
      margin-bottom: 3px;
      color: #000000;
    }

    /* BANNIÈRES GRISES DE TITRE */
    .banner-title-box {
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

    .annee-subbanner {
      text-align: center;
      font-weight: bold;
      font-size: 10.5pt;
      margin-bottom: 8px;
      color: #000000;
    }

    /* FORMULAIRE DONNÉES ÉTUDIANT & PHOTO */
    .info-photo-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .info-left-cell {
      vertical-align: top;
      width: 75%;
    }
    .photo-right-cell {
      vertical-align: top;
      text-align: right;
      width: 125px;
    }
    .photo-frame {
      width: 120px;
      height: 130px;
     /* border: 2px solid #000000;*/
      background: #F1F5F9;
      display: inline-block;
      box-sizing: border-box;
      vertical-align: top;
      text-align: center;
      overflow: hidden;
      margin-bottom: 8px;
    }
    .photo-img {
      width: 120px;
      height: 130px;
      border: 1px solid #000000;
      object-fit: cover;
      display: block;
    }
    .photo-inits {
      width: 100%;
      height: 100%;
      line-height: 126px;
      text-align: center;
      font-weight: bold;
      font-size: 26pt;
      color: #000000;
      background: #F1F5F9;
    }

    .student-info-grid {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.8pt;
      line-height: 1.55;
    }
    .student-info-grid td {
      padding: 3.5px 4px 3.5px 0;
      vertical-align: middle;
    }
    .val-bold {
      font-weight: bold;
      color: #000000;
    }
    .amount-words-blue {
      color: #172f43ff;
      font-weight: bold;
      font-size: 9.8pt;
    }

    /* TABLEAU FINANCIER DES RÈGLEMENTS */
    .grid-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 3px;
      margin-bottom: 4px;
      font-size: 9.2pt;

    }
    .grid-table th {
      border: 1px solid #000000;
      /* font-size: 8pt;*/
      /* font-weight: bold; */
      color: #000000;
      padding: 3px 4px;
      text-align: center;
      background: #FFFFFF;
    }
    .grid-table td {
      border: 1px solid #000000;
      /* font-size: 8pt; */
      padding: 2.5px 4px;
      color: #000000;
    }
    .row-total td {
      font-family: 'arial bold';
      background-color: #999999;
      color: #000000;
      font-weight: bold;
      font-size: 9.5pt;
    }
    .cell-black {
      background-color: #000000;
    }
    .text-center { text-align: center; }
    .text-bold { font-weight: bold; }

    /* ZONE SOLDE & CODE-BARRES / CAISSIER (hors table pour que mPDF respecte margin/padding/alignement) */
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
      padding-right: 14px;
      vertical-align: middle;
    }
    .solde-badge-cell {
      background-color: #999999;
      color: #ea0a0aff;
      font-weight: 900;
      font-size: 11pt;
      padding: 5px 16px;
      text-align: center;
      vertical-align: middle;
      letter-spacing: 0.8px;
    }
    .barcode-box {
      text-align: center;
      margin-bottom: 8px;
    }
    .caissier-title {
      font-weight: bold;
      text-decoration: underline;
      font-size: 9pt;
      text-align: center;
      margin-top: 6px;
      margin-bottom: 3px;
    }
    .caissier-name {
      font-size: 8.8pt;
      font-weight: bold;
      color: #000000;
      text-align: center;
    }

    /* ENCADRÉ JAUNE ATTENTION */
    .nb-yellow-box {
      background-color: #e4e42cff;
      border: 1px solid #000000;
      padding: 3px 6px;
      font-weight: bold;
      font-size: 8.5pt;
      font-style: italic;
      color: #000000;
      margin: 8px 0 8px 0;
    }

    .notice-row-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5pt;
      color: #000000;
      font-style: italic;
      margin-bottom: 20px;
    }

    /* DIVISEUR POINTILLÉ ENTRE LES DEUX PARTIES */
    .dashed-divider-zone {
      text-align: center;
      margin: 4px 0 4px 0;
    }
    .dashed-line {
      border-top: 1px dashed #000000;
      margin-bottom: 35px;
    }
    .ref-caiss-text {
      font-size: 7.5pt;
      font-family: monospace;
      font-weight: bold;
      color: #000000;
    }

    @media print {
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>



  <?php
  $initsPDF = '?';
  if (!empty($nom_prenoms)) {
      $pParts = array_values(array_filter(explode(' ', trim($nom_prenoms))));
      if (count($pParts) >= 2) {
          $initsPDF = strtoupper(substr($pParts[0], 0, 1) . substr($pParts[1], 0, 1));
      } else if (!empty($pParts)) {
          $initsPDF = strtoupper(substr($pParts[0], 0, 2));
      }
  }
  ?>
  <div class="main-wrapper">

    <!-- ========================================================================= -->
    <!-- 1. PARTIE SUPERIEURE : REÇU D'INSCRIPTION ETUDIANT -->
    <!-- ========================================================================= -->

    <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG (MODE COMPACT) -->
    <?php 
      $headerMode = 'compact';
      $showHeaderDivider = false;
      include __DIR__ . '/components/header_institution.php'; 
    ?>
<hr>
    <!-- BANNIÈRE PRINCIPALE ET ANNÉE -->
    <div class="banner-title-box">REÇU D'INSCRIPTION</div>
    <div class="annee-subbanner">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_universitaire) ?></div>

    <!-- DONNÉES ÉTUDIANT & PHOTO -->
    <table class="info-photo-table">
      <tr>
        <td class="info-left-cell">
          <table class="student-info-grid">
            <tr>
              <td style="width: 54%;">N° : &nbsp;<span class="val-bold"><?= htmlspecialchars($numero_recu) ?></span></td>
              <td style="width: 46%;">Date : &nbsp;<span class="val-bold"><?= htmlspecialchars($date_operation) ?></span></td>
            </tr>
            <tr>
              <td>Matricule : &nbsp;<span class="val-bold"><?= htmlspecialchars($matricule) ?></span></td>
              <td>Filière / Niveau : &nbsp;<span class="val-bold"><?= htmlspecialchars($filiere_niveau) ?></span></td>
            </tr>
            <tr>
              <td colspan="2">Nom & Prénoms(s) : &nbsp;<span class="val-bold"><?= htmlspecialchars($nom_prenoms) ?></span></td>
            </tr>
            <tr>
              <td>Type d'Opération : &nbsp;<span class="val-bold">Frais d'inscription</span></td>
              <td>Statut : &nbsp;<span class="val-bold"><?= htmlspecialchars($statut_etudiant) ?></span></td>
            </tr>
            <tr>
              <td colspan="2">Montant de l'Opération : &nbsp;<span class="val-bold">(<?= htmlspecialchars($montant_operation_formatted) ?>)</span> &nbsp;<span class="amount-words-blue"><?= htmlspecialchars($montant_en_lettres) ?></span></td>
            </tr>
          </table>
        </td>
        <td class="photo-right-cell">
          <div class="photo-frame">
            <?php if (!empty($photo_src)): ?>
              <img src="<?= $photo_src ?>" alt="Photo Étudiant" class="photo-img">
            <?php else: ?>
              <div class="photo-inits"><?= htmlspecialchars($initsPDF) ?></div>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    </table>
<br>
    <!-- TABLEAU FINANCIER DES RÈGLEMENTS -->
    <table class="grid-table">
      <thead>
        <tr>
          <th style="width: 25%;"></th>
          <th style="width: 18%;">OP. DU JOUR</th>
          <th style="width: 20%;">Total à payer</th>
          <th style="width: 18%;">Total Versé</th>
          <th style="width: 19%;">Reste à Payer</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="">SCOLARITE</td>
          <td class="text-center text-bold"><?= number_format($scolarite_op_jour, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_tot_payer, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_tot_verse, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_reste, 0, ',', ' ') ?>CFA</td>
        </tr>
        <tr>
          <td class="">Droit d'Inscription</td>
          <td class="text-center text-bold"><?= $droit_op_jour > 0 ? number_format($droit_op_jour, 0, ',', ' ') . 'CFA' : '' ?></td>
          <?php if ($droit_tot_payer > 0): ?>
            <td class="text-center text-bold"><?= number_format($droit_tot_payer, 0, ',', ' ') ?>CFA</td>
            <td class="text-center text-bold"><?= number_format($droit_tot_verse, 0, ',', ' ') ?>CFA</td>
            <td class="cell-black"></td>
          <?php else: ?>
            <td class="cell-black"></td>
            <td class="cell-black"></td>
            <td class="cell-black"></td>
          <?php endif; ?>
        </tr>
        <tr>
          <td class="">AUTRES FRAIS</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr class="row-total">
          <td class="text-bold">TOTAL</td>
          <td class="text-center text-bold"><?= number_format($montant_operation, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_total, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($total_verse, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($reste_a_payer, 0, ',', ' ') ?>CFA</td>
        </tr>
      </tbody>
    </table>
  

    <!-- ZONE SOLDE & CODE-BARRES / CAISSIER -->
    <div class="bottom-status-row">
      <div class="bottom-status-left">
        <table class="solde-inline-table">
          <tr>
            <td class="solde-label-cell">Date du Prochain Payement :</td>
            <td class="solde-badge-cell"><?= $reste_a_payer <= 0 ? 'SOLDÉ' : ('RESTE : ' . number_format($reste_a_payer, 0, ',', ' ') . ' CFA') ?></td>
          </tr>
        </table>
      </div>
      <div class="bottom-status-right">
        <div class="barcode-box">
          <?= generatePicqerBarcodeHtml($code_barre_val, 45, 1.35) ?>
        </div>
        <div class="caissier-title">CAISSIER(RE)</div>
        <div class="caissier-name"><?= htmlspecialchars($caissier_nom) ?></div>
      </div>
      <div style="clear: both;"></div>
    </div>

    <!-- ENCADRÉ JAUNE N.B & AVIS DE CONSERVATION -->
    <div class="nb-yellow-box">
      N.B: - Aucun remboursement n'est admis après l'inscription.<br>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - Les paiements se font uniquement chez la caissière
    </div>

    <table class="notice-row-table">
      <tr>
        <td style="font-weight: bold;">Ce reçu est à conserver. Pour toute réclamation il doit être présenté.</td>
        <td style="text-align: right; font-weight: bold;"><?= htmlspecialchars($date_impression) ?></td>
      </tr>
    </table>

    <!-- DIVISEUR POINTILLÉ ENTRE LES DEUX PARTIES -->
    <div class="dashed-divider-zone">
      <div class="dashed-line"></div>
      <div class="ref-caiss-text">Réf_caiss <?= htmlspecialchars($code_barre_val) ?></div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. PARTIE INFERIEURE : COPIE REÇU DE VERSEMENT POUR ARCHIVAGE -->
    <!-- ========================================================================= -->

    <!-- BANNIÈRE COPIE ARCHIVAGE -->
    <div class="banner-title-box" style="font-size: 12pt;">COPIE REÇU DE VERSEMENT POUR ARCHIVAGE</div>
    <div class="annee-subbanner">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_universitaire) ?></div>

    <!-- DONNÉES ÉTUDIANT & PHOTO COPIE -->
    <table class="info-photo-table">
      <tr>
        <td class="info-left-cell">
          <table class="student-info-grid">
            <tr>
              <td style="width: 50%;">N° : &nbsp;<span class="val-bold"><?= htmlspecialchars($numero_recu) ?></span></td>
              <td style="width: 50%;">Date : &nbsp;<span class="val-bold"><?= htmlspecialchars($date_operation) ?></span></td>
            </tr>
            <tr>
              <td>Matricule : &nbsp;<span class="val-bold"><?= htmlspecialchars($matricule) ?></span></td>
              <td>Filière / Niveau : &nbsp;<span class="val-bold"><?= htmlspecialchars($filiere_niveau) ?></span></td>
            </tr>
            <tr>
              <td colspan="2">Nom & Prénoms(s) : &nbsp;<span class="val-bold"><?= htmlspecialchars($nom_prenoms) ?></span></td>
            </tr>
            <tr>
              <td>Type d'Opération : &nbsp;<span style="background:#CCCCCC; padding:1px 6px; font-weight:bold;"><?= htmlspecialchars($type_operation) ?></span></td>
              <td>Banque : <span style="background:#CCCCCC; padding:1px 5px; font-weight:bold;">--</span> &nbsp;Reste à Payer : <span style="background:#CCCCCC; padding:1px 6px; font-weight:bold; white-space:nowrap;"><?= number_format($reste_a_payer, 0, ',', ' ') ?>CFA</span></td>
            </tr>
            <tr>
              <td colspan="2">Montant de l'Opération : &nbsp;<span class="val-bold">(<?= htmlspecialchars($montant_operation_formatted) ?>)</span> &nbsp;<span class="amount-words-blue"><?= htmlspecialchars($montant_en_lettres) ?></span></td>
            </tr>
          </table>
        </td>
        <td class="photo-right-cell">
          <div class="photo-frame">
            <?php if (!empty($photo_src)): ?>
              <img src="<?= $photo_src ?>" alt="Photo Étudiant" class="photo-img">
            <?php else: ?>
              <div class="photo-inits"><?= htmlspecialchars($initsPDF) ?></div>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    </table>

    <!-- ZONE SOLDE & CODE-BARRES COPIE -->
    <div class="bottom-status-row">
      <div class="bottom-status-left">
        <table class="solde-inline-table">
          <tr>
            <td class="solde-label-cell">Date du Prochain Payement :</td>
            <td class="solde-badge-cell"><?= $reste_a_payer <= 0 ? 'SOLDÉ' : ('RESTE : ' . number_format($reste_a_payer, 0, ',', ' ') . ' CFA') ?></td>
          </tr>
        </table>
      </div>
      <div class="bottom-status-right">
        <div class="barcode-box">
          <?= generatePicqerBarcodeHtml($code_barre_val, 45, 1.35) ?>
        </div>
        <div class="caissier-title">CAISSIER(RE)</div>
        <div class="caissier-name"><?= htmlspecialchars($caissier_nom) ?></div>
      </div>
      <div style="clear: both;"></div>
    </div>

    <table class="notice-row-table" style="margin-top: 8px; margin-bottom: 0;">
      <tr>
        <td style="font-family: monospace; font-weight: bold; font-size: 8pt; font-style: normal;">
          <?= htmlspecialchars($code_barre_val) ?>
        </td>
        <td style="text-align: right; font-weight: bold; font-size: 8pt; font-style: normal;">
          <?= htmlspecialchars($date_impression) ?>
        </td>
      </tr>
    </table>

  </div>

</body>
</html>
