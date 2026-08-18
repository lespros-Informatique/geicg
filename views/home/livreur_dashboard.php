<?php
require_once __DIR__ . '/../../public/inc/header.php';

$stats = $stats ?? [];
$missions = $missions ?? [];
$livreurName = $stats['nom_livreur'] ?? ($currentUserName ?? 'Livreur');
$totalMissions = $stats['total_missions'] ?? 0;
$enAttente = $stats['missions_en_attente'] ?? 0;
$enCours = $stats['missions_en_cours'] ?? 0;
$termineesAuj = $stats['missions_terminees_aujourdhui'] ?? 0;
$totalSacs = $stats['total_sacs_transportes'] ?? 0;

// Identifier la prochaine mission prioritaire (première 'en_cours', sinon première 'en_attente')
$nextMission = null;
foreach ($missions as $m) {
    if ($m['statut_mission'] === 'en_cours') {
        $nextMission = $m;
        break;
    }
}
if (!$nextMission) {
    foreach ($missions as $m) {
        if ($m['statut_mission'] === 'en_attente') {
            $nextMission = $m;
            break;
        }
    }
}
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      
      <!-- === EN-TÊTE BIENVENUE LIVREUR === -->
      <div class="page-header" style="margin-bottom: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
              <i class="fa fa-motorcycle"></i>
            </div>
            <div>
              <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">
                Bonjour, <?= htmlspecialchars($livreurName) ?> !
              </h1>
              <p class="page-subtitle" style="margin: 2px 0 0; color: #64748B; font-size: 13px;">
                Espace Logistique & Tournées • <?= date('d/m/Y') ?>
              </p>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>mission/carte" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
            <i data-lucide="map" style="width: 16px; height: 16px;"></i> Carte des tournées
          </a>
          <a href="<?= RACINE ?>mission/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
            <i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i> Toutes mes missions
          </a>
        </div>
      </div>

      <!-- === 4 CARTES KPI DU LIVREUR === -->
      <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- 1. Missions en attente -->
        <div class="card" style="padding: 18px; border-radius: 14px; border: 1px solid #E2E8F0; background: #FFFFFF; display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <i class="fa fa-clock"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; display: block; text-transform: uppercase;">À démarrer</span>
            <strong style="font-size: 24px; font-weight: 800; color: #D97706; line-height: 1.2;"><?= $enAttente ?></strong>
            <small style="color: #94A3B8; font-size: 11px; display: block;">Missions en attente</small>
          </div>
        </div>

        <!-- 2. Courses en cours -->
        <div class="card" style="padding: 18px; border-radius: 14px; border: 1px solid #E2E8F0; background: #FFFFFF; display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <i class="fa fa-route"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; display: block; text-transform: uppercase;">En cours</span>
            <strong style="font-size: 24px; font-weight: 800; color: #2563EB; line-height: 1.2;"><?= $enCours ?></strong>
            <small style="color: #94A3B8; font-size: 11px; display: block;">Sur la route</small>
          </div>
        </div>

        <!-- 3. Terminées aujourd'hui -->
        <div class="card" style="padding: 18px; border-radius: 14px; border: 1px solid #E2E8F0; background: #FFFFFF; display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <i class="fa fa-check-circle"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; display: block; text-transform: uppercase;">Livrées aujourd'hui</span>
            <strong style="font-size: 24px; font-weight: 800; color: #059669; line-height: 1.2;"><?= $termineesAuj ?></strong>
            <small style="color: #94A3B8; font-size: 11px; display: block;">Succès du jour</small>
          </div>
        </div>

        <!-- 4. Sacs transportés -->
        <div class="card" style="padding: 18px; border-radius: 14px; border: 1px solid #E2E8F0; background: #FFFFFF; display: flex; align-items: center; gap: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <i class="fa fa-box-open"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; display: block; text-transform: uppercase;">Colis / Sacs</span>
            <strong style="font-size: 24px; font-weight: 800; color: #7C3AED; line-height: 1.2;"><?= $totalSacs ?></strong>
            <small style="color: #94A3B8; font-size: 11px; display: block;">Volume confié</small>
          </div>
        </div>

      </div>

      <!-- === CARTE MISE EN AVANT : PROCHAINE COURSE PRIORITAIRE === -->
      <?php if ($nextMission): 
        $isNextColis = ($nextMission['type_mission'] === 'collecte');
        $targetAdresse = !empty($nextMission['adresse_mission']) ? $nextMission['adresse_mission'] : ($nextMission['adresse_client'] ?? 'Abidjan');
        $gpsLat = !empty($nextMission['latitude_mission']) ? $nextMission['latitude_mission'] : ($nextMission['latitude_client'] ?? '');
        $gpsLng = !empty($nextMission['longitude_mission']) ? $nextMission['longitude_mission'] : ($nextMission['longitude_client'] ?? '');
        
        $mapsUrl = ($gpsLat && $gpsLng) 
            ? "https://www.google.com/maps/dir/?api=1&destination={$gpsLat},{$gpsLng}"
            : "https://www.google.com/maps/search/?api=1&query=" . urlencode($targetAdresse . ' Abidjan');
      ?>
      <div class="card" style="border: 2px solid <?= $isNextColis ? '#F59E0B' : '#2563EB' ?>; border-radius: 16px; padding: 22px; margin-bottom: 26px; background: <?= $isNextColis ? '#FFFDF5' : '#F8FAFF' ?>; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.06);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 16px;">
          <div>
            <span style="background: <?= $isNextColis ? '#FEF3C7' : '#EFF6FF' ?>; color: <?= $isNextColis ? '#92400E' : '#1E40AF' ?>; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
              ⚡ Course Prioritaire • <?= $isNextColis ? '📦 Collecte de linge' : '🚚 Livraison au client' ?>
            </span>
            <h2 style="margin: 8px 0 2px; font-size: 18px; font-weight: 800; color: #1E293B;">
              Mission #<?= htmlspecialchars($nextMission['code_mission']) ?> — Commande #<?= htmlspecialchars($nextMission['commande_code']) ?>
            </h2>
            <p style="margin: 0; color: #64748B; font-size: 13px;">
              Pressing : <strong><?= htmlspecialchars($nextMission['libelle_pressing'] ?? 'Pressing') ?></strong>
            </p>
          </div>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php if (!empty($nextMission['telephone_client'])): ?>
              <a href="tel:<?= htmlspecialchars($nextMission['telephone_client']) ?>" class="btn btn-success" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
                <i class="fa fa-phone"></i> Appeler le client (<?= htmlspecialchars($nextMission['telephone_client']) ?>)
              </a>
            <?php endif; ?>
            <a href="<?= $mapsUrl ?>" target="_blank" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i class="fa fa-location-arrow"></i> Lancer le GPS Google Maps
            </a>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; padding: 14px; background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; margin-bottom: 16px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; display: block; text-transform: uppercase;">Client</span>
            <strong style="font-size: 15px; color: #1E293B; display: block;"><?= htmlspecialchars($nextMission['nom_client'] ?? 'Client') ?></strong>
            <small style="color: #64748B;"><i class="fa fa-phone" style="font-size: 11px;"></i> <?= htmlspecialchars($nextMission['telephone_client'] ?? '-') ?></small>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; display: block; text-transform: uppercase;">Adresse de destination</span>
            <strong style="font-size: 14px; color: #1E293B; display: block;"><i class="fa fa-map-marker-alt" style="color: #DC2626;"></i> <?= htmlspecialchars($targetAdresse) ?></strong>
            <small style="color: #64748B;">Quartier : <?= htmlspecialchars($nextMission['quartier_client'] ?? 'Abidjan') ?></small>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; display: block; text-transform: uppercase;">Pressing Partenaire</span>
            <strong style="font-size: 14px; color: #1E293B; display: block;"><i class="fa fa-store" style="color: #2563EB;"></i> <?= htmlspecialchars($nextMission['libelle_pressing'] ?? 'Pressing') ?></strong>
            <small style="color: #64748B;"><?= htmlspecialchars($nextMission['adresse_pressing'] ?? '') ?></small>
          </div>
        </div>

        <!-- ACTIONS IMMÉDIATES 1-CLIC POUR LE LIVREUR -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
          <div style="font-size: 13px; color: #475569;">
            Statut actuel : <span class="badge-status <?= $nextMission['statut_mission'] === 'en_cours' ? 'badge-status-progress' : '' ?>"><?= htmlspecialchars($nextMission['statut_mission']) ?></span>
          </div>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php if ($isNextColis): ?>
              <?php if ($nextMission['statut_mission'] === 'en_attente'): ?>
                <button type="button" class="btn btn-primary" onclick="triggerLivreurAction('enRouteCollecte', '<?= $nextMission['code_mission'] ?>')" style="background: #D97706; border-color: #D97706; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fa fa-motorcycle"></i> 1. Démarrer la collecte
                </button>
              <?php elseif ($nextMission['statut_mission'] === 'en_cours'): ?>
                <button type="button" class="btn btn-primary" onclick="triggerLivreurAction('lingeCollecte', '<?= $nextMission['code_mission'] ?>')" style="background: #2563EB; border-color: #2563EB; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fa fa-box"></i> 2. Confirmer linge collecté
                </button>
                <button type="button" class="btn btn-success" onclick="triggerLivreurAction('deposeAuPressing', '<?= $nextMission['code_mission'] ?>')" style="background: #059669; border-color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fa fa-store"></i> 3. Déposé au pressing (Terminer)
                </button>
              <?php endif; ?>
            <?php else: ?>
              <?php if ($nextMission['statut_mission'] === 'en_attente'): ?>
                <button type="button" class="btn btn-primary" onclick="triggerLivreurAction('enRouteLivraison', '<?= $nextMission['code_mission'] ?>')" style="background: #2563EB; border-color: #2563EB; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fa fa-truck"></i> 1. En route pour livrer
                </button>
              <?php elseif ($nextMission['statut_mission'] === 'en_cours'): ?>
                <button type="button" class="btn btn-success" onclick="triggerLivreurAction('remiseAuClient', '<?= $nextMission['code_mission'] ?>')" style="background: #059669; border-color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fa fa-check-circle"></i> 2. Confirmer la remise au client
                </button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- === LISTE DE TOUTES LES MISSIONS DE LA TOURNÉE === -->
      <div class="card" style="border-radius: 16px; border: 1px solid #E2E8F0; padding: 22px; background: #FFFFFF;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;">Mes Tournées & Courses</h3>
            <p style="margin: 2px 0 0; font-size: 13px; color: #64748B;">Historique et suivi complet de vos livraisons</p>
          </div>

          <div style="display: flex; gap: 6px; background: #F1F5F9; padding: 4px; border-radius: 10px;">
            <button class="btn btn-sm btn-filter active" data-filter="all" onclick="filterMissionList('all', this)" style="border-radius: 6px; font-weight: 700; font-size: 12px; padding: 6px 12px; background: #FFFFFF; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">Toutes (<?= count($missions) ?>)</button>
            <button class="btn btn-sm btn-filter" data-filter="en_cours" onclick="filterMissionList('en_cours', this)" style="border-radius: 6px; font-weight: 600; font-size: 12px; padding: 6px 12px; background: transparent; border: none;">En cours</button>
            <button class="btn btn-sm btn-filter" data-filter="en_attente" onclick="filterMissionList('en_attente', this)" style="border-radius: 6px; font-weight: 600; font-size: 12px; padding: 6px 12px; background: transparent; border: none;">À faire</button>
            <button class="btn btn-sm btn-filter" data-filter="terminee" onclick="filterMissionList('terminee', this)" style="border-radius: 6px; font-weight: 600; font-size: 12px; padding: 6px 12px; background: transparent; border: none;">Terminées</button>
          </div>
        </div>

        <?php if (empty($missions)): ?>
          <div style="padding: 40px; text-align: center; color: #94A3B8;">
            <i class="fa fa-clipboard-check" style="font-size: 40px; margin-bottom: 12px; display: block; color: #CBD5E1;"></i>
            <h4 style="margin: 0; font-size: 16px; color: #475569;">Aucune mission assignée pour le moment</h4>
            <p style="margin: 4px 0 0; font-size: 13px;">Dès qu'un pressing vous assigne une course, elle apparaîtra directement ici.</p>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 12px;" id="missionsFeed">
            <?php foreach ($missions as $m): 
              $isCol = ($m['type_mission'] === 'collecte');
              $mLat = !empty($m['latitude_mission']) ? $m['latitude_mission'] : ($m['latitude_client'] ?? '');
              $mLng = !empty($m['longitude_mission']) ? $m['longitude_mission'] : ($m['longitude_client'] ?? '');
              $mAdr = !empty($m['adresse_mission']) ? $m['adresse_mission'] : ($m['adresse_client'] ?? 'Abidjan');
              $mMapsUrl = ($mLat && $mLng)
                  ? "https://www.google.com/maps/dir/?api=1&destination={$mLat},{$mLng}"
                  : "https://www.google.com/maps/search/?api=1&query=" . urlencode($mAdr . ' Abidjan');
            ?>
              <div class="mission-item-card card" data-status="<?= htmlspecialchars($m['statut_mission']) ?>" style="padding: 16px; border-radius: 12px; border: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                  <div style="width: 42px; height: 42px; border-radius: 10px; background: <?= $isCol ? '#FEF3C7' : '#EFF6FF' ?>; color: <?= $isCol ? '#D97706' : '#2563EB' ?>; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa <?= $isCol ? 'fa-box' : 'fa-truck' ?>"></i>
                  </div>
                  <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <strong style="font-size: 14px; color: #1E293B;">#<?= htmlspecialchars($m['code_mission']) ?></strong>
                      <span style="font-size: 11px; padding: 2px 8px; border-radius: 12px; background: <?= $isCol ? '#FFFBEB' : '#F0FDF4' ?>; color: <?= $isCol ? '#B45309' : '#15803D' ?>; font-weight: 700;">
                        <?= $isCol ? 'Collecte' : 'Livraison' ?>
                      </span>
                      <span class="badge-status <?= $m['statut_mission'] === 'terminee' ? 'delivered' : ($m['statut_mission'] === 'en_cours' ? 'badge-status-progress' : '') ?>" style="font-size: 10px;">
                        <?= htmlspecialchars($m['statut_mission']) ?>
                      </span>
                    </div>
                    <div style="font-size: 13px; color: #475569; margin-top: 3px;">
                      Client : <strong><?= htmlspecialchars($m['nom_client'] ?? 'Client') ?></strong> • <i class="fa fa-map-marker-alt" style="color: #DC2626; font-size: 11px;"></i> <?= htmlspecialchars($mAdr) ?>
                    </div>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                  <?php if (!empty($m['telephone_client'])): ?>
                    <a href="tel:<?= htmlspecialchars($m['telephone_client']) ?>" class="btn btn-sm btn-secondary" title="Appeler" style="padding: 6px 10px;">
                      <i class="fa fa-phone" style="color: #059669;"></i>
                    </a>
                  <?php endif; ?>
                  
                  <a href="<?= $mMapsUrl ?>" target="_blank" class="btn btn-sm btn-secondary" title="Guidage GPS" style="padding: 6px 10px; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                    <i class="fa fa-location-arrow" style="color: #2563EB;"></i> GPS
                  </a>

                  <?php if ($m['statut_mission'] === 'en_attente'): ?>
                    <button type="button" class="btn btn-sm btn-primary" onclick="triggerLivreurAction('<?= $isCol ? 'enRouteCollecte' : 'enRouteLivraison' ?>', '<?= $m['code_mission'] ?>')" style="padding: 6px 12px; font-weight: 700;">
                      Démarrer
                    </button>
                  <?php elseif ($m['statut_mission'] === 'en_cours'): ?>
                    <?php if ($isCol): ?>
                      <button type="button" class="btn btn-sm btn-primary" onclick="triggerLivreurAction('lingeCollecte', '<?= $m['code_mission'] ?>')" style="padding: 6px 10px; font-weight: 700;">
                        Linge pris
                      </button>
                      <button type="button" class="btn btn-sm btn-success" onclick="triggerLivreurAction('deposeAuPressing', '<?= $m['code_mission'] ?>')" style="padding: 6px 10px; font-weight: 700;">
                        Au pressing
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-sm btn-success" onclick="triggerLivreurAction('remiseAuClient', '<?= $m['code_mission'] ?>')" style="padding: 6px 12px; font-weight: 700;">
                        Livré
                      </button>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script>
