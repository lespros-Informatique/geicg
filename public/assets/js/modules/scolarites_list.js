/**
 * ==========================================================================
 * GEICG - MODULE LISTING DES SCOLARITÉS & TRANCHES (scolarites_list.js)
 * Externalisation JS stricte - Zéro JS inline dans les vues
 * ==========================================================================
 */

(function(window, $) {
  'use strict';

  var GEICG = window.GEICG || {};

  function initScolaritesListModule() {
    var $tableScolarites = $('#table-scolarites');
    var $tableTranches = $('#table-tranches_scolarite');
    if (!$tableScolarites.length && !$tableTranches.length) return;

    if ($.fn.select2) {
      $('#filter-annee, #filter-filiere, #filter-niveau, #filter-affectation').select2({ width: '100%' });
    }

    function updatePrintUrl() {
      var annee = $('#filter-annee').val() || '';
      var filiere = $('#filter-filiere').val() || '';
      var niveau = $('#filter-niveau').val() || '';
      var affectation = $('#filter-affectation').val() || '';

      var params = new URLSearchParams();
      if (annee) params.set('annee_code', annee);
      if (filiere) params.set('filiere_code', filiere);
      if (niveau) params.set('niveau_code', niveau);
      if (affectation) params.set('affectation_etat', affectation);

      var qs = params.toString();
      var href = GEICG.url('scolarite/imprimerPdf' + (qs ? '?' + qs : ''));
      $('#btn-print-scolarite').attr('href', href);
    }

    $('#filter-annee, #filter-filiere, #filter-niveau, #filter-affectation').on('change', function() {
      if (tableScolarites) tableScolarites.ajax.reload();
      if (tableTranches) tableTranches.ajax.reload();
      updatePrintUrl();
    });

    $('#btn-reset-filters').on('click', function(e) {
      e.preventDefault();
      var defAnnee = $('#filter-annee').data('default') || '';
      $('#filter-annee').val(defAnnee).trigger('change.select2');
      $('#filter-filiere').val('').trigger('change.select2');
      $('#filter-niveau').val('').trigger('change.select2');
      $('#filter-affectation').val('').trigger('change.select2');

      if (tableScolarites) tableScolarites.ajax.reload();
      if (tableTranches) tableTranches.ajax.reload();
      updatePrintUrl();
    });

    updatePrintUrl();

    var tableScolarites = $tableScolarites.DataTable({
      ajax: {
        url: GEICG.url('scolarite/apiList'),
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.filiere_code = $('#filter-filiere').val();
          d.niveau_code = $('#filter-niveau').val();
          d.affectation_etat = $('#filter-affectation').val();
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { data: null, width: '50px', render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }},
        { data: 'code_scolarite', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
        { data: 'affectation_etat', render: function(d) { 
          var isAffecte = (d === 'affecte');
          var bg = isAffecte ? '#DCFCE7' : '#F1F5F9';
          var fg = isAffecte ? '#15803D' : '#475569';
          var label = isAffecte ? 'Affecté (État)' : 'Non Affecté (Privé)';
          return '<span class="badge" style="background:' + bg + '; color:' + fg + '; font-weight:700; font-size:12px; padding:4px 10px; border-radius:6px; display:inline-block;">' + label + '</span>'; 
        } },
        { data: 'libelle_filiere', render: function(d, t, r) { return '<span style="font-weight:700; color:#0F172A;">' + (d || r.filiere_code || 'Non définie') + '</span>'; } },
        { data: 'libelle_niveau', render: function(d, t, r) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || r.niveau_code || 'Non défini') + '</span>'; } },
        { data: 'montant_scolarite', render: function(d) { 
          return '<span style="font-weight:800; color:#0F172A;">' + (d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA') + '</span>'; 
        } },
        { data: 'statut_scolarite', width: '90px', className: 'text-center', render: function(d, type, row) {
          var isActif = (d === 'actif');
          var checkedAttr = isActif ? 'checked' : '';
          return '<div style="display:flex; justify-content:center; align-items:center;">' +
                 '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
                 '<input type="checkbox" class="toggle-statut-scolarite" data-id="' + row.id_scolarite + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                 '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                 '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                 '</span>' +
                 '</label>' +
                 '</div>';
        } },
        { data: null, orderable: false, render: function(d) {
          return '<a href="' + GEICG.url('scolarite/edition/' + (d.editId || d.id_scolarite)) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
                 '<a href="' + GEICG.url('scolarite/details/' + (d.editId || d.id_scolarite)) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
        }, className: 'text-end' }
      ],
      language: GEICG.dataTableLanguage,
      drawCallback: function() { if (window.lucide) lucide.createIcons(); }
    });

    var tableTranches = $tableTranches.DataTable({
      ajax: {
        url: GEICG.url('tranche/apiList'),
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.filiere_code = $('#filter-filiere').val();
          d.niveau_code = $('#filter-niveau').val();
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { data: 'id_tranche', defaultContent: '-' },
        { data: 'code_tranche', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
        { data: 'libelle_filiere', render: function(d, t, r) {
          var fil = d || r.filiere_code || 'Filière';
          var niv = r.libelle_niveau || r.niveau_code || '';
          var ann = r.libelle_annee ? ' (' + r.libelle_annee + ')' : '';
          return '<span style="font-weight:700; color:#0F172A;">' + fil + ' ' + niv + '</span>' +
                 '<div style="font-size:11.5px; color:#64748B;">' + (r.montant_scolarite ? Number(r.montant_scolarite).toLocaleString('fr-FR') + ' FCFA' : '') + ann + '</div>';
        } },
        { data: 'libelle_tranche', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>'; } },
        { data: 'montant_tranche', render: function(d) { 
          return '<span style="font-weight:800; color:#0F172A;">' + (d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA') + '</span>'; 
        } },
        { data: 'date_limite', render: function(d) { return '<span style="font-weight:600; color:#475569;">' + (d || '-') + '</span>'; } },
        { data: 'statut_tranche', width: '90px', className: 'text-center', render: function(d, type, row) {
          var isActif = (d === 'actif');
          var checkedAttr = isActif ? 'checked' : '';
          return '<div style="display:flex; justify-content:center; align-items:center;">' +
                 '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
                 '<input type="checkbox" class="toggle-statut-tranche" data-id="' + row.id_tranche + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                 '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                 '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                 '</span>' +
                 '</label>' +
                 '</div>';
        } },
        { data: null, orderable: false, render: function(d) {
          return '<a href="' + GEICG.url('tranche/edition/' + (d.editId || d.id_tranche)) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
                 '<a href="' + GEICG.url('tranche/details/' + (d.editId || d.id_tranche)) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
        }, className: 'text-end' }
      ],
      language: GEICG.dataTableLanguage,
      drawCallback: function() { if (window.lucide) lucide.createIcons(); }
    });

    function bindAjaxToggle(selector, url, tableRef) {
      $(document).on('change', selector, function() {
        var id = $(this).data('id');
        var isChecked = $(this).is(':checked');
        var $input = $(this);
        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="csrf_token"]').val() || '';

        $.ajax({
          url: url,
          type: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: { id: id, csrf_token: token },
          dataType: 'json',
          success: function(res) {
            if (res.status === 1 || res.success) {
              if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
              tableRef.ajax.reload(null, false);
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
    }

    bindAjaxToggle('.toggle-statut-scolarite', GEICG.url('scolarite/changer'), tableScolarites);
    bindAjaxToggle('.toggle-statut-tranche', GEICG.url('tranche/changer'), tableTranches);

    // Switch Tab Handler
    function switchScolariteTab(targetId) {
      $('.nav-tabs .nav-link').removeClass('active').css({ 'color': '#64748B', 'background': '#F8FAFC' });
      $('.tab-pane').removeClass('show active').hide();

      if (targetId === '#tab-tranches') {
        $('#tab-tranches-btn').addClass('active').css({ 'color': '#1E3A5F', 'background': '#FFFFFF' });
        $('#tab-tranches').addClass('show active').show();
        $('#btn-add-action').attr('href', GEICG.url('tranche/formulaire'));
        $('#btn-add-label').text('Ajouter Tranche / Échéancier');
        tableTranches.columns.adjust().draw();
      } else {
        $('#tab-scolarites-btn').addClass('active').css({ 'color': '#1E3A5F', 'background': '#FFFFFF' });
        $('#tab-scolarites').addClass('show active').show();
        $('#btn-add-action').attr('href', GEICG.url('scolarite/formulaire'));
        $('#btn-add-label').text('Ajouter Tarif de Scolarité');
        tableScolarites.columns.adjust().draw();
      }
      if (window.lucide) lucide.createIcons();
    }

    $(document).on('click', '.nav-tabs .nav-link', function(e) {
      e.preventDefault();
      var target = $(this).attr('data-bs-target');
      switchScolariteTab(target);
    });

    // Default active tab
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'tranches') {
      switchScolariteTab('#tab-tranches');
    } else {
      switchScolariteTab('#tab-scolarites');
    }
  }

  $(document).ready(initScolaritesListModule);

})(window, window.jQuery);
