<?php
$annee_libelle = $annee_libelle ?? '-';
$photo_etudiant = $photo_etudiant ?? '';
$nom_prenom_etudiant = $nom_prenom_etudiant ?? '-';
$matricule_etudiant = $matricule_etudiant ?? '-';
$matricule_mesrs = $matricule_mesrs ?? $matricule_etudiant;
$date_naissance = $date_naissance ?? '-';
$lieu_naissance = $lieu_naissance ?? '-';
$sexe_etudiant = $sexe_etudiant ?? '-';
$filiere_libelle = $filiere_libelle ?? '-';
$niveau_libelle = $niveau_libelle ?? '-';
$classe_libelle = $classe_libelle ?? '-';
$classe_code_or_ref = $classe_code_or_ref ?? ($classe_libelle ?? '-');
$effectif_classe = $effectif_classe ?? '-';
$statut_affectation = $statut_affectation ?? '-';
$redoublant = $redoublant ?? 'NON';
$intitule_periode = $intitule_periode ?? 'SEMESTRE';
$matieresGenerales = $matieresGenerales ?? [];
$matieresPro = $matieresPro ?? [];
$matieres_notes = $matieres_notes ?? [];
$totalPointsGenerales = $totalPointsGenerales ?? 0;
$totalCoefficientsGenerales = $totalCoefficientsGenerales ?? 0;
$moyenneGenerales = $moyenneGenerales ?? 0;
$rangGeneralesFr = $rangGeneralesFr ?? '-';
$appreciationGenerales = $appreciationGenerales ?? '-';
$totalPointsPro = $totalPointsPro ?? 0;
$totalCoefficientsPro = $totalCoefficientsPro ?? 0;
$moyennePro = $moyennePro ?? 0;
$rangProFr = $rangProFr ?? '-';
$appreciationPro = $appreciationPro ?? '-';
$totalPoints = $totalPoints ?? 0;
$totalCoefficients = $totalCoefficients ?? 0;
$moyenneGenerale = $moyenneGenerale ?? 0;
$mention = $mention ?? '-';
$decision = $decision ?? '-';
$rang = $rang ?? '-';
$rangFr = $rangFr ?? ($rang !== '-' ? $rang . 'ème' : '-');
$totalElevesClasse = $totalElevesClasse ?? '-';
$moyenneMinClasse = $moyenneMinClasse ?? 0;
$moyenneMaxClasse = $moyenneMaxClasse ?? 0;
$moyenneMoyClasse = $moyenneMoyClasse ?? 0;
$heures_absences = $heures_absences ?? '0 H';
$moy_semestre1 = $moy_semestre1 ?? null;
$moy_semestre2 = $moy_semestre2 ?? null;
$moy_examen = $moy_examen ?? null;

if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}
$directeur_etudes = $directeur_etudes ?? ($etablissement['nom_directeur_etudes'] ?? '');
$titre_direction_pedagogique = !empty($etablissement['titre_direction_pedagogique']) ? $etablissement['titre_direction_pedagogique'] : '';
$ville_signature = !empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : ($etablissement['ville_etablissement'] ?? '');

$hasNotes = !empty($totalCoefficients) && (float)$totalCoefficients > 0;
$moyVal = (float)$moyenneGenerale;

if (!isset($code_distinction_sanction) && $hasNotes) {
    if (!class_exists('ModelConfiguration') && file_exists(__DIR__ . '/../../../models/configurations/ModelConfiguration.php')) {
        require_once __DIR__ . '/../../../models/configurations/ModelConfiguration.php';
    }
    if (class_exists('ModelConfiguration')) {
        $evalDyn = (new ModelConfiguration())->evaluateNote($moyVal);
        $code_distinction_sanction = $evalDyn['code_distinction_sanction'] ?? 'AUCUN';
    }
}
$codeDistSanct = strtoupper((string)($code_distinction_sanction ?? 'AUCUN'));

$dist_felicitations  = $hasNotes && ($codeDistSanct === 'FELICITATIONS');
$dist_encouragements = $hasNotes && ($codeDistSanct === 'ENCOURAGEMENTS');
$dist_honneur        = $hasNotes && ($codeDistSanct === 'HONNEUR');
$sanc_avertissement  = $hasNotes && ($codeDistSanct === 'AVERTISSEMENT');
$sanc_blame          = $hasNotes && ($codeDistSanct === 'BLAME');

