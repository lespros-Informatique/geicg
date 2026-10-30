<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$kpis = $kpis ?? [];

$fondInitial = (float)($kpis['fond_initial'] ?? ($item['fond_initial'] ?? 0));
$totalValide = (float)($kpis['total_valide'] ?? ($item['total_general'] ?? 0));
$totalAnnule = (float)($kpis['total_annule'] ?? 0);
$nbValides = (int)($kpis['nb_valides'] ?? 0);
$nbAnnules = (int)($kpis['nb_annules'] ?? 0);
$nbTotal = (int)($kpis['nb_total'] ?? count($paiements));
$totalEspeces = (float)($kpis['total_especes'] ?? ($item['total_especes'] ?? 0));
$totalMobile = (float)($kpis['total_mobile'] ?? ($item['total_mobile_money'] ?? 0));
$totalCheque = (float)($kpis['total_cheque'] ?? ($item['total_cheque_virement'] ?? 0));
$totalScolarite = (float)($kpis['total_scolarite'] ?? 0);
$totalFraisAnnexes = (float)($kpis['total_frais_annexes'] ?? 0);
$soldeAttendu = (float)($kpis['solde_attendu'] ?? ($fondInitial + $totalValide));
$ecart = (float)($kpis['ecart_caisse'] ?? ($item['ecart_caisse'] ?? 0));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Procès-Verbal & Détails de Session de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Journal des opérations, récapitulatif financier et clôture</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <button onclick="window.print()" class="btn btn-secondary btn-no-print" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le PV
          </button>
          <a href="<?= RACINE ?>session_caisse/list" class="btn btn-secondary btn-no-print" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour
          </a>
        </div>
      </div>

      <!-- PV Card -->
      <div class="card print-area" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <!-- En-tête du Reçu / PV -->
        <?php 
          $logoUrl = $logoUrl ?? getEtablissementLogoUrl();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #E2E8F0; padding-bottom: 20px; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <?php if (!empty($logoUrl)): ?>
              <img src="<?= $logoUrl ?>" alt="Logo GROUPE EICG" style="max-height: 65px; width: auto; object-fit: contain;">
            <?php endif; ?>
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; letter-spacing: 1px;">Établissement GEICG &bull; Service Comptabilité & Caisse</span>
              <h2 style="font-size: 22px; font-weight: 900; color: #0F172A; margin: 4px 0 0 0;">Procès-Verbal de Session de Caisse</h2>
              <div style="font-size: 13px; color: #64748B; margin-top: 4px;">
                Date de session : <strong><?= date('d/m/Y', strtotime($item['date_session'])) ?></strong>
              </div>
            </div>
          </div>
          <div style="text-align: right;">
            <code style="font-size: 16px; font-weight: 900; color: #1E3A5F; background: #EFF6FF; padding: 6px 14px; border-radius: 8px; border: 1px solid #BFDBFE;">
              <?= htmlspecialchars($item['code_session']) ?>
            </code>
            <div style="margin-top: 8px;">
              <?php if ($item['statut_session'] === 'ouverte'): ?>
                <span class="badge" style="background: #DCFCE7; color: #15803D; font-weight: 800; padding: 5px 12px; border-radius: 20px; font-size: 12px;">En cours (Ouverte)</span>
              <?php elseif ($item['statut_session'] === STATUTS::VALIDE): ?>
                <span class="badge" style="background: #DBEAFE; color: #1E40AF; font-weight: 800; padding: 5px 12px; border-radius: 20px; font-size: 12px;">Clôturée & Validée</span>
              <?php else: ?>
                <span class="badge" style="background: #FEF3C7; color: #B45309; font-weight: 800; padding: 5px 12px; border-radius: 20px; font-size: 12px;">Clôturée</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Informations Session & Horaires -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Caissier Responsable</span>
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?= htmlspecialchars($item['caissier_nom'] ?? ($item['user_code'] ?? 'Caissier')) ?>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Heure d'Ouverture</span>
            <div style="font-size: 14px; font-weight: 800; color: #15803D; margin-top: 2px;">
              <?= htmlspecialchars($item['heure_ouverture'] ?? '--:--') ?>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Heure de Clôture</span>
            <div style="font-size: 14px; font-weight: 800; color: #1E3A5F; margin-top: 2px;">
              <?= htmlspecialchars($item['heure_cloture'] ?? '--:--') ?>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Superviseur / Validation</span>
            <div style="font-size: 14px; font-weight: 800; color: #7E22CE; margin-top: 2px;">
              <?= htmlspecialchars($item['superviseur_nom'] ?? ($item['user_validation'] ?? '-')) ?>
            </div>
          </div>
        </div>

        <!-- Synthèse Financière de la Session -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">
            <i data-lucide="wallet" style="width: 17px; height: 17px; vertical-align: -3px; color: #1E3A5F;"></i> Synthèse des Flux & Arrêté de Caisse
          </h3>
          <div style="font-size: 12px; color: #64748B;">
            <?= (int)$nbTotal ?> opération(s) enregistrée(s) au total
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 28px;">
          
          <!-- Carte 1 : Fond Initial -->
          <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">1. Fond Initial</span>
              <i data-lucide="coins" style="width: 15px; height: 15px; color: #94A3B8;"></i>
            </div>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($fondInitial, 0, ',', ' ') ?> F</div>
            <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Démarrage session</div>
          </div>

          <!-- Carte 2 : Total Encaissé Validé -->
          <div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">2. Encaissé Validé</span>
              <span style="background: #DCFCE7; color: #15803D; font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">+ Effectif</span>
            </div>
            <div style="font-size: 18px; font-weight: 900; color: #15803D; margin-top: 4px;"><?= number_format($totalValide, 0, ',', ' ') ?> F</div>
            <div style="font-size: 11px; color: #166534; margin-top: 2px; font-weight: 700;"><?= $nbValides ?> règlement(s) encaissé(s)</div>
          </div>

          <!-- Carte 3 : Transactions Annulées -->
          <div style="background: <?= $totalAnnule > 0 ? '#FEF2F2' : '#F8FAFC' ?>; border: 1.5px solid <?= $totalAnnule > 0 ? '#FECACA' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; font-weight: 700; color: <?= $totalAnnule > 0 ? '#DC2626' : '#64748B' ?>; text-transform: uppercase;">3. Annulations</span>
              <span style="background: <?= $totalAnnule > 0 ? '#FEE2E2' : '#F1F5F9' ?>; color: <?= $totalAnnule > 0 ? '#DC2626' : '#64748B' ?>; font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">Exclues</span>
            </div>
            <div style="font-size: 18px; font-weight: 800; color: <?= $totalAnnule > 0 ? '#DC2626' : '#64748B' ?>; margin-top: 4px;"><?= number_format($totalAnnule, 0, ',', ' ') ?> F</div>
            <div style="font-size: 11px; color: <?= $totalAnnule > 0 ? '#B91C1C' : '#94A3B8' ?>; margin-top: 2px; font-weight: 700;"><?= $nbAnnules ?> transaction(s) annulée(s)</div>
          </div>

          <!-- Carte 4 : Répartition Scolarité vs Frais Annexes -->
          <div style="background: #EFF6FF; border: 1.5px solid #BFDBFE; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">4. Scol. & Annexes</span>
              <i data-lucide="layers" style="width: 15px; height: 15px; color: #1E3A5F;"></i>
            </div>
            <div style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin-top: 6px;">
              Scol. : <strong><?= number_format($totalScolarite, 0, ',', ' ') ?> F</strong>
            </div>
            <div style="font-size: 12px; font-weight: 700; color: #475569; margin-top: 2px;">
              Annexes : <strong><?= number_format($totalFraisAnnexes, 0, ',', ' ') ?> F</strong>
            </div>
          </div>

          <!-- Carte 5 : Total Espèces Réel -->
          <div style="background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; font-weight: 700; color: #B45309; text-transform: uppercase;">5. Espèces en Caisse</span>
              <i data-lucide="banknote" style="width: 15px; height: 15px; color: #B45309;"></i>
            </div>
            <div style="font-size: 18px; font-weight: 900; color: #B45309; margin-top: 4px;"><?= number_format($totalEspeces, 0, ',', ' ') ?> F</div>
            <div style="font-size: 11px; color: #92400E; margin-top: 2px;">Autre canal : <?= number_format($totalMobile + $totalCheque, 0, ',', ' ') ?> F</div>
          </div>

          <!-- Carte 6 : Solde & Écart de Caisse -->
          <div style="background: <?= $ecart != 0 ? '#FEF2F2' : '#F0FDF4' ?>; border: 1.5px solid <?= $ecart != 0 ? '#FECACA' : '#BBF7D0' ?>; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; font-weight: 700; color: <?= $ecart != 0 ? '#DC2626' : '#15803D' ?>; text-transform: uppercase;">6. Solde & Écart</span>
              <span style="background: <?= $ecart != 0 ? '#FEE2E2' : '#DCFCE7' ?>; color: <?= $ecart != 0 ? '#DC2626' : '#15803D' ?>; font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                <?= $ecart == 0 ? 'Conforme' : 'Écart' ?>
              </span>
            </div>
            <div style="font-size: 18px; font-weight: 900; color: <?= $ecart != 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
              <?= ($ecart > 0 ? '+' : '') . number_format($ecart, 0, ',', ' ') ?> F
            </div>
            <div style="font-size: 11px; color: #475569; margin-top: 2px;">Solde attendu : <strong><?= number_format($soldeAttendu, 0, ',', ' ') ?> F</strong></div>
          </div>

        </div>

        <!-- Détail de Toutes les Transactions de la Session -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="list-ordered" style="width: 17px; height: 17px; color: #1E3A5F;"></i> Journal de Toutes les Transactions de la Session (<?= count($paiements) ?>)
            </h3>
            <span style="font-size: 12px; color: #64748B;">Reçus validés et écritures annulées enregistrés au cours de cette session</span>
          </div>
          <div style="display: flex; gap: 8px; align-items: center;">
            <span class="badge" style="background: #DCFCE7; color: #15803D; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 12px; border: 1px solid #BBF7D0;">
              <?= $nbValides ?> Validé(s) : <?= number_format($totalValide, 0, ',', ' ') ?> F
            </span>
            <?php if ($nbAnnules > 0): ?>
              <span class="badge" style="background: #FEF2F2; color: #DC2626; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 12px; border: 1px solid #FECACA;">
                <?= $nbAnnules ?> Annulé(s) : <?= number_format($totalAnnule, 0, ',', ' ') ?> F
              </span>
            <?php endif; ?>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; margin-bottom: 28px; border: 1px solid #E2E8F0; border-radius: 10px;">
          <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px; margin: 0;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 1.5px solid #E2E8F0; text-align: left; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 14px; width: 40px;">#</th>
                <th style="padding: 12px 14px;">Réf. Reçu</th>
                <th style="padding: 12px 14px;">Date & Heure</th>
                <th style="padding: 12px 14px;">Étudiant & Classe</th>
                <th style="padding: 12px 14px;">Type Règlement</th>
                <th style="padding: 12px 14px;">Mode Règlement</th>
                <th style="padding: 12px 14px; text-align: center;">Statut</th>
                <th style="padding: 12px 14px; text-align: right;">Montant (FCFA)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($paiements)): ?>
                <tr>
                  <td colspan="8" style="padding: 24px; text-align: center; color: #94A3B8;">Aucune transaction enregistrée au cours de cette session.</td>
                </tr>
              <?php else: ?>
                <?php $idx = 1; foreach ($paiements as $p): 
                  $isAnnule = (strtolower($p['statut_paiement'] ?? '') === STATUTS::ANNULE);
                  $rowBg = $isAnnule ? '#FEF2F2' : '#FFFFFF';
                  $cat = strtoupper($p['categorie_paiement'] ?? 'SCOLARITE');
                  $typeLib = !empty($p['type_paiement']) ? $p['type_paiement'] : ($cat === 'FRAIS_ANNEXES' ? 'Frais Annexes' : 'Scolarité');
                  $trancheLib = $p['libelle_tranche'] ?? '';
                ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; background: <?= $rowBg ?>;">
                    <td style="padding: 12px 14px; color: #64748B; font-weight: 700;"><?= $idx++ ?></td>
                    <td style="padding: 12px 14px;">
                      <code style="font-weight: 800; color: #1E3A5F; font-size: 13px;"><?= htmlspecialchars($p['code_paiement'] ?? '-') ?></code>
                      <?php if (!empty($p['reference_paiement']) && $p['reference_paiement'] !== '-'): ?>
                        <div style="font-size: 11px; color: #64748B;">Réf: <?= htmlspecialchars($p['reference_paiement']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px; white-space: nowrap; color: #334155; font-size: 12.5px;">
                      <strong><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></strong>
                      <div style="font-size: 11.5px; color: #64748B;"><?= date('H:i:s', strtotime($p['date_paiement'])) ?></div>
                    </td>
                    <td style="padding: 12px 14px;">
                      <div style="font-weight: 800; color: #0F172A; font-size: 13.5px;">
                        <?= htmlspecialchars(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? '')) ?>
                      </div>
                      <div style="font-size: 11.5px; color: #64748B; display: flex; gap: 8px; align-items: center; margin-top: 2px;">
                        <?php if (!empty($p['matricule_etudiant'])): ?>
                          <span>Mat: <strong><?= htmlspecialchars($p['matricule_etudiant']) ?></strong></span>
                        <?php endif; ?>
                        <?php if (!empty($p['libelle_classe'])): ?>
                          <span>&bull; <?= htmlspecialchars($p['libelle_classe']) ?></span>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td style="padding: 12px 14px;">
                      <?php if ($cat === 'FRAIS_ANNEXES'): ?>
                        <span class="badge" style="background: #F3E8FF; color: #7E22CE; font-weight: 800; padding: 4px 9px; border-radius: 6px; font-size: 11.5px; border: 1px solid #E9D5FF; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="tag" style="width: 12px; height: 12px;"></i> Frais Annexes
                        </span>
                      <?php else: ?>
                        <span class="badge" style="background: #EFF6FF; color: #1E3A5F; font-weight: 800; padding: 4px 9px; border-radius: 6px; font-size: 11.5px; border: 1px solid #BFDBFE; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="graduation-cap" style="width: 12px; height: 12px;"></i> Scolarité
                        </span>
                        <?php if (!empty($trancheLib)): ?>
                          <div style="font-size: 11.5px; color: #475569; margin-top: 3px; font-weight: 600;">
                            <?= htmlspecialchars($trancheLib) ?>
                          </div>
                        <?php endif; ?>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px;">
                      <span class="badge" style="background: #F8FAFC; color: #334155; font-weight: 700; padding: 4px 8px; border-radius: 6px; font-size: 11px; text-transform: uppercase; border: 1px solid #CBD5E1;">
                        <?= htmlspecialchars($p['mode_paiement'] ?? 'espece') ?>
                      </span>
                      <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                        <?= htmlspecialchars($p['canal_paiement'] ?? 'guichet') ?>
                      </div>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <?php if ($isAnnule): ?>
                        <span class="badge" style="background: #FEE2E2; color: #DC2626; font-weight: 800; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; border: 1px solid #FECACA; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="x-circle" style="width: 13px; height: 13px;"></i> Annulé
                        </span>
                        <?php if (!empty($p['observations'])): ?>
                          <div style="font-size: 10.5px; color: #B91C1C; margin-top: 3px; max-width: 170px; line-height: 1.2; font-style: italic;">
                            <?= htmlspecialchars($p['observations']) ?>
                          </div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="badge" style="background: #DCFCE7; color: #15803D; font-weight: 800; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="check" style="width: 13px; height: 13px;"></i> Confirmé
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: right;">
                      <?php if ($isAnnule): ?>
                        <del style="font-weight: 700; color: #94A3B8; font-size: 13.5px;">
                          <?= number_format((float)$p['montant_paiement'], 0, ',', ' ') ?> F
                        </del>
                        <div style="font-size: 11px; color: #DC2626; font-weight: 700;">Exclu de la caisse</div>
                      <?php else: ?>
                        <span style="font-weight: 900; color: #0F172A; font-size: 14.5px;">
                          <?= number_format((float)$p['montant_paiement'], 0, ',', ' ') ?> F
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Observations & Signatures -->
        <?php if (!empty($item['observations_ouverture']) || !empty($item['observations_cloture'])): ?>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; font-size: 13px; color: #475569;">
            <strong>Observations :</strong>
            <?php if (!empty($item['observations_ouverture'])): ?>
              <div>&bull; Ouverture : <?= htmlspecialchars($item['observations_ouverture']) ?></div>
            <?php endif; ?>
            <?php if (!empty($item['observations_cloture'])): ?>
              <div>&bull; Clôture : <?= htmlspecialchars($item['observations_cloture']) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
          <div>
            <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Signature & Cachet du Caissier</div>
            <div style="height: 60px;"></div>
            <div style="font-size: 13px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($item['caissier_nom'] ?? 'Caissier') ?></div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Visa & Contrôle Superviseur / Comptabilité</div>
            <div style="height: 60px;"></div>
            <div style="font-size: 13px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($item['superviseur_nom'] ?? 'Superviseur') ?></div>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
