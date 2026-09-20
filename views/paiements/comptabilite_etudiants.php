<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<style>
@media print {
  @page {
    size: A4 landscape;
    margin: 8mm;
  }
  body, html, .app-layout, .main-content, .content-wrapper {
    background: #FFFFFF !important;
    color: #000000 !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-shadow: none !important;
  }
  header, .topbar, .topbar *, .sidebar, .sidebar *, aside, nav, .main-nav, .page-header, .no-print, 
  .card-filters, .card-kpi-container, .kpi-section-title,
  .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate,
  button, a.btn, input, select, textarea, .form-control, .select2, .select2-container {
    display: none !important;
  }
  #table-comptabilite-etudiants th:last-child,
  #table-comptabilite-etudiants td:last-child {
    display: none !important;
  }
  .card {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  table#table-comptabilite-etudiants {
    width: 100% !important;
    border-collapse: collapse !important;
    margin-top: 10px !important;
  }
  table#table-comptabilite-etudiants th,
  table#table-comptabilite-etudiants td {
    border: 1px solid #334155 !important;
    padding: 6px 8px !important;
    font-size: 11px !important;
    color: #000000 !important;
  }
  table#table-comptabilite-etudiants th {
    background: #F1F5F9 !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
  }
  table#table-comptabilite-etudiants tr {
    page-break-inside: avoid !important;
  }
  #print-header-banner, .print-signatures-block {
    display: block !important;
  }
}

#print-header-banner, .print-signatures-block {
  display: none;
}

