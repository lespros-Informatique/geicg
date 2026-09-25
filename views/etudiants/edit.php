<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 
$encryptedId = $encryptedId ?? (!empty($item['id_etudiant']) ? (new Validator())->crypter($item['id_etudiant']) : '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user-cog" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
            <?= !empty($item['id_etudiant']) ? 'Modifier la Fiche Étudiant' : 'Ajouter un Étudiant' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Mise à jour des informations d'état civil et coordonnées de l'étudiant
          </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <?php if (!empty($encryptedId)): ?>
            <a href="<?= RACINE ?>etudiant/details/<?= $encryptedId ?>" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; border: 1.5px solid #BFDBFE; color: #1E3A5F; background: #EFF6FF;">
              <i data-lucide="eye" style="width: 18px; height: 18px;"></i> Voir le dossier
            </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
        </div>
      </div>

      <!-- Formulaire d'édition -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form id="form-edit-etudiant" action="<?= RACINE ?>etudiant/<?= !empty($item['id_etudiant']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_etudiant'])): ?>
            <input type="hidden" name="id_etudiant" value="<?= $item['id_etudiant'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Matricule École (Lecture seule) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Matricule Établissement
              </label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; font-family: monospace; font-weight: 700; cursor: not-allowed;" name="matricule_etudiant" value="<?= htmlspecialchars($item['matricule_etudiant'] ?? '') ?>" readonly>
            </div>

            <!-- Matricule MENET-FP -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule MENET-FP</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-family: monospace; font-weight: 600;" name="matricule_menet" value="<?= htmlspecialchars($item['matricule_menet'] ?? '') ?>" placeholder="Ex: 18094523A (Éducation Nationale)">
            </div>

            <!-- Matricule MESRS -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule MESRS</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-family: monospace; font-weight: 600;" name="matricule_mesrs" value="<?= htmlspecialchars($item['matricule_mesrs'] ?? '') ?>" placeholder="Ex: MESRS-2026-00412 (Enseignement Supérieur)">
            </div>

            <!-- Nom de famille -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de famille <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="nom_etudiant" value="<?= htmlspecialchars($item['nom_etudiant'] ?? '') ?>" placeholder="Ex: KOUASSI" required>
            </div>

            <!-- Prénoms -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Prénoms <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="prenom_etudiant" value="<?= htmlspecialchars($item['prenom_etudiant'] ?? '') ?>" placeholder="Ex: Jean-Marc Emmanuel" required>
            </div>

            <!-- Sexe -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Sexe <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="sexe_etudiant" required>
                <option value="M" <?= (($item['sexe_etudiant'] ?? '') === 'M') ? 'selected' : '' ?>>Masculin (M)</option>
                <option value="F" <?= (($item['sexe_etudiant'] ?? '') === 'F') ? 'selected' : '' ?>>Féminin (F)</option>
              </select>
            </div>

            <?php
            $valDateNais = '';
            $rawDate = $item['date_naissance_etudiant'] ?? $item['date_naissance'] ?? '';
            if (!empty($rawDate) && $rawDate !== '0000-00-00') {
                $ts = strtotime($rawDate);
                if ($ts !== false && $ts > 0) {
                    $valDateNais = date('Y-m-d', $ts);
                }
            }
            $valLieuNais = trim((string)($item['lieu_naissance_etudiant'] ?? $item['lieu_naissance'] ?? ''));
            $valNationalite = trim((string)($item['nationalite_etudiant'] ?? $item['nationalite'] ?? ''));
            $valResidence = trim((string)($item['lieu_residence_etudiant'] ?? $item['adresse_etudiant'] ?? ''));
            ?>

            <!-- Date de naissance -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de naissance</label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="date_naissance_etudiant" value="<?= htmlspecialchars($valDateNais) ?>">
            </div>

            <!-- Lieu de naissance -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Lieu de naissance</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="lieu_naissance_etudiant" value="<?= htmlspecialchars($valLieuNais) ?>" placeholder="Ex: Abidjan Treichville">
            </div>

            <!-- Nationalité -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nationalité</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="nationalite_etudiant" value="<?= htmlspecialchars($valNationalite) ?>" placeholder="Ex: Ivoirienne">
            </div>

            <!-- Téléphone -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="telephone_etudiant" value="<?= htmlspecialchars($item['telephone_etudiant'] ?? '') ?>" placeholder="Ex: 0708091011" required>
            </div>

            <!-- Email -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Email</label>
              <input type="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="email_etudiant" value="<?= htmlspecialchars($item['email_etudiant'] ?? '') ?>" placeholder="Ex: jean.kouassi@etudiant.geicg.ci">
            </div>

            <!-- N° CNI / Pièce d'identité -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">N° CNI / Pièce d'identité</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="numero_cni" value="<?= htmlspecialchars($item['numero_cni'] ?? '') ?>" placeholder="Ex: C0123456789">
            </div>

            <!-- Établissement d'origine -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Établissement d'origine</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="etablissement_origine" value="<?= htmlspecialchars($item['etablissement_origine'] ?? '') ?>" placeholder="Ex: Lycée Moderne de Cocody">
            </div>

            <!-- Statut de l'étudiant -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="statut_etudiant">
                <option value="actif" <?= (($item['statut_etudiant'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= (($item['statut_etudiant'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif</option>
              </select>
            </div>

            <!-- Adresse / Lieu de résidence -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse de résidence</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none;" name="lieu_residence_etudiant" placeholder="Ex: Cocody Riviera 3, Villa 142" rows="3"><?= htmlspecialchars($valResidence) ?></textarea>
            </div>

          </div>

          <!-- Boutons d'action du formulaire -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" id="btn-submit-etudiant" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="check" style="width: 18px; height: 18px;"></i>
              <span>Enregistrer les modifications</span>
            </button>
            <a href="<?= !empty($encryptedId) ? (RACINE . 'etudiant/details/' . $encryptedId) : (RACINE . 'etudiant/list') ?>" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 6px;">
              Annuler
            </a>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  $('#form-edit-etudiant').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-etudiant');
    var originalHtml = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin" style="width:18px;height:18px;display:inline-block;animation:spin 1s linear infinite;"></i> <span>Enregistrement en cours...</span>');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Fiche étudiant modifiée avec succès !', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Fiche étudiant modifiée avec succès !');
          }
          var targetUrl = res.redirect || '<?= !empty($encryptedId) ? (RACINE . "etudiant/details/" . $encryptedId) : (RACINE . "etudiant/list") ?>';
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de la modification', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de la modification');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });
});
</script>
<style>
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.spin {
  animation: spin 1s linear infinite;
}
</style>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
