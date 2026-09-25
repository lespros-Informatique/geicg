<?php
/**
 * Template de Fiche d'Inscription / Reçu d'Inscription Officiel (Format UVCI)
 * Emplacement : /views/templates/pdf/fiche_inscription.php
 */

// Données dynamiques avec fallbacks conformes au modèle officiel
$ministere = $ministere ?? "MINISTERE DE L'ENSEIGNEMENT SUPERIEUR\nET DE LA RECHERCHE SCIENTIFIQUE";
$pays = $pays ?? "REPUBLIQUE DE CÔTE D'IVOIRE";
$devise_pays = $devise_pays ?? "Union - Discipline - Travail";
$universite = $universite ?? "UNIVERSITE VIRTUELLE DE CÔTE D'IVOIRE";

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
$montant_paiement = $montant_paiement ?? (isset($paiement['montant_paiement']) ? number_format((float)$paiement['montant_paiement'], 0, ',', '.') . " F" : "60.000 F");
$date_paiement = $date_paiement ?? (isset($paiement['created_at_paiement']) ? date('d-m-Y', strtotime($paiement['created_at_paiement'])) : "26-10-2022");

$lieu_date_delivrance = $lieu_date_delivrance ?? ("Fait Abidjan le " . ($date_fiche ?? "12 Janvier 2023"));
$titre_signataire = $titre_signataire ?? "La Sous-Directrice de la Scolarité,\ndes Services Juridiques et de la Communication";
$nom_signataire = $nom_signataire ?? "Mme KADIO Julie Epse ASSALE";

