<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Détaillée : Affectation de Cours</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation complète des données de l'affectation</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>enseignant_matiere/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>enseignant_matiere/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Éditer cet élément
          </a>
        </div>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #F1F5F9;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <h3 style="font-size: 16px; font-weight: 700; color: #0F172A; margin: 0;">Détail de l'Affectation</h3>
            <span style="font-size: 12px; color: #64748B;">Réf ID #<?= htmlspecialchars($item['id_enseignant_matiere'] ?? '-') ?></span>
          </div>
          <div style="margin-left: auto;">
            <?php if (($item['statut_enseignant_matiere'] ?? '') === 'actif'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 14px; border-radius:14px; font-weight:700; font-size:13px;">Statut : Actif</span>
            <?php else: ?>
              <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:6px 14px; border-radius:14px; font-weight:700; font-size:13px;">Statut : Inactif</span>
            <?php endif; ?>
          </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border-radius: 8px; padding: 16px; border: 1px solid #F1F5F9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Enseignant / Professeur</div>
            <div style="font-size: 15px; font-weight: 700; color: #0F172A; word-break: break-word;">
              <?= !empty($item['enseignant_nom']) ? htmlspecialchars($item['enseignant_nom']) : htmlspecialchars($item['enseignant_code'] ?? 'Non renseigné') ?>
            </div>
          </div>
          <div style="background: #F8FAFC; border-radius: 8px; padding: 16px; border: 1px solid #F1F5F9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Matière Enseignée</div>
            <div style="font-size: 15px; font-weight: 700; color: #1E3A5F; word-break: break-word;">
              <?= !empty($item['libelle_matiere']) ? htmlspecialchars($item['libelle_matiere']) : htmlspecialchars($item['matiere_code'] ?? 'Non renseigné') ?>
            </div>
          </div>
          <div style="background: #F8FAFC; border-radius: 8px; padding: 16px; border: 1px solid #F1F5F9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Classe Attribuée</div>
            <div style="font-size: 15px; font-weight: 700; color: #0F172A; word-break: break-word;">
              <?= !empty($item['libelle_classe']) ? htmlspecialchars($item['libelle_classe']) : htmlspecialchars($item['classe_code'] ?? 'Non assignée') ?>
            </div>
          </div>
          <div style="background: #F8FAFC; border-radius: 8px; padding: 16px; border: 1px solid #F1F5F9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Coefficient (Pondération)</div>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; word-break: break-word;">
              <?= htmlspecialchars($item['coefficient'] ?? '1.0') ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
