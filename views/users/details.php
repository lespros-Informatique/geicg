<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$role = isset($role) ? $role : [];
$userRoles = isset($userRoles) ? $userRoles : (!empty($role) ? [$role] : []);

// Détection d'un profil enseignant lié au compte utilisateur
$enseignantEncId = null;
if (!empty($user['code_user'])) {
    try {
        $db = (new ModelUser())->getCon();
        $stmtEns = $db->prepare("SELECT id_enseignant FROM " . TABLES::ENSEIGNANTS . " WHERE code_enseignant = ? LIMIT 1");
        $stmtEns->execute([$user['code_user']]);
        $ensRow = $stmtEns->fetch(PDO::FETCH_ASSOC);
        if (!empty($ensRow['id_enseignant'])) {
            $validator = new Validator();
            $enseignantEncId = $validator->crypter($ensRow['id_enseignant']);
        }
    } catch (Exception $e) {
        // En cas d'erreur silencieuse sur la détection
    }
}

// Configuration institutionnelle pour impression
$etablissementConfig = [];
if (class_exists('ModelConfiguration')) {
    $etablissementConfig = (new ModelConfiguration())->getConfig();
}

// Photo et placeholder
$photoUser = !empty($user['photo_user']) ? $user['photo_user'] : '';
$photoUrl = placeholderUrl('user');
if (!empty($photoUser)) {
    $cleanRel = ltrim($photoUser, '/');
    if (file_exists(__DIR__ . '/../../public/' . $cleanRel)) {
        $photoUrl = url('public/' . $cleanRel);
    } elseif (file_exists(__DIR__ . '/../../' . $cleanRel)) {
        $photoUrl = url($cleanRel);
    }
}

