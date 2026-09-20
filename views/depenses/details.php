<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$montant = (float)($item['montant_depense'] ?? ($item['montant'] ?? 0));
$codeDepense = $item['code_depense'] ?? ('DEP-' . ($item['id_depense'] ?? ''));
$statut = $item['statut_depense'] ?? 'en_attente';

// Dates de traçabilité de la dépense
$dateEngagement = !empty($item['periode_depense']) ? date('d/m/Y', strtotime($item['periode_depense'])) : (!empty($item['date_depense']) ? date('d/m/Y', strtotime($item['date_depense'])) : date('d/m/Y'));
$dateCreation = !empty($item['created_at_depense']) ? date('d/m/Y à H:i', strtotime($item['created_at_depense'])) : '';
$dateModification = !empty($item['updated_at_depense']) ? date('d/m/Y à H:i', strtotime($item['updated_at_depense'])) : '';
$dateConfirm = !empty($item['created_at_confirm']) ? date('d/m/Y à H:i', strtotime($item['created_at_confirm'])) : '';

$beneficiaire = !empty($item['beneficiaire']) ? $item['beneficiaire'] : (!empty($item['beneficiaire_depense']) ? $item['beneficiaire_depense'] : 'Non spécifié');
$modeReglement = !empty($item['mode_reglement']) ? $item['mode_reglement'] : (!empty($item['mode_paiement_depense']) ? $item['mode_paiement_depense'] : 'espece');
$labelsMode = [
  'espece' => 'Espèces (Caisse)',
  'mobile_money' => 'Mobile Money',
  'cheque' => 'Chèque bancaire',
  'virement' => 'Virement bancaire'
];
$modePaiementLibelle = $labelsMode[$modeReglement] ?? ucfirst($modeReglement);

