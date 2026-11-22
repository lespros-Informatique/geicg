<?php
  if (empty($etablissement) || !is_array($etablissement)) {
      if (class_exists('ModelConfiguration')) {
          $etablissement = (new ModelConfiguration())->getConfig();
      } else {
          $etablissement = [];
      }
  }
  $nomEtab = !empty($etablissement['nom_etablissement']) ? $etablissement['nom_etablissement'] : '';
  $sigleEtab = !empty($etablissement['sigle_etablissement']) ? $etablissement['sigle_etablissement'] : $nomEtab;
  $piedPage = !empty($etablissement['texte_pied_page']) ? $etablissement['texte_pied_page'] : $nomEtab;
  $ttl = (int)($ttlMinutes ?? 60);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réinitialisation de votre mot de passe</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 24px 12px; color: #1E293B;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">

    <!-- En-tête -->
    <tr>
      <td style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); padding: 34px 32px; text-align: center;">
        <div style="display: inline-block; background: rgba(255,255,255,0.1); padding: 7px 18px; border-radius: 20px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.15);">
          <span style="color: #60A5FA; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">Sécurité du Compte</span>
        </div>
        <h1 style="color: #FFFFFF; font-size: 23px; font-weight: 800; margin: 0; letter-spacing: -0.5px;"><?= htmlspecialchars($sigleEtab) ?></h1>
        <?php if ($nomEtab !== '' && $nomEtab !== $sigleEtab): ?>
          <p style="color: #94A3B8; font-size: 12.5px; margin: 6px 0 0 0; font-weight: 500;"><?= htmlspecialchars($nomEtab) ?></p>
        <?php endif; ?>
      </td>
    </tr>

    <!-- Contenu -->
    <tr>
      <td style="padding: 34px 32px;">
        <h2 style="color: #0F172A; font-size: 19px; font-weight: 800; margin-top: 0; margin-bottom: 14px;">
          Bonjour <?= htmlspecialchars($userNom ?? 'Utilisateur') ?>,
        </h2>

        <p style="font-size: 14px; line-height: 1.65; color: #475569; margin: 0 0 18px 0;">
          Nous avons reçu une demande de réinitialisation du mot de passe associée à votre compte (<strong><?= htmlspecialchars($userEmail ?? '') ?></strong>).
        </p>

        <p style="font-size: 14px; line-height: 1.65; color: #475569; margin: 0 0 26px 0;">
          Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :
        </p>

        <!-- Bouton CTA -->
        <div style="text-align: center; margin: 28px 0;">
          <a href="<?= htmlspecialchars($resetUrl ?? '') ?>" target="_blank" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F233D 100%); color: #FFFFFF; font-weight: 800; padding: 15px 34px; text-decoration: none; border-radius: 10px; font-size: 14.5px; display: inline-block; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.35);">
            Réinitialiser mon mot de passe &rarr;
          </a>
        </div>

        <!-- Alerte validité & sécurité -->
        <div style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 24px 0;">
          <div style="font-size: 13px; color: #92400E; line-height: 1.55;">
            <strong style="color: #78350F; display: block; margin-bottom: 4px;">Information importante :</strong>
            Ce lien est strictement personnel, à usage unique et valable pendant <strong><?= $ttl ?> minutes</strong>.<br>
            Si vous n'êtes pas à l'origine de cette demande, vous pouvez simplement ignorer cet e-mail ; votre mot de passe actuel reste inchangé.
          </div>
        </div>

        <!-- Lien de secours -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px; margin-top: 20px;">
          <div style="font-size: 12px; color: #64748B; margin-bottom: 6px;">Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</div>
          <div style="font-size: 12px; color: #1E3A5F; word-break: break-all; font-family: monospace;">
            <?= htmlspecialchars($resetUrl ?? '') ?>
          </div>
        </div>
      </td>
    </tr>

    <!-- Pied de page -->
    <tr>
      <td style="background-color: #F8FAFC; padding: 22px 32px; text-align: center; border-top: 1px solid #E2E8F0; font-size: 12px; color: #94A3B8; line-height: 1.5;">
        Cet e-mail vous a été envoyé automatiquement par le système <?= htmlspecialchars($sigleEtab) ?>.<br>
        &copy; <?= date('Y') ?> <?= htmlspecialchars($piedPage) ?> &bull; Tous droits réservés.
      </td>
    </tr>
  </table>
</body>
</html>
