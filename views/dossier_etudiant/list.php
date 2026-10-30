<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="folder-check" style="width: 26px; height: 26px; color: #1E3A5F;"></i> Dépôt des Dossiers Étudiants
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion, numérisation, émargement et contrôle de la complétude des pièces exigées</p>
        </div>
      </div>

      <!-- Barre de Filtres (Année, Classe & Statut Dossier) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          
          <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; flex: 1;">
            <!-- Année Académique -->
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

            <!-- Classe -->
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

            <!-- Statut du Dossier -->
            <div style="min-width: 200px; flex: 1;">
              <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">Statut du Dossier</label>
              <select id="filter-statut" class="form-select select2" style="width: 100%;">
                <option value="all">-- Tous les statuts --</option>
                <option value="incomplet">⚠️ Incomplets (Manque des pièces)</option>
                <option value="complet">✅ Complets (Toutes pièces déposées)</option>
                <option value="rejete">❌ Avec pièces rejetées</option>
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

      <!-- Cards KPI Interactifs -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div class="card kpi-card-clickable" data-status="all" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" title="Cliquez pour filtrer tous les étudiants">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total Étudiants</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-etudiants">0</div>
          </div>
        </div>

        <div class="card kpi-card-clickable" data-status="complet" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #DCFCE7; display: flex; align-items: center; gap: 14px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" title="Cliquez pour filtrer les dossiers complets">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #16A34A; text-transform: uppercase;">Dossiers Complets</div>
            <div style="font-size: 22px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-dossiers-complets">0</div>
          </div>
        </div>

        <div class="card kpi-card-clickable" data-status="incomplet" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #FED7AA; display: flex; align-items: center; gap: 14px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" title="Cliquez pour filtrer les dossiers incomplets">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="alert-circle" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #EA580C; text-transform: uppercase;">Dossiers Incomplets</div>
            <div style="font-size: 22px; font-weight: 900; color: #C2410C; margin-top: 2px;" id="kpi-dossiers-incomplets">0</div>
          </div>
        </div>

        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #F8FAFC; color: #0284C7; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="pie-chart" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Taux de Dépôt Global</div>
            <div style="font-size: 22px; font-weight: 900; color: #0284C7; margin-top: 2px;" id="kpi-taux-global">0%</div>
          </div>
        </div>

      </div>

      <!-- Tableau des Étudiants et Dépôt des Dossiers -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <table id="table-dossiers" class="table table-hover align-middle w-100" style="width: 100%;">
          <thead>
            <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
              <th>Étudiant</th>
              <th>Matricule</th>
              <th>Classe</th>
              <th>Pièces Déposées</th>
              <th>Complétude</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIF SUR MESURE : ÉMARGEMENT DU DOSSIER ÉTUDIANT              -->
<!-- ========================================================================= -->
<div id="modal-dossier" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 840px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="folder-check" style="width: 22px; height: 22px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h3 id="modal-student-name" style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Dossier de l'Étudiant</h3>
          <div id="modal-student-info" style="font-size: 12px; color: #94A3B8; margin-top: 2px;">Classe / Matricule</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-dossier" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <!-- Corps du modal (Scrollable) -->
    <div style="padding: 24px; background: #F8FAFC; overflow-y: auto; flex: 1;">
      
      <!-- Banner de Statut Global & Actions Rapides -->
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding: 14px 18px; background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <div>
          <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut Global du Dossier</span>
          <div style="font-size: 16px; font-weight: 800; color: #0F172A;" id="modal-completion-text">0 / 0 Pièces Déposées</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <button type="button" id="btn-tout-valider-modal" class="btn btn-sm btn-success" style="font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
            <i data-lucide="check-check" style="width: 16px; height: 16px;"></i> Tout Valider
          </button>
          <button type="button" id="btn-print-modal-recepisse" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer Récépissé
          </button>
        </div>
      </div>

      <!-- Liste dynamique des cartes de pièces exigées -->
      <div id="modal-pieces-list" style="display: flex; flex-direction: column; gap: 14px;">
        <!-- Rempli dynamiquement en JS -->
      </div>

    </div>

    <!-- Pied de modal -->
    <div style="background: #FFFFFF; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: flex-end;">
      <button type="button" class="btn btn-secondary btn-close-modal-dossier" style="font-weight: 700; border-radius: 8px; padding: 9px 22px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
        Fermer
      </button>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL INTERACTIF SUR MESURE : IMPRESSION RÉCÉPISSÉ                -->
