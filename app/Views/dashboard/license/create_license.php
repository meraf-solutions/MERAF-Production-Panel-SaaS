<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <?php if($subsection !== '') { ?>
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection) ) ?></h5>
        <?php } else { ?>
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($section)  ) ?></h5>
        <?php } ?>

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
    <div class="row justify-content-center">
        <div class="col-12 mt-4">
            <?php $newLicenseKey = generateLicenseKey($userData->id); ?>
            
            <!-- Create new license -->
            <div class="row g-2 mb-3">
                <div class="col-xl-3 col-lg-3 col-md-4 col-12 mb-3  text-center">
                    <div class="card rounded border-0 shadow p-1">

                        <ul class="nav nav-pills nav-link-soft nav-justified flex-column bg-white-color mt-0 mb-0" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded active" id="LicKeyOptions-tab" data-bs-toggle="pill" href="#LicKeyOptions" role="tab" aria-controls="LicKeyOptions" aria-selected="true">
                                    <div class="text-start px-3">
                                        <span class="badge bg-danger float-end hide" id="LicKeyOptions-error">!</span>
                                        <span class="mb-0"><?= lang('Pages.License_Key_and_Status') ?></span>
                                    </div>
                                </a><!--end nav link-->
                            </li><!--end nav item-->
                            
                            <li class="nav-item mt-2" role="presentation">
                                <a class="nav-link rounded" id="UserInfoOptions-tab" data-bs-toggle="pill" href="#UserInfoOptions" role="tab" aria-controls="UserInfoOptions" aria-selected="false" tabindex="-1">
                                    <div class="text-start px-3">
                                        <span class="badge bg-danger float-end hide" id="UserInfoOptions-error">!</span>
                                        <span class="mb-0"><?= lang('Pages.User_Info') ?></span>
                                    </div>
                                </a><!--end nav link-->
                            </li><!--end nav item-->

                            <li class="nav-item mt-2" role="presentation">
                                <a class="nav-link rounded" id="DomainsDevicesOptions-tab" data-bs-toggle="pill" href="#DomainsDevicesOptions" role="tab" aria-controls="DomainsDevicesOptions" aria-selected="false" tabindex="-1">
                                    <div class="text-start px-3">
                                        <span class="badge bg-danger float-end hide" id="DomainsDevicesOptions-error">!</span>
                                        <span class="mb-0"><?= lang('Pages.Domains_and_Devices') ?></span>
                                    </div>
                                </a><!--end nav link-->
                            </li><!--end nav item-->
                            
                            <li class="nav-item mt-2" role="presentation">
                                <a class="nav-link rounded" id="SubsRenewalOptions-tab" data-bs-toggle="pill" href="#SubsRenewalOptions" role="tab" aria-controls="SubsRenewalOptions" aria-selected="false" tabindex="-1">
                                    <div class="text-start px-3">
                                        <span class="badge bg-danger float-end hide" id="SubsRenewalOptions-error">!</span>
                                        <span class="mb-0"><?= lang('Pages.Subscription_and_Renewal') ?></span>
                                    </div>
                                </a><!--end nav link-->
                            </li><!--end nav item-->
                            
                            <li class="nav-item mt-2" role="presentation">
                                <a class="nav-link rounded" id="ProductOptions-tab" data-bs-toggle="pill" href="#ProductOptions" role="tab" aria-controls="ProductOptions" aria-selected="false" tabindex="-1">
                                    <div class="text-start px-3">
                                        <span class="badge bg-danger float-end hide" id="ProductOptions-error">!</span>
                                        <span class="mb-0"><?= lang('Pages.Product') ?></span>
                                    </div>
                                </a><!--end nav link-->
                            </li><!--end nav item-->
                            
                        </ul><!--end nav pills-->

                    </div>

                    <button class="mx-auto btn btn-success mt-3" id="new-license-submit"><i class="uil uil-plus"></i> <?= lang('Pages.Create_license') ?></button>

                </div><!--end col-->
                
                <div class="col-xl-9 col-lg-9 col-md-8 col-12">
                    <form class="" novalidate action="javascript:void(0)" id="new-license-form">
                        <div class="tab-content rounded-0 shadow-0" id="pills-tabContent">
                            <!-- License Key and Status -->
                            <div class="tab-pane fade show active" id="LicKeyOptions" role="tabpanel" aria-labelledby="LicKeyOptions-tab">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <h5 class="mb-0"><?= lang('Pages.License_Key_and_Status') ?> :</h5>
                                        </div>
                                    </div><!--end col-->                                                   

                                    <div class="col-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">

                                            <label for="license_key" class="form-label">
                                                <?= lang('Pages.Generated_New_License_Key') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_license_key') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" class="form-control bg-light newLicenseKey" id="license_key" name="license_key" placeholder="<?= lang('Pages.Generated_New_License_Key') ?>" value="<?= $newLicenseKey ?>" readonly required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">

                                            <label for="license_status" class="form-label">
                                                <?= lang('Pages.License_Status') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_license_status') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select class="form-select form-control" id="license_status" name="license_status" required>
                                                <option value=""><?= lang('Pages.Select_Option') ?></option>
                                                <option value="active" <?= $myConfig['default_license_status'] === 'active' ? 'selected' : ''?>><?= lang('Pages.Active') ?></option>
                                                <option value="pending" <?= $myConfig['default_license_status'] === 'pending' ? 'selected' : ''?>><?= lang('Pages.Pending') ?></option>                                                                                
                                                <option value="blocked" <?= $myConfig['default_license_status'] === 'blocked' ? 'selected' : ''?>><?= lang('Pages.Blocked') ?></option>
                                                <option value="expired" <?= $myConfig['default_license_status'] === 'expired' ? 'selected' : ''?>><?= lang('Pages.Expired') ?></option>
                                            </select>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.please_select_license_status') ?>
                                            </div>                                                                        
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">

                                            <label for="license_type" class="form-label">
                                                <?= lang('Pages.License_Type') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_license_type') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select class="form-select form-control" id="license_type" name="license_type" required>
                                                <option value=""><?= lang('Pages.Select_Option') ?></option>
                                                <option value="lifetime"><?= lang('Pages.Lifetime') ?></option>
                                                <option value="subscription"><?= lang('Pages.Subscription') ?></option>
                                                <option value="trial"><?= lang('Pages.Trial') ?></option>
                                            </select>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.please_select_license_type') ?>
                                            </div>                                                                        
                                        </div>
                                    </div>                                                                

                                </div><!--end row-->
                            </div><!--end teb pane-->
                            
                            <!-- User Info -->
                            <div class="tab-pane fade rounded" id="UserInfoOptions" role="tabpanel" aria-labelledby="UserInfoOptions-tab">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <h5 class="mb-0"><?= lang('Pages.User_Info') ?> :</h5>
                                        </div>
                                    </div><!--end col-->   

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="first_name" class="form-label">
                                                <?= lang('Pages.First_Name') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_first_name') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="<?= lang('Pages.First_Name') ?>" value="" required>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.first_name_required_feedback') ?>
                                            </div>                                                                       
                                        </div>
                                    </div> 
                                    
                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="last_name" class="form-label">
                                                <?= lang('Pages.Last_Name') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_last_name') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="<?= lang('Pages.Last_Name') ?>" value="" required>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.last_name_required_feedback') ?>
                                            </div>                                                                       
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="email" class="form-label">
                                                <?= lang('Pages.Email') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_email') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="email" class="form-control" id="email" name="email" placeholder="client@example.com" required>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.email_required_feedback') ?>
                                            </div>                                                                       
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="subscr_id" class="form-label">
                                                <?= lang('Pages.Subscriber_ID') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_subscr_id') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                            </label>        

                                            <input type="text" class="form-control" id="subscr_id" name="subscr_id" placeholder="<?= lang('Pages.Unique_ID') ?>">                                                                     
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="company_name" class="form-label">
                                                <?= lang('Pages.Company_Name') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_company_name') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                            </label>

                                            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="<?= lang('Pages.Company_Name') ?>">                                                                                                                                               
                                        </div>
                                    </div>

                                    
                                </div><!--end row-->
                            </div><!--end teb pane-->

                            <!-- Domains & Devices Info -->
                            <div class="tab-pane fade rounded" id="DomainsDevicesOptions" role="tabpanel" aria-labelledby="DomainsDevicesOptions-tab">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <h5 class="mb-0"><?= lang('Pages.Domains_and_Devices') ?> :</h5>
                                        </div>
                                    </div><!--end col-->   

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="max_allowed_domains" class="form-label">
                                                <?= lang('Pages.Allowed_Website') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_max_allowed_domains') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number" class="form-control" id="max_allowed_domains" name="max_allowed_domains" placeholder="<?= lang('Pages.Enter_a_whole_number') ?>" value="<?= $myConfig['defaultAllowedDomains'] ?>" required>
                                            
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.set_allowed_number_website_feedback') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="max_allowed_devices" class="form-label">
                                                <?= lang('Pages.Allowed_Device') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_max_allowed_devices') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number" class="form-control" id="max_allowed_devices" name="max_allowed_devices" placeholder="<?= lang('Pages.Enter_a_whole_number') ?>" value="<?= $myConfig['defaultAllowedDevices'] ?>" required>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.set_allowed_number_device_feedback') ?>
                                            </div>
                                        </div>
                                    </div>

                                </div><!--end row-->
                            </div><!--end teb pane-->                                                

                            <!-- Subscription and Renewal -->
                            <div class="tab-pane fade rounded" id="SubsRenewalOptions" role="tabpanel" aria-labelledby="SubsRenewalOptions-tab">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <h5 class="mb-0"><?= lang('Pages.Subscription_and_Renewal') ?> :</h5>
                                        </div>
                                    </div><!--end col--> 

                                    <div class="col-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="manual_reset_count" class="form-label"><?= lang('Pages.Manual_Reset_Count') ?> </label>

                                            <small class="text-muted mb-3"><?= lang('Pages.Manual_Reset_Count_desc') ?></small>

                                            <input type="number" class="form-control" id="manual_reset_count" name="manual_reset_count" placeholder="<?= lang('Pages.Manual_Reset_Count') ?>">                                                                       
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="billing_length" class="form-label">
                                                <?= lang('Pages.Billing_Length') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_billing_length') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-info">*</span>
                                            </label>

                                            <input type="number" class="form-control" id="billing_length" name="billing_length" placeholder="<?= lang('Pages.Billing_Length') ?>">                                                                     

                                            <small class="text-info">
                                                <?= lang('Pages.required_subscription_trial') ?> <?= lang('Pages.Billing_Length_feedback') ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                        <label for="billing_interval" class="form-label">
                                                <?= lang('Pages.Billing_Interval') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_billing_interval') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-info">*</span>
                                            </label>

                                            <select class="form-select form-control" id="billing_interval" name="billing_interval">
                                                <option value=""><?= lang('Pages.Select_Option') ?></option>
                                                <option value="days"><?= lang('Pages.Days') ?></option>
                                                <option value="months"><?= lang('Pages.Months') ?></option>
                                                <option value="years"><?= lang('Pages.Years') ?></option>
                                                <option value="onetime"><?= lang('Pages.Onetime') ?></option>
                                            </select>

                                            <small class="text-info">
                                                <?= lang('Pages.required_subscription_trial') ?> <?= lang('Pages.Billing_Interval_feedback') ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="date_expiry" class="form-label">
                                                <?= lang('Pages.Expiration_Date') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_date_expiry') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-info">*</span>
                                            </label>

                                            <input type="text" class="form-control" id="date_expiry" name="date_expiry" placeholder="<?= lang('Pages.format') ?> 2024-01-30 22:15:00" value="" required data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.format') ?> 2024-01-30 22:15:00">
                                            
                                            <small class="text-info">
                                                <?= lang('Pages.required_subscription_trial') ?> <?= lang('Pages.valid_date_required_feedback') ?>
                                            </small>
                                        </div>
                                    </div>
                                </div><!--end row-->                                                      
                            </div><!--end teb pane-->                                                     

                            <!-- Product -->
                            <div class="tab-pane fade rounded " id="ProductOptions" role="tabpanel" aria-labelledby="ProductOptions-tab">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <h5 class="mb-0"><?= lang('Pages.Product') ?> :</h5>
                                        </div>
                                    </div><!--end col--> 

                                    <div class="col-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="product_ref" class="form-label">
                                                <?= lang('Pages.Product') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_product_ref') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select class="form-select form-control" id="product_ref" name="product_ref" required>
                                                <option value=""><?= lang('Pages.Select_Product') ?></option>
                                                <?php
                                                $productListWithVariation = productListWithVariation();
                                                foreach ($productListWithVariation as $productName) {
                                                    echo '<option value="' . $productName . '">' . $productName . '</option>';
                                                }
                                                ?>
                                            </select>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.please_select_product_feedback') ?>
                                            </div>                                                                     
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="txn_id" class="form-label">
                                                <?= lang('Pages.Reference') ?> / <?= lang('Pages.Transaction_ID') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_txn_id') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" class="form-control" id="txn_id" name="txn_id" placeholder="<?= lang('Pages.Reference') ?> / <?= lang('Pages.Transaction_ID') ?>" value="" required>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.transaction_id_required_feedback') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="purchase_id_" class="form-label">
                                                <?= lang('Pages.Purchase_ID') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_purchase_id_') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" class="form-control" id="purchase_id_" name="purchase_id_" placeholder="<?= lang('Pages.Purchase_ID') ?>" value="" required>

                                            <div class="invalid-feedback">
                                                <?= lang('Pages.purchase_id_required_feedback') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="until" class="form-label">
                                                <?= lang('Pages.Supported_Until') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_until') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                            </label>

                                            <input type="text" class="form-control" id="until" name="until" placeholder="<?= lang('Pages.Supported_Until') ?>" value="">
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="current_ver" class="form-label">
                                                <?= lang('Pages.Current_Version') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_current_ver') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                            </label>

                                            <input type="text" class="form-control" id="current_ver" name="current_ver" placeholder="<?= lang('Pages.Current_Version') ?>" value="">
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border-0 rounded shadow p-4">
                                            <label for="current_ver" class="form-label">
                                                <?= lang('Pages.Item_Reference') ?>
                                                <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.Item_Reference_desc') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                                </a>
                                            </label>

                                            <input type="text" class="form-control" id="item_reference" name="item_reference" placeholder="<?= lang('Pages.Item_Reference') ?>" value="">
                                        </div>
                                    </div>

                                </div>
                            </div><!--end teb pane-->

                        </div>
                    </form>
                </div><!--end col-->
            </div>
        </div><!--end col-->                           
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Flatpickr
            flatpickr("#date_expiry", {
                enableTime: true,
                dateFormat: "Y-m-d H:i:00",
                time_24hr: true,
                minDate: "today",
                defaultHour: <?= date('H') ?>,
                defaultMinute: <?= date('i') ?>,
                defaultSeconds: 0,
                disableMobile: true,
                onReady: function(selectedDates, dateStr, instance) {
                    instance.set('defaultSeconds', 0);
                }
            });

            /*****************************************
            // Handle the create license requests
            *****************************************/
            $('#new-license-submit').on('click', function (e) {
                e.preventDefault();
                
                var form = $('#new-license-form');
                var emailInput = $('input[name="email"]');
                var selectInput = $('select');
                var submitButton = $(this);  
                var allowedDomain = $('#max_allowed_domains');
                var allowedDevice = $('#max_allowed_devices');
                var licenseType = $('#license_type');
                var expirationDateInput = $('#date_expiry');
                var newLicenseKeyInput = $('.newLicenseKey');
                var subscrIDInput = $('#subscr_id');
                var manualResetCountInput = $('#manual_reset_count');
                var itemReferenceInput = $('#item_reference');
                var billingLengthInput = $('#billing_length');
                var billingIntervalInput = $('#billing_interval');
                var companyNameInput = $('#company_name');
                var untilInput = $('#until');
                var current_verInput = $('#current_ver');
                var txn_idInput = $('#txn_id');
                var firstNameInput = $('#first_name');
                var lastNameInput = $('#last_name');

                // Regular expression for email validation
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                // Validate expiration date format
                var expirationDateRegex = /^\d{4}-\d{2}-\d{2}(?:\s\d{2}:\d{2}:\d{2})?$/;

                // Validate expiration date max values
                var maxMonth = 12;
                var maxDay = 31;

                // Define a regular expression for not allowed characters
                var disallowedCharsRegex_general = /[~!#$%&*+=|:.]/;
                var disallowedCharsRegex_forDate = /[~!#$%&*\_+=|:.]/;
                var disallowedCharsRegex_forEmail   = /[~!#$%&*+=|:()\[\]]/;
                var disallowedCharsRegex_forDomain  = /[^a-zA-Z0-9.-]/;
                var disallowedCharsRegex_name = /[!@#$%^&*()+=[\]{};:'",<>/?`|0-9]/;
                var allowedCharsRegex_name = /^[A-Za-z\u00C0-\u017F\s.'-]+$/u;
                var disallowedCharsRegex_number  = /[^0-9]/;

                // Declare tab panes with errors
                var tabPaneIds = [];

                // enable button loading effect
                enableLoadingEffect(submitButton);			

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');

                // Hide all error badges in tab panes
                $('.badge.bg-danger.float-end').hide();

                /*******************
                 * Start validations
                 ******************/

                /**
                 * Iterate over other input fields and validate
                 *  */ 
                form.find('input').not(itemReferenceInput).not(firstNameInput).not(lastNameInput).not(untilInput).not(current_verInput).not(emailInput).not(expirationDateInput).not(subscrIDInput).not(billingLengthInput).not(billingIntervalInput).not(manualResetCountInput).not(companyNameInput).not(txn_idInput).each(function () {
                    var input = $(this);
                    var value = input.val();

                    if (value === '') {
                        input.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);					
                    } else if (disallowedCharsRegex_general.test(value)) {
                        // Check if the value contains not allowed characters
                        input.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else {
                        input.addClass('is-valid');
                    }
                });

                /**
                 * Additional check for the select element
                 *  */ 
                form.find('select').not(billingIntervalInput).each(function () {
                    var selectInput = $(this);
                    if (selectInput.val() === '') {
                        selectInput.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else {
                        selectInput.addClass('is-valid');
                    }
                });

                /**
                 * separate validation for expiration date, billing length and billing interval
                 *  */
                if(licenseType.val() === 'subscription' || licenseType.val() === 'trial') {
                    // Expiration date validation after success
                    if (expirationDateInput.val() === '') {
                        expirationDateInput.addClass('is-invalid');
                        disableLoadingEffect(submitButton);
                    } 
                    // First check the format with regex
                    else if (!expirationDateRegex.test(expirationDateInput.val())) {
                        expirationDateInput.addClass('is-invalid');
                        disableLoadingEffect(submitButton);
                    }
                    // Then check for disallowed characters - but modify the regex to allow :
                    else if (disallowedCharsRegex_forDate.test(expirationDateInput.val().replace(/[:]/g, ''))) {
                        expirationDateInput.addClass('is-invalid');
                        disableLoadingEffect(submitButton);
                    }
                    else {
                        // Validate expiration date max values - extract just the date part
                        var datePart = expirationDateInput.val().split(' ')[0];
                        var dateParts = datePart.split('-');
                        var month = parseInt(dateParts[1]);
                        var day = parseInt(dateParts[2]);

                        if (month > maxMonth || day > maxDay) {
                            expirationDateInput.addClass('is-invalid');
                            disableLoadingEffect(submitButton);
                        } else {
                            expirationDateInput.addClass('is-valid');
                        }
                    }

                    // billing length and interval validation remains the same
                    if(billingLengthInput.val() === '') {
                        billingLengthInput.addClass('is-invalid');
                        disableLoadingEffect(submitButton);
                    }
                    else {
                        billingLengthInput.addClass('is-valid');
                    }

                    if(billingIntervalInput.val() === '') {
                        billingIntervalInput.addClass('is-invalid');
                        disableLoadingEffect(submitButton);
                    }
                    else {
                        billingIntervalInput.addClass('is-valid');
                    }
                }

                /**
                 * Email validation after success
                 *  */ 
                if(emailInput.val() === '') {
                    emailInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (emailInput.val().lastIndexOf('.') === -1 || emailInput.val().lastIndexOf('.') === emailInput.val().length - 1 || !emailRegex.test(emailInput.val())) {
                    emailInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex_forEmail.test(emailInput.val())) {
                    emailInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    emailInput.addClass('is-valid');
                }
                
                /**
                 * Transaction ID validation
                 *  */ 
                    if(txn_idInput.val() === '') {
                    txn_idInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex_forDomain.test(txn_idInput.val())) {
                    txn_idInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    txn_idInput.addClass('is-valid');
                }

                /**
                 * First Name validation
                 */
                if (firstNameInput.val() === '') {
                    firstNameInput.addClass('is-invalid');
                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (!allowedCharsRegex_name.test(firstNameInput.val())) {
                    firstNameInput.addClass('is-invalid');
                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    firstNameInput.addClass('is-valid');
                }

                /**
                 * Last Name validation
                 */
                if (lastNameInput.val() === '') {
                    lastNameInput.addClass('is-invalid');
                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (!allowedCharsRegex_name.test(lastNameInput.val())) {
                    lastNameInput.addClass('is-invalid');
                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    lastNameInput.addClass('is-valid');
                }

                /**
                 * Allowed Domain validation
                 *  */ 
                if(allowedDomain.val() === '') {
                    allowedDomain.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex_number.test(allowedDomain.val())) {
                    allowedDomain.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    allowedDomain.addClass('is-valid');
                }
                
                /**
                 * Allowed Device validation
                 *  */ 
                if(allowedDevice.val() === '') {
                    allowedDevice.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else if (disallowedCharsRegex_number.test(allowedDevice.val())) {
                    allowedDevice.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                } else {
                    allowedDevice.addClass('is-valid');
                }  			

                /**
                 * Get the tab pane ID with invalid elements
                 */
                form.find('.is-invalid').each(function() {
                    // Get the parent tab-pane id
                    var tabPaneId = $(this).closest('.tab-pane').attr('id');

                    // Check if the tab pane id is not already in the array
                    if (tabPaneIds.indexOf(tabPaneId) === -1) {
                        // If not, add it to the array
                        tabPaneIds.push(tabPaneId);
                    }                        
                });

                tabPaneIds.forEach(function(tabPaneId) {
                    $('#' + tabPaneId + '-error').show();
                });

                /*****************
                 * End validations
                 ****************/

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('license-manager/create-new-license/submit') ?>',
                        method: 'POST',
                        data: form.serialize(),
						success: function (response) {
                            let toastType = 'info';
							let setNextLicenseKey = false;
							
                            if (response.success !== false) {
								newLicenseKeyInput.val('');
								
								Object.keys(response).forEach(function (key) {
                                    var item = response[key];
                                    toastType = 'success';
                                    var message = '';

                                    if (typeof item === 'object') {
                                        if (item.status === 1) {
                                            resetForm(form, newLicenseKeyInput);
                                        } else if (item.status === 0) {
                                            toastType = 'danger';
                                        }
                                        message = item.msg;

                                        if (setNextLicenseKey === false && item.nextLicenseKey) {
                                            newLicenseKeyInput.val(item.nextLicenseKey);
                                            setNextLicenseKey = true;
                                        }
                                    } else if (typeof item === 'string') {
                                        message = item;
                                    }

                                    showToast(toastType, message);
                                });
                            } else {
                                showToast('danger', response.msg);
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
        });
    </script>
<?= $this->endSection() //End section('scripts')?>
