<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
$stats = $stats ?? ['nb_etudiants' => 0, 'total_du' => 0, 'total_recouvre' => 0, 'total_solde' => 0, 'taux_recouvrement' => 0];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="history" style="width: 26px; height: 26px; color: #DC2626;"></i> Arriérés des Années Antérieures
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Suivi du recouvrement et apurement des scolarités restant dues des exercices précédents</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btn-refresh-data" class="btn btn-outline-secondary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Actualiser
          </button>
        </div>
      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Année Origine de la Dette</label>
            <select id="filter-annee-origine" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années antérieures --</option>
              <?php foreach ($annees as $a): ?>
                <?php if ($a['code_annee'] !== $selectedAnneeCode): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>"><?= htmlspecialchars($a['libelle_annee']) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Classe Actuelle</label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Cartes KPI Syntheses Financieres -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Solde Arrière Dû -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FECACA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #991B1B; text-transform: uppercase;">Total Arriérés Dus</div>
            <div style="font-size: 20px; font-weight: 900; color: #991B1B; margin-top: 2px;" id="kpi-total-solde"><?= number_format($stats['total_solde'] ?? 0, 0, ',', ' ') ?> FCFA</div>
          </div>
        </div>

        <!-- Étudiants Débiteurs -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FED7AA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #C2410C; text-transform: uppercase;">Étudiants Débiteurs</div>
            <div style="font-size: 22px; font-weight: 900; color: #9A3412; margin-top: 2px;" id="kpi-nb-etudiants"><?= $stats['nb_etudiants'] ?? 0 ?></div>
          </div>
        </div>

        <!-- Total Recouvré -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase;">Total Recouvré</div>
            <div style="font-size: 20px; font-weight: 900; color: #14532D; margin-top: 2px;" id="kpi-total-recouvre"><?= number_format($stats['total_recouvre'] ?? 0, 0, ',', ' ') ?> FCFA</div>
          </div>
        </div>

        <!-- Taux de Recouvrement -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BAE6FD; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0F9FF; color: #0284C7; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0369A1; text-transform: uppercase;">Taux de Recouvrement</div>
            <div style="font-size: 22px; font-weight: 900; color: #075985; margin-top: 2px;" id="kpi-taux"><?= $stats['taux_recouvrement'] ?? 0 ?>%</div>
          </div>
        </div>

      </div>

      <!-- Registre DataTables des Arriérés -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-arrieres" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Étudiant</th>
                <th style="padding: 12px;">Matricule & Classe</th>
                <th style="padding: 12px;">Détails Année(s) d'Origine</th>
                <th style="padding: 12px; text-align: right;">Total Scolarité Initial</th>
                <th style="padding: 12px; text-align: right;">Total Réglé</th>
                <th style="padding: 12px; text-align: right;">Solde Arriéré Dû</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : ENCAISSEMENT DES ARRIÉRÉS DE SCOLARITÉ                 -->
<!-- ========================================================================= -->
<div id="modal-encaisser-arriere" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: #15803D; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="banknote" style="width: 22px; height: 22px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Règlement d'Arriéré de Scolarité</h3>
          <div id="modal-student-info" style="font-size: 12px; color: #DCFCE7; margin-top: 2px;">Étudiant / Matricule</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-arriere" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <!-- Corps du modal (Formulaire) -->
    <form id="form-reglement-arriere" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
      <div style="padding: 24px; background: #F8FAFC; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
        
        <!-- Sélection de l'année / inscription à apurer -->
        <div>
          <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">1. Sélectionner l'inscription / année à apurer <span class="text-danger">*</span></label>
          <div id="container-inscriptions-arrieres" style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Rempli dynamiquement en JS -->
          </div>
        </div>

        <!-- Détails du versement -->
        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div style="grid-column: span 2;">
            <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">2. Montant Versé (FCFA) <span class="text-danger">*</span></label>
            <div style="position: relative;">
              <input type="number" step="1000" min="1000" id="input-montant-arriere" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 20px; color: #15803D; padding-right: 70px; border-radius: 10px; background-color: #F1F5F9; cursor: not-allowed;" placeholder="Ex: 50000" required readonly>
              <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #64748B; font-size: 14px;">FCFA</span>
            </div>
            <div id="help-solde-max" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">Solde restant disponible sur l'année sélectionnée : <strong id="lbl-solde-dispo">0 FCFA</strong></div>
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Mode de Règlement <span class="text-danger">*</span></label>
            <select name="mode_paiement" class="form-control" style="border-radius: 8px; font-weight: 700;" required>
              <option value="Espèces" selected>💵 Espèces</option>
              <option value="Chèque & Banque">🏦 Chèque & Banque</option>
              <option value="Mobile Money">📱 Mobile Money</option>
              <option value="Virement Bancaire">🏛️ Virement Bancaire</option>
            </select>
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">N° Référence / Chèque / Trans.</label>
            <input type="text" name="reference_paiement" class="form-control" style="border-radius: 8px;" placeholder="Ex: CHQ-8849 / TRX-9921">
          </div>

          <div style="grid-column: span 2;">
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Observations / Notes de caisse</label>
            <input type="text" name="observations" class="form-control" style="border-radius: 8px;" placeholder="Remarques facultatives...">
          </div>
        </div>

      </div>

      <!-- Pied de modal -->
      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn btn-secondary btn-close-modal-arriere" style="font-weight: 700; border-radius: 8px; padding: 9px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-reglement-arriere" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 9px 24px; background: #15803D; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
          <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : REÇU DE RÈGLEMENT D'ARRIÉRÉ SCOLARITÉ                   -->
