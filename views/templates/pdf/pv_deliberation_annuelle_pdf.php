<?php
$classeInfo = $classeInfo ?? [];
$rows = $rows ?? [];
$summary = $summary ?? [];
$anneeLibelle = $anneeLibelle ?? '-';
$estPremiereAnnee = !empty($est_premiere_annee);

if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } elseif (file_exists(__DIR__ . '/../../../models/configurations/ModelConfiguration.php')) {
        require_once __DIR__ . '/../../../models/configurations/ModelConfiguration.php';
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}

$totalEffectif = count($rows);
$nbAdmisOffice = 0;
$nbAdmisGrace = 0;
$nbRedouble = 0;
$nbExclu = 0;
$nbAttente = 0;
$sommeMoy = 0.0;
$nbMoy = 0;

foreach ($rows as $r) {
    $c = $r['decision_choice'] ?? 'en_attente';
    if ($c === 'admis_office') $nbAdmisOffice++;
    elseif ($c === 'admis_grace') $nbAdmisGrace++;
    elseif ($c === 'redouble') $nbRedouble++;
    elseif ($c === 'exclu') $nbExclu++;
    else $nbAttente++;

    if (!empty($r['has_notes']) && $r['moyenne_annuelle'] !== null) {
        $sommeMoy += (float)$r['moyenne_annuelle'];
        $nbMoy++;
    }
}
$totalAdmis = $nbAdmisOffice + $nbAdmisGrace;
$moyClasse = $nbMoy > 0 ? round($sommeMoy / $nbMoy, 2) : 0.0;
$tauxAdmis = $totalEffectif > 0 ? round(($totalAdmis / $totalEffectif) * 100, 1) : 0.0;
$isFrozen = !empty($summary['is_delibere']);
$dateDelib = !empty($summary['last_date_deliberation']) ? date('d/m/Y à H:i', strtotime($summary['last_date_deliberation'])) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Procès-Verbal de Délibération Annuelle<?= !empty($etablissement['sigle_etablissement']) ? (' - ' . htmlspecialchars($etablissement['sigle_etablissement'])) : '' ?></title>
  <style>
    @page {
      margin: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8pt;
      color: #0F172A;
      line-height: 1.25;
    }
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
      font-size: 11.5pt;
      font-weight: bold;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 7.5pt;
      color: #E2E8F0;
      margin-top: 2px;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      background-color: #F8FAFC;
      border: 1px solid #CBD5E1;
    }
    .meta-table td {
      padding: 4px 6px;
      font-size: 7.5pt;
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
    .delib-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .delib-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 7.2pt;
      padding: 5px 4px;
      border: 0.5px solid #0F172A;
      text-align: center;
    }
    .delib-table td {
      font-size: 7.5pt;
      padding: 4px 4px;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
    }
    .delib-table tr:nth-child(even) td {
      background-color: #F8FAFC;
    }
    .cell-admis {
      background-color: #DCFCE7 !important;
      color: #166534 !important;
      font-weight: bold;
      text-align: center;
    }
    .cell-grace {
      background-color: #FEF3C7 !important;
      color: #92400E !important;
      font-weight: bold;
      text-align: center;
    }
    .cell-redouble {
      background-color: #FEE2E2 !important;
      color: #991B1B !important;
      font-weight: bold;
      text-align: center;
    }
    .cell-exclu {
      background-color: #E2E8F0 !important;
      color: #0F172A !important;
      font-weight: bold;
      text-align: center;
    }
    .cell-attente {
      background-color: #F1F5F9 !important;
      color: #475569 !important;
      font-weight: bold;
      text-align: center;
    }
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .signature-table td {
      width: 33.33%;
      text-align: center;
      vertical-align: top;
      font-size: 7.5pt;
      padding: 4px 10px;
    }
    .sig-title {
      font-weight: bold;
      color: #1E3A5F;
      border-bottom: 1px dashed #94A3B8;
      padding-bottom: 2px;
      margin-bottom: 28px;
    }
    .sig-cachet {
      font-size: 6.5pt;
      color: #94A3B8;
      font-style: italic;
    }
    .footer-text {
      text-align: center;
      font-size: 6.5pt;
      color: #64748B;
      border-top: 0.5px solid #E2E8F0;
      padding-top: 3px;
      margin-top: 8px;
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">PROCÈS-VERBAL DE DÉLIBÉRATION ANNUELLE DU CONSEIL DE CLASSE</div>
        <div class="banner-sub">
          Année Académique <?= htmlspecialchars($anneeLibelle) ?> &bull;
          Statut : <?= $isFrozen ? ('OFFICIEL / VALIDÉ' . ($dateDelib !== '' ? (' LE ' . $dateDelib) : '')) : 'PROVISOIRE (NON CLÔTURÉ)' ?>
        </div>
      </td>
    </tr>
  </table>

  <table class="meta-table">
    <tr>
      <td style="width: 22%;"><span class="meta-label">Classe :</span> <span class="meta-value"><?= htmlspecialchars($classeInfo['libelle_classe'] ?? '-') ?></span></td>
      <td style="width: 24%;"><span class="meta-label">Filière :</span> <span class="meta-value"><?= htmlspecialchars($classeInfo['libelle_filiere'] ?? '-') ?></span></td>
      <td style="width: 18%;"><span class="meta-label">Niveau :</span> <span class="meta-value"><?= htmlspecialchars($classeInfo['libelle_niveau'] ?? '-') ?></span></td>
      <td style="width: 18%;"><span class="meta-label">Effectif :</span> <span class="meta-value"><?= $totalEffectif ?> étudiant(s)</span></td>
      <td style="width: 18%;"><span class="meta-label">Taux d'Admission :</span> <span class="meta-value" style="color: #15803D;"><?= number_format($tauxAdmis, 1) ?> %</span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Admis d'office :</span> <span class="meta-value" style="color: #15803D;"><?= $nbAdmisOffice ?></span></td>
      <td><span class="meta-label">Admis par grâce (Rachat) :</span> <span class="meta-value" style="color: #B45309;"><?= $nbAdmisGrace ?></span></td>
      <td><span class="meta-label">Redoublement :</span> <span class="meta-value" style="color: #B91C1C;"><?= $nbRedouble ?></span></td>
      <td><span class="meta-label">Exclus / Attente :</span> <span class="meta-value"><?= $nbExclu ?> / <?= $nbAttente ?></span></td>
      <td><span class="meta-label">Moyenne Classe :</span> <span class="meta-value"><?= number_format($moyClasse, 2) ?> / 20</span></td>
    </tr>
  </table>

  <table class="delib-table">
    <thead>
      <tr>
        <th style="width: 30px;">Rang</th>
        <th style="width: 78px;">Matricule</th>
        <th style="text-align: left; width: 155px;">Nom &amp; Prénom(s)</th>
        <th style="width: 44px;">Moy. S1</th>
        <th style="width: 44px;">Moy. S2</th>
        <?php if ($estPremiereAnnee): ?>
          <th style="width: 46px;">Examen</th>
        <?php endif; ?>
        <th style="width: 52px;">Moy. Ann.</th>
        <th style="width: 48px;">Absences</th>
        <th style="width: 68px;">Mention</th>
        <th style="width: 118px;">Décision du Conseil</th>
        <th style="text-align: left;">Observation / Motif du Jury</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="<?= $estPremiereAnnee ? 11 : 10 ?>" style="text-align: center; padding: 14px; color: #64748B;">
            Aucun étudiant inscrit dans cette classe.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r):
          $c = $r['decision_choice'] ?? 'en_attente';
          $cellClass = 'cell-attente';
          if ($c === 'admis_office') $cellClass = 'cell-admis';
          elseif ($c === 'admis_grace') $cellClass = 'cell-grace';
          elseif ($c === 'redouble') $cellClass = 'cell-redouble';
          elseif ($c === 'exclu') $cellClass = 'cell-exclu';
          $moyAnn = $r['moyenne_annuelle'];
          $hasNotes = !empty($r['has_notes']) && $moyAnn !== null;
        ?>
          <tr>
            <td style="text-align: center; font-weight: bold; color: #1E3A5F;">
              <?php if ($r['rang_annuel'] !== null): ?>
                <?= (int)$r['rang_annuel'] ?><sup><?= ((int)$r['rang_annuel'] === 1) ? 'er' : 'e' ?></sup><?= !empty($r['ex_aequo']) ? ' <span style="font-size:6pt;">ex</span>' : '' ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td style="font-family: monospace; font-weight: bold; color: #334155;"><?= htmlspecialchars($r['matricule'] ?: '-') ?></td>
            <td style="font-weight: bold; color: #0F172A;"><?= htmlspecialchars($r['nom_prenom']) ?></td>
            <td style="text-align: center;"><?= $r['moyenne_s1'] !== null ? number_format((float)$r['moyenne_s1'], 2) : '-' ?></td>
            <td style="text-align: center;"><?= $r['moyenne_s2'] !== null ? number_format((float)$r['moyenne_s2'], 2) : '-' ?></td>
            <?php if ($estPremiereAnnee): ?>
              <td style="text-align: center;"><?= $r['moyenne_examen'] !== null ? number_format((float)$r['moyenne_examen'], 2) : '-' ?></td>
            <?php endif; ?>
            <td style="text-align: center; font-weight: bold; color: <?= $hasNotes ? ($moyAnn >= 10 ? '#15803D' : '#991B1B') : '#64748B' ?>;">
              <?= $hasNotes ? number_format((float)$moyAnn, 2) : '-' ?>
            </td>
            <td style="text-align: center;">
              <?= (float)$r['absences_total'] > 0 ? (rtrim(rtrim(number_format((float)$r['absences_total'], 2, '.', ''), '0'), '.') . ' h') : '0 h' ?>
            </td>
            <td style="text-align: center;"><?= htmlspecialchars($r['mention_finale'] ?: '-') ?></td>
            <td class="<?= $cellClass ?>"><?= htmlspecialchars($r['libelle_decision'] ?: '-') ?></td>
            <td><?= $r['observation_jury'] !== '' ? htmlspecialchars($r['observation_jury']) : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <table class="signature-table">
    <tr>
      <td>
        <div class="sig-title">Le Rapporteur / Chef de Département</div>
        <div style="height: 36px;"></div>
        <div class="sig-cachet"><?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? '') ?></div>
      </td>
      <td>
        <div class="sig-title"><?= htmlspecialchars($etablissement['titre_direction_pedagogique'] ?? '') ?></div>
        <?php if (!empty($etablissement['nom_directeur_etudes'])): ?>
          <div style="font-weight: bold; font-size: 7.5pt; color: #1E293B; margin-top: 4px;"><?= htmlspecialchars($etablissement['nom_directeur_etudes']) ?></div>
          <div style="height: 18px;"></div>
        <?php else: ?>
          <div style="height: 36px;"></div>
        <?php endif; ?>
        <div class="sig-cachet"><?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? '') ?></div>
      </td>
      <td>
        <div class="sig-title"><?= htmlspecialchars($etablissement['titre_direction_generale'] ?? '') ?></div>
        <?php if (!empty($etablissement['nom_directeur_general'])): ?>
          <div style="font-weight: bold; font-size: 7.5pt; color: #1E293B; margin-top: 4px;"><?= htmlspecialchars($etablissement['nom_directeur_general']) ?></div>
          <div style="height: 18px;"></div>
        <?php else: ?>
          <div style="height: 36px;"></div>
        <?php endif; ?>
        <div class="sig-cachet"><?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? '') ?></div>
      </td>
    </tr>
  </table>

  <?php
    $footerParts = array_filter([
      $etablissement['nom_etablissement'] ?? '',
      $etablissement['texte_pied_page'] ?? '',
      !empty($etablissement['ville_signature']) ? ('Fait à ' . $etablissement['ville_signature'] . ', le ' . date('d/m/Y')) : ('Le ' . date('d/m/Y'))
    ], fn($p) => trim((string)$p) !== '');
  ?>
  <div class="footer-text">
    <?= htmlspecialchars(implode(' • ', $footerParts)) ?>
  </div>

</body>
</html>
