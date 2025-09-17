<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <?php if($subsection !== '') { ?>
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection) ) ?></h5>
        <?php } else { ?>
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($section) ) ?></h5>
        <?php } ?>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <li class="breadcrumb-item text-capitalize <?= !$subsection ? 'active aria-current="page"' : '' ?>">
                    <?= $subsection ? '<a href="' . base_url('product-manager') .'">' : '' ?>
                    <?= lang('Pages.Product_manager') ?>
                    <?= $subsection ? '</a>' : '' ?>
                </li>

                <?php if($subsection) { ?>
                    <li class="breadcrumb-item text-capitalize active" aria-current="page"><?= lang('Pages.' . ucwords($subsection) ) ?></li>                                   
                <?php } ?>
            </ul>
        </nav>
    </div>
<?= $this->endSection() //End section('heading')?>

<?= $this->section('content') ?>
    <div class="row justify-content-center">
        <?php if($subsection === '') { ?> 
            <div class="col-12 text-center mt-4">
                <ul class="nav nav-pills shadow flex-column flex-sm-row d-md-inline-flex mb-0 p-1 bg-white-color rounded position-relative overflow-hidden justify-content-center" id="pills-tab" role="tablist">
                    <li class="nav-item m-1" role="presentation">
                        <a class="nav-link py-2 px-5 rounded active" id="create-product" data-bs-toggle="pill" href="#create_product" role="tab" aria-controls="create" aria-selected="true">
                            <div class="text-center">
                                <h6 class="mb-0"><i class="uil uil-plus-circle h5 align-middle me-2 mb-0"></i> <?= lang('Pages.Create_product') ?></h6>
                            </div>
                        </a><!--end nav link-->
                    </li><!--end nav item-->
                    
                    <li class="nav-item m-1" role="presentation">
                        <a class="nav-link py-2 px-5 rounded" id="modify-product" data-bs-toggle="pill" href="#modify_product" role="tab" aria-controls="modify" aria-selected="false" tabindex="-1">
                            <div class="text-center">
                                <h6 class="mb-0"><i class="uil uil-edit h5 align-middle me-2 mb-0"></i> <?= lang('Pages.Modify_product') ?></h6>
                            </div>
                        </a><!--end nav link-->
                    </li><!--end nav item-->

                    <li class="nav-item m-1" role="presentation">
                        <a class="nav-link py-2 px-5 rounded" id="version-files" data-bs-toggle="pill" href="#version_files" role="tab" aria-controls="upload" aria-selected="false" tabindex="-1">
                            <div class="text-center">
                                <h6 class="mb-0"><i class="uil uil-upload h5 align-middle me-2 mb-0"></i> <?= lang('Pages.Version_files') ?></h6>
                            </div>
                        </a><!--end nav link-->
                    </li><!--end nav item-->

                    <li class="nav-item m-1" role="presentation">
                        <a class="nav-link py-2 px-5 rounded" id="assign-product-variation-tab" data-bs-toggle="pill" href="#assign_product_variation" role="tab" aria-controls="assign-product-variation" aria-selected="false" tabindex="-1">
                            <div class="text-center">
                                <h6 class="mb-0"> <i class="uil uil-books h5 align-middle me-2 mb-0"></i><?= lang('Pages.Assign_variation') ?></h6>
                            </div>
                        </a><!--end nav link-->
                    </li><!--end nav item-->

                    <li class="nav-item m-1" role="presentation">
                        <a class="nav-link py-2 px-5 rounded" id="product-variations-tab" data-bs-toggle="pill" href="#product_variations" role="tab" aria-controls="product-variations" aria-selected="false" tabindex="-1">
                            <div class="text-center">
                                <h6 class="mb-0"> <i class="uil uil-tag h5 align-middle me-2 mb-0"></i><?= lang('Pages.Product_variations') ?></h6>
                            </div>
                        </a><!--end nav link-->
                    </li><!--end nav item-->

                    <li class="nav-item m-1" role="presentation">
                        <a class="nav-link py-2 px-5 rounded" id="product-getting-started-tab" href="<?= base_url('product-manager/gettings-started-guide')?>">
                            <div class="text-center">
                                <h6 class="mb-0"> <i class="uil uil-bookmark h5 align-middle me-2 mb-0"></i><?= lang('Pages.Getting_Started_Guide') ?></h6>
                            </div>
                        </a><!--end nav link-->
                    </li><!--end nav item-->
                </ul>
            </div>
            
        <?php } ?>

        <div class="col-12 mt-4">
            <div class="tab-content rounded-0 shadow-0" id="pills-tabContent">

                <!-- Create Product -->
                <div class="tab-pane fade <?php echo ($subsection === '') || ($subsection === 'create_product') ? 'show active' : ''; ?>" id="create_product" role="tabpanel" aria-labelledby="create-product">                                                                                
                    <div class="card rounded shadow p-4 border-0">
                        <h4 class="mb-3"><?= lang('Pages.Add_New_Product_To_Manage') ?></h4>
                        <form class="" novalidate action="javascript:void(0)" id="create-product-form">
                            <div class="row gy-3">

                                <div class="col-12 mb-3">
                                    <label for="new-productName" class="form-label"><?= lang('Pages.New_Product_Name') ?></label>
                                    <input type="text" class="form-control" id="new-productName" name="new-productName" placeholder="<?= lang('Pages.Enter_product_name') ?>"
                                        required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.Please_enter_a_valid_product_name') ?>
                                    </div>
                                </div>

                            </div>

                            <div class="col-12 text-center">
                                <button class="mx-auto btn btn-primary" id="create-product-submit"><i class="uil uil-plus"></i> <?= lang('Pages.Create_New_Product') ?></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modify Product -->
                <div class="tab-pane fade rounded <?php echo $subsection === 'modify_product' ? 'show active' : ''; ?>" id="modify_product" role="tabpanel" aria-labelledby="modify-product">                                        
                    <div class="card rounded shadow p-4 border-0">
                        <h4 class="mb-3"><?= lang('Pages.Rename_or_Delete_Existing_Product') ?></h4>
                        <form class="" novalidate action="javascript:void(0)" id="modify-product-form">
                            <div class="row gy-3">

                                <div class="col-12">
                                    <label for="modify-productName" class="form-label"><?= lang('Pages.Product') ?></label>
                                    
                                    <select class="form-select form-control" id="modify-productName" name="modify-productName" onchange="updateRenameChangelogDeleteButton()" required>
                                        <option value=""><?= lang('Pages.Select_Product') ?></option>
                                        <?php foreach($sideBarMenu['products'] as $productName) { ?>
                                            <option value="<?= $productName ?>" <?php echo $selectedProduct == $productName ? 'selected' : ''; ?>><?= $productName ?></option>
                                        <?php }?>
                                    </select>
                                    
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.please_select_product_feedback') ?>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <button class="w-100 btn btn-primary" data-bs-toggle="modal" data-bs-target="#rename-product-modal-popup" id="rename-product-modal" <?php echo !$selectedProduct ? 'disabled' : '' ?>><i class="uil uil-edit"></i> <?= lang('Pages.Rename') ?></button>
                                </div>

                                <div class="col-sm-3">
                                    <a class="w-100 btn btn-outline-secondary" href="<?php echo $selectedProduct ? base_url('product-manager/gettings-started-guide?s=' . $selectedProduct) : 'javascript:void(0)' ?>" id="go-to-product-guide-btn"><i class="uil uil-list-ul"></i> <?= lang('Pages.Product_Guide') ?></a>
                                </div>

                                <div class="col-sm-3">
                                    <a class="w-100 btn btn-outline-secondary" href="<?php echo $selectedProduct ? base_url('product-changelog/' . $selectedProduct) : 'javascript:void(0)' ?>" id="go-to-changelog-btn"><i class="uil uil-history"></i> <?= lang('Pages.Changelog') ?></a>
                                </div>

                                <div class="col-sm-3">
                                    <button class="w-100 btn btn-outline-danger" id="delete-product-submit" <?php echo !$selectedProduct ? 'disabled' : '' ?>><i class="uil uil-trash"></i> <?= lang('Pages.Delete') ?></button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div><!--end teb pane-->

                <!-- Release New Version -->
                <div class="tab-pane fade rounded <?php echo $subsection === 'version_files' ? 'show active' : ''; ?>" id="version_files" role="tabpanel" aria-labelledby="version-files">                                        
                    <div class="card rounded shadow p-4 border-0 mb-3">
                        <h4 class="mb-3"><?= lang('Pages.Upload_or_Delete_Release_Package') ?></h4>
                        <form class="mb-3" novalidate action="javascript:void(0)" id="version-files-form" >
                            <div class="row gy-3">

                                <div class="col-sm-6">
                                    <label for="upload-productName" class="form-label"><?= lang('Pages.Product') ?></label>

                                    <select class="form-select form-control" id="upload-productName" name="upload-productName" required onchange="listFilesAvailable(); updateRenameChangelogDeleteButton();">
                                        <option value=""><?= lang('Pages.Select_Product') ?></option>
                                        <?php foreach($sideBarMenu['products'] as $productName) { ?>
                                            <option value="<?= $productName ?>" <?php echo $selectedProduct == $productName ? 'selected' : ''; ?>><?= $productName ?></option>
                                        <?php }?>
                                    </select>
                                    
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.please_select_product_feedback') ?>
                                    </div>
                                </div>                                          

                                <div class="col-sm-6">
                                    <label for="release-Package" class="form-label"><?= lang('Pages.Release_Package') ?></label>
                                    
                                    <?php
                                    if ($myConfig['acceptedFileExtensions']) {
                                        $fileExtensions = json_decode(str_replace('\r', '', $myConfig['acceptedFileExtensions']), true);
                                    
                                        if (count($fileExtensions) === 1) {
                                            // Handle case when there's only one file extension
                                            $fileExtensions = '.' . $fileExtensions[0];
                                        } else {
                                            $fileExtensions = '.'. implode(',.', $fileExtensions);
                                        }
                                    }
                                    
                                    echo '('.$fileExtensions.')';
                                    ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm mb-2 hide" id="toggle-upload-mode"><?= lang('Pages.Switch_to_Multi_file_Upload') ?></button>
                                    <input class="form-control multi-file-upload" name="release-Package-multi[]" id="release-Package-multi" type="file" accept="<?= $fileExtensions ?>" multiple required>
                                    <input class="form-control single-file-upload" name="release-Package" id="release-Package" type="file" accept="<?= $fileExtensions ?>" style="display:none;">
                                    <div class="invalid-feedback single-file-feedback">
                                        <?= lang('Pages.release_package_upload_feedback', ['acceptedFileExtensions' => $fileExtensions]) ?>
                                    </div>
                                    <div class="invalid-feedback multi-file-feedback" style="display:none;">
                                        <?= lang('Pages.release_package_upload_feedback', ['acceptedFileExtensions' => $fileExtensions]) ?>
                                    </div>
                                </div>       

                                <div class="col-sm-4">
                                    <a class="w-100 btn btn-outline-secondary" href="<?php echo $selectedProduct ? base_url('product-manager/gettings-started-guide?s=' . $selectedProduct) : 'javascript:void(0)' ?>" id="go-to-product-guide-btn2"><i class="uil uil-list-ul"></i> <?= lang('Pages.Product_Guide') ?></a>
                                </div> 
                                
                                <div class="col-sm-4">
                                    <a class="w-100 btn btn-outline-secondary" href="<?php echo $selectedProduct ? base_url('product-changelog/' . $selectedProduct) : 'javascript:void(0)' ?>" id="go-to-changelog-btn2"><i class="uil uil-history"></i> <?= lang('Pages.Changelog') ?></a>
                                </div>                                                       

                                <div class="col-sm-4">
                                    <button class="w-100 btn btn-primary" id="version-files-submit"><i class="uil uil-cloud-upload"></i> <?= lang('Pages.Upload_File') ?></button>
                                </div>
                                                                                    
                            </div>
                        </form>
                    </div>

                    <div class="card rounded shadow p-4 border-0 mb-3">
                        <h4 class="mb-3"><?= lang('Pages.Version_files') ?></h4>
                        <div class="row gy-3">

                            <div class="col-12" id="no-selected-file-responseMsg">
                                    <?php if(!$selectedProduct) { ?>
                                
                                        <div class="alert bg-soft-primary fade show text-center" role="alert"><?= lang('Pages.Select_product_to_view_files') ?></div>
                                    
                                <?php } ?>
                            </div>
                                                                        
                            <div class="col-12" id="table-file-wrapper" <?= !$selectedProduct ? 'style="display:none"' : '' ?>>
                                <div class="table-responsive rounded">
                                    <form novalidate="" action="javascript:void(0)" id="delete-file-form">
                                        <input type="hidden" name="productFolder" id="productFolder" value="<?= $selectedProduct ? $selectedProduct : '' ?>">
                                        <table class="table table-striped mb-3">
                                            <thead>
                                                <tr>
                                                    <th class="p-3 border-bottom" style="width: 50px;">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="checkAll">
                                                        </div>
                                                    </th>
                                                    <th class="border-bottom" style="min-width: 200px;"><?= lang('Pages.File_Name') ?></th>
                                                </tr>
                                            </thead>

                                            <tbody id="product-file-list">
                                                <?php if($selectedProduct) { 
                                                        if(isset($productFiles) && !empty($productFiles) && array_key_exists($selectedProduct, $productFiles)) {
                                                            foreach($productFiles[$selectedProduct] as $productFile) { ?>
                                                                <tr>
                                                                    <td class="p-3">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" value="<?= urldecode($productFile) ?>" id="<?= urldecode($productFile) ?>" name="<?= urldecode($productFile) ?>">
                                                                        </div>
                                                                    </td>

                                                                    <td class="align-middle">
                                                                        <label for="<?= $productFile ?>" class="form-label"><?= $productFile ?>
                                                                            <small class="text-muted"> [ <a href="<?= base_url('download/' . $selectedProduct . '/' . $productFile) ?>"><?= lang('Pages.download') ?></a> ]</small>
                                                                        </label>
                                                                    </td>
                                                                </tr>                                                                            
                                                    <?php }
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        
                                        <div class="col-12 text-center">
                                            <button class="mx-auto btn btn-outline-danger" id="delete-file-submit"><i class="uil uil-trash"></i> <?= lang('Pages.Delete') ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>                                      
                    </div>
                </div><!--end teb pane-->

                <!-- Setup Product Variations -->
                <div class="tab-pane fade rounded <?php echo $subsection === 'assign_product_variation' ? 'show active' : ''; ?>" id="assign_product_variation" role="tabpanel" aria-labelledby="assign-product-variation">                                        
                    <div class="card rounded shadow p-4 border-0">
                        <h4 class="mb-3"><?= lang('Pages.Setup_The_Variation') ?></h4>
                        
                        <div class="row gy-3">
                            <div class="col-sm-6">
                                <label for="variation_select" class="form-label"><?= lang('Pages.Select_A_Variation') ?></label>
                                <select class="form-select form-control" id="variation_select" name="variation_select" onchange="showProductsearchVariation()" required>
                                    <option value=""><?= lang('Pages.Select_Variation') ?></option>
                                    <?php
                                    $variationFilePath = USER_DATA_PATH . $userData->id . '/'. $myConfig['userAppSettings'] . 'product-variations.json';
                                    $variationList = json_decode(file_get_contents($variationFilePath), true);
                                    if (!empty($variationList)) {
                                        foreach ($variationList as $key => $variation) {
                                            if (!empty($key)) {
                                                echo '<option value="' . htmlspecialchars(str_replace(' ', '_', $key)) . '">' . htmlspecialchars($key) . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.select_variation_feedback') ?>
                                </div>
                            </div>
                            <div class="col-sm-6" id="productWrapper">
                                <?php
                                if (!empty($variationList)) {
                                    foreach ($variationList as $key => $includedProducts) {
                                        if (!empty($key)) { ?>
                                            <div id="<?= str_replace(' ', '_', $key) ?>-wrapper" style="display:none">
                                                <label for="<?= str_replace(' ', '_', $key) ?>-group" class="form-label"><?= lang('Pages.list_product_with_this_variation') ?> "<?= $key ?>"</label>
                                                <select class="form-select form-control" id="<?= str_replace(' ', '_', $key) ?>-group" name="<?= str_replace(' ', '_', $key) ?>-group" multiple="multiple" style="height: 150px;" onchange="updateVariationGroup()">
                                                    <?php foreach ($sideBarMenu['products'] as $productName) { ?>
                                                        <option value="<?= $productName ?>" <?= strpos($includedProducts,$productName) !== false ? 'selected' : '' ?>><?= $productName ?></option>
                                                    <?php } ?>
                                                </select>
                                                
                                            </div>
                                        <?php }
                                    }
                                }
                                ?>
                            </div>
                            <form class="mb-3" novalidate action="javascript:void(0)" id="set-variations-form">
                                <?php
                                if (!empty($variationList)) {
                                    foreach ($variationList as $key => $includedProducts) {
                                        if (!empty($key)) {
                                            echo ' <input type="hidden" id="' . str_replace(' ', '_', $key) . '" name="' . str_replace(' ', '_', $key) . '" value="' . $includedProducts . '">';
                                        }
                                    }
                                }
                                ?>
                                <div class="col-12 text-center">
                                    <button class="mx-auto btn btn-primary" id="set-variations-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save') ?></button>
                                </div>
                            </form> 
                        </div>                                       
                    </div>
                </div><!--end teb pane-->
                
                <!-- Configure Product's Variations -->
                <div class="tab-pane fade rounded <?php echo $subsection === 'product_variations' ? 'show active' : ''; ?>" id="product_variations" role="tabpanel" aria-labelledby="product-variations">                                        
                    <div class="card rounded shadow p-4 border-0 mb-3">
                        <h4 class="mb-3"><?= lang('Pages.Add_A_Variation') ?></h4>
                        <div class="row g-3">                                        

                            <div class="col-12">
                                <label for="variationInput" class="form-label text-muted"><?= lang('Pages.allowed_variation_chars') ?></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text bg-light text-muted border">
                                        <i class="uil uil-tag align-middle"></i>
                                    </span>
                                    <input type="text" class="form-control" id="variationInput" name="variationInput" placeholder="<?= lang('Pages.Enter_a_variation') ?>" required>
                                    <div class="invalid-feedback"><?= lang('Pages.variation_input_feedback') ?></div>
                                </div>
                            </div>

                            <div class="col-12 text-center">                                            
                                <a class="mx-auto btn btn-secondary" name="add-variation-button" id="add-variation-button"><i class="uil uil-plus"></i> <?= lang('Pages.Add') ?></a>
                            </div>  
                        </div>
                    </div>
                    
                    <div class="card rounded shadow p-4 border-0 mb-3">
                        <h4 class="mb-3"><?= lang('Pages.Modify_Delete_and_Save_Variation_List') ?></h4>
                        <div class="row g-3">

                            <div class="col-sm-6">
                                <label for="productVariations" class="form-label"><?= lang('Pages.Current_Variations') ?></label>	
                                <select class="form-select form-control" style="height: 150px;" id="productVariations" name="productVariations[]" multiple="multiple" required="" onchange="updateRenameVariationButton()">
                                    <?php
                                    $variationFilePath = USER_DATA_PATH . $userData->id . '/'. $myConfig['userAppSettings'] . 'product-variations.json';
                                    $variationList = json_decode(file_get_contents($variationFilePath), true);
                                    $hiddenInputValue = '';
                                    $count = 0;
                                    if (!empty($variationList)) {
                                        foreach ($variationList as $key => $variation) {
                                            
                                            if (!empty($key)) {
                                                echo '<option value="' . htmlspecialchars($key) . '">' . htmlspecialchars($key) . '</option>';
                                                $hiddenInputValue = $count === 0 ? $key : $hiddenInputValue . ',' . $key;
                                                $count++;
                                            }
                                        }
                                    }
                                    ?>
                                </select>

                            </div>

                            <div class="col-sm-6">                                                                                          
                                <button class="w-100 btn btn-outline-danger mt-4 mb-3" id="removeSelected" disabled><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>
                                <button class="w-100 btn btn-outline-secondary mb-3" data-bs-toggle="modal" data-bs-target="#modify-variation-modal-popup" id="modifySelected" disabled><i class="uil uil-edit"></i> <?= lang('Pages.Modify_Selected') ?></button>
                                <a class="w-100 btn btn-outline-secondary" href="<?= base_url('product-manager/assign-product-variation') ?>" ><i class="uil uil-check-square"></i> <?= lang('Pages.Assign_product_variation') ?></a>
                            </div>
                            <div class="col-sm-6">
                                <form class="mb-3" novalidate id="add-variation-form">
                                    <input type="hidden" id="hiddenVariationsInput" name="hiddenVariationsInput" value="<?= $hiddenInputValue ?>">
                                    <div class="col-12 text-center">
                                        <a class="mx-auto btn btn-primary" id="saveVariations"><i class="uil uil-save"></i> <?= lang('Pages.Save_Variation_List') ?></a>
                                    </div>
                                </form>
                            </div>                                        
                        </div>
                    </div>
                </div><!--end tab pane-->                                    

            </div>
        </div><!--end col-->                            
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <div class="modal fade" id="rename-product-modal-popup" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>                    
                <div class="modal-body py-5">
                    <div class="text-center">
                        <div class="icon d-flex align-items-center justify-content-center bg-soft-danger rounded-circle mx-auto" style="height: 95px; width:95px;">
                            <h1 class="mb-0"><i class="uil uil-edit align-middle"></i></h1>
                        </div>
                        <div class="mt-4">
                            <h4 id="modal-product-name"><?= $selectedProduct ?? ''?></h4>
                            <form class="mb-3" novalidate action="javascript:void(0)" id="rename-product-form" >
                                <div class="row gy-3">
                                    <div class="col-12 mb-3">
                                        <label for="rename-productName" class="form-label"><?= lang('Pages.Rename_the_following_product') ?></label>
                                        <input type="text" class="form-control text-center" id="rename-productName" name="rename-productName" placeholder="Enter product name" value="<?= $selectedProduct ?? ''?>" required>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_enter_a_valid_product_name') ?>
                                        </div>
                                    </div>                                                                                           
                                </div>

                                <div class="col-12 text-center">
                                    <button class="mx-auto btn btn-primary" id="rename-product-submit"><i class="uil uil-save"></i> <?= lang('Pages.Submit') ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="modify-variation-modal-popup" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">              
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>                        
                <form class="mb-3" novalidate action="javascript:void(0)" id="modify-variation-form">
                    <div class="modal-body py-5">
                        <div class="text-center">
                            <div class="icon d-flex align-items-center justify-content-center bg-soft-danger rounded-circle mx-auto" style="height: 95px; width:95px;">
                                <h1 class="mb-0"><i class="uil uil-edit align-middle"></i></h1>
                            </div>
                            <div class="mt-4">
                                <h4><?= lang('Pages.Rename_Variation') ?></h4>
                                    <div class="row gy-3">
                                        <div class="col-12" id="modifyVariation-responseMsg"></div>
                                        
                                        <!-- This div will be populated dynamically based on selected options -->                                            

                                        
                                    </div> 

                                    <div class="col-12 text-center">
                                        <button class="mx-auto btn btn-primary" id="modify-variation-submit"><i class="uil uil-save"></i> <?= lang('Pages.Submit') ?></button>      
                                    </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        <?php if(!$subsection) : ?>
            $('#sidebarProductManagerSection > a').trigger('click');
        <?php endif; ?>

        // Global variable declaration
        var completeFileList = '<?= isset($productFiles) ? json_encode($productFiles) : '' ?>';
        
        var selectedProduct = '';
        
        // Debounce function to limit how often a function can be called
        function debounce(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    func.apply(context, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Function to enable/disable the delete button based on checkbox state
        function updateDeleteButtonState() {
            var anyCheckboxChecked = $('tbody#product-file-list input[type="checkbox"]:checked').length > 0;
            $('#delete-file-submit').prop('disabled', !anyCheckboxChecked);
        }
        
        var debouncedUpdateDeleteButtonState = debounce(updateDeleteButtonState, 300);

        $(document).ready(function() {
            // Function to update selectedProduct when dropdown changes
            function updateSelectedProduct() {
                selectedProduct = $("#upload-productName").val();
                console.log("Selected product:", selectedProduct);

                // Update the button links accordingly
                updateRenameChangelogDeleteButton();
                
                // Load the file list according to the initial data
                listFilesAvailable();

            }

            // Set initial value if needed
            selectedProduct = $("#upload-productName").val();
            
            // Attach the event listener
            $("#upload-productName").on("change", updateSelectedProduct);

            // Load the file list according to the initial data
            listFilesAvailable();

            // Toggle upload mode button handler
            $('#toggle-upload-mode').on('click', function() {
                var multiFileInput = $('.multi-file-upload');
                var singleFileInput = $('.single-file-upload');
                var multiFileFeedback = $('.multi-file-feedback');
                var singleFileFeedback = $('.single-file-feedback');
                var toggleButton = $(this);

                if (multiFileInput.is(':visible')) {
                    // Switch to single file upload
                    multiFileInput.hide();
                    multiFileFeedback.hide();
                    singleFileInput.show();
                    singleFileFeedback.show();
                    singleFileInput.prop('required', true);
                    multiFileInput.prop('required', false);
                    toggleButton.text('<?= lang('Pages.Switch_to_Multi_file_Upload') ?>');
                } else {
                    // Switch to multi-file upload
                    multiFileInput.show();
                    multiFileFeedback.show();
                    singleFileInput.hide();
                    singleFileFeedback.hide();
                    singleFileInput.prop('required', false);
                    multiFileInput.prop('required', true);
                    toggleButton.text('<?= lang('Pages.Switch_to_Single_File_Upload') ?>');
                }
            });

            /***************************
            // Handle the Version Files
            ***************************/
            $('#version-files-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#version-files-form');
                var productNameSelect = $('#upload-productName');
                var singleFileInput = $('.single-file-upload');
                var multiFileInput = $('.multi-file-upload');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // Product name validation
                if (productNameSelect.val() === '') {
                    productNameSelect.addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                } else {
                    productNameSelect.addClass('is-valid');
                }

                // Determine which input is visible
                var isMultiFile = multiFileInput.is(':visible');
                var files = isMultiFile ? multiFileInput[0].files : singleFileInput[0].files;

                // Validate files presence
                if (!files || files.length === 0) {
                    if (isMultiFile) {
                        multiFileInput.addClass('is-invalid');
                        $('.multi-file-feedback').show();
                    } else {
                        singleFileInput.addClass('is-invalid');
                        $('.single-file-feedback').show();
                    }
                    disableLoadingEffect(submitButton);
                    return;
                } else {
                    if (isMultiFile) {
                        multiFileInput.removeClass('is-invalid');
                        $('.multi-file-feedback').hide();
                    } else {
                        singleFileInput.removeClass('is-invalid');
                        $('.single-file-feedback').hide();
                    }
                }

                // Validate file extensions
                var acceptedExtensions = [];
                <?php
                if ($myConfig['acceptedFileExtensions']) {
                    $fileExtensions = json_decode(str_replace('\r', '', $myConfig['acceptedFileExtensions']), true);
                    foreach ($fileExtensions as $ext) {
                        echo "acceptedExtensions.push('".$ext."');\n";
                    }
                }
                ?>

                var invalidFiles = [];
                for (var i = 0; i < files.length; i++) {
                    var fileName = files[i].name;
                    var fileExtension = fileName.split('.').pop().toLowerCase();
                    if (acceptedExtensions.indexOf(fileExtension) === -1) {
                        invalidFiles.push(fileName);
                    }
                }

                if (invalidFiles.length > 0) {
                    var errorMsg = 'Invalid file extensions: ' + invalidFiles.join(', ');
                    showToast('danger', errorMsg);
                    disableLoadingEffect(submitButton);
                    return;
                }

                // Prepare FormData
                var data = new FormData();
                data.append('productName', productNameSelect.val());

                if (isMultiFile) {
                    for (var i = 0; i < files.length; i++) {
                        data.append('releasePackageMulti[]', files[i]);
                    }
                } else {
                    data.append('releasePackage', files[0]);
                }

                // Clear previous upload status container if any
                $('#upload-status-container').remove();

                // Create a container to show upload status
                var statusContainer = $('<div id="upload-status-container" class="mt-3"></div>');
                form.append(statusContainer);

                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('product-manager/version-files-action') ?>',
                    method: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        resetValidations(form);

                        // Clear file inputs
                        singleFileInput.val('');
                        multiFileInput.val('');

                        // Update the global variable
                        completeFileList = JSON.stringify(response.current_files);
                        listFilesAvailable();

                        // Show individual file upload results if available
                        if (response.fileResults && Array.isArray(response.fileResults)) {
                            response.fileResults.forEach(function (fileResult) {
                                var toastType = fileResult.success ? 'success' : 'danger';
                                var message = fileResult.fileName + ': ' + fileResult.message;
                                showToast(toastType, message);
                            });
                        } else {
                            // Fallback to general message
                            var toastType = response.success ? 'success' : 'danger';
                            showToast(toastType, response.msg);
                        }
                    },
                    error: function (xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    },
                    complete: function () {
                        disableLoadingEffect(submitButton);
                    }
                });
            });

            /*********************************
            // Handle the delete product files
            *********************************/
            $('#delete-file-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#delete-file-form');
                var submitButton = $(this);

                // Get the selected file names
                var selectedFiles = [];
                $('tbody#product-file-list input[type="checkbox"]:checked').each(function () {
                    selectedFiles.push($(this).attr('id'));
                });

                // Remove existing hidden inputs before adding new ones
                form.find('input[name="selectedFiles[]"]').remove();

                // Add the selected files to the form data
                $.each(selectedFiles, function (index, fileName) {
                    form.append('<input type="hidden" name="selectedFiles[]" value="' + fileName + '">');
                });

                // Enable loading effect
                enableLoadingEffect(submitButton);

                var data = new FormData(form[0]);
                // Append additional data to the FormData object
                data.append('productFolder', selectedProduct);

                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_files') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('product-manager/delete-product-files-action') ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            // console.log('Deleted Files:\n' + (response.deleted_files.length > 0 ? response.deleted_files.join('\n') : '<?= lang('Pages.None') ?>'));
                            // console.log('Failed Files:\n' + (response.failed_files.length > 0 ? response.failed_files.join('\n') : '<?= lang('Pages.None') ?>'));

                            if (response.status == 1) {
                                // Response fully success
                                let resultSuccessResult = response.deleted_files.length > 0 ? 
                                    '<?= lang('Pages.list_success_deleted_files') ?>:<br>' + response.deleted_files.join('<br>') : 
                                    null;

                                let resultFailedResult = response.failed_files.length > 0 ? 
                                    '<?= lang('Pages.list_failed_deletion_files') ?>:<br>' + response.failed_files.join('<br>') : 
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
                            
                            submitButton.prop('disabled', true);

                            // Update the global variable
                            completeFileList = JSON.stringify(response.current_files);
                            
                            listFilesAvailable();
                            
                            // Update delete button state
                            debouncedUpdateDeleteButtonState();
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

            /*******************************
            // Handle the Create New Product
            *******************************/
            $('#create-product-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#create-product-form');
                var productName = $('#new-productName');
                var submitButton = $(this);	

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // Define a regular expression for not allowed characters
                var disallowedCharsRegex    = /[~@!#$%&*\-_+=|:.]/;

                /*******************
                 * Start validations
                 ******************/

                // Product name validation
                if(productName.val() === '') {
                    productName.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex.test(productName.val())) {
                    productName.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    productName.addClass('is-valid');
                }   		

                /*****************
                 * End validations
                 ****************/

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Form data is valid, proceed with further processing
                    var data = new FormData(form[0]);

                    // Append additional data to the FormData object
                    data.append('productName', productName.val());

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('product-manager/new-product-action') ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,					
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success';
                                resetForm(form);

                                completeFileList = JSON.stringify(response.current_files);
                                
                                // Call the updateSidebar function with the newly created product name
                                updateSidebarAndSelectOptions();

                                // Call fetchProductFiles() to reload the file list
                                listFilesAvailable();

                                // Call to refresh the variations and products therein
                                fetchProductVariations();
                                
                            } else if (response.status == 2) {                
                                // Response success but with error
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
                }
            });

            /***************************
            // Handle the Rename Product
            ***************************/
            $('#rename-product-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#rename-product-form');
                var oldProductName = $('#modify-productName');
                var newProductName = $('#rename-productName');
                var submitButton = $(this);		

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // Define a regular expression for not allowed characters
                var disallowedCharsRegex    = /[~@!#$%&*\-_+=|:.]/;			

                /*******************
                 * Start validations
                 ******************/

                // Product name validation
                if(newProductName.val() === '') {
                    newProductName.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex.test(newProductName.val())) {
                    newProductName.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    newProductName.addClass('is-valid');
                } 		

                /*****************
                 * End validations
                 ****************/

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Form data is valid, proceed with further processing
                    var data = new FormData(form[0]);

                    // Append additional data to the FormData object
                    data.append('oldProductName', oldProductName.val());
                    data.append('newProductName', newProductName.val());

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('product-manager/rename-product-action') ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,					
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success';

                                resetForm(form);

                                // Update the global variable
                                completeFileList = JSON.stringify(response.current_files);
                                
                                listFilesAvailable();

                                // Response fully success
                                resetValidations(form);

                                // Call the updateSidebar function to update sidebar menu
                                updateSidebarAndSelectOptions();

                                // Call to refresh the variations and products therein
                                fetchProductVariations();

                                $('#rename-product-modal-popup').modal('hide');

                            } else if (response.status == 2) { 
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
            
            /***************************
            // Handle the Delete Product
            ***************************/
            $('#delete-product-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#modify-product-form');
                var productName = $('#modify-productName');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                /*******************
                 * Start validations
                 ******************/

                // Product name validation
                // if(productName.val() === '') {
                // 	productName.addClass('is-invalid');

                // 	// Disable loading effect
                // 	disableLoadingEffect(submitButton);	
                // } else {
                // 	productName.addClass('is-valid');
                // }   		

                /*****************
                 * End validations
                 ****************/

                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_product') ?>");

                if (confirmDelete) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('product-manager/delete-whole-product-action') ?>',
                        method: 'POST',
                        data: { 'modify-productName': productName.val() },
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success';

                                // Response fully success
                                resetValidations(form);

                                // Update the global variable
                                completeFileList = JSON.stringify(response.current_files);
                                
                                listFilesAvailable();

                                // Call the updateSidebar function to update sidebar menu
                                updateSidebarAndSelectOptions();

                                // Call to refresh the variations and products therein
                                fetchProductVariations();
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
                } 
                else {
                    // User cancelled the deletion action, so disable loading effect
                    disableLoadingEffect(submitButton);
                }
            });

            /*******************************
            // Handle the product variations
            *******************************/	
            $("#add-variation-button").click(function(){
                var newVariation = $("#variationInput").val().trim();
                var savedVariations = $("#productVariations");

                // Define a regular expression for not allowed characters
                // var disallowedCharsRegex = /[~@!#$%&*\-_+=|:.]/;
                var disallowedCharsRegex = /[^a-zA-Z0-9\s-]/;

                // Check for duplicate variations
                var isDuplicate = savedVariations.find('option[value="' + newVariation + '"]').length > 0;

                // Start validations
                if(newVariation === '') {
                    $("#variationInput").addClass('is-invalid');
                } else if (disallowedCharsRegex.test(newVariation)) {
                    $("#variationInput").addClass('is-invalid');
                } else if (isDuplicate) {
                    // Show response message for duplicated variation name
                    showToast('danger', '<?= lang('Pages.duplicated_variation_not_allowed') ?>');
                } else {
                    // Add variation to select and hidden input
                    $("#variationInput").removeClass('is-invalid');
                    savedVariations.append('<option value="' + newVariation + '">' + newVariation + '</option>');
                    $("#variationInput").val('');

                    // Update hidden input
                    updateVariationsInput();

                    // Update modal for variation rename
                    updateModalContent();
                } 
            });

            // Remove Selected Button Click Event
            $("#removeSelected").click(function () {
                var selectedOptions = $("#productVariations option:selected");

                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_variation') ?>");

                if (confirmDelete) {			
                    selectedOptions.each(function () {
                        $(this).remove();
                    });

                    // Update hidden input
                    updateVariationsInput();
                    $(this).prop('disabled', true);
                    $('#modifySelected').prop('disabled', true);
                }
            });

            // Function to update hidden input with selected options
            function updateVariationsInput() {
                var selectedValues = [];
                $("#productVariations option").each(function() {
                    selectedValues.push($(this).val());
                });
                $("#hiddenVariationsInput").val(selectedValues.join(','));
            }

            // Handle Save variations
            $('#saveVariations').on('click', function (e) {
                e.preventDefault();

                var form = $('#add-variation-form');
                var variationList = $('#hiddenVariationsInput');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('manage-products/product-variations/save') ?>',
                    method: 'POST',
                    data: { 'variationList': variationList.val() },
                    success: function (response) {
                        let toastType = 'info';

                        if (response.status == 1) {
                            toastType = 'success';
                        } else if (response.status == 2) {
                            // Response success but with error
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

            // Handle modify variation name
            // Function to update modal content based on selected options
            function updateModalContent() {
                var selectedOptions = $('#productVariations').val();
                var modalBody = $('#modify-variation-modal-popup .modal-body');
                
                modalBody.find('.variation-input').remove(); // Remove previous variation inputs
                
                $.each(selectedOptions, function (index, option) {
                    var variationLabel = option;
                    var variationName = option; // Replace spaces with underscores
                    
                    var variationInput = '<div class="col-12 mb-3 variation-input">' +
                        '<label for="' + variationName.replace(/\s+/g, '_') + '" class="form-label"><?= lang('Pages.rename_following_variation') ?> </label>' +
                        '<input type="text" class="form-control text-center" id="' + variationName.replace(/\s+/g, '_') + '" name="' + variationName.replace(/\s+/g, '_') + '" placeholder="<?= lang('Pages.Enter_a_variation') ?>" value="' + variationName + '"required>' +
                        '<div class="invalid-feedback"><?= lang('Pages.variation_input_feedback') ?></div>' +
                        '</div>';
                    
                    modalBody.find('.row').append(variationInput);
                });
            }

            // Submit renamed variations
            $("#modify-variation-submit").click(function(){
                var responseWrapper = $("#modifyVariation-responseMsg"); // do not remove
                var form = $('#modify-variation-form');
                var submitButton = $(this);

                // Define a regular expression for not allowed characters
                // var disallowedCharsRegex = /[~@!#$%&*\-_+=|:.]/;
                var disallowedCharsRegex = /[^a-zA-Z0-9\s-]/;

                // enable button loading effect
                enableLoadingEffect(submitButton);			

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');			

                /*******************
                 * Start validations
                 ******************/
                // find all the inputs and validate the values based on the disallowedCharsRegex
                form.find('.variation-input input').each(function() {
                    var input = $(this);
                    var value = input.val().trim();

                    if (value === '') {
                        input.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else if (disallowedCharsRegex.test(value)) {
                        input.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else {
                        input.addClass('is-valid');
                    } 
                });			
                /*****************
                 * End validations
                 ****************/

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {

                    // Form data is valid, proceed with further processing
                    var data = new FormData(form[0]);

                    form.find('.variation-input input').each(function() {
                        var input = $(this);
                        var value = input.val().trim();

                        data.append(input, value);
                    });					

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('manage-products/product-variations/rename') ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,					
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success';
                                resetValidations(form);
                                updateVariationSelect();
                                
                            } else if (response.status == 2) {                
                                // Response success but with error
                                toastType = 'info';
                                resetValidations(form);
                                updateVariationSelect();
                            } else {	
                                toastType = 'danger';
                            }

                            showToast(toastType, response.msg);

                            $('#modifySelected').prop('disabled', true);
                            $('#removeSelected').prop('disabled', true);			
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

            function updateVariationSelect() {
                // Fetch updated list of product names from the server
                $.ajax({
                    url: '<?= base_url('manage-products/product-variations/fetch-variation-list') ?>',
                    method: 'GET',
                    success: function (response) {
                        
                        // Initialize an empty string to store the options HTML
                        var optionSelect = '';

                        // Iterate through the response array and generate options
                        response.forEach(function(variationName) {
                            // Append each variation name as an option to the optionSelect string
                            optionSelect += '<option value="' + variationName + '">' + variationName + '</option>';
                        });

                        // Update the productVariations select element with the new HTML
                        $('#productVariations').html(optionSelect);
                    },
                    error: function (error) {
                        console.error('<?= lang('Pages.Error_fetching_variations') ?>', error);
                    }
                });
            }			

            // Update modal content when select options change
            $('#productVariations').change(function () {
                updateModalContent();
            });

            // Trigger update when the modal is shown
            $('#modify-variation-modal-popup').on('show.bs.modal', function () {
                updateModalContent();
            });		

            /*************************************
            // Handle setup variations of products
            *************************************/
            $('#set-variations-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#set-variations-form');
                var submitButton = $(this);

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('product-manager/variations/save') ?>',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        let toastType = 'info';

                        if (response.status == 1) {
                            toastType = 'success';
                        } else if (response.status == 2) {                
                            // Response success but with error
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
        });           
    </script>	  
    
    <script type="text/javascript">
        function updateVariationGroup() {
            // Iterate over each variation group select element
            $('#productWrapper select').each(function () {
                var variationId = $(this).attr('id').replace('-group', ''); // Extract variation ID from select element ID
                var selectedOptions = $(this).val(); // Get selected options

                // Update hidden input value with selected options
                $('#' + variationId).val(selectedOptions.join(',')); // Join selected options with commas and set as hidden input value
            });
        }
        
        // Dynamically update the changelog button
        function updateRenameChangelogDeleteButton() {
            var selectedModifyProduct = $('#modify-productName').val();
            var changelogUrl = '<?= base_url('product-changelog/') ?>';
            var productGuideUrl = '<?= base_url('product-manager/gettings-started-guide?s=') ?>';

            // Update links and button based on selected products
            if (selectedModifyProduct !== '') { // product-manager/modify-product
                $('#rename-product-modal').prop('disabled', false);
                $('#go-to-changelog-btn').attr('href', changelogUrl + selectedModifyProduct);
                $('#go-to-product-guide-btn').attr('href', productGuideUrl + selectedModifyProduct);
                $('#delete-product-submit').prop('disabled', false);
                $('#modal-product-name').html(selectedModifyProduct);
                $('#rename-productName').val(selectedModifyProduct);
            } else {
                $('#rename-product-modal').prop('disabled', true);
                $('#go-to-changelog-btn').attr('href', 'javascript:void(0)');
                $('#go-to-product-guide-btn').attr('href', 'javascript:void(0)');
                $('#delete-product-submit').prop('disabled', true);
                $('#modal-product-name').html('');
            }

            if (selectedProduct !== '') {
                $('#go-to-changelog-btn2').attr('href', changelogUrl + selectedProduct);
                $('#go-to-product-guide-btn2').attr('href', productGuideUrl + selectedProduct);
            } else {
                $('#go-to-changelog-btn2').attr('href', 'javascript:void(0)');
                $('#go-to-product-guide-btn2').attr('href', 'javascript:void(0)');
            }
        }

        function updateRenameVariationButton() {
            var selectedVariation = $('#productVariations').val();

            // Update links and button based on selected products
            if (selectedVariation !== '') { // product-manager/modify-product
                $('#modifySelected').prop('disabled', false);
                $('#removeSelected').prop('disabled', false);
            } else {
                $('#modifySelected').prop('disabled', true);
                $('#removeSelected').prop('disabled', true);
            }
        }

        // Dynamically select products for each variations
        function showProductsearchVariation() {
            var productWrapper = $('#productWrapper');
            var selectedVariation = $('#variation_select').val();

            // Hide all product wrappers first
            productWrapper.find('div').hide();

            // Show the selected product wrapper
            $('#' + selectedVariation + '-wrapper').slideDown();
        }

        // Dynamically show the list of current files
        function fetchProductFiles() {
            $('#checkAll').prop('checked', false); // Uncheck the "checkAll" checkbox

            const tableContent = $('#product-file-list');
            const tableWrapper = $('#table-file-wrapper');
            const noSelectedProductNotification = $('#no-selected-file-responseMsg');

            if (selectedProduct === '') {
                tableWrapper.removeClass('show'); // Hide the table
                return;
            }

            $.ajax({
                url: '<?= base_url('product-files/show') ?>',
                method: 'POST',
                success: function (response) {
                    if (response.success) {
                        completeFileList = response.current_files;

                        if (selectedProduct in completeFileList) {
                            const productFiles = completeFileList[selectedProduct];
                            let filesHtml = '';

                            if (
                                (Array.isArray(productFiles) && productFiles.length === 0) ||
                                ($.isPlainObject(productFiles) && $.isEmptyObject(productFiles))
                            ) {
                                // No files for this product
                                tableWrapper.hide();
                                noSelectedProductNotification.html(`
                                    <div class="alert bg-warning fade show text-center" role="alert">
                                        <?= lang('Pages.no_files_in_the_product') ?>
                                    </div>
                                `).slideDown();
                                return;
                            }

                            // Build HTML for the product files
                            $.each(productFiles, function (key, value) {
                                filesHtml += `
                                    <tr>
                                        <td class="p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="${value}" id="${value}" name="${value}">
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <label for="${value}" class="form-label">
                                                ${value}
                                                <small class="text-muted">
                                                    [ <a href="<?= base_url('download/') ?>${selectedProduct}/${value}">
                                                        <?= lang('Pages.download') ?>
                                                    </a> ]
                                                </small>
                                            </label>
                                        </td>
                                    </tr>
                                `;
                            });

                            tableContent.html(filesHtml);
                            $('#checkAll').prop('checked', false);
                            tableWrapper.slideDown();
                            noSelectedProductNotification.hide();
                        } else {
                            // Product not found
                            tableWrapper.hide();
                            noSelectedProductNotification.html(`
                                <div class="alert bg-warning fade show text-center" role="alert">
                                    <?= lang('Pages.product_not_found_in_file_list') ?>
                                </div>
                            `).slideDown();
                        }
                    } else {
                        
                        noSelectedProductNotification.hide();
                        showToast('danger', response.msg);
                    }
                },
                error: function (xhr, status) {
                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                },
                complete: function () {
                    tableWrapper.hide();
                }
            });
        }

        // Dynamically show the list of variations and respective products therein
        function fetchProductVariations() {
            const productWrapper = $('#productWrapper');
            
            $.ajax({
                url: '<?= base_url('api/variation/all') ?>',
                method: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                },
                success: function (response) {
                    // Clear existing content
                    productWrapper.empty();
                    
                    // Check if response is valid and not an error
                    if (response && !response.result) {
                        // Loop through each variation and create the HTML
                        $.each(response, function(key, includedProducts) {
                            if (key) {
                                // Create a unique ID by replacing spaces with underscores
                                const variationId = key.replace(/ /g, '_');
                                
                                // Create the wrapper div
                                let html = `
                                    <div id="${variationId}-wrapper" style="display:none">
                                        <label for="${variationId}-group" class="form-label">${'<?= lang('Pages.list_product_with_this_variation') ?>'} "${key}"</label>
                                        <select class="form-select form-control" id="${variationId}-group" name="${variationId}-group" multiple="multiple" style="height: 150px;" onchange="updateVariationGroup()">`;
                                
                                // Parse the completeFileList global variable to get product names
                                try {
                                    // Parse the JSON string if it's a string
                                    const productData = typeof completeFileList === 'string' ? 
                                        JSON.parse(completeFileList) : completeFileList;
                                    
                                    // Get all product names (keys of the object)
                                    const productNames = Object.keys(productData);
                                    
                                    // Split the included products string by comma
                                    const selectedProducts = includedProducts.split(',');
                                    
                                    // Add each product as an option
                                    $.each(productNames, function(i, productName) {
                                        const isSelected = selectedProducts.includes(productName) ? 'selected' : '';
                                        html += `<option value="${productName}" ${isSelected}>${productName}</option>`;
                                    });
                                } catch (e) {
                                    console.error('Error parsing product list:', e);
                                    showToast('warning', '<?= lang('Pages.could_not_load_product_names') ?>');
                                }
                                
                                // Close the select and div
                                html += `
                                        </select>
                                    </div>
                                `;

                                $('#variation_select').val(''); // reset the drowdown select
                                
                                // Append to the wrapper
                                productWrapper.append(html);
                            }
                        });
                    } else {
                        // Handle empty response or error
                        productWrapper.html('<div class="alert alert-info"><?= lang('Pages.no_variations_available') ?></div>');
                        if (response && response.message) {
                            showToast('info', response.message);
                        }
                    }
                },
                error: function (xhr, status) {
                    productWrapper.html('<div class="alert alert-danger"><?= lang('Pages.ajax_no_response') ?></div>');
                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                }
            });
        }

        function listFilesAvailable() {
            // console.log(completeFileList);

            var tableContent = $('#product-file-list');
            var tableWrapper = $('#table-file-wrapper');
            var noSelectedProductNotification = $('#no-selected-file-responseMsg');
            
            tableWrapper.hide();
            noSelectedProductNotification.hide();

            if(completeFileList !== '') {
                // Parse the JSON string into an object
                var allProductFiles = JSON.parse(completeFileList);
                
                if(selectedProduct !== '' && selectedProduct in allProductFiles) {
                    // console.log('Loading files for: ' + selectedProduct);
                    var productFiles = allProductFiles[selectedProduct];
                    
                    // Check if the product has files
                    if(Array.isArray(productFiles) && productFiles.length === 0) {
                        // No files for this product (empty array)
                        tableWrapper.hide();
                        noSelectedProductNotification.hide();
                        noSelectedProductNotification.html('<div class="alert bg-warning fade show text-center" role="alert"><?= lang('Pages.no_files_in_the_product') ?></div>');
                        noSelectedProductNotification.slideDown();
                    } else if($.isEmptyObject(productFiles)) {
                        // No files for this product (empty object)
                        tableWrapper.hide();
                        // noSelectedProductNotification.hide();
                        noSelectedProductNotification.html('<div class="alert bg-warning fade show text-center" role="alert"><?= lang('Pages.no_files_in_the_product') ?></div>');
                        noSelectedProductNotification.slideDown();
                    } else {
                        // Build HTML for the list of product files
                        var filesHtml = '';
                        
                        // Handle both array and object formats
                        if(Array.isArray(productFiles)) {
                            // If productFiles is an array
                            $.each(productFiles, function(key, value) {
                                filesHtml += '<tr>';
                                filesHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + value + '" id="'+ value +'" name="'+ value +'"></div></td>';
                                filesHtml += '<td class="align-middle"><label for="'+ value +'" class="form-label">' + value + '<small class="text-muted"> [ <a href="<?= base_url('download/') ?>'+selectedProduct+'/'+value+'"><?= lang('Pages.download') ?></a> ]</small></label></td>';
                                filesHtml += '</tr>';
                            });
                        } else {
                            // If productFiles is an object with numeric keys
                            $.each(productFiles, function(key, value) {
                                filesHtml += '<tr>';
                                filesHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + value + '" id="'+ value +'" name="'+ value +'"></div></td>';
                                filesHtml += '<td class="align-middle"><label for="'+ value +'" class="form-label">' + value + '<small class="text-muted"> [ <a href="<?= base_url('download/') ?>'+selectedProduct+'/'+value+'"><?= lang('Pages.download') ?></a> ]</small></label></td>';
                                filesHtml += '</tr>';
                            });
                        }
                        
                        // Update the content of the product files div
                        tableContent.html(filesHtml);
                        
                        $('#checkAll').prop('checked', false);
                        
                        // Show the table wrapper
                        tableWrapper.slideDown();
                        
                        // Hide any notification
                        noSelectedProductNotification.hide();
                    }
                } else {
                    // No product selected or product not found in the list
                    tableWrapper.hide();
                    
                    if(selectedProduct === '') {
                        // No product selected
                        noSelectedProductNotification.hide();
                        noSelectedProductNotification.html('<div class="alert bg-info fade show text-center" role="alert"><?= lang('Pages.select_product_file_manager') ?></div>');
                        noSelectedProductNotification.slideDown();
                    } else {
                        // Product not found in the list
                        noSelectedProductNotification.hide();
                        noSelectedProductNotification.html('<div class="alert bg-warning fade show text-center" role="alert"><?= lang('Pages.product_not_found_in_file_list') ?></div>');
                        noSelectedProductNotification.slideDown();
                    }
                }
            } else {
                // If no initial file list, fetch product files via AJAX
                fetchProductFiles();
            }
        }
        
        // Check/uncheck all checkboxes when "checkAll" is clicked
        $('#checkAll').on('change', function () {
            var isChecked = $(this).prop('checked');
            $('tbody#product-file-list').find('input[type="checkbox"]').prop('checked', isChecked);
            updateDeleteButtonState();
        });

        // Check/uncheck "checkAll" based on the state of individual checkboxes
        $(document).on('change', 'tbody#product-file-list input[type="checkbox"]', function () {
            var allChecked = $('tbody#product-file-list input[type="checkbox"]:checked').length === $('tbody#product-file-list input[type="checkbox"]').length;
            $('#checkAll').prop('checked', allChecked);
            updateDeleteButtonState();
        });

        // Uncheck "checkAll" if any individual checkbox is unchecked
        $(document).on('change', 'tbody#product-file-list input[type="checkbox"]', function () {
            if (!$(this).prop('checked')) {
                $('#checkAll').prop('checked', false);
            }
        });	

        // Call the function initially to set the button state
        updateDeleteButtonState();

        function updateSidebarAndSelectOptions() {
            var sidebarHTML = '';
            var optionSelect = '<option value=""><?= lang('Pages.Select_Product') ?></option>';

            if(completeFileList !== '') {
                var allProductFiles = JSON.parse(completeFileList);

                // Update sidebar
                $.each(allProductFiles, function(productName, productFiles) {
                    sidebarHTML += '<li><a href="<?= base_url('product-changelog/') ?>' + productName + '">' + productName + '</a></li>';
                    optionSelect += '<option value="' + productName + '">' + productName + '</option>';
                });

                // Update the sidebar with the new HTML
                $('#changelog-product-list-sidebar').html(sidebarHTML);
                                
                // Update the dropdown elements
                $('#modify-productName').html(optionSelect);
                $('#upload-productName').html(optionSelect);
                updateVariationGroup();
                
                // Update menu buttons
                $('#rename-product-modal').prop('disabled', true);
                $('#go-to-changelog-btn').attr('href', 'javascript:void(0)');
                $('#go-to-product-guide-btn').attr('href', 'javascript:void(0)');
                $('#delete-product-submit').prop('disabled', true);
                $('#modal-product-name').html('');
                
                return;
            }

            // Fallback: Fetch product names via AJAX
            fetchProductFiles();
            updateSidebarAndSelectOptions();
        }
    </script>
<?= $this->endSection() //End section('scripts')?>