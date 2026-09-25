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

$isFirstPayment = isset($isFirstPayment) ? (bool)$isFirstPayment : true;
$totalScolariteCumul = (float)($totalScolariteCumul ?? $totalPayeCumul);
$totalFraisAnnexesCumul = (float)($totalFraisAnnexesCumul ?? 0);

$trancheCodeItem = $item['tranche_code'] ?? '';
$catItem = strtolower(trim($item['categorie_paiement'] ?? ''));
$isFAItem = ($trancheCodeItem === 'FRAIS_ANNEXES' || $catItem === 'frais_annexes');

if ($isFirstPayment) {
    $mainRecuTitle = "REÇU D'INSCRIPTION";
    $copieRecuTitle = "COPIE REÇU DE VERSEMENT POUR ARCHIVAGE";
    $pageTitle = "Reçu d'Inscription N° " . $numRecu;
    $typeOp = "INSCRIPTION";

    $opScolarite = isset($firstGroupScolarite) ? (float)$firstGroupScolarite : 0;
    $opDroit = isset($firstGroupFraisAnnexes) ? (float)$firstGroupFraisAnnexes : 0;

    if ($opScolarite == 0 && $opDroit == 0) {
        if ($isFAItem) {
            $opDroit = $montantOp;
            $opScolarite = 0;
        } else {
            $opFA = (float)($item['montant_frais_annexes'] ?? 0);
            if ($opFA > 0) {
                $opDroit = $opFA;
                $opScolarite = max(0, $montantOp - $opFA);
            } elseif ($montantOp > 80000 && $montantOp > $scolarite) {
                $opDroit = 80000;
                $opScolarite = max(0, $montantOp - 80000);
            } elseif ($montantOp == 105000) {
                $opScolarite = 25000;
                $opDroit = 80000;
            } else {
                $opScolarite = $montantOp;
                $opDroit = 0;
            }
        }
    }

    $montantOpAfficher = $opScolarite + $opDroit;
    if ($montantOpAfficher <= 0) {
        $montantOpAfficher = $montantOp;
    }
} else {
    $mainRecuTitle = "REÇU DE VERSEMENT";
    $copieRecuTitle = "COPIE REÇU DU 1er VERSEMENT POUR ARCHIVAGE";
    $pageTitle = "Reçu de Versement N° " . $numRecu;
    $typeOp = !empty($item['libelle_tranche']) ? $item['libelle_tranche'] : (!empty($item['type_paiement']) ? $item['type_paiement'] : ($isFAItem ? 'Frais Annexes' : 'SCOLARITE'));

    $opScolarite = 0;
    $opDroit = 0;
    $montantOpAfficher = $montantOp;
}

$montantEnLettres = Validator::numberToWordsFCFA($montantOpAfficher);

$refSeed = !empty($item['code_paiement']) ? $item['code_paiement'] : $numRecu;
$refCaissHash1 = sprintf("%05d", abs(crc32($refSeed . '_sco')) % 90000 + 10000);
$refCaissHash2 = sprintf("%010d", abs(crc32($refSeed . '_cais')) % 9000000000 + 1000000000);
$refCaiss = !empty($item['reference_paiement']) 
    ? $item['reference_paiement'] 
    : ($numRecu . 'ScoFOF' . $refCaissHash1 . ',' . $refCaissHash2 . 'ScaisKON');

$barcodeCode = !empty($item['recu_numero_paiement']) 
    ? $item['recu_numero_paiement'] 
    : (!empty($item['code_paiement']) ? $item['code_paiement'] : $numRecu);

// Résolution robuste de la photo de l'étudiant
$studentPhotoUrl = '';
$rawPhoto = !empty($item['photo_inscription']) ? $item['photo_inscription'] : (!empty($item['photo_etudiant']) ? $item['photo_etudiant'] : '');

