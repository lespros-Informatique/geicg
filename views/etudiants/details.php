<?php
require_once __DIR__ . '/../../public/inc/header.php';

$parent = $parent ?? [];
$inscription = $inscription ?? [];
$inscriptionsHistory = $inscriptionsHistory ?? [];
$etablissement = $etablissement ?? [];
$dossierPieces = $dossierPieces ?? [];
$paiementsScolarite = $paiementsScolarite ?? ($paiements ?? []);
$paiementsAnnexes = $paiementsAnnexes ?? [];
$absences = $absences ?? [];
$kits = $kits ?? [];
$bulletins = $bulletins ?? [];
$deliberations = $deliberations ?? [];

$scolariteTotale = (float)($scolariteTotale ?? 0);
$totalPaye = (float)($totalPaye ?? 0);
$soldeRestant = (float)($soldeRestant ?? 0);
$totalFraisAnnexesPaye = (float)($totalFraisAnnexesPaye ?? 0);
$tauxPaiement = ($scolariteTotale > 0) ? min(100, (int)round(($totalPaye / $scolariteTotale) * 100)) : 0;

$encryptedId = (string)($encryptedId ?? '');
$encryptedInscriptionId = (string)($encryptedInscriptionId ?? '');
$encryptedClasseId = (string)($encryptedClasseId ?? '');
$encryptedAnneeId = (string)($encryptedAnneeId ?? '');

$nomComplet = trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? ''));
$codeEtudiant = (string)($item['code_etudiant'] ?? '');
$matricule = (string)($item['matricule_etudiant'] ?? '');
$isActif = (strtolower(trim((string)($item['statut_etudiant'] ?? ''))) === STATUTS::ACTIF);

// Initiales et photo (priorité à photo_inscription puis photo_etudiant)
$parts = preg_split('/\s+/', $nomComplet) ?: [];
$initials = '';
foreach (array_slice($parts, 0, 2) as $pt) {
    if ($pt !== '') $initials .= mb_strtoupper(mb_substr($pt, 0, 1, 'UTF-8'), 'UTF-8');
}
if ($initials === '') $initials = 'ET';

$photoUrl = '';
$photoCandidates = [
    $inscription['photo_inscription'] ?? '',
    $item['photo_etudiant'] ?? '',
];
foreach ($photoCandidates as $cand) {
    if (!empty($cand)) {
        $cleanRel = ltrim((string)$cand, '/');
        if (file_exists(__DIR__ . '/../../' . $cleanRel)) {
            $photoUrl = url($cleanRel);
            break;
        } elseif (file_exists(__DIR__ . '/../../public/' . $cleanRel)) {
            $photoUrl = url('public/' . $cleanRel);
            break;
        } elseif (strpos((string)$cand, 'http') === 0) {
            $photoUrl = (string)$cand;
            break;
        }
    }
}
$hasPhoto = ($photoUrl !== '');
$placeholderPhotoUrl = placeholderUrl('etudiant');
$finalPhotoUrl = $hasPhoto ? $photoUrl : $placeholderPhotoUrl;

// Établissement dynamique depuis table configurations (Règle 9)
$etabNom = (string)($etablissement['nom_etablissement'] ?? '');
$etabSigle = (string)($etablissement['sigle_etablissement'] ?? '');
$etabPays = (string)($etablissement['pays_etablissement'] ?? '');
$etabAdresse = trim((string)($etablissement['adresse_etablissement'] ?? '') . (!empty($etablissement['ville_etablissement']) ? ', ' . $etablissement['ville_etablissement'] : ''));
$etabContacts = (string)($etablissement['contacts_formatted'] ?? trim(($etablissement['telephone_fixe'] ?? '') . (!empty($etablissement['telephone_mobile']) ? ' / ' . $etablissement['telephone_mobile'] : '')));
$etabTitreDG = (string)($etablissement['titre_direction_generale'] ?? '');
$etabNomDG = (string)($etablissement['nom_directeur_general'] ?? '');
$etabLogoUrl = '';
if (!empty($etablissement['logo_etablissement'])) {
    $cleanLogo = ltrim((string)$etablissement['logo_etablissement'], '/');
    if (file_exists(__DIR__ . '/../../' . $cleanLogo)) {
        $etabLogoUrl = url($cleanLogo);
    } elseif (file_exists(__DIR__ . '/../../public/' . $cleanLogo)) {
        $etabLogoUrl = url('public/' . $cleanLogo);
    }
}

// Sexe & état civil (zéro fallback arbitraire)
$sexeRaw = strtoupper(trim((string)($item['sexe_etudiant'] ?? '')));
$sexeLibelle = $sexeRaw !== ''
    ? (($sexeRaw === 'M' || $sexeRaw === 'MASCULIN' || $sexeRaw === 'HOMME') ? 'Masculin' : (($sexeRaw === 'F' || $sexeRaw === 'FEMININ' || $sexeRaw === 'FEMME') ? 'Féminin' : $sexeRaw))
    : 'Non précisé';
$isF = ($sexeRaw !== '' && strpos($sexeRaw, 'F') !== false);

$dobRaw = (string)($item['date_naissance_etudiant'] ?? '');
$dobFormatted = ($dobRaw !== '' && $dobRaw !== '0000-00-00') ? date('d/m/Y', strtotime($dobRaw)) : 'Non renseignée';

// Inscription active
$classeLibelle = (string)($inscription['libelle_classe'] ?? '');
$filiereLibelle = (string)($inscription['libelle_filiere'] ?? '');
$niveauLibelle = (string)($inscription['libelle_niveau'] ?? '');
$cycleLibelle = (string)($inscription['libelle_cycle'] ?? '');
$anneeLibelle = (string)($inscription['libelle_annee'] ?? '');
$dateFinAnnee = !empty($inscription['date_fin_annee']) && $inscription['date_fin_annee'] !== '0000-00-00'
    ? date('d/m/Y', strtotime($inscription['date_fin_annee']))
    : '';
$affRaw = strtolower(trim((string)($inscription['affectation_etat'] ?? '')));
$hasInscr = !empty($inscription['code_inscription']);
$isAffecte = ($affRaw === 'oui' || $affRaw === 'affecte');
$statutInscr = strtolower(trim((string)($inscription['statut_inscription'] ?? '')));

// Dossier administratif
$totalPieces = count($dossierPieces);
$deposedCount = 0;
foreach ($dossierPieces as $dp) {
    if (($dp['statut_depot'] ?? '') === 'depose') $deposedCount++;
}
$tauxDossier = ($totalPieces > 0) ? (int)round(($deposedCount / $totalPieces) * 100) : 0;
$isDossierComplet = ($totalPieces > 0 && $deposedCount === $totalPieces);

// Absences
$totalAbsCount = count($absences);
$totalAbsHeures = 0.0;
$totalAbsJust = 0.0;
$totalAbsInjust = 0.0;
foreach ($absences as $ab) {
    $dur = (float)($ab['duree_heures'] ?? 0);
    $totalAbsHeures += $dur;
    if (strtolower((string)($ab['justifiee'] ?? 'non')) === 'oui') {
        $totalAbsJust += $dur;
    } else {
        $totalAbsInjust += $dur;
    }
}

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};
$fmtMoney = static function ($m): string {
    return number_format((float)$m, 0, ',', ' ') . ' FCFA';
};

// Kits & Accessoires
$totalKits = count($kits);
$kitsRetiresCount = 0;
foreach ($kits as $kt) {
    if (strtolower((string)($kt['etat_retrait'] ?? '')) === 'retire') {
        $kitsRetiresCount++;
    }
}
$kitsEnAttenteCount = max(0, $totalKits - $kitsRetiresCount);

