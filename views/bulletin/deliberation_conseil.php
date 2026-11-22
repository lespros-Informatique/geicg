<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? '';
$selectedClasseCode = $selectedClasseCode ?? '';
$encryptedClasseCode = $encryptedClasseCode ?? '';
$classeInfo = $classeInfo ?? null;
$anneeLibelle = $anneeLibelle ?? '-';
$estPremiereAnnee = !empty($estPremiereAnnee);
$lastSemestreCode = $lastSemestreCode ?? '';
$rows = $rows ?? [];
$summary = $summary ?? [];
$canManageDeliberations = !empty($canManageDeliberations);
$canWriteYear = !empty($canWriteYear);

$isFrozen = !empty($summary['is_delibere']);
$isDraft = !$isFrozen && !empty($summary['has_provisoire']);
$isEditable = $canManageDeliberations && $canWriteYear && !$isFrozen && !empty($rows);

$dateDelibFmt = !empty($summary['last_date_deliberation'])
    ? date('d/m/Y à H:i', strtotime($summary['last_date_deliberation']))
    : '';
$delibAuthor = trim((string)($summary['delibere_par_nom'] ?? ''));

$choiceLabels = [
    'admis_office' => ['label' => 'Admis d\'office', 'short' => 'Admis', 'bg' => '#DCFCE7', 'color' => '#166534', 'border' => '#86EFAC'],
    'admis_grace'  => ['label' => 'Admis par grâce (Rachat)', 'short' => 'Grâce', 'bg' => '#FEF3C7', 'color' => '#92400E', 'border' => '#FCD34D'],
    'redouble'     => ['label' => 'Redoublement (Recalé)', 'short' => 'Redouble', 'bg' => '#FEE2E2', 'color' => '#991B1B', 'border' => '#FCA5A5'],
    'exclu'        => ['label' => 'Exclu', 'short' => 'Exclu', 'bg' => '#F1F5F9', 'color' => '#0F172A', 'border' => '#94A3B8'],
    'en_attente'   => ['label' => 'En attente', 'short' => 'Attente', 'bg' => '#F8FAFC', 'color' => '#475569', 'border' => '#CBD5E1']
];
?>

