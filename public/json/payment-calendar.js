(function() {
  'use strict';

  var LINK = window.LINK || 'http://localhost/kits/';
  var currentClient = null;
  var currentKit = null;
  var currentLigneCode = null;
  var currentCampagne = null;
  var currentSessionCode = '';

  function showCard(id) {
    document.getElementById(id).style.display = 'block';
  }

  function hideCard(id) {
    document.getElementById(id).style.display = 'none';
  }

  function showLoading(id) {
    document.getElementById(id).style.display = 'block';
  }

  function hideLoading(id) {
    document.getElementById(id).style.display = 'none';
  }

  function checkSession() {
    $.get(LINK + 'session/current', function(resp) {
      if (resp.status && resp.data && resp.data.code_session) {
        currentSessionCode = resp.data.code_session;
        updateCloseButton(true);
        initPaymentFlow();
      } else {
        currentSessionCode = '';
        updateCloseButton(false);
        document.getElementById('sessionModal').classList.add('active');
      }
    }, 'json').fail(function() {
      showToast('Erreur vérification session caisse', 'error');
    });
  }

  function updateCloseButton(hasSession) {
    var btn = document.getElementById('closeSessionBtn');
    if (btn) {
      btn.style.display = hasSession ? 'inline-flex' : 'none';
    }
  }

  function openSession() {
    var montant = document.getElementById('sessionMontant').value.trim();
    if (montant === '' || isNaN(montant)) {
      showToast('Veuillez renseigner le montant d\'ouverture', 'warning');
      return;
    }

    var btn = document.getElementById('sessionModalOpen');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Ouverture...';

    $.post(LINK + 'session/open', {
      csrf_token: document.querySelector('#sessionForm [name="csrf_token"]').value,
      montant_ouverture: montant
    }, function(resp) {
      btn.disabled = false;
      btn.innerHTML = 'Ouvrir la session';
      if (resp.status) {
        currentSessionCode = resp.code_session || '';
        document.getElementById('sessionModal').classList.remove('active');
        showToast('Session de caisse ouverte', 'success');
        updateCloseButton(true);
        initPaymentFlow();
      } else {
        showToast(resp.message || 'Erreur ouverture session', 'error');
      }
    }, 'json').fail(function() {
      btn.disabled = false;
      btn.innerHTML = 'Ouvrir la session';
      showToast('Erreur serveur', 'error');
    });
  }

  function openCloseModal() {
    if (!currentSessionCode) {
      showToast('Aucune session ouverte', 'warning');
      return;
    }
    document.getElementById('closeSessionId').value = currentSessionCode;
    document.getElementById('closeMontantReel').value = '';
    document.getElementById('closeEcart').value = '';

    $.get(LINK + 'session/current', function(resp) {
      if (resp.status && resp.data) {
        var attendu = Number(resp.data.montant_attendu || 0);
        document.getElementById('closeMontantAttendu').value = attendu;
        document.getElementById('closeMontantAttendu').dataset.raw = attendu;
      } else {
        document.getElementById('closeMontantAttendu').value = 0;
        document.getElementById('closeMontantAttendu').dataset.raw = 0;
      }
      document.getElementById('closeSessionModal').classList.add('active');
    }, 'json').fail(function() {
      document.getElementById('closeMontantAttendu').value = 0;
      document.getElementById('closeMontantAttendu').dataset.raw = 0;
      document.getElementById('closeSessionModal').classList.add('active');
    });
  }

  function closeSession() {
    var sessionId = document.getElementById('closeSessionId').value;
    var montantReel = document.getElementById('closeMontantReel').value.trim();

    if (!sessionId || montantReel === '' || isNaN(montantReel)) {
      showToast('Veuillez renseigner le montant réel', 'warning');
      return;
    }

    var btn = document.getElementById('closeSessionModalSave');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Fermeture...';

    $.post(LINK + 'session/close/' + encodeURIComponent(sessionId), {
      csrf_token: document.querySelector('#closeSessionForm [name="csrf_token"]').value,
      montant_reel: montantReel
    }, function(resp) {
      btn.disabled = false;
      btn.innerHTML = 'Fermer la session';
      if (resp.status) {
        showToast('Session fermée avec succès', 'success');
        document.getElementById('closeSessionModal').classList.remove('active');
        currentSessionCode = '';
        updateCloseButton(false);
        hideCard('calendarCard');
        hideCard('kitsCard');
        currentLigneCode = null;
        currentClient = null;
        document.getElementById('clientsList').querySelectorAll('.client-card').forEach(function(c) { c.style.borderColor = 'var(--border-color)'; });
        document.getElementById('sessionModal').classList.add('active');
      } else {
        showToast(resp.message || 'Erreur', 'error');
      }
    }, 'json').fail(function() {
      btn.disabled = false;
      btn.innerHTML = 'Fermer la session';
      showToast('Erreur serveur', 'error');
    });
  }

  function initPaymentFlow() {
    loadClients();
    
  }
  function loadClients() {
  
    hideCard('kitsCard');
    hideCard('calendarCard');
    hideLoading('clientsEmpty');
    hideLoading('clientsLoading');
    showLoading('clientsLoading');
    document.getElementById('clientsList').innerHTML = '';

    $.get(LINK + 'paiement/clients', function(resp) {
      console.log('Réponse serveur loadClients:', resp);

      hideLoading('clientsLoading');
      if (resp.status && resp.data && resp.data.length) {
        renderClients(resp.data);
      } else {
        showLoading('clientsEmpty');
      }
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
      console.log('Erreur chargement clients:', textStatus, errorThrown);
      console.log('Réponse serveur:', jqXHR.responseJSON || jqXHR.responseText);

      hideLoading('clientsLoading');
      showToast('Erreur chargement clients', 'error');
    });
  }

  function renderClients(clients) {
    var container = document.getElementById('clientsList');
    if (!container) return;

    var html = clients.map(function(c) {
      return '<div class="client-card" data-code="' + c.code_client + '">' +
        '<div style="font-weight:700;font-size:1.1rem;color:#000;">' + (c.nom_client || 'SANS NOM') + '</div>' +
        '<div style="font-size:0.9rem;color:#333;">' + (c.telephone_client || '') + '</div>' +
        '<div style="font-size:0.85rem;color:#666;">' + (c.quartier_client || '') + '</div>' +
      '</div>';
    }).join('');
    container.innerHTML = html;

    container.querySelectorAll('.client-card').forEach(function(card) {
      card.addEventListener('click', function() {
        currentClient = this.dataset.code;
        container.querySelectorAll('.client-card').forEach(function(c) { c.style.borderColor = 'var(--border-color)'; });
        this.style.borderColor = 'var(--primary-color)';
        sessionStorage.setItem('payment_current_client', currentClient);
        sessionStorage.setItem('payment_flow_ts', Date.now());
        sessionStorage.removeItem('payment_current_ligne');
        loadKits(currentClient);
      });
    });

    var savedClient = sessionStorage.getItem('payment_current_client');
    var ts = parseInt(sessionStorage.getItem('payment_flow_ts') || '0');
    if (savedClient && ts && (Date.now() - ts <= 3600000)) {
      var target = container.querySelector('.client-card[data-code="' + savedClient + '"]');
      if (target) {
        target.click();
      } else {
        sessionStorage.removeItem('payment_current_client');
        sessionStorage.removeItem('payment_current_ligne');
      }
    }
  }

  function loadKits(clientCode) {
    hideCard('calendarCard');
    showCard('kitsCard');
    hideLoading('kitsEmpty');
    hideLoading('kitsLoading');
    showLoading('kitsLoading');
    document.getElementById('kitsList').innerHTML = '';
    document.getElementById('backToClients').style.display = 'inline-flex';

    $.get(LINK + 'paiement/kits/' + encodeURIComponent(clientCode), function(resp) {
      hideLoading('kitsLoading');
      if (resp.status && resp.data && resp.data.length) {
        renderKits(resp.data);

        var savedLigne = sessionStorage.getItem('payment_current_ligne');
        var ts = parseInt(sessionStorage.getItem('payment_flow_ts') || '0');
        if (savedLigne && ts && (Date.now() - ts <= 3600000)) {
          var target = document.querySelector('.kit-card[data-ligne="' + savedLigne + '"]');
          if (target) {
            target.click();
          } else {
            sessionStorage.removeItem('payment_current_ligne');
          }
        }
      } else {
        showLoading('kitsEmpty');
        sessionStorage.removeItem('payment_current_ligne');
      }
    }, 'json').fail(function() {
      hideLoading('kitsLoading');
      showToast('Erreur chargement kits', 'error');
      sessionStorage.removeItem('payment_current_ligne');
    });
  }

  function renderKits(kits) {
    var container = document.getElementById('kitsList');
    var html = kits.map(function(k) {
      var badgeClass = k.statut_ligne === 'solde' ? 'delivered' : (k.statut_ligne === 'partiel' ? 'shipping' : 'pending');
      var nbJours = parseInt(k.nb_jours) || 1;
      var attendu = (parseFloat(k.prix_kit) || 0) * nbJours;
      var totalPaye = parseFloat(k.total_paye) || 0;
      var reste = Math.max(0, attendu - totalPaye);
      return '<div class="kit-card" data-ligne="' + k.code_ligne_commande + '" style="cursor:pointer;padding:14px;border:1px solid var(--border-color);border-radius:var(--radius-md);background:var(--bg-secondary);transition:all 0.2s;">' +
        '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">' +
          '<div style="font-weight:600;font-size:0.95rem;">' + (k.libelle_kit || k.kit_code) + '</div>' +
          '<span class="badge-status ' + badgeClass + '" style="font-size:0.7rem;">' + (k.statut_ligne || '') + '</span>' +
        '</div>' +
        '<div style="font-size:0.8rem;color:#666;">' + (k.code_commande || '') + ' · ' + (k.date_commande || '') + '</div>' +
        '<div style="display:flex;justify-content:space-between;margin-top:8px;font-size:0.85rem;">' +
          '<span>Attendu: <strong>' + Number(attendu).toLocaleString('fr-FR') + ' FCFA</strong></span>' +
          '<span>Payé: <strong>' + Number(totalPaye).toLocaleString('fr-FR') + ' FCFA</strong></span>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;margin-top:4px;font-size:0.85rem;">' +
          '<span style="color:#dc3545;">Reste: <strong>' + Number(reste).toLocaleString('fr-FR') + ' FCFA</strong></span>' +
        '</div>' +
      '</div>';
    }).join('');
    container.innerHTML = html;

    container.querySelectorAll('.kit-card').forEach(function(card) {
      card.addEventListener('click', function() {
        currentLigneCode = this.dataset.ligne;
        container.querySelectorAll('.kit-card').forEach(function(c) { c.style.borderColor = 'var(--border-color)'; });
        this.style.borderColor = 'var(--primary-color)';
        sessionStorage.setItem('payment_current_ligne', currentLigneCode);
        sessionStorage.setItem('payment_flow_ts', Date.now());
        loadCalendar(currentLigneCode);
      });
    });
  }

  function loadCalendar(ligneCode) {
    hideLoading('calendarEmpty');
    hideLoading('calendarLoading');
    showLoading('calendarLoading');
    showCard('calendarCard');
    document.getElementById('backToKits').style.display = 'inline-flex';

    $.get(LINK + 'paiement/calendar/' + encodeURIComponent(ligneCode), function(resp) {
      hideLoading('calendarLoading');
      if (resp.status && resp.days) {
        currentCampagne = resp.campagne;
        currentKit = resp.ligne;
        renderCalendar(resp);
      } else {
        showLoading('calendarEmpty');
      }
    }, 'json').fail(function() {
      hideLoading('calendarLoading');
      showToast('Erreur chargement calendrier', 'error');
    });
  }

  function renderCalendar(data) {
    var info = document.getElementById('calendarInfo');
    if (info && currentKit) {
      info.innerHTML = '<strong>' + (currentKit.libelle_kit || '') + '</strong> — ' +
        (currentKit.code_commande || '') + ' — Client: ' + (currentKit.nom_client || '');
    }

    var grid = document.getElementById('calendarGrid');
    var daysHtml = data.days.map(function(day) {
      var statusClass = '';
      var statusLabel = '';
      if (day.has_payment && day.payment) {
        statusClass = 'paid';
        statusLabel = Number(day.payment.montant_paiement || 0).toLocaleString('fr-FR');
      } else {
        statusClass = 'free';
        statusLabel = '';
      }
      return '<div class="day-slot ' + statusClass + '" data-date="' + day.date + '" data-jour="' + day.jour_num + '" title="Jour ' + day.jour_num + ' — ' + day.date + (statusLabel ? ' — ' + statusLabel + ' FCFA' : '') + '">' +
        '<div class="day-num">J' + day.jour_num + '</div>' +
        '<div class="day-date">' + day.date.slice(8, 10) + '/' + day.date.slice(5, 7) + '</div>' +
        '<div class="day-amount">' + statusLabel + '</div>' +
      '</div>';
    }).join('');
    grid.innerHTML = daysHtml;

    grid.querySelectorAll('.day-slot').forEach(function(slot) {
      slot.addEventListener('click', function() {
        if (this.classList.contains('paid')) {
          showToast('Paiement déjà enregistré pour ce jour', 'warning');
          return;
        }
        payDay(this.dataset.date, this.dataset.jour);
      });
    });
  }

  function payDay(date, jour) {
    if (!currentLigneCode || !currentSessionCode) {
      showToast('Veuillez ouvrir une session de caisse', 'warning');
      return;
    }

    var prixKit = parseFloat(currentKit && currentKit.prix_kit) || 0;
    console.log('payDay ligne=', currentLigneCode, 'prixKit=', prixKit, 'montant=', prixKit);

    var formData = {
      csrf_token: document.querySelector('#paiementForm [name="csrf_token"]').value,
      ligne_commande_code: currentLigneCode,
      montant_paiement: prixKit,
      mode_paiement: 'espece',
      reference_paiement: 'J' + jour,
      observation_paiement: 'Paiement du jour ' + jour + ' (' + date + ')',
      date_paiement: date,
      session_caisse_code: currentSessionCode
    };

    var slot = document.getElementById('calendarGrid').querySelector('.day-slot[data-date="' + date + '"]');
    if (slot) {
      slot.style.opacity = '0.5';
      slot.style.pointerEvents = 'none';
    }

    $.post(LINK + 'paiement/pay', formData, function(resp) {
      if (resp.status) {
        showToast(resp.message || 'Paiement enregistré', 'success');
        loadCalendar(currentLigneCode);
      } else {
        showToast(resp.message || 'Erreur', 'error');
        if (slot) {
          slot.style.opacity = '';
          slot.style.pointerEvents = '';
        }
      }
    }, 'json').fail(function() {
      showToast('Erreur serveur', 'error');
      if (slot) {
        slot.style.opacity = '';
        slot.style.pointerEvents = '';
      }
    });
  }

  function openPaiementModal(date, jour) {
    document.getElementById('payLigneCode').value = currentLigneCode || '';
    document.getElementById('payDate').value = 'Jour ' + jour + ' — ' + date;
    document.getElementById('payMontant').value = parseFloat(currentKit && currentKit.prix_kit) || '';
    document.getElementById('payReference').value = '';
    document.getElementById('payObservation').value = '';
    document.getElementById('payCode').value = '';
    document.getElementById('paiementModal').classList.add('active');
  }

  function closePaiementModal() {
    document.getElementById('paiementModal').classList.remove('active');
  }

  document.getElementById('paiementModalClose').addEventListener('click', closePaiementModal);
  document.getElementById('paiementModalCancel').addEventListener('click', closePaiementModal);
  document.getElementById('paiementModal').addEventListener('click', function(e) {
    if (e.target === this) closePaiementModal();
  });

  document.getElementById('paiementModalSave').addEventListener('click', function() {
    var ligneCode = document.getElementById('payLigneCode').value.trim();
    var montant = document.getElementById('payMontant').value.trim();
    var mode = document.getElementById('payMode').value;
    var ref = document.getElementById('payReference').value.trim();
    var obs = document.getElementById('payObservation').value.trim();

    if (!ligneCode || !montant || !currentSessionCode) {
      showToast('Veuillez renseigner tous les champs obligatoires', 'warning');
      return;
    }

    var formData = {
      csrf_token: document.querySelector('#paiementForm [name="csrf_token"]').value,
      ligne_commande_code: ligneCode,
      montant_paiement: montant,
      mode_paiement: mode,
      reference_paiement: ref,
      observation_paiement: obs,
      session_caisse_code: currentSessionCode
    };

    var btn = document.getElementById('paiementModalSave');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enregistrement...';

    $.post(LINK + 'paiement/pay', formData, function(resp) {
      btn.disabled = false;
      btn.innerHTML = 'Enregistrer';
      if (resp.status) {
        showToast(resp.message || 'Paiement enregistré', 'success');
        closePaiementModal();
        if (currentLigneCode) {
          loadCalendar(currentLigneCode);
        }
      } else {
        showToast(resp.message || 'Erreur', 'error');
      }
    }, 'json').fail(function() {
      btn.disabled = false;
      btn.innerHTML = 'Enregistrer';
      showToast('Erreur serveur', 'error');
    });
  });

  document.getElementById('sessionModalClose').addEventListener('click', function() {
    document.getElementById('sessionModal').classList.remove('active');
  });

  document.getElementById('sessionModalCancel').addEventListener('click', function() {
    document.getElementById('sessionModal').classList.remove('active');
  });

  document.getElementById('sessionModal').addEventListener('click', function(e) {
    if (e.target === this) {
      document.getElementById('sessionModal').classList.remove('active');
    }
  });

  document.getElementById('sessionModalOpen').addEventListener('click', function() {
    openSession();
  });

  document.getElementById('closeSessionBtn').addEventListener('click', function() {
    openCloseModal();
  });

  document.getElementById('closeSessionModalClose').addEventListener('click', function() {
    document.getElementById('closeSessionModal').classList.remove('active');
  });

  document.getElementById('closeSessionModalCancel').addEventListener('click', function() {
    document.getElementById('closeSessionModal').classList.remove('active');
  });

  document.getElementById('closeSessionModal').addEventListener('click', function(e) {
    if (e.target === this) {
      document.getElementById('closeSessionModal').classList.remove('active');
    }
  });

  document.getElementById('closeMontantReel').addEventListener('input', function() {
    var attendu = parseFloat(document.getElementById('closeMontantAttendu').dataset.raw) || 0;
    var reel = parseFloat(this.value) || 0;
    var ecart = reel - attendu;
    document.getElementById('closeEcart').value = Number(ecart).toLocaleString('fr-FR') + ' FCFA';
  });

  document.getElementById('closeSessionModalSave').addEventListener('click', function() {
    closeSession();
  });

  document.getElementById('backToClients').addEventListener('click', function() {
    hideCard('kitsCard');
    hideCard('calendarCard');
    currentClient = null;
    document.getElementById('clientsList').querySelectorAll('.client-card').forEach(function(c) { c.style.borderColor = 'var(--border-color)'; });
  });

  document.getElementById('backToKits').addEventListener('click', function() {
    hideCard('calendarCard');
    currentLigneCode = null;
    document.getElementById('kitsList').querySelectorAll('.kit-card').forEach(function(c) { c.style.borderColor = 'var(--border-color)'; });
  });

  checkSession();
})();
