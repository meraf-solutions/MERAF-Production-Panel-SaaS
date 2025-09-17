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

            <form novalidate action="javascript:void(0)" id="individual-package-form">
                <div class="col-12 mt-4">
                    <div class="card rounded shadow p-4 border-0">
                        <div class="d-flex flex-column flex-md-row align-items-center mb-3">
                            <h4 class="mb-3 mb-md-0 me-3 col-12 col-md-auto"><?= lang('Pages.Package_Details') ?></h4>

                            <?php 
                            if(isset($allPackageData)) {
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
                                
                            ?>
                                <div class="form-icon position-relative col-12 col-md-auto me-lg-3 mx-auto mb-3 mb-md-0">
                                    <i data-feather="package" class="fea icon-sm icons"></i>
                                    <select class="form-select form-control ps-5" id="selectPackage" onchange="handlePackageSelect(this)">
                                        <option value=""><?= lang('Pages.Select_Package') ?></option>
                                        <?php
                                        foreach($allPackageData as $package) { 
                                            $durationText = '';
                                            if ($package['validity_duration'] === 'lifetime') {
                                                $durationText = $validityDurationMap['lifetime'];
                                            } else {
                                                $durationLabel = $package['validity'] > 1 ? 'plural' : 'singular';
                                                $durationText = $package['validity'] . ' ' . $validityDurationMap[$package['validity_duration']][$durationLabel];
                                            }
                                        ?>
                                            <option value="<?= $package['id'] ?>" <?= isset($packageData) && $packageData['id'] === $package['id'] ? 'selected' : '' ?>>
                                            <?= $package['package_name'] ?> | 
                                            <?= $durationText ?> | 
                                            <?= $package['price'] === '0.00' ? lang('Pages.Free') : $package['price'] . ' ' . $myConfig['packageCurrency'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.sort_order_feedback') ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-icon position-relative col-12 col-md-auto <?= !isset($allPackageData) ? 'me-lg-3 mx-auto mb-lg-3 mb-md-0' : '' ?>">
                                <i data-feather="hash" class="fea icon-sm icons"></i>
                                <input type="number" class="form-control ps-5" id="sortOrder" placeholder="<?= lang('Pages.Sort_Order') ?>" value="<?= !isset($packageData) ? 0 : '' ?>" required data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.sort_order_description') ?>">
                                <div class="invalid-feedback">
                                    <?= lang('Pages.sort_order_feedback') ?>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="packageName" class="form-label"><?= lang('Pages.Package_Name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="packageName" placeholder="<?= lang('Pages.Package_Name') ?>" value="" required="">
                                <div class="invalid-feedback">
                                    <?= lang('Pages.Package_name_feedback') ?>
                                </div>
                            </div>
                            
                            <div class="col-sm-6">
                                <label for="packagePrice" class="form-label"><?= lang('Pages.Price_in_currency', ['currency' => $myConfig['packageCurrency']]) ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="packagePrice" placeholder="<?= lang('Pages.Package_Price') ?>" value="" required="">
                                <div class="invalid-feedback">
                                    <?= lang('Pages.Price_in_currency_feedback') ?>
                                </div>
                            </div>

                            <div class="col-sm-6" id="packageValidityBlock">
                                <label for="packageValidity" class="form-label"><?= lang('Pages.Validity') ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="packageValidity" placeholder="<?= lang('Pages.Package_Validity') ?>" value="" required="" <?= isset($packageData) && $packageData['validity_duration'] === 'lifetime' ? 'readonly' : '' ?>>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.Package_Validity_feedback') ?>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="packageDuration" class="form-label"><?= lang('Pages.Package_Duration') ?> <span class="text-danger">*</span></label>
                                <select class="form-select form-control" id="packageDuration" required="">
                                    <option value=""><?= lang('Pages.Select_Option') ?></option>
                                    <option value="day"><?= lang('Pages.Day') ?></option>
                                    <option value="week"><?= lang('Pages.Week') ?></option>
                                    <option value="month"><?= lang('Pages.Month') ?></option>
                                    <option value="year"><?= lang('Pages.Year') ?></option>
                                    <option value="lifetime"><?= lang('Pages.Lifetime') ?></option>
                                </select>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.Package_Duration_feedback') ?>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="defaultPackage">
                                    <label class="form-check-label" for="defaultPackage"><?= lang('Pages.Default_Package') ?></label>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.error_exisiting_default_package') ?>
                                    </div>
                                </div>
                            </div>                                                

                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="packageActive">
                                    <label class="form-check-label" for="packageActive"><?= lang('Pages.Available_to_purchase') ?></label>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="packageHighlighted">
                                    <label class="form-check-label" for="packageHighlighted"><?= lang('Pages.Highlighted_package') ?></label>
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-3 mt-4 pt-4 border-top"><?= lang('Pages.Package_Modules') ?></h4>

                        <div class="accordion" id="packageModulesAccordion">
                            <?php $count = 1; foreach($moduleCategories as $moduleCategory) { ?>
                                <div class="accordion-item rounded <?= $count === 1 ? '' : 'mt-2' ?>">
                                    <h2 class="accordion-header" id="<?= $moduleCategory['id'] ?>">
                                        <button class="accordion-button border-0 bg-light <?= $count === 1 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $moduleCategory['category_name'] ?>"
                                            aria-expanded="<?= $count === 1 ? 'true' : 'false' ?>" aria-controls="<?= $moduleCategory['category_name'] ?>">
                                            <?= lang('Pages.' . $moduleCategory['category_name']) ?>
                                        </button>
                                    </h2>

                                    <div id="<?= $moduleCategory['category_name'] ?>" class="accordion-collapse border-0 collapse <?= $count === 1 ? 'show' : '' ?>" aria-labelledby="<?= $moduleCategory['id'] ?>" data-bs-parent="#packageModulesAccordion">
                                        <div class="accordion-body table-responsive shadow rounded mb-0">
                                            <table class="table mb-0 table-center table-striped">
                                                <thead>
                                                    <tr>
                                                        <th class="p-3 border-bottom" style="width: 50px;">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="" id="checkAll_<?= $moduleCategory['category_name'] ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.tickEnableAllFeatures') ?>">
                                                            </div>
                                                        </th>
                                                        <th class="border-bottom" style="min-width: 120px;"><?= lang('Pages.Module_Name') ?></th>
                                                        <th class="border-bottom" style="min-width: 300px;"><?= lang('Pages.Description') ?></th>
                                                        <th class="border-bottom text-center" style="min-width: 130px;"><?= lang('Pages.Value') ?></th>
                                                        <th class="border-bottom text-center" style="min-width: 130px;"><?= lang('Pages.Measurement_Unit') ?></th>
                                                    </tr>
                                                </thead>

                                                <tbody id="tableContent<?= $moduleCategory['category_name'] ?>">
                                                    <?php 
                                                    foreach($packageModules as $packageModule) { 
                                                        if($packageModule['module_category_id'] === $moduleCategory['id']) {
                                                            $measurementUnit = json_decode($packageModule['measurement_unit'], true);
                                                    ?>
                                                        <tr>
                                                            <td class="p-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="module_<?= $packageModule['id'] ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.tickCheckboxModule') ?>">
                                                                </div>
                                                            </td>
                                                            <td><label for="module_<?= $packageModule['id'] ?>"  class="text-dark"><?= lang('Pages.' . $packageModule['module_name']) ?></label></td>
                                                            <td class="text-muted"><?= lang('Pages.' . $packageModule['module_description']) ?></td>

                                                            <?php
                                                            $inputDescription = '';
                                                            if(array_key_exists('description', $measurementUnit)) {
                                                                $inputDescription = 'data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="'.$measurementUnit['description'].'"';
                                                            }                                                                               
                                                            ?>                                                                            

                                                            <?php if($measurementUnit['type'] === 'checkbox') { ?>
                                                                <td class="text-muted">
                                                                    <div class="form-check d-flex justify-content-center">
                                                                        <input class="form-check-input" type="checkbox" id="<?= $packageModule['id'] ?>_value" name="<?= $packageModule['id'] ?>_value" autocomplete="off" <?= $inputDescription ?>>
                                                                    </div>
                                                                </td>
                                                                <td class="text-muted text-center"><?= lang('Pages.' . $measurementUnit['unit']) ?></td>
                                                            <?php } else { ?>
                                                                <td class="text-muted">
                                                                    <div class="d-flex justify-content-between">
                                                                        <div class="form-icon position-relative">
                                                                            <?php if($measurementUnit['icon']) { ?>
                                                                                <i data-feather="<?= $measurementUnit['icon'] ?>" class="fea icon-sm icons"></i>
                                                                            <?php } ?>

                                                                            <?php
                                                                            $extraAttributes = '';
                                                                            $defaultValue = '';

                                                                            if( ($measurementUnit['type'] === 'number') ) {

                                                                                if(array_key_exists('min', $measurementUnit)) {
                                                                                    $extraAttributes .= 'min="'.$measurementUnit['min'].'"';
                                                                                }

                                                                                if(array_key_exists('max', $measurementUnit)) {
                                                                                    $extraAttributes .= 'max="'.$measurementUnit['max'].'"';
                                                                                }

                                                                                if(array_key_exists('step', $measurementUnit)) {
                                                                                    $extraAttributes .= 'step="'.$measurementUnit['step'].'"';
                                                                                }                                                                                                    
                                                                            }


                                                                            if(array_key_exists('default', $measurementUnit) && !isset($packageData)) {
                                                                                $defaultValue = $measurementUnit['default'];
                                                                            }                                                                                                
                                                                            ?>
                                                                            <input class="form-control <?= $measurementUnit['icon'] ? 'ps-5' : '' ?>" type="<?= $measurementUnit['type'] ?>" value="<?= $defaultValue ?>" id="<?= $packageModule['id'] ?>_value" name="<?= $packageModule['id'] ?>_value" autocomplete="off" <?= $inputDescription ?> <?= $extraAttributes ?>>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-muted text-center"><?= lang('Pages.' . $measurementUnit['unit']) ?></td>
                                                            <?php } ?>
                                                        </tr>
                                                    <?php }
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                                                                            
                                </div>
                            <?php $count++; } ?>
                        </div>
                        
                        <button class="mx-auto btn btn-primary mt-3" id="submit-package"><i class="uil uil-save"></i> <?= lang('Pages.Save') ?></button>
                    </div>
                </div><!--end col-->
            </form>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            const $packageSelect = $('#selectPackage');
            const $submitButton = $('button[type="submit"]');

            // Function to handle package selection and button state
            function handlePackageSelect() {
                const isPackageSelected = $packageSelect.val() !== '';
                $submitButton.prop('disabled', !isPackageSelected);
            }

            $packageSelect.on('change', handlePackageSelect);

            handlePackageSelect();

            // Handle "Check All" for each module category
            <?php foreach($moduleCategories as $moduleCategory) { ?>
            $('#checkAll_<?= $moduleCategory['category_name'] ?>').change(function() {
                var isChecked = $(this).prop('checked');
                // Get all module checkboxes in this category
                var $categoryModules = $('#<?= $moduleCategory['category_name'] ?> .form-check-input[id^="module_"]');
                
                // Check/uncheck all modules in this category
                $categoryModules.each(function() {
                    var moduleId = $(this).attr('id').replace('module_', '');
                    $(this).prop('checked', isChecked);
                    
                    // Get the corresponding value input
                    var $valueInput = $('#' + moduleId + '_value');
                    if ($valueInput.attr('type') === 'checkbox') {
                        $valueInput.prop('checked', isChecked);
                    }
                });
            });
            <?php } ?>

            // Handle individual module checkboxes
            $('.form-check-input[id^="module_"]').change(function() {
                var moduleId = $(this).attr('id').replace('module_', '');
                var $valueInput = $('#' + moduleId + '_value');
                
                // If unchecking a module, clear its value
                if (!$(this).prop('checked')) {
                    if ($valueInput.attr('type') === 'checkbox') {
                        $valueInput.prop('checked', false);
                    } else {
                        $valueInput.val('');
                    }
                }
            });

            $('#submit-package').on('click', function (e) {
                e.preventDefault();

                const form = $('#individual-package-form');
                const button = $(this);

                enableLoadingEffect(button);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // Basic validation
                if (!$('#sortOrder').val() || !$('#packageName').val() || !$('#packagePrice').val() || !$('#packageValidity').val() || !$('#packageDuration').val()) {
                    showToast('danger', '<?= lang('Notifications.please_fill_required_fields') ?>');

                    if (!$('#sortOrder').val()) $('#sortOrder').addClass('is-invalid');
                    if (!$('#packageName').val()) $('#packageName').addClass('is-invalid');
                    if (!$('#packagePrice').val()) $('#packagePrice').addClass('is-invalid');
                    if (!$('#packageValidity').val()) $('#packageValidity').addClass('is-invalid');
                    if (!$('#packageDuration').val()) $('#packageDuration').addClass('is-invalid');
                    disableLoadingEffect(button);
                    return;
                }

                // Collect module data dynamically
                var moduleData = {};
                <?php foreach($moduleCategories as $moduleCategory) { ?>
                    moduleData['<?= $moduleCategory['category_name'] ?>'] = {};
                    <?php foreach($packageModules as $module) { 
                        if($module['module_category_id'] === $moduleCategory['id']) {
                            $measurementUnit = json_decode($module['measurement_unit'], true);
                    ?>
                        var moduleId = '<?= $module['id'] ?>';
                        var moduleName = '<?= $module['module_name'] ?>';
                        var $moduleCheckbox = $('#module_' + moduleId);
                        var $moduleValue = $('#' + moduleId + '_value');
                        
                        if ($moduleCheckbox.length) {
                            moduleData['<?= $moduleCategory['category_name'] ?>'][moduleName] = {
                                enabled: $moduleCheckbox.prop('checked'),
                                value: $moduleValue.attr('type') === 'checkbox' 
                                    ? $moduleValue.prop('checked')
                                    : $moduleValue.val() || 0
                            };
                        }
                    <?php } 
                    } ?>
                <?php } ?>

                // Collect form data
                var formData = {
                    packageId: '<?= isset($packageID) ? $packageID : "" ?>',
                    sortOrder: $('#sortOrder').val(),
                    packageName: $('#packageName').val(),
                    packagePrice: $('#packagePrice').val(),
                    packageValidity: $('#packageValidity').val(),
                    packageDuration: $('#packageDuration').val().toLowerCase(),
                    defaultPackage: $('#defaultPackage').prop('checked'),
                    packageActive: $('#packageActive').prop('checked'),
                    packageHighlighted: $('#packageHighlighted').prop('checked'),
                    moduleData: moduleData
                };

                // Send AJAX request
                $.ajax({
                    url: '<?= base_url('admin-options/package-manager/save-package') ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
                            delayedRedirect('<?= base_url('admin-options/package-manager/list-packages') ?>');
                        } else {
                            toastType = 'danger';

                            disableLoadingEffect(button);
                        }

                        showToast(toastType, response.msg);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        disableLoadingEffect(button);
                    }
                });
            });
            
            <?php if (isset($allPackageData)): ?>
                <?php foreach($allPackageData as $eachPackage) { ?>
                    // Load other module data
                    var moduleData_<?= $eachPackage['id'] ?> = <?= isset($eachPackage['package_modules']) ? $eachPackage['package_modules'] : '{}' ?>;
                <?php } ?>
            <?php endif; ?>

            <?php if (isset($packageData)): ?>
            // Load existing data if editing
            $('#sortOrder').val('<?= $packageData['sort_order'] ?>');
            $('#packageName').val('<?= $packageData['package_name'] ?>');
            $('#packagePrice').val('<?= $packageData['price'] ?>');
            $('#packageValidity').val('<?= $packageData['validity'] ?>');
            $('#packageDuration').val('<?= $packageData['validity_duration'] ?>');
            $('#defaultPackage').prop('checked', <?= $packageData['is_default'] === 'on' ? 'true' : 'false' ?>);
            $('#packageActive').prop('checked', <?= $packageData['visible'] === 'on' ? 'true' : 'false' ?>);
            $('#packageHighlighted').prop('checked', <?= $packageData['highlight'] === 'on' ? 'true' : 'false' ?>);

            // Load module data
            var moduleData = <?= isset($packageData['package_modules']) ? $packageData['package_modules'] : '{}' ?>;
            
            <?php foreach($moduleCategories as $moduleCategory) { ?>
                <?php foreach($packageModules as $module) { 
                    if($module['module_category_id'] === $moduleCategory['id']) {
                        $measurementUnit = json_decode($module['measurement_unit'], true);
                ?>
                    if (moduleData['<?= $moduleCategory['category_name'] ?>'] && 
                        moduleData['<?= $moduleCategory['category_name'] ?>']['<?= $module['module_name'] ?>'])
                    {
                        var moduleSettings = moduleData['<?= $moduleCategory['category_name'] ?>']['<?= $module['module_name'] ?>'];
                        var moduleId = '<?= $module['id'] ?>';
                        
                        if(moduleSettings.enabled === 'true') {
                            $('#module_' + moduleId).prop('checked', moduleSettings.enabled);
                        }

                        var $valueInput = $('#' + moduleId + '_value');
                        
                        if ($valueInput.attr('type') === 'checkbox') {
                            if(moduleSettings.enabled === 'true') {
                                $valueInput.prop('checked', moduleSettings.value);
                            }
                        } else {
                            $valueInput.val(moduleSettings.value);
                        }
                    }

                    // Setup for License Management section
                    setupCheckAllBehavior('<?= $moduleCategory['category_name'] ?>');                  
                <?php } 
                } ?>
            <?php } ?>
            <?php else: ?>
                <?php foreach($moduleCategories as $moduleCategory) { ?>
                    setupCheckAllBehavior('<?= $moduleCategory['category_name'] ?>');
                <?php } ?>
            <?php endif; ?>
        });

        function setupCheckAllBehavior(sectionId) {
            const checkAllBox = document.getElementById(`checkAll_${sectionId}`);
            const tbody = document.getElementById(`tableContent${sectionId}`);
            const moduleCheckboxes = tbody.querySelectorAll('input[type="checkbox"][id^="module_"]');
            
            // Get all value checkboxes in the fourth column
            const valueCheckboxes = tbody.querySelectorAll('input[type="checkbox"][id$="_value"]');

            // Handle "Check All" checkbox click
            checkAllBox.addEventListener('change', function() {
                moduleCheckboxes.forEach(checkbox => {
                    checkbox.checked = checkAllBox.checked;
                    handleRowInteraction(checkbox);
                });
            });

            // Handle individual checkbox changes in first column
            moduleCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Check if all individual checkboxes are checked
                    const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                    checkAllBox.checked = allChecked;
                    handleRowInteraction(this);
                });
            });

            // Handle checkbox changes in fourth column
            valueCheckboxes.forEach(valueCheckbox => {
                valueCheckbox.addEventListener('change', function() {
                    // Find the corresponding first column checkbox in the same row
                    const row = this.closest('tr');
                    if (row) {
                        const moduleCheckbox = row.querySelector('input[type="checkbox"][id^="module_"]');
                        if (moduleCheckbox) {
                            moduleCheckbox.checked = this.checked;
                            // Update the "Check All" state
                            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                            checkAllBox.checked = allChecked;
                        }
                    }
                });
            });

            // Function to handle row-level interactions
            function handleRowInteraction(checkbox) {
                const row = checkbox.closest('tr');
                if (!row) return;

                // Find the value input in the same row (could be checkbox or text input)
                const valueInput = row.querySelector('input[id$="_value"]');
                if (!valueInput) return;

                if (checkbox.checked) {
                    if (valueInput.type === 'checkbox') {
                        valueInput.checked = true;
                    } else if (valueInput.type === 'number') {
                        valueInput.focus();
                        // Only set a default value if the input is empty
                        if (!valueInput.value) {
                            valueInput.value = '1'; // Default value, can be modified as needed
                        }
                    }
                } else {
                    if (valueInput.type === 'checkbox') {
                        valueInput.checked = false;
                    } else if (valueInput.type === 'number') {
                        valueInput.value = ''; // Clear the input when unchecking
                        valueInput.blur(); // Remove focus
                    }
                }
            }

            // Function to update "Check All" state based on individual checkboxes
            function updateCheckAllState() {
                const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                checkAllBox.checked = allChecked;
            }

            // Initial state check
            updateCheckAllState();

            // Initial row state setup
            moduleCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    handleRowInteraction(checkbox);
                }
            });
        }
        
        $('#packageDuration').on('change', function() {
            var packageValidityInput = $('#packageValidity');
            
            if ($(this).val() === 'lifetime') {
                // If lifetime is selected
                packageValidityInput
                    .val(1)           // Remove the value
                    .prop('readonly', true)  // Make the input readonly
                    // .attr('disabled', 'disabled');  // Optional: also disable the input
            } else {
                // If any other option is selected
                packageValidityInput
                    // .val('')           // Clear any existing value
                    .prop('readonly', false)  // Remove readonly
                    // .removeAttr('disabled');  // Remove disabled attribute
                    
            }
        });
        
        function handlePackageSelect(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = `<?= base_url('admin-options/package-manager/edit/') ?>${selectedValue}/select-package`;
            }
        }
    </script>  
<?= $this->endSection() //End section('scripts')?>