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
                <a href="<?= base_url('admin-options/package-manager/new') ?>" class="btn btn-primary"><i class="uil uil-plus me-1"></i> <?= lang('Pages.New_package') ?></a>
            </div>

            <div class="table-responsive shadow rounded p-4">
                <table id="package-list-table" class="table table-center table-striped text-center bg-white mb-0">
                    <thead>
                        <tr>
                            <th class="border-bottom p-3" style="min-width: 50px;"><?= lang('Pages.Sort_Order') ?></th>
                            <th class="border-bottom p-3" style="min-width: 200px;"><?= lang('Pages.Package_Name') ?></th>
                            <th class="border-bottom p-3" style="min-width: 150px;"><?= lang('Pages.Price') ?></th>
                            <th class="border-bottom p-3" style="min-width: 150px;"><?= lang('Pages.Validity') ?></th>
                            <th class="border-bottom p-3" style="min-width: 150px;"><?= lang('Pages.Visibility') ?></th>
                            <th class="border-bottom p-3" style="min-width: 150px;"><?= lang('Pages.Features') ?></th>
                            <th class="border-bottom p-3" style="min-width: 150px;"><?= lang('Pages.Package_Status') ?></th>
                            <th class="border-bottom p-3 text-end" style="min-width: 100px;"><?= lang('Pages.Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="package-list-tbody">
                        <!-- Packages will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            loadPackages();

            // Function to load packages
            function loadPackages() {
                $.ajax({
                    url: '<?= base_url('admin-options/package-manager/list-packages/data') ?>',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            displayPackages(response.data);
                            initializeModals(response.data);
                        } else {
                            showToast(toastType, response.msg || '<?= lang('Notifications.error_loading_packages') ?>');
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }

            const languageMapping = JSON.parse('<?= json_encode($featureLanguageMap) ?>');

            const validityDurationMap = {
                    'day': { singular: '<?= lang('Pages.Day') ?>', plural: '<?= lang('Pages.Days') ?>' },
                    'month': { singular: '<?= lang('Pages.Month') ?>', plural: '<?= lang('Pages.Months') ?>' },
                    'year': { singular: '<?= lang('Pages.Year') ?>', plural: '<?= lang('Pages.Years') ?>' },
                    'lifetime': '<?= lang('Pages.Lifetime') ?>'
                };
                
            const currency = '<?= $myConfig['packageCurrency'] ?>';                

            // Function to initialize modals with package data
            function initializeModals(packages) {
                packages.forEach(pkg => {
                    if (pkg.package_modules) {
                        try {
                            let validityDuration = '';
                            if (pkg.validity_duration === 'lifetime') {
                                validityDuration = validityDurationMap['lifetime'];
                            } else {
                                const durationLabel = pkg.validity > 1 ? 'plural' : 'singular';
                                validityDuration = `${pkg.validity} ${validityDurationMap[pkg.validity_duration][durationLabel]}`;
                            }

                            const modulesData = JSON.parse(pkg.package_modules);
                            
                            let modalContent = '';
                            
                            // Process each category
                            Object.entries(modulesData).forEach(([category, modules], index) => {
                                const count = index + 1;
                                const mappedCategoryName = languageMapping[category][category];
                                
                                const categoryId = category.replace(/[^a-zA-Z0-9]/g, '_');
                                
                                modalContent += `
                                    <div class="accordion-item rounded ${count === 1 ? '' : 'mt-2'}">
                                        <h2 class="accordion-header" id="${categoryId}_${pkg.id}">
                                            <button class="accordion-button border-0 bg-light ${count === 1 ? '' : 'collapsed'}" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse_${categoryId}_${pkg.id}"
                                                    aria-expanded="${count === 1 ? 'true' : 'false'}" 
                                                    aria-controls="collapse_${categoryId}_${pkg.id}">
                                                ${mappedCategoryName}
                                            </button>
                                        </h2>
                                        <div id="collapse_${categoryId}_${pkg.id}" 
                                            class="accordion-collapse border-0 collapse ${count === 1 ? 'show' : ''}" 
                                            aria-labelledby="${categoryId}_${pkg.id}"
                                            data-bs-parent="#packageModulesAccordion_${pkg.id}">
                                            <div class="accordion-body table-responsive shadow rounded mb-0">
                                                <table class="table mb-0 table-center table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th class="border-bottom" style="min-width: 120px;"><?= lang('Pages.Module_Name') ?></th>
                                                            <th class="border-bottom text-center" style="min-width: 130px;"><?= lang('Pages.Status') ?></th>
                                                            <th class="border-bottom text-center" style="min-width: 130px;"><?= lang('Pages.Value') ?></th>
                                                            <th class="border-bottom text-center" style="min-width: 130px;"><?= lang('Pages.Measurement_Unit') ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                `;

                                // Add each module in the category
                                Object.entries(modules).forEach(([moduleName, moduleData]) => {
                                    const isEnabled = moduleData.enabled === "true";
                                    const value = getModuleValue(moduleData.value, pkg.id);
                                    const mappedModuleName = languageMapping[category][moduleName];
                                    const moduleDescription = languageMapping[category][`${moduleName}_description`];
                                    const measurementUnit = languageMapping[category][`${moduleName}_unit`];
                                    
                                    modalContent += `
                                        <tr>
                                            <td>
                                                <span class="text-dark">${mappedModuleName}</span>
                                                <a href="javascript:void(0)" class="text-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="${moduleDescription}">
                                                    <i class="ti ti-info-circle"></i>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-${isEnabled ? 'success' : 'danger'} rounded-pill">
                                                    ${isEnabled ? '<?= lang('Pages.Enabled') ?>' : '<?= lang('Pages.Disabled') ?>'}
                                                </span>
                                            </td>
                                            <td class="text-muted text-center">${value} </td>
                                            <td class="text-muted text-center">${measurementUnit}</td>
                                        </tr>
                                    `;
                                });

                                modalContent += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            function getModuleValue(value, packageId) {
                                if (value === 'true') {
                                    return `<input class="form-check-input" type="checkbox" id="value_${packageId}" name="value_${packageId}" disabled checked>`;
                                }
                                if (value === 'false') {
                                    return `<input class="form-check-input" type="checkbox" id="value_${packageId}" name="value_${packageId}" disabled>`;
                                }
                                return value;
                            }

                            // Create modal for this package
                            const modalHtml = `
                                <div class="modal fade" id="modalInclusions_${pkg.id}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-xl">
                                        <div class="modal-content rounded shadow border-0">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title">${pkg.package_name}  | ${validityDuration} | ${pkg.price === '0.00' ? '<?= lang('Pages.Free') ?>' : `${pkg.price} ${currency}`}</h5>
                                                <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal">
                                                    <i class="uil uil-times fs-4 text-dark"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="accordion" id="packageModulesAccordion_${pkg.id}">
                                                    ${modalContent}
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?= base_url('admin-options/package-manager/edit') ?>/${pkg.id}/select-package" class="btn btn-primary">
                                                    <i class="uil uil-edit"></i> <?= lang('Pages.Edit') ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Remove existing modal if any
                            $(`#modalInclusions_${pkg.id}`).remove();
                            // Append new modal to body
                            $('body').append(modalHtml);

                        } catch (e) {
                            console.error('Error parsing package modules for package:', pkg.id, e);
                        }
                    }
                });

                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();                    
            }

            // Function to display packages
            function displayPackages(packages) {
                const tbody = $('#package-list-tbody');
                const defaultPackageId = "<?= $defaultPackage['id'] ?? '' ?>";
                const statusMap = {
                    'active': { badge: 'success', text: '<?= lang('Pages.Active') ?>' },
                    'inactive': { badge: 'warning', text: '<?= lang('Pages.Inactive') ?>' },
                    'default': { badge: 'dark text-light', text: '<?= lang('Pages.Deleted') ?>' }
                };
                tbody.empty();

                if (packages.length === 0) {
                    tbody.append('<tr><td colspan="7" class="text-center"><?= lang('Pages.No_packages_found') ?></td></tr>');
                    return;
                }

                packages.forEach((pkg, index) => {
                    const features = [];
                    if (pkg.highlight === 'on') features.push('<?= lang('Pages.Highlighted_Package') ?>');
                    if (pkg.is_default === 'on') features.push('<?= lang('Pages.Default_Package') ?>');

                    const packageStatus = statusMap[pkg.status] || statusMap['default'];
                    const packageStatusBadge = packageStatus.badge;
                    const packageStatusText = packageStatus.text;

                    let validityDuration = '';
                    if (pkg.validity_duration === 'lifetime') {
                        validityDuration = validityDurationMap['lifetime'];
                    } else {
                        const durationLabel = pkg.validity > 1 ? 'plural' : 'singular';
                        validityDuration = `${pkg.validity} ${validityDurationMap[pkg.validity_duration][durationLabel]}`;
                    }

                    const row = `
                        <tr>
                            <td class="p-3">${pkg.sort_order !== '99' ? pkg.sort_order : '-'}</td>
                            <td>
                                <a href="${pkg.status === 'deleted' ? 'javascript:void(0)' : `<?= base_url('admin-options/package-manager/edit') ?>/${pkg.id}`}/select-package" class="text-${pkg.status === 'deleted' ? 'secondary' : 'primary'}">
                                    ${pkg.status === 'deleted' ? '<strike>' : ''}${pkg.package_name}${pkg.status === 'deleted' ? '</strike>' : ''}
                                </a>
                            </td>
                            <td class="${pkg.status === 'deleted' ? 'text-muted' : ''}">
                                ${pkg.status === 'deleted' ? '<strike>' : ''}${pkg.price === '0.00' ? '<?= lang('Pages.Free') ?>' : `${pkg.price} ${currency}`}${pkg.status === 'deleted' ? '</strike>' : ''}
                            </td>
                            <td class="${pkg.status === 'deleted' ? 'text-muted' : ''}">
                                ${pkg.status === 'deleted' ? '<strike>' : ''}${validityDuration}${pkg.status === 'deleted' ? '</strike>' : ''}
                            </td>
                            <td class="p-3">
                                <a href="javascript:void(0)" class="text-${pkg.visible === 'on' ? 'success' : 'danger'}" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="${pkg.visible === 'on' ? '<?= lang('Pages.tooltipPackageVisibilityShown') ?>' : '<?= lang('Pages.tooltipPackageVisibilityHidden') ?>'}">
                                    <i class="ti ti-${pkg.visible === 'on' ? 'eye' : 'eye-off'}"></i>
                                </a>
                            </td>
                            <td class="p-3">
                                ${features.map(f => `<span class="badge bg-soft-primary">${f}</span>`).join(' ')}
                                <span class="badge bg-primary rounded-pill" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalInclusions_${pkg.id}">
                                    <?= lang('Pages.Show_Inclusions') ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="badge bg-${packageStatusBadge} rounded-pill">
                                    ${packageStatusText}
                                </span>
                            </td>
                            <td class="text-end p-3">
                                ${pkg.status !== 'deleted' ? `
                                    <a href="<?= base_url('admin-options/package-manager/edit') ?>/${pkg.id}/select-package" 
                                    class="btn btn-icon btn-soft-primary">
                                        <i class="uil uil-pen"></i>
                                    </a>
                                    ${pkg.id !== defaultPackageId ? `
                                        <button type="button" class="btn btn-icon btn-soft-danger delete-package" 
                                                data-package-id="${pkg.id}">
                                            <i class="uil uil-trash-alt"></i>
                                        </button>
                                    ` : '' }
                                ` : ''}
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            // Delete package handler
            $(document).on('click', '.delete-package', function() {
                const packageId = $(this).data('package-id');
                if (confirm('<?= lang('Pages.Confirm_delete_package') ?>')) {
                    $.ajax({
                        url: '<?= base_url('admin-options/package-manager/delete') ?>/' + packageId,
                        type: 'POST',
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.msg);
                                loadPackages(); // Reload the list
                            } else {
                                showToast('error', response.msg);
                            }
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        }
                    });
                }
            });
        });
    </script>  
<?= $this->endSection() //End section('scripts')?>
