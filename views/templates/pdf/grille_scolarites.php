<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Grille des Frais de Scolarité - GEICG</title>
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

    /* Résumé Chiffré / KPI */
    .kpi-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .kpi-cell {
      padding: 5px 8px;
      text-align: center;
      background: #FFFFFF;
      border: 1px solid #CBD5E1;
      border-radius: 4px;
    }
    .kpi-num {
      font-size: 11pt;
      font-weight: bold;
      color: #1E3A5F;
    }
    .kpi-text {
      font-size: 7pt;
      color: #475569;
      text-transform: uppercase;
      font-weight: bold;
      letter-spacing: 0.3px;
    }

    /* Titres de Sections */
    .section-header-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 6px;
      background-color: #E2E8F0;
      border-left: 3.5px solid #1E3A5F;
    }
    .section-header-table td {
      padding: 4px 8px;
      font-size: 8.5pt;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    /* Tableaux de Données */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }
    .data-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-size: 7.5pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      padding: 4.5px 6px;
      border: 0.5px solid #1E3A5F;
      text-align: left;
    }
    .data-table td {
      padding: 4.5px 6px;
      font-size: 8pt;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
    }
    .row-even {
      background-color: #FFFFFF;
    }
    .row-odd {
      background-color: #F8FAFC;
    }
    .text-center {
      text-align: center;
    }
    .text-end {
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
    .badge-affecte {
      color: #15803D;
      font-weight: bold;
      font-size: 7pt;
      background-color: #DCFCE7;
      border: 0.5px solid #86EFAC;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }
    .badge-non-affecte {
      color: #475569;
      font-weight: bold;
      font-size: 7pt;
      background-color: #F1F5F9;
      border: 0.5px solid #CBD5E1;
      padding: 1.5px 6px;
      border-radius: 3px;
      display: inline-block;
    }

    /* Bloc de Signature */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
      page-break-inside: avoid;
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
      margin-bottom: 24px;
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
      <?= htmlspecialchars($etablissement['libelle_etablissement'] ?? 'GROUPE EICG') ?> &bull; Grille des Frais de Scolarité &bull; <?= htmlspecialchars($annee_libelle ?? 'Année Académique Active') ?>
    </div>
  </htmlpageheader>
  <sethtmlpageheader name="pageHeader" value="on" show-this-page="0" />

  <!-- PIED DE PAGE FIXE POUR MPDF -->
  <htmlpagefooter name="pageFooter">
    <table style="width: 100%; border-top: 0.5px solid #CBD5E1; font-size: 7.5pt; color: #64748B; padding-top: 4px;">
      <tr>
        <td style="width: 40%; text-align: left;">
          Document généré par le Système GEICG &bull; <?= date('d/m/Y H:i:s') ?>
        </td>
        <td style="width: 20%; text-align: center; font-weight: bold;">
          Page {PAGENO} / {nbpg}
        </td>
        <td style="width: 40%; text-align: right;">
          Réf: REF-SCOL-<?= date('Ymd-His') ?>
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
      <td style="width: 22%; text-align: left;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 52px; max-width: 135px;" alt="Logo EICG">
        <?php else: ?>
          <div style="font-size: 14pt; font-weight: bold; color: #1E3A5F;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 78%; text-align: center;">
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
        <div class="banner-title">GRILLE DES FRAIS DE SCOLARITÉ</div>
        <div class="banner-sub">BARÈME DES DROITS DE SCOLARITÉ PAR FILIÈRE ET NIVEAU D'ÉTUDES</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE MÉTADONNÉES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 33%;">
        <span class="meta-label">Année Académique :</span> 
        <span class="meta-value" style="color: #0369A1; font-size: 9pt;"><?= htmlspecialchars($annee_libelle ?? 'Active') ?></span>
      </td>
      <td style="width: 34%;">
        <span class="meta-label">Date d'édition :</span> 
        <span class="meta-value"><?= date('d/m/Y à H:i') ?></span>
      </td>
      <td style="width: 33%;">
        <span class="meta-label">Édité par :</span> 
        <span class="meta-value"><?= htmlspecialchars($editeur_nom ?? 'Service Comptabilité') ?></span>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="meta-label">Établissement :</span> 
        <span class="meta-value"><?= htmlspecialchars(!empty($etablissement['sigle_etablissement']) ? $etablissement['sigle_etablissement'] : 'GROUPE EICG') ?></span>
      </td>
      <td>
        <span class="meta-label">Critères de filtre :</span> 
        <span class="meta-value" style="font-size: 7.5pt; color: #475569;">
          <?php
            $filtreInfo = [];
            if (!empty($filtres['filiere_code'])) $filtreInfo[] = 'Filière: ' . htmlspecialchars($filtres['filiere_code']);
            if (!empty($filtres['niveau_code'])) $filtreInfo[] = 'Niveau: ' . htmlspecialchars($filtres['niveau_code']);
            if (!empty($filtres['affectation_etat'])) $filtreInfo[] = 'Régime: ' . ($filtres['affectation_etat'] === 'affecte' ? 'Affecté' : 'Privé');
            echo !empty($filtreInfo) ? implode(' | ', $filtreInfo) : 'Tous les critères';
          ?>
        </span>
      </td>
    </tr>
  </table>

  <!-- KPI / RÉSUMÉ CHIFFRÉ -->
  <?php
    $totalGrilles = count($scolarites ?? []);
    $nbAffectes = 0;
    $nbNonAffectes = 0;
    foreach (($scolarites ?? []) as $sc) {
      if (($sc['affectation_etat'] ?? '') === 'affecte') {
        $nbAffectes++;
      } else {
        $nbNonAffectes++;
      }
    }
  ?>
  <!-- KPI / RÉSUMÉ CHIFFRÉ STYLISÉ -->
  <table style="width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 8px;">
    <tr>
      <td style="width: 33.33%; background-color: #F8FAFC; border: 1px solid #CBD5E1; border-top: 3px solid #1E3A5F; padding: 5px 8px; text-align: center; border-radius: 4px;">
        <div style="font-size: 13pt; font-weight: bold; color: #1E3A5F; line-height: 1.1;"><?= $totalGrilles ?></div>
        <div style="font-size: 7.2pt; font-weight: bold; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px;">Grilles Tarifaires</div>
        <div style="font-size: 6.2pt; color: #64748B; margin-top: 1px;">Offre tarifaire globale</div>
      </td>
      <td style="width: 33.33%; background-color: #F0FDF4; border: 1px solid #BBF7D0; border-top: 3px solid #16A34A; padding: 5px 8px; text-align: center; border-radius: 4px;">
        <div style="font-size: 13pt; font-weight: bold; color: #15803D; line-height: 1.1;"><?= $nbAffectes ?></div>
        <div style="font-size: 7.2pt; font-weight: bold; color: #15803D; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px;">Tarifs Affectés (État)</div>
        <div style="font-size: 6.2pt; color: #166534; margin-top: 1px;">Bourse & prise en charge publique</div>
      </td>
      <td style="width: 33.33%; background-color: #F0F9FF; border: 1px solid #BAE6FD; border-top: 3px solid #0284C7; padding: 5px 8px; text-align: center; border-radius: 4px;">
        <div style="font-size: 13pt; font-weight: bold; color: #0369A1; line-height: 1.1;"><?= $nbNonAffectes ?></div>
        <div style="font-size: 7.2pt; font-weight: bold; color: #0369A1; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px;">Tarifs Privés (Non Affectés)</div>
        <div style="font-size: 6.2pt; color: #075985; margin-top: 1px;">Inscriptions directes privées</div>
      </td>
    </tr>
  </table>

  <!-- SECTION : GRILLE TARIFAIRE DES FRAIS DE SCOLARITÉ -->
  <table class="section-header-table">
    <tr>
      <td>GRILLE TARIFAIRE DES FRAIS DE SCOLARITÉ</td>
    </tr>
  </table>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 6%; text-align: center;">#</th>
        <th style="width: 44%;">Filière</th>
        <th style="width: 18%;">Niveau d'Études</th>
        <th style="width: 16%; text-align: center;">Régime d'Affectation</th>
        <th style="width: 16%; text-align: right;">Montant Annuel</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($scolarites)): ?>
        <tr>
          <td colspan="5" class="text-center" style="padding: 12px; color: #64748B; font-style: italic;">
            Aucune grille tarifaire de scolarité ne correspond aux critères sélectionnés.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($scolarites as $idx => $sc): 
          $rowClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
          $isAffecte = (($sc['affectation_etat'] ?? '') === 'affecte');
        ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center" style="font-weight: bold; color: #64748B;"><?= $idx + 1 ?></td>
            <td style="font-weight: bold; color: #0F172A;"><?= htmlspecialchars($sc['libelle_filiere'] ?? $sc['filiere_code'] ?? 'Non définie') ?></td>
            <td style="color: #1E3A5F; font-weight: 600;"><?= htmlspecialchars($sc['libelle_niveau'] ?? $sc['niveau_code'] ?? '-') ?></td>
            <td class="text-center">
              <?php if ($isAffecte): ?>
                <span class="badge-affecte">Affecté (État)</span>
              <?php else: ?>
                <span class="badge-non-affecte">Non Affecté (Privé)</span>
              <?php endif; ?>
            </td>
            <td class="text-end" style="font-weight: bold; color: #0F172A;">
              <?= number_format((float)($sc['montant_scolarite'] ?? 0), 0, ',', ' ') ?> FCFA
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- BLOC DE SIGNATURE ET VALIDATION -->
  <table class="signature-table">
    <tr>
      <td class="signature-box">
        <div class="signature-title">LE SERVICE COMPTABILITÉ & TRÉSORERIE</div>
        <div style="height: 48px;"></div>
        <div class="signature-mention">Signature & Cachet de l'Agent Comptable</div>
      </td>
      <td style="width: 4%;"></td>
      <td class="signature-box">
        <div class="signature-title">LA DIRECTION GÉNÉRALE</div>
        <div style="height: 48px;"></div>
        <div class="signature-mention">Approbation & Cachet de l'Établissement</div>
      </td>
    </tr>
  </table>

</body>
</html>
