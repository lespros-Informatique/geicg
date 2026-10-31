<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$rawFilieres = $filieres ?? (new ModelFiliere())->getFilieresAssocieesAuxCycles();
$filieres = array_filter($rawFilieres, function($f) {
    return (($f['statut_filiere'] ?? 'actif') === 'actif');
});
$niveaux = $niveaux ?? (new ModelNiveau())->getActifs();
$currentAnneeCode = $selectedAnneeCode ?? (Session::get('annee_active_code', ''));
$canEditClasse = (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::MANAGE_CLASSES])));
$canManageClasses = (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::MANAGE_CLASSES])));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Classes</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre des classes d'étudiants</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
          <?php if (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::PRINT_CLASSES, PERMISSIONS::VIEW_CLASSES, PERMISSIONS::PRINT_OFFRE_ACADEMIQUE]))): ?>
            <a href="<?= RACINE ?>classe/imprimerPdf" target="_blank" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer le Répertoire (PDF)
            </a>
          <?php endif; ?>
          <?php if ($canManageClasses): ?>
            <button type="button" class="btn btn-secondary btn-reconduire-classes" style="background: #0D9488; border-color: #0D9488; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;" title="Reconduire toutes les classes d'une année vers une autre">
              <i data-lucide="copy-check" style="width: 18px; height: 18px;"></i> Reconduire les Classes (N-1 &rarr; N)
            </button>
            <button type="button" class="btn btn-primary btn-add-classe" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; border: none; color: #FFFFFF;">
              <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe
            </button>
          <?php endif; ?>
        </div>
      </div>
 
      <?php if (empty($annees)): ?>
        <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
          <div style="display: flex; align-items: center; gap: 10px; color: #991B1B;">
            <i data-lucide="alert-triangle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
            <span style="font-weight: 700; font-size: 13.5px;">Aucune année académique n'est configurée. Pour créer une classe, veuillez d'abord ajouter une année académique active.</span>
          </div>
          <a href="<?= RACINE ?>annee/list" class="btn btn-sm" style="background: #DC2626; color: #FFFFFF; font-weight: 700; border-radius: 6px; padding: 6px 14px; text-decoration: none;">
            Ajouter une Année
          </a>
        </div>
      <?php else: ?>
        <!-- BANDEAU FILTRE PAR ANNÉE ACADÉMIQUE -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 14px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
              <label for="filter-annee-classe" style="font-size: 13px; font-weight: 700; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Année Académique :
              </label>
              <select id="filter-annee-classe" class="form-control" style="width: 220px; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px;">
                <option value="">-- Toutes les années --</option>
                <?php foreach (($annees ?? []) as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($currentAnneeCode ?? '') === $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?><?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if (!empty($currentAnneeCode)): ?>
              <span class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                Année active : <?= htmlspecialchars(Session::get('annee_active_libelle', 'Active')) ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-classes" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Libellé de la Classe</th>
                <th style="padding: 12px;">Filière</th>
                <th style="padding: 12px;">Niveau</th>
                <th style="padding: 12px; text-align: center;">Capacité</th>
                <th style="padding: 12px;" class="text-center">Statut</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER UNE CLASSE                        -->
<!-- ========================================================================= -->
<div id="modal-classe" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 700px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-classe-title" style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe
      </h3>
      <button type="button" class="btn-close-modal-classe" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-classe" style="padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_classe" id="classe_id" value="">
      <input type="hidden" name="cycle_code" id="classe_cycle_code" value="">

      <!-- LIGNE 1 : ANNÉE ACADÉMIQUE ET CAPACITÉ D'ACCUEIL (2 COLONNES) -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Année Académique <span style="color: #EF4444;">*</span>
          </label>
          <select name="annee_code" id="classe_annee" required class="form-control select2" style="width: 100%;">
            <?php if (empty($annees)): ?>
              <option value="">-- Aucune année disponible --</option>
            <?php else: ?>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($currentAnneeCode === $a['code_annee']) || (($a['statut_annee'] ?? '') === STATUTS::ACTIF && empty($currentAnneeCode))) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?><?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <?php if (empty($annees)): ?>
            <small style="color: #EF4444; font-size: 12px; margin-top: 4px; display: block; font-weight: 600;">
              <i data-lucide="alert-circle" style="width: 13px; height: 13px; vertical-align: middle;"></i>
              Veuillez d'abord configurer une année académique dans Configuration &gt; Années Académiques.
            </small>
          <?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Capacité d'accueil</label>
          <input type="number" min="1" max="500" name="capacite_max_classe" id="classe_capacite" value="35" class="form-control" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px;">
        </div>
      </div>

      <!-- LIGNE 2 : PARCOURS PIVOT SUR UNE LIGNE PLEINE LARGEUR -->
      <?php if (!empty($parcoursPivots)): ?>
      <div class="form-group" style="margin-bottom: 16px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
        <label style="display: block; font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 4px;">
          <i data-lucide="layers" style="width: 14px; height: 14px; vertical-align: middle;"></i> Parcours Pivot (Cycle - Filière - Niveau) <span style="color: #EF4444;">*</span>
        </label>
        <select id="sel_parcours_pivot" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600;">
          <option value="">-- Sélectionner un parcours pivot --</option>
          <?php foreach ($parcoursPivots as $p): 
            $cSlug = !empty($p['slug_cycle']) ? $p['slug_cycle'] : $p['libelle_cycle'];
            $fSlug = !empty($p['slug_filiere']) ? $p['slug_filiere'] : $p['libelle_filiere'];
            $nSlug = !empty($p['slug_niveau']) ? $p['slug_niveau'] : ($p['libelle_niveau'] ?? '');
          ?>
            <option value="<?= htmlspecialchars($p['code_filiere_cycle']) ?>" data-filiere="<?= htmlspecialchars($p['filiere_code']) ?>" data-niveau="<?= htmlspecialchars($p['niveau_code'] ?? '') ?>" data-cycle="<?= htmlspecialchars($p['libelle_cycle'] ?? '') ?>" data-cycle-code="<?= htmlspecialchars($p['cycle_code'] ?? '') ?>" data-cycle-slug="<?= htmlspecialchars($cSlug) ?>" data-filiere-slug="<?= htmlspecialchars($fSlug) ?>" data-niveau-slug="<?= htmlspecialchars($nSlug) ?>">
              <?= htmlspecialchars($cSlug) ?> &rarr; <?= htmlspecialchars($fSlug) ?><?= !empty($nSlug) ? ' (' . htmlspecialchars($nSlug) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <!-- LIGNE 3 : CYCLE D'ÉTUDES ET NIVEAU D'ÉTUDES SUR UNE LIGNE (2 COLONNES) -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Cycle d'études <span style="font-weight: 400; font-size: 11px; color: #64748B;">(Lecture seule)</span>
          </label>
          <input type="text" id="classe_cycle" readonly placeholder="-- Dérivé du pivot --" class="form-control" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1; background-color: #F8FAFC; color: #1E3A5F; font-weight: 700; font-size: 13.5px; cursor: not-allowed;">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Niveau d'études <span style="color: #EF4444;">*</span> <span style="font-weight: 400; font-size: 11px; color: #64748B;">(Lecture seule)</span>
          </label>
          <div style="pointer-events: none; opacity: 0.95;">
            <select name="niveau_code" id="classe_niveau" required class="form-control select2" style="width: 100%; background-color: #F8FAFC;">
              <option value="">-- Choisir un niveau --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>" data-nom="<?= htmlspecialchars($n['libelle_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- LIGNE 4 : FILIÈRE RATTACHÉE SUR UNE LIGNE DÉDIÉE -->
      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Filière rattachée <span style="color: #EF4444;">*</span> <span style="font-weight: 400; font-size: 11px; color: #64748B;">(Lecture seule)</span>
        </label>
        <div style="pointer-events: none; opacity: 0.95;">
          <select name="filiere_code" id="classe_filiere" required class="form-control select2" style="width: 100%; background-color: #F8FAFC;">
            <option value="">-- Choisir une filière --</option>
            <?php foreach ($filieres as $f): ?>
              <option value="<?= htmlspecialchars($f['code_filiere']) ?>" data-nom="<?= htmlspecialchars($f['libelle_filiere']) ?>">
                <?= htmlspecialchars($f['libelle_filiere']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- LIGNE 5 : LIBELLÉ DE LA CLASSE SUR UNE LIGNE DÉDIÉE -->
      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Libellé de la classe <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_classe" id="classe_libelle" required placeholder="Ex: IDA - Première année" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
      </div>

      <!-- LIGNE 6 : STATUT DE LA CLASSE (ACTIF PAR DÉFAUT) -->
      <div class="form-group" style="margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 16px;">
        <div>
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin: 0;">
            Statut de la classe
          </label>
          <span style="font-size: 11px; color: #64748B;">Activer cette classe par défaut pour les inscriptions et emplois du temps</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <label style="position: relative; display: inline-block; width: 44px; height: 22px; margin: 0; cursor: pointer;">
            <input type="checkbox" name="statut_classe" id="classe_statut" value="actif" checked style="opacity: 0; width: 0; height: 0;">
            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #15803D; transition: .3s; border-radius: 22px;" id="statut_classe_slider">
              <span style="position: absolute; content: ''; height: 16px; width: 16px; left: 22px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%;" id="statut_classe_knob"></span>
            </span>
          </label>
          <span id="statut_classe_text" style="font-weight: 700; font-size: 13px; color: #15803D; min-width: 48px;">Actif</span>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-classe" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-classe" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : RECONDUCTION DES CLASSES (N-1 -> N)                  -->
<!-- ========================================================================= -->
<div id="modal-reconduire-classes" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #0D9488; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="copy-check" style="width: 18px; height: 18px;"></i> Reconduction des Classes (N-1 &rarr; N)
      </h3>
      <button type="button" class="btn-close-modal-reconduire" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-reconduire-classes" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      
      <p style="font-size: 13px; color: #475569; margin-top: 0; margin-bottom: 16px; line-height: 1.5;">
        Cet outil va copier automatiquement toutes les classes actives de l'<strong>Année Source</strong> vers l'<strong>Année de Destination</strong> sans dupliquer les classes déjà existantes.
      </p>
      
      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Année Source (Classes à copier) <span style="color: #EF4444;">*</span></label>
        <select name="annee_source_code" id="reconduire_annee_source" required class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1;">
          <option value="">-- Sélectionner l'année source --</option>
          <?php foreach (($annees ?? []) as $a): ?>
            <option value="<?= htmlspecialchars($a['code_annee']) ?>">
              <?= htmlspecialchars($a['libelle_annee']) ?><?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 20px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Année de Destination <span style="color: #EF4444;">*</span></label>
        <select name="annee_cible_code" id="reconduire_annee_cible" required class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1;">
          <option value="">-- Sélectionner l'année de destination --</option>
          <?php foreach (($annees ?? []) as $a): ?>
            <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($currentAnneeCode ?? '') === $a['code_annee']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($a['libelle_annee']) ?><?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-reconduire" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-reconduire" class="btn btn-primary" style="background: #0D9488; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="copy-check" style="width: 16px; height: 16px;"></i> Lancer la Reconduction
        </button>
      </div>
    </form>
  </div>
</div>

<script type="application/json" id="classes-list-config">
<?= json_encode([
    'racine' => RACINE,
    'canEditClasse' => (bool)$canEditClasse,
    'csrfToken' => Validator::generateCsrfToken(),
    'currentAnneeCode' => $currentAnneeCode ?? ''
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/classes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
