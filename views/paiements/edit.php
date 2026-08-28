<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$db = (new Database())->getCon();
$stmtIns = $db->query("
  SELECT i.code_inscription, i.montant_scolarite_inscription, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, c.libelle_classe 
  FROM inscriptions i 
  LEFT JOIN etudiants e ON i.etudiant_code = e.code_etudiant 
  LEFT JOIN classes c ON i.classe_code = c.code_classe 
  ORDER BY e.nom_etudiant ASC
");
$inscriptionsList = $stmtIns->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
$stmtSessionToday = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
$stmtSessionToday->execute([$today]);
$sessionJour = $stmtSessionToday->fetch(PDO::FETCH_ASSOC);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_paiement']) ? 'Éditer ' : 'Nouveau ' ?> Règlement Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Guichet d'encaissement intelligent des frais de scolarité</p>
        </div>
        <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <!-- BANDEAU D'ÉTAT DE LA SESSION DE CAISSE DU JOUR -->
      <?php if (!empty($sessionJour) && in_array($sessionJour['statut_session'], ['cloturee', 'valide'])): ?>
        <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 20px;">
              <i data-lucide="lock" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <strong style="color: #991B1B; font-size: 14px;">Session de caisse du jour CLÔTURÉE (Réf : <?= htmlspecialchars($sessionJour['code_session']) ?>)</strong>
              <div style="color: #7F1D1D; font-size: 12px; margin-top: 2px;">La session du jour a déjà été arrêtée. Tout nouvel encaissement en espèces est bloqué.</div>
            </div>
          </div>
          <a href="<?= RACINE ?>session_caisse/details/<?= $this->validator->crypter($sessionJour['id_session']) ?>" class="btn btn-sm btn-outline-danger" style="font-weight: 700; border-radius: 6px; font-size: 12px;">
            Voir le PV de Clôture
          </a>
        </div>
      <?php elseif (!empty($sessionJour) && $sessionJour['statut_session'] === 'ouverte'): ?>
        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <strong style="color: #166534; font-size: 13px;">Session de Caisse Active &bull; Ouverte</strong>
              <div style="color: #15803D; font-size: 12px;">Fond initial : <strong><?= number_format((float)$sessionJour['fond_initial'], 0, ',', ' ') ?> FCFA</strong> (Réf : <?= htmlspecialchars($sessionJour['code_session']) ?>)</div>
            </div>
          </div>
          <a href="<?= RACINE ?>session_caisse/cloturer/<?= $this->validator->crypter($sessionJour['id_session']) ?>" class="btn btn-sm btn-outline-success" style="font-weight: 700; border-radius: 6px; font-size: 12px; background:#FFFFFF;">
            <i data-lucide="lock" style="width: 14px; height: 14px;"></i> Clôturer la Session
          </a>
        </div>
      <?php else: ?>
        <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
              <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <strong style="color: #92400E; font-size: 14px;">Attention : Aucune session de caisse n'est OUVERTE pour aujourd'hui</strong>
              <div style="color: #B45309; font-size: 12px; margin-top: 2px;">Pour assurer la traçabilité des espèces et le rapprochement du soir, veuillez démarrer la session de caisse.</div>
            </div>
          </div>
          <a href="<?= RACINE ?>session_caisse/formulaire" class="btn btn-sm btn-warning" style="background: #D97706; border-color: #D97706; color: #FFF; font-weight: 700; border-radius: 6px; font-size: 12px;">
            <i data-lucide="unlock" style="width: 14px; height: 14px;"></i> Ouvrir la Caisse
          </a>
        </div>
      <?php endif; ?>

      <!-- Bande Preview Financière Dynamique (Fiche Synthèse Élève) -->
      <div id="financial-preview-banner" class="card" style="display: none; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(15,23,42,0.06); transition: all 0.3s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          
          <!-- Infos Élève & Matricule -->
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);" id="prev_avatar">ET</div>
            <div>
              <div style="font-weight: 800; color: #0F172A; font-size: 16px;" id="prev_nom">Nom Étudiant</div>
              <div style="font-size: 13px; color: #64748B; margin-top: 2px;">
                Matricule : <code id="prev_matricule" style="font-weight:800; color:#1E3A5F; font-size:13px;">-</code> &bull; 
                Classe : <span id="prev_classe" style="font-weight:700; color:#334155;">-</span>
              </div>
            </div>
          </div>

          <!-- Badges Financiers -->
          <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <div style="text-align: center; padding: 10px 18px; background: #F8FAFC; border-radius: 10px; border: 1px solid #E2E8F0;">
              <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Scolarité Due</div>
              <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 2px;" id="prev_due">0 FCFA</div>
            </div>

            <div style="text-align: center; padding: 10px 18px; background: #F0FDF4; border-radius: 10px; border: 1px solid #BBF7D0;">
              <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Total Déjà Payé</div>
              <div style="font-size: 16px; font-weight: 800; color: #15803D; margin-top: 2px;" id="prev_paye">0 FCFA</div>
            </div>

            <div style="text-align: center; padding: 10px 18px; background: #FEF2F2; border-radius: 10px; border: 1px solid #FCA5A5;">
              <div style="font-size: 11px; font-weight: 800; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">Solde Restant À Payer</div>
              <div style="font-size: 16px; font-weight: 800; color: #DC2626; margin-top: 2px;" id="prev_solde">0 FCFA</div>
            </div>
          </div>

        </div>
      </div>

      <!-- TABLEAU INTERACTIF DES TRANCHES DE L'ÉLÈVE -->
      <div id="student-tranches-card" class="card" style="display: none; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 22px 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15,23,42,0.06);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1.5px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Échéancier des Tranches & Suivi des Règlements
            </h3>
            <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">
              Visualisez les tranches payées et sélectionnez la tranche concernée par cet encaissement.
            </p>
          </div>
          <span id="tranches-count-badge" class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 8px;">
            0 tranche(s)
          </span>
        </div>

        <div style="width: 100%; overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 10px;">
          <table class="table" style="width: 100%; margin: 0; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <th style="padding: 10px 14px;">Tranche</th>
                <th style="padding: 10px 14px; text-align: center;">Échéance</th>
                <th style="padding: 10px 14px; text-align: right;">Montant Exigible</th>
                <th style="padding: 10px 14px; text-align: right;">Déjà Payé</th>
                <th style="padding: 10px 14px; text-align: right;">Reste Dû</th>
                <th style="padding: 10px 14px; text-align: center; width: 160px;">Statut</th>
              </tr>
            </thead>
            <tbody id="student-tranches-tbody">
              <!-- Injecté via AJAX -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>paiement/<?= !empty($item['id_paiement']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;" id="form-paiement">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_paiement'])): ?>
            <input type="hidden" name="id_paiement" value="<?= $item['id_paiement'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i> Saisie du Règlement de Caisse
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Recherche Sélection Élève -->
            <div class="form-group" style="width: 100%; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13.5px; color: #1E3A5F; margin-bottom: 6px;">
                <i data-lucide="search" style="width: 16px; height: 16px; vertical-align: -2px;"></i> Recherche de l'étudiant (par Matricule, Nom ou Prénom) <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="select_inscription_code" name="inscription_code" style="width: 100%;" required>
                <option value="">-- Saisir le matricule ou le nom de l'élève --</option>
                <?php foreach($inscriptionsList as $ins): ?>
                  <?php
                    $mat = $ins['matricule_etudiant'] ?? '-';
                    $nom = trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? ''));
                    $classe = $ins['libelle_classe'] ?? 'Non affecté';
                    $labelOpt = "$mat - $nom ($classe)";
                  ?>
                  <option value="<?= $ins['code_inscription'] ?>" <?= (($item['inscription_code'] ?? '') == $ins['code_inscription']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($labelOpt) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Tranche correspondante (STRICTEMENT OBLIGATOIRE) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #1E3A5F; margin-bottom: 6px;">
                <i data-lucide="calendar" style="width: 15px; height: 15px; vertical-align: -2px;"></i> Tranche correspondante au versement <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control" id="select_tranche_code" name="tranche_code" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; font-weight: 700; border-radius: 8px; border: 1.5px solid #1E3A5F; background: #F8FAFC; color: #0F172A;" required>
                <option value="">-- Sélectionnez d'abord un élève pour charger ses tranches --</option>
              </select>
              <div id="tranche-helper-hint" style="font-size: 12px; color: #64748B; margin-top: 4px; display: none;"></div>
            </div>

            <!-- Montant versé -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Montant versé (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" id="inp_montant_paiement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1; color: #0F172A;" name="montant_paiement" value="<?= htmlspecialchars($item['montant_paiement'] ?? '') ?>" placeholder="Ex: 150000" min="0" step="any" required>
            </div>

            <!-- Mode de paiement -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Mode de règlement <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="mode_paiement" required>
                <option value="espece" <?= (($item['mode_paiement'] ?? '') === 'espece') ? 'selected' : '' ?>>Espèces (Caisse Guichet)</option>
                <option value="mobile_money" <?= (($item['mode_paiement'] ?? '') === 'mobile_money') ? 'selected' : '' ?>>Mobile Money (Wave, Orange, MTN, Moov)</option>
                <option value="cheque" <?= (($item['mode_paiement'] ?? '') === 'cheque') ? 'selected' : '' ?>>Chèque bancaire</option>
                <option value="virement" <?= (($item['mode_paiement'] ?? '') === 'virement') ? 'selected' : '' ?>>Virement bancaire</option>
              </select>
            </div>

            <!-- Intitulé du versement -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Intitulé du versement</label>
              <input type="text" id="inp_type_paiement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="type_paiement" value="<?= htmlspecialchars($item['type_paiement'] ?? 'Règlement Scolarité') ?>" placeholder="Ex: Règlement 1ère Tranche Scolarité">
            </div>

            <!-- Numéro de transaction -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro de transaction</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="reference_paiement" value="<?= htmlspecialchars($item['reference_paiement'] ?? '') ?>" placeholder="Ex: TRX-928374 / BORD-10492">
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; border-radius: 8px; padding: 11px 28px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i> Enregistrer l'Encaissement
            </button>
            <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  if ($.fn.select2) {
    $('#select_inscription_code').select2({
      placeholder: "-- Rechercher l'étudiant par Matricule, Nom ou Prénom --",
      allowClear: true,
      width: '100%'
    });
  }

  var currentTranchesData = [];
  var preSelectedTrancheCode = "<?= htmlspecialchars($item['tranche_code'] ?? '') ?>";

  function formatFcfa(val) {
    return Number(val || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  function renderTranchesTable(tranches, suggestedCode) {
    currentTranchesData = tranches || [];
    var $tbody = $('#student-tranches-tbody');
    var $select = $('#select_tranche_code');

    $tbody.empty();
    $select.empty();

    if (!tranches || tranches.length === 0) {
      $('#student-tranches-card').slideUp(200);
      $select.append('<option value="">Aucune tranche trouvée pour cette classe</option>');
      return;
    }

    $('#tranches-count-badge').text(tranches.length + ' tranche(s)');
    $select.append('<option value="">-- Choisir la tranche à encaisser --</option>');

    var targetSelectCode = preSelectedTrancheCode || suggestedCode || '';

    tranches.forEach(function(tr, idx) {
      var isSelected = (tr.code_tranche === targetSelectCode);
      var rowBg = isSelected ? '#EFF6FF' : '#FFFFFF';
      var rowBorder = isSelected ? '2px solid #3B82F6' : '1px solid #F1F5F9';

      var badgeIcon = tr.is_soldee ? '✓ ' : '';
      var optLabel = tr.libelle_tranche + ' - Total: ' + tr.montant_tranche_fmt + ' (Reste: ' + tr.reste_a_payer_fmt + ') [' + tr.statut_libelle + ']';

      // Option in dropdown
      var $opt = $('<option></option>')
        .val(tr.code_tranche)
        .text(optLabel)
        .attr('data-reste', tr.reste_a_payer)
        .attr('data-total', tr.montant_tranche)
        .attr('data-libelle', tr.libelle_tranche)
        .attr('data-soldee', tr.is_soldee ? '1' : '0');

      if (isSelected) {
        $opt.prop('selected', true);
      }
      $select.append($opt);

      // Table Row
      var rowHtml = '<tr class="tranche-item-row" data-code="' + tr.code_tranche + '" style="background:' + rowBg + '; border-bottom:' + rowBorder + '; cursor:pointer; transition:background 0.2s;">' +
        '<td style="padding:12px 14px;">' +
          '<div style="font-weight:800; color:#0F172A; font-size:13.5px;">' + $('<div>').text(tr.libelle_tranche).html() + '</div>' +
          '<code style="font-size:11px; color:#64748B;">' + tr.code_tranche + '</code>' +
        '</td>' +
        '<td style="padding:12px 14px; text-align:center; font-size:13px; color:#475569;">' +
          tr.date_limite_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:right; font-weight:700; color:#0F172A;">' +
          tr.montant_tranche_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:right; font-weight:700; color:#15803D;">' +
          tr.deja_paye_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:right; font-weight:800; color:' + (tr.reste_a_payer > 0 ? '#DC2626' : '#15803D') + ';">' +
          tr.reste_a_payer_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:center;">' +
          '<span class="badge" style="background:' + tr.badge_bg + '; color:' + tr.badge_color + '; font-weight:700; font-size:12px; padding:6px 12px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; border:1px solid ' + (tr.is_soldee ? '#86EFAC' : (tr.statut_code === 'partiel' ? '#FCD34D' : '#BFDBFE')) + ';">' +
            badgeIcon + tr.statut_libelle +
          '</span>' +
        '</td>' +
      '</tr>';

      $tbody.append(rowHtml);
    });

    $('#student-tranches-card').stop(true, true).slideDown(250);
    if (window.lucide) lucide.createIcons();

    // Trigger selection
    if (targetSelectCode) {
      applySelectedTranche(targetSelectCode);
    }
  }

  function applySelectedTranche(tCode) {
    if (!tCode) {
      $('#tranche-helper-hint').hide();
      return;
    }

    var selectedTr = null;
    currentTranchesData.forEach(function(tr) {
      if (tr.code_tranche === tCode) {
        selectedTr = tr;
      }
    });

    // Update select
    $('#select_tranche_code').val(tCode);

    // Highlight row
    $('#student-tranches-tbody tr').each(function() {
      var rowCode = $(this).data('code');
      if (rowCode === tCode) {
        $(this).css('background', '#EFF6FF');
      } else {
        $(this).css('background', '#FFFFFF');
      }
    });

    if (selectedTr) {
      // Auto set amount to remaining balance if not in manual edit mode
      var currentAmount = parseFloat($('#inp_montant_paiement').val()) || 0;
      if (currentAmount === 0 || !preSelectedTrancheCode) {
        $('#inp_montant_paiement').val(selectedTr.reste_a_payer > 0 ? selectedTr.reste_a_payer : selectedTr.montant_tranche);
      }

      // Auto set wording
      $('#inp_type_paiement').val('Règlement ' + selectedTr.libelle_tranche);

      // Helper hint
      $('#tranche-helper-hint')
        .html('<strong>' + selectedTr.libelle_tranche + '</strong> : Solde restant exigible de <strong>' + selectedTr.reste_a_payer_fmt + '</strong> (Montant total de la tranche : ' + selectedTr.montant_tranche_fmt + ').')
        .show();
    }
  }

  function fetchStudentFinancialSummary(inscriptionCode) {
    if (!inscriptionCode) {
      $('#financial-preview-banner').slideUp(200);
      $('#student-tranches-card').slideUp(200);
      $('#select_tranche_code').empty().append('<option value="">-- Sélectionnez d\'abord un élève --</option>');
      return;
    }

    var requestUrl = '<?= RACINE ?>paiement/getStudentFinancialSummary';

    $.ajax({
      url: requestUrl,
      type: 'GET',
      data: { inscription_code: inscriptionCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          
          // Compute initials
          var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          $('#prev_avatar').text(initials || 'ET');

          $('#prev_nom').text(d.nom_complet);
          $('#prev_matricule').text(d.matricule);
          $('#prev_classe').text(d.classe);
          $('#prev_due').text(d.scolarite_due_fmt);
          $('#prev_paye').text(d.total_paye_fmt);
          $('#prev_solde').text(d.solde_restant_fmt);

          $('#financial-preview-banner').stop(true, true).slideDown(250);

          // Render tranches table & dropdown
          renderTranchesTable(d.tranches, d.suggested_tranche_code);

          if (window.lucide) lucide.createIcons();
        } else {
          $('#financial-preview-banner').slideUp(200);
          $('#student-tranches-card').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement synthèse financière:', err);
        $('#financial-preview-banner').slideUp(200);
        $('#student-tranches-card').slideUp(200);
      }
    });
  }

  $('#select_inscription_code').on('change select2:select', function() {
    var val = $(this).val();
    preSelectedTrancheCode = '';
    fetchStudentFinancialSummary(val);
  });

  $('#select_tranche_code').on('change', function() {
    var tCode = $(this).val();
    applySelectedTranche(tCode);
  });

  $(document).on('click', '.btn-select-tranche-row, .tranche-item-row', function(e) {
    var tCode = $(this).closest('tr').data('code');
    applySelectedTranche(tCode);
  });

  // Auto trigger if initial selected
  var initVal = $('#select_inscription_code').val();
  if (initVal) {
    fetchStudentFinancialSummary(initVal);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
