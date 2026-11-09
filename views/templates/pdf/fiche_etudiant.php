<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

// Logo institutionnel dynamique
$resolvedLogoPath = null;
if (!empty($etablissement['logo_etablissement'])) {
    $candidatePath = __DIR__ . '/../../public/' . ltrim($etablissement['logo_etablissement'], '/');
    if (file_exists($candidatePath)) {
        $resolvedLogoPath = $candidatePath;
    }
}
if (!$resolvedLogoPath) {
    $defaultLogo = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
    if (file_exists($defaultLogo)) {
        $resolvedLogoPath = $defaultLogo;
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
      font-size: 8.8px;
      color: #0F172A;
      line-height: 1.22;
      background: #FFFFFF;
    }

    /* Cadre de la page */
    .page-frame {
      border: 1.5px solid #1E3A5F;
      padding: 9px 11px;
      box-sizing: border-box;
    }

    /* En-tête */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .inst-title {
      font-size: 12px;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      line-height: 1.15;
    }
    .inst-subtitle {
      font-size: 9px;
      font-weight: bold;
      text-align: center;
      color: #0284C7;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 8px;
      text-align: center;
      color: #64748B;
      margin-top: 1px;
    }

    /* Bannière Principale */
    .banner-title {
      background: #1E3A5F;
      color: #FFFFFF;
      font-size: 11.5px;
      font-weight: bold;
      text-align: center;
      padding: 4px 6px;
      margin-top: 3px;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .banner-sub {
      font-size: 8.5px;
      font-weight: normal;
      color: #E2E8F0;
      display: block;
      margin-top: 1px;
    }

    /* Titres de sections */
    .section-title {
      background: #F1F5F9;
      color: #1E3A5F;
      font-size: 9.5px;
      font-weight: bold;
      padding: 3px 6px;
      border-left: 3.5px solid #1E3A5F;
      margin-top: 5px;
      margin-bottom: 3px;
      text-transform: uppercase;
    }

    /* Tableaux de données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .data-table td {
      padding: 2px 4px;
      vertical-align: top;
      font-size: 8.8px;
    }
    .val-bold {
      font-weight: bold;
      color: #0F172A;
    }

    .photo-box {
      width: 82px;
      height: 96px;
      border: 1.5px solid #1E3A5F;
      border-radius: 4px;
      object-fit: cover;
    }

    /* Boîte décision de passage & Récap ancienne classe */
    .recap-box {
      background: #F8FAFC;
      border: 1px solid #CBD5E1;
      border-radius: 5px;
      padding: 5px 7px;
      margin-bottom: 4px;
    }
    .decision-banner {
      background: <?= $badgeBg ?>;
      color: <?= $badgeColor ?>;
      border: 1.5px solid <?= $badgeBorder ?>;
      border-radius: 5px;
      padding: 4px 8px;
      text-align: center;
      font-size: 10px;
      font-weight: bold;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    /* Boîte orientation nouvelle classe */
    .cible-box {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
      border-radius: 5px;
      padding: 5px 7px;
      margin-bottom: 4px;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    .signature-grid td {
      width: 33.33%;
      border: 1px solid #CBD5E1;
      padding: 5px 6px;
      vertical-align: top;
      background: #FAFAFA;
      font-size: 8.5px;
    }
    .sign-title {
      font-weight: bold;
      color: #1E3A5F;
      text-decoration: underline;
      font-size: 8.8px;
      margin-bottom: 2px;
    }

    .footer-line {
      font-size: 7.5px;
      color: #64748B;
      text-align: right;
      margin-top: 4px;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php 
    $showHeaderDivider = true;
    include __DIR__ . '/components/header_institution.php'; 
  ?>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">
    FICHE DE RÉINSCRIPTION & BILAN ACADÉMIQUE
    <span class="banner-sub">
      ANCIENNE SESSION (N-1) : <?= htmlspecialchars($annee_precedente ?? '-') ?> &nbsp;|&nbsp; 
      RÉINSCRIPTION CIBLE : <?= htmlspecialchars($annee_reinscription ?? '-') ?>
    </span>
  </div>

  <!-- SECTION 1 : IDENTITÉ DU CANDIDAT -->
  <div class="section-title">1. IDENTITÉ DE L'ÉTUDIANT(E)</div>
  <table class="data-table">
    <tr>
      <td style="width: 80%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 35%;">Matricule : <span class="val-bold" style="color:#1E3A5F; font-size:9.5px;"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td style="width: 35%;">Code Inscription N-1 : <span class="val-bold"><?= htmlspecialchars($code_inscription ?? '-') ?></span></td>
            <td style="width: 30%;">Régime : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="3">Nom & Prénom(s) : <span class="val-bold" style="font-size: 10.5px; color:#1E3A5F;"><?= htmlspecialchars(mb_strtoupper($nom_prenom_etudiant ?? '-')) ?></span></td>
          </tr>
          <tr>
            <td>Né(e) le : <span class="val-bold"><?= htmlspecialchars($date_naissance ?? '-') ?></span></td>
            <td colspan="2">À : <span class="val-bold"><?= htmlspecialchars($lieu_naissance ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Sexe : <span class="val-bold"><?= htmlspecialchars($sexe_etudiant ?? '-') ?></span></td>
            <td>Nationalité : <span class="val-bold"><?= htmlspecialchars($nationalite ?? 'Ivoirienne') ?></span></td>
            <td>Téléphone : <span class="val-bold"><?= htmlspecialchars($contact_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Email : <span class="val-bold"><?= htmlspecialchars($email_etudiant ?? '-') ?></span></td>
            <td colspan="2">Domicile / Résidence : <span class="val-bold"><?= htmlspecialchars($adresse_etudiant ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 20%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 96px; color: #94A3B8; font-size: 8px;">PHOTO ÉTUDIANT</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- SECTION 2 : RÉCAPITULATIF ANCIENNE CLASSE & DÉCISION DE PASSAGE -->
  <div class="section-title">2. RÉCAPITULATIF DE L'ANCIENNE CLASSE & DÉCISION DE FIN D'ANNÉE (N-1)</div>
  <div class="recap-box">
    <table style="width: 100%;">
      <tr>
        <td style="width: 34%;">Classe Fréquentée : <span class="val-bold" style="font-size:10px; color:#1E3A5F;"><?= htmlspecialchars($classe_precedente ?? '-') ?></span></td>
        <td style="width: 36%;">Filière : <span class="val-bold"><?= htmlspecialchars($filiere_precedente ?? '-') ?></span></td>
        <td style="width: 30%;">Niveau N-1 : <span class="val-bold"><?= htmlspecialchars($niveau_precedent ?? '-') ?></span></td>
      </tr>
      <tr>
        <td>Année Académique : <span class="val-bold"><?= htmlspecialchars($annee_precedente ?? '-') ?></span></td>
        <td>Moyenne Générale : <span class="val-bold" style="font-size:10px; color:#0F172A;"><?= htmlspecialchars($moyenne_n1 ?? 'Non renseignée') ?></span></td>
        <td>Mention : <span class="val-bold"><?= htmlspecialchars($mention_n1 ?? 'Non évalué') ?></span></td>
      </tr>
      <tr>
        <td colspan="3">Statut Redoublement N-1 : <span class="val-bold"><?= htmlspecialchars($est_redoublant ?? 'NON') ?></span></td>
      </tr>
    </table>

    <!-- DÉCISION OFFICIELLE DU CONSEIL / JURY DE PASSAGE -->
    <div class="decision-banner">
      DÉCISION DU JURY / CONSEIL DE PASSAGE : <?= htmlspecialchars(mb_strtoupper($decision_n1 ?? 'Autorisé(e) à se réinscrire')) ?>
    </div>
  </div>

  <!-- SECTION 3 : ORIENTATION & PROPOSITION POUR LA NOUVELLE CLASSE -->
  <div class="section-title">3. ORIENTATION & PROPOSITION DE RÉINSCRIPTION</div>
  <div class="cible-box">
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

  <!-- SECTION 4 : SITUATION FINANCIÈRE DE L'ANCIENNE SESSION (N-1) -->
  <div class="section-title">4. SITUATION COMPTABLE DE L'ANCIENNE CLASSE (N-1)</div>
  <table class="data-table" style="background: #FAFAFA; border: 1px solid #E2E8F0; padding: 4px;">
    <tr>
      <td style="width: 33%;">Scolarité N-1 Fixée : <br><strong style="font-size:9.5px;"><?= number_format((float)($total_scolarite_n1 ?? 0), 0, ',', ' ') ?> FCFA</strong></td>
      <td style="width: 33%;">Total Versé N-1 : <br><strong style="color:#15803D; font-size:9.5px;"><?= number_format((float)($total_paye_n1 ?? 0), 0, ',', ' ') ?> FCFA</strong></td>
      <td style="width: 34%; text-align: right;">Reliquat N-1 : <br>
        <?php if ($soldeRestantN1 <= 0): ?>
          <span style="background:#DCFCE7; color:#15803D; padding:2px 7px; border-radius:4px; font-weight:bold; font-size:9px;">SOLDÉ (0 FCFA)</span>
        <?php else: ?>
          <span style="background:#FEE2E2; color:#B91C1C; padding:2px 7px; border-radius:4px; font-weight:bold; font-size:9.5px;"><?= number_format($soldeRestantN1, 0, ',', ' ') ?> FCFA (IMPAYÉ)</span>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- SECTION 5 : COORDONNÉES DES PARENTS ET DU TUTEUR -->
  <div class="section-title">5. COORDONNÉES DES PARENTS ET TUTEUR LÉGAL</div>
  <table class="data-table">
    <tr>
      <td style="width: 50%;">Père : <span class="val-bold"><?= htmlspecialchars($nom_pere ?? '-') ?></span> (Tél: <?= htmlspecialchars($contact_pere ?? '-') ?>)</td>
      <td style="width: 50%;">Mère : <span class="val-bold"><?= htmlspecialchars($nom_mere ?? '-') ?></span> (Tél: <?= htmlspecialchars($contact_mere ?? '-') ?>)</td>
    </tr>
    <tr>
      <td>Tuteur Légal : <span class="val-bold"><?= htmlspecialchars($nom_tuteur ?? '-') ?></span> (Urgence: <span style="color:#B91C1C; font-weight:bold;"><?= htmlspecialchars($contact_tuteur ?? '-') ?></span>)</td>
      <td>Profession / Habitation : <span class="val-bold"><?= htmlspecialchars($profession_tuteur ?? '-') ?></span> - <?= htmlspecialchars($adresse_tuteur ?? '-') ?></td>
    </tr>
  </table>

  <!-- SECTION 6 : VISAS ET SIGNATURES OFFICIELLES -->
  <table class="signature-grid">
    <tr>
      <td>
        <div class="sign-title">L'Étudiant(e) / Tuteur Légal</div>
        <div style="font-size: 7.8px; font-style: italic; color: #64748B; margin-bottom: 30px;">"Lu et approuvé, certifié sincère et exact."</div>
        <div style="font-weight: bold; font-size: 8px;">Signature :</div>
      </td>
      <td>
        <div class="sign-title">La Direction des Études</div>
        <div style="font-size: 7.8px; color: #475569; margin-bottom: 24px;">Avis et Décision de passage validée</div>
        <div style="font-weight: bold; font-size: 8.5px; color:#1E3A5F;"><?= htmlspecialchars(!empty($etablissement['nom_directeur_etudes']) ? $etablissement['nom_directeur_etudes'] : 'La Direction des Études') ?></div>
      </td>
      <td style="text-align: right;">
        <div style="font-size: 8px; color: #64748B; margin-bottom: 2px;">Fait à <?= htmlspecialchars(!empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : (!empty($etablissement['ville_etablissement']) ? $etablissement['ville_etablissement'] : 'Bouaké')) ?>, le <?= date('d/m/Y') ?></div>
        <div class="sign-title" style="text-align: right;"><?= htmlspecialchars(!empty($etablissement['titre_service_scolarite']) ? $etablissement['titre_service_scolarite'] : 'Le Chef du Service Scolarité') ?></div>
        <div style="margin-top: 24px; font-weight: bold; font-size: 8.5px;"><?= htmlspecialchars(!empty($etablissement['nom_responsable_scolarite']) ? $etablissement['nom_responsable_scolarite'] : 'Service Scolarité GEICG') ?></div>
      </td>
    </tr>
  </table>

  <!-- PIED DE PAGE HORODATAGE -->
  <div class="footer-line">
    Document officiel généré par le système informatique <?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GEICG') ?> &bull; Édition du <?= date('d/m/Y H:i:s') ?>
  </div>

</div>

</body>
</html>
