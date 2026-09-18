<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu d'Inscription Officiel</title>
  <style>
    @page {
      margin: 8mm 8mm 8mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #000000;
      line-height: 1.25;
    }
    .page-frame {
      border: 2px solid #800000;
      padding: 10px 14px;
      box-sizing: border-box;
    }

    /* Header Institution */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .logo-box {
      width: 60px;
      height: 50px;
      background: #800000;
      color: #FFFFFF;
      text-align: center;
      font-weight: bold;
      font-size: 11px;
      padding-top: 4px;
      display: inline-block;
    }
    .inst-title {
      font-size: 13.5px;
      font-weight: bold;
      color: #800000;
      text-decoration: underline;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 11px;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 2px;
    }
    .inst-contacts {
      font-size: 10px;
      font-style: italic;
      text-align: center;
      color: #000000;
      margin-top: 1px;
    }

    /* Bannières Titres */
    .banner-title {
      background: #808080;
      color: #000000;
      font-size: 15px;
      font-weight: bold;
      text-align: center;
      padding: 4px;
      margin-top: 4px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }
    .banner-copy {
      background: #808080;
      color: #000000;
      font-size: 13.5px;
      font-weight: bold;
      text-align: center;
      padding: 3px;
      margin-top: 12px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .annee-header {
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 6px;
    }

    /* Grille d'Infos Étudiant */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .info-table td {
      padding: 2px 4px;
      vertical-align: top;
      font-size: 10.5px;
    }
    .photo-box {
      width: 75px;
      height: 90px;
      border: 1px solid #000000;
      object-fit: cover;
    }
    .val-bold {
      font-weight: bold;
    }
    .text-blue {
      color: #0000D0;
      font-weight: bold;
    }

    /* Tableau Financier */
    .fin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
    }
    .fin-table th, .fin-table td {
      border: 1px solid #000000;
      padding: 3px 5px;
      font-size: 10px;
    }
    .fin-table th {
      background: #FFFFFF;
      font-weight: bold;
      text-align: center;
    }
    .fin-table td.col-label {
      font-weight: bold;
      width: 25%;
    }
    .fin-table td.col-num {
      text-align: center;
      font-weight: bold;
      width: 18.75%;
    }
    .fin-table tr.total-row td {
      background: #808080;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 11px;
    }

    /* Badges & Warning */
    .badge-solde {
      background: #808080;
      color: #800000;
      font-size: 13px;
      font-weight: bold;
      padding: 3px 14px;
      display: inline-block;
      text-align: center;
    }
    .warning-box {
      background: #FFFF00;
      border: 1px solid #000000;
      padding: 4px 8px;
      font-weight: bold;
      font-size: 9.5px;
      margin-top: 6px;
      margin-bottom: 6px;
    }

    /* Footer & Signatures */
    .footer-note {
      font-size: 9.5px;
      margin-top: 4px;
    }
    .signature-area {
      text-align: right;
      margin-top: -20px;
    }
    .signature-title {
      font-weight: bold;
      text-decoration: underline;
      font-size: 10px;
    }
    .signature-name {
      font-size: 10.5px;
      margin-top: 2px;
    }

    .dashed-sep {
      border-top: 1px dashed #000000;
      margin-top: 8px;
      margin-bottom: 6px;
    }
    .ref-caiss {
      font-size: 9px;
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
      <td style="width: 15%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 48px; max-width: 110px;">
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

  <!-- BANNIÈRE 1 : REÇU D'INSCRIPTION -->
  <div class="banner-title">REÇU D'INSCRIPTION</div>
  <div class="annee-header">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025-2026') ?></div>

  <!-- INFOS ÉTUDIANT ORIGINAL -->
  <table class="info-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 50%;">
              N° <span class="val-bold"><?= htmlspecialchars($code_inscription ?? 'GE-25260276') ?></span>
              <div style="margin-top: 2px;">
                <barcode code="<?= htmlspecialchars($code_inscription ?? 'GE-25260276') ?>" type="C128A" size="0.6" height="0.6" />
              </div>
            </td>
            <td style="width: 50%;"><?= htmlspecialchars($date_operation ?? date('d/m/Y H:i:s')) ?></td>
          </tr>
          <tr>
            <td>Numéro réf étudiant( e) : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière_Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom_Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Type d'Opération : <span class="val-bold"><?= htmlspecialchars($type_operation ?? '-') ?></span></td>
            <td>Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
          </tr>
          <tr>
            <td>Montant de l'Opération : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?>CFA</span></td>
            <td class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 90px; color: #94A3B8; font-size: 9px;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- TABLEAU FINANCIER DÉTAILLÉ -->
  <table class="fin-table">
    <thead>
      <tr>
        <th style="width: 25%;"></th>
        <th style="width: 18.75%;">OP. DU JOUR</th>
        <th style="width: 18.75%;">Total à payer</th>
        <th style="width: 18.75%;">Total Versé</th>
        <th style="width: 18.75%;">Reste à Payer</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="col-label">SCOLARITE</td>
        <td class="col-num"><?= isset($scolarite_op_du_jour) ? number_format($scolarite_op_du_jour, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"><?= isset($scolarite_total_payer) ? number_format($scolarite_total_payer, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"><?= isset($scolarite_total_verse) ? number_format($scolarite_total_verse, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"><?= isset($scolarite_reste_payer) ? number_format($scolarite_reste_payer, 0, ',', ' ') . 'CFA' : '0CFA' ?></td>
      </tr>
      <tr>
        <td class="col-label">Droit d'Inscription</td>
        <td class="col-num"><?= isset($droit_op_du_jour) ? number_format($droit_op_du_jour, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"></td>
        <td class="col-num"><?= isset($droit_total_verse) ? number_format($droit_total_verse, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"></td>
      </tr>
      <tr>
        <td class="col-label">AUTRES FRAIS</td>
        <td class="col-num"><?= isset($autres_op_du_jour) && $autres_op_du_jour > 0 ? number_format($autres_op_du_jour, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"></td>
        <td class="col-num"><?= isset($autres_total_verse) && $autres_total_verse > 0 ? number_format($autres_total_verse, 0, ',', ' ') . 'CFA' : '' ?></td>
        <td class="col-num"></td>
      </tr>
      <tr class="total-row">
        <td class="col-label" style="background: #808080;">TOTAL</td>
        <td class="col-num" style="background: #808080;"><?= number_format($total_op_du_jour ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num" style="background: #808080;"><?= number_format($total_payer ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num" style="background: #808080;"><?= number_format($total_verse ?? 0, 0, ',', ' ') ?>CFA</td>
        <td class="col-num" style="background: #808080;"><?= number_format($total_reste_payer ?? 0, 0, ',', ' ') ?>CFA</td>
      </tr>
    </tbody>
  </table>

  <!-- STATUT PROCHAIN PAIEMENT & SIGNATURE CAISSIER -->
  <table style="width: 100%; margin-top: 4px;">
    <tr>
      <td style="width: 60%;">
        Date du Prochain Payement : 
        <span class="badge-solde"><?= htmlspecialchars($date_prochain_paiement ?? 'SOLDÉ') ?></span>
      </td>
      <td style="width: 40%; text-align: right;">
        <div class="signature-title">CAISSIER(RE)</div>
        <div class="signature-name"><?= htmlspecialchars($caissier_nom ?? 'Mlle KONE N\'diatty A. Mariam') ?></div>
      </td>
    </tr>
  </table>

  <!-- AVERTISSEMENT N.B -->
  <div class="warning-box">
    N.B: - Aucun remboursement n'est admis après l'inscription.<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Les paiements se font uniquement chez la caissière
  </div>

  <table style="width: 100%;" class="footer-note">
    <tr>
      <td style="width: 70%;">Ce reçu est à conserver. Pour toute réclamation il doit être présenté.</td>
      <td style="width: 30%; text-align: right;"><?= htmlspecialchars($date_impression ?? date('26/08/2026 13:13:53')) ?></td>
    </tr>
  </table>

  <div class="dashed-sep"></div>
  <div class="ref-caiss">Réf_caiss <?= htmlspecialchars($ref_caiss ?? 'GE-25260276ScoFOF45944,4399676042ScaisKON') ?></div>

  <!-- ========================================================================= -->
  <!-- BANNIÈRE 2 : COPIE REÇU DE VERSEMENT POUR ARCHIVAGE                        -->
  <!-- ========================================================================= -->
  <div class="banner-copy">COPIE REÇU DE VERSEMENT POUR ARCHIVAGE</div>
  <div class="annee-header" style="font-size: 11.5px; margin-bottom: 4px;">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025-2026') ?></div>

  <table class="info-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 50%;">
              N° <span class="val-bold"><?= htmlspecialchars($code_inscription ?? 'GE-25260276') ?></span>
              <div style="margin-top: 2px;">
                <barcode code="<?= htmlspecialchars($code_inscription ?? 'GE-25260276') ?>" type="C128A" size="0.6" height="0.6" />
              </div>
            </td>
            <td style="width: 50%; color: #94A3B8;"><?= htmlspecialchars($date_operation ?? date('14/10/2025 10:33:33')) ?></td>
          </tr>
          <tr>
            <td>Numéro réf étudiant( e) : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Filière_Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom_Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Type d'Opération : <span class="val-bold"><?= htmlspecialchars($type_operation ?? '-') ?></span></td>
            <td>Banque : </td>
          </tr>
          <tr>
            <td>Montant de l'Opération : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?>CFA</span></td>
            <td class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></td>
          </tr>
          <tr>
            <td colspan="2">
              Reste à Payer : <span class="val-bold" style="background: #D1D5DB; padding: 2px 8px;"><?= number_format($total_reste_payer ?? 0, 0, ',', ' ') ?>CFA</span>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box" style="height: 75px;">
        <?php else: ?>
          <div class="photo-box" style="height: 75px; background: #F1F5F9; text-align: center; line-height: 75px; color: #94A3B8; font-size: 8.5px;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- STATUT PROCHAIN PAIEMENT & SIGNATURE COPIE -->
  <table style="width: 100%; margin-top: 4px;">
    <tr>
      <td style="width: 60%;">
        Date du Prochain Payement : 
        <span class="badge-solde" style="padding: 2px 10px;"><?= htmlspecialchars($date_prochain_paiement ?? 'SOLDÉ') ?></span>
      </td>
      <td style="width: 40%; text-align: right;">
        <div class="signature-title">CAISSIER(RE)</div>
        <div class="signature-name"><?= htmlspecialchars($caissier_nom ?? 'Mlle KONE N\'diatty A. Mariam') ?></div>
      </td>
    </tr>
  </table>

  <table style="width: 100%; font-size: 9px; margin-top: 6px;">
    <tr>
      <td style="width: 70%; font-style: italic;"><?= htmlspecialchars($ref_caiss ?? 'GE-25260276ScoFOF45944,4399676042ScaisKON') ?></td>
      <td style="width: 30%; text-align: right;"><?= htmlspecialchars($date_impression ?? date('26/08/2026 13:13:53')) ?></td>
    </tr>
  </table>

</div>

</body>
</html>
