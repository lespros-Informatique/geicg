<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Convocation Examen de Passage / Fin d'Année</title>
  <style>
    @page {
      margin: 8mm 10mm 8mm 10mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #000000;
      line-height: 1.25;
    }

    /* Header Institutionnel */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
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
      margin-top: 2px;
    }

    /* Titre Box Convocation */
    .convocation-box {
      width: 60%;
      margin: 4px auto 6px auto;
      border: 1.5px solid #000000;
      text-align: center;
      padding: 4px 10px;
      font-size: 18px;
      font-weight: bold;
      letter-spacing: 1px;
    }
    .session-title {
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 10px;
      text-transform: uppercase;
    }

    /* Profil Etudiant & Photo */
    .student-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .student-table td {
      vertical-align: top;
      padding: 2.5px 4px;
      font-size: 11px;
    }
    .val-bold {
      font-weight: bold;
    }
    .photo-box {
      width: 90px;
      height: 108px;
      border: 1px solid #000000;
      object-fit: cover;
    }

    /* Instruction text */
    .notice-text {
      font-size: 11px;
      margin-top: 4px;
      margin-bottom: 8px;
    }

    /* Sections Epreuves */
    .section-banner {
      font-size: 12px;
      font-weight: bold;
      text-decoration: underline;
      margin-top: 8px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    /* Tableaux d'épreuves */
    .epreuves-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .epreuves-table th {
      border: 1px solid #000000;
      padding: 4px 6px;
      font-size: 10.5px;
      font-weight: bold;
      text-align: center;
      background: #D9D9D9;
    }
    .epreuves-table td {
      border: 1px solid #000000;
      padding: 4px 6px;
      font-size: 10.5px;
      vertical-align: middle;
    }

    /* Footer & Mention */
    .turn-page {
      text-align: center;
      font-size: 11px;
      font-style: italic;
      font-weight: bold;
      margin-top: 10px;
      margin-bottom: 15px;
    }
    .footer-line {
      border-top: 1px solid #000000;
      padding-top: 4px;
      width: 100%;
    }
    .footer-contacts {
      text-align: center;
      font-size: 9.5px;
      line-height: 1.3;
      margin-top: 4px;
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
      <td style="width: 22%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 50px; max-width: 140px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#990000; font-size:14px;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 78%; text-align: center; vertical-align: middle;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE FDFP</div>
      </td>
    </tr>
  </table>

  <!-- TITRE DE LA CONVOCATION -->
  <div class="convocation-box">CONVOCATION</div>
  <div class="session-title"><?= htmlspecialchars($session_titre ?? 'EXAMEN DE FIN D\'ANNÉE BTS 1A - SESSION JUIN 2026') ?></div>

  <!-- INFOS ÉTUDIANT -->
  <table class="student-table">
    <tr>
      <td style="width: 76%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 22%;">Filière :</td>
            <td style="width: 78%;"><span class="val-bold"><?= htmlspecialchars($filiere_libelle ?? 'RESSOURCES HUMAINES ET COMMUNICATION') ?></span></td>
          </tr>
          <tr>
            <td>M .(Mme, Mlle) :</td>
            <td><span class="val-bold" style="font-size: 12px;"><?= htmlspecialchars($nom_prenom_etudiant ?? 'TRAORE FATOUMATA') ?></span></td>
          </tr>
          <tr>
            <td>Date et lieu naissance :</td>
            <td><span class="val-bold"><?= htmlspecialchars($date_naissance ?? '17/02/2004') ?></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="val-bold"><?= htmlspecialchars($lieu_naissance ?? 'BOUAKE') ?></span></td>
          </tr>
          <tr>
            <td>Identifiant Permanent :</td>
            <td><span class="val-bold"><?= htmlspecialchars($identifiant_permanent ?? 'TRAF1710040002') ?></span></td>
          </tr>
          <tr>
            <td colspan="2" style="height: 6px;"></td>
          </tr>
          <tr>
            <td><strong style="font-size: 11.5px;">NUMGPEICG :</strong></td>
            <td><span class="val-bold" style="font-size: 12px;"><?= htmlspecialchars($num_gpeicg ?? 'TF-797/GEB/RHC25') ?></span></td>
          </tr>
          <tr>
            <td>Salle de composition :</td>
            <td><span class="val-bold" style="font-size: 13px;"><?= htmlspecialchars($salle_composition ?? 'S6') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 24%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 108px; color: #94A3B8; font-size: 9px;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- CONSIGNES & RAPPEL -->
  <div class="notice-text">
    est informé(e) que les épreuves se dérouleront suivant le calendrier ci-après indiqué :
  </div>

  <!-- ÉPREUVES PRATIQUES -->
  <div class="section-banner">EPREUVES PRATIQUES</div>
  <table class="epreuves-table">
    <thead>
      <tr>
        <th style="width: 55%; text-align: left;">EPREUVES</th>
        <th style="width: 25%;">DATE</th>
        <th style="width: 20%;">HEURE</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($epreuves_pratiques) && is_array($epreuves_pratiques)): ?>
        <?php foreach ($epreuves_pratiques as $ep): ?>
          <tr>
            <td><?= htmlspecialchars($ep['matiere'] ?? '') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($ep['date'] ?? '') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($ep['horaire'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td style="height: 18px;"></td>
          <td></td>
          <td></td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- ÉPREUVES ÉCRITES -->
  <div class="section-banner">EPREUVES ECRITES</div>
  <table class="epreuves-table">
    <thead>
      <tr>
        <th style="width: 52%; text-align: left;">MATIERES</th>
        <th style="width: 10%;">COEFF</th>
        <th style="width: 20%;">DATES</th>
        <th style="width: 18%;">HORAIRES</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $defaultEcrites = [
          ['matiere' => 'TECHNIQUES D\'EXPRESSION ECRITE ET ORALE', 'coeff' => 3, 'date' => 'lundi 22 juin 2026', 'horaire' => '08H00 – 10H00'],
          ['matiere' => 'ANGLAIS', 'coeff' => 3, 'date' => 'lundi 22 juin 2026', 'horaire' => '10H30 – 12H30'],
          ['matiere' => 'ECONOMIE ET ORGANISATION D\'ENTREPRISE', 'coeff' => 3, 'date' => 'mardi 23 juin 2026', 'horaire' => '08H00 – 10H00'],
          ['matiere' => 'DROIT - LEGISLATION DU TRAVAIL ET DE LA COMMUNICATION', 'coeff' => 5, 'date' => 'mardi 23 juin 2026', 'horaire' => '10H30 – 12H30'],
          ['matiere' => 'COMPTABILITE - INFORMATIQUE', 'coeff' => 4, 'date' => 'mercredi 24 juin 2026', 'horaire' => '08H00 – 10H00'],
          ['matiere' => 'COMMUNICATION D\'ENTREPRISE - TECHNIQUE DE COMMUNICATION ET D\'ANIMATION', 'coeff' => 5, 'date' => 'mercredi 24 juin 2026', 'horaire' => '10H30 – 12H30'],
          ['matiere' => 'ENQUETE DE SATISFACTION - STATISTIQUES APPLIQUEES', 'coeff' => 5, 'date' => 'jeudi 25 juin 2026', 'horaire' => '08H00 – 10H00'],
          ['matiere' => 'MARKETING ET POLITIQUE DE COMMUNICATION', 'coeff' => 4, 'date' => 'jeudi 25 juin 2026', 'horaire' => '10H30 – 12H30'],
          ['matiere' => 'PSYCHOSOCIOLOGIE APPLIQUEE - PROCESSUS DE PRODUCTION DANS LES MEDIAS', 'coeff' => 5, 'date' => 'vendredi 26 juin 2026', 'horaire' => '08H00 – 10H00'],
          ['matiere' => 'PSYCHOSOCIOLOGIE DES ORGANISATIONS - NEGOCIATION DES RELATIONS SOCIALES', 'coeff' => 4, 'date' => 'vendredi 26 juin 2026', 'horaire' => '10H30 – 12H30'],
        ];
        $listEcrites = !empty($epreuves_ecrites) && is_array($epreuves_ecrites) ? $epreuves_ecrites : $defaultEcrites;
      ?>
      <?php foreach ($listEcrites as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['matiere'] ?? '') ?></td>
          <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($item['coeff'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($item['date'] ?? '') ?></td>
          <td style="text-align: center;"><?= htmlspecialchars($item['horaire'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- MENTION TOURNER LA PAGE -->
  <div class="turn-page">Tournez la page SVP !</div>

  <!-- BAS DE PAGE -->
  <div class="footer-line">
    <table style="width: 100%; font-size: 9px;">
      <tr>
        <td style="width: 50%;">Imprimée &nbsp;&nbsp;&nbsp;&nbsp; <?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></td>
        <td style="width: 50%; text-align: right;">Page 1 sur 1</td>
      </tr>
    </table>
    
    <div class="footer-contacts">
      Contacts : Bouaké quartier Kennedy, route ancien ADDR près de l’ONG SAVE THE CHILDREN<br>
      01 BP 960 Bouaké 01 – Email : eicgbouake01@gmail.com<br>
      Tél : 27 31 62 40 57 – Cél : 07 79 37 37 38 / 05 04 59 39 99
    </div>
  </div>

</body>
</html>
