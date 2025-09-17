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
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <?php if ($status === 'success'): ?>
                        <!-- Success Message -->
                        <div class="mb-4">
                            <i class="uil uil-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="mb-4">Thank You!</h2>
                        <p class="lead mb-4">
                            Your subscription has been successfully activated.
                        </p>
                        <div class="subscription-details mb-4">
                            <h5>Subscription Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Package</th>
                                    <td><?= esc($package['package_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Pages.Start_Date') ?></th>
                                    <td><?= formatDate($subscription['start_time'], $myConfig) ?></td>
                                </tr>
                                <tr>
                                    <th>Next Billing</th>
                                    <td><?= formatDate($subscription['next_billing_time'], $myConfig) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="<?= base_url('/') ?>" class="btn btn-primary">
                                Go to Dashboard
                            </a>
                            <a href="<?= base_url('subscription/details/' . $subscription['id']) ?>" class="btn btn-info">
                                View Subscription Details
                            </a>
                        </div>

                    <?php elseif ($status === 'cancelled'): ?>
                        <!-- Cancelled Message -->
                        <div class="mb-4">
                            <i class="uil uil-times-circle text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="mb-4">Subscription Cancelled</h2>
                        <p class="lead mb-4">
                            You have cancelled the subscription process.
                        </p>
                        <div class="text-center">
                            <a href="<?= base_url('/') ?>" class="btn btn-primary">
                                Return to Dashboard
                            </a>
                            <a href="<?= base_url('subscription/packages') ?>" class="btn btn-info">
                                View Available Packages
                            </a>
                        </div>

                    <?php else: ?>
                        <!-- Error Message -->
                        <div class="mb-4">
                            <i class="uil uil-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="mb-4">Oops! Something went wrong</h2>
                        <p class="lead mb-4">
                            <?= esc($error_message ?? 'We encountered an error while processing your subscription.') ?>
                        </p>
                        <div class="alert alert-info">
                            <h5><i class="icon uil uil-info-circle"></i> What should you do?</h5>
                            <ul class="mb-0 text-left">
                                <li>Check if your payment was processed by logging into your <?= $subscription['payment_method'] ?? 'selected payment method' ?> account</li>
                                <li>Contact our support team if you need assistance</li>
                                <li>Try subscribing again in a few minutes</li>
                            </ul>
                        </div>
                        <div class="text-center">
                            <a href="<?= base_url('/') ?>" class="btn btn-primary">
                                Return to Dashboard
                            </a>
                            <a href="<?= base_url('contact') ?>" class="btn btn-info">
                                Contact Support
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($status === 'success'): ?>
            <!-- Additional Information -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">What's Next?</h5>
                    <ul class="list-unstyled">
                        <li class="media mb-3">
                            <i class="uil uil-check-circle text-success mr-3 mt-1" style="font-size: 1.5rem;"></i>
                            <div class="media-body">
                                <h6 class="mt-0 mb-1">Access Your Features</h6>
                                Start using all the features included in your subscription package.
                            </div>
                        </li>
                        <li class="media mb-3">
                            <i class="uil uil-calendar-alt text-info mr-3 mt-1" style="font-size: 1.5rem;"></i>
                            <div class="media-body">
                                <h6 class="mt-0 mb-1">Automatic Renewal</h6>
                                Your subscription will automatically renew on <?= formatDate($subscription['next_billing_time'], $myConfig) ?>.
                            </div>
                        </li>
                        <li class="media">
                            <i class="uil uil-cog text-primary mr-3 mt-1" style="font-size: 1.5rem;"></i>
                            <div class="media-body">
                                <h6 class="mt-0 mb-1">Manage Your Subscription</h6>
                                You can manage your subscription settings from your account dashboard.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>

<?= $this->endSection() //End section('scripts')?>
