<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$classes = (new ModelClasse())->getAll();
$accessoires = (new ModelAccessoire())->getAll();
$pieces = (new ModelPieceFournir())->getAll();
?>
<style>
  .wizard-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    background: #FFFFFF;
    border-radius: 12px;
    padding: 16px 24px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow-x: auto;
  }
  .wizard-step-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 13px;
    color: #94A3B8;
    position: relative;
    white-space: nowrap;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.25s ease;
    cursor: pointer;
  }
  .wizard-step-item.active {
    color: #1E3A5F;
    background: #EFF6FF;
  }
  .wizard-step-item.completed {
    color: #15803D;
  }
  .wizard-step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #F1F5F9;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
    transition: all 0.25s ease;
  }
  .wizard-step-item.active .wizard-step-number {
    background: #1E3A5F;
    color: #FFFFFF;
  }
  .wizard-step-item.completed .wizard-step-number {
    background: #DCFCE7;
    color: #15803D;
  }
  .wizard-step-divider {
    flex: 1;
    height: 2px;
    background: #E2E8F0;
    margin: 0 12px;
    min-width: 20px;
  }
  .wizard-card {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 28px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  .wizard-step-content {
    display: none;
  }
  .wizard-step-content.active {
    display: block;
  }
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Nouveau Dossier d'Inscription Étudiant</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Assistant étape par étape pour l'enregistrement complet d'un étudiant</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <button type="button" id="btn-reset-draft" class="btn" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 10px 16px; border: 1px solid #FCA5A5; color: #DC2626; background: #FEF2F2; cursor: pointer;">
            <i data-lucide="rotate-ccw" style="width: 16px; height: 16px;"></i> Effacer le brouillon
          </button>
          <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
        </div>
      </div>

      <!-- Stepper Navigation -->
      <div class="wizard-stepper">
        <div class="wizard-step-item active" data-step="1">
          <div class="wizard-step-number">1</div>
          <i data-lucide="user" style="width: 16px; height: 16px;"></i> <span>Étudiant</span>
        </div>
        <div class="wizard-step-divider"></div>
        <div class="wizard-step-item" data-step="2">
          <div class="wizard-step-number">2</div>
          <i data-lucide="users" style="width: 16px; height: 16px;"></i> <span>Parents</span>
        </div>
        <div class="wizard-step-divider"></div>
        <div class="wizard-step-item" data-step="3">
          <div class="wizard-step-number">3</div>
          <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i> <span>Inscription</span>
        </div>
        <div class="wizard-step-divider"></div>
        <div class="wizard-step-item" data-step="4">
          <div class="wizard-step-number">4</div>
          <i data-lucide="package" style="width: 16px; height: 16px;"></i> <span>Accessoires & Dossier</span>
        </div>
        <div class="wizard-step-divider"></div>
        <div class="wizard-step-item" data-step="5">
          <div class="wizard-step-number">5</div>
          <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> <span>Récapitulatif</span>
        </div>
      </div>

      <!-- Form Wizard -->
      <div class="wizard-card">
        <form id="form-wizard-etudiant" action="<?= RACINE ?>etudiant/addWizard" method="POST">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <!-- ÉTAPE 1 : IDENTITÉ ÉTUDIANT -->
          <div class="wizard-step-content active" data-step="1">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="user" style="width: 20px; height: 20px;"></i> Étape 1 : Identité & Coordonnées de l'Étudiant
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule École (Auto-généré)</label>
                <input type="text" class="form-control" name="matricule_etudiant" id="wiz_matricule" placeholder="Auto-généré : XX-123/GEB/AA26" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                <small style="color: #64748B; font-size: 11px; margin-top: 3px; display: block;">Format : Initiales - Ordre / GEB / Filière + Année (laissé vide pour génération auto)</small>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule MENET-FP</label>
                <input type="text" class="form-control" name="matricule_menet" placeholder="Ex: 18094523A (Éducation Nationale)" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-family: monospace; font-weight: 600;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule MESRS</label>
                <input type="text" class="form-control" name="matricule_mesrs" placeholder="Ex: MESRS-2026-00412 (Enseignement Supérieur)" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-family: monospace; font-weight: 600;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de famille <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control" id="wiz_nom" name="nom_etudiant" placeholder="Ex: KOUASSI" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Prénoms <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control" id="wiz_prenom" name="prenom_etudiant" placeholder="Ex: Jean-Marc Emmanuel" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Sexe <span style="color: #EF4444;">*</span></label>
                <select class="form-control" name="sexe_etudiant" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
                  <option value="M">Masculin (M)</option>
                  <option value="F">Féminin (F)</option>
                </select>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de naissance</label>
                <input type="date" class="form-control" name="date_naissance_etudiant" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Lieu de naissance</label>
                <input type="text" class="form-control" name="lieu_naissance_etudiant" placeholder="Ex: Abidjan Treichville" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nationalité</label>
                <input type="text" class="form-control" name="nationalite_etudiant" value="Ivoirienne" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone étudiant <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control" id="wiz_tel" name="telephone_etudiant" placeholder="Ex: 0708091011" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Email étudiant</label>
                <input type="email" class="form-control" name="email_etudiant" placeholder="Ex: jean.kouassi@etudiant.geicg.ci" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse de résidence</label>
                <textarea class="form-control" name="lieu_residence_etudiant" rows="2" placeholder="Ex: Cocody Riviera 3, Abidjan" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
              </div>
            </div>
          </div>

          <!-- ÉTAPE 2 : PARENTS ET TUTEURS -->
          <div class="wizard-step-content" data-step="2">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="users" style="width: 20px; height: 20px;"></i> Étape 2 : Parents & Tuteurs Légaux
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom & Prénom du Père</label>
                <input type="text" class="form-control" name="nom_pere" placeholder="Ex: KOUASSI Paul" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone du Père</label>
                <input type="text" class="form-control" name="telephone_pere" placeholder="Ex: 0102030405" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Profession du Père</label>
                <input type="text" class="form-control" name="profession_pere" placeholder="Ex: Ingénieur BTP" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom & Prénom de la Mère</label>
                <input type="text" class="form-control" name="nom_mere" placeholder="Ex: YAO Marie" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone de la Mère</label>
                <input type="text" class="form-control" name="telephone_mere" placeholder="Ex: 0506070809" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Profession de la Mère</label>
                <input type="text" class="form-control" name="profession_mere" placeholder="Ex: Commerçante" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Tuteur Légal</label>
                <input type="text" class="form-control" name="nom_tuteur" placeholder="Ex: KOUAME Bernard" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone du Tuteur Légal</label>
                <input type="text" class="form-control" name="telephone_tuteur" placeholder="Ex: 0707070707" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Profession du Tuteur Légal</label>
                <input type="text" class="form-control" name="profession_tuteur" placeholder="Ex: Cadre d'Administration" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
            </div>
          </div>

          <!-- ÉTAPE 3 : INSCRIPTION ACADÉMIQUE -->
          <div class="wizard-step-content" data-step="3">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="graduation-cap" style="width: 20px; height: 20px;"></i> Étape 3 : Inscription Académique & Scolarité
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
              
              <!-- RÉGIME / STATUT D'AFFECTATION -->
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Statut d'Affectation État / Régime de l'Étudiant <span style="color: #EF4444;">*</span>
                </label>
                <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                  <label class="label-affectation-choice" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1.5px solid #1E3A5F; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #1E3A5F; background: #EFF6FF; transition: all 0.2s;">
                    <input type="radio" name="affectation_etat" value="non_affecte" checked style="accent-color: #1E3A5F; width: 16px; height: 16px;">
                    <span>Non Affecté (Privé)</span>
                  </label>
                  <label class="label-affectation-choice" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1.5px solid #CBD5E1; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #334155; background: #FFFFFF; transition: all 0.2s;">
                    <input type="radio" name="affectation_etat" value="affecte" style="accent-color: #1E3A5F; width: 16px; height: 16px;">
                    <span>Affecté (Subventionné par l'État)</span>
                  </label>
                </div>
                <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Le tarif et le nombre d'échéances s'adaptent automatiquement selon le régime choisi.</small>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe d'affectation <span style="color: #EF4444;">*</span></label>
                <select class="form-control select2" id="wiz_classe" name="classe_code" style="width: 100%;" required>
                  <option value="">-- Rechercher / Sélectionner la classe d'affectation --</option>
                  <?php foreach($classes as $cl): ?>
                    <option value="<?= $cl['code_classe'] ?>"><?= htmlspecialchars($cl['libelle_classe']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Montant auto-récupéré -->
              <input type="hidden" id="wiz_montant_scolarite" name="montant_scolarite_inscription" value="0">

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
                <input type="number" class="form-control" id="wiz_remise" name="remise_accordee" placeholder="Ex: 50000" value="0" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
            </div>

            <!-- FICHE TARIFAIRE AUTOMATIQUE & ÉCHÉANCIER COMPLET DES TRANCHES -->
            <div id="wiz-class-tuition-box" style="display: none; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 22px 24px; margin-top: 22px; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.06); transition: all 0.3s ease;">
              
              <!-- 1. En-tête avec résumé de la classe et total scolarité -->
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 16px; margin-bottom: 18px;">
                <div>
                  <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="graduation-cap" style="width: 16px; height: 16px; color: #D97706;"></i> Grille Tarifaire & Échéancier de Scolarité
                  </div>
                  <div style="font-size: 17px; font-weight: 900; color: #0F172A; margin-top: 4px;" id="wiz_summary_classe_title">-</div>
                  <div style="font-size: 13px; color: #64748B; margin-top: 2px;" id="wiz_summary_filiere_niveau">-</div>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                  <div style="background: #EFF6FF; border: 1.5px solid #BFDBFE; padding: 10px 18px; border-radius: 10px; text-align: right;">
                    <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase;">Scolarité Annuelle</div>
                    <div style="font-size: 18px; font-weight: 900; color: #1E3A5F; margin-top: 2px;" id="wiz_summary_total_scolarite">0 FCFA</div>
                  </div>
                  <div id="wiz_summary_net_box" style="display: none; background: #F0FDF4; border: 1.5px solid #86EFAC; padding: 10px 18px; border-radius: 10px; text-align: right;">
                    <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase;">Net à Payer (Après Remise)</div>
                    <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 2px;" id="wiz_summary_net_scolarite">0 FCFA</div>
                  </div>
                </div>
              </div>

              <!-- 2. Titre de la section des tranches -->
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="calendar" style="width: 15px; height: 15px; color: #2563EB;"></i> Échéancier de Toutes les Tranches de Paiement
                </span>
                <span id="wiz_tranches_count_badge" style="background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                  0 tranche(s)
                </span>
              </div>

              <!-- 3. Tableau détaillé et visuel de TOUTES les tranches -->
              <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 8px; overflow: hidden; border: 1px solid #E2E8F0;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                      <th style="padding: 12px 16px; border-bottom: 2px solid #E2E8F0; width: 60px;">N°</th>
                      <th style="padding: 12px 16px; border-bottom: 2px solid #E2E8F0;">Intitulé de l'Échéance / Tranche</th>
                      <th style="padding: 12px 16px; border-bottom: 2px solid #E2E8F0; text-align: center; width: 140px;">Part</th>
                      <th style="padding: 12px 16px; border-bottom: 2px solid #E2E8F0; text-align: center; width: 180px;">Date Limite d'Exigibilité</th>
                      <th style="padding: 12px 16px; border-bottom: 2px solid #E2E8F0; text-align: right; width: 180px;">Montant Tranche</th>
                    </tr>
                  </thead>
                  <tbody id="wiz_tranches_table_body">
                    <!-- Rempli dynamiquement en JS avec toutes les tranches -->
                  </tbody>
                  <tfoot>
                    <tr style="background: #F8FAFC; font-weight: 800; font-size: 13px; color: #0F172A; border-top: 2px solid #CBD5E1;">
                      <td colspan="4" style="padding: 12px 16px; text-align: right; text-transform: uppercase;">Total Général Échéancier :</td>
                      <td style="padding: 12px 16px; text-align: right; color: #1E3A5F; font-size: 15px; font-weight: 900;" id="wiz_tranches_total_sum">0 FCFA</td>
                    </tr>
                  </tfoot>
                </table>
              </div>

            </div>
          </div>

          <!-- ÉTAPE 4 : ACCESSOIRES & DOSSIER ÉTUDIANT (PIÈCES FOURNIES) -->
          <div class="wizard-step-content" data-step="4">
            
            <!-- SECTION A : ACCESSOIRES & KITS -->
            <div style="margin-bottom: 30px;">
              <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin-bottom: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="package" style="width: 18px; height: 18px;"></i> Accessoires & Kits d'Inscription
              </h3>
              <p style="font-size: 13px; color: #64748B; margin-bottom: 14px;">Sélectionnez les kits et accessoires souscrits lors de cette inscription :</p>
              <?php if (!empty($accessoires)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                  <?php foreach($accessoires as $acc): ?>
                    <label class="acc-checkbox-card" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 1.5px solid #CBD5E1; border-radius: 10px; background: #FFFFFF; cursor: pointer; transition: all 0.2s;">
                      <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="accessoires[]" class="chk-accessoire" data-label="<?= htmlspecialchars($acc['libelle_accessoire']) ?>" data-prix="<?= (float)$acc['prix_accessoire'] ?>" value="<?= htmlspecialchars($acc['code_accessoire']) ?>" style="width: 18px; height: 18px; accent-color: #1E3A5F; cursor: pointer;">
                        <span class="acc-label" style="font-weight: 700; color: #0F172A; font-size: 13px;"><?= htmlspecialchars($acc['libelle_accessoire']) ?></span>
                      </div>
                      <span class="acc-price" style="font-weight: 800; color: #1E3A5F; font-size: 13px;"><?= number_format((float)$acc['prix_accessoire'], 0, ',', ' ') ?> FCFA</span>
                    </label>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="padding: 14px; background: #F8FAFC; border-radius: 8px; color: #64748B; font-size: 12.5px; font-weight: 600;">
                  Aucun kit ou accessoire configuré.
                </div>
              <?php endif; ?>
            </div>

            <!-- SECTION B : DOSSIER DE L'ÉTUDIANT & PIÈCES PHYSIQUES FOURNIES -->
            <div style="background: #FAFAFA; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                <div>
                  <h3 style="font-size: 15px; font-weight: 800; color: #15803D; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="folder-check" style="width: 18px; height: 18px;"></i> Dossier de l'Étudiant & Pièces Physiques Fournies
                  </h3>
                  <p style="font-size: 12px; color: #64748B; margin: 3px 0 0 0;">Cochez les pièces déposées pour mettre à jour automatiquement le dossier d'inscription</p>
                </div>
                <div style="display: flex; gap: 8px;">
                  <button type="button" id="btn-check-all-pieces" style="background: #DCFCE7; border: 1px solid #86EFAC; color: #15803D; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer;">
                    Tout cocher (Dossier Complet)
                  </button>
                  <button type="button" id="btn-uncheck-all-pieces" style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer;">
                    Tout décocher
                  </button>
                </div>
              </div>

              <?php if (!empty($pieces)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 12px;">
                  <?php foreach($pieces as $p): ?>
                    <label class="piece-checkbox-card" style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; border: 1.5px solid #E2E8F0; border-radius: 8px; background: #FFFFFF; cursor: pointer; transition: all 0.2s;">
                      <input type="checkbox" name="pieces_fournies[]" class="chk-piece" value="<?= htmlspecialchars($p['code_piece_fournir']) ?>" style="width: 18px; height: 18px; accent-color: #15803D; margin-top: 2px; flex-shrink: 0;">
                      <div style="flex: 1;">
                        <div style="font-weight: 700; color: #0F172A; font-size: 12.5px; line-height: 1.35;">
                          <?= htmlspecialchars($p['libelle_piece']) ?>
                        </div>
                        <?php if (!empty($p['description_piece'])): ?>
                          <div style="font-size: 11px; color: #64748B; margin-top: 2px; line-height: 1.3;">
                            <?= htmlspecialchars($p['description_piece']) ?>
                          </div>
                        <?php endif; ?>
                        <div style="margin-top: 5px;">
                          <span class="piece-status-badge" style="background: #F1F5F9; color: #64748B; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                            En attente de dépôt
                          </span>
                        </div>
                      </div>
                    </label>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="padding: 14px; background: #FFFFFF; border-radius: 8px; color: #64748B; font-size: 12.5px; font-weight: 600;">
                  Aucune pièce configurée dans le catalogue des pièces à fournir.
                </div>
              <?php endif; ?>
            </div>

          </div>

          <!-- ÉTAPE 5 : RÉCAPITULATIF & CONFIRMATION DU DOSSIER -->
          <div class="wizard-step-content" data-step="5">
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 22px; border-bottom: 2px solid #E2E8F0; padding-bottom: 12px;">
              <div>
                <h3 style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="clipboard-check" style="width: 22px; height: 22px; color: #1E3A5F;"></i> Synthèse Générale du Dossier d'Inscription
                </h3>
                <p style="font-size: 13px; color: #64748B; margin: 4px 0 0 0;">Veuillez vérifier l'exactitude des renseignements saisis avant la validation définitive de l'inscription.</p>
              </div>
              <span class="badge" style="background: #DCFCE7; color: #166534; border: 1.5px solid #86EFAC; font-size: 12px; font-weight: 800; padding: 6px 14px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="check" style="width: 14px; height: 14px;"></i> Prêt pour Enregistrement
              </span>
            </div>

            <!-- 1. CARTE PROFIL & IDENTITÉ -->
            <div style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 22px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
              
              <!-- En-tête avec Avatar et Noms -->
              <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 16px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                  <div id="recap_avatar" style="width: 48px; height: 48px; border-radius: 50%; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 18px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
                    ET
                  </div>
                  <div>
                    <div style="font-size: 18px; font-weight: 900; color: #0F172A;" id="recap_nom_prenom">-</div>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 3px;">
                      <span id="recap_sexe_badge" class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">-</span>
                      <span id="recap_nationalite_badge" class="badge" style="background: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">-</span>
                    </div>
                  </div>
                </div>

                <!-- Matricules -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                  <div style="background: #F8FAFC; border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 8px; font-size: 12px;">
                    <span style="color: #64748B; font-weight: 600; display: block; font-size: 10.5px; text-transform: uppercase;">Matricule Établissement</span>
                    <strong style="color: #1E3A5F; font-size: 13px;" id="recap_matricule_geb">Généré automatiquement</strong>
                  </div>
                  <div style="background: #F8FAFC; border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 8px; font-size: 12px;">
                    <span style="color: #64748B; font-weight: 600; display: block; font-size: 10.5px; text-transform: uppercase;">Matricule MENET</span>
                    <strong style="color: #334155; font-size: 13px;" id="recap_mat_menet">Non renseigné</strong>
                  </div>
                  <div style="background: #F8FAFC; border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 8px; font-size: 12px;">
                    <span style="color: #64748B; font-weight: 600; display: block; font-size: 10.5px; text-transform: uppercase;">Matricule MESRS</span>
                    <strong style="color: #334155; font-size: 13px;" id="recap_mat_mesrs">Non renseigné</strong>
                  </div>
                </div>
              </div>

              <!-- Grille des Coordonnées et État Civil -->
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; font-size: 13px;">
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Date & Lieu de Naissance</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_naissance_lieu">-</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Téléphone Principal</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_tel">-</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Email Étudiant</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_email">Non renseigné</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Lieu de Résidence</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_residence">Non renseigné</div>
                </div>
              </div>
            </div>

            <!-- 2. SECTION PARENTS & TUTEURS -->
            <div style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 22px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
              <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="users" style="width: 16px; height: 16px;"></i> Parents & Tuteurs Légaux
              </div>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                
                <!-- Bloc Père -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px;">
                  <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 6px;">Père</div>
                  <div style="font-weight: 800; color: #0F172A; font-size: 13.5px;" id="recap_pere_nom">Non renseigné</div>
                  <div style="font-size: 12px; color: #475569; margin-top: 3px;" id="recap_pere_contact">-</div>
                  <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;" id="recap_pere_prof">-</div>
                </div>

                <!-- Bloc Mère -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px;">
                  <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 6px;">Mère</div>
                  <div style="font-weight: 800; color: #0F172A; font-size: 13.5px;" id="recap_mere_nom">Non renseignée</div>
                  <div style="font-size: 12px; color: #475569; margin-top: 3px;" id="recap_mere_contact">-</div>
                  <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;" id="recap_mere_prof">-</div>
                </div>

                <!-- Bloc Tuteur -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px;">
                  <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 6px;">Tuteur / Contact d'Urgence</div>
                  <div style="font-weight: 800; color: #0F172A; font-size: 13.5px;" id="recap_tuteur_nom">Non renseigné</div>
                  <div style="font-size: 12px; color: #475569; margin-top: 3px;" id="recap_tuteur_contact">-</div>
                </div>

              </div>
            </div>

            <!-- 3. SECTION SCOLARITÉ & ÉCHÉANCIER -->
            <div style="background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 12px; padding: 22px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(22,101,52,0.05);">
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 16px;">
                <div>
                  <div style="font-size: 12px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="calculator" style="width: 16px; height: 16px;"></i> Inscription Académique & Scolarité
                  </div>
                  <div style="font-size: 17px; font-weight: 900; color: #0F172A; margin-top: 4px;" id="recap_classe_title">-</div>
                  <div style="font-size: 12.5px; font-weight: 700; color: #166534; margin-top: 2px;" id="recap_regime_text">-</div>
                </div>

                <!-- Synthèse des Chiffres -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                  <div style="background: #FFFFFF; border: 1px solid #86EFAC; padding: 8px 14px; border-radius: 8px; text-align: right;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase;">Tarif Officiel</div>
                    <div style="font-size: 16px; font-weight: 900; color: #0F172A;" id="recap_scolarite_officielle">0 FCFA</div>
                  </div>
                  <div style="background: #FFFFFF; border: 1px solid #86EFAC; padding: 8px 14px; border-radius: 8px; text-align: right;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #15803D; text-transform: uppercase;">Remise Accordée</div>
                    <div style="font-size: 16px; font-weight: 900; color: #15803D;" id="recap_remise_val">0 FCFA</div>
                  </div>
                  <div style="background: #166534; border: 1px solid #14532D; padding: 8px 16px; border-radius: 8px; text-align: right;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #DCFCE7; text-transform: uppercase;">Net Définitif</div>
                    <div style="font-size: 17px; font-weight: 900; color: #FFFFFF;" id="recap_net_scolarite">0 FCFA</div>
                  </div>
                </div>
              </div>

              <!-- Tableau Échéancier Récapitulatif -->
              <div style="background: #FFFFFF; border-radius: 8px; border: 1px solid #BBF7D0; overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; margin: 0; font-size: 12.5px;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #475569; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                      <th style="padding: 8px 12px; border-bottom: 1px solid #E2E8F0; width: 40px;">N°</th>
                      <th style="padding: 8px 12px; border-bottom: 1px solid #E2E8F0;">Intitulé Tranche</th>
                      <th style="padding: 8px 12px; border-bottom: 1px solid #E2E8F0; text-align: center; width: 90px;">Part</th>
                      <th style="padding: 8px 12px; border-bottom: 1px solid #E2E8F0; text-align: center; width: 130px;">Date Limite</th>
                      <th style="padding: 8px 12px; border-bottom: 1px solid #E2E8F0; text-align: right; width: 140px;">Montant</th>
                    </tr>
                  </thead>
                  <tbody id="recap_tranches_table_body">
                    <!-- Tranches clonées dynamiquement -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- 4. SECTION DOSSIER PHYSIQUE & ACCESSOIRES -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; margin-bottom: 20px;">
              
              <!-- Pièces Fournies -->
              <div style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #F1F5F9; padding-bottom: 8px;">
                  <div style="font-size: 12.5px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="folder-check" style="width: 15px; height: 15px;"></i> Pièces du Dossier
                  </div>
                  <span id="recap_dossier_badge" class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">0 / 0</span>
                </div>
                <div id="recap_pieces_list" style="display: flex; flex-direction: column; gap: 6px; font-size: 12.5px;">
                  <!-- Liste des pièces avec statuts -->
                </div>
              </div>

              <!-- Kits & Accessoires -->
              <div style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #F1F5F9; padding-bottom: 8px;">
                  <div style="font-size: 12.5px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="package" style="width: 15px; height: 15px;"></i> Kits & Accessoires Souscrits
                  </div>
                  <span id="recap_acc_badge" class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">0 kit</span>
                </div>
                <div id="recap_accessoires_list" style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 12.5px;">
                  <!-- Badges des accessoires cochés -->
                </div>
              </div>

            </div>

          </div>

          <!-- Wizard Footer Buttons -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
            <button type="button" id="btn-wizard-prev" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 22px; display: none; align-items: center; gap: 8px;">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Précédent
            </button>
            <div></div>
            <button type="button" id="btn-wizard-next" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px;">
              Suivant <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
            </button>
            <button type="submit" id="btn-wizard-submit" class="btn btn-success" style="background: #15803D; border-color: #15803D; font-weight: 800; border-radius: 8px; padding: 10px 28px; display: none; align-items: center; gap: 8px;">
              <i data-lucide="check" style="width: 18px; height: 18px;"></i> Valider & Enregistrer l'Inscription
            </button>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  var currentStep = 1;
  var totalSteps = 5;

  // Persist form inputs to localStorage
  function saveFormData() {
    var formData = {};
    $('#form-wizard-etudiant').find('input, select, textarea').each(function() {
      var name = $(this).attr('name');
      if (!name || name === 'csrf_token') return;
      
      if ($(this).attr('type') === 'checkbox') {
        if (!formData[name]) formData[name] = [];
        if ($(this).is(':checked')) {
          formData[name].push($(this).val());
        }
      } else {
        formData[name] = $(this).val();
      }
    });
    localStorage.setItem('geicg_etudiant_wizard_data', JSON.stringify(formData));
    localStorage.setItem('geicg_etudiant_wizard_step', currentStep);
  }

  // Restore form inputs from localStorage
  function restoreFormData() {
    var savedDataStr = localStorage.getItem('geicg_etudiant_wizard_data');
    if (savedDataStr) {
      try {
        var formData = JSON.parse(savedDataStr);
        $.each(formData, function(name, val) {
          var $field = $('#form-wizard-etudiant').find('[name="' + name + '"]');
          if ($field.length) {
            if ($field.attr('type') === 'checkbox') {
              if (Array.isArray(val)) {
                $field.each(function() {
                  if (val.indexOf($(this).val()) !== -1) {
                    $(this).prop('checked', true);
                    if ($(this).hasClass('chk-piece')) updatePieceCardStyle($(this));
                    if ($(this).hasClass('chk-accessoire')) updateAccCardStyle($(this));
                  }
                });
              }
            } else {
              $field.val(val);
            }
          }
        });
      } catch(e) { console.error('Erreur restauration brouillon', e); }
    }

    var savedStep = localStorage.getItem('geicg_etudiant_wizard_step');
    if (savedStep) {
      var parsedStep = parseInt(savedStep);
      if (parsedStep >= 1 && parsedStep <= totalSteps) {
        currentStep = parsedStep;
      }
    }
  }

  function updateWizardUI() {
    $('.wizard-step-content').removeClass('active').hide();
    $('.wizard-step-content[data-step="' + currentStep + '"]').addClass('active').show();

    $('.wizard-step-item').each(function() {
      var stepNum = parseInt($(this).attr('data-step'));
      $(this).removeClass('active completed');
      if (stepNum === currentStep) {
        $(this).addClass('active');
      } else if (stepNum < currentStep) {
        $(this).addClass('completed');
      }
    });

    if (currentStep === 1) {
      $('#btn-wizard-prev').hide();
    } else {
      $('#btn-wizard-prev').css('display', 'inline-flex');
    }

    if (currentStep === totalSteps) {
      $('#btn-wizard-next').hide();
      $('#btn-wizard-submit').css('display', 'inline-flex');
      fillRecap();
    } else {
      $('#btn-wizard-next').css('display', 'inline-flex');
      $('#btn-wizard-submit').hide();
    }

    saveFormData();
    if (window.lucide) lucide.createIcons();
  }

  function validateStep(step) {
    if (step === 1) {
      var nom = $('#wiz_nom').val().trim();
      var prenom = $('#wiz_prenom').val().trim();
      var tel = $('#wiz_tel').val().trim();
      if (!nom || !prenom || !tel) {
        showToast('Veuillez remplir le nom, les prénoms et le téléphone de l\'étudiant avant de continuer.', 'warning', 'Champs requis');
        return false;
      }
    }
    if (step === 3) {
      var classe = $('#wiz_classe').val();
      if (!classe) {
        showToast('Veuillez sélectionner la classe d\'affectation.', 'warning', 'Champs requis');
        return false;
      }
    }
    return true;
  }

  function getFormVal(selector) {
    try {
      var $el = $(selector);
      if (!$el.length) return '';
      var val = $el.val();
      if (val === null || val === undefined) return '';
      return String(val).trim();
    } catch(e) {
      return '';
    }
  }

  function fillRecap() {
    var nom = getFormVal('#wiz_nom') || getFormVal('[name="nom_etudiant"]');
    var prenom = getFormVal('#wiz_prenom') || getFormVal('[name="prenom_etudiant"]');
    var tel = getFormVal('#wiz_tel') || getFormVal('[name="telephone_etudiant"]');
    var email = getFormVal('[name="email_etudiant"]');
    var residence = getFormVal('[name="lieu_residence_etudiant"]');
    var dateNais = getFormVal('[name="date_naissance_etudiant"]');
    var lieuNais = getFormVal('[name="lieu_naissance_etudiant"]');
    var nat = getFormVal('[name="nationalite_etudiant"]') || 'Ivoirienne';
    var sexeVal = getFormVal('[name="sexe_etudiant"]');
    var sexe = (sexeVal === 'F') ? 'Féminin' : 'Masculin';

    var matMenet = getFormVal('[name="matricule_menet"]');
    var matMesrs = getFormVal('[name="matricule_mesrs"]');

    var fullName = (nom + ' ' + prenom).trim().toUpperCase();
    var initials = ((nom ? nom.charAt(0) : 'E') + (prenom ? prenom.charAt(0) : 'T')).toUpperCase();

    $('#recap_avatar').text(initials || 'ET');
    $('#recap_nom_prenom').text(fullName || 'NOUVEL ÉTUDIANT');
    $('#recap_sexe_badge').text(sexe);
    $('#recap_nationalite_badge').text(nat);

    var naisParts = [];
    if (dateNais) naisParts.push('Né(e) le ' + dateNais);
    if (lieuNais) naisParts.push('à ' + lieuNais);
    $('#recap_naissance_lieu').text(naisParts.length ? naisParts.join(' ') : 'Date non renseignée');
    $('#recap_tel').text(tel || '-');
    $('#recap_email').text(email || 'Non renseigné');
    $('#recap_residence').text(residence || 'Non renseigné');

    $('#recap_mat_menet').text(matMenet || 'Non renseigné');
    $('#recap_mat_mesrs').text(matMesrs || 'Non renseigné');

    // 2. Parents
    var pereNom = getFormVal('[name="nom_pere"]');
    var pereTel = getFormVal('[name="telephone_pere"]');
    var pereProf = getFormVal('[name="profession_pere"]');
    $('#recap_pere_nom').text(pereNom || 'Non renseigné');
    $('#recap_pere_contact').text(pereTel ? 'Tél : ' + pereTel : 'Aucun contact');
    $('#recap_pere_prof').text(pereProf ? 'Profession : ' + pereProf : '-');

    var mereNom = getFormVal('[name="nom_mere"]');
    var mereTel = getFormVal('[name="telephone_mere"]');
    var mereProf = getFormVal('[name="profession_mere"]');
    $('#recap_mere_nom').text(mereNom || 'Non renseignée');
    $('#recap_mere_contact').text(mereTel ? 'Tél : ' + mereTel : 'Aucun contact');
    $('#recap_mere_prof').text(mereProf ? 'Profession : ' + mereProf : '-');

    var tuteurNom = getFormVal('[name="nom_tuteur"]');
    var tuteurTel = getFormVal('[name="telephone_tuteur"]');
    $('#recap_tuteur_nom').text(tuteurNom || 'Non renseigné');
    $('#recap_tuteur_contact').text(tuteurTel ? 'Tél : ' + tuteurTel : 'Aucun contact');

    // 3. Scolarité & Échéancier
    var classeText = $('#wiz_classe option:selected').text();
    var isAffecte = $('input[name="affectation_etat"]:checked').val() === 'affecte';
    var montant = Number(getFormVal('#wiz_montant_scolarite') || getFormVal('[name="montant_scolarite_inscription"]') || 0);
    var remise = Number(getFormVal('#wiz_remise') || getFormVal('[name="remise_scolarite"]') || 0);
    var net = Math.max(0, montant - remise);

    $('#recap_classe_title').text(classeText && classeText.indexOf('Choisir') === -1 ? classeText : 'Classe non sélectionnée');
    $('#recap_regime_text').html(isAffecte ? '<span style="color:#1E3A5F; font-weight:800;">● Régime Affecté (Subventionné par l\'État)</span>' : '<span style="color:#475569; font-weight:800;">● Régime Non Affecté (Privé)</span>');
    $('#recap_scolarite_officielle').text(montant > 0 ? montant.toLocaleString('fr-FR') + ' FCFA' : '0 FCFA');
    $('#recap_remise_val').text(remise > 0 ? remise.toLocaleString('fr-FR') + ' FCFA' : '0 FCFA');
    $('#recap_net_scolarite').text(net.toLocaleString('fr-FR') + ' FCFA');

    // Cloner ou regénérer les tranches dans le récapitulatif
    var tranchesRows = $('#wiz_tranches_table_body').html();
    if (tranchesRows && tranchesRows.trim() !== '') {
      $('#recap_tranches_table_body').html(tranchesRows);
    } else {
      $('#recap_tranches_table_body').html('<tr><td colspan="5" style="padding:10px; text-align:center; color:#64748B;">Règlement standard unique de ' + net.toLocaleString('fr-FR') + ' FCFA</td></tr>');
    }

    // 4. Pièces du Dossier
    var totalPieces = $('.chk-piece').length;
    var checkedPieces = $('.chk-piece:checked').length;
    $('#recap_dossier_badge').text(checkedPieces + ' / ' + totalPieces + ' pièce(s) fournie(s)');
    if (checkedPieces === totalPieces && totalPieces > 0) {
      $('#recap_dossier_badge').css({ 'background': '#DCFCE7', 'color': '#166534', 'border-color': '#86EFAC' });
    } else {
      $('#recap_dossier_badge').css({ 'background': '#EFF6FF', 'color': '#1E3A5F', 'border-color': '#BFDBFE' });
    }

    var piecesHtml = '';
    $('.chk-piece').each(function() {
      var isChk = $(this).is(':checked');
      var label = $(this).closest('.piece-checkbox-card').find('div > div:first-child').text().trim();
      piecesHtml += '<div style="display:flex; align-items:center; justify-content:space-between; padding:6px 10px; border-radius:6px; background:' + (isChk ? '#F0FDF4' : '#F8FAFC') + '; border:1px solid ' + (isChk ? '#BBF7D0' : '#E2E8F0') + ';">';
      piecesHtml += '  <span style="color:#0F172A; font-weight:600;">' + label + '</span>';
      piecesHtml += isChk ? '  <span style="color:#166534; font-weight:800; font-size:11px;"><i data-lucide="check" style="width:12px;height:12px;display:inline-block;vertical-align:middle;"></i> Déposée</span>' : '  <span style="color:#64748B; font-weight:600; font-size:11px;">En attente</span>';
      piecesHtml += '</div>';
    });
    $('#recap_pieces_list').html(piecesHtml || '<span style="color:#64748B; font-style:italic;">Aucune pièce configurée</span>');

    // 5. Accessoires & Kits Souscrits
    var accsHtml = '';
    var totalAccs = 0;
    var seenAccs = {};
    $('#form-wizard-etudiant .chk-accessoire:checked, #form-wizard-etudiant input[name="accessoires[]"]:checked').each(function() {
      var val = $(this).val();
      if (!val || seenAccs[val]) return;
      seenAccs[val] = true;
      totalAccs++;

      var label = $(this).attr('data-label') || $(this).closest('.acc-checkbox-card').find('.acc-label').text().trim() || $(this).next('span').text().trim() || $(this).closest('label').text().trim() || val;
      var prix = Number($(this).attr('data-prix') || 0);
      var prixFormatted = prix > 0 ? ' (' + prix.toLocaleString('fr-FR') + ' FCFA)' : '';
      accsHtml += '<div style="background: #EFF6FF; color: #1E3A5F; border: 1.5px solid #BFDBFE; padding: 7px 12px; border-radius: 8px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(30,58,95,0.06); margin: 3px;">';
      accsHtml += '  <i data-lucide="check-circle" style="width: 15px; height: 15px; color: #15803D; flex-shrink: 0;"></i>';
      accsHtml += '  <span>' + label + '</span>';
      if (prix > 0) {
        accsHtml += '  <span style="color: #1E3A5F; font-weight: 800; font-size: 11.5px; background: #DBEAFE; padding: 2px 7px; border-radius: 4px;">' + prix.toLocaleString('fr-FR') + ' FCFA</span>';
      }
      accsHtml += '</div>';
    });

    if (totalAccs > 0) {
      $('#recap_acc_badge').text(totalAccs + ' kit(s) souscrit(s)').css({'background': '#DCFCE7', 'color': '#166534', 'border-color': '#86EFAC'});
      $('#recap_accessoires_list').html(accsHtml);
    } else {
      $('#recap_acc_badge').text('0 kit').css({'background': '#EFF6FF', 'color': '#1E3A5F', 'border-color': '#BFDBFE'});
      $('#recap_accessoires_list').html('<span style="color: #64748B; font-style: italic; font-size: 12.5px; padding: 4px 0;">Aucun kit ou accessoire supplémentaire sélectionné lors de cette inscription.</span>');
    }

    if (window.lucide) lucide.createIcons();
  }

  // Gestion visuelle et réactive des kits / accessoires
  function updateAccCardStyle($chk) {
    var $card = $chk.closest('.acc-checkbox-card');
    if ($chk.is(':checked')) {
      $card.css({ 'background': '#EFF6FF', 'border-color': '#93C5FD' });
    } else {
      $card.css({ 'background': '#FFFFFF', 'border-color': '#CBD5E1' });
    }
  }

  $(document).on('change', '.chk-accessoire', function() {
    updateAccCardStyle($(this));
  });

  // Gestion visuelle et réactive des pièces à fournir
  function updatePieceCardStyle($chk) {
    var $card = $chk.closest('.piece-checkbox-card');
    var $badge = $card.find('.piece-status-badge');
    if ($chk.is(':checked')) {
      $card.css({ 'background': '#F0FDF4', 'border-color': '#86EFAC' });
      $badge.css({ 'background': '#DCFCE7', 'color': '#15803D' }).text('✓ Pièce Déposée');
    } else {
      $card.css({ 'background': '#FFFFFF', 'border-color': '#E2E8F0' });
      $badge.css({ 'background': '#F1F5F9', 'color': '#64748B' }).text('En attente de dépôt');
    }
  }

  $(document).on('change', '.chk-piece', function() {
    updatePieceCardStyle($(this));
  });

  $('#btn-check-all-pieces').on('click', function() {
    $('.chk-piece').prop('checked', true).each(function() {
      updatePieceCardStyle($(this));
    });
    saveFormData();
  });

  $('#btn-uncheck-all-pieces').on('click', function() {
    $('.chk-piece').prop('checked', false).each(function() {
      updatePieceCardStyle($(this));
    });
    saveFormData();
  });

  // Auto-récupération et affichage complet de la scolarité et de TOUTES les tranches selon la classe et le statut d'affectation
  function refreshClassTuition() {
    var classeCode = $('#wiz_classe').val();
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

    if (!classeCode) {
      $('#wiz-class-tuition-box').slideUp(200);
      $('#wiz_montant_scolarite').val(0);
      updateNetScolarite();
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>inscription/getTuitionByClass',
      type: 'GET',
      data: { 
        classe_code: classeCode,
        affectation_etat: affectationEtat
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          var totalScolarite = Number(d.montant_scolarite || 0);

          $('#wiz_montant_scolarite').val(totalScolarite);
          var regimeBadge = d.affectation_etat === 'affecte' ? ' <span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-size:11px; padding:2px 8px; border-radius:4px; border:1px solid #BFDBFE; font-weight:700;">Affecté État</span>' : ' <span class="badge" style="background:#F8FAFC; color:#475569; font-size:11px; padding:2px 8px; border-radius:4px; border:1px solid #CBD5E1; font-weight:700;">Non Affecté / Privé</span>';
          $('#wiz_summary_classe_title').html(d.libelle_classe + regimeBadge);
          $('#wiz_summary_filiere_niveau').text(
            (d.libelle_filiere ? 'Filière : ' + d.libelle_filiere + ' • ' : '') + 
            (d.libelle_niveau ? 'Niveau : ' + d.libelle_niveau + ' • ' : '') + 
            (d.libelle_annee ? 'Année : ' + d.libelle_annee : '')
          );
          $('#wiz_summary_total_scolarite').text(totalScolarite.toLocaleString('fr-FR') + ' FCFA');

          // Rendu dynamique de TOUTES les tranches
          var tranches = d.tranches || [];
          var tbodyHtml = '';
          var sumTranches = 0;

          if (tranches.length > 0) {
            $('#wiz_tranches_count_badge').text(tranches.length + ' tranche(s) configurée(s)').show();

            tranches.forEach(function(tr, idx) {
              var mt = Number(tr.montant_tranche || tr.montant_tranche_num || 0);
              sumTranches += mt;
              var isFirst = (idx === 0);
              var pct = totalScolarite > 0 ? Math.round((mt / totalScolarite) * 100) : 0;
              var dateLimite = tr.date_limite_formatee || (tr.date_limite ? tr.date_limite : 'Non définie');

              tbodyHtml += '<tr style="border-bottom: 1px solid #F1F5F9; background: ' + (isFirst ? '#F8FAFC' : '#FFFFFF') + ';">';
              tbodyHtml += '  <td style="padding: 12px 16px; font-weight: 800; color: #64748B;">' + (idx + 1) + '</td>';
              tbodyHtml += '  <td style="padding: 12px 16px;">';
              tbodyHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13.5px;">' + (tr.libelle_tranche || ('Tranche ' + (idx + 1))) + '</div>';
              if (isFirst) {
                tbodyHtml += '    <span style="background:#EFF6FF; color:#1D4ED8; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; border:1px solid #BFDBFE;">Exigible à l\'inscription</span>';
              }
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 12px 16px; text-align: center;">';
              tbodyHtml += '    <span style="background:#F1F5F9; color:#334155; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;">' + pct + '%</span>';
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 12px 16px; text-align: center; color: #475569; font-size: 12.5px; font-weight: 600;">';
              tbodyHtml += '    <i data-lucide="calendar" style="width:13px; height:13px; display:inline-block; vertical-align:middle; margin-right:4px; color:#64748B;"></i>' + dateLimite;
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: #1E3A5F; font-size: 14px;">';
              tbodyHtml += '    ' + mt.toLocaleString('fr-FR') + ' FCFA';
              tbodyHtml += '  </td>';
              tbodyHtml += '</tr>';
            });
          } else {
            $('#wiz_tranches_count_badge').text('Paiement Unique');
            sumTranches = totalScolarite;
            tbodyHtml += '<tr>';
            tbodyHtml += '  <td colspan="5" style="padding: 16px; text-align: center; color: #64748B; font-style: italic;">';
            tbodyHtml += '    Aucune tranche intermédiaire configurée pour cette classe. Règlement unique de la scolarité totale : <strong>' + totalScolarite.toLocaleString('fr-FR') + ' FCFA</strong>';
            tbodyHtml += '  </td>';
            tbodyHtml += '</tr>';
          }

          $('#wiz_tranches_table_body').html(tbodyHtml);
          $('#wiz_tranches_total_sum').text(sumTranches.toLocaleString('fr-FR') + ' FCFA');

          updateNetScolarite();
          $('#wiz-class-tuition-box').stop(true, true).slideDown(250);
          if (window.lucide) lucide.createIcons();
        } else {
          $('#wiz-class-tuition-box').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        $('#wiz-class-tuition-box').slideUp(200);
      }
    });
  }

  $('#wiz_classe').on('change select2:select', refreshClassTuition);
  $('input[name="affectation_etat"]').on('change', function() {
    $('input[name="affectation_etat"]').each(function() {
      var isChecked = $(this).is(':checked');
      $(this).closest('label').css({
        'border-color': isChecked ? '#1E3A5F' : '#CBD5E1',
        'background': isChecked ? '#EFF6FF' : '#FFFFFF',
        'color': isChecked ? '#1E3A5F' : '#334155'
      });
    });
    refreshClassTuition();
  });

  // Calcul dynamique du net à payer après remise
  function updateNetScolarite() {
    var total = Number($('#wiz_montant_scolarite').val() || 0);
    var remise = Number($('#wiz_remise').val() || 0);
    if (remise > 0 && total > 0) {
      var net = Math.max(0, total - remise);
      $('#wiz_summary_net_scolarite').text(net.toLocaleString('fr-FR') + ' FCFA');
      $('#wiz_summary_net_box').fadeIn(200);
    } else {
      $('#wiz_summary_net_box').hide();
    }
  }

  $('#wiz_remise').on('input change keyup', function() {
    updateNetScolarite();
  });

  // Auto save on any input change
  $('#form-wizard-etudiant').on('input change keyup', 'input, select, textarea', function() {
    saveFormData();
  });

  // Next Step
  $('#btn-wizard-next').on('click', function() {
    if (validateStep(currentStep)) {
      if (currentStep < totalSteps) {
        currentStep++;
        updateWizardUI();
      }
    }
  });

  // Previous Step
  $('#btn-wizard-prev').on('click', function() {
    if (currentStep > 1) {
      currentStep--;
      updateWizardUI();
    }
  });

  // Direct Step click
  $('.wizard-step-item').on('click', function() {
    var targetStep = parseInt($(this).attr('data-step'));
    if (targetStep < currentStep) {
      currentStep = targetStep;
      updateWizardUI();
    } else if (targetStep === currentStep + 1 && validateStep(currentStep)) {
      currentStep = targetStep;
      updateWizardUI();
    }
  });

  // Manual Reset Draft button (Custom Pro Modal)
  $('#btn-reset-draft').on('click', function() {
    showConfirm(
      'Voulez-vous réinitialiser le formulaire et effacer toutes les données saisies ? Cette action est irréversible.',
      function() {
        localStorage.removeItem('geicg_etudiant_wizard_data');
        localStorage.removeItem('geicg_etudiant_wizard_step');
        location.reload();
      },
      'Réinitialiser le brouillon',
      'Effacer tout',
      true
    );
  });

  // Clear storage on form submit
  $('#form-wizard-etudiant').on('submit', function() {
    localStorage.removeItem('geicg_etudiant_wizard_data');
    localStorage.removeItem('geicg_etudiant_wizard_step');
  });

  // Restore state on load
  restoreFormData();
  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Rechercher / Sélectionner --",
      allowClear: true,
      width: '100%'
    });
  }
  updateWizardUI();

  // Déclencher le chargement des tarifs si une classe est déjà pré-sélectionnée
  var initClass = $('#wiz_classe').val();
  if (initClass) {
    $('#wiz_classe').trigger('change');
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
