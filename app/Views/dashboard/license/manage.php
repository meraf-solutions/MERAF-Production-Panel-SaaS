<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('head') ?>
<style>
    #license-list-table th,
    #license-list-table td {
        text-align: center;
    }
    .filter-row { margin-bottom: 15px; }
    .quick-edit-input {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }
    .quick-view-popup {
        position: absolute;
        background-color: white;
        border: 1px solid #ddd;
        padding: 10px;
        z-index: 1000;
        box-shadow: 0 0 3px rgba(60,72,88,.15);
    }
    .selected-row { background-color: #e6f3ff !important; }
    .highlight {
        background-color: yellow;
        font-weight: bold;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <div class="d-flex">
            <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>
        </div>

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
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <?php if($myConfig['licenseManagerOnUse'] !== 'slm') { ?>
        <div class="row">
            <div class="col-12 mt-4">
                <div class="border-0">
                    <button class="mx-auto btn btn-danger mb-3" id="delete-license-submit" disabled aria-label="<?= lang('Pages.Delete_Selected') ?>"><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>
                    <button class="mx-auto btn btn-success mb-3" id="export-csv" aria-label="<?= lang('Pages.Export_CSV') ?>"><i class="uil uil-export"></i> <?= lang('Pages.Export_CSV') ?></button>
                    <button class="mx-auto btn btn-info mb-3" id="refresh-table" aria-label="<?= lang('Pages.Refresh') ?>"><i class="uil uil-refresh"></i> <?= lang('Pages.Refresh') ?></button>
                    <button class="mx-auto btn btn-secondary mb-3" id="reset-filters" aria-label="<?= lang('Pages.Reset_Filters') ?>"><i class="uil uil-times"></i> <?= lang('Pages.Reset_Filters') ?></button>
                    <!-- <select id="bulk-action" class="form-select d-inline-block w-auto mb-3" aria-label="<?= lang('Pages.Bulk_Actions') ?>">
                        <option value=""><?= lang('Pages.Bulk_Actions') ?></option>
                        <option value="active"><?= lang('Pages.Set_Active') ?></option>
                        <option value="pending"><?= lang('Pages.Set_Pending') ?></option>
                        <option value="blocked"><?= lang('Pages.Set_Blocked') ?></option>
                        <option value="expired"><?= lang('Pages.Set_Expired') ?></option>
                    </select>
                    <button class="mx-auto btn btn-secondary mb-3" id="apply-bulk-action" disabled aria-label="<?= lang('Pages.Apply') ?>"><?= lang('Pages.Apply') ?></button> -->
                </div>

                <form novalidate action="javascript:void(0)" id="delete-template-form">
                    <div class="row filter-row">
                        <div class="col-md-3 mb-3">
                            <select id="status-filter" class="form-select" aria-label="<?= lang('Pages.Filter_by_status') ?>">
                                <option value=""><?= lang('Pages.All_Statuses') ?></option>
                                <option value="active"><?= lang('Pages.Active') ?></option>
                                <option value="pending"><?= lang('Pages.Pending') ?></option>
                                <option value="blocked"><?= lang('Pages.Blocked') ?></option>
                                <option value="expired"><?= lang('Pages.Expired') ?></option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select id="type-filter" class="form-select" aria-label="<?= lang('Pages.Filter_by_type') ?>">
                                <option value=""><?= lang('Pages.All_Types') ?></option>
                                <option value="lifetime"><?= lang('Pages.Lifetime') ?></option>
                                <option value="subscription"><?= lang('Pages.Subscription') ?></option>
                                <option value="trial"><?= lang('Pages.Trial') ?></option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" id="search-input" class="form-control" placeholder="<?= lang('Pages.Search') ?>" aria-label="<?= lang('Pages.Search_licenses') ?>">
                        </div>
                        <div class="col-md-3">
                            <button id="apply-filters" class="btn btn-primary" aria-label="<?= lang('Pages.Apply_Filters') ?>"><i class="uil uil-filter"></i> <?= lang('Pages.Apply_Filters') ?></button>
                        </div>
                    </div>
                    <div class="table-responsive shadow rounded p-4">
                        <table id="license-list-table" class="table table-center table-striped bg-white mb-0">
                            <thead>
                                
                            </thead>
                            <tbody id="license-list-tbody">
                                <!-- Initial rows loaded with the view -->

                            </tbody>
                        </table>
                    </div>
                </form>
            </div><!--end col-->
        </div><!--end row-->

    <?php } else { ?>
        <div class="row">
            <div class="col-12 mt-4">
                <div class="card rounded shadow border-0 align-items-center">
                    <div class="col-lg-6 col-md-12 text-center">
                        <div class="alert alert-danger mt-3 d-inline-block" role="alert"><?= lang('Pages.error_manage_license_page_builtin_not_active') ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
    <div class="modal fade" id="rowModal" tabindex="-1" aria-labelledby="rowModal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="rowModal-title"><?= lang('Pages.License_Key') ?>: </h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-pills shadow flex-column flex-sm-row d-md-inline-flex mb-0 p-1 bg-white-color rounded position-relative overflow-hidden col-12 text-center justify-content-center" id="pills-tab" role="tablist">
                        <li class="nav-item m-1">
                            <a class="nav-link mx-auto active rounded" id="licenseDetails-data" data-bs-toggle="pill" href="#licenseDetails" role="tab" aria-controls="licenseDetails" aria-selected="false">
                                <div class="text-center">
                                    <h6 class="mb-0"><?= lang('Pages.License_Details') ?></h6>
                                </div>
                            </a><!--end nav link-->
                        </li><!--end nav item-->
                        
                        <li class="nav-item m-1">
                            <a class="nav-link mx-auto rounded text-center" id="licenseLogs-info" data-bs-toggle="pill" href="#licenseLogs" role="tab" aria-controls="licenseLogs" aria-selected="false">
                                <div class="text-center">
                                    <h6 class="mb-0"><?= lang('Pages.Activity_Log') ?></h6>
                                </div>
                            </a><!--end nav link-->
                        </li><!--end nav item-->

                        <li class="nav-item m-1">
                            <a class="nav-link mx-auto rounded text-center" id="licenseRegisteredDomainDevice-data" data-bs-toggle="pill" href="#licenseRegistrations" role="tab" aria-controls="licenseRegistrations" aria-selected="false">
                                <div class="text-center">
                                    <h6 class="mb-0"><?= lang('Pages.Registered_DomainDevice') ?></h6>
                                </div>
                            </a><!--end nav link-->
                        </li><!--end nav item-->

                        <li class="nav-item m-1">
                            <a class="nav-link mx-auto rounded text-center" id="licenseResendInfo-form" data-bs-toggle="pill" href="#licenseResendInfo" role="tab" aria-controls="licenseResendInfo" aria-selected="false">
                                <div class="text-center">
                                    <h6 class="mb-0"><?= lang('Pages.resend_license_details') ?></h6>
                                </div>
                            </a><!--end nav link-->
                        </li><!--end nav item-->

                    </ul>
                            
                    <div class="tab-content mt-3" id="pills-tabContent">
                        <div class="card border-0 tab-pane fade show active p-4 rounded shadow" id="licenseDetails" role="tabpanel" aria-labelledby="licenseDetails-data">                            
                            <div class="d-flex mb-3">
                                <a href="javascript:void(0)" class="btn btn-info btn-sm me-2" id="edit-license-btn"><i class="uil uil-edit"></i> <?= lang('Pages.Edit') ?></a>
                                <div id="edit-menu" style="display: none;">
                                    <a href="javascript:void(0)" class="btn btn-success btn-sm me-2" id="edit-license-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save') ?></a>
                                    <a href="javascript:void(0)" class="btn btn-danger btn-sm me-2" id="cancel-btn"><i class="uil uil-times"></i> <?= lang('Pages.Cancel') ?></a>
                                </div>
                            </div>
                            <form novalidate action="javascript:void(0)" id="edit-template-form">
                                <input type="hidden" id="id" name="id" value="">
                                <input type="hidden" id="license_key" name="license_key" value="">

                                <div class="table-responsive shadow rounded p-4">
                                    <table class="table table-center table-striped bg-white mb-0" id="license-detail-table">
                                        <tbody>
                                            <tr hidden>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.ID') ?> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Key') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_license_key') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Allowed_Website') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_max_allowed_domains') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Allowed_Device') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_max_allowed_devices') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Status') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_license_status') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Type') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_license_type') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.First_Name') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_first_name') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Last_Name') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_last_name') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Email') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_email') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td> </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Company_Name') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_company_name') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td> </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Reference') ?> / <?= lang('Pages.Transaction_ID') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_txn_id') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Purchase_ID') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_purchase_id_') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Created_on') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_date_created') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Expiration_Date') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_date_expiry') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Reminder_Sent') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_reminder_sent') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Reminder_Sent_Date') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_reminder_sent_date') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Product') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_product_ref') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Billing_Length') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_billing_length') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Billing_Interval') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_billing_interval') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Subscriber_ID') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_subscr_id') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Supported_Until') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_until') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Current_Version') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.info_label_current_ver') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>

                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Manual_Reset_Count') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.Manual_Reset_Count_desc') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>

                                            <tr>
                                                <th style="text-align: left; width: 30%;"> <?= lang('Pages.Item_Reference') ?> <a href="javascript:void" class="text-muted" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.Item_Reference_desc') ?>"><i class="h6 mdi mdi-account-question"></i></a> </th>
                                                <td>  </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div><!-- end licenseDetails -->

                        <div class="card border-0 tab-pane fade p-4 rounded shadow" id="licenseLogs" role="tabpanel" aria-labelledby="licenseLogs-data">                                
                            <div class="d-flex mb-3">
                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm me-2" id="clear-log-license-btn"><i class="uil uil-trash"></i> <?= lang('Pages.Clear_activity_log') ?></a>
                            </div>
                            
                            <div class="table-responsive shadow rounded">
                                <table id="license-log-table" class="table table-center table-striped bg-white mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center border-bottom p-3"><?= lang('Pages.ID') ?></th>
                                            <th class="border-bottom p-3" style="min-width: 180px;"><?= lang('Pages.Action') ?></th>
                                            <th class="border-bottom p-3"><?= lang('Pages.Date_and_Time') ?></th>
                                            <th class="text-center border-bottom p-3"><?= lang('Pages.Source') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="license-log-tbody">
                                        <!-- Initial rows loaded with the view -->
                                    </tbody>
                                </table>
                            </div>

                        </div><!-- end licenseLogs -->

                        <div class="card border-0 tab-pane fade p-4 rounded shadow" id="licenseRegistrations" role="tabpanel" aria-labelledby="licenseRegisteredDomainDevice-data">                                

                            <div class="row text-center" id="no_registered_domain_device" style="display:none">
                                <div class="alert bg-soft-danger fade show text-center mt-3" role="alert"><?= lang('Pages.No_registered_domain_or_device_for_this_license_key') ?></div>
                            </div>

                            <div class="row text-center" id="expired_license_notification" style="display:none">
                                <div class="alert bg-soft-danger fade show text-center mb-0 mt-3" role="alert"><?= lang('Pages.License_is_not_active') ?></div>
                            </div>

                            <form class="mb-3" novalidate id="remove-domain-device-form">
                                <input type="hidden" id="hash" name="hash" value="<?= $hash ?>">
                                <input type="hidden" id="captcha" name="captcha" value="<?= $captcha ?>">
                                <input type="hidden" id="verified-license" name="verified-license" value="">
                                <input type="hidden" id="selected-domain" name="selected-domain" value="">
                                <input type="hidden" id="selected-device" name="selected-device" value="">
                                <button class="btn btn-danger btn-sm me-2" id="delete-domain-device-submit" disabled><i class="uil uil-trash"></i> <?= lang('Pages.Delete_Selected') ?></button>
                            </form>

                            <div class="table-responsive shadow rounded p-3" id="domain-table" style="display:none">
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

                            <div class="table-responsive shadow rounded p-3" id="device-table" style="display:none">
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
                        </div><!-- end licenseRegistrations -->

                        <div class="card border-0 tab-pane fade p-4 rounded shadow" id="licenseResendInfo" role="tabpanel" aria-labelledby="licenseResendInfo-form">                                
                            <form class="" novalidate id="resend-license-form">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="input-group has-validation">
                                            <span class="input-group-text bg-light text-muted border"><i class="uil uil-key-skeleton align-middle"></i></span>
                                            <input type="text" class="form-control" id="licenseInput" name="licenseInput" placeholder="<?= lang('Pages.Enter_the_license_key') ?>" required>
                                            <div class="invalid-feedback"> <?= lang('Pages.A_license_key_is_required') ?> </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="mb-3">
                                            <label class="form-label" for="recipientTextarea"><?= lang('Pages.Recipients') ?><br><span
                                                class="text-info"><?= lang('Pages.one_email_per_line') ?></span></label>
                                            <div class="form-icon position-relative">
                                                <i class="uil uil-at icon-sm icons"></i>
                                                <textarea name="recipientTextarea" id="recipientTextarea" rows="8" class="form-control ps-5" placeholder="<?= lang('Pages.Recipients') ?> :" required></textarea>
                                                <div class="invalid-feedback"> <?= lang('Pages.Valid_email_required') ?> </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>        

                                <div class="col-12 text-center">
                                    <button class="mx-auto btn btn-primary" id="resend-license-submit"><i class="uil uil-envelope-send"></i> <?= lang('Pages.Resend_License_Key_Details') ?></button>
                                </div>
                            </form>
                        </div><!-- end licenseResendInfo -->

                    </div><!-- end tabContent -->
                </div><!-- end modal body -->
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/jquery.mark.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#license-list-table').DataTable({
                "autoWidth": false,
                "width": "100%",
                "processing": false,
                "serverSide": true,
                "ajax": {
                    "url": "<?= base_url('api/license/all/' . $myConfig['Manage_License_SecretKey']) ?>",
                    "type": "GET",
                    "data": function(d) {
                        d.status = $('#status-filter').val();
                        d.type = $('#type-filter').val();
                        d.search = $('#search-input').val();
                    },
                    "beforeSend": function (xhr) {
                        xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        $('#loading-indicator').show();
                    },
                    "complete": function() {
                        $('#loading-indicator').hide();
                    },
                    "dataSrc": function(json) {
                        return json.data || [];
                    }
                },
                "columns": [
                    { "data": null, "title": '<input type="checkbox" id="checkAll" class="form-check-input" aria-label="Select all licenses">' },
                    { "data": "id", "title": '<div class="text-center"><?= lang('Pages.ID') ?></div>', "visible": false },
                    { "data": "license_key", "title": '<?= lang('Pages.Key') ?>', "render": function(data, type, row) {
                        if (type === 'display') {
                            return '<span title="' + data + '">' + data + '</span>';
                        }
                        return data;
                    }},
                    { "data": "max_allowed_domains", "title": '<?= lang('Pages.Allowed_Website') ?>', "className": "text-muted quick-edit", "render": function(data, type, row) {
                        if (type === 'display') {
                            return '<span class="editable" data-field="max_allowed_domains">' + data + '</span>';
                        }
                        return data;
                    }},
                    { "data": "max_allowed_devices", "title": '<?= lang('Pages.Allowed_Device') ?>', "className": "text-muted quick-edit", "render": function(data, type, row) {
                        if (type === 'display') {
                            return '<span class="editable" data-field="max_allowed_devices">' + data + '</span>';
                        }
                        return data;
                    }},
                    { "data": "license_status", "title": '<?= lang('Pages.Status') ?>' },
                    { "data": "license_type", "title": '<?= lang('Pages.Type') ?>' },
                    { "data": "first_name", "title": '<?= lang('Pages.First_Name') ?>', "className": "text-muted" },
                    { "data": "last_name", "title": '<?= lang('Pages.Last_Name') ?>', "className": "text-muted" },
                    { "data": "email", "title": '<div class="text-center"><?= lang('Pages.Email') ?></div>', "render": function(data, type, row) {
                        if (type === 'display') {
                            return '<span title="' + data + '">' + data + '</span>';
                        }
                        return data;
                    }},
                    { "data": "company_name", "title": '<?= lang('Pages.Company_Name') ?>', "className": "text-muted" },
                    { "data": "txn_id", "title": '<?= lang('Pages.Reference') ?> / <?= lang('Pages.Transaction_ID') ?>', "className": "text-muted" },
                    { "data": "purchase_id_", "title": '<?= lang('Pages.Purchase_ID') ?>', "className": "text-muted" },
                    { "data": "date_created", "title": '<?= lang('Pages.Created_on') ?>' },
                    { "data": "date_expiry", "title": '<?= lang('Pages.Expiration_Date') ?>', "className": "text-muted" },
                    { "data": "reminder_sent", "title": '<?= lang('Pages.Reminder_Sent') ?>', "className": "text-muted" },
                    { "data": "reminder_sent_date", "title": '<?= lang('Pages.Reminder_Sent_Date') ?>', "className": "text-muted" },
                    { "data": "product_ref", "title": '<?= lang('Pages.Product') ?>' },
                    { "data": "billing_length", "title": '<?= lang('Pages.Billing_Length') ?>', "className": "text-muted" },
                    { "data": "billing_interval", "title": '<?= lang('Pages.Billing_Interval') ?>', "className": "text-muted" },
                    { "data": "subscr_id", "title": '<?= lang('Pages.Subscriber_ID') ?>', "className": "text-muted" },
                    { "data": "until", "title": '<?= lang('Pages.Supported_Until') ?>', "className": "text-muted" },
                    { "data": "current_ver", "title": '<?= lang('Pages.Current_Version') ?>', "className": "text-muted" },
                    { "data": "manual_reset_count", "title": '<?= lang('Pages.Manual_Reset_Count') ?>', "className": "text-muted" },
                    { "data": "item_reference", "title": '<?= lang('Pages.Item_Reference') ?>', "className": "text-muted" },
                ],
                "columnDefs": [
                    {
                        "targets": 0,
                        "orderable": false,
                        "render": function (data, type, row, meta) {
                            return "<input type='checkbox' id='" + row.id + "' class='form-check-input license-checkbox' aria-label='Select license'>";
                        }
                    },
                    {
                        "targets": "_all",
                        "className": "p-3"
                    },
                    {
                        "targets": [13, 14, 16],
                        "render": function(data, type, row) {
                            if (type === 'display' && data) {
                                if (data === '0000-00-00 00:00:00' || data === '') {
                                    return '';
                                } else {
                                    var date = new Date(data);
                                    if (!isNaN(date.getTime())) {
                                        return formatDateTime(data);
                                    } else {
                                        return data;
                                    }
                                }
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        "targets": 5,
                        "render": function(data, type, row) {
                            if (type === 'display') {
                                var statusClass = {
                                    'active': 'bg-success',
                                    'pending': 'bg-warning',
                                    'blocked': 'bg-dark text-light',
                                    'expired': 'bg-danger'
                                };
                                return '<span class="badge ' + statusClass[data] + ' me-2 mt-2">' + languageMapping[data] + '</span>';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        "targets": 6,
                        "render": function(data, type, row) {
                            if (type === 'display') {
                                return languageMapping[data];
                            } else {
                                return data;
                            }
                        }
                    },
					{
                        "targets": 2, // License Key column
                        "render": function(data, type, row) {
                            if (type === 'display') {
                                return '<span title="' + data + '">' + data + '</span>';
                            }
                            return data;
                        }
                    }
                ],
                "pagingType": "full_numbers",
                "language": {
                    "paginate": {
                        "first": '<i class="mdi mdi-chevron-double-left" aria-hidden="true"></i>',
                        "last": '<i class="mdi mdi-chevron-double-right" aria-hidden="true"></i>',
                        "next": '<i class="mdi mdi-chevron-right" aria-hidden="true"></i>',
                        "previous": '<i class="mdi mdi-chevron-left" aria-hidden="true"></i>'
                    },
                    "search": "<?= lang('Pages.Search') ?>:",
                    "lengthMenu": "<?= lang('Pages.DT_lengthMenu') ?>",
                    "loadingRecords": "<?= lang('Pages.Loading_button') ?>",
                    "info": '<?= lang('Pages.DT_info') ?>',
                    "infoEmpty": '<?= lang('Pages.DT_infoEmpty') ?>',
                    "zeroRecords": '<?= lang('Pages.DT_zeroRecords') ?>',
                    "emptyTable": '<?= lang('Pages.DT_emptyTable') ?>'
                },
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "responsive": true,
                "paging": true,
                "info": true,
                "drawCallback": function(settings) {
                    var api = this.api();
                    var json = api.ajax.json();
                    // console.log('Draw callback JSON:', json);

                    // Ensure we have valid total records
                    var totalRecords = json ? (json.recordsTotal || json.length || 0) : 0;
                    var filteredRecords = json ? (json.recordsFiltered || json.length || totalRecords) : 0;
                    
                    // Calculate start and end records
                    var pageInfo = api.page.info();
                    var startRecord = pageInfo.start + 1;
                    var endRecord = Math.min(pageInfo.end, filteredRecords);

                    // Update footer information
                    if (filteredRecords > 0) {
                        $('.dataTables_info').html('<?= lang('Pages.Showing') ?> ' + startRecord + ' <?= lang('Pages.to') ?> ' + endRecord + ' <?= lang('Pages.of') ?> ' + filteredRecords + ' <?= lang('Pages.entries') ?>');
                    } else {
                        $('.dataTables_info').html('<?= lang('Pages.DT_infoEmpty') ?>');
                    }

                    // Update pagination visibility
                    if (filteredRecords <= 10) {
                        $('.dataTables_length, .dataTables_paginate').hide();
                    } else {
                        $('.dataTables_length, .dataTables_paginate').show();
                    }

                    // Ensure checkboxes and button states are updated
                    var allChecked = $('.license-checkbox:checked').length === $('.license-checkbox').length;
                    $('#checkAll').prop('checked', allChecked);
                    updateButtonStates();
					
                    // Only add Quick View buttons if there's data
                    // if (api.rows().data().length > 0) {
                    //     $('#license-list-table tbody tr').each(function() {
                    //         if (!$(this).find('.quick-view-btn').length) {
                    //             $(this).find('td:eq(1)').append('<button class="btn btn-sm btn-info quick-view-btn"><i class="uil uil-eye"></i> <?= lang('Pages.Quick_View') ?></button>');
                    //         }
                    //     });
                    // }
                },
                "dom": "<'row'<'col-sm-12 col-md-6'l>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "initComplete": function(settings, json) {
                    // console.log('Init complete JSON:', json);
                    var api = this.api();
                    var totalRecords = json ? (json.recordsTotal || 0) : 0;
                    
                    // Hide pagination and length menu if records are few
                    if (totalRecords <= 10) {
                        $('.dataTables_length').hide();
                        $('.dataTables_paginate').hide();
                    } else {
                        $('.dataTables_length').show();
                        $('.dataTables_paginate').show();
                    }

                    if (searchValue !== '') {
                        this.api().search(searchValue).draw();
                    }
                }
            });

            // Function to parse URL parameters
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            };

            // Search highlight functionality
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var searchTerm = $('#search-input').val().toLowerCase();
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].toLowerCase().includes(searchTerm)) {
                            return true;
                        }
                    }
                    return false;
                }
            );

            let searchTimeout;

            $('#search-input').on('keyup', function() {
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(function() {
                    table.draw();
                    highlightSearchTerm();
                }, 2000);
            });

            function highlightSearchTerm() {
                var searchTerm = $('#search-input').val();
                $('#license-list-table tbody').unmark({
                    done: function() {
                        $('#license-list-table tbody').mark(searchTerm, {
                            "element": "span",
                            "className": "highlight"
                        });
                    }
                });
            }

            // Check if the URL parameter 's' exists
            var searchValue = getUrlParameter('s');
            if (searchValue !== '') {
                $('#search-input').val(searchValue).trigger('change');
            }

            // Check if the URL parameter 'status' exists
            var statusValue = getUrlParameter('status');
            if (statusValue !== '') {
                // Set the select value directly and ensure it's selected
                $('#status-filter option').each(function() {
                    if ($(this).val() === statusValue) {
                        $(this).prop('selected', true);
                    } else {
                        $(this).prop('selected', false);
                    }
                });
            }

            // Check if the URL parameter 'type' exists
            var typeValue = getUrlParameter('type');
            if (typeValue !== '') {
                // Set the select value directly and ensure it's selected
                $('#type-filter option').each(function() {
                    if ($(this).val() === typeValue) {
                        $(this).prop('selected', true);
                    } else {
                        $(this).prop('selected', false);
                    }
                });
            }

            // Reload the table if URL parameters are present
            if (statusValue !== '' || typeValue !== '' || searchValue !== '') {
                setTimeout(function() {
                    table.ajax.reload();
                }, 100);
            }

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            function updateButtonStates() {
                var checkedCount = $('.license-checkbox:checked').length;
                $('#delete-license-submit, #apply-bulk-action').prop('disabled', checkedCount === 0);
            }

            $('#apply-filters').on('click', function() {
                table.ajax.reload();
            });

            $('#reset-filters').on('click', function() {
                $('#status-filter').val('');
                $('#type-filter').val('');
                $('#search-input').val('');
                table.ajax.reload();
            });

            $('#refresh-table').on('click', function() {
                table.ajax.reload();
            });
			
            $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
                console.error('AJAX Error:', thrownError);
                showToast('danger', '<?= lang('Pages.An_error_occurred_Please_check_your_connection_and_try_again') ?>');
            });

            $('#export-csv').on('click', function () {
                // Collect parameters
                var params = {
                    status: $('#status-filter').val(),
                    type: $('#type-filter').val(),
                    search: $('#search-input').val()
                };

                // Construct the full URL with parameters
                var exportUrl = '<?= base_url('api/license/export/' . $myConfig['Manage_License_SecretKey']) ?>?' + $.param(params);

                // Show loading indicator
                var $exportBtn = $(this);
                $exportBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?= lang('Pages.Exporting') ?>');

                // Trigger file download
                fetch(exportUrl, {
                    method: 'GET',
                    headers: {
                        'User-API-Key': '<?= $userData->api_key ?>'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('<?= lang('Pages.Export_Failed') ?>');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Create a link to download the file
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'licenses_export_' + 
                                new Date().toISOString().slice(0, 10).replace(/-/g, '-') + 'T' + 
                                new Date().toLocaleTimeString('en-US', { 
                                    hour: '2-digit', 
                                    minute: '2-digit', 
                                    second: '2-digit', 
                                    hour12: false 
                                }).replace(/:/g, '') + 
                                'Z.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showToast('danger', error.message);
                })
                .finally(() => {
                    // Restore button state
                    $exportBtn.prop('disabled', false).html('<?= lang('Pages.Export_CSV') ?>');
                });
            });

            $('#checkAll').on('change', function() {
                $('.license-checkbox').prop('checked', $(this).prop('checked'));
                updateButtonStates();
            });

            $(document).on('change', '.license-checkbox', function() {
                var allChecked = $('.license-checkbox:checked').length === $('.license-checkbox').length;
                $('#checkAll').prop('checked', allChecked);
                updateButtonStates();
            });

            $('#apply-bulk-action').on('click', function() {
                var action = $('#bulk-action').val();
                if (!action) {
                    showToast('danger', '<?= lang('Pages.Please_select_an_action') ?>');
                    return;
                }

                var selectedLicenses = $('.license-checkbox:checked').map(function() {
                    return this.id;
                }).get();

                if (selectedLicenses.length === 0) {
                    showToast('danger', '<?= lang('Pages.Please_select_at_least_one_license') ?>');
                    return;
                }

                if (confirm('<?= lang('Pages.Are_you_sure_you_want_to_apply_this_action') ?>')) {
                    $.ajax({
                        url: '<?= base_url('api/license/bulk-action/' . $myConfig['Manage_License_SecretKey']) ?>',
                        method: 'POST',
                        data: {
                            action: action,
                            licenses: selectedLicenses
                        },
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('danger', response.message);

                                table.ajax.reload();
                            } else {
                                showToast('danger', '<?= lang('Pages.Error') ?>: ' + response.message);
                            }
                        },
                        error: function() {
                            showToast('danger', '<?= lang('Pages.An_error_occurred_please_try_again') ?>');
                        }
                    });
                }
            });

            // Quick Edit functionality
            $('#license-list-table').on('click', '.editable', function() {
                var $this = $(this);
                var currentValue = $this.text();
                var field = $this.data('field');
                var input = $('<input>').attr({
                    type: 'text',
                    class: 'quick-edit-input',
                    value: currentValue
                });

                $this.html(input);
                input.focus();

                input.on('blur', function() {
                    var newValue = $(this).val();
                    if (newValue !== currentValue) {
                        // Perform AJAX update
                        $.ajax({
                            url: '<?= base_url('api/license/quick-edit/' . $myConfig['Manage_License_SecretKey']) ?>',
                            method: 'POST',
                            data: {
                                id: $this.closest('tr').find('.license-checkbox').attr('id'),
                                field: field,
                                value: newValue
                            },
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                            },
                            success: function(response) {
                                if (response.success) {
                                    $this.text(newValue);
                                } else {
                                    showToast('danger', '<?= lang('Pages.Error') ?>: ' + response.message);
                                    $this.text(currentValue);
                                }
                            },
                            error: function() {
                                showToast('danger', '<?= lang('Pages.An_error_occurred_please_try_again') ?>');
                                $this.text(currentValue);
                            }
                        });
                    } else {
                        $this.text(currentValue);
                    }
                });

                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        $(this).blur();
                    }
                });
            });

            // Hide the sorter icon in the first column
            var firstTh = $('thead tr th:first');
            firstTh.find('span.dt-column-order').hide();   
            
            var licenseKey = '';
            var lastRowReached = false;
            var offset = 0;
            var limit = 5;
            var isLoading = false;
            /***************************************************
             * Able to open modal by clicking in any rows
             **************************************************/
            // Define a global variable to store original values
            var originalRowData = [];

            // Define a mapping between original values and their corresponding language strings
            var languageMapping = {
                'active': '<?= lang('Pages.Active') ?>',
                'pending': '<?= lang('Pages.Pending') ?>',
                'blocked': '<?= lang('Pages.Blocked') ?>',
                'expired': '<?= lang('Pages.Expired') ?>',
                'lifetime': '<?= lang('Pages.Lifetime') ?>',
                'subscription': '<?= lang('Pages.Subscription') ?>',
                'trial': '<?= lang('Pages.Trial') ?>',
                'days': '<?= lang('Pages.Days') ?>',
                'months': '<?= lang('Pages.Months') ?>',
                'years': '<?= lang('Pages.Years') ?>',
                'onetime': '<?= lang('Pages.Onetime') ?>'
            };

            // Function to update and show the modal
            function updateAndShowModal(rowData, key) {
                // console.log(rowData);
                // Refresh all data
                $('#clear-log-license-btn').hide();
                $('#license-log-table').hide();
                $('#license-log-tbody').empty();
                $('#edit-license-btn').show();
                $('#edit-menu').hide();
                $('#domain-table').hide();
                $('#device-table').hide();
                $('#domain-list').empty();
                $('#device-list').empty();
                $('#no_registered_domain_device').hide();
                $('#expired_license_notification').hide();

                if(rowData[5] === 'active') {
                    $('#resend-license-submit').prop('disabled', false);
                    $('#recipientTextarea').prop('disabled', false);
                    $('#expired_license_notification').hide();
                    $('#delete-domain-device-submit').show();
                    loadRegisteredDomainDevice(rowData[2]);
                }
                else {
                    $('#resend-license-submit').prop('disabled', true);
                    $('#recipientTextarea').prop('disabled', true);
                    $('#expired_license_notification').show();
                    $('#delete-domain-device-submit').hide();
                }

                offset = 0;
                limit = 5;

                loadRows(rowData[2]); // reload activity logs

                let licenseKey = rowData[2];
                if (licenseKey.length > 10) {
                    licenseKey = licenseKey.substring(0, 10) + '...';
                }
                
                $('#rowModal-title').text('<?= lang('Pages.License_Key') ?>: ' + licenseKey);
                $('#rowModal .modal-body tbody tr').each(function(index) {
                    if (index === 1 ) {
                        var licenseKey = rowData[2];
                        $(this).find('td:last-child').html(`<span class="broken-underline copy-to-clipboard" data-clipboard-text="${licenseKey}">${licenseKey}</span> <i class="ti ti-copy copy-to-clipboard" data-clipboard-text="${licenseKey}"></i>`);
                    }
                    else if (index === 4 || index === 5 || index === 18) {
                        var status = rowData[index + 1];
                        var translatedValue = languageMapping[status.toLowerCase()] || status;
                        $(this).find('td:last-child').html(translatedValue);
                    } else {
                        $(this).find('td:last-child').text(rowData[index + 1]);
                    }
                });

                $('#rowModal').attr('aria-hidden', 'false');
                $('#rowModal').modal('show');
            }

            // Event handler for table row click
            $('#license-list-table').on('click', 'tbody tr', function(event) {
                if ($(event.target).is(':checkbox')) return;
                
                var data = table.row(this).data();
                var key = data.id;
                licenseKey = data.license_key;
                $('#licenseInput').val(licenseKey).prop('readonly', true);
                $('#licenseReg').val(licenseKey);

                // Reset variables for fresh data loading
                lastRowReached = false;
                offset = 0;
                isLoading = false;

                var rowData = $(this).find('td').map(function() {
                    return $(this).text();
                }).get();
                rowData.splice(1, 0, key);
                originalRowData = rowData.map(function(value) {
                    if(value == '<?= lang('Pages.Active') ?>') {
                        return value.trim() === '' ? ' ' : 'active';
                    }
                    else if(value == '<?= lang('Pages.Pending') ?>') {
                        return value.trim() === '' ? ' ' : 'pending';
                    }
                    else if(value == '<?= lang('Pages.Blocked') ?>') {
                        return value.trim() === '' ? ' ' : 'blocked';
                    }
                    else if(value == '<?= lang('Pages.Expired') ?>') {
                        return value.trim() === '' ? ' ' : 'expired';
                    }
                    else if(value == '<?= lang('Pages.Lifetime') ?>') {
                        return value.trim() === '' ? ' ' : 'lifetime';
                    }
                    else if(value == '<?= lang('Pages.Subscription') ?>') {
                        return value.trim() === '' ? ' ' : 'subscription';
                    }
                    else if(value == '<?= lang('Pages.Trial') ?>') {
                        return value.trim() === '' ? ' ' : 'trial';
                    }
                    else if(value == '<?= lang('Pages.Days') ?>') {
                        return value.trim() === '' ? ' ' : 'days';
                    }
                    else if(value == '<?= lang('Pages.Months') ?>') {
                        return value.trim() === '' ? ' ' : 'months';
                    }
                    else if(value == '<?= lang('Pages.Years') ?>') {
                        return value.trim() === '' ? ' ' : 'years';
                    }
                    else if(value == '<?= lang('Pages.Onetime') ?>') {
                        return value.trim() === '' ? ' ' : 'onetime';
                    }
                    else {
                        return value.trim() === '' ? '' : value;
                    }
                });
                updateAndShowModal(originalRowData, key);
            });

            // Function to refresh the modal manually if needed
            function refreshModal() {
                updateAndShowModal(originalRowData, originalRowData[1]);
            }

            // Retrieve license logs
            $('#rowModal .modal-body').scroll(function() {
                if ($('#licenseLogs').hasClass('active')) {
                    var modal_scrollTop = $(this).scrollTop();
                    var modal_scrollHeight = $(this).prop('scrollHeight');
                    var modal_innerHeight = $(this).innerHeight();
                    if (modal_scrollTop + modal_innerHeight >= (modal_scrollHeight - 100)) {
                        loadRows(licenseKey);
                    } 
                }
            });

            function showLoadingIndicator() {
                var licenseLogsButton = $('#licenseLogs-info');
                var licenseRegButton = $('#licenseRegisteredDomainDevice-data');
                enableLoadingEffect(licenseLogsButton);
                enableLoadingEffect(licenseRegButton);
                $('#loading-indicator').show();
            }

            function hideLoadingIndicator() {
                var licenseLogsButton = $('#licenseLogs-info');
                var licenseRegButton = $('#licenseRegisteredDomainDevice-data');
                disableLoadingEffect(licenseLogsButton);
                disableLoadingEffect(licenseRegButton);
                $('#loading-indicator').hide();
            }

            function loadRows(licenseKey) {
                if (licenseKey !== '' && !lastRowReached && !isLoading) {
                    isLoading = true;
                    showLoadingIndicator();
                    // License Logs
                    $.ajax({
                        url: '<?= base_url('load-rows/license-logs/') ?>' + offset + '/' + limit + '/' + licenseKey,
                        method: 'GET',
                        success: function (data) {
                            var jsonData = JSON.parse(data);
                            if (jsonData.length > 0) {
                                jsonData.forEach(function(row) {
                                    var html = '<tr>' +
                                        '<td class="p-3 text-muted">' + row.id + '</td>' +
                                        `<td class="p-3 text-dark"><span class="text-${row.is_valid === 'yes' ? 'success' : 'danger'}">${row.action}</span></td>` +
                                        '<td class="p-3 text-muted">' + formatDateTime(row.time) + '</td>' +
                                        '<td class="p-3 text-muted">' + row.source + '</td>' +
                                        '</tr>';
                                    $('#license-log-tbody').append(html);
                                });
                                offset += limit;

                                $('#clear-log-license-btn').show();
                                $('#license-log-table').show();
                            } else {
                                lastRowReached = true;
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('<?= lang('Pages.ajax_no_response') ?>', status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
                            isLoading = false;
                            hideLoadingIndicator();
                        }
                    });
                }
            }

            function loadRegisteredDomainDevice(licenseKey) {
                // showLoadingIndicator();
                $('#verified-license').val(licenseKey);

                // License Registered Domain/Device
                var form = $('#search-license-form');
                var licenseReg = $('#licenseReg');
                var selectRegWrapper = $('#select-domain-device-wrapper');
                var domainWrapper = $('#domain-table');
                var tableDomain = $('#domain-list');
                var deviceWrapper = $('#device-table');
                var tableDevice = $('#device-list');
                var hash = $('#hash');
                var captcha = $('#captcha');
                var noDomainDeviceNotification = $('#no_registered_domain_device');
                var deleteButton = $('#delete-domain-device-submit');
                // reset the tables and hide
                selectRegWrapper.hide();
                deleteButton.hide();
                $('#checkAll-domain').prop('checked', false);
                $('#checkAll-device').prop('checked', false);
                tableDomain.html('');
                domainWrapper.hide();
                tableDevice.html('');
                deviceWrapper.hide();
                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('reset-license/search') ?>',
                    method: 'POST',
                    data: {
                        hash: hash.val(),
                        captcha: captcha.val(),
                        licenseInput: licenseKey
                    },
                    success: function (response) {
                        // console.log(response);
                        if (response.status == 1) {
                            var licenseDetails = response.data;
                            // Response fully success
                            noDomainDeviceNotification.hide();
                            // build the tables
                            // for domain list
                            if (licenseDetails['registered_domains'].length > 0) {
                                var domainHtml = '';
                                $.each(licenseDetails['registered_domains'], function (key, domain) {
                                    domainHtml += '<tr>';
                                    domainHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + domain.domain_name + '" id="' + domain.domain_name + '" name="' + domain.domain_name + '"></div></td>';
                                    domainHtml += '<td class="align-middle"><label for="' + domain.domain_name + '" class="form-label">' + domain.domain_name + '</label></td>';
                                    domainHtml += '</tr>';
                                });
                                tableDomain.html(domainHtml);
                                domainWrapper.show();
                                selectRegWrapper.show();
                                deleteButton.show();
                            }
                            // for device list
                            if (licenseDetails['registered_devices'].length > 0) {
                                var deviceHtml = '';
                                $.each(licenseDetails['registered_devices'], function (key, device) {
                                    deviceHtml += '<tr>';
                                    deviceHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + device.device_name + '" id="' + device.device_name + '" name="' + device.device_name + '"></div></td>';
                                    deviceHtml += '<td class="align-middle"><label for="' + device.device_name + '" class="form-label">' + device.device_name + '</label></td>';
                                    deviceHtml += '</tr>';
                                });
                                tableDevice.html(deviceHtml);
                                deviceWrapper.show();
                                selectRegWrapper.show();
                                deleteButton.show();
                            }
                            if( (licenseDetails['registered_domains'].length === 0) && (licenseDetails['registered_devices'].length === 0) ) {
                                noDomainDeviceNotification.slideDown();
                                deleteButton.hide();
                            }
                        } else if (response.status == 2) {
                            // Response success but with error
                            showToast('info', response.msg);
                        } else {
                            // Response error in processing the request
                            showToast('danger', response.msg);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }

            // Edit license details
            $('#edit-license-btn').on('click', function() {
                $(this).hide();
                $('#edit-menu').show();
                convertToInputs();
            });

            $('#cancel-btn').on('click', function() {
                $('#edit-license-btn').show();
                $('#edit-menu').hide();
                refreshModal();
            });

            // When modal is closed
            $('#rowModal').on('hidden.bs.modal', function () {
                $('#rowModal').attr('aria-hidden', 'true');
                $('#clear-log-license-btn').hide();
                $('#license-log-table').hide();
                $('#license-log-tbody').empty();
                $('#edit-license-btn').show();
                $('#edit-menu').hide();
                $('#domain-table').hide();
                $('#device-table').hide();
                $('#domain-list').empty();
                $('#device-list').empty();
                $('#no_registered_domain_device').hide();
                $('#expired_license_notification').hide();
                
                // Remove the 'shown.bs.modal' event handler to prevent multiple bindings
                $(this).off('shown.bs.modal');
            });

            // Convert the values into inputs
            function getIndividualRowValue(rowNumber) {
                var $td = $('#rowModal .modal-body #license-detail-table tbody tr:nth-child(' + rowNumber + ') td:last-child');
                var value = $td.text().trim();
                return value;
            }

            // reload the data maintaining the current user's selection
            function reloadDataTable() {
                $('#license-list-table').DataTable().ajax.reload(null, false);
            }

            // Function to enable/disable the delete button based on checkbox state
            function updateDeleteButtonState() {
                var anyCheckboxChecked = $('tbody#license-list-tbody input[type="checkbox"]:checked').length > 0;
                $('#delete-license-submit').prop('disabled', !anyCheckboxChecked);
            }  

            function convertToInputs() { 
                var currentLicenseID = getIndividualRowValue(1);
                var hiddenLicenseID = $('#id');
                hiddenLicenseID.val(currentLicenseID);

                var currentLicenseKey = getIndividualRowValue(2);
                var hiddenLicenseKey = $('#license_key');
                hiddenLicenseKey.val(currentLicenseKey);

                var rowsToConvert = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 17, 18, 19, 20, 21, 22, 23, 24];
                $.each(rowsToConvert, function(index, rowNumber) {
                    var $td = $('#rowModal .modal-body #license-detail-table tbody tr:nth-child(' + rowNumber + ') td:last-child');
                    var value = originalRowData[rowNumber];
                    
                    // Store the original value as data
                    $td.data('original-value', value);
                    
                    if (rowNumber === 3) {
                        var input = '<input type="number" class="form-control" id="max_allowed_domains" name="max_allowed_domains" value="' + value + '" placeholder="<?= lang('Pages.Enter_a_whole_number') ?>" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 4) {
                        var input = '<input type="number" class="form-control" id="max_allowed_devices" name="max_allowed_devices" value="' + value + '" placeholder="<?= lang('Pages.Enter_a_whole_number') ?>" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 5) {
                        var selectHtml = '<select class="form-select form-control" name="license_status" id="license_status">';
                        selectHtml += '<option value=""><?= lang('Pages.Select_Option') ?></option>';
                        selectHtml += '<option value="active"><?= lang('Pages.Active') ?></option>';
                        selectHtml += '<option value="pending"><?= lang('Pages.Pending') ?></option>';
                        selectHtml += '<option value="blocked"><?= lang('Pages.Blocked') ?></option>';
                        selectHtml += '<option value="expired"><?= lang('Pages.Expired') ?></option>';
                        selectHtml += '</select>';

                        var $select = $(selectHtml);
                        $select.val(value);
                        $td.html($select);
                    } 
                    else if (rowNumber === 6) {
                        var selectHtml = '<select class="form-select form-control" id="license_type" name="license_type">';
                        selectHtml += '<option value=""><?= lang('Pages.Select_Option') ?></option>';
                        selectHtml += '<option value="lifetime"><?= lang('Pages.Lifetime') ?></option>';
                        selectHtml += '<option value="subscription"><?= lang('Pages.Subscription') ?></option>';
                        selectHtml += '<option value="trial"><?= lang('Pages.Trial') ?></option>';
                        selectHtml += '</select>';

                        var $select = $(selectHtml);
                        $select.val(value);
                        $td.html($select);                           
                    }
                    else if (rowNumber === 7) {
                        var input = '<input type="text" class="form-control" id="first_name" name="first_name" value="' + value + '" placeholder="<?= lang('Pages.First_Name') ?>" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 8) {
                        var input = '<input type="text" class="form-control" id="last_name" name="last_name" value="' + value + '" placeholder="<?= lang('Pages.Last_Name') ?>" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 9) {
                        var input = '<input type="text" class="form-control" id="email" name="email" value="' + value + '" placeholder="client@example.com" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 10) {
                        var input = '<input type="text" class="form-control" id="company_name" name="company_name" value="' + value + '" placeholder="<?= lang('Pages.Company_Name') ?>">';
                        $td.html(input);
                    }
                    else if (rowNumber === 11) {
                        var input = '<input type="text" class="form-control" id="txn_id" name="txn_id" value="' + value + '" placeholder="<?= lang('Pages.Reference') ?> / <?= lang('Pages.Transaction_ID') ?>" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 12) {
                        var input = '<input type="text" class="form-control" id="purchase_id_" name="purchase_id_" value="' + value + '" placeholder="<?= lang('Pages.Purchase_ID') ?>" required>';
                        $td.html(input);
                    }
                    else if (rowNumber === 14) {
                        var input = '<input type="text" class="form-control" id="date_expiry" name="date_expiry" placeholder="<?= lang('Pages.Expiration_Date') ?> (<?= lang('Pages.format') ?> 2024-01-30 22:15:00)" value="' + convertDateFormat(value) + '" required data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang('Pages.format')?> 2024-01-30 22:15:00">';
                        $td.html(input);
                    }
                    else if (rowNumber === 17) {
                        var selectHtml = '<select id="product_ref" name="product_ref" class="form-select form-control" required>';
                        selectHtml += '<option value="" ' + (value === '' ? 'selected' : '') + '><?= lang('Pages.Select_Option') ?></option>';
                        <?php
                        $productListWithVariation = productListWithVariation($userData->id);
                        foreach ($productListWithVariation as $productName) {
                            echo 'selectHtml += \'<option value="' . $productName . '" \' + (value === "' . $productName . '" ? \'selected\' : \'\') + \'>' . $productName . '</option>\';';
                        }
                        ?>
                        selectHtml += '</select>';
                        $td.html(selectHtml);
                    }
                    else if (rowNumber === 18) {
                        var input = '<input type="number" class="form-control" id="billing_length" name="billing_length" value="' + value + '" placeholder="<?= lang('Pages.Billing_Length') ?>">';
                        $td.html(input);
                    }
                    else if (rowNumber === 19) {
                        var selectHtml = '<select class="form-select form-control" id="billing_interval" name="billing_interval">';
                        selectHtml += '<option value=""><?= lang('Pages.Select_Option') ?></option>';
                        selectHtml += '<option value="days"><?= lang('Pages.Days') ?></option>';
                        selectHtml += '<option value="months"><?= lang('Pages.Months') ?></option>';
                        selectHtml += '<option value="years"><?= lang('Pages.Years') ?></option>';
                        selectHtml += '<option value="onetime"><?= lang('Pages.Onetime') ?></option>';
                        selectHtml += '</select>';

                        var $select = $(selectHtml);
                        $select.val(value);
                        $td.html($select);
                    }
                    else if (rowNumber === 20) {
                        var input = '<input type="text" class="form-control" id="subscr_id" name="subscr_id" value="' + value + '" placeholder="<?= lang('Pages.Unique_ID') ?>">';
                        $td.html(input);
                    }
                    else if (rowNumber === 21) {
                        var input = '<input type="text" class="form-control" id="until" name="until" value="' + value + '" placeholder="<?= lang('Pages.Supported_Until') ?>">';
                        $td.html(input);
                    }
                    else if (rowNumber === 22) {
                        var input = '<input type="text" class="form-control" id="current_ver" name="current_ver" value="' + value + '" placeholder="<?= lang('Pages.Current_Version') ?>">';
                        $td.html(input);
                    }
                    else if (rowNumber === 23) {
                        var input = '<input type="number" class="form-control" id="manual_reset_count" name="manual_reset_count" value="' + value + '" placeholder="<?= lang('Pages.Manual_Reset_Count') ?>">';
                        $td.html(input);
                    }
                    else if (rowNumber === 24) {
                        var input = '<input type="text" class="form-control" id="item_reference" name="item_reference" value="' + value + '" placeholder="<?= lang('Pages.Item_Reference') ?>">';
                        $td.html(input);
                    }
                });

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
                                
            }

            // Delete license
            $('#delete-license-submit').on('click', function (e) {
                e.preventDefault();
                var form = $('#delete-template-form');
                var submitButton = $(this);
                var selectedLicense = [];
                $('tbody#license-list-tbody input[type="checkbox"]:checked').each(function () {
                    selectedLicense.push($(this).attr('id'));
                });
                form.find('input[name="selectedLicense[]"]').remove();
                $.each(selectedLicense, function (index, licenseKey) {
                    form.append('<input type="hidden" name="selectedLicense[]" value="' + licenseKey + '">');
                });
                enableLoadingEffect(submitButton);
                var data = new FormData(form[0]);
                var confirmDelete = confirm("<?= lang('Pages.confirmation_to_delete_license') ?>");
                if (confirmDelete) {
                    $.ajax({
                        url: '<?= base_url('api/license/delete/key/' . $myConfig['Manage_License_SecretKey']) ?>',
                        method: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        },
                        success: function (response) {
                            let toastType = 'info';

                            if (response.result === 'success') {
                                toastType = 'success';
                                reloadDataTable();
                                submitButton.prop('disabled', true);
                                $('#checkAll').prop('checked', false);
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
                            updateDeleteButtonState();
                            disableLoadingEffect(submitButton);
                        }
                    });
                    
                } else {
                    disableLoadingEffect(submitButton);
                }
            });

            // Clear license activity log
            $('#clear-log-license-btn').click(function() {
                var requestURL = '<?= base_url('api/license/delete/logs/' . $myConfig['Manage_License_SecretKey'] .'/') ?>' + licenseKey;
                var confirmDelete = confirm("<?= lang('Pages.confirm_clear_license_activity_log') ?>");
                if (confirmDelete) {
                    $.ajax({
                        url: requestURL,
                        method: 'POST',
                        data: { licenseKey: licenseKey },
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('User-API-Key', '<?= $userData->api_key ?>');
                        },                            
                        success: function (response) {
                            let toastType = 'info';

                            if (response.result === 'success') {   
                                toastType = 'success';
                                $('#license-log-tbody').empty();
                                refreshModal();
                            } else {
                                toastType = 'danger';
                            }

                            showToast(toastType, response.message);
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        }
                    });
                }
            });

            // Edit license requests
            $('#edit-license-submit').on('click', function(e) {
                    e.preventDefault();

                    var form = $('#edit-template-form');
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
                    var productReferenceSelect = $('#product_ref');
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
                    var expirationDateRegex = /^\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}:\d{2}$/;

                    // Validate expiration date max values
                    var maxMonth = 12;
                    var maxDay = 31;

                    // Define a regular expression for not allowed characters
                    var disallowedCharsRegex_general = /[~!#$%&*+=|:.]/;
                    // var disallowedCharsRegex_forDate    = /[~!#$%&*\_+=|:.]/;
                    var disallowedCharsRegex_forDate = /[~!#$%&*\_+=|]/;
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

                    /*******************
                     * Start validations
                     ******************/

                    /**
                     * Iterate over other input fields and validate
                     *  */ 
                    form.find('input').not(itemReferenceInput).not(firstNameInput).not(lastNameInput).not(untilInput).not(current_verInput).not(emailInput).not(expirationDateInput).not(subscrIDInput).not(billingLengthInput).not(billingIntervalInput).not(manualResetCountInput).not(companyNameInput).not(txn_idInput).not(itemReferenceInput).each(function () {
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
                    form.find('select').not(productReferenceSelect).not(billingIntervalInput).each(function () {
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
                            // Disable loading effect
                            disableLoadingEffect(submitButton);	
                        } else if (!expirationDateRegex.test(expirationDateInput.val())) {
                            expirationDateInput.addClass('is-invalid');
                            // Disable loading effect
                            disableLoadingEffect(submitButton);	
                        } else if (disallowedCharsRegex_forDate.test(expirationDateInput.val())) {
                            expirationDateInput.addClass('is-invalid');
                            // Disable loading effect
                            disableLoadingEffect(submitButton);	
                        } else {
                            // Validate expiration date max values
                            var dateParts = expirationDateInput.val().split(' ')[0].split('-');
                            var month = parseInt(dateParts[1]);
                            var day = parseInt(dateParts[2]);
                            
                            if (month > maxMonth || day > maxDay) {
                                expirationDateInput.addClass('is-invalid');
                                // Disable loading effect
                                disableLoadingEffect(submitButton);	
                            } else {
                                expirationDateInput.addClass('is-valid');
                            }
                        }

                        // billing length and interval
                        if(billingLengthInput.val() === '') {
                            billingLengthInput.addClass('is-invalid');

                            // Disable loading effect
                            disableLoadingEffect(submitButton);	
                        }
                        else {
                            billingLengthInput.addClass('is-valid');
                        }

                        if(billingIntervalInput.val() === '') {
                            billingIntervalInput.addClass('is-invalid');

                            // Disable loading effect
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
                     *  */ 
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
                     *  */ 
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
                     * Product validation
                     *  */ 
                    if(productReferenceSelect.val() === '') {
                        productReferenceSelect.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else if (disallowedCharsRegex_name.test(productReferenceSelect.val())) {
                        productReferenceSelect.addClass('is-invalid');

                        // Disable loading effect
                        disableLoadingEffect(submitButton);	
                    } else {
                        productReferenceSelect.addClass('is-valid');
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

                    /*****************
                     * End validations
                     ****************/

                    // Check if there are any elements with 'is-invalid' class
                    if (form.find('.is-invalid').length === 0) {
                        // Use AJAX to submit the form data
                        $.ajax({
                            url: '<?= base_url('license-manager/edit-license/submit') ?>',
                            method: 'POST',
                            data: form.serialize(),
                            success: function (response) {
                                let toastType = 'info';

                                if (response.success !== false) {
                                    if (response.status === 1) {
                                        toastType = 'success';
                                    } else {
                                        toastType = 'danger';
                                    }

                                    // Update the originalRowData from the received `data` key from response
                                    var newData = JSON.parse(response.data);

                                    originalRowData[3] = newData.max_allowed_domains;
                                    originalRowData[4] = newData.max_allowed_devices;
                                    originalRowData[5] = newData.status;
                                    originalRowData[6] = newData.license_type;
                                    originalRowData[7] = newData.first_name;
                                    originalRowData[8] = newData.last_name;
                                    originalRowData[9] = newData.email;
                                    originalRowData[10] = newData.company_name;
                                    originalRowData[11] = newData.txn_id;
                                    originalRowData[12] = newData.purchase_id_;
                                    originalRowData[13] = originalRowData[13];
                                    originalRowData[14] = (newData.date_expiry === "0000-00-00 00:00:00" || new Date(newData.date_expiry).toISOString().slice(0, 10) === '1970-01-01') ? "" : formatDateTime(newData.date_expiry);
                                    originalRowData[15] = newData.reminder_sent;
                                    originalRowData[16] = originalRowData[16];
                                    originalRowData[17] = newData.product_ref;
                                    originalRowData[18] = newData.billing_length;
                                    originalRowData[19] = newData.billing_interval;
                                    originalRowData[20] = newData.subscr_id;
                                    originalRowData[21] = newData.until;
                                    originalRowData[22] = newData.current_ver;
                                    originalRowData[23] = newData.manual_reset_count;
                                    originalRowData[24] = newData.item_reference;
                                    
                                    refreshModal(); // refresh the modal
                                    reloadDataTable(); // reload the main license table

                                    // disable the forms
                                    $("#cancel-btn").click();
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
                        showToast('danger', '<?= lang('Notifications.required_fields_missing') ?>');
                    }
                }); 
			
			// Keyboard navigation
            $(document).on('keydown', function(e) {
                if (e.which === 38) { // Up arrow
                    moveSelection('up');
                } else if (e.which === 40) { // Down arrow
                    moveSelection('down');
                } else if (e.which === 13) { // Enter
                    openSelectedRow();
                }
            });

            function moveSelection(direction) {
                var $rows = $('#license-list-table tbody tr');
                var $selected = $rows.filter('.selected-row');
                var curIndex = $rows.index($selected);

                if (direction === 'up' && curIndex > 0) {
                    $rows.removeClass('selected-row');
                    $rows.eq(curIndex - 1).addClass('selected-row');
                } else if (direction === 'down' && curIndex < $rows.length - 1) {
                    $rows.removeClass('selected-row');
                    $rows.eq(curIndex + 1).addClass('selected-row');
                }
            }

            function openSelectedRow() {
                var $selected = $('#license-list-table tbody tr.selected-row');
                if ($selected.length) {
                    $selected.click();
                }
            }

            // Quick View feature
            $('#license-list-table').on('click', '.quick-view-btn', function(e) {
                e.stopPropagation();
                var $row = $(this).closest('tr');
                var data = table.row($row).data();
                
                var popupContent = `
                    <strong><?= lang('Pages.Key') ?>:</strong> <span class="broken-underline copy-to-clipboard">${data.license_key}</span> <i class="mdi mdi-content-copy copy-to-clipboard" data-clipboard-text="${data.license_key}" title="<?= lang('Pages.Copy_to_clipboard') ?>"></i><br>
                    <strong><?= lang('Pages.Status') ?>:</strong> ${languageMapping[data.license_status]}<br>
                    <strong><?= lang('Pages.Type') ?>:</strong> ${languageMapping[data.license_type]}<br>
                    <strong><?= lang('Pages.Email') ?>:</strong> ${data.email}
                `;

                var $popup = $('<div class="quick-view-popup"></div>').html(popupContent);
                $('body').append($popup);

                var pos = $(this).offset();
                $popup.css({
                    top: pos.top + $(this).height() + 5,
                    left: pos.left
                }).show();

                $(document).on('click', function hidePopup(e) {
                    if (!$(e.target).closest('.quick-view-popup').length) {
                        $popup.remove();
                        $(document).off('click', hidePopup);
                    }
                });
            });

            // Add Quick View button to each row
            // table.on('draw', function() {
                // $('#license-list-table tbody tr').each(function() {
                //     if (!$(this).find('.quick-view-btn').length) {
                //         $(this).find('td:first').append('<button class="btn btn-sm btn-info quick-view-btn"><i class="uil uil-eye"></i> <?= lang('Pages.Quick_View') ?></button>');
                //     }
                // });
            // });
			
            /*******************************************
            // Handle the resend license detail requests
            *******************************************/
            $('#resend-license-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#resend-license-form');
                var licenseInput = $('#licenseInput');
                var recipientTextarea = $('#recipientTextarea');
                var submitButton = $(this);

                // Regular expression for email validation
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                // Define a regular expression for not allowed characters
                var disallowedCharsRegex_licenseKey = /[~!#$%&*\-_+=|:.]/;
                var disallowedCharsRegex_forEmail   = /[~!#$%&*+=|:()\[\]]/;

                // enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid');
                
                /*******************
                 * Start validations
                 ******************/        
                
                // Iterate over other license key field and validate
                if(licenseInput.val() === '') {
                    licenseInput.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                // } else if (disallowedCharsRegex_licenseKey.test(licenseInput.val()) || licenseInput.val().length !== 40) {
                //     licenseInput.addClass('is-invalid');
                } else {
                    licenseInput.addClass('is-valid');
                } 

                if(recipientTextarea.val() === '') {
                    recipientTextarea.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	
                }

                // Validate recipient text area input
                var recipientLines = recipientTextarea.val().split('\n');
                var isValidRecipient = true;

                for (var i = 0; i < recipientLines.length; i++) {
                    var trimmedLine = recipientLines[i].trim();

                    if (trimmedLine !== '' && (!emailRegex.test(trimmedLine) || trimmedLine.includes(','))) {
                        isValidRecipient = false;
                        break;
                    }
                }

                if (recipientTextarea.val() === '' || !isValidRecipient) {
                    recipientTextarea.addClass('is-invalid');

                    // Disable loading effect
                    disableLoadingEffect(submitButton);	

                    return;
                } else {
                    recipientTextarea.addClass('is-valid');
                }
                /*****************
                 * End validations
                 ****************/      

                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('resend-license/request') ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if (response.success) {
                                toastType = 'success';
                                form.find('.is-valid').removeClass('is-valid');
                                $('#recipientTextarea').val('');
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

            /***********************************
            // Handle the delete detail requests
            ***********************************/
            $('#delete-domain-device-submit').on('click', function (e) {
                e.preventDefault();

                var form = $('#remove-domain-device-form');
                var submitButton = $(this);
                var selectWrapper = $('#select-domain-device-wrapper');
                var tableDomain = $('#domain-list');
                var tableDevice = $('#device-list');
                var licenseKey = $('#verified-license').val();

                var selectedDomain = [];
                $('tbody#domain-list input[type="checkbox"]:checked').each(function () {
                    selectedDomain.push($(this).val());
                });

                form.find('input[name="selected-domain"]').val(selectedDomain.join(','));

                var selectedDevice = [];
                $('tbody#device-list input[type="checkbox"]:checked').each(function () {
                    selectedDevice.push($(this).val());
                });

                form.find('input[name="selected-device"]').val(selectedDevice.join(','));

                // Enable loading effect
                enableLoadingEffect(submitButton);

                // Display a confirmation dialog box
                var confirmDelete = confirm("<?= lang('Pages.confirmation_delete_selected') ?>");

                if (confirmDelete) {                
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('reset-license/delete-selected') ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success';
                                selectWrapper.hide(); 
                                tableDomain.html('');
                                tableDevice.html('');
                            } else if (response.status == 2) {
                                toastType = 'info';  
                                selectWrapper.hide(); 
                                tableDomain.html('');
                                tableDevice.html('');
                            } else {
                                toastType = 'danger';
                            }

                            refreshModal();

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
                } else {
                    // User canceled the deletion action
                    // Disable loading effect
                    disableLoadingEffect(submitButton);
                }
            });
        });

        // Function to enable/disable the delete button based on checkbox state
        function updateDeleteButtonStateRegArea() {
            var anyCheckboxChecked1 = $('tbody#domain-list input[type="checkbox"]:checked').length > 0;
            var anyCheckboxChecked2 = $('tbody#device-list input[type="checkbox"]:checked').length > 0;
            $('#delete-domain-device-submit').prop('disabled', !(anyCheckboxChecked1 || anyCheckboxChecked2));      
        }    

        /****
         * For domain selection
         */	
        // Check/uncheck all checkboxes when "checkAll-domain" is clicked
        $('#checkAll-domain').on('change', function () {
            var isChecked = $(this).prop('checked');
            $('tbody#domain-list').find('input[type="checkbox"]').prop('checked', isChecked);
            updateDeleteButtonStateRegArea();
        });

        // Check/uncheck "checkAll-domain" based on the state of individual checkboxes
        $(document).on('change', 'tbody#domain-list input[type="checkbox"]', function () {
            var allChecked = $('tbody#domain-list input[type="checkbox"]:checked').length === $('tbody#domain-list input[type="checkbox"]').length;
            $('#checkAll-domain').prop('checked', allChecked);
            updateDeleteButtonStateRegArea();
        });

        // Uncheck "checkAll-domain" if any individual checkbox is unchecked
        $(document).on('change', 'tbody#domain-list input[type="checkbox"]', function () {
            if (!$(this).prop('checked')) {
                $('#checkAll-domain').prop('checked', false);
            }
        });	

        /****
         * For device selection
         */	
        // Check/uncheck all checkboxes when "checkAll-device" is clicked
        $('#checkAll-device').on('change', function () {
            var isChecked = $(this).prop('checked');
            $('tbody#device-list').find('input[type="checkbox"]').prop('checked', isChecked);
            updateDeleteButtonStateRegArea();
        });

        // Check/uncheck "checkAll-device" based on the state of individual checkboxes
        $(document).on('change', 'tbody#device-list input[type="checkbox"]', function () {
            var allChecked = $('tbody#device-list input[type="checkbox"]:checked').length === $('tbody#device-list input[type="checkbox"]').length;
            $('#checkAll-device').prop('checked', allChecked);
            updateDeleteButtonStateRegArea();
        });

        // Uncheck "checkAll-device" if any individual checkbox is unchecked
        $(document).on('change', 'tbody#device-list input[type="checkbox"]', function () {
            if (!$(this).prop('checked')) {
                $('#checkAll-device').prop('checked', false);
            }
        });	    

        // Call the function initially to set the button state
        updateDeleteButtonStateRegArea();	
    </script>
<?= $this->endSection() ?>
