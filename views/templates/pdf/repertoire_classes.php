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
  <title>Liste des Classes - GEICG</title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 10mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #1E293B;
      line-height: 1.25;
    }

    /* En-tête */
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
      margin-bottom: 5px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 5px 8px;
      text-align: center;
      color: #FFFFFF;
      height: 40px;
    }
    .banner-title {
      font-size: 14pt;
      font-weight: bold;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 9.5pt;
      color: #E2E8F0;
    }

    /* Cartouche Métadonnées */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 2.5px 6px;
      font-size: 9pt;
      vertical-align: middle;
      border: 0.5px solid #E2E8F0;
      height: 50px;
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
      margin-bottom: 15px;
    }
    .kpi-cell {
      padding: 3px 6px;
      text-align: center;
      background: #FFFFFF;
      border: 1px solid #CBD5E1;
      border-radius: 4px;
    }
    .kpi-num {
      font-size: 11pt;
      font-weight: bold;
      color: #1E3A5F;
    }
    .kpi-text {
      font-size: 8.8pt;
      color: #475569;
      text-transform: uppercase;
      font-weight: bold;
      letter-spacing: 0.3px;
    }

    /* Titres de Sections */
    .section-header-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
      margin-bottom: 6px;
      background-color: #E2E8F0;
      border-left: 3.5px solid #1E3A5F;
    }
    .section-header-table td {
      padding: 3.5px 7px;
      font-size: 8.5pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    /* Tableaux de Données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }
    .data-table th {
      background-color: #D0D3D6F9;
      color: #0E0E0E;
      font-size: 9pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      padding: 4px 6px;
      border: 0.5px solid #1E3A5F;
      text-align: left;
      height: 42px;
    }
    .data-table td {
      padding: 4px 6px;
      font-size: 9pt;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
      height: 45px;
    }
    .row-even {
      background-color: #FFFFFF;
    }
    .row-odd {
      background-color: #F8FAFC;
    }
    .text-center {
      text-align: center;
    }
    .text-end {
      text-align: right;
    }

    /* Badges */
    .badge-code {
      color: #1E3A5F;
      font-weight: bold;
      font-size: 7.5pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
      letter-spacing: 0.3px;
    }
    .badge-cycle {
      color: #424242;
      font-weight: bold;
      font-size: 10pt;
      padding: 1px 5px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-filiere {
      color: #28292A;
      font-weight: 600;
      font-size: 9pt;
    }
    .badge-niveau {
      color: #64748B;
      font-size: 9.5pt;
      font-weight: bold;
    }
    .badge-capacite {
      color: #2F2F2F;
      font-weight: bold;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }

 

    /* Bloc de Signature (hors table pour que mPDF applique margin/padding sur les div) */
    .signature-block {
      width: 48%;
      margin-left: auto;
      margin-top: 25px;
      text-align: right;
    }
    .signature-title {
      font-size: 8.7pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: right;
      padding-bottom: 4px;
      margin-bottom: 2px;
    }
    .signature-name {
      font-weight: bold;
      font-size: 8.5pt;
      color: #1E293B;
      text-align: right;
      margin-top: 2px;
      margin-bottom: 18px;
    }
    .signature-mention {
      font-size: 7.5pt;
      font-style: italic;
      color: #64748B;
      text-align: right;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Répertoire des Classes &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

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
          Réf: REF-CLA-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- ENTÊTE DE L'ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">LISTE DES CLASSES</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 33%;">
        <span class="meta-label">Année Académique :</span> 
        <span class="meta-value" style="color: #0369A1; font-size: 9.5pt;"><?= htmlspecialchars($annee_libelle ?? '-') ?></span>
      </td>
      <td style="width: 34%;">
        <span class="meta-label">Date d'édition :</span> 
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
      <td style="width: 33%;">
        <span class="meta-label">Édité par :</span> 
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? '-') ?></span>
      </td>
    </tr>
  </table>

  <!-- SYNTHÈSE CHIFFRÉE / KPI -->
  <?php
    $totalCount = count($classes);
    $totalCapacite = 0;
    foreach ($classes as $c) {
      $totalCapacite += (int)($c['capacite_max_classe'] ?? 0);
    }
  ?>
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 50%;">
        <div class="kpi-num"><?= $totalCount ?></div>
        <div class="kpi-text">Total Classes</div>
      </td>
      <td class="kpi-cell" style="width: 50%;">
        <div class="kpi-num" style="color: #0369A1;"><?= $totalCapacite ?></div>
        <div class="kpi-text">Capacité Globale d'Accueil</div>
      </td>
    </tr>
  </table>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 5%; text-align: center;">#</th>
        <th style="width: 18%; text-align: center;">Code Classe</th>
        <th style="width: 25%;">Libellé de la Classe</th>
        <th style="width: 36%;">Cycle & Filière de Rattachement</th>
        <th style="width: 16%; text-align: center;">Capacité</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($classes)): ?>
        <?php foreach ($classes as $idx => $classe): ?>
          <?php
            $isEven = ($idx % 2 === 0);
            $libelle = trim($classe['libelle_classe'] ?? '');
            $filiere = trim($classe['libelle_filiere'] ?? ($classe['slug_filiere'] ?? '-'));
            $cycleSlug = trim($classe['slug_cycle'] ?? ($classe['libelle_cycle'] ?? ''));
            $niveauSlug = trim($classe['slug_niveau'] ?? ($classe['libelle_niveau'] ?? ''));
            $capacite = (int)($classe['capacite_max_classe'] ?? 0);
            $statut = $classe['statut_classe'] ?? 'actif';
            $isActif = ($statut === 'actif');
          ?>
          <tr class="<?= $isEven ? 'row-even' : 'row-odd' ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;">
              <?= $idx + 1 ?>
            </td>
            <td class="text-center">
              <span class="badge-code"><?= htmlspecialchars($classe['code_classe'] ?? '-') ?></span>
            </td>
            <td>
              <strong style="color: #0F172A; font-size: 8.5pt;"><?= htmlspecialchars($libelle) ?></strong>
            </td>
            <td>
              <?php if (!empty($cycleSlug)): ?>
                <span class="badge-cycle"><?= htmlspecialchars($cycleSlug) ?></span>
              <?php endif; ?>
              <span class="badge-filiere"><?= htmlspecialchars($filiere) ?></span>
              <?php if (!empty($niveauSlug)): ?>
                <span class="badge-niveau">&bull; <?= htmlspecialchars($niveauSlug) ?></span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($capacite > 0): ?>
                <span class="badge-capacite"><?= $capacite ?> places</span>
              <?php else: ?>
                <span style="color: #94A3B8; font-weight: bold;">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center" style="padding: 15px; color: #64748B;">
            Aucune classe enregistrée dans la base de données.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURES -->
  <div class="signature-block">
    <div class="signature-title"><?= htmlspecialchars($etablissement['titre_direction_pedagogique'] ?? '') ?></div>
    <div class="signature-name"><?= htmlspecialchars($etablissement['nom_directeur_etudes'] ?? '') ?></div>
    <div class="signature-mention"><?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? '') ?></div>
  </div>

</body>
</html>
