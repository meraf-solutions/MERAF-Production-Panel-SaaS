<?php
/***********************************************************
 * Single Page Design w/o Sidebars and top header for PUBLIC
 **********************************************************/

// Set the locale dynamically based on user preference
setMyLocale();
?>

<!doctype html>
<html lang="en" <?php $dir = service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?> dir="<?= $dir ?>">

    <head>
        <title><?= $pageTitle ?></title>
        <?php include_once APPPATH . 'Views/includes/auth/head.php'; ?>
        <?= $this->renderSection('head') ?>
    </head>

    <body>
        <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>
        <!-- Hero Start -->
        <?= $this->renderSection('content') ?>
        <!-- Hero End -->
        
        <!-- javascript -->
        <!-- JAVASCRIPT -->
        <?php include_once APPPATH . 'Views/includes/auth/js-assets.php'; ?>
        <?= $this->renderSection('modals') ?>
        <?= $this->renderSection('scripts') ?>
    </body>

</html>