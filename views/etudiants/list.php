<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 
$niveaux = $niveaux ?? [];
$anneeActive = $anneeActive ?? '';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user-plus" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Nouveaux Étudiants
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation du registre et enregistrement des nouveaux dossiers étudiants</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
          <a href="<?= RACINE ?>etudiant/imprimerPdf" id="btn-print-etudiants" target="_blank" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;" title="Générer & Imprimer la liste des étudiants en PDF">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </a>
          <?php if (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::MANAGE_ETUDIANTS, PERMISSIONS::MANAGE_INSCRIPTIONS]))): ?>
          <a href="<?= RACINE ?>etudiant/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Dossier Étudiant
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES & INTELLIGENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
          <span style="font-size: 13px; font-weight: 700; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="filter" style="width: 15px; height: 15px;"></i> Filtres de Recherche Dynamique
          </span>
          <button type="button" id="btn-reset-filters" style="background: none; border: none; color: #64748B; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px;" onmouseover="this.style.color='#EF4444'; this.style.background='#FEF2F2';" onmouseout="this.style.color='#64748B'; this.style.background='none';">
            <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i> Réinitialiser les filtres
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: flex-end;">
          
          <!-- Filtre Année Académique (Verrouillé sur l'année active en session) -->
          <div class="form-group" style="margin: 0;">
            <label style="display: flex; align-items: center; justify-content: space-between; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <span>Année Académique</span>
              <span style="font-size: 10px; font-weight: 800; color: #1E3A5F; background: #E2E8F0; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                <i data-lucide="lock" style="width: 10px; height: 10px;"></i> Session
              </span>
            </label>
            <div style="position: relative;">
              <select id="filter-annee" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 700; background: #F1F5F9; color: #1E3A5F; cursor: not-allowed; pointer-events: none;" readonly tabindex="-1">
                <?php foreach ($annees as $a): ?>
                  <?php if ($a['code_annee'] === $anneeActive): ?>
                    <option value="<?= htmlspecialchars($a['code_annee']) ?>" selected>
                      <?= htmlspecialchars($a['libelle_annee']) ?> (Année Active)
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
                <?php if (empty($anneeActive)): ?>
                  <option value="ALL">-- Année Active --</option>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- Filtre Filière -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <i data-lucide="book-open" style="width: 13px; height: 13px; color: #1E3A5F; vertical-align: middle;"></i> Filière
            </label>
            <select id="filter-filiere" class="form-control select2" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les filières --</option>
              <?php foreach ($filieres as $f): ?>
                <?php 
                  $displayFil = (!empty($useSlugFiliere) && !empty($f['slug_filiere']))
                    ? $f['slug_filiere']
                    : $f['libelle_filiere'];
                ?>
                <option value="<?= htmlspecialchars($f['code_filiere']) ?>" title="<?= htmlspecialchars($f['libelle_filiere']) ?>">
                  <?= htmlspecialchars($displayFil) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Niveau -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <i data-lucide="layers" style="width: 13px; height: 13px; color: #1E3A5F; vertical-align: middle;"></i> Niveau d'Études
            </label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Tous les niveaux --</option>
              <?php foreach ($niveaux as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>">
                  <?= htmlspecialchars($n['libelle_niveau']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Classe -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <i data-lucide="graduation-cap" style="width: 13px; height: 13px; color: #1E3A5F; vertical-align: middle;"></i> Classe Spécifique
            </label>
            <select id="filter-classe" class="form-control select2" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Toutes les classes --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-filiere="<?= htmlspecialchars($c['filiere_code'] ?? '') ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>" data-annee="<?= htmlspecialchars($c['annee_code'] ?? '') ?>">
                  <?= htmlspecialchars($c['libelle_classe']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Régime -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <i data-lucide="user-check" style="width: 13px; height: 13px; color: #1E3A5F; vertical-align: middle;"></i> Régime Étudiant
            </label>
            <select id="filter-regime" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Tous les régimes --</option>
              <option value="affecte">Affecté(e) État</option>
              <option value="non_affecte">Non Affecté(e) / Privé</option>
            </select>
          </div>

          <!-- Filtre Canal Inscription -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <i data-lucide="split" style="width: 13px; height: 13px; color: #1E3A5F; vertical-align: middle;"></i> Canal
            </label>
            <select id="filter-canal" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Tous les canaux --</option>
              <option value="guichet">Guichet (Admin)</option>
              <option value="en_ligne">En Ligne (Portail)</option>
            </select>
          </div>

          <!-- Filtre Statut Inscription -->
          <div class="form-group" style="margin: 0;">
            <label style="display: block; font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
              <i data-lucide="shield-check" style="width: 13px; height: 13px; color: #1E3A5F; vertical-align: middle;"></i> Statut Dossier
            </label>
            <select id="filter-statut-inscription" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
              <option value="ALL">-- Tous les statuts --</option>
              <option value="en_attente">En Attente</option>
              <option value="valide">Validée</option>
              <option value="solde">Soldée</option>
              <option value="annule">Annulée</option>
            </select>
          </div>

        </div>
      </div>

      <!-- CARTE KPI / INDICATEURS CLÉS DU REGISTRE ÉTUDIANT -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;" class="no-print">
        
        <!-- Total Étudiants -->
        <div class="card kpi-card-clickable" data-regime="ALL" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04); display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.2s ease;" title="Cliquer pour afficher tous les étudiants">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(30, 58, 95, 0.12);">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Étudiants</div>
            <div style="font-size: 24px; font-weight: 900; color: #0F172A; line-height: 1.2;" id="kpi-total-etudiants">0</div>
            <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">Dossiers inscrits filtrés</div>
          </div>
        </div>

        <!-- Régime Affecté (État) -->
        <div class="card kpi-card-clickable" data-regime="affecte" style="background: linear-gradient(135deg, #FFFFFF 0%, #F0FDF4 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #DCFCE7; border-left: 4px solid #16A34A; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.05); display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.2s ease;" title="Cliquer pour filtrer les étudiants affectés par l'État">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(21, 128, 61, 0.12);">
            <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Affectés (État)</div>
            <div style="font-size: 24px; font-weight: 900; color: #15803D; line-height: 1.2;" id="kpi-affectes">0</div>
            <div style="font-size: 11.5px; color: #166534; margin-top: 2px;">Boursiers & orientations d'État</div>
          </div>
        </div>

        <!-- Régime Non Affecté (Privé) -->
        <div class="card kpi-card-clickable" data-regime="non_affecte" style="background: linear-gradient(135deg, #FFFFFF 0%, #EFF6FF 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #BFDBFE; border-left: 4px solid #2563EB; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.05); display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.2s ease;" title="Cliquer pour filtrer les étudiants non affectés / privé">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #DBEAFE; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(29, 78, 216, 0.12);">
            <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #1D4ED8; text-transform: uppercase; letter-spacing: 0.5px;">Non Affectés / Privé</div>
            <div style="font-size: 24px; font-weight: 900; color: #1E40AF; line-height: 1.2;" id="kpi-non-affectes">0</div>
            <div style="font-size: 11.5px; color: #1D4ED8; margin-top: 2px;">Inscriptions directes autofinancées</div>
          </div>
        </div>

        <!-- Répartition Genre (Hommes / Femmes) -->
        <div class="card kpi-card-clickable" style="background: linear-gradient(135deg, #FFFFFF 0%, #FDF4FF 100%); border-radius: 12px; padding: 18px 20px; border: 1px solid #f15f81ff; border-left: 4px solid #ef6373ff; box-shadow: 0 2px 4px rgba(168, 85, 247, 0.05); display: flex; align-items: center; gap: 16px; transition: all 0.2s ease;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F3E8FF; color: #ef2424ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(126, 34, 206, 0.12);">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #e72525ff; text-transform: uppercase; letter-spacing: 0.5px;">Répartition Genre</div>
            <div style="font-size: 20px; font-weight: 900; color: #ba292bff; line-height: 1.2;" id="kpi-genre">0 H / 0 F</div>
            <div style="font-size: 11.5px; color: #e33549ff; margin-top: 2px;">Hommes & Femmes inscrit(e)s</div>
          </div>
        </div>

      </div>

      <!-- TABLEAU DU REGISTRE DES ÉTUDIANTS (Colonnes intelligentes) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-etudiants" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 45px;">#</th>
                <th style="padding: 12px;"><i data-lucide="calendar" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Date Inscription</th>
                <th style="padding: 12px;"><i data-lucide="qr-code" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Matricule</th>
                <th style="padding: 12px;"><i data-lucide="user" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Étudiant (Nom & Prénoms)</th>
                <th style="padding: 12px;"><i data-lucide="venus-mars" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Sexe</th>
                <th style="padding: 12px;"><i data-lucide="shield-check" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Régime</th>
                <th style="padding: 12px;"><i data-lucide="graduation-cap" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Classe</th>
                <th style="padding: 12px;"><i data-lucide="book-open" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Filière</th>
                <th style="padding: 12px;"><i data-lucide="layers" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Niveau</th>
                <th style="padding: 12px; text-align: center; width: 160px;"><i data-lucide="settings" style="width: 14px; height: 14px; color: #1E3A5F; vertical-align: middle; margin-right: 4px;"></i> Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>



<script type="application/json" id="etudiants-list-config">
<?= json_encode([
    'canChangerClasse' => (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::CHANGER_CLASSE_ETUDIANT, PERMISSIONS::MANAGE_ETUDIANTS]))),
    'canEditEtudiant' => (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::EDIT_ETUDIANT, PERMISSIONS::MANAGE_ETUDIANTS]))),
    'canAnnulerInscription' => (!isset($canAccess) || (is_callable($canAccess) && $canAccess([PERMISSIONS::ANNULER_INSCRIPTION, PERMISSIONS::MANAGE_INSCRIPTIONS]))),
    'useSlugFiliere' => !empty($useSlugFiliere),
    'useSlugNiveau' => !empty($useSlugNiveau),
    'anneeActive' => $anneeActive ?? '',
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/etudiants_list.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
