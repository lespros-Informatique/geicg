/**
 * Module JS Unifié : Notes, Évaluations & Bulletins (Registres, Saisies, Bulletins & PV de Classe)
 * GEICG - Application Web
 */
var isFormDirty = false;
var isInitialLoading = true;

function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/');

  // =========================================================================
  // 1. VUE : REGISTRE DES NOTES (Execute si #table-notes est present)
  // =========================================================================
  if ($('#table-notes').length) {

  var cfg = {};
  try {
    var cfgEl = document.getElementById('notes-list-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var csrfToken = cfg.csrfToken || '';
  var canEnterNotes = cfg.canEnterNotes !== false;
  var canViewNotes = cfg.canViewNotes !== false;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe, #filter-type-eval, #filter-matiere, #filter-semestre, #filter-compo').select2({ width: '100%' });
  }

  function updatePrintRegistreUrl() {
    var params = [];
    var annee = $('#filter-annee').val();
    var niv = $('#filter-niveau').val();
    var cls = $('#filter-classe').val();
    var typ = $('#filter-type-eval').val();
    var mat = $('#filter-matiere').val();
    var sem = $('#filter-semestre').val();
    var compo = $('#filter-compo').val();

    if (annee) params.push('annee_code=' + encodeURIComponent(annee));
    if (niv) params.push('niveau_code=' + encodeURIComponent(niv));
    if (cls) params.push('classe_code=' + encodeURIComponent(cls));
    if (typ) params.push('type_composition=' + encodeURIComponent(typ));
    if (mat) params.push('matiere_code=' + encodeURIComponent(mat));
    if (sem) params.push('semestre_code=' + encodeURIComponent(sem));
    if (compo) params.push('composition_code=' + encodeURIComponent(compo));

    var url = window.RACINE + 'note/imprimerRegistrePdf' + (params.length ? ('?' + params.join('&')) : '');
    $('#btn-print-registre-notes').attr('href', url);
  }
  updatePrintRegistreUrl();

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
    updatePrintRegistreUrl();
    table.ajax.reload();
  });

  // Filtrage en cascade Type Éval -> Libellé Compo
  $('#filter-type-eval').on('change', function() {
    var typ = $(this).val();
    $('#filter-compo option').each(function() {
      var optType = $(this).data('type');
      if (!typ || !optType || optType === typ || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#filter-compo option:selected').prop('disabled')) {
      $('#filter-compo').val('').trigger('change.select2');
    } else {
      $('#filter-compo').select2({ width: '100%' });
    }
    updatePrintRegistreUrl();
    table.ajax.reload();
  });

  $('#filter-classe, #filter-annee, #filter-matiere, #filter-semestre, #filter-compo').on('change', function() {
    var cls = $('#filter-classe').val();
    var semStatut = ($('#filter-semestre option:selected').data('statut') || '').toString().toLowerCase();
    var compoStatut = ($('#filter-compo option:selected').data('statut') || '').toString().toLowerCase();

    var isSemestreBlocked = (semStatut === 'cloture');
    var isCompoBlocked = (compoStatut === 'cloture' || compoStatut === 'termine' || compoStatut === 'annule');
    var isBlocked = isSemestreBlocked || isCompoBlocked;

    if (isBlocked) {
      var lockMsg = isSemestreBlocked 
        ? 'Le semestre sélectionné est clôturé : la saisie des notes est désactivée.' 
        : (compoStatut === 'annule' 
            ? 'L\'évaluation sélectionnée est annulée : la saisie des notes est désactivée.' 
            : 'L\'évaluation sélectionnée est clôturée : la saisie des notes est désactivée.');

      $('#btn-saisie-classe')
        .addClass('disabled')
        .attr('href', 'javascript:void(0);')
        .attr('title', lockMsg)
        .css({
          'background': '#CBD5E1',
          'border': '1px solid #CBD5E1',
          'color': '#64748B',
          'cursor': 'not-allowed',
          'pointer-events': 'none',
          'box-shadow': 'none'
        })
        .html('<i data-lucide="lock" style="width: 18px; height: 18px;"></i> Saisie Verrouillée');
    } else {
      var targetUrl = cls ? (window.RACINE + 'note/saisieClasse?classe_code=' + encodeURIComponent(cls)) : (window.RACINE + 'note/saisieClasse');
      $('#btn-saisie-classe')
        .removeClass('disabled')
        .attr('href', targetUrl)
        .removeAttr('title')
        .css({
          'background': 'linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%)',
          'border': 'none',
          'color': '#FFFFFF',
          'cursor': 'pointer',
          'pointer-events': 'auto',
          'box-shadow': '0 4px 12px rgba(30,58,95,0.25)'
        })
        .html('<i data-lucide="edit-3" style="width: 18px; height: 18px;"></i> Saisie des Notes');
    }
    if (window.lucide) lucide.createIcons();
    updatePrintRegistreUrl();
    table.ajax.reload();
  });

  var table = $('#table-notes').DataTable({
    ajax: {
      url: window.RACINE + 'note/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.type_composition = $('#filter-type-eval').val();
        d.matiere_code = $('#filter-matiere').val();
        d.semestre_code = $('#filter-semestre').val();
        d.composition_code = $('#filter-compo').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { 
        data: null, 
        width: '45px', 
        render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }
      },
      { 
        data: 'libelle_classe', 
        defaultContent: '',
        render: function(d, t, r) {
          var cls = d || r.code_classe || '-';
          var niv = r.libelle_niveau ? '<div style="font-size:11px; color:#64748B; margin-top:2px;">' + r.libelle_niveau + '</div>' : '';
          return '<div><strong style="color:#0F172A; font-size:13px;">' + cls + '</strong>' + niv + '</div>';
        }
      },
      { 
        data: 'libelle_composition', 
        defaultContent: '',
        render: function(d, t, r) {
          var compoNom = d || (r && r.code_composition) || '-';
          var cpStatut = (r && r.statut_composition ? String(r.statut_composition).toLowerCase() : '');
          var statusBadge = '';
          if (cpStatut === 'annule') {
            statusBadge = '<span class="badge" style="background:#FEE2E2; color:#B91C1C; font-size:10px; font-weight:800; padding:2px 5px; border-radius:4px; margin-left:6px;" title="Évaluation annulée">Annulée</span>';
          } else if (cpStatut === 'cloture' || cpStatut === 'termine') {
            statusBadge = '<span class="badge" style="background:#F1F5F9; color:#475569; font-size:10px; font-weight:800; padding:2px 5px; border-radius:4px; margin-left:6px;" title="Évaluation clôturée / terminée">Clôturée</span>';
          }
          return '<div style="display:flex; align-items:center; flex-wrap:wrap; gap:4px;"><strong style="color:#0F172A; font-size:13px;">' + compoNom + '</strong>' + statusBadge + '</div>';
        }
      },
      { 
        data: 'type_composition', 
        defaultContent: 'EXAMEN',
        className: 'text-center',
        render: function(d) {
          var typ = d || 'EXAMEN';
          var badgeStyle = 'background:#EFF6FF; color:#1D4ED8;';
          var badgeText = 'EXAMEN';
          if (typ === 'DEVOIR') {
            badgeStyle = 'background:#FEF3C7; color:#B45309;';
            badgeText = 'DEVOIR';
          } else if (typ === 'INTERROGATION') {
            badgeStyle = 'background:#DCFCE7; color:#15803D;';
            badgeText = 'INTERRO';
          } else if (typ !== 'EXAMEN') {
            badgeStyle = 'background:#F1F5F9; color:#475569;';
            badgeText = typ;
          }
          return '<span class="badge" style="font-size:11px; font-weight:800; ' + badgeStyle + ' padding:4px 9px; border-radius:6px; display:inline-block;">' + badgeText + '</span>';
        }
      },
      { 
        data: 'libelle_matiere', 
        defaultContent: '-', 
        render: function(d) {
          return '<span style="font-size:13px; color:#334155; font-weight:700;">' + (d || '-') + '</span>';
        } 
      },
      { 
        data: 'libelle_semestre', 
        defaultContent: '-',
        render: function(d, t, r) {
          var semNom = d || '-';
          var isCloture = (r && r.statut_semestre ? String(r.statut_semestre).toLowerCase() === 'cloture' : false);
          if (isCloture) {
            return '<div style="display:inline-flex; align-items:center; gap:6px;">' +
                     '<span style="font-size:13px; color:#64748B; font-weight:700;">' + semNom + '</span>' +
                     '<span class="badge" style="background:#FEE2E2; color:#B91C1C; font-size:10px; font-weight:800; padding:2px 6px; border-radius:4px;" title="Semestre clôturé">Clôturé</span>' +
                   '</div>';
          }
          return '<span style="font-size:13px; color:#0284C7; font-weight:700;">' + semNom + '</span>';
        }
      },
      { 
        data: 'coefficient', 
        defaultContent: '1.00',
        className: 'text-center',
        width: '70px',
        render: function(d) {
          var val = d ? parseFloat(d).toFixed(2) : '1.00';
          return '<span class="badge" style="background:#F1F5F9; color:#334155; font-size:12px; font-weight:700; padding:4px 8px; border-radius:6px;">' + val + '</span>';
        }
      },
      { 
        data: 'date_enregistrement', 
        defaultContent: '',
        className: 'text-center',
        width: '115px',
        render: function(d, type, row) { 
          var dateStr = d || (row ? (row.date_enregistrement_note || row.date_composition || row.created_at_composition) : null);
          if (!dateStr || dateStr === '0000-00-00 00:00:00' || dateStr === '0000-00-00') {
            return '<span style="color:#94A3B8;">-</span>';
          }
          var cleanDate = dateStr.split(' ')[0];
          var parts = cleanDate.split('-');
          if (parts.length === 3) {
            return '<span style="font-weight:700; color:#1E3A5F; font-size:12.5px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="calendar" style="width:13px;height:13px;color:#64748B;"></i> ' + parts[2] + '/' + parts[1] + '/' + parts[0] + '</span>';
          }
          return '<span style="font-weight:700; color:#1E3A5F; font-size:12.5px;">' + cleanDate + '</span>';
        } 
      },
      { 
        data: 'nb_notes', 
        width: '130px', 
        className: 'text-center',
        render: function(d, type, row) {
          var nb = parseInt(d || 0, 10);
          var tot = parseInt(row.total_etudiants || 0, 10);
          if (tot === 0 && nb === 0) {
            return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-weight:700; padding:4px 8px; border-radius:6px; font-size:11px;">0 inscrit</span>';
          }
          if (tot > 0 && nb >= tot) {
            return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:4px 10px; border-radius:6px; font-size:12px;">' + nb + ' / ' + tot + ' (Complet)</span>';
          }
          if (nb > 0) {
            return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-weight:800; padding:4px 10px; border-radius:6px; font-size:12px;">' + nb + ' / ' + tot + '</span>';
          }
          return '<span class="badge" style="background:#FEE2E2; color:#B91C1C; font-weight:800; padding:4px 10px; border-radius:6px; font-size:12px;">0 / ' + tot + ' (Non noté)</span>';
        }
      },
      { 
        data: 'moyenne_classe', 
        width: '120px', 
        className: 'text-center', 
        render: function(d) {
          if (d !== null && d !== undefined && d !== '') {
            var val = parseFloat(d);
            var col = val >= 10 ? '#15803D' : '#DC2626';
            return '<strong style="font-size:14px; font-weight:800; color:' + col + ';">' + val.toFixed(2) + ' / 20</strong>';
          }
          return '<span style="color:#94A3B8; font-size:12px; font-style:italic;">En attente</span>';
        }
      },
      { 
        data: null, 
        width: '130px', 
        orderable: false, 
        className: 'text-end',
        render: function(d) {
          if (!d) return '';
          var saisieUrl = window.RACINE + 'note/saisieClasse?classe_code=' + encodeURIComponent(d.code_classe || '')
                        + '&composition_code=' + encodeURIComponent(d.code_composition || '')
                        + '&semestre_code=' + encodeURIComponent(d.semestre_code || '')
                        + '&matiere_code=' + encodeURIComponent(d.matiere_code || '')
                        + '&type_evaluation_code=' + encodeURIComponent(d.type_composition || '');
          
          var printUrl = window.RACINE + 'note/imprimerPdf?classe_code=' + encodeURIComponent(d.code_classe || '')
                       + '&composition_code=' + encodeURIComponent(d.code_composition || '')
                       + '&matiere_code=' + encodeURIComponent(d.matiere_code || '');
          
          var actionsHtml = '<div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:nowrap; align-items:center;">';
          if (canViewNotes) {
            actionsHtml += '<button type="button" class="btn btn-sm btn-details-session" data-classe="' + (d.code_classe || '') + '" data-compo="' + (d.code_composition || '') + '" data-matiere="' + (d.matiere_code || '') + '" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; cursor:pointer;" title="Consulter les détails">' +
                             '<i data-lucide="eye" style="width:16px; height:16px;"></i>' +
                           '</button>';
          }
          if (canEnterNotes) {
            var isSemestreCloture = (d.statut_semestre ? String(d.statut_semestre).toLowerCase() === 'cloture' : false);
            var compoStatut = (d.statut_composition ? String(d.statut_composition).toLowerCase() : '');
            var isCompoClotureOuAnnule = (compoStatut === 'cloture' || compoStatut === 'termine' || compoStatut === 'annule');
            var isLocked = isSemestreCloture || isCompoClotureOuAnnule;

            if (isLocked) {
              var lockTitle = isSemestreCloture 
                ? 'Semestre clôturé (Saisie et modification désactivées)' 
                : (compoStatut === 'annule' 
                    ? 'Évaluation annulée (Saisie et modification désactivées)' 
                    : 'Évaluation clôturée (Saisie et modification désactivées)');

              actionsHtml += '<button type="button" class="btn btn-sm" disabled style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#F1F5F9; color:#94A3B8; border:1px solid #CBD5E1; border-radius:6px; cursor:not-allowed;" title="' + lockTitle + '">' +
                               '<i data-lucide="lock" style="width:16px; height:16px;"></i>' +
                             '</button>';
            } else {
              actionsHtml += '<a href="' + saisieUrl + '" class="btn btn-sm" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#16A34A; color:#FFFFFF; border:none; border-radius:6px; text-decoration:none; cursor:pointer;" title="Saisir / Modifier les notes">' +
                               '<i data-lucide="edit-3" style="width:16px; height:16px;"></i>' +
                             '</a>';
            }
          }
          if (canViewNotes) {
            actionsHtml += '<a href="' + printUrl + '" target="_blank" class="btn btn-sm" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#F8FAFC; color:#0F172A; border:1px solid #CBD5E1; border-radius:6px; text-decoration:none; cursor:pointer;" title="Imprimer la fiche de notes (PDF)">' +
                             '<i data-lucide="printer" style="width:16px; height:16px;"></i>' +
                           '</a>';
          }
          actionsHtml += '</div>';
          return actionsHtml;
        }
      }
    ],
    language: { url: window.RACINE + 'public/json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons();
      try {
        var api = this.api();
        var rows = api.rows({ search: 'applied' }).data().toArray();
        
        var compoMap = {};
        var complets = 0;

        rows.forEach(function(r, idx) {
          if (r) {
            var compoKey = r.code_composition || (r.id_composition ? 'id_' + r.id_composition : 'row_' + idx);
            if (!compoMap[compoKey]) {
              compoMap[compoKey] = {
                type: r.type_composition || 'EXAMEN'
              };
            }
            var nb = parseInt(r.nb_notes || 0, 10);
            var tot = parseInt(r.total_etudiants || 0, 10);
            if (tot > 0 && nb >= tot) complets++;
          }
        });

        var examens = 0;
        var controles = 0;
        Object.keys(compoMap).forEach(function(k) {
          if (compoMap[k].type === 'EXAMEN') {
            examens++;
          } else {
            controles++;
          }
        });

        var total = Object.keys(compoMap).length;

        $('#kpi-total-sessions').text(total);
        $('#kpi-total-examens').text(examens);
        $('#kpi-total-controles').text(controles);
        $('#kpi-total-complets').text(complets);
      } catch (e) {}
    }
  });

  // GESTION DU CLIC SUR LE BOUTON DETAILS (AFFICHE LA MODALE)
  $(document).on('click', '.btn-details-session', function() {
    var classeCode = $(this).data('classe');
    var compoCode = $(this).data('compo');
    var matiereCode = $(this).data('matiere') || '';
    if (!classeCode || !compoCode) return;

    var anneeCode = $('#filter-annee').val() || '';

    $('#modal-loading-spinner').show();
    $('#modal-details-content').hide();
    $('#md-students-list').empty();
    $('#modal-details-notes').css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'note/apiDetailsClasse',
      type: 'GET',
      data: { 
        classe_code: classeCode, 
        composition_code: compoCode, 
        matiere_code: matiereCode,
        annee_code: anneeCode
      },
      dataType: 'json',
      success: function(res) {
        $('#modal-loading-spinner').hide();
        if (res.status === 1 && res.data) {
          var d = res.data;
          var info = d.info || {};
          var stats = d.stats || {};
          var students = d.students || [];

          // Header
          var compoLibelle = info.libelle_composition || 'Évaluation';
          var typeEval = info.type_composition || 'EXAMEN';
          var badgeTypeStyle = typeEval === 'EXAMEN' 
            ? 'background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE;' 
            : (typeEval === 'DEVOIR' ? 'background:#FEF3C7; color:#B45309; border:1px solid #FDE68A;' : 'background:#F1F5F9; color:#475569; border:1px solid #E2E8F0;');
          
          var compoStatut = (info.statut_composition ? String(info.statut_composition).toLowerCase() : '');
          var isCompoClotureOuAnnule = (compoStatut === 'cloture' || compoStatut === 'termine' || compoStatut === 'annule');

          var badgeCompoStatus = '';
          if (compoStatut === 'annule') {
            badgeCompoStatus = ' <span style="font-size:11px; font-weight:800; padding:2px 8px; border-radius:5px; text-transform:uppercase; background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA;">Annulée</span>';
          } else if (compoStatut === 'cloture' || compoStatut === 'termine') {
            badgeCompoStatus = ' <span style="font-size:11px; font-weight:800; padding:2px 8px; border-radius:5px; text-transform:uppercase; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1;">Clôturée</span>';
          }

          $('#modal-compo-title').html(
            escapeHtml(compoLibelle) + ' ' +
            '<span style="font-size:11px; font-weight:800; padding:2px 8px; border-radius:5px; text-transform:uppercase; ' + badgeTypeStyle + '">' + escapeHtml(typeEval) + '</span>' +
            badgeCompoStatus
          );

          var isSemestreCloture = (info.statut_semestre ? String(info.statut_semestre).toLowerCase() === 'cloture' : false);
          var badgeSemestre = '<span style="background:#F0FDF4; color:#15803D; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="calendar" style="width:13px;height:13px;color:#22C55E;"></i> ' + escapeHtml(info.libelle_semestre || '-') + '</span>';
          if (isSemestreCloture) {
            badgeSemestre = '<span style="background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:800; display:inline-flex; align-items:center; gap:4px;" title="Ce semestre est clôturé"><i data-lucide="lock" style="width:13px;height:13px;"></i> ' + escapeHtml(info.libelle_semestre || '-') + ' (Clôturé)</span>';
          }

          $('#modal-compo-subtitle').html(
            '<span style="background:#F1F5F9; color:#334155; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="graduation-cap" style="width:13px;height:13px;color:#64748B;"></i> Classe : ' + escapeHtml(info.libelle_classe || '-') + '</span>' +
            '<span style="background:#EEF2FF; color:#4338CA; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="book-open" style="width:13px;height:13px;color:#6366F1;"></i> Matière : ' + escapeHtml(info.libelle_matiere || 'Session globale') + '</span>' +
            badgeSemestre +
            '<span style="background:#FEF3C7; color:#B45309; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="scale" style="width:13px;height:13px;color:#F59E0B;"></i> Coef : ' + parseFloat(info.coefficient || 1.00).toFixed(2) + '</span>'
          );

          // KPIs
          var totalInscrits = parseInt(stats.total_etudiants || 0, 10);
          var nbNotes = parseInt(stats.nb_notes || 0, 10);
          $('#md-total-etudiants').text(totalInscrits);
          $('#md-nb-notes').text(nbNotes + ' / ' + totalInscrits);

          var pct = totalInscrits > 0 ? Math.round((nbNotes / totalInscrits) * 100) : 0;
          $('#md-progression-text').text(pct + '% complété');

          if (stats.moyenne_classe !== null && stats.moyenne_classe !== undefined) {
            var moy = parseFloat(stats.moyenne_classe);
            var colMoy = moy >= 10 ? '#059669' : '#DC2626';
            $('#md-moyenne-classe').html('<span style="color:' + colMoy + ';">' + moy.toFixed(2) + '</span> <span style="font-size:13px; font-weight:700; color:#64748B;">/ 20</span>');
          } else {
            $('#md-moyenne-classe').text('-');
          }
          
          if (nbNotes > 0) {
            var tx = parseFloat(stats.taux_reussite || 0);
            var colTx = tx >= 50 ? '#D97706' : '#DC2626';
            $('#md-taux-reussite').html('<span style="color:' + colTx + ';">' + tx.toFixed(1) + ' %</span>');
          } else {
            $('#md-taux-reussite').text('-');
          }

          // Tableau des étudiants
          var rowsHtml = '';
          if (students.length === 0) {
            rowsHtml = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#64748B;">Aucun étudiant inscrit dans cette classe.</td></tr>';
          } else {
            students.forEach(function(st, idx) {
              var noteHtml = '-';
              if (st.valeur_note !== null && st.valeur_note !== '') {
                var v = parseFloat(st.valeur_note);
                var color = v >= 10 ? '#15803D' : '#DC2626';
                noteHtml = '<strong style="color:' + color + '; font-size:14px;">' + v.toFixed(2) + '</strong>';
              } else {
                noteHtml = '<span class="badge" style="background:#F1F5F9; color:#64748B;">Non noté</span>';
              }

              var rangHtml = '<strong style="color:#1E3A5F; font-size:12px;">' + (st.rang || '-') + '</strong>';

              var mentionBadge = '<span style="font-size:11px; color:#64748B;">' + (st.mention || '-') + '</span>';
              if (st.mention === 'Très Bien' || st.mention === 'Bien') {
                mentionBadge = '<span class="badge" style="background:#DCFCE7; color:#15803D;">' + st.mention + '</span>';
              } else if (st.mention === 'Assez Bien' || st.mention === 'Passable') {
                mentionBadge = '<span class="badge" style="background:#EFF6FF; color:#1D4ED8;">' + st.mention + '</span>';
              } else if (st.mention === 'Insuffisant' || st.mention === 'Très Insuffisant') {
                mentionBadge = '<span class="badge" style="background:#FEE2E2; color:#B91C1C;">' + st.mention + '</span>';
              }

              rowsHtml += '<tr style="border-bottom:1px solid #F1F5F9;">' +
                            '<td style="padding:10px 14px; font-weight:700; color:#64748B;">' + (idx + 1) + '</td>' +
                            '<td style="padding:10px 14px; font-family:monospace; font-size:12px; color:#1E3A5F; font-weight:700;">' + (st.matricule_etudiant || '-') + '</td>' +
                            '<td style="padding:10px 14px; font-weight:700; color:#0F172A;">' + (st.nom_etudiant || '-') + '</td>' +
                            '<td style="padding:10px 14px; text-align:center;">' + noteHtml + '</td>' +
                            '<td style="padding:10px 14px; text-align:center;">' + rangHtml + '</td>' +
                            '<td style="padding:10px 14px; text-align:center;">' + mentionBadge + '</td>' +
                            '<td style="padding:10px 14px; color:#64748B; font-size:12px;">' + (st.observations || '-') + '</td>' +
                          '</tr>';
            });
          }
          $('#md-students-list').html(rowsHtml);

          // Bouton Saisir / Modifier
          var saisieUrl = window.RACINE + 'note/saisieClasse?classe_code=' + encodeURIComponent(info.classe_code || classeCode)
                        + '&composition_code=' + encodeURIComponent(info.code_composition || compoCode)
                        + '&semestre_code=' + encodeURIComponent(info.semestre_code || '')
                        + '&matiere_code=' + encodeURIComponent(info.matiere_code || '')
                        + '&type_evaluation_code=' + encodeURIComponent(info.type_composition || '');

          var isLocked = isSemestreCloture || isCompoClotureOuAnnule;

          if (isLocked) {
            var lockTitle = isSemestreCloture 
              ? 'Le semestre est clôturé : la saisie et la modification des notes sont verrouillées.' 
              : (compoStatut === 'annule' 
                  ? 'Cette évaluation est annulée : la saisie et la modification des notes sont désactivées.' 
                  : 'Cette évaluation est clôturée / terminée : la saisie et la modification des notes sont verrouillées.');
            
            var lockLabel = isSemestreCloture 
              ? 'Semestre Clôturé' 
              : (compoStatut === 'annule' ? 'Évaluation Annulée' : 'Évaluation Clôturée');

            $('#md-btn-go-saisie')
              .attr('href', 'javascript:void(0);')
              .addClass('disabled')
              .attr('title', lockTitle)
              .css({
                'background': '#CBD5E1',
                'border-color': '#CBD5E1',
                'color': '#64748B',
                'cursor': 'not-allowed',
                'pointer-events': 'none',
                'box-shadow': 'none'
              })
              .html('<i data-lucide="lock" style="width: 16px; height: 16px;"></i> ' + lockLabel);
          } else {
            $('#md-btn-go-saisie')
              .attr('href', saisieUrl)
              .removeClass('disabled')
              .removeAttr('title')
              .css({
                'background': '#1E3A5F',
                'border-color': '#1E3A5F',
                'color': '#FFFFFF',
                'cursor': 'pointer',
                'pointer-events': 'auto',
                'box-shadow': 'none'
              })
              .html('<i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Saisir / Modifier les Notes');
          }

          // Bouton Imprimer
          var printUrl = window.RACINE + 'note/imprimerPdf?classe_code=' + encodeURIComponent(info.classe_code || classeCode)
                       + '&composition_code=' + encodeURIComponent(info.code_composition || compoCode)
                       + '&matiere_code=' + encodeURIComponent(info.matiere_code || '');
          $('#md-btn-go-print').attr('href', printUrl);

          $('#modal-details-content').show();
          if (window.lucide) lucide.createIcons();
        } else {
          $('#md-students-list').html('<tr><td colspan="7" style="text-align:center; padding:20px; color:#DC2626;">Erreur de chargement des détails.</td></tr>');
          $('#modal-details-content').show();
        }
      },
      error: function() {
        $('#modal-loading-spinner').hide();
        $('#md-students-list').html('<tr><td colspan="7" style="text-align:center; padding:20px; color:#DC2626;">Erreur de communication avec le serveur.</td></tr>');
        $('#modal-details-content').show();
      }
    });
  });

  // FERMETURE DU MODAL DETAILS
  $(document).on('click', '.btn-close-details-modal', function() {
    $('#modal-details-notes').hide();
  });
  $('#modal-details-notes').on('click', function(e) {
    if ($(e.target).is('#modal-details-notes')) {
      $('#modal-details-notes').hide();
    }
  });
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#modal-details-notes').is(':visible')) {
      $('#modal-details-notes').hide();
    }
  });

  $(document).on('change', '.toggle-statut-note', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: window.RACINE + 'note/changer',
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

  }

  // =========================================================================
  // 2. VUE : EDITION UNITAIRE D'UNE NOTE (Execute si #form-note-indiv est present)
  // =========================================================================
  if ($('#form-note-indiv').length) {

  var cfg = {};
  try {
    var cfgEl = document.getElementById('notes-edit-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}

  if (window.lucide) lucide.createIcons();
  
  var studentsByClass = cfg.studentsByClass || {};
  var selectedInscCode = cfg.selectedInscCode || '';
  var existingCompCode = cfg.existingCompCode || '';

  if ($.fn.select2) {
    $('#sel_niveau_filter, #sel_classe_no, #sel_mat_no, #sel_sem_no, #sel_type_eval, #sel_composition_code').select2({ width: '100%' });
  }

  // Live appreciation calculator
  function updateAppreciationBadge() {
    var val = $('#valeur_note_input').val();
    var $b = $('#badge-appreciation-live');
    if (val === '' || isNaN(val)) {
      $b.text('Non saisi').css({ 'background': '#F1F5F9', 'color': '#64748B' });
      return;
    }
    val = parseFloat(val);
    if (val >= 16) { $b.text('Très Bien').css({ 'background': '#DCFCE7', 'color': '#15803D' }); }
    else if (val >= 14) { $b.text('Bien').css({ 'background': '#E0F2FE', 'color': '#0369A1' }); }
    else if (val >= 12) { $b.text('Assez Bien').css({ 'background': '#FEF3C7', 'color': '#B45309' }); }
    else if (val >= 10) { $b.text('Passable').css({ 'background': '#FFEDD5', 'color': '#C2410C' }); }
    else { $b.text('Insuffisant').css({ 'background': '#FEE2E2', 'color': '#B91C1C' }); }
  }

  $('#valeur_note_input').on('input change', updateAppreciationBadge);
  updateAppreciationBadge();

  // Dynamic Type Eval Switcher (LOCKING/UNLOCKING LIBELLÉ, COEF, SEMESTRE)
  function handleTypeEvalChange() {
    var typeEval = $('#sel_type_eval').val();
    
    if (typeEval === 'EXAMEN') {
      $('#box-composition-select').slideDown(200);
      
      var $selectedOption = $('#sel_composition_code option:selected');
      if ($selectedOption.length && $selectedOption.val()) {
        var lib = $selectedOption.data('libelle');
        var coef = $selectedOption.data('coef');
        var sem = $selectedOption.data('semestre');

        if (lib) $('#input_libelle_eval').val(lib);
        if (coef) $('#input_coef_eval').val(coef);
        if (sem) {
          $('#sel_sem_no').val(sem).trigger('change.select2');
          $('#semestre_code_hidden').val(sem);
        }
      }

      $('#input_libelle_eval').prop('readonly', true).addClass('input-readonly');
      $('#input_coef_eval').prop('readonly', true).addClass('input-readonly');
      $('#badge-libelle-mode').text('Lecture Seule').css({ 'background': '#FEF3C7', 'color': '#B45309' });
      $('#badge-coef-mode').text('Lecture Seule').css({ 'background': '#FEF3C7', 'color': '#B45309' });
      $('#badge-semestre-lock').show();

    } else {
      $('#box-composition-select').slideUp(200);
      
      $('#input_libelle_eval').prop('readonly', false).removeClass('input-readonly');
      $('#input_coef_eval').prop('readonly', false).removeClass('input-readonly');
      
      if ($('#input_libelle_eval').val() === '' || $('#input_libelle_eval').val().indexOf('Composition') !== -1) {
        $('#input_libelle_eval').val(typeEval === 'DEVOIR' ? 'Devoir Sur Table N°1' : 'Interrogation N°1');
      }
      if ($('#input_coef_eval').val() === '' || $('#input_coef_eval').val() === '2.00') {
        $('#input_coef_eval').val('1.00');
      }

      $('#badge-libelle-mode').text('Modifiable').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#badge-coef-mode').text('Modifiable').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#badge-semestre-lock').hide();
    }
  }

  $('#sel_type_eval').on('change', handleTypeEvalChange);

  // Composition Option Change Event
  $('#sel_composition_code').on('change', function() {
    var $opt = $(this).find('option:selected');
    if ($opt.length && $opt.val()) {
      var lib = $opt.data('libelle');
      var coef = $opt.data('coef');
      var sem = $opt.data('semestre');

      if (lib) $('#input_libelle_eval').val(lib);
      if (coef) $('#input_coef_eval').val(coef);
      if (sem) {
        $('#sel_sem_no').val(sem).trigger('change.select2');
        $('#semestre_code_hidden').val(sem);
      }
    }
  });

  // Fetch Compositions via AJAX when Classe or Matiere changes
  function fetchCompositionsAjax() {
    var classeCode = $('#sel_classe_no').val();
    var matiereCode = $('#sel_mat_no').val();

    if (!classeCode || !matiereCode) return;

    $.ajax({
      url: window.RACINE + 'composition/getByClasseMatiereApi',
      type: 'GET',
      data: { classe_code: classeCode, matiere_code: matiereCode },
      dataType: 'json',
      success: function(res) {
        if (res && res.data) {
          var $select = $('#sel_composition_code');
          $select.empty().append('<option value="">-- Sélectionner l\'épreuve programmée --</option>');
          
          if (res.data.length === 0) {
            $('#msg-no-composition').show();
          } else {
            $('#msg-no-composition').hide();
            res.data.forEach(function(comp) {
              var dComp = comp.date_composition ? comp.date_composition : '';
              var coef = comp.coefficient ? ' (Coef ' + parseFloat(comp.coefficient) + ')' : '';
              var isSel = (existingCompCode && existingCompCode === comp.code_composition) ? 'selected' : '';
              var optHtml = '<option value="' + comp.code_composition + '" data-libelle="' + comp.libelle_composition + '" data-coef="' + comp.coefficient + '" data-semestre="' + comp.semestre_code + '" ' + isSel + '>[Examen] ' + comp.libelle_composition + ' ' + coef + '</option>';
              $select.append(optHtml);
            });
          }
          $select.trigger('change.select2');
          handleTypeEvalChange();
        }
      }
    });
  }

  $('#sel_classe_no, #sel_mat_no').on('change', function() {
    fetchCompositionsAjax();
  });

  // Filtrage Niveau -> Classe
  $('#sel_niveau_filter').on('change', function() {
    var nivCode = $(this).val();
    $('#sel_classe_no option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!nivCode || !optNiveau || optNiveau === nivCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#sel_classe_no option:selected').prop('disabled')) {
      $('#sel_classe_no').val('').trigger('change.select2');
      loadStudentsForClasse('', false);
    } else {
      $('#sel_classe_no').select2({ width: '100%' });
    }
  });

  function loadStudentsForClasse(classeCode, keepSelected) {
    var $el = $('#sel_ins_no');
    $el.empty();
    
    if (!classeCode || !studentsByClass[classeCode]) {
      $el.append('<option value="">-- Veuillez d\'abord choisir une classe ci-dessus --</option>');
      $el.prop('disabled', true);
      $('#class-info-banner').css('display', 'none');
    } else {
      var list = studentsByClass[classeCode];
      var clsName = $('#sel_classe_no option:selected').text();

      $('#class-info-title').text(clsName);
      $('#class-student-badge').text(list.length + ' élève' + (list.length > 1 ? 's' : '') + ' inscrit' + (list.length > 1 ? 's' : ''));
      $('#class-info-banner').css('display', 'flex');

      $el.append('<option value="">-- Choisir un élève de la classe --</option>');
      $.each(list, function(i, st) {
        var isSelected = (keepSelected && st.code_inscription === selectedInscCode) ? 'selected' : '';
        $el.append('<option value="' + st.code_inscription + '" ' + isSelected + '>' + st.nom_affiche + '</option>');
      });
      $el.prop('disabled', false);
    }
    
    if ($.fn.select2) {
      $el.select2({ placeholder: "-- Choisir un élève --", width: '100%' });
    }
  }

  $('#sel_classe_no').on('change', function() {
    loadStudentsForClasse($(this).val(), false);
  });

  var initialClasse = $('#sel_classe_no').val();
  if (initialClasse) {
    loadStudentsForClasse(initialClasse, true);
    fetchCompositionsAjax();
  } else {
    loadStudentsForClasse('', false);
  }

  handleTypeEvalChange();

  }

  // =========================================================================
  // 3. VUE : SAISIE COLLECTIVE PAR CLASSE (Execute si #filter-notes-form est present)
  // =========================================================================
  if ($('#filter-notes-form').length) {

  var cfg = {};
  try {
    var cfgEl = document.getElementById('notes-saisie-classe-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}

  window.COMPOSITIONS = cfg.compositions || [];
  window.ALL_CLASSES = cfg.allClasses || [];
  var selectedTypeEval = cfg.selectedTypeEval || '';
  var selectedCompositionCode = cfg.selectedCompositionCode || '';
  var selectedClasseCode = cfg.selectedClasseCode || '';
  var selectedMatiereCode = cfg.selectedMatiereCode || '';

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // Rendu dynamique et intelligent des classes dans le select
  function renderClasseOptions(classList, selectedCode) {
    var $sel = $('#sel_classe_code');
    var currentVal = selectedCode || $sel.val();
    $sel.empty().append('<option value="">-- Choisir la classe --</option>');
    
    var valStillExists = false;
    classList.forEach(function(c) {
      var isSelected = (currentVal && currentVal === c.code_classe);
      if (isSelected) valStillExists = true;
      var opt = $('<option></option>')
        .attr('value', c.code_classe)
        .text(c.libelle_classe);
      if (isSelected) opt.prop('selected', true);
      $sel.append(opt);
    });

    if (!valStillExists && currentVal && !isInitialLoading) {
      $sel.val('');
    }
    $sel.trigger('change.select2');
  }

  // 1. Détection du Type d'Évaluation : Tout réinitialiser lors d'un changement
  $('#sel_type_eval').on('change', function() {
    var typeEval = $(this).val();

    if (!isInitialLoading) {
      // RÉINITIALISATION COMPLÈTE DE TOUT L'ÉTAT ET DES CHAMPS
      $('#box-grille-notes-container').empty();
      isFormDirty = false;
      $('#badge-unsaved').hide();

      // Réinitialiser la composition / examen
      $('#sel_composition_code').val('').trigger('change.select2');

      // Réinitialiser Semestre (déverrouillé et vide)
      $('#sel_semestre_code').prop('disabled', false).val('').trigger('change.select2');
      $('#badge-semestre-lock').hide();
      $('#semestre_code_hidden').val('');

      // Réinitialiser Coefficient (déverrouillé et vide)
      $('#input_coef_eval').prop('readonly', false).removeClass('input-readonly').val('');

      // Réinitialiser Classe (remettre toutes les classes et vider la sélection)
      renderClasseOptions(window.ALL_CLASSES, '');
      $('#sel_classe_code').val('').trigger('change.select2');

      // Réinitialiser Matière (vider la liste et remettre l'option d'attente)
      $('#sel_matiere_code').empty().append('<option value="">-- Choisir d\'abord une classe --</option>').val('').trigger('change.select2');
      $('#badge-matiere-count').text('0 matière(s)');

      // Réinitialiser le libellé d'évaluation
      $('#input_libelle_eval').val('');
    }

    if (!typeEval) {
      $('#box-composition-select').slideUp(150);
      $('#box-params-fields').slideUp(150);
      $('#box-libelle-eval').slideUp(150);
      return;
    }

    if (typeEval === 'EXAMEN') {
      // A) Sélection Type EXAMEN : affiche le sélecteur d'examen programmé
      $('#box-composition-select').slideDown(200);
      $('#box-libelle-eval').slideUp(150);
      // Le bloc des 4 champs reste masqué tant que l'examen n'est pas choisi
      $('#box-params-fields').slideUp(150);
    } else {
      // B) Sélection Type INTERROGATION, DEVOIR ou AUTRE :
      $('#box-composition-select').slideUp(150);

      // Intitulé par défaut selon le type
      var defaultLib = (typeEval === 'DEVOIR') ? 'Devoir Sur Table N°1' : ((typeEval === 'INTERROGATION') ? 'Interrogation N°1' : 'Évaluation N°1');
      $('#input_libelle_eval').val(defaultLib);
      $('#box-libelle-eval').slideDown(150);

      // Coefficient par défaut si vide
      if (!$('#input_coef_eval').val()) {
        $('#input_coef_eval').val('1.00');
      }

      // Afficher immédiatement les 4 champs dans l'ordre strict
      $('#box-params-fields').slideDown(200);
    }
  });

  // A) Sélection du Libellé Examen : remplit Semestre, Coefficient et filtre les Classes
  $('#sel_composition_code').on('change', function() {
    var compCode = $(this).val();

    if (!isInitialLoading) {
      $('#box-grille-notes-container').empty();
      renderClasseOptions(window.ALL_CLASSES, '');
      $('#sel_classe_code').val('').trigger('change.select2');
      $('#sel_matiere_code').empty().append('<option value="">-- Choisir d\'abord une classe --</option>').val('').trigger('change.select2');
      $('#badge-matiere-count').text('0 matière(s)');
    }

    if (!compCode) {
      if ($('#sel_type_eval').val() === 'EXAMEN') {
        $('#box-params-fields').slideUp(150);
      }
      return;
    }

    var $opt = $(this).find('option:selected');
    var lib = $opt.data('libelle');
    var coef = $opt.data('coef');
    var sem = $opt.data('semestre');
    var rawClasses = $opt.data('classes');
    var targetClasses = [];

    if (rawClasses) {
      try {
        targetClasses = (typeof rawClasses === 'string') ? JSON.parse(rawClasses) : rawClasses;
      } catch(e) {
        targetClasses = [];
      }
    }

    // 1- Remplir et verrouiller Semestre
    if (sem) {
      $('#sel_semestre_code').val(sem).trigger('change.select2');
      $('#semestre_code_hidden').val(sem);
      $('#sel_semestre_code').prop('disabled', true);
      $('#badge-semestre-lock').show();
    }

    // 1- Remplir et verrouiller Coefficient
    if (coef) {
      $('#input_coef_eval').val(coef).prop('readonly', true).addClass('input-readonly');
    }

    if (lib) {
      $('#input_libelle_eval').val(lib);
    }

    // 1- Filtrer les classes concernées par cet examen
    var classesToRender = (targetClasses && targetClasses.length > 0) ? targetClasses : window.ALL_CLASSES;
    var currentClasse = isInitialLoading ? selectedClasseCode : '';
    renderClasseOptions(classesToRender, currentClasse);

    if (!isInitialLoading || !currentClasse) {
      // Réinitialiser la matière
      $('#sel_matiere_code').empty().append('<option value="">-- Choisir d\'abord une classe --</option>').trigger('change.select2');
    }

    // Révéler le bloc des 4 champs
    $('#box-params-fields').slideDown(200);
  });

  // 2- La sélection de classe remplit le champ Matière (via API)
  $('#sel_classe_code').on('change', function() {
    var classeCode = $(this).val();
    var $matiereSel = $('#sel_matiere_code');

    if (!isInitialLoading) {
      $('#box-grille-notes-container').empty();
    }

    if (!classeCode) {
      $matiereSel.empty().append('<option value="">-- Choisir d\'abord une classe --</option>');
      $('#badge-matiere-count').text('0 matière(s)');
      $matiereSel.trigger('change.select2');
      return;
    }

    var typeEval = $('#sel_type_eval').val();
    var compCode = (typeEval === 'EXAMEN') ? $('#sel_composition_code').val() : '';

    $matiereSel.empty().append('<option value="">Chargement des matières...</option>').trigger('change.select2');

    $.ajax({
      url: window.RACINE + 'note/getMatieresByClasseApi',
      type: 'POST',
      data: {
        classe_code: classeCode,
        composition_code: compCode
      },
      dataType: 'json',
      success: function(res) {
        $matiereSel.empty();
        var items = (res && res.data) ? res.data : [];
        if (items.length === 0) {
          $matiereSel.append('<option value="">-- Aucune matière trouvée pour cette classe --</option>');
          $('#badge-matiere-count').text('0 matière');
        } else {
          $matiereSel.append('<option value="">-- Choisir la matière (' + items.length + ' disponible(s)) --</option>');
          $('#badge-matiere-count').text(items.length + ' matière(s)');

          var preselectedMatiere = isInitialLoading ? selectedMatiereCode : '';

          items.forEach(function(m) {
            var coef = m.coefficient ? parseFloat(m.coefficient) : 1.0;
            var prof = m.prof_nom ? ' [' + m.prof_nom + ']' : '';
            var isSel = (preselectedMatiere && preselectedMatiere === m.code_matiere);
            var opt = $('<option></option>')
              .attr('value', m.code_matiere)
              .attr('data-coef', coef)
              .text(m.libelle_matiere + ' (Coef ' + coef + ')' + prof);
            if (isSel) opt.prop('selected', true);
            $matiereSel.append(opt);
          });
        }

        $matiereSel.trigger('change.select2');
      },
      error: function(xhr, status, err) {
        console.error('Erreur chargement matières:', status, err, xhr.responseText);
        $matiereSel.empty().append('<option value="">-- Erreur de chargement --</option>').trigger('change.select2');
      }
    });
  });

  // 3- Fonction de chargement AJAX de la grille des étudiants sans rechargement de page
  function loadGrilleNotesAjax() {
    var matiereCode = $('#sel_matiere_code').val();
    if (!matiereCode) {
      $('#box-grille-notes-container').empty();
      return;
    }

    var classeCode = $('#sel_classe_code').val();
    if (!classeCode) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Classe requise',
          text: 'Veuillez sélectionner une classe.',
          confirmButtonColor: '#1E3A5F'
        });
      }
      return;
    }

    var semCode = $('#sel_semestre_code').val() || $('#semestre_code_hidden').val();
    if (!semCode) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Semestre requis',
          text: 'Veuillez sélectionner le semestre avant d\'afficher la liste des étudiants.',
          confirmButtonColor: '#1E3A5F'
        }).then(function() {
          $('#sel_semestre_code').select2('open');
        });
      } else {
        alert('Veuillez sélectionner le semestre.');
        $('#sel_semestre_code').focus();
      }
      return;
    }

    var typeEval = $('#sel_type_eval').val();
    var compCode = (typeEval === 'EXAMEN') ? $('#sel_composition_code').val() : '';
    var libelleEval = $('#input_libelle_eval').val();
    var coefEval = $('#input_coef_eval').val();

    // Afficher l'animation de chargement
    $('#loading-matiere-grid').slideDown(150);

    $.ajax({
      url: window.RACINE + 'note/getGrilleAjax',
      type: 'POST',
      data: {
        classe_code: classeCode,
        matiere_code: matiereCode,
        semestre_code: semCode,
        type_evaluation_code: typeEval,
        composition_code: compCode,
        libelle_eval: libelleEval,
        coefficient: coefEval
      },
      dataType: 'html',
      success: function(html) {
        $('#loading-matiere-grid').slideUp(150);
        $('#box-grille-notes-container').html(html);

        if (window.lucide) {
          lucide.createIcons();
        }

        updateDynamicStats();
      },
      error: function(xhr, status, err) {
        $('#loading-matiere-grid').slideUp(150);
        console.error('Erreur chargement grille AJAX:', status, err);
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Erreur lors du chargement de la grille des notes.',
            confirmButtonColor: '#1E3A5F'
          });
        }
      }
    });
  }

  // 3- La sélection Matière affiche la liste des étudiants de la classe via AJAX
  $('#sel_matiere_code').on('change', function() {
    if (isInitialLoading) return;

    var matiereCode = $(this).val();
    if (!matiereCode) {
      $('#box-grille-notes-container').empty();
      return;
    }

    var typeEval = $('#sel_type_eval').val();
    var opt = $(this).find('option:selected');
    var coef = opt.data('coef');

    // Mise à jour automatique du coefficient si hors-examen
    if (typeEval !== 'EXAMEN' && coef) {
      $('#input_coef_eval').val(coef);
    }

    loadGrilleNotesAjax();
  });

  // Support sélection Semestre après choix de la matière (Cas B)
  $('#sel_semestre_code').on('change', function() {
    if (isInitialLoading) return;
    var semVal = $(this).val();
    $('#semestre_code_hidden').val(semVal);

    var classeCode = $('#sel_classe_code').val();
    var matiereCode = $('#sel_matiere_code').val();

    if (semVal && classeCode && matiereCode) {
      loadGrilleNotesAjax();
    }
  });

  // Intercepter toute tentative de soumission GET classique pour forcer le chargement AJAX
  $('#filter-notes-form').on('submit', function(e) {
    e.preventDefault();
    loadGrilleNotesAjax();
  });

  // Initialisation à l'arrivée sur la page
  var initialType = selectedTypeEval;
  var initialComp = selectedCompositionCode;
  var initialClasse = selectedClasseCode;

  if (initialType === 'EXAMEN') {
    $('#box-composition-select').show();
    if (initialComp) {
      var $selCompOpt = $('#sel_composition_code option:selected');
      var rawClasses = $selCompOpt.data('classes');
      var targetClasses = [];
      if (rawClasses) {
        try {
          targetClasses = (typeof rawClasses === 'string') ? JSON.parse(rawClasses) : rawClasses;
        } catch(e) {}
      }
      var classesToRender = (targetClasses && targetClasses.length > 0) ? targetClasses : window.ALL_CLASSES;
      renderClasseOptions(classesToRender, initialClasse);
      $('#sel_semestre_code').prop('disabled', true);
      $('#badge-semestre-lock').show();
      $('#input_coef_eval').prop('readonly', true).addClass('input-readonly');
      $('#box-params-fields').show();
    }
  } else if (initialType) {
    $('#box-composition-select').hide();
    renderClasseOptions(window.ALL_CLASSES, initialClasse);
    if (!initialClasse) {
      $('#sel_matiere_code').empty().append('<option value="">-- Choisir d\'abord une classe --</option>').trigger('change.select2');
    }
    $('#sel_semestre_code').prop('disabled', false);
    $('#badge-semestre-lock').hide();
    $('#input_coef_eval').prop('readonly', false).removeClass('input-readonly');
    $('#box-params-fields').show();
    $('#box-libelle-eval').show();
  }

  isInitialLoading = false;

  // 5. Calcul dynamique des appréciations et KPIs en direct (Zero Hardcoding)
  function getAppreciation(val, isAbsent) {
    if (isAbsent) return { text: 'Absent', bg: '#FEE2E2', color: '#B91C1C' };
    if (isNaN(val) || val === '' || val === null) return { text: 'Non saisi', bg: '#F1F5F9', color: '#64748B' };
    val = parseFloat(val);
    if (val >= 16) return { text: 'Très Bien', bg: '#DCFCE7', color: '#15803D' };
    if (val >= 14) return { text: 'Bien', bg: '#E0F2FE', color: '#0369A1' };
    if (val >= 12) return { text: 'Assez Bien', bg: '#FEF3C7', color: '#B45309' };
    if (val >= 10) return { text: 'Passable', bg: '#FFEDD5', color: '#C2410C' };
    return { text: 'Insuffisant', bg: '#FEE2E2', color: '#B91C1C' };
  }

  function updateDynamicStats() {
    var sum = 0;
    var count = 0;
    var totalStudents = $('.note-row').length;
    var maxVal = null;
    var minVal = null;

    $('.input-valeur-note').each(function() {
      var rawVal = $(this).val().trim();
      var $row = $(this).closest('tr');
      var $obsInput = $row.find('.input-obs');
      var obsText = $obsInput.val().trim();
      var isAbsent = $row.hasClass('row-absent') || (obsText.toLowerCase().indexOf('absent') !== -1);

      // Support saisie rapide "a", "abs", "ABS"
      if (rawVal.toLowerCase() === 'a' || rawVal.toLowerCase() === 'abs' || rawVal.toLowerCase() === 'absent') {
        $(this).val('0');
        $obsInput.val('ABSENT');
        $row.addClass('row-absent');
        isAbsent = true;
        rawVal = '0';
      }

      var normalizedVal = rawVal.replace(',', '.');
      var numericVal = parseFloat(normalizedVal);

      var $badge = $row.find('.badge-appreciation');
      var app = getAppreciation(rawVal !== '' ? numericVal : '', isAbsent);
      $badge.text(app.text).css({ 'background': app.bg, 'color': app.color });

      if (rawVal !== '' && !isNaN(numericVal)) {
        if (!isAbsent) {
          sum += numericVal;
          count++;
          if (maxVal === null || numericVal > maxVal) maxVal = numericVal;
          if (minVal === null || numericVal < minVal) minVal = numericVal;
        } else {
          // Absent comptabilisé comme noté
          count++;
        }
      } else if (isAbsent) {
        count++;
      }
    });

    // Progression
    var pct = totalStudents > 0 ? Math.round((count / totalStudents) * 100) : 0;
    $('#stat-progress-count').text(count + ' / ' + totalStudents);
    $('#stat-progress-pct').text(pct + '%');
    $('#stat-progress-bar').css('width', pct + '%');

    // Moyenne, Min, Max
    if (count > 0 && sum > 0) {
      var moy = (sum / count).toFixed(2);
      $('#stat-moyenne-classe').text(moy + ' / 20');
    } else {
      $('#stat-moyenne-classe').text('- / 20');
    }

    $('#stat-note-max').text(maxVal !== null ? maxVal.toFixed(2) + ' / 20' : '-');
    $('#stat-note-min').text(minVal !== null ? minVal.toFixed(2) + ' / 20' : '-');
  }

  // 6. Navigation ergonomique au clavier (Enter, ArrowDown, ArrowUp)
  $(document).on('keydown', '.input-valeur-note', function(e) {
    var $inputs = $('.input-valeur-note');
    var idx = $inputs.index(this);

    if (e.key === 'Enter' || e.key === 'ArrowDown') {
      e.preventDefault();
      if (idx + 1 < $inputs.length) {
        $inputs.eq(idx + 1).focus().select();
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (idx - 1 >= 0) {
        $inputs.eq(idx - 1).focus().select();
      }
    }
  });

  $(document).on('input change', '.input-valeur-note, .input-obs', function() {
    isFormDirty = true;
    $('#badge-unsaved').show();
    var $row = $(this).closest('tr');
    $row.addClass('row-dirty');
    updateDynamicStats();
  });

  updateDynamicStats();

  // 7. Bouton bascule statut absent par ligne
  $(document).on('click', '.btn-toggle-absent', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var $noteInput = $row.find('.input-valeur-note');
    var $obsInput = $row.find('.input-obs');

    if ($row.hasClass('row-absent')) {
      $row.removeClass('row-absent');
      if ($obsInput.val() === 'ABSENT') $obsInput.val('');
    } else {
      $row.addClass('row-absent');
      $noteInput.val('0');
      $obsInput.val('ABSENT');
    }
    isFormDirty = true;
    $('#badge-unsaved').show();
    updateDynamicStats();
  });

  // 8. Outils rapides d'assistance (délégation d'événements document pour persistance après AJAX)
  $(document).on('click', '#btn-quick-absent', function() {
    $('.note-row').each(function() {
      var $val = $(this).find('.input-valeur-note');
      var $obs = $(this).find('.input-obs');
      if ($val.val().trim() === '') {
        $val.val('0');
        $obs.val('ABSENT');
        $(this).addClass('row-absent row-dirty');
      }
    });
    isFormDirty = true;
    $('#badge-unsaved').show();
    updateDynamicStats();
    if (window.toastr) toastr.info('Étudiants sans note marqués comme Absents.');
  });

  $(document).on('click', '#btn-quick-fill', function() {
    var noteVal = prompt('Entrez la note uniforme à attribuer aux étudiants non notés (entre 0 et 20) :');
    if (noteVal !== null && noteVal.trim() !== '') {
      var num = parseFloat(noteVal.replace(',', '.'));
      if (isNaN(num) || num < 0 || num > 20) {
        alert('Veuillez saisir une note valide entre 0 et 20.');
        return;
      }
      $('.note-row').each(function() {
        var $val = $(this).find('.input-valeur-note');
        if ($val.val().trim() === '') {
          $val.val(num.toFixed(2));
          $(this).addClass('row-dirty');
        }
      });
      isFormDirty = true;
      $('#badge-unsaved').show();
      updateDynamicStats();
    }
  });

  $(document).on('click', '#btn-quick-clear', function() {
    if (confirm('Voulez-vous réinitialiser toutes les modifications non enregistrées ?')) {
      $('.note-row').each(function() {
        var $val = $(this).find('.input-valeur-note');
        var $obs = $(this).find('.input-obs');
        $val.val($val.data('original'));
        $obs.val($obs.data('original'));
        $(this).removeClass('row-dirty');
        if (($obs.val() || '').toLowerCase().indexOf('absent') === -1) {
          $(this).removeClass('row-absent');
        }
      });
      isFormDirty = false;
      $('#badge-unsaved').hide();
      updateDynamicStats();
    }
  });

  // 9. Enregistrement AJAX (Enregistrer & Rester vs Enregistrer & Quitter)
  function executeSave(shouldRedirect) {
    var $form = $('#form-save-batch-notes');
    var $btnSaveStay = $('#btn-save-stay');
    var $btnSaveLeave = $('#btn-save-leave');

    $btnSaveStay.prop('disabled', true);
    $btnSaveLeave.prop('disabled', true);

    var targetBtn = shouldRedirect ? $btnSaveLeave : $btnSaveStay;
    targetBtn.html('<i data-lucide="loader" class="spin"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'note/saveBatch',
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(res) {
        $btnSaveStay.prop('disabled', false).html('<i data-lucide="save"></i> Enregistrer & Rester');
        $btnSaveLeave.prop('disabled', false).html('<i data-lucide="check-circle"></i> Enregistrer & Quitter');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          isFormDirty = false;
          $('#badge-unsaved').hide();
          
          var now = new Date();
          var timeStr = now.toLocaleTimeString();
          $('#last-saved-time').text('Dernière sauvegarde : ' + timeStr);

          if (res.composition_code) {
            $('input[name="composition_code"]').val(res.composition_code);
            $('#sel_composition_code').val(res.composition_code);
          }

          // Mettre à jour les données originales pour chaque champ
          $('.note-row').each(function() {
            var $val = $(this).find('.input-valeur-note');
            var $obs = $(this).find('.input-obs');
            $val.data('original', $val.val());
            $obs.data('original', $obs.val());
            $(this).removeClass('row-dirty');
          });

          if (window.toastr) toastr.success(res.message || 'Notes enregistrées avec succès !');

          if (shouldRedirect) {
            setTimeout(function() {
              window.location.href = window.RACINE + 'note/list';
            }, 800);
          }
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement des notes');
        }
      },
      error: function(xhr) {
        $btnSaveStay.prop('disabled', false).html('<i data-lucide="save"></i> Enregistrer & Rester');
        $btnSaveLeave.prop('disabled', false).html('<i data-lucide="check-circle"></i> Enregistrer & Quitter');
        if (window.lucide) lucide.createIcons();
        if (window.toastr) toastr.error('Erreur technique lors de la communication avec le serveur.');
      }
    });
  }

  $(document).on('click', '#btn-save-stay', function(e) {
    e.preventDefault();
    executeSave(false);
  });

  $(document).on('click', '#btn-save-leave', function(e) {
    e.preventDefault();
    executeSave(true);
  });

  $(document).on('submit', '#form-save-batch-notes', function(e) {
    e.preventDefault();
    executeSave(false);
  });

  // Protection contre fermeture accidentelle si modifications non enregistrées
  window.addEventListener('beforeunload', function(e) {
    if (isFormDirty) {
      e.preventDefault();
      e.returnValue = 'Des modifications n\'ont pas été enregistrées.';
      return e.returnValue;
    }
  });

  }

  // =========================================================================
  // 4. VUE : LISTE DES BULLETINS (Exécuté si #table-bulletin est présent)
  // =========================================================================
  if ($('#table-bulletin').length) {
    if ($.fn.select2) {
      $('#filter-annee, #filter-semestre, #filter-niveau, #filter-classe').select2({ width: '100%' });
    }

    // Helper pour mettre à jour les URLs d'impression et du PV de classe
    function updateBulletinUrls() {
      var annee = $('#filter-annee').val();
      var sem = $('#filter-semestre').val();
      var niv = $('#filter-niveau').val();
      var cls = $('#filter-classe').val();

      if (!cls) {
        // Aucune classe sélectionnée : désactiver les boutons et afficher l'alerte
        $('#btn-print-registre-bulletin')
          .attr('href', 'javascript:void(0);')
          .css({ 'opacity': '0.5', 'cursor': 'not-allowed', 'pointer-events': 'none' })
          .attr('title', 'Veuillez sélectionner une classe pour imprimer le registre.');

        $('#btn-pv-classe')
          .attr('href', 'javascript:void(0);')
          .css({ 'opacity': '0.5', 'cursor': 'not-allowed', 'pointer-events': 'none' })
          .attr('title', 'Veuillez sélectionner une classe pour accéder au PV de classe.');

        $('#alert-select-classe').show();
      } else {
        // Classe sélectionnée : activer les boutons et masquer l'alerte
        var params = ['classe_code=' + encodeURIComponent(cls)];
        var pvParams = ['classe_code=' + encodeURIComponent(cls)];

        if (annee) params.push('annee_code=' + encodeURIComponent(annee));
        if (sem) {
          params.push('semestre_code=' + encodeURIComponent(sem));
          pvParams.push('semestre_code=' + encodeURIComponent(sem));
        }
        if (niv) params.push('niveau_code=' + encodeURIComponent(niv));

        var printUrl = window.RACINE + 'bulletin/imprimerRegistrePdf?' + params.join('&');
        $('#btn-print-registre-bulletin')
          .attr('href', printUrl)
          .css({ 'opacity': '1', 'cursor': 'pointer', 'pointer-events': 'auto' })
          .attr('title', 'Générer et imprimer le registre officiel des bulletins en PDF');

        var pvUrl = window.RACINE + 'bulletin/pvClasse?' + pvParams.join('&');
        $('#btn-pv-classe')
          .attr('href', pvUrl)
          .css({ 'opacity': '1', 'cursor': 'pointer', 'pointer-events': 'auto' })
          .attr('title', 'Consulter le Procès-Verbal récapitulatif de la classe sélectionnée');

        $('#alert-select-classe').hide();
      }
    }
    updateBulletinUrls();

    // Cascades & événements filtres
    $('#filter-classe, #filter-annee, #filter-semestre').on('change', function() {
      updateBulletinUrls();
      table.ajax.reload();
    });

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
      updateBulletinUrls();
      table.ajax.reload();
    });

    var table = $('#table-bulletin').DataTable({
      ajax: {
        url: window.RACINE + 'bulletin/apiList',
        type: 'GET',
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.semestre_code = $('#filter-semestre').val();
          d.niveau_code = $('#filter-niveau').val();
          d.classe_code = $('#filter-classe').val();
        },
        dataSrc: function(json) {
          var cls = $('#filter-classe').val();
          if (!cls) {
            return [];
          }
          return json.data || [];
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { 
          data: null, 
          width: '45px', 
          render: function(d, type, row, meta) {
            return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
          }
        },
        { 
          data: 'etudiant_nom',
          render: function(d, type, r) {
            var nom = d || '-';
            var mat = r.matricule_etudiant || '-';
            var sexe = r.sexe_etudiant || '';
            var photo = r.photo_etudiant ? String(r.photo_etudiant).trim() : '';

            var inits = '';
            var parts = nom.trim().split(/\s+/);
            if (parts.length >= 2) {
              inits = (parts[0][0] || '') + (parts[1][0] || '');
            } else if (parts.length === 1 && parts[0].length > 0) {
              inits = parts[0].substring(0, 2);
            }
            inits = inits.toUpperCase() || 'ET';

            var avatarHtml = '';
            if (photo !== '') {
              var photoUrl = (photo.indexOf('http') === 0) ? photo : (window.RACINE + photo.replace(/^\//, ''));
              avatarHtml = '<div style="width:38px; height:38px; border-radius:50%; overflow:hidden; flex-shrink:0; border:1.5px solid #CBD5E1; background:#F1F5F9; display:flex; align-items:center; justify-content:center;">' +
                             '<img src="' + photoUrl + '" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display=\'none\'; $(this).next().show();">' +
                             '<div style="display:none; width:100%; height:100%; align-items:center; justify-content:center; background:#EFF6FF; color:#1E3A5F; font-weight:800; font-size:12px;">' + inits + '</div>' +
                           '</div>';
            } else {
              avatarHtml = '<div style="width:38px; height:38px; border-radius:50%; background:#EFF6FF; color:#1E3A5F; border:1px solid #DBEAFE; font-weight:800; font-size:12px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">' + inits + '</div>';
            }

            var sexeBadge = sexe ? '<span style="font-size:10px; font-weight:800; color:#64748B; background:#F1F5F9; padding:1px 5px; border-radius:4px; margin-left:4px;">' + sexe + '</span>' : '';

            return '<div style="display:flex; align-items:center; gap:12px;">' +
                     avatarHtml +
                     '<div>' +
                       '<div style="display:flex; align-items:center; gap:4px;">' +
                         '<strong style="color:#0F172A; font-size:13.5px;">' + nom + '</strong>' +
                         sexeBadge +
                       '</div>' +
                       '<div style="display:flex; align-items:center; gap:6px; margin-top:2px;">' +
                         '<span style="font-family:monospace; font-size:11px; font-weight:700; color:#1E3A5F; background:#F8FAFC; border:1px solid #E2E8F0; padding:1px 6px; border-radius:4px;">' + mat + '</span>' +
                       '</div>' +
                     '</div>' +
                   '</div>';
          }
        },
        { 
          data: 'nb_notes', 
          className: 'text-center',
          render: function(d) {
            var count = parseInt(d || 0, 10);
            if (count > 0) {
              return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-size:12px; font-weight:800; padding:4px 10px; border-radius:6px;">' + count + ' note(s)</span>';
            }
            return '<span class="badge" style="background:#F1F5F9; color:#64748B; font-size:11px; font-weight:700; padding:4px 8px; border-radius:6px;">0 note</span>';
          }
        },
        { 
          data: 'moyenne_num', 
          className: 'text-center',
          render: function(d) {
            if (d !== null && d !== undefined && d !== '') {
              var val = parseFloat(d);
              var col = val >= 10 ? '#15803D' : '#DC2626';
              var bg = val >= 10 ? '#F0FDF4' : '#FEF2F2';
              var border = val >= 10 ? '#BBF7D0' : '#FECACA';
              return '<span style="display:inline-block; padding:4px 10px; border-radius:6px; background:' + bg + '; border:1px solid ' + border + '; color:' + col + '; font-weight:800; font-size:13.5px;">' +
                       val.toFixed(2) + ' / 20' +
                     '</span>';
            }
            return '<span style="color:#94A3B8; font-size:12px; font-style:italic;">En attente</span>';
          }
        },
        { 
          data: 'decision', 
          className: 'text-center',
          render: function(d) {
            var dec = d || 'En attente';
            if (dec === 'Admis(e)') {
              return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11px; font-weight:800; padding:4px 8px; border-radius:6px;">Admis(e)</span>';
            } else if (dec === 'Ajourné(e)') {
              return '<span class="badge" style="background:#FEE2E2; color:#B91C1C; font-size:11px; font-weight:800; padding:4px 8px; border-radius:6px;">Ajourné(e)</span>';
            }
            return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-size:11px; font-weight:700; padding:4px 8px; border-radius:6px;">En attente</span>';
          }
        },
        { 
          data: null, 
          width: '160px', 
          orderable: false, 
          className: 'text-end',
          render: function(d) {
            if (!d) return '';
            var curSem = $('#filter-semestre').val() || '';
            var semQuery = curSem ? ('?semestre=' + encodeURIComponent(curSem)) : '';
            var viewUrl = window.RACINE + 'bulletin/details/' + (d.editId || d.id_inscription) + semQuery;
            var pdfUrl = window.RACINE + 'bulletin/imprimerBulletinPdf/' + (d.editId || d.id_inscription) + semQuery;
            var pvUrl = d.code_classe 
              ? (window.RACINE + 'bulletin/pvClasse?classe_code=' + encodeURIComponent(d.code_classe) + (curSem ? ('&semestre_code=' + encodeURIComponent(curSem)) : '')) 
              : '';

            var actionsHtml = '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">';

            // Bouton Voir Bulletin
            actionsHtml += '<a href="' + viewUrl + '" class="btn btn-sm btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:5px; padding:6px 10px;" title="Consulter le relevé de notes détaillé">' +
                             '<i data-lucide="eye" style="width:14px; height:14px;"></i> Voir' +
                           '</a>';

            // Bouton Télécharger PDF
            actionsHtml += '<a href="' + pdfUrl + '" target="_blank" class="btn btn-sm btn-outline-secondary" style="border:1.5px solid #CBD5E1; color:#334155; background:#FFFFFF; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:5px; padding:6px 10px;" title="Télécharger le bulletin au format PDF">' +
                             '<i data-lucide="printer" style="width:14px; height:14px;"></i> PDF' +
                           '</a>';

            // Bouton PV Classe
            if (pvUrl) {
              actionsHtml += '<a href="' + pvUrl + '" class="btn btn-sm" style="background:#F1F5F9; border:1px solid #CBD5E1; color:#475569; font-weight:700; border-radius:6px; width:30px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;" title="Accéder au PV de cette classe">' +
                               '<i data-lucide="file-spreadsheet" style="width:14px; height:14px;"></i>' +
                             '</a>';
            }

            actionsHtml += '</div>';
            return actionsHtml;
          }
        }
      ],
      language: { url: window.RACINE + 'public/json/datatables-i18n-fr-FR.json' },
      drawCallback: function() { 
        var cls = $('#filter-classe').val();
        if (!cls) {
          $('#table-bulletin tbody td.dataTables_empty').html(
            '<div style="padding: 40px 20px; text-align: center;">' +
              '<div style="width: 52px; height: 52px; border-radius: 50%; background: #EFF6FF; color: #1E3A5F; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">' +
                '<i data-lucide="graduation-cap" style="width: 28px; height: 28px;"></i>' +
              '</div>' +
              '<div style="font-weight: 800; font-size: 15px; color: #1E3A5F;">Aucune classe sélectionnée</div>' +
              '<div style="font-size: 13px; color: #64748B; margin-top: 4px; max-width: 440px; margin-left: auto; margin-right: auto;">' +
                'Veuillez choisir une <strong>Classe</strong> dans les filtres ci-dessus pour charger les étudiants inscrits et leurs bulletins semestriels.' +
              '</div>' +
            '</div>'
          );
        }
        if (window.lucide) lucide.createIcons();
        try {
          var api = this.api();
          var rows = cls ? api.rows({ search: 'applied' }).data().toArray() : [];

          var totalInscrits = rows.length;
          var bulletinsDispo = 0;
          var moyennesAdmis = 0;
          var enAttente = 0;

          rows.forEach(function(r) {
            if (r) {
              var nb = parseInt(r.nb_notes || 0, 10);
              var moy = r.moyenne_num !== null && r.moyenne_num !== undefined ? parseFloat(r.moyenne_num) : null;
              if (nb > 0) {
                bulletinsDispo++;
              } else {
                enAttente++;
              }
              if (moy !== null && moy >= 10) {
                moyennesAdmis++;
              }
            }
          });

          $('#kpi-total-inscrits').text(totalInscrits);
          $('#kpi-bulletins-dispo').text(bulletinsDispo);
          $('#kpi-moyennes-admis').text(moyennesAdmis);
          $('#kpi-en-attente').text(enAttente);
        } catch (e) {}
      }
    });
  }

  // =========================================================================
  // 5. VUE : ÉDITION DE BULLETIN (Exécuté si #sel_etu_bu ou #sel_cls_bu présent)
  // =========================================================================
  if ($('#sel_etu_bu, #sel_cls_bu').length) {
    if ($.fn.select2) {
      $('#sel_etu_bu').select2({ placeholder: "-- Rechercher un étudiant --", allowClear: true, width: '100%' });
      $('#sel_cls_bu').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
    }
  }

  if (window.lucide) lucide.createIcons();
});
