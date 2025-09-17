<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0">PayPal Settings</h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>
                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Admin') ?></li>
                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Payment_Options') ?></li>
                <li class="breadcrumb-item text-capitalize active"><?= lang('Pages.PayPal_Settings') ?></li>
            </ul>
        </nav>
    </div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">PayPal Integration Settings</h3>
                    </div>
                    <div class="card-body">
                        <!-- Response Messages -->
                        <div class="col-md-12 col-lg-6 mx-auto text-center" id="responseMessage" style=""></div>
                        
                        <!-- PayPal Configuration Warning -->
                        <?php if (!$paypalConfigStatus->isConfigured): ?>
                            <div class="alert alert-warning" role="alert">
                                <h4 class="alert-heading"><i class="uil uil-exclamation-triangle"></i> PayPal Not Fully Configured</h4>
                                <p>Your PayPal integration is not fully configured. Please complete the settings below to enable PayPal payments.</p>
                                <hr>
                                <p class="mb-0">
                                    Environment: <?= $paypalConfigStatus->environment ?><br>
                                    Client ID: <?= $paypalConfigStatus->clientIdSet ? 'Set' : 'Not Set' ?><br>
                                    Client Secret: <?= $paypalConfigStatus->clientSecretSet ? 'Set' : 'Not Set' ?><br>
                                    Webhook ID: <?= $paypalConfigStatus->webhookIdSet ? 'Set' : 'Not Set' ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?= form_open('payment-options/paypal-settings/save', ['id' => 'paypalSettingsForm']) ?>
                            <?= csrf_field() ?>
                            <!-- Environment Selection -->
                            <div class="col-12 mb-3">
                                <div class="card border-0 p-4">                                                                        

                                        <label class="card-title h4">Environment</label>
                                    <div class="form-icon position-relative">
                                        <i data-feather="layout" class="fea icon-sm icons"></i>
                                        <select class="form-select form-control ps-5" id="environment" name="PAYPAL_MODE">
                                            <option value="sandbox" <?= $myConfig['PAYPAL_MODE'] === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                                            <option value="production" <?= $myConfig['PAYPAL_MODE'] === 'production' ? 'selected' : '' ?>>Production (Live)</option>
                                        </select>
                                        <small class="text-info">
                                            Select 'Sandbox' for testing and 'Production' for live transactions.
                                        </small>
                                    </div>
                                </div>
                            </div><!--end col-->

                            <!-- Sandbox Credentials -->
                            <div class="card mb-3" id="sandboxCredentials">
                                <div class="card-header">
                                    <h4 class="card-title">Sandbox Credentials</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="sandbox_client_id">Client ID</label>
                                        <input type="text" class="form-control" id="sandbox_client_id" 
                                            name="PAYPAL_SANDBOX_CLIENT_ID" value="<?= esc($myConfig['PAYPAL_SANDBOX_CLIENT_ID']) ?>">
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Enter_a_valid_value') ?>
                                        </div> 
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="sandbox_client_secret">Client Secret</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="sandbox_client_secret" 
                                                name="PAYPAL_SANDBOX_CLIENT_SECRET" value="<?= esc($myConfig['PAYPAL_SANDBOX_CLIENT_SECRET']) ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary toggle-password">
                                                    <i class="uil uil-eye"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.Enter_a_valid_value') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="sandbox_webhook_id">Webhook ID</label>
                                        <input type="text" class="form-control" id="sandbox_webhook_id" 
                                            name="PAYPAL_SANDBOX_WEBHOOK_ID" value="<?= esc($myConfig['PAYPAL_SANDBOX_WEBHOOK_ID']) ?>">
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Enter_a_valid_value') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Production Credentials -->
                            <div class="card mb-3" id="productionCredentials">
                                <div class="card-header">
                                    <h4 class="card-title">Production Credentials</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="live_client_id">Client ID</label>
                                        <input type="text" class="form-control" id="live_client_id" 
                                            name="PAYPAL_LIVE_CLIENT_ID" value="<?= esc($myConfig['PAYPAL_LIVE_CLIENT_ID']) ?>">
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Enter_a_valid_value') ?>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="live_client_secret">Client Secret</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="live_client_secret" 
                                                name="PAYPAL_LIVE_CLIENT_SECRET" value="<?= esc($myConfig['PAYPAL_LIVE_CLIENT_SECRET']) ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary toggle-password">
                                                    <i class="uil uil-eye"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.Enter_a_valid_value') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="live_webhook_id">Webhook ID</label>
                                        <input type="text" class="form-control" id="live_webhook_id" 
                                            name="PAYPAL_LIVE_WEBHOOK_ID" value="<?= esc($myConfig['PAYPAL_LIVE_WEBHOOK_ID']) ?>">
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Enter_a_valid_value') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Webhook Settings -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4 class="card-title">Webhook Configuration</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>Webhook URL</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control copy-to-clipboard" value="<?= base_url('paypal/webhook') ?>" readonly>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary copy-to-clipboard" data-clipboard-text="<?= base_url('paypal/webhook') ?>" title="<?= lang('Pages.Click_to_copy') ?>">
                                                    <i class="uil uil-copy"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.Enter_a_valid_value') ?>
                                            </div>
                                        </div>
                                        <small class="text-info">
                                            Use this URL when setting up your webhook in the PayPal Developer Dashboard.
                                        </small>
                                    </div>

                                    <div class="alert alert-info">
                                        <h5><i class="uil uil-info"></i> Webhook Events</h5>
                                        <p>Configure your webhook to listen for the following events:</p>
                                        <ul class="mb-0">
                                            <li>BILLING.SUBSCRIPTION.CREATED</li>
                                            <li>BILLING.SUBSCRIPTION.ACTIVATED</li>
                                            <li>BILLING.SUBSCRIPTION.UPDATED</li>
                                            <li>BILLING.SUBSCRIPTION.CANCELLED</li>
                                            <li>BILLING.SUBSCRIPTION.SUSPENDED</li>
                                            <li>BILLING.SUBSCRIPTION.PAYMENT.FAILED</li>
                                            <li>PAYMENT.SALE.COMPLETED</li>
                                            <li>PAYMENT.SALE.DENIED</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group row">
                                <div class="col-md-auto mb-2 mb-md-0">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="uil uil-save"></i> Save Settings
                                    </button>
                                </div>
                                <div class="col-md-auto">
                                    <button type="button" class="btn btn-info w-100" id="testConnection">
                                        <i class="uil uil-plug"></i> Test Connection
                                    </button>
                                </div>
                            </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            // Show response message
            function showResponse(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                
                const html = `
                    <div class="alert ${alertClass} alert-dismissible fade show d-inline-block text-center" role="alert">
                        ${message} 
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                $('#responseMessage').html(html);
                
                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }

            // Toggle password visibility
            $('.toggle-password').click(function() {
                const input = $(this).closest('.input-group').find('input');
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('uil-eye').addClass('uil-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('uil-eye-slash').addClass('uil-eye');
                }
            });

            // Test connection
            $('#testConnection').click(function() {
                const button = $(this);
                const originalHtml = button.html();
                // button.prop('disabled', true).html('Testing... <i class="mdi mdi-loading mdi-spin mb-0 align-middle"></i>');

                const environment = $('#environment').val();
                const clientId = environment === 'sandbox' ? 
                    $('#sandbox_client_id').val() : $('#live_client_id').val();
                const clientSecret = environment === 'sandbox' ? 
                    $('#sandbox_client_secret').val() : $('#live_client_secret').val();

                enableLoadingEffect(button);

                // Test the connection
                $.ajax({
                    url: '<?= site_url('payment-options/paypal-settings/test-connection') ?>',
                    method: 'POST',
                    data: {
                        environment: environment,
                        client_id: clientId,
                        client_secret: clientSecret,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                        } else {
                            toastType = 'danger';
                        }

                        showToast(toastType, response.message);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    },
                    complete: function () {
                        disableLoadingEffect(button);
                    }
                });
            });

            // Form validation and submission
            $('#paypalSettingsForm').submit(function(e) {
                e.preventDefault();

                const environment = $('#environment').val();
                let valid = true;
                let message = '';
                const button = $('button[type="submit"]');

                enableLoadingEffect(button);

                if (environment === 'sandbox') {
                    if (!$('#sandbox_client_id').val()) {
                        showToast('danger', 'Sandbox Client ID is required');
                        $('#sandbox_client_id').addClass('is-invalid');
                        valid = false;
                    } 

                    if (!$('#sandbox_client_secret').val()) {
                        showToast('danger', 'Sandbox Client Secret is required');
                        $('#sandbox_client_secret').addClass('is-invalid');
                        valid = false;
                    }

                    if (!$('#sandbox_webhook_id').val()) {
                        showToast('danger', 'Sandbox Webhook ID is required');
                        $('#sandbox_webhook_id').addClass('is-invalid');
                        valid = false;
                    }
                } else {
                    if (!$('#live_client_id').val()) {
                        showToast('danger', 'Production Client ID is required');
                        $('#live_client_id').addClass('is-invalid');
                        valid = false;
                    }

                    if (!$('#live_client_secret').val()) {
                        showToast('danger', 'Production Client Secret is required');
                        $('#live_client_secret').addClass('is-invalid');
                        valid = false;
                    }

                    if (!$('#live_webhook_id').val()) {
                        showToast('danger', 'Production Webhook ID is required');
                        $('#live_webhook_id').addClass('is-invalid');
                        valid = false;
                    }
                }

                if (!valid) {
                    disableLoadingEffect(button);
                    return;
                }

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            delayedRedirect(location);
                        } else {
                            toastType = 'danger';
                        }

                        showToast(toastType, response.message);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    },
                    complete: function () {
                        disableLoadingEffect(button);
                    }
                });
            });

            // Toggle environment credentials
            function toggleCredentials() {
                const environment = $('#environment').val();
                if (environment === 'sandbox') {
                    $('#sandboxCredentials').show();
                    $('#productionCredentials').hide();
                } else {
                    $('#sandboxCredentials').hide();
                    $('#productionCredentials').show();
                }
            }

            $('#environment').change(toggleCredentials);
            toggleCredentials(); // Initial state
        });
    </script>
<?= $this->endSection() ?>
