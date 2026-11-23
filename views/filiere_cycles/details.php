<?php
require_once __DIR__ . '/../../public/inc/header.php';

$classes = $classes ?? [];
$maquettes = $maquettes ?? [];
$scolarites = $scolarites ?? [];
$anneeCode = (string)($anneeCode ?? '');
$anneeLibelle = (string)($anneeLibelle ?? '');
$encryptedId = (string)($encryptedId ?? '');
$encryptedFiliereId = (string)($encryptedFiliereId ?? '');
$encryptedCycleId = (string)($encryptedCycleId ?? '');
$encryptedNiveauId = (string)($encryptedNiveauId ?? '');
$filieresList = $filieresList ?? [];
$cyclesList = $cyclesList ?? [];
$niveauxList = $niveauxList ?? [];

$idFc = (int)($item['id_filiere_cycle'] ?? 0);
$codeFc = (string)($item['code_filiere_cycle'] ?? '');
$libelleFiliere = (string)($item['libelle_filiere'] ?? '');
$libelleCycle = (string)($item['libelle_cycle'] ?? '');
$libelleNiveau = (string)($item['libelle_niveau'] ?? '');
$typeFiliere = strtoupper(trim((string)($item['type_filiere'] ?? '')));
$statutFc = (string)($item['statut_filiere_cycle'] ?? STATUTS::ACTIF);
$isActif = ($statutFc === STATUTS::ACTIF);

$createdAt = !empty($item['created_at_filiere_cycle']) ? date('d/m/Y', strtotime($item['created_at_filiere_cycle'])) : '';
$updatedAt = !empty($item['updated_at_filiere_cycle']) ? date('d/m/Y', strtotime($item['updated_at_filiere_cycle'])) : '';

// Agrégations dynamiques depuis les classes rattachées
$nbClasses = count($classes);
$totalEtudiants = 0;
$totalCapacite = 0;
$totalAffectes = 0;
$totalNonAffectes = 0;
foreach ($classes as $c) {
    $totalEtudiants += (int)($c['nb_etudiants'] ?? 0);
    $totalCapacite += (int)($c['capacite_max_classe'] ?? 0);
    $totalAffectes += (int)($c['nb_affectes'] ?? 0);
    $totalNonAffectes += (int)($c['nb_non_affectes'] ?? 0);
}
$tauxRemplissageGlobal = ($totalCapacite > 0) ? min(100, (int)round(($totalEtudiants / $totalCapacite) * 100)) : 0;

// Agrégations dynamiques depuis la maquette pédagogique
$nbMatieresMaquette = count($maquettes);
$totalCoefs = 0.0;
$nbGen = 0;
$nbPro = 0;
foreach ($maquettes as $mq) {
    if (($mq['statut_maquette'] ?? STATUTS::ACTIF) === STATUTS::ACTIF) {
        $totalCoefs += (float)($mq['coefficient'] ?? 0);
    }
    if (strtoupper((string)($mq['categorie_matiere'] ?? '')) === CATEGORIES_MATIERES::PROFESSIONNELLE) {
        $nbPro++;
    } else {
        $nbGen++;
    }
}

// Tarifs de scolarité (strictement table scolarites, dissociée des frais annexes)
$tarifAffecte = null;
$tarifNonAffecte = null;
foreach ($scolarites as $sc) {
    $etat = strtolower(trim((string)($sc['affectation_etat'] ?? '')));
    if ($etat === 'affecte' && $tarifAffecte === null) {
        $tarifAffecte = (float)$sc['montant_scolarite'];
    } elseif ($etat === 'non_affecte' && $tarifNonAffecte === null) {
        $tarifNonAffecte = (float)$sc['montant_scolarite'];
    }
}

$fmtCoef = static function ($c): string {
    $val = (float)$c;
    return (fmod($val, 1.0) === 0.0) ? (string)(int)$val : number_format($val, 2, ',', ' ');
};
$fmtMoney = static function ($m): string {
    return number_format((float)$m, 0, ',', ' ') . ' FCFA';
};

