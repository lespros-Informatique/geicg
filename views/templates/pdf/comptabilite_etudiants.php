<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>État Comptable des Étudiants - GROUPE EICG</title>
  <style>
    @page {
      margin-top: 6mm;
      margin-bottom: 8mm;
      margin-left: 6mm;
      margin-right: 6mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8pt;
      color: #0F172A;
      line-height: 1.2;
    }

    /* En-tête */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .header-table td {
      vertical-align: middle;
    }
    .inst-title {
      font-size: 11pt;
      font-weight: bold;
      color: #800000;
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
      font-size: 11.5pt;
      font-weight: bold;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .banner-sub {
      font-size: 8pt;
      color: #E2E8F0;
      margin-top: 1px;
    }

    /* Filtres appliqués */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 4px 6px;
      font-size: 7.5pt;
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

    /* Synthèse KPIs */
    .kpi-summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .kpi-summary-table td {
      padding: 5px 6px;
      font-size: 7.5pt;
      font-weight: bold;
      text-align: center;
      border: 1px solid #CBD5E1;
    }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }
    .kpi-scolarite { background-color: #F0F9FF; color: #0369A1; }
    .kpi-frais { background-color: #FAF5FF; color: #7E22CE; }
    .kpi-attendu { background-color: #F0FDF4; color: #15803D; }
    .kpi-encaisse { background-color: #DCFCE7; color: #166534; }
    .kpi-reste { background-color: #FEF2F2; color: #991B1B; }

    /* Tableau des Données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .data-table th {
      background-color: #1E293B;
      color: #FFFFFF;
      border: 1px solid #000000;
      padding: 5px 4px;
      font-size: 7.5pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 1px solid #000000;
      padding: 3.5px 4px;
      font-size: 7.5pt;
      vertical-align: middle;
    }
    .data-table tr.total-row td {
      background-color: #A6A6A6;
      color: #000000;
      font-weight: bold;
      font-size: 8pt;
    }

    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .text-bold { font-weight: bold; }

    .status-solde { color: #15803D; font-weight: bold; }
    .status-partiel { color: #B45309; font-weight: bold; }
    .status-non-paye { color: #B91C1C; font-weight: bold; }
  </style>
</head>
<body>

  <!-- EN-TÊTE ÉTABLISSEMENT -->
  <table class="header-table">
    <tr>
      <td style="width: 12%;">
        <?php if (!empty($logo_src)): ?>
          <img src="<?= $logo_src ?>" style="max-height: 44px; max-width: 95px;">
        <?php else: ?>
          <div style="background: #800000; color: #FFF; font-weight: bold; padding: 4px; text-align: center; font-size: 9pt;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 88%; text-align: center;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">Agréé par l'Etat et le FDFP — Service de la Comptabilité & Direction Financière</div>
        <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99 — Site Web : www.groupe-eicg.net</div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">ÉTAT DE SYNTHÈSE COMPTABLE & SUIVI FINANCIER DES ÉTUDIANTS</div>
        <div class="banner-sub">ANNEE ACADEMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025-2026') ?></div>
      </td>
    </tr>
  </table>

  <!-- CRITÈRES DE FILTRAGE -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 20%;"><span class="meta-label">Niveau d'Études :</span> <span class="meta-value"><?= htmlspecialchars($niveau_libelle ?? 'Tous') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Classe / Filière :</span> <span class="meta-value"><?= htmlspecialchars($classe_libelle ?? 'Toutes') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Régime Étudiant :</span> <span class="meta-value"><?= htmlspecialchars($regime_libelle ?? 'Tous') ?></span></td>
      <td style="width: 30%;"><span class="meta-label">Statut Règlement :</span> <span class="meta-value"><?= htmlspecialchars($statut_libelle ?? 'Tous') ?></span></td>
    </tr>
  </table>

  <!-- SYNTHÈSE COMPTABLE (KPI BAR) -->
  <?php $k = $kpis ?? []; ?>
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 15%;">EFFECTIF<br><span style="font-size: 9pt;"><?= number_format($k['total_etudiants'] ?? 0, 0, ',', ' ') ?> Étudiant(s)</span></td>
      <td class="kpi-scolarite" style="width: 17%;">SCOLARITÉ DUE<br><span style="font-size: 9pt;"><?= number_format($k['scolarite_due_total'] ?? 0, 0, ',', ' ') ?> CFA</span></td>
      <td class="kpi-frais" style="width: 17%;">FRAIS ANNEXES<br><span style="font-size: 9pt;"><?= number_format($k['frais_annexes_due_total'] ?? 0, 0, ',', ' ') ?> CFA</span></td>
      <td class="kpi-attendu" style="width: 17%;">TOTAL ATTENDU<br><span style="font-size: 9pt;"><?= number_format($k['total_attendu'] ?? 0, 0, ',', ' ') ?> CFA</span></td>
      <td class="kpi-encaisse" style="width: 17%;">TOTAL ENCAISSÉ<br><span style="font-size: 9pt;"><?= number_format($k['total_encaisse'] ?? 0, 0, ',', ' ') ?> CFA</span></td>
      <td class="kpi-reste" style="width: 17%;">RESTE À RECOUVRER<br><span style="font-size: 9pt;"><?= number_format($k['reste_a_recouvrer'] ?? 0, 0, ',', ' ') ?> CFA</span></td>
    </tr>
  </table>

  <!-- TABLEAU COMPTABLE DES ÉTUDIANTS -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 3%;">#</th>
        <th style="width: 11%;">Matricule</th>
        <th style="width: 22%;">Nom & Prénom(s)</th>
        <th style="width: 10%;">Classe</th>
        <th style="width: 8%;">Régime</th>
        <th style="width: 9%;">Scolarité Due</th>
        <th style="width: 8%;">Frais Ann.</th>
        <th style="width: 10%;">Total Attendu</th>
        <th style="width: 10%;">Total Encaissé</th>
        <th style="width: 9%;">Reste à Payer</th>
        <th style="width: 8%;">Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($etudiants)): ?>
        <?php $num = 0; foreach ($etudiants as $e): $num++; ?>
          <tr>
            <td class="text-center text-bold"><?= $num ?></td>
            <td class="text-center text-bold"><?= htmlspecialchars($e['matricule']) ?></td>
            <td class="text-left text-bold"><?= htmlspecialchars($e['nom_complet']) ?></td>
            <td class="text-center"><?= htmlspecialchars($e['classe']) ?></td>
            <td class="text-center"><?= htmlspecialchars($e['regime']) ?></td>
            <td class="text-right"><?= number_format($e['scolarite_due'], 0, ',', ' ') ?></td>
            <td class="text-right"><?= number_format($e['frais_annexes_dus'], 0, ',', ' ') ?></td>
            <td class="text-right text-bold"><?= number_format($e['total_attendu'], 0, ',', ' ') ?></td>
            <td class="text-right text-bold" style="color: #166534;"><?= number_format($e['total_encaisse'], 0, ',', ' ') ?></td>
            <td class="text-right text-bold" style="color: <?= $e['solde_restant'] > 0 ? '#B91C1C' : '#15803D' ?>;"><?= number_format($e['solde_restant'], 0, ',', ' ') ?></td>
            <td class="text-center">
              <?php if ($e['statut_code'] === 'solde'): ?>
                <span class="status-solde">Soldé</span>
              <?php elseif ($e['statut_code'] === 'partiel'): ?>
                <span class="status-partiel">Acompte</span>
              <?php else: ?>
                <span class="status-non-paye">Non Réglé</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td colspan="5" class="text-bold text-center">TOTAL GÉNÉRAL (<?= $num ?> ÉTUDIANT(S))</td>
          <td class="text-right"><?= number_format($k['scolarite_due_total'] ?? 0, 0, ',', ' ') ?></td>
          <td class="text-right"><?= number_format($k['frais_annexes_due_total'] ?? 0, 0, ',', ' ') ?></td>
          <td class="text-right"><?= number_format($k['total_attendu'] ?? 0, 0, ',', ' ') ?></td>
          <td class="text-right"><?= number_format($k['total_encaisse'] ?? 0, 0, ',', ' ') ?></td>
          <td class="text-right"><?= number_format($k['reste_a_recouvrer'] ?? 0, 0, ',', ' ') ?></td>
          <td class="text-center">-</td>
        </tr>
      <?php else: ?>
        <tr>
          <td colspan="11" class="text-center" style="padding: 12px; color: #64748B;">Aucun étudiant trouvé pour les critères de filtrage sélectionnés.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SIGNATURE ET PIED DE PAGE -->
  <table style="width: 100%; margin-top: 15px;">
    <tr>
      <td style="width: 60%; font-size: 7.5pt; font-style: italic; color: #64748B;">
        Document édité le <?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?> par <?= htmlspecialchars($caissier_nom ?? 'Direction Financière') ?>.
      </td>
      <td style="width: 40%; text-align: right; vertical-align: top;">
        <div style="font-weight: bold; text-decoration: underline; font-size: 8pt; margin-bottom: 25px;">LE SERVICE DE COMPTABILITÉ</div>
        <div style="font-size: 8pt; font-weight: bold;"><?= htmlspecialchars($caissier_nom ?? 'Direction Financière') ?></div>
      </td>
    </tr>
  </table>

</body>
</html>
