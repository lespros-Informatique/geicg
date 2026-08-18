<?php
require_once __DIR__ . '/../../public/inc/header.php';
$solde = $solde ?? ['solde_disponible' => 0, 'total_online' => 0, 'total_pending' => 0, 'total_completed' => 0];
$retraits = $retraits ?? [];
$minRetrait = $minRetrait ?? 2000;
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
          <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="wallet" style="color: #059669;"></i> Mon Portefeuille & Retraits
          </h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">Gérez vos gains issus des paiements en ligne et effectuez vos retraits</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="ouvrirModalRetrait()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #059669; border-color: #059669; padding: 10px 18px; border-radius: 10px;">
          <i data-lucide="arrow-up-right" style="width: 18px; height: 18px;"></i>
          Demander un retrait
        </button>
      </div>

      <!-- CARTES DE SOLDE / STATS FINANCIÈRES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Solde Disponible -->
        <div style="background: linear-gradient(135deg, #059669 0%, #10B981 100%); color: #FFFFFF; border-radius: 16px; padding: 22px; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; opacity: 0.9;">Solde Disponible</span>
              <h2 style="font-size: 28px; font-weight: 900; margin: 8px 0 4px; color: #FFFFFF;">
                <?= number_format($solde['solde_disponible'], 0, ',', ' ') ?> <span style="font-size: 16px; font-weight: 600;">FCFA</span>
              </h2>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
              <i data-lucide="banknote" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
            </div>
          </div>
          <p style="margin: 12px 0 0; font-size: 12px; opacity: 0.85;">Fonds immédiatement transférables vers votre Mobile Money</p>
        </div>

        <!-- Total Encaissé en Ligne -->
        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748B;">Total Encaissé en Ligne</span>
              <h3 style="font-size: 22px; font-weight: 800; margin: 8px 0 4px; color: #1E293B;">
                <?= number_format($solde['total_online'], 0, ',', ' ') ?> <span style="font-size: 14px; font-weight: 600; color: #64748B;">FCFA</span>
              </h3>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="credit-card" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <span style="font-size: 12px; color: #2563EB; font-weight: 600; margin-top: 10px; display: inline-block;">Wave, Orange & MTN</span>
        </div>

        <!-- En Attente de Virement -->
        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748B;">En cours de traitement</span>
              <h3 style="font-size: 22px; font-weight: 800; margin: 8px 0 4px; color: #D97706;">
                <?= number_format($solde['total_pending'], 0, ',', ' ') ?> <span style="font-size: 14px; font-weight: 600; color: #64748B;">FCFA</span>
              </h3>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <span style="font-size: 12px; color: #D97706; font-weight: 600; margin-top: 10px; display: inline-block;">Retraits en cours</span>
        </div>

        <!-- Total Déjà Retiré -->
        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748B;">Total Déjà Retiré</span>
              <h3 style="font-size: 22px; font-weight: 800; margin: 8px 0 4px; color: #1E293B;">
                <?= number_format($solde['total_completed'], 0, ',', ' ') ?> <span style="font-size: 14px; font-weight: 600; color: #64748B;">FCFA</span>
              </h3>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #F1F5F9; color: #475569; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <span style="font-size: 12px; color: #059669; font-weight: 600; margin-top: 10px; display: inline-block;">Virements effectués</span>
        </div>

      </div>

      <!-- HISTORIQUE DES RETRAITS -->
      <div class="card" style="border-radius: 16px; border: 1px solid #E2E8F0; padding: 24px; background: #FFFFFF;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <div>
            <h2 style="font-size: 17px; font-weight: 800; color: #1E293B; margin: 0;">Historique de vos retraits</h2>
            <p style="font-size: 12px; color: #64748B; margin: 4px 0 0;">Suivez le traitement de vos reversements Mobile Money en temps réel</p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table" style="width: 100%;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Code</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Date</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Montant</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Opérateur & Numéro</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Bénéficiaire</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B; text-align: center;">Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($retraits)): ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 36px; color: #94A3B8;">
                    <i data-lucide="inbox" style="width: 36px; height: 36px; margin-bottom: 8px; display: inline-block;"></i>
                    <p style="margin: 0; font-size: 14px;">Aucune demande de retrait effectuée pour le moment.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($retraits as $r): ?>
                  <?php
                    $badgeCls = 'bg-secondary';
                    $badgeLabel = $r['statut_retrait'];
                    if ($r['statut_retrait'] === 'complete') {
                        $badgeCls = 'background:#ECFDF5; color:#059669; border:1px solid #A7F3D0;';
                        $badgeLabel = 'Virement complété';
                    } elseif ($r['statut_retrait'] === 'en_attente') {
                        $badgeCls = 'background:#FFFBEB; color:#D97706; border:1px solid #FDE68A;';
                        $badgeLabel = 'En attente';
                    } elseif ($r['statut_retrait'] === 'approuve') {
                        $badgeCls = 'background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE;';
                        $badgeLabel = 'Approuvé (en cours)';
                    } elseif ($r['statut_retrait'] === 'rejete' || $r['statut_retrait'] === 'echoue') {
                        $badgeCls = 'background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;';
                        $badgeLabel = 'Échoué / Rejeté';
                    }
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 14px 12px;">
                      <span style="background: #F1F5F9; color: #1E293B; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: 700; font-size: 12px;">
                        <?= htmlspecialchars($r['code_retrait']) ?>
                      </span>
                    </td>
                    <td style="padding: 14px 12px; font-size: 13px; color: #64748B;">
                      <?= date('d/m/Y H:i', strtotime($r['created_at_retrait'])) ?>
                    </td>
                    <td style="padding: 14px 12px;">
                      <strong style="font-size: 15px; color: #1E293B;">
                        <?= number_format($r['montant_demande'], 0, ',', ' ') ?> FCFA
                      </strong>
                    </td>
                    <td style="padding: 14px 12px;">
                      <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="text-transform: uppercase; font-size: 11px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: <?= $r['operateur_retrait'] === 'wave' ? '#E0F2FE; color:#0284C7' : ($r['operateur_retrait'] === 'orange_money' ? '#FFEDD5; color:#EA580C' : '#FEF3C7; color:#D97706') ?>;">
                          <?= htmlspecialchars($r['operateur_retrait']) ?>
                        </span>
                        <span style="font-weight: 600; font-size: 13px; color: #1E293B;"><?= htmlspecialchars($r['telephone_beneficiaire']) ?></span>
                      </div>
                    </td>
                    <td style="padding: 14px 12px; font-size: 13px; color: #475569;">
                      <?= htmlspecialchars($r['nom_beneficiaire'] ?: '-') ?>
                    </td>
                    <td style="padding: 14px 12px; text-align: center;">
                      <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; <?= $badgeCls ?>">
                        <?= $badgeLabel ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL DEMANDE DE RETRAIT -->
