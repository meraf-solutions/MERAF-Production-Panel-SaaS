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

        <!-- Upload email logo -->
        <div class="col-xl-5 mt-4">
            <div class="card rounded shadow p-4 border-0 mb-3">
                <h4 class="mb-3"><?= lang('Pages.Email_Body_Logo') ?></h4>

                <p class="small text-info mb-0"><?= lang('Pages.max_height') ?> 60 px</p>
                <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png, *.gif</p>
                <p id="logoWrapper" <?= empty($emailLogoFile) ? 'class="d-none"' : '' ?>>
                <img class="email_logoPreview" src="<?= !empty($emailLogoFile) ? esc($emailLogoFile) : '' ?>" height="60" alt="<?= esc($userData->username) ?>">
                </p>

                <form class="mb-3" novalidate action="javascript:void(0)" id="upload-email-logo-form">
                    <div class="row gy-3">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <div class="form-icon position-relative input-group input-group-sm">
                                            <span class="input-group-text">
                                                <a href="javascript:void(0)" class="delete-saved-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.delete_saved_image') ?>"><i class="mdi mdi-delete"> </i> </a>
                                                <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"> </i> </a> 
                                            </span>
                                            &nbsp;
                                            <input class="form-control" name="email_logo" id="email_logo" type="file" accept=".jpg,.jpeg,.png,.gif">
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.image_format_requirement') ?>
                                            </div>                                                                        
                                        </div>
                                        
                                    </div>
                                </div><!--end col--> 															
                            </div><!--end row-->
                        </div>

                        <div class="col-12 text-center">
                            <button class="mx-auto btn btn-primary" id="upload-logo-email-logo-submit"><i class="uil uil-arrow-circle-up"></i> <?= lang('Pages.Upload_Email_Logo') ?></button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Upload email template package -->
        <div class="col-xl-7 mt-md-4">
            <div class="card rounded shadow p-4 border-0 mb-3">
                <h4 class="mb-3"><?= lang('Pages.Upload_Templates') ?></h4>
                <p class="small text-info">
                    <?= lang('Pages.Upload_Templates_Desc') ?>
                </p>
                <p class="small text-info">
                    <?= lang('Pages.Upload_Response_Msg') ?>
                </p>
                
                <form class="mb-3" novalidate action="javascript:void(0)" id="upload-email-template-form">
                    <div class="row gy-3">

                        <div class="col-12">
                            <input class="form-control" name="emailTemplate-Package" id="emailTemplate-Package" type="file" accept=".zip" required>
                            <div class="invalid-feedback">
                                <?= lang('Pages.Upload_Response_Msg') ?>
                            </div>
                        </div>

                        <div class="col-12 text-center">
                            <button class="mx-auto btn btn-primary" id="upload-email-template-submit"><i class="uil uil-arrow-circle-up"></i> <?= lang('Pages.Upload_Email_Template') ?></button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-6 mt-md-4">
            <div class="card rounded shadow p-4 border-0 mb-3">
                <h4 class="mb-3"><?= lang('Pages.Manage_Templates') ?></h4>
                <div class="row g-3">
                    
                    <div class="col-12 no-templates-responseMsg" <?= count($userEmailTemplates) !== 0 ? 'style="display:none"' : '' ?>>
                        <div class="alert bg-soft-primary fade show text-center" role="alert"><?= lang('Pages.No_Templates_Found') ?></div>
                    </div>                                        

                    <form novalidate="" action="javascript:void(0)" id="delete-template-form">                                            
                        <div class="col-12 table-responsive rounded" id="table-file-wrapper" <?= count($userEmailTemplates) !== 0 ? '' : 'style="display:none"' ?>>

                            <table class="table table-striped mb-3">
                                <thead>
                                    <tr>
                                        <th class="p-3 border-bottom" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="checkAll-Templates">
                                            </div>
                                        </th>
                                        <th class="border-bottom" style="min-width: 200px;"><?= lang('Pages.Template_Name') ?></th>
                                    </tr>
                                </thead>

                                <tbody id="template-file-list">

                                </tbody>
                            </table>                                        
                            <input type="hidden" id="selectedTemplates" name="selectedTemplates" value=""> 
                            
                            <div class="col-12 text-center">
                                <button class="mx-auto btn btn-outline-danger mb-3" id="delete-template-submit" disabled><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>
                            </div>
                        </div>  
                    </form>                                  
                </div>
            </div> 
        </div>

        <div class="col-xl-6 mt-md-4">
            <div class="card rounded shadow p-4 border-0">
                <h4 class="mb-3"><?= lang('Pages.Set_Associated_Products') ?></h4>
                <div class="row g-3">
                    <div class="col-12 no-templates-responseMsg" <?= count($userEmailTemplates) !== 0 ? 'style="display:none"' : '' ?>>
                        <div class="alert bg-soft-primary fade show text-center" role="alert"><?= lang('Pages.No_Templates_Found') ?></div>
                    </div>  

                    <div class="col-12" id="select-file-wrapper" <?= count($userEmailTemplates) !== 0 ? '' : 'style="display:none"' ?>>
                        <label for="template_select" class="form-label"><?= lang('Pages.Select_Email_Template') ?></label>
                        <select class="form-select form-control" id="template_select" name="template_select" onchange="showProductsearchTemplate()" required>

                        </select>
                    </div>

                    <div class="col-12" id="templateWrapper" <?= count($userEmailTemplates) !== 0 ? '' : 'style="display:none"' ?>>

                    </div>

                    <form novalidate action="javascript:void(0)" id="set-product-template-form" <?= count($userEmailTemplates) !== 0 ? '' : 'style="display:none"' ?> >

                    </form>

                    <div class="col-12 text-center">
                        <button class="mx-auto btn btn-primary" id="set-email-template-submit" <?= count($userEmailTemplates) !== 0 ? '' : 'style="display:none"' ?>><i class="uil uil-save"></i> <?= lang('Pages.Save') ?></button>
                    </div>                                                                          
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            /***********************************
            // Handle the Upload Email Logo
            ***********************************/
            $('#upload-logo-email-logo-submit').on('click', function (e) {
                e.preventDefault();						

                var form = $('#upload-email-logo-form');
                var logoFile = $('#email_logo');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // File attachment validation
                if (logoFile.val() === '') {
                    logoFile.addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                }

                // Check if the file extension is valid
                var fileName = logoFile.val().split('\\').pop();
                var fileExtension = fileName.split('.').pop().toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    logoFile.addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                }

                // Form data is valid, proceed with AJAX submission
                var formData = new FormData(form[0]);

                $.ajax({
                    url: '<?= base_url('email-service/template/upload-email-logo-action') ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,					
                    success: function (response) {
                        resetValidations(form);
                        logoFile.val('');

                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            $('#logoWrapper').removeClass('d-none').find('img').attr('src', response.logoString);
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

            /*******************************
             // Handle the delete email logo
             *******************************/
            $('.delete-saved-media').on('click', function (e) {
                e.preventDefault();

                var logoWrapper = $('#logoWrapper');

                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Notifications.confirmation_to_delete_logo') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('email-service/template/delete-email-logo-action') ?>',
                        method: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            let toastType = 'info';

                            if (response.success) {
                                toastType = 'success';

                                // Hide the logo preview
                                logoWrapper.addClass('d-none').find('img').attr('src', '');
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

            /***********************************
            // Handle the Upload Email Templates
            ***********************************/
            $('#upload-email-template-submit').on('click', function (e) {
                e.preventDefault();						

                var form = $('#upload-email-template-form');
                var templateFile = $('#emailTemplate-Package');
                var submitButton = $(this);		

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                /*******************
                 * Start validations
                 ******************/
                // File attachment validation
                if (templateFile.val() === '') {
                    templateFile.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    // Check if the file extension is .zip
                    var fileName = templateFile.val().split('\\').pop(); // Get the file name from the path
                    var fileExtension = fileName.split('.').pop().toLowerCase();

                    if (fileExtension !== 'zip') {
                        templateFile.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else {
                        templateFile.addClass('is-valid');
                    }
                }    			

                /*****************
                 * End validations
                 ****************/

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Form data is valid, proceed with further processing
                    var data = new FormData(form[0]);

                    // Append additional data to the FormData object
                    data.append('templateFile', templateFile[0].files[0]);

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('email-service/template/upload-template-action') ?>',
                        method: 'POST',
                        // data: form.serialize(),
                        data: data,
                        processData: false,
                        contentType: false,					
                        success: function (response) {
                            resetValidations(form);

                            // Clear the choose file button
                            templateFile.val('');

                            let toastType = 'info';

                            if (response.success === true && response.status === 1) {
                                toastType = 'success';

                                // hide no template notif
                                $('.no-templates-responseMsg').slideUp();

                                // update and show the table
                                updateManageTemplates();

                                $('#table-file-wrapper').slideDown();

                                // Show the select option
                                $('#select-file-wrapper').slideDown();

                                // Show the product selection and Save button
                                updateSelectOptions();
                            } else if(response.success === false && response.status === 2) {
                                toastType = 'info';
                            } else {
                                toastType = 'danger';
                            }


                            if (typeof response.msg === 'object') {
                                // If msg is an object (likely validation errors), display each error message in a separate alert
                                Object.keys(response.msg).forEach(function (key) {
                                    showToast(toastType, response.msg[key]);
                                });
                            } else {
                                // If not an object, display it as a single alert
                                showToast(toastType, response.msg);
                            }  
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

            /***********************************
            // Handle the delete email templates
            ***********************************/
            $('#delete-template-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#delete-template-form');
                var submitButton = $(this);

                // Get the selected template names
                var selectedTemplates = [];
                $('tbody#template-file-list input[type="checkbox"]:checked').each(function () {
                    selectedTemplates.push($(this).attr('id'));
                });

                // Remove existing hidden inputs before adding new ones
                form.find('input[name="selectedTemplates[]"]').remove();

                // Set the value of the hidden input to the joined IDs
                $('#selectedTemplates').val(selectedTemplates.join(','));

                // Enable loading effect
                enableLoadingEffect(submitButton);

                var data = new FormData(form[0]);
                // Append additional data to the FormData object
                data.append('selectedTemplates', $('#selectedTemplates').val());

                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_template') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('email-service/template/delete-template-action') ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            console.log('Deleted Folders:\n' + (response.deleted_folders.length > 0 ? response.deleted_folders.join('\n') : '<?= lang('Pages.None') ?>'));
                            console.log('Failed Folders:\n' + (response.failed_folders.length > 0 ? response.failed_folders.join('\n') : '<?= lang('Pages.None') ?>'));

                            if (response.status == 1) {
								// update manage templates
                                updateManageTemplates();
								
								// update the config form
                                updateSelectOptions();
								
                                // Response fully success
                                let resultSuccessResult = response.deleted_folders.length > 0 ? 
                                    '<?= lang('Pages.successful_deleted_template_list') ?><br>' + response.deleted_folders.join('<br>') : 
                                    null;

                                let resultFailedResult = response.failed_folders.length > 0 ? 
                                    '<?= lang('Pages.delete_delete_template_list') ?><br>' + response.failed_folders.join('<br>') : 
                                    null;

                                // Fixed condition for showing failed results toast
                                if(resultSuccessResult) {
                                    showToast('success', resultSuccessResult);
                                }

                                if(resultFailedResult) {  // Changed from resultSuccessResult to resultFailedResult
                                    showToast('danger', resultFailedResult);
                                }

                            } else if (response.status == 2) {
                                // Response success but with error
                                showToast('info', response.msg);
                            } else {
                                // Response error in processing the request
                                showToast('danger', response.msg);
                            }
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
                            submitButton.prop('disabled', true);
                            disableLoadingEffect(submitButton);
                        }
                    });
                } else {
                    // User cancelled the deletion action
                    // Disable loading effect
                    disableLoadingEffect(submitButton);
                }
            });
            
            /*************************************
            // Handle setup variations of products
            *************************************/
            $('#set-email-template-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#set-product-template-form');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('email-service/template/save-product-template-settings') ?>',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        let toastType = 'info';

                        if (response.status == 1) {
                            toastType = 'success';

                            // update the form
                            updateSelectOptions();
                        } else if (response.status == 2) {
                            toastType = 'info';
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

            function updateManageTemplates() {
                var tableContent = $('#template-file-list');
                var deleteTemplateForm = $('#delete-template-form');
                var generalPurposeEmailTemplate = '<?= $myConfig['selectedEmailTemplate'] ?>';
                var responseWrapper = $('.no-templates-responseMsg');
                var defaultEmailTemplate = 'default_email_template';

                // Uncheck the "checkAll" checkbox
                $('#checkAll-Templates').prop('checked', false);

                // Perform AJAX request
                $.ajax({
                    url: '<?= base_url('email-service/template/fetch-template-list-only') ?>',
                    method: 'GET',
                    dataType: 'json', // Expect JSON response
                    success: function(response) {
                        var filesHtml = '';

                        if (response.status == 1) {
                            /**
                             * Build HTML for the list of product files
                             *  */
                            
                            Object.keys(response.msg).forEach(function(key) {
                                var template = response.msg[key];

                                if(key === generalPurposeEmailTemplate) {
                                    filesHtml += '<tr>';
                                    filesHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + key + '" id="' + key + '" name="' + key + '" disabled></div></td>';
                                    filesHtml += '<td class="align-middle"><label for="' + key + '" class="form-label">' + template.templateName + '<small class="text-muted"> [ <?= lang('Pages.version') ?>: ' + template.version + ' - <a href="<?= base_url('download/email-template/') ?>' + key + '?v=' + template.version + '"><?= lang('Pages.download') ?></a> ]</small><br><small class="text-info"><?= lang('Pages.notification_disabled_delete_gen_email_template') ?></label></td>';
                                    filesHtml += '</tr>';
                                }
                                else if(key === defaultEmailTemplate) {
                                    filesHtml += '<tr>';
                                    filesHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + key + '" id="' + key + '" name="' + key + '" disabled></div></td>';
                                    filesHtml += '<td class="align-middle"><label for="' + key + '" class="form-label">' + template.templateName + '<small class="text-muted"> [ <?= lang('Pages.version') ?>: ' + template.version + ' - <a href="<?= base_url('download/email-template/') ?>' + key + '?v=' + template.version + '"><?= lang('Pages.download') ?></a> ]</small><br><small class="text-info"><?= lang('Pages.notification_disabled_delete_default_email_template') ?></label></td>';
                                    filesHtml += '</tr>';
                                }
                                else {
                                    filesHtml += '<tr>';
                                    filesHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + key + '" id="' + key + '" name="' + key + '"></div></td>';
                                    filesHtml += '<td class="align-middle"><label for="' + key + '" class="form-label">' + template.templateName + '<small class="text-muted"> [ <?= lang('Pages.version') ?>: ' + template.version + ' - <a href="<?= base_url('download/email-template/') ?>' + key + '?v=' + template.version + '"><?= lang('Pages.download') ?></a> ] </small></label></td>';
                                    filesHtml += '</tr>';
                                }
                            });
                            tableContent.html(filesHtml);

                            // show template form
                            deleteTemplateForm.slideDown();
                        } else if (response.status == 2) {
                            // Response success but returned empty as no email templates
                            // show no template notif
                            $('.no-templates-responseMsg').slideDown();

                            // empty the table body and hide the form
                            tableContent.html(filesHtml);
                            deleteTemplateForm.slideUp();
                        } else {
                            // Handle the case where there was an error in the controller
                            responseWrapper.slideUp();
                            responseWrapper.html('<div class="alert bg-soft-danger fade show text-center" role="alert">' + response.msg + '</div>');
                            responseWrapper.slideDown();
                        }

                        deactivateSelectedOptions();
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }

            // Initiate function upon loading
            updateManageTemplates();

            /***
             * Select Option for Config
             */		
            function updateSelectOptions() {
                var selectContent = $('#template_select');
                var associatedProdctContent = $('#templateWrapper');
                var productList = <?= json_encode($sideBarMenu['products']) ?>;
                var hiddenInputs = $('#set-product-template-form');
                var saveButton = $('#set-email-template-submit');
                var selectWrapper = $('#select-file-wrapper');
                var noTemplateNotif = $('.no-templates-responseMsg');
                var generalPurposeEmailTemplate = '<?= $myConfig['selectedEmailTemplate'] ?>';

                // Perform AJAX request
                $.ajax({
                    url: '<?= base_url('email-service/template/fetch-template-list-only') ?>',
                    method: 'GET',
                    dataType: 'json', // Expect JSON response
                    success: function(response) {
                        var templateList = response.msg;
                        if (response.success && response.status === 2) {
                            // No email templates found
                            // Handle this case, for example, show a message to the user
                            selectContent.html('');
                            selectContent.slideUp();
                            associatedProdctContent.html('');
                            associatedProdctContent.slideUp();
                            selectWrapper.slideUp();
                            hiddenInputs.slideUp();
                            saveButton.slideUp();
                            return; // Exit the function early
                        } else if (response.success && response.status === 1) {
                            /**
                             * Build HTML for the select options
                             */
                            var optionSelect = '<option value=""><?= lang('Pages.Select_Template') ?></option>';
                            Object.keys(response.msg).forEach(function(key) {
                                var template = response.msg[key];
                                // if(key !== generalPurposeEmailTemplate) {
                                    // Append each variation name as an option to the optionSelect string
                                    optionSelect += '<option value="' + key + '">' + template.templateName + '</option>';
                                // }
                            });
                            selectContent.html(optionSelect);

                            // Fetch fresh data from the server
                            var divContent = $('<div>');

                            $.ajax({
                                url: '<?= base_url('email-service/template/fetch-templates-config') ?>',
                                method: 'GET',
                                dataType: 'json',
                                success: function(freshData) {
                                    if(freshData.status === 1) {
                                        var emailTemplateConfig = JSON.parse(freshData.msg);

                                        Object.keys(response.msg).forEach(function(key) {
                                            var template = response.msg[key];
                                            // Check if emailTemplateConfig[key] exists and has values
                                            if (emailTemplateConfig[key] && emailTemplateConfig[key].trim() !== '') {
                                                var productNames = emailTemplateConfig[key].split(',');

                                                var selectOptions = '';
                                                productList.forEach(function(product) {
                                                    var selected = productNames.includes(product.trim()) ? 'selected' : '';
                                                    selectOptions += '<option value="' + product.trim() + '" ' + selected + '>' + product.trim() + '</option>';
                                                });

                                                var select = '<select class="form-select form-control" id="' + key + '-group" name="' + key + '-group" multiple="multiple" style="height: 150px;" onchange="updateTemplateGroup()">' + selectOptions + '</select>';

                                                var wrapper = '<div id="' + key + '-wrapper" style="display:none">' +
                                                    '<label for="' + key + '-group" class="form-label"><?= lang('Pages.Products_under_template') ?>"' + template.templateName + '"</label>' +
                                                    select +
                                                    '</div>';

                                                divContent.append(wrapper);
                                            } else {
                                                var selectOptions = '';
                                                productList.forEach(function(product) {
                                                    selectOptions += '<option value="' + product.trim() + '">' + product.trim() + '</option>';
                                                });

                                                var select = '<select class="form-select form-control" id="' + key + '-group" name="' + key + '-group" multiple="multiple" style="height: 150px;" onchange="updateTemplateGroup()">' + selectOptions + '</select>';

                                                var wrapper = '<div id="' + key + '-wrapper" style="display:none">' +
                                                    '<label for="' + key + '-group" class="form-label"><?= lang('Pages.Products_under_template') ?>"' + template.templateName + '"</label>' +
                                                    select +
                                                    '</div>';

                                                divContent.append(wrapper);
                                            }
                                        });

                                        associatedProdctContent.html(divContent.html());

                                        var hiddenInput = '';
                                        Object.keys(response.msg).forEach(function(key) {
                                            if (emailTemplateConfig[key] && emailTemplateConfig[key].trim() !== '') {
                                                hiddenInput += '<input type="hidden" id="' + key + '-hidden" name="' + key + '" value="' + emailTemplateConfig[key] + '">';
                                            }
                                            else {
                                                hiddenInput += '<input type="hidden" id="' + key + '-hidden" name="' + key + '" value="">';
                                            }
                                        });
                                        hiddenInputs.html(hiddenInput);

                                        // show the form
                                        selectWrapper.slideDown();
                                        associatedProdctContent.slideDown();
                                        hiddenInputs.slideDown();
                                        saveButton.slideDown();
                                        noTemplateNotif.slideUp();									
                                        deactivateSelectedOptions();									
                                    }
                                    else if(freshData.status === 2) {
                                        // Build default form without config values
                                        var selectOptions = '';
                                        productList.forEach(function(product) {
                                            selectOptions += '<option value="' + product.trim() + '">' + product.trim() + '</option>';
                                        });

                                        Object.keys(templateList).forEach(function(key) {
                                            // Append each variation name as an option to the optionSelect string

                                            var select = '<select class="form-select form-control" id="' + key + '-group" name="' + key + '-group" multiple="multiple" style="height: 150px;" onchange="updateTemplateGroup()">' + selectOptions + '</select>';

                                            var wrapper = '<div id="' + key + '-wrapper" style="display:none">' +
                                                '<label for="' + key + '-group" class="form-label"><?= lang('Pages.Products_under_template') ?>"' + templateList[key].templateName + '"</label>' +
                                                select +
                                                '</div>';

                                            divContent.append(wrapper);
                                            associatedProdctContent.html(divContent.html());

                                            var hiddenInput = '';
                                            Object.keys(response.msg).forEach(function(key) {
                                                hiddenInput += '<input type="hidden" id="' + key + '-hidden" name="' + key + '" value="">';
                                            });
                                            hiddenInputs.html(hiddenInput);										
                                        });								
                                    }
                                    else {
                                        console.error('<?= lang('Pages.Error_fetching_data') ?>', error);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('<?= lang('Pages.ajax_no_response') ?>', status.toUpperCase() + ' ' + xhr.status);
                                    // Handle error if needed
                                }
                            });

                            selectContent.slideDown();
                            associatedProdctContent.slideDown();
                            selectWrapper.slideDown();
                            hiddenInputs.slideDown();
                            saveButton.slideDown();						

                        } else {
                            // Handle other cases, such as errors
                            console.error('Error:', response.msg);
                            // Show an appropriate message to the user
                        }

                        deactivateSelectedOptions();
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }

            // Initiate function upon loading
            updateSelectOptions();                
        });
    </script>	

    <script type="text/javascript">
        
        function updateTemplateGroup() {
            // Iterate over each variation group select element
            $('#templateWrapper select').each(function () {
                var templateId = $(this).attr('id').replace('-group', ''); // Extract variation ID from select element ID
                var selectedOptions = $(this).val(); // Get selected options
                // Update hidden input value with selected options
                $('#' + templateId + '-hidden').val(selectedOptions.join(',')); // Join selected options with commas and set as hidden input value
            });

            // Call the deactivateSelectedOptions function
            deactivateSelectedOptions();		
        }

        function deactivateSelectedOptions() {
            // Get all select elements inside the templateWrapper
            var selects = document.querySelectorAll('#templateWrapper select');

            // Array to store selected options
            var selectedOptions = [];

            // Loop through each select element
            selects.forEach(function(select) {
                // Loop through each option in the select element
                select.querySelectorAll('option').forEach(function(option) {
                    // If the option is selected, add it to the selectedOptions array
                    if (option.selected) {
                        selectedOptions.push(option.value);
                    }
                });
            });

            // Loop through each select element again to deactivate options
            selects.forEach(function(select) {
                // Loop through each option in the select element
                select.querySelectorAll('option').forEach(function(option) {
                    // If the option is selected, skip it
                    if (option.selected) {
                        return;
                    }
                    // If the option value is in the selectedOptions array, disable it
                    if (selectedOptions.includes(option.value)) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });
        }

        deactivateSelectedOptions();
        
        // Dynamically select products for each template
        function showProductsearchTemplate() {
            var templateWrapper = $('#templateWrapper');
            var selectedTemplate = $('#template_select').val();

            // Hide all product wrappers first
            templateWrapper.find('div').hide();

            // Show the selected product wrapper
            $('#' + selectedTemplate + '-wrapper').slideDown();
        }

        // Function to enable/disable the delete button based on checkbox state
        function updateDeleteTemplateButtonState() {
            var anyCheckboxChecked = $('tbody#template-file-list input[type="checkbox"]:checked').length > 0;
            $('#delete-template-submit').prop('disabled', !anyCheckboxChecked);
        }
        
        // Check/uncheck all checkboxes when "checkAll" is clicked
        $('#checkAll-Templates').on('change', function () {
            var isChecked = $(this).prop('checked');
            $('tbody#template-file-list').find('input[type="checkbox"]:not(:disabled)').prop('checked', isChecked);
            updateDeleteTemplateButtonState();
        });

        // Check/uncheck "checkAll-Templates" based on the state of individual checkboxes
        $(document).on('change', 'tbody#template-file-list input[type="checkbox"]:not(:disabled)', function () {
            var allChecked = $('tbody#template-file-list input[type="checkbox"]:not(:disabled):checked').length === $('tbody#template-file-list input[type="checkbox"]:not(:disabled)').length;
            $('#checkAll-Templates').prop('checked', allChecked);
            updateDeleteTemplateButtonState();
        });

        // Uncheck "checkAll-Templates" if any individual checkbox is unchecked
        $(document).on('change', 'tbody#template-file-list input[type="checkbox"]:not(:disabled)', function () {
            if (!$(this).prop('checked')) {
                $('#checkAll-Templates').prop('checked', false);
            }
        });

        // Call the function initially to set the button state
        updateDeleteTemplateButtonState();	    
    </script>  
<?= $this->endSection() //End section('scripts')?>
