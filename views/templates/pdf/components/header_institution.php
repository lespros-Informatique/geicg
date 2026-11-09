<?php
/**
 * GEICG - Composant d'En-tête Institutionnel Partagé pour mPDF
 * Conforme Directive Système Règle 1 (Zero Hardcoding) & Règle 9 (Configurations & Logos)
 * 
 * Paramètres optionnels acceptés en amont :
 * - $headerMode : 'standard' (défaut) | 'compact' (pour reçus et coupons découpés)
 * - $showHeaderDivider : bool (défaut: true)
 * - $logoCustomHeight : string (ex: '50px')
 */

// 1. Initialisation défensive de la configuration (Règle 9)
if (empty($etablissement) || !is_array($etablissement)) {
    if (class_exists('ModelConfiguration')) {
        $etablissement = (new ModelConfiguration())->getConfig();
    } elseif (file_exists(__DIR__ . '/../../../../models/configurations/ModelConfiguration.php')) {
        require_once __DIR__ . '/../../../../models/configurations/ModelConfiguration.php';
        $etablissement = (new ModelConfiguration())->getConfig();
    } else {
        $etablissement = [];
    }
}

// 2. Extraction des métadonnées officielles
$nomEtab = $etablissement['nom_etablissement'] ?? ($etablissement['libelle_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION');
$sigleEtab = $etablissement['sigle_etablissement'] ?? 'GROUPE EICG';
$sloganEtab = $etablissement['slogan_etablissement'] ?? '';
$numAutorisation = $etablissement['numero_autorisation'] ?? ($etablissement['numero_autorisation_etablissement'] ?? 'AGRÉÉ PAR L\'ÉTAT ET LE FDFP');
$adresseEtab = $etablissement['adresse_etablissement'] ?? 'Bouaké - Côte d\'Ivoire';
$bpEtab = $etablissement['boite_postale'] ?? '';
$contactsEtab = !empty($etablissement['contacts_formatted']) 
    ? $etablissement['contacts_formatted'] 
    : ($etablissement['telephone_mobile'] ?? ($etablissement['telephone_etablissement'] ?? '27 31 62 40 57 / 07 79 37 37 38'));
$emailEtab = $etablissement['email_etablissement'] ?? '';
$siteWebEtab = $etablissement['site_web'] ?? 'www.groupe-eicg.net';

// 3. Résolution dynamique et absolue du logo officiel
$resolvedLogo = null;
if (!empty($etablissement['logo_etablissement'])) {
    $cleanPath = ltrim($etablissement['logo_etablissement'], '/');
    $cand1 = __DIR__ . '/../../../../' . $cleanPath;
    $cand2 = __DIR__ . '/../../../../public/' . $cleanPath;
    if (file_exists($cand1)) {
        $resolvedLogo = $cand1;
    } elseif (file_exists($cand2)) {
        $resolvedLogo = $cand2;
    }
}
if (!$resolvedLogo) {
    $defaultJpg = __DIR__ . '/../../../../public/assets/images/logo/logo_eicg.jpg';
    $defaultPng = __DIR__ . '/../../../../public/assets/images/logo/logo_eicg.png';
    if (file_exists($defaultJpg)) {
        $resolvedLogo = $defaultJpg;
    } elseif (file_exists($defaultPng)) {
        $resolvedLogo = $defaultPng;
    }
}

// 4. Configuration du mode (Standard vs Compact)
$isCompact = (isset($headerMode) && $headerMode === 'compact');
$logoStyle = $isCompact 
    ? 'max-height: ' . ($logoCustomHeight ?? '42px') . '; max-width: 105px;' 
    : 'max-height: ' . ($logoCustomHeight ?? '52px') . '; max-width: 135px;';
$titleFontSize = $isCompact ? '10pt' : '11pt';
$subFontSize = $isCompact ? '7.5pt' : '8pt';
$contactsFontSize = $isCompact ? '7pt' : '7.5pt';
$tableMarginBottom = $isCompact ? '2px' : '3px';
?>

<!-- EN-TÊTE INSTITUTIONNEL UNIFIÉ GEICG -->
<table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: <?= $tableMarginBottom ?>;">
  <tr>
    <td style="width: <?= $isCompact ? '16%' : '18%' ?>; text-align: left; vertical-align: middle;">
      <?php if (!empty($resolvedLogo) && file_exists($resolvedLogo)): ?>
        <img src="<?= $resolvedLogo ?>" style="<?= $logoStyle ?>" alt="<?= htmlspecialchars($sigleEtab) ?>">
      <?php else: ?>
        <div style="font-size: <?= $isCompact ? '11pt' : '13pt' ?>; font-weight: bold; color: #1E3A5F;"><?= htmlspecialchars($sigleEtab) ?></div>
      <?php endif; ?>
    </td>
    <td style="width: <?= $isCompact ? '84%' : '82%' ?>; text-align: center; vertical-align: middle;">
      <div class="inst-title" style="font-size: <?= $titleFontSize ?>; font-weight: bold; color: #990000; text-transform: uppercase; letter-spacing: 0.4px; line-height: 1.15; margin: 0;">
        <?= htmlspecialchars($nomEtab) ?>
      </div>
      <div class="inst-subtitle" style="font-size: <?= $subFontSize ?>; font-weight: bold; color: #0369A1; margin-top: 1px; letter-spacing: 0.2px;">
        <?= htmlspecialchars(strpos($numAutorisation, 'AGRÉ') !== false ? $numAutorisation : 'AGRÉMENT N° ' . $numAutorisation) ?>
      </div>
      <div class="inst-contacts" style="font-size: <?= $contactsFontSize ?>; color: #475569; margin-top: 1px; line-height: 1.2;">
        <?= htmlspecialchars($adresseEtab) ?>
        <?php if (!empty($bpEtab)): ?> &bull; <?= htmlspecialchars($bpEtab) ?><?php endif; ?>
        &nbsp;&bull;&nbsp; Contacts : <?= htmlspecialchars($contactsEtab) ?>
        <?php if (!empty($emailEtab)): ?> &nbsp;&bull;&nbsp; Email : <?= htmlspecialchars($emailEtab) ?><?php endif; ?>
        <?php if (!empty($siteWebEtab)): ?> &nbsp;&bull;&nbsp; Site : <?= htmlspecialchars($siteWebEtab) ?><?php endif; ?>
      </div>
    </td>
  </tr>
</table>

<?php if (!isset($showHeaderDivider) || $showHeaderDivider !== false): ?>
  <div class="divider-line" style="height: 1.5px; background-color: #1E3A5F; margin-top: <?= $isCompact ? '2px' : '3px' ?>; margin-bottom: <?= $isCompact ? '4px' : '6px' ?>;"></div>
<?php endif; ?>