<div id="modalRetrait" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
  <div style="background: #FFFFFF; width: 100%; max-width: 480px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: modalPop 0.2s ease-out;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="arrow-up-right" style="color: #059669;"></i> Demander un Retrait
      </h3>
      <button type="button" onclick="fermerModalRetrait()" style="background: none; border: none; font-size: 22px; color: #94A3B8; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="formDemandeRetrait" onsubmit="soumettreRetrait(event)" style="padding: 24px;">
      <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;">
        <div style="font-size: 12px; color: #065F46; font-weight: 600;">Solde disponible actuel :</div>
        <div style="font-size: 20px; font-weight: 900; color: #047857;" id="modalSoldeDisponible"><?= number_format($solde['solde_disponible'], 0, ',', ' ') ?> FCFA</div>
      </div>

      <!-- Opérateur -->
      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 8px;">Opérateur Mobile Money <span style="color:#DC2626;">*</span></label>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
          <label style="border: 2px solid #E2E8F0; border-radius: 12px; padding: 10px 8px; text-align: center; cursor: pointer; transition: all 0.2s;" class="op-card" onclick="selectOp(this)">
            <input type="radio" name="operateur" value="wave" checked style="display: none;">
            <div style="font-weight: 800; font-size: 13px; color: #0284C7;">Wave</div>
            <small style="color: #64748B; font-size: 11px;">Instantané</small>
          </label>
          <label style="border: 2px solid #E2E8F0; border-radius: 12px; padding: 10px 8px; text-align: center; cursor: pointer; transition: all 0.2s;" class="op-card" onclick="selectOp(this)">
            <input type="radio" name="operateur" value="orange_money" style="display: none;">
            <div style="font-weight: 800; font-size: 13px; color: #EA580C;">Orange</div>
            <small style="color: #64748B; font-size: 11px;">OM CI</small>
          </label>
          <label style="border: 2px solid #E2E8F0; border-radius: 12px; padding: 10px 8px; text-align: center; cursor: pointer; transition: all 0.2s;" class="op-card" onclick="selectOp(this)">
            <input type="radio" name="operateur" value="mtn_money" style="display: none;">
            <div style="font-weight: 800; font-size: 13px; color: #D97706;">MTN</div>
            <small style="color: #64748B; font-size: 11px;">MoMo</small>
          </label>
        </div>
      </div>

      <!-- Montant -->
      <div style="margin-bottom: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label for="retraitMontant" style="font-size: 13px; font-weight: 700; color: #1E293B;">Montant à retirer (FCFA) <span style="color:#DC2626;">*</span></label>
          <button type="button" onclick="retirerMax()" style="background: none; border: none; color: #059669; font-weight: 700; font-size: 12px; cursor: pointer;">Tout retirer</button>
        </div>
        <input type="number" id="retraitMontant" name="montant" class="form-control" min="<?= $minRetrait ?>" max="<?= $solde['solde_disponible'] ?>" step="500" placeholder="Ex: 10000" required style="font-size: 16px; font-weight: 700;">
        <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Montant minimum : <strong><?= number_format($minRetrait, 0, ',', ' ') ?> FCFA</strong></small>
      </div>

      <!-- Téléphone -->
      <div style="margin-bottom: 18px;">
        <label for="retraitTelephone" style="font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px; display: block;">Numéro de téléphone récepteur <span style="color:#DC2626;">*</span></label>
        <input type="tel" id="retraitTelephone" name="telephone" class="form-control" placeholder="Ex: 0748123456" required style="font-size: 15px;">
      </div>

      <!-- Nom Bénéficiaire (Optionnel) -->
      <div style="margin-bottom: 22px;">
        <label for="retraitNom" style="font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px; display: block;">Nom du titulaire du compte (optionnel)</label>
        <input type="text" id="retraitNom" name="nom_beneficiaire" class="form-control" placeholder="Ex: Kouamé Jean">
      </div>

      <div style="display: flex; gap: 10px;">
        <button type="button" onclick="fermerModalRetrait()" class="btn btn-secondary" style="flex: 1; font-weight: 600;">Annuler</button>
        <button type="submit" id="btnSubmitRetrait" class="btn btn-primary" style="flex: 2; font-weight: 700; background: #059669; border-color: #059669;">
          Confirmer le retrait
        </button>
      </div>
    </form>
  </div>
