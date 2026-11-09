<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$item = isset($item) && is_array($item) ? $item : [];
$cours = isset($cours) && is_array($cours) ? $cours : [];

$nomComplet = trim(($item['nom_enseignant'] ?? ($item['nom_user'] ?? '')) . ' ' . ($item['prenom_enseignant'] ?? ($item['prenom_user'] ?? '')));
$codeEns = $item['code_enseignant'] ?? ($item['user_code'] ?? '-');
$emailEns = $item['email_enseignant'] ?? ($item['email_user'] ?? '-');
$telEns = $item['telephone_enseignant'] ?? ($item['telephone_user'] ?? '-');
$gradeEns = !empty($item['grade_enseignant']) ? $item['grade_enseignant'] : 'Enseignant';
$statutEns = strtoupper($item['statut_enseignant'] ?? ($item['statut_user'] ?? 'ACTIF'));
$typeContrat = !empty($item['type_contrat']) ? ucfirst($item['type_contrat']) : 'Non précisé';
$numAutorisation = !empty($item['numero_autorisation']) ? $item['numero_autorisation'] : 'Non renseigné';
$dateCreation = !empty($item['created_at_enseignant']) ? date('d/m/Y H:i', strtotime($item['created_at_enseignant'])) : '-';

$sexeEns = 'Non précisé';
$rawSexe = $item['sexe_enseignant'] ?? ($item['sexe_user'] ?? '');
if (!empty($rawSexe)) {
    $s = strtoupper(trim($rawSexe));
    $sexeEns = ($s === 'M' || $s === 'HOMME') ? 'Masculin' : (($s === 'F' || $s === 'FEMME') ? 'Féminin' : $s);
}

// Logo de l'établissement
$logoPath = '';
if (!empty($etablissement['logo_etablissement'])) {
    $cLogo = [
        __DIR__ . '/../../../public/' . ltrim($etablissement['logo_etablissement'], '/'),
        __DIR__ . '/../../../' . ltrim($etablissement['logo_etablissement'], '/'),
        __DIR__ . '/../../public/' . ltrim($etablissement['logo_etablissement'], '/')
    ];
    foreach ($cLogo as $clp) {
        if (file_exists($clp) && is_file($clp)) {
            $logoPath = $clp;
            break;
        }
    }
}
if (empty($logoPath) || !file_exists($logoPath)) {
    $candidateLogos = [
        __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg',
        __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg'
    ];
    foreach ($candidateLogos as $l) {
        if (file_exists($l)) {
            $logoPath = $l;
            break;
        }
    }
}

// Photo Enseignant
$photoPath = '';
$rawPhoto = !empty($item['photo_user']) ? $item['photo_user'] : (!empty($item['photo_enseignant']) ? $item['photo_enseignant'] : '');
if (!empty($rawPhoto)) {
    $cPaths = [
        __DIR__ . '/../../../public/' . ltrim($rawPhoto, '/'),
        __DIR__ . '/../../../' . ltrim($rawPhoto, '/'),
        __DIR__ . '/../../public/' . ltrim($rawPhoto, '/')
    ];
    foreach ($cPaths as $cp) {
        if (file_exists($cp) && is_file($cp)) {
            $photoPath = $cp;
            break;
        }
    }
}
if (empty($photoPath) || !file_exists($photoPath)) {
    $userPl = placeholderPath('user');
    if (file_exists($userPl)) {
        $photoPath = $userPl;
    }
}

// Calcul total volume horaire
$totalHeures = 0;
foreach ($cours as $c) {
    $totalHeures += (float)($c['volume_horaire'] ?? 0);
}

