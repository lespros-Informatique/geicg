<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$db = (new Database())->getCon();
$activeYear = $_SESSION['annee_active_code'] ?? '';
if (empty($activeYear)) {
    $actRow = $db->query("SELECT code_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $activeYear = $actRow['code_annee'] ?? '';
}

$niveauxList = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY id_niveau ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$classesList = $db->query("SELECT code_classe, libelle_classe, niveau_code FROM classes WHERE statut_classe = 'actif' ORDER BY libelle_classe ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

$stmtIns = $db->prepare("
  SELECT i.code_inscription, i.montant_scolarite_inscription, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, c.libelle_classe, c.code_classe, c.niveau_code
  FROM inscriptions i 
  LEFT JOIN etudiants e ON i.etudiant_code = e.code_etudiant 
  LEFT JOIN classes c ON i.classe_code = c.code_classe 
  WHERE (i.annee_code = ? OR ? = '') AND i.statut_inscription != 'annule'
  ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
");
$stmtIns->execute([$activeYear, $activeYear]);
$inscriptionsList = $stmtIns->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
$stmtSessionToday = $db->prepare("SELECT * FROM sessions_caisse WHERE date_session = ? ORDER BY id_session DESC LIMIT 1");
$stmtSessionToday->execute([$today]);
$sessionJour = $stmtSessionToday->fetch(PDO::FETCH_ASSOC);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_paiement']) ? 'Éditer ' : 'Nouveau ' ?> Règlement Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Guichet d'encaissement intelligent des frais de scolarité</p>
        </div>
        <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <!-- BANDEAU D'ÉTAT DE LA SESSION DE CAISSE DU JOUR -->
      <?php if (!empty($sessionJour) && in_array($sessionJour['statut_session'], ['cloturee', 'valide'])): ?>
        <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 20px;">
              <i data-lucide="lock" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <strong style="color: #991B1B; font-size: 14px;">Session de caisse du jour CLÔTURÉE (Réf : <?= htmlspecialchars($sessionJour['code_session']) ?>)</strong>
              <div style="color: #7F1D1D; font-size: 12px; margin-top: 2px;">La session du jour a déjà été arrêtée. Tout nouvel encaissement en espèces est bloqué.</div>
            </div>
          </div>
          <a href="<?= RACINE ?>session_caisse/details/<?= $this->validator->crypter($sessionJour['id_session']) ?>" class="btn btn-sm btn-outline-danger" style="font-weight: 700; border-radius: 6px; font-size: 12px;">
            Voir le PV de Clôture
          </a>
        </div>
      <?php elseif (!empty($sessionJour) && $sessionJour['statut_session'] === 'ouverte'): ?>
        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <strong style="color: #166534; font-size: 13px;">Session de Caisse Active &bull; Ouverte</strong>
              <div style="color: #15803D; font-size: 12px;">Fond initial : <strong><?= number_format((float)$sessionJour['fond_initial'], 0, ',', ' ') ?> FCFA</strong> (Réf : <?= htmlspecialchars($sessionJour['code_session']) ?>)</div>
            </div>
          </div>
          <a href="<?= RACINE ?>session_caisse/cloturer/<?= $this->validator->crypter($sessionJour['id_session']) ?>" class="btn btn-sm btn-outline-success" style="font-weight: 700; border-radius: 6px; font-size: 12px; background:#FFFFFF;">
            <i data-lucide="lock" style="width: 14px; height: 14px;"></i> Clôturer la Session
          </a>
        </div>
      <?php else: ?>
        <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
              <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <strong style="color: #92400E; font-size: 14px;">Attention : Aucune session de caisse n'est OUVERTE pour aujourd'hui</strong>
              <div style="color: #B45309; font-size: 12px; margin-top: 2px;">Pour assurer la traçabilité des espèces et le rapprochement du soir, veuillez démarrer la session de caisse.</div>
            </div>
          </div>
          <a href="<?= RACINE ?>session_caisse/formulaire" class="btn btn-sm btn-warning" style="background: #D97706; border-color: #D97706; color: #FFF; font-weight: 700; border-radius: 6px; font-size: 12px;">
            <i data-lucide="unlock" style="width: 14px; height: 14px;"></i> Ouvrir la Caisse
          </a>
        </div>
      <?php endif; ?>

      <!-- Bande Preview Financière Dynamique (Fiche Synthèse Élève) -->
      <div id="financial-preview-banner" class="card" style="display: none; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(15,23,42,0.06); transition: all 0.3s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          
          <!-- Infos Élève & Matricule -->
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; box-shadow: 0 4px 10px rgba(30,58,95,0.25);" id="prev_avatar">ET</div>
            <div>
              <div style="font-weight: 800; color: #0F172A; font-size: 16px;" id="prev_nom">Nom Étudiant</div>
              <div style="font-size: 13px; color: #64748B; margin-top: 2px;">
                Matricule : <code id="prev_matricule" style="font-weight:800; color:#1E3A5F; font-size:13px;">-</code> &bull; 
                Classe : <span id="prev_classe" style="font-weight:700; color:#334155;">-</span>
              </div>
            </div>
          </div>

          <!-- Badges Financiers -->
          <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <div style="text-align: center; padding: 10px 18px; background: #F8FAFC; border-radius: 10px; border: 1px solid #E2E8F0;">
              <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Scolarité Due</div>
              <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 2px;" id="prev_due">0 FCFA</div>
            </div>

            <div style="text-align: center; padding: 10px 18px; background: #F0FDF4; border-radius: 10px; border: 1px solid #BBF7D0;">
              <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Total Déjà Payé</div>
              <div style="font-size: 16px; font-weight: 800; color: #15803D; margin-top: 2px;" id="prev_paye">0 FCFA</div>
            </div>

            <div style="text-align: center; padding: 10px 18px; background: #FEF2F2; border-radius: 10px; border: 1px solid #FCA5A5;">
              <div style="font-size: 11px; font-weight: 800; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">Solde Restant À Payer</div>
              <div style="font-size: 16px; font-weight: 800; color: #DC2626; margin-top: 2px;" id="prev_solde">0 FCFA</div>
            </div>
          </div>

        </div>
      </div>

      <!-- TABLEAU INTERACTIF DES TRANCHES DE L'ÉLÈVE -->
      <div id="student-tranches-card" class="card" style="display: none; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 22px 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15,23,42,0.06);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1.5px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Échéancier des Tranches & Suivi des Règlements
            </h3>
            <p style="color: #64748B; font-size: 12.5px; margin: 3px 0 0 0;">
              Visualisez les tranches payées et sélectionnez la tranche concernée par cet encaissement.
            </p>
          </div>
          <span id="tranches-count-badge" class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 8px;">
            0 tranche(s)
          </span>
        </div>

        <div style="width: 100%; overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 10px;">
          <table class="table" style="width: 100%; margin: 0; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                <th style="padding: 10px 14px;">Tranche</th>
                <th style="padding: 10px 14px; text-align: center;">Échéance</th>
                <th style="padding: 10px 14px; text-align: right;">Montant Exigible</th>
                <th style="padding: 10px 14px; text-align: right;">Déjà Payé</th>
                <th style="padding: 10px 14px; text-align: right;">Reste Dû</th>
                <th style="padding: 10px 14px; text-align: center; width: 160px;">Statut</th>
              </tr>
            </thead>
            <tbody id="student-tranches-tbody">
              <!-- Injecté via AJAX -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>paiement/<?= !empty($item['id_paiement']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;" id="form-paiement">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_paiement'])): ?>
            <input type="hidden" name="id_paiement" value="<?= $item['id_paiement'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i> Saisie du Règlement de Caisse
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Filtre Rapide par Niveau & Classe -->
            <div style="grid-column: 1 / -1; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 14px 18px; margin-bottom: 4px;">
              <div style="font-weight: 700; font-size: 13px; color: #1E3A5F; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="filter" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Filtrage rapide par groupe / classe (Optionnel) :
              </div>
              <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                  <select id="filter_niveau_select" class="form-control select2" style="width: 100%;">
                    <option value="">-- Tous les Niveaux --</option>
                    <?php foreach ($niveauxList as $n): ?>
                      <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div style="flex: 1; min-width: 220px;">
                  <select id="filter_classe_select" class="form-control select2" style="width: 100%;">
                    <option value="">-- Toutes les Classes --</option>
                    <?php foreach ($classesList as $c): ?>
                      <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <!-- Recherche Sélection Élève -->
            <div class="form-group" style="width: 100%; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13.5px; color: #1E3A5F; margin-bottom: 6px;">
                <i data-lucide="search" style="width: 16px; height: 16px; vertical-align: -2px;"></i> Recherche de l'étudiant (par Matricule, Nom ou Prénom) <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="select_inscription_code" name="inscription_code" style="width: 100%;" required>
                <option value="">-- Saisir le matricule ou le nom de l'élève --</option>
                <?php foreach($inscriptionsList as $ins): ?>
                  <?php
                    $mat = $ins['matricule_etudiant'] ?? '-';
                    $nom = trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? ''));
                    $classe = $ins['libelle_classe'] ?? 'Non affecté';
                    $labelOpt = "$mat - $nom ($classe)";
                  ?>
                  <option value="<?= $ins['code_inscription'] ?>" data-classe="<?= htmlspecialchars($ins['code_classe'] ?? '') ?>" data-niveau="<?= htmlspecialchars($ins['niveau_code'] ?? '') ?>" <?= (($item['inscription_code'] ?? '') == $ins['code_inscription']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($labelOpt) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Tranche correspondante (STRICTEMENT OBLIGATOIRE - SÉLECTION AUTOMATIQUE DE LA PROCHAINE TRANCHE IMPAYÉE) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #1E3A5F; margin-bottom: 6px;">
                <i data-lucide="calendar" style="width: 15px; height: 15px; vertical-align: -2px;"></i> Tranche correspondante au versement <span style="font-size: 11px; font-weight: 600; color: #15803D; background: #DCFCE7; padding: 2px 8px; border-radius: 4px; margin-left: 6px;">(Automatique - Prochaine tranche impayée)</span>
              </label>
              <input type="hidden" id="hidden_tranche_code" name="tranche_code" value="<?= htmlspecialchars($item['tranche_code'] ?? '') ?>">
              <select class="form-control" id="select_tranche_code" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; font-weight: 800; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; pointer-events: none; cursor: not-allowed;" tabindex="-1" readonly required>
                <option value="">-- Sélectionnez d'abord un élève pour charger ses tranches --</option>
              </select>
              <div id="tranche-helper-hint" style="font-size: 12px; color: #64748B; margin-top: 4px; display: none;"></div>
            </div>

            <!-- Montant versé (Readonly / Automatique) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Montant versé (FCFA) <span style="font-size: 11px; font-weight: 600; color: #15803D; background: #DCFCE7; padding: 2px 8px; border-radius: 4px; margin-left: 6px;">(Automatique)</span>
              </label>
              <input type="number" id="inp_montant_paiement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; font-weight: 800; border-radius: 8px; border: 1.5px solid #CBD5E1; background: #F8FAFC; color: #1E3A5F; pointer-events: none; cursor: not-allowed;" name="montant_paiement" value="<?= htmlspecialchars($item['montant_paiement'] ?? '') ?>" placeholder="Montant automatique" min="0" step="any" readonly required tabindex="-1">
            </div>

            <!-- Mode de paiement -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Mode de règlement <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="mode_paiement" required>
                <option value="espece" <?= (($item['mode_paiement'] ?? '') === 'espece') ? 'selected' : '' ?>>Espèces (Caisse Guichet)</option>
                <option value="mobile_money" <?= (($item['mode_paiement'] ?? '') === 'mobile_money') ? 'selected' : '' ?>>Mobile Money (Wave, Orange, MTN, Moov)</option>
                <option value="cheque" <?= (($item['mode_paiement'] ?? '') === 'cheque') ? 'selected' : '' ?>>Chèque bancaire</option>
                <option value="virement" <?= (($item['mode_paiement'] ?? '') === 'virement') ? 'selected' : '' ?>>Virement bancaire</option>
              </select>
            </div>

            <!-- Intitulé du versement -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Intitulé du versement</label>
              <input type="text" id="inp_type_paiement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="type_paiement" value="<?= htmlspecialchars($item['type_paiement'] ?? 'Règlement Scolarité') ?>" placeholder="Ex: Règlement 1ère Tranche Scolarité">
            </div>

            <!-- Numéro de transaction -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro de transaction</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="reference_paiement" value="<?= htmlspecialchars($item['reference_paiement'] ?? '') ?>" placeholder="Ex: TRX-928374 / BORD-10492">
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; border-radius: 8px; padding: 11px 28px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i> Enregistrer l'Encaissement
            </button>
            <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
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
    $('#select_inscription_code, #filter_niveau_select, #filter_classe_select').select2({
      width: '100%'
    });
  }

  var $studentSelect = $('#select_inscription_code');
  var originalStudentOptions = $studentSelect.find('option').clone();

  $('#filter_niveau_select').on('change', function() {
    var selNiveau = $(this).val();
    $('#filter_classe_select option').each(function() {
      var nCode = $(this).attr('data-niveau');
      if (!selNiveau || !nCode || nCode === selNiveau || $(this).val() === '') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });
    if ($.fn.select2) {
      $('#filter_classe_select').select2({ width: '100%' });
    }
    applyStudentFilter();
  });

  $('#filter_classe_select').on('change', function() {
    applyStudentFilter();
  });

  function applyStudentFilter() {
    var selectedNiveau = $('#filter_niveau_select').val();
    var selectedClasse = $('#filter_classe_select').val();
    var currentVal = $studentSelect.val();

    $studentSelect.empty().append(originalStudentOptions.clone());

    if (selectedClasse || selectedNiveau) {
      $studentSelect.find('option').each(function() {
        var val = $(this).val();
        if (!val) return; // Ne pas toucher l'option par défaut

        var cCode = $(this).attr('data-classe');
        var nCode = $(this).attr('data-niveau');

        var matchClasse = !selectedClasse || (cCode === selectedClasse);
        var matchNiveau = !selectedNiveau || (nCode === selectedNiveau);

        if (!matchClasse || !matchNiveau) {
          $(this).remove();
        }
      });
    }

    if ($.fn.select2) {
      $studentSelect.select2({
        placeholder: "-- Rechercher l'étudiant par Matricule, Nom ou Prénom --",
        allowClear: true,
        width: '100%'
      });
    }

    if (currentVal && $studentSelect.find('option[value="' + currentVal + '"]').length > 0) {
      $studentSelect.val(currentVal).trigger('change');
    } else {
      $studentSelect.val('').trigger('change');
    }
  }

  var currentTranchesData = [];
  var preSelectedTrancheCode = "<?= htmlspecialchars($item['tranche_code'] ?? '') ?>";

  function formatFcfa(val) {
    return Number(val || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  function renderTranchesTable(tranches, suggestedCode) {
    currentTranchesData = tranches || [];
    var $tbody = $('#student-tranches-tbody');
    var $select = $('#select_tranche_code');

    $tbody.empty();
    $select.empty();

    if (!tranches || tranches.length === 0) {
      $('#student-tranches-card').slideUp(200);
      $select.append('<option value="">Aucune tranche trouvée pour cette classe</option>');
      $('#hidden_tranche_code').val('');
      return;
    }

    $('#tranches-count-badge').text(tranches.length + ' tranche(s)');
    $select.append('<option value="">-- Choisir la tranche à encaisser --</option>');

    // La tranche à sélectionner automatiquement est STRICTEMENT la prochaine tranche impayée (suggestedCode)
    var nextUnpaidCode = suggestedCode || '';

    tranches.forEach(function(tr, idx) {
      var isPaid = (tr.is_soldee === true || tr.is_soldee === 1 || tr.is_soldee === '1' || tr.statut_code === 'soldee' || parseFloat(tr.reste_a_payer) <= 0);
      var isPartiel = (!isPaid && (tr.statut_code === 'partiel' || parseFloat(tr.deja_paye) > 0));
      var isNextUnpaid = (!isPaid && tr.code_tranche === nextUnpaidCode);
      var isBlocked = (!isPaid && !isNextUnpaid);

      var rowBg = isNextUnpaid ? '#EFF6FF' : (isPaid ? '#F8FAFC' : '#FFFFFF');
      var rowBorder = isNextUnpaid ? '2px solid #1E3A5F' : '1px solid #F1F5F9';

      var statusBadgeHtml = '';
      if (isPaid) {
        statusBadgeHtml = '<button type="button" class="btn btn-sm" disabled style="background:#DCFCE7 !important; color:#15803D !important; font-weight:800; font-size:12px; padding:6px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; border:1px solid #86EFAC !important; cursor:not-allowed; opacity:0.85;">' +
                            '✓ Soldée' +
                          '</button>';
      } else if (isNextUnpaid) {
        if (isPartiel) {
          statusBadgeHtml = '<button type="button" class="btn btn-sm btn-select-tranche-row" data-code="' + tr.code_tranche + '" style="background:#FEF3C7 !important; color:#B45309 !important; font-weight:800; font-size:12px; padding:6px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; border:1px solid #FCD34D !important; cursor:pointer;" title="Tranche partielle - Reste dû: ' + tr.reste_a_payer_fmt + '">' +
                              '⏳ Partielle' +
                            '</button>';
        } else {
          statusBadgeHtml = '<button type="button" class="btn btn-sm btn-select-tranche-row" data-code="' + tr.code_tranche + '" style="background:#1E3A5F !important; color:#FFFFFF !important; font-weight:800; font-size:12px; padding:6px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; border:1px solid #1E3A5F !important; cursor:pointer; box-shadow:0 2px 5px rgba(30,58,95,0.25);" title="Prochaine tranche à régler">' +
                              '● À Payer' +
                            '</button>';
        }
      } else {
        // Tranche ultérieure bloquée jusqu'au solde de la précédente
        statusBadgeHtml = '<button type="button" class="btn btn-sm" disabled style="background:#F1F5F9 !important; color:#94A3B8 !important; font-weight:700; font-size:12px; padding:6px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; border:1px solid #E2E8F0 !important; cursor:not-allowed; opacity:0.65;" title="Solder obligatoirement la tranche impayée précédente d\'abord">' +
                            '🔒 Bloquée' +
                          '</button>';
      }

      var optLabel = tr.libelle_tranche + (isPaid ? ' - [Soldée - 0 F restant]' : (isNextUnpaid ? ' - [PROCHAINE TRANCHE À PAYER - Reste: ' + tr.reste_a_payer_fmt + ']' : ' - [Bloquée - Solder tranche précédente]'));

      // Option in dropdown
      var $opt = $('<option></option>')
        .val(tr.code_tranche)
        .text(optLabel)
        .attr('data-reste', tr.reste_a_payer)
        .attr('data-total', tr.montant_tranche)
        .attr('data-libelle', tr.libelle_tranche)
        .attr('data-soldee', isPaid ? '1' : '0')
        .attr('data-next', isNextUnpaid ? '1' : '0');

      if (isPaid || isBlocked) {
        $opt.prop('disabled', true);
      }
      if (isNextUnpaid) {
        $opt.prop('selected', true);
      }
      $select.append($opt);

      // Table Row
      var rowClass = isPaid ? 'tranche-item-row-soldee' : (isNextUnpaid ? 'tranche-item-row-active' : 'tranche-item-row-blocked');
      var rowCursor = isNextUnpaid ? 'pointer' : 'not-allowed';
      var rowOpacity = isBlocked ? '0.6' : (isPaid ? '0.85' : '1');

      var rowHtml = '<tr class="' + rowClass + '" data-code="' + tr.code_tranche + '" data-soldee="' + (isPaid ? '1' : '0') + '" data-blocked="' + (isBlocked ? '1' : '0') + '" data-next="' + (isNextUnpaid ? '1' : '0') + '" style="background:' + rowBg + '; border-bottom:' + rowBorder + '; cursor:' + rowCursor + '; opacity:' + rowOpacity + '; transition:background 0.2s;">' +
        '<td style="padding:12px 14px;">' +
          '<div class="tranche-libelle-text" style="font-weight:800; color:#0F172A; font-size:13.5px;">' + $('<div>').text(tr.libelle_tranche).html() + '</div>' +
          '<code style="font-size:11px; color:#64748B;">' + tr.code_tranche + '</code>' +
        '</td>' +
        '<td style="padding:12px 14px; text-align:center; font-size:13px; color:#475569;">' +
          tr.date_limite_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:right; font-weight:700; color:#0F172A;">' +
          tr.montant_tranche_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:right; font-weight:700; color:#15803D;">' +
          tr.deja_paye_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:right; font-weight:800; color:' + (isPaid ? '#15803D' : '#DC2626') + ';">' +
          tr.reste_a_payer_fmt +
        '</td>' +
        '<td style="padding:12px 14px; text-align:center;">' +
          statusBadgeHtml +
        '</td>' +
      '</tr>';

      $tbody.append(rowHtml);
    });

    $('#student-tranches-card').stop(true, true).slideDown(250);
    if (window.lucide) lucide.createIcons();

    // Auto-apply the next unpaid tranche
    if (nextUnpaidCode) {
      applySelectedTranche(nextUnpaidCode);
    } else {
      $('#select_tranche_code').val('');
      $('#hidden_tranche_code').val('');
      $('#inp_montant_paiement').val('');
      $('#tranche-helper-hint')
        .html('<span style="color:#15803D; font-weight:700;">✓ Toutes les tranches de scolarité de cet élève sont intégralement soldées.</span>')
        .show();
    }
  }

  function applySelectedTranche(tCode) {
    if (!tCode) {
      $('#hidden_tranche_code').val('');
      $('#select_tranche_code').val('');
      $('#tranche-helper-hint').hide();
      return;
    }

    var selectedTr = null;
    currentTranchesData.forEach(function(tr) {
      if (tr.code_tranche === tCode) {
        selectedTr = tr;
      }
    });

    if (!selectedTr) return;

    var isPaid = (selectedTr.is_soldee === true || selectedTr.is_soldee === 1 || selectedTr.is_soldee === '1' || selectedTr.statut_code === 'soldee' || parseFloat(selectedTr.reste_a_payer) <= 0);

    if (isPaid) {
      $('#select_tranche_code').val('');
      $('#hidden_tranche_code').val('');
      $('#inp_montant_paiement').val('');
      $('#tranche-helper-hint')
        .html('<span style="color:#DC2626; font-weight:700;">⚠️ La tranche <strong>' + selectedTr.libelle_tranche + '</strong> est déjà intégralement payée (Soldée).</span>')
        .show();
      return;
    }

    // Update select & hidden input
    $('#hidden_tranche_code').val(tCode);
    $('#select_tranche_code').val(tCode);

    // Highlight active row in table
    $('#student-tranches-tbody tr').each(function() {
      var rowCode = $(this).data('code');
      if (rowCode === tCode) {
        $(this).css('background', '#EFF6FF');
      } else {
        var isSoldee = $(this).data('soldee') === '1' || $(this).data('soldee') === 1;
        $(this).css('background', isSoldee ? '#F8FAFC' : '#FFFFFF');
      }
    });

    // Auto set amount strictly to remaining balance of this tranche
    var maxAmount = parseFloat(selectedTr.reste_a_payer) || 0;
    $('#inp_montant_paiement').val(maxAmount).attr('max', maxAmount);

    // Auto set wording
    $('#inp_type_paiement').val('Règlement ' + selectedTr.libelle_tranche);

    // Helper hint
    $('#tranche-helper-hint')
      .html('Tranche sélectionnée automatiquement : <strong>' + selectedTr.libelle_tranche + '</strong> • Reste dû exigible : <strong style="color:#DC2626;">' + selectedTr.reste_a_payer_fmt + '</strong> (Montant total tranche : ' + selectedTr.montant_tranche_fmt + ').')
      .show();
    
    validateMontant();
  }

  function validateMontant() {
    var tCode = $('#hidden_tranche_code').val() || $('#select_tranche_code').val();
    var enteredAmount = parseFloat($('#inp_montant_paiement').val()) || 0;

    if (!tCode) return true;

    var selectedTr = currentTranchesData.find(function(t) { return t.code_tranche === tCode; });
    if (!selectedTr) return true;

    var maxAllowed = parseFloat(selectedTr.reste_a_payer) || 0;

    if (enteredAmount <= 0) {
      $('#inp_montant_paiement').css('border-color', '#EF4444');
      return false;
    } else if (enteredAmount > maxAllowed) {
      $('#inp_montant_paiement').css('border-color', '#EF4444');
      $('#tranche-helper-hint').html('<span style="color:#DC2626; font-weight:800;">⛔ Le montant saisi (' + enteredAmount.toLocaleString('fr-FR') + ' FCFA) dépasse le solde restant dû de cette tranche (' + selectedTr.reste_a_payer_fmt + ').</span>').show();
      return false;
    } else {
      $('#inp_montant_paiement').css('border-color', '#CBD5E1');
      $('#tranche-helper-hint').html('Tranche sélectionnée automatiquement : <strong>' + selectedTr.libelle_tranche + '</strong> • Reste dû exigible : <strong style="color:#15803D;">' + selectedTr.reste_a_payer_fmt + '</strong>.').show();
      return true;
    }
  }

  $('#inp_montant_paiement').on('input change keyup', function() {
    validateMontant();
  });

  $('#form-paiement').on('submit', function(e) {
    var tCode = $('#hidden_tranche_code').val() || $('#select_tranche_code').val();
    if (!tCode) {
      e.preventDefault();
      alert('Veuillez sélectionner un élève avec des tranches impayées.');
      return false;
    }
    if (!validateMontant()) {
      e.preventDefault();
      alert('Le montant du versement doit être valide et ne pas dépasser le reste dû de la tranche sélectionnée.');
      return false;
    }
  });

  function fetchStudentFinancialSummary(inscriptionCode) {
    if (!inscriptionCode) {
      $('#financial-preview-banner').slideUp(200);
      $('#student-tranches-card').slideUp(200);
      $('#select_tranche_code').empty().append('<option value="">-- Sélectionnez d\'abord un élève --</option>');
      $('#hidden_tranche_code').val('');
      return;
    }

    var requestUrl = '<?= RACINE ?>paiement/getStudentFinancialSummary';

    $.ajax({
      url: requestUrl,
      type: 'GET',
      data: { inscription_code: inscriptionCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          
          var initials = (d.nom_complet || 'ET').split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
          $('#prev_avatar').text(initials || 'ET');

          $('#prev_nom').text(d.nom_complet);
          $('#prev_matricule').text(d.matricule);
          $('#prev_classe').text(d.classe);
          $('#prev_due').text(d.scolarite_due_fmt);
          $('#prev_paye').text(d.total_paye_fmt);
          $('#prev_solde').text(d.solde_restant_fmt);

          $('#financial-preview-banner').stop(true, true).slideDown(250);

          renderTranchesTable(d.tranches, d.suggested_tranche_code);

          if (window.lucide) lucide.createIcons();
        } else {
          $('#financial-preview-banner').slideUp(200);
          $('#student-tranches-card').slideUp(200);
        }
      },
      error: function(err) {
        console.error('Erreur chargement synthèse financière:', err);
        $('#financial-preview-banner').slideUp(200);
        $('#student-tranches-card').slideUp(200);
      }
    });
  }

  $('#select_inscription_code').on('change select2:select', function() {
    var val = $(this).val();
    preSelectedTrancheCode = '';
    fetchStudentFinancialSummary(val);
  });

  $(document).on('click', '.btn-select-tranche-row, .tranche-item-row-active, .tranche-item-row-blocked, .tranche-item-row-soldee', function(e) {
    var $tr = $(this).closest('tr');
    var isSoldee = $tr.data('soldee');
    var isBlocked = $tr.data('blocked');
    
    if (isSoldee === '1' || isSoldee === 1) {
      if (window.toastr) toastr.info('Cette tranche est déjà totalement soldée.');
      return;
    }

    if (isBlocked === '1' || isBlocked === 1) {
      var $activeRow = $('#student-tranches-tbody tr[data-next="1"]');
      var activeLib = $activeRow.find('.tranche-libelle-text').text() || 'précédente';
      if (window.toastr) toastr.warning('Vous devez obligatoirement solder la tranche impayée en cours (« ' + activeLib + ' ») avant de pouvoir régler une tranche ultérieure.');
      return;
    }

    var tCode = $tr.data('code');
    applySelectedTranche(tCode);
  });

  // Auto trigger if initial selected
  var initVal = $('#select_inscription_code').val();
  if (initVal) {
    fetchStudentFinancialSummary(initVal);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
