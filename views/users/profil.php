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
$encryptedId = (string)($encryptedId ?? ($editId ?? ''));

$userId = (int)($user['id_user'] ?? (Auth::id() ?: 0));
$codeUser = (string)($user['code_user'] ?? '');
$matriculeUser = (string)($user['matricule_user'] ?? '');
$nomUser = (string)($user['nom_user'] ?? (Auth::user('nom_user') ?? ''));
$prenomUser = (string)($user['prenom_user'] ?? (Auth::user('prenom_user') ?? ''));
$nomComplet = trim($nomUser . ' ' . $prenomUser);
$emailUser = (string)($user['email_user'] ?? '');
$telUser = (string)($user['telephone_user'] ?? '');
$libelleFonction = (string)($user['libelle_fonction'] ?? '');
$isActif = (strtolower(trim((string)($user['statut_user'] ?? ''))) === STATUTS::ACTIF);

// Résolution sécurisée de la photo de profil avec repli visuel placeholderUrl('user')
$photoUser = !empty($user['photo_user']) ? (string)$user['photo_user'] : '';
$photoUrl = '';
if ($photoUser !== '') {
    $cleanRel = ltrim($photoUser, '/');
    if (strpos($photoUser, 'http') === 0 || strpos($photoUser, 'data:') === 0) {
        $photoUrl = $photoUser;
    } elseif (file_exists(__DIR__ . '/../../' . $cleanRel)) {
        $photoUrl = url($cleanRel);
    } elseif (file_exists(__DIR__ . '/../../public/' . $cleanRel)) {
        $photoUrl = url('public/' . $cleanRel);
    } elseif (file_exists(__DIR__ . '/../../public/assets/images/users/' . $cleanRel)) {
        $photoUrl = url('public/assets/images/users/' . $cleanRel);
    }
}
$placeholderPhotoUrl = placeholderUrl('user');
$finalPhotoUrl = ($photoUrl !== '') ? $photoUrl : $placeholderPhotoUrl;

// Sexe normalisé
$rawSexe = strtoupper(trim((string)($user['sexe_user'] ?? '')));
$sexeValue = ($rawSexe === 'F' || $rawSexe === 'FEMININ' || $rawSexe === 'FEMME') ? 'F' : (($rawSexe === 'M' || $rawSexe === 'MASCULIN' || $rawSexe === 'HOMME') ? 'M' : '');
$sexeLibelle = $sexeValue === 'M' ? 'Masculin' : ($sexeValue === 'F' ? 'Féminin' : 'Non précisé');

// Agrégation des privilèges CRUD cumulés et détection SuperAdmin
$hasCreate = false;
$hasEdit = false;
$hasShow = false;
$hasDelete = false;
$isUserSuperAdmin = Auth::isSuperAdmin();
foreach ($userRoles as $ur) {
    if (($ur['role_code'] ?? '') === ROLES::SUPER_ADMIN) {
        $isUserSuperAdmin = true;
    }
    if (!empty($ur['create_permission'])) $hasCreate = true;
    if (!empty($ur['edit_permission']))   $hasEdit = true;
    if (!isset($ur['show_permission']) || (int)$ur['show_permission'] === 1) $hasShow = true;
    if (!empty($ur['delete_permission'])) $hasDelete = true;
}

// Regroupement des permissions effectives par module
$permsByModule = [];
foreach ($permissionsDetailed as $p) {
    $mod = !empty($p['module_permission']) ? (string)$p['module_permission'] : 'AUTRES';
    if (!isset($permsByModule[$mod])) {
        $permsByModule[$mod] = [];
    }
    $permsByModule[$mod][] = $p;
}
ksort($permsByModule);

// Répartition des accès années
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
$updatedAt = !empty($user['updated_at_user']) ? date('d/m/Y H:i', strtotime($user['updated_at_user'])) : '-';
$lastLogin = !empty($user['last_connexion']) ? date('d/m/Y H:i', strtotime($user['last_connexion'])) : '-';

