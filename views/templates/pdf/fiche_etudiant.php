<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

// Logo institutionnel dynamique depuis configurations
$resolvedLogoPath = null;
if (!empty($etablissement['logo_etablissement'])) {
    $cleanLogo = ltrim($etablissement['logo_etablissement'], '/');
    foreach ([__DIR__ . '/../../../public/' . $cleanLogo, __DIR__ . '/../../../' . $cleanLogo] as $candidatePath) {
        if (file_exists($candidatePath) && is_file($candidatePath)) {
            $resolvedLogoPath = $candidatePath;
            break;
        }
    }
}

// Styles conditionnels selon la décision
$badgeBg = '#EFF6FF';
$badgeColor = '#1E3A5F';
$badgeBorder = '#BFDBFE';
if (($decision_badge ?? '') === 'success') {
    $badgeBg = '#DCFCE7';
    $badgeColor = '#15803D';
    $badgeBorder = '#86EFAC';
} elseif (($decision_badge ?? '') === 'warning') {
    $badgeBg = '#FEF3C7';
    $badgeColor = '#B45309';
    $badgeBorder = '#FCD34D';
} elseif (($decision_badge ?? '') === 'danger') {
    $badgeBg = '#FEE2E2';
    $badgeColor = '#B91C1C';
    $badgeBorder = '#FCA5A5';
}