$logoPath = '';
if (!empty($etablissement['logo_etablissement'])) {
    $cleanLogoRel = preg_replace('#^public/#', '', ltrim($etablissement['logo_etablissement'], '/'));
    foreach ([
        __DIR__ . '/../../../public/' . $cleanLogoRel,
        __DIR__ . '/../../../' . ltrim($etablissement['logo_etablissement'], '/'),
        __DIR__ . '/../../' . ltrim($etablissement['logo_etablissement'], '/')
    ] as $candLogo) {
        if (file_exists($candLogo) && is_file($candLogo)) {
            $logoPath = $candLogo;
            break;
        }
    }
}

$defaultPlaceholder = placeholderPath('etudiant');

$photoPath = '';
if (!empty($photo_etudiant)) {
    $cleanPhotoRel = preg_replace('#^public/#', '', ltrim($photo_etudiant, '/'));
    $cPaths = [
        __DIR__ . '/../../../public/' . $cleanPhotoRel,
        __DIR__ . '/../../../' . ltrim($photo_etudiant, '/'),
        __DIR__ . '/../../public/' . $cleanPhotoRel
    ];
    foreach ($cPaths as $cp) {
        if (file_exists($cp) && is_file($cp)) {
            $photoPath = $cp;
            break;
        }
    }
}

