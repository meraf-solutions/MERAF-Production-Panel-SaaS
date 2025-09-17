<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.Offline_Payment') ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>
                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Payment_Options') ?></li>
                <li class="breadcrumb-item text-capitalize active"><?= lang('Pages.Offline_Payment') ?></li>
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
                        <h3 class="card-title"><?= lang('Pages.Offline_Payment_Reference') ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($paymentInstructions)): ?>
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> <?= lang('Pages.Offline_Payment_Instructions') ?></h5>
                                <p><?= nl2br(esc($paymentInstructions)) ?></p>
                                <p><?= lang('Pages.Note_reference_form') ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($subscription)): ?>
                            <div class="alert bg-soft-info">
                                <h5><i class="icon fas fa-info"></i> <?= lang('Pages.Subscription_Details') ?></h5>

                                <div class="d-flex align-items-center">
                                    <i data-feather="package" class="fea icon-ex-md text-muted me-3"></i>
                                    <div class="flex-1">
                                        <h6 class="text-dark mb-0"><?= lang('Pages.Package') ?> :</h6>
                                        <a href="javascript:void(0)" class="text-muted"><?= esc($selectedPackage['package_name']) ?></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <i data-feather="credit-card" class="fea icon-ex-md text-muted me-3"></i>
                                    <div class="flex-1">
                                        <h6 class="text-dark mb-0"><?= lang('Pages.Amount') ?> :</h6>
                                        <a href="javascript:void(0)" class="text-muted"><?= esc($subscription->amount_paid) ?> <?= esc($subscription->currency) ?></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <i data-feather="calendar" class="fea icon-ex-md text-muted me-3"></i>
                                    <div class="flex-1">
                                        <h6 class="text-dark mb-0"><?= lang('Pages.Duration') ?> :</h6>
                                        <a href="javascript:void(0)" class="text-muted"><?= esc($subscription->billing_period) ?> <?= lang('Pages.'.$subscription->billing_cycle) ?></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <i data-feather="book-open" class="fea icon-ex-md text-muted me-3"></i>
                                    <div class="flex-1">
                                        <h6 class="text-dark mb-0"><?= lang('Pages.Payment_Reference_ID') ?> :</h6>
                                        <a href="javascript:void(0)" class="text-muted"><?= esc($subscription->payment_reference) ?></a>
                                    </div>
                                </div>
                                
                            </div>
                        <?php endif; ?>

                        <?= form_open(route_to('offline-payment-process'), ['id' => 'offlinePaymentForm']) ?>
                            <?= csrf_field() ?>
                            <?php if (isset($subscription)): ?>
                                <input type="hidden" name="subscription_reference" value="<?= esc($subscription->subscription_reference) ?>">
                            <?php endif; ?>
                            <div class="form-group mb-3">
                                <label for="reference_id"><?= lang('Pages.Completed_Transaction_Reference') ?></label>
                                <input type="text" class="form-control" id="reference_id" name="reference_id" required <?= $subscription->transaction_id !== 'PENDING_PAYMENT' ? 'value="'.$subscription->transaction_id.'" readonly' : '' ?>>
                                <small class="text-muted"><?= lang('Pages.Enter_Reference_ID') ?></small>
                            </div>
                            <div class="form-group">
                                <?php if($subscription->transaction_id === 'PENDING_PAYMENT') : ?>
                                    <button type="submit" class="btn btn-primary"><?= lang('Pages.Submit_Payment') ?></button>
                                <?php else : ?>
                                    <div class="alert alert-warning">
                                        <?= lang('Pages.transaction_reference_under_review') ?>
                                    </div>
                                <?php endif; ?>
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
            $('#offlinePaymentForm').submit(function(e) {
                e.preventDefault();
                // Add any client-side validation here if needed
                this.submit();
            });
        });
    </script>
<?= $this->endSection() ?>
