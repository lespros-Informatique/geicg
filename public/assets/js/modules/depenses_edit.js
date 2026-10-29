/**
 * Module JS: Édition d'une Dépense
 * Externalisation stricte du code JS pour views/depenses/edit.php
 */

$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Rechercher la catégorie de dépense --",
      allowClear: true,
      width: '100%'
    });
  }
});
