<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$filieres = (new ModelFiliere())->getAll();
$niveaux = (new ModelNiveau())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_classe']) ? 'Modifier Classe / Promotion' : 'Créer un Classe / Promotion' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-classes" action="<?= RACINE ?>classe/<?= !empty($item['id_classe']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_classe'])): ?>
            <input type="hidden" name="id_classe" value="<?= $item['id_classe'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de la classe (ex: BTS 1 - GL A)</label>
            <input type="text" class="form-control" name="libelle_classe" value="<?= htmlspecialchars($item['libelle_classe'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Filière</label>
            <select class="form-control" name="filiere_code" required>
              <option value="">-- Sélectionner une filière --</option>
              <?php foreach($filieres as $fl): ?>
                <option value="<?= $fl['code_filiere'] ?>" <?= (($item['filiere_code'] ?? '') == $fl['code_filiere']) ? 'selected' : '' ?>><?= htmlspecialchars($fl['libelle_filiere']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau</label>
            <select class="form-control" name="niveau_code" required>
              <option value="">-- Sélectionner un niveau --</option>
              <?php foreach($niveaux as $n): ?>
                <option value="<?= $n['code_niveau'] ?>" <?= (($item['niveau_code'] ?? '') == $n['code_niveau']) ? 'selected' : '' ?>><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Capacité maximale d'élèves</label>
            <input type="number"  class="form-control" name="capacite_max_classe" value="<?= htmlspecialchars($item['capacite_max_classe'] ?? '') ?>" >
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>classe/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