$soldeRestantN1 = (float)($solde_n1 ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche de Réinscription & Bilan Académique - <?= htmlspecialchars($nom_prenom_etudiant ?? '') ?></title>
  <style>
    @page {
      margin: 6mm 7mm 6mm 7mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9.5px;
      color: #0F172A;
      line-height: 1.32;
      background: #FFFFFF;
    }

    /* Cadre pleine page A4 natif mPDF (position:fixed relatif à la zone de marge @page) */
    .page-border {
      position: fixed;
      top: 0mm;
      left: 0mm;
      width: 196mm;
      height: 284mm;
      border: 1.5px solid #1E3A5F;
    }
    .page-frame {
      padding: 10px 12px 14px 12px;
      box-sizing: border-box;
    }
    .footer-line {
      position: fixed;
      top: 276.5mm;
      left: 3.5mm;
      width: 189mm;
      font-size: 8px;
      color: #64748B;
      text-align: right;
      border-top: 0.5px solid #CBD5E1;
      padding-top: 3px;
      font-style: italic;
    }

    /* En-tête */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 5px;
    }
    .inst-title {
      font-size: 12.5px;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      line-height: 1.18;
    }
    .inst-subtitle {
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
      color: #0284C7;
      margin-top: 2px;
    }
    .inst-contacts {
      font-size: 8.5px;
      text-align: center;
      color: #64748B;
      margin-top: 2px;
    }

    /* Bannière Principale */
    .banner-title {
      border: 2px solid #373839;
      border-radius: 5px;
      height: 35px;
      font-size: 20px;
      font-weight: bold;
      text-align: center;
      line-height: 40px;
      padding: 6px 8px;
      margin-top: 15px;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;

    }
    .banner-sub {
      font-size: 9px;
      font-weight: normal;
      color: #E2E8F0;
      display: block;
      margin-top: 2px;
    }

    /* Titres de sections */
    .section-title {
      color: #202020;
      font-size: 16px;
      font-weight: bold;
      padding: 5px 8px;
      margin-top: 13px;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    /* Tableaux de données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .data-table td {
      padding: 4px 5px;
      vertical-align: top;
      font-size: 13px;
    }
    .val-bold {
      font-weight: bold;
      color: #0F172A;
    }

  
    .photo-frame-cell {
      width: 90px;
      height: 102px;
      text-align: center;
      vertical-align: middle;
      padding: 4px;
      background: #F8FAFC;
    }
    .photo-img {
      width: 120px;
      height: 130px;
      border: 1px solid #2C2C2D;
      display: block;
    }

    /* Boîte décision de passage & Récap ancienne classe */
    .recap-box {
      background: #F8FAFC;
      border: 1px solid #CBD5E1;
      border-radius: 5px;
      padding: 12px 14px;
      margin-bottom: 10px;
      margin-top: 8px;
    }
    .recap-box table {
      width: 100%;
      border-collapse: collapse;
    }
    .recap-box td {
      font-size: 12.5px;
      line-height: 1.65;
      padding: 6px 4px;
      vertical-align: top;
    }
    .decision-banner {
      background: <?= $badgeBg ?>;
      color: <?= $badgeColor ?>;
      border: 1.5px solid <?= $badgeBorder ?>;
      border-radius: 5px;
      padding: 8px 12px;
      text-align: center;
      font-size: 12px;
      line-height: 1.45;
      font-weight: bold;
      margin-top: 10px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    /* Tableau Scolarité Année Antérieure */
    .scolarite-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      margin-bottom: 10px;
    }
    .scolarite-table th {
      background-color: #153D66;
      color: #FFFFFF;
      font-size: 13px;
      font-weight: bold;
      text-transform: uppercase;
      text-align: center;
      padding: 8px 10px;
      border: 1px solid #4B484F;
      letter-spacing: 0.3px;
    }
    .scolarite-table td {
      background-color: #FFFFFF;
      font-size: 13.5px;
      font-weight: bold;
      text-align: center;
      padding: 10px 8px;
      border: 1px solid #CBD5E1;
      vertical-align: middle;
    }

    /* Boîte orientation nouvelle classe */
    .cible-box {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
      border-radius: 5px;
      padding: 9px 10px;
      margin-bottom: 8px;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      border-collapse: collapse;
      margin-top: 38px;
    }
    .signature-grid td {
      width: 50%;
      border: none;
      padding: 8px 12px;
      vertical-align: top;
      font-size: 11px;
    }
    .sign-title {
      font-weight: bold;
      color: #1E3A5F;
      text-decoration: underline;
      font-size: 13.5px;
      margin-bottom: 6px;
    }
  </style>
</head>
<body>

<div class="page-border"></div>
<div class="page-frame">

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php 
    $showHeaderDivider = true;
    include __DIR__ . '/components/header_institution.php'; 
  ?>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">
    FICHE DE RÉINSCRIPTION & BILAN ACADÉMIQUE
  </div>

  <!-- SECTION 1 : IDENTITÉ DU CANDIDAT -->
  <div class="section-title"> IDENTITÉ DE L'ÉTUDIANT(E)</div>
  <table class="data-table">
    <tr>
      <td style="width: 80%; vertical-align: top;">
        <table style="width: 100%; border-collapse: collapse;">
          <tr>
            <td colspan="2">Nom &amp; Prénom(s) : <span class="val-bold" style="color:#1E3A5F;"><?= htmlspecialchars(mb_strtoupper($nom_prenom_etudiant ?? '-')) ?></span></td>
          </tr>
          <tr>
            <td style="width: 50%;">Matricule : <span class="val-bold" style="color:#1E3A5F;"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td style="width: 50%;">Sexe : <span class="val-bold"><?= htmlspecialchars($sexe_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Date de naissance : <span class="val-bold"><?= htmlspecialchars($date_naissance ?? '-') ?></span></td>
            <td>Lieu de naissance : <span class="val-bold"><?= htmlspecialchars($lieu_naissance ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Nationalité : <span class="val-bold"><?= htmlspecialchars($nationalite ?? '-') ?></span></td>
            <td>Téléphone : <span class="val-bold"><?= htmlspecialchars($contact_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Email : <span class="val-bold"><?= htmlspecialchars($email_etudiant ?? '-') ?></span></td>
            <td> Résidence : <span class="val-bold"><?= htmlspecialchars($adresse_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Redoublant(e) : <span class="val-bold"><?= htmlspecialchars($est_redoublant ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 20%; text-align: right; vertical-align: top; padding-top: 2px;">
        <?php
          $resolvedPhoto = '';
          if (!empty($photo_etudiant)) {
              $cleanPhoto = ltrim($photo_etudiant, '/');
              foreach ([__DIR__ . '/../../../public/' . $cleanPhoto, __DIR__ . '/../../../' . $cleanPhoto] as $candPhoto) {
                  if (file_exists($candPhoto) && is_file($candPhoto)) {
                      $resolvedPhoto = $candPhoto;
                      break;
                  }
              }
          }
          $placeholderPhoto = (function_exists('placeholderPath') && file_exists(placeholderPath('etudiant')))
              ? placeholderPath('etudiant')
              : '';
        ?>
        <table align="right">
          <tr>
            <td class="photo-frame-cell">
                <img src="<?= !empty($resolvedPhoto) ? $resolvedPhoto : $placeholderPhoto ?>" class="photo-img" alt="Photo Étudiant">
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<hr>
  <!-- SECTION 2 : RÉCAPITULATIF ANCIENNE CLASSE & DÉCISION DE PASSAGE -->
  <div class="section-title"> RÉCAPITULATIF DE L'ANNÉE ACADÉMIQUE <?= $annee_precedente ?> </div>
  <div class="recap-box">
    <table style="width: 100%;">
      <tr>
        <td style="width: 34%;">Classe Fréquentée : <span class="val-bold" style="color:#1E3A5F;"><?= htmlspecialchars($classe_precedente ?? '-') ?></span></td>
        <td style="width: 36%;">Filière : <span class="val-bold"><?= htmlspecialchars($filiere_precedente ?? '-') ?></span></td>
        <td style="width: 30%;">Niveau N-1 : <span class="val-bold"><?= htmlspecialchars($niveau_precedent ?? '-') ?></span></td>
      </tr>
      <tr>
        <td>Année Académique : <span class="val-bold"><?= htmlspecialchars($annee_precedente ?? '-') ?></span></td>
        <td>Moyenne Générale : <span class="val-bold" style="color:#0F172A;"><?= htmlspecialchars($moyenne_n1 ?? 'Non renseignée') ?></span></td>
        <td>Mention : <span class="val-bold"><?= htmlspecialchars($mention_n1 ?? 'Non évalué') ?></span></td>
      </tr>
      <tr>
        <td colspan="3">Code Inscription : <span class="val-bold" style="color:#1E3A5F;"><?= htmlspecialchars($code_inscription ?? '-') ?></span></td>
      </tr>
    </table>

    <!-- DÉCISION OFFICIELLE DU CONSEIL / JURY DE PASSAGE -->
    <div class="decision-banner">
      DÉCISION DU JURY : <?= htmlspecialchars(mb_strtoupper($decision_n1 ?? 'Autorisé(e) à se réinscrire')) ?>
    </div>
  </div>
<br>
<hr>
    <!-- SECTION 3: SITUATION FINANCIÈRE -->
  <div class="section-title"> SITUATION FINANCIÈRE DE L'ANNÉE (<?= $annee_precedente ?> ) </div>
  <table class="scolarite-table">
    <thead>
      <tr>
        <th style="width: 33.33%;">Scolarité Fixée</th>
        <th style="width: 33.33%;">Total Versé</th>
        <th style="width: 33.34%;">Reliquat</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="color: #0F172A;"><?= number_format((float)($total_scolarite_n1 ?? 0), 0, ',', ' ') ?> FCFA</td>
        <td style="color: #0F172A;"><?= number_format((float)($total_paye_n1 ?? 0), 0, ',', ' ') ?> FCFA</td>
        <td>
          <?php if ($soldeRestantN1 <= 0): ?>
            <span style="background:#DCFCE7; color:#15803D; padding:3px 9px; border-radius:4px; font-weight:bold; font-size:11.5px;">SOLDÉ (0 FCFA)</span>
          <?php else: ?>
            <span style="background:#FEE2E2; color:#B91C1C; padding:3px 9px; border-radius:4px; font-weight:bold; font-size:11.5px;"><?= number_format($soldeRestantN1, 0, ',', ' ') ?> FCFA (IMPAYÉ)</span>
          <?php endif; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- SECTION 4 : ORIENTATION & PROPOSITION POUR LA NOUVELLE CLASSE -->
  <div style="display: none;" class="section-title">ORIENTATION & PROPOSITION DE RÉINSCRIPTION</div>
  <div style="display: none;" class="cible-box">
    <table style="width: 100%;">
      <tr>
        <td style="width: 34%;">Nouvelle Classe Suggérée : <span class="val-bold" style="font-size:10.5px; color:#1E3A5F;"><?= htmlspecialchars($classe_cible ?? '-') ?></span></td>
        <td style="width: 36%;">Filière Cible : <span class="val-bold"><?= htmlspecialchars($filiere_cible ?? '-') ?></span></td>
        <td style="width: 30%;">Niveau Cible : <span class="val-bold"><?= htmlspecialchars($niveau_cible ?? '-') ?></span></td>
      </tr>
      <tr>
        <td>Session Académique : <span class="val-bold"><?= htmlspecialchars($annee_reinscription ?? '-') ?></span></td>
        <td colspan="2">Frais de Scolarité Cible : <span class="val-bold" style="color:#15803D; font-size:10px;"><?= number_format((float)($scolarite_cible ?? 0), 0, ',', ' ') ?> FCFA</span></td>
      </tr>
    </table>
  </div>

<br>
<br>

  <!-- SIGNATURES (2 COLONNES) -->
  <table class="signature-grid">
    <tr>
      <td style="text-align: left; vertical-align: middle;">
        <?php
          $qrParts = [];
          if (!empty($matricule_etudiant) && $matricule_etudiant !== '-') {
              $qrParts[] = 'Matricule: ' . $matricule_etudiant;
          }
          if (!empty($code_inscription) && $code_inscription !== '-') {
              $qrParts[] = 'Code Inscription: ' . $code_inscription;
          }
          $qrPayload = implode(' | ', $qrParts);
        ?>
        <?php if ($qrPayload !== ''): ?>
          <barcode code="<?= htmlspecialchars($qrPayload, ENT_QUOTES, 'UTF-8') ?>" type="QR" size="1.15" error="M" disableborder="1" />
        <?php endif; ?>
      </td>
      <td style="text-align: right;">
        <div style="font-size: 12.5px; color: #334155; margin-bottom: 10px;">
          Fait à <?= htmlspecialchars(!empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : ($etablissement['ville_etablissement'] ?? '')) ?>, le <?= date('d/m/Y') ?>
        </div>
        <br>
        <div class="sign-title" style="text-align: right;">
          <?= htmlspecialchars(!empty($etablissement['titre_direction_pedagogique']) ? $etablissement['titre_direction_pedagogique'] : '') ?>
        </div>
        <br>
        <div style="margin-top:13px;padding-top: 46px; font-weight: bold; font-size: 13px; color: #46484A;">
          <?= htmlspecialchars(!empty($etablissement['nom_directeur_etudes']) ? $etablissement['nom_directeur_etudes'] : '') ?>
        </div>
      </td>
    </tr>
  </table>

</div>



</body>
</html>
