<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réinitialisation de Mot de Passe - GEICG</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 24px 12px; color: #1E293B;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); border: 1px solid #E2E8F0;">
    
    <!-- En-tête -->
    <tr>
      <td style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); padding: 36px 32px; text-align: center;">
        <div style="display: inline-block; background: rgba(255,255,255,0.1); padding: 8px 18px; border-radius: 20px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.15);">
          <span style="color: #60A5FA; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">Sécurité du Compte</span>
        </div>
        <h1 style="color: #FFFFFF; font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">GROUPE EICG</h1>
        <p style="color: #94A3B8; font-size: 13px; margin: 6px 0 0 0; font-weight: 500;">Demande de Réinitialisation de Mot de Passe</p>
      </td>
    </tr>

    <!-- Contenu Principal -->
    <tr>
      <td style="padding: 36px 32px;">
        <h2 style="color: #0F172A; font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 16px;">
          Bonjour <?= htmlspecialchars($userName ?? 'Cher Utilisateur') ?>,
        </h2>
        
        <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
          Une demande de réinitialisation de votre mot de passe d'accès au portail <strong>GROUPE EICG</strong> a été enregistrée.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
          Pour définir votre nouveau mot de passe en toute sécurité, veuillez cliquer sur le bouton ci-dessous :
        </p>

        <!-- Bouton CTA -->
        <div style="text-align: center; margin: 32px 0 24px 0;">
          <a href="<?= htmlspecialchars($resetUrl ?? '#') ?>" target="_blank" style="background: linear-gradient(135deg, #16A34A 0%, #15803D 100%); color: #FFFFFF; font-weight: 800; padding: 16px 36px; text-decoration: none; border-radius: 12px; font-size: 15px; display: inline-block; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35); letter-spacing: 0.3px;">
            Réinitialiser Mon Mot de Passe &rarr;
          </a>
        </div>

        <!-- Alerte d'expiration -->
        <div style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 24px 0;">
          <p style="font-size: 13px; color: #92400E; margin: 0; line-height: 1.5;">
            <strong>Information importante :</strong> Ce lien de réinitialisation est à usage unique. Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité.
          </p>
        </div>

        <div style="background: #F1F5F9; border-radius: 8px; padding: 12px 16px; margin: 16px 0 24px 0; text-align: center;">
          <p style="font-size: 11px; color: #64748B; margin: 0 0 4px 0; font-weight: 600;">
            Lien direct de réinitialisation :
          </p>
          <a href="<?= htmlspecialchars($resetUrl ?? '#') ?>" style="color: #2563EB; font-size: 12px; font-weight: 600; word-break: break-all; text-decoration: underline;">
            <?= htmlspecialchars($resetUrl ?? '') ?>
          </a>
        </div>
      </td>
    </tr>

    <!-- Pied de page -->
    <tr>
      <td style="background-color: #F8FAFC; padding: 24px 32px; text-align: center; border-top: 1px solid #E2E8F0; font-size: 12px; color: #94A3B8; line-height: 1.5;">
        Cet e-mail automatique est transmis par le système de sécurité GROUPE EICG.<br>
        &copy; <?= date('Y') ?> GROUPE EICG &bull; Tous droits réservés.
      </td>
    </tr>
  </table>
</body>
</html>
