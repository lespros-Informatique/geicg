$(document).ready(function() {
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
});

