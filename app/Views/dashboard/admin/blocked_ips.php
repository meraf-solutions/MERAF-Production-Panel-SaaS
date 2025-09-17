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

            <div class="border-0 mb-3">
                <button class="btn mx-auto btn-danger" id="delete-ip-submit"><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>
				<button class="btn btn-secondary" id="clear-selection"><i class="uil uil-times-circle"></i> <?= lang('Pages.Clear_Selection') ?></button>
            </div>

            <form novalidate action="javascript:void(0)" id="delete-template-form">
                <div class="table-responsive shadow rounded p-4">
                    <table id="ip-list-table" class="table table-striped bg-white mb-0">
                        <thead>
                            
                        </thead>
                        <tbody id="ip-list-tbody">
                            <!-- Initial rows loaded with the view -->

                        </tbody>
                    </table>
                </div>
            </form>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script type="text/javascript">
        
        $(document).ready(function() {

            /*******************
             * Handle dataTables
             ******************/   
            $('#ip-list-table').DataTable({
                "ajax": {
                    "url": "<?= base_url('admin-options/blocked-ip-logs/get') ?>",
                    "type": "GET",
                    "dataSrc": "",
                    "beforeSend": function () {
                        $('#loading-indicator').show();
                    },
                    "complete": function() {
                        $('#loading-indicator').hide();
                    },
                },
                "autoWidth": false,
                "width": "100%",
                "columns": [
                    { "data": null, "title": '<input type="checkbox" id="checkAll" class="form-check-input">' },
                    { "data": "id", "title": '<?= lang('Pages.ID') ?>', "visible": false },
                    { "data": "ip_address", "title": '<?= lang('Pages.IP_Address') ?>' },
                    { "data": "license_key", "title": '<?= lang('Pages.License_Key') ?>' },
                    { "data": "owner_username", "title": '<?= lang('Pages.Owner') ?>' },
                    { "data": "created_at", "title": '<?= lang('Pages.Added_On') ?>' },
                ],
                "columnDefs": [
                    {
                        "targets": 0,
                        "orderable": false,
                        "render": function (data, type, row, meta) {
                            return "<input type='checkbox' id='" + row.id + "' class='form-check-input'>";
                        }
                    },
                    {
                        "targets": 1,
                        "orderable": false,
                        "visible": false,
                    },
                    {
                        "targets": 3,
                        "orderable": false,
                        "render": function(data, type, row) {
                            if (type === 'display' && data) {
                                var encodedData = encodeURIComponent(data); // URL encode the license_key value
                                return `<a class="text-primary" href="<?= base_url('license-manager/list-all?s=') ?>${encodedData}" class="rounded">${data.length > 10 ? data.substring(0, 10)+'...' : data}</a>`;
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        "targets": 4,
                        "orderable": false,
                        "render": function(data, type, row, meta) {
                            return `${data} (<?= lang('Pages.id') ?>: ${row.owner_id})`;
                        }
                    },
                    {
                        "targets": 5,
                        "render": function(data) {
                            return formatDateTime(data);
                        }
                    }
                ],
                "pagingType": "first_last_numbers",
                "language": {
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
                "fixedHeader": {
                    "header": true,
                    "footer": true
                },
                "pageLength": 25,
                "responsive": true
            });

            // Hide the sorter icon in the first column
            var firstTh = $('thead tr th:first');
            firstTh.find('span.dt-column-order').hide();   

            // reload the data maintaining the current user's selection
            function reloadDataTable() {
                $('#ip-list-table').DataTable().ajax.reload(null, false);
            }

            // Function to enable/disable the delete button based on checkbox state
            function updateDeleteButtonState() {
                var anyCheckboxChecked = $('tbody#ip-list-tbody input[type="checkbox"]:checked').length > 0;
                $('#delete-ip-submit').prop('disabled', !anyCheckboxChecked);
            }                

            // Check/uncheck all checkboxes when "checkAll" is clicked
            $('#checkAll').on('change', function () {
                var isChecked = $(this).prop('checked');
                $('tbody#ip-list-tbody').find('input[type="checkbox"]').prop('checked', isChecked);
                updateDeleteButtonState();
            });

            // Check/uncheck "checkAll" based on the state of individual checkboxes
            $(document).on('change', 'tbody#ip-list-tbody input[type="checkbox"]', function () {
                var allChecked = $('tbody#ip-list-tbody input[type="checkbox"]:checked').length === $('tbody#ip-list-tbody input[type="checkbox"]').length;
                $('#checkAll').prop('checked', allChecked);
                updateDeleteButtonState();
            });
			
            $('#clear-selection').on('click', function() {
                $('tbody#ip-list-tbody input[type="checkbox"], #checkAll').prop('checked', false);
                updateDeleteButtonState();
            });			

            // Call the function initially to set the button state
            updateDeleteButtonState();                

            // Uncheck "checkAll" if any individual checkbox is unchecked
            $(document).on('change', 'tbody#ip-list-tbody input[type="checkbox"]', function () {
                if (!$(this).prop('checked')) {
                    $('#checkAll').prop('checked', false);
                }
            });
            
            /***************************
            // Handle the delete license
            ***************************/
            $('#delete-ip-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#delete-template-form');
                var submitButton = $(this);

                // Get the selected file names
                var selectedLicense = [];
                $('tbody#ip-list-tbody input[type="checkbox"]:checked').each(function () {
                    selectedLicense.push($(this).attr('id'));
                });

                // Remove existing hidden inputs before adding new ones
                form.find('input[name="selectedLicense[]"]').remove();

                // Add the selected files to the form data
                $.each(selectedLicense, function (index, licenseKey) {
                    form.append('<input type="hidden" name="selectedLicense[]" value="' + licenseKey + '">');
                });

                // Enable loading effect
                enableLoadingEffect(submitButton);

                var data = new FormData(form[0]);
                
                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_log') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms 
                    $.ajax({
                        url: '<?= base_url('admin-options/blocked-ip-logs/delete') ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            let toastType = 'info';

                            if (response.result === 'success') {
                                toastType = 'success';

                                // reload the dataTable
                                reloadDataTable();

                                // uncheck all
                                $('#checkAll').prop('checked', false);
                            } else if (response.result === 'error') {
                                toastType = 'info';
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
                            updateDeleteButtonState();
                            disableLoadingEffect(submitButton);
                        }
                    });                    
                } else {
                    // User cancelled the deletion action
                    // Disable loading effect
                    disableLoadingEffect(submitButton);
                }                  
            });         
        });       
    </script>
<?= $this->endSection() //End section('scripts')?>