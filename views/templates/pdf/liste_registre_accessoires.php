<?php
/**
 * Template PDF mPDF : Registre de Retrait et d'Émargement des Kits Fournitures
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
$titreDirection = $etablissement['titre_service_scolarite'] ?? ($etablissement['titre_direction_pedagogique'] ?? 'Le Responsable du Service Fournitures & Kits');
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

$totalKits = (int)($stats['total'] ?? 0);
$retires = (int)($stats['retires'] ?? ($stats['retire'] ?? 0));
$enAttente = (int)($stats['en_attente'] ?? 0);
$tauxRetrait = (float)($stats['taux_retrait'] ?? ($stats['taux'] ?? 0));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre Retrait Kits - <?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GEICG') ?></title>
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
      color: #15803D;
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
      background-color: #15803D;
      margin-top: 3px;
      margin-bottom: 6px;
    }

    /* Bannière Titre */
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      background-color: #15803D;
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
      color: #DCFCE7;
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
    .kpi-retires { background-color: #F0FDF4; color: #15803D; }
    .kpi-attente { background-color: #FFF7ED; color: #C2410C; }
    .kpi-taux { background-color: #F0F9FF; color: #0284C7; }

    /* Tableau des Données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 12px;
    }
    .data-table th {
      background-color: #15803D;
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
    .badge-retire {
      background-color: #DCFCE7;
      color: #15803D;
      font-weight: bold;
      padding: 1.5px 6px;
      border-radius: 3px;
      font-size: 7.5pt;
      display: inline-block;
    }
    .badge-attente {
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
      color: #15803D;
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
      <?= htmlspecialchars($etablissement['nom_etablissement'] ?? 'GROUPE EICG') ?> &bull; Registre d'Émargement des Kits Fournitures &bull; Session <?= htmlspecialchars($annee_libelle ?? '') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 35%; text-align: left;">
          Document Officiel d'Émargement - <?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GEICG') ?>
        </td>
        <td style="width: 30%; text-align: center; font-style: italic;">
          Page {PAGENO} sur {nbpg}
        </td>
        <td style="width: 35%; text-align: right;">
          Imprimé le <?= date('d/m/Y à H:i') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- EN-TÊTE INSTITUTIONNEL OFFICIEL -->
  <table class="header-table">
    <tr>
      <?php if (file_exists($logoPath)): ?>
      <td style="width: 65px; text-align: left;">
        <img src="<?= $logoPath ?>" style="max-height: 52px; max-width: 65px;" alt="Logo" />
      </td>
      <?php endif; ?>
      <td style="padding-left: 8px;">
        <div class="inst-title"><?= htmlspecialchars($nomEtab) ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars($numAutorisation) ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars($adresse) ?> &bull; Tél: <?= htmlspecialchars($contacts) ?>
          <?php if (!empty($email)): ?> &bull; Email: <?= htmlspecialchars($email) ?><?php endif; ?>
          <?php if (!empty($siteWeb)): ?> &bull; Web: <?= htmlspecialchars($siteWeb) ?><?php endif; ?>
        </div>
      </td>
      <td style="width: 130px; text-align: right; vertical-align: top;">
        <div style="font-size: 7.5pt; font-weight: bold; color: #475569;">SERVICE LOGISTIQUE</div>
        <div style="font-size: 7pt; color: #64748B;">Émargement Fournitures</div>
        <div style="font-size: 7pt; color: #0F172A; font-weight: bold; margin-top: 3px;">Date: <?= date('d/m/Y') ?></div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE DE RETRAIT ET D'ÉMARGEMENT DES KITS SCOLAIRES</div>
        <div class="banner-sub">Bordereau officiel de remise des kits, uniformes et fournitures aux apprenants</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE FILTRAGE ET CONTEXTE -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span><br>
        <span class="meta-value"><?= !empty($annee_libelle) ? htmlspecialchars($annee_libelle) : 'Toutes les années' ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Classe Ciblée :</span><br>
        <span class="meta-value"><?= htmlspecialchars($classe_libelle ?? 'Toutes les classes') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Filtre Statut :</span><br>
        <span class="meta-value"><?= htmlspecialchars($statut_label ?? 'Tous les statuts') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Opérateur / Éditeur :</span><br>
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Service Scolarité') ?></span>
      </td>
    </tr>
  </table>

  <!-- BARRE DES KPIs / STATISTIQUES EN TEMPS RÉEL -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 25%;">
        TOTAL ÉLÈVES CIBLÉS<br>
        <span style="font-size: 11pt; font-weight: 900;"><?= count($rows ?? []) ?></span>
      </td>
      <td class="kpi-retires" style="width: 25%;">
        KITS REMIS (GLOBAL)<br>
        <span style="font-size: 11pt; font-weight: 900;"><?= $retires ?></span>
      </td>
      <td class="kpi-attente" style="width: 25%;">
        EN ATTENTE DE REMISE<br>
        <span style="font-size: 11pt; font-weight: 900;"><?= $enAttente ?></span>
      </td>
      <td class="kpi-taux" style="width: 25%;">
        TAUX DE RETRAIT GLOBAL<br>
        <span style="font-size: 11pt; font-weight: 900;"><?= $tauxRetrait ?>%</span>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DÉTAILLÉ DU REGISTRE -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 28px;">N°</th>
        <th style="width: 75px;">Matricule</th>
        <th style="text-align: left; padding-left: 8px;">Nom & Prénoms de l'Étudiant</th>
        <th style="width: 100px;">Classe</th>
        <th style="width: 75px;">Fournitures</th>
        <th style="width: 80px;">Statut</th>
        <th style="width: 110px;">Date & Émargement</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="7" class="text-center" style="padding: 16px; color: #64748B; font-style: italic;">
            Aucun enregistrement d'attribution ou de retrait trouvé pour ces critères.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $idx => $r): 
          $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
          $isComplet = ($r['total_retires'] == $r['total_kits'] && $r['total_kits'] > 0);
          $nomEtudiant = !empty($r['nom_complet']) ? $r['nom_complet'] : trim(($r['nom_etudiant'] ?? '') . ' ' . ($r['prenom_etudiant'] ?? ''));
          if (empty($nomEtudiant)) $nomEtudiant = 'Étudiant non identifié';
        ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center text-bold" style="color: #64748B;"><?= $idx + 1 ?></td>
            <td class="text-center">
              <span class="badge-code"><?= htmlspecialchars($r['matricule_etudiant'] ?? '-') ?></span>
            </td>
            <td class="text-left text-bold" style="color: #0F172A;">
              <?= htmlspecialchars($nomEtudiant) ?>
            </td>
            <td class="text-center" style="color: #1E3A5F; font-weight: bold;">
              <?= htmlspecialchars($r['libelle_classe'] ?? '-') ?>
            </td>
            <td class="text-center text-bold">
              <?= (int)($r['total_retires'] ?? 0) ?> / <?= (int)($r['total_kits'] ?? 0) ?>
            </td>
            <td class="text-center">
              <?php if ($isComplet): ?>
                <span class="badge-retire">Remis</span>
              <?php elseif (($r['total_retires'] ?? 0) > 0): ?>
                <span class="badge-attente">Partiel</span>
              <?php else: ?>
                <span class="badge-attente">En attente</span>
              <?php endif; ?>
            </td>
            <td class="text-center" style="font-size: 7pt; color: #64748B; vertical-align: bottom; height: 26px;">
              ..................................
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SECTION DES SIGNATURES OFFICIELLES -->
  <table class="signature-table">
    <tr>
      <td class="signature-box">
        <div class="signature-title"><?= htmlspecialchars($titreDirection) ?></div>
        <div style="height: 48px;"></div>
        <div class="signature-mention">
          Fait à <?= htmlspecialchars($villeSignature) ?>, le <?= date('d/m/Y') ?><br>
          <strong><?= htmlspecialchars($nomSignataire) ?></strong>
        </div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box">
        <div class="signature-title"><?= htmlspecialchars($etablissement['titre_direction_generale'] ?? 'La Direction Générale') ?></div>
        <div style="height: 48px;"></div>
        <div class="signature-mention">
          Visa & Cachet Officiel<br>
          <strong><?= htmlspecialchars($etablissement['nom_directeur_general'] ?? '') ?></strong>
        </div>
      </td>
    </tr>
  </table>

</body>
</html>

