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
            <div class="card shadow rounded border-0">
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <!-- Language Dropdown -->
                        <div class="col-md-4 mb-3">
                            <!-- mb-3 here will apply margin-bottom in mobile view -->
                            <div class="form-group">
                                <label for="language-select" class="form-label"><?= lang('Pages.Select_Language') ?></label>
                                <select id="language-select" class="form-select">
                                    <option value=""><?= lang('Pages.Select_Option') ?></option>
                                    <!-- Languages will be loaded here -->
                                </select>
                            </div>
                        </div>

                        <!-- File Dropdown -->
                        <div class="col-md-4 mb-3">
                            <!-- mb-3 here ensures spacing below this block on mobile -->
                            <div class="form-group">
                                <label for="file-select" class="form-label"><?= lang('Pages.Select_File_label') ?></label>
                                <select id="file-select" class="form-select" disabled>
                                    <option value=""><?= lang('Pages.Select_Option') ?></option>
                                    <!-- Files will be loaded here -->
                                </select>
                            </div>
                        </div>

                        <!-- Button Group -->
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="btn-group flex-wrap">
                                <!-- flex-wrap allows buttons to wrap on small screens if needed -->
                                <button id="add-language-btn" class="btn btn-primary mb-2 me-2" data-bs-toggle="modal" data-bs-target="#add-language-modal">
                                    <i class="uil uil-plus"></i> <?= lang('Pages.Add') ?> <?= lang('Pages.Language') ?>
                                </button>
                                <button id="add-key-btn" class="btn btn-success mb-2 me-2" data-bs-toggle="modal" data-bs-target="#add-key-modal" disabled>
                                    <i class="uil uil-plus"></i> <?= lang('Pages.Add') ?> <?= lang('Pages.Key') ?>
                                </button>
                                <button id="add-file-btn" class="btn btn-info mb-2" data-bs-toggle="modal" data-bs-target="#add-file-modal" disabled>
                                    <i class="uil uil-plus"></i> <?= lang('Pages.Add') ?> <?= lang('Pages.File') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive rounded">
                        <table id="language-table" class="table table-center bg-white mb-0">
                            <thead>
                                <tr>
                                    <th class="border-bottom"><?= lang('Pages.Key') ?></th>
                                    <th class="border-bottom"><?= lang('Pages.Value') ?></th>
                                    <th class="border-bottom text-end"><?= lang('Pages.Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Language keys and values will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Add Language Modal -->
    <div class="modal fade" id="add-language-modal" tabindex="-1" aria-labelledby="add-language-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="add-language-modal-label"><?= lang('Pages.Add') ?> <?= lang('Pages.Language') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="add-language-form">
                        <div class="mb-3">
                            <label for="language-code" class="form-label"><?= lang('Pages.Language') ?> <?= lang('Pages.Code') ?> (ISO 639-1)</label>
                            <input type="text" class="form-control" id="language-code" placeholder="e.g., fr, de, it" required>
                            <small class="text-muted"><?= lang('Pages.use_iso_lang_code') ?></small>
                        </div>
                        <div class="mb-3">
                            <label for="base-language" class="form-label"><?= lang('Pages.Base_Language') ?></label>
                            <select id="base-language" class="form-select">
                                <option value=""><?= lang('Pages.Select_Option') ?></option>
                                <!-- Languages will be loaded here -->
                            </select>
                            <small class="text-muted"><?= lang('Pages.new_language_copy_base_lang') ?></small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="add-language-submit"><i class="uil uil-plus"></i> <?= lang('Pages.Add') ?> <?= lang('Pages.Language') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add File Modal -->
    <div class="modal fade" id="add-file-modal" tabindex="-1" aria-labelledby="add-file-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="add-file-modal-label"><?= lang('Pages.Add') ?> <?= lang('Pages.File') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="add-file-form">
                        <div class="mb-3">
                            <label for="file-name" class="form-label"><?= lang('Pages.File_Name') ?></label>
                            <input type="text" class="form-control" id="file-name" placeholder="e.g., Custom, API, Settings" required>
                            <small class="text-muted"><?= lang('Pages.lang_file_created_php') ?></small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="add-file-submit"><i class="uil uil-plus"></i> <?= lang('Pages.Add') ?> <?= lang('Pages.File') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Key Modal -->
    <div class="modal fade" id="add-key-modal" tabindex="-1" aria-labelledby="add-key-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="add-key-modal-label"><?= lang('Pages.Add') ?> <?= lang('Pages.Key') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="add-key-form">
                        <div class="mb-3">
                            <label for="key-name" class="form-label"><?= lang('Pages.Key') ?> <?= lang('Pages.Name') ?></label>
                            <input type="text" class="form-control" id="key-name" placeholder="e.g., welcome_message, error_text" required>
                        </div>
                        <div class="mb-3">
                            <label for="key-value" class="form-label"><?= lang('Pages.Value') ?></label>
                            <textarea class="form-control" id="key-value" rows="3" placeholder="Enter the translation value"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="add-key-submit"><i class="uil uil-plus"></i> <?= lang('Pages.Add') ?> <?= lang('Pages.Key') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Key Modal -->
    <div class="modal fade" id="edit-key-modal" tabindex="-1" aria-labelledby="edit-key-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="edit-key-modal-label"><?= lang('Pages.Edit') ?> <?= lang('Pages.Key') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="edit-key-form">
                        <input type="hidden" id="edit-key-name">
                        <div class="mb-3">
                            <label for="edit-key-display" class="form-label"><?= lang('Pages.Key') ?></label>
                            <input type="text" class="form-control" id="edit-key-display" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-key-value" class="form-label"><?= lang('Pages.Value') ?></label>
                            <textarea class="form-control" id="edit-key-value" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="delete-key-btn"><i class="uil uil-trash"></i> <?= lang('Pages.Delete') ?> <?= lang('Pages.Key') ?></button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="edit-key-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save') ?> <?= lang('Pages.Changes') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="confirm-delete-modal" tabindex="-1" aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="confirm-delete-modal-label"><?= lang('Pages.Confirm') ?> <?= lang('Pages.Delete') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <p id="confirm-delete-message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn"><i class="uil uil-trash"></i> <?= lang('Pages.Delete') ?></button>
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
            let languageTable;
            let currentLanguage = '';
            let currentFile = '';
            let deleteKeyName = '';
            
            // Initialize DataTable
            languageTable = $('#language-table').DataTable({
                "autoWidth": false,
                "width": "100%",
                "pagingType": "first_last_numbers",
                "columns": [
                    { "data": "key" },
                    { "data": "value" },
                    { 
                        "data": null,
                        "orderable": false,
                        "className": "text-end",
                        "render": function(data, type, row) {
                            return '<button class="btn btn-sm btn-primary edit-key-btn" data-key="' + row.key + '"><i class="uil uil-edit"></i> ' + '<?= lang('Pages.Edit') ?>' + '</button>';
                        }
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
                "responsive": true
            });
            
            // Load languages
            function loadLanguages() {
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/get-languages') ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const languageSelect = $('#language-select');
                            const baseLanguageSelect = $('#base-language');
                            
                            languageSelect.empty();
                            baseLanguageSelect.empty();
                            
                            languageSelect.append('<option value=""><?= lang('Pages.Select_Option') ?></option>');
                            baseLanguageSelect.append('<option value=""><?= lang('Pages.Select_Option') ?></option>');
                            
                            response.languages.forEach(function(language) {
                                languageSelect.append('<option value="' + language + '">' + language + '</option>');
                                baseLanguageSelect.append('<option value="' + language + '">' + language + '</option>');
                            });
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }
            
            // Load language files
            function loadLanguageFiles(language) {
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/get-files') ?>',
                    type: 'GET',
                    data: { language: language },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const fileSelect = $('#file-select');
                            
                            fileSelect.empty();
                            fileSelect.append('<option value=""><?= lang('Pages.Select_Option') ?></option>');
                            
                            response.files.forEach(function(file) {
                                fileSelect.append('<option value="' + file + '">' + file + '</option>');
                            });
                            
                            fileSelect.prop('disabled', false);
                            $('#add-file-btn').prop('disabled', false);
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }
            
            // Load language keys
            function loadLanguageKeys(language, file) {
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/get-keys') ?>',
                    type: 'GET',
                    data: { language: language, file: file },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            languageTable.clear();
                            
                            const keys = Object.keys(response.keys);
                            keys.sort();
                            
                            keys.forEach(function(key) {
                                languageTable.row.add({
                                    "key": key,
                                    "value": response.keys[key]
                                });
                            });
                            
                            languageTable.draw();
                            $('#add-key-btn').prop('disabled', false);
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }
            
            // Language select change event
            $('#language-select').change(function() {
                const language = $(this).val();
                
                if (language) {
                    currentLanguage = language;
                    loadLanguageFiles(language);
                    
                    // Reset file select and disable key button
                    $('#file-select').val('').prop('disabled', true);
                    $('#add-key-btn').prop('disabled', true);
                    
                    // Clear table
                    languageTable.clear().draw();
                }
            });
            
            // File select change event
            $('#file-select').change(function() {
                const file = $(this).val();
                
                if (file && currentLanguage) {
                    currentFile = file;
                    loadLanguageKeys(currentLanguage, file);
                } else {
                    // Clear table
                    languageTable.clear().draw();
                    $('#add-key-btn').prop('disabled', true);
                }
            });
            
            // Add language form submit
            $('#add-language-submit').click(function() {
                const languageCode = $('#language-code').val();
                const baseLanguage = $('#base-language').val();
                
                if (!languageCode) {
                    showToast('danger', '<?= lang('Pages.Please_Enter') ?> <?= lang('Pages.Language') ?> <?= lang('Pages.Code') ?>');
                    return;
                }
                
                if (!baseLanguage) {
                    showToast('danger', '<?= lang('Pages.Please_Select') ?> <?= lang('Pages.Base_Language') ?>');
                    return;
                }
                
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/add-language') ?>',
                    type: 'POST',
                    data: { 
                        language_code: languageCode,
                        base_language: baseLanguage
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#add-language-modal').modal('hide');
                            
                            // Reset form
                            $('#language-code').val('');
                            $('#base-language').val('');
                            
                            // Reload languages
                            loadLanguages();
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });
            
            // Add file form submit
            $('#add-file-submit').click(function() {
                const fileName = $('#file-name').val();
                
                if (!fileName) {
                    showToast('danger', '<?= lang('Pages.Please_Enter') ?> <?= lang('Pages.File_Name') ?>');
                    return;
                }
                
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/add-file') ?>',
                    type: 'POST',
                    data: { file_name: fileName },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#add-file-modal').modal('hide');
                            
                            // Reset form
                            $('#file-name').val('');
                            
                            // Reload files if language is selected
                            if (currentLanguage) {
                                loadLanguageFiles(currentLanguage);
                            }
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });
            
            // Add key form submit
            $('#add-key-submit').click(function() {
                const keyName = $('#key-name').val();
                const keyValue = $('#key-value').val();
                
                if (!keyName) {
                    showToast('danger', '<?= lang('Pages.Please_Enter') ?> <?= lang('Pages.Key') ?> <?= lang('Pages.Name') ?>');
                    return;
                }
                
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/add-key') ?>',
                    type: 'POST',
                    data: { 
                        language: currentLanguage,
                        file: currentFile,
                        key: keyName,
                        value: keyValue
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#add-key-modal').modal('hide');
                            
                            // Reset form
                            $('#key-name').val('');
                            $('#key-value').val('');
                            
                            // Reload keys
                            loadLanguageKeys(currentLanguage, currentFile);
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });
            
            // Edit key button click
            $(document).on('click', '.edit-key-btn', function() {
                const key = $(this).data('key');
                const value = languageTable.row($(this).closest('tr')).data().value;
                
                $('#edit-key-name').val(key);
                $('#edit-key-display').val(key);
                $('#edit-key-value').val(value);
                
                $('#edit-key-modal').modal('show');
            });
            
            // Edit key form submit
            $('#edit-key-submit').click(function() {
                const keyName = $('#edit-key-name').val();
                const keyValue = $('#edit-key-value').val();
                
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/update-key') ?>',
                    type: 'POST',
                    data: { 
                        language: currentLanguage,
                        file: currentFile,
                        key: keyName,
                        value: keyValue
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#edit-key-modal').modal('hide');
                            
                            // Reload keys
                            loadLanguageKeys(currentLanguage, currentFile);
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });
            
            // Delete key button click
            $('#delete-key-btn').click(function() {
                deleteKeyName = $('#edit-key-name').val();
                $('#confirm-delete-message').text('<?= lang('Pages.Are_you_sure_delete_key') ?> "' + deleteKeyName + '"?');
                $('#edit-key-modal').modal('hide');
                $('#confirm-delete-modal').modal('show');
            });
            
            // Confirm delete button click
            $('#confirm-delete-btn').click(function() {
                $.ajax({
                    url: '<?= base_url('admin-options/language-editor/delete-key') ?>',
                    type: 'POST',
                    data: { 
                        language: currentLanguage,
                        file: currentFile,
                        key: deleteKeyName
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#confirm-delete-modal').modal('hide');
                            
                            // Reload keys
                            loadLanguageKeys(currentLanguage, currentFile);
                        } else {
                            showToast('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            });
            
            // Load languages on page load
            loadLanguages();
        });           
    </script>   
<?= $this->endSection() //End section('scripts')?>