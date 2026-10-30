<?php
/**
 * Vue d'impression / Bilan du Registre Caisse Annexe (Format A4 Paysage / Impression Directe)
 * Conforme aux règles d'affichage dynamique et charte graphique GEICG
 */
$anneeLibelle = $anneeLibelle ?? 'Toutes les années';
$paiements = $paiements ?? [];
$stats = $stats ?? [];
$sumTable = 0;
$dateDebutFmt = !empty($dateDebut) ? date('d/m/Y', strtotime($dateDebut)) : '';
$dateFinFmt = !empty($dateFin) ? date('d/m/Y', strtotime($dateFin)) : '';
$periodeTexte = 'Période globale';
if ($dateDebutFmt && $dateFinFmt) {
    $periodeTexte = "Du {$dateDebutFmt} au {$dateFinFmt}";
} elseif ($dateDebutFmt) {
    $periodeTexte = "À partir du {$dateDebutFmt}";
} elseif ($dateFinFmt) {
    $periodeTexte = "Jusqu'au {$dateFinFmt}";
}

$agentNom = trim(Auth::fullName());
if (empty($agentNom)) {
    $agentNom = 'Service Comptabilité & Caisse GEICG';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Caisse Annexe - Registre des Encaissements - <?= htmlspecialchars($anneeLibelle) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #FFF !important;
                padding: 0 !important;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 11px;
            color: #1E293B;
            background: #F8FAFC;
            padding: 20px 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #FFF;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
        }

        /* Barre d'action supérieure */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #E2E8F0;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .btn-print {
            background: #1E3A5F;
            color: #FFF;
        }
        .btn-print:hover {
            background: #152A45;
        }
        .btn-back {
            background: #F1F5F9;
            color: #475569;
            border-color: #CBD5E1;
        }
        .btn-back:hover {
            background: #E2E8F0;
        }

        /* En-tête officiel */
        .institution-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1E3A5F;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .inst-info h2 {
            font-size: 17px;
            font-weight: 800;
            color: #800000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .inst-info p {
            font-size: 10.5px;
            color: #475569;
            margin-top: 2px;
        }
        .report-title-box {
            text-align: right;
        }
        .report-badge {
            display: inline-block;
            background: #EFF6FF;
            color: #1E3A5F;
            border: 1px solid #BFDBFE;
            font-weight: 700;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .report-title {
            font-size: 18px;
            font-weight: 900;
            color: #1E3A5F;
            text-transform: uppercase;
        }
        .report-meta {
            font-size: 10.5px;
            color: #64748B;
            margin-top: 2px;
        }

        /* Cartes de synthèse financière */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        .kpi-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px 12px;
            border-left: 4px solid #1E3A5F;
        }
        .kpi-box.emerald {
            border-left-color: #15803D;
            background: #F0FDF4;
        }
        .kpi-box.amber {
            border-left-color: #D97706;
            background: #FFFBEB;
        }
        .kpi-box.indigo {
            border-left-color: #4F46E5;
            background: #EEF2FF;
        }
        .kpi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748B;
            letter-spacing: 0.5px;
        }
        .kpi-val {
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
            margin-top: 2px;
        }
        .kpi-sub {
            font-size: 9.5px;
            color: #64748B;
            margin-top: 1px;
        }

        /* Table du registre */
        table.registre-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }
        table.registre-table th {
            background: #1E3A5F;
            color: #FFF;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 9.5px;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #1E3A5F;
        }
        table.registre-table td {
            padding: 6px;
            border: 1px solid #E2E8F0;
            vertical-align: middle;
        }
        table.registre-table tr:nth-child(even) {
            background-color: #F8FAFC;
        }
        table.registre-table tr:hover {
            background-color: #F1F5F9;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .font-mono { font-family: monospace, SFMono-Regular, Consolas, sans-serif; }

        .badge-mode {
            display: inline-block;
            padding: 2px 5px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-espece { background: #DCFCE7; color: #166534; }
        .badge-digital { background: #DBEAFE; color: #1E40AF; }
        .badge-autre { background: #F1F5F9; color: #475569; }

        /* Pied de page & signatures */
        .signatures-row {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 16px;
            border-top: 1px dashed #CBD5E1;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 280px;
            text-align: center;
        }
        .signature-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 50px;
        }
        .signature-line {
            border-bottom: 1px solid #94A3B8;
            margin-bottom: 4px;
        }
        .signature-author {
            font-size: 10px;
            color: #64748B;
            font-weight: 600;
        }

        .print-footer {
            margin-top: 16px;
            text-align: center;
            font-size: 9px;
            color: #94A3B8;
        }
    </style>
</head>
<body>

<div class="page-container">
    <!-- Barre d'action (masquée à l'impression) -->
    <div class="action-bar no-print">
        <div>
            <a href="<?= RACINE ?>caisse_annexe/list" class="btn-action btn-back">
                &larr; Retour au Bilan Caisse Annexe
            </a>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-action btn-print">
                &#128438; Imprimer le Bilan (A4 Paysage)
            </button>
        </div>
    </div>

    <!-- En-tête officiel -->
    <div class="institution-header">
        <div class="inst-info">
            <h2>GROUPE EICG - DIRECTION GÉNÉRALE</h2>
            <p><strong>École Internationale de Commerce et de Gestion</strong></p>
            <p>Service Comptabilité & Caisse Annexe | Abidjan - Côte d'Ivoire</p>
        </div>
        <div class="report-title-box">
            <span class="report-badge">Journal Financier Officiel</span>
            <div class="report-title">REGISTRE CAISSE ANNEXE</div>
            <div class="report-meta">
                Année : <strong><?= htmlspecialchars($anneeLibelle) ?></strong> &bull; 
                Période : <strong><?= htmlspecialchars($periodeTexte) ?></strong>
            </div>
            <div class="report-meta">
                Édité le : <?= date('d/m/Y à H:i:s') ?> &bull; Par : <?= htmlspecialchars($agentNom) ?>
            </div>
        </div>
    </div>

    <!-- Cartes KPIs de synthèse -->
    <div class="kpi-row">
        <div class="kpi-box emerald">
            <div class="kpi-label">Total Recouvré Annexes</div>
            <div class="kpi-val"><?= number_format($stats['total_collecte'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div class="kpi-sub"><?= count($paiements) ?> transaction(s) enregistrée(s)</div>
        </div>
        <div class="kpi-box indigo">
            <div class="kpi-label">Budget Annexe Attendu</div>
            <div class="kpi-val"><?= number_format($stats['budget_attendu'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div class="kpi-sub"><?= number_format($stats['total_etudiants'] ?? 0, 0, ',', ' ') ?> étudiant(s) inscrit(s)</div>
        </div>
        <div class="kpi-box amber">
            <div class="kpi-label">Reste à Recouvrer</div>
            <div class="kpi-val"><?= number_format($stats['reste_a_recouvrer'] ?? 0, 0, ',', ' ') ?> FCFA</div>
            <div class="kpi-sub">Taux : <?= number_format($stats['taux_recouvrement'] ?? 0, 1, ',', ' ') ?>% de recouvrement</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Répartition Espèces / Digital</div>
            <div class="kpi-val" style="font-size: 13px;">
                Esp : <?= number_format($stats['total_especes'] ?? 0, 0, ',', ' ') ?> F
            </div>
            <div class="kpi-sub">
                Dig : <?= number_format($stats['total_digital'] ?? 0, 0, ',', ' ') ?> F
            </div>
        </div>
    </div>

    <!-- Tableau détaillé des règlements -->
    <table class="registre-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 80px;">Réf. Reçu</th>
                <th style="width: 75px;">Date & Heure</th>
                <th style="width: 75px;">Matricule</th>
                <th>Nom & Prénoms Étudiant</th>
                <th style="width: 90px;">Classe</th>
                <th style="width: 70px;">Filière</th>
                <th style="width: 70px;">Mode</th>
                <th style="width: 90px;">Réf. Paiement</th>
                <th style="width: 95px;" class="text-end">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paiements)): ?>
            <tr>
                <td colspan="10" class="text-center" style="padding: 24px; color: #64748B; font-style: italic;">
                    Aucun encaissement de frais annexe n'a été trouvé pour les critères sélectionnés.
                </td>
            </tr>
            <?php else: ?>
                <?php 
                $i = 1; 
                $sumTable = 0;
                foreach ($paiements as $p): 
                    $mt = (float)$p['montant_paiement'];
                    $sumTable += $mt;
                    $mode = strtolower($p['mode_paiement'] ?? 'espece');
                    $isEspece = (strpos($mode, 'espece') !== false || strpos($mode, 'cash') !== false);
                    $isDig = (strpos($mode, 'mobile') !== false || strpos($mode, 'wave') !== false || strpos($mode, 'orange') !== false || strpos($mode, 'mtn') !== false || strpos($mode, 'moov') !== false || strpos($mode, 'cheque') !== false || strpos($mode, 'virement') !== false);
                    $badgeCls = $isEspece ? 'badge-espece' : ($isDig ? 'badge-digital' : 'badge-autre');
                ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td class="fw-bold font-mono" style="color: #1E3A5F;"><?= htmlspecialchars($p['code_paiement'] ?? '-') ?></td>
                    <td><?= !empty($p['date_paiement']) ? date('d/m/Y H:i', strtotime($p['date_paiement'])) : '-' ?></td>
                    <td class="font-mono"><?= htmlspecialchars($p['matricule_etudiant'] ?? '-') ?></td>
                    <td class="fw-bold">
                        <?= htmlspecialchars(trim(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? ''))) ?>
                    </td>
                    <td><?= htmlspecialchars($p['libelle_classe'] ?? '-') ?></td>
                    <td>
                        <span style="font-size: 8.5px;"><?= htmlspecialchars($p['libelle_filiere'] ?? '-') ?></span>
                    </td>
                    <td>
                        <span class="badge-mode <?= $badgeCls ?>"><?= htmlspecialchars(ucfirst($p['mode_paiement'] ?? 'espece')) ?></span>
                    </td>
                    <td class="font-mono" style="font-size: 9px;"><?= htmlspecialchars($p['reference_paiement'] ?: '-') ?></td>
                    <td class="text-end fw-bold font-mono" style="color: #15803D;">
                        <?= number_format($mt, 0, ',', ' ') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($paiements)): ?>
        <tfoot>
            <tr style="background: #E2E8F0; font-weight: 800; font-size: 11px;">
                <td colspan="9" class="text-end" style="padding: 8px;">TOTAL RECOUVRÉ SUR CE REGISTRE :</td>
                <td class="text-end font-mono" style="padding: 8px; color: #1E3A5F; font-size: 12px;">
                    <?= number_format($sumTable, 0, ',', ' ') ?> FCFA
                </td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <!-- Bloc des Signatures officielles -->
    <div class="signatures-row">
        <div class="signature-box">
            <div class="signature-title">Le Caissier / Agent Émetteur</div>
            <div class="signature-line"></div>
            <div class="signature-author"><?= htmlspecialchars($agentNom) ?></div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Le Chef de Service Comptabilité</div>
            <div class="signature-line"></div>
            <div class="signature-author">Visa & Cachet Officiel</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">La Direction Générale</div>
            <div class="signature-line"></div>
            <div class="signature-author">Pour Approbation</div>
        </div>
    </div>

    <div class="print-footer">
        Système GEICG &bull; Document officiel certifié généré automatiquement le <?= date('d/m/Y à H:i:s') ?> &bull; Page 1/1
    </div>
</div>

</body>
</html>
