<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Registre des Bulletins & Notes - GEICG</title>
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

    .repertoire-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .repertoire-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-size: 7.5pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      padding: 4px 5px;
      border: 0.5px solid #0F172A;
      text-align: left;
    }
    .repertoire-table td {
      font-size: 7.5pt;
      padding: 3.5px 5px;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
    }
    .row-even {
      background-color: #F8FAFC;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .badge {
      display: inline-block;
      padding: 1.5px 4.5px;
      border-radius: 3px;
      font-size: 7pt;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE OFFICIEL DE L'ÉTABLISSEMENT -->
  <table class="header-table">
    <tr>
      <td style="width: 70%;">
        <div class="inst-title"><?= htmlspecialchars($etablissement['nom_etablissement'] ?? "GROUPE ECOLE D'INGENIEURS ET DE COMMERCE") ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars($etablissement['nom_court'] ?? 'GEICG') ?> &bull; Direction Académique & des Études</div>
        <div class="inst-contacts">
          <?= htmlspecialchars($etablissement['adresse_etablissement'] ?? 'B.P. 518 Gagnoa') ?> &bull; 
          Tél : <?= htmlspecialchars($etablissement['telephone_etablissement'] ?? '+225 27 34 77 22 28') ?> &bull; 
          Email : <?= htmlspecialchars($etablissement['email_etablissement'] ?? 'contact@geicg.ci') ?>
        </div>
      </td>
      <td style="width: 30%; text-align: right;">
        <div style="font-size: 7.5pt; color: #475569;">
          <div>Édité le : <strong><?= htmlspecialchars($date_impression ?? date('d/m/Y H:i')) ?></strong></div>
          <div style="margin-top: 1px;">Par : <strong><?= htmlspecialchars($editeur_nom ?? 'Direction des Études') ?></strong></div>
          <div style="margin-top: 1px;">Année : <strong><?= htmlspecialchars($filtres_labels['annee'] ?? '-') ?></strong></div>
        </div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>

  <!-- BANNIÈRE DE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">REGISTRE & SYNTHÈSE DES BULLETINS DE NOTES</div>
        <div class="banner-sub">État récapitulatif des inscriptions, volumes de notes et résultats académiques par classe</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DES FILTRES APPLIQUÉS -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 20%;"><span class="meta-label">Année Académique :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['annee'] ?? 'Toutes') ?></span></td>
      <td style="width: 20%;"><span class="meta-label">Semestre :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['semestre'] ?? 'Tous') ?></span></td>
      <td style="width: 20%;"><span class="meta-label">Niveau d'Études :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['niveau'] ?? 'Tous') ?></span></td>
      <td style="width: 20%;"><span class="meta-label">Classe Cible :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['classe'] ?? 'Toutes') ?></span></td>
      <td style="width: 20%;"><span class="meta-label">Statut Notes :</span> <span class="meta-value"><?= htmlspecialchars($filtres_labels['statut_notes'] ?? 'Tous') ?></span></td>
    </tr>
  </table>

  <!-- STATISTIQUES & INDICATEURS CLÉS (KPIS) -->
  <table class="kpi-table">
    <tr>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Total Inscrits</div>
        <div class="kpi-val" style="color: #1E3A5F;"><?= (int)($kpis['total_inscrits'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Avec Notes</div>
        <div class="kpi-val" style="color: #0284C7;"><?= (int)($kpis['total_avec_notes'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Sans Notes</div>
        <div class="kpi-val" style="color: #B45309;"><?= (int)($kpis['total_sans_notes'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Admis (Moy &ge; 10)</div>
        <div class="kpi-val" style="color: #15803D;"><?= (int)($kpis['total_admis'] ?? 0) ?></div>
      </td>
      <td class="kpi-cell" style="width: 20%;">
        <div class="kpi-title">Moyenne Globale</div>
        <div class="kpi-val" style="color: #0F172A;"><?= number_format((float)($kpis['moyenne_globale'] ?? 0), 2, ',', ' ') ?> / 20</div>
      </td>
    </tr>
  </table>

  <!-- TABLE DES ÉTUDIANTS ET RÉSULTATS -->
  <table class="repertoire-table">
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 80px;">Matricule</th>
        <th>Nom & Prénoms de l'Étudiant</th>
        <th style="width: 30px;" class="text-center">Sexe</th>
        <th style="width: 100px;">Classe</th>
        <th style="width: 110px;">Filière</th>
        <th style="width: 60px;" class="text-center">Notes</th>
        <th style="width: 70px;" class="text-center">Moyenne</th>
        <th style="width: 75px;" class="text-center">Décision</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($etudiants)): ?>
        <tr>
          <td colspan="9" class="text-center" style="padding: 18px; color: #64748B; font-style: italic;">
            Aucun étudiant inscrit trouvé pour les critères sélectionnés.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($etudiants as $idx => $e): ?>
          <?php 
            $moy = $e['moyenne_semestre'] !== null ? (float)$e['moyenne_semestre'] : null;
            $nbNotes = (int)($e['nb_notes'] ?? 0);
          ?>
          <tr class="<?= ($idx % 2 === 1) ? 'row-even' : '' ?>">
            <td class="text-center" style="color: #64748B; font-weight: bold;"><?= $idx + 1 ?></td>
            <td><strong style="color: #1E3A5F;"><?= htmlspecialchars($e['matricule_etudiant'] ?? '-') ?></strong></td>
            <td><strong><?= htmlspecialchars($e['etudiant_nom'] ?? '-') ?></strong></td>
            <td class="text-center"><?= htmlspecialchars($e['sexe_etudiant'] ?? '-') ?></td>
            <td><strong><?= htmlspecialchars($e['classe_nom'] ?? '-') ?></strong></td>
            <td><span style="color: #475569;"><?= htmlspecialchars($e['filiere_nom'] ?? '-') ?></span></td>
            <td class="text-center">
              <?php if ($nbNotes > 0): ?>
                <span class="badge" style="background-color: #DCFCE7; color: #15803D;"><?= $nbNotes ?> note(s)</span>
              <?php else: ?>
                <span class="badge" style="background-color: #F1F5F9; color: #64748B;">0 note</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($moy !== null): ?>
                <strong style="color: <?= $moy >= 10 ? '#15803D' : '#DC2626' ?>; font-size: 8pt;">
                  <?= number_format($moy, 2, ',', ' ') ?> / 20
                </strong>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">-</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($moy !== null): ?>
                <?php if ($moy >= 10): ?>
                  <span class="badge" style="background-color: #DCFCE7; color: #15803D;">Admis(e)</span>
                <?php else: ?>
                  <span class="badge" style="background-color: #FEE2E2; color: #B91C1C;">Ajourné(e)</span>
                <?php endif; ?>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">En attente</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- PIED DE PAGE / VISA -->
  <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
    <tr>
      <td style="width: 50%; vertical-align: top; font-size: 7.5pt; color: #64748B;">
        <em>Document académique officiel généré automatiquement par le progiciel GEICG.</em>
      </td>
      <td style="width: 50%; text-align: right; vertical-align: top; font-size: 8pt;">
        <strong>Le Directeur des Études & des Examens</strong>
        <div style="height: 35px;"></div>
        <div style="font-size: 7.5pt; color: #94A3B8;">(Signature et Cachet)</div>
      </td>
    </tr>
  </table>

</body>
</html>