function filterMissionList(status, btn) {
  $('.btn-filter').removeClass('active').css({ 'background': 'transparent', 'box-shadow': 'none' });
  $(btn).addClass('active').css({ 'background': '#FFFFFF', 'box-shadow': '0 1px 3px rgba(0,0,0,0.1)' });

  if (status === 'all') {
    $('.mission-item-card').show();
  } else {
    $('.mission-item-card').each(function() {
      if ($(this).attr('data-status') === status) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  }
}

function triggerLivreurAction(actionName, codeMission) {
  const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
  
  if (typeof showToast === 'function') {
    showToast('Mise à jour de la mission...', 'info');
  }

  $.post(baseApi + 'mission/' + actionName, { code_mission: codeMission }, function(rep) {
    if (rep.status) {
      if (typeof showToast === 'function') showToast(rep.message || 'Action exécutée avec succès !', 'success');
      setTimeout(function() {
        window.location.reload();
      }, 700);
    } else {
      if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'action', 'error');
    }
  }, 'json').fail(function() {
    if (typeof showToast === 'function') showToast('Erreur de connexion au serveur', 'error');
  });
}
</script>

<script src="<?= RACINE ?>public/json/livreur-gps-tracker.js?v=2"></script>
<script>
$(document).ready(function() {
  const codeLiv = '<?= htmlspecialchars($livreurCode ?? '') ?>';
  const hasActiveMission = <?= ($enCours > 0) ? 'true' : 'false' ?>;
  if (typeof LivreurGpsTracker !== 'undefined') {
    LivreurGpsTracker.init(null, codeLiv, hasActiveMission);
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