$qr_data = $qr_data ?? ("UVCI-INSCRIPTION-" . $matricule_mesrs . "-" . $code_paiement);
$qr_code_url = $qr_code_url ?? ("https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($qr_data));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche d'Inscription - <?= htmlspecialchars($nom . ' ' . $prenoms) ?></title>
  <style>
    @page {
      size: A4 portrait;
      margin: 10mm 12mm 10mm 12mm;
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

    /* FILIGRANE DE FOND UVCI (WATERMARK) */
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
      margin-bottom: 6px;
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
      border-top: 1px solid #000000;
      margin: 6px 0 14px 0;
    }

    /* Etablissement Logo & Title */
    .univ-brand-container {
      text-align: center;
      margin-bottom: 12px;
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
      color: #1E293B;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin: 0;
    }

    .brand-dashed-line {
      text-align: center;
      font-weight: bold;
      letter-spacing: 2px;
      color: #334155;
      margin: 10px 0 16px 0;
      font-size: 10pt;
    }

    /* CADRE DU TITRE PRINCIPAL */
    .title-box-wrapper {
      text-align: center;
      margin: 14px 0 20px 0;
    }
    .title-box {
      display: inline-block;
      border: 2px solid #000000;
      border-radius: 4px;
      padding: 7px 42px;
      width: 78%;
      box-sizing: border-box;
      background: #FFFFFF;
    }
    .title-box-text {
      font-family: "Times New Roman", Times, Georgia, serif;
      font-size: 17pt;
      font-weight: 900;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #000000;
      margin: 0;
    }

    .annee-subtitle {
      font-size: 11.5pt;
      font-weight: bold;
      color: #000000;
      margin: 16px 0 22px 0;
    }

    /* SECTIONS DE FICHE */
    .section-block {
      margin-bottom: 18px;
    }
    .section-header {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      margin-bottom: 8px;
      letter-spacing: 0.5px;
    }

    .field-row {
      margin-bottom: 6px;
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
      margin-top: 14px;
      margin-bottom: 30px;
    }
    .payment-table th {
      background-color: #FDE047;
      border: 1px solid #CA8A04;
      font-size: 9pt;
      font-weight: bold;
      color: #000000;
      padding: 7px 8px;
      text-align: center;
    }
    .payment-table td {
      border: 1px solid #CBD5E1;
      font-size: 9pt;
      padding: 8px 10px;
      color: #000000;
    }
    .text-center { text-align: center; }
    .text-bold { font-weight: bold; }

    /* ZONE DE SIGNATURE ET CACHET */
    .signature-area {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }
    .signature-area td {
      vertical-align: top;
    }

    .qr-container {
      width: 140px;
      text-align: left;
    }
    .qr-code-img {
      width: 115px;
      height: 115px;
      border: 1px solid #CBD5E1;
      padding: 3px;
      background: #FFFFFF;
    }

    .signatory-container {
      text-align: right;
    }
    .date-delivrance {
      font-size: 9.5pt;
      color: #000000;
      margin-bottom: 12px;
    }
    .signatory-title {
      font-size: 9.5pt;
      font-weight: bold;
      color: #000000;
      line-height: 1.3;
      margin-bottom: 8px;
    }

    /* CACHET ROND DE L'UNIVERSITÉ EN SVG / STYLED HTML */
    .stamp-box {
      display: inline-block;
      position: relative;
      width: 170px;
      height: 110px;
      margin-top: 4px;
      margin-bottom: 6px;
    }
    
    .stamp-svg {
      width: 140px;
      height: 140px;
      position: absolute;
      right: 10px;
      top: -15px;
      opacity: 0.9;
    }

    .signatory-name {
      font-size: 10pt;
      font-weight: bold;
      color: #000000;
      margin-top: 8px;
      display: block;
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

  <!-- FILIGRANE DE FOND (SUBTLE BACKGROUND WATERMARK) -->
  <div class="watermark-bg">
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <circle cx="100" cy="100" r="90" fill="none" stroke="#166534" stroke-width="4"/>
      <circle cx="100" cy="100" r="78" fill="none" stroke="#166534" stroke-width="1.5" stroke-dasharray="4 2"/>
      <path d="M65,115 C65,70 135,70 135,115 C135,145 65,145 65,115 Z" fill="none" stroke="#7E22CE" stroke-width="6"/>
      <circle cx="100" cy="75" r="8" fill="#166534"/>
      <text x="100" y="170" font-size="14" font-family="Arial" font-weight="bold" fill="#166534" text-anchor="middle">UVCI</text>
    </svg>
  </div>

  <!-- BARRE D'IMPRESSION (MASQUÉE EN IMPRESSION PDF) -->
  <div class="no-print" style="background: #1E3A5F; color: #FFFFFF; padding: 10px 16px; margin-bottom: 20px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
    <div style="font-weight: bold; font-size: 13px; display: flex; align-items: center; gap: 8px;">
      <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #22C55E;"></span>
      Aperçu Officiel — Fiche d'Inscription Universitaire (Modèle Référence)
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

  <!-- 2. IDENTIFICATION DE L'ÉTABLISSEMENT / UNIVERSITÉ -->
  <div class="univ-brand-container">
    <div class="univ-logo-row">
      <!-- LOGO STYLISÉ UVCI -->
      <svg width="46" height="34" viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 20 C20 65 50 75 60 75 C70 75 100 65 100 20" stroke="#7E22CE" stroke-width="12" stroke-linecap="round" fill="none"/>
        <path d="M38 18 C38 52 56 60 60 60 C64 60 82 52 82 18" stroke="#166534" stroke-width="8" stroke-linecap="round" fill="none"/>
        <circle cx="60" cy="18" r="6" fill="#166534"/>
      </svg>
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

  <!-- 8. PIED DE PAGE : QR CODE, LIEU, CACHET ET SIGNATURE -->
  <table class="signature-area">
    <tr>
      <!-- COLONNE GAUCHE : QR CODE DE VÉRIFICATION -->
      <td class="qr-container">
        <img src="<?= htmlspecialchars($qr_code_url) ?>" alt="QR Code" class="qr-code-img">
      </td>

      <!-- COLONNE DROITE : DATE, QUALITÉ SIGNATAIRE, CACHET & NOM -->
      <td class="signatory-container">
        <div class="date-delivrance"><?= htmlspecialchars($lieu_date_delivrance) ?></div>
        
        <div class="signatory-title">
          <?= nl2br(htmlspecialchars($titre_signataire)) ?>
        </div>

        <!-- REPRÉSENTATION DU CACHET OFFICIEL ET DE LA SIGNATURE -->
        <div class="stamp-box">
          <svg class="stamp-svg" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
            <!-- Cercle extérieur bleu -->
            <circle cx="80" cy="80" r="74" fill="none" stroke="#1D4ED8" stroke-width="3"/>
            <!-- Cercle intérieur pointillé -->
            <circle cx="80" cy="80" r="65" fill="none" stroke="#1D4ED8" stroke-width="1.5" stroke-dasharray="4 2"/>
            
            <!-- Texte circulaire supérieur -->
            <path id="circlePathTop" d="M 22 80 A 58 58 0 0 1 138 80" fill="none"/>
            <text font-size="9" font-family="Arial" font-weight="bold" fill="#1D4ED8" text-anchor="middle">
              <textPath href="#circlePathTop" startOffset="50%">
                Université Virtuelle de Côte d'Ivoire
              </textPath>
            </text>

            <!-- Texte central dans cartouche -->
            <rect x="35" y="68" width="90" height="24" rx="4" fill="#EFF6FF" stroke="#1D4ED8" stroke-width="1.5"/>
            <text x="80" y="84" font-size="11" font-family="Arial" font-weight="900" fill="#1D4ED8" text-anchor="middle" letter-spacing="1">
              SCOLARITE
            </text>

            <!-- Étoiles décoratives -->
            <text x="26" y="98" font-size="10" fill="#1D4ED8">*</text>
            <text x="130" y="98" font-size="10" fill="#1D4ED8">*</text>

            <!-- Texte inférieur -->
            <path id="circlePathBottom" d="M 138 80 A 58 58 0 0 1 22 80" fill="none"/>
            <text font-size="9" font-family="Arial" font-weight="bold" fill="#1D4ED8" text-anchor="middle">
              <textPath href="#circlePathBottom" startOffset="50%">
                ★ UVCI ★
              </textPath>
            </text>

            <!-- Signature manuscrite stylisée en bleu marine superposée -->
            <path d="M 30 95 C 45 60 70 110 85 70 C 95 50 110 90 140 75 M 60 85 L 125 80" fill="none" stroke="#1E3A5F" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
        </div>

        <div class="signatory-name"><?= htmlspecialchars($nom_signataire) ?></div>
      </td>
    </tr>
  </table>

</body>
</html>
