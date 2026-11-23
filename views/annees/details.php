<?php
require_once __DIR__ . '/../../public/inc/header.php';

$stats = $stats ?? [];
$classes = $classes ?? [];
$semestres = $semestres ?? [];
$stAnnee = (string)($item['statut_annee'] ?? 'planifie');

$isActif = ($stAnnee === STATUTS::ACTIF);
$isPlanifie = ($stAnnee === 'planifie');
$isCloture = ($stAnnee === STATUTS::CLOTURE || (!$isActif && !$isPlanifie));
$isEditable = ($isActif || $isPlanifie);

$codeAnnee = (string)($item['code_annee'] ?? '');
$libelleAnnee = (string)($item['libelle_annee'] ?? '');
$dateDebutRaw = $item['date_debut_annee'] ?? '';
$dateFinRaw = $item['date_fin_annee'] ?? '';
$dateDebutFmt = !empty($dateDebutRaw) ? date('d/m/Y', strtotime($dateDebutRaw)) : '-';
$dateFinFmt = !empty($dateFinRaw) ? date('d/m/Y', strtotime($dateFinRaw)) : '-';
$createdFmt = !empty($item['created_at_annee']) ? date('d/m/Y à H:i', strtotime($item['created_at_annee'])) : '-';
$updatedFmt = !empty($item['updated_at_annee']) ? date('d/m/Y à H:i', strtotime($item['updated_at_annee'])) : '';

// Calcul dynamique de la durée et de la progression temporelle
$dureeLabel = '-';
$progressPct = 0;
$progressLabel = '';
if (!empty($dateDebutRaw) && !empty($dateFinRaw)) {
    $tsDeb = strtotime($dateDebutRaw);
    $tsFin = strtotime($dateFinRaw);
    $tsNow = strtotime(date('Y-m-d'));
    if ($tsDeb && $tsFin && $tsFin > $tsDeb) {
        $totalDays = (int)round(($tsFin - $tsDeb) / 86400);
        $monthsApprox = max(1, (int)round($totalDays / 30.4375));
        $dureeLabel = $monthsApprox . ' mois (' . $totalDays . ' j)';

        if ($isCloture || $tsNow >= $tsFin) {
            $progressPct = 100;
            $progressLabel = $isCloture ? 'Année clôturée' : 'Période achevée';
        } elseif ($tsNow <= $tsDeb) {
            $progressPct = 0;
            $daysLeft = (int)ceil(($tsDeb - $tsNow) / 86400);
            $progressLabel = 'Démarre dans ' . $daysLeft . ' j';
        } else {
            $elapsed = $tsNow - $tsDeb;
            $progressPct = min(100, max(1, (int)round(($elapsed / ($tsFin - $tsDeb)) * 100)));
            $daysRemaining = (int)ceil(($tsFin - $tsNow) / 86400);
            $progressLabel = $progressPct . '% écoulé (' . $daysRemaining . ' j restants)';
        }
    }
}

$totalEtudiants = (int)($stats['total_etudiants'] ?? 0);
$totalAffectes = (int)($stats['total_affectes'] ?? 0);
$totalNonAffectes = (int)($stats['total_non_affectes'] ?? 0);
$totalHommes = (int)($stats['total_hommes'] ?? 0);
$totalFemmes = (int)($stats['total_femmes'] ?? 0);
$totalClasses = (int)($stats['total_classes'] ?? count($classes));
$totalCapacite = (int)($stats['total_capacite'] ?? 0);
$tauxRemplissage = $totalCapacite > 0 ? min(100, (int)round(($totalEtudiants / $totalCapacite) * 100)) : 0;
$totalSemAll = (int)($stats['total_semestres_all'] ?? count($semestres));
$totalSemClot = (int)($stats['total_semestres_clotures'] ?? 0);
$totalEvals = (int)($stats['total_evaluations'] ?? 0);
$totalAbsHeures = (float)($stats['total_absences_heures'] ?? 0);
$totalRecouvrement = (float)($stats['total_recouvrement'] ?? 0);

