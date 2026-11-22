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
  <title>Liste des Étudiants<?= !empty($etablissement['sigle_etablissement']) ? ' - ' . htmlspecialchars($etablissement['sigle_etablissement']) : '' ?></title>
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
      color: #0F172A;
      line-height: 1.25;
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

    /* Cartouche Métadonnées & KPIs */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 4px 8px;
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
      margin-bottom: 10px;
    }
    .kpi-summary-table td {
      padding: 5px 8px;
      font-size: 8pt;
      font-weight: bold;
      text-align: center;
      border: 1px solid #CBD5E1;
    }
    .kpi-total { background-color: #EFF6FF; color: #1E3A5F; }
    .kpi-affecte { background-color: #F0FDF4; color: #15803D; }
    .kpi-prive { background-color: #F0F9FF; color: #0369A1; }
    .kpi-genre { background-color: #FDF4FF; color: #ce2225ff; }

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
      padding: 5px 6px;
      font-size: 8.5pt;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .data-table td {
      border: 1px solid #000000;
      padding: 4px 6px;
      font-size: 8.5pt;
      vertical-align: middle;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-bold { font-weight: bold; }

    .badge-affecte {
      color: #15803D;
      font-weight: bold;
    }
    .badge-prive {
      color: #1D4ED8;
      font-weight: bold;
    }

    /* Footer & Signatures */
    .footer-table {
      width: 100%;
      margin-top: 15px;
      border-top: 1px solid #CBD5E1;
      padding-top: 6px;
      font-size: 8pt;
      color: #64748B;
    }
    .signature-grid {
      width: 100%;
      margin-top: 18px;
      font-size: 8.5pt;
    }
    .signature-grid td {
      vertical-align: top;
    }
  </style>
</head>
<body>

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

  <!-- EN-TÊTE ÉTABLISSEMENT UNIFIÉ GEICG -->
  <?php include __DIR__ . '/components/header_institution.php'; ?>

  <!-- BANNIÈRE PRINCIPALE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE DES ÉTUDIANTS INSCRITS</div>
        <div class="banner-sub">ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?? '') ?></div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE FILTRES ET ÉDITION -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;"><span class="meta-label">Année Académique :</span> <span class="meta-value"><?= htmlspecialchars($annee_libelle) ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Cycle :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['cycle'] ?? 'Tous les cycles') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Niveau :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['niveau'] ?? 'Tous les niveaux') ?></span></td>
      <td style="width: 25%;"><span class="meta-label">Classe :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['classe'] ?? 'Toutes les classes') ?></span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Régime Étudiant :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['regime'] ?? 'Tous les régimes') ?></span></td>
      <td><span class="meta-label">Effectif Registre :</span> <span class="meta-value"><?= count($etudiants) ?> Étudiant(s)</span></td>
      <td><span class="meta-label">Éditeur / Agent :</span> <span class="meta-value"><?= htmlspecialchars($editeur_nom) ?></span></td>
      <td><span class="meta-label">Date d'Édition :</span> <span class="meta-value"><?= date('d/m/Y H:i') ?></span></td>
    </tr>
  </table>

  <!-- Kpis SUMMARY -->
  <table class="kpi-summary-table">
    <tr>
      <td class="kpi-total" style="width: 25%;">TOTAL ÉTUDIANTS : <?= $kpis['total'] ?? 0 ?></td>
      <td class="kpi-affecte" style="width: 25%;">AFFECTÉS (ÉTAT) : <?= $kpis['affectes'] ?? 0 ?></td>
      <td class="kpi-prive" style="width: 25%;">NON AFFECTÉS (PRIVÉ) : <?= $kpis['non_affectes'] ?? 0 ?></td>
      <td class="kpi-genre" style="width: 25%;">GENRE : <?= $kpis['hommes'] ?? 0 ?> HOMMES / <?= $kpis['femmes'] ?? 0 ?> FEMMES</td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE DES ÉTUDIANTS -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 4%;">#</th>
        <th style="width: 14%;">MATRICULE</th>
        <th style="width: 28%; text-align: left;">NOM & PRÉNOM(S)</th>
        <th style="width: 6%;">SEXE</th>
        <th style="width: 14%;">RÉGIME</th>
        <th style="width: 12%;">CLASSE</th>
        <th style="width: 12%;">FILIÈRE</th>
        <th style="width: 10%;">DATE INSC.</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($etudiants)): ?>
        <?php foreach ($etudiants as $idx => $e): ?>
          <?php 
            $isAffecte = ($e['affectation_etat'] === 'affecte' || $e['affectation_etat'] === 'oui');
            $nomComplet = mb_strtoupper($e['nom_etudiant'] ?? '') . ' ' . ($e['prenom_etudiant'] ?? '');
            $rawDate = $e['created_at_inscription'] ?? ($e['created_at_etudiant'] ?? '');
            $dateFormatted = '-';
            if (!empty($rawDate) && $rawDate !== '0000-00-00 00:00:00') {
                $dateFormatted = date('d/m/Y', strtotime($rawDate));
            }
          ?>
          <tr>
            <td class="text-center text-bold"><?= $idx + 1 ?></td>
            <td class="text-center text-bold" style="font-family: monospace; color: #1E3A5F;"><?= htmlspecialchars($e['matricule_etudiant'] ?? '-') ?></td>
            <td class="text-left text-bold"><?= htmlspecialchars($nomComplet) ?></td>
            <td class="text-center text-bold"><?= htmlspecialchars(!empty($e['sexe_etudiant']) ? $e['sexe_etudiant'] : '-') ?></td>
            <td class="text-center">
              <?php if ($isAffecte): ?>
                <span class="badge-affecte">Affecté(e) État</span>
              <?php else: ?>
                <span class="badge-prive">Non Affecté(e)</span>
              <?php endif; ?>
            </td>
            <td class="text-center text-bold"><?= htmlspecialchars($e['libelle_classe'] ?? '-') ?></td>
            <td class="text-center" title="<?= htmlspecialchars($e['libelle_filiere'] ?? '') ?>">
              <?= htmlspecialchars(!empty($e['slug_filiere']) ? $e['slug_filiere'] : ($e['libelle_filiere'] ?? '-')) ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($dateFormatted) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center" style="padding: 15px; color: #64748B; font-style: italic;">
            Aucun étudiant ne correspond aux critères de recherche sélectionnés.
          </td>
        </tr>
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
