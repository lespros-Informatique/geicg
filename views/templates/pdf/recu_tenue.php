<?php
/**
 * Template PDF mPDF : Reçu de Paiement des Frais de Tenue & Décharge Kits
 * GEICG - Conforme Règle 1 (Zero Hardcoding) & Règle 9 (Configurations & Logos)
 */
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$nomEtab = $etablissement['nom_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION';
$numAutorisation = $etablissement['numero_autorisation'] ?? 'AGRÉÉ PAR L\'ÉTAT ET LE FDFP';
$adresse = $etablissement['adresse_etablissement'] ?? 'Bouaké - Côte d\'Ivoire';
$contacts = !empty($etablissement['contacts_formatted']) ? $etablissement['contacts_formatted'] : ($etablissement['telephone_mobile'] ?? '27 31 62 40 57 / 07 79 37 37 38');
$email = $etablissement['email_etablissement'] ?? '';
$siteWeb = $etablissement['site_web'] ?? 'www.groupe-eicg.net';

$logoPath = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.png';
}
if (!empty($etablissement['logo_etablissement'])) {
    $customLogo = __DIR__ . '/../../../' . ltrim($etablissement['logo_etablissement'], '/');
    if (file_exists($customLogo)) {
        $logoPath = $customLogo;
    }
}

$resolvedPhoto = '';
if (!empty($photo_etudiant)) {
    $cleanRel = ltrim($photo_etudiant, '/');
    $cand1 = __DIR__ . '/../../../public/' . $cleanRel;
    $cand2 = __DIR__ . '/../../../' . $cleanRel;
    if (file_exists($cand1)) {
        $resolvedPhoto = $cand1;
    } elseif (file_exists($cand2)) {
        $resolvedPhoto = $cand2;
    }
}
$placeholderPhoto = placeholderPath('etudiant');

