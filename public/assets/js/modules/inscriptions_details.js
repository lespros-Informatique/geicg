/**
 * Module JS: Détails de l'Inscription
 * Externalisation stricte du code JS pour views/inscriptions/details.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('print')) {
    setTimeout(function() { window.print(); }, 400);
  }
});
