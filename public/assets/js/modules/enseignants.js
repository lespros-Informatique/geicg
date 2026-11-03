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
        { data: 'volume_horaire', width: '110px', className: 'text-center', render: function(d, type, row) {
          var vh = parseFloat(d || 0);
          if (type !== 'display') return vh;
          if (vh > 0) {
            return '<span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-weight: 800; padding: 4px 10px; border-radius: 6px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;" title="Volume horaire planifié">' +
                   '  <i data-lucide="clock" style="width: 13px; height: 13px; color: #3B82F6;"></i> ' + vh + ' h' +
                   '</span>';
          }
          return '<span style="background: #F8FAFC; color: #94A3B8; border: 1px solid #E2E8F0; font-weight: 600; padding: 3px 8px; border-radius: 6px; font-size: 11px;" title="Aucun cours planifié">0 h</span>';
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
        { data: null, width: '130px', orderable: false, render: function(d) {
          var ensName = (((d.nom_enseignant || '') + ' ' + (d.prenom_enseignant || '')).trim()).replace(/"/g, '&quot;');
          return '<div style="display:inline-flex; align-items:center; gap:5px; justify-content:flex-end;">' +
                 '  <button type="button" class="btn btn-sm btn-outline-primary btn-open-prof-programme" data-code="' + (d.code_enseignant || '') + '" data-name="' + ensName + '" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:8px;" title="Programme des cours"><i data-lucide="calendar" style="width:15px;height:15px;"></i></button>' +
                 '  <a href="' + racine + 'enseignant/edition/' + (d.editId || d.id_enseignant) + '" class="btn btn-sm btn-secondary" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:8px;" title="Modifier cet enseignant"><i data-lucide="edit-3" style="width:15px;height:15px;"></i></a>' +
                 '  <a href="' + racine + 'enseignant/details/' + (d.editId || d.id_enseignant) + '" class="btn btn-sm btn-info" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:8px;" title="Profil complet"><i data-lucide="eye" style="width:15px;height:15px;"></i></a>' +
                 '</div>';
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
            $('#kpi-hommes-enseignants').text(json.stats.hommes || 0);
            $('#kpi-femmes-enseignants').text(json.stats.femmes || 0);
            $('#kpi-permanents-enseignants').text(json.stats.permanents || 0);
            $('#kpi-vacataires-enseignants').text(json.stats.vacataires || 0);
          } else {
            var rows = api.rows().data().toArray();
            var total = rows.length;
            var hommes = 0;
            var femmes = 0;
            var permanents = 0;
            var vacataires = 0;

            rows.forEach(function(r) {
              if (r) {
                var sx = (r.sexe_user || r.sexe_enseignant || '').toString().toUpperCase().trim();
                var tc = (r.type_contrat || '').toString().toLowerCase().trim();
                if (sx === 'M' || sx === 'MASCULIN') hommes++;
                else if (sx === 'F' || sx === 'FEMININ') femmes++;
                if (tc === 'permanent') permanents++;
                else if (tc === 'vacataire' || tc === 'prestataire') vacataires++;
              }
            });

            $('#kpi-total-enseignants').text(total);
            $('#kpi-hommes-enseignants').text(hommes);
            $('#kpi-femmes-enseignants').text(femmes);
            $('#kpi-permanents-enseignants').text(permanents);
            $('#kpi-vacataires-enseignants').text(vacataires);
          }
        } catch (e) {}
      }
    });

    // Variable d'état pour le filtre rapide KPI (ex: Actifs)
    var kpiFilterStatut = '';

    // Initialisation Select2 des filtres avancés (Année, Genre, Type de contrat)
    $('#filter-annee, #filter-sexe, #filter-contrat').select2({
      width: '100%'
    });

    // Moteur de recherche et de filtrage multi-critères DataTable
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
      if (!settings || !settings.nTable || settings.nTable.id !== 'table-enseignants') {
        return true;
      }

      var filterAnnee = ($('#filter-annee').val() || '').trim();
      var filterSexe = ($('#filter-sexe').val() || '').toUpperCase().trim();
      var filterContrat = ($('#filter-contrat').val() || '').toLowerCase().trim();

      // 1. Filtre par Année Académique
      if (filterAnnee) {
        var anneesCodes = (rowData.annees_codes || '').toString().split(',');
        if (anneesCodes.indexOf(filterAnnee) === -1) return false;
      }

      // 2. Filtre par Genre / Sexe
      if (filterSexe) {
        var rowSexe = (rowData.sexe_user || rowData.sexe_enseignant || '').toString().toUpperCase().trim();
        if (filterSexe === 'M' && rowSexe !== 'M' && rowSexe !== 'MASCULIN') return false;
        if (filterSexe === 'F' && rowSexe !== 'F' && rowSexe !== 'FEMININ') return false;
      }

      // 3. Filtre par Type de Contrat
      if (filterContrat) {
        var rowContrat = (rowData.type_contrat || '').toString().toLowerCase().trim();
        if (rowContrat !== filterContrat) return false;
      }

      // 4. Filtre rapide par statut via carte KPI (ex: Enseignants Actifs)
      if (kpiFilterStatut) {
        var rowStatut = (rowData.statut_enseignant || '').toString().toLowerCase().trim();
        if (rowStatut !== kpiFilterStatut) return false;
      }

      return true;
    });

    // Synchronisation de l'URL du bouton Imprimer en fonction des filtres actifs
    function updatePrintUrl() {
      var annee = ($('#filter-annee').val() || '').trim();
      var sexe = ($('#filter-sexe').val() || '').trim();
      var contrat = ($('#filter-contrat').val() || '').trim();
      var params = [];
      if (annee) params.push('annee_code=' + encodeURIComponent(annee));
      if (sexe) params.push('sexe=' + encodeURIComponent(sexe));
      if (contrat) params.push('type_contrat=' + encodeURIComponent(contrat));
      var qs = params.length > 0 ? ('?' + params.join('&')) : '';
      $('#btn-print-enseignants').attr('href', racine + 'enseignant/imprimerPdf' + qs);
    }
    updatePrintUrl();

    // Déclencheur sur changement des filtres select2
    $('#filter-annee, #filter-sexe, #filter-contrat').on('change', function() {
      table.draw();
      updatePrintUrl();
    });

    // Réinitialisation globale de tous les filtres
    $('#btn-reset-filters').on('click', function(e) {
      e.preventDefault();
      kpiFilterStatut = '';
      $('#filter-annee').val('').trigger('change.select2');
      $('#filter-sexe').val('').trigger('change.select2');
      $('#filter-contrat').val('').trigger('change.select2');
      $('.kpi-card-filter').css({
        'border-color': '#E2E8F0',
        'box-shadow': '0 1px 3px rgba(0,0,0,0.04)',
        'transform': 'none'
      });
      table.search('').draw();
      updatePrintUrl();
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

      if (filterVal === 'genre') {
        var currentSexe = $('#filter-sexe').val();
        if (!currentSexe) {
          $('#filter-sexe').val('M').trigger('change.select2');
        } else if (currentSexe === 'M') {
          $('#filter-sexe').val('F').trigger('change.select2');
        } else {
          $('#filter-sexe').val('').trigger('change.select2');
        }
      } else if (filterVal === 'permanent' || filterVal === 'vacataire' || filterVal === 'prestataire') {
        $('#filter-contrat').val(filterVal).trigger('change.select2');
      } else {
        $('#filter-annee').val('').trigger('change.select2');
        $('#filter-sexe').val('').trigger('change.select2');
        $('#filter-contrat').val('').trigger('change.select2');
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

    // =========================================================================
    // GESTION DU MODAL : PROGRAMME DES COURS (Aujourd'hui & Autre date)
    // =========================================================================
    function getTodayDateStr() {
      var now = new Date();
      var y = now.getFullYear();
      var m = String(now.getMonth() + 1).padStart(2, '0');
      var d = String(now.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + d;
    }

    function formatTime(t) {
      if (!t) return '';
      return t.substring(0, 5);
    }

    function loadProgrammeCours(dateStr, ensCode) {
      if (!dateStr) dateStr = getTodayDateStr();
      ensCode = (ensCode !== undefined && ensCode !== null) ? ensCode : ($('#select-programme-enseignant').val() || '');

      $('#programme-loading').show();
      $('#programme-table-wrapper').hide();
      $('#programme-empty-state').hide();

      $.ajax({
        url: racine + 'enseignant/apiProgrammeJour',
        type: 'GET',
        data: {
          date: dateStr,
          enseignant_code: ensCode
        },
        dataType: 'json',
        success: function(res) {
          $('#programme-loading').hide();
          if (res.status === 1) {
            // Met à jour les libellés de date et badge
            $('#badge-programme-jour').text(res.is_today ? "Aujourd'hui" : res.jour_libelle);
            $('#badge-programme-jour').css('background', res.is_today ? '#10B981' : '#3B82F6');
            $('#modal-programme-sub-text').text(res.date_formatee + (ensCode ? ' • Filtré par enseignant' : ' • Planning global'));

            // Met à jour les KPI
            var stats = res.stats || {};
            $('#kpi-programme-cours').text(stats.total_cours || 0);
            $('#kpi-programme-heures').text((stats.total_heures || 0) + ' h');
            $('#kpi-programme-profs').text(stats.nb_profs || 0);
            $('#kpi-programme-classes').text(stats.nb_classes || 0);

            var items = res.data || [];
            if (items.length > 0) {
              var html = '';
              items.forEach(function(item) {
                var creneau = formatTime(item.heure_debut) + ' - ' + formatTime(item.heure_fin);
                var duree = (item.duree || 0) + ' h';
                var profNom = ((item.nom_enseignant || '') + ' ' + (item.prenom_enseignant || '')).trim() || 'Non assigné';
                var profContact = item.telephone_enseignant ? '<div style="font-size:11px;color:#64748B;"><i data-lucide="phone" style="width:11px;height:11px;display:inline-block;vertical-align:middle;"></i> ' + item.telephone_enseignant + '</div>' : '';

                html += '<tr style="border-bottom: 1px solid #F1F5F9;">' +
                        '  <td style="padding: 12px;">' +
                        '    <div style="font-weight: 800; color: #1E3A5F; font-size: 13px;">' + creneau + '</div>' +
                        '    <span style="background:#EFF6FF;color:#1D4ED8;font-size:11px;font-weight:700;padding:2px 6px;border-radius:4px;">' + duree + '</span>' +
                        '  </td>' +
                        '  <td style="padding: 12px;">' +
                        '    <strong style="color: #0F172A; font-size: 13px;">' + (item.libelle_matiere || '-') + '</strong>' +
                        '  </td>' +
                        '  <td style="padding: 12px;">' +
                        '    <span style="background:#F8FAFC;border:1px solid #CBD5E1;color:#1E293B;padding:4px 8px;border-radius:6px;font-weight:700;font-size:12px;">' +
                        '      ' + (item.libelle_classe || '-') +
                        '    </span>' +
                        '  </td>' +
                        '  <td style="padding: 12px;">' +
                        '    <div style="font-weight:700;color:#0F172A;font-size:13px;"><i data-lucide="door-closed" style="width:13px;height:13px;display:inline-block;vertical-align:middle;color:#3B82F6;"></i> ' + (item.libelle_salle || 'Non définie') + '</div>' +
                        '  </td>' +
                        '  <td style="padding: 12px;">' +
                        '    <div style="font-weight:800;color:#0F172A;font-size:13px;">' + profNom + '</div>' +
                        '    ' + profContact +
                        '  </td>' +
                        '</tr>';
              });
              $('#programme-cours-tbody').html(html);
              $('#programme-table-wrapper').show();
              if (window.lucide) lucide.createIcons();
            } else {
              $('#programme-empty-msg').text('Aucun cours n\'est programmé pour le ' + res.date_formatee + (ensCode ? ' avec cet enseignant.' : '.'));
              $('#programme-empty-state').show();
              if (window.lucide) lucide.createIcons();
            }
          }
        },
        error: function() {
          $('#programme-loading').hide();
          $('#programme-empty-msg').text('Erreur lors du chargement du programme.');
          $('#programme-empty-state').show();
        }
      });
    }

    function openProgrammeModal(preselectedDate, preselectedEnsCode) {
      var dateToUse = preselectedDate || getTodayDateStr();
      $('#input-programme-date').val(dateToUse);
      if (preselectedEnsCode !== undefined) {
        $('#select-programme-enseignant').val(preselectedEnsCode);
      }
      $('#modal-programme-cours').css('display', 'flex');
      loadProgrammeCours(dateToUse, $('#select-programme-enseignant').val());
      if (window.lucide) lucide.createIcons();
    }

    function closeProgrammeModal() {
      $('#modal-programme-cours').hide();
    }

    // Clic bouton d'ouverture global
    $('#btn-programme-cours').on('click', function(e) {
      e.preventDefault();
      openProgrammeModal(getTodayDateStr(), '');
    });

    // Clic bouton d'action par enseignant dans le tableau
    $(document).on('click', '.btn-open-prof-programme', function(e) {
      e.preventDefault();
      var ensCode = $(this).data('code') || '';
      openProgrammeModal(getTodayDateStr(), ensCode);
    });

    // Fermeture modal
    $(document).on('click', '.btn-close-programme-modal', function(e) {
      e.preventDefault();
      closeProgrammeModal();
    });

    // Fermeture si clic à l'extérieur
    $('#modal-programme-cours').on('click', function(e) {
      if (e.target === this) {
        closeProgrammeModal();
      }
    });

    // Changement de date via l'input
    $('#input-programme-date').on('change', function() {
      var d = $(this).val();
      loadProgrammeCours(d, $('#select-programme-enseignant').val());
    });

    // Raccourci bouton Aujourd'hui
    $('#btn-programme-today').on('click', function() {
      var today = getTodayDateStr();
      $('#input-programme-date').val(today);
      loadProgrammeCours(today, $('#select-programme-enseignant').val());
    });

    // Jour précédent
    $('#btn-programme-prev-day').on('click', function() {
      var curVal = $('#input-programme-date').val();
      var cur = curVal ? new Date(curVal + 'T00:00:00') : new Date();
      cur.setDate(cur.getDate() - 1);
      var y = cur.getFullYear();
      var m = String(cur.getMonth() + 1).padStart(2, '0');
      var d = String(cur.getDate()).padStart(2, '0');
      var dateStr = y + '-' + m + '-' + d;
      $('#input-programme-date').val(dateStr);
      loadProgrammeCours(dateStr, $('#select-programme-enseignant').val());
    });

    // Jour suivant
    $('#btn-programme-next-day').on('click', function() {
      var curVal = $('#input-programme-date').val();
      var cur = curVal ? new Date(curVal + 'T00:00:00') : new Date();
      cur.setDate(cur.getDate() + 1);
      var y = cur.getFullYear();
      var m = String(cur.getMonth() + 1).padStart(2, '0');
      var d = String(cur.getDate()).padStart(2, '0');
      var dateStr = y + '-' + m + '-' + d;
      $('#input-programme-date').val(dateStr);
      loadProgrammeCours(dateStr, $('#select-programme-enseignant').val());
    });

    // Filtre enseignant
    $('#select-programme-enseignant').on('change', function() {
      loadProgrammeCours($('#input-programme-date').val(), $(this).val());
    });

    // Impression de la journée depuis le modal
    $('#btn-print-programme-modal').on('click', function() {
      var title = $('#modal-programme-sub-text').text();
      var rowsHtml = $('#programme-cours-tbody').html();
      if (!rowsHtml || rowsHtml.trim() === '') {
        if (typeof showToast === 'function') showToast('Aucun cours à imprimer pour cette journée', 'warning');
        return;
      }
      var cfg = window.ETABLISSEMENT_CONFIG || {};
      var etabNom = cfg.nom_etablissement || 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION';
      var etabSigle = cfg.sigle_etablissement || 'GROUPE EICG';
      var dirTitre = cfg.titre_direction_pedagogique || 'La Direction des Études & Pédagogie';
      var contacts = cfg.contacts_formatted || '27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99';
      var piedPage = cfg.texte_pied_page || 'Document officiel généré par le Système GEICG';

      var win = window.open('', '_blank');
      win.document.write('<!DOCTYPE html><html><head><title>Programme des cours - ' + etabSigle + '</title>');
      win.document.write('<style>');
      win.document.write('body{font-family:Arial,sans-serif;font-size:12px;margin:25px;color:#1E293B;}');
      win.document.write('.header{margin-bottom:15px;border-bottom:2px solid #1E3A5F;padding-bottom:10px;}');
      win.document.write('.inst-nom{font-size:13px;font-weight:bold;color:#990000;text-transform:uppercase;}');
      win.document.write('.title{font-size:16px;font-weight:bold;color:#1E3A5F;text-transform:uppercase;margin-top:4px;}');
      win.document.write('.sub{font-size:11.5px;color:#64748B;margin-top:3px;}');
      win.document.write('table{width:100%;border-collapse:collapse;margin-top:10px;}');
      win.document.write('th,td{border:1px solid #CBD5E1;padding:8px 10px;text-align:left;}');
      win.document.write('th{background:#1E3A5F;color:#FFFFFF;font-size:11px;text-transform:uppercase;}');
      win.document.write('tr:nth-child(even){background:#F8FAFC;}');
      win.document.write('.footer{margin-top:20px;padding-top:10px;border-top:1px solid #CBD5E1;font-size:10px;color:#64748B;display:flex;justify-content:space-between;}');
      win.document.write('</style></head><body>');
      win.document.write('<div class="header">');
      win.document.write('  <div class="inst-nom">' + etabNom + ' &bull; ' + dirTitre + '</div>');
      win.document.write('  <div class="title">' + etabSigle + ' &bull; Programme Officiel des Cours & Séances</div>');
      win.document.write('  <div class="sub">' + title + ' &bull; Date d\'impression : ' + (new Date()).toLocaleDateString('fr-FR') + '</div>');
      win.document.write('</div>');
      win.document.write('<table><thead><tr><th>Créneau</th><th>Matière</th><th>Classe</th><th>Salle</th><th>Enseignant</th></tr></thead><tbody>');
      win.document.write(rowsHtml);
      win.document.write('</tbody></table>');
      win.document.write('<div class="footer"><span>' + piedPage + '</span><span>Contacts : ' + contacts + '</span></div>');
      win.document.write('<script>window.onload=function(){window.print();};<\/script>');
      win.document.write('</body></html>');
      win.document.close();
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

