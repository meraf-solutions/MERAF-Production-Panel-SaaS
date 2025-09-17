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
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-3"><?= lang('Pages.Subscription_Details') ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('subscription-manager/list') ?>" class="btn btn-sm btn-secondary">
                            <i class="uil uil-arrow-left"></i> <?= lang('Pages.Back_to_List') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%"><?= lang('Pages.Subscription_ID') ?></th>
                            <td><?= esc($subscription['id']) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Status') ?></th>
                            <td>
                                <span class="badge bg-<?= getStatusBadgeClass($subscription['subscription_status']) ?>">
                                    <?= lang('Pages.' . $subscription['subscription_status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.User') ?></th>
                            <td>
                                <?= esc($subscription['first_name']) ?> <?= esc($subscription['last_name']) ?><br>
                                <small class="text-muted"><?= esc($subscription['email']) ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Package') ?></th>
                            <td><?= esc($subscription['package_name']) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Payment_Method') ?></th>
                            <td><?= esc($subscription['payment_method']) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Start_Date') ?></th>
                            <td><?= formatDate($subscription['start_date'], $myConfig) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Next_Billing') ?></th>
                            <td><?= formatDate($subscription['next_payment_date'], $myConfig) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Last_Payment') ?></th>
                            <td><?= formatDate($subscription['last_payment_date'], $myConfig) ?></td>
                        </tr>
                    </table>

                    <?php if ($subscription['subscription_status'] !== 'cancelled'): ?>
                    <div class="mt-3">
                        <?php if ($subscription['subscription_status'] === 'active'): ?>
                        <button type="button" class="btn btn-warning suspend-btn mb-3">
                            <i class="uil uil-pause"></i> <?= lang('Pages.Suspend_Subscription') ?>
                        </button>
                        <?php elseif ($subscription['subscription_status'] === 'suspended'): ?>
                        <button type="button" class="btn btn-success activate-btn mb-3">
                            <i class="uil uil-play"></i> <?= lang('Pages.Activate_Subscription') ?>
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-danger cancel-btn mb-3">
                            <i class="uil uil-times"></i> <?= lang('Pages.Cancel_Subscription') ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <?php if (isset($subscription['payment_method_details'])): ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= lang('Pages.Payment_Details') ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%"><?= lang('Pages.Status') ?></th>
                            <td><?= ucfirst(esc($subscription['payment_method_details']->status)) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Plan_ID') ?></th>
                            <td><?= esc($subscription['payment_method_details']->plan_id) ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Billing_Cycles') ?></th>
                            <td><?= $subscription['payment_method_details']->billing_info->cycle_executions[0]->cycles_completed ?? 0 ?> <?= lang('Pages.completed') ?></td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Last_Payment') ?></th>
                            <td>
                                <?php if (isset($subscription['payment_method_details']->billing_info->last_payment)): ?>
                                    <?= lang('Pages.Amount') ?>: <?= esc($subscription['payment_method_details']->billing_info->last_payment->amount->value) ?>
                                    <?= esc($subscription['payment_method_details']->billing_info->last_payment->amount->currency_code) ?><br>
                                    <?= lang('Pages.Date') ?>: <?= formatDate($subscription['payment_method_details']->billing_info->last_payment->time, $myConfig) ?>
                                <?php else: ?>
                                    <?= lang('Pages.No_payment_recorded') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= lang('Pages.Failed_Payments') ?></th>
                            <td><?= $subscription['payment_method_details']->billing_info->failed_payments_count ?? 0 ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= lang('Pages.Payment_History') ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('subscription-manager/subscription/payments/' . $subscription['id']) ?>" class="btn btn-sm btn-info">
                        <?= lang('Pages.View_All') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <p class="text-muted"><?= lang('Pages.No_payments_recorded') ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?= lang('Pages.Date') ?></th>
                                        <th><?= lang('Pages.Transaction_ID') ?></th>
                                        <th><?= lang('Pages.Amount') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                                    <tr>
                                        <td><?= formatDate($payment['payment_date'], $myConfig) ?></td>
                                        <td><?= esc($payment['transaction_id']) ?></td>
                                        <td>
                                            <?= esc($payment['amount']) ?>
                                            <?= esc($payment['currency']) ?>
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
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Modals -->
    <div class="modal fade" id="reasonModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Enter_Reason') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="reasonForm">
                        <input type="hidden" name="action_type" id="action_type">
                        <div class="form-group">
                            <label for="reason"><?= lang('Pages.Reason') ?></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="submitReason"><i class="uil uil-save"></i> <?= lang('Pages.Submit') ?></button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            // Handle action buttons
            $('.suspend-btn, .activate-btn, .cancel-btn').click(function() {
                const action = $(this).hasClass('suspend-btn') ? 'suspend' : 
                            $(this).hasClass('activate-btn') ? 'activate' : 'cancel';
                
                $('#action_type').val(action);
                $('#reasonModal').modal('show');
            });

            // Handle reason submission
            $('#submitReason').click(function() {
                const action = $('#action_type').val();
                const reason = $('#reason').val();
                const button = $(this);

                if (!reason) {
                    showToast('danger', '<?= lang('Pages.Please_enter_a_reason') ?>');
                    return;
                }

                $.ajax({
                    url: `<?= base_url('subscription-manager/subscription/') ?>${action}/<?= $subscription['id'] ?>`,
                    method: 'POST',
                    data: { reason: reason },
                    success: function(response) {
                        if (response.success) {
                            delayedRedirect(location);
                        } else {
                            showToast('danger', response.message || '<?= lang('Pages.Action_failed') ?>');
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });
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
                    return 'secondary text-light';
                default:
                    return 'info';
            }
        }
    </script>
<?= $this->endSection() ?>