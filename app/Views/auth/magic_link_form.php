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
                        <form action="<?= url_to('magic-link') ?>" method="post">
                            <?= csrf_field() ?>
                            <a href="<?= base_url() ?>"><img src="<?= $appLogo ?>" class="mb-4 d-block mx-auto" alt="<?= $myConfig['appName'] ?>"></a>
                            <h5 class="mb-3 text-center"><?= lang('Auth.useMagicLink') ?></h5>
                            
                            <?php if (session('error') !== null) : ?>
                                <div class="alert alert-danger mb-4 mx-auto" role="alert"><?= session('error') ?></div>
                            <?php elseif (session('errors') !== null) : ?>		
                                <div class="alert alert-danger mb-4 mx-auto" role="alert">
                                    <?php if (is_array(session('errors'))) : ?>
                                        <?php foreach (session('errors') as $error) : ?>
                                            <?= $error ?>
                                            <br>
                                        <?php endforeach ?>
                                    <?php else : ?>
                                        <?= session('errors') ?>
                                    <?php endif ?>									
                                </div>
                            <?php endif ?>								
                        
                            <div class="form-floating mb-2">
                                <input type="email" class="form-control" id="floatingEmailInput" name="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>"
                        value="<?= old('email', auth()->user()->email ?? null) ?>" required>
                                <label for="floatingEmailInput"><?= lang('Auth.email') ?></label>
                            </div>
            
                            <button class="btn btn-primary w-100" type="submit"><?= lang('Auth.send') ?></button>

                            <div class="col-12 text-center mt-3">
                                <p class="mb-0 mt-3"><a href="<?= url_to('login') ?>" class="text-dark fw-bold"><?= lang('Auth.backToLogin') ?></a></p>
                            </div><!--end col-->  

                            <?php include_once APPPATH . 'Views/includes/auth/footer-copyright.php';?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section> 
<?= $this->endSection() //End section('content')?>