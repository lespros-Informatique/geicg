/**
 * Module JS : Création / Modification d'une Classe (views/classes/edit.php)
 */
$(document).ready(function() {
  var racine = window.RACINE || '/';
  if (window.lucide) lucide.createIcons();

  if ($.fn.select2) {
    $('#sel_filiere_classe').select2({ placeholder: "-- Choisir une filière --", allowClear: true, width: '100%' });
    $('#sel_niveau_classe').select2({ placeholder: "-- Choisir un niveau --", allowClear: true, width: '100%' });
  }

  // Génération automatique intelligente du libellé de la classe
  function updateLibelleClasse() {
    var filiereText = $('#sel_filiere_classe option:selected').data('nom') || $('#sel_filiere_classe option:selected').text().trim();
    var niveauText  = $('#sel_niveau_classe option:selected').data('nom') || $('#sel_niveau_classe option:selected').text().trim();
    
    // Si placeholder sélectionné
    if (!$('#sel_filiere_classe').val()) filiereText = '';
    if (!$('#sel_niveau_classe').val()) niveauText = '';

    if (filiereText && niveauText) {
      $('#libelle_classe_input').val(filiereText + ' - ' + niveauText);
    } else if (filiereText) {
      $('#libelle_classe_input').val(filiereText);
    } else if (niveauText) {
      $('#libelle_classe_input').val(niveauText);
    }
  }

  $('#sel_filiere_classe, #sel_niveau_classe').on('change', function() {
    updateLibelleClasse();
  });

  // Soumission AJAX du formulaire classe (Création & Modification)
  $('#form-edit-classe').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-classe');
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
          if (window.toastr) {
            toastr.success(res.message || 'Classe enregistrée avec succès !');
          }
          var targetUrl = res.redirect || (racine + 'classe/list');
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 500);
        } else {
          $btn.prop('disabled', false).html(originalText);
          if (window.lucide) lucide.createIcons();
          if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de l\'enregistrement de la classe.');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalText);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });
});

