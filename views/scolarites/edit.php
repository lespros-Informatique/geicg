<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$currentAnneeCode = $item['annee_code'] ?? Session::get('annee_active_code', '');
$cycles = $cycles ?? [];
$filieres = $filieres ?? [];
$niveaux = $niveaux ?? [];
$filiereCyclesMap = $filiereCyclesMap ?? [];

$cycleToNiveaux = [];
$filiereToCycles = [];

foreach ($filiereCyclesMap as $fc) {
    $cCode = $fc['cycle_code'] ?? '';
    $fCode = $fc['filiere_code'] ?? '';
    $nCode = $fc['niveau_code'] ?? '';

    if (!empty($cCode) && !empty($nCode)) {
        if (!isset($cycleToNiveaux[$cCode])) {
            $cycleToNiveaux[$cCode] = [];
        }
        if (!in_array($nCode, $cycleToNiveaux[$cCode], true)) {
            $cycleToNiveaux[$cCode][] = $nCode;
        }
    }

    if (!empty($fCode) && !empty($cCode)) {
        $filiereToCycles[$fCode][] = $cCode;
    }
}
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_scolarite']) ? 'Éditer ' : 'Ajouter ' ?> Tarif de Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des grilles tarifaires par année, filière et niveau d'études</p>
        </div>
        <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>scolarite/<?= !empty($item['id_scolarite']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_scolarite'])): ?>
            <input type="hidden" name="id_scolarite" value="<?= $item['id_scolarite'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="calculator" style="width: 18px; height: 18px;"></i> Paramètres du Tarif de Scolarité
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Année Académique (Sélection Readonly / Verrouillée sur l'année active) -->
            <?php 
              $currentAnneeCode = $item['annee_code'] ?? ($_SESSION['annee_active_code'] ?? '');
              $currentAnneeLibelle = $_SESSION['annee_active_libelle'] ?? 'Session Active';
            ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Année Académique <span style="font-size: 11px; font-weight: 500; color: #64748B; margin-left: 6px;">(Session Active)</span>
              </label>
              <input type="hidden" name="annee_code" value="<?= htmlspecialchars($currentAnneeCode) ?>">
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; font-weight: 800; pointer-events: none; cursor: not-allowed;" tabindex="-1" readonly>
                <?php foreach($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" data-debut="<?= htmlspecialchars($a['date_debut_annee'] ?? '') ?>" data-fin="<?= htmlspecialchars($a['date_fin_annee'] ?? '') ?>" <?= ($currentAnneeCode == $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?> <?= (($a['statut_annee'] ?? '') === 'actif') ? '(Session Active)' : '' ?>
                  </option>
                <?php endforeach; ?>
                <?php if (empty($annees)): ?>
                  <option value="<?= htmlspecialchars($currentAnneeCode) ?>" selected><?= htmlspecialchars($currentAnneeLibelle) ?></option>
                <?php endif; ?>
              </select>
            </div>

            <?php $isAddMode = empty($item['id_scolarite']); ?>

            <!-- Cycle Académique (Select2 pour filtrer les filières) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Cycle Académique <span style="color: #EF4444;">*</span> </label>
              <select class="form-control select2" id="sel_cycle_scolarite" name="cycle_code" style="width: 100%;" required>
                <option value="">-- Choisir un cycle académique --</option>
                <?php 
                  $selectedFiliereCycles = !empty($item['filiere_code']) ? ($filiereToCycles[$item['filiere_code']] ?? []) : [];
                  foreach($cycles as $c): 
                    $isCycleSelected = in_array($c['code_cycle'], $selectedFiliereCycles);
                ?>
                  <option value="<?= htmlspecialchars($c['code_cycle']) ?>" <?= $isCycleSelected ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['libelle_cycle']) ?> (<?= htmlspecialchars($c['code_cycle']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Niveau d'études (Select2 Multi-Select en création, Simple en édition) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                <?= $isAddMode ? 'Niveau(x) d\'études <span style="color: #EF4444;">*</span>' : 'Niveau d\'études <span style="color: #EF4444;">*</span>' ?>
              </label>
              <select class="form-control select2" id="sel_niveau_scolarite" name="<?= $isAddMode ? 'niveau_codes[]' : 'niveau_code' ?>" <?= $isAddMode ? 'multiple="multiple"' : 'required' ?> style="width: 100%;">
                <?php if (!$isAddMode): ?>
                  <option value="">-- Choisir un niveau --</option>
                <?php endif; ?>
                <?php foreach($niveaux as $n): ?>
                  <option value="<?= htmlspecialchars($n['code_niveau']) ?>" <?= (($item['niveau_code'] ?? '') == $n['code_niveau']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n['libelle_niveau']) ?> (<?= htmlspecialchars($n['code_niveau']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Type de Filière (Select2 pour filtrer les filières par type) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de Filière</label>
              <select class="form-control select2" id="sel_type_filiere_scolarite" name="type_filiere" style="width: 100%;">
                <option value="">-- Tous les types --</option>
                <?php 
                  $typesFilieres = STATUTS::TYPES_FILIERES ?? ['INDUSTRIELLE' => 'Filière Industrielle', 'TERTIAIRE' => 'Filière Tertiaire'];
                  $selectedTypeFiliere = '';
                  if (!empty($item['filiere_code'])) {
                      foreach ($filieres as $fItem) {
                          if ($fItem['code_filiere'] === $item['filiere_code']) {
                              $selectedTypeFiliere = $fItem['type_filiere'] ?? '';
                              break;
                          }
                      }
                  }
                  foreach($typesFilieres as $codeType => $libelleType): 
                ?>
                  <option value="<?= htmlspecialchars($codeType) ?>" <?= ($selectedTypeFiliere === $codeType) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($libelleType) ?> (<?= htmlspecialchars($codeType) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filière (Select2 Multi-Select en création, Simple en édition) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                <?= $isAddMode ? 'Filière(s) rattachée(s) <span style="color: #EF4444;">*</span>' : 'Filière rattachée <span style="color: #EF4444;">*</span>' ?>
              </label>
              <select class="form-control select2" id="sel_filiere_scolarite" name="<?= $isAddMode ? 'filiere_codes[]' : 'filiere_code' ?>" <?= $isAddMode ? 'multiple="multiple"' : 'required' ?> style="width: 100%;">
                <?php if (!$isAddMode): ?>
                  <option value="">-- Choisir une filière --</option>
                <?php endif; ?>
                <?php foreach($filieres as $f): 
                  $fCycles = $filiereToCycles[$f['code_filiere']] ?? [];
                  $cyclesAttr = htmlspecialchars(implode(',', $fCycles));
                ?>
                  <option value="<?= htmlspecialchars($f['code_filiere']) ?>" data-cycles="<?= $cyclesAttr ?>" <?= (($item['filiere_code'] ?? '') == $f['code_filiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['libelle_filiere']) ?> (<?= htmlspecialchars($f['code_filiere']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Régime / Statut d'affectation (Select statique) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut d'Affectation <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_affectation_scolarite" name="affectation_etat" style="width: 100%;" required>
                <option value="affecte" <?= (($item['affectation_etat'] ?? 'affecte') === 'affecte') ? 'selected' : '' ?>>Affecté (de l'État)</option>
                <option value="non_affecte" <?= (($item['affectation_etat'] ?? '') === 'non_affecte') ? 'selected' : '' ?>>Non Affecté (Privé)</option>
              </select>
            </div>

            <!-- Montant annuel -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Montant annuel (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" step="any" min="0" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="montant_scolarite" value="<?= htmlspecialchars($item['montant_scolarite'] ?? '') ?>" placeholder="Ex: 650000" required>
            </div>

          </div>

          <!-- SECTION DYNAMIQUE : ÉCHÉANCIER / TRANCHES DE SCOLARITÉ (PANIER) -->
          <input type="hidden" name="deleted_tranches_ids" id="deleted_tranches_ids" value="">

          <div style="margin-top: 32px; padding-top: 24px; border-top: 2px dashed #E2E8F0;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div>
                <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="shopping-cart" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Échéancier des Tranches de Paiement
                </h3>
                <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">
                  Ajoutez et planifiez directement les tranches / versements pour cette scolarité (optionnel).
                </p>
              </div>
              
              <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" id="btn-add-tranche" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 8px 16px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #F8FAFC; cursor: pointer;">
                  <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter une tranche
                </button>
              </div>
            </div>

            <!-- Résumé de répartition / Badge indicateur de cumul -->
            <div id="tranches-summary-bar" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 18px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
              <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center; font-size: 13px;">
                <div>Montant Scolarité : <strong id="lbl-total-scolarite" style="color: #0F172A;">0 FCFA</strong></div>
                <div>Total Tranches : <strong id="lbl-cumul-tranches" style="color: #1E3A5F;">0 FCFA</strong></div>
                <div>Reste à répartir : <strong id="lbl-reste-scolarite" style="color: #64748B;">0 FCFA</strong></div>
              </div>
              <div id="badge-repartition-status" style="font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; background: #EFF6FF; color: #1E3A5F;">
                0 tranche(s)
              </div>
            </div>

            <!-- Table dynamique Panier Tranches -->
            <div style="width: 100%; overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 10px;">
              <table class="table" id="table-panier-tranches" style="width: 100%; margin: 0; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F1F5F9; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <th style="padding: 10px 12px; width: 45px; text-align: center;">#</th>
                    <th style="padding: 10px 12px; min-width: 200px;">Libellé de la tranche *</th>
                    <th style="padding: 10px 12px; width: 180px;">Montant (FCFA) *</th>
                    <th style="padding: 10px 12px; width: 170px;">Date Limite *</th>
                    <th style="padding: 10px 12px; width: 110px; text-align: center;">Statut</th>
                    <th style="padding: 10px 12px; width: 60px; text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody id="tbody-tranches">
                  <!-- Lignes injectées dynamiquement -->
                </tbody>
              </table>
              <div id="tranches-empty-state" style="padding: 24px; text-align: center; color: #94A3B8; font-size: 13px; font-style: italic;">
                <i data-lucide="layers" style="width: 28px; height: 28px; display: block; margin: 0 auto 6px auto; opacity: 0.6;"></i>
                Aucune tranche ajoutée pour le moment. Cliquez sur <strong>"+ Ajouter une tranche"</strong> pour définir l'échéancier.
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
<?php
$scolariteEditConfig = [
    'isAddMode' => (bool)$isAddMode,
    'allNiveaux' => array_map(function($n) {
        return [
            'code_niveau' => $n['code_niveau'],
            'libelle_niveau' => $n['libelle_niveau']
        ];
    }, $niveaux),
    'allFilieres' => array_map(function($f) use ($filiereToCycles) {
        return [
            'code_filiere' => $f['code_filiere'],
            'libelle_filiere' => $f['libelle_filiere'],
            'type_filiere' => $f['type_filiere'] ?? '',
            'cycles' => $filiereToCycles[$f['code_filiere']] ?? []
        ];
    }, $filieres),
    'cycleToNiveauxMap' => $cycleToNiveaux,
    'filiereCyclesPivot' => $filiereCyclesMap,
    'preselectedFiliereCode' => $item['filiere_code'] ?? '',
    'preselectedNiveauCode' => $item['niveau_code'] ?? '',
    'existingTranches' => $tranches ?? [],
    'redirectUrl' => RACINE . 'scolarite/list'
];
?>
<script type="application/json" id="scolarite-edit-config">
<?= json_encode($scolariteEditConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>
<?php 
$pageScripts = ['public/assets/js/modules/scolarites_edit.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>
