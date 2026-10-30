$(document).ready(function() {
  var cfg = {};
  try {
    var cfgEl = document.getElementById('enseignant-matiere-list-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var csrfToken = cfg.csrfToken || '';

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Filtrage en cascade Niveau -> Classe
  $('#filter-niveau').on('change', function() {
    var niveauCode = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!niveauCode || !optNiveau || optNiveau === niveauCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#filter-classe option:selected').prop('disabled')) {
      $('#filter-classe').val('').trigger('change.select2');
    } else {
      $('#filter-classe').select2({ width: '100%' });
    }
    table.ajax.reload();
  });

  $('#filter-classe, #filter-annee').on('change', function() {
    table.ajax.reload();
  });

  var table = $('#table-enseignant_matiere').DataTable({
    ajax: {
      url: window.RACINE + 'enseignant_matiere/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'enseignant_nom', render: function(d, type, row) {
        if (type !== 'display') return d || row.enseignant_code || '';
        var nom = (d && d.trim().length > 0) ? d : row.enseignant_code;
        return '<strong style="color:#0F172A;">' + (nom || '-') + '</strong>';
      }},
      { data: 'libelle_matiere', render: function(d, type, row) {
        if (type !== 'display') return d || row.matiere_code || '';
        return '<span style="color:#1E3A5F; font-weight:600;">' + (d || row.matiere_code || '-') + '</span>';
      }},
      { data: 'libelle_classe', render: function(d, type, row) {
        if (type !== 'display') return d || row.classe_code || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || row.classe_code || '<span style="color:#94A3B8; font-style:italic;">Non assignée</span>') + '</span>';
      }},
      { data: 'coefficient', width: '90px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || 1;
        return '<span style="display:inline-block; padding: 3px 10px; border-radius: 6px; font-size: 13px; font-weight: 800; background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;">' + (d || '1.0') + '</span>';
      }},
      { data: 'statut_enseignant_matiere', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-em" data-id="' + row.id_enseignant_matiere + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-em" data-id="' + (d.id_enseignant_matiere) + '" data-enseignant="' + (d.enseignant_code || '') + '" data-matiere="' + (d.matiere_code || '') + '" data-classe="' + (d.classe_code || '') + '" data-coef="' + (d.coefficient || '1.0') + '" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>'
             + '<a href="' + window.RACINE + 'enseignant_matiere/details/' + (d.editId || d.id_enseignant_matiere) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: window.RACINE + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.toggle-statut-em', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: window.RACINE + 'enseignant_matiere/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  // GESTION MODALE AJOUT / ÉDITION AFFECTATION ENSEIGNANT-MATIÈRE
  $(document).on('click', '.btn-add-em', function(e) {
    e.preventDefault();
    $('#form-em')[0].reset();
    $('#em_id').val('');
    $('#em_coef').val('1.0');
    $('#modal-em-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Affectation Matière / Enseignant');
    $('#modal-em').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#em_enseignant').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-em', function(e) {
    e.preventDefault();
    $('#form-em')[0].reset();
    var id = $(this).data('id');
    var ens = $(this).data('enseignant');
    var mat = $(this).data('matiere');
    var cls = $(this).data('classe');
    var coef = $(this).data('coef');

    $('#em_id').val(id);
    $('#em_enseignant').val(ens);
    $('#em_matiere').val(mat);
    $('#em_classe').val(cls);
    $('#em_coef').val(coef || '1.0');

    $('#modal-em-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Affectation');
    $('#modal-em').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-em').on('click', function() {
    $('#modal-em').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-em')) {
      $('#modal-em').hide();
    }
  });

  $('#form-em').on('submit', function(e) {
    e.preventDefault();
    var id = $('#em_id').val();
    var url = window.RACINE + (id ? 'enseignant_matiere/edit' : 'enseignant_matiere/add');
    var $btn = $('#btn-submit-em');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: url,
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Affectation enregistrée avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Affectation enregistrée avec succès');
          $('#modal-em').hide();
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });
});

