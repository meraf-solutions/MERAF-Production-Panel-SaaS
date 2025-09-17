<?= $this->extend('layouts/dashboard') ?>

<?php
if($userData->id !== 1) { header("Location:" . base_url());exit(); }
$savedLicense = isset($registeredLicense['license_key']) ? $registeredLicense['license_key']  : NULL;
if($savedLicense) {
    $formURL = base_url('app-settings/registration/deactivate');
    $formSubmitBtn = lang('Pages.Unregister');
}
else {
    $formURL = base_url('app-settings/registration/submit');
    $formSubmitBtn = lang('Pages.Activate');
}

$request = service('request');
if(null !== $request->getGet('license_key')) {
    $savedLicense = $request->getGet('license_key');
}
?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Admin') ?></li>

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
                <h4><?= lang('Pages.Production_Panel_Activation') ?></h4>                                                                       
                
                <form novalidate id="prod-panel-registration-form">
                    <div class="row g-3">
                        <div class="col-12" id="responseMsg"><?php echo !function_exists('writeJSONResponse') || (null !== $request->getGet('license_error')) ? '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">'.urldecode($request->getGet('license_error')).'</div>' : '' ?></div>
                        <div class="col-12">
                            <label for="purchasedLicenseKey" class="form-label"><?= lang('Pages.Purchased_License_Key') ?></label>
                            
                            <div class="input-group has-validation">
                                <input type="<?= $savedLicense && (null === $request->getGet('license_error')) ? 'password' : 'text'?>" class="form-control" id="purchasedLicenseKey" name="purchasedLicenseKey" value="<?= $savedLicense ?>" <?= $savedLicense && (null === $request->getGet('license_error')) ? 'readonly' : ''?> required <?php echo !function_exists('writeJSONResponse') ? 'disabled' : '' ?> >
                                <button class="btn btn-outline-secondary" id="submit-button" <?php echo !function_exists('writeJSONResponse') ? 'disabled' : '' ?> ><?= $formSubmitBtn ?></button>
                                <div class="invalid-feedback"><?= lang('Pages.enter_purchased_license_key_feedback') ?></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>                                                
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {

            /********************************
            // Handle the activation requests
            ********************************/
            $('#submit-button').on('click', function (e) {
                e.preventDefault();

                var form = $('#prod-panel-registration-form');
                var licenseInput = $('#purchasedLicenseKey');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // Start validations

                // Iterate over other license key field and validate
                if (licenseInput.val() === '') {
                    licenseInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);
                } else {
                    licenseInput.addClass('is-valid');
                }
                // End validations

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= $formURL ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success'; 
                            } else if (response.status == 2) {
                                toastType = 'info';
                            } else {
                                toastType = 'danger';
                            }
                            
                            showToast(toastType, response.msg);

                            <?php if(!$savedLicense) { ?>
                                delayedRedirect("<?= base_url() ?>");
                                licenseInput.prop('readonly', true).attr('type', 'password');
                                submitButton.prop('disabled', true);
                            <?php } else { ?>
                                delayedRedirect("<?= base_url('app-settings/registration') ?>");
                            <?php } ?>
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
                            showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                            disableLoadingEffect(submitButton);
                        }
                    });
                }
            });
        });
    </script>	

    <script type="text/javascript">
        <?php
        function isMobile() {
            return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
        }
        
        if(!isMobile()){ ?>
            // Toggle the sidebar upon page load
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("close-sidebar").click();
            });
        <?php } ?>
    </script>   
<?= $this->endSection() //End section('scripts')?>