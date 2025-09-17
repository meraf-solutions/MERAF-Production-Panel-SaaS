<?php
    $request = \Config\Services::request();
    $searchLicenseKey = $request->getGet('s');
?>

<div class="row justify-content-center">
    <div class="col-lg-12 col-xl-10 mt-4 mb-3">
        <div class="card rounded shadow p-4 border-0">
            <h4 class="mb-3"><?= lang('Pages.Retrieve_License_Details') ?></h4>
            <form novalidate id="search-license-form">
                <input type="hidden" id="hash" name="hash" value="<?= $hash ?>">
                <div class="row g-3">
                    <div class="col-sm-7">
                        <label for="licenseInput" class="form-label"><?= lang('Pages.License_Key') ?></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light text-muted border"><i class="uil uil-key-skeleton align-middle"></i></span>
                            <input type="text" class="form-control" id="licenseInput" name="licenseInput" placeholder="<?= lang('Pages.Enter_license_key') ?>" value="<?= isset($searchLicenseKey) ? $searchLicenseKey : '' ?>" required>
                            <div class="invalid-feedback"><?= lang('Pages.A_license_key_is_required') ?></div>
                        </div>
                    </div>
                    <div class="col-sm-5 mb-3">
                        <label for="captcha" class="form-label"><?= lang('Pages.Prove_youre_not_a_robot') ?></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light text-muted border"><span id="firstNumber"><?= $firstNumber ?></span>+<span id="secondNumber"><?= $secondNumber ?></span>=</span>
                            <input type="number" class="form-control" id="captcha" name="captcha" required>
                            <button class="btn btn-outline-secondary" id="search-license-button"><i class="uil uil-search"></i> <?= lang('Pages.Search') ?></button>
                            <div class="invalid-feedback"><?= lang('Pages.Please_solve_the_math_problem') ?></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div><!--end col-->

    <div class="col-md-7 col-lg-10" id="no_registered_domain_device" style="display:none">
        <div class="card rounded shadow p-4 border-0">
            <div class="row g-3 mt-1">
                <div class="alert bg-soft-danger fade show text-center" role="alert"><?= lang('Pages.No_registered_domain_or_device_for_this_license_key') ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-7 col-lg-10" id="select-domain-device-wrapper" style="display:none">
        <div class="card rounded shadow p-4 border-0">                                                                        
            <div class="row g-3">                                        

                <div class="table-responsive rounded" id="domain-table" style="display:none">
                    <h4 class="mb-3"><?= lang('Pages.Select_Domain') ?></h4>
                    <table class="table table-striped mb-3">
                        <thead>
                            <tr>
                                <th class="p-3 border-bottom" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="checkAll-domain">
                                    </div>
                                </th>
                                <th class="border-bottom" style="min-width: 200px;"><?= lang('Pages.Domain_Name') ?></th>
                            </tr>
                        </thead>

                        <tbody id="domain-list">

                        </tbody>
                    </table>
                </div>

                <div class="table-responsive rounded" id="device-table" style="display:none">
                    <h4 class="mb-3"><?= lang('Pages.Select_Device') ?></h4>
                    <table class="table table-striped mb-3">
                        <thead>
                            <tr>
                                <th class="p-3 border-bottom" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="checkAll-device">
                                    </div>
                                </th>
                                <th class="border-bottom" style="min-width: 200px;"><?= lang('Pages.Device_Name') ?></th>
                            </tr>
                        </thead>

                        <tbody id="device-list">

                        </tbody>
                    </table>
                </div>
            </div>
            <form novalidate id="remove-domain-device-form">                                        
                <input type="hidden" id="verified-license" name="verified-license" value="">
                <input type="hidden" id="selected-domain" name="selected-domain" value="">
                <input type="hidden" id="selected-device" name="selected-device" value="">
                <div class="col-12 text-center">
                    <button class="mx-auto btn btn-danger" id="delete-domain-device-submit" disabled><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>                                        
                </div>
            </form>
        </div>
    </div><!--end col-->                            
</div>