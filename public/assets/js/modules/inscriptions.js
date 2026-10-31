/**
 * Module JS Unifié : Inscriptions & Gestion des Photos
 * GEICG - Application Web
 */
(function(window, $) {
  'use strict';
  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');

  // 1. VUE : LISTE DES INSCRIPTIONS / RÉINSCRIPTIONS (views/inscriptions/list.php)
  function initInscriptionsList() {
    if (!$('#table-inscriptions').length) return;

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  if ($.fn.select2) {
    $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe').select2({
      width: '100%'
    });
  }

  var table = $('#table-inscriptions').DataTable({
    order: [],
    ajax: {
      url: racine + 'inscription/apiList',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'matricule_etudiant', render: function(d) {
        return '<code style="font-weight:800; color:#1E3A5F; background:#EFF6FF; padding:3px 7px; border-radius:6px; font-size:12px;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_complet', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; font-size:13.5px;">' + (d || '-') + '</span>';
      }},
      { data: 'telephone', render: function(d) {
        if (!d || d === '-') return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Non renseigné</span>';
        return '<span style="color:#334155; font-weight:700; font-size:12.5px;"><i data-lucide="phone" style="width:12px;height:12px;display:inline-block;vertical-align:-1px;margin-right:4px;color:#2563EB;"></i>' + d + '</span>';
      }},
      { data: 'filiere_precedente', render: function(d, type, row) {
        var fil = d || 'Non définie';
        return '<div>' +
               '  <div style="font-weight:700; color:#1E3A5F; font-size:13px;">' + fil + '</div>' +
               '  <div style="font-size:11.5px; color:#64748B;">' + (row.niveau_precedent || 'Niveau non défini') + '</div>' +
               '</div>';
      } },
      { data: 'classe_precedente', render: function(d, type, row) {
        if (!d) {
          return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Nouveau / Sans historique</span>';
        }
        var annee = row.annee_precedente ? ' <span style="font-size:11px; color:#64748B;">(' + row.annee_precedente + ')</span>' : '';
        return '<div style="font-weight:700; color:#334155; font-size:13px;">' + d + annee + '</div>';
      } },
      { data: null, orderable: false, width: '220px', className: 'text-end', render: function(d, type, row) {
        var idInscription = row.editId || row.id_inscription;
        var printUrl = idInscription ? (racine + 'inscription/details/' + idInscription + '?print=1') : (racine + 'etudiant/details/' + encodeURIComponent(row.code_etudiant || row.id_etudiant) + '?print=1');
        return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">' +
               '  <a href="' + printUrl + '" target="_blank" class="btn btn-sm btn-outline-primary" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;" title="Imprimer la fiche"><i data-lucide="printer" style="width:13px;height:13px;"></i> Imprimer</a>' +
               '  <a href="' + racine + 'inscription/formulaire?etudiant_code=' + encodeURIComponent(row.code_etudiant) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; font-weight:700; border-radius:6px; padding:5px 10px; display:inline-flex; align-items:center; gap:4px; box-shadow:0 1px 3px rgba(30,58,95,0.2);">' +
               '    <i data-lucide="user-check" style="width:13px;height:13px;"></i> Réinscrire' +
               '  </a>' +
               '</div>';
      } }
    ],

    language: dtLang,
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
  });

  function toggleFieldsByAnnee() {
    var val = $('#filter-annee').val();
    var hasAnnee = !!(val && val !== '');
    var $targets = $('#filter-filiere, #filter-niveau, #filter-classe');

    $targets.prop('disabled', !hasAnnee);
    if (!hasAnnee) {
      $targets.attr('readonly', 'readonly');
      $('#btn-reset-filters').prop('disabled', true).addClass('disabled').css('opacity', '0.5');
    } else {
      $targets.removeAttr('readonly');
      $('#btn-reset-filters').prop('disabled', false).removeClass('disabled').css('opacity', '1');
    }

    if ($.fn.select2) {
      $targets.trigger('change.select2');
      $targets.each(function() {
        var $container = $(this).next('.select2-container');
        if (!hasAnnee) {
          $container.find('.select2-selection').css({
            'background-color': '#F8FAFC',
            'cursor': 'not-allowed',
            'opacity': '0.7'
          });
        } else {
          $container.find('.select2-selection').css({
            'background-color': '#FFFFFF',
            'cursor': 'pointer',
            'opacity': '1'
          });
        }
      });
    }
    return hasAnnee;
  }

  toggleFieldsByAnnee();

  $('#filter-annee').on('change', function() {
    var hasAnnee = toggleFieldsByAnnee();
    if (!hasAnnee) {
      $('#filter-filiere').val('ALL').trigger('change.select2');
      $('#filter-niveau').val('ALL').trigger('change.select2');
      $('#filter-classe').val('ALL').trigger('change.select2');
    }
  });

  $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe').on('change', function() {
    var filSelected = $('#filter-filiere').val();
    var nivSelected = $('#filter-niveau').val();

    $('#filter-classe option').each(function() {
      var val = $(this).val();
      if (val === 'ALL') return;
      var cFil = $(this).attr('data-filiere');
      var cNiv = $(this).attr('data-niveau');

      var matchFil = (filSelected === 'ALL' || cFil === filSelected);
      var matchNiv = (nivSelected === 'ALL' || cNiv === nivSelected);

      if (matchFil && matchNiv) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });

    table.ajax.reload();
  });

  $('#btn-reset-filters').on('click', function() {
    if ($('#btn-reset-filters').is(':disabled')) return;
    $('#filter-filiere').val('ALL').trigger('change.select2');
    $('#filter-niveau').val('ALL').trigger('change.select2');
    $('#filter-classe').val('ALL').trigger('change.select2');
    $('#filter-classe option').show();
    table.ajax.reload();
  });

  }

  // 2. VUE : FORMULAIRE D'INSCRIPTION / RÉINSCRIPTION (views/inscriptions/edit.php)
  function initInscriptionsEdit() {
    if (!$('#form_inscription_main').length) return;
 
  var cfg = {};
  try {
    var cfgEl = document.getElementById('inscriptions-edit-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}
  var isEditMode = !!cfg.isEditMode;

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_etudiant_inscription').select2({
      placeholder: "-- Rechercher par nom, matricule ou téléphone --",
      allowClear: !$('#sel_etudiant_inscription').hasClass('readonly-select'),
      width: '100%'
    });

    if ($('#sel_etudiant_inscription').hasClass('readonly-select')) {
      $('#sel_etudiant_inscription').next('.select2-container').css({
        'pointer-events': 'none',
        'cursor': 'not-allowed'
      }).find('.select2-selection').css({
        'background-color': '#F1F5F9',
        'border-color': '#CBD5E1',
        'color': '#334155',
        'cursor': 'not-allowed'
      });
      $('#sel_etudiant_inscription').on('select2:opening', function(e) {
        e.preventDefault();
      });
    }

    $('#sel_classe_inscription').select2({
      placeholder: "-- Choisir la classe --",
      allowClear: true,
      width: '100%'
    });
  }

  var currentStudentData = null;

  // 1. Récupération et affichage dynamique de la fiche synthèse complète de l'étudiant
  function fetchStudentProfile(etudiantCode) {
    if (!etudiantCode) {
      currentStudentData = null;
      $('#student-profile-preview-banner').slideUp(200);
      return;
    }

    $.ajax({
      url: window.RACINE + 'inscription/getStudentProfileSummary',
      type: 'GET',
      data: { 
        etudiant_code: etudiantCode,
        annee_code: $('#sel_annee_inscription').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          currentStudentData = d;

          var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          if (d.photo_url) {
            $('#prev_stu_photo_img').off('error').on('error', function() {
              $(this).hide();
              $('#prev_stu_avatar').text(initials || 'ET').show();
            }).attr('src', d.photo_url).show();
            $('#prev_stu_avatar').hide();
          } else {
            $('#prev_stu_photo_img').hide();
            $('#prev_stu_avatar').text(initials || 'ET').show();
          }

          // Étape 1 : Identité & Coordonnées
          $('#prev_stu_nom').text(d.nom_complet);
          $('#prev_stu_nom_famille').text(d.nom_famille || '-');
          $('#prev_stu_prenoms').text(d.prenom || '-');
          $('#prev_stu_matricule').text(d.matricule);
          $('#prev_stu_badge_sexe').text(d.sexe === 'M' ? 'Masculin (M)' : (d.sexe === 'F' ? 'Féminin (F)' : d.sexe));
          $('#prev_stu_naissance').text(d.date_naissance);
          $('#prev_stu_lieu_naissance').text(d.lieu_naissance || 'Non renseigné');
          $('#prev_stu_nationalite').text(d.nationalite || 'Ivoirienne');
          $('#prev_stu_residence').text(d.residence || 'Non renseigné');
          $('#prev_stu_contact').text(d.telephone);
          $('#prev_stu_email').text(d.email);

          // Étape 2 : Parents & Tuteurs
          $('#prev_stu_parent').text(d.parent_nom + ' (' + d.parent_role + ')');
          $('#prev_stu_parent_tel').text(d.parent_tel);
          $('#prev_stu_parent_prof').text(d.parent_profession || 'Non renseignée');
          if (d.nom_mere) {
            $('#prev_stu_mere').text(d.nom_mere);
            $('#prev_stu_mere_tel').text(d.telephone_mere || '-');
            $('#prev_box_mere').show();
          } else {
            $('#prev_box_mere').hide();
          }

          // Étape 3 : Cursus & Situation Antérieure (N-1)
          if (d.has_history) {
            $('#prev_stu_filiere').text(d.derniere_filiere || 'Filière antérieure').show();
            $('#prev_stu_niveau').text(d.dernier_niveau || 'Niveau antérieur').show();
            $('#prev_stu_classe').text(d.derniere_classe || 'Classe antérieure').show();
            $('#prev_stu_annee_detail').text((d.derniere_annee ? 'Session ' + d.derniere_annee + ' • ' : '') + 'Régime : ' + d.prev_regime);
            
            var solde = Number(d.prev_solde || 0);
            if (solde <= 0) {
              $('#prev_stu_solde').css('color', '#15803D').text('Compte Soldé (0 FCFA)');
              // Quitus Financier Antérieur Validé
              $('#quitus-financial-status-box').css({
                'background': '#F0FDF4',
                'border-color': '#86EFAC'
              }).show();
              $('#quitus_icon_box').css('background', '#DCFCE7');
              $('#quitus_icon').attr('data-lucide', 'shield-check').css('color', '#15803D');
              $('#quitus_title').css('color', '#166534').text('✅ Quitus Financier Antérieur Validé');
              $('#quitus_desc').css('color', '#166534').text('L\'étudiant est entièrement à jour de ses règlements sur les sessions précédentes. Aucune dette antérieure enregistrée.');
              $('#quitus_badge_box').html('<span class="badge" style="background:#15803D; color:#FFFFFF; padding:6px 12px; border-radius:6px; font-weight:800; font-size:12px;">Quitus Accordé</span>');
              $('#quitus_derogation_zone').hide();
              $('#submit_block_notice').hide();
              $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
            } else {
              $('#prev_stu_solde').css('color', '#DC2626').text(solde.toLocaleString('fr-FR') + ' FCFA (Reliquat)');
              // Arriérés détectés : Blocage par défaut avec dérogation
              $('#quitus-financial-status-box').css({
                'background': '#FEF2F2',
                'border-color': '#FECACA'
              }).show();
              $('#quitus_icon_box').css('background', '#FEE2E2');
              $('#quitus_icon').attr('data-lucide', 'alert-octagon').css('color', '#DC2626');
              $('#quitus_title').css('color', '#991B1B').text('⚠️ Alerte Financière : Arriérés Antérieurs Détectés (' + solde.toLocaleString('fr-FR') + ' FCFA)');
              $('#quitus_desc').css('color', '#991B1B').html('L\'étudiant présente un reliquat impayé de <strong>' + solde.toLocaleString('fr-FR') + ' FCFA</strong> sur la session précédente (' + (d.derniere_annee || 'antérieure') + '). La réinscription normale requiert la régularisation préalable au <strong>Bureau des Versements</strong>.');
              $('#quitus_badge_box').html('<span class="badge" style="background:#DC2626; color:#FFFFFF; padding:6px 12px; border-radius:6px; font-weight:800; font-size:12px;">Solde Débiteur</span>');
              $('#quitus_derogation_zone').show();

              // Si dérogation non cochée, bloquer le bouton de validation
              updateSubmitButtonQuitusState();
            }

            // Présélection automatique du régime (Affecté / Non Affecté) selon l'historique
            if (d.prev_affectation_etat === 'affecte') {
              $('input[name="affectation_etat"][value="affecte"]').prop('checked', true);
            } else {
              $('input[name="affectation_etat"][value="non_affecte"]').prop('checked', true);
            }
            $('input[name="affectation_etat"]').each(function() {
              var isChecked = $(this).is(':checked');
              $(this).closest('label').css({
                'border-color': isChecked ? '#1E3A5F' : '#CBD5E1',
                'background': isChecked ? '#EFF6FF' : '#FFFFFF',
                'color': isChecked ? '#1E3A5F' : '#334155'
              });
            });

            // Gestion automatique de l'état Redoublant / Passant avec suggestion intelligente
            handleRedoublantState();
          } else {
            $('#prev_stu_badge_redoublant').hide();
            $('#prev_stu_filiere').text('Non définie');
            $('#prev_stu_niveau').text('Non défini');
            $('#prev_stu_classe').text('Nouvel Inscrit');
            $('#prev_stu_annee_detail').text('Première inscription dans l\'établissement');
            $('#prev_stu_solde').css('color', '#15803D').text('Aucun arriéré (0 FCFA)');
            $('#quitus-financial-status-box').hide();
            $('#smart_class_suggestion_hint').hide();
            $('#submit_block_notice').hide();
            $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
          }

          // Étape 4 : Accessoires & Kits d'Inscription
          if (d.accessoires_etudiant && d.accessoires_etudiant.length > 0) {
            var accHtml = '';
            d.accessoires_etudiant.forEach(function(acc) {
              accHtml += '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; padding:4px 8px; border-radius:6px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#15803D;"></i> ' + acc.libelle_accessoire + '</span> ';
            });
            $('#prev_acc_list').html(accHtml);
          } else {
            $('#prev_acc_list').html('<span class="badge" style="background:#F1F5F9; color:#64748B; padding:4px 8px; border-radius:6px; font-weight:600;">Aucun kit/accessoire souscrit</span>');
          }

          // Vérification si déjà inscrit pour cette session en cours (en mode ajout)
          if (d.is_already_registered_this_year && !isEditMode) {
            $('#already_registered_warning_text').html(
              'L\'étudiant <strong>' + (d.nom_complet || '') + '</strong> est déjà réinscrit pour la session active (<strong>' + (d.already_registered_annee || 'active') + '</strong>) dans la classe <strong>' + (d.already_registered_classe || '-') + '</strong> (Réf Inscription : <code>' + (d.already_registered_code || '-') + '</code>).<br>Une double réinscription pour cette même année n\'est pas autorisée.'
            );
            $('#already_registered_warning').stop(true, true).slideDown(250);
            $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
          } else {
            $('#already_registered_warning').slideUp(200);
            updateSubmitButtonQuitusState();
          }

          $('#student-profile-preview-banner').stop(true, true).slideDown(250);

          // Si une classe est déjà choisie, charger immédiatement ses tarifs
          var selClass = $('#sel_classe_inscription').val();
          if (selClass) {
            fetchTuitionForClass(selClass);
          }

          if (window.lucide) lucide.createIcons();
        } else {
          $('#already_registered_warning').slideUp(200);
          $('#student-profile-preview-banner').slideUp(200);
          $('#quitus-financial-status-box').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement profil étudiant:', err);
        $('#student-profile-preview-banner').slideUp(200);
      }
    });
  }

  var isTuitionValid = true;

  // Contrôle de l'état du bouton selon le Quitus, la scolarité et la dérogation
  function updateSubmitButtonQuitusState() {
    if (!isTuitionValid) {
      $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
      $('#submit_block_notice').html('<i data-lucide="alert-octagon" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> Aucun tarif de scolarité configuré pour cette classe').show();
      if (window.lucide) lucide.createIcons();
      return;
    }
    if (!currentStudentData) return;
    if (currentStudentData.is_already_registered_this_year && !isEditMode) {
      $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
      return;
    }

    var solde = Number(currentStudentData.prev_solde || 0);
    if (solde > 0) {
      var isDerogationChecked = $('#chk_derogation_arriere').is(':checked');
      if (isDerogationChecked) {
        $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
        $('#submit_block_notice').hide();
      } else {
        $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
        $('#submit_block_notice').html('<i data-lucide="alert-circle" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> Régularisation caisse requise ou cochez la dérogation').show();
        if (window.lucide) lucide.createIcons();
      }
    } else {
      $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
      $('#submit_block_notice').hide();
    }
  }

  $('#chk_derogation_arriere').on('change', function() {
    if ($(this).is(':checked')) {
      $('#derogation_motif_container').slideDown(200);
    } else {
      $('#derogation_motif_container').slideUp(200);
    }
    updateSubmitButtonQuitusState();
  });

  // 2. Gestion de l'option Redoublant / Passant (Smart Class Progression)
  function handleRedoublantState() {
    var isRedoublant = $('input[name="is_redoublant"]:checked').val() === '1';

    if (isRedoublant) {
      $('#prev_stu_badge_redoublant').show();
      $('#label_classe_select').text("Classe d'affectation (Classe Redoublée)");

      // Si l'étudiant a une classe précédente enregistrée, la pré-sélectionner
      if (currentStudentData && currentStudentData.derniere_classe_code) {
        $('#sel_classe_inscription').val(currentStudentData.derniere_classe_code).trigger('change');
        $('#smart_class_suggestion_text').html('Classe redoublée sélectionnée : <strong>' + (currentStudentData.derniere_classe || '-') + '</strong>');
        $('#smart_class_suggestion_hint').show();
      }
    } else {
      $('#prev_stu_badge_redoublant').hide();
      $('#label_classe_select').text("Classe d'affectation (Nouvelle Classe)");

      // Si le système a détecté une classe N+1 suggérée, la pré-sélectionner en auto
      if (currentStudentData && currentStudentData.suggested_next_class_code) {
        $('#sel_classe_inscription').val(currentStudentData.suggested_next_class_code).trigger('change');
        $('#smart_class_suggestion_text').html('Classe suggérée : <strong>' + (currentStudentData.suggested_next_class_libelle || '-') + '</strong> (Niveau supérieur)');
        $('#smart_class_suggestion_hint').show();
      } else if (currentStudentData && currentStudentData.has_history) {
        $('#smart_class_suggestion_text').html('Veuillez sélectionner la classe supérieure de la filière <strong>' + (currentStudentData.derniere_filiere || '-') + '</strong>.');
        $('#smart_class_suggestion_hint').show();
      } else {
        $('#smart_class_suggestion_hint').hide();
      }
    }
    if (window.lucide) lucide.createIcons();
  }

  $('input[name="is_redoublant"]').on('change', function() {
    handleRedoublantState();
  });

  // 3. Auto-suggestion et affichage des détails tarifaires de la classe sélectionnée
  function fetchTuitionForClass(classeCode) {
    if (!classeCode) {
      isTuitionValid = true;
      $('#no_tuition_warning').slideUp(200);
      $('#prev_class_tuition_section').slideUp(200);
      updateSubmitButtonQuitusState();
      return;
    }
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

    $.ajax({
      url: window.RACINE + 'inscription/getTuitionByClass',
      type: 'GET',
      data: { 
        classe_code: classeCode,
        affectation_etat: affectationEtat,
        annee_code: $('#sel_annee_inscription').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          isTuitionValid = true;
          $('#no_tuition_warning').slideUp(200);
          var d = res.data;
          var totalScolarite = Number(d.montant_scolarite || 0);

          // Remplissage automatique du champ montant de scolarité
          $('#inp_montant_scolarite').val(totalScolarite);

          var regimeText = d.affectation_etat === 'affecte' ? ' (Affecté État)' : ' (Non Affecté / Privé)';
          var filiereNiveauText = (d.libelle_filiere ? 'Filière : ' + d.libelle_filiere + ' • ' : '') + (d.libelle_niveau ? 'Niveau : ' + d.libelle_niveau : '');

          // Mise à jour de la section Modalités & Tarifs de la Classe dans le Preview Banner
          $('#prev_modalite_classe_title').text(d.libelle_classe + regimeText);
          $('#prev_modalite_filiere_niveau').text(filiereNiveauText);
          $('#prev_modalite_total_scolarite').text(totalScolarite.toLocaleString('fr-FR') + ' FCFA');

          // Rendu dynamique de TOUTES les tranches
          var tranches = d.tranches || [];
          var tbodyHtml = '';
          var sumTranches = 0;

          if (tranches.length > 0) {
            $('#prev_tranches_count_badge').text(tranches.length + ' tranche(s) configurée(s)').show();

            tranches.forEach(function(tr, idx) {
              var mt = Number(tr.montant_tranche || tr.montant_tranche_num || 0);
              sumTranches += mt;
              var isFirst = (idx === 0);
              var pct = totalScolarite > 0 ? Math.round((mt / totalScolarite) * 100) : 0;
              var dateLimite = tr.date_limite_formatee || (tr.date_limite ? tr.date_limite : 'Non définie');

              tbodyHtml += '<tr style="border-bottom: 1px solid #E2E8F0; background: ' + (isFirst ? '#F8FAFC' : '#FFFFFF') + ';">';
              tbodyHtml += '  <td style="padding: 10px 14px; font-weight: 800; color: #64748B;">' + (idx + 1) + '</td>';
              tbodyHtml += '  <td style="padding: 10px 14px;">';
              tbodyHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13px;">' + (tr.libelle_tranche || ('Tranche ' + (idx + 1))) + '</div>';
              if (isFirst) {
                tbodyHtml += '    <span style="background:#EFF6FF; color:#1D4ED8; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; border:1px solid #BFDBFE;">Exigible à l\'inscription</span>';
              }
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 10px 14px; text-align: center;">';
              tbodyHtml += '    <span style="background:#F1F5F9; color:#334155; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px;">' + pct + '%</span>';
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 10px 14px; text-align: center; color: #475569; font-size: 12px; font-weight: 600;">';
              tbodyHtml += '    <i data-lucide="calendar" style="width:12px; height:12px; display:inline-block; vertical-align:middle; margin-right:4px; color:#64748B;"></i>' + dateLimite;
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 10px 14px; text-align: right; font-weight: 800; color: #166534; font-size: 13.5px;">';
              tbodyHtml += '    ' + mt.toLocaleString('fr-FR') + ' FCFA';
              tbodyHtml += '  </td>';
              tbodyHtml += '</tr>';
            });
          } else {
            $('#prev_tranches_count_badge').text('Paiement Unique');
            sumTranches = totalScolarite;
            tbodyHtml += '<tr>';
            tbodyHtml += '  <td colspan="5" style="padding: 14px; text-align: center; color: #64748B; font-style: italic;">';
            tbodyHtml += '    Aucune tranche intermédiaire configurée. Règlement unique de la scolarité totale : <strong>' + totalScolarite.toLocaleString('fr-FR') + ' FCFA</strong>';
            tbodyHtml += '  </td>';
            tbodyHtml += '</tr>';
          }

          $('#prev_modalite_tranches_table_body').html(tbodyHtml);
          $('#prev_modalite_tranches_total_sum').text(sumTranches.toLocaleString('fr-FR') + ' FCFA');

          $('#prev_class_tuition_section').stop(true, true).slideDown(250);
          updateSubmitButtonQuitusState();
          if (window.lucide) lucide.createIcons();
        } else {
          isTuitionValid = false;
          $('#inp_montant_scolarite').val('0');
          var errMsg = res.message || "Aucun tarif de scolarité actif n'est configuré pour cette classe sous le régime sélectionné.";
          $('#no_tuition_warning_text').html(errMsg);
          $('#no_tuition_warning').stop(true, true).slideDown(250);
          $('#prev_class_tuition_section').slideUp(200);
          updateSubmitButtonQuitusState();
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        isTuitionValid = false;
        $('#inp_montant_scolarite').val('0');
        $('#no_tuition_warning_text').html("Erreur lors de la vérification du tarif de scolarité pour cette classe.");
        $('#no_tuition_warning').stop(true, true).slideDown(250);
        $('#prev_class_tuition_section').slideUp(200);
        updateSubmitButtonQuitusState();
        if (window.lucide) lucide.createIcons();
      }
    });
  }

  function filterInscriptionClassesByAnnee() {
    var selectedAnnee = $('#sel_annee_inscription').val();
    var $classeSelect = $('#sel_classe_inscription');
    var currentVal = $classeSelect.val();
    var currentStillValid = false;

    $classeSelect.find('option').each(function() {
      var optAnnee = $(this).data('annee');
      if (!$(this).val()) return;

      if (!selectedAnnee || !optAnnee || optAnnee === selectedAnnee) {
        $(this).prop('disabled', false).show();
        if ($(this).val() === currentVal) {
          currentStillValid = true;
        }
      } else {
        $(this).prop('disabled', true).hide();
      }
    });

    if (!currentStillValid && currentVal) {
      $classeSelect.val('').trigger('change');
    } else {
      $classeSelect.trigger('change.select2');
    }
  }

  $('#sel_annee_inscription').on('change select2:select', function() {
    filterInscriptionClassesByAnnee();
    var stu = $('#sel_etudiant_inscription').val();
    if (stu) {
      fetchStudentProfile(stu);
    }
    var currentClass = $('#sel_classe_inscription').val();
    if (currentClass) {
      fetchTuitionForClass(currentClass);
    }
  });

  // Événements de sélection
  $('#sel_etudiant_inscription').on('change select2:select', function() {
    var val = $(this).val();
    fetchStudentProfile(val);
  });

  $('#sel_classe_inscription').on('change select2:select', function() {
    var val = $(this).val();
    fetchTuitionForClass(val);
  });

  $('input[name="affectation_etat"]').on('change', function() {
    $('input[name="affectation_etat"]').each(function() {
      var isChecked = $(this).is(':checked');
      $(this).closest('label').css({
        'border-color': isChecked ? '#1E3A5F' : '#CBD5E1',
        'background': isChecked ? '#EFF6FF' : '#FFFFFF',
        'color': isChecked ? '#1E3A5F' : '#334155'
      });
    });
    var currentClass = $('#sel_classe_inscription').val();
    if (currentClass) {
      fetchTuitionForClass(currentClass);
    }
  });

  filterInscriptionClassesByAnnee();

  // Chargement initial automatique via paramètre URL ou valeur pré-sélectionnée
  var urlParams = new URLSearchParams(window.location.search);
  var urlEtudiantCode = urlParams.get('etudiant_code');
  if (urlEtudiantCode && !$('#sel_etudiant_inscription').val()) {
    $('#sel_etudiant_inscription').val(urlEtudiantCode).trigger('change');
  }

  var initStu = $('#sel_etudiant_inscription').val() || urlEtudiantCode;
  if (initStu) {
    fetchStudentProfile(initStu);
  }

  var initClasse = $('#sel_classe_inscription').val();
  if (initClasse) {
    fetchTuitionForClass(initClasse);
  }

  // Soumission unique et sécurisée en AJAX avec émission de la Fiche Navette
  $('#form_inscription_main').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $('#btn_submit_inscription');

    if ($submitBtn.prop('disabled') || $submitBtn.hasClass('btn-is-loading')) {
      return false;
    }

    loading($submitBtn, true, 'Validation en cours...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(res) {
        loading($submitBtn, false, 'Valider la Réinscription & Émettre Fiche Navette');
        if (res.status === 1) {
          showToast(res.message || 'Réinscription enregistrée avec succès !', 'success');

          // Remplissage dynamique et affichage de la Fiche Navette pour le Bureau des Versements
          if (res.voucher_data) {
            var v = res.voucher_data;
            $('#v_code_inscription').text(v.code_inscription || 'INS-XXXX');
            $('#v_matricule').text(v.matricule_etudiant || '-');
            $('#v_nom_complet').text(v.nom_complet || '-');
            $('#v_telephone').text(v.telephone_etudiant || '-');
            $('#v_classe').text(v.classe_libelle || '-');
            $('#v_regime').text(v.regime || '-');
            $('#v_annee_libelle').text(v.annee_libelle || 'Session active');
            $('#v_scolarite_totale').text(Number(v.scolarite_totale || 0).toLocaleString('fr-FR') + ' FCFA');
            $('#v_tranche1_montant').text(Number(v.tranche1_montant || 0).toLocaleString('fr-FR') + ' FCFA');
            $('#v_tranche1_libelle').text(v.tranche1_libelle || 'Tranche 1 / Droit de réinscription');
            $('#v_agent').text(v.agent_inscription || 'Agent Scolarité');
            $('#v_date_inscription').text('Délivré le ' + (v.date_inscription || '-'));

            if (Number(v.arrieres_n1 || 0) > 0) {
              $('#v_arrieres_montant').text(Number(v.arrieres_n1).toLocaleString('fr-FR') + ' FCFA (Moratoire)');
              $('#v_arrieres_box').show();
            } else {
              $('#v_arrieres_box').hide();
            }

            // Mettre à jour le bouton de passage direct au Bureau des Versements
            $('#btn-goto-caisse').attr('href', window.RACINE + 'paiement/formulaire?inscription_code=' + encodeURIComponent(v.code_inscription));

            // Ouvrir le modal  de la Fiche Navette
            showFicheNavetteModal();
          } else {
            setTimeout(function() {
              window.location.href = window.RACINE + 'reinscription/list';
            }, 1200);
          }
        } else {
          showToast(res.message || 'Une erreur est survenue lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        loading($submitBtn, false, 'Valider la Réinscription & Émettre Fiche Navette');
        var msg = 'Erreur lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        showToast(msg, 'error');
      }
    });
  });

  // Fonctions de contrôle du modal Fiche Navette
  function showFicheNavetteModal() {
    $('#modal_fiche_navette').css('display', 'flex').hide().fadeIn(200);
    $('body').css('overflow', 'hidden');
    if (window.lucide) lucide.createIcons();
  }

  function hideFicheNavetteModal() {
    $('#modal_fiche_navette').fadeOut(150, function() {
      $(this).css('display', 'none');
      $('body').css('overflow', '');
    });
  }

  // Fermeture par le bouton de fermeture X
  $(document).on('click', '.btn-close-fiche-navette', function(e) {
    e.preventDefault();
    hideFicheNavetteModal();
  });

  // Fermeture par clic sur l'arrière-plan semi-transparent
  $('#modal_fiche_navette').on('click', function(e) {
    if (e.target === this) {
      hideFicheNavetteModal();
    }
  });

  // Fermeture par la touche Échap
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#modal_fiche_navette').is(':visible')) {
      hideFicheNavetteModal();
    }
  });

  // Impression de la Fiche Navette
  $('#btn-print-voucher').on('click', function() {
    window.print();
  });

  // Fonctions de contrôle et d'impression de la Fiche de Situation Financière
  window.openFinancialStatementModal = function() {
    if (!currentStudentData) {
      if (typeof showToast === 'function') {
        showToast("Veuillez d'abord sélectionner un étudiant.", "warning");
      } else {
        alert("Veuillez d'abord sélectionner un étudiant.");
      }
      return;
    }
    var d = currentStudentData;

    $('#fin_doc_date').text(new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
    $('#fin_session_libelle').text(d.derniere_annee || d.already_registered_annee || 'Session Active');
    $('#fin_stu_nom').text(d.nom_complet || '-');
    $('#fin_stu_matricule').text(d.matricule || '-');
    $('#fin_stu_nationalite').text(d.nationalite || 'Ivoirienne');
    $('#fin_stu_regime_badge').text(d.prev_regime || 'Non Défini');
    $('#fin_stu_classe').text(d.derniere_classe || d.already_registered_classe || 'Classe non assignée');
    $('#fin_stu_contact').text(d.telephone || '-');
    
    var parentVal = (d.parent_nom || '').trim();
    if (parentVal && parentVal.toLowerCase().indexOf('non renseigné') === -1 && parentVal.toLowerCase().indexOf('non meublé') === -1 && parentVal !== '-') {
      $('#fin_stu_parent').text(parentVal);
      $('#fin_stu_parent_box').show();
    } else {
      $('#fin_stu_parent_box').hide();
    }

    var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
    if (d.photo_url) {
      $('#fin_stu_photo_img').off('error').on('error', function() {
        $(this).hide();
        $('#fin_stu_avatar').text(initials || 'ET').show();
      }).attr('src', d.photo_url).show();
      $('#fin_stu_avatar').hide();
    } else {
      $('#fin_stu_photo_img').hide();
      $('#fin_stu_avatar').text(initials || 'ET').show();
    }

    var scolarite = Number(d.prev_scolarite || 0);
    var paye = Number(d.prev_paye || 0);
    var solde = Number(d.prev_solde || 0);

    $('#fin_scolarite_totale').text(scolarite.toLocaleString('fr-FR') + ' FCFA');
    $('#fin_total_paye').text(paye.toLocaleString('fr-FR') + ' FCFA');

    if (solde <= 0) {
      $('#fin_solde_restant').css('color', '#15803D').text('Compte Soldé (0 FCFA)');
      $('#fin_solde_box_card').css({ 'background': '#F0FDF4', 'border-color': '#86EFAC' });
      $('#fin_arrieres_alert_box').hide();
    } else {
      $('#fin_solde_restant').css('color', '#DC2626').text(solde.toLocaleString('fr-FR') + ' FCFA');
      $('#fin_solde_box_card').css({ 'background': '#FEF2F2', 'border-color': '#FECACA' });
      $('#fin_arrieres_montant').text(solde.toLocaleString('fr-FR') + ' FCFA');
      $('#fin_arrieres_alert_box').show();
    }

    // Remplissage du tableau des paiements
    var payments = d.history_payments || [];
    $('#fin_paiements_count').text(payments.length + ' règlement(s)');
    if (payments.length > 0) {
      var rowsHtml = '';
      payments.forEach(function(p) {
        var dateFmt = p.date_paiement ? new Date(p.date_paiement).toLocaleDateString('fr-FR') : '-';
        var mntFmt = Number(p.montant_paiement || 0).toLocaleString('fr-FR') + ' FCFA';
        rowsHtml += '<tr style="border-bottom: 1px solid #E2E8F0;">' +
          '<td style="padding: 8px 10px; font-weight: 700; color: #1E3A5F; font-family: monospace;">' + (p.code_paiement || '-') + '</td>' +
          '<td style="padding: 8px 10px;">' + dateFmt + '</td>' +
          '<td style="padding: 8px 10px; text-transform: capitalize;">' + (p.mode_paiement || 'Espèces') + '</td>' +
          '<td style="padding: 8px 10px; color: #64748B;">' + (p.reference_paiement || '-') + '</td>' +
          '<td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #047857;">' + mntFmt + '</td>' +
        '</tr>';
      });
      $('#fin_payments_table_body').html(rowsHtml);
    } else {
      $('#fin_payments_table_body').html('<tr><td colspan="5" style="padding: 12px; text-align: center; color: #64748B; font-style: italic;">Aucun paiement enregistré pour cet étudiant.</td></tr>');
    }

    $('#financial-statement-modal').css('display', 'flex').hide().fadeIn(200);
    $('body').css('overflow', 'hidden');
    if (window.lucide) lucide.createIcons();
  };

  window.printFinancialStatement = function() {
    $('body').addClass('printing-financial-statement');
    window.print();
    setTimeout(function() {
      $('body').removeClass('printing-financial-statement');
    }, 1000);
  };

  $(document).on('click', '#btn-print-financial-statement, #btn-quick-print-finance', function(e) {
    e.preventDefault();
    openFinancialStatementModal();
  });

  $('#financial-statement-modal').on('click', function(e) {
    if (e.target === this) {
      $(this).fadeOut(150, function() {
        $('body').css('overflow', '');
      });
    }
  });

  }

  // 3. VUE : DÉTAILS D'INSCRIPTION (views/inscriptions/details.php)
  function initInscriptionsDetails() {
    if (!$('#btn-print-fiche-inscription').length && !$('#btn-annuler-inscription-detail').length) return;

  if (window.lucide) lucide.createIcons();
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('print')) {
    setTimeout(function() { window.print(); }, 400);
  }

  }

  // 4. VUE : PRISE DE VUE / STUDIO WEBCAM (views/inscriptions/prise_de_vue.php)
  function initInscriptionsPriseDeVue() {
    if (!$('#table-prise-de-vue').length) return;

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  var webcamStream = null;

  function showNotify(msg, type) {
    type = type || 'info';
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else if (window.Swal) {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
      });
      Toast.fire({
        icon: type,
        title: msg
      });
    } else {
      alert(msg);
    }
  }

  function getStudentInitials(fullName) {
    if (!fullName || typeof fullName !== 'string') return '?';
    var parts = fullName.trim().split(/\s+/).filter(function(p) { return p.length > 0; });
    if (parts.length === 0) return '?';
    var inits = parts[0][0];
    if (parts.length > 1) {
      inits += parts[1][0];
    }
    return inits.toUpperCase();
  }

  function renderFramedAvatar(photoPath, fullName, size) {
    size = size || 40;
    var inits = getStudentInitials(fullName);
    var fontSize = Math.round(size * 0.42);
    var escapedName = $('<div>').text(fullName || '').html();
    var avatarBadgeHtml = '<div style="width:' + size + 'px; height:' + size + 'px; border-radius:50%; background:linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color:#FFFFFF; font-weight:800; font-size:' + fontSize + 'px; border:2px solid #3B82F6; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.12); text-transform:uppercase; letter-spacing:0.5px; flex-shrink:0;" title="' + escapedName + '">' + inits + '</div>';

    if (photoPath && photoPath.trim() !== '') {
      var cleanRel = photoPath.trim().replace(/^\/+/, '');
      var fullUrl = racine + cleanRel;
      var escapedFallback = avatarBadgeHtml.replace(/'/g, "\\'");
      return '<img src="' + fullUrl + '" style="width:' + size + 'px; height:' + size + 'px; border-radius:50%; object-fit:cover; border:2px solid #10B981; box-shadow:0 2px 4px rgba(0,0,0,0.1);" title="Photo de ' + escapedName + '" onerror="this.onerror=null; this.outerHTML=\'' + escapedFallback + '\';">';
    }
    return avatarBadgeHtml;
  }

  var table = $('#table-prise-de-vue').DataTable({
    ajax: {
      url: racine + 'inscription/apiPriseDeVue',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filter_photo = $('#filter-photo').val();
        d.filiere_code = $('#filter-filiere').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'photo_path', width: '60px', className: 'text-center', render: function(d, type, row) {
        return renderFramedAvatar(d, row.nom_complet, 40);
      }},
      { data: 'matricule_etudiant', render: function(d) {
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_complet', render: function(d, type, row) {
        var badgePhoto = row.has_photo 
          ? ' <span class="badge bg-success" style="font-size:10px; padding:2px 6px; border-radius:4px; margin-left:4px;">Photo OK</span>' 
          : ' <span class="badge bg-danger" style="font-size:10px; padding:2px 6px; border-radius:4px; margin-left:4px;">Sans photo</span>';
        return '<span style="font-weight:800; color:#0F172A;">' + (d || '-') + '</span>' + badgePhoto;
      }},
      { data: 'libelle_classe', render: function(d) {
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Classe non définie') + '</span>';
      }},
      { data: 'telephone_etudiant', render: function(d) {
        return '<span style="color:#475569; font-weight:600; font-size:13px;">' + (d || '-') + '</span>';
      }},
      { data: 'created_at_inscription', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dp = parts[0].split('-');
        if (dp.length === 3) return '<span style="color:#64748B; font-weight:600; font-size:12.5px;">' + dp[2] + '/' + dp[1] + '/' + dp[0] + '</span>';
        return d;
      }},
      { data: null, orderable: false, width: '140px', render: function(d) {
        var safeNom = $('<div>').text(d.nom_complet || '').html();
        var safeInfo = $('<div>').text((d.matricule_etudiant || '-') + ' • ' + (d.libelle_classe || '-')).html();
        var btnText = d.has_photo ? 'Reprendre Photo' : 'Prendre Photo';
        var btnColor = d.has_photo ? '#475569' : '#1E3A5F';
        return '<button type="button" class="btn btn-sm btn-primary btn-upload-photo" ' +
               'data-id="' + d.id_inscription + '" ' +
               'data-code="' + d.code_inscription + '" ' +
               'data-nom="' + safeNom + '" ' +
               'data-info="' + safeInfo + '" ' +
               'style="font-weight:700; border-radius:6px; background:' + btnColor + '; border-color:' + btnColor + '; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
               '<i data-lucide="camera" style="width:14px;height:14px;"></i> ' + btnText + '</button>';
      }, className: 'text-center' }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee, #filter-photo, #filter-filiere, #filter-classe').on('change', function() {
    table.ajax.reload();
  });

  function startWebcam() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ video: { width: 400, height: 400 } })
        .then(function(stream) {
          webcamStream = stream;
          var video = document.getElementById('webcam-video');
          video.srcObject = stream;
          video.play();
        })
        .catch(function(err) {
          console.warn("Impossible d'accéder à la webcam:", err);
          showNotify("Caméra inaccessible. Vous pouvez téléverser un fichier image.", "info");
          switchToTab('file');
        });
    } else {
      switchToTab('file');
    }
  }

  function stopWebcam() {
    if (webcamStream) {
      webcamStream.getTracks().forEach(function(track) { track.stop(); });
      webcamStream = null;
    }
  }

  function switchToTab(mode) {
    if (mode === 'webcam') {
      $('#tab-mode-webcam').css({ 'background': '#1E3A5F', 'color': '#FFFFFF' });
      $('#tab-mode-file').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#section-webcam').show();
      $('#section-file').hide();
      $('#input_photo_file').val('');
      startWebcam();
    } else {
      $('#tab-mode-file').css({ 'background': '#1E3A5F', 'color': '#FFFFFF' });
      $('#tab-mode-webcam').css({ 'background': '#F1F5F9', 'color': '#475569' });
      $('#section-file').show();
      $('#section-webcam').hide();
      $('#photo_webcam_data').val('');
      stopWebcam();
    }
  }

  $('#tab-mode-webcam').on('click', function() { switchToTab('webcam'); });
  $('#tab-mode-file').on('click', function() { switchToTab('file'); });

  $(document).on('click', '.btn-upload-photo', function() {
    var id = $(this).data('id');
    var code = $(this).data('code');
    var nom = $(this).data('nom');
    var info = $(this).data('info');

    $('#photo_modal_id_inscription').val(id);
    $('#photo_modal_code_inscription').val(code);
    $('#photo_modal_student_name').text(nom);
    $('#photo_modal_student_info').text(info);

    $('#photo_webcam_data').val('');
    $('#input_photo_file').val('');
    $('#webcam-captured-preview').hide().attr('src', '');
    $('#webcam-video').show();
    $('#btn-capture-webcam').show();
    $('#btn-retake-webcam').hide();
    var inits = getStudentInitials(nom);
    $('#preview-placeholder').css({
      'width': '120px',
      'height': '120px',
      'border-radius': '50%',
      'background': 'linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%)',
      'border': '3px solid #3B82F6',
      'color': '#FFFFFF',
      'font-size': '42px',
      'font-weight': '800',
      'box-shadow': '0 4px 10px rgba(0,0,0,0.15)',
      'display': 'inline-flex',
      'align-items': 'center',
      'justify-content': 'center',
      'margin': '0 auto',
      'letter-spacing': '1px'
    }).html(inits).show();
    $('#preview-file-img').hide().attr('src', '');

    $('#modal-upload-photo').css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    switchToTab('webcam');
  });

  $('#btn-capture-webcam').on('click', function() {
    var video = document.getElementById('webcam-video');
    var canvas = document.getElementById('webcam-canvas');
    var context = canvas.getContext('2d');
    canvas.width = video.videoWidth || 300;
    canvas.height = video.videoHeight || 300;

    context.translate(canvas.width, 0);
    context.scale(-1, 1);
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    var dataUrl = canvas.toDataURL('image/png');
    $('#photo_webcam_data').val(dataUrl);

    $('#webcam-video').hide();
    $('#webcam-captured-preview').attr('src', dataUrl).show();
    $('#btn-capture-webcam').hide();
    $('#btn-retake-webcam').css('display', 'inline-flex');
  });

  $('#btn-retake-webcam').on('click', function() {
    $('#photo_webcam_data').val('');
    $('#webcam-captured-preview').hide();
    $('#webcam-video').show();
    $('#btn-retake-webcam').hide();
    $('#btn-capture-webcam').show();
  });

  $('#input_photo_file').on('change', function() {
    var file = this.files[0];
    if (file) {
      $('#photo_webcam_data').val('');
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#preview-placeholder').hide();
        $('#preview-file-img').attr('src', e.target.result).show();
      };
      reader.readAsDataURL(file);
    }
  });

  function closeModal() {
    stopWebcam();
    $('#modal-upload-photo').css('display', 'none');
  }

  $('.btn-close-modal-photo').on('click', closeModal);
  $('#modal-upload-photo').on('click', function(e) {
    if ($(e.target).is('#modal-upload-photo')) {
      closeModal();
    }
  });

  $('#form-upload-photo').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var $btn = $('#btn-save-photo');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: racine + 'inscription/uploadPhoto',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Photo d\'inscription enregistrée avec succès !', 'success');
          closeModal();
          table.ajax.reload(null, false);
        } else {
          showNotify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur serveur ou réseau';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        } else if (xhr.responseText) {
          try {
            var json = JSON.parse(xhr.responseText);
            if (json && json.message) msg = json.message;
          } catch(e) {
            if (xhr.statusText && xhr.status) msg = 'Erreur (' + xhr.status + ') : ' + xhr.statusText;
          }
        }
        showNotify(msg, 'error');
      }
    });
  });

  }

  // 5. VUE : GESTION DES DOSSIERS SANS PHOTO (views/inscriptions/sans_photo.php)
  function initInscriptionsSansPhoto() {
    if (!$('#table-sans-photo').length) return;

  var racine = (typeof RACINE !== 'undefined') ? RACINE : (window.RACINE || '/geicg/');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : { url: racine + 'json/datatables-i18n-fr-FR.json' };

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  function showNotify(msg, type) {
    type = type || 'info';
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else if (window.Swal) {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
      });
      Toast.fire({
        icon: type,
        title: msg
      });
    } else {
      alert(msg);
    }
  }

  function getStudentInitials(fullName) {
    if (!fullName || typeof fullName !== 'string') return '?';
    var parts = fullName.trim().split(/\s+/).filter(function(p) { return p.length > 0; });
    if (parts.length === 0) return '?';
    var inits = parts[0][0];
    if (parts.length > 1) {
      inits += parts[1][0];
    }
    return inits.toUpperCase();
  }

  function renderFramedAvatar(photoPath, fullName, size) {
    size = size || 38;
    var inits = getStudentInitials(fullName);
    var fontSize = Math.round(size * 0.42);
    var escapedName = $('<div>').text(fullName || '').html();
    var avatarBadgeHtml = '<div style="width:' + size + 'px; height:' + size + 'px; border-radius:50%; background:linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color:#FFFFFF; font-weight:800; font-size:' + fontSize + 'px; border:2px solid #3B82F6; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.12); text-transform:uppercase; letter-spacing:0.5px; flex-shrink:0;" title="' + escapedName + '">' + inits + '</div>';

    if (photoPath && photoPath.trim() !== '') {
      var cleanRel = photoPath.trim().replace(/^\/+/, '');
      var fullUrl = racine + cleanRel;
      var escapedFallback = avatarBadgeHtml.replace(/'/g, "\\'");
      return '<img src="' + fullUrl + '" style="width:' + size + 'px; height:' + size + 'px; border-radius:50%; object-fit:cover; border:2px solid #10B981; box-shadow:0 2px 4px rgba(0,0,0,0.1);" title="Photo de ' + escapedName + '" onerror="this.onerror=null; this.outerHTML=\'' + escapedFallback + '\';">';
    }
    return avatarBadgeHtml;
  }

  var table = $('#table-sans-photo').DataTable({
    ajax: {
      url: racine + 'inscription/apiSansPhoto',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.classe_code = $('#filter-classe').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'photo_inscription', width: '60px', className: 'text-center', render: function(d, type, row) {
        return renderFramedAvatar(d, row.nom_complet, 38);
      }},
      { data: 'matricule_etudiant', render: function(d) {
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</code>';
      }},
      { data: 'nom_complet', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'libelle_classe', render: function(d, t, r) {
        var fil = r.libelle_filiere ? ' <span style="font-size:11.5px; color:#64748B;">(' + r.libelle_filiere + ')</span>' : '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Classe non définie') + '</span>' + fil;
      }},
      { data: 'telephone_etudiant', render: function(d) {
        return '<span style="color:#475569; font-weight:600; font-size:13px;">' + (d || '-') + '</span>';
      }},
      { data: 'created_at_inscription', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dp = parts[0].split('-');
        if (dp.length === 3) return '<span style="color:#64748B; font-weight:600; font-size:12.5px;">' + dp[2] + '/' + dp[1] + '/' + dp[0] + '</span>';
        return d;
      }},
      { data: null, orderable: false, render: function(d) {
        var safeNom = $('<div>').text(d.nom_complet || '').html();
        var safeInfo = $('<div>').text((d.matricule_etudiant || '-') + ' • ' + (d.libelle_classe || '-')).html();
        return '<button type="button" class="btn btn-sm btn-primary btn-upload-photo" ' +
               'data-id="' + d.id_inscription + '" ' +
               'data-code="' + d.code_inscription + '" ' +
               'data-nom="' + safeNom + '" ' +
               'data-info="' + safeInfo + '" ' +
               'style="font-weight:700; border-radius:6px; background:#1E3A5F; border-color:#1E3A5F; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
               '<i data-lucide="camera" style="width:14px;height:14px;"></i> Ajouter Photo</button>';
      }, className: 'text-end' }
    ],
    language: dtLang,
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee, #filter-filiere, #filter-classe').on('change', function() {
    table.ajax.reload();
  });

  $(document).on('click', '.btn-upload-photo', function() {
    var id = $(this).data('id');
    var code = $(this).data('code');
    var nom = $(this).data('nom');
    var info = $(this).data('info');

    $('#photo_modal_id_inscription').val(id);
    $('#photo_modal_code_inscription').val(code);
    $('#photo_modal_student_name').text(nom);
    $('#photo_modal_student_info').text(info);

    $('#input_photo_file').val('');
    $('#preview-img').hide().attr('src', '');
    $('#preview-placeholder').show();

    $('#modal-upload-photo').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('#input_photo_file').on('change', function() {
    var file = this.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#preview-placeholder').hide();
        $('#preview-img').attr('src', e.target.result).show();
      };
      reader.readAsDataURL(file);
    }
  });

  $('.btn-close-modal-photo').on('click', function() {
    $('#modal-upload-photo').css('display', 'none');
  });
  $('#modal-upload-photo').on('click', function(e) {
    if ($(e.target).is('#modal-upload-photo')) {
      $(this).css('display', 'none');
    }
  });

  $('#form-upload-photo').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var $btn = $('#btn-save-photo');

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Téléversement...');

    $.ajax({
      url: racine + 'inscription/uploadPhoto',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Photo téléversée avec succès !', 'success');
          $('#modal-upload-photo').css('display', 'none');
          table.ajax.reload(null, false);
        } else {
          showNotify(res.message || 'Erreur lors du téléversement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Photo');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur serveur ou réseau';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        showNotify(msg, 'error');
      }
    });
  });

  }

  $(document).ready(function() {
    initInscriptionsList();
    initInscriptionsEdit();
    initInscriptionsDetails();
    initInscriptionsPriseDeVue();
    initInscriptionsSansPhoto();
    if (window.lucide) lucide.createIcons();
  });
})(window, jQuery);
