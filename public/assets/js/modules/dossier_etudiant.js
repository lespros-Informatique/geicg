$(document).ready(function() {
  var table;
  var currentStudentData = null;

  if ($.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  if ($.fn.select2) {
    $('#filter-annee, #filter-classe, #filter-statut').select2({ width: '100%' });
  }

  function initTable() {
    table = $('#table-dossiers').DataTable({
      ajax: {
        url: window.RACINE + 'dossier_etudiant/apiList',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
          d.filter_statut = $('#filter-statut').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement DataTables:", error, thrown);
        },
        dataSrc: function(json) {
          var items = json.data || [];
          var totalCount = items.length;
          var completsCount = 0;
          var incompletsCount = 0;
          var totalPiecesGlobal = 0;
          var totalDeposeesGlobal = 0;

          items.forEach(function(i) {
            if (i.est_complet) completsCount++;
            else incompletsCount++;
            totalPiecesGlobal += i.total_pieces;
            totalDeposeesGlobal += i.deposees_count;
          });

          var tauxGlobal = (totalPiecesGlobal > 0) ? Math.round((totalDeposeesGlobal / totalPiecesGlobal) * 100) : 0;

          $('#kpi-total-etudiants').text(totalCount);
          $('#kpi-dossiers-complets').text(completsCount);
          $('#kpi-dossiers-incomplets').text(incompletsCount);
          $('#kpi-taux-global').text(tauxGlobal + '%');

          return items;
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var rawName = (d.nom_complet ? d.nom_complet.trim() : '') || 'Étudiant non identifié';
            var parts = rawName.split(' ').filter(function(p) { return p.length > 0; });
            var initials = 'ET';
            if (parts.length >= 2) {
              initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            } else if (parts.length === 1 && parts[0].length >= 2) {
              initials = parts[0].substring(0, 2).toUpperCase();
            }

            var photoPath = d.photo ? String(d.photo).trim() : '';
            var initialsDiv = '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';

            var avatarHtml = initialsDiv;
            if (photoPath !== '') {
              var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : (window.RACINE + photoPath.replace(/^\//, ''));
              avatarHtml = '<img src="' + photoUrl + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; flex-shrink: 0;" onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';">' +
                           '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';
            }

            return '<div style="display: flex; align-items: center; gap: 12px; min-width: 180px;">' +
                     avatarHtml +
                     '<div>' +
                       '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">' + escapeHtml(rawName) + '</div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: 'matricule',
          render: function(d) {
            return '<span class="badge bg-light text-dark font-monospace" style="font-size: 12px; font-weight: 700; padding: 6px 10px; border: 1px solid #CBD5E1;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: 'classe',
          render: function(d) {
            return '<span style="font-weight: 700; color: #1E3A5F;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var badgeClass = d.est_complet ? 'bg-success' : (d.deposees_count > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.deposees_count + ' / ' + d.total_pieces + ' pièce(s)' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 120px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
                       '<div style="height: 100%; width: ' + pct + '%; background: ' + barColor + '; border-radius: 4px;"></div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function(d) {
            return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                     '<button type="button" class="btn btn-sm btn-primary btn-open-dossier" style="font-weight: 700; border-radius: 8px; padding: 6px 11px;" title="Gérer les pièces et les scans">' +
                       '<i data-lucide="folder-open" style="width: 14px; height: 14px; margin-right: 4px;"></i> Gérer' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-primary btn-print-recepisse-row" title="Imprimer le Récépissé" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="printer" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate" title="Tout valider en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="check-check" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                   '</div>';
          }
        }
      ],
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      },
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
      }
    });
  }

  initTable();

  // Filtrage interactif
  $('#filter-annee, #filter-classe, #filter-statut').on('change', function() {
    table.ajax.reload();
  });

  $('.kpi-card-clickable').on('click', function() {
    var status = $(this).data('status');
    $('#filter-statut').val(status).trigger('change.select2');
    table.ajax.reload();
  });

  $('#btn-refresh').on('click', function() {
    table.ajax.reload();
  });

  // Action Gérer le dossier (Modal)
  $('#table-dossiers').on('click', '.btn-open-dossier', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openDossierModal(rowData);
  });

  // Action Impression Récépissé en ligne
  $('#table-dossiers').on('click', '.btn-print-recepisse-row', function() {
    var rowData = table.row($(this).closest('tr')).data();
    loadAndOpenRecepisseModal(rowData.code_inscription);
  });

  $('#btn-print-modal-recepisse').on('click', function() {
    if (currentStudentData) {
      loadAndOpenRecepisseModal(currentStudentData.code_inscription);
    }
  });

  // Fermeture des modals
  $('.btn-close-modal-dossier').on('click', function() {
    $('#modal-dossier').hide();
  });
  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-dossier')) $('#modal-dossier').hide();
    if ($(e.target).is('#modal-recepisse')) $('#modal-recepisse').hide();
  });

  $('.btn-close-modal-recepisse').on('click', function() {
    $('#modal-recepisse').hide();
  });

  // Action Tout valider rapide
  $('#table-dossiers').on('click', '.btn-quick-validate', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllStudentPieces(rowData.code_inscription, rowData.code_etudiant);
  });

  $('#btn-tout-valider-modal').on('click', function() {
    if (currentStudentData) {
      validateAllStudentPieces(currentStudentData.code_inscription, currentStudentData.code_etudiant);
    }
  });

  function updatePieceCardStyle($card, st) {
    var borderCol = '#EA580C';
    var bgCol = '#FFF7ED';
    if (st === 'depose') {
      borderCol = '#16A34A';
      bgCol = '#F0FDF4';
    } else if (st === 'rejete') {
      borderCol = '#DC2626';
      bgCol = '#FEF2F2';
    } else if (st === 'non_requis') {
      borderCol = '#64748B';
      bgCol = '#F8FAFC';
    }
    $card.css({
      'border-left': '5px solid ' + borderCol,
      'background': bgCol
    });
  }

  $(document).on('change', '.select-statut-piece', function() {
    var st = $(this).val();
    var $card = $(this).closest('.piece-card-form');
    updatePieceCardStyle($card, st);
  });

  function openDossierModal(d) {
    $('#modal-student-name').text(d.nom_complet);
    $('#modal-student-info').text('Matricule : ' + (d.matricule || '-') + ' | Classe : ' + (d.classe || '-'));
    $('#modal-completion-text').text(d.deposees_count + ' / ' + d.total_pieces + ' Pièce(s) Déposée(s) (' + d.pourcentage + '%)');

    var html = '';
    if (d.dossier_items && d.dossier_items.length > 0) {
      d.dossier_items.forEach(function(item) {
        var st = item.statut_depot || 'en_attente';
        var borderCol = st === 'depose' ? '#16A34A' : (st === 'rejete' ? '#DC2626' : (st === 'non_requis' ? '#64748B' : '#EA580C'));
        var bgCol = st === 'depose' ? '#F0FDF4' : (st === 'rejete' ? '#FEF2F2' : (st === 'non_requis' ? '#F8FAFC' : '#FFF7ED'));

        var fileHtml = '';
        if (item.fichier_joint) {
          var fullFileUrl = item.fichier_joint.startsWith('http') ? item.fichier_joint : (window.RACINE + item.fichier_joint);
          fileHtml = '<div style="margin-top: 6px;"><a href="' + fullFileUrl + '" target="_blank" class="btn btn-sm btn-outline-info" style="font-size: 11.5px; font-weight: 700; border-radius: 6px; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="file-text" style="width: 14px; height: 14px;"></i> Voir le scan joint</a></div>';
        }

        var dateDepotText = item.date_depot ? ('<span style="font-size: 11px; font-weight: 600; color: #15803D; margin-left: 6px;">(Déposé le ' + item.date_depot + ')</span>') : '';

        html += '<form class="piece-card-form" style="background: ' + bgCol + '; border-left: 5px solid ' + borderCol + '; padding: 16px; border-radius: 12px; border-top: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">' +
                  '<input type="hidden" name="inscription_code" value="' + d.code_inscription + '">' +
                  '<input type="hidden" name="etudiant_code" value="' + d.code_etudiant + '">' +
                  '<input type="hidden" name="piece_code" value="' + item.piece_code + '">' +
                  
                  '<div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">' +
                    '<div>' +
                      '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_piece) + dateDepotText + '</div>' +
                      '<div style="font-size: 12px; color: #64748B; margin-top: 2px;">' + escapeHtml(item.description_piece || 'Pièce exigée pour le dossier') + '</div>' +
                      fileHtml +
                    '</div>' +
                    '<div>' +
                      '<select name="statut" class="form-select form-select-sm select-statut-piece" style="font-weight: 700; border-radius: 8px; min-width: 140px;">' +
                        '<option value="depose" ' + (st === 'depose' ? 'selected' : '') + '>✅ Déposé</option>' +
                        '<option value="en_attente" ' + (st === 'en_attente' ? 'selected' : '') + '>⏳ En attente</option>' +
                        '<option value="rejete" ' + (st === 'rejete' ? 'selected' : '') + '>❌ Rejeté</option>' +
                        '<option value="non_requis" ' + (st === 'non_requis' ? 'selected' : '') + '>⚪ Non requis</option>' +
                      '</select>' +
                    '</div>' +
                  '</div>' +

                  '<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">' +
                    '<div style="flex: 1; min-width: 180px;">' +
                      '<input type="text" name="observations" class="form-control form-control-sm" placeholder="Remarques / Observations..." value="' + escapeHtml(item.observations || '') + '" style="border-radius: 6px; font-size: 12px;">' +
                    '</div>' +
                    '<div style="flex: 1; min-width: 180px;">' +
                      '<input type="file" name="fichier_joint" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" title="Joindre un scan numérisé (PDF/Image)" style="font-size: 11px; border-radius: 6px;">' +
                    '</div>' +
                    '<div>' +
                      '<button type="submit" class="btn btn-sm btn-primary btn-save-piece" style="font-weight: 700; border-radius: 6px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer</button>' +
                    '</div>' +
                  '</div>' +
                '</form>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucune pièce exigée configurée pour le cycle de cette classe.</div>';
    }

    $('#modal-pieces-list').html(html);
    $('#modal-dossier').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  }

  // Action enregistrer modification statut unitaire avec upload éventuel
  $(document).on('submit', '.piece-card-form', function(e) {
    e.preventDefault();
    var form = this;
    var $btn = $(form).find('.btn-save-piece');
    var formData = new FormData(form);

    $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spin" style="width: 14px; height: 14px;"></i>');

    $.ajax({
      url: window.RACINE + 'dossier_etudiant/saveStatut',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistré !');
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Pièce mise à jour avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Pièce mise à jour avec succès');
          setTimeout(function() { $btn.html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer'); }, 1500);
          table.ajax.reload(null, false);
        } else {
          var err = res.message || 'Erreur lors de l\'enregistrement';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer');
        var msg = 'Erreur lors de la mise à jour.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Action Tout valider rapide
  function validateAllStudentPieces(insCode, etuCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les pièces de ce dossier comme DÉPOSÉES ?")) return;

    $.ajax({
      url: window.RACINE + 'dossier_etudiant/saveAll',
      type: 'POST',
      data: {
        inscription_code: insCode,
        etudiant_code: etuCode
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Toutes les pièces ont été validées avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          table.ajax.reload(null, false);
          $('#modal-dossier').hide();
        } else {
          var msgErr = res.message || 'Erreur lors de la validation.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur de connexion au serveur.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  }

  // Chargement et impression du Bordereau / Récépissé 
  function loadAndOpenRecepisseModal(inscrCode) {
    $.ajax({
      url: window.RACINE + 'dossier_etudiant/getRecepisseData',
      type: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { inscription_code: inscrCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.student) {
          var s = res.student;
          $('#rec-student-name').text((s.nom_etudiant || '') + ' ' + (s.prenom_etudiant || ''));
          $('#rec-student-matricule').text(s.matricule_etudiant || '-');
          $('#rec-student-classe').text(s.libelle_classe || '-');
          $('#rec-student-phone').text(s.telephone_etudiant || '-');
          $('#rec-annee').text(s.libelle_annee ? ('Année Académique ' + s.libelle_annee) : 'GEICG');
          $('#rec-date-emission').text(res.date_emission || '-');
          $('#rec-agent-nom').text(res.agent_nom || 'Administration');

          if (s.nom_etablissement) $('#rec-etab-nom').text(s.nom_etablissement);
          if (s.adresse_etablissement) $('#rec-etab-adresse').text((s.adresse_etablissement || '') + ' ' + (s.ville_etablissement || ''));
          if (s.telephone_etablissement) $('#rec-etab-tel').text('Tél : ' + s.telephone_etablissement);
          if (s.logo_etablissement) $('#rec-etab-logo').attr('src', window.RACINE + s.logo_etablissement).show();

          $('#rec-count-deposees').text(res.deposees_count || 0);
          $('#rec-count-manquantes').text(res.manquantes_count || 0);

          // Table pièces déposées
          var htmlDep = '';
          if (res.pieces_deposees && res.pieces_deposees.length > 0) {
            res.pieces_deposees.forEach(function(p) {
              htmlDep += '<tr>' +
                           '<td style="padding:8px;"><strong>' + escapeHtml(p.libelle_piece) + '</strong></td>' +
                           '<td style="padding:8px;" class="text-success fw-bold">✓ ' + (p.date_depot || 'Déposé') + '</td>' +
                           '<td style="padding:8px;">' + escapeHtml(p.observations || '-') + '</td>' +
                         '</tr>';
            });
          } else {
            htmlDep = '<tr><td colspan="3" class="text-muted text-center" style="padding:10px;">Aucune pièce déposée pour le moment.</td></tr>';
          }
          $('#rec-table-deposees').html(htmlDep);

          // Table pièces manquantes
          var htmlManq = '';
          if (res.pieces_manquantes && res.pieces_manquantes.length > 0) {
            res.pieces_manquantes.forEach(function(p) {
              var badgeSt = p.statut_depot === 'rejete' ? '<span class="badge bg-danger">❌ Rejeté</span>' : '<span class="badge bg-warning text-dark">⏳ En attente</span>';
              htmlManq += '<tr>' +
                            '<td style="padding:8px;"><strong>' + escapeHtml(p.libelle_piece) + '</strong></td>' +
                            '<td style="padding:8px;">' + badgeSt + '</td>' +
                            '<td style="padding:8px;">' + escapeHtml(p.observations || 'À fournir impérativement') + '</td>' +
                          '</tr>';
            });
          } else {
            htmlManq = '<tr><td colspan="3" class="text-success text-center fw-bold" style="padding:10px;">🎉 Dossier complet ! Aucune pièce manquante.</td></tr>';
          }
          $('#rec-table-manquantes').html(htmlManq);

          $('#modal-recepisse').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          var err = res.message || 'Erreur lors de la génération du récépissé.';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
          else if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: err });
        }
      },
      error: function(xhr) {
        var msg = 'Erreur lors de la connexion au serveur.';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
        else if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
      }
    });
  }

  $('#btn-trigger-print-recepisse').on('click', function() {
    window.print();
  });

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
