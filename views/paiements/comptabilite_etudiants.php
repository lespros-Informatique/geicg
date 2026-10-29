<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$isCaisseOuverte = $isCaisseOuverte ?? false;
$activeSession = $activeSession ?? null;
$encryptedSessionId = $encryptedSessionId ?? '';
$canRecord = $canRecord ?? false;
$canOpenCaisse = $canOpenCaisse ?? false;
$canCloseCaisse = $canCloseCaisse ?? false;
$annees = $annees ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? '';
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
?>


  <!-- BANNIÈRE D'IMPRESSION  -->
  <div id="print-header-banner">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 15px;">
      <div>
        <h2 style="margin:0; font-size: 18px; font-weight: 900; text-transform: uppercase;">GROUPE ÉCOLE D'INGÉNIEURS ET DE GESTION (GEICG)</h2>
        <p style="margin: 3px 0 0 0; font-size: 12px; color: #333;">Service de la Comptabilité & Direction Financière</p>
      </div>
      <div style="text-align: right;">
        <h3 style="margin:0; font-size: 16px; font-weight: 800;">SUIVI COMPTABLE DES ÉTUDIANTS</h3>
        <p style="margin: 3px 0 0 0; font-size: 11px; color: #555;">Année Académique: <strong id="print-annee-label"><?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'En Cours') ?></strong></p>
        <p style="margin: 1px 0 0 0; font-size: 10px; color: #666;">Édité le: <?= date('d/m/Y H:i') ?></p>
      </div>
    </div>
  </div>

  <!-- HEADER DE PAGE ACCUEIL -->
  <div class="page-header no-print" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
      <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); display: flex; align-items: center; justify-content: center; color: #FFF; box-shadow: 0 4px 10px rgba(30,58,95,0.25);">
          <i data-lucide="users" style="width: 22px; height: 22px;"></i>
        </div>
        <div>
          <h1 style="font-size: 22px; font-weight: 900; color: #0F172A; margin: 0; letter-spacing: -0.5px;">Comptabilité & Suivi Financier des Étudiants</h1>
          <p style="font-size: 13px; color: #64748B; margin: 0;">Registre comptable détaillé, suivi des créances et scolarités par étudiant.</p>
        </div>
      </div>
    </div>

    <!-- ACTIONS HAUT DROITE -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
      
      <!-- Session Caisse Status -->
      <div style="background: #FFFFFF; border: 1px solid #E2E8F0; padding: 6px 14px; border-radius: 10px; display: flex; align-items: center; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="width: 10px; height: 10px; border-radius: 50%; background: <?= $isCaisseOuverte ? '#16A34A' : '#DC2626' ?>; box-shadow: 0 0 0 3px <?= $isCaisseOuverte ? 'rgba(22,163,74,0.2)' : 'rgba(220,38,38,0.2)' ?>;"></div>
        <div style="line-height: 1.2;">
          <div style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Caisse du Jour</div>
          <div style="font-size: 12px; font-weight: 800; color: #0F172A;">
            <?= $isCaisseOuverte ? 'Ouverte (' . date('d/m/Y') . ')' : 'Fermée / Inactive' ?>
          </div>
        </div>
      </div>

      <button type="button" onclick="printEtatComptabilite()" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 700; font-size: 13px; padding: 9px 16px; border: 1px solid #CBD5E1; background: #FFF; color: #334155; display: inline-flex; align-items: center; gap: 8px;" title="Imprimer l'état comptable filtré en PDF">
        <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer l'État
      </button>



    </div>
  </div>

  <!-- BARRE DE FILTRES AVANCÉS -->
  <div class="card card-filters no-print" style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 18px 20px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <h3 style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="filter" style="width: 16px; height: 16px; color: #3B82F6;"></i> Filtres Comptables & Critères de Recherche
      </h3>
      <button type="button" onclick="resetComptaFilters()" style="background: none; border: none; font-size: 12px; font-weight: 700; color: #64748B; cursor: pointer; display: flex; align-items: center; gap: 4px;">
        <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Réinitialiser
      </button>
    </div>

    <form id="form-compta-filter" onsubmit="event.preventDefault(); applyComptaFilters();">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; align-items: end;">
        
        <!-- Année Académique -->
        <div>
          <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px; display: block;">Année Académique</label>
          <select id="filter_annee_code" name="annee_code" class="form-control" style="border-radius: 8px; font-weight: 700; font-size: 13px;" onchange="applyComptaFilters()">
            <?php foreach ($annees as $a): ?>
              <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($a['code_annee'] === $selectedAnneeCode) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['libelle_annee']) ?> <?= (($a['statut_annee'] ?? '') === 'actif') ? ' (Active)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Niveau -->
        <div>
          <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px; display: block;">Niveau d'Études</label>
          <select id="filter_niveau_code" name="niveau_code" class="form-control" style="border-radius: 8px; font-weight: 600; font-size: 13px;" onchange="applyComptaFilters()">
            <option value="ALL">Tous les niveaux</option>
            <?php foreach ($niveaux as $n): ?>
              <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Classe -->
        <div>
          <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px; display: block;">Classe / Filière</label>
          <select id="filter_classe_code" name="classe_code" class="form-control" style="border-radius: 8px; font-weight: 600; font-size: 13px;" onchange="applyComptaFilters()">
            <option value="ALL">Toutes les classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= htmlspecialchars($c['code_classe']) ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Régime -->
        <div>
          <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px; display: block;">Régime Étudiant</label>
          <select id="filter_regime" name="regime" class="form-control" style="border-radius: 8px; font-weight: 600; font-size: 13px;" onchange="applyComptaFilters()">
            <option value="ALL">Tous les régimes</option>
            <option value="affecte">Affecté de l'État</option>
            <option value="non_affecte">Privé / Non-Affecté</option>
          </select>
        </div>

        <!-- Statut de Règlement -->
        <div>
          <label style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px; display: block;">Statut Règlement</label>
          <select id="filter_statut_paiement" name="statut_paiement" class="form-control" style="border-radius: 8px; font-weight: 700; font-size: 13px;" onchange="applyComptaFilters()">
            <option value="ALL">Tous les statuts</option>
            <option value="solde">Soldé (Totalement Réglé)</option>
            <option value="partiel">Acompte Payé / Partiel</option>
            <option value="non_paye">Non Réglé (0 FCFA)</option>
          </select>
        </div>

      </div>
    </form>
  </div>

  <!-- INDICATEURS COMPTABLES (KPI BAR) -->
  <div class="card-kpi-container no-print" style="margin-bottom: 24px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">

      <!-- KPI 1 : Effectif Total Inscrit -->
      <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants Inscrits</div>
            <div id="kpi-total-etudiants" style="font-size: 24px; font-weight: 900; color: #0F172A; margin-top: 4px;">0</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: #475569;">
            <i data-lucide="users" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #64748B; margin-top: 8px;">Comptes actifs filtrés</div>
      </div>

      <!-- KPI 2 : Total Scolarités Dues -->
      <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Scolarités Dues</div>
            <div id="kpi-total-scolarites" style="font-size: 20px; font-weight: 900; color: #1E3A5F; margin-top: 4px;">0 FCFA</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #E0F2FE; display: flex; align-items: center; justify-content: center; color: #0284C7;">
            <i data-lucide="receipt" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #64748B; margin-top: 8px;">Total scolarités théoriques</div>
      </div>

      <!-- KPI 3 : Taux de Recouvrement Global -->
      <div class="kpi-card" style="border-left: 4px solid #3B82F6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.5px;">Taux de Recouvrement</div>
            <div id="kpi-taux-recouvrement" style="font-size: 20px; font-weight: 900; color: #1E3A5F; margin-top: 4px;">0 %</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #DBEAFE; display: flex; align-items: center; justify-content: center; color: #2563EB;">
            <i data-lucide="percent" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #64748B; margin-top: 8px;">Progression des encaissements</div>
      </div>

      <!-- KPI 5 : Total Encaissé -->
      <div class="kpi-card" style="border-left: 4px solid #16A34A; background: #F0FDF4;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Total Encaissé (Paiements)</div>
            <div id="kpi-total-encaisse" style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 4px;">0 FCFA</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #DCFCE7; display: flex; align-items: center; justify-content: center; color: #16A34A;">
            <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #16A34A; margin-top: 8px; font-weight: 700;">Somme reçue en caisse</div>
      </div>

      <!-- KPI 6 : Reste à Recouvrer -->
      <div class="kpi-card" style="border-left: 4px solid #DC2626; background: #FEF2F2;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #B91C1C; text-transform: uppercase; letter-spacing: 0.5px;">Solde Restant (Créances)</div>
            <div id="kpi-reste-recouvrer" style="font-size: 20px; font-weight: 900; color: #DC2626; margin-top: 4px;">0 FCFA</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #FEE2E2; display: flex; align-items: center; justify-content: center; color: #DC2626;">
            <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #B91C1C; margin-top: 8px; font-weight: 700;">Créances dues non réglées</div>
      </div>

    </div>
  </div>

  <!-- TABLEAU DES COMPTES ÉTUDIANTS -->
  <div class="card" style="background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;" class="no-print">
      <div>
        <h2 style="font-size: 16px; font-weight: 900; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="table" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Relevé Comptable Individuel des Étudiants
        </h2>
        <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Montants dus, règlements encaissés, soldes et taux de règlement en temps réel.</p>
      </div>
      <div>
        <span id="compta-records-count" class="badge-status badge-info" style="font-size: 12px;">Chargement...</span>
      </div>
    </div>

    <div class="table-responsive">
      <table id="table-comptabilite-etudiants" class="table table-hover align-middle" style="width: 100%;">
        <thead>
          <tr style="background: #F8FAFC; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
            <th style="padding: 12px; font-weight: 800;">Étudiant & Matricule</th>
            <th style="padding: 12px; font-weight: 800;">Classe / Niveau</th>
            <th style="padding: 12px; font-weight: 800; text-align: center;">Régime</th>
            <th style="padding: 12px; font-weight: 800; text-align: right;">Scolarité Due</th>
            <th style="padding: 12px; font-weight: 800; text-align: right;">Total Encaissé</th>
            <th style="padding: 12px; font-weight: 800; text-align: right;">Solde Dû</th>
            <th style="padding: 12px; font-weight: 800; text-align: center;">Taux %</th>
            <th style="padding: 12px; font-weight: 800; text-align: center;">Statut</th>
            <th style="padding: 12px; font-weight: 800; text-align: center;" class="no-print">Actions</th>
          </tr>
        </thead>
        <tbody style="font-size: 13px;">
          <!-- Chargé dynamiquement en AJAX -->
        </tbody>
      </table>
    </div>

  </div>

  <!-- BLOC DE SIGNATURES IMPRESSION -->
  <div class="print-signatures-block" style="margin-top: 40px; page-break-inside: avoid;">
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; font-size: 11px;">
      <div>
        <p style="font-weight: 800; text-transform: uppercase; margin-bottom: 50px;">L'Agent Comptable</p>
        <p style="border-top: 1px dashed #94A3B8; padding-top: 4px; font-style: italic;">Nom & Signature</p>
      </div>
      <div>
        <p style="font-weight: 800; text-transform: uppercase; margin-bottom: 50px;">Le Chef de Caisse</p>
        <p style="border-top: 1px dashed #94A3B8; padding-top: 4px; font-style: italic;">Nom & Signature</p>
      </div>
      <div>
        <p style="font-weight: 800; text-transform: uppercase; margin-bottom: 50px;">Le Directeur Général</p>
        <p style="border-top: 1px dashed #94A3B8; padding-top: 4px; font-style: italic;">Visa & Cachet</p>
      </div>
    </div>
  </div>

