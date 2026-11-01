<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre des Évaluations - GEICG</title>
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
      color: #1E293B;
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
      letter-spacing: 0.8px;
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
      padding: 3.5px 6px;
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

    /* KPIs Box */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .kpi-cell {
      border: 1px solid #CBD5E1;
      background-color: #FFFFFF;
      padding: 4px 8px;
      text-align: center;
    }
    .kpi-title {
      font-size: 7pt;
      font-weight: bold;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .kpi-val {
      font-size: 10.5pt;
      font-weight: bold;
      margin-top: 1px;
    }

    /* Table Principale */
    .repertoire-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    .repertoire-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      font-size: 7.5pt;
      text-transform: uppercase;
      padding: 4px 6px;
      border: 0.5px solid #0F2744;
      text-align: left;
    }
    .repertoire-table td {
      padding: 3.5px 6px;
      border: 0.5px solid #CBD5E1;
      font-size: 7.5pt;
      vertical-align: middle;
    }
    .row-even {
      background-color: #F8FAFC;
    }
    .row-odd {
      background-color: #FFFFFF;
    }
    .text-center {
      text-align: center;
    }
    .text-right {
      text-align: right;
    }

    /* Badges */
    .badge-eval {
      color: #1D4ED8;
      font-weight: bold;
      font-size: 7pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 5px;
      border-radius: 3px;
    }
    .badge-devoir {
      color: #B45309;
      font-weight: bold;
      font-size: 7pt;
      background-color: #FEF3C7;
      border: 0.5px solid #FDE68A;
      padding: 1.5px 5px;
      border-radius: 3px;
    }
    .badge-interro {
      color: #15803D;
      font-weight: bold;
      font-size: 7pt;
      background-color: #DCFCE7;
      border: 0.5px solid #86EFAC;
      padding: 1.5px 5px;
      border-radius: 3px;
    }

    /* Bloc de Signature */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }
    .signature-box {
      width: 48%;
      vertical-align: top;
      border: 0.5px solid #CBD5E1;
      background-color: #FAFAFA;
      padding: 6px 10px;
    }
    .signature-title {
      font-size: 8pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      text-align: center;
      border-bottom: 0.5px solid #CBD5E1;
      padding-bottom: 3px;
      margin-bottom: 22px;
    }
    .signature-mention {
      font-size: 7pt;
      font-style: italic;
      color: #64748B;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE FIXE POUR MPDF -->
  <htmlpageheader name="pageHeader">
    <div style="font-size: 7.5pt; color: #94A3B8; text-align: right; border-bottom: 0.5px solid #E2E8F0; padding-bottom: 2px;">
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Registre des Évaluations & Sessions de Notes &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          Document officiel GEICG &bull; Édité le <?= date('d/m/Y H:i:s') ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: REF-EVAL-<?= date('Ymd-His') ?>
        </td>
      </tr>
    </table>
  </htmlpagefooter>
  <sethtmlpagefooter name="pageFooter" value="on" />

  <!-- ENTÊTE DE L'ÉTABLISSEMENT -->
  <?php
    $logoPath = __DIR__ . '/../../../public/assets/images/logo/logo_eicg.jpg';
    if (!file_exists($logoPath)) {
      $logoPath = '/var/www/html/geicg/public/assets/images/logo/logo_eicg.jpg';
    }
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 20%; text-align: left;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 48px; max-width: 130px;" alt="Logo EICG">
        <?php else: ?>
          <div style="font-size: 13pt; font-weight: bold; color: #1E3A5F;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 80%; text-align: center;">
        <?php
          $nomEtab = 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION';
          if (!empty($etablissement['libelle_etablissement']) && !in_array(strtoupper(trim($etablissement['libelle_etablissement'])), ['GROUPE EICG', 'GEICG', 'ETABLISSEMENT A'], true)) {
              $nomEtab = $etablissement['libelle_etablissement'];
          }
        ?>
        <div class="inst-title"><?= htmlspecialchars($nomEtab) ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars(!empty($etablissement['numero_autorisation_etablissement']) ? 'AGRÉMENT N° ' . $etablissement['numero_autorisation_etablissement'] : 'AGRÉÉ PAR L\'ÉTAT ET LE FDFP') ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars(!empty($etablissement['adresse_etablissement']) && $etablissement['adresse_etablissement'] !== 'adresse' ? $etablissement['adresse_etablissement'] : 'Bouaké - Côte d\'Ivoire') ?>
          &nbsp;|&nbsp; Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99
        </div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title"><?= !empty($isTeacherOnly) ? 'REGISTRE DES ÉVALUATIONS - MES CLASSES ET MATIÈRES' : 'REGISTRE GÉNÉRAL DES ÉVALUATIONS & SESSIONS DE NOTES' ?></div>
        <div class="banner-sub">Synthèse officielle du suivi des épreuves, progression de saisie et moyennes des classes</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['annee'] ?? '-') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Semestre :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['semestre'] ?? 'Tous') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Niveau d'Études :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['niveau'] ?? 'Tous') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Classe :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['classe'] ?? 'Toutes') ?></span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="meta-label">Matière :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['matiere'] ?? 'Toutes') ?></span>
      </td>
      <td>
        <span class="meta-label">Type d'Épreuve :</span><br>
        <span class="meta-value"><?= htmlspecialchars($filtres_labels['type'] ?? 'Tous') ?></span>
      </td>
      <td>
        <span class="meta-label">Édité par :</span><br>
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Direction des Études') ?></span>
      </td>
      <td>
        <span class="meta-label">Date d'édition :</span><br>
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE SYNTHÈSE / KPIS -->
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Total Sessions</div>
        <div class="kpi-val" style="color: #0F172A;"><?= (int)($kpis['total_sessions'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Total Inscrits</div>
        <div class="kpi-val" style="color: #4338CA;"><?= (int)($kpis['total_inscrits'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Copies Saisies</div>
        <div class="kpi-val" style="color: #1D4ED8;"><?= (int)($kpis['total_copies'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Taux de Complétion</div>
        <div class="kpi-val" style="color: #059669;"><?= number_format($kpis['taux_completion'] ?? 0, 1) ?> %</div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Moyenne Générale</div>
        <div class="kpi-val" style="color: <?= (($kpis['moyenne_globale'] ?? 0) >= 10) ? '#059669' : '#DC2626' ?>;">
          <?= ($kpis['moyenne_globale'] !== null) ? number_format($kpis['moyenne_globale'], 2) . ' / 20' : '-' ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DU REGISTRE -->
  <table class="repertoire-table">
    <thead>
      <tr>
        <th style="width: 24px;" class="text-center">#</th>
        <th style="width: 80px;">Classe</th>
        <th>Évaluation</th>
        <th style="width: 55px;" class="text-center">Type</th>
        <th>Matière</th>
        <th style="width: 70px;">Semestre</th>
        <th style="width: 38px;" class="text-center">Coef</th>
        <th style="width: 65px;" class="text-center">Date Enreg.</th>
        <th style="width: 85px;" class="text-center">Progression</th>
        <th style="width: 70px;" class="text-center">Moyenne</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($sessions)): ?>
        <tr>
          <td colspan="10" class="text-center" style="padding: 18px; color: #64748B; font-style: italic;">
            Aucune session d'évaluation ne correspond aux critères de sélection.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($sessions as $idx => $s): ?>
          <?php
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $typ = strtoupper($s['type_composition'] ?? 'EXAMEN');
            $badgeClass = 'badge-eval';
            if ($typ === 'DEVOIR') $badgeClass = 'badge-devoir';
            elseif ($typ === 'INTERROGATION') $badgeClass = 'badge-interro';

            // Date format
            $rawDate = $s['date_enregistrement'] ?? ($s['date_enregistrement_note'] ?? ($s['date_composition'] ?? ($s['created_at_composition'] ?? '')));
            $formattedDate = '-';
            if (!empty($rawDate) && $rawDate !== '0000-00-00' && $rawDate !== '0000-00-00 00:00:00') {
                $clean = explode(' ', $rawDate)[0];
                $parts = explode('-', $clean);
                if (count($parts) === 3) {
                    $formattedDate = $parts[2] . '/' . $parts[1] . '/' . $parts[0];
                }
            }

            // Progression
            $nb = (int)($s['nb_notes'] ?? 0);
            $tot = (int)($s['total_etudiants'] ?? 0);
            $progText = $tot > 0 ? "{$nb} / {$tot}" : "{$nb}";

            // Moyenne
            $moyText = '-';
            $moyColor = '#64748B';
            if ($s['moyenne_classe'] !== null && $s['moyenne_classe'] !== '') {
                $moyVal = (float)$s['moyenne_classe'];
                $moyText = number_format($moyVal, 2) . ' / 20';
                $moyColor = $moyVal >= 10 ? '#15803D' : '#DC2626';
            }
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td><strong><?= htmlspecialchars($s['libelle_classe'] ?? $s['code_classe'] ?? '-') ?></strong></td>
            <td><strong><?= htmlspecialchars($s['libelle_composition'] ?? $s['code_composition'] ?? '-') ?></strong></td>
            <td class="text-center"><span class="<?= $badgeClass ?>"><?= htmlspecialchars($typ) ?></span></td>
            <td><?= htmlspecialchars($s['libelle_matiere'] ?? '-') ?></td>
            <td><?= htmlspecialchars($s['libelle_semestre'] ?? '-') ?></td>
            <td class="text-center" style="font-weight: bold;"><?= number_format((float)($s['coefficient'] ?? 1), 2) ?></td>
            <td class="text-center" style="font-weight: bold; color: #1E3A5F;"><?= $formattedDate ?></td>
            <td class="text-center" style="font-weight: bold;"><?= $progText ?></td>
            <td class="text-center" style="font-weight: bold; color: <?= $moyColor ?>;"><?= $moyText ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURE -->
  <table class="signature-table">
    <tr>
      <td class="signature-box">
        <div class="signature-title">L'Enseignant / Responsable de Matière</div>
        <div class="signature-mention">Nom, Signature et Date</div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box">
        <div class="signature-title">La Direction des Études & Examens</div>
        <div class="signature-mention">Visa, Cachet officiel et Date</div>
      </td>
    </tr>
  </table>

</body>
</html>

