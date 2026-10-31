/**
 * Module JS Unifie : Notes & Evaluations (Liste, Edition Unitaire & Saisie par Classe)
 * GEICG - Application Web
 */
var isFormDirty = false;
var isInitialLoading = true;

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
    var cls = $('#filter-classe').val();
    if (cls) {
      $('#btn-add-note').attr('href', window.RACINE + 'note/formulaire?classe_code=' + encodeURIComponent(cls));
      $('#btn-saisie-classe').attr('href', window.RACINE + 'note/saisieClasse?classe_code=' + encodeURIComponent(cls));
    } else {
      $('#btn-add-note').attr('href', window.RACINE + 'note/formulaire');
      $('#btn-saisie-classe').attr('href', window.RACINE + 'note/saisieClasse');
    }
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
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'nom_etudiant', defaultContent: '', render: function(d, t, r) {
        var name = (d && d.trim()) ? d : (r.inscription_code || '-');
        var mat = r.matricule_etudiant ? ' <small style="color:#64748B;">(' + r.matricule_etudiant + ')</small>' : '';
        return '<strong>' + name + '</strong>' + mat;
      }},
      { data: 'libelle_matiere', defaultContent: '', render: function(d, t, r) { return d || r.matiere_code || '-'; } },
      { data: 'type_evaluation_code', defaultContent: '-', width: '110px' },
      { data: 'valeur_note', defaultContent: '-', width: '80px', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '0') + '/20</strong>';
      } },
      { data: 'statut_note', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-note" data-id="' + row.id_note + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'note/edition/' + (d.editId || d.id_note) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + window.RACINE + 'note/details/' + (d.editId || d.id_note) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: window.RACINE + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
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

  if (window.lucide) lucide.createIcons();
});
