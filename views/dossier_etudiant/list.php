<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
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

            <!-- Classe / Promotion -->
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
            <div><strong>Classe / Promotion :</strong> <span id="rec-student-classe" style="font-weight: 700;">-</span></div>
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
  #printable-recepisse-area, #printable-recepisse-area * {
    visibility: visible;
  }
  #printable-recepisse-area {
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
  }

  function initTable() {
    table = $('#table-dossiers').DataTable({
      ajax: {
        url: '<?= RACINE ?>dossier_etudiant/apiList',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: function(d) {
          d.annee_code = $('#filter-annee').val();
          d.classe_code = $('#filter-classe').val();
          d.filter_statut = $('#filter-statut').val();
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement DataTables:", error, thrown);
        },
        dataSrc: function(json) {
          var items = json.data || [];
          var totalCount = items.length;
          var completsCount = 0;
          var incompletsCount = 0;
          var totalPiecesGlobal = 0;
          var totalDeposeesGlobal = 0;

          items.forEach(function(i) {
            if (i.est_complet) completsCount++;
            else incompletsCount++;
            totalPiecesGlobal += i.total_pieces;
            totalDeposeesGlobal += i.deposees_count;
          });

          var tauxGlobal = (totalPiecesGlobal > 0) ? Math.round((totalDeposeesGlobal / totalPiecesGlobal) * 100) : 0;

          $('#kpi-total-etudiants').text(totalCount);
          $('#kpi-dossiers-complets').text(completsCount);
          $('#kpi-dossiers-incomplets').text(incompletsCount);
          $('#kpi-taux-global').text(tauxGlobal + '%');

          return items;
        }
      },
      columns: [
        {
          data: null,
          render: function(d) {
            var rawName = (d.nom_complet ? d.nom_complet.trim() : '') || 'Étudiant non identifié';
            var parts = rawName.split(' ').filter(function(p) { return p.length > 0; });
            var initials = 'ET';
            if (parts.length >= 2) {
              initials = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            } else if (parts.length === 1 && parts[0].length >= 2) {
              initials = parts[0].substring(0, 2).toUpperCase();
            }

            var photoPath = d.photo ? String(d.photo).trim() : '';
            var initialsDiv = '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';

            var avatarHtml = initialsDiv;
            if (photoPath !== '') {
              var photoUrl = (photoPath.indexOf('http') === 0) ? photoPath : ('<?= RACINE ?>' + photoPath.replace(/^\//, ''));
              avatarHtml = '<img src="' + photoUrl + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; flex-shrink: 0;" onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';">' +
                           '<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1E3A5F, #0F172A); color: #FFFFFF; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0; border: 2px solid #E2E8F0;">' + escapeHtml(initials) + '</div>';
            }

            return '<div style="display: flex; align-items: center; gap: 12px; min-width: 180px;">' +
                     avatarHtml +
                     '<div>' +
                       '<div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">' + escapeHtml(rawName) + '</div>' +
                     '</div>' +
                   '</div>';
          }
        },
        {
          data: 'matricule',
          render: function(d) {
            return '<span class="badge bg-light text-dark font-monospace" style="font-size: 12px; font-weight: 700; padding: 6px 10px; border: 1px solid #CBD5E1;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: 'classe',
          render: function(d) {
            return '<span style="font-weight: 700; color: #1E3A5F;">' + escapeHtml(d || '-') + '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var badgeClass = d.est_complet ? 'bg-success' : (d.deposees_count > 0 ? 'bg-warning text-dark' : 'bg-danger');
            return '<span class="badge ' + badgeClass + '" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">' +
                   d.deposees_count + ' / ' + d.total_pieces + ' pièce(s)' +
                   '</span>';
          }
        },
        {
          data: null,
          render: function(d) {
            var pct = d.pourcentage;
            var barColor = pct === 100 ? '#16A34A' : (pct >= 50 ? '#EA580C' : '#DC2626');
            return '<div style="min-width: 120px;">' +
                     '<div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #475569; margin-bottom: 3px;">' +
                       '<span>' + pct + '%</span>' +
                     '</div>' +
                     '<div style="height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">' +
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
                     '<button type="button" class="btn btn-sm btn-primary btn-open-dossier" style="font-weight: 700; border-radius: 8px; padding: 6px 11px;" title="Gérer les pièces et les scans">' +
                       '<i data-lucide="folder-open" style="width: 14px; height: 14px; margin-right: 4px;"></i> Gérer' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-primary btn-print-recepisse-row" title="Imprimer le Récépissé" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
                       '<i data-lucide="printer" style="width: 14px; height: 14px;"></i>' +
                     '</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success btn-quick-validate" title="Tout valider en 1 clic" style="font-weight: 700; border-radius: 8px; padding: 6px 10px;">' +
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
  });

  $('.kpi-card-clickable').on('click', function() {
    var status = $(this).data('status');
    $('#filter-statut').val(status).trigger('change.select2');
    table.ajax.reload();
  });

  $('#btn-refresh').on('click', function() {
    table.ajax.reload();
  });

  // Action Gérer le dossier (Modal)
  $('#table-dossiers').on('click', '.btn-open-dossier', function() {
    var rowData = table.row($(this).closest('tr')).data();
    currentStudentData = rowData;
    openDossierModal(rowData);
  });

  // Action Impression Récépissé en ligne
  $('#table-dossiers').on('click', '.btn-print-recepisse-row', function() {
    var rowData = table.row($(this).closest('tr')).data();
    loadAndOpenRecepisseModal(rowData.code_inscription);
  });

  $('#btn-print-modal-recepisse').on('click', function() {
    if (currentStudentData) {
      loadAndOpenRecepisseModal(currentStudentData.code_inscription);
    }
  });

  // Fermeture des modals
  $('.btn-close-modal-dossier').on('click', function() {
    $('#modal-dossier').hide();
  });
  $(window).on('click', function(e) {
    if ($(e.target).is('#modal-dossier')) $('#modal-dossier').hide();
    if ($(e.target).is('#modal-recepisse')) $('#modal-recepisse').hide();
  });

  $('.btn-close-modal-recepisse').on('click', function() {
    $('#modal-recepisse').hide();
  });

  // Action Tout valider rapide
  $('#table-dossiers').on('click', '.btn-quick-validate', function() {
    var rowData = table.row($(this).closest('tr')).data();
    validateAllStudentPieces(rowData.code_inscription, rowData.code_etudiant);
  });

  $('#btn-tout-valider-modal').on('click', function() {
    if (currentStudentData) {
      validateAllStudentPieces(currentStudentData.code_inscription, currentStudentData.code_etudiant);
    }
  });

  function updatePieceCardStyle($card, st) {
    var borderCol = '#EA580C';
    var bgCol = '#FFF7ED';
    if (st === 'depose') {
      borderCol = '#16A34A';
      bgCol = '#F0FDF4';
    } else if (st === 'rejete') {
      borderCol = '#DC2626';
      bgCol = '#FEF2F2';
    } else if (st === 'non_requis') {
      borderCol = '#64748B';
      bgCol = '#F8FAFC';
    }
    $card.css({
      'border-left': '5px solid ' + borderCol,
      'background': bgCol
    });
  }

  $(document).on('change', '.select-statut-piece', function() {
    var st = $(this).val();
    var $card = $(this).closest('.piece-card-form');
    updatePieceCardStyle($card, st);
  });

  function openDossierModal(d) {
    $('#modal-student-name').text(d.nom_complet);
    $('#modal-student-info').text('Matricule : ' + (d.matricule || '-') + ' | Classe : ' + (d.classe || '-'));
    $('#modal-completion-text').text(d.deposees_count + ' / ' + d.total_pieces + ' Pièce(s) Déposée(s) (' + d.pourcentage + '%)');

    var html = '';
    if (d.dossier_items && d.dossier_items.length > 0) {
      d.dossier_items.forEach(function(item) {
        var st = item.statut_depot || 'en_attente';
        var borderCol = st === 'depose' ? '#16A34A' : (st === 'rejete' ? '#DC2626' : (st === 'non_requis' ? '#64748B' : '#EA580C'));
        var bgCol = st === 'depose' ? '#F0FDF4' : (st === 'rejete' ? '#FEF2F2' : (st === 'non_requis' ? '#F8FAFC' : '#FFF7ED'));

        var fileHtml = '';
        if (item.fichier_joint) {
          var fullFileUrl = item.fichier_joint.startsWith('http') ? item.fichier_joint : ('<?= RACINE ?>' + item.fichier_joint);
          fileHtml = '<div style="margin-top: 6px;"><a href="' + fullFileUrl + '" target="_blank" class="btn btn-sm btn-outline-info" style="font-size: 11.5px; font-weight: 700; border-radius: 6px; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="file-text" style="width: 14px; height: 14px;"></i> Voir le scan joint</a></div>';
        }

        var dateDepotText = item.date_depot ? ('<span style="font-size: 11px; font-weight: 600; color: #15803D; margin-left: 6px;">(Déposé le ' + item.date_depot + ')</span>') : '';

        html += '<form class="piece-card-form" style="background: ' + bgCol + '; border-left: 5px solid ' + borderCol + '; padding: 16px; border-radius: 12px; border-top: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">' +
                  '<input type="hidden" name="inscription_code" value="' + d.code_inscription + '">' +
                  '<input type="hidden" name="etudiant_code" value="' + d.code_etudiant + '">' +
                  '<input type="hidden" name="piece_code" value="' + item.piece_code + '">' +
                  
                  '<div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">' +
                    '<div>' +
                      '<div style="font-weight: 800; color: #0F172A; font-size: 14px;">' + escapeHtml(item.libelle_piece) + dateDepotText + '</div>' +
                      '<div style="font-size: 12px; color: #64748B; margin-top: 2px;">' + escapeHtml(item.description_piece || 'Pièce exigée pour le dossier') + '</div>' +
                      fileHtml +
                    '</div>' +
                    '<div>' +
                      '<select name="statut" class="form-select form-select-sm select-statut-piece" style="font-weight: 700; border-radius: 8px; min-width: 140px;">' +
                        '<option value="depose" ' + (st === 'depose' ? 'selected' : '') + '>✅ Déposé</option>' +
                        '<option value="en_attente" ' + (st === 'en_attente' ? 'selected' : '') + '>⏳ En attente</option>' +
                        '<option value="rejete" ' + (st === 'rejete' ? 'selected' : '') + '>❌ Rejeté</option>' +
                        '<option value="non_requis" ' + (st === 'non_requis' ? 'selected' : '') + '>⚪ Non requis</option>' +
                      '</select>' +
                    '</div>' +
                  '</div>' +

                  '<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">' +
                    '<div style="flex: 1; min-width: 180px;">' +
                      '<input type="text" name="observations" class="form-control form-control-sm" placeholder="Remarques / Observations..." value="' + escapeHtml(item.observations || '') + '" style="border-radius: 6px; font-size: 12px;">' +
                    '</div>' +
                    '<div style="flex: 1; min-width: 180px;">' +
                      '<input type="file" name="fichier_joint" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" title="Joindre un scan numérisé (PDF/Image)" style="font-size: 11px; border-radius: 6px;">' +
                    '</div>' +
                    '<div>' +
                      '<button type="submit" class="btn btn-sm btn-primary btn-save-piece" style="font-weight: 700; border-radius: 6px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer</button>' +
                    '</div>' +
                  '</div>' +
                '</form>';
      });
    } else {
      html = '<div class="alert alert-info text-center" style="border-radius: 10px;">Aucune pièce exigée configurée pour le cycle de cette classe.</div>';
    }

    $('#modal-pieces-list').html(html);
    $('#modal-dossier').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  }

  // Action enregistrer modification statut unitaire avec upload éventuel
  $(document).on('submit', '.piece-card-form', function(e) {
    e.preventDefault();
    var form = this;
    var $btn = $(form).find('.btn-save-piece');
    var formData = new FormData(form);

    $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spin" style="width: 14px; height: 14px;"></i>');

    $.ajax({
      url: '<?= RACINE ?>dossier_etudiant/saveStatut',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistré !');
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') showToast(res.message || 'Pièce mise à jour avec succès', 'success');
          else if (window.toastr) toastr.success(res.message || 'Pièce mise à jour avec succès');
          setTimeout(function() { $btn.html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer'); }, 1500);
          table.ajax.reload(null, false);
        } else {
          var err = res.message || 'Erreur lors de l\'enregistrement';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 14px; height: 14px;"></i> Enregistrer');
        var msg = 'Erreur lors de la mise à jour.';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
      }
    });
  });

  // Action Tout valider rapide
  function validateAllStudentPieces(insCode, etuCode) {
    if (!confirm("Voulez-vous vraiment marquer TOUTES les pièces de ce dossier comme DÉPOSÉES ?")) return;

    $.ajax({
      url: '<?= RACINE ?>dossier_etudiant/saveAll',
      type: 'POST',
      data: {
        inscription_code: insCode,
        etudiant_code: etuCode
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          var msgSuccess = res.message || 'Toutes les pièces ont été validées avec succès.';
          if (typeof showToast === 'function') showToast(msgSuccess, 'success');
          else if (window.toastr) toastr.success(msgSuccess);
          table.ajax.reload(null, false);
          $('#modal-dossier').hide();
        } else {
          var msgErr = res.message || 'Erreur lors de la validation.';
          if (typeof showToast === 'function') showToast(msgErr, 'error');
          else if (window.toastr) toastr.error(msgErr);
        }
      },
      error: function() {
        var msgErr2 = 'Erreur de connexion au serveur.';
        if (typeof showToast === 'function') showToast(msgErr2, 'error');
        else if (window.toastr) toastr.error(msgErr2);
      }
    });
  }

  // Chargement et impression du Bordereau / Récépissé 
  function loadAndOpenRecepisseModal(inscrCode) {
    $.ajax({
      url: '<?= RACINE ?>dossier_etudiant/getRecepisseData',
      type: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: { inscription_code: inscrCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.student) {
          var s = res.student;
          $('#rec-student-name').text((s.nom_etudiant || '') + ' ' + (s.prenom_etudiant || ''));
          $('#rec-student-matricule').text(s.matricule_etudiant || '-');
          $('#rec-student-classe').text(s.libelle_classe || '-');
          $('#rec-student-phone').text(s.telephone_etudiant || '-');
          $('#rec-annee').text(s.libelle_annee ? ('Année Académique ' + s.libelle_annee) : 'GEICG');
          $('#rec-date-emission').text(res.date_emission || '-');
          $('#rec-agent-nom').text(res.agent_nom || 'Administration');

          if (s.nom_etablissement) $('#rec-etab-nom').text(s.nom_etablissement);
          if (s.adresse_etablissement) $('#rec-etab-adresse').text((s.adresse_etablissement || '') + ' ' + (s.ville_etablissement || ''));
          if (s.telephone_etablissement) $('#rec-etab-tel').text('Tél : ' + s.telephone_etablissement);
          if (s.logo_etablissement) $('#rec-etab-logo').attr('src', '<?= RACINE ?>' + s.logo_etablissement).show();

          $('#rec-count-deposees').text(res.deposees_count || 0);
          $('#rec-count-manquantes').text(res.manquantes_count || 0);

          // Table pièces déposées
          var htmlDep = '';
          if (res.pieces_deposees && res.pieces_deposees.length > 0) {
            res.pieces_deposees.forEach(function(p) {
              htmlDep += '<tr>' +
                           '<td style="padding:8px;"><strong>' + escapeHtml(p.libelle_piece) + '</strong></td>' +
                           '<td style="padding:8px;" class="text-success fw-bold">✓ ' + (p.date_depot || 'Déposé') + '</td>' +
                           '<td style="padding:8px;">' + escapeHtml(p.observations || '-') + '</td>' +
                         '</tr>';
            });
          } else {
            htmlDep = '<tr><td colspan="3" class="text-muted text-center" style="padding:10px;">Aucune pièce déposée pour le moment.</td></tr>';
          }
          $('#rec-table-deposees').html(htmlDep);

          // Table pièces manquantes
          var htmlManq = '';
          if (res.pieces_manquantes && res.pieces_manquantes.length > 0) {
            res.pieces_manquantes.forEach(function(p) {
              var badgeSt = p.statut_depot === 'rejete' ? '<span class="badge bg-danger">❌ Rejeté</span>' : '<span class="badge bg-warning text-dark">⏳ En attente</span>';
              htmlManq += '<tr>' +
                            '<td style="padding:8px;"><strong>' + escapeHtml(p.libelle_piece) + '</strong></td>' +
                            '<td style="padding:8px;">' + badgeSt + '</td>' +
                            '<td style="padding:8px;">' + escapeHtml(p.observations || 'À fournir impérativement') + '</td>' +
                          '</tr>';
            });
          } else {
            htmlManq = '<tr><td colspan="3" class="text-success text-center fw-bold" style="padding:10px;">🎉 Dossier complet ! Aucune pièce manquante.</td></tr>';
          }
          $('#rec-table-manquantes').html(htmlManq);

          $('#modal-recepisse').css('display', 'flex');
          if (window.lucide) lucide.createIcons();
        } else {
          var err = res.message || 'Erreur lors de la génération du récépissé.';
          if (typeof showToast === 'function') showToast(err, 'error');
          else if (window.toastr) toastr.error(err);
          else if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: err });
        }
      },
      error: function(xhr) {
        var msg = 'Erreur lors de la connexion au serveur.';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') showToast(msg, 'error');
        else if (window.toastr) toastr.error(msg);
        else if (window.Swal) Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
      }
    });
  }

  $('#btn-trigger-print-recepisse').on('click', function() {
    window.print();
  });

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
