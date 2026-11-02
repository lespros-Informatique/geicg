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
$statut_affectation = $statut_affectation ?? 'AFFECTE';
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
$directeur_etudes = $directeur_etudes ?? 'Dr COULIBALY Sonan Hamed';

$hasNotes = !empty($totalCoefficients) && (float)$totalCoefficients > 0;
$moyVal = (float)$moyenneGenerale;

$dist_felicitations = $hasNotes && ($moyVal >= 16);
$dist_encouragements = $hasNotes && ($moyVal >= 14 && $moyVal < 16);
$dist_honneur = $hasNotes && ($moyVal >= 12 && $moyVal < 14);
$sanc_avertissement = $hasNotes && ($moyVal < 10 && $moyVal >= 8);
$sanc_blame = $hasNotes && ($moyVal < 8);

$logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
}

$photoPath = '';
if (!empty($photo_etudiant)) {
    $cPaths = [
        __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'),
        __DIR__ . '/../../../public/' . ltrim($photo_etudiant, '/')
    ];
    foreach ($cPaths as $cp) {
        if (file_exists($cp)) {
            $photoPath = $cp;
            break;
        }
    }
}
?>
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

    /* Cadre englobant conforme au modèle */
    .page-container {
      border: 1.5px solid #000000;
      padding: 6px 8px 6px 8px;
      box-sizing: border-box;
    }

    /* En-tête */
    .header-table {
      width: 100%;
      border-collapse: collapse;
    }
    .header-table td {
      vertical-align: middle;
    }
    .inst-title {
      color: #990000;
      font-size: 10.5pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.2px;
      text-align: center;
      line-height: 1.1;
    }
    .inst-subtitle {
      font-size: 8.5pt;
      font-weight: bold;
      color: #000000;
      text-align: center;
      margin-top: 3px;
    }
    .fdfp-badge {
      color: #0284C7;
      font-weight: 900;
      font-style: italic;
      font-size: 11pt;
      letter-spacing: 0.5px;
    }
    .annee-header {
      font-size: 11pt;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 3px;
    }

    .sep-thick {
      border-bottom: 2px solid #000000;
      margin-top: 5px;
      margin-bottom: 5px;
    }

    /* Profil Étudiant */
    .student-title-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .student-name {
      font-size: 13pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #000000;
      text-align: left;
    }
    .student-ref {
      font-size: 11pt;
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
      font-size: 8.5pt;
      line-height: 1.35;
    }
    .photo-box {
      width: 75px;
      height: 92px;
      border: 1px solid #000000;
      object-fit: cover;
      display: block;
    }
    .photo-placeholder {
      width: 75px;
      height: 92px;
      border: 1px solid #000000;
      background: #F1F5F9;
      text-align: center;
      line-height: 92px;
      font-size: 8pt;
      color: #64748B;
      font-weight: bold;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
    }
    .info-table td {
      padding: 1px 0;
      font-size: 8.5pt;
    }

    /* Titres Bulletin */
    .bulletin-title-block {
      text-align: center;
      margin-top: 3px;
      margin-bottom: 5px;
    }
    .title-main {
      font-size: 16pt;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #000000;
      line-height: 1.1;
    }
    .title-sub {
      font-size: 13pt;
      font-weight: 800;
      text-transform: uppercase;
      color: #000000;
      margin-top: 2px;
      line-height: 1.1;
    }

    /* Tableau des Notes */
    .notes-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000000;
    }
    .notes-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 8pt;
      border: 1px solid #000000;
      padding: 4px 2px;
      text-align: center;
    }
    .notes-table td {
      border: 1px solid #000000;
      padding: 2.5px 3.5px;
      font-size: 7.8pt;
      line-height: 1.15;
    }
    .subtotal-row td {
      background-color: #D9D9D9;
      font-weight: bold;
      font-size: 8pt;
    }
    .bilan-row td {
      background-color: #BDD7EE;
      font-weight: bold;
      font-size: 8.5pt;
    }

    /* Bloc Inférieur */
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
      border-right: 1.5px solid #000000;
      box-sizing: border-box;
    }
    .col-right {
      width: 61%;
      box-sizing: border-box;
    }

    .absences-box {
      padding: 4px 6px;
      font-size: 8.5pt;
      border-bottom: 1.5px solid #000000;
    }
    .mentions-box {
      padding: 4px 6px 5px 6px;
    }
    .mentions-main-title {
      font-weight: bold;
      text-decoration: underline;
      text-align: center;
      font-size: 8.5pt;
      text-transform: uppercase;
      margin-bottom: 3px;
    }
    .mentions-sub-title {
      font-weight: bold;
      text-align: center;
      font-size: 8pt;
      margin-top: 2px;
      margin-bottom: 2px;
    }

    .chk-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 7.5pt;
    }
    .chk-table td {
      padding: 1.5px 0;
    }
    .chk-box {
      width: 17px;
      height: 11px;
      border: 1px solid #000000;
      text-align: center;
      line-height: 11px;
      font-size: 8pt;
      font-weight: bold;
      margin-left: auto;
    }

    .recap-banner {
      background-color: #FFFFFF;
      font-weight: bold;
      font-size: 10.5pt;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 2.5px;
      border-bottom: 1.5px solid #000000;
    }
    .recap-body {
      padding: 4px 6px;
    }
    .recap-stats-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8pt;
      line-height: 1.5;
    }
    .split-signature-table {
      width: 100%;
      border-collapse: collapse;
      border-top: 1.5px solid #000000;
    }
    .split-signature-table td {
      vertical-align: top;
      padding: 4px 6px;
    }

    /* Pied de page */
    .footer-block {
      text-align: center;
      font-size: 6.8pt;
      margin-top: 5px;
      line-height: 1.25;
      color: #000000;
    }
  </style>
