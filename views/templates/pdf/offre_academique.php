<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Offre Académique Officielle - GEICG</title>
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
      color: #1E3A5F;
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
      border: 1px solid #E2E8F0;
      vertical-align: middle;
    }
    .meta-label {
      font-weight: bold;
      color: #475569;
    }
    .meta-value {
      font-weight: bold;
      color: #0F172A;
    }

    /* Cartes Statistiques / KPIs */
    .kpi-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 4px;
      margin-bottom: 6px;
    }
    .kpi-card {
      border: 1px solid #CBD5E1;
      background-color: #FFFFFF;
      padding: 4px;
      text-align: center;
      border-radius: 4px;
    }
    .kpi-number {
      font-size: 12pt;
      font-weight: bold;
      color: #1E3A5F;
      display: block;
    }
    .kpi-label {
      font-size: 7pt;
      color: #64748B;
      text-transform: uppercase;
      font-weight: bold;
      margin-top: 1px;
    }

    /* Section Titres */
    .section-heading {
      font-size: 8.5pt;
      font-weight: bold;
      color: #1E3A5F;
      background-color: #E2E8F0;
      padding: 3.5px 6px;
      margin-top: 6px;
      margin-bottom: 4px;
      border-left: 3px solid #1E3A5F;
      text-transform: uppercase;
    }

    /* Tableaux Principaux */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .data-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-size: 7.5pt;
      font-weight: bold;
      padding: 4.5px 4px;
      border: 1px solid #0F172A;
      text-transform: uppercase;
      text-align: left;
    }
    .data-table th.text-center {
      text-align: center;
    }
    .data-table th.text-end {
      text-align: right;
    }
    .data-table td {
      font-size: 7.5pt;
      padding: 3px 4px;
      border: 1px solid #CBD5E1;
      vertical-align: middle;
    }
    .row-even {
      background-color: #F8FAFC;
    }
    .row-odd {
      background-color: #FFFFFF;
    }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 2px 6px;
      font-size: 7pt;
      font-weight: bold;
      border-radius: 3px;
      text-transform: uppercase;
    }
    .badge-indus {
      background-color: #E0F2FE;
      color: #0369A1;
      border: 0.5px solid #BAE6FD;
    }
    .badge-tert {
      background-color: #FEF3C7;
      color: #B45309;
      border: 0.5px solid #FDE68A;
    }
    .badge-actif {
      background-color: #DCFCE7;
      color: #15803D;
      border: 0.5px solid #BBF7D0;
    }
    .badge-inactif {
      background-color: #FEE2E2;
      color: #B91C1C;
      border: 0.5px solid #FECACA;
    }

    /* Zone de Signatures */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
      page-break-inside: avoid;
    }
    .signature-table td {
      width: 50%;
      vertical-align: top;
      font-size: 8pt;
    }
    .sig-box {
      border: 1px dashed #94A3B8;
      background-color: #F8FAFC;
      padding: 6px 8px;
      min-height: 50px;
    }
    .sig-title {
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      margin-bottom: 28px;
    }
    .sig-footnote {
      font-size: 7pt;
      color: #64748B;
      font-style: italic;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Répertoire Officiel de l'Offre Académique &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          Document officiel généré par le Système GEICG &bull; <?= date('d/m/Y H:i:s') ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: REF-OA-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- ENTÊTE DE L'ÉTABLISSEMENT -->
  <?php
    $logoResolved = null;
    if (!empty($etablissement['logo_etablissement'])) {
      $p1 = __DIR__ . '/../../' . ltrim($etablissement['logo_etablissement'], '/');
      if (file_exists($p1)) $logoResolved = $p1;
    }
    if (!$logoResolved) {
      $p2 = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
      if (file_exists($p2)) $logoResolved = $p2;
    }
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 18%; text-align: left;">
        <?php if ($logoResolved): ?>
          <img src="<?= $logoResolved ?>" style="max-height: 52px; max-width: 120px;" alt="Logo">
        <?php else: ?>
          <div style="font-size: 14pt; font-weight: bold; color: #1E3A5F;">EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 82%; text-align: center;">
        <div class="inst-title"><?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION') ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars(!empty($etablissement['numero_autorisation_etablissement']) ? 'AGRÉMENT N° ' . $etablissement['numero_autorisation_etablissement'] : 'ÉTABLISSEMENT D\'ENSEIGNEMENT SUPÉRIEUR AGRÉÉ PAR L\'ÉTAT') ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars($etablissement['adresse_etablissement'] ?? 'Bouaké - Côte d\'Ivoire') ?>
          <?php if (!empty($etablissement['telephone_etablissement'])): ?>
            &nbsp;|&nbsp; Tél: <?= htmlspecialchars($etablissement['telephone_etablissement']) ?>
            <?php if (!empty($etablissement['telephone_etablissement2'])): ?> / <?= htmlspecialchars($etablissement['telephone_etablissement2']) ?><?php endif; ?>
          <?php endif; ?>
          <?php if (!empty($etablissement['email_etablissement'])): ?>
            &nbsp;|&nbsp; Email: <?= htmlspecialchars($etablissement['email_etablissement']) ?>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">OFFRE ACADÉMIQUE OFFICIELLE</div>
        <div class="banner-sub">RÉPERTOIRE DES CYCLES, FILIÈRES DE FORMATION & NIVEAUX D'ENSEIGNEMENT</div>
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
        <span class="meta-label">Cadre institutionnel :</span> 
        <span class="meta-value"><?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?></span>
      </td>
      <td>
        <span class="meta-label">Périmètre :</span> 
        <span class="meta-value"><?= !empty($filtre_cycle_libelle) ? 'Cycle : ' . htmlspecialchars($filtre_cycle_libelle) : 'Tous les Cycles de Formation' ?></span>
      </td>
      <td>
        <span class="meta-label">Statut du Répertoire :</span> 
        <span class="meta-value" style="color: #15803D;">HOMOLOGUÉ & EN VIGUEUR</span>
      </td>
    </tr>
  </table>

  <!-- KPIS / STATISTIQUES EN EN-TÊTE -->
  <table class="kpi-table">
    <tr>
      <td style="width: 25%;">
        <div class="kpi-card">
          <span class="kpi-number"><?= (int)count($cycles ?? []) ?></span>
          <span class="kpi-label">Cycles d'Études</span>
        </div>
      </td>
      <td style="width: 25%;">
        <div class="kpi-card">
          <span class="kpi-number"><?= (int)count($filieres ?? []) ?></span>
          <span class="kpi-label">Filières / Spécialités</span>
        </div>
      </td>
      <td style="width: 25%;">
        <div class="kpi-card">
          <span class="kpi-number"><?= (int)count($niveaux ?? []) ?></span>
          <span class="kpi-label">Niveaux d'Enseignement</span>
        </div>
      </td>
      <td style="width: 25%;">
        <div class="kpi-card">
          <span class="kpi-number" style="color: #047857;"><?= (int)count($parcours ?? []) ?></span>
          <span class="kpi-label">Parcours Pivots Actifs</span>
        </div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DES ASSIGNATIONS PARCOURS PIVOTS -->
  <div class="section-heading">1. Nomenclature des Parcours Pivots de Formation (Cycle ↔ Filière ↔ Niveau)</div>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 4%;" class="text-center">#</th>
        <th style="width: 14%;">Code Parcours</th>
        <th style="width: 23%;">Cycle d'Études</th>
        <th style="width: 31%;">Filière de Formation</th>
        <th style="width: 12%;" class="text-center">Type</th>
        <th style="width: 16%;">Niveau d'Études</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($parcours) && is_array($parcours)): ?>
        <?php foreach ($parcours as $idx => $p): ?>
          <?php 
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $typeFiliere = strtoupper($p['type_filiere'] ?? '');
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td style="font-family: monospace; font-weight: bold; color: #334155; font-size: 7.5pt;">
              <?= htmlspecialchars($p['code_filiere_cycle'] ?? '-') ?>
            </td>
            <td style="font-weight: bold; color: #1E3A5F;">
              <?= htmlspecialchars($p['libelle_cycle'] ?? 'Non assigné') ?>
            </td>
            <td style="font-weight: bold; color: #0F172A;">
              <?= htmlspecialchars($p['libelle_filiere'] ?? 'Non assigné') ?>
            </td>
            <td class="text-center">
              <?php if ($typeFiliere === 'INDUSTRIELLE'): ?>
                <span class="badge badge-indus">Industrielle</span>
              <?php elseif ($typeFiliere === 'TERTIAIRE'): ?>
                <span class="badge badge-tert">Tertiaire</span>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic; font-size: 7.5pt;">-</span>
              <?php endif; ?>
            </td>
            <td style="font-weight: bold; color: #475569;">
              <?= htmlspecialchars($p['libelle_niveau'] ?? 'Tous / Global') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center" style="padding: 15px; color: #64748B; font-style: italic;">
            Aucun parcours pivot enregistré dans l'offre académique pour cette sélection.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SYNTHÈSE ANALYTIQUE PAR CYCLE D'ÉTUDES -->
  <?php if (!empty($synthese_cycles) && is_array($synthese_cycles)): ?>
    <div class="section-heading" style="margin-top: 14px;">2. Synthèse Structurelle Répartie par Cycle d'Études</div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 25%;">Cycle de Formation</th>
          <th style="width: 45%;">Filières & Spécialités Hébergées</th>
          <th style="width: 18%;">Niveaux d'Enseignement</th>
          <th style="width: 12%;" class="text-center">Nb Parcours</th>
        </tr>
      </thead>
      <tbody>
        <?php $cIdx = 0; ?>
        <?php foreach ($synthese_cycles as $cCode => $cGroup): ?>
          <?php $cRowClass = ($cIdx % 2 === 0) ? 'row-even' : 'row-odd'; $cIdx++; ?>
          <tr class="<?= $cRowClass ?>">
            <td style="font-weight: bold; color: #1E3A5F; vertical-align: top;">
              <?= htmlspecialchars($cGroup['libelle_cycle']) ?>
              <?php if (!empty($cGroup['code_cycle'])): ?>
                <div style="font-size: 7pt; font-family: monospace; color: #64748B;"><?= htmlspecialchars($cGroup['code_cycle']) ?></div>
              <?php endif; ?>
            </td>
            <td style="vertical-align: top;">
              <?php if (!empty($cGroup['filieres'])): ?>
                <ul style="margin: 0; padding-left: 14px; font-size: 7.5pt; color: #0F172A;">
                  <?php foreach ($cGroup['filieres'] as $fil): ?>
                    <li>
                      <strong><?= htmlspecialchars($fil['libelle']) ?></strong>
                      <?php if (!empty($fil['type'])): ?>
                        <span style="font-size: 6.5pt; color: #64748B;">(<?= htmlspecialchars($fil['type']) ?>)</span>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">Aucune filière rattachée</span>
              <?php endif; ?>
            </td>
            <td style="vertical-align: top;">
              <?php if (!empty($cGroup['niveaux'])): ?>
                <div style="font-size: 7.5pt; color: #334155;">
                  <?= htmlspecialchars(implode(', ', array_unique($cGroup['niveaux']))) ?>
                </div>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">-</span>
              <?php endif; ?>
            </td>
            <td class="text-center" style="font-weight: bold; font-size: 9pt; color: #047857; vertical-align: middle;">
              <?= (int)count($cGroup['parcours_items'] ?? []) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- SIGNATURES ET CERTIFICATION -->
  <table class="signature-table">
    <tr>
      <td style="padding-right: 10px;">
        <div class="sig-box">
          <div class="sig-title">La Direction des Études & de la Pédagogie</div>
          <div class="sig-footnote">Visa pour conformité académique</div>
        </div>
      </td>
      <td style="padding-left: 10px;">
        <div class="sig-box">
          <div class="sig-title">La Direction Générale</div>
          <div class="sig-footnote">Approbation & Cachet Officiel de l'Établissement</div>
        </div>
      </td>
    </tr>
  </table>

  <div style="margin-top: 10px; font-size: 7.5pt; color: #94A3B8; text-align: center;">
    Fait à Bouaké, le <?= date('d/m/Y') ?> &bull; Document officiel à usage pédagogique et administratif interne.
  </div>

</body>
</html>