// Règle : si l'étudiant n'a pas d'image, utilisation systématique de la photo par défaut
if (empty($photoPath) && file_exists($defaultPlaceholder)) {
    $photoPath = $defaultPlaceholder;
}
?>
<?php if (empty($is_batch) || !empty($include_styles)): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bulletin de Notes - <?= htmlspecialchars($nom_prenom_etudiant) ?></title>
  <style>
    @page {
      margin: 6mm 7mm 6mm 7mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #000000;
      line-height: 1.15;
      margin: 0;
      padding: 0;
    }

    /* Conteneur interne (sans bordure de page) */
    .page-container {
      padding: 4px 6px 4px 6px;
      box-sizing: border-box;
    }

    /* PARTIE 1 : En-tête encadré spécifique au Bulletin */
    .bulletin-header-frame {
      border: 1.8px solid #000000;
      padding: 4px 8px;
      margin-bottom: 6px;
    }
    .bulletin-header-box {
      width: 100%;
      border-collapse: collapse;
    }
    .bulletin-header-box td {
      vertical-align: middle;
      padding: 2px 4px;
    }
    .inst-title {
      color: #C00000;
      font-size: 11.3pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.2px;
      text-align: center;
      line-height: 1.15;
    }
    .header-blue-line {
      width: 72%;
      margin: 4px auto;
      border-bottom: 2px solid #1E3A5F;
    }
    .inst-subtitle {
      font-size: 9.5pt;
      font-weight: bold;
      color: #1E3A5F;
      text-align: center;
      margin-top: 3px;
    }
    .fdfp-badge {
      color: #0284C7;
      font-weight: 900;
      font-style: italic;
      font-size: 13pt;
      letter-spacing: 0.5px;
    }
    .annee-header {
      font-size: 12.9pt;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 4px;
    }

    .sep-thick {
      border-bottom: 2px solid #000000;
      margin-top: 5px;
      margin-bottom: 4px;
    }

    /* Profil Étudiant */
    .student-title-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .student-name {
      font-size: 13.5pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      text-align: left;
    }
    .student-ref {
      font-size: 10.5pt;
      font-weight: bold;
      color: #000000;
      text-align: right;
    }

    .student-grid {
      width: 100%;
      border-collapse: collapse;
    }
    .student-grid td {
      vertical-align: top;
      font-size: 9.2pt;
      line-height: 1.4;
    }
    .photo-box {
      width: 120px;
      height: 140px;
      border: 1px solid #000000;
      object-fit: cover;
      display: block;
    }
    .photo-placeholder {
      width: 120px;
      height: 140px;
      border: 1px solid #000000;
      background: #F1F5F9;
      text-align: center;
      line-height: 106px;
      font-size: 8pt;
      color: #64748B;
      font-weight: bold;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
    }
    .info-table td {
      padding: 1.5px 0;
      font-size: 9.9pt;
    }

    /* Titres Bulletin */
    .bulletin-title-block {
      text-align: center;
      margin-top: 4px;
      margin-bottom: 5px;
    }
    .title-main {
      font-size: 16pt;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: underline;
      letter-spacing: 0.4px;
      color: #000000;
      line-height: 1.15;
    }
    .title-sub {
      font-size: 13pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      margin-top: 2px;
      line-height: 1.15;
    }

    /* PARTIE 2 : Tableau des Notes */
    .notes-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000000;
    }
    .notes-table th {
      background-color: #2C3A4A;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 9pt;
      border: 1px solid #000000;
      padding: 4px 3px;
      text-align: center;
    }
    .notes-table td {
      border: 1px solid #000000;
      padding: 2.5px 4px;
      font-size: 8.2pt;
      line-height: 1.15;
    }
    .subtotal-row td {
      background-color: #A6A6A6;
      font-weight: bold;
      font-size: 8.8pt;
    }
    .bilan-row td {
      background-color: #9BC2E6;
      font-weight: bold;
      font-size: 10.5pt;
      padding: 3px 4px;
    }

    /* PARTIE 3 : Bloc Inférieur */
    .bottom-matrix {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000000;
      margin-top: 4px;
    }
    .bottom-matrix > tbody > tr > td {
      vertical-align: top;
      padding: 0;
    }

    .col-left {
      width: 39%;
      border-right: 1px solid #000000;
      box-sizing: border-box;
    }
    .col-right {
      width: 61%;
      box-sizing: border-box;
    }

    .absences-box {
      padding: 5px 8px;
      font-size: 9.2pt;
      border-bottom: 1px solid #000000;
    }
    .mentions-box {
      padding: 5px 8px 6px 8px;
    }
    .mentions-main-title {
      font-weight: bold;
      text-decoration: underline;
      text-align: center;
      font-size: 8.6pt;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .mentions-sub-title {
      font-weight: bold;
      text-align: center;
      font-size: 8.6pt;
      margin-top: 3px;
      margin-bottom: 3px;
    }

    .chk-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 3px;
      font-size: 8.2pt;
    }
    .chk-table td {
      padding: 1px 0;
      vertical-align: middle;
    }
    .chk-label {
      text-align: left;
      padding-right: 8px !important;
      font-size: 9.2pt;
      padding: 0;
    }
    .chk-box-cell {
      width: 22px;
      height: 14px;
      border: 1px solid #000000;
      text-align: center;
      font-size: 8.5pt;
      font-weight: bold;
      background-color: #FFFFFF;
    }

    .recap-banner {
      background-color: #FFFFFF;
      font-weight: bold;
      font-size: 11pt;
      text-align: center;
      text-transform: uppercase;
      text-decoration: underline;
      letter-spacing: 0.4px;
      padding: 4px 2px 2px 2px;
    }
    .recap-body {
      padding: 3px 8px 5px 8px;
    }
    .recap-stats-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.2pt;
      line-height: 1.45;
    }
    .recap-stats-table td {
      padding: 1.5px 2px;
    }
    .moy-gen-highlight {
      background-color: #BFBFBF;
      padding: 2px 6px !important;
      font-size: 10.5pt;
      font-weight: bold;
    }
    .split-signature-table {
      width: 100%;
      border-collapse: collapse;
      border-top: 1px solid #000000;
    }
    .split-signature-table td {
      vertical-align: top;
      padding: 5px 6px;
    }

    /*Pied de page fixe en bas du cadre A4 */
    .footer-block {
      position: fixed;
      top: 278mm;
      left: 4mm;
      width: 188mm;
      text-align: center;
      font-size: 13pt;
      line-height: 1.3;
      color: #000000;
      border-top: 0.5px solid #CBD5E1;
      padding-top: 2px;
    }
  </style>
</head>
<body>
<?php endif; ?>

