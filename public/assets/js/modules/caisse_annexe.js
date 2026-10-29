/**
 * Module JS: Caisse Annexe (Registre des règlements, encaissement direct et reçus)
 * Externalisation stricte du code JS pour views/caisse_annexe/list.php
 */
$(document).ready(function() {

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

});
