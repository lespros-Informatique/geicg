/**
 * Module Maquettes Pédagogiques & Coefficients (GEICG)
 */
(function(window, document, $) {
  'use strict';

  var maquetteTable = null;

  // Configuration globale
  function getConfig() {
    var config = window.MAQUETTE_CONFIG || {};
    var configEl = document.getElementById('maquette-list-config');
    if (configEl) {
      try {
        var parsed = JSON.parse(configEl.textContent);
        config = Object.assign({}, config, parsed);
      } catch (e) {}
    }
    var racine = window.RACINE || config.racine || '/geicg/';
    var csrfToken = config.csrfToken || '';
    return {
      racine: racine.endsWith('/') ? racine : racine + '/',
      csrfToken: csrfToken
    };
  }

  // Fonctions d'ouverture / fermeture des modales accessibles globalement
  window.openModalMaquette = function() {
    var form = document.getElementById('form-maquette');
    if (form) form.reset();
    $('#maq_id').val('');
    $('#modal-maquette-title').html('<i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Ajouter Matière à la Maquette');

    // Pré-sélectionner selon les filtres actifs
    var currentAnnee = $('#filter-annee').val();
    var currentFiliere = $('#filter-filiere').val();
    var currentNiveau = $('#filter-niveau').val();
    if (currentAnnee) $('#maq_annee').val(currentAnnee).trigger('change');
    if (currentFiliere) $('#maq_filiere').val(currentFiliere).trigger('change');
    if (currentNiveau) $('#maq_niveau').val(currentNiveau).trigger('change');

    $('#modal-maquette').css('display', 'flex');

    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }
  };

  window.closeModalMaquette = function() {
    $('#modal-maquette').css('display', 'none');
  };

  window.openModalDuplicateMaquette = function() {
    $('#modal-dupliquer-maquette').css('display', 'flex');
    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }
  };

  window.closeModalDuplicateMaquette = function() {
    $('#modal-dupliquer-maquette').css('display', 'none');
  };

  window.openEditMaquetteModal = function(id, annee, filiere, niveau, matiere, categorie, coef) {
    $('#maq_id').val(id);
    if (annee) $('#maq_annee').val(annee).trigger('change');
    if (filiere) $('#maq_filiere').val(filiere).trigger('change');
    if (niveau) $('#maq_niveau').val(niveau).trigger('change');
    if (matiere) $('#maq_matiere').val(matiere).trigger('change');
    $('#maq_categorie').val(categorie || 'GENERALE');
    $('#maq_coef').val(coef || '1.0');

    $('#modal-maquette-title').html('<i data-lucide="edit" style="width:18px;height:18px;"></i> Modifier Matière de la Maquette');
    $('#modal-maquette').css('display', 'flex');

    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }
  };

  // Event Listeners globaux (attachés immédiatement sur document)
  $(document).on('click', '.btn-add-maquette', function(e) {
    e.preventDefault();
    window.openModalMaquette();
  });

  $(document).on('click', '.btn-close-modal-maquette', function(e) {
    e.preventDefault();
    window.closeModalMaquette();
  });

  $(document).on('click', '#modal-maquette', function(e) {
    if ($(e.target).is('#modal-maquette')) {
      window.closeModalMaquette();
    }
  });

  $(document).on('click', '.btn-duplicate-maquette', function(e) {
    e.preventDefault();
    window.openModalDuplicateMaquette();
  });

  $(document).on('click', '.btn-close-modal-dup', function(e) {
    e.preventDefault();
    window.closeModalDuplicateMaquette();
  });

  $(document).on('click', '#modal-dupliquer-maquette', function(e) {
    if ($(e.target).is('#modal-dupliquer-maquette')) {
      window.closeModalDuplicateMaquette();
    }
  });

  $(document).on('click', '.btn-edit-maq', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var annee = $(this).data('annee');
    var filiere = $(this).data('filiere');
    var niveau = $(this).data('niveau');
    var matiere = $(this).data('matiere');
    var categorie = $(this).data('categorie');
    var coef = $(this).data('coef');
    window.openEditMaquetteModal(id, annee, filiere, niveau, matiere, categorie, coef);
  });

  // Soumission Formulaire Maquette (Ajout / Édition)
  $(document).on('submit', '#form-maquette', function(e) {
    e.preventDefault();
    var cfg = getConfig();
    var isEdit = !!$('#maq_id').val();
    var url = cfg.racine + (isEdit ? 'maquette/edit' : 'maquette/add');
    var $btn = $('#btn-save-maquette');

    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
    if (window.lucide) { try { lucide.createIcons(); } catch(e) {} }

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) { try { lucide.createIcons(); } catch(e) {} }

        if (res.status === 1) {
          if (window.toastr) toastr.success(res.message || 'Matière enregistrée avec succès');
          window.closeModalMaquette();
          if (maquetteTable) maquetteTable.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
        if (window.lucide) { try { lucide.createIcons(); } catch(e) {} }
        var msg = 'Erreur technique serveur';
        try {
          var j = JSON.parse(xhr.responseText);
          if (j.message) msg = j.message;
        } catch(e) {}
        if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Soumission Duplication Maquette
  $(document).on('submit', '#form-duplicate-maquette', function(e) {
    e.preventDefault();
    var cfg = getConfig();
    var $btn = $('#btn-save-duplicate');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Reconduction en cours...');
    if (window.lucide) { try { lucide.createIcons(); } catch(e) {} }

    $.ajax({
      url: cfg.racine + 'maquette/dupliquer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction');
        if (window.lucide) { try { lucide.createIcons(); } catch(e) {} }

        if (res.status === 1) {
          if (window.toastr) toastr.success(res.message);
          window.closeModalDuplicateMaquette();
          if (maquetteTable) maquetteTable.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de la reconduction');
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction');
        if (window.lucide) { try { lucide.createIcons(); } catch(e) {} }
        if (window.toastr) toastr.error('Erreur réseau ou serveur');
      }
    });
  });

  // Basculer Statut via Ajax
  $(document).on('change', '.toggle-statut-maq', function() {
    var cfg = getConfig();
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: cfg.racine + 'maquette/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { id: id, csrf_token: cfg.csrfToken },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          if (window.toastr) toastr.success(res.message);
          if (maquetteTable) maquetteTable.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message);
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  // Supprimer Matière de la Maquette
  $(document).on('click', '.btn-delete-maq', function() {
    var cfg = getConfig();
    var id = $(this).data('id');
    var nom = $(this).data('nom');
    if (!confirm('Êtes-vous sûr de vouloir retirer la matière "' + nom + '" de cette maquette pédagogique ?')) {
      return;
    }

    $.ajax({
      url: cfg.racine + 'maquette/delete',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { id: id, csrf_token: cfg.csrfToken },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          if (window.toastr) toastr.success(res.message);
          if (maquetteTable) maquetteTable.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
      }
    });
  });

  // Initialisation complète au chargement de la page
  function initMaquettesModule() {
    if (!$('#table-maquette').length) return;

    var cfg = getConfig();

    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }

    // Initialisation Select2 propre
    if ($.fn.select2) {
      try {
        $('#filter-annee, #filter-filiere, #filter-niveau, #filter-categorie').select2({
          width: '100%'
        });
        $('#maq_annee, #maq_filiere, #maq_niveau, #maq_matiere').select2({
          width: '100%',
          dropdownParent: $('#modal-maquette')
        });
        $('#dup_source_annee, #dup_target_annee, #dup_filiere').select2({
          width: '100%',
          dropdownParent: $('#modal-dupliquer-maquette')
        });
      } catch (e) {
        console.warn('Select2 init warning:', e);
      }
    }

    // DataTable Maquettes
    try {
      maquetteTable = $('#table-maquette').DataTable({
        ajax: {
          url: cfg.racine + 'maquette/apiList',
          type: 'GET',
          data: function(d) {
            d.annee_code = $('#filter-annee').val();
            d.filiere_code = $('#filter-filiere').val();
            d.niveau_code = $('#filter-niveau').val();
            d.categorie = $('#filter-categorie').val();
          }
        },
        processing: true,
        autoWidth: false,
        columns: [
          {
            data: null,
            width: '50px',
            render: function(d, type, row, meta) {
              return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
            }
          },
          {
            data: 'libelle_matiere',
            render: function(d, type, row) {
              if (type !== 'display') return d || row.matiere_code || '';
              var matNom = $('<div>').text(d || row.matiere_code || '-').html();
              return '<strong style="color:#0F172A; font-size:13.5px;">' + matNom + '</strong>';
            }
          },
          {
            data: 'slug_filiere',
            render: function(d, type, row) {
              var slug = row.slug_filiere || d || '';
              if (type !== 'display') return slug || row.libelle_filiere || row.filiere_code || '';
              if (slug) {
                return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:800; font-size:12px; padding:4px 8px; border-radius:6px; border:1px solid #BFDBFE;" title="' + (row.libelle_filiere || '').replace(/"/g, '&quot;') + '">' + slug + '</span>';
              }
              return '<span style="color:#64748B;">-</span>';
            }
          },
          {
            data: 'libelle_niveau',
            render: function(d, type, row) {
              if (type !== 'display') return d || row.niveau_code || '';
              return '<span class="badge" style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:3px 8px; border-radius:6px; font-weight:600;">' +
                     $('<div>').text(d || row.niveau_code || '-').html() + '</span>';
            }
          },
          {
            data: 'categorie_matiere',
            className: 'text-center',
            render: function(d) {
              var isGen = (d === 'GENERALE');
              if (isGen) {
                return '<span class="badge" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:4px 10px; border-radius:12px; font-weight:700; font-size:11.5px;">GÉNÉRALE</span>';
              } else {
                return '<span class="badge" style="background:#FFFBEB; color:#B45309; border:1px solid #FDE68A; padding:4px 10px; border-radius:12px; font-weight:700; font-size:11.5px;">PROFESSIONNELLE</span>';
              }
            }
          },
          {
            data: 'coefficient',
            width: '90px',
            className: 'text-center',
            render: function(d) {
              var coef = parseFloat(d) || 1.0;
              return '<span style="display:inline-block; padding:3px 12px; border-radius:6px; font-size:13px; font-weight:800; background:#F8FAFC; color:#0F172A; border:1.5px solid #CBD5E1;">' +
                     coef.toFixed(1) + '</span>';
            }
          },
          {
            data: 'statut_maquette',
            width: '80px',
            className: 'text-center',
            render: function(d, type, row) {
              var isActif = (d === 'actif');
              var checkedAttr = isActif ? 'checked' : '';
              return '<div style="display:flex; justify-content:center; align-items:center;">' +
                     '<label style="position:relative; display:inline-block; width:36px; height:18px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
                     '<input type="checkbox" class="toggle-statut-maq" data-id="' + row.id_maquette + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                     '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                     '<span style="position:absolute; content:\'\'; height:12px; width:12px; left:' + (isActif ? '19px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                     '</span>' +
                     '</label>' +
                     '</div>';
            }
          },
          {
            data: null,
            width: '100px',
            orderable: false,
            className: 'text-end',
            render: function(d, type, row) {
              return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">' +
                     '<button type="button" class="btn btn-sm btn-edit-maq" ' +
                     'data-id="' + row.id_maquette + '" ' +
                     'data-annee="' + (row.annee_code || '') + '" ' +
                     'data-filiere="' + (row.filiere_code || '') + '" ' +
                     'data-niveau="' + (row.niveau_code || '') + '" ' +
                     'data-matiere="' + (row.matiere_code || '') + '" ' +
                     'data-categorie="' + (row.categorie_matiere || 'GENERALE') + '" ' +
                     'data-coef="' + (row.coefficient || '1.0') + '" ' +
                     'title="Modifier la matière" ' +
                     'style="background:#F0F9FF; color:#0284C7; border:none; padding:6px 9px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;">' +
                     '<i data-lucide="edit-3" style="width:15px;height:15px;"></i></button>' +
                     '<button type="button" class="btn btn-sm btn-delete-maq" ' +
                     'data-id="' + row.id_maquette + '" ' +
                     'data-nom="' + $('<div>').text(row.libelle_matiere || '').html() + '" ' +
                     'title="Retirer de la maquette" ' +
                     'style="background:#FEF2F2; color:#DC2626; border:none; padding:6px 9px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;">' +
                     '<i data-lucide="trash-2" style="width:15px;height:15px;"></i></button>' +
                     '</div>';
            }
          }
        ],
        language: { url: cfg.racine + 'public/json/datatables-i18n-fr-FR.json' },
        drawCallback: function() {
          if (window.lucide) {
            try { lucide.createIcons(); } catch(e) {}
          }
        }
      });
    } catch (err) {
      console.error('Erreur initialisation DataTable maquette:', err);
    }

    // Mise à jour de l'URL du bouton imprimer selon les filtres actifs
    function updatePrintUrl() {
      var annee = $('#filter-annee').val() || '';
      var filiere = $('#filter-filiere').val() || '';
      var niveau = $('#filter-niveau').val() || '';
      var cat = $('#filter-categorie').val() || '';

      var params = [];
      if (annee) params.push('annee_code=' + encodeURIComponent(annee));
      if (filiere) params.push('filiere_code=' + encodeURIComponent(filiere));
      if (niveau) params.push('niveau_code=' + encodeURIComponent(niveau));
      if (cat) params.push('categorie=' + encodeURIComponent(cat));

      var q = params.length ? '?' + params.join('&') : '';
      $('#btn-print-maquette').attr('href', cfg.racine + 'maquette/imprimerPdf' + q);
    }

    updatePrintUrl();

    // Rechargement sur changement de filtres
    $('#filter-annee, #filter-filiere, #filter-niveau, #filter-categorie').on('change', function() {
      if (maquetteTable) maquetteTable.ajax.reload();
      updatePrintUrl();
    });
  }

  $(document).ready(function() {
    initMaquettesModule();
  });

})(window, document, jQuery);