// Sexe libellé
$sexeLibelle = 'Non précisé';
if (!empty($user['sexe_user'])) {
    $s = strtoupper(trim($user['sexe_user']));
    $sexeLibelle = ($s === 'M' || $s === 'HOMME') ? 'Masculin' : (($s === 'F' || $s === 'FEMME') ? 'Féminin' : $s);
}
?>
<style>
  .user-details-grid {
    display: grid;
    grid-template-columns: minmax(300px, 1fr) minmax(400px, 2fr);
    gap: 24px;
    width: 100%;
    box-sizing: border-box;
  }
  @media (max-width: 960px) {
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
    .print-only-banner {
      display: block !important;
    }
    .page-header,
    .main-nav,
    .app-sidebar,
    .btn,
    nav,
    aside,
    footer,
    .sidebar-wrapper,
    header {
      display: none !important;
    }
    body, html, .app-layout, .main-content, .content-wrapper {
      background: #FFFFFF !important;
      padding: 0 !important;
      margin: 0 !important;
      width: 100% !important;
    }
    .card {
      box-shadow: none !important;
      border: 1px solid #CBD5E1 !important;
      page-break-inside: avoid;
    }
    .user-details-grid {
      display: grid !important;
      grid-template-columns: 280px 1fr !important;
      gap: 20px !important;
    }
  }
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">

      <!-- EN-TÊTE D'IMPRESSION (VISIBLE UNIQUEMENT À L'IMPRESSION) -->
      <div class="print-only-banner" style="display: none; margin-bottom: 20px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">
              <?= htmlspecialchars($etablissementConfig['libelle_etablissement'] ?? 'GEICG - GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION') ?>
            </h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">
              FICHE OFFICIELLE D'IDENTIFICATION & DE GESTION DU COMPTE UTILISATEUR
            </div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(Auth::fullName() ?: 'Administration') ?></strong></div>
          </div>
        </div>
      </div>
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user-check" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
            <?= htmlspecialchars(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? '')) ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation des informations de l'utilisateur</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
          

          <a href="<?= url('user/list') ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>

          <a href="<?= url('user/imprimerFichePdf/' . ($encryptedId)) ?>" target="_blank" class="btn btn-outline-primary" style="background: #FFFFFF; border: 1.5px solid #1E3A5F; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; cursor: pointer; transition: all 0.2s;" title="Générer et imprimer la fiche officielle de l'utilisateur en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer Fiche Utilisateur
          </a>

          <a href="<?= url('user/edition/' . ($encryptedId)) ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier l'utilisateur
          </a>
        </div>
      </div>

      <div class="user-details-grid">
        
        <!-- Colonne Gauche : Identité & Paramètres du Compte -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: fit-content;">
          
          <!-- Avatar & Nom -->
          <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #F1F5F9;">
            <div style="width: 115px; height: 115px; border-radius: 50%; border: 3px solid #E2E8F0; overflow: hidden; background: #F8FAFC; margin: 0 auto 16px auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
              <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Photo Utilisateur" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= placeholderUrl('user') ?>';">
            </div>
            
            <h2 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.3;">
              <?= htmlspecialchars(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? '')) ?>
            </h2>

            <div style="margin-top: 6px;">
              <span class="badge" style="background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; font-weight: 700; font-size: 12px; padding: 4px 12px; border-radius: 20px;">
                <i data-lucide="shield" style="width: 13px; height: 13px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                <?= htmlspecialchars($role['libelle_role'] ?? 'Rôle Standard') ?>
              </span>
            </div>

            <div style="margin-top: 10px;">
              <?php if (strtolower(trim($user['statut_user'] ?? '')) === STATUTS::ACTIF): ?>
                <span class="badge" style="background: #DCFCE7; color: #15803D; padding: 5px 14px; border-radius: 12px; font-weight: 700; font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px;">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: #15803D;"></span> Compte Actif
                </span>
              <?php else: ?>
                <span class="badge" style="background: #FEE2E2; color: #B91C1C; padding: 5px 14px; border-radius: 12px; font-weight: 700; font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px;">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: #B91C1C;"></span> Compte Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Métadonnées & Coordonnées -->
          <div style="padding-top: 16px;">
            
            <!-- Code Utilisateur -->
            <div class="info-detail-row">
              <div class="info-icon-box">
                <i data-lucide="hash" style="width: 16px; height: 16px;"></i>
              </div>
              <div style="flex: 1;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Code Compte</span>
                <div style="font-size: 13.5px; font-weight: 700; color: #1E3A5F; font-family: monospace;"><?= htmlspecialchars($user['code_user'] ?? '-') ?></div>
              </div>
            </div>

            <!-- Genre / Sexe -->
            <div class="info-detail-row">
              <div class="info-icon-box">
                <i data-lucide="user" style="width: 16px; height: 16px;"></i>
              </div>
              <div style="flex: 1;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Genre / Sexe</span>
                <div style="font-size: 13px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($sexeLibelle) ?></div>
              </div>
            </div>

            <!-- Email -->
            <div class="info-detail-row">
              <div class="info-icon-box">
                <i data-lucide="mail" style="width: 16px; height: 16px;"></i>
              </div>
              <div style="flex: 1; min-width: 0;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Email de connexion</span>
                <div style="font-size: 13px; font-weight: 600; color: #0F172A; word-break: break-all;"><?= htmlspecialchars($user['email_user'] ?? 'Non renseigné') ?></div>
              </div>
            </div>

            <!-- Téléphone -->
            <div class="info-detail-row">
              <div class="info-icon-box">
                <i data-lucide="phone" style="width: 16px; height: 16px;"></i>
              </div>
              <div style="flex: 1;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Téléphone</span>
                <div style="font-size: 13px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($user['telephone_user'] ?? 'Non renseigné') ?></div>
              </div>
            </div>

            <!-- Date de création -->
            <div class="info-detail-row">
              <div class="info-icon-box">
                <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
              </div>
              <div style="flex: 1;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Date de Création</span>
                <div style="font-size: 12.5px; color: #334155; font-weight: 600;"><?= !empty($user['created_at_user']) ? date('d/m/Y H:i', strtotime($user['created_at_user'])) : '-' ?></div>
              </div>
            </div>

            <!-- Dernière Connexion -->
            <div class="info-detail-row">
              <div class="info-icon-box">
                <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
              </div>
              <div style="flex: 1;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Dernière Connexion</span>
                <div style="font-size: 12.5px; color: #334155; font-weight: 600;">
                  <?= !empty($user['last_connexion']) ? date('d/m/Y H:i', strtotime($user['last_connexion'])) : 'Jamais connecté' ?>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- Colonne Droite : Rôles & Privilèges RBAC -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
          
          <!-- Carte 1 : Rôles Système Détenus -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <i data-lucide="shield-check" style="width: 20px; height: 20px; color: #1E3A5F;"></i> Rôles Attribués & Privilèges RBAC
            </h3>

            <div>
              <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 12px;">Rôles Système Détenus</div>

              <?php if (!empty($userRoles)): ?>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                  <?php foreach($userRoles as $ur): ?>
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid var(--primary-color, #18385F); border-radius: 8px; padding: 16px 18px;">
                      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
                          <i data-lucide="award" style="width: 18px; height: 18px; color: var(--primary-color);"></i>
                          <?= htmlspecialchars($ur['libelle_role'] ?? 'Rôle non défini') ?>
                        </div>
                        <span style="background: #E2E8F0; color: #334155; font-weight: 700; font-size: 11px; padding: 3px 10px; border-radius: 6px;">
                          <?= htmlspecialchars($ur['groupe'] ?? ($ur['module'] ?? 'Standard')) ?>
                        </span>
                      </div>

                      <?php if (!empty($ur['description'])): ?>
                        <p style="color: #64748B; font-size: 12.5px; margin: 8px 0 0 26px; line-height: 1.4;"><?= htmlspecialchars($ur['description']) ?></p>
                      <?php endif; ?>

                      <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #E2E8F0; display: flex; flex-wrap: wrap; gap: 16px;">
                        <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['create_permission']) ? '#15803D' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= !empty($ur['create_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Créer
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['edit_permission']) ? '#0284C7' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= !empty($ur['edit_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Modifier
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? '#475569' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Consulter
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['delete_permission']) ? '#DC2626' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= !empty($ur['delete_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Supprimer
                        </span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="color: #94A3B8; font-style: italic; padding: 12px 0;">Aucun rôle attribué pour le moment.</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Carte 2 : Matrice Synthétique des Actions Autorisées -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h4 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="sliders" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Matrice des Actions Autorisées :
            </h4>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
              
              <!-- Création -->
              <div style="display: flex; align-items: center; gap: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px 14px; border-radius: 8px;">
                <i data-lucide="<?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 22px; height: 22px; color: <?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? '#15803D' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 700; font-size: 13px; color: #0F172A;">Création (Ajout)</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 600;"><?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

              <!-- Modification -->
              <div style="display: flex; align-items: center; gap: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px 14px; border-radius: 8px;">
                <i data-lucide="<?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 22px; height: 22px; color: <?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? '#0284C7' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 700; font-size: 13px; color: #0F172A;">Modification (Édition)</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 600;"><?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

              <!-- Consultation -->
              <div style="display: flex; align-items: center; gap: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px 14px; border-radius: 8px;">
                <i data-lucide="<?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 22px; height: 22px; color: <?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? '#15803D' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 700; font-size: 13px; color: #0F172A;">Consultation (Lecture)</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 600;"><?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

              <!-- Suppression -->
              <div style="display: flex; align-items: center; gap: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px 14px; border-radius: 8px;">
                <i data-lucide="<?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 22px; height: 22px; color: <?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? '#15803D' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 700; font-size: 13px; color: #0F172A;">Suppression</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 600;"><?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

            </div>
          </div>

        </div>

      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
