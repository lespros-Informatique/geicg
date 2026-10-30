/**
 * Module JS: Édition de Bulletin de Notes
 * Externalisation stricte du code JS pour views/bulletin/edit.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_etu_bu').select2({ placeholder: "-- Rechercher un étudiant --", allowClear: true, width: '100%' });
    $('#sel_cls_bu').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
  }
});
