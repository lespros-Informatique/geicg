/**
 * ==========================================================================
 * GEICG - MODULE ÉDITION TRANCHE SCOLARITÉ (tranches_edit.js)
 * Externalisation JS stricte - Zéro JS inline dans les vues
 * ==========================================================================
 */

(function(window, $) {
  'use strict';

  var GEICG = window.GEICG || {};

  function initTrancheEditModule() {
    var $form = $('#form-tranche');
    if (!$form.length) return;

    if (window.lucide) lucide.createIcons();
    if ($.fn.select2) {
      $('.select2').select2({
        placeholder: "-- Rechercher / Choisir --",
        allowClear: true,
        width: '100%'
      });
    }

    $form.on('submit', function(e) {
      e.preventDefault();
      var $btn = $('#btn-submit-tranche');
      var originalHtml = $btn.html();

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:14px;height:14px;margin-right:6px;"></span> Enregistrement...');

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          if (res && (res.status === 1 || res.status === 'success' || res.success)) {
            var successMsg = res.message || 'Tranche enregistrée avec succès !';
            var targetUrl = res.redirect || GEICG.url('scolarite/list?tab=tranches');

            if (window.Swal) {
              Swal.fire({
                icon: 'success',
                title: 'Succès !',
                text: successMsg,
                timer: 1500,
                showConfirmButton: false
              }).then(function() {
                window.location.href = targetUrl;
              });
            } else {
              if (window.toastr) {
                toastr.success(successMsg);
              } else {
                alert(successMsg);
              }
              setTimeout(function() {
                window.location.href = targetUrl;
              }, 800);
            }
          } else {
            $btn.prop('disabled', false).html(originalHtml);
            var errMsg = (res && res.message) ? res.message : 'Erreur lors de l\'enregistrement.';
            if (window.Swal) {
              Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: errMsg
              });
            } else if (window.toastr) {
              toastr.error(errMsg);
            } else {
              alert(errMsg);
            }
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html(originalHtml);
          var errMsg = 'Une erreur est survenue lors de la communication avec le serveur.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errMsg = xhr.responseJSON.message;
          }
          if (window.Swal) {
            Swal.fire({
              icon: 'error',
              title: 'Erreur serveur',
              text: errMsg
            });
          } else if (window.toastr) {
            toastr.error(errMsg);
          } else {
            alert(errMsg);
          }
        }
      });
    });
  }

  $(document).ready(initTrancheEditModule);

})(window, window.jQuery);
