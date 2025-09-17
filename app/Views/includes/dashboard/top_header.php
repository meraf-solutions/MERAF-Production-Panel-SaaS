                <div class="top-header">
                    <div class="header-bar d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <a href="<?= base_url() ?>" class="logo-icon me-3">
                                <img src="<?= $myConfig['appIcon'] ?>" height="30" class="small appIconPreview" alt="<?= $myConfig['appName'] ?>">
                                <span class="big">
                                    <img src="<?= $myConfig['appLogo_light'] ?>" height="56" class="logo-light-mode appLogo_lightPreview" alt="<?= $myConfig['appName'] ?>">
                                </span>
                                
                                <span class="big">
                                    <img src="<?= $myConfig['appLogo_dark'] ?>" height="56" class="logo-dark-mode appLogo_darkPreview" alt="<?= $myConfig['appName'] ?>">
                                </span>
                            </a>
                            <a id="close-sidebar" class="btn btn-icon btn-soft-light" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.ToggleSideBar') ?>">
                                <i class="ti ti-menu-2"></i>
                            </a>
                        </div>              

                        <ul class="list-unstyled mb-0">
                            <li class="list-inline-item mb-0 ms-1 light-version-wrapper" <?= strpos($theme, 'light') !== false || $theme === 'style'  ? 'style="display: none"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.ChangeToLightMode') ?>">
                                <a href="javascript:void(0)" class="light-version t-light mt-4" onclick="setTheme('style');updateToastTheme();">
                                    <div class="btn btn-icon btn-soft-light"><i class="ti ti-sun"></i></div>
                                </a>
                            </li>
                            <li class="list-inline-item mb-0 ms-1 dark-version-wrapper" <?= strpos($theme, 'dark') !== false ? 'style="display: none"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.ChangeToDarkMode') ?>">
                                <a href="javascript:void(0)" class="dark-version t-light mt-4" onclick="setTheme('style-dark');updateToastTheme();">
                                    <div class="btn btn-icon btn-soft-light"><i class="ti ti-moon"></i></div>
                                </a>
                            </li> 
                            
                            <li class="list-inline-item mb-0 ms-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.ChangeLanguage') ?>">
                                <div class="dropdown dropdown-primary">
                                    <a href="javascript:void(0)" class="light-version t-light mt-4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <div class="btn btn-icon btn-soft-light"><i class="ti ti-language"></i></div>
                                    </a>
                                    <div class="dropdown-menu dd-menu dropdown-menu-end shadow border-0 mt-3 py-3" style="min-width: 90px;">
                                        <?php
                                        $currentURL = service('uri');
                                        $languageDirectories = get_product_directories(APPPATH . 'Language');
                                        $language_reference = json_decode(file_get_contents(ROOTPATH . 'public/assets/libs/language-codes.json'), true);
                                        foreach($languageDirectories as $languageDirectory) { ?>
                                            <a class="dropdown-item text-dark" href="<?= base_url('setlocale/'. $languageDirectory) . '?redirect=' . $currentURL ?>"><span class="mb-0 d-inline-block me-1"><?= $language_reference[$languageDirectory]['EnglishName'] ?></span></a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </li>

                            <li class="list-inline-item mb-0 ms-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.AppSettings') ?>">
                                <a href="<?= base_url('app-settings') ?>">
                                    <div class="btn btn-icon btn-soft-light"><i class="ti ti-settings"></i></div>
                                </a>
                            </li>

                            <!-- Notifications -->
                            <li class="list-inline-item mb-0 ms-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Notification_Center') ?>">
                                <div class="dropdown dropdown-primary">
                                    <button type="button" class="btn btn-icon btn-soft-light dropdown-toggle p-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i id="notification-icon" class="ti ti-bell"></i></button>
                                    <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none">
                                        <span class="visually-hidden"><?= lang('Pages.New_alerts') ?></span>
                                    </span>
                    
                                    <div id="notification-dropdown" class="dropdown-menu dd-menu shadow rounded border-0 mt-3 p-0" data-simplebar style="height: 420px; width: 400px;">
                                        <div class="d-flex bg-light align-items-center justify-content-between p-3 border-bottom sticky-header">
                                            <h6 class="mb-0 text-dark"><?= lang('Pages.Notifications') ?></h6>
                                            <span id="unread-count" class="badge bg-danger rounded-pill">0</span>
                                        </div>
                                        <div id="notification-list" class="p-0 mb-3">
                                            <!-- Notifications will be dynamically inserted here -->
                                        </div>
                                        <div class="d-flex bg-light justify-content-between align-items-center p-3 border-top small w-100 sticky-footer">
                                            <?php if($myConfig['push_notification_feature_enabled']) : ?>
                                                <div class="text-start">
                                                    <button id="enable-notifications" class="btn btn-primary btn-sm" style="<?= $myConfig['hasEnabledNotifications'] ? 'display: none;' : ''?>">
                                                        <i class="ti ti-bell"></i> <?= lang('Pages.Enable_Push_Notifications') ?>
                                                    </button>
                                                    
                                                    <button id="registered-device" class="btn btn-success btn-sm" disabled style="<?= !$myConfig['hasEnabledNotifications'] ? 'display: none;' : ''?>">
                                                        <i class="ti ti-bell"></i> <?= lang('Pages.Push_Notifications_Enabled') ?>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-end">
                                                <button id="mark-all-as-read" class="btn btn-warning btn-sm">
                                                        <i class="ti ti-checks"></i> <?= lang('Pages.Mark_All_As_Read') ?>
                                                    </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </li>

                            <li class="list-inline-item mb-0 ms-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.MyProfile') ?>">
                                <div class="dropdown dropdown-primary">
                                    <button type="button" class="btn btn-soft-light dropdown-toggle p-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="<?= base_url('writable/uploads/user-avatar/' . $avatar) ?>" class="avatar avatar-ex-small rounded" alt="<?= htmlspecialchars($userData->username) ?>"></button>
                                    <div class="dropdown-menu dd-menu dropdown-menu-end shadow border-0 mt-3 py-3" style="min-width: 200px;">
                                        <a class="dropdown-item d-flex align-items-center text-dark pb-3">

                                            <div class="container-profilepic card rounded-circle overflow-hidden">
                                                <div class="photo-preview card-img w-100 h-100"></div>
                                                <div class="middle-profilepic text-center card-img-overlay d-none flex-column justify-content-center" onclick="openUploadAvatarModal(); $(this).closest('li').tooltip('hide'); $(this).tooltip('hide');" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.upload_new_avatar') ?>">
                                                        <i class="ti ti-upload text-dark"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1 ms-2">
                                                <span class="d-block"><?= htmlspecialchars($userData->username) ?></span> 
                                                <span class="d-block text-muted small"><?= htmlspecialchars($userData->first_name) ?> <?= htmlspecialchars($userData->last_name) ?></span>
                                                <span class="d-block text-muted small">
                                                    <?= lang('Pages.User_API') ?>
                                                    <span data-bs-toggle="modal" data-bs-target="#manageUserApiKey" style="cursor: pointer;">
                                                        <i class="ti ti-edit" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Manage_user_api_key') ?>"></i>:
                                                    </span>
                                                    
                                                    <span class="broken-underline" 
                                                        data-clipboard-text="<?= $userData->api_key ?>" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="bottom" 
                                                        data-bs-original-title="<?= lang('Pages.Copy_user_api') ?>" 
                                                        id="user_api_top"
                                                        onclick="copyUserApi(this);">
                                                        <?= $userData->api_key ?>
                                                    </span> 

                                                    <i class="ti ti-copy" style="cursor: pointer;" onclick="copyUserApi(document.getElementById('user_api_top'))"></i>
                                                </span>
                                                <small class="text-muted"><?= htmlspecialchars($userData->getEmail()) ?></small>
                                                <?php $lastLoginHistory = json_decode($lastLoginHistory, true); ?>
                                                <span class="d-block text-muted small">
                                                    <?php
                                                    if($lastLoginHistory) {
                                                        echo lang('Pages.last_login_details', ['ip_address' => $lastLoginHistory['ip_address'], 'date' => formatDate($lastLoginHistory['date']['date'])]);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </a>
                                        <?php if($myConfig['licenseManagerOnUse'] !== 'slm') { ?>
                                            <a class="dropdown-item text-dark"
                                                href="<?= base_url('documentation/api/') ?>"
                                                target="_blank"
                                                onclick="$(this).closest('li').tooltip('hide');">
                                                <span class="mb-0 d-inline-block me-1"><i class="ti ti-book"></i></span>
                                                <?= lang('Pages.API_Documentation') ?>
                                            </a>
                                        <?php } ?>

                                        <a class="dropdown-item text-dark"
                                            href="javascript:void(0)"
                                            data-bs-toggle="modal"
                                            data-bs-target="#userChangePassword"
                                            onclick="$(this).closest('li').tooltip('hide');">
                                            <span class="mb-0 d-inline-block me-1"><i class="ti ti-lock"></i></span>
                                            <?= lang('Pages.change_password') ?>
                                        </a>

                                        <?php if($userData->id !== 1) { ?>
                                            <a class="dropdown-item text-danger"
                                                href="javascript:void(0)"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteAccount"
                                                onclick="$(this).closest('li').tooltip('hide');">
                                                <span class="mb-0 d-inline-block me-1"><i class="ti ti-user-x"></i></span>
                                                <?= lang('Pages.Delete_Account') ?>
                                            </a>
                                        <?php } ?>
                                        <?php if($userData->id === 1) { ?>
                                            <a class="dropdown-item text-dark border-top <?= $myConfig['licenseManagerOnUse'] !== 'slm' ? '' : 'border-bottom' ?>" href="<?= base_url('app-settings/registration/') ?>"><span class="mb-0 d-inline-block me-1"><i class="ti ti-key"></i></span> <?= lang('Pages.app_registration') ?></a>

                                            <?php if($myConfig['licenseManagerOnUse'] !== 'slm') { ?>
                                                <a class="dropdown-item text-dark border-bottom" href="<?= base_url('documentation/api-super-admin/') ?>" target="_blank"><span class="mb-0 d-inline-block me-1"><i class="ti ti-book"></i></span> <?= lang('Pages.API_Documentation_Super_Admin') ?></a>
                                            <?php } ?>  
                                                                                        
                                        <?php } ?>
                                        <a class="dropdown-item text-dark" href="<?= base_url('logout') ?>"><span class="mb-0 d-inline-block me-1"><i class="ti ti-logout"></i></span> <?= lang('Pages.Logout') ?></a>

                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
