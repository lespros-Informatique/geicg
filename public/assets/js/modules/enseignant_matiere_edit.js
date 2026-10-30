/**
 * Module JS: Édition d'Affectation Enseignant - Matière
 * Externalisation stricte du code JS pour views/enseignant_matiere/edit.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_ens_em').select2({ placeholder: "-- Rechercher un enseignant --", allowClear: true, width: '100%' });
    $('#sel_mat_em').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_cls_em').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
  }
});
