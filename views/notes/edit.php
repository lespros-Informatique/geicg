<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = $item ?? [];

$matieres = (new ModelMatiere())->getAll();
$semestres = (new ModelSemestre())->getAll();
$inscriptions = (new ModelInscription())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A;"><?= !empty($item['id_note']) ? 'Modifier Note / Évaluation' : 'Créer un Note / Évaluation' ?></h1>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; max-width: 650px;">
        <form id="form-notes" action="<?= RACINE ?>note/<?= !empty($item['id_note']) ? 'edit' : 'add' ?>" method="POST">
          <?= Validator::csrfField() ?>
          <?php if(!empty($item['id_note'])): ?>
            <input type="hidden" name="id_note" value="<?= $item['id_note'] ?>">
          <?php endif; ?>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Élève / Inscription</label>
            <select class="form-control" name="inscription_code" required>
              <option value="">-- Sélectionner une inscription élève --</option>
              <?php foreach($inscriptions as $ins): ?>
                <option value="<?= $ins['code_inscription'] ?>" <?= (($item['inscription_code'] ?? '') == $ins['code_inscription']) ? 'selected' : '' ?>><?= htmlspecialchars($ins['code_inscription']) ?></option>
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
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Semestre</label>
            <select class="form-control" name="semestre_code" required>
              <option value="">-- Sélectionner un semestre --</option>
              <?php foreach($semestres as $sm): ?>
                <option value="<?= $sm['code_semestre'] ?>" <?= (($item['semestre_code'] ?? '') == $sm['code_semestre']) ? 'selected' : '' ?>><?= htmlspecialchars($sm['libelle_semestre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Type d'évaluation</label>
            <select class="form-control" name="type_evaluation_code" required>
              <option value="Devoir" <?= (($item['type_evaluation_code'] ?? '') === 'Devoir') ? 'selected' : '' ?>>Devoir Surveillé</option>
              <option value="Examen" <?= (($item['type_evaluation_code'] ?? '') === 'Examen') ? 'selected' : '' ?>>Examen Semestriel</option>
              <option value="TP" <?= (($item['type_evaluation_code'] ?? '') === 'TP') ? 'selected' : '' ?>>Travaux Pratiques (TP)</option>
            </select>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Note sur 20</label>
            <input type="number" step="0.01" class="form-control" name="valeur_note" value="<?= htmlspecialchars($item['valeur_note'] ?? '') ?>" required>
          </div>
          <div class="form-field" style="margin-bottom: 16px;">
            <label style="display:block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Observations</label>
            <textarea class="form-control" name="observations"  rows="3"><?= htmlspecialchars($item['observations'] ?? '') ?></textarea>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700;">Enregistrer</button>
            <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="font-weight: 600;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