if (!empty($rawPhoto)) {
    $cleanPhotoPath = ltrim($rawPhoto, '/');
    if (strpos($cleanPhotoPath, 'public/') === 0) {
        if (file_exists(__DIR__ . '/../../' . $cleanPhotoPath)) {
            $studentPhotoUrl = RACINE . $cleanPhotoPath;
        }
    } else {
        if (file_exists(__DIR__ . '/../../public/' . $cleanPhotoPath)) {
            $studentPhotoUrl = RACINE . 'public/' . $cleanPhotoPath;
        } elseif (file_exists(__DIR__ . '/../../' . $cleanPhotoPath)) {
            $studentPhotoUrl = RACINE . $cleanPhotoPath;
        }
    }
}

if (empty($studentPhotoUrl) && !empty($item['nom_etudiant'])) {
    $slugNom = preg_replace('/[^a-zA-Z0-9_]/', '_', $item['nom_etudiant']);
    $anneeFolder = !empty($anneeLibelle) ? $anneeLibelle : '2025-2026';
    $potentialPath = 'public/uploads/photos_inscriptions/' . $anneeFolder . '/' . $slugNom . '.png';
    if (file_exists(__DIR__ . '/../../' . $potentialPath)) {
        $studentPhotoUrl = RACINE . $potentialPath;
    }
}

// Générateur vectoriel SVG Code 128 (Subset B) natif, net et conforme pour impression et lecture scanner
// Générateur de code-barres compact et proportionnel via Picqer Barcode Generator
if (!function_exists('generatePicqerBarcodeHtml')) {
    function generatePicqerBarcodeHtml($code, $height = 24) {
        $code = (string)$code;
        if (empty($code)) $code = 'GE-25260276';
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $pngData = $generator->getBarcode($code, $generator::TYPE_CODE_128, 1, $height);
            return '<img src="data:image/png;base64,' . base64_encode($pngData) . '" alt="Code-barres ' . htmlspecialchars($code) . '" style="display:inline-block; vertical-align:middle; height:' . $height . 'px; width:auto; max-width:140px;">';
        } catch (\Throwable $e) {
            return '<div style="font-family:monospace; font-weight:bold; font-size:10px;">' . htmlspecialchars($code) . '</div>';
        }
    }
}

