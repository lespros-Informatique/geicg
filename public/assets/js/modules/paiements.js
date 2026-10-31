/**
 * Module JS Unifié : Trésorerie & Caisse (Règlements, Comptabilité, Détails, Arriérés, Impayés & Sessions de Caisse)
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // =========================================================================
  // 1. SECTION : REÇU & DÉTAILS DU PAIEMENT (views/paiements/details.php)
  // =========================================================================
  if ($('.receipt-page-container, .receipt-outer-frame').length) {
    (function() {
      if (window.lucide) lucide.createIcons();

      var urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('print')) {
        setTimeout(function() {
          window.print();
        }, 400);
      }
    })();
  }

  // =========================================================================
  // 2. SECTION : GESTION DES RÈGLEMENTS DE SCOLARITÉ (views/paiements/list.php)
  // =========================================================================
  if ($('#table-paiements, #paiements-list-config').length) {
    (function() {
function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

$(document).ready(function() {
  var racine = (typeof RACINE !== 'undefined') ? RACINE : '/geicg/';
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  var configEl = document.getElementById('paiements-list-config');
  var config = {};
  if (configEl) {
    try {
      config = JSON.parse(configEl.textContent);
    } catch (e) {
      console.error('Erreur parsing paiements-list-config:', e);
    }
  }

  var canCancelPaiement = Boolean(config.canCancelPaiement);
  var isCaisseActive = Boolean(config.isCaisseOuverte);
  var csrfToken = config.csrfToken || '';

  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Cascading dropdown pour la sélection de classe selon le niveau choisi
  $('#filter-niveau').on('change', function() {
    var selNiv = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiv = $(this).data('niveau');
      if (selNiv === 'ALL' || !selNiv || !optNiv || optNiv === selNiv) {
        $(this).show();
      } else {
        $(this).hide();
        if ($('#filter-classe').val() === $(this).val()) {
          $('#filter-classe').val('ALL').trigger('change.select2');
        }
      }
    });
  });

  var listUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/apiList') : (racine + 'paiement/apiList');

  var table = $('#table-paiements').DataTable({
    order: [],
    ajax: {
      url: listUrl,
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.date_debut = $('#filter-date-debut').val();
        d.date_fin = $('#filter-date-fin').val();
        d.canal_paiement = $('#filter-canal').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', className: 'text-center', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_paiement', width: '120px', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(d || '-') + '</code>';
      } },
      { data: 'date_paiement', width: '120px', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          var dateFr = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
          var timeFr = parts[1] ? parts[1].substring(0,5) : '';
          return '<span style="font-weight:600; color:#334155;">' + dateFr + '</span>' + (timeFr && timeFr !== '00:00' ? ' <span style="font-size:11px; color:#64748B;">' + timeFr + '</span>' : '');
        }
        return escapeHtml(d);
      } },
      { data: 'matricule_etudiant', width: '110px', render: function(d) {
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; border:1px solid #CBD5E1; padding:3px 7px; border-radius:6px; font-size:11.5px;">' + escapeHtml(d || '-') + '</code>';
      } },
      { data: 'etudiant_nom', render: function(d, type, row) {
        var nom = (row && row.nom_etudiant) ? String(row.nom_etudiant).trim() : '';
        var prenom = (row && row.prenom_etudiant) ? String(row.prenom_etudiant).trim() : '';

        if (type === 'filter' || type === 'sort') {
          return (nom + ' ' + prenom).trim() || d || '';
        }

        if (nom && prenom) {
          return '<div style="line-height:1.3; white-space:normal; min-width:140px;">' +
                 '  <div style="font-weight:800; color:#0F172A; font-size:13px; text-transform:uppercase;">' + escapeHtml(nom) + '</div>' +
                 '  <div style="font-weight:600; color:#475569; font-size:12px; margin-top:2px;">' + escapeHtml(prenom) + '</div>' +
                 '</div>';
        }

        var full = (d || (row && row.etudiant_nom) || 'Étudiant non identifié').trim();
        var parts = full.split(' ');
        if (parts.length > 1) {
          var firstPart = parts[0];
          var otherParts = parts.slice(1).join(' ');
          return '<div style="line-height:1.3; white-space:normal; min-width:140px;">' +
                 '  <div style="font-weight:800; color:#0F172A; font-size:13px; text-transform:uppercase;">' + escapeHtml(firstPart) + '</div>' +
                 '  <div style="font-weight:600; color:#475569; font-size:12px; margin-top:2px;">' + escapeHtml(otherParts) + '</div>' +
                 '</div>';
        }

        return '<div style="font-weight:700; color:#0F172A; font-size:13px; white-space:normal; min-width:140px;">' + escapeHtml(full) + '</div>';
      } },
      { data: 'libelle_classe', width: '110px', render: function(d) {
        return '<span style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(d || '-') + '</span>';
      } },
      { data: 'libelle_tranche', render: function(d, type, row) {
        var libelle = (d && d.trim() !== '') ? 'Scolarité (' + escapeHtml(d) + ')' : 'Frais de Scolarité';
        var isEnLigne = (row.canal_paiement === 'en_ligne');
        var canalBadge = isEnLigne
          ? '<span style="font-weight:800; color:#7E22CE; background:#F3E8FF; border:1px solid #D8B4FE; padding:2px 7px; border-radius:6px; font-size:10px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="globe" style="width:11px;height:11px;"></i> En Ligne</span>'
          : '<span style="font-weight:700; color:#475569; background:#F1F5F9; border:1px solid #CBD5E1; padding:2px 7px; border-radius:6px; font-size:10px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="landmark" style="width:11px;height:11px;"></i> Guichet</span>';
        
        var modeStr = (row.mode_paiement || '').toUpperCase();
        var modeBadge = modeStr ? '<span style="font-size:11px; color:#64748B; font-weight:700;">' + escapeHtml(modeStr) + '</span>' : '';
        var refStr = row.reference_paiement ? '<div style="font-size:10.5px; color:#64748B; margin-top:2px;">Réf: <code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; padding:1px 4px; border-radius:3px;">' + escapeHtml(row.reference_paiement) + '</code></div>' : '';

        return '<div>' +
               '  <div style="font-weight:700; color:#15803D; background:#DCFCE7; border:1px solid #86EFAC; padding:3px 8px; border-radius:6px; font-size:11.5px; display:inline-flex; align-items:center; gap:4px; margin-bottom:3px;"><i data-lucide="graduation-cap" style="width:12px;height:12px;"></i> ' + libelle + '</div>' +
               '  <div style="display:flex; align-items:center; gap:6px;">' + canalBadge + ' ' + modeBadge + '</div>' +
               refStr +
               '</div>';
      } },
      { data: 'montant_paiement', width: '160px', className: 'text-end', render: function(d, type, row) {
        var isAnnule = (row.statut_paiement === 'annule');
        if (!d) return '-';
        var formatted = Number(d).toLocaleString('fr-FR') + ' FCFA';
        if (isAnnule) {
          return '<div style="display:flex; flex-direction:column; align-items:flex-end; gap:2px;">' +
                 '  <span style="color:#94A3B8; text-decoration:line-through; font-size:13px; font-weight:600;">' + formatted + '</span>' +
                 '  <span style="background:#FEE2E2; color:#DC2626; border:1px solid #FECACA; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:800; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="slash" style="width:10px;height:10px;"></i> ANNULÉ</span>' +
                 '</div>';
        }
        return '<strong style="color:#15803D; font-size:14px;">' + formatted + '</strong>';
      } },
      { data: null, width: '130px', orderable: false, className: 'text-center', render: function(d, type, row) {
        var idCrypte = d.editId || d.id_paiement;
        var isAnnule = (row.statut_paiement === 'annule');
        var codePaiement = row.code_paiement || '';
        var etudiantNom = row.etudiant_nom || '';
        var montant = Number(row.montant_paiement || 0).toLocaleString('fr-FR') + ' FCFA';

        var printUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/imprimerPdf/' + idCrypte) : (racine + 'paiement/imprimerPdf/' + idCrypte);
        var detailsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/details/' + idCrypte) : (racine + 'paiement/details/' + idCrypte);

        var printBtn = !isAnnule
          ? '  <a href="' + printUrl + '" target="_blank" class="btn btn-sm btn-outline-primary" style="font-weight:700; border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; border:1.5px solid #1E3A5F; color:#1E3A5F; background:#FFFFFF;" title="Imprimer le reçu de versement (PDF)"><i data-lucide="printer" style="width:16px;height:16px;"></i></a>'
          : '  <span class="btn btn-sm" style="border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#F8FAFC; border:1px solid #E2E8F0; color:#CBD5E1; cursor:not-allowed;" title="Reçu annulé (impression désactivée)"><i data-lucide="printer" style="width:16px;height:16px;"></i></span>';

        var detailsBtn = '  <a href="' + detailsUrl + '" class="btn btn-sm btn-info" style="font-weight:700; border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#1E3A5F; border-color:#1E3A5F; color:#FFFFFF;" title="Détails du versement"><i data-lucide="eye" style="width:16px;height:16px;"></i></a>';

        var cancelBtn = '';
        if (canCancelPaiement) {
          if (!isAnnule) {
            cancelBtn = '  <button type="button" class="btn btn-sm btn-action-cancel-paiement btn-annuler-paiement" data-id="' + (row.id_paiement || '') + '" data-code="' + escapeHtml(codePaiement) + '" data-nom="' + escapeHtml(etudiantNom) + '" data-montant="' + escapeHtml(montant) + '" style="font-weight:700; border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center;" title="Annuler le versement"><i data-lucide="x-circle" style="width:16px;height:16px;"></i></button>';
          } else {
            cancelBtn = '  <span class="btn btn-sm" style="border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; background:#FEF2F2; border:1px solid #FECACA; color:#EF4444; cursor:not-allowed; opacity:0.6;" title="Paiement déjà annulé"><i data-lucide="ban" style="width:16px;height:16px;"></i></span>';
          }
        }

        return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:center;">' +
               printBtn +
               detailsBtn +
               cancelBtn +
               '</div>';
      } }
    ],
    language: (typeof dtLang === 'object') ? dtLang : { url: dtLang },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // FONCTION DE GESTION IMPRESSION DU REGISTRE ENCAISSEMENTS (PDF mPDF)
  window.printRegistry = function() {
    var params = {
      annee_code: $('#filter-annee').val() || '',
      niveau_code: $('#filter-niveau').val() || '',
      classe_code: $('#filter-classe').val() || '',
      canal_paiement: $('#filter-canal').val() || '',
      mode_paiement: $('#filter-mode').val() || '',
      categorie_paiement: $('#filter-categorie').val() || '',
      date_debut: $('#filter-date-debut').val() || '',
      date_fin: $('#filter-date-fin').val() || ''
    };
    var basePrintUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/imprimerRegistre') : (racine + 'paiement/imprimerRegistre');
    var url = basePrintUrl + '?' + $.param(params);
    window.open(url, '_blank');
  };

  // Helper d'animation de compteur numérique fluide (CountUp)
  function animateCounter($elem, targetValue, isFloat, duration) {
    if (!$elem || !$elem.length) return;
    duration = duration || 650;
    var rawText = ($elem.text() || '0').toString().replace(/\s/g, '').replace(/,/g, '.');
    var startValue = parseFloat($elem.data('current-val') !== undefined ? $elem.data('current-val') : rawText.replace(/[^0-9.-]/g, '')) || 0;
    $elem.data('current-val', targetValue);
    
    if (Math.abs(startValue - targetValue) < 0.001) {
      if (isFloat) {
        $elem.text(targetValue.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }));
      } else {
        $elem.text(Math.round(targetValue).toLocaleString('fr-FR'));
      }
      return;
    }

    var startTime = null;
    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var ease = 1 - Math.pow(1 - progress, 3);
      var current = startValue + (targetValue - startValue) * ease;
      
      if (isFloat) {
        $elem.text(current.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }));
      } else {
        $elem.text(Math.round(current).toLocaleString('fr-FR'));
      }
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        if (isFloat) {
          $elem.text(targetValue.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }));
        } else {
          $elem.text(Math.round(targetValue).toLocaleString('fr-FR'));
        }
      }
    }
    window.requestAnimationFrame(step);
  }

  // Lancement de l'animation de défilement des compteurs au chargement initial
  setTimeout(function() {
    $('.card-kpi-container [id^="kpi-"]').each(function() {
      var $el = $(this);
      var raw = $el.text().replace(/\s/g, '').replace(/,/g, '.');
      var val = parseFloat(raw) || 0;
      var isFloat = ($el.attr('id') === 'kpi-taux-recouvrement');
      $el.data('current-val', 0);
      $el.text(isFloat ? '0,0' : '0');
      animateCounter($el, val, isFloat, 750);
    });
  }, 120);

  // Fonction de rafraîchissement AJAX des statistiques KPI avec transition animée
  function refreshKpis() {
    var statsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/apiStats') : (racine + 'paiement/apiStats');
    $.ajax({
      url: statsUrl,
      type: 'GET',
      data: {
        annee_code: $('#filter-annee').val(),
        niveau_code: $('#filter-niveau').val(),
        classe_code: $('#filter-classe').val(),
        date_debut: $('#filter-date-debut').val(),
        date_fin: $('#filter-date-fin').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.stats) {
          var s = res.stats;
          animateCounter($('#kpi-total-encaisse'), Number(s.total_encaisse || 0), false);
          animateCounter($('#kpi-taux-recouvrement'), Number(s.taux_recouvrement || 0), true);
          animateCounter($('#kpi-encaisse-aujourdhui'), Number(s.encaisse_aujourdhui || 0), false);
          animateCounter($('#kpi-encaisse-especes'), Number(s.encaisse_especes || 0), false);
          animateCounter($('#kpi-montant-en-attente'), Number(s.montant_en_attente || 0), false);
          
          animateCounter($('#kpi-encaisse-affectes'), Number(s.encaisse_affectes || 0), false);
          animateCounter($('#kpi-encaisse-prives'), Number(s.encaisse_prives || 0), false);
          animateCounter($('#kpi-encaisse-mois'), Number(s.encaisse_mois || 0), false);
          animateCounter($('#kpi-total-exercice'), Number(s.total_exercice_session || s.total_scolarite_attendue || 0), false);
          if (s.annee_exercice_libelle) {
            $('#kpi-exercice-session-libelle').text(s.annee_exercice_libelle);
          }
          
          animateCounter($('#kpi-eleves-soldes'), Number(s.eleves_soldes || 0), false);
          animateCounter($('#kpi-eleves-acomptes'), Number(s.eleves_acomptes || 0), false);
          animateCounter($('#kpi-eleves-non-payeurs'), Number(s.eleves_non_payeurs || 0), false);
          animateCounter($('#kpi-total-inscrits'), Number(s.total_inscrits || 0), false);
        }
      }
    });
  }

  // Événements de changement sur tous les filtres
  $('#filter-annee, #filter-niveau, #filter-classe, #filter-canal, #filter-date-debut, #filter-date-fin').on('change input', function() {
    table.ajax.reload();
    refreshKpis();
  });

  // GESTION DU MODAL D'ENCAISSEMENT DIRECT DE SCOLARITÉ
  var currentStudentSummary = null;

  function updateSubmitButtonState() {
    var mode = $('#modal_select_mode').val() || 'espece';
    var isCash = (mode === 'espece' || mode === 'especes' || mode === 'cash');
    var inscrCode = $('#modal_select_inscription').val();
    var montant = parseFloat($('#input-montant-versement').val() || 0);
    var $optTranche = $('#modal_select_tranche').find('option:selected');
    var isTrancheSoldee = $optTranche.length && ($optTranche.data('soldee') === '1' || $optTranche.data('soldee') === 1);

    var $btn = $('#btn-submit-encaissement');
    var $footerNote = $('#caisse-status-footer-note');
    var $modeAlert = $('#mode-espece-caisse-alert');

    var disableBtn = false;
    var disableReason = '';

    // 1. Session de caisse fermée avec mode Espèces
    if (isCash && !isCaisseActive) {
      disableBtn = true;
      disableReason = "Session de caisse fermée : vous devez ouvrir la session de caisse du jour pour enregistrer un versement en espèces.";
      $footerNote.css('display', 'inline-flex');
      $modeAlert.slideDown(150);
    } else {
      $modeAlert.slideUp(150);
      $footerNote.hide();
    }

    // 2. Règles de validation de saisie et de solde
    if (!disableBtn) {
      if (!inscrCode) {
        disableBtn = true;
        disableReason = "Veuillez d'abord sélectionner un étudiant.";
      } else if (currentStudentSummary && currentStudentSummary.solde_restant <= 0) {
        disableBtn = true;
        disableReason = "Scolarité intégralement soldée pour cet étudiant.";
      } else if (isTrancheSoldee) {
        disableBtn = true;
        disableReason = "Cette tranche de scolarité est déjà intégralement réglée.";
      } else if (montant <= 0) {
        disableBtn = true;
        disableReason = "Le montant du versement doit être supérieur à 0 FCFA.";
      }
    }

    // 3. Application du statut visuel et d'accessibilité sur le bouton
    if (disableBtn) {
      $btn.prop('disabled', true)
          .css({
            'background': '#94A3B8',
            'cursor': 'not-allowed',
            'opacity': '0.65',
            'box-shadow': 'none'
          })
          .attr('title', disableReason);
    } else {
      $btn.prop('disabled', false)
          .css({
            'background': '#16A34A',
            'cursor': 'pointer',
            'opacity': '1',
            'box-shadow': '0 2px 6px rgba(22,163,74,0.3)'
          })
          .removeAttr('title');
    }

    if (window.lucide) lucide.createIcons();
  }

  function openEncaissementModal(preselectedCode) {
    $('#modal-encaisser-scolarite').css('display', 'flex');
    if ($.fn.select2) {
      $('#modal_select_inscription').select2({
        dropdownParent: $('#modal-encaisser-scolarite'),
        width: '100%',
        placeholder: "-- Tapez le nom, matricule ou classe de l'étudiant --",
        allowClear: true,
        escapeMarkup: function(m) { return m; },
        templateResult: function(data) {
          if (!data.id) return data.text;
          var photo = $(data.element).data('photo');
          var nom = $(data.element).data('nom') || data.text;
          var mat = $(data.element).data('matricule') || '';
          var classe = $(data.element).data('classe') || '';
          var inits = (nom || 'ET').split(' ').filter(function(x){return x;}).map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          
          var photoHtml = '';
          if (photo && photo.trim() !== '') {
            photoHtml = '<div style="width: 34px; height: 34px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1.5px solid #CBD5E1; background: #F1F5F9; display: flex; align-items: center; justify-content: center;">' +
                          '<img src="' + escapeHtml(photo) + '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display=\'none\'; $(this).next().show();">' +
                          '<span style="display:none; font-size: 11px; font-weight: 800; color: #1E3A5F;">' + escapeHtml(inits) + '</span>' +
                        '</div>';
          } else {
            photoHtml = '<div style="width: 34px; height: 34px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; border: 1px solid #DBEAFE; font-weight: 800; font-size: 11.5px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">' + escapeHtml(inits) + '</div>';
          }

          return '<div style="display: flex; align-items: center; gap: 10px; padding: 2px 0;">' +
                   photoHtml +
                   '<div style="line-height: 1.25;">' +
                     '<div style="font-weight: 700; color: #0F172A; font-size: 13px;">' + escapeHtml(nom) + '</div>' +
                     '<div style="font-size: 11px; color: #64748B;"><code style="color: #1E3A5F; font-weight: 700; background: #F1F5F9; padding: 1px 4px; border-radius: 3px;">' + escapeHtml(mat) + '</code> &bull; ' + escapeHtml(classe) + '</div>' +
                   '</div>' +
                 '</div>';
        }
      });
    }
    if (preselectedCode) {
      $('#modal_select_inscription').val(preselectedCode).trigger('change');
    }
    updateSubmitButtonState();
    if (window.lucide) lucide.createIcons();
  }

  function closeEncaissementModal() {
    $('#modal-encaisser-scolarite').hide();
    $('#form-encaissement-scolarite')[0].reset();
    if ($.fn.select2) {
      $('#modal_select_inscription').val('').trigger('change.select2');
    }
    $('#encaisse-stu-photo').hide().attr('src', '');
    $('#encaisse-stu-avatar').show().text('ET');
    $('#modal_select_tranche').html('');
    $('#tranche-hint-info').html('');
    $('#hidden_montant_tranche').val(0);
    $('#encaisse-student-summary-card').hide();
    $('#encaisse-payment-inputs-section').hide();
    currentStudentSummary = null;
    updateSubmitButtonState();
  }

  $('#btn-open-encaissement-modal').on('click', function() {
    openEncaissementModal();
  });

  $('.btn-close-modal-encaissement').on('click', function() {
    closeEncaissementModal();
  });

  $('.btn-close-modal-success').on('click', function() {
    $('#modal-recu-paiement-success').hide();
  });

  $('#modal-encaisser-scolarite').on('click', function(e) {
    if ($(e.target).is('#modal-encaisser-scolarite')) {
      closeEncaissementModal();
    }
  });

  $('#modal-recu-paiement-success').on('click', function(e) {
    if ($(e.target).is('#modal-recu-paiement-success')) {
      $('#modal-recu-paiement-success').hide();
    }
  });

  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('action') === 'encaissement') {
    var preCode = urlParams.get('inscription_code') || '';
    openEncaissementModal(preCode);
  }

  // Chargement dynamique de la synthèse financière au choix de l'étudiant
  $('#modal_select_inscription').on('change', function() {
    var val = $(this).val();
    if (!val) {
      $('#encaisse-student-summary-card').slideUp(150);
      $('#encaisse-payment-inputs-section').slideUp(150);
      currentStudentSummary = null;
      return;
    }

    $('#btn-submit-encaissement').prop('disabled', true);
    
    var summaryUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/getStudentFinancialSummary') : (racine + 'paiement/getStudentFinancialSummary');

    $.ajax({
      url: summaryUrl,
      type: 'GET',
      data: { inscription_code: val },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          currentStudentSummary = d;

          var initials = (d.nom_complet || 'ET').split(' ').filter(function(x){return x;}).map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          $('#encaisse-stu-avatar').text(initials || 'ET');
          $('#encaisse-stu-nom').text(d.nom_complet);
          $('#encaisse-stu-mat').text(d.matricule);
          $('#encaisse-stu-classe').text(d.classe + (d.filiere ? ' - ' + d.filiere : ''));
          $('#encaisse-stu-regime-badge').text(d.affectation_label || 'Non Défini');
          if (d.affectation_etat === 'affecte') {
            $('#encaisse-stu-regime-badge').css({'background': '#DCFCE7', 'color': '#15803D', 'border-color': '#86EFAC'});
          } else {
            $('#encaisse-stu-regime-badge').css({'background': '#EFF6FF', 'color': '#1E3A5F', 'border-color': '#BFDBFE'});
          }

          if (d.photo_url && d.photo_url.trim() !== '') {
            $('#encaisse-stu-photo')
              .attr('src', d.photo_url)
              .off('load error')
              .on('load', function() {
                $(this).show();
                $('#encaisse-stu-avatar').hide();
              })
              .on('error', function() {
                $(this).hide();
                $('#encaisse-stu-avatar').show();
              });
          } else {
            $('#encaisse-stu-photo').hide().attr('src', '');
            $('#encaisse-stu-avatar').show();
          }

          $('#encaisse-val-scolarite').text(d.scolarite_due_fmt);
          $('#encaisse-val-paye').text(d.total_paye_fmt);
          $('#encaisse-val-reste').text(d.solde_restant_fmt);

          $('#encaisse-student-summary-card').stop(true, true).slideDown(200);

          var trHtml = '';
          if (d.tranches && d.tranches.length > 0) {
            d.tranches.forEach(function(tr) {
              if (tr.code_tranche === 'FRAIS_ANNEXES') return;
              var suffix = tr.is_soldee ? ' (SOLDÉ)' : '';
              var prefix = '📅 ';
              trHtml += '<option value="' + escapeHtml(tr.code_tranche) + '" data-reste="' + tr.reste_a_payer + '" data-montant="' + tr.montant_tranche + '" data-soldee="' + (tr.is_soldee ? '1' : '0') + '" data-limite="' + escapeHtml(tr.date_limite_fmt || '') + '">' +
                        prefix + escapeHtml(tr.libelle_tranche) + ' - ' + tr.montant_tranche_fmt + suffix +
                        '</option>';
            });
          }
          $('#modal_select_tranche').html(trHtml);

          var suggestCode = d.suggested_tranche_code;
          if (suggestCode === 'FRAIS_ANNEXES' && d.tranches) {
            var firstSco = d.tranches.find(function(t) { return t.code_tranche !== 'FRAIS_ANNEXES' && !t.is_soldee; });
            if (firstSco) suggestCode = firstSco.code_tranche;
          }

          if (suggestCode && $('#modal_select_tranche option[value="' + suggestCode + '"]').length > 0) {
            $('#modal_select_tranche').val(suggestCode);
          } else if ($('#modal_select_tranche option').length > 0) {
            $('#modal_select_tranche').val($('#modal_select_tranche option:first').val());
          }
          
          $('#modal_select_tranche').trigger('change');
          $('#encaisse-payment-inputs-section').stop(true, true).slideDown(200);

          if (d.solde_restant <= 0) {
            $('#montant-max-hint').html('<span style="color:#15803D; font-weight:800;">🎉 Scolarité intégralement soldée pour cet étudiant. Aucun versement supplémentaire attendu.</span>');
          }
          updateSubmitButtonState();

          if (window.lucide) lucide.createIcons();
        } else {
          updateSubmitButtonState();
          showEncaissementError(res.message || 'Impossible de récupérer la situation financière de cet étudiant.');
        }
      },
      error: function() {
        updateSubmitButtonState();
        showEncaissementError('Erreur lors de la communication avec le serveur.');
      }
    });
  });

  $('#modal_select_mode').on('change', function() {
    updateSubmitButtonState();
  });

  $('#input-montant-versement').on('input change', function() {
    updateSubmitButtonState();
  });

  $('#modal_select_tranche').on('change', function() {
    var $opt = $(this).find('option:selected');
    if (!$opt.length) return;

    var resteTranche = parseFloat($opt.data('reste') || 0);
    var montantTranche = parseFloat($opt.data('montant') || resteTranche);
    var isSoldee = $opt.data('soldee') === '1' || $opt.data('soldee') === 1;
    var limite = $opt.data('limite');

    var trancheVal = isSoldee ? 0 : (resteTranche > 0 ? resteTranche : montantTranche);

    $('#hidden_montant_tranche').val(trancheVal);
    $('#input-montant-tranche-scolarite').val(trancheVal.toLocaleString('fr-FR') + ' FCFA');
    $('#input-montant-versement').val(trancheVal);

    if (isSoldee) {
      $('#montant-max-hint').html('<span style="color:#15803D; font-weight:800;">🎉 Cette tranche est déjà intégralement réglée.</span>');
    } else {
      $('#montant-max-hint').html('Montant fixé pour la tranche : <strong style="color: #15803D;">' + trancheVal.toLocaleString('fr-FR') + ' FCFA</strong>');
    }
    updateSubmitButtonState();

    if (limite && limite !== 'Non définie') {
      $('#tranche-hint-info').html('📅 Date d\'échéance : <strong>' + escapeHtml(limite) + '</strong>');
    } else {
      $('#tranche-hint-info').html('');
    }

    if (window.lucide) lucide.createIcons();
  });

  function showEncaissementError(message) {
    message = message || 'Une erreur est survenue lors de l\'encaissement.';
    
    $('#encaisse-error-dynamic-text').html(escapeHtml(message));
    var isCaisseError = (message.indexOf("session de caisse") !== -1 || message.indexOf("Session de caisse") !== -1 || message.indexOf("OUVERTE") !== -1);
    if (isCaisseError) {
      $('#encaisse-error-dynamic-action').show();
    } else {
      $('#encaisse-error-dynamic-action').hide();
    }
    $('#encaisse-error-dynamic-notice').slideDown(200);
    if (window.lucide) lucide.createIcons();

    if (window.Swal) {
      if (isCaisseError) {
        Swal.fire({
          icon: 'warning',
          title: 'Session de Caisse Requise',
          html: '<div style="font-size: 13.5px; color: #334155; line-height: 1.5; text-align: left; padding: 4px;">' +
                  '<div style="background: #FEF2F2; border-left: 4px solid #DC2626; padding: 12px 14px; border-radius: 6px; margin-bottom: 12px; color: #991B1B; font-weight: 600;">' +
                    '<i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> ' + escapeHtml(message) +
                  '</div>' +
                  '<p style="margin: 0 0 8px 0; color: #475569; font-size: 13px;">Pour enregistrer un encaissement en espèces au guichet, vous devez obligatoirement ouvrir la session de caisse du jour.</p>' +
                  '<p style="margin: 0; color: #64748B; font-size: 12px; font-style: italic;">💡 Les modes Mobile Money, Chèque et Virement sont immédiatement acceptés sans ouverture de caisse.</p>' +
                '</div>',
          showCancelButton: true,
          confirmButtonColor: '#1E3A5F',
          cancelButtonColor: '#64748B',
          confirmButtonText: '<i class="fas fa-cash-register"></i> Ouvrir la Session de Caisse',
          cancelButtonText: 'Fermer',
          reverseButtons: true,
          focusConfirm: true
        }).then(function(result) {
          if (result.isConfirmed) {
            $('.btn-open-session-caisse').first().trigger('click');
          }
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Encaissement non validé',
          text: message,
          confirmButtonColor: '#1E3A5F',
          confirmButtonText: 'D\'accord'
        });
      }
    } else if (typeof showToast === 'function') {
      showToast(message, 'error');
    } else if (window.toastr) {
      toastr.error(message);
    } else {
      alert(message);
    }
  }

  // Soumission AJAX du formulaire d'encaissement
  $('#form-encaissement-scolarite').on('submit', function(e) {
    e.preventDefault();
    $('#encaisse-error-dynamic-notice').slideUp(150);

    var inscrCode = $('#modal_select_inscription').val();
    if (!inscrCode) {
      showEncaissementError('Veuillez d\'abord sélectionner un étudiant ou dossier d\'inscription.');
      return;
    }

    var montant = parseFloat($('#input-montant-versement').val() || 0);
    if (montant <= 0) {
      showEncaissementError('Le montant du versement doit être supérieur à 0 FCFA.');
      return;
    }

    var modePaiement = $('#modal_select_mode').val();
    if ((modePaiement === 'espece' || modePaiement === 'especes' || modePaiement === 'cash') && !isCaisseActive) {
      showEncaissementError("Encaissement impossible : Aucune session de caisse n'est OUVERTE pour aujourd'hui. Veuillez ouvrir la session de caisse avant d'encaisser.");
      return;
    }

    var $btn = $('#btn-submit-encaissement');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:6px;"></span> Validation en cours...');

    var addPaiementUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/add') : (racine + 'paiement/add');

    $.ajax({
      url: addPaiementUrl,
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
        updateSubmitButtonState();
        if (window.lucide) lucide.createIcons();

        if (res.status === 1) {
          closeEncaissementModal();
          table.ajax.reload(null, false);
          refreshKpis();

          var codePaiement = res.code_paiement || 'PAI-CONFIRME';
          var idCrypte = res.encrypted_id || res.id_paiement;
          var encInscrId = res.encrypted_inscription_id || '';
          var countVersements = parseInt(res.count_versements || 0, 10);

          $('#success-recu-num').text(codePaiement);

          if (countVersements === 2 && encInscrId) {
            var ficheUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('inscription/fiche/' + encInscrId + '?pdf=1') : (racine + 'inscription/fiche/' + encInscrId + '?pdf=1');
            $('#success-recu-print-link').attr('href', ficheUrl);
          } else {
            var printPdfUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/imprimerPdf/' + idCrypte) : (racine + 'paiement/imprimerPdf/' + idCrypte);
            $('#success-recu-print-link').attr('href', printPdfUrl);
          }

          $('#modal-recu-paiement-success').css('display', 'flex');

          if (window.lucide) lucide.createIcons();
        } else {
          showEncaissementError(res.message || 'Erreur lors de l\'encaissement.');
        }
      },
      error: function(xhr) {
        $btn.html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
        updateSubmitButtonState();
        if (window.lucide) lucide.createIcons();
        var errMsg = 'Une erreur est survenue lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errMsg = xhr.responseJSON.message;
        } else {
          try {
            var r = JSON.parse(xhr.responseText);
            if (r && r.message) errMsg = r.message;
          } catch(e) {}
        }
        showEncaissementError(errMsg);
      }
    });
  });

  // GESTION MODALE OUVERTURE DE CAISSE DU JOUR
  $(document).on('click', '.btn-open-session-caisse', function(e) {
    e.preventDefault();
    $('#form-open-session-caisse')[0].reset();
    $('#session_date').val(new Date().toISOString().split('T')[0]);
    $('#session_fond_initial').val(0);
    $('#modal-open-session-caisse').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#session_fond_initial').focus().select(); }, 100);
  });

  $(document).on('click', '.btn-close-modal-session', function() {
    $('#modal-open-session-caisse').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-open-session-caisse')) {
      $('#modal-open-session-caisse').hide();
    }
  });

  $('#form-open-session-caisse').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-session');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Ouverture...');
    if (window.lucide) lucide.createIcons();

    var addSessionUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('session_caisse/add') : (racine + 'session_caisse/add');

    $.ajax({
      url: addSessionUrl,
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Session de caisse ouverte avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Session de caisse ouverte avec succès');
          $('#modal-open-session-caisse').hide();
          setTimeout(function() {
            window.location.reload();
          }, 600);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'ouverture de la session', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'ouverture de la session');
          else alert(res.message || 'Erreur lors de l\'ouverture de la session');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'ouverture de la session';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
        else alert(msg);
      }
    });
  });

  // Annulation d'un paiement / versement
  $(document).on('click', '.btn-annuler-paiement', function(e) {
    e.preventDefault();
    var idPaiement = $(this).data('id');
    var codePaiement = $(this).data('code') || '';
    var nomEtudiant = $(this).data('nom') || 'l\'étudiant';
    var montant = $(this).data('montant') || '';

    var htmlContent = '<div style="text-align:left; font-size:13px; color:#475569; line-height:1.5;">' +
      '<p>Êtes-vous certain de vouloir annuler le versement <strong style="color:#1E3A5F;">#' + $('<div>').text(codePaiement).html() + '</strong> d\'un montant de <strong style="color:#DC2626;">' + $('<div>').text(montant).html() + '</strong> effectué pour <strong style="color:#0F172A;">' + $('<div>').text(nomEtudiant).html() + '</strong> ?</p>' +
      '<div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:10px 12px; margin:12px 0; color:#92400E; display:flex; gap:8px; align-items:flex-start;">' +
      '  <span style="font-weight:800; font-size:14px;">⚠️</span>' +
      '  <span>Cette opération déduira ce versement du total encaissé en caisse et réajustera le solde de la scolarité de l\'étudiant.</span>' +
      '</div>' +
      '<label style="display:block; font-weight:700; color:#334155; margin-bottom:5px; font-size:12px;">Motif d\'annulation (facultatif) :</label>' +
      '<input type="text" id="swal-input-motif-annulation" class="swal2-input" placeholder="Ex: Erreur de saisie caisse, rejet de chèque..." style="margin:0; width:100%; box-sizing:border-box; font-size:13px; padding:8px 12px; height:38px;">' +
      '</div>';

    if (window.Swal) {
      Swal.fire({
        title: 'Annuler ce versement ?',
        html: htmlContent,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="fas fa-times-circle"></i> Confirmer l\'annulation',
        cancelButtonText: 'Non, conserver',
        reverseButtons: true,
        focusCancel: true,
        preConfirm: function() {
          return $('#swal-input-motif-annulation').val() || '';
        }
      }).then(function(result) {
        if (result.isConfirmed) {
          executeAnnulationPaiement(idPaiement, codePaiement, result.value);
        }
      });
    } else {
      if (confirm('Êtes-vous certain de vouloir annuler le versement #' + codePaiement + ' (' + montant + ') ?')) {
        var motif = prompt('Motif d\'annulation (facultatif) :') || '';
        executeAnnulationPaiement(idPaiement, codePaiement, motif);
      }
    }
  });

  function executeAnnulationPaiement(idPaiement, codePaiement, motif) {
    var annulerUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/annuler') : (racine + 'paiement/annuler');
    $.ajax({
      url: annulerUrl,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id_paiement: idPaiement,
        code_paiement: codePaiement,
        motif: motif || '',
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var successMsg = res.message || 'Paiement annulé avec succès';
          if (typeof showToast === 'function') {
            showToast(successMsg, 'success');
          } else if (window.toastr) {
            toastr.success(successMsg);
          }
          table.ajax.reload(null, false);
          refreshKpis();
        } else {
          var errMsg = res.message || 'Erreur lors de l\'annulation du paiement';
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
});

    })();
  }

  // =========================================================================
  // 3. SECTION : COMPTABILITÉ DES ÉTUDIANTS (views/paiements/comptabilite_etudiants.php)
  // =========================================================================
  if ($('#table-comptabilite-etudiants, #paiements-compta-config').length) {
    (function() {
(function(window, document, $) {
    'use strict';

    var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
    var configEl = document.getElementById('paiements-compta-config');
    var config = {};
    if (configEl) {
        try {
            config = JSON.parse(configEl.textContent);
        } catch (e) {
            console.error('Erreur parsing paiements-compta-config:', e);
        }
    }

    var canRecord = Boolean(config.canRecord);
    var isCaisseOuverte = Boolean(config.isCaisseOuverte);

    var dataTableCompta = null;

    function formatFCFA(amount) {
        return new Intl.NumberFormat('fr-FR').format(Math.round(amount || 0)) + ' FCFA';
    }

    function initDataTableCompta() {
        if ($.fn.DataTable.isDataTable('#table-comptabilite-etudiants')) {
            $('#table-comptabilite-etudiants').DataTable().destroy();
        }

        var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

        dataTableCompta = $('#table-comptabilite-etudiants').DataTable({
            language: {
                url: dtLang
            },
            pageLength: 25,
            autoWidth: false,
            order: [[0, 'asc']],
            dom: '<"row no-print"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row no-print"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            columns: [
                { data: null, render: function(d) {
                    var initStr = d.initiales || 'E';
                    var avatarHtml = '';
                    if (d.has_photo && d.photo) {
                        avatarHtml = '<img src="' + d.photo + '" class="student-avatar" alt="Photo" style="flex-shrink:0;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-flex\';">' +
                                     '<div class="student-avatar-initials" style="display:none; flex-shrink:0;">' + initStr + '</div>';
                    } else {
                        avatarHtml = '<div class="student-avatar-initials" style="flex-shrink:0;">' + initStr + '</div>';
                    }
                    return '<div style="display: flex; align-items: center; gap: 8px; min-width: 170px; white-space: normal;">' +
                                avatarHtml +
                                '<div style="line-height: 1.25;">' +
                                    '<div style="font-weight: 800; color: #0F172A; word-break: break-word;">' + (d.nom_complet || '') + '</div>' +
                                    '<div style="font-size: 11px; color: #64748B; margin-top: 2px;">Mat: <strong style="color: #1E3A5F;">' + (d.matricule || '') + '</strong></div>' +
                                '</div>' +
                            '</div>';
                }},
                { data: null, render: function(d) {
                    return '<div style="min-width: 110px; max-width: 170px; white-space: normal; line-height: 1.25;">' +
                                '<div style="font-weight: 700; color: #1E3A5F; word-break: break-word;">' + (d.classe || '') + '</div>' +
                                '<div style="font-size: 11px; color: #64748B; margin-top: 2px;">Niveau: ' + (d.niveau || '') + '</div>' +
                            '</div>';
                }},
                { data: null, className: 'text-center', render: function(d) {
                    var bCls = (d.regime_code === 'affecte') ? 'badge-info' : 'badge-secondary';
                    return '<span class="badge-status ' + bCls + '">' + (d.regime || '') + '</span>';
                }},
                { data: 'scolarite_due', className: 'text-end font-monospace', render: function(v) { return formatFCFA(v); }},
                { data: 'total_encaisse', className: 'text-end font-monospace text-success fw-bold', render: function(v) { return formatFCFA(v); }},
                { data: 'solde_restant', className: 'text-end font-monospace text-danger fw-bold', render: function(v) { return formatFCFA(v); }},
                { data: null, className: 'text-center', render: function(d) {
                    var barColor = (d.taux_reglement >= 100) ? '#16A34A' : ((d.taux_reglement > 0) ? '#D97706' : '#DC2626');
                    return '<div style="min-width: 64px;">' +
                                '<div style="font-size: 11px; font-weight: 800; margin-bottom: 2px;">' + d.taux_reglement + '%</div>' +
                                '<div style="width: 100%; height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden;">' +
                                    '<div style="width: ' + d.taux_reglement + '%; height: 100%; background: ' + barColor + ';"></div>' +
                                '</div>' +
                            '</div>';
                }},
                { data: null, className: 'text-center', render: function(d) {
                    return '<span class="badge-status ' + (d.badge_class || '') + '">' + (d.statut_libelle || '') + '</span>';
                }},
                { data: null, className: 'text-center no-print', render: function(d) {
                    var btnEncaisse = '';

                    if (canRecord && isCaisseOuverte) {
                        btnEncaisse = '<button type="button" onclick="openModalPayementForStudent(\'' + d.code_inscription + '\')" ' +
                                       'class="btn btn-sm btn-success" ' +
                                       'style="width: 32px; height: 32px; padding: 0; border-radius: 8px; font-weight: 800; background: #16A34A; border: none; color: #FFF; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(22,163,74,0.25);" ' +
                                       'title="Encaisser un versement de scolarité">' +
                                        '<i data-lucide="credit-card" style="width: 15px; height: 15px;"></i>' +
                                       '</button>';
                    } else if (canRecord && !isCaisseOuverte) {
                        btnEncaisse = '<button type="button" onclick="alert(\'La caisse du jour est actuellement fermée. Veuillez d\\\'abord ouvrir une session de caisse.\')" ' +
                                       'class="btn btn-sm btn-secondary" ' +
                                       'style="width: 32px; height: 32px; padding: 0; border-radius: 8px; font-weight: 800; opacity: 0.6; display: inline-flex; align-items: center; justify-content: center;" ' +
                                       'title="Caisse du jour fermée">' +
                                        '<i data-lucide="credit-card" style="width: 15px; height: 15px;"></i>' +
                                       '</button>';
                    }

                    var btnVoir = '<button type="button" onclick="openStudentFinancialSummaryModal(\'' + d.code_inscription + '\')" ' +
                                   'class="btn btn-sm btn-primary" ' +
                                   'style="width: 32px; height: 32px; padding: 0; border-radius: 8px; font-weight: 800; background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(30,58,95,0.2);" ' +
                                   'title="Voir résumé financier & récapitulatif">' +
                                    '<i data-lucide="eye" style="width: 15px; height: 15px;"></i>' +
                                   '</button>';

                    var btnRecuInscription = (d.encrypted_inscription_id || d.encrypted_inscription_code) ? 
                        '<a href="' + racine + 'paiement/imprimerBilanFinancier/' + (d.encrypted_inscription_id || d.encrypted_inscription_code) + '" target="_blank" ' +
                            'class="btn btn-sm btn-info" ' +
                            'style="width: 32px; height: 32px; padding: 0; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; background: #0EA5E9; border-color: #0EA5E9; color: #FFF; box-shadow: 0 2px 4px rgba(14,165,233,0.25);" ' +
                            'title="Imprimer bilan financier de l\'étudiant">' +
                            '<i data-lucide="printer" style="width: 15px; height: 15px;"></i>' +
                         '</a>' : '';

                    var btnProfil = d.encrypted_etudiant_code ? 
                        '<a href="' + racine + 'etudiant/details/' + d.encrypted_etudiant_code + '" target="_blank" ' +
                            'class="btn btn-sm btn-outline-secondary" ' +
                            'style="width: 32px; height: 32px; padding: 0; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; color: #475569; border-color: #CBD5E1;" ' +
                            'title="Consulter le dossier profil de l\'étudiant">' +
                            '<i data-lucide="user" style="width: 15px; height: 15px;"></i>' +
                         '</a>' : '';

                    return '<div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: nowrap;">' +
                                btnEncaisse +
                                btnVoir +
                                btnRecuInscription +
                                btnProfil +
                            '</div>';
                }}
            ],
            drawCallback: function() {
                if (window.lucide) { lucide.createIcons(); }
            }
        });
    }

    function loadComptaData() {
        var selAnneeObj = document.getElementById('filter_annee_code');
        var anneeCode = selAnneeObj ? selAnneeObj.value : '';
        var niveauCode = document.getElementById('filter_niveau_code') ? document.getElementById('filter_niveau_code').value : 'ALL';
        var classeCode = document.getElementById('filter_classe_code') ? document.getElementById('filter_classe_code').value : 'ALL';
        var regime = document.getElementById('filter_regime') ? document.getElementById('filter_regime').value : 'ALL';
        var statutPaiement = document.getElementById('filter_statut_paiement') ? document.getElementById('filter_statut_paiement').value : 'ALL';

        if (selAnneeObj && selAnneeObj.options[selAnneeObj.selectedIndex]) {
            var lbl = selAnneeObj.options[selAnneeObj.selectedIndex].text.replace('(Active)', '').trim();
            var printLbl = document.getElementById('print-annee-label');
            if (printLbl) printLbl.innerText = lbl;
        }

        var url = racine + 'paiement/apiComptabiliteEtudiants?annee_code=' + encodeURIComponent(anneeCode) +
                  '&niveau_code=' + encodeURIComponent(niveauCode) +
                  '&classe_code=' + encodeURIComponent(classeCode) +
                  '&regime=' + encodeURIComponent(regime) +
                  '&statut_paiement=' + encodeURIComponent(statutPaiement);

        fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success && res.data) {
                // Update KPIs
                var elEtud = document.getElementById('kpi-total-etudiants');
                if (elEtud) elEtud.innerText = res.kpis.total_etudiants;
                var elScol = document.getElementById('kpi-total-scolarites');
                if (elScol) elScol.innerText = formatFCFA(res.kpis.total_scolarites);
                var elEnc = document.getElementById('kpi-total-encaisse');
                if (elEnc) elEnc.innerText = formatFCFA(res.kpis.total_encaisse);
                var elRest = document.getElementById('kpi-reste-recouvrer');
                if (elRest) elRest.innerText = formatFCFA(res.kpis.reste_a_recouvrer);
                var elTaux = document.getElementById('kpi-taux-recouvrement');
                if (elTaux) {
                    elTaux.innerText = (res.kpis.taux_recouvrement || 0) + ' %';
                }

                var elCount = document.getElementById('compta-records-count');
                if (elCount) elCount.innerText = res.data.length + ' Étudiants enregistrés';

                // Update DataTable
                if (dataTableCompta) {
                    dataTableCompta.clear().rows.add(res.data).draw();
                }
            }
        })
        .catch(function(err) {
            console.error("Erreur chargement données comptables :", err);
        });
    }

    function applyComptaFilters() {
        loadComptaData();
    }

    function resetComptaFilters() {
        var elNiv = document.getElementById('filter_niveau_code');
        if (elNiv) elNiv.value = 'ALL';
        var elCls = document.getElementById('filter_classe_code');
        if (elCls) elCls.value = 'ALL';
        var elReg = document.getElementById('filter_regime');
        if (elReg) elReg.value = 'ALL';
        var elStat = document.getElementById('filter_statut_paiement');
        if (elStat) elStat.value = 'ALL';
        loadComptaData();
    }

    function printEtatComptabilite() {
        var anneeCode = document.getElementById('filter_annee_code') ? document.getElementById('filter_annee_code').value : '';
        var niveauCode = document.getElementById('filter_niveau_code') ? document.getElementById('filter_niveau_code').value : 'ALL';
        var classeCode = document.getElementById('filter_classe_code') ? document.getElementById('filter_classe_code').value : 'ALL';
        var regime = document.getElementById('filter_regime') ? document.getElementById('filter_regime').value : 'ALL';
        var statutPaiement = document.getElementById('filter_statut_paiement') ? document.getElementById('filter_statut_paiement').value : 'ALL';

        var url = racine + 'paiement/imprimerComptabiliteEtudiants?annee_code=' + encodeURIComponent(anneeCode) +
                  '&niveau_code=' + encodeURIComponent(niveauCode) +
                  '&classe_code=' + encodeURIComponent(classeCode) +
                  '&regime=' + encodeURIComponent(regime) +
                  '&statut_paiement=' + encodeURIComponent(statutPaiement);
        window.open(url, '_blank');
    }

    function openStudentFinancialSummaryModal(inscriptionCode) {
        var anneeCode = document.getElementById('filter_annee_code') ? document.getElementById('filter_annee_code').value : '';
        fetch(racine + 'paiement/getStudentFinancialSummary?inscription_code=' + encodeURIComponent(inscriptionCode) + '&annee_code=' + encodeURIComponent(anneeCode))
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status === 1 || res.success) {
                var data = res.data || res;
                
                var studentName = data.etudiant_nom || data.nom_complet || (data.nom_etudiant ? (data.nom_etudiant + ' ' + (data.prenom_etudiant || '')) : 'Étudiant');
                var studentMatricule = data.matricule || data.matricule_etudiant || '-';
                var studentClasse = data.classe || data.libelle_classe || '-';

                var elName = document.getElementById('sum-student-name');
                if (elName) elName.innerText = studentName;
                var elDetails = document.getElementById('sum-student-details');
                if (elDetails) elDetails.innerText = 'Matricule: ' + studentMatricule + ' | Classe: ' + studentClasse;
                var elPhoto = document.getElementById('sum-student-photo');
                if (elPhoto) elPhoto.src = data.photo || data.photo_url || (racine + 'public/assets/images/default-avatar.png');

                var elScol = document.getElementById('sum-val-scolarite');
                if (elScol) elScol.innerText = formatFCFA(data.scolarite_due);
                var elPay = document.getElementById('sum-val-paye');
                if (elPay) elPay.innerText = formatFCFA(data.total_paye);
                var elRest = document.getElementById('sum-val-reste');
                if (elRest) elRest.innerText = formatFCFA(data.solde_restant);

                // Populate tranches
                var htmlTr = '';
                if (data.tranches && data.tranches.length > 0) {
                    data.tranches.forEach(function(tr) {
                        var libelle = tr.libelle || tr.libelle_tranche || 'Tranche';
                        var montant = tr.montant || tr.montant_tranche || 0;
                        var dejaPaye = tr.deja_paye !== undefined ? tr.deja_paye : 0;
                        var reste = tr.reste !== undefined ? tr.reste : (tr.reste_a_payer !== undefined ? tr.reste_a_payer : 0);
                        var badge = tr.badge || 'badge-info';
                        var statut = tr.statut || tr.statut_libelle || 'En attente';

                        htmlTr += '<tr>' +
                            '<td><strong>' + libelle + '</strong></td>' +
                            '<td style="text-align: right;">' + formatFCFA(montant) + '</td>' +
                            '<td style="text-align: right;" class="text-success">' + formatFCFA(dejaPaye) + '</td>' +
                            '<td style="text-align: right;" class="text-danger">' + formatFCFA(reste) + '</td>' +
                            '<td style="text-align: center;"><span class="badge-status ' + badge + '">' + statut + '</span></td>' +
                        '</tr>';
                    });
                } else {
                    htmlTr = '<tr><td colspan="5" class="text-center text-muted">Aucune tranche configurée.</td></tr>';
                }
                var elBodyTr = document.getElementById('sum-tbody-tranches');
                if (elBodyTr) elBodyTr.innerHTML = htmlTr;

                // Populate payments history
                var htmlPay = '';
                var payments = data.all_payments || data.historique_paiements || [];
                if (payments.length > 0) {
                    payments.forEach(function(p) {
                        var codePaiement = p.code_paiement || ('PAI-' + p.id_paiement);
                        var datePaiement = p.date_paiement_fmt || p.date_paiement || '-';
                        var modePaiement = p.mode_paiement_fmt || p.mode_paiement || 'Espèces';
                        var typePaiement = p.type_transaction || p.type_paiement || 'Versement';
                        var encId = p.encrypted_id || p.id_crypte || '';

                        htmlPay += '<tr>' +
                            '<td>' + datePaiement + '</td>' +
                            '<td class="font-monospace"><strong>' + codePaiement + '</strong></td>' +
                            '<td>' + typePaiement + '</td>' +
                            '<td class="text-uppercase">' + modePaiement + '</td>' +
                            '<td style="text-align: right;" class="fw-bold text-success">' + formatFCFA(p.montant_paiement) + '</td>' +
                            '<td style="text-align: center;">' +
                                '<a href="' + racine + 'paiement/imprimerPdf/' + encId + '" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 2px 6px; font-size: 11px;" title="Imprimer le reçu de versement (PDF)">' +
                                    'Reçu <i data-lucide="printer" style="width: 12px; height: 12px;"></i>' +
                                '</a>' +
                            '</td>' +
                        '</tr>';
                    });
                } else {
                    htmlPay = '<tr><td colspan="6" class="text-center text-muted">Aucun règlement encaissé pour le moment.</td></tr>';
                }
                var elBodyPay = document.getElementById('sum-tbody-paiements');
                if (elBodyPay) elBodyPay.innerHTML = htmlPay;

                var elModal = document.getElementById('modal-student-financial-summary');
                if (elModal) elModal.style.display = 'flex';
                if (window.lucide) { lucide.createIcons(); }
            } else {
                alert(res.message || "Impossible de charger les données financières de cet étudiant.");
            }
        })
        .catch(function(err) {
            console.error("Erreur résumé financier :", err);
            alert("Une erreur de communication est survenue.");
        });
    }

    function closeStudentSummaryModal() {
        var elModal = document.getElementById('modal-student-financial-summary');
        if (elModal) elModal.style.display = 'none';
    }

    function openModalEncaissementCompta() {
        if (dataTableCompta && dataTableCompta.rows().count() > 0) {
            var firstStudent = dataTableCompta.row(0).data();
            if (firstStudent && firstStudent.code_inscription) {
                openModalPayementForStudent(firstStudent.code_inscription);
                return;
            }
        }
        var elModal = document.getElementById('modal-encaisse-versement');
        if (elModal) elModal.style.display = 'flex';
    }

    function openModalPayementForStudent(inscriptionCode) {
        var elModal = document.getElementById('modal-encaisse-versement');
        if (elModal) elModal.style.display = 'flex';
        var sel = document.getElementById('modal_select_inscription');
        if (sel) {
            sel.value = inscriptionCode;
            onInscriptionSelected(inscriptionCode);
        }
    }

    function onInscriptionSelected(inscriptionCode) {
        if (!inscriptionCode) {
            var elCard = document.getElementById('encaisse-student-summary-card');
            if (elCard) elCard.style.display = 'none';
            var elInputs = document.getElementById('encaisse-payment-inputs-section');
            if (elInputs) elInputs.style.display = 'none';
            return;
        }

        var anneeCode = document.getElementById('filter_annee_code') ? document.getElementById('filter_annee_code').value : '';
        fetch(racine + 'paiement/getStudentFinancialSummary?inscription_code=' + encodeURIComponent(inscriptionCode) + '&annee_code=' + encodeURIComponent(anneeCode))
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status === 1 || res.success) {
                var data = res.data || res;

                var studentName = data.etudiant_nom || data.nom_complet || (data.nom_etudiant ? (data.nom_etudiant + ' ' + (data.prenom_etudiant || '')) : 'Étudiant');
                var studentMatricule = data.matricule || data.matricule_etudiant || '-';
                var studentClasse = data.classe || data.libelle_classe || '-';

                var elName = document.getElementById('encaisse-student-name');
                if (elName) elName.innerText = studentName;
                var elMeta = document.getElementById('encaisse-student-meta');
                if (elMeta) elMeta.innerText = 'Matricule: ' + studentMatricule + ' | Classe: ' + studentClasse;
                var elPhoto = document.getElementById('encaisse-photo-preview');
                if (elPhoto) elPhoto.src = data.photo || data.photo_url || (racine + 'public/assets/images/default-avatar.png');

                var elScol = document.getElementById('encaisse-val-scolarite');
                if (elScol) elScol.innerText = formatFCFA(data.scolarite_due);
                var elPay = document.getElementById('encaisse-val-paye');
                if (elPay) elPay.innerText = formatFCFA(data.total_paye);
                var elRest = document.getElementById('encaisse-val-reste');
                if (elRest) elRest.innerText = formatFCFA(data.solde_restant);

                // Populate tranches
                var selTr = document.getElementById('modal_select_tranche');
                if (selTr) {
                    selTr.innerHTML = '';
                    if (data.tranches && data.tranches.length > 0) {
                        var suggested = null;
                        data.tranches.forEach(function(tr) {
                            var opt = document.createElement('option');
                            var trCode = tr.code_tranche || '';
                            var libelle = tr.libelle || tr.libelle_tranche || 'Tranche';
                            var reste = tr.reste !== undefined ? tr.reste : (tr.reste_a_payer !== undefined ? tr.reste_a_payer : 0);
                            
                            opt.value = trCode;
                            opt.text = libelle + ' - Dû: ' + formatFCFA(reste);
                            opt.dataset.montantTranche = reste;
                            selTr.appendChild(opt);

                            if (reste > 0 && !suggested) { 
                                suggested = trCode; 
                            }
                        });
                        if (suggested) selTr.value = suggested;
                    }

                    var selectedOpt = selTr.options[selTr.selectedIndex];
                    var currentTrancheMontant = selectedOpt ? parseFloat(selectedOpt.dataset.montantTranche || 0) : parseFloat(data.suggested_tranche_reste || 0);
                    var elHiddenM = document.getElementById('hidden_montant_tranche');
                    if (elHiddenM) elHiddenM.value = currentTrancheMontant;
                    var elInputM = document.getElementById('input-montant-versement');
                    if (elInputM) elInputM.value = currentTrancheMontant;
                }

                var elSummaryCard = document.getElementById('encaisse-student-summary-card');
                if (elSummaryCard) elSummaryCard.style.display = 'block';
                var elInputsSec = document.getElementById('encaisse-payment-inputs-section');
                if (elInputsSec) elInputsSec.style.display = 'block';
            } else {
                alert(res.message || "Erreur de chargement des données de l'étudiant.");
            }
        })
        .catch(function(err) {
            console.error("Erreur encaissement étudiant :", err);
            alert("Une erreur de communication est survenue.");
        });
    }

    function updateSubmitButtonStateCompta() {
        var isCaisseActive = isCaisseOuverte;
        var selMode = document.getElementById('modal_select_mode');
        var mode = selMode ? selMode.value : 'espece';
        var isCash = (mode === 'espece' || mode === 'especes' || mode === 'cash');
        var btnSubmit = document.getElementById('btn-submit-encaissement');
        var note = document.getElementById('caisse-status-footer-note');

        if (!btnSubmit) return;

        if (isCash && !isCaisseActive) {
            btnSubmit.disabled = true;
            btnSubmit.style.background = '#94A3B8';
            btnSubmit.style.cursor = 'not-allowed';
            btnSubmit.style.opacity = '0.65';
            btnSubmit.title = "Session de caisse fermée : veuillez d'abord ouvrir la caisse du jour pour encaisser en espèces.";
            if (note) note.style.display = 'inline-flex';
        } else {
            btnSubmit.disabled = false;
            btnSubmit.style.background = '#16A34A';
            btnSubmit.style.cursor = 'pointer';
            btnSubmit.style.opacity = '1';
            btnSubmit.removeAttribute('title');
            if (note) note.style.display = 'none';
        }
    }

    function submitEncaissementForm(form) {
        var btnSubmit = document.getElementById('btn-submit-encaissement');
        if (btnSubmit) { btnSubmit.disabled = true; }

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (btnSubmit) { updateSubmitButtonStateCompta(); }
            if (res.status === 1 || res.success) {
                var elModal = document.getElementById('modal-encaisse-versement');
                if (elModal) elModal.style.display = 'none';
                loadComptaData();
                alert(res.message || "Versement encaissé avec succès !");
            } else {
                alert(res.message || res.error || "Erreur lors de l'enregistrement du versement.");
            }
        })
        .catch(function(err) {
            if (btnSubmit) { updateSubmitButtonStateCompta(); }
            console.error("Erreur encaissement :", err);
            alert("Une erreur de communication est survenue avec le serveur.");
        });
    }

    // Exposer sur window pour la compatibilité avec les handlers HTML inline existants
    window.initDataTableCompta = initDataTableCompta;
    window.loadComptaData = loadComptaData;
    window.applyComptaFilters = applyComptaFilters;
    window.resetComptaFilters = resetComptaFilters;
    window.printEtatComptabilite = printEtatComptabilite;
    window.formatFCFA = formatFCFA;
    window.openStudentFinancialSummaryModal = openStudentFinancialSummaryModal;
    window.closeStudentSummaryModal = closeStudentSummaryModal;
    window.openModalEncaissementCompta = openModalEncaissementCompta;
    window.openModalPayementForStudent = openModalPayementForStudent;
    window.onInscriptionSelected = onInscriptionSelected;
    window.updateSubmitButtonStateCompta = updateSubmitButtonStateCompta;
    window.submitEncaissementForm = submitEncaissementForm;

    // Initialisation DOM
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('statut_paiement')) {
            var selStatut = document.getElementById('filter_statut_paiement');
            if (selStatut) selStatut.value = urlParams.get('statut_paiement');
        }
        if (urlParams.has('annee_code')) {
            var selAnnee = document.getElementById('filter_annee_code');
            if (selAnnee) selAnnee.value = urlParams.get('annee_code');
        }
        if (urlParams.has('niveau_code')) {
            var selNiveau = document.getElementById('filter_niveau_code');
            if (selNiveau) selNiveau.value = urlParams.get('niveau_code');
        }
        if (urlParams.has('classe_code')) {
            var selClasse = document.getElementById('filter_classe_code');
            if (selClasse) selClasse.value = urlParams.get('classe_code');
        }
        if (urlParams.has('regime')) {
            var selRegime = document.getElementById('filter_regime');
            if (selRegime) selRegime.value = urlParams.get('regime');
        }

        initDataTableCompta();
        loadComptaData();

        // Event handlers close modals
        document.querySelectorAll('.btn-close-modal-encaissement').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var elModal = document.getElementById('modal-encaisse-versement');
                if (elModal) elModal.style.display = 'none';
            });
        });

        // Handle form encaissement submit
        var formEncaisse = document.getElementById('form-encaisse-versement');
        if (formEncaisse) {
            formEncaisse.addEventListener('submit', function(e) {
                e.preventDefault();
                submitEncaissementForm(this);
            });
        }

        // Handle inscription change in modal
        var selInscr = document.getElementById('modal_select_inscription');
        if (selInscr) {
            selInscr.addEventListener('change', function() {
                onInscriptionSelected(this.value);
            });
        }

        // Handle tranche change in modal
        var selTranche = document.getElementById('modal_select_tranche');
        if (selTranche) {
            selTranche.addEventListener('change', function() {
                var selectedOpt = this.options[this.selectedIndex];
                if (selectedOpt) {
                    var mTranche = parseFloat(selectedOpt.dataset.montantTranche || 0);
                    var elHidden = document.getElementById('hidden_montant_tranche');
                    if (elHidden) elHidden.value = mTranche;
                    var elInput = document.getElementById('input-montant-versement');
                    if (elInput) elInput.value = mTranche;
                }
            });
        }

        var selMode = document.getElementById('modal_select_mode');
        if (selMode) {
            selMode.addEventListener('change', updateSubmitButtonStateCompta);
        }
        updateSubmitButtonStateCompta();
    });

})(window, document, jQuery);

    })();
  }


  // =========================================================================
  // 4. SECTION : GESTION DES ARRIÉRÉS & ENCAISSEMENTS (views/arrieres/list.php)
  // =========================================================================
  if ($('#table-arrieres').length) {
    (function() {
      var GEICG = window.GEICG || {};
function initArrieresModule() {
    var $table = $('#table-arrieres');
    if (!$table.length) return;

    var table;
    var currentStudentData = null;

    function formatMoney(amount) {
      return (GEICG.formatMoney ? GEICG.formatMoney(amount, ' FCFA') : (new Intl.NumberFormat('fr-FR').format(amount || 0) + ' FCFA'));
    }

    function escapeHtml(text) {
      return GEICG.escapeHtml ? GEICG.escapeHtml(text) : String(text || '');
    }

    if ($.fn.dataTable) {
      $.fn.dataTable.ext.errMode = 'none';
    }

    if ($.fn.select2) {
      $('#filter-annee-origine, #filter-niveau, #filter-classe').select2({ width: '100%' });
    }

    // Filtrage en cascade Niveau -> Classe
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
      $('#filter-classe').trigger('change.select2');
    });

    function initTable() {
      table = $table.DataTable({
        ajax: {
          url: GEICG.url('arriere/apiList'),
          type: 'GET',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: function(d) {
            d.annee_origine_code = $('#filter-annee-origine').val();
            d.classe_code = $('#filter-classe').val();
          },
          error: function(xhr, error, thrown) {
            console.error("Erreur de chargement des arriérés:", error, thrown);
          },
          dataSrc: function(json) {
            return json.data || [];
          }
        },
        columns: [
          {
            data: null,
            render: function(d) {
              var rawName = (d.nom_complet ? d.nom_complet.trim() : '') || 
                            ((d.nom_etudiant || '') + ' ' + (d.prenom_etudiant || '')).trim() || 
                            'Étudiant non identifié';
              
              var parts = rawName.split(' ').filter(function(p) { return p.length > 0; });
              var initials = 'ET';
              if (parts.length >= 2) {
                initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
              } else if (parts.length === 1 && parts[0].length >= 2) {
                initials = parts[0].substring(0, 2).toUpperCase();
              }

              var photoPath = d.photo_etudiant ? String(d.photo_etudiant).trim() : '';
              var initialsDiv = '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(30,58,95,0.15);">' + escapeHtml(initials) + '</div>';

              var avatarHtml = initialsDiv;
              if (photoPath !== '') {
                var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : GEICG.url(photoPath.replace(/^\//, ''));
                avatarHtml = '<img src="' + photoUrl + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; flex-shrink: 0;" onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';">' +
                             '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';
              }

              var phone = (d.telephone_etudiant && d.telephone_etudiant !== '-') ? d.telephone_etudiant : null;
              var phoneHtml = phone ? '<div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Tél : ' + escapeHtml(phone) + '</div>' : '<div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Tél : -</div>';

              return '<div style="display: flex; align-items: center; gap: 12px; min-width: 180px;">' +
                       avatarHtml +
                       '<div style="min-width: 0;">' +
                         '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px; line-height: 1.3;">' + escapeHtml(rawName) + '</div>' +
                         phoneHtml +
                       '</div>' +
                     '</div>';
            }
          },
          {
            data: null,
            render: function(d) {
              return '<div>' +
                       '<span class="badge bg-light text-dark font-monospace border" style="font-size: 11.5px; font-weight: 700; padding: 4px 8px;">' + escapeHtml(d.matricule_etudiant || '-') + '</span>' +
                       '<div style="font-weight: 700; color: #1E3A5F; font-size: 12px; margin-top: 3px;">' + escapeHtml(d.classe_actuelle || 'Non assignée') + '</div>' +
                     '</div>';
            }
          },
          {
            data: null,
            render: function(d) {
              var items = d.items || [];
              if (items.length === 0) return '<span class="text-muted" style="font-size: 12px;">Aucun détail</span>';

              var html = '<div style="display: flex; flex-direction: column; gap: 4px;">';
              items.forEach(function(it) {
                html += '<div style="font-size: 11.5px; font-weight: 700;">' +
                          '<span class="badge bg-danger" style="padding: 3px 6px; font-size: 10.5px;">' + escapeHtml(it.libelle_annee) + '</span> ' +
                          '<span style="color: #475569;">' + escapeHtml(it.libelle_classe) + '</span> : ' +
                          '<strong style="color: #DC2626;">' + formatMoney(it.solde_restant) + '</strong>' +
                        '</div>';
              });
              html += '</div>';
              return html;
            }
          },
          {
            data: 'total_initial_du',
            className: 'text-end',
            render: function(d) {
              return '<span style="font-weight: 700; color: #475569; font-size: 13px;">' + formatMoney(d) + '</span>';
            }
          },
          {
            data: 'total_deja_paye',
            className: 'text-end',
            render: function(d) {
              return '<span style="font-weight: 700; color: #16A34A; font-size: 13px;">' + formatMoney(d) + '</span>';
            }
          },
          {
            data: 'total_solde_arriere',
            className: 'text-end',
            render: function(d) {
              return '<span class="badge bg-danger text-white" style="font-size: 13px; font-weight: 800; padding: 6px 12px; border-radius: 8px;">' +
                       formatMoney(d) +
                     '</span>';
            }
          },
          {
            data: null,
            className: 'text-end',
            render: function(d) {
              var totalRegle = parseFloat(d.total_deja_paye) || 0;
              var isZeroRegle = (totalRegle <= 0);

              if (isZeroRegle) {
                return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                         '<button type="button" class="btn btn-sm" disabled style="font-weight: 700; border-radius: 8px; padding: 6px 12px; background: #E2E8F0; color: #94A3B8; border: 1px solid #CBD5E1; cursor: not-allowed; opacity: 0.65;" title="Total réglé égal à 0 : Encaissement désactivé">' +
                           '<i data-lucide="ban" style="width: 14px; height: 14px; margin-right: 4px;"></i> Encaisser Arriéré' +
                         '</button>' +
                       '</div>';
              }

              return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                       '<button type="button" class="btn btn-sm btn-success btn-open-reglement" style="font-weight: 700; border-radius: 8px; padding: 6px 12px; background: #15803D; border: none;" title="Encaisser les arriérés de cet étudiant">' +
                         '<i data-lucide="banknote" style="width: 14px; height: 14px; margin-right: 4px;"></i> Encaisser Arriéré' +
                       '</button>' +
                     '</div>';
            }
          }
        ],
        drawCallback: function() {
          if (window.lucide) lucide.createIcons();
        },
        language: GEICG.dataTableLanguage
      });
    }

    initTable();

    // Filtrage interactif
    $('#filter-annee-origine, #filter-classe').on('change', function() {
      table.ajax.reload();
      refreshKpis();
    });

    $('#btn-refresh-data').on('click', function() {
      table.ajax.reload();
      refreshKpis();
    });

    function refreshKpis() {
      $.ajax({
        url: GEICG.url('arriere/apiStats'),
        type: 'GET',
        dataType: 'json',
        success: function(res) {
          var s = res.stats || {};
          $('#kpi-total-solde').text(formatMoney(s.total_solde || 0));
          $('#kpi-nb-etudiants').text(s.nb_etudiants || 0);
          $('#kpi-total-recouvre').text(formatMoney(s.total_recouvre || 0));
          $('#kpi-taux').text((s.taux_recouvrement || 0) + '%');
        }
      });
    }

    // Ouvrir le modal d'encaissement d'arriéré
    $table.on('click', '.btn-open-reglement', function() {
      var rowData = table.row($(this).closest('tr')).data();
      if (!rowData || (parseFloat(rowData.total_deja_paye) || 0) <= 0) {
        return;
      }
      currentStudentData = rowData;
      openReglementModal(rowData);
    });

    function openReglementModal(d) {
      $('#modal-student-info').text(d.nom_complet + ' | Matricule : ' + d.matricule_etudiant + ' | Classe : ' + d.classe_actuelle);
      
      var $container = $('#container-inscriptions-arrieres');
      $container.empty();

      var items = d.items || [];
      if (items.length === 0) {
        $container.html('<div class="alert alert-info">Aucun arriéré restant sur cet étudiant.</div>');
      } else {
        items.forEach(function(it, idx) {
          var isChecked = (idx === 0) ? 'checked' : '';
          var html = '<label style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: all 0.2s;" class="item-arriere-option">' +
                       '<div style="display: flex; align-items: center; gap: 12px;">' +
                         '<input type="radio" name="inscription_code" value="' + escapeHtml(it.inscription_code) + '" data-solde="' + it.solde_restant + '" ' + isChecked + ' style="width: 18px; height: 18px; accent-color: #15803D;">' +
                         '<div>' +
                           '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">Année Académique : ' + escapeHtml(it.libelle_annee) + ' (' + escapeHtml(it.libelle_classe) + ')</div>' +
                           '<div style="font-size: 11.5px; color: #64748B;">Scolarité Initiale : ' + formatMoney(it.montant_initial) + ' | Déjà payé : ' + formatMoney(it.total_paye) + '</div>' +
                         '</div>' +
                       '</div>' +
                       '<div style="text-align: right;">' +
                         '<span class="badge bg-danger text-white" style="font-size: 12.5px; font-weight: 800; padding: 5px 10px;">Solde : ' + formatMoney(it.solde_restant) + '</span>' +
                       '</div>' +
                     '</label>';
          $container.append(html);
        });
      }

      updateSoldeDispoHelp();
      $('#modal-encaisser-arriere').css('display', 'flex');
    }

    $(document).on('change', 'input[name="inscription_code"]', function() {
      updateSoldeDispoHelp();
    });

    function updateSoldeDispoHelp() {
      var $sel = $('input[name="inscription_code"]:checked');
      if ($sel.length > 0) {
        var solde = parseFloat($sel.data('solde')) || 0;
        $('#lbl-solde-dispo').text(formatMoney(solde));
        $('#input-montant-arriere').attr('max', solde);
        $('#input-montant-arriere').val(solde);
      } else {
        $('#lbl-solde-dispo').text('0 FCFA');
        $('#input-montant-arriere').val('');
      }
    }

    // Fermer les modals
    $('.btn-close-modal-arriere').on('click', function() {
      $('#modal-encaisser-arriere').hide();
    });
    $('.btn-close-modal-recu').on('click', function() {
      $('#modal-recu-arriere').hide();
    });

    // Soumission du formulaire de règlement
    $('#form-reglement-arriere').on('submit', function(e) {
      e.preventDefault();
      
      var $btn = $('#btn-submit-reglement-arriere');
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span> Traitement...');

      $.ajax({
        url: GEICG.url('arriere/enregistrerReglement'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
          $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
          if (window.lucide) lucide.createIcons();

          if (res.status === 1) {
            $('#modal-encaisser-arriere').hide();
            table.ajax.reload();
            refreshKpis();

            if (window.Swal) {
              Swal.fire({
                icon: 'success',
                title: 'Règlement Réussi !',
                text: res.message,
                confirmButtonColor: '#15803D'
              });
            }

            // Afficher le reçu d'apurement
            showRecuModal(res, currentStudentData);
          } else {
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: res.message });
            else alert(res.message);
          }
        },
        error: function(xhr, status, error) {
          $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
          if (window.lucide) lucide.createIcons();
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur Serveur', text: 'Une erreur est survenue lors de l\'enregistrement.' });
        }
      });
    });

    function showRecuModal(res, studentData) {
      var codePaiement = res.code_paiement || 'PAI-ARR-000';
      var montantPaye = formatMoney(res.montant || 0);
      var soldeApres = formatMoney(res.solde_apres || 0);
      var dateFr = new Date().toLocaleDateString('fr-FR') + ' à ' + new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});

      var html = '<div style="border: 2px dashed #CBD5E1; padding: 24px; border-radius: 12px; background: #FFFFFF;">' +
                   '<div style="text-align: center; border-bottom: 2px solid #1E3A5F; padding-bottom: 14px; margin-bottom: 18px;">' +
                     '<h2 style="font-size: 18px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">REÇU DE RÈGLEMENT D\'ARRIÉRÉ</h2>' +
                     '<div style="font-size: 12px; color: #64748B; margin-top: 4px;">Grand Établissement d\'Enseignement Supérieur (GEICG)</div>' +
                     '<div style="font-size: 11px; font-weight: 700; color: #0284C7; margin-top: 4px;">N° REÇU : ' + escapeHtml(codePaiement) + ' | Date : ' + dateFr + '</div>' +
                   '</div>' +
                   
                   '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; font-size: 13px;">' +
                     '<div><strong>Étudiant :</strong> ' + escapeHtml(studentData ? studentData.nom_complet : '-') + '</div>' +
                     '<div><strong>Matricule :</strong> ' + escapeHtml(studentData ? studentData.matricule_etudiant : '-') + '</div>' +
                     '<div><strong>Classe Actuelle :</strong> ' + escapeHtml(studentData ? studentData.classe_actuelle : '-') + '</div>' +
                     '<div><strong>Type d\'Encaissement :</strong> Règlement d\'Arriéré Antérieur</div>' +
                   '</div>' +

                   '<div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: 10px; padding: 16px; margin-bottom: 20px; text-align: center;">' +
                     '<div style="font-size: 12px; font-weight: 800; color: #166534; text-transform: uppercase;">MONTANT ENCAISSÉ SUR ARRIÉRÉ</div>' +
                     '<div style="font-size: 26px; font-weight: 900; color: #15803D; margin-top: 4px;">' + montantPaye + '</div>' +
                     '<div style="font-size: 12px; font-weight: 700; color: #475569; margin-top: 6px;">Solde Arriéré Restant Après Règlement : <span style="color:#DC2626;">' + soldeApres + '</span></div>' +
                   '</div>' +

                   '<div style="display: flex; justify-content: space-between; margin-top: 30px; padding-top: 16px; border-top: 1px solid #E2E8F0; font-size: 11px; text-align: center;">' +
                     '<div style="width: 200px;">' +
                       '<strong>L\'Étudiant / Le Tuteur</strong>' +
                       '<div style="height: 45px;"></div>' +
                       '<div style="color:#94A3B8;">Signature</div>' +
                     '</div>' +
                     '<div style="width: 200px;">' +
                       '<strong>Le Caissier / Agent Comptable</strong>' +
                       '<div style="height: 45px;"></div>' +
                       '<div style="color:#94A3B8;">Cachet & Signature</div>' +
                     '</div>' +
                   '</div>' +
                 '</div>';

      $('#printable-recu-arriere').html(html);
      $('#modal-recu-arriere').css('display', 'flex');
    }

    $('#btn-print-recu-action').on('click', function() {
      var content = $('#printable-recu-arriere').html();
      var win = window.open('', '', 'width=800,height=600');
      win.document.write('<html><head><title>Imprimer Reçu d\'Arriéré</title>');
      win.document.write('<style>body{font-family:sans-serif; padding:20px;}</style></head><body>');
      win.document.write(content);
      win.document.write('</body></html>');
      win.document.close();
      win.focus();
      win.print();
      win.close();
    });
  }

  
      $(document).ready(initArrieresModule);
    })();
  }


  // =========================================================================
  // 5. SECTION : GESTION DES IMPAYÉS & RELANCES (views/impayes/list.php)
  // =========================================================================
  if ($('#table-impayes').length) {
    (function() {
      var GEICG = window.GEICG || {};
function initImpayesModule() {
    var $table = $('#table-impayes');
    if (!$table.length) return;

    if (window.lucide) lucide.createIcons();
    if ($.fn.select2) {
      $('#filter-annee, #filter-niveau, #filter-classe, #filter-severite').select2({ width: '100%' });
    }

    function formatFCFA(val) {
      return Number(val || 0).toLocaleString('fr-FR') + ' FCFA';
    }

    function updatePrintUrl() {
      var url = GEICG.url('impayes/imprimerListeImpayes') +
        '?annee_code=' + encodeURIComponent($('#filter-annee').val() || '') +
        '&niveau_code=' + encodeURIComponent($('#filter-niveau').val() || 'ALL') +
        '&classe_code=' + encodeURIComponent($('#filter-classe').val() || 'ALL') +
        '&severite=' + encodeURIComponent($('#filter-severite').val() || 'ALL');
      $('#btn-print-impayes-list').attr('href', url);
    }

    // Filtrage en cascade Niveau -> Classe
    $('#filter-niveau').on('change', function() {
      var niveauCode = $(this).val();
      $('#filter-classe option').each(function() {
        var optNiveau = $(this).data('niveau');
        if (!niveauCode || niveauCode === 'ALL' || !optNiveau || optNiveau === niveauCode || $(this).val() === 'ALL') {
          $(this).prop('disabled', false);
        } else {
          $(this).prop('disabled', true);
        }
      });
      if ($('#filter-classe option:selected').prop('disabled')) {
        $('#filter-classe').val('ALL').trigger('change.select2');
      } else {
        $('#filter-classe').select2({ width: '100%' });
      }
      updatePrintUrl();
      if (table) table.ajax.reload();
    });

    $('#filter-classe, #filter-annee, #filter-severite').on('change', function() {
      updatePrintUrl();
      if (table) table.ajax.reload();
    });

    updatePrintUrl();

    var selectedRowsData = [];

    var table = $table.DataTable({
      ajax: {
        url: GEICG.url('impayes/apiList'),
        type: 'GET',
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.niveau_code = $('#filter-niveau').val();
          d.classe_code = $('#filter-classe').val();
          d.severite = $('#filter-severite').val();
        },
        dataSrc: function(json) {
          if (json.kpis) {
            $('#kpi-total-impayes').text(formatFCFA(json.kpis.total_impayes_echus));
            $('#kpi-total-etudiants').text(json.kpis.total_etudiants_retard || 0);
            $('#kpi-total-affectes').text(json.kpis.total_affectes || 0);
            $('#kpi-total-non-affectes').text(json.kpis.total_non_affectes || 0);
            $('#kpi-relances-mois').text(json.kpis.total_relances_mois || 0);
          }
          $('#check-all-impayes').prop('checked', false);
          return json.data || [];
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { 
          data: null, 
          orderable: false, 
          className: 'text-center', 
          width: '36px', 
          render: function(d) {
            return `<input type="checkbox" class="check-impaye-row" 
                      data-id="${d.id_inscription}" 
                      data-etudiant-code="${d.code_etudiant}" 
                      data-inscription-code="${d.code_inscription}" 
                      data-phone="${(d.telephone_parent || '').replace(/"/g, '&quot;')}" 
                      data-montant-echu="${d.montant_echu}" 
                      data-student-name="${(d.nom_complet || '').replace(/"/g, '&quot;')}" 
                      data-classe="${(d.classe || '').replace(/"/g, '&quot;')}" 
                      style="width: 17px; height: 17px; cursor: pointer; vertical-align: middle;">`;
          }
        },
        { data: null, className: 'text-center', width: '40px', render: function(d, type, row, meta) {
          return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
        }},
        { 
          data: null,
          render: function(d) {
            var photoPath = d.photo ? String(d.photo).trim() : '';
            if (photoPath && !photoPath.startsWith('http') && !photoPath.startsWith('data:') && !photoPath.startsWith('/')) {
              photoPath = GEICG.url(photoPath);
            }

            var photoHtml = photoPath 
              ? `<div style="position: relative; width: 38px; height: 38px; flex-shrink: 0; display: inline-flex;">
                  <img src="${photoPath}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1.5px solid #CBD5E1;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div style="display: none; width: 38px; height: 38px; border-radius: 50%; background: #1E3A5F; color: #FFF; font-weight: 800; font-size: 13px; align-items: center; justify-content: center;">${d.initiales}</div>
                 </div>`
              : `<div style="width: 38px; height: 38px; border-radius: 50%; background: #1E3A5F; color: #FFF; font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">${d.initiales}</div>`;

            return `<div style="display: flex; align-items: center; gap: 10px;">
                      ${photoHtml}
                      <div>
                        <div style="font-weight: 800; color: #0F172A; font-size: 13px;">${d.nom_complet}</div>
                        <div style="font-size: 11.5px; color: #64748B; font-family: monospace; font-weight: 700;">${d.matricule}</div>
                      </div>
                    </div>`;
          }
        },
        { 
          data: null,
          render: function(d) {
            return `<div>
                      <div style="font-size: 12.5px; font-weight: 700; color: #1E293B;">${d.nom_parent}</div>
                      <div style="font-size: 11.5px; color: #2563EB; font-weight: 700; font-family: monospace;">📞 ${d.telephone_parent}</div>
                    </div>`;
          }
        },
        { 
          data: null,
          render: function(d) {
            var regBadge = d.regime === 'Affecté' ? 'background: #EFF6FF; color: #1D4ED8;' : 'background: #F1F5F9; color: #475569;';
            return `<div>
                      <div style="font-weight: 700; color: #0F172A; font-size: 12.5px;">${d.classe}</div>
                      <span style="${regBadge} font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">${d.regime}</span>
                    </div>`;
          }
        },
        { 
          data: 'echeance_libelle',
          render: function(v) {
            return `<div style="font-weight: 800; color: #0F172A; font-size: 12.5px;">${v || '-'}</div>`;
          }
        },
        { 
          data: 'echeance_date', className: 'text-center',
          render: function(v) {
            return `<span style="font-weight: 800; color: #1E293B; font-size: 12.5px;">${v || '-'}</span>`;
          }
        },
        { 
          data: 'total_paye', className: 'text-end font-monospace',
          render: function(v) {
            return `<div style="font-size: 12.5px; font-weight: 700; color: #16A34A;">${formatFCFA(v)}</div>`;
          }
        },
        { 
          data: 'solde_restant', className: 'text-end font-monospace',
          render: function(v) {
            return `<strong style="font-size: 13px; color: #DC2626; background: #FEF2F2; padding: 4px 8px; border-radius: 6px; border: 1px solid #FCA5A5;">${formatFCFA(v)}</strong>`;
          }
        },
        { 
          data: null, orderable: false, className: 'text-center',
          render: function(d) {
            var btnPrint = `<a href="${GEICG.url('impayes/imprimerRappelPdf/' + d.encrypted_id)}" target="_blank" 
                              class="btn btn-sm" style="width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #1E3A5F; color: #FFF; border: none; display: inline-flex; align-items: center; justify-content: center;" title="Imprimer la Lettre de Rappel PDF">
                              <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
                            </a>`;
            
            var btnRelancer = `<button type="button" class="btn btn-sm btn-relancer-impaye" 
                                data-etudiant-code="${d.code_etudiant}"
                                data-inscription-code="${d.code_inscription}"
                                data-student-name="${(d.nom_complet || '').replace(/"/g, '&quot;')}"
                                data-classe="${(d.classe || '').replace(/"/g, '&quot;')}"
                                data-phone="${(d.telephone_parent || '').replace(/"/g, '&quot;')}"
                                data-montant-echu="${d.montant_echu}"
                                style="width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #EA580C; color: #FFF; border: none; display: inline-flex; align-items: center; justify-content: center;" title="Émettre une Relance (SMS / WhatsApp / Email)">
                                <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                              </button>`;

            var btnPaiement = `<a href="${GEICG.url('paiement/comptabilite_etudiants?annee_code=' + d.annee_code + '&classe_code=' + d.code_classe)}" 
                                class="btn btn-sm" style="width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #16A34A; color: #FFF; border: none; display: inline-flex; align-items: center; justify-content: center;" title="Encaisser en Caisse">
                                <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i>
                              </a>`;

            return `<div style="display: flex; gap: 4px; justify-content: center;">
                      ${btnPrint}
                      ${btnRelancer}
                      ${btnPaiement}
                    </div>`;
          }
        }
      ],
      language: GEICG.dataTableLanguage,
      drawCallback: function() { if (window.lucide) lucide.createIcons(); }
    });

    // Check all / Check row events
    $(document).on('change', '#check-all-impayes', function() {
      $('.check-impaye-row').prop('checked', $(this).prop('checked'));
    });

    // Ouvre le modal de relance individuelle
    $(document).on('click', '.btn-relancer-impaye', function() {
      var etudiantCode = $(this).data('etudiant-code');
      var inscriptionCode = $(this).data('inscription-code');
      var studentName = $(this).data('student-name');
      var classe = $(this).data('classe');
      var phone = $(this).data('phone');
      var montantEchu = $(this).data('montant-echu');

      $('#relance_etudiant_code').val(etudiantCode);
      $('#relance_inscription_code').val(inscriptionCode);
      $('#relance_student_name').text(studentName);
      $('#relance_student_meta').text('Classe : ' + classe);
      $('#relance_telephone_destinataire').val(phone || '');
      $('#relance_montant_impaye').val(montantEchu || 0);

      window.updateRelanceTemplate();
      $('#modalRelanceImpaye').css('display', 'flex');
    });

    $(document).on('click', '.btn-close-modal-relance', function() {
      $('#modalRelanceImpaye').hide();
    });

    $('#modalRelanceImpaye').on('click', function(e) {
      if ($(e.target).is('#modalRelanceImpaye')) {
        $('#modalRelanceImpaye').hide();
      }
    });

    window.updateRelanceTemplate = function() {
      var studentName = $('#relance_student_name').text();
      var montant = formatFCFA($('#relance_montant_impaye').val());
      var type = $('#relance_niveau_relance').val();

      var msg = "";
      if (type === 'rappel_amiable') {
        msg = "GROUPE EICG - Rappel Amiable : Cher parent, nous vous rappelons que la tranche de scolarité de " + studentName + " s'élevant à " + montant + " est arrivée à échéance. Merci de régulariser à la caisse de l'établissement.";
      } else if (type === 'relance_ferme') {
        msg = "GROUPE EICG - Relance de Scolarité : Cher parent, sauf erreur de notre part, le montant de " + montant + " concernant l'élève " + studentName + " demeure impayé. Veuillez régulariser sous 48h afin d'éviter l'interruption des cours.";
      } else {
        msg = "GROUPE EICG - MISE EN DEMEURE : M./Mme le Tuteur de " + studentName + ", un reliquat impayé de " + montant + " est échu. Nous vous prions de vous présenter d'urgence à la caisse de l'école.";
      }
      $('#relance_message_relance').val(msg);
    };

    // LOGIQUE RELANCE GROUPÉE PAR SMS
    $('#btn-open-group-sms-modal').on('click', function() {
      selectedRowsData = [];
      var totalAmount = 0;

      $('.check-impaye-row:checked').each(function() {
        var etudiantCode = $(this).data('etudiant-code');
        var inscriptionCode = $(this).data('inscription-code');
        var studentName = $(this).data('student-name');
        var classe = $(this).data('classe');
        var phone = $(this).data('phone');
        var montantEchu = parseFloat($(this).data('montant-echu')) || 0;

        totalAmount += montantEchu;
        selectedRowsData.push({
          etudiant_code: etudiantCode,
          inscription_code: inscriptionCode,
          student_name: studentName,
          classe: classe,
          phone: phone,
          montant_echu: montantEchu
        });
      });

      if (selectedRowsData.length === 0) {
        if (window.toastr) toastr.warning('Veuillez cocher au moins un étudiant dans la liste pour envoyer la relance SMS groupée.');
        return;
      }

      $('#group_sms_count').text(selectedRowsData.length + ' Étudiant(s) Sélectionné(s)');
      $('#group_sms_total_amount').text(formatFCFA(totalAmount));

      window.updateGroupSMSTemplate();
      $('#modalRelanceGroupSMS').css('display', 'flex');
    });

    // LOGIQUE RELANCE GROUPÉE PAR WHATSAPP
    $('#btn-open-group-whatsapp-modal').on('click', function() {
      selectedRowsData = [];
      var totalAmount = 0;

      $('.check-impaye-row:checked').each(function() {
        var etudiantCode = $(this).data('etudiant-code');
        var inscriptionCode = $(this).data('inscription-code');
        var studentName = $(this).data('student-name');
        var classe = $(this).data('classe');
        var phone = $(this).data('phone');
        var montantEchu = parseFloat($(this).data('montant-echu')) || 0;

        totalAmount += montantEchu;
        selectedRowsData.push({
          etudiant_code: etudiantCode,
          inscription_code: inscriptionCode,
          student_name: studentName,
          classe: classe,
          phone: phone,
          montant_echu: montantEchu
        });
      });

      if (selectedRowsData.length === 0) {
        if (window.toastr) toastr.warning('Veuillez cocher au moins un étudiant dans la liste pour envoyer les relances WhatsApp.');
        return;
      }

      $('#group_sms_count').text(selectedRowsData.length + ' Étudiant(s) Sélectionné(s)');
      $('#group_sms_total_amount').text(formatFCFA(totalAmount));
      $('#group_canal_relance').val('whatsapp');

      window.updateGroupSMSTemplate();
      $('#modalRelanceGroupSMS').css('display', 'flex');
    });

    // LOGIQUE IMPRESSION EN MASSE
    $('#btn-imprimer-en-masse').on('click', function() {
      var selectedIds = [];
      $('.check-impaye-row:checked').each(function() {
        var id = $(this).data('id') || $(this).data('inscription-code');
        if (id) selectedIds.push(id);
      });

      if (selectedIds.length === 0) {
        if (window.toastr) toastr.warning('Veuillez cocher au moins un étudiant dans la liste pour l\'impression en masse des relances.');
        return;
      }

      var url = GEICG.url('impayes/imprimerRappelsMasse?ids=' + encodeURIComponent(selectedIds.join(',')));
      window.open(url, '_blank');
    });

    $(document).on('click', '.btn-close-modal-group-sms', function() {
      $('#modalRelanceGroupSMS').hide();
    });

    $('#modalRelanceGroupSMS').on('click', function(e) {
      if ($(e.target).is('#modalRelanceGroupSMS')) {
        $('#modalRelanceGroupSMS').hide();
      }
    });

    window.updateGroupSMSTemplate = function() {
      var type = $('#group_niveau_relance').val();
      var msg = "";

      if (type === 'rappel_amiable') {
        msg = "GROUPE EICG - Rappel Amiable : Cher parent, nous vous rappelons que la tranche de scolarité de {NOM_ETUDIANT} s'élevant à {MONTANT_ECHU} est arrivée à échéance. Merci de régulariser à la caisse de l'établissement.";
      } else if (type === 'relance_ferme') {
        msg = "GROUPE EICG - Relance de Scolarité : Cher parent, sauf erreur de notre part, le montant de {MONTANT_ECHU} concernant l'élève {NOM_ETUDIANT} ({CLASSE}) demeure impayé. Veuillez régulariser sous 48h afin d'éviter l'interruption des cours.";
      } else {
        msg = "GROUPE EICG - MISE EN DEMEURE : M./Mme le Tuteur de {NOM_ETUDIANT}, un reliquat impayé de {MONTANT_ECHU} est échu. Nous vous prions de vous présenter d'urgence à la caisse de l'école.";
      }
      $('#group_message_relance').val(msg);
    };

    // Soumission Relance Individuelle
    $('#formRelanceImpaye').on('submit', function(e) {
      e.preventDefault();
      var formData = $(this).serialize();

      $.ajax({
        url: GEICG.url('impayes/add'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Relance enregistrée avec succès!');
            $('#modalRelanceImpaye').hide();
            table.ajax.reload(null, false);
          } else {
            if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          }
        },
        error: function() {
          if (window.toastr) toastr.error('Erreur réseau lors de l\'envoi');
        }
      });
    });

    // Soumission Relance Groupée
    $('#formRelanceGroupSMS').on('submit', function(e) {
      e.preventDefault();
      if (selectedRowsData.length === 0) {
        if (window.toastr) toastr.warning('Aucun étudiant sélectionné.');
        return;
      }

      var niveauRelance = $('#group_niveau_relance').val();
      var canalRelance = $('#group_canal_relance').val();
      var messageTemplate = $('#group_message_relance').val();
      var csrfToken = $('input[name="csrf_token"]', this).val();

      $.ajax({
        url: GEICG.url('impayes/addGrouped'),
        type: 'POST',
        data: {
          csrf_token: csrfToken,
          niveau_relance: niveauRelance,
          canal_relance: canalRelance,
          message_relance: messageTemplate,
          items: JSON.stringify(selectedRowsData)
        },
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Relances groupées transmises avec succès!');
            $('#modalRelanceGroupSMS').hide();
            $('#check-all-impayes').prop('checked', false);
            table.ajax.reload(null, false);
          } else {
            if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'envoi des relances groupées');
          }
        },
        error: function() {
          if (window.toastr) toastr.error('Erreur réseau lors de l\'envoi groupé');
        }
      });
    });
  }

  
      $(document).ready(initImpayesModule);
    })();
  }


  // =========================================================================
  // 6. SECTION : SESSIONS DE CAISSE & CLÔTURE (views/sessions_caisse/list.php, cloturer.php)
  // =========================================================================
  if ($('#table_sessions_caisse, #form_cloture_session').length) {
    (function() {
      var GEICG = window.GEICG || {};
function initSessionsCaisseModule() {
    // -------------------------------------------------------------
    // 1. PAGE LISTING : table_sessions_caisse
    // -------------------------------------------------------------
    var $tableSessions = $('#table_sessions_caisse');
    if ($tableSessions.length) {
      if (window.lucide) lucide.createIcons();
      if ($.fn.select2) {
        $('#filter-annee').select2({ width: '100%' });
      }

      var table = $tableSessions.DataTable({
        ajax: {
          url: GEICG.url('session_caisse/apiList'),
          type: 'GET',
          data: function(d) {
            d.annee_code = $('#filter-annee').val();
          }
        },
        processing: true,
        autoWidth: false,
        order: [[0, 'desc']],
        columns: [
          { data: null, width: '50px', render: function(d, type, row, meta) {
            return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
          }},
          { 
            data: 'code_session',
            render: function(data) {
              return '<code style="font-weight:800; color:#1E3A5F;">' + (data || '-') + '</code>';
            }
          },
          { 
            data: 'date_session',
            render: function(data) {
              if (!data) return '-';
              return '<strong>' + new Date(data).toLocaleDateString('fr-FR') + '</strong>';
            }
          },
          {
            data: 'caissier_nom',
            render: function(d, t, r) {
              return '<span style="font-weight:700; color:#0F172A;">' + (d && d.trim() ? d : (r.user_code || 'Caissier')) + '</span>';
            }
          },
          {
            data: 'heure_ouverture',
            render: function(d, t, r) {
              var ouv = d ? d.substring(0, 5) : '--:--';
              var clot = r.heure_cloture ? r.heure_cloture.substring(0, 5) : '--:--';
              return '<span style="font-size:12px; color:#475569;">' + ouv + ' &rarr; ' + clot + '</span>';
            }
          },
          { 
            data: 'fond_initial',
            className: 'text-end',
            render: function(data) {
              return '<span style="font-weight:700; color:#475569;">' + Number(data || 0).toLocaleString('fr-FR') + ' F</span>';
            }
          },
          { 
            data: 'total_general',
            className: 'text-end',
            render: function(data) {
              return '<strong style="color:#1E3A5F; font-size:13.5px;">' + Number(data || 0).toLocaleString('fr-FR') + ' F</strong>';
            }
          },
          { 
            data: 'ecart_caisse',
            className: 'text-center',
            render: function(data) {
              var ec = Number(data || 0);
              if (ec === 0) return '<span style="background:#F0FDF4; color:#15803D; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; border:1px solid #BBF7D0;">0 F</span>';
              if (ec > 0) return '<span style="background:#F0FDF4; color:#15803D; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; border:1px solid #BBF7D0;">+' + ec.toLocaleString('fr-FR') + ' F</span>';
              return '<span style="background:#FEF2F2; color:#DC2626; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px; border:1px solid #FECACA;">' + ec.toLocaleString('fr-FR') + ' F</span>';
            }
          },
          { 
            data: 'statut_session',
            className: 'text-center',
            render: function(data) {
              if (data === 'ouverte') {
                return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11.5px; border:1px solid #BBF7D0;"><i data-lucide="unlock" style="width:11px;height:11px;vertical-align:-1px;"></i> Ouverte</span>';
              }
              if (data === 'valide') {
                return '<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11.5px; border:1px solid #BFDBFE;"><i data-lucide="check-check" style="width:11px;height:11px;vertical-align:-1px;"></i> Validée</span>';
              }
              return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11.5px; border:1px solid #FDE68A;"><i data-lucide="lock" style="width:11px;height:11px;vertical-align:-1px;"></i> Clôturée</span>';
            }
          },
          { 
            data: null,
            orderable: false,
            className: 'text-end',
            render: function(data, type, row) {
              var btns = '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">';
              btns += '<button type="button" class="btn btn-sm btn-show-transactions" data-id="' + (row.editId || row.id_session) + '" data-code="' + (row.code_session || '') + '" style="font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; padding:5px 10px; font-size:12px; background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; cursor:pointer;" title="Voir les transactions"><i data-lucide="receipt" style="width:13px;height:13px;"></i> Transactions</button>';
              if (row.statut_session === 'ouverte') {
                btns += '<a href="' + GEICG.url('session_caisse/cloturer/' + (row.editId || row.id_session)) + '" class="btn btn-sm btn-primary" style="font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px; background:#1E3A5F; padding:5px 10px; font-size:12px;"><i data-lucide="lock" style="width:13px;height:13px;"></i> Clôturer</a>';
              }
              btns += '<a href="' + GEICG.url('session_caisse/details/' + (row.editId || row.id_session)) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; padding:5px 10px; font-size:12px; background:#F1F5F9; color:#1E3A5F; border:1px solid #CBD5E1;"><i data-lucide="file-text" style="width:13px;height:13px;"></i> PV & Détails</a>';
              btns += '</div>';
              return btns;
            }
          }
        ],
        language: GEICG.dataTableLanguage,
        drawCallback: function() { if (window.lucide) lucide.createIcons(); }
      });

      // OUVERTURE DU MODAL TRANSACTIONS DE SESSION
      $(document).on('click', '.btn-show-transactions', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var code = $(this).data('code') || '';
        
        $('#modal-trans-code').text(code || '...');
        $('#modal-trans-sub').text('Chargement des transactions...');
        $('#modal-trans-kpis').html('');
        $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 24px; text-align: center; color: #64748B;"><i data-lucide="loader" class="lucide-spin" style="width: 20px; height: 20px; vertical-align: -4px;"></i> Chargement des opérations en cours...</td></tr>');
        $('#modal-trans-link-pv').attr('href', GEICG.url('session_caisse/details/' + id));
        $('#modal-transactions-session').css('display', 'flex');
        if (window.lucide) lucide.createIcons();

        $.ajax({
          url: GEICG.url('session_caisse/apiTransactions/' + id),
          type: 'GET',
          dataType: 'json',
          success: function(res) {
            if (res.status === 1 && res.data) {
              var s = res.session || {};
              var list = res.data || [];
              
              $('#modal-trans-code').text(s.code_session || code);
              $('#modal-trans-sub').text('Session du ' + (s.date_session || '') + ' • Caissier : ' + (s.caissier_nom || 'Caissier') + ' • Statut : ' + (s.statut_session || ''));

              // Injecter Mini KPIs
              var kpHtml = '';
              kpHtml += '<div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 14px;">';
              kpHtml += '  <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Fond Initial</div>';
              kpHtml += '  <div style="font-size:16px; font-weight:800; color:#0F172A; margin-top:2px;">' + Number(s.fond_initial || 0).toLocaleString('fr-FR') + ' F</div>';
              kpHtml += '</div>';

              kpHtml += '<div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 14px;">';
              kpHtml += '  <div style="font-size:11px; font-weight:700; color:#15803D; text-transform:uppercase;">Total Encaissé Validé</div>';
              kpHtml += '  <div style="font-size:16px; font-weight:900; color:#15803D; margin-top:2px;">' + Number(s.total_valide || 0).toLocaleString('fr-FR') + ' F</div>';
              kpHtml += '  <div style="font-size:11px; color:#166534; font-weight:700;">' + (s.nb_valides || 0) + ' règlement(s) effectif(s)</div>';
              kpHtml += '</div>';

              kpHtml += '<div style="background:' + (s.total_annule > 0 ? '#FEF2F2' : '#F8FAFC') + '; border:1px solid ' + (s.total_annule > 0 ? '#FECACA' : '#E2E8F0') + '; border-radius:8px; padding:10px 14px;">';
              kpHtml += '  <div style="font-size:11px; font-weight:700; color:' + (s.total_annule > 0 ? '#DC2626' : '#64748B') + '; text-transform:uppercase;">Annulations</div>';
              kpHtml += '  <div style="font-size:16px; font-weight:800; color:' + (s.total_annule > 0 ? '#DC2626' : '#64748B') + '; margin-top:2px;">' + Number(s.total_annule || 0).toLocaleString('fr-FR') + ' F</div>';
              kpHtml += '  <div style="font-size:11px; color:' + (s.total_annule > 0 ? '#B91C1C' : '#94A3B8') + '; font-weight:700;">' + (s.nb_annules || 0) + ' annulée(s)</div>';
              kpHtml += '</div>';

              kpHtml += '<div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:10px 14px;">';
              kpHtml += '  <div style="font-size:11px; font-weight:700; color:#1E3A5F; text-transform:uppercase;">Solde Attendu</div>';
              kpHtml += '  <div style="font-size:16px; font-weight:900; color:#1E3A5F; margin-top:2px;">' + Number(s.solde_attendu || 0).toLocaleString('fr-FR') + ' F</div>';
              kpHtml += '  <div style="font-size:11px; color:#475569;">Écart : ' + (s.ecart_caisse > 0 ? '+' : '') + Number(s.ecart_caisse || 0).toLocaleString('fr-FR') + ' F</div>';
              kpHtml += '</div>';

              $('#modal-trans-kpis').html(kpHtml);

              // Remplir tableau
              if (list.length === 0) {
                $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 24px; text-align: center; color: #94A3B8;">Aucune transaction enregistrée au cours de cette session.</td></tr>');
              } else {
                var tbHtml = '';
                for (var i = 0; i < list.length; i++) {
                  var t = list[i];
                  var isAnn = (String(t.statut_paiement).toLowerCase() === 'annule');
                  var rowBg = isAnn ? '#FEF2F2' : '#FFFFFF';
                  var cat = String(t.categorie_paiement || 'SCOLARITE').toUpperCase();
                  
                  tbHtml += '<tr style="border-bottom: 1px solid #F1F5F9; background: ' + rowBg + ';">';
                  tbHtml += '  <td style="padding: 10px 12px; color: #64748B; font-weight: 700;">' + (i + 1) + '</td>';
                  tbHtml += '  <td style="padding: 10px 12px;"><code style="font-weight: 800; color: #1E3A5F;">' + (t.code_paiement || '-') + '</code></td>';
                  tbHtml += '  <td style="padding: 10px 12px; white-space: nowrap; font-size: 12px; color: #334155;"><strong>' + (t.formatted_date || '') + '</strong></td>';
                  tbHtml += '  <td style="padding: 10px 12px;">';
                  tbHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13px;">' + (t.etudiant_complet || '') + '</div>';
                  if (t.matricule_etudiant) tbHtml += '    <div style="font-size: 11px; color: #64748B;">' + t.matricule_etudiant + '</div>';
                  tbHtml += '  </td>';
                  
                  // Type
                  tbHtml += '  <td style="padding: 10px 12px;">';
                  if (cat === 'FRAIS_ANNEXES') {
                    tbHtml += '    <span class="badge" style="background:#F3E8FF; color:#7E22CE; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #E9D5FF;"><i data-lucide="tag" style="width:11px;height:11px;vertical-align:-1px;"></i> Frais Annexes</span>';
                  } else {
                    tbHtml += '    <span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #BFDBFE;"><i data-lucide="graduation-cap" style="width:11px;height:11px;vertical-align:-1px;"></i> Scolarité</span>';
                    if (t.libelle_tranche) {
                      tbHtml += '    <div style="font-size: 11px; color: #475569; margin-top: 2px;">' + t.libelle_tranche + '</div>';
                    }
                  }
                  tbHtml += '  </td>';

                  // Mode
                  tbHtml += '  <td style="padding: 10px 12px;">';
                  tbHtml += '    <span class="badge" style="background:#F8FAFC; color:#334155; font-weight:700; padding:3px 7px; border-radius:5px; font-size:11px; text-transform:uppercase; border:1px solid #CBD5E1;">' + (t.mode_paiement || 'espece') + '</span>';
                  tbHtml += '  </td>';

                  // Statut
                  tbHtml += '  <td style="padding: 10px 12px; text-align: center;">';
                  if (isAnn) {
                    tbHtml += '    <span class="badge" style="background:#FEE2E2; color:#DC2626; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #FECACA;"><i data-lucide="x-circle" style="width:11px;height:11px;vertical-align:-1px;"></i> Annulé</span>';
                    if (t.observations) tbHtml += '    <div style="font-size:10px; color:#B91C1C; margin-top:2px; max-width:140px; line-height:1.2; font-style:italic;">' + t.observations + '</div>';
                  } else {
                    tbHtml += '    <span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:3px 8px; border-radius:6px; font-size:11px; border:1px solid #BBF7D0;"><i data-lucide="check" style="width:11px;height:11px;vertical-align:-1px;"></i> Confirmé</span>';
                  }
                  tbHtml += '  </td>';

                  // Montant
                  tbHtml += '  <td style="padding: 10px 12px; text-align: right;">';
                  if (isAnn) {
                    tbHtml += '    <del style="font-weight:700; color:#94A3B8; font-size:13px;">' + (t.formatted_montant || '') + '</del>';
                    tbHtml += '    <div style="font-size:10.5px; color:#DC2626; font-weight:700;">Exclu</div>';
                  } else {
                    tbHtml += '    <span style="font-weight:900; color:#0F172A; font-size:13.5px;">' + (t.formatted_montant || '') + '</span>';
                  }
                  tbHtml += '  </td>';

                  tbHtml += '</tr>';
                }
                $('#modal-trans-tbody').html(tbHtml);
              }
              if (window.lucide) lucide.createIcons();
            } else {
              $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 20px; text-align: center; color: #DC2626;">Erreur lors du chargement des transactions.</td></tr>');
            }
          },
          error: function() {
            $('#modal-trans-tbody').html('<tr><td colspan="8" style="padding: 20px; text-align: center; color: #DC2626;">Erreur de communication avec le serveur.</td></tr>');
          }
        });
      });

      $('.btn-close-modal-trans').on('click', function() {
        $('#modal-transactions-session').hide();
      });

      $(window).on('click', function(e) {
        if ($(e.target).is('#modal-transactions-session')) {
          $('#modal-transactions-session').hide();
        }
      });

      $('#filter-annee').on('change', function() {
        var val = $(this).val();
        window.location.href = GEICG.url('session_caisse/list?annee_code=' + encodeURIComponent(val));
      });

      // GESTION MODALE OUVERTURE DE CAISSE DU JOUR
      $(document).on('click', '.btn-open-session-caisse', function(e) {
        e.preventDefault();
        $('#form-open-session-caisse')[0].reset();
        $('#session_date').val(new Date().toISOString().split('T')[0]);
        $('#session_fond_initial').val(0);
        $('#modal-open-session-caisse').css('display', 'flex');
        if (window.lucide) lucide.createIcons();
        setTimeout(function() { $('#session_fond_initial').focus().select(); }, 100);
      });

      $('.btn-close-modal-session').on('click', function() {
        $('#modal-open-session-caisse').hide();
      });

      $(window).on('click', function(e) {
        if ($(e.target).is('#modal-open-session-caisse')) {
          $('#modal-open-session-caisse').hide();
        }
      });

      $('#form-open-session-caisse').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-submit-session');
        $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Ouverture...');
        if (window.lucide) lucide.createIcons();

        $.ajax({
          url: GEICG.url('session_caisse/add'),
          type: 'POST',
          data: $(this).serialize(),
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          dataType: 'json',
          success: function(res) {
            $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
            if (window.lucide) lucide.createIcons();
            if (res.status === 1 || res.success) {
              if (typeof showToast === 'function') showToast(res.message || 'Session de caisse ouverte avec succès', 'success');
              else if (window.toastr) toastr.success(res.message || 'Session de caisse ouverte avec succès');
              $('#modal-open-session-caisse').hide();
              setTimeout(function() {
                window.location.reload();
              }, 600);
            } else {
              if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'ouverture de la session', 'error');
              else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'ouverture de la session');
            }
          },
          error: function(xhr) {
            $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
            if (window.lucide) lucide.createIcons();
            var msg = 'Erreur lors de l\'enregistrement';
            try {
              var json = JSON.parse(xhr.responseText);
              if (json.message) msg = json.message;
            } catch(ex) {}
            if (typeof showToast === 'function') showToast(msg, 'error');
            else if (window.toastr) toastr.error(msg);
          }
        });
      });
    }

    // -------------------------------------------------------------
    // 2. PAGE CLÔTURE DE SESSION : recalculateEcart
    // -------------------------------------------------------------
    var $clotureForm = $('form#form-cloture-session, form[action*="session_caisse/cloturer"]');
    if ($clotureForm.length) {
      if (window.lucide) lucide.createIcons();

      var fondInitial = parseFloat($clotureForm.data('fond-initial') || $('#panel-ecart').data('fond-initial') || 0);

      function recalculateEcart() {
        var esp = parseFloat($('#inp_total_especes').val()) || 0;
        var mob = parseFloat($('#inp_total_mobile').val()) || 0;
        var chq = parseFloat($('#inp_total_cheque').val()) || 0;
        var totalTheorique = fondInitial + esp + mob + chq;

        var physique = parseFloat($('#inp_montant_physique').val()) || 0;
        var ecart = physique - totalTheorique;

        if (ecart === 0) {
          $('#panel-ecart').css({ 'background': '#F0FDF4', 'border-color': '#BBF7D0' });
          $('#ecart-val-text').css('color', '#15803D').text('0 FCFA (Caisse Équilibrée)');
          $('#ecart-badge').css({ 'background': '#DCFCE7', 'color': '#15803D' }).text('Parfaitement Conforme');
        } else if (ecart > 0) {
          $('#panel-ecart').css({ 'background': '#EFF6FF', 'border-color': '#BFDBFE' });
          $('#ecart-val-text').css('color', '#1E40AF').text('+' + ecart.toLocaleString('fr-FR') + ' FCFA (Excédent)');
          $('#ecart-badge').css({ 'background': '#DBEAFE', 'color': '#1E40AF' }).text('Excédent constaté');
        } else {
          $('#panel-ecart').css({ 'background': '#FEF2F2', 'border-color': '#FECACA' });
          $('#ecart-val-text').css('color', '#DC2626').text(ecart.toLocaleString('fr-FR') + ' FCFA (Déficit)');
          $('#ecart-badge').css({ 'background': '#FEE2E2', 'color': '#DC2626' }).text('Manquant de caisse');
        }
      }

      $('.calc-field, #inp_montant_physique').on('input', recalculateEcart);
      recalculateEcart();
    }
  }

  
      $(document).ready(initSessionsCaisseModule);
    })();
  }

})(window, document, jQuery);