<!-- ========================================================================= -->
<div id="modal-recepisse" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 10000; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 850px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: slideDown 0.2s ease-out;">
    
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Bordereau de Dépôt de Pièces
      </h3>
      <button type="button" class="btn-close-modal-recepisse" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div style="padding: 24px; background: #FFFFFF; overflow-y: auto; flex: 1;" id="printable-recepisse-area">
      
      <!-- Document A4  d'impression -->
      <div style="border: 2px solid #1E3A5F; padding: 24px; border-radius: 12px; background: #FFFFFF; font-family: 'Segoe UI', Arial, sans-serif;">
        
        <!-- En-tête Récépissé -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed #CBD5E1; padding-bottom: 16px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <img id="rec-etab-logo" src="<?= RACINE ?>public/images/logo-placeholder.png" style="max-height: 55px; max-width: 140px; object-fit: contain;" onerror="this.style.display='none'">
            <div>
              <div id="rec-etab-nom" style="font-size: 16px; font-weight: 900; color: #1E3A5F; text-transform: uppercase;">ÉTABLISSEMENT GEICG</div>
              <div id="rec-etab-adresse" style="font-size: 11px; color: #64748B;">Adresse / Ville</div>
              <div id="rec-etab-tel" style="font-size: 11px; color: #64748B;">Tél : -</div>
            </div>
          </div>
          <div style="text-align: right;">
            <span class="badge bg-dark" style="font-size: 11px; padding: 6px 12px; text-transform: uppercase; background: #0F172A; color: #FFF; border-radius: 6px;" id="rec-annee">ANNÉE -</span>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; margin-top: 6px;">Émis le : <span id="rec-date-emission">-</span></div>
          </div>
        </div>

        <!-- Titre du Document -->
        <div style="text-align: center; margin-bottom: 22px;">
          <h3 style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">RÉCÉPISSÉ DE DÉPÔT DE PIÈCES</h3>
          <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">Bordereau d'émargement du dossier d'inscription</p>
        </div>

        <!-- Informations Étudiant -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
            <div><strong>Nom & Prénom :</strong> <span id="rec-student-name" style="color: #0F172A; font-weight: 800;">-</span></div>
            <div><strong>Matricule :</strong> <span id="rec-student-matricule" class="font-monospace" style="font-weight: 700; color: #1E3A5F;">-</span></div>
            <div><strong>Classe :</strong> <span id="rec-student-classe" style="font-weight: 700;">-</span></div>
            <div><strong>Téléphone :</strong> <span id="rec-student-phone">-</span></div>
          </div>
        </div>

        <!-- Table 1 : Pièces Déposées -->
        <div style="margin-bottom: 18px;">
          <div style="font-size: 12px; font-weight: 800; color: #16A34A; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Pièces Réceptionnées & Déposées (<span id="rec-count-deposees">0</span>)
          </div>
          <table class="table table-bordered table-sm" style="font-size: 12px; margin-bottom: 0; width: 100%; border-collapse: collapse;">
            <thead style="background: #F0FDF4; color: #166534;">
              <tr>
                <th style="width: 45%; padding: 8px;">Intitulé du document</th>
                <th style="width: 25%; padding: 8px;">Date de dépôt</th>
                <th style="width: 30%; padding: 8px;">Observations / Remarques</th>
              </tr>
            </thead>
            <tbody id="rec-table-deposees">
              <!-- Rempli en JS -->
            </tbody>
          </table>
        </div>

        <!-- Table 2 : Pièces Restant à Fournir -->
        <div style="margin-bottom: 22px;">
          <div style="font-size: 12px; font-weight: 800; color: #EA580C; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i> Pièces Restant à Fournir / En Attente (<span id="rec-count-manquantes">0</span>)
          </div>
          <table class="table table-bordered table-sm" style="font-size: 12px; margin-bottom: 0; width: 100%; border-collapse: collapse;">
            <thead style="background: #FFF7ED; color: #9A3412;">
              <tr>
                <th style="width: 45%; padding: 8px;">Intitulé du document</th>
                <th style="width: 25%; padding: 8px;">Statut actuel</th>
                <th style="width: 30%; padding: 8px;">Observations</th>
              </tr>
            </thead>
            <tbody id="rec-table-manquantes">
              <!-- Rempli en JS -->
            </tbody>
          </table>
        </div>

        <!-- Taux de Complétude & Signatures -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px; padding-top: 16px; border-top: 1px dashed #CBD5E1;">
          <div style="font-size: 11.5px; color: #475569;">
            <div><strong>Agent de Saisie :</strong> <span id="rec-agent-nom">Administration</span></div>
            <div style="margin-top: 4px;">N.B. : Ce document atteste de la réception effective des pièces mentionnées.</div>
          </div>
          
          <div style="display: flex; gap: 50px;">
            <div style="text-align: center; min-width: 140px;">
              <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 40px;">Signature de l'Élève</div>
              <div style="border-top: 1px solid #94A3B8; font-size: 10px; color: #94A3B8; padding-top: 4px;">Lu et approuvé</div>
            </div>
            <div style="text-align: center; min-width: 160px;">
              <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 40px;">Cachet & Signature Agent</div>
              <div style="border-top: 1px solid #94A3B8; font-size: 10px; color: #94A3B8; padding-top: 4px;">Visa de l'Administration</div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px;">
      <button type="button" class="btn btn-secondary btn-close-modal-recepisse" style="font-weight: 700; border-radius: 8px; padding: 8px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">Fermer</button>
      <button type="button" id="btn-trigger-print-recepisse" class="btn btn-primary" style="background: #1E3A5F; border: none; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 8px 20px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Lancer l'Impression
      </button>
    </div>
  </div>
</div>

<?php
ViewContext::setScripts(['public/assets/js/modules/dossier_etudiant.js']);
require_once __DIR__ . '/../../public/inc/footer.php';
?>
