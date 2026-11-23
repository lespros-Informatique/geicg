<?php
require_once __DIR__ . '/../../public/inc/header.php';

$cours = $cours ?? [];
$planning = $planning ?? [];
$stats = $stats ?? [];
$anneeCode = (string)($anneeCode ?? '');
$anneeLibelle = (string)($anneeLibelle ?? '');
$encryptedId = (string)($encryptedId ?? '');
$encryptedUserId = (string)($encryptedUserId ?? '');

$idEnseignant = (int)($item['id_enseignant'] ?? 0);
$codeEnseignant = (string)($item['code_enseignant'] ?? '');
$matricule = (string)($item['matricule_user'] ?? '');
$nomComplet = trim(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? ''));
$grade = (string)($item['grade_enseignant'] ?? '');
$typeContrat = strtolower(trim((string)($item['type_contrat'] ?? '')));
$numAutorisation = (string)($item['numero_autorisation'] ?? '');
$isActif = (strtolower(trim((string)($item['statut_enseignant'] ?? ''))) === STATUTS::ACTIF);
$isCompteActif = (strtolower(trim((string)($item['statut_user'] ?? ''))) === STATUTS::ACTIF);

// Initiales et photo
$parts = preg_split('/\s+/', $nomComplet) ?: [];
$initials = '';
foreach (array_slice($parts, 0, 2) as $pt) {
    if ($pt !== '') $initials .= mb_strtoupper(mb_substr($pt, 0, 1, 'UTF-8'), 'UTF-8');
}
if ($initials === '') $initials = 'EN';

$photoRaw = !empty($item['photo_user']) ? (string)$item['photo_user'] : (!empty($item['photo_enseignant']) ? (string)$item['photo_enseignant'] : '');
$photoUrl = '';
if ($photoRaw !== '') {
    $cleanRel = ltrim($photoRaw, '/');
    if (file_exists(__DIR__ . '/../../' . $cleanRel)) {
        $photoUrl = url($cleanRel);
    } elseif (file_exists(__DIR__ . '/../../public/' . $cleanRel)) {
        $photoUrl = url('public/' . $cleanRel);
    } elseif (strpos($photoRaw, 'http') === 0) {
        $photoUrl = $photoRaw;
    }
}
$hasPhoto = ($photoUrl !== '');
$placeholderPhotoUrl = placeholderUrl('user');
$finalPhotoUrl = $hasPhoto ? $photoUrl : $placeholderPhotoUrl;

$sexeLibelle = 'Non précisé';
if (!empty($item['sexe_enseignant'])) {
    $sx = strtoupper(trim((string)$item['sexe_enseignant']));
    $sexeLibelle = ($sx === 'M' || $sx === 'MASCULIN' || $sx === 'HOMME') ? 'Masculin' : (($sx === 'F' || $sx === 'FEMININ' || $sx === 'FEMME') ? 'Féminin' : $sx);
}

$contratLabel = $typeContrat !== '' ? ucfirst($typeContrat) : 'Non précisé';
$contratColors = [
    'permanent'   => ['bg' => '#EFF6FF', 'fg' => '#1D4ED8', 'bd' => '#BFDBFE'],
    'vacataire'   => ['bg' => '#FEF3C7', 'fg' => '#B45309', 'bd' => '#FDE68A'],
    'prestataire' => ['bg' => '#F3E8FF', 'fg' => '#7E22CE', 'bd' => '#E9D5FF'],
];
$cStyle = $contratColors[$typeContrat] ?? ['bg' => '#F1F5F9', 'fg' => '#475569', 'bd' => '#CBD5E1'];

$totalCreneaux = (int)($stats['total_creneaux'] ?? count($planning));
$totalHeuresHebdo = (float)($stats['total_heures_hebdo'] ?? 0);
$totalJoursActifs = (int)($stats['total_jours_actifs'] ?? 0);
$totalClasses = (int)($stats['total_classes'] ?? 0);
$totalMatieres = (int)($stats['total_matieres'] ?? 0);
$totalSalles = (int)($stats['total_salles'] ?? 0);
$totalEtudiants = (int)($stats['total_etudiants'] ?? 0);

