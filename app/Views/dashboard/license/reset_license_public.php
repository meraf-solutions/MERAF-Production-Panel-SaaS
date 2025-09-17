<?= $this->extend('layouts/single_page_public') ?>

<?php
if(!isset($myConfig)) {
    $myConfig = getMyConfig();
}

if(!isset($theme)) {
    // Initialize theme variable with user's preference
    $theme = $myConfig['defaultTheme'];
}

if($myConfig['defaultTheme'] === 'system') {
    // Set theme based on detected system preference
    $theme = isset($_COOKIE["color_scheme"]) ? $_COOKIE["color_scheme"] : "light"; // Default to light if cookie not set
}

// Only allow theme cookie override if not system preference
if(isset($_COOKIE["theme"])) {
    $theme = $_COOKIE['theme'];
}  
?>

<?= $this->section('content') ?>
    <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>
    <section class="bg-home bg-circle-gradiant d-flex align-items-center">
        <div class="bg-overlay bg-overlay-white"></div>
        <div class="container">
                <?php
                // Set the app's logo
                $logoMode = $theme === '' || (strpos($theme, 'dark') !== false) ? 'dark' : 'light';

                // Set the app's icon
                $appIcon = $myConfig['appIcon'];
                ?>

                <a href="<?= base_url() ?>"><img src="<?= $myConfig['appLogo_' . $logoMode] ?>" class="d-block mx-auto" alt="<?= $myConfig['appName'] ?>"></a>                    
                <?php include_once APPPATH . 'Views/dashboard/license/reset_license_form.php'; ?>
                <p class="mb-0 text-muted text-center mt-3"><?= lang('Pages.footer_copyright', ['year' => date("Y"),'appName' => $myConfig['appName']]) ?></p>
        </div> <!--end container-->
    </section>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('assets/libs/jquery-3.7.1.min.js') ?>"></script>
    <?php include_once APPPATH . 'Views/includes/general/custom-alert-js.php'; ?>
    <?php include_once APPPATH . 'Views/includes/general/reset-license-js.php'; ?>
<?= $this->endSection() //End section('scripts')?>