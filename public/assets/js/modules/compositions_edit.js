$(document).ready(function() {
  var cfg = {};
  try {
    var cfgEl = document.getElementById('compositions-edit-config');
    if (cfgEl) cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {}

  window.ALL_CLASSES = cfg.allClasses || [];
  window.ALL_FILIERES = cfg.allFilieres || [];
  window.ALL_NIVEAUX = cfg.allNiveaux || [];
  window.EDT_ACTIVE_TARGETS = cfg.edtActiveTargets || [];

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // Synchronisation des dates de début et de fin
  $('#date_composition').on('change', function() {
    var startVal = $(this).val();
    var $endInput = $('#date_fin_composition');
    if (startVal) {
      $endInput.attr('min', startVal);
      if ($endInput.val() && $endInput.val() < startVal) {
        $endInput.val(startVal);
      }
    }
  }).trigger('change');

  // Active Niveaux & Filières mapping from emplois_temps
  var edtActiveNiveaux = [];
  var edtActiveFiliereMap = {}; // niveau_code -> array of filiere_codes

  if (window.EDT_ACTIVE_TARGETS && window.EDT_ACTIVE_TARGETS.length > 0) {
    window.EDT_ACTIVE_TARGETS.forEach(function(item) {
      if (item.niveau_code) {
        if (edtActiveNiveaux.indexOf(item.niveau_code) === -1) {
          edtActiveNiveaux.push(item.niveau_code);
        }
        if (!edtActiveFiliereMap[item.niveau_code]) {
          edtActiveFiliereMap[item.niveau_code] = [];
        }
        if (item.filiere_code && edtActiveFiliereMap[item.niveau_code].indexOf(item.filiere_code) === -1) {
          edtActiveFiliereMap[item.niveau_code].push(item.filiere_code);
        }
      }
    });
  }

  function filterFilieresForNiveauCard($card) {
    var nivCode = $card.find('.sel-niveau-item').val();
    var $checkboxGroup = $card.find('.filieres-checkbox-group');

    if (!nivCode) {
      $checkboxGroup.find('label').show();
      $checkboxGroup.find('.chk-filiere-item').prop('disabled', false);
      return;
    }

    if (edtActiveNiveaux.length > 0) {
      var activeFiliereCodes = edtActiveFiliereMap[nivCode] || [];
      $checkboxGroup.find('.chk-filiere-item').each(function() {
        var fCode = $(this).val();
        var $label = $(this).closest('label');
        if (activeFiliereCodes.indexOf(fCode) !== -1) {
          $label.show();
          $(this).prop('disabled', false);
        } else {
          $label.hide();
          $(this).prop('checked', false);
          $(this).prop('disabled', true);
        }
      });
    } else {
      var validFiliereCodes = window.ALL_CLASSES.filter(function(c) {
        return c.niveau_code === nivCode;
      }).map(function(c) { return c.filiere_code; });

      $checkboxGroup.find('.chk-filiere-item').each(function() {
        var fCode = $(this).val();
        var $label = $(this).closest('label');
        if (validFiliereCodes.indexOf(fCode) !== -1 || validFiliereCodes.length === 0) {
          $label.show();
          $(this).prop('disabled', false);
        } else {
          $label.hide();
          $(this).prop('checked', false);
          $(this).prop('disabled', true);
        }
      });
    }
  }

  function updateDisabledNiveauxOptions() {
    var selectedNiveaux = [];
    $('.sel-niveau-item').each(function() {
      var val = $(this).val();
      if (val) selectedNiveaux.push(val);
    });

    $('.sel-niveau-item').each(function() {
      var $select = $(this);
      var currentVal = $select.val();

      $select.find('option').each(function() {
        var optVal = $(this).attr('value');
        if (!optVal) return;

        var isNotInEdt = (edtActiveNiveaux.length > 0 && edtActiveNiveaux.indexOf(optVal) === -1);
        var isAlreadySelected = (optVal !== currentVal && selectedNiveaux.indexOf(optVal) !== -1);

        if (isNotInEdt || isAlreadySelected) {
          $(this).prop('disabled', true);
        } else {
          $(this).prop('disabled', false);
        }
      });
    });
  }

  function renderClassesPreview($card) {
    filterFilieresForNiveauCard($card);

    var nivCode = $card.find('.sel-niveau-item').val();
    var checkedFilieres = [];
    $card.find('.chk-filiere-item:checked:visible').each(function() {
      checkedFilieres.push($(this).val());
    });

    var $previewContainer = $card.find('.preview-badges-list');
    if (!nivCode) {
      $previewContainer.html('<span style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>');
      return;
    }

    if (checkedFilieres.length === 0) {
      $previewContainer.html('<span style="color: #DC2626; font-weight: 700;">Aucune filière configurée dans l\'emploi du temps pour ce niveau.</span>');
      return;
    }

    var matchingClasses = window.ALL_CLASSES.filter(function(c) {
      if (c.niveau_code !== nivCode) return false;
      return checkedFilieres.indexOf(c.filiere_code) !== -1;
    });

    if (matchingClasses.length === 0) {
      $previewContainer.html('<span style="color: #DC2626; font-weight: 600;">Aucune classe configurée dans l\'emploi du temps.</span>');
    } else {
      var html = matchingClasses.map(function(c) {
        return '<span style="background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">' + c.libelle_classe + '</span>';
      }).join(' ');
      $previewContainer.html(html);
    }
  }

  function updateNiveauRowIndices() {
    $('#niveaux-container .niveau-row-card').each(function(idx) {
      $(this).find('.niveau-row-num').text(idx + 1);
      $(this).find('.sel-niveau-item').attr('name', 'niveaux[' + idx + '][niveau_code]');
      $(this).find('.chk-filiere-item').each(function() {
        $(this).attr('name', 'niveaux[' + idx + '][filiere_codes][]');
      });

      if ($('#niveaux-container .niveau-row-card').length > 1) {
        $(this).find('.btn-remove-niveau-row').css('display', 'inline-flex');
      } else {
        $(this).find('.btn-remove-niveau-row').css('display', 'none');
      }
    });
  }

  // Event handlers for dynamic level row
  $(document).on('change', '.sel-niveau-item', function() {
    var $card = $(this).closest('.niveau-row-card');
    updateDisabledNiveauxOptions();
    renderClassesPreview($card);
  });

  $(document).on('change', '.chk-filiere-item', function() {
    var $card = $(this).closest('.niveau-row-card');
    renderClassesPreview($card);
  });

  $(document).on('click', '.btn-toggle-all-filieres', function(e) {
    e.preventDefault();
    var $card = $(this).closest('.niveau-row-card');
    var $chks = $card.find('.chk-filiere-item:visible');
    var allChecked = $chks.filter(':checked').length === $chks.length;
    $chks.prop('checked', !allChecked);
    $(this).text(allChecked ? 'Tout cocher' : 'Tout décocher');
    renderClassesPreview($card);
  });

  $('#btn-add-niveau-row').on('click', function(e) {
    e.preventDefault();
    var idx = $('#niveaux-container .niveau-row-card').length;
    var filieresHtml = window.ALL_FILIERES.map(function(f) {
      return '<label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #334155; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; cursor: pointer; user-select: none;">' +
             '<input type="checkbox" name="niveaux[' + idx + '][filiere_codes][]" value="' + f.code_filiere + '" class="chk-filiere-item" checked style="accent-color: #1E3A5F;"> ' +
             f.libelle_filiere +
             '</label>';
    }).join('');

    var niveauxHtml = window.ALL_NIVEAUX.map(function(n) {
      return '<option value="' + n.code_niveau + '">' + n.libelle_niveau + '</option>';
    }).join('');

    var newCardHtml = `
      <div class="niveau-row-card" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 20px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <span style="font-weight: 800; font-size: 13px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="bookmark" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Groupe Niveau #<span class="niveau-row-num">${idx + 1}</span>
          </span>
          <button type="button" class="btn-remove-niveau-row" style="background: transparent; border: none; color: #EF4444; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Retirer ce niveau
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; align-items: start;">
          <div>
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px;">
              Niveau d'Études <span style="color: #EF4444;">*</span>
            </label>
            <select class="form-control sel-niveau-item" name="niveaux[${idx}][niveau_code]" required style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700;">
              <option value="">-- Choisir un niveau --</option>
              ${niveauxHtml}
            </select>
          </div>

          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label style="font-weight: 700; font-size: 12px; color: #334155; margin: 0;">
                Filières Concernées <span style="font-size: 11px; color: #15803D; font-weight: 600;">(Emploi du temps)</span>
              </label>
              <span style="font-size: 11px;">
                <a href="#" class="btn-toggle-all-filieres" style="color: #1D4ED8; font-weight: 700; text-decoration: none;">Tout cocher</a>
              </span>
            </div>
            <div class="filieres-checkbox-group" style="display: flex; flex-wrap: wrap; gap: 8px; background: #FFFFFF; padding: 10px; border-radius: 8px; border: 1px solid #CBD5E1; max-height: 120px; overflow-y: auto;">
              ${filieresHtml}
            </div>
          </div>
        </div>

        <div class="classes-preview-box" style="margin-top: 12px; font-size: 12px; color: #475569; display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
          <span style="font-weight: 700; color: #0F172A;"><i data-lucide="check-circle" style="width: 14px; height: 14px; color: #16A34A; vertical-align: text-bottom;"></i> Classes concernées :</span>
          <span class="preview-badges-list" style="font-style: italic; color: #94A3B8;">Sélectionnez un niveau pour voir les classes impactées.</span>
        </div>
      </div>
    `;

    $('#niveaux-container').append(newCardHtml);
    if (window.lucide) lucide.createIcons();
    updateNiveauRowIndices();
    updateDisabledNiveauxOptions();
    var $newCard = $('#niveaux-container .niveau-row-card').last();
    renderClassesPreview($newCard);
  });

  $(document).on('click', '.btn-remove-niveau-row', function(e) {
    e.preventDefault();
    if ($('#niveaux-container .niveau-row-card').length > 1) {
      $(this).closest('.niveau-row-card').remove();
      updateNiveauRowIndices();
      updateDisabledNiveauxOptions();
      $('#niveaux-container .niveau-row-card').each(function() {
        renderClassesPreview($(this));
      });
    }
  });

  // Initial setup on page load
  updateDisabledNiveauxOptions();
  $('#niveaux-container .niveau-row-card').each(function() {
    renderClassesPreview($(this));
  });

  // Soumission AJAX du formulaire de programmation de composition
  $('#form-composition').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-composition');
    var originalHtml = $btn.html();

    // Validation préalable : au moins un niveau et une filière cochée
    var hasValidTarget = false;
    $('#niveaux-container .niveau-row-card').each(function() {
      var niv = $(this).find('.sel-niveau-item').val();
      var checkedFil = $(this).find('.chk-filiere-item:checked:visible').length;
      if (!checkedFil) {
        checkedFil = $(this).find('.chk-filiere-item:checked').length;
      }
      if (niv && checkedFil > 0) {
        hasValidTarget = true;
      }
    });

    if (!hasValidTarget) {
      var warningMsg = 'Veuillez sélectionner au moins un niveau et cocher au moins une filière associée.';
      if (window.toastr) {
        toastr.warning(warningMsg);
      } else if (window.Swal) {
        Swal.fire({ icon: 'warning', title: 'Attention', text: warningMsg });
      } else {
        alert(warningMsg);
      }
      return false;
    }

    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;display:inline-block;animation:spin 1s linear infinite;"></i> Enregistrement en cours...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) {
            toastr.success(res.message || 'Composition / Examen programmé avec succès !');
          } else if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Succès',
              text: res.message || 'Composition / Examen programmé avec succès !',
              timer: 1500,
              showConfirmButton: false
            });
          }
          var targetUrl = res.redirect || (window.RACINE + 'composition/list');
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          var errMsg = res.message || 'Erreur lors de la programmation de la composition.';
          if (window.toastr) {
            toastr.error(errMsg);
          } else if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Erreur', text: errMsg });
          } else {
            alert(errMsg);
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        if (window.toastr) {
          toastr.error(msg);
        } else if (window.Swal) {
          Swal.fire({ icon: 'error', title: 'Erreur Serveur', text: msg });
        } else {
          alert(msg);
        }
      }
    });
  });
});