$nomDirecteur = !empty($etablissement['nom_directeur_etudes']) ? $etablissement['nom_directeur_etudes'] : (!empty($etablissement['nom_directeur_general']) ? $etablissement['nom_directeur_general'] : 'La Direction des Études');
$villeSignature = !empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : 'Bouaké';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche Enseignant - <?= htmlspecialchars($nomComplet) ?></title>
  <style>
    @page {
      margin: 8mm 9mm 8mm 9mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9.5px;
      color: #000000;
      line-height: 1.25;
    }

    .page-frame {
      border: 1.5px solid #1E3A5F;
      padding: 12px 14px;
      box-sizing: border-box;
    }

    /* En-tête */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .inst-title {
      font-size: 13px;
      font-weight: bold;
      color: #990000;
      text-transform: uppercase;
      text-align: center;
      line-height: 1.15;
    }
    .inst-subtitle {
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
      color: #0369A1;
      margin-top: 2px;
    }
    .inst-contacts {
      font-size: 8px;
      text-align: center;
      color: #475569;
      margin-top: 2px;
    }

    /* Bannière */
    .banner-title {
      background: #1E3A5F;
      color: #FFFFFF;
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      padding: 5px;
      margin-top: 4px;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Titres sections */
    .section-title {
      background: #F1F5F9;
      color: #1E3A5F;
      font-size: 10px;
      font-weight: bold;
      padding: 4px 6px;
      border-left: 4px solid #1E3A5F;
      margin-top: 8px;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    /* Tableaux Données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .data-table td {
      padding: 3px 5px;
      vertical-align: top;
      font-size: 9.5px;
    }
    .val-bold {
      font-weight: bold;
      color: #0F172A;
    }

    .photo-box {
      width: 90px;
      height: 90px;
      border: 1.5px solid #1E3A5F;
      border-radius: 8px;
      object-fit: cover;
    }

    /* Table des cours */
    .cours-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
    }
    .cours-table th {
      background: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      padding: 5px 6px;
      font-size: 9px;
      text-align: left;
      border: 1px solid #1E3A5F;
    }
    .cours-table td {
      padding: 4px 6px;
      font-size: 9px;
      border: 1px solid #E2E8F0;
      vertical-align: middle;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      margin-top: 18px;
      font-size: 9.5px;
    }
    .signature-grid td {
      vertical-align: top;
      width: 50%;
    }

    .footer-line {
      font-size: 8px;
      color: #64748B;
      text-align: center;
      margin-top: 14px;
      padding-top: 6px;
      border-top: 1px solid #E2E8F0;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- EN-TÊTE INSTITUTIONNEL UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">
    FICHE D'IDENTIFICATION & DE SERVICE DU CORPS ENSEIGNANT
  </div>

  <!-- SECTION 1 : ÉTAT CIVIL & IDENTIFICATION -->
  <div class="section-title">1. ÉTAT CIVIL & IDENTIFICATION DE L'ENSEIGNANT</div>
  <table class="data-table">
    <tr>
      <td style="width: 78%; vertical-align: top;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 35%;">Code Enseignant : <span class="val-bold" style="font-family: monospace; color:#1E3A5F;"><?= htmlspecialchars($codeEns) ?></span></td>
            <td style="width: 35%;">Genre / Sexe : <span class="val-bold"><?= htmlspecialchars($sexeEns) ?></span></td>
            <td style="width: 30%;">Statut : <span class="val-bold" style="color: <?= ($statutEns === 'ACTIF') ? '#15803D' : '#B91C1C' ?>;"><?= htmlspecialchars($statutEns) ?></span></td>
          </tr>
          <tr>
            <td colspan="3" style="padding-top: 4px;">
              Nom & Prénom(s) : <span class="val-bold" style="font-size: 11.5px; color:#1E3A5F;"><?= htmlspecialchars(mb_strtoupper($nomComplet)) ?></span>
            </td>
          </tr>
          <tr>
            <td colspan="3" style="padding-top: 4px;">
              Grade / Titre académique : <span class="val-bold"><?= htmlspecialchars($gradeEns) ?></span>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: center; vertical-align: middle;">
        <?php if (!empty($photoPath) && file_exists($photoPath)): ?>
          <img src="<?= $photoPath ?>" class="photo-box" alt="Photo Enseignant">
        <?php else: ?>
          <div style="width: 90px; height: 90px; border: 1.5px solid #1E3A5F; line-height: 90px; font-weight: bold; color: #64748B; background: #F1F5F9; border-radius: 8px; margin: 0 auto;">
            PHOTO
          </div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- SECTION 2 : COORDONNÉES & STATUT RH -->
  <div class="section-title">2. COORDONNÉES & STATUT CONTRACTUEL RH</div>
  <table class="data-table" style="border: 1px solid #E2E8F0; background: #FAFAFA; border-radius: 6px;">
    <tr>
      <td style="width: 50%;">Email de contact : <span class="val-bold"><?= htmlspecialchars($emailEns) ?></span></td>
      <td style="width: 50%;">Téléphone : <span class="val-bold"><?= htmlspecialchars($telEns) ?></span></td>
    </tr>
    <tr>
      <td>Type de contrat : <span class="val-bold"><?= htmlspecialchars($typeContrat) ?></span></td>
      <td>N° Autorisation d'enseigner : <span class="val-bold"><?= htmlspecialchars($numAutorisation) ?></span></td>
    </tr>
    <tr>
      <td colspan="2">Date d'enregistrement : <span class="val-bold"><?= htmlspecialchars($dateCreation) ?></span></td>
    </tr>
  </table>

  <!-- SECTION 3 : COURS & MATIÈRES ATTRIBUÉS -->
  <div class="section-title">3. MATIÈRES DISPENSÉES & CHARGES HORAIRES HEBDOMADAIRES (<?= count($cours) ?>)</div>
  <?php if (!empty($cours)): ?>
    <table class="cours-table">
      <thead>
        <tr>
          <th style="width: 8%; text-align: center;">N°</th>
          <th style="width: 47%;">Matière / Discipline</th>
          <th style="width: 25%;">Classe / Section</th>
          <th style="width: 20%; text-align: center;">Volume Hebdo</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($cours as $c): ?>
          <tr>
            <td style="text-align: center; color: #64748B;"><?= $i++ ?></td>
            <td><strong><?= htmlspecialchars($c['libelle_matiere'] ?? '-') ?></strong></td>
            <td><?= htmlspecialchars($c['libelle_classe'] ?? '-') ?></td>
            <td style="text-align: center; font-weight: bold; color: #1E3A5F;">
              <?= !empty($c['volume_horaire']) ? $c['volume_horaire'] . ' h' : '-' ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr style="background: #F8FAFC; font-weight: bold;">
          <td colspan="3" style="text-align: right; padding: 6px; color: #0F172A; text-transform: uppercase;">Total Volume Horaire Hebdomadaire :</td>
          <td style="text-align: center; padding: 6px; color: #0284C7; font-size: 10px;"><?= $totalHeures ?> h</td>
        </tr>
      </tbody>
    </table>
  <?php else: ?>
    <div style="padding: 10px; border: 1px dashed #CBD5E1; text-align: center; font-style: italic; color: #64748B; background: #F8FAFC; border-radius: 4px; margin-bottom: 6px;">
      Aucune attribution de cours n'a été enregistrée à ce jour pour cet enseignant.
    </div>
  <?php endif; ?>

  <!-- SECTION 4 : SIGNATURES & VALIDATION -->
  <table class="signature-grid">
    <tr>
      <td style="text-align: left;">
        <div><strong>L'Enseignant :</strong></div>
        <div style="margin-top: 3px; font-style: italic; color: #64748B;">(Lu et approuvé, signature)</div>
        <div style="height: 48px;"></div>
        <div><strong><?= htmlspecialchars($nomComplet) ?></strong></div>
      </td>
      <td style="text-align: right;">
        <div>Fait à <?= htmlspecialchars($villeSignature) ?>, le <strong><?= date('d/m/Y') ?></strong></div>
        <div style="margin-top: 3px;"><strong>Pour la Direction des Études / Pédagogie :</strong></div>
        <div style="height: 48px;"></div>
        <div><strong><?= htmlspecialchars($nomDirecteur) ?></strong></div>
      </td>
    </tr>
  </table>

  <!-- BAS DE PAGE -->
  <div class="footer-line">
    Document officiel généré par le Système Intégré de Gestion GEICG &bull; <?= date('d/m/Y H:i:s') ?> &bull; Page 1/1
  </div>

</div>

</body>
</html>

