<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$parent = isset($parent) ? $parent : [];
$inscription = isset($inscription) ? $inscription : [];
$etablissement = isset($etablissement) ? $etablissement : [];
$paiements = isset($paiements) ? $paiements : [];
$absences = isset($absences) ? $absences : [];
$scolariteTotale = $scolariteTotale ?? 0;
$totalPaye = $totalPaye ?? 0;
$soldeRestant = $soldeRestant ?? 0;
$tauxPaiement = ($scolariteTotale > 0) ? min(100, round(($totalPaye / $scolariteTotale) * 100)) : 100;

$nomComplet = trim(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? ''));
$matricule = $item['matricule_etudiant'] ?? '-';
$classeLibelle = $inscription['libelle_classe'] ?? 'Non inscrit';
$anneeLibelle = $inscription['libelle_annee'] ?? date('Y') . '-' . (date('Y') + 1);
$etabNom = $etablissement['libelle_etablissement'] ?? 'GROUPE ECOLE INTERNATIONALE';
$etabTel = $etablissement['telephone_etablissement'] ?? '+225 01 02 03 04 05';
$etabEmail = $etablissement['email_etablissement'] ?? 'contact@geicg.ci';
$etabAdresse = $etablissement['adresse_etablissement'] ?? 'Abidjan, Côte d\'Ivoire';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="folder-check" style="width: 24px; height: 24px; color: #1E3A5F;"></i> Dossier Étudiant : <?= htmlspecialchars($nomComplet) ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Matricule : <strong><?= htmlspecialchars($matricule) ?></strong> &bull; Classe : <strong><?= htmlspecialchars($classeLibelle) ?></strong> &bull; Année : <strong><?= htmlspecialchars($anneeLibelle) ?></strong></p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
          <button type="button" onclick="imprimerCarteScolaire()" class="btn btn-warning" style="background: #D97706; border-color: #D97706; color: #FFF; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer Carte Scolaire
          </button>
          <a href="<?= RACINE ?>etudiant/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; border-radius: 8px; padding: 9px 16px;">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier le dossier
          </a>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION NOUVELLE : CARTE SCOLAIRE OFFICIELLE & BADGE ÉTUDIANT (RECTO / VERSO) -->
      <!-- ========================================================================= -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid #EFF6FF; padding-bottom: 12px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="id-card" style="width: 20px; height: 20px; color: #D97706;"></i> Carte d'Identité Scolaire & Badge Étudiant
            </h3>
            <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Format PVC Standard (CR-80) - Prête à l'impression et à l'encodage</p>
          </div>
          <div style="display: flex; gap: 8px;">
            <button type="button" onclick="imprimerCarteScolaire()" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 12px;">
              <i data-lucide="printer" style="width: 14px; height: 14px;"></i> Lancer l'impression badge
            </button>
          </div>
        </div>

        <!-- ZONE D'AFFICHAGE DU BADGE (RECTO ET VERSO CÔTE À CÔTE) -->
        <div id="section-carte-scolaire" style="display: flex; justify-content: center; align-items: center; gap: 28px; flex-wrap: wrap; padding: 10px 0;">
          
          <!-- ===== 1. RECTO DU BADGE (FACE AVANT) ===== -->
          <div class="badge-card-container badge-recto" style="width: 390px; height: 245px; border-radius: 14px; background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 60%, #1E40AF 100%); color: #FFFFFF; position: relative; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.35); overflow: hidden; padding: 14px 18px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.15); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            
            <!-- Décoration holographique de fond -->
            <div style="position: absolute; right: -40px; bottom: -40px; width: 180px; height: 180px; border-radius: 50%; background: radial-gradient(circle, rgba(217,119,6,0.2) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>
            <div style="position: absolute; left: 0; top: 0; right: 0; height: 5px; background: linear-gradient(90deg, #D97706, #F59E0B, #3B82F6, #D97706);"></div>

            <!-- En-tête Badge -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.18); padding-bottom: 8px; margin-bottom: 10px;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #D97706; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; color: #FFF; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                  G
                </div>
                <div>
                  <div style="font-size: 10px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #FDE68A; line-height: 1.1;">
                    <?= htmlspecialchars(strtoupper($etabNom)) ?>
                  </div>
                  <div style="font-size: 8px; color: #CBD5E1; letter-spacing: 0.5px;">RÉPUBLIQUE DE CÔTE D'IVOIRE</div>
                </div>
              </div>
              <div style="text-align: right;">
                <span style="background: rgba(217,119,6,0.3); border: 1px solid #F59E0B; color: #FEF3C7; font-size: 8px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                  <?= htmlspecialchars($anneeLibelle) ?>
                </span>
              </div>
            </div>

            <!-- Corps du Badge -->
            <div style="display: flex; gap: 14px; align-items: center; margin-top: 6px;">
              
              <!-- Photo / Avatar Étudiant avec cadre sécurité -->
              <div style="width: 78px; height: 96px; border-radius: 8px; border: 2px solid #F59E0B; background: #0F172A; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.3); flex-shrink: 0;">
                <?php if (!empty($item['photo_etudiant']) && file_exists(__DIR__ . '/../../public/' . $item['photo_etudiant'])): ?>
                  <img src="<?= RACINE . $item['photo_etudiant'] ?>" alt="Photo Étudiant" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <span style="font-size: 24px; font-weight: 900; color: #FDE68A;">
                    <?= strtoupper(substr($item['nom_etudiant'] ?? 'E', 0, 1) . substr($item['prenom_etudiant'] ?? 'T', 0, 1)) ?>
                  </span>
                <?php endif; ?>
                <span style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(217,119,6,0.9); font-size: 7px; font-weight: 800; text-align: center; color: #FFF; padding: 1px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                  ÉTUDIANT
                </span>
              </div>

              <!-- Informations de l'Étudiant -->
              <div style="flex: 1; min-width: 0;">
                <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase; letter-spacing: 0.5px;">NOM & PRÉNOMS</div>
                <div style="font-size: 13px; font-weight: 900; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                  <?= htmlspecialchars($nomComplet) ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-top: 6px;">
                  <div>
                    <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">MATRICULE</div>
                    <div style="font-size: 10px; font-weight: 800; color: #FDE68A; font-family: monospace; letter-spacing: 0.5px;">
                      <?= htmlspecialchars($matricule) ?>
                    </div>
                  </div>
                  <div>
                    <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">SEXE</div>
                    <div style="font-size: 10px; font-weight: 700; color: #FFF;">
                      <?= htmlspecialchars($item['sexe_etudiant'] ?? 'M') ?>
                    </div>
                  </div>
                </div>

                <div style="margin-top: 6px;">
                  <div style="font-size: 7px; font-weight: 800; color: #93C5FD; text-transform: uppercase;">CLASSE & FILIÈRE</div>
                  <div style="font-size: 10px; font-weight: 800; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($classeLibelle) ?>
                  </div>
                </div>
              </div>

              <!-- QR Code de vérification numérique -->
              <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; background: #FFFFFF; padding: 4px; border-radius: 6px; border: 1px solid #CBD5E1;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= urlencode('MAT:' . $matricule . '|NOM:' . $nomComplet . '|CLASSE:' . $classeLibelle . '|ANNEE:' . $anneeLibelle) ?>" alt="QR Code" style="width: 54px; height: 54px; display: block;">
                <span style="font-size: 6px; font-weight: 800; color: #0F172A; margin-top: 2px;">SCAN VALIDE</span>
              </div>

            </div>

            <!-- Pied du Recto -->
            <div style="position: absolute; bottom: 8px; left: 18px; right: 18px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 4px; font-size: 7px; color: #94A3B8;">
              <span>CARTE D'IDENTITÉ SCOLAIRE OFFICIELLE</span>
              <span style="color: #FDE68A; font-weight: 700;">VALABLE JUSQU'AU 31/08/2026</span>
            </div>

          </div>

          <!-- ===== 2. VERSO DU BADGE (FACE ARRIÈRE) ===== -->
          <div class="badge-card-container badge-verso" style="width: 390px; height: 245px; border-radius: 14px; background: #FFFFFF; color: #1E293B; position: relative; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25); overflow: hidden; padding: 14px 18px; box-sizing: border-box; border: 1px solid #CBD5E1; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            
            <div style="position: absolute; left: 0; top: 0; right: 0; height: 5px; background: #1E3A5F;"></div>

            <!-- Conditions & Règlement -->
            <div style="font-size: 8px; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;">
              CONDITIONS D'UTILISATION
            </div>
            <p style="font-size: 7.5px; line-height: 1.35; color: #475569; margin: 0 0 8px 0;">
              1. Cette carte est strictement personnelle et engage la responsabilité de son titulaire.<br>
              2. Elle doit être obligatoirement présentée à toute réquisition et lors des contrôles/examens.<br>
              3. En cas de perte ou de vol, aviser immédiatement le secrétariat de l'établissement.
            </p>

            <!-- Contacts d'urgence -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 10px; margin-bottom: 8px;">
              <div style="font-size: 7.5px; font-weight: 800; color: #0F172A;">CONTACTS EN CAS D'URGENCE :</div>
              <div style="font-size: 7.5px; color: #334155;">
                Tuteur : <strong><?= htmlspecialchars($parent['nom_tuteur'] ?? ($parent['nom_pere'] ?? 'Administration Scolaire')) ?></strong> - Tél : <strong><?= htmlspecialchars($parent['telephone_tuteur'] ?? ($parent['telephone_pere'] ?? $item['telephone_etudiant'] ?? '-')) ?></strong>
              </div>
            </div>

            <!-- Signatures et Cachet -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 4px;">
              <div style="text-align: center; width: 110px;">
                <div style="font-size: 7px; color: #64748B; text-transform: uppercase; margin-bottom: 18px;">Signature du Titulaire</div>
                <div style="border-bottom: 1px dashed #94A3B8; width: 100%;"></div>
              </div>

              <!-- Code-barre décoratif -->
              <div style="text-align: center;">
                <div style="font-family: monospace; font-size: 16px; letter-spacing: 2px; color: #0F172A; font-weight: 900;">|||| | ||||| || ||||</div>
                <div style="font-size: 7px; font-family: monospace; color: #64748B;"><?= htmlspecialchars($matricule) ?></div>
              </div>

              <div style="text-align: center; width: 110px;">
                <div style="font-size: 7px; color: #1E3A5F; font-weight: 700; text-transform: uppercase; margin-bottom: 18px;">Le Directeur Général</div>
                <div style="border-bottom: 1px dashed #94A3B8; width: 100%;"></div>
              </div>
            </div>

            <!-- Footer Verso -->
            <div style="position: absolute; bottom: 6px; left: 18px; right: 18px; border-top: 1px solid #E2E8F0; padding-top: 3px; font-size: 6.5px; color: #64748B; text-align: center;">
              <?= htmlspecialchars($etabNom) ?> &bull; <?= htmlspecialchars($etabAdresse) ?> &bull; Tél : <?= htmlspecialchars($etabTel) ?>
            </div>

          </div>

        </div>

      </div>

      <!-- CARD 1 (COL-12) : ÉTAT CIVIL & INSCRIPTION ACTIVE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="user" style="width: 18px; height: 18px;"></i> Identité & Parcours Pédagogique
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 64px; height: 76px; min-width: 64px; border-radius: 8px; border: 2px solid #CBD5E1; background: #EFF6FF; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.06); flex-shrink: 0;">
              <?php if (!empty($item['photo_etudiant']) && file_exists(__DIR__ . '/../../public/' . $item['photo_etudiant'])): ?>
                <img src="<?= RACINE . $item['photo_etudiant'] ?>" alt="Photo Étudiant" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <span style="font-size: 18px; font-weight: 900; color: #1E3A5F;">
                  <?= strtoupper(substr($item['nom_etudiant'] ?? 'E', 0, 1) . substr($item['prenom_etudiant'] ?? 'T', 0, 1)) ?>
                </span>
                <span style="font-size: 7.5px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-top: 2px;">PHOTO</span>
              <?php endif; ?>
            </div>
            <div>
              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0;">
                <?= htmlspecialchars($nomComplet) ?>
              </h2>
              <span style="font-size: 12px; color: #64748B;">Sexe : <strong><?= htmlspecialchars($item['sexe_etudiant'] ?? 'M') ?></strong> &bull; Nat. : <strong><?= htmlspecialchars($item['nationalite_etudiant'] ?? 'Ivoirienne') ?></strong></span>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Coordonnées de Contact</span>
            <div style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($item['telephone_etudiant'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #1E3A5F;"><?= htmlspecialchars($item['email_etudiant'] ?? 'Aucun email') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classe Actuelle</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($classeLibelle) ?></div>
            <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars(($inscription['libelle_filiere'] ?? '-') . ' / ' . ($inscription['libelle_niveau'] ?? '-')) ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Statut Dossier</span>
            <div style="margin-top: 4px;">
              <?php if (($item['statut_etudiant'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Actif / Régulier</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Inactif</span>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; padding-top: 14px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 16px;">
          <div><strong style="color: #64748B;">Date de Naissance :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_naissance_etudiant']) ? date('d/m/Y', strtotime($item['date_naissance_etudiant'])) : '-' ?></span> <?= !empty($item['lieu_naissance_etudiant']) ? 'à ' . htmlspecialchars($item['lieu_naissance_etudiant']) : '' ?></div>
          <div><strong style="color: #64748B;">Lieu de Résidence :</strong> <span style="color: #0F172A;"><?= htmlspecialchars($item['lieu_residence_etudiant'] ?? 'Non renseigné') ?></span></div>
          <?php if (!empty($item['matricule_menet'])): ?>
            <div><strong style="color: #64748B;">Matricule MENET-FP :</strong> <code style="font-weight: 700; color: #1E3A5F; background: #EFF6FF; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['matricule_menet']) ?></code></div>
          <?php endif; ?>
          <?php if (!empty($item['matricule_mesrs'])): ?>
            <div><strong style="color: #64748B;">Matricule MESRS :</strong> <code style="font-weight: 700; color: #1E3A5F; background: #EFF6FF; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['matricule_mesrs']) ?></code></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : PARENTS ET TUTEURS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i> Responsables Légaux & Filiation
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Père</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($parent['nom_pere'] ?? 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($parent['telephone_pere'] ?? '-') ?></div>
            <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Profession : <?= htmlspecialchars($parent['profession_pere'] ?? '-') ?></div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Mère</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($parent['nom_mere'] ?? 'Non renseignée') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($parent['telephone_mere'] ?? '-') ?></div>
            <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Profession : <?= htmlspecialchars($parent['profession_mere'] ?? '-') ?></div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Tuteur Légal / Correspondant</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($parent['nom_tuteur'] ?? 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($parent['telephone_tuteur'] ?? '-') ?></div>
            <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Profession : <?= htmlspecialchars($parent['profession_tuteur'] ?? '-') ?></div>
          </div>
        </div>
      </div>

      <!-- CARD : DOSSIER DE L'ÉTUDIANT & PIÈCES PHYSIQUES FOURNIES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <?php
          $totalPieces = count($dossierPieces ?? []);
          $deposedCount = 0;
          foreach (($dossierPieces ?? []) as $dp) {
              if (($dp['statut_depot'] ?? '') === 'depose') $deposedCount++;
          }
          $isComplet = ($totalPieces > 0 && $deposedCount === $totalPieces);
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="folder-check" style="width: 18px; height: 18px; color: #15803D;"></i> Dossier Étudiant & Pièces Fournies
            </h3>
            <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Pointage et contrôle des documents administratifs physiques déposés</p>
          </div>
          <div>
            <?php if ($isComplet): ?>
              <span class="badge" style="background: #DCFCE7; color: #15803D; padding: 6px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; border: 1px solid #86EFAC;">
                ✓ Dossier Complet (<?= $deposedCount ?> / <?= $totalPieces ?>)
              </span>
            <?php else: ?>
              <span class="badge" style="background: #FEF3C7; color: #B45309; padding: 6px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; border: 1px solid #FCD34D;">
                ⏳ Dossier Incomplet (<?= $deposedCount ?> / <?= $totalPieces ?> pièces fournies)
              </span>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($dossierPieces)): ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
            <?php foreach ($dossierPieces as $dp): 
              $isDep = (($dp['statut_depot'] ?? '') === 'depose');
            ?>
              <div style="background: <?= $isDep ? '#F0FDF4' : '#F8FAFC' ?>; border: 1px solid <?= $isDep ? '#BBF7D0' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px 16px; display: flex; align-items: flex-start; gap: 12px;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: <?= $isDep ? '#DCFCE7' : '#E2E8F0' ?>; color: <?= $isDep ? '#15803D' : '#64748B' ?>; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0; margin-top: 2px;">
                  <?= $isDep ? '✓' : '•' ?>
                </div>
                <div style="flex: 1;">
                  <div style="font-weight: 700; color: #0F172A; font-size: 13px; line-height: 1.35;">
                    <?= htmlspecialchars($dp['libelle_piece']) ?>
                  </div>
                  <?php if (!empty($dp['description_piece'])): ?>
                    <div style="font-size: 11.5px; color: #64748B; margin-top: 3px; line-height: 1.3;">
                      <?= htmlspecialchars($dp['description_piece']) ?>
                    </div>
                  <?php endif; ?>
                  <div style="margin-top: 6px;">
                    <?php if ($isDep): ?>
                      <span style="background: #DCFCE7; color: #15803D; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid #86EFAC;">
                        Déposée <?= !empty($dp['date_depot']) ? 'le ' . date('d/m/Y', strtotime($dp['date_depot'])) : '' ?>
                      </span>
                    <?php else: ?>
                      <span style="background: #FEF2F2; color: #DC2626; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid #FECACA;">
                        Non fournie / En attente
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="color: #94A3B8; text-align: center; padding: 16px 0; font-style: italic;">Aucune pièce répertoriée dans le dossier.</p>
        <?php endif; ?>
      </div>

      <!-- CARD 3 (COL-12) : SITUATION FINANCIÈRE & PAIEMENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="credit-card" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Situation Financière & Reçus de Scolarité
            </h3>
          </div>
          <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Nouveau règlement
          </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Scolarité Totale</span>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($scolariteTotale, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Total Versé (<?= $tauxPaiement ?>%)</span>
            <div style="font-size: 20px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format($totalPaye, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: <?= $soldeRestant > 0 ? '#FEF2F2' : '#F8FAFC' ?>; border: 1px solid <?= $soldeRestant > 0 ? '#FECACA' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: <?= $soldeRestant > 0 ? '#DC2626' : '#64748B' ?>; text-transform: uppercase;">Reste à Payer</span>
            <div style="font-size: 20px; font-weight: 800; color: <?= $soldeRestant > 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
              <?= $soldeRestant > 0 ? number_format($soldeRestant, 0, ',', ' ') . ' F' : 'Soldé (0 F)' ?>
            </div>
          </div>
        </div>

        <?php if (empty($paiements)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 20px 0; font-style: italic;">Aucun paiement enregistré pour cet étudiant.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Reçu N°</th>
                  <th style="padding: 10px;">Date</th>
                  <th style="padding: 10px;">Mode Règlement</th>
                  <th style="padding: 10px; text-align: right;">Montant Payé</th>
                  <th style="padding: 10px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($paiements as $p): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                      <a href="<?= RACINE ?>paiement/details/<?= $this->validator->crypter($p['id_paiement']) ?>" style="color: #1E3A5F; text-decoration: underline;">
                        <?= htmlspecialchars($p['reference_paiement'] ?? ($p['code_paiement'] ?? '-')) ?>
                      </a>
                    </td>
                    <td style="padding: 10px; color: #334155;"><?= !empty($p['date_paiement']) ? date('d/m/Y', strtotime($p['date_paiement'])) : '-' ?></td>
                    <td style="padding: 10px; color: #334155; text-transform: uppercase; font-size: 12px;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Espèces') ?></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #15803D;">
                      <?= number_format((float)($p['montant_paiement'] ?? ($p['montant_paye'] ?? 0)), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 10px; text-align: center;">
                      <span class="badge" style="background:#DCFCE7; color:#15803D; padding:2px 8px; border-radius:8px; font-weight:700; font-size:11px;">Validé</span>
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

<!-- ========================================================================= -->
<!-- STYLES D'IMPRESSION DÉDIÉS : IMPRESSION STRICTEMENT RÉSERVÉE AU BADGE -->
<!-- ========================================================================= -->
<style>
@media print {
  body * {
    visibility: hidden !important;
  }
  #section-carte-scolaire, #section-carte-scolaire * {
    visibility: visible !important;
  }
  #section-carte-scolaire {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 30px !important;
    padding: 20px !important;
    background: #FFF !important;
  }
  .badge-card-container {
    box-shadow: none !important;
    border: 1px solid #94A3B8 !important;
    page-break-inside: avoid !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<script>
function imprimerCarteScolaire() {
  window.print();
}

$(document).ready(function() { 
  if (window.lucide) lucide.createIcons(); 
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
