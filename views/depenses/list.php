<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$stats = $stats ?? (new ModelDepense())->getStats();
$typeDepenses = $typeDepenses ?? (new ModelTypeDepense())->getAll();
$canValidate = $canValidate ?? Auth::can(PERMISSIONS::VALIDATE_DEPENSES);
$canRecord = $canRecord ?? Auth::can(PERMISSIONS::RECORD_DEPENSES);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Dépenses & Charges de Fonctionnement</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et suivi des décaissements, engagements budgétaires et validation des dépenses</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>type_depense/list" class="btn btn-secondary" style="background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="tags" style="width: 18px; height: 18px;"></i> Catégories de Dépenses
          </a>
          <?php if ($canRecord): ?>
          <button type="button" class="btn btn-primary btn-add-depense" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Décaissement / Dépense
          </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- BARRE DE FILTRAGE : CATÉGORIE & PÉRIODE -->
      <!-- ========================================================================= -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="filter" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <span style="font-size: 13.5px; font-weight: 800; color: #0F172A; display: block;">Filtrer les Dépenses</span>
              <span style="font-size: 11.5px; color: #64748B;">Filtrage par catégorie et période d'engagement</span>
            </div>
          </div>
          
          <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end;">
            <!-- Filtre Catégorie -->
            <div style="min-width: 200px;">
              <label style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; display: block;">Catégorie</label>
              <select id="filter-type-depense" class="form-control select2" style="width: 100%;">
                <option value="">-- Toutes les catégories --</option>
                <?php foreach ($typeDepenses as $t): ?>
                  <option value="<?= htmlspecialchars($t['code_type_depense']) ?>">
                    <?= htmlspecialchars($t['libelle_type_depense']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filtre Date Début -->
            <div style="min-width: 150px;">
              <label style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; display: block;">Période (Du)</label>
              <input type="date" id="filter-date-debut" class="form-control" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600;">
            </div>

            <!-- Filtre Date Fin -->
            <div style="min-width: 150px;">
              <label style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; display: block;">Au</label>
              <input type="date" id="filter-date-fin" class="form-control" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600;">
            </div>

            <!-- Bouton Réinitialiser -->
            <div style="margin-top: 18px;">
              <button type="button" id="btn-reset-filters" class="btn btn-secondary" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;" title="Réinitialiser tous les filtres">
                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Réinitialiser
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- CARTES KPI DE STATISTIQUES FINANCIÈRES DES DÉPENSES -->
      <!-- ========================================================================= -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 24px;">
        
        <!-- Total Approuvé -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 22px; min-height: 105px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; box-sizing: border-box;">
          <div class="kpi-icon-wrapper" style="width: 50px; height: 50px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="check-circle" style="width: 25px; height: 25px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">Dépenses Approuvées</div>
            <div style="font-size: 19px; font-weight: 900; color: #15803D; margin-top: 4px;" id="kpi-montant-approuve"><?= number_format($stats['montant_approuve'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; font-weight: 700; color: #16A34A; margin-top: 3px;" id="kpi-count-approuve"><?= ($stats['count_approuve'] ?? 0) ?> dépense(s)</div>
          </div>
        </div>

        <!-- En Attente de Validation -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 22px; min-height: 105px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; box-sizing: border-box;">
          <div class="kpi-icon-wrapper" style="width: 50px; height: 50px; border-radius: 12px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 25px; height: 25px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">En Attente de Validation</div>
            <div style="font-size: 19px; font-weight: 900; color: #B45309; margin-top: 4px;" id="kpi-montant-attente"><?= number_format($stats['montant_en_attente'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; font-weight: 700; color: #D97706; margin-top: 3px;" id="kpi-count-attente"><?= ($stats['count_en_attente'] ?? 0) ?> dépense(s)</div>
          </div>
        </div>

        <!-- Dépenses Annulées -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 22px; min-height: 105px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; box-sizing: border-box;">
          <div class="kpi-icon-wrapper" style="width: 50px; height: 50px; border-radius: 12px; background: #F8FAFC; color: #64748B; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="x-circle" style="width: 25px; height: 25px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">Dépenses Annulées</div>
            <div style="font-size: 19px; font-weight: 900; color: #64748B; margin-top: 4px;" id="kpi-montant-annule"><?= number_format($stats['montant_annule'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; font-weight: 700; color: #EF4444; margin-top: 3px;" id="kpi-count-annule"><?= ($stats['count_annule'] ?? 0) ?> dépense(s)</div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- TABLEAU DES DÉPENSES DE FONCTIONNEMENT -->
      <!-- ========================================================================= -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Registre des Dépenses & Validation</h3>
            <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Seules les dépenses au statut <strong style="color: #B45309;">En attente</strong> peuvent être modifiées par l'utilisateur.</p>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-depenses" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 40px;">#</th>
                <th style="padding: 12px;">Code Dépense</th>
                <th style="padding: 12px;">Catégorie</th>
                <th style="padding: 12px; text-align: right;">Montant Engagé</th>
                <th style="padding: 12px;">Date Engagement</th>
                <th style="padding: 12px;">Auteur / Traitement</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : NOUVEAU / MODIFIER DÉCAISSEMENT / DÉPENSE -->
<!-- ========================================================================= -->
<div id="modal-depense" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 640px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-depense-title" style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Nouveau Décaissement / Dépense
      </h3>
      <button type="button" class="btn-close-modal-depense" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-depense-modal" enctype="multipart/form-data" style="padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_depense" id="depense_modal_id" value="">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
        
        <!-- Catégorie de Dépense -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Catégorie de dépense <span style="color: #EF4444;">*</span>
          </label>
          <select name="type_depense_code" id="depense_modal_type" required class="form-control select2" style="width: 100%;">
            <option value="">-- Sélectionnez une catégorie --</option>
            <?php foreach ($typeDepenses as $td): ?>
              <option value="<?= htmlspecialchars($td['code_type_depense']) ?>">
                <?= htmlspecialchars($td['libelle_type_depense']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Motif / Description -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Motif / Description de la dépense <span style="color: #EF4444;">*</span>
          </label>
          <input type="text" name="description_depense" id="depense_modal_description" required placeholder="Ex: Achat de fournitures de bureau, maintenance..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Montant engagé -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Montant engagé (FCFA) <span style="color: #EF4444;">*</span>
          </label>
          <input type="number" name="montant_depense" id="depense_modal_montant" required min="1" step="any" placeholder="Ex: 85000" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Mode de paiement -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Mode de règlement <span style="color: #EF4444;">*</span>
          </label>
          <select name="mode_reglement" id="depense_modal_mode" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="espece">Espèces (Caisse)</option>
            <option value="mobile_money">Mobile Money (Wave, Orange, MTN, Moov)</option>
            <option value="cheque">Chèque bancaire</option>
            <option value="virement">Virement bancaire</option>
          </select>
        </div>

        <!-- Bénéficiaire -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Bénéficiaire / Prestataire
          </label>
          <input type="text" name="beneficiaire" id="depense_modal_beneficiaire" placeholder="Ex: Librairie de France Abidjan" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Date d'engagement -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date d'engagement <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="periode_depense" id="depense_modal_date" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Document / Pièce Justificative -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Document / Pièce justificative <span style="font-size: 11.5px; color: #64748B; font-weight: 500;">(PDF, JPG, PNG - Max 3 Mo)</span>
          </label>
          <input type="file" name="piece_justificative" id="depense_modal_piece" accept=".pdf,.jpg,.jpeg,.png" class="form-control" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; background: #F8FAFC;">
          <div id="piece-justificative-preview" style="margin-top: 6px; font-size: 12.5px;"></div>
        </div>

      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-depense" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</button>
        <button type="submit" id="btn-save-depense-modal" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Dépense
        </button>
      </div>
    </form>
  </div>
</div>

<?php
$depensesListConfig = [
    'canValidate' => (bool)(!empty($canValidate)),
    'csrfToken' => Validator::generateCsrfToken()
];
?>
<script type="application/json" id="depenses-list-config">
<?= json_encode($depensesListConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>
<?php 
ViewContext::setScripts(['public/assets/js/modules/depenses.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>