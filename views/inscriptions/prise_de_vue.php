<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="camera" style="width: 26px; height: 26px; color: #1E3A5F;"></i> Prise de vue
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Registre des étudiants régulièrement inscrits avec gestion de la prise de vue et capture des photos</p>
        </div>
      </div>

      <!-- Card Filtres Dynamiques -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
          
          <!-- Filtre Année -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Année Académique</label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['statut_annee']) && $a['statut_annee'] === STATUTS::ACTIF) ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Statut Photo -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Statut Photo</label>
            <select id="filter-photo" class="form-control" style="width: 100%; border-radius: 8px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 13.5px;">
              <option value="all">Tous les étudiants inscrits</option>
              <option value="sans_photo" selected>Photos Manquantes (Sans photo)</option>
              <option value="avec_photo">Photos renseignées (Avec photo)</option>
            </select>
          </div>

          <!-- Filtre Filière -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Filière / Spécialité</label>
            <select id="filter-filiere" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les filières --</option>
              <?php foreach ($filieres as $f): ?>
                <option value="<?= htmlspecialchars($f['code_filiere']) ?>">
                  <?= htmlspecialchars($f['libelle_filiere']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Classe -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Classe / Niveau</label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>">
                  <?= htmlspecialchars($c['libelle_classe']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>

      <!-- Tableau DataTables -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-prise-de-vue" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px; width: 60px; text-align: center;">Aperçu Photo</th>
                <th style="padding: 12px;">Matricule</th>
                <th style="padding: 12px;">Nom & Prénom(s) Étudiant</th>
                <th style="padding: 12px;">Classe & Filière</th>
                <th style="padding: 12px;">Téléphone</th>
                <th style="padding: 12px;">Date Inscription</th>
                <th style="padding: 12px; text-align: center; width: 140px;">Action Unique</th>
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
<!-- MODAL INTERACTIVE : PRISE DE VUE WEBCAM & TÉLÉVERSEMENT FOTO              -->
<!-- ========================================================================= -->
<div id="modal-upload-photo" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="camera" style="width: 20px; height: 20px;"></i> Prise de vue
      </h3>
      <button type="button" class="btn-close-modal-photo" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-upload-photo" enctype="multipart/form-data" style="padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_inscription" id="photo_modal_id_inscription" value="">
      <input type="hidden" name="code_inscription" id="photo_modal_code_inscription" value="">
      <input type="hidden" name="photo_webcam_data" id="photo_webcam_data" value="">

      <!-- Info Etudiant -->
      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 20px; text-align: center;">
        <div id="photo_modal_student_name" style="font-weight: 800; font-size: 15.5px; color: #0F172A;">Nom Étudiant</div>
        <div id="photo_modal_student_info" style="font-size: 12.5px; color: #64748B; margin-top: 2px;">Matricule • Classe</div>
      </div>

      <!-- Onglets Mode WebCam vs Fichier -->
      <div style="display: flex; gap: 8px; margin-bottom: 18px; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
        <button type="button" id="tab-mode-webcam" class="btn btn-sm" style="flex: 1; background: #1E3A5F; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 8px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
          <i data-lucide="camera" style="width: 16px; height: 16px;"></i> Prise de vue WebCam
        </button>
        <button type="button" id="tab-mode-file" class="btn btn-sm" style="flex: 1; background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 8px; padding: 8px 12px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
          <i data-lucide="upload" style="width: 16px; height: 16px;"></i> Importer un Fichier
        </button>
      </div>

      <!-- SECTION 1 : WEBCAM -->
      <div id="section-webcam" style="text-align: center; margin-bottom: 20px;">
        <div style="position: relative; width: 220px; height: 220px; margin: 0 auto 14px auto; border-radius: 12px; overflow: hidden; background: #0F172A; border: 3px solid #1E3A5F; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);">
          <video id="webcam-video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
          <canvas id="webcam-canvas" width="300" height="300" style="display: none;"></canvas>
          <img id="webcam-captured-preview" src="" alt="Capture WebCam" style="display: none; width: 100%; height: 100%; object-fit: cover;">
        </div>

        <div style="display: flex; justify-content: center; gap: 10px;">
          <button type="button" id="btn-capture-webcam" class="btn btn-sm btn-primary" style="background: #10B981; border-color: #10B981; font-weight: 700; border-radius: 8px; padding: 8px 18px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="aperture" style="width: 16px; height: 16px;"></i> Capture Instantanée
          </button>
          <button type="button" id="btn-retake-webcam" class="btn btn-sm btn-secondary" style="display: none; font-weight: 700; border-radius: 8px; padding: 8px 18px; align-items: center; gap: 6px;">
            <i data-lucide="rotate-ccw" style="width: 16px; height: 16px;"></i> Reprendre
          </button>
        </div>
      </div>

      <!-- SECTION 2 : FICHIER IMAGE -->
      <div id="section-file" style="display: none; text-align: center; margin-bottom: 20px;">
        <div id="preview-container" style="margin-bottom: 14px;">
          <div id="preview-placeholder" style="width: 120px; height: 120px; border-radius: 50%; background: #F1F5F9; border: 2px dashed #CBD5E1; display: inline-flex; align-items: center; justify-content: center; color: #94A3B8; margin: 0 auto;">
            <i data-lucide="user" style="width: 54px; height: 54px;"></i>
          </div>
          <img id="preview-file-img" src="" alt="Aperçu photo" style="display: none; width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #1E3A5F; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin: 0 auto;">
        </div>

        <label for="input_photo_file" style="display: inline-block; background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 10px 20px; cursor: pointer;">
          <i data-lucide="upload" style="width: 16px; height: 16px; display: inline; margin-right: 6px;"></i> Sélectionner une photo d'identité...
        </label>
        <input type="file" name="photo_inscription" id="input_photo_file" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">
        <div style="font-size: 11.5px; color: #94A3B8; margin-top: 6px;">Formats acceptés : PNG, JPG, JPEG, WEBP (Max 5 Mo)</div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-photo" style="font-weight: 700; border-radius: 8px; padding: 9px 20px;">Annuler</button>
        <button type="submit" id="btn-save-photo" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo
        </button>
      </div>
    </form>
  </div>
</div>

<?php 
ViewContext::setScripts(['public/assets/js/modules/inscriptions_prise_de_vue.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>
