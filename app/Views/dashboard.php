<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
    <?php if($myConfig['PWA_App_enabled']) { include_once APPPATH . 'Views/includes/dashboard/pwa-body.php'; } ?>
    <div class="row align-items-center justify-content-between mb-4">
        <div class="col-md-6">
            <h4 class="fw-bold text-primary mb-1">
                <?= lang('Pages.welcome_back', ['username' => htmlspecialchars($userData->first_name) . ' ' . htmlspecialchars($userData->last_name)]) ?>    
            </h4>
            <h5 class="text-muted mb-0"><?= lang('Pages.' . ucwords($section) ) ?></h5>
        </div>

        <?php if($myConfig['licenseManagerOnUse'] !== 'slm') { ?>
            <div class="col-md-4 col-lg-3 mt-3 mt-md-0">
                <select class="form-select form-control shadow-sm" id="dailychart">
                    <?php
                    // Get current month and year
                    $currentMonth = (int)date('m');
                    $currentYear = (int)date('Y');

                    // Get previous months including the current month up to January of the current year
                    $previousMonths = array();
                    for ($i = $currentMonth; $i >= 1; $i--) {
                        $monthTimestamp = mktime(0, 0, 0, $i, 1, $currentYear);
                        $previousMonth = date('F', $monthTimestamp);  // Full month name
                        $previousMonthValue = date('M-Y', $monthTimestamp);  // Short month name with year
                        $previousMonths[$previousMonthValue] = sprintf(
                            '<option value="%s">%s-%s</option>',
                            htmlspecialchars($previousMonthValue),
                            $previousMonth,
                            $currentYear
                        );
                    }

                    // Reverse the order of the array
                    $previousMonthsReversed = array_reverse($previousMonths, true);

                    // Get current year option
                    $currentYearOption = "<option value='$currentYear'>" . lang('Pages.Current_Year') . "</option>";
                    $previousYearOption = "<option value='" . ($currentYear - 1) . "'>" . lang('Pages.Previous_Year') . "</option>";

                    // All years option
                    $allYearsOption = "<option value='all_data' selected>" . lang('Pages.All_Data') . "</option>";

                    // Add all years option
                    echo $allYearsOption;

                    // Generate options for previous months up to January, including the current month
                    foreach ($previousMonths as $option) {
                        echo $option;
                    }

                    // Add current year option
                    echo $currentYearOption;
                    echo $previousYearOption;
                    ?>
                </select>
            </div>
        <?php } ?>
    </div>

    <?php $productNames = $sideBarMenu['products']; if($myConfig['licenseManagerOnUse'] === 'slm') { ?>
        <!------------------------ SLM WP Plugin ------------------------>
        <!-- Info box -->
        <div class="row row-cols-xl-3 row-cols-md-2 row-cols-1 justify-content-center">
            <div class="col mt-4">
                <a href="<?= base_url('product-manager/modify-product') ?>" class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon text-center rounded-pill" aria-hidden="true">
                            <i class="uil uil-store fs-4 mb-0"></i>
                        </div>
                        <div class="flex-1 ms-3">
                            <h6 class="mb-0 text-muted"><?= lang('Pages.Total_Product') ?></h6>
                            <p class="fs-5 text-dark fw-bold mb-0"><span class="counter-value" data-target="<?= isset($productNames) && is_array($productNames) ? count($productNames) : 0 ?>">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->
            
            <div class="col mt-4">
                <a href="<?= base_url('product-manager/product-variations') ?>" class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon text-center rounded-pill" aria-hidden="true">
                            <i class="uil uil-tag-alt fs-4 mb-0"></i>
                        </div>
                        <div class="flex-1 ms-3">
                            <h6 class="mb-0 text-muted"><?= lang('Pages.Product_variations') ?></h6>
                            <?php $productVariations = getProductVariations($userData->id); ?>
                            <p class="fs-5 text-dark fw-bold mb-0"><span class="counter-value" data-target="<?= isset($productVariations) && is_array($productVariations) ? count($productVariations) : 0 ?>">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

            <div class="col mt-4">
                <a href="<?= base_url('email-service/template') ?>" class="features feature-primary d-flex justify-content-between align-items-center rounded shadow p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon text-center rounded-pill" aria-hidden="true">
                            <i class="uil uil-file-blank fs-4 mb-0"></i>
                        </div>
                        <div class="flex-1 ms-3">
                            <h6 class="mb-0 text-muted"><?= lang('Pages.email_templates') ?></h6>
                            <?php $emailTemplates = getEmailTemplateDetails($userData->id); ?>
                            <p class="fs-5 text-dark fw-bold mb-0"><span class="counter-value" data-target="<?= isset($emailTemplates) && is_array($emailTemplates) ? count($emailTemplates) : 0 ?>">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

        </div><!--end row-->

        <div class="row justify-content-center">
            <div class="col-lg-12 col-xl-12 mt-4">
                <div class="card blog blog-primary blog-detail border-0 shadow rounded">
                    <div class="card-body content">
                        <h4 class="mt-3"><?= $myConfig['appName'] ?></h4>                        
                        <p class="text-muted mt-3"><?= lang('Pages.index_content', ['appName' => $myConfig['appName']]) ?></p>                      
                        <blockquote class="blockquote mt-3 p-3">
                            <p class="text-muted mb-0 fst-italic"><?= lang('Pages.select_from_main_menu') ?></p>
                        </blockquote>

                    </div>
                </div>
            </div>
        </div>
    <?php } else {?>
        <!------------------------ Built-in SLM ------------------------>

        <!-- Main Infos -->
        <div class="bg-light px-4 py-4 mt-4 rounded shadow-sm" style="background: url('assets/images/bg.png') center;">
            <!-- Info box -->
            <div class="row row-cols-xl-4 row-cols-md-2 row-cols-1 g-4">
                <div class="col">
                    <a href="<?= base_url('product-manager/modify-product') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-primary text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-store fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-primary mb-0"><?= lang('Pages.Total_Product') ?></h6>
                                <p class="fs-4 text-primary fw-bold mb-0"><span class="counter-value" data-target="<?= isset($productNames) && is_array($productNames) ? count($productNames) : 0 ?>">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->
                
                <div class="col">
                    <a href="<?= base_url('product-manager/product-variations') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-info text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-tag-alt fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-info mb-0"><?= lang('Pages.Product_variations') ?></h6>
                                <?php $productVariations = getProductVariations($userData->id); ?>
                                <p class="fs-4 text-info fw-bold mb-0"><span class="counter-value" data-target="<?= isset($productVariations) && is_array($productVariations) ? count($productVariations) : 0 ?>">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->

                <div class="col">
                    <a href="<?= base_url('email-service/template') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-warning text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-file-blank fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-warning mb-0"><?= lang('Pages.email_templates') ?></h6>
                                <?php $emailTemplates = getEmailTemplateDetails($userData->id); ?>
                                <p class="fs-4 text-warning fw-bold mb-0"><span class="counter-value" data-target="<?= isset($emailTemplates) && is_array($emailTemplates) ? count($emailTemplates) : 0 ?>">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->
                
                <?php
                $data = [];
                $LicensesModel = model('LicensesModel');
                $allLicenses = $LicensesModel->where('owner_id', $userData->id)->where('owner_id', $userData->id)->orderBy('id', 'DESC')->findAll();

                $productCount = [];
                $productList = $sideBarMenu['products'];

                foreach($productList as $product) {
                    $productCount[$product] = count($LicensesModel->where('owner_id', $userData->id)->like('product_ref', $product)->orderBy('id', 'DESC')->findAll());
                }                                    
                ?>
                <div class="col">
                    <a href="<?= base_url('license-manager/list-all') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-success text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-key-skeleton fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-0"><?= lang('Pages.Total_License') ?></h6>
                                <p class="fs-4 text-success fw-bold mb-0"><span class="counter-value" data-target="<?= isset($allLicenses) && is_array($allLicenses) ? count($allLicenses) : 0 ?>">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->

            </div><!--end row-->

            <!-- Other count -->
            <?php
            // get licenses created this week
            $licensesThisWeek = $LicensesModel->where('owner_id', $userData->id)
                                            ->where('date_created >=',  $startOfWeek)
                                            ->where('date_created <=', $endOfWeek)
                                            ->orderBy('id', 'DESC')
                                            ->findAll();

            // get licenses created this month
            $licensesThisMonth = $LicensesModel->where('owner_id', $userData->id)
                                        ->where('MONTH(date_created)', $currentMonth)
                                        ->where('YEAR(date_created)', $currentYear)
                                        ->orderBy('id', 'DESC')
                                        ->findAll();

            // sum of total reminders sent
            $sumReminderSent = $LicensesModel->where('owner_id', $userData->id)
                                        ->selectSum('reminder_sent')
                                        ->get()
                                        ->getRow()
                                        ->reminder_sent;

            // get expiring license as set in the number of ours to send reminder in settings                                
            $where = "license_status = 'active' 
                        AND date_expiry IS NOT NULL 
                        AND (license_type = 'subscription' OR license_type = 'trial')";
            
            $expiringLicenses = $LicensesModel->where('owner_id', $userData->id)
                                                ->where($where)
                                                ->where('date_expiry >=', $currentDate)
                                                ->where('date_expiry <=', $threeDaysLater)
                                                ->orderBy('id', 'DESC')
                                                ->findAll();
            ?>                                 
            <div class="row row-cols-xl-4 row-cols-md-2 row-cols-1 g-4 mt-4">
                <div class="col">
                    <a href="<?= base_url('license-manager/list-all') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-info text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-list-ol fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-info mb-0"><?= lang('Pages.Licenses_This_Week') ?></h6>
                                <p class="fs-4 text-info fw-bold mb-0"><span class="counter-value" data-target="<?= isset($licensesThisWeek) && is_array($licensesThisWeek) ? count($licensesThisWeek) : 0 ?>" id="licensesThisWeek-counter">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->
                
                <div class="col">
                    <a href="<?= base_url('license-manager/list-all') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-success text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-list-ol-alt fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-0"><?= lang('Pages.Licenses_This_Month') ?></h6>
                                <p class="fs-4 text-success fw-bold mb-0"><span class="counter-value" data-target="<?= isset($licensesThisMonth ) && is_array($licensesThisMonth ) ? count($licensesThisMonth ) : 0 ?>" id="licensesThisMonth-counter">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->
                
                <div class="col">
                    <a href="<?= base_url('license-manager/list-all?type=trial') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-warning text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-envelope-check fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-warning mb-0"><?= lang('Pages.Reminders_Sent') ?></h6>
                                <p class="fs-4 text-warning fw-bold mb-0"><span class="counter-value" data-target="<?= isset($sumReminderSent) ? $sumReminderSent : 0 ?>" id="sumReminderSent-counter">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->
                
                <div class="col">
                    <a href="<?= base_url('license-manager/list-all?type=trial') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon text-center rounded-circle bg-danger text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                                <i class="uil uil-clock fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-danger mb-0"><?= lang('Pages.Expiring_Licenses') ?></h6>
                                <p class="fs-4 text-danger fw-bold mb-0"><span class="counter-value" data-target="<?= isset($expiringLicenses) && is_array($expiringLicenses) ? count($expiringLicenses) : 0 ?>" id="expiringLicenses-counter">0</span></p>
                            </div>
                        </div>
                    </a>
                </div><!--end col-->
            </div>
        </div>
        <!-- End Main Infos -->

        <!-- License Status count -->
        <div class="row row-cols-xl-4 row-cols-md-2 row-cols-1 g-4 mt-4">
            
            <div class="col">
                <a href="<?= base_url('license-manager/list-all?status=active') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-success text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-user-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Active_License') ?></h6>
                            <p class="fs-4 text-success fw-bold mb-0"><span class="counter-value" data-target="" id="activeLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

            <div class="col">
                <a href="<?= base_url('license-manager/list-all?status=pending') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-warning text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-user-exclamation fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Pending_License') ?></h6>
                            <p class="fs-4 text-warning fw-bold mb-0"><span class="counter-value" data-target="" id="pendingLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

            <div class="col">
                <a href="<?= base_url('license-manager/list-all?status=blocked') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-secondary text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-user-times fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Blocked_License') ?></h6>
                            <p class="fs-4 text-secondary fw-bold mb-0"><span class="counter-value" data-target="" id="blockedLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->
            
            <div class="col">
                <a href="<?= base_url('license-manager/list-all?status=expired') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-danger text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-calendar-alt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Expired_License') ?></h6>
                            <p class="fs-4 text-danger fw-bold mb-0"><span class="counter-value" data-target="" id="expiredLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->                            

        </div><!--end row-->

        <!-- License Type count -->
        <div class="row row-cols-xl-4 row-cols-md-2 row-cols-1 g-4 mt-4">

            <div class="col">
                <a href="<?= base_url('license-manager/list-all?type=lifetime') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-primary text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-user-circle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Lifetime_License') ?></h6>
                            <p class="fs-4 text-primary fw-bold mb-0"><span class="counter-value" data-target="" id="lifetimeLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

            <div class="col">
                <a href="<?= base_url('license-manager/list-all?type=trial') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-info text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-exclamation-triangle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Trial_License') ?></h6>
                            <p class="fs-4 text-info fw-bold mb-0"><span class="counter-value" data-target="" id="trialLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

            <div class="col">
                <a href="<?= base_url('license-manager/list-all?type=subscription') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-success text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-clock fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Subscription_License') ?></h6>
                            <p class="fs-4 text-success fw-bold mb-0"><span class="counter-value" data-target="" id="subscriptionLicense-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

            <div class="col">
                <a href="<?= base_url('license-manager/activity-logs') ?>" class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon text-center rounded-circle bg-secondary text-white me-3" style="width: 48px; height: 48px; line-height: 48px;">
                            <i class="uil uil-pen fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= lang('Pages.Total_Logs_Saved') ?></h6>
                            <p class="fs-4 text-secondary fw-bold mb-0"><span class="counter-value" data-target="" id="licenseLogs-counter">0</span></p>
                        </div>
                    </div>
                </a>
            </div><!--end col-->

        </div><!--end row-->                           
        
        <div class="row g-4 mt-4">
            
            <!-- Donut chart by license status -->
            <div class="col-xl-4">
                <div class="card rounded shadow-sm border-0 h-100">
                    <div class="card-body p-0">
                        <h5 class="card-title p-3"><?= lang('Pages.License_by_Status') ?></h5>
                        <div id="license-status-chart" style="min-height: 365px;"></div>
                    </div>
                </div>
            </div><!--end col-->

            <div class="col-xl-4">
                <div class="card rounded shadow-sm border-0 h-100">
                    <div class="card-body p-0">
                        <h5 class="card-title p-3"><?= lang('Pages.License_by_Type') ?></h5>
                        <div id="license-type-chart" style="min-height: 365px;"></div>
                    </div>
                </div>
            </div><!--end col-->                         

            <!-- Donut chart by product -->
            <div class="col-xl-4">
                <div class="card rounded shadow-sm border-0 h-100">
                    <div class="card-body p-0">
                        <h5 class="card-title p-3"><?= lang('Pages.Products_by_License') ?></h5>
                        <div id="top-product-chart" style="min-height: 365px;"></div>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

        <div class="row g-4 mt-4">
            <!-- Recent licenses issued -->
            <div class="col-xl-12">
                <div class="card border-0">
                    <div class="d-flex justify-content-between p-3 shadow rounded-top">
                        <h6 class="fw-bold mb-0" id="tableTitle"><?= lang('Pages.Recent_Licenses') ?></h6>
                    </div>
                    <div class="table-responsive shadow rounded-bottom" data-simplebar style="height: 545px;">
                        <table class="table table-center table-striped bg-white mb-0">
                            <thead class="text-center">
                                <tr>
                                    <!-- <th class="border-bottom p-3"><?= lang('Pages.ID') ?></th> -->
                                    <th class="border-bottom p-3" style="min-width: 220px;"><?= lang('Pages.Key') ?></th>
                                    <th class="text-center border-bottom p-3"><?= lang('Pages.Status') ?></th>
                                    <th class="text-center border-bottom p-3"><?= lang('Pages.Type') ?></th>
                                    <th class="text-center border-bottom p-3"><?= lang('Pages.Email') ?></th>
                                    <th class="text-center border-bottom p-3"><?= lang('Pages.Product') ?></th>
                                </tr>
                            </thead>
                            <tbody>

                                <!-- All Data Start -->
                                <?php 
                                $licenseCounts = $LicensesModel->where('owner_id', $userData->id)->orderBy('id', 'DESC')->limit(10)->findAll();
                                foreach($licenseCounts as $license) {
                                    if ( $license['license_status'] === 'active') {
                                        $license_status = '<span class="badge bg-soft-success rounded px-3 py-1">' . lang('Pages.Active') . '</span>';                                    
                                    }
                                    else if ( $license['license_status'] === 'pending') {
                                        $license_status = '<span class="badge bg-soft-warning rounded px-3 py-1">' . lang('Pages.Pending') . '</span>';
                                    }
                                    else if ( $license['license_status'] === 'blocked') {
                                        $license_status = '<span class="badge bg-soft-dark text-dark rounded px-3 py-1">' . lang('Pages.Blocked') . '</span>';
                                    }
                                    else {
                                        $license_status = '<span class="badge bg-soft-danger rounded px-3 py-1">' . lang('Pages.Expired') . '</span>';
                                    }
                                ?>
                                    <tr class="licenseData-all_data">
                                        <!-- <td class="p-3"><?= $license['id'] ?></td> -->
                                        <th class="text-center p-3" onclick="window.location.href='<?= base_url('license-manager/list-all?s=' . $license['license_key']) ?>';" style="cursor: pointer;">
                                            <?= strlen($license['license_key']) > 10 ? substr($license['license_key'], 0, 10) . '...' : $license['license_key'] ?>
                                        </th>
                                        <td class="text-center p-3"><?= $license_status ?></td>
                                        <td class="text-center p-3"><?= lang('Pages.' . ucwords($license['license_type'])) ?></td>
                                        <td class="text-center p-3"><?= $license['email'] ?></td>
                                        <td class="text-center p-3"><?= productBasename($license['product_ref'], $userData->id) ?></td>
                                    </tr>
                                <?php } ?>
                                <!-- End -->

                                <!-- Current Year Start -->
                                <?php
                                $licenseList = [];

                                $licenseCounts = $LicensesModel->where('owner_id', $userData->id)
                                                                ->orderBy('id', 'DESC')
                                                                ->where('YEAR(date_created)', $currentYear)
                                                                ->findAll();

                                // Add all license keys to the array
                                foreach($licenseCounts as $license) {
                                    $licenseList[$currentYear][] = $license['license_key'];
                                }                                                        

                                $totalRows = count($licenseCounts); // Get the total number of rows

                                // Only display the number of licenses available, up to a maximum of 10
                                $rowsToShow = min($totalRows, 10);

                                for ($i = 0; $i < $rowsToShow; $i++) {
                                    $license = $licenseCounts[$i];

                                    // Determine license status badge
                                    if ($license['license_status'] === 'active') {
                                        $license_status = '<span class="badge bg-soft-success rounded px-3 py-1">' . lang('Pages.Active') . '</span>';
                                    } elseif ($license['license_status'] === 'pending') {
                                        $license_status = '<span class="badge bg-soft-warning rounded px-3 py-1">' . lang('Pages.Pending') . '</span>';
                                    } elseif ($license['license_status'] === 'blocked') {
                                        $license_status = '<span class="badge bg-soft-dark text-dark rounded px-3 py-1">' . lang('Pages.Blocked') . '</span>';
                                    } else {
                                        $license_status = '<span class="badge bg-soft-danger rounded px-3 py-1">' . lang('Pages.Expired') . '</span>';
                                    }
                                    ?>
                                    <tr class="licenseData-<?= $currentYear ?>" style="display:none">
                                        <!-- <td class="p-3"><?= $license['id'] ?></td> -->
                                        <th class="text-center p-3" onclick="window.location.href='<?= base_url('license-manager/list-all?s=' . $license['license_key']) ?>';" style="cursor: pointer;">
                                            <?= strlen($license['license_key']) > 10 ? substr($license['license_key'], 0, 10) . '...' : $license['license_key'] ?>
                                        </th>
                                        <td class="text-center p-3"><?= $license_status ?></td>
                                        <td class="text-center p-3"><?= lang('Pages.' . ucwords($license['license_type'])) ?></td>
                                        <td class="text-center p-3"><?= $license['email'] ?></td>
                                        <td class="text-end p-3"><?= productBasename($license['product_ref'], $userData->id) ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <!-- End -->

                                <!-- Previous Year Start -->
                                <?php 
                                $previousYear = $currentYear - 1;
                                $licenseCounts = $LicensesModel->where('owner_id', $userData->id)
                                                                ->orderBy('id', 'DESC')
                                                                ->where('YEAR(date_created)', $previousYear)
                                                                ->findAll();

                                // Add all license keys to the array
                                foreach($licenseCounts as $license) {
                                    $licenseList[$previousYear][] = $license['license_key'];
                                }    

                                $totalRows = count($licenseCounts); // Get the total number of rows

                                // Only display the number of licenses available, up to a maximum of 10
                                $rowsToShow = min($totalRows, 10);

                                for ($i = 0; $i < $rowsToShow; $i++) {
                                    $license = $licenseCounts[$i];

                                    // Determine license status badge
                                    if ($license['license_status'] === 'active') {
                                        $license_status = '<span class="badge bg-soft-success rounded px-3 py-1">' . lang('Pages.Active') . '</span>';
                                    } elseif ($license['license_status'] === 'pending') {
                                        $license_status = '<span class="badge bg-soft-warning rounded px-3 py-1">' . lang('Pages.Pending') . '</span>';
                                    } elseif ($license['license_status'] === 'blocked') {
                                        $license_status = '<span class="badge bg-soft-dark text-dark rounded px-3 py-1">' . lang('Pages.Blocked') . '</span>';
                                    } else {
                                        $license_status = '<span class="badge bg-soft-danger rounded px-3 py-1">' . lang('Pages.Expired') . '</span>';
                                    }
                                    ?>
                                    <tr class="licenseData-<?= $previousYear ?>" style="display:none">
                                        <!-- <td class="p-3"><?= $license['id'] ?></td> -->
                                        <th class="text-center p-3" onclick="window.location.href='<?= base_url('license-manager/list-all?s=' . $license['license_key']) ?>';" style="cursor: pointer;">
                                            <?= strlen($license['license_key']) > 10 ? substr($license['license_key'], 0, 10) . '...' : $license['license_key'] ?>
                                        </th>
                                        <td class="text-center p-3"><?= $license_status ?></td>
                                        <td class="text-center p-3"><?= lang('Pages.' . ucwords($license['license_type'])) ?></td>
                                        <td class="text-center p-3"><?= $license['email'] ?></td>
                                        <td class="text-end p-3"><?= productBasename($license['product_ref'], $userData->id) ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <!-- End -->

                                <!--By Month start -->
                                <?php
                                foreach ($previousMonthsReversed as $MonthYear => $byMonth) {
                                    // Break down the key $MonthYear to get the month
                                    $parts = explode('-', $MonthYear);
                                    $month = $parts[0]; // Extract the month part

                                    // Map the month name to its number
                                    $monthNumber = date('m', strtotime($month));

                                    // Get all licenses for the specific month and year
                                    $licenseCounts = $LicensesModel->where('owner_id', $userData->id)
                                                                    ->orderBy('id', 'DESC')
                                                                    ->where('MONTH(date_created)', $monthNumber)
                                                                    ->where('YEAR(date_created)', $parts[1])
                                                                    ->findAll();

                                    // Add all license keys to the array
                                    foreach($licenseCounts as $license) {
                                        $licenseList[$MonthYear][] = $license['license_key'];
                                    }

                                    // Get the total number of rows
                                    $totalRows = count($licenseCounts);

                                    // Only display the number of licenses available, up to a maximum of 10
                                    $rowsToShow = min($totalRows, 10);

                                    for ($i = 0; $i < $rowsToShow; $i++) {
                                        $license = $licenseCounts[$i];

                                        // Determine license status badge
                                        if ($license['license_status'] === 'active') {
                                            $license_status = '<span class="badge bg-soft-success rounded px-3 py-1">' . lang('Pages.Active') . '</span>';
                                        } elseif ($license['license_status'] === 'pending') {
                                            $license_status = '<span class="badge bg-soft-warning rounded px-3 py-1">' . lang('Pages.Pending') . '</span>';
                                        } elseif ($license['license_status'] === 'blocked') {
                                            $license_status = '<span class="badge bg-soft-dark text-dark rounded px-3 py-1">' . lang('Pages.Blocked') . '</span>';
                                        } else {
                                            $license_status = '<span class="badge bg-soft-danger rounded px-3 py-1">' . lang('Pages.Expired') . '</span>';
                                        }
                                        ?>
                                        <tr class="licenseData-<?= $MonthYear ?>" style="display:none">
                                            <!-- <td class="p-3"><?= $license['id'] ?></td> -->
                                            <th class="text-center p-3" onclick="window.location.href='<?= base_url('license-manager/list-all?s=' . $license['license_key']) ?>';" style="cursor: pointer;">
                                                <?= strlen($license['license_key']) > 10 ? substr($license['license_key'], 0, 10) . '...' : $license['license_key'] ?>
                                            </th>
                                            <td class="text-center p-3"><?= $license_status ?></td>
                                            <td class="text-center p-3"><?= lang('Pages.' . ucwords($license['license_type'])) ?></td>
                                            <td class="text-center p-3"><?= $license['email'] ?></td>
                                            <td class="text-end p-3"><?= productBasename($license['product_ref'], $userData->id) ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                                <!--No Record -->
                                <div class="licenseData-no_record card rounded shadow border-0" style="display:none">
                                    <div class="alert alert-danger mt-3 mx-auto" role="alert"><?= lang('Pages.DT_infoEmpty') ?></div>
                                </div>
                                <!-- End -->
                                
                            </tbody>

                        </table>                                         
                    </div>
                </div>
            </div><!--end col-->

        </div><!--end row-->
    <?php } ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script type="text/javascript">
        <?php if($myConfig['licenseManagerOnUse'] !== 'slm') { ?>
            
            /**
             * Start: Prepare Data
             *  */

            var defaultTypeCounts = [];
            var defaultStatusCounts = [];            
            var defaultProductCounts = [];   
            
            <?php
            $typeLabels = [
                lang('Pages.Lifetime'),
                lang('Pages.Trial'),
                lang('Pages.Subscription')
            ];

            $typeColors = [
                'lifetime' => '#17a2b8',
                'trial' => '#6c757d',
                'subscription' => '#6610f2'
            ];

            $statusLabels = [
                lang('Pages.Active'),
                lang('Pages.Expired'),
                lang('Pages.Pending'),
                lang('Pages.Blocked')
            ];

            $productLabels = json_encode($sideBarMenu['products']);

            $statusColors = [
                'active' => '#2eca8b',
                'expired' => '#e43f52',
                'pending' => '#f17425',
                'blocked' => '#adb5bd'
            ];
                
            $lifetimeTypeData = [];
            $trialTypeData = [];
            $subscriptionTypeData = [];

            $activeStatusData = [];
            $expiredStatusData = [];
            $pendingStatusData = [];
			$blockedStatusData = [];

            $counter = 1;
            $arrayKeyCount = count($previousMonthsReversed);            
            foreach ($previousMonthsReversed as $MonthYear => $byMonth) {
                // Break down the key $MonthYear to get the month
                $parts = explode('-', $MonthYear);
                $month = $parts[0]; // Extract the month part

                // Map the month name to its number
                $monthNumber = date('m', strtotime($month));

                $lifetimeTypeData[$MonthYear] = count($LicensesModel->where('license_type', 'lifetime')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());

                $trialTypeData[$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'trial')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());

                $subscriptionTypeData[$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'subscription')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());

                    $activeStatusData[$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'active')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());

                $expiredStatusData[$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'expired')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());

                $pendingStatusData[$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'pending')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());
					
				$blockedStatusData[$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'blocked')
                    ->where('MONTH(date_created)', $monthNumber)
                    ->where('YEAR(date_created)', $parts[1])
                    ->findAll());

                $counter++;
            }

            // Current Year
            $lifetimeTypeData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'lifetime')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());

            $trialTypeData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'trial')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());

            $subscriptionTypeData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'subscription')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());

                $activeStatusData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'active')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());

            $expiredStatusData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'expired')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());

            $pendingStatusData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'pending')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());
				
			$blockedStatusData[$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'blocked')
                ->where('YEAR(date_created)', $currentYear)
                ->findAll());                

            // Previous Year    
            $previousYear = $currentYear - 1;
            $lifetimeTypeData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'lifetime')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());

            $trialTypeData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'trial')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());

            $subscriptionTypeData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'subscription')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());

            $activeStatusData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'active')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());

            $expiredStatusData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'expired')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());

            $pendingStatusData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'pending')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());
				
			$blockedStatusData[$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'blocked')
                ->where('YEAR(date_created)', $previousYear)
                ->findAll());                

            // All data
            $lifetimeTypeData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'lifetime')
                ->findAll());

            $trialTypeData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'trial')
                ->findAll());

            $subscriptionTypeData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_type', 'subscription')
                ->findAll());

            $activeStatusData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'active')
                ->findAll());

            $expiredStatusData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'expired')
                ->findAll());

            $pendingStatusData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'pending')
                ->findAll());
				
			$blockedStatusData['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->where('license_status', 'blocked')
                ->findAll());

            // Default data
            echo "defaultTypeCounts = [" . $lifetimeTypeData['all_data'] . ", " . $trialTypeData['all_data'] . ", " . $subscriptionTypeData['all_data'] . "];
            defaultStatusCounts = [" . $activeStatusData['all_data'] . ", " . $expiredStatusData['all_data'] . ", " . $pendingStatusData['all_data'] . ", " . $blockedStatusData['all_data'] . "];\n";                

            // initial active license counter
            echo "\n            $('#activeLicense-counter').attr('data-target', defaultStatusCounts[0]);\n";

            // initial expired license counter
            echo "            $('#expiredLicense-counter').attr('data-target', defaultStatusCounts[1]);\n";
            
            // initial pending license counter
            echo "            $('#pendingLicense-counter').attr('data-target', defaultStatusCounts[2]);\n";
            
            // initial blocked license counter
            echo "            $('#blockedLicense-counter').attr('data-target', defaultStatusCounts[3]);\n";
            
            // initial lifetime license counter
            echo "            $('#lifetimeLicense-counter').attr('data-target', defaultTypeCounts[0]);\n";

            // initial trial license counter
            echo "            $('#trialLicense-counter').attr('data-target', defaultTypeCounts[1]);\n";
            
            // initial subscription license counter
            echo "            $('#subscriptionLicense-counter').attr('data-target', defaultTypeCounts[2]);\n";

            // Product counts
            $productCounts = [];
            
            // Initialize product counts array
            foreach ($productList as $product) {
                $productCounts[$product] = [];
            }
            
            foreach ($previousMonthsReversed as $MonthYear => $byMonth) {
                // Break down the key $MonthYear to get the month
                $parts = explode('-', $MonthYear);
                $month = $parts[0]; // Extract the month part
            
                // Map the month name to its number
                $monthNumber = date('m', strtotime($month));
            
                // Iterate over each product to populate counts for the current month
                foreach ($productList as $product) {
                    $productCounts[$product][$MonthYear] = count($LicensesModel->where('owner_id', $userData->id)->like('product_ref', $product)
                        ->where('MONTH(date_created)', $monthNumber)
                        ->where('YEAR(date_created)', $parts[1])
                        ->findAll());
                }
            }
            
            foreach ($productList as $product) {
                // Current Year
                $productCounts[$product][$currentYear] = count($LicensesModel->where('owner_id', $userData->id)->like('product_ref', $product)
                            ->where('YEAR(date_created)', $currentYear)
                            ->findAll());
                
                // Previous Year
                $productCounts[$product][$previousYear] = count($LicensesModel->where('owner_id', $userData->id)->like('product_ref', $product)
                            ->where('YEAR(date_created)', $previousYear)
                            ->findAll());

                // All Data
                $productCounts[$product]['all_data'] = count($LicensesModel->where('owner_id', $userData->id)->like('product_ref', $product)
                            ->findAll());                
            }
            
            // Initialize an empty array to store default product counts
            $defaultProductCounts = [];

            // Iterate through the product list
            foreach ($productList as $product) {
                // Access the 'all_data' value for each product from the $productCounts array
                // and add it to the $defaultProductCounts array
                $defaultProductCounts[] = $productCounts[$product]['all_data'];
            }

            // Convert the $defaultProductCounts array to a string for JavaScript variable
            $defaultProductCountsString = '[' . implode(', ', $defaultProductCounts) . ']';

            // Echo the JavaScript variable
            echo "            defaultProductCounts = " . $defaultProductCountsString . ";\n\n";

            // Total logs saved data
            $LicenseLogsModel = model('LicenseLogsModel');
            $logCount = [];

            // All Data
            $logCount['all_data'] = $LicenseLogsModel->where('owner_id', $userData->id)->countAllResults();

            // Echo the variable
            echo "            var defaultLogCounts = " . $logCount['all_data'] . ";\n\n";
            echo "            $('#licenseLogs-counter').attr('data-target', defaultLogCounts);" . "\n\n";

            // Current Year
            if(array_key_exists($currentYear, $licenseList)) {
                $logCount[$currentYear] = 0;
                $count = 0;
                foreach($licenseList[$currentYear] as $licenseKey) {
                    $count = $LicenseLogsModel->where('owner_id', $userData->id)
                                                ->where('license_key', $licenseKey)
                                                ->where('YEAR(time)', $currentYear)
                                                ->countAllResults();
                                                            
                    $logCount[$currentYear] = $count + $logCount[$currentYear];
                }
            
            }

            // Previous Year
            if(array_key_exists($previousYear, $licenseList)) {
                $logCount[$previousYear] = 0;
                $count = 0;
                foreach($licenseList[$previousYear] as $licenseKey) {
                    $count = $LicenseLogsModel->where('owner_id', $userData->id)
                                                ->where('license_key', $licenseKey)
                                                ->where('YEAR(time)', $previousYear)
                                                ->countAllResults();

                    $logCount[$previousYear] = $count + $logCount[$previousYear];
                }
            
            }

            // By Month
            foreach ($previousMonthsReversed as $MonthYear => $byMonth) {
                $logCount[$MonthYear] = 0;
                $count = 0;

                if(array_key_exists($MonthYear, $licenseList)) {
                    $parts = explode('-', $MonthYear);
                    $month = $parts[0]; // Extract the month part        
                    $year = $parts[1]; // Extract the year part

                    // Map the month name to its number
                    $monthNumber = date('m', strtotime($month));   

                    foreach($licenseList[$MonthYear] as $licenseKey) {
                        $count = $LicenseLogsModel->where('owner_id', $userData->id)
                                                    ->where('license_key', $licenseKey)
                                                    ->where('MONTH(time)', $monthNumber)
                                                    ->where('YEAR(time)', $year)
                                                    ->countAllResults();

                        $logCount[$MonthYear] = $count + $logCount[$MonthYear];
                    }
                
                }
            }

            /***
             * End: Prepare Data
             */
            ?>

            document.getElementById("dailychart").addEventListener("change", function() {
                var selectedOption = this.value;            

                var typeCounts = [];
                var statusCounts = [];
                var productCounts = [];
                var logCount = 0;
                
                // Get the previously shown rows
                var previouslyShown = $('tbody tr:visible');
                if (previouslyShown.length === 0) {
                    previouslyShown = $('.licenseData-no_record');
                }

                // Get the current rows to show
                var currentRows = $('.licenseData-' + selectedOption);
                if (currentRows.length === 0) {
                    currentRows = $('.licenseData-no_record');
                }

                // Hide the previously shown rows with a fade-out animation
                previouslyShown.hide('slow', function() {
                    // Show the current rows with a fade-in animation
                    currentRows.show('slow');
                });

                // Update counts based on the selected option
                switch (selectedOption) {
                    case "<?= $currentYear ?>":
                        typeCounts = [<?= $lifetimeTypeData[$currentYear] ?>, <?= $trialTypeData[$currentYear] ?>, <?= $subscriptionTypeData[$currentYear] ?>];
                        statusCounts = [<?= $activeStatusData[$currentYear] ?>, <?= $expiredStatusData[$currentYear] ?>, <?= $pendingStatusData[$currentYear] ?>, <?= $blockedStatusData[$currentYear] ?>];
                        <?php
                        $productCount = [];
                        foreach ($productList as $product) {
                            $productCount[] = $productCounts[$product][$currentYear];
                        }
                        $productCountString = '[' . implode(', ', $productCount) . ']';
                        echo 'productCounts = ' . $productCountString . ";\n";
                        if (array_key_exists($currentYear, $logCount)) {
                            echo 'logCount = ' . $logCount[$currentYear] . "\n";
                        }
                        ?>                     
                        break;
                    
                    case "<?= $previousYear ?>":
                        typeCounts = [<?= $lifetimeTypeData[$previousYear] ?>, <?= $trialTypeData[$previousYear] ?>, <?= $subscriptionTypeData[$previousYear] ?>];
                        statusCounts = [<?= $activeStatusData[$previousYear] ?>, <?= $expiredStatusData[$previousYear] ?>, <?= $pendingStatusData[$previousYear] ?>, <?= $blockedStatusData[$previousYear] ?>];
                        <?php
                        $productCount = [];
                        foreach ($productList as $product) {
                            $productCount[] = $productCounts[$product][$previousYear];
                        }
                        $productCountString = '[' . implode(', ', $productCount) . ']';
                        echo 'productCounts = ' . $productCountString . ";\n";
                        if (array_key_exists($previousYear, $logCount)) {
                            echo 'logCount = ' . $logCount[$previousYear] . "\n";
                        }                        
                        ?>
                        break;

                    <?php
                    $arrayKeyCount = count($previousMonthsReversed);
                    $counter = 1;
                    foreach ($previousMonthsReversed as $MonthYear => $byMonth) {
                        echo "\n" . '                    case "' . $MonthYear . '":' . "\n";
                        ?>
                        typeCounts = [<?= $lifetimeTypeData[$MonthYear] ?>, <?= $trialTypeData[$MonthYear] ?>, <?= $subscriptionTypeData[$MonthYear] ?>];
                        statusCounts = [<?= $activeStatusData[$MonthYear] ?>, <?= $expiredStatusData[$MonthYear] ?>, <?= $pendingStatusData[$MonthYear] ?>, <?= $blockedStatusData[$MonthYear] ?>];
                        <?php
                        $productCount = [];
                        foreach ($productList as $product) {
                            $productCount[] = $productCounts[$product][$MonthYear];
                        }
                        $productCountString = '[' . implode(', ', $productCount) . ']';
                        echo 'productCounts = ' . $productCountString . ";\n";
                        echo 'logCount = ' . $logCount[$MonthYear] . "\n";
                        echo '                        break;' . "\n";
                        $counter++;
                    }
                    ?>

                    case "all_data":
                        typeCounts = [ <?= $lifetimeTypeData['all_data'] ?>, <?= $trialTypeData['all_data'] ?>, <?= $subscriptionTypeData['all_data'] ?>];
                        statusCounts = [<?= $activeStatusData['all_data'] ?>, <?= $expiredStatusData['all_data'] ?>, <?= $pendingStatusData['all_data'] ?>, <?= $blockedStatusData['all_data'] ?>];
                        <?php
                        $productCount = [];
                        foreach ($productList as $product) {
                            $productCount[] = $productCounts[$product]['all_data'];
                        }
                        $productCountString = '[' . implode(', ', $productCount) . ']';
                        echo 'productCounts = ' . $productCountString . ";\n";
                        echo 'logCount = ' . $logCount['all_data'] . "\n";
                        ?>
                        break;                       

                    default: //all_data
                        typeCounts = [ <?= $lifetimeTypeData['all_data'] ?>, <?= $trialTypeData['all_data'] ?>, <?= $subscriptionTypeData['all_data'] ?>];
                        statusCounts = [<?= $activeStatusData['all_data'] ?>, <?= $expiredStatusData['all_data'] ?>, <?= $pendingStatusData['all_data'] ?>, <?= $blockedStatusData['all_data'] ?>];
                        <?php
                        $productCount = [];
                        foreach ($productList as $product) {
                            $productCount[] = $productCounts[$product]['all_data'];
                        }
                        $productCountString = '[' . implode(', ', $productCount) . ']';
                        echo 'productCounts = ' . $productCountString . ";\n";
                        echo 'logCount = ' . $logCount['all_data'] . "\n";
                        ?>
                        break;                    
                }

                <?php                
                // Update the counters
                // active license
                echo "$('#activeLicense-counter').attr('data-target', statusCounts[0]);" . "\n";

                // expired license
                echo "                $('#expiredLicense-counter').attr('data-target', statusCounts[1]);" . "\n";
                
                // pending license
                echo "                $('#pendingLicense-counter').attr('data-target', statusCounts[2]);" . "\n";
                
                // blocked license
                echo "                $('#blockedLicense-counter').attr('data-target', statusCounts[3]);" . "\n";
                
                // lifetime license
                echo "                $('#lifetimeLicense-counter').attr('data-target', typeCounts[0]);" . "\n";

                // trial license
                echo "                $('#trialLicense-counter').attr('data-target', typeCounts[1]);" . "\n";
                
                // subscription license
                echo "                $('#subscriptionLicense-counter').attr('data-target', typeCounts[2]);" . "\n";

                // Update License Log count
                echo "                $('#licenseLogs-counter').attr('data-target', logCount);" . "\n";
                
                echo "                runCounter();" . "\n";
                ?>

                // Function to check if all elements in an array are zero
                function isAllZero(arr) {
                    return arr.every(function(value) { return value === 0; });
                }

                // License Type
                try {
                    if (isAllZero(typeCounts)) {
                        typeCounts = [];
                    }

                    licenseTypeChart.updateSeries(typeCounts);
                } catch (error) {
                    console.error("Error rendering license type chart:", error);
                }

                // License Status
                try {
                    if (isAllZero(statusCounts)) {
                        statusCounts = [];
                    }

                    licenseStatusChart.updateSeries(statusCounts);
                } catch (error) {
                    console.error("Error rendering license status chart:", error);
                }

                // Product
                try {
                    if (isAllZero(productCounts)) {
                        productCounts = [];
                    }
                    productChart.updateSeries(productCounts);
                } catch (error) {
                    console.error("Error rendering product chart:", error);
                }
            });

            //=====================================//
            /*/*               Charts              */
            //=====================================//

            /**
             * License by Type chart
             *  */ 
            try {
                var typeChartOptions = {
                    chart: {
                        height: 320,
                        type: 'donut',
                    },
                    series: defaultTypeCounts,
                    noData: {
                        text: '<?= lang('Pages.DT_infoEmpty') ?>',
                        align: 'center',
                        verticalAlign: 'middle',
                        offsetX: 0,
                        offsetY: 0,
                        style: {
                            color: '#e43f52',
                            fontSize: '14px',
                        }
                    },                    
                    labels: <?= json_encode($typeLabels) ?>,
                    legend: {
                        show: true,
                        position: 'bottom',
                        offsetY: 0,
                    },
                    dataLabels: {
                        enabled: true,
                        dropShadow: {
                            enabled: false,
                        }
                    },
                    colors: <?= json_encode(array_values($typeColors)) ?>,
                    stroke: {
                        show: true,
                        colors: ['transparent'],
                    },
                    theme: {
                        monochrome: {
                            enabled: false,
                            color: '#2f55d4',
                        }
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart: {
                                height: 400,
                            },
                        }
                    }]
                }
                var licenseTypeChart  = new ApexCharts(document.querySelector("#license-type-chart"), typeChartOptions);
                licenseTypeChart.render();
            } catch (error) {
                console.error("Error rendering initial chart:", error);
            }

            /**
             * License by Status chart
             *  */  
            try {
                var statusChartOptions = {
                    chart: {
                        height: 320,
                        type: 'donut',
                    },
                    series: defaultStatusCounts,
                    noData: {
                        text: '<?= lang('Pages.DT_infoEmpty') ?>',
                        align: 'center',
                        verticalAlign: 'middle',
                        offsetX: 0,
                        offsetY: 0,
                        style: {
                            color: '#e43f52',
                            fontSize: '14px',
                        }
                    },
                    labels: <?= json_encode($statusLabels) ?>,
                    legend: {
                        show: true,
                        position: 'bottom',
                        offsetY: 0,
                    },
                    dataLabels: {
                        enabled: true,
                        dropShadow: {
                            enabled: false,
                        }
                    },
                    colors: <?= json_encode(array_values($statusColors)) ?>,
                    stroke: {
                        show: true,
                        colors: ['transparent'],
                    },
                    theme: {
                        monochrome: {
                            enabled: false,
                            color: '#2f55d4',
                        }
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart: {
                                height: 400,
                            },
                        }
                    }]
                }
                var licenseStatusChart = new ApexCharts(document.querySelector("#license-status-chart"), statusChartOptions);
                licenseStatusChart.render();
            } catch (error) {
                console.error("Error rendering initial chart:", error);
            }

            /**
             * Products By License chart
             *  */ 
            try {
                var productChartOptions = {
                    chart: {
                        height: 320,
                        type: 'donut',
                    },
                    series: defaultProductCounts,
                    noData: {
                        text: '<?= lang('Pages.DT_infoEmpty') ?>',
                        align: 'center',
                        verticalAlign: 'middle',
                        offsetX: 0,
                        offsetY: 0,
                        style: {
                            color: '#e43f52',
                            fontSize: '14px',
                        }
                    },                    
                    labels: <?= $productLabels ?>,
                    legend: {
                        show: true,
                        position: 'bottom',
                        offsetY: 0,
                    },
                    dataLabels: {
                        enabled: true,
                        dropShadow: {
                            enabled: false,
                        }
                    },
                    colors: '',
                    stroke: {
                        show: true,
                        colors: ['transparent'],
                    },
                    theme: {
                        monochrome: {
                            enabled: true,
                            color: '#2f55d4',
                        }
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart: {
                                height: 400,
                            },
                        }
                    }]
                }
                var productChart  = new ApexCharts(document.querySelector("#top-product-chart"), productChartOptions);
                productChart.render();
            } catch (error) {
                console.error("Error rendering initial chart:", error);
            }
        <?php } ?>

        runCounter();
    </script>    
<?= $this->endSection() ?>
