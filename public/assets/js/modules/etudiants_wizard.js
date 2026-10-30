$(document).ready(function() {
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
});

