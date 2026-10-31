<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$niveaux = $niveaux ?? [];
$canSendRelance = $canSendRelance ?? true;
$canRecordPayment = $canRecordPayment ?? true;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE PAGE ET BOUTONS D'ACTIONS HAUTS -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="alert-triangle" style="width: 26px; height: 26px; color: #DC2626;"></i>
            Suivi des Impayés & Retards de Versements
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Détection automatique des échéances de scolarité échues et relances multi-canaux des parents/tuteurs
          </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
          <button type="button" id="btn-open-group-sms-modal" class="btn" style="background: #EA580C; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 18px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(234,88,12,0.25); cursor: pointer;">
            <i data-lucide="message-square" style="width: 18px; height: 18px;"></i> Notification par SMS
          </button>

          <button type="button" id="btn-open-group-whatsapp-modal" class="btn" style="background: #16A34A; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 18px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(22,163,74,0.25); cursor: pointer;">
            <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i> Notification WhatsApp
          </button>

          <button type="button" id="btn-imprimer-en-masse" class="btn" style="background: #2ba3dfff; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 18px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(58, 165, 237, 0.25); cursor: pointer;">
            <i data-lucide="files" style="width: 18px; height: 18px;"></i> Imprimer en Masse
          </button>

          <a id="btn-print-impayes-list" href="<?= RACINE ?>impayes/imprimerListeImpayes" target="_blank" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 18px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(30,58,95,0.25); text-decoration: none;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer Liste
          </a>
        </div>
      </div>

      <!-- BANDEAU DE CARTES SYNTHÈSE KPIS -->
      <!-- BANDEAU DE CARTES SYNTHÈSE KPIS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- Card 1: Total Impayés Échus -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #DC2626; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Impayés Échus</div>
            <div id="kpi-total-impayes" style="font-size: 20px; font-weight: 900; color: #DC2626; margin-top: 4px;">0 FCFA</div>
            <div style="font-size: 11.5px; color: #94A3B8; margin-top: 4px;">Cumul des tranches dépassées</div>
          </div>
          <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="banknote" style="width: 18px; height: 18px;"></i>
          </div>
        </div>

        <!-- Card 2: Étudiants en Retard -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #EA580C; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total en Retard</div>
            <div id="kpi-total-etudiants" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 4px;">0</div>
            <div style="font-size: 11.5px; color: #94A3B8; margin-top: 4px;">Dossiers avec solde échu</div>
          </div>
          <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 8px; background: #FFEDD5; color: #EA580C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="users" style="width: 18px; height: 18px;"></i>
          </div>
        </div>

        <!-- Card 3: Nombre d'Affectés -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #2563EB; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants Affectés</div>
            <div id="kpi-total-affectes" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 4px;">0</div>
            <div style="font-size: 11.5px; color: #94A3B8; margin-top: 4px;">Bénéficiaires État / Affectés</div>
          </div>
          <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 8px; background: #DBEAFE; color: #2563EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
          </div>
        </div>

        <!-- Card 4: Nombre de Non Affectés -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #9333EA; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Non Affectés (Privés)</div>
            <div id="kpi-total-non-affectes" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 4px;">0</div>
            <div style="font-size: 11.5px; color: #94A3B8; margin-top: 4px;">Régulier / Direct privé</div>
          </div>
          <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 8px; background: #F3E8FF; color: #9333EA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-x" style="width: 18px; height: 18px;"></i>
          </div>
        </div>

        <!-- Card 5: Relances Effectuées Ce Mois -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #059669; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Relances Ce Mois</div>
            <div id="kpi-relances-mois" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 4px;">0</div>
            <div style="font-size: 11.5px; color: #94A3B8; margin-top: 4px;">Rappels émis aux parents</div>
          </div>
          <div class="kpi-icon-box" style="width: 38px; height: 38px; border-radius: 8px; background: #D1FAE5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="send" style="width: 18px; height: 18px;"></i>
          </div>
        </div>
      </div>

      <!-- FILTRES MULTI-CRITÈRES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: center;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Année Académique</label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau d'Études</label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Classe</label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau de Sévérité</label>
            <select id="filter-severite" class="form-control select2" style="width: 100%;">
              <option value="ALL">Tous les retards</option>
              <option value="leger">🟡 Retard Léger (1-15j)</option>
              <option value="modere">🟠 Retard Modéré (16-30j)</option>
              <option value="critique">🔴 Retard Critique (>30j)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- TABLEAU COMPTABLE DES IMPAYÉS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-impayes" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                <th style="padding: 12px; width: 36px;" class="text-center">
                  <input type="checkbox" id="check-all-impayes" style="width: 17px; height: 17px; cursor: pointer; vertical-align: middle;">
                </th>
                <th style="padding: 12px; width: 40px;" class="text-center">#</th>
                <th style="padding: 12px;">Élève / Étudiant</th>
                <th style="padding: 12px;">Parent / Contact</th>
                <th style="padding: 12px;">Classe & Régime</th>
                <th style="padding: 12px;">Libellé Tranche</th>
                <th style="padding: 12px;" class="text-center">Date d'Échéance</th>
                <th style="padding: 12px;" class="text-end">Total Versé</th>
                <th style="padding: 12px;" class="text-end">Reste à Payer</th>
                <th style="padding: 12px; text-align: center; width: 140px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL D'ÉMISSION DE RELANCE INDIVIDUELLE (Style GEICG) -->
