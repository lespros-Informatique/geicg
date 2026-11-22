<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}
$absences = $absences ?? [];
$filtres = $filtres ?? [];
$kpis = $kpis ?? [
    'total_absences' => count($absences),
    'total_heures' => 0,
    'nb_justifiees' => 0,
    'heures_justifiees' => 0,
    'nb_injustifiees' => 0,
    'heures_injustifiees' => 0
];
$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre des Absences<?= !empty($etablissement['sigle_etablissement']) ? ' - ' . htmlspecialchars($etablissement['sigle_etablissement']) : '' ?></title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 10mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9.5pt;
      color: #1E293B;
      line-height: 1.28;
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
      font-size: 12.5pt;
      font-weight: bold;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 8.5pt;
      color: #E2E8F0;
      margin-top: 2px;
    }

    /* Cartouche Métadonnées */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 7px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 4.5px 8px;
      font-size: 9pt;
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

    /* KPIs */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .kpi-cell {
      border: 0.5px solid #CBD5E1;
      background-color: #FFFFFF;
      padding: 5px 8px;
      text-align: center;
    }
    .kpi-title {
      font-size: 7.5pt;
      font-weight: bold;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .kpi-val {
      font-size: 11pt;
      font-weight: bold;
      margin-top: 2px;
    }

    /* Table Principale */
    .registre-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .registre-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 8.5pt;
      text-transform: uppercase;
      padding: 5.5px 7px;
      border: 0.5px solid #0F2744;
      text-align: left;
    }
    .registre-table td {
      padding: 5px 7px;
      border: 0.5px solid #CBD5E1;
      font-size: 8.5pt;
      vertical-align: middle;
    }
    .row-even {
      background-color: #F8FAFC;
    }
    .row-odd {
      background-color: #FFFFFF;
    }
    .text-center {
      text-align: center;
    }

    /* Badges */
    .badge-semestre {
      color: #1E3A5F;
      font-weight: bold;
      font-size: 8pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 6px;
      border-radius: 3px;
    }
    .badge-justifie {
      color: #15803D;
      font-weight: bold;
      font-size: 8pt;
      background-color: #DCFCE7;
      border: 0.5px solid #86EFAC;
      padding: 1.5px 6px;
      border-radius: 3px;
    }
    .badge-injustifie {
      color: #B91C1C;
      font-weight: bold;
      font-size: 8pt;
      background-color: #FEE2E2;
      border: 0.5px solid #FCA5A5;
      padding: 1.5px 6px;
      border-radius: 3px;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 8pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['nom_etablissement'] ?? ($etablissement['sigle_etablissement'] ?? '')) ?> &bull; Registre des Absences &bull; <?= htmlspecialchars($annee_libelle ?? '') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 8pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          <?= htmlspecialchars(!empty($etablissement['texte_pied_page']) ? $etablissement['texte_pied_page'] : trim('Document officiel ' . ($etablissement['sigle_etablissement'] ?? ''))) ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          REF : <?= date('YmdHis') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- ENTÊTE INSTITUTIONNEL -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE OFFICIEL DES ABSENCES</div>
        <div class="banner-sub">État récapitulatif de l'assiduité et du suivi des absences par classe et semestre</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span>
        <span class="meta-value"><?= htmlspecialchars($annee_libelle ?? '-') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Semestre :</span>
        <span class="meta-value"><?= htmlspecialchars($filtres['semestre'] ?? 'Tous') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Niveau :</span>
        <span class="meta-value"><?= htmlspecialchars($filtres['niveau'] ?? 'Tous') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Classe :</span>
        <span class="meta-value"><?= htmlspecialchars($filtres['classe'] ?? 'Toutes') ?></span>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="meta-label">Période :</span>
        <span class="meta-value"><?= htmlspecialchars($filtres['periode'] ?? 'Toute la période') ?></span>
      </td>
      <td>
        <span class="meta-label">Édité par :</span>
        <span class="meta-value"><?= htmlspecialchars(!empty($editeur_nom) ? $editeur_nom : '-') ?></span>
      </td>
      <td>
        <span class="meta-label">Date d'édition :</span>
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
    </tr>
  </table>

  <!-- SYNTHÈSE KPIs -->
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Total Absences</div>
        <div class="kpi-val" style="color: #0F172A;"><?= (int)($kpis['total_absences'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Volume Horaire Total</div>
        <div class="kpi-val" style="color: #1E3A5F;"><?= $fmtHeures($kpis['total_heures'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Justifiées</div>
        <div class="kpi-val" style="color: #15803D;"><?= (int)($kpis['nb_justifiees'] ?? 0) ?> (<?= $fmtHeures($kpis['heures_justifiees'] ?? 0) ?>)</div>
      </td>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Non Justifiées</div>
        <div class="kpi-val" style="color: #B91C1C;"><?= (int)($kpis['nb_injustifiees'] ?? 0) ?> (<?= $fmtHeures($kpis['heures_injustifiees'] ?? 0) ?>)</div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE -->
  <table class="registre-table">
    <thead>
      <tr>
        <th style="width: 4%; text-align: center;">#</th>
        <th style="width: 23%;">Étudiant & Matricule</th>
        <th style="width: 11%;">Classe</th>
        <th style="width: 11%; text-align: center;">Semestre</th>
        <th style="width: 20%;">Matière Manquée</th>
        <th style="width: 10%; text-align: center;">Date</th>
        <th style="width: 9%; text-align: center;">Volume</th>
        <th style="width: 12%; text-align: center;">Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($absences)): ?>
        <tr>
          <td colspan="8" class="text-center" style="padding: 18px; color: #64748B; font-style: italic;">
            Aucune absence enregistrée pour les critères sélectionnés.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($absences as $idx => $ab): ?>
          <?php
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $nom = trim((string)($ab['nom_etudiant'] ?? ''));
            if ($nom === '') {
                $nom = (string)($ab['etudiant_code'] ?? ($ab['inscription_code'] ?? '-'));
            }
            $mat = trim((string)($ab['matricule_etudiant'] ?? ''));
            $isJustifiee = (($ab['justifiee'] ?? 'non') === STATUTS::JUSTIFIE);
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td>
              <strong style="color: #0F172A;"><?= htmlspecialchars($nom) ?></strong>
              <?php if ($mat !== ''): ?>
                <br><span style="font-size: 7.5pt; color: #64748B;"><?= htmlspecialchars($mat) ?></span>
              <?php endif; ?>
            </td>
            <td style="font-weight: bold; color: #1E3A5F;"><?= htmlspecialchars($ab['libelle_classe'] ?? '-') ?></td>
            <td class="text-center">
              <?php if (!empty($ab['libelle_semestre']) || !empty($ab['semestre_code'])): ?>
                <span class="badge-semestre"><?= htmlspecialchars($ab['libelle_semestre'] ?? $ab['semestre_code']) ?></span>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars(!empty($ab['libelle_matiere']) ? $ab['libelle_matiere'] : (!empty($ab['matiere_code']) ? $ab['matiere_code'] : '-')) ?></td>
            <td class="text-center" style="font-weight: bold;">
              <?= !empty($ab['date_absence']) ? date('d/m/Y', strtotime($ab['date_absence'])) : '-' ?>
            </td>
            <td class="text-center" style="font-weight: bold; color: #0F172A;">
              <?= $fmtHeures($ab['duree_heures'] ?? 0) ?>
            </td>
            <td class="text-center">
              <?php if ($isJustifiee): ?>
                <span class="badge-justifie">Justifiée</span>
              <?php else: ?>
                <span class="badge-injustifie">Non justifiée</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURE OFFICIELLE -->
  <?php
    $sigDirPedTitre = trim((string)($etablissement['titre_direction_pedagogique'] ?? ''));
    $sigDirPedNom = trim((string)($etablissement['nom_directeur_etudes'] ?? ''));
    $sigMention = trim((string)($etablissement['mention_cachet_signature'] ?? ''));
    $sigVille = !empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : ($etablissement['ville_etablissement'] ?? '');
  ?>
  <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
    <tr>
      <td style="width: 60%;"></td>
      <td style="width: 40%; vertical-align: top; padding: 0;">
        <table style="width: 100%; border-collapse: collapse;">
          <?php if (!empty($sigVille)): ?>
          <tr>
            <td style="text-align: center; font-size: 9pt; color: #475569; padding: 0 8px 3px 8px;">
              Fait à <?= htmlspecialchars($sigVille) ?>, le <?= date('d/m/Y') ?>
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td style="text-align: center; font-size: 9.5pt; font-weight: bold; color: #1E3A5F; text-transform: uppercase;">
              <?= htmlspecialchars($sigDirPedTitre) ?>
            </td>
          </tr>
          <tr>
            <td height="44" style="height: 44px; text-align: center; vertical-align: middle;">&nbsp;</td>
          </tr>
          <?php if ($sigDirPedNom !== ''): ?>
          <tr>
            <td style="text-align: center; font-size: 10pt; font-weight: bold; color: #0F172A;">
              <?= htmlspecialchars($sigDirPedNom) ?>
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td style="text-align: center; font-size: 8.5pt; font-style: italic; color: #64748B; padding: 0 8px 4px 8px;">
              <?= htmlspecialchars($sigMention) ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

</body>
</html>