</div>

<!-- ========================================================================= -->
<!-- INCLUSION DU MODAL RÉCAPITULATIF FINANCIER ÉTUDIANT                      -->
<!-- ========================================================================= -->
<div id="modal-student-financial-summary" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 10005; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 820px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; animation: modalZoomIn 0.25s ease-out; max-height: 90vh; display: flex; flex-direction: column;">
    
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <img id="sum-student-photo" src="" class="student-avatar" style="width: 44px; height: 44px; border-color: #64748B;" alt="Photo">
        <div>
          <h3 id="sum-student-name" style="font-size: 17px; font-weight: 900; margin: 0; letter-spacing: -0.3px;">RÉSUMÉ FINANCIER ÉTUDIANT</h3>
          <p id="sum-student-details" style="font-size: 12px; color: #94A3B8; margin: 2px 0 0 0;">Matricule: - | Classe: -</p>
        </div>
      </div>
      <button type="button" onclick="closeStudentSummaryModal()" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer;">&times;</button>
    </div>

    <div style="padding: 24px; overflow-y: auto; flex: 1;">
      
      <!-- Cartes synthétiques -->
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px;">
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px; border-radius: 10px; text-align: center;">
          <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Scolarité Due</div>
          <div id="sum-val-scolarite" style="font-size: 15px; font-weight: 900; color: #1E3A5F; margin-top: 2px;">0 FCFA</div>
        </div>
        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; padding: 12px; border-radius: 10px; text-align: center;">
          <div style="font-size: 10.5px; font-weight: 800; color: #15803D; text-transform: uppercase;">Total Encaissé</div>
          <div id="sum-val-paye" style="font-size: 15px; font-weight: 900; color: #15803D; margin-top: 2px;">0 FCFA</div>
        </div>
        <div style="background: #FEF2F2; border: 1px solid #FECACA; padding: 12px; border-radius: 10px; text-align: center;">
          <div style="font-size: 10.5px; font-weight: 800; color: #DC2626; text-transform: uppercase;">Solde Restant Dû</div>
          <div id="sum-val-reste" style="font-size: 15px; font-weight: 900; color: #DC2626; margin-top: 2px;">0 FCFA</div>
        </div>
      </div>

      <!-- Échéancier des tranches -->
      <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0 0 10px 0;">Échéancier & Ventilation des Tranches</h4>
      <div class="table-responsive" style="margin-bottom: 20px;">
        <table class="table table-bordered table-sm align-middle" style="font-size: 12px;">
          <thead style="background: #F1F5F9; color: #475569;">
            <tr>
              <th>Libellé Tranche</th>
              <th style="text-align: right;">Montant Exigible</th>
              <th style="text-align: right;">Montant Payé</th>
              <th style="text-align: right;">Reste à Régler</th>
              <th style="text-align: center;">Statut</th>
            </tr>
          </thead>
          <tbody id="sum-tbody-tranches">
            <!-- Rempli en JS -->
          </tbody>
        </table>
      </div>

      <!-- Historique des Versements -->
      <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin: 0 0 10px 0;">Historique des Règlements & Quittances</h4>
      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle" style="font-size: 12px;">
          <thead style="background: #F1F5F9; color: #475569;">
            <tr>
              <th>Date</th>
              <th>N° Quittance</th>
              <th>Affectation / Type</th>
              <th>Mode</th>
              <th style="text-align: right;">Montant</th>
              <th style="text-align: center;">Action</th>
            </tr>
          </thead>
          <tbody id="sum-tbody-paiements">
            <!-- Rempli en JS -->
          </tbody>
        </table>
      </div>

    </div>

    <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 24px; display: flex; justify-content: flex-end; gap: 10px;">
      <button type="button" onclick="closeStudentSummaryModal()" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; font-size: 13px;">
        Fermer
      </button>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- INCLUSION DU MODAL ENCAISSER VERSEMENT (SI AUTORISÉ)                      -->
