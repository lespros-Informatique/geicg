<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Scolarités & Échéanciers de Paiement</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion centralisée des grilles tarifaires et des tranches de versement</p>
        </div>
        <div id="btn-container" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
          <?php if (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::PRINT_FRAIS_SCOLARITE, PERMISSIONS::VIEW_FRAIS_SCOLARITE, PERMISSIONS::MANAGE_FRAIS_SCOLARITE]))): ?>
            <a href="<?= RACINE ?>scolarite/imprimerPdf" id="btn-print-scolarite" target="_blank" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer la Grille Tarifaire (PDF)
            </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>scolarite/formulaire" id="btn-add-action" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; color: #FFFFFF;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> <span id="btn-add-label">Ajouter Tarif de Scolarité</span>
          </a>
        </div>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES MULTI-CRITÈRES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid #F1F5F9; padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
          <div style="font-size: 13.5px; font-weight: 700; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="sliders-horizontal" style="width: 16px; height: 16px;"></i>
            <span>Filtres de Recherche & d'Impression</span>
          </div>
          <button type="button" id="btn-reset-filters" class="btn btn-sm btn-link text-decoration-none" style="color: #64748B; font-size: 12.5px; font-weight: 600; padding: 0; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; border: none; background: transparent;">
            <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Réinitialiser les filtres
          </button>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; align-items: flex-end;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Année Académique</label>
            <select id="filter-annee" data-default="<?= htmlspecialchars($selectedAnneeCode ?? '') ?>" class="form-control select2" style="width: 100%; border-radius: 8px; font-size: 13px;">
              <option value="">-- Toutes les années --</option>
              <?php foreach (($annees ?? []) as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($selectedAnneeCode ?? '') === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Filière</label>
            <select id="filter-filiere" class="form-control select2" style="width: 100%; border-radius: 8px; font-size: 13px;">
              <option value="">-- Toutes les filières --</option>
              <?php foreach (($filieres ?? []) as $f): ?>
                <option value="<?= htmlspecialchars($f['code_filiere']) ?>">
                  <?= htmlspecialchars($f['libelle_filiere'] ?? $f['code_filiere']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%; border-radius: 8px; font-size: 13px;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach (($niveaux ?? []) as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau'] ?? $n['code_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Régime d'Affectation</label>
            <select id="filter-affectation" class="form-control select2" style="width: 100%; border-radius: 8px; font-size: 13px;">
              <option value="">-- Tous les régimes --</option>
              <option value="affecte">Affecté (État)</option>
              <option value="non_affecte">Non Affecté (Privé)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Bandeau Résumé / Indicateurs Clés Stylisés -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Grilles -->
        <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04); display: flex; align-items: center; gap: 16px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
          <div style="width: 50px; height: 50px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(30, 58, 95, 0.12);">
            <i data-lucide="calculator" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Grilles Tarifaires</div>
            <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($totalScolarites ?? 0) ?></div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Combinaisons filières & niveaux</div>
          </div>
        </div>

        <!-- Régime Affecté (État) -->
        <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F0FDF4 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #DCFCE7; border-left: 4px solid #16A34A; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.05); display: flex; align-items: center; gap: 16px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
          <div style="width: 50px; height: 50px; border-radius: 12px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(21, 128, 61, 0.12);">
            <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Tarifs Affectés (État)</div>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; line-height: 1.2;"><?= (int)($totalAffectes ?? 0) ?></div>
            <div style="font-size: 11.5px; color: #166534; margin-top: 2px;">Bourse & prise en charge publique</div>
          </div>
        </div>

        <!-- Régime Non Affecté (Privé) -->
        <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F0F9FF 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #E0F2FE; border-left: 4px solid #0284C7; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.05); display: flex; align-items: center; gap: 16px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
          <div style="width: 50px; height: 50px; border-radius: 12px; background: #E0F2FE; color: #0369A1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(3, 105, 161, 0.12);">
            <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #0369A1; text-transform: uppercase; letter-spacing: 0.5px;">Tarifs Privés (Non Affectés)</div>
            <div style="font-size: 24px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($totalNonAffectes ?? 0) ?></div>
            <div style="font-size: 11.5px; color: #0284C7; margin-top: 2px;">Inscriptions directes autofinancées</div>
          </div>
        </div>
      </div>

      <!-- Nav Tabs -->
      <ul class="nav nav-tabs" id="scolariteTabs" role="tablist" style="border-bottom: 2px solid #E2E8F0; margin-bottom: 20px; gap: 8px; list-style: none; padding-left: 0; display: flex;">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-scolarites-btn" data-bs-target="#tab-scolarites" type="button" role="tab" style="font-weight: 700; padding: 12px 22px; border-radius: 8px 8px 0 0; color: #1E3A5F; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E2E8F0; border-bottom: none; background: #FFFFFF; cursor: pointer;">
            <i data-lucide="receipt" style="width: 16px; height: 16px;"></i> Grille des Scolarités
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-tranches-btn" data-bs-target="#tab-tranches" type="button" role="tab" style="font-weight: 700; padding: 12px 22px; border-radius: 8px 8px 0 0; color: #64748B; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E2E8F0; border-bottom: none; background: #F8FAFC; cursor: pointer;">
            <i data-lucide="list-checks" style="width: 16px; height: 16px;"></i> Échéanciers de Paiement
          </button>
        </li>
      </ul>

      <div class="tab-content" id="scolariteTabsContent">
        <!-- TAB 1 : GRILLE DES SCOLARITÉS -->
        <div class="tab-pane fade show active" id="tab-scolarites" role="tabpanel">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table-scolarites" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                    <th style="padding: 12px; width: 50px;">#</th>
                    <th style="padding: 12px;">Code</th>
                    <th style="padding: 12px;">Statut d'Affectation</th>
                    <th style="padding: 12px;">Filière</th>
                    <th style="padding: 12px;">Niveau</th>
                    <th style="padding: 12px;">Montant (FCFA)</th>
                    <th class="text-center" style="padding: 12px;">Statut</th>
                    <th class="text-end" style="padding: 12px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 2 : ÉCHEANCIERS DE PAIEMENT -->
        <div class="tab-pane fade" id="tab-tranches" role="tabpanel" style="display: none;">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table-tranches_scolarite" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                    <th style="padding: 12px; width: 50px;">#</th>
                    <th style="padding: 12px;">Code</th>
                    <th style="padding: 12px;">Scolarité Rattachée</th>
                    <th style="padding: 12px;">Libellé Tranche</th>
                    <th style="padding: 12px;">Montant (FCFA)</th>
                    <th style="padding: 12px;">Date Limite</th>
                    <th class="text-center" style="padding: 12px;">Statut</th>
                    <th class="text-end" style="padding: 12px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<?php ViewContext::setScripts(['public/assets/js/modules/scolarites.js']); ?>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