/* Animations and Premium Layout */
@keyframes kpiEntrance {
  0% { opacity: 0; transform: translateY(18px) scale(0.98); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes btnPulseGlow {
  0%, 100% { box-shadow: 0 4px 14px rgba(22, 163, 74, 0.25); transform: translateY(0); }
  50% { box-shadow: 0 6px 22px rgba(22, 163, 74, 0.45); transform: translateY(-1.5px); }
}

@keyframes modalZoomIn {
  from { opacity: 0; transform: scale(0.95) translateY(-8px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.kpi-card {
  animation: kpiEntrance 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  transition: all 0.25s ease;
  border-radius: 12px;
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  padding: 18px 20px;
}
.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px -5px rgba(0,0,0,0.08);
  border-color: #CBD5E1;
}

.badge-status {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  text-transform: uppercase;
}
.badge-success { background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }
.badge-warning { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
.badge-danger { background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; }
.badge-info { background: #E0F2FE; color: #0369A1; border: 1px solid #7DD3FC; }
.badge-secondary { background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }

.student-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #E2E8F0;
}
</style>

<div class="content-wrapper">

  <!-- BANNIÈRE D'IMPRESSION OFFICIELLE -->
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
          <p style="font-size: 13px; color: #64748B; margin: 0;">Registre comptable détaillé, suivi des créances, scolarités et frais annexes par étudiant.</p>
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

      <button type="button" onclick="window.print()" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 700; font-size: 13px; padding: 9px 16px; border: 1px solid #CBD5E1; background: #FFF; color: #334155; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer l'État
      </button>

      <?php if ($canRecord && $isCaisseOuverte): ?>
      <button type="button" onclick="openModalEncaissementCompta()" class="btn btn-success" style="border-radius: 10px; font-weight: 800; font-size: 13px; padding: 9px 18px; background: #16A34A; border: none; color: #FFF; display: inline-flex; align-items: center; gap: 8px; animation: btnPulseGlow 3s infinite;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Encaisser un Versement
      </button>
      <?php endif; ?>

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

      <!-- KPI 3 : Total Frais Annexes Dus -->
      <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Frais Annexes Dus</div>
            <div id="kpi-total-frais-annexes" style="font-size: 20px; font-weight: 900; color: #B45309; margin-top: 4px;">0 FCFA</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #FEF3C7; display: flex; align-items: center; justify-content: center; color: #D97706;">
            <i data-lucide="package-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #64748B; margin-top: 8px;">Kits & Uniformes obligatoires</div>
      </div>

      <!-- KPI 4 : Total Attendu (Scolarités + Annexes) -->
      <div class="kpi-card" style="border-left: 4px solid #3B82F6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.5px;">Total Général Attendu</div>
            <div id="kpi-total-attendu" style="font-size: 20px; font-weight: 900; color: #1E3A5F; margin-top: 4px;">0 FCFA</div>
          </div>
          <div style="width: 38px; height: 38px; border-radius: 10px; background: #DBEAFE; display: flex; align-items: center; justify-content: center; color: #2563EB;">
            <i data-lucide="calculator" style="width: 20px; height: 20px;"></i>
          </div>
        </div>
        <div style="font-size: 11.5px; color: #64748B; margin-top: 8px;">Scolarité + Frais Annexes</div>
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
            <th style="padding: 12px; font-weight: 800; text-align: right;">Frais Annexes</th>
            <th style="padding: 12px; font-weight: 800; text-align: right;">Total Attendu</th>
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
        <p style="border-top: 1px dashed #94A3B8; padding-top: 4px; font-style: italic;">Visa & Cachet Officiel</p>
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
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px; border-radius: 10px; text-align: center;">
          <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase;">Scolarité Due</div>
          <div id="sum-val-scolarite" style="font-size: 15px; font-weight: 900; color: #1E3A5F; margin-top: 2px;">0 FCFA</div>
        </div>
        <div style="background: #FFFBEB; border: 1px solid #FDE68A; padding: 12px; border-radius: 10px; text-align: center;">
          <div style="font-size: 10.5px; font-weight: 800; color: #B45309; text-transform: uppercase;">Frais Annexes</div>
          <div id="sum-val-frais-annexes" style="font-size: 15px; font-weight: 900; color: #B45309; margin-top: 2px;">0 FCFA</div>
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

        <div style="margin-bottom: 16px;">
          <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">
            1. Sélectionner l'Étudiant Inscrit <span class="text-danger">*</span>
          </label>
          <select id="modal_select_inscription" name="inscription_code" class="form-control" style="width: 100%; font-size: 14px;" required>
            <option value="">-- Choisir un étudiant dans la liste --</option>
            <?php foreach ($inscriptions as $ins): ?>
              <option value="<?= htmlspecialchars($ins['code_inscription']) ?>">
                <?= htmlspecialchars(strtoupper($ins['nom_etudiant']) . ' ' . ucwords(strtolower($ins['prenom_etudiant']))) ?> 
                (<?= htmlspecialchars($ins['matricule_etudiant']) ?> - <?= htmlspecialchars($ins['libelle_classe'] ?? 'Classe N/A') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div id="encaisse-student-summary-card" style="display: none; background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
            <img id="encaisse-photo-preview" src="<?= RACINE ?>public/assets/images/default-avatar.png" class="student-avatar" alt="Avatar">
            <div>
              <div id="encaisse-student-name" style="font-size: 15px; font-weight: 900; color: #0F172A;">Nom Étudiant</div>
              <div id="encaisse-student-meta" style="font-size: 12px; color: #64748B;">Matricule: - | Classe: -</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center;">
            <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px;">
              <div style="font-size: 10px; font-weight: 700; color: #64748B;">Scolarité Due</div>
              <div id="encaisse-val-scolarite" style="font-size: 14px; font-weight: 900; color: #1E3A5F;">0 FCFA</div>
            </div>
            <div id="encaisse-card-frais-annexes" style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 8px;">
              <div style="font-size: 10px; font-weight: 700; color: #B45309;" id="encaisse-lbl-frais-annexes">Frais Annexes</div>
              <div id="encaisse-val-frais-annexes" style="font-size: 14px; font-weight: 900; color: #B45309;">0 FCFA</div>
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
        <input type="hidden" id="hidden_montant_frais_annexes" name="montant_frais_annexes" value="0">

        <div id="encaisse-payment-inputs-section" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 16px; border: 1px solid #E2E8F0;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            
            <div style="grid-column: span 2;">
              <label style="font-size: 11.5px; font-weight: 800; color: #0F172A; margin-bottom: 4px; display: block;">2. Tranche / Échéance à régler</label>
              <div style="position: relative;">
                <select id="modal_select_tranche" name="tranche_code" class="form-control" style="font-weight: 800; font-size: 13px; color: #1E3A5F; background: #F8FAFC; pointer-events: none; cursor: not-allowed;" tabindex="-1" required>
                </select>
              </div>
              <div id="tranche-hint-info" style="font-size: 11px; color: #64748B; margin-top: 3px;"></div>
            </div>

            <div id="container-frais-annexes" style="display: none; grid-column: span 2;">
              <label style="font-size: 11px; font-weight: 700; color: #B45309; margin-bottom: 4px; display: block;">Frais d'Annexe Exigibles</label>
              <input type="text" id="input-montant-frais-annexes" class="form-control" style="font-weight: 800; font-size: 13px; color: #B45309; background: #FFFBEB;" value="0 FCFA" readonly>
            </div>

            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 4px; display: block;">3. Montant Total Versé (FCFA) <span class="text-danger">*</span></label>
              <div style="position: relative;">
                <input type="number" step="1" id="input-montant-versement" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 18px; color: #15803D; background: #F0FDF4; border: 2px solid #86EFAC;" readonly required>
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

      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 14px 24px; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-close-modal-encaissement" style="font-weight: 700; border-radius: 8px; padding: 8px 16px;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-encaissement" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 8px 20px; background: #16A34A; border: none; color: #FFF;">
          <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Valider & Encaisser
        </button>
      </div>
    </form>

  </div>
</div>
<?php endif; ?>

<!-- SCRIPTS DE GESTION DU TABLEAU ET AJAX -->
<script>
let dataTableCompta = null;

document.addEventListener('DOMContentLoaded', function() {
    initDataTableCompta();
    loadComptaData();

    // Event handlers close modals
    document.querySelectorAll('.btn-close-modal-encaissement').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modal-encaisse-versement').style.display = 'none';
        });
    });

    // Handle form encaissement submit
    const formEncaisse = document.getElementById('form-encaisse-versement');
    if (formEncaisse) {
        formEncaisse.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEncaissementForm(this);
        });
    }

    // Handle inscription change in modal
    const selInscr = document.getElementById('modal_select_inscription');
    if (selInscr) {
        selInscr.addEventListener('change', function() {
            onInscriptionSelected(this.value);
        });
    }
});

