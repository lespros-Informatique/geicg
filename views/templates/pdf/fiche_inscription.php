<?php
/**
 * Template Officiel de Fiche d'Inscription / Reçu d'Inscription (Adapté GROUPE EICG)
 * Emplacement : /views/templates/pdf/fiche_inscription.php
 */

// Générateur vectoriel SVG Code 128 (Subset B) natif pour l'affichage/impression web
if (!function_exists('generateCode128BarcodeSvg')) {
    function generateCode128BarcodeSvg($code, $height = 38, $scale = 1.3) {
        $patterns = [
            '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
            '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
            '221231','213212','223112','312131','311222','321122','321221','312212','322112','322211',
            '212123','212321','232121','111323','131123','131321','112313','132113','132311','211313',
            '231113','231311','112133','112331','132131','113123','113321','133121','313121','211331',
            '231131','213113','213311','213131','311123','311321','331121','312113','312311','332111',
            '314111','221411','431111','111224','111422','121124','121421','141122','141221','112214',
            '112412','122114','122411','142112','142211','241211','221114','413111','241112','134111',
            '111242','121142','121241','114212','124112','124211','411212','421112','421211','212141',
            '214121','412121','111143','111341','131141','114113','114311','411113','411311','113141',
            '114131','311141','411131','211412','211214','211232','2331112'
        ];
        $code = (string)$code;
        if ($code === '') $code = 'EICG-REC';
        $startB = 104;
        $checksum = $startB;
        $sequence = [$patterns[$startB]];
        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $charVal = ord($code[$i]) - 32;
            if ($charVal < 0 || $charVal > 95) $charVal = 0;
            $checksum += $charVal * ($i + 1);
            $sequence[] = $patterns[$charVal];
        }
        $checkVal = $checksum % 103;
        $sequence[] = $patterns[$checkVal];
        $sequence[] = $patterns[106];
        
        $totalModules = 0;
        foreach ($sequence as $p) {
            for ($j = 0; $j < strlen($p); $j++) {
                $totalModules += (int)$p[$j];
            }
        }
        $totalWidth = round($totalModules * $scale, 1);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $totalWidth . '" height="' . $height . '" viewBox="0 0 ' . $totalWidth . ' ' . $height . '" style="display:block; margin:0 auto; max-width:100%; height:' . $height . 'px;">';
        $x = 0;
        foreach ($sequence as $p) {
            for ($j = 0; $j < strlen($p); $j++) {
                $w = (int)$p[$j] * $scale;
                if ($j % 2 == 0) {
                    $svg .= '<rect x="' . round($x, 2) . '" y="0" width="' . round($w, 2) . '" height="' . $height . '" fill="#000000" />';
                }
                $x += $w;
            }
        }
        $svg .= '</svg>';
        return $svg;
    }
}

$ministere = $ministere ?? "MINISTERE DE L'ENSEIGNEMENT SUPERIEUR\nET DE LA RECHERCHE SCIENTIFIQUE";
$pays = $pays ?? "REPUBLIQUE DE CÔTE D'IVOIRE";
$devise_pays = $devise_pays ?? "Union - Discipline - Travail";
$universite = $universite ?? ($etablissement['nom_etablissement'] ?? "GROUPE EICG - ÉCOLE INTERNATIONALE DE COMMERCE ET DE GESTION");

// Chargement du logo officiel Groupe EICG (public/assets/images/logo/logo_eicg.jpg)
$logoEicgFile = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
$logoSrc = RACINE . 'assets/images/logo/logo_eicg.jpg';
if (file_exists($logoEicgFile)) {
    $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoEicgFile));
}

$annee_universitaire = $annee_universitaire ?? "2022 - 2023";

$matricule_mesrs = $matricule_mesrs ?? ($item['matricule_mesrs'] ?? ($item['matricule_etudiant'] ?? "SEDJ1406010001"));
$nom = $nom ?? ($item['nom_etudiant'] ?? "SEDEGNON");
$prenoms = $prenoms ?? ($item['prenom_etudiant'] ?? "JOSUE GUY-ARNAUD");
$date_naissance = $date_naissance ?? ($item['date_naissance_etudiant'] ?? "14-06-2001");
$lieu_naissance = $lieu_naissance ?? ($item['lieu_naissance_etudiant'] ?? "ABOBO");
$date_lieu_naissance = $date_lieu_naissance ?? ($date_naissance . " à " . $lieu_naissance);
$nationalite = $nationalite ?? ($item['nationalite_etudiant'] ?? "IVOIRIENNE");

