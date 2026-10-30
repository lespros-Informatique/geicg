/**
 * Module JS : Création / Modification d'une Permission Granulaire (views/permissions/edit.php)
 */
var isCustomModule = false;
window.toggleModuleInput = function() {
  isCustomModule = !isCustomModule;
  if (isCustomModule) {
    $('#bloc-select-module').hide();
    $('#bloc-text-module').show();
    $('#input_nouveau_module').focus();
    $('#btn-toggle-module').html('<i data-lucide="list" style="width:14px;height:14px;"></i> Choisir un module existant');
    $('#help-module').text('Saisissez le nom en lettres majuscules (ex: BIBLIOTHEQUE).');
  } else {
    $('#bloc-text-module').hide();
    $('#input_nouveau_module').val('');
    $('#bloc-select-module').show();
    $('#btn-toggle-module').html('<i data-lucide="plus-circle" style="width:14px;height:14px;"></i> + Créer un nouveau module');
    $('#help-module').text('Choisissez parmi les modules existants ou cliquez ci-dessus pour en créer un nouveau.');
  }
  if (window.lucide) lucide.createIcons();
};

$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();
});

