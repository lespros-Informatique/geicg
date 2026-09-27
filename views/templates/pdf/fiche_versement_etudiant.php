<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche Individuelle des Versements de Scolarité</title>
  <style>
    @page {
      margin: 6mm 8mm 6mm 8mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 9.5px;
      color: #000000;
      line-height: 1.25;
    }

    /* Container Page Border */
    .page-frame {
      border: 1.5px solid #800000;
      padding: 10px 12px;
      box-sizing: border-box;
    }

    /* Header */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .inst-title {
      font-size: 12.5px;
      font-weight: bold;
      color: #800000;
      text-decoration: underline;
      text-align: center;
    }
    .inst-subtitle {
      font-size: 11.5px;
      font-weight: bold;
      text-align: center;
      color: #000000;
      margin-top: 1px;
    }
    .inst-contacts {
      font-size: 10.5px;
      font-style: italic;
      font-weight: bold;
      text-align: center;
      margin-top: 1px;
    }

    /* Bannières Titres */
    .banner-title {
      background: #800000;
      color: #FFFFFF;
      font-size: 13.5px;
      font-weight: bold;
      text-align: center;
      padding: 4px;
      margin-top: 4px;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .section-title {
      background: #F1F5F9;
      color: #800000;
      font-size: 11.5px;
      font-weight: bold;
      padding: 3px 6px;
      border-left: 4px solid #800000;
      margin-top: 6px;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    /* Data Grids */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .data-table td {
      padding: 2px 3px;
      vertical-align: top;
      font-size: 9.9px;
    }
    .val-bold {
      font-weight: bold;
    }
    .photo-box {
      width: 80px;
      height: 96px;
      border: 1px solid #000000;
      object-fit: cover;
    }

    /* Financial Summary Box */
    .fin-summary-box {
      border: 1px solid #000000;
      background: #FAFAFA;
      padding: 6px;
      margin-top: 4px;
      margin-bottom: 6px;
    }

    /* Payments & Schedule Tables */
    .grid-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2px;
      margin-bottom: 6px;
    }
    .grid-table th {
      background: #1E293B;
      color: #FFFFFF;
      border: 1px solid #000000;
      padding: 7px 4px;
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
    }
    .grid-table td {
      border: 1px solid #000000;
      padding: 5.5px 4px;
      font-size: 9.9px;
    }

    .total-row td {
      background: #F1F5F9;
      font-weight: bold;
      font-size: 9.5px;
    }

    /* Badges Statut */
    .badge-solde {
      display: inline-block;
      padding: 3px 8px;
      font-weight: bold;
      font-size: 10px;
      color: #FFFFFF;
      background: #000000ff;
      border-radius: 3px;
    }
    .badge-partiel {
      display: inline-block;
      padding: 3px 8px;
      font-weight: bold;
      font-size: 10px;
      color: #FFFFFF;
      background: #B45309;
      border-radius: 3px;
    }

    /* Signatures */
    .signature-grid {
      width: 100%;
      margin-top: 12px;
      font-size: 9.5px;
    }
    .signature-grid td {
      vertical-align: top;
    }

    .footer-line {
      font-size: 10px;
      color: #64748B;
      text-align: right;
      margin-top: 6px;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="page-frame">

  <!-- EN-TÊTE -->
  <?php 
    if (empty($logo_src)) {
        $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
        if (file_exists($logoPath)) {
            $logo_src = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
    if (empty($photo_etudiant)) {
        $placeholderFile = __DIR__ . '/../../public/assets/images/placeholders/etudiant.png';
        if (file_exists($placeholderFile)) {
            $photo_etudiant = 'data:image/png;base64,' . base64_encode(file_get_contents($placeholderFile));
        }
    }
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 18%; vertical-align: middle;">
        <?php if (!empty($logo_src)): ?>
          <img src="<?= $logo_src ?>" style="max-height: 44px; max-width: 115px;">
        <?php else: ?>
          <div style="font-weight:bold; color:#800000; font-size:13px;">GROUPE EICG</div>
        <?php endif; ?>
      </td>
      <td style="width: 82%; text-align: center; vertical-align: middle;">
        <div class="inst-title">GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION</div>
        <br>
        <div class="inst-subtitle">AGREE PAR L'ETAT ET LE FDFP</div>
        <div class="inst-contacts">Contacts : 27 31 62 40 57 / 07 79 37 37 38 / 05 04 59 39 99</div>
        <div class="inst-contacts">Site web : www.geicg.org</div>
      </td>
    </tr>
  </table>
  <br>

  <!-- BANNIÈRE PRINCIPALE -->
  <div class="banner-title">
    FICHE INDIVIDUELLE DES VERSEMENTS DE SCOLARITÉ
    <span style="font-size: 10px; font-weight: normal; float: right;">
      ANNÉE ACADÉMIQUE : <?= htmlspecialchars($annee_libelle ?? '2025 - 2026') ?>
    </span>
  </div>

  <!-- SECTION 1 : SIGNALÉTIQUE ÉTUDIANT -->
  <div class="section-title">1. SIGNALÉTIQUE ET PROFIL DE L'ÉTUDIANT</div>
  <table class="data-table">
    <tr>
      <td style="width: 78%;">
        <table style="width: 100%;">
          <tr>
            <td style="width: 33%;">Matricule MESRS : <span class="val-bold"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></span></td>
            <td style="width: 33%;">Code Inscription : <span class="val-bold"><?= htmlspecialchars($code_inscription ?? '-') ?></span></td>
            <td style="width: 34%;">Statut : <span class="val-bold"><?= htmlspecialchars($statut_affectation ?? 'AFFECTE') ?></span></td>
          </tr>
          <tr>
            <td colspan="3">Nom & Prénom(s) : <span class="val-bold" style="font-size: 11px; color:#800000;"><?= htmlspecialchars(mb_strtoupper($nom_prenom_etudiant ?? '-')) ?></span></td>
          </tr>
          <tr>
            <td colspan="2">Filière d'études : <span class="val-bold"><?= htmlspecialchars($filiere_libelle ?? '-') ?></span></td>
            <td>Niveau : <span class="val-bold"><?= htmlspecialchars($niveau_libelle ?? '-') ?></span></td>
          </tr>
          <tr>
            <td style="width: 33%;">Classe : <span class="val-bold"><?= htmlspecialchars($classe_libelle ?? '-') ?></span></td>
            <td style="width: 33%;">Contact : <span class="val-bold"><?= htmlspecialchars($contact_etudiant ?? '-') ?></span></td>
            <td style="width: 34%;">Date Inscription : <span class="val-bold"><?= htmlspecialchars($date_inscription ?? '-') ?></span></td>
          </tr>
        </table>
      </td>
      <td style="width: 22%; text-align: right; vertical-align: top;">
        <?php if (!empty($photo_etudiant)): ?>
          <img src="<?= $photo_etudiant ?>" class="photo-box">
        <?php else: ?>
          <div class="photo-box" style="background: #F1F5F9; text-align: center; line-height: 96px; color: #94A3B8; font-size: 8px;">PHOTO ÉTUDIANT</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <br>
  <!-- SECTION 2 : SITUATION FINANCIÈRE GLOBALE -->
  <div class="section-title">2. SITUATION FINANCIÈRE GLOBALE DE LA SCOLARITÉ</div>
  <div class="fin-summary-box">
    <table style="width: 100%; font-size: 10px;">
      <tr>
        <td style="width: 25%;">TOTAL SCOLARITÉ FIXÉ : <br><strong style="font-size: 11px;"><?= number_format($total_scolarite ?? 115000, 0, ',', ' ') ?> FCFA</strong></td>
        <td style="width: 25%;">DROIT D'INSCRIPTION : <br><strong><?= number_format($frais_inscription ?? 15000, 0, ',', ' ') ?> FCFA</strong></td>
        <td style="width: 25%;">CUMUL DÉJÀ PAYÉ : <br><strong style="color: #166534; font-size: 11px;"><?= number_format($montant_paye ?? 115000, 0, ',', ' ') ?> FCFA</strong></td>
        <td style="width: 25%; text-align: right;">SOLDE RESTANT DÛ : <br><strong style="color: <?= ($reste_payer ?? 0) <= 0 ? '#166534' : '#990000' ?>; font-size: 11.5px;"><?= number_format($reste_payer ?? 0, 0, ',', ' ') ?> FCFA</strong></td>
      </tr>
      <tr>
        <td colspan="4" style="padding-top: 6px;">
          État du compte : &nbsp;&nbsp;
          <?php if (($reste_payer ?? 0) <= 0): ?>
            <span class="badge-solde">✔ SCOLARITÉ ENTIÈREMENT SOLDÉE</span>
          <?php else: ?>
            <span class="badge-partiel">⏳ SCOLARITÉ EN COURS DE RÈGLEMENT - RESTANT DÛ : <?= number_format($reste_payer, 0, ',', ' ') ?> FCFA</span>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>

  <br>
  <!-- SECTION 3 : HISTORIQUE CHRONOLOGIQUE DES ENCAISSEMENTS -->
  <div class="section-title">3. HISTORIQUE CHRONOLOGIQUE DES VERSEMENTS EN CAISSE</div>
  <table class="grid-table">
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 15%;">DATE & HEURE</th>
        <th style="width: 17%;">CODE REÇU</th>
        <th style="width: 25%; text-align: left;">LIBELLÉ / OPÉRATION</th>
        <th style="width: 13%;">MODE PAIEMENT</th>
        <th style="width: 13%;">RÉF TRANSACTION</th>
        <th style="width: 13%;">MONTANT VERSÉ</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($paiements) && is_array($paiements)): ?>
        <?php $cumulPaiements = 0; ?>
        <?php foreach ($paiements as $index => $p): ?>
          <?php $cumulPaiements += (float)($p['montant_paiement'] ?? 0); ?>
          <tr>
            <td style="text-align: center;"><?= $index + 1 ?></td>
            <td style="text-align: center;"><?= date('d/m/Y H:i', strtotime($p['date_paiement'] ?? ($p['created_at_paiement'] ?? 'now'))) ?></td>
            <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($p['code_paiement'] ?? '-') ?></td>
            <td><?= htmlspecialchars($p['type_paiement'] ?? 'Versement Scolarité') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Espèces') ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($p['reference_paiement'] ?? '-') ?></td>
            <td style="text-align: right; font-weight: bold; color: #000000;"><?= number_format($p['montant_paiement'] ?? 0, 0, ',', ' ') ?> FCFA</td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td colspan="6" style="text-align: right;">CUMUL TOTAL DES VERSEMENTS EFFECTUÉS :</td>
          <td style="text-align: right; font-size: 10px; color: #000000; font-weight: bold;"><?= number_format($cumulPaiements, 0, ',', ' ') ?> FCFA</td>
        </tr>
      <?php else: ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 10px; color: #64748B;">Aucun versement enregistré en caisse pour cette inscription.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <br>
  <!-- SECTION 4 : ÉCHÉANCIER DE EXIGIBILITÉ DES TRANCHES -->
  <div class="section-title">4. ÉCHÉANCIER ET RÉPARTITION DES TRANCHES DE SCOLARITÉ</div>
  <table class="grid-table">
    <thead>
      <tr>
        <th style="width: 30%;">TRANCHE / ÉCHÉANCE</th>
        <th style="width: 20%;">DATE DE RIGUEUR</th>
        <th style="width: 20%;">MONTANT EXIGIBLE</th>
        <th style="width: 30%;">STATUT DU RÈGLEMENT</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($echeancier) && is_array($echeancier)): ?>
        <?php foreach ($echeancier as $ech): ?>
          <tr>
            <td style="font-weight: bold;"><?= htmlspecialchars($ech['libelle']) ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($ech['date_limite']) ?></td>
            <td style="text-align: right; font-weight: bold;"><?= number_format($ech['montant'], 0, ',', ' ') ?> FCFA</td>
            <td style="text-align: center; font-weight: bold;"><?= $ech['statut_html'] ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" style="text-align: center; padding: 10px; color: #64748B;">Aucun échéancier défini pour cette filière.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <br>
  <br>
  <!-- SIGNATURES -->
  <table class="signature-grid">
    <tr>
      <td style="width: 50%;">
        <div style="font-weight: bold; text-decoration: underline;">Le Caissier / Agent Comptable</div>
        <div style="margin-top: 20px; font-weight: bold; font-size: 9.5px;"><?= htmlspecialchars($agent_caisse_nom ?? 'Caisse Principale GEICG') ?></div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div style="font-size: 9px; margin-bottom: 2px;">Fait à Bouaké, le <?= date('d/m/Y') ?></div>
        <div style="font-weight: bold; text-decoration: underline;">Le Chef du Service Financier</div>
        <div style="margin-top: 20px; font-weight: bold; font-size: 9.5px;">Direction Financière GEICG</div>
      </td>
    </tr>
  </table>

  <!-- FOOTER HORODATAGE -->
  <div class="footer-line">
    Relevé des versements généré par le système informatique GEICG - Édition du <?= date('d/m/Y H:i:s') ?>
  </div>

</div>

</body>
</html>
