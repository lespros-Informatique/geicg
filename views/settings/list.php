<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>

  <main class="main-content">
    <div class="content-header" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
      <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
          <i data-lucide="sliders" style="width: 28px; height: 28px; color: #2563EB;"></i>
          Paramètres Globaux Système Lavex
        </h1>
        <p style="color: #64748B; font-size: 14px; margin: 4px 0 0;">
          Configuration centrale de la commission globale, des tarifs logistiques standards et des réglages plateforme (Réservé au Super Admin).
        </p>
      </div>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
      <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
        <?= htmlspecialchars($_SESSION['success_msg']) ?>
      </div>
      <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <form action="<?= RACINE ?>setting/list" method="POST">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">

        <!-- === BLOC 1: COMMISSION & FINANCES === -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
          <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="percent" style="width: 20px; height: 20px; color: #2563EB;"></i>
            Commission & Modèle Économique
          </h3>

          <div class="form-field" style="margin-bottom: 18px;">
            <label for="commission_defaut_lavex" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Taux de Commission Globale Lavex (%)
            </label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('percent'); ?></span>
              <input type="number" class="form-control" id="commission_defaut_lavex" name="commission_defaut_lavex"
                     min="0" max="100" step="0.5" value="<?= htmlspecialchars($settings['commission_defaut_lavex'] ?? '0.00') ?>" required>
            </div>
            <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 6px;">
              Pourcentage prélevé par défaut par Lavex sur les ventes de tous les pressings partenaires (0.00% au lancement).
            </small>
          </div>
        </div>

        <!-- === BLOC 2: LOGISTIQUE STANDARDS === -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
          <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="truck" style="width: 20px; height: 20px; color: #2563EB;"></i>
            Tarification Flotte Officielle Lavex
          </h3>

          <div class="form-field" style="margin-bottom: 18px;">
            <label for="frais_collecte_defaut_lavex" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Frais de Collecte Standards (FCFA)
            </label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
              <input type="number" class="form-control" id="frais_collecte_defaut_lavex" name="frais_collecte_defaut_lavex"
                     min="0" step="100" value="<?= htmlspecialchars($settings['frais_collecte_defaut_lavex'] ?? '1000.00') ?>" required>
            </div>
            <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 6px;">
              Tarif forfaitaire de collecte appliqué lorsque le pressing utilise la flotte Lavex.
            </small>
          </div>

          <div class="form-field" style="margin-bottom: 18px;">
            <label for="frais_livraison_defaut_lavex" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Frais de Livraison Standards (FCFA)
            </label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
              <input type="number" class="form-control" id="frais_livraison_defaut_lavex" name="frais_livraison_defaut_lavex"
                     min="0" step="100" value="<?= htmlspecialchars($settings['frais_livraison_defaut_lavex'] ?? '1000.00') ?>" required>
            </div>
            <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 6px;">
              Tarif forfaitaire de livraison appliqué lorsque le pressing utilise la flotte Lavex.
            </small>
          </div>

          <div class="form-field" style="margin-bottom: 18px;">
            <label for="delai_livraison_defaut_lavex" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Temps / Délais Moyen de Livraison Lavex
            </label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('clock'); ?></span>
              <input type="text" class="form-control" id="delai_livraison_defaut_lavex" name="delai_livraison_defaut_lavex"
                     placeholder="ex: 24h - 48h" value="<?= htmlspecialchars($settings['delai_livraison_defaut_lavex'] ?? '24h - 48h') ?>" required>
            </div>
            <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 6px;">
              Délais moyen de traitement et livraison garanti par la flotte officielle Lavex (ex: 24h - 48h).
            </small>
          </div>
        </div>

        <!-- === BLOC 3: INFORMATIONS DE LA PLATEFORME === -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); grid-column: 1 / -1;">
          <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="info" style="width: 20px; height: 20px; color: #2563EB;"></i>
            Identité & Support Lavex
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div class="form-field">
              <label for="nom_plateforme" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                Nom de la Plateforme
              </label>
              <input type="text" class="form-control" id="nom_plateforme" name="nom_plateforme"
                     value="<?= htmlspecialchars($settings['nom_plateforme'] ?? 'Lavex') ?>" required>
            </div>

            <div class="form-field">
              <label for="email_support" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                Email Support Client
              </label>
              <input type="email" class="form-control" id="email_support" name="email_support"
                     value="<?= htmlspecialchars($settings['email_support'] ?? 'contact@lavex.ci') ?>" required>
            </div>
          </div>
        </div>

        <!-- === BLOC 4: NOTIFICATIONS PUSH ONESIGNAL === -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); grid-column: 1 / -1;">
          <h3 style="font-size: 16px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="bell-ring" style="width: 20px; height: 20px; color: #2563EB;"></i>
            Clés de Notifications Push Temps Réel (OneSignal)
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div class="form-field">
              <label for="onesignal_app_id" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                OneSignal App ID
              </label>
              <input type="text" class="form-control" id="onesignal_app_id" name="onesignal_app_id"
                     placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" value="<?= htmlspecialchars($settings['onesignal_app_id'] ?? '') ?>">
              <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 6px;">
                Identifiant unique de votre projet OneSignal App.
              </small>
            </div>

            <div class="form-field">
              <label for="onesignal_rest_api_key" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                OneSignal REST API Key
              </label>
              <input type="password" class="form-control" id="onesignal_rest_api_key" name="onesignal_rest_api_key"
                     placeholder="Os_v2_app_xxxxxxxx..." value="<?= htmlspecialchars($settings['onesignal_rest_api_key'] ?? '') ?>">
              <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 6px;">
                Clé secrète REST API utilisée par le serveur pour expédier les notifications push en arrière-plan.
              </small>
            </div>
          </div>
        </div>

      </div>

      <div style="margin-top: 24px; text-align: right;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-weight: 700; font-size: 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="save" style="width: 18px; height: 18px;"></i>
          Enregistrer les Paramètres Globaux
        </button>
      </div>
    </form>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