<!-- ========================================================================= -->
<?php if ($canRecord): ?>
<div id="modal-encaisse-versement" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.7); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 680px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; animation: modalZoomIn 0.25s ease-out; max-height: 92vh; display: flex; flex-direction: column;">
    
    <div style="background: linear-gradient(135deg, #16A34A 0%, #15803D 100%); color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <i data-lucide="credit-card" style="width: 22px; height: 22px;"></i>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0;">Encaisser un Versement de Scolarité</h3>
      </div>
      <button type="button" class="btn-close-modal-encaissement" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer;">&times;</button>
    </div>

    <form id="form-encaisse-versement" action="<?= RACINE ?>paiement/add" method="POST" style="display: flex; flex-direction: column; overflow: hidden; height: 100%;">
      <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">

        <input type="hidden" id="modal_select_inscription" name="inscription_code" value="" required>

        <div id="encaisse-student-summary-card" style="display: none; background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
            <img id="encaisse-photo-preview" src="<?= RACINE ?>public/assets/images/default-avatar.png" class="student-avatar" alt="Avatar">
            <div>
              <div id="encaisse-student-name" style="font-size: 15px; font-weight: 900; color: #0F172A;">Nom Étudiant</div>
              <div id="encaisse-student-meta" style="font-size: 12px; color: #64748B;">Matricule: - | Classe: -</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; text-align: center;">
            <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px;">
              <div style="font-size: 10px; font-weight: 700; color: #64748B;">Scolarité Due</div>
              <div id="encaisse-val-scolarite" style="font-size: 14px; font-weight: 900; color: #1E3A5F;">0 FCFA</div>
            </div>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 8px;">
              <div style="font-size: 10px; font-weight: 700; color: #15803D;">Total Payé</div>
              <div id="encaisse-val-paye" style="font-size: 14px; font-weight: 900; color: #15803D;">0 FCFA</div>
            </div>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 8px;">
              <div style="font-size: 10px; font-weight: 700; color: #DC2626;">Reste à Payer</div>
              <div id="encaisse-val-reste" style="font-size: 14px; font-weight: 900; color: #DC2626;">0 FCFA</div>
            </div>
          </div>
        </div>

        <input type="hidden" id="hidden_montant_tranche" name="montant_tranche" value="0">

        <div id="encaisse-payment-inputs-section" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 16px; border: 1px solid #E2E8F0;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            
            <div style="grid-column: span 2;">
              <label style="font-size: 11.5px; font-weight: 800; color: #0F172A; margin-bottom: 4px; display: block;">1. Tranche / Échéance à régler <span class="text-danger">*</span></label>
              <div style="position: relative;">
                <select id="modal_select_tranche" name="tranche_code" class="form-control" style="font-weight: 800; font-size: 13px; color: #1E3A5F; background: #FFFFFF;" required>
                </select>
              </div>
              <div id="tranche-hint-info" style="font-size: 11px; color: #64748B; margin-top: 3px;"></div>
            </div>

            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 4px; display: block;">2. Montant Total Versé (FCFA) <span class="text-danger">*</span></label>
              <div style="position: relative;">
                <input type="number" step="1" id="input-montant-versement" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 18px; color: #15803D; background-color: #F0FDF4; border: 2px solid #86EFAC; padding-right: 70px; cursor: not-allowed;" placeholder="0" readonly required>
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #15803D; font-size: 13px;">FCFA</span>
              </div>
              <div id="montant-max-hint" style="font-size: 11px; color: #64748B; margin-top: 4px;">
                Montant calculé automatiquement selon la tranche sélectionnée.
              </div>
            </div>

            <div>
              <label style="font-size: 11.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Mode de Règlement</label>
              <select id="modal_select_mode" name="mode_paiement" class="form-control" style="font-size: 13px; font-weight: 700;" required>
                <option value="espece" selected>💵 Espèces</option>
                <option value="mobile_money">📱 Mobile Money</option>
                <option value="cheque">🏦 Chèque</option>
                <option value="virement">🏛️ Virement</option>
              </select>
            </div>

            <div>
              <label style="font-size: 11.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Référence Transaction</label>
              <input type="text" name="reference_paiement" id="input_ref_paiement" class="form-control" placeholder="Réf. / N° Chèque">
            </div>

          </div>
        </div>

      </div>

      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
        <div id="caisse-status-footer-note" style="<?= empty($isCaisseOuverte) ? 'display: inline-flex;' : 'display: none;' ?> align-items: center; gap: 6px; font-size: 11.5px; color: #B45309; background: #FFFBEB; border: 1px solid #FDE68A; padding: 5px 10px; border-radius: 6px;">
          <i data-lucide="lock" style="width: 14px; height: 14px; color: #D97706; flex-shrink: 0;"></i>
          <span>Session de caisse fermée &bull; Ouvrez la caisse du jour pour encaisser en espèces</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-left: auto;">
          <button type="button" class="btn btn-secondary btn-close-modal-encaissement" style="font-weight: 700; border-radius: 8px; padding: 8px 16px;">
            Annuler
          </button>
          <button type="submit" id="btn-submit-encaissement" class="btn btn-success" 
            <?= empty($isCaisseOuverte) ? 'disabled style="font-weight: 800; border-radius: 8px; padding: 8px 20px; background: #94A3B8; border: none; color: #FFF; cursor: not-allowed; opacity: 0.65;" title="Session de caisse fermée : veuillez d\'abord ouvrir la caisse du jour."' : 'style="font-weight: 800; border-radius: 8px; padding: 8px 20px; background: #16A34A; border: none; color: #FFF; cursor: pointer;"' ?>>
            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Valider & Encaisser
          </button>
        </div>
      </div>
    </form>

  </div>
</div>
<?php endif; ?>

<!-- SCRIPTS DE GESTION DU TABLEAU ET AJAX -->
<?php
$comptaConfig = [
    'canRecord' => (bool)(!empty($canRecord)),
    'isCaisseOuverte' => (bool)(!empty($isCaisseOuverte)),
    'racine' => RACINE
];
?>
<script type="application/json" id="paiements-compta-config">
<?= json_encode($comptaConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>
<?php 
$pageScripts = ['public/assets/js/modules/paiements_compta.js'];
require_once __DIR__ . '/../../public/inc/footer.php'; 
?>