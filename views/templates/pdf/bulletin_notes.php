<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bulletin de Notes & Récapitulatif Annuel</title>
  <style>
    @page {
      margin: 5mm 8mm 5mm 8mm;
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
      margin-bottom: 2px;
    }
    .inst-title {
      font-size: 12.5px;
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
      margin-bottom: 4px;
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
      background: #000000;
      color: #FFFFFF;
      font-size: 15px;
      font-weight: bold;
      text-align: center;
      padding: 3px;
      margin-top: 4px;
      margin-bottom: 2px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .banner-subtitle {
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    /* Tableau des Notes */
    .notes-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .notes-table th {
      background: #000000;
      color: #FFFFFF;
      border: 1px solid #000000;
      padding: 3.5px 4px;
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
    }
    .notes-table td {
      border: 1px solid #000000;
      padding: 3px 4px;
      font-size: 9.5px;
    }
    .subtotal-row td {
      background: #B9D5E9;
      font-weight: bold;
    }
    .bilan-row td {
      background: #8DB4E2;
      font-weight: bold;
      font-size: 10px;
    }

    /* Blocs Inférieurs (Absences, Mentions & Récap) */
    .bottom-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .bottom-table td {
      vertical-align: top;
    }

    .mentions-box {
      border: 1px solid #000000;
      padding: 4px 6px;
      height: 185px;
    }
    .box-title {
      font-weight: bold;
      text-align: center;
      text-decoration: underline;
      font-size: 10px;
      margin-bottom: 4px;
    }
    .checkbox-item {
      margin-bottom: 3px;
      font-size: 9px;
    }
    .checkbox-sq {
      display: inline-block;
      width: 11px;
      height: 11px;
      border: 1px solid #000000;
      float: right;
      margin-top: 1px;
    }

    .recap-box {
      border: 1px solid #000000;
      padding: 4px 6px;
      height: 185px;
    }
    .recap-header {
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      text-decoration: underline;
      margin-bottom: 6px;
    }
    .recap-grid {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .recap-grid td {
      padding: 2.5px 4px;
      font-size: 10px;
    }

    /* Footer Contacts */
    .footer-contacts {
      text-align: center;
      font-size: 8.5px;
      border-top: 1px solid #000000;
      padding-top: 3px;
      margin-top: 6px;
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
        <div class="annee-header">Année Académique : <?= htmlspecialchars($annee_libelle ?? '2025 - 2026') ?></div>
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
        <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px;"><?= htmlspecialchars($nom_prenom_etudiant ?? 'YAYO DJEDJESS TRIJI JEAN JAURES') ?></div>
        <table style="width: 100%;">
          <tr>
            <td style="width: 32%;">Matricule MESRS :</td>
            <td style="width: 68%;"><span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? 'YAYD0207010001') ?></span></td>
          </tr>
          <tr>
            <td>Né( e) le :</td>
            <td><span class="val-bold"><?= htmlspecialchars($date_naissance ?? '02/07/2001') ?></span></td>
          </tr>
          <tr>
            <td>Lieu :</td>
            <td><span class="val-bold"><?= htmlspecialchars($lieu_naissance ?? 'VIEIL-OUROU') ?></span></td>
          </tr>
          <tr>
            <td>Sexe :</td>
            <td><span class="val-bold"><?= htmlspecialchars($sexe_etudiant ?? 'M') ?></span></td>
          </tr>
          <tr>
            <td>Filière :</td>
            <td><span class="val-bold" style="font-size: 11px;"><?= htmlspecialchars($filiere_libelle ?? 'Ressources Humaines et Communication') ?></span></td>
          </tr>
          <tr>
            <td>Niveau :</td>
            <td><span class="val-bold"><?= htmlspecialchars($niveau_libelle ?? 'Première Année') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 28%; text-align: right; vertical-align: top;">
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 6px;"><?= htmlspecialchars($num_gpeicg ?? 'YD-934/GEB/RHC25') ?></div>
        <table style="width: 100%; text-align: right;">
          <tr>
            <td>Effectif : <span class="val-bold"><?= htmlspecialchars($effectif_classe ?? '73') ?></span></td>
          </tr>
          <tr>
            <td>Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
          </tr>
          <tr>
            <td>Redoublant (e ) : <span class="val-bold"><?= htmlspecialchars($est_redoublant ?? 'NON') ?></span></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- TITRE BULLETIN -->
  <div class="banner-title">BULLETIN DE NOTES</div>
  <div class="banner-subtitle"><?= htmlspecialchars($intitule_periode ?? 'SEMESTRE 2') ?></div>

  <!-- TABLEAU DES NOTES -->
  <table class="notes-table">
    <thead>
      <tr>
        <th style="width: 44%; text-align: left;">MATIERES</th>
        <th style="width: 7%;">Moy</th>
        <th style="width: 6%;">Coef</th>
        <th style="width: 8%;">MOY Coef</th>
        <th style="width: 9%;">Rang</th>
        <th style="width: 16%;">Enseignants</th>
        <th style="width: 10%;">Appréciation</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $defaultGenerales = [
          ['matiere' => 'Techniques d\'Expression Ecrite et Orale', 'moy' => '13,00', 'coef' => 3, 'total' => '39,00', 'rang' => '54ème', 'prof' => 'M. FOFANA Mohamed', 'appr' => 'Assez Bien'],
          ['matiere' => 'Anglais Professionnel', 'moy' => '10,00', 'coef' => 3, 'total' => '30,00', 'rang' => '37ème', 'prof' => 'Mr DENOU Sékou', 'appr' => 'Passable'],
          ['matiere' => 'Economie Générale et Economie des Org. et des Entrep.', 'moy' => '16,25', 'coef' => 3, 'total' => '48,75', 'rang' => '5ème', 'prof' => 'M. AIKI Lassissi', 'appr' => 'Très Bien'],
          ['matiere' => 'Droit des Affaires et Droit du Travail', 'moy' => '10,00', 'coef' => 2, 'total' => '20,00', 'rang' => '53ème', 'prof' => 'M. CAMARA Mamadou', 'appr' => 'Passable'],
          ['matiere' => 'Comptabilité Générale', 'moy' => '9,00', 'coef' => 2, 'total' => '18,00', 'rang' => '48ème', 'prof' => 'M. N\'GORAN K. Marius', 'appr' => 'Insuffisant'],
          ['matiere' => 'Statistiques Appliquées', 'moy' => '14,50', 'coef' => 2, 'total' => '29,00', 'rang' => '58ème', 'prof' => 'M. KOUAKOU Jacques', 'appr' => 'Bien'],
        ];
        $defaultProfessionnelles = [
          ['matiere' => 'Législation du Travail et de la Communication', 'moy' => '11,00', 'coef' => 3, 'total' => '33,00', 'rang' => '60ème', 'prof' => 'M. KONE Dowonan G.', 'appr' => 'Passable'],
          ['matiere' => 'Psychosociologie Appliquée', 'moy' => '10,00', 'coef' => 3, 'total' => '30,00', 'rang' => '63ème', 'prof' => 'M. KROKRO K. Cédric Paphis', 'appr' => 'Passable'],
          ['matiere' => 'Techniques de Communication et d\'Animation', 'moy' => '9,50', 'coef' => 3, 'total' => '28,50', 'rang' => '58ème', 'prof' => 'Dr COULIBALY Sonan Hamed', 'appr' => 'Insuffisant'],
          ['matiere' => 'Enquête de Satisfaction', 'moy' => '13,00', 'coef' => 3, 'total' => '39,00', 'rang' => '39ème', 'prof' => 'M. KOUMOIN K. Romaric Danie', 'appr' => 'Assez Bien'],
          ['matiere' => 'Négociation des Ressources Sociales', 'moy' => '8,00', 'coef' => 2, 'total' => '16,00', 'rang' => '60ème', 'prof' => 'M. OUATTARA Isabelle', 'appr' => 'Insuffisant'],
          ['matiere' => 'Marketing et Politique de Communication', 'moy' => '10,25', 'coef' => 4, 'total' => '41,00', 'rang' => '44ème', 'prof' => 'M. KIN Blé Yannick', 'appr' => 'Passable'],
          ['matiere' => 'Communication d\'Entreprise', 'moy' => '12,00', 'coef' => 2, 'total' => '24,00', 'rang' => '52ème', 'prof' => 'Mme ADIA A. Marie Michelle P.', 'appr' => 'Assez Bien'],
          ['matiere' => 'Processus de Production dans les Médias', 'moy' => '4,00', 'coef' => 2, 'total' => '8,00', 'rang' => '65ème', 'prof' => 'Mme Coulibaly Y. Y. Mariam', 'appr' => ''],
          ['matiere' => 'Psychosociologie des Organisations', 'moy' => '6,00', 'coef' => 3, 'total' => '18,00', 'rang' => '65ème', 'prof' => 'Mme OUATTARA T. Isabelle', 'appr' => ''],
          ['matiere' => 'Informatique Appliquée', 'moy' => '11,00', 'coef' => 2, 'total' => '22,00', 'rang' => '13ème', 'prof' => 'M. DJEI Christian Arnold', 'appr' => 'Passable'],
        ];

        $matieresGen = !empty($matieres_generales) && is_array($matieres_generales) ? $matieres_generales : $defaultGenerales;
        $matieresProf = !empty($matieres_professionnelles) && is_array($matieres_professionnelles) ? $matieres_professionnelles : $defaultProfessionnelles;
      ?>

      <!-- MATIERES GENERALES -->
      <?php foreach ($matieresGen as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['matiere'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['moy'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['coef'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['total'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['rang'] ?? '') ?></td>
          <td><?= htmlspecialchars($m['prof'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['appr'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      <tr class="subtotal-row">
        <td>TOTAL MATIERES GENERALES :</td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_generales['moy'] ?? '11,53') ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_generales['coef'] ?? '15') ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_generales['total'] ?? '172,97') ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_generales['rang'] ?? '50ème') ?></td>
        <td></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_generales['appr'] ?? 'Passable') ?></td>
      </tr>

      <!-- MATIERES PROFESSIONNELLES -->
      <?php foreach ($matieresProf as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['matiere'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['moy'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['coef'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['total'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['rang'] ?? '') ?></td>
          <td><?= htmlspecialchars($m['prof'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($m['appr'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      <tr class="subtotal-row">
        <td>TOTAL MATIERES PROFESSIONNELLES :</td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_pro['moy'] ?? '9,61') ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_pro['coef'] ?? '27') ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_pro['total'] ?? '259,57') ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_pro['rang'] ?? '62ème') ?></td>
        <td></td>
        <td style="text-align: center;"><?= htmlspecialchars($totaux_pro['appr'] ?? 'Insuffisant') ?></td>
      </tr>

      <!-- BILAN GÉNÉRAL -->
      <tr class="bilan-row">
        <td style="text-align: center; font-size: 11px;">Bilan</td>
        <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($bilan_general['moy'] ?? '10,70') ?></td>
        <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($bilan_general['coef'] ?? '42') ?></td>
        <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($bilan_general['total'] ?? '449,51') ?></td>
        <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($bilan_general['rang'] ?? '60ème') ?></td>
        <td></td>
        <td style="text-align: center; font-size: 11px;"><?= htmlspecialchars($bilan_general['appr'] ?? 'Passable') ?></td>
      </tr>
    </tbody>
  </table>

  <!-- BAS DU BULLETIN : ABSENCES, MENTIONS & RÉCAPITULATIF ANNUEL -->
  <table class="bottom-table">
    <tr>
      <!-- COLONNE GAUCHE : MENTIONS DU CONSEIL -->
      <td style="width: 38%; padding-right: 6px;">
        <div class="mentions-box">
          <div style="font-size: 11px; margin-bottom: 6px;">
            Heures d'Absences : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong><?= htmlspecialchars($heures_absences ?? '6 H') ?></strong>
          </div>
          
          <div class="box-title">MENTIONS DU CONSEIL DE CLASSE</div>
          
          <div style="font-weight: bold; text-decoration: underline; margin-top: 4px; margin-bottom: 2px;">Distinctions</div>
          <div class="checkbox-item">Tableau d'honneur + Félicitations <div class="checkbox-sq"></div></div>
          <div class="checkbox-item">Tableau d'honneur + Encouragements <div class="checkbox-sq"></div></div>
          <div class="checkbox-item">Tableau d'honneur <div class="checkbox-sq"></div></div>

          <div style="font-weight: bold; text-decoration: underline; margin-top: 6px; margin-bottom: 2px;">Sanctions</div>
          <div class="checkbox-item">Avertissement Travail <div class="checkbox-sq"></div></div>
          <div class="checkbox-item">Blâme Travail <div class="checkbox-sq"></div></div>
        </div>
      </td>

      <!-- COLONNE DROITE : RÉCAPITULATIF ANNUEL & SIGNATURE -->
      <td style="width: 62%;">
        <div class="recap-box">
          <div class="recap-header">RECAPITULATIF ANNUEL</div>
          
          <table class="recap-grid">
            <tr>
              <td style="width: 50%;">Moy. Semestre 1 : <strong style="font-size: 11px;"><?= htmlspecialchars($moy_semestre_1 ?? '10,86') ?></strong></td>
              <td style="width: 50%;">Moy. Semestre 2 : <strong style="font-size: 11px;"><?= htmlspecialchars($moy_semestre_2 ?? '10,70') ?></strong></td>
            </tr>
            <tr>
              <td>Moy. Examen : <strong style="font-size: 11px;"><?= htmlspecialchars($moy_examen ?? '7,08') ?></strong></td>
              <td><span style="font-size: 11px; font-weight: bold;">Moy. Générale : <?= htmlspecialchars($moy_generale ?? '10,04') ?></span></td>
            </tr>
          </table>

          <div style="text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 6px;">
            Rang : <?= htmlspecialchars($rang_annuel ?? '60ème') ?> /<?= htmlspecialchars($effectif_classe ?? '73') ?> Etudiants
          </div>

          <table style="width: 100%; font-size: 8.5px; margin-bottom: 6px;">
            <tr>
              <td>Moy. Min : <strong><?= htmlspecialchars($moy_min ?? '0,16') ?></strong></td>
              <td>Moy. Max : <strong><?= htmlspecialchars($moy_max ?? '13,83') ?></strong></td>
              <td>Moy. de la classe : <strong><?= htmlspecialchars($moy_classe ?? '10,31') ?></strong></td>
            </tr>
          </table>

          <table style="width: 100%; margin-top: 4px;">
            <tr>
              <td style="width: 45%; vertical-align: top;">
                <div style="border: 1px solid #000000; padding: 3px; height: 50px; font-size: 9.5px;">
                  <strong style="text-decoration: underline;">Appréciation du conseil</strong><br>
                  <?= htmlspecialchars($appreciation_conseil ?? '') ?>
                </div>
              </td>
              <td style="width: 55%; text-align: center; vertical-align: top;">
                <div style="font-size: 9.5px; margin-bottom: 4px;">Fait à Bouaké le ......../......../............</div>
                <div style="font-weight: bold; text-decoration: underline; font-size: 10px;">LE DIRECTEUR DES ETUDES</div>
                <div style="font-size: 10px; font-weight: bold; margin-top: 25px;"><?= htmlspecialchars($nom_directeur_etudes ?? 'Dr COULIBALY Sonan Hamed') ?></div>
              </td>
            </tr>
          </table>

        </div>
      </td>
    </tr>
  </table>

  <!-- FOOTER CONTACTS -->
  <div class="footer-contacts">
    Contacts : Bouaké quartier Kennedy, route ancien ADDR<br>
    01 BP 960 Bouaké 01 – Email : eicgbouake01@gmail.com | Tél : 27 31 62 40 57 – Cél : 07 79 37 37 38 / 05 04 59 39 99
  </div>

</body>
</html>
