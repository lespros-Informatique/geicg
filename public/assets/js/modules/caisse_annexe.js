/**
 * Module JS Unifié : Caisse Annexe & Frais Annexes (Catalogue des frais, Encaissement direct, Reçus & Règlements)
 * GEICG - Application Web
 */
(function(window, document, $) {
  'use strict';

  // =========================================================================
  // 1. VUE : CAISSE ANNEXE (views/caisse_annexe/list.php)
  // =========================================================================
  function initCaisseAnnexeModule() {
    if (!$('#table-caisse-annexe').length) return;


  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  var racine = (typeof RACINE !== 'undefined') ? RACINE : '/geicg/';
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // Animation compteur
  function animateCounter($elem, targetValue, isFloat, duration) {
    if (!$elem || !$elem.length) return;
    duration = duration || 650;
    var rawText = ($elem.text() || '0').toString().replace(/\s/g, '').replace(/,/g, '.');
    var startValue = parseFloat($elem.data('current-val') !== undefined ? $elem.data('current-val') : rawText.replace(/[^0-9.-]/g, '')) || 0;
    $elem.data('current-val', targetValue);
    var startTime = null;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var easeOut = 1 - Math.pow(1 - progress, 3);
      var current = startValue + (targetValue - startValue) * easeOut;

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

  // Défilement initial
  setTimeout(function() {
    $('.card-kpi-container [id^="kpi-"]').each(function() {
      var $el = $(this);
      var raw = $el.text().replace(/\s/g, '').replace(/,/g, '.');
      var val = parseFloat(raw) || 0;
      var isFloat = ($el.attr('id') === 'kpi-taux-recouvrement-fa');
      $el.data('current-val', 0);
      $el.text(isFloat ? '0,0' : '0');
      animateCounter($el, val, isFloat, 750);
    });
  }, 120);

  // Initialisation DataTables Caisse Annexe
  var listUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('caisse_annexe/apiList') : (racine + 'caisse_annexe/apiList');

  var table = $('#table-caisse-annexe').DataTable({
    order: [],
    ajax: {
      url: listUrl,
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.type_filiere = $('#filter-type-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.mode_paiement = $('#filter-mode').val();
        d.date_debut = $('#filter-date-debut').val();
        d.date_fin = $('#filter-date-fin').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', className: 'text-center', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_paiement', width: '120px', render: function(d) {
        return '<code style="font-weight:800; color:#B45309; background:#FEF3C7; border:1px solid #FDE68A; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(d || '-') + '</code>';
      } },
      { data: 'date_paiement', width: '120px', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          return '<span style="font-weight:600; color:#0F172A;">' + dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + '</span>' +
                 (parts[1] ? ' <small style="color:#64748B; font-size:10.5px;">' + parts[1].substr(0,5) + '</small>' : '');
        }
        return d;
      } },
      { data: 'matricule_etudiant', width: '110px', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:2px 6px; border-radius:4px;">' + escapeHtml(d || '-') + '</code>';
      } },
      { data: 'etudiant_nom', render: function(d, type, row) {
        var photo = row.photo_inscription || row.photo_etudiant;
        var inits = (d || 'ET').split(' ').filter(function(x){return x;}).map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
        var photoHtml = '';
        if (photo && photo.trim() !== '') {
          var pUrl = racine + photo.replace(/^\/+/, '');
          photoHtml = '<div style="width:32px; height:32px; border-radius:8px; overflow:hidden; flex-shrink:0; border:1px solid #CBD5E1; background:#F8FAFC;">' +
                        '<img src="' + escapeHtml(pUrl) + '" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display=\'none\'; $(this).next().show();">' +
                        '<span style="display:none; font-size:11px; font-weight:800; color:#1E3A5F;">' + escapeHtml(inits) + '</span>' +
                      '</div>';
        } else {
          photoHtml = '<div style="width:32px; height:32px; border-radius:8px; background:#EFF6FF; color:#1E3A5F; font-size:11px; font-weight:800; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">' + escapeHtml(inits) + '</div>';
        }
        return '<div style="display:flex; align-items:center; gap:9px;">' +
                 photoHtml +
                 '<span style="font-weight:700; color:#0F172A;">' + escapeHtml(d || '-') + '</span>' +
               '</div>';
      } },
      { data: 'libelle_classe', width: '110px', render: function(d, type, row) {
        var niv = row.libelle_niveau || '';
        return '<div>' +
                 '<strong style="color:#1E3A5F;">' + escapeHtml(d || '-') + '</strong>' +
                 (niv ? '<div style="font-size:10.5px; color:#64748B;">' + escapeHtml(niv) + '</div>' : '') +
               '</div>';
      } },
      { data: 'type_filiere', render: function(d) {
        var t = (d || '').toUpperCase();
        var isIndus = (t === 'INDUSTRIELLE');
        var bg = isIndus ? '#E0F2FE' : '#E0E7FF';
        var col = isIndus ? '#0369A1' : '#3730A3';
        var brd = isIndus ? '#BAE6FD' : '#C7D2FE';
        var lbl = isIndus ? 'Industrielle' : 'Tertiaire';
        return '<span style="font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; background:' + bg + '; color:' + col + '; border:1px solid ' + brd + ';">' + lbl + '</span>';
      } },
      { data: 'type_paiement', render: function(d) {
        return '<span style="font-weight:600; color:#475569;"><i data-lucide="tag" style="width:13px;height:13px;vertical-align:middle;color:#B45309;margin-right:4px;"></i>' + escapeHtml(d || 'Frais Annexes') + '</span>';
      } },
      { data: 'mode_paiement', render: function(d) {
        var m = (d || '').toLowerCase();
        var badgeStyle = 'background:#F1F5F9; color:#475569; border:1px solid #CBD5E1;';
        var label = ucfirst(m || 'Espèce');
        if (m.indexOf('espece') !== -1 || m.indexOf('cash') !== -1) {
          badgeStyle = 'background:#DCFCE7; color:#15803D; border:1px solid #86EFAC;';
          label = '💵 Espèces';
        } else if (m.indexOf('mobile') !== -1 || m.indexOf('wave') !== -1 || m.indexOf('orange') !== -1 || m.indexOf('mtn') !== -1 || m.indexOf('moov') !== -1) {
          badgeStyle = 'background:#CFFAFE; color:#0E7490; border:1px solid #A5F3FC;';
          label = '📱 Mobile Money';
        } else if (m.indexOf('cheque') !== -1) {
          badgeStyle = 'background:#FEF3C7; color:#B45309; border:1px solid #FDE68A;';
          label = '🏦 Chèque';
        } else if (m.indexOf('virement') !== -1) {
          badgeStyle = 'background:#F3E8FF; color:#7E22CE; border:1px solid #D8B4FE;';
          label = '🏛️ Virement';
        }
        return '<span style="font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; display:inline-block; ' + badgeStyle + '">' + label + '</span>';
      } },
      { data: 'montant_paiement', className: 'text-end', width: '130px', render: function(d) {
        var val = parseFloat(d) || 0;
        return '<span style="font-weight:900; font-size:13.5px; color:#15803D; background:#F0FDF4; padding:3px 8px; border-radius:6px; border:1px solid #BBF7D0;">' + val.toLocaleString('fr-FR') + ' FCFA</span>';
      } },
      { data: null, className: 'text-center', width: '90px', orderable: false, render: function(d, type, row) {
        var idCrypte = row.encrypted_id || row.editId || row.id_paiement;
        var printUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/imprimerPdf/' + idCrypte) : (racine + 'paiement/imprimerPdf/' + idCrypte);
        var recuUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/recu/' + idCrypte) : (racine + 'paiement/recu/' + idCrypte);
        return '<div style="display:flex; justify-content:center; gap:6px;">' +
                 '<a href="' + printUrl + '" target="_blank" class="btn btn-sm btn-outline-primary" style="padding:4px 8px; border-radius:6px;" title="Imprimer le reçu officiel">' +
                   '<i data-lucide="printer" style="width:14px;height:14px;"></i>' +
                 '</a>' +
                 '<a href="' + recuUrl + '" target="_blank" class="btn btn-sm btn-outline-secondary" style="padding:4px 8px; border-radius:6px;" title="Consulter la quittance">' +
                   '<i data-lucide="eye" style="width:14px;height:14px;"></i>' +
                 '</a>' +
               '</div>';
      } }
    ],
    language: (typeof dtLang === 'object') ? dtLang : { url: dtLang },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Rafraîchissement des KPIs AJAX
  function refreshKpis() {
    var statsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('caisse_annexe/apiStats') : (racine + 'caisse_annexe/apiStats');
    $.ajax({
      url: statsUrl,
      type: 'GET',
      data: {
        annee_code: $('#filter-annee').val(),
        type_filiere: $('#filter-type-filiere').val(),
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
          animateCounter($('#kpi-taux-recouvrement-fa'), Number(s.taux_recouvrement_fa || 0), true);
          animateCounter($('#kpi-encaisse-aujourdhui'), Number(s.encaisse_aujourdhui || 0), false);
          animateCounter($('#kpi-encaisse-especes'), Number(s.encaisse_especes || 0), false);
          animateCounter($('#kpi-attente-frais-annexes'), Number(s.attente_frais_annexes || 0), false);

          animateCounter($('#kpi-encaisse-industrielle'), Number(s.encaisse_industrielle || 0), false);
          animateCounter($('#kpi-encaisse-tertiaire'), Number(s.encaisse_tertiaire || 0), false);
          animateCounter($('#kpi-encaisse-mobile-money'), Number((s.encaisse_mobile_money || 0) + (s.encaisse_banque || 0)), false);
          animateCounter($('#kpi-total-frais-annexes-attendus'), Number(s.total_frais_annexes_attendus || 0), false);

          animateCounter($('#kpi-eleves-soldes'), Number(s.eleves_soldes || 0), false);
          animateCounter($('#kpi-eleves-non-soldes'), Number(s.eleves_non_soldes || 0), false);
          animateCounter($('#kpi-total-recus'), Number(s.total_recus || 0), false);
          animateCounter($('#kpi-total-inscrits'), Number(s.total_inscrits || 0), false);

          if (s.annee_exercice_libelle) {
            $('#kpi-exercice-session-libelle').text(s.annee_exercice_libelle);
          }

          // Mise à jour de la bannière d'impression
          $('#print-kpi-total').text(Number(s.total_encaisse || 0).toLocaleString('fr-FR') + ' FCFA');
          $('#print-kpi-especes').text(Number(s.encaisse_especes || 0).toLocaleString('fr-FR') + ' FCFA');
          $('#print-kpi-mobile-money').text(Number(s.encaisse_mobile_money || 0).toLocaleString('fr-FR') + ' FCFA');
          $('#print-kpi-budget').text(Number(s.total_frais_annexes_attendus || 0).toLocaleString('fr-FR') + ' FCFA');
          $('#print-kpi-reste').text(Number(s.attente_frais_annexes || 0).toLocaleString('fr-FR') + ' FCFA');
        }
      }
    });
  }

  // Événements de filtre
  $('#filter-annee, #filter-type-filiere, #filter-niveau, #filter-classe, #filter-mode, #filter-date-debut, #filter-date-fin').on('change input', function() {
    table.ajax.reload();
    refreshKpis();
  });

  // Impression
  window.printRegistry = function() {
    var params = {
      annee_code: $('#filter-annee').val() || '',
      type_filiere: $('#filter-type-filiere').val() || '',
      niveau_code: $('#filter-niveau').val() || '',
      classe_code: $('#filter-classe').val() || '',
      mode_paiement: $('#filter-mode').val() || '',
      date_debut: $('#filter-date-debut').val() || '',
      date_fin: $('#filter-date-fin').val() || ''
    };
    var basePrintUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('caisse_annexe/imprimerRegistre') : (racine + 'caisse_annexe/imprimerRegistre');
    var url = basePrintUrl + '?' + $.param(params);
    window.open(url, '_blank');
  };

  // GESTION DU MODAL D'ENCAISSEMENT DIRECT DE FRAIS ANNEXE
  function openEncaissementModal() {
    $('#modal-encaisser-annexe').css('display', 'flex');
    if ($.fn.select2) {
      $('#modal_select_inscription').select2({
        dropdownParent: $('#modal-encaisser-annexe'),
        width: '100%',
        placeholder: "-- Tapez le nom, matricule ou classe de l'étudiant --",
        allowClear: true
      });
    }
    if (window.lucide) lucide.createIcons();
  }

  function closeEncaissementModal() {
    $('#modal-encaisser-annexe').hide();
    $('#form-encaissement-annexe')[0].reset();
    if ($.fn.select2) {
      $('#modal_select_inscription').val('').trigger('change.select2');
    }
    $('#encaisse-stu-photo').hide().attr('src', '');
    $('#encaisse-stu-avatar').show().text('ET');
    $('#encaisse-student-summary-card').hide();
    $('#encaisse-payment-inputs-section').hide();
  }

  $('#btn-open-encaissement-annexe-modal').on('click', function() {
    openEncaissementModal();
  });

  $('.btn-close-modal-encaissement').on('click', function() {
    closeEncaissementModal();
  });

  $('.btn-close-modal-success').on('click', function() {
    $('#modal-recu-annexe-success').hide();
  });

  $('#modal-encaisser-annexe').on('click', function(e) {
    if ($(e.target).is('#modal-encaisser-annexe')) {
      closeEncaissementModal();
    }
  });

  $('#modal_select_inscription').on('change', function() {
    var val = $(this).val();
    if (!val) {
      $('#encaisse-student-summary-card').slideUp(150);
      $('#encaisse-payment-inputs-section').slideUp(150);
      return;
    }

    var summaryUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('caisse_annexe/getStudentAnnexesSummary') : (racine + 'caisse_annexe/getStudentAnnexesSummary');

    $.ajax({
      url: summaryUrl,
      type: 'GET',
      data: { inscription_code: val },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          var inits = (d.nom_complet || 'ET').split(' ').filter(function(x){return x;}).map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();

          $('#encaisse-stu-avatar').text(inits || 'ET');
          $('#encaisse-stu-nom').text(d.nom_complet);
          $('#encaisse-stu-mat').text(d.matricule);
          $('#encaisse-stu-classe').text(d.classe + (d.filiere ? ' - ' + d.filiere : ''));
          $('#encaisse-stu-filiere-badge').text('Filière ' + d.type_filiere);

          if (d.photo_url && d.photo_url.trim() !== '') {
            $('#encaisse-stu-photo').attr('src', d.photo_url).show();
            $('#encaisse-stu-avatar').hide();
          } else {
            $('#encaisse-stu-photo').hide();
            $('#encaisse-stu-avatar').show();
          }

          $('#encaisse-lbl-exigible').text(d.libelle_frais_annexe || 'Tarif Frais Annexes');
          $('#encaisse-val-exigible').text(d.montant_exigible_fmt);
          $('#encaisse-val-paye').text(d.deja_paye_fmt);
          $('#encaisse-val-reste').text(d.reste_a_payer_fmt);

          $('#encaisse-student-summary-card').stop(true, true).slideDown(200);

          var reste = parseFloat(d.reste_a_payer || 0);
          $('#input-montant-versement').val(reste > 0 ? reste : 0);

          if (d.is_solde) {
            $('#montant-max-hint').html('<span style="color:#15803D; font-weight:800;">🎉 Cet étudiant a déjà intégralement soldé ses frais annexes.</span>');
            $('#btn-submit-encaissement').prop('disabled', true);
          } else {
            $('#montant-max-hint').html('Montant attendu pour solder les annexes : <strong style="color:#B45309;">' + d.reste_a_payer_fmt + '</strong>');
            $('#btn-submit-encaissement').prop('disabled', false);
          }

          $('#encaisse-payment-inputs-section').stop(true, true).slideDown(200);
          if (window.lucide) lucide.createIcons();
        }
      }
    });
  });

  function showEncaissementAnnexeError(message) {
    message = message || 'Une erreur est survenue lors de l\'enregistrement.';
    var isCaisseError = (message.indexOf("session de caisse") !== -1 || message.indexOf("Session de caisse") !== -1 || message.indexOf("OUVERTE") !== -1 || message.indexOf("ouverte") !== -1);
    
    if (window.Swal) {
      if (isCaisseError) {
        Swal.fire({
          icon: 'warning',
          title: 'Session de Caisse Requise',
          html: '<div style="font-size: 13.5px; color: #334155; line-height: 1.5; text-align: left; padding: 4px;">' +
                  '<div style="background: #FEF2F2; border-left: 4px solid #DC2626; padding: 12px 14px; border-radius: 6px; margin-bottom: 12px; color: #991B1B; font-weight: 600;">' +
                    '<i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> ' + escapeHtml(message) +
                  '</div>' +
                  '<p style="margin: 0 0 8px 0; color: #475569; font-size: 13px;">Pour enregistrer un règlement en espèces au guichet, vous devez obligatoirement ouvrir la session de caisse du jour.</p>' +
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
          title: 'Opération non validée',
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

  // Validation Formulaire Encaissement Annexe
  $('#form-encaissement-annexe').on('submit', function(e) {
    e.preventDefault();
    var montant = parseFloat($('#input-montant-versement').val() || 0);
    if (montant <= 0) {
      showEncaissementAnnexeError('Le montant du versement doit être supérieur à 0 FCFA.');
      return;
    }

    var $btn = $('#btn-submit-encaissement');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:6px;"></span> Validation...');

    var encaisseUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('caisse_annexe/encaisser') : (racine + 'caisse_annexe/encaisser');

    $.ajax({
      url: encaisseUrl,
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider le Règlement');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1) {
          closeEncaissementModal();
          table.ajax.reload(null, false);
          refreshKpis();

          var codePaiement = res.code_paiement || 'REC-FA-CONFIRME';
          var idCrypte = res.encrypted_id || res.id_paiement;
          var printPdfUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('paiement/imprimerPdf/' + idCrypte) : (racine + 'paiement/imprimerPdf/' + idCrypte);
          $('#success-recu-num').text(codePaiement);
          $('#success-recu-print-link').attr('href', printPdfUrl);
          $('#modal-recu-annexe-success').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          showEncaissementAnnexeError(res.message || 'Erreur lors de l\'enregistrement.');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider le Règlement');
        if (window.lucide) lucide.createIcons();
        var errMsg = 'Une erreur est survenue lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errMsg = xhr.responseJSON.message;
        }
        showEncaissementAnnexeError(errMsg);
      }
    });
  });

  // Gestion session de caisse
  $(document).on('click', '.btn-open-session-caisse', function(e) {
    e.preventDefault();
    $('#modal-open-session-caisse').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $(document).on('click', '.btn-close-modal-session', function() {
    $('#modal-open-session-caisse').hide();
  });

  $('#form-open-session-caisse').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-session');
    $btn.prop('disabled', true).text('Ouverture...');

    var addSessionUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('session_caisse/add') : (racine + 'session_caisse/add');

    $.ajax({
      url: addSessionUrl,
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).text('Confirmer l\'Ouverture');
        if (res.status === 1) {
          window.location.reload();
        } else {
          alert(res.message || 'Erreur lors de l\'ouverture de la session.');
        }
      }
    });
  });


  }

  // =========================================================================
  // 2. VUE : CATALOGUE & PARAMÉTRAGE DES FRAIS ANNEXES (views/frais_annexes/list.php)
  // =========================================================================
  function initFraisAnnexesModule() {
    if (!$('#table-frais-annexes').length) return;

  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  var listUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('fraisAnnexe/apiList') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'fraisAnnexe/apiList');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'json/datatables-i18n-fr-FR.json');

  var tableFrais = $('#table-frais-annexes').DataTable({
    ajax: {
      url: listUrl,
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.categorie = $('#filter-categorie').val();
        d.type_filiere = $('#filter-type-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_frais_annexe', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'libelle_frais_annexe', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'categorie_frais_annexe', render: function(d) {
        if (!d) return '-';
        var label = (d === 'inscription') ? 'Inscription' : 'Autre';
        return '<span style="font-weight:600; color:#334155;">' + label + '</span>';
      }},
      { data: 'type_filiere', render: function(d) {
        if (d === 'INDUSTRIELLE') return '<span class="badge" style="background:#E0F2FE; color:#0369A1; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Industrielle</span>';
        if (d === 'TERTIAIRE') return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Tertiaire</span>';
        return '<span class="badge" style="background:#F1F5F9; color:#475569; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px;">Toutes Filières</span>';
      }},
      { data: 'libelle_niveau', render: function(d) {
        if (!d) return '<span style="color:#94A3B8;">Tous les Niveaux</span>';
        return '<span style="font-weight:600; color:#334155;">' + d + '</span>';
      }},
      { data: 'montant_frais_annexe', render: function(d) {
        var val = parseFloat(d || 0);
        return '<span style="font-weight:800; color:#15803D; font-size:14px;">' + val.toLocaleString('fr-FR') + ' FCFA</span>';
      }},
      { data: 'statut_frais_annexe', width: '80px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-frais" data-id="' + row.id_frais_annexe + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '120px', orderable: false, render: function(d) {
        var safeLib = (d.libelle_frais_annexe || '').replace(/"/g, '&quot;');
        var fid = d.id_frais_annexe || d.id || '';
        return '<button type="button" class="btn btn-sm btn-secondary btn-edit-frais" ' +
               'data-id="' + fid + '" ' +
               'data-libelle="' + safeLib + '" ' +
               'data-categorie="' + (d.categorie_frais_annexe || 'autre') + '" ' +
               'data-type-filiere="' + (d.type_filiere || 'TOUT') + '" ' +
               'data-niveau-code="' + (d.niveau_code || 'TOUT') + '" ' +
               'data-montant="' + (d.montant_frais_annexe || '0') + '" ' +
               'data-statut="' + (d.statut_frais_annexe || 'actif') + '" ' +
               'style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px; cursor:pointer;">' +
               '<i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</button>';
      }, className: 'text-end' }
    ],
    language: (typeof dtLang === 'object') ? dtLang : { url: dtLang },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  function updatePrintUrl() {
    var annee = $('#filter-annee').val() || '';
    var categorie = $('#filter-categorie').val() || '';
    var typeFiliere = $('#filter-type-filiere').val() || '';
    var niveau = $('#filter-niveau').val() || '';

    var params = new URLSearchParams();
    if (annee) params.set('annee_code', annee);
    if (categorie) params.set('categorie', categorie);
    if (typeFiliere) params.set('type_filiere', typeFiliere);
    if (niveau) params.set('niveau_code', niveau);

    var qs = params.toString();
    var printBase = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('fraisAnnexe/imprimerPdf') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'fraisAnnexe/imprimerPdf');
    var href = printBase + (qs ? '?' + qs : '');
    $('#btn-print-frais').attr('href', href);
  }

  $('#filter-annee, #filter-categorie, #filter-type-filiere, #filter-niveau').on('change', function() {
    tableFrais.ajax.reload();
    updatePrintUrl();
  });

  updatePrintUrl();

  // Modal : Ajouter
  $('.btn-add-frais').on('click', function() {
    $('#form-frais-annexe')[0].reset();
    $('#frais_annexe_id').val('');
    $('#frais_categorie').val('inscription');
    $('#frais_type_filiere').val('INDUSTRIELLE');
    $('#frais_niveau_code').val('TOUT');
    $('#modal-frais-annexe-title').html('<i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Tarification de Frais Annexes');
    $('#modal-frais-annexe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Modal : Éditer
  $(document).on('click', '.btn-edit-frais', function() {
    var $btn = $(this);
    var id = $btn.attr('data-id') || $btn.data('id');
    var libelle = $btn.attr('data-libelle') || $btn.data('libelle');
    var categorie = $btn.attr('data-categorie') || $btn.data('categorie') || 'autre';
    var typeFiliere = $btn.attr('data-type-filiere') || $btn.data('typeFiliere') || $btn.data('type-filiere');
    var niveauCode = $btn.attr('data-niveau-code') || $btn.data('niveauCode') || $btn.data('niveau-code');
    var montant = $btn.attr('data-montant') || $btn.data('montant');
    var statut = $btn.attr('data-statut') || $btn.data('statut');

    var decLib = $('<div>').html(libelle || '').text();

    $('#frais_annexe_id').val(id);
    $('#frais_libelle').val(decLib || libelle);
    $('#frais_categorie').val(categorie);
    $('#frais_type_filiere').val(typeFiliere || 'TOUT');
    $('#frais_niveau_code').val(niveauCode || 'TOUT');
    $('#frais_montant').val(montant);
    $('#frais_statut').val(statut || 'actif');
    $('#modal-frais-annexe-title').html('<i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le Tarif de Frais Annexes');
    $('#modal-frais-annexe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  // Fermer modal via bouton (croix ou Annuler)
  $(document).on('click', '.btn-close-modal-frais', function() {
    $('#modal-frais-annexe').css('display', 'none');
  });

  // Fermer modal en cliquant sur l'arrière-plan (backdrop)
  $(document).on('click', '#modal-frais-annexe', function(e) {
    if ($(e.target).is('#modal-frais-annexe')) {
      $(this).css('display', 'none');
    }
  });

  function notify(msg, type) {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else if (window.toastr && typeof toastr[type] === 'function') {
      toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  // Soumission Formulaire
  $('#form-frais-annexe').on('submit', function(e) {
    e.preventDefault();
    var isEdit = $('#frais_annexe_id').val() !== '';
    var targetUrl = (typeof GEICG !== 'undefined' && GEICG.url) ?
      GEICG.url(isEdit ? 'fraisAnnexe/edit' : 'fraisAnnexe/store') :
      ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + (isEdit ? 'fraisAnnexe/edit' : 'fraisAnnexe/store'));
    var $btn = $('#btn-save-frais');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: targetUrl,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          notify(res.message || 'Opération réussie avec succès !', 'success');
          $('#modal-frais-annexe').css('display', 'none');
          tableFrais.ajax.reload(null, false);
        } else {
          notify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr, status, err) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer');
        if (window.lucide) lucide.createIcons();

        var msg = (xhr.responseJSON && xhr.responseJSON.message) || err || 'Erreur lors de l\'enregistrement';
        notify(msg, 'error');
      }
    });
  });

  // Toggle Statut
  $(document).on('change', '.toggle-statut-frais', function() {
    var id = $(this).data('id');
    var csrfToken = $('input[name="csrf_token"]').val() || '';
    var toggleUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('fraisAnnexe/toggleStatut') : ((typeof RACINE !== 'undefined' ? RACINE : '/geicg/') + 'fraisAnnexe/toggleStatut');

    $.ajax({
      url: toggleUrl,
      type: 'POST',
      data: { id_frais_annexe: id, csrf_token: csrfToken },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          notify(res.message, 'success');
        } else {
          notify(res.message, 'error');
        }
        tableFrais.ajax.reload(null, false);
      },
      error: function() {
        notify('Erreur serveur lors du changement de statut', 'error');
        tableFrais.ajax.reload(null, false);
      }
    });
  });

  }

  $(document).ready(function() {
    initCaisseAnnexeModule();
    initFraisAnnexesModule();
    if (window.lucide) lucide.createIcons();
  });

})(window, document, jQuery);