function initDataTableCompta() {
    if ($.fn.DataTable.isDataTable('#table-comptabilite-etudiants')) {
        $('#table-comptabilite-etudiants').DataTable().destroy();
    }

    dataTableCompta = $('#table-comptabilite-etudiants').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        pageLength: 25,
        order: [[0, 'asc']],
        dom: '<"row no-print"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row no-print"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columns: [
            { data: null, render: function(d) {
                return `<div style="display: flex; align-items: center; gap: 10px;">
                            <img src="${d.photo}" class="student-avatar" alt="Photo">
                            <div>
                                <div style="font-weight: 800; color: #0F172A;">${d.nom_complet}</div>
                                <div style="font-size: 11.5px; color: #64748B;">Matricule: <strong style="color: #1E3A5F;">${d.matricule}</strong></div>
                            </div>
                        </div>`;
            }},
            { data: null, render: function(d) {
                return `<div>
                            <div style="font-weight: 700; color: #1E3A5F;">${d.classe}</div>
                            <div style="font-size: 11px; color: #64748B;">Niveau: ${d.niveau}</div>
                        </div>`;
            }},
            { data: null, className: 'text-center', render: function(d) {
                const bCls = (d.regime_code === 'affecte') ? 'badge-info' : 'badge-secondary';
                return `<span class="badge-status ${bCls}">${d.regime}</span>`;
            }},
            { data: 'scolarite_due', className: 'text-end font-monospace', render: function(v) { return formatFCFA(v); }},
            { data: 'frais_annexes_dus', className: 'text-end font-monospace', render: function(v) { return formatFCFA(v); }},
            { data: 'total_attendu', className: 'text-end font-monospace fw-bold', render: function(v) { return formatFCFA(v); }},
            { data: 'total_encaisse', className: 'text-end font-monospace text-success fw-bold', render: function(v) { return formatFCFA(v); }},
            { data: 'solde_restant', className: 'text-end font-monospace text-danger fw-bold', render: function(v) { return formatFCFA(v); }},
            { data: null, className: 'text-center', render: function(d) {
                let barColor = (d.taux_reglement >= 100) ? '#16A34A' : ((d.taux_reglement > 0) ? '#D97706' : '#DC2626');
                return `<div style="min-width: 80px;">
                            <div style="font-size: 11px; font-weight: 800; margin-bottom: 2px;">${d.taux_reglement}%</div>
                            <div style="width: 100%; height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden;">
                                <div style="width: ${d.taux_reglement}%; height: 100%; background: ${barColor};"></div>
                            </div>
                        </div>`;
            }},
            { data: null, className: 'text-center', render: function(d) {
                return `<span class="badge-status ${d.badge_class}">${d.statut_libelle}</span>`;
            }},
            { data: null, className: 'text-center no-print', render: function(d) {
                let btnEncaisse = '';
                const canRecord = <?= $canRecord ? 'true' : 'false' ?>;
                const isCaisseOuverte = <?= $isCaisseOuverte ? 'true' : 'false' ?>;

                if (canRecord && isCaisseOuverte) {
                    btnEncaisse = `<button type="button" onclick="openModalPayementForStudent('${d.code_inscription}')" 
                                           class="btn btn-sm btn-success me-1" 
                                           style="border-radius: 8px; font-weight: 800; font-size: 12px; padding: 5px 12px; background: #16A34A; border: none; color: #FFF; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(22,163,74,0.25);" 
                                           title="Encaisser un versement de scolarité">
                                    <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> Versement
                                   </button>`;
                } else if (canRecord && !isCaisseOuverte) {
                    btnEncaisse = `<button type="button" onclick="alert('La caisse du jour est actuellement fermée. Veuillez d\'abord ouvrir une session de caisse.')" 
                                           class="btn btn-sm btn-secondary me-1" 
                                           style="border-radius: 8px; font-weight: 800; font-size: 12px; padding: 5px 12px; opacity: 0.7; display: inline-flex; align-items: center; gap: 5px;" 
                                           title="Caisse du jour fermée">
                                    <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> Versement
                                   </button>`;
                }

                let btnVoir = `<button type="button" onclick="openStudentFinancialSummaryModal('${d.code_inscription}')" 
                                       class="btn btn-sm btn-primary me-1" 
                                       style="border-radius: 8px; font-weight: 800; font-size: 12px; padding: 5px 12px; background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(30,58,95,0.2);" 
                                       title="Voir les détails & le récapitulatif de l'étudiant">
                                <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Voir
                               </button>`;

                let btnProfil = d.encrypted_etudiant_code ? 
                    `<a href="${window.RACINE}etudiant/details/${d.encrypted_etudiant_code}" target="_blank" 
                        class="btn btn-sm btn-outline-secondary" 
                        style="border-radius: 8px; font-weight: 700; font-size: 12px; padding: 5px 10px; display: inline-flex; align-items: center; gap: 4px;" 
                        title="Consulter le dossier profil de l'étudiant">
                        <i data-lucide="user" style="width: 14px; height: 14px;"></i> Profil
                     </a>` : '';

                return `<div style="display: flex; align-items: center; justify-content: center; gap: 4px; flex-wrap: nowrap;">
                            ${btnEncaisse}
                            ${btnVoir}
                            ${btnProfil}
                        </div>`;
            }}
        ],
        drawCallback: function() {
            if (window.lucide) { lucide.createIcons(); }
        }
    });
}

