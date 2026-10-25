<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bulletin de Notes & Récapitulatif Académique</title>
  <style>
    @page {
      margin: 6mm 8mm 6mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 10px;
      color: #000000;
      line-height: 1.2;
    }

    /* Header */
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
      font-size: 10.5px;
      font-weight: bold;
      text-align: center;
      color: #004080;
      margin-top: 1px;
    }
    .annee-header {
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      margin-top: 2px;
      margin-bottom: 4px;
    }

    /* Profil Etudiant & Photo */
    .student-block {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .student-block td {
      vertical-align: top;
      padding: 1.5px 3px;
      font-size: 10px;
    }
    .val-bold {
      font-weight: bold;
    }
    .photo-box {
      width: 75px;
      height: 90px;
      border: 1px solid #000000;
      object-fit: cover;
    }

    /* Bannières Titres */
    .banner-title {
      background: #1E3A5F;
      color: #FFFFFF;
      font-size: 14px;
      font-weight: bold;
      text-align: center;
      padding: 4px;
      margin-top: 4px;
      margin-bottom: 2px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .banner-subtitle {
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 4px;
      text-transform: uppercase;
      color: #0F172A;
    }

    /* Tableau des Notes */
    .notes-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .notes-table th {
      background: #1E3A5F;
      color: #FFFFFF;
      border: 1px solid #0F172A;
      padding: 4px;
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
    }
    .notes-table td {
      border: 1px solid #CBD5E1;
      padding: 3.5px 4px;
      font-size: 9.5px;
    }
    .subtotal-row td {
      background: #E2E8F0;
      font-weight: bold;
      border: 1px solid #94A3B8;
    }
    .bilan-row td {
      background: #CBD5E1;
      font-weight: bold;
      font-size: 10px;
      border: 1px solid #64748B;
    }

    /* Blocs Inférieurs (Absences, Mentions & Récap) */
    .bottom-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    .bottom-table td {
      vertical-align: top;
    }

    .mentions-box {
      border: 1px solid #94A3B8;
      padding: 6px 8px;
      height: 175px;
      border-radius: 4px;
    }
    .box-title {
      font-weight: bold;
      text-align: center;
      text-decoration: underline;
      font-size: 10px;
      margin-bottom: 6px;
      color: #1E3A5F;
    }
    .checkbox-item {
      margin-bottom: 4px;
      font-size: 9px;
    }
    .checkbox-sq {
      display: block;
      width: 11px;
      height: 11px;
      border: 1px solid #000000;
      float: right;
      margin-top: 1px;
      text-align: center;
      line-height: 11px;
      font-size: 8.5px;
      font-weight: bold;
    }

    .recap-box {
      border: 1px solid #94A3B8;
      padding: 6px 8px;
      height: 175px;
      border-radius: 4px;
    }
    .recap-header {
      font-size: 11px;
      font-weight: bold;
      text-align: center;
      text-decoration: underline;
      margin-bottom: 6px;
      color: #1E3A5F;
    }
    .recap-grid {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .recap-grid td {
      padding: 2.5px 4px;
      font-size: 9.5px;
    }

    /* Footer Contacts */
    .footer-contacts {
      text-align: center;
      font-size: 8.5px;
      border-top: 1px solid #CBD5E1;
      padding-top: 4px;
      margin-top: 8px;
      color: #475569;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 20%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 46px; max-width: 120px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#990000; font-size:13px;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 80%; text-align: center; vertical-align: middle;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE FDFP</div>
        <div class="annee-header">Année Académique : <?= htmlspecialchars($annee_libelle ?? '-') ?></div>
      </td>
    </tr>
  </table>

  <!-- PROFIL ÉTUDIANT -->
  <table class="student-block">
    <tr>
      <td style="width: 14%;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 90px; color: #94A3B8; font-size: 8px;">PHOTO</div>
        <?php endif; ?>
      </td>
      <td style="width: 58%;">
        <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px; color: #0F172A;"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></div>
        <table style="width: 100%;">
          <tr>
            <td style="width: 32%;">Matricule :</td>
            <td style="width: 68%;"><span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Né(e) le :</td>
            <td><span class="val-bold"><?= htmlspecialchars($date_naissance ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Lieu :</td>
            <td><span class="val-bold"><?= htmlspecialchars($lieu_naissance ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Sexe :</td>
            <td><span class="val-bold"><?= htmlspecialchars($sexe_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Filière :</td>
            <td><span class="val-bold"><?= htmlspecialchars($filiere_libelle ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Niveau :</td>
            <td><span class="val-bold"><?= htmlspecialchars($niveau_libelle ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 28%; text-align: right; vertical-align: top;">
        <table style="width: 100%; text-align: right;">
          <tr>
            <td>Classe : <span class="val-bold"><?= htmlspecialchars($classe_libelle ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Effectif : <span class="val-bold"><?= htmlspecialchars($effectif_classe ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- TITRE BULLETIN -->
  <div class="banner-title">BULLETIN DE NOTES</div>
  <div class="banner-subtitle"><?= htmlspecialchars($intitule_periode ?? 'SEMESTRE') ?></div>

  <!-- TABLEAU DES NOTES -->
  <table class="notes-table">
    <thead>
      <tr>
        <th style="width: 44%; text-align: left;">MATIÈRES</th>
        <th style="width: 8%;">Moyenne /20</th>
        <th style="width: 8%;">Coefficient</th>
        <th style="width: 10%;">Points Pondérés</th>
        <th style="width: 15%;">Enseignant</th>
        <th style="width: 15%;">Appréciation</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($matieres_notes) && is_array($matieres_notes)): ?>
        <?php foreach ($matieres_notes as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['matiere'] ?? ($m['libelle'] ?? '-')) ?></td>
            <td style="text-align: center; font-weight: 700;"><?= isset($m['moyenne']) ? number_format((float)$m['moyenne'], 2, ',', ' ') : (htmlspecialchars($m['moy'] ?? '-')) ?></td>
            <td style="text-align: center;"><?= isset($m['coefficient']) ? number_format((float)$m['coefficient'], 2, ',', ' ') : (htmlspecialchars($m['coef'] ?? '-')) ?></td>
            <td style="text-align: center; font-weight: 700;"><?= isset($m['points']) ? number_format((float)$m['points'], 2, ',', ' ') : (htmlspecialchars($m['total'] ?? '-')) ?></td>
            <td><?= htmlspecialchars($m['prof'] ?? ($m['enseignant'] ?? '-')) ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($m['appreciation'] ?? ($m['appr'] ?? '-')) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" style="text-align: center; padding: 12px; color: #64748B; font-style: italic;">
            Aucune note enregistrée pour cette période.
          </td>
        </tr>
      <?php endif; ?>

      <!-- LIGNE TOTAUX / BILAN -->
      <?php if (!empty($totalCoefficients) && $totalCoefficients > 0): ?>
      <tr class="bilan-row">
        <td style="text-align: right; font-weight: bold;">TOTAL & BILAN GÉNÉRAL :</td>
        <td style="text-align: center; font-size: 11px;"><?= number_format((float)($moyenneGenerale ?? 0), 2, ',', ' ') ?> /20</td>
        <td style="text-align: center; font-size: 11px;"><?= number_format((float)($totalCoefficients ?? 0), 2, ',', ' ') ?></td>
        <td style="text-align: center; font-size: 11px;"><?= number_format((float)($totalPoints ?? 0), 2, ',', ' ') ?></td>
        <td></td>
        <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($mention ?? '-') ?></td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BAS DU BULLETIN : ABSENCES, MENTIONS & RÉCAPITULATIF ANNUEL -->
  <table class="bottom-table">
    <tr>
      <!-- COLONNE GAUCHE : MENTIONS DU CONSEIL -->
      <td style="width: 38%; padding-right: 6px;">
        <div class="mentions-box">
          <div style="font-size: 11px; margin-bottom: 6px;">
            Heures d'Absences : &nbsp;&nbsp;&nbsp;&nbsp; <strong><?= htmlspecialchars($heures_absences ?? '0 H') ?></strong>
          </div>
          
          <div class="box-title">MENTIONS DU CONSEIL DE CLASSE</div>
          
          <div style="font-weight: bold; text-decoration: underline; margin-top: 4px; margin-bottom: 2px;">Distinctions</div>
          <?php
            $hasNotes = !empty($totalCoefficients) && $totalCoefficients > 0;
            $moy = (float)($moyenneGenerale ?? 0);
            $felicitations = $hasNotes && ($moy >= 16);
            $tableauHonneur = $hasNotes && ($moy >= 14 && $moy < 16);
            $encouragements = $hasNotes && ($moy >= 12 && $moy < 14);
          ?>
          <div class="checkbox-item">Félicitations du Jury <div class="checkbox-sq"><?= $felicitations ? 'X' : '' ?></div></div>
          <div class="checkbox-item">Tableau d'Honneur <div class="checkbox-sq"><?= $tableauHonneur ? 'X' : '' ?></div></div>
          <div class="checkbox-item">Encouragements <div class="checkbox-sq"><?= $encouragements ? 'X' : '' ?></div></div>

          <div style="font-weight: bold; text-decoration: underline; margin-top: 6px; margin-bottom: 2px;">Décision</div>
          <div style="font-size: 11px; font-weight: bold; color: #1E3A5F; margin-top: 4px;">
            <?= htmlspecialchars($decision ?? 'En attente de délibération') ?>
          </div>
        </div>
      </td>

      <!-- COLONNE DROITE : RÉCAPITULATIF ANNUEL & SIGNATURE -->
      <td style="width: 62%;">
        <div class="recap-box">
          <div class="recap-header">STATISTIQUES DE LA CLASSE</div>
          
          <table class="recap-grid">
            <tr>
              <td style="width: 50%;">Moyenne de l'Élève : <strong style="font-size: 11px; color: #1E3A5F;"><?= isset($moyenneGenerale) ? number_format((float)$moyenneGenerale, 2, ',', ' ') : '-' ?> /20</strong></td>
              <td style="width: 50%;">Rang : <strong style="font-size: 11px;"><?= htmlspecialchars($rang ?? '-') ?></strong> / <?= htmlspecialchars($totalElevesClasse ?? '-') ?></td>
            </tr>
            <tr>
              <td>Moyenne Plus Basse : <strong><?= isset($moyenneMinClasse) ? number_format((float)$moyenneMinClasse, 2, ',', ' ') : '-' ?></strong></td>
              <td>Moyenne Plus Haute : <strong><?= isset($moyenneMaxClasse) ? number_format((float)$moyenneMaxClasse, 2, ',', ' ') : '-' ?></strong></td>
            </tr>
            <tr>
              <td>Moyenne Générale Classe : <strong><?= isset($moyenneMoyClasse) ? number_format((float)$moyenneMoyClasse, 2, ',', ' ') : '-' ?></strong></td>
              <td>Mention : <strong><?= htmlspecialchars($mention ?? '-') ?></strong></td>
            </tr>
          </table>

          <table style="width: 100%; margin-top: 6px;">
            <tr>
              <td style="width: 50%; vertical-align: top;">
                <div style="border: 1px solid #CBD5E1; padding: 4px; height: 44px; font-size: 9px; border-radius: 3px;">
                  <strong style="text-decoration: underline;">Appréciation du Conseil :</strong><br>
                  <?= htmlspecialchars($appreciation_conseil ?? '') ?>
                </div>
              </td>
              <td style="width: 50%; text-align: center; vertical-align: top;">
                <div style="font-size: 9px; margin-bottom: 2px;">Fait le <?= date('d/m/Y') ?></div>
                <div style="font-weight: bold; text-decoration: underline; font-size: 9.5px; color: #1E3A5F;">LA DIRECTION DES ÉTUDES</div>
              </td>
            </tr>
          </table>

        </div>
      </td>
    </tr>
  </table>

  <!-- FOOTER CONTACTS -->
  <div class="footer-contacts">
    GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION (GEICG) &bull; Direction des Études &bull; Document Officiel
  </div>

</body>
</html>
