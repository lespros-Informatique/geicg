<?php
/**
 * Template PDF mPDF : Registre de Dépôt des Dossiers Étudiants
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
$titreDirection = $etablissement['titre_service_scolarite'] ?? ($etablissement['titre_direction_pedagogique'] ?? 'Le Responsable de la Scolarité');
$nomSignataire = $etablissement['nom_responsable_scolarite'] ?? ($etablissement['nom_directeur_etudes'] ?? '');

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

$totalEtudiants = (int)($kpis['total'] ?? count($rows ?? []));
$completsCount = (int)($kpis['complets'] ?? 0);
$incompletsCount = (int)($kpis['incomplets'] ?? 0);
$tauxGlobal = (float)($kpis['taux_global'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre Dossiers Étudiants - <?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GEICG') ?></title>
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
      margin-bottom: 6px;
    }

    /* Bannière Titre */
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 6px 10px;
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
      font-size: 8pt;
      color: #E2E8F0;
      margin-top: 1px;
    }

    /* Cartouche Métadonnées & Filtres */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border: 0.5px solid #CBD5E1;
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

    /* Barre des Indicateurs / KPIs */
    .kpi-summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .kpi-summary-table td {
      padding: 5px 8px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      border: 0.5px solid #CBD5E1;
    }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }
    .kpi-complets { background-color: #F0FDF4; color: #15803D; }
    .kpi-incomplets { background-color: #FFF7ED; color: #C2410C; }
    .kpi-taux { background-color: #F0F9FF; color: #0284C7; }

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
    .badge-complet {
      background-color: #DCFCE7;
      color: #15803D;
      font-weight: bold;
      padding: 1.5px 6px;
      border-radius: 3px;
      font-size: 7.5pt;
      display: inline-block;
    }
    .badge-incomplet {
      background-color: #FED7AA;
      color: #C2410C;
      font-weight: bold;
      padding: 1.5px 6px;
      border-radius: 3px;
      font-size: 7.5pt;
      display: inline-block;
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
      <?= htmlspecialchars($etablissement['nom_etablissement'] ?? 'GROUPE EICG') ?> &bull; Registre des Dossiers Étudiants &bull; Session <?= htmlspecialchars($annee_libelle ?? '') ?>
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
          Réf: REF-DOSSIERS-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- EN-TÊTE ÉTABLISSEMENT -->
  <table class="header-table">
    <tr>
      <td style="width: 18%; text-align: left;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 50px; max-width: 130px;" alt="Logo EICG">
        <?php else: ?>
          <div style="font-size: 13pt; font-weight: bold; color: #1E3A5F;"><?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GROUPE EICG') ?></div>
        <?php endif; ?>
      </td>
      <td style="width: 82%; text-align: center;">
        <div class="inst-title"><?= htmlspecialchars($nomEtab) ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars(strpos($numAutorisation, 'AGRÉ') !== false ? $numAutorisation : 'AGRÉMENT N° ' . $numAutorisation) ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars($adresse) ?>
          &nbsp;&bull;&nbsp; Contacts : <?= htmlspecialchars($contacts) ?>
          <?php if (!empty($email)): ?>
            &nbsp;&bull;&nbsp; Email : <?= htmlspecialchars($email) ?>
          <?php endif; ?>
          &nbsp;&bull;&nbsp; Site Web : <?= htmlspecialchars($siteWeb) ?>
        </div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE DE CONTRÔLE ET DÉPÔT DES DOSSIERS ÉTUDIANTS</div>
        <div class="banner-sub">SESSION ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?: 'Toutes') ?></div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE FILTRES ET ÉDITION -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 30%;"><span class="meta-label">Année Académique :</span> <span class="meta-value"><?= htmlspecialchars($annee_libelle ?: 'En cours') ?></span></td>
      <td style="width: 35%;"><span class="meta-label">Classe :</span> <span class="meta-value"><?= htmlspecialchars($classe_libelle ?? 'Toutes') ?></span></td>
      <td style="width: 35%;"><span class="meta-label">Filtre Statut :</span> <span class="meta-value"><?= htmlspecialchars($statut_label ?? 'Tous') ?></span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="meta-label">Éditeur :</span> <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Service Scolarité') ?></span></td>
      <td style="text-align: right;"><span class="meta-label">Date d'édition :</span> <span class="meta-value"><?= date('d/m/Y à H:i') ?></span></td>
    </tr>
  </table>

  <!-- BARRE DES INDICATEURS / KPIS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 25%;">
        TOTAL ÉTUDIANTS : <?= $totalEtudiants ?>
      </td>
      <td class="kpi-complets" style="width: 25%;">
        DOSSIERS COMPLETS : <?= $completsCount ?>
      </td>
      <td class="kpi-incomplets" style="width: 25%;">
        DOSSIERS INCOMPLETS : <?= $incompletsCount ?>
      </td>
      <td class="kpi-taux" style="width: 25%;">
        TAUX GLOBAL DÉPÔT : <?= number_format($tauxGlobal, 1, ',', ' ') ?>%
      </td>
    </tr>
  </table>

  <!-- TABLEAU DES DOSSIERS -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 5%;">#</th>
        <th style="width: 17%;">Matricule</th>
        <th style="width: 35%;">Nom & Prénoms</th>
        <th style="width: 20%;">Classe</th>
        <th style="width: 13%;">Pièces Déposées</th>
        <th style="width: 10%;">Statut Dossier</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($rows)): ?>
        <?php foreach ($rows as $index => $r): ?>
          <?php 
            $rowClass = ($index % 2 === 0) ? 'row-even' : 'row-odd';
            $nomComplet = $r['nom_complet'] ?? '';
            $estComplet = !empty($r['est_complet']);
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $index + 1 ?></td>
            <td><span class="badge-code"><?= htmlspecialchars($r['matricule'] ?? '-') ?></span></td>
            <td class="text-left text-bold" style="color: #0F172A;"><?= htmlspecialchars($nomComplet) ?></td>
            <td class="text-left" style="font-weight: bold; color: #1E3A5F;"><?= htmlspecialchars($r['classe'] ?? '-') ?></td>
            <td class="text-center" style="font-weight: bold; color: #334155;">
              <?= (int)$r['deposees_count'] ?> / <?= (int)$r['total_pieces'] ?> (<?= (int)$r['pourcentage'] ?>%)
            </td>
            <td class="text-center">
              <?php if ($estComplet): ?>
                <span class="badge-complet">Complet</span>
              <?php else: ?>
                <span class="badge-incomplet">Incomplet</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center" style="padding: 16px; color: #64748B; font-style: italic;">
            Aucun dossier étudiant correspondant aux critères de sélection.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC SIGNATURES CONFORME RÈGLE 9 -->
  <table class="signature-table">
    <tr>
      <td class="signature-box" style="width: 48%;">
        <div class="signature-title">L'Agent de Réception des Pièces</div>
        <div style="height: 40px;"></div>
        <div class="signature-mention"><?= htmlspecialchars($editeur_nom) ?></div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box" style="width: 48%;">
        <div class="signature-title"><?= htmlspecialchars($titreDirection) ?></div>
        <div style="text-align: center; font-size: 7.5pt; color: #475569; margin-bottom: 2px;">
          Fait à <?= htmlspecialchars($villeSignature) ?>, le <?= date('d/m/Y') ?>
        </div>
        <div style="height: 35px;"></div>
        <div class="signature-mention"><?= htmlspecialchars($nomSignataire) ?></div>
      </td>
    </tr>
  </table>

</body>
</html>

