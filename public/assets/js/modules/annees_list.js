/**
 * Module JS : Liste & Gestion des Années Académiques (views/annees/list.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('annees-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  var table = $('#table-annees').DataTable({
    ajax: racine + 'annee/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_annee', width: '120px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_annee', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { 
        data: 'date_debut_annee', 
        render: function(d) {
          if (!d) return '<span style="color:#94A3B8;">-</span>';
          var p = d.split('-');
          var formatted = p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
          return '<span style="font-weight:700; color:#1E293B; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="calendar" style="width:14px;height:14px;color:#1E3A5F;"></i> ' + formatted + '</span>';
        }
      },
      { 
        data: 'date_fin_annee', 
        render: function(d) {
          if (!d) return '<span style="color:#94A3B8;">-</span>';
          var p = d.split('-');
          var formatted = p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
          return '<span style="font-weight:700; color:#1E293B; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="calendar" style="width:14px;height:14px;color:#1E3A5F;"></i> ' + formatted + '</span>';
        }
      },
      { data: 'statut_annee', width: '130px', className: 'text-center', render: function(d, type, row) {
        if (d === 'actif') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Active en cours</span>';
        } else if (d === 'planifie') {
          return '<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En préparation</span>';
        } else {
          return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturée</span>';
        }
      }},
      { data: null, width: '220px', orderable: false, render: function(d) {
        var isActif = (d.statut_annee === 'actif');
        var isPlanifie = (d.statut_annee === 'planifie');

        var statusBtn = '';
        if (isActif) {
          statusBtn = '<button type="button" class="btn btn-sm btn-warning btn-activate-annee" data-id="' + d.id_annee + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer; background:#F59E0B; border-color:#F59E0B; color:#FFFFFF;" title="Clôturer cette année académique"><i data-lucide="lock" style="width:14px;height:14px;"></i> Clôturer</button>';
        } else if (isPlanifie) {
          statusBtn = '<button type="button" class="btn btn-sm btn-success btn-activate-annee" data-id="' + d.id_annee + '" style="margin-right:6px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" title="Activer cette année académique"><i data-lucide="power" style="width:14px;height:14px;"></i> Activer</button>';
        }

        var editBtn = isPlanifie ?
          '<button type="button" class="btn btn-sm btn-secondary btn-edit-annee" data-id="' + d.id_annee + '" data-libelle="' + (d.libelle_annee ? $('<div>').text(d.libelle_annee).html() : '') + '" data-debut="' + (d.date_debut_annee || '') + '" data-fin="' + (d.date_fin_annee || '') + '" data-statut="' + (d.statut_annee || 'planifie') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' :
          '<button class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; opacity:0.5; cursor:not-allowed;" disabled title="Seule une année académique en préparation (planifiée) peut être éditée"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';

        return statusBtn + editBtn +
               '<a href="' + racine + 'annee/details/' + (d.editId || d.id_annee) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule d'activation d'année via Ajax
  $(document).on('click', '.btn-activate-annee', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    $.ajax({
      url: racine + 'annee/changer',
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
            showToast(res.message || 'Année activée avec succès', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Année activée avec succès');
          }
          if (res.activeYear && res.activeYear.libelle_annee) {
            $('#activeAnneeDisplay').text(res.activeYear.libelle_annee);
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
          showToast('Erreur de communication réseau', 'error');
        } else if (window.toastr) {
          toastr.error('Erreur réseau');
        }
      }
    });
  });

  // GESTION MODALE AJOUT / ÉDITION ANNÉE ACADÉMIQUE
  $(document).on('click', '.btn-add-annee', function(e) {
    e.preventDefault();
    $('#form-annee')[0].reset();
    $('#annee_id').val('');
    $('#modal-annee-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Année Académique');
    $('#modal-annee').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#annee_libelle').focus(); }, 100);
  });

  $(document).on('click', '.btn-edit-annee', function(e) {
    e.preventDefault();
    $('#form-annee')[0].reset();
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var debut = $(this).data('debut');
    var fin = $(this).data('fin');

    $('#annee_id').val(id);
    $('#annee_libelle').val(libelle);
    $('#annee_date_debut').val(debut);
    $('#annee_date_fin').val(fin);

    $('#modal-annee-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Année Académique');
    $('#modal-annee').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#annee_libelle').focus(); }, 100);
  });

  $('.btn-close-modal-annee').on('click', function() {
    $('#modal-annee').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-annee')) {
      $('#modal-annee').hide();
    }
  });

  $('#form-annee').on('submit', function(e) {
    e.preventDefault();
    var id = $('#annee_id').val();
    var url = racine + (id ? 'annee/edit' : 'annee/add');
    var $btn = $('#btn-submit-annee');
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
          if (typeof showToast === 'function') {
            showToast(res.message || 'Année académique enregistrée avec succès', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Année académique enregistrée avec succès');
          }
          $('#modal-annee').hide();
          table.ajax.reload(null, false);
        } else {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          }
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
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });
});