<!-- ========================================================================= -->
<div id="modal-recu-arriere" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 650px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
    
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 20px; height: 20px;"></i> Reçu de Règlement d'Arriéré
      </h3>
      <button type="button" class="btn-close-modal-recu" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div id="printable-recu-arriere" style="padding: 24px; background: #FFFFFF; overflow-y: auto; flex: 1; font-family: sans-serif;">
      <!-- Rempli dynamiquement en JS -->
    </div>

    <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
      <button type="button" class="btn btn-secondary btn-close-modal-recu" style="font-weight: 700; border-radius: 8px; padding: 9px 20px;">Fermer</button>
      <button type="button" id="btn-print-recu-action" class="btn btn-primary" style="font-weight: 800; border-radius: 8px; padding: 9px 22px; background: #1E3A5F; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu
      </button>
    </div>

  </div>
</div>

<script>
function formatMoney(amount) {
  return new Intl.NumberFormat('fr-FR').format(amount || 0) + ' FCFA';
}

function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

$(document).ready(function() {
  var table;
  var currentStudentData = null;

  if ($.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  if ($.fn.select2) {
    $('#filter-annee-origine, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Filtrage en cascade Niveau -> Classe
  $('#filter-niveau').on('change', function() {
    var niveauCode = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!niveauCode || !optNiveau || optNiveau === niveauCode || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    $('#filter-classe').trigger('change.select2');
  });

  function initTable() {
    table = $('#table-arrieres').DataTable({
      ajax: {
        url: '<?= RACINE ?>arriere/apiList',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_origine_code = $('#filter-annee-origine').val();
          d.classe_code = $('#filter-classe').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement des arriérés:", error, thrown);
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
              var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : ('<?= RACINE ?>' + photoPath.replace(/^\//, ''));
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
                     '<div style="font-weight: 700; color: #1E3A5F; font-size: 12px; margin-top: 3px;">' + escapeHtml(d.classe_actuelle || 'Non assignée') + '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            var items = d.items || [];
            if (items.length === 0) return '<span class="text-muted" style="font-size: 12px;">Aucun détail</span>';

            var html = '<div style="display: flex; flex-direction: column; gap: 4px;">';
            items.forEach(function(it) {
              html += '<div style="font-size: 11.5px; font-weight: 700;">' +
                        '<span class="badge bg-danger" style="padding: 3px 6px; font-size: 10.5px;">' + escapeHtml(it.libelle_annee) + '</span> ' +
                        '<span style="color: #475569;">' + escapeHtml(it.libelle_classe) + '</span> : ' +
                        '<strong style="color: #DC2626;">' + formatMoney(it.solde_restant) + '</strong>' +
                      '</div>';
            });
            html += '</div>';
            return html;
          }
        },
        {
          data: 'total_initial_du',
          className: 'text-end',
          render: function(d) {
            return '<span style="font-weight: 700; color: #475569; font-size: 13px;">' + formatMoney(d) + '</span>';
          }
        },
        {
          data: 'total_deja_paye',
          className: 'text-end',
          render: function(d) {
            return '<span style="font-weight: 700; color: #16A34A; font-size: 13px;">' + formatMoney(d) + '</span>';
          }
        },
        {
          data: 'total_solde_arriere',
          className: 'text-end',
          render: function(d) {
            return '<span class="badge bg-danger text-white" style="font-size: 13px; font-weight: 800; padding: 6px 12px; border-radius: 8px;">' +
                     formatMoney(d) +
                   '</span>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function(d) {
            var totalRegle = parseFloat(d.total_deja_paye) || 0;
            var isZeroRegle = (totalRegle <= 0);

            if (isZeroRegle) {
              return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                       '<button type="button" class="btn btn-sm" disabled style="font-weight: 700; border-radius: 8px; padding: 6px 12px; background: #E2E8F0; color: #94A3B8; border: 1px solid #CBD5E1; cursor: not-allowed; opacity: 0.65;" title="Total réglé égal à 0 : Encaissement désactivé">' +
                         '<i data-lucide="ban" style="width: 14px; height: 14px; margin-right: 4px;"></i> Encaisser Arriéré' +
                       '</button>' +
                     '</div>';
            }

            return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                     '<button type="button" class="btn btn-sm btn-success btn-open-reglement" style="font-weight: 700; border-radius: 8px; padding: 6px 12px; background: #15803D; border: none;" title="Encaisser les arriérés de cet étudiant">' +
                       '<i data-lucide="banknote" style="width: 14px; height: 14px; margin-right: 4px;"></i> Encaisser Arriéré' +
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
  $('#filter-annee-origine, #filter-classe').on('change', function() {
    table.ajax.reload();
    refreshKpis();
  });

  $('#btn-refresh-data').on('click', function() {
    table.ajax.reload();
    refreshKpis();
  });

  function refreshKpis() {
    $.ajax({
      url: '<?= RACINE ?>arriere/apiStats',
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        var s = res.stats || {};
        $('#kpi-total-solde').text(formatMoney(s.total_solde || 0));
        $('#kpi-nb-etudiants').text(s.nb_etudiants || 0);
        $('#kpi-total-recouvre').text(formatMoney(s.total_recouvre || 0));
        $('#kpi-taux').text((s.taux_recouvrement || 0) + '%');
      }
    });
  }

  // Ouvrir le modal d'encaissement d'arriéré
  $('#table-arrieres').on('click', '.btn-open-reglement', function() {
    var rowData = table.row($(this).closest('tr')).data();
    if (!rowData || (parseFloat(rowData.total_deja_paye) || 0) <= 0) {
      return;
    }
    currentStudentData = rowData;
    openReglementModal(rowData);
  });

  function openReglementModal(d) {
    $('#modal-student-info').text(d.nom_complet + ' | Matricule : ' + d.matricule_etudiant + ' | Classe : ' + d.classe_actuelle);
    
    var $container = $('#container-inscriptions-arrieres');
    $container.empty();

    var items = d.items || [];
    if (items.length === 0) {
      $container.html('<div class="alert alert-info">Aucun arriéré restant sur cet étudiant.</div>');
    } else {
      items.forEach(function(it, idx) {
        var isChecked = (idx === 0) ? 'checked' : '';
        var html = '<label style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: all 0.2s;" class="item-arriere-option">' +
                     '<div style="display: flex; align-items: center; gap: 12px;">' +
                       '<input type="radio" name="inscription_code" value="' + escapeHtml(it.inscription_code) + '" data-solde="' + it.solde_restant + '" ' + isChecked + ' style="width: 18px; height: 18px; accent-color: #15803D;">' +
                       '<div>' +
                         '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">Année Académique : ' + escapeHtml(it.libelle_annee) + ' (' + escapeHtml(it.libelle_classe) + ')</div>' +
                         '<div style="font-size: 11.5px; color: #64748B;">Scolarité Initiale : ' + formatMoney(it.montant_initial) + ' | Déjà payé : ' + formatMoney(it.total_paye) + '</div>' +
                       '</div>' +
                     '</div>' +
                     '<div style="text-align: right;">' +
                       '<span class="badge bg-danger text-white" style="font-size: 12.5px; font-weight: 800; padding: 5px 10px;">Solde : ' + formatMoney(it.solde_restant) + '</span>' +
                     '</div>' +
                   '</label>';
        $container.append(html);
      });
    }

    updateSoldeDispoHelp();
    $('#modal-encaisser-arriere').css('display', 'flex');
  }

  $(document).on('change', 'input[name="inscription_code"]', function() {
    updateSoldeDispoHelp();
  });

  function updateSoldeDispoHelp() {
    var $sel = $('input[name="inscription_code"]:checked');
    if ($sel.length > 0) {
      var solde = parseFloat($sel.data('solde')) || 0;
      $('#lbl-solde-dispo').text(formatMoney(solde));
      $('#input-montant-arriere').attr('max', solde);
      $('#input-montant-arriere').val(solde);
    } else {
      $('#lbl-solde-dispo').text('0 FCFA');
      $('#input-montant-arriere').val('');
    }
  }

  // Fermer les modals
  $('.btn-close-modal-arriere').on('click', function() {
    $('#modal-encaisser-arriere').hide();
  });
  $('.btn-close-modal-recu').on('click', function() {
    $('#modal-recu-arriere').hide();
  });

  // Soumission du formulaire de règlement
  $('#form-reglement-arriere').on('submit', function(e) {
    e.preventDefault();
    
    var $btn = $('#btn-submit-reglement-arriere');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span> Traitement...');

    $.ajax({
      url: '<?= RACINE ?>arriere/enregistrerReglement',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1) {
          $('#modal-encaisser-arriere').hide();
          table.ajax.reload();
          refreshKpis();

          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Règlement Réussi !',
              text: res.message,
              confirmButtonColor: '#15803D'
            });
          }

          // Afficher le reçu d'apurement
          showRecuModal(res, currentStudentData);
        } else {
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: res.message });
          else alert(res.message);
        }
      },
      error: function(xhr, status, error) {
        $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
        if (window.lucide) lucide.createIcons();
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur Serveur', text: 'Une erreur est survenue lors de l\'enregistrement.' });
      }
    });
  });

  function showRecuModal(res, studentData) {
    var codePaiement = res.code_paiement || 'PAI-ARR-000';
    var montantPaye = formatMoney(res.montant || 0);
    var soldeApres = formatMoney(res.solde_apres || 0);
    var dateFr = new Date().toLocaleDateString('fr-FR') + ' à ' + new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});

    var html = '<div style="border: 2px dashed #CBD5E1; padding: 24px; border-radius: 12px; background: #FFFFFF;">' +
                 '<div style="text-align: center; border-bottom: 2px solid #1E3A5F; padding-bottom: 14px; margin-bottom: 18px;">' +
                   '<h2 style="font-size: 18px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">REÇU DE RÈGLEMENT D\'ARRIÉRÉ</h2>' +
                   '<div style="font-size: 12px; color: #64748B; margin-top: 4px;">Grand Établissement d\'Enseignement Supérieur (GEICG)</div>' +
                   '<div style="font-size: 11px; font-weight: 700; color: #0284C7; margin-top: 4px;">N° REÇU : ' + escapeHtml(codePaiement) + ' | Date : ' + dateFr + '</div>' +
                 '</div>' +
                 
                 '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; font-size: 13px;">' +
                   '<div><strong>Étudiant :</strong> ' + escapeHtml(studentData ? studentData.nom_complet : '-') + '</div>' +
                   '<div><strong>Matricule :</strong> ' + escapeHtml(studentData ? studentData.matricule_etudiant : '-') + '</div>' +
                   '<div><strong>Classe Actuelle :</strong> ' + escapeHtml(studentData ? studentData.classe_actuelle : '-') + '</div>' +
                   '<div><strong>Type d\'Encaissement :</strong> Règlement d\'Arriéré Antérieur</div>' +
                 '</div>' +

                 '<div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: 10px; padding: 16px; margin-bottom: 20px; text-align: center;">' +
                   '<div style="font-size: 12px; font-weight: 800; color: #166534; text-transform: uppercase;">MONTANT ENCAISSÉ SUR ARRIÉRÉ</div>' +
                   '<div style="font-size: 26px; font-weight: 900; color: #15803D; margin-top: 4px;">' + montantPaye + '</div>' +
                   '<div style="font-size: 12px; font-weight: 700; color: #475569; margin-top: 6px;">Solde Arriéré Restant Après Règlement : <span style="color:#DC2626;">' + soldeApres + '</span></div>' +
                 '</div>' +

                 '<div style="display: flex; justify-content: space-between; margin-top: 30px; padding-top: 16px; border-top: 1px solid #E2E8F0; font-size: 11px; text-align: center;">' +
                   '<div style="width: 200px;">' +
                     '<strong>L\'Étudiant / Le Tuteur</strong>' +
                     '<div style="height: 45px;"></div>' +
                     '<div style="color:#94A3B8;">Signature</div>' +
                   '</div>' +
                   '<div style="width: 200px;">' +
                     '<strong>Le Caissier / Agent Comptable</strong>' +
                     '<div style="height: 45px;"></div>' +
                     '<div style="color:#94A3B8;">Cachet & Signature</div>' +
                   '</div>' +
                 '</div>' +
               '</div>';

    $('#printable-recu-arriere').html(html);
    $('#modal-recu-arriere').css('display', 'flex');
  }

  $('#btn-print-recu-action').on('click', function() {
    var content = $('#printable-recu-arriere').html();
    var win = window.open('', '', 'width=800,height=600');
    win.document.write('<html><head><title>Imprimer Reçu d\'Arriéré</title>');
    win.document.write('<style>body{font-family:sans-serif; padding:20px;}</style></head><body>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    win.print();
    win.close();
  });

});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
