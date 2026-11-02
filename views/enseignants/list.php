<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Corps Enseignant & Professeurs</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion des enseignants, grades académiques et accès pédagogiques</p>
        </div>
        <a href="<?= RACINE ?>enseignant/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> Nouvel Enseignant
        </a>
      </div>
      <!-- CARTES KPI / STATISTIQUES DU CORPS ENSEIGNANT -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Enseignants -->
        <div class="card kpi-card-filter" data-filter="" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Afficher tous les enseignants">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Enseignants</div>
            <div style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 2px;" id="kpi-total-enseignants"><?= (int)($stats['total'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Enseignants Actifs -->
        <div class="card kpi-card-filter" data-filter="actif" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Filtrer les enseignants actifs">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Enseignants Actifs</div>
            <div style="font-size: 22px; font-weight: 900; color: #15803D; margin-top: 2px;" id="kpi-actifs-enseignants"><?= (int)($stats['actifs'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Enseignants Permanents -->
        <div class="card kpi-card-filter" data-filter="permanent" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Filtrer les enseignants permanents">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="briefcase" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Contrats Permanents</div>
            <div style="font-size: 22px; font-weight: 900; color: #4338CA; margin-top: 2px;" id="kpi-permanents-enseignants"><?= (int)($stats['permanents'] ?? 0) ?></div>
          </div>
        </div>

        <!-- Vacataires & Prestataires -->
        <div class="card kpi-card-filter" data-filter="vacataire" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.2s;" title="Filtrer les vacataires & prestataires">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
          </div>
          <div style="overflow: hidden;">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Vacataires / Prestataires</div>
            <div style="font-size: 22px; font-weight: 900; color: #B45309; margin-top: 2px;" id="kpi-vacataires-enseignants"><?= (int)($stats['vacataires'] ?? 0) ?></div>
          </div>
        </div>

      </div>

      <!-- BARRE DE FILTRES MULTI-CRITÈRES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="filter" style="width: 16px; height: 16px; color: #3B82F6;"></i> Filtres de Recherche Avancée
          </div>
          <button type="button" id="btn-reset-filters" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid #CBD5E1; color: #475569; background: #F8FAFC; cursor: pointer;">
            <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i> Réinitialiser les filtres
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
          
          <!-- 1. Statut -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="check-circle" style="width: 14px; height: 14px; color: #64748B;"></i> Statut
            </label>
            <select id="filter-statut" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les statuts --</option>
              <option value="actif">Actif</option>
              <option value="inactif">Inactif</option>
            </select>
          </div>

          <!-- 2. Type de Contrat -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="briefcase" style="width: 14px; height: 14px; color: #64748B;"></i> Type de Contrat
            </label>
            <select id="filter-contrat" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les contrats --</option>
              <option value="permanent">Permanent / Titulaire</option>
              <option value="vacataire">Vacataire</option>
              <option value="prestataire">Prestataire</option>
            </select>
          </div>

          <!-- 3. Matière Enseignée -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="book-open" style="width: 14px; height: 14px; color: #64748B;"></i> Matière Enseignée
            </label>
            <select id="filter-matiere" class="form-control select2" style="width: 100%;">
              <option value="">-- Toutes les matières --</option>
              <?php foreach ($matieres as $m): ?>
                <option value="<?= htmlspecialchars($m['code_matiere']) ?>"><?= htmlspecialchars($m['libelle_matiere']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 4. Grade Académique -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="award" style="width: 14px; height: 14px; color: #64748B;"></i> Grade Académique
            </label>
            <select id="filter-grade" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous les grades --</option>
              <?php if (!empty($grades)): ?>
                <?php foreach ($grades as $g): ?>
                  <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                <?php endforeach; ?>
              <?php else: ?>
                <option value="Professeur">Professeur</option>
                <option value="Docteur">Docteur</option>
                <option value="Ingénieur">Ingénieur</option>
                <option value="Master">Master / DEA</option>
              <?php endif; ?>
            </select>
          </div>

          <!-- 5. Genre / Sexe -->
          <div>
            <label style="font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="user" style="width: 14px; height: 14px; color: #64748B;"></i> Genre
            </label>
            <select id="filter-sexe" class="form-control select2" style="width: 100%;">
              <option value="">-- Tous --</option>
              <option value="M">Masculin</option>
              <option value="F">Féminin</option>
            </select>
          </div>

        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-enseignants" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 50px;">#</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Enseignant </th>
                <th style="padding: 12px;" class="text-center">Genre</th>
                <th style="padding: 12px;">Email / Téléphone</th>
                <th style="padding: 12px;">Contrat</th>
                <th style="padding: 12px;" class="text-center">Statut</th>
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
<script type="application/json" id="enseignants-list-config">
<?= json_encode([
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/enseignants.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
