/**
 * Module JS Unifié : Paiements (Liste des Règlements, Comptabilité Étudiants, Détails / Reçus)
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

})(window, document, jQuery);