<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$nomComplet = trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? ''));
$codeUser = $user['code_user'] ?? '-';
$emailUser = $user['email_user'] ?? '-';
$telUser = $user['telephone_user'] ?? '-';
$statutUser = strtoupper($user['statut_user'] ?? 'ACTIF');
$dateCreation = !empty($user['created_at_user']) ? date('d/m/Y H:i', strtotime($user['created_at_user'])) : '-';
$lastConnexion = !empty($user['last_connexion']) ? date('d/m/Y H:i', strtotime($user['last_connexion'])) : 'Jamais connecté';
$etabCode = $user['etablissement_code'] ?? ($etablissement['code_etablissement'] ?? '-');

$sexeUser = 'Non précisé';
if (!empty($user['sexe_user'])) {
    $s = strtoupper(trim($user['sexe_user']));
    $sexeUser = ($s === 'M' || $s === 'HOMME') ? 'Masculin' : (($s === 'F' || $s === 'FEMME') ? 'Féminin' : $s);
}

// Logo
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

// Photo Utilisateur
$photoPath = '';
if (!empty($user['photo_user'])) {
    $cPaths = [
        __DIR__ . '/../../../public/' . ltrim($user['photo_user'], '/'),
        __DIR__ . '/../../../' . ltrim($user['photo_user'], '/'),
        __DIR__ . '/../../public/' . ltrim($user['photo_user'], '/')
    ];
    foreach ($cPaths as $cp) {
        if (file_exists($cp) && is_file($cp)) {
            $photoPath = $cp;
            break;
        }
    }
}
if (empty($photoPath) || !file_exists($photoPath)) {
    $placeholders = [
        __DIR__ . '/../../../public/assets/images/placeholders/user.png',
        __DIR__ . '/../../public/assets/images/placeholders/user.png'
    ];
    foreach ($placeholders as $pl) {
        if (file_exists($pl)) {
            $photoPath = $pl;
            break;
        }
    }
}

