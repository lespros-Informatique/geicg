<?php
require_once __DIR__ . '/../../public/inc/header.php';

$studentStats = $studentStats ?? [];
$studentHistory = $studentHistory ?? [];
$encryptedId = $encryptedId ?? '';
$encryptedEtudiantId = $encryptedEtudiantId ?? '';

$justVal = strtolower((string)($item['justifiee'] ?? ($item['justifie'] ?? '')));
$isJustifie = ($justVal === '1' || $justVal === STATUTS::JUSTIFIE);

$nomComplet = trim((string)($item['nom_etudiant'] ?? ''));
if ($nomComplet === '') {
    $nomComplet = trim((string)($item['etu_nom'] ?? '') . ' ' . (string)($item['etu_prenom'] ?? ''));
}
if ($nomComplet === '') {
    $nomComplet = (string)($item['code_etudiant'] ?? ($item['inscription_code'] ?? '-'));
}

// Initiales pour l'avatar étudiant
$parts = preg_split('/\s+/', trim($nomComplet)) ?: [];
$initials = '';
foreach (array_slice($parts, 0, 2) as $p) {
    if ($p !== '') {
        $initials .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
    }
}
if ($initials === '') {
    $initials = 'ET';
}

$fmtHeures = static function ($h): string {
    $val = (float)$h;
    return (fmod($val, 1.0) === 0.0) ? ((int)$val . ' h') : (number_format($val, 1, ',', ' ') . ' h');
};

$joursFr = [
    1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
    5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
];
$dateAbsenceRaw = $item['date_absence'] ?? '';
$dateAbsenceFmt = '-';
$jourAbsenceFr = '';
if (!empty($dateAbsenceRaw)) {
    $ts = strtotime($dateAbsenceRaw);
    if ($ts !== false) {
        $dateAbsenceFmt = date('d/m/Y', $ts);
        $jourAbsenceFr = $joursFr[(int)date('N', $ts)] ?? '';
    }
}

$dateSaisieFmt = !empty($item['created_at_absence']) ? date('d/m/Y à H:i', strtotime($item['created_at_absence'])) : '';
$dureeHeures = (float)($item['duree_heures'] ?? 0);
$codeAbsence = (string)($item['code_absence'] ?? ('#' . ($item['id_absence'] ?? '')));
$motifText = trim((string)($item['motif_absence'] ?? ($item['motif'] ?? '')));

$canManageAbsences = Auth::can(PERMISSIONS::MANAGE_ABSENCES);
$canViewEtudiants = Auth::can(PERMISSIONS::VIEW_ETUDIANTS);

