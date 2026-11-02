/**
 * Module JS Unifié : Enseignants (Liste & Édition)
 * Regroupe la gestion de la liste DataTable des enseignants et le formulaire de création/édition
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/');

  // =========================================================================
  // 1. SECTION : LISTE DES ENSEIGNANTS (Exécutée si #table-enseignants est présent)
  // =========================================================================
  if ($('#table-enseignants').length) {
    var cfg = {};
    try {
      var cfgEl = document.getElementById('enseignants-list-config');
      if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
    } catch (e) {}
    var csrfToken = cfg.csrfToken || '';

    var table = $('#table-enseignants').DataTable({
      ajax: racine + 'enseignant/apiList',
      processing: true,
      autoWidth: false,
      columns: [
        { data: null, width: '50px', render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }},
        { data: 'code_enseignant', width: '100px', render: function(d, type) {
          if (type !== 'display') return d || '';
          return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
        }},
        { data: 'nom_enseignant', width: '220px', render: function(d, type, row) {
          if (type !== 'display') return (d || '') + ' ' + (row.prenom_enseignant || '');
          var nom = (d || '').trim().toUpperCase();
          var prenom = (row.prenom_enseignant || '').trim();
          if (!nom && !prenom) return '<strong style="color:#0F172A;">Enseignant sans nom</strong>';
          return '<div style="white-space: normal; word-break: break-word; line-height: 1.35;">' +
                 '  <div style="font-weight: 800; color: #0F172A; font-size: 13px;">' + nom + '</div>' +
                 (prenom ? '  <div style="font-weight: 600; color: #475569; font-size: 12px; margin-top: 1px;">' + prenom + '</div>' : '') +
                 '</div>';
        }},
        { data: 'sexe_user', width: '60px', className: 'text-center', render: function(d, type, row) {
          var s = (d || row.sexe_enseignant || '').toUpperCase().trim();
          if (type !== 'display') return s;
          if (s === 'F' || s === 'FEMININ' || s.indexOf('FEM') !== -1) {
            return '<span style="background:#FCE7F3; color:#9D174D; border:1px solid #FBCFE8; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800;" title="Féminin">F</span>';
          }
          return '<span style="background:#E0E7FF; color:#3730A3; border:1px solid #C7D2FE; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800;" title="Masculin">M</span>';
        }},
        { data: 'email_enseignant', render: function(d, type, row) {
          if (type !== 'display') return d || row.telephone_enseignant || '';
          var res = '';
          if (d) res += '<div style="font-weight:600; color:#1E3A5F; font-size:13px;">' + d + '</div>';
          if (row.telephone_enseignant) res += '<div style="font-size:12px; color:#64748B;">' + row.telephone_enseignant + '</div>';
          return res || '-';
        }},
        { data: 'type_contrat', render: function(d, type, row) {
          if (type !== 'display') return d || '';
          var badge = d === 'vacataire' 
            ? '<span style="background:#FEF3C7; color:#92400E; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px;">Vacataire</span>'
            : (d === 'prestataire' ? '<span style="background:#F1F5F9; color:#475569; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px;">Prestataire</span>' : '<span style="background:#E0F2FE; color:#0369A1; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px;">Permanent</span>');
          if (row.numero_autorisation) {
            badge += '<div style="font-size:11px; color:#475569; font-weight:600; margin-top:3px;"><i data-lucide="award" style="width:11px;height:11px;display:inline-block;vertical-align:middle;"></i> ' + row.numero_autorisation + '</div>';
          }
          return badge;
        }},
        { data: 'statut_enseignant', width: '80px', className: 'text-center', render: function(d, type, row) {
          var isActif = (d === 'actif');
          var checkedAttr = isActif ? 'checked' : '';
          return '<div style="display:flex; justify-content:center; align-items:center;">' +
                 '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
                 '<input type="checkbox" class="toggle-statut-ens" data-id="' + row.id_enseignant + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                 '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                 '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                 '</span>' +
                 '</label>' +
                 '</div>';
        }},
        { data: null, width: '160px', orderable: false, render: function(d) {
          return '<a href="' + racine + 'enseignant/edition/' + (d.editId || d.id_enseignant) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
                 '<a href="' + racine + 'enseignant/details/' + (d.editId || d.id_enseignant) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Profil</a>';
        }, className: 'text-end' }
      ],
      language: (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : {
        url: racine + 'json/datatables-i18n-fr-FR.json'
      },
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
        try {
          var api = this.api();
          var json = api.ajax.json();
          if (json && json.stats) {
            $('#kpi-total-enseignants').text(json.stats.total || 0);
            $('#kpi-actifs-enseignants').text(json.stats.actifs || 0);
            $('#kpi-permanents-enseignants').text(json.stats.permanents || 0);
            $('#kpi-vacataires-enseignants').text(json.stats.vacataires || 0);
          } else {
            var rows = api.rows().data().toArray();
            var total = rows.length;
            var actifs = 0;
            var permanents = 0;
            var vacataires = 0;

            rows.forEach(function(r) {
              if (r) {
                var st = (r.statut_enseignant || '').toString().toLowerCase().trim();
                var tc = (r.type_contrat || '').toString().toLowerCase().trim();
                if (st === 'actif') actifs++;
                if (tc === 'permanent') permanents++;
                else if (tc === 'vacataire' || tc === 'prestataire') vacataires++;
              }
            });

            $('#kpi-total-enseignants').text(total);
            $('#kpi-actifs-enseignants').text(actifs);
            $('#kpi-permanents-enseignants').text(permanents);
            $('#kpi-vacataires-enseignants').text(vacataires);
          }
        } catch (e) {}
      }
    });

    // Initialisation Select2 des filtres avancés
    $('#filter-statut, #filter-contrat, #filter-matiere, #filter-grade, #filter-sexe').select2({
      width: '100%'
    });

    // Moteur de recherche et de filtrage multi-critères DataTable
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
      if (!settings || !settings.nTable || settings.nTable.id !== 'table-enseignants') {
        return true;
      }

      var filterStatut = ($('#filter-statut').val() || '').toLowerCase().trim();
      var filterContrat = ($('#filter-contrat').val() || '').toLowerCase().trim();
      var filterMatiere = ($('#filter-matiere').val() || '').trim();
      var filterGrade = ($('#filter-grade').val() || '').toLowerCase().trim();
      var filterSexe = ($('#filter-sexe').val() || '').toUpperCase().trim();

      // 1. Filtre par Statut
      if (filterStatut) {
        var rowStatut = (rowData.statut_enseignant || '').toString().toLowerCase().trim();
        if (rowStatut !== filterStatut) return false;
      }

      // 2. Filtre par Contrat
      if (filterContrat) {
        var rowContrat = (rowData.type_contrat || '').toString().toLowerCase().trim();
        if (rowContrat !== filterContrat) return false;
      }

      // 3. Filtre par Matière
      if (filterMatiere) {
        var matCodes = (rowData.matieres_codes || '').toString().split(',');
        if (matCodes.indexOf(filterMatiere) === -1) return false;
      }

      // 4. Filtre par Grade Académique
      if (filterGrade) {
        var rowGrade = (rowData.grade_enseignant || '').toString().toLowerCase().trim();
        if (rowGrade.indexOf(filterGrade) === -1) return false;
      }

      // 5. Filtre par Genre / Sexe
      if (filterSexe) {
        var rowSexe = (rowData.sexe_user || rowData.sexe_enseignant || '').toString().toUpperCase().trim();
        if (filterSexe === 'M' && rowSexe !== 'M' && rowSexe !== 'MASCULIN') return false;
        if (filterSexe === 'F' && rowSexe !== 'F' && rowSexe !== 'FEMININ') return false;
      }

      return true;
    });

    // Déclencheur sur changement des filtres select2
    $('#filter-statut, #filter-contrat, #filter-matiere, #filter-grade, #filter-sexe').on('change', function() {
      table.draw();
    });

    // Réinitialisation globale de tous les filtres
    $('#btn-reset-filters').on('click', function(e) {
      e.preventDefault();
      $('#filter-statut').val('').trigger('change.select2');
      $('#filter-contrat').val('').trigger('change.select2');
      $('#filter-matiere').val('').trigger('change.select2');
      $('#filter-grade').val('').trigger('change.select2');
      $('#filter-sexe').val('').trigger('change.select2');
      $('.kpi-card-filter').css({
        'border-color': '#E2E8F0',
        'box-shadow': '0 1px 3px rgba(0,0,0,0.04)',
        'transform': 'none'
      });
      table.search('').draw();
    });

    // Filtrage rapide au clic sur les cartes statistiques KPI
    $(document).on('click', '.kpi-card-filter', function() {
      var filterVal = $(this).data('filter') || '';
      $('.kpi-card-filter').css({
        'border-color': '#E2E8F0',
        'box-shadow': '0 1px 3px rgba(0,0,0,0.04)',
        'transform': 'none'
      });

      if (filterVal) {
        $(this).css({
          'border-color': '#3B82F6',
          'box-shadow': '0 0 0 2px rgba(59,130,246,0.2)',
          'transform': 'translateY(-2px)'
        });
      }

      if (filterVal === 'actif' || filterVal === 'inactif') {
        $('#filter-statut').val(filterVal).trigger('change.select2');
        $('#filter-contrat').val('').trigger('change.select2');
      } else if (filterVal === 'permanent' || filterVal === 'vacataire' || filterVal === 'prestataire') {
        $('#filter-contrat').val(filterVal).trigger('change.select2');
        $('#filter-statut').val('').trigger('change.select2');
      } else {
        $('#filter-statut').val('').trigger('change.select2');
        $('#filter-contrat').val('').trigger('change.select2');
        $('#filter-matiere').val('').trigger('change.select2');
        $('#filter-grade').val('').trigger('change.select2');
        $('#filter-sexe').val('').trigger('change.select2');
      }
      table.draw();
    });

    // Bascule de statut instantanée via Ajax
    $(document).on('change', '.toggle-statut-ens', function() {
      var id = $(this).data('id');
      var isChecked = $(this).is(':checked');
      var $input = $(this);

      $.ajax({
        url: racine + 'enseignant/changer',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: {
          id: id,
          csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            var msg = res.message || 'Statut mis à jour avec succès';
            if (typeof showToast === 'function') showToast(msg, 'success');
            else if (window.toastr) toastr.success(msg);
            table.ajax.reload(null, false);
          } else {
            var errMsg = res.message || 'Erreur lors du changement de statut';
            if (typeof showToast === 'function') showToast(errMsg, 'error');
            else if (window.toastr) toastr.error(errMsg);
            $input.prop('checked', !isChecked);
          }
        },
        error: function() {
          if (typeof showToast === 'function') showToast('Erreur réseau', 'error');
          else if (window.toastr) toastr.error('Erreur réseau');
          $input.prop('checked', !isChecked);
        }
      });
    });
  }

  // =========================================================================
  // 2. SECTION : FORMULAIRE D'ÉDITION ENSEIGNANT (Exécutée si #form-enseignant est présent)
  // =========================================================================
  if ($('#form-enseignant').length) {
    $('#radio-mode-nouveau').on('change', function() {
      if ($(this).is(':checked')) {
        $('#bloc-user-existant').slideUp(200);
        $('#bloc-user-identity').slideDown(200);
        $('.field-ident').prop('required', true);
        $('#select_user_existant').prop('required', false).val('');
      }
    });

    $('#radio-mode-existant').on('change', function() {
      if ($(this).is(':checked')) {
        $('#bloc-user-identity').slideUp(200);
        $('#bloc-user-existant').slideDown(200);
        $('.field-ident').prop('required', false);
        $('#select_user_existant').prop('required', true);
      }
    });

    // Soumission AJAX du formulaire enseignant (Création & Édition)
    $('#form-enseignant').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $('#btn-submit-enseignant');
      var originalText = $btn.html();

      $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;display:inline-block;animation:spin 1s linear infinite;"></i> Enregistrement...');
      if (window.lucide) lucide.createIcons();

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            var targetUrl = res.redirect || (racine + 'enseignant/list');
            window.location.href = targetUrl;
          } else {
            $btn.prop('disabled', false).html(originalText);
            if (window.lucide) lucide.createIcons();
            var errMsg = res.message || 'Erreur lors de l\'enregistrement.';
            if (typeof showToast === 'function') {
              showToast(errMsg, 'error');
            } else if (window.toastr) {
              toastr.error(errMsg);
            }
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html(originalText);
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur lors de l\'enregistrement';
          try {
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
              var json = JSON.parse(xhr.responseText);
              if (json && json.message) msg = json.message;
            }
          } catch(e) {}
          if (typeof showToast === 'function') {
            showToast(msg, 'error');
          } else if (window.toastr) {
            toastr.error(msg);
          }
        }
      });
    });
  }

  if (window.lucide) lucide.createIcons();
});

