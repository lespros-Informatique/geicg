<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Reçu de Règlement</title>
  <style>
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 12px;
      color: #0F172A;
      line-height: 1.4;
    }
    .header-table {
      width: 100%;
      border-bottom: 2px solid #1E3A5F;
      padding-bottom: 12px;
      margin-bottom: 15px;
    }
    .institution-name {
      font-size: 16px;
      font-weight: bold;
      color: #1E3A5F;
      text-transform: uppercase;
    }
    .receipt-title {
      font-size: 18px;
      font-weight: bold;
      color: #15803D;
      text-transform: uppercase;
      text-align: right;
    }
    .badge-code {
      font-family: monospace;
      font-size: 12px;
      font-weight: bold;
      background: #EFF6FF;
      color: #1E3A5F;
      padding: 4px 8px;
      border-radius: 4px;
      border: 1px solid #BFDBFE;
    }
    .info-box {
      width: 100%;
      border: 1px solid #CBD5E1;
      border-radius: 8px;
      background: #F8FAFC;
      padding: 12px;
      margin-bottom: 15px;
    }
    .info-table {
      width: 100%;
    }
    .info-table td {
      padding: 4px 6px;
      vertical-align: top;
    }
    .amount-box {
      width: 100%;
      border: 2px solid #16A34A;
      border-radius: 8px;
      background: #F0FDF4;
      padding: 15px;
      text-align: center;
      margin-bottom: 20px;
    }
    .amount-number {
      font-size: 24px;
      font-weight: bold;
      color: #15803D;
    }
    .signatures-table {
      width: 100%;
      margin-top: 30px;
    }
    .signatures-table td {
      text-align: center;
      width: 50%;
      vertical-align: top;
    }
    .signature-box {
      height: 60px;
    }
  </style>
</head>
<body>

  <!-- En-tête Institution & Titre -->
  <?php 
    $logoPath = __DIR__ . '/../../public/assets/images/logo/logo_eicg.jpg';
  ?>
  <table class="header-table">
    <tr>
      <td style="width: 60%;">
        <?php if (file_exists($logoPath)): ?>
          <img src="<?= $logoPath ?>" style="max-height: 55px; max-width: 180px; margin-bottom: 6px;">
        <?php endif; ?>
        <div class="institution-name"><?= htmlspecialchars($etablissement_nom ?? 'GROUPE EICG') ?></div>
        <div style="font-size: 10px; color: #64748B; margin-top: 2px;">
          Enseignement Supérieur & Recherche Scientifique<br>
          Service de la Caisse & de la Comptabilité
        </div>
      </td>
      <td style="width: 40%; text-align: right;">
        <div class="receipt-title">REÇU DE CAISSE</div>
        <div style="margin-top: 5px;">
          <span class="badge-code">N° <?= htmlspecialchars($code_paiement ?? 'PAI-0000') ?></span>
        </div>
        <div style="margin-top: 5px;">
          <barcode code="<?= htmlspecialchars($code_paiement ?? 'PAI-0000') ?>" type="C128A" size="0.75" height="0.8" />
        </div>
        <div style="font-size: 10px; color: #64748B; margin-top: 4px;">
          Date : <?= htmlspecialchars($date_paiement ?? date('d/m/Y H:i')) ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- Informations Étudiant & Inscription -->
  <div class="info-box">
    <table class="info-table">
      <tr>
        <td style="width: 18%;"><strong>Étudiant :</strong></td>
        <td style="width: 32%; color: #0F172A; font-weight: bold;"><?= htmlspecialchars($nom_etudiant ?? '-') ?></td>
        <td style="width: 18%;"><strong>Matricule :</strong></td>
        <td style="width: 32%; font-family: monospace; font-weight: bold;"><?= htmlspecialchars($matricule_etudiant ?? '-') ?></td>
      </tr>
      <tr>
        <td><strong>Classe :</strong></td>
        <td><?= htmlspecialchars($libelle_classe ?? '-') ?></td>
        <td><strong>Année Acad. :</strong></td>
        <td><?= htmlspecialchars($libelle_annee ?? '-') ?></td>
      </tr>
      <tr>
        <td><strong>Motif :</strong></td>
        <td><?= htmlspecialchars($type_paiement ?? 'Règlement Scolarité') ?></td>
        <td><strong>Mode Règlement :</strong></td>
        <td><?= htmlspecialchars($mode_paiement ?? 'Espèces') ?></td>
      </tr>
      <?php if (!empty($reference_paiement)): ?>
      <tr>
        <td><strong>Référence :</strong></td>
        <td colspan="3"><?= htmlspecialchars($reference_paiement) ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- Encadré du Montant Encaissé -->
  <div class="amount-box">
    <div style="font-size: 11px; font-weight: bold; color: #166534; text-transform: uppercase;">Montant Encaissé (FCFA)</div>
    <div class="amount-number"><?= number_format($montant_paiement ?? 0, 0, ',', ' ') ?> FCFA</div>
    <?php if (isset($solde_restant)): ?>
      <div style="font-size: 11px; color: #475569; margin-top: 4px;">
        Solde Restant Après Règlement : <strong style="color: <?= ($solde_restant > 0) ? '#DC2626' : '#15803D' ?>;"><?= number_format($solde_restant, 0, ',', ' ') ?> FCFA</strong>
      </div>
    <?php endif; ?>
  </div>

  <!-- Signatures & Cachet -->
  <table class="signatures-table">
    <tr>
      <td>
        <div style="font-size: 11px; font-weight: bold; color: #1E3A5F; text-transform: uppercase;">L'Élève / Le Tuteur</div>
        <div class="signature-box"></div>
        <div style="font-size: 10px; color: #94A3B8;">Signature</div>
      </td>
      <td>
        <div style="font-size: 11px; font-weight: bold; color: #1E3A5F; text-transform: uppercase;">Le Caissier / Agent Comptable</div>
        <div class="signature-box"></div>
        <div style="font-size: 10px; color: #64748B; font-weight: bold;"><?= htmlspecialchars($agent_nom ?? 'Administration') ?></div>
      </td>
    </tr>
  </table>

</body>
</html>
