<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$classes = $classes ?? [];
$semestres = $semestres ?? [];
$selectedClasseCode = $selectedClasseCode ?? '';
$selectedSemestreCode = $selectedSemestreCode ?? '';
$classeInfo = $classeInfo ?? [];
$matieres = $matieres ?? [];
$pvRows = $pvRows ?? [];
$statsClasse = $statsClasse ?? [];
?>
<style>
@media print {
  .app-layout, .sidebar, .nav, .no-print, .main-content > nav { display: none !important; }
  .content-wrapper { padding: 0 !important; margin: 0 !important; width: 100% !important; }
  .pv-container { border: none !important; box-shadow: none !important; }
  body { background: #fff !important; color: #000 !important; font-size: 11pt; }
  table { page-break-inside: auto; }
  tr { page-break-inside: avoid; page-break-after: auto; }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Barre d'actions non-imprimable -->
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <a href="<?= RACINE ?>bulletin/list" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour Bulletins
            </a>
            <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Procès-Verbal (PV) de Notes de la Classe</h1>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Tableau récapitulatif officiel de délibération des notes et classements par classe</p>
        </div>
        <button onclick="window.print();" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer / Exporter PV
        </button>
      </div>

      <!-- Filtres de Sélection Classe & Semestre -->
      <div class="card no-print" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <form method="GET" action="<?= RACINE ?>bulletin/pvClasse" id="filter-pv-form">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end;">
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Classe <span style="color:#EF4444;">*</span></label>
              <select name="classe_code" class="form-control select2" required style="width: 100%;">
                <?php foreach ($classes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_classe']) ?>" <?= ($selectedClasseCode === $c['code_classe']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['libelle_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Semestre <span style="color:#EF4444;">*</span></label>
              <select name="semestre_code" class="form-control select2" required style="width: 100%;">
                <?php foreach ($semestres as $s): ?>
                  <option value="<?= htmlspecialchars($s['code_semestre']) ?>" <?= ($selectedSemestreCode === $s['code_semestre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['libelle_semestre']) ?>
                  </option>
                <?php endforeach; ?>
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

      <!-- PV OFFICIEL ET IMPRIMABLE -->
      <div class="card pv-container" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <!-- En-tête officiel de l'établissement -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1E3A5F; padding-bottom: 16px; margin-bottom: 24px;">
          <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">
              <?= htmlspecialchars($globalEtablissementNom ?? 'GEICG - ÉTABLISSEMENT ACADÉMIQUE') ?>
            </h2>
            <p style="font-size: 13px; font-weight: 600; color: #475569; margin: 4px 0 0 0;">
              Direction des Études & Commission des Examens et Délibérations
            </p>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 16px; font-weight: 800; color: #0F172A;">PROCES-VERBAL DE DÉLIBÉRATION</div>
            <div style="font-size: 13px; color: #64748B; font-weight: 600; margin-top: 2px;">
              Année Académique : <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '-') ?>
            </div>
          </div>
        </div>

        <!-- Meta infos de la Classe -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: #F8FAFC; padding: 14px 18px; border-radius: 8px; border: 1px solid #E2E8F0; margin-bottom: 24px;">
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Classe :</strong> <br><span style="font-size: 14px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_classe'] ?? '-') ?></span></div>
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Filière / Spécialité :</strong> <br><span style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_filiere'] ?? '-') ?></span></div>
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Niveau d'Études :</strong> <br><span style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_niveau'] ?? '-') ?></span></div>
          <div><strong style="color: #64748B; font-size: 11px; text-transform: uppercase;">Période / Semestre :</strong> <br><span style="font-size: 13px; font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($selectedSemestreCode) ?></span></div>
        </div>

        <!-- Badges Statistiques de Classe -->
        <div class="no-print" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 24px;">
          <div style="background: #EFF6FF; padding: 12px 16px; border-radius: 8px; border: 1px solid #BFDBFE;">
            <div style="font-size: 11px; font-weight: 700; color: #1E40AF; text-transform: uppercase;">Total Élèves</div>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F;"><?= $statsClasse['totalEleves'] ?? 0 ?></div>
          </div>
          <div style="background: #F0FDF4; padding: 12px 16px; border-radius: 8px; border: 1px solid #BBF7D0;">
            <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase;">Moyenne Classe</div>
            <div style="font-size: 20px; font-weight: 800; color: #15803D;"><?= $statsClasse['moyenneGeneraleClasse'] ?? 0 ?> / 20</div>
          </div>
          <div style="background: #FEFCE8; padding: 12px 16px; border-radius: 8px; border: 1px solid #FEF08A;">
            <div style="font-size: 11px; font-weight: 700; color: #854D0E; text-transform: uppercase;">Plus haute moyenne</div>
            <div style="font-size: 20px; font-weight: 800; color: #A16207;"><?= $statsClasse['moyenneMax'] ?? 0 ?> / 20</div>
          </div>
          <div style="background: #FDF2F8; padding: 12px 16px; border-radius: 8px; border: 1px solid #FBCFE8;">
            <div style="font-size: 11px; font-weight: 700; color: #9D174D; text-transform: uppercase;">Taux de Réussite</div>
            <div style="font-size: 20px; font-weight: 800; color: #BE185D;"><?= $statsClasse['tauxAdmis'] ?? 0 ?> %</div>
          </div>
        </div>

        <!-- Tableau récapitulatif officiel (PV Matrix) -->
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
                  $isAdmis = ($row['decision'] === 'Admis(e)');
                ?>
                  <tr style="border-bottom: 1px solid #E2E8F0; <?= $row['rang'] <= 3 ? 'background: #F8FAFC;' : '' ?>">
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-weight: 800; color: #1E3A5F;">
                      <?= $row['rang'] ?><sup><?= $row['rang'] == 1 ? 'er' : 'e' ?></sup>
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
                      <?= number_format($row['total_points'], 2) ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-size: 13px; font-weight: 800; background: <?= $isAdmis ? '#F0FDF4' : '#FEF2F2' ?>; color: <?= $isAdmis ? '#15803D' : '#991B1B' ?>;">
                      <?= number_format($row['moyenne_generale'], 2) ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #CBD5E1; text-align: center; font-weight: 700; font-size: 11px;">
                      <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; background: <?= $isAdmis ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $isAdmis ? '#166534' : '#991B1B' ?>;">
                        <?= htmlspecialchars($row['decision']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Bloc de Signatures Officielles (pour Impression) -->
        <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; text-align: center;">
          <div style="border-top: 1px dashed #CBD5E1; padding-top: 10px;">
            <strong style="font-size: 12px; color: #1E3A5F;">Le Chef de Département / Enseignant</strong>
            <div style="height: 60px;"></div>
            <small style="color: #94A3B8;">Signature & Cachet</small>
          </div>
          <div style="border-top: 1px dashed #CBD5E1; padding-top: 10px;">
            <strong style="font-size: 12px; color: #1E3A5F;">Le Directeur des Études</strong>
            <div style="height: 60px;"></div>
            <small style="color: #94A3B8;">Signature & Cachet</small>
          </div>
          <div style="border-top: 1px dashed #CBD5E1; padding-top: 10px;">
            <strong style="font-size: 12px; color: #1E3A5F;">Le Président du Jury / Directeur Général</strong>
            <div style="height: 60px;"></div>
            <small style="color: #94A3B8;">Signature & Cachet</small>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
