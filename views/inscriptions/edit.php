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
$annees = (new ModelAnnee())->getAll();

if (!isset($globalEtablissementLogo)) {
    try {
        $dbConnLogo = (new Database())->getCon();
        $stmtLogo = $dbConnLogo->query("SELECT logo_etablissement, libelle_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1");
        $etabRowLogo = $stmtLogo ? $stmtLogo->fetch(PDO::FETCH_ASSOC) : null;
        $rawLogo = $etabRowLogo['logo_etablissement'] ?? '';
        $globalEtablissementLogo = (!empty($rawLogo)) ? ((strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/')) : '';
        $globalEtablissementNom = $etabRowLogo['libelle_etablissement'] ?? 'GEICG';
    } catch (Exception $e) {
        $globalEtablissementLogo = '';
        $globalEtablissementNom = 'GEICG';
    }
}
?>
<style>
@media print {
  body {
    background: #FFFFFF !important;
  }
  .app-layout, .sidebar, .main-nav, nav, .page-header, .card, #quitus-financial-status-box, #smart_class_suggestion_hint, form, .btn, .no-print {
    display: none !important;
  }
  
  /* Impression spécifique Fiche Financière Étudiant */
  body.printing-financial-statement #modal_fiche_navette {
    display: none !important;
  }
  body.printing-financial-statement #financial-statement-modal {
    display: block !important;
    position: static !important;
    background: #FFFFFF !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    box-shadow: none !important;
    z-index: 999999 !important;
  }
  body.printing-financial-statement #financial-statement-modal > div {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  body.printing-financial-statement #printable-financial-zone {
    display: block !important;
    visibility: visible !important;
    padding: 20px !important;
  }

  /* Impression spécifique Fiche Navette */
  body:not(.printing-financial-statement) #modal_fiche_navette {
    display: block !important;
    position: static !important;
    background: none !important;
    padding: 0 !important;
    width: 100% !important;
    height: auto !important;
    z-index: 1 !important;
  }
  body:not(.printing-financial-statement) #modal_fiche_navette > div {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  body:not(.printing-financial-statement) #modal_fiche_navette .btn-close-fiche-navette,
  body:not(.printing-financial-statement) #modal_fiche_navette .modal-custom-header,
  body:not(.printing-financial-statement) #modal_fiche_navette .modal-custom-footer,
  body:not(.printing-financial-statement) #modal_fiche_navette .no-print {
    display: none !important;
  }
  body:not(.printing-financial-statement) #printable-voucher-zone {
    display: block !important;
    visibility: visible !important;
    position: static !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 24px !important;
    border: 2px solid #1E3A5F !important;
    box-sizing: border-box !important;
  }
}
.btn-is-loading {
  opacity: 0.7;
  pointer-events: none;
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_inscription']) ? 'Éditer Inscription' : 'Bureau d\'Inscription : Guichet de Réinscription Étudiant' ?></h1>
            <span class="badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 8px;">
              Session <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'en cours') ?>
            </span>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Contrôle du quitus financier N-1, progression académique et émission de la Fiche Navette pour le Bureau des Versements</p>
        </div>
        <a href="<?= RACINE ?>reinscription/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux Réinscriptions
        </a>
      </div>

      <!-- ========================================================================= -->
      <!-- BANDEAU PREVIEW / FICHE SYNTHÈSE COMPLÈTE ÉTUDIANT (AFFICHÉ DÈS LA RECHERCHE) -->
      <!-- ========================================================================= -->
      <!-- ========================================================================= -->
      <!-- FICHE SYNTHÈSE COMPLÈTE ÉTUDIANT (CARTES PAR ÉTAPES ET CARTE PHOTO D'IDENTITÉ) -->
      <!-- ========================================================================= -->
      <div id="student-profile-preview-banner" style="display: none; margin-bottom: 24px; transition: all 0.3s ease;">
        
        <!-- 1. CARTE PHOTO D'IDENTITÉ & PROFIL ÉLÈVE -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.06); margin-bottom: 18px;">
          <!-- Header de la Carte Photo -->
          <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 12px 20px; font-size: 12px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 28px; height: 28px; border-radius: 8px; background: #DBEAFE; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="camera" style="width: 16px; height: 16px; color: #2563EB;"></i>
              </div>
              <span>Photo d'Identité & Identité Élève</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <button type="button" id="btn-quick-print-finance" class="btn btn-sm" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; font-weight: 700; font-size: 11.5px; padding: 4px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="file-text" style="width: 14px; height: 14px; color: #059669;"></i> Imprimer l'État Financier
              </button>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; background: #EDF2F7; padding: 3px 10px; border-radius: 12px;">Identité Académique</span>
            </div>
          </div>

          <!-- Corps de la Carte Photo -->
          <div style="padding: 20px 24px; display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <!-- Boîtier Photo / Avatar -->
            <div style="position: relative; flex-shrink: 0; text-align: center;">
              <div style="width: 84px; height: 84px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 26px; border: 3px solid #CBD5E1; box-shadow: 0 4px 12px rgba(0,0,0,0.12); overflow: hidden; position: relative;">
                <img id="prev_stu_photo_img" src="" alt="Photo d'identité" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                <span id="prev_stu_avatar">ET</span>
              </div>
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.5px;">Photo d'identité</div>
            </div>

            <!-- Détails de l'étudiant -->
            <div style="flex: 1; min-width: 260px;">
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                <h2 id="prev_stu_nom" style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0; letter-spacing: -0.2px;">Nom et Prénoms</h2>
                <span id="prev_stu_badge_sexe" class="badge" style="background: #E0F2FE; color: #0369A1; font-weight: 700; font-size: 11.5px; padding: 4px 10px; border-radius: 20px; border: 1px solid #BAE6FD;">Masculin (M)</span>
                <span id="prev_stu_badge_redoublant" class="badge" style="display: none; background: #FEE2E2; color: #991B1B; font-weight: 800; font-size: 11.5px; padding: 4px 10px; border-radius: 20px; border: 1px solid #FCA5A5;">Redoublant</span>
              </div>

              <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; font-size: 13px; color: #475569; margin-top: 8px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background: #F8FAFC; padding: 4px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
                  <i data-lucide="shield" style="width: 14px; height: 14px; color: #2563EB;"></i>
                  <strong>Matricule :</strong> <code id="prev_stu_matricule" style="font-weight: 800; color: #1E3A5F; font-size: 13px; background: transparent; padding: 0;">-</code>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px; background: #F8FAFC; padding: 4px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
                  <i data-lucide="globe" style="width: 14px; height: 14px; color: #059669;"></i>
                  <strong>Nationalité :</strong> <span id="prev_stu_nationalite" style="font-weight: 700; color: #0F172A;">Ivoirienne</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. GRILLE DES CARTES D'INFORMATIONS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px;">
          
          <!-- Étape / Carte 1 : Identité & Coordonnées -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);">
            <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 12px 16px; font-size: 11.5px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
              <div style="width: 26px; height: 26px; border-radius: 6px; background: #DBEAFE; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="user" style="width: 14px; height: 14px; color: #2563EB;"></i>
              </div>
              <span>Identité & Coordonnées</span>
            </div>
            <div style="padding: 16px 18px; font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 8px;">
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Nom de famille :</span> <span id="prev_stu_nom_famille" style="font-weight: 700; color: #0F172A;">-</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Prénoms :</span> <span id="prev_stu_prenoms" style="font-weight: 700; color: #0F172A;">-</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Né(e) le :</span> <span id="prev_stu_naissance" style="font-weight: 700;">-</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Lieu de naissance :</span> <span id="prev_stu_lieu_naissance" style="font-weight: 600;">-</span></div>
              <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 4px; border-top: 1px dashed #E2E8F0;">
                <span style="color: #64748B;">Téléphone :</span> 
                <span id="prev_stu_contact" style="font-weight: 800; color: #047857; background: #ECFDF5; padding: 2px 8px; border-radius: 6px; border: 1px solid #A7F3D0;">-</span>
              </div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Email :</span> <span id="prev_stu_email" style="font-weight: 600; color: #2563EB;">-</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Résidence :</span> <span id="prev_stu_residence" style="font-weight: 600;">-</span></div>
            </div>
          </div>

          <!-- Étape / Carte 2 : Cursus & Bilan Antérieur -->
          <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);">
            <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 12px 16px; font-size: 11.5px; font-weight: 800; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
              <div style="width: 26px; height: 26px; border-radius: 6px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="history" style="width: 14px; height: 14px; color: #D97706;"></i>
              </div>
              <span>Cursus & Bilan Antérieur</span>
            </div>
            <div id="prev_history_content" style="padding: 16px 18px; font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 8px;">
              <div><span style="color: #64748B;">Filière passée :</span> <span id="prev_stu_filiere" style="font-weight: 700; color: #1E3A5F; display: block; margin-top: 2px;">-</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Niveau d'études :</span> <span id="prev_stu_niveau" style="font-weight: 700;">-</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="color: #64748B;">Classe passée :</span> <span id="prev_stu_classe" style="font-weight: 700;">-</span></div>
              <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><span id="prev_stu_annee_detail">-</span></div>
              <div style="margin-top: 6px; padding: 8px 12px; background: #FEF2F2; border-radius: 8px; border: 1px solid #FCA5A5; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11.5px; font-weight: 700; color: #991B1B;">Solde restant antérieur :</span>
                <span id="prev_stu_solde" style="font-weight: 900; font-size: 13.5px; color: #DC2626;">0 FCFA</span>
              </div>
            </div>
          </div>

        </div>

      </div>

        <!-- ========================================================================= -->
        <!-- BANDEAU QUITUS FINANCIER N-1 (CONTRÔLE BUREAU SCOLARITÉ / CAISSE) -->
        <!-- ========================================================================= -->
        <div id="quitus-financial-status-box" style="display: none; margin-top: 18px; padding: 16px 20px; border-radius: 10px; border: 1.5px solid transparent; transition: all 0.3s ease;">
          <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
              <div id="quitus_icon_box" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                <i id="quitus_icon" data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
              </div>
              <div>
                <div id="quitus_title" style="font-weight: 800; font-size: 15px; margin-bottom: 2px;">Statut du Quitus Financier N-1</div>
                <div id="quitus_desc" style="font-size: 13px; line-height: 1.45;"></div>
              </div>
            </div>
            <div id="quitus_badge_box"></div>
          </div>

          <!-- ZONE DE DÉROGATION ADMINISTRATIVE SI ARRIÉRÉS DÉTECTÉS (SOUMIS À PERMISSION RBAC) -->
          <?php $canDerogate = $canDerogate ?? true; ?>
          <div id="quitus_derogation_zone" style="display: none; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #FECACA; background: #FFF5F5; padding: 14px 16px; border-radius: 8px; border: 1px solid #FEE2E2;">
            <?php if (!empty($canDerogate)): ?>
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #991B1B; margin: 0;">
                <input type="checkbox" name="derogation_arriere" value="1" id="chk_derogation_arriere" style="width: 18px; height: 18px; accent-color: #DC2626; cursor: pointer;">
                <span>Dérogation Administrative : Autoriser la réinscription sous réserve d'apurement des arriérés au Bureau des Versements</span>
              </label>
              <div id="derogation_motif_container" style="display: none; margin-top: 10px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Motif de la dérogation / Référence du moratoire ou accord :</label>
                <input type="text" name="derogation_motif" id="inp_derogation_motif" class="form-control" style="font-size: 12.5px; padding: 8px 12px; border-radius: 6px;" placeholder="Ex: Accord Direction Générale / Engagement écrit de paiement échelonné">
              </div>
            <?php else: ?>
              <div style="font-size: 12.5px; color: #991B1B; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="lock" style="width: 16px; height: 16px; color: #DC2626;"></i>
                <span>Dérogation Administrative : Privilège restreint (permission <code>MANAGE_DEROGATION_INSCRIPTION</code> requise pour accorder une dérogation).</span>
              </div>
            <?php endif; ?>
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

          <!-- ALERTE SI ÉTUDIANT DÉJÀ INSCRIT POUR L'ANNÉE EN COURS -->
          <div id="already_registered_warning" style="display: none; background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; color: #991B1B;">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
              <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #DC2626; flex-shrink: 0; margin-top: 2px;"></i>
              <div>
                <strong style="font-size: 14px; display: block; margin-bottom: 2px;">Étudiant Déjà Inscrit pour la Session Active</strong>
                <span id="already_registered_warning_text" style="font-size: 13px; line-height: 1.4;"></span>
              </div>
            </div>
          </div>

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

          <!-- ANNÉE ACADÉMIQUE D'INSCRIPTION -->
          <div class="form-group" style="width: 100%; box-sizing: border-box; margin-bottom: 20px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
              Année Académique d'Inscription <span style="color: #EF4444;">*</span>
            </label>
            <select class="form-control select2" id="sel_annee_inscription" style="width: 100%; box-sizing: border-box;" name="annee_code" required>
              <?php 
                $selectedAnneeIns = $item['annee_code'] ?? ($_SESSION['annee_active_code'] ?? '');
                foreach($annees as $a): 
              ?>
                <option value="<?= $a['code_annee'] ?>" <?= ($selectedAnneeIns == $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= (!empty($a['est_active']) || ($a['statut_annee'] ?? '') === 'actif') ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- SÉLECTION DE LA CLASSE D'AFFECTATION (PLEINE LARGEUR) -->
          <div class="form-group" style="width: 100%; box-sizing: border-box; margin-bottom: 20px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
              <span id="label_classe_select">Classe d'affectation (Nouvelle Classe)</span> <span style="color: #EF4444;">*</span>
            </label>
            <select class="form-control select2" id="sel_classe_inscription" style="width: 100%; box-sizing: border-box;" name="classe_code" required>
              <option value="">-- Choisir la classe --</option>
              <?php foreach($classes as $cl): ?>
                <option value="<?= $cl['code_classe'] ?>" data-annee="<?= htmlspecialchars($cl['annee_code'] ?? '') ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
            <div id="smart_class_suggestion_hint" style="display: none; font-size: 12.5px; font-weight: 700; color: #15803D; background: #DCFCE7; border: 1px solid #86EFAC; padding: 8px 12px; border-radius: 6px; margin-top: 6px;">
              <i data-lucide="sparkles" style="width: 15px; height: 15px; vertical-align: -2px; display: inline-block;"></i>
              <span id="smart_class_suggestion_text"></span>
            </div>
            <div id="no_tuition_warning" style="display: none; font-size: 13px; font-weight: 700; color: #991B1B; background: #FEF2F2; border: 1.5px solid #FCA5A5; padding: 10px 14px; border-radius: 8px; margin-top: 8px;">
              <i data-lucide="alert-octagon" style="width: 16px; height: 16px; vertical-align: -3px; display: inline-block; color: #DC2626;"></i>
              <span id="no_tuition_warning_text">Aucun tarif de scolarité actif n'est enregistré pour cette classe sous le régime sélectionné. L'inscription est impossible tant que la scolarité n'est pas paramétrée dans le module Finance.</span>
            </div>
          </div>

          <!-- SCOLARITÉ DUE ET DATE D'INSCRIPTION (2 COLONNES) -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 20px;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Scolarité Totale Due (FCFA) <span style="color: #64748B; font-size: 12px; font-weight: 600;">(Fixée par barème)</span>
              </label>
              <input type="number" id="inp_montant_scolarite" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; font-weight: 800; pointer-events: none; cursor: not-allowed;" name="montant_scolarite_inscription" value="<?= htmlspecialchars($item['montant_scolarite_inscription'] ?? '') ?>" placeholder="0" readonly required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Date d'inscription <span style="color: #64748B; font-size: 12px; font-weight: 600;">(Automatique)</span>
              </label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #334155; font-weight: 600; pointer-events: none; cursor: not-allowed;" name="date_inscription" value="<?= htmlspecialchars($item['date_inscription'] ?? date('Y-m-d')) ?>" readonly required>
            </div>

          </div>

          <!-- REMISE / BOURSE ACCORDÉE (PLEINE LARGEUR) -->
          <div class="form-group" style="width: 100%; box-sizing: border-box; margin-bottom: 20px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Remise / Bourse Accordée (FCFA)</label>
            <input type="number" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #64748B; font-weight: 600; pointer-events: none; cursor: not-allowed;" name="remise_accordee" value="<?= htmlspecialchars($item['remise_accordee'] ?? '0') ?>" placeholder="0" readonly>
          </div>



          <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; align-items: center; flex-wrap: wrap;">
            <button type="submit" id="btn_submit_inscription" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 11px 26px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
              <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i> Valider la Réinscription & Émettre Fiche Navette
            </button>
            <a href="<?= RACINE ?>reinscription/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
            <span id="submit_block_notice" style="display: none; font-size: 12.5px; font-weight: 700; color: #DC2626;"></span>
          </div>
        </form>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL DE SUCCÈS & FICHE NAVETTE / BON DE VERSEMENT POUR LA CAISSE -->
    <!-- ========================================================================= -->
    <div id="modal_fiche_navette" style="display: none; position: fixed; inset: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 99999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box; overflow-y: auto;">
      <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 820px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); overflow: hidden; border: 1px solid #CBD5E1; margin: auto; display: flex; flex-direction: column; max-height: 92vh; position: relative;">
        
        <div class="modal-custom-header" style="background: #1E3A5F; color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="check-circle" style="width: 22px; height: 22px; color: #4ADE80;"></i>
            </div>
            <div>
              <h5 style="font-size: 16.5px; font-weight: 800; margin: 0; color: #FFFFFF;">Réinscription Administrative Validée avec Succès</h5>
              <span style="font-size: 12px; color: #CBD5E1;">Fiche Navette émise par le Bureau d'Inscription pour le Bureau des Versements</span>
            </div>
          </div>
          <button type="button" class="btn-close-fiche-navette" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; width: 34px; height: 34px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'" title="Fermer la fenêtre">
            <i data-lucide="x" style="width: 20px; height: 20px;"></i>
          </button>
        </div>

        <div class="modal-custom-body" style="padding: 24px; overflow-y: auto; flex-grow: 1;">
          
          <!-- CADRE D'INSTRUCTION BUREAU DÉTACHÉ -->
          <div class="no-print" style="background: #EFF6FF; border: 1.5px solid #BFDBFE; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px;">
            <i data-lucide="info" style="width: 20px; height: 20px; color: #1D4ED8; flex-shrink: 0; margin-top: 2px;"></i>
            <div style="font-size: 13px; color: #1E3A5F; line-height: 1.45;">
              <strong>Procédure Services Détachés :</strong> Le dossier administratif est validé. L'étudiant doit maintenant se présenter au <strong>Bureau des Versements (Caisse)</strong> muni de cette <strong>Fiche Navette</strong> pour régler ses droits de réinscription et obtenir son reçu de paiement.
            </div>
          </div>

          <!-- FICHE NAVETTE IMPRIMABLE (Zone imprimée) -->
          <div id="printable-voucher-zone" style="background: #FFFFFF; border: 2px dashed #94A3B8; border-radius: 12px; padding: 24px; position: relative;">
            
            <!-- En-tête  du Bon de versement -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1E3A5F; padding-bottom: 14px; margin-bottom: 18px;">
              <div style="display: flex; align-items: center; gap: 14px;">
                <?php if (!empty($globalEtablissementLogo)): ?>
                  <img src="<?= htmlspecialchars($globalEtablissementLogo) ?>" alt="Logo" style="max-height: 52px; width: auto; object-fit: contain; flex-shrink: 0;">
                <?php endif; ?>
                <div>
                  <div style="font-size: 18px; font-weight: 900; color: #1E3A5F; letter-spacing: 0.5px;">GROUPE EICG - ADMINISTRATION</div>
                  <div style="font-size: 11.5px; color: #64748B; font-weight: 600;">SERVICE SCOLARITÉ • BUREAU DES ADMISSIONS</div>
                  <div style="font-size: 11px; color: #15803D; font-weight: 700; margin-top: 2px;">Session Académique : <span id="v_annee_libelle">-</span></div>
                </div>
              </div>
              <div style="text-align: right;">
                <span style="display: inline-block; background: #1E3A5F; color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 6px; letter-spacing: 0.5px;">BON DE VERSEMENT N°</span>
                <div id="v_code_inscription" style="font-size: 17px; font-weight: 900; color: #1E3A5F; margin-top: 4px; font-family: monospace;">INS-XXXXXXXX</div>
                <div id="v_date_inscription" style="font-size: 11px; color: #64748B; margin-top: 2px;">-</div>
              </div>
            </div>

            <!-- Détails Étudiant -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; margin-bottom: 16px;">
              <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 13px;">
                <div><strong>Matricule Permanent :</strong> <span id="v_matricule" style="font-weight: 800; color: #1E3A5F;">-</span></div>
                <div><strong>Téléphone :</strong> <span id="v_telephone">-</span></div>
                <div style="grid-column: 1 / -1;"><strong>Nom et Prénoms :</strong> <span id="v_nom_complet" style="font-weight: 800; font-size: 14px; color: #0F172A;">-</span></div>
                <div style="grid-column: 1 / -1;"><strong>Classe d'affectation :</strong> <span id="v_classe" style="font-weight: 800; color: #1E3A5F;">-</span></div>
                <div><strong>Régime :</strong> <span id="v_regime" style="font-weight: 700;">-</span></div>
                <div><strong>Agent Scolarité :</strong> <span id="v_agent">-</span></div>
              </div>
            </div>

            <!-- Montants à Régler à la Caisse -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-bottom: 18px;">
              <div style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 12px 16px;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Scolarité Annuelle Prévue</div>
                <div id="v_scolarite_totale" style="font-size: 18px; font-weight: 900; color: #0F172A; margin-top: 4px;">0 FCFA</div>
                <div id="v_arrieres_box" style="display: none; font-size: 11.5px; font-weight: 700; color: #DC2626; margin-top: 4px;">
                  + Arriérés N-1 : <span id="v_arrieres_montant">0 FCFA</span>
                </div>
              </div>
              <div style="background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 8px; padding: 12px 16px;">
                <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase;">Montant Exigible à la Caisse</div>
                <div id="v_tranche1_montant" style="font-size: 20px; font-weight: 900; color: #15803D; margin-top: 4px;">0 FCFA</div>
                <div id="v_tranche1_libelle" style="font-size: 11px; font-weight: 700; color: #166534; margin-top: 4px;">Tranche 1 / Droit de réinscription</div>
              </div>
            </div>

            <!-- Signatures / Visa -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 20px; padding-top: 14px; border-top: 1px solid #CBD5E1; font-size: 12px;">
              <div>
                <div style="font-weight: 700; color: #475569; margin-bottom: 35px;">Visa & Cachet du Bureau d'Inscription :</div>
                <div style="font-size: 11px; color: #64748B; font-style: italic;">Document généré électroniquement</div>
              </div>
              <div style="text-align: right;">
                <div style="font-weight: 700; color: #475569; margin-bottom: 35px;">Émargement / Quittance Caisse :</div>
                <div style="font-size: 11px; color: #64748B; font-style: italic;">Réservé au Bureau des Versements</div>
              </div>
            </div>

          </div>

        </div>

        <!-- Boutons d'Action Modal -->
        <div class="modal-custom-footer" style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; flex-shrink: 0;">
          <a href="<?= RACINE ?>reinscription/list" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; background: #64748B; color: #FFFFFF; border: none; text-decoration: none;">
            <i data-lucide="list" style="width: 16px; height: 16px;"></i> Retour au registre
          </a>

          <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" id="btn-print-financial-statement" class="btn btn-outline-success" style="border: 1.5px solid #059669; color: #047857; background: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="file-text" style="width: 18px; height: 18px; color: #059669;"></i> Imprimer l'État Financier
            </button>
            <button type="button" id="btn-print-voucher" class="btn btn-outline-primary" style="border: 1.5px solid #1E3A5F; color: #1E3A5F; background: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
              <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer la Fiche Navette
            </button>
            <a id="btn-goto-caisse" href="<?= RACINE ?>paiement/formulaire" class="btn btn-success" style="background: #15803D; border: 1px solid #15803D; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
              <i data-lucide="arrow-right-circle" style="width: 18px; height: 18px;"></i> Passer au Bureau des Versements (Caisse)
            </a>
          </div>
        </div>

      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL & MODÈLE IMPRIMABLE : FICHE DE SITUATION FINANCIÈRE ÉTUDIANT -->
    <!-- ========================================================================= -->
    <div id="financial-statement-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); justify-content: center; align-items: center; padding: 20px; overflow-y: auto;">
      <div style="background: #FFFFFF; border-radius: 16px; max-width: 850px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
        
        <!-- En-tête Modal (Non Imprimé) -->
        <div class="no-print" style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
          <div style="font-weight: 800; font-size: 15px; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="file-text" style="width: 18px; height: 18px; color: #059669;"></i>
            <span>Fiche de Situation Financière Élève</span>
          </div>
          <div style="display: flex; gap: 10px;">
            <button type="button" onclick="printFinancialStatement()" class="btn btn-success" style="background: #059669; border: none; color: #FFF; font-weight: 700; border-radius: 8px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
              <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Lancer l'impression
            </button>
            <button type="button" onclick="$('#financial-statement-modal').fadeOut(200)" class="btn btn-secondary" style="font-weight: 700; border-radius: 8px; padding: 8px 14px; cursor: pointer;">
              Fermer
            </button>
          </div>
        </div>

        <!-- Zone Imprimable : Relevé de Compte & Fiche Financière -->
        <div id="printable-financial-zone" style="padding: 28px 32px; overflow-y: auto; background: #FFFFFF; color: #0F172A; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
          
          <!-- En-tête  EICG -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #1E3A5F; padding-bottom: 16px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 16px;">
              <?php if (!empty($globalEtablissementLogo)): ?>
                <img src="<?= htmlspecialchars($globalEtablissementLogo) ?>" alt="Logo" style="max-height: 56px; width: auto; object-fit: contain; flex-shrink: 0;">
              <?php endif; ?>
              <div>
                <div style="font-size: 18px; font-weight: 900; color: #1E3A5F; letter-spacing: 0.5px;">GROUPE EICG - DIRECTION FINANCIÈRE</div>
                <div style="font-size: 11.5px; color: #64748B; font-weight: 700; margin-top: 2px;">SERVICE COMPTABILITÉ & RECOUVREMENT • RELEVÉ DE COMPTE ÉTUDIANT</div>
                <div style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 3px;">Date d'édition : <span id="fin_doc_date">-</span></div>
              </div>
            </div>
            <div style="text-align: right;">
              <span style="display: inline-block; background: #1E3A5F; color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 6px; letter-spacing: 0.5px;">SITUATION FINANCIÈRE</span>
              <div style="font-size: 11px; color: #64748B; margin-top: 4px;">Session Académique : <strong id="fin_session_libelle" style="color: #1E3A5F;">-</strong></div>
            </div>
          </div>

          <!-- Bloc Cartouche Étudiant (Photo + Identité) -->
          <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 18px 22px; margin-bottom: 22px; display: flex; align-items: center; gap: 22px;">
            <!-- Boîtier Photo -->
            <div style="width: 80px; height: 80px; border-radius: 12px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 26px; border: 2.5px solid #CBD5E1; overflow: hidden; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
              <img id="fin_stu_photo_img" src="" alt="Photo" style="display: none; width: 100%; height: 100%; object-fit: cover;">
              <span id="fin_stu_avatar">ET</span>
            </div>

            <!-- Détails de l'étudiant -->
            <div style="flex: 1; min-width: 0;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap;">
                <div>
                  <h2 id="fin_stu_nom" style="font-size: 18px; font-weight: 900; color: #0F172A; margin: 0;">-</h2>
                  <div style="font-size: 12.5px; color: #475569; margin-top: 4px;">
                    Matricule : <strong id="fin_stu_matricule" style="color: #1E3A5F; font-size: 13px;">-</strong> &bull; 
                    Nationalité : <span id="fin_stu_nationalite" style="font-weight: 700;">-</span>
                  </div>
                </div>
                <div style="text-align: right;">
                  <span id="fin_stu_regime_badge" class="badge" style="background: #DBEAFE; color: #1E3A5F; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 6px;">Affecté (État)</span>
                </div>
              </div>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; margin-top: 10px; padding-top: 8px; border-top: 1px dashed #CBD5E1; font-size: 12px;">
                <div><strong>Classe :</strong> <span id="fin_stu_classe" style="font-weight: 700; color: #1E3A5F;">-</span></div>
                <div><strong>Téléphone :</strong> <span id="fin_stu_contact" style="font-weight: 700; color: #047857;">-</span></div>
                <div id="fin_stu_parent_box"><strong>Parent / Tuteur :</strong> <span id="fin_stu_parent" style="font-weight: 600;">-</span></div>
              </div>
            </div>
          </div>

          <!-- Synthèse des Totaux Financiers (Bilan 3 Cartes) -->
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 22px;">
            <div style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 10px; padding: 14px; text-align: center;">
              <div style="font-size: 10.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Scolarité Totale Prévue</div>
              <div id="fin_scolarite_totale" style="font-size: 18px; font-weight: 900; color: #0F172A; margin-top: 4px;">0 FCFA</div>
            </div>
            <div style="background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 10px; padding: 14px; text-align: center;">
              <div style="font-size: 10.5px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Total Règlement Encaissements</div>
              <div id="fin_total_paye" style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 4px;">0 FCFA</div>
            </div>
            <div id="fin_solde_box_card" style="background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 10px; padding: 14px; text-align: center;">
              <div style="font-size: 10.5px; font-weight: 800; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">Solde Restant Dû</div>
              <div id="fin_solde_restant" style="font-size: 18px; font-weight: 900; color: #DC2626; margin-top: 4px;">0 FCFA</div>
            </div>
          </div>

          <!-- Alerte Reliquat Antérieur si présent -->
          <div id="fin_arrieres_alert_box" style="display: none; background: #FFF5F5; border: 1px solid #FEE2E2; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; font-size: 12px; color: #991B1B;">
            <i data-lucide="alert-triangle" style="width: 14px; height: 14px; color: #DC2626; vertical-align: middle; margin-right: 6px;"></i>
            <strong>Reliquat Antérieur :</strong> L'étudiant enregistre un solde restant impayé de <strong id="fin_arrieres_montant">0 FCFA</strong> sur les exercices antérieurs.
          </div>

          <!-- Tableau des Règlements Effectués (Journal des Reçus) -->
          <div style="margin-bottom: 22px;">
            <div style="font-size: 12px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
              <span>Historique & Journal des Paiements Effectués</span>
              <span id="fin_paiements_count" style="font-size: 11px; font-weight: 700; color: #64748B;">0 règlement(s)</span>
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
              <thead>
                <tr style="background: #F1F5F9; color: #334155; font-size: 11px; font-weight: 800; text-transform: uppercase; border-top: 1.5px solid #CBD5E1; border-bottom: 1.5px solid #CBD5E1;">
                  <th style="padding: 8px 10px; text-align: left;">N° Reçu / Code</th>
                  <th style="padding: 8px 10px; text-align: left;">Date</th>
                  <th style="padding: 8px 10px; text-align: left;">Mode</th>
                  <th style="padding: 8px 10px; text-align: left;">Référence</th>
                  <th style="padding: 8px 10px; text-align: right;">Montant Versé</th>
                </tr>
              </thead>
              <tbody id="fin_payments_table_body">
                <tr>
                  <td colspan="5" style="padding: 12px; text-align: center; color: #64748B; font-style: italic;">Aucun paiement enregistré pour cet étudiant.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Signatures & Visas  -->
          <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px; padding-top: 16px; border-top: 1px solid #CBD5E1; font-size: 11.5px;">
            <div>
              <div style="font-weight: 800; color: #334155; margin-bottom: 40px;">Le Service Scolarité / Comptabilité :</div>
              <div style="font-size: 10.5px; color: #64748B; font-style: italic;">Signature & Cachet</div>
            </div>
            <div style="text-align: right;">
              <div style="font-weight: 800; color: #334155; margin-bottom: 40px;">Émargement Étudiant / Parent :</div>
              <div style="font-size: 10.5px; color: #64748B; font-style: italic;">Mention "Lu et approuvé"</div>
            </div>
          </div>

        </div>

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
      data: { 
        etudiant_code: etudiantCode,
        annee_code: $('#sel_annee_inscription').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          currentStudentData = d;

          var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          if (d.photo_url) {
            $('#prev_stu_photo_img').off('error').on('error', function() {
              $(this).hide();
              $('#prev_stu_avatar').text(initials || 'ET').show();
            }).attr('src', d.photo_url).show();
            $('#prev_stu_avatar').hide();
          } else {
            $('#prev_stu_photo_img').hide();
            $('#prev_stu_avatar').text(initials || 'ET').show();
          }

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
            $('#prev_stu_filiere').text(d.derniere_filiere || 'Filière antérieure').show();
            $('#prev_stu_niveau').text(d.dernier_niveau || 'Niveau antérieur').show();
            $('#prev_stu_classe').text(d.derniere_classe || 'Classe antérieure').show();
            $('#prev_stu_annee_detail').text((d.derniere_annee ? 'Session ' + d.derniere_annee + ' • ' : '') + 'Régime : ' + d.prev_regime);
            
            var solde = Number(d.prev_solde || 0);
            if (solde <= 0) {
              $('#prev_stu_solde').css('color', '#15803D').text('Compte Soldé (0 FCFA)');
              // Quitus Financier Antérieur Validé
              $('#quitus-financial-status-box').css({
                'background': '#F0FDF4',
                'border-color': '#86EFAC'
              }).show();
              $('#quitus_icon_box').css('background', '#DCFCE7');
              $('#quitus_icon').attr('data-lucide', 'shield-check').css('color', '#15803D');
              $('#quitus_title').css('color', '#166534').text('✅ Quitus Financier Antérieur Validé');
              $('#quitus_desc').css('color', '#166534').text('L\'étudiant est entièrement à jour de ses règlements sur les sessions précédentes. Aucune dette antérieure enregistrée.');
              $('#quitus_badge_box').html('<span class="badge" style="background:#15803D; color:#FFFFFF; padding:6px 12px; border-radius:6px; font-weight:800; font-size:12px;">Quitus Accordé</span>');
              $('#quitus_derogation_zone').hide();
              $('#submit_block_notice').hide();
              $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
            } else {
              $('#prev_stu_solde').css('color', '#DC2626').text(solde.toLocaleString('fr-FR') + ' FCFA (Reliquat)');
              // Arriérés détectés : Blocage par défaut avec dérogation
              $('#quitus-financial-status-box').css({
                'background': '#FEF2F2',
                'border-color': '#FECACA'
              }).show();
              $('#quitus_icon_box').css('background', '#FEE2E2');
              $('#quitus_icon').attr('data-lucide', 'alert-octagon').css('color', '#DC2626');
              $('#quitus_title').css('color', '#991B1B').text('⚠️ Alerte Financière : Arriérés Antérieurs Détectés (' + solde.toLocaleString('fr-FR') + ' FCFA)');
              $('#quitus_desc').css('color', '#991B1B').html('L\'étudiant présente un reliquat impayé de <strong>' + solde.toLocaleString('fr-FR') + ' FCFA</strong> sur la session précédente (' + (d.derniere_annee || 'antérieure') + '). La réinscription normale requiert la régularisation préalable au <strong>Bureau des Versements</strong>.');
              $('#quitus_badge_box').html('<span class="badge" style="background:#DC2626; color:#FFFFFF; padding:6px 12px; border-radius:6px; font-weight:800; font-size:12px;">Solde Débiteur</span>');
              $('#quitus_derogation_zone').show();

              // Si dérogation non cochée, bloquer le bouton de validation
              updateSubmitButtonQuitusState();
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



            // Gestion automatique de l'état Redoublant / Passant avec suggestion intelligente
            handleRedoublantState();
          } else {
            $('#prev_stu_badge_redoublant').hide();
            $('#prev_stu_filiere').text('Non définie');
            $('#prev_stu_niveau').text('Non défini');
            $('#prev_stu_classe').text('Nouvel Inscrit');
            $('#prev_stu_annee_detail').text('Première inscription dans l\'établissement');
            $('#prev_stu_solde').css('color', '#15803D').text('Aucun arriéré (0 FCFA)');
            $('#quitus-financial-status-box').hide();
            $('#smart_class_suggestion_hint').hide();
            $('#submit_block_notice').hide();
            $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
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

          // Vérification si déjà inscrit pour cette session en cours (en mode ajout)
          var isEditMode = <?= !empty($item['id_inscription']) ? 'true' : 'false' ?>;
          if (d.is_already_registered_this_year && !isEditMode) {
            $('#already_registered_warning_text').html(
              'L\'étudiant <strong>' + (d.nom_complet || '') + '</strong> est déjà réinscrit pour la session active (<strong>' + (d.already_registered_annee || 'active') + '</strong>) dans la classe <strong>' + (d.already_registered_classe || '-') + '</strong> (Réf Inscription : <code>' + (d.already_registered_code || '-') + '</code>).<br>Une double réinscription pour cette même année n\'est pas autorisée.'
            );
            $('#already_registered_warning').stop(true, true).slideDown(250);
            $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
          } else {
            $('#already_registered_warning').slideUp(200);
            updateSubmitButtonQuitusState();
          }

          $('#student-profile-preview-banner').stop(true, true).slideDown(250);

          // Si une classe est déjà choisie, charger immédiatement ses tarifs
          var selClass = $('#sel_classe_inscription').val();
          if (selClass) {
            fetchTuitionForClass(selClass);
          }

          if (window.lucide) lucide.createIcons();
        } else {
          $('#already_registered_warning').slideUp(200);
          $('#student-profile-preview-banner').slideUp(200);
          $('#quitus-financial-status-box').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement profil étudiant:', err);
        $('#student-profile-preview-banner').slideUp(200);
      }
    });
  }

  var isTuitionValid = true;

  // Contrôle de l'état du bouton selon le Quitus, la scolarité et la dérogation
  function updateSubmitButtonQuitusState() {
    if (!isTuitionValid) {
      $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
      $('#submit_block_notice').html('<i data-lucide="alert-octagon" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> Aucun tarif de scolarité configuré pour cette classe').show();
      if (window.lucide) lucide.createIcons();
      return;
    }
    if (!currentStudentData) return;
    var isEditMode = <?= !empty($item['id_inscription']) ? 'true' : 'false' ?>;
    if (currentStudentData.is_already_registered_this_year && !isEditMode) {
      $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
      return;
    }

    var solde = Number(currentStudentData.prev_solde || 0);
    if (solde > 0) {
      var isDerogationChecked = $('#chk_derogation_arriere').is(':checked');
      if (isDerogationChecked) {
        $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
        $('#submit_block_notice').hide();
      } else {
        $('#btn_submit_inscription').prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
        $('#submit_block_notice').html('<i data-lucide="alert-circle" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> Régularisation caisse requise ou cochez la dérogation').show();
        if (window.lucide) lucide.createIcons();
      }
    } else {
      $('#btn_submit_inscription').prop('disabled', false).css({'opacity': '1', 'cursor': 'pointer'});
      $('#submit_block_notice').hide();
    }
  }

  $('#chk_derogation_arriere').on('change', function() {
    if ($(this).is(':checked')) {
      $('#derogation_motif_container').slideDown(200);
    } else {
      $('#derogation_motif_container').slideUp(200);
    }
    updateSubmitButtonQuitusState();
  });



  // 2. Gestion de l'option Redoublant / Passant (Smart Class Progression)
  function handleRedoublantState() {
    var isRedoublant = $('input[name="is_redoublant"]:checked').val() === '1';

    if (isRedoublant) {
      $('#prev_stu_badge_redoublant').show();
      $('#label_classe_select').text("Classe d'affectation (Classe Redoublée)");

      // Si l'étudiant a une classe précédente enregistrée, la pré-sélectionner
      if (currentStudentData && currentStudentData.derniere_classe_code) {
        $('#sel_classe_inscription').val(currentStudentData.derniere_classe_code).trigger('change');
        $('#smart_class_suggestion_text').html('Classe redoublée sélectionnée : <strong>' + (currentStudentData.derniere_classe || '-') + '</strong>');
        $('#smart_class_suggestion_hint').show();
      }
    } else {
      $('#prev_stu_badge_redoublant').hide();
      $('#label_classe_select').text("Classe d'affectation (Nouvelle Classe)");

      // Si le système a détecté une classe N+1 suggérée, la pré-sélectionner en auto
      if (currentStudentData && currentStudentData.suggested_next_class_code) {
        $('#sel_classe_inscription').val(currentStudentData.suggested_next_class_code).trigger('change');
        $('#smart_class_suggestion_text').html('Promotion automatique suggérée : <strong>' + (currentStudentData.suggested_next_class_libelle || '-') + '</strong> (Classe supérieure)');
        $('#smart_class_suggestion_hint').show();
      } else if (currentStudentData && currentStudentData.has_history) {
        $('#smart_class_suggestion_text').html('Veuillez sélectionner la classe supérieure de la filière <strong>' + (currentStudentData.derniere_filiere || '-') + '</strong>.');
        $('#smart_class_suggestion_hint').show();
      } else {
        $('#smart_class_suggestion_hint').hide();
      }
    }
    if (window.lucide) lucide.createIcons();
  }

  $('input[name="is_redoublant"]').on('change', function() {
    handleRedoublantState();
  });

  // 3. Auto-suggestion et affichage des détails tarifaires de la classe sélectionnée
  function fetchTuitionForClass(classeCode) {
    if (!classeCode) {
      isTuitionValid = true;
      $('#no_tuition_warning').slideUp(200);
      $('#prev_class_tuition_section').slideUp(200);
      updateSubmitButtonQuitusState();
      return;
    }
    var affectationEtat = $('input[name="affectation_etat"]:checked').val() || 'non_affecte';

    $.ajax({
      url: '<?= RACINE ?>inscription/getTuitionByClass',
      type: 'GET',
      data: { 
        classe_code: classeCode,
        affectation_etat: affectationEtat,
        annee_code: $('#sel_annee_inscription').val()
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          isTuitionValid = true;
          $('#no_tuition_warning').slideUp(200);
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
          updateSubmitButtonQuitusState();
          if (window.lucide) lucide.createIcons();
        } else {
          isTuitionValid = false;
          $('#inp_montant_scolarite').val('0');
          var errMsg = res.message || "Aucun tarif de scolarité actif n'est configuré pour cette classe sous le régime sélectionné.";
          $('#no_tuition_warning_text').html(errMsg);
          $('#no_tuition_warning').stop(true, true).slideDown(250);
          $('#prev_class_tuition_section').slideUp(200);
          updateSubmitButtonQuitusState();
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function(err) {
        console.error('Erreur chargement tarif classe:', err);
        isTuitionValid = false;
        $('#inp_montant_scolarite').val('0');
        $('#no_tuition_warning_text').html("Erreur lors de la vérification du tarif de scolarité pour cette classe.");
        $('#no_tuition_warning').stop(true, true).slideDown(250);
        $('#prev_class_tuition_section').slideUp(200);
        updateSubmitButtonQuitusState();
        if (window.lucide) lucide.createIcons();
      }
    });
  }

  function filterInscriptionClassesByAnnee() {
    var selectedAnnee = $('#sel_annee_inscription').val();
    var $classeSelect = $('#sel_classe_inscription');
    var currentVal = $classeSelect.val();
    var currentStillValid = false;

    $classeSelect.find('option').each(function() {
      var optAnnee = $(this).data('annee');
      if (!$(this).val()) return;

      if (!selectedAnnee || !optAnnee || optAnnee === selectedAnnee) {
        $(this).prop('disabled', false).show();
        if ($(this).val() === currentVal) {
          currentStillValid = true;
        }
      } else {
        $(this).prop('disabled', true).hide();
      }
    });

    if (!currentStillValid && currentVal) {
      $classeSelect.val('').trigger('change');
    } else {
      $classeSelect.trigger('change.select2');
    }
  }

  $('#sel_annee_inscription').on('change select2:select', function() {
    filterInscriptionClassesByAnnee();
    var stu = $('#sel_etudiant_inscription').val();
    if (stu) {
      fetchStudentProfile(stu);
    }
    var currentClass = $('#sel_classe_inscription').val();
    if (currentClass) {
      fetchTuitionForClass(currentClass);
    }
  });

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

  filterInscriptionClassesByAnnee();

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
  // Soumission unique et sécurisée en AJAX avec émission de la Fiche Navette
  $('#form_inscription_main').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $('#btn_submit_inscription');

    if ($submitBtn.prop('disabled') || $submitBtn.hasClass('btn-is-loading')) {
      return false;
    }

    loading($submitBtn, true, 'Validation en cours...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(res) {
        loading($submitBtn, false, 'Valider la Réinscription & Émettre Fiche Navette');
        if (res.status === 1) {
          showToast(res.message || 'Réinscription enregistrée avec succès !', 'success');

          // Remplissage dynamique et affichage de la Fiche Navette pour le Bureau des Versements
          if (res.voucher_data) {
            var v = res.voucher_data;
            $('#v_code_inscription').text(v.code_inscription || 'INS-XXXX');
            $('#v_matricule').text(v.matricule_etudiant || '-');
            $('#v_nom_complet').text(v.nom_complet || '-');
            $('#v_telephone').text(v.telephone_etudiant || '-');
            $('#v_classe').text(v.classe_libelle || '-');
            $('#v_regime').text(v.regime || '-');
            $('#v_annee_libelle').text(v.annee_libelle || 'Session active');
            $('#v_scolarite_totale').text(Number(v.scolarite_totale || 0).toLocaleString('fr-FR') + ' FCFA');
            $('#v_tranche1_montant').text(Number(v.tranche1_montant || 0).toLocaleString('fr-FR') + ' FCFA');
            $('#v_tranche1_libelle').text(v.tranche1_libelle || 'Tranche 1 / Droit de réinscription');
            $('#v_agent').text(v.agent_inscription || 'Agent Scolarité');
            $('#v_date_inscription').text('Délivré le ' + (v.date_inscription || '-'));

            if (Number(v.arrieres_n1 || 0) > 0) {
              $('#v_arrieres_montant').text(Number(v.arrieres_n1).toLocaleString('fr-FR') + ' FCFA (Moratoire)');
              $('#v_arrieres_box').show();
            } else {
              $('#v_arrieres_box').hide();
            }

            // Mettre à jour le bouton de passage direct au Bureau des Versements
            $('#btn-goto-caisse').attr('href', '<?= RACINE ?>paiement/formulaire?inscription_code=' + encodeURIComponent(v.code_inscription));

            // Ouvrir le modal  de la Fiche Navette
            showFicheNavetteModal();
          } else {
            setTimeout(function() {
              window.location.href = '<?= RACINE ?>reinscription/list';
            }, 1200);
          }
        } else {
          showToast(res.message || 'Une erreur est survenue lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        loading($submitBtn, false, 'Valider la Réinscription & Émettre Fiche Navette');
        var msg = 'Erreur lors de la communication avec le serveur.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        showToast(msg, 'error');
      }
    });
  });

  // Fonctions de contrôle du modal Fiche Navette
  function showFicheNavetteModal() {
    $('#modal_fiche_navette').css('display', 'flex').hide().fadeIn(200);
    $('body').css('overflow', 'hidden');
    if (window.lucide) lucide.createIcons();
  }

  function hideFicheNavetteModal() {
    $('#modal_fiche_navette').fadeOut(150, function() {
      $(this).css('display', 'none');
      $('body').css('overflow', '');
    });
  }

  // Fermeture par le bouton de fermeture X
  $(document).on('click', '.btn-close-fiche-navette', function(e) {
    e.preventDefault();
    hideFicheNavetteModal();
  });

  // Fermeture par clic sur l'arrière-plan semi-transparent
  $('#modal_fiche_navette').on('click', function(e) {
    if (e.target === this) {
      hideFicheNavetteModal();
    }
  });

  // Fermeture par la touche Échap
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#modal_fiche_navette').is(':visible')) {
      hideFicheNavetteModal();
    }
  });

  // Impression de la Fiche Navette
  $('#btn-print-voucher').on('click', function() {
    window.print();
  });

  // Fonctions de contrôle et d'impression de la Fiche de Situation Financière
  window.openFinancialStatementModal = function() {
    if (!currentStudentData) {
      if (typeof showToast === 'function') {
        showToast("Veuillez d'abord sélectionner un étudiant.", "warning");
      } else {
        alert("Veuillez d'abord sélectionner un étudiant.");
      }
      return;
    }
    var d = currentStudentData;

    $('#fin_doc_date').text(new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
    $('#fin_session_libelle').text(d.derniere_annee || d.already_registered_annee || 'Session Active');
    $('#fin_stu_nom').text(d.nom_complet || '-');
    $('#fin_stu_matricule').text(d.matricule || '-');
    $('#fin_stu_nationalite').text(d.nationalite || 'Ivoirienne');
    $('#fin_stu_regime_badge').text(d.prev_regime || 'Non Défini');
    $('#fin_stu_classe').text(d.derniere_classe || d.already_registered_classe || 'Classe non assignée');
    $('#fin_stu_contact').text(d.telephone || '-');
    
    var parentVal = (d.parent_nom || '').trim();
    if (parentVal && parentVal.toLowerCase().indexOf('non renseigné') === -1 && parentVal.toLowerCase().indexOf('non meublé') === -1 && parentVal !== '-') {
      $('#fin_stu_parent').text(parentVal);
      $('#fin_stu_parent_box').show();
    } else {
      $('#fin_stu_parent_box').hide();
    }

    var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
    if (d.photo_url) {
      $('#fin_stu_photo_img').off('error').on('error', function() {
        $(this).hide();
        $('#fin_stu_avatar').text(initials || 'ET').show();
      }).attr('src', d.photo_url).show();
      $('#fin_stu_avatar').hide();
    } else {
      $('#fin_stu_photo_img').hide();
      $('#fin_stu_avatar').text(initials || 'ET').show();
    }

    var scolarite = Number(d.prev_scolarite || 0);
    var paye = Number(d.prev_paye || 0);
    var solde = Number(d.prev_solde || 0);

    $('#fin_scolarite_totale').text(scolarite.toLocaleString('fr-FR') + ' FCFA');
    $('#fin_total_paye').text(paye.toLocaleString('fr-FR') + ' FCFA');

    if (solde <= 0) {
      $('#fin_solde_restant').css('color', '#15803D').text('Compte Soldé (0 FCFA)');
      $('#fin_solde_box_card').css({ 'background': '#F0FDF4', 'border-color': '#86EFAC' });
      $('#fin_arrieres_alert_box').hide();
    } else {
      $('#fin_solde_restant').css('color', '#DC2626').text(solde.toLocaleString('fr-FR') + ' FCFA');
      $('#fin_solde_box_card').css({ 'background': '#FEF2F2', 'border-color': '#FECACA' });
      $('#fin_arrieres_montant').text(solde.toLocaleString('fr-FR') + ' FCFA');
      $('#fin_arrieres_alert_box').show();
    }

    // Remplissage du tableau des paiements
    var payments = d.history_payments || [];
    $('#fin_paiements_count').text(payments.length + ' règlement(s)');
    if (payments.length > 0) {
      var rowsHtml = '';
      payments.forEach(function(p) {
        var dateFmt = p.date_paiement ? new Date(p.date_paiement).toLocaleDateString('fr-FR') : '-';
        var mntFmt = Number(p.montant_paiement || 0).toLocaleString('fr-FR') + ' FCFA';
        rowsHtml += '<tr style="border-bottom: 1px solid #E2E8F0;">' +
          '<td style="padding: 8px 10px; font-weight: 700; color: #1E3A5F; font-family: monospace;">' + (p.code_paiement || '-') + '</td>' +
          '<td style="padding: 8px 10px;">' + dateFmt + '</td>' +
          '<td style="padding: 8px 10px; text-transform: capitalize;">' + (p.mode_paiement || 'Espèces') + '</td>' +
          '<td style="padding: 8px 10px; color: #64748B;">' + (p.reference_paiement || '-') + '</td>' +
          '<td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #047857;">' + mntFmt + '</td>' +
        '</tr>';
      });
      $('#fin_payments_table_body').html(rowsHtml);
    } else {
      $('#fin_payments_table_body').html('<tr><td colspan="5" style="padding: 12px; text-align: center; color: #64748B; font-style: italic;">Aucun paiement enregistré pour cet étudiant.</td></tr>');
    }

    $('#financial-statement-modal').css('display', 'flex').hide().fadeIn(200);
    $('body').css('overflow', 'hidden');
    if (window.lucide) lucide.createIcons();
  };

  window.printFinancialStatement = function() {
    $('body').addClass('printing-financial-statement');
    window.print();
    setTimeout(function() {
      $('body').removeClass('printing-financial-statement');
    }, 1000);
  };

  $(document).on('click', '#btn-print-financial-statement, #btn-quick-print-finance', function(e) {
    e.preventDefault();
    openFinancialStatementModal();
  });

  $('#financial-statement-modal').on('click', function(e) {
    if (e.target === this) {
      $(this).fadeOut(150, function() {
        $('body').css('overflow', '');
      });
    }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>

