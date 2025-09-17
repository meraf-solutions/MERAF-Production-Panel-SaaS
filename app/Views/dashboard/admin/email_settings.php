<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('head') ?>
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
    <form class="row mt-4" novalidate id="email-settings-form">
        <?php $adminSettings = getMyConfig('', 0); ?>

        <div class="col-xl-12 mt-3 mb-3 text-center">                                
            <button class="mx-auto btn btn-primary" id="email-settings-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save_Settings') ?></button>
        </div>                                
        
        <!-- Modify email service settings -->
        <div class="col-12 mt-4">
            <div class="card border-0 rounded shadow p-4">
                <div class="d-flex flex-column flex-md-row align-items-center">
                    <h4 class="mb-0 me-md-4"><?= lang('Pages.Email_Service_Config') ?></h4>

                    <div class="position-relative col-12 col-md-auto me-lg-3 mx-auto mb-3 mb-md-0 text-center mt-4 mt-md-0">
                        <a href="javascript:void(0)" class="btn btn-soft-primary btn-sm me-2" id="edit-email-service-btn">
                            <i class="uil uil-eye"></i> <?= lang('Pages.Show_Configuration') ?>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-soft-secondary btn-sm me-2" id="test-email-service-btn">
                            <i class="uil uil-envelope-send"></i> <?= lang('Pages.Send_Test_Email') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- From Name/Email -->
        <div class="col-xl-6 mt-4">
            <div class="card border-0 rounded shadow p-4">
                <h5 class="mb-0"><?= lang('Pages.From_Name_Email') ?></h5>
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label" for="fromName"><?= lang('Pages.From_Name_Label') ?> <span class="text-danger">*</span></label>
                            <div class="form-icon position-relative">
                                <i data-feather="user" class="fea icon-sm icons"></i>
                                <input name="fromName" id="fromName" type="text" class="form-control ps-5 emailName" placeholder="<?= lang('Pages.From_Name_Placeholder') ?>" value="<?= $adminSettings['fromName'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.From_Name_Required') ?>
                                </div> 
                            </div>
                        </div>
                    </div><!--end col-->      
                    
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label" for="fromEmail"><?= lang('Pages.From_Email_Label') ?> <span class="text-danger">*</span></label>
                            <div class="form-icon position-relative">
                                <i data-feather="mail" class="fea icon-sm icons"></i>
                                <input name="fromEmail" id="fromEmail" type="email" class="form-control ps-5 emailInput" placeholder="<?= lang('Pages.From_Email_Placeholder') ?>" value="<?= $adminSettings['fromEmail'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.From_Email_Required') ?>
                                </div> 
                            </div>
                        </div>
                    </div><!--end col-->                                                          
                </div><!--end row-->
            </div>
        </div><!--end col-->

        <!-- Support Name/Email -->
        <div class="col-xl-6 mt-4">
            <div class="card border-0 rounded shadow p-4">
                <h5 class="mb-0"><?= lang('Pages.Support_Name_Label') ?></h5>
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label" for="supportName"><?= lang('Pages.From_Name_Label') ?> <span class="text-danger">*</span></label>
                            <div class="form-icon position-relative">
                                <i data-feather="user" class="fea icon-sm icons"></i>
                                <input name="supportName" id="supportName" type="text" class="form-control ps-5 emailName" placeholder="<?= lang('Pages.From_Name_Placeholder') ?>" value="<?= $adminSettings['supportName'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.From_Name_Required') ?>
                                </div> 
                            </div>
                        </div>
                    </div><!--end col-->      
                    
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label" for="supportEmail"><?= lang('Pages.From_Email_Label') ?> <span class="text-danger">*</span></label>
                            <div class="form-icon position-relative">
                                <i data-feather="mail" class="fea icon-sm icons"></i>
                                <input name="supportEmail" id="supportEmail" type="email" class="form-control ps-5 emailInput" placeholder="<?= lang('Pages.From_Email_Placeholder') ?>" value="<?= $adminSettings['supportEmail'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.From_Email_Required') ?>
                                </div> 
                            </div>
                        </div>
                    </div><!--end col-->
                </div><!--end row-->
            </div>
        </div><!--end col-->

        <!-- Sales Name/Email -->
        <div class="col-xl-6 mt-4">
            <div class="card border-0 rounded shadow p-4">
                <h5 class="mb-0"><?= lang('Pages.Sales_Name_Label') ?></h5>
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label" for="salesName"><?= lang('Pages.From_Name_Label') ?> <span class="text-danger">*</span></label>
                            <div class="form-icon position-relative">
                                <i data-feather="user" class="fea icon-sm icons"></i>
                                <input name="salesName" id="salesName" type="text" class="form-control ps-5 emailName" placeholder="<?= lang('Pages.From_Name_Placeholder') ?>" value="<?= $adminSettings['salesName'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.From_Name_Required') ?>
                                </div> 
                            </div>
                        </div>
                    </div><!--end col-->      
                    
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label" for="salesEmail"><?= lang('Pages.From_Email_Label') ?> <span class="text-danger">*</span></label>
                            <div class="form-icon position-relative">
                                <i data-feather="mail" class="fea icon-sm icons"></i>
                                <input name="salesEmail" id="salesEmail" type="email" class="form-control ps-5 emailInput" placeholder="<?= lang('Pages.From_Email_Placeholder') ?>" value="<?= $adminSettings['salesEmail'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= lang('Pages.From_Email_Required') ?>
                                </div> 
                            </div>
                        </div>
                    </div><!--end col-->
                </div><!--end row-->
            </div>
        </div><!--end col-->

        <!-- Promotional Email Footer -->
        <div class="col-12 mt-4">
            <div class="card border-0 rounded shadow p-4">
                <h5 class="mb-0"><?= lang('Pages.Promotional_Email_Footer') ?></h5>
                <div class="row mt-4">
                    
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label class="form-label" for="htmlEmailFooter"><?= lang('Pages.HTML_Format') ?> <span class="text-danger">*</span></label>
                        <small class="text-info">[ <?= lang('Pages.available_values') ?> 
                            <span class="copy-to-clipboard">{app_name}</span> | 
                            <span class="copy-to-clipboard">{company_name}</span> | 
                            <span class="copy-to-clipboard">{company_address}</span> | 
                            <span class="copy-to-clipboard">{app_url}</span> 
                        ]</small>
                        <div class="form-icon position-relative">
                            <i data-feather="layout" class="fea icon-sm icons"></i>
                            <textarea name="htmlEmailFooter" id="htmlEmailFooter" rows="8" class="form-control ps-5" placeholder="<?= lang('Pages.HTML_codes_here') ?>" required=""><?= isset($myConfig['htmlEmailFooter']) ? htmlspecialchars(trim($myConfig['htmlEmailFooter']), ENT_QUOTES, 'UTF-8') : htmlspecialchars(trim('<div class="footer" style="background:#f8f9fa;padding:10px;text-align:center;font-size:12px;color:#6c757d;border-radius:0 0 5px 5px;margin-top:10px">
                                    <p>Simplify your licensing & digital product management with <strong>{app_name}</strong>, your all-in-one solution for license and digital product management—brought to you by <strong>{company_name}</strong>.</p>
                                    <p style="text-align:center"><a href="{app_url}" style="display:inline-block;padding:10px 20px;text-decoration:none;border-radius:3px;margin:10px;color:#fff;background-color:#007bff">Discover More</a></p>
                                </div>')) ?></textarea>
                            <div class="invalid-feedback">
                                <?= lang('Pages.Email_footer_html_codes_required') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="mb-3">
                        <label class="form-label" for="textEmailFooter"><?= lang('Pages.Text_Format') ?> <span class="text-danger">*</span></label>
                        <small class="text-info">[ <?= lang('Pages.available_values') ?> 
                            <span class="copy-to-clipboard">{app_name}</span> | 
                            <span class="copy-to-clipboard">{company_name}</span> | 
                            <span class="copy-to-clipboard">{company_address}</span> | 
                            <span class="copy-to-clipboard">{app_url}</span> 
                        ]</small>
                        <div class="form-icon position-relative">
                            <i data-feather="align-left" class="fea icon-sm icons"></i>
                            <textarea name="textEmailFooter" id="textEmailFooter" rows="8" class="form-control ps-5" placeholder="<?= lang('Pages.Text_email_footer_here') ?>" required=""><?= isset($myConfig['textEmailFooter']) ? htmlspecialchars(trim($myConfig['textEmailFooter']), ENT_QUOTES, 'UTF-8') : htmlspecialchars(trim("==========\nSimplify your licensing & digital product management with {app_name}, your all-in-one solution for license and digital product management—brought to you by {company_name} ({app_url})")) ?></textarea>
                            <div class="invalid-feedback">
                                <?= lang('Pages.Email_footer_text_required') ?>
                            </div>
                        </div>
                    </div>
                </div>


                </div><!--end row-->
            </div>
        </div><!--end col-->

    </form>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Email Service Setup modal -->
    <div class="modal fade" id="emailService" tabindex="-1" aria-labelledby="emailService-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><?= lang('Pages.Email_Service_Config') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="emailService-form" novalidate>
                        <input type="hidden" id="prev_protocol" name="prev_protocol" value="<?= $emailService['protocol'] ?? '' ?>"> 
                        <input type="hidden" id="prev_sendmailPath" name="prev_sendmailPath" value="<?= $emailService['sendmailPath'] ?? '' ?>"> 
                        <input type="hidden" id="prev_smtpHostname" name="prev_smtpHostname" value="<?= $emailService['smtpHostname'] ?? '' ?>"> 
                        <input type="hidden" id="prev_smtpUsername" name="prev_smtpUsername" value="<?= $emailService['smtpUsername'] ?? '' ?>"> 
                        <input type="hidden" id="prev_smtpPassword" name="prev_smtpPassword" value="<?= $emailService['smtpPassword'] ?? '' ?>"> 
                        <input type="hidden" id="prev_smtpPort" name="prev_smtpPort" value="<?= $emailService['smtpPort'] ?? 587 ?>"> 
                        <input type="hidden" id="prev_smtpEncryption" name="prev_smtpEncryption" value="<?= $emailService['smtpEncryption'] ?? '' ?>"> 

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="protocol">Protocol</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-cloud fea icon-sm icons"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path></svg>
                                    <select class="form-select form-control ps-5" id="protocol" name="protocol">
                                        <option value="" disabled><?= lang('Pages.Select_Option') ?></option>
                                        <option value="mail" <?= $emailService['protocol'] === 'mail' ? 'selected' : '' ?> >mail</option>
                                        <option value="sendmail" <?= $emailService['protocol'] === 'sendmail' ? 'selected' : '' ?> >Sendmail</option>
                                        <option value="smtp" <?= $emailService['protocol'] === 'smtp' ? 'selected' : '' ?> >SMTP</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.protocolError') ?>
                                    </div>                                        
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="sendmailPath">Server path to Sendmail</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder fea icon-sm icons"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                    <input name="sendmailPath" id="sendmailPath" type="text" class="form-control ps-5" value ="<?= $emailService['sendmailPath'] ? $emailService['sendmailPath'] : '' ?>" placeholder="The server path to Sendmail">
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.sendmailPathError') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="smtpHostname">SMTP Server Hostname</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link-2 fea icon-sm icons"><path d="M15 7h3a5 5 0 0 1 5 5 5 5 0 0 1-5 5h-3m-6 0H6a5 5 0 0 1-5-5 5 5 0 0 1 5-5h3"></path><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                    <input name="smtpHostname" id="smtpHostname" type="text" class="form-control ps-5" value="<?= $emailService['smtpHostname'] ?? '' ?>" placeholder="Your SMTP Server Hostname">
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.smtpHostnameError') ?>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="smtpUsername">SMTP Username</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user fea icon-sm icons"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <input name="smtpUsername" id="smtpUsername" type="text" class="form-control ps-5" value="<?= $emailService['smtpUsername'] ?? '' ?>" placeholder="Your SMTP Username">
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.smtpUsernameError') ?>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="smtpPassword">SMTP Password</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-key fea icon-sm icons"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                                    <input name="smtpPassword" id="smtpPassword" type="text" class="form-control ps-5" value="<?= $emailService['smtpPassword'] ?? '' ?>" placeholder="Your SMTP Password">
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.smtpPasswordError') ?>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="smtpPort">SMTP Port</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-hash fea icon-sm icons"><line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line></svg>
                                    <input name="smtpPort" id="smtpPort" type="number" class="form-control ps-5" value="<?= $emailService['smtpPort'] ?? '' ?>" placeholder="Your SMTP Port (e.g. 587)">
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.smtpPortError') ?>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="smtpEncryption">SMTP Encryption</label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock fea icon-sm icons"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <select class="form-select form-control ps-5" id="smtpEncryption" name="smtpEncryption">
                                        <option value="" disabled><?= lang('Pages.Select_Option') ?></option>
                                        <option value="tls" <?= $emailService['smtpEncryption'] === 'tls' ? 'selected' : '' ?> >TLS</option>
                                        <option value="ssl" <?= $emailService['smtpEncryption'] === 'ssl' ? 'selected' : '' ?> >SSL</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.smtpEncryptionError') ?>
                                    </div>                                        
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-md-6">
                            <div class="mt-4 mb-3 text-center text-center">
                                <button class="mx-auto btn btn-primary" id="email-service-settings-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save') ?></button>
                            </div>
                        </div><!--end col-->

                    </form>
                </div><!-- end modal body -->
            </div>
        </div>
    </div>
    
    <!-- Email Test modal -->
    <div class="modal fade" id="TestEmail" tabindex="-1" aria-labelledby="TestEmail-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><?= lang('Pages.Test_Email_Sending') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="TestEmail-form" novalidate>

                        <!-- From Email -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="testFromEmail"><?= lang('Pages.test_From_email') ?></label>
                                <div class="form-icon position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail fea icon-sm icons"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input name="testFromEmail" id="testFromEmail" type="text" class="form-control ps-5 emailInput" value ="<?= $adminSettings['fromEmail'] ?>" placeholder="Enter From Email address" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_From_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->
                        
                        <!-- To Email -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="testToEmail"><?= lang('Pages.test_To_email') ?></label>
                                <div class="form-icon position-relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail fea icon-sm icons"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <input name="testToEmail" id="testToEmail" type="text" class="form-control ps-5 emailInput" value ="<?= $myConfig['replyToEmail'] ?>" placeholder="Enter To Email address" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_To_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->
                        
                        <!-- Subject -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="testSubjectEmail"><?= lang('Pages.test_Subject_email') ?></label>
                                <div class="form-icon position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 fea icon-sm icons"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    <input name="testSubjectEmail" id="testSubjectEmail" type="text" class="form-control ps-5" value ="This is a test email from <?= $adminSettings['appName'] ?>" placeholder="Enter email subject" required>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_Subject_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->							

                        <!-- Email Body-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="testBodyEmail"><?= lang('Pages.test_Body_email') ?></label>
                                <div class="form-icon position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square fea icon-sm icons"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    <textarea name="testBodyEmail" id="testBodyEmail" rows="5" class="form-control ps-5" placeholder="Enter email message" required>The quick brown fox jumps over the lazy dog.</textarea>
                                    <div class="invalid-feedback">
                                        <?= lang('Pages.test_Body_Email_Required') ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div><!--end col-->

                        <div class="d-flex justify-content-center align-items-center">
                            <label class="toggler text-muted " id="text">Text</label>
                            <div class="form-check form-switch mx-3">
                                <input class="form-check-input" type="checkbox" id="testEmailFormat" name="testEmailFormat" checked="">
                            </div>
                            <label class="toggler text-muted toggler--is-active" id="html">HTML</label>
                        </div>                            

                        <div class="col-12">
                            <div class="mt-4 mb-3 text-center text-center">
                                <button class="mx-auto btn btn-primary" id="test-email-submit"><i class="uil uil-envelope-send"></i> <?= lang('Pages.Send_Test_Email') ?></button>
                            </div>
                        </div><!--end col-->

                    </form>
                </div><!-- end modal body -->
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <script type="text/javascript">
        $(document).ready(function() {                                
            /******************************
            // Handle the app settings save
            ******************************/
            $('#email-settings-submit').on('click', function (e) {
                e.preventDefault();

                const formElement = $('#email-settings-form');
                const submitButtonElement = $(this);
                const emailInputElement = $('.emailInput');
                const emailNameInputElement = $('.emailName');
                const htmlEmailFooterElement = $('#htmlEmailFooter');
                const textEmailFooterElement = $('#textEmailFooter');

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const disallowedCharsRegexGeneralText = /[^a-zA-Z0-9\s\-_\.]/;
                const disallowedCharsRegexForEmail = /[~!#$%&*+=|:()\[\]]/;

                // Enable button loading effect
                enableLoadingEffect(submitButtonElement);

                // Remove existing 'is-invalid' classes
                formElement.find('.is-invalid').removeClass('is-invalid').end().find('.is-valid').removeClass('is-valid');

                // Validation logic
                let isValid = true;

                // Validate email inputs
                const validateEmailInputs = () => {
                    const emailValue = emailInputElement.val().trim();
                    const inputWithError = emailInputElement.attr('id');
                    const errorPlaceholder = emailInputElement.attr('placeholder');
                    if (emailValue !== '' && emailRegex.test(emailValue) && !disallowedCharsRegexForEmail.test(emailValue)) {
                        emailInputElement.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', '<?= lang('Pages.Invalid') ?> ' + errorPlaceholder);
                    }
                };

                // Validate Email Name
                const validateEmailNames = () => {
                    const emailNameValue = emailNameInputElement.val().trim();
                    const inputWithError = emailNameInputElement.attr('id');
                    const errorMsg = emailNameInputElement.attr('placeholder');
                    if (emailNameValue === '') {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', errorMsg + ' is required.');
                    } else if (disallowedCharsRegexGeneralText.test(emailNameValue)) {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', '<?= lang('Pages.Invalid') ?> ' + errorMsg);
                    } else {
                        emailNameInputElement.removeClass('is-invalid').addClass('is-valid');
                    }
                };

                // Validate Email Footers
                const validateEmailFooters = () => {
                    const htmlEmailFooterValue = htmlEmailFooterElement.val().trim();
                    const textEmailFooterValue = textEmailFooterElement.val().trim();

                    if(htmlEmailFooterValue === '') {
                        $(htmlEmailFooterElement).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', '<?= lang('Pages.Email_footer_html_codes_required') ?>');
                    }

                    if(textEmailFooterValue === '') {
                        $(textEmailFooterElement).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', '<?= lang('Pages.Email_footer_text_required') ?>');
                    }
                };

                // Perform validations
                validateEmailInputs();
                validateEmailNames();
                validateEmailFooters();

                if (!isValid) {
                    showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                    disableLoadingEffect(submitButtonElement);
                    return;
                }

                // Check if there are any elements with 'is-invalid' class
                if (formElement.find('.is-invalid').length === 0) {

                    // Form data is valid, proceed with further processing
                    var data = new FormData(formElement[0]);

                    // Append additional data to the FormData object
                    // Email Setup
                    data.append('fromName', $('#fromName').val());
                    data.append('fromEmail', $('#fromEmail').val());

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('admin-options/email-settings/save') ?>',
                        method: 'POST',
                        processData: false,
                        contentType: false,
                        data: data,
                        success: function (response) {
                            var alertsArray = []; // Array to store alert HTML
                            let toastType = 'info';

                            // Handle inputs response
                            if (response.inputs) {
                                if (response.inputs.success) {
                                    showToast('success', response.inputs.msg);
                                } else {
                                    if (typeof response.inputs.msg === 'object') {
                                        // Display each error message in a separate alert
                                        Object.keys(response.inputs.msg).forEach(function (key) {
                                            showToast('danger', response.inputs.msg[key]);
                                            const inputElement = $('#' + key);
                                            if (inputElement.length) {
                                                inputElement.addClass('is-invalid');
                                            }
                                        });
                                    } else {
                                        // Display as a single alert
                                        showToast('danger', response.inputs.msg);
                                    }
                                }
                            }
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

            /*******************
             * Handle Test Email
             ******************/
            $('#test-email-submit').on('click', function (e) {
                e.preventDefault();

                const form = $('#TestEmail-form');
                const submitButton = $(this);
                const testFromEmail = $('#testFromEmail');
                const testToEmail = $('#testToEmail');
                const testSubjectEmail = $('#testSubjectEmail');
                const testBodyEmail = $('#testBodyEmail');
                const testEmailFormat = $('#testEmailFormat');
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;                    
                const disallowedCharsRegexForEmail = /[~!#$%&*+=|:()\[\]]/;

                // Enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid').end().find('.is-valid').removeClass('is-valid');
                
                // Validation logic
                let isValid = true;
                let validationErrors = [];

                // Validate From email input
                const validateTestFromEmail = () => {
                    const emailFromValue = testFromEmail.val().trim();
                    const inputWithError = testFromEmail.attr('id');
                    if (emailFromValue !== '' && emailRegex.test(emailFromValue) && !disallowedCharsRegexForEmail.test(emailFromValue)) {
                        testFromEmail.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', '<?= lang('Pages.test_From_Email_Required') ?>');
                    }
                };  

                // Validate To email input
                const validateTestToEmail = () => {
                    const emailFromValue = testToEmail.val().trim();
                    const inputWithError = testToEmail.attr('id');
                    if (emailFromValue !== '' && emailRegex.test(emailFromValue) && !disallowedCharsRegexForEmail.test(emailFromValue)) {
                        testToEmail.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', '<?= lang('Pages.test_To_Email_Required') ?>');
                    }
                };  
                
                // Validate email subject
                const validateTestSubject = () => {
                    const emailSubjectValue = testSubjectEmail.val().trim();
                    const inputWithError = testSubjectEmail.attr('id');
                    const errorMsg = '<?= lang('Pages.test_Subject_Email_Required') ?>';
                    if (emailSubjectValue === '') {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', errorMsg);
                    }
                    else {
                        testSubjectEmail.removeClass('is-invalid').addClass('is-valid');
                    }
                };
                
                // Validate email body
                const validateTestBody = () => {
                    const emailBodyValue = testBodyEmail.val().trim();
                    const inputWithError = testBodyEmail.attr('id');
                    const errorMsg = '<?= lang('Pages.test_Body_Email_Required') ?>';
                    if (emailBodyValue === '') {
                        $('#' + inputWithError).addClass('is-invalid');
                        isValid = false;
                        showToast('danger', errorMsg);
                    }
                    else {
                        testBodyEmail.removeClass('is-invalid').addClass('is-valid');
                    }
                };							
                
                // Perform validations
                validateTestFromEmail();
                validateTestToEmail();
                validateTestSubject();
                validateTestBody();

                if (!isValid) {
                    showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                    disableLoadingEffect(submitButton);
                    return;
                }
                
                // Check if there are any elements with 'is-invalid' class
                if (form.find('.is-invalid').length === 0) {
                    $.ajax({
                        url: '<?= base_url('email-service/settings/test') ?>',
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            let toastType = 'info';

                            if (response.status == 1) {
                                toastType = 'success';
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
            });            

            /*******************************
             * Handle Email Service Settings
             ******************************/
            $('#email-service-settings-submit').on('click', function (e) {
                e.preventDefault();

                const form = $('#emailService-form');
                const submitButton = $(this);
                const protocolSelect = $('#protocol');
                const sendmailPathInput = $('#sendmailPath');
                const smtpHostnameInput = $('#smtpHostname');
                const smtpUsernameInput = $('#smtpUsername');
                const smtpPasswordInput = $('#smtpPassword');
                const smtpPortInput = $('#smtpPort');
                const smtpEncryptionSelect = $('#smtpEncryption');

                // Enable button loading effect
                enableLoadingEffect(submitButton);

                // Remove existing 'is-invalid' classes
                form.find('.is-invalid').removeClass('is-invalid').end().find('.is-valid').removeClass('is-valid');

                // Validation logic
                let isValid = true;

                // Validate Protocol
                if (protocolSelect.val().trim() === '') {
                    isValid = false;
                    protocolSelect.addClass('is-invalid');
                    showToast('danger', "<?= lang('Pages.protocolError') ?>");
                }

                // Validate Sendmail Path
                if (protocolSelect.val().trim() !== '' && protocolSelect.val().trim() !== 'smtp') {
                    if (sendmailPathInput.val().trim() === '') {
                        isValid = false;
                        sendmailPathInput.addClass('is-invalid');
                        showToast('danger', "<?= lang('Pages.sendmailPathError') ?>");
                    }
                }

                // Validate SMTP Credentials
                if (protocolSelect.val().trim() !== '' && protocolSelect.val().trim() === 'smtp') {
                    if (smtpHostnameInput.val().trim() === '') {
                        isValid = false;
                        smtpHostnameInput.addClass('is-invalid');
                        showToast('danger', "<?= lang('Pages.smtpHostnameError') ?>");
                    }
                    if (smtpUsernameInput.val().trim() === '') {
                        isValid = false;
                        smtpUsernameInput.addClass('is-invalid');
                        showToast('danger', "<?= lang('Pages.smtpUsernameError') ?>");
                    }
                    if (smtpPasswordInput.val().trim() === '') {
                        isValid = false;
                        smtpPasswordInput.addClass('is-invalid');
                        showToast('danger', "<?= lang('Pages.smtpPasswordError') ?>");
                    }
                    if (smtpPortInput.val().trim() === '') {
                        isValid = false;
                        smtpPortInput.addClass('is-invalid');
                        showToast('danger', "<?= lang('Pages.smtpPortError') ?>");
                    }
                    if (smtpEncryptionSelect.val().trim() === '') {
                        isValid = false;
                        smtpEncryptionSelect.addClass('is-invalid');
                        showToast('danger', "<?= lang('Pages.smtpEncryptionError') ?>");
                    }
                }

                if (!isValid) {
                    showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                    disableLoadingEffect(submitButton);
                    return;
                }

                $.ajax({
                    url: '<?= base_url('admin-options/email-settings/save-email-service') ?>',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        let toastType = 'info';

                        if (response.status == 1) {
                            toastType = 'success';

                            // Update the hidden inputs
                            $('#prev_protocol').val($('#protocol').val());
                            $('#prev_sendmailPath').val($('#sendmailPath').val());
                            $('#prev_smtpHostname').val($('#smtpHostname').val());
                            $('#prev_smtpUsername').val($('#smtpUsername').val());
                            $('#prev_smtpPassword').val($('#smtpPassword').val());
                            $('#prev_smtpPort').val($('#smtpPort').val());
                            $('#prev_smtpEncryption').val($('#smtpEncryption').val());
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
            });                
        });
        
        // Add an event listener to the button
        document.getElementById('edit-email-service-btn').addEventListener('click', function() {
            // Use jQuery to trigger the modal display
            $('#emailService').modal('show');
        });
        
        document.getElementById('test-email-service-btn').addEventListener('click', function() {
            // Use jQuery to trigger the modal display
            $('#TestEmail').modal('show');
        });                
    </script>
<?= $this->endSection() //End section('scripts')?>