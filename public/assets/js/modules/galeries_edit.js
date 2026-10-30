/**
 * Module JS : Création / Modification d'un Élément de Galerie (views/galeries/edit.php)
 */
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

$(document).ready(function() {
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
});

