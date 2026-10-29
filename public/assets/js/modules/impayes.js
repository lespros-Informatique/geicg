/**
 * ==========================================================================
 * GEICG - MODULE IMPAYÉS & RELANCES DE SCOLARITÉ (impayes.js)
 * Externalisation JS stricte - Zéro JS inline dans les vues
 * ==========================================================================
 */

(function(window, $) {
  'use strict';

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

})(window, window.jQuery);
