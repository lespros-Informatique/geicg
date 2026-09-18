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

/* --- ANIMATIONS ET INTERACTIONS PREMIUM --- */
@keyframes kpiEntrance {
  0% {
    opacity: 0;
    transform: translateY(18px) scale(0.98);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes liveRadar {
  0% { transform: scale(0.6); opacity: 1; }
  50% { opacity: 0.6; }
  100% { transform: scale(2.2); opacity: 0; }
}

@keyframes btnPulseGlow {
  0%, 100% {
    box-shadow: 0 4px 14px rgba(22, 163, 74, 0.25);
    transform: translateY(0);
  }
  50% {
    box-shadow: 0 6px 22px rgba(22, 163, 74, 0.45);
    transform: translateY(-1.5px);
  }
}

@keyframes modalZoomIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.card-kpi-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
@media (max-width: 1200px) {
  .card-kpi-container {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 640px) {
  .card-kpi-container {
    grid-template-columns: 1fr;
  }
}

.kpi-card {
  position: relative;
  overflow: hidden;
  min-height: 118px !important;
  padding: 22px 22px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  box-sizing: border-box;
  transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
              box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
              border-color 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  animation: kpiEntrance 0.55s cubic-bezier(0.16, 1, 0.3, 1) backwards;
  cursor: default;
}

.kpi-card > div:first-child {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
}

.kpi-card [id^="kpi-"] {
  font-size: 20px;
  font-weight: 900;
}

/* Échelonnement de l'apparition des cartes (Staggered entrance) */
.card-kpi-container:nth-of-type(1) .kpi-card:nth-child(1) { animation-delay: 0.04s; }
.card-kpi-container:nth-of-type(1) .kpi-card:nth-child(2) { animation-delay: 0.08s; }
.card-kpi-container:nth-of-type(1) .kpi-card:nth-child(3) { animation-delay: 0.12s; }
.card-kpi-container:nth-of-type(1) .kpi-card:nth-child(4) { animation-delay: 0.16s; }

.card-kpi-container:nth-of-type(2) .kpi-card:nth-child(1) { animation-delay: 0.20s; }
.card-kpi-container:nth-of-type(2) .kpi-card:nth-child(2) { animation-delay: 0.24s; }
.card-kpi-container:nth-of-type(2) .kpi-card:nth-child(3) { animation-delay: 0.28s; }
.card-kpi-container:nth-of-type(2) .kpi-card:nth-child(4) { animation-delay: 0.32s; }

.card-kpi-container:nth-of-type(3) .kpi-card:nth-child(1) { animation-delay: 0.36s; }
.card-kpi-container:nth-of-type(3) .kpi-card:nth-child(2) { animation-delay: 0.40s; }
.card-kpi-container:nth-of-type(3) .kpi-card:nth-child(3) { animation-delay: 0.44s; }
.card-kpi-container:nth-of-type(3) .kpi-card:nth-child(4) { animation-delay: 0.48s; }

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 6px 10px -3px rgba(15, 23, 42, 0.03) !important;
}

.kpi-icon-box {
  width: 48px !important;
  height: 48px !important;
  border-radius: 12px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0 !important;
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease;
}

.kpi-icon-box svg, .kpi-icon-box i {
  width: 24px !important;
  height: 24px !important;
}

.kpi-card:hover .kpi-icon-box {
  transform: scale(1.15) rotate(4deg);
}

/* Effet reflet de lumière (Shimmer shine) */
.kpi-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: -120%;
  width: 60%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.55), transparent);
  transform: skewX(-22deg);
  pointer-events: none;
}

.kpi-card:hover::after {
  left: 180%;
  transition: left 0.85s ease-in-out;
}

/* Bouton Encaisser scolarité Pulse lumineux */
#btn-open-encaissement-modal {
  animation: btnPulseGlow 2.8s infinite ease-in-out;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
#btn-open-encaissement-modal:hover {
  transform: translateY(-2px) scale(1.03);
}

/* Indicateur radar vert en direct */
.live-status-pulse {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #10B981;
  position: relative;
  margin-right: 6px;
  vertical-align: middle;
}
.live-status-pulse::after {
  content: '';
  position: absolute;
  top: -3px;
  left: -3px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background-color: rgba(16, 185, 129, 0.55);
  animation: liveRadar 2s infinite ease-out;
}

/* Carte filtres */
.card-filters {
  transition: box-shadow 0.25s ease, border-color 0.25s ease;
}
.card-filters:focus-within {
  box-shadow: 0 6px 18px rgba(30, 58, 95, 0.08) !important;
  border-color: #CBD5E1 !important;
}

/* Animation tableau DataTables */
#table-paiements tbody tr {
  transition: background-color 0.18s ease;
}
#table-paiements tbody tr:hover {
  background-color: #F8FAFC !important;
}

/* Boutons d'actions avec micro-rebond */
#table-paiements a.btn {
  transition: transform 0.16s ease, box-shadow 0.16s ease;
}
#table-paiements a.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Modals animés avec ouverture fluide */
#modal-encaissement-scolarite > div,
#modal-recu-paiement-success > div {
  animation: modalZoomIn 0.32s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
