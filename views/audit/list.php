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
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('audit/imprimerPdf') ?>" id="btn-print-audit" target="_blank" class="btn btn-outline-primary" style="background: #FFFFFF; border: 1.5px solid #1E3A5F; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 9px 16px; text-decoration: none; cursor: pointer; transition: all 0.2s;" title="Générer et imprimer le journal d'audit en PDF">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer le Journal
          </a>
          <button type="button" id="btn-refresh-logs" class="btn btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 9px 16px; cursor: pointer;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Actualiser
          </button>
        </div>
      </div>

      <!-- Filtres de recherche -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <form id="form-filter-logs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)) 160px; gap: 16px; align-items: flex-end;">
          
          <!-- Filtre Utilisateur (Nom, Numéro, Code) -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="user" style="width: 14px; height: 14px; color: #64748B;"></i> Utilisateur (Nom, N°, Code)
            </label>
            <select id="filter-user" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les utilisateurs --</option>
              <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): ?>
                  <?php 
                    $uNom = trim(($u['nom_user'] ?? '') . ' ' . ($u['prenom_user'] ?? ''));
                    $uCode = $u['code_user'] ?? '-';
                    $uTel = !empty($u['telephone_user']) ? $u['telephone_user'] : 'Sans tél';
                  ?>
                  <option value="<?= $u['id_user'] ?>">
                    <?= htmlspecialchars($uNom) ?> | <?= htmlspecialchars($uCode) ?> | <?= htmlspecialchars($uTel) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

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

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : DÉTAILS COMPLETS DU LOG -->
<!-- ========================================================================= -->
<div id="modal-log-details" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(3px); z-index: 99999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 680px; max-height: 90vh; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column;">
    
    <!-- En-tête de la modale -->
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="info" style="width: 18px; height: 18px;"></i> Détails Complets de l'Opération
      </h3>
      <button type="button" class="btn-close-modal-log" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
    </div>

    <!-- Corps de la modale avec défilement -->
    <div style="padding: 22px; overflow-y: auto; flex: 1;">
      
      <!-- Cartouche Auteur & Date/IP -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
        <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Auteur de l'action</div>
          <div id="modal-log-user" style="font-size: 13.5px; font-weight: 700; color: #0F172A; margin-top: 3px;">-</div>
          <div id="modal-log-usercode" style="font-size: 11.5px; color: #64748B; font-family: monospace;">-</div>
        </div>
        <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Horodatage & IP</div>
          <div id="modal-log-date" style="font-size: 13.5px; font-weight: 700; color: #1E3A5F; margin-top: 3px;">-</div>
          <div id="modal-log-ip" style="font-size: 11.5px; color: #64748B; font-family: monospace; margin-top: 2px;">IP : -</div>
        </div>
      </div>

      <!-- Action & Module & Cible & Établissement -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 16px;">
        <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Type d'Action</div>
          <div id="modal-log-action" style="margin-top: 4px;">-</div>
        </div>
        <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Module</div>
          <div id="modal-log-module" style="font-size: 13px; font-weight: 700; color: #1E3A5F; margin-top: 4px;">-</div>
        </div>
        <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Réf. Cible</div>
          <div id="modal-log-entite" style="font-size: 12px; font-weight: 600; font-family: monospace; color: #0F172A; margin-top: 4px;">-</div>
        </div>
        <div style="background: #F8FAFC; padding: 10px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
          <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Établissement</div>
          <div id="modal-log-etab" style="font-size: 12px; font-weight: 700; color: #1E3A5F; margin-top: 4px;">-</div>
        </div>
      </div>

      <!-- Description complète -->
      <div style="margin-bottom: 16px;">
        <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Description de l'opération</div>
        <div id="modal-log-desc" style="font-size: 13px; font-weight: 600; color: #0F172A; background: #F1F5F9; padding: 10px 14px; border-radius: 8px; border-left: 3px solid #1E3A5F;">-</div>
      </div>

      <!-- Navigateur / User Agent -->
      <div style="margin-bottom: 16px;">
        <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Navigateur & Appareil (User-Agent)</div>
        <div id="modal-log-useragent" style="font-size: 11px; color: #475569; word-break: break-all; font-family: monospace; background: #F8FAFC; padding: 8px 12px; border-radius: 6px; border: 1px solid #E2E8F0;">-</div>
      </div>

      <!-- Données contextuelles JSON -->
      <div>
        <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Données contextuelles / Valeurs transmises (JSON)</div>
        <pre id="modal-log-json" style="background: #0F172A; color: #38BDF8; padding: 14px; border-radius: 8px; font-size: 12px; max-height: 220px; overflow-y: auto; font-family: monospace; white-space: pre-wrap; margin: 0; border: 1px solid #334155;">Aucune donnée complémentaire</pre>
      </div>

    </div>

    <!-- Pied de la modale -->
    <div style="padding: 12px 20px; border-top: 1px solid #E2E8F0; background: #F8FAFC; display: flex; justify-content: flex-end;">
      <button type="button" class="btn btn-secondary btn-close-modal-log" style="font-weight: 700; border-radius: 8px; padding: 8px 20px; cursor: pointer;">Fermer</button>
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

