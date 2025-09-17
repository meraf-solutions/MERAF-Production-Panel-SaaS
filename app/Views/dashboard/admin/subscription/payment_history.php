<?= $this->extend('layouts/dashboard') ?>

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
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-3"><?= lang('Pages.Payment_History') ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('subscription-manager/subscription/view/' . $subscription['id']) ?>" class="btn btn-sm btn-secondary">
                            <i class="uil uil-arrow-left"></i> <?= lang('Pages.Back_to_Subscription') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Subscription Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%"><?= lang('Pages.Subscription_ID') ?></th>
                                    <td><?= esc($subscription['id']) ?></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Pages.User') ?></th>
                                    <td><?= esc($subscription['email']) ?></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Pages.Package') ?></th>
                                    <td><?= esc($subscription['package_name']) ?></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Pages.Payment_Method') ?></th>
                                    <td><?= esc($subscription['payment_method']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box d-flex mb-3">
                                <span class="info-box-icon bg-info">
                                    <i class="uil uil-dollar-sign"></i>
                                </span>
                                <div class="info-box-content ps-2">
                                    <span class="info-box-text"><?= lang('Pages.Total_Payments') ?></span>
                                    <span class="info-box-number">
                                        <?php
                                        $total = array_reduce($payments, function($carry, $payment) {
                                            return $carry + $payment['amount'];
                                        }, 0);
                                        echo number_format($total, 2) . ' ' . ($payments[0]['currency'] ?? 'USD');
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="payments-table">
                            <thead>
                                <tr>
                                    <th><?= lang('Pages.Date') ?></th>
                                    <th><?= lang('Pages.Transaction_ID') ?></th>
                                    <th><?= lang('Pages.Amount') ?></th>
                                    <th><?= lang('Pages.Status') ?></th>
                                    <th><?= lang('Pages.Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= formatDate($payment['payment_date'], $myConfig) ?></td>
                                    <td><?= esc($payment['transaction_id']) ?></td>
                                    <td>
                                        <?= number_format($payment['amount'], 2) ?>
                                        <?= esc($payment['currency']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= getPaymentStatusBadgeClass($payment['payment_status']) ?>"><?= lang('Pages.' . $payment['payment_status']) ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-details-btn" 
                                                data-transaction="<?= esc($payment['transaction_id']) ?>"
                                                title="<?= lang('Pages.View_Details') ?>">
                                            <i class="uil uil-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Payment_Details') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">

                        </div>
                    </div>
                    <div id="paymentDetails" style="display: none;">
                        <!-- Payment details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Close') ?></button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            $('#payments-table').DataTable({
                autoWidth: false,
                width: "100%",
                order: [[0, 'desc']],
                language: {
                    "paginate": {
                        "first": '<i class="mdi mdi-chevron-double-left"></i>',
                        "last": '<i class="mdi mdi-chevron-double-right"></i>',
                        "next": "&#8594;",
                        "previous": "&#8592;"
                    },
                    "search": "<?= lang('Pages.Search') ?>:",
                    "lengthMenu": "<?= lang('Pages.DT_lengthMenu') ?>",
                    "loadingRecords": "<?= lang('Pages.Loading_button') ?>",
                    "info": '<?= lang('Pages.DT_info') ?>',
                    "infoEmpty": '<?= lang('Pages.DT_infoEmpty') ?>',
                    "zeroRecords": '<?= lang('Pages.DT_zeroRecords') ?>',
                    "emptyTable": '<?= lang('Pages.DT_emptyTable') ?>'
                },
            });

            // Handle view details button
            $('.view-details-btn').click(function() {
                const transactionId = $(this).data('transaction');
                
                // Show modal with loading spinner
                $('#paymentDetails').hide();
                $('.spinner-border').show();
                $('#paymentDetailsModal').modal('show');

                // Fetch payment details from subscription's payment method
                $.ajax({
                    url: `<?= base_url('subscription-manager/subscription/payment-details/') ?>${transactionId}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Format and display payment details
                            const details = response.data;
                            let html = `
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Transaction ID</th>
                                        <td>${details.transaction_id}</td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Status') ?></th>
                                        <td>${details.payment_status}</td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Amount') ?></th>
                                        <td>${details.amount_paid} ${details.currency}</td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Payment_Date') ?></th>
                                        <td>${formatDateTime(details.last_payment_date)}</td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('Pages.Payer') ?></th>
                                        <td>${details.email_address}</td>
                                    </tr>
                                </table>
                            `;
                            
                            $('#paymentDetails').html(html).show();
                            $('.spinner-border').hide();
                        } else {
                            showToast('danger', response.message || '<?= lang('Pages.Failed_to_load_payment_details') ?>');
                            $('#paymentDetailsModal').modal('hide');
                        }
                    },
                    error: function (xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        $('#paymentDetailsModal').modal('hide');
                    }
                });
            });
        });
        
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