$createdAt = !empty($item['created_at_enseignant']) ? date('d/m/Y', strtotime($item['created_at_enseignant'])) : '';
$updatedAt = !empty($item['updated_at_enseignant']) ? date('d/m/Y', strtotime($item['updated_at_enseignant'])) : '';
$lastLogin = !empty($item['last_connexion']) ? date('d/m/Y H:i', strtotime($item['last_connexion'])) : 'Jamais connecté';

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};
$fmtHeureShort = static function ($t): string {
    $str = (string)$t;
    return strlen($str) >= 5 ? substr($str, 0, 5) : $str;
};

// Répartition dynamique par jour de la semaine
$joursSemaine = [
    'lundi'    => 'Lundi',
    'mardi'    => 'Mardi',
    'mercredi' => 'Mercredi',
    'jeudi'    => 'Jeudi',
    'vendredi' => 'Vendredi',
    'samedi'   => 'Samedi',
];
$statsParJour = [];
foreach ($joursSemaine as $k => $lbl) {
    $statsParJour[$k] = ['label' => $lbl, 'creneaux' => 0, 'heures' => 0.0];
}
foreach ($planning as $p) {
    $jKey = strtolower(trim((string)($p['jour'] ?? '')));
    if ($jKey !== '' && !isset($statsParJour[$jKey])) {
        $statsParJour[$jKey] = ['label' => ucfirst($jKey), 'creneaux' => 0, 'heures' => 0.0];
    }
    if ($jKey !== '') {
        $statsParJour[$jKey]['creneaux']++;
        $statsParJour[$jKey]['heures'] += (float)($p['duree_heures'] ?? 0);
    }
}

