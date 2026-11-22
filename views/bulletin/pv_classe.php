<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$matieres = $matieres ?? [];
$classes = $classes ?? [];
$semestres = $semestres ?? [];
$pvRows = $pvRows ?? [];
$statsClasse = $statsClasse ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? '';
$selectedClasseCode = $selectedClasseCode ?? '';
$selectedSemestreCode = $selectedSemestreCode ?? '';
$selectedSemestreLibelle = $selectedSemestreLibelle ?? ($selectedSemestreCode ?: '-');
$encryptedClasseCode = $encryptedClasseCode ?? '';
$anneeLibelle = $anneeLibelle ?? '-';
$deliberationsMap = $deliberationsMap ?? [];
$deliberationSummary = $deliberationSummary ?? [
  'is_delibere' => false,
  'has_provisoire' => false,
  'total_deliberes' => 0,
  'total_valides' => 0,
  'nb_admis' => 0,
  'nb_ajournes' => 0,
  'nb_redoublants' => 0,
  'nb_exclus' => 0,
  'nb_en_attente' => 0,
  'nb_rachetes' => 0,
  'last_date_deliberation' => null,
  'delibere_par_nom' => ''
];
$isFinalSemestre = !empty($isFinalSemestre);
$canManageDeliberations = !empty($canManageDeliberations);
$canWriteYear = !empty($canWriteYear);
$etab = !empty($etablissement) && is_array($etablissement) ? $etablissement : ($etablissementConfig ?? []);
$nomEtab = $etab['nom_etablissement'] ?? ($globalEtablissementNom ?? '');
$titreDirPed = $etab['titre_direction_pedagogique'] ?? 'Le Directeur des Études';
$nomDirPed = $etab['nom_directeur_etudes'] ?? '';
$titreDirGen = $etab['titre_direction_generale'] ?? 'Le Président du Jury / Directeur Général';
$nomDirGen = $etab['nom_directeur_general'] ?? '';
$mentionCachet = $etab['mention_cachet_signature'] ?? 'Signature & Cachet';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Barre d'actions non-imprimable -->
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <a href="<?= url('bulletin/list') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour Bulletins
            </a>
            <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Procès-Verbal (PV) de Notes de la Classe</h1>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Tableau récapitulatif de délibération des notes et classements par classe</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <?php if (!empty($encryptedClasseCode) && !empty($classeInfo)): ?>
            <a href="<?= url() ?>bulletin/imprimerPvPdf/<?= $encryptedClasseCode ?>?semestre=<?= urlencode($selectedSemestreCode) ?><?= !empty($selectedAnneeCode) ? ('&annee_code=' . urlencode($selectedAnneeCode)) : '' ?>" target="_blank" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; border-color: #1E3A5F; color: #1E3A5F;">
              <i data-lucide="file-down" style="width: 18px; height: 18px;"></i> Télécharger PV (PDF)
            </a>
          <?php else: ?>
            <a href="javascript:void(0);" class="btn btn-outline-primary disabled" aria-disabled="true" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; border-color: #1E3A5F; color: #1E3A5F; opacity: 0.5; pointer-events: none;">
              <i data-lucide="file-down" style="width: 18px; height: 18px;"></i> Télécharger PV (PDF)
            </a>
          <?php endif; ?>
          <button onclick="window.print();" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer Direct
          </button>
        </div>
      </div>

      <!-- Filtres de Sélection Classe & Semestre -->
      <div class="card no-print" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <form method="GET" action="<?= url('bulletin/pvClasse') ?>" id="filter-pv-form">
          <?php if (!empty($selectedAnneeCode)): ?>
            <input type="hidden" name="annee_code" value="<?= htmlspecialchars($selectedAnneeCode) ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end;">
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Classe <span style="color:#EF4444;">*</span></label>
              <select name="classe_code" class="form-control select2" required style="width: 100%;">
                <?php if (empty($classes)): ?>
                  <option value="">-- Aucune classe disponible --</option>
                <?php else: ?>
                  <?php foreach ($classes as $c): ?>
                    <option value="<?= htmlspecialchars($c['code_classe']) ?>" <?= ($selectedClasseCode === $c['code_classe']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['libelle_classe']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Semestre <span style="color:#EF4444;">*</span></label>
              <select name="semestre_code" class="form-control select2" <?= !empty($semestres) ? 'required' : '' ?> style="width: 100%;">
                <?php if (empty($semestres)): ?>
                  <option value="">-- Aucun semestre configuré --</option>
                <?php else: ?>
                  <?php foreach ($semestres as $s): ?>
                    <option value="<?= htmlspecialchars($s['code_semestre']) ?>" <?= ($selectedSemestreCode === $s['code_semestre']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['libelle_semestre']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; width: 100%; padding: 10px;">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; vertical-align: middle;"></i> Générer le PV
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Statut & Actions de Délibération Annuelle Officielle (non imprimable) -->
      <?php if (!empty($selectedClasseCode) && !empty($classeInfo)):
        $isDelibValidee = !empty($deliberationSummary['is_delibere']);
        $isDelibProvisoire = !$isDelibValidee && !empty($deliberationSummary['has_provisoire']);
        $dateDelibFmt = !empty($deliberationSummary['last_date_deliberation'])
          ? date('d/m/Y à H:i', strtotime($deliberationSummary['last_date_deliberation']))
          : '';
        $nbNonAdmisDelib = (int)($deliberationSummary['nb_ajournes'] ?? 0)
          + (int)($deliberationSummary['nb_redoublants'] ?? 0)
          + (int)($deliberationSummary['nb_exclus'] ?? 0);
        $bannerBg = $isDelibValidee ? '#F0FDF4' : ($isDelibProvisoire ? '#FFFBEB' : '#F8FAFC');
        $bannerBorder = $isDelibValidee ? '#86EFAC' : ($isDelibProvisoire ? '#FDE68A' : '#E2E8F0');
        $badgeBg = $isDelibValidee ? '#15803D' : ($isDelibProvisoire ? '#B45309' : '#475569');
      ?>
      <div class="card no-print" id="deliberation-status-card" style="background: <?= $bannerBg ?>; border-radius: 12px; padding: 16px 20px; border: 1px solid <?= $bannerBorder ?>; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: flex-start; gap: 12px; flex: 1; min-width: 260px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: <?= $badgeBg ?>; color: #FFFFFF; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="<?= $isDelibValidee ? 'shield-check' : ($isDelibProvisoire ? 'unlock' : 'scale') ?>" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <strong style="font-size: 14px; color: #0F172A;">Délibération Annuelle de la Classe</strong>
                <?php if ($isDelibValidee): ?>
                  <span style="font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 999px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC;">
                    Validée &amp; Figée
                  </span>
                <?php elseif ($isDelibProvisoire): ?>
                  <span style="font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 999px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                    Provisoire (Réouverte)
                  </span>
                <?php else: ?>
                  <span style="font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 999px; background: #E2E8F0; color: #334155;">
                    Non délibérée (Calcul en direct)
                  </span>
                <?php endif; ?>
              </div>
              <div style="font-size: 12.5px; color: #475569; margin-top: 4px; line-height: 1.45;">
                <?php if ($isDelibValidee): ?>
                  Les décisions annuelles officielles sont enregistrées et réutilisées directement (réinscription, fiche étudiant, bulletins S2) sans recalcul.
                  <?php if ($dateDelibFmt !== ''): ?>
                    <br><span style="color: #166534; font-weight: 600;">Validée le <?= htmlspecialchars($dateDelibFmt) ?><?= !empty($deliberationSummary['delibere_par_nom']) ? (' par ' . htmlspecialchars($deliberationSummary['delibere_par_nom'])) : '' ?></span>
                  <?php endif; ?>
                  <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; font-size: 12px; font-weight: 700;">
                    <span style="color: #15803D;">Admis : <?= (int)($deliberationSummary['nb_admis'] ?? 0) ?></span>
                    <span style="color: #991B1B;">Ajournés / Autres : <?= $nbNonAdmisDelib ?></span>
                    <?php if (!empty($deliberationSummary['nb_rachetes'])): ?>
                      <span style="color: #B45309;">Dont rachetés : <?= (int)$deliberationSummary['nb_rachetes'] ?></span>
                    <?php endif; ?>
                    <?php if (!empty($deliberationSummary['nb_en_attente'])): ?>
                      <span style="color: #64748B;">En attente : <?= (int)$deliberationSummary['nb_en_attente'] ?></span>
                    <?php endif; ?>
                  </div>
                <?php elseif ($isDelibProvisoire): ?>
                  La délibération de cette classe est actuellement en mode provisoire. Revalidez-la pour figer à nouveau les décisions annuelles officielles.
                <?php else: ?>
                  Validez la délibération en fin d'année pour calculer une seule fois les moyennes annuelles et figer officiellement les décisions du conseil de classe (avec possibilité d'ajuster les rachats).
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if ($canManageDeliberations && $canWriteYear && !empty($pvRows)): ?>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
              <?php if (!$isDelibValidee): ?>
                <button type="button" id="btn-valider-deliberation" data-mode="validate" class="btn btn-success" style="background: #15803D; border-color: #15803D; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 7px; cursor: pointer;">
                  <i data-lucide="check-circle-2" style="width: 17px; height: 17px;"></i> Valider &amp; Figer la Délibération Annuelle
                </button>
              <?php else: ?>
                <button type="button" id="btn-valider-deliberation" data-mode="recalculate" class="btn btn-outline-primary" style="border-color: #1E3A5F; color: #1E3A5F; background: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                  <i data-lucide="refresh-cw" style="width: 15px; height: 15px;"></i> Recalculer &amp; Mettre à jour
                </button>
                <button type="button" id="btn-reouvrir-deliberation" class="btn btn-outline-warning" style="border-color: #D97706; color: #B45309; background: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                  <i data-lucide="unlock" style="width: 15px; height: 15px;"></i> Rouvrir (Provisoire)
                </button>
              <?php endif; ?>
            </div>
          <?php elseif ($canManageDeliberations && !$canWriteYear): ?>
            <span style="font-size: 12px; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 6px 12px; border-radius: 8px; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="lock" style="width: 14px; height: 14px;"></i> Année clôturée (lecture seule)
            </span>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- PV  ET IMPRIMABLE -->
      <div class="card pv-container" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <!-- En-tête  de l'établissement -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1E3A5F; padding-bottom: 16px; margin-bottom: 24px;">
          <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">
              <?= htmlspecialchars($nomEtab) ?>
            </h2>
            <p style="font-size: 13px; font-weight: 600; color: #475569; margin: 4px 0 0 0;">
              <?= htmlspecialchars(!empty($etab['titre_direction_pedagogique']) ? $etab['titre_direction_pedagogique'] . ' & Commission des Examens et Délibérations' : 'Direction des Études & Commission des Examens et Délibérations') ?>
            </p>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 16px; font-weight: 800; color: #0F172A;">PROCES-VERBAL DE DÉLIBÉRATION</div>
            <div style="font-size: 13px; color: #64748B; font-weight: 600; margin-top: 2px;">
              Année Académique : <?= htmlspecialchars($anneeLibelle) ?>
            </div>
          </div>
        </div>

        <!-- Meta infos de la Classe -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: #F8FAFC; padding: 14px 18px; border-radius: 8px; border: 1px solid #E2E8F0; margin-bottom: 24px;">
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Classe :</strong> <br><span style="font-size: 14px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_classe'] ?? '-') ?></span></div>
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Filière / Spécialité :</strong> <br><span style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_filiere'] ?? '-') ?></span></div>
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Niveau d'Études :</strong> <br><span style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_niveau'] ?? '-') ?></span></div>
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Période / Semestre :</strong> <br><span style="font-size: 13px; font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($selectedSemestreLibelle) ?></span></div>
        </div>

        <!-- Badges Statistiques de Classe -->
        <div class="no-print" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 24px;">
          <div style="background: #EFF6FF; padding: 12px 16px; border-radius: 8px; border: 1px solid #BFDBFE;">
            <div style="font-size: 11px; font-weight: 700; color: #1E40AF; text-transform: uppercase;">Total Élèves</div>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F;"><?= $statsClasse['totalEleves'] ?? 0 ?></div>
          </div>
          <div style="background: #F0FDF4; padding: 12px 16px; border-radius: 8px; border: 1px solid #BBF7D0;">
            <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase;">Moyenne Classe</div>
            <div style="font-size: 20px; font-weight: 800; color: #15803D;"><?= number_format((float)($statsClasse['moyenneGeneraleClasse'] ?? 0), 2) ?> / 20</div>
          </div>
          <div style="background: #FEFCE8; padding: 12px 16px; border-radius: 8px; border: 1px solid #FEF08A;">
            <div style="font-size: 11px; font-weight: 700; color: #854D0E; text-transform: uppercase;">Plus haute moyenne</div>
            <div style="font-size: 20px; font-weight: 800; color: #A16207;"><?= number_format((float)($statsClasse['moyenneMax'] ?? 0), 2) ?> / 20</div>
          </div>
          <div style="background: #FDF2F8; padding: 12px 16px; border-radius: 8px; border: 1px solid #FBCFE8;">
            <div style="font-size: 11px; font-weight: 700; color: #9D174D; text-transform: uppercase;">Taux de Réussite</div>
            <div style="font-size: 20px; font-weight: 800; color: #BE185D;"><?= number_format((float)($statsClasse['tauxAdmis'] ?? 0), 1) ?> %</div>
          </div>
        </div>

        <!-- Tableau récapitulatif  (PV Matrix) -->
        <div style="width: 100%; overflow-x: auto;">
          <table class="table display" style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
              <tr style="background: #1E3A5F; color: #FFFFFF; text-align: left;">
                <th style="padding: 10px; width: 40px; border: 1px solid #1E3A5F; text-align: center;">Rang</th>
                <th style="padding: 10px; border: 1px solid #1E3A5F; width: 110px;">Matricule</th>
                <th style="padding: 10px; border: 1px solid #1E3A5F; min-width: 160px;">Nom & Prénom Élève</th>
                <?php foreach ($matieres as $m): ?>
                  <th style="padding: 8px; border: 1px solid #334155; text-align: center; font-size: 11px;" title="<?= htmlspecialchars($m['libelle_matiere']) ?>">
                    <?= htmlspecialchars(mb_strimwidth($m['libelle_matiere'], 0, 14, '...')) ?>
                    <br><small style="color: #94A3B8; font-weight: 400;">Coef <?= (float)$m['coefficient'] ?></small>
                  </th>
                <?php endforeach; ?>
                <th style="padding: 10px; border: 1px solid #1E3A5F; text-align: center; width: 70px;">Total Pts</th>
                <th style="padding: 10px; border: 1px solid #1E3A5F; text-align: center; width: 80px; font-weight: 800;">Moyenne</th>
                <th style="padding: 10px; border: 1px solid #1E3A5F; text-align: center; width: 90px;">Décision</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pvRows)): ?>
                <tr>
                  <td colspan="<?= 6 + count($matieres) ?>" style="padding: 24px; text-align: center; color: #64748B;">
                    Aucun étudiant inscrit n'a été trouvé pour cette classe.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($pvRows as $row): 
                  $inscCodeRow = $row['inscription_code'] ?? '';
                  $delibRow = ($inscCodeRow !== '' && isset($deliberationsMap[$inscCodeRow])) ? $deliberationsMap[$inscCodeRow] : null;
                  $hasValidatedDelib = ($delibRow !== null && (($delibRow['statut_deliberation'] ?? '') === STATUTS::VALIDE));

                  $isNonEval = ($row['decision'] === 'Non éval.' || (float)($row['total_coef'] ?? 0) <= 0);
                  $isAdmis = (!$isNonEval && $row['decision'] === 'Admis(e)');
                  $isTop3 = ($row['rang'] !== null && (int)$row['rang'] <= 3);
                  $moyBg = $isNonEval ? '#F8FAFC' : ($isAdmis ? '#F0FDF4' : '#FEF2F2');
                  $moyColor = $isNonEval ? '#64748B' : ($isAdmis ? '#15803D' : '#991B1B');

                  $displayDecision = $row['decision'];
                  $decBg = $isNonEval ? '#F1F5F9' : ($isAdmis ? '#DCFCE7' : '#FEE2E2');
                  $decColor = $isNonEval ? '#475569' : ($isAdmis ? '#166534' : '#991B1B');
                  $isRacheteRow = false;
                  $obsJuryRow = '';

                  if ($isFinalSemestre && $hasValidatedDelib) {
                    $decCode = $delibRow['decision_finale'] ?? 'en_attente';
                    $displayDecision = !empty($delibRow['libelle_decision']) ? $delibRow['libelle_decision'] : ModelDeliberation::defaultLabelForDecision($decCode, !empty($delibRow['est_rachete']));
                    $isRacheteRow = !empty($delibRow['est_rachete']);
                    $obsJuryRow = (string)($delibRow['observation_jury'] ?? '');
                    if ($decCode === 'admis') {
                      $decBg = '#DCFCE7';
                      $decColor = '#166534';
                    } elseif ($decCode === 'en_attente') {
                      $decBg = '#F1F5F9';
                      $decColor = '#475569';
                    } elseif ($decCode === 'redouble') {
                      $decBg = '#FEF3C7';
                      $decColor = '#92400E';
                    } else {
                      $decBg = '#FEE2E2';
                      $decColor = '#991B1B';
                    }
                  }
                ?>
                  <tr style="border-bottom: 1px solid #E2E8F0; <?= $isTop3 ? 'background: #F8FAFC;' : '' ?>">
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-weight: 800; color: #1E3A5F;">
                      <?php if ($row['rang'] !== null): ?>
                        <?= (int)$row['rang'] ?><sup><?= ((int)$row['rang'] === 1) ? 'er' : 'e' ?></sup><?= !empty($row['ex_aequo']) ? ' <small style="font-weight: 600;">ex</small>' : '' ?>
                      <?php else: ?>
                        <span style="color: #94A3B8;">-</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; font-weight: 700; color: #475569;">
                      <?= htmlspecialchars($row['matricule'] ?: '-') ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; font-weight: 700; color: #0F172A;">
                      <?= htmlspecialchars($row['nom_prenom']) ?>
                    </td>
                    <?php foreach ($matieres as $m): 
                      $val = $row['notes_matieres'][$m['code_matiere']] ?? null;
                    ?>
                      <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-weight: 600; color: <?= ($val !== null && $val < 10) ? '#DC2626' : '#0F172A' ?>;">
                        <?= $val !== null ? number_format($val, 2) : '-' ?>
                      </td>
                    <?php endforeach; ?>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-weight: 700; color: #334155;">
                      <?= $isNonEval ? '-' : number_format($row['total_points'], 2) ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-size: 13px; font-weight: 800; background: <?= $moyBg ?>; color: <?= $moyColor ?>;">
                      <?= $isNonEval ? '-' : number_format($row['moyenne_generale'], 2) ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-weight: 700; font-size: 11px;">
                      <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 3px;">
                        <div style="display: inline-flex; align-items: center; gap: 4px; flex-wrap: wrap; justify-content: center;">
                          <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; background: <?= $decBg ?>; color: <?= $decColor ?>;" <?= $obsJuryRow !== '' ? ('title="Observation jury : ' . htmlspecialchars($obsJuryRow) . '"') : '' ?>>
                            <?= htmlspecialchars($displayDecision) ?>
                          </span>
                          <?php if ($canManageDeliberations && $canWriteYear && $delibRow !== null): ?>
                            <button type="button"
                              class="btn-edit-decision-jury no-print"
                              title="Ajuster la décision officielle du jury (rachat, redoublement, observation)"
                              data-inscription="<?= htmlspecialchars($inscCodeRow) ?>"
                              data-etudiant="<?= htmlspecialchars($row['nom_prenom']) ?>"
                              data-matricule="<?= htmlspecialchars($row['matricule'] ?: '-') ?>"
                              data-decision="<?= htmlspecialchars($delibRow['decision_finale'] ?? 'en_attente') ?>"
                              data-libelle="<?= htmlspecialchars($delibRow['libelle_decision'] ?? '') ?>"
                              data-rachete="<?= !empty($delibRow['est_rachete']) ? '1' : '0' ?>"
                              data-observation="<?= htmlspecialchars($delibRow['observation_jury'] ?? '') ?>"
                              data-moy-s1="<?= $delibRow['moyenne_s1'] !== null ? number_format((float)$delibRow['moyenne_s1'], 2) : '-' ?>"
                              data-moy-s2="<?= $delibRow['moyenne_s2'] !== null ? number_format((float)$delibRow['moyenne_s2'], 2) : '-' ?>"
                              data-moy-exam="<?= $delibRow['moyenne_examen'] !== null ? number_format((float)$delibRow['moyenne_examen'], 2) : '-' ?>"
                              data-moy-ann="<?= $delibRow['moyenne_annuelle'] !== null ? number_format((float)$delibRow['moyenne_annuelle'], 2) : '-' ?>"
                              style="background: #F8FAFC; border: 1px solid #CBD5E1; color: #334155; border-radius: 5px; width: 24px; height: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                              <i data-lucide="gavel" style="width: 13px; height: 13px;"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                        <?php if ($isRacheteRow): ?>
                          <span style="display: inline-block; padding: 1px 6px; border-radius: 4px; background: #FEF3C7; color: #92400E; font-size: 10px; font-weight: 800;">Racheté jury</span>
                        <?php endif; ?>
                        <?php if (!$isFinalSemestre && $hasValidatedDelib): ?>
                          <span class="no-print" style="font-size: 10px; color: #475569; font-weight: 600;">
                            Annuel : <?= htmlspecialchars($delibRow['libelle_decision'] ?: $delibRow['decision_finale']) ?><?= !empty($delibRow['est_rachete']) ? ' (Racheté)' : '' ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Bloc de Signatures s (pour Impression) -->
        <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; text-align: center;">
          <div style="border-top: 1px dashed #CBD5E1; padding-top: 10px;">
            <strong style="font-size: 12px; color: #1E3A5F;">Le Chef de Département / Enseignant</strong>
            <div style="height: 60px;"></div>
            <small style="color: #94A3B8;"><?= htmlspecialchars($mentionCachet) ?></small>
          </div>
          <div style="border-top: 1px dashed #CBD5E1; padding-top: 10px;">
            <strong style="font-size: 12px; color: #1E3A5F;"><?= htmlspecialchars($titreDirPed) ?></strong>
            <?php if (!empty($nomDirPed)): ?>
              <div style="font-size: 11.5px; font-weight: 700; color: #0F172A; margin-top: 6px;"><?= htmlspecialchars($nomDirPed) ?></div>
              <div style="height: 38px;"></div>
            <?php else: ?>
              <div style="height: 60px;"></div>
            <?php endif; ?>
            <small style="color: #94A3B8;"><?= htmlspecialchars($mentionCachet) ?></small>
          </div>
          <div style="border-top: 1px dashed #CBD5E1; padding-top: 10px;">
            <strong style="font-size: 12px; color: #1E3A5F;"><?= htmlspecialchars($titreDirGen) ?></strong>
            <?php if (!empty($nomDirGen)): ?>
              <div style="font-size: 11.5px; font-weight: 700; color: #0F172A; margin-top: 6px;"><?= htmlspecialchars($nomDirGen) ?></div>
              <div style="height: 38px;"></div>
            <?php else: ?>
              <div style="height: 60px;"></div>
            <?php endif; ?>
            <small style="color: #94A3B8;"><?= htmlspecialchars($mentionCachet) ?></small>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php if ($canManageDeliberations && $canWriteYear): ?>
