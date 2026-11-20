<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bilan de la Filière & Encaissements</title>
  <style>
    @page {
      margin: 8mm 8mm 8mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 10px;
      color: #000000;
      line-height: 1.25;
    }

    /* Header Institution & Logo */
    .top-header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .inst-title {
      font-size: 13px;
      font-weight: bold;
      color: #990000;
      text-align: right;
    }

    /* Block Bilan Header Grid */
    .bilan-header-grid {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .bilan-header-grid td {
      vertical-align: top;
    }

    .left-info-box {
      border: 1.5px solid #000000;
      text-align: center;
      padding: 4px;
      margin-bottom: 4px;
    }
    .action-title {
      font-size: 13px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .annee-title {
      font-size: 12px;
      font-weight: bold;
    }

    .filiere-box {
      border: 1.5px solid #000000;
      text-align: center;
      padding: 3px;
    }
    .filiere-header {
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .filiere-code {
      font-size: 14px;
      font-weight: bold;
    }

    /* Banner & Stats Summary */
    .banner-title {
      background: #808080;
      color: #000000;
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      padding: 4px 6px;
      border: 1px solid #000000;
      margin-bottom: 4px;
    }

    .stats-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000000;
    }
    .stats-table td {
      border: 1px solid #000000;
      padding: 3px 6px;
      font-size: 10.5px;
    }
    .stat-label {
      font-weight: bold;
      font-size: 10px;
    }
    .stat-val {
      font-weight: bold;
      text-align: right;
    }

    /* Main Students Table */
    .main-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    .main-table th {
      background: #FFFF00;
      color: #000000;
      border: 1px solid #000000;
      padding: 4px 5px;
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
    }
    .main-table td {
      border: 1px solid #000000;
      padding: 3px 4px;
      font-size: 9.5px;
      vertical-align: middle;
    }

    .val-center {
      text-align: center;
    }
    .val-right {
      text-align: right;
      font-weight: bold;
    }

    /* Footer */
    .footer-line {
      width: 100%;
      border-top: 1px solid #000000;
      margin-top: 8px;
      padding-top: 4px;
      font-size: 8.5px;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE FINANCIÈRE & STATISTIQUES FILIÈRE -->
  <table class="bilan-header-grid">
    <tr>
      <!-- COLONNE GAUCHE : BLOCS DE TITRE -->
      <td style="width: 32%; padding-right: 8px;">
        <div class="left-info-box">
          <div class="action-title">ACTION CAISSE</div>
          <div class="annee-title"><?= htmlspecialchars($annee_libelle ?? '-') ?></div>
        </div>
        
        <div class="filiere-box">
          <div class="filiere-header">FILIERE ET NIVEAU</div>
          <div class="filiere-code"><?= htmlspecialchars($filiere_niveau ?? '-') ?></div>
        </div>
      </td>

      <!-- COLONNE DROITE : STATISTIQUES ET BANNIÈRE -->
      <td style="width: 68%;">
        <div class="banner-title">
          BILAN DE LA FILIERE à la date du <?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?>
        </div>
        
        <table class="stats-table">
          <tr>
            <td class="stat-label" style="width: 18%;">EFFECTIF</td>
            <td class="stat-val" style="width: 15%; text-align: center; font-size: 12px;"><?= htmlspecialchars($effectif_total ?? '0') ?></td>
            <td class="stat-label" style="width: 33%;">TOTAL A PAYER</td>
            <td class="stat-val" style="width: 34%;"><?= number_format($total_a_payer ?? 0, 0, ',', ' ') ?> CFA</td>
          </tr>
          <tr>
            <td class="stat-label">AFFECTE</td>
            <td class="stat-val" style="text-align: center;"><?= htmlspecialchars($nb_affectes ?? '0') ?></td>
            <td class="stat-label">TOTAL PAYE</td>
            <td class="stat-val"><?= number_format($total_paye ?? 0, 0, ',', ' ') ?> CFA</td>
          </tr>
          <tr>
            <td class="stat-label">NON AFF</td>
            <td class="stat-val" style="text-align: center;"><?= htmlspecialchars($nb_non_affectes ?? '0') ?></td>
            <td class="stat-label">RESTE A PAYER</td>
            <td class="stat-val" style="color: #990000;"><?= number_format($reste_a_payer ?? 0, 0, ',', ' ') ?> CFA</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- TABLEAU PRINCIPAL DES ÉTUDIANTS ET DES ENCAISSEMENTS -->
  <table class="main-table">
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 36%; text-align: left;">NOM_PRENOM(S)</th>
        <th style="width: 12%;">STATUT</th>
        <th style="width: 13%;">SOMME A PAYER</th>
        <th style="width: 13%;">SOMME PAYEE</th>
        <th style="width: 8%;">TENUE</th>
        <th style="width: 14%;">RESTE A PAYER</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($etudiants) && is_array($etudiants)): ?>
        <?php foreach ($etudiants as $index => $e): ?>
          <tr>
            <td class="val-center"><?= $index + 1 ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars($e['nom_prenoms'] ?? '') ?></td>
            <td class="val-center"><?= htmlspecialchars($e['statut'] ?? '-') ?></td>
            <td class="val-right"><?= number_format($e['somme_a_payer'] ?? 0, 0, ',', ' ') ?> CFA</td>
            <td class="val-right"><?= number_format($e['somme_payee'] ?? 0, 0, ',', ' ') ?> CFA</td>
            <td class="val-center"><?= htmlspecialchars($e['tenue'] ?? '') ?></td>
            <td class="val-right"><?= number_format($e['reste_a_payer'] ?? 0, 0, ',', ' ') ?> CFA</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="val-center" style="padding: 12px; color: #64748B;">Aucun étudiant enregistré pour cette filière.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- FOOTER -->
  <div class="footer-line">
    <?= htmlspecialchars($etablissement['nom_etablissement'] ?? '') ?> - État Financier et Bilan Global de la Filière
  </div>

</body>
</html>
