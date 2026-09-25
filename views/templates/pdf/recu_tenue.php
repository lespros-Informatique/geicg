<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu de Paiement des frais de tenue</title>
  <style>
    @page {
      margin: 6mm 6mm 6mm 6mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 10px;
      color: #000000;
      line-height: 1.2;
    }
    .page-frame {
      border: 2px solid #800000;
      padding: 8px 10px;
      box-sizing: border-box;
    }

    /* Header Institution */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .logo-box {
      width: 55px;
      height: 45px;
      background: #800000;
      color: #FFFFFF;
      text-align: center;
      font-weight: bold;
      font-size: 10px;
      padding-top: 3px;
    }
    .inst-title {
      font-size: 13px;
      font-weight: bold;
      color: #800000;
      text-decoration: underline;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 10.5px;
      font-weight: bold;
      text-align: center;
      color: #000000;
    }
    .inst-contacts {
      font-size: 9.5px;
      font-style: italic;
      text-align: center;
    }

    /* Bannières Titres */
    .banner-title {
      background: #808080;
      color: #000000;
      font-size: 13.5px;
      font-weight: bold;
      text-align: center;
      padding: 3px;
      margin-top: 3px;
      margin-bottom: 3px;
      text-transform: uppercase;
    }
    .banner-subhead {
      background: #D1D5DB;
      color: #000000;
      font-size: 11px;
      font-weight: bold;
      text-align: center;
      padding: 2px;
    }

    .annee-header {
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 4px;
    }

    /* Info Table */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .info-table td {
      padding: 1.5px 3px;
      vertical-align: top;
      font-size: 10px;
    }
    .photo-box {
      width: 65px;
      height: 80px;
      border: 1px solid #000000;
      object-fit: cover;
    }
    .val-bold {
      font-weight: bold;
    }
    .text-blue {
      color: #0000D0;
      font-weight: bold;
    }

    /* Tableaux Financiers & Kits */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .data-table th, .data-table td {
      border: 1px solid #000000;
      padding: 2.5px 4px;
      font-size: 9.5px;
    }
    .data-table th {
      background: #D1D5DB;
      font-weight: bold;
      text-align: center;
    }

    .dashed-sep {
      border-top: 1px dashed #000000;
      margin-top: 6px;
      margin-bottom: 6px;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- ========================================================================= -->
  <!-- SECTION 1 : REÇU PRINCIPAL (ORIGINAL ÉLÈVE)                              -->
  <!-- ========================================================================= -->

  <!-- EN-TÊTE -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 15%; vertical-align: middle;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 44px; max-width: 100px;">
        <?php else: ?>
          <div class="logo-box">GROUPE<br>EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 85%; text-align: center;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">Agréé par l'Etat et le FDFP</div>
        <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
      </td>
    </tr>
  </table>

  <!-- BANNIÈRE TITRE -->
  <div class="banner-title">Reçu de Paiement des frais de tenue</div>
  <div class="annee-header">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025-2026') ?></div>

  <!-- INFOS ÉTUDIANT -->
  <table class="info-table">
    <tr>
      <td style="width: 80%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 33%;">
              N° <span class="val-bold"><?= htmlspecialchars($code_paiement ?? 'GE-25260003') ?></span>
              <div style="margin-top: 2px;">
                <barcode code="<?= htmlspecialchars($code_paiement ?? 'GE-25260003') ?>" type="C128A" size="0.6" height="0.6" />
              </div>
            </td>
            <td style="width: 33%;">Date : <span class="val-bold"><?= htmlspecialchars($date_operation ?? date('08/09/2025')) ?></span></td>
            <td style="width: 34%;">Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Numéro réf étudiant( e) : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td>Contacts : <span class="val-bold"><?= htmlspecialchars($telephone_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Nom_Prénom(s) : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
            <td>Filière_Niveau : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Montant de l'Opération : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?>CFA</span></td>
            <td colspan="2" class="text-blue"><?= htmlspecialchars($montant_operation_lettres ?? '') ?></td>
          </tr>
        </table>
      </td>
      <td style="width: 20%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 80px; color: #94A3B8; font-size: 8.5px;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- TABLEAU FINANCIER DES FRAIS DE TENUE -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 33.33%;">Montant à Payer</th>
        <th style="width: 33.33%;">Montant Payé</th>
        <th style="width: 33.33%;">Reste à Payer</th>
      </tr>
    </thead>
    <tbody>
      <tr style="text-align: center; font-weight: bold;">
        <td><?= number_format($montant_payer_tenue ?? $montant_operation ?? 0, 0, ',', ' ') ?>CFA</td>
        <td><?= number_format($montant_paye_tenue ?? $montant_operation ?? 0, 0, ',', ' ') ?>CFA</td>
        <td><?= htmlspecialchars($reste_payer_tenue ?? 'Soldé') ?></td>
      </tr>
    </tbody>
  </table>

  <!-- TABLEAU DES KITS / ACCESSOIRES -->
  <table class="data-table">
    <thead>
      <tr style="background: #808080; color: #FFFFFF;">
        <th colspan="3" style="text-align: center; font-size: 10.5px;">Kits</th>
      </tr>
      <tr>
        <th style="width: 35%;">Désignation</th>
        <th style="width: 35%;">Date de Réception</th>
        <th style="width: 30%;">Émargements</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $defaultKits = ['1 Cravate', '1 Tissu complet costumes', '1 Polo', '1 Tissu Pantalon'];
        $kitsList = !empty($items_kits) ? $items_kits : $defaultKits;
        foreach ($kitsList as $kName):
      ?>
      <tr>
        <td><strong><?= htmlspecialchars(is_array($kName) ? ($kName['libelle'] ?? '') : $kName) ?></strong></td>
        <td><?= htmlspecialchars(is_array($kName) ? ($kName['date'] ?? '') : '') ?></td>
        <td></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- INFORMATION RÉCAPITULATIVE SCOLARITÉ -->
  <table class="data-table">
    <thead>
      <tr style="background: #D1D5DB;">
        <th colspan="3" style="text-align: center; font-size: 10px;">Information récapitulative sur la scolarité</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="width: 33.33%;">Montant à Payer : <strong><?= isset($scolarite_recap['montant_payer']) ? number_format($scolarite_recap['montant_payer'], 0, ',', ' ') . 'CFA' : '' ?></strong></td>
        <td style="width: 33.33%;">Montant payé : <strong><?= isset($scolarite_recap['montant_paye']) ? number_format($scolarite_recap['montant_paye'], 0, ',', ' ') . 'CFA' : '' ?></strong></td>
        <td style="width: 33.34%;">Reste à payer : <strong><?= isset($scolarite_recap['reste_payer']) ? number_format($scolarite_recap['reste_payer'], 0, ',', ' ') . 'CFA' : '' ?></strong></td>
      </tr>
      <tr>
        <td colspan="3">Prochain RDV à la caisse : <strong><?= htmlspecialchars($scolarite_recap['prochain_rdv'] ?? 'Soldé') ?></strong></td>
      </tr>
    </tbody>
  </table>

  <!-- SIGNATURES ORIGINAL -->
  <table style="width: 100%; margin-top: 6px;">
    <tr>
      <td style="width: 45%;">
        <strong>GESTIONNAIRE</strong><br>
        <span style="font-size: 9.5px; margin-top: 2px; display: block;"><?= htmlspecialchars($gestionnaire_nom ?? 'Mlle ESSOH ELIACHIB') ?></span>
      </td>
      <td style="width: 10%; text-align: center; font-size: 8px; color: #64748B; font-family: monospace;">
        LY-723/GEB/RHC25
      </td>
      <td style="width: 45%; text-align: right;">
        <span style="text-decoration: underline; font-weight: bold;">CAISSIER(RE)</span><br>
        <span style="font-size: 9.5px; margin-top: 2px; display: block;"><?= htmlspecialchars($caissier_nom ?? 'Mlle KONE N\'diatty A. Mariam') ?></span>
      </td>
    </tr>
    <tr>
      <td style="font-size: 9px; color: #475569;"><?= htmlspecialchars($date_impression ?? date('26/08/2026')) ?></td>
      <td></td>
      <td style="font-size: 9px; color: #475569; text-align: right;"><?= htmlspecialchars($date_impression ?? date('26/08/2026')) ?></td>
    </tr>
  </table>

  <!-- ========================================================================= -->
  <!-- SECTION 2 : SOUCHE ARCHIVAGE GESTIONNAIRE                                 -->
  <!-- ========================================================================= -->
  <div class="banner-title" style="font-size: 11px; padding: 2px; margin-top: 8px;">SOUCHE REÇU DE PAIEMENT DE TENUE POUR ARCHIVAGE GESTIONNAIRE</div>

  <table class="info-table" style="font-size: 9.5px;">
    <tr>
      <td style="width: 25%;">
        N° <span class="val-bold"><?= htmlspecialchars($code_paiement ?? 'GE-25260003') ?></span>
        <div style="margin-top: 2px;">
          <barcode code="<?= htmlspecialchars($code_paiement ?? 'GE-25260003') ?>" type="C128A" size="0.6" height="0.6" />
        </div>
      </td>
      <td style="width: 25%;">Fil/Niv : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? 'RHC_1A') ?></span></td>
      <td style="width: 25%;">Contact : <span class="val-bold"><?= htmlspecialchars($telephone_etudiant ?? '0757885051') ?></span></td>
      <td style="width: 25%; text-align: right;">Date : <span class="val-bold"><?= htmlspecialchars($date_operation ?? '08/09/2025') ?></span></td>
    </tr>
    <tr>
      <td colspan="2">Nom_Prénoms : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
      <td colspan="2">Montant Payé : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?>CFA</span></td>
    </tr>
  </table>

  <!-- KITS REÇUS TABLE -->
  <table class="data-table" style="font-size: 9px;">
    <thead>
      <tr style="background: #D1D5DB;">
        <th colspan="3" style="text-align: center;">KITS Reçus</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="width: 35%;">1 Tissu Pantalon</td>
        <td style="width: 35%;">1 Polo</td>
        <td style="width: 30%; font-weight: bold; text-align: center;">Date de réception</td>
      </tr>
      <tr>
        <td>1 Tissu complet Veste</td>
        <td>1 Cravate</td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <table style="width: 100%; font-size: 9px;">
    <tr>
      <td style="width: 50%;"><strong>GESTIONNAIRE</strong> : <?= htmlspecialchars($gestionnaire_nom ?? 'Mlle ESSOH ELIACHIB') ?></td>
      <td style="width: 50%; text-align: right;"><u>CAISSIER(RE)</u> : <?= htmlspecialchars($caissier_nom ?? 'Mlle KONE N\'diatty A. Mariam') ?></td>
    </tr>
    <tr>
      <td style="color: #64748B; font-size: 8.5px;"><?= htmlspecialchars($date_impression ?? '26/08/2026') ?></td>
      <td style="color: #64748B; font-size: 8.5px; text-align: right;"><?= htmlspecialchars($date_impression ?? '26/08/2026') ?></td>
    </tr>
  </table>

  <div class="dashed-sep"></div>

  <!-- ========================================================================= -->
  <!-- SECTION 3 : SOUCHE ARCHIVAGE CAISSE                                       -->
  <!-- ========================================================================= -->
  <div class="banner-title" style="font-size: 11px; padding: 2px; margin-top: 4px;">SOUCHE REÇU DE PAIEMENT DE TENUE POUR ARCHIVAGE CAISSE</div>

  <table class="info-table" style="font-size: 9.5px;">
    <tr>
      <td style="width: 80%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 33%;">N° <span class="val-bold"><?= htmlspecialchars($code_paiement ?? 'GE-25260003') ?></span></td>
            <td style="width: 33%;">Fil/Niv : <span class="val-bold"><?= htmlspecialchars($filiere_niveau ?? 'RHC_1A') ?></span></td>
            <td style="width: 34%;">Contact : <span class="val-bold"><?= htmlspecialchars($telephone_etudiant ?? '0757885051') ?></span></td>
          </tr>
          <tr>
            <td colspan="3">Nom_Prénoms : <span class="val-bold"><?= htmlspecialchars($nom_prenom_etudiant ?? '-') ?></span></td>
          </tr>
          <tr>
            <td>Date : <span class="val-bold"><?= htmlspecialchars($date_operation ?? '08/09/2025') ?></span></td>
            <td colspan="2">Montant Payé : <span class="val-bold"><?= number_format($montant_operation ?? 0, 0, ',', ' ') ?>CFA</span></td>
          </tr>
        </table>
      </td>
      <td style="width: 20%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant) && file_exists(__DIR__ . '/../../public/' . ltrim($photo_etudiant, '/'))): ?>
          <img src="<?= __DIR__ . '/../../public/' . ltrim($photo_etudiant, '/') ?>" class="photo-box" style="height: 65px; width: 55px;">
        <?php else: ?>
          <div class="photo-box" style="height: 65px; width: 55px; background: #F1F5F9; text-align: center; line-height: 65px; color: #94A3B8; font-size: 8px;">PHOTO</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <table style="width: 100%; font-size: 9px; margin-top: 4px;">
    <tr>
      <td style="width: 50%;"><strong>GESTIONNAIRE</strong> : <?= htmlspecialchars($gestionnaire_nom ?? 'Mlle ESSOH ELIACHIB') ?></td>
      <td style="width: 50%; text-align: right;"><u>CAISSIER(RE)</u> : <?= htmlspecialchars($caissier_nom ?? 'Mlle KONE N\'diatty A. Mariam') ?></td>
    </tr>
    <tr>
      <td></td>
      <td style="color: #64748B; font-size: 8.5px; text-align: right;"><?= htmlspecialchars($date_impression ?? '26/08/2026') ?></td>
    </tr>
  </table>

</div>

</body>
</html>
