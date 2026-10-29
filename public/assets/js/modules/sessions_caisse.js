/**
 * ==========================================================================
 * GEICG - MODULE SESSIONS DE CAISSE (sessions_caisse.js)
 * Externalisation JS stricte - Zéro JS inline dans les vues
 * ==========================================================================
 */

(function(window, $) {
  'use strict';

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

})(window, window.jQuery);
