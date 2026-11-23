<?php
require_once __DIR__ . '/../../public/inc/header.php';

$affectations = $affectations ?? [];
$maquettes = $maquettes ?? [];
$stats = $stats ?? [];
$anneeCode = (string)($anneeCode ?? '');
$anneeLibelle = (string)($anneeLibelle ?? '');
$encryptedId = (string)($encryptedId ?? '');

$idMatiere = (int)($item['id_matiere'] ?? 0);
$codeMatiere = (string)($item['code_matiere'] ?? '');
$libelleMatiere = (string)($item['libelle_matiere'] ?? '');
$statutMatiere = (string)($item['statut_matiere'] ?? STATUTS::ACTIF);
$isActif = ($statutMatiere === STATUTS::ACTIF);

$createdAt = !empty($item['created_at_matiere']) ? date('d/m/Y', strtotime($item['created_at_matiere'])) : '';
$updatedAt = !empty($item['updated_at_matiere']) ? date('d/m/Y', strtotime($item['updated_at_matiere'])) : '';

$totalClasses = (int)($stats['total_classes'] ?? 0);
$totalProfs = (int)($stats['total_profs'] ?? 0);
$totalSalles = (int)($stats['total_salles'] ?? 0);
$totalCreneaux = (int)($stats['total_creneaux'] ?? 0);
$totalVolumeHebdo = (float)($stats['total_volume_hebdo'] ?? 0);

$totalMaquettes = (int)($stats['total_maquettes'] ?? count($maquettes));
$maquettesGen = (int)($stats['maquettes_generales'] ?? 0);
$maquettesPro = (int)($stats['maquettes_professionnelles'] ?? 0);
$coefMin = $stats['coef_min'] ?? null;
$coefMax = $stats['coef_max'] ?? null;

$totalNotes = (int)($stats['total_notes'] ?? 0);
$totalEvaluations = (int)($stats['total_evaluations'] ?? 0);
$moyenneGlobale = $stats['moyenne_globale'] ?? null;
$totalAbsencesCount = (int)($stats['total_absences_count'] ?? 0);
$totalAbsencesHeures = (float)($stats['total_absences_heures'] ?? 0);
$totalAbsencesInjust = (float)($stats['total_absences_injustifiees'] ?? 0);

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};
$fmtCoef = static function ($c): string {
    $val = (float)$c;
    return (fmod($val, 1.0) === 0.0) ? (string)(int)$val : number_format($val, 2, ',', ' ');
};

$coefLabel = '-';
if ($coefMin !== null && $coefMax !== null) {
    $coefLabel = ((float)$coefMin === (float)$coefMax)
        ? ('Coef. ' . $fmtCoef($coefMin))
        : ('Coef. ' . $fmtCoef($coefMin) . ' à ' . $fmtCoef($coefMax));
}

