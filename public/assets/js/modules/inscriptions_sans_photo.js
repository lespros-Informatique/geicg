/**
 * Module JS: Inscriptions sans Photo d'Identité
 * Externalisation stricte du code JS pour views/inscriptions/sans_photo.php
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

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

  function getStudentInitials(fullName) {
    if (!fullName || typeof fullName !== 'string') return '?';
    var parts = fullName.trim().split(/\s+/).filter(function(p) { return p.length > 0; });
    if (parts.length === 0) return '?';
    var inits = parts[0][0];
    if (parts.length > 1) {
      inits += parts[1][0];
    }
    return inits.toUpperCase();
  }

  function renderFramedAvatar(photoPath, fullName, size) {
    size = size || 38;
    var inits = getStudentInitials(fullName);
    var fontSize = Math.round(size * 0.42);
    var escapedName = $('<div>').text(fullName || '').html();
    var avatarBadgeHtml = '<div style="width:' + size + 'px; height:' + size + 'px; border-radius:50%; background:linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color:#FFFFFF; font-weight:800; font-size:' + fontSize + 'px; border:2px solid #3B82F6; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.12); text-transform:uppercase; letter-spacing:0.5px; flex-shrink:0;" title="' + escapedName + '">' + inits + '</div>';

    if (photoPath && photoPath.trim() !== '') {
      var cleanRel = photoPath.trim().replace(/^\/+/, '');
      var fullUrl = racine + cleanRel;
      var escapedFallback = avatarBadgeHtml.replace(/'/g, "\\'");
      return '<img src="' + fullUrl + '" style="width:' + size + 'px; height:' + size + 'px; border-radius:50%; object-fit:cover; border:2px solid #10B981; box-shadow:0 2px 4px rgba(0,0,0,0.1);" title="Photo de ' + escapedName + '" onerror="this.onerror=null; this.outerHTML=\'' + escapedFallback + '\';">';
    }
    return avatarBadgeHtml;
  }

  var table = $('#table-sans-photo').DataTable({
    ajax: {
      url: racine + 'inscription/apiSansPhoto',
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
      { data: 'photo_inscription', width: '60px', className: 'text-center', render: function(d, type, row) {
        return renderFramedAvatar(d, row.nom_complet, 38);
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
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee, #filter-filiere, #filter-classe').on('change', function() {
    table.ajax.reload();
  });

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

  $('.btn-close-modal-photo').on('click', function() {
    $('#modal-upload-photo').css('display', 'none');
  });
  $('#modal-upload-photo').on('click', function(e) {
    if ($(e.target).is('#modal-upload-photo')) {
      $(this).css('display', 'none');
    }
  });

  $('#form-upload-photo').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var $btn = $('#btn-save-photo');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Téléversement...');

    $.ajax({
      url: racine + 'inscription/uploadPhoto',
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