function loadComptaData() {
    const selAnneeObj = document.getElementById('filter_annee_code');
    const anneeCode = selAnneeObj.value;
    const niveauCode = document.getElementById('filter_niveau_code').value;
    const classeCode = document.getElementById('filter_classe_code').value;
    const regime = document.getElementById('filter_regime').value;
    const statutPaiement = document.getElementById('filter_statut_paiement').value;

    if (selAnneeObj && selAnneeObj.options[selAnneeObj.selectedIndex]) {
        const lbl = selAnneeObj.options[selAnneeObj.selectedIndex].text.replace('(Active)', '').trim();
        const printLbl = document.getElementById('print-annee-label');
        if (printLbl) printLbl.innerText = lbl;
    }

    const url = `<?= RACINE ?>paiement/apiComptabiliteEtudiants?annee_code=${anneeCode}&niveau_code=${niveauCode}&classe_code=${classeCode}&regime=${regime}&statut_paiement=${statutPaiement}`;

    fetch(url)
    .then(r => r.json())
    .then(res => {
        if (res.success && res.data) {
            // Update KPIs
            document.getElementById('kpi-total-etudiants').innerText = res.kpis.total_etudiants;
            document.getElementById('kpi-total-scolarites').innerText = formatFCFA(res.kpis.total_scolarites);
            document.getElementById('kpi-total-frais-annexes').innerText = formatFCFA(res.kpis.total_frais_annexes);
            document.getElementById('kpi-total-attendu').innerText = formatFCFA(res.kpis.total_attendu);
            document.getElementById('kpi-total-encaisse').innerText = formatFCFA(res.kpis.total_encaisse);
            document.getElementById('kpi-reste-recouvrer').innerText = formatFCFA(res.kpis.reste_a_recouvrer);

            document.getElementById('compta-records-count').innerText = `${res.data.length} Étudiants enregistrés`;

            // Update DataTable
            dataTableCompta.clear().rows.add(res.data).draw();
        }
    })
    .catch(err => {
        console.error("Erreur chargement données comptables :", err);
    });
}

