<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$item = $item ?? [];
$paiements = $paiements ?? [];

$fondInitial = (float)($item['fond_initial'] ?? 0);
$totalGeneral = (float)($item['total_general'] ?? 0);
$soldeAttendu = (float)($item['solde_attendu'] ?? ($fondInitial + $totalGeneral));
$ecart = (float)($item['ecart_caisse'] ?? 0);
?>
<style>
@media print {
  body { background: #FFF !important; margin: 0; padding: 0; }
  .app-layout > *:not(.main-content), .main-content > *:not(.content-wrapper), .page-header, .btn-no-print { display: none !important; }
  .content-wrapper { padding: 0 !important; margin: 0 !important; width: 100% !important; }
  .print-area { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Procès-Verbal & Détails de Session de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Journal des opérations, récapitulatif financier et clôture</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <button onclick="window.print()" class="btn btn-secondary btn-no-print" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le PV
          </button>
          <a href="<?= RACINE ?>session_caisse/list" class="btn btn-secondary btn-no-print" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour
          </a>
        </div>
      </div>

      <!-- PV Card -->
      <div class="card print-area" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <!-- En-tête du Reçu / PV -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #E2E8F0; padding-bottom: 20px; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 1px;">Établissement GEICG &bull; Service Comptabilité & Caisse</span>
            <h2 style="font-size: 22px; font-weight: 900; color: #0F172A; margin: 4px 0 0 0;">Procès-Verbal de Session de Caisse</h2>
            <div style="font-size: 13px; color: #64748B; margin-top: 4px;">
              Date de session : <strong><?= date('d/m/Y', strtotime($item['date_session'])) ?></strong>
            </div>
          </div>
          <div style="text-align: right;">
            <code style="font-size: 16px; font-weight: 900; color: #1E3A5F; background: #EFF6FF; padding: 6px 14px; border-radius: 8px; border: 1px solid #BFDBFE;">
              <?= htmlspecialchars($item['code_session']) ?>
            </code>
            <div style="margin-top: 8px;">
              <?php if ($item['statut_session'] === 'ouverte'): ?>
                <span class="badge" style="background: #DCFCE7; color: #15803D; font-weight: 800; padding: 5px 12px; border-radius: 20px; font-size: 12px;">En cours (Ouverte)</span>
              <?php elseif ($item['statut_session'] === 'valide'): ?>
                <span class="badge" style="background: #DBEAFE; color: #1E40AF; font-weight: 800; padding: 5px 12px; border-radius: 20px; font-size: 12px;">Clôturée & Validée</span>
              <?php else: ?>
                <span class="badge" style="background: #FEF3C7; color: #B45309; font-weight: 800; padding: 5px 12px; border-radius: 20px; font-size: 12px;">Clôturée</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Informations Session & Horaires -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Caissier Responsable</span>
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?= htmlspecialchars($item['caissier_nom'] ?? ($item['user_code'] ?? 'Caissier')) ?>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Heure d'Ouverture</span>
            <div style="font-size: 14px; font-weight: 800; color: #15803D; margin-top: 2px;">
              <?= htmlspecialchars($item['heure_ouverture'] ?? '--:--') ?>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Heure de Clôture</span>
            <div style="font-size: 14px; font-weight: 800; color: #1E3A5F; margin-top: 2px;">
              <?= htmlspecialchars($item['heure_cloture'] ?? '--:--') ?>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Superviseur / Validation</span>
            <div style="font-size: 14px; font-weight: 800; color: #7E22CE; margin-top: 2px;">
              <?= htmlspecialchars($item['superviseur_nom'] ?? ($item['user_validation'] ?? '-')) ?>
            </div>
          </div>
        </div>

        <!-- Synthèse Financière de la Session -->
        <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 14px 0;">Synthèse des Flux Financiers</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px;">
          
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">1. Fond Initial</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($fondInitial, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">2. Espèces Encaissées</span>
            <div style="font-size: 18px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format((float)$item['total_especes'], 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">3. Mobile Money</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format((float)$item['total_mobile_money'], 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #B45309; text-transform: uppercase;">4. Total Encaissé</span>
            <div style="font-size: 18px; font-weight: 800; color: #B45309; margin-top: 4px;"><?= number_format($totalGeneral, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: <?= $ecart != 0 ? '#FEF2F2' : '#F0FDF4' ?>; border: 1px solid <?= $ecart != 0 ? '#FECACA' : '#BBF7D0' ?>; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: <?= $ecart != 0 ? '#DC2626' : '#15803D' ?>; text-transform: uppercase;">5. Écart Constaté</span>
            <div style="font-size: 18px; font-weight: 900; color: <?= $ecart != 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
              <?= ($ecart > 0 ? '+' : '') . number_format($ecart, 0, ',', ' ') ?> F
            </div>
          </div>

        </div>

        <!-- Détail des Transactions Collectées -->
        <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 14px 0;">Journal des Règlements Encaissés (<?= count($paiements) ?>)</h3>
        <div style="width: 100%; overflow-x: auto; margin-bottom: 28px;">
          <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; text-align: left;">
                <th style="padding: 10px;">Réf. Reçu</th>
                <th style="padding: 10px;">Étudiant & Classe</th>
                <th style="padding: 10px;">Tranche Réglée</th>
                <th style="padding: 10px;">Mode Règlement</th>
                <th style="padding: 10px;">Réf. Transaction</th>
                <th style="padding: 10px; text-align: right;">Montant (FCFA)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($paiements)): ?>
                <tr>
                  <td colspan="6" style="padding: 16px; text-align: center; color: #94A3B8;">Aucun encaissement enregistré au cours de cette session.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($paiements as $p): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px;"><code style="font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($p['code_paiement'] ?? '-') ?></code></td>
                    <td style="padding: 10px;">
                      <div style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars(($p['nom_etudiant'] ?? ($p['nom'] ?? '')) . ' ' . ($p['prenom_etudiant'] ?? ($p['prenom'] ?? ''))) ?></div>
                      <div style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($p['libelle_classe'] ?? '') ?></div>
                    </td>
                    <td style="padding: 10px;">
                      <span style="font-weight: 700; color: #1E3A5F; font-size: 12px;"><?= htmlspecialchars($p['libelle_tranche'] ?? ($p['type_paiement'] ?? 'Scolarité')) ?></span>
                    </td>
                    <td style="padding: 10px;">
                      <span class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; padding: 3px 8px; border-radius: 6px; font-size: 11px; text-transform: uppercase;">
                        <?= htmlspecialchars($p['mode_paiement'] ?? 'espece') ?>
                      </span>
                    </td>
                    <td style="padding: 10px; color: #64748B; font-size: 12px;"><?= htmlspecialchars($p['reference_paiement'] ?? '-') ?></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #0F172A;">
                      <?= number_format((float)$p['montant_paiement'], 0, ',', ' ') ?> F
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Observations & Signatures -->
        <?php if (!empty($item['observations_ouverture']) || !empty($item['observations_cloture'])): ?>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; font-size: 13px; color: #475569;">
            <strong>Observations :</strong>
            <?php if (!empty($item['observations_ouverture'])): ?>
              <div>&bull; Ouverture : <?= htmlspecialchars($item['observations_ouverture']) ?></div>
            <?php endif; ?>
            <?php if (!empty($item['observations_cloture'])): ?>
              <div>&bull; Clôture : <?= htmlspecialchars($item['observations_cloture']) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
          <div>
            <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Signature & Cachet du Caissier</div>
            <div style="height: 60px;"></div>
            <div style="font-size: 13px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($item['caissier_nom'] ?? 'Caissier') ?></div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Visa & Contrôle Superviseur / Comptabilité</div>
            <div style="height: 60px;"></div>
            <div style="font-size: 13px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($item['superviseur_nom'] ?? 'Superviseur') ?></div>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
