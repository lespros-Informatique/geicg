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

  <!-- EN-TÊTE -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
    $logoSrc = '';
    if (file_exists($logoPath)) {
      $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
    }

    $photoSrc = '';
    if (!empty($photo_etudiant)) {
      $cleanP = ltrim($photo_etudiant, '/');
      $candidateP = __DIR__ . '/../../public/' . $cleanP;
      if (file_exists($candidateP)) {
        $ext = pathinfo($candidateP, PATHINFO_EXTENSION);
        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
        $photoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($candidateP));
      }
    }
    if (empty($photoSrc)) {
      $defaultPlaceholder = __DIR__ . '/../../public/assets/images/placeholders/etudiant.png';
      if (file_exists($defaultPlaceholder)) {
        $photoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultPlaceholder));
      }
    }
  ?>
  <!-- EN-TÊTE INSTITUTION (Inspiré de recu_versement.php) -->
  <table class="header-table">
    <tr>
      <td style="width: 15%; vertical-align: middle;">
        <?php if (!empty($logo_src)): ?>
          <img src="<?= $logo_src ?>" style="max-height: 48px; max-width: 110px;">
        <?php elseif (!empty($logoSrc)): ?>
          <img src="<?= $logoSrc ?>" style="max-height: 48px; max-width: 110px;">
        <?php else: ?>
          <div class="logo-box">GROUPE<br>EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 85%; text-align: center;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">Agréé par l'Etat et le FDFP</div>
        <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
      </td>
    </tr>
  </table>

  <!-- BANNIÈRE TITRE -->
  <div class="banner-title">RAPPEL DE SCOLARITÉ</div>
  <div class="annee-header">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '-') ?></div>

  <!-- INFOS ÉTUDIANT & PHOTO -->
  <table class="info-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 50%;">
              N° <span class="val-bold"><?= htmlspecialchars($code_rappel ?? ($code_inscription ?? '-')) ?></span>
            </td>
            <td style="width: 50%; color: #666666;"><?= htmlspecialchars(date('d/m/Y')) ?></td>
          </tr>
          <tr>
            <td>Numéro réf étudiant( e) : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière_Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom_Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
            <td>Contact : <span class="val-bold"><?= htmlspecialchars($contact_etudiant ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_src)): ?>
          <img src="<?= $photo_src ?>" class="photo-box">
        <?php elseif (!empty($photoSrc)): ?>
          <img src="<?= $photoSrc ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 90px; color: #94A3B8; font-size: 9px;">PHOTO</div>
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

  <!-- DÉLAIS & MONTANT A PAYER AVEC CODE-BARRES -->
  <table style="width: 100%; margin-top: 8px; margin-bottom: 12px; border-collapse: collapse;">
    <tr>
      <td style="width: 72%; vertical-align: middle;">
        <div class="exigible-box" style="margin: 0;">
          <div class="exigible-row">
            Montant à payer : &nbsp;&nbsp;&nbsp;&nbsp;
            <strong style="font-size: 13px; color: #800000;"><?= number_format($montant_exigible_du ?? $reste_payer ?? 0, 0, ',', ' ') ?> CFA</strong>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span style="font-size: 10.5px; font-weight: bold; font-style: italic; color: #000000;"><?= htmlspecialchars($montant_exigible_lettres ?? '') ?></span>
          </div>
          
          <div class="exigible-row" style="margin-top: 8px;">
            Délai de rigueur : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <strong style="font-size: 13px; color: #000000;"><?= htmlspecialchars($delai_rigueur ?? '-') ?></strong>
          </div>
        </div>
      </td>
      <td style="width: 28%; text-align: right; vertical-align: middle;">
        <div style="text-align: center; display: inline-block;">
          <barcode code="<?= htmlspecialchars($code_barre_val ?? $code_rappel ?? ($code_inscription ?? 'RAP-2026-001')) ?>" type="C128A" size="0.65" height="0.6" />
          <div style="font-size: 8px; font-family: monospace; font-weight: bold; margin-top: 2px;">* <?= htmlspecialchars($code_barre_val ?? $code_rappel ?? ($code_inscription ?? 'RAP-2026-001')) ?> *</div>
        </div>
      </td>
    </tr>
  </table>

  <!-- CAISSIER SIGNATURE -->
  <table style="width: 100%; margin-top: 10px;">
    <tr>
      <td></td>
      <td style="width: 50%; text-align: right;">
        <div style="font-weight: bold; text-decoration: underline; font-size: 11px;">CAISSIER(RE)</div>
        <div style="font-size: 11px; margin-top: 3px; font-weight: bold;"><?= htmlspecialchars($caissier_nom ?? '-') ?></div>
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
      <td style="width: 70%; font-style: italic; font-size: 8.5px;"><?= htmlspecialchars($ref_caiss ?? '-') ?></td>
      <td style="width: 30%; text-align: right; font-size: 8.5px;"><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></td>
    </tr>
  </table>

</div>

</body>
</html>
