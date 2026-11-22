<?php
require_once __DIR__ . '/../../public/inc/header.php';

$loginLogo = $loginLogo ?? ($etablissement['logo_etablissement'] ?? '');
$sigleEtab = !empty($etablissement['sigle_etablissement']) ? $etablissement['sigle_etablissement'] : 'GEICG';
$nomEtab = !empty($etablissement['nom_etablissement']) ? $etablissement['nom_etablissement'] : $sigleEtab;
$token = $token ?? '';
$isValidToken = !empty($isValidToken);
$resetUserName = $resetUserName ?? '';
?>

<div class="app-layout auth-layout">
  <main class="auth-content">
    <div class="auth-card">
      <div class="auth-header">
        <div class="logo">
          <?php if (!empty($loginLogo)): ?>
            <?php $logoUrl = (strpos($loginLogo, 'http') === 0) ? $loginLogo : url(ltrim($loginLogo, '/')); ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="max-height: 50px; max-width: 180px; object-fit: contain;">
          <?php else: ?>
            <?= LOGO ?>
          <?php endif; ?>
        </div>
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 10px;">Nouveau mot de passe</h1>
        <?php if ($isValidToken): ?>
          <p style="color: #64748B; font-size: 13px;">
            Bonjour <strong><?= htmlspecialchars($resetUserName) ?></strong>, choisissez un nouveau mot de passe sécurisé pour votre compte.
          </p>
        <?php else: ?>
          <p style="color: #64748B; font-size: 13px;">Lien de réinitialisation expiré ou invalide</p>
        <?php endif; ?>
      </div>

      <div class="auth-body">
        <?php if (!$isValidToken): ?>
          <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; padding: 14px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 18px; line-height: 1.5;">
            Ce lien de réinitialisation est invalide, a déjà été utilisé ou a expiré (validité 60 minutes).
          </div>
          <a href="<?= url('user/motDePasseOublie') ?>" class="btn btn-primary btn-block" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; justify-content: center; align-items: center; gap: 8px; text-decoration: none; width: 100%; box-sizing: border-box;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Demander un nouveau lien
          </a>
          <div style="text-align: center; margin-top: 16px;">
            <a href="<?= url('user/connexion') ?>" style="font-size: 13px; font-weight: 700; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Retour à la connexion
            </a>
          </div>
        <?php else: ?>
          <div id="reset-password-feedback" style="display: none; padding: 12px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px;"></div>

          <form id="form-reset-password" method="POST">
            <?= Validator::csrfField() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-field">
              <label for="reset_new_password">Nouveau mot de passe</label>
              <div class="input-with-icon">
                <span class="input-icon"><?= Validator::icon('lock'); ?></span>
                <input type="password" class="form-control" id="reset_new_password" name="new_password" minlength="6" placeholder="Minimum 6 caractères" required autofocus>
                <button type="button" class="password-toggle btn-toggle-reset-pwd" data-target="#reset_new_password" aria-label="Afficher le mot de passe">
                  <i data-lucide="eye"></i>
                </button>
              </div>
            </div>

            <div class="form-field">
              <label for="reset_confirm_password">Confirmer le mot de passe</label>
              <div class="input-with-icon">
                <span class="input-icon"><?= Validator::icon('shield'); ?></span>
                <input type="password" class="form-control" id="reset_confirm_password" name="confirm_password" minlength="6" placeholder="Répétez le nouveau mot de passe" required>
                <button type="button" class="password-toggle btn-toggle-reset-pwd" data-target="#reset_confirm_password" aria-label="Afficher le mot de passe">
                  <i data-lucide="eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" id="btn-submit-reset" class="btn btn-primary btn-block btn_actions" style="background: #1E3A5F; border-color: #1E3A5F;">
              <span class="btn-text">
                <i data-lucide="check-circle"></i>
                Enregistrer le nouveau mot de passe
              </span>
            </button>

            <div style="text-align: center; margin-top: 16px;">
              <a href="<?= url('user/connexion') ?>" style="font-size: 13px; font-weight: 700; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Retour à la connexion
              </a>
            </div>
          </form>
        <?php endif; ?>
      </div>

      <div class="auth-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($nomEtab) ?>
      </div>
    </div>
  </main>
</div>

<script type="application/json" id="reset-password-config">
<?= json_encode([
    'actionUrl' => url('user/reset-password'),
    'loginUrl'  => url('user/connexion')
]) ?>
</script>

<?php
ViewContext::setScripts(['public/assets/js/modules/users.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
