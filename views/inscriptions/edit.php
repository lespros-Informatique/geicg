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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_inscription']) ? 'Éditer ' : 'Ajouter ' ?> Inscription</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisie des données du module Inscriptions Annuelles</p>
        </div>
        <a href="<?= RACINE ?>inscription/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
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
              </div>
              <div style="font-size: 13px; color: #64748B; margin-top: 3px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span>Matricule : <code id="prev_stu_matricule" style="font-weight: 800; color: #1E3A5F; font-size: 13px; background: #F1F5F9; padding: 2px 8px; border-radius: 4px;">-</code></span>
                <span>&bull; Nationalité : <strong id="prev_stu_nationalite" style="color: #0F172A;">Ivoirienne</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Grille des 3 Blocs d'Informations Détaillées -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
          
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

        </div>

      </div>

      <!-- FORMULAIRE D'INSCRIPTION STANDARD -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>inscription/<?= !empty($item['id_inscription']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_inscription'])): ?>
            <input type="hidden" name="id_inscription" value="<?= $item['id_inscription'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Étudiant <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_etudiant_inscription" style="width: 100%; box-sizing: border-box;" name="etudiant_code" required>
                <option value="">-- Rechercher par nom, matricule ou téléphone --</option>
                <?php foreach($etudiants as $e): ?>
                  <option value="<?= $e['code_etudiant'] ?>" <?= (($item['etudiant_code'] ?? '') == $e['code_etudiant']) ? 'selected' : '' ?>><?= htmlspecialchars($e['matricule_etudiant'] . ' - ' . $e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe d'affectation <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_classe_inscription" style="width: 100%; box-sizing: border-box;" name="classe_code" required>
                <option value="">-- Rechercher / Choisir une classe --</option>
                <?php foreach($classes as $cl): ?>
                  <option value="<?= $cl['code_classe'] ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Scolarité Totale Due (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" id="inp_montant_scolarite" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700; outline: none; transition: border-color 0.2s;" name="montant_scolarite_inscription" value="<?= htmlspecialchars($item['montant_scolarite_inscription'] ?? '') ?>" placeholder="Ex: 650000" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
              <input type="number" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="remise_accordee" value="<?= htmlspecialchars($item['remise_accordee'] ?? '') ?>" placeholder="Ex: 50000">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date d'inscription <span style="color: #EF4444;">*</span></label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="date_inscription" value="<?= htmlspecialchars($item['date_inscription'] ?? date('Y-m-d')) ?>" placeholder="Ex: 2025-10-05" required>
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
      allowClear: true,
      width: '100%'
    });
    $('#sel_classe_inscription').select2({
      placeholder: "-- Rechercher / Choisir une classe --",
      allowClear: true,
      width: '100%'
    });
  }

  // 1. Récupération et affichage dynamique de la fiche synthèse complète de l'étudiant
  function fetchStudentProfile(etudiantCode) {
    if (!etudiantCode) {
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
          } else {
            $('#prev_stu_filiere').text('Non définie');
            $('#prev_stu_niveau').text('Non défini');
            $('#prev_stu_classe').text('Nouvel Inscrit');
            $('#prev_stu_annee_detail').text('Première inscription dans l\'établissement');
            $('#prev_stu_solde').css('color', '#15803D').text('Aucun arriéré (0 FCFA)');
          }

          $('#student-profile-preview-banner').stop(true, true).slideDown(250);
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

  // 2. Auto-suggestion du tarif de scolarité sur sélection de la classe
  function fetchTuitionForClass(classeCode) {
    if (!classeCode) return;

    $.ajax({
      url: '<?= RACINE ?>inscription/getTuitionByClass',
      type: 'GET',
      data: { classe_code: classeCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data && res.data.montant_scolarite) {
          var currentVal = $('#inp_montant_scolarite').val();
          if (!currentVal || currentVal == 0) {
            $('#inp_montant_scolarite').val(res.data.montant_scolarite);
          }
        }
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

  // Chargement initial si un étudiant est pré-sélectionné (ex: mode édition)
  var initStu = $('#sel_etudiant_inscription').val();
  if (initStu) {
    fetchStudentProfile(initStu);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>

