<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Procès-Verbal de Notes - GROUPE EICG</title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 8mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #0F172A;
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
      font-size: 11pt;
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
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 8pt;
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
      padding: 4px 6px;
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

    /* Summary Indicators Bar */
    .kpi-summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .kpi-summary-table td {
      padding: 4px 6px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      border: 1px solid #CBD5E1;
    }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }
    .kpi-moyenne { background-color: #F0FDF4; color: #15803D; }
    .kpi-reussite { background-color: #FEF3C7; color: #B45309; }
    .kpi-minmax { background-color: #F8FAFC; color: #334155; }

    /* Tableau Nominatif */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .data-table th {
      background-color: #1E293B;
      color: #FFFFFF;
      border: 1px solid #000000;
      padding: 5px 5px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 1px solid #000000;
      padding: 4px 5px;
      font-size: 8pt;
      vertical-align: middle;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .text-bold { font-weight: bold; }

    /* Footer & Signatures */
    .footer-summary {
      width: 100%;
      margin-top: 10px;
      border-top: 1px solid #000000;
      padding-top: 5px;
      font-size: 8pt;
    }
    .signature-grid {
      width: 100%;
      margin-top: 12px;
      font-size: 8.5pt;
    }
    .signature-grid td {
      vertical-align: top;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE PRINCIPALE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">FICHE DE COTATION & PROCÈS-VERBAL DE NOTES</div>
        <div class="banner-sub">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle) ?></div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE METADATA -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 34%;"><span class="meta-label">Classe :</span> <span class="meta-value"><?= htmlspecialchars($info['libelle_classe'] ?? '-') ?></span></td>
      <td style="width: 33%;"><span class="meta-label">Évaluation :</span> <span class="meta-value"><?= htmlspecialchars($info['libelle_composition'] ?? '-') ?> [<?= htmlspecialchars($info['type_composition'] ?? 'EXAMEN') ?>]</span></td>
      <td style="width: 33%;"><span class="meta-label">Semestre :</span> <span class="meta-value"><?= htmlspecialchars($info['libelle_semestre'] ?? '-') ?></span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Matière :</span> <span class="meta-value"><?= htmlspecialchars($info['libelle_matiere'] ?? 'Session globale') ?></span></td>
      <td><span class="meta-label">Coefficient :</span> <span class="meta-value"><?= number_format((float)($info['coefficient'] ?? 1.00), 2, '.', '') ?></span></td>
      <td><span class="meta-label">Filière / Niveau :</span> <span class="meta-value"><?= htmlspecialchars($info['libelle_filiere'] ?? '-') ?> (<?= htmlspecialchars($info['libelle_niveau'] ?? '-') ?>)</span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Effectif Classe :</span> <span class="meta-value"><?= count($students ?? []) ?> Étudiant(s)</span></td>
      <td><span class="meta-label">Édité par :</span> <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Service Examens & Notes') ?></span></td>
      <td><span class="meta-label">Date d'Édition :</span> <span class="meta-value"><?= date('d/m/Y H:i') ?></span></td>
    </tr>
  </table>

  <!-- SUMMARY KPIS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 25%;">COPIES NOTÉES : <?= $stats['nb_notes'] ?? 0 ?> / <?= $stats['total_etudiants'] ?? count($students ?? []) ?></td>
      <td class="kpi-moyenne" style="width: 25%;">MOYENNE : <?= isset($stats['moyenne_classe']) && $stats['moyenne_classe'] !== null ? number_format((float)$stats['moyenne_classe'], 2, '.', '') . ' / 20' : '-' ?></td>
      <td class="kpi-reussite" style="width: 25%;">TAUX DE SUCCÈS (≥10) : <?= ($stats['nb_notes'] ?? 0) > 0 ? ($stats['taux_reussite'] . ' %') : '-' ?></td>
      <td class="kpi-minmax" style="width: 25%;">NOTE MIN / MAX : <?= isset($stats['note_min']) ? number_format((float)$stats['note_min'], 2, '.', '') : '-' ?> / <?= isset($stats['note_max']) ? number_format((float)$stats['note_max'], 2, '.', '') : '-' ?></td>
    </tr>
  </table>

  <!-- TABLEAU DES NOTES -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 14%;">MATRICULE</th>
        <th style="width: 33%; text-align: left;">NOM & PRÉNOM(S)</th>
        <th style="width: 11%;">NOTE / 20</th>
        <th style="width: 11%;">RANG</th>
        <th style="width: 13%;">MENTION</th>
        <th style="width: 14%;">OBSERVATIONS</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($students)): ?>
        <?php foreach ($students as $i => $st): ?>
          <?php 
            $hasNote = ($st['valeur_note'] !== null && $st['valeur_note'] !== '');
            $val = $hasNote ? (float)$st['valeur_note'] : null;
            $noteColor = $hasNote ? ($val >= 10 ? '#15803D' : '#DC2626') : '#64748B';
          ?>
          <tr>
            <td class="text-center text-bold"><?= $i + 1 ?></td>
            <td class="text-center text-bold" style="font-family: monospace; color: #1E3A5F;"><?= htmlspecialchars($st['matricule_etudiant'] ?? '-') ?></td>
            <td class="text-left text-bold"><?= htmlspecialchars(mb_strtoupper($st['nom_etudiant'] ?? '')) ?></td>
            <td class="text-center text-bold" style="font-size: 9pt; color: <?= $noteColor ?>;">
              <?= $hasNote ? number_format($val, 2, '.', '') : '<span style="color:#94A3B8; font-style:italic;">Non noté</span>' ?>
            </td>
            <td class="text-center text-bold" style="color: #1E3A5F; font-size: 8.5pt;">
              <?= $st['rang'] ?? '-' ?>
            </td>
            <td class="text-center">
              <?= htmlspecialchars($st['mention'] ?? '-') ?>
            </td>
            <td class="text-center" style="font-size: 7.5pt; color: #475569;">
              <?= htmlspecialchars($st['observations'] ?? '') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center" style="padding: 20px; color: #64748B; font-style: italic;">
            Aucun étudiant inscrit dans cette classe pour cette évaluation.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <br>
  <!-- BAS DE PAGE ET SIGNATURES -->
  <table class="footer-summary">
    <tr>
      <td style="width: 50%;">
        Bilan : <?= ($stats['nb_notes'] ?? 0) ?> copies évaluées &bull; <?= ($stats['admis_count'] ?? 0) ?> admis (≥10/20) &bull; <?= max(0, ($stats['nb_notes'] ?? 0) - ($stats['admis_count'] ?? 0)) ?> ajournés
      </td>
      <td style="width: 50%; text-align: right;">
        Fait à Bouaké, le <?= date('d/m/Y') ?>
      </td>
    </tr>
  </table>

  <table class="signature-grid">
    <tr>
      <td style="width: 33%;">
        <div style="font-weight: bold; text-decoration: underline;">L'Enseignant de la Matière</div>
      </td>
      <td style="width: 34%; text-align: center;">
        <div style="font-weight: bold; text-decoration: underline;">Le Responsable Pédagogique</div>
      </td>
      <td style="width: 33%; text-align: right;">
        <div style="font-weight: bold; text-decoration: underline;">La Direction des Études & Examens</div>
      </td>
    </tr>
  </table>

</body>
</html>

