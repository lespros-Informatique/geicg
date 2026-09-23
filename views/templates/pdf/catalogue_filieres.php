<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Catalogue Général des Filières - GEICG</title>
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
      font-size: 10pt;
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
      margin-top: 18px;
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
      margin-bottom: 28px;
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
      padding: 3.8px 6px;
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
    .badge-tertiaire {
      color: #B45309;
      font-weight: bold;
      font-size: 7pt;
      background-color: #FEF3C7;
      padding: 1.5px 5px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-industrielle {
      color: #0369A1;
      font-weight: bold;
      font-size: 7pt;
      background-color: #E0F2FE;
      padding: 1.5px 5px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-slug {
      color: #1E3A5F;
      font-weight: bold;
      font-size: 7pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1px 5px;
      border-radius: 3px;
      display: inline-block;
      letter-spacing: 0.3px;
    }
    .badge-cycle {
      color: #0F172A;
      font-weight: bold;
      font-size: 7pt;
      background-color: #F1F5F9;
      border: 0.5px solid #CBD5E1;
      padding: 1px 4px;
      border-radius: 3px;
      margin-right: 2px;
      display: inline-block;
    }
    .badge-statut-actif {
      color: #15803D;
      font-weight: bold;
      font-size: 7pt;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Catalogue des Filières &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
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
          Réf: REF-FIL-<?= date('Ymd-His') ?>
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
        <div class="banner-title">CATALOGUE GÉNÉRAL DES FILIÈRES</div>
        <div class="banner-sub">RÉPERTOIRE DES FILIÈRES & SPÉCIALITÉS DE FORMATION ACCRÉDITÉES</div>
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
      <td style="width: 33%;">
        <span class="meta-label">Cadre institutionnel :</span> 
        <span class="meta-value"><?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?></span>
      </td>
      <td style="width: 34%;">
        <span class="meta-label">Périmètre :</span> 
        <span class="meta-value">Ensemble des Spécialités Enseignées</span>
      </td>
      <td style="width: 33%;">
        <span class="meta-label">Statut du Répertoire :</span> 
        <span class="meta-value" style="color: #15803D;">HOMOLOGUÉ & EN VIGUEUR</span>
      </td>
    </tr>
  </table>

  <?php
    $totalFilieres = count($filieres ?? []);
    $nbTertiaire = 0;
    $nbIndustrielle = 0;
    $allCyclesSet = [];

    foreach ($filieres ?? [] as $f) {
      $t = strtoupper($f['type_filiere'] ?? '');
      if ($t === 'TERTIAIRE') $nbTertiaire++;
      if ($t === 'INDUSTRIELLE') $nbIndustrielle++;
      if (!empty($f['cycles_codes'])) {
        $cList = explode(',', $f['cycles_codes']);
        foreach ($cList as $cl) {
          $cl = trim($cl);
          if (!empty($cl)) $allCyclesSet[$cl] = true;
        }
      }
    }
    $nbCyclesDistincts = count($allCyclesSet);
  ?>

  <!-- RÉSUMÉ STATISTIQUE -->
  <table class="kpi-table">
    <tr>
      <td style="width: 25%; padding-right: 4px;">
        <div class="kpi-cell">
          <div class="kpi-num"><?= $totalFilieres ?></div>
          <div class="kpi-text">Total Filières</div>
        </div>
      </td>
      <td style="width: 25%; padding-left: 2px; padding-right: 2px;">
        <div class="kpi-cell">
          <div class="kpi-num" style="color: #B45309;"><?= $nbTertiaire ?></div>
          <div class="kpi-text">Filières Tertiaires</div>
        </div>
      </td>
      <td style="width: 25%; padding-left: 2px; padding-right: 2px;">
        <div class="kpi-cell">
          <div class="kpi-num" style="color: #0369A1;"><?= $nbIndustrielle ?></div>
          <div class="kpi-text">Filières Industrielles</div>
        </div>
      </td>
      <td style="width: 25%; padding-left: 4px;">
        <div class="kpi-cell">
          <div class="kpi-num" style="color: #047857;"><?= $nbCyclesDistincts ?></div>
          <div class="kpi-text">Cycles Associés</div>
        </div>
      </td>
    </tr>
  </table>

  <!-- SECTION 1 : TABLEAU DÉTAILLÉ DES FILIÈRES -->
  <table class="section-header-table">
    <tr>
      <td>1. Nomenclature Officielle des Filières & Spécialités</td>
    </tr>
  </table>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 5%;" class="text-center">#</th>
        <th style="width: 48%;">Nom de la Filière / Spécialité</th>
        <th style="width: 12%;" class="text-center">Sigle</th>
        <th style="width: 15%;" class="text-center">Type</th>
        <th style="width: 20%;">Cycles Associés</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($filieres)): ?>
        <?php foreach ($filieres as $idx => $f): ?>
          <?php 
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $tFiliere = strtoupper($f['type_filiere'] ?? '');
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td style="font-weight: bold; color: #0F172A;">
              <?= htmlspecialchars($f['libelle_filiere'] ?? '-') ?>
            </td>
            <td class="text-center">
              <?php if (!empty($f['slug_filiere'])): ?>
                <span class="badge-slug"><?= htmlspecialchars($f['slug_filiere']) ?></span>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">-</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($tFiliere === 'INDUSTRIELLE'): ?>
                <span class="badge-industrielle">Industrielle</span>
              <?php elseif ($tFiliere === 'TERTIAIRE'): ?>
                <span class="badge-tertiaire">Tertiaire</span>
              <?php else: ?>
                <span style="color: #94A3B8; font-size: 7pt; font-style: italic;">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($f['cycles_libelles'])): ?>
                <span style="font-size: 7.2pt; color: #1E3A5F; font-weight: bold;">
                  <?= htmlspecialchars($f['cycles_libelles']) ?>
                </span>
              <?php elseif (!empty($f['cycles_codes'])): ?>
                <span class="badge-cycle"><?= htmlspecialchars($f['cycles_codes']) ?></span>
              <?php else: ?>
                <span style="color: #94A3B8; font-size: 7pt; font-style: italic;">Non assigné</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center" style="padding: 15px; color: #64748B; font-style: italic;">
            Aucune filière enregistrée dans le catalogue.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SIGNATURES ET CERTIFICATION -->
  <table style="width: 100%; margin-top: 10px; border-collapse: separate; border-spacing: 15px 0;">
    <tr>
      <td style="width: 50%; border: 1px dashed #94A3B8; background-color: #F8FAFC; padding: 6px 10px; text-align: center; vertical-align: top;">
        <div style="font-weight: bold; color: #1E3A5F; text-transform: uppercase; font-size: 7.5pt; margin-bottom: 26px;">
          La Direction des Études & de la Pédagogie
        </div>
        <div style="font-size: 6.5pt; color: #64748B; font-style: italic;">
          Visa pour conformité académique
        </div>
      </td>
      <td style="width: 50%; border: 1px dashed #94A3B8; background-color: #F8FAFC; padding: 6px 10px; text-align: center; vertical-align: top;">
        <div style="font-weight: bold; color: #1E3A5F; text-transform: uppercase; font-size: 7.5pt; margin-bottom: 26px;">
          La Direction Générale
        </div>
        <div style="font-size: 6.5pt; color: #64748B; font-style: italic;">
          Approbation & Cachet de l'Établissement
        </div>
      </td>
    </tr>
  </table>

  <div style="margin-top: 10px; font-size: 7.5pt; color: #94A3B8; text-align: center;">
    Fait à Bouaké, le <?= date('d/m/Y') ?> &bull; Document à usage pédagogique et administratif interne.
  </div>

</body>
</html>
