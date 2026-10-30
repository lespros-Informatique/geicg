$(document).ready(function() {
  var cfg = {};
  try {
    var cfgEl = document.getElementById('compositions-list-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var csrfToken = cfg.csrfToken || '';

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Filtrage Niveau -> Classe
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
    var cls = $('#filter-classe').val();
    if (cls) {
      $('#btn-add-composition').attr('href', window.RACINE + 'composition/formulaire?classe_code=' + encodeURIComponent(cls));
    } else {
      $('#btn-add-composition').attr('href', window.RACINE + 'composition/formulaire');
    }
    table.ajax.reload();
  });

  var table = $('#table-compositions').DataTable({
    ajax: {
      url: window.RACINE + 'composition/apiList',
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
      { 
        data: null, 
        defaultContent: '',
        width: '50px', 
        render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }
      },
      { 
        data: 'libelle_composition', 
        defaultContent: '',
        render: function(d, t, r) { 
          var typ = (r && r.type_composition) ? r.type_composition : 'EXAMEN';
          var badgeStyle = 'background:#EFF6FF; color:#1D4ED8;';
          var badgeText = 'EXAMEN';
          if (typ === 'DEVOIR') {
            badgeStyle = 'background:#FEF3C7; color:#B45309;';
            badgeText = 'DEVOIR';
          } else if (typ === 'INTERROGATION') {
            badgeStyle = 'background:#F0FDF4; color:#15803D;';
            badgeText = 'INTERRO';
          } else if (typ !== 'EXAMEN') {
            badgeStyle = 'background:#F1F5F9; color:#475569;';
            badgeText = typ;
          }
          var typeBadge = '<span style="font-size:10px; font-weight:800; ' + badgeStyle + ' padding:2px 7px; border-radius:4px; margin-right:6px; display:inline-block;">' + badgeText + '</span>';
          return '<div>' + typeBadge + '<strong style="font-size:13px; color:#0F172A;">' + (d || r.code_composition || '-') + '</strong></div>'; 
        } 
      },
      { 
        data: 'libelle_semestre', 
        defaultContent: '',
        render: function(d, t, r) { 
          return '<span style="font-size:13px; color:#0284C7; font-weight:700;">' + (d || r.semestre_code || '-') + '</span>'; 
        } 
      },
      { 
        data: 'coefficient', 
        defaultContent: '1.00',
        className: 'text-center',
        render: function(d) { 
          var coef = d ? parseFloat(d).toFixed(2) : '1.00';
          return '<span class="badge" style="background:#F1F5F9; color:#334155; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px;">' + coef + '</span>'; 
        } 
      },
      { 
        data: 'date_composition', 
        defaultContent: '',
        render: function(d, type, row) { 
          var dateStr = d || (row ? row.created_at_composition : null);
          if (!dateStr) return '<span style="color:#94A3B8;">-</span>';
          var cleanDate = dateStr.split(' ')[0];
          var parts = cleanDate.split('-');
          if (parts.length === 3) {
            return '<strong style="font-size:13px; color:#1E3A5F;">' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</strong>';
          }
          return '<strong style="font-size:13px; color:#1E3A5F;">' + cleanDate + '</strong>';
        } 
      },
      { 
        data: 'date_fin_composition', 
        defaultContent: '',
        render: function(d, type, row) { 
          var dateStr = d || (row ? (row.date_composition || row.created_at_composition) : null);
          if (!dateStr) return '<span style="color:#94A3B8;">-</span>';
          var cleanDate = dateStr.split(' ')[0];
          var parts = cleanDate.split('-');
          if (parts.length === 3) {
            return '<strong style="font-size:13px; color:#1E3A5F;">' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</strong>';
          }
          return '<strong style="font-size:13px; color:#1E3A5F;">' + cleanDate + '</strong>';
        } 
      },
      { 
        data: 'statut_composition', 
        defaultContent: 'programme',
        width: '90px', 
        className: 'text-center', 
        render: function(d, type, row) {
          var isActif = (d === 'programme' || d === 'actif');
          var checkedAttr = isActif ? 'checked' : '';
          return '<div style="display:flex; justify-content:center; align-items:center;">' +
                 '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Programmé - Cliquez pour terminer' : 'Terminé - Cliquez pour programmer') + '">' +
                 '<input type="checkbox" class="toggle-statut-composition" data-id="' + (row ? row.id_composition : '') + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                 '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                 '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                 '</span>' +
                 '</label>' +
                 '</div>';
        }
      },
      { 
        data: null, 
        defaultContent: '',
        orderable: false, 
        className: 'text-end',
        render: function(d) {
          if (!d) return '';
          var detailsUrl = window.RACINE + 'composition/details/' + (d.editId || d.id_composition);
          var editUrl = window.RACINE + 'composition/edition/' + (d.editId || d.id_composition);
          var noterUrl = window.RACINE + 'note/saisieClasse?type_evaluation_code=EXAMEN&composition_code=' + encodeURIComponent(d.code_composition || '');
          return '<div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:nowrap; align-items:center;">' +
                   '<a href="' + noterUrl + '" class="btn btn-sm" style="background:#16A34A; color:#FFFFFF; border:none; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Saisir les notes de l\'épreuve">' +
                     '<i data-lucide="edit-3" style="width:14px; height:14px;"></i> Noter' +
                   '</a>' +
                   '<a href="' + detailsUrl + '" class="btn btn-sm" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Voir les détails de l\'épreuve">' +
                     '<i data-lucide="eye" style="width:14px; height:14px;"></i> Détails' +
                   '</a>' +
                   '<a href="' + editUrl + '" class="btn btn-sm" style="background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:5px;" title="Modifier">' +
                     '<i data-lucide="edit" style="width:14px; height:14px;"></i> Éditer' +
                   '</a>' +
                 '</div>';
        } 
      }
    ],
    language: { 
      url: window.RACINE + 'public/json/datatables-i18n-fr-FR.json'
    },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
      try {
        var api = this.api();
        var rows = api.rows({ search: 'applied' }).data().toArray();
        var total = rows.length;
        var programme = 0;
        var termine = 0;
        rows.forEach(function(r) {
          if (r && (r.statut_composition === 'termine')) {
            termine++;
          } else {
            programme++;
          }
        });
        $('#kpi-total-compositions').text(total);
        $('#kpi-programme-compositions').text(programme);
        $('#kpi-termine-compositions').text(termine);
      } catch (e) {}
    }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-composition', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: window.RACINE + 'composition/changer',
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
});

