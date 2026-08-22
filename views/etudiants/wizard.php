<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$classes = (new ModelClasse())->getAll();
$accessoires = (new ModelAccessoire())->getAll();
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
        <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
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
          <i data-lucide="package" style="width: 16px; height: 16px;"></i> <span>Accessoires</span>
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
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matricule (Auto-généré si vide)</label>
                <input type="text" class="form-control" name="matricule_etudiant" placeholder="Ex: ETU-2026-001" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
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
                <input type="date" class="form-control" name="date_naissance" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Lieu de naissance</label>
                <input type="text" class="form-control" name="lieu_naissance" placeholder="Ex: Abidjan Treichville" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nationalité</label>
                <input type="text" class="form-control" name="nationalite" value="Ivoirienne" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
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
                <textarea class="form-control" name="adresse_etudiant" rows="2" placeholder="Ex: Cocody Riviera 3, Abidjan" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;"></textarea>
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
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom du Tuteur Légal</label>
                <input type="text" class="form-control" name="nom_tuteur" placeholder="Ex: KOUAME Bernard" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Téléphone du Tuteur Légal</label>
                <input type="text" class="form-control" name="telephone_tuteur" placeholder="Ex: 0707070707" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
            </div>
          </div>

          <!-- ÉTAPE 3 : INSCRIPTION ACADÉMIQUE -->
          <div class="wizard-step-content" data-step="3">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="graduation-cap" style="width: 20px; height: 20px;"></i> Étape 3 : Inscription Académique & Scolarité
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe d'affectation <span style="color: #EF4444;">*</span></label>
                <select class="form-control" id="wiz_classe" name="classe_code" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
                  <option value="">-- Sélectionner la classe --</option>
                  <?php foreach($classes as $cl): ?>
                    <option value="<?= $cl['code_classe'] ?>"><?= htmlspecialchars($cl['libelle_classe']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Montant Scolarité Annuelle Due (FCFA) <span style="color: #EF4444;">*</span></label>
                <input type="number" class="form-control" id="wiz_montant_scolarite" name="montant_scolarite_inscription" placeholder="Ex: 500000" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
                <input type="number" class="form-control" name="remise_accordee" placeholder="Ex: 50000" value="0" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;">
              </div>
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date d'inscription <span style="color: #EF4444;">*</span></label>
                <input type="date" class="form-control" name="date_inscription" value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
              </div>
            </div>
          </div>

          <!-- ÉTAPE 4 : ACCESSOIRES & KITS -->
          <div class="wizard-step-content" data-step="4">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="package" style="width: 20px; height: 20px;"></i> Étape 4 : Accessoires & Kits d'Inscription
            </h3>
            <p style="font-size: 13px; color: #64748B; margin-bottom: 16px;">Sélectionnez les kits et accessoires souscrits lors de cette inscription :</p>
            <?php if (!empty($accessoires)): ?>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach($accessoires as $acc): ?>
                  <label style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border: 1px solid #E2E8F0; border-radius: 10px; background: #F8FAFC; cursor: pointer; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <input type="checkbox" name="accessoires[]" value="<?= $acc['code_accessoire'] ?>" style="width: 18px; height: 18px; accent-color: #1E3A5F;">
                      <span style="font-weight: 700; color: #0F172A; font-size: 14px;"><?= htmlspecialchars($acc['libelle_accessoire']) ?></span>
                    </div>
                    <span style="font-weight: 800; color: #1E3A5F; font-size: 13px;"><?= number_format((float)$acc['prix_accessoire'], 0, ',', ' ') ?> FCFA</span>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="padding: 16px; background: #F8FAFC; border-radius: 8px; color: #64748B; font-size: 13px; font-weight: 600;">
                Aucun kit ou accessoire configuré. Vous pouvez continuer cette étape.
              </div>
            <?php endif; ?>
          </div>

          <!-- ÉTAPE 5 : RÉCAPITULATIF & CONFIRMATION -->
          <div class="wizard-step-content" data-step="5">
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i> Étape 5 : Récapitulatif & Validation du Dossier
            </h3>

            <div style="background: #F8FAFC; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
              <div>
                <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="user" style="width: 16px; height: 16px;"></i> Identité Étudiant
                </h4>
                <div style="font-size: 13px; color: #334155; line-height: 1.6;">
                  <div><strong>Nom & Prénoms :</strong> <span id="recap_nom_prenom" style="font-weight: 700; color: #0F172A;">-</span></div>
                  <div><strong>Téléphone :</strong> <span id="recap_tel">-</span></div>
                  <div><strong>Sexe :</strong> <span id="recap_sexe">-</span></div>
                </div>
              </div>

              <div>
                <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="users" style="width: 16px; height: 16px;"></i> Parents & Contacts
                </h4>
                <div style="font-size: 13px; color: #334155; line-height: 1.6;">
                  <div><strong>Père :</strong> <span id="recap_pere">-</span></div>
                  <div><strong>Mère :</strong> <span id="recap_mere">-</span></div>
                  <div><strong>Tuteur :</strong> <span id="recap_tuteur">-</span></div>
                </div>
              </div>

              <div>
                <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i> Inscription & Scolarité
                </h4>
                <div style="font-size: 13px; color: #334155; line-height: 1.6;">
                  <div><strong>Classe :</strong> <span id="recap_classe" style="font-weight: 700; color: #1E3A5F;">-</span></div>
                  <div><strong>Scolarité Due :</strong> <span id="recap_montant" style="font-weight: 800; color: #0F172A;">- FCFA</span></div>
                  <div><strong>Remise Accordée :</strong> <span id="recap_remise" style="font-weight: 700; color: #15803D;">0 FCFA</span></div>
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

    if (window.lucide) lucide.createIcons();
  }

  function validateStep(step) {
    if (step === 1) {
      var nom = $('#wiz_nom').val().trim();
      var prenom = $('#wiz_prenom').val().trim();
      var tel = $('#wiz_tel').val().trim();
      if (!nom || !prenom || !tel) {
        alert('Veuillez remplir le nom, les prénoms et le téléphone de l\'étudiant avant de continuer.');
        return false;
      }
    }
    if (step === 3) {
      var classe = $('#wiz_classe').val();
      var montant = $('#wiz_montant_scolarite').val();
      if (!classe || !montant) {
        alert('Veuillez sélectionner la classe et renseigner la scolarité due.');
        return false;
      }
    }
    return true;
  }

  function fillRecap() {
    var nom = $('#wiz_nom').val().trim();
    var prenom = $('#wiz_prenom').val().trim();
    var tel = $('#wiz_tel').val().trim();
    var sexe = $('select[name="sexe_etudiant"]').val() === 'M' ? 'Masculin' : 'Féminin';

    $('#recap_nom_prenom').text((nom + ' ' + prenom).toUpperCase());
    $('#recap_tel').text(tel || '-');
    $('#recap_sexe').text(sexe);

    var pere = $('input[name="nom_pere"]').val().trim();
    var mere = $('input[name="nom_mere"]').val().trim();
    var tuteur = $('input[name="nom_tuteur"]').val().trim();
    $('#recap_pere').text(pere || 'Non renseigné');
    $('#recap_mere').text(mere || 'Non renseignée');
    $('#recap_tuteur').text(tuteur || 'Non renseigné');

    var classeText = $('#wiz_classe option:selected').text();
    var montant = $('#wiz_montant_scolarite').val();
    var remise = $('input[name="remise_accordee"]').val() || 0;

    $('#recap_classe').text(classeText || 'Non choisie');
    $('#recap_montant').text(montant ? Number(montant).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA');
    $('#recap_remise').text(Number(remise).toLocaleString('fr-FR') + ' FCFA');
  }

  $('#btn-wizard-next').on('click', function() {
    if (validateStep(currentStep)) {
      if (currentStep < totalSteps) {
        currentStep++;
        updateWizardUI();
      }
    }
  });

  $('#btn-wizard-prev').on('click', function() {
    if (currentStep > 1) {
      currentStep--;
      updateWizardUI();
    }
  });

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

  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
