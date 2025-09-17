<?= $this->extend('layouts/auth_page') ?>

<?php
if(!isset($myConfig)) {
    $myConfig = getMyConfig();
}
?>

<?= $this->section('title') ?>
    <title><?= $myConfig['appName'] ?> | <?= lang('Auth.useMagicLink') ?></title>
<?= $this->endSection() //End section('title')?>

<?= $this->section('head') ?>
    <?php include_once APPPATH . 'Views/includes/auth/head.php'; ?>
<?= $this->endSection() //End section('head')?>

<?= $this->section('content') ?>
    <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>
    <section class="bg-home d-flex align-items-center position-relative" style="background: url('<?= base_url('assets/images/shape01.png') ?>') center;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card form-signin p-4 rounded shadow">
                        <a href="<?= base_url() ?>"><img src="<?= $appLogo ?>" class="mb-4 d-block mx-auto" alt="<?= $myConfig['appName'] ?>"></a>

                        <div class="alert alert-success mb-4 mx-auto" role="alert">
                            <?= lang('Auth.checkYourEmail') ?>
                        </div>
                        
                        <div class="alert alert-info mb-4 mx-auto" role="alert">
                            <?= lang('Auth.magicLinkDetails', [setting('Auth.magicLinkLifetime') / 60]) ?>
                        </div>							
                        
                    </div>
                </div>
            </div>
        </div>
    </section>  
<?= $this->endSection() //End section('content')?>