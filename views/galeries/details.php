<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$url     = $item['url_fichier']         ?? '';
$type    = $item['type_galerie']        ?? '';
$titre   = $item['titre_galerie']       ?? '';
$desc    = $item['description_galerie'] ?? '';
$statut  = $item['statut_galerie']      ?? '';
$code    = $item['code_galerie']        ?? '-';
$id      = $item['id_galerie']          ?? '-';
$created = $item['created_at_galerie']  ?? '';

$isYoutube = preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $ytm);
$isVimeo   = preg_match('/vimeo\.com\/(\d+)/', $url, $vmm);
$isVideo   = preg_match('/\.(mp4|mov|avi|webm)(\?.*)?$/i', $url);
$isImage   = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i', $url);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">

      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Détaillée : <?= htmlspecialchars($titre ?: 'Galerie Médias') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation complète des données du module Galeries Photos &amp; Vidéos</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>galerie/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>galerie/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Éditer cet élément
          </a>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start;">

        <!-- Aperçu du média -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #E2E8F0;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="<?= $type === 'video' ? 'video' : 'image' ?>" style="width: 18px; height: 18px;"></i>
              </div>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: #0F172A;">Aperçu du Média</div>
                <div style="font-size: 12px; color: #64748B;">Réf. <?= htmlspecialchars($code) ?></div>
              </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <?php if ($statut === 'actif'): ?>
                <span style="background: #DCFCE7; color: #15803D; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;">Actif</span>
              <?php else: ?>
                <span style="background: #FEE2E2; color: #B91C1C; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;">Inactif</span>
              <?php endif; ?>
              <span style="background: #F1F5F9; color: #475569; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;"><?= $type === 'video' ? 'Vidéo' : 'Photo' ?></span>
            </div>
          </div>

          <div style="background: #F8FAFC; min-height: 300px; display: flex; align-items: center; justify-content: center;">
            <?php if (!$url): ?>
              <div style="text-align: center; color: #94A3B8; padding: 60px 20px;">
                <i data-lucide="image-off" style="width: 48px; height: 48px; opacity: 0.4;"></i>
                <p style="margin-top: 10px; font-size: 13px; font-weight: 500;">Aucun média disponible</p>
              </div>
            <?php elseif ($isYoutube): ?>
              <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytm[1]) ?>"
                style="width: 100%; height: 360px; border: none;" allowfullscreen loading="lazy"></iframe>
            <?php elseif ($isVimeo): ?>
              <iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($vmm[1]) ?>"
                style="width: 100%; height: 360px; border: none;" allowfullscreen loading="lazy"></iframe>
            <?php elseif ($isVideo): ?>
              <video src="<?= htmlspecialchars($url) ?>" controls
                style="width: 100%; max-height: 360px; display: block;"></video>
            <?php elseif ($isImage): ?>
              <img src="<?= htmlspecialchars($url) ?>" alt="<?= htmlspecialchars($titre) ?>"
                style="width: 100%; max-height: 360px; object-fit: contain; display: block;"
                onerror="this.parentNode.innerHTML='<div style=\'text-align:center;color:#94A3B8;padding:60px 20px\'><i data-lucide=\'image-off\' style=\'width:48px;height:48px;opacity:0.4;\'><\/i><p style=\'margin-top:10px;font-size:13px\'>Image non accessible<\/p><\/div>';">
            <?php else: ?>
              <div style="text-align: center; color: #94A3B8; padding: 60px 20px;">
                <i data-lucide="link" style="width: 48px; height: 48px; opacity: 0.4;"></i>
                <p style="margin-top: 10px; font-size: 13px; font-weight: 500;">Aperçu non disponible</p>
                <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                  style="display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; color: #1E3A5F; font-size: 13px; font-weight: 600; text-decoration: none;">
                  <i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Ouvrir le lien
                </a>
              </div>
            <?php endif; ?>
          </div>

          <?php if ($url): ?>
          <div style="padding: 14px 20px; border-top: 1px solid #E2E8F0; background: #FFFFFF;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Lien / URL du fichier</div>
            <div style="display: flex; align-items: center; gap: 10px;">
              <span style="font-size: 13px; color: #334155; word-break: break-all; flex: 1; line-height: 1.5;"><?= htmlspecialchars($url) ?></span>
              <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                style="flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; background: #1E3A5F; color: #fff; padding: 7px 14px; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none;">
                <i data-lucide="external-link" style="width: 13px; height: 13px;"></i> Ouvrir
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Informations -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #F1F5F9;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
              <div style="font-size: 14px; font-weight: 700; color: #0F172A;">Informations</div>
              <div style="font-size: 12px; color: #64748B;">ID #<?= htmlspecialchars($id) ?></div>
            </div>
          </div>

          <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
              <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Code Galerie</div>
              <div style="font-size: 13px; font-weight: 700; color: #1E3A5F; font-family: monospace;"><?= htmlspecialchars($code) ?></div>
            </div>

            <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
              <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Titre de l'Album</div>
              <div style="font-size: 14px; font-weight: 600; color: #0F172A; word-break: break-word;">
                <?= $titre ? htmlspecialchars($titre) : '<span style="color:#94A3B8; font-style:italic; font-weight:400;">Non renseigné</span>' ?>
              </div>
            </div>

            <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
              <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Type de Média</div>
              <div style="display: flex; align-items: center; gap: 6px;">
                <i data-lucide="<?= $type === 'video' ? 'video' : 'image' ?>" style="width: 14px; height: 14px; color: #64748B;"></i>
                <span style="font-size: 14px; font-weight: 600; color: #0F172A;"><?= $type === 'video' ? 'Album Vidéos' : 'Album Photos' ?></span>
              </div>
            </div>

            <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
              <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Description</div>
              <div style="font-size: 13px; color: #334155; line-height: 1.6;">
                <?= $desc ? nl2br(htmlspecialchars($desc)) : '<span style="color:#94A3B8; font-style:italic; font-weight:400;">Non renseignée</span>' ?>
              </div>
            </div>

            <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
              <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Statut</div>
              <?php if ($statut === 'actif'): ?>
                <span style="background: #DCFCE7; color: #15803D; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;">Actif</span>
              <?php else: ?>
                <span style="background: #FEE2E2; color: #B91C1C; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;">Inactif</span>
              <?php endif; ?>
            </div>

            <?php if ($created): ?>
            <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
              <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Date d'Ajout</div>
              <div style="display: flex; align-items: center; gap: 6px;">
                <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748B;"></i>
                <span style="font-size: 13px; font-weight: 600; color: #0F172A;"><?= date('d/m/Y à H:i', strtotime($created)) ?></span>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
