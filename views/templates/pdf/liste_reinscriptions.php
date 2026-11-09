<?php
/**
 * Template PDF mPDF : Registre des Candidats à la Réinscription
 * GEICG - Conforme Directive Système Règle 9 & Sanctuaire UI
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
$villeSignature = $etablissement['ville_signature'] ?? ($etablissement['ville_etablissement'] ?? 'Bouaké');

$logoPath = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
if (!file_exists($logoPath)) {
    $logoPath = '/var/www/html/geicg/public/assets/images/logo/logo_eicg.jpg';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Candidats à la Réinscription - <?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GEICG') ?></title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 10mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #0F172A;
      line-height: 1.25;
    }

    /* En-tête Institutionnel */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .header-table td {
      vertical-align: middle;
    }
    .inst-title {
      font-size: 11pt;
      font-weight: bold;
      color: #990000;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.15;
    }
    .inst-subtitle {
      font-size: 8pt;
      font-weight: bold;
      color: #0369A1;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 7.5pt;
      color: #64748B;
      margin-top: 1px;
    }

    .divider-line {
      height: 1.5px;
      background-color: #1E3A5F;
      margin-top: 3px;
      margin-bottom: 5px;
    }

    /* Bannière Titre */
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 5px 8px;
      text-align: center;
      color: #FFFFFF;
    }
    .banner-title {
      font-size: 11pt;
      font-weight: bold;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 7.5pt;
      color: #E2E8F0;
      margin-top: 1px;
    }

    /* Cartouche Métadonnées & Filtres */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 4px 8px;
      font-size: 8pt;
      vertical-align: middle;
      border: 0.5px solid #E2E8F0;
    }
    .meta-label {
      font-weight: bold;
      color: #475569;
    }
    .meta-value {
      font-weight: bold;
      color: #0F172A;
    }

    /* Barre KPI Synthèse */
    .kpi-summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .kpi-summary-table td {
      padding: 4px 8px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      border: 1px solid #CBD5E1;
    }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }
    .kpi-hommes { background-color: #EEF2FF; color: #3730A3; }
    .kpi-femmes { background-color: #FDF2F8; color: #9D174D; }
    .kpi-session { background-color: #F0FDF4; color: #15803D; }

    /* Tableau des Données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 12px;
    }
    .data-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      border: 0.5px solid #0F172A;
      padding: 5px 6px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .data-table td {
      border: 0.5px solid #CBD5E1;
      padding: 4px 6px;
      font-size: 8pt;
      vertical-align: middle;
    }
    .row-even { background-color: #FFFFFF; }
    .row-odd { background-color: #F8FAFC; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-bold { font-weight: bold; }

    .badge-code {
      font-family: monospace;
      font-weight: bold;
      color: #1E3A5F;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 5px;
      border-radius: 3px;
      font-size: 7.5pt;
    }
    .badge-genre-m {
      background-color: #E0E7FF;
      color: #3730A3;
      font-weight: bold;
      padding: 1px 5px;
      border-radius: 3px;
      font-size: 7.5pt;
    }
    .badge-genre-f {
      background-color: #FCE7F3;
      color: #9D174D;
      font-weight: bold;
      padding: 1px 5px;
      border-radius: 3px;
      font-size: 7.5pt;
    }

    /* Signatures & Footer */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    .signature-box {
      width: 48%;
      vertical-align: top;
      border: 0.5px solid #CBD5E1;
      background-color: #FAFAFA;
      padding: 6px 10px;
    }
    .signature-title {
      font-size: 8pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      border-bottom: 0.5px solid #CBD5E1;
      padding-bottom: 3px;
      margin-bottom: 22px;
    }
    .signature-mention {
      font-size: 7pt;
      font-style: italic;
      color: #64748B;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['nom_etablissement'] ?? 'GROUPE EICG') ?> &bull; Registre des Candidats à la Réinscription &bull; Session <?= htmlspecialchars($annee_active_libelle ?? '') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          Document officiel GEICG &bull; Édité le <?= date('d/m/Y H:i:s') ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: REF-REINSC-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE DES CANDIDATS À LA RÉINSCRIPTION</div>
        <div class="banner-sub">SESSION ACADÉMIQUE : <?= htmlspecialchars($annee_active_libelle) ?></div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE FILTRES ET ÉDITION -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;"><span class="meta-label">Année Antérieure (N-1) :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['annee'] ?? '-') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Filière :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['filiere'] ?? 'Toutes les filières') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Niveau d'Études :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['niveau'] ?? 'Tous les niveaux') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Classe Antérieure :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['classe'] ?? 'Toutes les classes') ?></span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Session Cible Active :</span> <span class="meta-value"><?= htmlspecialchars($annee_active_libelle) ?></span></td>
      <td><span class="meta-label">Effectif Éligible :</span> <span class="meta-value"><?= count($candidates) ?> Candidat(s)</span></td>
      <td><span class="meta-label">Éditeur / Agent :</span> <span class="meta-value"><?= htmlspecialchars($editeur_nom) ?></span></td>
      <td><span class="meta-label">Date d'Édition :</span> <span class="meta-value"><?= date('d/m/Y H:i') ?></span></td>
    </tr>
  </table>

  <!-- BARRE SYNTHÈSE KPIS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 25%;">TOTAL ÉTUDIANTS : <?= $kpis['total'] ?? 0 ?></td>
      <td class="kpi-hommes" style="width: 25%;">HOMMES : <?= $kpis['hommes'] ?? 0 ?></td>
      <td class="kpi-femmes" style="width: 25%;">FEMMES : <?= $kpis['femmes'] ?? 0 ?></td>
      <td class="kpi-session" style="width: 25%;">SESSION ACTIVE : <?= htmlspecialchars($annee_active_libelle) ?></td>
    </tr>
  </table>

  <!-- TABLEAU DES CANDIDATS -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 4%;">#</th>
        <th style="width: 15%;">MATRICULE</th>
        <th style="width: 36%; text-align: left;">NOM & PRÉNOM(S)</th>
        <th style="width: 11%;">DATE NAIS.</th>
        <th style="width: 6%;">SEXE</th>
        <th style="width: 18%;">CLASSE ANTÉRIEURE</th>
        <th style="width: 10%;">ANNÉE CURSUS</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($candidates)): ?>
        <?php foreach ($candidates as $idx => $item): ?>
          <?php
            $etu = $item['etudiant'] ?? [];
            $prev = $item['prev'] ?? [];
            $nomComplet = mb_strtoupper($etu['nom_etudiant'] ?? '') . ' ' . ($etu['prenom_etudiant'] ?? '');
            $sexe = strtoupper(trim($etu['sexe_etudiant'] ?? 'M'));
            $isF = (strpos($sexe, 'F') !== false);
            $rawDateNais = $etu['date_naissance_etudiant'] ?? '';
            $dateNaisFmt = (!empty($rawDateNais) && $rawDateNais !== '0000-00-00') ? date('d/m/Y', strtotime($rawDateNais)) : '-';
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center text-bold"><?= $idx + 1 ?></td>
            <td class="text-center">
              <span class="badge-code"><?= htmlspecialchars($etu['matricule_etudiant'] ?? '-') ?></span>
            </td>
            <td class="text-left text-bold"><?= htmlspecialchars($nomComplet) ?></td>
            <td class="text-center"><?= htmlspecialchars($dateNaisFmt) ?></td>
            <td class="text-center">
              <?php if ($isF): ?>
                <span class="badge-genre-f">F</span>
              <?php else: ?>
                <span class="badge-genre-m">H</span>
              <?php endif; ?>
            </td>
            <td class="text-center text-bold"><?= htmlspecialchars($prev['classe_prev'] ?? '-') ?></td>
            <td class="text-center"><?= htmlspecialchars($prev['annee_prev'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center" style="padding: 15px; color: #64748B; font-style: italic;">
            Aucun candidat à la réinscription ne correspond aux critères sélectionnés.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURES OFFICIELLES -->
  <table class="signature-table">
    <tr>
      <td class="signature-box">
        <div class="signature-title"><?= htmlspecialchars($etablissement['titre_service_scolarite'] ?? 'Le Service Scolarité & Inscriptions') ?></div>
        <div style="font-weight: bold; font-size: 7.5pt; color: #1E293B; margin-top: 2px; margin-bottom: 22px;">
          <?= htmlspecialchars($etablissement['nom_responsable_scolarite'] ?? 'Le Chef de Scolarité') ?>
        </div>
        <div class="signature-mention"><?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? 'Cachet & Signature autorisée') ?></div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box">
        <div class="signature-title"><?= htmlspecialchars($etablissement['titre_direction_pedagogique'] ?? 'La Direction des Études & Pédagogie') ?></div>
        <div style="font-weight: bold; font-size: 7.5pt; color: #1E293B; margin-top: 2px; margin-bottom: 22px;">
          <?= htmlspecialchars($etablissement['nom_directeur_etudes'] ?? 'Le Directeur des Études') ?>
        </div>
        <div class="signature-mention">
          Fait à <?= htmlspecialchars($villeSignature) ?>, le <?= date('d/m/Y') ?>
        </div>
      </td>
    </tr>
  </table>

</body>
</html>
