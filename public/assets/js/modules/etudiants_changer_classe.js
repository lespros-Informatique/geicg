$(document).ready(function() {
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
});
