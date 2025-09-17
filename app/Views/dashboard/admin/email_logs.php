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
<?= $this->endSection() //End section('heading')?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-12 mt-4">

            <div class="border-0">
                <button class="mx-auto btn btn-info" id="refresh-btn" aria-label="<?= lang('Pages.Refresh') ?>"><i class="uil uil-refresh"></i> <?= lang('Pages.Refresh') ?></button>
                <button class="mx-auto btn btn-secondary" id="reset-btn" aria-label="<?= lang('Pages.Reset_Filters') ?>"><i class="uil uil-times"></i> <?= lang('Pages.Reset_Filters') ?></button>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mt-3 border-0">
            <select id="status-filter" class="form-select">
                <option value=""><?= lang('Pages.All_Statuses') ?></option>
                <option value="sent"><?= lang('Pages.Sent') ?></option>
                <option value="failed"><?= lang('Pages.Failed') ?></option>
            </select>
        </div>
        <div class="col-md-6 mt-3">
            <div class="input-group">
                <input type="text" id="start-date" class="form-control" placeholder="<?= lang('Pages.Start_Date') ?>">
                <span class="input-group-text"><?= lang('Pages.To') ?></span>
                <input type="text" id="end-date" class="form-control" placeholder="<?= lang('Pages.End_Date') ?>">
            </div>
        </div>
        <div class="col-md-3 mt-3">
            <button id="filter-btn" class="btn btn-primary"><i class="uil uil-filter"></i> <?= lang('Pages.Apply_Filters') ?></button>
        </div>

        <div class="table-responsive shadow rounded mt-3 p-4">
            <table id="email-logs-table" class="table table-striped">
                <thead>
                    <tr>
                        <th><?= lang('Pages.Subject') ?></th>
                        <th><?= lang('Pages.To') ?></th>
                        <th><?= lang('Pages.Status') ?></th>
                        <th><?= lang('Pages.Date_Time') ?></th>
                        <th><?= lang('Pages.Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated by DataTables -->
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Modal for viewing email content -->
    <div class="modal fade" id="emailContentModal" tabindex="-1" aria-labelledby="emailContentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailContentModalLabel"><?= lang('Pages.Email_Content') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <iframe id="emailContentFrame" style="width: 100%; height: 500px; border: none; background: #fff"></iframe>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
    $(document).ready(function() {
        // Initialize Flatpickr for date inputs
        flatpickr("#start-date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
        flatpickr("#end-date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        var table = $('#email-logs-table').DataTable({
            "autoWidth": false,
            "width": "100%",
            "ajax": {
                url: '<?= $pageUrl.'data' ?>',
                dataSrc: '',
                data: function(d) {
                    d.status = $('#status-filter').val();
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                },
                beforeSend: function (xhr) {
                        xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        $('#loading-indicator').show();
                    },
                complete: function() {
                    $('#loading-indicator').hide();
                },
            },
            "columns": [
                { data: 'id' },
                { data: 'subject' },
                { data: 'to' },
                { data: 'status' },
                { data: 'created_at' },
                {
                    data: null,
                    render: function(data, type, row) {
                            return '<div class="btn-group"><button class="btn btn-sm btn-info view-email" data-id="' + row.id + '"><i class="uil uil-eye"></i></button>' +
                                '<button class="btn btn-sm btn-primary resend-email" data-id="' + row.id + '"><i class="uil uil-redo"></i></button></div>';
                        }
                }
            ],
            "columnDefs": [
                    {
                        "targets": 0,
                        "visible": false,
                        "searchable": false
                    },
                    {
                        "targets": 4,
                        "render": function(data, type, row) {
                            return formatDateTime(data);
                        }
                    },
            ],
            "order": [0, "desc"],
            "pagingType": "full_numbers",
            "language": {
                "paginate": {
                    "first": '<i class="mdi mdi-chevron-double-left" aria-hidden="true"></i>',
                    "last": '<i class="mdi mdi-chevron-double-right" aria-hidden="true"></i>',
                    "next": '<i class="mdi mdi-chevron-right" aria-hidden="true"></i>',
                    "previous": '<i class="mdi mdi-chevron-left" aria-hidden="true"></i>'
                },
                "search": "<?= lang('Pages.Search') ?>:",
                "lengthMenu": "<?= lang('Pages.DT_lengthMenu') ?>",
                "loadingRecords": "<?= lang('Pages.Loading_button') ?>",
                "info": '<?= lang('Pages.DT_info') ?>',
                "infoEmpty": '<?= lang('Pages.DT_infoEmpty') ?>',
                "zeroRecords": '<?= lang('Pages.DT_zeroRecords') ?>',
                "emptyTable": '<?= lang('Pages.DT_emptyTable') ?>'
            },
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "responsive": true,
            "paging": true,
            "info": true,
        });

        // View email content
        $('#email-logs-table').on('click', '.view-email', function() {
            var id = $(this).data('id');
            $('#emailContentFrame').attr('src', '<?= $pageUrl . 'view/' ?>' + id);
            $('#emailContentModal').modal('show');
        });
        
        $('#emailContentModal').on('hidden.bs.modal', function () {
            $('#emailContentFrame').attr('src', '');
        });

        // Resend email
        $('#email-logs-table').on('click', '.resend-email', function() {
            var id = $(this).data('id');
            var resentBtn = $(this);

            // Display a confirmation dialog box
            var confirmResend = confirm("<?= lang('Notifications.confirmation_to_resend_email') ?>");

            if (confirmResend) {
                enableLoadingEffect(resentBtn);
                let toastType = 'info';

                $.post('<?= $pageUrl . 'resend/' ?>' + id, function(response) {
                    if(response.success) {
                        toastType = 'success';
                    }
                    else {
                        toastType = 'danger';
                    }
                    showToast(toastType, response.message);
                    disableLoadingEffect(resentBtn);
                    table.ajax.reload();
                });
            }
        });

        // Filter functionality
        $('#filter-btn').click(function() {
            table.ajax.reload();
        });

        // Reset filters
        $('#reset-btn').click(function() {
            $('#status-filter').val('');
            $('#start-date').val('');
            $('#end-date').val('');
            table.ajax.reload();
        });

        // Refresh button
        $('#refresh-btn').click(function() {
            table.ajax.reload();
        });
    });
    </script>
<?= $this->endSection() ?>