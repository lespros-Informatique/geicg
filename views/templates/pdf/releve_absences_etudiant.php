<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}
$etudiant = $etudiant ?? [];
$historique = $historique ?? [];
$stats = $stats ?? [
    'annuel_count' => count($historique),
    'annuel_heures' => 0,
    'annuel_justifiees_count' => 0,
    'annuel_justifiees_heures' => 0,
    'annuel_injustifiees_count' => 0,
    'annuel_injustifiees_heures' => 0
];

$nomComplet = trim((string)($etudiant['nom_etudiant'] ?? ''));
if ($nomComplet === '') {
    $nomComplet = trim((string)($etudiant['etu_nom'] ?? '') . ' ' . (string)($etudiant['etu_prenom'] ?? ''));
}
if ($nomComplet === '') {
    $nomComplet = (string)($etudiant['code_etudiant'] ?? ($etudiant['inscription_code'] ?? '-'));
}

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};

// Résolution de la photo de l'étudiant (prise de vue inscription prioritaire, sinon photo profil, sinon placeholder)
$resolvedStudentPhoto = '';
$photoCandidatesRaw = array_filter([
    trim((string)($etudiant['photo_inscription'] ?? '')),
    trim((string)($etudiant['photo_etudiant'] ?? ''))
]);
foreach ($photoCandidatesRaw as $rel) {
    $cleanRel = ltrim($rel, '/\\');
    $pathsToTry = [
        __DIR__ . '/../../../public/' . $cleanRel,
        __DIR__ . '/../../../' . $cleanRel,
        __DIR__ . '/../../../public/uploads/etudiants/' . $cleanRel,
        __DIR__ . '/../../../public/uploads/photos/' . $cleanRel
    ];
    foreach ($pathsToTry as $p) {
        if (file_exists($p) && is_file($p)) {
            $resolvedStudentPhoto = $p;
            break 2;
        }
    }
}
if ($resolvedStudentPhoto === '') {
    $phPath = function_exists('placeholderPath')
        ? placeholderPath('etudiant')
        : (__DIR__ . '/../../../public/assets/images/placeholders/etudiant.png');
    if (file_exists($phPath) && is_file($phPath)) {
        $resolvedStudentPhoto = $phPath;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Historique des Absences - <?= htmlspecialchars($nomComplet) ?><?= !empty($etablissement['sigle_etablissement']) ? ' - ' . htmlspecialchars($etablissement['sigle_etablissement']) : '' ?></title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 10mm;
      margin-left: 10mm;
      margin-right: 10mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9.5pt;
      color: #1E293B;
      line-height: 1.3;
    }

    /* Bannière Titre */
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 7px;
      background-color: #1E3A5F;
    }
    .banner-table td {
      padding: 6px 10px;
      text-align: center;
      color: #FFFFFF;
    }
    .banner-title {
      font-size: 12pt;
      font-weight: bold;
      letter-spacing: 0.7px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 8.5pt;
      color: #E2E8F0;
      margin-top: 2px;
    }

    /* Cartouche Identité Étudiant */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 5px 8px;
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
      margin-bottom: 9px;
    }
    .kpi-cell {
      border: 0.5px solid #CBD5E1;
      background-color: #FFFFFF;
      padding: 6px 8px;
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
      font-size: 10.5pt;
      font-weight: bold;
      margin-top: 2px;
    }

    /* Table Historique */
    .hist-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .hist-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 8.5pt;
      text-transform: uppercase;
      padding: 5.5px 7px;
      border: 0.5px solid #0F2744;
      text-align: left;
    }
    .hist-table td {
      padding: 5.5px 7px;
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

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 8pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 42%; text-align: left;">
          <?= htmlspecialchars(!empty($etablissement['texte_pied_page']) ? $etablissement['texte_pied_page'] : trim('Document officiel ' . ($etablissement['sigle_etablissement'] ?? ''))) ?>
        </td>
        <td style="width: 16%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 42%; text-align: right;">
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
        <div class="banner-title">RELEVÉ INDIVIDUEL DES ABSENCES</div>
        <div class="banner-sub">Historique annuel d'assiduité de l'étudiant &bull; Année Académique <?= htmlspecialchars($annee_libelle ?? '-') ?></div>
      </td>
    </tr>
  </table>

  <!-- IDENTITÉ & CURSUS DE L'ÉTUDIANT + PHOTO -->
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
    <tr>
      <td style="width: 83%; vertical-align: top; padding: 0 8px 0 0;">
        <table class="meta-box-table" style="margin-bottom: 0;">
          <tr>
            <td style="width: 55%;">
              <span class="meta-label">Étudiant :</span>
              <span class="meta-value"><?= htmlspecialchars($nomComplet) ?></span>
            </td>
            <td style="width: 45%;">
              <span class="meta-label">Matricule :</span>
              <span class="meta-value"><?= htmlspecialchars($etudiant['matricule_etudiant'] ?? '-') ?></span>
            </td>
          </tr>
          <tr>
            <td>
              <span class="meta-label">Classe :</span>
              <span class="meta-value"><?= htmlspecialchars($etudiant['libelle_classe'] ?? '-') ?></span>
              <?php if (!empty($etudiant['libelle_niveau'])): ?>
                <span style="color: #475569;">(<?= htmlspecialchars($etudiant['libelle_niveau']) ?>)</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="meta-label">Filière :</span>
              <span class="meta-value"><?= htmlspecialchars($etudiant['libelle_filiere'] ?? '-') ?></span>
            </td>
          </tr>
          <tr>
            <td>
              <span class="meta-label">Contact :</span>
              <span class="meta-value"><?= htmlspecialchars(!empty($etudiant['telephone_etudiant']) ? $etudiant['telephone_etudiant'] : '-') ?></span>
            </td>
            <td>
              <span class="meta-label">Édité par :</span>
              <span class="meta-value"><?= htmlspecialchars(!empty($editeur_nom) ? $editeur_nom : '-') ?></span>
              <span style="color: #64748B; font-size: 8pt;">(<?= date('d/m/Y H:i') ?>)</span>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 17%; vertical-align: middle; text-align: center; padding: 0;">
        <table style="width: 100%; border-collapse: collapse;">
          <tr>
            <td style="text-align: center; vertical-align: middle; padding: 3px;">
              <?php if ($resolvedStudentPhoto !== ''): ?>
                <img src="<?= htmlspecialchars($resolvedStudentPhoto) ?>" style="width: 120px; height: 130px; object-fit: cover; display: block; margin: 0 auto;">
              <?php else: ?>
                <div style="width: 68px; height: 78px; line-height: 78px; text-align: center; color: #94A3B8; font-size: 7pt;">PHOTO</div>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- SYNTHÈSE ASSIDUITÉ -->
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Séances Manquées</div>
        <div class="kpi-val" style="color: #0F172A;"><?= (int)($stats['annuel_count'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Volume Total</div>
        <div class="kpi-val" style="color: #1E3A5F;"><?= $fmtHeures($stats['annuel_heures'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Justifiées</div>
        <div class="kpi-val" style="color: #15803D;"><?= (int)($stats['annuel_justifiees_count'] ?? 0) ?> (<?= $fmtHeures($stats['annuel_justifiees_heures'] ?? 0) ?>)</div>
      </td>
      <td class="kpi-cell" style="width: 25%;">
        <div class="kpi-title">Non Justifiées</div>
        <div class="kpi-val" style="color: #B91C1C;"><?= (int)($stats['annuel_injustifiees_count'] ?? 0) ?> (<?= $fmtHeures($stats['annuel_injustifiees_heures'] ?? 0) ?>)</div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU HISTORIQUE DES ABSENCES -->
  <table class="hist-table">
    <thead>
      <tr>
        <th style="width: 5%; text-align: center;">#</th>
        <th style="width: 13%; text-align: center;">Date</th>
        <th style="width: 14%; text-align: center;">Semestre</th>
        <th style="width: 27%;">Matière Manquée</th>
        <th style="width: 10%; text-align: center;">Volume</th>
        <th style="width: 14%; text-align: center;">Statut</th>
        <th style="width: 17%;">Motif / Obs.</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historique)): ?>
        <tr>
          <td colspan="7" class="text-center" style="padding: 18px; color: #64748B; font-style: italic;">
            Aucune absence enregistrée pour cet étudiant sur cette année académique.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($historique as $idx => $h): ?>
          <?php
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $isJust = (($h['justifiee'] ?? 'non') === STATUTS::JUSTIFIE);
            $hMotif = trim((string)($h['motif_absence'] ?? ($h['motif'] ?? '')));
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td class="text-center" style="font-weight: bold; color: #0F172A;">
              <?= !empty($h['date_absence']) ? date('d/m/Y', strtotime($h['date_absence'])) : '-' ?>
            </td>
            <td class="text-center">
              <?php if (!empty($h['libelle_semestre']) || !empty($h['semestre_code'])): ?>
                <span class="badge-semestre"><?= htmlspecialchars($h['libelle_semestre'] ?? $h['semestre_code']) ?></span>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td style="font-weight: bold; color: #1E293B;">
              <?= htmlspecialchars(!empty($h['libelle_matiere']) ? $h['libelle_matiere'] : (!empty($h['matiere_code']) ? $h['matiere_code'] : '-')) ?>
            </td>
            <td class="text-center" style="font-weight: bold; color: #0F172A;">
              <?= $fmtHeures($h['duree_heures'] ?? 0) ?>
            </td>
            <td class="text-center">
              <?php if ($isJust): ?>
                <span class="badge-justifie">Justifiée</span>
              <?php else: ?>
                <span class="badge-injustifie">Non justifiée</span>
              <?php endif; ?>
            </td>
            <td style="color: #475569; font-size: 8pt;">
              <?= $hMotif !== '' ? htmlspecialchars($hMotif) : '-' ?>
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
  <table style="width: 100%; border-collapse: collapse; margin-top: 22px;">
    <tr>
      <td style="width: 52%;"></td>
      <td style="width: 48%; vertical-align: top; padding: 0;">
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
            <td height="46" style="height: 46px; text-align: center; vertical-align: middle;">&nbsp;</td>
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
