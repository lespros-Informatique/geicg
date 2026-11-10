<?php
/**
 * Template Officiel PDF : Registre des Encaissements de la Caisse des Frais Annexes
 * Moteur : mPDF via core/PdfService.php
 * Conforme aux Règles 1 (Zero Hardcoding), 4 (Dissociation Scolarité/Annexes) et 9 (Consommation configurations)
 */
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$anneeLibelle = $anneeLibelle ?? 'Toutes les années';
$niveauLibelle = $niveauLibelle ?? 'Tous les niveaux';
$classeLibelle = $classeLibelle ?? 'Toutes les classes';
$paiements = $paiements ?? [];
$stats = $stats ?? [];
$agentNom = $agentNom ?? (trim(Auth::fullName()) ?: 'Service Caisse & Comptabilité');
$dateImpression = $dateImpression ?? date('d/m/Y à H:i:s');

$dateDebutFmt = !empty($dateDebut) ? date('d/m/Y', strtotime($dateDebut)) : '';
$dateFinFmt = !empty($dateFin) ? date('d/m/Y', strtotime($dateFin)) : '';
$periodeTexte = 'Exercice global';
if ($dateDebutFmt && $dateFinFmt) {
    $periodeTexte = "Du {$dateDebutFmt} au {$dateFinFmt}";
} elseif ($dateDebutFmt) {
    $periodeTexte = "À partir du {$dateDebutFmt}";
} elseif ($dateFinFmt) {
    $periodeTexte = "Jusqu'au {$dateFinFmt}";
}

