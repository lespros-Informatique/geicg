<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre des Encaissements - GROUPE EICG</title>
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
      color: #000000;
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
    .kpi-especes { background-color: #DCFCE7; color: #166534; }
    .kpi-banque { background-color: #FEF3C7; color: #92400E; }
    .kpi-count { background-color: #F1F5F9; color: #334155; }

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
      padding: 4px 4px;
      font-size: 7.5pt;
      vertical-align: middle;
    }
    .data-table tr.total-row td {
      background-color: #A6A6A6;
      color: #000000;
      font-weight: bold;
      font-size: 8pt;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      margin-top: 15px;
      border-collapse: collapse;
    }
    .signature-grid td {
      vertical-align: top;
      font-size: 8pt;
    }

    .footer-line {
      font-size: 7.5pt;
      color: #64748B;
      text-align: right;
      margin-top: 10px;
      font-style: italic;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE ETABLISSEMENT -->
  <table class="header-table">
    <tr>
      <td style="width: 15%;">
        <?php if (!empty($logo_src)): ?>
          <img src="<?= $logo_src ?>" style="max-height: 48px; max-width: 120px;">
        <?php else: ?>
          <div style="font-weight: bold; color: #800000; font-size: 14pt;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 85%; text-align: left; padding-left: 10px;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGRÉÉ PAR L'ÉTAT ET LE FDFP &bull; BOUAKÉ / CÔTE D'IVOIRE</div>
        <div class="inst-contacts">Tél : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99 &bull; Site web : www.geicg.org</div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE CHRONOLOGIQUE DES ENCAISSEMENTS EN CAISSE</div>
        <div class="banner-sub">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?? '-') ?> &bull; Édité le <?= htmlspecialchars($date_impression ?? date('d/m/Y H:i:s')) ?></div>
      </td>
    </tr>
  </table>

  <!-- RÉCAPITULATIF DES KPIS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 20%;">
        CUMUL ENCAISSÉ REGISTRE<br>
        <span style="font-size: 9.5pt; font-weight: bold;"><?= number_format($total_general ?? 0, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-scolarite" style="width: 16%;">
        PART SCOLARITÉ<br>
        <span style="font-size: 9pt; font-weight: bold;"><?= number_format($total_scolarite ?? 0, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-frais" style="width: 16%;">
        PART FRAIS ANNEXES<br>
        <span style="font-size: 9pt; font-weight: bold;"><?= number_format($total_frais_annexes ?? 0, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-especes" style="width: 16%;">
        VERSEMENTS ESPÈCES<br>
        <span style="font-size: 9pt; font-weight: bold;"><?= number_format($total_especes ?? 0, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-banque" style="width: 16%;">
        MOBILES & BANQUE<br>
        <span style="font-size: 9pt; font-weight: bold;"><?= number_format(($total_mobile_money + $total_banque) ?? 0, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-count" style="width: 16%;">
        NBRE ENCAISSEMENTS<br>
        <span style="font-size: 9.5pt; font-weight: bold;"><?= (int)($count_paiements ?? 0) ?> Règlement(s)</span>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 3%;">N°</th>
        <th style="width: 11%;">DATE & HEURE</th>
        <th style="width: 12%;">N° REÇU</th>
        <th style="width: 11%;">MATRICULE</th>
        <th style="width: 20%; text-align: left;">NOM & PRÉNOMS ÉTUDIANT</th>
        <th style="width: 11%;">CLASSE</th>
        <th style="width: 14%; text-align: left;">LIBELLÉ OPÉRATION</th>
        <th style="width: 8%;">MODE</th>
        <th style="width: 10%;">RÉFÉRENCE</th>
        <th style="width: 10%; text-align: right;">MONTANT VERSÉ</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($paiements) && is_array($paiements)): ?>
        <?php foreach ($paiements as $idx => $p): ?>
          <tr>
            <td style="text-align: center;"><?= $idx + 1 ?></td>
            <td style="text-align: center;"><?= date('d/m/Y H:i', strtotime($p['date_paiement'] ?? 'now')) ?></td>
            <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($p['code_paiement'] ?? '-') ?></td>
            <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($p['matricule_etudiant'] ?? '-') ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars(mb_strtoupper(trim(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? '')))) ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($p['libelle_classe'] ?? '-') ?></td>
            <td><?= htmlspecialchars($p['type_paiement'] ?? 'Versement Scolarité') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Espèces') ?></td>
            <td style="text-align: center; font-size: 7pt;"><?= htmlspecialchars($p['reference_paiement'] ?? '-') ?></td>
            <td style="text-align: right; font-weight: bold; color: #000000;"><?= number_format($p['montant_paiement'] ?? 0, 0, ',', ' ') ?> FCFA</td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td colspan="9" style="text-align: right; font-weight: bold;">TOTAL GÉNÉRAL DU REGISTRE DES ENCAISSEMENTS :</td>
          <td style="text-align: right; font-weight: bold; font-size: 8.5pt; color: #000000;"><?= number_format($total_general ?? 0, 0, ',', ' ') ?> FCFA</td>
        </tr>
      <?php else: ?>
        <tr>
          <td colspan="10" style="text-align: center; padding: 12px; color: #64748B;">Aucun encaissement enregistré pour les critères sélectionnés.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SIGNATURES -->
  <table class="signature-grid">
    <tr>
      <td style="width: 50%;">
        <div style="font-weight: bold; text-decoration: underline;">Le Caissier / Agent Comptable</div>
        <div style="margin-top: 25px; font-weight: bold; font-size: 8.5pt;"><?= htmlspecialchars($agent_nom ?? 'Caisse Principale GEICG') ?></div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div style="font-size: 8pt; margin-bottom: 2px;">Fait à Bouaké, le <?= date('d/m/Y') ?></div>
        <div style="font-weight: bold; text-decoration: underline;">Le Chef du Service Financier</div>
        <div style="margin-top: 25px; font-weight: bold; font-size: 8.5pt;">Direction Financière GEICG</div>
      </td>
    </tr>
  </table>

  <!-- FOOTER -->
  <div class="footer-line">
    Registre des encaissements extrait du système informatique GEICG - Page 1 / Édition du <?= date('d/m/Y H:i:s') ?>
  </div>

</body>
</html>
