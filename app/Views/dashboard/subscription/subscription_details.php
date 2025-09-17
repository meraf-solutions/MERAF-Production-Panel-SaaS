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
<?= $this->endSection() //End section('head')?>

<?= $this->section('content') ?>
    <div class="row mt-4">
        <?php if(!empty($subscription) && !empty($currentPackageFeatures)) : ?>
            <div class="col-md-8 mb-3">
                <!-- Subscription Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= lang('Pages.Subscription_Details') ?></h3>

                        <?php if ($subscription['subscription_status'] === 'active'): ?>
                        <div class="card-tools">
                            <span class="badge bg-success me-2 mt-2"><?= lang('Pages.Active') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><?= lang('Pages.Package_Information') ?></h5>
                                <table class="table align-middle">
                                    <tr>
                                        <th><?= lang('Pages.Package_Name') ?></th>
                                        <td><?= esc($currentPackageFeatures['package_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Price') ?></th>
                                        <td>
                                            <?php if($currentPackageFeatures['price'] !== '0.00') : ?>
                                                <?= $myConfig['packageCurrency'] ?> <?= number_format($currentPackageFeatures['price'], 2) ?>
                                            <?php else : ?>
                                                <?= lang('Pages.Free') ?>
                                            <?php endif; ?>

                                            <small class="d-block text-muted">
                                                / <?= $currentPackageFeatures['validity'] != 1 ? esc($currentPackageFeatures['validity']) . ' ' : '' ?><?= esc($currentPackageFeatures['validity_duration']) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Status') ?></th>
                                        <td>
                                            <span class="badge bg-<?= getStatusBadgeClass($subscription['subscription_status']) ?> me-2 mt-2">
                                                <?= lang('Pages.' . $subscription['subscription_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5><?= lang('Pages.Billing_Information') ?></h5>
                                <table class="table align-middle">
                                    <tr>
                                        <th><?= lang('Pages.Start_Date') ?></th>
                                        <td><?= formatDate($subscription['start_date'], $myConfig) ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Next_Billing') ?></th>
                                        <td>
                                            <?php 
                                            if($nextBillingDate) {
                                                $datePart = preg_replace('/\s*\([^)]*\)/', '', $nextBillingDate); // Remove the (Qatar) part
                                                $nextBillingDate = (strtotime($datePart) === false) 
                                                    ? '<span class="badge bg-' . getStatusSoftBadgeClass($nextBillingDate) . ' me-2 mt-2">' 
                                                        . lang('Pages.' . $nextBillingDate) . '</span>'
                                                    : $nextBillingDate;
                                                echo $nextBillingDate;
                                            }
                                            else {
                                                echo '<span class="badge bg-soft-dark textme-2 mt-2">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Last_Payment') ?></th>
                                        <td>
                                            <?= formatDate($subscription['last_payment_date'] ?? $subscription['start_date'], $myConfig) ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if ($subscription['subscription_status'] === 'active'): ?>
                            <?php if ($subscription['is_reactivated'] === 'yes'): ?>
                                <div class="alert alert-info mt-4">
                                    <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.Reactivated_Subscription') ?></h5>
                                    <p><?= lang('Pages.This_subscription_reactivated_remaining_days', ['daysRemaining' => $daysRemaining ?? 0]) ?></p>
                                </div>

                                <div class="alert alert-primary mt-4">
                                    <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.New_Subscription') ?></h5>
                                    <p class="mb-0">
                                        <?= lang('Pages.Reactivated_new_package_notice') ?>
                                        <a href="<?= base_url('subscription/packages') ?>" class="btn btn-secondary btn-sm ml-2">
                                            <i class="uil uil-box"></i> <?= lang('Pages.View_Packages') ?>
                                        </a>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="uil uil-times"></i> <?= lang('Pages.Cancel_Subscription') ?>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if ($subscription['transaction_token']): ?>
                                <div class="alert alert-warning mt-4">
                                <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.Complete_Your_Due_Bill') ?></h5>

                                <p class="mb-0">
                                    <?= lang('Pages.Complete_Your_Due_Bill_note', ['due_date' => $nextBillingDate]) ?>
                                    <?= $subscription['payment_method'] !== 'Offline' ? lang('Pages.Note_expiration_of_payment_link', ['linkExpirationDate' => formatDate($linkExpirationDate, $myConfig)]) : '' ?>
                                    <a href="<?= $subscription['transaction_token'] ?>" class="btn btn-secondary btn-sm ml-2">
                                        <i class="uil uil-save"></i> <?= lang('Pages.Complete_Payment') ?>
                                    </a>
                                </p>
                            </div>
                            <?php endif; ?>
                        <?php elseif ($subscription['subscription_status'] === 'cancelled'): ?>
                            <div class="alert alert-info mt-4">
                                <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.Subscription_Cancelled') ?></h5>
                                <p class="mb-0">
                                    <?= lang('Pages.subscription_has_been_cancelled') ?>
                                    <?= $subscription['payment_status'] === 'pending' ? lang('Pages.unique_payment_expired_notice') : '' ?>
                                    <?= lang('Pages.Subscribe_anytime') ?>
                                    <a href="<?= base_url('subscription/packages') ?>" class="btn btn-secondary btn-sm ml-2">
                                        <i class="uil uil-box"></i> <?= lang('Pages.View_Packages') ?>
                                    </a>
                                </p>
                            </div>

                            <?php if (isset($daysRemaining) && $daysRemaining > 0): ?>
                                <div class="alert alert-primary mt-4">
                                    <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.Reactivate_Subscription') ?></h5>
                                    <p>
                                        <?= lang('Pages.Remaining_days_notice', ['daysRemaining' => $daysRemaining]) ?>
                                    </p>
                                    <p class="mb-0">
                                        <?= lang('Pages.Change_mind_to_reactivate') ?>
                                        <a href="<?= base_url('subscription/reactivate/' . $subscription['id']) ?>" class="btn btn-secondary btn-sm ml-2">
                                            <i class="uil uil-power"></i> <?= lang('Pages.Reactivate_Subscription') ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>

                        <?php elseif ($subscription['subscription_status'] === 'pending' && $subscription['transaction_token']): ?>
                            <?php
                            $dateTimeParser = new CodeIgniter\I18n\Time;
                            $linkExpirationDate = $dateTimeParser::parse($subscription['start_date'])->addHours(3);
                            ?>
                            <div class="alert alert-info mt-4">
                                <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.Complete_Your_Subscription') ?></h5>

                                <p class="mb-0">
                                    <?= lang('Pages.Ensure_uninterrupted_access') ?>
                                    <?= $subscription['payment_method'] !== 'Offline' ? lang('Pages.Note_expiration_of_payment_link', ['linkExpirationDate' => formatDate($linkExpirationDate, $myConfig)]) : '' ?>
                                    <a href="<?= $subscription['transaction_token'] ?>" class="btn btn-secondary btn-sm ml-2">
                                        <i class="uil uil-save"></i> <?= lang('Pages.Complete_Payment') ?>
                                    </a>
                                </p>
                            </div>
                        <?php elseif ($subscription['subscription_status'] === 'expired'): ?>
                            <div class="alert alert-secondary text-light mt-4">
                                <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.Subscription_Expired') ?></h5>
                                <p class="mb-0">
                                    <?= lang('Pages.subscription_has_expired') ?>
                                    <a href="<?= base_url('subscription/packages') ?>" class="btn btn-primary btn-sm ml-2">
                                        <i class="uil uil-box"></i> <?= lang('Pages.View_Packages') ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <!-- Payment History Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= lang('Pages.Payment_History') ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($payments)): ?>
                        <p class="text-muted p-3"><?= lang('Pages.No_payment_history_available') ?></p>
                        <?php else: ?>
                        <div class="table-responsive mb-3">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?= lang('Pages.Date') ?></th>
                                        <th><?= lang('Pages.Status') ?></th>
                                        <th><?= lang('Pages.Amount') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= formatDate($payment['payment_date'], $myConfig) ?></td>
                                        <td><?= lang('Pages.' . $payment['payment_status']) ?></td>
                                        <td>
                                            <?= number_format($payment['amount'], 2) ?>
                                            <?= esc($payment['currency']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="<?= base_url('subscription/payment-history/' . $payment['subscription_id']) ?>" class="btn btn-primary btn-block btn-sm">
                            <i class="uil uil-history"></i> View All</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Support Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><?= lang('Pages.Need_Help_question') ?></h3>
                    </div>
                    <div class="card-body">
                        <p><?= lang('Pages.Need_Help_text') ?></p>
                        <a href="<?= base_url('contact') ?>" class="btn btn-info btn-block">
                            <i class="uil uil-envelope"></i> <?= lang('Pages.Contact_Support') ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="shadow p-3 mb-3 bg-body rounded text-center">
                <h6 class="mb-0 text-info">
                    "<?= lang('Pages.No_subscription_notice') ?>"
                </h6>
            </div>

            <!-- Pricing Section -->
            <?php include_once APPPATH . 'Views/dashboard/subscription/package_pricing.php'; ?>

            <!-- FAQ Section -->
            <?php include_once APPPATH . 'Views/dashboard/subscription/package_faq.php'; ?>
        <?php endif; ?>
    </div>

    <!-- Features Card -->
    <?php if(!empty($subscription) && !empty($currentPackageFeatures)) : ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><?= lang('Pages.Package_Features') ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered features-comparison">
                                <thead>
                                    <tr>
                                        <th><?= lang('Pages.Feature') ?></th>
                                        <?php foreach ($packages as $package): ?>
                                            <th class="text-center <?= $currentPackageFeatures['id'] === $package['id'] ? 'bg-dark text-light' : ''?>" data-package-id="<?= $package['id'] ?>" data-duration="<?= $package['validity_duration'] ?>"><?= esc($package['package_name']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong><?= lang('Pages.Price') ?></strong></td>
                                        <?php foreach ($packages as $package): ?>
                                            <td class="text-center <?= $currentPackageFeatures['id'] === $package['id'] ? 'bg-dark text-light' : ''?>" data-package-id="<?= $package['id'] ?>" data-duration="<?= $package['validity_duration'] ?>">
                                                <?php if($package['price'] !== '0.00') : ?>
                                                    <?= $myConfig['packageCurrency'] ?> <?= number_format($package['price'], 2) ?>
                                                <?php else : ?>
                                                    <?= lang('Pages.Free') ?>
                                                <?php endif; ?>
                                                <small class="d-block text-muted">
                                                    / <?= $package['validity'] != 1 ? esc($package['validity']) . ' ' : '' ?><?= lang('Pages.' . $package['validity_duration']) ?>
                                                </small>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php
                                    // Get all unique features across packages
                                    $allModules = [];
                                    foreach ($packages as $package) {
                                        $modules = json_decode($package['package_modules'], true) ?? [];
                                        $allModules = array_merge($allModules, array_keys($modules));
                                    }
                                    $allModules = array_unique($allModules);                            
                                    
                                    foreach ($allModules as $module):
                                    ?>
                                    <tr>
                                        <td><?= lang('Pages.' . $module) ?></td>
                                        <?php foreach ($packages as $package): ?>
                                            <td class="text-center <?= $currentPackageFeatures['id'] === $package['id'] ? 'bg-dark text-light' : ''?>" data-package-id="<?= $package['id'] ?>" data-duration="<?= $package['validity_duration'] ?>">
                                                <?php
                                                $modules = json_decode($package['package_modules'], true) ?? [];
                                                foreach($modules[$module] as $featureName => $value) :
                                                    $measurementUnit = $packageModules[array_search($featureName, array_column($packageModules, 'module_name'))]['measurement_unit'] ?? null;
                                                    $measurementUnit = json_decode($measurementUnit, true);
                                                    
                                                    if ($value['enabled'] === 'true') {
                                                        echo '<i class="uil uil-check-circle align-middle text-success"></i>';
                                                    } else {
                                                        echo '<i class="uil uil-times-circle align-middle text-danger"></i>';
                                                    }
                                                    echo ' ' . lang('Pages.' . $featureName);
                                                    
                                                    if ($measurementUnit['unit'] !== 'Enabled') {
                                                        echo ': ';
                                                        if ($value['value'] !== 'true' && $value['value'] !== 'false') {
                                                            echo $value['value'] . ' ' . ($measurementUnit['unit'] ?? '');
                                                        }
                                                    }
                                                    echo '<br>';
                                                endforeach;
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Description -->
        <?php include_once APPPATH . 'Views/dashboard/subscription/features_description.php'; ?>
    <?php endif; ?>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <?php if(!empty($subscription) && !empty($currentPackageFeatures)) : ?>
        <!-- Cancel Subscription Modal -->
        <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= lang('Pages.Cancel_Subscription') ?></h5>
                        <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <h5><i class="icon uil uil-exclamation-triangle"></i> <?= lang('Pages.Warning_exclamation') ?></h5>
                            <p><?= lang('Pages.Warning_to_cancel_subscription') ?></p>
                            <ul>
                                <li><?= lang('Pages.Your_subscription_will_be_cancelled_immediately') ?></li>
                                <li><?= lang('Pages.You_will_lose_access_to_premium_features') ?></li>
                                <li><?= lang('Pages.No_refunds_will_be_issued_for_the_current_billing_period') ?></li>
                                <li><?= lang('Pages.Unable_to_renew_the_cancelled_subscription') ?></li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <label for="cancelReason"><?= lang('Pages.Reason_for_cancellation_optional') ?></label>
                            <textarea class="form-control" id="cancelReason" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Pages.Keep_Subscription') ?></button>
                        <button type="button" class="btn btn-danger" id="confirmCancel">
                            <i class="uil uil-times"></i> <?= lang('Pages.Cancel_Subscription') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <?php if(!empty($subscription) && !empty($currentPackageFeatures)) : ?>
        <script type="text/javascript">
            $(document).ready(function() {
                // Handle subscription cancellation
                $('#confirmCancel').click(function() {
                    const reason = $('#cancelReason').val();
                    const confirmButton = $(this);

                    enableLoadingEffect(confirmButton);
                    
                    $.ajax({
                        url: '<?= base_url('subscription/cancel-subscription') ?>',
                        method: 'POST',
                        data: { reason: reason },
                        success: function(response) {
                            if (response.success) {
                                delayedRedirect(location);
                            } else {
                                showToast('danger', '<?= lang('Pages.Failed_to_cancel_subscription') ?>');
                                disableLoadingEffect(confirmButton);
                            }
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
                            showToast('danger', '<?= lang('Pages.Failed_to_process_request') ?>');
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                            disableLoadingEffect(confirmButton);
                        }
                    });
                });

                <?php if (isset($daysRemaining) && $daysRemaining > 0): ?>
                // Handle reactivate subscription
                $('#reactivate-subscription').on('click', function(e) {
                    e.preventDefault();
                    const reactivateButton = $(this);

                    enableLoadingEffect(reactivateButton);

                    var subscriptionId = <?= $subscription['id'] ?>;
                    
                    $.ajax({
                        url: '<?= base_url('subscription/reactivate/') ?>' + subscriptionId,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                delayedRedirect(location);
                            } else {
                                showToast('danger', response.message || '<?= lang('Pages.Failed_to_reactivate_subscription') ?>');
                            }

                            disableLoadingEffect(reactivateButton);
                        },
                        error: function(xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                            
                            disableLoadingEffect(reactivateButton);
                        }
                    });
                });
                <?php endif; ?>
            });

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'active':
                        return 'success';
                    case 'suspended':
                        return 'warning';
                    case 'cancelled':
                        return 'danger';
                    case 'expired':
                        return 'secondary';
					case 'pending':
                        return 'info';
                    default:
                        return 'info';
                }
            }

            function getModuleIcon(module) {
                // Add your module icon mappings here
                const icons = {
                    'api_access': 'code',
                    'storage': 'database',
                    'users': 'users',
                    'reports': 'chart-bar',
                    'support': 'headset',
                    // Add more mappings as needed
                };
                return icons[module] || 'cube';
            }

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
    <?php else : ?>
        <?php include_once APPPATH . 'Views/includes/dashboard/subscribe-to-package-js.php'; ?>
    <?php endif; ?>
<?= $this->endSection() //End section('scripts')?>