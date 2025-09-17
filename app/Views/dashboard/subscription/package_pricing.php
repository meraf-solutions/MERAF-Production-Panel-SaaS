        <!-- Header Section -->
        <div class="text-center my-5 mb-3">
            <h1><?= lang('Pages.Choose_Your_Plan') ?></h1>
            <p class="lead"><?= lang('Pages.Select_the_package_that_best_suits_your_needs') ?></p>
        </div>

        <!-- Pricing Toggle -->
        <div class="text-center mb-3">
            <div class="btn-group">
                <?php foreach ($billingDuration as $duration): ?>
                    <button type="button" class="btn btn-outline-primary <?= $duration === $defaultDuration ? 'active' : '' ?>" data-billing="<?= $duration ?>">
                        <?php
                        if ($duration === 'lifetime') {
                            echo lang('Pages.Lifetime');
                        } else {
                            echo lang('Pages.' . ucfirst($duration) . 's');
                        }
                        ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Packages Grid -->
        <?php foreach ($groupedPackages as $duration => $durationPackages): ?>
        <div class="package-group mt-4" data-duration="<?= $duration ?>" style="display: <?= $duration === $defaultDuration ? 'block' : 'none' ?>;">
            <div class="row">
                <?php foreach ($durationPackages as $package): $package['id'] = (int)$package['id'] ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?= $package['highlight'] === 'on' ? 'border-primary' : '' ?>">
                        <?php if($package['highlight'] === 'on') : ?>
                            <div class="ribbon ribbon-right ribbon-warning overflow-hidden"><span class="text-center d-block shadow small h6"><?= lang('Pages.Popular') ?></span></div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h3 class="card-title text-center border-bottom"><?= esc($package['package_name']) ?></h3>
                            <div class="text-center mb-4">
                                <h2 class="pricing-value">
                                    <?php if($package['price'] !== '0.00') : ?>
                                        <?= $myConfig['packageCurrency'] ?> <?= number_format($package['price'], 2) ?>
                                    <?php else : ?>
                                        <?= lang('Pages.Free') ?>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        / <?= $package['validity'] != 1 ? esc($package['validity']) . ' ' : '' ?>
                                        <?php
                                        if ($package['validity_duration'] === 'lifetime') {
                                            echo lang('Pages.Lifetime');
                                        } else {
                                            echo lang('Pages.' . ($package['validity'] == 1 ? ucfirst($package['validity_duration']) : ucfirst($package['validity_duration']) . 's'));
                                        }
                                        ?>
                                    </small>
                                </h2>
                            </div>

                            <?php if ($modules = json_decode($package['package_modules'], true)): ?>
                            <ul class="list-unstyled mb-0 ps-0">
                                <?php foreach ($modules as $module => $features): ?>
                                <li class="h6 text-muted mb-2 mt-2">
                                    <strong><?= lang('Pages.' . $module) ?></strong>
                                    <ul class="list-unstyled mb-0 ps-0">
                                    <?php
                                    foreach ($features as $featureName => $value): 
                                        $measurementUnit = $packageModules[array_search($featureName, array_column($packageModules, 'module_name'))]['measurement_unit'] ?? null;
                                        $measurementUnit = json_decode($measurementUnit, true);
                                    ?>  
                                        <li class="h6 text-muted mb-0 ps-2">
                                            <?php if ($value['enabled'] === 'true'): ?>
                                                <i class="uil uil-check-circle align-middle text-success"></i>
                                            <?php else: ?>
                                                <i class="uil uil-times-circle align-middle text-danger"></i>
                                            <?php endif; ?>
                                            <?= lang('Pages.' . $featureName) ?> <?= $measurementUnit['unit'] === 'Enabled' ? '' : ':' ?>
                                            <?php
                                            $unit = '';
                                            if ($measurementUnit) {
                                                if($measurementUnit['unit'] === 'Enabled') {
                                                    $featureValue = '';
                                                    $unit = '';
                                                }
                                                else {
                                                    $featureValue = $value ?? '';
                                                    $unit = $measurementUnit['unit'] ?? '';
                                                }
                                            }
                                            ?>
                                            <?= $value['value'] !== 'true' && $value['value'] !== 'false' ? $value['value'] : '' ?> <?= $unit ?>
                                        </li>
                                    <?php endforeach; ?>
                                    </ul>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>

                            <?php if ($currentPackage && (int)$currentPackage['package_id'] === $package['id']): ?>
                                <button class="btn w-100 btn-outline-success btn-block" disabled>
                                    <i class="uil uil-check"></i> <?= lang('Pages.Current_Package') ?>
                                </button>
                            <?php elseif ($defaultPackage && (int)$defaultPackage['id'] === $package['id']): ?>
                                <button class="btn w-100 btn-outline-warning btn-block" id=""  data-bs-toggle="modal" data-bs-target="#trialModal">
                                    <?= lang('Pages.Claim_Now') ?>
                                </button>
                            <?php else: ?>
                                <button class="btn w-100 <?= $package['highlight'] === 'on' ? 'btn-primary' : 'btn-outline-primary' ?> btn-block subscribe-btn"
                                    data-package-id="<?= $package['id'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#paymentMethod">
                                    <?= lang('Pages.Subscribe_Now') ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($package['features'])): ?>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <?= esc($package['features']) ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Package Comparison -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><?= lang('Pages.Package_Comparison') ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered features-comparison">
                                <thead>
                                    <tr>
                                        <th><?= lang('Pages.Feature') ?></th>
                                        <?php foreach ($packages as $package): ?>
                                            <th class="text-center <?= $package['highlight'] === 'on' ? 'bg-secondary text-light' : ''?>" data-package-id="<?= $package['id'] ?>" data-duration="<?= $package['validity_duration'] ?>"><?= esc($package['package_name']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get all unique features across packages
                                    $allModules = [];
                                    foreach ($packages as $package) {
                                        $modules = json_decode($package['package_modules'], true) ?? [];
                                        $allModules = array_merge($allModules, array_keys($modules));
                                    }
                                    $allModules = array_unique($allModules);                            
                                    
                                    foreach ($allModules as $module):
                                    ?>
                                    <tr>
                                        <td><?= lang('Pages.' . $module) ?></td>
                                        <?php foreach ($packages as $package): ?>
                                            <td class="text-center <?= $package['highlight'] === 'on' ? 'bg-secondary text-light' : ''?>" data-package-id="<?= $package['id'] ?>" data-duration="<?= $package['validity_duration'] ?>">
                                                <?php
                                                $modules = json_decode($package['package_modules'], true) ?? [];
                                                foreach($modules[$module] as $featureName => $value) :
                                                    $measurementUnit = $packageModules[array_search($featureName, array_column($packageModules, 'module_name'))]['measurement_unit'] ?? null;
                                                    $measurementUnit = json_decode($measurementUnit, true);
                                                    
                                                    if ($value['enabled'] === 'true') {
                                                        echo '<i class="uil uil-check-circle align-middle text-success"></i>';
                                                    } else {
                                                        echo '<i class="uil uil-times-circle align-middle text-danger"></i>';
                                                    }
                                                    echo ' ' . lang('Pages.' . $featureName);
                                                    
                                                    if ($measurementUnit['unit'] !== 'Enabled') {
                                                        echo ': ';
                                                        if ($value['value'] !== 'true' && $value['value'] !== 'false') {
                                                            echo $value['value'] . ' ' . ($measurementUnit['unit'] ?? '');
                                                        }
                                                    }
                                                    echo '<br>';
                                                endforeach;
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>