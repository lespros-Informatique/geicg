<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = (new ModelAnnee())->getAccessibleAnnees();
$modelAnnee = new ModelAnnee();
$activeYearRow = $modelAnnee->getActiveYear();
$activeAnneeCode = Session::get('annee_active_code', '') ?: ($activeYearRow['code_annee'] ?? '');
if (empty($activeAnneeCode)) {
    foreach ($annees as $a) {
        if (!empty($a['est_active']) || ($a['statut_annee'] ?? '') === STATUTS::ACTIF) {
            $activeAnneeCode = $a['code_annee'];
            break;
        }
    }
    if (empty($activeAnneeCode) && !empty($annees)) {
        $activeAnneeCode = $annees[0]['code_annee'];
    }
}
$classes = (new ModelClasse())->getClassesWithScolarite($activeAnneeCode, 'non_affecte');
$accessoires = (new ModelAccessoire())->getAll();
$pieces = (new ModelPieceFournir())->getPiecesWithRequirements();
?>

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
        <form id="form-wizard-etudiant" action="<?= RACINE ?>etudiant/addWizard" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <!-- ÉTAPE 1 : IDENTITÉ ÉTUDIANT -->
          <div class="wizard-step-content active" data-step="1">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="user" style="width: 20px; height: 20px;"></i> Étape 1 : Identité & Coordonnées de l'Étudiant
            </h3>
            <div class="wizard-grid-etape1">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule École (Auto-généré)</label>
                <input type="text" class="form-control" name="matricule_etudiant" id="wiz_matricule" placeholder="Auto-généré : XX-123/GEB/AA26" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; font-family: monospace; font-weight: 700; color: #1E3A5F; cursor: not-allowed;" readonly>
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
                <input type="date" class="form-control" id="wiz_date_naissance" name="date_naissance_etudiant" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Lieu de naissance</label>
                <input type="text" class="form-control" id="wiz_lieu_naissance" name="lieu_naissance_etudiant" placeholder="Ex: Abidjan Treichville" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nationalité</label>
                <input type="text" class="form-control" id="wiz_nationalite" name="nationalite_etudiant" value="Ivoirienne" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone étudiant <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control" id="wiz_tel" name="telephone_etudiant" placeholder="Ex: 0708091011" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Email étudiant</label>
                <input type="email" class="form-control" id="wiz_email" name="email_etudiant" placeholder="Ex: jean.kouassi@etudiant.geicg.ci" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse de résidence</label>
                <textarea class="form-control" id="wiz_residence" name="lieu_residence_etudiant" rows="2" placeholder="Ex: Cocody Riviera 3, Abidjan" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
              </div>
            </div>
          </div>

          <!-- ÉTAPE 2 : PARENTS ET TUTEURS -->
          <div class="wizard-step-content" data-step="2">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="users" style="width: 20px; height: 20px;"></i> Étape 2 : Parents & Tuteurs Légaux
            </h3>
            <div class="wizard-grid-3">
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
                  <label class="label-affectation-choice label-affectation-non-affecte" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 2px solid #2563EB; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #1E3A5F; background: #EFF6FF; transition: all 0.2s;">
                    <input type="radio" name="affectation_etat" value="non_affecte" checked style="accent-color: #2563EB; width: 16px; height: 16px;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                      <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #2563EB;"></span>
                      Non Affecté
                    </span>
                  </label>
                  <label class="label-affectation-choice label-affectation-affecte" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 2px solid #CBD5E1; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #334155; background: #FFFFFF; transition: all 0.2s;">
                    <input type="radio" name="affectation_etat" value="affecte" style="accent-color: #16A34A; width: 16px; height: 16px;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                      <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #16A34A;"></span>
                      Affecté
                    </span>
                  </label>
                </div>
                <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Le tarif et le nombre d'échéances s'adaptent automatiquement selon le régime choisi (Affecté ou Non Affecté).</small>
              </div>

              <!-- ANNÉE ACADÉMIQUE -->
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                  Année Académique d'Inscription <span style="color: #EF4444;">*</span>
                </label>
                <select class="form-control select2" id="wiz_annee" name="annee_code" style="width: 100%;" required>
                  <?php foreach($annees as $a): ?>
                    <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($activeAnneeCode === $a['code_annee'] || (!empty($a['est_active']) || ($a['statut_annee'] ?? '') === STATUTS::ACTIF)) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($a['libelle_annee']) ?> (<?= htmlspecialchars($a['code_annee']) ?>)<?= (!empty($a['est_active']) || ($a['statut_annee'] ?? '') === STATUTS::ACTIF) ? ' — Session Active' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- CLASSE D'AFFECTATION -->
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe d'affectation <span style="color: #EF4444;">*</span></label>
                <select class="form-control select2" id="wiz_classe" name="classe_code" style="width: 100%;" required>
                  <?php if (!empty($classes)): ?>
                    <option value="">-- Rechercher / Sélectionner la classe d'affectation --</option>
                    <?php foreach($classes as $cl): ?>
                      <option value="<?= htmlspecialchars($cl['code_classe']) ?>" 
                              data-annee="<?= htmlspecialchars($cl['annee_code'] ?? $activeAnneeCode) ?>"
                              data-type-filiere="<?= htmlspecialchars($cl['type_filiere'] ?? '') ?>"
                              data-niveau="<?= htmlspecialchars($cl['niveau_code'] ?? '') ?>">
                        <?= htmlspecialchars($cl['libelle_classe']) ?> (<?= htmlspecialchars($cl['code_classe']) ?>)
                      </option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <option value="">-- Aucune classe avec scolarité enregistrée pour cette année --</option>
                  <?php endif; ?>
                </select>
                <div id="wiz_classe_empty_msg" style="<?= empty($classes) ? '' : 'display: none;' ?> margin-top: 8px; font-size: 12.5px; color: #B45309; background: #FFFBEB; border: 1px solid #FDE68A; padding: 10px 14px; border-radius: 8px; line-height: 1.4;">
                  <i data-lucide="alert-triangle" style="width: 15px; height: 15px; display: inline-block; vertical-align: middle; margin-right: 6px; color: #D97706;"></i>
                  <span>Aucune classe n'a été créée pour l'année académique sélectionnée. Veuillez créer les classes dans le module Structure Académique &gt; Classes pour associer les tarifs de scolarité enregistrés.</span>
                </div>
              </div>

              <!-- Montant auto-récupéré -->
              <input type="hidden" id="wiz_montant_scolarite" name="montant_scolarite_inscription" value="0">

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
                <input type="number" min="0" step="any" class="form-control" id="wiz_remise" name="remise_accordee" placeholder="0" value="0" readonly style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #64748B; font-weight: 600; pointer-events: none; cursor: not-allowed;">
              </div>
            </div>

            <!-- FICHE TARIFAIRE AUTOMATIQUE & ÉCHÉANCIER COMPLET DES TRANCHES -->
            <div id="wiz-class-tuition-box" style="display: none; background: #FFFFFF; border: 2px solid #CBD5E1; border-radius: 12px; padding: 22px 24px; margin-top: 22px; box-shadow: 0 4px 14px -2px rgba(0,0,0,0.08); transition: all 0.3s ease;">
              
              <!-- 1. En-tête avec résumé de la classe et total scolarité -->
              <div id="wiz_tuition_header_bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 16px; margin-bottom: 18px; border-radius: 8px; padding: 12px 16px; transition: all 0.3s;">
                <div>
                  <div id="wiz_tuition_top_tag" style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="graduation-cap" style="width: 16px; height: 16px; color: #D97706;"></i> Grille Tarifaire & Échéancier de Scolarité
                  </div>
                  <div style="font-size: 17px; font-weight: 900; color: #0F172A; margin-top: 4px;" id="wiz_summary_classe_title">-</div>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                  <div id="wiz_summary_total_scolarite_box" style="background: #EFF6FF; border: 1.5px solid #BFDBFE; padding: 10px 18px; border-radius: 10px; text-align: right; transition: all 0.3s;">
                    <div id="wiz_summary_total_scolarite_label" style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase;">Scolarité Annuelle</div>
                    <div style="font-size: 18px; font-weight: 900; color: #1E3A5F; margin-top: 2px;" id="wiz_summary_total_scolarite">0 FCFA</div>
                  </div>
                  <div id="wiz_summary_frais_annexes_box" style="background: #FFFBEB; border: 1.5px solid #FDE68A; padding: 10px 18px; border-radius: 10px; text-align: right; transition: all 0.3s;">
                    <div style="font-size: 11px; font-weight: 800; color: #B45309; text-transform: uppercase;">Frais Annexes</div>
                    <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 2px;" id="wiz_summary_total_frais_annexes">0 FCFA</div>
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
                  <i id="wiz_tranches_calendar_icon" data-lucide="calendar" style="width: 15px; height: 15px; color: #2563EB;"></i> Échéancier de Toutes les Tranches de Paiement
                </span>
                <span id="wiz_tranches_count_badge" style="background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                  0 tranche(s)
                </span>
              </div>

              <!-- 3. Tableau détaillé et visuel de TOUTES les tranches -->
              <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 8px; overflow: hidden; border: 1px solid #E2E8F0;">
                  <thead>
                    <tr id="wiz_tranches_table_header" style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s;">
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
                </table>
              </div>

              <!-- 4. Carte détaillée des Frais d'inscription (1ère Tranche + Frais Annexes Catégorie Inscription) -->
              <div id="wiz_frais_annexes_detail_card" style="margin-top: 18px; background: #FFFBEB; border: 1.5px solid #FDE68A; padding: 14px 18px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                  <span style="font-size: 11.5px; font-weight: 800; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="bookmark-check" style="width: 16px; height: 16px; color: #D97706;"></i> Frais d'inscription
                  </span>
                  <div style="font-size: 14px; font-weight: 700; color: #78350F; margin-top: 4px;" id="wiz_frais_annexes_title_text">-</div>
                  <div style="font-size: 12px; color: #92400E; margin-top: 2px;" id="wiz_frais_annexes_details_desc">-</div>
                </div>
                <div style="background: #FEF3C7; border: 1px solid #FCD34D; padding: 8px 16px; border-radius: 8px; text-align: right;">
                  <div style="font-size: 10.5px; font-weight: 800; color: #92400E; text-transform: uppercase;">Total Frais d'Inscription</div>
                  <div style="font-size: 17px; font-weight: 900; color: #B45309;" id="wiz_frais_annexes_amount_badge">0 FCFA</div>
                </div>
              </div>

            </div>
          </div>

          <!-- ÉTAPE 4 : ACCESSOIRES & DOSSIER ÉTUDIANT (PIÈCES FOURNIES) -->
          <div class="wizard-step-content" data-step="4">
            
            <!-- SECTION A : ACCESSOIRES & KITS -->
            <div style="margin-bottom: 30px;">
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
                <div>
                  <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="package" style="width: 18px; height: 18px;"></i> Accessoires & Kits d'Inscription
                  </h3>
                  <p style="font-size: 12.5px; color: #64748B; margin: 3px 0 0 0;">Sélectionnez les kits et accessoires souscrits lors de cette inscription :</p>
                </div>
                <div style="display: flex; gap: 8px;">
                  <button type="button" id="btn-check-all-accessoires" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E3A5F; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;">
                    <i data-lucide="check-square" style="width: 14px; height: 14px;"></i> Tout cocher
                  </button>
                  <button type="button" id="btn-uncheck-all-accessoires" style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; font-size: 11.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;">
                    Tout décocher
                  </button>
                </div>
              </div>
              <div id="acc_no_class_msg" style="display: none; padding: 12px 16px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; color: #92400E; font-size: 13px; font-weight: 600; margin-bottom: 14px;">
                <i data-lucide="info" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 6px; color: #D97706;"></i>
                <span>Sélectionnez une classe à l'étape 3 pour filtrer et pré-cocher automatiquement les kits correspondants.</span>
              </div>

              <?php if (!empty($accessoires)): ?>
                <div id="acc_cards_container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                  <?php foreach($accessoires as $acc): 
                    $kitTypeFiliere = $acc['type_filiere_cible'] ?? 'TOUT';
                    $niveauCode = $acc['niveau_code'] ?? '';
                    $libelleNiveau = $acc['libelle_niveau'] ?? '';
                    
                    $filiereBadgeText = ($kitTypeFiliere === 'INDUSTRIELLE') ? 'Industrielle' : (($kitTypeFiliere === 'TERTIAIRE') ? 'Tertiaire' : 'Toutes filières');
                    $filiereBadgeBg = ($kitTypeFiliere === 'INDUSTRIELLE') ? '#FEF3C7' : (($kitTypeFiliere === 'TERTIAIRE') ? '#DBEAFE' : '#F1F5F9');
                    $filiereBadgeColor = ($kitTypeFiliere === 'INDUSTRIELLE') ? '#92400E' : (($kitTypeFiliere === 'TERTIAIRE') ? '#1E40AF' : '#475569');

                    $niveauBadgeText = !empty($libelleNiveau) ? $libelleNiveau : 'Tous niveaux';
                  ?>
                    <label class="acc-checkbox-card" 
                           data-code="<?= htmlspecialchars($acc['code_accessoire'] ?? '') ?>"
                           data-type-filiere="<?= htmlspecialchars($kitTypeFiliere) ?>"
                           data-niveau="<?= htmlspecialchars($niveauCode) ?>"
                           style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border: 1.5px solid #CBD5E1; border-radius: 10px; background: #FFFFFF; cursor: pointer; transition: all 0.2s;">
                      <input type="checkbox" name="accessoires[]" class="chk-accessoire" data-label="<?= htmlspecialchars($acc['libelle_accessoire'] ?? '') ?>" value="<?= htmlspecialchars($acc['code_accessoire'] ?? '') ?>" style="width: 18px; height: 18px; accent-color: #1E3A5F; cursor: pointer; margin-top: 2px; flex-shrink: 0;">
                      <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                          <span class="acc-label" style="font-weight: 700; color: #0F172A; font-size: 14px; text-transform: capitalize;"><?= htmlspecialchars($acc['libelle_accessoire'] ?? '') ?></span>
                          <span class="acc-match-badge" style="display: none; font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 4px; background: #DCFCE7; color: #15803D;">Pré-sélectionné</span>
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                          <span style="font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 4px; background: <?= $filiereBadgeBg ?>; color: <?= $filiereBadgeColor ?>;">
                            <?= htmlspecialchars($filiereBadgeText) ?>
                          </span>
                          <span style="font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 4px; background: #F3F4F6; color: #4B5563;">
                            <?= htmlspecialchars($niveauBadgeText) ?>
                          </span>
                        </div>
                      </div>
                    </label>
                  <?php endforeach; ?>
                </div>

                <div id="acc_no_match_msg" style="display: none; padding: 20px; background: #F8FAFC; border: 1.5px dashed #CBD5E1; border-radius: 10px; color: #64748B; font-size: 13px; text-align: center; font-weight: 600; margin-top: 8px;">
                  <i data-lucide="package-x" style="width: 28px; height: 28px; display: block; margin: 0 auto 8px auto; color: #94A3B8;"></i>
                  Aucun kit ou accessoire spécifique n'est configuré pour cette filière et ce niveau.
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
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
                  <?php foreach($pieces as $p): 
                    $nbExemplaires = max(1, (int)($p['nombre_exemplaires'] ?? 1));
                    $natureDoc = $p['nature_document'] ?? '';
                    $natureLabel = '';
                    if ($natureDoc === 'photocopie_simple') $natureLabel = 'Photocopie simple';
                    elseif ($natureDoc === 'photocopie_legalisee') $natureLabel = 'Photocopie légalisée';
                    elseif ($natureDoc === 'original') $natureLabel = 'Original requis';
                    elseif ($natureDoc === 'numerique') $natureLabel = 'Scan numérique';
                  ?>
                    <label class="piece-checkbox-card" 
                           data-nb-exemplaires="<?= $nbExemplaires ?>"
                           style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border: 1.5px solid #E2E8F0; border-radius: 10px; background: #FFFFFF; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                      <input type="checkbox" name="pieces_fournies[]" class="chk-piece" value="<?= htmlspecialchars($p['code_piece_fournir'] ?? '') ?>" style="width: 18px; height: 18px; accent-color: #15803D; margin-top: 2px; flex-shrink: 0; cursor: pointer;">
                      <div style="flex: 1;">
                        <div class="piece-title" style="font-weight: 700; color: #0F172A; font-size: 13.5px; line-height: 1.35;">
                          <?= htmlspecialchars($p['libelle_piece'] ?? '') ?>
                        </div>
                        <?php if (!empty($p['description_piece'])): ?>
                          <div style="font-size: 11.5px; color: #64748B; margin-top: 2px; line-height: 1.3;">
                            <?= htmlspecialchars($p['description_piece']) ?>
                          </div>
                        <?php endif; ?>
                        
                        <!-- Badges indicatifs : Nombre d'exemplaires & Nature & Statut -->
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #E2E8F0;">
                          <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span class="badge-nb-exemplaires" style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 5px; display: inline-flex; align-items: center; gap: 4px;">
                              <i data-lucide="copy" style="width: 12px; height: 12px; color: #D97706;"></i>
                              <?= $nbExemplaires ?> exemplaire<?= $nbExemplaires > 1 ? 's' : '' ?> requis
                            </span>
                            <?php if (!empty($natureLabel)): ?>
                              <span style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 5px;">
                                <?= htmlspecialchars($natureLabel) ?>
                              </span>
                            <?php endif; ?>
                          </div>
                          <div>
                            <span class="piece-status-badge" style="background: #F1F5F9; color: #64748B; font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 5px;">
                              En attente de dépôt
                            </span>
                          </div>
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
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 13px;">
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Date de naissance</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_date_naissance">-</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Lieu de naissance</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_lieu_naissance">-</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Nationalité</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_nationalite">Ivoirienne</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Adresse de résidence</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_residence">Non renseignée</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Téléphone Principal</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_tel">-</div>
                </div>
                <div>
                  <span style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Email Étudiant</span>
                  <div style="color: #0F172A; font-weight: 700;" id="recap_email">Non renseigné</div>
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
                    <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase;">Tarif Scolarité</div>
                    <div style="font-size: 16px; font-weight: 900; color: #0F172A;" id="recap_scolarite_">0 FCFA</div>
                  </div>
                  <div style="background: #FFFBEB; border: 1px solid #FDE68A; padding: 8px 14px; border-radius: 8px; text-align: right;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #B45309; text-transform: uppercase;">Frais Annexes</div>
                    <div style="font-size: 16px; font-weight: 900; color: #B45309;" id="recap_frais_annexes_val">0 FCFA</div>
                  </div>
                  <div style="background: #FFFFFF; border: 1px solid #86EFAC; padding: 8px 14px; border-radius: 8px; text-align: right;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #15803D; text-transform: uppercase;">Remise Accordée</div>
                    <div style="font-size: 16px; font-weight: 900; color: #15803D;" id="recap_remise_val">0 FCFA</div>
                  </div>
                  <div style="background: #166534; border: 1px solid #14532D; padding: 8px 16px; border-radius: 8px; text-align: right;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #DCFCE7; text-transform: uppercase;">Net Définitif (Scolarité)</div>
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

<?php
ViewContext::setScripts(['public/assets/js/modules/etudiants_wizard.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
