<?= $this->extend('layouts/dashboard') ?>

<?php
$languageDirectories = get_product_directories(APPPATH . 'Language');
$language_reference = json_decode(file_get_contents(ROOTPATH . 'public/assets/libs/language-codes.json'), true);

// Check if features are enabled
$isLicensePrefixEnabled = $subscriptionChecker->isFeatureEnabled($userData->id, 'License_Prefix');
$isLicenseSuffixEnabled = $subscriptionChecker->isFeatureEnabled($userData->id, 'License_Suffix');
$isEnvatoSyncEnabled = $subscriptionChecker->isFeatureEnabled($userData->id, 'Envato_Sync');
?>

<?= $this->section('head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-check-input.form-check-input {
            width: 48px;
            height: 24px;
            margin-top: 0;                
            background-color: #2f55d4 !important;
            border-color: #2f55d4 !important;
        }
    </style>
<?= $this->endSection() //End section('head')?>

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
    <div class="row justify-content-center">
        <div class="col-12 mt-4 text-center">

            <!-- Menu -->
            <ul class="nav nav-pills shadow flex-column flex-sm-row d-md-inline-flex mb-0 p-1 bg-white-color rounded position-relative overflow-hidden" id="pills-tab" role="tablist">
                <li class="nav-item m-1">
                    <a class="nav-link py-2 px-5 active rounded" id="general-data" data-bs-toggle="pill" href="#generalData" role="tab" aria-controls="general-data" aria-selected="false">
                        <div class="text-center">
                            <h6 class="mb-0"><?= lang('Pages.General') ?></h6>
                        </div>
                    </a><!--end nav link-->
                </li><!--end nav item-->

                <li class="nav-item m-1">
                    <a class="nav-link py-2 px-5 rounded" id="license-manager" data-bs-toggle="pill" href="#licenseManager" role="tab" aria-controls="license-manager" aria-selected="false">
                        <div class="text-center">
                            <h6 class="mb-0"><?= lang('Pages.License_Manager') ?></h6>
                        </div>
                    </a><!--end nav link-->
                </li><!--end nav item-->
            </ul>   
        </div>                                                            

        <div class="col-12 mt-3 mb-3 text-center">                                
            <button class="mx-auto btn btn-primary" id="app-settings-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save_Settings') ?></button>
        </div>                            

        <div class="col-12 mb-3">
            <form class="" novalidate id="app-settings-form">

                <!-- Content -->
                <div class="tab-content" id="pills-tabContent">
                    <form class="" novalidate id="resend-license-form">
                    <!-- General Data -->
                    <div class="tab-pane show active" id="generalData" role="tabpanel" aria-labelledby="general-data">
                        <div class="row">

                            <!-- Company Name -->
                            <div class="col-lg-6 mb-3">
                                <div class="card rounded shadow p-4 border-0 mb-3">
                                    <label class="form-label" for="userCompanyName">
                                        <h5 class="mb-0">
                                            <?= lang('Pages.Company_Name') ?>
                                        </h5>
                                    </label>
                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <div class="form-icon position-relative">
                                                    <i data-feather="monitor" class="fea icon-sm icons"></i>
                                                    <input name="userCompanyName" id="userCompanyName" type="text" class="form-control ps-5" value="<?= $myConfig['userCompanyName'] ?? '' ?>" placeholder="<?= lang('Pages.the_company_name') ?>" required>
                                                    <div class="invalid-feedback">
                                                        <?= lang('Pages.company_name_invalid_feedback') ?>
                                                    </div>                                                                        
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                       

                                    </div><!--end row-->
                                </div>
                            </div>

                            <!-- Company Address -->
                            <div class="col-lg-6 mb-3">
                                <div class="card rounded shadow p-4 border-0 mb-3">
                                    <label class="form-label" for="userCompanyAddress">
                                        <h5 class="mb-0">
                                            <?= lang('Pages.Company_Address') ?>
                                        </h5>
                                    </label>
                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <div class="form-icon position-relative">
                                                    <i data-feather="monitor" class="fea icon-sm icons"></i>
                                                    <textarea name="userCompanyAddress" id="userCompanyAddress" rows="4" class="form-control ps-5" placeholder="<?= lang('Pages.the_company_address') ?>" required><?= $myConfig['userCompanyAddress'] ?? '' ?></textarea>
                                                    <div class="invalid-feedback">
                                                        <?= lang('Pages.company_name_invalid_feedback') ?>
                                                    </div>                                                                        
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                       

                                    </div><!--end row-->
                                </div>
                            </div>

                            <!-- Date and Time -->
                            <div class="col-lg-6 mb-3">                                                
                                <div class="card border-0 rounded shadow p-4">
                                    <h5 class="mb-0"><?= lang('Pages.Date_and_Time') ?></h5>
                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="defaultTimezone"><?= lang('Pages.Default_Timezone') ?> <span class="text-danger">*</span></label>
                                                <select class="form-select form-control ps-5" id="defaultTimezone" name="defaultTimezone" required="">
                                                    <option value="" disabled>- <?= lang('Pages.Select_Timezone') ?> -</option>

                                                    <?php 
                                                    $timezone_references = json_decode(file_get_contents(ROOTPATH . 'public/assets/libs/timezone-codes.json'), true); 
                                                    foreach($timezone_references as $continent => $places) { ?>
                                                        <optgroup label="<?= $continent ?>">
                                                            <?php foreach ($places as $place) { 
                                                                $value = $continent.'/'.$place;
                                                            ?>
                                                                <option value="<?= $value ?>" <?= $myConfig['defaultTimezone'] === $value ? 'selected' : ''?> ><?= str_replace('_', '. ', $place) ?></option>
                                                            <?php } ?>
                                                        </optgroup>
                                                    <?php } ?>

                                                </select>
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.Default_Timezone_feedback') ?>
                                                </div> 
                                            </div>
                                        </div><!--end col-->                                                       

                                    </div><!--end row-->
                                </div>
                            </div>

                            <!-- Locale -->
                            <div class="col-lg-6 mb-3">    
                                <div class="card border-0 rounded shadow p-4">
                                    <h5 class="mb-0"><?= lang('Pages.Locale') ?></h5>
                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="defaultLocale"><?= lang('Pages.Default_Language') ?> <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="file-text" class="fea icon-sm icons"></i>
                                                    <select class="form-select form-control ps-5" id="defaultLocale" name="defaultLocale" required="">
                                                        <option value="" disabled>- <?= lang('Pages.Select_Language') ?> -</option>
                                                        <?php foreach($languageDirectories as $languageDirectory) { ?>
                                                            <option value="<?= $languageDirectory ?>" <?= $myConfig['defaultLocale'] === $languageDirectory ? 'selected' : ''?> ><?= $language_reference[$languageDirectory]['EnglishName'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        <?= lang('Pages.Select_Language_feedback') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                       

                                    </div><!--end row-->
                                </div>                                                    
                            </div><!--end col-->

                            <!-- File extensions -->
                            <div class="col-lg-6 mb-3">    
                                <div class="card border-0 rounded shadow p-4">
                                    <h5 class="mb-0"><?= lang('Pages.Product_File_Extensions') ?></h5>
                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="acceptedFileExtensions"><?= lang('Pages.File_Extension') ?> <span class="text-danger">*</span><br><span
                                                    class="text-info"><?= lang('Pages.File_Extension_info') ?></span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="file" class="fea icon-sm icons"></i>
                                                    <textarea name="acceptedFileExtensions" id="acceptedFileExtensions" rows="1" class="form-control ps-5" placeholder="<?= lang('Pages.List_of_file_extensions') ?> :" required><?php
                                                    if($myConfig['acceptedFileExtensions']) {
                                                        $fileExtensions = json_decode($myConfig['acceptedFileExtensions'], true);
                                                        
                                                        foreach($fileExtensions as $fileExtension) {
                                                            echo $fileExtension;
                                                        }
                                                    }?></textarea>
                                                    <div class="invalid-feedback"><?= lang('Pages.File_Extension_feedback') ?></div>
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                       

                                    </div><!--end row-->
                                </div>                                                    
                            </div><!--end col-->

                            <!-- Default theme -->
                            <div class="col-lg-6 mb-3">
                                <div class="card border-0 rounded shadow p-4">
                                    <h5 class="mb-0"><?= lang('Pages.Theme') ?></h5>
                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="defaultTheme"><?= lang('Pages.Default_Theme') ?> <a href="javascript:void(0)" class="delete-cookies-action text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Delete_cookies') ?>"><i class="mdi mdi-delete-circle-outline"> </i> </a></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="moon" class="fea icon-sm icons"></i>
                                                    <select class="form-select form-control ps-5" id="defaultTheme" name="defaultTheme">
                                                        <option value="" disabled><?= lang('Pages.Select_Option') ?></option>
                                                        <option value="light" <?= $myConfig['defaultTheme'] === 'light' ? 'selected' : ''?> ><?= lang('Pages.Light_Mode') ?></option>
                                                        <option value="dark" <?= $myConfig['defaultTheme'] === 'dark' ? 'selected' : ''?> ><?= lang('Pages.Dark_Mode') ?></option>
                                                        <option value="system" <?= $myConfig['defaultTheme'] === 'system' ? 'selected' : ''?>><?= lang('Pages.Use_system_setting') ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div><!--end col-->                                                       

                                    </div><!--end row-->
                                </div>                                                    
                            </div><!--end col-->                                             

                        </div><!--end row-->
                    </div>

                    <!-- License Manager -->
                    <div class="tab-pane" id="licenseManager" role="tabpanel" aria-labelledby="license-manager">

                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <div class="card border-0 rounded shadow p-4">

                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <div class="switcher-pricing d-flex justify-content-center align-items-center">
                                                <label style="cursor: pointer;" class="toggler text-muted <?= $myConfig['licenseManagerOnUse'] === 'slm' ? 'toggler--is-active' : '' ?>" id="filt-slm"><?= lang('Pages.SLM_Wordpress_Plugin') ?></label>
                                                <div class="form-check form-switch mx-3">
                                                    <input class="form-check-input" type="checkbox" id="switcherLicenseManager" name="licenseManagerOnUse" <?= $myConfig['licenseManagerOnUse'] === 'slm' ? '' : 'checked' ?>>
                                                </div>
                                                <label style="cursor: pointer;" class="toggler text-muted <?= $myConfig['licenseManagerOnUse'] === 'built-in' ? 'toggler--is-active' : '' ?>" id="filt-builtin"><?= lang('Pages.Builtin_License_Manager_switch') ?></label>
                                            </div>
                                        </div><!--end col-->
                                    </div><!--end row-->
                                </div>
                            </div><!--end col--> 
                        </div><!--end row-->

                        <div class="row g-2 mb-3">
                            <div class="col-xl-2 col-lg-3 col-md-4 col-12 mb-3">
                                <div class="card rounded border-0 shadow p-1">
                                    <ul class="nav nav-pills nav-link-soft nav-justified flex-column bg-white-color mt-0 mb-0" id="pills-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link rounded active" id="LicManagerGeneral-tab" data-bs-toggle="pill" href="#LicManagerGeneral" role="tab" aria-controls="LicManagerGeneral" aria-selected="false">
                                                <div class="text-start px-3">
                                                    <span class="mb-0"><?= lang('Pages.General') ?></span>
                                                </div>
                                            </a><!--end nav link-->
                                        </li><!--end nav item-->
                                        
                                        <li class="nav-item mt-2 slm-options <?= $myConfig['licenseManagerOnUse'] === 'slm' ? '' : 'hide' ?>">
                                            <a class="nav-link rounded" id="SLMWP-tab" data-bs-toggle="pill" href="#SLMWP" role="tab" aria-controls="SLMWP" aria-selected="false">
                                                <div class="text-start px-3">
                                                    <span class="mb-0"><?= lang('Pages.SLM_WP') ?></span>
                                                </div>
                                            </a><!--end nav link-->
                                        </li><!--end nav item-->
                                        
                                        <li class="nav-item mt-2 built-in-options <?= $myConfig['licenseManagerOnUse'] !== 'slm' ? '' : 'hide' ?>">
                                            <a class="nav-link rounded" id="BuiltinLM-tab" data-bs-toggle="pill" href="#BuiltinLM" role="tab" aria-controls="BuiltinLM" aria-selected="false">
                                                <div class="text-start px-3">
                                                    <span class="mb-0"><?= lang('Pages.Builtin_SLM') ?></span>
                                                </div>
                                            </a><!--end nav link-->
                                        </li><!--end nav item-->
                                        
										<li class="nav-item mt-2 built-in-options <?= $myConfig['licenseManagerOnUse'] !== 'slm' ? '' : 'hide' ?>">
                                            <a class="nav-link rounded" id="LicManagerAPIs-tab" data-bs-toggle="pill" href="#LicManagerAPIs" role="tab" aria-controls="LicManagerAPIs" aria-selected="false">
                                                <div class="text-start px-3">
                                                    <span class="mb-0"><?= lang('Pages.API_Keys') ?></span>
                                                </div>
                                            </a><!--end nav link-->
                                        </li><!--end nav item-->

                                        <?php if($isEnvatoSyncEnabled) : ?>
                                            <li class="nav-item mt-2 built-in-options <?= $myConfig['licenseManagerOnUse'] !== 'slm' ? '' : 'hide' ?>">
                                                <a class="nav-link rounded" id="EnvatoAuthor-tab" data-bs-toggle="pill" href="#EnvatoAuthor" role="tab" aria-controls="EnvatoAuthor" aria-selected="false">
                                                    <div class="text-start px-3">
                                                        <span class="mb-0"><?= lang('Pages.Envato_Author') ?></span>
                                                    </div>
                                                </a><!--end nav link-->
                                            </li><!--end nav item-->
                                        <?php endif; ?>
                                    </ul><!--end nav pills-->
                                </div>
                            </div><!--end col-->

                            <div class="col-xl-10 col-lg-9 col-md-8 col-12">
                                <div class="tab-content rounded-0 shadow-0" id="pills-tabContent">
                                    <!-- General SLM Settings -->
                                    <div class="tab-pane fade show active" id="LicManagerGeneral" role="tabpanel" aria-labelledby="LicManagerGeneral-tab">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">
                                                    <h5 class="mb-0"><?= lang('Pages.General_License_Settings') ?></h5>
                                                </div>
                                            </div><!--end col-->

                                            <?php if($isLicensePrefixEnabled) : ?>
                                                <div class="col-lg-6 col-md-12 mb-3">
                                                    <div class="card border-0 rounded shadow p-4">                                                                        
                                                        <label class="form-label" for="licensePrefix"><?= lang('Pages.License_Prefix_Label') ?></label>
                                                        <div class="form-icon position-relative">
                                                            <i data-feather="type" class="fea icon-sm icons"></i>
                                                            <input name="licensePrefix" id="licensePrefix" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Specify_a_prefix_for_the_license_keys') ?>" value="<?= $myConfig['licensePrefix'] ?? '' ?>">
                                                        </div>
                                                    </div>
                                                </div><!--end col-->
                                            <?php endif; ?>

                                            <?php if($isLicenseSuffixEnabled) : ?>
                                                <div class="col-lg-6 col-md-12 mb-3">
                                                    <div class="card border-0 rounded shadow p-4">                                                                        
                                                        <label class="form-label" for="licenseSuffix"><?= lang('Pages.License_Suffix_Label') ?></label>
                                                        <div class="form-icon position-relative">
                                                            <i data-feather="type" class="fea icon-sm icons"></i>
                                                            <input name="licenseSuffix" id="licenseSuffix" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.Specify_a_suffix_for_the_license_keys') ?>" value="<?= $myConfig['licenseSuffix'] ?? '' ?>">
                                                        </div>
                                                    </div>
                                                </div><!--end col-->
                                            <?php endif; ?>

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="licenseKeyCharsCount"><?= lang('Pages.License_Key_Chars_Count_Label') ?></label>
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="hash" class="fea icon-sm icons"></i>
                                                        <input name="licenseKeyCharsCount" id="licenseKeyCharsCount" type="number" class="form-control ps-5" placeholder="<?= lang('Pages.Preferred_number_of_characters') ?>" value="<?= $myConfig['licenseKeyCharsCount'] ?? '' ?>">
                                                    </div>                                                    
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <div class="d-flex justify-content-between ">
                                                        <label class="h6 mb-0" for="autoExpireLicenseKeys"><?= lang('Pages.Auto_Expire_License_Keys') ?></label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" <?= $myConfig['autoExpireLicenseKeys'] ? 'checked' : '' ?> id="autoExpireLicenseKeys" name="autoExpireLicenseKeys">                                                                    
                                                        </div>
                                                    </div>
                                                    <span class="small text-info">
                                                        <?= lang('Pages.Auto_Expire_License_Keys_desc') ?>
                                                    </span>                                                  
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <div class="d-flex justify-content-between ">
                                                        <label class="h6 mb-0" for="defaultAllowedDomains"><?= lang('Pages.Maximum_Allowed_Domains') ?></label>
                                                        <div class="form-icon position-relative">
                                                            <i data-feather="hash" class="fea icon-sm icons"></i>
                                                            <input name="defaultAllowedDomains" id="defaultAllowedDomains" type="number" class="form-control ps-5" placeholder="" value="<?= $myConfig['defaultAllowedDomains'] ?? '0' ?>">
                                                        </div>
                                                    </div>
                                                    <span class="small text-info">
                                                        <?= lang('Pages.Maximum_Allowed_Domains_desc') ?>
                                                    </span>                                                 
                                                </div>
                                            </div><!--end col-->
                                            
                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <div class="d-flex justify-content-between ">
                                                        <label class="h6 mb-0" for="defaultAllowedDevices"><?= lang('Pages.Maximum_Allowed_Devices') ?></label>
                                                        <div class="form-icon position-relative">
                                                            <i data-feather="hash" class="fea icon-sm icons"></i>
                                                            <input name="defaultAllowedDevices" id="defaultAllowedDevices" type="number" class="form-control ps-5" placeholder="" value="<?= $myConfig['defaultAllowedDevices'] ?? '0' ?>">
                                                        </div>
                                                    </div>
                                                    <span class="small text-info">
                                                        <?= lang('Pages.Maximum_Allowed_Devices_desc') ?>
                                                    </span>                                               
                                                </div>
                                            </div><!--end col-->
                                        </div><!--end row-->
                                    </div><!--end teb pane-->
                                    
                                    <!-- SLM WP settings -->
                                    <div class="tab-pane fade rounded" id="SLMWP" role="tabpanel" aria-labelledby="SLMWP-tab">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">
                                                    <h5 class="mb-0"><?= lang('Pages.SLM_License_Server') ?></h5>
                                                    <small class="text-info mb-3">[ <a href="https://prod.merafsolutions.com/download/MERAF%20Production%20Panel/SLM-WP-Plugin"><?= lang('Pages.download_SLM_plugin') ?></a> ]</small>

                                                    <label class="form-label" for="licenseServerDomain"><?= lang('Pages.Domain_Name_Label') ?> <span class="text-danger">*</span></label>
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="globe" class="fea icon-sm icons"></i>
                                                        <input name="licenseServerDomain" id="licenseServerDomain" type="url" class="form-control ps-5" placeholder="<?= lang('Pages.SLM_Domain_Name') ?>" value="<?= $myConfig['licenseServerDomain'] ?? '' ?>" required>
                                                        <div class="invalid-feedback">
                                                            <?= lang('Pages.Please_enter_the_SLM_Domain_Name_with_https_and_trailing_slash') ?>
                                                        </div>
                                                    </div>                                                                            
                                                </div>
                                            </div><!--end col-->   

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="licenseServer_Validate_SecretKey"><?= lang('Pages.License_Validation_SecretKey_Label') ?> <span class="text-danger">*</span></label>
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="key" class="fea icon-sm icons"></i>
                                                        <input name="licenseServer_Validate_SecretKey" id="licenseServer_Validate_SecretKey" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.SLM_Validation_Secret_Key') ?>" value="<?= $myConfig['licenseServer_Validate_SecretKey'] ?? '' ?>" required>
                                                        <div class="invalid-feedback">
                                                            <?= lang('Pages.Please_enter_the_SLM_Validation_Secret_Key') ?>
                                                        </div>
                                                    </div>                                
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="licenseServer_Create_SecretKey"><?= lang('Pages.License_Creation_SecretKey_Label') ?> <span class="text-danger">*</span></label>
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="key" class="fea icon-sm icons"></i>
                                                        <input name="licenseServer_Create_SecretKey" id="licenseServer_Create_SecretKey" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.SLM_License_Creation_Secret_Key') ?>" value="<?= $myConfig['licenseServer_Create_SecretKey'] ?? '' ?>" required>
                                                        <div class="invalid-feedback">
                                                            <?= lang('Pages.Please_enter_the_SLM_License_Creation_Secret_Key') ?>
                                                        </div>
                                                    </div>                                      
                                                </div>
                                            </div><!--end col-->                                                                   
                                        </div><!--end row-->
                                    </div><!--end teb pane-->

                                    <!-- Built-in SLM -->
                                    <div class="tab-pane fade rounded" id="BuiltinLM" role="tabpanel" aria-labelledby="BuiltinLM-tab">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">
                                                    <h5 class="mb-0"><?= lang('Pages.Builtin_License_Manager') ?></h5>
                                                    <small class="text-info mb-3">[ <a href="https://prod.merafsolutions.com/download/MERAF%20Production%20Panel%20SaaS/WooCommerce-Addon-Plugin"><?= lang('Pages.download_Woocommerce_plugin') ?></a> ]</small>

                                                    <label class="form-label" for="woocommerceServerDomain"><?= lang('Pages.Domain_Name_Label') ?> </label>                                                                        
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="globe" class="fea icon-sm icons"></i>
                                                        <input name="woocommerceServerDomain" id="woocommerceServerDomain" type="url" class="form-control ps-5" placeholder="<?= lang('Pages.WooCommerce_Domain_Name') ?>" value="<?= $myConfig['woocommerceServerDomain'] ?? '' ?>" required>
                                                        <div class="invalid-feedback">
                                                            <?= lang('Pages.Please_enter_the_WooCommerce_Domain_Name_with_https_and_trailing_slash') ?>
                                                        </div>
                                                    </div>
                                                    <span class="small text-info"><?= lang('Pages.Woocommerce_plugin_note') ?></span>
                                                </div>                                                                  
                                            </div><!--end col--> 
                                            
                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="default_license_status"><?= lang('Pages.default_license_status') ?></label>
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="tag" class="fea icon-sm icons"></i>
                                                        <select class="form-select form-control ps-5" id="default_license_status" name="default_license_status">
                                                            <option value="" disabled><?= lang('Pages.Select_Option') ?></option>
                                                            <option value="active" <?= $myConfig['default_license_status'] === 'active' ? 'selected' : ''?>>Active</option>
                                                            <option value="pending" <?= $myConfig['default_license_status'] === 'pending' ? 'selected' : ''?>>Pending</option>                                                                                
                                                            <option value="blocked" <?= $myConfig['default_license_status'] === 'blocked' ? 'selected' : ''?>>Blocked</option>
                                                            <option value="expired" <?= $myConfig['default_license_status'] === 'expired' ? 'selected' : ''?>>Expired</option>
                                                        </select>
                                                    </div>                                                                            
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="defaultTrialDays"><?= lang('Pages.default_trial_days') ?></label>
                                                    <div class="form-icon position-relative">
                                                        <i data-feather="hash" class="fea icon-sm icons"></i>
                                                        <input name="defaultTrialDays" id="defaultTrialDays" type="number" class="form-control ps-5" placeholder="<?= lang('Pages.Preferred_number_of_trial_days') ?>" value="<?= $myConfig['defaultTrialDays'] ?? '' ?>">
                                                    </div>                                                    
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <p class="form-label"><?= lang('Pages.Master_License_Log') ?></p>
                                                    <small class="text-info mb-1"><?= lang('Pages.clear_all_license_logs_info') ?></small>
                                                    <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm me-2" id="clear-all-logs-btn"><i class="uil uil-trash"></i> <?= lang('Pages.clear_all_license_logs') ?></a>                                                 
                                                </div>
                                            </div><!--end col-->

                                        </div><!--end row-->                                                      
                                    </div><!--end teb pane-->

                                    <!-- APIs -->
                                    <div class="tab-pane fade rounded " id="LicManagerAPIs" role="tabpanel" aria-labelledby="LicManagerAPIs-tab">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">
                                                    <h5 class="mb-0"><?= lang('Pages.API_Keys_heading') ?></h5>
                                                </div>
                                            </div><!--end col--> 

                                            <?php
                                            $newSecretKey1 = generateApiKey();
                                            $newSecretKey2 = generateApiKey();
                                            $newSecretKey3 = generateApiKey();
                                            $newSecretKey4 = generateApiKey();
                                            $newSecretKey5 = generateApiKey();
                                            ?>

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="License_Create_SecretKey"><?= lang('Pages.License_Creation_SecretKey_Label') ?> <span class="text-danger">*</span></label>

                                                    <div class="form-icon position-relative input-group input-group-sm">
                                                            <span class="input-group-text">
                                                                <a href="javascript:void(0)" class="text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.click_to_generate_new_key') ?>"><i class="mdi mdi-lock-reset"> </i> </a>                                                                            
                                                            </span>
                                                            &nbsp;
                                                            <input readonly="readonly" name="License_Create_SecretKey" id="License_Create_SecretKey" type="text" class="form-control bg-light copy-to-clipboard" placeholder="<?= lang('Pages.License_Creation_Secret_Key') ?>" value="<?= $myConfig['License_Create_SecretKey'] ?? $newSecretKey1 ?>" required>
                                                    </div>										
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="License_Validate_SecretKey"><?= lang('Pages.License_Validation_SecretKey_Label') ?> <span class="text-danger">*</span></label>

                                                    <div class="form-icon position-relative input-group input-group-sm">
                                                            <span class="input-group-text">
                                                                <a href="javascript:void(0)" class="text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.click_to_generate_new_key') ?>"><i class="mdi mdi-lock-reset"> </i> </a>                                                                            
                                                            </span>
                                                            &nbsp;
                                                            <input readonly="readonly" name="License_Validate_SecretKey" id="License_Validate_SecretKey" type="text" class="form-control bg-light copy-to-clipboard" placeholder="<?= lang('Pages.Validation_Secret_Key') ?>" value="<?= $myConfig['License_Validate_SecretKey'] ?? $newSecretKey2 ?>" required>                                                                    
                                                    </div>                                                                                                                
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="License_DomainDevice_Registration_SecretKey"><?= lang('Pages.License_DomainDevice_Registration_SecretKey_Label') ?> <span class="text-danger">*</span></label>

                                                    <div class="form-icon position-relative input-group input-group-sm">
                                                            <span class="input-group-text">
                                                                <a href="javascript:void(0)" class="text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.click_to_generate_new_key') ?>"><i class="mdi mdi-lock-reset"> </i> </a>                                                                            
                                                            </span>
                                                            &nbsp;
                                                            <input readonly="readonly" name="License_DomainDevice_Registration_SecretKey" id="License_DomainDevice_Registration_SecretKey" type="text" class="form-control bg-light copy-to-clipboard" placeholder="<?= lang('Pages.License_DomainDevice_Registration_SecretKey_Label') ?>" value="<?= $myConfig['License_DomainDevice_Registration_SecretKey'] ?? $newSecretKey3 ?>" required>                                                                    
                                                    </div>                                                                                                                
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3">
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="Manage_License_SecretKey"><?= lang('Pages.Manage_License_SecretKey_Label') ?> <span class="text-danger">*</span></label>

                                                    <div class="form-icon position-relative input-group input-group-sm">
                                                            <span class="input-group-text">
                                                                <a href="javascript:void(0)" class="text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.click_to_generate_new_key') ?>"><i class="mdi mdi-lock-reset"> </i> </a>                                                                            
                                                            </span>
                                                            &nbsp;
                                                            <input readonly="readonly" name="Manage_License_SecretKey" id="Manage_License_SecretKey" type="text" class="form-control bg-light copy-to-clipboard" placeholder="<?= lang('Pages.Manage_License_SecretKey_Label') ?>" value="<?= $myConfig['Manage_License_SecretKey'] ?? $newSecretKey4 ?>" required>                                                                    
                                                    </div>                                                                                                                
                                                </div>
                                            </div><!--end col-->

                                            <div class="col-lg-6 col-md-12 mb-3"> 
                                                <div class="card border-0 rounded shadow p-4">                                                                        
                                                    <label class="form-label" for="General_Info_SecretKey"><?= lang('Pages.General_Info_SecretKey_Label') ?> <span class="text-danger">*</span></label>

                                                    <div class="form-icon position-relative input-group input-group-sm">
                                                            <span class="input-group-text">
                                                                <a href="javascript:void(0)" class="text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.click_to_generate_new_key') ?>"><i class="mdi mdi-lock-reset"> </i> </a>                                                                            
                                                            </span>
                                                            &nbsp;
                                                            <input readonly="readonly" name="General_Info_SecretKey" id="General_Info_SecretKey" type="text" class="form-control bg-light copy-to-clipboard" placeholder="<?= lang('Pages.General_Info_SecretKey_Label') ?>" value="<?= $myConfig['General_Info_SecretKey'] ?? $newSecretKey5 ?>" required>                                                                    
                                                    </div>                                                                                                                
                                                </div>
                                            </div><!--end col-->

                                        </div>
                                    </div><!--end teb pane-->

                                    <?php if($isEnvatoSyncEnabled) : ?>
                                        <!-- API Envato Author -->
                                        <div class="tab-pane fade rounded " id="EnvatoAuthor" role="tabpanel" aria-labelledby="EnvatoAuthor-tab">
                                            <div class="row">
                                                <div class="col-lg-6 mb-3">
                                                    <div class="alert alert-info">
                                                        <?= lang('Pages.Envato_Sync_how_it_works') ?>
                                                    </div>
                                                </div>

                                                <!-- Envato Author Sync -->
                                                <div class="col-lg-6 mb-3">
                                                    <div class="card border-0 rounded shadow p-4">                                                                        
                                                        <div class="d-flex justify-content-between ">
                                                            <label class="h5 mb-0" for="userEnvatoSyncEnabled"><?= lang('Pages.Activate_Envato_Sync') ?></label>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" <?= $myConfig['userEnvatoSyncEnabled'] ? 'checked' : '' ?> id="userEnvatoSyncEnabled" name="userEnvatoSyncEnabled">                                                                    
                                                            </div>
                                                        </div>

                                                        <label class="form-label mt-4" for="userEnvatoAPIKey"><?= lang('Pages.userEnvatoAPIKey') ?></label>
                                                        <div class="form-icon position-relative">
                                                            <i data-feather="key" class="fea icon-sm icons"></i>
                                                            <input name="userEnvatoAPIKey" id="userEnvatoAPIKey" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.userEnvatoAPIKey') ?>" value="<?= $myConfig['userEnvatoAPIKey'] ?? '' ?>">
                                                        </div>                                                    
                                                    </div>
                                                </div><!--end col-->
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div><!--end col-->
                        </div><!--end row-->
                    </div>
                </div> 

            </form>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#defaultTimezone').select2();

            /***************************************
             * Handle clear all license activity log
             **************************************/
            $('#clear-all-logs-btn').on('click', function (e) {

                var submitButton = $(this);
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_clear_all_license_log') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('api/license/delete/all-logs/' . $myConfig['Manage_License_SecretKey']) ?>',
                        method: 'POST',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        },                            
                        success: function (response) {
                            let toastType = 'info';

                            if (response.result === 'success') {
                                toastType = 'success';
							} else if (response.result === 'error') {
								toastType = 'info';
                            } else {
                                toastType = 'danger';
                            }

                            showToast(toastType, response.message);
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
                }
            });

            /*******************************
             * Handle license manager switch
             ******************************/
            var slm = $("#filt-slm");
            var builtin = $("#filt-builtin");
            var switcher = $("#switcherLicenseManager");
            var slmWrapper = $(".slm-options");
            var builtinWrapper = $(".built-in-options");
            var slmTab = $('#SLMWP-tab');
            var builtinTab = $('#BuiltinLM-tab');
            var notificationTab = $('#LicManagerNotifs-tab');

            // Check if all required elements exist
            if (slm.length && builtin.length && switcher.length && slmWrapper.length && builtinWrapper.length && slmTab.length && builtinTab.length) {
                // Add event listener for SLM click
                slm.on("click", function(){
                    switcher.prop("checked", false);
                    slm.addClass("toggler--is-active");
                    builtin.removeClass("toggler--is-active");
                    slmWrapper.removeClass("hide");
                    builtinWrapper.addClass("hide");
                    notificationTab.addClass("hide");
                    slmTab.tab('show');
                });

                // Add event listener for Built-in click
                builtin.on("click", function(){
                    switcher.prop("checked", true);
                    builtin.addClass("toggler--is-active");
                    slm.removeClass("toggler--is-active");
                    slmWrapper.addClass("hide");
                    builtinWrapper.removeClass("hide");
                    notificationTab.removeClass("hide");
                    builtinTab.tab('show');
                });

                // Add event listener for Switcher click
                switcher.on("click", function(){
                    builtin.toggleClass("toggler--is-active");
                    slm.toggleClass("toggler--is-active");
                    slmWrapper.toggleClass("hide");
                    builtinWrapper.toggleClass("hide");
                    notificationTab.toggleClass("hide");
                    if (builtin.hasClass('toggler--is-active')) {
                        builtinTab.tab('show'); // Activate the Built-in tab pane
                    } else {
                        slmTab.tab('show'); // Activate the SLM tab pane
                    }
                });
            }

            /******************************
            // Handle the app settings save
            ******************************/
            $('#app-settings-submit').on('click', function (e) {
                e.preventDefault();

                const formElement = $('#app-settings-form');
                const submitButtonElement = $(this);                    
                const emailInputElement = $('.emailInput');
                const emailNameInputElement = $('.emailName');
                const websiteInputElement = $('#licenseServerDomain');
                const websiteInputElement2 = $('#woocommerceServerDomain');
                const validateSecretKeyInputElement = $('#licenseServer_Validate_SecretKey');
                const createSecretKeyInputElement = $('#licenseServer_Create_SecretKey');
                const BuiltinCreateSecretKeyInputElement = $('#License_Create_SecretKey');
                const BuiltinValidateSecretKeyInputElement = $('#License_Validate_SecretKey');
                const BuiltinRegistrationSecretKeyInputElement = $('#License_DomainDevice_Registration_SecretKey');
                const BuiltinManageSecretKeyInputElement = $('#Manage_License_SecretKey');
                const BuiltinGeneralSecretKeyInputElement = $('#General_Info_SecretKey');
                const licenseManagerOnUse = $('[name="licenseManagerOnUse"]').prop('checked') ? 'built-in' : 'slm';
                const defaultLicenseStatusSelect = $('#default_license_status');
                const defaultTheme = $('#defaultTheme');

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const disallowedCharsRegexGeneralText = /[^a-zA-Z0-9\s\-_\.]/;
                const disallowedCharsRegexForEmail = /[~!#$%&*+=|:()\[\]]/;
                const disallowedCharsRegexDomain = /[^a-zA-Z0-9\s\-_\.:/]/;
                const disallowedCharsRegexSecretKey = /[^a-zA-Z0-9]/;
                const disallowedFileExtension = /^[^a-zA-Z0-9\s\-_]/;

                // Enable button loading effect
                enableLoadingEffect(submitButtonElement);

                // Remove existing 'is-invalid' classes
                formElement.find('.is-invalid').removeClass('is-invalid').end().find('.is-valid').removeClass('is-valid');

                // Validation logic
                let validationErrors = [];

                // Validate File Extensions
                const validateFileExtension = () => {
                    const fileExtensionTextarea = $('#acceptedFileExtensions');
                    const fileExtensionLines = fileExtensionTextarea.val().split('\n');
                    let isValidExtension = true;

                    if (fileExtensionTextarea.val().trim() === '') {
                        isValidExtension = false;
                        showToast('danger', '<?= lang('Pages.file_extension_required') ?>');

                        // Disable loading effect after error toast
                        disableLoadingEffect(submitButtonElement);
                    }

                    for (let i = 0; i < fileExtensionLines.length; i++) { 
                        const trimmedLine = fileExtensionLines[i].trim();

                        if (trimmedLine !== '' && (disallowedCharsRegexGeneralText.test(trimmedLine) || disallowedFileExtension.test(trimmedLine) || trimmedLine.includes(' ') || trimmedLine.includes(','))) {
                            isValidExtension = false;
                            showToast('danger', '<?= lang('Pages.invalid_file_extension_error') ?>');

                            // Disable loading effect after error toast
                            disableLoadingEffect(submitButtonElement);
                        }
                    }

                    if (!isValidExtension) {
                        fileExtensionTextarea.addClass('is-invalid');

                        // Disable loading effect after error toast
                        disableLoadingEffect(submitButtonElement);
                    } else {
                        fileExtensionTextarea.addClass('is-valid');
                    }
                };

                // Validate <select> elements
                const validateSelectInputs = () => {
                    formElement.find('select').each(function () {
                        const selectInput = $(this);
                        const inputWithError = selectInput.attr('id');
                        let errorPlaceholder = selectInput.parent().find('label').text().replace('*', '');
                            if (selectInput.val() === '') {
                            // if (selectInput.val() === '' && selectInput.attr('id') !== 'selectedEmailTemplate') {
                                selectInput.addClass('is-invalid');
                                showToast('danger', '<?= lang('Pages.required_to_select_option') ?>' + errorPlaceholder.trim());

                                // Disable loading effect after error toast
                                disableLoadingEffect(submitButtonElement);
                            } else {
                                selectInput.addClass('is-valid');
                            }                                
                        // }
                    });
                };

                // Validate Website Input (SLM)
                const validateWebsiteInput = () => {
                    const websiteValue = websiteInputElement.val();
                    const websiteRegex = /^(http:\/\/|https:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}\/$/;
                    if (websiteValue === '' || !websiteRegex.test(websiteValue) || disallowedCharsRegexDomain.test(websiteValue)) {
                        websiteInputElement.addClass('is-invalid');
                        showToast('danger', '<?= lang('Pages.Invalid_Website_URL') ?>');

                        // Disable loading effect after error toast
                        disableLoadingEffect(submitButtonElement);
                    } else {
                        websiteInputElement.addClass('is-valid');
                    }
                };

                // Validate Website Input (WooCommerce)
                const validateWebsiteInput2 = () => {
                    const websiteValue = websiteInputElement2.val();
                    const websiteRegex = /^(http:\/\/|https:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}\/$/;
                    if(websiteValue !== '') {
                        if (!websiteRegex.test(websiteValue) || disallowedCharsRegexDomain.test(websiteValue)) {
                            websiteInputElement2.addClass('is-invalid');
                            showToast('danger', '<?= lang('Pages.Invalid_Website_URL') ?>');

                            // Disable loading effect after error toast
                            disableLoadingEffect(submitButtonElement);
                        } else {
                            websiteInputElement2.addClass('is-valid');
                        }
                    }
                };

                // Validate Secret Key Input
                const validateSecretKeyInput = (inputElement, inputName) => {
                    const inputValue = inputElement.val();
                    if (inputValue === '' || disallowedCharsRegexSecretKey.test(inputValue)) {
                        inputElement.addClass('is-invalid');
                        showToast('danger', `<?= lang('Pages.Invalid') ?> ${inputName}`);

                        // Disable loading effect after error toast
                        disableLoadingEffect(submitButtonElement);
                    } else {
                        inputElement.addClass('is-valid');
                    }
                };

                // Perform validations
                validateFileExtension();
                validateSelectInputs();

                if(licenseManagerOnUse === 'slm') {
                    validateWebsiteInput();
                    validateSecretKeyInput(validateSecretKeyInputElement, 'Validation Secret Key');
                    validateSecretKeyInput(createSecretKeyInputElement, 'License Creation Secret Key');
                }
                else {
                    validateWebsiteInput2();
                    validateSecretKeyInput(BuiltinValidateSecretKeyInputElement, 'Validation Secret Key');
                    validateSecretKeyInput(BuiltinCreateSecretKeyInputElement, 'License Creation Secret Key');
                    validateSecretKeyInput(BuiltinRegistrationSecretKeyInputElement, 'Domain/Device Registration Secret Key');
                    validateSecretKeyInput(BuiltinManageSecretKeyInputElement, 'Manage License Secret Key');
                    validateSecretKeyInput(BuiltinGeneralSecretKeyInputElement, 'General Info Secret Key');
                }

                // Check if there are any elements with 'is-invalid' class
                if (formElement.find('.is-invalid').length === 0) {

                    // Form data is valid, proceed with further processing
                    var data = new FormData(formElement[0]);

                    // Append additional data to the FormData object
                    // General
                    data.append('defaultTimezone', $('#defaultTimezone').val());
                    data.append('defaultLocale', $('#defaultLocale').val());
                    data.append('acceptedFileExtensions', $('#acceptedFileExtensions').val());
                    data.append('defaultTheme', $('#defaultTheme').val());

                    // License Manager
                    data.append('licenseManagerOnUse', licenseManagerOnUse);
                    data.append('licensePrefix', $('#licensePrefix').val());
                    data.append('licenseKeyCharsCount', $('#licenseKeyCharsCount').val());
                    data.append('licenseServerDomain', $('#licenseServerDomain').val());
                    data.append('woocommerceServerDomain', $('#woocommerceServerDomain').val());
                    data.append('licenseServer_Validate_SecretKey', $('#licenseServer_Validate_SecretKey').val());
                    data.append('licenseServer_Create_SecretKey', $('#licenseServer_Create_SecretKey').val());
                    data.append('License_Validate_SecretKey', $('#License_Validate_SecretKey').val());
                    data.append('License_Create_SecretKey', $('#License_Create_SecretKey').val());
                    data.append('License_DomainDevice_Registration_SecretKey', $('#License_DomainDevice_Registration_SecretKey').val());
                    data.append('Manage_License_SecretKey', $('#Manage_License_SecretKey').val());
                    data.append('General_Info_SecretKey', $('#General_Info_SecretKey').val());

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('app-settings/save') ?>',
                        method: 'POST',
                        processData: false,
                        contentType: false,
                        data: data,
                        success: function (response) {
                            let toastType = 'info';

                            if (response.inputs.success) {
                                toastType = 'success';

                                // hide or show Manage License sidebar menu
                                if(licenseManagerOnUse === 'slm') {
                                    $('#ManageLicense-sidebarMenu').hide();
                                }
                                else {
                                    $('#ManageLicense-sidebarMenu').show();
                                }
                            } else {
                                toastType = 'danger';
                            }

                            showToast(toastType, response.inputs.msg);
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
                            disableLoadingEffect(submitButtonElement);
                        }
                    });                
                }
            });
        });          
    </script>	

    <script type="text/javascript">    
        document.addEventListener("DOMContentLoaded", function() {
            // Helper functions
            function decodeHtmlEntities(text) {
                const textarea = document.createElement('textarea');
                textarea.innerHTML = text;
                return textarea.value;
            }

            function adjustTextareaHeight(textarea) {
                if (!textarea) return;
                
                // Store scroll position
                const scrollPos = textarea.scrollTop;
                
                // Reset height and calculate new height
                textarea.style.height = '0px';
                const newHeight = Math.max(
                    textarea.scrollHeight,
                    parseInt(getComputedStyle(textarea).lineHeight, 10) * parseInt(textarea.getAttribute('rows') || 1, 10)
                );
                textarea.style.height = newHeight + 'px';
                
                // Restore scroll position
                textarea.scrollTop = scrollPos;
            }

            function initializeTextarea(textarea) {
                if (!textarea) return;

                // Decode HTML entities if present
                if (textarea.value.includes('&lt;') || textarea.value.includes('&gt;')) {
                    textarea.value = decodeHtmlEntities(textarea.value);
                }

                // Set minimum height based on rows attribute
                const lineHeight = parseInt(getComputedStyle(textarea).lineHeight, 10);
                const rows = parseInt(textarea.getAttribute('rows') || 1, 10);
                const minHeight = lineHeight * rows;
                
                textarea.style.minHeight = minHeight + 'px';
                
                // Adjust height based on content
                adjustTextareaHeight(textarea);
            }

            // Add CSS dynamically
            // const style = document.createElement('style');
            // style.textContent = `
            //     textarea.form-control {
            //         box-sizing: border-box;
            //         resize: vertical;
            //         overflow-y: hidden;
            //         transition: height 0.1s ease-out;
            //     }
            // `;
            // document.head.appendChild(style);

            // Initialize textareas
            const textareaIds = [
                'acceptedFileExtensions'
            ];

            textareaIds.forEach(id => {
                const textarea = document.getElementById(id);
                if (textarea) {
                    // Initialize
                    initializeTextarea(textarea);

                    // Add event listeners
                    textarea.addEventListener('input', () => adjustTextareaHeight(textarea));
                    textarea.addEventListener('change', () => adjustTextareaHeight(textarea));
                    
                    // Also adjust on window resize
                    window.addEventListener('resize', () => adjustTextareaHeight(textarea));
                }
            });

            // Additional adjustment after a short delay to ensure proper rendering
            setTimeout(() => {
                textareaIds.forEach(id => {
                    const textarea = document.getElementById(id);
                    if (textarea) {
                        adjustTextareaHeight(textarea);
                    }
                });
            }, 100);
        });

        $('.mdi-close').click(function() {
            // Clear the value of the input field next to it
            var elementInput = $(this).closest('.form-icon').find('.form-control');
            elementInput.val('');
            elementInput.removeClass('is-invalid').removeClass('is-valid');
        });

        $('.mdi-refresh').click(function() {
            var tooltipElement = $(this).closest('[data-bs-toggle="tooltip"]');

            var elementID = $(this).closest('.form-icon').find('.form-control').attr('id');
            var requestURL = '<?= base_url('admin-options/global-settings/reset/') ?>' + elementID;

            var confirmDelete = confirm("<?= lang('Pages.Confirm_Delete_Message') ?>");

            if (confirmDelete) {
                $.ajax({
                    url: requestURL,
                    method: 'GET',
                    success: function (response) {
                        tooltipElement.tooltip('hide');

                        if (response.status == 1) {
                            var newSrc;
                            if (response.appIcon) {
                                newSrc = response.appIcon;
                            } else if (response.appLogo_light) {
                                newSrc = response.appLogo_light;
                            } else  {
                                newSrc = response.appLogo_dark;
                            }
                            
                            $('.' + elementID +'Preview').attr('src', newSrc);
                                                        
                            showToast('success',response.msg);

                        } else if (response.status == 2) {         
                            showToast('info', response.msg);
                        } else {	
                            showToast('danger', response.msg);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }
        });

        $('.mdi-lock-reset').click(function() {
            var clickedButton = $(this);
            var tooltipElement = clickedButton.closest('[data-bs-toggle="tooltip"]');

            var elementID = clickedButton.closest('.form-icon').find('.form-control').attr('id');
            var requestURL = '<?= base_url('app-settings/generate-new-key/') ?>' + elementID + '/8';

            var confirmReset = confirm("<?= lang('Pages.Confirm_Reset_Key') ?>");

            if (confirmReset) {
                // Show loading state
                var originalIcon = clickedButton.html();
                clickedButton.html('<i class="mdi mdi-loading mdi-spin"></i>');

                $.ajax({
                    url: requestURL,
                    method: 'GET',
                    success: function (response) {
                        tooltipElement.tooltip('hide');

                        let toastType = 'info';
                        let toastMessage = '';

                        if (response.status == 1) {
                            $('#' + elementID).val(response.msg);

                            // Check if the key was auto-saved
                            if (response.auto_saved === true) {
                                toastType = 'success';
                                toastMessage = `<?= lang('Pages.New_API_Key_Generated') ?> and automatically saved! <strong>${response.msg}</strong>`;
                            } else if (response.auto_saved === false) {
                                toastType = 'warning';
                                if (response.save_error) {
                                    toastMessage = `<?= lang('Pages.New_API_Key_Generated') ?> <strong>${response.msg}</strong><br><small class="text-warning">${response.save_error}</small>`;
                                } else {
                                    toastMessage = `<?= lang('Pages.New_API_Key_Generated') ?> <strong>${response.msg}</strong><br><small class="text-warning">Please click "Save Settings" to persist this change.</small>`;
                                }
                            } else {
                                // Fallback for backwards compatibility
                                toastType = 'info';
                                toastMessage = `<?= lang('Pages.New_API_Key_Generated') ?> <strong>${response.msg}</strong><br><small class="text-info">Please click "Save Settings" to save this change.</small>`;
                            }
                        } else if (response.status == 2) {
                            toastType = 'info';
                            toastMessage = response.msg || 'Key generation completed';
                        } else {
                            toastType = 'danger';
                            toastMessage = response.msg || 'Key generation failed';
                        }

                        showToast(toastType, toastMessage);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    },
                    complete: function () {
                        // Restore original icon
                        clickedButton.html(originalIcon);
                    }
                });
            }
        });
        
        $('.mdi-delete-circle-outline').click(function() {
            var tooltipElement = $(this).closest('[data-bs-toggle="tooltip"]');

            var requestURL = '<?= base_url('app-settings/delete-cookies') ?>';

            var confirmDelete = confirm("<?= lang('Pages.Confirm_delete_cookies') ?>");

            if (confirmDelete) {
                $.ajax({
                    url: requestURL,
                    method: 'GET',
                    success: function (response) {
                        tooltipElement.tooltip('hide');
                        
                        let toastType = 'info';

                        if (response.success) {
                            toastType = 'success';
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
    </script> 
<?= $this->endSection() //End section('scripts')?>