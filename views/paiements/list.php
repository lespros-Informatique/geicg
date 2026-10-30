<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$isCaisseOuverte = $isCaisseOuverte ?? false;
$activeSession = $activeSession ?? null;
$encryptedSessionId = $encryptedSessionId ?? '';
$canRecord = $canRecord ?? false;
$canOpenCaisse = $canOpenCaisse ?? false;
$canCloseCaisse = $canCloseCaisse ?? false;
$canCancelPaiement = $canCancelPaiement ?? (isset($canAccess) && is_callable($canAccess) && $canAccess([PERMISSIONS::CANCEL_PAIEMENTS]));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE D'IMPRESSION  (VISIBLE UNIQUEMENT À L'IMPRESSION) -->
      <div id="print-header-banner" style="margin-bottom: 16px; border-bottom: 2px solid #1E3A5F; padding-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="font-size: 16px; font-weight: 900; color: #1E3A5F; margin: 0; text-transform: uppercase;">GEICG - GROUPE ÉCOLE D'INGÉNIEURS ET DE COMMERCE DE GAGNOA</h2>
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-top: 3px;">JOURNAL DES ENCAISSEMENTS DE CAISSE & SCOLARITÉ</div>
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

          <!-- Filtre Canal d'Encaissement -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="split" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Canal d'Encaissement :
            </label>
            <select id="filter-canal" class="form-control" style="height: 42px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 4px 10px; font-size: 13px; font-weight: 600; color: #1E293B; background-color: #FFFFFF; width: 100%; box-sizing: border-box;">
              <option value="ALL">-- Tous les canaux --</option>
              <option value="guichet">Guichet Physique (Caisse)</option>
              <option value="en_ligne">En Ligne (Portail Étudiant)</option>
            </select>
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

      <!-- SECTION 2 : RÉPARTITION PAR RÉGIME & PÉRIODE (4 CARTES) -->
      <div class="kpi-section-title" style="margin-bottom: 8px; margin-top: 20px;">
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
        <div onclick="window.location.href='<?= RACINE ?>paiement/comptabilite_etudiants?statut_paiement=solde'" class="card kpi-card kpi-card-clickable" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; cursor: pointer;" title="Cliquer pour afficher la liste des étudiants soldés">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants Soldés (100%)</div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 3px;">
              <span id="kpi-eleves-soldes"><?= number_format((int)($stats['eleves_soldes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Scolarité entièrement payée &bull; <span style="color: #15803D; font-weight: 700;">Voir liste &rarr;</span></div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 10. Étudiants en Acompte -->
        <div onclick="window.location.href='<?= RACINE ?>paiement/comptabilite_etudiants?statut_paiement=partiel'" class="card kpi-card kpi-card-clickable" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FEF3C7; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; cursor: pointer;" title="Cliquer pour afficher la liste des étudiants en acompte">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #D97706; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants en Acompte</div>
            <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 3px;">
              <span id="kpi-eleves-acomptes"><?= number_format((int)($stats['eleves_acomptes'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Versements partiels &bull; <span style="color: #D97706; font-weight: 700;">Voir liste &rarr;</span></div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 11. Frais d'inscription non réglé -->
        <div onclick="window.location.href='<?= RACINE ?>paiement/comptabilite_etudiants?statut_paiement=non_paye'" class="card kpi-card kpi-card-clickable" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #FECACA; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; cursor: pointer;" title="Cliquer pour afficher la liste des étudiants non réglés">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #E11D48; text-transform: uppercase; letter-spacing: 0.5px;">Frais d'inscription non réglé</div>
            <div style="font-size: 18px; font-weight: 900; color: #BE123C; margin-top: 3px;">
              <span id="kpi-eleves-non-payeurs"><?= number_format((int)($stats['eleves_non_payeurs'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">0 FCFA versé &bull; <span style="color: #E11D48; font-weight: 700;">Voir liste &rarr;</span></div>
          </div>
          <div class="kpi-icon-box" style="width: 40px; height: 40px; border-radius: 10px; background: #FFE4E6; color: #E11D48; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-x" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- 12. Total Inscrits -->
        <div onclick="window.location.href='<?= RACINE ?>paiement/comptabilite_etudiants'" class="card kpi-card kpi-card-clickable" style="background: #FFFFFF; border-radius: 12px; padding: 16px 18px; border: 1px solid #E9D5FF; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; cursor: pointer;" title="Cliquer pour afficher la liste complète des étudiants inscrits">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Total Inscrits</div>
            <div style="font-size: 18px; font-weight: 900; color: #7E22CE; margin-top: 3px;">
              <span id="kpi-total-inscrits"><?= number_format((int)($stats['total_inscrits'] ?? 0), 0, ',', ' ') ?></span> <span style="font-size: 10.5px; font-weight: 700;">étudiant(s)</span>
            </div>
            <div style="font-size: 10.5px; font-weight: 600; color: #64748B; margin-top: 2px;">Effectif académique suivi &bull; <span style="color: #7E22CE; font-weight: 700;">Voir liste &rarr;</span></div>
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
                <th style="padding: 12px;">Type Transaction</th>
                <th style="padding: 12px; width: 150px; text-align: right;">Montant Versé</th>
                <th style="padding: 12px; width: 130px; text-align: center;">Actions</th>
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

        <!-- Alerte dynamique d'erreur d'encaissement intégrée au modal -->
        <div id="encaisse-error-dynamic-notice" style="display: none; background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 10px; padding: 12px 16px;">
          <div style="display: flex; align-items: flex-start; gap: 10px;">
            <i data-lucide="alert-circle" style="width: 20px; height: 20px; color: #DC2626; flex-shrink: 0; margin-top: 1px;"></i>
            <div style="flex: 1;">
              <div id="encaisse-error-dynamic-title" style="font-size: 13px; font-weight: 800; color: #991B1B; margin-bottom: 2px;">Attention : Encaissement non validé</div>
              <div id="encaisse-error-dynamic-text" style="font-size: 12.5px; color: #B91C1C; line-height: 1.45;"></div>
              <div id="encaisse-error-dynamic-action" style="margin-top: 8px; display: none;">
                <a href="javascript:void(0)" class="btn-open-session-caisse" style="display: inline-flex; align-items: center; gap: 6px; background: #1E3A5F; color: #FFFFFF; font-size: 11.5px; font-weight: 700; padding: 6px 12px; border-radius: 6px; text-decoration: none;">
                  <i data-lucide="unlock" style="width: 14px; height: 14px;"></i> Ouvrir la session de caisse maintenant
                </a>
              </div>
            </div>
            <button type="button" onclick="$('#encaisse-error-dynamic-notice').slideUp(150);" style="background: transparent; border: none; color: #991B1B; font-size: 18px; cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
          </div>
        </div>

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
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 14px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Scolarité Nette</div>
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

        <!-- Hidden input for payment tranche -->
        <input type="hidden" id="hidden_montant_tranche" name="montant_tranche" value="0">

        <!-- 3. Sélection de la tranche & Paramètres du versement -->
        <div id="encaisse-payment-inputs-section" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Tranche / Échéance (Readonly) -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">
                2. Tranche / Échéance à régler <span style="font-weight: 700; color: #64748B; text-transform: none;"> </span> <span class="text-danger">*</span>
              </label>
              <div style="position: relative;">
                <select id="modal_select_tranche" name="tranche_code" class="form-control form-control-lg" style="font-weight: 800; font-size: 14px; color: #1E3A5F; border-radius: 10px; background-color: #F8FAFC; border: 1.5px solid #CBD5E1; pointer-events: none; cursor: not-allowed; padding-right: 40px;" tabindex="-1" required>
                  <!-- Rempli dynamiquement -->
                </select>
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #64748B; pointer-events: none;">
                  <i data-lucide="lock" style="width: 18px; height: 18px; color: #64748B;"></i>
                </span>
              </div>
              <div id="tranche-hint-info" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">
                <!-- Rempli dynamiquement -->
              </div>
            </div>

            <!-- Montant Total versé -->
            <div style="grid-column: span 2;">
              <label style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 6px; display: block; text-transform: uppercase;">
                3. Montant Total Versé (FCFA) <span class="text-danger">*</span>
              </label>
              <div style="position: relative;">
                <input type="number" step="1" id="input-montant-versement" name="montant_paiement" class="form-control form-control-lg" style="font-weight: 900; font-size: 20px; color: #15803D; padding-right: 70px; border-radius: 10px; background-color: #F0FDF4; border: 2px solid #86EFAC;" placeholder="0" readonly required>
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #15803D; font-size: 14px;">FCFA</span>
              </div>
              <div id="montant-max-hint" style="font-size: 11.5px; color: #64748B; margin-top: 4px;">
                Montant calculé automatiquement.
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
              <div id="mode-espece-caisse-alert" style="<?= empty($isCaisseOuverte) ? 'display: flex;' : 'display: none;' ?> margin-top: 6px; font-size: 11.5px; color: #B45309; background: #FFFBEB; border: 1px solid #FDE68A; padding: 6px 10px; border-radius: 6px; align-items: center; gap: 6px;">
                <i data-lucide="alert-triangle" style="width: 14px; height: 14px; color: #D97706; flex-shrink: 0;"></i>
                <span>Session fermée : <a href="javascript:void(0)" class="btn-open-session-caisse" style="color: #1E3A5F; font-weight: 800; text-decoration: underline;">Ouvrir la caisse</a> pour encaisser en espèces.</span>
              </div>
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
      <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
        <div id="caisse-status-footer-note" style="<?= empty($isCaisseOuverte) ? 'display: inline-flex;' : 'display: none;' ?> align-items: center; gap: 8px; font-size: 12px; color: #B45309; background: #FFFBEB; border: 1px solid #FDE68A; padding: 6px 12px; border-radius: 8px;">
          <i data-lucide="lock" style="width: 15px; height: 15px; color: #D97706; flex-shrink: 0;"></i>
          <span>Session de caisse fermée &bull; <a href="javascript:void(0)" class="btn-open-session-caisse" style="color: #1E3A5F; font-weight: 800; text-decoration: underline;">Ouvrir la caisse</a></span>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; margin-left: auto;">
          <button type="button" class="btn btn-secondary btn-close-modal-encaissement" style="font-weight: 700; border-radius: 8px; padding: 10px 20px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
            Annuler
          </button>
          <button type="submit" id="btn-submit-encaissement" class="btn btn-success" 
            <?= empty($isCaisseOuverte) ? 'disabled style="font-weight: 800; border-radius: 8px; padding: 10px 26px; background: #94A3B8; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; cursor: not-allowed; opacity: 0.65; box-shadow: none;" title="Session de caisse fermée : veuillez d\'abord ouvrir la caisse du jour pour encaisser en espèces."' : 'style="font-weight: 800; border-radius: 8px; padding: 10px 26px; background: #16A34A; border: none; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 6px rgba(22,163,74,0.3);"' ?>>
            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Encaisser
          </button>
        </div>
      </div>
    </form>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUCCÈS & IMPRESSION DU REÇU                                -->
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
<?php
$paiementsListConfig = [
    'canCancelPaiement' => (bool)(!empty($canCancelPaiement) || (isset($canAccess) && is_callable($canAccess) && $canAccess([PERMISSIONS::CANCEL_PAIEMENTS]))),
    'isCaisseOuverte' => (bool)(!empty($isCaisseOuverte)),
    'csrfToken' => Validator::generateCsrfToken()
];
?>
<script type="application/json" id="paiements-list-config">
<?= json_encode($paiementsListConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>
<?php 
$pageScripts = ['public/assets/js/modules/paiements_list.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>