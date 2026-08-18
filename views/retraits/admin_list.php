<?php
require_once __DIR__ . '/../../public/inc/header.php';
$retraits = $retraits ?? [];
$mode = $mode ?? 'sandbox';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
          <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="banknote" style="color: #2563EB;"></i> Retraits des Pressings
          </h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">Supervision, approbation et déclenchement des reversements Mobile Money aux ateliers partenaires</p>
        </div>
        <div>
          <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="zap" style="width: 14px; height: 14px;"></i> Passerelle GeniusPay (Mode <?= ucfirst($mode) ?>)
          </span>
        </div>
      </div>

      <div class="card" style="border-radius: 16px; border: 1px solid #E2E8F0; padding: 24px; background: #FFFFFF;">
        <div class="table-responsive">
          <table class="table" style="width: 100%;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Code</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Pressing</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Date</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Montant</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Opérateur & Compte</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B; text-align: center;">Statut</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B; text-align: center;">Actions Admin</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($retraits)): ?>
                <tr>
                  <td colspan="7" style="text-align: center; padding: 36px; color: #94A3B8;">
                    <i data-lucide="inbox" style="width: 36px; height: 36px; margin-bottom: 8px; display: inline-block;"></i>
                    <p style="margin: 0; font-size: 14px;">Aucune demande de retrait enregistrée pour le moment.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($retraits as $r): ?>
                  <?php
                    $badgeCls = 'bg-secondary';
                    $badgeLabel = $r['statut_retrait'];
                    if ($r['statut_retrait'] === 'complete') {
                        $badgeCls = 'background:#ECFDF5; color:#059669; border:1px solid #A7F3D0;';
                        $badgeLabel = '✓ Virement effectué';
                    } elseif ($r['statut_retrait'] === 'en_attente') {
                        $badgeCls = 'background:#FFFBEB; color:#D97706; border:1px solid #FDE68A;';
                        $badgeLabel = '⏳ En attente validation Admin';
                    } elseif ($r['statut_retrait'] === 'approuve') {
                        $badgeCls = 'background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE;';
                        $badgeLabel = '⚡ Virement en cours (GeniusPay)';
                    } elseif ($r['statut_retrait'] === 'rejete' || $r['statut_retrait'] === 'echoue') {
                        $badgeCls = 'background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;';
                        $badgeLabel = '✗ Rejeté / Échoué';
                    }
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 14px 12px;">
                      <span style="background: #F1F5F9; color: #1E293B; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: 700; font-size: 12px;">
                        <?= htmlspecialchars($r['code_retrait']) ?>
                      </span>
                    </td>
                    <td style="padding: 14px 12px;">
                      <strong style="color: #1E293B; font-size: 14px;"><?= htmlspecialchars($r['libelle_pressing'] ?? $r['pressing_code']) ?></strong>
                      <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars($r['pressing_code']) ?></div>
                    </td>
                    <td style="padding: 14px 12px; font-size: 13px; color: #64748B;">
                      <?= date('d/m/Y H:i', strtotime($r['created_at_retrait'])) ?>
                    </td>
                    <td style="padding: 14px 12px;">
                      <strong style="font-size: 15px; color: #059669;">
                        <?= number_format($r['montant_demande'], 0, ',', ' ') ?> FCFA
                      </strong>
                    </td>
                    <td style="padding: 14px 12px;">
                      <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="text-transform: uppercase; font-size: 11px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: <?= $r['operateur_retrait'] === 'wave' ? '#E0F2FE; color:#0284C7' : ($r['operateur_retrait'] === 'orange_money' ? '#FFEDD5; color:#EA580C' : '#FEF3C7; color:#D97706') ?>;">
                          <?= htmlspecialchars($r['operateur_retrait']) ?>
                        </span>
                        <span style="font-weight: 700; font-size: 13px; color: #1E293B;"><?= htmlspecialchars($r['telephone_beneficiaire']) ?></span>
                      </div>
                      <?php if (!empty($r['nom_beneficiaire'])): ?>
                        <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Titulaire : <?= htmlspecialchars($r['nom_beneficiaire']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 14px 12px; text-align: center;">
                      <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; <?= $badgeCls ?>">
                        <?= $badgeLabel ?>
                      </span>
                    </td>
                    <td style="padding: 14px 12px; text-align: center;">
                      <?php if ($r['statut_retrait'] === 'en_attente'): ?>
                        <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                          <button type="button" class="btn btn-sm btn-primary" onclick="approuverRetrait(<?= $r['id_retrait'] ?>, '<?= $r['code_retrait'] ?>', '<?= htmlspecialchars($r['libelle_pressing'] ?? '') ?>', <?= (float)$r['montant_demande'] ?>, '<?= htmlspecialchars($r['operateur_retrait']) ?>', '<?= htmlspecialchars($r['telephone_beneficiaire']) ?>')" style="padding: 6px 12px; font-size: 12px; font-weight: 700; background: #059669; border-color: #059669;">
                            <i data-lucide="send" style="width: 14px; height: 14px;"></i> Approuver & Virer
                          </button>
                          <button type="button" class="btn btn-sm btn-danger" onclick="rejeterVirement(<?= $r['id_retrait'] ?>, '<?= $r['code_retrait'] ?>')" style="padding: 6px 10px; font-size: 12px; font-weight: 700;">
                            <i data-lucide="x" style="width: 14px; height: 14px;"></i> Rejeter
                          </button>
                        </div>
                      <?php elseif ($r['statut_retrait'] === 'approuve'): ?>
                        <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                          <button type="button" class="btn btn-sm btn-success" onclick="simulerWebhookCashout('<?= $r['code_retrait'] ?>', 'complete')" style="padding: 6px 10px; font-size: 11px; font-weight: 700;">
                            ⚡ Confirmer Webhook Terminé
                          </button>
                        </div>
                      <?php else: ?>
                        <span style="font-size: 12px; color: #94A3B8; font-style: italic;">Demande clôturée</span>
                      <?php endif; ?>
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

<script>
function approuverRetrait(id, code, pressing, montant, operateur, tel) {
  const montantStr = Number(montant).toLocaleString('fr-FR') + ' FCFA';
  if (!confirm("Voulez-vous approuver la demande #" + code + " du pressing " + pressing + " et déclencher le virement de " + montantStr + " vers le compte " + operateur.toUpperCase() + " (" + tel + ") via GeniusPay ?")) {
    return;
  }

  $.post(LINK + 'retrait/changerStatut', {
    id_retrait: id,
    statut: 'approuve'
  }, function(rep) {
    if (rep.status) {
      showToast(rep.message, 'success');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(rep.message || 'Erreur lors du virement', 'error');
    }
  }, 'json').fail(function() {
    showToast('Erreur de communication avec le serveur', 'error');
  });
}

function simulerWebhookCashout(codeRetrait, statut) {
  $.post(LINK + 'retrait/simulerWebhookCashout', {
    code_retrait: codeRetrait,
    statut: statut
  }, function(rep) {
    if (rep.status) {
      showToast(rep.message, 'success');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(rep.message || 'Erreur', 'error');
    }
  }, 'json').fail(function() {
    showToast('Erreur serveur', 'error');
  });
}

function rejeterVirement(id, code) {
  const motif = prompt("Motif du rejet pour la demande " + code + " :", "Numéro de téléphone incorrect ou informations manquantes");
  if (!motif) return;

  $.post(LINK + 'retrait/changerStatut', {
    id_retrait: id,
    statut: 'rejete',
    motif: motif
  }, function(rep) {
    if (rep.status) {
      showToast(rep.message, 'success');
      setTimeout(() => location.reload(), 900);
    } else {
      showToast(rep.message, 'error');
    }
  }, 'json').fail(function() {
    showToast('Erreur serveur', 'error');
  });
}
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
