<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$stats = $stats ?? (new ModelDepense())->getStats();
$annees = $annees ?? [];
$selectedAnneeCode = $selectedAnneeCode ?? ($_SESSION['annee_active_code'] ?? '');
$typeDepenses = $typeDepenses ?? (new ModelTypeDepense())->getAll();
$canValidate = $canValidate ?? (isset($_SESSION[USERS_AUTH]['permissions']) && in_array('VALIDATE_DEPENSES', $_SESSION[USERS_AUTH]['permissions']));
$canRecord = $canRecord ?? (isset($_SESSION[USERS_AUTH]['permissions']) && in_array('RECORD_DEPENSES', $_SESSION[USERS_AUTH]['permissions']));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de la page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Dépenses & Charges de Fonctionnement</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et suivi des décaissements, engagements budgétaires et validation des dépenses</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>type_depense/list" class="btn btn-secondary" style="background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="tags" style="width: 18px; height: 18px;"></i> Catégories de Dépenses
          </a>
          <?php if ($canRecord): ?>
          <button type="button" class="btn btn-primary btn-add-depense" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Décaissement / Dépense
          </button>
          <?php endif; ?>
        </div>
      </div>


<style>
.kpi-card {
  transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.28s ease;
}
.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 20px -4px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.04) !important;
  border-color: #CBD5E1 !important;
}
.kpi-icon-wrapper {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.kpi-card:hover .kpi-icon-wrapper {
  transform: scale(1.12) rotate(-4deg);
}
</style>

      <!-- ========================================================================= -->
      <!-- BARRE DE FILTRAGE : CATÉGORIE & PÉRIODE -->
      <!-- ========================================================================= -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 22px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="filter" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <span style="font-size: 13.5px; font-weight: 800; color: #0F172A; display: block;">Filtrer les Dépenses</span>
              <span style="font-size: 11.5px; color: #64748B;">Filtrage par catégorie et période d'engagement</span>
            </div>
          </div>
          
          <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end;">
            <!-- Filtre Catégorie -->
            <div style="min-width: 200px;">
              <label style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; display: block;">Catégorie</label>
              <select id="filter-type-depense" class="form-control select2" style="width: 100%;">
                <option value="">-- Toutes les catégories --</option>
                <?php foreach ($typeDepenses as $t): ?>
                  <option value="<?= htmlspecialchars($t['code_type_depense']) ?>">
                    <?= htmlspecialchars($t['libelle_type_depense']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filtre Date Début -->
            <div style="min-width: 150px;">
              <label style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; display: block;">Période (Du)</label>
              <input type="date" id="filter-date-debut" class="form-control" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600;">
            </div>

            <!-- Filtre Date Fin -->
            <div style="min-width: 150px;">
              <label style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; display: block;">Au</label>
              <input type="date" id="filter-date-fin" class="form-control" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600;">
            </div>

            <!-- Bouton Réinitialiser -->
            <div style="margin-top: 18px;">
              <button type="button" id="btn-reset-filters" class="btn btn-secondary" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;" title="Réinitialiser tous les filtres">
                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Réinitialiser
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- CARTES KPI DE STATISTIQUES FINANCIÈRES DES DÉPENSES -->
      <!-- ========================================================================= -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 24px;">
        
        <!-- Total Approuvé -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 22px; min-height: 105px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; box-sizing: border-box;">
          <div class="kpi-icon-wrapper" style="width: 50px; height: 50px; border-radius: 12px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="check-circle" style="width: 25px; height: 25px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">Dépenses Approuvées</div>
            <div style="font-size: 19px; font-weight: 900; color: #15803D; margin-top: 4px;" id="kpi-montant-approuve"><?= number_format($stats['montant_approuve'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; font-weight: 700; color: #16A34A; margin-top: 3px;" id="kpi-count-approuve"><?= ($stats['count_approuve'] ?? 0) ?> dépense(s)</div>
          </div>
        </div>

        <!-- En Attente de Validation -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 22px; min-height: 105px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; box-sizing: border-box;">
          <div class="kpi-icon-wrapper" style="width: 50px; height: 50px; border-radius: 12px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="clock" style="width: 25px; height: 25px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">En Attente de Validation</div>
            <div style="font-size: 19px; font-weight: 900; color: #B45309; margin-top: 4px;" id="kpi-montant-attente"><?= number_format($stats['montant_en_attente'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; font-weight: 700; color: #D97706; margin-top: 3px;" id="kpi-count-attente"><?= ($stats['count_en_attente'] ?? 0) ?> dépense(s)</div>
          </div>
        </div>

        <!-- Dépenses Annulées -->
        <div class="card kpi-card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 22px; min-height: 105px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; box-sizing: border-box;">
          <div class="kpi-icon-wrapper" style="width: 50px; height: 50px; border-radius: 12px; background: #F8FAFC; color: #64748B; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="x-circle" style="width: 25px; height: 25px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.3px;">Dépenses Annulées</div>
            <div style="font-size: 19px; font-weight: 900; color: #64748B; margin-top: 4px;" id="kpi-montant-annule"><?= number_format($stats['montant_annule'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; font-weight: 700; color: #EF4444; margin-top: 3px;" id="kpi-count-annule"><?= ($stats['count_annule'] ?? 0) ?> dépense(s)</div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- TABLEAU DES DÉPENSES DE FONCTIONNEMENT -->
      <!-- ========================================================================= -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Registre des Dépenses & Validation</h3>
            <p style="font-size: 12.5px; color: #64748B; margin: 2px 0 0 0;">Seules les dépenses au statut <strong style="color: #B45309;">En attente</strong> peuvent être modifiées par l'utilisateur.</p>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-depenses" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px; width: 40px;">#</th>
                <th style="padding: 12px;">Code Dépense</th>
                <th style="padding: 12px;">Catégorie</th>
                <th style="padding: 12px; text-align: right;">Montant Engagé</th>
                <th style="padding: 12px;">Date Engagement</th>
                <th style="padding: 12px;">Auteur / Traitement</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
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

<!-- ========================================================================= -->
<!-- MODAL INTERACTIVE : NOUVEAU / MODIFIER DÉCAISSEMENT / DÉPENSE -->
<!-- ========================================================================= -->
<div id="modal-depense" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 14px; width: 100%; max-width: 640px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: slideDown 0.2s ease-out;">
    <div style="background: #1E3A5F; color: #FFFFFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 id="modal-depense-title" style="font-size: 16px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Nouveau Décaissement / Dépense
      </h3>
      <button type="button" class="btn-close-modal-depense" style="background: transparent; border: none; color: #FFFFFF; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="form-depense-modal" enctype="multipart/form-data" style="padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_depense" id="depense_modal_id" value="">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
        
        <!-- Catégorie de Dépense -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Catégorie de dépense <span style="color: #EF4444;">*</span>
          </label>
          <select name="type_depense_code" id="depense_modal_type" required class="form-control select2" style="width: 100%;">
            <option value="">-- Sélectionnez une catégorie --</option>
            <?php foreach ($typeDepenses as $td): ?>
              <option value="<?= htmlspecialchars($td['code_type_depense']) ?>">
                <?= htmlspecialchars($td['libelle_type_depense']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Motif / Description -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Motif / Description de la dépense <span style="color: #EF4444;">*</span>
          </label>
          <input type="text" name="description_depense" id="depense_modal_description" required placeholder="Ex: Achat de fournitures de bureau, maintenance..." class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Montant engagé -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Montant engagé (FCFA) <span style="color: #EF4444;">*</span>
          </label>
          <input type="number" name="montant_depense" id="depense_modal_montant" required min="1" step="any" placeholder="Ex: 85000" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Mode de paiement -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Mode de règlement <span style="color: #EF4444;">*</span>
          </label>
          <select name="mode_reglement" id="depense_modal_mode" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
            <option value="espece">Espèces (Caisse)</option>
            <option value="mobile_money">Mobile Money (Wave, Orange, MTN, Moov)</option>
            <option value="cheque">Chèque bancaire</option>
            <option value="virement">Virement bancaire</option>
          </select>
        </div>

        <!-- Bénéficiaire -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Bénéficiaire / Prestataire
          </label>
          <input type="text" name="beneficiaire" id="depense_modal_beneficiaire" placeholder="Ex: Librairie de France Abidjan" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Date d'engagement -->
        <div class="form-group">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Date d'engagement <span style="color: #EF4444;">*</span>
          </label>
          <input type="date" name="periode_depense" id="depense_modal_date" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 600; font-size: 14px;">
        </div>

        <!-- Document / Pièce Justificative -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Document / Pièce justificative <span style="font-size: 11.5px; color: #64748B; font-weight: 500;">(PDF, JPG, PNG - Max 3 Mo)</span>
          </label>
          <input type="file" name="piece_justificative" id="depense_modal_piece" accept=".pdf,.jpg,.jpeg,.png" class="form-control" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; background: #F8FAFC;">
          <div id="piece-justificative-preview" style="margin-top: 6px; font-size: 12.5px;"></div>
        </div>

      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn btn-secondary btn-close-modal-depense" style="font-weight: 700; border-radius: 8px; padding: 10px 20px;">Annuler</button>
        <button type="submit" id="btn-save-depense-modal" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Dépense
        </button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#filter-annee').select2({ width: '100%' });
  }

  function showNotify(msg, type) {
    type = type || 'info';
    if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else if (window.Swal) {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
      });
      Toast.fire({
        icon: type,
        title: msg
      });
    } else {
      alert(msg);
    }
  }

  if ($.fn.select2) {
    $('#filter-type-depense').select2({ width: '100%' });
    $('#depense_modal_type').select2({
      dropdownParent: $('#modal-depense'),
      width: '100%',
      placeholder: '-- Sélectionnez une catégorie --'
    });
  }

  function reloadStats() {
    var typeCode = $('#filter-type-depense').val();
    var dateDebut = $('#filter-date-debut').val();
    var dateFin = $('#filter-date-fin').val();

    var params = [];
    if (typeCode) params.push('type_depense_code=' + encodeURIComponent(typeCode));
    if (dateDebut) params.push('date_debut=' + encodeURIComponent(dateDebut));
    if (dateFin) params.push('date_fin=' + encodeURIComponent(dateFin));

    var url = '<?= RACINE ?>depense/apiStats' + (params.length ? '?' + params.join('&') : '');

    $.getJSON(url, function(res) {
      if (res.status === 1 && res.stats) {
        $('#kpi-montant-approuve').text(Number(res.stats.montant_approuve || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-count-approuve').text((res.stats.count_approuve || 0) + ' dépense(s)');
        $('#kpi-montant-attente').text(Number(res.stats.montant_en_attente || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-count-attente').text((res.stats.count_en_attente || 0) + ' dépense(s)');
        $('#kpi-montant-annule').text(Number(res.stats.montant_annule || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#kpi-count-annule').text((res.stats.count_annule || 0) + ' dépense(s)');
      }
    });
  }

  var table = $('#table-depenses').DataTable({
    ajax: {
      url: '<?= RACINE ?>depense/apiList',
      type: 'GET',
      data: function(d) {
        d.type_depense_code = $('#filter-type-depense').val();
        d.date_debut = $('#filter-date-debut').val();
        d.date_fin = $('#filter-date-fin').val();
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: null, width: '40px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},
      { data: 'code_depense', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px;">' + d + '</code>';
      }},
      { data: 'libelle_type_depense', render: function(d) {
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; border:1px solid #DBEAFE; font-weight:700; padding:5px 10px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="tag" style="width:12px;height:12px;color:#2563EB;"></i> ' + (d || 'Général') + '</span>';
      }},
      { data: 'montant_depense', className: 'text-end', render: function(d) {
        var num = parseFloat(d) || 0;
        return '<strong style="color:#991B1B; font-size:14px;">' + num.toLocaleString('fr-FR') + ' FCFA</strong>';
      }},
      { data: 'periode_depense', render: function(d) {
        if (!d) return '-';
        var parts = d.split(' ');
        var dateParts = parts[0].split('-');
        if (dateParts.length === 3) {
          return '<span style="color:#475569; font-weight:600; font-size:12.5px;"><i data-lucide="calendar" style="width:13px;height:13px;display:inline;margin-right:4px;"></i>' + dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] + '</span>';
        }
        return d;
      }},
      { data: null, render: function(d) {
        var auteur = d.auteur_nom_complet ? d.auteur_nom_complet.trim() : '-';
        var confirmateur = d.confirmateur_nom_complet ? d.confirmateur_nom_complet.trim() : '';
        var html = '<div style="font-size:12px; color:#0F172A; font-weight:700;"><i data-lucide="user" style="width:12px;height:12px;color:#64748B;"></i> ' + auteur + '</div>';
        if (confirmateur && d.statut_depense !== 'en_attente') {
          var label = (d.statut_depense === 'approuve') ? 'Validé par' : 'Annulé par';
          html += '<div style="font-size:10.5px; color:#64748B; font-weight:600;">' + label + ' : ' + confirmateur + '</div>';
        }
        return html;
      }},
      { data: 'statut_depense', width: '130px', className: 'text-center', render: function(d) {
        if (d === 'approuve') {
          return '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:800; padding:6px 12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:13px;height:13px;"></i> Approuvé</span>';
        } else if (d === 'annule') {
          return '<span class="badge" style="background:#FEE2E2; color:#991B1B; font-weight:800; padding:6px 12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="x-circle" style="width:13px;height:13px;"></i> Annulé</span>';
        } else {
          return '<span class="badge" style="background:#FEF3C7; color:#B45309; font-weight:800; padding:6px 12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:13px;height:13px;"></i> En attente</span>';
        }
      }},
      { data: null, orderable: false, className: 'text-center no-print', render: function(d) {
        var descVal = $('<div>').text(d.description_depense || d.libelle_depense || '').html();
        var benVal = $('<div>').text(d.beneficiaire || '').html();
        var rawDate = d.periode_depense ? d.periode_depense.split(' ')[0] : '';
        var canValidate = <?= $canValidate ? 'true' : 'false' ?>;

        // 1. BOUTON ÉDITER (Icône uniquement) - Disponible UNIQUEMENT si la dépense est En attente
        var btnEdit = '';
        if (d.statut_depense === 'en_attente') {
          btnEdit = '<button type="button" class="btn btn-sm btn-secondary btn-edit-depense" ' +
                    'data-id="' + d.id_depense + '" ' +
                    'data-type="' + (d.type_depense_code || '') + '" ' +
                    'data-description="' + descVal + '" ' +
                    'data-montant="' + (d.montant_depense || '') + '" ' +
                    'data-mode="' + (d.mode_reglement || 'espece') + '" ' +
                    'data-beneficiaire="' + benVal + '" ' +
                    'data-date="' + rawDate + '" ' +
                    'data-piece="' + (d.piece_justificative || '') + '" ' +
                    'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; background:#64748B; border:none; color:#FFF; box-shadow:0 2px 4px rgba(100,116,139,0.25);" ' +
                    'title="Modifier la dépense (En attente)">' +
                    '<i data-lucide="edit" style="width:15px;height:15px;"></i></button>';
        } else {
          var labelStatut = (d.statut_depense === 'approuve') ? 'approuvée (statut définitif)' : 'annulée';
          btnEdit = '<button type="button" class="btn btn-sm btn-outline-secondary" disabled ' +
                    'style="width:32px; height:32px; padding:0; border-radius:8px; opacity:0.4; cursor:not-allowed; display:inline-flex; align-items:center; justify-content:center; border-color:#CBD5E1; color:#94A3B8;" ' +
                    'title="Impossible de modifier une dépense ' + labelStatut + '">' +
                    '<i data-lucide="lock" style="width:15px;height:15px;"></i></button>';
        }

        // 2. BOUTONS D'APPROBATION ET D'ANNULATION (Icônes uniquement)
        var btnStatus = '';
        if (d.statut_depense === 'en_attente') {
          if (canValidate) {
            btnStatus = '<button type="button" onclick="changerStatutDepense(' + d.id_depense + ', \'approuve\')" ' +
                        'class="btn btn-sm btn-success" ' +
                        'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; background:#16A34A; border:none; color:#FFF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(22,163,74,0.25);" ' +
                        'title="Approuver définitivement cette dépense">' +
                        '<i data-lucide="check" style="width:15px;height:15px;"></i></button> ' +
                        '<button type="button" onclick="changerStatutDepense(' + d.id_depense + ', \'annule\')" ' +
                        'class="btn btn-sm btn-danger" ' +
                        'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; background:#DC2626; border:none; color:#FFF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(220,38,38,0.25);" ' +
                        'title="Annuler cette dépense">' +
                        '<i data-lucide="x" style="width:15px;height:15px;"></i></button>';
          }
        } else if (d.statut_depense === 'approuve') {
          // Une dépense approuvée LE RESTE INDÉFINIMENT
          btnStatus = '<button type="button" class="btn btn-sm btn-success" disabled ' +
                      'style="width:32px; height:32px; padding:0; border-radius:8px; opacity:0.85; background:#16A34A; border:none; color:#FFF; cursor:not-allowed; display:inline-flex; align-items:center; justify-content:center;" ' +
                      'title="Dépense approuvée indéfiniment (Statut définitif non modifiable)">' +
                      '<i data-lucide="check-circle" style="width:15px;height:15px;"></i></button>';
        } else if (d.statut_depense === 'annule') {
          if (canValidate) {
            btnStatus = '<button type="button" onclick="changerStatutDepense(' + d.id_depense + ', \'en_attente\')" ' +
                        'class="btn btn-sm btn-outline-warning" ' +
                        'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:700; color:#B45309; border:1px solid #FCD34D; background:#FFFBEB; display:inline-flex; align-items:center; justify-content:center;" ' +
                        'title="Remettre en attente de validation">' +
                        '<i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i></button>';
          }
        }

        // 3. BOUTON DÉTAILS (Icône eye uniquement)
        var btnDetails = '<a href="' + window.RACINE + 'depense/details/' + (d.editId || d.id_depense) + '" ' +
                         'class="btn btn-sm btn-primary" ' +
                         'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; background:#1E3A5F; border-color:#1E3A5F; color:#FFF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(30,58,95,0.2);" ' +
                         'title="Consulter la fiche détails de la dépense">' +
                         '<i data-lucide="eye" style="width:15px;height:15px;"></i></a>';

        // 4. BOUTON PIÈCE JUSTIFICATIVE (Icône paperclip)
        var btnDoc = '';
        if (d.piece_justificative) {
          btnDoc = '<a href="' + window.RACINE + 'public/' + d.piece_justificative + '" target="_blank" ' +
                   'class="btn btn-sm btn-outline-info" ' +
                   'style="width:32px; height:32px; padding:0; border-radius:8px; font-weight:800; color:#0284C7; border:1px solid #BAE6FD; background:#F0F9FF; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(2,132,199,0.15);" ' +
                   'title="Consulter / Télécharger la pièce justificative (Max 3 Mo)">' +
                   '<i data-lucide="paperclip" style="width:15px;height:15px;"></i></a>';
        }

        return '<div style="display:flex; align-items:center; justify-content:center; gap:6px; flex-wrap:nowrap;">' +
               btnEdit +
               btnStatus +
               btnDetails +
               btnDoc +
               '</div>';
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Ouvrir la modale pour un NOUVEAU décaissement
  $('.btn-add-depense').on('click', function() {
    $('#depense_modal_id').val('');
    $('#form-depense-modal')[0].reset();
    if ($.fn.select2) {
      $('#depense_modal_type').val('').trigger('change');
    }
    $('#depense_modal_piece').val('');
    $('#piece-justificative-preview').html('');
    var today = new Date().toISOString().split('T')[0];
    $('#depense_modal_date').val(today);
    $('#modal-depense-title').html('<i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Nouveau Décaissement / Dépense');
    $('#modal-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#depense_modal_description').focus(); }, 100);
  });

  // Ouvrir la modale pour MODIFIER une dépense
  $(document).on('click', '.btn-edit-depense', function() {
    var id = $(this).data('id');
    var type = $(this).data('type');
    var description = $(this).data('description');
    var montant = $(this).data('montant');
    var mode = $(this).data('mode');
    var beneficiaire = $(this).data('beneficiaire');
    var date = $(this).data('date');
    var piece = $(this).data('piece');

    $('#depense_modal_id').val(id);
    if ($.fn.select2) {
      $('#depense_modal_type').val(type).trigger('change');
    } else {
      $('#depense_modal_type').val(type);
    }
    $('#depense_modal_description').val(description);
    $('#depense_modal_montant').val(montant);
    $('#depense_modal_mode').val(mode || 'espece');
    $('#depense_modal_beneficiaire').val(beneficiaire);
    $('#depense_modal_date').val(date);
    $('#depense_modal_piece').val('');

    if (piece) {
      $('#piece-justificative-preview').html('<a href="' + window.RACINE + 'public/' + piece + '" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="paperclip" style="width:13px;height:13px;"></i> Voir la pièce justificative actuelle</a>');
    } else {
      $('#piece-justificative-preview').html('');
    }

    $('#modal-depense-title').html('<i data-lucide="edit" style="width: 20px; height: 20px;"></i> Modifier la Dépense');
    $('#modal-depense').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { $('#depense_modal_description').focus(); }, 100);
  });

  // Fermer la modale
  $('.btn-close-modal-depense').on('click', function() {
    $('#modal-depense').css('display', 'none');
  });
  $('#modal-depense').on('click', function(e) {
    if ($(e.target).is('#modal-depense')) {
      $(this).css('display', 'none');
    }
  });

  // Soumission AJAX du formulaire de modale
  $('#form-depense-modal').on('submit', function(e) {
    e.preventDefault();

    // Contrôle client de la taille de la pièce justificative (Max 3 Mo)
    var fileInput = $('#depense_modal_piece')[0];
    if (fileInput && fileInput.files && fileInput.files[0]) {
      var file = fileInput.files[0];
      var maxBytes = 3 * 1024 * 1024; // 3 Mo
      if (file.size > maxBytes) {
        var currentMb = (file.size / (1024 * 1024)).toFixed(2);
        showNotify('La pièce justificative dépasse la taille maximale autorisée de 3 Mo (Fichier actuel : ' + currentMb + ' Mo)', 'error');
        return false;
      }
    }

    var isEdit = !!$('#depense_modal_id').val();
    var url = isEdit ? '<?= RACINE ?>depense/edit' : '<?= RACINE ?>depense/add';
    var $btn = $('#btn-save-depense-modal');
    var formData = new FormData(this);

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enregistrement...');

    $.ajax({
      url: url,
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Dépense');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Décaissement enregistré avec succès !', 'success');
          $('#modal-depense').css('display', 'none');
          table.ajax.reload(null, false);
          reloadStats();
        } else {
          showNotify(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 18px; height: 18px;"></i> Enregistrer la Dépense');
        if (window.lucide) lucide.createIcons();

        var msg = 'Erreur réseau ou serveur';
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.message) msg = json.message;
        } catch(e) {}
        showNotify(msg, 'error');
      }
    });
  });

  window.changerStatutDepense = function(id, newStatut) {
    var labels = {
      'approuve': 'approuver',
      'annule': 'annuler',
      'en_attente': 'remettre en attente'
    };
    var actionMsg = labels[newStatut] || 'modifier le statut de';
    if (!confirm('Voulez-vous vraiment ' + actionMsg + ' cette dépense ?')) {
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>depense/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: newStatut,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          showNotify(res.message || 'Statut mis à jour avec succès', 'success');
          table.ajax.reload(null, false);
          reloadStats();
        } else {
          showNotify(res.message || 'Erreur lors du changement de statut', 'error');
        }
      },
      error: function() {
        showNotify('Erreur réseau ou permission insuffisante', 'error');
      }
    });
  };

  $('#filter-type-depense, #filter-date-debut, #filter-date-fin').on('change input', function() {
    table.ajax.reload();
    reloadStats();
  });

  $('#btn-reset-filters').on('click', function() {
    if ($.fn.select2 && $('#filter-type-depense').data('select2')) {
      $('#filter-type-depense').val('').trigger('change.select2');
    } else {
      $('#filter-type-depense').val('');
    }
    $('#filter-date-debut').val('');
    $('#filter-date-fin').val('');
    table.ajax.reload();
    reloadStats();
  });

});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
