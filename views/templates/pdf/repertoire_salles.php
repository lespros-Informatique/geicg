<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Répertoire des Salles de Cours - GEICG</title>
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
      margin-bottom: 5px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 5px 8px;
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
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 3.5px 6px;
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
      margin-bottom: 6px;
    }
    .kpi-cell {
      padding: 3px 6px;
      text-align: center;
      background: #FFFFFF;
      border: 1px solid #CBD5E1;
      border-radius: 4px;
    }
    .kpi-num {
      font-size: 10.5pt;
      font-weight: bold;
      color: #1E3A5F;
    }
    .kpi-text {
      font-size: 6.8pt;
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
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-size: 7.5pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      padding: 4px 6px;
      border: 0.5px solid #1E3A5F;
      text-align: left;
    }
    .data-table td {
      padding: 4px 6px;
      font-size: 8pt;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
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
    .badge-capacite {
      color: #1E3A5F;
      font-weight: bold;
      font-size: 7.5pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-statut-actif {
      color: #15803D;
      font-weight: bold;
      font-size: 7.5pt;
      background-color: #DCFCE7;
      border: 0.5px solid #86EFAC;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-statut-inactif {
      color: #B91C1C;
      font-weight: bold;
      font-size: 7.5pt;
      background-color: #FEE2E2;
      border: 0.5px solid #FCA5A5;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }

    /* Bloc de Signature */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }
    .signature-box {
      width: 48%;
      vertical-align: top;
      border: 0.5px solid #CBD5E1;
      background-color: #FAFAFA;
      padding: 6px 10px;
    }
    .signature-title {
      font-size: 8pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      border-bottom: 0.5px solid #CBD5E1;
      padding-bottom: 3px;
      margin-bottom: 24px;
    }
    .signature-mention {
      font-size: 7pt;
      font-style: italic;
      color: #64748B;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Répertoire des Salles de Cours &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          Document généré par le Système GEICG &bull; <?= date('d/m/Y H:i:s') ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: REF-SAL-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- ENTÊTE DE L'ÉTABLISSEMENT -->
  <?php
    $logoPath = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
    if (!file_exists($logoPath)) {
      $logoPath = '/var/www/html/geicg/public/assets/images/logo/logo_eicg.jpg';
    }
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 22%; text-align: left;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 52px; max-width: 135px;" alt="Logo EICG">
        <?php else: ?>
          <div style="font-size: 14pt; font-weight: bold; color: #1E3A5F;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 78%; text-align: center;">
        <?php
          $nomEtab = 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION';
          if (!empty($etablissement['libelle_etablissement']) && !in_array(strtoupper(trim($etablissement['libelle_etablissement'])), ['GROUPE EICG', 'GEICG', 'ETABLISSEMENT A'], true)) {
              $nomEtab = $etablissement['libelle_etablissement'];
          }
        ?>
        <div class="inst-title"><?= htmlspecialchars($nomEtab) ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars(!empty($etablissement['numero_autorisation_etablissement']) ? 'AGRÉMENT N° ' . $etablissement['numero_autorisation_etablissement'] : 'AGRÉÉ PAR L\'ÉTAT ET LE FDFP') ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars(!empty($etablissement['adresse_etablissement']) && $etablissement['adresse_etablissement'] !== 'adresse' ? $etablissement['adresse_etablissement'] : 'Bouaké - Côte d\'Ivoire') ?>
          &nbsp;|&nbsp; Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99
        </div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">RÉPERTOIRE GÉNÉRAL DES SALLES DE COURS</div>
        <div class="banner-sub">INFRASTRUCTURES & ESPACES PÉDAGOGIQUES D'ENSEIGNEMENT</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 33%;">
        <span class="meta-label">Année Académique :</span> 
        <span class="meta-value" style="color: #0369A1; font-size: 9.5pt;"><?= htmlspecialchars($annee_libelle ?? 'Active') ?></span>
      </td>
      <td style="width: 34%;">
        <span class="meta-label">Date d'édition :</span> 
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
      <td style="width: 33%;">
        <span class="meta-label">Édité par :</span> 
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Direction des Études') ?></span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="meta-label">Campus / Établissement :</span> 
        <span class="meta-value"><?= htmlspecialchars($etablissement['nom_court_etablissement'] ?? 'Campus Central') ?></span>
      </td>
      <td>
        <span class="meta-label">Nombre de Salles :</span> 
        <span class="meta-value" style="color: #1E3A5F; font-size: 9.5pt;"><?= (int)count($salles) ?> salle(s)</span>
      </td>
      <td>
        <span class="meta-label">Statut du Registre :</span> 
        <span class="meta-value" style="color: #15803D;">Vérifié & Actif</span>
      </td>
    </tr>
  </table>

  <!-- SYNTHÈSE CHIFFRÉE / KPI -->
  <?php
    $totalCount = count($salles);
    $activesCount = 0;
    $inactivesCount = 0;
    foreach ($salles as $s) {
      if (($s['statut_salle'] ?? 'actif') === 'actif') {
        $activesCount++;
      } else {
        $inactivesCount++;
      }
    }
  ?>
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 33.3%;">
        <div class="kpi-num"><?= $totalCount ?></div>
        <div class="kpi-text">Total Espaces Pédagogiques</div>
      </td>
      <td class="kpi-cell" style="width: 33.3%;">
        <div class="kpi-num" style="color: #15803D;"><?= $activesCount ?></div>
        <div class="kpi-text">Salles Opérationnelles (Actives)</div>
      </td>
      <td class="kpi-cell" style="width: 33.3%;">
        <div class="kpi-num" style="color: <?= $inactivesCount > 0 ? '#B91C1C' : '#64748B' ?>;"><?= $inactivesCount ?></div>
        <div class="kpi-text">Salles Inactives / Travaux</div>
      </td>
    </tr>
  </table>

  <!-- SECTION : TABLEAU DES SALLES -->
  <table class="section-header-table">
    <tr>
      <td>1. Nomenclature des Salles & Espaces Pédagogiques</td>
    </tr>
  </table>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 6%; text-align: center;">#</th>
        <th style="width: 22%; text-align: center;">Code Salle</th>
        <th style="width: 42%;">Nom de la Salle / Espace</th>
        <th style="width: 16%; text-align: center;">Capacité</th>
        <th style="width: 14%; text-align: center;">Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($salles)): ?>
        <?php foreach ($salles as $idx => $salle): ?>
          <?php
            $isEven = ($idx % 2 === 0);
            $libelle = trim($salle['libelle_salle'] ?? '');
            $capacite = isset($salle['capacite_salle']) && $salle['capacite_salle'] !== '' && $salle['capacite_salle'] !== null 
                        ? (int)$salle['capacite_salle'] 
                        : (isset($salle['capacite']) && $salle['capacite'] !== '' && $salle['capacite'] !== null ? (int)$salle['capacite'] : null);
            $statut = $salle['statut_salle'] ?? 'actif';
            $isActif = ($statut === 'actif');
          ?>
          <tr class="<?= $isEven ? 'row-even' : 'row-odd' ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;">
              <?= $idx + 1 ?>
            </td>
            <td class="text-center">
              <span class="badge-code"><?= htmlspecialchars($salle['code_salle'] ?? '-') ?></span>
            </td>
            <td>
              <strong style="color: #0F172A; font-size: 8.5pt;"><?= htmlspecialchars($libelle) ?></strong>
            </td>
            <td class="text-center">
              <?php if ($capacite !== null && $capacite > 0): ?>
                <span class="badge-capacite"><?= $capacite ?> places</span>
              <?php else: ?>
                <span style="color: #94A3B8; font-weight: bold;">-</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($isActif): ?>
                <span class="badge-statut-actif">Actif</span>
              <?php else: ?>
                <span class="badge-statut-inactif">Inactif</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center" style="padding: 15px; color: #64748B;">
            Aucune salle de cours enregistrée dans la base de données.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURES -->
  <table class="signature-table">
    <tr>
      <td class="signature-box">
        <div class="signature-title">La Direction des Études & Pédagogie</div>
        <div style="height: 38px;"></div>
        <div class="signature-mention">Cachet & Signature autorisée</div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box">
        <div class="signature-title">La Direction Générale</div>
        <div style="height: 38px;"></div>
        <div class="signature-mention">Cachet & Signature autorisée</div>
      </td>
    </tr>
  </table>

</body>
</html>
