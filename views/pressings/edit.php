<?php
require_once __DIR__ . '/../../public/inc/header.php';
$pressing = isset($pressing) ? $pressing : [];
$isEdit = !empty($pressing['id_pressing']);
$villes = isset($villes) ? $villes : [];
$quartiers = isset($quartiers) ? $quartiers : [];
$forfaits = isset($forfaits) ? $forfaits : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main, #1e293b); margin: 0; display: flex; align-items: center; gap: 8px;">
            <?php if ($isEdit): ?>
              <i data-lucide="edit-3" style="color: #2563eb;"></i> Modifier le pressing
            <?php else: ?>
              <i data-lucide="rocket" style="color: #2563eb;"></i> Nouveau Pressing - Onboarding Tout-en-Un
            <?php endif; ?>
          </h1>
          <p class="page-subtitle" style="color: #64748b; margin-top: 4px;">
            <?= $isEdit ? 'Mise à jour des coordonnées' : 'Création simultanée de l\'établissement, du compte gérant et du forfait B2B' ?>
          </p>
        </div>
        <a href="<?= RACINE ?>pressing/list" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; padding: 8px 16px;">
          <i data-lucide="arrow-left"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;">
        <?php if (!$isEdit): ?>
        <!-- WIZARD STEP HEADER -->
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 20px 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; max-width: 700px; margin: 0 auto; position: relative;">
            <div style="position: absolute; top: 18px; left: 10%; right: 10%; height: 3px; background: #e2e8f0; z-index: 1;"></div>
            
            <div class="wizard-step-item active" id="step-btn-1" style="z-index: 2; text-align: center; cursor: pointer;" onclick="goToStep(1)">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 6px; box-shadow: 0 0 0 4px #dbeafe;">1</div>
              <span style="font-size: 13px; font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="building-2" style="width: 14px; height: 14px;"></i> 1. Pressing
              </span>
            </div>

            <div class="wizard-step-item" id="step-btn-2" style="z-index: 2; text-align: center; cursor: pointer;" onclick="goToStep(2)">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 6px;">2</div>
              <span style="font-size: 13px; font-weight: 600; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="user-check" style="width: 14px; height: 14px;"></i> 2. Gérant (1er User)
              </span>
            </div>

            <div class="wizard-step-item" id="step-btn-3" style="z-index: 2; text-align: center; cursor: pointer;" onclick="goToStep(3)">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 6px;">3</div>
              <span style="font-size: 13px; font-weight: 600; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> 3. Forfait B2B
              </span>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="card-body" style="padding: 32px;">
          <form class="formEditPressing" id="onboardingForm" novalidate>
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_pressing" name="id_pressing" value="<?= htmlspecialchars($pressing['id_pressing'] ?? '') ?>">

            <!-- SECTION 1 : PRESSING -->
            <div class="wizard-section" id="wizard-step-1">
              <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="building-2" style="color: #2563eb;"></i> Informations de l'Établissement
              </h3>

              <div class="form-grid">
                <div class="form-field">
                  <label for="libelle_pressing">Nom du Pressing <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('store'); ?></span>
                    <input type="text" class="form-control" id="libelle_pressing" name="libelle_pressing"
                           placeholder="ex: Pressing Riviera Palmeraie" value="<?= htmlspecialchars($pressing['libelle_pressing'] ?? '') ?>" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="telephone_pressing">Téléphone Atelier / Service Client</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('phone'); ?></span>
                    <input type="text" class="form-control" id="telephone_pressing" name="telephone_pressing"
                           placeholder="ex: 0707070707" value="<?= htmlspecialchars($pressing['telephone_pressing'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-field">
                  <label for="email_pressing">Email Officiel du Pressing</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                    <input type="email" class="form-control" id="email_pressing" name="email_pressing"
                           placeholder="contact@pressing.ci" value="<?= htmlspecialchars($pressing['email_pressing'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-field">
                  <label for="ville_code">Ville</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                    <select class="form-control" id="ville_code" name="ville_code">
                      <option value="">Sélectionner une ville</option>
                      <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['code_ville'] ?>" <?= ($pressing['ville_code'] ?? '') == $v['code_ville'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($v['libelle_ville']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-field">
                  <label for="quartier_code">Quartier</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('map'); ?></span>
                    <select class="form-control" id="quartier_code" name="quartier_code">
                      <option value="">Sélectionner un quartier</option>
                      <?php foreach ($quartiers as $q): ?>
                        <option value="<?= $q['code_quartier'] ?>" data-ville="<?= $q['ville_code'] ?>" <?= ($pressing['quartier_code'] ?? '') == $q['code_quartier'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($q['libelle_quartier']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-field" style="grid-column: 1 / -1;">
                  <label for="adresse_pressing">Adresse / Localisation précise</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('navigation'); ?></span>
                    <textarea class="form-control" id="adresse_pressing" name="adresse_pressing" rows="2" placeholder="Rue, Carrefour, Repère..."><?= htmlspecialchars($pressing['adresse_pressing'] ?? '') ?></textarea>
                  </div>
                </div>

                <div style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                  <!-- CHAMP FICHIER : LOGO DU PRESSING -->
                  <div class="form-field">
                    <label for="logo_file" style="font-weight: 700; font-size: 13px; color: #1E293B; margin-bottom: 6px; display: block;">
                      Logo Officiel du Pressing (Icône Rond/Carré)
                    </label>
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                      <div id="logoPreviewWrapper" style="width: 70px; height: 70px; border-radius: 12px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative;">
                        <?php 
                          $currentLogo = $pressing['logo_pressing'] ?? '';
                          $hasLogo = !empty($currentLogo);
                          $logoUrl = $hasLogo ? ((strpos($currentLogo, 'http') === 0) ? $currentLogo : RACINE . 'public/assets/images/pressings/' . $currentLogo) : '';
                        ?>
                        <?php if ($hasLogo): ?>
                          <img id="logoPreviewImg" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo actuel" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                          <img id="logoPreviewImg" src="" alt="Aperçu Logo" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                          <i id="logoPlaceholderIcon" class="fa fa-building" style="font-size: 24px; color: #94A3B8;"></i>
                        <?php endif; ?>
                      </div>
                      <div style="flex: 1;">
                        <div class="input-with-icon">
                          <span class="input-icon"><?= Validator::icon('image'); ?></span>
                          <input type="file" class="form-control" id="logo_file" name="logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" onchange="previewPressingLogo(this)">
                        </div>
                        <small style="color: #64748B; font-size: 11px; display: block; margin-top: 4px;">Logo/Insigne officiel (PNG, JPG, WEBP, SVG)</small>
                      </div>
                    </div>
                  </div>

                  <!-- CHAMP FICHIER : MINIATURE / COUVERTURE DU PRESSING -->
                  <div class="form-field">
                    <label for="miniature_file" style="font-weight: 700; font-size: 13px; color: #1E293B; margin-bottom: 6px; display: block;">
                      Miniature / Photo de Couverture (Affiche Lavex) <i data-lucide="star" style="width: 14px; height: 14px; color: #2563EB; vertical-align: -1px; display: inline;"></i>
                    </label>
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                      <div id="miniaturePreviewWrapper" style="width: 110px; height: 70px; border-radius: 12px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative;">
                        <?php 
                          $currentMiniature = $pressing['miniature_pressing'] ?? '';
                          $hasMiniature = !empty($currentMiniature);
                          $miniatureUrl = $hasMiniature ? ((strpos($currentMiniature, 'http') === 0) ? $currentMiniature : RACINE . 'public/assets/images/pressings/' . $currentMiniature) : '';
                        ?>
                        <?php if ($hasMiniature): ?>
                          <img id="miniaturePreviewImg" src="<?= htmlspecialchars($miniatureUrl) ?>" alt="Miniature actuelle" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                          <img id="miniaturePreviewImg" src="" alt="Aperçu Miniature" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                          <i id="miniaturePlaceholderIcon" class="fa fa-camera" style="font-size: 24px; color: #94A3B8;"></i>
                        <?php endif; ?>
                      </div>
                      <div style="flex: 1;">
                        <div class="input-with-icon">
                          <span class="input-icon"><?= Validator::icon('image'); ?></span>
                          <input type="file" class="form-control" id="miniature_file" name="miniature_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" onchange="previewPressingMiniature(this)">
                        </div>
                        <small style="color: #2563EB; font-size: 11px; display: block; margin-top: 4px; font-weight: 600;">Image principale affichée sur la plateforme Lavex</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- SECTION LIVRAISON & COLLECTE AU SAC -->
                <div class="form-field" style="grid-column: 1 / -1; margin-top: 10px; padding-top: 16px; border-top: 1px dashed #E2E8F0;">
                  <h4 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 14px 0; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="truck" style="width: 16px; height: 16px; color: #2563EB;"></i> Configuration Livraison & Collecte au Sac
                  </h4>
                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                    
                    <div class="form-field" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0;">
                        <span>Proposer la Livraison Gratuite</span>
                        <input type="checkbox" id="livraison_gratuite" name="livraison_gratuite" value="1" <?= (!empty($pressing['livraison_gratuite'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #2563EB;">
                      </label>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Offre la livraison au client si le seuil ci-dessous est atteint.
                      </small>
                    </div>

                    <div class="form-field" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label for="seuil_livraison_gratuite" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Seuil Minimum Livraison Gratuite (FCFA)
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                        <input type="number" class="form-control" id="seuil_livraison_gratuite" name="seuil_livraison_gratuite"
                               placeholder="ex: 15000" min="0" step="500" value="<?= htmlspecialchars($pressing['seuil_livraison_gratuite'] ?? '0') ?>">
                      </div>
                    </div>

                    <div class="form-field" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0;">
                        <span>Accepter la Collecte au Sac (Sans Détail)</span>
                        <input type="checkbox" id="accepte_colis_sans_detail" name="accepte_colis_sans_detail" value="1" <?= (!isset($pressing['accepte_colis_sans_detail']) || !empty($pressing['accepte_colis_sans_detail'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #2563EB;">
                      </label>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Permet de confier des sacs sans détail d'articles à la réservation.
                      </small>
                    </div>

                  </div>
                </div>
              </div>

              <?php if (!$isEdit): ?>
              <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn btn-primary" onclick="goToStep(2)" style="border-radius: 10px; padding: 10px 24px; font-weight: 600;">
                  Étape Suivante (Compte Gérant) <i data-lucide="arrow-right"></i>
                </button>
              </div>
              <?php endif; ?>
            </div>

            <?php if (!$isEdit): ?>
            <!-- SECTION 2 : GERANT (1ER USER) -->
            <div class="wizard-section" id="wizard-step-2" style="display: none;">
              <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="user-check" style="color: #2563eb;"></i> Compte Gérant / Propriétaire (1er Administrateur du Pressing)
              </h3>
              
              <div class="alert alert-info" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 13px;">
                <i data-lucide="info" style="vertical-align: -2px; margin-right: 6px;"></i>
                Ces accès permettront au propriétaire du pressing de se connecter immédiatement à son tableau de bord d'administration <strong>admin-lavex</strong>.
              </div>

              <div class="form-grid">
                <div class="form-field">
                  <label for="nom_user">Nom complet du Gérant <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('user'); ?></span>
                    <input type="text" class="form-control" id="nom_user" name="nom_user" placeholder="ex: Kouassi Jean" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="prenom_user">Prénom(s)</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('user'); ?></span>
                    <input type="text" class="form-control" id="prenom_user" name="prenom_user" placeholder="ex: Marc">
                  </div>
                </div>

                <div class="form-field">
                  <label for="email_user">Email de Connexion (Login Admin) <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                    <input type="email" class="form-control" id="email_user" name="email_user" placeholder="gerant@pressing.ci" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="password_user">Mot de passe de connexion <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('lock'); ?></span>
                    <input type="password" class="form-control" id="password_user" name="password_user" placeholder="••••••••" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="telephone_user">Téléphone Personnel / Mobile Money</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('smartphone'); ?></span>
                    <input type="text" class="form-control" id="telephone_user" name="telephone_user" placeholder="0505050505">
                  </div>
                </div>
              </div>

              <div style="display: flex; justify-content: space-between; margin-top: 24px;">
                <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)" style="border-radius: 10px; padding: 10px 24px;">
                  <i data-lucide="arrow-left"></i> Précédent
                </button>
                <button type="button" class="btn btn-primary" onclick="goToStep(3)" style="border-radius: 10px; padding: 10px 24px; font-weight: 600;">
                  Étape Suivante (Forfait B2B) <i data-lucide="arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- SECTION 3 : FORFAIT B2B -->
            <div class="wizard-section" id="wizard-step-3" style="display: none;">
              <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="credit-card" style="color: #2563eb;"></i> Choix du Forfait B2B & Abonnement Initial
              </h3>

              <div class="forfaits-grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <?php foreach ($forfaits as $idx => $f): ?>
                  <?php 
                    $isSelected = ($idx === 0); 
                    $isGratuit = ((float)$f['montant_forfait'] == 0);
                    $badgeText = $isGratuit ? 'Essai Gratuit' : ((float)$f['montant_forfait'] >= 30000 ? 'Recommandé' : 'Populaire');
                    $badgeBg = $isGratuit ? '#10b981' : ((float)$f['montant_forfait'] >= 30000 ? '#f59e0b' : '#2563eb');
                  ?>
                  <div class="forfait-card-box <?= $isSelected ? 'selected' : '' ?>" 
                       id="forfait-card-<?= $f['code_forfait'] ?>"
                       onclick="selectForfaitCard('<?= $f['code_forfait'] ?>', <?= (float)$f['montant_forfait'] ?>, <?= (int)$f['duree_mois_forfait'] ?>)"
                       style="position: relative; border: 2px solid <?= $isSelected ? '#2563eb' : '#e2e8f0' ?>; background: <?= $isSelected ? '#f0f6ff' : '#ffffff' ?>; border-radius: 16px; padding: 20px; cursor: pointer; transition: all 0.25s ease; box-shadow: <?= $isSelected ? '0 8px 24px rgba(37,99,235,0.12)' : '0 2px 8px rgba(0,0,0,0.02)' ?>;">
                    
                    <!-- Badge & Check Icon -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                      <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 20px; background: <?= $badgeBg ?>; color: #ffffff;">
                        <?= $badgeText ?>
                      </span>
                      <div class="card-radio-icon" style="width: 22px; height: 22px; border-radius: 50%; border: 2px solid <?= $isSelected ? '#2563eb' : '#cbd5e1' ?>; background: <?= $isSelected ? '#2563eb' : '#fff' ?>; display: flex; align-items: center; justify-content: center; color: #fff; transition: all 0.2s;">
                        <?php if ($isSelected): ?><i data-lucide="check" style="width: 14px; height: 14px; stroke-width: 3;"></i><?php endif; ?>
                      </div>
                    </div>

                    <input type="radio" name="forfait_code" value="<?= $f['code_forfait'] ?>" <?= $isSelected ? 'checked' : '' ?> style="display: none;" id="radio-forfait-<?= $f['code_forfait'] ?>">

                    <div style="font-weight: 800; font-size: 17px; color: #1e293b; margin-bottom: 4px;"><?= htmlspecialchars($f['libelle_forfait']) ?></div>
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 38px;">
                      <?= htmlspecialchars($f['description_forfait'] ?: 'Accès aux fonctionnalités d\'administration B2B.') ?>
                    </div>

                    <div style="display: flex; align-items: baseline; gap: 6px; border-top: 1px dashed #cbd5e1; padding-top: 12px; margin-top: 8px;">
                      <span style="font-size: 22px; font-weight: 900; color: #2563eb;"><?= number_format($f['montant_forfait'], 0, ',', ' ') ?></span>
                      <span style="font-size: 12px; font-weight: 700; color: #64748b;">FCFA / <?= (int)$f['duree_mois_forfait'] ?> mois</span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="form-grid">
                <div class="form-field">
                  <label for="duree_mois">Durée de la période (en mois)</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('calendar'); ?></span>
                    <input type="number" class="form-control" id="duree_mois" name="duree_mois" value="1" min="1" max="36">
                  </div>
                </div>

                <div class="form-field">
                  <label for="montant_abonnement">Montant Facturé (FCFA)</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                    <input type="number" class="form-control" id="montant_abonnement" name="montant_abonnement" placeholder="Montant">
                  </div>
                </div>

                <div class="form-field">
                  <label for="date_debut_abonnement">Date de Début d'Abonnement</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                    <input type="date" class="form-control" id="date_debut_abonnement" name="date_debut_abonnement" value="<?= date('Y-m-d') ?>">
                  </div>
                </div>
              </div>

              <div style="display: flex; justify-content: space-between; margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)" style="border-radius: 10px; padding: 10px 24px;">
                  <i data-lucide="arrow-left"></i> Précédent
                </button>

                <button type="submit" class="btn btn-success btn_actions btnEditPressing" style="border-radius: 10px; padding: 12px 32px; font-weight: 700; background: #10b981; border-color: #10b981;">
                  <span class="btn-text">
                    <i data-lucide="check-circle"></i> Valider & Activer le Pressing (Tout-en-Un)
                  </span>
                </button>
              </div>
            </div>
            <?php else: ?>
            <!-- BOUTON POUR MODIFICATION PRESSING SEUL -->
            <div class="form-actions" style="margin-top: 24px;">
              <button type="submit" class="btn btn-primary btn_actions btnEditPressing" style="border-radius: 10px; padding: 10px 24px;">
                <span class="btn-text">
                  <i data-lucide="save"></i> Sauvegarder les modifications
                </span>
              </button>
              <a href="<?= RACINE ?>pressing/list" class="btn btn-secondary" style="border-radius: 10px; padding: 10px 24px;">
                Annuler
              </a>
            </div>
            <?php endif; ?>

          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
function goToStep(step) {
  $('.wizard-section').hide();
  $('#wizard-step-' + step).show();
  if (window.lucide) window.lucide.createIcons();

  $('.wizard-step-item').each(function(idx) {
    const sNum = idx + 1;
    const circle = $(this).find('div');
    const label = $(this).find('span');

    if (sNum === step) {
      circle.css({'background': '#2563eb', 'color': '#fff', 'box-shadow': '0 0 0 4px #dbeafe'});
      label.css({'color': '#1e293b', 'font-weight': '700'});
    } else if (sNum < step) {
      circle.css({'background': '#10b981', 'color': '#fff', 'box-shadow': 'none'});
      label.css({'color': '#10b981', 'font-weight': '600'});
    } else {
      circle.css({'background': '#e2e8f0', 'color': '#64748b', 'box-shadow': 'none'});
      label.css({'color': '#64748b', 'font-weight': '600'});
    }
  });
}

function selectForfaitCard(code, montant, duree) {
  $('.forfait-card-box').css({
    'border-color': '#e2e8f0',
    'background': '#ffffff',
    'box-shadow': '0 2px 8px rgba(0,0,0,0.02)',
    'transform': 'scale(1)'
  }).removeClass('selected');

  $('.card-radio-icon').css({
    'border-color': '#cbd5e1',
    'background': '#ffffff'
  }).html('');

  const selectedCard = $('#forfait-card-' + code);
  selectedCard.css({
    'border-color': '#2563eb',
    'background': '#f0f6ff',
    'box-shadow': '0 8px 24px rgba(37,99,235,0.15)',
    'transform': 'translateY(-2px)'
  }).addClass('selected');

  selectedCard.find('.card-radio-icon').css({
    'border-color': '#2563eb',
    'background': '#2563eb'
  }).html('<i data-lucide="check" style="width: 14px; height: 14px; stroke-width: 3; color: #fff;"></i>');

  $('#radio-forfait-' + code).prop('checked', true);

  if (montant !== undefined) $('#montant_abonnement').val(montant);
  if (duree !== undefined) $('#duree_mois').val(duree);

  if (window.lucide) window.lucide.createIcons();
}

function filterQuartiersByVille() {
  const selectedVille = $('#ville_code').val();
  $('#quartier_code option').each(function() {
    const qVille = $(this).attr('data-ville');
    if (!qVille || !selectedVille || qVille === selectedVille) {
      $(this).show().prop('disabled', false);
    } else {
      $(this).hide().prop('disabled', true);
    }
  });

  const currentQuartier = $('#quartier_code option:selected');
  if (currentQuartier.length && currentQuartier.attr('data-ville') && currentQuartier.attr('data-ville') !== selectedVille) {
    $('#quartier_code').val('');
  }
}

$('#ville_code').on('change', filterQuartiersByVille);

function previewPressingLogo(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#logoPreviewImg').attr('src', e.target.result).show();
      $('#logoPlaceholderIcon').hide();
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewPressingMiniature(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#miniaturePreviewImg').attr('src', e.target.result).show();
      $('#miniaturePlaceholderIcon').hide();
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function toggleSeuilLivraison() {
  const isChecked = $('#livraison_gratuite').is(':checked');
  const input = $('#seuil_livraison_gratuite');
  if (isChecked) {
    input.prop('disabled', false).css({'background': '#FFFFFF', 'opacity': '1', 'cursor': 'text'});
  } else {
    input.prop('disabled', true).css({'background': '#F1F5F9', 'opacity': '0.6', 'cursor': 'not-allowed'});
  }
}

$('#livraison_gratuite').on('change', toggleSeuilLivraison);

$(document).ready(function() {
  const firstForfait = $('input[name="forfait_code"]:checked').val();
  if (firstForfait) {
    $('#forfait-card-' + firstForfait).css({'border-color': '#2563eb', 'background': '#eff6ff'});
  }
  filterQuartiersByVille();
  toggleSeuilLivraison();
});
</script>

<script src="<?= RACINE ?>json/entities/pressings.js?v=5"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
