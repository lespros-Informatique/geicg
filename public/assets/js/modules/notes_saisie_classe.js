var isFormDirty = false;
var isInitialLoading = true;

$(document).ready(function() {
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
});

