<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.Getting_Started_Guide') ?></h5>

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
<?= $this->endSection() //End section('heading') ?>

<?= $this->section('content') ?>
    <div class="row justify-content-center">
        <div class="col-12 mt-4">
            <?php if($toUpdate === '') { ?>
                <div class="card blog blog-primary blog-detail border-0 shadow rounded">
                    <div class="card-body content">
                        <blockquote class="blockquote mt-3 p-3">
                            <p class="text-muted mb-0 fst-italic"><?= lang('Pages.select_product_for_getting_started_guide') ?></p>
                        </blockquote>
                        <div class="text-center">
                            <?php foreach($sideBarMenu['products'] as $productName) { ?>
                                <a href="<?= base_url('product-manager/gettings-started-guide/?s='.$productName)?>" class="btn btn-soft-primary mt-2 me-2"><?= $productName ?></a>
                            <?php } ?>                                  
                        </div>
                    </div>
                </div>                         
            <?php } else { ?>
                <div class="card rounded shadow p-4 border-0">
                    <div class="d-flex flex-column flex-md-row align-items-center mb-3">
                        <h4 class="mb-3 mb-md-0 me-3 col-12 col-md-auto"><?= $toUpdate ?></h4>

                            <div class="form-icon position-relative col-12 col-md-auto me-lg-3 mx-auto mb-3 mb-md-0">
                                <i data-feather="package" class="fea icon-sm icons"></i>
                                <select class="form-select form-control ps-5" id="selectProduct" onchange="handleProductSelect(this)">
                                    <option value=""><?= lang('Pages.Select_Package') ?></option>
                                    <?php foreach($sideBarMenu['products'] as $productOption) { ?>
                                        <option value="<?= $productOption ?>" <?= $productOption === $toUpdate ? 'selected' : '' ?>>
                                            <?= $productOption ?> 
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                    </div>

                    <p class="mb-3"><?= lang('Pages.getting_started_usage') ?></p>
                    <p class="mb-3"><?= lang('Pages.product_guide_textarea_optional_notice') ?></p>
                    
                    <form class="" novalidate id="product-guide-update-form">
                        <input type="hidden" name="productName" id="productName" value="<?= $toUpdate ?>">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <a class="w-100 btn btn-outline-secondary" href="<?= base_url('product-manager/modify-product/?product=' . $toUpdate) ?>"><i class="uil uil-edit"></i> <?= lang('Pages.Modify') ?></a>
                            </div>
                            
                            <div class="col-sm-8">
                                    <button class="w-100 btn btn-primary" id="product-guide-update-submit"><i class="uil uil-save"></i> <?= lang('Pages.Update') ?></button>
                            </div>

                            <div class="col-12 mb-3">
                                <small class="text-info"> [ <?= lang('Pages.available_values') ?> <span class="copy-to-clipboard">{clientFullName}</span> | <span class="copy-to-clipboard">{licenseKey}</span> | <span class="copy-to-clipboard">{productName}</span> ]</small>
                                <div class="form-icon position-relative mt-3">
                                    <i data-feather="file-text" class="fea icon-sm icons"></i>
                                    <textarea name="productGuideTextarea" id="productGuideTextarea" rows="8" class="form-control ps-5" placeholder="<?= lang('Pages.HTML_codes_here') ?>" required><?= empty(getProductGuide($toUpdate, $userData->id)) ? '' : html_entity_decode(getProductGuide($toUpdate, $userData->id)) ?></textarea>                                                            
                                </div>

                                <script type="text/javascript">
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var textarea = document.getElementById('productGuideTextarea');
                                
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
            // Handle the product guide requests
            *******************************/    
            $('#product-guide-update-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#product-guide-update-form');
                var nameInput = $('#productName');
                var productGuideTextarea = $('#productGuideTextarea'); // Changed to match textarea ID
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');
                
                /*******************
                 * Start validations
                 ******************/     
                
                // Validate both the product name and textarea content
                if(nameInput.val() === '') {
                    showToast('danger', '<?= lang('Pages.hidden_input_empty') ?>');
                    disableLoadingEffect(submitButton);
                    return;
                }

                // Optional: Add validation for textarea if needed
                // if(productGuideTextarea.val() === '') {
                //     responseWrapper.slideUp();
                //     responseWrapper.html('<div class="alert alert-danger fade show text-center" role="alert">Please enter product guide content</div>');
                //     responseWrapper.slideDown();
                //     disableLoadingEffect(submitButton);
                //     return;
                // }
                /*****************
                 * End validations
                 ****************/      

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('product-manager/gettings-started-guide-update') ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if(response.success) {
                                toastType = 'success'; 
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

        function handleProductSelect(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = `<?= base_url('product-manager/gettings-started-guide?s=') ?>${selectedValue}`;
            }
        }
    </script>
<?= $this->endSection() //End section('scripts')?>