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
  <title>Maquette Pédagogique & Coefficients - <?= htmlspecialchars($etablissement['nom_etablissement'] ?? 'GEICG') ?></title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 12mm;
      margin-left: 8mm;
      margin-right: 8mm;
      footer: html_pageFooter;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #1E293B;
      line-height: 1.25;
    }

    /* En-tête Institutionnel */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .header-table td {
      vertical-align: middle;
    }
    .inst-title {
      font-size: 11.5pt;
      font-weight: bold;
      color: #990000;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.15;
    }
    .inst-subtitle {
      font-size: 8.5pt;
      font-weight: bold;
      color: #0369A1;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 7.5pt;
      color: #64748B;
      margin-top: 1px;
    }

    .divider-line {
      height: 1.5px;
      background-color: #1E3A5F;
      margin-top: 3px;
      margin-bottom: 5px;
    }

    /* Bannière Titre */
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 6px 10px;
      text-align: center;
      color: #FFFFFF;
    }
    .banner-title {
      font-size: 11pt;
      font-weight: bold;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 7.5pt;
      color: #E2E8F0;
      margin-top: 1px;
    }

    /* Cartouche Métadonnées */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 4px 8px;
      font-size: 8pt;
      vertical-align: middle;
      border: 0.5px solid #E2E8F0;
    }
    .meta-label {
      font-weight: bold;
      color: #475569;
    }
    .meta-value {
      font-weight: bold;
      color: #0F172A;
    }

    /* Résumé Chiffré / KPI */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .kpi-cell {
      padding: 4px 6px;
      text-align: center;
      background: #FFFFFF;
      border: 1px solid #CBD5E1;
    }
    .kpi-num {
      font-size: 11pt;
      font-weight: bold;
      color: #1E3A5F;
    }
    .kpi-text {
      font-size: 7pt;
      color: #475569;
      text-transform: uppercase;
      font-weight: bold;
      letter-spacing: 0.3px;
    }

    /* Table des matières */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 8px;
    }
    .data-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-size: 8pt;
      font-weight: bold;
      text-align: left;
      padding: 5px 6px;
      border: 0.5px solid #0F2744;
    }
    .data-table td {
      font-size: 7.8pt;
      padding: 4px 6px;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
    }
    .data-table tr:nth-child(even) {
      background-color: #F8FAFC;
    }

    .badge-gen {
      background-color: #EFF6FF;
      color: #1D4ED8;
      border: 0.5px solid #BFDBFE;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 7.5pt;
      display: inline-block;
    }
    .badge-pro {
      background-color: #FFFBEB;
      color: #B45309;
      border: 0.5px solid #FDE68A;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 7.5pt;
      display: inline-block;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }

    /* Bloc de Signature (hors table pour que mPDF applique margin/padding sur les div) */
    .signature-block {
      width: 250px;
      float: right;
      margin-top: 25px;
      text-align: center;
    }
    .signature-title {
      font-size: 8.7pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      padding-bottom: 4px;
      margin-bottom: 2px;
    }
    .signature-name {
      font-weight: bold;
      font-size: 8.5pt;
      color: #1E293B;
      text-align: center;
      margin-top: 2px;
      margin-bottom: 18px;
    }
    .signature-mention {
      font-size: 7.5pt;
      font-style: italic;
      color: #64748B;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: REF-MAQ-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" page="ALL" />

  <!-- EN-TÊTE OFFICIEL UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">MAQUETTE PÉDAGOGIQUE & COEFFICIENTS DES MATIÈRES</div>
        
      </td>
    </tr>
  </table>

  <!-- CARTUCHE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span> 
        <span class="meta-value"><?= htmlspecialchars($annee_libelle) ?></span>
      </td>
      <td style="width: 30%;">
        <span class="meta-label">Filière :</span> 
        <span class="meta-value"><?= htmlspecialchars($filiere_libelle) ?></span>
      </td>
      <td style="width: 22%;">
        <span class="meta-label">Niveau d'Études :</span> 
        <span class="meta-value"><?= htmlspecialchars($niveau_libelle) ?></span>
      </td>
      <td style="width: 23%;">
        <span class="meta-label">Date Édition :</span> 
        <span class="meta-value"><?= htmlspecialchars($date_edition) ?></span>
      </td>
    </tr>
  </table>

  <!-- STATISTIQUES / KPI -->
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 33.33%;">
        <div class="kpi-num"><?= $totalMatieres ?></div>
        <div class="kpi-text">Total Matières</div>
      </td>
      <td class="kpi-cell" style="width: 33.33%;">
        <div class="kpi-num" style="color: #1D4ED8;"><?= $totalGenerales ?></div>
        <div class="kpi-text">Matières Générales</div>
      </td>
      <td class="kpi-cell" style="width: 33.33%;">
        <div class="kpi-num" style="color: #B45309;"><?= $totalProfessionnelles ?></div>
        <div class="kpi-text">Matières Professionnelles</div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DES MATIÈRES -->
  <?php if (empty($items)): ?>
    <div style="padding: 20px; text-align: center; color: #64748B; font-style: italic; border: 1px dashed #CBD5E1; margin-top: 10px;">
      Aucune matière configurée dans la maquette pour les critères sélectionnés.
    </div>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 24px;" class="text-center">#</th>
          <th>Matière à Enseigner</th>
          <th style="width: 140px;">Filière</th>
          <th style="width: 80px;">Niveau</th>
          <th style="width: 120px;" class="text-center">Catégorie Bulletin</th>
          <th style="width: 75px;" class="text-center">Coefficient</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $row): ?>
          <?php $isGen = (($row['categorie_matiere'] ?? CATEGORIES_MATIERES::GENERALE) === CATEGORIES_MATIERES::GENERALE); ?>
          <tr>
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td>
              <strong style="color: #0F172A; font-size: 8.2pt;"><?= htmlspecialchars($row['libelle_matiere'] ?? $row['matiere_code']) ?></strong>
              <div style="font-size: 6.8pt; color: #64748B;">Code: <?= htmlspecialchars($row['matiere_code']) ?></div>
            </td>
            <td>
              <strong style="color: #1E3A5F;"><?= htmlspecialchars($row['libelle_filiere'] ?? $row['filiere_code']) ?></strong>
            </td>
            <td>
              <span style="font-weight: bold; color: #475569;"><?= htmlspecialchars($row['libelle_niveau'] ?? $row['niveau_code']) ?></span>
            </td>
            <td class="text-center">
              <?php if ($isGen): ?>
                <span class="badge-gen">GÉNÉRALE</span>
              <?php else: ?>
                <span class="badge-pro">PROFESSIONNELLE</span>
              <?php endif; ?>
            </td>
            <td class="text-center" style="font-weight: bold; font-size: 8.5pt; color: #0F172A;">
              <?= number_format((float)($row['coefficient'] ?? 1.0), 1) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- BLOC DE SIGNATURES -->
  <div class="signature-block">
    <div class="signature-title"><?= htmlspecialchars($etablissement['titre_direction_pedagogique'] ?? '') ?></div>
    <div class="signature-name"><?= htmlspecialchars($etablissement['nom_directeur_etudes'] ?? '') ?></div>
    <div class="signature-mention"><?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? '') ?></div>
  </div>

</body>
</html>