function applyComptaFilters() {
    loadComptaData();
}

function resetComptaFilters() {
    document.getElementById('filter_niveau_code').value = 'ALL';
    document.getElementById('filter_classe_code').value = 'ALL';
    document.getElementById('filter_regime').value = 'ALL';
    document.getElementById('filter_statut_paiement').value = 'ALL';
    loadComptaData();
}

function formatFCFA(amount) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(amount || 0)) + ' FCFA';
}

function openStudentFinancialSummaryModal(inscriptionCode) {
    const anneeCode = document.getElementById('filter_annee_code').value;
    fetch(`<?= RACINE ?>paiement/getStudentFinancialSummary?inscription_code=${inscriptionCode}&annee_code=${anneeCode}`)
    .then(r => r.json())
    .then(res => {
        if (res.status === 1 || res.success) {
            const data = res.data || res;
            
            document.getElementById('sum-student-name').innerText = data.etudiant_nom;
            document.getElementById('sum-student-details').innerText = `Matricule: ${data.matricule} | Classe: ${data.classe}`;
            document.getElementById('sum-student-photo').src = data.photo || '<?= RACINE ?>public/assets/images/default-avatar.png';

            document.getElementById('sum-val-scolarite').innerText = formatFCFA(data.scolarite_due);
            document.getElementById('sum-val-frais-annexes').innerText = formatFCFA(data.montant_frais_annexes || 0);
            document.getElementById('sum-val-paye').innerText = formatFCFA(data.total_paye);
            document.getElementById('sum-val-reste').innerText = formatFCFA(data.solde_restant);

            // Populate tranches
            let htmlTr = '';
            if (data.tranches && data.tranches.length > 0) {
                data.tranches.forEach(tr => {
                    htmlTr += `<tr>
                        <td><strong>${tr.libelle}</strong></td>
                        <td style="text-align: right;">${formatFCFA(tr.montant)}</td>
                        <td style="text-align: right;" class="text-success">${formatFCFA(tr.deja_paye)}</td>
                        <td style="text-align: right;" class="text-danger">${formatFCFA(tr.reste)}</td>
                        <td style="text-align: center;"><span class="badge-status ${tr.badge}">${tr.statut}</span></td>
                    </tr>`;
                });
            } else {
                htmlTr = '<tr><td colspan="5" class="text-center text-muted">Aucune tranche configurée.</td></tr>';
            }
            document.getElementById('sum-tbody-tranches').innerHTML = htmlTr;

            // Populate payments history
            let htmlPay = '';
            if (data.all_payments && data.all_payments.length > 0) {
                data.all_payments.forEach(p => {
                    htmlPay += `<tr>
                        <td>${p.date_paiement}</td>
                        <td class="font-monospace"><strong>${p.code_paiement}</strong></td>
                        <td>${p.type_transaction || 'Versement'}</td>
                        <td class="text-uppercase">${p.mode_paiement}</td>
                        <td style="text-align: right;" class="fw-bold text-success">${formatFCFA(p.montant_paiement)}</td>
                        <td style="text-align: center;">
                            <a href="<?= RACINE ?>paiement/details/${p.encrypted_id}" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 2px 6px; font-size: 11px;">
                                Reçu <i data-lucide="printer" style="width: 12px; height: 12px;"></i>
                            </a>
                        </td>
                    </tr>`;
                });
            } else {
                htmlPay = '<tr><td colspan="6" class="text-center text-muted">Aucun règlement encaisse pour le moment.</td></tr>';
            }
            document.getElementById('sum-tbody-paiements').innerHTML = htmlPay;

            document.getElementById('modal-student-financial-summary').style.display = 'flex';
            if (window.lucide) { lucide.createIcons(); }
        }
    });
}

function closeStudentSummaryModal() {
    document.getElementById('modal-student-financial-summary').style.display = 'none';
}

