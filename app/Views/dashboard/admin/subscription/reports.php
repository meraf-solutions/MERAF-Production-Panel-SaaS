<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<?= $this->endSection() //End section('head')?>

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
    <!-- Summary Cards -->
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 mt-4">
            <section class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3 bg-info text-white">
                <div class="align-items-center">
                    <h3><?= $summary['active_subscriptions'] ?></h3>
                    <p><?= lang('Pages.Active_Subscriptions') ?></p>
                </div>
                <div class="avatar avatar-ex-small rounded">
                    <i class="uil uil-user"></i>
                </div>
            </section>
        </div>
        <div class="col-lg-4 col-md-6 mt-4">
            <section class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3 bg-success text-white">
                <div class="align-items-center">
                    <h3><?= number_format($summary['total_revenue'], 2) ?></h3>
                    <p><?= lang('Pages.Total_Revenue') ?></p>
                </div>
                <div class="avatar avatar-ex-small rounded">
                    <i class="uil uil-dollar-sign"></i>
                </div>
            </section>
        </div>
        <div class="col-lg-4 col-md-6 mt-4">
            <section class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3 bg-warning text-dark">
                <div class="align-items-center">
                    <h3><?= count($summary['recent_payments']) ?></h3>
                    <p><?= lang('Pages.Recent_Payments') ?></p>
                </div>
                <div class="avatar avatar-ex-small rounded">
                    <i class="uil uil-chart-line"></i>
                </div>
            </section>
        </div>
        <div class="col-lg-4 col-md-6 mt-4">
            <section class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3 bg-primary text-white">
                <div class="align-items-center">
                    <h3><?= $summary['pending_payments'] ?></h3>
                    <p><?= lang('Pages.Pending_Payments') ?></p>
                </div>
                <div class="avatar avatar-ex-small rounded">
                    <i class="uil uil-clock"></i>
                </div>
            </section>
        </div>
        <div class="col-lg-4 col-md-6 mt-4">
            <section class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3 bg-danger text-white">
                <div class="align-items-center">
                    <h3><?= $summary['failed_payments'] ?></h3>
                    <p><?= lang('Pages.Failed_Payments') ?></p>
                </div>
                <div class="avatar avatar-ex-small rounded">
                    <i class="uil uil-times-circle"></i>
                </div>
            </section>
        </div>
    </div>

    <!-- Report Generator -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= lang('Pages.Generate_Report') ?></h3>
                </div>
                <div class="card-body">
                    <form id="reportForm">
                        <div class="row align-items-end">
                            <!-- Date Range Field -->
                            <div class="col-lg-3 col-md-12">
                                <div class="form-group">
                                    <label for="dateRange"><?= lang('Pages.Date_Range') ?></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="uil uil-calendar-alt"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="dateRange">
                                        <input type="hidden" id="startDate" name="start_date">
                                        <input type="hidden" id="endDate" name="end_date">
                                    </div>
                                </div>
                            </div>
                            <!-- Report Type Field -->
                            <div class="col-lg-3 col-md-12 mt-3">
                                <div class="form-group">
                                    <label for="reportType"><?= lang('Pages.Report_Type') ?></label>
                                    <select class="form-control" name="type" id="reportType">
                                        <option value="revenue"><?= lang('Pages.Revenue_Report') ?></option>
                                        <option value="subscriptions"><?= lang('Pages.Subscription_Report') ?></option>
                                    </select>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="col-lg-3 col-md-12 mt-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="uil uil-plus"></i> <?= lang('Pages.Generate_Report') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Report Results -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <!-- Title -->
                    <h3 class="card-title mb-0"><?= lang('Pages.Report_Results') ?></h3>
                    
                    <!-- Tools -->
                    <div class="card-tools">
                        <button type="button" class="btn btn-secondary btn-tool" id="exportReport" disabled>
                            <i class="uil uil-cloud-download"></i> <?= lang('Pages.Export') ?>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div id="reportLoading" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                            </div>
                        </div>
                    </div>
                    <div id="reportResults">
                        <p class="text-muted"><?= lang('Pages.Generate_a_report_to_see_results') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= lang('Pages.Recent_Activity') ?></h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($summary['recent_payments'] as $payment): ?>
                        <div class="d-flex mb-3">
                            <i class="uil uil-dollar-sign bg-success"></i>
                            <div class="timeline-item ps-2">
                                <span class="time">
                                    <i class="uil uil-clock"></i> 
                                    <?= formatDate($payment['payment_date'], $myConfig) ?>
                                </span>
                                <h3 class="timeline-header">
                                    <?= lang('Pages.Payment_Received') ?>
                                </h3>
                                <div class="timeline-body">
                                    <?= lang('Pages.Amount') ?>: <?= number_format($payment['amount'], 2) ?> <?= esc($payment['currency']) ?><br>
                                    <?= lang('Pages.Transaction_ID') ?>: <?= esc($payment['transaction_id']) ?><br>
                                    <?= lang('Pages.User_Email') ?>: <?= esc($payment['email']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    
	
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
			// Calculate start and end dates for last 30 days
            const endDate = moment();
            const startDate = moment().subtract(29, 'days');
			
            // Initialize date range picker
            $('#dateRange').daterangepicker({
                ranges: {
                    '<?= lang('Pages.Today') ?>': [moment(), moment()],
                    '<?= lang('Pages.Yesterday') ?>': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '<?= lang('Pages.Last_seven_Days') ?>': [moment().subtract(6, 'days'), moment()],
                    '<?= lang('Pages.Last_thirty_Days') ?>': [moment().subtract(29, 'days'), moment()],
                    '<?= lang('Pages.This_Month') ?>': [moment().startOf('month'), moment().endOf('month')],
                    '<?= lang('Pages.Last_Month') ?>': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: startDate,
                endDate: endDate
            }, function(start, end) {
                $('#startDate').val(start.format('YYYY-MM-DD'));
                $('#endDate').val(end.format('YYYY-MM-DD'));
            });
			
			// Set initial hidden input values
            $('#startDate').val(startDate.format('YYYY-MM-DD'));
            $('#endDate').val(endDate.format('YYYY-MM-DD'));

            // Handle report generation
            $('#reportForm').submit(function(e) {
                e.preventDefault();
                
                $('#reportLoading').show();
                $('#reportResults').hide();
                $('#exportReport').prop('disabled', true);

                $.ajax({
                    url: '<?= base_url('subscription-manager/reports/generate') ?>',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        console.log(response);
                        $('#reportLoading').hide();
                        if (response.success) {
                            displayReport(response.data, $('#reportType').val());
                            $('#exportReport').prop('disabled', false);
                        } else {
                            $('#reportResults').html(`
                                <div class="alert alert-danger">
                                    <?= lang('Pages.Failed_to_generate_report') ?> ${response.message}
                                </div>
                            `).show();
                        }
                    },
                    error: function() {
                        $('#reportLoading').hide();
                        $('#reportResults').html(`
                            <div class="alert alert-danger">
                                <?= lang('Pages.An_error_occurred_while_generating_the_report') ?>
                            </div>
                        `).show();
                    }
                });
            });

            // Handle report export
            $('#exportReport').click(function() {
                const reportType = $('#reportType').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                
                window.location.href = `<?= base_url('subscription-manager/reports/export') ?>?type=${reportType}&start_date=${startDate}&end_date=${endDate}`;
            });
        });
        
            var languageMapping = {
                'active': '<?= lang('Pages.active') ?>',
                'pending': '<?= lang('Pages.pending') ?>',
                'suspended': '<?= lang('Pages.suspended') ?>',
                'refunded': '<?= lang('Pages.refunded') ?>',
                'partially_refunded': '<?= lang('Pages.partially_refunded') ?>',
            };
        
        function displayReport(data, type) {
            let html = '';
            
            if (type === 'revenue') {
                html = `
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?= lang('Pages.Currency') ?></th>
                                    <th><?= lang('Pages.Number_of_Payments') ?></th>
                                    <th><?= lang('Pages.Total_Amount') ?></th>
                                    <th><?= lang('Pages.Refunded_Amount') ?></th>
                                    <th><?= lang('Pages.Net_Revenue') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(row => `
                                    <tr>
                                        <td>${row.currency}</td>
                                        <td>${row.count}</td>
                                        <td>${formatCurrency(row.total, row.currency)}</td>
                                        <td>${formatCurrency(row.refunded_total, row.currency)}</td>
                                        <td>${formatCurrency(row.net_revenue, row.currency)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html = `
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?= lang('Pages.Date') ?></th>
                                    <th><?= lang('Pages.Package') ?></th>
                                    <th><?= lang('Pages.Status') ?></th>
                                    <th><?= lang('Pages.User') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(row => `
                                    <tr>
                                        <td>${formatDateTime(row.created_at)}</td>
                                        <td>${row.package_name}</td>
                                        <td>
                                            <span class="badge bg-${getStatusBadgeClass(row.subscription_status)}">
                                                ${languageMapping[row.subscription_status]}
                                            </span>
                                        </td>
                                        <td>${row.email}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            $('#reportResults').html(html).show();
        }

        function formatCurrency(amount, currency) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        }

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
