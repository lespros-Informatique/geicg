(function() {
  'use strict';

  var LINK = window.LINK || 'http://localhost/kits/';
  var currentClient = null;

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

  function loadClients() {
    hideCard('retraitKitsCard');
    hideLoading('retraitClientsEmpty');
    hideLoading('retraitClientsLoading');
    showLoading('retraitClientsLoading');
    document.getElementById('retraitClientsList').innerHTML = '';

    $.get(LINK + 'retrait/clients', function(resp) {
      console.log('Réponse serveur loadClients retrait:', resp);
      hideLoading('retraitClientsLoading');
      if (resp.status && resp.data && resp.data.length) {
        renderClients(resp.data);
      } else {
        showLoading('retraitClientsEmpty');
      }
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
      console.log('Erreur chargement clients retrait:', textStatus, errorThrown);
      hideLoading('retraitClientsLoading');
      showToast('Erreur chargement clients', 'error');
    });
  }

  function renderClients(clients) {
    var container = document.getElementById('retraitClientsList');
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
        loadKits(currentClient);
      });
    });
  }

  function loadKits(clientCode) {
    hideLoading('retraitKitsEmpty');
    hideLoading('retraitKitsLoading');
    showLoading('retraitKitsLoading');
    showCard('retraitKitsCard');
    document.getElementById('retraitKitsList').innerHTML = '';
    document.getElementById('retraitBackToClients').style.display = 'inline-flex';

    $.get(LINK + 'retrait/kits/' + encodeURIComponent(clientCode), function(resp) {
      console.log('Réponse serveur loadKits retrait:', resp);
      hideLoading('retraitKitsLoading');
      if (resp.status && resp.data && resp.data.length) {
        renderKits(resp.data);
      } else {
        showLoading('retraitKitsEmpty');
      }
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
      console.log('Erreur chargement kits retrait:', textStatus, errorThrown);
      hideLoading('retraitKitsLoading');
      showToast('Erreur chargement kits', 'error');
    });
  }

  function renderKits(kits) {
    var container = document.getElementById('retraitKitsList');
    if (!container) return;

    var html = kits.map(function(k) {
      var attendu = parseFloat(k.montant_attendu) || 0;
      var paye = parseFloat(k.total_paye) || 0;
      var reste = attendu - paye;
      var soldeStr = Number(reste).toLocaleString('fr-FR') + ' FCFA';
      var estPaye = attendu > 0 && reste <= 0;
      var estRetire = !!k.a_retrait;

      var btnClass = 'btn-sm';
      var btnStyle = 'width:100%;';
      var btnContent = '';
      var btnDisabled = '';

      if (estRetire) {
        btnClass += ' btn-success';
        btnContent = '<i class="fa fa-check"></i> Retiré';
      } else if (estPaye) {
        btnClass += ' btn-primary';
        btnContent = '<i class="fa fa-exchange-alt"></i> Retirer';
      } else {
        btnClass += ' btn-secondary';
        btnDisabled = 'disabled';
        btnContent = '<i class="fa fa-lock"></i> Solde non réglé';
      }

      return '<div class="kit-card" data-ligne="' + k.code_ligne_commande + '" data-client="' + (k.client_code || '') + '" style="padding:14px;border:1px solid var(--border-color);border-radius:var(--radius-md);background:var(--bg-secondary);transition:all 0.2s;">' +
        '<div style="font-weight:600;font-size:0.95rem;margin-bottom:4px;">' + (k.libelle_kit || k.kit_code) + '</div>' +
        '<div style="font-size:0.8rem;color:#666;margin-bottom:8px;">' + (k.code_commande || '') + ' · ' + (k.date_commande || '') + '</div>' +
        '<div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:4px;">' +
          '<span>Attendu: <strong>' + Number(attendu).toLocaleString('fr-FR') + ' FCFA</strong></span>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:10px;">' +
          '<span>Payé: <strong>' + Number(paye).toLocaleString('fr-FR') + ' FCFA</strong></span>' +
          '<span>Solde: <strong style="color:' + (reste > 0 ? '#dc3545' : '#28a745') + ';">' + soldeStr + '</strong></span>' +
        '</div>' +
        '<button type="button" class="btn ' + btnClass + ' retrait-btn" style="' + btnStyle + '" ' + btnDisabled + '>' +
          btnContent +
        '</button>' +
      '</div>';
    }).join('');
    container.innerHTML = html;

    container.querySelectorAll('.kit-card').forEach(function(card) {
      var btn = card.querySelector('.retrait-btn');
      if (btn && !btn.disabled) {
        btn.addEventListener('click', function(e) {
          e.stopPropagation();
          var ligneCode = card.dataset.ligne;
          var clientCode = card.dataset.client;
          var kitName = card.querySelector('div > div:first-child').textContent.trim();
          var soldeText = card.querySelector('div > div:nth-child(3) strong:last-child').textContent.trim();
          openRetraitModal(ligneCode, clientCode, kitName, soldeText);
        });
      }
    });
  }

  function openRetraitModal(ligneCode, clientCode, kitName, solde) {
    document.getElementById('retraitLigneCode').value = ligneCode;
    document.getElementById('retraitClientCode').value = clientCode;
    document.getElementById('retraitKitName').value = kitName;
    document.getElementById('retraitSolde').value = solde;
    document.getElementById('retraitModal').classList.add('active');
  }

  function closeRetraitModal() {
    document.getElementById('retraitModal').classList.remove('active');
  }

  document.getElementById('retraitModalClose').addEventListener('click', closeRetraitModal);
  document.getElementById('retraitModalCancel').addEventListener('click', closeRetraitModal);
  document.getElementById('retraitModal').addEventListener('click', function(e) {
    if (e.target === this) closeRetraitModal();
  });

  document.getElementById('retraitModalSave').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enregistrement...';

    var formData = {
      csrf_token: document.querySelector('#retraitForm [name="csrf_token"]').value,
      ligne_commande_code: document.getElementById('retraitLigneCode').value,
      client_code: document.getElementById('retraitClientCode').value,
      code: ''
    };

    $.post(LINK + 'retrait/add', formData, function(resp) {
      btn.disabled = false;
      btn.innerHTML = 'Confirmer le retrait';
      if (resp.status) {
        showToast(resp.message || 'Retrait enregistré', 'success');
        closeRetraitModal();
        if (currentClient) {
          loadKits(currentClient);
        }
      } else {
        showToast(resp.message || 'Erreur', 'error');
      }
    }, 'json').fail(function() {
      btn.disabled = false;
      btn.innerHTML = 'Confirmer le retrait';
      showToast('Erreur serveur', 'error');
    });
  });

  document.getElementById('retraitBackToClients').addEventListener('click', function() {
    hideCard('retraitKitsCard');
    currentClient = null;
    document.getElementById('retraitClientsList').querySelectorAll('.client-card').forEach(function(c) { c.style.borderColor = 'var(--border-color)'; });
  });

  loadClients();
})();