$filiere = $filiere ?? ($inscription['libelle_filiere'] ?? "INFORMATIQUE ET SCIENCES DU NUMERIQUE");
$niveau = $niveau ?? ($inscription['libelle_niveau'] ?? "MASTER 1 - SEMESTRE 1 & 2");
$specialite = $specialite ?? ($inscription['libelle_specialite'] ?? "CYBERSECURITE ET INTERNET DES OBJETS (CIO)");
$type_formation = $type_formation ?? "FORMATION INITIALE";

$session_semestrielle = $session_semestrielle ?? "Rentree de septembre " . $annee_universitaire;
$semestre_libelle = $semestre_libelle ?? $niveau;
$code_paiement = $code_paiement ?? ($paiement['code_paiement'] ?? "IDK23633D9A81957EE");
$code_barre_val = $code_barre_val ?? ($inscription['code_inscription'] ?? $code_paiement);
$montant_paiement = $montant_paiement ?? (isset($paiement['montant_paiement']) ? number_format((float)$paiement['montant_paiement'], 0, ',', '.') . " F" : "60.000 F");
$date_paiement = $date_paiement ?? (isset($paiement['created_at_paiement']) ? date('d-m-Y', strtotime($paiement['created_at_paiement'])) : "26-10-2022");

$lieu_date_delivrance = $lieu_date_delivrance ?? ("Fait Abidjan le " . ($date_fiche ?? "12 Janvier 2023"));
$titre_signataire = $titre_signataire ?? "La Sous-Directrice de la Scolarité,\ndes Services Juridiques et de la Communication";
$nom_signataire = $nom_signataire ?? "Mme KADIO Julie Epse ASSALE";

