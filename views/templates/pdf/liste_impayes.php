<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des Étudiants en Retard de Paiement</title>
  <style>
    @page {
      margin: 8mm 8mm 8mm 8mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 8pt;
      color: #000000;
      line-height: 1.25;
      background-color: #FFFFFF;
      margin: 0;
      padding: 0;
    }
    .page-wrapper {
      padding: 4px;
    }

    /* EN-TÊTE ÉTABLISSEMENT */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .header-logo-cell {
      width: 90px;
      vertical-align: top;
    }
    .header-logo-img {
      max-height: 44px;
      width: auto;
    }
    .header-text-cell {
      text-align: center;
      vertical-align: top;
    }
    .header-title {
      color: #800000;
      font-weight: bold;
      font-size: 11pt;
      text-transform: uppercase;
      margin: 0;
      text-decoration: underline;
    }
    .header-subtitle {
      font-weight: bold;
      font-style: italic;
      font-size: 8pt;
      margin-top: 3px;
      color: #000000;
    }

    /* BANNIÈRE TITRE */
    .banner-title-box {
      background-color: #999999;
      color: #000000;
      font-weight: bold;
      font-size: 11pt;
      text-align: center;
      padding: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 4px;
      margin-bottom: 6px;
      border: 1px solid #000000;
    }

    /* METADATA BAR */
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      font-size: 8pt;
    }
    .meta-table td {
      padding: 3px 4px;
    }
    .meta-label {
      font-weight: bold;
    }

    /* KPI SUMMARY BAR */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .kpi-table td {
      border: 1px solid #000000;
      padding: 6px;
      text-align: center;
      font-weight: bold;
      font-size: 8.5pt;
    }
    .kpi-total { background: #FEE2E2; color: #991B1B; }
    .kpi-count { background: #FEF3C7; color: #92400E; }
    .kpi-delay { background: #DBEAFE; color: #1E40AF; }

    /* TABLEAU DONNÉES */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 7.5pt;
    }
    .data-table th {
      background: #CCCCCC;
      color: #000000;
      font-weight: bold;
      border: 1px solid #000000;
      padding: 5px 4px;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 1px solid #000000;
      padding: 4px 5px;
      vertical-align: middle;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .text-bold { font-weight: bold; }

    .status-leger { color: #D97706; font-weight: bold; }
    .status-modere { color: #EA580C; font-weight: bold; }
    .status-critique { color: #DC2626; font-weight: bold; }

    .footer-table {
      width: 100%;
      font-size: 7pt;
      margin-top: 10px;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="page-wrapper">

  <!-- EN-TÊTE ÉTABLISSEMENT -->
  <?php 
    $logoFile = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
    $logoSrc = file_exists($logoFile) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoFile)) : '';
  ?>
  <table class="header-table">
    <tr>
      <td class="header-logo-cell">
        <?php if (!empty($logoSrc)): ?>
          <img src="<?= $logoSrc ?>" class="header-logo-img">
        <?php else: ?>
          <div style="font-weight:bold; color:#800000; font-size:12pt;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td class="header-text-cell">
        <div class="header-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="header-subtitle">Agréé par l'Etat et le FDFP</div>
        <div style="font-size: 7.5pt; font-style: italic; margin-top: 2px;">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99 — Site web : www.groupe-eicg.net</div>
      </td>
    </tr>
  </table>

  <!-- BANNIÈRE TITRE -->
  <div class="banner-title-box">RÉCAPITULATIF DES IMPAYÉS & RETARDS DE VERSEMENT</div>

  <!-- FILTRES ET MÉTRIS -->
  <table class="meta-table">
    <tr>
      <td style="width: 25%;"><span class="meta-label">Année Académique :</span> <?= htmlspecialchars($annee_libelle ?? 'Toutes') ?></td>
      <td style="width: 25%;"><span class="meta-label">Niveau d'Études :</span> <?= htmlspecialchars($niveau_libelle ?? 'Tous') ?></td>
      <td style="width: 25%;"><span class="meta-label">Classe :</span> <?= htmlspecialchars($classe_libelle ?? 'Toutes') ?></td>
      <td style="width: 25%; text-align: right;"><span class="meta-label">Sévérité :</span> <?= htmlspecialchars($severite_libelle ?? 'Tous') ?></td>
    </tr>
  </table>

  <!-- KPI SUMMARY -->
  <?php $k = $kpis ?? []; ?>
  <table class="kpi-table">
    <tr>
      <td class="kpi-count" style="width: 25%;">EFFECTIF EN RETARD<br><span style="font-size: 10pt;"><?= number_format($k['total_etudiants_retard'] ?? 0, 0, ',', ' ') ?> Étudiant(s)</span></td>
      <td class="kpi-delay" style="width: 25%;">AFFECTÉS (ÉTAT)<br><span style="font-size: 10pt;"><?= number_format($k['total_affectes'] ?? 0, 0, ',', ' ') ?></span></td>
      <td class="kpi-delay" style="width: 25%; background: #F3E8FF; color: #6B21A8;">NON AFFECTÉS (PRIVÉS)<br><span style="font-size: 10pt;"><?= number_format($k['total_non_affectes'] ?? 0, 0, ',', ' ') ?></span></td>
      <td class="kpi-total" style="width: 25%;">TOTAL IMPAYÉS ÉCHUS<br><span style="font-size: 10pt;"><?= number_format($k['total_impayes_echus'] ?? 0, 0, ',', ' ') ?> FCFA</span></td>
    </tr>
  </table>

  <!-- TABLEAU DONNÉES -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 3%;">#</th>
        <th style="width: 11%;">Matricule</th>
        <th style="width: 20%;">Nom & Prénom(s)</th>
        <th style="width: 17%;">Tuteur / Contact</th>
        <th style="width: 10%;">Classe</th>
        <th style="width: 13%;">Échéance Échue</th>
        <th style="width: 8%;">Retard</th>
        <th style="width: 9%;">Total Versé</th>
        <th style="width: 9%;">Impayé Échu</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($list)): ?>
        <?php $num = 0; foreach ($list as $e): $num++; ?>
          <tr>
            <td class="text-center text-bold"><?= $num ?></td>
            <td class="text-center text-bold"><?= htmlspecialchars($e['matricule']) ?></td>
            <td class="text-left text-bold"><?= htmlspecialchars($e['nom_complet']) ?></td>
            <td class="text-left">
              <?= htmlspecialchars($e['nom_parent']) ?><br>
              <span style="font-family: monospace; font-size: 7pt; color: #1D4ED8;"><?= htmlspecialchars($e['telephone_parent']) ?></span>
            </td>
            <td class="text-center"><?= htmlspecialchars($e['classe']) ?></td>
            <td class="text-center">
              <span style="color: #991B1B; font-weight: bold;"><?= htmlspecialchars($e['echeance_libelle']) ?></span><br>
              <span style="font-size: 6.5pt; color: #475569;"><?= htmlspecialchars($e['echeance_date']) ?></span>
            </td>
            <td class="text-center">
              <?php if ($e['severite_code'] === 'critique'): ?>
                <span class="status-critique">+<?= $e['retard_jours'] ?>j</span>
              <?php elseif ($e['severite_code'] === 'modere'): ?>
                <span class="status-modere">+<?= $e['retard_jours'] ?>j</span>
              <?php else: ?>
                <span class="status-leger">+<?= $e['retard_jours'] ?>j</span>
              <?php endif; ?>
            </td>
            <td class="text-right" style="color: #166534; font-weight: bold;"><?= number_format($e['total_paye'], 0, ',', ' ') ?></td>
            <td class="text-right text-bold" style="color: #B91C1C; background: #FEF2F2;"><?= number_format($e['montant_echu'], 0, ',', ' ') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9" class="text-center" style="padding: 12px; color: #64748B;">Aucun étudiant en retard de paiement trouvé pour les critères sélectionnés.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- FOOTER DATE & REF -->
  <table class="footer-table">
    <tr>
      <td style="width: 70%;">GEICG - Système de Gestion Intégré des Impayés et Recouvrements</td>
      <td style="width: 30%; text-align: right;">Édité le : <?= date('d/m/Y H:i:s') ?></td>
    </tr>
  </table>

</div>

</body>
</html>