<!-- Modal Ajustement Décision Individuelle du Jury (non imprimable) -->
<div id="modal-decision-jury" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 560px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.28); overflow: hidden;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width: 36px; height: 36px; border-radius: 9px; background: rgba(255,255,255,0.16); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="gavel" style="width: 18px; height: 18px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: #FFFFFF;">Décision Officielle du Jury</h3>
          <div id="jury-modal-student-label" style="font-size: 12px; color: #CBD5E1; margin-top: 2px;">Étudiant</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-jury" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; width: 30px; height: 30px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;">&times;</button>
    </div>

    <form id="form-decision-jury" style="padding: 20px 22px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
      <input type="hidden" name="inscription_code" id="jury-inscription-code" value="">

      <!-- Rappel des moyennes figées -->
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 10px 12px; text-align: center;">
        <div>
          <div style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Moy. S1</div>
          <div id="jury-moy-s1" style="font-size: 13px; font-weight: 800; color: #0F172A;">-</div>
        </div>
        <div>
          <div style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Moy. S2</div>
          <div id="jury-moy-s2" style="font-size: 13px; font-weight: 800; color: #0F172A;">-</div>
        </div>
        <div>
          <div style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Examen</div>
          <div id="jury-moy-exam" style="font-size: 13px; font-weight: 800; color: #0F172A;">-</div>
        </div>
        <div>
          <div style="font-size: 10px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Annuelle</div>
          <div id="jury-moy-ann" style="font-size: 14px; font-weight: 900; color: #1E3A5F;">-</div>
        </div>
      </div>

      <div>
        <label for="jury-decision-finale" style="font-size: 12px; font-weight: 700; color: #0F172A; display: block; margin-bottom: 5px;">Décision finale <span style="color:#EF4444;">*</span></label>
        <select name="decision_finale" id="jury-decision-finale" class="form-control" required style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1;">
          <option value="admis">Admis(e) — Passage en classe supérieure</option>
          <option value="ajourne">Ajourné(e)</option>
          <option value="redouble">Redoublement</option>
          <option value="exclu">Exclu(e)</option>
          <option value="en_attente">En attente</option>
        </select>
      </div>

      <div id="jury-rachat-wrapper" style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 12px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; font-size: 12.5px; font-weight: 700; color: #92400E;">
          <input type="checkbox" name="est_rachete" id="jury-est-rachete" value="1" style="width: 16px; height: 16px;">
          Admis(e) par rachat exceptionnel du jury
        </label>
      </div>

      <div>
        <label for="jury-libelle-decision" style="font-size: 12px; font-weight: 700; color: #0F172A; display: block; margin-bottom: 5px;">Libellé officiel affiché (PV, Bulletin S2, Fiche)</label>
        <input type="text" name="libelle_decision" id="jury-libelle-decision" class="form-control" maxlength="150" placeholder="Ex : Admis(e) par rachat du jury" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1;">
      </div>

      <div>
        <label for="jury-observation" style="font-size: 12px; font-weight: 700; color: #0F172A; display: block; margin-bottom: 5px;">Observation / Motif du jury (optionnel)</label>
        <textarea name="observation_jury" id="jury-observation" rows="3" class="form-control" placeholder="Ex : Assiduité exemplaire et progression constante au S2..." style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1; resize: vertical;"></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 14px; margin-top: 4px;">
        <button type="button" class="btn btn-outline-secondary btn-close-modal-jury" style="border-radius: 8px; padding: 8px 18px; font-weight: 700; cursor: pointer;">Annuler</button>
        <button type="submit" id="btn-submit-decision-jury" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; border-radius: 8px; padding: 8px 20px; font-weight: 700; cursor: pointer;">Enregistrer la Décision</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script type="application/json" id="pv-deliberation-config">
<?= json_encode([
  'racine' => RACINE,
  'csrfToken' => Validator::generateCsrfToken(),
  'classeCode' => $selectedClasseCode,
  'classeLibelle' => $classeInfo['libelle_classe'] ?? $selectedClasseCode,
  'anneeCode' => $selectedAnneeCode,
  'anneeLibelle' => $anneeLibelle,
  'validerUrl' => url('bulletin/validerDeliberation'),
  'modifierUrl' => url('bulletin/modifierDecisionJury'),
  'reouvrirUrl' => url('bulletin/reouvrirDeliberation')
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<?php
ViewContext::setScripts(['public/assets/js/modules/notes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
