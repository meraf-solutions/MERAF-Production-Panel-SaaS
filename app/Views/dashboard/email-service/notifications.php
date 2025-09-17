<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <?php if(isset($section)) : ?>
                    <li class="breadcrumb-item text-capitalize <?= !isset($subsection) ? 'active' : '' ?>" <?= !isset($subsection) ? 'aria-current="page"' : '' ?>><?= lang('Pages.' . ucwords($section)) ?></li>
                <?php endif; ?>

                <?php if(isset($subsection)) : ?>
                    <li class="breadcrumb-item text-capitalize active" aria-current="page"><?= lang('Pages.' . ucwords($subsection)  ) ?></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?= $this->endSection() //End section('heading')?>

<?= $this->section('content') ?>
    <form class="" novalidate id="email-notifications-form">
        <div class="row">
            <div class="col-lg-12 mt-4">

                <div class="col-12 mb-3 text-center">                                
                    <button class="mx-auto btn btn-primary" id="email-notifications-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save_Settings') ?></button>
                </div> 

                <div class="card border-0 rounded shadow p-4">
                    <h4 class="mb-3"><?= lang('Pages.Notification_Messages') ?></h4>

                    <label class="form-label" for="selectedEmailTemplate"><?= lang('Pages.Default_Email_Template') ?></label>
                    <div class="form-icon position-relative">
                        <i data-feather="layout" class="fea icon-sm icons"></i>
                        <select class="form-select form-control ps-5" id="selectedEmailTemplate" name="selectedEmailTemplate">
                            <option value="" disabled><?= lang('Pages.Select_Template') ?></option>
                            <?php
                            if(count($emailTemplateSelections) !== 0) {
                                
                                $emailTemplateDetails = getEmailTemplateDetails($userData->id);

                                foreach($emailTemplateSelections as $emailTemplate) {
                                    $selected = $myConfig['selectedEmailTemplate'] === $emailTemplate ? 'selected' : '';                                                                            
                                    echo '<option value="' . $emailTemplate . '" ' . $selected . '>' . $emailTemplateDetails[$emailTemplate]['templateName'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div><!--end col--> 

            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.Email_Notifications') ?></h5>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between pb-4">
                            <label class="h6 mb-0" for="sendEmailInvalidChecks"><?= lang('Pages.notify_admin_invalid_license_check') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendEmailInvalidChecks'] ? 'checked' : '' ?> id="sendEmailInvalidChecks" name="sendEmailInvalidChecks">                                                                    
                            </div>
                        </div>
                        <div class="d-flex justify-content-between py-4 border-top">
                            <label class="h6 mb-0" for="sendEmailNewLicense"><?= lang('Pages.send_license_details_to_customer') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendEmailNewLicense'] ? 'checked' : '' ?> id="sendEmailNewLicense" name="sendEmailNewLicense">
                            </div>
                        </div>                                                          

                        <div class="d-flex justify-content-between py-4 border-top">
                            <label class="h6 mb-0" for="sendBCCtoResendLicense"><?= lang('Pages.BCC_the_admin_resend_license') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendBCCtoResendLicense'] ? 'checked' : '' ?> id="sendBCCtoResendLicense" name="sendBCCtoResendLicense">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between py-4 border-top">
                            <label class="h6 mb-0" for="sendBCCtoLicenseClientNotifications"><?= lang('Pages.BCC_the_admin_license_notification_for_client') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendBCCtoLicenseClientNotifications'] ? 'checked' : '' ?> id="sendBCCtoLicenseClientNotifications" name="sendBCCtoLicenseClientNotifications">
                            </div>
                        </div>
                    </div>

                </div>
            </div><!--end col-->

            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.heading_license_key_activation_email') ?></h5>
                    <div class="row mt-4">
                        <div class="d-flex mb-3 justify-content-between py-2 border-top border-bottom">
                            <label class="h6 mb-0" for="sendActivationNotification"><?= lang('Pages.send_activated_notification_label') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendActivationNotification'] ? 'checked' : '' ?> id="sendActivationNotification" name="sendActivationNotification">
                            </div>
                        </div>                                                                              
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="activationEmailSubject"><?= lang('Pages.Email_Subject') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="activationEmailSubject" id="activationEmailSubject" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Subject') ?>" value="<?= $myConfig['activationEmailSubject'] ?? 'Your License Has Been Activated' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div>  
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="activationEmailMessage"><?= lang('Pages.Email_Message') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="activationEmailMessage" id="activationEmailMessage" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Message') ?>" value="<?= $myConfig['activationEmailMessage'] ?? 'Your license key was activated successfully' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->

            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.heading_license_key_reminder_email') ?></h5>
                    <div class="row mt-4">
                        <div class="d-flex mb-3 justify-content-between py-2 border-top border-bottom">
                            <label class="h6 mb-0" for="sendReminderNotification"><?= lang('Pages.send_reminder_notification_label') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendReminderNotification'] ? 'checked' : '' ?> id="sendReminderNotification" name="sendReminderNotification">
                            </div>
                        </div>
                        <div class="d-flex mb-3 justify-content-between py-2 border-top border-bottom">
                            <label class="h6 mb-0" for="numberOfHoursToRemind" style="align-content: center;"><?= lang('Pages.send_reminder_notification_checkbox') ?></label>
                            <div class="form-icon position-relative">
                                <i data-feather="clock" class="fea icon-sm icons"></i>
                                <input name="numberOfHoursToRemind" id="numberOfHoursToRemind" type="number" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Value') ?>" value="<?= $myConfig['numberOfHoursToRemind'] ?? '24' ?>">
                            </div> 
                        </div> 
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="reminderEmailSubject"><?= lang('Pages.Email_Subject') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="reminderEmailSubject" id="reminderEmailSubject" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Subject') ?>" value="<?= $myConfig['reminderEmailSubject'] ?? 'Your License is Expiring Soon' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div>  
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="reminderEmailMessage"><?= lang('Pages.Email_Message') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="reminderEmailMessage" id="reminderEmailMessage" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Message') ?>" value="<?= $myConfig['reminderEmailMessage'] ?? 'Your license key is about to expire' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->

            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.heading_license_key_expired_email') ?></h5>
                    <div class="row mt-4">
                        <div class="d-flex mb-3 justify-content-between py-2 border-top border-bottom">
                            <label class="h6 mb-0" for="sendExpiredNotification"><?= lang('Pages.send_expired_notification_label') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendExpiredNotification'] ? 'checked' : '' ?> id="sendExpiredNotification" name="sendExpiredNotification">
                            </div>
                        </div>  
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="expiredLicenseEmailSubject"><?= lang('Pages.Email_Subject') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="expiredLicenseEmailSubject" id="expiredLicenseEmailSubject" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Subject') ?>" value="<?= $myConfig['expiredLicenseEmailSubject'] ?? 'Your License Has Expired' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div>  
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="expiredLicenseEmailMessage"><?= lang('Pages.Email_Message') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="expiredLicenseEmailMessage" id="expiredLicenseEmailMessage" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Message') ?>" value="<?= $myConfig['expiredLicenseEmailMessage'] ?? 'Your license key has expired' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->

            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.heading_license_key_registration_email') ?></h5>
                    <div class="row mt-4">
                        <div class="d-flex mb-3 justify-content-between py-2 border-top border-bottom">
                            <label class="h6 mb-0" for="sendNewDomainDeviceRegistration"><?= lang('Pages.send_notif_new_reg_domaindevice') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendNewDomainDeviceRegistration'] ? 'checked' : '' ?> id="sendNewDomainDeviceRegistration" name="sendNewDomainDeviceRegistration">
                            </div>
                        </div>                                                                            
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="newDomainDeviceEmailSubject"><?= lang('Pages.Email_Subject') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="newDomainDeviceEmailSubject" id="newDomainDeviceEmailSubject" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Subject') ?>" value="<?= $myConfig['newDomainDeviceEmailSubject'] ?? 'New Domain/Device Activated' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div>  
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="newDomainDeviceEmailMessage"><?= lang('Pages.Email_Message') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="newDomainDeviceEmailMessage" id="newDomainDeviceEmailMessage" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Message') ?>" value="<?= $myConfig['newDomainDeviceEmailMessage'] ?? 'A new domain/device was registered to your license' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->

            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.heading_license_key_deactivation_email') ?></h5>
                    <div class="row mt-4">
                        <div class="d-flex mb-3 justify-content-between py-2 border-top border-bottom">
                            <label class="h6 mb-0" for="sendUnregisteredDomainDeviceRegistration"><?= lang('Pages.send_notif_unreg_domaindevice') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" <?= $myConfig['sendUnregisteredDomainDeviceRegistration'] ? 'checked' : '' ?> id="sendUnregisteredDomainDeviceRegistration" name="sendUnregisteredDomainDeviceRegistration">
                            </div>
                        </div>                                                                              
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="unregisteredDomainDeviceEmailSubject"><?= lang('Pages.Email_Subject') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="unregisteredDomainDeviceEmailSubject" id="unregisteredDomainDeviceEmailSubject" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Subject') ?>" value="<?= $myConfig['unregisteredDomainDeviceEmailSubject'] ?? 'Domain/Device Deactivated' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div>  
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="unregisteredDomainDeviceEmailMessage"><?= lang('Pages.Email_Message') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="unregisteredDomainDeviceEmailMessage" id="unregisteredDomainDeviceEmailMessage" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Email_Message') ?>" value="<?= $myConfig['unregisteredDomainDeviceEmailMessage'] ?? 'A domain/device was deactivated from your license' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Enter_a_valid_value') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->
        </div>
    </form>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        /******************************
        // Handle the app settings save
        ******************************/
        $('#email-notifications-submit').on('click', function (e) {
            e.preventDefault();

            const submitButton = $(this);
            const form = $('#email-notifications-form');

            // Clear previous validation states
            form.find('.is-invalid').removeClass('is-invalid');
            
            // Perform client-side validation
            const fields = [
                'activationEmailSubject', 'activationEmailMessage',
                'reminderEmailSubject', 'reminderEmailMessage',
                'expiredLicenseEmailSubject', 'expiredLicenseEmailMessage',
                'newDomainDeviceEmailSubject', 'newDomainDeviceEmailMessage',
                'unregisteredDomainDeviceEmailSubject', 'unregisteredDomainDeviceEmailMessage'
            ];

            fields.forEach(field => {
                const input = $(`#${field}`);
                const defaultFeedback = '<?= lang('Notifications.the_field_is_required') ?>';
                const value = input.val().trim();
                
                if (value === '') {
                    input.addClass('is-invalid');
                    showToast('danger', defaultFeedback); // the default feedback message
                } else if (field.includes('Subject')) {
                    if (value.length < 5 || value.length > 200) {
                        input.addClass('is-invalid');
                        showToast('danger', '<?= lang('Notifications.email_notif_subject_req_characters') ?>');
                    }
                } else if (field.includes('Message')) {
                    if (value.length < 10) {
                        input.addClass('is-invalid');
                        showToast('danger', '<?= lang('Notifications.email_notif_message_req_characters') ?>');
                    }
                }
            });
            
            enableLoadingEffect(submitButton);

            $.ajax({
                url: '<?= base_url('email-service/notifications/save') ?>',
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    let toastType = 'info';

                    if (response.success) {
                        showToast('success', response.msg);
                    } else {
                        // Handle validation errors
                        if (typeof response.msg === 'object') {
                            // Loop through each error message
                            // Object.entries(response.msg).forEach(([field, message]) => {
                            //     // Find the input element and its feedback div
                            //     const inputElement = $(`#${field}`);
                            //     const feedbackDiv = inputElement.siblings('.invalid-feedback');
                                
                            //     if (inputElement.length) {
                            //         // Add is-invalid class
                            //         inputElement.addClass('is-invalid');
                                    
                            //         // Update existing feedback div
                            //         if (feedbackDiv.length) {
                            //             feedbackDiv.text(message);
                            //         }
                            //     }
                            // });

                            // Show general error message
                            showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                        } else {
                            // Handle non-validation error
                            showToast('danger', response.msg);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    // Show error toast
                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                },
                complete: function () {
                    disableLoadingEffect(submitButton);
                }
            });
        });
    </script>
<?= $this->endSection() //End section('scripts')?>
