<?= $this->extend('layouts/dashboard') ?>

<?php
// Check if features are enabled
$isEnvatoSyncEnabled = $subscriptionChecker->isFeatureEnabled($userData->id, 'Envato_Sync');
?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <?php if(isset($subsection)) : ?>
            <h5 class="mb-0"><?= $subsection ?></h5>
        <?php else : ?>
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($section)) ?></h5>
        <?php endif; ?>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <?php if(isset($section)) : ?>
                    <li class="breadcrumb-item text-capitalize <?= !isset($subsection) ? 'active' : '' ?>" <?= !isset($subsection) ? 'aria-current="page"' : '' ?>><?= lang('Pages.' . ucwords($section)) ?></li>
                <?php endif; ?>

                <?php if(isset($subsection)) : ?>
                    <li class="breadcrumb-item text-capitalize active" aria-current="page"><?= $subsection ?></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>  
<?= $this->endSection() //End section('heading') ?>

<?= $this->section('content') ?>
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-8 mt-4">
            <?php if($subsection === '') { ?>
                <div class="card blog blog-primary blog-detail border-0 shadow rounded">
                    <div class="card-body content">
                        <blockquote class="blockquote mt-3 p-3">
                            <p class="text-muted mb-0 fst-italic"><?= lang('Pages.select_product_for_changelog') ?></p>
                        </blockquote>
                        <div class="text-center">
                            <?php foreach($sideBarMenu['products'] as $productName) { ?>
                                <a href="<?= base_url('product-changelog/'.$productName)?>" class="btn btn-soft-primary mt-2 me-2"><?= $productName ?></a>
                            <?php } ?>                                  
                        </div>
                    </div>
                </div>                         
            <?php } else { ?>
                <div class="card rounded shadow p-4 border-0">
                    <h6 class="mb-3"><?= lang('Pages.Version_Package_and_Changelog') ?></h6>
                    
                    <form class="" novalidate id="changelog-update-form">
                        <input type="hidden" name="productName" id="productName" value="<?= $subsection ?>">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <a class="w-100 btn btn-outline-secondary" href="<?= base_url('product-manager/modify-product/?product=' . $subsection) ?>"><i class="uil uil-edit"></i> <?= lang('Pages.Modify') ?></a>
                            </div>
                            
                            <div class="col-sm-8">
                                    <button class="w-100 btn btn-primary" id="changelog-update-submit"><i class="uil uil-save"></i> <?= lang('Pages.Update') ?></button>
                            </div>												
                            
                            <div class="col-sm-4">
                                <label for="productVersion" class="form-label"><?= lang('Pages.Version') ?></label>
                                <input type="text" class="form-control" id="productVersion" name="productVersion" placeholder="<?= lang('Pages.Version') ?>" value="<?= isset($productDetails[$subsection]['version']) ? $productDetails[$subsection]['version'] : lang('Pages.Theres_problem_in_the_data') ?>"
                                    required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.valid_version_format') ?>
                                </div>
                            </div>

                            <div class="col-sm-8">
                                <label for="productFile" class="form-label">
                                    <?= lang('Pages.Package_File') ?>
                                    
                                    <?php if(isset($productDetails[$subsection]['url'])) { ?>
                                        <span class="text-muted">
                                            [ <a href="<?php echo $productDetails[$subsection]['url'] !== '' ? $productDetails[$subsection]['url'] : 'javascript:void(0)';  ?>" id="currentFileURL"><?= lang('Pages.download') ?></a> | <a href="<?= base_url('product-manager/version-files/?product=' . urlencode($subsection) ) ?>"><?= lang('Pages.upload_file') ?></a> ]
                                        </span>
                                    <?php } ?>
                                </label>													
                                <select class="form-select form-control" id="productFile" name="productFile" required>
                                    <option value=""><?= lang('Pages.Select_File') ?></option>
                                    <?php 
                                    $selectedVersion = isset($productDetails[$subsection]['url']) ? basename($productDetails[$subsection]['url']) : '';
                                    $selectedVersion = urldecode($selectedVersion);

                                    foreach ($productFiles as $product => $files): 
                                    ?>
                                        <?php if ($product === $subsection): ?>
                                            <optgroup label="<?= $product; ?>">
                                                <?php foreach ($files as $file): ?>
                                                    <option value="<?= base_url('download/'.urlencode($subsection).'/'.urlencode($file)); ?>" <?= $selectedVersion === $file ? 'selected' : ''?>><?= $file; ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.Please_select_a_file') ?>
                                </div>
                            </div>

                            <?php if ($isEnvatoSyncEnabled && $myConfig['userEnvatoSyncEnabled']) : ?>
                                <div class="col-sm-4">
                                    <label for="EnvatoItemCode" class="form-label"><?= lang('Pages.Envato_Item_Code') ?></label>
                                    <input type="text" class="form-control" id="EnvatoItemCode" name="EnvatoItemCode" placeholder="<?= lang('Pages.Envato_Item_Code') ?>" value="<?= isset($productDetails[$subsection]['envato_item_code']) ? $productDetails[$subsection]['envato_item_code'] : '' ?>">
                                </div>
                            <?php endif; ?>

                            <div class="col-12 mb-3">
                                <label class="form-label" for="productChangelog"><?= lang('Pages.Changelog') ?></label>

                                <div class="invalid-feedback"> <?= lang('Pages.required_changelog_entry') ?>. </div>
                                
                                <div class="form-icon position-relative">
                                    <i data-feather="file-text" class="fea icon-sm icons"></i>
                                    <textarea name="productChangelog" id="productChangelog" rows="8" class="form-control ps-5" placeholder="<?= lang('Pages.Changelog') ?> :" required><?= isset($productDetails[$subsection]['changelog']) ? $productDetails[$subsection]['changelog'] : lang('Pages.Theres_problem_in_the_data') ?></textarea>                                                            
                                </div>

                                <script type="text/javascript">
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var textarea = document.getElementById('productChangelog');
                                
                                        function adjustTextareaHeight() {
                                            // Set the height based on the scrollHeight to show all content
                                            textarea.style.height = textarea.scrollHeight + 'px';
                                        }
                                
                                        // Update height on input
                                        textarea.addEventListener('input', function() {
                                            adjustTextareaHeight();
                                        });
                                
                                        // Initially set the height to ensure the content is fully visible
                                        adjustTextareaHeight();
                                    });
                                </script>
                                                
                            </div>
                        </div>                                                               
                    </form>
                </div>
            <?php } ?>
        </div><!--end col-->
    </div>
