/**
 * Module JS: Fiche Détails de l'Étudiant
 * Externalisation stricte du code JS pour views/etudiants/details.php
 */
function imprimerCarteScolaire() {
  window.print();
}

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('print')) {
    setTimeout(function() { window.print(); }, 400);
  }
});