$canManageMatieres = Auth::can(PERMISSIONS::MANAGE_MATIERES);
$canViewClasses = Auth::can(PERMISSIONS::VIEW_CLASSES);
$canViewEnseignants = Auth::can(PERMISSIONS::VIEW_ENSEIGNANTS);
$canViewEmplois = Auth::can(PERMISSIONS::VIEW_EMPLOI_TEMPS);
$canManageEmplois = Auth::can(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
$canViewMaquettes = Auth::can(PERMISSIONS::VIEW_MAQUETTES);
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
              <i data-lucide="book-open" style="width: 21px; height: 21px;"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
              <?= htmlspecialchars($libelleMatiere ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeMatiere ?: '-') ?></code>
            <?php if ($isActif): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Active
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Inactive
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
          <a href="<?= url('matiere/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <?php if ($canViewEmplois): ?>
            <a href="<?= url('emploi/list') ?>" style="background: #F0F9FF; color: #0369A1; border: 1.5px solid #BAE6FD; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Emplois du temps
            </a>
          <?php endif; ?>
          <?php if ($canViewMaquettes): ?>
            <a href="<?= url('maquette/list') ?>" style="background: #FAF5FF; color: #7E22CE; border: 1.5px solid #E9D5FF; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="layout-list" style="width: 16px; height: 16px;"></i> Maquettes
            </a>
          <?php endif; ?>
          <?php if ($canManageMatieres): ?>
            <button type="button" id="btn-toggle-statut-matiere-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'pause-circle' : 'play-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Désactiver' : 'Activer' ?>
            </button>
            <button type="button" id="btn-edit-matiere-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; cursor: pointer;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 22px;">

        <!-- KPI 1 : Classes & Volume Hebdo -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="layers" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Classes Rattachées</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalClasses ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">classe<?= $totalClasses > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Volume : <strong style="color: #0369A1;"><?= htmlspecialchars($fmtHeures($totalVolumeHebdo)) ?>/sem.</strong></span>
            <span><strong style="color: #0F172A;"><?= $totalCreneaux ?></strong> séance<?= $totalCreneaux > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 2 : Enseignants & Salles -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="users" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Enseignants Mobilisés</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalProfs ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">professeur<?= $totalProfs > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Salles mobilisées :</span>
            <span><strong style="color: #15803D;"><?= $totalSalles ?></strong> salle<?= $totalSalles > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 3 : Maquettes & Coefficients -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="layout-list" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Maquettes / Parcours</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalMaquettes ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">parcours</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span><strong style="color: #7E22CE;"><?= htmlspecialchars($coefLabel) ?></strong></span>
            <span><strong><?= $maquettesGen ?></strong> Gén. &bull; <strong><?= $maquettesPro ?></strong> Pro.</span>
          </div>
        </div>

        <!-- KPI 4 : Évaluations & Assiduité -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="clipboard-check" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Évaluations & Notes</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalNotes ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">note<?= $totalNotes > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span><?= $totalEvaluations ?> éval. &bull; Moyenne :</span>
              <strong style="color: <?= ($moyenneGlobale !== null && $moyenneGlobale >= 10) ? '#15803D' : (($moyenneGlobale !== null) ? '#B91C1C' : '#64748B') ?>;">
                <?= $moyenneGlobale !== null ? (number_format($moyenneGlobale, 2, ',', ' ') . '/20') : '-' ?>
              </strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Absences (<?= $totalAbsencesCount ?>) :</span>
              <span>
                <strong style="color: #0F172A;"><?= htmlspecialchars($fmtHeures($totalAbsencesHeures)) ?></strong>
                <?php if ($totalAbsencesInjust > 0): ?>
                  <span style="color: #B91C1C; font-weight: 700;">(<?= htmlspecialchars($fmtHeures($totalAbsencesInjust)) ?> inj.)</span>
                <?php endif; ?>
              </span>
            </div>
          </div>
        </div>

      </div>

      <!-- SECTION 1 : RÉPARTITION PÉDAGOGIQUE PAR CLASSE & ENSEIGNANT -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Répartition par Classe & Enseignant (<span id="count-visible-affectations"><?= count($affectations) ?></span>)
          </h3>

          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <?php if (!empty($affectations)): ?>
              <div style="position: relative; min-width: 230px;">
                <input type="text" id="search-matiere-affectations" placeholder="Rechercher classe, filière, prof, salle..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
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

        <?php if (empty($affectations)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Cette matière n'est encore programmée dans aucune classe pour cette session académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-matiere-affectations" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Filière & Niveau</th>
                  <th style="padding: 10px 12px;">Enseignant Titulaire</th>
                  <th style="padding: 10px 12px;">Créneaux & Salle(s)</th>
                  <th style="padding: 10px 12px; text-align: center;">Séances</th>
                  <th style="padding: 10px 12px; text-align: right;">Volume Hebdo</th>
                  <th style="padding: 10px 12px; text-align: right; width: 90px;">Accès</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $sumCreneaux = 0;
                  $sumVolume = 0.0;
                  foreach ($affectations as $idx => $a):
                    $nbCr = (int)($a['nb_creneaux'] ?? 0);
                    $volH = (float)($a['volume_horaire'] ?? 0);
                    $sumCreneaux += $nbCr;
                    $sumVolume += $volH;
                    $profNom = trim(($a['nom_prof'] ?? '') . ' ' . ($a['prenom_prof'] ?? ''));
                ?>
                  <tr class="matiere-affectation-row" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; font-weight: 800; color: #0F172A; white-space: nowrap;">
                      <?php if ($canViewClasses && !empty($a['encrypted_classe_id'])): ?>
                        <a href="<?= url('classe/details/' . $a['encrypted_classe_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($a['libelle_classe'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($a['libelle_classe'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; color: #334155;">
                      <div style="font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($a['libelle_filiere'] ?? '-') ?></div>
                      <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($a['libelle_niveau'] ?? '-') ?></div>
                    </td>
                    <td style="padding: 11px 12px;">
                      <div style="color: #0F172A; font-weight: 700;"><?= htmlspecialchars($profNom !== '' ? $profNom : 'Non attribué') ?></div>
                      <?php if (!empty($a['grade_enseignant'])): ?>
                        <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($a['grade_enseignant']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px;">
                      <?php if (!empty($a['creneaux_resume'])): ?>
                        <div style="font-weight: 600; color: #334155; font-size: 12.5px;"><?= htmlspecialchars($a['creneaux_resume']) ?></div>
                      <?php endif; ?>
                      <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
                        Salle : <strong style="color: #475569;"><?= htmlspecialchars(!empty($a['salles_noms']) ? $a['salles_noms'] : '-') ?></strong>
                      </div>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; color: #475569; font-weight: 600; white-space: nowrap;">
                      <?= $nbCr ?> séance<?= $nbCr > 1 ? 's' : '' ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <span style="background: #EFF6FF; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 9px; border-radius: 6px; font-weight: 800; font-size: 12px;">
                        <?= $volH > 0 ? htmlspecialchars($fmtHeures($volH)) : '-' ?>
                      </span>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <?php if ($canViewClasses && !empty($a['encrypted_classe_id'])): ?>
                          <a href="<?= url('classe/details/' . $a['encrypted_classe_id']) ?>" title="Ouvrir la fiche classe" aria-label="Ouvrir la fiche classe" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($canViewEnseignants && !empty($a['encrypted_enseignant_id'])): ?>
                          <a href="<?= url('enseignant/details/' . $a['encrypted_enseignant_id']) ?>" title="Ouvrir la fiche enseignant" aria-label="Ouvrir la fiche enseignant" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BBF7D0; background: #F0FDF4; color: #15803D; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
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
                  <td colspan="5" style="padding: 11px 12px; font-weight: 800; color: #1E3A5F; text-align: right;">Total Hebdomadaire :</td>
                  <td style="padding: 11px 12px; text-align: center; font-weight: 800; color: #0F172A;"><?= $sumCreneaux ?> séance<?= $sumCreneaux > 1 ? 's' : '' ?></td>
                  <td style="padding: 11px 12px; text-align: right; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($fmtHeures($sumVolume)) ?></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- SECTION 2 : MAQUETTES PÉDAGOGIQUES & COEFFICIENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layout-list" style="width: 18px; height: 18px; color: #7E22CE;"></i>
            Maquettes Pédagogiques & Coefficients (<span id="count-visible-maquettes"><?= count($maquettes) ?></span>)
          </h3>

          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <?php if (!empty($maquettes)): ?>
              <select id="filter-matiere-categorie" style="padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC; font-weight: 600;">
                <option value="">Toutes les catégories</option>
                <option value="<?= CATEGORIES_MATIERES::GENERALE ?>">Matières Générales</option>
                <option value="<?= CATEGORIES_MATIERES::PROFESSIONNELLE ?>">Matières Professionnelles</option>
              </select>
              <div style="position: relative; min-width: 210px;">
                <input type="text" id="search-matiere-maquettes" placeholder="Filtrer filière, niveau..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
              </div>
            <?php endif; ?>
            <?php if ($canViewMaquettes): ?>
              <a href="<?= url('maquette/list') ?>" style="background: #FFFFFF; color: #7E22CE; border: 1.5px solid #E9D5FF; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="settings-2" style="width: 14px; height: 14px;"></i> Gérer les maquettes
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if (empty($maquettes)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Cette matière n'est encore associée à aucune maquette pédagogique pour cette session académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-matiere-maquettes" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Code</th>
                  <th style="padding: 10px 12px;">Filière</th>
                  <th style="padding: 10px 12px; text-align: center;">Type</th>
                  <th style="padding: 10px 12px;">Niveau</th>
                  <th style="padding: 10px 12px; text-align: center;">Catégorie</th>
                  <th style="padding: 10px 12px; text-align: center;">Coefficient</th>
                  <th style="padding: 10px 12px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($maquettes as $idx => $mq):
                  $cat = strtoupper((string)($mq['categorie_matiere'] ?? ''));
                  $isPro = ($cat === CATEGORIES_MATIERES::PROFESSIONNELLE);
                  $typeFil = strtoupper((string)($mq['type_filiere'] ?? ''));
                  $mqActif = (($mq['statut_maquette'] ?? '') === STATUTS::ACTIF);
                ?>
                  <tr class="matiere-maquette-row" data-categorie="<?= htmlspecialchars($cat) ?>" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <code style="font-weight: 700; color: #475569; background: #F1F5F9; padding: 2px 7px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($mq['code_maquette'] ?? '-') ?></code>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 700; color: #0F172A;">
                      <?= htmlspecialchars($mq['libelle_filiere'] ?? '-') ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                      <?php if ($typeFil === 'INDUSTRIELLE'): ?>
                        <span style="background: #E0F2FE; color: #0369A1; padding: 3px 9px; border-radius: 6px; font-weight: 700; font-size: 11px;">Industrielle</span>
                      <?php elseif ($typeFil === 'TERTIAIRE'): ?>
                        <span style="background: #FEF3C7; color: #B45309; padding: 3px 9px; border-radius: 6px; font-weight: 700; font-size: 11px;">Tertiaire</span>
                      <?php else: ?>
                        <span style="color: #94A3B8;">-</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 600; color: #334155; white-space: nowrap;">
                      <?= htmlspecialchars($mq['libelle_niveau'] ?? '-') ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                      <?php if ($isPro): ?>
                        <span style="background: #FAF5FF; color: #7E22CE; border: 1px solid #E9D5FF; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11.5px;">Professionnelle</span>
                      <?php else: ?>
                        <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11.5px;">Générale</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                      <span style="background: #F8FAFC; color: #0F172A; border: 1px solid #CBD5E1; padding: 3px 10px; border-radius: 6px; font-weight: 800; font-size: 12px;">
                        <?= htmlspecialchars($fmtCoef($mq['coefficient'] ?? 0)) ?>
                      </span>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                      <?php if ($mqActif): ?>
                        <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Actif</span>
                      <?php else: ?>
                        <span style="background: #FEE2E2; color: #B91C1C; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Inactif</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<?php if ($canManageMatieres): ?>
<!-- MODAL ÉDITION RAPIDE DE LA MATIÈRE -->
<div id="modal-matiere-detail" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la Matière
      </h3>
      <button type="button" class="btn-close-modal-matiere-detail" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-matiere-detail" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_matiere" value="<?= $idMatiere ?>">

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Code Matière
        </label>
        <input type="text" value="<?= htmlspecialchars($codeMatiere) ?>" disabled style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0; background: #F8FAFC; color: #64748B; font-weight: 700; font-size: 13px;">
      </div>

      <div style="margin-bottom: 18px;">
        <label for="detail_matiere_libelle" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Intitulé / Nom de la Matière <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_matiere" id="detail_matiere_libelle" required value="<?= htmlspecialchars($libelleMatiere) ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; color: #0F172A;">
      </div>

      <div style="margin-bottom: 22px;">
        <label for="detail_matiere_statut" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_matiere" id="detail_matiere_statut" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px; color: #0F172A; background: #FFFFFF;">
          <option value="<?= STATUTS::ACTIF ?>" <?= $isActif ? 'selected' : '' ?>>Actif</option>
          <option value="<?= STATUTS::INACTIF ?>" <?= !$isActif ? 'selected' : '' ?>>Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn-close-modal-matiere-detail" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 9px 18px; cursor: pointer;">Annuler</button>
        <button type="submit" id="btn-save-matiere-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1px solid #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script type="application/json" id="matieres-details-config">
<?= json_encode([
    'racine'         => RACINE,
    'matiereId'      => $idMatiere,
    'isActif'        => $isActif,
    'libelleMatiere' => $libelleMatiere,
    'csrfToken'      => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/filieres.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
