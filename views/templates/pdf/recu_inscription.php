<?php
/**
 * Template Officiel du Reçu d'Inscription (Modèle GROUPE EICG 100% Conforme)
 * Emplacement : /views/templates/pdf/recu_inscription.php
 */

// Générateur de code-barres professionnel via la librairie Picqer Barcode Generator
if (!function_exists('generatePicqerBarcodeHtml')) {
    function generatePicqerBarcodeHtml($code, $height = 30, $widthFactor = 1.35) {
        $code = (string)$code;
        if (empty($code)) $code = 'GE-25260276';
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $pngData = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);
            return '<img src="data:image/png;base64,' . base64_encode($pngData) . '" alt="Code-barres ' . htmlspecialchars($code) . '" style="display:inline-block; vertical-align:middle; height:' . $height . 'px; max-width:100%;">';
        } catch (\Throwable $e) {
            return '<div style="font-family:monospace; font-weight:bold; font-size:10px;">' . htmlspecialchars($code) . '</div>';
        }
    }
}

// Chargement du logo officiel Groupe EICG (public/assets/images/logo/logo_eicg.jpg)
$logoEicgFile = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
$logoSrc = defined('RACINE') ? RACINE . 'assets/images/logo/logo_eicg.jpg' : '/geicg/public/assets/images/logo/logo_eicg.jpg';
if (file_exists($logoEicgFile)) {
    $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoEicgFile));
}

// Données dynamiques
$annee_universitaire = $annee_universitaire ?? "2025-2026";
$numero_recu = $numero_recu ?? "GE-25260276";
$date_operation = $date_operation ?? "14/10/2025 10:33:33";
$matricule = $matricule ?? "AA-914/GEB/GEC25";
$nom_prenoms = $nom_prenoms ?? "ABOZAN AMON AMBROISINE";
$filiere_niveau = $filiere_niveau ?? "GEC_2A";
$statut_etudiant = $statut_etudiant ?? "AFFECTE";
$type_operation = $type_operation ?? "SCOLARITE";
$montant_operation = $montant_operation ?? 105000;
$montant_operation_formatted = $montant_operation_formatted ?? "105 000CFA";
$montant_en_lettres = $montant_en_lettres ?? "Cent cinq mille francs CFA";
$scolarite_total = $scolarite_total ?? 105000;
$total_verse = $total_verse ?? 105000;
$reste_a_payer = $reste_a_payer ?? 0;
$caissier_nom = $caissier_nom ?? "Mlle KONE N'diatty A. Mariam";
$date_impression = $date_impression ?? date('d/m/Y H:i:s');
$code_barre_val = $code_barre_val ?? "GE-25260276ScoFOF45944,4399676042ScaisKON";

// Montants lignes du tableau
$scolarite_op_jour = 25000;
$scolarite_tot_payer = $scolarite_total;
$scolarite_tot_verse = 25000;
$scolarite_reste = 0;

$droit_op_jour = 80000;
$droit_tot_verse = 80000;

