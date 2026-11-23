<?php
require_once __DIR__ . '/../../public/inc/header.php';

$user = $user ?? [];
$role = $role ?? [];
$userRoles = $userRoles ?? (!empty($role) ? [$role] : []);
$permissionsDetailed = $permissionsDetailed ?? [];
$anneesAcces = $anneesAcces ?? [];
$enseignantProfile = $enseignantProfile ?? null;
$encryptedEnseignantId = (string)($encryptedEnseignantId ?? '');
$recentLogs = $recentLogs ?? [];
$totalLogsCount = (int)($totalLogsCount ?? 0);
$etablissement = $etablissement ?? [];
$encryptedId = (string)($encryptedId ?? '');
$isSelf = !empty($isSelf);

$userId = (int)($user['id_user'] ?? 0);
$codeUser = (string)($user['code_user'] ?? '');
$matriculeUser = (string)($user['matricule_user'] ?? '');
$nomComplet = trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? ''));
$isActif = (strtolower(trim((string)($user['statut_user'] ?? ''))) === STATUTS::ACTIF);

// Initiales et photo utilisateur
$parts = preg_split('/\s+/', $nomComplet) ?: [];
$initials = '';
foreach (array_slice($parts, 0, 2) as $pt) {
    if ($pt !== '') $initials .= mb_strtoupper(mb_substr($pt, 0, 1, 'UTF-8'), 'UTF-8');
}
if ($initials === '') $initials = 'US';

$photoUser = !empty($user['photo_user']) ? (string)$user['photo_user'] : '';
$photoUrl = '';
if ($photoUser !== '') {
    $cleanRel = ltrim($photoUser, '/');
    if (file_exists(__DIR__ . '/../../' . $cleanRel)) {
        $photoUrl = url($cleanRel);
    } elseif (file_exists(__DIR__ . '/../../public/' . $cleanRel)) {
        $photoUrl = url('public/' . $cleanRel);
    } elseif (strpos($photoUser, 'http') === 0) {
        $photoUrl = $photoUser;
    }
}
$hasPhoto = ($photoUrl !== '');
$placeholderPhotoUrl = placeholderUrl('user');
$finalPhotoUrl = $hasPhoto ? $photoUrl : $placeholderPhotoUrl;

// Sexe libellé
$sexeLibelle = 'Non précisé';
if (!empty($user['sexe_user'])) {
    $s = strtoupper(trim((string)$user['sexe_user']));
    $sexeLibelle = ($s === 'M' || $s === 'HOMME') ? 'Masculin' : (($s === 'F' || $s === 'FEMME') ? 'Féminin' : $s);
}

// Agrégation des droits CRUD cumulés sur l'ensemble des rôles de l'utilisateur
$hasCreate = false;
$hasEdit = false;
$hasShow = false;
$hasDelete = false;
$isUserSuperAdmin = false;
foreach ($userRoles as $ur) {
    if (($ur['role_code'] ?? '') === ROLES::SUPER_ADMIN) {
        $isUserSuperAdmin = true;
    }
    if (!empty($ur['create_permission'])) $hasCreate = true;
    if (!empty($ur['edit_permission']))   $hasEdit = true;
    if (!isset($ur['show_permission']) || (int)$ur['show_permission'] === 1) $hasShow = true;
    if (!empty($ur['delete_permission'])) $hasDelete = true;
}

// Regroupement des permissions par module
$permsByModule = [];
foreach ($permissionsDetailed as $p) {
    $mod = !empty($p['module_permission']) ? (string)$p['module_permission'] : 'AUTRES';
    if (!isset($permsByModule[$mod])) {
        $permsByModule[$mod] = [];
    }
    $permsByModule[$mod][] = $p;
}
ksort($permsByModule);

// Répartition accès années (écriture / lecture)
$nbAccesEcriture = 0;
$nbAccesLecture = 0;
foreach ($anneesAcces as $aa) {
    if (strtolower((string)($aa['niveau_acces'] ?? '')) === 'ecriture') {
        $nbAccesEcriture++;
    } else {
        $nbAccesLecture++;
    }
}