$annuelHeures = (float)($studentStats['annuel_heures'] ?? 0);
$annuelJustHeures = (float)($studentStats['annuel_justifiees_heures'] ?? 0);
$annuelInjustHeures = (float)($studentStats['annuel_injustifiees_heures'] ?? 0);
$annuelCount = (int)($studentStats['annuel_count'] ?? 0);
$semCount = (int)($studentStats['semestre_count'] ?? 0);
$semHeures = (float)($studentStats['semestre_heures'] ?? 0);
$semInjustHeures = (float)($studentStats['semestre_injustifiees_heures'] ?? 0);
$pctJustifie = $annuelHeures > 0 ? min(100, round(($annuelJustHeures / $annuelHeures) * 100)) : 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: <?= $isJustifie ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $isJustifie ? '#15803D' : '#B91C1C' ?>; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="<?= $isJustifie ? 'user-check' : 'user-x' ?>" style="width: 20px; height: 20px;"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche d'Absence</h1>
            <code style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 3px 9px; border-radius: 6px;"><?= htmlspecialchars($codeAbsence) ?></code>
            <?php if ($isJustifie): ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> Justifiée
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;">
                <i data-lucide="alert-circle" style="width: 14px; height: 14px;"></i> Non justifiée
              </span>
            <?php endif; ?>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 8px 0 0 0;">
            Étudiant : <strong style="color: #0F172A;"><?= htmlspecialchars($nomComplet) ?></strong>
            &bull; Classe : <strong style="color: #1E3A5F;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></strong>
            <?php if (!empty($item['libelle_annee'])): ?>
              &bull; Année : <strong style="color: #0F172A;"><?= htmlspecialchars($item['libelle_annee']) ?></strong>
            <?php endif; ?>
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url('absence/list') ?>" style="background: #FFFFFF; color: #334155; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
          </a>
          <?php if ($canViewEtudiants && $encryptedEtudiantId !== ''): ?>
            <a href="<?= url('etudiant/details/' . $encryptedEtudiantId) ?>" style="background: #F8FAFC; color: #1E3A5F; border: 1.5px solid #BFDBFE; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; text-decoration: none;">
              <i data-lucide="user" style="width: 16px; height: 16px;"></i> Dossier Étudiant
            </a>
          <?php endif; ?>
          <?php if ($canManageAbsences): ?>
            <button type="button" id="btn-toggle-justifiee-detail" data-target-status="<?= $isJustifie ? STATUTS::NON_JUSTIFIE : STATUTS::JUSTIFIE ?>" style="background: <?= $isJustifie ? '#FFFBEB' : '#16A34A' ?>; color: <?= $isJustifie ? '#B45309' : '#FFFFFF' ?>; border: 1.5px solid <?= $isJustifie ? '#FCD34D' : '#16A34A' ?>; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 15px; cursor: pointer;">
              <i data-lucide="<?= $isJustifie ? 'x-circle' : 'check-circle' ?>" style="width: 16px; height: 16px;"></i>
              <?= $isJustifie ? 'Marquer non justifiée' : 'Justifier cette absence' ?>
            </button>
            <a href="<?= url('absence/edition/' . $encryptedId) ?>" style="background: #1E3A5F; color: #FFFFFF; border: 1.5px solid #1E3A5F; display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px; border-radius: 8px; padding: 9px 16px; text-decoration: none;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DE 4 CARTES DE SYNTHÈSE -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 22px;">

        <!-- Carte 1 : Étudiant & Coordonnées -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex-shrink: 0;">
              <?= htmlspecialchars($initials) ?>
            </div>
            <div style="min-width: 0;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Étudiant concerné</div>
              <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 2px; word-break: break-word;"><?= htmlspecialchars($nomComplet) ?></div>
              <div style="margin-top: 3px;">
                <code style="font-size: 11.5px; font-weight: 700; color: #334155; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; color: #475569;">
            <div style="display: flex; align-items: center; gap: 7px;">
              <i data-lucide="phone" style="width: 14px; height: 14px; color: #64748B; flex-shrink: 0;"></i>
              <?php if (!empty($item['telephone_etudiant'])): ?>
                <a href="tel:<?= htmlspecialchars($item['telephone_etudiant']) ?>" style="color: #0F172A; font-weight: 600; text-decoration: none;"><?= htmlspecialchars($item['telephone_etudiant']) ?></a>
              <?php else: ?>
                <span style="color: #94A3B8;">Téléphone non renseigné</span>
              <?php endif; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 7px; min-width: 0;">
              <i data-lucide="mail" style="width: 14px; height: 14px; color: #64748B; flex-shrink: 0;"></i>
              <?php if (!empty($item['email_etudiant'])): ?>
                <a href="mailto:<?= htmlspecialchars($item['email_etudiant']) ?>" style="color: #0284C7; font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($item['email_etudiant']) ?></a>
              <?php else: ?>
                <span style="color: #94A3B8;">Email non renseigné</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Carte 2 : Cursus, Semestre & Matière -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="book-open" style="width: 20px; height: 20px;"></i>
            </div>
            <div style="min-width: 0;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Matière manquée</div>
              <div style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin-top: 2px; line-height: 1.3;">
                <?= htmlspecialchars(!empty($item['libelle_matiere']) ? $item['libelle_matiere'] : (!empty($item['matiere_code']) ? $item['matiere_code'] : 'Non spécifiée')) ?>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; color: #475569;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
              <span>Classe :</span>
              <strong style="color: #1E3A5F;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
              <span>Filière / Niveau :</span>
              <span style="color: #0F172A; font-weight: 600; text-align: right;"><?= htmlspecialchars(trim(($item['libelle_filiere'] ?? '') . (!empty($item['libelle_niveau']) ? ' • ' . $item['libelle_niveau'] : '')) ?: '-') ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
              <span>Semestre :</span>
              <span style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE; padding: 2px 9px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                <?= htmlspecialchars($item['libelle_semestre'] ?? ($item['semestre_code'] ?? '-')) ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Carte 3 : Date, Volume & Horodatage -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="calendar-clock" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Date de l'absence</div>
              <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($dateAbsenceFmt) ?></div>
              <?php if ($jourAbsenceFr !== ''): ?>
                <div style="font-size: 12px; color: #64748B; font-weight: 600;"><?= htmlspecialchars($jourAbsenceFr) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; color: #475569;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span>Volume manqué :</span>
              <span style="background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; padding: 2px 10px; border-radius: 6px; font-weight: 800; font-size: 12.5px;"><?= htmlspecialchars($fmtHeures($dureeHeures)) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span>Enregistrée le :</span>
              <span style="color: #0F172A; font-weight: 600;"><?= htmlspecialchars($dateSaisieFmt ?: '-') ?></span>
            </div>
          </div>
        </div>

        <!-- Carte 4 : Assiduité cumulée de l'étudiant -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $annuelInjustHeures > 0 ? '#FEE2E2' : '#DCFCE7' ?>; color: <?= $annuelInjustHeures > 0 ? '#B91C1C' : '#15803D' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i data-lucide="bar-chart-3" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px;">Cumul Annuel Étudiant</div>
              <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 2px;">
                <?= htmlspecialchars($fmtHeures($annuelHeures)) ?>
                <span style="font-size: 12px; font-weight: 600; color: #64748B;">(<?= $annuelCount ?> abs.)</span>
              </div>
            </div>
          </div>
          <div style="border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; flex-direction: column; gap: 7px; font-size: 12px; color: #475569;">
            <?php if (!empty($item['semestre_code'])): ?>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?= htmlspecialchars($item['libelle_semestre'] ?? 'Semestre') ?> :</span>
                <strong style="color: #0F172A;"><?= htmlspecialchars($fmtHeures($semHeures)) ?> <span style="color: #64748B; font-weight: 500;">(<?= $semCount ?>)</span></strong>
              </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span>Justifiées / Injustifiées :</span>
              <span>
                <strong style="color: #15803D;"><?= htmlspecialchars($fmtHeures($annuelJustHeures)) ?></strong>
                /
                <strong style="color: #B91C1C;"><?= htmlspecialchars($fmtHeures($annuelInjustHeures)) ?></strong>
              </span>
            </div>
            <div style="width: 100%; height: 6px; background: #FEE2E2; border-radius: 99px; overflow: hidden;" title="<?= $pctJustifie ?>% d'heures justifiées">
              <div style="width: <?= $pctJustifie ?>%; height: 100%; background: #16A34A; border-radius: 99px;"></div>
            </div>
          </div>
        </div>

      </div>

      <!-- CARTE MOTIF & JUSTIFICATIF -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; border-left: 4px solid <?= $isJustifie ? '#16A34A' : '#DC2626' ?>; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 22px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
          <h3 style="font-size: 14.5px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="message-square-text" style="width: 18px; height: 18px; color: <?= $isJustifie ? '#16A34A' : '#DC2626' ?>;"></i>
            Motif & Observation
          </h3>
          <?php if ($canManageAbsences && $motifText === ''): ?>
            <a href="<?= url('absence/edition/' . $encryptedId) ?>" style="font-size: 12.5px; font-weight: 700; color: #0284C7; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
              <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i> Renseigner un motif
            </a>
          <?php endif; ?>
        </div>
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 16px; font-size: 13.5px; color: <?= $motifText !== '' ? '#0F172A' : '#94A3B8' ?>; line-height: 1.6; <?= $motifText === '' ? 'font-style: italic;' : '' ?>">
          <?= $motifText !== '' ? nl2br(htmlspecialchars($motifText)) : "Aucun motif ni observation n'a été renseigné pour cette absence." ?>
        </div>
      </div>

      <!-- CARTE HISTORIQUE DES ABSENCES DE L'ÉTUDIANT SUR L'ANNÉE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 22px 26px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="history" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
              Historique des Absences de <?= htmlspecialchars($nomComplet) ?>
              <?php if (!empty($item['libelle_annee'])): ?>
                <span style="font-size: 12px; font-weight: 700; color: #64748B;">(<?= htmlspecialchars($item['libelle_annee']) ?>)</span>
              <?php endif; ?>
            </h3>
          </div>
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="background: #F1F5F9; color: #334155; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">
              <?= $annuelCount ?> séance<?= $annuelCount > 1 ? 's' : '' ?>
            </span>
            <span style="background: #EFF6FF; color: #1E3A5F; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">
              Total : <?= htmlspecialchars($fmtHeures($annuelHeures)) ?>
            </span>
            <?php if ($annuelInjustHeures > 0): ?>
              <span style="background: #FEE2E2; color: #B91C1C; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                Injustifiées : <?= htmlspecialchars($fmtHeures($annuelInjustHeures)) ?>
              </span>
            <?php endif; ?>
            <?php if (Auth::can(PERMISSIONS::VIEW_ABSENCES) && $encryptedId !== ''): ?>
              <a href="<?= url('absence/imprimerHistoriqueEtudiant/' . $encryptedId) ?>" target="_blank" style="background: #FFFFFF; color: #1E3A5F; border: 1.5px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 12.5px; border-radius: 8px; padding: 6px 13px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04);" title="Imprimer l'historique des absences de l'étudiant en PDF">
                <i data-lucide="printer" style="width: 15px; height: 15px;"></i> Imprimer
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if (empty($studentHistory)): ?>
          <div style="padding: 24px; text-align: center; color: #94A3B8; font-size: 13px; font-style: italic;">
            Aucune autre absence enregistrée pour cet étudiant sur cette année académique.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 11.5px; text-transform: uppercase;">
                  <th style="padding: 10px 12px; width: 42px;">#</th>
                  <th style="padding: 10px 12px;">Date</th>
                  <th style="padding: 10px 12px;">Semestre</th>
                  <th style="padding: 10px 12px;">Matière</th>
                  <th style="padding: 10px 12px; text-align: center;">Volume</th>
                  <th style="padding: 10px 12px; text-align: center;">Statut</th>
                  <th style="padding: 10px 12px;">Motif</th>
                  <th style="padding: 10px 12px; text-align: right; width: 90px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($studentHistory as $idx => $h):
                  $isCurrent = ((int)$h['id_absence'] === (int)($item['id_absence'] ?? 0));
                  $hJust = (($h['justifiee'] ?? 'non') === STATUTS::JUSTIFIE);
                  $hMotif = trim((string)($h['motif_absence'] ?? ($h['motif'] ?? '')));
                ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; <?= $isCurrent ? 'background: #EFF6FF;' : '' ?>">
                    <td style="padding: 11px 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                    <td style="padding: 11px 12px; font-weight: 700; color: #0F172A; white-space: nowrap;">
                      <?= !empty($h['date_absence']) ? date('d/m/Y', strtotime($h['date_absence'])) : '-' ?>
                      <?php if ($isCurrent): ?>
                        <span style="margin-left: 6px; background: #1E3A5F; color: #FFFFFF; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">Actuelle</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; white-space: nowrap;">
                      <span style="background: #F1F5F9; color: #1E3A5F; border: 1px solid #CBD5E1; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                        <?= htmlspecialchars($h['libelle_semestre'] ?? ($h['semestre_code'] ?? '-')) ?>
                      </span>
                    </td>
                    <td style="padding: 11px 12px; color: #334155; font-weight: 600;">
                      <?= htmlspecialchars(!empty($h['libelle_matiere']) ? $h['libelle_matiere'] : (!empty($h['matiere_code']) ? $h['matiere_code'] : '-')) ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; font-weight: 700; color: #0F172A; white-space: nowrap;">
                      <?= htmlspecialchars($fmtHeures($h['duree_heures'] ?? 0)) ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: center; white-space: nowrap;">
                      <?php if ($hJust): ?>
                        <span style="background: #DCFCE7; color: #15803D; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11.5px;">Justifiée</span>
                      <?php else: ?>
                        <span style="background: #FEE2E2; color: #B91C1C; padding: 3px 9px; border-radius: 12px; font-weight: 700; font-size: 11.5px;">Non justifiée</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 11px 12px; color: #64748B; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($hMotif) ?>">
                      <?= $hMotif !== '' ? htmlspecialchars($hMotif) : '-' ?>
                    </td>
                    <td style="padding: 11px 12px; text-align: right;">
                      <?php if (!$isCurrent && !empty($h['encrypted_id'])): ?>
                        <a href="<?= url('absence/details/' . $h['encrypted_id']) ?>" title="Consulter cette absence" aria-label="Consulter cette absence" style="width: 30px; height: 30px; border-radius: 7px; border: 1px solid #BAE6FD; background: #EFF6FF; color: #0284C7; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                          <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                        </a>
                      <?php else: ?>
                        <span style="color: #94A3B8; font-size: 12px;">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script type="application/json" id="absences-details-config">
<?= json_encode([
    'absenceId' => (int)($item['id_absence'] ?? 0),
    'csrfToken' => Validator::generateCsrfToken()
]) ?>
</script>
<?php
ViewContext::setScripts(['public/assets/js/modules/emplois_temps.js']);
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>
