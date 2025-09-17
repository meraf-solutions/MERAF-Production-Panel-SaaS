<?php
/********************************************************************
 * Single Page Design w/o Sidebars and top header for LOGGED IN USERS
 *******************************************************************/
?>
<!doctype html>
<html lang="en" <?php $dir = service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?> dir="<?= $dir ?>">

    <head>
        <title><?= $pageTitle ?></title>
        <?php include_once APPPATH . 'Views/includes/general/head.php'; ?>
        <?= $this->renderSection('head') ?>
    </head>

    <body>
        <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>

        <!-- Alert Section -->
        <?php if (isset($alert)): 
            $type = 'info';

            if (array_key_exists('type', $alert)) {
                $type = $alert['type'];
            }
            
            if (array_key_exists('success', $alert)) {
                $type = $alert['success'] ? 'success' : 'danger';
            }
        ?>
            <section class="bg-home bg-circle-gradiant d-flex align-items-center">
                <div class="bg-overlay bg-overlay-white"></div>
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="card form-signin p-4 rounded shadow">
                                    <a href="<?= base_url() ?>"><img src="<?= $myConfig['appIcon'] ?>" class="avatar avatar-small mb-4 d-block mx-auto" alt="Logo"></a>
                                    <h5 class="mb-5 text-center"><?= $myConfig['appName'] ?></h5>

                                    <div class="alert alert-<?= $type ?> fade show text-center" role="alert">
                                        <?= $alert['message'] ?>
                                    </div>

                                    <?php if (isset($alert) && 
                                    array_key_exists('redirect', $alert) && 
                                    $alert['redirect'] !== null && 
                                    $alert['redirect'] !== ''): ?>
                                        <div class="alert alert-info fade show text-center" role="alert" id="redirect-alert">
                                            
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($alert['copiedFileList']) && !empty($alert['copiedFileList'])) : ?>
                                        <div class="alert alert-info fade show text-center" role="alert">
                                            See the list of backed up files <a href="#copiedFileList" class="alert-link" data-bs-toggle="modal" data-bs-target="#copiedFileList">here</a>.
                                        </div>

                                        <div class="modal fade" id="copiedFileList" tabindex="-1" aria-labelledby="copiedFileList-title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                                                <div class="modal-content rounded shadow border-0">
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title">Files backed up:</h5>
                                                        <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                            // Sort the list for better readability
                                                            sort($alert['copiedFileList']);
                                                            // Limit the number of files logged to prevent excessive log size
                                                            $logLimit = 1000;
                                                            $loggedFiles = array_slice($alert['copiedFileList'], 0, $logLimit);
                                                            if (count($alert['copiedFileList']) > $logLimit) {
                                                                $loggedFiles[] = "... and " . (count($alert['copiedFileList']) - $logLimit) . " more files";
                                                            }
                                                            echo implode("<br>", $loggedFiles);
                                                        ?>
                                                    </div><!-- end modal body -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <p class="mb-0 text-muted mt-3 text-center">
                                        <?= lang('Pages.footer_copyright', ['year' => date("Y"),'appName' => $myConfig['appName']]) ?>
                                    </p>

                            </div>
                        </div>
                    </div>
                </div> <!--end container-->
            </section><!--end section-->
        <?php endif; ?>

        <!-- Hero Start -->
        <?= $this->renderSection('content') ?>
        <!-- Hero End -->
        
        <!-- javascript -->
        <!-- JAVASCRIPT -->
        <?php include_once APPPATH . 'Views/includes/general/js-assets.php'; ?>
        <?= $this->renderSection('modals') ?>
        <?= $this->renderSection('scripts') ?>

        <?php if (isset($alert) && 
           array_key_exists('redirect', $alert) && 
           $alert['redirect'] !== null && 
           $alert['redirect'] !== ''): ?>
            <script type="text/javascript">
                delayedRedirect("<?= htmlspecialchars($alert['redirect'], ENT_QUOTES, 'UTF-8') ?>", 4000, document.getElementById('redirect-alert'));
            </script>
        <?php endif; ?>
    </body>

</html>
