<!-- Payment Method Popup Start -->
<div class="modal fade" id="paymentMethod" tabindex="-1" aria-labelledby="paymentMethod-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded shadow border-0">
            <div class="modal-body py-5">
                <?php if(isset($subscription) && $subscription['subscription_status'] === 'active') : ?>
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
                <?php endif; ?>
                <div class="text-center">
                    <div class="icon d-flex align-items-center justify-content-center bg-soft-success rounded-circle mx-auto" style="height: 95px; width:95px;">
                        <h1 class="mb-0"><i class="uil uil-transaction align-middle"></i></h1>
                    </div>
                    <div class="mt-4">
                        <h5>Please select your preferred payment method:</h5>
                        
                        <?php
                        if (array_key_exists("payment_methods", $sideBarMenu) && count($sideBarMenu['payment_methods']) !== 0) :
                            foreach ($sideBarMenu['payment_methods'] as $paymentOption) :
                                try {
                                    $altText = htmlspecialchars($paymentOption['title']);
                                    $srcImg = $paymentOption['logo'];

                                    // Validate file path
                                    if (!$srcImg) {
                                        log_message('warning', "[Dashboard Subscription-to-Package-JS] No logo path provided for payment method: {$altText}");
                                        echo '<a href="javascript:void(0)" class="btn btn-outline-primary">' . $altText . '</a>';
                                        continue;
                                    }

                                    // Check if file exists with absolute path
                                    if (!file_exists($srcImg)) {
                                        log_message('error', "[Dashboard Subscription-to-Package-JS] Logo file not found: {$srcImg}");
                                        echo '<a href="javascript:void(0)" class="btn btn-outline-primary">' . $altText . '</a>';
                                        continue;
                                    }

                                    // Validate file type
                                    $fileInfo = pathinfo($srcImg);
                                    $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
                                    $fileExtension = strtolower($fileInfo['extension']);

                                    if (!in_array($fileExtension, $allowedExtensions)) {
                                        log_message('warning', "[Dashboard Subscription-to-Package-JS] Unsupported file type for logo: {$srcImg}");
                                        echo '<a href="javascript:void(0)" class="btn btn-outline-primary">' . $altText . '</a>';
                                        continue;
                                    }

                                    // Read file contents
                                    $imgContent = file_get_contents($srcImg);
                                    if ($imgContent === false) {
                                        log_message('error', "[Dashboard Subscription-to-Package-JS] Unable to read logo file: {$srcImg}");
                                        echo '<a href="javascript:void(0)" class="btn btn-outline-primary">' . $altText . '</a>';
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

                                    // Output image with alt text and optional class
                                    echo '<a href="javascript:void(0)" class="btn btn-light payment-method" data-method="' . $altText . '">
                                            <img src="' . $base64Img . '" alt="' . $altText . '" style="max-width: 120px;" class="avatar avatar-ex-medium">
                                        </a>';

                                } catch (\Exception $e) {
                                    log_message('critical', "[Dashboard Subscription-to-Package-JS] Error processing payment method logo: " . $e->getMessage());
                                    echo '<a href="javascript:void(0)" class="btn btn-outline-primary payment-method" data-method="' . $altText . '">' . $altText . '</a>';
                                }
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Payment Method Popup End -->

<?php if ($defaultPackage && isset($defaultPackage['id'])): ?>
    <!-- Trial Subscription Modal -->
    <div class="modal fade" id="trialModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Trial_Subscription') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <h5><i class="icon uil uil-exclamation-triangle"></i> <?= lang('Pages.Warning_exclamation') ?></h5>
                        <p><?= lang('Pages.confirmation_subscribe_to_trial_package') ?></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="claim-trial-package">
                        <i class="uil uil-check"></i> <?= lang('Pages.Confirm') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        // Handle billing toggle
        $('.btn-group .btn').click(function() {
            $('.btn-group .btn').removeClass('active');
            $(this).addClass('active');
            
            const billing = $(this).data('billing');
            $('.package-group').hide();
            $('.package-group[data-duration="' + billing + '"]').show();

            // Update Features Comparison table
            updateFeaturesComparison(billing);
        });

        // Initially show the first billing duration's packages
        const initialBilling = $('.btn-group .btn.active').data('billing');
        $('.package-group[data-duration="' + initialBilling + '"]').show();
        updateFeaturesComparison(initialBilling);

        // Handle subscription button click
        $('.subscribe-btn').click(function() {
            const packageId = $(this).data('package-id');
            $('.payment-method').attr('data-package-id', packageId);
            
            // Store package details for later use
            const packageName = $(this).closest('.card').find('.card-title').text();
            const packagePrice = $(this).closest('.card').find('.pricing-value').text();
            $('#paymentMethod').data('package-name', packageName);
            $('#paymentMethod').data('package-price', packagePrice);
        });

        // Handle payment method button click
        $('.payment-method').click(function() {
            const packageId = $(this).data('package-id');
            const method = $(this).data('method');
            const button = $(this);

            // Show loading state
            button.addClass('disabled').prop('disabled', true);
            const originalContent = button.html();
            button.html('Processing... <i class="mdi mdi-loading mdi-spin mb-0 align-middle"></i></span>');

            // Close the modal
            // $('#paymentMethod').modal('hide');

            // Make the form submit
            const form = $('<form>')
                .attr('method', 'POST')
                .attr('action', '<?= base_url('subscription/create') ?>')
                .append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'package_id')
                    .val(packageId)
                )
                .append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'payment_method')
                    .val(method)
                );

            $('body').append(form);
            form.submit();
        });

        function updateFeaturesComparison(billing) {
            // Hide all columns
            $('.features-comparison th, .features-comparison td').hide();

            // Show the first column (Feature names)
            $('.features-comparison th:first-child, .features-comparison td:first-child').show();

            // Show columns for the selected billing duration
            $('.features-comparison th[data-duration="' + billing + '"], .features-comparison td[data-duration="' + billing + '"]').show();
        }
        
        <?php if ($defaultPackage && isset($defaultPackage['id'])): ?>
            /***************************
             * Handle Claim Trial package
             ***************************/
            $('#claim-trial-package').on('click', function (e) {

                var submitButton = $(this);
                enableLoadingEffect(submitButton)

                // Proceed with AJAX request if user confirms
                $.ajax({
                    url: '<?= base_url('subscription/trial') ?>',
                    method: 'POST',
                    success: function (response) {
                        showToast(response.success? 'success' : 'danger', response.msg);

                        if(response.redirect) {
                            delayedRedirect(response.redirect);
                        }

                        if(!response.success) {
                            disableLoadingEffect(submitButton);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        disableLoadingEffect(submitButton);
                    }
                });

            });
        <?php endif; ?>
    });

    function formatModuleName(module) {
        return module.split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    function formatModuleSettings(settings) {
        if (typeof settings === 'object') {
            return Object.entries(settings)
                .map(([key, value]) => `${formatModuleName(key)}: ${value}`)
                .join(', ');
        }
        return '';
    }
</script>
