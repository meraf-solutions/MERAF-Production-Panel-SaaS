<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('head') ?>
    <style>
    #license-list-table th,
    #license-list-table td {
        text-align: center; /* Center-align all table headers and cells */
    }
    </style>
<?= $this->endSection() //End section('head')?>

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
    <?php if($myConfig['licenseManagerOnUse'] !== 'slm') { ?>
        <div class="row">
            <div class="col-12 mt-4">
                
                <div class="border-0 mb-3">
                    <button class="mx-auto btn btn-danger" id="delete-license-submit"><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>
                </div>

                <form novalidate action="javascript:void(0)" id="delete-template-form">
                    <div class="table-responsive shadow rounded p-4">
                        <table id="license-list-table" class="table table-center table-striped bg-white mb-0">
                            <thead></thead>
                            <tbody id="license-list-tbody">
                                <!-- Initial rows loaded with the view -->
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </form>
            </div><!--end col-->
        </div><!--end row-->

        <!-- <div class="row text-center"> -->
            <!-- PAGINATION START -->
            
            <!-- <div class="mt-4 d-flex align-items-center justify-content-between">
                <ul class="pagination mb-0">
                    <li class="page-item"><a class="page-link" href="javascript:void(0)" aria-label="Previous">Prev</a></li>
                    <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a></li>
                    <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                    <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                    <li class="page-item"><a class="page-link" href="javascript:void(0)" aria-label="Next">Next</a></li>
                </ul>
            </div>                             -->
            <!-- <div class="col-12 mt-4">
                <div class="d-md-flex align-items-center text-center justify-content-between">
                    <span class="text-muted me-3">Showing 1 - 10 out of 50</span>
                    <ul class="pagination mb-0 justify-content-center mt-4 mt-sm-0">
                        <li class="page-item"><a class="page-link" href="javascript:void(0)" aria-label="Previous">Prev</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#" aria-label="Next">Next</a></li>
                    </ul>
                </div>
            </div> -->
            <!-- PAGINATION END -->
        <!-- </div> -->
        <!-- end row -->
    <?php } else { ?>
        <div class="row">
            <div class="col-12 mt-4">
                <div class="card rounded shadow border-0 align-items-center">
                    <div class="col-lg-6 col-md-12 text-center">
                        <div class="alert alert-danger mt-3 d-inline-block" role="alert"><?= lang('Pages.error_manage_license_page_builtin_not_active') ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            /*******************
             * Handle dataTables
             ******************/   
            $('#license-list-table').DataTable({
                "ajax": {
                    "url": "<?= base_url('api/license/subscribers/' . $myConfig['General_Info_SecretKey']) ?>",
                    "type": "GET",
                    "dataSrc": "",
                    "beforeSend": function (xhr) {
                        xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        $('#loading-indicator').show();
                    },
                    "complete": function() {
                        $('#loading-indicator').hide();
                    }
                },
                "autoWidth": false,
                "width": "100%",
                "columns": [
                    { "data": null, "title": '<input type="checkbox" id="checkAll" class="form-check-input">' },
                    { "data": "id", "title": '<?= lang('Pages.ID') ?>', "visible": false },
                    { "data": "license_key", "title": '<?= lang('Pages.Key') ?>' },
                    { "data": "sent_to", "title": '<?= lang('Pages.Email_Address') ?>' },
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
                        "targets": 2,
                        "className": "text-muted"
                    },
                    {
                        "targets": "_all",
                        "className": "p-3"
                    },
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
                $('#license-list-table').DataTable().ajax.reload(null, false);
            }

            // Function to enable/disable the delete button based on checkbox state
            function updateDeleteButtonState() {
                var anyCheckboxChecked = $('tbody#license-list-tbody input[type="checkbox"]:checked').length > 0;
                $('#delete-license-submit').prop('disabled', !anyCheckboxChecked);
            }                

            // Check/uncheck all checkboxes when "checkAll" is clicked
            $('#checkAll').on('change', function () {
                var isChecked = $(this).prop('checked');
                $('tbody#license-list-tbody').find('input[type="checkbox"]').prop('checked', isChecked);
                updateDeleteButtonState();
            });

            // Check/uncheck "checkAll" based on the state of individual checkboxes
            $(document).on('change', 'tbody#license-list-tbody input[type="checkbox"]', function () {
                var allChecked = $('tbody#license-list-tbody input[type="checkbox"]:checked').length === $('tbody#license-list-tbody input[type="checkbox"]').length;
                $('#checkAll').prop('checked', allChecked);
                updateDeleteButtonState();
            });

            // Call the function initially to set the button state
            updateDeleteButtonState();                

            // Uncheck "checkAll" if any individual checkbox is unchecked
            $(document).on('change', 'tbody#license-list-tbody input[type="checkbox"]', function () {
                if (!$(this).prop('checked')) {
                    $('#checkAll').prop('checked', false);
                }
            });
            
            /***************************
            // Handle the delete license
            ***************************/
            $('#delete-license-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#delete-template-form');
                var submitButton = $(this);

                // Get the selected file names
                var selectedLicense = [];
                $('tbody#license-list-tbody input[type="checkbox"]:checked').each(function () {
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
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_subscriber') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('api/license/delete/subscriber/' . $myConfig['Manage_License_SecretKey']) ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        },                            
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
                            
                            submitButton.prop('disabled', true);
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
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