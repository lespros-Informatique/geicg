<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$encryptedId = $encryptedId ?? '';
$etablissement = (!empty($etablissement) && is_array($etablissement)) ? $etablissement : [];
$nomEtablissement = !empty($etablissement['nom_etablissement']) ? $etablissement['nom_etablissement'] : '';
$titreDirectionPedagogique = !empty($etablissement['titre_direction_pedagogique']) ? $etablissement['titre_direction_pedagogique'] : 'Direction des Études';
$nomDirecteurEtudes = !empty($etablissement['nom_directeur_etudes']) ? $etablissement['nom_directeur_etudes'] : '';
$villeSignature = !empty($etablissement['ville_signature']) ? $etablissement['ville_signature'] : (!empty($etablissement['ville_etablissement']) ? $etablissement['ville_etablissement'] : '');
$matieresNotes = $matieresNotes ?? [];
$totalPoints = $totalPoints ?? 0;
$totalCoefficients = $totalCoefficients ?? 0;
$moyenneGenerale = $moyenneGenerale ?? 0;
$mention = $mention ?? '-';
$decision = $decision ?? '-';
$rang = $rang ?? 1;
$rangFr = $rangFr ?? ($rang == 1 ? '1er' : ($rang . 'ème'));
$totalElevesClasse = $totalElevesClasse ?? 1;
$moyenneMinClasse = $moyenneMinClasse ?? 0;
$moyenneMaxClasse = $moyenneMaxClasse ?? 0;
$moyenneMoyClasse = $moyenneMoyClasse ?? 0;
$estPremiereAnnee = !empty($est_premiere_annee);
$estDeuxiemeSemestre = !empty($est_deuxieme_semestre);
$moyenneRecap = isset($moyenneRecap) ? (float)$moyenneRecap : (float)$moyenneGenerale;
$rangRecapFr = !empty($rangRecapFr) ? $rangRecapFr : $rangFr;
$moyenneMinRecap = isset($moyenneMinRecap) ? (float)$moyenneMinRecap : (float)$moyenneMinClasse;
$moyenneMaxRecap = isset($moyenneMaxRecap) ? (float)$moyenneMaxRecap : (float)$moyenneMaxClasse;
$moyenneMoyRecap = isset($moyenneMoyRecap) ? (float)$moyenneMoyRecap : (float)$moyenneMoyClasse;
$coefSem1 = (int)($coef_semestre1 ?? 2);
$coefSem2 = (int)($coef_semestre2 ?? 2);
$coefExam = (int)($coef_examen ?? 1);
$heuresAbsences = $heuresAbsences ?? '0 H';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Top Action Bar -->
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Relevé de Notes & Bulletin Académique</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Étudiant : <strong style="color: #0F172A;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></strong> &bull; Matricule : <strong style="color: #1E3A5F;"><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></strong>
          </p>
        </div>
        <div class="page-header-actions" style="display: flex; gap: 10px; align-items: center;">
          <a href="<?= url('bulletin/list') ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= url() ?>bulletin/imprimerBulletinPdf/<?= $encryptedId ?>?semestre=<?= urlencode($selectedSemestreCode) ?>" target="_blank" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; border-color: #1E3A5F; color: #1E3A5F;">
            <i data-lucide="file-down" style="width: 18px; height: 18px;"></i> Télécharger PDF
          </a>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 20px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer Direct
          </button>
        </div>
      </div>

      <!-- Semester Tabs / Filter Bar (No-Print) -->
      <?php if (!empty($semestres)): ?>
      <div class="card no-print" style="background: #FFFFFF; border-radius: 10px; padding: 14px 20px; border: 1px solid #E2E8F0; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="calendar" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <span style="font-weight: 700; font-size: 13px; color: #334155;">Période / Semestre :</span>
          </div>
          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php foreach ($semestres as $sem): ?>
              <?php $isActive = ($sem['code_semestre'] === $selectedSemestreCode); ?>
              <a href="<?= url() ?>bulletin/details/<?= $encryptedId ?>?semestre=<?= urlencode($sem['code_semestre']) ?>"
                 class="btn btn-sm"
                 style="font-weight: 700; border-radius: 6px; padding: 6px 16px; border: 1px solid <?= $isActive ? '#1E3A5F' : '#CBD5E1' ?>; background: <?= $isActive ? '#1E3A5F' : '#FFFFFF' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>;">
                <?= htmlspecialchars($sem['libelle_semestre']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Printable Report Card Container -->
      <div class="card card-bulletin" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <!-- Institutional Header (University / School Header) -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #1E3A5F; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 56px; height: 56px; border-radius: 10px; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="graduation-cap" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
              <h2 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($nomEtablissement) ?></h2>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;"><?= htmlspecialchars($titreDirectionPedagogique) ?></p>
            </div>
          </div>
          <div style="text-align: right;">
            <div style="display: inline-block; padding: 6px 14px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px;">
              <span style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase;">
                BULLETIN DU <?= htmlspecialchars(!empty($selectedSemestreCode) ? (array_column($semestres, 'libelle_semestre', 'code_semestre')[$selectedSemestreCode] ?? 'SEMESTRE') : 'SEMESTRE') ?>
              </span>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
              Année Académique : <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></strong>
            </div>
          </div>
        </div>

        <!-- Student & Enrollment Information Block -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px 24px; margin-bottom: 24px;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 2px;">Nom & Prénom(s)</span>
              <strong style="font-size: 15px; color: #0F172A;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></strong>
            </div>
            <div>
              <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 2px;">Matricule Étudiant</span>
              <strong style="font-size: 14px; color: #1E3A5F; font-family: monospace;"><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></strong>
            </div>
            <div>
              <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 2px;">Classe & Niveau</span>
              <strong style="font-size: 14px; color: #0F172A;"><?= htmlspecialchars($item['libelle_classe'] ?? 'Non assigné') ?></strong>
            </div>
            <div>
              <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 2px;">Date & Lieu de Naissance</span>
              <span style="font-size: 13px; color: #334155;"><?= htmlspecialchars(($item['date_naissance_etudiant'] ?? '-') . (!empty($item['lieu_naissance_etudiant']) ? ' à ' . $item['lieu_naissance_etudiant'] : '')) ?></span>
            </div>
            <div>
              <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 2px;">Absences Constatées</span>
              <span style="font-size: 13px; font-weight: 700; color: #B91C1C;"><?= htmlspecialchars($heuresAbsences) ?></span>
            </div>
          </div>
        </div>

        <!-- Academic Summary Metrics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px;">
          <!-- Moyenne Card -->
          <div style="background: #1E3A5F; border-radius: 10px; padding: 16px; color: #FFFFFF; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 4px;">Moyenne Semestrielle</div>
            <div style="font-size: 26px; font-weight: 900; line-height: 1;"><?= number_format($moyenneGenerale, 2, ',', ' ') ?> <span style="font-size: 14px; font-weight: 600; opacity: 0.8;">/ 20</span></div>
          </div>
          <!-- Rang Card -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; margin-bottom: 4px;">Rang de l'Étudiant</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A;">
              <?= $totalCoefficients > 0 ? $rang . '<sup>' . ($rang == 1 ? 'er' : 'ème') . '</sup>' : '-' ?>
              <span style="font-size: 13px; font-weight: 500; color: #64748B;"> / <?= $totalElevesClasse ?></span>
            </div>
          </div>
          <!-- Mention Card -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; margin-bottom: 4px;">Mention Obtenue</div>
            <div style="font-size: 16px; font-weight: 800; color: <?= $moyenneGenerale >= 10 ? '#15803D' : '#B91C1C' ?>;">
              <?= htmlspecialchars($mention) ?>
            </div>
          </div>
          <!-- Total Coeffs Card -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; margin-bottom: 4px;">Total Coefficients</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A;"><?= $totalCoefficients ?></div>
          </div>
        </div>

        <!-- Detailed Subjects and Grades Table -->
        <div style="margin-bottom: 24px; overflow-x: auto;">
          <table class="bulletin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
              <tr>
                <th style="width: 32%;">Matière & Enseignement</th>
                <th style="width: 10%; text-align: center;">Coeff.</th>
                <th style="width: 25%;">Détail des Évaluations</th>
                <th style="width: 13%; text-align: center;">Moy. / 20</th>
                <th style="width: 10%; text-align: center;">Total Pts</th>
                <th style="width: 10%; text-align: right;">Appréciation</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($matieresNotes)): ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 30px; color: #64748B; font-style: italic;">
                    <i data-lucide="info" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 6px;"></i>
                    Aucune note enregistrée pour cet étudiant sur ce semestre.
                  </td>
                </tr>
              <?php else: ?>
                <?php 
                  $hasGen = !empty($matieresGenerales) && is_array($matieresGenerales);
                  $hasPr = !empty($matieresPro) && is_array($matieresPro);
                ?>

                <?php if ($hasGen || $hasPr): ?>
                  <!-- SECTION 1 : MATIÈRES GÉNÉRALES -->
                  <?php if ($hasGen): ?>
                    <tr style="background:#EFF6FF; border-left: 4px solid #1D4ED8;">
                      <td colspan="6" style="font-weight: 800; color: #1D4ED8; text-transform: uppercase; font-size: 12px; padding: 8px 12px;">
                        <i data-lucide="book" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;"></i> Matières Générales
                      </td>
                    </tr>
                    <?php foreach ($matieresGenerales as $m): ?>
                      <tr>
                        <td style="font-weight: 600; color: #0F172A;">
                          <?= htmlspecialchars($m['libelle']) ?>
                          <?php if (!empty($m['rang_matiere']) && $m['rang_matiere'] !== '-'): ?>
                            <span class="badge" style="background:#F1F5F9; color:#475569; font-size:10px; margin-left:6px;">Rang: <?= htmlspecialchars($m['rang_matiere']) ?></span>
                          <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #334155;"><?= $m['coefficient'] ?></td>
                        <td>
                          <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <?php foreach ($m['evaluations'] as $ev): ?>
                              <span style="font-size: 11px; padding: 2px 7px; border-radius: 4px; background: #F1F5F9; border: 1px solid #E2E8F0; color: #334155;">
                                <strong><?= htmlspecialchars($ev['type'] ?: 'Eval') ?><?= !empty($ev['coefficient']) && (float)$ev['coefficient'] > 0 ? ' (C.' . (float)$ev['coefficient'] . ')' : '' ?>:</strong> <?= number_format($ev['note'], 2, ',', ' ') ?>
                              </span>
                            <?php endforeach; ?>
                          </div>
                        </td>
                        <td style="text-align: center; font-weight: 800; color: <?= $m['moyenne'] >= 10 ? '#15803D' : '#B91C1C' ?>;">
                          <?= number_format($m['moyenne'], 2, ',', ' ') ?>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #0F172A;"><?= number_format($m['points'], 2, ',', ' ') ?></td>
                        <td style="text-align: right; font-weight: 600; font-size: 12px; color: <?= $m['moyenne'] >= 10 ? '#15803D' : '#B91C1C' ?>;">
                          <?= htmlspecialchars($m['appreciation']) ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr style="background: #F8FAFC; font-weight: 800; border-top: 1px solid #CBD5E1; border-bottom: 2px solid #E2E8F0;">
                      <td style="text-transform: uppercase; color: #1E3A5F; font-weight: 800;">TOTAL MATIÈRES GÉNÉRALES</td>
                      <td style="text-align: center; color: #1E3A5F; font-weight: 800;"><?= $totalCoefficientsGenerales ?? 0 ?></td>
                      <td style="color: #64748B; font-size: 12px;">Rang dans les générales : <strong style="color: #1E3A5F;"><?= htmlspecialchars($rangGeneralesFr ?? '-') ?></strong></td>
                      <td style="text-align: center; font-size: 14px; color: #1E3A5F; font-weight: 800;"><?= number_format((float)($moyenneGenerales ?? 0), 2, ',', ' ') ?> / 20</td>
                      <td style="text-align: center; font-size: 14px; color: #1E3A5F; font-weight: 800;"><?= number_format((float)($totalPointsGenerales ?? 0), 2, ',', ' ') ?></td>
                      <td style="text-align: right; color: <?= ($moyenneGenerales ?? 0) >= 10 ? '#15803D' : '#B91C1C' ?>; font-weight: 800;"><?= htmlspecialchars($appreciationGenerales ?? '-') ?></td>
                    </tr>
                  <?php endif; ?>

                  <!-- SECTION 2 : MATIÈRES PROFESSIONNELLES -->
                  <?php if ($hasPr): ?>
                    <tr style="background:#FFFBEB; border-left: 4px solid #B45309;">
                      <td colspan="6" style="font-weight: 800; color: #B45309; text-transform: uppercase; font-size: 12px; padding: 8px 12px;">
                        <i data-lucide="briefcase" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;"></i> Matières Professionnelles
                      </td>
                    </tr>
                    <?php foreach ($matieresPro as $m): ?>
                      <tr>
                        <td style="font-weight: 600; color: #0F172A;">
                          <?= htmlspecialchars($m['libelle']) ?>
                          <?php if (!empty($m['rang_matiere']) && $m['rang_matiere'] !== '-'): ?>
                            <span class="badge" style="background:#F1F5F9; color:#475569; font-size:10px; margin-left:6px;">Rang: <?= htmlspecialchars($m['rang_matiere']) ?></span>
                          <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #334155;"><?= $m['coefficient'] ?></td>
                        <td>
                          <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <?php foreach ($m['evaluations'] as $ev): ?>
                              <span style="font-size: 11px; padding: 2px 7px; border-radius: 4px; background: #F1F5F9; border: 1px solid #E2E8F0; color: #334155;">
                                <strong><?= htmlspecialchars($ev['type'] ?: 'Eval') ?><?= !empty($ev['coefficient']) && (float)$ev['coefficient'] > 0 ? ' (C.' . (float)$ev['coefficient'] . ')' : '' ?>:</strong> <?= number_format($ev['note'], 2, ',', ' ') ?>
                              </span>
                            <?php endforeach; ?>
                          </div>
                        </td>
                        <td style="text-align: center; font-weight: 800; color: <?= $m['moyenne'] >= 10 ? '#15803D' : '#B91C1C' ?>;">
                          <?= number_format($m['moyenne'], 2, ',', ' ') ?>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #0F172A;"><?= number_format($m['points'], 2, ',', ' ') ?></td>
                        <td style="text-align: right; font-weight: 600; font-size: 12px; color: <?= $m['moyenne'] >= 10 ? '#15803D' : '#B91C1C' ?>;">
                          <?= htmlspecialchars($m['appreciation']) ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr style="background: #F8FAFC; font-weight: 800; border-top: 1px solid #CBD5E1; border-bottom: 2px solid #E2E8F0;">
                      <td style="text-transform: uppercase; color: #B45309; font-weight: 800;">TOTAL MATIÈRES PROFESSIONNELLES</td>
                      <td style="text-align: center; color: #B45309; font-weight: 800;"><?= $totalCoefficientsPro ?? 0 ?></td>
                      <td style="color: #64748B; font-size: 12px;">Rang dans les professionnelles : <strong style="color: #B45309;"><?= htmlspecialchars($rangProFr ?? '-') ?></strong></td>
                      <td style="text-align: center; font-size: 14px; color: #B45309; font-weight: 800;"><?= number_format((float)($moyennePro ?? 0), 2, ',', ' ') ?> / 20</td>
                      <td style="text-align: center; font-size: 14px; color: #B45309; font-weight: 800;"><?= number_format((float)($totalPointsPro ?? 0), 2, ',', ' ') ?></td>
                      <td style="text-align: right; color: <?= ($moyennePro ?? 0) >= 10 ? '#15803D' : '#B91C1C' ?>; font-weight: 800;"><?= htmlspecialchars($appreciationPro ?? '-') ?></td>
                    </tr>
                  <?php endif; ?>

                <?php else: ?>
                  <?php foreach ($matieresNotes as $m): ?>
                    <tr>
                      <td style="font-weight: 600; color: #0F172A;">
                        <?= htmlspecialchars($m['libelle']) ?>
                      </td>
                      <td style="text-align: center; font-weight: 600; color: #334155;"><?= $m['coefficient'] ?></td>
                      <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                          <?php foreach ($m['evaluations'] as $ev): ?>
                            <span style="font-size: 11px; padding: 2px 7px; border-radius: 4px; background: #F1F5F9; border: 1px solid #E2E8F0; color: #334155;">
                              <strong><?= htmlspecialchars($ev['type'] ?: 'Eval') ?><?= !empty($ev['coefficient']) && (float)$ev['coefficient'] > 0 ? ' (C.' . (float)$ev['coefficient'] . ')' : '' ?>:</strong> <?= number_format($ev['note'], 2, ',', ' ') ?>
                            </span>
                          <?php endforeach; ?>
                        </div>
                      </td>
                      <td style="text-align: center; font-weight: 800; color: <?= $m['moyenne'] >= 10 ? '#15803D' : '#B91C1C' ?>;">
                        <?= number_format($m['moyenne'], 2, ',', ' ') ?>
                      </td>
                      <td style="text-align: center; font-weight: 700; color: #0F172A;"><?= number_format($m['points'], 2, ',', ' ') ?></td>
                      <td style="text-align: right; font-weight: 600; font-size: 12px; color: <?= $m['moyenne'] >= 10 ? '#15803D' : '#B91C1C' ?>;">
                        <?= htmlspecialchars($m['appreciation']) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>

                <!-- Total Row -->
                <tr style="background: #EFF6FF; font-weight: 800; border-top: 2px solid #1E3A5F;">
                  <td style="text-transform: uppercase; color: #1E3A5F; font-weight: 900;">BILAN GÉNÉRAL PONDÉRÉ</td>
                  <td style="text-align: center; color: #1E3A5F; font-weight: 900;"><?= $totalCoefficients ?></td>
                  <td style="color: #64748B; font-size: 12px;"><?= count($matieresNotes) ?> matière(s) évaluée(s)</td>
                  <td style="text-align: center; font-size: 15px; color: #1E3A5F; font-weight: 900;"><?= number_format($moyenneGenerale, 2, ',', ' ') ?> / 20</td>
                  <td style="text-align: center; font-size: 15px; color: #1E3A5F; font-weight: 900;"><?= number_format($totalPoints, 2, ',', ' ') ?></td>
                  <td style="text-align: right; color: <?= $moyenneGenerale >= 10 ? '#15803D' : '#B91C1C' ?>; font-weight: 900;"><?= htmlspecialchars($mention) ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Récapitulatif Semestriel / Annuel & Appréciation du Conseil -->
        <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px 20px;">
            <?php if (!$estDeuxiemeSemestre): ?>
              <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 10px; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; padding-bottom: 6px;">
                RÉCAPITULATIF SEMESTRE
              </div>
              <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px;">
                <span style="background: #E2E8F0; color: #0F172A; font-weight: 800; font-size: 14px; padding: 4px 12px; border-radius: 6px;">
                  Moyenne : <?= number_format($moyenneRecap, 2, ',', ' ') ?> / 20
                </span>
                <span style="font-size: 13px; font-weight: 800; color: #1E3A5F;">
                  Rang : <?= htmlspecialchars($rangRecapFr) ?> / <?= htmlspecialchars((string)$totalElevesClasse) ?> Étudiants
                </span>
              </div>
            <?php else: ?>
              <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 10px; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; padding-bottom: 6px;">
                RÉCAPITULATIF ANNUEL
              </div>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; font-size: 13px; margin-bottom: 10px;">
                <div>Moy. Semestre 1 <span style="color:#64748B; font-size:11px;">(Coef <?= $coefSem1 ?>)</span> : <strong style="color: #0F172A;"><?= isset($moy_semestre1) && $moy_semestre1 !== null ? number_format((float)$moy_semestre1, 2, ',', ' ') : '-' ?></strong></div>
                <div>Moy. Semestre 2 <span style="color:#64748B; font-size:11px;">(Coef <?= $coefSem2 ?>)</span> : <strong style="color: #0F172A;"><?= isset($moy_semestre2) && $moy_semestre2 !== null ? number_format((float)$moy_semestre2, 2, ',', ' ') : '-' ?></strong></div>
                <?php if ($estPremiereAnnee): ?>
                  <div>Moy. Examen <span style="color:#64748B; font-size:11px;">(Coef <?= $coefExam ?>)</span> : <strong style="color: #0F172A;"><?= isset($moy_examen) && $moy_examen !== null ? number_format((float)$moy_examen, 2, ',', ' ') : '-' ?></strong></div>
                <?php endif; ?>
                <div>
                  <span style="background: #E2E8F0; color: #0F172A; font-weight: 800; padding: 3px 10px; border-radius: 6px; display: inline-block;">
                    Moy. Générale : <?= number_format($moyenneRecap, 2, ',', ' ') ?> / 20
                  </span>
                </div>
              </div>
              <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 8px;">
                Rang : <?= htmlspecialchars($rangRecapFr) ?> / <?= htmlspecialchars((string)$totalElevesClasse) ?> Étudiants
              </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 12px; color: #64748B; border-top: 1px dashed #CBD5E1; padding-top: 8px;">
              <span>Moy. Min : <strong style="color: #B91C1C;"><?= number_format($moyenneMinRecap, 2, ',', ' ') ?></strong></span>
              <span>Moy. Max : <strong style="color: #15803D;"><?= number_format($moyenneMaxRecap, 2, ',', ' ') ?></strong></span>
              <span>Moy. de la classe : <strong style="color: #0F172A;"><?= number_format($moyenneMoyRecap, 2, ',', ' ') ?></strong></span>
            </div>
          </div>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px 20px; display: flex; flex-direction: column; justify-content: center;">
            <div style="font-size: 13px; font-weight: 800; color: #334155; margin-bottom: 8px; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; padding-bottom: 6px;">
              Appréciation du conseil <?= $estDeuxiemeSemestre ? '(Fin d\'Année - S2)' : '(Semestre 1)' ?>
            </div>
            <div style="font-size: 14px; font-weight: 800; color: <?= $moyenneRecap >= 10 ? '#1E3A5F' : '#B91C1C' ?>;">
              <?= htmlspecialchars($decision) ?>
            </div>
          </div>
        </div>

        <!-- Signatures & Official Stamp Block -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 60px;">Le Responsable Pédagogique</div>
            <div style="font-size: 11px; color: #94A3B8; font-style: italic;">Signature et Visa</div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 45px;">
              Fait<?= $villeSignature !== '' ? ' à ' . htmlspecialchars($villeSignature) : '' ?> le <?= date('d/m/Y') ?><br>
              <?= htmlspecialchars(mb_strtoupper($titreDirectionPedagogique)) ?>
            </div>
            <?php if ($nomDirecteurEtudes !== ''): ?>
              <div style="font-size: 13px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($nomDirecteurEtudes) ?></div>
            <?php else: ?>
              <div style="font-size: 11px; color: #94A3B8; font-style: italic;">Cachet de l'établissement</div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
