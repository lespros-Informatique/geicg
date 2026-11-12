<?php
/**
 * Template PDF mPDF : Répertoire / Catalogue des Kits & Accessoires Actifs
 * Conforme Directive Système Règle 9 & Règle 1.3
 */
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$nomEtab = $etablissement['nom_etablissement'] ?? '';
$sigleEtab = $etablissement['sigle_etablissement'] ?? '';
$villeSignature = $etablissement['ville_signature'] ?? ($etablissement['ville_etablissement'] ?? '');
$titreDirection = $etablissement['titre_service_scolarite'] ?? ($etablissement['titre_direction_pedagogique'] ?? '');
$nomSignataire = $etablissement['nom_responsable_scolarite'] ?? ($etablissement['nom_directeur_etudes'] ?? '');
$prefixeRef = !empty($etablissement['prefixe_reference']) ? $etablissement['prefixe_reference'] : 'REF';
$piedPage = $etablissement['texte_pied_page'] ?? $nomEtab;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Catalogue des Kits Actifs<?= $sigleEtab !== '' ? ' - ' . htmlspecialchars($sigleEtab) : '' ?></title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 10mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9pt;
      color: #0F172A;
      line-height: 1.3;
    }
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 7px 10px;
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
      margin-top: 2px;
    }
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      border: 0.5px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 5px 8px;
      font-size: 8.5pt;
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
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 14px;
    }
    .data-table th {
      background-color: #686868ff;
      color: #FFFFFF;
      border: 0.5px solid #0F172A;
      padding: 6px 8px;
      font-size: 8.5pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 0.5px solid #CBD5E1;
      padding: 6px 8px;
      font-size: 8.5pt;
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
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 8pt;
    }
    .badge-actif {
      background-color: #DCFCE7;
      color: #15803D;
      font-weight: bold;
      padding: 2px 8px;
      border-radius: 3px;
      font-size: 7.5pt;
    }
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
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
      margin-bottom: 24px;
    }
    .signature-mention {
      font-size: 7.5pt;
      font-style: italic;
      color: #64748B;
      text-align: center;
    }
  </style>
</head>
<body>

  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($nomEtab) ?> &bull; Répertoire des Kits & Accessoires Actifs
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 45%; text-align: left;">
          <?= htmlspecialchars($piedPage) ?> &bull; Édité le <?= date('d/m/Y H:i') ?>
        </td>
        <td style="width: 15%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: <?= htmlspecialchars($prefixeRef) ?>-CAT-KITS-<?= date('Ymd') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">RÉPERTOIRE DES KITS & ACCESSOIRES </div>
        <div class="banner-sub">ARTICLES ET KITS SCOLAIRES DISPONIBLES À L'INSCRIPTION</div>
      </td>
    </tr>
  </table>

  <table class="meta-box-table">
    <tr>
      <td style="width: 50%;"><span class="meta-label">Total Kits Actifs :</span> <span class="meta-value"><?= count($rows ?? []) ?></span></td>
      <td style="width: 50%; text-align: right;"><span class="meta-label">Date d'édition :</span> <span class="meta-value"><?= date('d/m/Y à H:i') ?></span></td>
    </tr>
  </table>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 45px;">N°</th>
        <th style="text-align: left; padding-left: 10px;">Désignation du Kit / Accessoire</th>
        <th style="width: 180px;">Type de Filière</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="3" class="text-center" style="padding: 18px; color: #64748B; font-style: italic;">
            Aucun kit ou accessoire actif enregistré dans le répertoire.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $idx => $r):
          $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
          $tf = $r['type_filiere_cible'] ?? 'TOUT';
          $filiereText = ($tf === 'INDUSTRIELLE') ? 'Filière Industrielle' : (($tf === 'TERTIAIRE') ? 'Filière Tertiaire' : 'Toutes Filières');
        ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center text-bold" style="color: #64748B;"><?= $idx + 1 ?></td>
            <td class="text-left text-bold" style="color: #0F172A; padding-left: 10px;">
              <?= htmlspecialchars($r['libelle_accessoire'] ?? '-') ?>
            </td>
            <td class="text-center">
              <?= htmlspecialchars($filiereText) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>

