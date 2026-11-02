<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- En-tête Général -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="book-marked" style="color: #1E3A5F; width: 26px; height: 26px;"></i> Maquettes Pédagogiques & Coefficients
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des matières par Filière et Niveau d'études (Matières Générales & Matières Professionnelles)</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <button type="button" class="btn btn-outline-primary btn-duplicate-maquette" onclick="openModalDuplicateMaquette()" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; cursor: pointer;">
            <i data-lucide="copy" style="width: 16px; height: 16px;"></i> Reconduire d'une année à l'autre
          </button>
          <button type="button" class="btn btn-primary btn-add-maquette" onclick="openModalMaquette()" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter une Matière à la Maquette
          </button>
        </div>
      </div>

      <!-- Barre de Filtres Avancés -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Année Académique</label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($a['code_annee'] === $activeAnnee) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Filière</label>
            <select id="filter-filiere" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les filières --</option>
              <?php foreach ($filieres as $f): ?>
                <option value="<?= htmlspecialchars($f['code_filiere']) ?>">
                  <?= htmlspecialchars($f['libelle_filiere']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Catégorie</label>
            <select id="filter-categorie" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les catégories --</option>
              <option value="GENERALE">Matières Générales</option>
              <option value="PROFESSIONNELLE">Matières Professionnelles</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tableau des Matières de la Maquette -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-maquette" class="table display nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>Matière</th>
                <th>Filière Associée</th>
                <th>Niveau</th>
                <th class="text-center">Catégorie Bulletin</th>
                <th class="text-center">Coefficient</th>
                <th class="text-center">Statut</th>
                <th class="text-end" style="width: 140px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL : AJOUTER / MODIFIER MATIÈRE DANS MAQUETTE -->
<div id="modal-maquette" onclick="if(event.target === this) closeModalMaquette();" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(2px); z-index:9999; justify-content:center; align-items:center; padding:16px;">
  <div style="max-width:580px; width:92%; background:#fff; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); overflow:hidden;">
    <div style="padding:18px 24px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
      <h3 id="modal-maquette-title" style="margin:0; font-size:16px; font-weight:800; color:#1E3A5F; display:flex; align-items:center; gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Ajouter Matière à la Maquette
      </h3>
      <button type="button" class="btn-close-modal-maquette" onclick="closeModalMaquette();" style="background:none; border:none; font-size:20px; color:#64748B; cursor:pointer;">&times;</button>
    </div>
    <form id="form-maquette" style="padding:24px;">
      <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
      <input type="hidden" name="id_maquette" id="maq_id">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Année Académique *</label>
          <select name="annee_code" id="maq_annee" class="form-control select2" required style="width:100%;">
            <?php foreach ($annees as $a): ?>
              <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($a['code_annee'] === $activeAnnee) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['libelle_annee']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Niveau d'Études *</label>
          <select name="niveau_code" id="maq_niveau" class="form-control select2" required style="width:100%;">
            <option value="">-- Choisir un niveau --</option>
            <?php foreach ($niveaux as $n): ?>
              <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="margin-bottom: 14px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Filière *</label>
        <select name="filiere_code" id="maq_filiere" class="form-control select2" required style="width:100%;">
          <option value="">-- Choisir une filière --</option>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= htmlspecialchars($f['code_filiere']) ?>"><?= htmlspecialchars($f['libelle_filiere']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 14px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Matière à Enseigner *</label>
        <select name="matiere_code" id="maq_matiere" class="form-control select2" required style="width:100%;">
          <option value="">-- Choisir une matière --</option>
          <?php foreach ($matieres as $m): ?>
            <option value="<?= htmlspecialchars($m['code_matiere']) ?>"><?= htmlspecialchars($m['libelle_matiere']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Catégorie (Pour Bulletin) *</label>
          <select name="categorie_matiere" id="maq_categorie" class="form-control" required style="width:100%; border-radius:6px; height:38px;">
            <option value="GENERALE">MATIÈRES GÉNÉRALES</option>
            <option value="PROFESSIONNELLE">MATIÈRES PROFESSIONNELLES</option>
          </select>
          <small style="color:#64748B; font-size:11px;">Définit le bloc d'affichage et le sous-total dans le bulletin</small>
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Coefficient *</label>
          <input type="number" step="0.5" min="0.5" max="20" name="coefficient" id="maq_coef" class="form-control" value="1.0" required style="border-radius:6px; height:38px; font-weight:700;">
          <small style="color:#64748B; font-size:11px;">Pondération pour les moyennes</small>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #E2E8F0; padding-top:16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-maquette" onclick="closeModalMaquette();" style="font-weight:600; border-radius:6px;">Annuler</button>
        <button type="submit" id="btn-save-maquette" class="btn btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
          <i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL : RECONDUCTION / DUPLICATION MAQUETTE -->
<div id="modal-dupliquer-maquette" onclick="if(event.target === this) closeModalDuplicateMaquette();" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(2px); z-index:9999; justify-content:center; align-items:center; padding:16px;">
  <div style="max-width:520px; width:92%; background:#fff; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); overflow:hidden;">
    <div style="padding:18px 24px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
      <h3 style="margin:0; font-size:16px; font-weight:800; color:#1E3A5F; display:flex; align-items:center; gap:8px;">
        <i data-lucide="copy" style="width:18px;height:18px;"></i> Reconduire une Maquette
      </h3>
      <button type="button" class="btn-close-modal-dup" onclick="closeModalDuplicateMaquette();" style="background:none; border:none; font-size:20px; color:#64748B; cursor:pointer;">&times;</button>
    </div>
    <form id="form-duplicate-maquette" style="padding:24px;">
      <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">

      <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:12px 14px; margin-bottom:16px; font-size:12.5px; color:#1E3A5F;">
        Cette fonction permet de copier les matières, catégories et coefficients d'une année académique vers une nouvelle année académique sans ressaisie manuelle.
      </div>

      <div style="margin-bottom: 14px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Année Source (Année d'origine) *</label>
        <select name="source_annee_code" id="dup_source_annee" class="form-control select2" required style="width:100%;">
          <?php foreach ($annees as $a): ?>
            <option value="<?= htmlspecialchars($a['code_annee']) ?>"><?= htmlspecialchars($a['libelle_annee']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 14px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Année Cible (Nouvelle année) *</label>
        <select name="target_annee_code" id="dup_target_annee" class="form-control select2" required style="width:100%;">
          <?php foreach ($annees as $a): ?>
            <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($a['code_annee'] === $activeAnnee) ? 'selected' : '' ?>>
              <?= htmlspecialchars($a['libelle_annee']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 18px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:5px;">Filière Spécifique (Optionnel)</label>
        <select name="filiere_code" id="dup_filiere" class="form-control select2" style="width:100%;">
          <option value="">-- Toutes les filières --</option>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= htmlspecialchars($f['code_filiere']) ?>"><?= htmlspecialchars($f['libelle_filiere']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #E2E8F0; padding-top:16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-dup" onclick="closeModalDuplicateMaquette();" style="font-weight:600; border-radius:6px;">Annuler</button>
        <button type="submit" id="btn-save-duplicate" class="btn btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
          <i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction
        </button>
      </div>
    </form>
  </div>
</div>

<script type="application/json" id="maquette-list-config">
<?= json_encode([
    'racine' => RACINE,
    'csrfToken' => Session::getCsrfToken()
]) ?>
</script>
<script>
  window.MAQUETTE_CONFIG = {
    racine: '<?= RACINE ?>',
    csrfToken: '<?= Session::getCsrfToken() ?>'
  };

  window.openModalMaquette = function() {
    var form = document.getElementById('form-maquette');
    if (form) form.reset();
    var idInput = document.getElementById('maq_id');
    if (idInput) idInput.value = '';
    var title = document.getElementById('modal-maquette-title');
    if (title) title.innerHTML = '<i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Ajouter Matière à la Maquette';

    var fAnnee = document.getElementById('filter-annee');
    var fFiliere = document.getElementById('filter-filiere');
    var fNiveau = document.getElementById('filter-niveau');
    var mAnnee = document.getElementById('maq_annee');
    var mFiliere = document.getElementById('maq_filiere');
    var mNiveau = document.getElementById('maq_niveau');

    if (fAnnee && fAnnee.value && mAnnee) {
      mAnnee.value = fAnnee.value;
      if (window.jQuery && $(mAnnee).data('select2')) $(mAnnee).trigger('change');
    }
    if (fFiliere && fFiliere.value && mFiliere) {
      mFiliere.value = fFiliere.value;
      if (window.jQuery && $(mFiliere).data('select2')) $(mFiliere).trigger('change');
    }
    if (fNiveau && fNiveau.value && mNiveau) {
      mNiveau.value = fNiveau.value;
      if (window.jQuery && $(mNiveau).data('select2')) $(mNiveau).trigger('change');
    }

    var modal = document.getElementById('modal-maquette');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }
  };

  window.closeModalMaquette = function() {
    var modal = document.getElementById('modal-maquette');
    if (modal) modal.style.display = 'none';
  };

  window.openModalDuplicateMaquette = function() {
    var modal = document.getElementById('modal-dupliquer-maquette');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }
  };

  window.closeModalDuplicateMaquette = function() {
    var modal = document.getElementById('modal-dupliquer-maquette');
    if (modal) modal.style.display = 'none';
  };
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/maquettes.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>