$auteur = !empty($item['auteur_nom_complet']) && trim($item['auteur_nom_complet']) !== '' ? trim($item['auteur_nom_complet']) : ((!empty($item['nom_user']) ? $item['nom_user'] . ' ' . ($item['prenom_user'] ?? '') : 'Comptabilité'));
$confirmateur = !empty($item['confirmateur_nom_complet']) ? trim($item['confirmateur_nom_complet']) : '';
?>
<style>
@media print {
  body { background: #fff !important; color: #000 !important; }
  .sidebar, .nav-header, .page-header-actions, .no-print { display: none !important; }
  .app-layout { display: block !important; }
  .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  .content-wrapper { padding: 0 !important; }
  .card-bon { border: 1px dashed #000 !important; box-shadow: none !important; }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Dépense N° <?= htmlspecialchars($codeDepense) ?></h1>
            <?php if ($statut === 'approuve'): ?>
              <span class="badge" style="background: #DCFCE7; color: #15803D; font-weight: 800; padding: 6px 12px; border-radius: 8px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Approuvée
              </span>
            <?php elseif ($statut === 'annule'): ?>
              <span class="badge" style="background: #FEE2E2; color: #991B1B; font-weight: 800; padding: 6px 12px; border-radius: 8px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Annulée
              </span>
            <?php else: ?>
              <span class="badge" style="background: #FEF3C7; color: #B45309; font-weight: 800; padding: 6px 12px; border-radius: 8px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i> En attente de validation
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Bénéficiaire : <strong><?= htmlspecialchars($beneficiaire) ?></strong> &bull; Catégorie : <strong><?= htmlspecialchars($item['libelle_type_depense'] ?? 'Générale') ?></strong></p>
        </div>
        <div class="page-header-actions" style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>depense/list" class="btn btn-secondary" style="background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour au registre
          </a>
          <?php if (!empty($item['piece_justificative'])): ?>
            <a href="<?= RACINE ?>public/<?= htmlspecialchars($item['piece_justificative']) ?>" target="_blank" class="btn btn-info" style="background: #0284C7; border-color: #0284C7; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
              <i data-lucide="paperclip" style="width: 18px; height: 18px;"></i> Pièce Justificative (3 Mo max)
            </a>
          <?php endif; ?>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Bon
          </button>
        </div>
      </div>

      <!-- CARD : BON DE DÉCAISSEMENT OFFICIEL -->
      <div class="card card-bon" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        
        <!-- En-tête du Bon -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #1E3A5F; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; border-radius: 10px; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; text-transform: uppercase;">INSTITUT SUPÉRIEUR GEICG</h2>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Direction Financière & Comptable &bull; Bon de Sortie de Caisse / Décaissement</p>
            </div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; font-family: monospace;">
              N° <?= htmlspecialchars($codeDepense) ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
              Date d'engagement : <strong><?= htmlspecialchars($dateEngagement) ?></strong>
            </div>
            <?php if (!empty($dateCreation)): ?>
              <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">
                Saisie système : <?= htmlspecialchars($dateCreation) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Grille des détails principaux -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
          
          <!-- Montant -->
          <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 800; color: #991B1B; text-transform: uppercase; letter-spacing: 0.3px;">Montant Engagé</span>
            <div style="font-size: 24px; font-weight: 900; color: #DC2626; margin-top: 4px;"><?= number_format($montant, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 3px;">Mode : <strong><?= htmlspecialchars($modePaiementLibelle) ?></strong></div>
          </div>

          <!-- Bénéficiaire & Catégorie -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">Bénéficiaire & Catégorie</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($beneficiaire) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 3px;">Catégorie : <strong><?= htmlspecialchars($item['libelle_type_depense'] ?? 'Générale') ?></strong></div>
          </div>

          <!-- Enregistreur & Année -->
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.3px;">Enregistré Par</span>
            <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($auteur) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 3px;">Année Académique : <strong><?= htmlspecialchars($item['libelle_annee'] ?? 'En cours') ?></strong></div>
          </div>

          <!-- Traitement & Validation -->
          <div style="background: <?= ($statut === 'approuve') ? '#F0FDF4' : (($statut === 'annule') ? '#FEE2E2' : '#FFFBEB') ?>; border: 1px solid <?= ($statut === 'approuve') ? '#BBF7D0' : (($statut === 'annule') ? '#FECACA' : '#FDE68A') ?>; border-radius: 10px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 800; color: <?= ($statut === 'approuve') ? '#15803D' : (($statut === 'annule') ? '#991B1B' : '#B45309') ?>; text-transform: uppercase; letter-spacing: 0.3px;">Traitement / Validation</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;">
              <?php if (!empty($confirmateur) && $statut !== 'en_attente'): ?>
                <?= ($statut === 'approuve' ? 'Validé par ' : 'Annulé par ') . htmlspecialchars($confirmateur) ?>
              <?php else: ?>
                En attente de confirmation
              <?php endif; ?>
            </div>
            <?php if (!empty($dateConfirm) && $statut !== 'en_attente'): ?>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 3px;">Le <?= htmlspecialchars($dateConfirm) ?></div>
            <?php endif; ?>
          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- HORODATAGE & CHRONOLOGIE DES DATES -->
        <!-- ========================================================================= -->
        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px 22px; margin-bottom: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <div style="font-size: 12px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="calendar-clock" style="width: 16px; height: 16px; color: #2563EB;"></i> Chronologie & Horodatage des Dates
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            
            <!-- Date 1 : Engagement -->
            <div style="background: #F8FAFC; border-left: 3px solid #2563EB; border-radius: 6px; padding: 10px 14px;">
              <span style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">1. Date d'Engagement</span>
              <span style="font-size: 14px; font-weight: 800; color: #0F172A; display: block; margin-top: 2px;"><?= htmlspecialchars($dateEngagement) ?></span>
              <span style="font-size: 11px; color: #64748B;">Date comptable du décaissement</span>
            </div>

            <!-- Date 2 : Saisie Système -->
            <div style="background: #F8FAFC; border-left: 3px solid #0284C7; border-radius: 6px; padding: 10px 14px;">
              <span style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">2. Date de Saisie (Création)</span>
              <span style="font-size: 14px; font-weight: 800; color: #0F172A; display: block; margin-top: 2px;"><?= htmlspecialchars($dateCreation ?: 'Non renseignée') ?></span>
              <span style="font-size: 11px; color: #64748B;">Horodatage de la création</span>
            </div>

            <!-- Date 3 : Dernière Modification -->
            <div style="background: #F8FAFC; border-left: 3px solid #64748B; border-radius: 6px; padding: 10px 14px;">
              <span style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">3. Dernière Modification</span>
              <span style="font-size: 14px; font-weight: 800; color: #0F172A; display: block; margin-top: 2px;"><?= htmlspecialchars($dateModification ?: 'Aucune modification') ?></span>
              <span style="font-size: 11px; color: #64748B;">Dernière mise à jour</span>
            </div>

            <!-- Date 4 : Confirmation / Validation -->
            <div style="background: #F8FAFC; border-left: 3px solid <?= ($statut === 'approuve') ? '#16A34A' : (($statut === 'annule') ? '#DC2626' : '#D97706') ?>; border-radius: 6px; padding: 10px 14px;">
              <span style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">4. Validation / Traitement</span>
              <span style="font-size: 14px; font-weight: 800; color: <?= ($statut === 'approuve') ? '#15803D' : (($statut === 'annule') ? '#991B1B' : '#B45309') ?>; display: block; margin-top: 2px;"><?= htmlspecialchars($dateConfirm ?: 'En attente') ?></span>
              <span style="font-size: 11px; color: #64748B;"><?= ($statut === 'approuve') ? 'Date d\'approbation' : (($statut === 'annule') ? 'Date d\'annulation' : 'En attente de visa') ?></span>
            </div>

          </div>
        </div>

        <!-- Motif / Description de la charge -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px 22px; margin-bottom: 24px;">
          <span style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 6px; letter-spacing: 0.3px;">Motif / Description de la charge :</span>
          <div style="font-size: 14.5px; color: #0F172A; font-weight: 600; line-height: 1.6;">
            <?= nl2br(htmlspecialchars($item['description_depense'] ?? ($item['motif_depense'] ?? 'Aucune description saisie.'))) ?>
          </div>
        </div>

        <!-- Pièce Justificative Jointe -->
        <?php if (!empty($item['piece_justificative'])): ?>
        <div style="background: #F0F9FF; border: 1px solid #BAE6FD; border-radius: 10px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="paperclip" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <span style="font-size: 13.5px; font-weight: 800; color: #0369A1; display: block;">Pièce Justificative Jointe</span>
              <span style="font-size: 12px; color: #0284C7; font-weight: 600;"><?= htmlspecialchars(basename($item['piece_justificative'])) ?> (Document joint, max 3 Mo)</span>
            </div>
          </div>
          <a href="<?= RACINE ?>public/<?= htmlspecialchars($item['piece_justificative']) ?>" target="_blank" class="btn btn-sm btn-primary no-print" style="background: #0284C7; border-color: #0284C7; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(2,132,199,0.2);">
            <i data-lucide="external-link" style="width: 16px; height: 16px;"></i> Consulter / Télécharger le document
          </a>
        </div>
        <?php endif; ?>

        <!-- Signatures & Validation -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 36px; padding-top: 24px; border-top: 1px solid #E2E8F0;">
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 800; color: #475569; margin-bottom: 55px; text-transform: uppercase;">Le Bénéficiaire (Émargement)</div>
            <div style="font-size: 11.5px; color: #94A3B8; font-style: italic;">Pour acquit des fonds reçus</div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 800; color: #475569; margin-bottom: 55px; text-transform: uppercase;">Le Responsable Financier / Direction</div>
            <div style="font-size: 11.5px; color: #94A3B8; font-style: italic;">Bon à payer & Visa de sortie</div>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
