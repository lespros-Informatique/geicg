/**
 * Module JS: Registre et Liste des Étudiants
 * Externalisation stricte du code JS pour views/etudiants/list.php
 */
$(document).ready(function() {
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
});

