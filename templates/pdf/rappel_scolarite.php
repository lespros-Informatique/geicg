<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rappel de Scolarité</title>
  <style>
    @page {
      margin: 10mm 10mm 10mm 10mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11.5px;
      color: #000000;
      line-height: 1.3;
    }
    .page-frame {
      border: 1px solid #000000;
      padding: 12px 16px;
      box-sizing: border-box;
    }

    /* Header Institution */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .inst-title {
      font-size: 13px;
      font-weight: bold;
      color: #990000;
      text-decoration: none;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 11px;
      font-weight: bold;
      text-align: center;
      color: #004080;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 9.5px;
      font-style: italic;
      text-align: center;
      margin-top: 2px;
      margin-bottom: 4px;
    }

    /* Bannières Titres */
    .banner-title {
      background: #969696;
      color: #000000;
      font-size: 17px;
      font-weight: bold;
      text-align: center;
      padding: 5px;
      margin-top: 4px;
      margin-bottom: 6px;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .annee-header {
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 8px;
    }

    /* Info Table */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .info-table td {
      padding: 2.5px 4px;
      vertical-align: top;
      font-size: 11px;
    }
    .photo-box {
      width: 82px;
      height: 98px;
      border: 1px solid #000000;
      object-fit: cover;
    }
    .val-bold {
      font-weight: bold;
    }

    /* Tableau Financier Rappel */
    .fin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      margin-bottom: 12px;
    }
    .fin-table th {
      background: #B0B0B0;
      color: #000000;
      font-weight: bold;
      font-size: 11px;
      border: 1px solid #000000;
      padding: 5px 6px;
      text-align: center;
    }
    .fin-table td {
      border: 1px solid #000000;
      padding: 6px 8px;
      font-size: 11.5px;
      font-weight: bold;
      text-align: center;
    }

    /* Délais & Exigibilité */
    .exigible-box {
      margin-top: 10px;
      margin-bottom: 15px;
      font-size: 11.5px;
    }
    .exigible-row {
      margin-bottom: 8px;
    }

    /* Warning NB Box */
    .warning-box {
      background: #FFFF00;
      border: 1px solid #000000;
      padding: 6px 8px;
      font-weight: bold;
      font-size: 10.5px;
      margin-top: 12px;
      margin-bottom: 10px;
      color: #000000;
    }

    .footer-table {
      width: 100%;
      font-size: 9px;
      margin-top: 8px;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- EN-TÊTE INSTITUTIONNEL -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 20%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 48px; max-width: 130px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#990000; font-size:14px;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 80%; text-align: center; vertical-align: middle;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE FDFP</div>
      </td>
    </tr>
  </table>
  <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>

  <!-- BANNIÈRE TITRE -->
  <div class="banner-title">RAPPEL SCOLARITE</div>
  <div class="annee-header">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025-2026') ?></div>

  <!-- INFOS ÉTUDIANT -->
  <table class="info-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 48%;">N° <span class="val-bold"><?= htmlspecialchars($code_rappel ?? $code_inscription ?? 'GE-25260129') ?></span></td>
            <td style="width: 52%;">Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
          </tr>
          <tr>
            <td>Numéro réf étudiant( e) : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière_Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom_Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Contact : <span class="val-bold"><?= htmlspecialchars($contact_etudiant ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 98px; color: #94A3B8; font-size: 9px;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- TABLEAU FINANCIER RAPPEL SCOLARITÉ -->
  <table class="fin-table">
    <thead>
      <tr>
        <th style="width: 33.33%;">TOTAL SCOLARITE</th>
        <th style="width: 33.33%;">Montant payé</th>
        <th style="width: 33.34%;">Reste à payer</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= number_format($total_scolarite ?? 0, 0, ',', ' ') ?> CFA</td>
        <td><?= number_format($montant_paye ?? 0, 0, ',', ' ') ?>CFA</td>
        <td><?= number_format($reste_payer ?? 0, 0, ',', ' ') ?>CFA</td>
      </tr>
    </tbody>
  </table>

  <!-- DÉLAIS & MONTANT A PAYER -->
  <div class="exigible-box">
    <div class="exigible-row">
      Montant à payer : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <strong style="font-size: 13px;"><?= number_format($montant_exigible_du ?? $reste_payer ?? 0, 0, ',', ' ') ?>CFA</strong>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span style="font-size: 11.5px;"><?= htmlspecialchars($montant_exigible_lettres ?? 'Zéro franc CFA') ?></span>
    </div>
    
    <div class="exigible-row" style="margin-top: 8px;">
      Délai de rigueur : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <strong style="font-size: 13px;"><?= htmlspecialchars($delai_rigueur ?? '5 Février 2026; 18h00min') ?></strong>
    </div>
  </div>

  <!-- CAISSIER SIGNATURE -->
  <table style="width: 100%; margin-top: 10px;">
    <tr>
      <td></td>
      <td style="width: 50%; text-align: right;">
        <div style="font-weight: bold; text-decoration: underline; font-size: 11px;">CAISSIER(RE)</div>
        <div style="font-size: 11px; margin-top: 3px; font-weight: bold;"><?= htmlspecialchars($caissier_nom ?? 'Mlle KONE N\'diatty A. Mariam') ?></div>
      </td>
    </tr>
  </table>

  <!-- AVERTISSEMENT N.B -->
  <div class="warning-box">
    <span style="font-style: italic; text-decoration: underline;">N.B :</span> - Passer ce délai, l'accès au cours vous sera formellement interdit.<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Les paiements s'effectuent exclusivement à la caisse.
  </div>

  <!-- PIED DE PAGE RÉFÉRENCE & DATE -->
  <table class="footer-table">
    <tr>
      <td style="width: 70%; font-style: italic; font-size: 8.5px;"><?= htmlspecialchars($ref_caiss ?? 'GM-833/GEB/GBAT250819GE-25260129ScaisKON') ?></td>
      <td style="width: 30%; text-align: right; font-size: 8.5px;"><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></td>
    </tr>
  </table>

</div>

</body>
</html>