<style>
@media (max-width: 960px) {
  .content-wrapper > div[style*="grid-template-columns: 1fr 360px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>



$isVimeo   = preg_match('/vimeo\.com\/(\d+)/', $url, $vmm);
$isVideo   = preg_match('/\.(mp4|mov|avi|webm)(\?.*)?$/i', $url);
$isImage   = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i', $url);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">

      <!-- En-tête -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
            <?= $type === 'video' ? '🎬' : '🖼️' ?> <?= htmlspecialchars($titre ?: 'Galerie Médias') ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation complète des données · Galeries Photos &amp; Vidéos</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>galerie/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>galerie/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Éditer
          </a>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; flex-wrap: wrap;">

        <!-- Colonne gauche : Aperçu du média -->
        <div>
          <div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="background: #0F172A; border-radius: 12px 12px 0 0; min-height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 14px 20px;">
              <span style="font-weight: 700; color: #fff; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="<?= $type === 'video' ? 'video' : 'image' ?>" style="width:18px;height:18px;"></i>
                Aperçu du Média
              </span>
              <?php if ($type === 'video'): ?>
                <span style="background:#7C3AED; color:#fff; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700;">Vidéo</span>
              <?php else: ?>
                <span style="background:#0EA5E9; color:#fff; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700;">Photo</span>
              <?php endif; ?>
            </div>
            <div style="padding: 0; background: #111827; min-height: 280px; display: flex; align-items: center; justify-content: center;">
              <?php if (!$url): ?>
                <div style="text-align: center; color: #6B7280; padding: 60px 20px;">
                  <i data-lucide="image-off" style="width:60px;height:60px;opacity:0.3;"></i>
                  <p style="margin-top:12px; font-size:14px;">Aucun média disponible</p>
                </div>
              <?php elseif ($isYoutube): ?>
                <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytm[1]) ?>"
                  style="width:100%; height:380px; border:none;" allowfullscreen loading="lazy"></iframe>
              <?php elseif ($isVimeo): ?>
                <iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($vmm[1]) ?>"
                  style="width:100%; height:380px; border:none;" allowfullscreen loading="lazy"></iframe>
              <?php elseif ($isVideo): ?>
                <video src="<?= htmlspecialchars($url) ?>" controls
                  style="width:100%; max-height:380px; display:block;"></video>
              <?php elseif ($isImage): ?>
                <img src="<?= htmlspecialchars($url) ?>" alt="<?= htmlspecialchars($titre) ?>"
                  style="width:100%; max-height:380px; object-fit:contain; display:block;"
                  onerror="this.style.display='none'; document.getElementById('img_err').style.display='flex';">
                <div id="img_err" style="display:none; flex-direction:column; align-items:center; color:#6B7280; padding:60px 20px; text-align:center;">
                  <i data-lucide="image-off" style="width:50px;height:50px;opacity:0.3;"></i>
                  <p style="margin-top:10px;">Image non accessible</p>
                </div>
              <?php else: ?>
                <div style="text-align:center; color:#6B7280; padding:60px 20px;">
                  <i data-lucide="link" style="width:48px;height:48px;opacity:0.3;"></i>
                  <p style="margin-top:12px; font-size:13px;">Aperçu non disponible pour cette URL</p>
                  <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                    style="margin-top:10px; display:inline-block; color:#3B82F6; font-size:13px; word-break:break-all;">
                    <?= htmlspecialchars($url) ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
            <?php if ($url): ?>
            <div style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid #E2E8F0;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Lien / URL</div>
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 13px; color: #0F172A; word-break: break-all; flex: 1;"><?= htmlspecialchars($url) ?></span>
                <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                  style="flex-shrink:0; background:#1E3A5F; color:#fff; padding:6px 14px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                  <i data-lucide="external-link" style="width:13px;height:13px;"></i> Ouvrir
                </a>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Colonne droite : Informations -->
        <div style="display: flex; flex-direction: column; gap: 16px;">

          <!-- Badge statut + type -->
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px;">
              <?php if ($statut === 'actif'): ?>
                <span style="background:#DCFCE7; color:#15803D; padding:6px 16px; border-radius:20px; font-weight:700; font-size:13px;">✅ Actif</span>
              <?php else: ?>
                <span style="background:#FEE2E2; color:#B91C1C; padding:6px 16px; border-radius:20px; font-weight:700; font-size:13px;">❌ Inactif</span>
              <?php endif; ?>
              <?php if ($type === 'video'): ?>
                <span style="background:#EDE9FE; color:#7C3AED; padding:6px 16px; border-radius:20px; font-weight:700; font-size:13px;">🎬 Album Vidéos</span>
              <?php else: ?>
                <span style="background:#E0F2FE; color:#0369A1; padding:6px 16px; border-radius:20px; font-weight:700; font-size:13px;">🖼️ Album Photos</span>
              <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Référence</div>
                <div style="font-size: 14px; font-weight: 700; color: #1E3A5F; font-family: monospace;"><?= htmlspecialchars($code) ?></div>
              </div>

              <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Titre de l'Album</div>
                <div style="font-size: 15px; font-weight: 600; color: #0F172A;">
                  <?= $titre ? htmlspecialchars($titre) : '<span style="color:#94A3B8; font-style:italic;">Non renseigné</span>' ?>
                </div>
              </div>

              <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Description</div>
                <div style="font-size: 14px; color: #334155; line-height: 1.6;">
                  <?= $desc ? nl2br(htmlspecialchars($desc)) : '<span style="color:#94A3B8; font-style:italic;">Aucune description</span>' ?>
                </div>
              </div>

              <?php if ($created): ?>
              <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">Date d'Ajout</div>
                <div style="font-size: 14px; font-weight: 600; color: #0F172A;">
                  <?= date('d/m/Y à H:i', strtotime($created)) ?>
                </div>
              </div>
              <?php endif; ?>

              <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; border: 1px solid #F1F5F9;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 5px;">ID Enregistrement</div>
                <div style="font-size: 14px; font-weight: 600; color: #0F172A;">#<?= htmlspecialchars($id) ?></div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </main>
</div>
<style>
@media (max-width: 900px) {
  .content-wrapper > div[style*="grid-template-columns: 1fr 380px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
