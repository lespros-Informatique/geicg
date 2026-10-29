/**
 * Module JS: Détails du Reçu / Bordereau de Paiement
 * Externalisation stricte du code JS pour views/paiements/details.php
 */
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('print')) {
    setTimeout(function() {
      window.print();
    }, 400);
  }
});
