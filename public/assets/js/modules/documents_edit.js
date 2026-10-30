/**
 * Module JS : Création / Modification d'un Document & Support (views/documents/edit.php)
 */
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

$(document).ready(function() {
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
});

