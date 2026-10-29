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
          <button type="button" class="btn btn-primary btn-add-acc-type" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Type de Kit
          </button>
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
        <button type="button" class="btn-switch-filter active" data-filter="catalogue" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #1E3A5F; background: #1E3A5F; color: #FFFFFF; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="settings" style="width: 16px; height: 16px;"></i> Catalogue & Types de Kits
        </button>

        <button type="button" class="btn-switch-filter" data-filter="all" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="layers" style="width: 16px; height: 16px;"></i> Tous les Kits Souscrits
        </button>

        <button type="button" class="btn-switch-filter" data-filter="en_attente" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #EA580C; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="clock" style="width: 16px; height: 16px;"></i> Étudiants en Attente de Kit
        </button>

        <button type="button" class="btn-switch-filter" data-filter="retire" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; padding: 9px 18px; border-radius: 10px; border: 1.5px solid #CBD5E1; background: #FFFFFF; color: #15803D; cursor: pointer; transition: all 0.2s;">
          <i data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i> Étudiants ayant Retiré leur Kit
        </button>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 1 : TABLEAU DU CATALOGUE DES ACCESSOIRES (PAR DÉFAUT) -->
      <!-- ========================================================================= -->
      <div id="section-catalogue" class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Catalogue des Types d'Accessoires & Kits</h3>
            <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Configuration des articles et kits souscriptibles par les étudiants</p>
          </div>
          <button type="button" class="btn btn-sm btn-primary btn-add-acc-type" style="background: #1E3A5F; font-weight: 700; border-radius: 6px; padding: 8px 14px; cursor: pointer;">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter un article
          </button>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-accessoires" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Désignation du Kit / Article</th>
                <th style="padding: 12px;">Filière</th>
                <th style="padding: 12px;">Niveau</th>
                <th style="padding: 12px;" class="text-center">Statut</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 2 : TABLEAU DES DISTRIBUTIONS ÉTUDIANTS -->
      <!-- ========================================================================= -->
      <div id="section-distributions" class="card" style="display: none; background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
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
<!-- MODAL INTERACTIVE : AJOUTER / MODIFIER TYPE D'ACCESSOIRE OU KIT -->
<!-- ========================================================================= -->
<div id="modal-accessoire-type" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-accessoire-type-title" style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Type de Kit / Article
      </h3>
      <button type="button" class="btn-close-modal-acc-type" style="background: transparent; border: none; color: #FFFFFF; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-accessoire-type" style="padding: 22px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_accessoire" id="acc_type_id" value="">

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Désignation du kit / accessoire <span style="color: #EF4444;">*</span>
        </label>
        <input type="text" name="libelle_accessoire" id="acc_type_libelle" required placeholder="Ex: Kit Uniforme, Badge Étudiant, Blouse Blanche..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Type de Filière <span style="color: #EF4444;">*</span>
        </label>
        <select name="type_filiere_cible" id="acc_type_filiere_cible" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="TOUT">Toutes les Filières (Général)</option>
          <option value="INDUSTRIELLE">Filière Industrielle uniquement (Ex: IDA, RIT...)</option>
          <option value="TERTIAIRE">Filière Tertiaire uniquement (Ex: RHCOM, GEC...)</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
          Niveau d'étude
        </label>
        <select name="niveau_code" id="acc_type_niveau_code" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="">Tous les niveaux</option>
          <?php foreach (($niveaux ?? []) as $niv): ?>
            <option value="<?= htmlspecialchars($niv['code_niveau']) ?>"><?= htmlspecialchars($niv['libelle_niveau']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut</label>
        <select name="statut_accessoire" id="acc_type_statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-acc-type" style="font-weight: 700; border-radius: 8px; padding: 9px 18px;">Annuler</button>
        <button type="submit" id="btn-save-acc-type" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer
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

<?php 
$pageScripts = ['public/assets/js/modules/accessoires_list.js'];
require_once __DIR__ . '/../../public/inc/footer-link.php'; 
?>

