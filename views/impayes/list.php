<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$classes = $classes ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
$canSendRelance = $canSendRelance ?? true;
$canRecordPayment = $canRecordPayment ?? true;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE PAGE -->
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
      </div>

      <!-- BANDEAU DE CARTES SYNTHÈSE KPIS -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- Card 1: Total Impayés Échus -->
        <div class="kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #DC2626;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Impayés Échus</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="banknote" style="width: 18px; height: 18px;"></i>
            </div>
          </div>
          <div id="kpi-total-impayes" style="font-size: 22px; font-weight: 900; color: #DC2626; margin-top: 10px;">0 FCFA</div>
          <div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Cumul des tranches dépassées</div>
        </div>

        <!-- Card 2: Étudiants en Retard -->
        <div class="kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #EA580C;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants en Retard</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #FFEDD5; color: #EA580C; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="users" style="width: 18px; height: 18px;"></i>
            </div>
          </div>
          <div id="kpi-total-etudiants" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 10px;">0</div>
          <div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Dossiers avec solde échu</div>
        </div>

        <!-- Card 3: Durée Retard Moyenne -->
        <div class="kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #D97706;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Retard Moyen</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
            </div>
          </div>
          <div id="kpi-retard-moyen" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 10px;">0 jour(s)</div>
          <div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Dépassement d'échéance moyen</div>
        </div>

        <!-- Card 4: Relances Effectuées Ce Mois -->
        <div class="kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid #2563EB;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Relances Ce Mois</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #DBEAFE; color: #2563EB; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="send" style="width: 18px; height: 18px;"></i>
            </div>
          </div>
          <div id="kpi-relances-mois" style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 10px;">0</div>
          <div style="font-size: 11.5px; color: #94A3B8; margin-top: 2px;">Rappels émis aux parents</div>
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
                <th style="padding: 12px; width: 40px;" class="text-center">#</th>
                <th style="padding: 12px;">Élève / Étudiant</th>
                <th style="padding: 12px;">Parent / Contact</th>
                <th style="padding: 12px;">Classe & Régime</th>
                <th style="padding: 12px;">Échéance Échue</th>
                <th style="padding: 12px;" class="text-center">Retard</th>
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

<!-- MODAL D'ÉMISSION DE RELANCE UNIFIÉ (Style GEICG) -->
<div id="modalRelanceImpaye" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 10000; justify-content: center; align-items: center; padding: 16px; box-sizing: border-box;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: modalZoomIn 0.25s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h5 style="font-weight: 800; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="send" style="width: 20px; height: 20px;"></i> Émettre une Relance aux Tuteurs
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

