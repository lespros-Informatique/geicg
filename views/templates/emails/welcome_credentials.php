<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenue sur GEICG</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 24px 12px; color: #1E293B;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); border: 1px solid #E2E8F0;">
    
    <!-- En-tête Institutionnel -->
    <tr>
      <td style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); padding: 36px 32px; text-align: center;">
        <div style="display: inline-block; background: rgba(255,255,255,0.1); padding: 8px 18px; border-radius: 20px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.15);">
          <span style="color: #60A5FA; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">Compte Utilisateur</span>
        </div>
        <h1 style="color: #FFFFFF; font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">GROUPE EICG</h1>
        <p style="color: #94A3B8; font-size: 13px; margin: 6px 0 0 0; font-weight: 500;">Portail d'Administration & Gestion Académique</p>
      </td>
    </tr>

    <!-- Message de Bienvenue -->
    <tr>
      <td style="padding: 36px 32px;">
        <h2 style="color: #0F172A; font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 16px;">
          Bienvenue au sein de notre institution, <?= htmlspecialchars($userNom ?? 'Cher Collaborateur') ?> ! 👋
        </h2>
        
        <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
          Votre compte d'accès à la plateforme **GROUPE EICG** a été créé et configuré avec succès par la Direction Administrateur. Nous sommes ravis de vous compter parmi nous.
        </p>

        <!-- Carte des Détails du Compte & Affectation -->
        <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; margin: 24px 0;">
          <div style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 14px; border-bottom: 1px solid #E2E8F0; padding-bottom: 8px;">
            Profil & Coordonnées d'Accès
          </div>
          
          <table role="presentation" width="100%" cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
            <tr>
              <td style="font-weight: 600; color: #64748B; font-size: 13px; width: 150px;">Collaborateur :</td>
              <td style="color: #0F172A; font-weight: 700; font-size: 14px;"><?= htmlspecialchars($userNom ?? '-') ?></td>
            </tr>
            <?php if (!empty($userFonction)): ?>
            <tr>
              <td style="font-weight: 600; color: #64748B; font-size: 13px;">Fonction / Rôle :</td>
              <td style="color: #1E3A5F; font-weight: 700; font-size: 14px;">
                <span style="background: #E0F2FE; color: #0369A1; padding: 3px 10px; border-radius: 6px; font-size: 12px; display: inline-block;">
                  <?= htmlspecialchars($userFonction) ?>
                </span>
              </td>
            </tr>
            <?php endif; ?>
            <tr>
              <td style="font-weight: 600; color: #64748B; font-size: 13px; padding-top: 10px;">Identifiant / Email :</td>
              <td style="color: #0F172A; font-weight: 800; font-size: 14px; padding-top: 10px;"><?= htmlspecialchars($userEmail ?? '-') ?></td>
            </tr>
            <?php if (!empty($userPassword)): ?>
            <tr>
              <td style="font-weight: 600; color: #64748B; font-size: 13px;">Mot de passe temporaire :</td>
              <td style="padding-top: 4px;">
                <code style="color: #15803D; font-weight: 800; font-size: 15px; font-family: 'Courier New', Courier, monospace; background: #DCFCE7; border: 1px dashed #86EFAC; padding: 4px 12px; border-radius: 6px; display: inline-block;">
                  <?= htmlspecialchars($userPassword) ?>
                </code>
              </td>
            </tr>
            <?php endif; ?>
          </table>
        </div>

        <!-- Consigne Importante de Sécurité -->
        <div style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; padding: 18px; border-radius: 8px; margin: 24px 0;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td style="width: 28px; vertical-align: top; font-size: 18px;">🔒</td>
              <td style="font-size: 13px; color: #92400E; line-height: 1.5;">
                <strong style="color: #78350F; font-size: 14px; display: block; margin-bottom: 4px;">Consigne Obligatoire de Sécurité :</strong>
                Le mot de passe ci-dessus est un <strong>mot de passe temporaire</strong>. Pour des raisons de confidentialité, veuillez vous connecter et <strong>personnaliser votre mot de passe</strong> dès votre première session.
              </td>
            </tr>
          </table>
        </div>

        <!-- Bouton d'Action CTA -->
        <div style="text-align: center; margin: 32px 0 20px 0;">
          <a href="<?= htmlspecialchars($loginUrl ?? RACINE) ?>" target="_blank" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F233D 100%); color: #FFFFFF; font-weight: 800; padding: 16px 36px; text-decoration: none; border-radius: 12px; font-size: 15px; display: inline-block; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.35); letter-spacing: 0.3px;">
            Se Connecter à la Plateforme &rarr;
          </a>
        </div>

        <p style="font-size: 13px; color: #64748B; line-height: 1.5; text-align: center; margin: 0;">
          Si vous rencontrez la moindre difficulté d'accès, veuillez contacter la Direction des Systèmes d'Information.
        </p>
      </td>
    </tr>

    <!-- Pied de page -->
    <tr>
      <td style="background-color: #F8FAFC; padding: 24px 32px; text-align: center; border-top: 1px solid #E2E8F0; font-size: 12px; color: #94A3B8; line-height: 1.5;">
        Cet e-mail institutionnel vous a été adressé automatiquement par le système GROUPE EICG.<br>
        &copy; <?= date('Y') ?> GROUPE EICG - Grande École d'Ingénieurs et de Gestion &bull; Tous droits réservés.
      </td>
    </tr>
  </table>
</body>
</html>
