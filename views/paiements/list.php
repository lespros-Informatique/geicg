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
  /* Masquer la colonne Actions lors de l'impression */
  #table-paiements th:last-child,
  #table-paiements td:last-child {
    display: none !important;
  }
  .card {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  table#table-paiements {
    width: 100% !important;
    border-collapse: collapse !important;
    margin-top: 10px !important;
  }
  table#table-paiements th,
  table#table-paiements td {
    border: 1px solid #334155 !important;
    padding: 6px 8px !important;
    font-size: 11px !important;
    color: #000000 !important;
  }
  table#table-paiements th {
    background: #F1F5F9 !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
  }
  table#table-paiements tr {
    page-break-inside: avoid !important;
  }
  #print-header-banner, .print-signatures-block {
    display: block !important;
  }
}

#print-header-banner, .print-signatures-block {
  display: none;
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE D'IMPRESSION OFFICIELLE (VISIBLE UNIQUEMENT À L'IMPRESSION) -->
      <div id="print-header-banner" style="margin-bottom: 16px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">GEICG - GROUPE ÉCOLE D'INGÉNIEURS ET DE COMMERCE DE GAGNOA</h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">JOURNAL OFFICIEL DES ENCAISSEMENTS DE CAISSE & SCOLARITÉ</div>
            <div style="font-size: 11px; color: #475569; margin-top: 3px;" id="print-filter-summary-text">
              Année Académique : All &bull; Niveau : Tous &bull; Classe : Toutes
            </div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(trim(($_SESSION['prenom_utilisateur'] ?? $_SESSION['nom_utilisateur'] ?? 'Caissier'))) ?></strong></div>
          </div>
        </div>
        
        <!-- Ligne synthèse financière -->
        <div style="margin-top: 10px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #0F172A;">
          <span>Total Encaissé : <strong style="color: #15803D;" id="print-kpi-total">0 FCFA</strong></span>
          <span>Espèces : <strong id="print-kpi-especes">0 FCFA</strong></span>
          <span>Mobile Money : <strong id="print-kpi-mobile">0 FCFA</strong></span>
          <span>Chèques / Banque : <strong id="print-kpi-banque">0 FCFA</strong></span>
        </div>
      </div>

      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Caisse & Encaissements Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Caisse & Encaissements Scolarité</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
          <button onclick="printRegistry()" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;" title="Imprimer le registre des encaissements">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </button>
          <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Règlement Caisse
          </a>
        </div>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES -->
      <div class="card card-filters" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; align-items: flex-end;">
          
          <!-- Filtre Année Académique -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Année Académique :
            </label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <?php foreach (($annees ?? []) as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($selectedAnneeCode ?? '') === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['statut_annee'] ?? '') === 'actif' ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Niveau -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="layer" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Niveau d'Études :
            </label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Tous les niveaux --</option>
              <?php foreach (($niveaux ?? []) as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Classe -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="users" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Classe / Groupe :
            </label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Toutes les classes --</option>
              <?php foreach (($classes ?? []) as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Période : Date Début -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar-days" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Date Début :
            </label>
            <input type="date" id="filter-date-debut" class="form-control" style="height: 42px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 4px 10px; font-size: 13px; font-weight: 600; color: #1E293B; background-color: #FFFFFF; width: 100%; box-sizing: border-box;">
          </div>

          <!-- Filtre Période : Date Fin -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar-check-2" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Date Fin :
            </label>
            <input type="date" id="filter-date-fin" class="form-control" style="height: 42px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 4px 10px; font-size: 13px; font-weight: 600; color: #1E293B; background-color: #FFFFFF; width: 100%; box-sizing: border-box;">
          </div>

        </div>
      </div>

      <!-- SECTION 1 : ENCAISSEMENTS & CAISSE GUICHET -->
      <div class="kpi-section-title" style="margin-bottom: 8px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="landmark" style="width: 15px; height: 15px; color: #15803D;"></i> Arrêt de Caisse & Modes de Règlement
        </h4>
      </div>
      
      <div class="card-kpi-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
        
        <!-- Total Encaissé -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Total Encaissé</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-total-encaisse"><?= number_format((float)($stats['total_encaisse'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Taux : <strong style="color: #15803D;" id="kpi-taux-recouvrement"><?= number_format((float)($stats['taux_recouvrement'] ?? 0), 1, ',', ' ') ?>%</strong>
            </div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Caisse Espèces -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">Espèces (Caisse)</div>
            <div style="font-size: 18px; font-weight: 900; color: #065F46; margin-top: 3px;">
              <span id="kpi-encaisse-especes"><?= number_format((float)($stats['encaisse_especes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Liquide au guichet</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #D1FAE5; color: #047857; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="banknote" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Mobile Money -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0284C7; text-transform: uppercase; letter-spacing: 0.5px;">Mobile Money</div>
            <div style="font-size: 18px; font-weight: 900; color: #0369A1; margin-top: 3px;">
              <span id="kpi-encaisse-mobile"><?= number_format((float)($stats['encaisse_mobile'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Wave, Orange, MTN, Moov</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="smartphone" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Chèques & Banque -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #4338CA; text-transform: uppercase; letter-spacing: 0.5px;">Chèques & Banque</div>
            <div style="font-size: 18px; font-weight: 900; color: #3730A3; margin-top: 3px;">
              <span id="kpi-encaisse-banque"><?= number_format((float)($stats['encaisse_banque'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Virements & Chèques</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="building-2" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Montant en Attente (Global) -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FCA5A5; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #B91C1C; text-transform: uppercase; letter-spacing: 0.5px;">Reste à Recouvrer</div>
            <div style="font-size: 18px; font-weight: 900; color: #B91C1C; margin-top: 3px;">
              <span id="kpi-montant-en-attente"><?= number_format((float)($stats['montant_en_attente'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Scolarités attendues</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #FEE2E2; color: #B91C1C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- SECTION 2 : RECOUUVREMENT ÉLÈVES & SUIVI POST-INSCRIPTION -->
      <div class="kpi-section-title" style="margin-bottom: 8px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="users" style="width: 15px; height: 15px; color: #EA580C;"></i> Suivi des Inscriptions & Santé Financière des Élèves
        </h4>
      </div>

      <div class="card-kpi-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px;">
        
        <!-- CARTE SPÉCIFIQUE : En Attente Post-Inscription Immédiate -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1.5px solid #FDBA74; box-shadow: 0 2px 4px rgba(234,88,12,0.06); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #C2410C; text-transform: uppercase; letter-spacing: 0.5px;">En Attente Post-Inscription</div>
            <div style="font-size: 18px; font-weight: 900; color: #C2410C; margin-top: 3px;">
              <span id="kpi-attente-post-inscription"><?= number_format((float)($stats['attente_post_inscription'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 700; color: #EA580C; margin-top: 2px;">Solde scolarité des inscrits</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #FFEDD5; color: #EA580C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-plus" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Élèves Soldés -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Élèves Soldés (100%)</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-eleves-soldes"><?= number_format((int)($stats['eleves_soldes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">élève(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Scolarité entièrement payée</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Élèves en Acompte -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #D97706; text-transform: uppercase; letter-spacing: 0.5px;">Élèves en Acompte</div>
            <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 3px;">
              <span id="kpi-eleves-acomptes"><?= number_format((int)($stats['eleves_acomptes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">élève(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Versements partiels</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Élèves Non Payeurs -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #E11D48; text-transform: uppercase; letter-spacing: 0.5px;">Élèves Non Payeurs</div>
            <div style="font-size: 18px; font-weight: 900; color: #BE123C; margin-top: 3px;">
              <span id="kpi-eleves-non-payeurs"><?= number_format((int)($stats['eleves_non_payeurs'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">élève(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">0 FCFA versé</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #FFE4E6; color: #E11D48; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-x" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Session du Jour -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Jour</div>
            <div style="font-size: 18px; font-weight: 900; color: #1E3A5F; margin-top: 3px;">
              <span id="kpi-encaisse-aujourdhui"><?= number_format((float)($stats['encaisse_aujourdhui'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Aujourd'hui</div>
          </div>
          <div style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calendar-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- TABLEAU DES ENCAISSEMENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-paiements" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 45px; text-align: center;">#</th>
                <th style="padding: 12px; width: 120px;">Réf. Reçu</th>
                <th style="padding: 12px; width: 120px;">Date</th>
                <th style="padding: 12px; width: 110px;">Matricule</th>
                <th style="padding: 12px;">Étudiant</th>
                <th style="padding: 12px; width: 110px;">Classe</th>
                <th style="padding: 12px;">Motif / Tranche</th>
                <th style="padding: 12px; width: 150px; text-align: right;">Montant Versé</th>
                <th style="padding: 12px; width: 220px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- BLOC SIGNATURES EN BAS D'IMPRESSION -->
      <div class="print-signatures-block" style="margin-top: 35px; page-break-inside: avoid;">
        <div style="display: flex; justify-content: space-between; padding: 0 40px;">
          <div style="text-align: center; width: 220px;">
            <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0F172A;">Le Caissier / Agent Guichet</div>
            <div style="height: 55px;"></div>
            <div style="font-size: 10px; color: #64748B;">Signature & Cachet</div>
          </div>
          <div style="text-align: center; width: 220px;">
            <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0F172A;">La Direction Financière</div>
            <div style="height: 55px;"></div>
            <div style="font-size: 10px; color: #64748B;">Signature & Cachet</div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>
function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

$(document).ready(function() {
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe').select2({ width: '100%' });
  }

  // Cascading dropdown pour la sélection de classe selon le niveau choisi
  $('#filter-niveau').on('change', function() {
    var selNiv = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiv = $(this).data('niveau');
      if (selNiv === 'ALL' || !selNiv || !optNiv || optNiv === selNiv) {
        $(this).show();
      } else {
        $(this).hide();
        if ($('#filter-classe').val() === $(this).val()) {
          $('#filter-classe').val('ALL').trigger('change.select2');
        }
      }
    });
  });

  var table = $('#table-paiements').DataTable({
    order: [],
    ajax: {
      url: '<?= RACINE ?>paiement/apiList',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.date_debut = $('#filter-date-debut').val();
        d.date_fin = $('#filter-date-fin').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', className: 'text-center', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_paiement', width: '120px', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(d || '-') + '</code>';
      } },
      { data: 'date_paiement', width: '120px', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          var dateFr = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
          var timeFr = parts[1] ? parts[1].substring(0,5) : '';
          return '<span style="font-weight:600; color:#334155;">' + dateFr + '</span>' + (timeFr && timeFr !== '00:00' ? ' <span style="font-size:11px; color:#64748B;">' + timeFr + '</span>' : '');
        }
        return escapeHtml(d);
      } },
      { data: 'matricule_etudiant', width: '110px', render: function(d) {
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; border:1px solid #CBD5E1; padding:3px 7px; border-radius:6px; font-size:11.5px;">' + escapeHtml(d || '-') + '</code>';
      } },
      { data: 'etudiant_nom', render: function(d, type, row) {
        return '<div style="font-weight:700; color:#0F172A; font-size:13.5px;">' + escapeHtml(d || 'Étudiant non identifié') + '</div>';
      } },
      { data: 'libelle_classe', width: '110px', render: function(d) {
        return '<span style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(d || '-') + '</span>';
      } },
      { data: 'libelle_tranche', render: function(d, type, row) {
        return '<span style="font-weight:600; color:#334155; font-size:13px;">' + escapeHtml(d || 'Frais de Scolarité') + '</span>';
      } },
      { data: 'montant_paiement', width: '150px', className: 'text-end', render: function(d) {
        return d ? '<strong style="color:#15803D; font-size:14px;">' + Number(d).toLocaleString('fr-FR') + ' FCFA</strong>' : '-';
      } },
      { data: null, width: '220px', orderable: false, className: 'text-end', render: function(d) {
        var idCrypte = d.editId || d.id_paiement;
        return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">' +
               '  <a href="' + window.RACINE + 'paiement/details/' + idCrypte + '?print=1" target="_blank" class="btn btn-sm btn-outline-primary" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;" title="Imprimer le reçu"><i data-lucide="printer" style="width:13px;height:13px;"></i> Imprimer</a>' +
               '  <a href="' + window.RACINE + 'paiement/details/' + idCrypte + '" class="btn btn-sm btn-info" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="eye" style="width:13px;height:13px;"></i> Détails</a>' +
               '  <a href="' + window.RACINE + 'paiement/edition/' + idCrypte + '" class="btn btn-sm btn-secondary" style="font-weight:600; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="edit" style="width:13px;height:13px;"></i> Éditer</a>' +
               '</div>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // FONCTION DE GESTION IMPRESSION OFFICIELLE DU REGISTRE
  window.printRegistry = function() {
    var anneeTxt = $('#filter-annee option:selected').text().trim() || 'Toutes';
    var niveauTxt = $('#filter-niveau option:selected').text().trim() || 'Tous';
    var classeTxt = $('#filter-classe option:selected').text().trim() || 'Toutes';
    var dtDeb = $('#filter-date-debut').val();
    var dtFin = $('#filter-date-fin').val();
    var periodTxt = '';
    if (dtDeb || dtFin) {
      periodTxt = ' &bull; Période : <strong>' + (dtDeb ? 'Du ' + dtDeb.split('-').reverse().join('/') : '') + (dtFin ? ' Au ' + dtFin.split('-').reverse().join('/') : '') + '</strong>';
    }

    $('#print-filter-summary-text').html(
      'Année Académique : <strong>' + escapeHtml(anneeTxt) + '</strong> &bull; Niveau : <strong>' + escapeHtml(niveauTxt) + '</strong> &bull; Classe : <strong>' + escapeHtml(classeTxt) + '</strong>' + periodTxt
    );

    $('#print-kpi-total').text($('#kpi-total-encaisse').text() + ' FCFA');
    $('#print-kpi-especes').text($('#kpi-encaisse-especes').text() + ' FCFA');
    $('#print-kpi-mobile').text($('#kpi-encaisse-mobile').text() + ' FCFA');
    $('#print-kpi-banque').text($('#kpi-encaisse-banque').text() + ' FCFA');

    var currentLen = table.page.len();
    table.page.len(-1).draw();

    setTimeout(function() {
      window.print();
      setTimeout(function() {
        table.page.len(currentLen).draw();
      }, 400);
    }, 300);
  };

  // Fonction de rafraîchissement AJAX des statistiques KPI
  function refreshKpis() {
    $.ajax({
      url: '<?= RACINE ?>paiement/apiStats',
      type: 'GET',
      data: {
        annee_code: $('#filter-annee').val(),
        niveau_code: $('#filter-niveau').val(),
        classe_code: $('#filter-classe').val(),
        date_debut: $('#filter-date-debut').val(),
        date_fin: $('#filter-date-fin').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.stats) {
          var s = res.stats;
          $('#kpi-total-encaisse').text(Number(s.total_encaisse || 0).toLocaleString('fr-FR'));
          $('#kpi-taux-recouvrement').text(Number(s.taux_recouvrement || 0).toLocaleString('fr-FR', { minimumFractionDigits: 1 }));
          $('#kpi-encaisse-especes').text(Number(s.encaisse_especes || 0).toLocaleString('fr-FR'));
          $('#kpi-encaisse-mobile').text(Number(s.encaisse_mobile || 0).toLocaleString('fr-FR'));
          $('#kpi-encaisse-banque').text(Number(s.encaisse_banque || 0).toLocaleString('fr-FR'));
          $('#kpi-montant-en-attente').text(Number(s.montant_en_attente || 0).toLocaleString('fr-FR'));
          
          $('#kpi-attente-post-inscription').text(Number(s.attente_post_inscription || 0).toLocaleString('fr-FR'));
          $('#kpi-eleves-soldes').text(Number(s.eleves_soldes || 0).toLocaleString('fr-FR'));
          $('#kpi-eleves-acomptes').text(Number(s.eleves_acomptes || 0).toLocaleString('fr-FR'));
          $('#kpi-eleves-non-payeurs').text(Number(s.eleves_non_payeurs || 0).toLocaleString('fr-FR'));
          $('#kpi-encaisse-aujourdhui').text(Number(s.encaisse_aujourdhui || 0).toLocaleString('fr-FR'));
        }
      }
    });
  }

  // Événements de changement sur tous les filtres
  $('#filter-annee, #filter-niveau, #filter-classe, #filter-date-debut, #filter-date-fin').on('change input', function() {
    table.ajax.reload();
    refreshKpis();
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
