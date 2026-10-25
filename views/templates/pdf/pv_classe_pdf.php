<?php
$classeInfo = $classeInfo ?? [];
$matieres = $matieres ?? [];
$pvRows = $pvRows ?? [];
$statsClasse = $statsClasse ?? [];
$selectedClasseCode = $selectedClasseCode ?? '';
$selectedSemestreCode = $selectedSemestreCode ?? '';
$selectedSemestreLibelle = $selectedSemestreLibelle ?? $selectedSemestreCode;
$anneeLibelle = $anneeLibelle ?? '-';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Procès-Verbal de Délibération - GROUPE EICG</title>
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
      font-size: 7pt;
      color: #64748B;
      margin-top: 1px;
    }

    .divider-line {
      height: 1.5px;
      background-color: #1E3A5F;
      margin-top: 2px;
      margin-bottom: 4px;
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

    /* Cartouche Métadonnées & KPIs */
    .meta-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      border: 1px solid #CBD5E1;
      background-color: #F8FAFC;
    }
    .meta-box-table td {
      padding: 3.5px 6px;
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

    /* Tableau Matrice PV */
    .pv-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .pv-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 7pt;
      padding: 4px 3px;
      border: 0.5px solid #0F172A;
      text-align: center;
    }
    .pv-table td {
      font-size: 7.5pt;
      padding: 3px 3px;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
    }
    .pv-table tr:nth-child(even) td {
      background-color: #F8FAFC;
    }
    .pv-table .admis-cell {
      background-color: #DCFCE7 !important;
      color: #166534 !important;
      font-weight: bold;
      text-align: center;
    }
    .pv-table .ajourne-cell {
      background-color: #FEE2E2 !important;
      color: #991B1B !important;
      font-weight: bold;
      text-align: center;
    }
    .pv-table .note-danger {
      color: #DC2626;
      font-weight: bold;
    }

    /* Bloc Signatures */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }
    .signature-table td {
      width: 33.33%;
      text-align: center;
      vertical-align: top;
      font-size: 7.5pt;
      padding: 4px 10px;
    }
    .sig-title {
      font-weight: bold;
      color: #1E3A5F;
      border-bottom: 1px dashed #94A3B8;
      padding-bottom: 2px;
      margin-bottom: 30px;
    }
    .sig-cachet {
      font-size: 6.5pt;
      color: #94A3B8;
      font-style: italic;
    }

    /* Pied de page */
    .footer-text {
      text-align: center;
      font-size: 6.5pt;
      color: #64748B;
      border-top: 0.5px solid #E2E8F0;
      padding-top: 3px;
      margin-top: 6px;
    }
  </style>
