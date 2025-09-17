<?php
/**
 * SECURE INSTALLER INDEX PAGE
 * Includes security headers and installation status check
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

include_once('settings.php');

// Check if already installed
$installFlagFile = dirname(dirname(__DIR__)) . '/writable/.installed';
if (file_exists($installFlagFile)) {
    $installDate = file_get_contents($installFlagFile);
    die('<div style="text-align: center; margin-top: 50px; font-family: Arial, sans-serif;">
        <h1 style="color: #28a745;">✓ Installation Complete</h1>
        <p>The application was successfully installed.</p>
        <p><small>Installed: ' . htmlspecialchars($installDate, ENT_QUOTES, 'UTF-8') . '</small></p>
        <p><strong style="color: #dc3545;">Security Notice:</strong> Please remove the installer directory for security reasons.</p>
        <a href="/" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Application</a>
    </div>');
}
?>

<!doctype html>
<html lang="en" dir="ltr">

    <head>
        <title><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?> | Installation</title>
        <meta charset="utf-8" />        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="<?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?>" />
        <meta name="keywords" content="MERAF Codeigniter 4 app installer" />
        <meta name="author" content="<?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?>" />
        <meta name="email" content="<?= htmlspecialchars($companyContact, ENT_QUOTES, 'UTF-8') ?>" />
        <meta name="website" content="<?= htmlspecialchars($companyURL, ENT_QUOTES, 'UTF-8') ?>" />
        <meta name="Version" content="v1.0" />
        <meta name="robots" content="noindex, nofollow">

        <!-- favicon -->
        <link rel="shortcut icon" href="assets/images/meraf-appIcon.png" />
        
        <!-- Css -->
        <?php include_once('includes/style.php'); ?>
        
        <style>
            .security-notice {
                background-color: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .security-notice h5 {
                color: #856404;
                margin-bottom: 10px;
            }
            .secure-badge {
                display: inline-block;
                background-color: #28a745;
                color: white;
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 12px;
                margin-left: 10px;
            }
        </style>
    </head>

    <body>
        <?php include_once('includes/navbar.php'); ?>   
        
        <section class="min-vh-100 bg-half-100 d-table w-100 full-page-background">
            <div class="bg-overlay bg-overlay-white"></div>
            <div class="container mb-0">

                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="title-heading text-center mb-4 pb-2">
                            <h4 class="heading"><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?> Installation
                                <span class="secure-badge">
                                    <i class="uil uil-shield-check"></i> SECURE
                                </span>
                            </h4>
                            <h4 class="mb-0">Thank you for purchasing our app!</h4>
                        </div>
                    </div><!--end col-->
                </div><!--end row-->

                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 col-12 mt-4 pt-2">
                        <div class="card features feature-primary explore-feature border-0 rounded text-center">
                            <div class="card-body">
                                <div class="icons rounded-circle shadow-lg d-inline-block mb-2 h3">
                                    <i class="uil uil-shield-check"></i>
                                </div>
                                <div class="content mt-4">
                                    <a href="install_secure.php" class="title h5 text-dark">
                                        Secure Installation
                                    </a>
                                    <p class="text-muted mt-3 mb-0">Install the app</p>
                                </div>
                            </div>
                        </div>
                    </div><!--end col-->
                    
                    <div class="col-lg-4 col-md-6 col-12 mt-4 pt-2">
                        <div class="card features feature-primary explore-feature border-0 rounded text-center">
                            <div class="card-body">
                                <div class="icons rounded-circle shadow-lg d-inline-block mb-2 h3">
                                    <i class="uil uil-document-info"></i>
                                </div>
                                <div class="content mt-4">
                                    <a href="<?= htmlspecialchars($documentationURL, ENT_QUOTES, 'UTF-8') ?>" class="title h5 text-dark">Documentation</a>
                                    <p class="text-muted mt-3 mb-0">Read App Documentation</p>
                                </div>
                            </div>
                        </div>
                    </div><!--end col-->
                    
                    <div class="col-lg-4 col-md-6 col-12 mt-4 pt-2">
                        <div class="card features feature-primary explore-feature border-0 rounded text-center">
                            <div class="card-body">
                                <div class="icons rounded-circle shadow-lg d-inline-block mb-2 h3">
                                    <i class="uil uil-envelope"></i>
                                </div>
                                <div class="content mt-4">
                                    <a href="mailto:<?= htmlspecialchars($companyContact, ENT_QUOTES, 'UTF-8') ?>" class="title h5 text-dark">Support Request</a>
                                    <p class="text-muted mt-3 mb-0">Contact <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    </div><!--end col-->

                </div><!--end row-->
                
            </div><!--end container--> 
        </section><!--end section-->
        <!-- Hero End -->
        
        <!-- javascript -->
        <?php include_once('includes/scripts.php'); ?>

    </body>
</html>