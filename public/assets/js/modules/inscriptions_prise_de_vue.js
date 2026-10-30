/**
 * Module JS: Studio Prise de Vue Photo d'Identité
 * Externalisation stricte du code JS pour views/inscriptions/prise_de_vue.php
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  var webcamStream = null;

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
    size = size || 40;
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

  var table = $('#table-prise-de-vue').DataTable({
    ajax: {
      url: racine + 'inscription/apiPriseDeVue',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filter_photo = $('#filter-photo').val();
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
      { data: 'photo_path', width: '60px', className: 'text-center', render: function(d, type, row) {
        return renderFramedAvatar(d, row.nom_complet, 40);
      }},
      { data: 'matricule_etudiant', render: function(d) {
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_complet', render: function(d, type, row) {
        var badgePhoto = row.has_photo 
          ? ' <span class="badge bg-success" style="font-size:10px; padding:2px 6px; border-radius:4px; margin-left:4px;">Photo OK</span>' 
          : ' <span class="badge bg-danger" style="font-size:10px; padding:2px 6px; border-radius:4px; margin-left:4px;">Sans photo</span>';
        return '<span style="font-weight:800; color:#0F172A;">' + (d || '-') + '</span>' + badgePhoto;
      }},
      { data: 'libelle_classe', render: function(d) {
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Classe non définie') + '</span>';
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
      { data: null, orderable: false, width: '140px', render: function(d) {
        var safeNom = $('<div>').text(d.nom_complet || '').html();
        var safeInfo = $('<div>').text((d.matricule_etudiant || '-') + ' • ' + (d.libelle_classe || '-')).html();
        var btnText = d.has_photo ? 'Reprendre Photo' : 'Prendre Photo';
        var btnColor = d.has_photo ? '#475569' : '#1E3A5F';
        return '<button type="button" class="btn btn-sm btn-primary btn-upload-photo" ' +
               'data-id="' + d.id_inscription + '" ' +
               'data-code="' + d.code_inscription + '" ' +
               'data-nom="' + safeNom + '" ' +
               'data-info="' + safeInfo + '" ' +
               'style="font-weight:700; border-radius:6px; background:' + btnColor + '; border-color:' + btnColor + '; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
               '<i data-lucide="camera" style="width:14px;height:14px;"></i> ' + btnText + '</button>';
      }, className: 'text-center' }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee, #filter-photo, #filter-filiere, #filter-classe').on('change', function() {
    table.ajax.reload();
  });

  function startWebcam() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ video: { width: 400, height: 400 } })
        .then(function(stream) {
          webcamStream = stream;
          var video = document.getElementById('webcam-video');
          video.srcObject = stream;
          video.play();
        })
        .catch(function(err) {
          console.warn("Impossible d'accéder à la webcam:", err);
          showNotify("Caméra inaccessible. Vous pouvez téléverser un fichier image.", "info");
          switchToTab('file');
        });
    } else {
      switchToTab('file');
    }
  }

  function stopWebcam() {
    if (webcamStream) {
      webcamStream.getTracks().forEach(function(track) { track.stop(); });
      webcamStream = null;
    }
  }

  function switchToTab(mode) {
    if (mode === 'webcam') {
      $('#tab-mode-webcam').css({ 'background': '#1E3A5F', 'color': '#FFFFFF' });
      $('#tab-mode-file').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#section-webcam').show();
      $('#section-file').hide();
      $('#input_photo_file').val('');
      startWebcam();
    } else {
      $('#tab-mode-file').css({ 'background': '#1E3A5F', 'color': '#FFFFFF' });
      $('#tab-mode-webcam').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#section-file').show();
      $('#section-webcam').hide();
      $('#photo_webcam_data').val('');
      stopWebcam();
    }
  }

  $('#tab-mode-webcam').on('click', function() { switchToTab('webcam'); });
  $('#tab-mode-file').on('click', function() { switchToTab('file'); });

  $(document).on('click', '.btn-upload-photo', function() {
    var id = $(this).data('id');
    var code = $(this).data('code');
    var nom = $(this).data('nom');
    var info = $(this).data('info');

    $('#photo_modal_id_inscription').val(id);
    $('#photo_modal_code_inscription').val(code);
    $('#photo_modal_student_name').text(nom);
    $('#photo_modal_student_info').text(info);

    $('#photo_webcam_data').val('');
    $('#input_photo_file').val('');
    $('#webcam-captured-preview').hide().attr('src', '');
    $('#webcam-video').show();
    $('#btn-capture-webcam').show();
    $('#btn-retake-webcam').hide();
    var inits = getStudentInitials(nom);
    $('#preview-placeholder').css({
      'width': '120px',
      'height': '120px',
      'border-radius': '50%',
      'background': 'linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%)',
      'border': '3px solid #3B82F6',
      'color': '#FFFFFF',
      'font-size': '42px',
      'font-weight': '800',
      'box-shadow': '0 4px 10px rgba(0,0,0,0.15)',
      'display': 'inline-flex',
      'align-items': 'center',
      'justify-content': 'center',
      'margin': '0 auto',
      'letter-spacing': '1px'
    }).html(inits).show();
    $('#preview-file-img').hide().attr('src', '');

    $('#modal-upload-photo').css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    switchToTab('webcam');
  });

  $('#btn-capture-webcam').on('click', function() {
    var video = document.getElementById('webcam-video');
    var canvas = document.getElementById('webcam-canvas');
    var context = canvas.getContext('2d');
    canvas.width = video.videoWidth || 300;
    canvas.height = video.videoHeight || 300;

    context.translate(canvas.width, 0);
    context.scale(-1, 1);
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    var dataUrl = canvas.toDataURL('image/png');
    $('#photo_webcam_data').val(dataUrl);

    $('#webcam-video').hide();
    $('#webcam-captured-preview').attr('src', dataUrl).show();
    $('#btn-capture-webcam').hide();
    $('#btn-retake-webcam').css('display', 'inline-flex');
  });

  $('#btn-retake-webcam').on('click', function() {
    $('#photo_webcam_data').val('');
    $('#webcam-captured-preview').hide();
    $('#webcam-video').show();
    $('#btn-retake-webcam').hide();
    $('#btn-capture-webcam').show();
  });

  $('#input_photo_file').on('change', function() {
    var file = this.files[0];
    if (file) {
      $('#photo_webcam_data').val('');
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#preview-placeholder').hide();
        $('#preview-file-img').attr('src', e.target.result).show();
      };
      reader.readAsDataURL(file);
    }
  });

  function closeModal() {
    stopWebcam();
    $('#modal-upload-photo').css('display', 'none');
  }

  $('.btn-close-modal-photo').on('click', closeModal);
  $('#modal-upload-photo').on('click', function(e) {
    if ($(e.target).is('#modal-upload-photo')) {
      closeModal();
    }
  });

  $('#form-upload-photo').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var $btn = $('#btn-save-photo');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

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
          showNotify(res.message || 'Photo d\'inscription enregistrée avec succès !', 'success');
          closeModal();
          table.ajax.reload(null, false);
        } else {
          showNotify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur serveur ou réseau';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        } else if (xhr.responseText) {
          try {
            var json = JSON.parse(xhr.responseText);
            if (json && json.message) msg = json.message;
          } catch(e) {
            if (xhr.statusText && xhr.status) msg = 'Erreur (' + xhr.status + ') : ' + xhr.statusText;
          }
        }
        showNotify(msg, 'error');
      }
    });
  });
});