</head>
<body>

<div class="page-container">

  <!-- 1. EN-TÊTE OFFICIEL -->
  <table class="header-table">
    <tr>
      <td style="width: 18%; text-align: left;">
        <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 52px; max-width: 110px;">
        <?php else: ?>
          <div style="font-weight: bold; color: #990000; font-size: 11pt;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 82%; text-align: center;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE <span class="fdfp-badge">FDFP</span></div>
        <div class="annee-header">Année Académique : <?= htmlspecialchars($annee_libelle) ?></div>
      </td>
    </tr>
  </table>

  <div class="sep-thick"></div>

  <!-- 2. BLOC PROFIL ÉTUDIANT -->
  <table class="student-title-table">
    <tr>
      <td class="student-name"><?= htmlspecialchars($nom_prenom_etudiant) ?></td>
      <td class="student-ref"><?= htmlspecialchars($classe_code_or_ref) ?></td>
    </tr>
  </table>

  <table class="student-grid">
    <tr>
      <td style="width: 14%;">
        <?php if (!empty($photoPath)): ?>
          <img src="<?= $photoPath ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-placeholder">PHOTO</div>
        <?php endif; ?>
      </td>
      <td style="width: 54%; padding-left: 6px;">
        <table class="info-table">
          <tr>
            <td style="width: 38%;">Matricule MESRS :</td>
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
            <td><strong><?= htmlspecialchars($filiere_libelle) ?></strong></td>
          </tr>
          <tr>
            <td>Niveau :</td>
            <td><strong><?= htmlspecialchars($niveau_libelle) ?></strong></td>
          </tr>
        </table>
      </td>
      <td style="width: 32%;">
        <table class="info-table">
          <tr>
            <td style="text-align: right; width: 62%;">Effectif :</td>
            <td style="padding-left: 8px;"><strong><?= htmlspecialchars($effectif_classe) ?></strong></td>
          </tr>
          <tr>
            <td style="text-align: right;">Statut :</td>
            <td style="padding-left: 8px;"><strong><?= htmlspecialchars($statut_affectation) ?></strong></td>
          </tr>
          <tr>
            <td style="text-align: right;">Redoublant (e ) :</td>
            <td style="padding-left: 8px;"><strong><?= htmlspecialchars($redoublant) ?></strong></td>
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

  <!-- 4. TABLEAU DES NOTES -->
  <table class="notes-table">
    <thead>
      <tr>
        <th style="width: 36%;">MATIERES</th>
        <th style="width: 7%;">Moy</th>
        <th style="width: 6%;">Coef</th>
        <th style="width: 8%;">MOY<br>Coef</th>
        <th style="width: 7%;">Rang</th>
        <th style="width: 22%;">Enseignants</th>
        <th style="width: 14%;">Appréciation</th>
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
              <td style="text-align: center; font-weight: bold;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: center;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 0) : '-' ?></td>
              <td style="text-align: center; font-weight: bold;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: center;"><?= htmlspecialchars($m['rang_matiere'] ?? '-') ?></td>
              <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
              <td style="text-align: left; font-size: 7.8pt;"><?= htmlspecialchars($m['appreciation'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
          <tr class="subtotal-row">
            <td style="text-transform: uppercase;">TOTAL MATIERES GENERALES :</td>
            <td style="text-align: center;"><?= number_format((float)($moyenneGenerales ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: center;"><?= number_format((float)($totalCoefficientsGenerales ?? 0), 0) ?></td>
            <td style="text-align: center;"><?= number_format((float)($totalPointsGenerales ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($rangGeneralesFr ?? '-') ?></td>
            <td></td>
            <td style="text-align: center;"><?= htmlspecialchars($appreciationGenerales ?? '-') ?></td>
          </tr>
        <?php endif; ?>

        <!-- SECTION 2 : MATIÈRES PROFESSIONNELLES -->
        <?php if ($hasPro): ?>
          <?php foreach ($matieresPro as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['libelle'] ?? ($m['matiere'] ?? '-')) ?></td>
              <td style="text-align: center; font-weight: bold;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: center;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 0) : '-' ?></td>
              <td style="text-align: center; font-weight: bold;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : '-' ?></td>
              <td style="text-align: center;"><?= htmlspecialchars($m['rang_matiere'] ?? '-') ?></td>
              <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
              <td style="text-align: left; font-size: 7.8pt;"><?= htmlspecialchars($m['appreciation'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
          <tr class="subtotal-row">
            <td style="text-transform: uppercase;">TOTAL MATIERES PROFESSIONNELLES :</td>
            <td style="text-align: center;"><?= number_format((float)($moyennePro ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: center;"><?= number_format((float)($totalCoefficientsPro ?? 0), 0) ?></td>
            <td style="text-align: center;"><?= number_format((float)($totalPointsPro ?? 0), 2, ',', ' ') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($rangProFr ?? '-') ?></td>
            <td></td>
            <td style="text-align: center;"><?= htmlspecialchars($appreciationPro ?? '-') ?></td>
          </tr>
        <?php endif; ?>

        <!-- LIGNE BILAN -->
        <tr class="bilan-row">
          <td style="text-align: center; font-size: 9pt;">Bilan</td>
          <td style="text-align: center; font-size: 9pt;"><?= number_format((float)($moyenneGenerale ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: center; font-size: 9pt;"><?= number_format((float)($totalCoefficients ?? 0), 0) ?></td>
          <td style="text-align: center; font-size: 9pt;"><?= number_format((float)($totalPoints ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: center; font-size: 9pt;"><?= htmlspecialchars($rangFr ?? '-') ?></td>
          <td></td>
          <td style="text-align: center; font-size: 9pt;"><?= htmlspecialchars($mention ?? '-') ?></td>
        </tr>

      <?php elseif (!empty($matieres_notes) && is_array($matieres_notes)): ?>

        <!-- FALLBACK SI TOUTES MATIERES -->
        <?php foreach ($matieres_notes as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['libelle'] ?? ($m['matiere'] ?? '-')) ?></td>
            <td style="text-align: center; font-weight: bold;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : '-' ?></td>
            <td style="text-align: center;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 0) : '-' ?></td>
            <td style="text-align: center; font-weight: bold;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : '-' ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($m['rang_matiere'] ?? '-') ?></td>
            <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
            <td style="text-align: left; font-size: 7.8pt;"><?= htmlspecialchars($m['appreciation'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="bilan-row">
          <td style="text-align: center; font-size: 9pt;">Bilan</td>
          <td style="text-align: center; font-size: 9pt;"><?= number_format((float)($moyenneGenerale ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: center; font-size: 9pt;"><?= number_format((float)($totalCoefficients ?? 0), 0) ?></td>
          <td style="text-align: center; font-size: 9pt;"><?= number_format((float)($totalPoints ?? 0), 2, ',', ' ') ?></td>
          <td style="text-align: center; font-size: 9pt;"><?= htmlspecialchars($rangFr ?? '-') ?></td>
          <td></td>
          <td style="text-align: center; font-size: 9pt;"><?= htmlspecialchars($mention ?? '-') ?></td>
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

  <!-- 5. BLOC INFÉRIEUR CONFORME AU MODÈLE -->
  <table class="bottom-matrix">
    <tbody>
      <tr>
        <!-- COLONNE GAUCHE (ABSENCES & MENTIONS) -->
        <td class="col-left">
          <div class="absences-box">
            Heures d'Absences : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <strong style="font-size: 11pt;"><?= htmlspecialchars($heures_absences) ?></strong>
          </div>
          <div class="mentions-box">
            <div class="mentions-main-title">MENTIONS DU CONSEIL DE CLASSE</div>
            
            <div class="mentions-sub-title">Distinctions</div>
            <table class="chk-table">
              <tr>
                <td>Tableau d'honneur + Félicitations</td>
                <td style="width: 24px;"><div class="chk-box"><?= $dist_felicitations ? 'X' : '' ?></div></td>
              </tr>
              <tr>
                <td>Tableau d'honneur + Encouragements</td>
                <td><div class="chk-box"><?= $dist_encouragements ? 'X' : '' ?></div></td>
              </tr>
              <tr>
                <td>Tableau d'honneur</td>
                <td><div class="chk-box"><?= $dist_honneur ? 'X' : '' ?></div></td>
              </tr>
            </table>

            <div class="mentions-sub-title" style="margin-top: 4px;">Sanctions</div>
            <table class="chk-table">
              <tr>
                <td>Avertissement Travail</td>
                <td style="width: 24px;"><div class="chk-box"><?= $sanc_avertissement ? 'X' : '' ?></div></td>
              </tr>
              <tr>
                <td>Blâme Travail</td>
                <td><div class="chk-box"><?= $sanc_blame ? 'X' : '' ?></div></td>
              </tr>
            </table>
          </div>
        </td>

        <!-- COLONNE DROITE (RÉCAPITULATIF ANNUEL & SIGNATURE) -->
        <td class="col-right">
          <div class="recap-banner">RECAPITULATIF ANNUEL</div>
          <div class="recap-body">
            <table class="recap-stats-table">
              <tr>
                <td style="width: 50%;">
                  Moy. Semestre 1 : <strong><?= $moy_semestre1 !== null ? number_format((float)$moy_semestre1, 2, ',', ' ') : '-' ?></strong>
                </td>
                <td style="width: 50%;">
                  Moy. Semestre 2 : <strong><?= $moy_semestre2 !== null ? number_format((float)$moy_semestre2, 2, ',', ' ') : '-' ?></strong>
                </td>
              </tr>
              <tr>
                <td>
                  Moy. Examen : <strong><?= $moy_examen !== null ? number_format((float)$moy_examen, 2, ',', ' ') : '-' ?></strong>
                </td>
                <td>
                  <strong style="font-size: 9.5pt;">Moy. Générale : <?= number_format((float)$moyenneGenerale, 2, ',', ' ') ?></strong>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="padding-top: 2px;">
                  Rang : <strong><?= htmlspecialchars($rangFr) ?></strong> &nbsp; / <?= htmlspecialchars($totalElevesClasse) ?> Etudiants
                </td>
              </tr>
              <tr>
                <td colspan="2" style="font-size: 7.2pt; padding-top: 3px;">
                  Moy. Min : <strong><?= number_format((float)$moyenneMinClasse, 2, ',', ' ') ?></strong> &nbsp;&nbsp;&nbsp;
                  Moy. Max : <strong><?= number_format((float)$moyenneMaxClasse, 2, ',', ' ') ?></strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  Moy. de la classe : <strong><?= number_format((float)$moyenneMoyClasse, 2, ',', ' ') ?></strong>
                </td>
              </tr>
            </table>
          </div>

          <table class="split-signature-table">
            <tr>
              <td style="width: 44%; border-right: 1.5px solid #000000;">
                <div style="font-weight: bold; text-decoration: underline; text-align: center; font-size: 8pt; margin-bottom: 3px;">Appréciation du conseil</div>
                <div style="font-size: 8pt; font-weight: bold; color: #0F172A; min-height: 52px;">
                  <?= htmlspecialchars($decision) ?>
                </div>
              </td>
              <td style="width: 56%; text-align: center;">
                <div style="font-size: 7.5pt; margin-bottom: 2px;">Fait à Bouaké le <?= date('d/m/Y') ?></div>
                <div style="font-weight: bold; text-decoration: underline; font-size: 8pt; text-transform: uppercase;">LE DIRECTEUR DES ETUDES</div>
                <div style="height: 38px;"></div>
                <div style="font-weight: bold; font-size: 8.5pt;"><?= htmlspecialchars($directeur_etudes) ?></div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- 6. PIED DE PAGE CONTACTS -->
  <div class="footer-block">
    Contacts : Bouaké quartier Kennedy, route ancien ADDR<br>
    01 BP 960 Bouaké 01 – Email : eicgbouake01@gmail.com | Tél : 27 31 62 40 57 – Cél : 07 79 37 37 38 / 05 04 59 39 99
  </div>

</div>

</body>
</html>
