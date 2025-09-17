<?php
/**
 * SECURE INSTALLATION PAGE
 * Includes CSRF protection and secure form handling
 */

// Start secure session
session_start();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

include_once('settings.php');

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if already installed
$installFlagFile = dirname(dirname(__DIR__)) . '/writable/.installed';
if (file_exists($installFlagFile)) {
    die('<h1>Installation Complete</h1><p>The application has already been installed. Please remove the installer directory for security.</p>');
}

$phpExtensionsActivated = false;

// Check PHP extensions
$requiredExtensions = ['mysqli', 'mbstring', 'openssl', 'curl', 'intl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$phpExtensionsActivated = empty($missingExtensions);

// Check PHP version
$phpVersionOk = version_compare(PHP_VERSION, '8.1.0', '>=');

// Check mod_rewrite
$modRewriteOk = true;
if (function_exists('apache_get_modules')) {
    $modRewriteOk = in_array('mod_rewrite', apache_get_modules());
}

// Check directory permissions
$writablePaths = [
    dirname(dirname(__DIR__)) . '/app/Config',
    dirname(dirname(__DIR__)) . '/writable'
];

$permissionsOk = true;
foreach ($writablePaths as $path) {
    if (!is_writable($path)) {
        $permissionsOk = false;
        break;
    }
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
        .requirement-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .requirement-ok {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .requirement-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }
    </style>
</head>

<body>
    <?php include_once('includes/navbar.php'); ?>
    
    <section class="min-vh-100 bg-half-100 d-table w-100 full-page-background">
        <div class="bg-overlay bg-overlay-white"></div>
        <div class="container">
            <div class="row align-items-center">

                <div class="col-lg-4 col-md-6 order-1 order-md-2">
                    <div class="title-heading">
                        <h4 class="heading">System Requirements</h4>
                        <p class="text-dark">
                            Please make sure your system meets these <span class="text-primary fw-bold">requirements</span>:
                        </p>
                        
                        <!-- PHP Version Check -->
                        <div class="requirement-item <?= $phpVersionOk ? 'requirement-ok' : 'requirement-error' ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?= $phpVersionOk ? 'uil uil-check' : 'uil uil-times' ?> me-2"></i>
                                <div>
                                    <strong>PHP <?= $phpVersionOk ? '8.1+' : '8.1+ Required' ?></strong>
                                    <div class="small">Current: <?= PHP_VERSION ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- PHP Extensions Check -->
                        <div class="requirement-item <?= $phpExtensionsActivated ? 'requirement-ok' : 'requirement-error' ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?= $phpExtensionsActivated ? 'uil uil-check' : 'uil uil-times' ?> me-2"></i>
                                <div>
                                    <strong>PHP Extensions</strong>
                                    <?php if (!$phpExtensionsActivated): ?>
                                        <div class="small text-danger">
                                            Missing: <?= implode(', ', $missingExtensions) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="small">All required extensions loaded</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- mod_rewrite Check -->
                        <div class="requirement-item <?= $modRewriteOk ? 'requirement-ok' : 'requirement-error' ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?= $modRewriteOk ? 'uil uil-check' : 'uil uil-times' ?> me-2"></i>
                                <div>
                                    <strong>Apache mod_rewrite</strong>
                                    <div class="small">
                                        <?= $modRewriteOk ? 'Enabled' : 'Required for URL rewriting' ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Directory Permissions Check -->
                        <div class="requirement-item <?= $permissionsOk ? 'requirement-ok' : 'requirement-error' ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?= $permissionsOk ? 'uil uil-check' : 'uil uil-times' ?> me-2"></i>
                                <div>
                                    <strong>Directory Permissions</strong>
                                    <div class="small">
                                        <?= $permissionsOk ? 'Config and writable directories are writable' : 'Some directories need write permissions' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-6 order-2 order-md-1 mt-4 pt-2 mt-sm-0 opt-sm-0">
                    <div class="card login-page border-0" style="z-index: 1">
                        <div class="card-body">
                            <?php
                            $canInstall = $phpVersionOk && $phpExtensionsActivated && $modRewriteOk && $permissionsOk;
                            ?>
                            
                            <?php if (!$canInstall): ?>
                                <div class="alert alert-danger">
                                    <h5>System Requirements Not Met</h5>
                                    <p>Please fix the issues listed on the right before proceeding with installation.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <h5>System Ready for Installation</h5>
                                    <p>All requirements are met. You can proceed with the installation.</p>
                                </div>
                            <?php endif; ?>

                            <form class="login-form mt-4" action="action_secure.php" method="post" <?= !$canInstall ? 'style="display:none;"' : '' ?>>
                                <!-- CSRF Token -->
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                
                                <div id="simple-msg"></div>
                                
                                <!-- Database Configuration -->
                                <div class="form-section">
                                    <h5 class="text-primary mb-3">
                                        <i class="uil uil-database me-2"></i>Database Configuration
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Database Host <span class="text-danger">*</span></label>
                                                <input name="host" id="host" type="text" class="form-control" 
                                                       placeholder="localhost" required maxlength="255">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Database Name <span class="text-danger">*</span></label>
                                                <input name="database" id="database" type="text" class="form-control"
                                                       placeholder="database_name" required maxlength="64"
                                                       pattern="[a-zA-Z0-9_-]+">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Database Username <span class="text-danger">*</span></label>
                                                <input name="username" id="username" type="text" class="form-control"
                                                       placeholder="database_username" required maxlength="64"
                                                       pattern="[a-zA-Z0-9_-]+">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Database Password <span class="text-danger">*</span></label>
                                                <input name="password" id="password" type="password" class="form-control" 
                                                       placeholder="database_password" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Configuration -->
                                <div class="form-section">
                                    <h5 class="text-primary mb-3">
                                        <i class="uil uil-envelope me-2"></i>Email Configuration
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Protocol <span class="text-danger">*</span></label>
                                        <select name="protocol" id="protocol" class="form-select" required>
                                            <option value="">Select Protocol</option>
                                            <option value="smtp">SMTP</option>
                                            <option value="sendmail">Sendmail</option>
                                            <option value="mail">PHP Mail</option>
                                        </select>
                                    </div>

                                    <!-- Sendmail/Mail Path -->
                                    <div id="sendmail-config" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">Sendmail Path <span class="text-danger">*</span></label>
                                            <input name="sendmailPath" id="sendmailPath" type="text" class="form-control" 
                                                   placeholder="/usr/sbin/sendmail">
                                        </div>
                                    </div>

                                    <!-- SMTP Configuration -->
                                    <div id="smtp-config" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                                    <input name="smtpHostname" id="smtpHostname" type="text" class="form-control" 
                                                           placeholder="smtp.example.com" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                                    <input name="smtpPort" id="smtpPort" type="number" class="form-control" 
                                                           placeholder="587" min="1" max="65535" value="587">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Username</label>
                                                    <input name="smtpUsername" id="smtpUsername" type="text" class="form-control" 
                                                           placeholder="your-email@example.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SMTP Password</label>
                                                    <input name="smtpPassword" id="smtpPassword" type="password" class="form-control" 
                                                           placeholder="your-smtp-password">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">SMTP Encryption</label>
                                            <select name="smtpEncryption" id="smtpEncryption" class="form-select">
                                                <option value="">None</option>
                                                <option value="tls" selected>TLS</option>
                                                <option value="ssl">SSL</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-grid">
                                        <input type="submit" id="submit" name="send" class="btn btn-primary" 
                                               value="Install Application" <?= !$canInstall ? 'disabled' : '' ?>>
                                    </div>
                                </div>
                            </form>
                            
                            <?php if (!$canInstall): ?>
                                <div class="text-center mt-3">
                                    <button class="btn btn-secondary" disabled>
                                        Fix Requirements First
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- javascript -->
    <?php include_once('includes/scripts.php'); ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const protocolSelect = document.getElementById('protocol');
            const smtpConfig = document.getElementById('smtp-config');
            const sendmailConfig = document.getElementById('sendmail-config');
            
            // Protocol change handler
            protocolSelect.addEventListener('change', function() {
                const protocol = this.value;
                
                // Hide all configs
                smtpConfig.style.display = 'none';
                sendmailConfig.style.display = 'none';
                
                // Show relevant config
                if (protocol === 'smtp') {
                    smtpConfig.style.display = 'block';
                    // Make SMTP fields required
                    document.getElementById('smtpHostname').required = true;
                    document.getElementById('smtpUsername').required = true;
                    document.getElementById('smtpPassword').required = true;
                    document.getElementById('sendmailPath').required = false;
                } else if (protocol === 'sendmail' || protocol === 'mail') {
                    sendmailConfig.style.display = 'block';
                    document.getElementById('sendmailPath').required = true;
                    // Make SMTP fields optional
                    document.getElementById('smtpHostname').required = false;
                    document.getElementById('smtpUsername').required = false;
                    document.getElementById('smtpPassword').required = false;
                }
            });
            
            // Form submission with AJAX
            const form = document.querySelector('.login-form');
            const submitButton = document.getElementById('submit');
            const messageDiv = document.getElementById('simple-msg');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable submit button
                submitButton.disabled = true;
                submitButton.value = 'Installing...';
                
                // Show loading message
                messageDiv.innerHTML = '<div class="alert alert-info"><i class="uil uil-sync fa-spin me-2"></i>Installing application, please wait...</div>';
                
                // Submit form data
                const formData = new FormData(form);
                
                fetch('action_secure.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="alert alert-success"><i class="uil uil-check me-2"></i>' + data.msg + '</div>';
                        form.style.display = 'none';
                        
                        // Redirect after 5 seconds
                        setTimeout(() => {
                            window.location.href = '/register';
                        }, 5000);
                    } else {
                        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="uil uil-exclamation-triangle me-2"></i>' + data.msg + '</div>';
                        submitButton.disabled = false;
                        submitButton.value = 'Install Application';
                    }
                })
                .catch(error => {
                    messageDiv.innerHTML = '<div class="alert alert-danger"><i class="uil uil-exclamation-triangle me-2"></i>An error occurred during installation. Please try again.</div>';
                    submitButton.disabled = false;
                    submitButton.value = 'Install Application';
                });
            });
        });
    </script>
</body>
</html>