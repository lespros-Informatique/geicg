<?php
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    }
}

$logs = isset($logs) && is_array($logs) ? $logs : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];

// Logo
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

$editeurNom = class_exists('Auth') && Auth::check() ? Auth::fullName() : 'Direction du Système d\'Information';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Journal d'Audit & Registre des Logs - GEICG</title>
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
      color: #1E293B;
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
      margin-bottom: 5px;
    }

    /* Bannière Titre */
    .banner-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
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

    /* Grille Logs */
    .logs-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2px;
      margin-bottom: 4px;
    }
    .logs-table th {
      background-color: #1E3A5F;
      color: #FFFFFF;
      font-weight: bold;
      padding: 5px 4px;
      font-size: 7.5pt;
      text-align: left;
      border: 1px solid #1E3A5F;
      text-transform: uppercase;
    }
    .logs-table td {
      padding: 3.5px 4px;
      font-size: 7.5pt;
      border: 0.5px solid #CBD5E1;
      vertical-align: middle;
    }
    .logs-table tr:nth-child(even) {
      background-color: #F8FAFC;
    }

    /* Badges actions */
    .badge-act {
      font-weight: bold;
      font-size: 7pt;
      padding: 2px 4px;
      border-radius: 3px;
      display: inline-block;
      text-align: center;
    }
    .act-connexion { background-color: #DCFCE7; color: #15803D; }
    .act-deconnexion { background-color: #F1F5F9; color: #475569; }
    .act-ajout { background-color: #DBEAFE; color: #1D4ED8; }
    .act-modif { background-color: #FEF3C7; color: #B45309; }
    .act-suppr { background-color: #FEE2E2; color: #DC2626; }
    .act-print { background-color: #F3E8FF; color: #7E22CE; }
    .act-other { background-color: #F1F5F9; color: #475569; }

    .footer-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 7.5pt;
      color: #64748B;
      border-top: 0.5px solid #CBD5E1;
      padding-top: 3px;
    }
  </style>
</head>
<body>

  <!-- EN-TÊTE INSTITUTIONNEL -->
  <table class="header-table">
    <tr>
      <td style="width: 15%; text-align: left;">
        <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 44px; max-width: 110px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#1E3A5F; font-size:14px;">GEICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 85%; text-align: center;">
        <div class="inst-title"><?= htmlspecialchars($etablissement['libelle_etablissement'] ?? ($etablissement['nom_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION')) ?></div>
        <div class="inst-subtitle"><?= htmlspecialchars($etablissement['numero_autorisation'] ?? 'AGREE PAR L\'ETAT ET LE FDFP') ?></div>
        <div class="inst-contacts">
          <?= htmlspecialchars(!empty($etablissement['adresse_etablissement']) ? $etablissement['adresse_etablissement'] : 'Bouaké quartier Kennedy, route ancien ADDR') ?> 
          &bull; <?= htmlspecialchars($etablissement['boite_postale'] ?? '01 BP 960 Bouaké 01') ?>
          <br> Contacts : <?= htmlspecialchars(!empty($etablissement['contacts_formatted']) ? $etablissement['contacts_formatted'] : '27 31 62 40 57 / 07 79 37 37 38') ?>
        </div>
      </td>
    </tr>
  </table>

  <div class="divider-line"></div>
  <br> 

  <!-- BANNIÈRE TITRE -->
  <table class="banner-table">
    <tr>
      <td>
        <div class="banner-title">JOURNAL D'AUDIT & REGISTRE OFFICIEL DES ACTIVITÉS SYSTÈME</div>
      </td>
    </tr>
  </table>

  <!-- CARTOUCHE DE SYNTHÈSE & FILTRES -->
  <table class="meta-box-table">
    <tr>
      <td style="width: 25%;">
        <span class="meta-label">Action filtrée :</span> 
        <span class="meta-value"><?= !empty($filters['action']) ? htmlspecialchars($filters['action']) : 'Toutes les actions' ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Module :</span> 
        <span class="meta-value"><?= !empty($filters['module']) ? htmlspecialchars(strtoupper($filters['module'])) : 'Tous les modules' ?></span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Période :</span> 
        <span class="meta-value">
          <?php if (!empty($filters['date_debut']) || !empty($filters['date_fin'])): ?>
            <?= !empty($filters['date_debut']) ? date('d/m/Y', strtotime($filters['date_debut'])) : 'Début' ?> 
            au 
            <?= !empty($filters['date_fin']) ? date('d/m/Y', strtotime($filters['date_fin'])) : 'Fin' ?>
          <?php else: ?>
            Historique complet
          <?php endif; ?>
        </span>
      </td>
      <td style="width: 25%;">
        <span class="meta-label">Total entrées :</span> 
        <span class="meta-value" style="color: #1D4ED8;"><?= count($logs) ?> opération(s)</span>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span class="meta-label">Utilisateur ciblé :</span> 
        <span class="meta-value">
          <?= !empty($targetUserLabel) ? htmlspecialchars($targetUserLabel) : 'Tous les comptes utilisateurs' ?>
        </span>
      </td>
      <td colspan="2">
        <span class="meta-label">Imprimé par :</span> 
        <span class="meta-value"><?= htmlspecialchars($editeurNom) ?></span>
        &bull; le <strong><?= date('d/m/Y à H:i') ?></strong>
      </td>
    </tr>
  </table>

  <!-- TABLEAU DES LOGS -->
  <table class="logs-table">
    <thead>
      <tr>
        <th style="width: 4%; text-align: center;">N°</th>
        <th style="width: 13%;">Date & Heure</th>
        <th style="width: 17%;">Utilisateur (Auteur)</th>
        <th style="width: 11%; text-align: center;">Action</th>
        <th style="width: 10%;">Module</th>
        <th style="width: 13%;">Cible / Réf</th>
        <th style="width: 22%;">Description</th>
        <th style="width: 10%; text-align: center;">Adresse IP</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($logs)): ?>
        <?php $idx = 1; foreach ($logs as $l): ?>
          <?php
            $act = strtoupper(trim($l['action'] ?? ''));
            $badgeClass = 'act-other';
            if ($act === 'CONNEXION') $badgeClass = 'act-connexion';
            elseif ($act === 'DECONNEXION') $badgeClass = 'act-deconnexion';
            elseif ($act === 'AJOUT') $badgeClass = 'act-ajout';
            elseif ($act === 'MODIFICATION') $badgeClass = 'act-modif';
            elseif ($act === 'SUPPRESSION') $badgeClass = 'act-suppr';
            elseif ($act === 'IMPRESSION') $badgeClass = 'act-print';
          ?>
          <tr>
            <td style="text-align: center; color: #64748B;"><?= $idx++ ?></td>
            <td style="font-weight: bold;"><?= !empty($l['created_at']) ? date('d/m/Y H:i:s', strtotime($l['created_at'])) : '-' ?></td>
            <td>
              <strong><?= htmlspecialchars($l['user_nom'] ?: 'Système') ?></strong>
              <?php if (!empty($l['user_code']) && $l['user_code'] !== '-'): ?>
                <div style="font-size: 6.5pt; color: #64748B; font-family: monospace;"><?= htmlspecialchars($l['user_code']) ?></div>
              <?php endif; ?>
            </td>
            <td style="text-align: center;">
              <span class="badge-act <?= $badgeClass ?>"><?= htmlspecialchars($act) ?></span>
            </td>
            <td><strong style="color: #1E3A5F;"><?= htmlspecialchars(strtoupper($l['module'] ?? '-')) ?></strong></td>
            <td style="font-family: monospace; color: #334155;"><?= htmlspecialchars($l['entite_id'] ?? '-') ?></td>
            <td><?= htmlspecialchars($l['description'] ?? '-') ?></td>
            <td style="text-align: center; font-family: monospace; font-size: 7pt; color: #64748B;"><?= htmlspecialchars($l['ip_address'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" style="text-align: center; padding: 12px; color: #94A3B8; font-style: italic;">
            Aucune opération enregistrée ne correspond aux critères de sélection.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- PIED DE PAGE -->
  <table class="footer-table">
    <tr>
      <td style="width: 50%; text-align: left;">
        Document officiel généré automatiquement par le Système GEICG &bull; Traçabilité & Audit Trail
      </td>
      <td style="width: 50%; text-align: right;">
        Édité le <?= date('d/m/Y à H:i:s') ?> &bull; Page {PAGENO}/{nbpg}
      </td>
    </tr>
  </table>

</body>
</html>

