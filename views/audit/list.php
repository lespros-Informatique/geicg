<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="history" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
            Journal d'Audit & Traçabilité des Logs
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Historique chronologique et surveillance des actions effectuées sur le système</p>
        </div>
        <div>
          <button type="button" id="btn-refresh-logs" class="btn btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 9px 16px; cursor: pointer;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Actualiser
          </button>
        </div>
      </div>

      <!-- Filtres de recherche -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <form id="form-filter-logs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 160px; gap: 16px; align-items: flex-end;">
          
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="tag" style="width: 14px; height: 14px; color: #64748B;"></i> Type d'Action
            </label>
            <select id="filter-action" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les actions --</option>
              <option value="CONNEXION">Connexion</option>
              <option value="DECONNEXION">Déconnexion</option>
              <option value="AJOUT">Création / Ajout</option>
              <option value="MODIFICATION">Modification</option>
              <option value="SUPPRESSION">Suppression</option>
              <option value="IMPRESSION">Impression / Export</option>
            </select>
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="box" style="width: 14px; height: 14px; color: #64748B;"></i> Module Applicatif
            </label>
            <select id="filter-module" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les modules --</option>
              <?php foreach ($modules as $mod): ?>
                <option value="<?= htmlspecialchars($mod) ?>"><?= htmlspecialchars(strtoupper($mod)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i> Date Début
            </label>
            <input type="date" id="filter-date-debut" class="form-control" style="width: 100%; height: 42px; border-radius: 8px; font-size: 13px;">
          </div>

          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i> Date Fin
            </label>
            <input type="date" id="filter-date-fin" class="form-control" style="width: 100%; height: 42px; border-radius: 8px; font-size: 13px;">
          </div>

          <div style="display: flex; gap: 8px; height: 42px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; height: 100%; background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
              <i data-lucide="filter" style="width: 15px; height: 15px;"></i> Filtrer
            </button>
            <button type="button" id="btn-reset-filters" class="btn btn-secondary" style="height: 100%; font-weight: 700; border-radius: 8px; padding: 0 14px; display: inline-flex; align-items: center; justify-content: center;" title="Réinitialiser les filtres">
              <i data-lucide="rotate-ccw" style="width: 15px; height: 15px;"></i>
            </button>
          </div>

        </form>
      </div>

      <!-- Tableau des logs -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-audit-logs" class="table display nowrap" style="width: 100%; max-width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 45px;">#</th>
                <th style="padding: 12px; width: 140px;">Date & Heure</th>
                <th style="padding: 12px; width: 180px;">Utilisateur</th>
                <th style="padding: 12px; width: 110px;" class="text-center">Action</th>
                <th style="padding: 12px; width: 110px;">Module</th>
                <th style="padding: 12px; width: 120px;">Réf / Cible</th>
                <th style="padding: 12px;">Description de l'action</th>
                <th style="padding: 12px; width: 110px;">Adresse IP</th>
                <th style="padding: 12px; width: 70px; text-align: center;">Détails</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL DÉTAILS DU LOG -->
<div class="modal fade" id="modal-log-details" tabindex="-1" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
      <div class="modal-header" style="background: #1E3A5F; color: #FFFFFF; border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 16px 20px;">
        <h5 class="modal-title" style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px; margin: 0;">
          <i data-lucide="info" style="width: 18px; height: 18px;"></i> Détails Complets de l'Action
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" style="padding: 22px;">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
          <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Auteur de l'action</div>
            <div id="modal-log-user" style="font-size: 13.5px; font-weight: 700; color: #0F172A; margin-top: 3px;">-</div>
            <div id="modal-log-usercode" style="font-size: 11.5px; color: #64748B; font-family: monospace;">-</div>
          </div>
          <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Horodatage & IP</div>
            <div id="modal-log-date" style="font-size: 13.5px; font-weight: 700; color: #1E3A5F; margin-top: 3px;">-</div>
            <div id="modal-log-ip" style="font-size: 11.5px; color: #64748B;">IP : -</div>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Description de l'action</div>
          <div id="modal-log-desc" style="font-size: 13px; font-weight: 600; color: #0F172A; background: #F1F5F9; padding: 10px 12px; border-radius: 6px;">-</div>
        </div>

        <div style="margin-bottom: 16px;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Navigateur / Agent Utilisateur</div>
          <div id="modal-log-useragent" style="font-size: 11px; color: #475569; word-break: break-all; font-family: monospace; background: #F8FAFC; padding: 8px 10px; border-radius: 6px; border: 1px solid #E2E8F0;">-</div>
        </div>

        <div>
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Données contextuelles / Valeurs modifiées (JSON)</div>
          <pre id="modal-log-json" style="background: #0F172A; color: #38BDF8; padding: 14px; border-radius: 8px; font-size: 12px; max-height: 250px; overflow-y: auto; font-family: monospace; white-space: pre-wrap; margin: 0;">Aucune donnée complémentaire</pre>
        </div>

      </div>
      <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-weight: 700; border-radius: 8px; padding: 8px 16px;">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script type="application/json" id="audit-list-config">
<?= json_encode([
    'racine' => RACINE,
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>

<?php
ViewContext::setScripts(['public/assets/js/modules/audit_logs.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>