$nomDirecteur = !empty($etablissement['nom_directeur_general']) ? $etablissement['nom_directeur_general'] : (!empty($etablissement['nom_directeur_etudes']) ? $etablissement['nom_directeur_etudes'] : 'La Direction');
$villeSignature = !empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : 'Bouaké';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche Utilisateur - <?= htmlspecialchars($nomComplet) ?></title>
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
      color: #b90c0cff;
      text-transform: uppercase;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 10px;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 2px;
    }
    .inst-contacts {
      font-size: 8.5px;
      font-style: italic;
      text-align: center;
      margin-top: 2px;
      color: #475569;
    }

    /* Bannières */
    .banner-title {
      background: #1E3A5F;
      color: #FFFFFF;
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      padding: 5px;
      margin-top: 6px;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

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

    /* Grille rôles */
    .roles-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
    }
    .roles-table th {
      background: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      padding: 5px 6px;
      font-size: 9px;
      text-align: left;
      border: 1px solid #1E3A5F;
    }
    .roles-table td {
      padding: 4px 6px;
      font-size: 9px;
      border: 1px solid #E2E8F0;
      vertical-align: middle;
    }

    /* Matrice permissions */
    .perm-grid {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
    }
    .perm-box {
      border: 1px solid #CBD5E1;
      background: #F8FAFC;
      padding: 6px 8px;
      text-align: center;
      border-radius: 4px;
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

  <!-- EN-TÊTE INSTITUTIONNEL -->
  <table class="header-table">
    <tr>
      <td style="width: 18%; vertical-align: middle; text-align: left;">
        <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 48px; max-width: 120px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#1E3A5F; font-size:14px;">GEICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 82%; text-align: center; vertical-align: middle;">
        <div class="inst-title"><?= htmlspecialchars($etablissement['libelle_etablissement'] ?? ($etablissement['nom_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION')) ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars($etablissement['numero_autorisation'] ?? 'AGREE PAR L\'ETAT ET LE FDFP') ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars(!empty($etablissement['adresse_etablissement']) ? $etablissement['adresse_etablissement'] : 'Bouaké quartier Kennedy, route ancien ADDR') ?> 
          &bull; <?= htmlspecialchars($etablissement['boite_postale'] ?? '01 BP 960 Bouaké 01') ?>
        <br> Contacts : <?= htmlspecialchars(!empty($etablissement['contacts_formatted']) ? $etablissement['contacts_formatted'] : '27 31 62 40 57 / 07 79 37 37 38') ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">
    FICHE D'IDENTIFICATION & DE GESTION DU COMPTE UTILISATEUR
  </div>

  <!-- SECTION 1 : ÉTAT CIVIL & IDENTITÉ -->
  <div class="section-title">1. ÉTAT CIVIL & IDENTITÉ DU TITULAIRE</div>
  <table class="data-table">
    <tr>
      <td style="width: 78%; vertical-align: top;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 35%;">Code Compte : <span class="val-bold" style="font-family: monospace; color:#1E3A5F;"><?= htmlspecialchars($codeUser) ?></span></td>
            <td style="width: 35%;">Genre / Sexe : <span class="val-bold"><?= htmlspecialchars($sexeUser) ?></span></td>
            <td style="width: 30%;">Statut : <span class="val-bold" style="color: <?= ($statutUser === 'ACTIF') ? '#15803D' : '#B91C1C' ?>;"><?= htmlspecialchars($statutUser) ?></span></td>
          </tr>
          <tr>
            <td colspan="3" style="padding-top: 4px;">
              Nom & Prénom(s) : <span class="val-bold" style="font-size: 11.5px; color:#1E3A5F;"><?= htmlspecialchars(mb_strtoupper($nomComplet)) ?></span>
            </td>
          </tr>
          <tr>
            <td colspan="3" style="padding-top: 4px;">
              Rôle Principal : <span class="val-bold"><?= htmlspecialchars($role['libelle_role'] ?? 'Standard') ?></span>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: center; vertical-align: middle;">
        <?php if (!empty($photoPath) && file_exists($photoPath)): ?>
          <img src="<?= $photoPath ?>" class="photo-box" alt="Photo">
        <?php else: ?>
          <div style="width: 90px; height: 90px; border: 1.5px solid #1E3A5F; line-height: 90px; font-weight: bold; color: #64748B; background: #F1F5F9; border-radius: 8px; margin: 0 auto;">
            PHOTO
          </div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- SECTION 2 : COORDONNÉES & ACCÈS -->
  <div class="section-title">2. COORDONNÉES</div>
  <table class="data-table" style="border: 1px solid #E2E8F0; background: #FAFAFA; border-radius: 6px;">
    <tr>
      <td style="width: 50%;">Email de connexion : <span class="val-bold"><?= htmlspecialchars($emailUser) ?></span></td>
      <td style="width: 50%;">Numéro de Téléphone : <span class="val-bold"><?= htmlspecialchars($telUser) ?></span></td>
    </tr>
    <tr>
      <td>Date d'ouverture du compte : <span class="val-bold"><?= htmlspecialchars($dateCreation) ?></span></td>
      <td>Dernière session active : <span class="val-bold"><?= htmlspecialchars($lastConnexion) ?></span></td>
    </tr>
  </table>

  <!-- SECTION 3 : RÔLES ATTRIBUÉS & PRIVILÈGES RBAC -->
  <div class="section-title">3. RÔLES SYSTÈME ATTRIBUÉS & PÉRIMÈTRE D'ACTION</div>
  <?php if (!empty($userRoles)): ?>
    <table class="roles-table">
      <thead>
        <tr>
          <th style="width: 30%;">Intitulé du Rôle</th>
          <th style="width: 20%;">Module / Groupe</th>
          <th style="width: 32%;">Description</th>
          <th style="width: 18%; text-align: center;">Actions Système</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($userRoles as $ur): ?>
          <tr>
            <td><strong><?= htmlspecialchars($ur['libelle_role'] ?? 'Rôle') ?></strong></td>
            <td><?= htmlspecialchars($ur['groupe'] ?? ($ur['module'] ?? 'Standard')) ?></td>
            <td style="font-size: 8.5px; color: #475569;"><?= htmlspecialchars($ur['description'] ?? '-') ?></td>
            <td style="text-align: center; font-size: 8.5px;">
              <?= !empty($ur['create_permission']) ? '[C]' : '[-]' ?>
              <?= !empty($ur['edit_permission']) ? '[M]' : '[-]' ?>
              <?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? '[L]' : '[-]' ?>
              <?= !empty($ur['delete_permission']) ? '[S]' : '[-]' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div style="padding: 6px; font-style: italic; color: #64748B;">Aucun rôle spécifique attribué.</div>
  <?php endif; ?>

  <!-- SECTION 4 : MATRICE DES ACTIONS AUTORISÉES -->
  <div class="section-title">4. MATRICE SYNTHÉTIQUE DES PRIVILÈGES</div>
  <table class="perm-grid">
    <tr>
      <td style="width: 25%; padding: 4px;">
        <div class="perm-box">
          <div style="font-weight: bold; color: #0F172A; font-size: 9px;">CRÉATION (AJOUT)</div>
          <div style="font-weight: bold; margin-top: 2px; color: <?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? '#15803D' : '#DC2626' ?>;">
            <?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'AUTORISÉ' : 'REFUSÉ' ?>
          </div>
        </div>
      </td>
      <td style="width: 25%; padding: 4px;">
        <div class="perm-box">
          <div style="font-weight: bold; color: #0F172A; font-size: 9px;">MODIFICATION (ÉDITION)</div>
          <div style="font-weight: bold; margin-top: 2px; color: <?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? '#0284C7' : '#DC2626' ?>;">
            <?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'AUTORISÉ' : 'REFUSÉ' ?>
          </div>
        </div>
      </td>
      <td style="width: 25%; padding: 4px;">
        <div class="perm-box">
          <div style="font-weight: bold; color: #0F172A; font-size: 9px;">CONSULTATION (LECTURE)</div>
          <div style="font-weight: bold; margin-top: 2px; color: <?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? '#15803D' : '#DC2626' ?>;">
            <?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'AUTORISÉ' : 'REFUSÉ' ?>
          </div>
        </div>
      </td>
      <td style="width: 25%; padding: 4px;">
        <div class="perm-box">
          <div style="font-weight: bold; color: #0F172A; font-size: 9px;">SUPPRESSION</div>
          <div style="font-weight: bold; margin-top: 2px; color: <?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? '#15803D' : '#DC2626' ?>;">
            <?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'AUTORISÉ' : 'REFUSÉ' ?>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <!-- SECTION 5 : SIGNATURES & VALIDATION -->
  <table class="signature-grid">
    <tr>
      <td style="text-align: left;">
        <div><strong>Le Titulaire du Compte :</strong></div>
        <div style="margin-top: 3px; font-style: italic; color: #64748B;">(Lu et approuvé, signature)</div>
        <div style="height: 48px;"></div>
        <div><strong><?= htmlspecialchars($nomComplet) ?></strong></div>
      </td>
      <td style="text-align: right;">
        <div>Fait à <?= htmlspecialchars($villeSignature) ?>, le <strong><?= date('d/m/Y') ?></strong></div>
        <div style="margin-top: 3px;"><strong>Pour la Direction / Administration SI :</strong></div>
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