$coefS1 = isset($item['coef_semestre1']) ? (int)$item['coef_semestre1'] : '';
$coefS2 = isset($item['coef_semestre2']) ? (int)$item['coef_semestre2'] : '';

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};

$canManageAnnees = Auth::can(PERMISSIONS::MANAGE_ANNEES);
$canViewEtudiants = Auth::can(PERMISSIONS::VIEW_ETUDIANTS);
$canViewClasses = Auth::can(PERMISSIONS::VIEW_CLASSES);
$canViewSemestres = Auth::can(PERMISSIONS::VIEW_SEMESTRES);
$canViewPaiements = Auth::can(PERMISSIONS::VIEW_PAIEMENTS);
$canViewEmplois = Auth::can(PERMISSIONS::VIEW_EMPLOI_TEMPS);
$canViewNotes = Auth::can(PERMISSIONS::VIEW_NOTES);
$canViewAbsences = Auth::can(PERMISSIONS::VIEW_ABSENCES);
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
              <i data-lucide="calendar-range" style="width: 20px; height: 20px;"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
              Année Académique <?= htmlspecialchars($libelleAnnee ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeAnnee ?: '-') ?></code>
            <?php if ($isActif): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Active en cours
              </span>
            <?php elseif ($isPlanifie): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i> En préparation
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="lock" style="width: 14px; height: 14px;"></i> Clôturée
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 8px 0 0 0;">
            Période : <strong style="color: #0F172A;"><?= htmlspecialchars($dateDebutFmt) ?> &rarr; <?= htmlspecialchars($dateFinFmt) ?></strong>
            &bull; Bilan consolidé des effectifs, classes, semestres et activités pédagogiques
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('annee/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
          </a>
          <?php if ($canViewEtudiants && $codeAnnee !== ''): ?>
            <a href="<?= url('etudiant/imprimerPdf') . '?annee_code=' . urlencode($codeAnnee) ?>" target="_blank" style="background: #F8FAFC; color: #1E3A5F; border: 1.5px solid #BFDBFE; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;" title="Imprimer le registre nominatif des étudiants de cette année">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Registre Étudiants
            </a>
          <?php endif; ?>
          <?php if ($canManageAnnees && $isEditable): ?>
            <button type="button" id="btn-toggle-statut-annee-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'lock' : 'power' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Clôturer l\'année' : 'Activer l\'année' ?>
            </button>
            <button type="button" id="btn-edit-annee-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; cursor: pointer;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI PRINCIPALES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 22px;">

        <!-- KPI 1 : Effectifs Étudiants -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="users" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Étudiants Inscrits</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= number_format($totalEtudiants, 0, ',', ' ') ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 5px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Affectés / Privés :</span>
              <span><strong style="color: #15803D;"><?= $totalAffectes ?></strong> / <strong style="color: #0369A1;"><?= $totalNonAffectes ?></strong></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Répartition Genre :</span>
              <span><strong style="color: #1E3A5F;"><?= $totalHommes ?> H</strong> &bull; <strong style="color: #BE185D;"><?= $totalFemmes ?> F</strong></span>
            </div>
          </div>
        </div>

        <!-- KPI 2 : Classes & Capacité -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="layers" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Classes Ouvertes</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalClasses ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Capacité globale :</span>
              <strong style="color: #0F172A;"><?= $totalEtudiants ?> / <?= $totalCapacite ?> (<?= $tauxRemplissage ?>%)</strong>
            </div>
            <div style="width: 100%; height: 6px; background: #E2E8F0; border-radius: 99px; overflow: hidden;">
              <div style="width: <?= $tauxRemplissage ?>%; height: 100%; background: <?= $tauxRemplissage >= 90 ? '#DC2626' : '#16A34A' ?>; border-radius: 99px;"></div>
            </div>
          </div>
        </div>

        <!-- KPI 3 : Semestres & Coefficients -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="clock" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Semestres & Périodes</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalSemAll ?>
                <span style="font-size: 12px; font-weight: 600; color: #64748B;">(<?= $totalSemClot ?> clôturé<?= $totalSemClot > 1 ? 's' : '' ?>)</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #475569;">
            <span>Coefficients Bulletins :</span>
            <div style="display: inline-flex; gap: 5px;">
              <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 5px;">S1 : <?= $coefS1 !== '' ? $coefS1 : '-' ?></span>
              <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 5px;">S2 : <?= $coefS2 !== '' ? $coefS2 : '-' ?></span>
            </div>
          </div>
        </div>

        <!-- KPI 4 : Recouvrement Scolarité (si autorisé) ou Activité Pédagogique -->
        <?php if ($canViewPaiements): ?>
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
              <div style="width: 44px; height: 44px; border-radius: 12px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="wallet" style="width: 21px; height: 21px;"></i>
              </div>
              <div style="min-width: 0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Recouvrement Scolarité</div>
                <div style="font-size: 19px; font-weight: 800; color: #047857; line-height: 1.2; margin-top: 2px; word-break: break-word;">
                  <?= number_format($totalRecouvrement, 0, ',', ' ') ?> FCFA
                </div>
              </div>
            </div>
            <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
              <span>Suivi pédagogique :</span>
              <span><strong style="color: #0F172A;"><?= $totalEvals ?></strong> éval. &bull; <strong style="color: #B91C1C;"><?= htmlspecialchars($fmtHeures($totalAbsHeures)) ?></strong> abs.</span>
            </div>
          </div>
        <?php else: ?>
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
              <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="award" style="width: 21px; height: 21px;"></i>
              </div>
              <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Évaluations & Assiduité</div>
                <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                  <?= $totalEvals ?>
                  <span style="font-size: 12px; font-weight: 600; color: #64748B;">éval.</span>
                </div>
              </div>
            </div>
            <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
              <span>Total absences :</span>
              <strong style="color: #B91C1C;"><?= htmlspecialchars($fmtHeures($totalAbsHeures)) ?></strong>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- LIGNE 2 : CHRONOLOGIE & PARAMÈTRES + EXPORTS RAPIDES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; margin-bottom: 22px;">

        <!-- Chronologie & Avancement -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 14.5px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="calendar-days" style="width: 17px; height: 17px;"></i> Calendrier & Avancement
            </h3>
            <?php if ($progressLabel !== ''): ?>
              <span style="font-size: 11.5px; font-weight: 700; color: <?= $isCloture ? '#475569' : '#1E3A5F' ?>; background: #F1F5F9; padding: 3px 10px; border-radius: 20px;">
                <?= htmlspecialchars($progressLabel) ?>
              </span>
            <?php endif; ?>
          </div>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 12px;">
              <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase;">Début</div>
              <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($dateDebutFmt) ?></div>
            </div>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 12px;">
              <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase;">Fin</div>
              <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($dateFinFmt) ?></div>
            </div>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 12px;">
              <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase;">Durée</div>
              <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-top: 3px;"><?= htmlspecialchars($dureeLabel) ?></div>
            </div>
          </div>

          <div style="width: 100%; height: 8px; background: #E2E8F0; border-radius: 99px; overflow: hidden; margin-bottom: 12px;">
            <div style="width: <?= $progressPct ?>%; height: 100%; background: <?= $isCloture ? '#64748B' : ($isActif ? '#1E3A5F' : '#3B82F6') ?>; border-radius: 99px;"></div>
          </div>

          <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 12px; color: #64748B;">
            <span>Créée le : <strong style="color: #334155;"><?= htmlspecialchars($createdFmt) ?></strong></span>
            <?php if ($updatedFmt !== ''): ?>
              <span>Modifiée le : <strong style="color: #334155;"><?= htmlspecialchars($updatedFmt) ?></strong></span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Accès Rapides & Documents PDF -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <h3 style="font-size: 14.5px; font-weight: 800; color: #1E3A5F; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="file-spreadsheet" style="width: 17px; height: 17px;"></i> Registres & Exports de l'Année
          </h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 10px;">
            <?php if ($canViewEtudiants): ?>
              <a href="<?= url('etudiant/imprimerPdf') . '?annee_code=' . urlencode($codeAnnee) ?>" target="_blank" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 11px 13px; text-decoration: none; display: flex; align-items: center; gap: 10px; color: #0F172A;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: #1D4ED8; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                </span>
                <span style="min-width: 0;">
                  <strong style="display: block; font-size: 12.5px; color: #0F172A;">Liste Étudiants</strong>
                  <small style="color: #64748B; font-size: 11px;">Registre PDF annuel</small>
                </span>
              </a>
            <?php endif; ?>
            <?php if ($canViewEmplois): ?>
              <a href="<?= url('emploi/imprimerPdf') . '?annee_code=' . urlencode($codeAnnee) ?>" target="_blank" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 11px 13px; text-decoration: none; display: flex; align-items: center; gap: 10px; color: #0F172A;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: #F0FDF4; color: #15803D; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <i data-lucide="calendar-clock" style="width: 16px; height: 16px;"></i>
                </span>
                <span style="min-width: 0;">
                  <strong style="display: block; font-size: 12.5px; color: #0F172A;">Emplois du Temps</strong>
                  <small style="color: #64748B; font-size: 11px;">Répertoire PDF</small>
                </span>
              </a>
            <?php endif; ?>
            <?php if ($canViewNotes): ?>
              <a href="<?= url('note/imprimerRegistrePdf') . '?annee_code=' . urlencode($codeAnnee) ?>" target="_blank" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 11px 13px; text-decoration: none; display: flex; align-items: center; gap: 10px; color: #0F172A;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: #FEF3C7; color: #B45309; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i>
                </span>
                <span style="min-width: 0;">
                  <strong style="display: block; font-size: 12.5px; color: #0F172A;">Évaluations</strong>
                  <small style="color: #64748B; font-size: 11px;">Registre PDF</small>
                </span>
              </a>
            <?php endif; ?>
            <?php if ($canViewAbsences): ?>
              <a href="<?= url('absence/imprimerPdf') . '?annee_code=' . urlencode($codeAnnee) ?>" target="_blank" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 11px 13px; text-decoration: none; display: flex; align-items: center; gap: 10px; color: #0F172A;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: #FEE2E2; color: #B91C1C; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <i data-lucide="user-x" style="width: 16px; height: 16px;"></i>
                </span>
                <span style="min-width: 0;">
                  <strong style="display: block; font-size: 12.5px; color: #0F172A;">Absences</strong>
                  <small style="color: #64748B; font-size: 11px;">Registre PDF</small>
                </span>
              </a>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- SECTION 3 : DÉCOUPAGE SEMESTRIEL -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="clock" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Découpage Semestriel (<?= count($semestres) ?>)
          </h3>
          <?php if ($canViewSemestres): ?>
            <a href="<?= url('semestre/list') ?>" style="font-size: 12.5px; font-weight: 700; color: #1E3A5F; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
              Gérer les semestres <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
          <?php endif; ?>
        </div>

        <?php if (empty($semestres)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 20px 0; font-style: italic; font-size: 13px;">
            Aucun semestre n'a encore été créé pour cette année académique.
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
            <?php foreach ($semestres as $s):
              $sStatut = (string)($s['statut_semestre'] ?? 'planifie');
              $sActif = ($sStatut === STATUTS::ACTIF);
              $sClot = ($sStatut === STATUTS::CLOTURE);
              $borderAccent = $sActif ? '#16A34A' : ($sClot ? '#64748B' : '#2563EB');
              $sDebut = !empty($s['date_debut_semestre']) ? date('d/m/Y', strtotime($s['date_debut_semestre'])) : '-';
              $sFin = !empty($s['date_fin_semestre']) ? date('d/m/Y', strtotime($s['date_fin_semestre'])) : '-';
            ?>
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid <?= $borderAccent ?>; border-radius: 10px; padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                  <div>
                    <div style="font-size: 15px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($s['libelle_semestre'] ?? '-') ?></div>
                    <code style="font-size: 11px; color: #64748B; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 1px 6px; border-radius: 4px; display: inline-block; margin-top: 3px;"><?= htmlspecialchars($s['code_semestre'] ?? '-') ?></code>
                  </div>
                  <?php if ($sActif): ?>
                    <span style="background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 3px 10px; border-radius: 20px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                      <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> Actif
                    </span>
                  <?php elseif ($sClot): ?>
                    <span style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; padding: 3px 10px; border-radius: 20px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                      <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Clôturé
                    </span>
                  <?php else: ?>
                    <span style="background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; padding: 3px 10px; border-radius: 20px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                      <i data-lucide="clock" style="width: 12px; height: 12px;"></i> Planifié
                    </span>
                  <?php endif; ?>
                </div>

                <div style="font-size: 12.5px; color: #475569; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i>
                  <span>Du <strong><?= htmlspecialchars($sDebut) ?></strong> au <strong><?= htmlspecialchars($sFin) ?></strong></span>
                </div>

                <div style="border-top: 1px solid #E2E8F0; padding-top: 9px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #475569;">
                  <span>
                    <strong style="color: #0F172A;"><?= (int)($s['nb_evaluations'] ?? 0) ?></strong> éval.
                    &bull;
                    <strong style="color: #B91C1C;"><?= htmlspecialchars($fmtHeures($s['heures_absences'] ?? 0)) ?></strong> abs.
                  </span>
                  <?php if ($canViewSemestres && !empty($s['encrypted_id'])): ?>
                    <a href="<?= url('semestre/details/' . $s['encrypted_id']) ?>" style="color: #0284C7; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                      Détails <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- SECTION 4 : CLASSES & GROUPES PÉDAGOGIQUES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Classes & Groupes Pédagogiques (<?= count($classes) ?>)
          </h3>
          <?php if (!empty($classes)): ?>
            <div style="position: relative; min-width: 240px;">
              <input type="text" id="search-annee-classes" placeholder="Rechercher une classe, filière, niveau..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
              <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($classes)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 24px 0; font-style: italic; font-size: 13px;">
            Aucune classe n'a été ouverte pour cette année académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-annee-classes" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 42px;">#</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Filière</th>
                  <th style="padding: 10px 12px;">Niveau</th>
                  <th style="padding: 10px 12px; text-align: center; width: 170px;">Remplissage</th>
                  <th style="padding: 10px 12px; text-align: center; width: 95px;">Statut</th>
                  <th style="padding: 10px 12px; text-align: right; width: 100px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($classes as $idx => $cl):
                  $clIdCrypte = (string)($cl['encrypted_id'] ?? '');
                  $isClActif = (($cl['statut_classe'] ?? STATUTS::ACTIF) === STATUTS::ACTIF);
                  $nbEl = (int)($cl['nb_eleves'] ?? 0);
                  $capMax = (int)($cl['capacite_max_classe'] ?? 0);
                  $pctCl = $capMax > 0 ? min(100, (int)round(($nbEl / $capMax) * 100)) : 0;
                ?>
                  <tr class="classe-row-item" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; font-weight: 800; color: #0F172A; white-space: nowrap;">
                      <?php if ($canViewClasses && $clIdCrypte !== ''): ?>
                        <a href="<?= url('classe/details/' . $clIdCrypte) ?>" style="color: #1E3A5F; text-decoration: none;">
                          <?= htmlspecialchars($cl['libelle_classe'] ?? '-') ?>
                        </a>
                      <?php else: ?>
                        <?= htmlspecialchars($cl['libelle_classe'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; color: #334155; font-weight: 600;"><?= htmlspecialchars($cl['libelle_filiere'] ?? '-') ?></td>
                    <td style="padding: 11px 12px; color: #475569;"><?= htmlspecialchars($cl['libelle_niveau'] ?? '-') ?></td>
                    <td style="padding: 11px 12px; text-align: center;">
                      <div style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px;">
                        <?= $nbEl ?> <span style="color: #64748B; font-weight: 500;">/ <?= $capMax ?></span>
                        <?php if ($capMax > 0): ?>
                          <span style="color: #64748B; font-size: 11px;">(<?= $pctCl ?>%)</span>
                        <?php endif; ?>
                      </div>
                      <div style="width: 100%; height: 5px; background: #E2E8F0; border-radius: 99px; overflow: hidden;">
                        <div style="width: <?= $pctCl ?>%; height: 100%; background: <?= $pctCl >= 90 ? '#DC2626' : '#1E3A5F' ?>; border-radius: 99px;"></div>
                      </div>
                    </td>
                    <td style="padding: 11px 12px; text-align: center;">
                      <?php if ($isClActif): ?>
                        <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11.5px;">Actif</span>
                      <?php else: ?>
                        <span style="background: #FEE2E2; color: #B91C1C; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11.5px;">Inactif</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                        <?php if ($canViewEtudiants): ?>
                          <a href="<?= url('etudiant/imprimerPdf') . '?annee_code=' . urlencode($codeAnnee) . '&classe_code=' . urlencode($cl['code_classe'] ?? '') ?>" target="_blank" title="Imprimer la liste de la classe" aria-label="Imprimer la liste de la classe" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #334155; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($canViewClasses && $clIdCrypte !== ''): ?>
                          <a href="<?= url('classe/details/' . $clIdCrypte) ?>" title="Voir les détails de la classe" aria-label="Voir les détails de la classe" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                      </div>
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

<?php if ($canManageAnnees && $isEditable): ?>
<!-- MODALE D'ÉDITION RAPIDE DE L'ANNÉE ACADÉMIQUE -->
<div id="modal-annee-detail" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier l'Année Académique
      </h3>
      <button type="button" class="btn-close-modal-annee-detail" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-annee-detail" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_annee" value="<?= (int)($item['id_annee'] ?? 0) ?>">

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Année Académique <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_annee" required value="<?= htmlspecialchars($libelleAnnee) ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
        <div>
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de Début <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="date_debut_annee" required value="<?= htmlspecialchars($dateDebutRaw) ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
        <div>
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date de Fin <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="date_fin_annee" required value="<?= htmlspecialchars($dateFinRaw) ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;">
        </div>
      </div>

      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 20px;">
        <div style="font-size: 11.5px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="sliders" style="width: 14px; height: 14px;"></i> Coefficients Semestriels (Bulletins)
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
          <div>
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 4px;">
              Coef. Semestre 1 <span style="color: #EF4444;">*</span>
            </label>
            <input type="number" step="1" min="1" name="coef_semestre1" required value="<?= htmlspecialchars((string)$coefS1) ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13px;">
          </div>
          <div>
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 4px;">
              Coef. Semestre 2 <span style="color: #EF4444;">*</span>
            </label>
            <input type="number" step="1" min="1" name="coef_semestre2" required value="<?= htmlspecialchars((string)$coefS2) ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13px;">
          </div>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn-close-modal-annee-detail" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-annee-detail" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script type="application/json" id="annees-details-config">
<?= json_encode([
    'racine' => RACINE,
    'anneeId' => (int)($item['id_annee'] ?? 0),
    'statutAnnee' => $stAnnee,
    'libelleAnnee' => $libelleAnnee,
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/annees.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
