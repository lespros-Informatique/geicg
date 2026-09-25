<?php 
require_once __DIR__ . '/../../public/inc/header.php'; 
$annees = $annees ?? [];
$niveaux = $niveaux ?? [];
$filieres = $filieres ?? [];
$classes = $classes ?? [];
$anneeActive = $anneeActive ?? '';
?>
<style>
.btn-action-icon {
  width: 32px;
  height: 32px;
  min-width: 32px;
  padding: 0;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  cursor: pointer;
  text-decoration: none;
  box-sizing: border-box;
}
.btn-action-icon:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.btn-action-icon i, .btn-action-icon svg {
  width: 15px;
  height: 15px;
  stroke: currentColor;
}
.btn-action-classe {
  background: #FFFBEB;
  border: 1px solid #FCD34D;
  color: #D97706;
}
.btn-action-classe:hover {
  background: #F59E0B;
  border-color: #F59E0B;
  color: #FFFFFF;
}
.btn-action-print {
  background: #F0FDF4;
  border: 1px solid #86EFAC;
  color: #166534;
}
.btn-action-print:hover {
  background: #16A34A;
  border-color: #16A34A;
  color: #FFFFFF;
}
.btn-action-dossier {
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  color: #1D4ED8;
}
.btn-action-dossier:hover {
  background: #1E3A5F;
  border-color: #1E3A5F;
  color: #FFFFFF;
}
.btn-action-edit {
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  color: #475569;
}
.btn-action-edit:hover {
  background: #475569;
  border-color: #475569;
  color: #FFFFFF;
}
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
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="user-plus" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Nouveaux Étudiants
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation du registre et enregistrement des nouveaux dossiers étudiants</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
          <button onclick="window.print()" class="btn btn-outline-secondary" style="border: 1.5px solid #CBD5E1; color: #334155; background: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;" title="Imprimer la liste des étudiants">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer
          </button>
          <a href="<?= RACINE ?>etudiant/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouveau Dossier Étudiant
          </a>
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
            <select id="filter-filiere" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
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
            <select id="filter-niveau" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
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
            <select id="filter-classe" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 13px; font-weight: 600; background: #F8FAFC;">
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

        </div>
      </div>

      <!-- TABLEAU DU REGISTRE DES ÉTUDIANTS (Colonnes intelligentes) -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-etudiants" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 45px;">#</th>
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

