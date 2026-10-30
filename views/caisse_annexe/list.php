<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$canRecord = $canRecord ?? false;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE D'IMPRESSION (VISIBLE UNIQUEMENT À L'IMPRESSION) -->
      <div id="print-header-banner" style="margin-bottom: 16px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">GEICG - GROUPE ÉCOLE D'INGÉNIEURS ET DE COMMERCE DE GAGNOA</h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">CAISSE ANNEXE - BILAN DES FRAIS ANNEXES & ENCAISSEMENTS</div>
            <div style="font-size: 11px; color: #475569; margin-top: 3px;" id="print-filter-summary-text">
              Année Académique : All &bull; Niveau : Tous &bull; Classe : Toutes
            </div>
          </div>
          <div style="text-align: right; font-size: 11px; color: #475569;">
            <div>Date d'impression : <strong><?= date('d/m/Y H:i') ?></strong></div>
            <div style="margin-top: 2px;">Imprimé par : <strong><?= htmlspecialchars(trim((Auth::fullName() ?: 'Caissier'))) ?></strong></div>
          </div>
        </div>
        
        <!-- Synthèse Financière Impression -->
        <div style="margin-top: 10px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #0F172A;">
          <span>Total Annexes Encaissé : <strong style="color: #B45309;" id="print-kpi-total"><?= number_format((float)($stats['total_encaisse'] ?? 0), 0, ',', ' ') ?> FCFA</strong></span>
          <span>Espèces : <strong id="print-kpi-especes"><?= number_format((float)($stats['encaisse_especes'] ?? 0), 0, ',', ' ') ?> FCFA</strong></span>
          <span>Mobile Money : <strong id="print-kpi-mobile-money"><?= number_format((float)($stats['encaisse_mobile_money'] ?? 0), 0, ',', ' ') ?> FCFA</strong></span>
          <span>Budget Attendu : <strong id="print-kpi-budget"><?= number_format((float)($stats['total_frais_annexes_attendus'] ?? 0), 0, ',', ' ') ?> FCFA</strong></span>
          <span>Reste Dû : <strong style="color: #DC2626;" id="print-kpi-reste"><?= number_format((float)($stats['attente_frais_annexes'] ?? 0), 0, ',', ' ') ?> FCFA</strong></span>
        </div>
      </div>

      <!-- HEADER DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="background: #FEF3C7; color: #B45309; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; border: 1px solid #FDE68A;">
              Guichet Annexe
            </span>
            <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Caisse Annexe & Bilan des Frais Annexes</h1>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Journal des encaissements, suivi du recouvrement et bilan comptable des frais annexes</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
          <a href="<?= RACINE ?>fraisAnnexe/list" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 16px; text-decoration: none;" title="Consulter et configurer la grille tarifaire des frais annexes">
            <i data-lucide="package-check" style="width: 18px; height: 18px; color: #B45309;"></i> Grille des Tarifs
          </a>

          <button onclick="printRegistry()" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;" title="Imprimer le bilan des frais annexes">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </button>

          <?php if (!empty($isCaisseOuverte)): ?>
            <?php if (!empty($canCloseCaisse)): ?>
              <a href="<?= RACINE ?>session_caisse/cloturer/<?= !empty($encryptedSessionId) ? $encryptedSessionId : '' ?>" class="btn btn-danger" style="background: #DC2626; border-color: #DC2626; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(220,38,38,0.2); text-decoration: none;" title="Clôturer la session de caisse du jour (<?= htmlspecialchars($activeSession['code_session'] ?? '') ?>)">
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Fermeture caisse
              </a>
            <?php endif; ?>
          <?php else: ?>
            <?php if (!empty($canOpenCaisse)): ?>
              <button type="button" class="btn btn-primary btn-open-session-caisse" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(30,58,95,0.2); cursor: pointer;" title="Ouvrir la session de caisse du jour">
                <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Ouverture caisse
              </button>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($canRecord)): ?>
          <button type="button" id="btn-open-encaissement-annexe-modal" class="btn btn-warning" style="background: #D97706; border-color: #D97706; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 4px rgba(217,119,6,0.25); cursor: pointer;">
            <i data-lucide="wallet-cards" style="width: 18px; height: 18px;"></i> Encaisser Frais Annexe
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
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['statut_annee'] ?? '') === STATUTS::ACTIF ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Type de Filière -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="git-branch" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Type de Filière :
            </label>
            <select id="filter-type-filiere" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Tous les types --</option>
              <option value="TERTIAIRE">Tertiaire</option>
              <option value="INDUSTRIELLE">Industrielle</option>
            </select>
          </div>

          <!-- Filtre Niveau -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="layers" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Niveau d'Études :
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

          <!-- Filtre Mode de Règlement -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="credit-card" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Mode de Paiement :
            </label>
            <select id="filter-mode" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Tous les modes --</option>
              <option value="espece">💵 Espèces (Guichet)</option>
              <option value="mobile_money">📱 Mobile Money</option>
              <option value="cheque">🏦 Chèque</option>
              <option value="virement">🏛️ Virement</option>
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

      <!-- SECTION 1 : ENCAISSEMENTS & RECOUVREMENT DES ANNEXES (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <span class="live-status-pulse"></span><i data-lucide="landmark" style="width: 15px; height: 15px; color: #B45309;"></i> Encaissements de la Caisse Annexe
        </h4>
      </div>
      
      <div class="card-kpi-container">
        
        <!-- 1. Total Encaissé Frais Annexes -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FDE68A; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px;">Total Annexes Encaissé</div>
            <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 3px;">
              <span id="kpi-total-encaisse"><?= number_format((float)($stats['total_encaisse'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Taux Recouvrement : <strong style="color: #B45309;"><span id="kpi-taux-recouvrement-fa"><?= number_format((float)($stats['taux_recouvrement_fa'] ?? 0), 1, ',', ' ') ?></span>%</strong>
            </div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="wallet-cards" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 2. Encaissements Jour -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BFDBFE; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Jour (Annexes)</div>
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
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Espèces (Caisse Guichet)</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-encaisse-especes"><?= number_format((float)($stats['encaisse_especes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Frais annexes en liquide</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="banknote" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 4. Reste à Recouvrer -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FCA5A5; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #B91C1C; text-transform: uppercase; letter-spacing: 0.5px;">Reste à Recouvrer (Annexes)</div>
            <div style="font-size: 18px; font-weight: 900; color: #B91C1C; margin-top: 3px;">
              <span id="kpi-attente-frais-annexes"><?= number_format((float)($stats['attente_frais_annexes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Montant restant exigible</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEE2E2; color: #B91C1C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- SECTION 2 : RÉPARTITION PAR FILIÈRE & MODES DIGITAUX (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px; margin-top: 20px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="pie-chart" style="width: 15px; height: 15px; color: #0284C7;"></i> Répartition Filières & Canaux de Règlement
        </h4>
      </div>

      <div class="card-kpi-container">
        
        <!-- 5. Filière Industrielle -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0284C7; text-transform: uppercase; letter-spacing: 0.5px;">Filière Industrielle</div>
            <div style="font-size: 18px; font-weight: 900; color: #0369A1; margin-top: 3px;">
              <span id="kpi-encaisse-industrielle"><?= number_format((float)($stats['encaisse_industrielle'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Annexes filière industrielle</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="cpu" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 6. Filière Tertiaire -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #4338CA; text-transform: uppercase; letter-spacing: 0.5px;">Filière Tertiaire</div>
            <div style="font-size: 18px; font-weight: 900; color: #3730A3; margin-top: 3px;">
              <span id="kpi-encaisse-tertiaire"><?= number_format((float)($stats['encaisse_tertiaire'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Annexes filière tertiaire</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="briefcase" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 7. Mobile Money & Digital -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #0E7490; text-transform: uppercase; letter-spacing: 0.5px;">Mobile Money / Banque</div>
            <div style="font-size: 18px; font-weight: 900; color: #0E7490; margin-top: 3px;">
              <span id="kpi-encaisse-mobile-money"><?= number_format((float)(($stats['encaisse_mobile_money'] ?? 0) + ($stats['encaisse_banque'] ?? 0)), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Paiements digitaux & virements</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #CFFAFE; color: #0E7490; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="smartphone" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 8. Budget Frais Annexes Attendus -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BFDBFE; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.5px;">Total Budget Exigible</div>
            <div style="font-size: 18px; font-weight: 900; color: #1E3A8A; margin-top: 3px;">
              <span id="kpi-total-frais-annexes-attendus"><?= number_format((float)($stats['total_frais_annexes_attendus'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">FCFA</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Exercice : <strong style="color: #1E40AF;" id="kpi-exercice-session-libelle"><?= htmlspecialchars($stats['annee_exercice_libelle'] ?? (Session::get('annee_active_libelle', 'En session'))) ?></strong>
            </div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: #1E40AF; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- SECTION 3 : CONFORMITÉ & SANTÉ FINANCIÈRE DES ÉTUDIANTS (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px; margin-top: 20px;">
        <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="check-circle-2" style="width: 15px; height: 15px; color: #15803D;"></i> Conformité & Règlements des Étudiants
        </h4>
      </div>

      <div class="card-kpi-container">
        
        <!-- 9. Étudiants Soldés Annexes -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Annexes 100% Soldés</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-eleves-soldes"><?= number_format((int)($stats['eleves_soldes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Frais annexes en règle</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 10. Étudiants en Attente Annexes -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FECACA; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #DC2626; text-transform: uppercase; letter-spacing: 0.5px;">Annexes en Attente</div>
            <div style="font-size: 18px; font-weight: 900; color: #DC2626; margin-top: 3px;">
              <span id="kpi-eleves-non-soldes"><?= number_format((int)($stats['eleves_non_soldes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">À régulariser au guichet</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-x" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 11. Total Reçus Émis -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FEF3C7; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px;">Quittances & Reçus Émis</div>
            <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 3px;">
              <span id="kpi-total-recus"><?= number_format((int)($stats['total_recus'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">versement(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Transactions enregistrées</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="receipt" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 12. Effectif Total Inscrits -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E9D5FF; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Total Effectif Suivi</div>
            <div style="font-size: 18px; font-weight: 900; color: #7E22CE; margin-top: 3px;">
              <span id="kpi-total-inscrits"><?= number_format((int)($stats['total_inscrits'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Effectif académique sélectionné</div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #F3E8FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="graduation-cap" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- TABLEAU DU JOURNAL DE LA CAISSE ANNEXE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="list-ordered" style="width: 18px; height: 18px; color: #B45309;"></i> Journal des Règlements de Frais Annexes
          </h3>
          <span style="font-size: 12px; font-weight: 600; color: #64748B;">
            Historique ordonné par date décroissante
          </span>
        </div>
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-caisse-annexe" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 45px; text-align: center;">#</th>
                <th style="padding: 12px; width: 120px;">Réf. Reçu</th>
                <th style="padding: 12px; width: 120px;">Date & Heure</th>
                <th style="padding: 12px; width: 110px;">Matricule</th>
                <th style="padding: 12px;">Étudiant</th>
                <th style="padding: 12px; width: 110px;">Classe</th>
                <th style="padding: 12px;">Filière</th>
                <th style="padding: 12px;">Motif / Libellé</th>
                <th style="padding: 12px;">Mode</th>
                <th style="padding: 12px; width: 130px; text-align: right;">Montant Versé</th>
                <th style="padding: 12px; width: 90px; text-align: center;">Actions</th>
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
            <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0F172A;">Le Caissier Annexe</div>
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
<!-- MODAL SUR MESURE : ENCAISSEMENT DIRECT DE FRAIS ANNEXES                  -->
<!-- ========================================================================= -->
<div id="modal-encaisser-annexe" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 820px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F233D 100%); color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #D97706;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(217,119,6,0.25); border: 1.5px solid #FBBF24; display: flex; align-items: center; justify-content: center;">
          <i data-lucide="wallet-cards" style="width: 22px; height: 22px; color: #FBBF24;"></i>
        </div>
        <div>
          <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Encaisser un Versement de Frais Annexes</h3>
          <div style="font-size: 12px; color: #94A3B8; margin-top: 2px;">Caisse Annexe &bull; Tarification Exigible</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-encaissement" style="background: transparent; border: none; color: #FFFFFF; font-size: 26px; cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
    </div>

    <!-- Corps du modal (Formulaire) -->
    <form id="form-encaissement-annexe" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
      <div style="padding: 22px 24px; background: #F8FAFC; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
        
        <?php if (empty($isCaisseOuverte)): ?>
          <div id="caisse-warning-notice" style="background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: 10px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 10px;">
            <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #D97706; flex-shrink: 0; margin-top: 1px;"></i>
            <div style="font-size: 12.5px; color: #92400E; line-height: 1.4;">
              <strong>Session de caisse fermée :</strong> Aucune session de caisse n'est ouverte pour aujourd'hui. Pour les règlements en espèces, veuillez d'abord <a href="javascript:void(0)" class="btn-open-session-caisse" style="color: #1E3A5F; font-weight: 800; text-decoration: underline;">ouvrir une session de caisse</a>. Les modes Mobile Money, Chèque et Virement sont toutefois immédiatement acceptés.
            </div>
          </div>
        <?php endif; ?>

        <!-- 1. Sélection de l'étudiant -->
        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
          <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">
            1. Sélectionner l'Étudiant <span class="text-danger">*</span>
          </label>
          <select id="modal_select_inscription" name="inscription_code" class="form-control select2" style="width: 100%;" required>
            <option value="">-- Tapez le nom, matricule ou classe de l'étudiant --</option>
            <?php foreach (($inscriptions ?? []) as $in): ?>
              <?php 
                $photoPath = !empty($in['photo_inscription']) ? trim($in['photo_inscription']) : (!empty($in['photo_etudiant']) ? trim($in['photo_etudiant']) : '');
                $photoUrl = !empty($photoPath) ? RACINE . ltrim($photoPath, '/') : '';
              ?>
              <option value="<?= htmlspecialchars($in['code_inscription']) ?>" 
                      data-nom="<?= htmlspecialchars(trim(($in['nom_etudiant'] ?? '') . ' ' . ($in['prenom_etudiant'] ?? ''))) ?>"
                      data-matricule="<?= htmlspecialchars($in['matricule_etudiant'] ?? '') ?>"
                      data-classe="<?= htmlspecialchars($in['libelle_classe'] ?? '') ?>"
                      data-photo="<?= htmlspecialchars($photoUrl) ?>">
                <?= htmlspecialchars(trim(($in['nom_etudiant'] ?? '') . ' ' . ($in['prenom_etudiant'] ?? ''))) ?> (<?= htmlspecialchars($in['matricule_etudiant'] ?? '-') ?> - <?= htmlspecialchars($in['libelle_classe'] ?? '-') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 2. Synthèse Frais Annexes de l'Étudiant (Affichage AJAX dynamique) -->
        <div id="encaisse-student-summary-card" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1.5px solid #FDE68A; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.08);">
          
          <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px;">
              <div style="position: relative; width: 50px; height: 50px; border-radius: 12px; overflow: hidden; background: #FEF3C7; border: 2px solid #FDE68A; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                <img id="encaisse-stu-photo" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;" alt="Photo">
                <span id="encaisse-stu-avatar" style="font-size: 16px; font-weight: 900; color: #B45309;">ET</span>
              </div>
              <div>
                <div id="encaisse-stu-nom" style="font-size: 15px; font-weight: 800; color: #0F172A;">-</div>
                <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
                  Matricule : <code id="encaisse-stu-mat" style="color: #1E3A5F; font-weight: 700; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">-</code> &bull; Classe : <strong id="encaisse-stu-classe" style="color: #0F172A;">-</strong>
                </div>
              </div>
            </div>
            <div id="encaisse-stu-filiere-badge" style="font-size: 11.5px; font-weight: 800; padding: 4px 10px; border-radius: 6px; background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A;">
              -
            </div>
          </div>

          <!-- 3 Compteurs Financiers Annexes -->
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 14px;">
            <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #B45309; text-transform: uppercase;" id="encaisse-lbl-exigible">Tarif Frais Annexes Exigible</div>
              <div id="encaisse-val-exigible" style="font-size: 16px; font-weight: 900; color: #B45309; margin-top: 2px;">0 FCFA</div>
            </div>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Déjà Encaissé</div>
              <div id="encaisse-val-paye" style="font-size: 16px; font-weight: 900; color: #15803D; margin-top: 2px;">0 FCFA</div>
            </div>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #DC2626; text-transform: uppercase;">Solde Restant Dû</div>
              <div id="encaisse-val-reste" style="font-size: 16px; font-weight: 900; color: #DC2626; margin-top: 2px;">0 FCFA</div>
            </div>
          </div>

        </div>

        <!-- 3. Saisie du versement -->
        <div id="encaisse-payment-inputs-section" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Montant à verser -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">
                2. Montant du Versement (FCFA) <span class="text-danger">*</span>
              </label>
              <div style="position: relative;">
                <input type="number" step="1" id="input-montant-versement" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 20px; color: #B45309; padding-right: 70px; border-radius: 10px; background-color: #FFFBEB; border: 2px solid #FDE68A;" placeholder="0" required>
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #B45309; font-size: 14px;">FCFA</span>
              </div>
              <div id="montant-max-hint" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">
                Montant suggéré selon le reste à payer.
              </div>
            </div>

            <!-- Mode de règlement -->
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Mode de Règlement <span class="text-danger">*</span></label>
              <select id="modal_select_mode" name="mode_paiement" class="form-control" style="border-radius: 8px; font-weight: 700;" required>
                <option value="espece" selected>💵 Espèces (Guichet)</option>
                <option value="mobile_money">📱 Mobile Money (Wave / OM / MTN / Moov)</option>
                <option value="cheque">🏦 Chèque Bancaire</option>
                <option value="virement">🏛️ Virement Bancaire</option>
              </select>
            </div>

            <!-- Référence / Transaction -->
            <div>
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Réf. Transaction / N° Chèque</label>
              <input type="text" name="reference_paiement" id="input_ref_paiement" class="form-control" style="border-radius: 8px;" placeholder="Ex: TRX-ANNEXE-0912">
            </div>

            <!-- Observations / Remarques -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Observations / Notes (facultatif)</label>
              <input type="text" name="observations" class="form-control" style="border-radius: 8px;" placeholder="Ex: Règlement frais d'inscription annexe...">
            </div>

          </div>
        </div>

      </div>

      <!-- Pied de modal -->
      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn btn-secondary btn-close-modal-encaissement" style="font-weight: 700; border-radius: 8px; padding: 10px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-encaissement" class="btn btn-warning" style="font-weight: 800; border-radius: 8px; padding: 10px 26px; background: #D97706; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(217,119,6,0.3);">
          <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider le Règlement
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUCCÈS & REÇU DU PAIEMENT ANNEXE                                   -->
<!-- ========================================================================= -->
<div id="modal-recu-annexe-success" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.7); backdrop-filter: blur(4px); z-index: 10000; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; text-align: center; padding: 30px 24px;">
    
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #FEF3C7; border: 2px solid #FDE68A; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
      <i data-lucide="check" style="width: 32px; height: 32px; color: #D97706;"></i>
    </div>

    <h3 style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0 0 6px 0;">Frais Annexe Encaissé avec Succès !</h3>
    <p style="font-size: 13.5px; color: #64748B; margin: 0 0 16px 0;">Le versement a été validé et consigné dans le journal de la Caisse Annexe.</p>

    <div style="background: #F8FAFC; border: 1.5px dashed #CBD5E1; border-radius: 10px; padding: 14px; margin-bottom: 22px;">
      <div style="font-size: 11.5px; color: #64748B; font-weight: 700; text-transform: uppercase;">N° Quittance Caisse Annexe</div>
      <div id="success-recu-num" style="font-size: 20px; font-weight: 900; color: #B45309; font-family: monospace; margin: 4px 0;">REC-FA-XXXXXX</div>
    </div>

    <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
      <button type="button" class="btn btn-secondary btn-close-modal-success" style="font-weight: 700; border-radius: 8px; padding: 10px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
        Fermer
      </button>
      <a id="success-recu-print-link" href="#" target="_blank" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 22px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu
      </a>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL OUVERTURE SESSION DE CAISSE DU JOUR                                -->
<!-- ========================================================================= -->
<div id="modal-open-session-caisse" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <i data-lucide="unlock" style="width: 20px; height: 20px; color: #60A5FA;"></i>
        <h4 style="font-size: 15px; font-weight: 800; margin: 0; color: #FFFFFF;">Ouverture de Caisse Journalière</h4>
      </div>
      <button type="button" class="btn-close-modal-session" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <form id="form-open-session-caisse" style="padding: 20px;">
      <div class="form-group" style="margin-bottom: 14px;">
        <label style="font-weight: 700; font-size: 12px; color: #0F172A; margin-bottom: 4px; display: block;">Date de Caisse</label>
        <input type="date" id="session_date" name="date_session" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
      </div>
      <div class="form-group" style="margin-bottom: 14px;">
        <label style="font-weight: 700; font-size: 12px; color: #0F172A; margin-bottom: 4px; display: block;">Fond Initial de Caisse (FCFA)</label>
        <input type="number" step="100" id="session_fond_initial" name="fond_initial" class="form-control" value="0" required>
      </div>
      <div class="form-group" style="margin-bottom: 18px;">
        <label style="font-weight: 700; font-size: 12px; color: #0F172A; margin-bottom: 4px; display: block;">Observations</label>
        <textarea name="observations_ouverture" class="form-control" rows="2" placeholder="Notes d'ouverture..."></textarea>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-close-modal-session" style="font-weight: 700;">Annuler</button>
        <button type="submit" id="btn-submit-session" class="btn btn-primary" style="background: #1E3A5F; font-weight: 800;">Confirmer l'Ouverture</button>
      </div>
    </form>
  </div>
</div>

<?php 
ViewContext::setScripts(['public/assets/js/modules/caisse_annexe.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>