$canViewUsers = Auth::can(PERMISSIONS::VIEW_USERS);
$canViewRoles = Auth::can(PERMISSIONS::VIEW_ROLES);
$canViewAnnees = Auth::can(PERMISSIONS::VIEW_ANNEES);
$canViewEnseignants = Auth::can(PERMISSIONS::VIEW_ENSEIGNANTS);
$csrfToken = Validator::generateCsrfToken();
?>
<style>
  .profil-layout-grid {
    display: grid;
    grid-template-columns: minmax(290px, 1fr) minmax(440px, 2.15fr);
    gap: 22px;
    width: 100%;
    box-sizing: border-box;
    align-items: start;
  }
  @media (max-width: 992px) {
    .profil-layout-grid {
      grid-template-columns: 1fr;
    }
  }
  .profil-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }
  @media (max-width: 680px) {
    .profil-form-grid {
      grid-template-columns: 1fr;
    }
  }
  .profil-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
  }
  .profil-info-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #F1F5F9;
  }
  .profil-info-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  .profil-icon-box {
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
  .profil-tab-btn {
    background: #F8FAFC;
    color: #475569;
    border: 1px solid #E2E8F0;
    border-radius: 9px;
    padding: 9px 15px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    transition: all 0.18s ease;
  }
  .profil-tab-btn:hover {
    background: #EFF6FF;
    color: #1E3A5F;
    border-color: #BFDBFE;
  }
  .profil-tab-btn.is-active {
    background: #1E3A5F;
    color: #FFFFFF;
    border-color: #1E3A5F;
    font-weight: 800;
    box-shadow: 0 2px 6px rgba(30, 58, 95, 0.22);
  }
  .profil-input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 13px;
    font-size: 13.5px;
    border-radius: 8px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #0F172A;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }
  .profil-input:focus {
    outline: none;
    border-color: #1E3A5F;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.12);
  }
  .profil-input[readonly] {
    background: #F8FAFC;
    color: #64748B;
    cursor: not-allowed;
  }
  .pwd-strength-track {
    height: 6px;
    background: #E2E8F0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 8px;
  }
  .pwd-strength-bar {
    height: 100%;
    width: 0%;
    border-radius: 999px;
    transition: width 0.25s ease, background-color 0.25s ease;
  }
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- BANNIÈRE HERO PROFIL UTILISATEUR -->
      <div class="card" style="background: linear-gradient(135deg, #1E3A5F 0%, #152B47 65%, #0F2138 100%); border-radius: 14px; padding: 24px 28px; color: #FFFFFF; margin-bottom: 22px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.14); border: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
          <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
            <!-- Portrait Photo 120x130 avec téléversement instantané -->
            <div style="position: relative; flex-shrink: 0;">
              <div style="width: 120px; height: 130px; border-radius: 12px; border: 3px solid rgba(255,255,255,0.85); overflow: hidden; background: #EFF6FF; box-shadow: 0 6px 16px rgba(0,0,0,0.28);">
                <img id="profil-hero-photo" src="<?= htmlspecialchars($finalPhotoUrl) ?>" alt="Photo de profil" style="width: 120px; height: 130px; object-fit: cover; display: block;">
              </div>
              <button type="button" id="btn-trigger-photo-profil" title="Changer ma photo de profil" style="position: absolute; right: -8px; bottom: -8px; width: 34px; height: 34px; border-radius: 50%; background: #FFFFFF; color: #1E3A5F; border: 2px solid #1E3A5F; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 3px 8px rgba(0,0,0,0.22);">
                <i data-lucide="camera" style="width: 16px; height: 16px;"></i>
              </button>
              <input type="file" id="input-photo-profil" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
            </div>

            <!-- Identité & Badges -->
            <div>
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                <span style="background: <?= $isActif ? 'rgba(34, 197, 94, 0.22)' : 'rgba(239, 68, 68, 0.22)' ?>; color: <?= $isActif ? '#BBF7D0' : '#FECACA' ?>; border: 1px solid <?= $isActif ? 'rgba(134, 239, 172, 0.4)' : 'rgba(252, 165, 165, 0.4)' ?>; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px;">
                  <?= $isActif ? 'Compte Actif' : 'Compte Inactif' ?>
                </span>
                <?php if ($matriculeUser !== ''): ?>
                  <span style="background: rgba(255,255,255,0.14); color: #E2E8F0; font-size: 11.5px; font-weight: 700; font-family: monospace; padding: 3px 10px; border-radius: 999px;">
                    <?= htmlspecialchars($matriculeUser) ?>
                  </span>
                <?php endif; ?>
                <?php if ($codeUser !== ''): ?>
                  <span style="background: rgba(255,255,255,0.10); color: #CBD5E1; font-size: 11px; font-weight: 700; font-family: monospace; padding: 3px 9px; border-radius: 999px;">
                    <?= htmlspecialchars($codeUser) ?>
                  </span>
                <?php endif; ?>
              </div>

              <h1 id="profil-hero-name" style="font-size: 23px; font-weight: 900; color: #FFFFFF; margin: 0 0 4px 0; letter-spacing: -0.2px;">
                <?= htmlspecialchars($nomComplet !== '' ? $nomComplet : '-') ?>
              </h1>

              <div style="font-size: 13px; color: #CBD5E1; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 10px;">
                <?php if ($libelleFonction !== ''): ?>
                  <span style="display: inline-flex; align-items: center; gap: 5px; font-weight: 600; color: #F1F5F9;">
                    <i data-lucide="briefcase" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($libelleFonction) ?>
                  </span>
                <?php endif; ?>
                <span id="profil-hero-email" style="display: inline-flex; align-items: center; gap: 5px;">
                  <i data-lucide="mail" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($emailUser !== '' ? $emailUser : '-') ?>
                </span>
                <span id="profil-hero-tel" style="display: inline-flex; align-items: center; gap: 5px;">
                  <i data-lucide="phone" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($telUser !== '' ? $telUser : '-') ?>
                </span>
              </div>

              <!-- Rôles attribués -->
              <?php if (!empty($userRoles)): ?>
                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                  <?php foreach ($userRoles as $ur): ?>
                    <span style="background: rgba(59, 130, 246, 0.25); color: #DBEAFE; border: 1px solid rgba(147, 197, 253, 0.35); font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                      <i data-lucide="shield" style="width: 12px; height: 12px;"></i>
                      <?= htmlspecialchars($ur['libelle_role'] ?? ($ur['role_code'] ?? '-')) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Actions rapides Hero -->
          <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <?php if ($canViewUsers && $encryptedId !== ''): ?>
              <a href="<?= url('user/imprimerFichePdf/' . $encryptedId) ?>" target="_blank" style="background: rgba(255,255,255,0.14); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.28); font-weight: 700; font-size: 13px; border-radius: 9px; padding: 9px 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;">
                <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Ma Fiche PDF
              </a>
            <?php endif; ?>
            <?php if ($enseignantProfile && $canViewEnseignants && $encryptedEnseignantId !== ''): ?>
              <a href="<?= url('enseignant/details/' . $encryptedEnseignantId) ?>" style="background: rgba(255,255,255,0.14); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.28); font-weight: 700; font-size: 13px; border-radius: 9px; padding: 9px 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;">
                <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i> Profil Enseignant
              </a>
            <?php endif; ?>
            <a href="<?= url('user/decon') ?>" style="background: #DC2626; color: #FFFFFF; border: 1px solid #EF4444; font-weight: 700; font-size: 13px; border-radius: 9px; padding: 9px 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;">
              <i data-lucide="log-out" style="width: 16px; height: 16px;"></i> Déconnexion
            </a>
          </div>
        </div>
      </div>

      <!-- BANDEAU 4 KPIS SYNTHÈSE DU COMPTE -->
      <div class="profil-kpi-grid">
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Rôles Attribués</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 3px;"><?= count($userRoles) ?></div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($role['libelle_role'] ?? '-') ?></div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 9px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="shield-check" style="width: 19px; height: 19px;"></i>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; border-left: 4px solid #2563EB; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Permissions RBAC</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 3px;"><?= $isUserSuperAdmin ? 'Toutes' : count($permissionsDetailed) ?></div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;"><?= count($permsByModule) ?> module(s) actifs</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 9px; background: #DBEAFE; color: #2563EB; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="key-round" style="width: 19px; height: 19px;"></i>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; border-left: 4px solid #059669; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Accès Années</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 3px;"><?= $isUserSuperAdmin ? 'Global' : count($anneesAcces) ?></div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
              <?= $isUserSuperAdmin ? 'Toutes les années' : ($nbAccesEcriture . ' écriture • ' . $nbAccesLecture . ' lecture') ?>
            </div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 9px; background: #D1FAE5; color: #059669; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar-range" style="width: 19px; height: 19px;"></i>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; border-left: 4px solid #7C3AED; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Actions Journalisées</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 3px;"><?= number_format($totalLogsCount, 0, ',', ' ') ?></div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Dern. connexion : <?= htmlspecialchars($lastLogin) ?></div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 9px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="activity" style="width: 19px; height: 19px;"></i>
          </div>
        </div>
      </div>

      <!-- GRILLE PRINCIPALE 2 COLONNES -->
      <div class="profil-layout-grid">

        <!-- COLONNE GAUCHE : SYNTHÈSE & INFORMATIONS SYSTÈME -->
        <div style="display: flex; flex-direction: column; gap: 18px;">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="font-size: 14px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.4px; margin: 0 0 12px 0; padding-bottom: 10px; border-bottom: 2px solid #F1F5F9; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="id-card" style="width: 17px; height: 17px;"></i> Synthèse du Compte
            </h3>

            <div class="profil-info-row">
              <div class="profil-icon-box"><i data-lucide="hash" style="width: 15px; height: 15px;"></i></div>
              <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Matricule &amp; Code</div>
                <div style="font-size: 13px; font-weight: 800; color: #0F172A; font-family: monospace; margin-top: 2px;">
                  <?= htmlspecialchars($matriculeUser !== '' ? $matriculeUser : '-') ?>
                  <?php if ($codeUser !== ''): ?><span style="color: #64748B; font-weight: 600;"> / <?= htmlspecialchars($codeUser) ?></span><?php endif; ?>
                </div>
              </div>
            </div>

            <div class="profil-info-row">
              <div class="profil-icon-box"><i data-lucide="briefcase" style="width: 15px; height: 15px;"></i></div>
              <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Fonction</div>
                <div style="font-size: 13px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($libelleFonction !== '' ? $libelleFonction : 'Non assignée') ?></div>
              </div>
            </div>

            <div class="profil-info-row">
              <div class="profil-icon-box"><i data-lucide="user" style="width: 15px; height: 15px;"></i></div>
              <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Genre</div>
                <div id="profil-summary-sexe" style="font-size: 13px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($sexeLibelle) ?></div>
              </div>
            </div>

            <div class="profil-info-row">
              <div class="profil-icon-box"><i data-lucide="calendar-plus" style="width: 15px; height: 15px;"></i></div>
              <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Création &amp; Mise à jour</div>
                <div style="font-size: 12.5px; font-weight: 600; color: #334155; margin-top: 2px;">Créé le : <strong><?= htmlspecialchars($createdAt) ?></strong></div>
                <div id="profil-summary-updated" style="font-size: 12px; color: #64748B; margin-top: 1px;">Modifié le : <?= htmlspecialchars($updatedAt) ?></div>
              </div>
            </div>

            <div class="profil-info-row">
              <div class="profil-icon-box"><i data-lucide="clock" style="width: 15px; height: 15px;"></i></div>
              <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Dernière Connexion</div>
                <div style="font-size: 13px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($lastLogin) ?></div>
              </div>
            </div>

            <!-- Synthèse CRUD -->
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #E2E8F0;">
              <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 8px;">Droits Opérationnels Cumulés</div>
              <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                <?php
                  $crudBadges = [
                    ['label' => 'Consulter', 'ok' => $hasShow || $isUserSuperAdmin],
                    ['label' => 'Créer',     'ok' => $hasCreate || $isUserSuperAdmin],
                    ['label' => 'Modifier',  'ok' => $hasEdit || $isUserSuperAdmin],
                    ['label' => 'Supprimer', 'ok' => $hasDelete || $isUserSuperAdmin],
                  ];
                  foreach ($crudBadges as $cb):
                ?>
                  <span style="font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 6px; background: <?= $cb['ok'] ? '#DCFCE7' : '#F1F5F9' ?>; color: <?= $cb['ok'] ? '#15803D' : '#94A3B8' ?>; border: 1px solid <?= $cb['ok'] ? '#BBF7D0' : '#E2E8F0' ?>;">
                    <?= $cb['ok'] ? '✓ ' : '✕ ' ?><?= $cb['label'] ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <?php if ($enseignantProfile): ?>
            <div class="card" style="background: #EFF6FF; border-radius: 12px; padding: 18px 20px; border: 1px solid #BFDBFE;">
              <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: #1E3A5F; margin-bottom: 8px;">
                <i data-lucide="graduation-cap" style="width: 17px; height: 17px;"></i> Compte lié au Corps Enseignant
              </div>
              <div style="font-size: 12.5px; color: #334155; line-height: 1.5;">
                <div><strong>Grade :</strong> <?= htmlspecialchars($enseignantProfile['grade_enseignant'] ?? '-') ?></div>
                <div><strong>Contrat :</strong> <?= htmlspecialchars(!empty($enseignantProfile['type_contrat']) ? ucfirst($enseignantProfile['type_contrat']) : '-') ?></div>
                <div><strong>Autorisation :</strong> <?= htmlspecialchars($enseignantProfile['numero_autorisation'] ?? '-') ?></div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- COLONNE DROITE : HUB À ONGLETS INTERACTIF -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

          <!-- BARRE D'ONGLETS -->
          <div style="display: flex; gap: 8px; flex-wrap: wrap; border-bottom: 1px solid #E2E8F0; padding-bottom: 14px; margin-bottom: 20px;">
            <button type="button" class="profil-tab-btn is-active" data-tab="infos">
              <i data-lucide="user-pen" style="width: 15px; height: 15px;"></i> Informations Personnelles
            </button>
            <button type="button" class="profil-tab-btn" data-tab="securite">
              <i data-lucide="lock" style="width: 15px; height: 15px;"></i> Sécurité &amp; Mot de passe
            </button>
            <button type="button" class="profil-tab-btn" data-tab="acces">
              <i data-lucide="shield" style="width: 15px; height: 15px;"></i> Rôles, Permissions &amp; Années
            </button>
            <button type="button" class="profil-tab-btn" data-tab="activite">
              <i data-lucide="history" style="width: 15px; height: 15px;"></i> Mon Activité
              <span style="background: rgba(148,163,184,0.22); font-size: 11px; padding: 1px 7px; border-radius: 999px;"><?= count($recentLogs) ?></span>
            </button>
          </div>

          <!-- =============================================================== -->
          <!-- ONGLET 1 : ÉDITION DES INFORMATIONS PERSONNELLES                -->
          <!-- =============================================================== -->
          <div class="profil-tab-panel" id="tab-panel-infos">
            <div style="margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Mettre à jour mes coordonnées</h2>
              <p style="font-size: 12.5px; color: #64748B; margin: 4px 0 0 0;">
                Modifiez votre nom, prénom, e-mail, téléphone ou genre. Les rôles, fonctions et statuts restent gérés par l'administration.
              </p>
            </div>

            <div id="profil-infos-feedback" style="display: none; padding: 11px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; margin-bottom: 16px;"></div>

            <form id="form-update-profil" action="<?= url('user/updateProfil') ?>" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

              <div class="profil-form-grid" style="margin-bottom: 18px;">
                <div>
                  <label for="profil_nom_user" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Nom <span style="color: #DC2626;">*</span></label>
                  <input type="text" id="profil_nom_user" name="nom_user" class="profil-input" value="<?= htmlspecialchars($nomUser) ?>" required autocomplete="family-name">
                </div>

                <div>
                  <label for="profil_prenom_user" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Prénom(s)</label>
                  <input type="text" id="profil_prenom_user" name="prenom_user" class="profil-input" value="<?= htmlspecialchars($prenomUser) ?>" autocomplete="given-name">
                </div>

                <div>
                  <label for="profil_email_user" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Adresse E-mail</label>
                  <input type="email" id="profil_email_user" name="email_user" class="profil-input" value="<?= htmlspecialchars($emailUser) ?>" placeholder="exemple@domaine.ci" autocomplete="email">
                </div>

                <div>
                  <label for="profil_telephone_user" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Téléphone</label>
                  <input type="tel" id="profil_telephone_user" name="telephone_user" class="profil-input" value="<?= htmlspecialchars($telUser) ?>" placeholder="Ex: 0700000000" autocomplete="tel">
                </div>

                <div>
                  <label for="profil_sexe_user" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Genre / Sexe</label>
                  <select id="profil_sexe_user" name="sexe_user" class="profil-input">
                    <option value="">-- Non précisé --</option>
                    <option value="M" <?= ($sexeValue === 'M') ? 'selected' : '' ?>>Masculin</option>
                    <option value="F" <?= ($sexeValue === 'F') ? 'selected' : '' ?>>Féminin</option>
                  </select>
                </div>

                <div>
                  <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Fonction (lecture seule)</label>
                  <input type="text" class="profil-input" value="<?= htmlspecialchars($libelleFonction !== '' ? $libelleFonction : 'Non assignée') ?>" readonly>
                </div>
              </div>

              <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #F1F5F9;">
                <button type="submit" id="btn-save-profil" style="background: #1E3A5F; color: #FFFFFF; border: none; border-radius: 9px; padding: 10px 22px; font-size: 13.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                  <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer mes informations
                </button>
              </div>
            </form>
          </div>

          <!-- =============================================================== -->
          <!-- ONGLET 2 : SÉCURITÉ & MODIFICATION DU MOT DE PASSE              -->
          <!-- =============================================================== -->
          <div class="profil-tab-panel" id="tab-panel-securite" style="display: none;">
            <div style="margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Changer mon mot de passe</h2>
              <p style="font-size: 12.5px; color: #64748B; margin: 4px 0 0 0;">
                Utilisez un mot de passe unique combinant lettres majuscules, minuscules, chiffres et caractères spéciaux.
              </p>
            </div>

            <div id="profil-pwd-feedback" style="display: none; padding: 11px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; margin-bottom: 16px;"></div>

            <form id="form-profil-password" action="<?= url('user/editPassword') ?>" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

              <div style="display: flex; flex-direction: column; gap: 15px; max-width: 520px;">
                <div>
                  <label for="profil_old_password" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Mot de passe actuel <span style="color: #DC2626;">*</span></label>
                  <div style="position: relative;">
                    <input type="password" id="profil_old_password" name="old_password" class="profil-input" style="padding-right: 42px;" required autocomplete="current-password" placeholder="Saisissez votre mot de passe actuel">
                    <button type="button" class="btn-toggle-profil-pwd" data-target="#profil_old_password" aria-label="Afficher le mot de passe" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #64748B; cursor: pointer; padding: 4px;">
                      <i data-lucide="eye" style="width: 17px; height: 17px;"></i>
                    </button>
                  </div>
                </div>

                <div>
                  <label for="profil_new_password" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Nouveau mot de passe <span style="color: #DC2626;">*</span></label>
                  <div style="position: relative;">
                    <input type="password" id="profil_new_password" name="new_password" class="profil-input" style="padding-right: 42px;" required minlength="6" autocomplete="new-password" placeholder="Minimum 6 caractères">
                    <button type="button" class="btn-toggle-profil-pwd" data-target="#profil_new_password" aria-label="Afficher le mot de passe" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #64748B; cursor: pointer; padding: 4px;">
                      <i data-lucide="eye" style="width: 17px; height: 17px;"></i>
                    </button>
                  </div>
                  <div class="pwd-strength-track">
                    <div id="profil-pwd-strength-bar" class="pwd-strength-bar"></div>
                  </div>
                  <div id="profil-pwd-strength-label" style="font-size: 11.5px; font-weight: 700; color: #64748B; margin-top: 4px;">Force du mot de passe : -</div>
                </div>

                <div>
                  <label for="profil_confirm_password" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">Confirmer le nouveau mot de passe <span style="color: #DC2626;">*</span></label>
                  <div style="position: relative;">
                    <input type="password" id="profil_confirm_password" name="confirm_password" class="profil-input" style="padding-right: 42px;" required minlength="6" autocomplete="new-password" placeholder="Ressaisissez le nouveau mot de passe">
                    <button type="button" class="btn-toggle-profil-pwd" data-target="#profil_confirm_password" aria-label="Afficher le mot de passe" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #64748B; cursor: pointer; padding: 4px;">
                      <i data-lucide="eye" style="width: 17px; height: 17px;"></i>
                    </button>
                  </div>
                  <div id="profil-pwd-match-hint" style="font-size: 11.5px; font-weight: 700; margin-top: 4px; display: none;"></div>
                </div>

                <div style="display: flex; justify-content: flex-start; padding-top: 8px;">
                  <button type="submit" id="btn-save-profil-pwd" style="background: #1E3A5F; color: #FFFFFF; border: none; border-radius: 9px; padding: 10px 22px; font-size: 13.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <i data-lucide="key-round" style="width: 16px; height: 16px;"></i> Mettre à jour le mot de passe
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- =============================================================== -->
          <!-- ONGLET 3 : RÔLES, PERMISSIONS RBAC & ANNÉES AUTORISÉES          -->
          <!-- =============================================================== -->
          <div class="profil-tab-panel" id="tab-panel-acces" style="display: none;">
            <!-- Rôles -->
            <div style="margin-bottom: 20px;">
              <h3 style="font-size: 14px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0 0 10px 0;">Mes Rôles &amp; Privilèges (<?= count($userRoles) ?>)</h3>
              <?php if (!empty($userRoles)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px;">
                  <?php foreach ($userRoles as $ur): ?>
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px;">
                      <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                        <strong style="font-size: 13.5px; color: #0F172A;"><?= htmlspecialchars($ur['libelle_role'] ?? ($ur['role_code'] ?? '-')) ?></strong>
                        <code style="font-size: 10.5px; background: #EFF6FF; color: #1E3A5F; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($ur['role_code'] ?? '') ?></code>
                      </div>
                      <?php if (!empty($ur['description'])): ?>
                        <div style="font-size: 12px; color: #64748B; margin-top: 4px;"><?= htmlspecialchars($ur['description']) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="padding: 14px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 8px; font-size: 13px; color: #64748B;">Aucun rôle explicitement rattaché.</div>
              <?php endif; ?>
            </div>

            <!-- Années autorisées -->
            <div style="margin-bottom: 20px;">
              <h3 style="font-size: 14px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0 0 10px 0;">Accès aux Années Académiques</h3>
              <?php if ($isUserSuperAdmin): ?>
                <div style="padding: 11px 14px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; font-size: 13px; font-weight: 700; color: #15803D;">
                  Accès complet (lecture et écriture) sur l'ensemble des années académiques.
                </div>
              <?php elseif (!empty($anneesAcces)): ?>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                  <?php foreach ($anneesAcces as $aa): ?>
                    <?php $isWrite = strtolower((string)($aa['niveau_acces'] ?? '')) === 'ecriture'; ?>
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 700; background: <?= $isWrite ? '#EFF6FF' : '#F8FAFC' ?>; color: <?= $isWrite ? '#1E3A5F' : '#475569' ?>; border: 1px solid <?= $isWrite ? '#BFDBFE' : '#E2E8F0' ?>;">
                      <?= htmlspecialchars($aa['libelle_annee'] ?? ($aa['annee_code'] ?? '-')) ?>
                      <span style="font-size: 10.5px; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; background: <?= $isWrite ? '#DBEAFE' : '#E2E8F0' ?>;"><?= $isWrite ? 'Écriture' : 'Lecture' ?></span>
                    </span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="padding: 12px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 12.5px; color: #64748B;">
                  Accès standard limité à l'année académique active courante.
                </div>
              <?php endif; ?>
            </div>

            <!-- Permissions effectives filtrables -->
            <div>
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                <h3 style="font-size: 14px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0;">
                  Permissions Effectives (<span id="profil-perm-visible-count"><?= count($permissionsDetailed) ?></span>)
                </h3>
                <?php if (!empty($permsByModule)): ?>
                  <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <select id="filter-profil-perm-module" class="profil-input" style="width: 170px; padding: 6px 10px; font-size: 12px;">
                      <option value="">Tous les modules</option>
                      <?php foreach (array_keys($permsByModule) as $modName): ?>
                        <option value="<?= htmlspecialchars($modName) ?>"><?= htmlspecialchars($modName) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <input type="search" id="search-profil-perms" class="profil-input" style="width: 190px; padding: 6px 10px; font-size: 12px;" placeholder="Rechercher une permission...">
                  </div>
                <?php endif; ?>
              </div>

              <?php if (!empty($permsByModule)): ?>
                <div id="profil-permissions-list" style="max-height: 360px; overflow-y: auto; padding-right: 4px;">
                  <?php foreach ($permsByModule as $modName => $permsList): ?>
                    <div class="profil-perm-group" data-module="<?= htmlspecialchars($modName) ?>" style="margin-bottom: 12px;">
                      <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px;">
                        <?= htmlspecialchars($modName) ?> (<?= count($permsList) ?>)
                      </div>
                      <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php foreach ($permsList as $perm): ?>
                          <?php $hay = mb_strtolower(($perm['code_permission'] ?? '') . ' ' . ($perm['libelle_permission'] ?? '') . ' ' . $modName, 'UTF-8'); ?>
                          <span class="profil-perm-item" data-search="<?= htmlspecialchars($hay) ?>" title="<?= htmlspecialchars($perm['code_permission'] ?? '') ?>" style="font-size: 11.5px; font-weight: 600; color: #334155; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 4px 9px; border-radius: 6px;">
                            <?= htmlspecialchars($perm['libelle_permission'] ?? ($perm['code_permission'] ?? '-')) ?>
                          </span>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="padding: 14px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 8px; font-size: 13px; color: #64748B;">
                  <?= $isUserSuperAdmin ? 'Privilège Super-Administrateur : toutes les permissions système sont actives.' : 'Aucune permission détaillée trouvée.' ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- =============================================================== -->
          <!-- ONGLET 4 : MON ACTIVITÉ RÉCENTE (LOGS_ACTIVITES)                -->
          <!-- =============================================================== -->
          <div class="profil-tab-panel" id="tab-panel-activite" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
              <div>
                <h2 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Journal de mes dernières actions</h2>
                <p style="font-size: 12.5px; color: #64748B; margin: 3px 0 0 0;">
                  Les <?= count($recentLogs) ?> actions les plus récentes sur <?= number_format($totalLogsCount, 0, ',', ' ') ?> enregistrées.
                </p>
              </div>
            </div>

            <?php if (!empty($recentLogs)): ?>
              <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #64748B; text-align: left; border-bottom: 1px solid #E2E8F0;">
                      <th style="padding: 10px 10px; font-size: 11px; text-transform: uppercase;">Date &amp; Heure</th>
                      <th style="padding: 10px 10px; font-size: 11px; text-transform: uppercase;">Action</th>
                      <th style="padding: 10px 10px; font-size: 11px; text-transform: uppercase;">Module</th>
                      <th style="padding: 10px 10px; font-size: 11px; text-transform: uppercase;">Description</th>
                      <th style="padding: 10px 10px; font-size: 11px; text-transform: uppercase;">IP</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recentLogs as $lg): ?>
                      <?php
                        $act = strtoupper((string)($lg['action'] ?? ''));
                        $actBg = '#F1F5F9'; $actCol = '#334155';
                        if (strpos($act, 'CONNEXION') !== false) { $actBg = '#DCFCE7'; $actCol = '#15803D'; }
                        elseif (strpos($act, 'SUPPR') !== false || strpos($act, 'ANNUL') !== false) { $actBg = '#FEE2E2'; $actCol = '#B91C1C'; }
                        elseif (strpos($act, 'MODIF') !== false || strpos($act, 'UPDATE') !== false) { $actBg = '#FEF3C7'; $actCol = '#B45309'; }
                        elseif (strpos($act, 'IMPR') !== false || strpos($act, 'PRINT') !== false) { $actBg = '#EFF6FF'; $actCol = '#1D4ED8'; }
                        elseif (strpos($act, 'CREAT') !== false || strpos($act, 'AJOUT') !== false) { $actBg = '#E0F2FE'; $actCol = '#0369A1'; }
                      ?>
                      <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 10px; white-space: nowrap; font-weight: 600; color: #334155;">
                          <?= !empty($lg['created_at']) ? date('d/m/Y H:i', strtotime($lg['created_at'])) : '-' ?>
                        </td>
                        <td style="padding: 10px; white-space: nowrap;">
                          <span style="background: <?= $actBg ?>; color: <?= $actCol ?>; font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 999px;">
                            <?= htmlspecialchars($act !== '' ? $act : '-') ?>
                          </span>
                        </td>
                        <td style="padding: 10px; font-weight: 700; color: #1E3A5F; white-space: nowrap;">
                          <?= htmlspecialchars($lg['module'] ?? '-') ?>
                        </td>
                        <td style="padding: 10px; color: #334155; line-height: 1.4;">
                          <?= htmlspecialchars($lg['description'] ?? '-') ?>
                        </td>
                        <td style="padding: 10px; font-family: monospace; font-size: 11.5px; color: #64748B; white-space: nowrap;">
                          <?= htmlspecialchars($lg['ip_address'] ?? '-') ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div style="padding: 20px; text-align: center; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 10px; color: #64748B; font-size: 13px;">
                Aucune action journalisée pour ce compte pour le moment.
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

    </div>
  </main>
</div>

<script type="application/json" id="users-profil-config">
<?= json_encode([
    'racine' => RACINE,
    'csrfToken' => $csrfToken,
    'updateProfilUrl' => url('user/updateProfil'),
    'uploadPhotoUrl' => url('user/uploadPhotoProfil'),
    'editPasswordUrl' => url('user/editPassword'),
    'placeholderUrl' => $placeholderPhotoUrl
]) ?>
</script>

<?php
ViewContext::setScripts(['public/assets/js/modules/users.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
