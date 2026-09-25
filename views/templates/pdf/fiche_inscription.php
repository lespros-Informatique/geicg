<?php
/**
 * Template Officiel de Fiche d'Inscription / Reçu d'Inscription (Modèle Référence GEICG)
 * Emplacement : /views/templates/pdf/fiche_inscription.php
 */

// Générateur vectoriel SVG Code 128 (Subset B) natif pour l'affichage web
if (!function_exists('generateCode128BarcodeSvg')) {
    function generateCode128BarcodeSvg($code, $height = 36, $scale = 1.25) {
        $patterns = [
            '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
            '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
            '221231','213212','223112','312131','311222','321122','321221','322112','322211','212123',
            '212321','232121','111323','131123','131321','112313','132113','132311','211313','231113',
            '231311','112133','112331','132131','113123','113321','133121','313121','211331','231131',
            '213113','213311','213131','311123','311321','331121','312113','312311','332111','314111',
            '221411','431111','111224','111422','121124','121421','141122','141221','112214','112412',
            '122114','122411','142112','142211','241211','221114','413111','241112','134111','111242',
            '121142','121241','114212','124112','124211','411212','421112','421211','212141','214121',
            '412121','111143','111341','131141','114113','114311','411113','411311','113141','114131',
            '311141','411131','211412','211214','211232','2331112'
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

// Données dynamiques avec adaptabilités pour GROUPE EICG
$ministere = $ministere ?? "MINISTERE DE L'ENSEIGNEMENT SUPERIEUR\nET DE LA RECHERCHE SCIENTIFIQUE";
$pays = $pays ?? "REPUBLIQUE DE CÔTE D'IVOIRE";
$devise_pays = $devise_pays ?? "Union - Discipline - Travail";
$universite = $universite ?? ($etablissement['nom_etablissement'] ?? "GROUPE EICG - ÉCOLE INTERNATIONALE DE COMMERCE ET DE GESTION");

// Chargement du logo officiel Groupe EICG (public/assets/images/logo/logo_eicg.jpg)
$logoEicgFile = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
$logoSrc = defined('RACINE') ? RACINE . 'assets/images/logo/logo_eicg.jpg' : '/geicg/public/assets/images/logo/logo_eicg.jpg';
if (file_exists($logoEicgFile)) {
    $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoEicgFile));
}

$annee_universitaire = $annee_universitaire ?? "2025-2026";

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
$date_paiement = $date_paiement ?? (isset($paiement['created_at_paiement']) ? date('d-m-Y', strtotime($paiement['created_at_paiement'])) : (isset($paiement['date_paiement']) ? date('d-m-Y', strtotime($paiement['date_paiement'])) : "26-10-2022"));

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
      line-height: 1.35;
      background-color: #FFFFFF;
      margin: 0;
      padding: 0;
    }

    /* EN-TÊTE OFFICIEL DU MINISTÈRE & PAYS */
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
      line-height: 1.25;
      width: 55%;
    }
    .header-right {
      font-size: 7.5pt;
      font-weight: bold;
      text-align: right;
      text-transform: uppercase;
      color: #000000;
      line-height: 1.25;
      width: 45%;
    }
    .header-motto {
      font-size: 7.5pt;
      font-weight: normal;
      text-transform: none;
      color: #222222;
      margin-top: 2px;
    }

    .top-divider {
      border: none;
      border-top: 1.2px solid #000000;
      margin: 4px 0 8px 0;
    }

    /* IDENTIFICATION ÉTABLISSEMENT / GROUPE EICG (LOGO À GAUCHE, NOM À DROITE) */
    .brand-table {
      width: 100%;
      border-collapse: collapse;
      margin: 4px 0 4px 0;
    }
    .brand-logo-cell {
      width: 140px;
      text-align: right;
      vertical-align: middle;
      padding-right: 12px;
    }
    .brand-logo-img {
      max-height: 48px;
      width: auto;
      max-width: 130px;
      display: inline-block;
      vertical-align: middle;
    }
    .brand-title-cell {
      text-align: left;
      vertical-align: middle;
    }
    .brand-title {
      font-family: "Times New Roman", Times, Georgia, serif;
      font-size: 15.5pt;
      font-weight: bold;
      color: #1E3A5F;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin: 0;
      line-height: 1.25;
    }

    .brand-dashed-line {
      text-align: center;
      font-weight: bold;
      letter-spacing: 2px;
      color: #000000;
      margin: 6px 0 10px 0;
      font-size: 9pt;
    }

    /* CADRE DU TITRE PRINCIPAL */
    .title-box-wrapper {
      text-align: center;
      margin: 8px 0 10px 0;
    }
    .title-box {
      display: inline-block;
      border: 1.5px solid #000000;
      border-radius: 4px;
      padding: 6px 40px;
      width: 82%;
      box-sizing: border-box;
      background: #FFFFFF;
    }
    .title-box-text {
      font-family: "Times New Roman", Times, Georgia, serif;
      font-size: 16pt;
      font-weight: 900;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #000000;
      margin: 0;
    }

    .annee-subtitle {
      font-size: 10.5pt;
      font-weight: bold;
      color: #000000;
      margin: 10px 0 12px 0;
    }

    /* SECTIONS IDENTITÉ & INSCRIPTION */
    .section-block {
      margin-bottom: 12px;
    }
    .section-header {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      margin-bottom: 6px;
      letter-spacing: 0.5px;
    }

    .info-grid-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5pt;
      line-height: 1.6;
    }
    .info-grid-table td {
      padding: 2px 0;
      vertical-align: top;
    }
    .lbl {
      font-weight: bold;
      color: #000000;
      white-space: nowrap;
    }
    .val {
      color: #000000;
      font-weight: normal;
    }

    /* TABLEAU DES RÈGLEMENTS DE SCOLARITÉ */
    .payment-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
      margin-bottom: 16px;
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
      padding: 6px 8px;
      color: #000000;
    }
    .text-center { text-align: center; }
    .text-bold { font-weight: bold; }

    /* ZONE DE VALIDATION (QR CODE & CACHET & SIGNATURE) */
    .validation-area {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
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
      border: none;
      display: block;
    }
    .qr-caption {
      font-size: 7.5pt;
      color: #64748B;
      text-align: center;
      margin-top: 4px;
      width: 105px;
    }

    .signatory-container {
      text-align: right;
    }
    .date-delivrance {
      font-size: 9.5pt;
      color: #000000;
      margin-bottom: 4px;
    }
    .signatory-title {
      font-size: 9.5pt;
      font-weight: bold;
      color: #000000;
      line-height: 1.25;
      margin-bottom: 4px;
    }

    /* CACHET ROND OFFICIEL GROUPE EICG ET SIGNATURE */
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
      opacity: 0.88;
    }

    .signatory-name {
      font-size: 9.5pt;
      font-weight: bold;
      color: #000000;
      margin-top: 2px;
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

  <?php if (empty($is_pdf) && empty($_GET['pdf'])): ?>
  <!-- BARRE D'IMPRESSION (UNIQUEMENT VISIBLE DANS LE NAVIGATEUR, EXCLUE DU PDF GENERÉ) -->
  <div class="no-print" style="background: #1E3A5F; color: #FFFFFF; padding: 10px 16px; margin-bottom: 16px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
    <div style="font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 8px;">
      <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #22C55E;"></span>
      Fiche d'Inscription Officielle — GROUPE EICG
    </div>
    <button onclick="window.print();" style="background: #2563EB; color: #FFFFFF; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 6px;">
      🖨️ Imprimer / Exporter PDF
    </button>
  </div>
  <?php endif; ?>

  <!-- 1. EN-TÊTE OFFICIEL DU MINISTÈRE & PAYS -->
  <table class="header-table">
    <tr>
      <td class="header-left">
        <?= nl2br(htmlspecialchars($ministere)) ?>
      </td>
      <td class="header-right">
        <?= htmlspecialchars($pays) ?><br>
        <div class="header-motto"><?= htmlspecialchars($devise_pays) ?></div>
      </td>
    </tr>
  </table>

  <hr class="top-divider">

  <!-- 2. IDENTIFICATION DE L'ÉTABLISSEMENT (LOGO À GAUCHE & TITRE À DROITE) -->
  <table class="brand-table">
    <tr>
      <td class="brand-logo-cell">
        <img src="<?= $logoSrc ?>" alt="Logo EICG" class="brand-logo-img">
      </td>
      <td class="brand-title-cell">
        <h1 class="brand-title"><?= htmlspecialchars($universite) ?></h1>
      </td>
    </tr>
  </table>
  <div class="brand-dashed-line">----------------------------------------------------------------------------------------------------</div>

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
    <table class="info-grid-table">
      <tr>
        <td class="lbl" style="width: 175px;">Matricule MESRS :</td>
        <td class="val" colspan="3"><?= htmlspecialchars($matricule_mesrs) ?></td>
      </tr>
      <tr>
        <td class="lbl">Nom :</td>
        <td class="val" style="width: 230px;"><?= htmlspecialchars($nom) ?></td>
        <td class="lbl" style="width: 85px;">Prénoms :</td>
        <td class="val"><?= htmlspecialchars($prenoms) ?></td>
      </tr>
      <tr>
        <td class="lbl">Date et lieu de naissance :</td>
        <td class="val"><?= htmlspecialchars($date_lieu_naissance) ?></td>
        <td class="lbl">Nationalité :</td>
        <td class="val"><?= htmlspecialchars($nationalite) ?></td>
      </tr>
    </table>
  </div>

  <!-- 6. SECTION INSCRIPTION -->
  <div class="section-block">
    <div class="section-header">INSCRIPTION</div>
    <table class="info-grid-table">
      <tr>
        <td class="lbl" style="width: 175px;">Filière :</td>
        <td class="val" colspan="3"><?= htmlspecialchars($filiere) ?></td>
      </tr>
      <tr>
        <td class="lbl">Niveau :</td>
        <td class="val" colspan="3">
          <?= htmlspecialchars($niveau) ?>
          <?php if (!empty($specialite)): ?>
            &nbsp;|&nbsp; <span class="lbl">Spécialité:</span> <?= htmlspecialchars($specialite) ?>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td class="lbl">Type formation :</td>
        <td class="val" colspan="3"><?= htmlspecialchars($type_formation) ?></td>
      </tr>
    </table>
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
        <div class="qr-caption">
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
            <!-- Cercle extérieur bleu EICG -->
            <circle cx="80" cy="80" r="74" fill="none" stroke="#1E3A5F" stroke-width="2.5"/>
            <!-- Cercle intérieur pointillé -->
            <circle cx="80" cy="80" r="66" fill="none" stroke="#1E3A5F" stroke-width="1.2" stroke-dasharray="3 2"/>
            
            <!-- Texte circulaire supérieur -->
            <path id="cPathTop" d="M 22 80 A 58 58 0 0 1 138 80" fill="none"/>
            <text font-size="8" font-family="Arial" font-weight="bold" fill="#1E3A5F" text-anchor="middle">
              <textPath href="#cPathTop" startOffset="50%">GROUPE EICG - SCOLARITE</textPath>
            </text>

            <!-- Texte central dans cartouche -->
            <rect x="35" y="68" width="90" height="24" rx="4" fill="#FFFFFF" stroke="#1E3A5F" stroke-width="1.2"/>
            <text x="80" y="84" font-size="10" font-family="Arial" font-weight="bold" fill="#1E3A5F" text-anchor="middle" letter-spacing="1">
              GROUPE EICG
            </text>

            <!-- Texte inférieur -->
            <path id="cPathBot" d="M 138 80 A 58 58 0 0 1 22 80" fill="none"/>
            <text font-size="8" font-family="Arial" font-weight="bold" fill="#1E3A5F" text-anchor="middle">
              <textPath href="#cPathBot" startOffset="50%">★ DIRECTION ACADÉMIQUE ★</textPath>
            </text>

            <!-- Signature manuscrite stylisée superposée -->
            <path d="M 25 90 C 45 55 70 115 85 65 C 95 45 115 95 145 70 M 55 80 L 130 75" fill="none" stroke="#0F233D" stroke-width="2.2" stroke-linecap="round"/>
          </svg>
        </div>

        <div class="signatory-name"><?= htmlspecialchars($nom_signataire) ?></div>
      </td>
    </tr>
  </table>

  <!-- 9. PIED DE PAGE EXTRÊME : CODE-BARRES CENTRÉ TOUT EN BAS DU DOCUMENT -->
  <div class="page-footer-barcode-band">
    <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
      CODE D'IDENTIFICATION & DE SÉCURITÉ NUMÉRIQUE
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
