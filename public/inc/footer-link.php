<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= RACINE ?>json/func.js?v=<?= time() ?>"></script>
<script src="<?= RACINE ?>json/validator.js"></script>
<script src="<?= RACINE ?>json/app.js?v=<?= time() ?>"></script>
<script src="<?= RACINE ?>json/auth.js"></script>
<script src="<?= RACINE ?>json/theme-manager.js"></script>
<script>
    try {
        lucide.createIcons();
    } catch(e) {
        console.warn('lucide error:', e);
    }
    window.addEventListener('error', function(e) {
        console.error('[global error]', e.message, 'at', e.filename + ':' + e.lineno);
    });
</script>

<!-- OneSignal Web Push SDK pour Pressings & Livreurs -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.requestPushPermission = async function() {
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    window.OneSignalDeferred.push(async function(OneSignal) {
      try {
        if (typeof OneSignal.Notifications !== 'undefined' && typeof OneSignal.Notifications.requestPermission === 'function') {
          await OneSignal.Notifications.requestPermission();
        } else if (typeof OneSignal.Slidedown !== 'undefined' && typeof OneSignal.Slidedown.promptPush === 'function') {
          await OneSignal.Slidedown.promptPush();
        }
        const banner = document.getElementById('pushBannerAdmin');
        if (banner) banner.style.display = 'none';
      } catch (err) {
        console.warn('Erreur activation push:', err);
      }
    });
  };

  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    try {
      const appId = "<?= defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : '' ?>";
      const targetUserCode = "<?= !empty($currentPressingCode) ? $currentPressingCode : (!empty($currentLivreurCode) ? $currentLivreurCode : ($currentUserCode ?? '')) ?>";

      if (appId && typeof OneSignal.init === 'function') {
        await OneSignal.init({
          appId: appId,
          notifyButton: { enable: false },
          allowLocalhostAsSecureOrigin: true,
          serviceWorkerParam: { scope: '/' },
          serviceWorkerPath: 'OneSignalSDKWorker.js'
        });

        if (targetUserCode && typeof OneSignal.login === 'function') {
          await OneSignal.login(targetUserCode);

          // Tenter l'invite Slidedown / Permission Push
          if (typeof OneSignal.Notifications !== 'undefined' && !OneSignal.Notifications.permission) {
            const banner = document.getElementById('pushBannerAdmin');
            if (banner) banner.style.display = 'flex';

            if (typeof OneSignal.Slidedown !== 'undefined' && typeof OneSignal.Slidedown.promptPush === 'function') {
              await OneSignal.Slidedown.promptPush();
            } else if (typeof OneSignal.Notifications.requestPermission === 'function') {
              await OneSignal.Notifications.requestPermission();
            }
          }
        }
      }
    } catch (e) {
      console.log("OneSignal push actif uniquement sur le domaine enregistré.");
    }
  });
</script>
