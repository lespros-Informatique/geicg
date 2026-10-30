$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Mettre à jour l'URL du bouton PV de classe lors de la sélection d'une classe
  $('#filter-classe').on('change', function() {
    var classeCode = $(this).val();
    if (classeCode) {
      $('#btn-pv-classe').attr('href', window.RACINE + 'bulletin/pvClasse?classe_code=' + encodeURIComponent(classeCode));
    } else {
      $('#btn-pv-classe').attr('href', window.RACINE + 'bulletin/pvClasse');
    }
    table.ajax.reload();
  });

  $('#filter-niveau').on('change', function() {
    var niveauCode = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!niveauCode || !optNiveau || optNiveau === niveauCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#filter-classe option:selected').prop('disabled')) {
      $('#filter-classe').val('').trigger('change.select2');
    } else {
      $('#filter-classe').select2({ width: '100%' });
    }
    table.ajax.reload();
  });

  $('#filter-annee').on('change', function() {
    table.ajax.reload();
  });

  var table = $('#table-bulletin').DataTable({
    ajax: {
      url: window.RACINE + 'bulletin/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'matricule_etudiant', defaultContent: '<span style="color:#94A3B8; font-style:italic;">-</span>', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#1E3A5F;">' + (d || '-') + '</strong>';
      }},
      { data: 'etudiant_nom',    defaultContent: '-', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:600; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'classe_nom',      defaultContent: '-', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || '<span style="color:#94A3B8; font-style:italic;">Non assigné</span>') + '</span>';
      }},
      { data: 'annee_nom',       defaultContent: '-', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#64748B;">' + (d || '-') + '</span>';
      }},
      { data: 'nb_notes',        defaultContent: '0', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || 0;
        var count = parseInt(d) || 0;
        if (count > 0) {
          return '<span style="display:inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #DCFCE7; color: #15803D;">' + count + ' note(s)</span>';
        } else {
          return '<span style="display:inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #F1F5F9; color: #64748B;">0 note</span>';
        }
      }},
      { data: null, width: '210px', orderable: false, render: function(d) {
        var viewBtn = '<a href="' + window.RACINE + 'bulletin/details/' + (d.editId || d.id_inscription) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F;border-color:#1E3A5F;color:#fff;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;"><i data-lucide="file-text" style="width:14px;height:14px;"></i> Voir</a>';
        var pdfBtn = '<a href="' + window.RACINE + 'bulletin/imprimerBulletinPdf/' + (d.editId || d.id_inscription) + '" target="_blank" class="btn btn-sm btn-outline-primary" style="border-color:#1E3A5F;color:#1E3A5F;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;" title="Télécharger le bulletin au format PDF"><i data-lucide="printer" style="width:14px;height:14px;"></i> PDF</a>';
        return '<div style="display:inline-flex;gap:6px;justify-content:flex-end;">' + viewBtn + pdfBtn + '</div>';
      }, className: 'text-end' }
    ],
    language: { url: window.RACINE + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});

