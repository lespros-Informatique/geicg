/**
 * Module JS: Édition Parent / Tuteur
 * Externalisation stricte du code JS pour views/parents/edit.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Rechercher / Choisir un étudiant --",
      allowClear: true,
      width: '100%'
    });
  }
});
