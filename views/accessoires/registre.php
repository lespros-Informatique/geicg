<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$etudiants = (new ModelEtudiant())->getAll();
$accessoires = (new ModelAccessoire())->getAll();
$stats = $stats ?? (new ModelAccessoire())->getStats();
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
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Classe</label>
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
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total kits</div>
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
            <div style="font-size: 11px; font-weight: 800; color: #16A34A; text-transform: uppercase;">Kits remis</div>
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
              <th>Nombre d'articles</th>
              <th>Kits remis</th>
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
          <div style="font-size: 16px; font-weight: 800; color: #0F172A;" id="modal-kit-completion-text">0 / 0 Kits remis</div>
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
<!-- MODAL SUR MESURE : BON DE REMISE  (DÉCHARGE KIT A4)               -->
<!-- ========================================================================= -->
<div id="modal-bon-remise" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 10000; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 850px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <div style="background: #15803D; color: #FFFFFF; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Bon de Remise de Kits & Accessoires
      </h3>
      <button type="button" class="btn-close-modal-bon" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div style="padding: 24px; background: #FFFFFF; overflow-y: auto; flex: 1;" id="printable-bon-remise-area">
      
      <!-- Document A4  d'impression -->
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
            <div><strong>Classe :</strong> <span id="bon-student-classe" style="font-weight: 700;">-</span></div>
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

<?php 
ViewContext::setScripts(['public/assets/js/modules/accessoires_registre.js']);
require_once __DIR__ . '/../../public/inc/footer.php'; 
?>
