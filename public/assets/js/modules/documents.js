/**
 * Module JS Unifié : Documents Pédagogiques & Galerie Multimédia (Photos, Vidéos, Fichiers & Téléchargements)
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // --- Fonctions globales de gestion des Documents & Médias ---
var currentDocTab = 'upload';
var existingDocUrl = '';

(function() {
  var configEl = document.getElementById('documents-edit-config');
  if (configEl) {
    try {
      var cfg = JSON.parse(configEl.textContent);
      existingDocUrl = cfg.existingDocUrl || '';
    } catch (e) {}
  }
})();

window.switchDocTab = function(tab) {
  currentDocTab = tab;
  var isUpload = (tab === 'upload');
  document.getElementById('panel_upload').style.display = isUpload ? '' : 'none';
  document.getElementById('panel_url').style.display = isUpload ? 'none' : '';
  
  document.getElementById('tab_upload').style.background = isUpload ? '#1E3A5F' : '#F1F5F9';
  document.getElementById('tab_upload').style.color = isUpload ? '#FFFFFF' : '#475569';
  document.getElementById('tab_url').style.background = isUpload ? '#F1F5F9' : '#1E3A5F';
  document.getElementById('tab_url').style.color = isUpload ? '#475569' : '#FFFFFF';

  if (!isUpload) {
    var inp = document.getElementById('url_fichier_input');
    if (!inp.value && existingDocUrl) { 
      inp.value = existingDocUrl; 
    }
    document.getElementById('lien_document').value = inp.value;
  }
};

window.handleDocFile = function(file) {
  var maxMo = 50;
  if (file.size > maxMo * 1024 * 1024) {
    var msgSize = 'Le fichier sélectionné dépasse la taille maximale autorisée de ' + maxMo + ' Mo.';
    if (typeof showToast === 'function') showToast(msgSize, 'error');
    else if (window.toastr) toastr.error(msgSize);
    return;
  }

  var ext = file.name.split('.').pop().toLowerCase();
  var iconName = 'file-text';
  var typeSelectVal = 'autre';

  if (ext === 'pdf') {
    iconName = 'file-text';
    typeSelectVal = 'pdf';
  } else if (['doc', 'docx', 'odt', 'rtf', 'txt'].indexOf(ext) !== -1) {
    iconName = 'file-type-2';
    typeSelectVal = 'word';
  } else if (['xls', 'xlsx', 'ods'].indexOf(ext) !== -1) {
    iconName = 'file-spreadsheet';
    typeSelectVal = 'excel';
  } else if (['ppt', 'pptx', 'odp'].indexOf(ext) !== -1) {
    iconName = 'presentation';
    typeSelectVal = 'powerpoint';
  } else if (['zip', 'rar', '7z'].indexOf(ext) !== -1) {
    iconName = 'archive';
    typeSelectVal = 'archive';
  } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].indexOf(ext) !== -1) {
    iconName = 'image';
    typeSelectVal = 'image';
  }

  document.getElementById('doc_preview_zone').style.display = '';
  document.getElementById('doc_file_name').textContent = file.name;
  document.getElementById('doc_file_size').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' Mo — Extension .' + ext.toUpperCase();
  document.getElementById('type_document').value = typeSelectVal;
  document.getElementById('lien_document').value = '';

  var iconEl = document.getElementById('doc_type_icon');
  iconEl.setAttribute('data-lucide', iconName);
  if (window.lucide) lucide.createIcons();
};

window.clearDocFile = function() {
  document.getElementById('fichier_upload').value = '';
  document.getElementById('doc_preview_zone').style.display = 'none';
  document.getElementById('lien_document').value = existingDocUrl;
};

var currentTab = 'upload';
var existingUrl = '';

(function() {
  var configEl = document.getElementById('galeries-edit-config');
  if (configEl) {
    try {
      var cfg = JSON.parse(configEl.textContent);
      existingUrl = cfg.existingUrl || '';
    } catch (e) {}
  }
})();

window.switchTab = function(tab) {
  currentTab = tab;
  var isUpload = (tab === 'upload');
  document.getElementById('panel_upload').style.display = isUpload ? '' : 'none';
  document.getElementById('panel_url').style.display   = isUpload ? 'none' : '';
  document.getElementById('tab_upload').style.background = isUpload ? '#1E3A5F' : '#F1F5F9';
  document.getElementById('tab_upload').style.color     = isUpload ? '#fff'     : '#475569';
  document.getElementById('tab_url').style.background   = isUpload ? '#F1F5F9' : '#1E3A5F';
  document.getElementById('tab_url').style.color        = isUpload ? '#475569' : '#fff';
  if (!isUpload) {
    var inp = document.getElementById('url_fichier_input');
    if (!inp.value && existingUrl) { inp.value = existingUrl; }
    previewUrl(inp.value);
    document.getElementById('url_fichier').value = inp.value;
  }
};

window.handleFile = function(file) {
  var maxMo = 50;
  if (file.size > maxMo * 1024 * 1024) {
    var msgSize = 'Le fichier dépasse ' + maxMo + ' Mo. Veuillez choisir un fichier plus petit.';
    if (typeof showToast === 'function') showToast(msgSize, 'error');
    else if (window.toastr) toastr.error(msgSize);
    return;
  }
  var isImage = file.type.startsWith('image/');
  var isVideo = file.type.startsWith('video/');
  if (!isImage && !isVideo) {
    var msgFmt = 'Format non supporté. Choisissez une image ou une vidéo.';
    if (typeof showToast === 'function') showToast(msgFmt, 'error');
    else if (window.toastr) toastr.error(msgFmt);
    return;
  }

  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('preview_zone').style.display = '';
    if (isImage) {
      document.getElementById('preview_img').src = e.target.result;
      document.getElementById('preview_img').style.display = '';
      document.getElementById('preview_vid').style.display = 'none';
    } else {
      document.getElementById('preview_vid').src = e.target.result;
      document.getElementById('preview_vid').style.display = '';
      document.getElementById('preview_img').style.display = 'none';
    }
    document.getElementById('file_info').style.display = '';
    document.getElementById('file_info').innerHTML = '<i data-lucide="file" style="width:14px;height:14px;vertical-align:middle;display:inline-block;margin-right:4px;"></i> <strong>' + file.name + '</strong> &nbsp;(' + (file.size/1024/1024).toFixed(2) + ' Mo)';
    if (window.lucide) lucide.createIcons();
    document.getElementById('url_fichier').value = '';
  };
  reader.readAsDataURL(file);
  if (isImage) document.getElementById('type_galerie').value = 'photo';
  if (isVideo) document.getElementById('type_galerie').value = 'video';
};

window.clearFile = function() {
  document.getElementById('fichier_upload').value = '';
  document.getElementById('preview_zone').style.display = 'none';
  document.getElementById('preview_img').src = '';
  document.getElementById('preview_vid').src = '';
  document.getElementById('file_info').style.display = 'none';
  document.getElementById('url_fichier').value = existingUrl;
};

window.previewUrl = function(url) {
  var imgEl = document.getElementById('preview_url_img');
  var vidEl = document.getElementById('preview_url_video');
  var zone  = document.getElementById('preview_url_zone');
  if (!url) { zone.style.display='none'; imgEl.style.display='none'; vidEl.innerHTML=''; vidEl.style.display='none'; return; }
  var ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
  var vmMatch = url.match(/vimeo\.com\/(\d+)/);
  if (ytMatch) {
    zone.style.display=''; imgEl.style.display='none';
    vidEl.style.display=''; vidEl.innerHTML='<iframe src="https://www.youtube.com/embed/'+ytMatch[1]+'" style="width:100%;height:280px;border:none;" allowfullscreen></iframe>';
  } else if (vmMatch) {
    zone.style.display=''; imgEl.style.display='none';
    vidEl.style.display=''; vidEl.innerHTML='<iframe src="https://player.vimeo.com/video/'+vmMatch[1]+'" style="width:100%;height:280px;border:none;" allowfullscreen></iframe>';
  } else if (/\.(mp4|mov|avi|webm)(\?.*)?$/i.test(url)) {
    zone.style.display=''; imgEl.style.display='none';
    vidEl.style.display=''; vidEl.innerHTML='<video src="'+url+'" controls style="width:100%;max-height:280px;border-radius:10px;"></video>';
  } else if (/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i.test(url)) {
    zone.style.display=''; vidEl.innerHTML=''; vidEl.style.display='none';
    imgEl.style.display=''; imgEl.src=url;
  } else {
    zone.style.display='none';
  }
};

  // =========================================================================
  // 1. VUE : LISTE DES DOCUMENTS PÉDAGOGIQUES (views/documents/list.php)
  // =========================================================================
  function initDocumentsListModule() {
    if (!$('#table-documents, #documents-list-config').length) return;

  var configEl = document.getElementById('documents-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-documents').DataTable({
    ajax: racine + 'document/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'libelle_document', defaultContent: '-' },
      { data: 'libelle_filiere', defaultContent: '-' },
      { data: 'libelle_niveau', defaultContent: '-' },
      { data: 'lien_document', defaultContent: '-', width: '130px', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;font-size:12px;font-style:italic;">Aucun fichier</span>';
        return '<a href="' + d + '" target="_blank" download class="btn btn-sm" style="background:#F1F5F9;color:#1E3A5F;border:1px solid #CBD5E1;font-weight:700;font-size:11.5px;padding:4px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;"><i data-lucide="download" style="width:13px;height:13px;"></i> Télécharger</a>';
      }},
      { data: 'statut_document', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-doc" data-id="' + row.id_document + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + racine + 'document/edition/' + (d.editId || d.id_document) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + racine + 'document/details/' + (d.editId || d.id_document) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Gestion du Switch Activer / Désactiver en Ajax
  $(document).on('change', '.toggle-statut-doc', function() {
    var idDoc = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'document/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: idDoc,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) {
            toastr.success(res.message || 'Statut mis à jour avec succès');
          }
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur de communication avec le serveur');
        $input.prop('checked', !isChecked);
      }
    });
  });

  }

  // =========================================================================
  // 2. VUE : CRÉATION / MODIFICATION D'UN DOCUMENT (views/documents/edit.php)
  // =========================================================================
  function initDocumentsEditModule() {
    if (!$('#doc_drop_zone, #documents-edit-config, #panel_upload').length) return;

  var dropZone = document.getElementById('doc_drop_zone');
  if (dropZone) {
    dropZone.addEventListener('dragover', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#1E3A5F';
      dropZone.style.background = '#EFF6FF';
    });

    dropZone.addEventListener('dragleave', function() {
      dropZone.style.borderColor = '#CBD5E1';
      dropZone.style.background = '#F8FAFC';
    });

    dropZone.addEventListener('drop', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#CBD5E1';
      dropZone.style.background = '#F8FAFC';
      var files = e.dataTransfer.files;
      if (files.length) {
        document.getElementById('fichier_upload').files = files;
        handleDocFile(files[0]);
      }
    });
  }

  var uploadInput = document.getElementById('fichier_upload');
  if (uploadInput) {
    uploadInput.addEventListener('change', function() {
      if (this.files.length) {
        handleDocFile(this.files[0]);
      }
    });
  }

  var urlInput = document.getElementById('url_fichier_input');
  if (urlInput) {
    urlInput.addEventListener('input', function() {
      var val = this.value.trim();
      document.getElementById('lien_document').value = val;
    });
  }

  if (window.lucide) lucide.createIcons();

  if (existingDocUrl) {
    if (existingDocUrl.indexOf('http') === 0) {
      switchDocTab('url');
      document.getElementById('url_fichier_input').value = existingDocUrl;
    } else {
      document.getElementById('doc_preview_zone').style.display = '';
      var fileName = existingDocUrl.split('/').pop();
      document.getElementById('doc_file_name').textContent = fileName;
      document.getElementById('doc_file_size').textContent = 'Fichier enregistré existant';
      if (window.lucide) lucide.createIcons();
    }
  }

  }

  // =========================================================================
  // 3. VUE : LISTE & GESTION DES GALERIES MÉDIAS (views/galeries/list.php)
  // =========================================================================
  function initGaleriesListModule() {
    if (!$('#table-galeries, #galeries-list-config').length) return;

  var configEl = document.getElementById('galeries-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-galeries').DataTable({
    ajax: racine + 'galerie/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_galerie',  defaultContent: '-', width: '130px' },
      { data: 'titre_galerie', defaultContent: '-' },
      { data: 'type_galerie',  defaultContent: '-', width: '110px', render: function(d) {
        if (!d) return '-';
        return d === 'video'
          ? '<span style="background:#F1F5F9;color:#475569;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Vidéo</span>'
          : '<span style="background:#EFF6FF;color:#1E3A5F;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Photo</span>';
      }},
      { data: 'statut_galerie', defaultContent: '-', width: '130px', className: 'text-center', render: function(d, type, row) {
        var val = d || 'actif';
        var bgColors = { 'actif': '#DCFCE7', 'brouillon': '#FEF3C7', 'archive': '#F1F5F9' };
        var textColors = { 'actif': '#15803D', 'brouillon': '#B45309', 'archive': '#475569' };
        var borderColors = { 'actif': '#86EFAC', 'brouillon': '#FCD34D', 'archive': '#CBD5E1' };
        var currentBg = bgColors[val] || '#F1F5F9';
        var currentText = textColors[val] || '#334155';
        var currentBorder = borderColors[val] || '#CBD5E1';

        return '<select class="select-statut-galerie" data-id="' + row.id_galerie + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="actif" ' + (val === 'actif' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Actif</option>' +
               '<option value="brouillon" ' + (val === 'brouillon' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">Brouillon</option>' +
               '<option value="archive" ' + (val === 'archive' ? 'selected' : '') + ' style="background:#fff; color:#475569;">Archivé</option>' +
               '</select>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + racine + 'galerie/edition/' + (d.editId || d.id_galerie) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + racine + 'galerie/details/' + (d.editId || d.id_galerie) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Changement de statut via Ajax
  $(document).on('change', '.select-statut-galerie', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: racine + 'galerie/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: newStatut,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          table.ajax.reload(null, false);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        table.ajax.reload(null, false);
      }
    });
  });

  }

  // =========================================================================
  // 4. VUE : CRÉATION / MODIFICATION D'UN ÉLÉMENT DE GALERIE (views/galeries/edit.php)
  // =========================================================================
  function initGaleriesEditModule() {
    if (!$('#drop_zone, #galeries-edit-config, #preview_zone').length) return;

  var dz = document.getElementById('drop_zone');
  if (dz) {
    dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor='#1E3A5F'; });
    dz.addEventListener('dragleave', function(){ dz.style.borderColor='#CBD5E1'; });
    dz.addEventListener('drop', function(e){
      e.preventDefault(); dz.style.borderColor='#CBD5E1';
      var files = e.dataTransfer.files;
      if (files.length) { document.getElementById('fichier_upload').files = files; handleFile(files[0]); }
    });
  }

  var uploadInp = document.getElementById('fichier_upload');
  if (uploadInp) {
    uploadInp.addEventListener('change', function(){
      if (this.files.length) handleFile(this.files[0]);
    });
  }

  var urlInp = document.getElementById('url_fichier_input');
  if (urlInp) {
    urlInp.addEventListener('input', function(){
      var val = this.value.trim();
      previewUrl(val);
      document.getElementById('url_fichier').value = val;
    });
  }

  if (window.lucide) lucide.createIcons();
  if (existingUrl) {
    switchTab('url');
    document.getElementById('url_fichier_input').value = existingUrl;
    previewUrl(existingUrl);
  }

  }

  $(document).ready(function() {
    initDocumentsListModule();
    initDocumentsEditModule();
    initGaleriesListModule();
    initGaleriesEditModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);