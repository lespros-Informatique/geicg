<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$etudiants = (new ModelEtudiant())->getAll();
$accessoires = (new ModelAccessoire())->getAll();
$stats = $stats ?? (new ModelAccessoire())->getStats();
$annees = $annees ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
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
            <i data-lucide="package-check" style="width: 26px; height: 26px; color: #15803D;"></i> Registre de Retrait des Kits Étudiants
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Suivi exclusif des retraits, émargement au guichet et impression des bons de décharge</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <button type="button" id="btn-open-attrib-modal" class="btn btn-success" style="background: #15803D; border: none; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(21,128,61,0.2);">
            <i data-lucide="package-plus" style="width: 18px; height: 18px;"></i> Attribuer un Kit
          </button>
        </div>
      </div>

      <!-- Filtres (Année, Classe & Statut Retrait) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; flex: 1;">
            
            <!-- Filter Année -->
            <div style="min-width: 200px; flex: 1;">
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Année Académique</label>
              <select id="filter-annee" class="form-select select2" style="width: 100%;">
                <option value="">-- Toutes les années --</option>
                <?php foreach ($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filter Classe -->
            <div style="min-width: 200px; flex: 1;">
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Classe / Promotion</label>
              <select id="filter-classe" class="form-select select2" style="width: 100%;">
                <option value="">-- Toutes les classes --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_classe']) ?>">
                    <?= htmlspecialchars($c['libelle_classe'] ?? ($c['nom_classe'] ?? '')) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filter Statut Retrait -->
            <div style="min-width: 200px; flex: 1;">
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Statut de Retrait</label>
              <select id="filter-statut" class="form-select select2" style="width: 100%;">
                <option value="all">-- Tous les statuts --</option>
                <option value="en_attente">⏳ Avec des kits en attente</option>
                <option value="retire">✅ Kits entièrement remis</option>
              </select>
            </div>

          </div>

          <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" id="btn-refresh" class="btn btn-light" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 15px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #334155; cursor: pointer;">
              <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Actualiser
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Cards Interactifs -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div class="card kpi-card-clickable" data-status="all" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; cursor: pointer; transition: transform 0.2s;" title="Cliquez pour filtrer toutes les attributions">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="package" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total Fournitures</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-kits"><?= $stats['total'] ?? 0 ?></div>
          </div>
        </div>

        <div class="card kpi-card-clickable" data-status="en_attente" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FED7AA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; cursor: pointer; transition: transform 0.2s;" title="Cliquez pour filtrer les articles en attente">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #EA580C; text-transform: uppercase;">En Attente de Retrait</div>
            <div style="font-size: 22px; font-weight: 900; color: #C2410C; margin-top: 2px;" id="kpi-en-attente"><?= $stats['en_attente'] ?? 0 ?></div>
          </div>
        </div>

        <div class="card kpi-card-clickable" data-status="retire" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; cursor: pointer; transition: transform 0.2s;" title="Cliquez pour filtrer les articles remis">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #16A34A; text-transform: uppercase;">Articles Remis</div>
            <div style="font-size: 22px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-retires"><?= $stats['retires'] ?? ($stats['retire'] ?? 0) ?></div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F8FAFC; color: #0284C7; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Taux de Remise Global</div>
            <div style="font-size: 22px; font-weight: 900; color: #0284C7; margin-top: 2px;" id="kpi-taux"><?= $stats['taux_retrait'] ?? ($stats['taux'] ?? 0) ?>%</div>
          </div>
        </div>

      </div>

      <!-- Tableau du Registre d'Émargement des Kits par Étudiant -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <table id="table-distributions" class="table table-hover align-middle w-100" style="width: 100%;">
          <thead>
            <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
              <th>Étudiant</th>
              <th>Matricule & Classe</th>
              <th>Composition du Kit & Statuts</th>
              <th>Articles Remis</th>
              <th>Progression Retrait</th>
              <th style="text-align: right;">Actions Émargement</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE GEICG : ÉMARGEMENT DÉTAILLÉ DU KIT ÉTUDIANT              -->
<!-- ========================================================================= -->
<div id="modal-emargement-student" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 780px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: #15803D; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="package-check" style="width: 22px; height: 22px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h3 id="modal-kit-student-name" style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Remise des Fournitures</h3>
          <div id="modal-kit-student-info" style="font-size: 12px; color: #DCFCE7; margin-top: 2px;">Matricule / Classe</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-kit" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <!-- Corps du modal (Scrollable) -->
    <div style="padding: 24px; background: #F8FAFC; overflow-y: auto; flex: 1;">
      
      <!-- Banner de Statut Global & Actions Rapides -->
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding: 14px 18px; background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <div>
          <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut des Remises</span>
          <div style="font-size: 16px; font-weight: 800; color: #0F172A;" id="modal-kit-completion-text">0 / 0 Articles Remis</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <button type="button" id="btn-tout-valider-kit-modal" class="btn btn-sm btn-success" style="font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; background: #15803D; border: none;">
            <i data-lucide="check-check" style="width: 16px; height: 16px;"></i> Tout Marquer Remis
          </button>
          <button type="button" id="btn-print-bon-modal" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer Bon de Remise
          </button>
        </div>
      </div>

      <!-- Liste dynamique des fournitures -->
      <div id="modal-kit-items-list" style="display: flex; flex-direction: column; gap: 12px;">
        <!-- Rempli dynamiquement en JS -->
      </div>

    </div>

    <!-- Pied de modal -->
    <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end;">
      <button type="button" class="btn btn-secondary btn-close-modal-kit" style="font-weight: 700; border-radius: 8px; padding: 9px 22px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
        Fermer
      </button>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : BON DE REMISE OFFICIEL (DÉCHARGE KIT A4)               -->
<!-- ========================================================================= -->
<div id="modal-bon-remise" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 10000; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 850px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <div style="background: #15803D; color: #FFFFFF; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Bon de Remise Officiel de Kits & Accessoires
      </h3>
      <button type="button" class="btn-close-modal-bon" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div style="padding: 24px; background: #FFFFFF; overflow-y: auto; flex: 1;" id="printable-bon-remise-area">
      
      <!-- Document A4 officiel d'impression -->
      <div style="border: 2px solid #15803D; padding: 24px; border-radius: 12px; background: #FFFFFF; font-family: 'Segoe UI', Arial, sans-serif;">
        
        <!-- En-tête Récépissé -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed #CBD5E1; padding-bottom: 16px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <img id="bon-etab-logo" src="<?= RACINE ?>public/images/logo-placeholder.png" style="max-height: 55px; max-width: 140px; object-fit: contain;" onerror="this.style.display='none'">
            <div>
              <div id="bon-etab-nom" style="font-size: 16px; font-weight: 900; color: #15803D; text-transform: uppercase;">ÉTABLISSEMENT GEICG</div>
              <div id="bon-etab-adresse" style="font-size: 11px; color: #64748B;">Adresse / Ville</div>
              <div id="bon-etab-tel" style="font-size: 11px; color: #64748B;">Tél : -</div>
            </div>
          </div>
          <div style="text-align: right;">
            <span class="badge bg-dark" style="font-size: 11px; padding: 6px 12px; text-transform: uppercase; background: #0F172A; color: #FFF; border-radius: 6px;" id="bon-annee">ANNÉE -</span>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; margin-top: 6px;">Délivré le : <span id="bon-date-emission">-</span></div>
          </div>
        </div>

        <!-- Titre du Document -->
        <div style="text-align: center; margin-bottom: 22px;">
          <h3 style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">BON DE REMISE & DÉCHARGE DE KITS</h3>
          <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">Bordereau d'émargement et de réception des tenues et fournitures scolaires</p>
        </div>

        <!-- Informations Étudiant -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
            <div><strong>Nom & Prénom :</strong> <span id="bon-student-name" style="color: #0F172A; font-weight: 800;">-</span></div>
            <div><strong>Matricule :</strong> <span id="bon-student-matricule" class="font-monospace" style="font-weight: 700; color: #15803D;">-</span></div>
            <div><strong>Classe / Promotion :</strong> <span id="bon-student-classe" style="font-weight: 700;">-</span></div>
            <div><strong>Téléphone :</strong> <span id="bon-student-phone">-</span></div>
          </div>
        </div>

        <!-- Table 1 : Articles Effectivement Remis -->
        <div style="margin-bottom: 18px;">
          <div style="font-size: 12px; font-weight: 800; color: #16A34A; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Articles / Fournitures Réceptionnées & Remises (<span id="bon-count-remis">0</span>)
          </div>
          <table class="table table-bordered table-sm" style="font-size: 12px; margin-bottom: 0; width: 100%; border-collapse: collapse;">
            <thead style="background: #F0FDF4; color: #166534;">
              <tr>
                <th style="width: 50%; padding: 8px;">Désignation de la fourniture</th>
                <th style="width: 25%; padding: 8px;">Date de remise</th>
                <th style="width: 25%; padding: 8px;">Statut</th>
              </tr>
            </thead>
            <tbody id="bon-table-remis">
              <!-- Rempli en JS -->
            </tbody>
          </table>
        </div>

        <!-- Table 2 : Articles Restant en Attente -->
        <div style="margin-bottom: 22px;">
          <div style="font-size: 12px; font-weight: 800; color: #EA580C; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="clock" style="width: 16px; height: 16px;"></i> Articles Restant à Fournir / En Attente (<span id="bon-count-attente">0</span>)
          </div>
          <table class="table table-bordered table-sm" style="font-size: 12px; margin-bottom: 0; width: 100%; border-collapse: collapse;">
            <thead style="background: #FFF7ED; color: #9A3412;">
              <tr>
                <th style="width: 50%; padding: 8px;">Désignation de la fourniture</th>
                <th style="width: 25%; padding: 8px;">Date d'attribution</th>
                <th style="width: 25%; padding: 8px;">Statut actuel</th>
              </tr>
            </thead>
            <tbody id="bon-table-attente">
              <!-- Rempli en JS -->
            </tbody>
          </table>
        </div>

        <!-- Taux de Complétude & Signatures -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px; padding-top: 16px; border-top: 1px dashed #CBD5E1;">
          <div style="font-size: 11.5px; color: #475569;">
            <div><strong>Agent Responsable :</strong> <span id="bon-agent-nom">Administration</span></div>
            <div style="margin-top: 4px;">N.B. : Ce bon fait foi de décharge et atteste de la remise des articles cochés.</div>
          </div>
          
          <div style="display: flex; gap: 50px;">
            <div style="text-align: center; min-width: 140px;">
              <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 40px;">Signature de l'Élève</div>
              <div style="border-top: 1px solid #94A3B8; font-size: 10px; color: #94A3B8; padding-top: 4px;">Lu et approuvé</div>
            </div>
            <div style="text-align: center; min-width: 160px;">
              <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 40px;">Cachet & Signature Agent</div>
              <div style="border-top: 1px solid #94A3B8; font-size: 10px; color: #94A3B8; padding-top: 4px;">Visa de la Scolarité</div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px;">
      <button type="button" class="btn btn-secondary btn-close-modal-bon" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">Fermer</button>
      <button type="button" id="btn-trigger-print-bon" class="btn btn-success" style="background: #15803D; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 8px 20px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Lancer l'Impression
      </button>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : ATTRIBUER UN KIT OU ACCESSOIRE                         -->
<!-- ========================================================================= -->
<div id="modal-attribuer-kit" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 550px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <div style="background: #15803D; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="package-plus" style="width: 22px; height: 22px;"></i> Attribuer un Kit / Accessoire
      </h3>
      <button type="button" class="btn-close-modal-attrib" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-attribuer-kit" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
      <div style="padding: 24px; overflow-y: auto; flex: 1;">
        
        <!-- Sélection Étudiant -->
        <div style="margin-bottom: 18px;">
          <label style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Sélectionner l'Étudiant <span style="color: #EF4444;">*</span></label>
          <select name="etudiant_code" id="modal-select-etudiant" class="form-select select2" style="width: 100%;" required>
            <option value="">-- Rechercher un étudiant --</option>
            <?php foreach ($etudiants as $e): ?>
              <option value="<?= htmlspecialchars($e['code_etudiant']) ?>">
                <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?> (<?= htmlspecialchars($e['matricule_etudiant'] ?? 'Sans matricule') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Sélection Kit / Accessoire -->
        <div style="margin-bottom: 18px;">
          <label style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Kit(s) / Accessoire(s) <span style="color: #EF4444;">*</span></label>
          <select name="accessoires[]" id="modal-select-accessoires" class="form-select select2" multiple style="width: 100%;" required>
            <?php foreach ($accessoires as $acc): ?>
              <option value="<?= htmlspecialchars($acc['code_accessoire']) ?>">
                <?= htmlspecialchars($acc['libelle_accessoire']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Statut de Retrait Initial -->
        <div style="margin-bottom: 18px;">
          <label style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">Statut de Retrait Initial</label>
          <select name="etat_retrait" class="form-select" style="border-radius: 8px; font-weight: 700;">
            <option value="en_attente">⏳ En attente de retrait</option>
            <option value="retire">✅ Remis / Retiré immédiatement</option>
          </select>
        </div>

      </div>

      <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-close-modal-attrib" style="font-weight: 700; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">Annuler</button>
        <button type="submit" class="btn btn-success" style="font-weight: 700; border-radius: 8px; padding: 9px 20px; background: #15803D; border: none; color: #FFFFFF; cursor: pointer;">Enregistrer l'Attribution</button>
      </div>
    </form>
  </div>
</div>

<style>
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-12px); }
  to { opacity: 1; transform: translateY(0); }
}
.kpi-card-clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08);
}
@media print {
  body * {
    visibility: hidden;
  }
  #printable-bon-remise-area, #printable-bon-remise-area * {
    visibility: visible;
  }
  #printable-bon-remise-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 0;
  }
}
</style>

