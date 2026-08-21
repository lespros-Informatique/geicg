<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$classes = (new ModelClasse())->getAll();
$etudiants = (new ModelEtudiant())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_inscription']) ? 'Modifier Bulletin de Notes' : 'Créer un Bulletin de Notes' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-bulletin" action="<?= RACINE ?>bulletin/<?= !empty($item['id_inscription']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_inscription'])): ?>
            <input type="hidden" name="id_inscription" value="<?= $item['id_inscription'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Étudiant</label>
            <select class="form-control" name="etudiant_code" required>
              <option value="">-- Sélectionner un étudiant --</option>
              <?php foreach($etudiants as $e): ?>
                <option value="<?= $e['code_etudiant'] ?>" <?= (($item['etudiant_code'] ?? '') == $e['code_etudiant']) ? 'selected' : '' ?>><?= htmlspecialchars($e['matricule_etudiant'] . ' - ' . $e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe</label>
            <select class="form-control" name="classe_code" required>
              <option value="">-- Sélectionner une classe --</option>
              <?php foreach($classes as $cl): ?>
                <option value="<?= $cl['code_classe'] ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>bulletin/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
