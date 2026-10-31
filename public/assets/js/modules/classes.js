/**
 * Module JS Unifié : Classes (Liste & Édition)
 * Regroupe la gestion de la liste DataTable, modales, reconduction et formulaire d'édition
 */
$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/');

  // =========================================================================
  // 1. SECTION : LISTE DES CLASSES (Exécutée si #table-classes est présent)
  // =========================================================================
  if ($('#table-classes').length) {
    var configEl = document.getElementById('classes-list-config');
    var config = {};
    if (configEl) {
      try { config = JSON.parse(configEl.textContent); } catch (e) {}
    }
    racine = config.racine || window.RACINE || '/';
    var canEditClasse = !!config.canEditClasse;
    var csrfToken = config.csrfToken || '';
    var currentAnneeCode = config.currentAnneeCode || '';

    var table = $('#table-classes').DataTable({
      ajax: {
        url: racine + 'classe/apiList',
        data: function(d) {
          d.annee_code = $('#filter-annee-classe').val();
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { data: null, width: '50px', render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }},
        { data: 'code_classe', width: '110px', render: function(d, type) {
          if (type !== 'display') return d || '';
          return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
        }},
        { data: 'libelle_classe', render: function(d, type) {
          if (type !== 'display') return d || '';
          return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
        }},
        { data: 'libelle_filiere', render: function(d, type, row) {
          if (type !== 'display') return d || row.filiere_code || '';
          return '<span style="color:#1E3A5F; font-weight:600;">' + (d || row.filiere_code || '-') + '</span>';
        }},
        { data: 'libelle_niveau', render: function(d, type, row) {
          if (type !== 'display') return d || row.niveau_code || '';
          return '<span style="color:#334155; font-weight:500;">' + (d || row.niveau_code || '-') + '</span>';
        }},
        { data: 'capacite_max_classe', width: '90px', className: 'text-center', render: function(d, type) {
          if (type !== 'display') return d || '';
          return '<span style="font-weight:700; color:#475569;">' + (d ? d + ' places' : '-') + '</span>';
        }},
        { data: 'statut_classe', width: '80px', className: 'text-center', render: function(d, type, row) {
          var isActif = (d === 'actif');
          if (!canEditClasse) {
            return isActif
              ? '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:700; padding:4px 8px; border-radius:6px; font-size:11px;">Actif</span>'
              : '<span class="badge" style="background:#FEE2E2; color:#B91C1C; font-weight:700; padding:4px 8px; border-radius:6px; font-size:11px;">Inactif</span>';
          }
          var checked = isActif ? 'checked' : '';
          var idCrypte = row.idCrypte || row.id_classe;
          return '<label class="switch" style="position:relative; display:inline-block; width:34px; height:18px; margin:0;" title="Bascule Statut Actif/Inactif">' +
                 '<input type="checkbox" class="toggle-statut-classe" data-id="' + idCrypte + '" ' + checked + ' style="opacity:0; width:0; height:0;">' +
                 '<span class="slider round" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#16A34A' : '#CBD5E1') + '; transition:.3s; border-radius:18px;"></span>' +
                 '</label>';
        }},
        { data: null, orderable: false, width: '130px', className: 'text-center', render: function(d, type, row) {
          var idCrypte = row.idCrypte || row.id_classe;
          var act = '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:center;">';
          act += '<a href="' + racine + 'classe/details/' + idCrypte + '" class="btn-action-view" style="color:#1E3A5F; padding:4px 8px; border-radius:6px; background:#F1F5F9; text-decoration:none;" title="Consulter la fiche classe"><i data-lucide="eye" style="width:15px;height:15px;"></i></a>';
          if (canEditClasse) {
            act += '<button type="button" class="btn-action-edit btn-edit-classe" data-id="' + idCrypte + '" data-libelle="' + (row.libelle_classe || '').replace(/"/g, '&quot;') + '" data-filiere="' + (row.filiere_code || '') + '" data-niveau="' + (row.niveau_code || '') + '" data-annee="' + (row.annee_code || '') + '" data-capacite="' + (row.capacite_max_classe || '35') + '" data-statut="' + (row.statut_classe || 'actif') + '" style="color:#0284C7; border:none; background:#F0F9FF; padding:4px 8px; border-radius:6px; cursor:pointer;" title="Modifier (Modale rapide)"><i data-lucide="edit-3" style="width:15px;height:15px;"></i></button>';
            act += '<button type="button" class="btn-action-duplicate btn-duplicate-classe" data-libelle="' + (row.libelle_classe || '').replace(/"/g, '&quot;') + '" data-filiere="' + (row.filiere_code || '') + '" data-niveau="' + (row.niveau_code || '') + '" data-annee="' + (row.annee_code || '') + '" data-capacite="' + (row.capacite_max_classe || '35') + '" style="color:#0D9488; border:none; background:#F0FDFA; padding:4px 8px; border-radius:6px; cursor:pointer;" title="Dupliquer pour créer une Section B / C"><i data-lucide="copy" style="width:15px;height:15px;"></i></button>';
          }
          act += '</div>';
          return act;
        }}
      ],
      language: (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : {
        emptyTable: "Aucune classe enregistrée",
        info: "Affichage de _START_ à _END_ sur _TOTAL_ classes",
        infoEmpty: "Affichage de 0 à 0 sur 0 classe",
        infoFiltered: "(filtré de _MAX_ classes au total)",
        lengthMenu: "Afficher _MENU_ classes",
        loadingRecords: "Chargement...",
        processing: "Traitement en cours...",
        search: "Rechercher :",
        zeroRecords: "Aucune classe correspondante trouvée",
        paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Précédent" }
      },
      pageLength: 25,
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      }
    });

    // Filtre Année Académique
    $('#filter-annee-classe').on('change', function() {
      table.ajax.reload();
    });

    // Bascule du statut d'une classe (Actif / Inactif)
    $(document).on('change', '.toggle-statut-classe', function() {
      var id = $(this).data('id');
      var isChecked = $(this).is(':checked');
      var $slider = $(this).siblings('.slider');

      $.ajax({
        url: racine + 'classe/changer',
        type: 'POST',
        data: {
          id: id,
          statut: isChecked ? 'actif' : 'inactif',
          csrf_token: csrfToken
        },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            $slider.css('background-color', isChecked ? '#16A34A' : '#CBD5E1');
            if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour', 'success');
            else if (window.toastr) toastr.success(res.message || 'Statut mis à jour');
          } else {
            table.ajax.reload(null, false);
            if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de la mise à jour', 'error');
            else if (window.toastr) toastr.error(res.message || 'Erreur lors de la mise à jour');
          }
        },
        error: function() {
          table.ajax.reload(null, false);
          if (typeof showToast === 'function') showToast('Erreur de communication avec le serveur', 'error');
          else if (window.toastr) toastr.error('Erreur réseau');
        }
      });
    });

    // GESTION MODALE INTERACTIVE AJOUT / ÉDITION CLASSE
    function updateStatutClasseUI(isActif) {
      $('#classe_statut_label').text(isActif ? 'Actif' : 'Inactif');
      $('#classe_statut_slider').css('background-color', isActif ? '#16A34A' : '#CBD5E1');
    }

    $('#classe_statut').on('change', function() {
      updateStatutClasseUI($(this).is(':checked'));
    });

    function autoGenerateLibelleClasse() {
      var idVal = $('#classe_id').val();
      if (idVal && idVal !== '') return;

      var filiereText = $('#classe_filiere option:selected').data('nom') || $('#classe_filiere option:selected').text().trim();
      var niveauText  = $('#classe_niveau option:selected').data('nom') || $('#classe_niveau option:selected').text().trim();
      if (!$('#classe_filiere').val()) filiereText = '';
      if (!$('#classe_niveau').val()) niveauText = '';

      if (filiereText && niveauText) {
        $('#classe_libelle').val(filiereText + ' - ' + niveauText);
      } else if (filiereText) {
        $('#classe_libelle').val(filiereText);
      } else if (niveauText) {
        $('#classe_libelle').val(niveauText);
      }
    }

    if ($.fn.select2) {
      $('#classe_filiere').select2({ placeholder: "-- Choisir une filière --", allowClear: true, width: '100%' });
      $('#classe_niveau').select2({ placeholder: "-- Choisir un niveau --", allowClear: true, width: '100%' });
      $('#reconduire_annee_source, #reconduire_annee_cible').select2({ width: '100%' });
    }

    $('#classe_filiere, #classe_niveau').on('change', function() {
      autoGenerateLibelleClasse();
    });

    // Ouverture modale en mode AJOUT
    $(document).on('click', '.btn-add-classe', function(e) {
      e.preventDefault();
      $('#form-classe')[0].reset();
      $('#classe_id').val('');
      var currentYear = $('#filter-annee-classe').val() || currentAnneeCode;
      if (currentYear) {
        $('#classe_annee').val(currentYear);
      }
      if ($.fn.select2) {
        $('#classe_filiere').val('').trigger('change.select2');
        $('#classe_niveau').val('').trigger('change.select2');
      }
      $('#classe_capacite').val('35');
      $('#classe_statut').prop('checked', true);
      updateStatutClasseUI(true);

      $('#modal-classe-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Classe');
      $('#modal-classe').css('display', 'flex');
      if (window.lucide) lucide.createIcons();
      setTimeout(function() { $('#classe_filiere').focus(); }, 100);
    });

    // Ouverture modale en mode ÉDITION
    $(document).on('click', '.btn-edit-classe', function(e) {
      e.preventDefault();
      $('#form-classe')[0].reset();
      var id = $(this).data('id');
      var libelle = $(this).data('libelle') || '';
      var filiere = $(this).data('filiere') || '';
      var niveau = $(this).data('niveau') || '';
      var annee = $(this).data('annee') || '';
      var capacite = $(this).data('capacite') || '35';
      var statut = $(this).data('statut') || 'actif';

      $('#classe_id').val(id);
      $('#classe_libelle').val(libelle);
      $('#classe_filiere').val(filiere);
      $('#classe_niveau').val(niveau);
      $('#classe_annee').val(annee);
      if ($.fn.select2) {
        $('#classe_filiere').trigger('change.select2');
        $('#classe_niveau').trigger('change.select2');
      }
      $('#classe_capacite').val(capacite);
      var isActif = (statut === 'actif');
      $('#classe_statut').prop('checked', isActif);
      updateStatutClasseUI(isActif);

      $('#modal-classe-title').html('<i data-lucide="edit-3" style="width: 18px; height: 18px;"></i> Modifier la Classe');
      $('#modal-classe').css('display', 'flex');
      if (window.lucide) lucide.createIcons();
      setTimeout(function() { $('#classe_libelle').focus(); }, 100);
    });

    // Fermeture modale
    $('.btn-close-modal-classe').on('click', function() {
      $('#modal-classe').hide();
    });

    $(window).on('click', function(e) {
      if ($(e.target).is('#modal-classe')) {
        $('#modal-classe').hide();
      }
    });

    // Soumission du formulaire d'ajout/modification de classe (Modale)
    $('#form-classe').on('submit', function(e) {
      e.preventDefault();
      var $btn = $('#btn-submit-classe');
      var id = $('#classe_id').val();
      var isEdit = (id && id !== '');
      var actionUrl = isEdit ? (racine + 'classe/edit') : (racine + 'classe/add');

      $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Enregistrement...');
      if (window.lucide) lucide.createIcons();

      $.ajax({
        url: actionUrl,
        type: 'POST',
        data: $(this).serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
          if (window.lucide) lucide.createIcons();

          if (res.status === 1 || res.success) {
            if (typeof showToast === 'function') showToast(res.message || 'Classe enregistrée avec succès', 'success');
            else if (window.toastr) toastr.success(res.message || 'Classe enregistrée avec succès');
            $('#modal-classe').hide();
            table.ajax.reload(null, false);
          } else {
            if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
            else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Enregistrer');
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur lors de l\'enregistrement';
          try {
            var json = JSON.parse(xhr.responseText);
            if (json.message) msg = json.message;
          } catch(e) {}
          if (typeof showToast === 'function') showToast(msg, 'error');
          else if (window.toastr) toastr.error(msg);
        }
      });
    });

    // Raccourci Création de Classe depuis la Grille Offre Académique
    $(document).on('click', '.btn-add-classe-from-grid', function(e) {
      e.preventDefault();
      var filiereCode = $(this).data('filiere');
      var niveauCode = $(this).data('niveau');
      var cycleCode = $(this).data('cycle');
      var cycleName = $(this).data('cycle-name') || '';

      $('.btn-add-classe').trigger('click');
      $('#classe_filiere').val(filiereCode);
      if ($.fn.select2) $('#classe_filiere').trigger('change.select2');

      $('#classe_niveau').val(niveauCode);
      if ($.fn.select2) $('#classe_niveau').trigger('change.select2');

      $('#classe_cycle_code').val(cycleCode);
      $('#classe_cycle').val(cycleName);
      autoGenerateLibelleClasse();
    });

    // Duplication rapide de classe (Section B, C...)
    $(document).on('click', '.btn-duplicate-classe', function(e) {
      e.preventDefault();
      $('#form-classe')[0].reset();
      var libelle = $(this).data('libelle') || '';
      var filiere = $(this).data('filiere') || '';
      var niveau = $(this).data('niveau') || '';
      var annee = $(this).data('annee') || '';
      var capacite = $(this).data('capacite') || '35';

      $('#classe_id').val('');
      $('#classe_libelle').val(libelle ? libelle + ' B' : '');
      $('#classe_filiere').val(filiere);
      $('#classe_niveau').val(niveau);
      $('#classe_annee').val(annee);
      if ($.fn.select2) {
        $('#classe_filiere').trigger('change.select2');
        $('#classe_niveau').trigger('change.select2');
        $('#classe_annee').trigger('change.select2');
      }
      $('#classe_capacite').val(capacite);
      $('#classe_statut').prop('checked', true);
      updateStatutClasseUI(true);

      $('#modal-classe-title').html('<i data-lucide="copy" style="width: 18px; height: 18px;"></i> Dupliquer la Classe / Nouvelle Section');
      $('#modal-classe').css('display', 'flex');
      if (window.lucide) lucide.createIcons();
      setTimeout(function() { $('#classe_libelle').focus(); }, 100);
    });

    // Gestion modale Reconduction des classes N-1 -> N
    $(document).on('click', '.btn-reconduire-classes', function(e) {
      e.preventDefault();
      $('#form-reconduire-classes')[0].reset();
      var currentYear = $('#filter-annee-classe').val() || currentAnneeCode;
      if (currentYear) {
        $('#reconduire_annee_cible').val(currentYear);
      }
      $('#modal-reconduire-classes').css('display', 'flex');
      if (window.lucide) lucide.createIcons();
    });

    $('.btn-close-modal-reconduire').on('click', function() {
      $('#modal-reconduire-classes').hide();
    });

    $(window).on('click', function(e) {
      if ($(e.target).is('#modal-reconduire-classes')) {
        $('#modal-reconduire-classes').hide();
      }
    });

    $('#form-reconduire-classes').on('submit', function(e) {
      e.preventDefault();
      var $btn = $('#btn-submit-reconduire');
      $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Traitement...');
      if (window.lucide) lucide.createIcons();

      $.ajax({
        url: racine + 'classe/reconduire',
        type: 'POST',
        data: $(this).serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          $btn.prop('disabled', false).html('<i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction');
          if (window.lucide) lucide.createIcons();
          if (res.status === 1 || res.success) {
            if (typeof showToast === 'function') showToast(res.message || 'Reconduction effectuée avec succès', 'success');
            else if (window.toastr) toastr.success(res.message || 'Reconduction effectuée avec succès');
            $('#modal-reconduire-classes').hide();
            table.ajax.reload(null, false);
          } else {
            if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de la reconduction', 'error');
            else if (window.toastr) toastr.error(res.message || 'Erreur lors de la reconduction');
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html('<i data-lucide="copy-check" style="width:16px;height:16px;"></i> Lancer la Reconduction');
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur lors de la reconduction';
          try {
            var json = JSON.parse(xhr.responseText);
            if (json.message) msg = json.message;
          } catch(e) {}
          if (typeof showToast === 'function') showToast(msg, 'error');
          else if (window.toastr) toastr.error(msg);
        }
      });
    });
  }

  // =========================================================================
  // 2. SECTION : PAGE ÉDITION DÉDIÉE (Exécutée si #form-edit-classe est présent)
  // =========================================================================
  if ($('#form-edit-classe').length) {
    if ($.fn.select2) {
      $('#sel_filiere_classe').select2({ placeholder: "-- Choisir une filière --", allowClear: true, width: '100%' });
      $('#sel_niveau_classe').select2({ placeholder: "-- Choisir un niveau --", allowClear: true, width: '100%' });
    }

    // Génération automatique intelligente du libellé de la classe
    function updateLibelleClasse() {
      var filiereText = $('#sel_filiere_classe option:selected').data('nom') || $('#sel_filiere_classe option:selected').text().trim();
      var niveauText  = $('#sel_niveau_classe option:selected').data('nom') || $('#sel_niveau_classe option:selected').text().trim();
      
      if (!$('#sel_filiere_classe').val()) filiereText = '';
      if (!$('#sel_niveau_classe').val()) niveauText = '';

      if (filiereText && niveauText) {
        $('#libelle_classe_input').val(filiereText + ' - ' + niveauText);
      } else if (filiereText) {
        $('#libelle_classe_input').val(filiereText);
      } else if (niveauText) {
        $('#libelle_classe_input').val(niveauText);
      }
    }

    $('#sel_filiere_classe, #sel_niveau_classe').on('change', function() {
      updateLibelleClasse();
    });

    // Soumission AJAX du formulaire classe
    $('#form-edit-classe').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $('#btn-submit-classe');
      var originalText = $btn.html();

      $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;display:inline-block;animation:spin 1s linear infinite;"></i> Enregistrement...');
      if (window.lucide) lucide.createIcons();

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) {
              toastr.success(res.message || 'Classe enregistrée avec succès !');
            }
            var targetUrl = res.redirect || (racine + 'classe/list');
            setTimeout(function() {
              window.location.href = targetUrl;
            }, 500);
          } else {
            $btn.prop('disabled', false).html(originalText);
            if (window.lucide) lucide.createIcons();
            if (window.toastr) {
              toastr.error(res.message || 'Erreur lors de l\'enregistrement de la classe.');
            }
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html(originalText);
          if (window.lucide) lucide.createIcons();
          var msg = 'Erreur réseau lors de la communication avec le serveur.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          }
          if (window.toastr) {
            toastr.error(msg);
          }
        }
      });
    });
  }

  if (window.lucide) lucide.createIcons();
});