if ($montant_operation != 105000) {
    $scolarite_op_jour = $montant_operation;
    $scolarite_tot_verse = $total_verse;
    $scolarite_reste = $reste_a_payer;
    $droit_op_jour = 0;
    $droit_tot_verse = 0;
}
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
      margin: 0 0 5px 0;
      text-decoration: underline;
      letter-spacing: 0.2px;
    }
    .header-subtitle-1 {
      font-weight: bold;
      font-style: italic;
      font-size: 8.5pt;
      margin-top: 5px;
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
      margin: 10px 0 4px 0;
      font-weight: bold;
      font-size: 15.5pt;
      font-family: 'arial bold';
      letter-spacing: 1.2px;
      text-transform: uppercase;
    }

    .annee-subbanner {
      text-align: center;
      font-weight: bold;
      font-size: 10.5pt;
      margin-bottom: 4px;
      color: #000000;
    }

    /* FORMULAIRE DONNÉES ÉTUDIANT & PHOTO */
    .info-photo-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .info-left-cell {
      vertical-align: top;
      width: 75%;
    }
    .photo-right-cell {
      vertical-align: top;
      text-align: right;
      width: 100px;
    }
    .photo-frame {
      width: 90px;
      height: 110px;
      border: 2px solid #000000;
      background: #F1F5F9;
      display: inline-block;
      box-sizing: border-box;
      vertical-align: top;
      text-align: center;
      overflow: hidden;
    }
    .photo-img {
      width: 90px;
      height: 110px;
      object-fit: cover;
      display: block;
    }
    .photo-inits {
      width: 100%;
      height: 100%;
      line-height: 106px;
      text-align: center;
      font-weight: bold;
      font-size: 26pt;
      color: #000000;
      background: #F1F5F9;
    }

    .student-info-grid {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5pt;
      line-height: 1.5;
    }
    .student-info-grid td {
      padding: 0.5px 0;
      vertical-align: top;
    }
    .val-bold {
      font-weight: bold;
      color: #000000;
    }
    .amount-words-blue {
      color: #0000FF;
      font-weight: bold;
      font-size: 9.5pt;
    }

    /* TABLEAU FINANCIER DES RÈGLEMENTS */
    .grid-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 3px;
      margin-bottom: 4px;
    }
    .grid-table th {
      border: 1px solid #000000;
      font-size: 8pt;
      font-weight: bold;
      color: #000000;
      padding: 3px 4px;
      text-align: center;
      background: #FFFFFF;
    }
    .grid-table td {
      border: 1px solid #000000;
      font-size: 8pt;
      padding: 2.5px 4px;
      color: #000000;
    }
    .row-total td {
      background-color: #999999;
      font-weight: 900;
      font-size: 8.5pt;
    }
    .cell-black {
      background-color: #000000;
    }
    .text-center { text-align: center; }
    .text-bold { font-weight: bold; }

    /* ZONE SOLDE & CODE-BARRES / CAISSIER */
    .bottom-status-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 3px;
      margin-bottom: 4px;
    }
    .bottom-status-table td {
      vertical-align: top;
    }
    .solde-badge {
      background-color: #999999;
      color: #DC2626;
      font-weight: 900;
      font-size: 11pt;
      padding: 2px 20px;
      display: inline-block;
      letter-spacing: 1px;
    }

    .caissier-title {
      font-weight: bold;
      text-decoration: underline;
      font-size: 8.5pt;
      margin-top: 1px;
    }
    .caissier-name {
      font-size: 8pt;
      color: #000000;
      margin-top: 1px;
    }

    /* ENCADRÉ JAUNE ATTENTION */
    .nb-yellow-box {
      background-color: #FFFF00;
      border: 1px solid #000000;
      padding: 3px 6px;
      font-weight: bold;
      font-size: 7.5pt;
      font-style: italic;
      color: #000000;
      margin: 4px 0 3px 0;
    }

    .notice-row-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 7.5pt;
      color: #000000;
      font-style: italic;
      margin-bottom: 4px;
    }

    /* DIVISEUR POINTILLÉ ENTRE LES DEUX PARTIES */
    .dashed-divider-zone {
      text-align: center;
      margin: 4px 0 4px 0;
    }
    .dashed-line {
      border-top: 1px dashed #000000;
      margin-bottom: 2px;
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

  <?php if (empty($is_pdf) && empty($_GET['pdf'])): ?>
  <!-- BARRE D'IMPRESSION EN NAVIGATEUR -->
  <div class="no-print" style="background: #1E3A5F; color: #FFFFFF; padding: 8px 14px; margin-bottom: 10px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
    <div style="font-weight: bold; font-size: 13px;">Reçu d'Inscription  — GROUPE EICG</div>
    <button onclick="window.print();" style="background: #2563EB; color: #FFFFFF; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer;">🖨️ Imprimer / Exporter PDF</button>
  </div>
  <?php endif; ?>

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

    <!-- EN-TÊTE ÉTABLISSEMENT -->
    <table class="header-table">
      <tr>
        <td class="header-logo-cell">
          <img src="<?= $logoSrc ?>" alt="Logo EICG" class="header-logo-img">
        </td>
        <td class="header-text-cell">
          <h1 class="header-title" style="color: #700303ff; font-weight: bold;">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</h1>
          <div class="header-subtitle-1">Agréé par l'Etat et le FDFP</div>
          <div class="header-subtitle-2">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
          <div class="header-subtitle-2">Site web : www.groupe-eicg.net</div>
        </td>
      </tr>
    </table>

    <!-- BANNIÈRE PRINCIPALE ET ANNÉE -->
    <div class="banner-title-box">REÇU D'INSCRIPTION</div>
    <div class="annee-subbanner">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_universitaire) ?></div>

    <!-- DONNÉES ÉTUDIANT & PHOTO -->
    <table class="info-photo-table">
      <tr>
        <td class="info-left-cell">
          <table class="student-info-grid">
            <tr>
              <td style="width: 50%;">N° &nbsp;<span class="val-bold"><?= htmlspecialchars($numero_recu) ?></span></td>
              <td style="text-align: right; padding-right: 15px;"><span class="val-bold"><?= htmlspecialchars($date_operation) ?></span></td>
            </tr>
            <tr>
              <td>Numéro réf étudiant( e) : &nbsp;<span class="val-bold"><?= htmlspecialchars($matricule) ?></span></td>
              <td>Filière_Niveau : &nbsp;<span class="val-bold"><?= htmlspecialchars($filiere_niveau) ?></span></td>
            </tr>
            <tr>
              <td colspan="2">Nom_Prénom(s) : &nbsp;<span class="val-bold"><?= htmlspecialchars($nom_prenoms) ?></span></td>
            </tr>
            <tr>
              <td>Type d'Opération</td>
              <td>Statut : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="val-bold"><?= htmlspecialchars($statut_etudiant) ?></span></td>
            </tr>
            <tr>
              <td>Montant de l'Opération : &nbsp;<span class="val-bold"><?= htmlspecialchars($montant_operation_formatted) ?></span></td>
              <td><span class="amount-words-blue"><?= htmlspecialchars($montant_en_lettres) ?></span></td>
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
          <td class="text-bold">SCOLARITE</td>
          <td class="text-center text-bold"><?= number_format($scolarite_op_jour, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_tot_payer, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_tot_verse, 0, ',', ' ') ?>CFA</td>
          <td class="text-center text-bold"><?= number_format($scolarite_reste, 0, ',', ' ') ?>CFA</td>
        </tr>
        <tr>
          <td class="text-bold">Droit d'Inscription</td>
          <td class="text-center text-bold"><?= number_format($droit_op_jour, 0, ',', ' ') ?>CFA</td>
          <td></td>
          <td class="text-center text-bold"><?= number_format($droit_tot_verse, 0, ',', ' ') ?>CFA</td>
          <td class="cell-black"></td>
        </tr>
        <tr>
          <td class="text-bold">AUTRES FRAIS</td>
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
    <table class="bottom-status-table">
      <tr>
        <td style="width: 55%;">
          Date du Prochain Payement : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <span class="solde-badge"><?= $reste_a_payer <= 0 ? 'SOLDÉ' : ('RESTE: ' . number_format($reste_a_payer, 0, ',', ' ') . 'CFA') ?></span>
        </td>
        <td style="width: 45%; text-align: right;">
          <div style="display: inline-block; text-align: right;">
            <?= generatePicqerBarcodeHtml($code_barre_val, 30, 1.35) ?>
            <div class="caissier-title">CAISSIER(RE)</div>
            <div class="caissier-name"><?= htmlspecialchars($caissier_nom) ?></div>
          </div>
        </td>
      </tr>
    </table>

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
              <td style="width: 50%;">N° &nbsp;<span class="val-bold"><?= htmlspecialchars($numero_recu) ?></span></td>
              <td style="text-align: right; padding-right: 15px;"><span class="val-bold"><?= htmlspecialchars($date_operation) ?></span></td>
            </tr>
            <tr>
              <td>Numéro réf étudiant( e) : &nbsp;<span class="val-bold"><?= htmlspecialchars($matricule) ?></span></td>
              <td>Filière_Niveau : &nbsp;<span class="val-bold"><?= htmlspecialchars($filiere_niveau) ?></span></td>
            </tr>
            <tr>
              <td colspan="2">Nom_Prénom(s) : &nbsp;<span class="val-bold"><?= htmlspecialchars($nom_prenoms) ?></span></td>
            </tr>
            <tr>
              <td>Type d'Opération &nbsp;&nbsp;&nbsp;&nbsp; <span style="background:#CCCCCC; padding:1px 8px; font-weight:bold;"><?= htmlspecialchars($type_operation) ?></span></td>
              <td>Banque : &nbsp;&nbsp;&nbsp;&nbsp; <span style="background:#CCCCCC; padding:1px 8px; font-weight:bold;">--</span> &nbsp;&nbsp;&nbsp;&nbsp; Reste à Payer &nbsp; <span style="background:#CCCCCC; padding:1px 8px; font-weight:bold;"><?= number_format($reste_a_payer, 0, ',', ' ') ?>CFA</span></td>
            </tr>
            <tr>
              <td>Montant de l'Opération : &nbsp;<span class="val-bold"><?= htmlspecialchars($montant_operation_formatted) ?></span></td>
              <td><span class="amount-words-blue"><?= htmlspecialchars($montant_en_lettres) ?></span></td>
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
    <table class="bottom-status-table" style="margin-top: 6px;">
      <tr>
        <td style="width: 55%; vertical-align: top;">
          Date du Prochain Payement : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <span class="solde-badge"><?= $reste_a_payer <= 0 ? 'SOLDÉ' : ('RESTE: ' . number_format($reste_a_payer, 0, ',', ' ') . 'CFA') ?></span>
        </td>
        <td style="width: 45%; text-align: right; vertical-align: top;">
          <div style="display: inline-block; text-align: right;">
            <?= generatePicqerBarcodeHtml($code_barre_val, 30, 1.35) ?>
            <div class="caissier-title">CAISSIER(RE)</div>
            <div class="caissier-name"><?= htmlspecialchars($caissier_nom) ?></div>
          </div>
        </td>
      </tr>
    </table>

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
