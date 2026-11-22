<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre des Évaluations<?= !empty($etablissement['sigle_etablissement']) ? ' - ' . htmlspecialchars($etablissement['sigle_etablissement']) : '' ?></title>
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
      color: #1E293B;
      line-height: 1.25;
    }

    /* En-tête */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .header-table td {
      vertical-align: middle;
    }
    .inst-title {
      font-size: 11.5pt;
      font-weight: bold;
      color: #990000;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.15;
    }
    .inst-subtitle {
      font-size: 8.5pt;
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
      margin-bottom: 5px;
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

    /* Cartouche Métadonnées */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 3.5px 6px;
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

    /* KPIs Box */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .kpi-cell {
      border: 1px solid #CBD5E1;
      background-color: #FFFFFF;
      padding: 4px 8px;
      text-align: center;
    }
    .kpi-title {
      font-size: 7pt;
      font-weight: bold;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .kpi-val {
      font-size: 10.5pt;
      font-weight: bold;
      margin-top: 1px;
    }

    /* Table Principale */
    .repertoire-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .repertoire-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 7.5pt;
      text-transform: uppercase;
      padding: 4px 6px;
      border: 0.5px solid #0F2744;
      text-align: left;
    }
    .repertoire-table td {
      padding: 3.5px 6px;
      border: 0.5px solid #CBD5E1;
      font-size: 7.5pt;
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
    .text-right {
      text-align: right;
    }

    /* Badges */
    .badge-eval {
      color: #1D4ED8;
      font-weight: bold;
      font-size: 7pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 5px;
      border-radius: 3px;
    }
    .badge-devoir {
      color: #B45309;
      font-weight: bold;
      font-size: 7pt;
      background-color: #FEF3C7;
      border: 0.5px solid #FDE68A;
      padding: 1.5px 5px;
      border-radius: 3px;
    }
    .badge-interro {
      color: #15803D;
      font-weight: bold;
      font-size: 7pt;
      background-color: #DCFCE7;
      border: 0.5px solid #86EFAC;
      padding: 1.5px 5px;
      border-radius: 3px;
    }

    /* Bloc de Signature */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }
    .signature-box {
      width: 34%;
      vertical-align: top;
      background-color: #FAFAFA;
      padding: 8px 12px;
      text-align: center;
    }
    .signature-date {
      font-size: 7.5pt;
      color: #475569;
      text-align: center;
      margin-bottom: 5px;
    }
    .signature-title {
      font-size: 10pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      border-bottom: 0.5px solid #CBD5E1;
      padding-bottom: 4px;
      margin-bottom: 0;
    }
    .signature-space {
      height: 38px;
    }
    .signature-name {
      font-size: 8.5pt;
      font-weight: bold;
      color: #0F172A;
      text-align: center;
      margin-bottom: 2px;
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

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['nom_etablissement'] ?? ($etablissement['sigle_etablissement'] ?? '')) ?> &bull; Registre des Évaluations & Sessions de Notes &bull; <?= htmlspecialchars($annee_libelle ?? '') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
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

  <!-- ENTÊTE DE L'ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title"><?= !empty($isTeacherOnly) ? 'REGISTRE DES ÉVALUATIONS - MES CLASSES ET MATIÈRES' : 'REGISTRE GÉNÉRAL DES ÉVALUATIONS & SESSIONS DE NOTES' ?></div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['annee'] ?? '-') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Semestre :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['semestre'] ?? 'Tous') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Niveau d'Études :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['niveau'] ?? 'Tous') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Classe :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['classe'] ?? 'Toutes') ?></span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="meta-label">Matière :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['matiere'] ?? 'Toutes') ?></span>
      </td>
      <td>
        <span class="meta-label">Type d'Épreuve :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['type'] ?? 'Tous') ?></span>
      </td>
      <td>
        <span class="meta-label">Édité par :</span><br>
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? '') ?></span>
      </td>
      <td>
        <span class="meta-label">Date d'édition :</span><br>
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE SYNTHÈSE / KPIS -->
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Total Sessions</div>
        <div class="kpi-val" style="color: #0F172A;"><?= (int)($kpis['total_sessions'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Total Inscrits</div>
        <div class="kpi-val" style="color: #4338CA;"><?= (int)($kpis['total_inscrits'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Copies Saisies</div>
        <div class="kpi-val" style="color: #1D4ED8;"><?= (int)($kpis['total_copies'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Taux de Complétion</div>
        <div class="kpi-val" style="color: #059669;"><?= number_format($kpis['taux_completion'] ?? 0, 1) ?> %</div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Moyenne Générale</div>
        <div class="kpi-val" style="color: <?= (($kpis['moyenne_globale'] ?? 0) >= 10) ? '#059669' : '#DC2626' ?>;">
          <?= ($kpis['moyenne_globale'] !== null) ? number_format($kpis['moyenne_globale'], 2) . ' / 20' : '-' ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE -->
  <table class="repertoire-table">
    <thead>
      <tr>
        <th style="width: 24px;" class="text-center">#</th>
        <th style="width: 80px;">Classe</th>
        <th>Évaluation</th>
        <th style="width: 55px;" class="text-center">Type</th>
        <th>Matière</th>
        <th style="width: 70px;">Semestre</th>
        <th style="width: 38px;" class="text-center">Coef</th>
        <th style="width: 65px;" class="text-center">Date Enreg.</th>
        <th style="width: 85px;" class="text-center">Progression</th>
        <th style="width: 70px;" class="text-center">Moyenne</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($sessions)): ?>
        <tr>
          <td colspan="10" class="text-center" style="padding: 18px; color: #64748B; font-style: italic;">
            Aucune session d'évaluation ne correspond aux critères de sélection.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($sessions as $idx => $s): ?>
          <?php
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $typ = strtoupper($s['type_composition'] ?? 'EXAMEN');
            $badgeClass = 'badge-eval';
            if ($typ === 'DEVOIR') $badgeClass = 'badge-devoir';
            elseif ($typ === 'INTERROGATION') $badgeClass = 'badge-interro';

            // Date format
            $rawDate = $s['date_enregistrement'] ?? ($s['date_enregistrement_note'] ?? ($s['date_composition'] ?? ($s['created_at_composition'] ?? '')));
            $formattedDate = '-';
            if (!empty($rawDate) && $rawDate !== '0000-00-00' && $rawDate !== '0000-00-00 00:00:00') {
                $clean = explode(' ', $rawDate)[0];
                $parts = explode('-', $clean);
                if (count($parts) === 3) {
                    $formattedDate = $parts[2] . '/' . $parts[1] . '/' . $parts[0];
                }
            }

            // Progression
            $nb = (int)($s['nb_notes'] ?? 0);
            $tot = (int)($s['total_etudiants'] ?? 0);
            $progText = $tot > 0 ? "{$nb} / {$tot}" : "{$nb}";

            // Moyenne
            $moyText = '-';
            $moyColor = '#64748B';
            if ($s['moyenne_classe'] !== null && $s['moyenne_classe'] !== '') {
                $moyVal = (float)$s['moyenne_classe'];
                $moyText = number_format($moyVal, 2) . ' / 20';
                $moyColor = $moyVal >= 10 ? '#15803D' : '#DC2626';
            }
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td><strong><?= htmlspecialchars($s['libelle_classe'] ?? $s['code_classe'] ?? '-') ?></strong></td>
            <td><strong><?= htmlspecialchars($s['libelle_composition'] ?? $s['code_composition'] ?? '-') ?></strong></td>
            <td class="text-center"><span class="<?= $badgeClass ?>"><?= htmlspecialchars($typ) ?></span></td>
            <td><?= htmlspecialchars($s['libelle_matiere'] ?? '-') ?></td>
            <td><?= htmlspecialchars($s['libelle_semestre'] ?? '-') ?></td>
            <td class="text-center" style="font-weight: bold;"><?= number_format((float)($s['coefficient'] ?? 1), 2) ?></td>
            <td class="text-center" style="font-weight: bold; color: #1E3A5F;"><?= $formattedDate ?></td>
            <td class="text-center" style="font-weight: bold;"><?= $progText ?></td>
            <td class="text-center" style="font-weight: bold; color: <?= $moyColor ?>;"><?= $moyText ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURE -->
  <table class="signature-table" style="width: 100%; border-collapse: collapse; margin-top: 14px;">
    <tr>
      <td style="width: 64%;"></td>
      <td style="width: 36%; vertical-align: top; padding: 0;">
        <table style="width: 100%; border-collapse: collapse; border: 0.5px solid #CBD5E1; background-color: #FAFAFA;">
          <?php if (!empty($etablissement['ville_signature']) || !empty($etablissement['ville_etablissement'])): ?>
          <tr>
            <td style="text-align: center; font-size: 7.5pt; color: #475569; padding: 5px 8px 2px 8px;">
              Fait à <?= htmlspecialchars($etablissement['ville_signature'] ?: $etablissement['ville_etablissement']) ?>, le <?= date('d/m/Y') ?>
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td style="text-align: center; font-size: 8pt; font-weight: bold; color: #1E3A5F; text-transform: uppercase; border-bottom: 0.5px solid #CBD5E1; padding: 4px 8px 5px 8px;">
              <?= htmlspecialchars($etablissement['titre_direction_pedagogique'] ?? '') ?>
            </td>
          </tr>
          <tr>
            <td height="44" style="height: 44px; text-align: center; vertical-align: middle;">&nbsp;</td>
          </tr>
          <?php if (!empty($etablissement['nom_directeur_etudes'])): ?>
          <tr>
            <td style="text-align: center; font-size: 8.5pt; font-weight: bold; color: #0F172A; padding: 0 8px 2px 8px;">
              <?= htmlspecialchars($etablissement['nom_directeur_etudes']) ?>
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td style="text-align: center; font-size: 7pt; font-style: italic; color: #64748B; padding: 0 8px 6px 8px;">
              <?= htmlspecialchars($etablissement['mention_cachet_signature'] ?? '') ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

</body>
</html>