// Logo (dynamique depuis la base ou fallback logo_eicg.jpg)
$logoSrc = '';
try {
    $dbEtab = (new Database())->getCon();
    $stmtEtab = $dbEtab->query("SELECT logo_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1");
    $rawEtabLogo = $stmtEtab->fetchColumn();
    if (!empty($rawEtabLogo)) {
        $cleanPath = ltrim($rawEtabLogo, '/');
        if (file_exists(__DIR__ . '/../../' . $cleanPath)) {
            $logoSrc = RACINE . $cleanPath;
        }
    }
} catch (Exception $e) {
    // continue
}
if (empty($logoSrc)) {
    if (file_exists(__DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg')) {
        $logoSrc = RACINE . 'public/assets/images/logo/logo_eicg.jpg';
    } elseif (file_exists(__DIR__ . '/../../public/uploads/logos/logo_1787358264.jpg')) {
        $logoSrc = RACINE . 'public/uploads/logos/logo_1787358264.jpg';
    }
}
?>
<style>
@media print {
  body { background: #FFFFFF !important; color: #000000 !important; font-family: Arial, Helvetica, sans-serif !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
  .sidebar, .sidebar *, .nav-header, .page-header-actions, .no-print, header, header *, nav, nav *, .main-nav, .main-nav *, .topbar, .topbar *, .dropdown-panel, .dropdown-panel * { display: none !important; visibility: hidden !important; }
  .app-layout, .main-content, .content-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; box-shadow: none !important; }
  .receipt-page-container { border: 2px solid #800000 !important; padding: 12px !important; margin: 0 !important; box-shadow: none !important; width: 100% !important; box-sizing: border-box !important; }
  .institution-logo-img { max-width: 145px !important; max-height: 70px !important; display: block !important; }
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

.institution-logo-img {
  max-width: 145px;
  max-height: 70px;
  object-fit: contain;
  display: block;
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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= htmlspecialchars($pageTitle) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Étudiant : <strong><?= htmlspecialchars($nomComplet) ?></strong> &bull; Impression conforme au modèle standard</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux paiements
          </a>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu
          </button>
        </div>
      </div>

      <!-- FRAME DU REÇU IMPRIMABLE -->
      <div class="receipt-page-container receipt-outer-frame">
        
        <!-- ========================================================================= -->
        <!-- PARTIE 1 : REÇU D'INSCRIPTION OU DE VERSEMENT                            -->
        <!-- ========================================================================= -->
        
        <!-- En-tête -->
        <table class="receipt-header-table">
          <tr>
            <td style="width: 155px; vertical-align: middle; padding-right: 14px;">
              <?php if (!empty($logoSrc)): ?>
                <img src="<?= $logoSrc ?>" alt="Logo GROUPE EICG" class="institution-logo-img">
              <?php else: ?>
                <div class="logo-eicg-box">
                  GROUPE<br>EICG
                </div>
              <?php endif; ?>
            </td>
            <td style="vertical-align: top; text-align: center;">
              <h1 class="institution-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</h1>
              <div class="institution-subtitle">Agréé par l'Etat et le FDFP</div>
              <div class="institution-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
            </td>
          </tr>
        </table>

        <!-- Bandeau Titre -->
        <div class="title-gray-banner"><?= htmlspecialchars($mainRecuTitle) ?></div>

        <!-- Année Académique -->
        <div class="annee-academique-title">ANNEE ACADEMIQUE : <?= htmlspecialchars($anneeLibelle) ?></div>

        <!-- Photo & Détails Étudiant -->
        <div style="clear: both; margin-bottom: 10px;">
          
          <div class="student-photo-box">
            <?php if (!empty($studentPhotoUrl)): ?>
              <img src="<?= htmlspecialchars($studentPhotoUrl) ?>" alt="Photo de l'étudiant" style="width: 100%; height: 100%; object-fit: cover; display: block;">
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
                Montant de l'Opération : <strong><?= number_format($montantOpAfficher, 0, '', ' ') ?>CFA</strong>
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
              <th style="width: 24%; text-align: left;"></th>
              <th style="width: 19%; text-align: center;">OP. DU JOUR</th>
              <th style="width: 19%; text-align: center;">Total à payer</th>
              <th style="width: 19%; text-align: center;">Total Versé</th>
              <th style="width: 19%; text-align: center;">Reste à Payer</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($isFirstPayment): ?>
              <tr>
                <td style="font-weight: bold;">SCOLARITE</td>
                <td style="text-align: center; font-weight: bold;"><?= $opScolarite > 0 ? number_format($opScolarite, 0, '', ' ') . 'CFA' : '' ?></td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($scolarite, 0, '', ' ') ?>CFA</td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($totalScolariteCumul, 0, '', ' ') ?>CFA</td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($soldeRestant, 0, '', ' ') ?>CFA</td>
              </tr>
              <tr>
                <td style="font-weight: bold;">Droit d'Inscription</td>
                <td style="text-align: center; font-weight: bold;"><?= $opDroit > 0 ? number_format($opDroit, 0, '', ' ') . 'CFA' : '' ?></td>
                <td style="background: #FFFFFF;"></td>
                <td style="text-align: center; font-weight: bold;"><?= $opDroit > 0 ? number_format($opDroit, 0, '', ' ') . 'CFA' : '' ?></td>
                <td style="background: #FFFFFF;"></td>
              </tr>
              <tr>
                <td style="font-weight: bold;">AUTRES FRAIS</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            <?php else: ?>
              <tr>
                <td style="font-weight: bold;">SCOLARITE</td>
                <td></td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($scolarite, 0, '', ' ') ?>CFA</td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($totalScolariteCumul, 0, '', ' ') ?>CFA</td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($soldeRestant, 0, '', ' ') ?>CFA</td>
              </tr>
              <tr>
                <td style="font-weight: bold;">Droit d'Inscription</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td style="font-weight: bold;">Versement</td>
                <td style="text-align: center; font-weight: bold;"><?= number_format($montantOpAfficher, 0, '', ' ') ?>CFA</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            <?php endif; ?>
            <tr class="fin-total-row">
              <td style="font-weight: bold; background: #404040;">TOTAL</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($montantOpAfficher, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($scolarite, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($totalPayeCumul, 0, '', ' ') ?>CFA</td>
              <td style="text-align: center; font-weight: bold; background: #404040;"><?= number_format($soldeRestant, 0, '', ' ') ?>CFA</td>
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
              <div style="display: inline-block; text-align: center; margin-bottom: 3px;">
                <div style="line-height: 1;">
                  <?= generatePicqerBarcodeHtml($barcodeCode, 32, 1.35) ?>
                </div>
                <div style="font-size: 10px; font-family: 'Courier New', Courier, monospace; font-weight: 700; letter-spacing: 1px; margin-top: 2px; color: #000000;">
                  * <?= htmlspecialchars($barcodeCode) ?> *
                </div>
              </div>
              <div style="font-weight: bold; font-size: 13px; margin-top: 2px;">CAISSIER(RE)</div>
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
        <!-- PARTIE 2 : COPIE REÇU POUR ARCHIVAGE                                      -->
        <!-- ========================================================================= -->
        
        <!-- Bandeau Titre Copie Archivage -->
        <div class="title-gray-banner" style="font-size: 16px;"><?= htmlspecialchars($copieRecuTitle) ?></div>

        <!-- Année Académique -->
        <div class="annee-academique-title">ANNEE ACADEMIQUE : <?= htmlspecialchars($anneeLibelle) ?></div>

        <!-- Photo & Détails Copie Archivage -->
        <div style="clear: both; margin-bottom: 10px;">
          
          <div class="student-photo-box">
            <?php if (!empty($studentPhotoUrl)): ?>
              <img src="<?= htmlspecialchars($studentPhotoUrl) ?>" alt="Photo de l'étudiant" style="width: 100%; height: 100%; object-fit: cover; display: block;">
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
              <td>
                Banque : &nbsp; <span class="gray-inline-badge"><?= htmlspecialchars($item['mode_paiement_fmt'] ?? ($item['mode_paiement'] ?? 'CAISSE CENTRALE')) ?></span>
                &nbsp;&nbsp;&nbsp;&nbsp; Reste à Payer &nbsp; <strong><?= number_format($soldeRestant, 0, '', ' ') ?>CFA</strong>
              </td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 4px;">
                Montant de l'Opération : <strong><?= number_format($montantOpAfficher, 0, '', ' ') ?>CFA</strong>
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
              <div style="display: inline-block; text-align: center; margin-bottom: 3px;">
                <div style="line-height: 1;">
                  <?= generatePicqerBarcodeHtml($barcodeCode, 32, 1.35) ?>
                </div>
                <div style="font-size: 10px; font-family: 'Courier New', Courier, monospace; font-weight: 700; letter-spacing: 1px; margin-top: 2px; color: #000000;">
                  * <?= htmlspecialchars($barcodeCode) ?> *
                </div>
              </div>
              <div style="font-weight: bold; font-size: 13px; margin-top: 2px;">CAISSIER(RE)</div>
              <div style="font-size: 12px; font-weight: 600;"><?= htmlspecialchars($caissierNom) ?></div>
            </td>
          </tr>
        </table>

        <!-- Pied de page Partie 2 -->
        <div style="font-size: 10px; font-family: monospace; margin-top: 14px; display: flex; justify-content: space-between; color: #334155;">
          <div>Réf_caiss <?= htmlspecialchars($refCaiss) ?></div>
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
