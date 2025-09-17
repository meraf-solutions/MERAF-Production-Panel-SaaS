<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('head') ?>
<?= $this->endSection() //End section('head')?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.Offline_Payment') ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>
                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Admin') ?></li>
                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Payment_Options') ?></li>
                <li class="breadcrumb-item text-capitalize active"><?= lang('Pages.Offline_Payment') ?></li>
            </ul>
        </nav>
    </div>
<?= $this->endSection() //End section('heading')?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-12 mt-4">
            
            <!-- Nav Pills -->
            <ul class="nav nav-pills border-0 gap-3 d-flex" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="mx-auto btn btn-primary active" id="pills-payments-tab" data-bs-toggle="pill" data-bs-target="#pills-payments" type="button" role="tab" aria-controls="pills-payments" aria-selected="true" aria-label="Manage Offline Payments">Manage Offline Payments</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="mx-auto btn btn-secondary" id="pills-settings-tab" data-bs-toggle="pill" data-bs-target="#pills-settings" type="button" role="tab" aria-controls="pills-settings" aria-selected="false" aria-label="Offline Payment Settings">Offline Payment Settings</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent">
                <!-- Manage Offline Payments Content -->
                <div class="tab-pane fade show active" id="pills-payments" role="tabpanel" aria-labelledby="pills-payments-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?= lang('Pages.Offline_Payment_Management') ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($payments)): ?>
                                        <p><?= lang('Pages.No_Offline_Payments') ?></p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-center table-striped bg-white mb-0" id="offlinePaymentsTable">
                                                <thead>
                                                    <tr>
                                                        <th><?= lang('Pages.Subscription_ID') ?></th>
                                                        <th><?= lang('Pages.Reference_ID') ?></th>
                                                        <th><?= lang('Pages.User') ?></th>
                                                        <th><?= lang('Pages.Amount') ?></th>
                                                        <th><?= lang('Pages.Payment_Date') ?></th>
                                                        <th><?= lang('Pages.Status') ?></th>
                                                        <th><?= lang('Pages.Actions') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($payments as $payment): ?>
                                                        <tr>
                                                            <td><?= esc($payment['subscription_id']) ?></td>
                                                            <td><?= esc($payment['transaction_id']) ?></td>
                                                            <td><a href="<?= base_url('admin-options/user-manager/?s=' . esc($payment['user_email'])) ?>" class="text-primary"><?= esc($payment['username']) ?></a></td>
                                                            <td><?= esc($payment['amount']) ?> <?= esc($payment['currency']) ?></td>
                                                            <td><?= formatDate($payment['payment_date'], $myConfig) ?></td>
                                                            <td>
                                                                <span class="badge bg-<?= getPaymentStatusBadgeClass($payment['payment_status']) ?>"><?= lang('Pages.' . $payment['payment_status']) ?></span>
                                                            </td>
                                                            <td>
                                                                <select class="form-select status-select" data-payment-id="<?= esc($payment['id']) ?>">
                                                                    <option value="pending" <?= $payment['payment_status'] == 'pending' ? 'selected' : '' ?>><?= lang('Pages.Pending') ?></option>
                                                                    <option value="completed" <?= $payment['payment_status'] == 'completed' ? 'selected' : '' ?>><?= lang('Pages.Completed') ?></option>
                                                                    <option value="failed" <?= $payment['payment_status'] == 'failed' ? 'selected' : '' ?>><?= lang('Pages.Failed') ?></option>
                                                                    <option value="refunded" <?= $payment['payment_status'] == 'refunded' ? 'selected' : '' ?>><?= lang('Pages.Refunded') ?></option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Offline Payment Settings Content -->
                <div class="tab-pane fade" id="pills-settings" role="tabpanel" aria-labelledby="pills-settings-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Offline Payment Settings</h5>
                                </div>
                                <div class="card-body">
                                    <?= form_open(route_to('offline-settings-save'), ['id' => 'offlineSettingsForm']) ?>
                                        <?= csrf_field() ?>
                                        <div class="form-group mb-3">
                                            <label for="payment_instructions"><?= lang('Pages.Payment_Instructions') ?></label>
                                            <textarea class="form-control" id="payment_instructions" name="payment_instructions" rows="5" required><?= esc($myConfig['OFFLINE_PAYMENT_INSTRUCTIONS'] ?? '') ?></textarea>
                                            <small class="text-muted"><?= lang('Pages.Offline_Payment_Instructions_Help') ?></small>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary"><?= lang('Pages.Save_Settings') ?></button>
                                        </div>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!--end col-->
    </div><!--end row-->
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
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#offlinePaymentsTable').DataTable({
                "autoWidth": false,
                "width": "100%",
                "order": [[3, "desc"]],
                "pageLength": 25
            });

            let currentPaymentId, currentStatusSelect;

            $('.status-select').change(function() {
                currentPaymentId = $(this).data('payment-id');
                currentStatusSelect = $(this);
                var newStatus = $(this).val();
                var statusCell = $(this).closest('tr').find('td:eq(5)');
                var amountCell = $(this).closest('tr').find('td:eq(3)');

                if (newStatus === 'refunded') {
                    // Show the refund modal
                    $('#totalPackageAmount').text(amountCell.text());
                    $('#refundModal').modal('show');
                } else if (confirm('<?= lang('Pages.Update_Payment_Status') ?>')) {
                    updatePaymentStatus(currentPaymentId, newStatus);
                }
            });

            $('#confirmRefund').click(function() {
                var refundAmount = $('#refundAmount').val();
                if (refundAmount && parseFloat(refundAmount) > 0) {
                    $('#refundModal').modal('hide');
                    updatePaymentStatus(currentPaymentId, 'refunded', refundAmount);
                } else {
                    alert('Please enter a valid refund amount.');
                }
            });

            function updatePaymentStatus(paymentId, newStatus, refundAmount = null) {
                var data = {
                    payment_id: paymentId,
                    status: newStatus,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                };

                if (refundAmount) {
                    data.refund_amount = refundAmount;
                }

                $.ajax({
                    url: '<?= route_to('offline-payment-update-status') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            // Update the status badge
                            var badgeClass = newStatus == 'completed' ? 'bg-success' : (newStatus == 'pending' ? 'bg-warning' : 'bg-danger');
                            currentStatusSelect.closest('tr').find('td:eq(5)').html('<span class="badge ' + badgeClass + '">' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + '</span>');
                        } else {
                            toastType = 'danger';
                            currentStatusSelect.val(currentStatusSelect.find('option:selected').prevAll().first().val());
                        }

                        showToast(toastType, response.message);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', 'An error occurred while updating the payment status.');
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);

                        currentStatusSelect.val(currentStatusSelect.find('option:selected').prevAll().first().val());
                    }
                });
            }

            $('#offlineSettingsForm').submit(function(e) {
                e.preventDefault();

                const submitButton = $('button[type="submit"]');

                enableLoadingEffect(submitButton);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
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
                        disableLoadingEffect(submitButton);
                    }
                });
            });
        });
    </script>
<?= $this->endSection() //End section('scripts')?>

<?= $this->section('modals') ?>
<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel"><?= lang('Pages.Refund_Payment') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?= lang('Pages.Total_Package_Amount') ?>: <span id="totalPackageAmount"></span></p>
                <div class="mb-3">
                    <label for="refundAmount" class="form-label"><?= lang('Pages.Refund_Amount') ?></label>
                    <input type="number" class="form-control" id="refundAmount" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Pages.Cancel') ?></button>
                <button type="button" class="btn btn-primary" id="confirmRefund"><?= lang('Pages.Confirm_Refund') ?></button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() //End section('modals')?>
