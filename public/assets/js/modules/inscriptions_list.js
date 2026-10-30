/**
 * Module JS: Liste des Étudiants à Réinscrire
 * Externalisation stricte du code JS pour views/inscriptions/list.php
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  if ($.fn.select2) {
    $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe').select2({
      width: '100%'
    });
  }

  var table = $('#table-inscriptions').DataTable({
    order: [],
    ajax: {
      url: racine + 'inscription/apiList',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'matricule_etudiant', render: function(d) {
        return '<code style="font-weight:800; color:#1E3A5F; background:#EFF6FF; padding:3px 7px; border-radius:6px; font-size:12px;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_complet', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; font-size:13.5px;">' + (d || '-') + '</span>';
      }},
      { data: 'telephone', render: function(d) {
        if (!d || d === '-') return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Non renseigné</span>';
        return '<span style="color:#334155; font-weight:700; font-size:12.5px;"><i data-lucide="phone" style="width:12px;height:12px;display:inline-block;vertical-align:-1px;margin-right:4px;color:#2563EB;"></i>' + d + '</span>';
      }},
      { data: 'filiere_precedente', render: function(d, type, row) {
        var fil = d || 'Non définie';
        return '<div>' +
               '  <div style="font-weight:700; color:#1E3A5F; font-size:13px;">' + fil + '</div>' +
               '  <div style="font-size:11.5px; color:#64748B;">' + (row.niveau_precedent || 'Niveau non défini') + '</div>' +
               '</div>';
      } },
      { data: 'classe_precedente', render: function(d, type, row) {
        if (!d) {
          return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Nouveau / Sans historique</span>';
        }
        var annee = row.annee_precedente ? ' <span style="font-size:11px; color:#64748B;">(' + row.annee_precedente + ')</span>' : '';
        return '<div style="font-weight:700; color:#334155; font-size:13px;">' + d + annee + '</div>';
      } },
      { data: null, orderable: false, width: '220px', className: 'text-end', render: function(d, type, row) {
        var idInscription = row.editId || row.id_inscription;
        var printUrl = idInscription ? (racine + 'inscription/details/' + idInscription + '?print=1') : (racine + 'etudiant/details/' + encodeURIComponent(row.code_etudiant || row.id_etudiant) + '?print=1');
        return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">' +
               '  <a href="' + printUrl + '" target="_blank" class="btn btn-sm btn-outline-primary" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;" title="Imprimer la fiche"><i data-lucide="printer" style="width:13px;height:13px;"></i> Imprimer</a>' +
               '  <a href="' + racine + 'inscription/formulaire?etudiant_code=' + encodeURIComponent(row.code_etudiant) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; font-weight:700; border-radius:6px; padding:5px 10px; display:inline-flex; align-items:center; gap:4px; box-shadow:0 1px 3px rgba(30,58,95,0.2);">' +
               '    <i data-lucide="user-check" style="width:13px;height:13px;"></i> Réinscrire' +
               '  </a>' +
               '</div>';
      } }
    ],

    language: dtLang,
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
  });

  function toggleFieldsByAnnee() {
    var val = $('#filter-annee').val();
    var hasAnnee = !!(val && val !== '');
    var $targets = $('#filter-filiere, #filter-niveau, #filter-classe');

    $targets.prop('disabled', !hasAnnee);
    if (!hasAnnee) {
      $targets.attr('readonly', 'readonly');
      $('#btn-reset-filters').prop('disabled', true).addClass('disabled').css('opacity', '0.5');
    } else {
      $targets.removeAttr('readonly');
      $('#btn-reset-filters').prop('disabled', false).removeClass('disabled').css('opacity', '1');
    }

    if ($.fn.select2) {
      $targets.trigger('change.select2');
      $targets.each(function() {
        var $container = $(this).next('.select2-container');
        if (!hasAnnee) {
          $container.find('.select2-selection').css({
            'background-color': '#F8FAFC',
            'cursor': 'not-allowed',
            'opacity': '0.7'
          });
        } else {
          $container.find('.select2-selection').css({
            'background-color': '#FFFFFF',
            'cursor': 'pointer',
            'opacity': '1'
          });
        }
      });
    }
    return hasAnnee;
  }

  toggleFieldsByAnnee();

  $('#filter-annee').on('change', function() {
    var hasAnnee = toggleFieldsByAnnee();
    if (!hasAnnee) {
      $('#filter-filiere').val('ALL').trigger('change.select2');
      $('#filter-niveau').val('ALL').trigger('change.select2');
      $('#filter-classe').val('ALL').trigger('change.select2');
    }
  });

  $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe').on('change', function() {
    var filSelected = $('#filter-filiere').val();
    var nivSelected = $('#filter-niveau').val();

    $('#filter-classe option').each(function() {
      var val = $(this).val();
      if (val === 'ALL') return;
      var cFil = $(this).attr('data-filiere');
      var cNiv = $(this).attr('data-niveau');

      var matchFil = (filSelected === 'ALL' || cFil === filSelected);
      var matchNiv = (nivSelected === 'ALL' || cNiv === nivSelected);

      if (matchFil && matchNiv) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });

    table.ajax.reload();
  });

  $('#btn-reset-filters').on('click', function() {
    if ($('#btn-reset-filters').is(':disabled')) return;
    $('#filter-filiere').val('ALL').trigger('change.select2');
    $('#filter-niveau').val('ALL').trigger('change.select2');
    $('#filter-classe').val('ALL').trigger('change.select2');
    $('#filter-classe option').show();
    table.ajax.reload();
  });
});
