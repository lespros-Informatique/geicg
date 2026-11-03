/**
 * Module JS : Traçabilité & Journal des Activités (views/audit/list.php)
 * Projet GEICG
 */
$(document).ready(function() {
  var configEl = document.getElementById('audit-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';

  if (window.lucide) lucide.createIcons();

  // Helper pour formater les badges d'actions
  function renderActionBadge(action) {
    var act = (action || '').toUpperCase();
    var style = 'padding: 4px 9px; border-radius: 6px; font-weight: 700; font-size: 11px; display: inline-block;';
    switch (act) {
      case 'CONNEXION':
        return '<span class="badge" style="' + style + ' background:#DCFCE7; color:#15803D;"><i data-lucide="log-in" style="width:12px;height:12px;margin-right:3px;vertical-align:text-bottom;"></i> Connexion</span>';
      case 'DECONNEXION':
        return '<span class="badge" style="' + style + ' background:#F1F5F9; color:#475569;"><i data-lucide="log-out" style="width:12px;height:12px;margin-right:3px;vertical-align:text-bottom;"></i> Déconnexion</span>';
      case 'AJOUT':
        return '<span class="badge" style="' + style + ' background:#DBEAFE; color:#1D4ED8;"><i data-lucide="plus" style="width:12px;height:12px;margin-right:3px;vertical-align:text-bottom;"></i> Création</span>';
      case 'MODIFICATION':
        return '<span class="badge" style="' + style + ' background:#FEF3C7; color:#B45309;"><i data-lucide="edit-2" style="width:12px;height:12px;margin-right:3px;vertical-align:text-bottom;"></i> Modification</span>';
      case 'SUPPRESSION':
        return '<span class="badge" style="' + style + ' background:#FEE2E2; color:#DC2626;"><i data-lucide="trash-2" style="width:12px;height:12px;margin-right:3px;vertical-align:text-bottom;"></i> Suppression</span>';
      case 'IMPRESSION':
        return '<span class="badge" style="' + style + ' background:#F3E8FF; color:#7E22CE;"><i data-lucide="printer" style="width:12px;height:12px;margin-right:3px;vertical-align:text-bottom;"></i> Impression</span>';
      default:
        return '<span class="badge" style="' + style + ' background:#F1F5F9; color:#475569;">' + act + '</span>';
    }
  }

  // Initialisation DataTable
  var table = $('#table-audit-logs').DataTable({
    ajax: {
      url: racine + 'audit/apiList',
      data: function(d) {
        d.user_id = $('#filter-user').val();
        d.action = $('#filter-action').val();
        d.module = $('#filter-module').val();
        d.date_debut = $('#filter-date-debut').val();
        d.date_fin = $('#filter-date-fin').val();
      }
    },
    processing: true,
    serverSide: false,
    autoWidth: false,
    order: [[1, 'desc']],
    pageLength: 25,
    columns: [
      {
        data: null,
        width: '45px',
        orderable: false,
        render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }
      },
      {
        data: 'date',
        width: '140px',
        render: function(d) {
          return '<span style="font-weight:600; color:#0F172A; font-size:12px;">' + (d || '-') + '</span>';
        }
      },
      {
        data: null,
        render: function(d) {
          var nom = $('<div>').text(d.user_nom || 'Système').html();
          var code = $('<div>').text(d.user_code || '-').html();
          return '<div style="font-weight:700; color:#0F172A; font-size:12.5px;">' + nom + '</div>' +
                 '<div style="font-size:11px; color:#64748B; font-family:monospace;">' + code + '</div>';
        }
      },
      {
        data: 'action',
        className: 'text-center',
        render: function(d) {
          return renderActionBadge(d);
        }
      },
      {
        data: 'module',
        render: function(d) {
          return '<span style="background:#F1F5F9; color:#1E3A5F; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; border:1px solid #E2E8F0;">' + (d || '-') + '</span>';
        }
      },
      {
        data: 'entite_id',
        render: function(d) {
          return '<span style="font-family:monospace; font-size:11.5px; color:#475569; font-weight:600;">' + ($('<div>').text(d || '-').html()) + '</span>';
        }
      },
      {
        data: 'description',
        render: function(d) {
          return '<span style="font-size:12.5px; color:#1E293B; font-weight:500;">' + ($('<div>').text(d || '').html()) + '</span>';
        }
      },
      {
        data: 'ip',
        render: function(d) {
          return '<span style="font-size:11px; color:#64748B; font-family:monospace;">' + (d || '-') + '</span>';
        }
      },
      {
        data: null,
        orderable: false,
        className: 'text-center',
        render: function(d) {
          return '<button type="button" class="btn btn-sm btn-outline-primary btn-view-log-details" style="padding:4px 8px; font-size:11px; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" title="Voir tous les détails">' +
                 '<i data-lucide="eye" style="width:13px;height:13px;"></i>' +
                 '</button>';
        }
      }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() {
      if (window.lucide) lucide.createIcons();
    }
  });

  // Initialisation Select2 des filtres
  if ($.fn.select2) {
    $('#filter-user, #filter-action, #filter-module').select2({
      width: '100%'
    });

    $('#filter-user, #filter-action, #filter-module').on('change', function() {
      table.ajax.reload();
    });
  }

  // Soumission des filtres
  $('#form-filter-logs').on('submit', function(e) {
    e.preventDefault();
    table.ajax.reload();
  });

  // Réinitialisation des filtres
  $('#btn-reset-filters').on('click', function() {
    $('#filter-user').val('');
    $('#filter-action').val('');
    $('#filter-module').val('');
    if ($.fn.select2) {
      $('#filter-user').trigger('change.select2');
      $('#filter-action').trigger('change.select2');
      $('#filter-module').trigger('change.select2');
    }
    $('#filter-date-debut').val('');
    $('#filter-date-fin').val('');
    table.ajax.reload();
  });

  // Bouton Actualiser
  $('#btn-refresh-logs').on('click', function() {
    table.ajax.reload(null, false);
  });

  // Affichage Modal Détails
  $('#table-audit-logs tbody').on('click', '.btn-view-log-details', function() {
    var tr = $(this).closest('tr');
    var rowData = table.row(tr).data();
    if (!rowData) return;

    $('#modal-log-user').text(rowData.user_nom || 'Système');
    $('#modal-log-usercode').text(rowData.user_code || '-');
    $('#modal-log-date').text(rowData.date || '-');
    $('#modal-log-ip').text('IP : ' + (rowData.ip || '-'));
    $('#modal-log-desc').text(rowData.description || '-');
    $('#modal-log-useragent').text(rowData.user_agent || '-');

    if (rowData.details) {
      try {
        var parsed = JSON.parse(rowData.details);
        $('#modal-log-json').text(JSON.stringify(parsed, null, 2));
      } catch (e) {
        $('#modal-log-json').text(rowData.details);
      }
    } else {
      $('#modal-log-json').text('Aucune donnée contextuelle complémentaire enregistrée.');
    }

    var modalEl = document.getElementById('modal-log-details');
    if (window.bootstrap && bootstrap.Modal) {
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    } else {
      $('#modal-log-details').modal('show');
    }

    setTimeout(function() {
      if (window.lucide) lucide.createIcons();
    }, 150);
  });

});