$createdAt = !empty($user['created_at_user']) ? date('d/m/Y H:i', strtotime($user['created_at_user'])) : '-';
$updatedAt = !empty($user['updated_at_user']) ? date('d/m/Y H:i', strtotime($user['updated_at_user'])) : '';
$lastLogin = !empty($user['last_connexion']) ? date('d/m/Y H:i', strtotime($user['last_connexion'])) : 'Jamais connecté';

$canManageUsers = Auth::can(PERMISSIONS::MANAGE_USERS);
$canViewRoles = Auth::can(PERMISSIONS::VIEW_ROLES);
$canViewAnnees = Auth::can(PERMISSIONS::VIEW_ANNEES);
$canViewEnseignants = Auth::can(PERMISSIONS::VIEW_ENSEIGNANTS);
$canViewLogs = Auth::can(PERMISSIONS::VIEW_LOGS) || Auth::isSuperAdmin();
?>
<style>
  .user-details-grid {
    display: grid;
    grid-template-columns: minmax(300px, 1fr) minmax(420px, 2fr);
    gap: 22px;
    width: 100%;
    box-sizing: border-box;
  }
  @media (max-width: 980px) {
    .user-details-grid {
      grid-template-columns: 1fr;
    }
  }
  .info-detail-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #F1F5F9;
  }
  .info-detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  .info-icon-box {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
    background: #EFF6FF;
    color: #1E3A5F;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  @media print {
    .print-only-banner { display: block !important; }
    .page-header, .main-nav, .app-sidebar, .no-print, nav, aside, footer, header { display: none !important; }
    body, html, .app-layout, .main-content, .content-wrapper { background: #FFFFFF !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .card { box-shadow: none !important; border: 1px solid #CBD5E1 !important; page-break-inside: avoid; }
    .user-details-grid { display: grid !important; grid-template-columns: 280px 1fr !important; gap: 18px !important; }
  }
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE D'IMPRESSION (DYNAMIQUE DEPUIS LA TABLE CONFIGURATIONS) -->
      <div class="print-only-banner" style="display: none; margin-bottom: 20px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">
              <?= htmlspecialchars($etablissement['nom_etablissement'] ?? '') ?>
            </h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">
              FICHE OFFICIELLE D'IDENTIFICATION &amp; DE GESTION DU COMPTE UTILISATEUR
            </div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(Auth::fullName() ?: '-') ?></strong></div>
          </div>
        </div>
      </div>

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="user-check" style="width: 21px; height: 21px;"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
              <?= htmlspecialchars($nomComplet ?: '-') ?>
            </h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeUser ?: '-') ?></code>
            <?php if ($matriculeUser !== ''): ?>
              <span style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; padding: 3px 9px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                <?= htmlspecialchars($matriculeUser) ?>
              </span>
            <?php endif; ?>
            <?php if ($isActif): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Compte Actif
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Compte Inactif
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 8px 0 0 0;">
            Rôle principal : <strong style="color: #1E3A5F;"><?= htmlspecialchars($role['libelle_role'] ?? 'Non attribué') ?></strong>
            <?php if (!empty($user['libelle_fonction'])): ?>
              &bull; Fonction : <strong style="color: #0F172A;"><?= htmlspecialchars($user['libelle_fonction']) ?></strong>
            <?php endif; ?>
            <?php if (!empty($user['email_user'])): ?>
              &bull; <span><?= htmlspecialchars($user['email_user']) ?></span>
            <?php endif; ?>
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('user/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <a href="<?= url('user/imprimerFichePdf/' . $encryptedId) ?>" target="_blank" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;" title="Imprimer la fiche officielle en PDF">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Fiche PDF
          </a>
          <?php if ($canViewEnseignants && !empty($enseignantProfile) && $encryptedEnseignantId !== ''): ?>
            <a href="<?= url('enseignant/details/' . $encryptedEnseignantId) ?>" style="background: #F0FDF4; color: #15803D; border: 1.5px solid #BBF7D0; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; text-decoration: none;">
              <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i> Fiche Enseignant
            </a>
          <?php endif; ?>
          <?php if ($canManageUsers && !$isSelf): ?>
            <button type="button" id="btn-toggle-statut-user-detail" style="background: <?= $isActif ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isActif ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isActif ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 14px; cursor: pointer;">
              <i data-lucide="<?= $isActif ? 'pause-circle' : 'play-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isActif ? 'Désactiver' : 'Activer' ?>
            </button>
          <?php endif; ?>
          <?php if ($canManageUsers): ?>
            <a href="<?= url('user/edition/' . $encryptedId) ?>" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; text-decoration: none;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES KPI -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 16px; margin-bottom: 22px;">

        <!-- KPI 1 : Rôles Attribués -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="shield-check" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Rôles Système</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= count($userRoles) ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">rôle<?= count($userRoles) > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Groupe :</span>
            <strong style="color: #1E3A5F;"><?= htmlspecialchars($role['groupe'] ?? ($role['module'] ?? '-')) ?></strong>
          </div>
        </div>

        <!-- KPI 2 : Permissions RBAC -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="key-round" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Permissions RBAC</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= count($permissionsDetailed) ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">droit<?= count($permissionsDetailed) > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span><?= count($permsByModule) ?> module<?= count($permsByModule) > 1 ? 's' : '' ?></span>
            <strong style="color: #7E22CE;"><?= $isUserSuperAdmin ? 'Super Admin' : 'Contrôlé RBAC' ?></strong>
          </div>
        </div>

        <!-- KPI 3 : Accès Années Académiques -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="calendar-range" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Sessions Autorisées</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= count($anneesAcces) ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">année<?= count($anneesAcces) > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Écriture : <strong style="color: #15803D;"><?= $nbAccesEcriture ?></strong></span>
            <span>Lecture : <strong style="color: #0369A1;"><?= $nbAccesLecture ?></strong></span>
          </div>
        </div>

        <!-- KPI 4 : Activité & Connexion -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="activity" style="width: 21px; height: 21px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Journal d'Activité</div>
              <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.1; margin-top: 2px;">
                <?= $totalLogsCount ?> <span style="font-size: 12px; font-weight: 600; color: #64748B;">action<?= $totalLogsCount > 1 ? 's' : '' ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
            <span>Dernier accès :</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($lastLogin) ?></strong>
          </div>
        </div>

      </div>

      <!-- CORPS PRINCIPAL 2 COLONNES -->
      <div class="user-details-grid">

        <!-- COLONNE GAUCHE : IDENTITÉ + PROFIL ENSEIGNANT LIÉ -->
        <div style="display: flex; flex-direction: column; gap: 22px;">

          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="text-align: center; padding-bottom: 18px; border-bottom: 1px solid #F1F5F9;">
              <div style="width: 112px; height: 112px; border-radius: 50%; border: 3px solid #CBD5E1; overflow: hidden; background: #F8FAFC; margin: 0 auto 14px auto; box-shadow: 0 4px 12px rgba(15,23,42,0.08);">
                <img src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo Utilisateur <?= htmlspecialchars($nomComplet) ?>" onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholderPhotoUrl) ?>';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
              </div>

              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.3;">
                <?= htmlspecialchars($nomComplet ?: '-') ?>
              </h2>

              <div style="margin-top: 8px; display: flex; flex-wrap: wrap; justify-content: center; gap: 6px;">
                <?php if (!empty($userRoles)): ?>
                  <?php foreach ($userRoles as $urBadge): ?>
                    <span style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 700; font-size: 11.5px; padding: 3px 10px; border-radius: 20px;">
                      <?= htmlspecialchars($urBadge['libelle_role'] ?? $urBadge['role_code']) ?>
                    </span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span style="background: #F1F5F9; color: #64748B; font-weight: 700; font-size: 11.5px; padding: 3px 10px; border-radius: 20px;">Aucun rôle</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="padding-top: 14px;">
              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="hash" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Code Compte</span>
                  <div style="font-size: 13px; font-weight: 700; color: #1E3A5F; font-family: monospace;"><?= htmlspecialchars($codeUser ?: '-') ?></div>
                </div>
              </div>

              <?php if ($matriculeUser !== ''): ?>
              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="badge-check" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Matricule</span>
                  <div style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($matriculeUser) ?></div>
                </div>
              </div>
              <?php endif; ?>

              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="briefcase" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Fonction / Poste</span>
                  <div style="font-size: 13px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars(!empty($user['libelle_fonction']) ? $user['libelle_fonction'] : 'Non renseignée') ?></div>
                </div>
              </div>

              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="user" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Genre / Sexe</span>
                  <div style="font-size: 13px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($sexeLibelle) ?></div>
                </div>
              </div>

              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="mail" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1; min-width: 0;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Email de Connexion</span>
                  <div style="font-size: 13px; font-weight: 600; color: #0F172A; word-break: break-all;">
                    <?php if (!empty($user['email_user'])): ?>
                      <a href="mailto:<?= htmlspecialchars($user['email_user']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($user['email_user']) ?></a>
                    <?php else: ?>
                      <span style="color: #94A3B8;">Non renseigné</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="phone" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Téléphone</span>
                  <div style="font-size: 13px; font-weight: 600; color: #0F172A;">
                    <?php if (!empty($user['telephone_user'])): ?>
                      <a href="tel:<?= htmlspecialchars($user['telephone_user']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($user['telephone_user']) ?></a>
                    <?php else: ?>
                      <span style="color: #94A3B8;">Non renseigné</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="calendar" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Création du Compte</span>
                  <div style="font-size: 12.5px; color: #334155; font-weight: 600;"><?= htmlspecialchars($createdAt) ?></div>
                  <?php if ($updatedAt !== ''): ?>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Mis à jour : <?= htmlspecialchars($updatedAt) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="info-detail-row">
                <div class="info-icon-box"><i data-lucide="clock" style="width: 15px; height: 15px;"></i></div>
                <div style="flex: 1;">
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Dernière Connexion</span>
                  <div style="font-size: 12.5px; color: #334155; font-weight: 600;"><?= htmlspecialchars($lastLogin) ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- PROFIL ENSEIGNANT LIÉ (SI EXISTANT) -->
          <?php if (!empty($enseignantProfile)): ?>
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 22px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #DCFCE7;">
              <h3 style="font-size: 14px; font-weight: 800; color: #15803D; margin: 0; display: flex; align-items: center; gap: 7px;">
                <i data-lucide="graduation-cap" style="width: 17px; height: 17px;"></i> Profil Enseignant Lié
              </h3>
              <span style="background: <?= (($enseignantProfile['statut_enseignant'] ?? '') === STATUTS::ACTIF) ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= (($enseignantProfile['statut_enseignant'] ?? '') === STATUTS::ACTIF) ? '#15803D' : '#B91C1C' ?>; padding: 2px 9px; border-radius: 10px; font-size: 11px; font-weight: 800;">
                <?= (($enseignantProfile['statut_enseignant'] ?? '') === STATUTS::ACTIF) ? 'Actif' : 'Inactif' ?>
              </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12.5px; color: #334155;">
              <div style="display: flex; justify-content: space-between; gap: 8px;">
                <span style="color: #64748B;">Grade / Spécialité :</span>
                <strong style="color: #0F172A; text-align: right;"><?= htmlspecialchars($enseignantProfile['grade_enseignant'] ?: '-') ?></strong>
              </div>
              <div style="display: flex; justify-content: space-between; gap: 8px;">
                <span style="color: #64748B;">Contrat :</span>
                <strong style="color: #15803D; text-transform: capitalize;"><?= htmlspecialchars($enseignantProfile['type_contrat'] ?: '-') ?></strong>
              </div>
              <?php if (!empty($enseignantProfile['numero_autorisation'])): ?>
              <div style="display: flex; justify-content: space-between; gap: 8px;">
                <span style="color: #64748B;">Autorisation :</span>
                <code style="font-weight: 700; color: #1E3A5F;"><?= htmlspecialchars($enseignantProfile['numero_autorisation']) ?></code>
              </div>
              <?php endif; ?>
            </div>

            <?php if ($canViewEnseignants && $encryptedEnseignantId !== ''): ?>
              <a href="<?= url('enseignant/details/' . $encryptedEnseignantId) ?>" style="margin-top: 14px; width: 100%; box-sizing: border-box; background: #F0FDF4; color: #15803D; border: 1px solid #86EFAC; border-radius: 8px; padding: 8px 12px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Ouvrir la fiche Enseignant complète
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>

        <!-- COLONNE DROITE : RÔLES, CRUD, ANNÉES, PERMISSIONS & AUDIT -->
        <div style="display: flex; flex-direction: column; gap: 22px;">

          <!-- CARTE 1 : RÔLES SYSTÈME & MATRICE CRUD CUMULÉE -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
                Rôles Attribués &amp; Privilèges CRUD (<?= count($userRoles) ?>)
              </h3>
            </div>

            <?php if (!empty($userRoles)): ?>
              <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px;">
                <?php foreach ($userRoles as $ur): ?>
                  <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                      <div style="font-size: 14.5px; font-weight: 800; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="award" style="width: 17px; height: 17px;"></i>
                        <?php if ($canViewRoles && !empty($ur['encrypted_role_id'])): ?>
                          <a href="<?= url('role/details/' . $ur['encrypted_role_id']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($ur['libelle_role'] ?? $ur['role_code']) ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($ur['libelle_role'] ?? $ur['role_code']) ?>
                        <?php endif; ?>
                      </div>
                      <div style="display: flex; align-items: center; gap: 6px;">
                        <code style="font-size: 11px; color: #475569; background: #FFFFFF; border: 1px solid #CBD5E1; padding: 2px 7px; border-radius: 5px; font-weight: 700;"><?= htmlspecialchars($ur['role_code'] ?? '') ?></code>
                        <span style="background: #E2E8F0; color: #334155; font-weight: 700; font-size: 11px; padding: 3px 9px; border-radius: 6px;">
                          <?= htmlspecialchars($ur['groupe'] ?? ($ur['module'] ?? 'Standard')) ?>
                        </span>
                      </div>
                    </div>

                    <?php if (!empty($ur['description'])): ?>
                      <p style="color: #64748B; font-size: 12.5px; margin: 7px 0 0 25px; line-height: 1.4;"><?= htmlspecialchars($ur['description']) ?></p>
                    <?php endif; ?>

                    <div style="margin-top: 10px; padding-top: 9px; border-top: 1px dashed #E2E8F0; display: flex; flex-wrap: wrap; gap: 14px;">
                      <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['create_permission']) ? '#15803D' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= !empty($ur['create_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Créer
                      </span>
                      <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['edit_permission']) ? '#0284C7' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= !empty($ur['edit_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Modifier
                      </span>
                      <span style="font-size: 12px; font-weight: 700; color: <?= (!isset($ur['show_permission']) || (int)$ur['show_permission'] === 1) ? '#334155' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= (!isset($ur['show_permission']) || (int)$ur['show_permission'] === 1) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Consulter
                      </span>
                      <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['delete_permission']) ? '#DC2626' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= !empty($ur['delete_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Supprimer
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="color: #94A3B8; font-style: italic; padding: 12px 0; font-size: 13px;">Aucun rôle attribué pour le moment.</div>
            <?php endif; ?>

            <!-- Matrice CRUD Cumulée -->
            <div style="border-top: 1px solid #F1F5F9; padding-top: 14px;">
              <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 10px;">
                Synthèse des Actions Cumulées
              </div>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                <?php
                  $crudItems = [
                    ['label' => 'Création', 'ok' => $hasCreate, 'okColor' => '#15803D'],
                    ['label' => 'Modification', 'ok' => $hasEdit, 'okColor' => '#0284C7'],
                    ['label' => 'Consultation', 'ok' => $hasShow, 'okColor' => '#15803D'],
                    ['label' => 'Suppression', 'ok' => $hasDelete, 'okColor' => '#DC2626'],
                  ];
                  foreach ($crudItems as $ci):
                ?>
                  <div style="display: flex; align-items: center; gap: 9px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 12px; border-radius: 8px;">
                    <i data-lucide="<?= $ci['ok'] ? 'check-circle-2' : 'x-circle' ?>" style="width: 18px; height: 18px; color: <?= $ci['ok'] ? $ci['okColor'] : '#94A3B8' ?>; flex-shrink: 0;"></i>
                    <div>
                      <div style="font-weight: 700; font-size: 12.5px; color: #0F172A;"><?= $ci['label'] ?></div>
                      <div style="font-size: 11px; color: <?= $ci['ok'] ? $ci['okColor'] : '#94A3B8' ?>; font-weight: 700;"><?= $ci['ok'] ? 'Autorisé' : 'Refusé' ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- CARTE 2 : ACCÈS AUX ANNÉES ACADÉMIQUES -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
              <i data-lucide="calendar-range" style="width: 18px; height: 18px; color: #15803D;"></i>
              Accès aux Sessions Académiques (<?= count($anneesAcces) ?>)
            </h3>

            <?php if (empty($anneesAcces)): ?>
              <div style="color: #94A3B8; font-style: italic; font-size: 13px; padding: 10px 0;">
                Aucun droit d'accès spécifique aux années académiques configuré (accès standard sur la session active).
              </div>
            <?php else: ?>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">
                <?php foreach ($anneesAcces as $aa):
                  $isWrite = (strtolower((string)($aa['niveau_acces'] ?? '')) === 'ecriture');
                  $stAnn = strtolower((string)($aa['statut_annee'] ?? ''));
                ?>
                  <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                    <div>
                      <div style="font-weight: 800; font-size: 13.5px; color: #0F172A;">
                        <?php if ($canViewAnnees && !empty($aa['encrypted_annee_id'])): ?>
                          <a href="<?= url('annee/details/' . $aa['encrypted_annee_id']) ?>" style="color: #0F172A; text-decoration: none;"><?= htmlspecialchars($aa['libelle_annee'] ?? $aa['annee_code']) ?></a>
                        <?php else: ?>
                          <?= htmlspecialchars($aa['libelle_annee'] ?? $aa['annee_code']) ?>
                        <?php endif; ?>
                      </div>
                      <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                        <?= $stAnn === STATUTS::ACTIF ? 'Session active' : ($stAnn === 'cloture' ? 'Session clôturée' : ucfirst($stAnn ?: '-')) ?>
                      </div>
                    </div>
                    <span style="background: <?= $isWrite ? '#DCFCE7' : '#E0F2FE' ?>; color: <?= $isWrite ? '#15803D' : '#0369A1' ?>; border: 1px solid <?= $isWrite ? '#86EFAC' : '#BAE6FD' ?>; padding: 3px 9px; border-radius: 12px; font-weight: 800; font-size: 11px; white-space: nowrap;">
                      <?= $isWrite ? 'Écriture' : 'Lecture' ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- CARTE 3 : PERMISSIONS RBAC EFFECTIVES -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="key-round" style="width: 18px; height: 18px; color: #7E22CE;"></i>
                Permissions RBAC Effectives (<span id="count-visible-user-perms"><?= count($permissionsDetailed) ?></span>)
              </h3>

              <?php if (!empty($permissionsDetailed)): ?>
                <div class="no-print" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                  <select id="filter-user-perm-module" style="padding: 6px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12px; color: #0F172A; background: #F8FAFC; font-weight: 600;">
                    <option value="">Tous les modules (<?= count($permsByModule) ?>)</option>
                    <?php foreach ($permsByModule as $modName => $modPerms): ?>
                      <option value="<?= htmlspecialchars($modName) ?>"><?= htmlspecialchars($modName) ?> (<?= count($modPerms) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                  <div style="position: relative; min-width: 190px;">
                    <input type="text" id="search-user-permissions" placeholder="Filtrer une permission..." style="width: 100%; box-sizing: border-box; padding: 6px 10px 6px 30px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 12px; color: #0F172A; background: #F8FAFC;">
                    <i data-lucide="search" style="width: 13px; height: 13px; color: #94A3B8; position: absolute; left: 9px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <?php if (empty($permissionsDetailed)): ?>
              <div style="color: #94A3B8; font-style: italic; font-size: 13px; padding: 10px 0;">
                Aucune permission granulaire active n'est associée aux rôles de ce compte.
              </div>
            <?php else: ?>
              <div id="user-permissions-list" style="display: flex; flex-direction: column; gap: 12px; max-height: 360px; overflow-y: auto; padding-right: 4px;">
                <?php foreach ($permsByModule as $modName => $modPerms): ?>
                  <div class="user-perm-module-group" data-module="<?= htmlspecialchars($modName) ?>" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px;">
                    <div style="font-size: 11.5px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 8px; display: flex; justify-content: space-between;">
                      <span><?= htmlspecialchars($modName) ?></span>
                      <span style="color: #64748B;"><?= count($modPerms) ?></span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                      <?php foreach ($modPerms as $perm): ?>
                        <span class="user-perm-item" data-module="<?= htmlspecialchars($modName) ?>" data-search="<?= htmlspecialchars(strtolower(($perm['libelle_permission'] ?? '') . ' ' . ($perm['code_permission'] ?? '') . ' ' . $modName)) ?>" title="<?= htmlspecialchars($perm['code_permission'] ?? '') ?>" style="background: #FFFFFF; color: #334155; border: 1px solid #CBD5E1; padding: 4px 9px; border-radius: 6px; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                          <span style="width: 6px; height: 6px; border-radius: 50%; background: #7E22CE; flex-shrink: 0;"></span>
                          <?= htmlspecialchars($perm['libelle_permission'] ?: $perm['code_permission']) ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- CARTE 4 : JOURNAL D'ACTIVITÉS RÉCENTES (SI DROIT VIEW_LOGS) -->
          <?php if ($canViewLogs): ?>
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
              <i data-lucide="history" style="width: 18px; height: 18px; color: #B45309;"></i>
              Activités Récentes (<?= count($recentLogs) ?><?= $totalLogsCount > count($recentLogs) ? (' / ' . $totalLogsCount) : '' ?>)
            </h3>

            <?php if (empty($recentLogs)): ?>
              <div style="color: #94A3B8; font-style: italic; font-size: 13px; padding: 10px 0;">
                Aucune action n'a encore été enregistrée dans le journal d'audit pour ce compte.
              </div>
            <?php else: ?>
              <div style="width: 100%; overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                  <thead>
                    <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11px; text-transform: uppercase;">
                      <th style="padding: 8px 10px;">Date</th>
                      <th style="padding: 8px 10px;">Action</th>
                      <th style="padding: 8px 10px;">Module</th>
                      <th style="padding: 8px 10px;">Description</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recentLogs as $lg): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 9px 10px; white-space: nowrap; color: #475569; font-weight: 600;">
                          <?= !empty($lg['created_at']) ? date('d/m/Y H:i', strtotime($lg['created_at'])) : '-' ?>
                        </td>
                        <td style="padding: 9px 10px; white-space: nowrap;">
                          <code style="background: #EFF6FF; color: #1E3A5F; padding: 2px 7px; border-radius: 5px; font-weight: 700; font-size: 11px;"><?= htmlspecialchars($lg['action'] ?? '-') ?></code>
                        </td>
                        <td style="padding: 9px 10px; white-space: nowrap; font-weight: 700; color: #334155;">
                          <?= htmlspecialchars($lg['module'] ?? '-') ?>
                        </td>
                        <td style="padding: 9px 10px; color: #0F172A;">
                          <?= htmlspecialchars($lg['description'] ?? '-') ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>

      </div>
    </div>
  </main>
</div>

<script type="application/json" id="users-details-config">
<?= json_encode([
    'racine'    => RACINE,
    'userId'    => $userId,
    'isActif'   => $isActif,
    'nomComplet'=> $nomComplet,
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/users.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
