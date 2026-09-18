<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste Officielle de Classe & Émargement</title>
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

    /* En-tête Institutionnel */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .inst-title {
      font-size: 13px;
      font-weight: bold;
      color: #990000;
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
      font-size: 9px;
      font-style: italic;
      text-align: center;
      margin-top: 1px;
    }

    /* Bannières Titres */
    .banner-title {
      background: #800000;
      color: #FFFFFF;
      font-size: 15px;
      font-weight: bold;
      text-align: center;
      padding: 5px;
      margin-top: 4px;
      margin-bottom: 4px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Fiche Informations Classe */
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000000;
      margin-bottom: 8px;
    }
    .meta-table td {
      padding: 4px 6px;
      vertical-align: middle;
      font-size: 10px;
      border: 1px solid #CBD5E1;
    }
    .val-bold {
      font-weight: bold;
    }

    /* Tableau Liste des Étudiants */
    .students-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .students-table th {
      background: #1E293B;
      color: #FFFFFF;
      border: 1px solid #000000;
      padding: 4.5px 4px;
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
    }
    .students-table td {
      border: 1px solid #000000;
      padding: 3.5px 4px;
      font-size: 9.5px;
      vertical-align: middle;
    }

    .val-center {
      text-align: center;
    }

    /* Footer & Signatures */
    .footer-summary {
      width: 100%;
      margin-top: 10px;
      border-top: 1px solid #000000;
      padding-top: 6px;
      font-size: 9px;
    }
    .signature-grid {
      width: 100%;
      margin-top: 15px;
      font-size: 9.5px;
    }
    .signature-grid td {
      vertical-align: top;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE OFFICIELLE INSTITUTION -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 20%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 48px; max-width: 125px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#990000; font-size:13px;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 80%; text-align: center; vertical-align: middle;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE FDFP</div>
        <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
      </td>
    </tr>
  </table>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">LISTE OFFICIELLE DE CLASSE</div>

  <!-- METADATA DE LA CLASSE -->
  <table class="meta-table">
    <tr>
      <td style="width: 33%;">Année Académique : <span class="val-bold" style="font-size: 11px; color: #800000;"><?= htmlspecialchars($annee_libelle ?? '2025-2026') ?></span></td>
      <td style="width: 34%;">Filière : <span class="val-bold"><?= htmlspecialchars($filiere_libelle ?? 'Ressources Humaines et Communication') ?></span></td>
      <td style="width: 33%;">Niveau : <span class="val-bold"><?= htmlspecialchars($niveau_libelle ?? 'Première Année') ?></span></td>
    </tr>
    <tr>
      <td>Classe / Groupement : <span class="val-bold" style="font-size: 11px;"><?= htmlspecialchars($classe_libelle ?? 'GBAT 1A') ?></span></td>
      <td>Salle de cours : <span class="val-bold"><?= htmlspecialchars($salle_libelle ?? 'Salle S6') ?></span></td>
      <td>Date d'édition : <span class="val-bold"><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></span></td>
    </tr>
    <tr>
      <td>Effectif Total : <span class="val-bold"><?= htmlspecialchars($effectif_total ?? count($etudiants ?? [])) ?> Étudiants</span></td>
      <td>Affectés (État) : <span class="val-bold"><?= htmlspecialchars($nb_affectes ?? '-') ?></span></td>
      <td>Non Affectés (Privés) : <span class="val-bold"><?= htmlspecialchars($nb_prives ?? '-') ?></span></td>
    </tr>
  </table>

  <!-- TABLEAU NOMINATIF DE CLASSE -->
  <table class="students-table">
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 14%;">MATRICULE</th>
        <th style="width: 38%; text-align: left;">NOM & PRÉNOM(S)</th>
        <th style="width: 6%;">SEXE</th>
        <th style="width: 11%;">STATUT</th>
        <th style="width: 11%;">CONTACT</th>
        <th style="width: 16%;">ÉMARGEMENT / OBS</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($etudiants) && is_array($etudiants)): ?>
        <?php foreach ($etudiants as $i => $e): ?>
          <tr>
            <td class="val-center"><?= $i + 1 ?></td>
            <td class="val-center" style="font-weight: bold;"><?= htmlspecialchars($e['matricule_etudiant'] ?? $e['num_gpeicg'] ?? '-') ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars(mb_strtoupper($e['nom_prenoms'] ?? ($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']))) ?></td>
            <td class="val-center"><?= htmlspecialchars($e['sexe'] ?? '-') ?></td>
            <td class="val-center"><?= htmlspecialchars($e['statut'] ?? $e['statut_affectation_etudiant'] ?? 'AFFECTE') ?></td>
            <td class="val-center"><?= htmlspecialchars($e['contact'] ?? $e['telephone_etudiant'] ?? '-') ?></td>
            <td class="val-center" style="height: 18px;"></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <?php 
          // Default mock demonstration rows
          $mockClassList = [
            ['mat' => 'YAYD0207010001', 'nom' => 'OUATTARA APATCHO ANDREA TISSEN', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0708091011'],
            ['mat' => 'LAGY1508020002', 'nom' => 'LAGOU YAO JEAN ROMARIC', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0102030405'],
            ['mat' => 'AKPW1203010003', 'nom' => 'AKPOUE KOUAKOU WILFRIED', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0506070809'],
            ['mat' => 'TOUR1011040004', 'nom' => 'TOURE NANDIEKPO JEAN-REGIS', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0711223344'],
            ['mat' => 'TANR1809050005', 'nom' => 'TANOU ROKIA', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0566778899'],
            ['mat' => 'KOFP2204060006', 'nom' => 'KOFFI AMOIN PRISCILLE', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0144556677'],
            ['mat' => 'KOFS2901070007', 'nom' => 'KOFFI KOUASSI SALOMON', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0788990011'],
            ['mat' => 'KOFN0406080008', 'nom' => 'KOFFI CHRIS RASSOU USHER NATHAN', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0522334455'],
            ['mat' => 'KOUA1907090009', 'nom' => 'KOUADIO AHOU ANGE', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0199887766'],
            ['mat' => 'CAML0102100010', 'nom' => 'CAMARA N\'GANLO LOSSA', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0733445566'],
            ['mat' => 'DOUM1205110011', 'nom' => 'DOUKOURE MATENIN', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0511223344'],
            ['mat' => 'DIAA2408120012', 'nom' => 'DIARRA ADJARATOU', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0744556677'],
            ['mat' => 'TRAM0901130013', 'nom' => 'TRAORE MAIMOUNA', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0155667788'],
            ['mat' => 'OUAM1803140014', 'nom' => 'OUATTARA MATAGARI', 'sexe' => 'F', 'statut' => 'AFFECTE', 'contact' => '0588990011'],
            ['mat' => 'SEKN2704150015', 'nom' => 'SEKONGO NAMARALA', 'sexe' => 'M', 'statut' => 'AFFECTE', 'contact' => '0722334455'],
          ];
        ?>
        <?php foreach ($mockClassList as $i => $e): ?>
          <tr>
            <td class="val-center"><?= $i + 1 ?></td>
            <td class="val-center" style="font-weight: bold;"><?= htmlspecialchars($e['mat']) ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars($e['nom']) ?></td>
            <td class="val-center"><?= htmlspecialchars($e['sexe']) ?></td>
            <td class="val-center"><?= htmlspecialchars($e['statut']) ?></td>
            <td class="val-center"><?= htmlspecialchars($e['contact']) ?></td>
            <td class="val-center" style="height: 18px;"></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BAS DE PAGE ET SIGNATURES -->
  <table class="footer-summary">
    <tr>
      <td style="width: 50%;">
        Bilan des Présences du jour : &nbsp;&nbsp;&nbsp;&nbsp; Présents : [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Absents : [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
      </td>
      <td style="width: 50%; text-align: right;">
        Fait à Bouaké, le <?= date('d/m/Y') ?>
      </td>
    </tr>
  </table>

  <table class="signature-grid">
    <tr>
      <td style="width: 50%;">
        <div style="font-weight: bold; text-decoration: underline;">Le Délégué de Classe</div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div style="font-weight: bold; text-decoration: underline;">Le Professeur / L'Administration</div>
      </td>
    </tr>
  </table>

</body>
</html>
