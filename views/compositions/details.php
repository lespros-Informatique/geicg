<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$item = $item ?? [];
$targetClasses = $targetClasses ?? [];
$matieres = $matieres ?? [];
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
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Détails de la programmation, coefficients et classes associées</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <a href="<?= RACINE ?>composition/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <?php if (!empty($item['code_composition']) && (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::ENTER_NOTES])))): ?>
          <a href="<?= RACINE ?>note/saisieClasse?type_evaluation_code=EXAMEN&composition_code=<?= urlencode($item['code_composition']) ?>" class="btn btn-success" style="background: #16A34A; border-color: #16A34A; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 10px rgba(22,163,74,0.2);">
            <i data-lucide="edit-3" style="width: 18px; height: 18px;"></i> Saisir les Notes
          </a>
          <?php endif; ?>
          <?php if (!empty($encryptedId)): ?>
          <a href="<?= RACINE ?>composition/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARD DETAILS GENERALS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Info Card 1 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="file-text" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Intitulé de l'Épreuve</div>
              <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 2px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <?php 
                  $typ = $item['type_composition'] ?? 'EXAMEN';
                  $badgeBg = '#EFF6FF'; $badgeCol = '#1D4ED8';
                  if ($typ === 'DEVOIR') { $badgeBg = '#FEF3C7'; $badgeCol = '#B45309'; }
                  elseif ($typ === 'INTERROGATION') { $badgeBg = '#F0FDF4'; $badgeCol = '#15803D'; }
                  elseif ($typ === 'AUTRE') { $badgeBg = '#F1F5F9'; $badgeCol = '#475569'; }
                ?>
                <span style="font-size: 11px; font-weight: 800; background: <?= $badgeBg ?>; color: <?= $badgeCol ?>; padding: 2px 8px; border-radius: 6px;"><?= htmlspecialchars($typ) ?></span>
                <span><?= htmlspecialchars($item['libelle_composition'] ?? '-') ?></span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; font-size: 12.5px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
            <span>Code Composition :</span>
            <code style="font-weight: 800; color: #1E3A5F; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['code_composition'] ?? '-') ?></code>
          </div>
        </div>

        <!-- Info Card 2 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #F0FDF4; color: #15803D; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="bar-chart-2" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Coefficient & Semestre</div>
              <div style="font-size: 15px; font-weight: 800; color: #15803D; margin-top: 2px;">
                Coef. <?= htmlspecialchars(number_format((float)($item['coefficient'] ?? 1), 2)) ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; font-size: 12.5px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
            <span>Semestre :</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_semestre'] ?? ($item['semestre_code'] ?? 'S1')) ?></strong>
          </div>
        </div>

        <!-- Info Card 3 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Date & Statut</div>
              <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">
                <?php if (!empty($item['date_composition']) && !empty($item['date_fin_composition']) && $item['date_composition'] !== $item['date_fin_composition']): ?>
                  Du <?= date('d/m/Y', strtotime($item['date_composition'])) ?> au <?= date('d/m/Y', strtotime($item['date_fin_composition'])) ?>
                <?php else: ?>
                  <?= !empty($item['date_composition']) ? date('d/m/Y', strtotime($item['date_composition'])) : (!empty($item['date_fin_composition']) ? date('d/m/Y', strtotime($item['date_fin_composition'])) : '-') ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; font-size: 12.5px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
            <span>Statut Actuel :</span>
            <?php if (($item['statut_composition'] ?? '') === 'termine'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:20px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Terminé</span>
            <?php else: ?>
              <span class="badge" style="background:#FEF3C7; color:#B45309; padding:3px 10px; border-radius:20px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:12px;height:12px;"></i> Programmé</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Info Card 4 -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Niveaux & Filières Rattachés</div>
              <div style="font-size: 15px; font-weight: 800; color: #7E22CE; margin-top: 2px;">
                <?= count($targetClasses) ?> Niveaux / Filières
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; font-size: 12.5px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
            <span>État :</span>
            <span class="badge" style="background:#F3E8FF; color:#7E22CE; padding:3px 10px; border-radius:20px; font-weight:700; font-size:11px;">Active(s)</span>
          </div>
        </div>

      </div>

      <!-- TARGET CLASSES TABLE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 18px; height: 18px;"></i> Niveaux & Filières Rattachés (<?= count($targetClasses) ?>)
        </h3>

        <?php if (empty($targetClasses)): ?>
          <div style="padding: 20px; text-align: center; color: #94A3B8; font-size: 13px;">Aucun niveau/filière spécifique associé à cette composition.</div>
        <?php else: ?>
          <table id="table-niveaux-filieres" class="table display nowrap" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px; text-transform: uppercase;">
                <th style="padding: 10px 14px;">#</th>
                <th style="padding: 10px 14px;">Niveau d'Études</th>
                <th style="padding: 10px 14px;">Filière</th>
                <th style="padding: 10px 14px; text-align: center;">Nombre de Matières</th>
                <th style="padding: 10px 14px; text-align: right;">Actions Saisie Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $rowNum = 1;
                foreach ($targetClasses as $tc): 
                  $classeCode = $tc['code_classe'] ?? '';
                  $targetName = !empty($tc['libelle_classe']) ? $tc['libelle_classe'] : (($tc['libelle_niveau'] ?? '') . ' - ' . ($tc['libelle_filiere'] ?? ''));
                  $nbMatieres = (int)($tc['nb_matieres'] ?? 0);
              ?>
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 12px 14px; font-weight: 700; color: #64748B;"><?= $rowNum++ ?></td>
                  <td style="padding: 12px 14px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($tc['libelle_niveau'] ?? '-') ?></td>
                  <td style="padding: 12px 14px;"><span style="background: #F1F5F9; color: #334155; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;"><?= htmlspecialchars($tc['libelle_filiere'] ?? '-') ?></span></td>
                  <td style="padding: 12px 14px; text-align: center;">
                    <span class="badge" style="background: <?= $nbMatieres > 0 ? '#EFF6FF' : '#F1F5F9' ?>; color: <?= $nbMatieres > 0 ? '#1E40AF' : '#64748B' ?>; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                      <i data-lucide="book-open" style="width: 13px; height: 13px;"></i>
                      <?= $nbMatieres ?> matière<?= $nbMatieres > 1 ? 's' : '' ?>
                    </span>
                  </td>
                  <td style="padding: 12px 14px; text-align: right;">
                    <div style="display: inline-flex; gap: 8px; align-items: center; justify-content: flex-end;">
                      <button type="button" 
                              class="btn btn-sm btn-open-saisie-modal" 
                              data-classe-code="<?= htmlspecialchars($classeCode) ?>"
                              data-classe-libelle="<?= htmlspecialchars($targetName) ?>"
                              data-cible-code="<?= htmlspecialchars($tc['code_composition_niveau_filiere'] ?? '') ?>"
                              style="background: #1E3A5F; color: #FFFFFF; border: none; border-radius: 6px; font-weight: 700; padding: 7px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 2px 4px rgba(30,58,95,0.2);">
                        <i data-lucide="list-checks" style="width: 14px; height: 14px;"></i> Liste Matières
                      </button>
                      <a href="<?= RACINE ?>note/saisieClasse?classe_code=<?= urlencode($classeCode) ?>&semestre_code=<?= urlencode($item['semestre_code'] ?? '') ?>&composition_code=<?= urlencode($item['code_composition'] ?? '') ?>&type_evaluation_code=EXAMEN" class="btn btn-sm" style="background:#EFF6FF; color:#1E40AF; border:1px solid #BFDBFE; border-radius:6px; font-weight:700; padding:7px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;">
                        <i data-lucide="edit-3" style="width:14px; height:14px;"></i> Grille des notes
                      </a>
                    </div>
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

<!-- MODAL D'ACTION : SAISIE DES NOTES AVEC CASES À COCHER PAR MATIÈRE -->
<div id="modal-saisie-matiere" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 580px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; border: 1px solid #E2E8F0; box-sizing: border-box; display: flex; flex-direction: column; max-height: 90vh;">
    
    <!-- Modal Header -->
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); padding: 20px 24px; color: #FFFFFF; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="check-square" style="width: 20px; height: 20px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Saisie des Notes - Choix des Matières</h3>
          <span style="font-size: 12px; color: #94A3B8;">Sélection des matières enseignées dans la classe</span>
        </div>
      </div>
      <button type="button" class="btn-close-saisie-modal" style="background: transparent; border: none; color: #94A3B8; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
        <i data-lucide="x" style="width: 22px; height: 22px;"></i>
      </button>
    </div>

    <!-- Form Content -->
    <form action="<?= RACINE ?>note/saisieClasse" method="GET" id="form-modal-saisie-notes" style="padding: 24px; display: flex; flex-direction: column; overflow: hidden; height: 100%;">
      <input type="hidden" name="classe_code" id="modal_input_classe_code" value="">
      <input type="hidden" name="composition_cible_code" id="modal_input_cible_code" value="">
      <input type="hidden" name="semestre_code" value="<?= htmlspecialchars($item['semestre_code'] ?? '') ?>">
      <input type="hidden" name="composition_code" value="<?= htmlspecialchars($item['code_composition'] ?? '') ?>">
      <input type="hidden" name="type_evaluation_code" value="EXAMEN">

      <!-- Context Card inside Modal -->
      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; flex-shrink: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
          <div>
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 2px;">Épreuve & Niveau / Filière</div>
            <div style="font-size: 14px; font-weight: 800; color: #0F172A;" id="modal_display_comp_name">
              <?= htmlspecialchars($item['libelle_composition'] ?? 'Examen') ?>
            </div>
          </div>
          <span id="badge-source-matieres" class="badge" style="background: #DCFCE7; color: #15803D; padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
            <i data-lucide="clock" style="width: 12px; height: 12px;"></i> Emploi du temps (Session)
          </span>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 10px; font-size: 12px; color: #334155; align-items: center; flex-wrap: wrap;">
          <span>Classe pour la grille : <strong id="modal_display_classe_name" style="color: #1E3A5F; font-size: 13px; background: #EFF6FF; padding: 3px 10px; border-radius: 6px; border: 1px solid #BFDBFE;">-</strong></span>
          <span>| Coef : <strong><?= htmlspecialchars($item['coefficient'] ?? '1') ?></strong></span>
          <span>| Semestre : <strong><?= htmlspecialchars($item['libelle_semestre'] ?? ($item['semestre_code'] ?? 'S1')) ?></strong></span>
        </div>
      </div>

      <!-- Bouton Tout cocher & Titre -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-shrink: 0;">
        <div style="font-size: 12.5px; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 8px;">
          <span>Matières enseignées dans cette classe :</span>
          <span id="badge-matieres-count" style="font-size: 11px; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 12px; font-weight: 700;">0 matière</span>
        </div>
        <button type="button" id="btn-toggle-select-all" class="btn btn-sm btn-outline-secondary" style="font-size: 12px; font-weight: 700; border-radius: 8px; padding: 6px 12px; white-space: nowrap;">
          Tout cocher
        </button>
      </div>

      <!-- Checkbox List Container (Scrollable) -->
      <div id="matieres-checkboxes-container" style="overflow-y: auto; max-height: 260px; padding-right: 4px; margin-bottom: 16px; flex-grow: 1;">
        <div style="padding: 30px; text-align: center; color: #64748B;">
          <i data-lucide="loader" class="spin" style="width: 24px; height: 24px; stroke-width: 2;"></i>
          <p style="margin: 8px 0 0 0; font-size: 13px;">Chargement des matières de l'emploi du temps...</p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 14px; border-top: 1px solid #F1F5F9; flex-shrink: 0;">
        <button type="button" class="btn btn-secondary btn-close-saisie-modal" style="font-weight: 600; border-radius: 8px; padding: 10px 18px;">
          Annuler
        </button>
        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border: none; font-weight: 700; border-radius: 8px; padding: 10px 22px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider les Matières
        </button>
      </div>
    </form>

  </div>
</div>

<script type="application/json" id="compositions-details-config">
<?= json_encode([
    'compositionCode' => $item['code_composition'] ?? ''
]) ?>
</script>
<?php
$pageScripts = ['public/assets/js/modules/compositions_details.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
