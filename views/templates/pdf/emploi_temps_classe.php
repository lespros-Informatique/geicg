<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Emploi du Temps - <?= htmlspecialchars($classe['libelle_classe'] ?? 'Classe') ?> - GEICG</title>
  <style>
    @page {
      margin-top: 8mm;
      margin-bottom: 8mm;
      margin-left: 8mm;
      margin-right: 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8pt;
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
      font-size: 8pt;
      font-weight: bold;
      color: #0369A1;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 7.2pt;
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

    /* Grille Hebdomadaire (Tableau des 6 jours) */
    .schedule-grid-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .schedule-grid-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 8pt;
      text-transform: uppercase;
      padding: 5px 4px;
      border: 0.5px solid #0F2744;
      text-align: center;
    }
    .schedule-grid-table td {
      border: 0.5px solid #CBD5E1;
      padding: 4px;
      vertical-align: top;
      background-color: #FFFFFF;
      min-height: 180px;
    }

    /* Carte de Créneau */
    .slot-card {
      border: 1px solid #CBD5E1;
      border-left: 3.5px solid #0284C7;
      background-color: #F8FAFC;
      padding: 4px 5px;
      margin-bottom: 5px;
      border-radius: 3px;
    }
    .slot-time {
      font-size: 7.2pt;
      font-weight: bold;
      color: #0284C7;
      margin-bottom: 2px;
    }
    .slot-matiere {
      font-size: 7.5pt;
      font-weight: bold;
      color: #0F172A;
      margin-bottom: 2px;
    }
    .slot-prof {
      font-size: 6.8pt;
      color: #475569;
    }
    .slot-salle {
      font-size: 6.8pt;
      font-weight: bold;
      color: #15803D;
      margin-top: 1px;
    }
    .empty-day {
      text-align: center;
      color: #94A3B8;
      font-size: 7pt;
      font-style: italic;
      padding: 16px 2px;
    }

    /* Tableau Synthèse du Volume Horaire */
    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 6px;
    }
    .summary-table th {
      background-color: #E2E8F0;
      color: #1E293B;
      font-weight: bold;
      font-size: 7pt;
      text-transform: uppercase;
      padding: 3px 5px;
      border: 0.5px solid #CBD5E1;
      text-align: left;
    }
    .summary-table td {
      padding: 3px 5px;
      border: 0.5px solid #E2E8F0;
      font-size: 7pt;
      vertical-align: middle;
    }

    /* Cadre de Signature */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    .signature-box {
      width: 48%;
      vertical-align: top;
      border: 0.5px solid #CBD5E1;
      background-color: #FAFAFA;
      padding: 4px 8px;
    }
    .signature-title {
      font-size: 7.5pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      border-bottom: 0.5px solid #CBD5E1;
      padding-bottom: 2px;
      margin-bottom: 24px;
    }
    .signature-mention {
      font-size: 6.8pt;
      font-style: italic;
      color: #64748B;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Emploi du Temps - Classe <?= htmlspecialchars($classe['libelle_classe'] ?? '') ?> &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7pt; color: #64748B; padding-top: 3px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          Document officiel GEICG &bull; Édité le <?= date('d/m/Y H:i') ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: EDT-CLS-<?= preg_replace('/[^A-Za-z0-9]/', '', $classe['code_classe'] ?? 'CLS') ?>-<?= date('Ymd') ?>
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
        <div class="banner-title">EMPLOI DU TEMPS HEBDOMADAIRE : <?= htmlspecialchars($classe['libelle_classe'] ?? 'CLASSE') ?></div>
        <div class="banner-sub">Planning officiel des enseignements et répartition hebdomadaire des salles</div>
      </td>
    </tr>
  </table>

  <?php
    $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $dayLabels = [
      'lundi' => 'Lundi',
      'mardi' => 'Mardi',
      'mercredi' => 'Mercredi',
      'jeudi' => 'Jeudi',
      'vendredi' => 'Vendredi',
      'samedi' => 'Samedi'
    ];

    $groupedSlots = [];
    foreach ($days as $d) {
        $groupedSlots[$d] = [];
    }

    $totalMinutes = 0;
    $distinctMatieres = [];
    $distinctProfs = [];

    foreach ($slots as $s) {
        $j = strtolower(trim($s['jour'] ?? ''));
        if (isset($groupedSlots[$j])) {
            $groupedSlots[$j][] = $s;
        }

        if (!empty($s['heure_debut']) && !empty($s['heure_fin'])) {
            $tDebut = strtotime('2000-01-01 ' . $s['heure_debut']);
            $tFin = strtotime('2000-01-01 ' . $s['heure_fin']);
            if ($tFin > $tDebut) {
                $totalMinutes += ($tFin - $tDebut) / 60;
            }
        }

        $mat = trim($s['libelle_matiere'] ?? '');
        if ($mat) $distinctMatieres[$mat] = true;

        $prof = trim($s['nom_prof'] ?? '');
        if ($prof) $distinctProfs[$prof] = true;
    }

    $hTotal = floor($totalMinutes / 60);
    $mTotal = $totalMinutes % 60;
    $volumeTotalStr = sprintf("%dh%02d", $hTotal, $mTotal);
  ?>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Classe :</span>
        <span class="meta-value"><?= htmlspecialchars($classe['libelle_classe'] ?? '-') ?></span>
        <span style="font-size: 6.8pt; color: #64748B;">(<?= htmlspecialchars($classe['code_classe'] ?? '') ?>)</span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Filière :</span>
        <span class="meta-value"><?= htmlspecialchars($classe['libelle_filiere'] ?? '-') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Niveau d'études :</span>
        <span class="meta-value"><?= htmlspecialchars($classe['libelle_niveau'] ?? '-') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span>
        <span class="meta-value"><?= htmlspecialchars($annee_libelle ?? 'Active') ?></span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="meta-label">Effectif Inscrits :</span>
        <span class="meta-value"><?= (int)($nb_etudiants ?? 0) ?> étudiant(s)</span>
        <?php if (!empty($classe['capacite_max_classe'])): ?>
          <span style="font-size: 6.8pt; color: #64748B;">/ <?= (int)$classe['capacite_max_classe'] ?> max</span>
        <?php endif; ?>
      </td>
      <td>
        <span class="meta-label">Volume Hebdo :</span>
        <span class="meta-value" style="color: #0284C7;"><?= $volumeTotalStr ?></span>
        <span style="font-size: 6.8pt; color: #64748B;">(<?= count($slots) ?> créneau(x))</span>
      </td>
      <td>
        <span class="meta-label">Matières actives :</span>
        <span class="meta-value"><?= count($distinctMatieres) ?> matière(s)</span>
      </td>
      <td>
        <span class="meta-label">Date d'édition :</span>
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
    </tr>
  </table>

  <!-- GRILLE HEBDOMADAIRE (6 JOURS) -->
  <table class="schedule-grid-table">
    <thead>
      <tr>
        <?php foreach ($days as $d): ?>
          <th style="width: 16.66%;">
            <?= strtoupper($dayLabels[$d]) ?>
            <div style="font-size: 6.5pt; font-weight: normal; opacity: 0.85; text-transform: lowercase;">
              <?= count($groupedSlots[$d]) ?> cours
            </div>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <tr>
        <?php foreach ($days as $d): ?>
          <td>
            <?php if (empty($groupedSlots[$d])): ?>
              <div class="empty-day">Aucun cours</div>
            <?php else: ?>
              <?php foreach ($groupedSlots[$d] as $slot): ?>
                <?php
                  $hDeb = substr($slot['heure_debut'] ?? '', 0, 5);
                  $hFin = substr($slot['heure_fin'] ?? '', 0, 5);
                  $mat = $slot['libelle_matiere'] ?? 'Matière n/a';
                  $prof = !empty($slot['nom_prof']) ? trim($slot['nom_prof']) : 'Enseignant non assigné';
                  $salle = !empty($slot['libelle_salle']) ? $slot['libelle_salle'] : 'Salle n/a';
                ?>
                <div class="slot-card">
                  <div class="slot-time"><?= htmlspecialchars($hDeb) ?> - <?= htmlspecialchars($hFin) ?></div>
                  <div class="slot-matiere"><?= htmlspecialchars($mat) ?></div>
                  <div class="slot-prof">&#9656; <?= htmlspecialchars($prof) ?></div>
                  <div class="slot-salle">Salle : <?= htmlspecialchars($salle) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>

  <!-- TABLEAU RÉCAPITULATIF DES MATIÈRES ET ENSEIGNANTS -->
  <?php
    $matiereStats = [];
    foreach ($slots as $s) {
        $mName = trim($s['libelle_matiere'] ?? 'Matière n/a');
        if (!isset($matiereStats[$mName])) {
            $matiereStats[$mName] = [
                'prof' => trim($s['nom_prof'] ?? '-'),
                'salle' => trim($s['libelle_salle'] ?? '-'),
                'creneaux' => 0,
                'minutes' => 0
            ];
        }
        $matiereStats[$mName]['creneaux']++;
        if (!empty($s['heure_debut']) && !empty($s['heure_fin'])) {
            $tD = strtotime('2000-01-01 ' . $s['heure_debut']);
            $tF = strtotime('2000-01-01 ' . $s['heure_fin']);
            if ($tF > $tD) {
                $matiereStats[$mName]['minutes'] += ($tF - $tD) / 60;
            }
        }
    }
  ?>

  <?php if (!empty($matiereStats)): ?>
  <table class="summary-table">
    <thead>
      <tr>
        <th style="width: 32%;">Matière Enseignée</th>
        <th style="width: 28%;">Enseignant Titulaire</th>
        <th style="width: 16%;">Salle Habituelle</th>
        <th style="width: 12%; text-align: center;">Séances / Semaine</th>
        <th style="width: 12%; text-align: right;">Volume Hebdo</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($matiereStats as $mName => $st): ?>
        <?php
          $hSub = floor($st['minutes'] / 60);
          $mSub = $st['minutes'] % 60;
          $volSub = sprintf("%dh%02d", $hSub, $mSub);
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($mName) ?></strong></td>
          <td><?= htmlspecialchars($st['prof']) ?></td>
          <td><?= htmlspecialchars($st['salle']) ?></td>
          <td style="text-align: center;"><?= $st['creneaux'] ?> séance(s)</td>
          <td style="text-align: right; font-weight: bold; color: #0284C7;"><?= $volSub ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- BLOC DE SIGNATURE ET VISAS -->
  <table class="signature-table">
    <tr>
      <td class="signature-box" style="margin-right: 4%;">
        <div class="signature-title">Le Délégué / Représentant de la Classe</div>
        <div class="signature-mention">Reçu et pris connaissance le : ..... / ..... / 20.....</div>
      </td>
      <td class="signature-box">
        <div class="signature-title">La Direction des Études & de la Pédagogie</div>
        <div class="signature-mention">Signature & Cachet Officiel de l'Établissement</div>
      </td>
    </tr>
  </table>

</body>
</html>

