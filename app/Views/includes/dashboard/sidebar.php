            <?php $sidebarMode = $theme === '' || (strpos($theme, 'dark') !== false) ? 'dark' : 'light'; ?>
            
            <nav id="sidebar" class="sidebar-wrapper sidebar-<?= $sidebarMode ?>">
                <div class="sidebar-content" data-simplebar style="height: calc(100%<?= $userData->inGroup('admin') ? ' - 60px' : '' ?>);">
                    <div class="sidebar-brand">
                        <a href="<?= base_url()?>">
                            <img src="<?= $myConfig['appLogo_light'] ?>" height="56" class="logo-light-mode appLogo_lightPreview" alt="<?= $myConfig['appName'] ?>">
                            <img src="<?= $myConfig['appLogo_dark'] ?>" height="56" class="logo-dark-mode appLogo_darkPreview" alt="<?= $myConfig['appName'] ?>">
                            <span class="sidebar-colored">
                                <img src="<?= $myConfig['appLogo_dark'] ?>" height="56" class="appLogo_darkPreview" alt="<?= $myConfig['appName'] ?>">
                            </span>
                        </a>
                    </div>
        
                    <ul class="sidebar-menu">
                        <li <?php echo $section === 'home' ? 'class=" active"' : ''; ?> >
                            <a href="<?= base_url()?>"><i class="ti ti-home me-2"></i><?= lang('Pages.Home') ?></a>
                        </li>

                        <li id="sidebarProductManagerSection" class="sidebar-dropdown">
                            <a href="javascript:void(0)"><i class="ti ti-folders me-2"></i><?= lang('Pages.Product_manager') ?></a>
                            <div class="sidebar-submenu">
                                <ul>
                                    <li><a href="<?= base_url('product-manager/create-product')?>"><?= lang('Pages.Create_product') ?></a></li>
									<li><a href="<?= base_url('product-manager/modify-product')?>"><?= lang('Pages.Modify_product') ?></a></li>                                    
                                    <li><a href="<?= base_url('product-manager/version-files')?>"><?= lang('Pages.Version_files') ?></a></li>
                                    <li><a href="<?= base_url('product-manager/assign-product-variation')?>"><?= lang('Pages.Assign_product_variation') ?></a></li>
                                    <li><a href="<?= base_url('product-manager/product-variations')?>"><?= lang('Pages.Product_variations') ?></a></li>
                                    <li><a href="<?= base_url('product-manager/gettings-started-guide')?>"><?= lang('Pages.Getting_Started_Guide') ?></a></li>
                                </ul>
                            </div>
                        </li>                       

                        <li class="sidebar-dropdown">
                            <a href="javascript:void(0)"><i class="ti ti-separator-horizontal me-2"></i><?= lang('Pages.Product_Changelog') ?></a>
                            <div class="sidebar-submenu">
                                <ul id="changelog-product-list-sidebar">
                                    <?php foreach($sideBarMenu['products'] as $productName) { ?>
                                        <li><a href="<?= base_url('product-changelog/'.$productName)?>"><?= $productName ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </li>

                        <li class="sidebar-dropdown">
                            <a href="javascript:void(0)"><i class="ti ti-ticket me-2"></i><?= lang('Pages.License_Manager') ?></a>
                            <div class="sidebar-submenu">
                                <ul>
                                    <?php if($myConfig['licenseManagerOnUse'] !== 'slm') {
                                        echo '<li id="ManageLicense-sidebarMenu"><a href="' . base_url('license-manager/list-all') . '">' . lang('Pages.Manage_licenses') . '</a></li>';
                                    }
                                    ?>
                                    <li><a href="<?= base_url('license-manager/create-new-license') ?>"><?= lang('Pages.Create_license') ?></a></li>
                                    <li><a href="<?= base_url('license-manager/resend-license') ?>"><?= lang('Pages.resend_license_details') ?></a></li>
                                    <li><a href="<?= base_url('license-manager/reset-license') ?>"><?= lang('Pages.Reset_license') ?></a></li>
                                    <?php if($myConfig['licenseManagerOnUse'] !== 'slm') {
                                        echo '<li><a href="'. base_url('license-manager/activity-logs') .'">' . lang('Pages.Activity_Logs') .'</a></li>';
                                        echo '<li><a href="'. base_url('license-manager/subscribers') .'">'. lang('Pages.Subscribers') .'</a></li>';
                                    }
                                    ?>

                                </ul>
                            </div>
                        </li>
                        
                        <li class="sidebar-dropdown">
                            <a href="javascript:void(0)"><i class="ti ti-mail me-2"></i><?= lang('Pages.Email_Service') ?></a>
                            <div class="sidebar-submenu">
                                <ul>
                                    <li><a href="<?= base_url('email-service/template') ?>"><?= lang('Pages.Template') ?></a></li>

                                    <?php if($myConfig['licenseManagerOnUse'] === 'built-in') : ?>
                                        <li><a href="<?= base_url('email-service/notifications') ?>"><?= lang('Pages.Notifications') ?></a></li>
                                    <?php endif; ?>

                                    <li><a href="<?= base_url('email-service/settings') ?>"><?= lang('Pages.Settings') ?></a></li>
                                    
                                    <li><a href="<?= base_url('email-service/logs') ?>"><?= lang('Pages.Logs') ?></a></li>
                                </ul>
                            </div>
                        </li>                        

                        <li>
                            <a href="<?= base_url('error-logs')?>"><i class="ti ti-user-exclamation me-2"></i><?= lang('Pages.error_logs') ?></a>
                        </li>

                        <li>
                            <a href="<?= base_url('success-logs')?>"><i class="ti ti-user-check me-2"></i><?= lang('Pages.success_logs') ?></a>
                        </li>

                        <li class="sidebar-dropdown">
                            <a href="javascript:void(0)"><i class="ti ti-receipt me-2"></i><?= lang('Pages.Subscription') ?></a>
                            <div class="sidebar-submenu">
                                <ul>
                                    <li><a href="<?= base_url('subscription/my-subscription') ?>"><?= lang('Pages.My_Subscription') ?></a></li>
                                    <li><a href="<?= base_url('subscription/packages') ?>"><?= lang('Pages.Packages') ?></a></li>
                                </ul>
                            </div>
                        </li>                        

                        <?php if($userData->id === 1) { $tasksLogEnabled = getMyConfig('Config\Tasks', 0); $tasksLogEnabled = $tasksLogEnabled['enabled'];?>
                            <li class="sidebar-dropdown border-top">
                                <a href="javascript:void(0)"><i class="ti ti-adjustments-alt me-2"></i><?= lang('Pages.Setup') ?></a>
                                <div class="sidebar-submenu">
                                    <ul>
                                        <li><a href="<?= base_url('admin-options/user-manager') ?>"><?= lang('Pages.user_manager') ?></a></li>
                                        <li><a href="<?= base_url('admin-options/global-settings') ?>"><?= lang('Pages.global_settings') ?></a></li>
                                        <li><a href="<?= base_url('admin-options/email-settings') ?>"><?= lang('Pages.email_settings') ?></a></li>
                                        <?php if($tasksLogEnabled) { ?>
                                            <li><a href="<?= base_url('admin-options/cronjob-logs') ?>"><?= lang('Pages.cronjob_logs') ?></a></li>
                                        <?php } ?>
                                        <li><a href="<?= base_url('admin-options/blocked-ip-logs') ?>"><?= lang('Pages.Blocked_ip_logs') ?></a></li>
                                    </ul>
                                </div>
                            </li>

                            <li class="sidebar-dropdown">
                                <a href="javascript:void(0)"><i class="ti ti-receipt me-2"></i><?= lang('Pages.Subscription_Manager') ?></a>
                                <div class="sidebar-submenu">
                                    <ul>
                                        <li><a href="<?= base_url('subscription-manager/list') ?>"><?= lang('Pages.Subscription_List') ?></a></li>
                                        <li><a href="<?= base_url('subscription-manager/reports') ?>"><?= lang('Pages.Reports') ?></a></li>
                                    </ul>
                                </div>
                            </li>

                            <li class="sidebar-dropdown">
                                <a href="javascript:void(0)"><i class="ti ti-box me-2"></i><?= lang('Pages.package_manager') ?></a>
                                <div class="sidebar-submenu">
                                    <ul>
                                        <li><a href="<?= base_url('admin-options/package-manager/list-packages') ?>"><?= lang('Pages.List_packages') ?></a></li>
                                        <li><a href="<?= base_url('admin-options/package-manager/new') ?>"><?= lang('Pages.New_package') ?></a></li>
                                        <li><a href="<?= base_url('admin-options/package-manager/edit/select-package') ?>"><?= lang('Pages.Edit_package') ?></a></li>
                                        <li><a href="<?= base_url('admin-options/package-manager/modules') ?>"><?= lang('Pages.Modules') ?></a></li>
                                    </ul>
                                </div>
                            </li>
                            
                            <li>
                                <a href="<?= base_url('admin-options/email-logs')?>"><i class="ti ti-list-details me-2"></i><?= lang('Pages.Email_Logs') ?></a>
                            </li>

                            <li>
                                <a href="<?= base_url('admin-options/language-editor')?>"><i class="ti ti-language me-2"></i><?= lang('Pages.Language_Editor') ?></a>
                            </li>

                            <li class="sidebar-dropdown">
                                <a href="javascript:void(0)"><i class="ti ti-cash me-2"></i><?= lang('Pages.Payment_Options') ?></a>
                                <div class="sidebar-submenu">
                                    <ul>
                                        <?php
                                        $paymentMethodOptions = [];
                                        if(array_key_exists("payment_methods", $sideBarMenu) && count($sideBarMenu['payment_methods']) !== 0) : ?>
                                            <?php foreach($sideBarMenu['payment_methods'] as $paymentOption) : ?>
                                                <?php
                                                $altText = htmlspecialchars($paymentOption['title']);
                                                $srcImg = $paymentOption['logo'];

                                                // Validate file path
                                                if (!$srcImg) {
                                                    log_message('warning', "[Dashboard Sidebar] No logo path provided for payment method: {$altText}");
                                                    echo '<a href="javascript:void(0)" class="btn btn-outline-primary">' . $altText . '</a>';
                                                    continue;
                                                }

                                                // Check if file exists with absolute path
                                                if (!file_exists($srcImg)) {
                                                    log_message('error', "[Dashboard Sidebar] Logo file not found: {$srcImg}");
                                                    echo '<li><a href="'. base_url($paymentOption['url']) .'">'. $paymentOption['title'] .'</a></li>';
                                                    continue;
                                                }

                                                // Validate file type
                                                $fileInfo = pathinfo($srcImg);
                                                $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
                                                $fileExtension = strtolower($fileInfo['extension']);

                                                if (!in_array($fileExtension, $allowedExtensions)) {
                                                    log_message('warning', "[Dashboard Sidebar] Unsupported file type for logo: {$srcImg}");
                                                    echo '<li><a href="'. base_url($paymentOption['url']) .'">'. $paymentOption['title'] .'</a></li>';
                                                    continue;
                                                }

                                                // Read file contents
                                                $imgContent = file_get_contents($srcImg);
                                                if ($imgContent === false) {
                                                    log_message('error', "[Dashboard Sidebar] Unable to read logo file: {$srcImg}");
                                                    echo '<li><a href="'. base_url($paymentOption['url']) .'">'. $paymentOption['title'] .'</a></li>';
                                                    continue;
                                                }

                                                // Determine MIME type
                                                $mimeTypes = [
                                                    'svg' => 'image/svg+xml',
                                                    'png' => 'image/png',
                                                    'jpg' => 'image/jpeg',
                                                    'jpeg' => 'image/jpeg',
                                                    'gif' => 'image/gif',
                                                    'webp' => 'image/webp'
                                                ];

                                                // Encode image
                                                $imgEncoded = base64_encode($imgContent);
                                                $mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';
                                                $base64Img = "data:{$mimeType};base64," . $imgEncoded;
                                                ?>
                                                <li>
                                                    <a href="<?= base_url($paymentOption['url']) ?>">
                                                        <img src="<?= $base64Img ?>" alt="<?= $altText ?>" style="max-width: 100px;" class="avatar avatar-ex-medium mb-2">
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </li>                               
                        <?php } ?>
                    </ul>
                    <!-- sidebar-menu  -->
                </div>
                <?php if($userData->id === 1) { ?>
                    <!-- Sidebar Footer -->
                    <?php
                    // Initiate version check
                    $newVersion = '';
                    $versionFilePath = USER_DATA_PATH . 'version.json';

                    if (file_exists($versionFilePath)) {
                        $versionData = json_decode(file_get_contents($versionFilePath), true);      
                        $newVersion = $versionData['newVersion'];
                    }

                    if($newVersion) {
                        $notification = '<a class="text-bg-danger" href="' . base_url('update-production-panel') . '">&nbsp; '.lang('Pages.Upgrade').' &nbsp;</a>';
                    }
                    else {
                        $notification = '<a class="text-bg-secondary" href="' . base_url('reinstall-production-panel') . '">&nbsp; '.lang('Pages.Latest').' &nbsp;</a>';
                    }

                    $appVersionButton = '<span class="text-bg-primary">&nbsp;v'. $myConfig['appVersion'] .'&nbsp;</span>';

                    $changelogButton = '<a class="text-bg-info" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#ModalChangelog">&nbsp; '.lang('Pages.Changelog').' &nbsp;</a>';
                    ?>
                    <ul class="sidebar-footer list-unstyled mb-0" <?= !file_exists(APPPATH . 'Views/includes/dashboard/developer.php') ? 'style="height:unset !important;"' : '' ?>>
                        <li class="list-inline-item mb-0">
                            <small class="text-muted fw-medium ms-1">
                                <?= $appVersionButton ?><?= $notification ?><?=$changelogButton ?>
                            </small>
                                
                            <?php if(file_exists(APPPATH . 'Views/includes/dashboard/developer.php')) : include_once APPPATH . 'Views/includes/dashboard/developer.php'; endif; ?>

                        </li>
                    </ul>
                    <!-- Sidebar Footer -->
                <?php } ?>
            </nav>