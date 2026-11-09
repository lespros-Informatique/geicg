<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$logoPath = '';
if (!empty($etablissement['logo_etablissement'])) {
    $cLogo = [
        __DIR__ . '/../../../public/' . ltrim($etablissement['logo_etablissement'], '/'),
        __DIR__ . '/../../../' . ltrim($etablissement['logo_etablissement'], '/'),
        __DIR__ . '/../../public/' . ltrim($etablissement['logo_etablissement'], '/')
    ];
    foreach ($cLogo as $clp) {
        if (file_exists($clp) && is_file($clp)) {
            $logoPath = $clp;
            break;
        }
    }
}
if (empty($logoPath) || !file_exists($logoPath)) {
    $candidateLogos = [
        __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg',
        __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg'
    ];
    foreach ($candidateLogos as $l) {
        if (file_exists($l)) {
            $logoPath = $l;
            break;
        }
    }
}

$depenses = isset($depenses) && is_array($depenses) ? $depenses : [];
$stats = isset($stats) && is_array($stats) ? $stats : [];
$anneeLibelle = $anneeLibelle ?? 'Toutes les années';
$typeLibelle = $typeLibelle ?? 'Toutes les catégories';
$imprimePar = $imprimePar ?? (class_exists('Auth') && Auth::check() ? Auth::fullName() : 'Service Comptabilité');

$dateDebutFmt = !empty($dateDebut) ? date('d/m/Y', strtotime($dateDebut)) : '';
$dateFinFmt = !empty($dateFin) ? date('d/m/Y', strtotime($dateFin)) : '';
$periodeTexte = 'Toute la période';
if ($dateDebutFmt && $dateFinFmt) {
    $periodeTexte = "Du {$dateDebutFmt} au {$dateFinFmt}";
} elseif ($dateDebutFmt) {
    $periodeTexte = "À partir du {$dateDebutFmt}";
} elseif ($dateFinFmt) {
    $periodeTexte = "Jusqu'au {$dateFinFmt}";
}

