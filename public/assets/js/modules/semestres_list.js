/**
 * Module JS : Liste & Gestion des Semestres (views/semestres/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('semestres-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';
  var selectedAnneeCode = config.selectedAnneeCode || '';

  if (window.lucide) lucide.createIcons();

  var table = $('#table-semestres').DataTable({
    ajax: racine + 'semestre/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_semestre', defaultContent: '-', width: '110px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_semestre', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_annee', defaultContent: '<span style="color:#94A3B8; font-style:italic;">Non définie</span>', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#1E3A5F; font-weight:600;">' + (d || '<span style="color:#94A3B8; font-style:italic;">Non définie</span>') + '</span>';
      }},
      { data: 'date_debut_semestre', defaultContent: '-' },
      { data: 'date_fin_semestre', defaultContent: '-' },
      { data: 'statut_semestre', width: '130px', className: 'text-center', render: function(d, type, row) {
        if (d === 'actif') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Active en cours</span>';
        } else if (d === 'planifie') {
          return '<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En préparation</span>';
        } else {
          return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturé</span>';
        }
      }},
      { data: null, width: '220px', orderable: false, render: function(d) {
        var isActif = (d.statut_semestre === 'actif');
        var isPlanifie = (d.statut_semestre === 'planifie');

        var statusBtn = '';
        if (isActif) {
          statusBtn = '<button type="button" class="btn btn-sm btn-warning btn-activate-semestre" data-id="' + d.id_semestre + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer; background:#F59E0B; border-color:#F59E0B; color:#FFFFFF;" title="Clôturer ce semestre"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturer</button>';
        } else if (isPlanifie) {
          statusBtn = '<button type="button" class="btn btn-sm btn-success btn-activate-semestre" data-id="' + d.id_semestre + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" title="Activer ce semestre"><i data-lucide="power" style="width:14px;height:14px;"></i> Activer</button>';
        }

        var editBtn = isPlanifie ?
          '<button type="button" class="btn btn-sm btn-secondary btn-edit-semestre" data-id="' + d.id_semestre + '" data-libelle="' + (d.libelle_semestre || '') + '" data-annee="' + (d.annee_code || '') + '" data-debut="' + (d.date_debut_semestre || '') + '" data-fin="' + (d.date_fin_semestre || '') + '" data-statut="' + (d.statut_semestre || 'planifie') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' :
          '<button class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; opacity:0.5; cursor:not-allowed;" disabled title="Seul un semestre en préparation (planifié) peut être édité"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';

        return statusBtn + editBtn +
               '<a href="' + racine + 'semestre/details/' + (d.editId || d.id_semestre) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule de statut d'un semestre via Ajax (Activer / Clôturer)
  $(document).on('click', '.btn-activate-semestre', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    $.ajax({
      url: racine + 'semestre/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Statut mis à jour avec succès', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Statut mis à jour avec succès');
          }
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors du changement de statut', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors du changement de statut');
          }
        }
      },
      error: function() {
        if (typeof showToast === 'function') {
          showToast('Erreur réseau', 'error');
        } else if (window.toastr) {
          toastr.error('Erreur réseau');
        }
      }
    });
  });

  // GESTION MODALE AJOUT / ÉDITION SEMESTRE
  $(document).on('click', '.btn-add-semestre', function(e) {
    e.preventDefault();
    $('#form-semestre')[0].reset();
    $('#semestre_id').val('');
    $('#semestre_statut').val('planifie');
    $('#semestre_annee').val(selectedAnneeCode);
    $('#modal-semestre-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Semestre');
    $('#modal-semestre').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#semestre_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-semestre', function(e) {
    e.preventDefault();
    $('#form-semestre')[0].reset();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var annee = $(this).data('annee');
    var debut = $(this).data('debut');
    var fin = $(this).data('fin');
    var statut = $(this).data('statut') || 'planifie';

    $('#semestre_id').val(id);
    $('#semestre_libelle').val(libelle);
    $('#semestre_annee').val(annee);
    $('#semestre_date_debut').val(debut !== '-' ? debut : '');
    $('#semestre_date_fin').val(fin !== '-' ? fin : '');
    $('#semestre_statut').val(statut);

    $('#modal-semestre-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Semestre');
    $('#modal-semestre').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#semestre_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-semestre').on('click', function() {
    $('#modal-semestre').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-semestre')) {
      $('#modal-semestre').hide();
    }
  });

  $('#form-semestre').on('submit', function(e) {
    e.preventDefault();
    var id = $('#semestre_id').val();
    var url = racine + (id ? 'semestre/edit' : 'semestre/add');
    var $btn = $('#btn-submit-semestre');
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
          if (typeof showToast === 'function') showToast(res.message || 'Semestre enregistré avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Semestre enregistré avec succès');
          $('#modal-semestre').hide();
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