function openModalEncaissementCompta() {
    document.getElementById('modal_select_inscription').value = '';
    document.getElementById('encaisse-student-summary-card').style.display = 'none';
    document.getElementById('encaisse-payment-inputs-section').style.display = 'none';
    document.getElementById('modal-encaisse-versement').style.display = 'flex';
}

function openModalPayementForStudent(inscriptionCode) {
    document.getElementById('modal-encaisse-versement').style.display = 'flex';
    const sel = document.getElementById('modal_select_inscription');
    if (sel) {
        sel.value = inscriptionCode;
        onInscriptionSelected(inscriptionCode);
    }
}

function onInscriptionSelected(inscriptionCode) {
    if (!inscriptionCode) {
        document.getElementById('encaisse-student-summary-card').style.display = 'none';
        document.getElementById('encaisse-payment-inputs-section').style.display = 'none';
        return;
    }

    const anneeCode = document.getElementById('filter_annee_code').value;
    fetch(`<?= RACINE ?>paiement/getStudentFinancialSummary?inscription_code=${inscriptionCode}&annee_code=${anneeCode}`)
    .then(r => r.json())
    .then(res => {
        if (res.status === 1 || res.success) {
            const data = res.data || res;
            document.getElementById('encaisse-student-name').innerText = data.etudiant_nom;
            document.getElementById('encaisse-student-meta').innerText = `Matricule: ${data.matricule} | Classe: ${data.classe}`;
            document.getElementById('encaisse-photo-preview').src = data.photo || '<?= RACINE ?>public/assets/images/default-avatar.png';

            document.getElementById('encaisse-val-scolarite').innerText = formatFCFA(data.scolarite_due);
            document.getElementById('encaisse-val-paye').innerText = formatFCFA(data.total_paye);
            document.getElementById('encaisse-val-reste').innerText = formatFCFA(data.solde_restant);

            const montantFA = parseFloat(data.montant_frais_annexes || 0);
            const cardFA = document.getElementById('encaisse-card-frais-annexes');
            if (montantFA > 0 && !data.has_paid_before) {
                if (cardFA) cardFA.style.display = 'block';
                document.getElementById('encaisse-val-frais-annexes').innerText = formatFCFA(montantFA);
            } else {
                if (cardFA) cardFA.style.display = 'none';
            }

            // Populate tranches
            const selTr = document.getElementById('modal_select_tranche');
            selTr.innerHTML = '';
            if (data.tranches && data.tranches.length > 0) {
                let suggested = null;
                data.tranches.forEach(tr => {
                    const opt = document.createElement('option');
                    opt.value = tr.code_tranche;
                    opt.text = `${tr.libelle} - Dû: ${formatFCFA(tr.reste)}`;
                    opt.dataset.montantTranche = tr.reste;
                    selTr.appendChild(opt);
                    if (tr.reste > 0 && !suggested) { suggested = tr.code_tranche; }
                });
                if (suggested) selTr.value = suggested;
            }

            const currentTrancheMontant = parseFloat(data.suggested_tranche_reste || 0);
            document.getElementById('hidden_montant_tranche').value = currentTrancheMontant;

            let totalPaiementAuto = currentTrancheMontant;
            if (!data.has_paid_before && montantFA > 0) {
                document.getElementById('container-frais-annexes').style.display = 'block';
                document.getElementById('input-montant-frais-annexes').value = formatFCFA(montantFA);
                document.getElementById('hidden_montant_frais_annexes').value = montantFA;
                totalPaiementAuto += montantFA;
            } else {
                document.getElementById('container-frais-annexes').style.display = 'none';
                document.getElementById('hidden_montant_frais_annexes').value = 0;
            }

            document.getElementById('input-montant-versement').value = totalPaiementAuto;

            document.getElementById('encaisse-student-summary-card').style.display = 'block';
            document.getElementById('encaisse-payment-inputs-section').style.display = 'block';
        }
    });
}

function submitEncaissementForm(form) {
    const formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 1 || res.success) {
            document.getElementById('modal-encaisse-versement').style.display = 'none';
            loadComptaData();
            alert("Versement encaissé avec succès !");
        } else {
            alert(res.message || "Erreur lors de l'enregistrement du versement.");
        }
    })
    .catch(err => {
        alert("Erreur de communication avec le serveur.");
    });
}
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
