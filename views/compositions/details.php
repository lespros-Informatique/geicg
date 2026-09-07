<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$item = $item ?? [];
$targetClasses = $targetClasses ?? [];
$encryptedId = $encryptedId ?? '';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="award" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            Fiche Composition : <?= htmlspecialchars($item['libelle_composition'] ?? 'Épreuve') ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Détails officiels de la programmation, coefficients et classes associées</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <a href="<?= RACINE ?>composition/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <?php if (!empty($encryptedId)): ?>
          <a href="<?= RACINE ?>composition/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARD DETAILS GENERALS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
        
        <!-- Info Card 1 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Intitulé de l'Épreuve</div>
              <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($item['libelle_composition'] ?? '-') ?></div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; font-size: 13px; color: #475569; display: flex; justify-content: space-between;">
            <span>Code Composition :</span>
            <code style="font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($item['code_composition'] ?? '-') ?></code>
          </div>
        </div>

        <!-- Info Card 2 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="bar-chart-2" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Coefficient & Semestre</div>
              <div style="font-size: 16px; font-weight: 800; color: #15803D; margin-top: 2px;">
                Coef. <?= htmlspecialchars(number_format((float)($item['coefficient'] ?? 1), 2)) ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; font-size: 13px; color: #475569; display: flex; justify-content: space-between;">
            <span>Semestre Académique :</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_semestre'] ?? ($item['semestre_code'] ?? 'S1')) ?></strong>
          </div>
        </div>

        <!-- Info Card 3 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
              <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Date & Statut</div>
              <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 2px;">
                <?= !empty($item['date_composition']) ? date('d/m/Y', strtotime($item['date_composition'])) : '-' ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; font-size: 13px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
            <span>Statut Actuel :</span>
            <?php if (($item['statut_composition'] ?? '') === 'termine'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px;">Terminé</span>
            <?php else: ?>
              <span class="badge" style="background:#FEF3C7; color:#B45309; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px;">Programmé</span>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- TARGET CLASSES TABLE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Classes & Filières Rattachées (<?= count($targetClasses) ?>)
        </h3>

        <?php if (empty($targetClasses)): ?>
          <div style="padding: 20px; text-align: center; color: #94A3B8; font-size: 13px;">Aucune classe spécifique associée à cette composition.</div>
        <?php else: ?>
          <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase;">
                <th style="padding: 10px 14px;">#</th>
                <th style="padding: 10px 14px;">Niveau d'Études</th>
                <th style="padding: 10px 14px;">Filière</th>
                <th style="padding: 10px 14px;">Classe Cible</th>
                <th style="padding: 10px 14px; text-align: right;">Actions Saisie Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($targetClasses as $idx => $tc): ?>
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 12px 14px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                  <td style="padding: 12px 14px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($tc['libelle_niveau'] ?? $tc['niveau_code'] ?? '-') ?></td>
                  <td style="padding: 12px 14px;"><span style="background: #F1F5F9; color: #334155; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;"><?= htmlspecialchars($tc['libelle_filiere'] ?? $tc['filiere_code'] ?? '-') ?></span></td>
                  <td style="padding: 12px 14px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($tc['libelle_classe'] ?? $tc['classe_code']) ?></td>
                  <td style="padding: 12px 14px; text-align: right;">
                    <a href="<?= RACINE ?>note/saisieClasse?classe_code=<?= urlencode($tc['classe_code']) ?>&semestre_code=<?= urlencode($item['semestre_code'] ?? '') ?>" class="btn btn-sm" style="background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE; border-radius:6px; font-weight:700; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;">
                      <i data-lucide="edit-3" style="width:14px; height:14px;"></i> Saisir les notes
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
