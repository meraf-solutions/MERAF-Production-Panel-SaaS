                <footer class="shadow py-3">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-sm-start text-center mx-md-2">
                                    <p class="mb-0 text-muted"><?= lang('Pages.footer_copyright', ['year' => date("Y"),'appName' => $myConfig['appName']]) ?></p>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end container-->
                </footer><!--end footer-->

                <?php if($myConfig['push_notification_feature_enabled']) : ?>
                    <?php
                    $cookieDisallowed = get_cookie('webprompt_disallowed') === 'true';
                    $hasEnabledNotifications = $myConfig['hasEnabledNotifications'];

                    // If either condition is true, don't show the prompt
                    $hidePrompt = $cookieDisallowed && $hasEnabledNotifications;
                    ?>

                    <div id="webpush-prompt"
                        class="position-fixed bottom-0 end-0 m-3 p-4 card cookie-popup shadow rounded py-3 px-4"
                        style="<?= $cookieDisallowed || $hasEnabledNotifications ? 'display: none;' : '' ?> z-index: 1050; max-width: 300px;">

                        <h6 class="mb-2 text-center"><?= lang('Pages.Enable_Notifications_question') ?></h6>
                        <div class="d-flex">
                            <img src="<?= esc($myConfig['appIcon']) ?>" height="64" alt="<?= $myConfig['appName'] ?>">
                            <p class="text-muted mb-3"><?= lang('Pages.Would_you_like_to_receive_updates_and_alerts') ?></p>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-secondary btn-sm me-2" id="webpush-deny"><?= lang('Pages.No_Thanks') ?></button>
                            <button class="btn btn-primary btn-sm" id="webpush-allow"><?= lang('Pages.Allow') ?></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(file_exists(APPPATH . 'Views/includes/dashboard/developer.php')) : ?>
                    <!--  Modal Changelog Start  -->
                    <div class="modal fade" id="ModalChangelog" tabindex="-1" aria-labelledby="ModalChangelog-title" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content rounded shadow border-0">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title" id="ModalChangelog-title"><?= lang('Pages.app_changelog', ['appName' => $myConfig['appName']]) ?></h5>
                                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <div class="p-3 rounded box-shadow">
                                        <p class="text-muted mb-0">
                                            <?php
                                                $versionFilePath = USER_DATA_PATH . 'version.json';

                                                if (file_exists($versionFilePath)) {
                                                    $versionData = json_decode(file_get_contents($versionFilePath), true);

                                                    // Access the 'changelog' key directly from $versionData
                                                    $changelog = $versionData['changelog'];

                                                    // Print the changelog
                                                    echo nl2br($changelog); // Use nl2br to preserve line breaks
                                                } else {
                                                    // Display an error message if the file doesn't exist
                                                    echo '<div class="alert alert-danger fade show text-center" role="alert">'.lang('Notifications.unable_to_retrieve_changelog').'</div>';
                                                }
                                            ?>
                                        </p>                                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--  Modal Changelog End  -->
                <?php endif; ?>

                <!--  Modal Manage User API Key Start  -->
                <div class="modal fade" id="manageUserApiKey" tabindex="-1" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog  modal-dialog-centered">
                        <div class="modal-content rounded shadow border-0">
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title"><?= lang('Pages.API_Key_Management') ?></h5>
                                <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                            </div>
                            <div class="modal-body p-0">
                                <form class="login-form p-4">

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="username"><?= lang('Pages.Username') ?></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="user" class="fea icon-sm icons"></i>
                                                    <input type="text" class="form-control ps-5" id="username" name="username" readonly value="<?= $userData->username ?>">
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                

                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="user_email"><?= lang('Pages.Admin_Email_Label') ?></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                                    <input type="text" class="form-control ps-5" id="user_email" name="user_email" readonly value="<?= $userData->email ?>">
                                                </div>
                                            </div>
                                        </div><!--end col-->

                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="user_api"><?= lang('Pages.User_API') ?></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                                    <input type="text" class="form-control ps-5 copy-to-clipboard" id="user_api" name="user_api" readonly value="<?= $userData->api_key ?>">
                                                </div>
                                            </div>
                                        </div><!--end col-->

                                        <div class="col-lg-12 text-center mb-0">
                                            <a href="javascript:void(0)" class="btn btn-primary" id="regenerate-user-api-key"><i class="uil uil-plus"></i> <?= lang('Pages.Regenerate') ?></a>
                                        </div><!--end col-->

                                    </div><!--end row-->
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  Modal Manage User API Key End  -->

                <!-- Delete Account Modal -->
                <div class="modal fade" id="deleteAccount" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= lang('Pages.Delete_Account') ?></h5>
                                <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <h5><i class="icon uil uil-exclamation-triangle"></i> <?= lang('Pages.Warning_exclamation') ?></h5>
                                    <p><?= lang('Pages.delete_account_warning') ?></p>
                                    <ul>
                                        <li><?= lang('Pages.delete_account_products') ?></li>
                                        <li><?= lang('Pages.delete_account_licenses') ?></li>
                                        <li><?= lang('Pages.delete_account_subscriptions') ?></li>
                                        <li><?= lang('Pages.delete_account_unrecoverable') ?></li>
                                    </ul>
                                </div>
                                <div id="delete-user-form" class="form-group">
                                    <label for="confirmDeletionText"><?= lang('Pages.delete_account_confirmation_input') ?></label>
                                    <input type="email" class="form-control" id="confirmDeletionText" />
                                     <div class="invalid-feedback">
                                                <?= lang('Pages.email_required_feedback') ?>
                                            </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                                <button type="button" class="btn btn-danger" id="confirmDeletionBtn">
                                    <i class="uil uil-trash"></i> <?= lang('Pages.Delete') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                      
                <!--  Modal Change Password Start  -->
                <div class="modal fade" id="userChangePassword" tabindex="-1" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog  modal-dialog-centered">
                        <div class="modal-content rounded shadow border-0">
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title"><?= lang('Pages.change_password') ?></h5>
                                <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                            </div>
                            <div class="modal-body p-0">
                                <form class="login-form p-4" id="change-password-form" novalidate>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="oldPassword"><?= lang('Pages.old_password') ?> <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                                    <input type="password" class="form-control ps-5" id="oldPassword" name="oldPassword" placeholder="<?= lang('Pages.enter_old_password') ?>" required="">
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                

                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="initial_newPassword"><?= lang('Pages.new_password') ?> <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                                    <input type="password" class="form-control ps-5" id="initial_newPassword" name="initial_newPassword" placeholder="<?= lang('Pages.enter_new_password') ?>" required="">
                                                </div>
                                            </div>
                                        </div><!--end col-->

                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="newPassword"><?= lang('Pages.confirm_password') ?> <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                                    <input type="password" class="form-control ps-5" id="newPassword" name="newPassword" placeholder="<?= lang('Pages.confirm_new_password') ?>" required="">
                                                </div>
                                            </div>
                                        </div><!--end col-->

                                        <div class="col-lg-12 text-center mb-0">
                                            <div class="d-grid">
                                                <button class="mx-auto btn btn-primary" id="change-password-submit"><i class="uil uil-save"></i> <?= lang('Pages.Submit') ?></button>
                                            </div>
                                        </div><!--end col-->

                                    </div><!--end row-->
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  Modal Change Password End  -->

                <!--  Modal Upload Avatar Start  -->
                <div class="modal fade" id="uploadAvatar" tabindex="-1" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog  modal-dialog-centered">
                        <div class="modal-content rounded shadow border-0">
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title"><?= lang('Pages.upload_avatar') ?></h5>
                                <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                            </div>
                            <div class="modal-body p-0">
                                <form class="login-form p-4" id="upload-avatar-form" novalidate>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="profilePassword"><?= lang('Pages.enter_your_password') ?> <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                                    <input type="password" class="form-control ps-5" id="profilePassword" name="profilePassword" placeholder="<?= lang('Pages.enter_your_password') ?>" required="">
                                                </div>
                                            </div>
                                        </div><!--end col-->                                               

                                        <div class="col-12 mb-3">
                                            <label class="form-label" for="newAvatarImage"><?= lang('Pages.jpeg_avatar_size') ?> <span class="text-danger">*</span></label>
                                            <input class="form-control" name="newAvatarImage" id="newAvatarImage" type="file" accept=".jpg,.jpeg" required>
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.choose_avatar_in_jpeg_format') ?>
                                            </div>
                                        </div>                                        

                                        <div class="col-lg-12 text-center mb-0">
                                            <div class="d-grid">
                                                <button class="mx-auto btn btn-primary" id="upload-avatar-submit"><i class="uil uil-save"></i> <?= lang('Pages.Submit') ?></button>
                                            </div>
                                        </div><!--end col-->

                                    </div><!--end row-->
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  Modal Change Password End  -->                