$itemsList = !empty($items_kits) ? $items_kits : [];
$remisList = !empty($kits_remis) ? $kits_remis : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu de Paiement des Frais de Tenue - <?= htmlspecialchars($matricule_etudiant ?? '') ?></title>
  <style>
    @page {
      margin: 4.5mm 6mm 4.5mm 6mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #000000;
      line-height: 1.15;
    }
    .page-frame {
      border: 1.5px solid #1E3A5F;
      padding: 6px 8px;
      box-sizing: border-box;
    }

    /* Header Institution */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2px;
    }
    .logo-box {
      width: 50px;
      height: 40px;
      background: #1E3A5F;
      color: #FFFFFF;
      text-align: center;
      font-weight: bold;
      font-size: 9pt;
      padding-top: 3px;
    }
    .inst-title {
      font-size: 11pt;
      font-weight: bold;
      color: #990000;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: 0.3px;
      line-height: 1.15;
    }
    .inst-subtitle {
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      color: #0369A1;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 7.5pt;
      font-style: italic;
      text-align: center;
      color: #475569;
    }

    /* Bannières Titres */
    .banner-title {
      background: #1E3A5F;
      color: #FFFFFF;
      font-size: 15pt;
      font-weight: bold;
      text-align: center;
      padding: 3px;
      margin-top: 5px;
      margin-bottom: 2px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .banner-title-secondary {
      background: #475569;
      color: #FFFFFF;
      font-size: 9pt;
      font-weight: bold;
      text-align: center;
      padding: 7px 2.5px;
      margin-top: 9px;
      margin-bottom: 10px;
      text-transform: uppercase;
    }

    .annee-header {
      font-size: 10pt;
      font-weight: bold;
      text-align: center;
      margin-top: 5px;
      margin-bottom: 5px;
      color: #1E3A5F;
    }

    /* Info Table */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .info-table td {
      padding: 1.5px 3px;
      vertical-align: top;
      font-size: 8pt;
    }
    .photo-box {
      width: 100px;
      height: 100px;
      border: 1px solid #CBD5E1;
      object-fit: cover;
    }
    .val-bold {
      font-weight: bold;
      color: #0F172A;
    }
    .text-blue {
      color: #0284C7;
      font-weight: bold;
      font-size: 7.5pt;
    }

    /* Tableaux Financiers & Kits */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .data-table th, .data-table td {
      border: 0.5px solid #000000;
      padding: 2px 4px;
      font-size: 9.5pt;
    }
    .data-table th {
      background: #E2E8F0;
      font-weight: bold;
      text-align: center;
      color: #0F172A;
    }

    .dashed-sep {
      border-top: 1px dashed #64748B;
      margin-top: 25px;
      margin-bottom: 30px;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- ========================================================================= -->
  <!-- SECTION 1 : REÇU PRINCIPAL (ORIGINAL ÉLÈVE)                              -->
  <!-- ========================================================================= -->

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG (MODE COMPACT) -->
  <?php 
    $headerMode = 'compact';
    $showHeaderDivider = false;
    include __DIR__ . '/components/header_institution.php'; 
  ?>

  <!-- BANNIÈRE TITRE -->
  <div class="banner-title">Reçu de Paiement des Frais de Tenue & Décharge Kits</div>
  <div class="annee-header">ANNÉE ACADÉMIQUE : <?= htmlspecialchars(!empty($annee_libelle) ? $annee_libelle : '-') ?></div>

  <!-- INFOS ÉTUDIANT -->
  <table class="info-table">
    <tr>
      <td style="width: 82%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 38%;">
              N° <span class="val-bold"><?= htmlspecialchars($code_paiement ?? '-') ?></span>
              <br>
              <?php if (!empty($code_paiement) && $code_paiement !== '-'): ?>
                <div style="margin-top: 2px;">
                  <barcode code="<?= htmlspecialchars($code_paiement) ?>" type="C128A" size="0.45" height="0.95" />
                </div>
                <br>
                <br>
              <?php endif; ?>
            </td>
            <td style="width: 32%;">Date : <span class="val-bold"><?= htmlspecialchars($date_operation ?? date('d/m/Y')) ?></span></td>
            <td style="width: 30%;">Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Matricule : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Contacts : <span class="val-bold"><?= htmlspecialchars($telephone_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom & Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
            <td>Classe / Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Montant Opération : <span class="val-bold"><?= number_format((float)($montant_operation ?? 0), 0, ',', ' ') ?> FCFA</span></td>
            <td colspan="2" class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></td>
          </tr>
        </table>
      </td>
      <td style="width: 18%; text-align: right; vertical-align: top;">
        <?php if (!empty($resolvedPhoto) && file_exists($resolvedPhoto)): ?>
          <img src="<?= $resolvedPhoto ?>" class="photo-box">
        <?php elseif (file_exists($placeholderPhoto)): ?>
          <img src="<?= $placeholderPhoto ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 75px; color: #94A3B8; font-size: 7.5pt;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <br>
  <!-- TABLEAU FINANCIER DES FRAIS DE TENUE -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 33.33%;">Montant Frais de Tenue</th>
        <th style="width: 33.33%;">Montant Payé</th>
        <th style="width: 33.33%;">Reste à Payer</th>
      </tr>
    </thead>
    <tbody>
      <tr style="text-align: center; font-weight: bold;">
        <td><?= number_format((float)($montant_payer_tenue ?? 0), 0, ',', ' ') ?> FCFA</td>
        <td><?= number_format((float)($montant_paye_tenue ?? 0), 0, ',', ' ') ?> FCFA</td>
        <td><?= htmlspecialchars($reste_payer_tenue ?? 'Soldé') ?></td>
      </tr>
    </tbody>
  </table>

  <!-- TABLEAU DES KITS / ACCESSOIRES -->
  <table class="data-table">
    <thead>
      <tr style="background: #1E3A5F; color: #FFFFFF;">
        <th colspan="3" style="text-align: center; font-size: 10pt; color: #0d0d0dff;">Kits Scolaires Assignés</th>
      </tr>
      <tr>
        <th style="width: 45%;">Désignation</th>
        <th style="width: 30%;">Statut / Date de Réception</th>
        <th style="width: 25%;">Émargement Élève</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($itemsList)): ?>
        <?php foreach ($itemsList as $k): 
          $libelleK = is_array($k) ? ($k['libelle_accessoire'] ?? ($k['libelle'] ?? '')) : (string)$k;
          $dateK = is_array($k) ? ($k['date_retrait_formatee'] ?? ($k['date'] ?? '')) : '';
          $etatK = is_array($k) ? ($k['etat_retrait'] ?? '') : '';
          $isRetire = ($etatK === 'retire' || !empty($dateK));
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($libelleK) ?></strong></td>
          <td style="text-align: center;"><?= $isRetire ? ('✓ Remis le ' . htmlspecialchars($dateK ?: date('d/m/Y'))) : '<span style="color:#C2410C;">⏳ En attente de remise</span>' ?></td>
          <td style="text-align: center; font-style: italic; color: #64748B;"><?= $isRetire ? 'Reçu' : '....................' ?></td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="3" style="text-align: center; color: #64748B; font-style: italic;">Aucun kit ou fourniture assigné.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>


  <!-- SIGNATURES ORIGINAL -->
  <table style="width: 100%; margin-top: 4px;">
    <tr>
      <td style="width: 45%;">
        <strong>LE GESTIONNAIRE / MAGASINIER</strong><br>
        <span style="font-size: 7.5pt; margin-top: 2px; display: block; color: #334155;"><?= htmlspecialchars($gestionnaire_nom ?? '') ?></span>
      </td>
      <td style="width: 10%; text-align: center; font-size: 7pt; color: #64748B;">
        Cachet
      </td>
      <td style="width: 45%; text-align: right;">
        <strong>LE SERVICE FINANCIER / CAISSE</strong><br>
        <span style="font-size: 7.5pt; margin-top: 2px; display: block; color: #334155;"><?= htmlspecialchars($caissier_nom ?? '') ?></span>
      </td>
    </tr>
  </table>

  <div class="dashed-sep"></div>

  <!-- ========================================================================= -->
  <!-- SECTION 2 : SOUCHE ARCHIVAGE GESTIONNAIRE                                 -->
  <!-- ========================================================================= -->
  <div class="banner-title-secondary">SOUCHE REÇU DE PAIEMENT DE TENUE & DÉCHARGE POUR ARCHIVAGE GESTIONNAIRE</div>

  <table class="info-table" style="font-size: 7.5pt;">
    <tr>
      <td style="width: 25%;">
        N° <span class="val-bold"><?= htmlspecialchars($code_paiement ?? '-') ?></span>
        <?php if (!empty($code_paiement) && $code_paiement !== '-'): ?>
          <div style="margin-top: 1px;">
            <barcode code="<?= htmlspecialchars($code_paiement) ?>" type="C128A" size="0.45" height="0.95" />
          </div>
          <br>
          <br>
        <?php endif; ?>
      </td>
      <td style="width: 25%;">Classe : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
      <td style="width: 25%;">Matricule : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
      <td style="width: 25%; text-align: right;">Date : <span class="val-bold"><?= htmlspecialchars($date_operation ?? date('d/m/Y')) ?></span></td>
    </tr>
    <tr>
      <td colspan="2">Nom & Prénoms : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
      <td colspan="2" style="text-align: right;">Montant Payé Tenue : <span class="val-bold"><?= number_format((float)($montant_paye_tenue ?? $montant_operation ?? 0), 0, ',', ' ') ?> FCFA</span></td>
    </tr>
  </table>

  <!-- KITS REÇUS TABLE -->
  <table class="data-table" style="font-size: 7pt;">
    <thead>
      <tr style="background: #CBD5E1;">
        <th style="width: 50%;">Kits / Fournitures Remis</th>
        <th style="width: 25%;">Date de Réception</th>
        <th style="width: 25%;">Émargement Élève</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($remisList)): ?>
        <?php foreach ($remisList as $kr): ?>
        <tr>
          <td><strong><?= htmlspecialchars(is_array($kr) ? ($kr['libelle_accessoire'] ?? '') : (string)$kr) ?></strong></td>
          <td style="text-align: center;"><?= htmlspecialchars(is_array($kr) ? ($kr['date_retrait_formatee'] ?? date('d/m/Y')) : date('d/m/Y')) ?></td>
          <td style="text-align: center;">Reçu</td>
        </tr>
        <?php endforeach; ?>
      <?php elseif (!empty($itemsList)): ?>
        <?php foreach ($itemsList as $ki): ?>
        <tr>
          <td><strong><?= htmlspecialchars(is_array($ki) ? ($ki['libelle_accessoire'] ?? '') : (string)$ki) ?></strong></td>
          <td style="text-align: center;"><?= !empty($ki['date_retrait_formatee']) ? htmlspecialchars($ki['date_retrait_formatee']) : 'En attente' ?></td>
          <td style="text-align: center;"><?= !empty($ki['date_retrait_formatee']) ? 'Reçu' : '..........' ?></td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="3" style="text-align: center; color: #64748B; font-style: italic;">Aucun kit remis.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <table style="width: 100%; font-size: 7.5pt; margin-top: 2px;">
    <tr>
      <td style="width: 50%;"><strong>GESTIONNAIRE</strong> : <?= htmlspecialchars($gestionnaire_nom ?? '') ?></td>
      <td style="width: 50%; text-align: right;"><strong>CAISSIER(RE)</strong> : <?= htmlspecialchars($caissier_nom ?? '') ?></td>
    </tr>
  </table>

  <div class="dashed-sep"></div>

  <!-- ========================================================================= -->
  <!-- SECTION 3 : SOUCHE ARCHIVAGE CAISSE                                       -->
  <!-- ========================================================================= -->
  <div class="banner-title-secondary">SOUCHE REÇU DE PAIEMENT DE TENUE POUR ARCHIVAGE CAISSE</div>

  <table class="info-table" style="font-size: 7.5pt;">
    <tr>
      <td style="width: 82%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 35%;">N° <span class="val-bold"><?= htmlspecialchars($code_paiement ?? '-') ?></span></td>
            <td style="width: 35%;">Classe : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
            <td style="width: 30%;">Matricule : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="3">Nom & Prénoms : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Date : <span class="val-bold"><?= htmlspecialchars($date_operation ?? date('d/m/Y')) ?></span></td>
            <td colspan="2">Montant Payé Tenue : <span class="val-bold"><?= number_format((float)($montant_paye_tenue ?? $montant_operation ?? 0), 0, ',', ' ') ?> FCFA</span></td>
          </tr>
        </table>
      </td>
      <td style="width: 18%; text-align: right; vertical-align: top;">
        <?php if (!empty($resolvedPhoto) && file_exists($resolvedPhoto)): ?>
          <img src="<?= $resolvedPhoto ?>" class="photo-box" style="height: 100px; width: 100px;">
        <?php elseif (file_exists($placeholderPhoto)): ?>
          <img src="<?= $placeholderPhoto ?>" class="photo-box" style="height: 100px; width: 100px;">
        <?php else: ?>
          <div class="photo-box" style="height: 100px; width: 100px; background: #F1F5F9; text-align: center; line-height: 60px; color: #94A3B8; font-size: 7pt;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <table style="width: 100%; font-size: 7.5pt; margin-top: 2px;">
    <tr>
      <td style="width: 50%;"><strong>GESTIONNAIRE</strong> : <?= htmlspecialchars($gestionnaire_nom ?? '') ?></td>
      <td style="width: 50%; text-align: right;"><strong>CAISSIER(RE)</strong> : <?= htmlspecialchars($caissier_nom ?? '') ?></td>
    </tr>
  </table>

</div>

</body>
</html>