// Permissions RBAC
$canEditEtudiant = Auth::can(PERMISSIONS::EDIT_ETUDIANT) || Auth::can(PERMISSIONS::MANAGE_ETUDIANTS);
$canChangerClasse = Auth::can(PERMISSIONS::CHANGER_CLASSE_ETUDIANT) || Auth::can(PERMISSIONS::MANAGE_ETUDIANTS);
$canViewClasses = Auth::can(PERMISSIONS::VIEW_CLASSES);
$canViewAnnees = Auth::can(PERMISSIONS::VIEW_ANNEES);
$canViewFinances = Auth::can(PERMISSIONS::VIEW_PAIEMENTS) || Auth::can(PERMISSIONS::VIEW_COMPTABILITE_ETUDIANTS) || Auth::can(PERMISSIONS::MANAGE_PAIEMENTS);
$canRecordPaiement = Auth::can(PERMISSIONS::RECORD_PAIEMENTS) || Auth::can(PERMISSIONS::MANAGE_PAIEMENTS);
$canViewAbsences = Auth::can(PERMISSIONS::VIEW_ABSENCES) || Auth::can(PERMISSIONS::MANAGE_ABSENCES);
$canViewDossiers = Auth::can(PERMISSIONS::VIEW_DEPOT_DOSSIERS) || Auth::can(PERMISSIONS::MANAGE_DEPOT_DOSSIERS);
$canViewKits = Auth::can(PERMISSIONS::VIEW_REMISE_KITS) || Auth::can(PERMISSIONS::MANAGE_REMISE_KITS) || Auth::can(PERMISSIONS::VIEW_ACCESSOIRES) || Auth::can(PERMISSIONS::MANAGE_ACCESSOIRES);
$canManageKits = Auth::can(PERMISSIONS::MANAGE_REMISE_KITS);
$canViewBulletins = Auth::can(PERMISSIONS::VIEW_BULLETINS) || Auth::can(PERMISSIONS::GENERATE_BULLETINS) || Auth::can(PERMISSIONS::VIEW_NOTES);
$canUploadPhoto = $hasInscr && (
    $hasPhoto
        ? Auth::can(PERMISSIONS::REPRENDRE_PHOTO_INSCRIPTION)
        : (Auth::can(PERMISSIONS::MANAGE_INSCRIPTIONS_SANS_PHOTO) || Auth::can(PERMISSIONS::MANAGE_INSCRIPTIONS))
);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
          <div style="width: 60px; height: 68px; border-radius: 12px; border: 2px solid #CBD5E1; overflow: hidden; background: #F8FAFC; flex-shrink: 0; box-shadow: 0 2px 6px rgba(15,23,42,0.08);">
            <img class="etu-photo-live-img" src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo <?= htmlspecialchars($nomComplet) ?>" onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholderPhotoUrl) ?>';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
          </div>

          <div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
                <?= htmlspecialchars($nomComplet ?: '-') ?>
              </h1>
              <?php if ($matricule !== ''): ?>
                <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($matricule) ?></code>
              <?php endif; ?>
              <?php if ($hasInscr): ?>
                <span style="background: <?= $isAffecte ? '#DCFCE7' : '#E0F2FE' ?>; color: <?= $isAffecte ? '#15803D' : '#0369A1' ?>; border: 1px solid <?= $isAffecte ? '#86EFAC' : '#BAE6FD' ?>; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">
                  <?= $isAffecte ? 'Affecté(e)' : 'Non Affecté(e)' ?>
                </span>
              <?php endif; ?>
              <?php if ($isActif): ?>
                <span style="display: inline-flex; align-items: center; gap: 4px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">
                  <i data-lucide="check-circle-2" style="width: 13px; height: 13px;"></i> Actif
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 4px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">
                  <i data-lucide="x-circle" style="width: 13px; height: 13px;"></i> Inactif
                </span>
              <?php endif; ?>
            </div>
            <p style="color: #64748B; font-size: 13px; margin: 6px 0 0 0;">
              Classe :
              <?php if ($classeLibelle !== '' && $canViewClasses && $encryptedClasseId !== ''): ?>
                <a href="<?= url('classe/details/' . $encryptedClasseId) ?>" style="color: #1E3A5F; font-weight: 800; text-decoration: none;"><?= htmlspecialchars($classeLibelle) ?></a>
              <?php else: ?>
                <strong style="color: #0F172A;"><?= htmlspecialchars($classeLibelle !== '' ? $classeLibelle : 'Non inscrit') ?></strong>
              <?php endif; ?>
              <?php if ($filiereLibelle !== ''): ?>
                &bull; <strong style="color: #334155;"><?= htmlspecialchars($filiereLibelle) ?></strong>
              <?php endif; ?>
              <?php if ($anneeLibelle !== ''): ?>
                &bull; Session :
                <?php if ($canViewAnnees && $encryptedAnneeId !== ''): ?>
                  <a href="<?= url('annee/details/' . $encryptedAnneeId) ?>" style="color: #1E3A5F; font-weight: 800; text-decoration: none;"><?= htmlspecialchars($anneeLibelle) ?></a>
                <?php else: ?>
                  <strong style="color: #1E3A5F;"><?= htmlspecialchars($anneeLibelle) ?></strong>
                <?php endif; ?>
              <?php endif; ?>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('etudiant/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Retour
          </a>
          <a href="<?= url('etudiant/imprimerFichePdf/' . $encryptedId) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 13px; text-decoration: none;" title="Imprimer la fiche officielle de l'étudiant en PDF">
            <i data-lucide="file-text" style="width: 15px; height: 15px;"></i> Fiche PDF
          </a>
          <button type="button" id="btn-print-carte-scolaire-header" style="background: #FFFBEB; color: #B45309; border: 1.5px solid #FCD34D; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 13px; cursor: pointer;" title="Imprimer la carte d'identité scolaire (Recto / Verso)">
            <i data-lucide="id-card" style="width: 15px; height: 15px;"></i> Carte Scolaire
          </button>
          <?php if ($canChangerClasse): ?>
            <a href="<?= url('etudiant/changerClassePage/' . $encryptedId) ?>" style="background: #F0F9FF; color: #0369A1; border: 1.5px solid #BAE6FD; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 13px; text-decoration: none;">
              <i data-lucide="arrow-left-right" style="width: 15px; height: 15px;"></i> Changer Classe
            </a>
          <?php endif; ?>
          <?php if ($canEditEtudiant): ?>
            <a href="<?= url('etudiant/edition/' . $encryptedId) ?>" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
              <i data-lucide="edit" style="width: 15px; height: 15px;"></i> Modifier
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 22px;">

        <!-- KPI 1 : Scolarité (strictement dissociée des frais annexes) -->
        <?php if ($canViewFinances): ?>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= ($scolariteTotale > 0 && $soldeRestant <= 0) ? '#F0FDF4' : '#EFF6FF' ?>; color: <?= ($scolariteTotale > 0 && $soldeRestant <= 0) ? '#15803D' : '#1D4ED8' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="wallet" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Scolarité (<?= $tauxPaiement ?>%)</div>
              <div style="font-size: 19px; font-weight: 800; color: <?= ($scolariteTotale > 0 && $soldeRestant <= 0) ? '#15803D' : '#0F172A' ?>; line-height: 1.15; margin-top: 2px;">
                <?= $scolariteTotale > 0 ? ($soldeRestant <= 0 ? 'Soldée' : htmlspecialchars($fmtMoney($soldeRestant))) : '-' ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 5px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Versé : <strong style="color: #15803D;"><?= htmlspecialchars($fmtMoney($totalPaye)) ?></strong></span>
              <span>/ <?= htmlspecialchars($fmtMoney($scolariteTotale)) ?></span>
            </div>
            <div style="width: 100%; height: 5px; background: #E2E8F0; border-radius: 99px; overflow: hidden;">
              <div style="width: <?= $tauxPaiement ?>%; height: 100%; background: <?= $soldeRestant <= 0 && $scolariteTotale > 0 ? '#16A34A' : '#2563EB' ?>; border-radius: 99px;"></div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- KPI 2 : Parcours & Classe -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="graduation-cap" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Inscription Active</div>
              <div style="font-size: 19px; font-weight: 800; color: #0F172A; line-height: 1.15; margin-top: 2px;">
                <?= htmlspecialchars($classeLibelle !== '' ? $classeLibelle : 'Non inscrit') ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span><?= htmlspecialchars($niveauLibelle !== '' ? $niveauLibelle : '-') ?></span>
            <strong style="color: #7E22CE;"><?= count($inscriptionsHistory) ?> session<?= count($inscriptionsHistory) > 1 ? 's' : '' ?></strong>
          </div>
        </div>

        <!-- KPI 3 : Pièces Administratives -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $isDossierComplet ? '#F0FDF4' : '#FEF3C7' ?>; color: <?= $isDossierComplet ? '#15803D' : '#B45309' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="folder-check" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Dossier Physique</div>
              <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $deposedCount ?> <span style="font-size: 13px; font-weight: 600; color: #64748B;">/ <?= $totalPieces ?> pièces</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 5px; font-size: 12px; color: #475569;">
            <div style="display: flex; justify-content: space-between;">
              <span>Complétude :</span>
              <strong style="color: <?= $isDossierComplet ? '#15803D' : '#B45309' ?>;"><?= $isDossierComplet ? 'Complet' : ($tauxDossier . '%') ?></strong>
            </div>
            <div style="width: 100%; height: 5px; background: #E2E8F0; border-radius: 99px; overflow: hidden;">
              <div style="width: <?= $tauxDossier ?>%; height: 100%; background: <?= $isDossierComplet ? '#16A34A' : '#D97706' ?>; border-radius: 99px;"></div>
            </div>
          </div>
        </div>

        <!-- KPI 4 : Assiduité & Absences -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $totalAbsInjust > 0 ? '#FEF2F2' : '#F0FDF4' ?>; color: <?= $totalAbsInjust > 0 ? '#B91C1C' : '#15803D' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="calendar-x" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Assiduité &amp; Absences</div>
              <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= htmlspecialchars($fmtHeures($totalAbsHeures)) ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">(<?= $totalAbsCount ?>)</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Just. : <strong style="color: #15803D;"><?= htmlspecialchars($fmtHeures($totalAbsJust)) ?></strong></span>
            <span>Injust. : <strong style="color: <?= $totalAbsInjust > 0 ? '#B91C1C' : '#15803D' ?>;"><?= htmlspecialchars($fmtHeures($totalAbsInjust)) ?></strong></span>
          </div>
        </div>

      </div>

      <!-- BARRE D'ONGLETS EXÉCUTIFS -->
      <div style="display: flex; gap: 6px; flex-wrap: wrap; border-bottom: 2px solid #E2E8F0; margin-bottom: 22px;">
        <button type="button" class="etu-detail-tab-btn active" data-tab="tab-etu-overview" style="background: transparent; border: none; border-bottom: 3px solid #1E3A5F; color: #1E3A5F; padding: 10px 16px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="user" style="width: 15px; height: 15px;"></i> Identité &amp; Parcours
        </button>
        <?php if ($canViewFinances): ?>
        <button type="button" class="etu-detail-tab-btn" data-tab="tab-etu-finances" style="background: transparent; border: none; border-bottom: 3px solid transparent; color: #64748B; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="credit-card" style="width: 15px; height: 15px;"></i> Scolarité &amp; Paiements (<?= count($paiementsScolarite) ?>)
        </button>
        <?php endif; ?>
        <button type="button" class="etu-detail-tab-btn" data-tab="tab-etu-dossier" style="background: transparent; border: none; border-bottom: 3px solid transparent; color: #64748B; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="folder-check" style="width: 15px; height: 15px;"></i> Pièces du Dossier (<?= $deposedCount ?>/<?= $totalPieces ?>)
        </button>
        <?php if ($canViewKits): ?>
        <button type="button" class="etu-detail-tab-btn" data-tab="tab-etu-kits" style="background: transparent; border: none; border-bottom: 3px solid transparent; color: #64748B; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="package" style="width: 15px; height: 15px;"></i> Kits (<span id="tab-count-kits-retires"><?= $kitsRetiresCount ?></span>/<?= $totalKits ?>)
        </button>
        <?php endif; ?>
        <?php if ($canViewBulletins): ?>
        <button type="button" class="etu-detail-tab-btn" data-tab="tab-etu-bulletins" style="background: transparent; border: none; border-bottom: 3px solid transparent; color: #64748B; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="award" style="width: 15px; height: 15px;"></i> Bulletins (<?= count($bulletins) ?>)
        </button>
        <?php endif; ?>
        <?php if ($canViewAbsences): ?>
        <button type="button" class="etu-detail-tab-btn" data-tab="tab-etu-absences" style="background: transparent; border: none; border-bottom: 3px solid transparent; color: #64748B; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="calendar-x" style="width: 15px; height: 15px;"></i> Absences (<?= $totalAbsCount ?>)
        </button>
        <?php endif; ?>
        <button type="button" class="etu-detail-tab-btn" data-tab="tab-etu-carte" style="background: transparent; border: none; border-bottom: 3px solid transparent; color: #64748B; padding: 10px 16px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; margin-bottom: -2px;">
          <i data-lucide="id-card" style="width: 15px; height: 15px;"></i> Carte Scolaire
        </button>
      </div>

      <!-- ========================================================================= -->
      <!-- ONGLET 1 : IDENTITÉ, FILIATION & HISTORIQUE DES INSCRIPTIONS -->
      <!-- ========================================================================= -->
      <div id="tab-etu-overview" class="etu-detail-tab-pane">

        <!-- État Civil & Coordonnées -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <i data-lucide="user" style="width: 18px; height: 18px; color: #1E3A5F;"></i> État Civil, Photo d'Identité &amp; Coordonnées
          </h3>

          <div style="display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Portrait Photo Étudiant -->
            <div style="width: 156px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 12px;">
              <div style="width: 120px; height: 144px; border-radius: 10px; border: 2px solid #CBD5E1; overflow: hidden; background: #FFFFFF; box-shadow: 0 3px 10px rgba(15,23,42,0.08);">
                <img class="etu-photo-live-img" src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo d'identité <?= htmlspecialchars($nomComplet) ?>" onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholderPhotoUrl) ?>';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
              </div>
              <span id="badge-etu-photo-status" style="margin-top: 10px; background: <?= $hasPhoto ? '#DCFCE7' : '#FEF3C7' ?>; color: <?= $hasPhoto ? '#15803D' : '#B45309' ?>; border: 1px solid <?= $hasPhoto ? '#86EFAC' : '#FCD34D' ?>; padding: 2px 9px; border-radius: 10px; font-size: 11px; font-weight: 700;">
                <?= $hasPhoto ? 'Photo enregistrée' : 'Photo manquante' ?>
              </span>
              <?php if ($canUploadPhoto): ?>
                <input type="file" id="input-etu-detail-photo" accept="image/png,image/jpeg,image/webp" style="display: none;">
                <button type="button" id="btn-upload-etu-detail-photo" style="margin-top: 8px; width: 100%; background: #1E3A5F; color: #FFFFFF; border: none; border-radius: 7px; padding: 6px 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px;">
                  <i data-lucide="camera" style="width: 13px; height: 13px;"></i>
                  <?= $hasPhoto ? 'Changer photo' : 'Ajouter photo' ?>
                </button>
              <?php endif; ?>
            </div>

            <div style="flex: 1; min-width: 260px; display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; font-size: 13px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 9px; padding: 12px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Code &amp; Matricule</div>
              <div style="font-weight: 800; color: #1E3A5F; font-family: monospace; margin-top: 4px;"><?= htmlspecialchars($matricule !== '' ? $matricule : '-') ?></div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($codeEtudiant ?: '-') ?></code></div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 9px; padding: 12px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Naissance &amp; Genre</div>
              <div style="font-weight: 700; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($dobFormatted) ?></div>
              <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                <?= htmlspecialchars(!empty($item['lieu_naissance_etudiant']) ? $item['lieu_naissance_etudiant'] : 'Lieu non renseigné') ?> &bull; <?= htmlspecialchars($sexeLibelle) ?>
              </div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 9px; padding: 12px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Nationalité &amp; Pièce CNI</div>
              <div style="font-weight: 700; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(!empty($item['nationalite_etudiant']) ? $item['nationalite_etudiant'] : 'Non renseignée') ?></div>
              <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                CNI : <strong style="color: #1E3A5F;"><?= htmlspecialchars(!empty($item['numero_cni']) ? $item['numero_cni'] : '-') ?></strong>
              </div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 9px; padding: 12px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Contact Étudiant</div>
              <div style="font-weight: 700; color: #0F172A; margin-top: 4px;">
                <?php if (!empty($item['telephone_etudiant'])): ?>
                  <a href="tel:<?= htmlspecialchars($item['telephone_etudiant']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($item['telephone_etudiant']) ?></a>
                <?php else: ?>
                  <span style="color: #94A3B8;">-</span>
                <?php endif; ?>
              </div>
              <div style="font-size: 12px; color: #1E3A5F; margin-top: 2px; word-break: break-all;">
                <?php if (!empty($item['email_etudiant'])): ?>
                  <a href="mailto:<?= htmlspecialchars($item['email_etudiant']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($item['email_etudiant']) ?></a>
                <?php else: ?>
                  <span style="color: #94A3B8;">Aucun email</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 9px; padding: 12px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Résidence &amp; Provenance</div>
              <div style="font-weight: 700; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(!empty($item['lieu_residence_etudiant']) ? $item['lieu_residence_etudiant'] : 'Non renseignée') ?></div>
              <?php if (!empty($item['etablissement_origine'])): ?>
                <div style="font-size: 11.5px; color: #475569; margin-top: 2px;">Origine : <?= htmlspecialchars($item['etablissement_origine']) ?></div>
              <?php endif; ?>
            </div>

            <?php if (!empty($item['matricule_menet']) || !empty($item['matricule_mesrs'])): ?>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 9px; padding: 12px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Matricules Officiels</div>
              <?php if (!empty($item['matricule_menet'])): ?>
                <div style="font-size: 12px; color: #334155; margin-top: 4px;">MENET : <code style="font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($item['matricule_menet']) ?></code></div>
              <?php endif; ?>
              <?php if (!empty($item['matricule_mesrs'])): ?>
                <div style="font-size: 12px; color: #334155; margin-top: 2px;">MESRS : <code style="font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($item['matricule_mesrs']) ?></code></div>
              <?php endif; ?>
            </div>
            <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Responsables Légaux & Filiation -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Responsables Légaux &amp; Filiation
            </h3>
            <?php if (!empty($parent['parent_habitation'])): ?>
              <span style="font-size: 12px; color: #475569; font-weight: 600;">
                Domicile familial : <strong style="color: #0F172A;"><?= htmlspecialchars($parent['parent_habitation']) ?></strong>
              </span>
            <?php endif; ?>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Père</span>
              <div style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(!empty($parent['nom_pere']) ? $parent['nom_pere'] : 'Non renseigné') ?></div>
              <div style="font-size: 12.5px; color: #334155; margin-top: 4px;">
                Tél :
                <?php if (!empty($parent['telephone_pere'])): ?>
                  <a href="tel:<?= htmlspecialchars($parent['telephone_pere']) ?>" style="color: #1E3A5F; font-weight: 700; text-decoration: none;"><?= htmlspecialchars($parent['telephone_pere']) ?></a>
                <?php else: ?>
                  <span>-</span>
                <?php endif; ?>
              </div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Profession : <?= htmlspecialchars(!empty($parent['profession_pere']) ? $parent['profession_pere'] : '-') ?></div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Mère</span>
              <div style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(!empty($parent['nom_mere']) ? $parent['nom_mere'] : 'Non renseignée') ?></div>
              <div style="font-size: 12.5px; color: #334155; margin-top: 4px;">
                Tél :
                <?php if (!empty($parent['telephone_mere'])): ?>
                  <a href="tel:<?= htmlspecialchars($parent['telephone_mere']) ?>" style="color: #1E3A5F; font-weight: 700; text-decoration: none;"><?= htmlspecialchars($parent['telephone_mere']) ?></a>
                <?php else: ?>
                  <span>-</span>
                <?php endif; ?>
              </div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Profession : <?= htmlspecialchars(!empty($parent['profession_mere']) ? $parent['profession_mere'] : '-') ?></div>
            </div>

            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px 16px;">
              <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Tuteur Légal / Urgence</span>
              <div style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(!empty($parent['nom_tuteur']) ? $parent['nom_tuteur'] : 'Non renseigné') ?></div>
              <div style="font-size: 12.5px; color: #334155; margin-top: 4px;">
                Tél :
                <?php if (!empty($parent['telephone_tuteur'])): ?>
                  <a href="tel:<?= htmlspecialchars($parent['telephone_tuteur']) ?>" style="color: #1D4ED8; font-weight: 700; text-decoration: none;"><?= htmlspecialchars($parent['telephone_tuteur']) ?></a>
                <?php else: ?>
                  <span>-</span>
                <?php endif; ?>
              </div>
              <div style="font-size: 11.5px; color: #475569; margin-top: 2px;">Profession : <?= htmlspecialchars(!empty($parent['profession_tuteur']) ? $parent['profession_tuteur'] : '-') ?></div>
            </div>
          </div>
        </div>

        <!-- Historique des Inscriptions -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <i data-lucide="history" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Historique des Inscriptions (<?= count($inscriptionsHistory) ?>)
          </h3>

          <?php if (empty($inscriptionsHistory)): ?>
            <div style="color: #94A3B8; text-align: center; padding: 20px 0; font-style: italic; font-size: 13px;">
              Aucune inscription enregistrée pour cet étudiant.
            </div>
          <?php else: ?>
            <div style="width: 100%; overflow-x: auto;">
              <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                    <th style="padding: 10px 12px;">Session</th>
                    <th style="padding: 10px 12px;">Code</th>
                    <th style="padding: 10px 12px;">Classe &amp; Parcours</th>
                    <th style="padding: 10px 12px; text-align: center;">Régime</th>
                    <?php if ($canViewFinances): ?>
                      <th style="padding: 10px 12px; text-align: right;">Scolarité</th>
                    <?php endif; ?>
                    <th style="padding: 10px 12px; text-align: center;">Statut</th>
                    <th style="padding: 10px 12px; text-align: right; width: 85px;">Fiche</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($inscriptionsHistory as $ih):
                    $ihAff = in_array(strtolower((string)($ih['affectation_etat'] ?? '')), ['oui', 'affecte'], true);
                    $ihSt = strtolower((string)($ih['statut_inscription'] ?? ''));
                    $stBadge = [
                      'solde'      => ['bg' => '#DCFCE7', 'fg' => '#15803D', 'lbl' => 'Soldé'],
                      'valide'     => ['bg' => '#EFF6FF', 'fg' => '#1D4ED8', 'lbl' => 'Validé'],
                      'en_attente' => ['bg' => '#FEF3C7', 'fg' => '#B45309', 'lbl' => 'En attente'],
                      'annule'     => ['bg' => '#FEE2E2', 'fg' => '#B91C1C', 'lbl' => 'Annulé'],
                    ][$ihSt] ?? ['bg' => '#F1F5F9', 'fg' => '#475569', 'lbl' => ucfirst($ihSt ?: '-')];
                  ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 11px 12px; font-weight: 800; color: #1E3A5F; white-space: nowrap;">
                        <?php if ($canViewAnnees && !empty($ih['encrypted_annee_id'])): ?>
                          <a href="<?= url('annee/details/' . $ih['encrypted_annee_id']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($ih['libelle_annee'] ?? '-') ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($ih['libelle_annee'] ?? '-') ?>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 11px 12px; white-space: nowrap;">
                        <code style="font-weight: 700; color: #475569; background: #F1F5F9; padding: 2px 7px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($ih['code_inscription'] ?? '-') ?></code>
                      </td>
                      <td style="padding: 11px 12px;">
                        <div style="font-weight: 800; color: #0F172A;">
                          <?php if ($canViewClasses && !empty($ih['encrypted_classe_id'])): ?>
                            <a href="<?= url('classe/details/' . $ih['encrypted_classe_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($ih['libelle_classe'] ?? '-') ?></a>
                          <?php else: ?>
                            <?= htmlspecialchars($ih['libelle_classe'] ?? '-') ?>
                          <?php endif; ?>
                        </div>
                        <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
                          <?= htmlspecialchars(trim(($ih['libelle_filiere'] ?? '') . (!empty($ih['libelle_niveau']) ? ' · ' . $ih['libelle_niveau'] : ''))) ?>
                        </div>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                        <span style="background: <?= $ihAff ? '#DCFCE7' : '#E0F2FE' ?>; color: <?= $ihAff ? '#15803D' : '#0369A1' ?>; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">
                          <?= $ihAff ? 'Affecté(e)' : 'Privé(e)' ?>
                        </span>
                      </td>
                      <?php if ($canViewFinances): ?>
                        <td style="padding: 11px 12px; text-align: right; font-weight: 700; color: #0F172A; white-space: nowrap;">
                          <?= htmlspecialchars($fmtMoney($ih['montant_scolarite_inscription'] ?? 0)) ?>
                        </td>
                      <?php endif; ?>
                      <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                        <span style="background: <?= $stBadge['bg'] ?>; color: <?= $stBadge['fg'] ?>; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">
                          <?= htmlspecialchars($stBadge['lbl']) ?>
                        </span>
                      </td>
                      <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                        <?php if (!empty($ih['encrypted_inscription_id'])): ?>
                          <a href="<?= url('etudiant/imprimerFichePdf/' . $encryptedId) . '?inscription_id=' . urlencode($ih['encrypted_inscription_id']) ?>" target="_blank" title="Imprimer la fiche de cette inscription" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #334155; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- ONGLET 2 : SCOLARITÉ & PAIEMENTS (DISSOCIATION STRICTE RÈGLE 4) -->
      <!-- ========================================================================= -->
      <?php if ($canViewFinances): ?>
      <div id="tab-etu-finances" class="etu-detail-tab-pane" style="display: none;">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="credit-card" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
              Situation Financière &amp; Reçus de Scolarité (<span id="count-visible-etu-paiements"><?= count($paiementsScolarite) ?></span>)
            </h3>

            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <?php if (!empty($paiementsScolarite)): ?>
                <div style="position: relative; min-width: 200px;">
                  <input type="text" id="search-etu-paiements" placeholder="Rechercher reçu, mode..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
                  <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                </div>
              <?php endif; ?>
              <?php if ($encryptedInscriptionId !== ''): ?>
                <a href="<?= url('paiement/imprimerBilanFinancier/' . $encryptedInscriptionId) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="printer" style="width: 14px; height: 14px;"></i> Bilan Scolarité PDF
                </a>
              <?php endif; ?>
              <?php if ($canRecordPaiement): ?>
                <a href="<?= url('paiement/formulaire') ?>" style="background: #1E3A5F; color: #FFFFFF; border-radius: 8px; padding: 7px 14px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i> Nouveau règlement
                </a>
              <?php endif; ?>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px 16px;">
              <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Scolarité Totale (Session Active)</span>
              <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($fmtMoney($scolariteTotale)) ?></div>
            </div>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 14px 16px;">
              <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Total Versé Scolarité (<?= $tauxPaiement ?>%)</span>
              <div style="font-size: 20px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= htmlspecialchars($fmtMoney($totalPaye)) ?></div>
            </div>
            <div style="background: <?= $soldeRestant > 0 ? '#FEF2F2' : '#F8FAFC' ?>; border: 1px solid <?= $soldeRestant > 0 ? '#FECACA' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px 16px;">
              <span style="font-size: 11px; font-weight: 700; color: <?= $soldeRestant > 0 ? '#DC2626' : '#64748B' ?>; text-transform: uppercase;">Reste à Payer</span>
              <div style="font-size: 20px; font-weight: 800; color: <?= $soldeRestant > 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
                <?= $soldeRestant > 0 ? htmlspecialchars($fmtMoney($soldeRestant)) : 'Soldé (0 FCFA)' ?>
              </div>
            </div>
          </div>

          <?php if (empty($paiementsScolarite)): ?>
            <div style="color: #94A3B8; text-align: center; padding: 24px 0; font-style: italic; font-size: 13px;">
              Aucun règlement de scolarité enregistré pour cet étudiant.
            </div>
          <?php else: ?>
            <div style="width: 100%; overflow-x: auto;">
              <table id="table-etu-paiements" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                    <th style="padding: 10px 12px;">Reçu / Réf.</th>
                    <th style="padding: 10px 12px;">Date</th>
                    <th style="padding: 10px 12px;">Session</th>
                    <th style="padding: 10px 12px;">Libellé / Tranche</th>
                    <th style="padding: 10px 12px;">Mode</th>
                    <th style="padding: 10px 12px; text-align: right;">Montant</th>
                    <th style="padding: 10px 12px; text-align: center;">Statut</th>
                    <th style="padding: 10px 12px; text-align: right; width: 70px;">Détail</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($paiementsScolarite as $p):
                    $stP = strtolower((string)($p['statut_paiement'] ?? ''));
                    $pBadge = [
                      'confirme'   => ['bg' => '#DCFCE7', 'fg' => '#15803D', 'lbl' => 'Confirmé'],
                      'valide'     => ['bg' => '#DCFCE7', 'fg' => '#15803D', 'lbl' => 'Validé'],
                      'en_attente' => ['bg' => '#FEF3C7', 'fg' => '#B45309', 'lbl' => 'En attente'],
                      'annule'     => ['bg' => '#FEE2E2', 'fg' => '#B91C1C', 'lbl' => 'Annulé'],
                    ][$stP] ?? ['bg' => '#F1F5F9', 'fg' => '#475569', 'lbl' => ucfirst($stP ?: '-')];
                  ?>
                    <tr class="etu-paiement-row" style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 11px 12px; white-space: nowrap;">
                        <code style="font-weight: 800; color: #1E3A5F; background: #EFF6FF; padding: 2px 7px; border-radius: 5px; font-size: 12px;">
                          <?= htmlspecialchars(!empty($p['reference_paiement']) ? $p['reference_paiement'] : ($p['code_paiement'] ?? '-')) ?>
                        </code>
                      </td>
                      <td style="padding: 11px 12px; color: #334155; white-space: nowrap;">
                        <?= !empty($p['date_paiement']) ? date('d/m/Y H:i', strtotime($p['date_paiement'])) : '-' ?>
                      </td>
                      <td style="padding: 11px 12px; color: #475569; font-weight: 600; white-space: nowrap;">
                        <?= htmlspecialchars($p['libelle_annee'] ?? '-') ?>
                      </td>
                      <td style="padding: 11px 12px; color: #0F172A; font-weight: 600;">
                        <?= htmlspecialchars(!empty($p['type_paiement']) ? $p['type_paiement'] : (!empty($p['observations']) ? $p['observations'] : '-')) ?>
                      </td>
                      <td style="padding: 11px 12px; color: #475569; font-weight: 600; white-space: nowrap;">
                        <?= htmlspecialchars(!empty($p['mode_paiement']) ? $p['mode_paiement'] : '-') ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: right; font-weight: 800; color: <?= $stP === 'annule' ? '#94A3B8' : '#15803D' ?>; white-space: nowrap;">
                        <?= htmlspecialchars($fmtMoney($p['montant_paiement'] ?? ($p['montant_paye'] ?? 0))) ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                        <span style="background: <?= $pBadge['bg'] ?>; color: <?= $pBadge['fg'] ?>; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11px;">
                          <?= htmlspecialchars($pBadge['lbl']) ?>
                        </span>
                      </td>
                      <td style="padding: 11px 12px; text-align: right;">
                        <?php if (!empty($p['encrypted_paiement_id'])): ?>
                          <a href="<?= url('paiement/details/' . $p['encrypted_paiement_id']) ?>" title="Voir le reçu" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <?php if (!empty($paiementsAnnexes)): ?>
        <!-- Caisse Frais Annexes : strictement indépendante et jamais additionnée à la scolarité -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #F1F5F9;">
            <div>
              <h3 style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="package" style="width: 17px; height: 17px; color: #B45309;"></i>
                Frais Annexes (<?= count($paiementsAnnexes) ?>)
              </h3>
              <p style="font-size: 11.5px; color: #64748B; margin: 2px 0 0 0;">Caisse annexe indépendante — non comptabilisée dans le solde de scolarité</p>
            </div>
            <span style="background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; padding: 4px 12px; border-radius: 10px; font-weight: 800; font-size: 12px;">
              Total Annexes (Session Active) : <?= htmlspecialchars($fmtMoney($totalFraisAnnexesPaye)) ?>
            </span>
          </div>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 9px 12px;">Code</th>
                  <th style="padding: 9px 12px;">Date</th>
                  <th style="padding: 9px 12px;">Objet</th>
                  <th style="padding: 9px 12px;">Mode</th>
                  <th style="padding: 9px 12px; text-align: right;">Montant</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($paiementsAnnexes as $pa): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 9px 12px;"><code><?= htmlspecialchars($pa['code_paiement'] ?? '-') ?></code></td>
                    <td style="padding: 9px 12px; color: #334155;"><?= !empty($pa['date_paiement']) ? date('d/m/Y', strtotime($pa['date_paiement'])) : '-' ?></td>
                    <td style="padding: 9px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($pa['type_paiement'] ?? ($pa['observations'] ?? '-')) ?></td>
                    <td style="padding: 9px 12px; color: #475569;"><?= htmlspecialchars($pa['mode_paiement'] ?? '-') ?></td>
                    <td style="padding: 9px 12px; text-align: right; font-weight: 800; color: #B45309;"><?= htmlspecialchars($fmtMoney($pa['montant_paiement'] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- ONGLET 3 : PIÈCES PHYSIQUES DU DOSSIER -->
      <!-- ========================================================================= -->
      <div id="tab-etu-dossier" class="etu-detail-tab-pane" style="display: none;">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <div>
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="folder-check" style="width: 18px; height: 18px; color: #15803D;"></i>
                Pièces Administratives Fournies (<?= $deposedCount ?> / <?= $totalPieces ?>)
              </h3>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Contrôle des pièces physiques déposées à la scolarité</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <?php if ($isDossierComplet): ?>
                <span style="background: #DCFCE7; color: #15803D; padding: 5px 12px; border-radius: 10px; font-weight: 800; font-size: 12px; border: 1px solid #86EFAC;">Dossier Complet</span>
              <?php else: ?>
                <span style="background: #FEF3C7; color: #B45309; padding: 5px 12px; border-radius: 10px; font-weight: 800; font-size: 12px; border: 1px solid #FCD34D;">Dossier Incomplet</span>
              <?php endif; ?>
              <?php if ($canViewDossiers): ?>
                <a href="<?= url('dossier/list') ?>" style="background: #FFFFFF; color: #1E3A5F; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 700; text-decoration: none;">Gérer les dépôts</a>
              <?php endif; ?>
            </div>
          </div>

          <?php if (empty($dossierPieces)): ?>
            <div style="color: #94A3B8; text-align: center; padding: 24px 0; font-style: italic; font-size: 13px;">
              Aucune pièce administrative configurée.
            </div>
          <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
              <?php foreach ($dossierPieces as $dp):
                $isDep = (($dp['statut_depot'] ?? '') === 'depose');
              ?>
                <div style="background: <?= $isDep ? '#F0FDF4' : '#F8FAFC' ?>; border: 1px solid <?= $isDep ? '#BBF7D0' : '#E2E8F0' ?>; border-radius: 10px; padding: 13px 15px; display: flex; align-items: flex-start; gap: 11px;">
                  <div style="width: 28px; height: 28px; border-radius: 8px; background: <?= $isDep ? '#DCFCE7' : '#E2E8F0' ?>; color: <?= $isDep ? '#15803D' : '#64748B' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="<?= $isDep ? 'check' : 'clock' ?>" style="width: 15px; height: 15px;"></i>
                  </div>
                  <div style="flex: 1;">
                    <div style="font-weight: 700; color: #0F172A; font-size: 13px; line-height: 1.35;">
                      <?= htmlspecialchars($dp['libelle_piece'] ?? '-') ?>
                    </div>
                    <?php if (!empty($dp['description_piece'])): ?>
                      <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($dp['description_piece']) ?></div>
                    <?php endif; ?>
                    <div style="margin-top: 6px;">
                      <?php if ($isDep): ?>
                        <span style="background: #DCFCE7; color: #15803D; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                          Déposée<?= !empty($dp['date_depot']) ? ' le ' . date('d/m/Y', strtotime($dp['date_depot'])) : '' ?>
                        </span>
                      <?php else: ?>
                        <span style="background: #FEF2F2; color: #B91C1C; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                          En attente
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- ONGLET 3B : KITS & ACCESSOIRES (TENUES, POLOS, BADGES, MACARONS) -->
      <!-- ========================================================================= -->
      <?php if ($canViewKits): ?>
      <div id="tab-etu-kits" class="etu-detail-tab-pane" style="display: none;">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <div>
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="package" style="width: 18px; height: 18px; color: #B45309;"></i>
                Kits &amp; Accessoires (<span id="header-count-kits-retires"><?= $kitsRetiresCount ?></span> / <?= $totalKits ?> retirés)
              </h3>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Suivi de la remise des tenues, polos, badges et accessoires scolaires</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <?php if ($totalKits > 0): ?>
                <span id="badge-kits-summary" style="background: <?= $kitsEnAttenteCount === 0 ? '#DCFCE7' : '#FEF3C7' ?>; color: <?= $kitsEnAttenteCount === 0 ? '#15803D' : '#B45309' ?>; border: 1px solid <?= $kitsEnAttenteCount === 0 ? '#86EFAC' : '#FCD34D' ?>; padding: 5px 12px; border-radius: 10px; font-weight: 800; font-size: 12px;">
                  <?= $kitsEnAttenteCount === 0 ? 'Tous les kits retirés' : ($kitsEnAttenteCount . ' en attente') ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($inscription['code_inscription'])): ?>
                <a href="<?= url('accessoire/imprimerBonPdf') . '?inscription_code=' . urlencode($inscription['code_inscription']) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="printer" style="width: 14px; height: 14px;"></i> Bon de Remise PDF
                </a>
              <?php endif; ?>
              <a href="<?= url('accessoire/registre') ?>" style="background: #F8FAFC; color: #1E3A5F; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="clipboard-list" style="width: 14px; height: 14px;"></i> Registre Kits
              </a>
            </div>
          </div>

          <?php if (empty($kits)): ?>
            <div style="color: #94A3B8; text-align: center; padding: 26px 0; font-style: italic; font-size: 13px;">
              Aucun kit ou accessoire n'est encore attribué à cet étudiant.
            </div>
          <?php else: ?>
            <div style="width: 100%; overflow-x: auto;">
              <table id="table-etu-kits" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                    <th style="padding: 10px 12px; width: 40px;">#</th>
                    <th style="padding: 10px 12px;">Code</th>
                    <th style="padding: 10px 12px;">Accessoire / Kit</th>
                    <th style="padding: 10px 12px;">Session</th>
                    <th style="padding: 10px 12px; text-align: center;">Date de Retrait</th>
                    <th style="padding: 10px 12px; text-align: center;">État</th>
                    <?php if ($canManageKits): ?>
                      <th style="padding: 10px 12px; text-align: right; width: 140px;">Action</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($kits as $idx => $kt):
                    $isRetire = (strtolower((string)($kt['etat_retrait'] ?? '')) === 'retire');
                    $dateRet = !empty($kt['date_retrait']) ? date('d/m/Y H:i', strtotime($kt['date_retrait'])) : '-';
                  ?>
                    <tr class="etu-kit-row" data-state="<?= $isRetire ? 'retire' : 'en_attente' ?>" style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                      <td style="padding: 11px 12px; white-space: nowrap;">
                        <code style="font-weight: 700; color: #475569; background: #F1F5F9; padding: 2px 7px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($kt['code_accessoire_inscription'] ?? '-') ?></code>
                      </td>
                      <td style="padding: 11px 12px; font-weight: 700; color: #0F172A;">
                        <?= htmlspecialchars($kt['libelle_accessoire'] ?? ($kt['accessoire_code'] ?? '-')) ?>
                      </td>
                      <td style="padding: 11px 12px; color: #475569; font-weight: 600; white-space: nowrap;">
                        <?= htmlspecialchars($kt['libelle_annee'] ?? '-') ?>
                      </td>
                      <td class="kit-date-cell" style="padding: 11px 12px; text-align: center; color: #475569; font-weight: 600; white-space: nowrap;">
                        <?= htmlspecialchars($dateRet) ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                        <span class="kit-state-badge" style="background: <?= $isRetire ? '#DCFCE7' : '#FEF3C7' ?>; color: <?= $isRetire ? '#15803D' : '#B45309' ?>; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">
                          <?= $isRetire ? 'Retiré' : 'En attente' ?>
                        </span>
                      </td>
                      <?php if ($canManageKits): ?>
                        <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                          <button type="button"
                                  class="btn-toggle-kit-retrait"
                                  data-id="<?= (int)($kt['id_accessoire_inscription'] ?? 0) ?>"
                                  data-code="<?= htmlspecialchars($kt['code_accessoire_inscription'] ?? '') ?>"
                                  style="background: <?= $isRetire ? '#F8FAFC' : '#16A34A' ?>; color: <?= $isRetire ? '#475569' : '#FFFFFF' ?>; border: 1px solid <?= $isRetire ? '#CBD5E1' : '#16A34A' ?>; border-radius: 7px; padding: 5px 10px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                            <i data-lucide="<?= $isRetire ? 'rotate-ccw' : 'check' ?>" style="width: 13px; height: 13px;"></i>
                            <span><?= $isRetire ? 'Annuler retrait' : 'Marquer retiré' ?></span>
                          </button>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- ONGLET 3C : BULLETINS DE NOTES & RÉSULTATS ACADÉMIQUES -->
      <!-- ========================================================================= -->
      <?php if ($canViewBulletins): ?>
      <div id="tab-etu-bulletins" class="etu-detail-tab-pane" style="display: none;">

        <?php if (!empty($deliberations)): ?>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #BFDBFE; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px; width: 100%; box-sizing: border-box;">
          <h3 style="font-size: 14.5px; font-weight: 800; color: #1E3A5F; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="gavel" style="width: 17px; height: 17px;"></i> Décisions Officielles de Délibération (<?= count($deliberations) ?>)
          </h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
            <?php foreach ($deliberations as $del):
              $dec = strtolower((string)($del['decision_finale'] ?? 'en_attente'));
              $decStyle = [
                'admis'      => ['bg' => '#DCFCE7', 'fg' => '#15803D', 'bd' => '#86EFAC'],
                'ajourne'    => ['bg' => '#FEF3C7', 'fg' => '#B45309', 'bd' => '#FCD34D'],
                'redouble'   => ['bg' => '#FEE2E2', 'fg' => '#B91C1C', 'bd' => '#FCA5A5'],
                'exclu'      => ['bg' => '#FEE2E2', 'fg' => '#991B1B', 'bd' => '#FCA5A5'],
                'en_attente' => ['bg' => '#F1F5F9', 'fg' => '#475569', 'bd' => '#CBD5E1'],
              ][$dec] ?? ['bg' => '#F1F5F9', 'fg' => '#475569', 'bd' => '#CBD5E1'];
            ?>
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px;">
                  <strong style="color: #0F172A; font-size: 13.5px;"><?= htmlspecialchars(($del['libelle_annee'] ?? '-') . (!empty($del['libelle_classe']) ? ' · ' . $del['libelle_classe'] : '')) ?></strong>
                  <span style="background: <?= $decStyle['bg'] ?>; color: <?= $decStyle['fg'] ?>; border: 1px solid <?= $decStyle['bd'] ?>; padding: 2px 9px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                    <?= htmlspecialchars(!empty($del['libelle_decision']) ? $del['libelle_decision'] : $dec) ?>
                  </span>
                </div>
                <div style="font-size: 12.5px; color: #334155; display: flex; flex-wrap: wrap; gap: 12px;">
                  <?php if ($del['moyenne_annuelle'] !== null): ?>
                    <span>Moy. annuelle : <strong style="color: #1E3A5F;"><?= number_format((float)$del['moyenne_annuelle'], 2, ',', ' ') ?>/20</strong></span>
                  <?php endif; ?>
                  <?php if (!empty($del['rang_annuel'])): ?>
                    <span>Rang : <strong><?= (int)$del['rang_annuel'] ?><?= !empty($del['effectif_classe']) ? (' / ' . (int)$del['effectif_classe']) : '' ?></strong></span>
                  <?php endif; ?>
                  <?php if (!empty($del['mention_finale'])): ?>
                    <span>Mention : <strong><?= htmlspecialchars($del['mention_finale']) ?></strong></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <div>
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="award" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
                Bulletins Semestriels de Notes (<?= count($bulletins) ?>)
              </h3>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Consultation des moyennes pondérées par semestre et impression officielle PDF</p>
            </div>
            <a href="<?= url('bulletin/list') ?>" style="background: #F8FAFC; color: #1E3A5F; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="file-spreadsheet" style="width: 14px; height: 14px;"></i> Module Bulletins
            </a>
          </div>

          <?php if (empty($bulletins)): ?>
            <div style="color: #94A3B8; text-align: center; padding: 26px 0; font-style: italic; font-size: 13px;">
              Aucun bulletin semestriel disponible pour cet étudiant.
            </div>
          <?php else: ?>
            <div style="width: 100%; overflow-x: auto;">
              <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                    <th style="padding: 10px 12px;">Session</th>
                    <th style="padding: 10px 12px;">Semestre</th>
                    <th style="padding: 10px 12px;">Classe</th>
                    <th style="padding: 10px 12px; text-align: center;">Matières / Notes</th>
                    <th style="padding: 10px 12px; text-align: center;">Moy. Générales</th>
                    <th style="padding: 10px 12px; text-align: center;">Moy. Pro.</th>
                    <th style="padding: 10px 12px; text-align: center;">Moyenne Générale</th>
                    <th style="padding: 10px 12px; text-align: right; width: 100px;">Bulletin</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($bulletins as $bul):
                    $nbNotesB = (int)($bul['nb_notes'] ?? 0);
                    $nbMatB = (int)($bul['nb_matieres'] ?? 0);
                    $moyG = $bul['moyenne_generale'] !== null ? (float)$bul['moyenne_generale'] : null;
                    $moyGen = $bul['moyenne_generales'] !== null ? (float)$bul['moyenne_generales'] : null;
                    $moyPro = $bul['moyenne_pro'] !== null ? (float)$bul['moyenne_pro'] : null;
                    $semParam = urlencode((string)($bul['code_semestre'] ?? ''));
                    $encInsB = (string)($bul['encrypted_inscription_id'] ?? '');
                  ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 11px 12px; font-weight: 800; color: #1E3A5F; white-space: nowrap;">
                        <?= htmlspecialchars($bul['libelle_annee'] ?? '-') ?>
                      </td>
                      <td style="padding: 11px 12px; white-space: nowrap;">
                        <span style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px; font-weight: 800; font-size: 12px;">
                          <?= htmlspecialchars($bul['libelle_semestre'] ?? '-') ?>
                        </span>
                      </td>
                      <td style="padding: 11px 12px; font-weight: 700; color: #0F172A; white-space: nowrap;">
                        <?= htmlspecialchars($bul['libelle_classe'] ?? '-') ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; color: #475569; font-weight: 600; white-space: nowrap;">
                        <?php if ($nbNotesB > 0): ?>
                          <strong><?= $nbMatB ?></strong> mat. &bull; <strong><?= $nbNotesB ?></strong> note<?= $nbNotesB > 1 ? 's' : '' ?>
                        <?php else: ?>
                          <span style="color: #94A3B8; font-style: italic;">Aucune note</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; font-weight: 700; color: #334155; white-space: nowrap;">
                        <?= $moyGen !== null ? (number_format($moyGen, 2, ',', ' ') . '/20') : '-' ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; font-weight: 700; color: #334155; white-space: nowrap;">
                        <?= $moyPro !== null ? (number_format($moyPro, 2, ',', ' ') . '/20') : '-' ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                        <?php if ($moyG !== null): ?>
                          <span style="background: <?= $moyG >= 10 ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $moyG >= 10 ? '#15803D' : '#B91C1C' ?>; border: 1px solid <?= $moyG >= 10 ? '#86EFAC' : '#FCA5A5' ?>; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 12.5px;">
                            <?= number_format($moyG, 2, ',', ' ') ?> / 20
                          </span>
                        <?php else: ?>
                          <span style="color: #94A3B8;">-</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                        <?php if ($encInsB !== '' && $nbNotesB > 0): ?>
                          <div style="display: inline-flex; align-items: center; gap: 6px;">
                            <a href="<?= url('bulletin/details/' . $encInsB) . '?semestre=' . $semParam ?>" title="Consulter le bulletin détaillé" aria-label="Consulter le bulletin détaillé" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                              <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                            </a>
                            <a href="<?= url('bulletin/imprimerBulletinPdf/' . $encInsB) . '?semestre=' . $semParam ?>" target="_blank" title="Imprimer le bulletin en PDF" aria-label="Imprimer le bulletin en PDF" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                              <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                            </a>
                          </div>
                        <?php else: ?>
                          <span style="color: #94A3B8;">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- ONGLET 4 : ABSENCES & ASSIDUITÉ -->
      <!-- ========================================================================= -->
      <?php if ($canViewAbsences): ?>
      <div id="tab-etu-absences" class="etu-detail-tab-pane" style="display: none;">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="calendar-x" style="width: 18px; height: 18px; color: #B91C1C;"></i>
              Relevé d'Absences &amp; Assiduité (<?= $totalAbsCount ?>)
            </h3>
            <a href="<?= url('absence/imprimerHistoriqueEtudiant/' . $encryptedId) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 7px 13px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="printer" style="width: 14px; height: 14px;"></i> Relevé d'Absences PDF
            </a>
          </div>

          <?php if (empty($absences)): ?>
            <div style="color: #15803D; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; text-align: center; padding: 24px; font-weight: 700; font-size: 13px;">
              Aucune absence enregistrée pour cet étudiant.
            </div>
          <?php else: ?>
            <div style="width: 100%; overflow-x: auto;">
              <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                    <th style="padding: 10px 12px;">Date</th>
                    <th style="padding: 10px 12px;">Session / Semestre</th>
                    <th style="padding: 10px 12px;">Matière</th>
                    <th style="padding: 10px 12px; text-align: center;">Durée</th>
                    <th style="padding: 10px 12px; text-align: center;">Statut</th>
                    <th style="padding: 10px 12px;">Motif</th>
                    <th style="padding: 10px 12px; text-align: right; width: 70px;">Détail</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($absences as $ab):
                    $isJust = (strtolower((string)($ab['justifiee'] ?? 'non')) === 'oui');
                  ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 11px 12px; font-weight: 700; color: #0F172A; white-space: nowrap;">
                        <?= !empty($ab['date_absence']) ? date('d/m/Y', strtotime($ab['date_absence'])) : '-' ?>
                      </td>
                      <td style="padding: 11px 12px; white-space: nowrap;">
                        <div style="font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($ab['libelle_semestre'] ?? '-') ?></div>
                        <div style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($ab['libelle_annee'] ?? '') ?></div>
                      </td>
                      <td style="padding: 11px 12px; font-weight: 600; color: #0F172A;">
                        <?= htmlspecialchars($ab['libelle_matiere'] ?? '-') ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; font-weight: 800; color: #1E3A5F; white-space: nowrap;">
                        <?= htmlspecialchars($fmtHeures($ab['duree_heures'] ?? 0)) ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                        <?php if ($isJust): ?>
                          <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Justifiée</span>
                        <?php else: ?>
                          <span style="background: #FEE2E2; color: #B91C1C; padding: 3px 9px; border-radius: 12px; font-size: 11.5px; font-weight: 700;">Non justifiée</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 11px 12px; color: #475569;">
                        <?= htmlspecialchars(!empty($ab['motif']) ? $ab['motif'] : '-') ?>
                      </td>
                      <td style="padding: 11px 12px; text-align: right;">
                        <?php if (!empty($ab['encrypted_absence_id'])): ?>
                          <a href="<?= url('absence/details/' . $ab['encrypted_absence_id']) ?>" title="Ouvrir la fiche absence" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- ========================================================================= -->
      <!-- ONGLET 5 : CARTE D'IDENTITÉ SCOLAIRE (RECTO / VERSO — 100% CONFIGURATIONS) -->
      <!-- ========================================================================= -->
      <div id="tab-etu-carte" class="etu-detail-tab-pane" style="display: none;">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <div>
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="id-card" style="width: 18px; height: 18px; color: #D97706;"></i> Carte d'Identité Scolaire &amp; Badge Étudiant
              </h3>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Format PVC Standard (CR-80) — Données institutionnelles dynamiques</p>
            </div>
            <button type="button" id="btn-print-carte-scolaire-tab" style="background: #1E3A5F; color: #FFFFFF; border: none; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer;">
              <i data-lucide="printer" style="width: 15px; height: 15px;"></i> Imprimer la Carte Scolaire
            </button>
          </div>

          <div id="section-carte-scolaire" style="display: flex; justify-content: center; align-items: center; gap: 28px; flex-wrap: wrap; padding: 10px 0;">
            <!-- RECTO -->
            <div class="badge-card-container badge-recto" style="width: 390px; height: 245px; border-radius: 14px; background: #1E3A5F; color: #FFFFFF; position: relative; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.35); overflow: hidden; padding: 14px 18px; box-sizing: border-box; border: 1px solid #334155; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
              <div style="position: absolute; left: 0; top: 0; right: 0; height: 5px; background: #D97706;"></div>

              <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 8px; margin-bottom: 10px; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                  <?php if ($etabLogoUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($etabLogoUrl) ?>" alt="Logo" style="width: 28px; height: 28px; border-radius: 6px; object-fit: cover; background: #FFFFFF; flex-shrink: 0;">
                  <?php else: ?>
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: #D97706; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 11px; color: #FFF; flex-shrink: 0;">
                      <?= htmlspecialchars(mb_substr($etabSigle !== '' ? $etabSigle : $etabNom, 0, 2, 'UTF-8')) ?>
                    </div>
                  <?php endif; ?>
                  <div style="min-width: 0;">
                    <div style="font-size: 9.5px; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase; color: #FDE68A; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <?= htmlspecialchars($etabSigle !== '' ? $etabSigle : $etabNom) ?>
                    </div>
                    <?php if ($etabPays !== ''): ?>
                      <div style="font-size: 7.5px; color: #CBD5E1; letter-spacing: 0.4px; text-transform: uppercase;"><?= htmlspecialchars($etabPays) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php if ($anneeLibelle !== ''): ?>
                  <span style="background: #D97706; border: 1px solid #F59E0B; color: #FFFFFF; font-size: 8px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; white-space: nowrap; flex-shrink: 0;">
                    <?= htmlspecialchars($anneeLibelle) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div style="display: flex; gap: 14px; align-items: center; margin-top: 6px;">
                <div style="width: 78px; height: 96px; border-radius: 8px; border: 2px solid #F59E0B; background: #0F172A; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; flex-shrink: 0;">
                  <img class="etu-photo-live-img" src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo Étudiant" onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholderPhotoUrl) ?>';" style="width: 100%; height: 100%; object-fit: cover;">
                  <span style="position: absolute; bottom: 0; left: 0; right: 0; background: #D97706; font-size: 7px; font-weight: 800; text-align: center; color: #FFF; padding: 1px 0; text-transform: uppercase;">ÉTUDIANT</span>
                </div>

                <div style="flex: 1; min-width: 0;">
                  <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">NOM &amp; PRÉNOMS</div>
                  <div style="font-size: 13px; font-weight: 900; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                    <?= htmlspecialchars($nomComplet ?: '-') ?>
                  </div>

                  <div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 4px; margin-top: 6px;">
                    <div>
                      <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">MATRICULE</div>
                      <div style="font-size: 10px; font-weight: 800; color: #FDE68A; font-family: monospace;">
                        <?= htmlspecialchars($matricule !== '' ? $matricule : $codeEtudiant) ?>
                      </div>
                    </div>
                    <div>
                      <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">SEXE</div>
                      <div style="font-size: 10px; font-weight: 700; color: #FFF;">
                        <?= htmlspecialchars($sexeRaw !== '' ? $sexeRaw : '-') ?>
                      </div>
                    </div>
                  </div>

                  <div style="margin-top: 6px;">
                    <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">CLASSE &amp; FILIÈRE</div>
                    <div style="font-size: 10px; font-weight: 800; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <?= htmlspecialchars($classeLibelle !== '' ? $classeLibelle : '-') ?>
                    </div>
                  </div>
                </div>

                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; background: #FFFFFF; padding: 4px; border-radius: 6px; border: 1px solid #CBD5E1;">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= urlencode('MAT:' . ($matricule ?: $codeEtudiant) . '|NOM:' . $nomComplet . '|CLASSE:' . $classeLibelle . '|ANNEE:' . $anneeLibelle) ?>" alt="QR Code" style="width: 54px; height: 54px; display: block;">
                  <span style="font-size: 6px; font-weight: 800; color: #0F172A; margin-top: 2px;">SCAN VALIDE</span>
                </div>
              </div>

              <div style="position: absolute; bottom: 8px; left: 18px; right: 18px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 4px; font-size: 7px; color: #94A3B8;">
                <span>CARTE D'IDENTITÉ SCOLAIRE</span>
                <?php if ($dateFinAnnee !== ''): ?>
                  <span style="color: #FDE68A; font-weight: 700;">VALABLE JUSQU'AU <?= htmlspecialchars($dateFinAnnee) ?></span>
                <?php elseif ($anneeLibelle !== ''): ?>
                  <span style="color: #FDE68A; font-weight: 700;">SESSION <?= htmlspecialchars($anneeLibelle) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- VERSO -->
            <div class="badge-card-container badge-verso" style="width: 390px; height: 245px; border-radius: 14px; background: #FFFFFF; color: #1E293B; position: relative; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25); overflow: hidden; padding: 14px 18px; box-sizing: border-box; border: 1px solid #CBD5E1; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
              <div style="position: absolute; left: 0; top: 0; right: 0; height: 5px; background: #1E3A5F;"></div>

              <div style="font-size: 8px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;">CONDITIONS D'UTILISATION</div>
              <p style="font-size: 7.5px; line-height: 1.35; color: #475569; margin: 0 0 8px 0;">
                1. Cette carte est strictement personnelle et engage la responsabilité de son titulaire.<br>
                2. Elle doit être présentée à toute réquisition et lors des contrôles ou examens.<br>
                3. En cas de perte ou de vol, aviser immédiatement le secrétariat de l'établissement.
              </p>

              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 10px; margin-bottom: 8px;">
                <div style="font-size: 7.5px; font-weight: 800; color: #0F172A;">CONTACT EN CAS D'URGENCE :</div>
                <div style="font-size: 7.5px; color: #334155;">
                  Tuteur : <strong><?= htmlspecialchars(!empty($parent['nom_tuteur']) ? $parent['nom_tuteur'] : (!empty($parent['nom_pere']) ? $parent['nom_pere'] : '-')) ?></strong>
                  &bull; Tél : <strong><?= htmlspecialchars(!empty($parent['telephone_tuteur']) ? $parent['telephone_tuteur'] : (!empty($parent['telephone_pere']) ? $parent['telephone_pere'] : (!empty($item['telephone_etudiant']) ? $item['telephone_etudiant'] : '-'))) ?></strong>
                </div>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 6px;">
                <div style="text-align: center; width: 112px;">
                  <div style="font-size: 7px; color: #64748B; text-transform: uppercase; margin-bottom: 18px;">Signature du Titulaire</div>
                  <div style="border-bottom: 1px dashed #94A3B8; width: 100%;"></div>
                </div>

                <div style="text-align: center;">
                  <div style="font-family: monospace; font-size: 14px; letter-spacing: 2px; color: #0F172A; font-weight: 900;">|||| | ||||| || ||||</div>
                  <div style="font-size: 7px; font-family: monospace; color: #64748B;"><?= htmlspecialchars($matricule !== '' ? $matricule : $codeEtudiant) ?></div>
                </div>

                <div style="text-align: center; width: 120px;">
                  <div style="font-size: 7px; color: #1E3A5F; font-weight: 700; text-transform: uppercase; margin-bottom: 14px;">
                    <?= htmlspecialchars($etabTitreDG !== '' ? $etabTitreDG : '') ?>
                  </div>
                  <div style="border-bottom: 1px dashed #94A3B8; width: 100%;"></div>
                  <?php if ($etabNomDG !== ''): ?>
                    <div style="font-size: 6.5px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($etabNomDG) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div style="position: absolute; bottom: 6px; left: 18px; right: 18px; border-top: 1px solid #E2E8F0; padding-top: 3px; font-size: 6.5px; color: #64748B; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?= htmlspecialchars(implode(' · ', array_filter([$etabNom, $etabAdresse, $etabContacts !== '' ? 'Tél : ' . $etabContacts : '']))) ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script type="application/json" id="etudiants-details-config">
<?= json_encode([
    'racine'          => RACINE,
    'nomComplet'      => $nomComplet,
    'matricule'       => $matricule !== '' ? $matricule : $codeEtudiant,
    'inscriptionId'   => (int)($inscription['id_inscription'] ?? 0),
    'codeInscription' => (string)($inscription['code_inscription'] ?? ''),
    'csrfToken'       => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/etudiants.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
