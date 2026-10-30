$(document).ready(function() {
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
});

