<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$session = $session ?? [];
$financials = $financials ?? ['total_especes' => 0, 'total_mobile_money' => 0, 'total_cheque_virement' => 0, 'total_general' => 0, 'nb_encaissements' => 0];

$fondInitial = (float)($session['fond_initial'] ?? 0);
$totalEspeces = (float)($financials['total_especes'] ?? 0);
$totalMobile = (float)($financials['total_mobile_money'] ?? 0);
$totalCheque = (float)($financials['total_cheque_virement'] ?? 0);
$totalGeneral = (float)($financials['total_general'] ?? 0);
$soldeTheorique = $fondInitial + $totalGeneral;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Arrêté & Clôture de Session de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Comptage physique des fonds, rapprochement théorique et clôture de journée</p>
        </div>
        <a href="<?= RACINE ?>session_caisse/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux sessions
        </a>
      </div>

      <!-- Informations sur la Session en cours -->
      <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 12px; padding: 18px 22px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
          <div style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Session Concernée</div>
          <div style="font-size: 18px; font-weight: 900; color: #581C87; margin-top: 2px;">
            <?= htmlspecialchars($session['code_session'] ?? '-') ?> &bull; <span style="font-size: 14px; font-weight: 700; color: #64748B;"><?= date('d/m/Y', strtotime($session['date_session'] ?? date('Y-m-d'))) ?></span>
          </div>
          <div style="font-size: 12px; color: #475569; margin-top: 2px;">
            Ouverte à <?= htmlspecialchars($session['heure_ouverture'] ?? '--:--') ?> par <strong><?= htmlspecialchars($session['caissier_nom'] ?? 'Caissier') ?></strong>
          </div>
        </div>
        <div style="text-align: right;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Fond Initial d'Ouverture</div>
          <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= number_format($fondInitial, 0, ',', ' ') ?> FCFA</div>
        </div>
      </div>

      <!-- Récapitulatif Théorique Calculé Automatiquement -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div class="card" style="background: #FFFFFF; border-radius: 10px; padding: 16px; border: 1px solid #E2E8F0;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Espèces</span>
          <div style="font-size: 20px; font-weight: 800; color: #166534; margin-top: 4px;"><?= number_format($totalEspeces, 0, ',', ' ') ?> FCFA</div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 10px; padding: 16px; border: 1px solid #E2E8F0;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Mobile Money</span>
          <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($totalMobile, 0, ',', ' ') ?> FCFA</div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 10px; padding: 16px; border: 1px solid #E2E8F0;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Chèques & Virements</span>
          <div style="font-size: 20px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= number_format($totalCheque, 0, ',', ' ') ?> FCFA</div>
        </div>

        <div class="card" style="background: #EFF6FF; border-radius: 10px; padding: 16px; border: 1px solid #BFDBFE;">
          <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Solde Théorique Attendu</span>
          <div style="font-size: 22px; font-weight: 900; color: #1E3A5F; margin-top: 4px;"><?= number_format($soldeTheorique, 0, ',', ' ') ?> FCFA</div>
          <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Fond initial + Encaissements</div>
        </div>

      </div>

      <!-- Formulaire de Clôture & Rapprochement -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>session_caisse/saveCloture" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_session" value="<?= $session['id_session'] ?>">

          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 18px 0; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
            <i data-lucide="calculator" style="width: 16px; height: 16px; vertical-align: -2px;"></i> Saisie du Comptage Réel & Validation
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Total Espèces Encaissé -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Espèces (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" min="0" step="any" id="inp_total_especes" class="form-control calc-field" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1;" name="total_especes" value="<?= $totalEspeces ?>" required>
            </div>

            <!-- Total Mobile Money -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Mobile Money (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" min="0" step="any" id="inp_total_mobile" class="form-control calc-field" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1;" name="total_mobile_money" value="<?= $totalMobile ?>" required>
            </div>

            <!-- Total Chèques & Virements -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Chèques / Virements (FCFA)</label>
              <input type="number" min="0" step="any" id="inp_total_cheque" class="form-control calc-field" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1;" name="total_cheque_virement" value="<?= $totalCheque ?>">
            </div>

            <!-- Montant Total Physique Compté -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #1E3A5F; margin-bottom: 6px;">
                <i data-lucide="check-circle" style="width: 15px; height: 15px; vertical-align: -2px;"></i> Montant Total Physique Compté (FCFA) <span style="color: #EF4444;">*</span>
              </label>
              <input type="number" min="0" step="any" id="inp_montant_physique" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 16px; font-weight: 900; border-radius: 8px; border: 2px solid #1E3A5F; color: #1E3A5F; background: #F8FAFC;" name="montant_physique_compte" value="<?= $soldeTheorique ?>" required>
            </div>

          </div>

          <!-- Panneau d'Écart en Direct -->
          <div id="panel-ecart" style="margin-top: 20px; padding: 14px 20px; border-radius: 10px; background: #F0FDF4; border: 1px solid #BBF7D0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Écart Constaté (Physique - Théorique)</span>
              <div id="ecart-val-text" style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 2px;">0 FCFA (Caisse Équilibrée)</div>
            </div>
            <div id="ecart-badge" style="font-weight: 700; font-size: 12px; background: #DCFCE7; color: #15803D; padding: 6px 12px; border-radius: 20px;">
              Parfaitement Conforme
            </div>
          </div>

          <!-- Observations Clôture -->
          <div class="form-group" style="width: 100%; box-sizing: border-box; margin-top: 20px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Note / Justification / Observations de clôture</label>
            <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="observations_cloture" placeholder="Ex: Caisse arrêtée et comptée sans écart." rows="3"></textarea>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; border-radius: 8px; padding: 11px 28px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Enregistrer & Clôturer Définitivement
            </button>
            <a href="<?= RACINE ?>session_caisse/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  var fondInitial = <?= $fondInitial ?>;

  function recalculateEcart() {
    var esp = parseFloat($('#inp_total_especes').val()) || 0;
    var mob = parseFloat($('#inp_total_mobile').val()) || 0;
    var chq = parseFloat($('#inp_total_cheque').val()) || 0;
    var totalTheorique = fondInitial + esp + mob + chq;

    var physique = parseFloat($('#inp_montant_physique').val()) || 0;
    var ecart = physique - totalTheorique;

    if (ecart === 0) {
      $('#panel-ecart').css({ 'background': '#F0FDF4', 'border-color': '#BBF7D0' });
      $('#ecart-val-text').css('color', '#15803D').text('0 FCFA (Caisse Équilibrée)');
      $('#ecart-badge').css({ 'background': '#DCFCE7', 'color': '#15803D' }).text('Parfaitement Conforme');
    } else if (ecart > 0) {
      $('#panel-ecart').css({ 'background': '#EFF6FF', 'border-color': '#BFDBFE' });
      $('#ecart-val-text').css('color', '#1E40AF').text('+' + ecart.toLocaleString('fr-FR') + ' FCFA (Excédent)');
      $('#ecart-badge').css({ 'background': '#DBEAFE', 'color': '#1E40AF' }).text('Excédent constaté');
    } else {
      $('#panel-ecart').css({ 'background': '#FEF2F2', 'border-color': '#FECACA' });
      $('#ecart-val-text').css('color', '#DC2626').text(ecart.toLocaleString('fr-FR') + ' FCFA (Déficit)');
      $('#ecart-badge').css({ 'background': '#FEE2E2', 'color': '#DC2626' }).text('Manquant de caisse');
    }
  }

  $('.calc-field, #inp_montant_physique').on('input', recalculateEcart);
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