$totalEncaisse = (float)($stats['total_encaisse'] ?? 0);
$totalBudget = (float)($stats['total_frais_annexes_attendus'] ?? 0);
$resteARecouvrer = (float)($stats['attente_frais_annexes'] ?? 0);
$tauxRecouvrement = (float)($stats['taux_recouvrement_fa'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre Caisse Annexe - <?= htmlspecialchars($anneeLibelle) ?></title>
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
      border: 0.5px solid #CBD5E1;
    }
    .meta-label {
      font-size: 7pt;
      font-weight: bold;
      color: #475569;
      text-transform: uppercase;
    }
    .meta-value {
      font-size: 8pt;
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
    .kpi-budget { background-color: #EFF6FF; color: #1E3A8A; }
    .kpi-encaisse { background-color: #FEF3C7; color: #B45309; }
    .kpi-reste { background-color: #FEE2E2; color: #991B1B; }
    .kpi-taux { background-color: #DCFCE7; color: #166534; }
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
      padding: 4px 5px;
      font-size: 7.5pt;
      vertical-align: middle;
    }
    .data-table tr.total-row td {
      background-color: #E2E8F0;
      color: #0F172A;
      font-weight: bold;
      font-size: 8.5pt;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      margin-top: 16px;
      border-collapse: collapse;
    }
    .signature-grid td {
      vertical-align: top;
      font-size: 8pt;
      text-align: center;
      width: 33.33%;
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

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE DES ENCAISSEMENTS - CAISSE DES FRAIS ANNEXES</div>
        <div class="banner-sub">EXERCICE ACADÉMIQUE : <?= htmlspecialchars($anneeLibelle) ?> &bull; Édité le <?= htmlspecialchars($dateImpression) ?></div>
      </td>
    </tr>
  </table>

  <!-- SECTION DES FILTRES APPLIQUÉS -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 20%;">
        <span class="meta-label">Année Académique :</span><br>
        <span class="meta-value"><?= htmlspecialchars($anneeLibelle) ?></span>
      </td>
      <td style="width: 20%;">
        <span class="meta-label">Niveau d'Étude :</span><br>
        <span class="meta-value"><?= htmlspecialchars($niveauLibelle) ?></span>
      </td>
      <td style="width: 20%;">
        <span class="meta-label">Classe / Groupe :</span><br>
        <span class="meta-value"><?= htmlspecialchars($classeLibelle) ?></span>
      </td>
      <td style="width: 20%;">
        <span class="meta-label">Période Couverte :</span><br>
        <span class="meta-value"><?= htmlspecialchars($periodeTexte) ?></span>
      </td>
      <td style="width: 20%;">
        <span class="meta-label">Édité par :</span><br>
        <span class="meta-value"><?= htmlspecialchars($agentNom) ?></span>
      </td>
    </tr>
  </table>

  <!-- SYNTHÈSE DES INDICATEURS FINANCIERS -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-budget" style="width: 20%;">
        TOTAL BUDGET EXIGIBLE<br>
        <span style="font-size: 9.5pt;"><?= number_format($totalBudget, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-encaisse" style="width: 20%;">
        TOTAL ANNEXES ENCAISSÉ<br>
        <span style="font-size: 9.5pt;"><?= number_format($totalEncaisse, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-reste" style="width: 20%;">
        RESTE À RECOUVRER<br>
        <span style="font-size: 9.5pt;"><?= number_format($resteARecouvrer, 0, ',', ' ') ?> FCFA</span>
      </td>
      <td class="kpi-taux" style="width: 20%;">
        TAUX RECOUVREMENT<br>
        <span style="font-size: 9.5pt;"><?= number_format($tauxRecouvrement, 1, ',', ' ') ?> %</span>
      </td>
      <td class="kpi-count" style="width: 20%;">
        TOTAL TRANSACTIONS<br>
        <span style="font-size: 9.5pt;"><?= count($paiements) ?> reçu(s)</span>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 25px;">#</th>
        <th style="width: 85px;">RÉF. REÇU</th>
        <th style="width: 75px;">DATE & HEURE</th>
        <th style="width: 75px;">MATRICULE</th>
        <th>NOM & PRÉNOMS ÉTUDIANT</th>
        <th style="width: 85px;">CLASSE</th>
        <th style="width: 140px;">MOTIF / LIBELLÉ</th>
        <th style="width: 95px; text-align: right;">MONTANT VERSÉ</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($paiements)): ?>
        <tr>
          <td colspan="8" style="text-align: center; padding: 16px; color: #64748B; font-style: italic;">
            Aucun encaissement de frais annexes trouvé pour les critères sélectionnés.
          </td>
        </tr>
      <?php else: ?>
        <?php 
        $i = 1; 
        $sumTable = 0;
        foreach ($paiements as $p): 
          $mt = (float)($p['montant_paiement'] ?? 0);
          $sumTable += $mt;
          $nomComplet = trim(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? ''));
          $motif = $p['motif_paiement'] ?: ($p['libelle_frais_annexe'] ?: 'Frais Annexes');
        ?>
        <tr>
          <td style="text-align: center;"><?= $i++ ?></td>
          <td style="font-family: monospace; font-weight: bold; color: #1E3A5F; text-align: center;">
            <?= htmlspecialchars($p['code_paiement'] ?? '-') ?>
          </td>
          <td style="text-align: center;">
            <?= !empty($p['date_paiement']) ? date('d/m/Y H:i', strtotime($p['date_paiement'])) : '-' ?>
          </td>
          <td style="font-family: monospace; text-align: center;">
            <?= htmlspecialchars($p['matricule_etudiant'] ?? '-') ?>
          </td>
          <td style="font-weight: bold;">
            <?= htmlspecialchars($nomComplet ?: '-') ?>
          </td>
          <td style="text-align: center;">
            <?= htmlspecialchars($p['libelle_classe'] ?? '-') ?>
          </td>
          <td>
            <?= htmlspecialchars($motif) ?>
          </td>
          <td style="text-align: right; font-weight: bold; color: #15803D; font-family: monospace;">
            <?= number_format($mt, 0, ',', ' ') ?> FCFA
          </td>
        </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td colspan="7" style="text-align: right; padding: 6px;">TOTAL VERSÉ SUR CE REGISTRE :</td>
          <td style="text-align: right; color: #1E3A5F; padding: 6px; font-family: monospace;">
            <?= number_format($sumTable, 0, ',', ' ') ?> FCFA
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- SIGNATURES OFFICIELLES (Règle 9) -->
  <table class="signature-grid">
    <tr>
      <td>
        <div style="font-weight: bold; text-transform: uppercase; color: #0F172A; margin-bottom: 45px;">Le Caissier Annexe</div>
        <div style="font-size: 7.5pt; color: #475569;"><?= htmlspecialchars($agentNom) ?></div>
      </td>
      <td>
        <div style="font-weight: bold; text-transform: uppercase; color: #0F172A; margin-bottom: 45px;"><?= htmlspecialchars($etablissement['titre_service_finance'] ?? 'Le Service Financier') ?></div>
        <div style="font-size: 7.5pt; color: #475569;"><?= htmlspecialchars($etablissement['nom_responsable_finance'] ?? 'Visa & Cachet') ?></div>
      </td>
      <td>
        <div style="font-weight: bold; text-transform: uppercase; color: #0F172A; margin-bottom: 45px;"><?= htmlspecialchars($etablissement['titre_direction_generale'] ?? 'La Direction Générale') ?></div>
        <div style="font-size: 7.5pt; color: #475569;"><?= htmlspecialchars($etablissement['nom_directeur_general'] ?? 'Pour Approbation') ?></div>
      </td>
    </tr>
  </table>

  <!-- BAS DE PAGE -->
  <div class="footer-line">
    Système GEICG &bull; Fait à <?= htmlspecialchars($etablissement['ville_signature'] ?? ($etablissement['ville_etablissement'] ?? '')) ?>, le <?= date('d/m/Y') ?> &bull; Document certifié conforme
  </div>

</body>
</html>
