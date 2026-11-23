<?php
require_once __DIR__ . '/../../public/inc/header.php';

$etudiants = $etudiants ?? [];
$matieres = $matieres ?? [];
$activityStats = $activityStats ?? [];
$encryptedId = $encryptedId ?? '';
$encryptedAnneeId = $encryptedAnneeId ?? '';

$codeClasse = (string)($item['code_classe'] ?? '');
$libelleClasse = (string)($item['libelle_classe'] ?? '');
$anneeCode = (string)($item['annee_code'] ?? '');
$isActif = (($item['statut_classe'] ?? STATUTS::ACTIF) === STATUTS::ACTIF);

$capacite = (int)($item['capacite_max_classe'] ?? 0);
$nbInscrits = count($etudiants);
$placesLibres = max(0, $capacite - $nbInscrits);
$tauxRemplissage = ($capacite > 0) ? min(100, (int)round(($nbInscrits / $capacite) * 100)) : 0;

// Répartitions calculées dynamiquement depuis les étudiants de la classe
$nbAffectes = 0;
$nbNonAffectes = 0;
$nbHommes = 0;
$nbFemmes = 0;
foreach ($etudiants as $e) {
    $aff = strtolower(trim((string)($e['affectation_etat'] ?? '')));
    if ($aff === 'affecte' || $aff === 'oui') {
        $nbAffectes++;
    } else {
        $nbNonAffectes++;
    }
    $sx = strtoupper(trim((string)($e['sexe_etudiant'] ?? '')));
    if ($sx !== '' && strpos($sx, 'F') !== false) {
        $nbFemmes++;
    } elseif ($sx !== '') {
        $nbHommes++;
    }
}

$totalVolumeHebdo = 0.0;
$totalCreneauxHebdo = 0;
foreach ($matieres as $m) {
    $totalVolumeHebdo += (float)($m['volume_horaire'] ?? 0);
    $totalCreneauxHebdo += (int)($m['nb_creneaux'] ?? 0);
}

$nbEnseignants = (int)($activityStats['nb_enseignants'] ?? 0);
$totalEvaluations = (int)($activityStats['total_evaluations'] ?? 0);
$totalAbsencesCount = (int)($activityStats['total_absences_count'] ?? 0);
$totalAbsencesHeures = (float)($activityStats['total_absences_heures'] ?? 0);
$totalAbsencesInjust = (float)($activityStats['total_absences_injustifiees'] ?? 0);

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};

