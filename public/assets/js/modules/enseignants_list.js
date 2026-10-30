$(document).ready(function() {
  var cfg = {};
  try {
    var cfgEl = document.getElementById('enseignants-list-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var csrfToken = cfg.csrfToken || '';

  var table = $('#table-enseignants').DataTable({
    ajax: window.RACINE + 'enseignant/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_enseignant', width: '100px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_enseignant', render: function(d, type, row) {
        if (type !== 'display') return (d || '') + ' ' + (row.prenom_enseignant || '');
        var nomComplet = (d || '') + ' ' + (row.prenom_enseignant || '');
        return '<strong style="color:#0F172A;">' + (nomComplet.trim() || 'Enseignant sans nom') + '</strong>';
      }},
      { data: 'email_enseignant', render: function(d, type, row) {
        if (type !== 'display') return d || row.telephone_enseignant || '';
        var res = '';
        if (d) res += '<div style="font-weight:600; color:#1E3A5F; font-size:13px;">' + d + '</div>';
        if (row.telephone_enseignant) res += '<div style="font-size:12px; color:#64748B;">' + row.telephone_enseignant + '</div>';
        return res || '-';
      }},
      { data: 'grade_enseignant', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:600; color:#334155;">' + (d || 'Enseignant') + '</span>';
      }},
      { data: 'type_contrat', render: function(d, type, row) {
        if (type !== 'display') return d || '';
        var badge = d === 'vacataire' 
          ? '<span style="background:#FEF3C7; color:#92400E; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px;">Vacataire</span>'
          : (d === 'prestataire' ? '<span style="background:#F1F5F9; color:#475569; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px;">Prestataire</span>' : '<span style="background:#E0F2FE; color:#0369A1; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px;">Permanent</span>');
        if (row.numero_autorisation) {
          badge += '<div style="font-size:11px; color:#475569; font-weight:600; margin-top:3px;"><i data-lucide="award" style="width:11px;height:11px;display:inline-block;vertical-align:middle;"></i> ' + row.numero_autorisation + '</div>';
        }
        return badge;
      }},
      { data: 'statut_enseignant', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-ens" data-id="' + row.id_enseignant + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'enseignant/edition/' + (d.editId || d.id_enseignant) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'enseignant/details/' + (d.editId || d.id_enseignant) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Profil</a>';
      }, className: 'text-end' }
    ],
    language: { url: window.RACINE + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-ens', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: window.RACINE + 'enseignant/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msg = res.message || 'Statut mis à jour avec succès';
          if (typeof showToast === 'function') showToast(msg, 'success');
          else if (window.toastr) toastr.success(msg);
          table.ajax.reload(null, false);
        } else {
          var errMsg = res.message || 'Erreur lors du changement de statut';
          if (typeof showToast === 'function') showToast(errMsg, 'error');
          else if (window.toastr) toastr.error(errMsg);
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (typeof showToast === 'function') showToast('Erreur réseau', 'error');
        else if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });
});