$canManageEns = Auth::can(PERMISSIONS::MANAGE_ENSEIGNANTS);
$canViewUsers = Auth::can(PERMISSIONS::VIEW_USERS);
$canViewClasses = Auth::can(PERMISSIONS::VIEW_CLASSES);
$canViewMatieres = Auth::can(PERMISSIONS::VIEW_MATIERES);
$canViewSalles = Auth::can(PERMISSIONS::VIEW_SALLES);
$canViewEmplois = Auth::can(PERMISSIONS::VIEW_EMPLOI_TEMPS);
$canManageEmplois = Auth::can(PERMISSIONS::MANAGE_EMPLOI_TEMPS);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
          <div style="width: 58px; height: 66px; border-radius: 12px; border: 2px solid #CBD5E1; overflow: hidden; background: #F8FAFC; flex-shrink: 0; box-shadow: 0 2px 6px rgba(15,23,42,0.08);">
            <img src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo <?= htmlspecialchars($nomComplet) ?>" onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholderPhotoUrl) ?>';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
          </div>
          <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
              <?= htmlspecialchars($nomComplet ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeEnseignant ?: '-') ?></code>
            <span style="background: <?= $cStyle['bg'] ?>; color: <?= $cStyle['fg'] ?>; border: 1px solid <?= $cStyle['bd'] ?>; padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 11.5px;">
              <?= htmlspecialchars($contratLabel) ?>
            </span>
            <?php if ($isActif): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Actif
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Inactif
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 8px 0 0 0;">
            <?php if ($grade !== ''): ?>
              Grade : <strong style="color: #0F172A;"><?= htmlspecialchars($grade) ?></strong>
            <?php endif; ?>
            <?php if ($anneeLibelle !== ''): ?>
              <?= $grade !== '' ? ' &bull; ' : '' ?>Session : <strong style="color: #1E3A5F;"><?= htmlspecialchars($anneeLibelle) ?></strong>
            <?php endif; ?>
            <?php if ($createdAt !== ''): ?>
              &bull; Enregistré le <strong style="color: #334155;"><?= htmlspecialchars($createdAt) ?></strong>
            <?php endif; ?>
            <?php if ($updatedAt !== ''): ?>
              &bull; Mis à jour le <strong style="color: #334155;"><?= htmlspecialchars($updatedAt) ?></strong>
            <?php endif; ?>
          </p>
          </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('enseignant/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <a href="<?= url('enseignant/imprimerFichePdf/' . $encryptedId) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;" title="Imprimer la fiche officielle enseignant en PDF">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Fiche PDF
          </a>
          <a href="<?= url('enseignant/imprimerProgrammePdf') . '?enseignant_code=' . urlencode($codeEnseignant) . '&date=' . date('Y-m-d') ?>" target="_blank" style="background: #F0F9FF; color: #0369A1; border: 1.5px solid #BAE6FD; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;" title="Imprimer le programme des cours du jour">
            <i data-lucide="calendar-clock" style="width: 16px; height: 16px;"></i> Programme du Jour
          </a>
          <?php if ($canViewUsers && $encryptedUserId !== ''): ?>
            <a href="<?= url('user/details/' . $encryptedUserId) ?>" style="background: #FAF5FF; color: #7E22CE; border: 1.5px solid #E9D5FF; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="user-check" style="width: 16px; height: 16px;"></i> Compte Accès
            </a>
          <?php endif; ?>
          <?php if ($canManageEns): ?>
            <button type="button" id="btn-toggle-statut-ens-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'pause-circle' : 'play-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Désactiver' : 'Activer' ?>
            </button>
            <a href="<?= url('enseignant/edition/' . $encryptedId) ?>" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; text-decoration: none;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 20px;">

        <!-- KPI 1 : Charge Horaire Hebdomadaire -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="clock" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Charge Hebdomadaire</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= htmlspecialchars($fmtHeures($totalHeuresHebdo)) ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">/ sem.</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span><strong style="color: #1D4ED8;"><?= $totalCreneaux ?></strong> séance<?= $totalCreneaux > 1 ? 's' : '' ?></span>
            <span><strong style="color: #0F172A;"><?= $totalJoursActifs ?></strong> jour<?= $totalJoursActifs > 1 ? 's' : '' ?> actif<?= $totalJoursActifs > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 2 : Classes & Étudiants -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="layers" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Classes Encadrées</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalClasses ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">classe<?= $totalClasses > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Étudiants suivis :</span>
            <span><strong style="color: #7E22CE;"><?= $totalEtudiants ?></strong> étudiant<?= $totalEtudiants > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 3 : Matières & Salles -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="book-open" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Matières Dispensées</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalMatieres ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">matière<?= $totalMatieres > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Salles fréquentées :</span>
            <span><strong style="color: #15803D;"><?= $totalSalles ?></strong> salle<?= $totalSalles > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <!-- KPI 4 : Contrat RH & Accès -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="award" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Statut RH &amp; Contrat</div>
              <div style="font-size: 19px; font-weight: 800; color: #0F172A; line-height: 1.2; margin-top: 3px;">
                <?= htmlspecialchars($contratLabel) ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Compte portail :</span>
            <strong style="color: <?= $isCompteActif ? '#15803D' : '#B91C1C' ?>;"><?= $isCompteActif ? 'Actif' : 'Inactif' ?></strong>
          </div>
        </div>

      </div>

      <!-- CARTE PROFIL IDENTITÉ, COORDONNÉES & CONTRAT RH -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; align-items: center; gap: 22px; flex-wrap: wrap;">
          <div style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0;">
            <div style="width: 106px; height: 122px; border-radius: 12px; border: 2px solid #CBD5E1; overflow: hidden; background: #F8FAFC; box-shadow: 0 3px 10px rgba(15,23,42,0.08);">
              <img src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo Enseignant <?= htmlspecialchars($nomComplet) ?>" onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholderPhotoUrl) ?>';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
            </div>
            <span style="margin-top: 7px; background: <?= $hasPhoto ? '#DCFCE7' : '#F1F5F9' ?>; color: <?= $hasPhoto ? '#15803D' : '#64748B' ?>; border: 1px solid <?= $hasPhoto ? '#86EFAC' : '#CBD5E1' ?>; padding: 2px 8px; border-radius: 10px; font-size: 10.5px; font-weight: 700;">
              <?= $hasPhoto ? 'Photo enregistrée' : 'Aucune photo' ?>
            </span>
          </div>

          <div style="flex: 1; min-width: 240px; display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Coordonnées</span>
              <div style="font-size: 13px; font-weight: 700; color: #0F172A; margin-top: 3px; word-break: break-all;">
                <?php if (!empty($item['email_enseignant'])): ?>
                  <a href="mailto:<?= htmlspecialchars($item['email_enseignant']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($item['email_enseignant']) ?></a>
                <?php else: ?>
                  <span style="color: #94A3B8;">Email non renseigné</span>
                <?php endif; ?>
              </div>
              <div style="font-size: 12.5px; color: #475569; margin-top: 2px;">
                Tél :
                <?php if (!empty($item['telephone_enseignant'])): ?>
                  <a href="tel:<?= htmlspecialchars($item['telephone_enseignant']) ?>" style="color: #0F172A; font-weight: 600; text-decoration: none;"><?= htmlspecialchars($item['telephone_enseignant']) ?></a>
                <?php else: ?>
                  <span>-</span>
                <?php endif; ?>
              </div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Identification</span>
              <div style="font-size: 13px; font-weight: 700; color: #0F172A; margin-top: 3px;">
                Matricule : <code style="color: #1E3A5F; background: #F1F5F9; padding: 1px 6px; border-radius: 4px;"><?= htmlspecialchars($matricule !== '' ? $matricule : '-') ?></code>
              </div>
              <div style="font-size: 12.5px; color: #475569; margin-top: 2px;">Genre : <strong><?= htmlspecialchars($sexeLibelle) ?></strong></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Autorisation d'Enseigner</span>
              <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-top: 3px;">
                <?= htmlspecialchars($numAutorisation !== '' ? $numAutorisation : 'Non renseignée') ?>
              </div>
              <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Dernière connexion : <strong><?= htmlspecialchars($lastLogin) ?></strong></div>
            </div>
          </div>
        </div>
      </div>

      <!-- BANDEAU RÉPARTITION PAR JOUR -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="calendar-days" style="width: 17px; height: 17px; color: #1E3A5F;"></i>
            Charge Hebdomadaire par Jour
          </h3>
          <span style="font-size: 12px; color: #64748B;">Cliquez sur un jour pour filtrer l'emploi du temps</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;">
          <?php foreach ($statsParJour as $jourKey => $jInfo):
            $hasSlots = ($jInfo['creneaux'] > 0);
          ?>
            <button type="button" class="ens-day-pill" data-jour="<?= htmlspecialchars($jourKey) ?>" style="text-align: left; background: <?= $hasSlots ? '#EFF6FF' : '#F8FAFC' ?>; border: 1.5px solid <?= $hasSlots ? '#BFDBFE' : '#E2E8F0' ?>; border-radius: 10px; padding: 11px 13px; cursor: pointer; transition: all 0.15s ease;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12.5px; font-weight: 800; color: <?= $hasSlots ? '#1E3A5F' : '#64748B' ?>;"><?= htmlspecialchars($jInfo['label']) ?></span>
                <span style="font-size: 11px; font-weight: 800; color: <?= $hasSlots ? '#0369A1' : '#94A3B8' ?>;">
                  <?= $hasSlots ? htmlspecialchars($fmtHeures($jInfo['heures'])) : 'Libre' ?>
                </span>
              </div>
              <div style="font-size: 11.5px; color: #64748B; margin-top: 4px; font-weight: 600;">
                <?= $hasSlots ? ($jInfo['creneaux'] . ' séance' . ($jInfo['creneaux'] > 1 ? 's' : '')) : '0 cours' ?>
              </div>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- SECTION 1 : EMPLOI DU TEMPS HEBDOMADAIRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="calendar" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Emploi du Temps Hebdomadaire (<span id="count-visible-ens-planning"><?= count($planning) ?></span>)
          </h3>

          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <?php if (!empty($planning)): ?>
              <select id="filter-ens-jour" style="padding: 7px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC; font-weight: 600;">
                <option value="">Tous les jours</option>
                <?php foreach ($statsParJour as $jourKey => $jInfo): ?>
                  <option value="<?= htmlspecialchars($jourKey) ?>"><?= htmlspecialchars($jInfo['label']) ?> (<?= $jInfo['creneaux'] ?>)</option>
                <?php endforeach; ?>
              </select>
              <div style="position: relative; min-width: 215px;">
                <input type="text" id="search-ens-planning" placeholder="Rechercher classe, matière, salle..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
              </div>
            <?php endif; ?>
            <?php if ($canManageEmplois): ?>
              <a href="<?= url('emploi/formulaire') ?>" style="background: #1E3A5F; color: #FFFFFF; border-radius: 8px; padding: 7px 14px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="calendar-plus" style="width: 15px; height: 15px;"></i> Programmer un créneau
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if (empty($planning)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 28px 0; font-style: italic; font-size: 13px;">
            Aucun créneau de cours n'est actuellement programmé pour cet enseignant sur cette session académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-ens-planning" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Jour</th>
                  <th style="padding: 10px 12px;">Créneau</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Salle</th>
                  <th style="padding: 10px 12px; text-align: right; width: 120px;">Accès</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $sumPlanningH = 0.0;
                  foreach ($planning as $idx => $p):
                    $jLower = strtolower(trim((string)($p['jour'] ?? '')));
                    $dur = (float)($p['duree_heures'] ?? 0);
                    $sumPlanningH += $dur;
                ?>
                  <tr class="ens-planning-row" data-jour="<?= htmlspecialchars($jLower) ?>" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <span style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px; font-weight: 800; font-size: 11.5px;">
                        <?= htmlspecialchars(ucfirst($jLower)) ?>
                      </span>
                    </td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <span style="font-family: monospace; font-weight: 700; color: #0F172A; font-size: 12.5px;">
                        <?= htmlspecialchars($fmtHeureShort($p['heure_debut'] ?? '')) ?> - <?= htmlspecialchars($fmtHeureShort($p['heure_fin'] ?? '')) ?>
                      </span>
                      <?php if ($dur > 0): ?>
                        <span style="margin-left: 6px; background: #F1F5F9; color: #475569; padding: 2px 6px; border-radius: 5px; font-size: 11px; font-weight: 700;">
                          <?= htmlspecialchars($fmtHeures($dur)) ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 700; color: #0F172A;">
                      <?php if ($canViewMatieres && !empty($p['encrypted_matiere_id'])): ?>
                        <a href="<?= url('matiere/details/' . $p['encrypted_matiere_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($p['libelle_matiere'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($p['libelle_matiere'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px;">
                      <div style="font-weight: 800; color: #1E3A5F;">
                        <?php if ($canViewClasses && !empty($p['encrypted_classe_id'])): ?>
                          <a href="<?= url('classe/details/' . $p['encrypted_classe_id']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($p['libelle_classe'] ?? '-') ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($p['libelle_classe'] ?? '-') ?>
                        <?php endif; ?>
                      </div>
                      <?php if (!empty($p['libelle_filiere'])): ?>
                        <div style="font-size: 11px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($p['libelle_filiere']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 600; color: #475569; white-space: nowrap;">
                      <?php if ($canViewSalles && !empty($p['encrypted_salle_id'])): ?>
                        <a href="<?= url('salle/details/' . $p['encrypted_salle_id']) ?>" style="color: #334155; text-decoration: none;"><?= htmlspecialchars($p['libelle_salle'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($p['libelle_salle'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <?php if ($canViewClasses && !empty($p['encrypted_classe_id'])): ?>
                          <a href="<?= url('classe/details/' . $p['encrypted_classe_id']) ?>" title="Fiche classe" aria-label="Fiche classe" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="graduation-cap" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($canViewMatieres && !empty($p['encrypted_matiere_id'])): ?>
                          <a href="<?= url('matiere/details/' . $p['encrypted_matiere_id']) ?>" title="Fiche matière" aria-label="Fiche matière" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E9D5FF; background: #FAF5FF; color: #7E22CE; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="book-open" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ($canViewSalles && !empty($p['encrypted_salle_id'])): ?>
                          <a href="<?= url('salle/details/' . $p['encrypted_salle_id']) ?>" title="Fiche salle" aria-label="Fiche salle" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #334155; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="building-2" style="width: 15px; height: 15px;"></i>
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background: #F8FAFC; border-top: 2px solid #E2E8F0;">
                  <td colspan="2" style="padding: 11px 12px; font-weight: 800; color: #1E3A5F;">Total Hebdomadaire :</td>
                  <td style="padding: 11px 12px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($fmtHeures($sumPlanningH)) ?></td>
                  <td colspan="4" style="padding: 11px 12px; text-align: right; font-weight: 700; color: #475569;">
                    <?= count($planning) ?> créneau<?= count($planning) > 1 ? 'x' : '' ?> programmé<?= count($planning) > 1 ? 's' : '' ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- SECTION 2 : SYNTHÈSE PAR CLASSE & MATIÈRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="book-open" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            Synthèse des Cours par Classe &amp; Matière (<span id="count-visible-ens-cours"><?= count($cours) ?></span>)
          </h3>

          <?php if (!empty($cours)): ?>
            <div style="position: relative; min-width: 220px;">
              <input type="text" id="search-ens-cours" placeholder="Filtrer classe, matière..." style="width: 100%; box-sizing: border-box; padding: 7px 12px 7px 32px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12.5px; color: #0F172A; background: #F8FAFC;">
              <i data-lucide="search" style="width: 14px; height: 14px; color: #94A3B8; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($cours)): ?>
          <div style="color: #94A3B8; text-align: center; padding: 26px 0; font-style: italic; font-size: 13px;">
            Aucun cours rattaché à cet enseignant pour la session active.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table id="table-ens-cours" class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 40px;">#</th>
                  <th style="padding: 10px 12px;">Classe</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px;">Créneaux &amp; Salle(s)</th>
                  <th style="padding: 10px 12px; text-align: center;">Séances</th>
                  <th style="padding: 10px 12px; text-align: right;">Volume Hebdo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $sumCoursCr = 0;
                  $sumCoursH = 0.0;
                  foreach ($cours as $idx => $c):
                    $nbCr = (int)($c['nb_creneaux'] ?? 0);
                    $volH = (float)($c['volume_horaire'] ?? 0);
                    $sumCoursCr += $nbCr;
                    $sumCoursH += $volH;
                ?>
                  <tr class="ens-cours-row" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px;">
                      <div style="font-weight: 800; color: #0F172A;">
                        <?php if ($canViewClasses && !empty($c['encrypted_classe_id'])): ?>
                          <a href="<?= url('classe/details/' . $c['encrypted_classe_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($c['libelle_classe'] ?? '-') ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($c['libelle_classe'] ?? '-') ?>
                        <?php endif; ?>
                      </div>
                      <?php if (!empty($c['libelle_filiere']) || !empty($c['libelle_niveau'])): ?>
                        <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                          <?= htmlspecialchars(trim(($c['libelle_filiere'] ?? '') . (!empty($c['libelle_niveau']) ? ' · ' . $c['libelle_niveau'] : ''))) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; font-weight: 700; color: #1E3A5F;">
                      <?php if ($canViewMatieres && !empty($c['encrypted_matiere_id'])): ?>
                        <a href="<?= url('matiere/details/' . $c['encrypted_matiere_id']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($c['libelle_matiere'] ?? '-') ?></a>
                      <?php else: ?>
                        <?= htmlspecialchars($c['libelle_matiere'] ?? '-') ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px;">
                      <?php if (!empty($c['creneaux_resume'])): ?>
                        <div style="font-weight: 600; color: #334155; font-size: 12.5px;"><?= htmlspecialchars($c['creneaux_resume']) ?></div>
                      <?php endif; ?>
                      <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
                        Salle : <strong style="color: #475569;"><?= htmlspecialchars(!empty($c['salles_noms']) ? $c['salles_noms'] : '-') ?></strong>
                      </div>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; color: #475569; font-weight: 600; white-space: nowrap;">
                      <?= $nbCr ?> séance<?= $nbCr > 1 ? 's' : '' ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right; white-space: nowrap;">
                      <span style="background: #EFF6FF; color: #0369A1; border: 1px solid #BAE6FD; padding: 3px 9px; border-radius: 6px; font-weight: 800; font-size: 12px;">
                        <?= $volH > 0 ? htmlspecialchars($fmtHeures($volH)) : '-' ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background: #F8FAFC; border-top: 2px solid #E2E8F0;">
                  <td colspan="4" style="padding: 11px 12px; font-weight: 800; color: #1E3A5F; text-align: right;">Total Charge Pédagogique :</td>
                  <td style="padding: 11px 12px; text-align: center; font-weight: 800; color: #0F172A;"><?= $sumCoursCr ?> séance<?= $sumCoursCr > 1 ? 's' : '' ?></td>
                  <td style="padding: 11px 12px; text-align: right; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($fmtHeures($sumCoursH)) ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script type="application/json" id="enseignants-details-config">
<?= json_encode([
    'racine'       => RACINE,
    'enseignantId' => $idEnseignant,
    'isActif'      => $isActif,
    'nomComplet'   => $nomComplet,
    'csrfToken'    => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/enseignants.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
