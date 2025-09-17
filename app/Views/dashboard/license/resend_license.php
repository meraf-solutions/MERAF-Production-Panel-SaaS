<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.' . ucwords($section)  ) ?></h5>

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
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-8 mt-4">
            <div class="card rounded shadow p-4 border-0">
                <h4 class="mb-3"><?= lang('Pages.License_Details_and_Recipient') ?></h4>
                <form class="" novalidate id="resend-license-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="licenseInput" class="form-label"><?= lang('Pages.License_Key') ?></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light text-muted border"><i class="uil uil-key-skeleton align-middle"></i></span>
                                <input type="text" class="form-control" id="licenseInput" name="licenseInput" placeholder="<?= lang('Pages.Enter_the_license_key') ?>" required>
                                <div class="invalid-feedback"> <?= lang('Pages.A_license_key_is_required') ?> </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="mb-3">
                                <label class="form-label" for="recipientTextarea"><?= lang('Pages.Recipients') ?><br><span
                                    class="text-info"><?= lang('Pages.one_email_per_line') ?></span></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="at-sign" class="fea icon-sm icons"></i>
                                    <textarea name="recipientTextarea" id="recipientTextarea" rows="8" class="form-control ps-5" placeholder="<?= lang('Pages.Recipients') ?> :" required></textarea>
                                    <div class="invalid-feedback"> <?= lang('Pages.Valid_email_required') ?> </div>
                                </div>
                            </div>
                        </div>

                    </div>        

                    <div class="col-12 text-center">
                        <button class="mx-auto btn btn-primary" id="resend-license-submit"><i class="uil uil-envelope-send"></i> <?= lang('Pages.Resend_License_Key_Details') ?></button>
                    </div>
                </form>
            </div>
        </div><!--end col-->
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            /*******************************************
            // Handle the resend license detail requests
            *******************************************/
            $('#resend-license-submit').on('click', function (e) {
                e.preventDefault();				

                var form = $('#resend-license-form');
                var licenseInput = $('#licenseInput');
                var recipientTextarea = $('#recipientTextarea');
                var submitButton = $(this);

                // Regular expression for email validation
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                // Define a regular expression for not allowed characters
                var disallowedCharsRegex_licenseKey = /[~!#$%&*\-_+=|:.]/;
                var disallowedCharsRegex_forEmail   = /[~!#$%&*+=|:()\[\]]/;

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');
                
                /*******************
                 * Start validations
                 ******************/        
                
                // Iterate over other license key field and validate
                if(licenseInput.val() === '') {
                    licenseInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                // } else if (disallowedCharsRegex_licenseKey.test(licenseInput.val()) || licenseInput.val().length !== 40) {
                //     licenseInput.addClass('is-invalid');
                } else {
                    licenseInput.addClass('is-valid');
                } 

                if(recipientTextarea.val() === '') {
                    recipientTextarea.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                }

                // Validate recipient text area input
                var recipientLines = recipientTextarea.val().split('\n');
                var isValidRecipient = true;

                for (var i = 0; i < recipientLines.length; i++) {
                    var trimmedLine = recipientLines[i].trim();

                    if (trimmedLine !== '' && (!emailRegex.test(trimmedLine) || trimmedLine.includes(','))) {
                        isValidRecipient = false;
                        break;
                    }
                }

                if (recipientTextarea.val() === '' || !isValidRecipient) {
                    recipientTextarea.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	

                    return;
                } else {
                    recipientTextarea.addClass('is-valid');
                }
                /*****************
                 * End validations
                 ****************/      

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('resend-license/request') ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if (response.success) {
                                toastType = 'success';
                                resetForm(form);
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
                }
            });	
        });
    </script>
<?= $this->endSection() //End section('scripts')?>