<?php
require_once __DIR__ . '/../../public/inc/header.php';

$planning = $planning ?? [];
$stats = $stats ?? [];
$anneeCode = (string)($anneeCode ?? '');
$anneeLibelle = (string)($anneeLibelle ?? '');
$encryptedId = (string)($encryptedId ?? '');

$idSalle = (int)($item['id_salle'] ?? 0);
$codeSalle = (string)($item['code_salle'] ?? '');
$libelleSalle = (string)($item['libelle_salle'] ?? '');
$capaciteRaw = $item['capacite_salle'] ?? null;
$hasCapacite = ($capaciteRaw !== null && $capaciteRaw !== '' && (int)$capaciteRaw > 0);
$capaciteSalle = $hasCapacite ? (int)$capaciteRaw : null;
$statutSalle = (string)($item['statut_salle'] ?? STATUTS::ACTIF);
$isActif = ($statutSalle === STATUTS::ACTIF);

$createdAt = !empty($item['created_at']) ? date('d/m/Y', strtotime($item['created_at'])) : '';
$updatedAt = !empty($item['updated_at']) ? date('d/m/Y', strtotime($item['updated_at'])) : '';

$totalCreneaux = (int)($stats['total_creneaux'] ?? count($planning));
$totalHeuresHebdo = (float)($stats['total_heures_hebdo'] ?? 0);
$totalJoursActifs = (int)($stats['total_jours_actifs'] ?? 0);
$totalClasses = (int)($stats['total_classes'] ?? 0);
$totalMatieres = (int)($stats['total_matieres'] ?? 0);
$totalEnseignants = (int)($stats['total_enseignants'] ?? 0);
$maxEffectif = (int)($stats['max_effectif_classe'] ?? 0);
$capaciteDepassee = ($hasCapacite && $maxEffectif > $capaciteSalle);

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};
$fmtHeureShort = static function ($t): string {
    $str = (string)$t;
    return strlen($str) >= 5 ? substr($str, 0, 5) : $str;
};

// Répartition dynamique par jour de la semaine
$joursSemaine = [
    'lundi'    => 'Lundi',
    'mardi'    => 'Mardi',
    'mercredi' => 'Mercredi',
    'jeudi'    => 'Jeudi',
    'vendredi' => 'Vendredi',
    'samedi'   => 'Samedi',
];
$statsParJour = [];
foreach ($joursSemaine as $k => $lbl) {
    $statsParJour[$k] = ['label' => $lbl, 'creneaux' => 0, 'heures' => 0.0];
}
foreach ($planning as $p) {
    $jKey = strtolower(trim((string)($p['jour'] ?? '')));
    if ($jKey !== '' && !isset($statsParJour[$jKey])) {
        $statsParJour[$jKey] = ['label' => ucfirst($jKey), 'creneaux' => 0, 'heures' => 0.0];
    }
    if ($jKey !== '') {
        $statsParJour[$jKey]['creneaux']++;
        $statsParJour[$jKey]['heures'] += (float)($p['duree_heures'] ?? 0);
    }
}

