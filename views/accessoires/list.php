<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$etudiants = (new ModelEtudiant())->getAll();
$accessoires = (new ModelAccessoire())->getAll();
$stats = $stats ?? (new ModelAccessoire())->getStats();
$annees = $annees ?? [];
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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Accessoires & Kits d'Inscription</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion du registre des kits, suivi des distributions et état des retraits étudiants</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btn-open-attrib-modal" class="btn" style="background: #15803D; border: 1px solid #15803D; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="package-check" style="width: 18px; height: 18px;"></i> Attribuer un Kit
          </button>
          <a href="<?= RACINE ?>accessoire/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Type de Kit
          </a>
        </div>
      </div>

      <!-- Filtre Année Académique (Select2) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
              <span style="font-size: 13px; font-weight: 700; color: #0F172A; display: block;">Année Académique</span>
              <span style="font-size: 11.5px; color: #64748B;">Filtrer les kits et distributions par année</span>
            </div>
          </div>
          <div style="min-width: 260px; flex-grow: 0;">
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les années --</option>
              <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($selectedAnneeCode === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active'])) ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- CARTES KPI / STATISTIQUES DES KITS ET DISTRIBUTIONS -->
      <!-- ========================================================================= -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Kits Souscrits -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="package" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Total Kits Souscrits</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-kits"><?= $stats['total'] ?></div>
          </div>
        </div>

        <!-- En Attente de Retrait -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #FED7AA; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #EA580C; text-transform: uppercase;">En Attente de Retrait</div>
            <div style="font-size: 22px; font-weight: 900; color: #C2410C; margin-top: 2px;" id="kpi-en-attente"><?= $stats['en_attente'] ?></div>
          </div>
        </div>

        <!-- Déjà Retirés / Livrés -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase;">Kits Retirés / Livrés</div>
            <div style="font-size: 22px; font-weight: 900; color: #166534; margin-top: 2px;" id="kpi-retires"><?= $stats['retire'] ?></div>
          </div>
        </div>

        <!-- Taux de Distribution -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="pie-chart" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Taux de Distribution</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-taux"><?= $stats['taux'] ?>%</div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- BOUTONS SWITCH / FILTRES INTERACTIFS -->
      <!-- ========================================================================= -->
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
        <button type="button" class="btn-switch-filter active" data-filter="all" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #1E3A5F; background: #1E3A5F; color: #FFFFFF; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="layers" style="width: 16px; height: 16px;"></i> Tous les Kits Souscrits
        </button>

        <button type="button" class="btn-switch-filter" data-filter="en_attente" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #EA580C; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="clock" style="width: 16px; height: 16px;"></i> Étudiants en Attente de Kit
        </button>

        <button type="button" class="btn-switch-filter" data-filter="retire" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #15803D; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i> Étudiants ayant Retiré leur Kit
        </button>

        <button type="button" class="btn-switch-filter" data-filter="catalogue" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer; transition: all 0.2s; margin-left: auto;">
          <i data-lucide="settings" style="width: 16px; height: 16px;"></i> Catalogue & Types de Kits
        </button>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 1 : TABLEAU DES DISTRIBUTIONS ÉTUDIANTS (PAR DÉFAUT) -->
      <!-- ========================================================================= -->
      <div id="section-distributions" class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;" id="table-dist-title">Suivi des Distributions & Retraits des Kits</h3>
            <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Cliquez sur le switch d'un étudiant pour basculer instantanément son statut de retrait</p>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-distributions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Matricule</th>
                <th style="padding: 12px;">Nom & Prénoms Étudiant</th>
                <th style="padding: 12px;">Téléphone</th>
                <th style="padding: 12px;">Classe</th>
                <th style="padding: 12px; text-align: center;">Statut Global</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 2 : TABLEAU DU CATALOGUE DES ACCESSOIRES (TYPES DE KITS) -->
      <!-- ========================================================================= -->
      <div id="section-catalogue" class="card" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Catalogue des Types d'Accessoires & Kits</h3>
            <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Configuration des articles et kits souscriptibles par les étudiants</p>
          </div>
          <a href="<?= RACINE ?>accessoire/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; font-weight: 700; border-radius: 6px; padding: 8px 14px;">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter un article
          </a>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-accessoires" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Désignation du Kit</th>
                <th style="padding: 12px;">Statut</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
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
<!-- MODAL D'ATTRIBUTION RAPIDE D'UN KIT À UN ÉTUDIANT -->
<!-- ========================================================================= -->
<div id="modal-attribuer-kit" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.25s ease;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="package-plus" style="width: 20px; height: 20px;"></i> Attribuer un Kit à un Étudiant
      </h3>
      <button type="button" class="btn-close-modal" style="background: transparent; border: none; color: #FFFFFF; font-size: 20px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-attribuer-kit" action="<?= RACINE ?>accessoire/attribuerKit" method="POST" style="padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Étudiant Bénéficiaire <span style="color: #EF4444;">*</span></label>
        <select class="form-control select2" name="etudiant_code" id="modal_etudiant_code" style="width: 100%;" required>
          <option value="">-- Rechercher par nom, matricule ou tél --</option>
          <?php foreach($etudiants as $e): ?>
            <option value="<?= $e['code_etudiant'] ?>"><?= htmlspecialchars($e['matricule_etudiant'] . ' - ' . $e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ========================================================================= -->
      <!-- ÉTAT DES LIEUX / PREVIEW EN TEMPS RÉEL DES KITS ÉTUDIANT -->
      <!-- ========================================================================= -->
      <div id="modal_student_kit_preview" style="display: none; margin-bottom: 18px; padding: 14px 16px; border-radius: 10px; background: #F8FAFC; border: 1.5px solid #E2E8F0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="package-search" style="width: 14px; height: 14px;"></i> État des Lieux : Kits Déjà Attribués cette Année
          </span>
          <span id="preview_kit_count_badge" class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 6px;">0 kit</span>
        </div>
        <div id="preview_existing_kits_list" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
          <!-- Badges dynamiques ici -->
        </div>
        <div id="preview_kit_alert_msg" style="display: none; font-size: 12px; margin-top: 10px; padding: 8px 12px; border-radius: 6px;"></div>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Kits / Accessoires à Attribuer (Sélection multiple) <span style="color: #EF4444;">*</span>
        </label>
        <select class="form-control select2" name="accessoires[]" id="modal_accessoires_select" multiple="multiple" style="width: 100%;" required>
          <?php foreach($accessoires as $acc): ?>
            <option value="<?= $acc['code_accessoire'] ?>" data-base-text="<?= htmlspecialchars($acc['libelle_accessoire']) ?>"><?= htmlspecialchars($acc['libelle_accessoire']) ?></option>
          <?php endforeach; ?>
        </select>
        <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Vous pouvez sélectionner un ou plusieurs kits en même temps (les kits déjà possédés sont verrouillés)</small>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">État Initial du Retrait <span style="color: #EF4444;">*</span></label>
        <div style="display: flex; gap: 12px;">
          <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #EA580C; background: #FFF7ED; border: 1.5px solid #FED7AA; padding: 10px 18px; border-radius: 8px; cursor: pointer; flex: 1;">
            <input type="radio" name="etat_retrait" value="en_attente" checked style="accent-color: #EA580C; cursor: pointer;">
            <span style="display: inline-flex; align-items: center; gap: 6px;"><i data-lucide="clock" style="width: 16px; height: 16px;"></i> En attente</span>
          </label>
          <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #15803D; background: #F0FDF4; border: 1.5px solid #BBF7D0; padding: 10px 18px; border-radius: 8px; cursor: pointer; flex: 1;">
            <input type="radio" name="etat_retrait" value="retire" style="accent-color: #15803D; cursor: pointer;">
            <span style="display: inline-flex; align-items: center; gap: 6px;"><i data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i> Déjà retiré</span>
          </label>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</button>
        <button type="submit" id="btn_submit_attribuer" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer l'Attribution
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL DE DÉTAILS ET SUIVI DES KITS D'UN ÉTUDIANT -->
<!-- ========================================================================= -->
<div id="modal-details-kits" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 620px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.25s ease;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="package-search" style="width: 20px; height: 20px;"></i> Détails & Retraits des Kits
      </h3>
      <button type="button" class="btn-close-modal-details" style="background: transparent; border: none; color: #FFFFFF; font-size: 20px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <div style="padding: 24px;">
      <!-- En-tête Étudiant -->
      <div style="background: #F8FAFC; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; border: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
          <h4 id="detail_student_name" style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">-</h4>
          <p id="detail_student_sub" style="font-size: 12.5px; color: #64748B; margin: 3px 0 0 0;">-</p>
        </div>
        <div id="detail_student_global_badge"></div>
      </div>

      <!-- Tableau des Kits -->
      <div style="width: 100%; overflow-x: auto; margin-bottom: 20px;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #F1F5F9; color: #475569; font-size: 12px; text-align: left;">
              <th style="padding: 10px 12px;">Kit / Accessoire</th>
              <th style="padding: 10px 12px; text-align: center;">État du Retrait</th>
              <th style="padding: 10px 12px; text-align: right;">Action Instantanée</th>
            </tr>
          </thead>
          <tbody id="detail_kits_tbody">
            <!-- Injecté dynamiquement par JS -->
          </tbody>
        </table>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 16px;">
        <div id="detail_bulk_action_container"></div>
        <button type="button" class="btn btn-secondary btn-close-modal-details" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
  }

  var currentFilter = 'all';
  var tableDist = null;
  var tableAcc = null;

  var studentRowsMap = {};
  var activeStudentCode = null;

  function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // 1. Initialiser le DataTable des Distributions
  function initDistTable(filter) {
    if ($.fn.DataTable.isDataTable('#table-distributions')) {
      tableDist.destroy();
    }

    studentRowsMap = {};

    tableDist = $('#table-distributions').DataTable({
      ajax: {
        url: '<?= RACINE ?>accessoire/apiDistributions',
        type: 'GET',
        data: function(d) {
          d.filter = filter;
          d.annee_code = $('#filter-annee').val();
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { data: 'matricule_etudiant', render: function(d) {
          return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + (d || '-') + '</code>';
        }},
        { data: 'nom_complet', render: function(d) {
          return '<strong style="color:#0F172A; font-size:13.5px;">' + (d || '-') + '</strong>';
        }},
        { data: 'telephone_etudiant', defaultContent: '-', render: function(d) {
          return '<span style="color:#475569; font-weight:600; font-size:12.5px;">' + (d || '-') + '</span>';
        }},
        { data: 'libelle_classe', render: function(d) {
          return '<span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700; padding:4px 8px; border-radius:6px;">' + (d || 'Non assignée') + '</span>';
        }},
        { data: null, className: 'text-center', render: function(d, type, row) {
          var totKits = parseInt(row.total_kits) || 0;
          var totRetires = parseInt(row.total_retires) || 0;
          if (totKits > 0 && totRetires === totKits) {
            return '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Tout retiré (' + totRetires + '/' + totKits + ')</span>';
          } else if (totRetires > 0) {
            return '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="pie-chart" style="width:14px;height:14px;"></i> Partiel (' + totRetires + '/' + totKits + ')</span>';
          } else {
            return '<span class="badge" style="background:#FFEDD5; color:#C2410C; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En attente (0/' + totKits + ')</span>';
          }
        }},
        { data: null, orderable: false, className: 'text-end', render: function(d, type, row) {
          return '<button type="button" class="btn btn-sm btn-open-student-kits-modal" data-ins="' + row.code_inscription + '" style="background:#1E3A5F; color:#FFFFFF; border:none; border-radius:6px; font-weight:700; padding:6px 14px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
                 '<i data-lucide="sliders" style="width:14px;height:14px;"></i> Détails & Retraits</button>';
        }}
      ],
      language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
      drawCallback: function() {
        var api = this.api();
        studentRowsMap = {};
        api.rows().data().each(function(row) {
          if (row.code_inscription) {
            studentRowsMap[row.code_inscription] = row;
          }
        });

        if (activeStudentCode && studentRowsMap[activeStudentCode] && $('#modal-details-kits').is(':visible')) {
          renderStudentKitsModal(studentRowsMap[activeStudentCode]);
        }

        if (window.lucide) lucide.createIcons();
      }
    });
  }

  // 2. Initialiser le DataTable du Catalogue
  function initCatalogueTable() {
    if (!$.fn.DataTable.isDataTable('#table-accessoires')) {
      tableAcc = $('#table-accessoires').DataTable({
        ajax: {
          url: '<?= RACINE ?>accessoire/apiList',
          type: 'GET',
          data: function(d) {
            d.annee_code = $('#filter-annee').val();
          }
        },
        processing: true,
        autoWidth: false,
        columns: [
          { data: null, width: '50px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
          { data: 'code_accessoire', width: '130px', render: function(d) {
            if (!d) return '-';
            return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
          }},
          { data: 'libelle_accessoire', render: function(d) {
            return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
          }},
          { data: 'statut_accessoire', width: '80px', className: 'text-center', render: function(d, type, row) {
            var isActif = (d === 'actif');
            var checkedAttr = isActif ? 'checked' : '';
            return '<div style="display:flex; justify-content:center; align-items:center;">' +
                   '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
                   '<input type="checkbox" class="toggle-statut-acc" data-id="' + row.id_accessoire + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
                   '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                   '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                   '</span>' +
                   '</label>' +
                   '</div>';
          }},
          { data: null, width: '160px', orderable: false, render: function(d) {
            return '<a href="' + window.RACINE + 'accessoire/edition/' + (d.editId || d.id_accessoire) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
                   '<a href="' + window.RACINE + 'accessoire/details/' + (d.editId || d.id_accessoire) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
          }, className: 'text-end' }
        ],
        language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
        drawCallback: function() { if (window.lucide) lucide.createIcons(); }
      });
    }
  }

  $('#filter-annee').on('change', function() {
    var val = $(this).val();
    window.location.href = window.RACINE + 'accessoire/list?annee_code=' + encodeURIComponent(val);
  });

  // Bascule de statut pour le catalogue d'accessoires via Ajax
  $(document).on('change', '.toggle-statut-acc', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>accessoire/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          if (tableAcc) tableAcc.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  // 3. Rafraîchir les statistiques KPI
  function reloadStats() {
    var anneeCode = $('#filter-annee').val();
    $.getJSON('<?= RACINE ?>accessoire/apiStats?annee_code=' + encodeURIComponent(anneeCode), function(res) {
      if (res.status === 1 && res.stats) {
        $('#kpi-total-kits').text(res.stats.total);
        $('#kpi-en-attente').text(res.stats.en_attente);
        $('#kpi-retires').text(res.stats.retire);
        $('#kpi-taux').text(res.stats.taux + '%');
      }
    });
  }

  // 4. Gestion des boutons switch / filtres
  $('.btn-switch-filter').on('click', function() {
    $('.btn-switch-filter').removeClass('active').css({
      'background': '#FFFFFF',
      'color': '#475569',
      'border-color': '#CBD5E1'
    });

    $(this).addClass('active');
    var filter = $(this).attr('data-filter');
    currentFilter = filter;

    if (filter === 'all') {
      $(this).css({ 'background': '#1E3A5F', 'color': '#FFFFFF', 'border-color': '#1E3A5F' });
      $('#section-distributions').show();
      $('#section-catalogue').hide();
      $('#table-dist-title').text("Suivi de Tous les Kits Souscrits");
      initDistTable('all');
    } else if (filter === 'en_attente') {
      $(this).css({ 'background': '#FFF7ED', 'color': '#EA580C', 'border-color': '#FED7AA' });
      $('#section-distributions').show();
      $('#section-catalogue').hide();
      $('#table-dist-title').text("Kits en Attente de Retrait par les Étudiants");
      initDistTable('en_attente');
    } else if (filter === 'retire') {
      $(this).css({ 'background': '#F0FDF4', 'color': '#15803D', 'border-color': '#BBF7D0' });
      $('#section-distributions').show();
      $('#section-catalogue').hide();
      $('#table-dist-title').text("Kits Déjà Retirés & Livrés aux Étudiants");
      initDistTable('retire');
    } else if (filter === 'catalogue') {
      $(this).css({ 'background': '#F8FAFC', 'color': '#0F172A', 'border-color': '#94A3B8' });
      $('#section-distributions').hide();
      $('#section-catalogue').show();
      initCatalogueTable();
    }
  });

  // 5. Modal de Détails des Kits Étudiant
  $(document).on('click', '.btn-open-student-kits-modal', function() {
    var insCode = $(this).attr('data-ins');
    var row = studentRowsMap[insCode];
    if (!row) return;
    activeStudentCode = insCode;
    renderStudentKitsModal(row);
    $('#modal-details-kits').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-details').on('click', function() {
    $('#modal-details-kits').css('display', 'none');
    activeStudentCode = null;
  });

  function renderStudentKitsModal(row) {
    $('#detail_student_name').html(escapeHtml(row.nom_complet) + ' <code style="font-weight:800; color:#1E3A5F; background:#EFF6FF; padding:3px 8px; border-radius:6px; font-size:12px;">' + escapeHtml(row.matricule_etudiant) + '</code>');
    $('#detail_student_sub').text('Classe : ' + (row.libelle_classe || 'Non assignée') + ' | Tél : ' + (row.telephone_etudiant || '-'));

    var totKits = parseInt(row.total_kits) || 0;
    var totRetires = parseInt(row.total_retires) || 0;
    var totEnAttente = parseInt(row.total_en_attente) || 0;

    var globalBadge = '';
    if (totKits > 0 && totRetires === totKits) {
      globalBadge = '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Tout retiré (' + totRetires + '/' + totKits + ')</span>';
    } else if (totRetires > 0) {
      globalBadge = '<span class="badge" style="background:#FEF3C7; color:#B45309; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="pie-chart" style="width:14px;height:14px;"></i> Partiel (' + totRetires + '/' + totKits + ')</span>';
    } else {
      globalBadge = '<span class="badge" style="background:#FFEDD5; color:#C2410C; padding:6px 12px; border-radius:20px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:14px;height:14px;"></i> En attente (0/' + totKits + ')</span>';
    }
    $('#detail_student_global_badge').html(globalBadge);

    var tbodyHtml = '';
    if (row.kits_details) {
      var items = row.kits_details.split('|||');
      items.forEach(function(item) {
        var parts = item.split(':::');
        var kitId = parts[0];
        var kitName = parts[1] || 'Kit';
        var kitEtat = parts[2] || 'en_attente';
        var kitDate = parts[3] || '';
        var isRetire = (kitEtat === 'retire');

        var etatBadge = isRetire
          ? '<span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:800; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Retiré ' + (kitDate ? 'le ' + kitDate : '') + '</span>'
          : '<span class="badge" style="background:#FFEDD5; color:#C2410C; padding:4px 10px; border-radius:12px; font-weight:800; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:12px;height:12px;"></i> En attente</span>';

        var checked = isRetire ? 'checked' : '';
        var switchHtml = '<label class="switch" style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="Basculer le retrait">' +
                         '<input type="checkbox" class="toggle-retrait-checkbox" data-id="' + kitId + '" ' + checked + ' style="opacity:0; width:0; height:0;">' +
                         '<span class="slider round" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isRetire ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
                         '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isRetire ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
                         '</span>' +
                         '</label>';

        tbodyHtml += '<tr style="border-bottom: 1px solid #F1F5F9;">' +
                     '<td style="padding:12px;"><strong style="color:#1E3A5F; font-size:13px; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="package" style="width:14px;height:14px;"></i> ' + escapeHtml(kitName) + '</strong></td>' +
                     '<td style="padding:12px; text-align:center;">' + etatBadge + '</td>' +
                     '<td style="padding:12px; text-align:right;">' + switchHtml + '</td>' +
                     '</tr>';
      });
    } else {
      tbodyHtml = '<tr><td colspan="3" style="padding:16px; text-align:center; color:#94A3B8;">Aucun kit attribué</td></tr>';
    }
    $('#detail_kits_tbody').html(tbodyHtml);

    var bulkBtn = '';
    if (totEnAttente > 0) {
      bulkBtn = '<button type="button" class="btn btn-sm btn-toggle-all-kits" data-ins="' + row.code_inscription + '" data-target="retire" style="background:#15803D; color:#FFFFFF; border:none; border-radius:8px; font-weight:700; padding:8px 16px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
                '<i data-lucide="check-check" style="width:15px;height:15px;"></i> Tout marquer comme retiré</button>';
    } else {
      bulkBtn = '<button type="button" class="btn btn-sm btn-toggle-all-kits" data-ins="' + row.code_inscription + '" data-target="en_attente" style="background:#FFFFFF; color:#EA580C; border:1px solid #EA580C; border-radius:8px; font-weight:700; padding:8px 16px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">' +
                '<i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i> Remettre tout en attente</button>';
    }
    $('#detail_bulk_action_container').html(bulkBtn);
    if (window.lucide) lucide.createIcons();
  }

  // 6. Bascule dynamique (Switch Toggle Ajax 1-clic)
  $(document).on('click', '.btn-quick-toggle', function() {
    var id = $(this).attr('data-id');
    toggleKitStatus(id);
  });

  $(document).on('change', '.toggle-retrait-checkbox', function() {
    var id = $(this).attr('data-id');
    toggleKitStatus(id);
  });

  $(document).on('click', '.btn-toggle-all-kits', function() {
    var insCode = $(this).attr('data-ins');
    var targetState = $(this).attr('data-target');
    $.ajax({
      url: '<?= RACINE ?>accessoire/toggleStudentAllKits',
      type: 'POST',
      data: {
        inscription_code: insCode,
        target_etat: targetState,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' || res.status === 1) {
          showToast(res.message || 'Statut des kits de l\'étudiant mis à jour !', 'success', 'Distribution Kits');
          if (tableDist) tableDist.ajax.reload(null, false);
          reloadStats();
        } else {
          showToast(res.message || 'Erreur lors de la mise à jour', 'danger', 'Erreur');
        }
      },
      error: function() {
        showToast('Une erreur réseau est survenue', 'danger', 'Erreur');
      }
    });
  });

  function toggleKitStatus(id) {
    $.ajax({
      url: '<?= RACINE ?>accessoire/toggleRetrait',
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' || res.status === 1) {
          showToast(res.message || 'Statut mis à jour !', 'success', 'Distribution Kit');
          tableDist.ajax.reload(null, false);
          reloadStats();
        } else {
          showToast(res.message || 'Erreur de mise à jour', 'danger', 'Erreur');
          tableDist.ajax.reload(null, false);
        }
      },
      error: function(err) {
        showToast('Une erreur est survenue lors de la mise à jour', 'danger', 'Erreur');
        tableDist.ajax.reload(null, false);
      }
    });
  }

  // 6. Gestion du Modal d'Attribution Rapide
  $('#btn-open-attrib-modal').on('click', function() {
    $('#modal-attribuer-kit').css('display', 'flex');
    if ($.fn.select2) {
      $('#modal_etudiant_code').select2({
        dropdownParent: $('#modal-attribuer-kit'),
        placeholder: "-- Rechercher par nom, matricule ou tél --",
        allowClear: true,
        width: '100%'
      });
      $('#modal_accessoires_select').select2({
        dropdownParent: $('#modal-attribuer-kit'),
        placeholder: "-- Choisir un ou plusieurs kits --",
        allowClear: true,
        width: '100%'
      });
    }
    if (window.lucide) lucide.createIcons();
  });

  // État des lieux / Preview en temps réel des kits lors de la sélection de l'étudiant
  $('#modal_etudiant_code').on('change', function() {
    var etuCode = $(this).val();
    if (!etuCode) {
      $('#modal_student_kit_preview').slideUp(150);
      resetKitSelectOptions([]);
      return;
    }

    $.getJSON('<?= RACINE ?>accessoire/getStudentKits?etudiant_code=' + encodeURIComponent(etuCode), function(res) {
      if (res.status === 1) {
        $('#modal_student_kit_preview').slideDown(200);
        var existingKits = res.existing_kits || [];
        var existingCodes = res.existing_codes || [];
        var count = existingKits.length;

        $('#preview_kit_count_badge').text(count + ' kit' + (count > 1 ? 's' : ''));

        if (count === 0) {
          $('#preview_existing_kits_list').html('<span style="font-size:12.5px; color:#64748B; font-style:italic;">Aucun kit n\'a encore été attribué à cet étudiant pour l\'année scolaire active.</span>');
          $('#preview_kit_alert_msg').hide();
          $('#btn_submit_attribuer').prop('disabled', false).css({ 'opacity': '1', 'cursor': 'pointer' });
        } else {
          var badgesHtml = '';
          existingKits.forEach(function(k) {
            var isRetire = (k.etat_retrait === 'retire');
            var bg = isRetire ? '#DCFCE7' : '#FFEDD5';
            var col = isRetire ? '#15803D' : '#C2410C';
            var iconName = isRetire ? 'check-circle' : 'clock';
            var statusLabel = isRetire 
              ? ('Retiré' + (k.date_retrait_formatee ? ' le ' + k.date_retrait_formatee : '')) 
              : 'En attente de retrait';

            badgesHtml += '<div style="background:' + bg + '; color:' + col + '; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">' +
                          '<i data-lucide="' + iconName + '" style="width:13px;height:13px;"></i> ' +
                          '<span>' + k.libelle_accessoire + '</span>' +
                          '<span style="font-size:11px; opacity:0.85; font-weight:600;">(' + statusLabel + ')</span>' +
                          '</div>';
          });
          $('#preview_existing_kits_list').html(badgesHtml);

          if (res.has_all) {
            $('#preview_kit_alert_msg').show()
              .css({ 'background': '#FEF2F2', 'color': '#991B1B', 'border': '1px solid #FECACA' })
              .html('<strong>Information :</strong> Cet étudiant a déjà reçu <u>la totalité des kits disponibles</u> pour cette année scolaire. Aucun kit supplémentaire ne peut être attribué.');
            $('#btn_submit_attribuer').prop('disabled', true).css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
          } else {
            $('#preview_kit_alert_msg').show()
              .css({ 'background': '#FFFBEB', 'color': '#B45309', 'border': '1px solid #FDE68A' })
              .html('<strong>Règle d\'unicité :</strong> Les kits déjà attribués ci-dessus sont verrouillés pour empêcher les doublons pour la même année.');
            $('#btn_submit_attribuer').prop('disabled', false).css({ 'opacity': '1', 'cursor': 'pointer' });
          }
        }

        // Mettre à jour les options du select multiple (désactiver les doublons)
        resetKitSelectOptions(existingCodes);
        if (window.lucide) lucide.createIcons();
      }
    });
  });

  function resetKitSelectOptions(disabledCodes) {
    $('#modal_accessoires_select option').each(function() {
      var val = $(this).val();
      var baseText = $(this).attr('data-base-text') || $(this).text().replace(' (Déjà attribué cette année)', '');
      $(this).attr('data-base-text', baseText);

      if (disabledCodes.indexOf(val) !== -1) {
        $(this).prop('disabled', true).text(baseText + ' (Déjà attribué cette année)');
      } else {
        $(this).prop('disabled', false).text(baseText);
      }
    });

    // Nettoyer les valeurs sélectionnées qui sont maintenant désactivées
    var currentVals = $('#modal_accessoires_select').val() || [];
    var validVals = currentVals.filter(function(v) { return disabledCodes.indexOf(v) === -1; });
    $('#modal_accessoires_select').val(validVals);

    if ($.fn.select2) {
      $('#modal_accessoires_select').select2({
        dropdownParent: $('#modal-attribuer-kit'),
        placeholder: "-- Choisir un ou plusieurs kits --",
        allowClear: true,
        width: '100%'
      });
    }
  }

  $('.btn-close-modal').on('click', function() {
    $('#modal-attribuer-kit').hide();
    $('#modal_student_kit_preview').hide();
    if ($.fn.select2) {
      $('#modal_etudiant_code').val(null).trigger('change.select2');
      $('#modal_accessoires_select').val(null).trigger('change.select2');
    }
    resetKitSelectOptions([]);
  });

  $('#form-attribuer-kit').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success' || res.status === 1) {
          showToast(res.message || 'Kit(s) attribué(s) avec succès !', 'success', 'Attribution Kit');
          $('#modal-attribuer-kit').hide();
          $('#modal_student_kit_preview').hide();
          form[0].reset();
          if ($.fn.select2) {
            $('#modal_etudiant_code').val(null).trigger('change.select2');
            $('#modal_accessoires_select').val(null).trigger('change.select2');
          }
          resetKitSelectOptions([]);
          if (tableDist) tableDist.ajax.reload(null, false);
          reloadStats();
        } else {
          showToast(res.message || 'Erreur lors de l\'attribution', 'danger', 'Erreur');
        }
      },
      error: function() {
        showToast('Erreur de communication avec le serveur', 'danger', 'Erreur');
      }
    });
  });

  // Initialisation au chargement
  initDistTable('all');
});
</script>

<style>
/* Style Slider Switch */
.switch input:checked + .slider {
  background-color: #15803D !important;
}
.switch input:focus + .slider {
  box-shadow: 0 0 1px #15803D;
}
.switch input:checked + .slider:before {
  transform: translateX(18px);
}
.slider:before {
  position: absolute;
  content: "";
  height: 14px;
  width: 14px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>

