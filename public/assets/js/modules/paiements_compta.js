/**
 * Module JS: Comptabilité des Étudiants & Suivi des Règlements
 * Externalisation stricte du code JS pour views/paiements/comptabilite_etudiants.php
 */

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