<?php
$isCaisseOuverte = $isCaisseOuverte ?? false;
$activeSession = $activeSession ?? null;
$encryptedSessionId = $encryptedSessionId ?? '';
$canRecord = $canRecord ?? false;
$canOpenCaisse = $canOpenCaisse ?? false;
$canCloseCaisse = $canCloseCaisse ?? false;
?>
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
          <span>Affectés (État) : <strong id="print-kpi-affectes">0 FCFA</strong></span>
          <span>Non Affectés (Privés) : <strong id="print-kpi-prives">0 FCFA</strong></span>
        </div>
      </div>

      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Caisse & Encaissements Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Caisse & Encaissements Scolarité</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
          <button onclick="printRegistry()" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;" title="Imprimer le registre des encaissements">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </button>

          <?php if (!empty($isCaisseOuverte)): ?>
            <?php if (!empty($canCloseCaisse)): ?>
              <a href="<?= RACINE ?>session_caisse/cloturer/<?= !empty($encryptedSessionId) ? $encryptedSessionId : '' ?>" class="btn btn-danger" style="background: #DC2626; border-color: #DC2626; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(220,38,38,0.2); text-decoration: none;" title="Clôturer la session de caisse du jour (<?= htmlspecialchars($activeSession['code_session'] ?? '') ?>)">
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Fermeture de caisse
              </a>
            <?php endif; ?>
          <?php else: ?>
            <?php if (!empty($canOpenCaisse)): ?>
              <button type="button" class="btn btn-primary btn-open-session-caisse" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(30,58,95,0.2); cursor: pointer;" title="Ouvrir la session de caisse du jour">
                <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouverture de caisse
              </button>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($canRecord)): ?>
          <button type="button" id="btn-open-encaissement-modal" class="btn btn-success" style="background: #16A34A; border-color: #16A34A; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(22,163,74,0.2); cursor: pointer;">
            <i data-lucide="banknote" style="width: 18px; height: 18px;"></i> Encaisser scolarité
          </button>
          <?php endif; ?>
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

      <!-- SECTION 1 : ENCAISSEMENTS & CAISSE GUICHET (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <span class="live-status-pulse"></span><i data-lucide="landmark" style="width: 15px; height: 15px; color: #15803D;"></i> Arrêt de Caisse & Modes de Règlement
        </h4>
      </div>
      
      <div class="card-kpi-container">
        
        <!-- 1. Total Encaissé -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Total Encaissé</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-total-encaisse"><?= number_format((float)($stats['total_encaisse'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Taux : <strong style="color: #15803D;"><span id="kpi-taux-recouvrement"><?= number_format((float)($stats['taux_recouvrement'] ?? 0), 1, ',', ' ') ?></span>%</strong>
            </div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 2. Encaissements Jour -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BFDBFE; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Jour</div>
            <div style="font-size: 18px; font-weight: 900; color: #1E3A5F; margin-top: 3px;">
              <span id="kpi-encaisse-aujourdhui"><?= number_format((float)($stats['encaisse_aujourdhui'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">
              <span class="live-status-pulse"></span>Aujourd'hui
            </div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calendar-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 3. Caisse Espèces -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">Espèces (Caisse)</div>
            <div style="font-size: 18px; font-weight: 900; color: #065F46; margin-top: 3px;">
              <span id="kpi-encaisse-especes"><?= number_format((float)($stats['encaisse_especes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Liquide au guichet</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #D1FAE5; color: #047857; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="banknote" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 4. Reste à Recouvrer -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FCA5A5; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #B91C1C; text-transform: uppercase; letter-spacing: 0.5px;">Reste à Recouvrer</div>
            <div style="font-size: 18px; font-weight: 900; color: #B91C1C; margin-top: 3px;">
              <span id="kpi-montant-en-attente"><?= number_format((float)($stats['montant_en_attente'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Scolarités attendues</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEE2E2; color: #B91C1C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- SECTION 2 : RÉPARTITION PAR RÉGIME & POST-INSCRIPTION (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="pie-chart" style="width: 15px; height: 15px; color: #0284C7;"></i> Répartition par Régime & Période
        </h4>
      </div>

      <div class="card-kpi-container">
        
        <!-- 5. Affectés (État) -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0284C7; text-transform: uppercase; letter-spacing: 0.5px;">Affectés (État)</div>
            <div style="font-size: 18px; font-weight: 900; color: #0369A1; margin-top: 3px;">
              <span id="kpi-encaisse-affectes"><?= number_format((float)($stats['encaisse_affectes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Montant encaissé (État)</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 6. Non Affectés (Privés) -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #4338CA; text-transform: uppercase; letter-spacing: 0.5px;">Non Affectés (Privés)</div>
            <div style="font-size: 18px; font-weight: 900; color: #3730A3; margin-top: 3px;">
              <span id="kpi-encaisse-prives"><?= number_format((float)($stats['encaisse_prives'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Montant encaissé (Privés)</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 7. Encaissements Mois -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0E7490; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Mois</div>
            <div style="font-size: 18px; font-weight: 900; color: #0E7490; margin-top: 3px;">
              <span id="kpi-encaisse-mois"><?= number_format((float)($stats['encaisse_mois'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Mois en cours</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #CFFAFE; color: #0E7490; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 8. Montant Total de l'Exercice de l'Année en Session -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BFDBFE; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.5px;">Total Exercice Année</div>
            <div style="font-size: 18px; font-weight: 900; color: #1E3A8A; margin-top: 3px;">
              <span id="kpi-total-exercice"><?= number_format((float)($stats['total_exercice_session'] ?? ($stats['total_scolarite_attendue'] ?? 0)), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Exercice : <strong style="color: #1E40AF;" id="kpi-exercice-session-libelle"><?= htmlspecialchars($stats['annee_exercice_libelle'] ?? ($_SESSION['annee_active_libelle'] ?? 'En session')) ?></strong>
            </div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: #1E40AF; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calculator" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- SECTION 3 : SANTÉ FINANCIÈRE & STATUTS DES ÉTUDIANTS (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="users" style="width: 15px; height: 15px; color: #EA580C;"></i> Suivi des Inscriptions & Santé Financière des Étudiants
        </h4>
      </div>

      <div class="card-kpi-container">
        
        <!-- 9. Étudiants Soldés -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants Soldés (100%)</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-eleves-soldes"><?= number_format((int)($stats['eleves_soldes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Scolarité entièrement payée</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 10. Étudiants en Acompte -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #D97706; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants en Acompte</div>
            <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 3px;">
              <span id="kpi-eleves-acomptes"><?= number_format((int)($stats['eleves_acomptes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Versements partiels</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 11. Frais d'inscription non réglé -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #E11D48; text-transform: uppercase; letter-spacing: 0.5px;">Frais d'inscription non réglé</div>
            <div style="font-size: 18px; font-weight: 900; color: #BE123C; margin-top: 3px;">
              <span id="kpi-eleves-non-payeurs"><?= number_format((int)($stats['eleves_non_payeurs'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">0 FCFA versé</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FFE4E6; color: #E11D48; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-x" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 12. Total Inscrits -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Total Inscrits</div>
            <div style="font-size: 18px; font-weight: 900; color: #7E22CE; margin-top: 3px;">
              <span id="kpi-total-inscrits"><?= number_format((int)($stats['total_inscrits'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Effectif académique suivi</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="graduation-cap" style="width: 20px; height: 20px;"></i>
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

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : ENCAISSEMENT DIRECT DE SCOLARITÉ                      -->
<!-- ========================================================================= -->
<div id="modal-encaisser-scolarite" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 820px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F233D 100%); color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #16A34A;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(22,163,74,0.25); border: 1.5px solid #4ADE80; display: flex; align-items: center; justify-content: center;">
          <i data-lucide="banknote" style="width: 22px; height: 22px; color: #4ADE80;"></i>
        </div>
        <div>
          <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Encaisser un Versement de Scolarité</h3>
          <div style="font-size: 12px; color: #94A3B8; margin-top: 2px;">Guichet Caisse &bull; Échéancier Réglementaire</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-encaissement" style="background: transparent; border: none; color: #FFFFFF; font-size: 26px; cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
    </div>

    <!-- Corps du modal (Formulaire) -->
    <form id="form-encaissement-scolarite" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
      <div style="padding: 22px 24px; background: #F8FAFC; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
        
        <?php if (empty($isCaisseOuverte)): ?>
          <div id="caisse-warning-notice" style="background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: 10px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 10px;">
            <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #D97706; flex-shrink: 0; margin-top: 1px;"></i>
            <div style="font-size: 12.5px; color: #92400E; line-height: 1.4;">
              <strong>Session de caisse fermée :</strong> Aucune session de caisse n'est ouverte pour aujourd'hui. Pour les règlements en espèces, veuillez d'abord <a href="javascript:void(0)" class="btn-open-session-caisse" style="color: #1E3A5F; font-weight: 800; text-decoration: underline;">ouvrir une session de caisse</a>. Les modes Mobile Money, Chèque et Virement sont toutefois immédiatement acceptés.
            </div>
          </div>
        <?php endif; ?>

        <!-- 1. Sélection de l'étudiant / Inscription (Uniquement année en session) -->
        <div style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1.5px solid #CBD5E1;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
            <label style="font-size: 12px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 6px; text-transform: uppercase;">
              <i data-lucide="user-check" style="width: 15px; height: 15px; color: #1E3A5F;"></i> 1. Rechercher l'étudiant / Dossier d'inscription <span class="text-danger">*</span>
            </label>
            <span style="font-size: 11px; font-weight: 700; color: #16A34A; background: #DCFCE7; border: 1px solid #86EFAC; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
              <i data-lucide="calendar-check" style="width: 12px; height: 12px;"></i> Année en session : <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'En cours') ?>
            </span>
          </div>
          <select id="modal_select_inscription" name="inscription_code" class="form-control" style="width: 100%;" required>
            <option value="">-- Tapez le nom, matricule ou classe de l'étudiant --</option>
            <?php foreach (($inscriptions ?? []) as $ins): ?>
              <?php 
                $photoPath = !empty($ins['photo_inscription']) ? trim($ins['photo_inscription']) : (!empty($ins['photo_etudiant']) ? trim($ins['photo_etudiant']) : '');
                $photoUrl = !empty($photoPath) ? RACINE . ltrim($photoPath, '/') : '';
              ?>
              <option value="<?= htmlspecialchars($ins['code_inscription']) ?>" 
                      data-matricule="<?= htmlspecialchars($ins['matricule_etudiant'] ?? '') ?>" 
                      data-annee="<?= htmlspecialchars($ins['annee_code'] ?? '') ?>"
                      data-photo="<?= htmlspecialchars($photoUrl) ?>"
                      data-nom="<?= htmlspecialchars(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? '')) ?>"
                      data-classe="<?= htmlspecialchars($ins['libelle_classe'] ?? 'Classe non assignée') ?>">
                <?= htmlspecialchars(($ins['matricule_etudiant'] ?? '') . ' - ' . ($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? '') . ' (' . ($ins['libelle_classe'] ?? 'Classe non assignée') . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 2. Synthèse Financière & Profil Étudiant (Chargé dynamiquement) -->
        <div id="encaisse-student-summary-card" style="display: none; background: #FFFFFF; border: 1.5px solid #93C5FD; border-radius: 12px; padding: 18px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
          
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; border-bottom: 1px solid #E2E8F0; padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
              <!-- Photo de l'étudiant avec Fallback Avatar stylisé -->
              <div style="position: relative; width: 56px; height: 56px; border-radius: 12px; overflow: hidden; border: 2.5px solid #3B82F6; box-shadow: 0 4px 10px rgba(30, 58, 95, 0.12); flex-shrink: 0; background: #F8FAFC; display: flex; align-items: center; justify-content: center;">
                <img id="encaisse-stu-photo" src="" alt="Photo étudiant" style="display: none; width: 100%; height: 100%; object-fit: cover; transition: transform 0.25s ease;">
                <div id="encaisse-stu-avatar" style="width: 100%; height: 100%; background: linear-gradient(135deg, #1E3A5F 0%, #0F233D 100%); color: #FFFFFF; font-weight: 900; font-size: 17px; display: flex; align-items: center; justify-content: center; letter-spacing: 0.5px;">
                  ET
                </div>
              </div>
              <div>
                <div id="encaisse-stu-nom" style="font-weight: 800; font-size: 15px; color: #0F172A;">-</div>
                <div style="font-size: 12px; color: #64748B; margin-top: 2px; display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                  <span>Matricule : <code id="encaisse-stu-mat" style="font-weight: 700; color: #1E3A5F; background: #EFF6FF; padding: 2px 6px; border-radius: 4px; border: 1px solid #DBEAFE;">-</code></span>
                  <span>&bull;</span>
                  <span>Classe : <strong id="encaisse-stu-classe" style="color: #0F172A;">-</strong></span>
                </div>
              </div>
            </div>
            <div id="encaisse-stu-regime-badge" style="font-size: 11.5px; font-weight: 800; padding: 4px 10px; border-radius: 6px; background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;">
              -
            </div>
          </div>

          <!-- 3 Compteurs Financiers -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 14px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Scolarité Totale</div>
              <div id="encaisse-val-scolarite" style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin-top: 2px;">0 FCFA</div>
            </div>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Total Déjà Payé</div>
              <div id="encaisse-val-paye" style="font-size: 16px; font-weight: 900; color: #15803D; margin-top: 2px;">0 FCFA</div>
            </div>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #DC2626; text-transform: uppercase;">Reste à Payer</div>
              <div id="encaisse-val-reste" style="font-size: 16px; font-weight: 900; color: #DC2626; margin-top: 2px;">0 FCFA</div>
            </div>
          </div>

        </div>

        <!-- 3. Sélection de la tranche & Paramètres du versement -->
        <div id="encaisse-payment-inputs-section" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Tranche / Échéance (Sélectionnée par ordre de création) -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">
                2. Tranche / Échéance à régler <span class="text-danger">*</span>
              </label>
              <div style="position: relative;">
                <select id="modal_select_tranche" name="tranche_code" class="form-control form-control-lg" style="font-weight: 800; font-size: 14.5px; color: #1E3A5F; border-radius: 10px; background-color: #F8FAFC; cursor: not-allowed; pointer-events: none; padding-right: 40px;" readonly tabindex="-1" required>
                  <!-- Rempli dynamiquement -->
                </select>
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #64748B; pointer-events: none;">
                  <i data-lucide="lock" style="width: 16px; height: 16px;"></i>
                </span>
              </div>
              <div id="tranche-hint-info" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">
                <!-- Rempli dynamiquement -->
              </div>
            </div>

            <!-- Montant versé -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">
                3. Montant Versé (FCFA) <span class="text-danger">*</span>
              </label>
              <div style="position: relative;">
                <input type="number" step="1" id="input-montant-versement" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 20px; color: #15803D; padding-right: 70px; border-radius: 10px; background-color: #F8FAFC; cursor: not-allowed;" placeholder="0" readonly required>
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #64748B; font-size: 14px;">FCFA</span>
              </div>
              <div id="montant-max-hint" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">
                Montant déterminé automatiquement par la tranche sélectionnée.
              </div>
            </div>

            <!-- Mode de règlement -->
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Mode de Règlement <span class="text-danger">*</span></label>
              <select id="modal_select_mode" name="mode_paiement" class="form-control" style="border-radius: 8px; font-weight: 700;" required>
                <option value="espece" selected>💵 Espèces (Caisse Guichet)</option>
                <option value="mobile_money">📱 Mobile Money (Wave / OM / MTN / Moov)</option>
                <option value="cheque">🏦 Chèque Bancaire</option>
                <option value="virement">🏛️ Virement Bancaire</option>
              </select>
            </div>

            <!-- Référence / Transaction -->
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Réf. Transaction / N° Chèque</label>
              <input type="text" name="reference_paiement" id="input_ref_paiement" class="form-control" style="border-radius: 8px;" placeholder="Ex: TRX-99214 / CHQ-8820">
            </div>

            <!-- Observations / Remarques -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Observations / Notes (facultatif)</label>
              <input type="text" name="observations" class="form-control" style="border-radius: 8px;" placeholder="Ex: Payé par le tuteur M. Kouassi...">
            </div>

          </div>
        </div>

      </div>

      <!-- Pied de modal -->
      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn btn-secondary btn-close-modal-encaissement" style="font-weight: 700; border-radius: 8px; padding: 10px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-encaissement" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 10px 26px; background: #16A34A; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(22,163,74,0.3);">
          <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUCCÈS & IMPRESSION DU REÇU OFFICIEL                               -->
<!-- ========================================================================= -->
<div id="modal-recu-paiement-success" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.7); backdrop-filter: blur(4px); z-index: 10000; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; text-align: center; padding: 30px 24px;">
    
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #DCFCE7; border: 2px solid #86EFAC; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
      <i data-lucide="check" style="width: 32px; height: 32px; color: #16A34A;"></i>
    </div>

    <h3 style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0 0 6px 0;">Encaissement Réussi avec Succès !</h3>
    <p style="font-size: 13.5px; color: #64748B; margin: 0 0 16px 0;">Le versement a été enregistré et rattaché à l'échéancier de l'étudiant.</p>

    <div style="background: #F8FAFC; border: 1.5px dashed #CBD5E1; border-radius: 10px; padding: 14px; margin-bottom: 22px;">
      <div style="font-size: 11.5px; color: #64748B; font-weight: 700; text-transform: uppercase;">N° Quittance / Reçu</div>
      <div id="success-recu-num" style="font-size: 20px; font-weight: 900; color: #1E3A5F; font-family: monospace; margin: 4px 0;">PAI-XXXXXXXX</div>
      <div id="success-recu-montant" style="font-size: 14px; font-weight: 800; color: #15803D;">0 FCFA</div>
    </div>

    <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
      <button type="button" class="btn btn-secondary btn-close-modal-success" style="font-weight: 700; border-radius: 8px; padding: 10px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
        Fermer
      </button>
      <a id="success-recu-print-link" href="#" target="_blank" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 22px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu Officiel
      </a>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : OUVERTURE DE LA SESSION DE CAISSE DU JOUR            -->
<!-- ========================================================================= -->
<div id="modal-open-session-caisse" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 10002; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: modalZoomIn 0.25s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouverture de Session de Caisse
      </h3>
      <button type="button" class="btn-close-modal-session" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-open-session-caisse" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

      <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="info" style="color: #15803D; width: 20px; height: 20px; flex-shrink: 0;"></i>
        <div style="font-size: 12.5px; color: #166534; line-height: 1.4;">
          L'ouverture initialise la journée financière. Tous les encaissements effectués seront rattachés à cette session.
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Date de la session <span style="color: #EF4444;">*</span>
        </label>
        <input type="date" name="date_session" id="session_date" required value="<?= date('Y-m-d') ?>" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Fond de caisse initial (FCFA) <span style="color: #EF4444;">*</span>
        </label>
        <div style="position: relative;">
          <input type="number" min="0" step="any" name="fond_initial" id="session_fond_initial" required value="0" placeholder="Ex: 50000" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px 10px 42px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 800; font-size: 15px; color: #0F172A;">
          <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748B; font-weight: 700; font-size: 13px;">F</span>
        </div>
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Montant en espèces disponible dans le tiroir au démarrage.</small>
      </div>

      <div class="form-group" style="margin-bottom: 22px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Observations / Remarques d'ouverture
        </label>
        <textarea name="observations_ouverture" id="session_observations" rows="2" placeholder="Ex: Fond de caisse vérifié en présence du responsable..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px;"></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-session" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-session" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(30,58,95,0.2);">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Confirmer l'Ouverture
        </button>
      </div>
    </form>
  </div>
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
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        var idCrypte = d.editId || d.id_paiement;
        return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">' +
               '  <a href="' + window.RACINE + 'paiement/details/' + idCrypte + '?print=1" target="_blank" class="btn btn-sm btn-outline-primary" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;" title="Imprimer le reçu"><i data-lucide="printer" style="width:13px;height:13px;"></i> Imprimer</a>' +
               '  <a href="' + window.RACINE + 'paiement/details/' + idCrypte + '" class="btn btn-sm btn-info" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="eye" style="width:13px;height:13px;"></i> Détails</a>' +
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
    $('#print-kpi-affectes').text($('#kpi-encaisse-affectes').text() + ' FCFA');
    $('#print-kpi-prives').text($('#kpi-encaisse-prives').text() + ' FCFA');

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
  // Helper d'animation de compteur numérique fluide (CountUp)
  function animateCounter($elem, targetValue, isFloat, duration) {
    if (!$elem || !$elem.length) return;
    duration = duration || 650;
    var rawText = ($elem.text() || '0').toString().replace(/\s/g, '').replace(/,/g, '.');
    var startValue = parseFloat($elem.data('current-val') !== undefined ? $elem.data('current-val') : rawText.replace(/[^0-9.-]/g, '')) || 0;
    $elem.data('current-val', targetValue);
    
    if (Math.abs(startValue - targetValue) < 0.001) {
      if (isFloat) {
        $elem.text(targetValue.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }));
      } else {
        $elem.text(Math.round(targetValue).toLocaleString('fr-FR'));
      }
      return;
    }

    var startTime = null;
    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var ease = 1 - Math.pow(1 - progress, 3);
      var current = startValue + (targetValue - startValue) * ease;
      
      if (isFloat) {
        $elem.text(current.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }));
      } else {
        $elem.text(Math.round(current).toLocaleString('fr-FR'));
      }
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        if (isFloat) {
          $elem.text(targetValue.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }));
        } else {
          $elem.text(Math.round(targetValue).toLocaleString('fr-FR'));
        }
      }
    }
    window.requestAnimationFrame(step);
  }

  // Lancement de l'animation de défilement des compteurs au chargement initial
  setTimeout(function() {
    $('.card-kpi-container [id^="kpi-"]').each(function() {
      var $el = $(this);
      var raw = $el.text().replace(/\s/g, '').replace(/,/g, '.');
      var val = parseFloat(raw) || 0;
      var isFloat = $el.attr('id') === 'kpi-taux-recouvrement';
      $el.data('current-val', 0);
      $el.text(isFloat ? '0,0' : '0');
      animateCounter($el, val, isFloat, 750);
    });
  }, 120);

  // Fonction de rafraîchissement AJAX des statistiques KPI avec transition animée
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
          animateCounter($('#kpi-total-encaisse'), Number(s.total_encaisse || 0), false);
          animateCounter($('#kpi-taux-recouvrement'), Number(s.taux_recouvrement || 0), true);
          animateCounter($('#kpi-encaisse-aujourdhui'), Number(s.encaisse_aujourdhui || 0), false);
          animateCounter($('#kpi-encaisse-especes'), Number(s.encaisse_especes || 0), false);
          animateCounter($('#kpi-montant-en-attente'), Number(s.montant_en_attente || 0), false);
          
          animateCounter($('#kpi-encaisse-affectes'), Number(s.encaisse_affectes || 0), false);
          animateCounter($('#kpi-encaisse-prives'), Number(s.encaisse_prives || 0), false);
          animateCounter($('#kpi-encaisse-mois'), Number(s.encaisse_mois || 0), false);
          animateCounter($('#kpi-total-exercice'), Number(s.total_exercice_session || s.total_scolarite_attendue || 0), false);
          if (s.annee_exercice_libelle) {
            $('#kpi-exercice-session-libelle').text(s.annee_exercice_libelle);
          }
          
          animateCounter($('#kpi-eleves-soldes'), Number(s.eleves_soldes || 0), false);
          animateCounter($('#kpi-eleves-acomptes'), Number(s.eleves_acomptes || 0), false);
          animateCounter($('#kpi-eleves-non-payeurs'), Number(s.eleves_non_payeurs || 0), false);
          animateCounter($('#kpi-total-inscrits'), Number(s.total_inscrits || 0), false);
        }
      }
    });
  }

  // Événements de changement sur tous les filtres
  $('#filter-annee, #filter-niveau, #filter-classe, #filter-date-debut, #filter-date-fin').on('change input', function() {
    table.ajax.reload();
    refreshKpis();
  });

  // =========================================================================
  // GESTION DU MODAL D'ENCAISSEMENT DIRECT DE SCOLARITÉ
  // =========================================================================
  var currentStudentSummary = null;

  function openEncaissementModal(preselectedCode) {
    $('#modal-encaisser-scolarite').css('display', 'flex');
    if ($.fn.select2) {
      $('#modal_select_inscription').select2({
        dropdownParent: $('#modal-encaisser-scolarite'),
        width: '100%',
        placeholder: "-- Tapez le nom, matricule ou classe de l'étudiant --",
        allowClear: true,
        escapeMarkup: function(m) { return m; },
        templateResult: function(data) {
          if (!data.id) return data.text;
          var photo = $(data.element).data('photo');
          var nom = $(data.element).data('nom') || data.text;
          var mat = $(data.element).data('matricule') || '';
          var classe = $(data.element).data('classe') || '';
          var inits = (nom || 'ET').split(' ').filter(function(x){return x;}).map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          
          var photoHtml = '';
          if (photo && photo.trim() !== '') {
            photoHtml = '<div style="width: 34px; height: 34px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1.5px solid #CBD5E1; background: #F1F5F9; display: flex; align-items: center; justify-content: center;">' +
                          '<img src="' + escapeHtml(photo) + '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display=\'none\'; $(this).next().show();">' +
                          '<span style="display:none; font-size: 11px; font-weight: 800; color: #1E3A5F;">' + escapeHtml(inits) + '</span>' +
                        '</div>';
          } else {
            photoHtml = '<div style="width: 34px; height: 34px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; border: 1px solid #DBEAFE; font-weight: 800; font-size: 11.5px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">' + escapeHtml(inits) + '</div>';
          }

          return '<div style="display: flex; align-items: center; gap: 10px; padding: 2px 0;">' +
                   photoHtml +
                   '<div style="line-height: 1.25;">' +
                     '<div style="font-weight: 700; color: #0F172A; font-size: 13px;">' + escapeHtml(nom) + '</div>' +
                     '<div style="font-size: 11px; color: #64748B;"><code style="color: #1E3A5F; font-weight: 700; background: #F1F5F9; padding: 1px 4px; border-radius: 3px;">' + escapeHtml(mat) + '</code> &bull; ' + escapeHtml(classe) + '</div>' +
                   '</div>' +
                 '</div>';
        }
      });
    }
    if (preselectedCode) {
      $('#modal_select_inscription').val(preselectedCode).trigger('change');
    }
    if (window.lucide) lucide.createIcons();
  }

  function closeEncaissementModal() {
    $('#modal-encaisser-scolarite').hide();
    $('#form-encaissement-scolarite')[0].reset();
    if ($.fn.select2) {
      $('#modal_select_inscription').val('').trigger('change.select2');
    }
    $('#encaisse-stu-photo').hide().attr('src', '');
    $('#encaisse-stu-avatar').show().text('ET');
    $('#modal_select_tranche').html('');
    $('#tranche-hint-info').html('');
    $('#encaisse-student-summary-card').hide();
    $('#encaisse-payment-inputs-section').hide();
    currentStudentSummary = null;
  }

  $('#btn-open-encaissement-modal').on('click', function() {
    openEncaissementModal();
  });

  $('.btn-close-modal-encaissement').on('click', function() {
    closeEncaissementModal();
  });

  $('.btn-close-modal-success').on('click', function() {
    $('#modal-recu-paiement-success').hide();
  });

  $('#modal-encaisser-scolarite').on('click', function(e) {
    if ($(e.target).is('#modal-encaisser-scolarite')) {
      closeEncaissementModal();
    }
  });

  $('#modal-recu-paiement-success').on('click', function(e) {
    if ($(e.target).is('#modal-recu-paiement-success')) {
      $('#modal-recu-paiement-success').hide();
    }
  });

  // Détection du paramètre URL ?action=encaissement
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('action') === 'encaissement') {
    var preCode = urlParams.get('inscription_code') || '';
    openEncaissementModal(preCode);
  }

  // Chargement dynamique de la synthèse financière au choix de l'étudiant
  $('#modal_select_inscription').on('change', function() {
    var val = $(this).val();
    if (!val) {
      $('#encaisse-student-summary-card').slideUp(150);
      $('#encaisse-payment-inputs-section').slideUp(150);
      currentStudentSummary = null;
      return;
    }

    $('#btn-submit-encaissement').prop('disabled', true);
    
    $.ajax({
      url: '<?= RACINE ?>paiement/getStudentFinancialSummary',
      type: 'GET',
      data: { inscription_code: val },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          currentStudentSummary = d;

          // Remplir la fiche étudiant
          var initials = (d.nom_complet || 'ET').split(' ').filter(function(x){return x;}).map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          $('#encaisse-stu-avatar').text(initials || 'ET');
          $('#encaisse-stu-nom').text(d.nom_complet);
          $('#encaisse-stu-mat').text(d.matricule);
          $('#encaisse-stu-classe').text(d.classe + (d.filiere ? ' - ' + d.filiere : ''));
          $('#encaisse-stu-regime-badge').text(d.affectation_label || 'Non Défini');
          if (d.affectation_etat === 'affecte') {
            $('#encaisse-stu-regime-badge').css({'background': '#DCFCE7', 'color': '#15803D', 'border-color': '#86EFAC'});
          } else {
            $('#encaisse-stu-regime-badge').css({'background': '#EFF6FF', 'color': '#1E3A5F', 'border-color': '#BFDBFE'});
          }

          // Affichage dynamique de la photo de l'étudiant
          if (d.photo_url && d.photo_url.trim() !== '') {
            $('#encaisse-stu-photo')
              .attr('src', d.photo_url)
              .off('load error')
              .on('load', function() {
                $(this).show();
                $('#encaisse-stu-avatar').hide();
              })
              .on('error', function() {
                $(this).hide();
                $('#encaisse-stu-avatar').show();
              });
          } else {
            $('#encaisse-stu-photo').hide().attr('src', '');
            $('#encaisse-stu-avatar').show();
          }

          // Compteurs
          $('#encaisse-val-scolarite').text(d.scolarite_due_fmt);
          $('#encaisse-val-paye').text(d.total_paye_fmt);
          $('#encaisse-val-reste').text(d.solde_restant_fmt);

          // Remplir les tranches
          var trHtml = '';
          if (d.tranches && d.tranches.length > 0) {
            d.tranches.forEach(function(tr) {
              var suffix = tr.is_soldee ? ' (SOLDÉE)' : '';
              trHtml += '<option value="' + escapeHtml(tr.code_tranche) + '" data-reste="' + tr.reste_a_payer + '" data-soldee="' + (tr.is_soldee ? '1' : '0') + '" data-limite="' + escapeHtml(tr.date_limite_fmt || '') + '" ' + (tr.is_soldee ? 'style="color:#94A3B8;"' : '') + '>' +
                        escapeHtml(tr.libelle_tranche) + ' - ' + tr.montant_tranche_fmt + suffix +
                        '</option>';
            });
          }
          $('#modal_select_tranche').html(trHtml);

          if (d.suggested_tranche_code) {
            $('#modal_select_tranche').val(d.suggested_tranche_code);
          } else if (d.tranches && d.tranches.length > 0) {
            // Sélection automatique de la première tranche par ordre de création
            $('#modal_select_tranche').val(d.tranches[0].code_tranche);
          }
          
          $('#modal_select_tranche').trigger('change');

          $('#encaisse-student-summary-card').stop(true, true).slideDown(200);
          $('#encaisse-payment-inputs-section').stop(true, true).slideDown(200);

          if (d.solde_restant <= 0) {
            $('#montant-max-hint').html('<span style="color:#15803D; font-weight:800;">🎉 Scolarité intégralement soldée pour cet étudiant. Aucun versement supplémentaire attendu.</span>');
            $('#btn-submit-encaissement').prop('disabled', true);
          } else {
            $('#btn-submit-encaissement').prop('disabled', false);
          }

          if (window.lucide) lucide.createIcons();
        } else {
          alert(res.message || 'Impossible de récupérer la situation financière de cet étudiant.');
        }
      },
      error: function() {
        alert('Erreur lors de la communication avec le serveur.');
      }
    });
  });

  // Verrouillage strict du champ Tranche (Lecture seule, non modifiable)
  $('#modal_select_tranche').on('mousedown keydown focus click touchstart', function(e) {
    e.preventDefault();
    return false;
  });

  // Changement de tranche (Calcul et verrouillage readonly du montant)
  $('#modal_select_tranche').on('change', function() {
    var $opt = $(this).find('option:selected');
    var reste = parseFloat($opt.data('reste') || 0);
    var isSoldee = $opt.data('soldee') === '1' || $opt.data('soldee') === 1;
    var limite = $opt.data('limite');

    if (isSoldee) {
      $('#montant-max-hint').html('<span style="color:#DC2626; font-weight:700;">⚠️ Cette tranche est déjà totalement soldée. Aucun versement requis.</span>');
      $('#input-montant-versement').val(0);
      $('#btn-submit-encaissement').prop('disabled', true);
    } else {
      $('#montant-max-hint').html('Montant fixé par la tranche : <strong id="lbl-max-autorise" style="color: #15803D;">' + Number(reste).toLocaleString('fr-FR') + ' FCFA</strong>');
      $('#input-montant-versement').val(reste);
      $('#btn-submit-encaissement').prop('disabled', false);
    }

    if (limite && limite !== 'Non définie') {
      $('#tranche-hint-info').html('📅 Date d\'échéance : <strong>' + escapeHtml(limite) + '</strong>');
    } else {
      $('#tranche-hint-info').html('');
    }
    if (window.lucide) lucide.createIcons();
  });

  // Soumission AJAX du formulaire d'encaissement
  $('#form-encaissement-scolarite').on('submit', function(e) {
    e.preventDefault();
    
    var montant = parseFloat($('#input-montant-versement').val() || 0);
    if (montant <= 0) {
      alert('Le montant du versement doit être supérieur à 0 FCFA.');
      return;
    }

    var $opt = $('#modal_select_tranche').find('option:selected');
    var resteTranche = parseFloat($opt.data('reste') || 0);
    if (resteTranche > 0 && montant > resteTranche) {
      alert('Le montant saisi (' + Number(montant).toLocaleString('fr-FR') + ' FCFA) dépasse le solde restant de la tranche (' + Number(resteTranche).toLocaleString('fr-FR') + ' FCFA).');
      return;
    }

    var $btn = $('#btn-submit-encaissement');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:6px;"></span> Validation en cours...');

    $.ajax({
      url: '<?= RACINE ?>paiement/add',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1) {
          closeEncaissementModal();
          table.ajax.reload(null, false);
          refreshKpis();

          // Afficher le modal de confirmation & impression
          var codePaiement = res.code_paiement || 'PAI-CONFIRME';
          var idCrypte = res.encrypted_id || res.id_paiement;
          $('#success-recu-num').text(codePaiement);
          $('#success-recu-montant').text(Number(montant).toLocaleString('fr-FR') + ' FCFA Encaissé');
          $('#success-recu-print-link').attr('href', window.RACINE + 'paiement/details/' + idCrypte + '?print=1');
          $('#modal-recu-paiement-success').css('display', 'flex');

          if (window.lucide) lucide.createIcons();
        } else {
          alert(res.message || 'Erreur lors de l\'encaissement.');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser');
        if (window.lucide) lucide.createIcons();
        var errMsg = 'Une erreur est survenue lors de la communication avec le serveur.';
        try {
          var r = JSON.parse(xhr.responseText);
          if (r && r.message) errMsg = r.message;
        } catch(e) {}
        alert(errMsg);
      }
    });
  });

  // GESTION MODALE OUVERTURE DE CAISSE DU JOUR
  $(document).on('click', '.btn-open-session-caisse', function(e) {
    e.preventDefault();
    $('#form-open-session-caisse')[0].reset();
    $('#session_date').val(new Date().toISOString().split('T')[0]);
    $('#session_fond_initial').val(0);
    $('#modal-open-session-caisse').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#session_fond_initial').focus().select(); }, 100);
  });

  $(document).on('click', '.btn-close-modal-session', function() {
    $('#modal-open-session-caisse').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-open-session-caisse')) {
      $('#modal-open-session-caisse').hide();
    }
  });

  $('#form-open-session-caisse').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-session');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:16px;height:16px;" class="lucide-spin"></i> Ouverture...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: window.RACINE + 'session_caisse/add',
      type: 'POST',
      data: $(this).serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
        if (window.lucide) lucide.createIcons();
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Session de caisse ouverte avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Session de caisse ouverte avec succès');
          $('#modal-open-session-caisse').hide();
          setTimeout(function() {
            window.location.reload();
          }, 600);
        } else {
          if (typeof showToast === 'function') showToast(res.message || 'Erreur lors de l\'ouverture de la session', 'error');
          else if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'ouverture de la session');
          else alert(res.message || 'Erreur lors de l\'ouverture de la session');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width:16px;height:16px;"></i> Confirmer l\'Ouverture');
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur lors de l\'ouverture de la session';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
        else alert(msg);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