<?= $this->endSection() //End section('content') ?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {
            /*******************************
            // Handle the changelog requests
            *******************************/    
            $('#changelog-update-submit').on('click', function (e) {
                e.preventDefault();					

                var form = $('#changelog-update-form');
                var nameInput = $('#productName');
                var versionInput = $('#productVersion');
                var fileSelect = $('#productFile');
                var changelogTextarea = $('#productChangelog');
                var downloadURL = $('#currentFileURL');
                var submitButton = $(this);

                // Define a regular expression for not allowed characters
                var disallowedCharsRegex_forVersion = /[^0-9.]/g;
                var disallowedCharsRegex_forFile = /[^a-zA-Z0-9._%\-:/]/g;

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');
                
                /*******************
                 * Start validations
                 ******************/     
                
                // Validate the product name is set
                if(nameInput.val() === '') {
                    showToast('danger', '<?= lang('Pages.hidden_input_empty') ?>');
                    disableLoadingEffect(submitButton);
                    return;
                }		 
                
                // Validate the version number
                if(versionInput.val() === '') {
                    versionInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex_forVersion.test(versionInput.val())) {
                    versionInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    versionInput.addClass('is-valid');
                }
                
                // Validate the selected file name
                if(fileSelect.val() === '') {
                    fileSelect.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex_forFile.test(fileSelect.val())) {
                    fileSelect.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    fileSelect.addClass('is-valid');
                }				
                
                // Validate the changelog values
                if(changelogTextarea.val() === '') {
                    changelogTextarea.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    changelogTextarea.addClass('is-valid');
                }				
                
                /*****************
                 * End validations
                 ****************/      

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('product-changelog/update') ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if(response.success) {
                                toastType = 'success'; 
                                // Update the href of #downloadURL
                                downloadURL.attr('href', fileSelect.val());  
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
        });
    </script>
<?= $this->endSection() //End section('scripts')?>