$canManageSalles = Auth::can(PERMISSIONS::MANAGE_SALLES);
$canPrintSalles = Auth::can(PERMISSIONS::PRINT_SALLES) || Auth::can(PERMISSIONS::VIEW_SALLES) || Auth::can(PERMISSIONS::PRINT_OFFRE_ACADEMIQUE);
$canViewEmplois = Auth::can(PERMISSIONS::VIEW_EMPLOI_TEMPS);
$canManageEmplois = Auth::can(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
$canViewClasses = Auth::can(PERMISSIONS::VIEW_CLASSES);
$canViewMatieres = Auth::can(PERMISSIONS::VIEW_MATIERES);
$canViewEnseignants = Auth::can(PERMISSIONS::VIEW_ENSEIGNANTS);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="building-2" style="width: 21px; height: 21px;"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
              <?= htmlspecialchars($libelleSalle ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeSalle ?: '-') ?></code>
            <?php if ($isActif): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Disponible / Active
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Indisponible
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 8px 0 0 0;">
            <?php if ($anneeLibelle !== ''): ?>
              Session académique : <strong style="color: #1E3A5F;"><?= htmlspecialchars($anneeLibelle) ?></strong>
            <?php endif; ?>
            <?php if ($createdAt !== ''): ?>
              <?= $anneeLibelle !== '' ? ' &bull; ' : '' ?>Créée le <strong style="color: #334155;"><?= htmlspecialchars($createdAt) ?></strong>
            <?php endif; ?>
            <?php if ($updatedAt !== ''): ?>
              &bull; Mise à jour le <strong style="color: #334155;"><?= htmlspecialchars($updatedAt) ?></strong>
            <?php endif; ?>
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('salle/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <?php if ($canPrintSalles): ?>
            <a href="<?= url('salle/imprimerPdf') ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Répertoire PDF
            </a>
          <?php endif; ?>
          <?php if ($canViewEmplois): ?>
            <a href="<?= url('emploi/list') ?>" style="background: #F0F9FF; color: #0369A1; border: 1.5px solid #BAE6FD; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Emplois du temps
            </a>
          <?php endif; ?>
          <?php if ($canManageSalles): ?>
            <button type="button" id="btn-toggle-statut-salle-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'pause-circle' : 'play-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Désactiver' : 'Activer' ?>
            </button>
            <button type="button" id="btn-edit-salle-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; cursor: pointer;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 22px;">

        <!-- KPI 1 : Capacité d'Accueil -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $capaciteDepassee ? '#FEE2E2' : '#EFF6FF' ?>; color: <?= $capaciteDepassee ? '#B91C1C' : '#1D4ED8' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="armchair" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Capacité d'Accueil</div>
              <?php if ($hasCapacite): ?>
                <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                  <?= $capaciteSalle ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">place<?= $capaciteSalle > 1 ? 's' : '' ?></span>
                </div>
              <?php else: ?>
                <div style="font-size: 18px; font-weight: 800; color: #64748B; line-height: 1.2; margin-top: 4px;">
                  Non définie
                </div>
              <?php endif; ?>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #475569;">
            <span>Effectif max classe :</span>
            <strong style="color: <?= $capaciteDepassee ? '#B91C1C' : '#0F172A' ?>;">
              <?= $maxEffectif > 0 ? ($maxEffectif . ' étud.') : '-' ?>
              <?= $capaciteDepassee ? ' (Capacité dépassée)' : '' ?>
            </strong>
          </div>
        </div>

        <!-- KPI 2 : Occupation Hebdomadaire -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="clock" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Occupation Hebdo</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= htmlspecialchars($fmtHeures($totalHeuresHebdo)) ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">/ sem.</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span><strong style="color: #7E22CE;"><?= $totalCreneaux ?></strong> créneau<?= $totalCreneaux > 1 ? 'x' : '' ?></span>
            <span><strong style="color: #0F172A;"><?= $totalJoursActifs ?></strong> jour<?= $totalJoursActifs > 1 ? 's' : '' ?> occupé<?= $totalJoursActifs > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 3 : Classes & Matières -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #E0F2FE; color: #0369A1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="layers" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Classes Accueillies</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalClasses ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">classe<?= $totalClasses > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Matières dispensées :</span>
            <span><strong style="color: #0369A1;"><?= $totalMatieres ?></strong> matière<?= $totalMatieres > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 4 : Enseignants Intervenants -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="users" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Enseignants Intervenants</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalEnseignants ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">professeur<?= $totalEnseignants > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>État de la salle :</span>
            <strong style="color: <?= $isActif ? '#15803D' : '#B91C1C' ?>;"><?= $isActif ? 'Opérationnelle' : 'Hors service' ?></strong>
          </div>
        </div>

      </div>

      <!-- SECTION 1 : RÉPARTITION PAR JOUR DE LA SEMAINE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="calendar-days" style="width: 17px; height: 17px; color: #1E3A5F;"></i>
            Occupation Hebdomadaire par Jour
          </h3>
          <span style="font-size: 12px; color: #64748B;">Cliquez sur un jour pour filtrer le planning</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;">
          <?php foreach ($statsParJour as $jourKey => $jInfo):
            $hasSlots = ($jInfo['creneaux'] > 0);
          ?>
            <button type="button" class="salle-day-pill" data-jour="<?= htmlspecialchars($jourKey) ?>" style="text-align: left; background: <?= $hasSlots ? '#EFF6FF' : '#F8FAFC' ?>; border: 1.5px solid <?= $hasSlots ? '#BFDBFE' : '#E2E8F0' ?>; border-radius: 10px; padding: 11px 13px; cursor: pointer; transition: all 0.15s ease;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12.5px; font-weight: 800; color: <?= $hasSlots ? '#1E3A5F' : '#64748B' ?>;"><?= htmlspecialchars($jInfo['label']) ?></span>
                <span style="font-size: 11px; font-weight: 800; color: <?= $hasSlots ? '#0369A1' : '#94A3B8' ?>;">
                  <?= $hasSlots ? htmlspecialchars($fmtHeures($jInfo['heures'])) : 'Libre' ?>
                </span>
              </div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 4px; font-weight: 600;">
                <?= $hasSlots ? ($jInfo['creneaux'] . ' créneau' . ($jInfo['creneaux'] > 1 ? 'x' : '')) : '0 séance' ?>
              </div>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- SECTION 2 : PLANNING D'OCCUPATION DE LA SALLE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="calendar" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Planning d'Occupation (<span id="count-visible-creneaux"><?= count($planning) ?></span>)
          </h3>

          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <?php if (!empty($planning)): ?>
              <select id="filter-salle-jour" style="padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC; font-weight: 600;">
                <option value="">Tous les jours</option>
                <?php foreach ($statsParJour as $jourKey => $jInfo): ?>
                  <option value="<?= htmlspecialchars($jourKey) ?>"><?= htmlspecialchars($jInfo['label']) ?> (<?= $jInfo['creneaux'] ?>)</option>
                <?php endforeach; ?>
              </select>
              <div style="position: relative; min-width: 220px;">
                <input type="text" id="search-salle-planning" placeholder="Rechercher classe, matière, prof..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
              </div>
            <?php endif; ?>
            <?php if ($canManageEmplois): ?>
              <a href="<?= url('emploi/formulaire') ?>" style="background: #1E3A5F; color: #FFFFFF; border-radius: 8px; padding: 7px 14px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="calendar-plus" style="width: 15px; height: 15px;"></i> Programmer un créneau
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if (empty($planning)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Aucun cours n'est actuellement programmé dans cette salle pour cette session académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-salle-planning" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Jour</th>
                  <th style="padding: 10px 12px;">Créneau Horaire</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px;">Enseignant</th>
                  <th style="padding: 10px 12px; text-align: right; width: 120px;">Accès</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $sumHeures = 0.0;
                  foreach ($planning as $idx => $p):
                    $jLower = strtolower(trim((string)($p['jour'] ?? '')));
                    $duree = (float)($p['duree_heures'] ?? 0);
                    $sumHeures += $duree;
                    $profNom = trim(($p['nom_prof'] ?? '') . ' ' . ($p['prenom_prof'] ?? ''));
                ?>
                  <tr class="salle-planning-row" data-jour="<?= htmlspecialchars($jLower) ?>" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <span style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px; font-weight: 800; font-size: 11.5px;">
                        <?= htmlspecialchars(ucfirst($jLower)) ?>
                      </span>
                    </td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <span style="font-family: monospace; font-weight: 700; color: #0F172A; font-size: 12.5px;">
                        <?= htmlspecialchars($fmtHeureShort($p['heure_debut'] ?? '')) ?> - <?= htmlspecialchars($fmtHeureShort($p['heure_fin'] ?? '')) ?>
                      </span>
                      <?php if ($duree > 0): ?>
                        <span style="margin-left: 6px; background: #F1F5F9; color: #475569; padding: 2px 6px; border-radius: 5px; font-size: 11px; font-weight: 700;">
                          <?= htmlspecialchars($fmtHeures($duree)) ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px;">
                      <div style="font-weight: 800; color: #0F172A;">
                        <?php if ($canViewClasses && !empty($p['encrypted_classe_id'])): ?>
                          <a href="<?= url('classe/details/' . $p['encrypted_classe_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($p['libelle_classe'] ?? '-') ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($p['libelle_classe'] ?? '-') ?>
                        <?php endif; ?>
                      </div>
                      <?php if (!empty($p['libelle_filiere']) || !empty($p['libelle_niveau'])): ?>
                        <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                          <?= htmlspecialchars(trim(($p['libelle_filiere'] ?? '') . (!empty($p['libelle_niveau']) ? ' · ' . $p['libelle_niveau'] : ''))) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 700; color: #1E3A5F;">
                      <?php if ($canViewMatieres && !empty($p['encrypted_matiere_id'])): ?>
                        <a href="<?= url('matiere/details/' . $p['encrypted_matiere_id']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($p['libelle_matiere'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($p['libelle_matiere'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px;">
                      <div style="color: #0F172A; font-weight: 600;"><?= htmlspecialchars($profNom !== '' ? $profNom : 'Non attribué') ?></div>
                      <?php if (!empty($p['grade_enseignant'])): ?>
                        <div style="font-size: 11px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($p['grade_enseignant']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <?php if ($canViewClasses && !empty($p['encrypted_classe_id'])): ?>
                          <a href="<?= url('classe/details/' . $p['encrypted_classe_id']) ?>" title="Ouvrir la fiche classe" aria-label="Ouvrir la fiche classe" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="graduation-cap" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($canViewMatieres && !empty($p['encrypted_matiere_id'])): ?>
                          <a href="<?= url('matiere/details/' . $p['encrypted_matiere_id']) ?>" title="Ouvrir la fiche matière" aria-label="Ouvrir la fiche matière" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E9D5FF; background: #FAF5FF; color: #7E22CE; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="book-open" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($canViewEnseignants && !empty($p['encrypted_enseignant_id'])): ?>
                          <a href="<?= url('enseignant/details/' . $p['encrypted_enseignant_id']) ?>" title="Ouvrir la fiche enseignant" aria-label="Ouvrir la fiche enseignant" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BBF7D0; background: #F0FDF4; color: #15803D; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="user" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background: #F8FAFC; border-top: 2px solid #E2E8F0;">
                  <td colspan="2" style="padding: 11px 12px; font-weight: 800; color: #1E3A5F;">Total Hebdomadaire :</td>
                  <td style="padding: 11px 12px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($fmtHeures($sumHeures)) ?></td>
                  <td colspan="4" style="padding: 11px 12px; text-align: right; font-weight: 700; color: #475569;">
                    <?= count($planning) ?> créneau<?= count($planning) > 1 ? 'x' : '' ?> programmé<?= count($planning) > 1 ? 's' : '' ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<?php if ($canManageSalles): ?>
<!-- MODAL ÉDITION RAPIDE DE LA SALLE -->
<div id="modal-salle-detail" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Salle de Cours
      </h3>
      <button type="button" class="btn-close-modal-salle-detail" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-salle-detail" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_salle" value="<?= $idSalle ?>">

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Code Salle</label>
        <input type="text" value="<?= htmlspecialchars($codeSalle) ?>" disabled style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0; background: #F8FAFC; color: #64748B; font-weight: 700; font-size: 13px;">
      </div>

      <div style="margin-bottom: 16px;">
        <label for="detail_salle_libelle" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Nom / Numéro de salle <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_salle" id="detail_salle_libelle" required value="<?= htmlspecialchars($libelleSalle) ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; color: #0F172A;">
      </div>

      <div style="margin-bottom: 16px;">
        <label for="detail_salle_capacite" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Capacité d'accueil (places)
        </label>
        <input type="number" min="1" step="1" name="capacite_salle" id="detail_salle_capacite" value="<?= $hasCapacite ? (int)$capaciteSalle : '' ?>" placeholder="Ex: 40" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; color: #0F172A;">
      </div>

      <div style="margin-bottom: 22px;">
        <label for="detail_salle_statut" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_salle" id="detail_salle_statut" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; color: #0F172A; background: #FFFFFF;">
          <option value="<?= STATUTS::ACTIF ?>" <?= $isActif ? 'selected' : '' ?>>Actif (Disponible)</option>
          <option value="<?= STATUTS::INACTIF ?>" <?= !$isActif ? 'selected' : '' ?>>Inactif (Indisponible)</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn-close-modal-salle-detail" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 9px 18px; cursor: pointer;">Annuler</button>
        <button type="submit" id="btn-save-salle-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1px solid #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script type="application/json" id="salles-details-config">
<?= json_encode([
    'racine'       => RACINE,
    'salleId'      => $idSalle,
    'isActif'      => $isActif,
    'libelleSalle' => $libelleSalle,
    'csrfToken'    => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/emplois_temps.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
