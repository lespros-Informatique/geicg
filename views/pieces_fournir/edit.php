<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 
$isEdit = !empty($item['id_piece_fournir']);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="<?= $isEdit ? 'file-edit' : 'file-plus' ?>" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
            <?= $isEdit ? 'Modifier le Document / Pièce Administrative' : 'Nouveau Document / Pièce Administrative' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            <?= $isEdit 
                ? 'Mise à jour des informations et consignes relatives à ce justificatif administratif' 
                : 'Enregistrez un nouveau document exigible auprès des étudiants pour la constitution de leur dossier' ?>
          </p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour au Répertoire
          </a>
        </div>
      </div>

      <?php if ($isEdit): ?>
        <!-- ========================================== -->
        <!-- MODE ÉDITION UNITAIRE D'UNE PIÈCE EXISTANTE -->
        <!-- ========================================== -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          
          <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 18px; margin-bottom: 24px; border-bottom: 1px solid #F1F5F9;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <span style="background: #EFF6FF; color: #1E3A5F; font-size: 13px; font-weight: 800; padding: 6px 14px; border-radius: 6px; border: 1px solid #BFDBFE;">
                Code : <?= htmlspecialchars($item['code_piece_fournir'] ?? '-') ?>
              </span>
              <?php if (!empty($item['created_at_piece'])): ?>
                <span style="color: #64748B; font-size: 12.5px;">
                  Créé le <?= date('d/m/Y à H:i', strtotime($item['created_at_piece'])) ?>
                </span>
              <?php endif; ?>
            </div>
            <span class="badge" style="background: <?= ($item['statut_piece'] ?? 'actif') === 'actif' ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= ($item['statut_piece'] ?? 'actif') === 'actif' ? '#15803D' : '#B91C1C' ?>; font-weight: 700; padding: 6px 12px; border-radius: 6px;">
              <?= strtoupper($item['statut_piece'] ?? 'actif') ?>
            </span>
          </div>

          <form action="<?= RACINE ?>piece_fournir/edit" method="POST" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
            <input type="hidden" name="id_piece_fournir" value="<?= (int)($item['id_piece_fournir'] ?? 0) ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
              
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Intitulé du document / Pièce <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" 
                       name="libelle_piece" 
                       value="<?= htmlspecialchars($item['libelle_piece'] ?? '') ?>" 
                       required 
                       placeholder="Ex: 1 Copie de l'Extrait de Naissance" 
                       class="form-control" 
                       style="border-radius: 8px; padding: 11px 14px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                <span style="font-size: 12px; color: #64748B; margin-top: 4px; display: block;">
                  Nom explicite affiché dans les dossiers d'inscription et listes de contrôle.
                </span>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Statut de la pièce <span style="color: #EF4444;">*</span>
                </label>
                <select name="statut_piece" class="form-select" style="border-radius: 8px; padding: 11px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="actif" <?= ($item['statut_piece'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif (Disponible pour les cycles)</option>
                  <option value="inactif" <?= ($item['statut_piece'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif (Désactivé du catalogue)</option>
                </select>
                <span style="font-size: 12px; color: #64748B; margin-top: 4px; display: block;">
                  Un document inactif ne pourra plus être sélectionné pour de nouveaux cycles d'études.
                </span>
              </div>

            </div>

            <div class="form-group" style="margin-bottom: 24px;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Description, remarques & consignes particulières
              </label>
              <textarea name="description_piece" 
                        rows="4" 
                        placeholder="Ex: Doit être daté de moins de 3 mois, certifié conforme ou copie lisible..." 
                        class="form-control" 
                        style="border-radius: 8px; padding: 12px 14px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%; resize: vertical;"><?= htmlspecialchars($item['description_piece'] ?? '') ?></textarea>
              <span style="font-size: 12px; color: #64748B; margin-top: 4px; display: block;">
                Consignes spécifiques communiquées au secrétariat académique et aux étudiants lors du dépôt de dossier.
              </span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #F1F5F9;">
              <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 22px;">
                Annuler
              </a>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 26px; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="save" style="width: 18px; height: 18px;"></i> Enregistrer les Modifications
              </button>
            </div>
          </form>

        </div>

      <?php else: ?>
        <!-- ============================================== -->
        <!-- MODE CRÉATION : SAISIE UNITAIRE OU MULTI-LOTS -->
        <!-- ============================================== -->
        
        <!-- Tabs Navigation -->
        <div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
          <button type="button" id="tab-btn-single" onclick="switchPieceTab('single')" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="file-plus-2" style="width: 17px; height: 17px;"></i> Saisie Unitaire
          </button>
          <button type="button" id="tab-btn-batch" onclick="switchPieceTab('batch')" class="btn" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 20px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i data-lucide="layers" style="width: 17px; height: 17px;"></i> Saisie Rapide par Lot (Multi-pièces)
          </button>
        </div>

        <!-- FORMULAIRE UNITAIRE -->
        <div id="pane-single" class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          
          <div style="margin-bottom: 20px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="file-text" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations du Document
            </h3>
            <p style="color: #64748B; font-size: 13px; margin: 0;">Saisissez les détails de la pièce administrative à ajouter au répertoire général.</p>
          </div>

          <form action="<?= RACINE ?>piece_fournir/add" method="POST" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
              
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Intitulé du document <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" 
                       name="libelle_piece" 
                       required 
                       placeholder="Ex: 1 Copie de CNI ou Passeport en cours de validité" 
                       class="form-control" 
                       style="border-radius: 8px; padding: 11px 14px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                <span style="font-size: 12px; color: #64748B; margin-top: 4px; display: block;">
                  Libellé qui figurera sur les fiches de dépôt de dossiers étudiants.
                </span>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Statut initial <span style="color: #EF4444;">*</span>
                </label>
                <select name="statut_piece" class="form-select" style="border-radius: 8px; padding: 11px 14px; border: 1px solid #CBD5E1; font-weight: 700; width: 100%;">
                  <option value="actif" selected>Actif (Immédiatement disponible pour les cycles)</option>
                  <option value="inactif">Inactif (Désactivé temporairement)</option>
                </select>
              </div>

            </div>

            <div class="form-group" style="margin-bottom: 24px;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Description, format exigé ou instructions complémentaires
              </label>
              <textarea name="description_piece" 
                        rows="4" 
                        placeholder="Ex: Original ou photocopie certifiée conforme, format A4, lisible..." 
                        class="form-control" 
                        style="border-radius: 8px; padding: 12px 14px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #F1F5F9;">
              <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 22px;">
                Annuler
              </a>
              <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 26px; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Enregistrer le Document
              </button>
            </div>
          </form>

        </div>

        <!-- FORMULAIRE PAR LOT (MULTI-LIGNES) -->
        <div id="pane-batch" class="card" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
          
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <div>
              <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Ajout Multiple de Pièces Administratives
              </h3>
              <p style="color: #64748B; font-size: 13px; margin: 0;">Ajoutez plusieurs pièces simultanément pour alimenter rapidement le répertoire de l'établissement.</p>
            </div>
            <button type="button" onclick="addBatchRow()" class="btn btn-secondary" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 700; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
              <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter une ligne
            </button>
          </div>

          <form action="<?= RACINE ?>piece_fournir/add" method="POST" id="form-batch-pieces" style="width: 100%;">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

            <div style="overflow-x: auto; width: 100%; margin-bottom: 24px;">
              <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; text-align: left;">
                    <th style="padding: 10px 14px; font-size: 12.5px; font-weight: 700; color: #475569; width: 40px;">#</th>
                    <th style="padding: 10px 14px; font-size: 12.5px; font-weight: 700; color: #475569; width: 45%;">Intitulé du document *</th>
                    <th style="padding: 10px 14px; font-size: 12.5px; font-weight: 700; color: #475569;">Instructions / Format requis</th>
                    <th style="padding: 10px 14px; font-size: 12.5px; font-weight: 700; color: #475569; width: 50px; text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody id="batch-rows-container">
                  <!-- Ligne 0 -->
                  <tr class="batch-row" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px 14px; font-weight: 700; color: #64748B;">1</td>
                    <td style="padding: 12px 14px;">
                      <input type="text" 
                             name="pieces[0][libelle]" 
                             required 
                             placeholder="Ex: 1 Copie du Dernier Diplôme ou Relevé de Notes" 
                             class="form-control" 
                             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                    </td>
                    <td style="padding: 12px 14px;">
                      <input type="text" 
                             name="pieces[0][description]" 
                             placeholder="Ex: Photocopie légalisée certifiée conforme" 
                             class="form-control" 
                             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%;">
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <button type="button" disabled class="btn btn-sm" style="background: #F1F5F9; color: #94A3B8; border: none; border-radius: 6px; padding: 6px 10px; cursor: not-allowed;">
                        <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                      </button>
                    </td>
                  </tr>

                  <!-- Ligne 1 -->
                  <tr class="batch-row" style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px 14px; font-weight: 700; color: #64748B;">2</td>
                    <td style="padding: 12px 14px;">
                      <input type="text" 
                             name="pieces[1][libelle]" 
                             placeholder="Ex: 4 Photos d'identité récentes format passeport" 
                             class="form-control" 
                             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
                    </td>
                    <td style="padding: 12px 14px;">
                      <input type="text" 
                             name="pieces[1][description]" 
                             placeholder="Ex: Fond blanc, nom & prénom au verso" 
                             class="form-control" 
                             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%;">
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <button type="button" onclick="removeBatchRow(this)" class="btn btn-sm btn-danger" style="background: #FEE2E2; color: #DC2626; border: none; border-radius: 6px; padding: 6px 10px; cursor: pointer;">
                        <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
              <button type="button" onclick="addBatchRow()" class="btn btn-secondary" style="background: #F8FAFC; color: #334155; border: 1px dashed #94A3B8; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter une autre ligne
              </button>

              <div style="display: flex; gap: 12px;">
                <a href="<?= RACINE ?>piece_fournir/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 22px;">
                  Annuler
                </a>
                <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 26px; display: inline-flex; align-items: center; gap: 8px;">
                  <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Enregistrer Toutes les Pièces
                </button>
              </div>
            </div>

          </form>

        </div>

      <?php endif; ?>

    </div>
  </main>
</div>

<script>
let batchRowIndex = 2;

function switchPieceTab(tab) {
  const btnSingle = document.getElementById('tab-btn-single');
  const btnBatch = document.getElementById('tab-btn-batch');
  const paneSingle = document.getElementById('pane-single');
  const paneBatch = document.getElementById('pane-batch');

  if (!btnSingle || !btnBatch || !paneSingle || !paneBatch) return;

  if (tab === 'single') {
    btnSingle.style.background = '#1E3A5F';
    btnSingle.style.color = '#FFFFFF';
    btnSingle.style.border = 'none';

    btnBatch.style.background = '#FFFFFF';
    btnBatch.style.color = '#64748B';
    btnBatch.style.border = '1px solid #CBD5E1';

    paneSingle.style.display = 'block';
    paneBatch.style.display = 'none';
  } else {
    btnBatch.style.background = '#1E3A5F';
    btnBatch.style.color = '#FFFFFF';
    btnBatch.style.border = 'none';

    btnSingle.style.background = '#FFFFFF';
    btnSingle.style.color = '#64748B';
    btnSingle.style.border = '1px solid #CBD5E1';

    paneSingle.style.display = 'none';
    paneBatch.style.display = 'block';
  }
  if (window.lucide) lucide.createIcons();
}

function addBatchRow() {
  const container = document.getElementById('batch-rows-container');
  if (!container) return;

  const rowCount = container.querySelectorAll('.batch-row').length + 1;
  const tr = document.createElement('tr');
  tr.className = 'batch-row';
  tr.style.borderBottom = '1px solid #F1F5F9';
  tr.innerHTML = `
    <td style="padding: 12px 14px; font-weight: 700; color: #64748B;">${rowCount}</td>
    <td style="padding: 12px 14px;">
      <input type="text" 
             name="pieces[${batchRowIndex}][libelle]" 
             placeholder="Ex: Certificat médical d'aptitude" 
             class="form-control" 
             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 600; width: 100%;">
    </td>
    <td style="padding: 12px 14px;">
      <input type="text" 
             name="pieces[${batchRowIndex}][description]" 
             placeholder="Ex: Délivré par un médecin agréé" 
             class="form-control" 
             style="border-radius: 6px; padding: 9px 12px; border: 1px solid #CBD5E1; font-weight: 500; width: 100%;">
    </td>
    <td style="padding: 12px 14px; text-align: center;">
      <button type="button" onclick="removeBatchRow(this)" class="btn btn-sm btn-danger" style="background: #FEE2E2; color: #DC2626; border: none; border-radius: 6px; padding: 6px 10px; cursor: pointer;">
        <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
      </button>
    </td>
  `;
  container.appendChild(tr);
  batchRowIndex++;
  if (window.lucide) lucide.createIcons();
}

function removeBatchRow(btn) {
  const row = btn.closest('tr');
  if (row) {
    row.remove();
    // Renuméroter les lignes
    const rows = document.querySelectorAll('#batch-rows-container .batch-row');
    rows.forEach((r, idx) => {
      const tdNum = r.querySelector('td');
      if (tdNum) tdNum.textContent = idx + 1;
    });
  }
}

document.addEventListener('DOMContentLoaded', function() {
  if (window.lucide) lucide.createIcons();
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
