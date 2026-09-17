<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$filieres = $filieres ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="camera-off" style="width: 26px; height: 26px; color: #DC2626;"></i> Inscriptions sans Photo d'Identité
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Registre des étudiants régulièrement inscrits dont la photo d'inscription officielle n'a pas encore été renseignée</p>
        </div>
        <div>
          <a href="<?= RACINE ?>reinscription/list" class="btn btn-secondary" style="background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="clipboard-check" style="width: 18px; height: 18px;"></i> Liste Générale des Inscriptions
          </a>
        </div>
      </div>

      <!-- Card Filtres Dynamiques -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: flex-end;">
          
          <!-- Filtre Année -->
          <div>
            <label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Année Académique</label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['statut_annee']) && $a['statut_annee'] === 'actif') ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
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
          <table id="table-sans-photo" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px; width: 60px; text-align: center;">Aperçu</th>
                <th style="padding: 12px;">Matricule</th>
                <th style="padding: 12px;">Nom & Prénom(s)</th>
                <th style="padding: 12px;">Classe & Filière</th>
                <th style="padding: 12px;">Téléphone</th>
                <th style="padding: 12px;">Date Inscription</th>
                <th style="padding: 12px; text-align: right;">Action</th>
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
<!-- MODAL INTERACTIVE : TÉLÉVERSER LA PHOTO D'INSCRIPTION                     -->
<!-- ========================================================================= -->
<div id="modal-upload-photo" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="camera" style="width: 20px; height: 20px;"></i> Téléverser la Photo d'Inscription
      </h3>
      <button type="button" class="btn-close-modal-photo" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-upload-photo" enctype="multipart/form-data" style="padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_inscription" id="photo_modal_id_inscription" value="">
      <input type="hidden" name="code_inscription" id="photo_modal_code_inscription" value="">

      <!-- Info Etudiant -->
      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 20px; text-align: center;">
        <div id="photo_modal_student_name" style="font-weight: 800; font-size: 15px; color: #0F172A;">Nom Étudiant</div>
        <div id="photo_modal_student_info" style="font-size: 12.5px; color: #64748B; margin-top: 2px;">Matricule • Classe</div>
      </div>

      <!-- Zone de Sélection Fichier Photo -->
      <div class="form-group" style="margin-bottom: 20px; text-align: center;">
        <div id="preview-container" style="margin-bottom: 14px;">
          <div id="preview-placeholder" style="width: 110px; height: 110px; border-radius: 50%; background: #F1F5F9; border: 2px dashed #CBD5E1; display: inline-flex; align-items: center; justify-content: center; color: #94A3B8;">
            <i data-lucide="user" style="width: 48px; height: 48px;"></i>
          </div>
          <img id="preview-img" src="" alt="Aperçu photo" style="display: none; width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 3px solid #1E3A5F; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin: 0 auto;">
        </div>

        <label for="input_photo_file" style="display: inline-block; background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 10px 20px; cursor: pointer;">
          <i data-lucide="upload" style="width: 16px; height: 16px; display: inline; margin-right: 6px;"></i> Choisir une photo d'identité...
        </label>
        <input type="file" name="photo_inscription" id="input_photo_file" accept="image/png, image/jpeg, image/jpg, image/webp" required style="display: none;">
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

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  function showNotify(msg, type) {
    type = type || 'info';
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else if (window.Swal) {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
      });
      Toast.fire({
        icon: type,
        title: msg
      });
    } else {
      alert(msg);
    }
  }

  var table = $('#table-sans-photo').DataTable({
    ajax: {
      url: '<?= RACINE ?>inscription/apiSansPhoto',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'photo_inscription', width: '60px', className: 'text-center', render: function(d) {
        if (d && d.trim() !== '') {
          return '<img src="<?= RACINE ?>' + d + '" style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid #10B981;">';
        }
        return '<div style="width:38px; height:38px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:inline-flex; align-items:center; justify-content:center;" title="Photo manquante"><i data-lucide="camera-off" style="width:18px;height:18px;"></i></div>';
      }},
      { data: 'matricule_etudiant', render: function(d) {
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_complet', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'libelle_classe', render: function(d, t, r) {
        var fil = r.libelle_filiere ? ' <span style="font-size:11.5px; color:#64748B;">(' + r.libelle_filiere + ')</span>' : '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Classe non définie') + '</span>' + fil;
      }},
      { data: 'telephone_etudiant', render: function(d) {
        return '<span style="color:#475569; font-weight:600; font-size:13px;">' + (d || '-') + '</span>';
      }},
      { data: 'created_at_inscription', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dp = parts[0].split('-');
        if (dp.length === 3) return '<span style="color:#64748B; font-weight:600; font-size:12.5px;">' + dp[2] + '/' + dp[1] + '/' + dp[0] + '</span>';
        return d;
      }},
      { data: null, orderable: false, render: function(d) {
        var safeNom = $('<div>').text(d.nom_complet || '').html();
        var safeInfo = $('<div>').text((d.matricule_etudiant || '-') + ' • ' + (d.libelle_classe || '-')).html();
        return '<button type="button" class="btn btn-sm btn-primary btn-upload-photo" ' +
               'data-id="' + d.id_inscription + '" ' +
               'data-code="' + d.code_inscription + '" ' +
               'data-nom="' + safeNom + '" ' +
               'data-info="' + safeInfo + '" ' +
               'style="font-weight:700; border-radius:6px; background:#1E3A5F; border-color:#1E3A5F; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
               '<i data-lucide="camera" style="width:14px;height:14px;"></i> Ajouter Photo</button>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee, #filter-filiere, #filter-classe').on('change', function() {
    table.ajax.reload();
  });

  // Modale Ajouter Photo
  $(document).on('click', '.btn-upload-photo', function() {
    var id = $(this).data('id');
    var code = $(this).data('code');
    var nom = $(this).data('nom');
    var info = $(this).data('info');

    $('#photo_modal_id_inscription').val(id);
    $('#photo_modal_code_inscription').val(code);
    $('#photo_modal_student_name').text(nom);
    $('#photo_modal_student_info').text(info);

    $('#input_photo_file').val('');
    $('#preview-img').hide().attr('src', '');
    $('#preview-placeholder').show();

    $('#modal-upload-photo').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Preview Image lors de la sélection
  $('#input_photo_file').on('change', function() {
    var file = this.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#preview-placeholder').hide();
        $('#preview-img').attr('src', e.target.result).show();
      };
      reader.readAsDataURL(file);
    }
  });

  // Fermer Modale Photo
  $('.btn-close-modal-photo').on('click', function() {
    $('#modal-upload-photo').css('display', 'none');
  });
  $('#modal-upload-photo').on('click', function(e) {
    if ($(e.target).is('#modal-upload-photo')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission Formulaire Upload
  $('#form-upload-photo').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var $btn = $('#btn-save-photo');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Téléversement...');

    $.ajax({
      url: '<?= RACINE ?>inscription/uploadPhoto',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Photo téléversée avec succès !', 'success');
          $('#modal-upload-photo').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          showNotify(res.message || 'Erreur lors du téléversement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur serveur ou réseau';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        showNotify(msg, 'error');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
