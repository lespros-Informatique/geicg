/**
 * Module JS: Registre d'Émargement des Fournitures & Bon de Remise A4
 * Externalisation stricte du code JS pour views/accessoires/registre.php
 */
$(document).ready(function() {
  var table;
  var currentStudentData = null;
  var racine = (typeof RACINE !== 'undefined') ? RACINE : '/geicg/';

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  if ($.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  if ($.fn.select2) {
    $('#filter-annee, #filter-classe, #filter-statut').select2({ width: '100%' });
    $('#modal-select-etudiant, #modal-select-accessoires').select2({
      dropdownParent: $('#modal-attribuer-kit'),
      width: '100%'
    });
  }

  var distUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiDistributions') : (racine + 'accessoire/apiDistributions');
  var dtLang = (typeof GEICG !== 'undefined' && GEICG.dataTableLanguage) ? GEICG.dataTableLanguage : (racine + 'json/datatables-i18n-fr-FR.json');

  function initTable() {
    table = $('#table-distributions').DataTable({
      ajax: {
        url: distUrl,
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
          d.filter = $('#filter-statut').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement des distributions:", error, thrown);
        },
        dataSrc: function(json) {
          return json.data || [];
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var rawName = (d.nom_complet ? d.nom_complet.trim() : '') || 
                          ((d.nom_etudiant || '') + ' ' + (d.prenom_etudiant || '')).trim() || 
                          'Étudiant non identifié';
            
            var parts = rawName.split(' ').filter(function(p) { return p.length > 0; });
            var initials = 'ET';
            if (parts.length >= 2) {
              initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            } else if (parts.length === 1 && parts[0].length >= 2) {
              initials = parts[0].substring(0, 2).toUpperCase();
            }

            var photoPath = d.photo_etudiant ? String(d.photo_etudiant).trim() : '';
            var initialsDiv = '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(30,58,95,0.15);">' + escapeHtml(initials) + '</div>';

            var avatarHtml = initialsDiv;
            if (photoPath !== '') {
              var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : (racine + photoPath.replace(/^\//, ''));
              avatarHtml = '<img src="' + photoUrl + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; flex-shrink: 0;" onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';">' +
                           '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';
            }

            var phone = (d.telephone_etudiant && d.telephone_etudiant !== '-') ? d.telephone_etudiant : null;
            var phoneHtml = phone ? '<div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Tél : ' + escapeHtml(phone) + '</div>' : '<div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Tél : -</div>';

            return '<div style="display: flex; align-items: center; gap: 12px; min-width: 180px;">' +
                     avatarHtml +
                     '<div style="min-width: 0;">' +
                       '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px; line-height: 1.3;">' + escapeHtml(rawName) + '</div>' +
                       phoneHtml +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            return '<div>' +
                     '<span class="badge bg-light text-dark font-monospace border" style="font-size: 11.5px; font-weight: 700; padding: 4px 8px;">' + escapeHtml(d.matricule_etudiant || '-') + '</span>' +
                     '<div style="font-weight: 700; color: #1E3A5F; font-size: 12px; margin-top: 3px;">' + escapeHtml(d.libelle_classe || '-') + '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            var items = d.items || [];
            if (items.length === 0) {
              return '<span class="text-muted" style="font-size: 12px;">Aucun article souscrit</span>';
            }

            var html = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
            items.forEach(function(item) {
              if (item.etat_retrait === 'retire') {
                html += '<span class="badge bg-success" style="font-size: 11px; font-weight: 700; padding: 4px 8px;" title="Remis le ' + (item.date_retrait_formatee || '-') + '">✓ ' + escapeHtml(item.libelle_accessoire) + '</span>';
              } else {
                html += '<span class="badge bg-warning text-dark" style="font-size: 11px; font-weight: 700; padding: 4px 8px;" title="En attente de remise">⏳ ' + escapeHtml(item.libelle_accessoire) + '</span>';
              }
            });
            html += '</div>';
            return html;
          }
        },
        {
          data: null,
          render: function(d) {
            var isComplet = (d.total_retires === d.total_kits && d.total_kits > 0);
            var badgeClass = isComplet ? 'bg-success' : (d.total_retires > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.total_retires + ' / ' + d.total_kits + ' remis' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage || 0;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 110px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 7px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
                       '<div style="height: 100%; width: ' + pct + '%; background: ' + barColor + '; border-radius: 4px;"></div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function() {
            return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                     '<button type="button" class="btn btn-sm btn-success btn-open-student-kit" style="font-weight: 700; border-radius: 8px; padding: 6px 11px; background: #15803D; border: none;" title="Gérer et émarger les fournitures de cet élève">' +
                       '<i data-lucide="package-check" style="width: 14px; height: 14px; margin-right: 4px;"></i> Émarger' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-primary btn-print-bon-row" title="Imprimer le Bon de Remise A4" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="printer" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate-all-kits" title="Tout marquer comme remis en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="check-check" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                   '</div>';
          }
        }
      ],
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      },
      language: (typeof dtLang === 'object') ? dtLang : { url: dtLang }
    });
  }

  initTable();

  // Filtrage interactif
  $('#filter-annee, #filter-classe, #filter-statut').on('change', function() {
    table.ajax.reload();
    refreshKpis();
  });

  $('.kpi-card-clickable').on('click', function() {
    var status = $(this).data('status');
    $('#filter-statut').val(status).trigger('change.select2');
    table.ajax.reload();
  });

  $('#btn-refresh, #btn-refresh-header').on('click', function() {
    table.ajax.reload();
    refreshKpis();
  });

  function refreshKpis() {
    var annee = $('#filter-annee').val();
    var statsUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/apiStats') : (racine + 'accessoire/apiStats');
    $.ajax({
      url: statsUrl,
      type: 'GET',
      data: { annee_code: annee },
      dataType: 'json',
      success: function(res) {
        var s = res.stats || res || {};
        $('#kpi-total-kits').text(s.total || 0);
        $('#kpi-en-attente').text(s.en_attente || 0);
        $('#kpi-retires').text(s.retires || (s.retire || 0));
        $('#kpi-taux').text((s.taux_retrait || (s.taux || 0)) + '%');
      }
    });
  }

  // Ouvrir le modal d'émargement détaillé de l'étudiant
  $('#table-distributions').on('click', '.btn-open-student-kit', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openStudentKitModal(rowData);
  });

  // Ouvrir le Bon de remise depuis la ligne
  $('#table-distributions').on('click', '.btn-print-bon-row', function() {
    var rowData = table.row($(this).closest('tr')).data();
    loadAndOpenBonRemiseModal(rowData.code_inscription);
  });

  $('#btn-print-bon-modal').on('click', function() {
    if (currentStudentData) {
      loadAndOpenBonRemiseModal(currentStudentData.code_inscription);
    }
  });

  // Tout marquer comme remis pour l'étudiant
  $('#table-distributions').on('click', '.btn-quick-validate-all-kits', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllKitsForStudent(rowData.code_inscription);
  });

  $('#btn-tout-valider-kit-modal').on('click', function() {
    if (currentStudentData) {
      validateAllKitsForStudent(currentStudentData.code_inscription);
    }
  });

  // Fermeture des modals
  $('.btn-close-modal-kit').on('click', function() {
    $('#modal-emargement-student').hide();
  });
  $('.btn-close-modal-bon').on('click', function() {
    $('#modal-bon-remise').hide();
  });
  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-emargement-student')) $('#modal-emargement-student').hide();
    if ($(e.target).is('#modal-bon-remise')) $('#modal-bon-remise').hide();
    if ($(e.target).is('#modal-attribuer-kit')) $('#modal-attribuer-kit').hide();
  });

  function openStudentKitModal(d) {
    $('#modal-kit-student-name').text(d.nom_complet);
    $('#modal-kit-student-info').text('Matricule : ' + (d.matricule_etudiant || '-') + ' | Classe : ' + (d.libelle_classe || '-'));
    $('#modal-kit-completion-text').text(d.total_retires + ' / ' + d.total_kits + ' Kits remis (' + d.pourcentage + '%)');

    var html = '';
    var items = d.items || [];
    if (items.length > 0) {
      items.forEach(function(item) {
        var isRetire = (item.etat_retrait === 'retire');
        var borderCol = isRetire ? '#16A34A' : '#EA580C';
        var bgCol = isRetire ? '#F0FDF4' : '#FFF7ED';
        var btnClass = isRetire ? 'btn-outline-secondary' : 'btn-success';
        var btnText = isRetire ? 'Annuler la remise' : 'Marquer comme Remis';
        var iconName = isRetire ? 'rotate-ccw' : 'check';
        var dateText = item.date_retrait_formatee ? ('<span style="font-size: 11px; font-weight: 600; color: #15803D; margin-left: 6px;">(Remis le ' + item.date_retrait_formatee + ')</span>') : '<span style="font-size: 11px; font-weight: 600; color: #EA580C; margin-left: 6px;">(En attente de retrait)</span>';

        html += '<div style="background: ' + bgCol + '; border-left: 5px solid ' + borderCol + '; padding: 14px 18px; border-radius: 12px; border-top: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">' +
                  '<div>' +
                    '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_accessoire) + dateText + '</div>' +
                    '<div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Fourniture souscrite d\'inscription</div>' +
                  '</div>' +
                  '<div>' +
                    '<button type="button" class="btn btn-sm ' + btnClass + ' btn-toggle-item-modal" data-id="' + item.id_accessoire_inscription + '" style="font-weight: 700; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px;">' +
                      '<i data-lucide="' + iconName + '" style="width: 15px; height: 15px;"></i> ' + btnText +
                    '</button>' +
                  '</div>' +
                '</div>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucun article souscrit pour cet élève.</div>';
    }

    $('#modal-kit-items-list').html(html);
    $('#modal-emargement-student').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  }

  // Action toggle item unitaire dans le modal
  $(document).on('click', '.btn-toggle-item-modal', function() {
    var id = $(this).data('id');
    var $btn = $(this);
    $btn.prop('disabled', true);
    var toggleUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/toggleRetrait') : (racine + 'accessoire/toggleRetrait');

    $.ajax({
      url: toggleUrl,
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour');
          table.ajax.reload(function() {
            if (currentStudentData) {
              var updatedData = table.rows().data().toArray().find(function(r) {
                return r.code_inscription === currentStudentData.code_inscription;
              });
              if (updatedData) {
                currentStudentData = updatedData;
                openStudentKitModal(updatedData);
              }
            }
          }, false);
          refreshKpis();
        } else {
          $btn.prop('disabled', false);
          var msg = res.message || 'Erreur de mise à jour';
          if (typeof showToast === 'function') showToast(msg, 'error');
          else if (window.toastr) toastr.error(msg);
        }
      },
      error: function() {
        $btn.prop('disabled', false);
        var msg = 'Erreur lors de la mise à jour du statut.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Action Tout valider pour un étudiant
  function validateAllKitsForStudent(inscrCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les fournitures de cet élève comme REMISES / RETIRÉES ?")) return;

    var toggleStudentUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/toggleStudentAllKits') : (racine + 'accessoire/toggleStudentAllKits');

    $.ajax({
      url: toggleStudentUrl,
      type: 'POST',
      data: {
        inscription_code: inscrCode,
        target_etat: 'retire'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Tous les kits ont été validés avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          table.ajax.reload(null, false);
          refreshKpis();
          $('#modal-emargement-student').hide();
        } else {
          var msgErr = res.message || 'Erreur lors de la validation.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur lors de la connexion au serveur.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  }

  // Chargement et affichage du Bon de Remise A4
  function loadAndOpenBonRemiseModal(inscrCode) {
    var bonUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/getBonRemiseData') : (racine + 'accessoire/getBonRemiseData');
    $.ajax({
      url: bonUrl,
      type: 'GET',
      data: { inscription_code: inscrCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.student) {
          var s = res.student;
          $('#bon-student-name').text((s.nom_etudiant || '') + ' ' + (s.prenom_etudiant || ''));
          $('#bon-student-matricule').text(s.matricule_etudiant || '-');
          $('#bon-student-classe').text(s.libelle_classe || '-');
          $('#bon-student-phone').text(s.telephone_etudiant || '-');
          $('#bon-annee').text(s.libelle_annee ? ('Année Académique ' + s.libelle_annee) : 'GEICG');
          $('#bon-date-emission').text(res.date_emission || '-');
          $('#bon-agent-nom').text(res.agent_nom || 'Administration');

          if (s.nom_etablissement) $('#bon-etab-nom').text(s.nom_etablissement);
          if (s.adresse_etablissement) $('#bon-etab-adresse').text(s.adresse_etablissement || '');
          if (s.telephone_etablissement) $('#bon-etab-tel').text('Tél : ' + s.telephone_etablissement);
          if (s.logo_etablissement) $('#bon-etab-logo').attr('src', racine + s.logo_etablissement).show();

          $('#bon-count-remis').text(res.total_remis || 0);
          $('#bon-count-attente').text(res.total_en_attente || 0);

          var htmlRemis = '';
          if (res.kits_remis && res.kits_remis.length > 0) {
            res.kits_remis.forEach(function(k) {
              htmlRemis += '<tr>' +
                             '<td style="padding:8px;"><strong>' + escapeHtml(k.libelle_accessoire) + '</strong></td>' +
                             '<td style="padding:8px;" class="text-success fw-bold">✓ ' + (k.date_retrait_formatee || 'Remis') + '</td>' +
                             '<td style="padding:8px;"><span class="badge bg-success">Remis & Réceptionné</span></td>' +
                           '</tr>';
            });
          } else {
            htmlRemis = '<tr><td colspan="3" class="text-muted text-center" style="padding:10px;">Aucun article remis pour le moment.</td></tr>';
          }
          $('#bon-table-remis').html(htmlRemis);

          var htmlAtt = '';
          if (res.kits_en_attente && res.kits_en_attente.length > 0) {
            res.kits_en_attente.forEach(function(k) {
              htmlAtt += '<tr>' +
                           '<td style="padding:8px;"><strong>' + escapeHtml(k.libelle_accessoire) + '</strong></td>' +
                           '<td style="padding:8px;">' + (k.date_attribution_formatee || '-') + '</td>' +
                           '<td style="padding:8px;"><span class="badge bg-warning text-dark">⏳ En attente de retrait</span></td>' +
                         '</tr>';
            });
          } else {
            htmlAtt = '<tr><td colspan="3" class="text-success text-center fw-bold" style="padding:10px;">🎉 Complètement remis ! Aucun article en attente.</td></tr>';
          }
          $('#bon-table-attente').html(htmlAtt);

          $('#modal-bon-remise').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          var err = res.message || 'Erreur lors de la génération du bon de remise.';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
        }
      },
      error: function() {
        var msg = 'Erreur de connexion au serveur.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  }

  $('#btn-trigger-print-bon').on('click', function() {
    window.print();
  });

  // Modal Attribution
  $('#btn-open-attrib-modal').on('click', function() {
    $('#modal-attribuer-kit').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-attrib').on('click', function() {
    $('#modal-attribuer-kit').hide();
  });

  $('#form-attribuer-kit').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var attribUrl = (typeof GEICG !== 'undefined' && GEICG.url) ? GEICG.url('accessoire/attribuerKit') : (racine + 'accessoire/attribuerKit');

    $.ajax({
      url: attribUrl,
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Attribution effectuée avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          $('#modal-attribuer-kit').hide();
          table.ajax.reload();
          refreshKpis();
        } else {
          var msgErr = res.message || 'Erreur lors de l\'attribution.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur lors de l\'enregistrement.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  });
});