<style>
  .delib-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
  }
  .delib-kpi-card {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 14px 16px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  }
  .delib-kpi-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #64748B;
  }
  .delib-kpi-val {
    font-size: 22px;
    font-weight: 900;
    margin-top: 4px;
    line-height: 1.1;
  }
  .delib-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .delib-filter-pill:hover {
    border-color: #1E3A5F;
    color: #1E3A5F;
  }
  .delib-filter-pill.active {
    background: #1E3A5F;
    border-color: #1E3A5F;
    color: #FFFFFF;
  }
  .delib-filter-pill .pill-count {
    background: rgba(15, 23, 42, 0.08);
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
  }
  .delib-filter-pill.active .pill-count {
    background: rgba(255, 255, 255, 0.22);
    color: #FFFFFF;
  }
  .delib-choice-group {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
  }
  .delib-choice-btn {
    padding: 5px 9px;
    border-radius: 7px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #475569;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s ease;
    line-height: 1.2;
    white-space: nowrap;
  }
  .delib-choice-btn:hover {
    border-color: #64748B;
    background: #F8FAFC;
  }
  .delib-choice-btn.active[data-choice="admis_office"] {
    background: #15803D;
    border-color: #15803D;
    color: #FFFFFF;
    box-shadow: 0 1px 3px rgba(21, 128, 61, 0.3);
  }
  .delib-choice-btn.active[data-choice="admis_grace"] {
    background: #D97706;
    border-color: #B45309;
    color: #FFFFFF;
    box-shadow: 0 1px 3px rgba(217, 119, 6, 0.3);
  }
  .delib-choice-btn.active[data-choice="redouble"] {
    background: #DC2626;
    border-color: #B91C1C;
    color: #FFFFFF;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
  }
  .delib-choice-btn.active[data-choice="exclu"] {
    background: #0F172A;
    border-color: #0F172A;
    color: #FFFFFF;
  }
  .delib-choice-btn.active[data-choice="en_attente"] {
    background: #475569;
    border-color: #475569;
    color: #FFFFFF;
  }
  tr.delib-row-grace-zone {
    background-color: #FFFBEB !important;
  }
  tr.delib-row-grace-zone td:first-child {
    border-left: 4px solid #F59E0B !important;
  }
  .delib-obs-input {
    width: 100%;
    min-width: 170px;
    padding: 6px 9px;
    border: 1px solid #CBD5E1;
    border-radius: 7px;
    font-size: 12px;
    color: #0F172A;
    background: #FFFFFF;
    transition: border-color 0.15s ease;
  }
  .delib-obs-input:focus {
    outline: none;
    border-color: #1E3A5F;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.12);
  }
  .delib-sticky-bar {
    position: sticky;
    bottom: 12px;
    z-index: 40;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 12px;
    padding: 14px 20px;
    margin-top: 18px;
    box-shadow: 0 12px 30px -8px rgba(15, 23, 42, 0.18);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <h1 style="font-size: 21px; font-weight: 800; color: #0F172A; margin: 0; display: inline-flex; align-items: center; gap: 9px;">
              <i data-lucide="gavel" style="width: 22px; height: 22px; color: #1E3A5F;"></i>
              Conseil de Classe &amp; Délibération Annuelle
            </h1>
            <?php if (!empty($classeInfo)): ?>
              <?php if ($isFrozen): ?>
                <span style="font-size: 11.5px; font-weight: 800; padding: 4px 12px; border-radius: 999px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC;">
                  Délibération Validée &amp; Figée
                </span>
              <?php elseif ($isDraft): ?>
                <span style="font-size: 11.5px; font-weight: 800; padding: 4px 12px; border-radius: 999px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                  Séance en cours (Brouillon)
                </span>
              <?php else: ?>
                <span style="font-size: 11.5px; font-weight: 800; padding: 4px 12px; border-radius: 999px; background: #E2E8F0; color: #334155;">
                  Non délibérée
                </span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Examinez chaque étudiant individuellement (moyennes annuelles &amp; assiduité) pour prononcer la décision officielle du conseil : admis d'office, grâce, redoublement, exclusion ou mise en attente.
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <?php if (!empty($selectedClasseCode)): ?>
            <a href="<?= url('bulletin/pvClasse') ?>?classe_code=<?= urlencode($selectedClasseCode) ?><?= !empty($selectedAnneeCode) ? ('&annee_code=' . urlencode($selectedAnneeCode)) : '' ?>" class="btn btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 7px; font-weight: 700; border-radius: 8px; padding: 9px 14px;">
              <i data-lucide="file-spreadsheet" style="width: 16px; height: 16px;"></i> PV Notes Semestriel
            </a>
          <?php endif; ?>
          <?php if (!empty($encryptedClasseCode) && !empty($classeInfo)): ?>
            <a href="<?= url('deliberation/imprimerPvPdf/' . $encryptedClasseCode) ?>?annee_code=<?= urlencode($selectedAnneeCode) ?>" target="_blank" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> PV Délibération Annuelle (PDF)
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Filtres Année Académique & Classe -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <form method="GET" action="<?= url('deliberation/conseil') ?>" id="filter-delib-form">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; align-items: end;">
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Année Académique</label>
              <select name="annee_code" id="delib-filter-annee" class="form-control select2" style="width: 100%;">
                <?php foreach ($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: block;">Classe à délibérer <span style="color:#EF4444;">*</span></label>
              <select name="classe_code" id="delib-filter-classe" class="form-control select2" required style="width: 100%;">
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
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; width: 100%; padding: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 7px;">
                <i data-lucide="folder-open" style="width: 16px; height: 16px;"></i> Ouvrir la Séance
              </button>
            </div>
          </div>
        </form>
      </div>

      <?php if (empty($classeInfo)): ?>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 48px 24px; border: 1px solid #E2E8F0; text-align: center;">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: #EFF6FF; color: #1E3A5F; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            <i data-lucide="gavel" style="width: 28px; height: 28px;"></i>
          </div>
          <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Sélectionnez une classe</h3>
          <p style="font-size: 13px; color: #64748B; margin: 6px 0 0 0;">Choisissez une année académique et une classe ci-dessus pour ouvrir la table de délibération du conseil.</p>
        </div>
      <?php else: ?>

        <!-- Cartouche Classe + Info Clôture si validée -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: <?= $isFrozen ? '#F0FDF4' : '#F8FAFC' ?>; border: 1px solid <?= $isFrozen ? '#86EFAC' : '#E2E8F0' ?>; border-radius: 12px; padding: 14px 18px; margin-bottom: 18px;">
          <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">
            <div>
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Classe</div>
              <div style="font-size: 15px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($classeInfo['libelle_classe'] ?? '-') ?></div>
            </div>
            <div>
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Filière / Niveau</div>
              <div style="font-size: 13.5px; font-weight: 700; color: #334155;">
                <?= htmlspecialchars($classeInfo['libelle_filiere'] ?? '-') ?> &bull; <?= htmlspecialchars($classeInfo['libelle_niveau'] ?? '-') ?>
              </div>
            </div>
            <div>
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Année</div>
              <div style="font-size: 13.5px; font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($anneeLibelle) ?></div>
            </div>
            <div>
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Formule Annuelle</div>
              <div style="font-size: 12.5px; font-weight: 700; color: #475569;">
                <?= $estPremiereAnnee ? '1ère Année (S1 + S2 + Examen)' : '2ème Année / Supérieur (S1 + S2)' ?>
              </div>
            </div>
          </div>

          <?php if ($isFrozen && $dateDelibFmt !== ''): ?>
            <div style="font-size: 12px; font-weight: 700; color: #166534; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i>
              Figée le <?= htmlspecialchars($dateDelibFmt) ?><?= $delibAuthor !== '' ? (' par ' . htmlspecialchars($delibAuthor)) : '' ?>
            </div>
          <?php elseif ($isDraft && $dateDelibFmt !== ''): ?>
            <div style="font-size: 12px; font-weight: 700; color: #92400E; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
              Dernier brouillon le <?= htmlspecialchars($dateDelibFmt) ?><?= $delibAuthor !== '' ? (' par ' . htmlspecialchars($delibAuthor)) : '' ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Cartes KPI temps réel -->
        <div class="delib-kpi-grid">
          <div class="delib-kpi-card" style="border-top: 3px solid #1E3A5F;">
            <div class="delib-kpi-label">Effectif Classe</div>
            <div class="delib-kpi-val" id="kpi-delib-total" style="color: #1E3A5F;"><?= count($rows) ?></div>
          </div>
          <div class="delib-kpi-card" style="border-top: 3px solid #15803D;">
            <div class="delib-kpi-label">Admis d'office</div>
            <div class="delib-kpi-val" id="kpi-delib-admis-office" style="color: #15803D;">0</div>
          </div>
          <div class="delib-kpi-card" style="border-top: 3px solid #D97706;">
            <div class="delib-kpi-label">Admis par grâce</div>
            <div class="delib-kpi-val" id="kpi-delib-admis-grace" style="color: #B45309;">0</div>
          </div>
          <div class="delib-kpi-card" style="border-top: 3px solid #DC2626;">
            <div class="delib-kpi-label">Redoublement</div>
            <div class="delib-kpi-val" id="kpi-delib-redouble" style="color: #B91C1C;">0</div>
          </div>
          <div class="delib-kpi-card" style="border-top: 3px solid #475569;">
            <div class="delib-kpi-label">Exclus / En attente</div>
            <div class="delib-kpi-val" id="kpi-delib-autres" style="color: #334155;">0</div>
          </div>
          <div class="delib-kpi-card" style="border-top: 3px solid #0284C7; background: #F0F9FF;">
            <div class="delib-kpi-label" style="color: #0369A1;">Taux d'Admission</div>
            <div class="delib-kpi-val" id="kpi-delib-taux" style="color: #0369A1;">0.0 %</div>
          </div>
        </div>

        <!-- Barre d'outils du Conseil : Zone de Grâce réglable + Filtres rapides + Recherche -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; margin-bottom: 16px;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 12px;">
            <!-- Contrôle Zone de Grâce ajustable -->
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; background: #FFFBEB; border: 1px solid #FDE68A; padding: 8px 12px; border-radius: 10px;">
              <i data-lucide="sparkles" style="width: 16px; height: 16px; color: #B45309;"></i>
              <span style="font-size: 12px; font-weight: 800; color: #92400E;">Zone de grâce (cas limites) :</span>
              <label style="font-size: 12px; font-weight: 700; color: #78350F; margin: 0;">De</label>
              <input type="number" id="delib-grace-min" value="9.00" min="0" max="19.99" step="0.05" style="width: 72px; padding: 4px 7px; border-radius: 6px; border: 1px solid #F59E0B; font-size: 12.5px; font-weight: 800; color: #92400E; text-align: center; background: #FFFFFF;">
              <label style="font-size: 12px; font-weight: 700; color: #78350F; margin: 0;">à</label>
              <input type="number" id="delib-grace-max" value="9.99" min="0" max="20" step="0.05" style="width: 72px; padding: 4px 7px; border-radius: 6px; border: 1px solid #F59E0B; font-size: 12.5px; font-weight: 800; color: #92400E; text-align: center; background: #FFFFFF;">
              <span style="font-size: 11.5px; font-weight: 700; color: #92400E;">/ 20</span>
            </div>

            <!-- Recherche + Actions rapides -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <div style="position: relative;">
                <input type="text" id="delib-search-input" placeholder="Rechercher nom ou matricule..." style="padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; width: 230px;">
                <i data-lucide="search" style="width: 15px; height: 15px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
              </div>
              <?php if ($isEditable): ?>
                <button type="button" id="btn-apply-auto-proposals" class="btn btn-sm btn-outline-primary" style="border-color: #1E3A5F; color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 7px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                  <i data-lucide="wand-2" style="width: 15px; height: 15px;"></i> Appliquer propositions auto
                </button>
              <?php endif; ?>
            </div>
          </div>

          <!-- Filtres rapides par statut / zone de grâce -->
          <div style="display: flex; flex-wrap: wrap; gap: 7px; align-items: center;" id="delib-filter-pills">
            <button type="button" class="delib-filter-pill active" data-filter="all">
              Tous <span class="pill-count" id="pill-count-all"><?= count($rows) ?></span>
            </button>
            <button type="button" class="delib-filter-pill" data-filter="grace_zone" style="border-color: #F59E0B; color: #92400E;">
              <i data-lucide="sparkles" style="width: 13px; height: 13px;"></i> Zone de grâce <span class="pill-count" id="pill-count-grace_zone">0</span>
            </button>
            <button type="button" class="delib-filter-pill" data-filter="admis_office">
              Admis d'office <span class="pill-count" id="pill-count-admis_office">0</span>
            </button>
            <button type="button" class="delib-filter-pill" data-filter="admis_grace">
              Graciés <span class="pill-count" id="pill-count-admis_grace">0</span>
            </button>
            <button type="button" class="delib-filter-pill" data-filter="redouble">
              Redoublement <span class="pill-count" id="pill-count-redouble">0</span>
            </button>
            <button type="button" class="delib-filter-pill" data-filter="exclu">
              Exclus <span class="pill-count" id="pill-count-exclu">0</span>
            </button>
            <button type="button" class="delib-filter-pill" data-filter="en_attente">
              En attente <span class="pill-count" id="pill-count-en_attente">0</span>
            </button>
          </div>
        </div>

        <!-- Tableau individuel de délibération -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" id="table-deliberation-conseil" style="width: 100%; border-collapse: collapse; font-size: 12.5px; margin: 0;">
              <thead>
                <tr style="background: #1E3A5F; color: #FFFFFF;">
                  <th style="padding: 11px 8px; text-align: center; width: 52px; border-bottom: 1px solid #0F172A;">Rang</th>
                  <th style="padding: 11px 10px; min-width: 195px; border-bottom: 1px solid #0F172A;">Étudiant</th>
                  <th style="padding: 11px 8px; text-align: center; width: 72px; border-bottom: 1px solid #0F172A;">Moy. S1</th>
                  <th style="padding: 11px 8px; text-align: center; width: 72px; border-bottom: 1px solid #0F172A;">Moy. S2</th>
                  <?php if ($estPremiereAnnee): ?>
                    <th style="padding: 11px 8px; text-align: center; width: 74px; border-bottom: 1px solid #0F172A;">Examen</th>
                  <?php endif; ?>
                  <th style="padding: 11px 8px; text-align: center; width: 92px; border-bottom: 1px solid #0F172A; background: #0F2744;">Moy. Annuelle</th>
                  <th style="padding: 11px 8px; text-align: center; width: 90px; border-bottom: 1px solid #0F172A;">Absences</th>
                  <th style="padding: 11px 8px; text-align: center; width: 120px; border-bottom: 1px solid #0F172A;">Proposition</th>
                  <th style="padding: 11px 10px; text-align: center; min-width: 310px; border-bottom: 1px solid #0F172A;">Décision du Conseil</th>
                  <th style="padding: 11px 10px; min-width: 190px; border-bottom: 1px solid #0F172A;">Observation / Motif du Jury</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($rows)): ?>
                  <tr>
                    <td colspan="<?= $estPremiereAnnee ? 10 : 9 ?>" style="padding: 36px; text-align: center; color: #64748B;">
                      Aucun étudiant inscrit dans cette classe pour l'année sélectionnée.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $row):
                    $moyAnn = $row['moyenne_annuelle'];
                    $hasNotes = !empty($row['has_notes']) && $moyAnn !== null;
                    $choice = $row['decision_choice'] ?? 'en_attente';
                    $choiceMeta = $choiceLabels[$choice] ?? $choiceLabels['en_attente'];
                    $bulletinUrl = !empty($row['encrypted_id_inscription'])
                        ? (url('bulletin/details/' . $row['encrypted_id_inscription']) . ($lastSemestreCode !== '' ? ('?semestre=' . urlencode($lastSemestreCode)) : ''))
                        : '';
                  ?>
                    <tr class="delib-student-row"
                        data-inscription="<?= htmlspecialchars($row['code_inscription']) ?>"
                        data-etudiant="<?= htmlspecialchars(mb_strtolower($row['nom_prenom'] . ' ' . $row['matricule'])) ?>"
                        data-moy="<?= $hasNotes ? number_format((float)$moyAnn, 2, '.', '') : '' ?>"
                        data-has-notes="<?= $hasNotes ? '1' : '0' ?>"
                        data-auto-choice="<?= htmlspecialchars($row['auto_choice']) ?>"
                        data-choice="<?= htmlspecialchars($choice) ?>"
                        style="border-bottom: 1px solid #E2E8F0; transition: background-color 0.15s ease;">
                      <td style="padding: 10px 8px; text-align: center; font-weight: 800; color: #1E3A5F;">
                        <?php if ($row['rang_annuel'] !== null): ?>
                          <?= (int)$row['rang_annuel'] ?><sup><?= ((int)$row['rang_annuel'] === 1) ? 'er' : 'e' ?></sup><?= !empty($row['ex_aequo']) ? ' <small style="font-weight: 600;">ex</small>' : '' ?>
                        <?php else: ?>
                          <span style="color: #94A3B8;">-</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                          <div>
                            <div style="font-weight: 800; color: #0F172A; line-height: 1.25;"><?= htmlspecialchars($row['nom_prenom']) ?></div>
                            <div style="font-size: 11px; color: #64748B; font-family: monospace; margin-top: 2px;">
                              <?= htmlspecialchars($row['matricule'] ?: '-') ?>
                              <?php if (!empty($row['mention_finale']) && $row['mention_finale'] !== 'Non évalué'): ?>
                                &bull; <span style="font-family: inherit; font-weight: 700; color: #475569;"><?= htmlspecialchars($row['mention_finale']) ?></span>
                              <?php endif; ?>
                            </div>
                          </div>
                          <?php if ($bulletinUrl !== ''): ?>
                            <a href="<?= $bulletinUrl ?>" target="_blank" title="Consulter le bulletin détaillé par matière" style="color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 6px; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              <i data-lucide="external-link" style="width: 13px; height: 13px;"></i>
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td style="padding: 10px 8px; text-align: center; font-weight: 700; color: <?= ($row['moyenne_s1'] !== null && $row['moyenne_s1'] < 10) ? '#DC2626' : '#334155' ?>;">
                        <?= $row['moyenne_s1'] !== null ? number_format((float)$row['moyenne_s1'], 2) : '-' ?>
                      </td>
                      <td style="padding: 10px 8px; text-align: center; font-weight: 700; color: <?= ($row['moyenne_s2'] !== null && $row['moyenne_s2'] < 10) ? '#DC2626' : '#334155' ?>;">
                        <?= $row['moyenne_s2'] !== null ? number_format((float)$row['moyenne_s2'], 2) : '-' ?>
                      </td>
                      <?php if ($estPremiereAnnee): ?>
                        <td style="padding: 10px 8px; text-align: center; font-weight: 700; color: <?= ($row['moyenne_examen'] !== null && $row['moyenne_examen'] < 10) ? '#DC2626' : '#334155' ?>;">
                          <?= $row['moyenne_examen'] !== null ? number_format((float)$row['moyenne_examen'], 2) : '-' ?>
                        </td>
                      <?php endif; ?>
                      <td class="delib-moy-cell" style="padding: 10px 8px; text-align: center; font-size: 13.5px; font-weight: 900;">
                        <?php if ($hasNotes): ?>
                          <span class="delib-moy-badge" style="display: inline-block; padding: 4px 10px; border-radius: 8px; background: <?= $moyAnn >= 10 ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $moyAnn >= 10 ? '#166534' : '#991B1B' ?>;">
                            <?= number_format((float)$moyAnn, 2) ?>
                          </span>
                        <?php else: ?>
                          <span style="color: #94A3B8; font-weight: 600;">-</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 8px; text-align: center;">
                        <?php if ((float)$row['absences_total'] > 0): ?>
                          <div style="font-weight: 800; color: <?= (float)$row['absences_injustifiees'] > 0 ? '#B91C1C' : '#334155' ?>;">
                            <?= rtrim(rtrim(number_format((float)$row['absences_total'], 2, '.', ''), '0'), '.') ?> h
                          </div>
                          <?php if ((float)$row['absences_injustifiees'] > 0): ?>
                            <div style="font-size: 10px; font-weight: 700; color: #DC2626;">
                              dont <?= rtrim(rtrim(number_format((float)$row['absences_injustifiees'], 2, '.', ''), '0'), '.') ?> h inj.
                            </div>
                          <?php endif; ?>
                        <?php else: ?>
                          <span style="color: #16A34A; font-weight: 700; font-size: 11.5px;">0 h</span>
                        <?php endif; ?>
                      </td>
                      <td class="delib-auto-cell" style="padding: 10px 8px; text-align: center;">
                        <?php if (!$hasNotes): ?>
                          <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; background: #F1F5F9; color: #64748B; font-size: 11px; font-weight: 700;">Sans notes</span>
                        <?php elseif ($moyAnn >= 10): ?>
                          <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-size: 11px; font-weight: 700;">Admis d'office</span>
                        <?php else: ?>
                          <span class="delib-auto-badge" style="display: inline-block; padding: 3px 8px; border-radius: 6px; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; font-size: 11px; font-weight: 700;">Recalé</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 8px; text-align: center;">
                        <?php if ($isEditable): ?>
                          <div class="delib-choice-group" role="group" aria-label="Décision pour <?= htmlspecialchars($row['nom_prenom']) ?>">
                            <button type="button" class="delib-choice-btn <?= $choice === 'admis_office' ? 'active' : '' ?>" data-choice="admis_office" title="Admis(e) d'office">Admis</button>
                            <button type="button" class="delib-choice-btn <?= $choice === 'admis_grace' ? 'active' : '' ?>" data-choice="admis_grace" title="Admis(e) par grâce (Rachat du jury)">Grâce</button>
                            <button type="button" class="delib-choice-btn <?= $choice === 'redouble' ? 'active' : '' ?>" data-choice="redouble" title="Redoublement (Recalé)">Redouble</button>
                            <button type="button" class="delib-choice-btn <?= $choice === 'exclu' ? 'active' : '' ?>" data-choice="exclu" title="Exclu(e)">Exclu</button>
                            <button type="button" class="delib-choice-btn <?= $choice === 'en_attente' ? 'active' : '' ?>" data-choice="en_attente" title="En attente">Attente</button>
                          </div>
                        <?php else: ?>
                          <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: <?= $choiceMeta['bg'] ?>; color: <?= $choiceMeta['color'] ?>; border: 1px solid <?= $choiceMeta['border'] ?>; font-size: 11.5px; font-weight: 800;">
                            <?= htmlspecialchars($row['libelle_decision'] ?: $choiceMeta['label']) ?>
                          </span>
                          <?php if ($choice === 'admis_grace'): ?>
                            <div style="margin-top: 3px;">
                              <span style="font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 4px; background: #FEF3C7; color: #92400E;">Rachat jury</span>
                            </div>
                          <?php endif; ?>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 8px;">
                        <?php if ($isEditable): ?>
                          <input type="text"
                                 class="delib-obs-input"
                                 maxlength="255"
                                 value="<?= htmlspecialchars($row['observation_jury']) ?>"
                                 placeholder="Motif grâce, remarque conseil...">
                        <?php else: ?>
                          <span style="color: <?= $row['observation_jury'] !== '' ? '#334155' : '#94A3B8' ?>; font-size: 12px;">
                            <?= $row['observation_jury'] !== '' ? htmlspecialchars($row['observation_jury']) : '-' ?>
                          </span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div id="delib-empty-filter-msg" style="display: none; padding: 28px; text-align: center; color: #64748B; font-size: 13px;">
            Aucun étudiant ne correspond au filtre ou à la recherche en cours.
          </div>
        </div>

        <!-- Barre d'action inférieure (Brouillon / Clôture / Réouverture) -->
        <?php if (!empty($rows)): ?>
          <div class="delib-sticky-bar">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <?php if ($isEditable): ?>
                <span id="delib-dirty-badge" style="display: none; font-size: 12px; font-weight: 800; color: #B45309; background: #FEF3C7; border: 1px solid #FCD34D; padding: 5px 10px; border-radius: 8px;">
                  Modifications non enregistrées
                </span>
                <span style="font-size: 12.5px; color: #475569;">
                  Enregistrez en <strong>brouillon</strong> pour continuer plus tard, ou <strong>clôturez</strong> pour figer officiellement les décisions.
                </span>
              <?php elseif ($isFrozen): ?>
                <span style="font-size: 13px; font-weight: 700; color: #166534; display: inline-flex; align-items: center; gap: 7px;">
                  <i data-lucide="lock" style="width: 16px; height: 16px;"></i>
                  Délibération clôturée : les décisions sont figées et actives dans tout le système (réinscriptions, fiches, bulletins S2).
                </span>
              <?php elseif (!$canWriteYear): ?>
                <span style="font-size: 12.5px; font-weight: 700; color: #64748B; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="lock" style="width: 15px; height: 15px;"></i> Année académique clôturée (consultation uniquement).
                </span>
              <?php else: ?>
                <span style="font-size: 12.5px; color: #64748B;">Consultation en lecture seule (permission <code>MANAGE_DELIBERATIONS</code> requise pour statuer).</span>
              <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <?php if ($isEditable): ?>
                <button type="button" id="btn-save-draft-delib" class="btn btn-outline-secondary" style="border-radius: 8px; padding: 9px 16px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; cursor: pointer;">
                  <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer Brouillon
                </button>
                <button type="button" id="btn-validate-delib" class="btn btn-success" style="background: #15803D; border-color: #15803D; color: #FFFFFF; border-radius: 8px; padding: 10px 18px; font-weight: 800; display: inline-flex; align-items: center; gap: 7px; cursor: pointer;">
                  <i data-lucide="shield-check" style="width: 17px; height: 17px;"></i> Clôturer &amp; Valider la Délibération
                </button>
              <?php elseif ($isFrozen && $canManageDeliberations && $canWriteYear): ?>
                <button type="button" id="btn-reopen-delib" class="btn btn-outline-warning" style="border-color: #D97706; color: #B45309; background: #FFFFFF; border-radius: 8px; padding: 9px 16px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; cursor: pointer;">
                  <i data-lucide="unlock" style="width: 16px; height: 16px;"></i> Rouvrir la Séance (Provisoire)
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </main>
</div>

<script type="application/json" id="deliberation-conseil-config">
<?= json_encode([
  'racine' => RACINE,
  'csrfToken' => Validator::generateCsrfToken(),
  'classeCode' => $selectedClasseCode,
  'classeLibelle' => $classeInfo['libelle_classe'] ?? $selectedClasseCode,
  'anneeCode' => $selectedAnneeCode,
  'anneeLibelle' => $anneeLibelle,
  'isEditable' => $isEditable,
  'enregistrerUrl' => url('deliberation/enregistrer'),
  'reouvrirUrl' => url('deliberation/reouvrir')
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<?php
ViewContext::setScripts(['public/assets/js/modules/notes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
