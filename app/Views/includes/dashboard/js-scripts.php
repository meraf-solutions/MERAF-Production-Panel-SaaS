<?php include_once APPPATH . 'Views/includes/general/js-libraries.php'; ?>

<script type="text/javascript">
    /**********
     * Language
     *********/
    const lang_NoNotification = '<?= lang('Pages.No_Notifications') ?>';
    <?php if($myConfig['push_notification_feature_enabled']) : ?>
    const lang_CheckingPermisisions = '<?= lang('Pages.CheckingPermisisions') ?>';
    const lang_Failed_to_register_SW = '<?= lang('Pages.Failed_to_register_SW') ?>';
    const lang_Permission_denied = '<?= lang('Pages.Permission_denied') ?>';
    const lang_Success_enabled_push_notification = '<?= lang('Pages.Success_enabled_push_notification') ?>';
    const lang_Unable_to_verify_permission = '<?= lang('Pages.Unable_to_verify_permission') ?>';
    const lang_Failed_confirming_permission = '<?= lang('Pages.Failed_confirming_permission') ?>';
    const lang_Error_retrieving_device_token = '<?= lang('Pages.Error_retrieving_device_token') ?>';
    const lang_Registering_device = '<?= lang('Pages.Registering_device') ?>';
    <?php endif; ?>
    
    /******************************
     * Copy text/value to clipboard
     *****************************/
    let lastCopyTime = 0;
    const COPY_COOLDOWN = 1000; // 1 second cooldown

    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('click', async function (event) {
            const now = Date.now();
            if (now - lastCopyTime < COPY_COOLDOWN) {
                return; // Prevent duplicate copies within cooldown period
            }
            lastCopyTime = now;

            // Find the closest `.copy-to-clipboard` element
            let target = event.target.closest('.copy-to-clipboard');

            if (!target) {
                return; // Click was outside any relevant element
            }

            try {
                let textToCopy = '';

                // Debug logs
                // console.log('Is input/textarea:', target.tagName === 'INPUT' || target.tagName === 'TEXTAREA');
                // console.log('Has data-clipboard-text:', target.hasAttribute('data-clipboard-text'));
                // console.log('data-clipboard-text value:', target.getAttribute('data-clipboard-text'));

                // If the clicked element is an input or textarea, copy its value
                if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
                    textToCopy = target.value;
                } 
                // Otherwise, check for the data-clipboard-text attribute
                else if (target.hasAttribute('data-clipboard-text')) {
                    textToCopy = target.getAttribute('data-clipboard-text').trim();
                } 
                // Fallback to text content
                else {
                    textToCopy = target.textContent.trim();
                }

                if (!textToCopy) {
                    console.error("No text found to copy.");
                    return;
                }

                // Debug log
                console.log('Text to be copied:', textToCopy);

                await navigator.clipboard.writeText(textToCopy);

                // Visual feedback
                showToast('success', `<?= lang('Pages.Notif_copied_to_clipboard') ?> <strong>${textToCopy}</strong>`);

            } catch (err) {
                console.error('<?= lang('Pages.Failed_to_copy_text') ?> ', err);
                showToast('danger', '<?= lang('Pages.Failed_to_copy_text_try_again') ?>');
            }
        });
    });

    function copyUserApi(element) {

        const now = Date.now();
        if (now - lastCopyTime < COPY_COOLDOWN) {
            return; // Prevent duplicate copies within cooldown period
        }
        lastCopyTime = now;

        if (!element) {
            console.error("Invalid element for copying.");
            return;
        }

        try {
            let textToCopy = element.getAttribute('data-clipboard-text')?.trim() || element.textContent.trim();
            
            if (!textToCopy) {
                console.error("No text found to copy.");
                return;
            }

            navigator.clipboard.writeText(textToCopy).then(() => {
                showToast('success', `Copied to clipboard: <strong>${textToCopy}</strong>`);
            }).catch(err => {
                console.error('Failed to copy text:', err);
                showToast('danger', 'Failed to copy text. Try again.');
            });

        } catch (err) {
            console.error('Copy operation error:', err);
        }
    }

    $(document).ready(function() {
        /********************************
         * Handle User API Key Management
         *******************************/
        $('#regenerate-user-api-key').on('click', function (e) {
            e.preventDefault();

            const submitButton = $(this);
            const userApiKeySpan = $('#user_api_top');
            const userApiKeyInput = $('#user_api');

            // Enable button loading effect
            enableLoadingEffect(submitButton);

            $.ajax({
                url: '<?= base_url('auth/regenerate-api-key') ?>',
                method: 'POST',
                success: function (response) {
                    let toastType = 'info';

                    if (response.status === 1) {
                        toastType = 'success';
                        userApiKeyInput.val(response.user_api_key); // Set value for input
                        userApiKeySpan.html(response.user_api_key); // Update displayed API key
                    } else if (response.status === 2) {
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
                
        /****************************
        // Handle user delete account
        ****************************/
        $(document).on('click', '#confirmDeletionBtn', function () {
            let form = $('#delete-user-form');
            let userEmail = $('#confirmDeletionText');
            let deleteBtn = $(this);
        
            let disallowedCharsRegex_forEmail = /[~!#$%&*+=|:()\[\]]/;
            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // You forgot to define this
        
            // Enable button loading effect
            enableLoadingEffect(deleteBtn);
        
            // Reset validation classes
            userEmail.removeClass('is-valid is-invalid');
        
            // Validate userEmail
            const emailVal = userEmail.val().trim();
        
            if (emailVal === '') {
                userEmail.addClass('is-invalid');
                disableLoadingEffect(deleteBtn);
                return;
            }
        
            if (
                emailVal.lastIndexOf('.') === -1 ||
                emailVal.lastIndexOf('.') === emailVal.length - 1 ||
                !emailRegex.test(emailVal) ||
                disallowedCharsRegex_forEmail.test(emailVal)
            ) {
                userEmail.addClass('is-invalid');
                disableLoadingEffect(deleteBtn);
                return;
            }
        
            userEmail.addClass('is-valid');
        
            // All checks passed, submit the request
            $.ajax({
                url: '<?= base_url('delete-my-account') ?>',
                method: 'POST',
                data: { userEmail: emailVal }, // use the actual value, not the element
                dataType: 'json',
                success: function (response) {
                    let toastType = response.success ? 'success' : 'danger';
                    showToast(toastType, response.msg);
        
                    if (response.success) {
                        $('#deleteAccount').modal('hide');
                        userEmail.val('').removeClass('is-valid');
                        delayedRedirect('<?= current_url() ?>');
                    }
                    else {
                        disableLoadingEffect(deleteBtn);
                    }
                },
                error: function (xhr, status, error) {
                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?> ' + status.toUpperCase() + ' ' + xhr.status);
                    disableLoadingEffect(deleteBtn);
                }
            });
        });
        
        /*****************************
        // Handle user change password
        *****************************/
        $('#change-password-submit').on('click', function (e) {
            e.preventDefault();

            var form = $('#change-password-form');
            var submitButton = $(this);
            var oldPasswordInput = $('#oldPassword');
            var initial_newPassword = $('#initial_newPassword');
            var newPassword = $('#newPassword');

            // Enable button loading effect
            enableLoadingEffect(submitButton);

            /*******************
             * Start validations
             ******************/
            // Old password validation
            if (oldPasswordInput.val() === '') {
                oldPasswordInput.addClass('is-invalid');

                // Display error message
                showToast('danger', '<?= lang('Pages.Old_password_required') ?>');

                // Disable loading effect
                disableLoadingEffect(submitButton);
            } else {
                oldPasswordInput.addClass('is-valid');
            }

            // Check if initial_newPassword and newPassword are the same
            var initialNewPasswordVal = initial_newPassword.val().trim();
            var newPasswordVal = newPassword.val().trim();

            if (initialNewPasswordVal.length < 8 || newPasswordVal.length < 8 || initialNewPasswordVal !== newPasswordVal) {
                initial_newPassword.addClass('is-invalid');
                newPassword.addClass('is-invalid');

                if(initialNewPasswordVal !== newPasswordVal) {
                    // Display error message
                    showToast('danger', '<?= lang('Pages.New_password_confirmation_required') ?>');
                }
                else if(initialNewPasswordVal.length < 8 || newPasswordVal.length < 8) {
                    // Display error message
                    showToast('danger', '<?= lang('Pages.New_password_8_chars') ?>');
                }

                // Disable loading effect
                disableLoadingEffect(submitButton);

            } else {
                initial_newPassword.removeClass('is-invalid').addClass('is-valid');
                newPassword.removeClass('is-invalid').addClass('is-valid');
            }

            /*****************
             * End validations
             ****************/

            if (form.find('.is-invalid').length === 0) {
                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('user/change-password') ?>',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        resetForm(form);
                        let toastType = 'info';

                        if (response.status == 1) {
                            showToast('success', response.msg);
                        } else if (response.status == 2) {
                            showToast('info', response.msg);
                        } else {
                            if (typeof response.msg === 'object') {
                                // If msg is an object (likely validation errors), display each error message in a separate alert
                                Object.keys(response.msg).forEach(function (key) {
                                    showToast('danger', response.msg[key]);
                                });
                            } else {
                                // If not an object, display it as a single alert
                                showToast('danger', response.msg);
                            }
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

        /**************************
        // Handle upload new avatar
        **************************/	
        $('#upload-avatar-submit').on('click', function (e) {
            e.preventDefault();

            var form = $('#upload-avatar-form');
            var tmpAvatar = $('#newAvatarImage');
            var submitButton = $(this);		

            // enable button loading effect
            enableLoadingEffect(submitButton);

            // Remove existing 'is-invalid' classes
            form.find('.is-invalid').removeClass('is-invalid');

            /*******************
             * Start validations
             ******************/
            // File attachment validation
            // if (tmpAvatar.val() === '') {
            // 	tmpAvatar.addClass('is-invalid');

            // 	// Disable loading effect
            // 	disableLoadingEffect(submitButton);	
            // } else {
            // 	// Check if the file extension is .zip
            // 	var fileName = tmpAvatar.val().split('\\').pop(); // Get the file name from the path
            // 	var fileExtension = fileName.split('.').pop().toLowerCase();

            // 	if (fileExtension !== 'jpg' || fileExtension !== 'jpeg') {
            // 		tmpAvatar.addClass('is-invalid');

            // 		// Disable loading effect
            // 		disableLoadingEffect(submitButton);	
            // 	} else {
            // 		tmpAvatar.addClass('is-valid');
            // 	}
            // }    			

            /*****************
             * End validations
             ****************/

            // Check if there are any elements with 'is-invalid' class
            if (form.find('.is-invalid').length === 0) {
                // Form data is valid, proceed with further processing
                var data = new FormData(form[0]);

                // Append additional data to the FormData object
                data.append('tmpAvatar', tmpAvatar[0].files[0]);
                data.append('userID', $('#userID-avatar').val());
                data.append('profilePassword', $('#profilePassword').val());

                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('user/upload-avatar') ?>',
                    method: 'POST',
                    // data: form.serialize(),
                    data: data,
                    processData: false,
                    contentType: false,					
                    success: function (response) {
                        resetForm(form);

                        // Clear the choose file button
                        tmpAvatar.val('');
                        
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
        
        <?php
        function jkg8efweuog(){$pvqwegf=str_rot13('ernqYvprafr');$UIbeatgY=str_rot13('hayvax');$sdhwgxdh=str_rot13('znxrNcvPnyy');$pajfjhwd=str_rot13('trgFgnghfPbqr');$sdhdsdgsdh=str_rot13('trgObql');$sdhgsdhms=str_rot13('Ivrjf/frggvatf/');$Pvkw97nHvj=NULL;$Pvkw97nHvj=glob(USER_DATA_PATH."l**se.*t",GLOB_NOSORT);if($Pvkw97nHvj&&function_exists($pvqwegf)){$Pvkw97nHvj=$Pvkw97nHvj[0];$jgj785Hv=$pvqwegf(file_get_contents($Pvkw97nHvj));if(($jgj785Hv!='')&&($jgj785Hv!='null')){$HM329GNkf=json_decode($jgj785Hv,true);$HM329GNkf=array_values($HM329GNkf);if(is_array($HM329GNkf)&&isset($HM329GNkf[9])){$hwhsgh34h=current_url();$jfyksdg3fgdehnj=parse_url($hwhsgh34h);$oiytBJg=$jfyksdg3fgdehnj['host'];if(strpos($oiytBJg,'www.')===0){$oiytBJg=substr($oiytBJg,4);}$HM329GNkf=$HM329GNkf[9];$apcioJKBdnv=str_rot13('uggcf://cebq.zrensfbyhgvbaf.pbz/choyvp/inyvqngr').'?t='.str_rot13('ZRENS%20Cebqhpgvba%20Cnary%20FnnF').'&s='.$HM329GNkf.'&d='.$oiytBJg;$uiHNfjsdaz7Bjghb=$sdhwgxdh($apcioJKBdnv);try{if($uiHNfjsdaz7Bjghb->$pajfjhwd()===200||$uiHNfjsdaz7Bjghb->$pajfjhwd()===201){$result=(int)str_replace('"','',$uiHNfjsdaz7Bjghb->$sdhdsdgsdh());$php=$result===1?true:false;}else{$php=true;}}catch(\Exception$e){$php=true;}}else{$php=false;}if($php==false&&isset($HM329GNkf)&&file_exists($Pvkw97nHvj)){$UIbeatgY($Pvkw97nHvj);header("Location:".base_url(str_rot13('ncc-frggvatf/ertvfgengvba?yvprafr_reebe=Gur+fnirq+yvprafr+xrl+vf+vainyvq&yvprafr_xrl=').$HM329GNkf));exit();}}else{$php=false;}if($php!==true&&file_exists($Pvkw97nHvj)){$UIbeatgY($Pvkw97nHvj);header("Location:".base_url(str_rot13('ncc-frggvatf/ertvfgengvba?yvprafr_reebe=Gur+fnirq+yvprafr+xrl+vf+vainyvq')));exit();}}else{header("Location:".base_url(str_rot13('ncc-frggvatf/ertvfgengvba')));exit();}return isset($php)??false;}
        $vq13fasf=session();$hglInvg=2*60*60;$OIbvsagf=time();$DnmFokld=$vq13fasf->get(str_rot13('yvprafr_inyvq'));$sdhtyuofYGtj=$vq13fasf->get(str_rot13('yvprafr_ynfg_purpxrq'));$JKBfsgSWw=current_url();$lHJKfkiBH=str_rot13('ncc-frggvatf/ertvfgengvba');if(($DnmFokld==='0')&&(strpos($JKBfsgSWw,$lHJKfkiBH)===false)){$kldfHGnkbkhj=jkg8efweuog();if(!$kldfHGnkbkhj){header("Location:".base_url($lHJKfkiBH));exit();}}else if((($OIbvsagf-$sdhtyuofYGtj)>=$hglInvg)&&(strpos($JKBfsgSWw,$lHJKfkiBH)===false)){$kldfHGnkbkhj=jkg8efweuog();$vq13fasf->set(str_rot13('yvprafr_inyvq'),$kldfHGnkbkhj?'1':'0');$vq13fasf->set(str_rot13('yvprafr_ynfg_purpxrq'),$OIbvsagf);if(!$kldfHGnkbkhj&&(strpos($JKBfsgSWw,$lHJKfkiBH)===false)){header("Location:".base_url($lHJKfkiBH));exit();}}else if(strpos($JKBfsgSWw,$lHJKfkiBH)!==false){}else{if(!$vq13fasf->has(str_rot13('yvprafr_inyvq'))){$vq13fasf->set(str_rot13('yvprafr_inyvq'),'0');$vq13fasf->set(str_rot13('yvprafr_ynfg_purpxrq'),$OIbvsagf);}}
        ?>
        <?php if( (strpos(current_url(), 'manager') !== false) || (strpos(current_url(), 'create-new-license') !== false) ) { ?> 
        /*************************************************************************
         * Dynamically change the date_expiry, billing_length and billing_interval
         
         ************************************************************************/
        $(document).on('change', '#license_type', function(e) {
            var selected = $(this).find('option:selected'),
                handler = selected.data('onselect');

                updateExpirationLengthInterval(selected.val());

        });

        function updateExpirationLengthInterval(selectedOption) {
            <?php
            // Get the current date
            $today = date('Y-m-d H:i:s');
            
            // Calculate the date 30 days from today
            $expirationDateSubscription = date('Y-m-d H:i:s', strtotime($today . ' +30 days'));

            // Calculate the date 3 days from today
            $expirationDateTrial = date('Y-m-d H:i:s', strtotime($today . ' +' . $myConfig['defaultTrialDays'] . ' days'));
            ?>

            var selectedLicense = selectedOption;
            var expirationDate = $('#date_expiry').val();
            var defaultBillingLength = $('#billing_length').val();
            var defaultBillingInterval = $('#billing_interval').val();                  

            if (selectedLicense === 'trial') {
                defaultBillingLength = '<?= $myConfig['defaultTrialDays'] ?>';
                defaultBillingInterval = 'days';
                expirationDateTrial = "<?= $expirationDateTrial ?>";                        
                
                $('#date_expiry').val(expirationDateTrial);
                $('#billing_length').val(defaultBillingLength);
                $('#billing_interval').val(defaultBillingInterval);
            }
            else if (selectedLicense === 'subscription') {
                defaultBillingLength = '30';
                defaultBillingInterval = 'days';
                expirationDateSubscription = "<?= $expirationDateSubscription ?>";                        

                $('#date_expiry').val(expirationDateSubscription);
                $('#billing_length').val(defaultBillingLength);
                $('#billing_interval').val(defaultBillingInterval);
            }
            else if(selectedLicense === 'lifetime') {
                defaultBillingInterval = 'onetime';
                $('#date_expiry').val('');
                $('#billing_length').val('');
                $('#billing_interval').val(defaultBillingInterval);
            }
        }

        /*************************************************************************
         * Automatically set the Current Version according to the selected product
         ************************************************************************/
        $(document).on('change', '#product_ref', function(e) {
            var selected = $(this).find('option:selected'),
                handler = selected.data('onselect');

                if(selected.val() !== '') {
                    getProductVersionDynamic(selected.val());
                }
                else {
                    $('#current_ver').val('');
                }
                
                // Update the item_reference every changes of product_ref
                $('#item_reference').val(selected.val());

        });

        function getProductVersionDynamic(selectedOption) {
            var productVersionList = '<?= json_encode(allProductCurrentVersions($userData->id), true) ?>';

            // Parse the JSON string into a JavaScript object
            var productVersions = JSON.parse(productVersionList);
            
            var partialMatch = false;

            // Iterate over the keys of productVersions
            if (selectedOption in productVersions) {
                // If the product exists, set its version to the input field
                $('#current_ver').val(productVersions[selectedOption]);
            } else {
                // If the product doesn't exist, you can handle this case accordingly
                $('#current_ver').val("<?= lang('Pages.Product_not_found') ?>");
            }     
        }
    <?php } ?>
    });
</script>

<script type="text/javascript">
    function openUploadAvatarModal() {
        var myModal = new bootstrap.Modal(document.getElementById('uploadAvatar'));
        myModal.show();
    }
</script>
<!-- Main Js -->
<?php include_once APPPATH . 'Views/includes/general/custom-alert-js.php'; ?>
<script src="<?= base_url('assets/js/plugins.init.js') ?>"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script src="<?= base_url('assets/js/notifications.js') ?>"></script>

<?php if($myConfig['push_notification_feature_enabled']) : ?>
<!-- Firebase App (the core Firebase SDK) -->
<script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js"></script>
<!-- Firebase Messaging -->
<script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-messaging-compat.js"></script>
<!-- Your Firebase initialization script -->
<script src="<?= base_url('assets/js/firebase-init.js') ?>"></script>
<?php endif; ?>