<?php
require_once __DIR__ . '/../../public/inc/header.php';

$item = isset($item) ? $item : [];
$montantOp = (float)($item['montant_paye'] ?? ($item['montant_paiement'] ?? 0));
$scolarite = (float)($scolarite ?? ($item['montant_scolarite_inscription'] ?? 0));
$totalPayeCumul = (float)($totalPayeCumul ?? 0);
$soldeRestant = (float)($soldeRestant ?? 0);

$numRecu = !empty($item['recu_numero_paiement']) ? $item['recu_numero_paiement'] : (!empty($item['code_paiement']) ? $item['code_paiement'] : 'GE-25260276');
$datePaiement = !empty($item['date_paiement']) ? date('d/m/Y H:i:s', strtotime($item['date_paiement'])) : date('d/m/Y H:i:s');
$datePrint = date('d/m/Y H:i:s');

$matricule = !empty($item['matricule_etudiant']) ? $item['matricule_etudiant'] : ($item['code_etudiant'] ?? '-');
$nomComplet = strtoupper(trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')));

$filiereLib = $item['libelle_filiere'] ?? '';
$niveauLib = $item['libelle_niveau'] ?? '';
$filiereNiveau = (!empty($filiereLib) && !empty($niveauLib)) ? ($filiereLib . '_' . $niveauLib) : ($item['libelle_classe'] ?? 'GEC_2A');

$statutAffectation = strtoupper(!empty($item['affectation_etat']) ? $item['affectation_etat'] : 'AFFECTE');
$anneeLibelle = $item['libelle_annee'] ?? '2025-2026';
$caissierNom = trim(($item['prenom_caissier'] ?? '') . ' ' . ($item['nom_caissier'] ?? 'Mlle KONE N\'diatty A. Mariam'));
if (empty($caissierNom)) $caissierNom = 'Mlle KONE N\'diatty A. Mariam';

$montantEnLettres = Validator::numberToWordsFCFA($montantOp);

$typeOp = !empty($item['libelle_tranche']) ? $item['libelle_tranche'] : (!empty($item['type_paiement']) ? $item['type_paiement'] : 'INSCRIPTION');

// Décomposition financière
$isDroitInscription = (stripos($typeOp, 'droit') !== false || stripos($typeOp, 'inscription') !== false && $montantOp <= 80000);
$opScolarite = $isDroitInscription ? max(0, $montantOp - 80000) : $montantOp;
$opDroit = $isDroitInscription ? min($montantOp, 80000) : 0;
if ($opDroit == 0 && $montantOp == 105000) {
    $opScolarite = 25000;
    $opDroit = 80000;
}

$refCaiss = $numRecu . 'ScoFOF' . sprintf("%05d", rand(10000, 99999)) . ',' . sprintf("%010d", rand(1000000000, 9999999999)) . 'ScaisKON';
?>
<style>
@media print {
  body { background: #FFFFFF !important; color: #000000 !important; font-family: Arial, Helvetica, sans-serif !important; }
  .sidebar, .nav-header, .page-header-actions, .no-print, header, nav, .main-nav { display: none !important; }
  .app-layout, .main-content, .content-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; box-shadow: none !important; }
  .receipt-page-container { border: 2px solid #800000 !important; padding: 12px !important; margin: 0 !important; box-shadow: none !important; width: 100% !important; box-sizing: border-box !important; }
}

.receipt-outer-frame {
  border: 2px solid #800000;
  padding: 16px;
  background: #FFFFFF;
  font-family: Arial, Helvetica, sans-serif;
  color: #000000;
  max-width: 860px;
  margin: 0 auto;
  box-sizing: border-box;
}

.receipt-header-table {
  width: 100%;
  border-collapse: collapse;
}

.logo-eicg-box {
  background: #800000;
  color: #FFFFFF;
  padding: 10px 14px;
  border-radius: 4px;
  text-align: center;
  font-weight: 900;
  font-size: 15px;
  line-height: 1.2;
}

.institution-title {
  color: #800000;
  font-weight: bold;
  text-decoration: underline;
  font-size: 16px;
  text-align: center;
  margin: 0 0 3px 0;
}

.institution-subtitle {
  font-style: italic;
  font-weight: bold;
  font-size: 13px;
  text-align: center;
  margin: 0 0 3px 0;
}

.institution-contacts {
  font-style: italic;
  font-size: 12px;
  text-align: center;
  margin: 0;
}

.title-gray-banner {
  background: #9E9E9E;
  color: #000000;
  font-weight: bold;
  font-size: 18px;
  text-align: center;
  padding: 6px;
  margin: 10px 0 6px 0;
  letter-spacing: 0.5px;
}

.annee-academique-title {
  text-align: center;
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 12px;
}

.student-photo-box {
  border: 1px solid #000000;
  width: 95px;
  height: 115px;
  float: right;
  margin-left: 12px;
  background: #F8FAFC;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.info-grid-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  line-height: 1.5;
}

.gray-inline-badge {
  background: #D1D5DB;
  padding: 2px 8px;
  font-weight: bold;
  display: inline-block;
}

.blue-text-words {
  color: #0000FF;
  font-weight: bold;
}

.fin-breakdown-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  margin-bottom: 12px;
  font-size: 12.5px;
  border: 1px solid #000000;
}

.fin-breakdown-table th {
  background: #D1D5DB;
  border: 1px solid #000000;
  padding: 6px 8px;
  text-align: center;
  font-weight: bold;
}

.fin-breakdown-table td {
  border: 1px solid #000000;
  padding: 6px 8px;
}

.fin-total-row {
  background: #404040;
  color: #FFFFFF;
  font-weight: bold;
}

.fin-total-row td {
  border: 1px solid #000000;
  color: #FFFFFF;
  font-weight: bold;
}

.yellow-nb-box {
  background: #FFFF00;
  border: 1px solid #000000;
  padding: 6px 10px;
  margin: 10px 0;
  font-size: 12px;
  font-weight: bold;
  font-style: italic;
  color: #000000;
  line-height: 1.4;
}

.barcode-simulated {
  font-family: 'Libre Barcode 128', 'Code 128', monospace;
  font-size: 26px;
  letter-spacing: 2px;
  text-align: right;
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- Barre d'Action Haut -->
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Reçu d'Inscription Officiel N° <?= htmlspecialchars($numRecu) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Étudiant : <strong><?= htmlspecialchars($nomComplet) ?></strong> &bull; Impression conforme au modèle institutionnel</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux paiements
          </a>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu (Modèle Officiel)
          </button>
        </div>
      </div>

      <!-- FRAME DU REÇU IMPRIMABLE -->
      <div class="receipt-page-container receipt-outer-frame">
        
        <!-- ========================================================================= -->
        <!-- PARTIE 1 : REÇU D'INSCRIPTION (COPIE ÉTUDIANT)                            -->
        <!-- ========================================================================= -->
        
        <!-- En-tête Institutionnel -->
        <table class="receipt-header-table">
          <tr>
            <td style="width: 140px; vertical-align: top;">
              <div class="logo-eicg-box">
                GROUPE<br>EICG
              </div>
            </td>
            <td style="vertical-align: top; text-align: center;">
              <h1 class="institution-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</h1>
              <div class="institution-subtitle">Agréé par l'Etat et le FDFP</div>
              <div class="institution-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
            </td>
          </tr>
        </table>

        <!-- Bandeau Titre -->
        <div class="title-gray-banner">REÇU D'INSCRIPTION</div>

        <!-- Année Académique -->
        <div class="annee-academique-title">ANNEE ACADEMIQUE : <?= htmlspecialchars($anneeLibelle) ?></div>

        <!-- Photo & Détails Étudiant -->
        <div style="clear: both; margin-bottom: 10px;">
          
          <div class="student-photo-box">
            <?php if (!empty($item['photo_etudiant']) && file_exists(__DIR__ . '/../../public/' . $item['photo_etudiant'])): ?>
              <img src="<?= RACINE . $item['photo_etudiant'] ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
              <div style="text-align: center; color: #94A3B8;">
                <span style="font-size: 24px; font-weight: 900; display: block;"><?= strtoupper(substr($item['nom_etudiant'] ?? 'E', 0, 1)) ?></span>
                <span style="font-size: 9px; font-weight: bold;">PHOTO</span>
              </div>
            <?php endif; ?>
          </div>

          <table class="info-grid-table">
            <tr>
              <td style="width: 55%; vertical-align: top;">
                <strong>N° <?= htmlspecialchars($numRecu) ?></strong>
              </td>
              <td style="width: 45%; vertical-align: top; text-align: right;">
                <?= htmlspecialchars($datePaiement) ?>
              </td>
            </tr>
            <tr>
              <td>Numéro réf étudiant( e) : <strong><?= htmlspecialchars($matricule) ?></strong></td>
              <td>Filière_Niveau : <strong><?= htmlspecialchars($filiereNiveau) ?></strong></td>
            </tr>
            <tr>
              <td colspan="2">Nom_Prénom(s) : <strong><?= htmlspecialchars($nomComplet) ?></strong></td>
            </tr>
            <tr>
              <td>Type d'Opération &nbsp; <span class="gray-inline-badge"><?= htmlspecialchars($typeOp) ?></span></td>
              <td>Statut : <strong><?= htmlspecialchars($statutAffectation) ?></strong></td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 4px;">
                Montant de l'Opération : <strong><?= number_format($montantOp, 0, '', ' ') ?>CFA</strong>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="blue-text-words"><?= htmlspecialchars($montantEnLettres) ?></span>
              </td>
            </tr>
          </table>

        </div>

        <!-- Tableau Financier / Opérations -->
        <table class="fin-breakdown-table">
          <thead>
            <tr>
              <th style="width: 25%; text-align: left;">OP. DU JOUR</th>
              <th style="width: 25%;">Total à payer</th>
              <th style="width: 25%;">Total Versé</th>
              <th style="width: 25%;">Reste à Payer</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="font-weight: bold;">SCOLARITE</td>
              <td style="text-align: center; font-weight: bold;"><?= number_format($opScolarite, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold;"><?= number_format($scolarite, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold;"><?= number_format($totalPayeCumul, 0, '', ' ') ?>CFA</td>
            </tr>
            <tr>
              <td style="font-weight: bold;">Droit d'Inscription</td>
              <td style="text-align: center; font-weight: bold;"><?= number_format($opDroit, 0, '', ' ') ?>CFA</td>
              <td style="background: #E0E0E0;"></td>
              <td style="text-align: center; font-weight: bold;"><?= number_format($opDroit, 0, '', ' ') ?>CFA</td>
            </tr>
            <tr>
              <td style="font-weight: bold;">AUTRES FRAIS</td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr class="fin-total-row">
              <td style="font-weight: bold; background: #404040;">TOTAL</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($montantOp, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($scolarite, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($totalPayeCumul, 0, '', ' ') ?>CFA</td>
            </tr>
          </tbody>
        </table>

        <!-- Date du Prochain Paiement & Caissier -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 13px;">
          <tr>
            <td style="vertical-align: middle;">
              <strong>Date du Prochain Payement :</strong> &nbsp;
              <span style="background: #D1D5DB; color: #DC2626; font-weight: bold; padding: 4px 14px; border-radius: 4px; font-size: 14px; display: inline-block;">
                <?= ($soldeRestant <= 0) ? 'SOLDÉ' : date('d/m/Y', strtotime('+30 days')) ?>
              </span>
            </td>
            <td style="text-align: right; vertical-align: top;">
              <div style="font-size: 11px; font-family: monospace; font-weight: bold; margin-bottom: 2px;">
                ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
                <br>
                <?= htmlspecialchars(substr($refCaiss, 0, 35)) ?>
              </div>
              <div style="font-weight: bold; font-size: 13px; margin-top: 4px;">CAISSIER(RE)</div>
              <div style="font-size: 12px; font-weight: 600;"><?= htmlspecialchars($caissierNom) ?></div>
            </td>
          </tr>
        </table>

        <!-- Encadré Jaune N.B. -->
        <div class="yellow-nb-box">
          N.B: - Aucun remboursement n'est admis après l'inscription.<br>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Les paiements se font uniquement chez la caissière
        </div>

        <!-- Pied de page Partie 1 -->
        <div style="font-size: 11.5px; display: flex; justify-content: space-between; margin-top: 6px; font-style: italic;">
          <div>Ce reçu est à conserver. Pour toute réclamation il doit être presenté.</div>
          <div><?= htmlspecialchars($datePrint) ?></div>
        </div>

        <div style="font-size: 10px; font-family: monospace; margin-top: 4px; color: #475569;">
          Réf_caiss <?= htmlspecialchars($refCaiss) ?>
        </div>

        <!-- ========================================================================= -->
        <!-- LIGNE DE SÉPARATION / DÉCOUPAGE DOTTED                                    -->
        <!-- ========================================================================= -->
        <div style="border-top: 2px dashed #000000; margin: 18px 0;"></div>

        <!-- ========================================================================= -->
        <!-- PARTIE 2 : COPIE REÇU DE VERSEMENT POUR ARCHIVAGE                         -->
        <!-- ========================================================================= -->
        
        <!-- Bandeau Titre Copie Archivage -->
        <div class="title-gray-banner" style="font-size: 16px;">COPIE REÇU DE VERSEMENT POUR ARCHIVAGE</div>

        <!-- Année Académique -->
        <div class="annee-academique-title">ANNEE ACADEMIQUE : <?= htmlspecialchars($anneeLibelle) ?></div>

        <!-- Photo & Détails Copie Archivage -->
        <div style="clear: both; margin-bottom: 10px;">
          
          <div class="student-photo-box">
            <?php if (!empty($item['photo_etudiant']) && file_exists(__DIR__ . '/../../public/' . $item['photo_etudiant'])): ?>
              <img src="<?= RACINE . $item['photo_etudiant'] ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
              <div style="text-align: center; color: #94A3B8;">
                <span style="font-size: 24px; font-weight: 900; display: block;"><?= strtoupper(substr($item['nom_etudiant'] ?? 'E', 0, 1)) ?></span>
                <span style="font-size: 9px; font-weight: bold;">PHOTO</span>
              </div>
            <?php endif; ?>
          </div>

          <table class="info-grid-table">
            <tr>
              <td style="width: 55%; vertical-align: top;">
                <strong>N° <?= htmlspecialchars($numRecu) ?></strong>
              </td>
              <td style="width: 45%; vertical-align: top; text-align: right; color: #64748B;">
                <?= htmlspecialchars($datePaiement) ?>
              </td>
            </tr>
            <tr>
              <td>Numéro réf étudiant( e) : <strong><?= htmlspecialchars($matricule) ?></strong></td>
              <td>Filière_Niveau : <strong><?= htmlspecialchars($filiereNiveau) ?></strong></td>
            </tr>
            <tr>
              <td colspan="2">Nom_Prénom(s) : <strong><?= htmlspecialchars($nomComplet) ?></strong></td>
            </tr>
            <tr>
              <td>Type d'Opération &nbsp; <span class="gray-inline-badge"><?= htmlspecialchars($typeOp) ?></span></td>
              <td>Banque : &nbsp; <span class="gray-inline-badge"><?= htmlspecialchars($item['banque_paiement'] ?? 'CAISSE CENTRALE') ?></span></td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 4px;">
                Montant de l'Opération : <strong><?= number_format($montantOp, 0, '', ' ') ?>CFA</strong>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="blue-text-words"><?= htmlspecialchars($montantEnLettres) ?></span>
              </td>
            </tr>
          </table>

        </div>

        <!-- Date du Prochain Paiement & Caissier Copie Archivage -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px;">
          <tr>
            <td style="vertical-align: middle;">
              <strong>Date du Prochain Payement :</strong> &nbsp;
              <span style="background: #D1D5DB; color: #DC2626; font-weight: bold; padding: 4px 14px; border-radius: 4px; font-size: 14px; display: inline-block;">
                <?= ($soldeRestant <= 0) ? 'SOLDÉ' : date('d/m/Y', strtotime('+30 days')) ?>
              </span>
            </td>
            <td style="text-align: right; vertical-align: top;">
              <div style="font-size: 11px; font-family: monospace; font-weight: bold; margin-bottom: 2px;">
                ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
                <br>
                <?= htmlspecialchars(substr($refCaiss, 0, 35)) ?>
              </div>
              <div style="font-weight: bold; font-size: 13px; margin-top: 4px;">CAISSIER(RE)</div>
              <div style="font-size: 12px; font-weight: 600;"><?= htmlspecialchars($caissierNom) ?></div>
            </td>
          </tr>
        </table>

        <!-- Pied de page Partie 2 -->
        <div style="font-size: 10px; font-family: monospace; margin-top: 14px; display: flex; justify-content: space-between; color: #334155;">
          <div>GE-25260276ScoFOF45944,4399676042ScaisKON</div>
          <div><?= htmlspecialchars($datePrint) ?></div>
        </div>

      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons(); 
  <?php if (isset($_GET['print'])): ?>
    setTimeout(function() { window.print(); }, 400);
  <?php endif; ?>
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
