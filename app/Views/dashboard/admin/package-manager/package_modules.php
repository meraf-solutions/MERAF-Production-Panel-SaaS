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
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                        <i class="uil uil-plus me-1"></i> <?= lang('Pages.New_Category') ?>
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newModuleModal">
                        <i class="uil uil-plus me-1"></i> <?= lang('Pages.New_Module') ?>
                    </button>
                </div>
            </div>

            <?php if (empty($moduleCategories)): ?>
                <div class="card shadow rounded border-0">
                    <div class="card-body text-center p-4">
                        <h5><?= lang('Pages.No_Categories_Found') ?></h5>
                        <p><?= lang('Pages.Add_New_Category_Message') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($moduleCategories as $category): ?>
                    <div class="card shadow rounded border-0 mb-4">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= lang('Pages.' . $category['category_name']) ?></h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary edit-category" 
                                        data-id="<?= $category['id'] ?>" 
                                        data-name="<?= $category['category_name'] ?>" 
                                        data-description="<?= $category['description'] ?>" 
                                        data-sort-order="<?= $category['sort_order'] ?>" 
                                        data-status="<?= $category['status'] ?>">
                                    <i class="uil uil-edit"></i> <?= lang('Pages.Edit') ?>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4"><?= lang('Pages.' . $category['description']) ?></p>
                            
                            <?php 
                            // Filter modules for this category
                            $categoryModules = array_filter($packageModules, function($module) use ($category) {
                                return $module['module_category_id'] == $category['id'];
                            });
                            ?>
                            
                            <?php if (empty($categoryModules)): ?>
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="mb-0"><?= lang('Pages.No_Modules_In_Category') ?></p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-center bg-white mb-0">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom py-3" style="min-width: 200px;"><?= lang('Pages.Module_Name') ?></th>
                                                <th class="border-bottom py-3" style="min-width: 300px;"><?= lang('Pages.Description') ?></th>
                                                <th class="border-bottom py-3"><?= lang('Pages.Type') ?></th>
                                                <th class="border-bottom py-3"><?= lang('Pages.Status') ?></th>
                                                <th class="border-bottom py-3 text-end"><?= lang('Pages.Actions') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categoryModules as $module): ?>
                                                <?php 
                                                $measurementUnit = json_decode($module['measurement_unit'], true);
                                                ?>
                                                <tr>
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($measurementUnit['icon'])): ?>
                                                                <i class="uil uil-<?= $measurementUnit['icon'] ?> text-primary me-2"></i>
                                                            <?php endif; ?>
                                                            <span><?= lang('Pages.' . $module['module_name']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="py-3">
                                                        <p class="text-muted mb-0"><?= lang('Pages.' . $module['module_description']) ?></p>
                                                    </td>
                                                    <td class="py-3">
                                                        <?php if ($measurementUnit['type'] === 'checkbox'): ?>
                                                            <span class="badge bg-soft-primary"><?= lang('Pages.Toggle') ?></span>
                                                        <?php elseif ($measurementUnit['type'] === 'number'): ?>
                                                            <span class="badge bg-soft-success"><?= lang('Pages.Numeric') ?> (<?= $measurementUnit['unit'] ?>)</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-soft-info"><?= ucfirst($measurementUnit['type']) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="py-3">
                                                        <?php if ($module['is_enabled'] === 'yes'): ?>
                                                            <span class="badge bg-soft-success"><?= lang('Pages.Enabled') ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-soft-danger"><?= lang('Pages.Disabled') ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end py-3">
                                                        <button type="button" class="btn btn-sm btn-primary edit-module" 
                                                                data-id="<?= $module['id'] ?>" 
                                                                data-name="<?= $module['module_name'] ?>" 
                                                                data-description="<?= $module['module_description'] ?>" 
                                                                data-category="<?= $module['module_category_id'] ?>" 
                                                                data-enabled="<?= $module['is_enabled'] ?>" 
                                                                data-measurement="<?= htmlspecialchars($module['measurement_unit']) ?>">
                                                            <i class="uil uil-edit"></i> <?= lang('Pages.Edit') ?>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- New Category Modal -->
    <div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="newCategoryModalLabel"><?= lang('Pages.New_Category') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="newCategoryForm" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="category_name"><?= lang('Pages.Category_Name_lang_key') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-tag-alt position-absolute mt-2 ms-3"></i>
                                        <input name="category_name" id="category_name" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Category_Name') ?>" required>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Enter_Category_Name') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="category_description"><?= lang('Pages.Description_lang_key') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-comment-alt-lines position-absolute mt-2 ms-3"></i>
                                        <textarea name="category_description" id="category_description" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="sort_order"><?= lang('Pages.Sort_Order') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-sort-amount-down position-absolute mt-2 ms-3"></i>
                                        <input name="sort_order" id="sort_order" type="number" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Sort_Order') ?>" value="1" min="1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="status"><?= lang('Pages.Status') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-toggle-on position-absolute mt-2 ms-3"></i>
                                        <select name="status" id="status" class="form-select form-control ps-5">
                                            <option value="active"><?= lang('Pages.Active') ?></option>
                                            <option value="inactive"><?= lang('Pages.Inactive') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i class="uil uil-save"></i> <?= lang('Pages.Save_Category') ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="editCategoryModalLabel"><?= lang('Pages.Edit_Category') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_category_id" name="category_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_category_name"><?= lang('Pages.Category_Name_lang_key') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-tag-alt position-absolute mt-2 ms-3"></i>
                                        <input name="category_name" id="edit_category_name" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Category_Name') ?>" required>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Enter_Category_Name') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_category_description"><?= lang('Pages.Description_lang_key') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-comment-alt-lines position-absolute mt-2 ms-3"></i>
                                        <textarea name="category_description" id="edit_category_description" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_sort_order"><?= lang('Pages.Sort_Order') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-sort-amount-down position-absolute mt-2 ms-3"></i>
                                        <input name="sort_order" id="edit_sort_order" type="number" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Sort_Order') ?>" min="1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_status"><?= lang('Pages.Status') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-toggle-on position-absolute mt-2 ms-3"></i>
                                        <select name="status" id="edit_status" class="form-select form-control ps-5">
                                            <option value="active"><?= lang('Pages.Active') ?></option>
                                            <option value="inactive"><?= lang('Pages.Inactive') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i class="uil uil-save"></i> <?= lang('Pages.Update_Category') ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Module Modal -->
    <div class="modal fade" id="newModuleModal" tabindex="-1" aria-labelledby="newModuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="newModuleModalLabel"><?= lang('Pages.New_Module') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="newModuleForm" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="module_name"><?= lang('Pages.Module_Name_lang_key') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-apps position-absolute mt-2 ms-3"></i>
                                        <input name="module_name" id="module_name" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Module_Name') ?>" required>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Enter_Module_Name') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="module_category_id"><?= lang('Pages.Category') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-folder position-absolute mt-2 ms-3"></i>
                                        <select name="module_category_id" id="module_category_id" class="form-select form-control ps-5" required>
                                            <option value=""><?= lang('Pages.Select_Category') ?></option>
                                            <?php foreach ($moduleCategories as $category): ?>
                                                <option value="<?= $category['id'] ?>"><?= lang('Pages.' . $category['category_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Select_Category') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="module_description"><?= lang('Pages.Description_lang_key') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-comment-alt-lines position-absolute mt-2 ms-3"></i>
                                        <textarea name="module_description" id="module_description" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="measurement_type"><?= lang('Pages.Measurement_Type') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-ruler position-absolute mt-2 ms-3"></i>
                                        <select name="measurement_type" id="measurement_type" class="form-select form-control ps-5" required>
                                            <option value="checkbox"><?= lang('Pages.Checkbox') ?></option>
                                            <option value="number"><?= lang('Pages.Number') ?></option>
                                            <option value="text"><?= lang('Pages.Text') ?></option>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Select_Measurement_Type') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="is_enabled"><?= lang('Pages.Status') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-toggle-on position-absolute mt-2 ms-3"></i>
                                        <select name="is_enabled" id="is_enabled" class="form-select form-control ps-5">
                                            <option value="yes"><?= lang('Pages.Enabled') ?></option>
                                            <option value="no"><?= lang('Pages.Disabled') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Measurement Unit Configuration -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label"><?= lang('Pages.Measurement_Configuration') ?></label>
                                    <div class="p-3 border rounded">
                                        <!-- Checkbox Type Fields -->
                                        <div id="checkbox_fields">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="checkbox_label"><?= lang('Pages.Label') ?></label>
                                                        <input type="text" id="checkbox_label" name="checkbox_label" class="form-control" placeholder="<?= lang('Pages.Enter_Label') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="checkbox_unit"><?= lang('Pages.Unit') ?></label>
                                                        <input type="text" id="checkbox_unit" name="checkbox_unit" class="form-control" value="Enabled" placeholder="<?= lang('Pages.Enter_Unit') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="checkbox_description"><?= lang('Pages.Description') ?></label>
                                                        <textarea id="checkbox_description" name="checkbox_description" class="form-control" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Number Type Fields -->
                                        <div id="number_fields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_label"><?= lang('Pages.Label') ?></label>
                                                        <input type="text" id="number_label" name="number_label" class="form-control" placeholder="<?= lang('Pages.Enter_Label') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_unit"><?= lang('Pages.Unit') ?></label>
                                                        <input type="text" id="number_unit" name="number_unit" class="form-control" placeholder="<?= lang('Pages.Enter_Unit') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_min"><?= lang('Pages.Min_Value') ?></label>
                                                        <input type="number" id="number_min" name="number_min" class="form-control" value="1" placeholder="<?= lang('Pages.Min') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_max"><?= lang('Pages.Max_Value') ?></label>
                                                        <input type="number" id="number_max" name="number_max" class="form-control" value="1000" placeholder="<?= lang('Pages.Max') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_step"><?= lang('Pages.Step') ?></label>
                                                        <input type="number" id="number_step" name="number_step" class="form-control" value="1" placeholder="<?= lang('Pages.Step') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_default"><?= lang('Pages.Default') ?></label>
                                                        <input type="number" id="number_default" name="number_default" class="form-control" value="10" placeholder="<?= lang('Pages.Default') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_description"><?= lang('Pages.Description') ?></label>
                                                        <textarea id="number_description" name="number_description" class="form-control" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="number_icon"><?= lang('Pages.Icon') ?> (<a href="https://feathericons.com/" target="_blank">Feather Icon</a>)</label>
                                                        <input type="text" id="number_icon" name="number_icon" class="form-control" placeholder="<?= lang('Pages.Enter_Icon_Name') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Text Type Fields -->
                                        <div id="text_fields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="text_label"><?= lang('Pages.Label') ?></label>
                                                        <input type="text" id="text_label" name="text_label" class="form-control" placeholder="<?= lang('Pages.Enter_Label') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="text_unit"><?= lang('Pages.Unit') ?></label>
                                                        <input type="text" id="text_unit" name="text_unit" class="form-control" placeholder="<?= lang('Pages.Enter_Unit') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="text_description"><?= lang('Pages.Description') ?></label>
                                                        <textarea id="text_description" name="text_description" class="form-control" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="text_default"><?= lang('Pages.Default') ?></label>
                                                        <input type="text" id="text_default" name="text_default" class="form-control" placeholder="<?= lang('Pages.Default_Value') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="text_icon"><?= lang('Pages.Icon') ?> (<a href="https://feathericons.com/" target="_blank">Feather Icon</a>)</label>
                                                        <input type="text" id="text_icon" name="text_icon" class="form-control" placeholder="<?= lang('Pages.Enter_Icon_Name') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i class="uil uil-save"></i> <?= lang('Pages.Save_Module') ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Module Modal -->
    <div class="modal fade" id="editModuleModal" tabindex="-1" aria-labelledby="editModuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="editModuleModalLabel"><?= lang('Pages.Edit_Module') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="editModuleForm" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_module_id" name="module_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_module_name"><?= lang('Pages.Module_Name_lang_key') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-apps position-absolute mt-2 ms-3"></i>
                                        <input name="module_name" id="edit_module_name" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Module_Name') ?>" required>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Enter_Module_Name') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_module_category_id"><?= lang('Pages.Category') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-folder position-absolute mt-2 ms-3"></i>
                                        <select name="module_category_id" id="edit_module_category_id" class="form-select form-control ps-5" required>
                                            <option value=""><?= lang('Pages.Select_Category') ?></option>
                                            <?php foreach ($moduleCategories as $category): ?>
                                                <option value="<?= $category['id'] ?>"><?= lang('Pages.' . $category['category_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Select_Category') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_module_description"><?= lang('Pages.Description_lang_key') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-comment-alt-lines position-absolute mt-2 ms-3"></i>
                                        <textarea name="module_description" id="edit_module_description" class="form-control ps-5" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_measurement_type"><?= lang('Pages.Measurement_Type') ?> <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-ruler position-absolute mt-2 ms-3"></i>
                                        <select name="measurement_type" id="edit_measurement_type" class="form-select form-control ps-5" required>
                                            <option value="checkbox"><?= lang('Pages.Checkbox') ?></option>
                                            <option value="number"><?= lang('Pages.Number') ?></option>
                                            <option value="text"><?= lang('Pages.Text') ?></option>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?= lang('Pages.Please_Select_Measurement_Type') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_is_enabled"><?= lang('Pages.Status') ?></label>
                                    <div class="form-icon position-relative">
                                        <i class="uil uil-toggle-on position-absolute mt-2 ms-3"></i>
                                        <select name="is_enabled" id="edit_is_enabled" class="form-select form-control ps-5">
                                            <option value="yes"><?= lang('Pages.Enabled') ?></option>
                                            <option value="no"><?= lang('Pages.Disabled') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Measurement Unit Configuration -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label"><?= lang('Pages.Measurement_Configuration') ?></label>
                                    <div class="p-3 border rounded">
                                        <!-- Checkbox Type Fields -->
                                        <div id="edit_checkbox_fields">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_checkbox_label"><?= lang('Pages.Label') ?></label>
                                                        <input type="text" id="edit_checkbox_label" name="edit_checkbox_label" class="form-control" placeholder="<?= lang('Pages.Enter_Label') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_checkbox_unit"><?= lang('Pages.Unit') ?></label>
                                                        <input type="text" id="edit_checkbox_unit" name="edit_checkbox_unit" class="form-control" placeholder="<?= lang('Pages.Enter_Unit') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_checkbox_description"><?= lang('Pages.Description_module') ?></label>
                                                        <textarea id="edit_checkbox_description" name="edit_checkbox_description" class="form-control" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Number Type Fields -->
                                        <div id="edit_number_fields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_label"><?= lang('Pages.Label') ?></label>
                                                        <input type="text" id="edit_number_label" name="edit_number_label" class="form-control" placeholder="<?= lang('Pages.Enter_Label') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_unit"><?= lang('Pages.Unit') ?></label>
                                                        <input type="text" id="edit_number_unit" name="edit_number_unit" class="form-control" placeholder="<?= lang('Pages.Enter_Unit') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_min"><?= lang('Pages.Min_Value') ?></label>
                                                        <input type="number" id="edit_number_min" name="edit_number_min" class="form-control" placeholder="<?= lang('Pages.Min') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_max"><?= lang('Pages.Max_Value') ?></label>
                                                        <input type="number" id="edit_number_max" name="edit_number_max" class="form-control" placeholder="<?= lang('Pages.Max') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_step"><?= lang('Pages.Step') ?></label>
                                                        <input type="number" id="edit_number_step" name="edit_number_step" class="form-control" placeholder="<?= lang('Pages.Step') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_default"><?= lang('Pages.Default') ?></label>
                                                        <input type="number" id="edit_number_default" name="edit_number_default" class="form-control" placeholder="<?= lang('Pages.Default') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_description"><?= lang('Pages.Description') ?></label>
                                                        <textarea id="edit_number_description" name="edit_number_description" class="form-control" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_number_icon"><?= lang('Pages.Icon') ?> (<a href="https://feathericons.com/" target="_blank">Feather Icon</a>)</label>
                                                        <input type="text" id="edit_number_icon" name="edit_number_icon" class="form-control" placeholder="<?= lang('Pages.Enter_Icon_Name') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Text Type Fields -->
                                        <div id="edit_text_fields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_text_label"><?= lang('Pages.Label') ?></label>
                                                        <input type="text" id="edit_text_label" name="edit_text_label" class="form-control" placeholder="<?= lang('Pages.Enter_Label') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_text_unit"><?= lang('Pages.Unit') ?></label>
                                                        <input type="text" id="edit_text_unit" name="edit_text_unit" class="form-control" placeholder="<?= lang('Pages.Enter_Unit') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_text_description"><?= lang('Pages.Description') ?></label>
                                                        <textarea id="edit_text_description" name="edit_text_description" class="form-control" placeholder="<?= lang('Pages.Enter_Description') ?>" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_text_default"><?= lang('Pages.Default') ?></label>
                                                        <input type="text" id="edit_text_default" name="edit_text_default" class="form-control" placeholder="<?= lang('Pages.Default_Value') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="edit_text_icon"><?= lang('Pages.Icon') ?> (<a href="https://feathericons.com/" target="_blank">Feather Icon</a>)</label>
                                                        <input type="text" id="edit_text_icon" name="edit_text_icon" class="form-control" placeholder="<?= lang('Pages.Enter_Icon_Name') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i class="uil uil-save"></i> <?= lang('Pages.Update_Module') ?></button>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-danger"><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Module') ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        // Show/hide measurement type fields based on selection
        $('#measurement_type').change(function() {
            const type = $(this).val();
            $('#checkbox_fields, #number_fields, #text_fields').hide();
            $(`#${type}_fields`).show();
        });
        
        $('#edit_measurement_type').change(function() {
            const type = $(this).val();
            $('#edit_checkbox_fields, #edit_number_fields, #edit_text_fields').hide();
            $(`#edit_${type}_fields`).show();
        });
        
        // Handle Edit Category button click
        $('.edit-category').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const sortOrder = $(this).data('sort-order');
            const status = $(this).data('status');
            
            $('#edit_category_id').val(id);
            $('#edit_category_name').val(name);
            $('#edit_category_description').val(description);
            $('#edit_sort_order').val(sortOrder);
            $('#edit_status').val(status);
            
            $('#editCategoryModal').modal('show');
        });
        
        // Handle Edit Module button click
        $('.edit-module').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const category = $(this).data('category');
            const enabled = $(this).data('enabled');
            const measurement = $(this).data('measurement');
            
            $('#edit_module_id').val(id);
            $('#edit_module_name').val(name);
            $('#edit_module_description').val(description);
            $('#edit_module_category_id').val(category);
            $('#edit_is_enabled').val(enabled);
            
            // Set measurement type and show appropriate fields
            const type = measurement.type;
            $('#edit_measurement_type').val(type);
            $('#edit_checkbox_fields, #edit_number_fields, #edit_text_fields').hide();
            $(`#edit_${type}_fields`).show();
            
            // Fill in measurement unit fields based on type
            if (type === 'checkbox') {
                $('#edit_checkbox_label').val(measurement.label);
                $('#edit_checkbox_unit').val(measurement.unit);
                $('#edit_checkbox_description').val(measurement.description);
            } else if (type === 'number') {
                $('#edit_number_label').val(measurement.label);
                $('#edit_number_unit').val(measurement.unit);
                $('#edit_number_min').val(measurement.min);
                $('#edit_number_max').val(measurement.max);
                $('#edit_number_step').val(measurement.step);
                $('#edit_number_default').val(measurement.default);
                $('#edit_number_description').val(measurement.description);
                $('#edit_number_icon').val(measurement.icon);
            } else if (type === 'text') {
                $('#edit_text_label').val(measurement.label);
                $('#edit_text_unit').val(measurement.unit);
                $('#edit_text_description').val(measurement.description);
                $('#edit_text_default').val(measurement.default);
                $('#edit_text_icon').val(measurement.icon);
            }
            
            $('#editModuleModal').modal('show');
        });
        
        // Form validation and submission for New Category
        $('#newCategoryForm').submit(function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            const formData = {
                category_name: $('#category_name').val(),
                description: $('#category_description').val(),
                sort_order: $('#sort_order').val(),
                status: $('#status').val()
            };
            
            $.ajax({
                url: '<?= base_url('admin-options/package-manager/save-category') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastType = 'success';
                        delayedRedirect('<?= current_url() ?>');
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
        });
        
        // Form validation and submission for Edit Category
        $('#editCategoryForm').submit(function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            const formData = {
                category_id: $('#edit_category_id').val(),
                category_name: $('#edit_category_name').val(),
                description: $('#edit_category_description').val(),
                sort_order: $('#edit_sort_order').val(),
                status: $('#edit_status').val()
            };
            
            $.ajax({
                url: '<?= base_url('admin-options/package-manager/update-category') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastType = 'success';
                        delayedRedirect('<?= current_url() ?>');
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
        });
        
        // Form validation and submission for New Module
        $('#newModuleForm').submit(function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            const type = $('#measurement_type').val();
            let measurementUnit = {};
            
            if (type === 'checkbox') {
                measurementUnit = {
                    type: 'checkbox',
                    label: $('#checkbox_label').val(),
                    unit: $('#checkbox_unit').val(),
                    description: $('#checkbox_description').val(),
                    icon: ''
                };
            } else if (type === 'number') {
                measurementUnit = {
                    type: 'number',
                    label: $('#number_label').val(),
                    unit: $('#number_unit').val(),
                    min: parseInt($('#number_min').val()),
                    max: parseInt($('#number_max').val()),
                    step: parseInt($('#number_step').val()),
                    default: parseInt($('#number_default').val()),
                    description: $('#number_description').val(),
                    icon: $('#number_icon').val()
                };
            } else if (type === 'text') {
                measurementUnit = {
                    type: 'text',
                    label: $('#text_label').val(),
                    unit: $('#text_unit').val(),
                    default: $('#text_default').val(),
                    description: $('#text_description').val(),
                    icon: $('#text_icon').val()
                };
            }
            
            const formData = {
                module_name: $('#module_name').val(),
                module_category_id: $('#module_category_id').val(),
                module_description: $('#module_description').val(),
                is_enabled: $('#is_enabled').val(),
                measurement_unit: JSON.stringify(measurementUnit)
            };
            
            $.ajax({
                url: '<?= base_url('admin-options/package-manager/save-module') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastType = 'success';
                        delayedRedirect('<?= current_url() ?>');
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
        });
        
        // Form validation and submission for Edit Module
        $('#editModuleForm').submit(function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            const type = $('#edit_measurement_type').val();
            let measurementUnit = {};
            
            if (type === 'checkbox') {
                measurementUnit = {
                    type: 'checkbox',
                    label: $('#edit_checkbox_label').val(),
                    unit: $('#edit_checkbox_unit').val(),
                    description: $('#edit_checkbox_description').val(),
                    icon: ''
                };
            } else if (type === 'number') {
                measurementUnit = {
                    type: 'number',
                    label: $('#edit_number_label').val(),
                    unit: $('#edit_number_unit').val(),
                    min: parseInt($('#edit_number_min').val()),
                    max: parseInt($('#edit_number_max').val()),
                    step: parseInt($('#edit_number_step').val()),
                    default: parseInt($('#edit_number_default').val()),
                    description: $('#edit_number_description').val(),
                    icon: $('#edit_number_icon').val()
                };
            } else if (type === 'text') {
                measurementUnit = {
                    type: 'text',
                    label: $('#edit_text_label').val(),
                    unit: $('#edit_text_unit').val(),
                    default: $('#edit_text_default').val(),
                    description: $('#edit_text_description').val(),
                    icon: $('#edit_text_icon').val()
                };
            }
            
            const formData = {
                module_id: $('#edit_module_id').val(),
                module_name: $('#edit_module_name').val(),
                module_category_id: $('#edit_module_category_id').val(),
                module_description: $('#edit_module_description').val(),
                is_enabled: $('#edit_is_enabled').val(),
                measurement_unit: JSON.stringify(measurementUnit)
            };
            
            $.ajax({
                url: '<?= base_url('admin-options/package-manager/update-module') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastType = 'success';
                        delayedRedirect('<?= current_url() ?>');
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
        });

        // Handle Delete Module button click
        $('#editModuleModal .btn-danger').click(function() {
            const moduleId = $('#edit_module_id').val();
            const moduleName = $('#edit_module_name').val();
            
            if (confirm('<?= lang('Pages.confirmation_to_delete_module') ?>')) {
                $.ajax({
                    url: '<?= base_url('admin-options/package-manager/delete-module') ?>/' + moduleId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            toastType = 'success';
                            delayedRedirect('<?= current_url() ?>');
                        } else {
                            toastType = 'danger';
                        }
                        
                        showToast(toastType, response.msg);
                    },
                    error: function (xhr, status, error) {
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection() //End section('scripts')?>