<script>
$(document).ready(function() {
  var table;
  var currentStudentData = null;

  if ($.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  if ($.fn.select2) {
    $('#filter-annee, #filter-classe, #filter-statut').select2({ width: '100%' });
    $('#modal-select-etudiant, #modal-select-accessoires').select2({
      dropdownParent: $('#modal-attribuer-kit'),
      width: '100%'
    });
  }

  function initTable() {
    table = $('#table-distributions').DataTable({
      ajax: {
        url: '<?= RACINE ?>accessoire/apiDistributions',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
          d.filter = $('#filter-statut').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement des distributions:", error, thrown);
        },
        dataSrc: function(json) {
          var items = json.data || [];
          return items;
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var photo = d.photo_etudiant ? '<?= RACINE ?>' + d.photo_etudiant : '<?= RACINE ?>public/images/avatar.png';
            return '<div style="display: flex; align-items: center; gap: 12px;">' +
                   '<img src="' + photo + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0;" onerror="this.src=\'<?= RACINE ?>public/images/avatar.png\'">' +
                   '<div>' +
                     '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">' + escapeHtml(d.nom_complet || (d.nom_etudiant + ' ' + d.prenom_etudiant)) + '</div>' +
                     '<div style="font-size: 11.5px; color: #64748B;">Tél : ' + escapeHtml(d.telephone_etudiant || '-') + '</div>' +
                   '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            return '<div>' +
                     '<span class="badge bg-light text-dark font-monospace border" style="font-size: 11.5px; font-weight: 700; padding: 4px 8px;">' + escapeHtml(d.matricule_etudiant || '-') + '</span>' +
                     '<div style="font-weight: 700; color: #1E3A5F; font-size: 12px; margin-top: 3px;">' + escapeHtml(d.libelle_classe || '-') + '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          render: function(d) {
            var items = d.items || [];
            if (items.length === 0) {
              return '<span class="text-muted" style="font-size: 12px;">Aucun article souscrit</span>';
            }

            var html = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
            items.forEach(function(item) {
              if (item.etat_retrait === 'retire') {
                html += '<span class="badge bg-success" style="font-size: 11px; font-weight: 700; padding: 4px 8px;" title="Remis le ' + (item.date_retrait_formatee || '-') + '">✓ ' + escapeHtml(item.libelle_accessoire) + '</span>';
              } else {
                html += '<span class="badge bg-warning text-dark" style="font-size: 11px; font-weight: 700; padding: 4px 8px;" title="En attente de remise">⏳ ' + escapeHtml(item.libelle_accessoire) + '</span>';
              }
            });
            html += '</div>';
            return html;
          }
        },
        {
          data: null,
          render: function(d) {
            var isComplet = (d.total_retires === d.total_kits && d.total_kits > 0);
            var badgeClass = isComplet ? 'bg-success' : (d.total_retires > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.total_retires + ' / ' + d.total_kits + ' remis' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage || 0;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 110px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 7px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
                       '<div style="height: 100%; width: ' + pct + '%; background: ' + barColor + '; border-radius: 4px;"></div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: null,
          className: 'text-end',
          render: function(d) {
            return '<div style="display: flex; justify-content: flex-end; gap: 6px;">' +
                     '<button type="button" class="btn btn-sm btn-success btn-open-student-kit" style="font-weight: 700; border-radius: 8px; padding: 6px 11px; background: #15803D; border: none;" title="Gérer et émarger les fournitures de cet élève">' +
                       '<i data-lucide="package-check" style="width: 14px; height: 14px; margin-right: 4px;"></i> Émarger' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-primary btn-print-bon-row" title="Imprimer le Bon de Remise Officiel A4" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="printer" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate-all-kits" title="Tout marquer comme remis en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="check-check" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                   '</div>';
          }
        }
      ],
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      },
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
      }
    });
  }

  initTable();

  // Filtrage interactif
  $('#filter-annee, #filter-classe, #filter-statut').on('change', function() {
    table.ajax.reload();
    refreshKpis();
  });

  $('.kpi-card-clickable').on('click', function() {
    var status = $(this).data('status');
    $('#filter-statut').val(status).trigger('change.select2');
    table.ajax.reload();
  });

  $('#btn-refresh, #btn-refresh-header').on('click', function() {
    table.ajax.reload();
    refreshKpis();
  });

  function refreshKpis() {
    var annee = $('#filter-annee').val();
    $.ajax({
      url: '<?= RACINE ?>accessoire/apiStats',
      type: 'GET',
      data: { annee_code: annee },
      dataType: 'json',
      success: function(res) {
        var s = res.stats || res || {};
        $('#kpi-total-kits').text(s.total || 0);
        $('#kpi-en-attente').text(s.en_attente || 0);
        $('#kpi-retires').text(s.retires || (s.retire || 0));
        $('#kpi-taux').text((s.taux_retrait || (s.taux || 0)) + '%');
      }
    });
  }

  // Ouvrir le modal d'émargement détaillé de l'étudiant
  $('#table-distributions').on('click', '.btn-open-student-kit', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openStudentKitModal(rowData);
  });

  // Ouvrir le Bon de remise depuis la ligne
  $('#table-distributions').on('click', '.btn-print-bon-row', function() {
    var rowData = table.row($(this).closest('tr')).data();
    loadAndOpenBonRemiseModal(rowData.code_inscription);
  });

  $('#btn-print-bon-modal').on('click', function() {
    if (currentStudentData) {
      loadAndOpenBonRemiseModal(currentStudentData.code_inscription);
    }
  });

  // Tout marquer comme remis pour l'étudiant
  $('#table-distributions').on('click', '.btn-quick-validate-all-kits', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllKitsForStudent(rowData.code_inscription);
  });

  $('#btn-tout-valider-kit-modal').on('click', function() {
    if (currentStudentData) {
      validateAllKitsForStudent(currentStudentData.code_inscription);
    }
  });

  // Fermeture des modals
  $('.btn-close-modal-kit').on('click', function() {
    $('#modal-emargement-student').hide();
  });
  $('.btn-close-modal-bon').on('click', function() {
    $('#modal-bon-remise').hide();
  });
  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-emargement-student')) $('#modal-emargement-student').hide();
    if ($(e.target).is('#modal-bon-remise')) $('#modal-bon-remise').hide();
  });

  function openStudentKitModal(d) {
    $('#modal-kit-student-name').text(d.nom_complet);
    $('#modal-kit-student-info').text('Matricule : ' + (d.matricule_etudiant || '-') + ' | Classe : ' + (d.libelle_classe || '-'));
    $('#modal-kit-completion-text').text(d.total_retires + ' / ' + d.total_kits + ' Articles Remis (' + d.pourcentage + '%)');

    var html = '';
    var items = d.items || [];
    if (items.length > 0) {
      items.forEach(function(item) {
        var isRetire = (item.etat_retrait === 'retire');
        var borderCol = isRetire ? '#16A34A' : '#EA580C';
        var bgCol = isRetire ? '#F0FDF4' : '#FFF7ED';
        var btnClass = isRetire ? 'btn-outline-secondary' : 'btn-success';
        var btnText = isRetire ? 'Annuler la remise' : 'Marquer comme Remis';
        var iconName = isRetire ? 'rotate-ccw' : 'check';
        var dateText = item.date_retrait_formatee ? ('<span style="font-size: 11px; font-weight: 600; color: #15803D; margin-left: 6px;">(Remis le ' + item.date_retrait_formatee + ')</span>') : '<span style="font-size: 11px; font-weight: 600; color: #EA580C; margin-left: 6px;">(En attente de retrait)</span>';

        html += '<div style="background: ' + bgCol + '; border-left: 5px solid ' + borderCol + '; padding: 14px 18px; border-radius: 12px; border-top: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">' +
                  '<div>' +
                    '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_accessoire) + dateText + '</div>' +
                    '<div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Fourniture souscrite d\'inscription</div>' +
                  '</div>' +
                  '<div>' +
                    '<button type="button" class="btn btn-sm ' + btnClass + ' btn-toggle-item-modal" data-id="' + item.id_accessoire_inscription + '" style="font-weight: 700; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px;">' +
                      '<i data-lucide="' + iconName + '" style="width: 15px; height: 15px;"></i> ' + btnText +
                    '</button>' +
                  '</div>' +
                '</div>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucun article souscrit pour cet élève.</div>';
    }

    $('#modal-kit-items-list').html(html);
    $('#modal-emargement-student').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  }

  // Action toggle item unitaire dans le modal
  $(document).on('click', '.btn-toggle-item-modal', function() {
    var id = $(this).data('id');
    var $btn = $(this);
    $btn.prop('disabled', true);

    $.ajax({
      url: '<?= RACINE ?>accessoire/toggleRetrait',
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Statut mis à jour', 'success');
          else if (window.toastr) toastr.success(res.message || 'Statut mis à jour');
          table.ajax.reload(function() {
            if (currentStudentData) {
              var updatedData = table.rows().data().toArray().find(function(r) {
                return r.code_inscription === currentStudentData.code_inscription;
              });
              if (updatedData) {
                currentStudentData = updatedData;
                openStudentKitModal(updatedData);
              }
            }
          }, false);
          refreshKpis();
        } else {
          $btn.prop('disabled', false);
          var msg = res.message || 'Erreur de mise à jour';
          if (typeof showToast === 'function') showToast(msg, 'error');
          else if (window.toastr) toastr.error(msg);
        }
      },
      error: function() {
        $btn.prop('disabled', false);
        var msg = 'Erreur lors de la mise à jour du statut.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Action Tout valider pour un étudiant
  function validateAllKitsForStudent(inscrCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les fournitures de cet élève comme REMISES / RETIRÉES ?")) return;

    $.ajax({
      url: '<?= RACINE ?>accessoire/toggleStudentAllKits',
      type: 'POST',
      data: {
        inscription_code: inscrCode,
        target_etat: 'retire'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Tous les kits ont été validés avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          table.ajax.reload(null, false);
          refreshKpis();
          $('#modal-emargement-student').hide();
        } else {
          var msgErr = res.message || 'Erreur lors de la validation.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur lors de la connexion au serveur.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  }

  // Chargement et affichage du Bon de Remise Officiel A4
  function loadAndOpenBonRemiseModal(inscrCode) {
    $.ajax({
      url: '<?= RACINE ?>accessoire/getBonRemiseData',
      type: 'GET',
      data: { inscription_code: inscrCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.student) {
          var s = res.student;
          $('#bon-student-name').text((s.nom_etudiant || '') + ' ' + (s.prenom_etudiant || ''));
          $('#bon-student-matricule').text(s.matricule_etudiant || '-');
          $('#bon-student-classe').text(s.libelle_classe || '-');
          $('#bon-student-phone').text(s.telephone_etudiant || '-');
          $('#bon-annee').text(s.libelle_annee ? ('Année Académique ' + s.libelle_annee) : 'GEICG');
          $('#bon-date-emission').text(res.date_emission || '-');
          $('#bon-agent-nom').text(res.agent_nom || 'Administration');

          if (s.nom_etablissement) $('#bon-etab-nom').text(s.nom_etablissement);
          if (s.adresse_etablissement) $('#bon-etab-adresse').text(s.adresse_etablissement || '');
          if (s.telephone_etablissement) $('#bon-etab-tel').text('Tél : ' + s.telephone_etablissement);
          if (s.logo_etablissement) $('#bon-etab-logo').attr('src', '<?= RACINE ?>' + s.logo_etablissement).show();

          $('#bon-count-remis').text(res.total_remis || 0);
          $('#bon-count-attente').text(res.total_en_attente || 0);

          // Table articles remis
          var htmlRemis = '';
          if (res.kits_remis && res.kits_remis.length > 0) {
            res.kits_remis.forEach(function(k) {
              htmlRemis += '<tr>' +
                             '<td style="padding:8px;"><strong>' + escapeHtml(k.libelle_accessoire) + '</strong></td>' +
                             '<td style="padding:8px;" class="text-success fw-bold">✓ ' + (k.date_retrait_formatee || 'Remis') + '</td>' +
                             '<td style="padding:8px;"><span class="badge bg-success">Remis & Réceptionné</span></td>' +
                           '</tr>';
            });
          } else {
            htmlRemis = '<tr><td colspan="3" class="text-muted text-center" style="padding:10px;">Aucun article remis pour le moment.</td></tr>';
          }
          $('#bon-table-remis').html(htmlRemis);

          // Table articles en attente
          var htmlAtt = '';
          if (res.kits_en_attente && res.kits_en_attente.length > 0) {
            res.kits_en_attente.forEach(function(k) {
              htmlAtt += '<tr>' +
                           '<td style="padding:8px;"><strong>' + escapeHtml(k.libelle_accessoire) + '</strong></td>' +
                           '<td style="padding:8px;">' + (k.date_attribution_formatee || '-') + '</td>' +
                           '<td style="padding:8px;"><span class="badge bg-warning text-dark">⏳ En attente de retrait</span></td>' +
                         '</tr>';
            });
          } else {
            htmlAtt = '<tr><td colspan="3" class="text-success text-center fw-bold" style="padding:10px;">🎉 Complètement remis ! Aucun article en attente.</td></tr>';
          }
          $('#bon-table-attente').html(htmlAtt);

          $('#modal-bon-remise').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          var err = res.message || 'Erreur lors de la génération du bon de remise.';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
        }
      },
      error: function() {
        var msg = 'Erreur de connexion au serveur.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  }

  $('#btn-trigger-print-bon').on('click', function() {
    window.print();
  });

  // Modal Attribution
  $('#btn-open-attrib-modal').on('click', function() {
    $('#modal-attribuer-kit').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-attrib').on('click', function() {
    $('#modal-attribuer-kit').hide();
  });

  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-emargement-student')) $('#modal-emargement-student').hide();
    if ($(e.target).is('#modal-bon-remise')) $('#modal-bon-remise').hide();
    if ($(e.target).is('#modal-attribuer-kit')) $('#modal-attribuer-kit').hide();
  });

  $('#form-attribuer-kit').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
      url: '<?= RACINE ?>accessoire/attribuerKit',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Attribution effectuée avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          $('#modal-attribuer-kit').hide();
          table.ajax.reload();
          refreshKpis();
        } else {
          var msgErr = res.message || 'Erreur lors de l\'attribution.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur lors de l\'enregistrement.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  });

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
