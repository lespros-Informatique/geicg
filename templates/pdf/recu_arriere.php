<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu de Règlement Arriéré de Scolarité</title>
  <style>
    @page {
      margin: 6mm 8mm 6mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 10px;
      color: #000000;
      line-height: 1.25;
    }

    /* Container Double Reçu */
    .recu-section {
      border: 1.5px solid #800000;
      padding: 10px 14px;
      box-sizing: border-box;
      position: relative;
    }

    /* Header Institutionnel */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .inst-title {
      font-size: 12px;
      font-weight: bold;
      color: #800000;
      text-decoration: underline;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 10px;
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

    /* Titre Reçu */
    .banner-title {
      background: #800000;
      color: #FFFFFF;
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      padding: 4px;
      margin-top: 4px;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Info Grids */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .info-table td {
      padding: 2px 3px;
      vertical-align: top;
      font-size: 9.5px;
    }
    .val-bold {
      font-weight: bold;
    }

    /* Tableau Financier */
    .fin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
    }
    .fin-table th {
      background: #F1F5F9;
      color: #000000;
      font-weight: bold;
      font-size: 9.5px;
      border: 1px solid #000000;
      padding: 4px 6px;
      text-align: center;
    }
    .fin-table td {
      border: 1px solid #000000;
      padding: 4px 6px;
      font-size: 9.5px;
    }

    /* Badges Statut */
    .badge-solde {
      display: inline-block;
      padding: 3px 8px;
      font-weight: bold;
      font-size: 10px;
      color: #FFFFFF;
      background: #166534; /* Vert apuré */
      border-radius: 3px;
      text-align: center;
    }
    .badge-partiel {
      display: inline-block;
      padding: 3px 8px;
      font-weight: bold;
      font-size: 10px;
      color: #FFFFFF;
      background: #B45309; /* Orange partiel */
      border-radius: 3px;
      text-align: center;
    }

    /* Separator */
    .cut-line {
      text-align: center;
      margin: 10px 0;
      font-size: 8.5px;
      font-style: italic;
      color: #64748B;
    }
    .cut-line hr {
      border: none;
      border-top: 1px dashed #94A3B8;
      margin-bottom: 3px;
    }

    /* Footer / Signatures */
    .signature-table {
      width: 100%;
      margin-top: 8px;
      font-size: 9.5px;
    }
    .signature-table td {
      vertical-align: top;
    }
  </style>
</head>
<body>

<?php for ($copy = 1; $copy <= 2; $copy++): ?>

<div class="recu-section">

  <!-- EN-TÊTE INSTITUTIONNEL -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 18%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 44px; max-width: 110px;">
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

  <!-- BANNIÈRE TITRE -->
  <div class="banner-title">
    REÇU DE RÈGLEMENT D'ARRIÉRÉ DE SCOLARITÉ
    <span style="font-size: 9px; font-weight: normal; float: right;">
      <?= $copy === 1 ? '[ ORIGINAL ÉLÈVE ]' : '[ COPIE ARCHIVAGE CAISSE ]' ?>
    </span>
  </div>

  <!-- INFORMATIONS ÉTUDIANT & TRANSACTION -->
  <table class="info-table">
    <tr>
      <td style="width: 50%;">
        N° Reçu : <span class="val-bold"><?= htmlspecialchars($reference_paiement ?? $numero_recu ?? 'REC-ARR-2026-001') ?></span>
        <div style="margin-top: 2px;">
          <barcode code="<?= htmlspecialchars($reference_paiement ?? $numero_recu ?? 'REC-ARR-2026-001') ?>" type="C128A" size="0.6" height="0.6" />
        </div>
      </td>
      <td style="width: 50%; text-align: right;">Date du versement : <span class="val-bold"><?= htmlspecialchars($date_paiement ?? date('d/m/Y H:i:s')) ?></span></td>
    </tr>
    <tr>
      <td>Matricule Élève : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
      <td style="text-align: right;">Année d'origine de l'arriéré : <span class="val-bold" style="color:#800000; font-size:10.5px;"><?= htmlspecialchars($annee_origine ?? '2024-2025') ?></span></td>
    </tr>
    <tr>
      <td colspan="2">Nom_Prénom(s) : <span class="val-bold" style="font-size: 11px;"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
    </tr>
    <tr>
      <td>Filière & Niveau d'origine : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
      <td style="text-align: right;">Statut Affectation : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
    </tr>
  </table>

  <!-- TABLEAU FINANCIER D'APUREMENT -->
  <table class="fin-table">
    <thead>
      <tr>
        <th style="width: 25%;">Arriéré Initial Dus</th>
        <th style="width: 25%;">Anciens Règlements</th>
        <th style="width: 25%; background: #FEF08A;">MONTANT VERSÉ</th>
        <th style="width: 25%;">Solde Restant Dus</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align: center; font-weight: bold;"><?= number_format($total_arriere_du ?? 0, 0, ',', ' ') ?> FCFA</td>
        <td style="text-align: center;"><?= number_format($cumul_ancien_regle ?? 0, 0, ',', ' ') ?> FCFA</td>
        <td style="text-align: center; font-weight: bold; font-size: 11px; color: #800000; background: #FEF08A;">
          <?= number_format($montant_verse ?? 0, 0, ',', ' ') ?> FCFA
        </td>
        <td style="text-align: center; font-weight: bold; color: <?= ($reste_a_payer_arriere ?? 0) <= 0 ? '#166534' : '#990000' ?>;">
          <?= number_format($reste_a_payer_arriere ?? 0, 0, ',', ' ') ?> FCFA
        </td>
      </tr>
    </tbody>
  </table>

  <!-- MONTANT EN LETTRES & MODE DE PAIEMENT -->
  <table style="width: 100%; margin-top: 4px; font-size: 9.5px;">
    <tr>
      <td style="width: 70%;">
        Montant versé en toutes lettres : <br>
        <strong style="color: #004080; font-size: 10.5px;"><?= htmlspecialchars($montant_lettres ?? 'Zéro Franc CFA') ?></strong>
      </td>
      <td style="width: 30%; text-align: right; vertical-align: top;">
        Mode de paiement : <span class="val-bold"><?= htmlspecialchars($mode_paiement ?? 'Espèces') ?></span><br>
        <?php if (!empty($reference_transaction)): ?>
          Réf Tx : <span class="val-bold"><?= htmlspecialchars($reference_transaction) ?></span>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- ETAT DE SOLDE & SIGNATURES -->
  <table class="signature-table">
    <tr>
      <td style="width: 45%;">
        Statut du compte arriéré : <br>
        <?php if (($reste_a_payer_arriere ?? 0) <= 0): ?>
          <span class="badge-solde">✔ ARRIÉRÉ TOTALEMENT SOLDÉ</span>
        <?php else: ?>
          <span class="badge-partiel">⏳ RÈGLEMENT PARTIEL ARRIÉRÉ</span>
        <?php endif; ?>
      </td>
      <td style="width: 25%; text-align: center;">
        <div style="font-weight: bold; text-decoration: underline;">L'Élève / Le Payeur</div>
      </td>
      <td style="width: 30%; text-align: right;">
        <div style="font-weight: bold; text-decoration: underline;">Le Caissier</div>
        <div style="margin-top: 15px; font-weight: bold;"><?= htmlspecialchars($caissier_nom ?? 'Agent Caisse GEICG') ?></div>
      </td>
    </tr>
  </table>

  <!-- FOOTER RÉFÉRENCE -->
  <div style="font-size: 8px; font-style: italic; color: #64748B; margin-top: 6px; text-align: right;">
    Session Caisse N° : <?= htmlspecialchars($session_caisse_id ?? '-') ?> | Imprimé le <?= date('d/m/Y H:i:s') ?>
  </div>

</div>

<?php if ($copy === 1): ?>
  <div class="cut-line">
    <hr>
    ✂ ------------------------------------- COPIE DE CONTRÔLE ET D'ARCHIVAGE CAISSE ------------------------------------- ✂
  </div>
<?php endif; ?>

<?php endfor; ?>

</body>
</html>
