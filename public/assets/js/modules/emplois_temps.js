/**
 * Module JS Unifié : Emplois du Temps, Absences, Affectations & Salles de cours
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // Exposer les méthodes d'impression globale
  window.printSchedule = function() {
    window.print();
  };

  // =========================================================================
  // 1. VUE : LISTE DES EMPLOIS DU TEMPS & GRILLE HEBDOMADAIRE (views/emplois_temps/list.php)
  // =========================================================================
  function initEmploisTempsListModule() {
    if (!$('#table-emplois_temps').length) return;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Cascading filter Niveau -> Classe
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
      $('#btn-add-emploi').attr('href', window.RACINE + 'emploi/formulaire?classe_code=' + encodeURIComponent(cls));
    } else {
      $('#btn-add-emploi').attr('href', window.RACINE + 'emploi/formulaire');
    }
    table.ajax.reload();
  });

  var table = $('#table-emplois_temps').DataTable({
    ajax: {
      url: window.RACINE + 'emploi/apiList',
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
        width: '50px', 
        render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }
      },
      { 
        data: 'libelle_classe', 
        render: function(d, t, r) { 
          return '<div style="display:flex; flex-direction:column; gap:3px;">' +
                   '<strong style="font-size:14px; color:#0F172A;">' + (d || r.code_classe) + '</strong>' +
                   '<span style="display:inline-block; width:fit-content; background:#E2E8F0; color:#475569; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">' + (r.libelle_niveau || 'Niveau non défini') + '</span>' +
                 '</div>'; 
        } 
      },
      { 
        data: 'total_heures_formatted', 
        render: function(d, t, r) { 
          return '<div style="display:flex; flex-direction:column; gap:3px;">' +
                   '<span style="display:inline-flex; align-items:center; gap:5px; background:#EFF6FF; color:#1E40AF; font-size:13px; font-weight:800; padding:4px 10px; border-radius:6px; width:fit-content;">' +
                     '<i data-lucide="clock" style="width:14px; height:14px;"></i> ' + d +
                   '</span>' +
                   '<small style="color:#64748B; font-size:11px; font-weight:600;">' + r.nb_creneaux + ' créneau(x) • ' + r.nb_jours + ' jour(s)</small>' +
                 '</div>'; 
        } 
      },
      { 
        data: 'nb_matieres', 
        render: function(d, t, r) { 
          var matList = r.matieres_noms ? r.matieres_noms : 'Aucune matière';
          if (matList.length > 45) matList = matList.substring(0, 42) + '...';
          return '<div style="display:flex; flex-direction:column; gap:3px;">' +
                   '<span style="font-size:12px; font-weight:700; color:#334155;">' + d + ' matière(s) • ' + r.nb_profs + ' enseignant(s)</span>' +
                   '<span style="font-size:11px; color:#64748B; font-style:italic;" title="' + (r.matieres_noms || '') + '">' + matList + '</span>' +
                 '</div>'; 
        } 
      },
      { 
        data: 'statut_planning', 
        className: 'text-center', 
        render: function(d, t, r) {
          if (d === 'Complet') {
            return '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 12px; border-radius:20px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;">' +
                     '<i data-lucide="check-circle-2" style="width:13px; height:13px;"></i> Planning Complet' +
                   '</span>';
          } else {
            return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:6px 12px; border-radius:20px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;">' +
                     '<i data-lucide="clock" style="width:13px; height:13px;"></i> En Saisie (' + r.nb_jours + '/6 j)' +
                   '</span>';
          }
        }
      },
      { 
        data: 'last_update', 
        render: function(d) { return '<span style="font-size:12px; color:#64748B; font-weight:600;">' + d + '</span>'; } 
      },
      { 
        data: null, 
        orderable: false, 
        className: 'text-end',
        render: function(d) {
          var codeEsc = encodeURIComponent(d.code_classe);
          var libEsc = $('<div>').text(d.libelle_classe).html();
          return '<div style="display:flex; justify-content:flex-end; gap:6px; flex-wrap:nowrap;">' +
                   '<a href="' + window.RACINE + 'emploi/formulaire?classe_code=' + codeEsc + '" class="btn btn-sm btn-action-planning" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; border: none; border-radius: 6px; font-weight: 700; padding: 6px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(30,58,95,0.2);" title="Gérer le planning">' +
                     '<i data-lucide="calendar-plus" style="width:14px; height:14px;"></i> Planning' +
                   '</a>' +
                   '<button type="button" class="btn btn-sm btn-action-grille btn-view-matrix" data-code="' + d.code_classe + '" data-libelle="' + libEsc + '" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; border-radius: 6px; font-weight: 700; padding: 6px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease;" title="Visualiser l\'emploi du temps">' +
                     '<i data-lucide="eye" style="width:14px; height:14px;"></i> Grille' +
                   '</button>' +
                   '<button type="button" class="btn btn-sm btn-action-reset btn-reset-classe" data-code="' + d.code_classe + '" data-libelle="' + libEsc + '" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; border-radius: 6px; font-weight: 700; padding: 6px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(220,38,38,0.08);" title="Vider le planning de cette classe">' +
                     '<i data-lucide="trash-2" style="width:14px; height:14px;"></i> Vider' +
                   '</button>' +
                 '</div>';
        } 
      }
    ],
    language: { url: window.RACINE + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
  });

  // Gestion de la fermeture de la modale Grille
  $(document).on('click', '.btn-close-modal', function() {
    $('#modalViewSchedule').hide();
  });
  $(document).on('click', '#modalViewSchedule', function(e) {
    if (e.target === this) {
      $('#modalViewSchedule').hide();
    }
  });

  // Action Visualiser Grille (Modal)
  $(document).on('click', '.btn-view-matrix', function() {
    var classeCode = $(this).data('code');
    var classeLibelle = $(this).data('libelle');
    
    $('#modal-classe-title').text(classeLibelle);
    $('#print-classe-name').text(classeLibelle);
    $('#modal-niveau-subtitle').text('Code Classe: ' + classeCode);
    $('#schedule-matrix-loader').show();
    $('#schedule-matrix-content').hide().empty();
    
    $('#modalViewSchedule').css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'emploi/getClassScheduleMatrix',
      type: 'GET',
      data: { classe_code: classeCode },
      dataType: 'json',
      success: function(res) {
        $('#schedule-matrix-loader').hide();
        if (res.status === 1) {
          if (res.classe && res.classe.libelle_niveau) {
            $('#modal-niveau-subtitle').text(res.classe.libelle_niveau);
          }
          renderScheduleMatrix(res.slots);
          $('#schedule-matrix-content').show();
          if (window.lucide) lucide.createIcons();
        } else {
          $('#schedule-matrix-content').html('<div class="alert alert-danger">Erreur de chargement de l\'emploi du temps.</div>').show();
        }
      },
      error: function() {
        $('#schedule-matrix-loader').hide();
        $('#schedule-matrix-content').html('<div class="alert alert-danger">Erreur réseau lors de la récupération des données.</div>').show();
      }
    });
  });

  // Action Réinitialiser / Vider Planning Classe avec SweetAlert2
  $(document).on('click', '.btn-reset-classe', function() {
    var classeCode = $(this).data('code');
    var classeLibelle = $(this).data('libelle');

    if (window.Swal) {
      Swal.fire({
        title: 'ATTENTION !',
        html: 'Voulez-vous vraiment supprimer <strong>TOUS les créneaux horaires</strong> de la classe <span style="color:#DC2626; font-weight:800;">"' + classeLibelle + '"</span> ?<br><small style="color:#64748B;">Cette action est irréversible.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i data-lucide="trash-2" style="width:16px; height:16px; margin-right:4px;"></i> Oui, tout supprimer',
        cancelButtonText: 'Annuler',
        customClass: {
          confirmButton: 'btn btn-danger font-bold',
          cancelButton: 'btn btn-secondary font-bold'
        }
      }).then(function(result) {
        if (result.isConfirmed) {
          executeResetClasse(classeCode);
        }
      });
    } else if (confirm('ATTENTION : Voulez-vous vraiment supprimer TOUS les créneaux horaires de la classe "' + classeLibelle + '" ?')) {
      executeResetClasse(classeCode);
    }
  });

  function executeResetClasse(classeCode) {
    $.ajax({
      url: window.RACINE + 'emploi/resetClasseSchedule',
      type: 'POST',
      data: { classe_code: classeCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'L\'emploi du temps de la classe a été entièrement réinitialisé !');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de la réinitialisation');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
      }
    });
  }

  function renderScheduleMatrix(slots) {
    var days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    var dayLabels = {
      'lundi': 'Lundi',
      'mardi': 'Mardi',
      'mercredi': 'Mercredi',
      'jeudi': 'Jeudi',
      'vendredi': 'Vendredi',
      'samedi': 'Samedi'
    };

    var grouped = {};
    days.forEach(function(d) { grouped[d] = []; });

    slots.forEach(function(s) {
      var j = (s.jour || '').toLowerCase().trim();
      if (grouped[j]) {
        grouped[j].push(s);
      }
    });

    var html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">';

    days.forEach(function(d) {
      var list = grouped[d];
      html += '<div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">';
      html +=   '<div style="background: #1E3A5F; color: #FFFFFF; padding: 10px 14px; font-weight: 800; font-size: 14px; display: flex; justify-content: space-between; align-items: center;">';
      html +=     '<span>' + dayLabels[d] + '</span>';
      html +=     '<span class="badge" style="background: rgba(255,255,255,0.2); font-size: 11px;">' + list.length + ' cours</span>';
      html +=   '</div>';
      html +=   '<div style="padding: 12px; display: flex; flex-direction: column; gap: 10px; min-height: 120px;">';

      if (list.length === 0) {
        html += '<div style="text-align: center; color: #94A3B8; font-size: 12px; margin: auto; padding: 20px 0;">Aucun cours programmé</div>';
      } else {
        list.forEach(function(item) {
          var debut = (item.heure_debut || '').substring(0,5);
          var fin = (item.heure_fin || '').substring(0,5);
          var mat = item.libelle_matiere || item.matiere_code || 'Matière n/a';
          var prof = item.nom_prof ? item.nom_prof.trim() : (item.enseignant_code || 'Non spécifié');
          var salle = item.libelle_salle || item.salle_code || 'Salle n/a';

          html += '<div style="background: #F8FAFC; border-left: 4px solid #0284C7; border: 1px solid #E2E8F0; border-left-width: 4px; border-left-color: #0284C7; border-radius: 8px; padding: 10px 12px;">';
          html +=   '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
          html +=     '<span style="font-weight: 800; font-size: 13px; color: #0284C7;">' + debut + ' - ' + fin + '</span>';
          html +=     '<span class="badge" style="background: #E0F2FE; color: #0369A1; font-size: 10px; font-weight: 700;">' + salle + '</span>';
          html +=   '</div>';
          html +=   '<div style="font-weight: 700; font-size: 13px; color: #0F172A; margin-bottom: 3px;">' + mat + '</div>';
          html +=   '<div style="font-size: 11px; color: #64748B; display: flex; align-items: center; gap: 4px;">';
          html +=     '<i data-lucide="user" style="width: 12px; height: 12px;"></i> ' + prof;
          html +=   '</div>';
          html += '</div>';
        });
      }

      html +=   '</div>';
      html += '</div>';
    });

    html += '</div>';
    $('#schedule-matrix-content').html(html);
  }

  }

  // =========================================================================
  // 2. VUE : ÉDITION CRÉNEAU & DÉTECTION CONFLITS (views/emplois_temps/edit.php)
  // =========================================================================
  function initEmploisTempsEditModule() {
    if (!$('#form-emploi, #emplois-temps-edit-config').length) return;

  var cfg = {};
  try {
    var cfgEl = document.getElementById('emplois-temps-edit-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var csrfToken = cfg.csrfToken || '';

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_cls_et').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
    $('#sel_mat_et').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_ens_et').select2({ placeholder: "-- Rechercher un enseignant --", allowClear: true, width: '100%' });
    $('#sel_sal_et').select2({ placeholder: "-- Rechercher une salle --", allowClear: true, width: '100%' });
  }

  // 1. Auto-Sélection Enseignant sur choix Classe + Matière
  function checkAndAutoSelectTeacher() {
    var classeCode = $('#sel_cls_et').val();
    var matiereCode = $('#sel_mat_et').val();

    if (!classeCode || !matiereCode) {
      $('#teacher-assigned-badge').slideUp(150);
      return;
    }

    $.ajax({
      url: window.RACINE + 'emploi/getAssignedTeacher',
      type: 'GET',
      data: { classe_code: classeCode, matiere_code: matiereCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data && res.data.enseignant_code) {
          var ensCode = res.data.enseignant_code;
          var currentVal = $('#sel_ens_et').val();
          if (currentVal !== ensCode) {
            $('#sel_ens_et').val(ensCode).trigger('change');
          }
          $('#teacher-assigned-badge').html(
            '<div style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1E3A5F; font-size:12px; font-weight:700; padding:6px 12px; border-radius:6px; margin-top:6px; display:flex; align-items:center; gap:6px;">' +
            '<i data-lucide="sparkles" style="width:14px;height:14px;color:#2563EB;"></i> Enseignant titulaire détecté : ' + res.data.nom_complet + ' (' + (res.data.grade || 'Titulaire') + ')' +
            '</div>'
          ).slideDown(200);
          if (window.lucide) lucide.createIcons();
        } else {
          $('#teacher-assigned-badge').slideUp(150);
        }
      }
    });
  }

  // 2. Contrôle de Conflits en Temps Réel
  var conflictTimer = null;
  function checkScheduleConflicts() {
    clearTimeout(conflictTimer);
    conflictTimer = setTimeout(function() {
      var classeCode = $('#sel_cls_et').val();
      var salleCode = $('#sel_sal_et').val();
      var enseignantCode = $('#sel_ens_et').val();
      var jour = $('select[name="jour"]').val();
      var heureDebut = $('input[name="heure_debut"]').val();
      var heureFin = $('input[name="heure_fin"]').val();
      var idEmploi = $('input[name="id_emploi"]').val() || '';

      if (!jour || !heureDebut || !heureFin) {
        $('#schedule-conflict-banner').slideUp(200);
        return;
      }

      $.ajax({
        url: window.RACINE + 'emploi/checkScheduleConflicts',
        type: 'GET',
        data: {
          classe_code: classeCode,
          salle_code: salleCode,
          enseignant_code: enseignantCode,
          jour: jour,
          heure_debut: heureDebut,
          heure_fin: heureFin,
          id_emploi: idEmploi
        },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1) {
            var banner = $('#schedule-conflict-banner');
            var iconBox = $('#conflict-icon-container');
            var icon = $('#conflict-icon');
            var title = $('#conflict-status-title');
            var subtitle = $('#conflict-status-subtitle');
            var list = $('#conflict-items-list');

            list.empty();

            if (res.has_conflict && res.conflicts && res.conflicts.length > 0) {
              banner.css({ 'background': '#FEF2F2', 'border': '1.5px solid #FCA5A5' });
              iconBox.css({ 'background': '#FEE2E2', 'color': '#DC2626' });
              icon.attr('data-lucide', 'alert-triangle');
              title.css('color', '#991B1B').text('Attention : Conflit d\'emploi du temps détecté !');
              subtitle.css('color', '#B91C1C').text('Ce créneau horaire chevauche un ou plusieurs cours existants :');

              res.conflicts.forEach(function(c) {
                var itemHtml = '<div style="background:#FFFFFF; border:1px solid #FECACA; border-radius:8px; padding:10px 14px; font-size:12.5px; color:#7F1D1D;">' +
                  '<div style="font-weight:800; display:flex; align-items:center; gap:6px; margin-bottom:3px;">' +
                  '<span class="badge" style="background:#DC2626; color:#FFF; font-size:10.5px; padding:2px 6px; border-radius:4px; text-transform:uppercase;">' + c.type + '</span> ' +
                  c.title +
                  '</div>' +
                  '<div>' + c.message + '</div>' +
                  '</div>';
                list.append(itemHtml);
              });

              $('#btn-submit-emploi, #btn-submit-emploi-new').prop('disabled', true).css('opacity', '0.6').attr('title', 'Veuillez résoudre le conflit avant d\'enregistrer');
              banner.stop(true, true).slideDown(250);
            } else {
              banner.css({ 'background': '#F0FDF4', 'border': '1.5px solid #BBF7D0' });
              iconBox.css({ 'background': '#DCFCE7', 'color': '#15803D' });
              icon.attr('data-lucide', 'check-circle-2');
              title.css('color', '#166534').text('Créneau Parfaitement Disponible');
              subtitle.css('color', '#15803D').text('La salle, l\'enseignant et la classe sont 100% libres sur cette plage horaire.');
              $('#btn-submit-emploi, #btn-submit-emploi-new').prop('disabled', false).css('opacity', '1').removeAttr('title');
              banner.stop(true, true).slideDown(250);
            }
            if (window.lucide) lucide.createIcons();
          }
        }
      });
    }, 150);
  }

  // 3. Mise à jour dynamique du bandeau de classe en cours & sync session
  function updateActiveClassBanner() {
    var selText = $('#sel_cls_et option:selected').text();
    var val = $('#sel_cls_et').val();

    if (val && selText && val !== '') {
      $('#active-class-title').text(selText);
      $('#active-class-badge').show();
      
      $.ajax({
        url: window.RACINE + 'emploi/setSessionClasse',
        type: 'GET',
        data: { classe_code: val }
      });
    } else {
      $('#active-class-title').text('Aucune classe sélectionnée');
      $('#active-class-badge').hide();
    }
  }

  // 4. Live Schedule Preview Widget Logic
  var activePreviewMode = 'class'; // 'class' ou 'teacher'

  function updateDraftSlotPreview() {
    $('.draft-slot-box').hide();

    var jour = $('select[name="jour"]').val();
    var hDebut = $('input[name="heure_debut"]').val();
    var hFin = $('input[name="heure_fin"]').val();
    var matText = $('#sel_mat_et option:selected').text();
    var salText = $('#sel_sal_et option:selected').text();

    if (jour && hDebut && hFin) {
      var dayKey = jour.toLowerCase();
      var $draftBox = $('#draft-slot-' + dayKey);
      if ($draftBox.length) {
        $draftBox.find('.draft-slot-time').text(hDebut.substring(0,5) + ' - ' + hFin.substring(0,5));
        
        var cleanMat = (matText && matText.indexOf('--') === -1) ? matText : 'Nouveau Créneau';
        var cleanSal = (salText && salText.indexOf('--') === -1) ? salText : '-';
        
        $draftBox.find('.draft-slot-mat').text(cleanMat);
        $draftBox.find('.draft-slot-room').text(cleanSal);
        $draftBox.stop(true, true).fadeIn(150);
        if (window.lucide) lucide.createIcons();
      }
    }
  }

  function loadSchedulePreview() {
    $('.day-slots-container').empty();
    $('#preview-loading-spinner').show();

    var url = '';
    var params = {};

    if (activePreviewMode === 'class') {
      var classeCode = $('#sel_cls_et').val();
      if (!classeCode) {
        $('#preview-loading-spinner').hide();
        return;
      }
      url = window.RACINE + 'emploi/apiSlots';
      params = { classe_code: classeCode };
    } else {
      var ensCode = $('#sel_ens_et').val();
      if (!ensCode) {
        $('#preview-loading-spinner').hide();
        return;
      }
      url = window.RACINE + 'emploi/getTeacherSchedule';
      params = { enseignant_code: ensCode };
    }

    $.ajax({
      url: url,
      type: 'GET',
      data: params,
      dataType: 'json',
      success: function(res) {
        $('#preview-loading-spinner').hide();
        var items = res.data || res.items || [];
        var currentId = $('input[name="id_emploi"]').val();

        items.forEach(function(item) {
          if (currentId && (item.id_emploi == currentId || item.id == currentId)) {
            return; // ne pas dupliquer le créneau en cours d'édition
          }

          var dayKey = (item.jour || '').toLowerCase();
          var $container = $('#day-slots-' + dayKey);

          if ($container.length) {
            var hDeb = item.heure_debut ? item.heure_debut.substring(0,5) : '';
            var hFin = item.heure_fin ? item.heure_fin.substring(0,5) : '';
            var mat = item.libelle_matiere || item.matiere_code || 'Cours';
            var sal = item.libelle_salle || item.salle_code || '';
            var extraInfo = (activePreviewMode === 'class') ? (item.nom_prof || '') : (item.libelle_classe || '');

            var cardHtml = '<div class="preview-slot-card" data-id="' + (item.id_emploi || item.id) + '" style="background:#F8FAFC; border-left:3.5px solid #1E3A5F; border:1px solid #E2E8F0; border-left-width:3.5px; border-left-color:#1E3A5F; border-radius:8px; padding:8px 10px; font-size:11px; box-shadow:0 1px 3px rgba(0,0,0,0.03); margin-bottom:6px;">' +
              '<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:4px;">' +
                '<div style="font-weight:800; color:#0F172A; line-height:1.2;">' + mat + '</div>' +
                '<button type="button" class="btn-delete-slot" data-id="' + (item.id_emploi || item.id) + '" style="background:#FEE2E2; color:#DC2626; border:none; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s ease; flex-shrink:0;" title="Supprimer ce créneau">' +
                  '<i data-lucide="trash-2" style="width:12px; height:12px;"></i>' +
                '</button>' +
              '</div>' +
              '<div style="color:#2563EB; font-weight:800; font-size:10.5px; margin-top:3px;">' + hDeb + ' - ' + hFin + '</div>' +
              '<div style="color:#64748B; font-size:10px; margin-top:2px; font-weight:500;">' + sal + (extraInfo ? (' • ' + extraInfo) : '') + '</div>' +
              '</div>';

            $container.append(cardHtml);
          }
        });

        updateDraftSlotPreview();
        if (window.lucide) lucide.createIcons();
      },
      error: function() {
        $('#preview-loading-spinner').hide();
      }
    });
  }

  // Toggle Mode Aperçu Classe / Enseignant
  $('#btn-view-class').on('click', function() {
    activePreviewMode = 'class';
    $(this).css({ 'background': '#1E3A5F', 'color': '#FFFFFF' }).addClass('active');
    $('#btn-view-teacher').css({ 'background': 'transparent', 'color': '#475569' }).removeClass('active');
    loadSchedulePreview();
  });

  $('#btn-view-teacher').on('click', function() {
    activePreviewMode = 'teacher';
    $(this).css({ 'background': '#1E3A5F', 'color': '#FFFFFF' }).addClass('active');
    $('#btn-view-class').css({ 'background': 'transparent', 'color': '#475569' }).removeClass('active');
    loadSchedulePreview();
  });

  // Événements en Temps Réel (input, keyup, change)
  $('#sel_cls_et').on('change select2:select select2:clear', function() {
    updateActiveClassBanner();
    checkAndAutoSelectTeacher();
    checkScheduleConflicts();
    loadSchedulePreview();
  });

  $('#sel_mat_et').on('change select2:select select2:clear', function() {
    checkAndAutoSelectTeacher();
    checkScheduleConflicts();
    updateDraftSlotPreview();
  });

  $('#sel_ens_et').on('change select2:select select2:clear', function() {
    checkScheduleConflicts();
    if (activePreviewMode === 'teacher') {
      loadSchedulePreview();
    } else {
      updateDraftSlotPreview();
    }
  });

  $('#sel_sal_et, select[name="jour"]').on('change select2:select select2:clear', function() {
    checkScheduleConflicts();
    updateDraftSlotPreview();
  });

  $('input[name="heure_debut"], input[name="heure_fin"]').on('input keyup change blur', function() {
    checkScheduleConflicts();
    updateDraftSlotPreview();
  });

  // 5. Suppression Unitaire d'un Créneau depuis l'aperçu
  $(document).on('click', '.btn-delete-slot', function(e) {
    e.preventDefault();
    var id = $(this).attr('data-id') || $(this).data('id');
    var $card = $(this).closest('.preview-slot-card');

    if (!id) {
      if (window.toastr) toastr.error('ID du créneau introuvable');
      return;
    }

    function performDelete() {
      $.ajax({
        url: window.RACINE + 'emploi/delete',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { id: id, csrf_token: csrfToken },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Créneau supprimé avec succès');
            $card.slideUp(200, function() { $(this).remove(); });
            checkScheduleConflicts();
            loadSchedulePreview();
          } else {
            if (window.Swal) {
              Swal.fire({ icon: 'error', title: 'Erreur', text: res.message || 'Erreur lors de la suppression' });
            } else if (window.toastr) toastr.error(res.message || 'Erreur lors de la suppression');
          }
        }
      });
    }

    if (window.Swal) {
      Swal.fire({
        title: 'Supprimer ce créneau ?',
        text: 'Voulez-vous vraiment retirer ce cours du planning ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      }).then(function(result) {
        if (result.isConfirmed) performDelete();
      });
    } else {
      if (confirm('Voulez-vous vraiment supprimer ce créneau horaire ?')) {
        performDelete();
      }
    }
  });

  // 6. Réinitialisation Globale de l'Emploi du Temps d'une Classe
  $('#btn-reset-schedule').on('click', function() {
    var classeCode = $('#sel_cls_et').val();
    var classeName = $('#sel_cls_et option:selected').text();

    if (!classeCode) {
      var msgCls = 'Veuillez d\'abord sélectionner une classe.';
      if (window.Swal) {
        Swal.fire({ icon: 'info', title: 'Information', text: msgCls });
      } else if (typeof showToast === 'function') {
        showToast(msgCls, 'warning');
      } else if (window.toastr) {
        toastr.warning(msgCls);
      }
      return;
    }

    function performReset() {
      $.ajax({
        url: window.RACINE + 'emploi/resetClasseSchedule',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { classe_code: classeCode, csrf_token: csrfToken },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.Swal) {
              Swal.fire({
                icon: 'success',
                title: 'Emploi du temps réinitialisé !',
                text: 'Tous les créneaux horaires de la classe ont été purgés avec succès.',
                timer: 1800,
                showConfirmButton: false
              });
            } else if (window.toastr) toastr.success(res.message || 'Emploi du temps réinitialisé !');
            loadSchedulePreview();
            checkScheduleConflicts();
          } else {
            if (window.Swal) {
              Swal.fire({ icon: 'error', title: 'Erreur', text: res.message || 'Erreur lors de la réinitialisation' });
            } else if (window.toastr) toastr.error(res.message || 'Erreur lors de la réinitialisation');
          }
        }
      });
    }

    if (window.Swal) {
      Swal.fire({
        title: 'ATTENTION : Vider le planning ?',
        html: 'Voulez-vous vraiment supprimer <b>TOUS</b> les créneaux horaires de la classe <br><b style="color:#DC2626; font-size:16px;">"' + htmlEntities(classeName) + '"</b> ?<br><br><small style="color:#64748B;">Cette action est définitive et irréversible.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, Vider Tout',
        cancelButtonText: 'Annuler'
      }).then(function(result) {
        if (result.isConfirmed) performReset();
      });
    } else {
      if (confirm('ATTENTION : Voulez-vous vraiment supprimer TOUS les créneaux horaires de la classe "' + classeName + '" ?')) {
        performReset();
      }
    }
  });

  function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // 7. Soumission du Formulaire 100% AJAX (Sans rechargement de page)
  $('#form-emploi').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $(document.activeElement);
    var submitAction = $submitBtn.val() || 'save';
    var formData = $form.serializeArray();
    formData.push({ name: 'submit_action', value: submitAction });

    var $btns = $form.find('button[type="submit"]');
    $btns.prop('disabled', true).css('opacity', '0.7');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $.param(formData),
      dataType: 'json',
      success: function(res) {
        $btns.prop('disabled', false).css('opacity', '1');
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Créneau enregistré avec succès !');

          if (submitAction === 'save' && res.redirect) {
            window.location.href = res.redirect;
            return;
          }

          // Enregistrer & Créer un autre (100% AJAX) : Réinitialiser uniquement le créneau
          $('#sel_mat_et').val('').trigger('change.select2');
          $('#sel_sal_et').val('').trigger('change.select2');
          $('input[name="heure_debut"]').val('');
          $('input[name="heure_fin"]').val('');
          $('#schedule-conflict-banner').slideUp(200);

          // Rafraîchir instantanément l'aperçu
          loadSchedulePreview();
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function() {
        $btns.prop('disabled', false).css('opacity', '1');
        if (window.toastr) toastr.error('Erreur réseau lors de l\'enregistrement');
      }
    });
  });

  // Exécution initiale si des champs sont déjà renseignés
  if ($('#sel_cls_et').val()) {
    checkAndAutoSelectTeacher();
    loadSchedulePreview();
  }
  updateDraftSlotPreview();
  if ($('input[name="heure_debut"]').val() && $('input[name="heure_fin"]').val()) {
    checkScheduleConflicts();
  }

  }

  
  // =========================================================================
  // 3. VUE : LISTE DES ABSENCES (views/absences/list.php)
  // =========================================================================
  function initAbsencesListModule() {
    if (!$('#table-absences, #absences-list-config').length) return;

  var cfg = {};
  try {
    var cfgEl = document.getElementById('absences-list-config');
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

  var table = $('#table-absences').DataTable({
    ajax: {
      url: window.RACINE + 'absence/apiList',
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
      { data: 'nom_etudiant', defaultContent: '', render: function(d, t, r) {
        var name = (d && d.trim()) ? d : (r.etudiant_code || r.inscription_code || '-');
        var mat = r.matricule_etudiant ? ' <small style="color:#64748B;">(' + r.matricule_etudiant + ')</small>' : '';
        return '<strong>' + name + '</strong>' + mat;
      }},
      { data: 'libelle_matiere', defaultContent: '', render: function(d, t, r) { return d || r.matiere_code || '-'; } },
      { data: 'date_absence', defaultContent: '-', width: '110px', render: function(d) {
        return d ? '<strong>' + new Date(d).toLocaleDateString('fr-FR') + '</strong>' : '-';
      } },
      { data: 'duree_heures', defaultContent: '-', width: '80px', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '0') + ' h</strong>';
      } },
      { data: 'justifiee', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isJustifiee = (d === 'oui');
        var checkedAttr = isJustifiee ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isJustifiee ? 'Justifiée - Cliquez pour marquer non justifiée' : 'Non justifiée - Cliquez pour justifier') + '">' +
               '<input type="checkbox" class="toggle-statut-absence" data-id="' + row.id_absence + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isJustifiee ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isJustifiee ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'absence/edition/' + (d.editId || d.id_absence) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + window.RACINE + 'absence/details/' + (d.editId || d.id_absence) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: window.RACINE + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.toggle-statut-absence', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: window.RACINE + 'absence/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: isChecked ? 'oui' : 'non',
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
  // 4. VUE : ÉDITION UNITAIRE D'UNE ABSENCE (views/absences/edit.php)
  // =========================================================================
  function initAbsencesEditModule() {
    if (!$('#sel_classe_ab, #absences-edit-config').length) return;

  if (window.lucide) lucide.createIcons();

  var cfg = {};
  try {
    var cfgEl = document.getElementById('absences-edit-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}

  var studentsByClass = cfg.studentsByClass || {};
  var selectedEtudCode = cfg.selectedEtudiantCode || '';

  if ($.fn.select2) {
    $('#sel_classe_ab, #sel_mat_ab').select2({ width: '100%' });
  }

  function loadStudentsForClasse(classeCode, keepSelected) {
    var $el = $('#sel_etud_ab');
    $el.empty();
    
    if (!classeCode || !studentsByClass[classeCode]) {
      $el.append('<option value="">-- Veuillez d\'abord choisir une classe --</option>');
      $el.prop('disabled', true);
    } else {
      $el.append('<option value="">-- Choisir un élève de la classe --</option>');
      var list = studentsByClass[classeCode];
      $.each(list, function(i, st) {
        var isSelected = (keepSelected && st.code_etudiant === selectedEtudCode) ? 'selected' : '';
        $el.append('<option value="' + st.code_etudiant + '" ' + isSelected + '>' + st.nom_affiche + '</option>');
      });
      $el.prop('disabled', false);
    }
    
    if ($.fn.select2) {
      $el.select2({ placeholder: "-- Choisir un élève --", width: '100%' });
    }
  }

  $('#sel_classe_ab').on('change', function() {
    loadStudentsForClasse($(this).val(), false);
  });

  var initialClasse = $('#sel_classe_ab').val();
  if (initialClasse) {
    loadStudentsForClasse(initialClasse, true);
  } else {
    loadStudentsForClasse('', false);
  }

  }

  // =========================================================================
  // 5. VUE : SAISIE DES ABSENCES PAR CLASSE (views/absences/saisie_classe.php)
  // =========================================================================
  function initAbsencesSaisieClasseModule() {
    if (!$('#form-save-batch-absences, .check-absent').length) return;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  $(document).on('change', '.check-absent', function() {
    var isAbsent = $(this).is(':checked');
    var $row = $(this).closest('tr');
    var $badge = $row.find('.badge-status');
    var $justSelect = $row.find('.select-justifiee');
    var $motifInput = $row.find('.input-motif');

    if (isAbsent) {
      $badge.text('ABSENT').css({ 'background': '#FEE2E2', 'color': '#991B1B' });
      $justSelect.prop('disabled', false);
      $motifInput.prop('disabled', false);
    } else {
      $badge.text('PRÉSENT').css({ 'background': '#DCFCE7', 'color': '#166534' });
      $justSelect.prop('disabled', true);
      $motifInput.prop('disabled', true);
    }
  });

  $('#btn-all-present').on('click', function() {
    $('.check-absent').prop('checked', false).trigger('change');
  });

  $('#form-save-batch-absences').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement en cours...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'absence/saveBatch',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Registre des absences enregistré avec succès !');
          setTimeout(function() { window.location.href = window.RACINE + 'absence/list'; }, 1000);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          $btn.prop('disabled', false).html('<i data-lucide="check-circle-2"></i> Enregistrer le Registre des Absences');
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur de connexion au serveur');
        $btn.prop('disabled', false).html('<i data-lucide="check-circle-2"></i> Enregistrer le Registre des Absences');
        if (window.lucide) lucide.createIcons();
      }
    });
  });

  }

  // =========================================================================
  // 6. VUE : LISTE DES AFFECTATIONS ENSEIGNANT-MATIÈRE (views/enseignant_matiere/list.php)
  // =========================================================================
  function initEnseignantMatiereListModule() {
    if (!$('#table-enseignant_matiere, #enseignant-matiere-list-config').length) return;

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

  }

  // =========================================================================
  // 7. VUE : ÉDITION UNITAIRE AFFECTATION ENSEIGNANT-MATIÈRE (views/enseignant_matiere/edit.php)
  // =========================================================================
  function initEnseignantMatiereEditModule() {
    if (!$('#sel_ens_em, #sel_mat_em, #sel_cls_em').length) return;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_ens_em').select2({ placeholder: "-- Rechercher un enseignant --", allowClear: true, width: '100%' });
    $('#sel_mat_em').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_cls_em').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
  }

  }

    // =========================================================================
  // 8. VUE : LISTE & GESTION DES SALLES DE COURS (views/salles/list.php)
  // =========================================================================
  function initSallesListModule() {
    if (!$('#table-salles, #salles-list-config').length) return;

  var configEl = document.getElementById('salles-list-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var racine = config.racine || window.RACINE || '/';
  var csrfToken = config.csrfToken || '';

  if (window.lucide) lucide.createIcons();

  var table = $('#table-salles').DataTable({
    ajax: racine + 'salle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_salle', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_salle', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'capacite_salle', width: '100px', className: 'text-center', render: function(d) {
        if (!d || d <= 0) return '<span style="color:#94A3B8;">-</span>';
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:700; font-size:12px; padding:4px 8px; border-radius:6px; border:1px solid #BFDBFE;">' + d + ' places</span>';
      }},
      { data: 'statut_salle', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-salle" data-id="' + row.id_salle + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        var safeLibelle = $('<div>').text(d.libelle_salle || '').html();
        var safeCapacite = d.capacite_salle || '';
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-salle" data-id="' + d.id_salle + '" data-libelle="' + safeLibelle + '" data-capacite="' + safeCapacite + '" data-statut="' + (d.statut_salle || 'actif') + '" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>' +
               '<a href="' + racine + 'salle/details/' + (d.editId || d.id_salle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Modal : Ajouter
  $('.btn-add-salle').on('click', function() {
    $('#form-salle')[0].reset();
    $('#salle_id').val('');
    $('#modal-salle-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Salle de cours');
    $('#modal-salle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#salle_libelle').focus(); }, 100);
  });

  // Modal : Modifier
  $(document).on('click', '.btn-edit-salle', function() {
    var id = $(this).data('id');
    var libelle = $(this).data('libelle');
    var capacite = $(this).data('capacite');
    var statut = $(this).data('statut');

    $('#salle_id').val(id);
    $('#salle_libelle').val(libelle);
    $('#salle_capacite').val(capacite);
    $('#salle_statut').val(statut || 'actif');
    $('#modal-salle-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier Salle de cours');
    $('#modal-salle').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#salle_libelle').focus(); }, 100);
  });

  // Modal : Fermer
  $('.btn-close-modal-salle').on('click', function() {
    $('#modal-salle').css('display', 'none');
  });
  $('#modal-salle').on('click', function(e) {
    if ($(e.target).is('#modal-salle')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX (Ajout & Modification)
  $('#form-salle').on('submit', function(e) {
    e.preventDefault();
    var isEdit = !!$('#salle_id').val();
    var url = isEdit ? (racine + 'salle/edit') : (racine + 'salle/add');
    var $btn = $('#btn-save-salle');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $('#modal-salle').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-salle', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'salle/changer',
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

  $(document).ready(function() {
    initEmploisTempsListModule();
    initEmploisTempsEditModule();
    initAbsencesListModule();
    initAbsencesEditModule();
    initAbsencesSaisieClasseModule();
    initEnseignantMatiereListModule();
    initEnseignantMatiereEditModule();
    initSallesListModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);