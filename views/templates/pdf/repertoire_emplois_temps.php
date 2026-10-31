<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= !empty($isTeacherOnly) ? 'Mon Emploi du Temps' : 'Répertoire des Emplois du Temps' ?> - GEICG</title>
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
    .badge-code {
      color: #1E3A5F;
      font-weight: bold;
      font-size: 7.5pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
      letter-spacing: 0.3px;
    }
    .badge-volume {
      color: #1E40AF;
      font-weight: bold;
      font-size: 7.5pt;
      background-color: #EFF6FF;
      border: 0.5px solid #BFDBFE;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-statut-complet {
      color: #15803D;
      font-weight: bold;
      font-size: 7pt;
      background-color: #DCFCE7;
      border: 0.5px solid #86EFAC;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-statut-brouillon {
      color: #B45309;
      font-weight: bold;
      font-size: 7pt;
      background-color: #FEF3C7;
      border: 0.5px solid #FCD34D;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }

    /* Bloc de Signature */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
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
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; <?= !empty($isTeacherOnly) ? 'Mon Emploi du Temps' : 'Répertoire des Emplois du Temps' ?> &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
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
          Réf: REF-EDT-<?= date('Ymd-His') ?>
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
          <img src="<?= $logoPath ?>" style="max-height: 50px; max-width: 130px;" alt="Logo EICG">
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
        <div class="banner-title"><?= !empty($isTeacherOnly) ? 'MON EMPLOI DU TEMPS - RELEVÉ INDIVIDUEL' : 'RÉPERTOIRE GÉNÉRAL DES EMPLOIS DU TEMPS' ?></div>
        <div class="banner-sub"><?= !empty($isTeacherOnly) ? 'Synthèse des volumes horaires et enseignements dispensés par classe' : 'Registre officiel et suivi des plannings hebdomadaires des classes' ?></div>
      </td>
    </tr>
  </table>

  <?php
    $totalCreneauxGlobal = 0;
    $totalHeuresGlobal = 0;
    foreach ($classes as $clItem) {
        $totalCreneauxGlobal += (int)($clItem['nb_creneaux'] ?? 0);
        $totalHeuresGlobal += (float)($clItem['total_heures'] ?? 0);
    }
    $hGlobal = floor($totalHeuresGlobal);
    $mGlobal = round(($totalHeuresGlobal - $hGlobal) * 60);
    $formattedTotalGlobal = sprintf("%dh%02d", $hGlobal, $mGlobal);
  ?>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Année Académique :</span>
        <span class="meta-value"><?= htmlspecialchars($annee_libelle ?? 'Active') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Date d'édition :</span>
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Classes répertoriées :</span>
        <span class="meta-value"><?= count($classes) ?> classe(s)</span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Total Heures :</span>
        <span class="meta-value"><?= $formattedTotalGlobal ?> (<?= $totalCreneauxGlobal ?> créneaux)</span>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="meta-label">Édité par :</span>
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Direction des Études') ?></span>
      </td>
      <td colspan="2">
        <span class="meta-label">Contexte :</span>
        <span class="meta-value"><?= !empty($isTeacherOnly) ? 'Enseignant - Consultation individuelle restreinte' : 'Administration Générale / Direction des Études' ?></span>
      </td>
    </tr>
  </table>

  <!-- TABLEAU RÉPERTOIRE -->
  <table class="repertoire-table">
    <thead>
      <tr>
        <th style="width: 4%; text-align: center;">#</th>
        <th style="width: 20%;">Classe & Niveau</th>
        <th style="width: 18%;">Volume & Créneaux</th>
        <th style="width: 34%;"><?= !empty($isTeacherOnly) ? 'Matière(s) dispensée(s)' : 'Matières & Enseignants' ?></th>
        <th style="width: 12%; text-align: center;">Statut Planning</th>
        <th style="width: 12%; text-align: center;">Dernière MàJ</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($classes)): ?>
        <tr>
          <td colspan="6" class="text-center" style="padding: 16px; color: #64748B; font-style: italic;">
            Aucun emploi du temps n'est actuellement enregistré pour les critères sélectionnés.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($classes as $idx => $c): ?>
          <?php
            $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            $tMinutes = round(((float)($c['total_heures'] ?? 0)) * 60);
            $h = floor($tMinutes / 60);
            $m = $tMinutes % 60;
            $volHoraire = sprintf("%dh%02d", $h, $m);
            $isComplet = ((int)($c['nb_jours'] ?? 0) >= 5);
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;">
              <?= $idx + 1 ?>
            </td>
            <td>
              <strong style="color: #0F172A; font-size: 8pt;"><?= htmlspecialchars($c['libelle_classe'] ?? $c['code_classe'] ?? '-') ?></strong>
              <br>
              <span style="color: #64748B; font-size: 7pt;"><?= htmlspecialchars($c['libelle_niveau'] ?? 'Niveau non défini') ?></span>
            </td>
            <td>
              <span class="badge-volume"><?= $volHoraire ?></span>
              <br>
              <span style="color: #475569; font-size: 7pt; font-weight: bold;">
                <?= (int)($c['nb_creneaux'] ?? 0) ?> créneau(x) &bull; <?= (int)($c['nb_jours'] ?? 0) ?> jour(s)
              </span>
            </td>
            <td>
              <?php if (!empty($isTeacherOnly)): ?>
                <strong style="color: #1E3A5F; font-size: 7.5pt;"><?= (int)($c['nb_matieres'] ?? 0) ?> matière(s) assignée(s)</strong>
              <?php else: ?>
                <strong style="color: #1E3A5F; font-size: 7.5pt;"><?= (int)($c['nb_matieres'] ?? 0) ?> matière(s) &bull; <?= (int)($c['nb_profs'] ?? 0) ?> prof(s)</strong>
              <?php endif; ?>
              <br>
              <span style="color: #475569; font-size: 7pt; font-style: italic;">
                <?= htmlspecialchars(!empty($c['matieres_noms']) ? $c['matieres_noms'] : 'Aucune matière') ?>
              </span>
            </td>
            <td class="text-center">
              <?php if ($isComplet): ?>
                <span class="badge-statut-complet">Complet</span>
              <?php else: ?>
                <span class="badge-statut-brouillon">En saisie (<?= (int)($c['nb_jours'] ?? 0) ?>/6 j)</span>
              <?php endif; ?>
            </td>
            <td class="text-center" style="font-size: 7pt; color: #64748B;">
              <?= !empty($c['last_update']) ? date('d/m/Y H:i', strtotime($c['last_update'])) : '-' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURES OFFICIELLES -->
  <table class="signature-table">
    <tr>
      <td class="signature-box">
        <div class="signature-title"><?= !empty($isTeacherOnly) ? "L'Enseignant(e)" : "Le Directeur des Études" ?></div>
        <div style="height: 38px;"></div>
        <div class="signature-mention">Date et Signature précédées de la mention manuscrite</div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box">
        <div class="signature-title"><?= !empty($isTeacherOnly) ? "Le Directeur des Études" : "La Direction Générale" ?></div>
        <div style="height: 38px;"></div>
        <div class="signature-mention">Signature & Cachet Officiel de l'Établissement</div>
      </td>
    </tr>
  </table>

</body>
</html>