$totalMontant = 0;
foreach ($depenses as $d) {
    $totalMontant += (float)($d['montant_depense'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre des Dépenses - <?= htmlspecialchars($etablissement['nom_etablissement'] ?? 'GROUPE EICG') ?></title>
  <style>
    @page {
      margin-top: 6mm;
      margin-bottom: 8mm;
      margin-left: 6mm;
      margin-right: 6mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 7.5pt;
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
      border: 0.5px solid #CBD5E1;
    }
    .meta-label {
      font-size: 6.5pt;
      font-weight: bold;
      color: #475569;
      text-transform: uppercase;
    }
    .meta-value {
      font-size: 7.5pt;
      font-weight: bold;
      color: #0F172A;
    }

    /* Synthèse KPIs */
    .kpi-summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .kpi-summary-table td {
      padding: 5px 6px;
      font-size: 7pt;
      font-weight: bold;
      text-align: center;
      border: 1px solid #CBD5E1;
    }
    .kpi-approuve { background-color: #F0FDF4; color: #166534; }
    .kpi-attente { background-color: #FFFBEB; color: #92400E; }
    .kpi-annule { background-color: #FEF2F2; color: #991B1B; }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }

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
      font-size: 7pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 1px solid #000000;
      padding: 4px 4px;
      font-size: 7pt;
      vertical-align: middle;
    }
    .data-table tr.total-row td {
      background-color: #E2E8F0;
      color: #000000;
      font-weight: bold;
      font-size: 7.5pt;
    }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 1px 4px;
      border-radius: 3px;
      font-size: 6pt;
      font-weight: bold;
      text-transform: uppercase;
      text-align: center;
    }
    .badge-success { background: #DCFCE7; color: #15803D; border: 0.5px solid #86EFAC; }
    .badge-warning { background: #FEF3C7; color: #B45309; border: 0.5px solid #FCD34D; }
    .badge-danger { background: #FEE2E2; color: #991B1B; border: 0.5px solid #FCA5A5; }
    .badge-secondary { background: #F1F5F9; color: #475569; border: 0.5px solid #CBD5E1; }

    /* Signatures */
    .signature-grid {
      width: 100%;
      margin-top: 14px;
      border-collapse: collapse;
    }
    .signature-grid td {
      vertical-align: top;
      font-size: 7.5pt;
    }

    .footer-line {
      font-size: 6.5pt;
      color: #64748B;
      text-align: right;
      margin-top: 8px;
      font-style: italic;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE DES DÉPENSES & CHARGES DE FONCTIONNEMENT</div>
        <div class="banner-sub">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($anneeLibelle) ?> &bull; Date d'édition : <?= date('d/m/Y H:i') ?> &bull; Édité par : <?= htmlspecialchars($imprimePar) ?></div>
      </td>
    </tr>
  </table>

  <!-- SECTION DES FILTRES APPLIQUÉS -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span><br>
        <span class="meta-value"><?= htmlspecialchars($anneeLibelle) ?></span>
      </td>
      <td style="width: 30%;">
        <span class="meta-label">Catégorie de Dépense :</span><br>
        <span class="meta-value"><?= htmlspecialchars($typeLibelle) ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Période du Registre :</span><br>
        <span class="meta-value"><?= htmlspecialchars($periodeTexte) ?></span>
      </td>
      <td style="width: 20%;">
        <span class="meta-label">Nombre de Dépenses :</span><br>
        <span class="meta-value"><?= count($depenses) ?> opération(s)</span>
      </td>
    </tr>
  </table>

  <!-- RÉCAPITULATIF DES KPIS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-approuve" style="width: 26%;">
        DÉPENSES APPROUVÉES<br>
        <span style="font-size: 8.5pt; font-weight: bold;"><?= number_format($stats['montant_approuve'] ?? 0, 0, ',', ' ') ?> FCFA</span>
        <span style="font-size: 6.5pt; font-weight: normal;">(<?= (int)($stats['count_approuve'] ?? 0) ?> validée(s))</span>
      </td>
      <td class="kpi-attente" style="width: 25%;">
        EN ATTENTE DE VALIDATION<br>
        <span style="font-size: 8.5pt; font-weight: bold;"><?= number_format($stats['montant_en_attente'] ?? 0, 0, ',', ' ') ?> FCFA</span>
        <span style="font-size: 6.5pt; font-weight: normal;">(<?= (int)($stats['count_en_attente'] ?? 0) ?> en attente)</span>
      </td>
      <td class="kpi-annule" style="width: 24%;">
        DÉPENSES ANNULÉES<br>
        <span style="font-size: 8.5pt; font-weight: bold;"><?= number_format($stats['montant_annule'] ?? 0, 0, ',', ' ') ?> FCFA</span>
        <span style="font-size: 6.5pt; font-weight: normal;">(<?= (int)($stats['count_annule'] ?? 0) ?> annulée(s))</span>
      </td>
      <td class="kpi-total" style="width: 25%;">
        TOTAL DÉCAISSEMENTS DU REGISTRE<br>
        <span style="font-size: 9pt; font-weight: bold;"><?= number_format($totalMontant, 0, ',', ' ') ?> FCFA</span>
        <span style="font-size: 6.5pt; font-weight: normal;">(<?= count($depenses) ?> ligne(s))</span>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 3%;">N°</th>
        <th style="width: 9%;">DATE</th>
        <th style="width: 9%;">CODE DÉPENSE</th>
        <th style="width: 14%; text-align: left;">CATÉGORIE</th>
        <th style="width: 22%; text-align: left;">MOTIF / DESCRIPTION</th>
        <th style="width: 13%; text-align: left;">BÉNÉFICIAIRE</th>
        <th style="width: 8%;">MODE</th>
        <th style="width: 11%; text-align: left;">AUTEUR / VALIDATEUR</th>
        <th style="width: 8%;">STATUT</th>
        <th style="width: 10%; text-align: right;">MONTANT (FCFA)</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($depenses) && is_array($depenses)): ?>
        <?php foreach ($depenses as $idx => $d): ?>
          <?php
            $rawDate = !empty($d['periode_depense']) ? date('d/m/Y', strtotime($d['periode_depense'])) : '-';
            $statut = $d['statut_depense'] ?? 'en_attente';
            $mode = $d['mode_reglement'] ?? 'espece';
            $modeLabels = [
                'espece' => 'Espèces',
                'mobile_money' => 'Mobile Money',
                'cheque' => 'Chèque',
                'virement' => 'Virement'
            ];
            $modeTxt = $modeLabels[$mode] ?? ucfirst($mode);
          ?>
          <tr>
            <td style="text-align: center;"><?= $idx + 1 ?></td>
            <td style="text-align: center;"><?= $rawDate ?></td>
            <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($d['code_depense'] ?? '-') ?></td>
            <td style="font-weight: 600;"><?= htmlspecialchars($d['libelle_type_depense'] ?? 'Général') ?></td>
            <td><?= htmlspecialchars($d['description_depense'] ?? ($d['libelle_depense'] ?? '-')) ?></td>
            <td><?= htmlspecialchars($d['beneficiaire'] ?? '-') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($modeTxt) ?></td>
            <td style="font-size: 6.5pt;">
              <div style="font-weight: bold;"><?= htmlspecialchars(trim($d['auteur_nom_complet'] ?? '-')) ?></div>
              <?php if (!empty($d['confirmateur_nom_complet']) && $statut !== 'en_attente'): ?>
                <div style="color: #64748B;"><?= ($statut === 'approuve' ? 'Val: ' : 'Ann: ') . htmlspecialchars(trim($d['confirmateur_nom_complet'])) ?></div>
              <?php endif; ?>
            </td>
            <td style="text-align: center;">
              <?php if ($statut === 'approuve'): ?>
                <span class="badge badge-success">Approuvé</span>
              <?php elseif ($statut === 'annule'): ?>
                <span class="badge badge-danger">Annulé</span>
              <?php else: ?>
                <span class="badge badge-warning">En attente</span>
              <?php endif; ?>
            </td>
            <td style="text-align: right; font-weight: bold; color: <?= $statut === 'annule' ? '#94A3B8; text-decoration: line-through;' : '#991B1B;' ?>">
              <?= number_format((float)($d['montant_depense'] ?? 0), 0, ',', ' ') ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td colspan="9" style="text-align: right; font-weight: bold;">TOTAL DU REGISTRE DES DÉPENSES :</td>
          <td style="text-align: right; font-weight: bold; font-size: 8pt; color: #991B1B;"><?= number_format($totalMontant, 0, ',', ' ') ?> FCFA</td>
        </tr>
      <?php else: ?>
        <tr>
          <td colspan="10" style="text-align: center; padding: 12px; color: #64748B;">Aucune dépense enregistrée pour les critères sélectionnés.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC SIGNATURES -->
  <table class="signature-grid">
    <tr>
      <td style="width: 50%;">
        <div style="font-weight: bold; text-decoration: underline;">L'Agent Comptable / Responsable Décaissements</div>
        <div style="margin-top: 25px; font-weight: bold; font-size: 8pt;"><?= htmlspecialchars($imprimePar) ?></div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div style="font-size: 7.5pt; margin-bottom: 2px;">Fait à <?= htmlspecialchars($etablissement['ville_signature'] ?? 'Bouaké') ?>, le <?= date('d/m/Y') ?></div>
        <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($etablissement['titre_service_finance'] ?? 'La Direction Financière & Comptable') ?></div>
        <div style="margin-top: 25px; font-weight: bold; font-size: 8pt;"><?= htmlspecialchars($etablissement['nom_responsable_finance'] ?? 'Direction Financière') ?></div>
      </td>
    </tr>
  </table>

  <!-- PIED DE PAGE -->
  <div class="footer-line">
    Registre des dépenses extrait du système informatique <?= htmlspecialchars($etablissement['sigle_etablissement'] ?? 'GEICG') ?> &bull; Page 1 &bull; Document officiel
  </div>

</body>
</html>

