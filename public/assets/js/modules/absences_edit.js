$(document).ready(function() {
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
});

