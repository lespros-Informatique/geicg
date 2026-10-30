$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  $(document).on('change', '.check-absent', function() {
    var isAbsent = $(this).is(':checked');
    var $row = $(this).closest('tr');
    var $badge = $row.find('.badge-status');
    var $justSelect = $row.find('.select-justifiee');
    var $motifInput = $row.find('.input-motif');

    if (isAbsent) {
      $badge.text('ABSENT').css({ 'background': '#FEE2E2', 'color': '#991B1B' });
      $justSelect.prop('disabled', false);
      $motifInput.prop('disabled', false);
    } else {
      $badge.text('PRÉSENT').css({ 'background': '#DCFCE7', 'color': '#166534' });
      $justSelect.prop('disabled', true);
      $motifInput.prop('disabled', true);
    }
  });

  $('#btn-all-present').on('click', function() {
    $('.check-absent').prop('checked', false).trigger('change');
  });

  $('#form-save-batch-absences').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement en cours...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'absence/saveBatch',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Registre des absences enregistré avec succès !');
          setTimeout(function() { window.location.href = window.RACINE + 'absence/list'; }, 1000);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          $btn.prop('disabled', false).html('<i data-lucide="check-circle-2"></i> Enregistrer le Registre des Absences');
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur de connexion au serveur');
        $btn.prop('disabled', false).html('<i data-lucide="check-circle-2"></i> Enregistrer le Registre des Absences');
        if (window.lucide) lucide.createIcons();
      }
    });
  });
});

