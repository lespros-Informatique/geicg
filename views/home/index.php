<?php
require_once __DIR__ . '/../../public/inc/header.php';
$campagnes = (new ModelHome())->getCampagnes();
$activeCampagne = $_SESSION[CAMPAIGN_SESSION]['code_campagne'] ?? '';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <h1 class="page-title">Tableau de Bord</h1>

      <div class="card" style="margin-bottom:20px;">
        <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
          <div class="form-group" style="flex:1;min-width:180px;margin-bottom:0;">
            <label>Campagne</label>
            <div class="input-wrapper">
              <select id="dashCampagne" class="form-control">
                <?php foreach ($campagnes as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_campagne']) ?>" <?= $c['code_campagne'] === $activeCampagne ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['libelle_campagne']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group" style="flex:1;min-width:140px;margin-bottom:0;">
            <label>Du</label>
            <div class="input-wrapper">
              <input type="date" id="dashDateFrom" class="form-control">
            </div>
          </div>
          <div class="form-group" style="flex:1;min-width:140px;margin-bottom:0;">
            <label>Au</label>
            <div class="input-wrapper">
              <input type="date" id="dashDateTo" class="form-control">
            </div>
          </div>
          <button type="button" class="btn btn-primary" id="dashApplyFilter">
            <i class="fa fa-filter"></i> Filtrer
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon revenue">
            <i data-lucide="euro"></i>
          </div>
          <div class="stat-info">
            <h3>Montant attendu</h3>
            <p class="stat-value" data-stat="montant_attendu">0 FCFA</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon delivered">
            <i data-lucide="check-circle"></i>
          </div>
          <div class="stat-info">
            <h3>Montant payé</h3>
            <p class="stat-value" data-stat="montant_paye">0 FCFA</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending">
            <i data-lucide="clock"></i>
          </div>
          <div class="stat-info">
            <h3>Montant restant</h3>
            <p class="stat-value" data-stat="montant_restant">0 FCFA</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon products">
            <i data-lucide="banknote"></i>
          </div>
          <div class="stat-info">
            <h3>Encaissé aujourd'hui</h3>
            <p class="stat-value" data-stat="montant_encaisse_aujourdhui">0 FCFA</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending">
            <i data-lucide="clock"></i>
          </div>
          <div class="stat-info">
            <h3>Commandes actives</h3>
            <p class="stat-value" data-stat="commandes_actives">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cancelled">
            <i data-lucide="x-circle"></i>
          </div>
          <div class="stat-info">
            <h3>Commandes annulées</h3>
            <p class="stat-value" data-stat="commandes_annulees">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon delivered">
            <i data-lucide="check-circle"></i>
          </div>
          <div class="stat-info">
            <h3>Retraits effectués</h3>
            <p class="stat-value" data-stat="retraits_effectues">0</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon delivery">
            <i data-lucide="truck"></i>
          </div>
          <div class="stat-info">
            <h3>Sessions ouvertes</h3>
            <p class="stat-value" data-stat="sessions_ouvertes">0</p>
          </div>
        </div>
      </div>

      <div class="charts-row">
        <div class="card">
          <h2>Ventes par jour</h2>
          <canvas id="salesChart"></canvas>
        </div>
        <div class="card">
          <h2>Top Kits</h2>
          <canvas id="topProductsChart"></canvas>
        </div>
      </div>

      <div class="recent-orders">
        <div class="card">
          <h2>Commandes récentes</h2>
          <div class="mobile-list-container" id="recentOrdersMobile"></div>
          <div class="table-responsive-mobile">
            <table class="table" id="recentOrdersTable">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Client</th>
                  <th>Montant</th>
                  <th>Statut</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="<?= RACINE ?>json/mobile-list.js"></script>
<script src="<?= RACINE ?>json/dashboard.js"></script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