$canManage = Auth::can(PERMISSIONS::MANAGE_FILIERES) || Auth::can(PERMISSIONS::MANAGE_CYCLES);
$canPrintOffre = Auth::can(PERMISSIONS::PRINT_OFFRE_ACADEMIQUE) || Auth::can(PERMISSIONS::VIEW_FILIERES) || Auth::can(PERMISSIONS::VIEW_CYCLES);
$canViewFilieres = Auth::can(PERMISSIONS::VIEW_FILIERES);
$canViewCycles = Auth::can(PERMISSIONS::VIEW_CYCLES);
$canViewNiveaux = Auth::can(PERMISSIONS::VIEW_NIVEAUX);
$canViewClasses = Auth::can(PERMISSIONS::VIEW_CLASSES);
$canManageClasses = Auth::can(PERMISSIONS::MANAGE_CLASSES);
$canViewMatieres = Auth::can(PERMISSIONS::VIEW_MATIERES);
$canViewMaquettes = Auth::can(PERMISSIONS::VIEW_MAQUETTES);
$canViewScolarite = Auth::can(PERMISSIONS::VIEW_FRAIS_SCOLARITE) || Auth::can(PERMISSIONS::MANAGE_FRAIS_SCOLARITE);
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
              <i data-lucide="git-merge" style="width: 21px; height: 21px;"></i>
            </div>
            <h1 style="font-size: 21px; font-weight: 800; color: #0F172A; margin: 0;">
              <?= htmlspecialchars($libelleFiliere ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeFc ?: '-') ?></code>
            <?php if ($libelleCycle !== ''): ?>
              <span style="background: #F3E8FF; color: #7E22CE; border: 1px solid #E9D5FF; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">
                <?= htmlspecialchars($libelleCycle) ?>
              </span>
            <?php endif; ?>
            <?php if ($typeFiliere === 'INDUSTRIELLE'): ?>
              <span style="background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">Industrielle</span>
            <?php elseif ($typeFiliere === 'TERTIAIRE'): ?>
              <span style="background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">Tertiaire</span>
            <?php endif; ?>
            <?php if ($isActif): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Actif
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Inactif
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 8px 0 0 0;">
            Niveau : <strong style="color: #0F172A;"><?= htmlspecialchars($libelleNiveau !== '' ? $libelleNiveau : 'Tous niveaux') ?></strong>
            <?php if ($anneeLibelle !== ''): ?>
              &bull; Session : <strong style="color: #1E3A5F;"><?= htmlspecialchars($anneeLibelle) ?></strong>
            <?php endif; ?>
            <?php if ($createdAt !== ''): ?>
              &bull; Créé le <strong style="color: #334155;"><?= htmlspecialchars($createdAt) ?></strong>
            <?php endif; ?>
            <?php if ($updatedAt !== ''): ?>
              &bull; Mis à jour le <strong style="color: #334155;"><?= htmlspecialchars($updatedAt) ?></strong>
            <?php endif; ?>
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('filiere_cycle/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <?php if ($canPrintOffre): ?>
            <a href="<?= url('filiere_cycle/imprimerPdf') . (!empty($item['cycle_code']) ? ('?cycle_code=' . urlencode($item['cycle_code'])) : '') ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Offre PDF
            </a>
          <?php endif; ?>
          <?php if ($canManage): ?>
            <button type="button" id="btn-toggle-statut-fc-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'pause-circle' : 'play-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Désactiver' : 'Activer' ?>
            </button>
            <button type="button" id="btn-edit-fc-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; cursor: pointer;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 20px;">

        <!-- KPI 1 : Classes Rattachées -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="graduation-cap" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Classes Ouvertes</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $nbClasses ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">classe<?= $nbClasses > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Capacité : <strong><?= $totalCapacite ?></strong> pl.</span>
            <span>Remplissage : <strong style="color: #1D4ED8;"><?= $tauxRemplissageGlobal ?>%</strong></span>
          </div>
        </div>

        <!-- KPI 2 : Effectif Inscrits -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="users" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Effectif du Parcours</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalEtudiants ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">étudiant<?= $totalEtudiants > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Affectés / Privés :</span>
            <span><strong style="color: #15803D;"><?= $totalAffectes ?></strong> / <strong style="color: #0369A1;"><?= $totalNonAffectes ?></strong></span>
          </div>
        </div>

        <!-- KPI 3 : Maquette Pédagogique -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="book-open" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Maquette Pédagogique</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $nbMatieresMaquette ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">matière<?= $nbMatieresMaquette > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Total Coef. : <strong style="color: #7E22CE;"><?= htmlspecialchars($fmtCoef($totalCoefs)) ?></strong></span>
            <span><strong><?= $nbGen ?></strong> Gén. &bull; <strong><?= $nbPro ?></strong> Pro.</span>
          </div>
        </div>

        <!-- KPI 4 : Frais de Scolarité (strictement dissocié des frais annexes) -->
        <?php if ($canViewScolarite): ?>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="wallet" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Frais de Scolarité</div>
              <div style="font-size: 17px; font-weight: 800; color: #0F172A; line-height: 1.2; margin-top: 4px;">
                <?= $tarifNonAffecte !== null ? htmlspecialchars($fmtMoney($tarifNonAffecte)) : ($tarifAffecte !== null ? htmlspecialchars($fmtMoney($tarifAffecte)) : 'Non défini') ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Affecté(e) :</span>
              <strong style="color: #15803D;"><?= $tarifAffecte !== null ? htmlspecialchars($fmtMoney($tarifAffecte)) : '-' ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Non Affecté(e) :</span>
              <strong style="color: #1E3A5F;"><?= $tarifNonAffecte !== null ? htmlspecialchars($fmtMoney($tarifNonAffecte)) : '-' ?></strong>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F1F5F9; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="layers" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Structure Pivot</div>
              <div style="font-size: 17px; font-weight: 800; color: #0F172A; line-height: 1.2; margin-top: 4px;">
                <?= htmlspecialchars($libelleCycle ?: '-') ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Niveau :</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($libelleNiveau !== '' ? $libelleNiveau : 'Global') ?></strong>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <!-- BANDEAU COMPOSANTES DU PARCOURS PIVOT -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px;">
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Filière</div>
              <div style="font-size: 13.5px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($libelleFiliere ?: '-') ?></div>
              <code style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($item['filiere_code'] ?? '-') ?></code>
            </div>
            <?php if ($canViewFilieres && $encryptedFiliereId !== ''): ?>
              <a href="<?= url('filiere/details/' . $encryptedFiliereId) ?>" title="Voir la fiche filière" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BFDBFE; background: #EFF6FF; color: #1D4ED8; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0;">
                <i data-lucide="arrow-up-right" style="width: 15px; height: 15px;"></i>
              </a>
            <?php endif; ?>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px;">
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Cycle Académique</div>
              <div style="font-size: 13.5px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($libelleCycle ?: '-') ?></div>
              <code style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($item['cycle_code'] ?? '-') ?></code>
            </div>
            <?php if ($canViewCycles && $encryptedCycleId !== ''): ?>
              <a href="<?= url('cycle/details/' . $encryptedCycleId) ?>" title="Voir la fiche cycle" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E9D5FF; background: #FAF5FF; color: #7E22CE; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0;">
                <i data-lucide="arrow-up-right" style="width: 15px; height: 15px;"></i>
              </a>
            <?php endif; ?>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px;">
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Niveau d'Études</div>
              <div style="font-size: 13.5px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($libelleNiveau !== '' ? $libelleNiveau : 'Tous / Global') ?></div>
              <code style="font-size: 11px; color: #64748B;"><?= htmlspecialchars(!empty($item['niveau_code']) ? $item['niveau_code'] : '-') ?></code>
            </div>
            <?php if ($canViewNiveaux && $encryptedNiveauId !== ''): ?>
              <a href="<?= url('niveau/details/' . $encryptedNiveauId) ?>" title="Voir la fiche niveau" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BBF7D0; background: #F0FDF4; color: #15803D; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0;">
                <i data-lucide="arrow-up-right" style="width: 15px; height: 15px;"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- SECTION 1 : CLASSES RATTACHÉES À CE PARCOURS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="graduation-cap" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Classes Rattachées (<span id="count-visible-fc-classes"><?= $nbClasses ?></span>)
          </h3>

          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <?php if (!empty($classes)): ?>
              <div style="position: relative; min-width: 210px;">
                <input type="text" id="search-fc-classes" placeholder="Rechercher une classe..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
              </div>
            <?php endif; ?>
            <?php if ($canManageClasses): ?>
              <a href="<?= url('classe/formulaire') ?>" style="background: #1E3A5F; color: #FFFFFF; border-radius: 8px; padding: 7px 14px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="plus-circle" style="width: 15px; height: 15px;"></i> Nouvelle classe
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if (empty($classes)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Aucune classe n'est ouverte pour ce parcours sur la session académique active.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-fc-classes" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Code</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Niveau</th>
                  <th style="padding: 10px 12px; text-align: center;">Effectif / Capacité</th>
                  <th style="padding: 10px 12px; text-align: center;">Affectés / Privés</th>
                  <th style="padding: 10px 12px; text-align: center;">Statut</th>
                  <th style="padding: 10px 12px; text-align: right; width: 70px;">Fiche</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($classes as $idx => $c):
                  $eff = (int)($c['nb_etudiants'] ?? 0);
                  $cap = (int)($c['capacite_max_classe'] ?? 0);
                  $pct = ($cap > 0) ? min(100, (int)round(($eff / $cap) * 100)) : 0;
                  $clActif = (($c['statut_classe'] ?? '') === STATUTS::ACTIF);
                ?>
                  <tr class="fc-classe-row" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 7px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($c['code_classe'] ?? '-') ?></code>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 800; color: #0F172A;">
                      <?php if ($canViewClasses && !empty($c['encrypted_classe_id'])): ?>
                        <a href="<?= url('classe/details/' . $c['encrypted_classe_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($c['libelle_classe'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($c['libelle_classe'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; color: #475569; font-weight: 600;">
                      <?= htmlspecialchars($c['libelle_niveau'] ?? '-') ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; min-width: 150px;">
                      <div style="font-weight: 800; color: #0F172A; font-size: 12.5px;">
                        <?= $eff ?><?= $cap > 0 ? (' / ' . $cap) : '' ?>
                        <?php if ($cap > 0): ?>
                          <span style="font-size: 11px; color: #64748B; font-weight: 600;">(<?= $pct ?>%)</span>
                        <?php endif; ?>
                      </div>
                      <?php if ($cap > 0): ?>
                        <div style="width: 100%; max-width: 120px; height: 5px; background: #E2E8F0; border-radius: 99px; overflow: hidden; margin: 4px auto 0;">
                          <div style="width: <?= $pct ?>%; height: 100%; background: <?= $pct >= 90 ? '#DC2626' : '#16A34A' ?>; border-radius: 99px;"></div>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap; font-size: 12.5px;">
                      <strong style="color: #15803D;"><?= (int)($c['nb_affectes'] ?? 0) ?></strong>
                      <span style="color: #94A3B8;"> / </span>
                      <strong style="color: #0369A1;"><?= (int)($c['nb_non_affectes'] ?? 0) ?></strong>
                    </td>
                    <td style="padding: 11px 12px; text-align: center;">
                      <?php if ($clActif): ?>
                        <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Active</span>
                      <?php else: ?>
                        <span style="background: #FEE2E2; color: #B91C1C; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right;">
                      <?php if ($canViewClasses && !empty($c['encrypted_classe_id'])): ?>
                        <a href="<?= url('classe/details/' . $c['encrypted_classe_id']) ?>" title="Ouvrir la fiche classe" aria-label="Ouvrir la fiche classe" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
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

      <!-- SECTION 2 : MAQUETTE PÉDAGOGIQUE & COEFFICIENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="book-open" style="width: 18px; height: 18px; color: #7E22CE;"></i>
            Maquette Pédagogique & Coefficients (<span id="count-visible-fc-maquettes"><?= $nbMatieresMaquette ?></span>)
          </h3>

          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <?php if (!empty($maquettes)): ?>
              <select id="filter-fc-maquette-cat" style="padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC; font-weight: 600;">
                <option value="">Toutes les catégories</option>
                <option value="<?= CATEGORIES_MATIERES::GENERALE ?>">Matières Générales (<?= $nbGen ?>)</option>
                <option value="<?= CATEGORIES_MATIERES::PROFESSIONNELLE ?>">Matières Professionnelles (<?= $nbPro ?>)</option>
              </select>
              <div style="position: relative; min-width: 200px;">
                <input type="text" id="search-fc-maquettes" placeholder="Filtrer une matière..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
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
            Aucune matière n'est encore configurée dans la maquette pédagogique de ce parcours pour cette session.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-fc-maquettes" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Code</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px; text-align: center;">Catégorie</th>
                  <th style="padding: 10px 12px; text-align: center;">Coefficient</th>
                  <th style="padding: 10px 12px; text-align: center;">Statut</th>
                  <th style="padding: 10px 12px; text-align: right; width: 70px;">Fiche</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($maquettes as $idx => $mq):
                  $cat = strtoupper((string)($mq['categorie_matiere'] ?? ''));
                  $isPro = ($cat === CATEGORIES_MATIERES::PROFESSIONNELLE);
                  $mqActif = (($mq['statut_maquette'] ?? '') === STATUTS::ACTIF);
                ?>
                  <tr class="fc-maquette-row" data-categorie="<?= htmlspecialchars($cat) ?>" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <code style="font-weight: 700; color: #475569; background: #F1F5F9; padding: 2px 7px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($mq['code_maquette'] ?? '-') ?></code>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 700; color: #0F172A;">
                      <?php if ($canViewMatieres && !empty($mq['encrypted_matiere_id'])): ?>
                        <a href="<?= url('matiere/details/' . $mq['encrypted_matiere_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($mq['libelle_matiere'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($mq['libelle_matiere'] ?? '-') ?>
                      <?php endif; ?>
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
                    <td style="padding: 11px 12px; text-align: center;">
                      <?php if ($mqActif): ?>
                        <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Actif</span>
                      <?php else: ?>
                        <span style="background: #FEE2E2; color: #B91C1C; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Inactif</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right;">
                      <?php if ($canViewMatieres && !empty($mq['encrypted_matiere_id'])): ?>
                        <a href="<?= url('matiere/details/' . $mq['encrypted_matiere_id']) ?>" title="Ouvrir la fiche matière" aria-label="Ouvrir la fiche matière" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E9D5FF; background: #FAF5FF; color: #7E22CE; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                          <i data-lucide="book-open" style="width: 15px; height: 15px;"></i>
                        </a>
                      <?php else: ?>
                        <span style="color: #94A3B8;">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background: #F8FAFC; border-top: 2px solid #E2E8F0;">
                  <td colspan="4" style="padding: 11px 12px; font-weight: 800; color: #1E3A5F; text-align: right;">Total des Coefficients Actifs :</td>
                  <td style="padding: 11px 12px; text-align: center; font-weight: 800; color: #7E22CE;"><?= htmlspecialchars($fmtCoef($totalCoefs)) ?></td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- SECTION 3 : GRILLE TARIFAIRE SCOLARITÉ (DISSOCIÉE DES FRAIS ANNEXES) -->
      <?php if ($canViewScolarite): ?>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="wallet" style="width: 18px; height: 18px; color: #B45309;"></i>
            Grille des Frais de Scolarité (<?= count($scolarites) ?>)
          </h3>
          <a href="<?= url('scolarite/list') ?>" style="background: #FFFFFF; color: #1E3A5F; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px;"></i> Gérer la scolarité
          </a>
        </div>

        <?php if (empty($scolarites)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 20px 0; font-style: italic; font-size: 13px;">
            Aucun tarif de scolarité n'est encore configuré pour ce parcours sur la session active.
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
            <?php foreach ($scolarites as $sc):
              $isAff = (strtolower((string)($sc['affectation_etat'] ?? '')) === 'affecte');
            ?>
              <div style="background: <?= $isAff ? '#F0FDF4' : '#EFF6FF' ?>; border: 1px solid <?= $isAff ? '#BBF7D0' : '#BFDBFE' ?>; border-radius: 10px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                <div>
                  <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: <?= $isAff ? '#15803D' : '#1E3A5F' ?>;">
                    <?= $isAff ? 'Étudiant Affecté (État)' : 'Étudiant Non Affecté (Privé)' ?>
                  </span>
                  <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;">
                    <?= htmlspecialchars($fmtMoney($sc['montant_scolarite'] ?? 0)) ?>
                  </div>
                  <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
                    <code><?= htmlspecialchars($sc['code_scolarite'] ?? '-') ?></code>
                    <?php if (!empty($sc['libelle_niveau'])): ?>
                      &bull; <?= htmlspecialchars($sc['libelle_niveau']) ?>
                    <?php endif; ?>
                  </div>
                </div>
                <span style="background: #FFFFFF; color: <?= $isAff ? '#15803D' : '#0369A1' ?>; border: 1px solid <?= $isAff ? '#86EFAC' : '#BAE6FD' ?>; padding: 4px 10px; border-radius: 12px; font-weight: 800; font-size: 11.5px;">
                  Scolarité
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<?php if ($canManage): ?>
<!-- MODAL ÉDITION RAPIDE DU PARCOURS PIVOT -->
<div id="modal-fc-detail" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Parcours Pivot
      </h3>
      <button type="button" class="btn-close-modal-fc-detail" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-fc-detail" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_filiere_cycle" value="<?= $idFc ?>">

      <div style="margin-bottom: 15px;">
        <label for="detail_fc_cycle" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Cycle Académique <span style="color: #EF4444;">*</span>
        </label>
        <select name="cycle_code" id="detail_fc_cycle" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px; color: #0F172A; background: #FFFFFF;">
          <option value="">-- Sélectionner un cycle --</option>
          <?php foreach ($cyclesList as $cy): ?>
            <option value="<?= htmlspecialchars($cy['code_cycle']) ?>" <?= (($item['cycle_code'] ?? '') === $cy['code_cycle']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cy['libelle_cycle']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 15px;">
        <label for="detail_fc_filiere" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Filière <span style="color: #EF4444;">*</span>
        </label>
        <select name="filiere_code" id="detail_fc_filiere" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px; color: #0F172A; background: #FFFFFF;">
          <option value="">-- Sélectionner une filière --</option>
          <?php foreach ($filieresList as $fil): ?>
            <option value="<?= htmlspecialchars($fil['code_filiere']) ?>" data-type="<?= htmlspecialchars($fil['type_filiere'] ?? '') ?>" <?= (($item['filiere_code'] ?? '') === $fil['code_filiere']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($fil['libelle_filiere']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de Filière</label>
        <input type="text" id="detail_fc_type_display" readonly value="<?= $typeFiliere === 'INDUSTRIELLE' ? 'Filière Industrielle' : ($typeFiliere === 'TERTIAIRE' ? 'Filière Tertiaire' : $typeFiliere) ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0; background: #F8FAFC; color: #475569; font-weight: 700; font-size: 13.5px;">
        <input type="hidden" name="type_filiere" id="detail_fc_type" value="<?= htmlspecialchars($typeFiliere !== '' ? $typeFiliere : 'AUTO') ?>">
      </div>

      <div style="margin-bottom: 15px;">
        <label for="detail_fc_niveau" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Niveau d'Études <span style="color: #EF4444;">*</span>
        </label>
        <select name="niveau_code" id="detail_fc_niveau" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px; color: #0F172A; background: #FFFFFF;">
          <option value="">-- Sélectionner un niveau --</option>
          <?php foreach ($niveauxList as $niv): ?>
            <option value="<?= htmlspecialchars($niv['code_niveau']) ?>" <?= (($item['niveau_code'] ?? '') === $niv['code_niveau']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($niv['libelle_niveau']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 22px;">
        <label for="detail_fc_statut" style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_filiere_cycle" id="detail_fc_statut" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px; color: #0F172A; background: #FFFFFF;">
          <option value="<?= STATUTS::ACTIF ?>" <?= $isActif ? 'selected' : '' ?>>Actif</option>
          <option value="<?= STATUTS::INACTIF ?>" <?= !$isActif ? 'selected' : '' ?>>Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn-close-modal-fc-detail" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 9px 18px; cursor: pointer;">Annuler</button>
        <button type="submit" id="btn-save-fc-detail" style="background: #1E3A5F; color: #FFFFFF; border: 1px solid #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script type="application/json" id="fc-details-config">
<?= json_encode([
    'racine'    => RACINE,
    'fcId'      => $idFc,
    'isActif'   => $isActif,
    'label'     => trim($libelleFiliere . ($libelleCycle !== '' ? ' · ' . $libelleCycle : '') . ($libelleNiveau !== '' ? ' · ' . $libelleNiveau : '')),
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/filieres.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
