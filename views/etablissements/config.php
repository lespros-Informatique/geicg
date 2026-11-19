<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <?php if (Session::has('flash_success')): ?>
        <div class="alert alert-success" style="background: #DCFCE7; color: #15803D; padding: 14px 18px; border-radius: 8px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
          <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
          <span><?= htmlspecialchars(Session::get('flash_success')) ?></span>
        </div>
        <?php Session::remove('flash_success'); ?>
      <?php endif; ?>

      <?php if (Session::has('flash_error')): ?>
        <div class="alert alert-danger" style="background: #FEE2E2; color: #B91C1C; padding: 14px 18px; border-radius: 8px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
          <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
          <span><?= htmlspecialchars(Session::get('flash_error')) ?></span>
        </div>
        <?php Session::remove('flash_error'); ?>
      <?php endif; ?>

      <!-- EN-TÊTE DE LA PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="building" style="color: #1E3A5F; width: 24px; height: 24px;"></i> Configuration Générale & Documents Officiels
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Identité institutionnelle, coordonnées et mentions officielles appliquées sur tous les documents PDF (Enregistrement modulaire AJAX par section)
          </p>
        </div>
        <span class="badge" style="background: #DCFCE7; color: #15803D; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> 4 Sections Autonomes
        </span>
      </div>

      <!-- BARRE DE NAVIGATION RAPIDE PAR ONGLETS / ANCRES -->
      <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; background: #FFFFFF; padding: 12px 16px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <a href="#section-identite" class="btn btn-sm btn-nav-section" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 8px 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #DBEAFE;">
          <i data-lucide="building" style="width: 14px; height: 14px;"></i> 1. Identité & Logo
        </a>
        <a href="#section-coordonnees" class="btn btn-sm btn-nav-section" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 8px; padding: 8px 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #E2E8F0;">
          <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i> 2. Coordonnées & Localisation
        </a>
        <a href="#section-signataires" class="btn btn-sm btn-nav-section" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 8px; padding: 8px 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #E2E8F0;">
          <i data-lucide="pen-tool" style="width: 14px; height: 14px;"></i> 3. Signataires Officiels (PDF)
        </a>
        <a href="#section-parametres" class="btn btn-sm btn-nav-section" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 8px; padding: 8px 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #E2E8F0;">
          <i data-lucide="sliders" style="width: 14px; height: 14px;"></i> 4. Paramètres & Slugs
        </a>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 1 : IDENTITÉ & LOGO INSTITUTIONNEL                               -->
      <!-- ========================================================================= -->
      <div id="section-identite" class="card config-section-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <form class="form-config-section" id="form-config-identite" action="<?= url('etablissement/edit') ?>" method="POST" enctype="multipart/form-data" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_etablissement" value="<?= htmlspecialchars($item['id_etablissement'] ?? 1) ?>">
          <input type="hidden" name="section_id" value="identite">
          <input type="hidden" name="section_name" value="Identité & Logo Institutionnel">

          <!-- En-tête de Section -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #E2E8F0; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="building" style="width: 18px; height: 18px; color: #1E3A5F;"></i> 1. Identité & Logo Institutionnel
            </div>
            <span class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 11px; padding: 5px 10px; border-radius: 6px;">Section 1 / 4</span>
          </div>

          <!-- Bloc Téléversement Logo -->
          <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap; margin-bottom: 24px; background: #F8FAFC; padding: 18px 20px; border-radius: 10px; border: 1px solid #E2E8F0;">
            <?php
              $rawLogo = $item['logo_etablissement'] ?? '';
              $logoSrc = '';
              if (!empty($rawLogo)) {
                $logoSrc = (strpos($rawLogo, 'http') === 0) ? $rawLogo : url(ltrim($rawLogo, '/'));
              }
            ?>
            <div style="width: 110px; height: 110px; border-radius: 12px; background: #FFFFFF; border: 2px dashed #CBD5E1; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative;">
              <img id="logo-img-preview" src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo Institutionnel" style="max-width: 100%; max-height: 100%; object-fit: contain; <?= empty($logoSrc) ? 'display:none;' : '' ?>">
              <i id="logo-icon-placeholder" data-lucide="school" style="width: 44px; height: 44px; color: #94A3B8; <?= !empty($logoSrc) ? 'display:none;' : '' ?>"></i>
            </div>
            <div style="flex: 1; min-width: 250px;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléverser le Logo Officiel (PNG, JPG, SVG, WEBP)</label>
              <input type="file" id="logo-file-input" name="logo_file" accept="image/*" class="form-control" style="width: 100%; box-sizing: border-box; padding: 9px 12px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;">
              <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">Le logo sera inséré en en-tête de toutes les fiches, attestations, reçus et répertoires officiels PDF.</small>
            </div>
          </div>

          <!-- Champs Textes Identité -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; width: 100%; margin-bottom: 20px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom Officiel de l'Établissement <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="nom_etablissement" value="<?= htmlspecialchars($item['nom_etablissement'] ?? ($item['libelle_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION')) ?>" placeholder="Ex: GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Sigle / Nom Court <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="sigle_etablissement" value="<?= htmlspecialchars($item['sigle_etablissement'] ?? 'GROUPE EICG') ?>" placeholder="Ex: GROUPE EICG" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Slogan / Devise</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="slogan_etablissement" value="<?= htmlspecialchars($item['slogan_etablissement'] ?? 'Excellence - Rigueur - Réussite') ?>" placeholder="Ex: Excellence - Rigueur - Réussite">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Arrêté / Numéro d'Autorisation</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="numero_autorisation" value="<?= htmlspecialchars($item['numero_autorisation'] ?? ($item['numero_autorisation_etablissement'] ?? 'AGRÉÉ PAR L\'ÉTAT ET LE FDFP')) ?>" placeholder="Ex: AGRÉÉ PAR L'ÉTAT ET LE FDFP">
            </div>

            <div class="form-group" style="width: 100%; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Ministère de Tutelle</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="ministere_tutelle" value="<?= htmlspecialchars($item['ministere_tutelle'] ?? 'Ministère de l\'Enseignement Supérieur et de la Recherche Scientifique') ?>" placeholder="Ex: Ministère de l'Enseignement Supérieur et de la Recherche Scientifique">
            </div>
          </div>

          <!-- Pied de carte avec bouton AJAX dédié -->
          <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #F1F5F9; width: 100%;">
            <button type="submit" class="btn btn-primary btn-save-section" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer l'Identité & le Logo
            </button>
          </div>
        </form>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 2 : COORDONNÉES & LOCALISATION                                   -->
      <!-- ========================================================================= -->
      <div id="section-coordonnees" class="card config-section-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <form class="form-config-section" id="form-config-coordonnees" action="<?= url('etablissement/edit') ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_etablissement" value="<?= htmlspecialchars($item['id_etablissement'] ?? 1) ?>">
          <input type="hidden" name="section_id" value="coordonnees">
          <input type="hidden" name="section_name" value="Coordonnées & Localisation">

          <!-- En-tête de Section -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #E2E8F0; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #1E3A5F;"></i> 2. Coordonnées Téléphoniques & Localisation
            </div>
            <span class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 11px; padding: 5px 10px; border-radius: 6px;">Section 2 / 4</span>
          </div>

          <!-- Sous-titre Contacts -->
          <div style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="phone-call" style="width: 14px; height: 14px;"></i> Contacts Téléphoniques & Digitaux
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone Fixe / Standard</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="telephone_fixe" value="<?= htmlspecialchars($item['telephone_fixe'] ?? '27 31 62 40 57') ?>" placeholder="Ex: 27 31 62 40 57">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Mobile Principal <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="telephone_mobile" value="<?= htmlspecialchars($item['telephone_mobile'] ?? ($item['telephone_etablissement'] ?? '07 79 37 37 38')) ?>" placeholder="Ex: 07 79 37 37 38" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Mobile Secondaire</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="telephone_mobile2" value="<?= htmlspecialchars($item['telephone_mobile2'] ?? ($item['telephone_etablissement2'] ?? '05 04 59 39 99')) ?>" placeholder="Ex: 05 04 59 39 99">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Email Officiel <span style="color: #EF4444;">*</span></label>
              <input type="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="email_etablissement" value="<?= htmlspecialchars($item['email_etablissement'] ?? 'eicgbouake01@gmail.com') ?>" placeholder="Ex: eicgbouake01@gmail.com" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Site Web / Portail</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="site_web" value="<?= htmlspecialchars($item['site_web'] ?? 'www.groupe-eicg.net') ?>" placeholder="Ex: www.groupe-eicg.net">
            </div>
          </div>

          <!-- Sous-titre Localisation -->
          <div style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; padding-top: 12px; border-top: 1px dashed #E2E8F0;">
            <i data-lucide="navigation" style="width: 14px; height: 14px;"></i> Adresse Géographique & Postale
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; width: 100%; margin-bottom: 20px;">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse Géographique / Quartier</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="adresse_etablissement" value="<?= htmlspecialchars($item['adresse_etablissement'] ?? 'Bouaké quartier Kennedy, route ancien ADDR') ?>" placeholder="Ex: Bouaké quartier Kennedy, route ancien ADDR">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Boîte Postale</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="boite_postale" value="<?= htmlspecialchars($item['boite_postale'] ?? 'BP 960 Bouaké 01') ?>" placeholder="Ex: BP 960 Bouaké 01">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Ville</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="ville_etablissement" value="<?= htmlspecialchars($item['ville_etablissement'] ?? 'Bouaké') ?>" placeholder="Ex: Bouaké">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Pays</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="pays_etablissement" value="<?= htmlspecialchars($item['pays_etablissement'] ?? 'Côte d\'Ivoire') ?>" placeholder="Ex: Côte d'Ivoire">
            </div>
          </div>

          <!-- Pied de carte avec bouton AJAX dédié -->
          <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #F1F5F9; width: 100%;">
            <button type="submit" class="btn btn-primary btn-save-section" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer les Coordonnées & l'Adresse
            </button>
          </div>
        </form>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 3 : SIGNATAIRES & RESPONSABLES OFFICIELS (PDF)                    -->
      <!-- ========================================================================= -->
      <div id="section-signataires" class="card config-section-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <form class="form-config-section" id="form-config-signataires" action="<?= url('etablissement/edit') ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_etablissement" value="<?= htmlspecialchars($item['id_etablissement'] ?? 1) ?>">
          <input type="hidden" name="section_id" value="signataires">
          <input type="hidden" name="section_name" value="Signataires & Mentions légales">

          <!-- En-tête de Section -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #E2E8F0; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="pen-tool" style="width: 18px; height: 18px; color: #1E3A5F;"></i> 3. Titres & Noms des Responsables Signataires (Édition PDF)
            </div>
            <span class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 11px; padding: 5px 10px; border-radius: 6px;">Section 3 / 4</span>
          </div>

          <p style="color: #64748B; font-size: 13px; margin: 0 0 20px 0;">
            Ces informations alimentent dynamiquement les zones de signature, visas officiels, bulletins, certificats, reçus et décharges PDF sans hardcoding.
          </p>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px; width: 100%; margin-bottom: 20px;">
            <!-- Direction des Études / Pédagogie -->
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre Direction des Études</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="titre_direction_pedagogique" value="<?= htmlspecialchars($item['titre_direction_pedagogique'] ?? 'La Direction des Études & Pédagogie') ?>" placeholder="Ex: La Direction des Études & Pédagogie">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Directeur des Études <span style="color: #0284C7;">(Bulletins & PV)</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="nom_directeur_etudes" value="<?= htmlspecialchars($item['nom_directeur_etudes'] ?? 'Dr COULIBALY Sonan Hamed') ?>" placeholder="Ex: Dr COULIBALY Sonan Hamed">
            </div>

            <!-- Direction Générale -->
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre Direction Générale</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="titre_direction_generale" value="<?= htmlspecialchars($item['titre_direction_generale'] ?? 'La Direction Générale') ?>" placeholder="Ex: La Direction Générale">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Directeur Général / Fondateur</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="nom_directeur_general" value="<?= htmlspecialchars($item['nom_directeur_general'] ?? 'La Direction Générale') ?>" placeholder="Ex: M. KOUASSI ... ou La Direction Générale">
            </div>

            <!-- Service Financier / Caisse -->
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre Service Financier / Caisse</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="titre_service_finance" value="<?= htmlspecialchars($item['titre_service_finance'] ?? 'Le Service Comptabilité & Caisse') ?>" placeholder="Ex: Le Service Comptabilité & Caisse">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Responsable Financier / Comptable</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="nom_responsable_finance" value="<?= htmlspecialchars($item['nom_responsable_finance'] ?? 'Direction Financière GEICG') ?>" placeholder="Ex: Direction Financière GEICG">
            </div>

            <!-- Service Scolarité & Inscriptions -->
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre Service Scolarité & Inscriptions</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="titre_service_scolarite" value="<?= htmlspecialchars($item['titre_service_scolarite'] ?? 'Le Service Scolarité & Inscriptions') ?>" placeholder="Ex: Le Service Scolarité & Inscriptions">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Responsable Scolarité</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="nom_responsable_scolarite" value="<?= htmlspecialchars($item['nom_responsable_scolarite'] ?? 'Service Scolarité & Inscriptions') ?>" placeholder="Ex: Service Scolarité & Inscriptions">
            </div>

            <!-- Secrétaire Général & Mentions -->
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Secrétaire Général</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="nom_secretaire_general" value="<?= htmlspecialchars($item['nom_secretaire_general'] ?? 'Le Secrétaire Général') ?>" placeholder="Ex: Le Secrétaire Général">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Mention Cachet & Signature</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="mention_cachet_signature" value="<?= htmlspecialchars($item['mention_cachet_signature'] ?? 'Cachet & Signature autorisée') ?>" placeholder="Ex: Cachet & Signature autorisée">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Ville de Signature (Fait à ...)</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="ville_signature" value="<?= htmlspecialchars($item['ville_signature'] ?? 'Bouaké') ?>" placeholder="Ex: Bouaké">
            </div>
          </div>

          <!-- Pied de carte avec bouton AJAX dédié -->
          <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #F1F5F9; width: 100%;">
            <button type="submit" class="btn btn-primary btn-save-section" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer les Signataires & Cachets
            </button>
          </div>
        </form>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 4 : PARAMÈTRES SYSTÈME, PIEDS DE PAGE & SLUGS                     -->
      <!-- ========================================================================= -->
      <div id="section-parametres" class="card config-section-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <form class="form-config-section" id="form-config-parametres" action="<?= url('etablissement/edit') ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_etablissement" value="<?= htmlspecialchars($item['id_etablissement'] ?? 1) ?>">
          <input type="hidden" name="section_parametres" value="1">
          <input type="hidden" name="section_id" value="parametres">
          <input type="hidden" name="section_name" value="Paramètres Système & Slugs">

          <!-- En-tête de Section -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #E2E8F0; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="sliders" style="width: 18px; height: 18px; color: #1E3A5F;"></i> 4. Paramètres Système, Pieds de Page & Sigles (Slugs)
            </div>
            <span class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 11px; padding: 5px 10px; border-radius: 6px;">Section 4 / 4</span>
          </div>

          <!-- Bas de page & Préfixes -->
          <div style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="file-text" style="width: 14px; height: 14px;"></i> Bas de Page & Références Documents
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Texte de Pied de Page Officiel</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="texte_pied_page" value="<?= htmlspecialchars($item['texte_pied_page'] ?? 'Document officiel généré par le Système GEICG') ?>" placeholder="Ex: Document officiel généré par le Système GEICG">
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Préfixe des Références</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" name="prefixe_reference" value="<?= htmlspecialchars($item['prefixe_reference'] ?? 'REF-') ?>" placeholder="Ex: REF-">
            </div>
          </div>

          <!-- Toggles Raccourcis / Slugs -->
          <div style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; padding-top: 12px; border-top: 1px dashed #E2E8F0;">
            <i data-lucide="tag" style="width: 14px; height: 14px;"></i> Configuration des Raccourcis / Slugs (Sigles d'affichage)
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
            <p style="color: #64748B; font-size: 13px; margin: 0 0 16px 0;">
              Activez ou désactivez l'affichage prioritaire des sigles courts (ex: IDA au lieu de INFORMATIQUE ET DÉVELOPPEUR D'APPLICATIONS) dans l'interface et les listes.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
              <!-- Toggle Filières -->
              <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                  <div style="font-weight: 700; font-size: 14px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="book-open" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Slugs Filières
                  </div>
                  <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Utiliser le sigle court (ex: <strong>IDA</strong>, <strong>RHCOM</strong>)</div>
                </div>
                <div>
                  <input type="hidden" name="use_slug_filiere" value="0">
                  <label class="form-check form-switch" style="margin: 0; cursor: pointer; display: inline-flex; align-items: center;">
                    <input class="form-check-input" type="checkbox" name="use_slug_filiere" value="1" id="use_slug_filiere" <?= !empty($item['use_slug_filiere']) ? 'checked' : '' ?> style="width: 44px; height: 22px; cursor: pointer;">
                  </label>
                </div>
              </div>

              <!-- Toggle Cycles -->
              <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                  <div style="font-weight: 700; font-size: 14px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="layers" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Slugs Cycles
                  </div>
                  <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Utiliser le sigle court (ex: <strong>BTS</strong>, <strong>LICENCE</strong>)</div>
                </div>
                <div>
                  <input type="hidden" name="use_slug_cycle" value="0">
                  <label class="form-check form-switch" style="margin: 0; cursor: pointer; display: inline-flex; align-items: center;">
                    <input class="form-check-input" type="checkbox" name="use_slug_cycle" value="1" id="use_slug_cycle" <?= !empty($item['use_slug_cycle']) ? 'checked' : '' ?> style="width: 44px; height: 22px; cursor: pointer;">
                  </label>
                </div>
              </div>

              <!-- Toggle Niveaux -->
              <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                  <div style="font-weight: 700; font-size: 14px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="bar-chart-2" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Slugs Niveaux
                  </div>
                  <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Utiliser le sigle court (ex: <strong>1ÈRE ANNÉE</strong>, <strong>L2</strong>)</div>
                </div>
                <div>
                  <input type="hidden" name="use_slug_niveau" value="0">
                  <label class="form-check form-switch" style="margin: 0; cursor: pointer; display: inline-flex; align-items: center;">
                    <input class="form-check-input" type="checkbox" name="use_slug_niveau" value="1" id="use_slug_niveau" <?= !empty($item['use_slug_niveau']) ? 'checked' : '' ?> style="width: 44px; height: 22px; cursor: pointer;">
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Pied de carte avec bouton AJAX dédié -->
          <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #F1F5F9; width: 100%;">
            <button type="submit" class="btn btn-primary btn-save-section" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer les Paramètres & Slugs
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>
<?php
ViewContext::setScripts(['public/assets/js/modules/etablissements_config.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
