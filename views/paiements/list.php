<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<style>
@media print {
  .sidebar, .main-nav, nav, .no-print, .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate, button, a.btn {
    display: none !important;
  }
  .main-content, .content-wrapper, .app-layout {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  .card {
    box-shadow: none !important;
    border: 1px solid #CBD5E1 !important;
  }
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  th, td {
    border: 1px solid #94A3B8 !important;
    padding: 6px 8px !important;
    font-size: 11px !important;
  }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Caisse & Encaissements Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Caisse & Encaissements Scolarité</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
          <button onclick="window.print()" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;" title="Imprimer le registre des encaissements">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </button>
          <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Règlement Caisse
          </a>
        </div>
      </div>

      <!-- BANDE DE FILTRES DYNAMIQUES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: flex-end;">
          
          <!-- Filtre Année Académique -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calendar" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Année Académique :
            </label>
            <select id="filter-annee" class="form-control select2" style="width: 100%;">
              <?php foreach (($annees ?? []) as $a): ?>
                <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($selectedAnneeCode ?? '') === $a['code_annee']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['libelle_annee']) ?> <?= ($a['statut_annee'] ?? '') === 'actif' ? ' (Active)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Niveau -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="layer" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Niveau d'Études :
            </label>
            <select id="filter-niveau" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Tous les niveaux --</option>
              <?php foreach (($niveaux ?? []) as $n): ?>
                <option value="<?= htmlspecialchars($n['code_niveau']) ?>"><?= htmlspecialchars($n['libelle_niveau']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtre Classe -->
          <div class="form-group" style="margin: 0;">
            <label style="font-weight: 700; font-size: 12.5px; color: #1E3A5F; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="users" style="width: 15px; height: 15px; color: #1E3A5F;"></i> Classe / Groupe :
            </label>
            <select id="filter-classe" class="form-control select2" style="width: 100%;">
              <option value="ALL">-- Toutes les classes --</option>
              <?php foreach (($classes ?? []) as $c): ?>
                <option value="<?= htmlspecialchars($c['code_classe']) ?>" data-niveau="<?= htmlspecialchars($c['niveau_code'] ?? '') ?>"><?= htmlspecialchars($c['libelle_classe']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>

      <!-- STATISTIQUES & INDICATEURS CLÉS CAISSE -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Encaissé -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Encaissé</div>
            <div style="font-size: 19px; font-weight: 900; color: #15803D; margin-top: 4px;">
              <?= number_format((float)($stats['total_encaisse'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 11px; font-weight: 700; color: #166534;">FCFA</span>
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Recouvrement : <strong style="color: #15803D;"><?= number_format((float)($stats['taux_recouvrement'] ?? 0), 1, ',', ' ') ?>%</strong>
            </div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Montant en Attente (Reste à Recouvrer) -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant en Attente</div>
            <div style="font-size: 19px; font-weight: 900; color: #B91C1C; margin-top: 4px;">
              <?= number_format((float)($stats['montant_en_attente'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 11px; font-weight: 700; color: #991B1B;">FCFA</span>
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #64748B; margin-top: 2px;">
              Scolarité attendue : <?= number_format((float)($stats['total_scolarite_attendue'] ?? 0), 0, ',', ' ') ?> F
            </div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEE2E2; color: #B91C1C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Encaissements Aujourd'hui -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Aujourd'hui</div>
            <div style="font-size: 19px; font-weight: 900; color: #1E3A5F; margin-top: 4px;">
              <?= number_format((float)($stats['encaisse_aujourdhui'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 11px; font-weight: 700; color: #1E3A5F;">FCFA</span>
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #64748B; margin-top: 2px;">Session du jour</div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calendar-check" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Encaissements du Mois -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Mois en Cours</div>
            <div style="font-size: 19px; font-weight: 900; color: #0F172A; margin-top: 4px;">
              <?= number_format((float)($stats['encaisse_mois'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 11px; font-weight: 700; color: #475569;">FCFA</span>
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #64748B; margin-top: 2px;">Mois en cours</div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: #F8FAFC; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #E2E8F0;">
            <i data-lucide="trending-up" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

        <!-- Reçus Délivrés & Étudiants Inscrits -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Reçus & Inscrits</div>
            <div style="font-size: 19px; font-weight: 900; color: #B45309; margin-top: 4px;">
              <?= number_format((int)($stats['total_operations'] ?? 0), 0, ',', ' ') ?> <span style="font-size: 11px; font-weight: 700; color: #B45309;">reçus</span>
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #64748B; margin-top: 2px;">
              <strong><?= number_format((int)($stats['total_eleves_payeurs'] ?? 0), 0, ',', ' ') ?></strong> payeur(s) / <strong><?= number_format((int)($stats['total_inscrits'] ?? 0), 0, ',', ' ') ?></strong> inscrit(s)
            </div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="receipt" style="width: 20px; height: 20px;"></i>
          </div>
        </div>

      </div>

      <!-- TABLEAU DES ENCAISSEMENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-paiements" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 45px; text-align: center;">#</th>
                <th style="padding: 12px; width: 130px;">Réf. Reçu</th>
                <th style="padding: 12px; width: 130px;">Date</th>
                <th style="padding: 12px;">Étudiant</th>
                <th style="padding: 12px;">Motif / Tranche</th>
                <th style="padding: 12px; width: 170px; text-align: right;">Montant Versé</th>
                <th style="padding: 12px; width: 170px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<script>
$(document).ready(function() {
  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
  }

  var table = $('#table-paiements').DataTable({
    order: [],
    ajax: {
      url: '<?= RACINE ?>paiement/apiList',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '45px', className: 'text-center', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_paiement', width: '130px', render: function(d) {
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + (d || '-') + '</code>';
      } },
      { data: 'date_paiement', width: '130px', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          var dateFr = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
          var timeFr = parts[1] ? parts[1].substring(0,5) : '';
          return '<span style="font-weight:600; color:#334155;">' + dateFr + '</span>' + (timeFr && timeFr !== '00:00' ? ' <span style="font-size:11px; color:#64748B;">' + timeFr + '</span>' : '');
        }
        return d;
      } },
      { data: 'etudiant_nom', render: function(d, type, row) {
        return '<div style="font-weight:700; color:#0F172A; font-size:13.5px;">' + (d || 'Étudiant non identifié') + '</div>';
      } },
      { data: 'libelle_tranche', render: function(d, type, row) {
        return '<span style="font-weight:600; color:#334155; font-size:13px;">' + (d || 'Frais de Scolarité') + '</span>';
      } },
      { data: 'montant_paiement', width: '170px', className: 'text-end', render: function(d) {
        return d ? '<strong style="color:#15803D; font-size:14px;">' + Number(d).toLocaleString('fr-FR') + ' FCFA</strong>' : '-';
      } },
      { data: null, width: '230px', orderable: false, className: 'text-end', render: function(d) {
        var idCrypte = d.editId || d.id_paiement;
        return '<div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">' +
               '  <a href="' + window.RACINE + 'paiement/details/' + idCrypte + '?print=1" target="_blank" class="btn btn-sm btn-outline-primary" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;" title="Imprimer le reçu"><i data-lucide="printer" style="width:13px;height:13px;"></i> Imprimer</a>' +
               '  <a href="' + window.RACINE + 'paiement/details/' + idCrypte + '" class="btn btn-sm btn-info" style="font-weight:700; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="eye" style="width:13px;height:13px;"></i> Détails</a>' +
               '  <a href="' + window.RACINE + 'paiement/edition/' + idCrypte + '" class="btn btn-sm btn-secondary" style="font-weight:600; border-radius:6px; padding:5px 9px; display:inline-flex; align-items:center; gap:3px;"><i data-lucide="edit" style="width:13px;height:13px;"></i> Éditer</a>' +
               '</div>';
      } }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-annee').on('change', function() {
    var val = $(this).val();
    window.location.href = '<?= RACINE ?>paiement/list?annee_code=' + encodeURIComponent(val);
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
