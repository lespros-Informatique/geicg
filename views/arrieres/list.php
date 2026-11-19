<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$niveaux = $niveaux ?? [];
$stats = $stats ?? ['nb_etudiants' => 0, 'total_du' => 0, 'total_recouvre' => 0, 'total_solde' => 0, 'taux_recouvrement' => 0];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="history" style="width: 26px; height: 26px; color: #DC2626;"></i> Arriérés des Années Antérieures
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Suivi du recouvrement et apurement des scolarités restant dues des exercices précédents</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btn-refresh-data" class="btn btn-outline-secondary" style="font-weight: 700; border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Actualiser
          </button>
        </div>
      </div>

      <!-- Filtres Multi-Critères -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Année Origine de la Dette</label>
            <select id="filter-annee-origine" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années antérieures --</option>
              <?php foreach ($annees as $a): ?>
                <?php if ($a['code_annee'] !== $selectedAnneeCode): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>"><?= htmlspecialchars($a['libelle_annee']) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Classe Actuelle</label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Cartes KPI Syntheses Financieres -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Solde Arrière Dû -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FECACA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #991B1B; text-transform: uppercase;">Total Arriérés Dus</div>
            <div style="font-size: 20px; font-weight: 900; color: #991B1B; margin-top: 2px;" id="kpi-total-solde"><?= number_format($stats['total_solde'] ?? 0, 0, ',', ' ') ?> FCFA</div>
          </div>
        </div>

        <!-- Étudiants Débiteurs -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FED7AA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #C2410C; text-transform: uppercase;">Étudiants Débiteurs</div>
            <div style="font-size: 22px; font-weight: 900; color: #9A3412; margin-top: 2px;" id="kpi-nb-etudiants"><?= $stats['nb_etudiants'] ?? 0 ?></div>
          </div>
        </div>

        <!-- Total Recouvré -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase;">Total Recouvré</div>
            <div style="font-size: 20px; font-weight: 900; color: #14532D; margin-top: 2px;" id="kpi-total-recouvre"><?= number_format($stats['total_recouvre'] ?? 0, 0, ',', ' ') ?> FCFA</div>
          </div>
        </div>

        <!-- Taux de Recouvrement -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BAE6FD; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0F9FF; color: #0284C7; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0369A1; text-transform: uppercase;">Taux de Recouvrement</div>
            <div style="font-size: 22px; font-weight: 900; color: #075985; margin-top: 2px;" id="kpi-taux"><?= $stats['taux_recouvrement'] ?? 0 ?>%</div>
          </div>
        </div>

      </div>

      <!-- Registre DataTables des Arriérés -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-arrieres" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Étudiant</th>
                <th style="padding: 12px;">Matricule & Classe</th>
                <th style="padding: 12px;">Détails Année(s) d'Origine</th>
                <th style="padding: 12px; text-align: right;">Total Scolarité Initial</th>
                <th style="padding: 12px; text-align: right;">Total Réglé</th>
                <th style="padding: 12px; text-align: right;">Solde Arriéré Dû</th>
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
<!-- MODAL SUR MESURE : ENCAISSEMENT DES ARRIÉRÉS DE SCOLARITÉ                 -->
<!-- ========================================================================= -->
<div id="modal-encaisser-arriere" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: #15803D; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="banknote" style="width: 22px; height: 22px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Règlement d'Arriéré de Scolarité</h3>
          <div id="modal-student-info" style="font-size: 12px; color: #DCFCE7; margin-top: 2px;">Étudiant / Matricule</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-arriere" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <!-- Corps du modal (Formulaire) -->
    <form id="form-reglement-arriere" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
      <div style="padding: 24px; background: #F8FAFC; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
        
        <!-- Sélection de l'année / inscription à apurer -->
        <div>
          <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">1. Sélectionner l'inscription / année à apurer <span class="text-danger">*</span></label>
          <div id="container-inscriptions-arrieres" style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Rempli dynamiquement en JS -->
          </div>
        </div>

        <!-- Détails du versement -->
        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div style="grid-column: span 2;">
            <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">2. Montant Versé (FCFA) <span class="text-danger">*</span></label>
            <div style="position: relative;">
              <input type="number" step="1000" min="1000" id="input-montant-arriere" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 20px; color: #15803D; padding-right: 70px; border-radius: 10px; background-color: #F1F5F9; cursor: not-allowed;" placeholder="Ex: 50000" required readonly>
              <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #64748B; font-size: 14px;">FCFA</span>
            </div>
            <div id="help-solde-max" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">Solde restant disponible sur l'année sélectionnée : <strong id="lbl-solde-dispo">0 FCFA</strong></div>
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Mode de Règlement <span class="text-danger">*</span></label>
            <select name="mode_paiement" class="form-control" style="border-radius: 8px; font-weight: 700;" required>
              <option value="Espèces" selected>💵 Espèces</option>
              <option value="Chèque & Banque">🏦 Chèque & Banque</option>
              <option value="Mobile Money">📱 Mobile Money</option>
              <option value="Virement Bancaire">🏛️ Virement Bancaire</option>
            </select>
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">N° Référence / Chèque / Trans.</label>
            <input type="text" name="reference_paiement" class="form-control" style="border-radius: 8px;" placeholder="Ex: CHQ-8849 / TRX-9921">
          </div>

          <div style="grid-column: span 2;">
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Observations / Notes de caisse</label>
            <input type="text" name="observations" class="form-control" style="border-radius: 8px;" placeholder="Remarques facultatives...">
          </div>
        </div>

      </div>

      <!-- Pied de modal -->
      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn btn-secondary btn-close-modal-arriere" style="font-weight: 700; border-radius: 8px; padding: 9px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-reglement-arriere" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 9px 24px; background: #15803D; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
          <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : REÇU DE RÈGLEMENT D'ARRIÉRÉ SCOLARITÉ                   -->
<!-- ========================================================================= -->
<div id="modal-recu-arriere" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 650px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
    
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 20px; height: 20px;"></i> Reçu de Règlement d'Arriéré
      </h3>
      <button type="button" class="btn-close-modal-recu" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div id="printable-recu-arriere" style="padding: 24px; background: #FFFFFF; overflow-y: auto; flex: 1; font-family: sans-serif;">
      <!-- Rempli dynamiquement en JS -->
    </div>

    <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
      <button type="button" class="btn btn-secondary btn-close-modal-recu" style="font-weight: 700; border-radius: 8px; padding: 9px 20px;">Fermer</button>
      <button type="button" id="btn-print-recu-action" class="btn btn-primary" style="font-weight: 800; border-radius: 8px; padding: 9px 22px; background: #1E3A5F; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu
      </button>
    </div>

  </div>
</div>

<?php ViewContext::setScripts(['public/assets/js/modules/paiements.js']); ?>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