</head>
<body>

  <!-- 1. En-tête Officiel -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 15%; text-align: left;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 40px; max-width: 100px;">
        <?php else: ?>
          <div style="font-weight: bold; color: #990000; font-size: 11pt;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 60%; text-align: center;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <div class="inst-subtitle">AGRÉÉ PAR L'ÉTAT ET LE FDFP &bull; ENSEIGNEMENT SUPÉRIEUR</div>
        <div class="inst-contacts">Direction des Études &bull; Commission des Examens, Notes et Délibérations</div>
      </td>
      <td style="width: 25%; text-align: right; font-size: 7pt; color: #475569;">
        <div><strong>Édition :</strong> <?= date('d/m/Y H:i') ?></div>
        <div><strong>Année :</strong> <?= htmlspecialchars($anneeLibelle ?? '-') ?></div>
        <div><strong>Période :</strong> <?= htmlspecialchars($selectedSemestreCode ?? '-') ?></div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- 2. Bannière Titre -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">PROCES-VERBAL DE DÉLIBÉRATION DES NOTES</div>
        <div class="banner-sub">Session Ordinaire &bull; Période : <?= htmlspecialchars($selectedSemestreLibelle ?? $selectedSemestreCode) ?> &bull; Année Académique <?= htmlspecialchars($anneeLibelle ?? '-') ?></div>
      </td>
    </tr>
  </table>

  <!-- 3. Métadonnées de Classe & KPIs Dynamiques -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 20%;"><span class="meta-label">Classe :</span> <span class="meta-value"><?= htmlspecialchars($classeInfo['libelle_classe'] ?? '-') ?></span></td>
      <td style="width: 22%;"><span class="meta-label">Filière :</span> <span class="meta-value"><?= htmlspecialchars($classeInfo['libelle_filiere'] ?? '-') ?></span></td>
      <td style="width: 18%;"><span class="meta-label">Niveau :</span> <span class="meta-value"><?= htmlspecialchars($classeInfo['libelle_niveau'] ?? '-') ?></span></td>
      <td style="width: 20%;"><span class="meta-label">Effectif Évalué :</span> <span class="meta-value"><?= (int)($statsClasse['totalEleves'] ?? 0) ?> élève(s)</span></td>
      <td style="width: 20%;"><span class="meta-label">Taux d'Admission :</span> <span class="meta-value" style="color: #15803D;"><?= number_format((float)($statsClasse['tauxAdmis'] ?? 0), 1) ?> %</span></td>
    </tr>
    <tr>
      <td><span class="meta-label">Moyenne Classe :</span> <span class="meta-value"><?= number_format((float)($statsClasse['moyenneGeneraleClasse'] ?? 0), 2) ?> /20</span></td>
      <td><span class="meta-label">Plus Forte Moyenne :</span> <span class="meta-value" style="color: #15803D;"><?= number_format((float)($statsClasse['moyenneMax'] ?? 0), 2) ?> /20</span></td>
      <td><span class="meta-label">Plus Faible Moyenne :</span> <span class="meta-value" style="color: #B91C1C;"><?= number_format((float)($statsClasse['moyenneMin'] ?? 0), 2) ?> /20</span></td>
      <td colspan="2"><span class="meta-label">Nombre de Matières :</span> <span class="meta-value"><?= count($matieres) ?> matière(s)</span></td>
    </tr>
  </table>

  <!-- 4. Tableau Matrice Récapitulatif -->
  <table class="pv-table">
    <thead>
      <tr>
        <th style="width: 28px;">Rang</th>
        <th style="width: 75px;">Matricule</th>
        <th style="text-align: left; width: 140px;">Nom & Prénom(s) Élève</th>
        <?php foreach ($matieres as $m): ?>
          <th title="<?= htmlspecialchars($m['libelle_matiere']) ?>">
            <?= htmlspecialchars(mb_strimwidth($m['libelle_matiere'], 0, 10, '..')) ?><br>
            <span style="font-weight: normal; font-size: 6pt; opacity: 0.9;">C.<?= (float)$m['coefficient'] ?></span>
          </th>
        <?php endforeach; ?>
        <th style="width: 48px;">Tot Pts</th>
        <th style="width: 52px;">Moyenne</th>
        <th style="width: 55px;">Décision</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($pvRows)): ?>
        <tr>
          <td colspan="<?= 6 + count($matieres) ?>" style="text-align: center; padding: 14px; color: #64748B;">
            Aucun étudiant noté trouvé pour cette classe et ce semestre.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($pvRows as $r): 
          $isAdmis = ($r['decision'] === 'Admis(e)');
        ?>
          <tr>
            <td style="text-align: center; font-weight: bold; color: #1E3A5F;">
              <?= $r['rang'] ?><sup><?= $r['rang'] == 1 ? 'er' : 'e' ?></sup>
            </td>
            <td style="font-family: monospace; font-weight: bold; color: #334155;">
              <?= htmlspecialchars($r['matricule'] ?: '-') ?>
            </td>
            <td style="font-weight: bold; color: #0F172A;">
              <?= htmlspecialchars($r['nom_prenom']) ?>
            </td>
            <?php foreach ($matieres as $m): 
              $val = $r['notes_matieres'][$m['code_matiere']] ?? null;
            ?>
              <td style="text-align: center; <?= ($val !== null && $val < 10) ? 'color: #DC2626; font-weight: bold;' : '' ?>">
                <?= $val !== null ? number_format($val, 2) : '-' ?>
              </td>
            <?php endforeach; ?>
            <td style="text-align: center; font-weight: bold; color: #334155;">
              <?= number_format($r['total_points'], 2) ?>
            </td>
            <td style="text-align: center; font-weight: 800; font-size: 8pt; color: <?= $isAdmis ? '#15803D' : '#991B1B' ?>;">
              <?= number_format($r['moyenne_generale'], 2) ?>
            </td>
            <td class="<?= $isAdmis ? 'admis-cell' : 'ajourne-cell' ?>">
              <?= htmlspecialchars($r['decision']) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- 5. Bloc de Signatures Officielles -->
  <table class="signature-table">
    <tr>
      <td>
        <div class="sig-title">Le Chef de Département / Enseignant</div>
        <div style="height: 38px;"></div>
        <div class="sig-cachet">Signature & Date</div>
      </td>
      <td>
        <div class="sig-title">Le Directeur des Études</div>
        <div style="height: 38px;"></div>
        <div class="sig-cachet">Signature, Visa & Cachet</div>
      </td>
      <td>
        <div class="sig-title">Le Président du Jury / Directeur Général</div>
        <div style="height: 38px;"></div>
        <div class="sig-cachet">Signature, Visa & Cachet</div>
      </td>
    </tr>
  </table>

  <!-- 6. Footer -->
  <div class="footer-text">
    GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION &bull; Document officiel généré par le Système Intégré de Délibérations &bull; Fait le <?= date('d/m/Y') ?>
  </div>

</body>
</html>