$qr_data = $qr_data ?? ("EICG-INSCRIPTION-" . $matricule_mesrs . "-" . $code_paiement);
$qr_code_url = $qr_code_url ?? ("https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($qr_data));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche d'Inscription - <?= htmlspecialchars($nom . ' ' . $prenoms) ?></title>
  <style>
    @page {
      margin: 8mm 10mm 8mm 10mm;
    }
    
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 9.5pt;
      color: #000000;
      line-height: 1.3;
      background-color: #FFFFFF;
      margin: 0;
      padding: 0;
    }

    /* FILIGRANE DE FOND GROUPE EICG */
    .watermark-bg {
      position: absolute;
      top: 32%;
      left: 15%;
      width: 70%;
      opacity: 0.055;
      z-index: -1;
      text-align: center;
      pointer-events: none;
    }
    .watermark-bg svg {
      width: 420px;
      height: 420px;
    }

    /* EN-TÊTE OFFICIEL */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .header-table td {
      vertical-align: top;
    }
    .header-left {
      font-size: 7.5pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      line-height: 1.2;
    }
    .header-right {
      font-size: 8pt;
      font-weight: bold;
      text-align: right;
      text-transform: uppercase;
      color: #000000;
      line-height: 1.25;
    }
    .header-motto {
      font-size: 7.5pt;
      font-weight: normal;
      text-transform: none;
      display: block;
      margin-top: 2px;
    }

    .top-divider {
      border: none;
      border-top: 1.5px solid #1E3A5F;
      margin: 4px 0 10px 0;
    }

    /* GROUPE EICG Brand & Title */
    .univ-brand-container {
      text-align: center;
      margin-bottom: 8px;
    }
    .univ-logo-row {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }
    .univ-title {
      font-family: "Times New Roman", Times, Georgia, serif;
      font-size: 17.5pt;
      font-weight: bold;
      color: #1E3A5F;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin: 0;
    }

    .brand-dashed-line {
      text-align: center;
      font-weight: bold;
      letter-spacing: 2px;
      color: #334155;
      margin: 4px 0 8px 0;
      font-size: 10pt;
    }

    /* CADRE DU TITRE PRINCIPAL */
    .title-box-wrapper {
      text-align: center;
      margin: 8px 0 10px 0;
    }
    .title-box {
      display: inline-block;
      border: 2px solid #000000;
      border-radius: 4px;
      padding: 6px 42px;
      width: 78%;
      box-sizing: border-box;
      background: #FFFFFF;
    }
    .title-box-text {
      font-family: "Times New Roman", Times, Georgia, serif;
      font-size: 16.5pt;
      font-weight: 900;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #000000;
      margin: 0;
    }

    .annee-subtitle {
      font-size: 11pt;
      font-weight: bold;
      color: #000000;
      margin: 8px 0 12px 0;
    }

    /* SECTIONS DE FICHE */
    .section-block {
      margin-bottom: 12px;
    }
    .section-header {
      font-size: 10.5pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      margin-bottom: 6px;
      letter-spacing: 0.5px;
    }

    .field-row {
      margin-bottom: 4px;
      font-size: 9.5pt;
    }
    .field-label {
      font-weight: bold;
      color: #000000;
    }
    .field-value {
      color: #000000;
      font-weight: normal;
    }

    /* TABLEAU DES RÈGLEMENTS */
    .payment-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 12px;
    }
    .payment-table th {
      background-color: #FDE047;
      border: 1px solid #CA8A04;
      font-size: 9pt;
      font-weight: bold;
      color: #000000;
      padding: 6px 8px;
      text-align: center;
    }
    .payment-table td {
      border: 1px solid #CBD5E1;
      font-size: 9pt;
      padding: 6px 10px;
      color: #000000;
    }
    .text-center { text-align: center; }
    .text-bold { font-weight: bold; }

    /* ZONE DE VALIDATION (QR CODE & CACHET) */
    .validation-area {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 12px;
    }
    .validation-area td {
      vertical-align: top;
    }

    .qr-container {
      width: 130px;
      text-align: left;
    }
    .qr-code-img {
      width: 105px;
      height: 105px;
      border: 1px solid #CBD5E1;
      padding: 3px;
      background: #FFFFFF;
      border-radius: 4px;
    }

    .signatory-container {
      text-align: right;
    }
    .date-delivrance {
      font-size: 9.5pt;
      color: #000000;
      margin-bottom: 8px;
    }
    .signatory-title {
      font-size: 9.5pt;
      font-weight: bold;
      color: #000000;
      line-height: 1.25;
      margin-bottom: 4px;
    }

    /* CACHET ROND OFFICIEL GROUPE EICG */
    .stamp-box {
      display: inline-block;
      position: relative;
      width: 150px;
      height: 95px;
      margin-top: 2px;
      margin-bottom: 2px;
    }
    
    .stamp-svg {
      width: 125px;
      height: 125px;
      position: absolute;
      right: 10px;
      top: -15px;
      opacity: 0.9;
    }

    .signatory-name {
      font-size: 9.5pt;
      font-weight: bold;
      color: #000000;
      margin-top: 4px;
      display: block;
    }

    /* BANDEAU DE PIED DE PAGE : CODE-BARRES DU BAS */
    .page-footer-barcode-band {
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px dashed #94A3B8;
      text-align: center;
      width: 100%;
    }
    .barcode-wrapper {
      margin: 4px auto 2px auto;
      text-align: center;
    }
    .barcode-text-code {
      font-family: monospace;
      font-size: 9pt;
      font-weight: bold;
      color: #0F172A;
      letter-spacing: 1.5px;
      margin-top: 2px;
    }

    @media print {
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- FILIGRANE DE FOND GROUPE EICG (WATERMARK) -->
  <div class="watermark-bg">
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <circle cx="100" cy="100" r="90" fill="none" stroke="#1E3A5F" stroke-width="4"/>
      <circle cx="100" cy="100" r="78" fill="none" stroke="#1E3A5F" stroke-width="1.5" stroke-dasharray="4 2"/>
      <text x="100" y="112" font-size="28" font-family="Arial" font-weight="900" fill="#1E3A5F" text-anchor="middle" letter-spacing="2">EICG</text>
      <text x="100" y="165" font-size="12" font-family="Arial" font-weight="bold" fill="#1E3A5F" text-anchor="middle">GROUPE EICG</text>
    </svg>
  </div>

  <!-- BARRE D'IMPRESSION (MASQUÉE EN IMPRESSION PDF) -->
  <div class="no-print" style="background: #1E3A5F; color: #FFFFFF; padding: 10px 16px; margin-bottom: 20px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
    <div style="font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 8px;">
      <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #22C55E;"></span>
      Fiche d'Inscription Officielle — GROUPE EICG
    </div>
    <button onclick="window.print();" style="background: #2563EB; color: #FFFFFF; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 6px;">
      🖨️ Imprimer / Exporter PDF
    </button>
  </div>

  <!-- 1. EN-TÊTE OFFICIEL DU MINISTÈRE & PAYS -->
  <table class="header-table">
    <tr>
      <td class="header-left">
        <?= nl2br(htmlspecialchars($ministere)) ?>
      </td>
      <td class="header-right">
        <?= htmlspecialchars($pays) ?>
        <span class="header-motto"><?= htmlspecialchars($devise_pays) ?></span>
      </td>
    </tr>
  </table>

  <hr class="top-divider">

  <!-- 2. IDENTIFICATION DE L'ÉTABLISSEMENT / GROUPE EICG -->
  <div class="univ-brand-container">
    <div class="univ-logo-row">
      <!-- LOGO OFFICIEL GROUPE EICG -->
      <img src="<?= $logoSrc ?>" alt="Logo GROUPE EICG" style="height: 52px; width: auto; max-width: 150px; object-fit: contain; vertical-align: middle;">
      <h1 class="univ-title"><?= htmlspecialchars($universite) ?></h1>
    </div>
    <div class="brand-dashed-line">---------------------------------------------------------------------------------------------------------</div>
  </div>

  <!-- 3. TITRE ENCADRÉ -->
  <div class="title-box-wrapper">
    <div class="title-box">
      <h2 class="title-box-text">FICHE D'INSCRIPTION</h2>
    </div>
  </div>

  <!-- 4. ANNÉE UNIVERSITAIRE -->
  <div class="annee-subtitle">
    ANNÉE UNIVERSITAIRE : &nbsp; <?= htmlspecialchars($annee_universitaire) ?>
  </div>

  <!-- 5. SECTION IDENTITÉ -->
  <div class="section-block">
    <div class="section-header">IDENTITE</div>
    
    <div class="field-row">
      <span class="field-label">Matricule MESRS :</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-value"><?= htmlspecialchars($matricule_mesrs) ?></span>
    </div>
    
    <div class="field-row">
      <span class="field-label">Nom :</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-value"><?= htmlspecialchars($nom) ?></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Prénoms :</span> &nbsp;
      <span class="field-value"><?= htmlspecialchars($prenoms) ?></span>
    </div>
    
    <div class="field-row">
      <span class="field-label">Date et lieu de naissance :</span> &nbsp;
      <span class="field-value"><?= htmlspecialchars($date_lieu_naissance) ?></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Nationalité :</span> &nbsp;
      <span class="field-value"><?= htmlspecialchars($nationalite) ?></span>
    </div>
  </div>

  <!-- 6. SECTION INSCRIPTION -->
  <div class="section-block" style="margin-top: 22px;">
    <div class="section-header">INSCRIPTION</div>
    
    <div class="field-row">
      <span class="field-label">Filière :</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-value"><?= htmlspecialchars($filiere) ?></span>
    </div>
    
    <div class="field-row">
      <span class="field-label">Niveau :</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-value"><?= htmlspecialchars($niveau) ?></span> &nbsp;|&nbsp;
      <span class="field-label">Spécialité:</span> &nbsp;
      <span class="field-value"><?= htmlspecialchars($specialite) ?></span>
    </div>
    
    <div class="field-row">
      <span class="field-label">Type formation :</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-value"><?= htmlspecialchars($type_formation) ?></span>
    </div>
  </div>

  <!-- 7. TABLEAU DES RÈGLEMENTS DE SCOLARITÉ -->
  <table class="payment-table">
    <thead>
      <tr>
        <th style="width: 28%;">Session semestrielle</th>
        <th style="width: 25%;">Semestre</th>
        <th style="width: 22%;">Code de paiement</th>
        <th style="width: 13%;">Montant</th>
        <th style="width: 12%;">Date</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= htmlspecialchars($session_semestrielle) ?></td>
        <td><?= htmlspecialchars($semestre_libelle) ?></td>
        <td class="text-center text-bold"><?= htmlspecialchars($code_paiement) ?></td>
        <td class="text-center text-bold"><?= htmlspecialchars($montant_paiement) ?></td>
        <td class="text-center"><?= htmlspecialchars($date_paiement) ?></td>
      </tr>
    </tbody>
  </table>

  <!-- 8. ZONE DE VALIDATION (QR CODE A GAUCHE, CACHET ET SIGNATURE A DROITE) -->
  <table class="validation-area">
    <tr>
      <!-- COLONNE GAUCHE : QR CODE DE VÉRIFICATION -->
      <td class="qr-container">
        <img src="<?= htmlspecialchars($qr_code_url) ?>" alt="QR Code" class="qr-code-img">
        <div style="font-size: 7.5pt; color: #64748B; text-align: center; margin-top: 4px; width: 115px;">
          Contrôle d'authenticité
        </div>
      </td>

      <!-- COLONNE DROITE : DATE, CACHET OFFICIEL GEICG ET SIGNATURE -->
      <td class="signatory-container">
        <div class="date-delivrance"><?= htmlspecialchars($lieu_date_delivrance) ?></div>
        
        <div class="signatory-title">
          <?= nl2br(htmlspecialchars($titre_signataire)) ?>
        </div>

        <!-- REPRÉSENTATION DU CACHET OFFICIEL GROUPE EICG ET DE LA SIGNATURE -->
        <div class="stamp-box">
          <svg class="stamp-svg" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
            <!-- Cercle extérieur bleu marine EICG -->
            <circle cx="80" cy="80" r="74" fill="none" stroke="#1E3A5F" stroke-width="3"/>
            <!-- Cercle intérieur pointillé -->
            <circle cx="80" cy="80" r="65" fill="none" stroke="#1E3A5F" stroke-width="1.5" stroke-dasharray="4 2"/>
            
            <!-- Texte circulaire supérieur -->
            <path id="circlePathTop" d="M 22 80 A 58 58 0 0 1 138 80" fill="none"/>
            <text font-size="8.5" font-family="Arial" font-weight="bold" fill="#1E3A5F" text-anchor="middle">
              <textPath href="#circlePathTop" startOffset="50%">
                GROUPE EICG - SCOLARITE
              </textPath>
            </text>

            <!-- Texte central dans cartouche -->
            <rect x="35" y="68" width="90" height="24" rx="4" fill="#EFF6FF" stroke="#1E3A5F" stroke-width="1.5"/>
            <text x="80" y="84" font-size="11" font-family="Arial" font-weight="900" fill="#1E3A5F" text-anchor="middle" letter-spacing="1">
              GROUPE EICG
            </text>

            <!-- Étoiles décoratives -->
            <text x="26" y="98" font-size="10" fill="#1E3A5F">*</text>
            <text x="130" y="98" font-size="10" fill="#1E3A5F">*</text>

            <!-- Texte inférieur -->
            <path id="circlePathBottom" d="M 138 80 A 58 58 0 0 1 22 80" fill="none"/>
            <text font-size="8.5" font-family="Arial" font-weight="bold" fill="#1E3A5F" text-anchor="middle">
              <textPath href="#circlePathBottom" startOffset="50%">
                ★ DIRECTION ACADÉMIQUE ★
              </textPath>
            </text>

            <!-- Signature manuscrite stylisée superposée -->
            <path d="M 30 95 C 45 60 70 110 85 70 C 95 50 110 90 140 75 M 60 85 L 125 80" fill="none" stroke="#0F233D" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
        </div>

        <div class="signatory-name"><?= htmlspecialchars($nom_signataire) ?></div>
      </td>
    </tr>
  </table>

  <!-- 9. PIED DE PAGE EXTRÊME : CODE-BARRES CENTRÉ TOUT EN BAS DU DOCUMENT -->
  <div class="page-footer-barcode-band">
    <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
      Code d'Identification & de Sécurité Numérique
    </div>
    <div class="barcode-wrapper">
      <?php if (!empty($is_pdf) || !empty($_GET['pdf'])): ?>
        <barcode code="<?= htmlspecialchars($code_barre_val) ?>" type="C128A" size="0.75" height="0.75" />
      <?php else: ?>
        <?= generateCode128BarcodeSvg($code_barre_val, 38, 1.3) ?>
      <?php endif; ?>
    </div>
    <div class="barcode-text-code">* <?= htmlspecialchars($code_barre_val) ?> *</div>
  </div>

</body>
</html>
