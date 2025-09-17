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
<?= $this->endSection() //End section('heading')?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= lang('Pages.Subscription_Management') ?></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="subscriptions-table">
                            <thead>
                                <tr>
                                    <th><?= lang('Pages.ID') ?></th>
                                    <th><?= lang('Pages.User') ?></th>
                                    <th><?= lang('Pages.Package') ?></th>
                                    <th><?= lang('Pages.Status') ?></th>
                                    <th><?= lang('Pages.Payment_Method') ?></th>
                                    <th><?= lang('Pages.Start_Date') ?></th>
                                    <th><?= lang('Pages.Next_Billing') ?></th>
                                    <th><?= lang('Pages.Next_Billing') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td><?= esc($sub['id']) ?></td>
                                    <td><a href="<?= base_url('admin-options/user-manager/?s=' . esc($sub['email'])) ?>" class="text-primary"><?= esc($sub['username']) ?></a></td>
                                    <td><?= esc($sub['package_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusBadgeClass($sub['subscription_status']) ?>">
                                            <?= lang('Pages.' . $sub['subscription_status']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($sub['payment_method']) ?></td>
                                    <td><?= formatDate($sub['start_date'], $myConfig) ?></td>
                                    <td><?= formatDate($sub['next_payment_date'], $myConfig) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= base_url('subscription-manager/subscription/view/' . $sub['id']) ?>" 
                                            class="btn btn-sm btn-info" title="<?= lang('Pages.View_Details') ?>">
                                                <i class="uil uil-eye"></i>
                                            </a>
                                            <?php if ($sub['subscription_status'] === 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-warning suspend-btn" 
                                                    data-id="<?= $sub['id'] ?>" title="<?= lang('Pages.Suspend') ?>">
                                                <i class="uil uil-pause"></i>
                                            </button>
                                            <?php elseif ($sub['subscription_status'] === 'suspended'): ?>
                                            <button type="button" class="btn btn-sm btn-success activate-btn" 
                                                    data-id="<?= $sub['id'] ?>" title="<?= lang('Pages.Activate') ?>">
                                                <i class="uil uil-play"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($sub['subscription_status'] !== 'cancelled'): ?>
                                            <button type="button" class="btn btn-sm btn-danger cancel-btn" 
                                                    data-id="<?= $sub['id'] ?>" title="<?= lang('Pages.Cancel') ?>">
                                                <i class="uil uil-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
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
                        <input type="hidden" name="subscription_id" id="subscription_id">
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
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            $('#subscriptions-table').DataTable({
                autoWidth: false,
                width: "100%",
                order: [[0, 'desc']]
            });

            // Handle action buttons
            $('.suspend-btn, .activate-btn, .cancel-btn').click(function() {
                const id = $(this).data('id');
                const action = $(this).hasClass('suspend-btn') ? 'suspend' : 
                            $(this).hasClass('activate-btn') ? 'activate' : 'cancel';
                
                $('#subscription_id').val(id);
                $('#action_type').val(action);
                $('#reasonModal').modal('show');
            });

            // Handle reason submission
            $('#submitReason').click(function() {
                const id = $('#subscription_id').val();
                const action = $('#action_type').val();
                const reason = $('#reason').val();

                if (!reason) {
                    showToast('danger', '<?= lang('Pages.Please_enter_a_reason') ?>');
                    return;
                }

                $.ajax({
                    url: `<?= base_url('subscription-manager/subscription/') ?>${action}/${id}`,
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
<?= $this->endSection() //End section('scripts')?>