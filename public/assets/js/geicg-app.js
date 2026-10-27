/**
 * ==========================================================================
 * GEICG - APPLICATION CORE UTILITIES (geicg-app.js)
 * Phase 4 : Centralisation et Allègement des Assets CSS & JS
 * Helpers mutualisés : Sécurité XSS, Formatage Monétaire, DataTables FR
 * ==========================================================================
 */

(function(window, $) {
  'use strict';

  // 1. Échappement HTML sécurisé anti-XSS
  window.escapeHtml = window.escapeHtml || function(text) {
    if (text === null || text === undefined) return '';
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
  };

  // 2. Formatage monétaire standard FCFA
  window.formatMoney = window.formatMoney || function(amount, suffix) {
    var s = (suffix !== undefined) ? suffix : ' F';
    var num = parseFloat(amount) || 0;
    return new Intl.NumberFormat('fr-FR').format(Math.round(num)) + s;
  };

  // 3. Formatage de date FR (JJ/MM/AAAA)
  window.formatDateFr = window.formatDateFr || function(dateStr, includeTime) {
    if (!dateStr) return '-';
    var parts = dateStr.split(' ');
    var dateParts = parts[0].split('-');
    if (dateParts.length === 3) {
      var formatted = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
      if (includeTime && parts[1]) {
        formatted += ' ' + parts[1].substring(0, 5);
      }
      return formatted;
    }
    return dateStr;
  };

  // 4. Traduction officielle française pour jQuery DataTables
  window.geicgDataTableLanguage = {
    processing: "Traitement en cours...",
    search: "Rechercher&nbsp;:",
    lengthMenu: "Afficher _MENU_ éléments",
    info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
    infoEmpty: "Affichage de 0 à 0 sur 0 entrée",
    infoFiltered: "(filtré de _MAX_ entrées au total)",
    infoPostFix: "",
    loadingRecords: "Chargement en cours...",
    zeroRecords: "Aucun élément à afficher",
    emptyTable: "Aucune donnée disponible dans le tableau",
    paginate: {
      first: "Premier",
      previous: "Précédent",
      next: "Suivant",
      last: "Dernier"
    },
    aria: {
      sortAscending: ": activer pour trier la colonne par ordre croissant",
      sortDescending: ": activer pour trier la colonne par ordre décroissant"
    }
  };

})(window, window.jQuery);
