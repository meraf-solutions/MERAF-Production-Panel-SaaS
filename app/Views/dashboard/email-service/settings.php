<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('head') ?>
    <style>
        .form-check-input.form-check-input {
            width: 48px;
            height: 24px;
            margin-top: 0;                
            background-color: #2f55d4 !important;
            border-color: #2f55d4 !important;
        }
    </style>    
<?= $this->endSection() //End section('head')?>

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
    <form class="" novalidate id="email-settings-form">
        <div class="row">
            <div class="col-lg-12 mt-4">

                <div class="col-12 mb-3 text-center">                                
                    <button class="mx-auto btn btn-primary" id="email-settings-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save_Settings') ?></button>
                </div> 
            </div>
            <!-- Admin Name/Email -->
            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.Admin_Name_Email') ?></h5>
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="adminName"><?= lang('Pages.Admin_Name_Label') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="user" class="fea icon-sm icons"></i>
                                    <input name="adminName" id="adminName" type="text" class="form-control ps-5 emailName" placeholder="<?= lang('Pages.Admin_Name_Placeholder') ?>" value="<?= $myConfig['adminName'] ?? '' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Admin_Name_Required') ?>
                                    </div>  
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="adminEmail"><?= lang('Pages.Admin_Email_Label') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="adminEmail" id="adminEmail" type="email" class="form-control ps-5 emailInput" placeholder="<?= lang('Pages.Admin_Email_Placeholder') ?>" value="<?= $myConfig['adminEmail'] ?? '' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Admin_Email_Required') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->                                            

            <!-- Reply-To Name/Email -->
            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.Reply_To_Name_Email') ?></h5>
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="replyToName"><?= lang('Pages.Reply_To_Name_Label') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="user" class="fea icon-sm icons"></i>
                                    <input name="replyToName" id="replyToName" type="text" class="form-control ps-5 emailName" placeholder="<?= lang('Pages.Reply_To_Name_Placeholder') ?>" value="<?= $myConfig['replyToName'] ? $myConfig['replyToName'] : '' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Reply_To_Name_Required') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="replyToEmail"><?= lang('Pages.Reply_To_Email_Label') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="replyToEmail" id="replyToEmail" type="email" class="form-control ps-5 emailInput" placeholder="<?= lang('Pages.Reply_To_Email_Placeholder') ?>" value="<?= $myConfig['replyToEmail'] ?? '' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Reply_To_Email_Required') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->

            <!-- BCC Name/Email -->
            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.BCC_Name_Email') ?></h5>
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="bccName"><?= lang('Pages.BCC_Name_Label') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="user" class="fea icon-sm icons"></i>
                                    <input name="bccName" id="bccName" type="text" class="form-control ps-5 emailName" placeholder="<?= lang('Pages.BCC_Name_Placeholder') ?>" value="<?= $myConfig['bccName'] ?? '' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.BCC_Name_Required') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->      
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="bccEmail"><?= lang('Pages.BCC_Email_Label') ?> <span class="text-danger">*</span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                    <input name="bccEmail" id="bccEmail" type="email" class="form-control ps-5 emailInput" placeholder="<?= lang('Pages.BCC_Email_Placeholder') ?>" value="<?= $myConfig['bccEmail'] ?? '' ?>" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.BCC_Email_Required') ?>
                                    </div> 
                                </div>
                            </div>
                        </div><!--end col-->                                                          
                    </div><!--end row-->
                </div>
            </div><!--end col-->                                            

            <!-- Test email service settings -->
            <div class="col-md-12 col-lg-6 mt-3">
                <div class="card border-0 rounded shadow p-4">
                    <h5 class="mb-0"><?= lang('Pages.Send_Test_Email') ?> :</h5>
                    <div class="col-12 mt-4 text-center">
                        <a href="javascript:void(0)" class="btn btn-soft-secondary btn-sm me-2" id="test-email-service-btn"><i class="uil uil-envelope-send"></i> <?= lang('Pages.open_test_email_modal') ?></a>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </form>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Email Service Test modal -->
    <div class="modal fade" id="TestEmail" tabindex="-1" aria-labelledby="TestEmail-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><?= lang('Pages.Test_Email_Sending') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="TestEmail-form" novalidate>                        
                        <!-- From Email -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="testFromEmail"><?= lang('Pages.test_From_email') ?></label>
                                <div class="form-icon position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail fea icon-sm icons"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input name="testFromEmail" id="testFromEmail" type="text" class="form-control ps-5 emailInput" value ="<?= $myConfig['fromEmail'] ?>" placeholder="Enter From Email address" readonly required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_From_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->
                        
                        <!-- To Email -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="testToEmail"><?= lang('Pages.test_To_email') ?></label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail fea icon-sm icons"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input name="testToEmail" id="testToEmail" type="text" class="form-control ps-5 emailInput" value ="<?= $myConfig['replyToEmail'] ?>" placeholder="Enter To Email address" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_To_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->
                        
                        <!-- Subject -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="testSubjectEmail"><?= lang('Pages.test_Subject_email') ?></label>
                                <div class="form-icon position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 fea icon-sm icons"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    <input name="testSubjectEmail" id="testSubjectEmail" type="text" class="form-control ps-5" value ="This is a test email from <?= $myConfig['appName'] ?>" placeholder="Enter email subject" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_Subject_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->							

                        <!-- Email Body-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="testBodyEmail"><?= lang('Pages.test_Body_email') ?></label>
                                <div class="form-icon position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square fea icon-sm icons"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    <textarea name="testBodyEmail" id="testBodyEmail" rows="5" class="form-control ps-5" placeholder="Enter email message" required>The quick brown fox jumps over the lazy dog.</textarea>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_Body_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->

                        <div class="d-flex justify-content-center align-items-center">
                            <label class="toggler text-muted " id="text">Text</label>
                            <div class="form-check form-switch mx-3">
                                <input class="form-check-input" type="checkbox" id="testEmailFormat" name="testEmailFormat" checked="">
                            </div>
                            <label class="toggler text-muted toggler--is-active" id="html">HTML</label>
                        </div>                            

                        <div class="col-12">
                            <div class="mt-4 mb-3 text-center text-center">
                                <button class="mx-auto btn btn-primary" id="test-email-submit"><i class="uil uil-envelope-send"></i> <?= lang('Pages.Send_Test_Email') ?></button>
                            </div>
                        </div><!--end col-->

                    </form>
                </div><!-- end modal body -->
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        /******************************
        // Handle the app settings save
        ******************************/
        // Define regex patterns
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const disallowedCharsRegexForEmail = /[~!#$%&*+=|:()\[\]]/;

        // Validation functions
        function validateEmail(email) {
            return email !== '' && emailRegex.test(email) && !disallowedCharsRegexForEmail.test(email);
        }

        function validateName(name) {
            return name.length >= 2 && name.length <= 50;
        }

        // Handle email settings submission
        $('#email-settings-submit').on('click', function (e) {
            e.preventDefault();

            const submitButton = $(this);
            const form = $('#email-settings-form');

            enableLoadingEffect(submitButton);

            // Clear previous validation states
            form.find('.is-invalid').removeClass('is-invalid');
            
            // Perform client-side validation
            let isValid = true;
            const fields = [
                'adminName', 'adminEmail',
                'replyToName', 'replyToEmail',
                'bccName', 'bccEmail'
            ];

            fields.forEach(field => {
                const input = $(`#${field}`);
                const feedbackDiv = input.siblings('.invalid-feedback');
                const value = input.val().trim();
                
                if (value === '') {
                    isValid = false;
                    input.addClass('is-invalid');
                    showToast('danger', '<?= lang('Notifications.the_field_is_required') ?>');
                } else if (field.includes('Name')) {
                    if (!validateName(value)) {
                        isValid = false;
                        input.addClass('is-invalid');
                        showToast('danger', '<?= lang('Notifications.email_name_req_characters') ?>');
                    }
                } else if (field.includes('Email')) {
                    if (!validateEmail(value)) {
                        isValid = false;
                        input.addClass('is-invalid');
                        showToast('danger', '<?= lang('Notifications.email_address_req_invalid') ?>');
                    }
                }
            });

            if (!isValid) {
                showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                disableLoadingEffect(submitButton);
                return;
            }

            $.ajax({
                url: '<?= base_url('email-service/settings/save') ?>',
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    if (response.success) {
                        showToast('success', response.msg);
                    } else {
                        if (typeof response.msg === 'object') {
                            Object.entries(response.msg).forEach(([field, message]) => {
                                const inputElement = $(`#${field}`);
                                const feedbackDiv = inputElement.siblings('.invalid-feedback');
                                
                                if (inputElement.length) {
                                    inputElement.addClass('is-invalid');
                                    if (feedbackDiv.length) {
                                        feedbackDiv.text(message);
                                    }
                                }
                            });
                            showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                        } else {
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

        // Handle test email submission
        $('#TestEmail-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            
            const form = $(this);
            const submitButton = $('#test-email-submit');
            const testFromEmail = $('#testFromEmail');
            const testToEmail = $('#testToEmail');
            const testSubjectEmail = $('#testSubjectEmail');
            const testBodyEmail = $('#testBodyEmail');

            // Enable button loading effect
            enableLoadingEffect(submitButton);

            // Remove existing 'is-invalid' classes
            form.find('.is-invalid').removeClass('is-invalid').end().find('.is-valid').removeClass('is-valid');
            
            // Validation logic
            let isValid = true;
            let validationErrors = [];

            if (!validateEmail(testFromEmail.val().trim())) {
                isValid = false;
                testFromEmail.addClass('is-invalid');
                showToast('danger', '<?= lang('Pages.test_From_Email_Required') ?>');
            }

            if (!validateEmail(testToEmail.val().trim())) {
                isValid = false;
                testToEmail.addClass('is-invalid');
                showToast('danger', '<?= lang('Pages.test_To_Email_Required') ?>');
            }

            if (testSubjectEmail.val().trim() === '') {
                isValid = false;
                testSubjectEmail.addClass('is-invalid');
                showToast('danger', '<?= lang('Pages.test_Subject_Email_Required') ?>');
            }

            if (testBodyEmail.val().trim() === '') {
                isValid = false;
                testBodyEmail.addClass('is-invalid');
                showToast('danger', '<?= lang('Pages.test_Body_Email_Required') ?>');
            }

            if (!isValid) {
                showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                disableLoadingEffect(submitButton);
                return;
            }
            
            $.ajax({
                url: '<?= base_url('email-service/settings/test') ?>',
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    let toastType = 'info';

                    if (response.success) {
                        toastType = 'success';
                    } else {
                        toastType = 'danger';
                    }

                    showToast(toastType, response.msg);
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

        // Prevent default form submission when the submit button is clicked
        $('#test-email-submit').on('click', function (e) {
            e.preventDefault();
            $('#TestEmail-form').submit(); // Trigger form submission
        });

        // Handle opening the test email modal
        $('#test-email-service-btn').on('click', function() {
            $('#TestEmail').modal('show');
        });
    </script>
<?= $this->endSection() //End section('scripts')?>