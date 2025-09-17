<div class="row">
            <div class="col- text-center" id="install-app" style="display: none;">
                <div class="alert alert-info d-inline-block mx-auto">
                    <?= lang('Pages.notice_install_pwa_app') ?>
                    <button id="install-button" class="btn btn-success btn-sm"><?= lang('Pages.Install') ?></button>
                </div>
            </div>
        </div>
        
        <script>
            let deferredPrompt;
            const installApp = document.getElementById('install-app');
            const installButton = document.getElementById('install-button');

            installApp.style.display = 'none'; // Hide banner initially

            async function checkIfAlreadyInstalled() {
                if ('getInstalledRelatedApps' in navigator) {
                    const relatedApps = await navigator.getInstalledRelatedApps();
                    if (relatedApps.length > 0) {
                        console.log('App already installed (related apps):', relatedApps);
                        return true;
                    }
                }

                // Fallback checks for standalone mode
                return (
                    window.matchMedia('(display-mode: standalone)').matches ||
                    window.navigator.standalone === true || // for iOS Safari
                    document.referrer.startsWith('android-app://')
                );
            }

            // Handle install prompt availability
            window.addEventListener('beforeinstallprompt', async (e) => {
                e.preventDefault();
                deferredPrompt = e;

                const isInstalled = await checkIfAlreadyInstalled();
                if (!isInstalled) {
                    installApp.style.display = 'block';
                }
            });

            // Install button click handler
            installButton.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`Install prompt outcome: ${outcome}`);
                    installApp.style.display = 'none';
                    deferredPrompt = null;
                }
            });

            // Hide banner if app is installed
            window.addEventListener('appinstalled', () => {
                console.log('App successfully installed');
                installApp.style.display = 'none';
            });

            // Extra: Optional initial check (e.g., on page reload)
            checkIfAlreadyInstalled().then((installed) => {
                if (installed) {
                    console.log('App is already installed');
                    installApp.style.display = 'none';
                }
            });
        </script>
