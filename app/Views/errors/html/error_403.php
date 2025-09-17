<?php
if(!isset($myConfig)) {
    $myConfig = getMyConfig();
}

// Set the locale dynamically based on user preference
setMyLocale();
?>
<!doctype html>
<html lang="en" <?php $dir = service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?> dir="<?= $dir ?>">

    <head>
		<meta name="robots" content="noindex">
        <title><?= lang('Pages.pageForbidden') ?></title>
        <?php include_once APPPATH.'Views/includes/general/head.php'; ?>
    </head>

    <body>

        <!-- ERROR PAGE -->
        <section class="bg-home d-flex align-items-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-12 text-center">
                        <img src="<?= base_url('assets/images/403.svg') ?>" style="max-width: 500px;" alt="Eror 403">
                        <div class="text-uppercase mt-4 display-5 fw-semibold"><?= lang('Pages.pageForbidden') ?></div>
                        <div class="text-capitalize text-dark mb-4 error-page"></div>
                        <p class="text-muted para-desc mx-auto">
						    <?= session('error') ?? lang('Pages.forbidden_error_msg') ?>
						</p>
                    </div><!--end col-->
                </div><!--end row-->

                <div class="row">
                    <div class="col-md-12 text-center">  
                        <a href="<?= base_url() ?>" class="btn btn-primary mt-4"><i class="uil uil-home"></i> <?= lang('Pages.go_to_home') ?></a>
                    </div><!--end col-->
                </div><!--end row-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- ERROR PAGE -->
        
        <!-- JAVASCRIPT -->
        <?php include_once APPPATH.'Views/includes/general/js-assets.php'; ?>
        
    </body>

</html>