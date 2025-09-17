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
            
            <form novalidate action="javascript:void(0)" id="delete-template-form">
                <div class="table-responsive shadow rounded p-4">
                    <table id="user-list-table" class="table table-center table-striped bg-white mb-0 text-center">
                        <thead>
                        </thead>
                        <tbody id="user-list-tbody">
                        </tbody>
                    </table>
                </div>
            </form>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Edit_User_Details') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="edit-user-id" name="user_id">
                        <div class="mb-3">
                            <label for="edit-username" class="form-label"><?= lang('Pages.Username') ?></label>
                            <input type="text" class="form-control" id="edit-username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label"><?= lang('Pages.Email') ?></label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-first_name" class="form-label"><?= lang('Pages.First_Name') ?></label>
                            <input type="text" class="form-control" id="edit-first_name" name="first_name">
                        </div>
                        <div class="mb-3">
                            <label for="edit-last_name" class="form-label"><?= lang('Pages.Last_Name') ?></label>
                            <input type="text" class="form-control" id="edit-last_name" name="last_name">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="saveUserDetailsBtn"><i class="uil uil-save"></i> <?= lang('Pages.Save_Changes') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Change_User_Password') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <input type="hidden" id="change-password-user-id" name="user_id">
                        <div class="mb-3">
                            <label for="new-password" class="form-label"><?= lang('Pages.new_password') ?></label>
                            <input type="password" class="form-control" id="new-password" name="new_password" required>
                            <small class="form-text text-muted">
                                <?= lang('Pages.password_should_contain') ?>
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-new-password" class="form-label"><?= lang('Pages.Confirm_New_Password') ?></label>
                            <input type="password" class="form-control" id="confirm-new-password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="changePasswordBtn"><i class="uil uil-save"></i> <?= lang('Pages.change_password') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Role Modal -->
    <div class="modal fade" id="userRoleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Manage_User_Role') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="userRoleForm">
                        <input type="hidden" id="role-user-id" name="user_id">
                        <div class="mb-3">
                            <label class="form-label"><?= lang('Pages.Select_User_Group') ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="group" id="user-group" value="user">
                                <label class="form-check-label" for="user-group"><?= lang('Pages.User') ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="group" id="admin-group" value="admin">
                                <label class="form-check-label" for="admin-group"><?= lang('Pages.Admin') ?></label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="saveUserRoleBtn"><i class="uil uil-save"></i> <?= lang('Pages.Save_Role') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- API Key Modal -->
    <div class="modal fade" id="apiKeyModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Manage_API_Key') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="api-key-user-id" name="user_id">
                    <div id="api-key-status">
                        <p><?= lang('Pages.Current_API_Key_Status') ?> <span id="current-api-key-status"><?= lang('Pages.Not_Generated') ?></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="generateApiKeyBtn"><i class="uil uil-plus"></i> <?= lang('Pages.Generate_API_Key') ?></button>
                    <button type="button" class="btn btn-danger" id="revokeApiKeyBtn"><i class="uil uil-shield-slash"></i> <?= lang('Pages.Revoke_API_Key') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Subscription Modal -->
    <div class="modal fade" id="manageSubscriptionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Pages.Manage_User_Subscription') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="manageSubscriptionForm">
                        <input type="hidden" id="subscription-user-id" name="user_id">
                        <div class="mb-3">
                            <label for="package-select" class="form-label"><?= lang('Pages.Package') ?></label>
                            <select class="form-control" id="package-select" name="package_id" required>
                                <option value=""><?= lang('Pages.Select_Package') ?></option>
                                <?php
                                $validityDurationMap = [
                                    'day' => [
                                        'singular' => lang('Pages.Day'),
                                        'plural' => lang('Pages.Days')
                                    ],
                                    'month' => [
                                        'singular' => lang('Pages.Month'),
                                        'plural' => lang('Pages.Months')
                                    ],
                                    'year' => [
                                        'singular' => lang('Pages.Year'),
                                        'plural' => lang('Pages.Years')
                                    ],
                                    'lifetime' => lang('Pages.Lifetime')
                                ];
                                
                                foreach ($packages as $package):                                    
                                    $durationText = '';
                                    if ($package['validity_duration'] === 'lifetime') {
                                        $durationText = $validityDurationMap['lifetime'];
                                    } else {
                                        $durationLabel = $package['validity'] > 1 ? 'plural' : 'singular';
                                        $durationText = $package['validity'] . ' ' . $validityDurationMap[$package['validity_duration']][$durationLabel];
                                    }
                                ?>
                                    <option value="<?= $package['id'] ?>">
                                        <?= $package['package_name'] ?> | 
                                        <?= $durationText ?> | 
                                        <?= $package['price'] === '0.00' ? lang('Pages.Free') : $package['price'] . ' ' . $myConfig['packageCurrency'] ?>    
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="expiry-date" class="form-label"><?= lang('Pages.Expiry_Date') ?></label>
                            <input type="text" class="form-control" id="expiry-date" name="expiry_date" required>
                        </div>
                    </form>

                    <div class="alert alert-warning mt-4">
                        <h5><i class="icon uil uil-info-circle"></i> <?= lang('Pages.heading_notice_change_user_package_admin') ?></h5>

                        <p class="mb-0">
                        <?= lang('Pages.body_notice_change_user_package_admin') ?>
                        </p>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="saveSubscriptionBtn"><i class="uil uil-save"></i> <?= lang('Pages.Save_Subscription') ?></button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Flatpickr
            const flatpickrInstance = flatpickr("#expiry-date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i:S",
                time_24hr: true
            });

            // Define a mapping between original values and their corresponding language strings
            var languageMapping = {
                'active': '<?= lang('Pages.Active') ?>',
                'cancelled': '<?= lang('Pages.Cancelled') ?>',
                'pending': '<?= lang('Pages.Pending') ?>',
                'expired': '<?= lang('Pages.Expired') ?>',
                'inactive': '<?= lang('Pages.Inactive') ?>',
                'deleted': '<?= lang('Pages.Inactive') ?>',
            };

            // Package data
            const packages = <?= json_encode($packages) ?>;

            /*******************
             * Handle dataTables
             ******************/
            // Function to parse URL parameters
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            };

            // Store the search value
            var searchValue = getUrlParameter('s');

            const userListTable = $('#user-list-table').DataTable({
                "ajax": {
                    "url": "<?= base_url('api/user/all/' . $myConfig['Manage_License_SecretKey']) ?>",
                    "type": "GET",
                    "dataSrc": function(json) {
                        // Transform object into array
                        return Object.entries(json).map(([id, data]) => ({
                            id: id,
                            ...data,
                            action: data.deleted_at
                                    ? '<span class="badge bg-soft-dark me-2 mt-2"><?= lang('Pages.User_Deleted') ?></span>'
                                    : '<div class="btn-group">' +
                                        '<a href="javascript:void(0)" class="edit-user-action text-info px-1 h4" data-user-id="' + id + '" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Edit_User') ?>"><i class="mdi mdi-account-credit-card"> </i> </a>' + 
                                        '<a href="javascript:void(0)" class="change-user-password-action text-warning px-1 h4" data-user-id="' + id + '" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Change_User_Password') ?>"><i class="mdi mdi-lock-plus"> </i> </a>' + 
                                        '<a href="javascript:void(0)" class="manage-user-role-action text-primary px-1 h4" data-user-id="' + id + '" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Manage_User_Role') ?>"><i class="mdi mdi-account-group"> </i> </a>' + 
                                        '<a href="javascript:void(0)" class="manage-api-key-action text-success px-1 h4" data-user-id="' + id + '" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Manage_API_Key') ?>"><i class="mdi mdi-key"> </i> </a>' + 
                                        '<a href="javascript:void(0)" class="manage-subscription-action text-warning px-1 h4" data-user-id="' + id + '" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Manage_Subscription') ?>"><i class="mdi mdi-package-variant-closed"> </i> </a>' + 
                                        '<a href="javascript:void(0)" class="delete-user-action text-danger px-1 h4" data-user-id="' + id + '" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Delete_User') ?>"><i class="mdi mdi-trash-can"> </i> </a>' +
                                    '</div>'
                        }));
                    },
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
                "pagingType": "first_last_numbers",
                "columns": [
                    { 
                        "data": null,
                        "title": '<input type="checkbox" id="checkAll" class="form-check-input">',
                        "orderable": false,
                        "className": "text-center align-middle",
                        "render": function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input user-checkbox" value="' + row.id + '">';
                        },
                        // "render": function(data, type, row) {
                        //     if (row.deleted_at) {
                        //         return ''; // Empty cell for soft-deleted users
                        //     }
                        //     return '<input type="checkbox" class="form-check-input user-checkbox" value="' + row.id + '">';
                        // },
                        "visible": false
                    },
                    { 
                        "data": "id", 
                        "title": '<?= lang('Pages.ID') ?>',
                        "className": "text-center align-middle",
                        "visible": false,
                        "orderable": false
                    },
                    { 
                        "data": "avatar",
                        "title": '<?= lang('Pages.Avatar') ?>',
                        "className": "text-center align-middle",
                        "render": function(data, type, row) {
                            if (data) {
                                return '<img src="<?= base_url('writable/uploads/user-avatar/') ?>' + data + '" class="avatar avatar-ex-small rounded" alt="avatar">';
                            }
                            return '<img src="<?= base_url('writable/uploads/user-avatar/default-avatar.jpg') ?>" class="avatar avatar-ex-small rounded" alt="avatar">';
                        },
                        "orderable": false
                    },
                    { 
                        "data": "username", 
                        "title": '<?= lang('Pages.Username') ?>',
                        "className": "text-center align-middle"
                    },
                    { 
                        "data": "email", 
                        "title": '<?= lang('Pages.Email') ?>',
                        "className": "text-center align-middle text-muted",
                        "orderable": false
                    },
                    { 
                        "data": "registered",
                        "title": '<?= lang('Pages.Registered') ?>',
                        "className": "text-center align-middle text-muted",
                        "render": function(data) {
                            return formatDateTime(data);
                        }
                    },
                    { 
                        "data": "package", 
                        "title": '<?= lang('Pages.Package') ?>',
                        "className": "text-center align-middle",
                        "render": function(data, type, row) {
                            return data || 'No Package';
                        }
                    },
                    {
                        "data": "status",
                        "title": '<?= lang('Pages.Status') ?>',
                        "className": "text-center align-middle",
                        "render": function(data, type, row) {
                            var statusClass = {
                                'active': 'bg-success',
                                'pending': 'bg-warning',
                                'expired': 'bg-dark text-light',
                                'cancelled': 'bg-danger',
                                'inactive': 'bg-dark text-light'
                            };
                            if (type === 'display') {
                                // Default to 'inactive' if data is undefined or not in statusClass
                                const status = (data && statusClass.hasOwnProperty(data)) ? data : 'inactive';
                                const className = statusClass[status];
                                // Use languageMapping[status] if it exists, otherwise use status directly
                                const displayText = languageMapping[status] || status;
                                return '<span class="badge ' + className + ' me-2 mt-2">' + displayText + '</span>';
                            } else {
                                return data || '<?= lang('Pages.inactive') ?>';
                            }
                        }
                    },
                    { 
                        "data": "package_expiry", 
                        "title": '<?= lang('Pages.Package_Expiry') ?>',
                        "className": "text-center align-middle",
                        "render": function(data, type, row) {
                            return data !== 'N/A' ? formatDateTime(data) : 'N/A';
                        }
                    },
                    { 
                        "data": "action", 
                        "title": '<?= lang('Pages.Action') ?>',
                        "className": "text-center align-middle",
                        "orderable": false
                    },
                    { 
                        "data": "last_login",
                        "title": '<?= lang('Pages.Last_Login') ?>',
                        "className": "text-center align-middle text-muted",
                        "render": function(data) {
                            return data !== 'N/A' ? formatDateTime(data) : 'N/A';
                        }
                    },
                    { 
                        "data": "last_ip", 
                        "title": '<?= lang('Pages.Last_IP') ?>',
                        "className": "text-center align-middle text-muted",
                        "orderable": false
                    }
                ],
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
                "pageLength": 25,
                "responsive": true,
                "initComplete": function(settings, json) {
                    // Apply the search after DataTable initialization
                    if (searchValue !== '') {
                        this.api().search(searchValue).draw();
                    }
                }
            });

            // Hide the sorter icon in the first column
            var firstTh = $('thead tr th:first');
            firstTh.find('span.dt-column-order').hide();                   

            // Handle "Check All" functionality
            $('#checkAll').on('change', function() {
                $('.user-checkbox').prop('checked', $(this).prop('checked'));
            });
			
			// Edit User Action
            $(document).on('click', '.edit-user-action', function() {
				const userId = $(this).data('user-id');
				
				// Retrieve the full data for the specific row
				const userData = userListTable.rows().data().toArray().find(row => row.id == userId);
				
				if (userData) {
					$('#edit-user-id').val(userId);
					$('#edit-username').val(userData.username);
					$('#edit-email').val(userData.email);
					$('#edit-first_name').val(userData.first_name || '');
					$('#edit-last_name').val(userData.last_name || '');
					
					// Fetch additional user details if first_name and last_name are not in the table data
					if (!userData.first_name && !userData.last_name) {
						$.ajax({
							url: '<?= base_url('admin-options/user-manager/get-user-details') ?>',
							method: 'POST',
							data: { user_id: userId },
							dataType: 'json',
							success: function(response) {
								if (response.success) {
									$('#edit-first_name').val(response.first_name || '');
									$('#edit-last_name').val(response.last_name || '');
								}
							},
							error: function (xhr, status, error) {
								// Show error toast
                                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
							},
                            complete: function () {
                                $('#editUserModal').modal('show');
                            }
						});
					}
				} else {
                    showToast('danger', '<?= lang('Pages.User_data_not_found') ?>');
				}
			});

            // Save User Details
            $('#saveUserDetailsBtn').on('click', function() {
                var submitButton = $(this);

                enableLoadingEffect(submitButton);

                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/update-user-details') ?>',
                    method: 'POST',
                    data: $('#editUserForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#editUserModal').modal('hide');
                            userListTable.ajax.reload();
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
            });

            // Change Password Action
            $(document).on('click', '.change-user-password-action', function() {
                const userId = $(this).data('user-id');
                $('#change-password-user-id').val(userId);
                $('#changePasswordModal').modal('show');
            });

            // Change Password Validation and Submit
            $('#changePasswordBtn').on('click', function() {
                const newPassword = $('#new-password').val();
                const confirmPassword = $('#confirm-new-password').val();
                var submitButton = $(this);

                enableLoadingEffect(submitButton);

                if (newPassword !== confirmPassword) {
                    showToast('danger', '<?= lang('Pages.Passwords_do_not_match') ?>');
                    disableLoadingEffect(submitButton);
                    return;
                }

                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/change-user-password') ?>',
                    method: 'POST',
                    data: $('#changePasswordForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#changePasswordModal').modal('hide');
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
            });

            // Manage User Role Action
            $(document).on('click', '.manage-user-role-action', function() {
                const userId = $(this).data('user-id');
                $('#role-user-id').val(userId);

                // Fetch user's current group
                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/get-user-group') ?>',
                    method: 'POST',
                    data: { user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Reset radio buttons
                            $('input[name="group"]').prop('checked', false);
                            
                            // Select the current user group
                            $(`input[name="group"][value="${response.group}"]`).prop('checked', true);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    },
                    complete: function() {
                        $('#userRoleModal').modal('show');
                    }
                });
            });

            // Save User Role
            $('#saveUserRoleBtn').on('click', function() {
                var submitButton = $(this);

                enableLoadingEffect(submitButton);

                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/set-user-group') ?>',
                    method: 'POST',
                    data: $('#userRoleForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#userRoleModal').modal('hide');
                            userListTable.ajax.reload();
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
            });

            // Manage API Key Action
            $(document).on('click', '.manage-api-key-action', function() {
                const userId = $(this).data('user-id');
                $('#api-key-user-id').val(userId);
                
                // Check current API key status
                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/get-user-api-key') ?>',
                    method: 'POST',
                    data: { user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        $('#current-api-key-status').text(
                            response.api_key ? '<?= lang('Pages.Generated') ?>' : '<?= lang('Pages.Not_Generated') ?>'
                        );
                    }
                });
                
                $('#apiKeyModal').modal('show');
            });

            // Generate API Key
            $('#generateApiKeyBtn').on('click', function() {
                var submitButton = $(this);

                enableLoadingEffect(submitButton);

                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/generate-user-api-key') ?>',
                    method: 'POST',
                    data: { user_id: $('#api-key-user-id').val() },
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#current-api-key-status').text('<?= lang('Pages.Generated') ?>');
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
            });

            // Revoke API Key
            $('#revokeApiKeyBtn').on('click', function() {
                var submitButton = $(this);

                enableLoadingEffect(submitButton);

                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/revoke-user-api-key') ?>',
                    method: 'POST',
                    data: { user_id: $('#api-key-user-id').val() },
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#current-api-key-status').text('<?= lang('Pages.Not_Generated') ?>');
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
            });

            // Delete User Action
            $(document).on('click', '.delete-user-action', function() {
                const userId = $(this).data('user-id');
                
                // Confirm deletion
                if (confirm('<?= lang('Pages.confirm_delete_user') ?>')) {
                    $.ajax({
                        url: '<?= base_url('admin-options/user-manager/delete-user') ?>',
                        method: 'POST',
                        data: { user_id: userId },
                        dataType: 'json',
                        success: function(response) {
                            let toastType = 'info';

                            if (response.success) {
                                toastType = 'success';
                                userListTable.ajax.reload();
                            } else {
                                toastType = 'danger';
                            }

                            showToast(toastType, response.msg);
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        }
                    });
                }
            });

            // Hide the sorter icon in the first column
            var firstTh = $('thead tr th:first');
            firstTh.find('span.dt-column-order').hide();                   

            // Handle "Check All" functionality
            $('#checkAll').on('change', function() {
                $('.user-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Manage Subscription Action
            $(document).on('click', '.manage-subscription-action', function() {
                const userId = $(this).data('user-id');
                $('#subscription-user-id').val(userId);

                // Retrieve the full data for the specific row
                const userData = userListTable.rows().data().toArray().find(row => row.id == userId);
                
                if (userData) {
                    // Find the package in the packages array that matches the user's current package
                    const userPackage = packages.find(pkg => pkg.package_name === userData.package);
                    
                    if (userPackage) {
                        $('#expiry-date').val('').prop('disabled', false);
                        $('#saveSubscriptionBtn').prop('disabled', false);
                        
                        // Set the selected option in the dropdown
                        $('#package-select').val(userPackage.id);
                        
                        // Trigger the change event to update the expiry date
                        $('#package-select').trigger('change');
                    }
                    console.log('User package expiry: ' + userData.package_expiry );
                    // Set the expiry date if available
                    if (userData.package_expiry && userData.package_expiry !== 'N/A') {
                        flatpickrInstance.setDate(userData.package_expiry);
                    }
                    else {
                        $('#package-select').val('');
                        $('#expiry-date').val('').prop('disabled', 'disabled');
                        $('#saveSubscriptionBtn').prop('disabled', 'disabled');
                    }
                }

                $('#manageSubscriptionModal').modal('show');
            });

            // Calculate expiry date based on package selection
            $('#package-select').on('change', function() {
                const selectedPackageId = $(this).val();
                const selectedPackage = packages.find(pkg => pkg.id == selectedPackageId);
                
                if (selectedPackage) {
                    $('#expiry-date').val('').prop('disabled', false);
                    $('#saveSubscriptionBtn').prop('disabled', false);
                    
                    const now = new Date();
                    let expiryDate = new Date(now);

                    if (selectedPackage.validity_duration === 'lifetime') {
                        expiryDate.setFullYear(expiryDate.getFullYear() + 100); // Set to 100 years from now for lifetime
                    } else {
                        const validity = parseInt(selectedPackage.validity);
                        switch (selectedPackage.validity_duration) {
                            case 'day':
                                expiryDate.setDate(expiryDate.getDate() + validity);
                                break;
                            case 'week':
                                expiryDate.setDate(expiryDate.getDate() + (validity * 7));
                                break;
                            case 'month':
                                expiryDate.setMonth(expiryDate.getMonth() + validity);
                                break;
                            case 'year':
                                expiryDate.setFullYear(expiryDate.getFullYear() + validity);
                                break;
                        }
                    }

                    flatpickrInstance.setDate(expiryDate);
                }
                else {
                    $('#expiry-date').val('').prop('disabled', 'disabled');
                    $('#saveSubscriptionBtn').prop('disabled', 'disabled');
                }
            });

            // Save Subscription
            $('#saveSubscriptionBtn').on('click', function() {
                var submitButton = $(this);

                enableLoadingEffect(submitButton);

                $.ajax({
                    url: '<?= base_url('admin-options/user-manager/update-user-subscription') ?>',
                    method: 'POST',
                    data: $('#manageSubscriptionForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#manageSubscriptionModal').modal('hide');
                            userListTable.ajax.reload();
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
            });
        });           
    </script>   
<?= $this->endSection() //End section('scripts')?>