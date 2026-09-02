<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$cycles = (new ModelCycle())->getAll();
$filieres = (new ModelFiliere())->getAll();
$niveaux = (new ModelNiveau())->getAll();
$classes = (new ModelClasse())->getAll();
$salles = (new ModelSalle())->getAll();
$scolarites = (new ModelScolarite())->getAll();
$ues = [];
$matieres = (new ModelMatiere())->getAll();
$semestres = (new ModelSemestre())->getAll();
$etudiants = (new ModelEtudiant())->getAll();
$inscriptions = (new ModelInscription())->getAll();
$typeDepenses = (new ModelTypeDepense())->getAll();
$users = (new ModelUser())->getAll();
$enseignants = (new ModelEnseignant())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_inscription']) ? 'Éditer Inscription' : 'Formulaire de Réinscription Étudiant' ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Réinscription annuelle et affectation de la nouvelle classe pour la session <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? '2025-2026') ?></p>
        </div>
        <a href="<?= RACINE ?>inscription/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux Réinscriptions
        </a>
      </div>

      <!-- ========================================================================= -->
      <!-- BANDEAU PREVIEW / FICHE SYNTHÈSE COMPLÈTE ÉTUDIANT (AFFICHÉ DÈS LA RECHERCHE) -->
      <!-- ========================================================================= -->
      <div id="student-profile-preview-banner" class="card" style="display: none; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 14px; padding: 22px 24px; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(15,23,42,0.06); transition: all 0.3s ease;">
        
        <!-- En-tête : Identité Générale & Avatar -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; border-bottom: 1.5px solid #F1F5F9; padding-bottom: 16px; margin-bottom: 18px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div id="prev_stu_avatar" style="width: 56px; height: 56px; border-radius: 50%; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);">ET</div>
            <div>
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h2 id="prev_stu_nom" style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0;">Nom et Prénoms</h2>
                <span id="prev_stu_badge_sexe" class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 800; font-size: 12px; padding: 3px 8px; border-radius: 6px;">Masculin (M)</span>
                <span id="prev_stu_badge_redoublant" class="badge" style="display: none; background: #FEF2F2; color: #991B1B; font-weight: 800; font-size: 12px; padding: 3px 8px; border-radius: 6px;">Redoublant</span>
              </div>
              <div style="font-size: 13px; color: #64748B; margin-top: 3px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span>Matricule : <code id="prev_stu_matricule" style="font-weight: 800; color: #1E3A5F; font-size: 13px; background: #F1F5F9; padding: 2px 8px; border-radius: 4px;">-</code></span>
                <span>&bull; Nationalité : <strong id="prev_stu_nationalite" style="color: #0F172A;">Ivoirienne</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Grille des 4 Blocs d'Informations Détaillées -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
          
          <!-- Étape / Bloc 1 : Identité & Coordonnées -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="user" style="width: 14px; height: 14px;"></i> Étape 1 : Identité & Coordonnées
            </div>
            <div style="font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 4px;">
              <div><strong>Nom de famille :</strong> <span id="prev_stu_nom_famille">-</span></div>
              <div><strong>Prénoms :</strong> <span id="prev_stu_prenoms">-</span></div>
              <div><strong>Date de naissance :</strong> <span id="prev_stu_naissance">-</span></div>
              <div><strong>Lieu de naissance :</strong> <span id="prev_stu_lieu_naissance">-</span></div>
              <div><strong>Téléphone étudiant :</strong> <span id="prev_stu_contact" style="font-weight: 700; color: #0F172A;">-</span></div>
              <div><strong>Email étudiant :</strong> <span id="prev_stu_email">-</span></div>
              <div><strong>Adresse de résidence :</strong> <span id="prev_stu_residence">-</span></div>
            </div>
          </div>

          <!-- Étape / Bloc 2 : Parents & Tuteurs Légaux -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="users" style="width: 14px; height: 14px;"></i> Étape 2 : Parents & Tuteurs Légaux
            </div>
            <div style="font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 4px;">
              <div><strong>Père / Tuteur :</strong> <span id="prev_stu_parent" style="font-weight: 700;">-</span></div>
              <div><strong>Téléphone :</strong> <span id="prev_stu_parent_tel" style="color: #15803D; font-weight: 700;">-</span></div>
              <div><strong>Profession :</strong> <span id="prev_stu_parent_prof">-</span></div>
              <div id="prev_box_mere" style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #E2E8F0;">
                <div><strong>Nom de la Mère :</strong> <span id="prev_stu_mere">-</span></div>
                <div><strong>Tél. Mère :</strong> <span id="prev_stu_mere_tel" style="color: #15803D; font-weight: 700;">-</span></div>
              </div>
            </div>
          </div>

          <!-- Étape / Bloc 3 : Cursus & Bilan Année Précédente (N-1) -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="history" style="width: 14px; height: 14px;"></i> Étape 3 : Cursus & Bilan N-1
            </div>
            <div id="prev_history_content" style="font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 4px;">
              <div><strong>Filière passée :</strong> <span id="prev_stu_filiere" style="font-weight: 700; color: #1E3A5F;">-</span></div>
              <div><strong>Niveau d'études :</strong> <span id="prev_stu_niveau" style="font-weight: 700;">-</span></div>
              <div><strong>Classe passée :</strong> <span id="prev_stu_classe" style="font-weight: 700;">-</span></div>
              <div><strong>Session & Régime :</strong> <span id="prev_stu_annee_detail">-</span></div>
              <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11.5px; color: #64748B;">Solde restant N-1 :</span>
                <span id="prev_stu_solde" style="font-weight: 800; font-size: 13px; color: #DC2626;">0 FCFA</span>
              </div>
            </div>
          </div>

          <!-- Étape / Bloc 4 : Accessoires & Kits d'Inscription -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="package" style="width: 14px; height: 14px;"></i> Étape 4 : Accessoires & Kits d'Inscription
            </div>
            <div id="prev_accessories_content" style="font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 6px;">
              <div style="font-size: 11.5px; color: #64748B;">Kits & accessoires associés :</div>
              <div id="prev_acc_list" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 2px;">
                <span class="badge" style="background: #E2E8F0; color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 700;">Aucun accessoire</span>
              </div>
            </div>
          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- BANDEAU MODALITÉS & TARIFS DE LA CLASSE (DANS LE PREVIEW BANNER) -->
        <!-- ========================================================================= -->
        <div id="prev_class_tuition_section" style="display: none; margin-top: 18px; padding-top: 16px; border-top: 1.5px solid #F1F5F9;">
          <div style="background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 12px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(22,101,52,0.06);">
            
            <!-- En-tête -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
              <div>
                <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="calculator" style="width: 15px; height: 15px;"></i> Modalités Tarifaires & Échéancier de Scolarité
                </div>
                <div style="font-size: 17px; font-weight: 900; color: #0F172A; margin-top: 4px;" id="prev_modalite_classe_title">-</div>
                <div style="font-size: 13px; font-weight: 700; color: #166534; margin-top: 2px;" id="prev_modalite_filiere_niveau">-</div>
              </div>

              <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="background: #FFFFFF; border: 1.5px solid #86EFAC; padding: 10px 18px; border-radius: 10px; text-align: right; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                  <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase;">Scolarité Totale Due</div>
                  <div style="font-size: 19px; font-weight: 900; color: #166534; margin-top: 2px;" id="prev_modalite_total_scolarite">0 FCFA</div>
                </div>
              </div>
            </div>

            <!-- Titre du tableau des tranches -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-top: 1px dashed #86EFAC; padding-top: 14px;">
              <span style="font-size: 12.5px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="calendar" style="width: 15px; height: 15px;"></i> Détail de Toutes les Tranches de Paiement
              </span>
              <span id="prev_tranches_count_badge" style="background: #DCFCE7; color: #166534; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; border: 1px solid #86EFAC;">
                0 tranche(s)
              </span>
            </div>

            <!-- Tableau transparent et complet de TOUTES les tranches -->
            <div style="overflow-x: auto; background: #FFFFFF; border-radius: 8px; border: 1px solid #BBF7D0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
              <table class="table" style="width: 100%; border-collapse: collapse; margin: 0;">
                <thead>
                  <tr style="background: #F8FAFC; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 10px 14px; border-bottom: 1.5px solid #E2E8F0; width: 50px;">N°</th>
                    <th style="padding: 10px 14px; border-bottom: 1.5px solid #E2E8F0;">Intitulé de la Tranche</th>
                    <th style="padding: 10px 14px; border-bottom: 1.5px solid #E2E8F0; text-align: center; width: 120px;">Part</th>
                    <th style="padding: 10px 14px; border-bottom: 1.5px solid #E2E8F0; text-align: center; width: 180px;">Date Limite</th>
                    <th style="padding: 10px 14px; border-bottom: 1.5px solid #E2E8F0; text-align: right; width: 160px;">Montant</th>
                  </tr>
                </thead>
                <tbody id="prev_modalite_tranches_table_body">
                  <!-- Rempli dynamiquement en JS avec toutes les tranches -->
                </tbody>
                <tfoot>
                  <tr style="background: #F0FDF4; font-weight: 800; border-top: 2px solid #86EFAC;">
                    <td colspan="4" style="padding: 10px 14px; text-align: right; color: #166534; font-size: 12px; text-transform: uppercase;">
                      Total Échéancier :
                    </td>
                    <td style="padding: 10px 14px; text-align: right; color: #166534; font-size: 14px;" id="prev_modalite_tranches_total_sum">
                      0 FCFA
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

          </div>
        </div>

      </div>

      <!-- FORMULAIRE D'INSCRIPTION STANDARD -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form id="form_inscription_main" action="<?= RACINE ?>inscription/<?= !empty($item['id_inscription']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_inscription'])): ?>
            <input type="hidden" name="id_inscription" value="<?= $item['id_inscription'] ?>">
          <?php endif; ?>

          <!-- SÉLECTEUR ÉTUDIANT & STATUT REDOUBLANT (OUI / NON) -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%; margin-bottom: 20px;">
            
            <?php 
              $selectedEtu = $item['etudiant_code'] ?? ($_GET['etudiant_code'] ?? '');
              $isReadonly = !empty($selectedEtu);
            ?>
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Étudiant <span style="color: #EF4444;">*</span> <?= $isReadonly ? '<span style="color: #64748B; font-size: 12px; font-weight: 600;">(Lecture seule)</span>' : '' ?>
              </label>
              <select class="form-control select2 <?= $isReadonly ? 'readonly-select' : '' ?>" id="sel_etudiant_inscription" style="width: 100%; box-sizing: border-box; <?= $isReadonly ? 'background-color: #F1F5F9; color: #334155; pointer-events: none; cursor: not-allowed;' : '' ?>" name="etudiant_code" required>
                <option value="">-- Rechercher par nom, matricule ou téléphone --</option>
                <?php foreach($etudiants as $e): ?>
                  <option value="<?= $e['code_etudiant'] ?>" <?= ($selectedEtu == $e['code_etudiant'] || $selectedEtu == $e['matricule_etudiant']) ? 'selected' : '' ?>><?= htmlspecialchars($e['matricule_etudiant'] . ' - ' . $e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- OPTION REDOUBLANT (OUI / NON) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Statut Académique : Redoublant ? <span style="color: #EF4444;">*</span>
              </label>
              <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; color: #1E3A5F; background: #EFF6FF; border: 1.5px solid #BFDBFE; padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                  <input type="radio" name="is_redoublant" value="0" checked id="radio_passant" style="accent-color: #1E3A5F; cursor: pointer;">
                  <span>Non (Passant - Choisir nouvelle classe)</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13.5px; color: #991B1B; background: #FEF2F2; border: 1.5px solid #FECACA; padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                  <input type="radio" name="is_redoublant" value="1" id="radio_redoublant" style="accent-color: #DC2626; cursor: pointer;">
                  <span>Oui (Redoublant)</span>
                </label>
              </div>
            </div>

          </div>

          <!-- STATUT D'AFFECTATION ÉTAT / RÉGIME -->
          <div class="form-group" style="width: 100%; grid-column: 1 / -1; margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
              Statut d'Affectation État / Régime Étudiant <span style="color: #EF4444;">*</span>
            </label>
            <div style="display: flex; gap: 14px; flex-wrap: wrap;">
              <label class="label-affectation-choice-edit" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1.5px solid #1E3A5F; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #1E3A5F; background: #EFF6FF; transition: all 0.2s;">
                <input type="radio" name="affectation_etat" value="non_affecte" <?= (($item['affectation_etat'] ?? '') !== 'oui' && ($item['affectation_etat'] ?? '') !== 'affecte') ? 'checked' : '' ?> style="accent-color: #1E3A5F; width: 16px; height: 16px;">
                <span>Non Affecté (Privé)</span>
              </label>
              <label class="label-affectation-choice-edit" style="display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1.5px solid #CBD5E1; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #334155; background: #FFFFFF; transition: all 0.2s;">
                <input type="radio" name="affectation_etat" value="affecte" <?= (($item['affectation_etat'] ?? '') === 'oui' || ($item['affectation_etat'] ?? '') === 'affecte') ? 'checked' : '' ?> style="accent-color: #1E3A5F; width: 16px; height: 16px;">
                <span>Affecté (Subventionné par l'État)</span>
              </label>
            </div>
            <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Le montant et l'échéancier des tranches s'ajustent automatiquement selon le régime sélectionné.</small>
          </div>

          <!-- SÉLECTION DE LA CLASSE D'AFFECTATION -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%; margin-bottom: 20px;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                <span id="label_classe_select">Classe d'affectation (Nouvelle Classe)</span> <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="sel_classe_inscription" style="width: 100%; box-sizing: border-box;" name="classe_code" required>
                <option value="">-- Choisir la classe --</option>
                <?php foreach($classes as $cl): ?>
                  <option value="<?= $cl['code_classe'] ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Scolarité Totale Due (FCFA) <span style="color: #64748B; font-size: 12px; font-weight: 600;">(Fixée par barème)</span>
              </label>
              <input type="number" id="inp_montant_scolarite" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; font-weight: 800; pointer-events: none; cursor: not-allowed;" name="montant_scolarite_inscription" value="<?= htmlspecialchars($item['montant_scolarite_inscription'] ?? '') ?>" placeholder="0" readonly required>
            </div>

          </div>

          <!-- REMISE & DATE D'INSCRIPTION -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
              <input type="number" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="remise_accordee" value="<?= htmlspecialchars($item['remise_accordee'] ?? '') ?>" placeholder="Ex: 50000">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Date d'inscription <span style="color: #64748B; font-size: 12px; font-weight: 600;">(Automatique)</span>
              </label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #334155; font-weight: 600; pointer-events: none; cursor: not-allowed;" name="date_inscription" value="<?= htmlspecialchars($item['date_inscription'] ?? date('Y-m-d')) ?>" readonly required>
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer l'Inscription</button>
            <a href="<?= RACINE ?>inscription/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_etudiant_inscription').select2({
      placeholder: "-- Rechercher par nom, matricule ou téléphone --",
      allowClear: !$('#sel_etudiant_inscription').hasClass('readonly-select'),
      width: '100%'
    });

    if ($('#sel_etudiant_inscription').hasClass('readonly-select')) {
      $('#sel_etudiant_inscription').next('.select2-container').css({
        'pointer-events': 'none',
        'cursor': 'not-allowed'
      }).find('.select2-selection').css({
        'background-color': '#F1F5F9',
        'border-color': '#CBD5E1',
        'color': '#334155',
        'cursor': 'not-allowed'
      });
      $('#sel_etudiant_inscription').on('select2:opening', function(e) {
        e.preventDefault();
      });
    }

    $('#sel_classe_inscription').select2({
      placeholder: "-- Choisir la classe --",
      allowClear: true,
      width: '100%'
    });
  }

  var currentStudentData = null;

  // 1. Récupération et affichage dynamique de la fiche synthèse complète de l'étudiant
  function fetchStudentProfile(etudiantCode) {
    if (!etudiantCode) {
      currentStudentData = null;
      $('#student-profile-preview-banner').slideUp(200);
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>inscription/getStudentProfileSummary',
      type: 'GET',
      data: { etudiant_code: etudiantCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          currentStudentData = d;

          var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          $('#prev_stu_avatar').text(initials || 'ET');

          // Étape 1 : Identité & Coordonnées
          $('#prev_stu_nom').text(d.nom_complet);
          $('#prev_stu_nom_famille').text(d.nom_famille || '-');
          $('#prev_stu_prenoms').text(d.prenom || '-');
          $('#prev_stu_matricule').text(d.matricule);
          $('#prev_stu_badge_sexe').text(d.sexe === 'M' ? 'Masculin (M)' : (d.sexe === 'F' ? 'Féminin (F)' : d.sexe));
          $('#prev_stu_naissance').text(d.date_naissance);
          $('#prev_stu_lieu_naissance').text(d.lieu_naissance || 'Non renseigné');
          $('#prev_stu_nationalite').text(d.nationalite || 'Ivoirienne');
          $('#prev_stu_residence').text(d.residence || 'Non renseigné');
          $('#prev_stu_contact').text(d.telephone);
          $('#prev_stu_email').text(d.email);

          // Étape 2 : Parents & Tuteurs
          $('#prev_stu_parent').text(d.parent_nom + ' (' + d.parent_role + ')');
          $('#prev_stu_parent_tel').text(d.parent_tel);
          $('#prev_stu_parent_prof').text(d.parent_profession || 'Non renseignée');
          if (d.nom_mere) {
            $('#prev_stu_mere').text(d.nom_mere);
            $('#prev_stu_mere_tel').text(d.telephone_mere || '-');
            $('#prev_box_mere').show();
          } else {
            $('#prev_box_mere').hide();
          }

          // Étape 3 : Cursus & Situation Antérieure (N-1)
          if (d.has_history) {
            $('#prev_stu_filiere').text(d.derniere_filiere || 'Filière N-1').show();
            $('#prev_stu_niveau').text(d.dernier_niveau || 'Niveau N-1').show();
            $('#prev_stu_classe').text(d.derniere_classe || 'Classe N-1').show();
            $('#prev_stu_annee_detail').text((d.derniere_annee ? 'Session ' + d.derniere_annee + ' • ' : '') + 'Régime : ' + d.prev_regime);
            
            var solde = Number(d.prev_solde || 0);
            if (solde <= 0) {
              $('#prev_stu_solde').css('color', '#15803D').text('Compte Soldé (0 FCFA)');
            } else {
              $('#prev_stu_solde').css('color', '#DC2626').text(solde.toLocaleString('fr-FR') + ' FCFA (Reliquat)');
            }

            // Présélection automatique du régime (Affecté / Non Affecté) selon l'historique
            if (d.prev_affectation_etat === 'affecte') {
              $('input[name="affectation_etat"][value="affecte"]').prop('checked', true);
            } else {
              $('input[name="affectation_etat"][value="non_affecte"]').prop('checked', true);
            }
            $('input[name="affectation_etat"]').each(function() {
              var isChecked = $(this).is(':checked');
              $(this).closest('label').css({
                'border-color': isChecked ? '#1E3A5F' : '#CBD5E1',
                'background': isChecked ? '#EFF6FF' : '#FFFFFF',
                'color': isChecked ? '#1E3A5F' : '#334155'
              });
            });

            // Gestion automatique de l'état Redoublant si applicable
            handleRedoublantState();
          } else {
            $('#prev_stu_badge_redoublant').hide();
            $('#prev_stu_filiere').text('Non définie');
            $('#prev_stu_niveau').text('Non défini');
            $('#prev_stu_classe').text('Nouvel Inscrit');
            $('#prev_stu_annee_detail').text('Première inscription dans l\'établissement');
            $('#prev_stu_solde').css('color', '#15803D').text('Aucun arriéré (0 FCFA)');
          }

          // Étape 4 : Accessoires & Kits d'Inscription
          if (d.accessoires_etudiant && d.accessoires_etudiant.length > 0) {
            var accHtml = '';
            d.accessoires_etudiant.forEach(function(acc) {
              accHtml += '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #BFDBFE; padding:4px 8px; border-radius:6px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#15803D;"></i> ' + acc.libelle_accessoire + '</span> ';
            });
            $('#prev_acc_list').html(accHtml);
          } else {
            $('#prev_acc_list').html('<span class="badge" style="background:#F1F5F9; color:#64748B; padding:4px 8px; border-radius:6px; font-weight:600;">Aucun kit/accessoire souscrit</span>');
          }

          $('#student-profile-preview-banner').stop(true, true).slideDown(250);

          // Si une classe est déjà choisie, charger immédiatement ses tarifs
          var selClass = $('#sel_classe_inscription').val();
          if (selClass) {
            fetchTuitionForClass(selClass);
          }

          if (window.lucide) lucide.createIcons();
        } else {
          $('#student-profile-preview-banner').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement profil étudiant:', err);
        $('#student-profile-preview-banner').slideUp(200);
      }
    });
  }

  // 2. Gestion de l'option Redoublant (Oui / Non)
  function handleRedoublantState() {
    var isRedoublant = $('input[name="is_redoublant"]:checked').val() === '1';

    if (isRedoublant) {
      $('#prev_stu_badge_redoublant').show();
      $('#label_classe_select').text("Classe d'affectation (Classe Redoublée)");

      // Si l'étudiant a une classe précédente enregistrée, la pré-sélectionner
      if (currentStudentData && currentStudentData.derniere_classe_code) {
        $('#sel_classe_inscription').val(currentStudentData.derniere_classe_code).trigger('change');
      }
    } else {
      $('#prev_stu_badge_redoublant').hide();
      $('#label_classe_select').text("Classe d'affectation (Nouvelle Classe)");
    }
  }

  $('input[name="is_redoublant"]').on('change', function() {
    handleRedoublantState();
  });

  // 3. Auto-suggestion et affichage des détails tarifaires de la classe sélectionnée
  function fetchTuitionForClass(classeCode) {
    if (!classeCode) {
      $('#prev_class_tuition_section').slideUp(200);
      return;
    }
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

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

          // Remplissage automatique du champ montant de scolarité
          $('#inp_montant_scolarite').val(totalScolarite);

          var regimeText = d.affectation_etat === 'affecte' ? ' (Affecté État)' : ' (Non Affecté / Privé)';
          var filiereNiveauText = (d.libelle_filiere ? 'Filière : ' + d.libelle_filiere + ' • ' : '') + (d.libelle_niveau ? 'Niveau : ' + d.libelle_niveau : '');

          // Mise à jour de la section Modalités & Tarifs de la Classe dans le Preview Banner
          $('#prev_modalite_classe_title').text(d.libelle_classe + regimeText);
          $('#prev_modalite_filiere_niveau').text(filiereNiveauText);
          $('#prev_modalite_total_scolarite').text(totalScolarite.toLocaleString('fr-FR') + ' FCFA');

          // Rendu dynamique de TOUTES les tranches
          var tranches = d.tranches || [];
          var tbodyHtml = '';
          var sumTranches = 0;

          if (tranches.length > 0) {
            $('#prev_tranches_count_badge').text(tranches.length + ' tranche(s) configurée(s)').show();

            tranches.forEach(function(tr, idx) {
              var mt = Number(tr.montant_tranche || tr.montant_tranche_num || 0);
              sumTranches += mt;
              var isFirst = (idx === 0);
              var pct = totalScolarite > 0 ? Math.round((mt / totalScolarite) * 100) : 0;
              var dateLimite = tr.date_limite_formatee || (tr.date_limite ? tr.date_limite : 'Non définie');

              tbodyHtml += '<tr style="border-bottom: 1px solid #E2E8F0; background: ' + (isFirst ? '#F8FAFC' : '#FFFFFF') + ';">';
              tbodyHtml += '  <td style="padding: 10px 14px; font-weight: 800; color: #64748B;">' + (idx + 1) + '</td>';
              tbodyHtml += '  <td style="padding: 10px 14px;">';
              tbodyHtml += '    <div style="font-weight: 700; color: #0F172A; font-size: 13px;">' + (tr.libelle_tranche || ('Tranche ' + (idx + 1))) + '</div>';
              if (isFirst) {
                tbodyHtml += '    <span style="background:#EFF6FF; color:#1D4ED8; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; border:1px solid #BFDBFE;">Exigible à l\'inscription</span>';
              }
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 10px 14px; text-align: center;">';
              tbodyHtml += '    <span style="background:#F1F5F9; color:#334155; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px;">' + pct + '%</span>';
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 10px 14px; text-align: center; color: #475569; font-size: 12px; font-weight: 600;">';
              tbodyHtml += '    <i data-lucide="calendar" style="width:12px; height:12px; display:inline-block; vertical-align:middle; margin-right:4px; color:#64748B;"></i>' + dateLimite;
              tbodyHtml += '  </td>';
              tbodyHtml += '  <td style="padding: 10px 14px; text-align: right; font-weight: 800; color: #166534; font-size: 13.5px;">';
              tbodyHtml += '    ' + mt.toLocaleString('fr-FR') + ' FCFA';
              tbodyHtml += '  </td>';
              tbodyHtml += '</tr>';
            });
          } else {
            $('#prev_tranches_count_badge').text('Paiement Unique');
            sumTranches = totalScolarite;
            tbodyHtml += '<tr>';
            tbodyHtml += '  <td colspan="5" style="padding: 14px; text-align: center; color: #64748B; font-style: italic;">';
            tbodyHtml += '    Aucune tranche intermédiaire configurée. Règlement unique de la scolarité totale : <strong>' + totalScolarite.toLocaleString('fr-FR') + ' FCFA</strong>';
            tbodyHtml += '  </td>';
            tbodyHtml += '</tr>';
          }

          $('#prev_modalite_tranches_table_body').html(tbodyHtml);
          $('#prev_modalite_tranches_total_sum').text(sumTranches.toLocaleString('fr-FR') + ' FCFA');

          $('#prev_class_tuition_section').stop(true, true).slideDown(250);
          if (window.lucide) lucide.createIcons();
        } else {
          $('#prev_class_tuition_section').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        $('#prev_class_tuition_section').slideUp(200);
      }
    });
  }

  // Événements de sélection
  $('#sel_etudiant_inscription').on('change select2:select', function() {
    var val = $(this).val();
    fetchStudentProfile(val);
  });

  $('#sel_classe_inscription').on('change select2:select', function() {
    var val = $(this).val();
    fetchTuitionForClass(val);
  });

  $('input[name="affectation_etat"]').on('change', function() {
    $('input[name="affectation_etat"]').each(function() {
      var isChecked = $(this).is(':checked');
      $(this).closest('label').css({
        'border-color': isChecked ? '#1E3A5F' : '#CBD5E1',
        'background': isChecked ? '#EFF6FF' : '#FFFFFF',
        'color': isChecked ? '#1E3A5F' : '#334155'
      });
    });
    var currentClass = $('#sel_classe_inscription').val();
    if (currentClass) {
      fetchTuitionForClass(currentClass);
    }
  });

  // Chargement initial automatique via paramètre URL ou valeur pré-sélectionnée
  var urlParams = new URLSearchParams(window.location.search);
  var urlEtudiantCode = urlParams.get('etudiant_code');
  if (urlEtudiantCode && !$('#sel_etudiant_inscription').val()) {
    $('#sel_etudiant_inscription').val(urlEtudiantCode).trigger('change');
  }

  var initStu = $('#sel_etudiant_inscription').val() || urlEtudiantCode;
  if (initStu) {
    fetchStudentProfile(initStu);
  }

  var initClasse = $('#sel_classe_inscription').val();
  if (initClasse) {
    fetchTuitionForClass(initClasse);
  }

  // Soumission unique et sécurisée en AJAX (Évite toute double soumission)
  $('#form_inscription_main').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');

    if ($submitBtn.prop('disabled') || $submitBtn.hasClass('btn-is-loading')) {
      return false;
    }

    loading($submitBtn, true, 'Enregistrement en cours...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          showToast(res.message || 'Réinscription enregistrée avec succès !', 'success');
          setTimeout(function() {
            window.location.href = '<?= RACINE ?>inscription/list';
          }, 1200);
        } else {
          loading($submitBtn, false, 'Enregistrer l\'Inscription');
          showToast(res.message || 'Une erreur est survenue lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        loading($submitBtn, false, 'Enregistrer l\'Inscription');
        var msg = 'Erreur lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        showToast(msg, 'error');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>

