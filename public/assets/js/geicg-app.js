/**
 * ==========================================================================
 * GEICG - APPLICATION CORE UTILITIES & JS SUITE (geicg-app.js)
 * Phase 4 : Centralisation et Allègement des Assets CSS & JS
 * Helpers mutualisés : Sécurité XSS, Formatage, DataTables, AJAX & Modales
 * ==========================================================================
 */

(function(window, $) {
  'use strict';

  var GEICG = window.GEICG || {};

  // 1. Helper de construction d'URL avec window.RACINE
  GEICG.url = function(endpoint) {
    var base = window.RACINE || '/geicg/';
    if (!endpoint) return base;
    if (endpoint.indexOf('http://') === 0 || endpoint.indexOf('https://') === 0) {
      return endpoint;
    }
    if (!base.endsWith('/')) base += '/';
    return base + endpoint.replace(/^\//, '');
  };

  // 2. Échappement HTML sécurisé anti-XSS
  GEICG.escapeHtml = function(text) {
    if (text === null || text === undefined) return '';
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
  };
  window.escapeHtml = GEICG.escapeHtml;

  // 3. Formatage monétaire standard FCFA
  GEICG.formatMoney = function(amount, suffix) {
    var s = (suffix !== undefined) ? suffix : ' F';
    var num = parseFloat(amount) || 0;
    return new Intl.NumberFormat('fr-FR').format(Math.round(num)) + s;
  };
  window.formatMoney = GEICG.formatMoney;

  // 4. Formatage de date FR (JJ/MM/AAAA)
  GEICG.formatDateFr = function(dateStr, includeTime) {
    if (!dateStr) return '-';
    var parts = dateStr.split(' ');
    var dateParts = parts[0].split('-');
    if (dateParts.length === 3) {
      var formatted = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
      if (includeTime && parts[1]) {
        formatted += ' ' + parts[1].substring(0, 5);
      }
      return formatted;
    }
    return dateStr;
  };
  window.formatDateFr = GEICG.formatDateFr;

  // 5. Traduction officielle française pour jQuery DataTables
  GEICG.dataTableLanguage = {
    processing: "Traitement en cours...",
    search: "Rechercher&nbsp;:",
    lengthMenu: "Afficher _MENU_ éléments",
    info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
    infoEmpty: "Affichage de 0 à 0 sur 0 entrée",
    infoFiltered: "(filtré de _MAX_ entrées au total)",
    infoPostFix: "",
    loadingRecords: "Chargement en cours...",
    zeroRecords: "Aucun élément à afficher",
    emptyTable: "Aucune donnée disponible dans le tableau",
    paginate: {
      first: "Premier",
      previous: "Précédent",
      next: "Suivant",
      last: "Dernier"
    },
    aria: {
      sortAscending: ": activer pour trier la colonne par ordre croissant",
      sortDescending: ": activer pour trier la colonne par ordre décroissant"
    }
  };
  window.geicgDataTableLanguage = GEICG.dataTableLanguage;

  // 6. Renders de colonnes standardisés pour DataTables
  GEICG.render = {
    index: function(meta) {
      var start = (meta && meta.settings && meta.settings._iDisplayStart) ? meta.settings._iDisplayStart : 0;
      var row = (meta && typeof meta.row === 'number') ? meta.row : 0;
      return '<span style="font-weight:700; color:#64748B;">' + (row + 1 + start) + '</span>';
    },
    code: function(data) {
      if (!data) return '<span style="color:#94A3B8;">-</span>';
      return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + GEICG.escapeHtml(data) + '</code>';
    },
    bold: function(data) {
      return '<strong style="color:#0F172A;">' + (data ? GEICG.escapeHtml(data) : '-') + '</strong>';
    },
    money: function(data, suffix) {
      return '<span style="font-weight:700; color:#0F172A;">' + GEICG.formatMoney(data, suffix) + '</span>';
    },
    date: function(data, includeTime) {
      if (!data) return '<span style="color:#94A3B8;">-</span>';
      return '<span style="font-weight:600; color:#334155;">' + GEICG.formatDateFr(data, includeTime) + '</span>';
    },
    badgeStatus: function(status) {
      var s = String(status || '').toLowerCase();
      var isActif = (s === 'actif' || s === 'valide' || s === 'paye' || s === 'termine');
      var color = isActif ? '#15803D' : '#991B1B';
      var bg = isActif ? '#DCFCE7' : '#FEE2E2';
      var border = isActif ? '#BBF7D0' : '#FECACA';
      var label = status || (isActif ? 'Actif' : 'Inactif');
      return '<span class="badge" style="background:' + bg + '; color:' + color + '; border:1px solid ' + border + '; font-weight:700; padding:4px 10px; border-radius:6px; font-size:11px;">' + label + '</span>';
    },
    toggleSwitch: function(id, isChecked, dataUrl, extraClass) {
      var checkedAttr = isChecked ? 'checked' : '';
      var extra = extraClass ? ' ' + extraClass : '';
      var urlAttr = dataUrl ? ' data-url="' + dataUrl + '"' : '';
      return '<div style="display:flex; justify-content:center; align-items:center;">' +
             '<label class="switch" style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;">' +
             '<input type="checkbox" class="toggle-status-ajax' + extra + '" data-id="' + id + '"' + urlAttr + ' ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
             '<span class="slider" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isChecked ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
             '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isChecked ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
             '</span>' +
             '</label>' +
             '</div>';
    }
  };

  // 7. Initialiseur DataTables complet
  GEICG.createDataTable = function(selector, userOptions) {
    if (!$.fn.DataTable) return null;
    var opts = $.extend(true, {
      processing: true,
      autoWidth: false,
      language: GEICG.dataTableLanguage,
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      }
    }, userOptions || {});

    // Support url raccourcie
    if (opts.url && !opts.ajax) {
      opts.ajax = GEICG.url(opts.url);
      delete opts.url;
    } else if (typeof opts.ajax === 'string') {
      opts.ajax = GEICG.url(opts.ajax);
    }

    return $(selector).DataTable(opts);
  };

  // 8. Boîte de confirmation standardisée SweetAlert2
  GEICG.confirm = function(options, onConfirm) {
    if (typeof options === 'string') {
      options = { title: options };
    }
    options = options || {};
    if (!window.Swal) {
      if (window.confirm(options.title || 'Êtes-vous sûr ?')) {
        if (typeof onConfirm === 'function') onConfirm();
      }
      return;
    }

    Swal.fire({
      title: options.title || 'Êtes-vous sûr ?',
      text: options.text || 'Cette action est irréversible.',
      icon: options.icon || 'warning',
      showCancelButton: true,
      confirmButtonColor: options.confirmColor || '#1E3A5F',
      cancelButtonColor: options.cancelColor || '#EF4444',
      confirmButtonText: options.confirmText || 'Oui, confirmer',
      cancelButtonText: options.cancelText || 'Annuler',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed && typeof onConfirm === 'function') {
        onConfirm();
      }
    });
  };

  // 9. Gestionnaire unifié de formulaires AJAX
  GEICG.handleAjaxForm = function(formSelector, btnSelector, options) {
    var form = $(formSelector);
    if (!form.length) return;
    options = options || {};

    form.off('submit.geicg').on('submit.geicg', function(e) {
      e.preventDefault();
      var submitBtn = btnSelector ? $(btnSelector) : form.find(':submit');
      var url = options.url ? GEICG.url(options.url) : (form.attr('action') || window.location.href);
      var method = (form.attr('method') || 'POST').toUpperCase();
      var isFormData = form.attr('enctype') === 'multipart/form-data' || form.find('input[type="file"]').length > 0;
      var data = isFormData ? new FormData(form[0]) : form.serialize();

      if (typeof window.loading === 'function') {
        loading(submitBtn, true, options.loadingMsg || 'Enregistrement...');
      }

      var ajaxConfig = {
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        success: function(res) {
          if (typeof window.loading === 'function') {
            loading(submitBtn, false);
          }
          if (res && (res.status === 'success' || res.success)) {
            var msg = res.message || options.successMsg || 'Opération réussie avec succès !';
            if (typeof window.showToast === 'function') {
              showToast(msg, 'success');
            } else if (window.toastr) {
              toastr.success(msg);
            }

            if (typeof options.onSuccess === 'function') {
              options.onSuccess(res);
            } else if (options.redirect) {
              setTimeout(function() {
                window.location.href = GEICG.url(options.redirect);
              }, options.delay || 700);
            } else if (options.reload !== false) {
              setTimeout(function() {
                window.location.reload();
              }, options.delay || 700);
            }
          } else {
            var errMsg = (res && res.message) ? res.message : (options.errorMsg || 'Une erreur est survenue.');
            if (typeof window.showToast === 'function') {
              showToast(errMsg, 'error');
            } else if (window.toastr) {
              toastr.error(errMsg);
            }
            if (typeof options.onError === 'function') {
              options.onError(res);
            }
          }
        },
        error: function(xhr) {
          if (typeof window.loading === 'function') {
            loading(submitBtn, false);
          }
          var errMsg = options.errorMsg || 'Erreur de connexion au serveur.';
          try {
            var json = JSON.parse(xhr.responseText);
            if (json && json.message) errMsg = json.message;
          } catch(e) {}

          if (typeof window.showToast === 'function') {
            showToast(errMsg, 'error');
          } else if (window.toastr) {
            toastr.error(errMsg);
          }
          if (typeof options.onError === 'function') {
            options.onError(xhr);
          }
        }
      };

      if (isFormData) {
        ajaxConfig.processData = false;
        ajaxConfig.contentType = false;
      }

      $.ajax(ajaxConfig);
    });
  };

  // 10. Handler délégué global pour les toggles de statut actifs/inactifs
  GEICG.bindToggleStatus = function() {
    $(document).off('change.geicgToggle').on('change.geicgToggle', '.toggle-status-ajax', function() {
      var checkbox = $(this);
      var id = checkbox.data('id');
      var url = checkbox.data('url');
      var isChecked = checkbox.is(':checked');
      var statut = isChecked ? 'actif' : 'inactif';

      if (!url) return;
      var targetUrl = GEICG.url(url);

      $.ajax({
        url: targetUrl,
        type: 'POST',
        data: { id: id, statut: statut },
        dataType: 'json',
        success: function(res) {
          if (res && (res.status === 'success' || res.success)) {
            var msg = res.message || 'Statut mis à jour avec succès';
            if (typeof window.showToast === 'function') showToast(msg, 'success');
            else if (window.toastr) toastr.success(msg);
          } else {
            checkbox.prop('checked', !isChecked); // rollback
            var errMsg = (res && res.message) ? res.message : 'Erreur lors de la mise à jour';
            if (typeof window.showToast === 'function') showToast(errMsg, 'error');
            else if (window.toastr) toastr.error(errMsg);
          }
        },
        error: function() {
          checkbox.prop('checked', !isChecked); // rollback
          if (typeof window.showToast === 'function') showToast('Erreur de connexion au serveur', 'error');
          else if (window.toastr) toastr.error('Erreur de connexion au serveur');
        }
      });
    });
  };

  // 12. Gestion complète et mutualisée de la Sidebar & des Accordéons
  GEICG.initSidebar = function() {
    var $sidebar = $('#sidebar, #mainSidebar, .sidebar');
    var $mainContent = $('.main-content');
    var $footer = $('#footer, .footer');

    // Restaurer l'état réduit depuis le localStorage
    try {
      if (localStorage.getItem('geicg_sidebar_collapsed') === '1') {
        $sidebar.addClass('collapsed');
        $mainContent.addClass('expanded');
        $footer.addClass('expanded');
      }
    } catch(e) {}

    // Toggle réduction/déploiement du sidebar
    $(document).off('click.geicgSidebarToggle', '#sidebarToggle').on('click.geicgSidebarToggle', '#sidebarToggle', function(e) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof window.toggleSidebar === 'function') {
        window.toggleSidebar();
      } else {
        $sidebar.toggleClass('collapsed');
        var isCollapsed = $sidebar.hasClass('collapsed');
        $mainContent.toggleClass('expanded', isCollapsed);
        $footer.toggleClass('expanded', isCollapsed);
        try {
          localStorage.setItem('geicg_sidebar_collapsed', isCollapsed ? '1' : '0');
        } catch(ex) {}
        if (window.lucide) {
          lucide.createIcons();
        }
      }
    });

    // Accordéons du sidebar (Comportement accordéon exclusif : fermer les autres modules à l'ouverture)
    $(document).off('click.geicgAccordion', '.sidebar-accordion-toggle').on('click.geicgAccordion', '.sidebar-accordion-toggle', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $toggle = $(this);
      var targetId = $toggle.attr('data-bs-target');
      var $target = $(targetId);

      if ($target.length) {
        var isExpanded = $toggle.attr('aria-expanded') === 'true';
        if (isExpanded) {
          $target.slideUp(200, function() {
            $target.removeClass('show');
          });
          $toggle.attr('aria-expanded', 'false');
        } else {
          // Fermer automatiquement tous les autres accordéons ouverts
          $('.sidebar-accordion-toggle').not($toggle).each(function() {
            var $otherToggle = $(this);
            var otherTargetId = $otherToggle.attr('data-bs-target');
            var $otherTarget = $(otherTargetId);
            if ($otherTarget.length && $otherToggle.attr('aria-expanded') === 'true') {
              $otherTarget.slideUp(200, function() {
                $otherTarget.removeClass('show');
              });
              $otherToggle.attr('aria-expanded', 'false');
            }
          });

          // Ouvrir la section sélectionnée
          $target.slideDown(200, function() {
            $target.addClass('show');
          });
          $toggle.attr('aria-expanded', 'true');
        }
      }
    });

    // Déplier automatiquement la section contenant le lien actif
    var $activeLink = $('.sidebar-nav .nav-item.sub.active');
    if ($activeLink.length) {
      var $parentItems = $activeLink.closest('.nav-section-items');
      if ($parentItems.length) {
        $parentItems.addClass('show').show();
        var $parentToggle = $parentItems.siblings('.sidebar-accordion-toggle');
        if ($parentToggle.length) {
          $parentToggle.attr('aria-expanded', 'true');
        }
      }
    } else {
      var $firstSection = $('.sidebar-nav .nav-section-items').first();
      if ($firstSection.length) {
        $firstSection.addClass('show').show();
        var $firstToggle = $firstSection.siblings('.sidebar-accordion-toggle');
        if ($firstToggle.length) {
          $firstToggle.attr('aria-expanded', 'true');
        }
      }
    }
  };

  // 11. Initialisation automatique UI universelle
  GEICG.initUI = function(context) {
    var $ctx = context ? $(context) : $(document);
    if (window.lucide) {
      lucide.createIcons();
    }
    if ($.fn.select2) {
      $ctx.find('.select2').each(function() {
        if (!$(this).hasClass("select2-hidden-accessible")) {
          $(this).select2({ width: '100%' });
        }
      });
    }
  };

  // Auto-démarrage au chargement du DOM
  $(document).ready(function() {
    GEICG.initUI();
    GEICG.bindToggleStatus();
    GEICG.initSidebar();
  });

  // Rafraîchissement automatique des icônes et select2 à l'ouverture de toute modale Bootstrap
  $(document).on('shown.bs.modal', function(e) {
    GEICG.initUI(e.target);
  });


  window.GEICG = GEICG;

})(window, window.jQuery);
