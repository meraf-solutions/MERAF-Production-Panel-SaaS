<?= $this->extend('layouts/auth_page') ?>

<?php
// Detect and reset partial/leftover session
$field = setting('Auth.sessionConfig')['field'];
$userId = session($field);
$userId = (is_array($userId) && array_key_exists('id', $userId)) ? $userId['id'] : null;

// Check if user is still in session
if (!empty($userId)) {
    log_message('debug', "Accessed /login while user_id ($field) = {$userId}. Destroying session.");
    session()->remove(setting('Auth.sessionConfig')['field']);

    return redirect()->to(current_url());
}

if(!isset($myConfig)) {
    $myConfig = getMyConfig();
}

// reCAPTCHA enabled check
$reCAPTCHA_enabled = false;
if($myConfig['reCAPTCHA_enabled'] && $myConfig['reCAPTCHA_Site_Key'] && $myConfig['reCAPTCHA_Secret_Key']) {
    $reCAPTCHA_enabled = true;
}
?>

<?= $this->section('title') ?>
    <title><?= $myConfig['appName'] ?> | <?= lang('Auth.register') ?></title>
<?= $this->endSection() //End section('title')?>

<?= $this->section('head') ?>
    <?php include_once APPPATH . 'Views/includes/auth/head.php'; ?>
<?= $this->endSection() //End section('head')?>

<?= $this->section('content') ?>  
    <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>
    <section class="bg-home d-flex position-relative" style="background: url('assets/images/shape01.png') center;">
        <div class="container">

            <div class="row">
                <div class="col-12 pt-3 pb-3">
                    <div class="card form-signin p-4 rounded shadow">
                        <?php $formURL = $reCAPTCHA_enabled ? 'custom-register' : 'register'; ?>
                        <form action="<?= url_to($formURL) ?>" method="post">
                            <?= csrf_field() ?>
                            <a href="<?= base_url() ?>"><img src="<?= $appLogo ?>" class="mb-4 d-block mx-auto" alt="<?= $myConfig['appName'] ?>"></a>
                            <h5 class="mb-3 text-center"><?= lang('Auth.register') ?></h5>
                            
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
                        
                            <!-- Email -->
                            <div class="form-floating mb-2">
                                <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>" required>
                                <label for="floatingEmailInput"><?= lang('Auth.email') ?></label>
                            </div>

                            <!-- First Name -->
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" id="floatingFirstNameInput" name="first_name" inputmode="text" autocomplete="given-name" placeholder="First Name" value="<?= old('first_name') ?>" required>
                                <label for="floatingFirstNameInput"><?= lang('Pages.First_Name') ?></label>
                            </div>

                            <!-- Last Name -->
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" id="floatingLastNameInput" name="last_name" inputmode="text" autocomplete="family-name" placeholder="Last Name" value="<?= old('last_name') ?>" required>
                                <label for="floatingLastNameInput"><?= lang('Pages.Last_Name') ?></label>
                            </div>

                            <!-- Username -->
                            <div class="form-floating mb-4">
                                <input type="text" class="form-control" id="floatingUsernameInput" name="username" inputmode="text" autocomplete="username" placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>" required>
                                <label for="floatingUsernameInput"><?= lang('Auth.username') ?></label>
                            </div>

                            <!-- Password -->
                            <div class="form-floating mb-2">
                                <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.password') ?>" required>
                                <label for="floatingPasswordInput"><?= lang('Auth.password') ?></label>
                            </div>

                            <!-- Password (Again) -->
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.passwordConfirm') ?>" required>
                                <label for="floatingPasswordConfirmInput"><?= lang('Auth.passwordConfirm') ?></label>
                            </div>

                            <!-- Google reCAPTCHA -->
                            <?php if($reCAPTCHA_enabled) { ?>
                                <div class="d-flex justify-content-center">
                                    <div class="mb-3">
                                        <div class="g-recaptcha" data-sitekey="<?= $myConfig['reCAPTCHA_Site_Key'] ?>" <?= strpos($theme, 'dark') !== false ? 'data-theme="dark"' : ''?>></div>
                                    </div>
                                </div>
                            <?php } ?>
            
                            <button class="btn btn-primary w-100" type="submit"><?= lang('Auth.register') ?></button>  

                            <div class="col-12 text-center mt-3">
                                <p class="mb-0 mt-3"><small class="text-dark me-2"><?= lang('Auth.haveAccount') ?></small> <a href="<?= url_to('login') ?>" class="text-dark fw-bold"><?= lang('Auth.login') ?></a></p>
                            </div><!--end col-->

                            <?php include_once APPPATH . 'Views/includes/auth/footer-copyright.php';?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <?php if($reCAPTCHA_enabled) { ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php } ?>
<?= $this->endSection() //End section('scripts')?>
