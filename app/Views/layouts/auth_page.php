<?php
/***********
 * Auth Page
 **********/

// Set the locale dynamically based on user preference
setMyLocale();
?>

<!doctype html>
<html lang="en" <?php $dir = service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?> dir="<?= $dir ?>">

    <head>
        <?= $this->renderSection('title') ?></title>
        
        <?= $this->renderSection('head') ?>
    </head>

    <body>
        <?php include_once APPPATH . 'Views/includes/general/loading_effect.php'; ?>
        <!-- Hero Start -->
        <?= $this->renderSection('content') ?>
        <!-- Hero End -->

        <?php include_once APPPATH . 'Views/includes/auth/js-assets.php';?>
        <?= $this->renderSection('modals') ?>
        <?= $this->renderSection('scripts') ?>
    </body>
</html>