<div id="modalRelanceImpaye" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 10000; justify-content: center; align-items: center; padding: 16px; box-sizing: border-box;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: modalZoomIn 0.25s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h5 style="font-weight: 800; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="send" style="width: 20px; height: 20px;"></i> Émettre une Relance Individuelle
      </h5>
      <button type="button" class="btn-close-modal-relance" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>
    
    <form id="formRelanceImpaye">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" id="relance_etudiant_code" name="etudiant_code" value="">
      <input type="hidden" id="relance_inscription_code" name="inscription_code" value="">

      <div style="padding: 20px;">
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
          <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Étudiant Destinataire</div>
          <div id="relance_student_name" style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">-</div>
          <div id="relance_student_meta" style="font-size: 12px; color: #64748B; margin-top: 1px;">-</div>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau de Relance</label>
          <select name="niveau_relance" id="relance_niveau_relance" class="form-control" style="border-radius: 8px; font-weight: 600;" onchange="updateRelanceTemplate()">
            <option value="rappel_amiable">Rappel Amiable (1er Rappel)</option>
            <option value="relance_ferme">Relance Ferme (Délai 48h)</option>
            <option value="mise_en_demeure">Mise en Demeure Officielle</option>
          </select>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Canal d'Expédition</label>
          <select name="canal_relance" id="relance_canal_relance" class="form-control" style="border-radius: 8px; font-weight: 600;">
            <option value="sms">SMS Direct (Téléphone Tuteur)</option>
            <option value="whatsapp">WhatsApp Officiel</option>
            <option value="email">Courrier Électronique (Email)</option>
            <option value="appel">Appel Téléphonique (Enregistré)</option>
          </select>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Téléphone / Contact Destinataire</label>
          <input type="text" name="telephone_destinataire" id="relance_telephone_destinataire" class="form-control" style="border-radius: 8px; font-weight: 700; font-family: monospace;" required>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Montant Impayé Échu (FCFA)</label>
          <input type="number" name="montant_impaye" id="relance_montant_impaye" class="form-control" style="border-radius: 8px; font-weight: 800; color: #DC2626; background: #F8FAFC;" required readonly>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Message du Rappel</label>
          <textarea name="message_relance" id="relance_message_relance" rows="4" class="form-control" style="border-radius: 8px; font-size: 12.5px;" required></textarea>
        </div>
      </div>

      <div style="background: #F8FAFC; padding: 14px 20px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-close-modal-relance" style="font-weight: 700; border-radius: 8px; background: #E2E8F0; color: #475569; border: none; padding: 8px 16px;">Annuler</button>
        <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; border-radius: 8px; padding: 8px 18px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="send" style="width: 16px; height: 16px;"></i> Confirmer & Journaliser la Relance
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL DE RELANCE GROUPÉE PAR SMS -->
<div id="modalRelanceGroupSMS" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 10001; justify-content: center; align-items: center; padding: 16px; box-sizing: border-box;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 580px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: modalZoomIn 0.25s ease-out;">
    <div style="background: #EA580C; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h5 style="font-weight: 800; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="message-square" style="width: 20px; height: 20px;"></i> Notification par SMS (Relance Groupée)
      </h5>
      <button type="button" class="btn-close-modal-group-sms" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>
    
    <form id="formRelanceGroupSMS">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

      <div style="padding: 20px;">
        <!-- Résumé de la sélection -->
        <div style="background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #C2410C; text-transform: uppercase;">Sélection Active</div>
            <div id="group_sms_count" style="font-size: 15px; font-weight: 900; color: #9A3412; margin-top: 2px;">0 Étudiant(s) Sélectionné(s)</div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 11px; font-weight: 800; color: #C2410C; text-transform: uppercase;">Cumul Impayés Échus</div>
            <div id="group_sms_total_amount" style="font-size: 15px; font-weight: 900; color: #DC2626; margin-top: 2px;">0 FCFA</div>
          </div>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Niveau de Relance</label>
          <select name="niveau_relance" id="group_niveau_relance" class="form-control" style="border-radius: 8px; font-weight: 600;" onchange="updateGroupSMSTemplate()">
            <option value="rappel_amiable">Rappel Amiable (1er Rappel)</option>
            <option value="relance_ferme">Relance Ferme (Délai 48h)</option>
            <option value="mise_en_demeure">Mise en Demeure Officielle</option>
          </select>
        </div>

        <div class="mb-3">
          <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-bottom: 4px; display: block;">Canal d'Expédition</label>
          <select name="canal_relance" id="group_canal_relance" class="form-control" style="border-radius: 8px; font-weight: 600;">
            <option value="sms">SMS Direct (Téléphones Tuteurs)</option>
            <option value="whatsapp">WhatsApp Officiel</option>
            <option value="email">Courrier Électronique (Email)</option>
          </select>
        </div>

        <div class="mb-3">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
            <label style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin: 0;">Message Modèle Révisable</label>
            <span style="font-size: 10.5px; color: #64748B;">Balises : <code>{NOM_ETUDIANT}</code>, <code>{MONTANT_ECHU}</code>, <code>{CLASSE}</code></span>
          </div>
          <textarea name="message_relance" id="group_message_relance" rows="5" class="form-control" style="border-radius: 8px; font-size: 12.5px;" required></textarea>
        </div>
      </div>

      <div style="background: #F8FAFC; padding: 14px 20px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-close-modal-group-sms" style="font-weight: 700; border-radius: 8px; background: #E2E8F0; color: #475569; border: none; padding: 8px 16px;">Annuler</button>
        <button type="submit" class="btn" style="background: #EA580C; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 8px 18px; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer;">
          <i data-lucide="send" style="width: 16px; height: 16px;"></i> Confirmer & Envoyer la Relance Groupée
        </button>
      </div>
    </form>
  </div>
</div>

<?php ViewContext::setScripts(['public/assets/js/modules/paiements.js']); ?>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
