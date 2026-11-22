<?php
require_once __DIR__ . '/../../public/inc/header.php';

$loginLogo = $loginLogo ?? ($etablissement['logo_etablissement'] ?? '');
$sigleEtab = !empty($etablissement['sigle_etablissement']) ? $etablissement['sigle_etablissement'] : 'GEICG';
$nomEtab = !empty($etablissement['nom_etablissement']) ? $etablissement['nom_etablissement'] : $sigleEtab;
?>

<div class="app-layout auth-layout auth-layout-campus">
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
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 10px;">Mot de passe oublié</h1>
        <p style="color: #64748B; font-size: 13px;">Saisissez votre adresse e-mail ou numéro de téléphone pour recevoir un lien de réinitialisation.</p>
      </div>

      <div class="auth-body">
        <div id="forgot-password-feedback" style="display: none; padding: 12px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 16px;"></div>

        <form id="form-forgot-password" method="POST">
          <?= Validator::csrfField() ?>

          <div class="form-field">
            <label for="forgot_login">Email ou Téléphone</label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('mail'); ?></span>
              <input type="text" class="form-control" id="forgot_login" name="login" placeholder="Ex: utilisateur@domaine.com ou 0700000000" required autofocus>
            </div>
          </div>

          <button type="submit" id="btn-submit-forgot" class="btn btn-primary btn-block btn_actions" style="background: #1E3A5F; border-color: #1E3A5F;">
            <span class="btn-text">
              <i data-lucide="send"></i>
              Envoyer le lien de réinitialisation
            </span>
          </button>

          <div style="text-align: center; margin-top: 16px;">
            <a href="<?= url('user/connexion') ?>" style="font-size: 13px; font-weight: 700; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Retour à la connexion
            </a>
          </div>
        </form>
      </div>

      <div class="auth-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($nomEtab) ?>
      </div>
    </div>
  </main>
</div>

<script type="application/json" id="forgot-password-config">
<?= json_encode([
    'actionUrl' => url('user/motDePasseOublie'),
    'loginUrl'  => url('user/connexion')
]) ?>
</script>

<?php
ViewContext::setScripts(['public/assets/js/modules/users.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
