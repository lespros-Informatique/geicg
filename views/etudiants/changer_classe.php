<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 

$item = $item ?? [];
$inscription = $inscription ?? [];
$classes = $classes ?? [];
$annees = $annees ?? [];
$encryptedId = $encryptedId ?? (!empty($item['id_etudiant']) ? (new Validator())->crypter($item['id_etudiant']) : '');
$activeAnneeCode = $activeAnneeCode ?? ($inscription['annee_code'] ?? '');

$nomComplet = trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? ''));
$classeActuelle = $inscription['libelle_classe'] ?? 'Non assignée';
$regimeActuel = ($inscription['affectation_etat'] ?? '') === 'affecte' ? 'Affecté (de l\'État)' : 'Non Affecté (Privé)';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; max-width: 1200px; margin: 0 auto;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="refresh-cw" style="width: 24px; height: 24px; color: #1E3A5F;"></i>
            Changer la Classe & Régime de l'Étudiant
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Sélection de la nouvelle classe d'affectation avec simulation en direct des tarifs et échéanciers de scolarité.
          </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <?php if (!empty($encryptedId)): ?>
            <a href="<?= RACINE ?>etudiant/details/<?= $encryptedId ?>" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
              Dossier Étudiant
            </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            Registre Étudiants
          </a>
        </div>
      </div>

      <!-- CARTE PROFIL ÉTUDIANT -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px 24px; border: 1.5px solid #CBD5E1; box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
          
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 56px; height: 68px; min-width: 56px; border-radius: 8px; border: 2px solid #CBD5E1; background: #1E3A5F; color: #FFFFFF; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; font-weight: 900; font-size: 18px; flex-shrink: 0;">
              <?php if (!empty($item['photo_etudiant']) && file_exists(__DIR__ . '/../../public/' . $item['photo_etudiant'])): ?>
                <img src="<?= RACINE . $item['photo_etudiant'] ?>" alt="Photo Étudiant" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <?= strtoupper(substr($item['nom_etudiant'] ?? 'E', 0, 1) . substr($item['prenom_etudiant'] ?? 'T', 0, 1)) ?>
              <?php endif; ?>
            </div>

            <div>
              <h2 style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0;">
                <?= htmlspecialchars($nomComplet) ?>
              </h2>
              <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 5px; font-size: 12.5px;">
                <span style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                  Matricule : <code style="font-weight: 800; font-family: monospace; color: #1E3A5F;"><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code>
                </span>
                <span style="color: #64748B;">Sexe : <strong><?= htmlspecialchars($item['sexe_etudiant'] ?? 'M') ?></strong></span>
                <span style="color: #64748B;">Nat. : <strong><?= htmlspecialchars($item['nationalite_etudiant'] ?? 'Ivoirienne') ?></strong></span>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 8px 14px; border-radius: 8px; text-align: right;">
              <span style="color: #64748B; font-weight: 700; display: block; font-size: 10.5px; text-transform: uppercase;">Classe Actuelle</span>
              <strong style="color: #1E3A5F; font-size: 13.5px;" id="disp_current_class"><?= htmlspecialchars($classeActuelle) ?></strong>
            </div>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 8px 14px; border-radius: 8px; text-align: right;">
              <span style="color: #64748B; font-weight: 700; display: block; font-size: 10.5px; text-transform: uppercase;">Régime Actuel</span>
              <strong style="color: #0F172A; font-size: 13.5px;"><?= htmlspecialchars($regimeActuel) ?></strong>
            </div>
          </div>

        </div>
      </div>

      <!-- FORMULAIRE PRINCIPAL : CHOIX DE LA CLASSE & RÉGIME (INSPIRÉ DE L'ÉTAPE 3 DU WIZARD) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1.5px solid #CBD5E1; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <form id="form-changer-classe-page" action="<?= RACINE ?>etudiant/changerClasse" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="code_inscription" value="<?= htmlspecialchars($inscription['code_inscription'] ?? '') ?>">
          <input type="hidden" name="code_etudiant" value="<?= htmlspecialchars($item['code_etudiant'] ?? '') ?>">
          <input type="hidden" name="encrypted_id" value="<?= htmlspecialchars($encryptedId) ?>">
          <input type="hidden" id="wiz_montant_scolarite" name="montant_scolarite_inscription" value="<?= htmlspecialchars($inscription['montant_scolarite_inscription'] ?? 0) ?>">

          <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0 0 20px 0; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="graduation-cap" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
            Affectation Académique & Choix de la Nouvelle Classe
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            
            <!-- RÉGIME / STATUT D'AFFECTATION ÉTAT -->
            <div class="form-group" style="grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Statut d'Affectation État / Régime de l'Étudiant <span style="color: #EF4444;">*</span>
              </label>
              <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                <?php 
                  $isAffecte = ($inscription['affectation_etat'] ?? '') === 'affecte' || ($inscription['affectation_etat'] ?? '') === 'oui'; 
                ?>
                <label class="label-affectation-choice label-affectation-non-affecte" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 2px solid <?= !$isAffecte ? '#2563EB' : '#CBD5E1' ?>; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: <?= !$isAffecte ? '#1E3A5F' : '#334155' ?>; background: <?= !$isAffecte ? '#EFF6FF' : '#FFFFFF' ?>; transition: all 0.2s;">
                  <input type="radio" name="affectation_etat" value="non_affecte" <?= !$isAffecte ? 'checked' : '' ?> style="accent-color: #2563EB; width: 16px; height: 16px;">
                  <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #2563EB;"></span>
                    Non Affecté (Privé)
                  </span>
                </label>
                <label class="label-affectation-choice label-affectation-affecte" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 2px solid <?= $isAffecte ? '#16A34A' : '#CBD5E1' ?>; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: <?= $isAffecte ? '#15803D' : '#334155' ?>; background: <?= $isAffecte ? '#F0FDF4' : '#FFFFFF' ?>; transition: all 0.2s;">
                  <input type="radio" name="affectation_etat" value="affecte" <?= $isAffecte ? 'checked' : '' ?> style="accent-color: #16A34A; width: 16px; height: 16px;">
                  <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #16A34A;"></span>
                    Affecté (de l'État)
                  </span>
                </label>
              </div>
              <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">Le tarif et le nombre d'échéances s'adaptent automatiquement selon le régime choisi (Affecté ou Non Affecté).</small>
            </div>

            <!-- ANNÉE ACADÉMIQUE -->
            <div class="form-group" style="grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Année Académique d'Inscription <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="wiz_annee" name="annee_code" style="width: 100%;" required>
                <?php foreach($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= ($activeAnneeCode === $a['code_annee'] || (!empty($a['est_active']) || ($a['statut_annee'] ?? '') === 'actif')) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?> (<?= htmlspecialchars($a['code_annee']) ?>)<?= (!empty($a['est_active']) || ($a['statut_annee'] ?? '') === 'actif') ? ' — Session Active' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- CLASSE D'AFFECTATION -->
            <div class="form-group" style="grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Nouvelle Classe d'affectation <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="wiz_classe" name="classe_code" style="width: 100%;" required>
                <?php if (!empty($classes)): ?>
                  <option value="">-- Rechercher / Sélectionner la classe d'affectation --</option>
                  <?php foreach($classes as $cl): ?>
                    <option value="<?= htmlspecialchars($cl['code_classe']) ?>" 
                            <?= (($inscription['classe_code'] ?? '') === $cl['code_classe']) ? 'selected' : '' ?>
                            data-annee="<?= htmlspecialchars($cl['annee_code'] ?? $activeAnneeCode) ?>"
                            data-type-filiere="<?= htmlspecialchars($cl['type_filiere'] ?? '') ?>"
                            data-niveau="<?= htmlspecialchars($cl['niveau_code'] ?? '') ?>">
                      <?= htmlspecialchars($cl['libelle_classe']) ?> (<?= htmlspecialchars($cl['code_classe']) ?>)
                    </option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="">-- Aucune classe disponible pour cette année --</option>
                <?php endif; ?>
              </select>
            </div>

            <!-- Remise / Bourse -->
            <div class="form-group">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
              <input type="number" min="0" step="any" class="form-control" id="wiz_remise" name="remise_accordee" placeholder="0" value="<?= htmlspecialchars($inscription['remise_scolarite'] ?? 0) ?>" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 600;">
            </div>

          </div>

          <!-- GRILLE TARIFAIRE AUTOMATIQUE & ÉCHÉANCIER COMPLET DES TRANCHES (STYLE WIZARD) -->
          <div id="wiz-class-tuition-box" style="display: none; background: #FFFFFF; border: 2px solid #CBD5E1; border-radius: 12px; padding: 22px 24px; margin-top: 24px; box-shadow: 0 4px 14px -2px rgba(0,0,0,0.08); transition: all 0.3s ease;">
            
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
                  <!-- Rempli dynamiquement en JS -->
                </tbody>
              </table>
            </div>

            <!-- 4. Carte détaillée des Frais d'inscription -->
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

          <!-- BOUTONS D'ACTION DU FORMULAIRE -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1.5px solid #E2E8F0; width: 100%;">
            <button type="submit" id="btn-submit-change-classe-page" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 11px 26px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="check" style="width: 18px; height: 18px;"></i>
              <span>Valider le Changement de Classe</span>
            </button>
            <a href="<?= !empty($encryptedId) ? (RACINE . 'etudiant/details/' . $encryptedId) : (RACINE . 'etudiant/list') ?>" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px; display: inline-flex; align-items: center; gap: 6px;">
              Annuler
            </a>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  if ($.fn.select2) {
    $('.select2').select2({
      placeholder: "-- Sélectionner --",
      allowClear: true,
      width: '100%'
    });
  }

  // Interaction visuelle sur le choix du régime (Radio Affecté vs Non Affecté)
  $('input[name="affectation_etat"]').on('change', function() {
    var val = $(this).val();
    $('.label-affectation-choice').each(function() {
      var isChecked = $(this).find('input[type="radio"]').is(':checked');
      if ($(this).hasClass('label-affectation-affecte')) {
        $(this).css({
          'border-color': isChecked ? '#16A34A' : '#CBD5E1',
          'background': isChecked ? '#F0FDF4' : '#FFFFFF',
          'color': isChecked ? '#15803D' : '#334155'
        });
      } else {
        $(this).css({
          'border-color': isChecked ? '#2563EB' : '#CBD5E1',
          'background': isChecked ? '#EFF6FF' : '#FFFFFF',
          'color': isChecked ? '#1E3A5F' : '#334155'
        });
      }
    });
    refreshClassTuition();
  });

  $('#wiz_classe, #wiz_annee').on('change', function() {
    refreshClassTuition();
  });

  $('#wiz_remise').on('input change', function() {
    updateNetScolarite();
  });

  function updateNetScolarite() {
    var totalScolarite = Number($('#wiz_montant_scolarite').val() || 0);
    var remise = Number($('#wiz_remise').val() || 0);
    var net = Math.max(0, totalScolarite - remise);

    if (remise > 0) {
      $('#wiz_summary_net_scolarite').text(net.toLocaleString('fr-FR') + ' FCFA');
      $('#wiz_summary_net_box').stop(true, true).fadeIn(200);
    } else {
      $('#wiz_summary_net_box').hide();
    }
  }

  function refreshClassTuition() {
    var classeCode = $('#wiz_classe').val();
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

    if (!classeCode) {
      $('#wiz-class-tuition-box').slideUp(200);
      $('#wiz_montant_scolarite').val(0);
      $('#wiz_summary_total_frais_annexes').text('0 FCFA');
      $('#wiz_frais_annexes_amount_badge').text('0 FCFA');
      $('#wiz_frais_annexes_title_text').text('-');
      $('#wiz_frais_annexes_details_desc').text('-');
      updateNetScolarite();
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>inscription/getTuitionByClass',
      type: 'GET',
      data: { 
        classe_code: classeCode,
        affectation_etat: affectationEtat,
        annee_code: $('#wiz_annee').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res && res.status === 1 && res.data) {
          var d = res.data;
          var totalScolarite = Number(d.montant_scolarite || 0);

          var totalFA = Number(d.total_frais_annexes || 0);
          var totalFAFormate = d.total_frais_annexes_formate || (totalFA.toLocaleString('fr-FR') + ' FCFA');
          var mtPremiereTranche = Number(d.frais_inscription || 0);
          var mtPremiereTrancheFormate = d.frais_inscription_formate || (mtPremiereTranche.toLocaleString('fr-FR') + ' FCFA');
          var totalFraisInscription = Number(d.total_frais_inscription || (mtPremiereTranche + totalFA));
          var totalFraisInscriptionFormate = d.total_frais_inscription_formate || (totalFraisInscription.toLocaleString('fr-FR') + ' FCFA');

          $('#wiz_summary_total_frais_annexes').text(totalFAFormate);
          $('#wiz_frais_annexes_amount_badge').text(totalFraisInscriptionFormate);
          $('#wiz_frais_annexes_title_text').text('1ère Tranche (' + mtPremiereTrancheFormate + ') + Frais Annexes (' + totalFAFormate + ')');
          $('#wiz_frais_annexes_details_desc').text('Montant total exigible à l\'inscription : ' + totalFraisInscriptionFormate);

          if (totalScolarite > 0) {
            $('#wiz_montant_scolarite').val(totalScolarite);

            var isAffecte = d.affectation_etat === 'affecte';
            var themeColor      = isAffecte ? '#15803D' : '#1E3A5F';
            var themeBorder     = isAffecte ? '#86EFAC' : '#BFDBFE';
            var themeBg         = isAffecte ? '#F0FDF4' : '#EFF6FF';
            var themeBadgeBg    = isAffecte ? '#DCFCE7' : '#DBEAFE';
            var themeBadgeText  = isAffecte ? '#15803D' : '#1D4ED8';
            var themeIconColor  = isAffecte ? '#16A34A' : '#2563EB';

            $('#wiz-class-tuition-box').css({
              'border-color': themeBorder,
              'background': '#FFFFFF'
            });

            $('#wiz_tuition_header_bar').css({
              'background': themeBg,
              'border-color': themeBorder
            });

            var regimeBadge = isAffecte 
              ? ' <span class="badge" style="background:#DCFCE7; color:#15803D; font-size:11.5px; padding:4px 10px; border-radius:6px; border:1.5px solid #86EFAC; font-weight:800; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="check-circle" style="width:13px; height:13px; color:#16A34A;"></i> Régime Affecté</span>' 
              : ' <span class="badge" style="background:#DBEAFE; color:#1D4ED8; font-size:11.5px; padding:4px 10px; border-radius:6px; border:1.5px solid #BFDBFE; font-weight:800; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="user-check" style="width:13px; height:13px; color:#2563EB;"></i> Régime Non Affecté</span>';

            $('#wiz_summary_classe_title').html(d.libelle_classe + regimeBadge);

            $('#wiz_summary_total_scolarite_box').css({
              'background': themeBg,
              'border-color': themeBorder
            });
            $('#wiz_summary_total_scolarite_label').css('color', themeColor);
            $('#wiz_summary_total_scolarite').css('color', themeColor).text(totalScolarite.toLocaleString('fr-FR') + ' FCFA');

            $('#wiz_tranches_calendar_icon').css('color', themeIconColor);
            $('#wiz_tranches_table_header').css('background', themeBg);

            var tranches = d.tranches || [];
            var tbodyHtml = '';
            var sumTranches = 0;

            if (tranches.length > 0) {
              $('#wiz_tranches_count_badge').css({
                'background': themeBadgeBg,
                'color': themeBadgeText,
                'border': '1px solid ' + themeBorder
              }).text(tranches.length + ' tranche(s) configurée(s)').show();

              tranches.forEach(function(tr, idx) {
                var mt = Number(tr.montant_tranche || tr.montant_tranche_num || 0);
                sumTranches += mt;
                var isFirst = (idx === 0);
                var pct = totalScolarite > 0 ? Math.round((mt / totalScolarite) * 100) : 0;
                var dateLimite = tr.date_limite_formatee || (tr.date_limite ? tr.date_limite : 'Non définie');

                var rowBg = isFirst ? (isAffecte ? '#F0FDF4' : '#F8FAFC') : '#FFFFFF';
                tbodyHtml += '<tr style="border-bottom: 1px solid #F1F5F9; background: ' + rowBg + ';">';
                tbodyHtml += '  <td style="padding: 12px 16px; font-weight: 800; color: #64748B;">' + (idx + 1) + '</td>';
                tbodyHtml += '  <td style="padding: 12px 16px;">';
                tbodyHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13.5px;">' + (tr.libelle_tranche || ('Tranche ' + (idx + 1))) + '</div>';
                if (isFirst) {
                  tbodyHtml += '    <span style="background:' + themeBadgeBg + '; color:' + themeBadgeText + '; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; border:1px solid ' + themeBorder + ';">Exigible à l\'inscription</span>';
                }
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: center;">';
                tbodyHtml += '    <span style="background:#F1F5F9; color:#334155; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;">' + pct + '%</span>';
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: center; color: #475569; font-size: 12.5px; font-weight: 600;">';
                tbodyHtml += '    <i data-lucide="calendar" style="width:13px; height:13px; display:inline-block; vertical-align:middle; margin-right:4px; color:#64748B;"></i>' + dateLimite;
                tbodyHtml += '  </td>';
                tbodyHtml += '  <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: ' + themeColor + '; font-size: 14px;">';
                tbodyHtml += '    ' + mt.toLocaleString('fr-FR') + ' FCFA';
                tbodyHtml += '  </td>';
                tbodyHtml += '</tr>';
              });
            } else {
              $('#wiz_tranches_count_badge').css({
                'background': themeBadgeBg,
                'color': themeBadgeText,
                'border': '1px solid ' + themeBorder
              }).text('Paiement Unique');
              sumTranches = totalScolarite;
              tbodyHtml += '<tr>';
              tbodyHtml += '  <td colspan="5" style="padding: 16px; text-align: center; color: #64748B; font-style: italic;">';
              tbodyHtml += '    Aucune tranche intermédiaire configurée pour cette classe. Règlement unique de la scolarité totale : <strong style="color:' + themeColor + ';">' + totalScolarite.toLocaleString('fr-FR') + ' FCFA</strong>';
              tbodyHtml += '  </td>';
              tbodyHtml += '</tr>';
            }

            $('#wiz_tranches_table_body').html(tbodyHtml);
            updateNetScolarite();
            $('#wiz-class-tuition-box').stop(true, true).slideDown(250);
            if (window.lucide) lucide.createIcons();
          } else {
            $('#wiz-class-tuition-box').slideUp(200);
            $('#wiz_montant_scolarite').val(0);
            updateNetScolarite();
          }
        } else {
          $('#wiz-class-tuition-box').slideUp(200);
          $('#wiz_montant_scolarite').val(0);
          updateNetScolarite();
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        $('#wiz-class-tuition-box').slideUp(200);
        $('#wiz_montant_scolarite').val(0);
        updateNetScolarite();
      }
    });
  }

  // Lancement initial de la simulation tarifaire
  refreshClassTuition();

  // Soumission AJAX du formulaire
  $('#form-changer-classe-page').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#btn-submit-change-classe-page');
    var originalHtml = $btn.html();

    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin" style="width:18px;height:18px;display:inline-block;animation:spin 1s linear infinite;"></i> <span>Enregistrement du changement de classe...</span>');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (typeof showToast === 'function') {
            showToast(res.message || 'La classe de l\'étudiant a été modifiée avec succès !', 'success');
          } else if (window.toastr) {
            toastr.success(res.message || 'La classe de l\'étudiant a été modifiée avec succès !');
          }
          var targetUrl = res.redirect || '<?= !empty($encryptedId) ? (RACINE . "etudiant/details/" . $encryptedId) : (RACINE . "etudiant/list") ?>';
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 600);
        } else {
          $btn.prop('disabled', false).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          if (typeof showToast === 'function') {
            showToast(res.message || 'Erreur lors de la modification de la classe', 'error');
          } else if (window.toastr) {
            toastr.error(res.message || 'Erreur lors de la modification de la classe');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var msg = 'Erreur réseau ou serveur lors de la mise à jour';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        if (typeof showToast === 'function') {
          showToast(msg, 'error');
        } else if (window.toastr) {
          toastr.error(msg);
        }
      }
    });
  });
});
</script>

<style>
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.spin {
  animation: spin 1s linear infinite;
}
</style>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