<script>
$(document).ready(function() {
  $('#modalRelanceImpaye').appendTo('body');
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee, #filter-niveau, #filter-classe, #filter-severite').select2({ width: '100%' });
  }

  function formatFCFA(val) {
    return Number(val || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  // Filtrage en cascade Niveau -> Classe
  $('#filter-niveau').on('change', function() {
    var niveauCode = $(this).val();
    $('#filter-classe option').each(function() {
      var optNiveau = $(this).data('niveau');
      if (!niveauCode || niveauCode === 'ALL' || !optNiveau || optNiveau === niveauCode || $(this).val() === 'ALL') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($('#filter-classe option:selected').prop('disabled')) {
      $('#filter-classe').val('ALL').trigger('change.select2');
    } else {
      $('#filter-classe').select2({ width: '100%' });
    }
    table.ajax.reload();
  });

  $('#filter-classe, #filter-annee, #filter-severite').on('change', function() {
    table.ajax.reload();
  });

  var table = $('#table-impayes').DataTable({
    ajax: {
      url: '<?= RACINE ?>impayes/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.severite = $('#filter-severite').val();
      },
      dataSrc: function(json) {
        if (json.kpis) {
          $('#kpi-total-impayes').text(formatFCFA(json.kpis.total_impayes_echus));
          $('#kpi-total-etudiants').text(json.kpis.total_etudiants_retard || 0);
          $('#kpi-retard-moyen').text((json.kpis.retard_moyen_jours || 0) + ' jour(s)');
          $('#kpi-relances-mois').text(json.kpis.total_relances_mois || 0);
        }
        return json.data || [];
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, className: 'text-center', width: '40px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { 
        data: null,
        render: function(d) {
          let photoHtml = d.photo 
            ? `<img src="${d.photo}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1.5px solid #E2E8F0;">`
            : `<div style="width: 36px; height: 36px; border-radius: 50%; background: #1E3A5F; color: #FFF; font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; justify-content: center;">${d.initiales}</div>`;

          return `<div style="display: flex; align-items: center; gap: 10px;">
                    ${photoHtml}
                    <div>
                      <div style="font-weight: 800; color: #0F172A; font-size: 13px;">${d.nom_complet}</div>
                      <div style="font-size: 11.5px; color: #64748B; font-family: monospace; font-weight: 700;">${d.matricule}</div>
                    </div>
                  </div>`;
        }
      },
      { 
        data: null,
        render: function(d) {
          return `<div>
                    <div style="font-size: 12.5px; font-weight: 700; color: #1E293B;">${d.nom_parent}</div>
                    <div style="font-size: 11.5px; color: #2563EB; font-weight: 700; font-family: monospace;">📞 ${d.telephone_parent}</div>
                  </div>`;
        }
      },
      { 
        data: null,
        render: function(d) {
          let regBadge = d.regime === 'Affecté' ? 'background: #EFF6FF; color: #1D4ED8;' : 'background: #F1F5F9; color: #475569;';
          return `<div>
                    <div style="font-weight: 700; color: #0F172A; font-size: 12.5px;">${d.classe}</div>
                    <span style="${regBadge} font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">${d.regime}</span>
                  </div>`;
        }
      },
      { 
        data: null,
        render: function(d) {
          return `<div>
                    <div style="font-weight: 800; color: #DC2626; font-size: 12px;">${d.echeance_libelle}</div>
                    <div style="font-size: 11px; color: #64748B;">Limite : ${d.echeance_date}</div>
                  </div>`;
        }
      },
      { 
        data: null, className: 'text-center',
        render: function(d) {
          let bStyle = 'background: #FEF3C7; color: #D97706; border: 1px solid #FCD34D;';
          if (d.severite_code === 'critique') {
            bStyle = 'background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5;';
          } else if (d.severite_code === 'modere') {
            bStyle = 'background: #FFEDD5; color: #EA580C; border: 1px solid #FDBA74;';
          }
          return `<span style="${bStyle} font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                    +${d.retard_jours} j (${d.severite_libelle})
                  </span>`;
        }
      },
      { 
        data: null, className: 'text-end font-monospace',
        render: function(d) {
          return `<div>
                    <div style="font-size: 12.5px; font-weight: 700; color: #16A34A;">${formatFCFA(d.total_paye)}</div>
                    <div style="font-size: 11px; color: #64748B;">Attendu: ${formatFCFA(d.total_attendu)}</div>
                  </div>`;
        }
      },
      { 
        data: 'solde_restant', className: 'text-end font-monospace',
        render: function(v) {
          return `<strong style="font-size: 13px; color: #DC2626; background: #FEF2F2; padding: 4px 8px; border-radius: 6px; border: 1px solid #FCA5A5;">${formatFCFA(v)}</strong>`;
        }
      },
      { 
        data: null, orderable: false, className: 'text-center',
        render: function(d) {
          let btnPrint = `<a href="<?= RACINE ?>impayes/imprimerRappelPdf/${d.encrypted_id}" target="_blank" 
                            class="btn btn-sm" style="width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #1E3A5F; color: #FFF; border: none; display: inline-flex; align-items: center; justify-content: center;" title="Imprimer la Lettre de Rappel PDF">
                            <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
                          </a>`;
          
          let btnRelancer = `<button type="button" class="btn btn-sm btn-relancer-impaye" 
                              data-etudiant-code="${d.code_etudiant}"
                              data-inscription-code="${d.code_inscription}"
                              data-student-name="${d.nom_complet.replace(/"/g, '&quot;')}"
                              data-classe="${d.classe.replace(/"/g, '&quot;')}"
                              data-phone="${d.telephone_parent.replace(/"/g, '&quot;')}"
                              data-montant-echu="${d.montant_echu}"
                              style="width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #EA580C; color: #FFF; border: none; display: inline-flex; align-items: center; justify-content: center;" title="Émettre une Relance (SMS / WhatsApp / Email)">
                              <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                            </button>`;

          let btnPaiement = `<a href="<?= RACINE ?>paiement/comptabilite_etudiants?annee_code=${d.annee_code}&classe_code=${d.code_classe}" 
                              class="btn btn-sm" style="width: 32px; height: 32px; padding: 0; border-radius: 8px; background: #16A34A; color: #FFF; border: none; display: inline-flex; align-items: center; justify-content: center;" title="Encaisser en Caisse">
                              <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i>
                            </a>`;

          return `<div style="display: flex; gap: 4px; justify-content: center;">
                    ${btnPrint}
                    ${btnRelancer}
                    ${btnPaiement}
                  </div>`;
        }
      }
    ],
    language: {
      url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json'
    },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('click', '.btn-relancer-impaye', function() {
    var etudiantCode = $(this).data('etudiant-code');
    var inscriptionCode = $(this).data('inscription-code');
    var studentName = $(this).data('student-name');
    var classe = $(this).data('classe');
    var phone = $(this).data('phone');
    var montantEchu = $(this).data('montant-echu');

    $('#relance_etudiant_code').val(etudiantCode);
    $('#relance_inscription_code').val(inscriptionCode);
    $('#relance_student_name').text(studentName);
    $('#relance_student_meta').text('Classe : ' + classe);
    $('#relance_telephone_destinataire').val(phone || '');
    $('#relance_montant_impaye').val(montantEchu || 0);

    updateRelanceTemplate();
    $('#modalRelanceImpaye').css('display', 'flex');
  });

  $(document).on('click', '.btn-close-modal-relance', function() {
    $('#modalRelanceImpaye').hide();
  });

  $('#modalRelanceImpaye').on('click', function(e) {
    if ($(e.target).is('#modalRelanceImpaye')) {
      $('#modalRelanceImpaye').hide();
    }
  });

  window.updateRelanceTemplate = function() {
    var studentName = $('#relance_student_name').text();
    var montant = formatFCFA($('#relance_montant_impaye').val());
    var type = $('#relance_niveau_relance').val();

    var msg = "";
    if (type === 'rappel_amiable') {
      msg = "GROUPE EICG - Rappel Amiable : Cher parent, nous vous rappelons que la tranche de scolarité de " + studentName + " s'élevant à " + montant + " est arrivée à échéance. Merci de régulariser à la caisse de l'établissement.";
    } else if (type === 'relance_ferme') {
      msg = "GROUPE EICG - Relance de Scolarité : Cher parent, sauf erreur de notre part, le montant de " + montant + " concernant l'élève " + studentName + " demeure impayé. Veuillez régulariser sous 48h afin d'éviter l'interruption des cours.";
    } else {
      msg = "GROUPE EICG - MISE EN DEMEURE : M./Mme le Tuteur de " + studentName + ", un reliquat impayé de " + montant + " est échu. Nous vous prions de vous présenter d'urgence à la caisse de l'école.";
    }
    $('#relance_message_relance').val(msg);
  };

  $('#formRelanceImpaye').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
      url: '<?= RACINE ?>impayes/add',
      type: 'POST',
      data: formData,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Relance enregistrée avec succès!');
          $('#modalRelanceImpaye').hide();
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau lors de l\'envoi');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
