<?php
/**
 * GEICG - Gabarit PDF Officiel : Programme des Cours & Séances
 * Conforme Directive Système Règle 1 (Zero Hardcoding), Règle 5 (MVC) & Règle 9 (Configurations Table)
 */

// 1. Initialisation défensive de la configuration institutionnelle (Règle 9)
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

$sigleEtab = $etablissement['sigle_etablissement'] ?? 'GEICG';
$dirTitre = !empty($etablissement['titre_direction_pedagogique']) ? $etablissement['titre_direction_pedagogique'] : 'La Direction des Études & Pédagogie';
$dirNom = $etablissement['nom_directeur_etudes'] ?? '';
$villeSignature = !empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : ($etablissement['ville_etablissement'] ?? '');
$textePiedPage = $etablissement['texte_pied_page'] ?? 'Document officiel généré par le Système GEICG';

$stats = $stats ?? ['total_cours' => 0, 'total_heures' => 0, 'nb_profs' => 0, 'nb_classes' => 0, 'nb_salles' => 0];
$items = $items ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Programme des Cours &amp; Séances - <?= htmlspecialchars($sigleEtab) ?></title>
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
      color: #1E293B;
      line-height: 1.25;
    }

    /* En-tête institutionnel */
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
    }
    .banner-table td {
      vertical-align: middle;
    }
    .banner-title {
      font-size: 13pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 0;
    }
    .banner-subtitle {
      font-size: 9pt;
      font-weight: bold;
      color: #0369A1;
      margin-top: 2px;
    }

    .badge-date {
      display: inline-block;
      background: #1E3A5F;
      color: #FFFFFF;
      padding: 3px 10px;
      border-radius: 4px;
      font-size: 8.5pt;
      font-weight: bold;
    }

    /* Bloc Filtre / Focus Enseignant */
    .focus-box {
      width: 100%;
      border: 1px solid #BAE6FD;
      background: #F0F9FF;
      border-radius: 4px;
      padding: 6px 10px;
      margin-bottom: 6px;
      box-sizing: border-box;
    }
    .focus-title {
      font-size: 8pt;
      font-weight: bold;
      color: #0369A1;
      text-transform: uppercase;
    }
    .focus-val {
      font-size: 9pt;
      font-weight: bold;
      color: #0F172A;
      margin-top: 1px;
    }

    /* Grille KPIs */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .kpi-table td {
      border: 1px solid #CBD5E1;
      padding: 5px 8px;
      text-align: center;
      background: #F8FAFC;
      width: 25%;
    }
    .kpi-lbl {
      font-size: 7pt;
      font-weight: bold;
      color: #64748B;
      text-transform: uppercase;
    }
    .kpi-val {
      font-size: 11pt;
      font-weight: bold;
      color: #1E3A5F;
      margin-top: 1px;
    }

    /* Tableau des séances */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 8px;
    }
    .data-table th, .data-table td {
      border: 1px solid #CBD5E1;
      padding: 5px 6px;
      font-size: 8pt;
      vertical-align: middle;
    }
    .data-table th {
      background: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 7.5pt;
      text-align: left;
    }
    .data-table tr:nth-child(even) td {
      background: #F8FAFC;
    }
    .col-num {
      width: 25px;
      text-align: center;
      font-weight: bold;
      color: #64748B;
    }
    .col-creneau {
      width: 110px;
    }
    .creneau-time {
      font-weight: bold;
      color: #1E3A5F;
      font-size: 8.5pt;
    }
    .creneau-dur {
      font-size: 7pt;
      color: #1D4ED8;
      font-weight: bold;
    }
    .badge-classe {
      display: inline-block;
      background: #F1F5F9;
      border: 1px solid #CBD5E1;
      color: #1E293B;
      padding: 2px 5px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 7.5pt;
    }
    .badge-salle {
      color: #0369A1;
      font-weight: bold;
      font-size: 8pt;
    }
    .prof-nom {
      font-weight: bold;
      color: #0F172A;
    }
    .prof-tel {
      font-size: 7pt;
      color: #64748B;
    }

    /* État vide */
    .empty-box {
      border: 1px dashed #CBD5E1;
      background: #F8FAFC;
      border-radius: 4px;
      padding: 30px;
      text-align: center;
      color: #64748B;
      font-size: 10pt;
      font-weight: bold;
      margin: 15px 0;
    }

    /* Bas de page & Signatures */
    .footer-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .footer-table td {
      vertical-align: top;
      font-size: 8pt;
    }
    .signature-box {
      text-align: right;
    }
    .signature-title {
      font-size: 8.5pt;
      font-weight: bold;
      text-decoration: underline;
      color: #0F172A;
    }
    .signature-name {
      font-size: 9pt;
      font-weight: bold;
      color: #1E3A5F;
      margin-top: 40px;
    }
    .doc-meta {
      font-size: 7pt;
      color: #64748B;
      font-style: italic;
      margin-top: 10px;
      border-top: 1px dashed #CBD5E1;
      padding-top: 4px;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE INSTITUTIONNEL UNIFIÉ (Règle 9) -->
  <?php 
    $headerMode = 'compact';
    $showHeaderDivider = false;
    include __DIR__ . '/components/header_institution.php'; 
  ?>

  <div class="divider-line"></div>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td style="width: 65%;">
        <div class="banner-title">PROGRAMME DES COURS &amp; SÉANCES</div>
        <div class="banner-subtitle">
          <?= htmlspecialchars(mb_strtoupper($date_formatee ?? date('d/m/Y'), 'UTF-8')) ?>
          <?php if (!empty($annee_libelle)): ?>
            &bull; Année Académique : <?= htmlspecialchars($annee_libelle) ?>
          <?php endif; ?>
        </div>
      </td>
      <td style="width: 35%; text-align: right;">
        <span class="badge-date">
          <?= !empty($is_today) ? "Planning d'Aujourd'hui" : htmlspecialchars($jour_libelle ?? 'Séances') ?>
        </span>
      </td>
    </tr>
  </table>

  <!-- FOCUS ENSEIGNANT (Si filtré par enseignant) -->
  <?php if (!empty($enseignant)): ?>
    <div class="focus-box">
      <div class="focus-title">Planning spécifique de l'Enseignant</div>
      <div class="focus-val">
        <?= htmlspecialchars(trim(($enseignant['nom_user'] ?? '') . ' ' . ($enseignant['prenom_user'] ?? ''))) ?>
        <?php if (!empty($enseignant['grade_enseignant'])): ?>
          &bull; <?= htmlspecialchars($enseignant['grade_enseignant']) ?>
        <?php endif; ?>
        <?php if (!empty($enseignant['type_contrat'])): ?>
          (<?= htmlspecialchars(ucfirst($enseignant['type_contrat'])) ?>)
        <?php endif; ?>
        <?php if (!empty($enseignant['telephone_user'])): ?>
          &bull; Tél: <?= htmlspecialchars($enseignant['telephone_user']) ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- RÉSUMÉ CHIFFRÉ (KPIS DU JOUR) -->
  <table class="kpi-table">
    <tr>
      <td>
        <div class="kpi-lbl">Séances Planifiées</div>
        <div class="kpi-val"><?= (int)($stats['total_cours'] ?? count($items)) ?></div>
      </td>
      <td>
        <div class="kpi-lbl">Volume Horaire Total</div>
        <div class="kpi-val"><?= number_format((float)($stats['total_heures'] ?? 0), 1, ',', ' ') ?> h</div>
      </td>
      <td>
        <div class="kpi-lbl">Enseignants Mobilisés</div>
        <div class="kpi-val"><?= (int)($stats['nb_profs'] ?? 0) ?></div>
      </td>
      <td>
        <div class="kpi-lbl">Classes Concernées</div>
        <div class="kpi-val"><?= (int)($stats['nb_classes'] ?? 0) ?></div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU PROGRAMME -->
  <?php if (!empty($items)): ?>
    <table class="data-table">
      <thead>
        <tr>
          <th class="col-num">#</th>
          <th class="col-creneau">Horaire</th>
          <th>Matière</th>
          <th style="width: 105px;">Classe</th>
          <th style="width: 95px;">Salle</th>
          <th>Enseignant Responsable</th>
        </tr>
      </thead>
      <tbody>
        <?php 
          $idx = 0;
          foreach ($items as $item): 
            $idx++;
            $hDeb = !empty($item['heure_debut']) ? substr($item['heure_debut'], 0, 5) : '';
            $hFin = !empty($item['heure_fin']) ? substr($item['heure_fin'], 0, 5) : '';
            $duree = (float)($item['duree'] ?? 0);
            $profNom = trim(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? ''));
            if (empty($profNom)) $profNom = 'Non assigné';
        ?>
          <tr>
            <td class="col-num"><?= $idx ?></td>
            <td class="col-creneau">
              <div class="creneau-time"><?= htmlspecialchars($hDeb . ' - ' . $hFin) ?></div>
              <div class="creneau-dur"><?= number_format($duree, 1, ',', ' ') ?> h</div>
            </td>
            <td>
              <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_matiere'] ?? '-') ?></strong>
              <?php if (!empty($item['code_matiere'])): ?>
                <span style="font-size: 7pt; color: #64748B;">(<?= htmlspecialchars($item['code_matiere']) ?>)</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge-classe"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></span>
            </td>
            <td>
              <span class="badge-salle"><?= htmlspecialchars($item['libelle_salle'] ?? 'Non définie') ?></span>
            </td>
            <td>
              <div class="prof-nom"><?= htmlspecialchars($profNom) ?></div>
              <?php if (!empty($item['telephone_enseignant'])): ?>
                <div class="prof-tel">Tél : <?= htmlspecialchars($item['telephone_enseignant']) ?></div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="empty-box">
      Aucune séance d'enseignement planifiée pour cette sélection (<?= htmlspecialchars($date_formatee ?? $date) ?>).
    </div>
  <?php endif; ?>

  <!-- BAS DE PAGE & SIGNATURES -->
  <table class="footer-table">
    <tr>
      <td style="width: 55%; vertical-align: bottom;">
        <div style="font-size: 7.5pt; color: #64748B;">
          Document édité le <?= date('d/m/Y à H:i') ?> via GEICG.<br>
          Planning conforme aux emplois du temps officiels validés.
        </div>
      </td>
      <td style="width: 45%;" class="signature-box">
        <?php if (!empty($villeSignature)): ?>
          <div style="font-size: 8pt; margin-bottom: 2px;">
            Fait à <?= htmlspecialchars($villeSignature) ?>, le <?= date('d/m/Y') ?>
          </div>
        <?php endif; ?>
        <div class="signature-title"><?= htmlspecialchars($dirTitre) ?></div>
        <div class="signature-name"><?= htmlspecialchars($dirNom) ?></div>
      </td>
    </tr>
  </table>

  <div class="doc-meta">
    <?= htmlspecialchars($textePiedPage) ?> &bull; Page 1/1
  </div>

</body>
</html>

