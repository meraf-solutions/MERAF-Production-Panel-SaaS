<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url('subscription/my-subscription') ?>"><?= lang('Pages.' . ucwords($section)) ?></a></li>
                
                <li class="breadcrumb-item text-capitalize active" aria-current="page"><?= lang('Pages.' . ucwords($subsection)) ?></li>
            </ul>
        </nav>
    </div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= lang('Pages.Payment_History') ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('subscription/my-subscription') ?>" class="btn btn-sm btn-secondary">
                            <i class="uil uil-arrow-left"></i> <?= lang('Pages.My_Subscription') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <p class="text-muted"><?= lang('Pages.No_payment_history_available') ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th><?= lang('Pages.Date') ?></th>
                                        <th><?= lang('Pages.Amount') ?></th>
                                        <th><?= lang('Pages.Status') ?></th>
                                        <th><?= lang('Pages.Transaction_ID') ?></th>
                                        <th><?= lang('Pages.Refund') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?= formatDate($payment['payment_date'], $myConfig) ?></td>
                                            <td>
                                                <?= number_format($payment['amount'], 2) ?> <?= esc($payment['currency']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getPaymentStatusBadgeClass($payment['payment_status']) ?>">
                                                    <?= lang('Pages.' . $payment['payment_status']) ?>
                                                </span>
                                            </td>
                                            <td><?= esc($payment['transaction_id']) ?></td>
                                            <td>
                                                <?php if ($payment['payment_status'] === 'refunded' || $payment['payment_status'] === 'partially_refunded'): ?>
                                                    <?= number_format($payment['refund_amount'], 2) ?> <?= esc($payment['refund_currency']) ?>
                                                    <br>
                                                    <small><?= formatDate($payment['refund_date'], $myConfig) ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <h5><?= lang('Pages.Total_Payments') ?>: <?= number_format($totalPayments, 2) ?> <?= esc($payments[0]['currency']) ?></h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    <?php
    function getPaymentStatusBadgeClass($status) {
        switch ($status) {
            case 'completed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            case 'refunded':
                return 'secondary text-light';
            case 'partially_refunded':
                return 'info';
            default:
                return 'info';
        }
    }
    ?>
</script>
<?= $this->endSection() ?>
