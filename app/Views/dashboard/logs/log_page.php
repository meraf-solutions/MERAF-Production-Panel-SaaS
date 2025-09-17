<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <div class="d-flex">
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>
            <?php if(!isset($error_message)) { ?>
                &nbsp;<a href="javascript:void(0)" id="downloadLink" data-url="<?= base_url('download-reports/'.str_replace('_', '-', $section).'') ?>" class="btn btn-icon btn-pills btn-outline-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.Download_the_log') ?>"><i data-feather="download" class="fea icon-sm"></i></a>
                &nbsp;<a href="javascript:void(0)" id="deleteLink" data-url="<?= base_url('delete-reports/'.str_replace('_', '-', $section).'') ?>" class="btn btn-icon btn-pills btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.Delete_the_log') ?>"><i data-feather="trash-2" class="fea icon-sm"></i></a>
            <?php } ?>
        </div>

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
            <?php if(isset($error_message)) { ?>
                <div class="card rounded shadow border-0">
                    <div class="alert alert-danger mt-3 mx-auto" role="alert"><?= $error_message ?></div>
                </div>
            <?php } else { ?>

                <div class="table-responsive shadow rounded p-4">
                    <table id="log-content-table" class="table table-center table-striped bg-white mb-0">
                        <thead>
                            <tr>
                                <th class="text-center border-bottom p-3"><?= lang('Pages.ID') ?></th>
                                <th class="text-center border-bottom p-3" style="min-width: 180px;"><?= lang('Pages.TIME') ?></th>
                                <th class="text-center border-bottom p-3"><?= lang('Pages.PRODUCT') ?></th>
                                <th class="text-center border-bottom p-3"><?= lang('Pages.DOMAIN_DEVICE') ?></th>
                                <th class="text-center border-bottom p-3"><?= lang('Pages.LICENSE') ?></th>
                                <th class="text-center border-bottom p-3"><?= lang('Pages.RESULT') ?></th>
                                <th class="text-center border-bottom p-3" style="min-width: 250px;"><?= lang('Pages.API_RESULT') ?></th>
                            </tr>
                        </thead>
                        <tbody id="log-content-tbody">  
                            <?php 
                            if(!$error_message) {
                                foreach ($logContent as $key => $logItem) { ?>
                                <tr>
                                    <td><?= $key ?></td>
                                    <td><?= $logItem[0] ?></td>
                                    <td><?= $logItem[1] ?></td>
                                    <td><?= $logItem[2] ?></td>
                                    <td><?= $logItem[3] ?></td>
                                    <td><?= $logItem[4] ?></td>
                                    <td>
                                        <?= $logItem[5] ?>
                                    </td>
                                </tr>             
                            <?php 
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>                               
                
            <?php } ?>
        </div><!--end col-->
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <?php if (!isset($error_message)): ?>
        <div class="modal fade" id="rowModal" tabindex="-1" aria-labelledby="rowModal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                <div class="modal-content rounded shadow border-0">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title" id="rowModal-title"><?= lang('Pages.Entry_number') ?></h5>
                        <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive shadow rounded p-4">
                            <table class="table table-center table-striped bg-white mb-0" id="individual-log-entry">
                                <tbody>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.ID') ?> </th>
                                        <td>  </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.TIME') ?> </th>
                                        <td>  </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.PRODUCT') ?> </th>
                                        <td>  </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.DOMAIN_DEVICE') ?> </th>
                                        <td>  </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.LICENSE') ?> </th>
                                        <td>  </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.RESULT') ?> </th>
                                        <td>  </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; width: 30%;"> <?= lang('Pages.API_RESULT') ?> </th>
                                        <td>  </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Save all data to an array
            var entriesData = [];
                <?php 
                if(!$error_message) {
                    foreach ($logContent as $key => $logItem) { ?>
                        var entryData<?= $key ?> = [
                            '<?= $key ?>',
                            '<?= $logItem[0] ?>',
                            '<?= $logItem[1] ?>',
                            '<?= $logItem[2] ?>',
                            '<?= $logItem[3] ?>',
                            '<?= $logItem[4] ?>',
                            '<?= $logItem[5] ?>'
                        ];
                        entriesData.push(entryData<?= $key ?>);
                <?php 
                    }
                }
                ?>

            /*********************
             * Initiate dataTables
             ********************/
            $('#log-content-table').DataTable({
                "autoWidth": false,
                "width": "100%",
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
                    "emptyTable": '<?= lang('Pages.DT_emptyTable') ?>',
                },
                "order": [[0, 'asc']],
                "columnDefs": [
                    {
                        "targets": 6, // Index of the fifth column (zero-based)
                        "render": function(data, type, row) {
                            // If the data length is greater than 70, truncate and add '...'
                            return data.length > 70 ? data.substr(0, 70) + '...' : data;
                        }
                    },
                    {
                        "targets": 1,
                        "render": function(data) {
                            return formatDateTime(data);
                        }
                    },
                    {
                        "targets": 4,
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
                        "targets": [0,1,3,6],
                        "className": "text-muted"
                    },
                    {
                        "targets": 5,
                        "render": function (data, type, row) {
                            return `<span class="badge bg-${data === 'Invalid' ? 'danger' : 'success'} me-2 mt-2">${data}</span>`;
                        }
                    },
                ],
                "pageLength": 25,
                "responsive": true
            });

            /**********************************
            // Handle the download log requests
            **********************************/
            $('#downloadLink').click(function(e) {
                e.preventDefault();

                // Get the URL from the data-url attribute
                var downloadUrl = $(this).data('url');

                // Make an AJAX request
                $.ajax({
                    url: downloadUrl,
                    method: 'GET',
                    dataType: 'json', // Specify the expected data type
                    success: function(response) {
                        // Check if the server responded with an error
                        if (response.hasOwnProperty('error')) {
                            // Show Bootstrap error (you can replace this with your own error handling logic)
                            showToast('danger', '<?= lang('Pages.Server_error') ?>: ' + response.error);
                        } else {
                            // Decode base64-encoded content
                            var decodedContent = atob(response.fileContent);

                            // Create a Blob from the decoded content
                            var blob = new Blob([decodedContent], { type: 'application/octet-stream' });

                            // Create a temporary link element to trigger the download
                            var link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = response.fileName + '.' + response.fileFormat;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });

            /**********************************
            // Handle the delete log requests
            **********************************/
            $('#deleteLink').click(function(e) {
                e.preventDefault();

                // Get the URL from the data-url attribute
                var deleteUrl = $(this).data('url');

                var confirmDelete = confirm("<?= lang('Pages.confirm_delete_log') ?>");

                if (confirmDelete) {                
                    // Make an AJAX request
                    $.ajax({
                        url: deleteUrl,
                        method: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {		
                                toastType = 'success';
                                $('#log-content-tbody').hide();
                            } else if (response.status == 2) {     
                                toastType = 'info';
                                $('#log-content-tbody').hide();
                            } else {
                                toastType = 'danger';
                            }

                            showToast(toastType, response.msg);
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
                            disableLoadingEffect(submitButton);
                        }
                    });
                }
            });

            /***************************************************
             * Able to open modal by clicking in any rows
             **************************************************/
            // Function to recursively parse and display nested objects
            function parseNestedObjects(obj) {
                var html = '';
                for (var key in obj) {
                    if (typeof obj[key] === 'object') {
                        html += '&nbsp;&nbsp;&nbsp;<strong> <i class="mdi mdi-devices"> </i> ' + key + ':</strong><br>';
                        html += parseNestedObjects(obj[key]) + '<br>'; // Recursively call the function for nested objects
                    } else {
                        html += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' + key + ':</strong> ' + obj[key] + '<br>';
                    }
                }
                return html;
            }

            $('#log-content-table').on('click', 'tbody tr', function(event) {

                // Retrieve data of the clicked row
                var rowData = [];

                var rowID = $(this).find('td:first').map(function() {
                    return $(this).text();
                }).get();

                rowData = entriesData[rowID-1]; // store the data to populate in the modal

                // Modal title
                $('#rowModal-title').text('ID: ' + rowData[0] + ' | Time: ' + formatDateTime(rowData[1]));

                // Populate the <td> elements in the modal with the rowData
                $('#individual-log-entry tbody tr td').each(function(index) {
                    // Set the text content of the second <td> element in each row to the corresponding value from rowData
                    if(index === 1) {
                        // $(this).text(rowData[index]);
                        $(this).text(formatDateTime(rowData[index]));
                    }
                    else if (index === 6 || index === 7) { // Assuming the JSON string is at index 6 or 7 in rowData
                        var jsonData = JSON.parse(rowData[index]);
                        var html = '';
                        for (var key in jsonData) {
                            if (typeof jsonData[key] === 'object') {
                                html += '<strong>' + key + ':</strong> <br>'
                                html += parseNestedObjects(jsonData[key]); // Call the function for nested objects
                            }
                            else {
                                html += '<strong>' + key + ':</strong> ' + jsonData[key] + key +'<br>';
                            }
                        }
                        $(this).html(html);
                    }
                    else {
                        $(this).text(rowData[index]);
                    }
                });
                
                // Open the modal
                $('#rowModal').modal('show');
            });
            
        });
    </script>    
<?= $this->endSection() //End section('scripts')?>