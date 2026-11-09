<?php
require_once __DIR__ . '/../../public/inc/header.php';
$cours = isset($cours) ? $cours : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Enseignant : <?= htmlspecialchars(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? '')) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Profil académique, compte d'accès et répartition des cours</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
          <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>enseignant/imprimerFichePdf/<?= $encryptedId ?>" target="_blank" class="btn btn-outline-primary" style="background: #FFFFFF; border: 1.5px solid #1E3A5F; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; cursor: pointer; transition: all 0.2s;" title="Générer et imprimer la fiche officielle de l'enseignant en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer fiche enseignant
          </a>
          <a href="<?= RACINE ?>enseignant/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le profil
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : PROFIL ENSEIGNANT HORIZONTAL -->
      <?php
      $photoEnseignant = !empty($item['photo_user']) ? $item['photo_user'] : (!empty($item['photo_enseignant']) ? $item['photo_enseignant'] : '');
      $photoUrl = placeholderUrl('user');
      if (!empty($photoEnseignant)) {
          $cleanRel = ltrim($photoEnseignant, '/');
          if (file_exists(__DIR__ . '/../../public/' . $cleanRel)) {
              $photoUrl = RACINE . 'public/' . $cleanRel;
          } elseif (file_exists(__DIR__ . '/../../' . $cleanRel)) {
              $photoUrl = RACINE . $cleanRel;
          }
      }
      ?>
      <style>
        .enseignant-profil-grid {
          display: grid;
          grid-template-columns: 105px minmax(220px, 1fr) minmax(320px, 1.8fr);
          gap: 28px;
          align-items: center;
        }
        @media (max-width: 992px) {
          .enseignant-profil-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
            gap: 20px;
          }
        }
      </style>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="user" style="width: 18px; height: 18px;"></i> Informations Personnelles & Contrat RH
        </h3>

        <div class="enseignant-profil-grid">
          
          <!-- Colonne 1 : Photo Enseignant -->
          <div>
            <div style="width: 105px; height: 105px; min-width: 105px; border-radius: 14px; border: 2px solid #E2E8F0; overflow: hidden; background: #F8FAFC; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center;">
              <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Photo Enseignant" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= placeholderUrl('user') ?>';">
            </div>
          </div>

          <!-- Colonne 2 : Nom, Prénom, Grade & Statut -->
          <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.25;">
              <?= htmlspecialchars(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? '')) ?>
            </h2>
            <div style="font-size: 13px; color: #1E3A5F; font-weight: 600; margin-top: 4px;">
              <?= htmlspecialchars($item['grade_enseignant'] ?? 'Enseignant') ?>
            </div>
            <div style="margin-top: 10px;">
              <?php if (($item['statut_enseignant'] ?? '') === STATUTS::ACTIF): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:5px 14px; border-radius:8px; font-weight:700; font-size:12px; display: inline-flex; align-items: center; gap: 6px;">
                  <span style="width: 7px; height: 7px; border-radius: 50%; background: #15803D;"></span> Actif
                </span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:5px 14px; border-radius:8px; font-weight:700; font-size:12px; display: inline-flex; align-items: center; gap: 6px;">
                  <span style="width: 7px; height: 7px; border-radius: 50%; background: #B91C1C;"></span> Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Colonne 3 : Coordonnées, Contrat RH & Autorisation -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Identifiant de Connexion</span>
              <div style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($item['email_enseignant'] ?? 'Non renseigné') ?></div>
              <div style="font-size: 12px; color: #64748B; margin-top: 3px;">Tél : <?= htmlspecialchars($item['telephone_enseignant'] ?? '-') ?></div>
            </div>
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Contrat & Autorisation</span>
              <div style="font-size: 14px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($item['numero_autorisation'] ?? 'Non renseigné') ?></div>
              <div style="font-size: 12px; color: #64748B; text-transform: capitalize; margin-top: 3px;">Contrat <?= htmlspecialchars($item['type_contrat'] ?? 'Permanent') ?></div>
            </div>
          </div>

        </div>
      </div>

      <!-- CARD 2 (COL-12) : COURS ET MATIÈRES ASSIGNÉS -->
      <!-- CARD 2 : COURS ET MATIÈRES PLANIFIÉS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="book-open" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Cours & Matières Dispensés (<?= count($cours) ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>emploi/list" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 6px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="calendar" style="width: 14px; height: 14px;"></i> Voir emplois du temps
          </a>
        </div>

        <?php if (empty($cours)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucun cours n'est actuellement programmé pour cet enseignant.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Matière</th>
                  <th style="padding: 10px;">Classe</th>
                  <th style="padding: 10px; text-align: center;">Volume Hebdo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cours as $c): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($c['libelle_matiere'] ?? '-') ?></td>
                    <td style="padding: 10px; color: #334155; font-weight: 600;"><?= htmlspecialchars($c['libelle_classe'] ?? '-') ?></td>
                    <td style="padding: 10px; text-align: center; font-weight: 700; color: #0284C7;">
                      <?= !empty($c['volume_horaire']) ? $c['volume_horaire'] . ' h' : '-' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