<!-- ========================================================================= -->
<!-- MODAL SUR MESURE : CHANGEMENT DE CLASSE D'UN ÉTUDIANT                     -->
<!-- ========================================================================= -->
<div id="modal-changer-classe" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; animation: modalZoomIn 0.2s ease-out;">
    
    <!-- En-tête modal -->
    <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F233D 100%); color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #F59E0B;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245,158,11,0.2); border: 1.5px solid #FBBF24; display: flex; align-items: center; justify-content: center;">
          <i data-lucide="refresh-cw" style="width: 20px; height: 20px; color: #FBBF24;"></i>
        </div>
        <div>
          <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF;">Changer la Classe de l'Étudiant</h3>
          <div style="font-size: 12px; color: #94A3B8; margin-top: 2px;">Affectation académique & Réinscription</div>
        </div>
      </div>
      <button type="button" class="btn-close-modal-change-classe" style="background: transparent; border: none; color: #FFFFFF; font-size: 26px; cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
    </div>

    <!-- Formulaire modal -->
    <form id="form-changer-classe" style="padding: 22px 24px; margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" id="change_classe_inscription_code" name="code_inscription" value="">
      <input type="hidden" id="change_classe_etudiant_code" name="code_etudiant" value="">

      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 18px;">
        <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Étudiant Concerné</div>
        <div id="change_classe_student_name" style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 3px;">Nom Étudiant</div>
      </div>

      <div class="form-group" style="margin-bottom: 20px;">
        <label style="display: block; font-size: 12.5px; font-weight: 800; color: #1E3A5F; margin-bottom: 6px;">
          Nouvelle Classe Affectée <span style="color: #EF4444;">*</span>
        </label>
        <select id="change_classe_new_code" name="classe_code" required class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 700; font-size: 14px; color: #0F172A; background: #FFFFFF;">
          <option value="">-- Sélectionner la nouvelle classe --</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= htmlspecialchars($c['code_classe']) ?>">
              <?= htmlspecialchars($c['libelle_classe']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
        <button type="button" class="btn btn-secondary btn-close-modal-change-classe" style="font-weight: 700; border-radius: 8px; padding: 9px 18px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-change-classe" class="btn btn-warning" style="background: #F59E0B; border: none; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 9px 22px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(245,158,11,0.3);">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider le Changement
        </button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  
  // Instance DataTable avec colonnes intelligentes
  var table = $('#table-etudiants').DataTable({
    order: [],
    ajax: {
      url: '<?= RACINE ?>etudiant/apiList',
      type: 'GET',
      data: function(d) {
        d.annee_code = $('#filter-annee').val();
        d.filiere_code = $('#filter-filiere').val();
        d.niveau_code = $('#filter-niveau').val();
        d.classe_code = $('#filter-classe').val();
        d.affectation_etat = $('#filter-regime').val();
      }
    },
    processing: true,
    autoWidth: false,
    pageLength: 25,
    columns: [
      // 0. # Incrémenté
      { data: null, width: '45px', render: function(d, type, row, meta) {
        return '<span style="font-weight:700; color:#64748B;">' + (meta.row + 1 + (meta.settings._iDisplayStart || 0)) + '</span>';
      }},

      // 1. Matricule
      { data: 'matricule_etudiant', width: '110px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#1E3A5F; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 8px; border-radius:6px; font-size:12px;">' + d + '</code>';
      }},

      // 2. Nom & Prénoms
      { data: null, render: function(d, type, row) {
        var nom = (row.nom_etudiant || '').toUpperCase();
        var prenom = row.prenom_etudiant || '';
        return '<div style="font-weight:700; color:#0F172A; font-size:13px;">' + nom + ' ' + prenom + '</div>';
      }},

      // 3. Sexe
      { data: 'sexe_etudiant', width: '80px', render: function(d) {
        if (!d) return '-';
        var isFem = (d.toLowerCase().indexOf('f') !== -1);
        var bg = isFem ? '#FDF2F8' : '#EFF6FF';
        var col = isFem ? '#DB2777' : '#2563EB';
        var border = isFem ? '#FBCFE8' : '#BFDBFE';
        return '<span style="background:' + bg + '; color:' + col + '; border:1px solid ' + border + '; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700;">' + d + '</span>';
      }},

      // 4. Régime (Affecté / Non Affecté)
      { data: 'affectation_etat', width: '120px', render: function(d) {
        var isAffecte = (d === 'affecte' || d === 'oui');
        if (isAffecte) {
          return '<span style="background:#DCFCE7; color:#15803D; border:1.5px solid #86EFAC; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#16A34A;"></i> Affecté(e)</span>';
        }
        return '<span style="background:#DBEAFE; color:#1D4ED8; border:1.5px solid #BFDBFE; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="user-check" style="width:12px;height:12px;color:#2563EB;"></i> Non Affecté(e)</span>';
      }},

      // 4. Classe (Indépendante)
      { data: 'libelle_classe', defaultContent: '-', render: function(d) {
        if (!d) return '<span style="color:#94A3B8; font-style:italic;">Non assigné</span>';
        return '<strong style="color:#0F172A; font-size:12px; display:inline-flex; align-items:center; gap:5px;"><i data-lucide="graduation-cap" style="width:14px;height:14px;color:#D97706;"></i> ' + d + '</strong>';
      }},

      // 5. Filière (Indépendante)
      { data: 'libelle_filiere', defaultContent: '-', render: function(d, type, row) {
        var useSlug = row.use_slug_filiere !== undefined ? row.use_slug_filiere : (<?= !empty($useSlugFiliere) ? 'true' : 'false' ?>);
        var slug = row.slug_filiere || '';
        var libelle = d || row.libelle_filiere || row.code_filiere || '-';

        if (useSlug && slug) {
          return '<span style="font-size:12.5px; font-weight:800; color:#1E3A5F;" title="' + libelle + '">' + slug + '</span>';
        }

        return '<span style="font-size:12px; font-weight:600; color:#1E293B;">' + libelle + '</span>';
      }},

      // 6. Niveau (Indépendant)
      { data: 'libelle_niveau', defaultContent: '-', render: function(d, type, row) {
        if (!d && !row.slug_niveau) return '<span style="color:#94A3B8;">-</span>';
        var useSlugNiv = row.use_slug_niveau !== undefined ? row.use_slug_niveau : (<?= !empty($useSlugNiveau) ? 'true' : 'false' ?>);
        var nivVal = (useSlugNiv && row.slug_niveau) ? row.slug_niveau : (d || row.slug_niveau || '-');
        return '<span style="background:#F1F5F9; color:#475569; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:700;" title="' + (d || '') + '">' + nivVal + '</span>';
      }},

      // 9. Actions
      { data: null, width: '160px', orderable: false, render: function(d, type, row) {
        var idCrypte = d.editId || d.id_etudiant;
        var nomComplet = (row.nom_etudiant || '').toUpperCase() + ' ' + (row.prenom_etudiant || '');
        var insCode = row.code_inscription || '';
        var etuCode = row.code_etudiant || '';
        var clsCode = row.code_classe || '';

        return '<div style="display:inline-flex; align-items:center; gap:5px; justify-content:center;">' +
               '  <button type="button" class="btn-action-icon btn-action-classe btn-change-classe" data-id="' + idCrypte + '" data-inscription="' + escapeHtml(insCode) + '" data-etudiant="' + escapeHtml(etuCode) + '" data-nom="' + escapeHtml(nomComplet) + '" data-classe="' + escapeHtml(clsCode) + '" title="Changer la classe de l\'étudiant"><i data-lucide="refresh-cw"></i></button>' +
               '  <a href="' + window.RACINE + 'etudiant/details/' + idCrypte + '?print=1" target="_blank" class="btn-action-icon btn-action-print" title="Imprimer le dossier / fiche"><i data-lucide="printer"></i></a>' +
               '  <a href="' + window.RACINE + 'etudiant/details/' + idCrypte + '" class="btn-action-icon btn-action-dossier" title="Consulter le dossier complet"><i data-lucide="eye"></i></a>' +
               '  <a href="' + window.RACINE + 'etudiant/edition/' + idCrypte + '" class="btn-action-icon btn-action-edit" title="Modifier la fiche étudiant"><i data-lucide="edit"></i></a>' +
               '</div>';
      }, className: 'text-center' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Ouverture du modal de changement de classe
  $(document).on('click', '.btn-change-classe', function() {
    var inscrCode = $(this).data('inscription') || '';
    var etuCode = $(this).data('etudiant') || '';
    var nom = $(this).data('nom') || 'Étudiant';
    var currentClasse = $(this).data('classe') || '';

    $('#change_classe_inscription_code').val(inscrCode);
    $('#change_classe_etudiant_code').val(etuCode);
    $('#change_classe_student_name').text(nom);
    $('#change_classe_new_code').val(currentClasse);

    $('#modal-changer-classe').css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('.btn-close-modal-change-classe').on('click', function() {
    $('#modal-changer-classe').hide();
  });

  $('#modal-changer-classe').on('click', function(e) {
    if ($(e.target).is('#modal-changer-classe')) {
      $('#modal-changer-classe').hide();
    }
  });

  // Soumission AJAX du formulaire de changement de classe
  $('#form-changer-classe').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-change-classe');
    $btn.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement...');

    $.ajax({
      url: '<?= RACINE ?>etudiant/changerClasse',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider le Changement');
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Classe modifiée avec succès');
          $('#modal-changer-classe').hide();
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de la modification');
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider le Changement');
        if (window.toastr) toastr.error('Erreur réseau lors de la mise à jour');
      }
    });
  });

  // Gestion intelligente de la visibilité des colonnes
  function updateSmartColumns() {
    var selFil = $('#filter-filiere').val();
    var selNiv = $('#filter-niveau').val();
    var selCls = $('#filter-classe').val();

    // Colonne 5 : Classe -> masquée si une classe spécifique est sélectionnée
    table.column(5).visible(selCls === 'ALL' || !selCls);

    // Colonne 6 : Filière -> masquée si une filière spécifique est sélectionnée
    table.column(6).visible(selFil === 'ALL' || !selFil);

    // Colonne 7 : Niveau -> masquée si un niveau spécifique est sélectionné
    table.column(7).visible(selNiv === 'ALL' || !selNiv);
  }

  // Déclenchement automatique du rechargement et des colonnes intelligentes
  $('#filter-annee, #filter-filiere, #filter-niveau, #filter-classe, #filter-regime').on('change', function() {
    filterClassDropdown();
    updateSmartColumns();
    table.ajax.reload();
  });

  // Filtrage en cascade du menu des classes selon la filière et le niveau choisis
  function filterClassDropdown() {
    var selFil = $('#filter-filiere').val();
    var selNiv = $('#filter-niveau').val();
    var selAnn = $('#filter-annee').val();

    $('#filter-classe option').each(function() {
      var optVal = $(this).val();
      if (optVal === 'ALL') return;

      var optFil = $(this).data('filiere');
      var optNiv = $(this).data('niveau');
      var optAnn = $(this).data('annee');

      var matchFil = (selFil === 'ALL' || !selFil || optFil === selFil);
      var matchNiv = (selNiv === 'ALL' || !selNiv || optNiv === selNiv);
      var matchAnn = (selAnn === 'ALL' || !selAnn || optAnn === selAnn);

      if (matchFil && matchNiv && matchAnn) {
        $(this).show();
      } else {
        $(this).hide();
        if ($('#filter-classe').val() === optVal) {
          $('#filter-classe').val('ALL');
        }
      }
    });
  }

  // Réinitialisation des filtres
  $('#btn-reset-filters').on('click', function() {
    $('#filter-annee').val('<?= $anneeActive ?>');
    $('#filter-filiere').val('ALL');
    $('#filter-niveau').val('ALL');
    $('#filter-classe').val('ALL');
    $('#filter-regime').val('ALL');
    filterClassDropdown();
    updateSmartColumns();
    table.ajax.reload();
  });

  // Bascule instantanée du statut étudiant
  $(document).on('change', '.toggle-statut-etudiant', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>etudiant/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
