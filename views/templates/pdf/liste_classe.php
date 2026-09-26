<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste de Classe & Fiche d'Émargement - GROUPE EICG</title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 8mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #0F172A;
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
    }
    .banner-title {
      font-size: 11.5pt;
      font-weight: bold;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 8pt;
      color: #E2E8F0;
      margin-top: 1px;
    }

    /* Cartouche Métadonnées & KPIs */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 4px 6px;
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

    /* Summary Indicators Bar */
    .kpi-summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .kpi-summary-table td {
      padding: 4px 6px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      border: 1px solid #CBD5E1;
    }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }
    .kpi-affecte { background-color: #F0FDF4; color: #15803D; }
    .kpi-prive { background-color: #F0F9FF; color: #0369A1; }
    .kpi-genre { background-color: #FDF4FF; color: #7E22CE; }

    /* Tableau Nominatif */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .data-table th {
      background-color: #1E293B;
      color: #FFFFFF;
      border: 1px solid #000000;
      padding: 5px 5px;
      font-size: 8.5pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 1px solid #000000;
      padding: 4px 5px;
      font-size: 8.5pt;
      vertical-align: middle;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-bold { font-weight: bold; }

    .badge-affecte {
      color: #15803D;
      font-weight: bold;
    }
    .badge-prive {
      color: #1D4ED8;
      font-weight: bold;
    }

    /* Footer & Signatures */
    .footer-summary {
      width: 100%;
      margin-top: 10px;
      border-top: 1px solid #000000;
      padding-top: 5px;
      font-size: 8pt;
    }
    .signature-grid {
      width: 100%;
      margin-top: 12px;
      font-size: 8.5pt;
    }
    .signature-grid td {
      vertical-align: top;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE ÉTABLISSEMENT -->
  <?php 
    $candidateLogos = [
        __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg',
        __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg',
        '/var/www/html/geicg/public/assets/images/logo/logo_eicg.jpg'
    ];
    $logoSrc = null;
    foreach ($candidateLogos as $lPath) {
        if (file_exists($lPath) && is_file($lPath)) {
            $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($lPath));
            break;
        }
    }
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 20%;">
        <?php if ($logoSrc): ?>
          <img src="<?= $logoSrc ?>" style="max-height: 48px; max-width: 130px;">
        <?php else: ?>
          <div style="font-weight: bold; color: #990000; font-size: 12pt;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 80%; text-align: center;">
        <div class="inst-title"><?= htmlspecialchars($etablissement['nom_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION') ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars($etablissement['sous_titre'] ?? 'AGREE PAR L\'ETAT ET LE FDFP') ?></div>
        <div class="inst-contacts">Contacts : <?= htmlspecialchars($etablissement['telephone'] ?? '27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99') ?> — Site Web : www.groupe-eicg.net</div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE PRINCIPALE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">LISTE DE CLASSE & FICHE D'ÉMARGEMENT</div>
        <div class="banner-sub">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle) ?></div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE METADATA CLASSE -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 33%;"><span class="meta-label">Classe / Groupement :</span> <span class="meta-value"><?= htmlspecialchars($classe_libelle ?? ($classe['libelle_classe'] ?? '')) ?></span></td>
      <td style="width: 34%;"><span class="meta-label">Filière :</span> <span class="meta-value"><?= htmlspecialchars($filiere_libelle ?? ($classe['libelle_filiere'] ?? '-')) ?></span></td>
      <td style="width: 33%;"><span class="meta-label">Niveau d'Études :</span> <span class="meta-value"><?= htmlspecialchars($niveau_libelle ?? ($classe['libelle_niveau'] ?? '-')) ?></span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Effectif Inscrit :</span> <span class="meta-value"><?= count($etudiants ?? []) ?> Étudiant(s)</span></td>
      <td><span class="meta-label">Éditeur / Agent :</span> <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Service Scolarité') ?></span></td>
      <td><span class="meta-label">Date d'Édition :</span> <span class="meta-value"><?= date('d/m/Y H:i') ?></span></td>
    </tr>
  </table>

  <!-- SUMMARY KPIS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 25%;">EFFECTIF TOTAL : <?= $kpis['total'] ?? count($etudiants ?? []) ?></td>
      <td class="kpi-affecte" style="width: 25%;">AFFECTÉS (ÉTAT) : <?= $kpis['affectes'] ?? 0 ?></td>
      <td class="kpi-prive" style="width: 25%;">NON AFFECTÉS (PRIVÉ) : <?= $kpis['non_affectes'] ?? 0 ?></td>
      <td class="kpi-genre" style="width: 25%;">GENRE : <?= $kpis['hommes'] ?? 0 ?> H / <?= $kpis['femmes'] ?? 0 ?> F</td>
    </tr>
  </table>

  <!-- TABLEAU NOMINATIF ÉMARGEMENT -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 15%;">MATRICULE</th>
        <th style="width: 35%; text-align: left;">NOM & PRÉNOM(S)</th>
        <th style="width: 6%;">SEXE</th>
        <th style="width: 14%;">STATUT / RÉGIME</th>
        <th style="width: 12%;">CONTACT</th>
        <th style="width: 14%;">ÉMARGEMENT / OBS</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($etudiants)): ?>
        <?php foreach ($etudiants as $i => $e): ?>
          <?php 
            $isAffecte = ($e['affectation_etat'] === 'affecte' || $e['affectation_etat'] === 'oui');
            $nomComplet = mb_strtoupper($e['nom_etudiant'] ?? '') . ' ' . ($e['prenom_etudiant'] ?? '');
          ?>
          <tr>
            <td class="text-center text-bold"><?= $i + 1 ?></td>
            <td class="text-center text-bold" style="font-family: monospace; color: #1E3A5F;"><?= htmlspecialchars($e['matricule_etudiant'] ?? $e['code_etudiant']) ?></td>
            <td class="text-left text-bold"><?= htmlspecialchars($nomComplet) ?></td>
            <td class="text-center text-bold"><?= htmlspecialchars($e['sexe_etudiant'] ?? 'M') ?></td>
            <td class="text-center">
              <?php if ($isAffecte): ?>
                <span class="badge-affecte">Affecté(e) État</span>
              <?php else: ?>
                <span class="badge-prive">Non Affecté(e)</span>
              <?php endif; ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($e['telephone_etudiant'] ?? '-') ?></td>
            <td class="text-center" style="height: 18px;"></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center" style="padding: 20px; color: #64748B; font-style: italic;">
            Aucun étudiant n'est actuellement inscrit dans cette classe.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BAS DE PAGE ET SIGNATURES -->
  <table class="footer-summary">
    <tr>
      <td style="width: 55%;">
        Bilan des Présences : &nbsp;&nbsp;&nbsp;&nbsp; Présents : [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Absents : [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Retards : [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
      </td>
      <td style="width: 45%; text-align: right;">
        Fait à Bouaké, le <?= date('d/m/Y') ?>
      </td>
    </tr>
  </table>

  <table class="signature-grid">
    <tr>
      <td style="width: 33%;">
        <div style="font-weight: bold; text-decoration: underline;">Le Délégué de Classe</div>
      </td>
      <td style="width: 34%; text-align: center;">
        <div style="font-weight: bold; text-decoration: underline;">Le Enseignant / Surveillant</div>
      </td>
      <td style="width: 33%; text-align: right;">
        <div style="font-weight: bold; text-decoration: underline;">La Direction des Études</div>
      </td>
    </tr>
  </table>

</body>
</html>
