<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche d'Identification de l'Étudiant</title>
  <style>
    @page {
      margin: 6mm 8mm 6mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9.5px;
      color: #000000;
      line-height: 1.25;
    }

    /* Container Page Border */
    .page-frame {
      border: 1.5px solid #800000;
      padding: 10px 12px;
      box-sizing: border-box;
    }

    /* Header Institutionnel */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .inst-title {
      font-size: 12.5px;
      font-weight: bold;
      color: #800000;
      text-decoration: underline;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 10.5px;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 8.5px;
      font-style: italic;
      text-align: center;
      margin-top: 1px;
    }

    /* Bannières Titres */
    .banner-title {
      background: #800000;
      color: #FFFFFF;
      font-size: 13.5px;
      font-weight: bold;
      text-align: center;
      padding: 4px;
      margin-top: 4px;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .section-title {
      background: #F1F5F9;
      color: #800000;
      font-size: 10.5px;
      font-weight: bold;
      padding: 3px 6px;
      border-left: 4px solid #800000;
      margin-top: 6px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    /* Data Grids */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .data-table td {
      padding: 2.5px 4px;
      vertical-align: top;
      font-size: 9.5px;
    }
    .val-bold {
      font-weight: bold;
    }
    .photo-box {
      width: 85px;
      height: 102px;
      border: 1px solid #000000;
      object-fit: cover;
    }

    /* Checklist Pièces */
    .checklist-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2px;
      margin-bottom: 4px;
    }
    .checklist-table td {
      padding: 2px 4px;
      font-size: 9px;
      border: 1px solid #CBD5E1;
    }
    .chk-sq {
      display: inline-block;
      width: 10px;
      height: 10px;
      border: 1px solid #000000;
      text-align: center;
      line-height: 9px;
      font-weight: bold;
      font-size: 9px;
    }

    /* Financial Summary Box */
    .fin-summary-box {
      border: 1px solid #000000;
      background: #FAFAFA;
      padding: 4px 6px;
      margin-top: 4px;
      margin-bottom: 6px;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      margin-top: 10px;
      font-size: 9.5px;
    }
    .signature-grid td {
      vertical-align: top;
    }

    .footer-line {
      font-size: 8px;
      color: #64748B;
      text-align: right;
      margin-top: 6px;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- EN-TÊTE OFFICIELLE INSTITUTION -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 18%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 44px; max-width: 115px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#800000; font-size:13px;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 82%; text-align: center; vertical-align: middle;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE FDFP</div>
        <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
      </td>
    </tr>
  </table>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">
    FICHE D'IDENTIFICATION DE L'ÉTUDIANT
    <span style="font-size: 10px; font-weight: normal; float: right;">
      ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025 - 2026') ?>
    </span>
  </div>

  <!-- SECTION 1 : ÉTAT CIVIL & IDENTITÉ -->
  <div class="section-title">1. ÉTAT CIVIL & IDENTITÉ DE L'ÉTUDIANT</div>
  <table class="data-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 33%;">Matricule MESRS : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? 'YAYD0207010001') ?></span></td>
            <td style="width: 33%;">Code Inscription : <span class="val-bold"><?= htmlspecialchars($code_inscription ?? 'GE-25260129') ?></span></td>
            <td style="width: 34%;">N° Réf Interne : <span class="val-bold"><?= htmlspecialchars($num_gpeicg ?? 'YD-934/GEB/RHC25') ?></span></td>
          </tr>
          <tr>
            <td colspan="3">Nom & Prénom(s) : <span class="val-bold" style="font-size: 11px; color:#800000;"><?= htmlspecialchars(mb_strtoupper($nom_prenom_etudiant ?? 'YAYO DJEDJESS TRIJI JEAN JAURES')) ?></span></td>
          </tr>
          <tr>
            <td>Date de naissance : <span class="val-bold"><?= htmlspecialchars($date_naissance ?? '02/07/2001') ?></span></td>
            <td colspan="2">Lieu de naissance : <span class="val-bold"><?= htmlspecialchars($lieu_naissance ?? 'VIEIL-OUROU') ?></span></td>
          </tr>
          <tr>
            <td>Sexe : <span class="val-bold"><?= htmlspecialchars($sexe_etudiant ?? 'Masculin') ?></span></td>
            <td>Nationalité : <span class="val-bold"><?= htmlspecialchars($nationalite ?? 'Ivoirienne') ?></span></td>
            <td>Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
          </tr>
          <tr>
            <td>N° Pièce Identité : <span class="val-bold"><?= htmlspecialchars($num_piece_identite ?? 'C0123456789') ?></span></td>
            <td colspan="2">Contact Téléphonique : <span class="val-bold"><?= htmlspecialchars($contact_etudiant ?? '07 10 53 81 08') ?></span></td>
          </tr>
          <tr>
            <td>Email : <span class="val-bold"><?= htmlspecialchars($email_etudiant ?? 'etudiant@eicg.ci') ?></span></td>
            <td colspan="2">Adresse / Domicile : <span class="val-bold"><?= htmlspecialchars($adresse_etudiant ?? 'Bouaké Quartier Kennedy') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 102px; color: #94A3B8; font-size: 9px;">PHOTO ÉTUDIANT</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- SECTION 2 : CURSUS ACADÉMIQUE -->
  <div class="section-title">2. INSCRIPTION ACADÉMIQUE & CURSUS</div>
  <table class="data-table">
    <tr>
      <td style="width: 50%;">Filière d'études : <span class="val-bold" style="font-size: 10.5px;"><?= htmlspecialchars($filiere_libelle ?? 'Ressources Humaines et Communication') ?></span></td>
      <td style="width: 50%;">Niveau d'études : <span class="val-bold"><?= htmlspecialchars($niveau_libelle ?? 'Première Année') ?></span></td>
    </tr>
    <tr>
      <td>Classe attribuée : <span class="val-bold"><?= htmlspecialchars($classe_libelle ?? 'GBAT 1A') ?></span></td>
      <td>Redoublant(e) : <span class="val-bold"><?= htmlspecialchars($est_redoublant ?? 'NON') ?></span></td>
    </tr>
    <tr>
      <td>Dernier diplôme obtenu : <span class="val-bold"><?= htmlspecialchars($dernier_diplome ?? 'BAC A2') ?></span></td>
      <td>Année du diplôme : <span class="val-bold"><?= htmlspecialchars($annee_diplome ?? '2024') ?></span></td>
    </tr>
    <tr>
      <td colspan="2">Établissement d'origine : <span class="val-bold"><?= htmlspecialchars($etablissement_origine ?? 'Lycée Classique de Bouaké') ?></span></td>
    </tr>
  </table>

  <!-- SECTION 3 : PARENTS / TUTEURS LÉGAUX -->
  <div class="section-title">3. COORDONNÉES DES PARENTS ET TUTEURS</div>
  <table class="data-table">
    <tr>
      <td style="width: 50%;">Nom & Prénom du Père : <span class="val-bold"><?= htmlspecialchars($nom_pere ?? 'YAYO Kouadio') ?></span></td>
      <td style="width: 50%;">Contact Téléphonique Père : <span class="val-bold"><?= htmlspecialchars($contact_pere ?? '01 02 03 04 05') ?></span></td>
    </tr>
    <tr>
      <td>Nom & Prénom de la Mère : <span class="val-bold"><?= htmlspecialchars($nom_mere ?? 'KOFFI Amenan') ?></span></td>
      <td>Contact Téléphonique Mère : <span class="val-bold"><?= htmlspecialchars($contact_mere ?? '05 06 07 08 09') ?></span></td>
    </tr>
    <tr>
      <td>Nom & Prénom du Tuteur Légal : <span class="val-bold"><?= htmlspecialchars($nom_tuteur ?? 'YAYO DJEDJESS') ?></span></td>
      <td>Contact Urgence Tuteur : <span class="val-bold" style="color:#800000;"><?= htmlspecialchars($contact_tuteur ?? '07 08 09 10 11') ?></span></td>
    </tr>
    <tr>
      <td>Profession du Tuteur : <span class="val-bold"><?= htmlspecialchars($profession_tuteur ?? 'Enseignant') ?></span></td>
      <td>Domicile / Quartier Tuteur : <span class="val-bold"><?= htmlspecialchars($adresse_tuteur ?? 'Bouaké Air France') ?></span></td>
    </tr>
  </table>

  <!-- SECTION 4 : SITUATION FINANCIÈRE DE L'INSCRIPTION -->
  <div class="section-title">4. SITUATION FINANCIÈRE DE L'INSCRIPTION</div>
  <div class="fin-summary-box">
    <table style="width: 100%; font-size: 9.5px;">
      <tr>
        <td style="width: 25%;">Total Scolarité Fixé : <br><strong style="font-size: 10.5px;"><?= number_format($total_scolarite ?? 115000, 0, ',', ' ') ?> FCFA</strong></td>
        <td style="width: 25%;">Droit d'Inscription : <br><strong><?= number_format($frais_inscription ?? 15000, 0, ',', ' ') ?> FCFA</strong></td>
        <td style="width: 25%;">Total Déjà Payé : <br><strong style="color: #166534; font-size: 10.5px;"><?= number_format($montant_paye ?? 115000, 0, ',', ' ') ?> FCFA</strong></td>
        <td style="width: 25%; text-align: right;">Reste à Payer : <br><strong style="color: <?= ($reste_payer ?? 0) <= 0 ? '#166534' : '#990000' ?>; font-size: 11px;"><?= number_format($reste_payer ?? 0, 0, ',', ' ') ?> FCFA</strong></td>
      </tr>
    </table>
  </div>

  <!-- SECTION 5 : ÉTAT DES PIÈCES DU DOSSIER -->
  <div class="section-title">5. CONTRÔLE ET ÉTAT DES PIÈCES DU DOSSIER D'INSCRIPTION</div>
  <table class="checklist-table">
    <tr>
      <td style="width: 33%;">Photos d'identité (4) <span style="float:right;"><div class="chk-sq"><?= !empty($piece_photos) ? '✔' : '' ?></div></span></td>
      <td style="width: 33%;">Extrait d'Acte de Naissance <span style="float:right;"><div class="chk-sq"><?= !empty($piece_extrait) ? '✔' : '' ?></div></span></td>
      <td style="width: 34%;">Photocopie CNI / Attestation <span style="float:right;"><div class="chk-sq"><?= !empty($piece_cni) ? '✔' : '' ?></div></span></td>
    </tr>
    <tr>
      <td>Photocopie Diplôme / Collante <span style="float:right;"><div class="chk-sq"><?= !empty($piece_diplome) ? '✔' : '' ?></div></span></td>
      <td>Bulletins de notes N-1 <span style="float:right;"><div class="chk-sq"><?= !empty($piece_bulletins) ? '✔' : '' ?></div></span></td>
      <td>Fiche d'Affectation (Si affecté) <span style="float:right;"><div class="chk-sq"><?= !empty($piece_affectation) ? '✔' : '' ?></div></span></td>
    </tr>
  </table>

  <!-- SIGNATURES -->
  <table class="signature-grid">
    <tr>
      <td style="width: 50%;">
        <div style="font-weight: bold; text-decoration: underline;">Signature de l'Étudiant / Tuteur Légal</div>
        <div style="font-size: 8.5px; font-style: italic; margin-top: 2px;">"Lu et approuvé, certifié exact."</div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div style="font-size: 9px; margin-bottom: 2px;">Fait à Bouaké, le <?= date('d/m/Y') ?></div>
        <div style="font-weight: bold; text-decoration: underline;">Le Chef du Service Scolarité & Inscriptions</div>
        <div style="margin-top: 20px; font-weight: bold; font-size: 9.5px;"><?= htmlspecialchars($agent_scolarite_nom ?? 'Service Scolarité GEICG') ?></div>
      </td>
    </tr>
  </table>

  <!-- FOOTER HORODATAGE -->
  <div class="footer-line">
    Document officiel généré par le système informatique GEICG - Édition du <?= date('d/m/Y H:i:s') ?>
  </div>

</div>

</body>
</html>
