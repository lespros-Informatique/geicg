<?php
require_once __DIR__ . '/../../public/inc/header.php';
var_dump(Validator::hashPassword(123));
?>

<div class="app-layout auth-layout">
  <main class="auth-content">
    <div class="auth-card">
      <div class="auth-header">
        <div class="logo"> <?= LOGO ?> </div>
        <h1>Connexion</h1>
        <p>Connectez-vous à l'administration</p>
      </div>

      <div class="auth-body">
        <form class="formConnexion" method="POST">
          <?= Validator::csrfField() ?>

          <div class="form-field">
            <label for="login">Code utilisateur</label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('user'); ?></span>
              <input type="text" class="form-control" id="login" name="login" required autofocus>
            </div>
          </div>

          <div class="form-field">
            <label for="password">Mot de passe</label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('lock'); ?></span>
              <input type="password" class="form-control" id="password" name="password" required>
              <button type="button" class="password-toggle" id="togglePassword" aria-label="Afficher le mot de passe">
                <i data-lucide="eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn_actions btnConnexion">
            <span class="btn-text">
              <i data-lucide="log-in"></i>
              Se connecter
            </span>
          </button>
        </form>
      </div>
      <div class="auth-footer">
        &copy; 2026 Kits Admin
      </div>
    </div>
  </main>
</div>

<?php 
require_once __DIR__ . '/../../public/inc/footer-link.php';
 ?>