</div>

<style>
.op-card.selected {
  border-color: #059669 !important;
  background: #F0FDF4 !important;
}
@keyframes modalPop {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>

<script>
const soldeMax = <?= (float)$solde['solde_disponible'] ?>;
const minRetrait = <?= (float)$minRetrait ?>;

function selectOp(card) {
  document.querySelectorAll('.op-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
}
document.querySelector('.op-card')?.classList.add('selected');

function ouvrirModalRetrait() {
  if (soldeMax < minRetrait) {
    showToast('Votre solde disponible (' + soldeMax.toLocaleString('fr-FR') + ' FCFA) est inférieur au seuil minimum de retrait (' + minRetrait.toLocaleString('fr-FR') + ' FCFA).', 'warning');
    return;
  }
  document.getElementById('modalRetrait').style.display = 'flex';
}

function fermerModalRetrait() {
  document.getElementById('modalRetrait').style.display = 'none';
}

function retirerMax() {
  document.getElementById('retraitMontant').value = soldeMax;
}

function soumettreRetrait(e) {
  e.preventDefault();
  const form = $('#formDemandeRetrait');
  const btn = $('#btnSubmitRetrait');
  const montant = parseFloat($('#retraitMontant').val() || 0);

  if (montant < minRetrait) {
    showToast('Le montant minimum est de ' + minRetrait.toLocaleString('fr-FR') + ' FCFA', 'error');
    return;
  }
  if (montant > soldeMax) {
    showToast('Le montant dépasse votre solde disponible.', 'error');
    return;
  }

  loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Envoi...');

  $.ajax({
    url: LINK + 'retrait/demander',
    type: 'POST',
    data: form.serialize(),
    dataType: 'json',
    success: function(rep) {
      loading(btn, false, 'Confirmer le retrait');
      if (rep.status) {
        showToast(rep.message, 'success');
        fermerModalRetrait();
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(rep.message, 'error');
      }
    },
    error: function() {
      loading(btn, false, 'Confirmer le retrait');
      showToast('Erreur serveur lors de la demande de retrait.', 'error');
    }
  });
}

$(document).ready(function() {
  if (window.location.search.indexOf('action=nouveau') !== -1 || window.location.hash === '#demander') {
    ouvrirModalRetrait();
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
