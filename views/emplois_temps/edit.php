<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$classes = (new ModelClasse())->getAll();
$salles = (new ModelSalle())->getAll();
$matieres = (new ModelMatiere())->getAll();
$enseignants = (new ModelEnseignant())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_emploi']) ? 'Modifier Créneau Horaire' : 'Créer un Créneau Horaire' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-emplois_temps" action="<?= RACINE ?>emploi/<?= !empty($item['id_emploi']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_emploi'])): ?>
            <input type="hidden" name="id_emploi" value="<?= $item['id_emploi'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe</label>
            <select class="form-control" name="classe_code" required>
              <option value="">-- Sélectionner une classe --</option>
              <?php foreach($classes as $cl): ?>
                <option value="<?= $cl['code_classe'] ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Matière</label>
            <select class="form-control" name="matiere_code" required>
              <option value="">-- Sélectionner une matière --</option>
              <?php foreach($matieres as $m): ?>
                <option value="<?= $m['code_matiere'] ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>><?= htmlspecialchars($m['libelle_matiere']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Enseignant</label>
            <input type="text" class="form-control" name="enseignant_code" value="<?= htmlspecialchars($item['enseignant_code'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Salle</label>
            <select class="form-control" name="salle_code" required>
              <option value="">-- Sélectionner une salle --</option>
              <?php foreach($salles as $s): ?>
                <option value="<?= $s['code_salle'] ?>" <?= (($item['salle_code'] ?? '') == $s['code_salle']) ? 'selected' : '' ?>><?= htmlspecialchars($s['libelle_salle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Jour de cours</label>
            <select class="form-control" name="jour" required>
              <?php foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'] as $j): ?>
                <option value="<?= $j ?>" <?= (($item['jour'] ?? '') === $j) ? 'selected' : '' ?>><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Heure de début</label>
            <input type="time" class="form-control" name="heure_debut" value="<?= htmlspecialchars($item['heure_debut'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Heure de fin</label>
            <input type="time" class="form-control" name="heure_fin" value="<?= htmlspecialchars($item['heure_fin'] ?? '') ?>" required>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
