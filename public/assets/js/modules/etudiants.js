/**
 * Module JS Unifié : Étudiants & Dossiers Scolaires
 * GEICG - Application Web
 */
(function(window, $) {
  'use strict';
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');

  // 1. VUE : REGISTRE DES ÉTUDIANTS (views/etudiants/list.php)
  function initEtudiantsList() {
    if (!$('#table-etudiants').length) return;

  var cfgEl = document.getElementById('etudiants-list-config');
  var config = {};
  if (cfgEl) {
    try { config = JSON.parse(cfgEl.textContent); } catch (e) {}
  }

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var csrfToken = config.csrfToken || $('#csrf_token').val() || '';
  var canChangerClasse = config.canChangerClasse || false;
  var canEditEtudiant = config.canEditEtudiant || false;
  var canAnnulerInscription = config.canAnnulerInscription || false;
  var defaultUseSlugFiliere = !!config.useSlugFiliere;
  var defaultUseSlugNiveau = !!config.useSlugNiveau;
  var anneeActiveVal = config.anneeActive || '';

  // Instance DataTable avec colonnes intelligentes
  var table = $('#table-etudiants').DataTable({
    order: [],
    ajax: {
      url: racine + 'etudiant/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.affectation_etat = $('#filter-regime').val();
        d.canal_inscription = $('#filter-canal').val();
        d.statut_inscription = $('#filter-statut-inscription').val();
      }
    },
    processing: true,
    autoWidth: false,
    pageLength: 25,
    columns: [
      // 0. # Incrémenté
      { data: null, width: '45px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},

      // 1. Date Inscription
      { data: 'created_at_inscription', defaultContent: '', width: '110px', render: function(d, type, row) {
        var rawDate = d || row.created_at_etudiant || '';
        if (!rawDate || rawDate === '0000-00-00 00:00:00' || rawDate === '0000-00-00') return '<span style="color:#94A3B8;">-</span>';
        var parts = rawDate.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          return '<span style="font-weight:700; color:#334155; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="calendar" style="width:13px;height:13px;color:#1E3A5F;"></i> ' + dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + '</span>';
        }
        return '<span style="font-weight:700; color:#334155; font-size:12px;">' + rawDate + '</span>';
      }},

      // 2. Matricule
      { data: 'matricule_etudiant', width: '110px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + d + '</code>';
      }},

      // 3. Nom & Prénoms (Multi-ligne / Retour à la ligne si long)
      { data: null, width: '220px', render: function(d, type, row) {
        var nom = (row.nom_etudiant || '').toUpperCase();
        var prenom = row.prenom_etudiant || '';
        var isAnnule = (row.statut_inscription === 'annule');
        var isEnAttente = (row.statut_inscription === 'en_attente');
        var isEnLigne = (row.canal_inscription === 'en_ligne');

        var badgeStatut = '';
        if (isAnnule) {
          badgeStatut = ' <span style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:800; vertical-align:middle;">ANNULÉE</span>';
        } else if (isEnAttente) {
          badgeStatut = ' <span style="background:#FEF3C7; color:#B45309; border:1px solid #FCD34D; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:800; vertical-align:middle;"><i data-lucide="clock" style="width:10px;height:10px;display:inline-block;vertical-align:-1px;"></i> EN ATTENTE</span>';
        }

        var badgeCanal = isEnLigne
          ? ' <span style="background:#F3E8FF; color:#7E22CE; border:1px solid #D8B4FE; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:800; vertical-align:middle;"><i data-lucide="globe" style="width:10px;height:10px;display:inline-block;vertical-align:-1px;"></i> EN LIGNE</span>'
          : '';

        return '<div style="white-space: normal; word-break: break-word; line-height: 1.35; max-width: 260px;">' +
               '  <div style="font-weight:800; color:' + (isAnnule ? '#94A3B8; text-decoration:line-through;' : '#0F172A;') + ' font-size:13px;">' + nom + badgeStatut + badgeCanal + '</div>' +
               (prenom ? '  <div style="font-weight:600; color:' + (isAnnule ? '#CBD5E1;' : '#475569;') + ' font-size:12px; margin-top:1px;">' + prenom + '</div>' : '') +
               '</div>';
      }},

      // 4. Sexe (Badge H / F)
      { data: 'sexe_etudiant', width: '60px', render: function(d) {
        var s = (d || '').toUpperCase();
        if (s.indexOf('F') !== -1) {
          return '<span style="background:#FCE7F3; color:#9D174D; border:1px solid #FBCFE8; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:800;">F</span>';
        }
        return '<span style="background:#E0E7FF; color:#3730A3; border:1px solid #C7D2FE; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:800;">H</span>';
      }},

      // 5. Régime (Affecté État vs Non affecté)
      { data: 'affectation_etat', width: '120px', render: function(d) {
        if (d === 'affecte' || d === 'oui') {
          return '<span style="background:#DCFCE7; color:#15803D; border:1px solid #BBF7D0; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:11px;height:11px;"></i> Affecté</span>';
        }
        return '<span style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="user" style="width:11px;height:11px;"></i> Non affecté</span>';
      }},

      // 6. Classe
      { data: 'libelle_classe', defaultContent: '', width: '130px', render: function(d, type, row) {
        var val = d || row.code_classe || 'Non affecté';
        var isNA = (val === 'Non affecté' || !d);
        return '<span style="font-weight:700; color:' + (isNA ? '#94A3B8; font-style:italic;' : '#1E3A5F;') + ' font-size:12.5px;">' + val + '</span>';
      }},

      // 7. Filière
      { data: 'filiere_libelle', defaultContent: '', width: '130px', render: function(d, type, row) {
        var useSlug = row.use_slug_filiere !== undefined ? row.use_slug_filiere : defaultUseSlugFiliere;
        var code = (useSlug && row.filiere_slug) ? row.filiere_slug : (row.code_filiere || '');
        var full = row.filiere_nom || d || row.code_filiere || '-';
        if (code && full && code !== full) {
          return '<span title="' + escapeHtml(full) + '" style="font-weight:700; color:#334155; font-size:12px; cursor:help;">' + escapeHtml(code) + '</span>';
        }
        return '<span style="font-weight:700; color:#334155; font-size:12px;">' + escapeHtml(full) + '</span>';
      }},

      // 8. Niveau d'Études
      { data: 'niveau_libelle', defaultContent: '', width: '120px', render: function(d, type, row) {
        var useSlugNiv = row.use_slug_niveau !== undefined ? row.use_slug_niveau : defaultUseSlugNiveau;
        var codeNiv = (useSlugNiv && row.niveau_slug) ? row.niveau_slug : (row.code_niveau || '');
        var fullNiv = row.niveau_nom || d || row.code_niveau || '-';
        if (codeNiv && fullNiv && codeNiv !== fullNiv) {
          return '<span title="' + escapeHtml(fullNiv) + '" style="font-weight:700; color:#475569; font-size:12px; cursor:help;">' + escapeHtml(codeNiv) + '</span>';
        }
        return '<span style="font-weight:700; color:#475569; font-size:12px;">' + escapeHtml(fullNiv) + '</span>';
      }},

      // 9. Actions (Palette moderne d'icônes avec tooltips)
      { data: null, orderable: false, width: '160px', render: function(d, type, row) {
        var idCrypte = row.editId || row.id;
        var idIns = row.id_inscription;
        var insCode = row.code_inscription;
        var isAnnule = (row.statut_inscription === 'annule');
        var isEnAttente = (row.statut_inscription === 'en_attente');
        var nomComplet = ((row.nom_etudiant || '') + ' ' + (row.prenom_etudiant || '')).trim();

        // 1. Validation (si en attente)
        var validerBtn = '';
        if (isEnAttente) {
          validerBtn = '  <button type="button" class="btn-action-icon btn-action-validate btn-valider-inscription" data-id="' + idIns + '" data-nom="' + nomComplet.replace(/"/g, '&quot;') + '" title="Valider définitivement l\'inscription"><i data-lucide="check"></i></button>';
        }

        // 2. Changement de classe
        var classeBtn = '';
        if (canChangerClasse && idIns) {
          classeBtn = '  <a href="' + racine + 'etudiant/changerClasse/' + idCrypte + '" class="btn-action-icon btn-action-classe" title="Changer de classe / Réorienter"><i data-lucide="refresh-cw"></i></a>';
        }

        // 3. Fiche Inscription PDF
        var printBtn = '';
        if (idIns) {
          printBtn = '  <a href="' + racine + 'inscription/fiche/' + idIns + '?pdf=1" target="_blank" class="btn-action-icon btn-action-print" title="Télécharger la fiche d\'inscription PDF"><i data-lucide="file-text"></i></a>';
        }

        // 4. Édition Étudiant
        var editBtn = '';
        if (canEditEtudiant) {
          editBtn = '  <a href="' + racine + 'etudiant/edit/' + idCrypte + '" class="btn-action-icon btn-action-edit" title="Modifier les coordonnées de l\'étudiant"><i data-lucide="edit-3"></i></a>';
        }

        // 5. Annulation Inscription
        var cancelBtn = '';
        if (canAnnulerInscription) {
          if (!isAnnule) {
            cancelBtn = '  <button type="button" class="btn-action-icon btn-action-cancel btn-annuler-inscription" data-id="' + idIns + '" data-code="' + insCode + '" data-nom="' + nomComplet.replace(/"/g, '&quot;') + '" data-payments="' + (row.total_paiements_count || 0) + '" title="Annuler l\'inscription"><i data-lucide="user-x"></i></button>';
          } else {
            cancelBtn = '  <span class="btn-action-icon" style="background:#FEE2E2; color:#991B1B; cursor:not-allowed; opacity:0.6; border:1px solid #FECACA;" title="Inscription déjà annulée"><i data-lucide="ban"></i></span>';
          }
        }

        return '<div style="display:inline-flex; align-items:center; gap:5px; justify-content:center;">' +
               validerBtn +
               classeBtn +
               printBtn +
               '  <a href="' + racine + 'etudiant/details/' + idCrypte + '" class="btn-action-icon btn-action-dossier" title="Consulter le dossier complet"><i data-lucide="eye"></i></a>' +
               editBtn +
               cancelBtn +
               '</div>';
      }, className: 'text-center' }
    ],
    language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
    drawCallback: function() {
      if (window.lucide) lucide.createIcons();

      var api = this.api();
      var rows = api.rows({ filter: 'applied' }).data().toArray();

      var total = rows.length;
      var affectes = 0;
      var nonAffectes = 0;
      var hommes = 0;
      var femmes = 0;

      rows.forEach(function(row) {
        var aff = row.affectation_etat || '';
        if (aff === 'affecte' || aff === 'oui') {
          affectes++;
        } else {
          nonAffectes++;
        }

        var sexe = (row.sexe_etudiant || '').toUpperCase();
        if (sexe.indexOf('F') !== -1) {
          femmes++;
        } else {
          hommes++;
        }
      });

      $('#kpi-total-etudiants').text(total);
      $('#kpi-affectes').text(affectes);
      $('#kpi-non-affectes').text(nonAffectes);
      $('#kpi-genre').text(hommes + ' H / ' + femmes + ' F');
    }
  });

  // Filtre rapide via clic sur les cartes KPI
  $('.kpi-card-clickable').on('click', function() {
    var regime = $(this).data('regime');
    if (regime !== undefined) {
      $('#filter-regime').val(regime).trigger('change');
    }
  });

  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Gestion intelligente de la visibilité des colonnes
  function updateSmartColumns() {
    var selFil = $('#filter-filiere').val();
    var selNiv = $('#filter-niveau').val();
    var selCls = $('#filter-classe').val();

    // Colonne 6 : Classe -> masquée si une classe spécifique est sélectionnée
    table.column(6).visible(selCls === 'ALL' || !selCls);

    // Colonne 7 : Filière -> masquée si une filière spécifique est sélectionnée
    table.column(7).visible(selFil === 'ALL' || !selFil);

    // Colonne 8 : Niveau -> masquée si un niveau spécifique est sélectionné
    table.column(8).visible(selNiv === 'ALL' || !selNiv);
  }

  // Initialisation de Select2 sur les filtres
  if ($.fn.select2) {
    $('#filter-filiere, #filter-niveau, #filter-classe').select2({
      width: '100%'
    });
  }

  // Mise à jour dynamique de l'URL d'impression PDF selon les filtres actifs
  function updatePrintPdfUrl() {
    var params = {
      annee_code: $('#filter-annee').val() || '',
      filiere_code: $('#filter-filiere').val() || '',
      niveau_code: $('#filter-niveau').val() || '',
      classe_code: $('#filter-classe').val() || '',
      affectation_etat: $('#filter-regime').val() || ''
    };
    var qs = $.param(params);
    $('#btn-print-etudiants').attr('href', racine + 'etudiant/imprimerPdf?' + qs);
  }

  // Déclenchement automatique du rechargement et des colonnes intelligentes
  $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe, #filter-regime, #filter-canal, #filter-statut-inscription').on('change', function() {
    filterClassDropdown();
    updateSmartColumns();
    updatePrintPdfUrl();
    table.ajax.reload();
  });

  updatePrintPdfUrl();

  // Filtrage en cascade du menu des classes selon la filière et le niveau choisis
  function filterClassDropdown() {
    var selFil = $('#filter-filiere').val();
    var selNiv = $('#filter-niveau').val();
    var selAnn = $('#filter-annee').val();
    var currentClasseVal = $('#filter-classe').val();
    var isCurrentValValid = false;

    $('#filter-classe option').each(function() {
      var optVal = $(this).val();
      if (!optVal || optVal === 'ALL') {
        $(this).prop('disabled', false).show();
        return;
      }

      var cFil = $(this).data('filiere');
      var cNiv = $(this).data('niveau');
      var cAnn = $(this).data('annee');

      var matchFil = (!selFil || selFil === 'ALL' || cFil === selFil);
      var matchNiv = (!selNiv || selNiv === 'ALL' || cNiv === selNiv);
      var matchAnn = (!selAnn || selAnn === 'ALL' || cAnn === selAnn);

      if (matchFil && matchNiv && matchAnn) {
        $(this).prop('disabled', false).show();
        if (optVal === currentClasseVal) isCurrentValValid = true;
      } else {
        $(this).prop('disabled', true).hide();
      }
    });

    if (!isCurrentValValid && currentClasseVal !== 'ALL') {
      $('#filter-classe').val('ALL').trigger('change.select2');
    } else if ($.fn.select2 && $('#filter-classe').hasClass('select2-hidden-accessible')) {
      $('#filter-classe').trigger('change.select2');
    }
  }

  // Réinitialisation des filtres
  $('#btn-reset-filters').on('click', function() {
    $('#filter-annee').val(anneeActiveVal);
    $('#filter-filiere').val('ALL').trigger('change.select2');
    $('#filter-niveau').val('ALL').trigger('change.select2');
    $('#filter-classe').val('ALL').trigger('change.select2');
    $('#filter-regime').val('ALL');
    filterClassDropdown();
    updateSmartColumns();
    updatePrintPdfUrl();
    table.ajax.reload();
  });

  // Bascule instantanée du statut étudiant
  $(document).on('change', '.toggle-statut-etudiant', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: racine + 'etudiant/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  // Validation directe d'une inscription en attente
  $(document).on('click', '.btn-valider-inscription', function(e) {
    e.preventDefault();
    var idInscription = $(this).data('id');
    var nomEtudiant = $(this).data('nom') || 'cet étudiant';

    var confirmMsg = 'Confirmez-vous la validation définitive de l\'inscription de ' + nomEtudiant + ' ?';
    if (window.Swal) {
      Swal.fire({
        title: 'Valider l\'inscription ?',
        text: confirmMsg,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16A34A',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="fas fa-check"></i> Oui, valider le dossier',
        cancelButtonText: 'Annuler',
        reverseButtons: true
      }).then(function(result) {
        if (result.isConfirmed) {
          executeValidation(idInscription);
        }
      });
    } else {
      if (confirm(confirmMsg)) {
        executeValidation(idInscription);
      }
    }
  });

  function executeValidation(idInscription) {
    $.ajax({
      url: racine + 'inscription/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: idInscription,
        statut: 'valide',
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var successMsg = res.message || 'Inscription validée avec succès !';
          if (typeof showToast === 'function') {
            showToast(successMsg, 'success');
          } else if (window.toastr) {
            toastr.success(successMsg);
          }
          table.ajax.reload(null, false);
        } else {
          var errMsg = res.message || 'Erreur lors de la validation';
          if (typeof showToast === 'function') {
            showToast(errMsg, 'error');
          } else if (window.toastr) {
            toastr.error(errMsg);
          }
        }
      },
      error: function() {
        if (typeof showToast === 'function') {
          showToast('Erreur réseau lors de la validation.', 'error');
        } else if (window.toastr) {
          toastr.error('Erreur réseau');
        }
      }
    });
  }

  // Annulation d'une inscription
  $(document).on('click', '.btn-annuler-inscription', function(e) {
    e.preventDefault();
    var idInscription = $(this).data('id');
    var codeInscription = $(this).data('code');
    var nomEtudiant = $(this).data('nom') || 'cet étudiant';
    var nbPayments = parseInt($(this).data('payments') || 0, 10);

    var warningHtml = '<div style="text-align:left; font-size:13px; color:#475569; line-height:1.5;">' +
      '<p>Êtes-vous sûr de vouloir <strong>annuler l\'inscription</strong> de <strong style="color:#0F172A;">' + $('<div>').text(nomEtudiant).html() + '</strong> ?</p>';

    if (nbPayments > 0) {
      warningHtml += '<div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:10px 12px; margin-top:10px; color:#92400E; display:flex; gap:8px; align-items:flex-start;">' +
        '<span style="font-weight:800; font-size:14px;">⚠️</span>' +
        '<span>Attention : <strong>' + nbPayments + ' paiement(s) actif(s)</strong> sont associés à ce dossier. L\'annulation du dossier d\'inscription n\'annulera pas automatiquement les paiements en caisse.</span>' +
        '</div>';
    }

    warningHtml += '<p style="margin-top:10px; font-size:12px; color:#64748B;">Cette action changera le statut de l\'inscription à <em>"annulé"</em>.</p></div>';

    if (window.Swal) {
      Swal.fire({
        title: 'Annuler l\'inscription ?',
        html: warningHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="fas fa-user-times"></i> Confirmer l\'annulation',
        cancelButtonText: 'Non, fermer',
        reverseButtons: true,
        focusCancel: true
      }).then(function(result) {
        if (result.isConfirmed) {
          executeAnnulation(idInscription, codeInscription);
        }
      });
    } else {
      if (confirm('Êtes-vous sûr de vouloir annuler l\'inscription de ' + nomEtudiant + ' ?')) {
        executeAnnulation(idInscription, codeInscription);
      }
    }
  });

  function executeAnnulation(idInscription, codeInscription) {
    $.ajax({
      url: racine + 'etudiant/annulerInscription',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id_inscription: idInscription,
        code_inscription: codeInscription,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var successMsg = res.message || 'Inscription annulée avec succès';
          if (typeof showToast === 'function') {
            showToast(successMsg, 'success');
          } else if (window.toastr) {
            toastr.success(successMsg);
          }
          table.ajax.reload(null, false);
        } else {
          var errMsg = res.message || 'Erreur lors de l\'annulation de l\'inscription';
          if (typeof showToast === 'function') {
            showToast(errMsg, 'error');
          } else if (window.toastr) {
            toastr.error(errMsg);
          }
        }
      },
      error: function(xhr) {
        var msg = 'Erreur réseau lors de la communication avec le serveur.';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch (e) {}
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  }

  if (window.lucide) lucide.createIcons();

  }

  // 2. VUE : ÉDITION INFORMATIONS ÉTUDIANT (views/etudiants/edit.php)
  function initEtudiantsEdit() {
    if (!$('#form-edit-etudiant').length) return;

  if (window.lucide) lucide.createIcons();

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var configEl = document.getElementById('etudiants-edit-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }
  var defaultRedirect = config.redirectUrl || (racine + 'etudiant/list');

  $('#form-edit-etudiant').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-etudiant');
    var originalHtml = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin" style="width:18px;height:18px;display:inline-block;animation:spin 1s linear infinite;"></i> <span>Enregistrement en cours...</span>');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'Fiche étudiant modifiée avec succès !', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'Fiche étudiant modifiée avec succès !');
          }
          var targetUrl = res.redirect || defaultRedirect;
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de la modification', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de la modification');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur lors de l\'enregistrement';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });

  }

  // 3. VUE : DÉTAILS PROFIL ÉTUDIANT (views/etudiants/details.php)
  function initEtudiantsDetails() {
    if (!$('#btn-print-profil-etudiant').length && !$('.badge-statut-etudiant').length) return;

  if (window.lucide) lucide.createIcons();
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('print')) {
    setTimeout(function() { window.print(); }, 400);
  }

  }

  // 4. VUE : CHANGEMENT DE CLASSE / RÉORIENTATION (views/etudiants/changer_classe.php)
  function initEtudiantsChangerClasse() {
    if (!$('#form-changer-classe-page').length) return;

  if (window.lucide) lucide.createIcons();

  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Sélectionner --",
      allowClear: true,
      width: '100%'
    });
  }

  // Interaction visuelle sur le choix du régime (Radio Affecté vs Non Affecté)
  $('input[name="affectation_etat"]').on('change', function() {
    var val = $(this).val();
    $('.label-affectation-choice').each(function() {
      var isChecked = $(this).find('input[type="radio"]').is(':checked');
      if ($(this).hasClass('label-affectation-affecte')) {
        $(this).css({
          'border-color': isChecked ? '#16A34A' : '#CBD5E1',
          'background': isChecked ? '#F0FDF4' : '#FFFFFF',
          'color': isChecked ? '#15803D' : '#334155'
        });
      } else {
        $(this).css({
          'border-color': isChecked ? '#2563EB' : '#CBD5E1',
          'background': isChecked ? '#EFF6FF' : '#FFFFFF',
          'color': isChecked ? '#1E3A5F' : '#334155'
        });
      }
    });
    refreshClassTuition();
  });

  $('#wiz_classe, #wiz_annee').on('change', function() {
    refreshClassTuition();
  });

  $('#wiz_remise').on('input change', function() {
    updateNetScolarite();
  });

  function updateNetScolarite() {
    var totalScolarite = Number($('#wiz_montant_scolarite').val() || 0);
    var remise = Number($('#wiz_remise').val() || 0);
    var net = Math.max(0, totalScolarite - remise);

    if (remise > 0) {
      $('#wiz_summary_net_scolarite').text(net.toLocaleString('fr-FR') + ' FCFA');
      $('#wiz_summary_net_box').stop(true, true).fadeIn(200);
    } else {
      $('#wiz_summary_net_box').hide();
    }
  }

  function refreshClassTuition() {
    var classeCode = $('#wiz_classe').val();
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

    if (!classeCode) {
      $('#wiz-class-tuition-box').slideUp(200);
      $('#wiz_montant_scolarite').val(0);
      $('#wiz_summary_total_frais_annexes').text('0 FCFA');
      $('#wiz_frais_annexes_amount_badge').text('0 FCFA');
      $('#wiz_frais_annexes_title_text').text('-');
      $('#wiz_frais_annexes_details_desc').text('-');
      updateNetScolarite();
      return;
    }

    $.ajax({
      url: window.RACINE + 'inscription/getTuitionByClass',
      type: 'GET',
      data: { 
        classe_code: classeCode,
        affectation_etat: affectationEtat,
        annee_code: $('#wiz_annee').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res && res.status === 1 && res.data) {
          var d = res.data;
          var totalScolarite = Number(d.montant_scolarite || 0);

          var totalFA = Number(d.total_frais_annexes || 0);
          var totalFAFormate = d.total_frais_annexes_formate || (totalFA.toLocaleString('fr-FR') + ' FCFA');
          var mtPremiereTranche = Number(d.frais_inscription || 0);
          var mtPremiereTrancheFormate = d.frais_inscription_formate || (mtPremiereTranche.toLocaleString('fr-FR') + ' FCFA');
          var totalFraisInscription = Number(d.total_frais_inscription || (mtPremiereTranche + totalFA));
          var totalFraisInscriptionFormate = d.total_frais_inscription_formate || (totalFraisInscription.toLocaleString('fr-FR') + ' FCFA');

          $('#wiz_summary_total_frais_annexes').text(totalFAFormate);
          $('#wiz_frais_annexes_amount_badge').text(totalFraisInscriptionFormate);
          $('#wiz_frais_annexes_title_text').text('1ère Tranche (' + mtPremiereTrancheFormate + ') + Frais Annexes (' + totalFAFormate + ')');
          $('#wiz_frais_annexes_details_desc').text('Montant total exigible à l\'inscription : ' + totalFraisInscriptionFormate);

          if (totalScolarite > 0) {
            $('#wiz_montant_scolarite').val(totalScolarite);

            var isAffecte = d.affectation_etat === 'affecte';
            var themeColor      = isAffecte ? '#15803D' : '#1E3A5F';
            var themeBorder     = isAffecte ? '#86EFAC' : '#BFDBFE';
            var themeBg         = isAffecte ? '#F0FDF4' : '#EFF6FF';
            var themeBadgeBg    = isAffecte ? '#DCFCE7' : '#DBEAFE';
            var themeBadgeText  = isAffecte ? '#15803D' : '#1D4ED8';
            var themeIconColor  = isAffecte ? '#16A34A' : '#2563EB';

            $('#wiz-class-tuition-box').css({
              'border-color': themeBorder,
              'background': '#FFFFFF'
            });

            $('#wiz_tuition_header_bar').css({
              'background': themeBg,
              'border-color': themeBorder
            });

            var regimeBadge = isAffecte 
              ? ' <span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; padding:4px 10px; border-radius:6px; border:1.5px solid #86EFAC; font-weight:800; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:13px; height:13px; color:#16A34A;"></i> Régime Affecté</span>' 
              : ' <span class="badge" style="background:#DBEAFE; color:#1D4ED8; font-size:11.5px; padding:4px 10px; border-radius:6px; border:1.5px solid #BFDBFE; font-weight:800; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="user-check" style="width:13px; height:13px; color:#2563EB;"></i> Régime Non Affecté</span>';

            $('#wiz_summary_classe_title').html(d.libelle_classe + regimeBadge);

            $('#wiz_summary_total_scolarite_box').css({
              'background': themeBg,
              'border-color': themeBorder
            });
            $('#wiz_summary_total_scolarite_label').css('color', themeColor);
            $('#wiz_summary_total_scolarite').css('color', themeColor).text(totalScolarite.toLocaleString('fr-FR') + ' FCFA');

            $('#wiz_tranches_calendar_icon').css('color', themeIconColor);
            $('#wiz_tranches_table_header').css('background', themeBg);

            var tranches = d.tranches || [];
            var tbodyHtml = '';
            var sumTranches = 0;

            if (tranches.length > 0) {
              $('#wiz_tranches_count_badge').css({
                'background': themeBadgeBg,
                'color': themeBadgeText,
                'border': '1px solid ' + themeBorder
              }).text(tranches.length + ' tranche(s) configurée(s)').show();

              tranches.forEach(function(tr, idx) {
                var mt = Number(tr.montant_tranche || tr.montant_tranche_num || 0);
                sumTranches += mt;
                var isFirst = (idx === 0);
                var pct = totalScolarite > 0 ? Math.round((mt / totalScolarite) * 100) : 0;
                var dateLimite = tr.date_limite_formatee || (tr.date_limite ? tr.date_limite : 'Non définie');

                var rowBg = isFirst ? (isAffecte ? '#F0FDF4' : '#F8FAFC') : '#FFFFFF';
                tbodyHtml += '<tr style="border-bottom: 1px solid #F1F5F9; background: ' + rowBg + ';">';
                tbodyHtml += '  <td style="padding: 12px 16px; font-weight: 800; color: #64748B;">' + (idx + 1) + '</td>';
                tbodyHtml += '  <td style="padding: 12px 16px;">';
                tbodyHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13.5px;">' + (tr.libelle_tranche || ('Tranche ' + (idx + 1))) + '</div>';
                if (isFirst) {
                  tbodyHtml += '    <span style="background:' + themeBadgeBg + '; color:' + themeBadgeText + '; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; border:1px solid ' + themeBorder + ';">Exigible à l\'inscription</span>';
                }
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: center;">';
                tbodyHtml += '    <span style="background:#F1F5F9; color:#334155; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;">' + pct + '%</span>';
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: center; color: #475569; font-size: 12.5px; font-weight: 600;">';
                tbodyHtml += '    <i data-lucide="calendar" style="width:13px; height:13px; display:inline-block; vertical-align:middle; margin-right:4px; color:#64748B;"></i>' + dateLimite;
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: ' + themeColor + '; font-size: 14px;">';
                tbodyHtml += '    ' + mt.toLocaleString('fr-FR') + ' FCFA';
                tbodyHtml += '  </td>';
                tbodyHtml += '</tr>';
              });
            } else {
              $('#wiz_tranches_count_badge').css({
                'background': themeBadgeBg,
                'color': themeBadgeText,
                'border': '1px solid ' + themeBorder
              }).text('Paiement Unique');
              sumTranches = totalScolarite;
              tbodyHtml += '<tr>';
              tbodyHtml += '  <td colspan="5" style="padding: 16px; text-align: center; color: #64748B; font-style: italic;">';
              tbodyHtml += '    Aucune tranche intermédiaire configurée pour cette classe. Règlement unique de la scolarité totale : <strong style="color:' + themeColor + ';">' + totalScolarite.toLocaleString('fr-FR') + ' FCFA</strong>';
              tbodyHtml += '  </td>';
              tbodyHtml += '</tr>';
            }

            $('#wiz_tranches_table_body').html(tbodyHtml);
            updateNetScolarite();
            $('#wiz-class-tuition-box').stop(true, true).slideDown(250);
            if (window.lucide) lucide.createIcons();
          } else {
            $('#wiz-class-tuition-box').slideUp(200);
            $('#wiz_montant_scolarite').val(0);
            updateNetScolarite();
          }
        } else {
          $('#wiz-class-tuition-box').slideUp(200);
          $('#wiz_montant_scolarite').val(0);
          updateNetScolarite();
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        $('#wiz-class-tuition-box').slideUp(200);
        $('#wiz_montant_scolarite').val(0);
        updateNetScolarite();
      }
    });
  }

  // Lancement initial de la simulation tarifaire
  refreshClassTuition();

  // Soumission AJAX du formulaire
  $('#form-changer-classe-page').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-change-classe-page');
    var originalHtml = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin" style="width:18px;height:18px;display:inline-block;animation:spin 1s linear infinite;"></i> <span>Enregistrement du changement de classe...</span>');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'La classe de l\'étudiant a été modifiée avec succès !', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'La classe de l\'étudiant a été modifiée avec succès !');
          }
          var targetUrl = res.redirect || (window.RACINE + 'etudiant/list');
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de la modification de la classe', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de la modification de la classe');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur lors de la mise à jour';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });

  }

  // 5. VUE : ASSISTANT DE CRÉATION WIZARD (views/etudiants/wizard.php)
  function initEtudiantsWizard() {
    if (!$('#form-wizard-etudiant').length) return;

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  var currentStep = 1;
  var totalSteps = 5;

  // Persist form inputs to localStorage
  function saveFormData() {
    var formData = {};
    $('#form-wizard-etudiant').find('input, select, textarea').each(function() {
      var name = $(this).attr('name');
      if (!name || name === 'csrf_token') return;
      
      if ($(this).attr('type') === 'checkbox') {
        if (!formData[name]) formData[name] = [];
        if ($(this).is(':checked')) {
          formData[name].push($(this).val());
        }
      } else {
        formData[name] = $(this).val();
      }
    });
    localStorage.setItem('geicg_etudiant_wizard_data', JSON.stringify(formData));
    localStorage.setItem('geicg_etudiant_wizard_step', currentStep);
  }

  // Restore form inputs from localStorage
  function restoreFormData() {
    var savedDataStr = localStorage.getItem('geicg_etudiant_wizard_data');
    if (savedDataStr) {
      try {
        var formData = JSON.parse(savedDataStr);
        $.each(formData, function(name, val) {
          if (name === 'montant_scolarite_inscription') return;
          var $field = $('#form-wizard-etudiant').find('[name="' + name + '"]');
          if (!$field.length) {
            if (name === 'date_naissance') $field = $('#form-wizard-etudiant').find('[name="date_naissance_etudiant"]');
            if (name === 'lieu_naissance') $field = $('#form-wizard-etudiant').find('[name="lieu_naissance_etudiant"]');
            if (name === 'nationalite') $field = $('#form-wizard-etudiant').find('[name="nationalite_etudiant"]');
            if (name === 'adresse_etudiant') $field = $('#form-wizard-etudiant').find('[name="lieu_residence_etudiant"]');
          }
          if ($field.length) {
            if ($field.attr('type') === 'checkbox') {
              if (Array.isArray(val)) {
                $field.each(function() {
                  if (val.indexOf($(this).val()) !== -1) {
                    $(this).prop('checked', true);
                    if ($(this).hasClass('chk-piece')) updatePieceCardStyle($(this));
                    if ($(this).hasClass('chk-accessoire')) updateAccCardStyle($(this));
                  }
                });
              }
            } else if ($field.attr('type') === 'radio') {
              $field.filter('[value="' + val + '"]').prop('checked', true);
              $field.each(function() {
                var isChecked = $(this).is(':checked');
                $(this).closest('label').css({
                  'border-color': isChecked ? '#1E3A5F' : '#CBD5E1',
                  'background': isChecked ? '#EFF6FF' : '#FFFFFF',
                  'color': isChecked ? '#1E3A5F' : '#334155'
                });
              });
            } else {
              $field.val(val);
            }
          }
        });
      } catch(e) { console.error('Erreur restauration brouillon', e); }
    }

    var savedStep = localStorage.getItem('geicg_etudiant_wizard_step');
    if (savedStep) {
      var parsedStep = parseInt(savedStep);
      if (parsedStep >= 1 && parsedStep <= totalSteps) {
        currentStep = parsedStep;
      }
    }
  }

  function updateWizardUI() {
    $('.wizard-step-content').removeClass('active').hide();
    $('.wizard-step-content[data-step="' + currentStep + '"]').addClass('active').show();

    $('.wizard-step-item').each(function() {
      var stepNum = parseInt($(this).attr('data-step'));
      $(this).removeClass('active completed');
      if (stepNum === currentStep) {
        $(this).addClass('active');
      } else if (stepNum < currentStep) {
        $(this).addClass('completed');
      }
    });

    if (currentStep === 1) {
      $('#btn-wizard-prev').hide();
    } else {
      $('#btn-wizard-prev').css('display', 'inline-flex');
    }

    if (currentStep === totalSteps) {
      $('#btn-wizard-next').hide();
      $('#btn-wizard-submit').css('display', 'inline-flex');
      fillRecap();
    } else {
      $('#btn-wizard-next').css('display', 'inline-flex');
      $('#btn-wizard-submit').hide();
    }

    if (currentStep >= 3) {
      refreshClassTuition();
    }
    if (currentStep === 4) {
      filterAndAutoCheckKits();
    }

    saveFormData();
    if (window.lucide) lucide.createIcons();
  }

  function validateStep(step) {
    if (step === 1) {
      var nom = ($('#wiz_nom').val() || '').trim();
      var prenom = ($('#wiz_prenom').val() || '').trim();
      var tel = ($('#wiz_tel').val() || '').trim();
      if (!nom || !prenom || !tel) {
        showToast('Veuillez remplir le nom, les prénoms et le téléphone de l\'étudiant avant de continuer.', 'warning', 'Champs requis');
        return false;
      }
    }
    if (step === 3) {
      var classe = $('#wiz_classe').val();
      var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';
      var regimeName = (affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";
      var montant = Number($('#wiz_montant_scolarite').val() || 0);

      if (!classe) {
        showToast('Veuillez sélectionner la classe d\'affectation.', 'warning', 'Champs requis');
        return false;
      }
      if (montant <= 0) {
        showToast('Attention : Aucun tarif de scolarité actif n\'est enregistré pour le régime "' + regimeName + '" sur cette classe. Veuillez configurer le tarif dans le module Scolarité.', 'warning', 'Tarif manquant');
        return false;
      }
    }
    return true;
  }

  function getFormVal(selector) {
    try {
      var $el = $(selector);
      if (!$el.length) return '';
      var val = $el.val();
      if (val === null || val === undefined) return '';
      return String(val).trim();
    } catch(e) {
      return '';
    }
  }

  function fillRecap() {
    var nom = getFormVal('#wiz_nom') || getFormVal('[name="nom_etudiant"]');
    var prenom = getFormVal('#wiz_prenom') || getFormVal('[name="prenom_etudiant"]');
    var tel = getFormVal('#wiz_tel') || getFormVal('[name="telephone_etudiant"]');
    var email = getFormVal('[name="email_etudiant"]');
    var residence = getFormVal('#wiz_residence') || getFormVal('[name="lieu_residence_etudiant"]') || getFormVal('[name="adresse_etudiant"]');
    var dateNais = getFormVal('#wiz_date_naissance') || getFormVal('[name="date_naissance_etudiant"]') || getFormVal('[name="date_naissance"]');
    var lieuNais = getFormVal('#wiz_lieu_naissance') || getFormVal('[name="lieu_naissance_etudiant"]') || getFormVal('[name="lieu_naissance"]');
    var nat = getFormVal('#wiz_nationalite') || getFormVal('[name="nationalite_etudiant"]') || getFormVal('[name="nationalite"]') || 'Ivoirienne';
    var sexeVal = getFormVal('[name="sexe_etudiant"]');
    var sexe = (sexeVal === 'F') ? 'Féminin' : 'Masculin';

    var formattedDateNais = '-';
    if (dateNais) {
      if (dateNais.indexOf('-') !== -1) {
        var dParts = dateNais.split('-');
        if (dParts.length === 3) {
          formattedDateNais = dParts[2] + '/' + dParts[1] + '/' + dParts[0];
        } else {
          formattedDateNais = dateNais;
        }
      } else {
        formattedDateNais = dateNais;
      }
    }

    var matMenet = getFormVal('[name="matricule_menet"]');
    var matMesrs = getFormVal('[name="matricule_mesrs"]');

    var fullName = (nom + ' ' + prenom).trim().toUpperCase();
    var initials = ((nom ? nom.charAt(0) : 'E') + (prenom ? prenom.charAt(0) : 'T')).toUpperCase();

    $('#recap_avatar').text(initials || 'ET');
    $('#recap_nom_prenom').text(fullName || 'NOUVEL ÉTUDIANT');
    $('#recap_sexe_badge').text(sexe);
    $('#recap_nationalite_badge').text(nat);

    $('#recap_date_naissance').text(formattedDateNais);
    $('#recap_lieu_naissance').text(lieuNais || '-');
    $('#recap_nationalite').text(nat || 'Ivoirienne');
    $('#recap_residence').text(residence || 'Non renseignée');
    $('#recap_tel').text(tel || '-');
    $('#recap_email').text(email || 'Non renseigné');

    $('#recap_mat_menet').text(matMenet || 'Non renseigné');
    $('#recap_mat_mesrs').text(matMesrs || 'Non renseigné');

    // 2. Parents
    var pereNom = getFormVal('[name="nom_pere"]');
    var pereTel = getFormVal('[name="telephone_pere"]');
    var pereProf = getFormVal('[name="profession_pere"]');
    $('#recap_pere_nom').text(pereNom || 'Non renseigné');
    $('#recap_pere_contact').text(pereTel ? 'Tél : ' + pereTel : 'Aucun contact');
    $('#recap_pere_prof').text(pereProf ? 'Profession : ' + pereProf : '-');

    var mereNom = getFormVal('[name="nom_mere"]');
    var mereTel = getFormVal('[name="telephone_mere"]');
    var mereProf = getFormVal('[name="profession_mere"]');
    $('#recap_mere_nom').text(mereNom || 'Non renseignée');
    $('#recap_mere_contact').text(mereTel ? 'Tél : ' + mereTel : 'Aucun contact');
    $('#recap_mere_prof').text(mereProf ? 'Profession : ' + mereProf : '-');

    var tuteurNom = getFormVal('[name="nom_tuteur"]');
    var tuteurTel = getFormVal('[name="telephone_tuteur"]');
    $('#recap_tuteur_nom').text(tuteurNom || 'Non renseigné');
    $('#recap_tuteur_contact').text(tuteurTel ? 'Tél : ' + tuteurTel : 'Aucun contact');

    // 3. Scolarité & Échéancier
    var classeText = $('#wiz_classe option:selected').text();
    var isAffecte = $('input[name="affectation_etat"]:checked').val() === 'affecte';
    var montant = Number(getFormVal('#wiz_montant_scolarite') || getFormVal('[name="montant_scolarite_inscription"]') || 0);
    var remise = Number(getFormVal('#wiz_remise') || getFormVal('[name="remise_scolarite"]') || 0);
    var net = Math.max(0, montant - remise);

    $('#recap_classe_title').text(classeText && classeText.indexOf('Choisir') === -1 ? classeText : 'Classe non sélectionnée');
    $('#recap_regime_text').html(isAffecte ? '<span style="color:#1E3A5F; font-weight:800;">● Régime Affecté</span>' : '<span style="color:#475569; font-weight:800;">● Régime Non Affecté</span>');
    $('#recap_scolarite_').text(montant > 0 ? montant.toLocaleString('fr-FR') + ' FCFA' : '0 FCFA');
    $('#recap_frais_annexes_val').text($('#wiz_summary_total_frais_annexes').text() || '0 FCFA');
    $('#recap_remise_val').text(remise > 0 ? remise.toLocaleString('fr-FR') + ' FCFA' : '0 FCFA');
    $('#recap_net_scolarite').text(net.toLocaleString('fr-FR') + ' FCFA');

    // Cloner ou regénérer les tranches dans le récapitulatif
    var tranchesRows = $('#wiz_tranches_table_body').html();
    if (tranchesRows && tranchesRows.trim() !== '') {
      $('#recap_tranches_table_body').html(tranchesRows);
    } else {
      $('#recap_tranches_table_body').html('<tr><td colspan="5" style="padding:10px; text-align:center; color:#64748B;">Règlement standard unique de ' + net.toLocaleString('fr-FR') + ' FCFA</td></tr>');
    }

    // 4. Pièces du Dossier
    var totalPieces = $('.chk-piece').length;
    var checkedPieces = $('.chk-piece:checked').length;
    $('#recap_dossier_badge').text(checkedPieces + ' / ' + totalPieces + ' pièce(s) fournie(s)');
    if (checkedPieces === totalPieces && totalPieces > 0) {
      $('#recap_dossier_badge').css({ 'background': '#DCFCE7', 'color': '#166534', 'border-color': '#86EFAC' });
    } else {
      $('#recap_dossier_badge').css({ 'background': '#EFF6FF', 'color': '#1E3A5F', 'border-color': '#BFDBFE' });
    }

    var piecesHtml = '';
    $('.chk-piece').each(function() {
      var isChk = $(this).is(':checked');
      var $card = $(this).closest('.piece-checkbox-card');
      var label = $card.find('.piece-title').text().trim() || $card.find('div > div:first-child').text().trim();
      var nbExText = $card.find('.badge-nb-exemplaires').text().trim();
      var exSuffix = nbExText ? (' (' + nbExText + ')') : '';
      piecesHtml += '<div style="display:flex; align-items:center; justify-content:space-between; padding:6px 10px; border-radius:6px; background:' + (isChk ? '#F0FDF4' : '#F8FAFC') + '; border:1px solid ' + (isChk ? '#BBF7D0' : '#E2E8F0') + ';">';
      piecesHtml += '  <span style="color:#0F172A; font-weight:600;">' + label + '<span style="font-size:11px; font-weight:700; color:#1E3A5F; margin-left:6px;">' + exSuffix + '</span></span>';
      piecesHtml += isChk ? '  <span style="color:#166534; font-weight:800; font-size:11px;"><i data-lucide="check" style="width:12px;height:12px;display:inline-block;vertical-align:middle;"></i> Déposée</span>' : '  <span style="color:#64748B; font-weight:600; font-size:11px;">En attente</span>';
      piecesHtml += '</div>';
    });
    $('#recap_pieces_list').html(piecesHtml || '<span style="color:#64748B; font-style:italic;">Aucune pièce configurée</span>');

    // 5. Accessoires & Kits Souscrits
    var accsHtml = '';
    var totalAccs = 0;
    var seenAccs = {};
    $('#form-wizard-etudiant .chk-accessoire:checked, #form-wizard-etudiant input[name="accessoires[]"]:checked').each(function() {
      var val = $(this).val();
      if (!val || seenAccs[val]) return;
      seenAccs[val] = true;
      totalAccs++;

      var label = $(this).attr('data-label') || $(this).closest('.acc-checkbox-card').find('.acc-label').text().trim() || $(this).next('span').text().trim() || $(this).closest('label').text().trim() || val;
      accsHtml += '<div style="background: #EFF6FF; color: #1E3A5F; border: 1.5px solid #BFDBFE; padding: 7px 14px; border-radius: 8px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(30,58,95,0.06); margin: 3px;">';
      accsHtml += '  <i data-lucide="check-circle" style="width: 15px; height: 15px; color: #15803D; flex-shrink: 0;"></i>';
      accsHtml += '  <span>' + label + '</span>';
      accsHtml += '</div>';
    });

    if (totalAccs > 0) {
      $('#recap_acc_badge').text(totalAccs + ' kit(s) souscrit(s)').css({'background': '#DCFCE7', 'color': '#166534', 'border-color': '#86EFAC'});
      $('#recap_accessoires_list').html(accsHtml);
    } else {
      $('#recap_acc_badge').text('0 kit').css({'background': '#EFF6FF', 'color': '#1E3A5F', 'border-color': '#BFDBFE'});
      $('#recap_accessoires_list').html('<span style="color: #64748B; font-style: italic; font-size: 12.5px; padding: 4px 0;">Aucun kit ou accessoire supplémentaire sélectionné lors de cette inscription.</span>');
    }

    if (window.lucide) lucide.createIcons();
  }

  // Gestion dynamique du filtrage et pré-cochage des kits (Option 1 : Même filière ou toute filière + Même niveau ou tout niveau)
  var lastEvaluatedClassForKits = null;

  function filterAndAutoCheckKits(typeFiliere, niveauCode, forceRecheck) {
    var classeCode = $('#wiz_classe').val();
    if (!classeCode) {
      lastEvaluatedClassForKits = null;
      $('#acc_no_class_msg').slideDown(150);
      $('#acc_no_match_msg').hide();
      $('.acc-checkbox-card').hide();
      $('.chk-accessoire').prop('checked', false).each(function() {
        updateAccCardStyle($(this));
      });
      return;
    }

    $('#acc_no_class_msg').slideUp(150);

    if (typeof typeFiliere === 'undefined' || typeFiliere === null || typeFiliere === '') {
      var $selectedOpt = $('#wiz_classe option:selected');
      typeFiliere = $selectedOpt.attr('data-type-filiere') || '';
      niveauCode = $selectedOpt.attr('data-niveau') || '';
    }

    typeFiliere = (typeFiliere || '').trim().toUpperCase();
    niveauCode = (niveauCode || '').trim();

    var isClassChanged = (classeCode !== lastEvaluatedClassForKits);
    var shouldAutoCheck = (forceRecheck === true || isClassChanged);

    var visibleCount = 0;

    $('.acc-checkbox-card').each(function() {
      var $card = $(this);
      var $chk = $card.find('.chk-accessoire');
      var kitFiliere = ($card.attr('data-type-filiere') || 'TOUT').trim().toUpperCase();
      var kitNiveau = ($card.attr('data-niveau') || '').trim();

      // Règle d'attribution Option 1 :
      // 1. Filière : 'TOUT', vide, ou même type de filière que la classe
      var matchFiliere = (kitFiliere === 'TOUT' || kitFiliere === '' || (typeFiliere !== '' && kitFiliere === typeFiliere));

      // 2. Niveau : vide, 'TOUT', ou même code niveau que la classe
      var matchNiveau = (!kitNiveau || kitNiveau === '' || kitNiveau === 'TOUT' || (niveauCode !== '' && kitNiveau === niveauCode));

      if (matchFiliere && matchNiveau) {
        $card.show();
        if (shouldAutoCheck) {
          $chk.prop('checked', true);
        }
        updateAccCardStyle($chk);
        visibleCount++;
      } else {
        $card.hide();
        $chk.prop('checked', false);
        updateAccCardStyle($chk);
      }
    });

    if (visibleCount === 0) {
      $('#acc_no_match_msg').slideDown(150);
    } else {
      $('#acc_no_match_msg').hide();
    }

    lastEvaluatedClassForKits = classeCode;
    if (window.lucide) lucide.createIcons();
  }

  // Gestion visuelle et réactive des kits / accessoires
  function updateAccCardStyle($chk) {
    var $card = $chk.closest('.acc-checkbox-card');
    var $badge = $card.find('.acc-match-badge');
    if ($chk.is(':checked')) {
      $card.css({ 'background': '#EFF6FF', 'border-color': '#93C5FD' });
      $badge.show();
    } else {
      $card.css({ 'background': '#FFFFFF', 'border-color': '#CBD5E1' });
      $badge.hide();
    }
  }

  $(document).on('change', '.chk-accessoire', function() {
    updateAccCardStyle($(this));
  });

  // Gestion visuelle et réactive des pièces à fournir
  function updatePieceCardStyle($chk) {
    var $card = $chk.closest('.piece-checkbox-card');
    var $badge = $card.find('.piece-status-badge');

    if ($chk.is(':checked')) {
      $card.css({ 'background': '#F0FDF4', 'border-color': '#86EFAC' });
      $badge.css({ 'background': '#DCFCE7', 'color': '#15803D' }).text('✓ Pièce Déposée');
    } else {
      $card.css({ 'background': '#FFFFFF', 'border-color': '#E2E8F0' });
      $badge.css({ 'background': '#F1F5F9', 'color': '#64748B' }).text('En attente de dépôt');
    }
  }

  $(document).on('change', '.chk-piece', function() {
    updatePieceCardStyle($(this));
  });

  $('#btn-check-all-accessoires').on('click', function() {
    $('.acc-checkbox-card:visible .chk-accessoire').prop('checked', true).each(function() {
      updateAccCardStyle($(this));
    });
    saveFormData();
  });

  $('#btn-uncheck-all-accessoires').on('click', function() {
    $('.acc-checkbox-card:visible .chk-accessoire').prop('checked', false).each(function() {
      updateAccCardStyle($(this));
    });
    saveFormData();
  });

  $('#btn-check-all-pieces').on('click', function() {
    $('.chk-piece').prop('checked', true).each(function() {
      updatePieceCardStyle($(this));
    });
    saveFormData();
  });

  $('#btn-uncheck-all-pieces').on('click', function() {
    $('.chk-piece').prop('checked', false).each(function() {
      updatePieceCardStyle($(this));
    });
    saveFormData();
  });

  function showRegimeWarningNotice(regimeName) {
    var msg = "Aucune classe n'a été créée pour l'année académique sélectionnée pour le régime <strong>" + escapeHtml(regimeName) + "</strong>. Veuillez ajouter les classes dans le module Classes.";
    
    if ($('#wiz_regime_notice_banner').length === 0) {
      var html = '<div id="wiz_regime_notice_banner" style="margin-top: 12px; font-size: 13px; color: #B45309; background: #FFFBEB; border: 1.5px solid #FDE68A; padding: 12px 16px; border-radius: 8px; line-height: 1.4; display: flex; align-items: center; gap: 10px;">' +
        '<i data-lucide="alert-triangle" style="width: 18px; height: 18px; flex-shrink: 0; color: #D97706;"></i>' +
        '<span class="wiz-regime-notice-text">' + msg + '</span>' +
        '</div>';
      $('input[name="affectation_etat"]').closest('.form-group').append(html);
    } else {
      $('#wiz_regime_notice_banner').find('.wiz-regime-notice-text').html(msg);
      $('#wiz_regime_notice_banner').slideDown(150);
    }
    if (window.lucide) lucide.createIcons();
  }

  function hideRegimeWarningNotice() {
    if ($('#wiz_regime_notice_banner').length) {
      $('#wiz_regime_notice_banner').slideUp(150);
    }
  }

  function filterWizardClassesByAnnee(callback) {
    var selectedAnnee = $('#wiz_annee').val();
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';
    var $classeSelect = $('#wiz_classe');
    var currentVal = $classeSelect.val();
    var $emptyMsg = $('#wiz_classe_empty_msg');

    if (!selectedAnnee) {
      $classeSelect.html('<option value="">-- Sélectionnez d\'abord une année académique --</option>');
      if ($classeSelect.data('select2')) $classeSelect.trigger('change.select2');
      if ($emptyMsg.length) $emptyMsg.hide();
      return;
    }

    var baseApi = (typeof LINK !== 'undefined' && LINK) ? LINK : ((typeof RACINE !== 'undefined' && RACINE) ? RACINE : '/');

    $.ajax({
      url: baseApi + 'classe/getClassesWithScolarite',
      type: 'GET',
      data: {
        annee_code: selectedAnnee,
        affectation_etat: affectationEtat
      },
      dataType: 'json',
      success: function(res) {
        var optionsHtml = '';
        var classes = (res && res.data) ? res.data : [];
        var valFound = false;

        if (classes.length > 0) {
          optionsHtml += '<option value="">-- Rechercher / Sélectionner la classe d\'affectation --</option>';
          classes.forEach(function(cl) {
            var isSel = (currentVal && cl.code_classe === currentVal);
            if (isSel) valFound = true;
            var classLabel = escapeHtml(cl.libelle_classe) + (cl.code_classe ? ' (' + escapeHtml(cl.code_classe) + ')' : '');
            optionsHtml += '<option value="' + escapeHtml(cl.code_classe) + '" data-annee="' + escapeHtml(cl.annee_code || selectedAnnee) + '" data-type-filiere="' + escapeHtml(cl.type_filiere || '') + '" data-niveau="' + escapeHtml(cl.niveau_code || '') + '"' + (isSel ? ' selected' : '') + '>' + classLabel + '</option>';
          });
          if ($emptyMsg.length) $emptyMsg.slideUp(150);
          hideRegimeWarningNotice();
        } else {
          var regimeName = (affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";
          optionsHtml += '<option value="">-- Aucune classe disponible pour le régime ' + escapeHtml(regimeName) + ' --</option>';
          if ($emptyMsg.length) {
            $emptyMsg.find('span').html('Aucune classe n\'a été créée pour le régime <strong>' + escapeHtml(regimeName) + '</strong> pour l\'année académique sélectionnée. Veuillez ajouter les classes dans le module Classes.');
            $emptyMsg.slideDown(200);
            if (window.lucide) lucide.createIcons();
          }
          showRegimeWarningNotice(regimeName);
        }

        $classeSelect.html(optionsHtml);
        if (!valFound && currentVal) {
          $classeSelect.val('').trigger('change');
        } else {
          $classeSelect.trigger('change.select2');
        }

        if (typeof callback === 'function') {
          callback(classes);
        }
      },
      error: function(xhr, status, err) {
        console.error('Erreur chargement des classes avec scolarité:', err);
      }
    });
  }

  $('#wiz_annee').on('change select2:select', function() {
    filterWizardClassesByAnnee(refreshClassTuition);
  });

  // Auto-récupération et affichage complet de la scolarité et de TOUTES les tranches selon la classe et le statut d'affectation
  function refreshClassTuition() {
    var classeCode = $('#wiz_classe').val();
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

    if (!classeCode) {
      filterAndAutoCheckKits(null, null);
      $('#wiz-class-tuition-box').slideUp(200);
      $('#wiz_montant_scolarite').val(0);
      $('#wiz_summary_total_frais_annexes').text('0 FCFA');
      $('#wiz_frais_annexes_amount_badge').text('0 FCFA');
      $('#wiz_frais_annexes_title_text').text('-');
      $('#wiz_frais_annexes_details_desc').text('-');
      updateNetScolarite();
      hideRegimeWarningNotice();
      return;
    }

    $.ajax({
      url: window.RACINE + 'inscription/getTuitionByClass',
      type: 'GET',
      data: { 
        classe_code: classeCode,
        affectation_etat: affectationEtat,
        annee_code: $('#wiz_annee').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res && res.status === 1 && res.data) {
          var d = res.data;
          filterAndAutoCheckKits(d.type_filiere, d.niveau_code);

          var totalScolarite = Number(d.montant_scolarite || 0);

          // Calcul dynamique des Frais d'inscription = Montant 1ère Tranche + Frais Annexes (catégorie inscription)
          var totalFA = Number(d.total_frais_annexes || 0);
          var totalFAFormate = d.total_frais_annexes_formate || (totalFA.toLocaleString('fr-FR') + ' FCFA');
          var mtPremiereTranche = Number(d.frais_inscription || 0);
          var mtPremiereTrancheFormate = d.frais_inscription_formate || (mtPremiereTranche.toLocaleString('fr-FR') + ' FCFA');
          var totalFraisInscription = Number(d.total_frais_inscription || (mtPremiereTranche + totalFA));
          var totalFraisInscriptionFormate = d.total_frais_inscription_formate || (totalFraisInscription.toLocaleString('fr-FR') + ' FCFA');

          $('#wiz_summary_total_frais_annexes').text(totalFAFormate);
          $('#wiz_frais_annexes_amount_badge').text(totalFraisInscriptionFormate);
          $('#wiz_frais_annexes_title_text').text('1ère Tranche (' + mtPremiereTrancheFormate + ') + Frais Annexes (' + totalFAFormate + ')');
          $('#wiz_frais_annexes_details_desc').text('Montant total exigible à l\'inscription : ' + totalFraisInscriptionFormate);

          if (totalScolarite > 0) {
            $('#wiz_montant_scolarite').val(totalScolarite);

            var isAffecte = d.affectation_etat === 'affecte';

            // Palette de couleurs pour différenciation Régime Affecté (Vert) vs Non Affecté (Bleu)
            var themeColor      = isAffecte ? '#15803D' : '#1E3A5F';
            var themeBorder     = isAffecte ? '#86EFAC' : '#BFDBFE';
            var themeBg         = isAffecte ? '#F0FDF4' : '#EFF6FF';
            var themeBadgeBg    = isAffecte ? '#DCFCE7' : '#DBEAFE';
            var themeBadgeText  = isAffecte ? '#15803D' : '#1D4ED8';
            var themeIconColor  = isAffecte ? '#16A34A' : '#2563EB';

            // Application dynamique de la thématique au container principal
            $('#wiz-class-tuition-box').css({
              'border-color': themeBorder,
              'background': '#FFFFFF'
            });

            $('#wiz_tuition_header_bar').css({
              'background': themeBg,
              'border-color': themeBorder
            });

            var regimeBadge = isAffecte 
              ? ' <span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; padding:4px 10px; border-radius:6px; border:1.5px solid #86EFAC; font-weight:800; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:13px; height:13px; color:#16A34A;"></i> Régime Affecté</span>' 
              : ' <span class="badge" style="background:#DBEAFE; color:#1D4ED8; font-size:11.5px; padding:4px 10px; border-radius:6px; border:1.5px solid #BFDBFE; font-weight:800; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="user-check" style="width:13px; height:13px; color:#2563EB;"></i> Régime Non Affecté</span>';

            $('#wiz_summary_classe_title').html(d.libelle_classe + regimeBadge);

            // Styling du bloc Total Scolarité selon le régime
            $('#wiz_summary_total_scolarite_box').css({
              'background': themeBg,
              'border-color': themeBorder
            });
            $('#wiz_summary_total_scolarite_label').css('color', themeColor);
            $('#wiz_summary_total_scolarite').css('color', themeColor).text(totalScolarite.toLocaleString('fr-FR') + ' FCFA');

            // Header & icône des tranches
            $('#wiz_tranches_calendar_icon').css('color', themeIconColor);
            $('#wiz_tranches_table_header').css('background', themeBg);

            // Rendu dynamique de TOUTES les tranches avec couleur selon le régime
            var tranches = d.tranches || [];
            var tbodyHtml = '';
            var sumTranches = 0;

            if (tranches.length > 0) {
              $('#wiz_tranches_count_badge').css({
                'background': themeBadgeBg,
                'color': themeBadgeText,
                'border': '1px solid ' + themeBorder
              }).text(tranches.length + ' tranche(s) configurée(s)').show();

              tranches.forEach(function(tr, idx) {
                var mt = Number(tr.montant_tranche || tr.montant_tranche_num || 0);
                sumTranches += mt;
                var isFirst = (idx === 0);
                var pct = totalScolarite > 0 ? Math.round((mt / totalScolarite) * 100) : 0;
                var dateLimite = tr.date_limite_formatee || (tr.date_limite ? tr.date_limite : 'Non définie');

                var rowBg = isFirst ? (isAffecte ? '#F0FDF4' : '#F8FAFC') : '#FFFFFF';
                tbodyHtml += '<tr style="border-bottom: 1px solid #F1F5F9; background: ' + rowBg + ';">';
                tbodyHtml += '  <td style="padding: 12px 16px; font-weight: 800; color: #64748B;">' + (idx + 1) + '</td>';
                tbodyHtml += '  <td style="padding: 12px 16px;">';
                tbodyHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13.5px;">' + (tr.libelle_tranche || ('Tranche ' + (idx + 1))) + '</div>';
                if (isFirst) {
                  tbodyHtml += '    <span style="background:' + themeBadgeBg + '; color:' + themeBadgeText + '; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; border:1px solid ' + themeBorder + ';">Exigible à l\'inscription</span>';
                }
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: center;">';
                tbodyHtml += '    <span style="background:#F1F5F9; color:#334155; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;">' + pct + '%</span>';
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: center; color: #475569; font-size: 12.5px; font-weight: 600;">';
                tbodyHtml += '    <i data-lucide="calendar" style="width:13px; height:13px; display:inline-block; vertical-align:middle; margin-right:4px; color:#64748B;"></i>' + dateLimite;
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: ' + themeColor + '; font-size: 14px;">';
                tbodyHtml += '    ' + mt.toLocaleString('fr-FR') + ' FCFA';
                tbodyHtml += '  </td>';
                tbodyHtml += '</tr>';
              });
            } else {
              $('#wiz_tranches_count_badge').css({
                'background': themeBadgeBg,
                'color': themeBadgeText,
                'border': '1px solid ' + themeBorder
              }).text('Paiement Unique');
              sumTranches = totalScolarite;
              tbodyHtml += '<tr>';
              tbodyHtml += '  <td colspan="5" style="padding: 16px; text-align: center; color: #64748B; font-style: italic;">';
              tbodyHtml += '    Aucune tranche intermédiaire configurée pour cette classe. Règlement unique de la scolarité totale : <strong style="color:' + themeColor + ';">' + totalScolarite.toLocaleString('fr-FR') + ' FCFA</strong>';
              tbodyHtml += '  </td>';
              tbodyHtml += '</tr>';
            }

            $('#wiz_tranches_table_body').html(tbodyHtml);
            $('#wiz_tranches_total_sum').css('color', themeColor).text(sumTranches.toLocaleString('fr-FR') + ' FCFA');

            hideRegimeWarningNotice();
            updateNetScolarite();
            $('#wiz-class-tuition-box').stop(true, true).slideDown(250);
            if (window.lucide) lucide.createIcons();
          } else {
            $('#wiz-class-tuition-box').slideUp(200);
            $('#wiz_montant_scolarite').val(0);
            updateNetScolarite();
            var regimeName = (affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";
            showRegimeWarningNotice(regimeName);
          }
        } else {
          $('#wiz-class-tuition-box').slideUp(200);
          $('#wiz_montant_scolarite').val(0);
          updateNetScolarite();
          var regimeName = (affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";
          showRegimeWarningNotice(regimeName);
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        $('#wiz-class-tuition-box').slideUp(200);
        $('#wiz_montant_scolarite').val(0);
        updateNetScolarite();
        var regimeName = (affectationEtat === 'affecte') ? "Affecté (de l'État)" : "Non Affecté (Privé)";
        showRegimeWarningNotice(regimeName);
      }
    });
  }

  $('#wiz_classe').on('change select2:select', function() {
    filterAndAutoCheckKits();
    refreshClassTuition();
  });
  $('input[name="affectation_etat"]').on('change', function() {
    $('input[name="affectation_etat"]').each(function() {
      var isChecked = $(this).is(':checked');
      var val = $(this).val();
      if (val === 'affecte') {
        $(this).closest('label').css({
          'border-color': isChecked ? '#16A34A' : '#CBD5E1',
          'background': isChecked ? '#F0FDF4' : '#FFFFFF',
          'color': isChecked ? '#15803D' : '#334155'
        });
      } else {
        $(this).closest('label').css({
          'border-color': isChecked ? '#2563EB' : '#CBD5E1',
          'background': isChecked ? '#EFF6FF' : '#FFFFFF',
          'color': isChecked ? '#1E3A5F' : '#334155'
        });
      }
    });
    filterWizardClassesByAnnee(refreshClassTuition);
  });

  // Calcul dynamique du net à payer après remise
  function updateNetScolarite() {
    var total = Number($('#wiz_montant_scolarite').val() || 0);
    var remise = Number($('#wiz_remise').val() || 0);
    if (remise > 0 && total > 0) {
      var net = Math.max(0, total - remise);
      $('#wiz_summary_net_scolarite').text(net.toLocaleString('fr-FR') + ' FCFA');
      $('#wiz_summary_net_box').fadeIn(200);
    } else {
      $('#wiz_summary_net_box').hide();
    }
  }

  $('#wiz_remise').on('input change keyup', function() {
    updateNetScolarite();
  });

  // Auto save on any input change
  $('#form-wizard-etudiant').on('input change keyup', 'input, select, textarea', function() {
    saveFormData();
  });

  // Next Step
  $('#btn-wizard-next').on('click', function() {
    if (validateStep(currentStep)) {
      if (currentStep < totalSteps) {
        currentStep++;
        updateWizardUI();
      }
    }
  });

  // Previous Step
  $('#btn-wizard-prev').on('click', function() {
    if (currentStep > 1) {
      currentStep--;
      updateWizardUI();
    }
  });

  // Direct Step click
  $('.wizard-step-item').on('click', function() {
    var targetStep = parseInt($(this).attr('data-step'));
    if (targetStep < currentStep) {
      currentStep = targetStep;
      updateWizardUI();
    } else if (targetStep === currentStep + 1 && validateStep(currentStep)) {
      currentStep = targetStep;
      updateWizardUI();
    }
  });

  // Manual Reset Draft button (Custom Pro Modal)
  $('#btn-reset-draft').on('click', function() {
    showConfirm(
      'Voulez-vous réinitialiser le formulaire et effacer toutes les données saisies ? Cette action est irréversible.',
      function() {
        localStorage.removeItem('geicg_etudiant_wizard_data');
        localStorage.removeItem('geicg_etudiant_wizard_step');
        location.reload();
      },
      'Réinitialiser le brouillon',
      'Effacer tout',
      true
    );
  });

  // Validation globale et soumission AJAX du Wizard
  function submitWizardForm() {
    // 1. Validation de l'Étape 1 (Identité)
    if (!validateStep(1)) {
      currentStep = 1;
      updateWizardUI();
      return false;
    }

    // 2. Validation de l'Étape 3 (Inscription)
    if (!validateStep(3)) {
      currentStep = 3;
      updateWizardUI();
      return false;
    }

    var $form = $('#form-wizard-etudiant');
    var $btnSubmit = $('#btn-wizard-submit');
    var originalHtml = $btnSubmit.html();

    $btnSubmit.prop('disabled', true).css('opacity', '0.75').html(
      '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Enregistrement du dossier...'
    );

    var formData = new FormData($form[0]);

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        if (res && res.status === 1) {
          localStorage.removeItem('geicg_etudiant_wizard_data');
          localStorage.removeItem('geicg_etudiant_wizard_step');
          if (typeof showToast === 'function') {
            showToast(res.message || "Dossier d'inscription créé avec succès !", 'success', 'Succès');
          }
          setTimeout(function() {
            window.location.href = res.redirect || (window.RACINE + 'etudiant/list');
          }, 600);
        } else {
          $btnSubmit.prop('disabled', false).css('opacity', '1').html(originalHtml);
          if (window.lucide) lucide.createIcons();
          var msg = (res && res.message) ? res.message : "Erreur lors de l'enregistrement de l'inscription.";
          if (typeof showToast === 'function') {
            showToast(msg, 'error', 'Erreur');
          } else {
            alert(msg);
          }
        }
      },
      error: function(xhr, status, err) {
        $btnSubmit.prop('disabled', false).css('opacity', '1').html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var errMsg = "Erreur de communication avec le serveur lors de l'enregistrement.";
        try {
          var parsed = JSON.parse(xhr.responseText);
          if (parsed && parsed.message) errMsg = parsed.message;
        } catch(e) {}
        if (typeof showToast === 'function') {
          showToast(errMsg, 'error', 'Erreur Serveur');
        } else {
          alert(errMsg);
        }
      }
    });

    return false;
  }

  $('#form-wizard-etudiant').on('submit', function(e) {
    e.preventDefault();
    submitWizardForm();
  });

  $('#btn-wizard-submit').on('click', function(e) {
    e.preventDefault();
    submitWizardForm();
  });

  // Restore state on load
  restoreFormData();
  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Rechercher / Sélectionner --",
      allowClear: true,
      width: '100%'
    });
  }
  filterWizardClassesByAnnee(function() {
    var initClass = $('#wiz_classe').val();
    if (initClass) {
      $('#wiz_classe').trigger('change');
    }
  });
  updateWizardUI();

  }

  // 6. VUE : GESTION DES DOSSIERS ET PIÈCES (views/dossier_etudiant/list.php)
  function initDossierEtudiant() {
    if (!$('#table-dossiers').length) return;

  var table;
  var currentStudentData = null;

  if ($.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  if ($.fn.select2) {
    $('#filter-annee, #filter-classe, #filter-statut').select2({ width: '100%' });
  }

  function initTable() {
    table = $('#table-dossiers').DataTable({
      ajax: {
        url: window.RACINE + 'dossier_etudiant/apiList',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
          d.filter_statut = $('#filter-statut').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement DataTables:", error, thrown);
        },
        dataSrc: function(json) {
          var items = json.data || [];
          var totalCount = items.length;
          var completsCount = 0;
          var incompletsCount = 0;
          var totalPiecesGlobal = 0;
          var totalDeposeesGlobal = 0;

          items.forEach(function(i) {
            if (i.est_complet) completsCount++;
            else incompletsCount++;
            totalPiecesGlobal += i.total_pieces;
            totalDeposeesGlobal += i.deposees_count;
          });

          var tauxGlobal = (totalPiecesGlobal > 0) ? Math.round((totalDeposeesGlobal / totalPiecesGlobal) * 100) : 0;

          $('#kpi-total-etudiants').text(totalCount);
          $('#kpi-dossiers-complets').text(completsCount);
          $('#kpi-dossiers-incomplets').text(incompletsCount);
          $('#kpi-taux-global').text(tauxGlobal + '%');

          return items;
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var rawName = (d.nom_complet ? d.nom_complet.trim() : '') || 'Étudiant non identifié';
            var parts = rawName.split(' ').filter(function(p) { return p.length > 0; });
            var initials = 'ET';
            if (parts.length >= 2) {
              initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            } else if (parts.length === 1 && parts[0].length >= 2) {
              initials = parts[0].substring(0, 2).toUpperCase();
            }

            var photoPath = d.photo ? String(d.photo).trim() : '';
            var initialsDiv = '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';

            var avatarHtml = initialsDiv;
            if (photoPath !== '') {
              var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : (window.RACINE + photoPath.replace(/^\//, ''));
              avatarHtml = '<img src="' + photoUrl + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; flex-shrink: 0;" onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';">' +
                           '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';
            }

            return '<div style="display: flex; align-items: center; gap: 12px; min-width: 180px;">' +
                     avatarHtml +
                     '<div>' +
                       '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">' + escapeHtml(rawName) + '</div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: 'matricule',
          render: function(d) {
            return '<span class="badge bg-light text-dark font-monospace" style="font-size: 12px; font-weight: 700; padding: 6px 10px; border: 1px solid #CBD5E1;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: 'classe',
          render: function(d) {
            return '<span style="font-weight: 700; color: #1E3A5F;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var badgeClass = d.est_complet ? 'bg-success' : (d.deposees_count > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.deposees_count + ' / ' + d.total_pieces + ' pièce(s)' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 120px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
                       '<div style="height: 100%; width: ' + pct + '%; background: ' + barColor + '; border-radius: 4px;"></div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function(d) {
            return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                     '<button type="button" class="btn btn-sm btn-primary btn-open-dossier" style="font-weight: 700; border-radius: 8px; padding: 6px 11px;" title="Gérer les pièces et les scans">' +
                       '<i data-lucide="folder-open" style="width: 14px; height: 14px; margin-right: 4px;"></i> Gérer' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-primary btn-print-recepisse-row" title="Imprimer le Récépissé" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="printer" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate" title="Tout valider en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="check-check" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                   '</div>';
          }
        }
      ],
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      },
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
      }
    });
  }

  initTable();

  // Filtrage interactif
  $('#filter-annee, #filter-classe, #filter-statut').on('change', function() {
    table.ajax.reload();
  });

  $('.kpi-card-clickable').on('click', function() {
    var status = $(this).data('status');
    $('#filter-statut').val(status).trigger('change.select2');
    table.ajax.reload();
  });

  $('#btn-refresh').on('click', function() {
    table.ajax.reload();
  });

  // Action Gérer le dossier (Modal)
  $('#table-dossiers').on('click', '.btn-open-dossier', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openDossierModal(rowData);
  });

  // Action Impression Récépissé en ligne
  $('#table-dossiers').on('click', '.btn-print-recepisse-row', function() {
    var rowData = table.row($(this).closest('tr')).data();
    loadAndOpenRecepisseModal(rowData.code_inscription);
  });

  $('#btn-print-modal-recepisse').on('click', function() {
    if (currentStudentData) {
      loadAndOpenRecepisseModal(currentStudentData.code_inscription);
    }
  });

  // Fermeture des modals
  $('.btn-close-modal-dossier').on('click', function() {
    $('#modal-dossier').hide();
  });
  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-dossier')) $('#modal-dossier').hide();
    if ($(e.target).is('#modal-recepisse')) $('#modal-recepisse').hide();
  });

  $('.btn-close-modal-recepisse').on('click', function() {
    $('#modal-recepisse').hide();
  });

  // Action Tout valider rapide
  $('#table-dossiers').on('click', '.btn-quick-validate', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllStudentPieces(rowData.code_inscription, rowData.code_etudiant);
  });

  $('#btn-tout-valider-modal').on('click', function() {
    if (currentStudentData) {
      validateAllStudentPieces(currentStudentData.code_inscription, currentStudentData.code_etudiant);
    }
  });

  function updatePieceCardStyle($card, st) {
    var borderCol = '#EA580C';
    var bgCol = '#FFF7ED';
    if (st === 'depose') {
      borderCol = '#16A34A';
      bgCol = '#F0FDF4';
    } else if (st === 'rejete') {
      borderCol = '#DC2626';
      bgCol = '#FEF2F2';
    } else if (st === 'non_requis') {
      borderCol = '#64748B';
      bgCol = '#F8FAFC';
    }
    $card.css({
      'border-left': '5px solid ' + borderCol,
      'background': bgCol
    });
  }

  $(document).on('change', '.select-statut-piece', function() {
    var st = $(this).val();
    var $card = $(this).closest('.piece-card-form');
    updatePieceCardStyle($card, st);
  });

  function openDossierModal(d) {
    $('#modal-student-name').text(d.nom_complet);
    $('#modal-student-info').text('Matricule : ' + (d.matricule || '-') + ' | Classe : ' + (d.classe || '-'));
    $('#modal-completion-text').text(d.deposees_count + ' / ' + d.total_pieces + ' Pièce(s) Déposée(s) (' + d.pourcentage + '%)');

    var html = '';
    if (d.dossier_items && d.dossier_items.length > 0) {
      d.dossier_items.forEach(function(item) {
        var st = item.statut_depot || 'en_attente';
        var borderCol = st === 'depose' ? '#16A34A' : (st === 'rejete' ? '#DC2626' : (st === 'non_requis' ? '#64748B' : '#EA580C'));
        var bgCol = st === 'depose' ? '#F0FDF4' : (st === 'rejete' ? '#FEF2F2' : (st === 'non_requis' ? '#F8FAFC' : '#FFF7ED'));

        var fileHtml = '';
        if (item.fichier_joint) {
          var fullFileUrl = item.fichier_joint.startsWith('http') ? item.fichier_joint : (window.RACINE + item.fichier_joint);
          fileHtml = '<div style="margin-top: 6px;"><a href="' + fullFileUrl + '" target="_blank" class="btn btn-sm btn-outline-info" style="font-size: 11.5px; font-weight: 700; border-radius: 6px; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="file-text" style="width: 14px; height: 14px;"></i> Voir le scan joint</a></div>';
        }

        var dateDepotText = item.date_depot ? ('<span style="font-size: 11px; font-weight: 600; color: #15803D; margin-left: 6px;">(Déposé le ' + item.date_depot + ')</span>') : '';

        html += '<form class="piece-card-form" style="background: ' + bgCol + '; border-left: 5px solid ' + borderCol + '; padding: 16px; border-radius: 12px; border-top: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">' +
                  '<input type="hidden" name="inscription_code" value="' + d.code_inscription + '">' +
                  '<input type="hidden" name="etudiant_code" value="' + d.code_etudiant + '">' +
                  '<input type="hidden" name="piece_code" value="' + item.piece_code + '">' +
                  
                  '<div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">' +
                    '<div>' +
                      '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_piece) + dateDepotText + '</div>' +
                      '<div style="font-size: 12px; color: #64748B; margin-top: 2px;">' + escapeHtml(item.description_piece || 'Pièce exigée pour le dossier') + '</div>' +
                      fileHtml +
                    '</div>' +
                    '<div>' +
                      '<select name="statut" class="form-select form-select-sm select-statut-piece" style="font-weight: 700; border-radius: 8px; min-width: 140px;">' +
                        '<option value="depose" ' + (st === 'depose' ? 'selected' : '') + '>✅ Déposé</option>' +
                        '<option value="en_attente" ' + (st === 'en_attente' ? 'selected' : '') + '>⏳ En attente</option>' +
                        '<option value="rejete" ' + (st === 'rejete' ? 'selected' : '') + '>❌ Rejeté</option>' +
                        '<option value="non_requis" ' + (st === 'non_requis' ? 'selected' : '') + '>⚪ Non requis</option>' +
                      '</select>' +
                    '</div>' +
                  '</div>' +

                  '<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">' +
                    '<div style="flex: 1; min-width: 180px;">' +
                      '<input type="text" name="observations" class="form-control form-control-sm" placeholder="Remarques / Observations..." value="' + escapeHtml(item.observations || '') + '" style="border-radius: 6px; font-size: 12px;">' +
                    '</div>' +
                    '<div style="flex: 1; min-width: 180px;">' +
                      '<input type="file" name="fichier_joint" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" title="Joindre un scan numérisé (PDF/Image)" style="font-size: 11px; border-radius: 6px;">' +
                    '</div>' +
                    '<div>' +
                      '<button type="submit" class="btn btn-sm btn-primary btn-save-piece" style="font-weight: 700; border-radius: 6px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer</button>' +
                    '</div>' +
                  '</div>' +
                '</form>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucune pièce exigée configurée pour le cycle de cette classe.</div>';
    }

    $('#modal-pieces-list').html(html);
    $('#modal-dossier').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  }

  // Action enregistrer modification statut unitaire avec upload éventuel
  $(document).on('submit', '.piece-card-form', function(e) {
    e.preventDefault();
    var form = this;
    var $btn = $(form).find('.btn-save-piece');
    var formData = new FormData(form);

    $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spin" style="width: 14px; height: 14px;"></i>');

    $.ajax({
      url: window.RACINE + 'dossier_etudiant/saveStatut',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistré !');
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Pièce mise à jour avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Pièce mise à jour avec succès');
          setTimeout(function() { $btn.html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer'); }, 1500);
          table.ajax.reload(null, false);
        } else {
          var err = res.message || 'Erreur lors de l\'enregistrement';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer');
        var msg = 'Erreur lors de la mise à jour.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Action Tout valider rapide
  function validateAllStudentPieces(insCode, etuCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les pièces de ce dossier comme DÉPOSÉES ?")) return;

    $.ajax({
      url: window.RACINE + 'dossier_etudiant/saveAll',
      type: 'POST',
      data: {
        inscription_code: insCode,
        etudiant_code: etuCode
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Toutes les pièces ont été validées avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          table.ajax.reload(null, false);
          $('#modal-dossier').hide();
        } else {
          var msgErr = res.message || 'Erreur lors de la validation.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur de connexion au serveur.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  }

  // Chargement et impression du Bordereau / Récépissé 
  function loadAndOpenRecepisseModal(inscrCode) {
    $.ajax({
      url: window.RACINE + 'dossier_etudiant/getRecepisseData',
      type: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { inscription_code: inscrCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.student) {
          var s = res.student;
          $('#rec-student-name').text((s.nom_etudiant || '') + ' ' + (s.prenom_etudiant || ''));
          $('#rec-student-matricule').text(s.matricule_etudiant || '-');
          $('#rec-student-classe').text(s.libelle_classe || '-');
          $('#rec-student-phone').text(s.telephone_etudiant || '-');
          $('#rec-annee').text(s.libelle_annee ? ('Année Académique ' + s.libelle_annee) : 'GEICG');
          $('#rec-date-emission').text(res.date_emission || '-');
          $('#rec-agent-nom').text(res.agent_nom || 'Administration');

          if (s.nom_etablissement) $('#rec-etab-nom').text(s.nom_etablissement);
          if (s.adresse_etablissement) $('#rec-etab-adresse').text((s.adresse_etablissement || '') + ' ' + (s.ville_etablissement || ''));
          if (s.telephone_etablissement) $('#rec-etab-tel').text('Tél : ' + s.telephone_etablissement);
          if (s.logo_etablissement) $('#rec-etab-logo').attr('src', window.RACINE + s.logo_etablissement).show();

          $('#rec-count-deposees').text(res.deposees_count || 0);
          $('#rec-count-manquantes').text(res.manquantes_count || 0);

          // Table pièces déposées
          var htmlDep = '';
          if (res.pieces_deposees && res.pieces_deposees.length > 0) {
            res.pieces_deposees.forEach(function(p) {
              htmlDep += '<tr>' +
                           '<td style="padding:8px;"><strong>' + escapeHtml(p.libelle_piece) + '</strong></td>' +
                           '<td style="padding:8px;" class="text-success fw-bold">✓ ' + (p.date_depot || 'Déposé') + '</td>' +
                           '<td style="padding:8px;">' + escapeHtml(p.observations || '-') + '</td>' +
                         '</tr>';
            });
          } else {
            htmlDep = '<tr><td colspan="3" class="text-muted text-center" style="padding:10px;">Aucune pièce déposée pour le moment.</td></tr>';
          }
          $('#rec-table-deposees').html(htmlDep);

          // Table pièces manquantes
          var htmlManq = '';
          if (res.pieces_manquantes && res.pieces_manquantes.length > 0) {
            res.pieces_manquantes.forEach(function(p) {
              var badgeSt = p.statut_depot === 'rejete' ? '<span class="badge bg-danger">❌ Rejeté</span>' : '<span class="badge bg-warning text-dark">⏳ En attente</span>';
              htmlManq += '<tr>' +
                            '<td style="padding:8px;"><strong>' + escapeHtml(p.libelle_piece) + '</strong></td>' +
                            '<td style="padding:8px;">' + badgeSt + '</td>' +
                            '<td style="padding:8px;">' + escapeHtml(p.observations || 'À fournir impérativement') + '</td>' +
                          '</tr>';
            });
          } else {
            htmlManq = '<tr><td colspan="3" class="text-success text-center fw-bold" style="padding:10px;">🎉 Dossier complet ! Aucune pièce manquante.</td></tr>';
          }
          $('#rec-table-manquantes').html(htmlManq);

          $('#modal-recepisse').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          var err = res.message || 'Erreur lors de la génération du récépissé.';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
          else if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: err });
        }
      },
      error: function(xhr) {
        var msg = 'Erreur lors de la connexion au serveur.';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
        else if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
      }
    });
  }

  $('#btn-trigger-print-recepisse').on('click', function() {
    window.print();
  });

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  }

  $(document).ready(function() {
    initEtudiantsList();
    initEtudiantsEdit();
    initEtudiantsDetails();
    initEtudiantsChangerClasse();
    initEtudiantsWizard();
    initDossierEtudiant();
    if (window.lucide) lucide.createIcons();
  });
})(window, jQuery);
