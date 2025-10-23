<?php
/***********************
 * Main Dashboard Design
 **********************/
?>
<!doctype html>
<html lang="en" <?php $dir = service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?> dir="<?= $dir ?>">

    <head>
        <title><?= $pageTitle ?></title>
        <?= $this->renderSection('head') ?>

        <?php if( $myConfig['PWA_App_enabled'] || $myConfig['push_notification_feature_enabled'] ) : ?>
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('<?= base_url('service-worker.js') ?>').then(function(registration) {
                        console.log('✅ Service Worker registration successful with scope: ', registration.scope);
                    }, function(err) {
                        console.log('❌ Service Worker registration failed: ', err);
                    });
                });
            }
        </script>
        <?php endif; ?>

        <?php if($myConfig['PWA_App_enabled']) : ?>
        <link rel="manifest" href="/manifest.json">
        <?php endif; ?>

        <?php include_once APPPATH . 'Views/includes/general/head.php'; ?>

        <style>
            .title {
                word-wrap: break-word;
                white-space: normal;
                max-width: 100%;
            }
            #notification-list {
                flex-grow: 1; /* Makes it expand to push the footer down */
                min-height: 420px; /* Ensures it does not collapse when empty */
                overflow-y: auto;
            }
            .sticky-header {
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .sticky-footer {
                position: sticky;
                bottom: 0;
                z-index: 10;
            }
        </style>
    </head>

    <body>
        <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>

        <div class="page-wrapper toggled">
            <!-- sidebar-wrapper -->
            <?php include_once APPPATH . 'Views/includes/dashboard/sidebar.php'; ?>
            <!-- sidebar-wrapper  -->

            <!-- Start Page Content -->
            <main class="page-content bg-light">
                <!-- Top Header -->
                <?php include_once APPPATH . 'Views/includes/dashboard/top_header.php'; ?>
                <!-- Top Header -->

                <!--- START MAIN SECTION -->
                <div class="container-fluid">
                    <div class="layout-specing">
                        <!-- Heading -->
                        <?= $this->renderSection('heading') ?>
                        
                        <!-- Main content -->
                        <?= $this->renderSection('content') ?>

                    </div>
                </div><!--end container-->

                <!-- Footer Start -->
                <?php include_once APPPATH . 'Views/includes/dashboard/footer.php'; ?>
                <!-- End -->
            </main>
            <!--End page-content" -->
        </div>
        <!-- page-wrapper -->
        
        <!-- javascript -->
        <!-- JAVASCRIPT -->
        <?php include_once APPPATH . 'Views/includes/dashboard/js-scripts.php'; ?>
        <?= $this->renderSection('modals') ?>
        <?= $this->renderSection('scripts') ?>
    </body>
</html>