<div class="page-container">

  <!-- 1. EN-TÊTE ENCADRÉ SPÉCIFIQUE AU BULLETIN (PARTIE 1) -->
  <?php
    $nomEtab = !empty($etablissement['nom_etablissement']) ? $etablissement['nom_etablissement'] : '';
    $agrementRaw = trim((string)($etablissement['numero_autorisation'] ?? ''));
    $agrementPrefix = $agrementRaw;
    $agrementBadge = '';
    if (preg_match('/^(.*?\b(?:ET\s+L[AE]\s+))(FDFP|FDEP)(.*)$/iu', $agrementRaw, $mAgr)) {
        $agrementPrefix = trim($mAgr[1]);
        $agrementBadge = strtoupper(trim($mAgr[2]));
    }
  ?>
  <div class="bulletin-header-frame">
    <table class="bulletin-header-box">
      <tr>
        <td style="width: 26%; text-align: left;">
          <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
            <img src="<?= $logoPath ?>" style="height: 60px; width: 150px;" alt="Logo">
          <?php endif; ?>
        </td>
        <td style="width: 74%; text-align: center;">
          <div class="inst-title"><?= htmlspecialchars($nomEtab) ?></div>
          <table align="center" style="width: 72%; border-collapse: collapse; margin: 3px auto;">
            <tr><td style="border-bottom: 2px solid #1E3A5F; padding: 0; height: 2px; line-height: 2px;">&nbsp;</td></tr>
          </table>
          <?php if ($agrementRaw !== ''): ?>
            <div class="inst-subtitle">
              <?= htmlspecialchars($agrementPrefix) ?>
              <?php if ($agrementBadge !== ''): ?>
                &nbsp;<span class="fdfp-badge"><?= htmlspecialchars($agrementBadge) ?></span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="annee-header">Année Académique : <?= htmlspecialchars($annee_libelle) ?></div>
        </td>
      </tr>
    </table>
  </div>

  <!-- 2. BLOC PROFIL ÉTUDIANT -->
  <table class="student-title-table">
    <tr>
      <td class="student-name"><?= htmlspecialchars($nom_prenom_etudiant) ?></td>
      <td class="student-ref"><?= htmlspecialchars($matricule_etudiant) ?></td>
    </tr>
  </table>

  <table class="student-grid">
    <tr>
      <td style="width: 15%;">
        <?php if (!empty($photoPath) && file_exists($photoPath)): ?>
          <img src="<?= $photoPath ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-placeholder">PHOTO</div>
        <?php endif; ?>
      </td>
      <td style="width: 55%; padding-left: 6px;">
        <table class="info-table">
          <tr>
            <td style="width: 34%;">Matricule MESRS :</td>
            <td><strong><?= htmlspecialchars($matricule_mesrs) ?></strong></td>
          </tr>
          <tr>
            <td>Né( e) le :</td>
            <td><strong><?= htmlspecialchars($date_naissance) ?></strong></td>
          </tr>
          <tr>
            <td>Lieu :</td>
            <td><strong><?= htmlspecialchars($lieu_naissance) ?></strong></td>
          </tr>
          <tr>
            <td>Sexe :</td>
            <td><strong><?= htmlspecialchars($sexe_etudiant) ?></strong></td>
          </tr>
          <tr>
            <td>Filière :</td>
            <td><strong style="font-size: 10.5pt;"><?= htmlspecialchars($filiere_libelle) ?></strong></td>
          </tr>
          <tr>
            <td>Niveau :</td>
            <td><strong><?= htmlspecialchars($niveau_libelle) ?></strong></td>
          </tr>
        </table>
      </td>
      <td style="width: 30%;">
        <table class="info-table">
          <tr>
            <td style="text-align: right; width: 58%;">Effectif :</td>
            <td style="padding-left: 6px;"><strong><?= htmlspecialchars($effectif_classe) ?></strong></td>
          </tr>
          <tr>
            <td style="text-align: right;">Statut :</td>
            <td style="padding-left: 6px;"><strong><?= htmlspecialchars($statut_affectation) ?></strong></td>
          </tr>
          <tr>
            <td style="text-align: right;">Redoublant (e ) :</td>
            <td style="padding-left: 6px;"><strong><?= htmlspecialchars($redoublant) ?></strong></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="sep-thick"></div>

  <!-- 3. TITRE BULLETIN -->
  <div class="bulletin-title-block">
    <div class="title-main">BULLETIN DE NOTES</div>
    <div class="title-sub"><?= htmlspecialchars($intitule_periode) ?></div>
  </div>

  <!-- 4. TABLEAU DES NOTES (PARTIE 2) -->
  <table class="notes-table">
    <thead>
      <tr>
        <th style="width: 37%;">MATIERES</th>
        <th style="width: 7%;">Moy</th>
        <th style="width: 6%;">Coef</th>
        <th style="width: 8%;">MOY<br>Coef</th>
        <th style="width: 7%;">Rang</th>
        <th style="width: 22%;">Enseignants</th>
        <th style="width: 13%;">Appréciation</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $hasGenerales = !empty($matieresGenerales) && is_array($matieresGenerales);
        $hasPro = !empty($matieresPro) && is_array($matieresPro);
      ?>

      <?php if ($hasGenerales || $hasPro): ?>

        <!-- SECTION 1 : MATIÈRES GÉNÉRALES -->
        <?php if ($hasGenerales): ?>
          <?php foreach ($matieresGenerales as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['libelle'] ?? ($m['matiere'] ?? '-')) ?></td>
              <td style="text-align: right; padding-right: 5px;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: right; padding-right: 5px;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 0) : '-' ?></td>
              <td style="text-align: right; padding-right: 5px;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: left; padding-left: 4px;"><?= htmlspecialchars($m['rang_matiere'] ?? '-') ?></td>
              <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
              <td style="text-align: left; font-size: 8.5pt;"><?= htmlspecialchars($m['appreciation'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          <tr class="subtotal-row">
            <td style="text-transform: uppercase; text-align: center;">TOTAL MATIERES GENERALES :</td>
            <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($moyenneGenerales ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalCoefficientsGenerales ?? 0), 0) ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalPointsGenerales ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($rangGeneralesFr ?? '-') ?></td>
            <td colspan="2" style="text-align: center;"><?= htmlspecialchars($appreciationGenerales ?? '-') ?></td>
          </tr>
        <?php endif; ?>

        <!-- SECTION 2 : MATIÈRES PROFESSIONNELLES -->
        <?php if ($hasPro): ?>
          <?php foreach ($matieresPro as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['libelle'] ?? ($m['matiere'] ?? '-')) ?></td>
              <td style="text-align: right; padding-right: 5px;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: right; padding-right: 5px;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 0) : '-' ?></td>
              <td style="text-align: right; padding-right: 5px;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: left; padding-left: 4px;"><?= htmlspecialchars($m['rang_matiere'] ?? '-') ?></td>
              <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
              <td style="text-align: left; font-size: 8.5pt;"><?= htmlspecialchars($m['appreciation'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          <tr class="subtotal-row">
            <td style="text-transform: uppercase; text-align: center;">TOTAL MATIERES PROFESSIONNELLES :</td>
            <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($moyennePro ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalCoefficientsPro ?? 0), 0) ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalPointsPro ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($rangProFr ?? '-') ?></td>
            <td colspan="2" style="text-align: center;"><?= htmlspecialchars($appreciationPro ?? '-') ?></td>
          </tr>
        <?php endif; ?>

        <!-- LIGNE BILAN -->
        <tr class="bilan-row">
          <td style="text-align: center; font-size: 11pt;">Bilan</td>
          <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($moyenneGenerale ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalCoefficients ?? 0), 0) ?></td>
          <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalPoints ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($rangFr ?? '-') ?></td>
          <td colspan="2" style="text-align: center; font-size: 11pt;"><?= htmlspecialchars($mention ?? '-') ?></td>
        </tr>

      <?php elseif (!empty($matieres_notes) && is_array($matieres_notes)): ?>

        <!-- FALLBACK SI TOUTES MATIERES -->
        <?php foreach ($matieres_notes as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['libelle'] ?? ($m['matiere'] ?? '-')) ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : '-' ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 0) : '-' ?></td>
            <td style="text-align: right; padding-right: 5px;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : '-' ?></td>
            <td style="text-align: left; padding-left: 4px;"><?= htmlspecialchars($m['rang_matiere'] ?? '-') ?></td>
            <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
            <td style="text-align: left; font-size: 8.5pt;"><?= htmlspecialchars($m['appreciation'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="bilan-row">
          <td style="text-align: center; font-size: 11pt;">Bilan</td>
          <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($moyenneGenerale ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalCoefficients ?? 0), 0) ?></td>
          <td style="text-align: right; padding-right: 5px;"><?= number_format((float)($totalPoints ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($rangFr ?? '-') ?></td>
          <td colspan="2" style="text-align: center; font-size: 11pt;"><?= htmlspecialchars($mention ?? '-') ?></td>
        </tr>

      <?php else: ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 12px; color: #64748B; font-style: italic;">
            Aucune note enregistrée pour cette période.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- 5. BLOC INFÉRIEUR CONFORME AU MODÈLE (PARTIE 3) -->
  <table class="bottom-matrix">
    <tbody>
      <tr>
        <!-- COLONNE GAUCHE (ABSENCES & MENTIONS) -->
        <td class="col-left">
          <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid #000000;">
            <tr>
              <td style="padding: 6px 8px; text-align: left; font-size: 9.5pt; white-space: nowrap;">Heures d'Absences :</td>
              <td style="padding: 6px 14px; text-align: right; font-size: 10.5pt; font-weight: bold;"><?= htmlspecialchars(trim(preg_replace('/\s*\(.*\)$/', '', (string)$heures_absences))) ?></td>
            </tr>
          </table>

          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 8.6pt; padding-top: 5px; padding-bottom: 3px;">
                MENTIONS DU CONSEIL DE CLASSE
              </td>
            </tr>
            <tr>
              <td style="text-align: center; font-weight: bold; font-size: 8.6pt; padding-bottom: 2px;">
                Distinctions
              </td>
            </tr>
            <tr>
              <td style="padding: 0 12px 0 6px;">
                <table class="chk-table" style="border-collapse: collapse;">
                  <tr>
                    <td class="chk-label">Tableau d'honneur + Félicitations</td>
                    <td class="chk-box-cell"><?= $dist_felicitations ? 'X' : '&nbsp;' ?></td>
                  </tr>
                  <tr><td colspan="2" style="height: 4px; font-size: 1pt; line-height: 4px; border: none;">&nbsp;</td></tr>
                  <tr>
                    <td class="chk-label">Tableau d'honneur + Encouragements</td>
                    <td class="chk-box-cell"><?= $dist_encouragements ? 'X' : '&nbsp;' ?></td>
                  </tr>
                  <tr><td colspan="2" style="height: 4px; font-size: 1pt; line-height: 4px; border: none;">&nbsp;</td></tr>
                  <tr>
                    <td class="chk-label">Tableau d'honneur</td>
                    <td class="chk-box-cell"><?= $dist_honneur ? 'X' : '&nbsp;' ?></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td style="text-align: center; font-weight: bold; font-size: 8.6pt; padding-top: 4px; padding-bottom: 2px;">
                Sanctions
              </td>
            </tr>
            <tr>
              <td style="padding: 0 12px 5px 6px;">
                <table class="chk-table" style="border-collapse: collapse;">
                  <tr>
                    <td class="chk-label">Avertissement Travail</td>
                    <td class="chk-box-cell"><?= $sanc_avertissement ? 'X' : '&nbsp;' ?></td>
                  </tr>
                  <tr><td colspan="2" style="height: 4px; font-size: 1pt; line-height: 4px; border: none;">&nbsp;</td></tr>
                  <tr>
                    <td class="chk-label">Blâme Travail</td>
                    <td class="chk-box-cell"><?= $sanc_blame ? 'X' : '&nbsp;' ?></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>

        <!-- COLONNE DROITE (RÉCAPITULATIF ANNUEL & SIGNATURE) -->
        <td class="col-right">
          <div class="recap-body">
            <table class="recap-stats-table">
              <tr>
                <td colspan="2" style="text-align: center; font-weight: bold; font-size: 11.5pt; text-decoration: underline; padding-top: 2px; padding-bottom: 5px;">
                  RECAPITULATIF ANNUEL
                </td>
              </tr>
              <tr>
                <td style="width: 48%; padding-left: 6px;">
                  Moy. Semestre 1 : &nbsp;<strong><?= $moy_semestre1 !== null ? number_format((float)$moy_semestre1, 2, ',', ' ') : '-' ?></strong>
                </td>
                <td style="width: 52%; padding-left: 6px;">
                  Moy. Semestre 2 : &nbsp;<strong><?= $moy_semestre2 !== null ? number_format((float)$moy_semestre2, 2, ',', ' ') : '-' ?></strong>
                </td>
              </tr>
              <tr>
                <td style="padding-left: 6px;">
                  Moy. Examen : &nbsp;&nbsp;&nbsp;&nbsp;<strong><?= $moy_examen !== null ? number_format((float)$moy_examen, 2, ',', ' ') : '-' ?></strong>
                </td>
                <td class="moy-gen-highlight">
                  Moy. Générale : &nbsp;<?= number_format((float)$moyenneGenerale, 2, ',', ' ') ?>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: center; padding-top: 5px; padding-bottom: 3px; font-size: 10pt;">
                  <strong>Rang : <?= htmlspecialchars($rangFr) ?></strong> &nbsp;/<?= htmlspecialchars($totalElevesClasse) ?> Etudiants
                </td>
              </tr>
              <tr>
                <td colspan="2" style="font-size: 7.6pt; padding-top: 4px; padding-left: 4px; font-size: 8.8pt;">
                  Moy. Min : &nbsp;<strong><?= number_format((float)$moyenneMinClasse, 2, ',', ' ') ?></strong> &nbsp;&nbsp;&nbsp;
                  Moy. Max : &nbsp;<strong><?= number_format((float)$moyenneMaxClasse, 2, ',', ' ') ?></strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  Moy. de la classe : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?= number_format((float)$moyenneMoyClasse, 2, ',', ' ') ?></strong>
                </td>
              </tr>
            </table>
          </div>

          <table class="split-signature-table">
            <tr>
              <td style="width: 42%; border-right: 1px solid #000000; text-align: center;">
                <div style="font-weight: bold; text-decoration: underline; font-size: 9.2pt;">Appréciation du conseil</div>
                <br>
                <div style="font-size: 9pt; font-weight: bold; color: #0F172A;">
                  <?= htmlspecialchars($decision) ?>
                </div>
              </td>
              <td style="width: 58%; text-align: center;">
                <div style="font-size: 8.3pt; font-style: italic;">Fait à <?= htmlspecialchars($ville_signature) ?> le <?= date('d/m/Y') ?></div>
                <div style="font-weight: bold; text-decoration: underline; font-size: 9.2pt; text-transform: uppercase;"><?= htmlspecialchars(mb_strtoupper($titre_direction_pedagogique)) ?></div>
                <br><br><br>
                <div style="font-weight: bold; font-size: 9.2pt;"><?= htmlspecialchars($directeur_etudes) ?></div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

</div>

<!-- 6. PIED DE PAGE CONTACTS (enfant direct de body pour position:fixed mPDF) -->
<?php
  $contactsTxt = !empty($etablissement['contacts_formatted']) ? $etablissement['contacts_formatted'] : '';
  $adresseTxt = !empty($etablissement['adresse_etablissement']) && $etablissement['adresse_etablissement'] !== 'adresse' ? $etablissement['adresse_etablissement'] : '';
  $bpTxt = !empty($etablissement['boite_postale']) ? $etablissement['boite_postale'] : '';
  $emailTxt = !empty($etablissement['email_etablissement']) ? $etablissement['email_etablissement'] : '';
?>
<div class="footer-block">
  Contacts : <?= htmlspecialchars($adresseTxt) ?><br>
  <?= htmlspecialchars($bpTxt) ?> – Email : <?= htmlspecialchars($emailTxt) ?> | Contacts : <?= htmlspecialchars($contactsTxt) ?>
</div>

<?php if (empty($is_batch) || !empty($is_last_in_batch)): ?>
</body>
</html>
<?php endif; ?>