$canManageClasses = Auth::can(PERMISSIONS::MANAGE_CLASSES);
$canViewEtudiants = Auth::can(PERMISSIONS::VIEW_ETUDIANTS);
$canViewEmplois = Auth::can(PERMISSIONS::VIEW_EMPLOI_TEMPS);
$canManageEmplois = Auth::can(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
$canManageAbsences = Auth::can(PERMISSIONS::MANAGE_ABSENCES);
$canEnterNotes = Auth::can(PERMISSIONS::ENTER_NOTES);
$canViewDelib = Auth::can(PERMISSIONS::VIEW_DELIBERATIONS);
$canViewAnnees = Auth::can(PERMISSIONS::VIEW_ANNEES);
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
              <i data-lucide="graduation-cap" style="width: 21px; height: 21px;"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
              Classe <?= htmlspecialchars($libelleClasse ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeClasse ?: '-') ?></code>
            <?php if (!empty($item['libelle_cycle'])): ?>
              <span style="background: #F3E8FF; color: #7E22CE; border: 1px solid #E9D5FF; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">
                <?= htmlspecialchars($item['libelle_cycle']) ?>
              </span>
            <?php endif; ?>
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
            Filière : <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_filiere'] ?? '-') ?></strong>
            &bull; Niveau : <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_niveau'] ?? '-') ?></strong>
            &bull; Année :
            <?php if ($canViewAnnees && $encryptedAnneeId !== ''): ?>
              <a href="<?= url('annee/details/' . $encryptedAnneeId) ?>" style="color: #1E3A5F; font-weight: 800; text-decoration: none;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></a>
            <?php else: ?>
              <strong style="color: #1E3A5F;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></strong>
            <?php endif; ?>
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('classe/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <?php if ($canViewEtudiants): ?>
            <a href="<?= url('etudiant/imprimerPdf') . '?annee_code=' . urlencode($anneeCode) . '&classe_code=' . urlencode($codeClasse) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;" title="Imprimer la liste d'émargement de la classe">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Liste Classe
            </a>
          <?php endif; ?>
          <?php if ($canViewEmplois): ?>
            <a href="<?= url('emploi/imprimerPdf') . '?classe_code=' . urlencode($codeClasse) . '&annee_code=' . urlencode($anneeCode) ?>" target="_blank" style="background: #F0F9FF; color: #0369A1; border: 1.5px solid #BAE6FD; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;" title="Imprimer l'emploi du temps hebdomadaire">
              <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Emploi du Temps
            </a>
          <?php endif; ?>
          <?php if ($canManageClasses): ?>
            <button type="button" id="btn-toggle-statut-classe-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'pause-circle' : 'play-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Désactiver' : 'Activer' ?>
            </button>
            <a href="<?= url('classe/edition/' . $encryptedId) ?>" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; text-decoration: none;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 20px;">

        <!-- KPI 1 : Effectif & Répartition -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="users" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Effectif Inscrit</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $nbInscrits ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">étudiant<?= $nbInscrits > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 5px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Affectés / Privés :</span>
              <span><strong style="color: #15803D;"><?= $nbAffectes ?></strong> / <strong style="color: #0369A1;"><?= $nbNonAffectes ?></strong></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Répartition Genre :</span>
              <span><strong style="color: #1E3A5F;"><?= $nbHommes ?> H</strong> &bull; <strong style="color: #BE185D;"><?= $nbFemmes ?> F</strong></span>
            </div>
          </div>
        </div>

        <!-- KPI 2 : Capacité & Taux de Remplissage -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $tauxRemplissage >= 90 ? '#FEE2E2' : '#F0FDF4' ?>; color: <?= $tauxRemplissage >= 90 ? '#B91C1C' : '#15803D' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="gauge" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Taux de Remplissage</div>
              <div style="font-size: 24px; font-weight: 800; color: <?= $tauxRemplissage >= 90 ? '#B91C1C' : '#15803D' ?>; line-height: 1.1; margin-top: 2px;">
                <?= $tauxRemplissage ?>%
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Capacité : <strong><?= $capacite ?></strong> places</span>
              <span style="color: #0F172A; font-weight: 700;"><?= $placesLibres ?> libre<?= $placesLibres > 1 ? 's' : '' ?></span>
            </div>
            <div style="width: 100%; height: 6px; background: #E2E8F0; border-radius: 99px; overflow: hidden;">
              <div style="width: <?= $tauxRemplissage ?>%; height: 100%; background: <?= $tauxRemplissage >= 90 ? '#DC2626' : '#16A34A' ?>; border-radius: 99px;"></div>
            </div>
          </div>
        </div>

        <!-- KPI 3 : Enseignements & Volume Hebdomadaire -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="book-open" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Volume Hebdomadaire</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= htmlspecialchars($fmtHeures($totalVolumeHebdo)) ?>
                <span style="font-size: 12px; font-weight: 600; color: #64748B;">/ sem.</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span><strong style="color: #7E22CE;"><?= count($matieres) ?></strong> matière<?= count($matieres) > 1 ? 's' : '' ?></span>
            <span><strong style="color: #0F172A;"><?= $nbEnseignants ?></strong> enseignant<?= $nbEnseignants > 1 ? 's' : '' ?> &bull; <strong><?= $totalCreneauxHebdo ?></strong> séance<?= $totalCreneauxHebdo > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 4 : Suivi Pédagogique & Assiduité -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="clipboard-check" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Évaluations & Absences</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalEvaluations ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">éval.</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Absences : <strong style="color: #0F172A;"><?= htmlspecialchars($fmtHeures($totalAbsencesHeures)) ?></strong> (<?= $totalAbsencesCount ?>)</span>
            <?php if ($totalAbsencesInjust > 0): ?>
              <strong style="color: #B91C1C;"><?= htmlspecialchars($fmtHeures($totalAbsencesInjust)) ?> injust.</strong>
            <?php else: ?>
              <strong style="color: #15803D;">0 h injust.</strong>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- RACCOURCIS PÉDAGOGIQUES DE LA CLASSE -->
      <?php if ($canManageAbsences || $canEnterNotes || $canViewDelib || $canManageEmplois): ?>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 14px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <span style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="zap" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Actions Pédagogiques Rapides
        </span>
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
          <?php if ($canManageAbsences): ?>
            <a href="<?= url('absence/saisieClasse') . '?classe_code=' . urlencode($codeClasse) . '&annee_code=' . urlencode($anneeCode) ?>" style="background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; padding: 7px 13px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="user-x" style="width: 15px; height: 15px;"></i> Faire l'appel
            </a>
          <?php endif; ?>
          <?php if ($canEnterNotes): ?>
            <a href="<?= url('note/saisieClasse') . '?classe_code=' . urlencode($codeClasse) ?>" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; padding: 7px 13px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i> Saisir des notes
            </a>
          <?php endif; ?>
          <?php if ($canManageEmplois): ?>
            <a href="<?= url('emploi/formulaire') . '?classe_code=' . urlencode($codeClasse) ?>" style="background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; padding: 7px 13px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar-plus" style="width: 15px; height: 15px;"></i> Programmer un cours
            </a>
          <?php endif; ?>
          <?php if ($canViewDelib): ?>
            <a href="<?= url('deliberation/conseil') . '?classe_code=' . urlencode($codeClasse) . '&annee_code=' . urlencode($anneeCode) ?>" style="background: #FAF5FF; color: #7E22CE; border: 1px solid #E9D5FF; padding: 7px 13px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="gavel" style="width: 15px; height: 15px;"></i> Conseil de Délibération
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- SECTION 2 : REGISTRE DES ÉTUDIANTS DE LA CLASSE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Registre des Étudiants (<span id="count-visible-etudiants"><?= $nbInscrits ?></span>)
          </h3>

          <?php if (!empty($etudiants)): ?>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <select id="filter-classe-regime" style="padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC; font-weight: 600;">
                <option value="">Tous les régimes</option>
                <option value="affecte">Affectés (<?= $nbAffectes ?>)</option>
                <option value="prive">Non Affectés (<?= $nbNonAffectes ?>)</option>
              </select>
              <div style="position: relative; min-width: 220px;">
                <input type="text" id="search-classe-etudiants" placeholder="Rechercher nom, matricule, tél..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($etudiants)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Aucun étudiant n'est encore inscrit dans cette classe pour cette année académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-classe-etudiants" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Étudiant</th>
                  <th style="padding: 10px 12px;">Matricule</th>
                  <th style="padding: 10px 12px; text-align: center;">Genre</th>
                  <th style="padding: 10px 12px; text-align: center;">Naissance</th>
                  <th style="padding: 10px 12px; text-align: center;">Régime</th>
                  <th style="padding: 10px 12px;">Contact</th>
                  <th style="padding: 10px 12px; text-align: right; width: 70px;">Fiche</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($etudiants as $idx => $e):
                  $nomEtu = trim(($e['nom_etudiant'] ?? '') . ' ' . ($e['prenom_etudiant'] ?? ''));
                  $parts = preg_split('/\s+/', $nomEtu) ?: [];
                  $init = '';
                  foreach (array_slice($parts, 0, 2) as $p) {
                      if ($p !== '') $init .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
                  }
                  if ($init === '') $init = 'ET';
                  $dob = $e['date_naissance_etudiant'] ?? '';
                  $dobFormatted = (!empty($dob) && $dob !== '0000-00-00') ? date('d/m/Y', strtotime($dob)) : '-';
                  $affRaw = strtolower(trim((string)($e['affectation_etat'] ?? '')));
                  $isAffecte = ($affRaw === 'affecte' || $affRaw === 'oui');
                  $sx = strtoupper(trim((string)($e['sexe_etudiant'] ?? '')));
                  $isF = ($sx !== '' && strpos($sx, 'F') !== false);
                ?>
                  <tr class="etudiant-row-item" data-regime="<?= $isAffecte ? 'affecte' : 'prive' ?>" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px;">
                      <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 32px; height: 32px; border-radius: 8px; background: <?= $isF ? '#FCE7F3' : '#EFF6FF' ?>; color: <?= $isF ? '#BE185D' : '#1E3A5F' ?>; font-weight: 800; font-size: 11.5px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                          <?= htmlspecialchars($init) ?>
                        </span>
                        <strong style="color: #0F172A;"><?= htmlspecialchars($nomEtu ?: '-') ?></strong>
                      </div>
                    </td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 7px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($e['matricule_etudiant'] ?? ($e['code_etudiant'] ?? '-')) ?></code>
                    </td>
                    <td style="padding: 11px 12px; text-align: center;">
                      <?php if ($sx !== ''): ?>
                        <span style="background: <?= $isF ? '#FCE7F3' : '#EFF6FF' ?>; color: <?= $isF ? '#BE185D' : '#1D4ED8' ?>; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 10px;">
                          <?= htmlspecialchars($isF ? 'F' : 'M') ?>
                        </span>
                      <?php else: ?>
                        <span style="color: #94A3B8;">-</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; color: #475569; font-weight: 600; white-space: nowrap;"><?= htmlspecialchars($dobFormatted) ?></td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                      <?php if ($isAffecte): ?>
                        <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Affecté(e)</span>
                      <?php else: ?>
                        <span style="background: #E0F2FE; color: #0369A1; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Privé(e)</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; color: #334155; white-space: nowrap;">
                      <?php if (!empty($e['telephone_etudiant'])): ?>
                        <a href="tel:<?= htmlspecialchars($e['telephone_etudiant']) ?>" style="color: #0F172A; font-weight: 600; text-decoration: none;"><?= htmlspecialchars($e['telephone_etudiant']) ?></a>
                      <?php else: ?>
                        <span style="color: #94A3B8;">-</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right;">
                      <?php if ($canViewEtudiants && !empty($e['encrypted_id'])): ?>
                        <a href="<?= url('etudiant/details/' . $e['encrypted_id']) ?>" title="Ouvrir le dossier étudiant" aria-label="Ouvrir le dossier étudiant" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                          <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                        </a>
                      <?php else: ?>
                        <span style="color: #94A3B8;">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- SECTION 3 : MATIÈRES, ENSEIGNANTS & VOLUME HORAIRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="book-open" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Matières & Enseignements Programmés (<?= count($matieres) ?>)
          </h3>
          <?php if ($canManageEmplois): ?>
            <a href="<?= url('emploi/formulaire') . '?classe_code=' . urlencode($codeClasse) ?>" style="background: #1E3A5F; color: #FFFFFF; border-radius: 8px; padding: 7px 14px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar-plus" style="width: 15px; height: 15px;"></i> Programmer un créneau
            </a>
          <?php endif; ?>
        </div>

        <?php if (empty($matieres)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Aucun cours n'est actuellement programmé pour cette classe dans l'emploi du temps.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px;">Enseignant Titulaire</th>
                  <th style="padding: 10px 12px;">Salle(s)</th>
                  <th style="padding: 10px 12px; text-align: center;">Séances / sem.</th>
                  <th style="padding: 10px 12px; text-align: right;">Volume Hebdo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($matieres as $idx => $m):
                  $profNom = trim(($m['nom_prof'] ?? '') . ' ' . ($m['prenom_prof'] ?? ''));
                ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; font-weight: 800; color: #0F172A;">
                      <?= htmlspecialchars($m['libelle_matiere'] ?? '-') ?>
                    </td>
                    <td style="padding: 11px 12px; color: #1E3A5F; font-weight: 600;">
                      <?= htmlspecialchars($profNom !== '' ? $profNom : 'Non attribué') ?>
                      <?php if (!empty($m['grade_enseignant'])): ?>
                        <span style="font-size: 11px; color: #64748B; font-weight: 500;">(<?= htmlspecialchars($m['grade_enseignant']) ?>)</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; color: #475569; font-weight: 600;">
                      <?= htmlspecialchars(!empty($m['salles_noms']) ? $m['salles_noms'] : '-') ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; color: #475569; font-weight: 600;">
                      <?= (int)($m['nb_creneaux'] ?? 0) ?> séance<?= ((int)($m['nb_creneaux'] ?? 0)) > 1 ? 's' : '' ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <span style="background: #EFF6FF; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 9px; border-radius: 6px; font-weight: 800; font-size: 12px;">
                        <?= !empty($m['volume_horaire']) ? htmlspecialchars($fmtHeures($m['volume_horaire'])) : '-' ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background: #F8FAFC; border-top: 2px solid #E2E8F0;">
                  <td colspan="4" style="padding: 11px 12px; font-weight: 800; color: #1E3A5F; text-align: right;">Total Hebdomadaire :</td>
                  <td style="padding: 11px 12px; text-align: center; font-weight: 800; color: #0F172A;"><?= $totalCreneauxHebdo ?> séance<?= $totalCreneauxHebdo > 1 ? 's' : '' ?></td>
                  <td style="padding: 11px 12px; text-align: right; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($fmtHeures($totalVolumeHebdo)) ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script type="application/json" id="classes-details-config">
<?= json_encode([
    'racine' => RACINE,
    'classeId' => (int)($item['id_classe'] ?? 0),
    'isActif' => $isActif,
    'libelleClasse' => $libelleClasse,
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/classes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
